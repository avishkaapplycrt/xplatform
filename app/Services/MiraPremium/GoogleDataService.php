<?php

namespace App\Services\MiraPremium;

use App\Models\MiraPremium\MiraGoogleConnection;
use App\Models\MiraPremium\MiraProject;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Measured Google data for a project, through the client's Google connection:
 *
 *   searchConsole()  the Search Console panel — clicks, impressions, CTR and average
 *                    position for the window vs the previous one, the daily trend, and
 *                    top queries, pages, countries and devices. Google's own numbers.
 *   ga4Traffic()     the Traffic panel from Analytics 4, in TrafficService's shape, for
 *                    sites without the tracking script.
 *
 * Windows match Period: [start, end) as calendar dates, so the last day is yesterday.
 * Search Console keeps 16 months, so a 12-month window has no full previous year —
 * the previous values are then null rather than a misleading partial total.
 * Reports are cached per project / property / period (config mira_premium.google.cache_minutes).
 */
class GoogleDataService
{
    public function __construct(private GoogleClient $google) {}

    public function connection(?int $clientId): ?MiraGoogleConnection
    {
        return $clientId ? MiraGoogleConnection::where('client_id', $clientId)->first() : null;
    }

    /** Connection status, and for a project the selectable properties with the best match for its domain. */
    public function status(?int $clientId, ?MiraProject $project): array
    {
        $conn = $this->connection($clientId);
        $out  = [
            'configured' => $this->google->isConfigured(),
            'connected'  => $conn !== null && $conn->status === 'active',
            'status'     => $conn?->status,
            'email'      => $conn?->email,
            'connected_at' => $conn?->connected_at?->toIso8601String(),
            'scopes'     => ['search_console' => (bool) $conn?->hasScope(GoogleClient::SCOPE_GSC), 'analytics' => (bool) $conn?->hasScope(GoogleClient::SCOPE_GA)],
            'connect_url' => route('mira-premium.google.connect', array_filter(['project' => $project?->id])),
        ];
        if ($project === null || !$out['connected']) {
            return $out;
        }

        $out['selected'] = ['gsc_site_url' => $project->gsc_site_url, 'ga4_property' => $project->ga4_property];
        $out['sites'] = $out['properties'] = [];
        $out['errors'] = [];
        if ($out['scopes']['search_console']) {
            try {
                $out['sites'] = $this->google->sites($conn);
            } catch (MiraApiException $e) {
                $out['errors']['search_console'] = $e->getMessage();
            }
        }
        if ($out['scopes']['analytics']) {
            try {
                $out['properties'] = $this->google->properties($conn);
            } catch (MiraApiException $e) {
                $out['errors']['analytics'] = $e->getMessage();
            }
        }
        $out['suggested'] = [
            'gsc_site_url' => self::matchSite(array_column($out['sites'], 'site_url'), $project->domain),
            'ga4_property' => self::matchProperty($out['properties'], $project),
        ];

        return $out;
    }

    // ---------------------------------------------------------- search console

    public function searchConsole(MiraProject $project, Period $period, bool $refresh = false): array
    {
        $conn = $this->connection($project->client_id);

        if ($conn === null && $this->sampleMode()) {
            return $this->sampleSearchConsole($project, $period);
        }
        if (!$this->google->isConfigured()) {
            return ['connected' => false, 'reason' => 'not_configured', 'message' => 'Google sign-in is not set up on this server yet.',
                'setup' => 'Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET and GOOGLE_REDIRECT_URI in .env'];
        }
        if ($conn === null) {
            return ['connected' => false, 'reason' => 'not_connected', 'connect_url' => route('mira-premium.google.connect', ['project' => $project->id]),
                'message' => "Connect Google to see the real clicks, impressions, CTR and positions Google Search Console records for {$project->domain}."];
        }
        if ($conn->status !== 'active' || !$conn->hasScope(GoogleClient::SCOPE_GSC)) {
            return ['connected' => false, 'reason' => 'reconnect', 'email' => $conn->email, 'connect_url' => route('mira-premium.google.connect', ['project' => $project->id]),
                'message' => $conn->status !== 'active' ? 'Google access was revoked. Reconnect to keep Search Console data flowing.' : 'Search Console access was not granted. Reconnect and allow Search Console.'];
        }
        if (!$project->gsc_site_url) {
            return ['connected' => false, 'reason' => 'pick_site', 'email' => $conn->email,
                'message' => "Choose the Search Console property for {$project->domain}."];
        }

        $site = $project->gsc_site_url;
        $key  = 'mira:gsc:' . $project->id . ':' . md5($site) . ':' . $period->key;
        if ($refresh) {
            Cache::forget($key);
        }

        $data = Cache::remember($key, now()->addMinutes((int) config('mira_premium.google.cache_minutes', 360)), function () use ($conn, $site, $period) {
            [$from, $to, $pFrom, $pTo] = self::dates($period);
            $q    = fn (array $body) => $this->google->searchAnalytics($conn, $site, $body + ['dataState' => 'all']);
            $top  = (int) config('mira_premium.google.top_rows', 10);
            $row  = fn (array $r, string $key) => [$key => $r['keys'][0] ?? null, 'clicks' => (int) $r['clicks'], 'impressions' => (int) $r['impressions'],
                'ctr' => round($r['ctr'] * 100, 2), 'position' => round($r['position'], 1)];
            $limit = Carbon::now()->subMonths(16)->toDateString();
            $prevComplete = $pFrom >= $limit;

            $cur  = self::gscTotals($q(['startDate' => $from, 'endDate' => $to])[0] ?? null);
            $prev = $prevComplete ? self::gscTotals($q(['startDate' => $pFrom, 'endDate' => $pTo])[0] ?? null) : array_fill_keys(array_keys($cur), null);
            $metrics = [];
            foreach ($cur as $k => $v) {
                // Lower position is better; the delta stays raw and the page inverts its colour.
                $metrics[$k] = ['value' => $v, 'previous' => $prev[$k], 'delta' => Period::delta($v, $prev[$k])];
            }

            $daily = [];
            foreach ($q(['startDate' => max($pFrom, $limit), 'endDate' => $to, 'dimensions' => ['date'], 'rowLimit' => 1000]) as $r) {
                $daily[$r['keys'][0]] = $r;
            }

            return [
                'connected'     => true,
                'source'        => 'search_console',
                'site'          => $site,
                'metrics'       => $metrics,
                'previous_complete' => $prevComplete,
                'data_through'  => $daily ? max(array_keys($daily)) : null,
                'series'        => self::gscSeries($daily, $period),
                'queries'       => array_map(fn ($r) => $row($r, 'query'), $q(['startDate' => $from, 'endDate' => $to, 'dimensions' => ['query'], 'rowLimit' => $top])),
                'pages'         => array_map(function ($r) use ($row) {
                    $out = $row($r, 'url');
                    $out['path'] = (parse_url((string) $out['url'], PHP_URL_PATH) ?: '/') . (($qs = parse_url((string) $out['url'], PHP_URL_QUERY)) ? '?' . $qs : '');

                    return $out;
                }, $q(['startDate' => $from, 'endDate' => $to, 'dimensions' => ['page'], 'rowLimit' => $top])),
                'countries'     => array_map(fn ($r) => $row($r, 'country'), $q(['startDate' => $from, 'endDate' => $to, 'dimensions' => ['country'], 'rowLimit' => $top])),
                'devices'       => array_map(fn ($r) => $row($r, 'device'), $q(['startDate' => $from, 'endDate' => $to, 'dimensions' => ['device']])),
                'fetched_at'    => now()->toIso8601String(),
            ];
        });

        return $data + ['email' => $conn->email];
    }

    // ------------------------------------------------------------- analytics 4

    /** Traffic panel data from GA4, or null when this project has no usable GA4 property. */
    public function ga4Traffic(MiraProject $project, Period $period, bool $refresh = false): ?array
    {
        if (!$project->ga4_property || !$this->google->isConfigured() || ($conn = $this->connection($project->client_id)) === null) {
            return null;
        }
        if ($conn->status !== 'active' || !$conn->hasScope(GoogleClient::SCOPE_GA)) {
            return ['connected' => false, 'source' => 'ga4', 'reason' => 'reconnect', 'connect_url' => route('mira-premium.google.connect', ['project' => $project->id]),
                'message' => 'Google Analytics access was revoked or not granted. Reconnect Google to see traffic.'];
        }

        $property = $project->ga4_property;
        $key = 'mira:ga4:' . $project->id . ':' . md5($property) . ':' . $period->key;
        if ($refresh) {
            Cache::forget($key);
        }

        try {
            return Cache::remember($key, now()->addMinutes((int) config('mira_premium.google.cache_minutes', 360)), fn () => $this->fetchGa4($conn, $property, $period));
        } catch (MiraApiException $e) {
            return ['connected' => false, 'source' => 'ga4', 'reason' => 'error', 'message' => $e->getMessage(),
                'setup' => 'Check that the Google Analytics Data API is enabled for the OAuth project and that this Google account can read the property.'];
        }
    }

    private function fetchGa4(MiraGoogleConnection $conn, string $property, Period $period): array
    {
        [$from, $to, $pFrom, $pTo] = self::dates($period);
        $report = fn (array $body) => $this->google->runReport($conn, $property, $body);
        $range  = fn ($a, $b) => ['dateRanges' => [['startDate' => $a, 'endDate' => $b]]];

        $totalsMetrics = ['sessions', 'totalUsers', 'screenPageViews', 'screenPageViewsPerSession', 'averageSessionDuration', 'bounceRate'];
        $tot = function ($a, $b) use ($report, $range, $totalsMetrics) {
            $r = $report($range($a, $b) + ['metrics' => array_map(fn ($m) => ['name' => $m], $totalsMetrics)])[0] ?? [];
            $visits = (int) ($r['sessions'] ?? 0);

            return [
                'visits'          => $visits,
                'unique_visitors' => (int) ($r['totalUsers'] ?? 0),
                'page_views'      => (int) ($r['screenPageViews'] ?? 0),
                'pages_per_visit' => $visits ? round((float) $r['screenPageViewsPerSession'], 2) : null,
                'visit_duration'  => $visits ? (int) round((float) $r['averageSessionDuration']) : null,
                'bounce_rate'     => $visits ? round((float) $r['bounceRate'] * 100, 1) : null,
            ];
        };
        $cur  = $tot($from, $to);
        $prev = $tot($pFrom, $pTo);
        $metrics = [];
        foreach ($cur as $k => $v) {
            $metrics[$k] = ['value' => $v, 'previous' => $prev[$k], 'delta' => Period::delta($v, $prev[$k])];
        }

        $daily = [];
        foreach ($report($range($pFrom, $to) + ['dimensions' => [['name' => 'date']], 'metrics' => [['name' => 'sessions'], ['name' => 'screenPageViews']], 'limit' => 1000]) as $r) {
            $d = $r['date'];
            $daily[substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2)] = ['visits' => (int) $r['sessions'], 'page_views' => (int) $r['screenPageViews']];
        }
        $sum = function (array $days) {
            return ['visits' => array_sum(array_column($days, 'visits')), 'page_views' => array_sum(array_column($days, 'page_views'))];
        };

        $top = fn (string $dim, string $metric, int $limit, array $extra = []) => $report($range($from, $to) + [
            'dimensions' => [['name' => $dim]], 'metrics' => array_map(fn ($m) => ['name' => $m], array_merge([$metric], $extra)),
            'orderBys' => [['metric' => ['metricName' => $metric], 'desc' => true]], 'limit' => $limit,
        ]);

        // AI assistants as GA4 records them: sessionSource is the referrer host or utm_source.
        $map   = (array) config('mira_premium.ai_referrers');
        $hosts = array_merge(array_keys($map), array_map(fn ($h) => 'www.' . $h, array_keys($map)));
        $ai = function ($a, $b) use ($report, $range, $map) {
            $out = [];
            foreach ($report($range($a, $b) + ['dimensions' => [['name' => 'sessionSource']], 'metrics' => [['name' => 'sessions']], 'limit' => 500]) as $r) {
                $name = $map[preg_replace('/^www\./', '', strtolower((string) $r['sessionSource']))] ?? null;
                if ($name !== null) {
                    $out[$name] = ($out[$name] ?? 0) + (int) $r['sessions'];
                }
            }

            return $out;
        };
        $aiCur  = $ai($from, $to);
        $aiPrev = $ai($pFrom, $pTo);
        $platforms = [];
        foreach (array_unique(array_merge(array_keys($aiCur), array_keys($aiPrev))) as $name) {
            $platforms[] = ['platform' => $name, 'visits' => $aiCur[$name] ?? 0, 'delta' => ($aiCur[$name] ?? 0) - ($aiPrev[$name] ?? 0)];
        }
        usort($platforms, fn ($a, $b) => $b['visits'] <=> $a['visits']);
        $aiPages = $aiCur ? $report($range($from, $to) + [
            'dimensions' => [['name' => 'landingPage']], 'metrics' => [['name' => 'sessions']], 'limit' => 8,
            'dimensionFilter' => ['filter' => ['fieldName' => 'sessionSource', 'inListFilter' => ['values' => $hosts, 'caseSensitive' => false]]],
            'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
        ]) : [];

        return [
            'connected'  => true,
            'source'     => 'ga4',
            'site'       => $property,
            'active'     => true,
            'metrics'    => $metrics,
            'series'     => self::bucketed($daily, $period, $sum, fn () => ['visits' => 0, 'page_views' => 0]),
            'top_pages'  => array_map(fn ($r) => ['path' => $r['pagePath'], 'views' => (int) $r['screenPageViews'], 'visitors' => (int) $r['totalUsers']], $top('pagePath', 'screenPageViews', 8, ['totalUsers'])),
            'sources'    => array_map(fn ($r) => ['source' => $r['sessionSource'] === '(direct)' ? 'Direct' : $r['sessionSource'], 'visits' => (int) $r['sessions']], $top('sessionSource', 'sessions', 6)),
            'devices'    => array_map(fn ($r) => ['device' => $r['deviceCategory'], 'visits' => (int) $r['sessions']], $top('deviceCategory', 'sessions', 5)),
            'ai_traffic' => [
                'visits'    => ['value' => array_sum($aiCur), 'delta' => array_sum($aiCur) - array_sum($aiPrev)],
                'platforms' => $platforms,
                'pages'     => array_map(fn ($r) => ['path' => $r['landingPage'], 'visits' => (int) $r['sessions']], $aiPages),
            ],
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    // ----------------------------------------------------------------- helpers

    /** The window and the previous one as inclusive Y-m-d dates: [from, to, prevFrom, prevTo]. */
    public static function dates(Period $period): array
    {
        return [
            $period->start->toDateString(), $period->end->copy()->subDay()->toDateString(),
            $period->prevStart->toDateString(), $period->start->copy()->subDay()->toDateString(),
        ];
    }

    /** Best Search Console property for a domain: the Domain property, then the https / www URL-prefix ones. */
    public static function matchSite(array $siteUrls, string $domain): ?string
    {
        $domain = strtolower(preg_replace('/^www\./i', '', $domain));
        foreach (["sc-domain:{$domain}", "https://{$domain}/", "https://www.{$domain}/", "http://{$domain}/", "http://www.{$domain}/"] as $candidate) {
            if (in_array($candidate, $siteUrls, true)) {
                return $candidate;
            }
        }

        return null;
    }

    /** A GA4 property whose name mentions the domain or the project name, when exactly one does. */
    public static function matchProperty(array $properties, MiraProject $project): ?string
    {
        $domain = strtolower(preg_replace('/^www\./i', '', $project->domain));
        $needles = array_filter([$domain, strtolower(explode('.', $domain)[0]), strtolower(trim($project->name))], fn ($n) => mb_strlen($n) >= 3);
        foreach ($needles as $needle) {
            $hits = array_values(array_filter($properties, fn ($p) => str_contains(strtolower($p['name']), $needle)));
            if (count($hits) === 1) {
                return $hits[0]['property'];
            }
        }

        return count($properties) === 1 ? $properties[0]['property'] : null;
    }

    private static function gscTotals(?array $r): array
    {
        return [
            'clicks'      => $r ? (int) $r['clicks'] : 0,
            'impressions' => $r ? (int) $r['impressions'] : 0,
            'ctr'         => $r && $r['impressions'] ? round($r['ctr'] * 100, 2) : null,
            'position'    => $r && $r['impressions'] ? round($r['position'], 1) : null,
        ];
    }

    /** Daily rows bucketed like the Traffic chart; CTR and position recomputed per bucket (position weighted by impressions). */
    private static function gscSeries(array $daily, Period $period): array
    {
        return self::bucketed($daily, $period, function (array $days) {
            $clicks = array_sum(array_column($days, 'clicks'));
            $impr   = array_sum(array_column($days, 'impressions'));
            $posW   = array_sum(array_map(fn ($d) => $d['position'] * $d['impressions'], $days));

            return ['clicks' => (int) $clicks, 'impressions' => (int) $impr, 'ctr' => $impr ? round($clicks / $impr * 100, 2) : null, 'position' => $impr ? round($posW / $impr, 1) : null];
        }, fn () => ['clicks' => 0, 'impressions' => 0, 'ctr' => null, 'position' => null]);
    }

    /**
     * Current and previous windows as aligned buckets (day ≤31d, week ≤120d, month beyond),
     * each bucket the $sum of its days; buckets with no data get $empty().
     */
    private static function bucketed(array $daily, Period $period, callable $sum, callable $empty): array
    {
        $bucket = $period->days > 120 ? 'month' : ($period->days > 31 ? 'week' : 'day');
        $step   = ['day' => 1, 'week' => 7, 'month' => 30][$bucket];
        $count  = (int) ceil($period->days / $step);

        $fill = function (Carbon $from) use ($daily, $step, $count, $sum, $empty) {
            $out = [];
            for ($i = 0; $i < $count; $i++) {
                $a = $from->copy()->startOfDay()->addDays($i * $step);
                $days = [];
                for ($d = 0; $d < $step; $d++) {
                    $key = $a->copy()->addDays($d)->toDateString();
                    if (isset($daily[$key])) {
                        $days[] = $daily[$key];
                    }
                }
                $out[] = ['date' => $a->toDateString()] + ($days ? $sum($days) : $empty());
            }

            return $out;
        };

        return ['bucket' => $bucket, 'current' => $fill($period->start), 'previous' => $fill($period->prevStart)];
    }

    private function sampleMode(): bool
    {
        return (bool) config('mira_premium.dataforseo.demo') && !app()->environment('production');
    }

    /** Sample data mode: generated Search Console numbers, tagged demo, with the Connect link kept. */
    private function sampleSearchConsole(MiraProject $project, Period $period): array
    {
        mt_srand(crc32('gsc' . $project->domain . $period->key));
        $level = mt_rand(140, 260);
        $daily = [];
        for ($d = $period->prevStart->copy()->startOfDay(); $d->lt($period->end); $d->addDay()) {
            $grow = $d->gte($period->start) ? 1.12 : 1;
            $weekday = in_array($d->dayOfWeek, [0, 6], true) ? .6 : 1;
            $impr = (int) round($level * 28 * $weekday * $grow * mt_rand(85, 115) / 100);
            $clicks = (int) round($impr * mt_rand(32, 46) / 1000);
            $daily[$d->toDateString()] = ['clicks' => $clicks, 'impressions' => $impr, 'position' => mt_rand(118, 168) / 10];
        }
        [$from, $to, $pFrom, $pTo] = self::dates($period);
        $tot = function ($a, $b) use ($daily) {
            $days = array_filter($daily, fn ($k) => $k >= $a && $k <= $b, ARRAY_FILTER_USE_KEY);
            $c = array_sum(array_column($days, 'clicks')); $i = array_sum(array_column($days, 'impressions'));

            return ['clicks' => $c, 'impressions' => $i, 'ctr' => $i ? round($c / $i * 100, 2) : null,
                'position' => $i ? round(array_sum(array_map(fn ($d) => $d['position'] * $d['impressions'], $days)) / $i, 1) : null];
        };
        $cur = $tot($from, $to);
        $prev = $tot($pFrom, $pTo);
        $metrics = [];
        foreach ($cur as $k => $v) {
            $metrics[$k] = ['value' => $v, 'previous' => $prev[$k], 'delta' => Period::delta($v, $prev[$k])];
        }
        $share = fn (array $names, array $shares, string $key) => array_map(fn ($n, $s) => [$key => $n, 'clicks' => (int) ($cur['clicks'] * $s),
            'impressions' => (int) ($cur['impressions'] * $s * mt_rand(70, 140) / 100), 'ctr' => mt_rand(15, 90) / 10, 'position' => mt_rand(15, 220) / 10], $names, $shares);
        $brand = strtolower($project->brandTerms()[0]);

        return [
            'connected' => true, 'demo' => true, 'source' => 'sample', 'site' => 'sc-domain:' . $project->domain, 'metrics' => $metrics, 'previous_complete' => true,
            'data_through' => $to, 'series' => self::gscSeries($daily, $period),
            'queries' => $share([$brand, $brand . ' login', 'best team chat app', $brand . ' pricing', 'team messaging app', 'slack alternatives', 'business chat software', $brand . ' vs teams'],
                [.22, .09, .06, .05, .04, .035, .03, .025], 'query'),
            'pages' => array_map(fn ($r) => $r + ['path' => parse_url($r['url'], PHP_URL_PATH)], $share(array_map(fn ($p) => "https://{$project->domain}{$p}", ['/', '/pricing', '/features', '/blog/team-communication', '/integrations', '/download']),
                [.34, .14, .1, .07, .05, .04], 'url')),
            'countries' => $share(['aus', 'usa', 'gbr', 'nzl', 'ind', 'can'], [.48, .21, .09, .06, .05, .04], 'country'),
            'devices' => $share(['DESKTOP', 'MOBILE', 'TABLET'], [.57, .4, .03], 'device'),
            'connect_url' => route('mira-premium.google.connect', ['project' => $project->id]),
            'fetched_at' => now()->toIso8601String(),
        ];
    }
}
