<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraPrompt;
use App\Models\MiraPremium\MiraSeoSnapshot;
use App\Models\MiraPremium\MiraUsage;
use App\Models\MiraPremium\MiraVisibilityAnswer;
use App\Models\MiraPremium\MiraVisibilityRun;
use App\Services\MiraPremium\VisibilityEngine;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tracking controls: scheduled runs, prompt keywords / volumes / tags,
 * Estimated impressions, brand match rules, real AI-referred traffic,
 * link-level backlinks, intent and SERP-feature trends, organic share of voice.
 */
class MiraPremiumTrackingTest extends TestCase
{
    private Client $owner;
    private MiraProject $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_03_18_000002_create_clients_table.php',
            'database/migrations/2026_06_17_000001_create_analytics_tables.php',
            'database/migrations/2026_10_15_000001_create_mira_premium_tables.php',
            'database/migrations/2026_10_16_000001_create_mira_seo_snapshots_table.php',
            'database/migrations/2026_10_17_000001_add_insights_to_mira_visibility_answers.php',
            'database/migrations/2026_10_19_000001_add_tracking_controls_to_mira_tables.php',
            'database/migrations/2026_10_20_000001_create_mira_google_connections_table.php',
        ]]);

        config(['mira_premium.dataforseo.demo' => false, 'mira_premium.unlock_all' => null, 'services.openai.api_key' => 'test-key',
            'mira_premium.visibility.analyze_answers' => false, 'mira_premium.dataforseo.login' => null, 'mira_premium.dataforseo.password' => null]);

        $this->owner = Client::forceCreate(['name' => 'Ria', 'company_name' => 'Acme', 'email' => 'r@acme.test', 'password' => 'x', 'status' => 'active', 'plan' => 'professional']);
        $this->project = MiraProject::create(['client_id' => $this->owner->id, 'name' => 'Acme', 'url' => 'https://acme.test/', 'domain' => 'acme.test',
            'brand_terms' => ['Acme'], 'competitors' => [['name' => 'Rival', 'domain' => 'rival.test']]]);
        $this->actingAs($this->owner, 'client');
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    private function panel(string $name)
    {
        return $this->getJson("/mira-premium/api/projects/{$this->project->id}/panels/{$name}")->assertOk()->json('data');
    }

    private function fakeOpenAi(array $replies): void
    {
        Http::swap(new Factory());
        $seq = Http::sequence();
        foreach ($replies as $r) {
            $seq->push(['choices' => [['message' => ['content' => $r]]]]);
        }
        $seq->whenEmpty(Http::response(['choices' => [['message' => ['content' => 'Generic answer.']]]]));
        Http::fake(['https://api.openai.com/*' => $seq, '*' => Http::response([], 404)]);
    }

    // ------------------------------------------------------------- prompts

    public function test_prompt_keywords_and_tags_are_derived_and_cleaned(): void
    {
        $this->assertSame('best crm tools small agencies', VisibilityEngine::keywordFor('What are the best CRM tools for small agencies?'));
        $this->assertSame(['funnel', 'q4 launch'], VisibilityEngine::cleanTags([' Funnel ', 'funnel', 'Q4 launch', 'x']));

        $this->postJson("/mira-premium/api/projects/{$this->project->id}/prompts", ['prompt' => 'Which analytics tool do agencies trust?', 'tags' => ['Trust', 'agency']])
            ->assertStatus(201)->assertJsonPath('prompt.keyword', 'analytics tool agencies trust')->assertJsonPath('prompt.tags', ['trust', 'agency']);

        $id = MiraPrompt::first()->id;
        $this->patchJson("/mira-premium/api/prompts/{$id}", ['keyword' => 'Agency Analytics', 'tags' => ['bottom']])
            ->assertOk()->assertJsonPath('prompt.keyword', 'agency analytics')->assertJsonPath('prompt.tags', ['bottom']);
    }

    public function test_brand_match_rules_case_sensitivity_and_exclusions(): void
    {
        $engine = app(VisibilityEngine::class);
        $apple  = new MiraProject(['name' => 'Apple', 'domain' => 'apple.test', 'brand_terms' => ['Apple'], 'competitors' => []]);

        $this->assertTrue($engine->parse('an apple a day', $apple)['brand_mentioned']);

        $apple->match_mode = 'case_sensitive';
        $this->assertFalse($engine->parse('an apple a day', $apple)['brand_mentioned']);
        $this->assertTrue($engine->parse('Buy from Apple today', $apple)['brand_mentioned']);

        $apple->match_mode = 'word';
        $apple->exclude_terms = ['apple pie'];
        $this->assertFalse($engine->parse('Grandma\'s apple pie recipe', $apple)['brand_mentioned']);
        $this->assertTrue($engine->parse('Apple pie aside, the Apple Watch is great', $apple)['brand_mentioned']);
        $this->assertTrue($engine->parse('See apple.test for details', $apple)['brand_mentioned'], 'domain still counts');
    }

    // ------------------------------------------------- volumes + impressions

    public function test_volumes_are_fetched_in_one_batch_and_weight_impressions(): void
    {
        config(['mira_premium.dataforseo.login' => 'u', 'mira_premium.dataforseo.password' => 'p', 'mira_premium.dataforseo.sandbox' => false]);
        $a = MiraPrompt::create(['project_id' => $this->project->id, 'prompt' => 'Best analytics tool?', 'keyword' => 'best analytics tool']);
        $b = MiraPrompt::create(['project_id' => $this->project->id, 'prompt' => 'Cheapest analytics?', 'keyword' => 'cheap analytics']);

        Http::swap(new Factory());
        Http::fake([
            'api.dataforseo.com/*' => Http::response(['status_code' => 20000, 'tasks' => [['status_code' => 20000, 'result' => [['items' => [
                ['keyword' => 'best analytics tool', 'keyword_info' => ['search_volume' => 900]], ['keyword' => 'cheap analytics', 'keyword_info' => ['search_volume' => 100]],
            ]]]]]]),
            'https://api.openai.com/*' => Http::sequence()
                ->push(['choices' => [['message' => ['content' => 'Acme and Rival are both good.']]]])
                ->push(['choices' => [['message' => ['content' => 'Rival is cheapest.']]]]),
        ]);

        $who = \App\Services\MiraPremium\Entitlement::resolve($this->owner, 'client:' . $this->owner->id);
        $run = app(VisibilityEngine::class)->startRun($who, $this->project, 'manual', true);

        Http::assertSentCount(3);   // one volume batch + two answers
        $this->assertSame(900, $a->fresh()->search_volume);
        $imp = $run->summary['impressions'];
        $this->assertEquals(['brand' => 900, 'possible' => 1000, 'share' => 90], array_intersect_key($imp, array_flip(['brand', 'possible', 'share'])));
        $this->assertSame(1000, $imp['entities'][1]['impressions']);   // Rival in both answers

        // Fresh volumes aren't fetched again.
        app(VisibilityEngine::class)->refreshVolumes($this->project);
        Http::assertSentCount(3);
    }

    // ------------------------------------------------------------ schedule

    public function test_schedule_is_set_through_the_api_and_runs_when_due(): void
    {
        MiraPrompt::create(['project_id' => $this->project->id, 'prompt' => 'Best analytics tool for shops?']);
        $this->patchJson("/mira-premium/api/projects/{$this->project->id}", ['visibility_schedule' => 'weekly'])
            ->assertOk()->assertJsonPath('project.visibility_schedule', 'weekly');
        $this->assertNotNull($this->project->fresh()->next_visibility_run_at);

        $this->fakeOpenAi(['Acme is the best.']);
        $this->artisan('mira:visibility-scheduled')->assertSuccessful();

        $run = MiraVisibilityRun::first();
        $this->assertSame(['done', 'schedule', 1], [$run->status, $run->trigger, $run->mentions]);
        $this->assertTrue($this->project->fresh()->next_visibility_run_at->between(now()->addDays(6), now()->addDays(8)));
        $this->assertSame(1, MiraUsage::where('metric', 'visibility_runs')->value('used'));

        // Not due again yet.
        $this->artisan('mira:visibility-scheduled')->assertSuccessful();
        $this->assertSame(1, MiraVisibilityRun::count());
    }

    public function test_schedule_skips_owners_without_visibility_runs(): void
    {
        $this->owner->update(['plan' => 'free']);
        $this->project->update(['visibility_schedule' => 'daily', 'next_visibility_run_at' => now()->subMinute()]);
        MiraPrompt::create(['project_id' => $this->project->id, 'prompt' => 'Best analytics tool for shops?']);

        $this->artisan('mira:visibility-scheduled')->assertSuccessful();

        $this->assertSame(0, MiraVisibilityRun::count());
        $this->assertTrue($this->project->fresh()->next_visibility_run_at->isFuture());
    }

    // ---------------------------------------------------------- AI traffic

    public function test_ai_traffic_counts_assistant_referred_visits(): void
    {
        $site = DB::table('analytics_sites')->insertGetId(['client_id' => $this->owner->id, 'domain' => 'acme.test', 'name' => 'Acme', 'tracking_id' => 't', 'api_key' => 'k', 'is_active' => 1]);
        $pv = function ($when, $ref, $utm = null, $path = '/pricing') use ($site) {
            $sid = (string) Str::uuid();
            DB::table('analytics_sessions')->insert(['site_id' => $site, 'session_id' => $sid, 'visitor_id' => 'v' . $sid, 'first_page' => $path, 'pageviews' => 1, 'started_at' => $when]);
            DB::table('analytics_pageviews')->insert(['site_id' => $site, 'session_id' => $sid, 'visitor_id' => 'v' . $sid, 'url' => 'u', 'path' => $path, 'referrer_domain' => $ref, 'utm_source' => $utm, 'created_at' => $when]);
        };
        $pv(now()->subDays(2), 'chatgpt.com');
        $pv(now()->subDays(3), 'www.google.com', 'chatgpt.com', '/');      // ChatGPT via utm_source
        $pv(now()->subDays(4), 'www.perplexity.ai');
        $pv(now()->subDays(5), 'google.com');                               // not AI
        $pv(now()->subDays(40), 'chatgpt.com');                             // previous window

        $ai = $this->panel('traffic')['ai_traffic'];

        $this->assertSame(['value' => 3, 'delta' => 2], $ai['visits']);
        $this->assertSame(['platform' => 'ChatGPT', 'visits' => 2, 'delta' => 1], $ai['platforms'][0]);
        $this->assertSame('Perplexity', $ai['platforms'][1]['platform']);
        $this->assertSame(['path' => '/pricing', 'visits' => 2], $ai['pages'][0]);
    }

    // ---------------------------------------------- links, trends, share of voice

    public function test_links_trends_and_share_of_voice(): void
    {
        config(['mira_premium.dataforseo.login' => 'u', 'mira_premium.dataforseo.password' => 'p', 'mira_premium.dataforseo.sandbox' => false]);
        $ok = fn ($r) => Http::response(['status_code' => 20000, 'tasks' => [['status_code' => 20000, 'result' => [$r]]]]);
        $kw = fn ($intent, $type = 'organic') => ['keyword_data' => ['keyword' => Str::random(6), 'search_intent_info' => ['main_intent' => $intent]], 'ranked_serp_element' => ['serp_item' => ['type' => $type, 'etv' => 100]]];
        $hist = fn ($etv) => [['year' => (int) now()->format('Y'), 'month' => (int) now()->format('n'), 'metrics' => ['organic' => ['etv' => $etv, 'count' => 10]]]];

        Http::swap(new Factory());
        Http::fake(function ($req) use ($ok, $kw, $hist) {
            $u = $req->url(); $b = $req->data()[0] ?? [];

            return match (true) {
                str_ends_with($u, 'backlinks/backlinks/live') => $ok(['items' => !empty($b['filters'])
                    ? [['url_from' => 'https://old.test/a', 'url_to' => 'https://acme.test/gone', 'domain_from' => 'old.test', 'domain_from_rank' => 50, 'url_to_status_code' => 404, 'dofollow' => true]]
                    : [['url_from' => 'https://news.test/x', 'page_from_title' => 'Best tools', 'domain_from' => 'news.test', 'domain_from_rank' => 80, 'url_to' => 'https://acme.test/', 'anchor' => 'Acme',
                        'text_pre' => 'Try', 'text_post' => 'today', 'dofollow' => false, 'item_type' => 'anchor']]]),
                str_ends_with($u, 'domain_pages_summary/live') => $ok(['items' => [['url' => 'https://acme.test/pricing', 'referring_domains' => 40, 'backlinks' => 300, 'broken_backlinks' => 2, 'rank' => 30]]]),
                str_ends_with($u, 'ranked_keywords/live') => $ok(['items' => [$kw('commercial'), $kw('informational', 'featured_snippet'), $kw('informational')]]),
                str_ends_with($u, 'domain_rank_overview/live') => $ok(['items' => [['metrics' => ['organic' => ['count' => 3, 'etv' => 300]]]]]),
                str_ends_with($u, 'historical_rank_overview/live') => $ok(['items' => $hist(($b['target'] ?? '') === 'rival.test' ? 300 : 100)]),
                str_ends_with($u, 'competitors_domain/live') => $ok(['items' => []]),
                default => Http::response(['status_code' => 40400], 404),
            };
        });

        $l = $this->panel('links');
        $this->assertSame(['news.test', 80, 'Acme', 'Try', false], [$l['backlinks'][0]['domain'], $l['backlinks'][0]['authority'], $l['backlinks'][0]['anchor'], $l['backlinks'][0]['text_pre'], $l['backlinks'][0]['dofollow']]);
        $this->assertSame([404, 'https://acme.test/gone'], [$l['broken'][0]['status'], $l['broken'][0]['to']]);
        $this->assertSame(['https://acme.test/pricing', 40, 2], [$l['pages'][0]['url'], $l['pages'][0]['referring_domains'], $l['pages'][0]['broken']]);

        // Trends: one point per stored day.
        MiraSeoSnapshot::create(['project_id' => $this->project->id, 'kind' => 'search', 'payload' => ['keywords' => [$kw('transactional')]], 'fetched_at' => now()->subDays(3)]);
        $t = $this->panel('trends');
        $this->assertCount(2, $t['points']);
        $this->assertEquals(100, $t['points'][0]['intent']['transactional']);
        $this->assertEquals(1, end($t['points'])['features']['featured_snippet']);

        // Share of voice: you 100 vs rival 300 -> 25% / 75%.
        $sov = $this->panel('competitors')['share_of_voice'];
        $this->assertSame(['acme.test', 'rival.test'], $sov['domains']);
        $this->assertEquals([25, 75], [$sov['series'][0]['acme.test'], $sov['series'][0]['rival.test']]);
    }

    public function test_sample_data_command_is_flagged_and_purgeable(): void
    {
        $real = MiraPrompt::create(['project_id' => $this->project->id, 'prompt' => 'A real prompt the user wrote?']);

        $this->artisan('mira:sample-data', ['project' => $this->project->id])->assertSuccessful();

        $this->assertSame(8, MiraPrompt::where('source', 'sample')->count());
        $this->assertSame(16, MiraVisibilityRun::where('trigger', 'sample')->where('status', 'done')->count());
        $run = MiraVisibilityRun::where('trigger', 'sample')->latest('finished_at')->first();
        $this->assertSame($run->answers()->where('brand_mentioned', true)->count(), $run->mentions);   // real aggregation, consistent numbers
        $this->assertNotNull($run->summary['impressions']);
        $this->assertTrue($this->panel('ai')['sample']);
        $this->assertTrue($this->panel('ai_brand')['sample']);

        $this->artisan('mira:sample-data', ['project' => $this->project->id, '--purge' => true])->assertSuccessful();
        $this->assertSame(0, MiraVisibilityRun::count());
        $this->assertSame([$real->id], MiraPrompt::pluck('id')->all(), 'real prompts survive the purge');

        $this->app->detectEnvironment(fn () => 'production');
        try {
            $this->artisan('mira:sample-data')->assertFailed();
        } finally {
            $this->app->detectEnvironment(fn () => 'testing');
        }
    }

    public function test_brand_radar_reports_impressions_tags_and_schedule(): void
    {
        $p1 = MiraPrompt::create(['project_id' => $this->project->id, 'prompt' => 'q1', 'tags' => ['pricing'], 'search_volume' => 500]);
        $p2 = MiraPrompt::create(['project_id' => $this->project->id, 'prompt' => 'q2', 'tags' => ['pricing', 'trust'], 'search_volume' => 100]);
        $run = MiraVisibilityRun::create(['uuid' => (string) Str::uuid(), 'project_id' => $this->project->id, 'status' => 'done', 'trigger' => 'schedule', 'answered_count' => 2, 'mentions' => 1,
            'finished_at' => now(), 'summary' => ['impressions' => ['brand' => 500, 'possible' => 600, 'share' => 83.3, 'entities' => []]]]);
        MiraVisibilityAnswer::create(['run_id' => $run->id, 'prompt_id' => $p1->id, 'prompt' => 'q1', 'brand_mentioned' => true, 'brands_mentioned' => ['Acme']]);
        MiraVisibilityAnswer::create(['run_id' => $run->id, 'prompt_id' => $p2->id, 'prompt' => 'q2', 'brand_mentioned' => false, 'brands_mentioned' => []]);
        $this->project->update(['visibility_schedule' => 'monthly', 'next_visibility_run_at' => now()->addMonth()]);

        $d = $this->panel('ai_brand');

        $this->assertSame(83.3, $d['impressions']['share']);
        $this->assertSame(['tag' => 'pricing', 'answers' => 2, 'mentions' => 1, 'rate' => 50], array_map(fn ($v) => is_float($v) ? (int) $v : $v, $d['tags'][0]));
        $this->assertSame(['monthly', 'schedule'], [$d['schedule']['frequency'], $d['schedule']['last_trigger']]);
    }
}
