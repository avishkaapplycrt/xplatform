<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraSeoSnapshot;
use App\Models\MiraPremium\MiraVisibilityAnswer;
use App\Models\MiraPremium\MiraVisibilityRun;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Mira Premium overview panels (GET mira-premium/api/projects/{id}/panels/{panel}):
 * traffic from Google Analytics 4, AI responses from visibility runs, and
 * Authority / Search from DataForSEO — each for a window vs the previous one.
 */
class MiraPremiumOverviewTest extends TestCase
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
            'database/migrations/2026_10_20_000001_add_ga4_property_to_mira_projects.php',
            'database/migrations/2026_10_16_000001_create_mira_seo_snapshots_table.php',
        ]]);

        config(['mira_premium.dataforseo.demo' => false, 'mira_premium.unlock_all' => null, 'mira_premium.dataforseo.login' => null, 'mira_premium.dataforseo.password' => null,
            'mira_premium.ga4.credentials' => null]);

        $this->owner = Client::forceCreate(['name' => 'Ria', 'company_name' => 'Acme', 'email' => 'r@acme.test',
            'password' => bcrypt('x'), 'status' => 'active', 'plan' => 'professional']);
        $this->project = MiraProject::create(['client_id' => $this->owner->id, 'name' => 'Acme', 'url' => 'https://acme.test/', 'domain' => 'acme.test']);
        $this->actingAs($this->owner, 'client');
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    private function panel(string $panel, string $period = '30d')
    {
        return $this->getJson("/mira-premium/api/projects/{$this->project->id}/panels/{$panel}?period={$period}");
    }

    // ------------------------------------------------------------------ traffic (GA4)

    /** A throwaway service-account key file, so the GA4 client can sign its token request. */
    private function ga4Key(): string
    {
        $opts = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $key  = @openssl_pkey_new($opts) ?: openssl_pkey_new($opts + ['config' => 'C:/xampp/php/extras/ssl/openssl.cnf']);
        openssl_pkey_export($key, $pem, null, is_file('C:/xampp/php/extras/ssl/openssl.cnf') ? ['config' => 'C:/xampp/php/extras/ssl/openssl.cnf'] : []);
        $path = tempnam(sys_get_temp_dir(), 'ga4');
        file_put_contents($path, json_encode(['client_email' => 'mira@xp-test.iam.gserviceaccount.com', 'private_key' => $pem]));
        config(['mira_premium.ga4.credentials' => $path]);

        return $path;
    }

    /** Fakes Google: the token endpoint and runReport, answering by what each report asks for. */
    private function fakeGa4(int $status = 200): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            'https://analyticsdata.googleapis.com/*' => function ($request) use ($status) {
                if ($status !== 200) {
                    return Http::response(['error' => ['message' => 'denied']], $status);
                }
                $body = $request->data();
                $dimension = $body['dimensions'][0]['name'] ?? null;
                $current = $body['dateRanges'][0]['startDate'] === now()->subDays(29)->toDateString();
                $row = fn ($dim, ...$values) => ['dimensionValues' => $dim === null ? [] : [['value' => $dim]],
                    'metricValues' => array_map(fn ($v) => ['value' => (string) $v], $values)];

                return Http::response(['rows' => match ($dimension) {
                    // totals: sessions, totalUsers, screenPageViews, averageSessionDuration, bounceRate (fraction)
                    null           => [$current ? $row(null, 120, 90, 300, 75.4, 0.425) : $row(null, 100, 80, 240, 60, 0.5)],
                    'date'         => [$row(now()->format('Ymd'), $current ? 7 : 3, 15), $row(now()->subDays(2)->format('Ymd'), 5, 9)],
                    'pagePath'     => [$row('/', 140, 70), $row('/pricing', 60, 40)],
                    'sessionSource'=> [$row('google', 80), $row('(direct)', 30)],
                    'deviceCategory' => [$row('desktop', 70), $row('mobile', 50)],
                }]);
            },
        ]);
    }

    public function test_traffic_reads_the_connected_ga4_property(): void
    {
        $this->ga4Key();
        $this->fakeGa4();
        $this->project->update(['ga4_property_id' => '412345678']);

        $res = $this->panel('traffic')->assertOk()->assertJsonPath('data.connected', true)
            ->assertJsonPath('data.source', 'ga4')->assertJsonPath('data.property_id', '412345678');
        $m = $res->json('data.metrics');

        $this->assertSame([120, 100, 20], [$m['visits']['value'], $m['visits']['previous'], $m['visits']['delta']]);
        $this->assertSame(90, $m['unique_visitors']['value']);
        $this->assertSame(300, $m['page_views']['value']);
        $this->assertEquals(2.5, $m['pages_per_visit']['value']);
        $this->assertSame(75, $m['visit_duration']['value']);
        $this->assertEquals(42.5, $m['bounce_rate']['value']);   // GA4 reports a fraction
        $this->assertCount(30, $res->json('data.series.current'));
        $this->assertSame(12, array_sum(array_column($res->json('data.series.current'), 'visits')));
        $this->assertSame(['/', 140, 70], array_values($res->json('data.top_pages.0')));
        $this->assertSame('Direct', $res->json('data.sources.1.source'));   // GA4's "(direct)"
        $this->assertSame('desktop', $res->json('data.devices.0.device'));

        Http::assertSent(fn ($r) => str_contains($r->url(), 'properties/412345678:runReport') && $r->hasHeader('Authorization', 'Bearer tok'));
        $this->assertCount(13, $this->panel('traffic', '90d')->json('data.series.current'));   // weekly buckets
    }

    public function test_traffic_asks_to_connect_a_ga4_property(): void
    {
        $this->ga4Key();
        $this->panel('traffic')->assertOk()->assertJsonPath('data.connected', false)
            ->assertJsonPath('data.ga4.available', true)
            ->assertJsonPath('data.ga4.service_account', 'mira@xp-test.iam.gserviceaccount.com');
    }

    public function test_traffic_without_ga4_set_up_is_not_available(): void
    {
        $this->panel('traffic')->assertOk()->assertJsonPath('data.connected', false)->assertJsonPath('data.ga4.available', false);
    }

    public function test_connecting_ga4_checks_access_before_saving(): void
    {
        $this->ga4Key();
        $url = "/mira-premium/api/projects/{$this->project->id}/ga4";

        $this->putJson($url, ['property_id' => 'abc'])->assertStatus(422)->assertJsonPath('error.code', 'InvalidInput');

        $this->fakeGa4(403);
        $this->putJson($url, ['property_id' => '412345678'])->assertStatus(422)->assertJsonPath('error.code', 'InvalidInput');
        $this->assertNull($this->project->fresh()->ga4_property_id);

        Http::swap(new \Illuminate\Http\Client\Factory());
        $this->fakeGa4();
        $this->putJson($url, ['property_id' => 'properties/412345678'])->assertOk()->assertJsonPath('project.ga4_property_id', '412345678');
        $this->assertSame('412345678', $this->project->fresh()->ga4_property_id);

        $this->deleteJson($url)->assertOk()->assertJsonPath('project.ga4_property_id', null);
        $this->assertNull($this->project->fresh()->ga4_property_id);
    }

    public function test_another_clients_project_cannot_be_connected(): void
    {
        $this->ga4Key();
        $this->fakeGa4();
        $other = Client::forceCreate(['name' => 'X', 'company_name' => 'X', 'email' => 'x@x.test', 'password' => 'x', 'status' => 'active', 'plan' => 'professional']);
        $theirs = MiraProject::create(['client_id' => $other->id, 'name' => 'X', 'url' => 'https://x.test/', 'domain' => 'x.test']);

        $this->putJson("/mira-premium/api/projects/{$theirs->id}/ga4", ['property_id' => '412345678'])->assertStatus(404);
        $this->assertNull($theirs->fresh()->ga4_property_id);
    }

    // ------------------------------------------------------------- AI responses

    public function test_ai_responses_count_mentions_and_own_pages_per_window(): void
    {
        $run = function ($daysAgo, array $answers) {
            $r = MiraVisibilityRun::create(['uuid' => (string) Str::uuid(), 'project_id' => $this->project->id, 'status' => 'done',
                'answered_count' => count($answers), 'mentions' => count(array_filter(array_column($answers, 0))), 'finished_at' => now()->subDays($daysAgo)]);
            foreach ($answers as [$mentioned, $urls]) {
                MiraVisibilityAnswer::create(['run_id' => $r->id, 'prompt' => 'q', 'answer' => 'a', 'brand_mentioned' => $mentioned, 'cited_urls' => $urls]);
            }
        };
        $run(3, [[true, ['https://acme.test/pricing', 'https://g2.com/x']], [true, ['https://www.acme.test/pricing?ref=1', 'https://acme.test/blog']], [false, []]]);
        $run(45, [[true, []], [false, []]]);

        $res = $this->panel('ai')->assertOk();
        $this->assertSame(['value' => 2, 'delta' => 1], $res->json('data.all.responses'));
        $this->assertSame(['value' => 2, 'delta' => 2], $res->json('data.all.pages'));     // /pricing (deduped) + /blog
        $this->assertSame('https://acme.test/pricing', $res->json('data.cited_pages.0.url'));
        $this->assertSame(2, $res->json('data.cited_pages.0.citations'));
        $this->assertEquals(66.7, $res->json('data.all.rate.value'));
        $this->assertTrue($res->json('data.platforms.0.tracked'));
        $this->assertFalse($res->json('data.platforms.1.tracked'));
        $this->assertCount(2, $res->json('data.trend'));
    }

    // --------------------------------------------------------- authority/search

    public function test_provider_panels_say_they_are_not_connected(): void
    {
        $this->panel('authority')->assertOk()->assertJsonPath('data.connected', false)->assertJsonPath('data.provider', 'DataForSEO');
        $this->panel('search')->assertOk()->assertJsonPath('data.connected', false);
    }

    public function test_authority_and_search_parse_dataforseo_and_cache_snapshots(): void
    {
        config(['mira_premium.dataforseo.login' => 'u', 'mira_premium.dataforseo.password' => 'p', 'mira_premium.dataforseo.sandbox' => true]);
        $ok = fn (array $result) => Http::response(['status_code' => 20000, 'tasks' => [['status_code' => 20000, 'result' => [$result]]]]);
        $month = fn (int $ago) => now()->subMonthsNoOverflow($ago)->startOfMonth()->toDateString();

        Http::fake([
            'sandbox.dataforseo.com/v3/backlinks/summary/live' => $ok(['rank' => 22, 'backlinks' => 5400, 'referring_domains' => 571, 'referring_main_domains' => 500, 'broken_backlinks' => 3]),
            'sandbox.dataforseo.com/v3/backlinks/history/live' => $ok(['items' => [
                ['date' => $month(2), 'rank' => 4, 'backlinks' => 700, 'referring_domains' => 158],
                ['date' => $month(1), 'rank' => 10, 'backlinks' => 2000, 'referring_domains' => 300, 'new_backlinks' => 1300, 'lost_backlinks' => 50, 'new_referring_domains' => 150, 'lost_referring_domains' => 8],
                ['date' => $month(0), 'rank' => 22, 'backlinks' => 5400, 'referring_domains' => 571, 'new_backlinks' => 3500, 'lost_backlinks' => 100, 'new_referring_domains' => 280, 'lost_referring_domains' => 9],
            ]]),
            'sandbox.dataforseo.com/v3/dataforseo_labs/google/domain_rank_overview/live' => $ok(['items' => [['metrics' => [
                'organic' => ['count' => 42, 'etv' => 725.4, 'estimated_paid_traffic_cost' => 78.2, 'pos_1' => 3, 'pos_2_3' => 4, 'pos_4_10' => 10, 'pos_11_20' => 5, 'pos_21_30' => 5, 'pos_31_40' => 0, 'pos_41_50' => 5, 'pos_51_60' => 10, 'is_new' => 4, 'is_lost' => 2],
                'paid' => ['count' => 0, 'etv' => 0, 'estimated_paid_traffic_cost' => 0],
            ]]]]),
            'sandbox.dataforseo.com/v3/dataforseo_labs/google/historical_rank_overview/live' => $ok(['items' => [
                ['year' => (int) now()->subMonthsNoOverflow(1)->format('Y'), 'month' => (int) now()->subMonthsNoOverflow(1)->format('n'), 'metrics' => ['organic' => ['count' => 75, 'etv' => 138, 'estimated_paid_traffic_cost' => 10, 'pos_1' => 1, 'pos_2_3' => 1], 'paid' => ['count' => 11, 'etv' => 5]]],
            ]]),
            'sandbox.dataforseo.com/v3/dataforseo_labs/google/ranked_keywords/live' => $ok(['items' => [
                ['keyword_data' => ['keyword' => 'acme analytics', 'keyword_info' => ['search_volume' => 880, 'cpc' => 3.1]],
                 'ranked_serp_element' => ['serp_item' => ['rank_group' => 2, 'etv' => 140.6, 'url' => 'https://acme.test/']]],
            ]]),
        ]);

        $a = $this->panel('authority')->assertOk()->json('data');
        $this->assertTrue($a['connected']);
        $this->assertSame(['value' => 22, 'delta' => 12], $a['score']);            // vs the month the 30-day window starts in
        $this->assertSame(5400 - 2000, $a['backlinks']['delta']);
        $this->assertSame(571, $a['referring_domains']['value']);
        $this->assertSame(3500, $a['backlinks']['new']);
        $this->assertCount(3, $a['history']);

        $s = $this->panel('search')->assertOk()->json('data');
        $this->assertSame(['value' => 42, 'delta' => -33], $s['organic']['keywords']);
        $this->assertSame(725, $s['organic']['traffic']['value']);
        $this->assertSame(['value' => 7, 'delta' => 5], $s['organic']['top3']);
        $this->assertSame(10, $s['organic']['positions'][3]['count']);              // 21–50 band
        $this->assertSame(10, $s['organic']['positions'][4]['count']);              // 51–100 band
        $this->assertSame(-11, $s['paid']['keywords']['delta']);
        $this->assertSame(['keyword' => 'acme analytics', 'volume' => 880, 'cpc' => 3.1, 'position' => 2, 'traffic' => 141, 'url' => 'https://acme.test/'], $s['top_keywords'][0]);

        // Cached: a second load makes no provider calls.
        $calls = count(Http::recorded());
        $this->panel('authority')->assertOk();
        $this->panel('search')->assertOk();
        $this->assertSame($calls, count(Http::recorded()));
        $this->assertSame(2, MiraSeoSnapshot::count());
    }

    public function test_provider_errors_are_tagged(): void
    {
        config(['mira_premium.dataforseo.login' => 'u', 'mira_premium.dataforseo.password' => 'p']);
        Http::fake(['api.dataforseo.com/*' => Http::response(['status_code' => 40100, 'status_message' => 'You are not authorized'], 401)]);

        $this->panel('authority')->assertStatus(503)->assertJsonPath('error.code', 'Unavailable');
    }

    public function test_panels_are_premium_only(): void
    {
        $this->project->update(['client_id' => $this->owner->id]);
        $this->owner->update(['plan' => 'free']);

        $this->panel('traffic')->assertStatus(403)->assertJsonPath('error.code', 'NotEntitled');
    }
}
