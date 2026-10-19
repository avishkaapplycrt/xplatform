<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\MiraPremium\MiraProject;
use App\Models\MiraPremium\MiraSeoSnapshot;
use App\Models\MiraPremium\MiraVisibilityAnswer;
use App\Models\MiraPremium\MiraVisibilityRun;
use App\Services\MiraPremium\VisibilityEngine;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Site Explorer reports (performance, positions, countries, pages, competitors,
 * insights, paid, backlinks) from DataForSEO-shaped responses, and the
 * Brand-Radar-style AI report from stored visibility answers.
 */
class MiraPremiumExplorerTest extends TestCase
{
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
        ]]);

        config(['mira_premium.dataforseo.demo' => false, 'mira_premium.unlock_all' => null, 'mira_premium.dataforseo.login' => 'u', 'mira_premium.dataforseo.password' => 'p',
            'mira_premium.dataforseo.sandbox' => true, 'services.openai.api_key' => 'test-key',
            'mira_premium.dataforseo.markets' => [
                ['code' => 2036, 'country' => 'Australia', 'iso' => 'AU', 'language' => 'en'],
                ['code' => 2840, 'country' => 'United States', 'iso' => 'US', 'language' => 'en'],
                ['code' => 2826, 'country' => 'United Kingdom', 'iso' => 'GB', 'language' => 'en'],
            ]]);

        $owner = Client::forceCreate(['name' => 'Ria', 'company_name' => 'Acme', 'email' => 'r@acme.test', 'password' => 'x', 'status' => 'active', 'plan' => 'professional']);
        $this->project = MiraProject::create(['client_id' => $owner->id, 'name' => 'Acme', 'url' => 'https://acme.test/', 'domain' => 'acme.test',
            'brand_terms' => ['Acme'], 'competitors' => [['name' => 'Rival', 'domain' => 'rival.test']]]);
        $this->actingAs($owner, 'client');
        $this->fakeProvider();
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

    private function fakeProvider(): void
    {
        $ok = fn ($r) => Http::response(['status_code' => 20000, 'tasks' => [['status_code' => 20000, 'result' => [$r]]]]);
        $m  = fn (int $ago) => now()->subMonthsNoOverflow($ago);
        $hist = fn ($scale) => array_map(fn ($ago) => ['year' => (int) $m($ago)->format('Y'), 'month' => (int) $m($ago)->format('n'),
            'metrics' => ['organic' => ['count' => 100 * $scale - $ago, 'etv' => 1000 * $scale - $ago * 10, 'estimated_paid_traffic_cost' => 50, 'pos_1' => 2, 'pos_2_3' => 3, 'pos_4_10' => 10, 'pos_11_20' => 5, 'pos_21_30' => 1, 'pos_51_60' => 4],
                'paid' => ['count' => 3, 'etv' => 20]]], [13, 12, 1, 0]);

        Http::fake(function ($request) use ($ok, $hist) {
            $url = $request->url();
            $body = $request->data()[0] ?? [];

            return match (true) {
                str_ends_with($url, 'backlinks/summary/live') => $ok(['rank' => 40, 'backlinks' => 900, 'referring_domains' => 120, 'referring_domains_nofollow' => 20, 'referring_ips' => 95, 'broken_backlinks' => 4,
                    'referring_links_tld' => ['com' => 70, 'au' => 30], 'referring_links_types' => ['anchor' => 800, 'image' => 100]]),
                str_ends_with($url, 'backlinks/history/live') => $ok(['items' => [
                    ['date' => now()->subMonthsNoOverflow(1)->startOfMonth()->toDateString(), 'referring_domains' => 100, 'backlinks' => 700, 'new_referring_domains' => 10, 'lost_referring_domains' => 2],
                    ['date' => now()->startOfMonth()->toDateString(), 'referring_domains' => 120, 'backlinks' => 900, 'new_referring_domains' => 25, 'lost_referring_domains' => 5, 'new_backlinks' => 250, 'lost_backlinks' => 50],
                ]]),
                str_ends_with($url, 'backlinks/referring_domains/live') => $ok(['items' => [
                    ['domain' => 'news.test', 'rank' => 91, 'backlinks' => 3, 'first_seen' => '2024-01-01 00:00:00 +00:00', 'referring_pages' => 3, 'referring_pages_nofollow' => 0],
                    ['domain' => 'blog.test', 'rank' => 35, 'backlinks' => 40, 'referring_pages' => 2, 'referring_pages_nofollow' => 2],
                    ['domain' => 'dir.test', 'rank' => 3, 'backlinks' => 1, 'referring_pages' => 1, 'referring_pages_nofollow' => 0],
                ]]),
                str_ends_with($url, 'backlinks/anchors/live') => $ok(['items' => [['anchor' => 'acme', 'referring_domains' => 30, 'backlinks' => 200], ['anchor' => '', 'referring_domains' => 10, 'backlinks' => 15]]]),
                str_ends_with($url, 'domain_rank_overview/live') => $ok(['items' => [['metrics' => [
                    'organic' => match ($body['location_code'] ?? 0) { 2840 => ['count' => 40, 'etv' => 300], 2826 => ['count' => 10, 'etv' => 100], default => ['count' => 100, 'etv' => 1600.4, 'estimated_paid_traffic_cost' => 80, 'pos_1' => 2, 'pos_2_3' => 3, 'is_new' => 7, 'is_lost' => 1] },
                    'paid' => ['count' => 3, 'etv' => 20]]]]]),
                str_ends_with($url, 'historical_rank_overview/live') => $ok(['items' => $hist(($body['target'] ?? '') === 'rival.test' ? 2 : 1)]),
                str_ends_with($url, 'ranked_keywords/live') && in_array('paid', $body['item_types'] ?? [], true) && count($body['item_types']) === 1 => $ok(['items' => [
                    ['keyword_data' => ['keyword' => 'acme pricing', 'keyword_info' => ['search_volume' => 500, 'cpc' => 4.2]], 'ranked_serp_element' => ['serp_item' => ['type' => 'paid', 'rank_group' => 1, 'title' => 'Acme — Pricing', 'url' => 'https://acme.test/pricing']]],
                ]]),
                str_ends_with($url, 'ranked_keywords/live') => $ok(['items' => [
                    ['keyword_data' => ['keyword' => 'acme login', 'search_intent_info' => ['main_intent' => 'navigational']], 'ranked_serp_element' => ['serp_item' => ['type' => 'organic', 'rank_group' => 1, 'etv' => 400]]],
                    ['keyword_data' => ['keyword' => 'best analytics tool', 'search_intent_info' => ['main_intent' => 'commercial']], 'ranked_serp_element' => ['serp_item' => ['type' => 'organic', 'rank_group' => 3, 'etv' => 300]]],
                    ['keyword_data' => ['keyword' => 'best analytics tool', 'search_intent_info' => ['main_intent' => 'commercial']], 'ranked_serp_element' => ['serp_item' => ['type' => 'featured_snippet', 'rank_group' => 1, 'etv' => 50]]],
                    ['keyword_data' => ['keyword' => 'what is churn', 'search_intent_info' => ['main_intent' => 'informational']], 'ranked_serp_element' => ['serp_item' => ['type' => 'ai_overview_reference', 'rank_group' => 1, 'etv' => 100]]],
                ]]),
                str_ends_with($url, 'relevant_pages/live') => $ok(['items' => [
                    ['page_address' => 'https://acme.test/pricing', 'metrics' => ['organic' => ['etv' => 600, 'count' => 30, 'pos_1' => 1, 'pos_2_3' => 2]]],
                    ['page_address' => 'https://acme.test/', 'metrics' => ['organic' => ['etv' => 400, 'count' => 50]]],
                ]]),
                str_ends_with($url, 'competitors_domain/live') => $ok(['items' => [
                    ['domain' => 'acme.test', 'intersections' => 100],
                    ['domain' => 'www.rival.test', 'intersections' => 40, 'avg_position' => 7.25, 'full_domain_metrics' => ['organic' => ['etv' => 5000, 'count' => 900]]],
                    ['domain' => 'other.test', 'intersections' => 10, 'full_domain_metrics' => ['organic' => ['etv' => 100, 'count' => 50]]],
                ]]),
                default => Http::response(['status_code' => 40400, 'status_message' => 'not faked: ' . $url], 404),
            };
        });
    }

    // ------------------------------------------------------------ Site Explorer

    public function test_performance_merges_search_and_link_history_with_yoy_and_notes(): void
    {
        $inside = now()->subMonthsNoOverflow(2)->toDateString();
        config(['mira_premium.google_updates' => [['date' => '2020-01-01', 'name' => 'Too old'], ['date' => $inside, 'name' => 'Recent core update']]]);
        $d = $this->panel('performance');

        $this->assertCount(4, $d['series']);
        $last = end($d['series']);
        $this->assertSame(1000, $last['organic_traffic']);
        $this->assertSame(120, $last['referring_domains']);
        $this->assertSame(880, $last['last_year']['organic_traffic']);     // 12 months earlier (1000 − 12×10)
        $this->assertSame([['date' => $inside, 'name' => 'Recent core update']], $d['notes']);   // only updates inside the chart's range
    }

    public function test_positions_history_uses_ahrefs_bands(): void
    {
        $d = $this->panel('positions');

        $this->assertSame(['1-3', '4-10', '11-20', '21-50', '51-100'], $d['bands']);
        $this->assertSame(['1-3' => 5, '4-10' => 10, '11-20' => 5, '21-50' => 1, '51-100' => 4], end($d['history'])['positions']);
        $this->assertSame(7, $d['movement']['new']);
    }

    public function test_countries_share_traffic_across_markets(): void
    {
        $d = $this->panel('countries');

        $this->assertSame(2000, $d['total_traffic']);
        $this->assertEquals(['AU', 1600, 80], [$d['countries'][0]['iso'], $d['countries'][0]['traffic'], $d['countries'][0]['share']]);
        $this->assertSame('GB', $d['countries'][2]['iso']);
    }

    public function test_top_pages_carry_ai_citations(): void
    {
        $run = MiraVisibilityRun::create(['uuid' => (string) Str::uuid(), 'project_id' => $this->project->id, 'status' => 'done', 'finished_at' => now()]);
        MiraVisibilityAnswer::create(['run_id' => $run->id, 'prompt' => 'q', 'cited_urls' => ['https://www.acme.test/pricing/', 'https://g2.test/x']]);

        $d = $this->panel('pages');

        $this->assertEquals(['/pricing', 600, 60, 30, 3, 1], [$d['pages'][0]['path'], $d['pages'][0]['traffic'], $d['pages'][0]['share'], $d['pages'][0]['keywords'], $d['pages'][0]['top3'], $d['pages'][0]['ai_citations']]);
        $this->assertSame(0, $d['pages'][1]['ai_citations']);
    }

    public function test_competitors_exclude_self_and_race_tracked_rivals(): void
    {
        $d = $this->panel('competitors');

        $this->assertEquals(['rival.test', 40, 40, 7.3, 5000, true], array_values(array_intersect_key($d['competitors'][0], array_flip(['domain', 'common', 'overlap', 'avg_position', 'traffic', 'tracked']))));
        $this->assertCount(2, $d['competitors']);
        $this->assertSame(['acme.test', 'rival.test'], array_column($d['race'], 'domain'));
        $this->assertSame(2000, end($d['race'][1]['series'])['traffic']);
    }

    public function test_insights_split_intent_branded_and_serp_features(): void
    {
        $d = $this->panel('insights');
        $intent = array_column($d['intent'], null, 'intent');

        $this->assertSame(3, $d['sample']);                                     // the duplicate keyword counts once
        $this->assertSame(['keywords' => 1, 'traffic' => 300], array_intersect_key($intent['commercial'], array_flip(['keywords', 'traffic'])));
        $this->assertEquals(50.0, $intent['navigational']['share']);           // 400 of 800
        $this->assertEquals(['keywords' => 1, 'traffic' => 400, 'share' => 50], $d['branded']['branded']);
        $this->assertSame([1, 1, 0], [$d['serp_features'][0]['owned'], $d['serp_features'][2]['owned'], $d['serp_features'][1]['owned']]);
    }

    public function test_paid_keywords(): void
    {
        $this->assertSame(['keyword' => 'acme pricing', 'volume' => 500, 'cpc' => 4.2, 'position' => 1, 'title' => 'Acme — Pricing', 'url' => 'https://acme.test/pricing'],
            $this->panel('paid')['keywords'][0]);
    }

    public function test_backlinks_profile_distribution_and_anchors(): void
    {
        $d = $this->panel('backlinks');

        $this->assertSame(['referring_domains' => 120, 'followed_domains' => 100], array_intersect_key($d['summary'], array_flip(['referring_domains', 'followed_domains'])));
        $this->assertSame(['followed' => 100, 'nofollow' => 20], $d['follow']);
        $this->assertSame([1, 1, 1], [$d['distribution'][9]['domains'], $d['distribution'][3]['domains'], $d['distribution'][0]['domains']]);
        $this->assertSame('90–100', $d['distribution'][9]['band']);
        $this->assertTrue($d['domains'][1]['nofollow']);
        $this->assertEquals(['(empty / image)', 25], [$d['anchors'][1]['anchor'], $d['anchors'][1]['share']]);
        $this->assertSame(25, end($d['history'])['new_referring_domains']);
    }

    public function test_reports_share_cached_snapshots(): void
    {
        $this->panel('performance');
        $calls = count(Http::recorded());
        $this->panel('positions');
        $this->panel('insights');
        $this->assertSame($calls, count(Http::recorded()), 'positions and insights reuse the search snapshot');
        $this->assertSame(2, MiraSeoSnapshot::count());   // search + authority
    }

    public function test_reports_need_the_provider(): void
    {
        config(['mira_premium.dataforseo.login' => null]);
        foreach (['performance', 'positions', 'countries', 'pages', 'competitors', 'insights', 'paid', 'backlinks'] as $p) {
            $this->assertFalse($this->panel($p)['connected'], $p);
        }
    }

    public function test_sample_data_mode_fills_every_panel_and_is_tagged(): void
    {
        config(['mira_premium.dataforseo.login' => null, 'mira_premium.dataforseo.password' => null, 'mira_premium.dataforseo.demo' => true]);
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake();   // no request may leave in sample mode

        foreach (['authority', 'search', 'performance', 'positions', 'countries', 'pages', 'competitors', 'insights', 'paid', 'backlinks', 'traffic'] as $p) {
            $d = $this->panel($p);
            $this->assertTrue($d['connected'], $p);
            $this->assertTrue($d['demo'] ?? false, "{$p} is tagged as sample data");
        }
        Http::assertNothingSent();

        $t = $this->panel('traffic');
        $this->assertSame(array_sum(array_column($t['series']['current'], 'visits')), $t['metrics']['visits']['value']);   // totals agree with the chart
        $this->assertStringEndsWith('.example', $this->panel('competitors')['competitors'][0]['domain']);                 // invented domains are .example

        // A real login takes over from sample data; production never uses it.
        config(['mira_premium.dataforseo.login' => 'u', 'mira_premium.dataforseo.password' => 'p']);
        $this->assertFalse(app(\App\Services\MiraPremium\DataForSeoClient::class)->isDemo());
        config(['mira_premium.dataforseo.login' => null]);
        $this->app->detectEnvironment(fn () => 'production');
        try {
            $this->assertFalse(app(\App\Services\MiraPremium\DataForSeoClient::class)->isDemo());
        } finally {
            $this->app->detectEnvironment(fn () => 'testing');
        }
    }

    // ------------------------------------------------------------ AI (Brand Radar)

    public function test_ai_brand_report(): void
    {
        $run = fn ($ago, $rows) => tap(MiraVisibilityRun::create(['uuid' => (string) Str::uuid(), 'project_id' => $this->project->id, 'status' => 'done',
            'answered_count' => count($rows), 'mentions' => count(array_filter(array_column($rows, 0))), 'citations' => 1, 'share_of_voice' => 50, 'finished_at' => now()->subDays($ago)]),
            function ($r) use ($rows) {
                foreach ($rows as [$mentioned, $brands, $urls, $sentiment, $found, $topic]) {
                    MiraVisibilityAnswer::create(['run_id' => $r->id, 'prompt' => 'Which tool? ' . Str::random(4), 'topic' => $topic, 'answer' => 'x', 'brand_mentioned' => $mentioned,
                        'brands_mentioned' => $brands, 'cited_urls' => $urls, 'cited_domains' => array_map(fn ($u) => parse_url($u, PHP_URL_HOST), $urls),
                        'sentiment' => $sentiment, 'brands_found' => $found]);
                }
            });

        $run(40, [[true, ['Acme'], [], 'neutral', ['Acme'], 'Pricing']]);
        $run(1, [
            [true, ['Acme', 'Rival'], ['https://g2.test/a', 'https://acme.test/pricing'], 'positive', ['Acme', 'Rival', 'HubSpot'], 'Discovery'],
            [false, ['Rival'], ['https://rival.test'], null, ['Rival', 'HubSpot'], 'Pricing'],
            [true, ['Acme'], ['https://acme.test/pricing'], 'negative', ['Acme'], 'Discovery'],
        ]);

        $d = $this->panel('ai_brand');

        $this->assertTrue($d['has_runs']);
        $this->assertCount(2, $d['monthly']);
        $this->assertSame([1, 2], array_column($d['brands_over_time'][0]['series'], 'mentions'));   // Acme per run
        $this->assertSame([0, 2], array_column($d['brands_over_time'][1]['series'], 'mentions'));   // Rival per run
        $this->assertSame(['url' => 'https://acme.test/pricing', 'cited_in' => 2, 'mentioned_in' => 2, 'avg_position' => 1.5], $d['cited_pages'][0]);
        $this->assertSame(['domain' => 'acme.test', 'answers' => 2, 'kind' => 'you'], $d['cited_domains'][0]);   // cited twice in answers naming Acme
        $this->assertSame('g2.test', $d['cited_domains'][1]['domain']);
        $this->assertSame([['prompt' => $d['gaps'][0]['prompt'], 'topic' => 'Pricing', 'competitors' => ['Rival']]], $d['gaps']);
        $this->assertEquals(['Discovery', 100], [$d['topics'][0]['topic'], $d['topics'][0]['rate']]);
        $this->assertSame(['Acme', 'you'], [$d['most_mentioned'][0]['name'] === 'Acme' ? 'Acme' : $d['most_mentioned'][1]['name'], 'you']);
        $this->assertSame(['positive' => 1, 'neutral' => 0, 'negative' => 1, 'analyzed' => 2], $d['sentiment']);
    }

    public function test_answer_analysis_reads_sentiment_and_brands(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());   // drop the provider fake from setUp
        Http::fake(['https://api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => '{"sentiment":"positive","brands":["Acme","Rival","Acme"]}']]]])]);
        $engine = app(VisibilityEngine::class);

        $this->assertSame(['sentiment' => 'positive', 'brands_found' => ['Acme', 'Rival']], $engine->analyze('Acme is great.', $this->project, true));
        $this->assertNull($engine->analyze('Rival only.', $this->project, false)['sentiment']);

        $parsed = $engine->parse('See https://g2.test/x and https://www.acme.test/p', $this->project);
        $this->assertSame(2, $parsed['cited_position']);
    }
}
