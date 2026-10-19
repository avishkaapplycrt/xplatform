<?php

namespace App\Services\MiraPremium;

/**
 * Sample data mode (config mira_premium.dataforseo.demo): DataForSEO-shaped
 * results generated locally, deterministic per target, so every Site Explorer
 * chart can be seen and demoed before a provider account exists. Never used in
 * production; every panel built from it is tagged "Sample data". Invented
 * domains use the reserved .example TLD so nothing reads as a real site.
 */
class DemoSeoData
{
    public static function result(string $path, array $task): array
    {
        $target = (string) ($task['target'] ?? 'example.com');
        mt_srand(crc32($target . '|' . ($task['location_code'] ?? '')));
        $scale = $task['location_code'] ?? 2036;
        $home  = (int) config('mira_premium.dataforseo.location_code', 2036);
        $mult  = $scale === $home ? 1.0 : (mt_rand(8, 45) / 100);
        $brand = ucfirst(explode('.', $target)[0]);

        return match (true) {
            str_ends_with($path, 'backlinks/summary/live')     => self::summary(),
            str_ends_with($path, 'backlinks/history/live')     => ['items' => self::linkHistory()],
            str_ends_with($path, 'backlinks/referring_domains/live') => ['items' => self::referringDomains((int) ($task['limit'] ?? 100))],
            str_ends_with($path, 'backlinks/anchors/live')     => ['items' => self::anchors($brand, $target)],
            str_ends_with($path, 'domain_rank_overview/live')  => ['items' => [['metrics' => self::rankMetrics($mult)]]],
            str_ends_with($path, 'historical_rank_overview/live') => ['items' => self::rankHistory()],
            str_ends_with($path, 'ranked_keywords/live')       => ['items' => self::keywords($brand, $target, $task['item_types'] ?? ['organic'])],
            str_ends_with($path, 'relevant_pages/live')        => ['items' => self::pages($target)],
            str_ends_with($path, 'competitors_domain/live')    => ['items' => self::competitors($target)],
            default => [],
        };
    }

    private static function summary(): array
    {
        $rd = mt_rand(600, 4200);

        return [
            'rank' => mt_rand(38, 82), 'backlinks' => $rd * mt_rand(8, 30), 'referring_domains' => $rd,
            'referring_domains_nofollow' => (int) ($rd * mt_rand(12, 30) / 100), 'referring_main_domains' => (int) ($rd * .9),
            'referring_ips' => (int) ($rd * .82), 'broken_backlinks' => mt_rand(20, 400), 'backlinks_spam_score' => mt_rand(2, 18),
            'first_seen' => '2016-0' . mt_rand(1, 9) . '-12 00:00:00 +00:00',
            'referring_links_tld' => ['com' => (int) ($rd * 4.1), 'au' => (int) ($rd * 1.2), 'org' => (int) ($rd * .6), 'net' => (int) ($rd * .4), 'io' => (int) ($rd * .3), 'edu' => (int) ($rd * .08)],
            'referring_links_types' => ['anchor' => $rd * 9, 'image' => $rd * 2, 'redirect' => (int) ($rd * .4), 'canonical' => (int) ($rd * .1)],
        ];
    }

    private static function linkHistory(): array
    {
        $out = [];
        $rd = mt_rand(300, 1600);
        $bl = $rd * 12;
        $rank = mt_rand(30, 55);
        for ($i = 24; $i >= 0; $i--) {
            $new = mt_rand(20, 140); $lost = mt_rand(5, 60);
            $rd += $new - $lost; $bl += ($new - $lost) * mt_rand(6, 14);
            $rank = min(95, $rank + (mt_rand(0, 10) > 6 ? 1 : 0));
            $out[] = ['date' => now()->subMonthsNoOverflow($i)->startOfMonth()->toDateString(), 'rank' => $rank, 'backlinks' => $bl, 'referring_domains' => $rd,
                'new_referring_domains' => $new, 'lost_referring_domains' => $lost, 'new_backlinks' => $new * mt_rand(5, 12), 'lost_backlinks' => $lost * mt_rand(4, 10)];
        }

        return $out;
    }

    private static function referringDomains(int $limit): array
    {
        $words = ['news', 'review', 'tech', 'daily', 'guide', 'hub', 'journal', 'weekly', 'insider', 'blog', 'forum', 'directory'];
        $out = [];
        for ($i = 0; $i < min($limit, 120); $i++) {
            $pages = mt_rand(1, 6);
            $out[] = ['domain' => $words[$i % 12] . '-' . ($i + 1) . '.example', 'rank' => max(1, 96 - (int) ($i * .78) - mt_rand(0, 6)), 'backlinks' => mt_rand(1, 60),
                'first_seen' => now()->subDays(mt_rand(10, 2400))->format('Y-m-d') . ' 00:00:00 +00:00', 'lost_date' => mt_rand(0, 20) === 0 ? now()->subDays(mt_rand(1, 60))->format('Y-m-d') : null,
                'referring_pages' => $pages, 'referring_pages_nofollow' => mt_rand(0, 4) === 0 ? $pages : 0];
        }

        return $out;
    }

    private static function anchors(string $brand, string $target): array
    {
        $rows = [[$brand, 900], [$target, 420], ["{$brand} app", 260], ['', 180], ['click here', 120], ["https://{$target}/", 110], ["{$brand} pricing", 70], ['this tool', 55], ["{$brand} review", 40], ['website', 30]];

        return array_map(fn ($r) => ['anchor' => $r[0], 'referring_domains' => (int) ($r[1] * mt_rand(80, 120) / 100), 'backlinks' => $r[1] * mt_rand(3, 9),
            'first_seen' => now()->subDays(mt_rand(30, 2000))->format('Y-m-d') . ' 00:00:00 +00:00'], $rows);
    }

    private static function rankMetrics(float $mult): array
    {
        $kw = (int) (mt_rand(2500, 9000) * $mult);
        $bands = ['pos_1' => .03, 'pos_2_3' => .05, 'pos_4_10' => .14, 'pos_11_20' => .16, 'pos_21_30' => .12, 'pos_31_40' => .1, 'pos_41_50' => .09,
            'pos_51_60' => .08, 'pos_61_70' => .07, 'pos_71_80' => .06, 'pos_81_90' => .05, 'pos_91_100' => .05];
        $organic = ['count' => $kw, 'etv' => $kw * mt_rand(9, 24) / 10, 'estimated_paid_traffic_cost' => $kw * mt_rand(12, 40) / 10,
            'is_new' => (int) ($kw * .06), 'is_up' => (int) ($kw * .11), 'is_down' => (int) ($kw * .08), 'is_lost' => (int) ($kw * .04)];
        foreach ($bands as $k => $share) {
            $organic[$k] = (int) round($kw * $share);
        }

        return ['organic' => $organic, 'paid' => ['count' => (int) (mt_rand(20, 180) * $mult), 'etv' => mt_rand(400, 3000) * $mult, 'estimated_paid_traffic_cost' => mt_rand(900, 6000) * $mult]];
    }

    private static function rankHistory(): array
    {
        $out = [];
        $kw = mt_rand(1500, 4000);
        for ($i = 24; $i >= 0; $i--) {
            $kw = (int) ($kw * (1 + mt_rand(-4, 9) / 100));
            $d = now()->subMonthsNoOverflow($i);
            $metrics = self::rankMetrics(1.0);
            $metrics['organic']['count'] = $kw;
            $metrics['organic']['etv'] = $kw * mt_rand(11, 16) / 10;
            foreach (['pos_1' => .03, 'pos_2_3' => .05, 'pos_4_10' => .14, 'pos_11_20' => .16, 'pos_21_30' => .12, 'pos_31_40' => .1, 'pos_41_50' => .09, 'pos_51_60' => .08, 'pos_61_70' => .07, 'pos_71_80' => .06, 'pos_81_90' => .05, 'pos_91_100' => .05] as $k => $s) {
                $metrics['organic'][$k] = (int) round($kw * $s * mt_rand(85, 115) / 100);
            }
            $out[] = ['year' => (int) $d->format('Y'), 'month' => (int) $d->format('n'), 'metrics' => $metrics];
        }

        return $out;
    }

    private static function keywords(string $brand, string $target, array $types): array
    {
        $b = mb_strtolower($brand);
        if ($types === ['paid']) {
            return array_map(fn ($k) => ['keyword_data' => ['keyword' => $k[0], 'keyword_info' => ['search_volume' => $k[1], 'cpc' => $k[2]]],
                'ranked_serp_element' => ['serp_item' => ['type' => 'paid', 'rank_group' => mt_rand(1, 3), 'title' => "{$brand} — {$k[3]}", 'url' => "https://{$target}/" . strtolower(str_replace(' ', '-', $k[3]))]]],
                [["{$b}", 74000, 1.2, 'Official site'], ["{$b} pricing", 6600, 4.8, 'Pricing'], ["{$b} alternative", 2900, 6.1, 'Why teams switch'], ['team chat app', 5400, 7.4, 'Team chat'], ['business messaging', 3600, 8.9, 'For business']]);
        }
        $rows = [
            [$b, 90500, 1, 'navigational', 'organic'], ["{$b} login", 49500, 1, 'navigational', 'organic'], ["{$b} pricing", 8100, 1, 'commercial', 'organic'],
            ['team communication app', 6600, 3, 'commercial', 'organic'], ['best team chat software', 4400, 4, 'commercial', 'organic'], ["what is {$b}", 3600, 1, 'informational', 'featured_snippet'],
            ['how to improve team communication', 2900, 6, 'informational', 'organic'], ['remote work tools', 5400, 8, 'commercial', 'organic'], ["{$b} vs alternatives", 1900, 2, 'commercial', 'organic'],
            ['async communication', 1600, 5, 'informational', 'ai_overview_reference'], ['workflow automation tools', 3300, 11, 'commercial', 'organic'], ["download {$b}", 2400, 1, 'transactional', 'organic'],
            ['secure messaging for business', 1300, 7, 'commercial', 'organic'], ['team collaboration best practices', 1000, 9, 'informational', 'organic'], ['project channels', 880, 3, 'informational', 'local_pack'],
            ["{$b} integrations", 2900, 1, 'navigational', 'organic'], ['buy team chat license', 590, 4, 'transactional', 'organic'], ['internal communication strategy', 1900, 13, 'informational', 'organic'],
        ];

        return array_map(fn ($k) => ['keyword_data' => ['keyword' => $k[0], 'keyword_info' => ['search_volume' => $k[1], 'cpc' => mt_rand(50, 900) / 100],
            'search_intent_info' => ['main_intent' => $k[3]]],
            'ranked_serp_element' => ['serp_item' => ['type' => $k[4], 'rank_group' => $k[2], 'etv' => $k[1] * [1 => .32, 2 => .18, 3 => .11, 4 => .08, 5 => .06, 6 => .05, 7 => .04, 8 => .035, 9 => .03][min(9, $k[2])] ?? .02 * 1,
                'url' => "https://{$target}/"]]], $rows);
    }

    private static function pages(string $target): array
    {
        $paths = ['', 'pricing', 'features', 'blog/team-communication', 'integrations', 'download', 'enterprise', 'help/getting-started', 'blog/remote-work', 'solutions/engineering', 'security', 'customers'];

        return array_map(function ($p, $i) use ($target) {
            $etv = (int) (12000 / ($i + 1.4)) * mt_rand(80, 120) / 100;

            return ['page_address' => "https://{$target}/{$p}", 'metrics' => ['organic' => ['etv' => $etv, 'count' => mt_rand(40, 900),
                'pos_1' => mt_rand(0, 30), 'pos_2_3' => mt_rand(2, 40), 'estimated_paid_traffic_cost' => $etv * mt_rand(8, 30) / 10]]];
        }, $paths, array_keys($paths));
    }

    private static function competitors(string $target): array
    {
        $out = [['domain' => $target, 'intersections' => 5000]];
        foreach (['rival-one', 'rival-two', 'teamtalk', 'chatflow', 'workhub', 'pingly', 'collably', 'huddle-hq', 'threadly', 'opsline'] as $i => $name) {
            $out[] = ['domain' => "{$name}.example", 'intersections' => (int) (1800 / ($i + 1.2)), 'avg_position' => mt_rand(40, 260) / 10,
                'full_domain_metrics' => ['organic' => ['etv' => mt_rand(4000, 900000), 'count' => mt_rand(2000, 90000)]]];
        }

        return $out;
    }
}
