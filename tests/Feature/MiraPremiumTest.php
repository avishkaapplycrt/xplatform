<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\MiraPremium\MiraEvent;
use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraScan;
use App\Models\MiraPremium\MiraUsage;
use App\Services\MiraPremium\Entitlement;
use App\Services\MiraPremium\UrlGuard;
use App\Services\MiraPremium\VisibilityEngine;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Mira Premium workspace: entitlement, metered quotas, the two-phase signed
 * scan protocol, projects, the AI visibility engine, chat metering and the
 * server-side premium decision. Every site and OpenAI call is faked; DNS is
 * stubbed through UrlGuard's resolver.
 */
class MiraPremiumTest extends TestCase
{
    private const GUEST_IP = '203.0.113.7';

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_03_18_000002_create_clients_table.php',
            'database/migrations/2026_10_05_000001_create_askmirap_predefined_prompts_table.php',
            'database/migrations/2026_10_15_000001_create_mira_premium_tables.php',
            'database/migrations/2026_10_16_000001_create_mira_seo_snapshots_table.php',
            'database/migrations/2026_10_17_000001_add_insights_to_mira_visibility_answers.php',
        ]]);

        config([
            'services.openai.api_key'  => 'test-key',
            'mira_premium.turnstile.site_key'   => null,
            'mira_premium.turnstile.secret_key' => null,
            'mira_premium.dispatch'    => 'after_response',
            // Tests exercise the real plan rules, whatever the local .env unlocks.
            'mira_premium.unlock_all'  => null,
            'mira_premium.dataforseo.demo' => false,
            // These tests queue OpenAI replies in order; the per-answer analysis call is tested separately.
            'mira_premium.visibility.analyze_answers' => false,
        ]);

        // Every test host resolves to a public address.
        $this->app->instance(UrlGuard::class, new UrlGuard(fn () => ['93.184.216.34']));
        $this->fakeWeb();
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    // ---------------------------------------------------------------- helpers

    private function client(array $attrs = []): Client
    {
        static $n = 0;
        $n++;

        return Client::forceCreate(array_merge([
            'name'         => "Owner {$n}",
            'company_name' => "Company {$n}",
            'email'        => "owner{$n}@example.test",
            'password'     => bcrypt('secret-password'),
            'status'       => 'active',
            'plan'         => 'free',
        ], $attrs));
    }

    private function page(string $title = 'Acme Analytics — customer insight', string $body = ''): string
    {
        $prose = '<p>' . str_repeat('Acme helps local businesses understand their customers with clear reports. ', 20) . '</p>';

        return "<!doctype html><html lang=\"en\"><head><title>{$title}</title><meta name=\"description\" content=\"Customer analytics for growing teams and local shops everywhere.\"></head><body><h1>Acme</h1>{$prose}{$body}</body></html>";
    }

    /** Fakes acme.test, rival.test and down.test, plus OpenAI with the given replies (in order). */
    private function fakeWeb(array $openAiReplies = []): void
    {
        Http::swap(new Factory());

        $openAi = Http::sequence();
        foreach ($openAiReplies as $reply) {
            $openAi->push(['choices' => [['message' => ['content' => $reply]]]]);
        }
        $openAi->whenEmpty(Http::response(['choices' => [['message' => ['content' => 'A general answer.']]]]));

        Http::fake([
            'https://api.openai.com/*'     => $openAi,
            'https://www.googleapis.com/*' => Http::response(['error' => ['message' => 'no key']], 500),
            'https://down.test/*'          => Http::response('Server error', 500, ['Content-Type' => 'text/html']),
            'https://*/robots.txt'         => Http::response('', 404),
            'https://*/sitemap.xml'        => Http::response('', 404),
            'https://*/llms.txt'           => Http::response('Not found', 404, ['Content-Type' => 'text/html']),
            'https://rival.test/*'         => Http::response($this->page('Rival — analytics', '<h2>What is Rival?</h2>'), 200, ['Content-Type' => 'text/html']),
            'https://*'                    => Http::response($this->page(), 200, ['Content-Type' => 'text/html']),
        ]);
    }

    private function guest(): self
    {
        return $this->withServerVariables(['REMOTE_ADDR' => self::GUEST_IP]);
    }

    private function scan(array $body): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/mira-premium/api/scans', $body);
    }

    // ------------------------------------------------------------ entitlement

    public function test_entitlement_is_decided_from_the_clients_plan_and_dates(): void
    {
        $g = 'guest:x';

        $this->assertSame(['preview', 'none'], $this->tierOf(null, $g));
        $this->assertSame(['preview', 'none'], $this->tierOf($this->client(), $g));
        $this->assertSame(['trial', 'active_trial'], $this->tierOf($this->client(['trial_ends_at' => now()->addDays(5)]), $g));
        $this->assertSame(['preview', 'expired'], $this->tierOf($this->client(['trial_ends_at' => now()->subDay()]), $g));
        $this->assertSame(['starter', 'active'], $this->tierOf($this->client(['plan' => 'starter']), $g));
        $this->assertSame(['professional', 'active_trial'], $this->tierOf($this->client(['plan' => 'professional', 'trial_ends_at' => now()->addDay()]), $g));
        $this->assertSame(['preview', 'expired'], $this->tierOf($this->client(['plan' => 'professional', 'subscription_ends_at' => now()->subDay()]), $g));
        $this->assertSame(['preview', 'expired'], $this->tierOf($this->client(['plan' => 'enterprise', 'status' => 'suspended']), $g));
    }

    private function tierOf(?Client $c, string $guest): array
    {
        $e = Entitlement::resolve($c, $guest);

        return [$e->tier, $e->status];
    }

    public function test_dev_unlock_gives_everyone_the_tier_except_in_production(): void
    {
        config(['mira_premium.unlock_all' => 'professional']);

        $guest = Entitlement::resolve(null, 'guest:x');
        $this->assertSame(['professional', 'active', true], [$guest->tier, $guest->status, $guest->devUnlock]);
        $this->assertSame('professional', Entitlement::resolve($this->client(), 'guest:x')->tier);

        // Guests still can't own projects.
        $this->guest()->postJson('/mira-premium/api/projects', ['url' => 'acme.test'])
            ->assertStatus(403)->assertJsonPath('error.code', 'NotEntitled');

        $this->app->detectEnvironment(fn () => 'production');
        try {
            $this->assertSame('preview', Entitlement::resolve(null, 'guest:x')->tier);
        } finally {
            // Back to testing, or tearDown's migrate:reset asks for production confirmation.
            $this->app->detectEnvironment(fn () => 'testing');
        }
    }

    public function test_me_reports_subscription_usage_and_caps(): void
    {
        $this->guest()->getJson('/mira-premium/api/me')
            ->assertOk()
            ->assertJsonPath('subscription.tier', 'preview')
            ->assertJsonPath('subscription.premium', false)
            ->assertJsonPath('usage.0.metric', 'scans')
            ->assertJsonPath('usage.0.limit', 2)
            ->assertJsonPath('caps.projects', 0);

        $this->actingAs($this->client(['plan' => 'professional']), 'client')
            ->getJson('/mira-premium/api/me')
            ->assertJsonPath('subscription.tier', 'professional')
            ->assertJsonPath('subscription.status', 'active')
            ->assertJsonPath('caps.projects', 10);
    }

    // ----------------------------------------------------- two-phase scanning

    public function test_preview_scan_runs_and_the_report_is_fetched_with_the_signed_handle(): void
    {
        $submit = $this->guest()->scan(['url' => 'acme.test'])->assertStatus(202)
            ->assertJsonPath('scan.url', 'https://acme.test/')
            ->assertJsonPath('scan.mode', 'preview')
            ->assertJsonPath('reused', false);

        $this->assertNotEmpty($submit->json('handle'));
        $this->assertNotEmpty($submit->json('valid_until'));

        $report = $this->guest()->postJson('/mira-premium/api/scans/report', ['handle' => $submit->json('handle')])
            ->assertOk()
            ->assertJsonPath('scan.status', 'done');

        $this->assertIsInt($report->json('scan.overall'));
        $this->assertArrayHasKey('crawl', $report->json('scan.stage_scores'));
        $this->assertSame('none', $report->json('report.recommendations_source'), 'Preview never gets the AI fixes');
        $this->assertSame(1, MiraUsage::where('metric', 'scans')->value('used'));
    }

    public function test_repeat_scan_of_the_same_site_is_reused_and_not_charged(): void
    {
        $this->guest()->scan(['url' => 'https://acme.test/'])->assertStatus(202);
        $this->guest()->scan(['url' => 'acme.test'])->assertStatus(202)->assertJsonPath('reused', true);

        $this->assertSame(1, MiraScan::count());
        $this->assertSame(1, MiraUsage::where('metric', 'scans')->value('used'));
    }

    public function test_preview_quota_is_enforced_with_a_tagged_error(): void
    {
        $this->guest()->scan(['url' => 'a1.test'])->assertStatus(202);
        $this->guest()->scan(['url' => 'a2.test'])->assertStatus(202);

        $this->guest()->scan(['url' => 'a3.test'])
            ->assertStatus(429)
            ->assertJsonPath('error.code', 'QuotaExceeded')
            ->assertJsonPath('error.metric', 'scans')
            ->assertJsonPath('error.limit', 2);
    }

    public function test_comparison_costs_two_scans_and_preview_gets_no_fixes(): void
    {
        $submit = $this->guest()->scan(['url' => 'acme.test', 'competitor_url' => 'rival.test'])->assertStatus(202);
        $report = $this->guest()->postJson('/mira-premium/api/scans/report', ['handle' => $submit->json('handle')])->assertOk();

        $this->assertSame('comparison', $report->json('scan.kind'));
        $this->assertTrue($report->json('report.locked'));
        foreach ($report->json('report.checks') as $check) {
            $this->assertArrayNotHasKey('recommendation', $check);
        }
        $this->assertSame(2, MiraUsage::where('metric', 'scans')->value('used'));
    }

    public function test_premium_comparison_is_unlocked_and_carries_fixes(): void
    {
        $this->actingAs($this->client(['plan' => 'starter']), 'client');
        $submit = $this->scan(['url' => 'acme.test', 'competitor_url' => 'rival.test'])->assertStatus(202);
        $report = $this->postJson('/mira-premium/api/scans/report', ['handle' => $submit->json('handle')])->assertOk();

        $this->assertFalse($report->json('report.locked'));
        $this->assertNotEmpty(array_filter($report->json('report.checks'), fn ($c) => isset($c['recommendation'])));
    }

    public function test_handles_cannot_be_tampered_with_shared_or_used_after_expiry(): void
    {
        $handle = $this->guest()->scan(['url' => 'acme.test'])->json('handle');
        [$payload, $sig] = explode('.', $handle);

        $forged = rtrim(strtr(base64_encode(json_encode(['s' => 'other', 'u' => 'x', 'e' => time() + 999])), '+/', '-_'), '=') . '.' . $sig;
        $this->guest()->postJson('/mira-premium/api/scans/report', ['handle' => $forged])
            ->assertStatus(401)->assertJsonPath('error.code', 'InvalidSignature');

        // Someone else (another network address) can't read it.
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.9'])
            ->postJson('/mira-premium/api/scans/report', ['handle' => $handle])
            ->assertStatus(401)->assertJsonPath('error.code', 'InvalidSignature');

        $this->travel(2)->hours();
        $this->guest()->postJson('/mira-premium/api/scans/report', ['handle' => $handle])
            ->assertStatus(410)->assertJsonPath('error.code', 'Expired');
    }

    public function test_unreachable_site_fails_cleanly_and_is_refunded(): void
    {
        $handle = $this->guest()->scan(['url' => 'down.test'])->assertStatus(202)->json('handle');

        $this->guest()->postJson('/mira-premium/api/scans/report', ['handle' => $handle])
            ->assertOk()
            ->assertJsonPath('scan.status', 'failed')
            ->assertJsonPath('scan.error.code', 'SiteUnreachable');

        $this->assertSame(0, MiraUsage::where('metric', 'scans')->value('used'));
    }

    public function test_private_and_malformed_addresses_are_refused(): void
    {
        $this->guest()->scan(['url' => 'http://127.0.0.1/admin'])->assertStatus(422)->assertJsonPath('error.code', 'InvalidUrl');
        $this->guest()->scan(['url' => 'ftp://acme.test'])->assertStatus(422)->assertJsonPath('error.code', 'InvalidUrl');
        $this->guest()->scan(['url' => 'not a url'])->assertStatus(422)->assertJsonPath('error.code', 'InvalidUrl');
        $this->assertSame(0, MiraScan::count());
    }

    public function test_hosts_resolving_to_private_addresses_are_refused(): void
    {
        // Set before the first request: routes cache their controller (and its UrlGuard) per test.
        $this->app->instance(UrlGuard::class, new UrlGuard(fn () => ['10.0.0.5']));

        $this->guest()->scan(['url' => 'intranet.test'])->assertStatus(422)->assertJsonPath('error.code', 'InvalidUrl');
        $this->assertSame(0, MiraScan::count());
    }

    public function test_turnstile_is_required_for_preview_when_configured(): void
    {
        config(['mira_premium.turnstile.site_key' => 'site', 'mira_premium.turnstile.secret_key' => 'secret']);
        Http::fake(['https://challenges.cloudflare.com/*' => Http::response(['success' => false])]);

        $this->guest()->scan(['url' => 'acme.test'])->assertStatus(422)->assertJsonPath('error.code', 'InvalidCaptcha');
        $this->guest()->scan(['url' => 'acme.test', 'turnstile_token' => 'bad'])->assertStatus(422)->assertJsonPath('error.code', 'InvalidCaptcha');

        // Subscribers skip the human check.
        $this->actingAs($this->client(['plan' => 'starter']), 'client');
        $this->scan(['url' => 'acme.test'])->assertStatus(202);
    }

    // ---------------------------------------------------------------- projects

    public function test_projects_are_premium_and_capped_by_plan(): void
    {
        $this->guest()->postJson('/mira-premium/api/projects', ['url' => 'acme.test'])
            ->assertStatus(403)->assertJsonPath('error.code', 'NotEntitled');

        $owner = $this->client(['plan' => 'starter']);
        $this->actingAs($owner, 'client');

        $id = $this->postJson('/mira-premium/api/projects', ['url' => 'https://www.acme.test/pricing', 'name' => 'Acme'])
            ->assertStatus(201)->assertJsonPath('project.domain', 'acme.test')->json('project.id');
        $this->postJson('/mira-premium/api/projects', ['url' => 'acme.test'])->assertOk()->assertJsonPath('project.id', $id);
        $this->postJson('/mira-premium/api/projects', ['url' => 'second.test'])->assertStatus(201);
        $this->postJson('/mira-premium/api/projects', ['url' => 'third.test'])->assertStatus(409)->assertJsonPath('error.code', 'LimitReached');

        $this->patchJson("/mira-premium/api/projects/{$id}", ['competitors' => [
            ['name' => 'Rival', 'domain' => 'https://www.rival.test'], ['name' => 'Bb', 'domain' => ''], ['name' => 'Cc', 'domain' => ''], ['name' => 'Dd', 'domain' => ''],
        ]])->assertStatus(409)->assertJsonPath('error.code', 'LimitReached');

        $this->patchJson("/mira-premium/api/projects/{$id}", ['brand_terms' => ['Acme', 'Acme Analytics'], 'competitors' => [['name' => 'Rival', 'domain' => 'https://www.rival.test']]])
            ->assertOk()->assertJsonPath('project.competitors.0.domain', 'rival.test');

        // Another subscriber can't touch it.
        $this->actingAs($this->client(['plan' => 'starter']), 'client')
            ->getJson("/mira-premium/api/projects/{$id}/history")->assertStatus(404);
    }

    public function test_project_history_reports_score_deltas(): void
    {
        $owner = $this->client(['plan' => 'professional']);
        $this->actingAs($owner, 'client');
        $id = $this->postJson('/mira-premium/api/projects', ['url' => 'acme.test'])->json('project.id');

        $this->scan(['url' => 'acme.test', 'project_id' => $id])->assertStatus(202);
        MiraScan::query()->update(['created_at' => now()->subHour()]);   // outside the reuse window
        MiraScan::query()->update(['overall' => 40]);
        $this->scan(['url' => 'acme.test', 'project_id' => $id])->assertStatus(202)->assertJsonPath('reused', false);

        $history = $this->getJson("/mira-premium/api/projects/{$id}/history")->assertOk();
        $this->assertCount(2, $history->json('scans'));
        $this->assertSame($history->json('scans.0.overall') - 40, $history->json('scans.0.delta.overall'));
        $this->assertCount(2, $history->json('timeline'));
    }

    // -------------------------------------------------------------- visibility

    public function test_visibility_engine_parses_mentions_positions_and_citations(): void
    {
        $project = new MiraProject(['name' => 'Acme', 'domain' => 'acme.test', 'brand_terms' => ['Acme', 'Acme Analytics'],
            'competitors' => [['name' => 'Rival', 'domain' => 'rival.test']]]);
        $engine  = app(VisibilityEngine::class);

        $parsed = $engine->parse('Top picks: Rival (https://rival.test/plans), then Acme Analytics — see acme.test or www.example.com. Also g2.com reviews.', $project);

        $this->assertTrue($parsed['brand_mentioned']);
        $this->assertSame(2, $parsed['brand_position']);
        $this->assertSame(['Rival', 'Acme'], $parsed['brands_mentioned']);
        $this->assertTrue($parsed['brand_cited']);
        $this->assertSame(['https://rival.test/plans'], $parsed['cited_urls']);
        $this->assertEqualsCanonicalizing(['rival.test', 'acme.test', 'g2.com'], $parsed['cited_domains']);

        $none = $engine->parse('Try a spreadsheet. Acmeology is unrelated.', $project);
        $this->assertFalse($none['brand_mentioned']);
        $this->assertNull($none['brand_position']);
    }

    public function test_visibility_prompts_and_runs_end_to_end(): void
    {
        $this->actingAs($this->client(['plan' => 'starter']), 'client');
        $id = $this->postJson('/mira-premium/api/projects', ['url' => 'acme.test', 'name' => 'Acme'])->json('project.id');
        $this->patchJson("/mira-premium/api/projects/{$id}", ['competitors' => [['name' => 'Rival', 'domain' => 'rival.test']]]);

        $this->postJson("/mira-premium/api/projects/{$id}/visibility/runs")->assertStatus(422)->assertJsonPath('error.code', 'InvalidInput');

        $this->fakeWeb([json_encode(['prompts' => [
            ['prompt' => 'What is the best customer analytics tool for shops?', 'topic' => 'Discovery'],
            ['prompt' => 'How much does customer analytics software cost?', 'topic' => 'pricing'],
        ]])]);
        $this->postJson("/mira-premium/api/projects/{$id}/prompts/suggest")->assertOk()->assertJsonCount(2, 'added')
            ->assertJsonPath('added.1.topic', 'Pricing');
        $this->postJson("/mira-premium/api/projects/{$id}/prompts", ['prompt' => 'Which analytics tools do agencies trust?', 'topic' => 'Trust'])->assertStatus(201);
        $this->postJson("/mira-premium/api/projects/{$id}/prompts", ['prompt' => 'which analytics tools do agencies trust?'])->assertStatus(422);

        $this->fakeWeb([
            'Acme is a strong choice (https://acme.test). Rival is cheaper.',
            'Rival starts at $20; Acme at $30.',
            'Agencies like Rival and HubSpot.',
        ]);
        $this->postJson("/mira-premium/api/projects/{$id}/visibility/runs")->assertStatus(202);

        $report = $this->getJson("/mira-premium/api/projects/{$id}/visibility")->assertOk();
        $latest = $report->json('report.latest');

        $this->assertSame('done', $latest['status']);
        $this->assertSame(3, $latest['answered_count']);
        $this->assertSame(2, $latest['mentions']);
        $this->assertSame(1, $latest['citations']);
        $this->assertEquals(40.0, $latest['share_of_voice']);    // Acme 2 of 5 tracked-brand mentions
        $this->assertSame(1.5, $latest['summary']['brand']['avg_position']);
        $this->assertSame('you', $latest['summary']['top_domains'][0]['kind']);
        $this->assertTrue($latest['summary']['modeled']);
        $this->assertNull($latest['new_mentions']);

        // Second run: the delta vs the previous cycle.
        $this->fakeWeb(['Rival is best.', 'Rival again.', 'Acme and Rival both work.']);
        $this->postJson("/mira-premium/api/projects/{$id}/visibility/runs")->assertStatus(202);
        $second = $this->getJson("/mira-premium/api/projects/{$id}/visibility")->json('report');
        $this->assertSame(-1, $second['latest']['new_mentions']);
        $this->assertCount(2, $second['timeline']);
    }

    public function test_visibility_is_not_available_in_preview(): void
    {
        $owner = $this->client();   // free plan, no trial
        $project = MiraProject::create(['client_id' => $owner->id, 'name' => 'Acme', 'url' => 'https://acme.test/', 'domain' => 'acme.test']);

        $this->actingAs($owner, 'client')
            ->postJson("/mira-premium/api/projects/{$project->id}/visibility/runs")
            ->assertStatus(403)->assertJsonPath('error.code', 'NotEntitled');
    }

    // --------------------------------------------------------------------- chat

    public function test_chat_questions_are_metered_and_saved_for_subscribers(): void
    {
        config(['mira_premium.plans.preview.limits.chat_messages' => [1, 'day']]);

        // PremiumMiraService resolves DNS itself; stub only its page loading.
        $mira = \Mockery::mock(\App\Services\Llm\PremiumMiraService::class, [new \App\Services\Llm\OpenAiClient('test-key')])->makePartial();
        $mira->shouldReceive('loadWebsite')->andReturnUsing(fn ($url) => [
            'url' => 'https://acme.test/', 'title' => 'Acme',
            'facts' => ['title' => 'Acme', 'h1_count' => 1, 'llms_txt' => false, 'entity_schema' => [], 'meta_description' => '', 'language' => 'en',
                'canonical' => '', 'open_graph' => false, 'meta_robots' => '', 'word_count' => 300, 'question_headings' => 0, 'snippet_ready' => 0,
                'lists' => 0, 'tables' => 0, 'schema_types' => [], 'answer_schema' => [], 'robots_txt' => false, 'crawlers' => [], 'https' => true],
            'context' => 'URL: https://acme.test/',
        ]);
        $this->app->instance(\App\Services\Llm\PremiumMiraService::class, $mira);

        $this->guest()->postJson('/mira-premium/send', ['message' => 'https://acme.test'])->assertOk()->assertJsonPath('site.url', 'https://acme.test/');
        $this->guest()->postJson('/mira-premium/send', ['message' => 'How do I rank?'])->assertOk()->assertJsonPath('usage.1.used', 1);
        $this->guest()->postJson('/mira-premium/send', ['message' => 'And then?'])->assertStatus(429)->assertJsonPath('error.code', 'QuotaExceeded');

        $owner = $this->client(['plan' => 'starter']);
        $this->actingAs($owner, 'client');
        $id = $this->postJson('/mira-premium/api/projects', ['url' => 'acme.test'])->json('project.id');
        $this->postJson('/mira-premium/send', ['message' => 'acme.test', 'project_id' => $id])->assertOk();
        $this->postJson('/mira-premium/send', ['message' => 'What should I fix first?', 'project_id' => $id])->assertOk();

        $this->getJson("/mira-premium/api/projects/{$id}/messages")->assertOk()->assertJsonCount(4, 'messages')
            ->assertJsonPath('messages.2.content', 'What should I fix first?');
    }

    // ------------------------------------------------------ premium flag + misc

    public function test_free_chat_premium_flag_is_ignored_without_a_plan(): void
    {
        // Signed-in clients always get the comparison open, plan or not. A visitor
        // who isn't signed in can't unlock it with premium: true — see
        // AskMiraSignedInReportsTest::test_guest_comparison_is_still_locked.
        $this->actingAs($this->client(), 'client')
            ->postJson('/chat/compare', ['url' => 'https://acme.test/', 'competitor_url' => 'https://rival.test/', 'premium' => true])
            ->assertOk()->assertJsonPath('comparison.locked', false);

        $this->actingAs($this->client(['plan' => 'starter']), 'client')
            ->postJson('/chat/compare', ['url' => 'https://acme.test/', 'competitor_url' => 'https://rival.test/', 'premium' => true])
            ->assertOk()->assertJsonPath('comparison.locked', false);
    }

    public function test_first_party_events_are_allow_listed(): void
    {
        $this->guest()->call('POST', '/mira-premium/api/event', [], [], [], ['CONTENT_TYPE' => 'text/plain'],
            json_encode(['n' => 'scan_submit', 'u' => 'https://x.test/mira-premium', 'p' => ['kind' => 'site']]))->assertNoContent();
        $this->guest()->call('POST', '/mira-premium/api/event', [], [], [], ['CONTENT_TYPE' => 'text/plain'],
            json_encode(['n' => 'drop_table']))->assertNoContent();

        $this->assertSame(['scan_submit'], MiraEvent::pluck('name')->all());
        $this->assertSame('preview', MiraEvent::first()->tier);
    }

    public function test_workspace_page_renders_with_boot_state(): void
    {
        $this->guest()->get('/mira-premium')->assertOk()->assertSee('id="miraBoot"', false);
    }
}
