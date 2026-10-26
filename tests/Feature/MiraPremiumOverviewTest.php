<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraSeoSnapshot;
use App\Models\MiraPremium\MiraVisibilityAnswer;
use App\Models\MiraPremium\MiraVisibilityRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Mira Premium overview panels (GET mira-premium/api/projects/{id}/panels/{panel}):
 * traffic from first-party tracking, AI responses from visibility runs, and
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
            'database/migrations/2026_10_19_000001_add_tracking_controls_to_mira_tables.php',
            'database/migrations/2026_10_20_000001_create_mira_google_connections_table.php',
            'database/migrations/2026_10_16_000001_create_mira_seo_snapshots_table.php',
        ]]);

        config(['mira_premium.dataforseo.demo' => false, 'mira_premium.unlock_all' => null, 'mira_premium.dataforseo.login' => null, 'mira_premium.dataforseo.password' => null]);

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

    private function visit(int $siteId, string $when, array $attrs = []): void
    {
        $sid = (string) Str::uuid();
        DB::table('analytics_sessions')->insert(array_merge([
            'site_id' => $siteId, 'session_id' => $sid, 'visitor_id' => 'v-' . $sid, 'pageviews' => 2, 'first_page' => 'https://acme.test/p1',
            'duration_seconds' => 60, 'is_bounce' => 0, 'device_type' => 'desktop', 'started_at' => $when,
        ], $attrs));
        foreach (range(1, $attrs['pageviews'] ?? 2) as $i) {
            DB::table('analytics_pageviews')->insert(['site_id' => $siteId, 'session_id' => $sid, 'visitor_id' => $attrs['visitor_id'] ?? 'v-' . $sid,
                'url' => 'https://acme.test/p' . $i, 'path' => '/p' . $i, 'referrer_domain' => 'google.com', 'created_at' => $when]);
        }
    }

    // ------------------------------------------------------------------ traffic

    public function test_traffic_compares_the_window_with_the_previous_one(): void
    {
        $site  = DB::table('analytics_sites')->insertGetId(['client_id' => $this->owner->id, 'domain' => 'acme.test', 'name' => 'Acme',
            'tracking_id' => 'T1', 'api_key' => 'k1', 'is_active' => 1]);
        $other = Client::forceCreate(['name' => 'X', 'company_name' => 'X', 'email' => 'x@x.test', 'password' => 'x', 'status' => 'active', 'plan' => 'free']);
        $theirs = DB::table('analytics_sites')->insertGetId(['client_id' => $other->id, 'domain' => 'acme.test', 'name' => 'Not yours',
            'tracking_id' => 'T2', 'api_key' => 'k2', 'is_active' => 1]);

        // Current 30 days: 3 visits (2 visitors), 6 views, one bounce. Previous: 1 visit, 2 views.
        $this->visit($site, now()->subDays(2), ['visitor_id' => 'alice', 'duration_seconds' => 90]);
        $this->visit($site, now()->subDays(5), ['visitor_id' => 'alice', 'duration_seconds' => 30]);
        $this->visit($site, now()->subDays(9), ['visitor_id' => 'bob', 'duration_seconds' => 60, 'is_bounce' => 1]);
        $this->visit($site, now()->subDays(40), ['visitor_id' => 'carol']);
        $this->visit($theirs, now()->subDays(1));   // another client's site with the same domain

        $res = $this->panel('traffic')->assertOk()->assertJsonPath('data.connected', true)->assertJsonPath('period.key', '30d');
        $m = $res->json('data.metrics');

        $this->assertSame([3, 1, 2], [$m['visits']['value'], $m['visits']['previous'], $m['visits']['delta']]);
        $this->assertSame(2, $m['unique_visitors']['value']);
        $this->assertSame(6, $m['page_views']['value']);
        $this->assertEquals(2.0, $m['pages_per_visit']['value']);
        $this->assertSame(60, $m['visit_duration']['value']);
        $this->assertEquals(33.3, $m['bounce_rate']['value']);
        $this->assertCount(30, $res->json('data.series.current'));
        $this->assertSame(3, array_sum(array_column($res->json('data.series.current'), 'visits')));
        $this->assertSame('google.com', $res->json('data.sources.0.source'));

        $this->assertCount(13, $this->panel('traffic', '90d')->json('data.series.current'));   // weekly buckets
    }

    public function test_traffic_without_tracking_says_what_to_connect(): void
    {
        $this->panel('traffic')->assertOk()->assertJsonPath('data.connected', false);
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
