<?php

namespace App\Services\MiraPremium;

use App\Models\MiraPremium\MiraProject;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * The Traffic analytics panel, from the project's Google Analytics 4 property
 * (Ga4Client, Google Analytics Data API). A project reads only the property
 * its owner connected (mira_projects.ga4_property_id), which Mira can read
 * only once the owner has given the service account Viewer access.
 *
 *   visits          GA4 sessions
 *   unique visitors GA4 totalUsers
 *   page views      GA4 screenPageViews
 *   pages / visit   page views ÷ visits
 *   visit duration  GA4 averageSessionDuration (seconds)
 *   bounce rate     GA4 bounceRate (% of sessions that weren't engaged)
 *
 * Windows are whole days ending today (GA4 reports by date): the last N days
 * vs the N before. Reports are cached for config mira_premium.ga4.cache_minutes.
 * Measured, not estimated.
 */
class TrafficService
{
    public function __construct(private ?Ga4Client $ga4 = null)
    {
        $this->ga4 ??= app(Ga4Client::class);
    }

    public function traffic(MiraProject $project, Period $period): array
    {
        $propertyId = (string) $project->ga4_property_id;

        if ($propertyId === '' && (bool) config('mira_premium.dataforseo.demo') && !app()->environment('production')) {
            return $this->sample($project, $period);
        }

        if (!$this->ga4->isConfigured()) {
            return [
                'connected' => false,
                'source'    => null,
                'ga4'       => ['available' => false],
                'message'   => 'Visits, visitors and engagement come from Google Analytics 4, which is not set up on this server.',
                'setup'     => 'Create a Google Cloud service account with the Google Analytics Data API enabled and set GA4_CREDENTIALS to its JSON key file.',
            ];
        }

        if ($propertyId === '') {
            return [
                'connected' => false,
                'source'    => null,
                'ga4'       => ['available' => true, 'service_account' => $this->ga4->serviceAccountEmail()],
                'message'   => "Connect the Google Analytics 4 property for {$project->domain} to see its visits, visitors and engagement.",
            ];
        }

        $minutes = (int) config('mira_premium.ga4.cache_minutes', 60);
        $key = "mira_ga4:{$project->id}:{$propertyId}:{$period->key}:" . now()->toDateString();

        return Cache::remember($key, now()->addMinutes($minutes), fn () => $this->report($project, $propertyId, $period));
    }

    /**
     * Checks Mira can read a GA4 property (a one-day sessions report).
     *
     * @throws MiraApiException InvalidInput with the reason when it can't
     */
    public function verifyProperty(string $propertyId): void
    {
        $this->ga4->runReport($propertyId, [
            'dateRanges' => [['startDate' => 'yesterday', 'endDate' => 'today']],
            'metrics'    => [['name' => 'sessions']],
        ]);
    }

    /** Forgets cached reports for a project (after its property changes). */
    public function forget(MiraProject $project, ?string $propertyId): void
    {
        foreach (array_keys((array) config('mira_premium.periods')) as $periodKey) {
            Cache::forget("mira_ga4:{$project->id}:{$propertyId}:{$periodKey}:" . now()->toDateString());
        }
    }

    private function report(MiraProject $project, string $propertyId, Period $period): array
    {
        [$cur, $prev] = $this->ranges($period);
        $top = (int) config('mira_premium.ga4.top_rows', 8);

        $totals = fn (array $range) => $this->totals($propertyId, $range);
        $c = $totals($cur);
        $p = $totals($prev);
        $metrics = [];
        foreach ($c as $k => $v) {
            $metrics[$k] = ['value' => $v, 'previous' => $p[$k], 'delta' => Period::delta($v, $p[$k])];
        }

        $list = function (string $dimension, array $metricNames, callable $map) use ($propertyId, $cur, $top) {
            $res = $this->ga4->runReport($propertyId, [
                'dateRanges' => [$cur],
                'dimensions' => [['name' => $dimension]],
                'metrics'    => array_map(fn ($m) => ['name' => $m], $metricNames),
                'orderBys'   => [['metric' => ['metricName' => $metricNames[0]], 'desc' => true]],
                'limit'      => $top,
            ]);

            return array_map(fn ($row) => $map(
                $row['dimensionValues'][0]['value'] ?? '',
                array_map(fn ($v) => (float) ($v['value'] ?? 0), $row['metricValues'] ?? [])
            ), $res['rows'] ?? []);
        };

        return [
            'connected'   => true,
            'source'      => 'ga4',
            'site'        => $project->domain,
            'property_id' => $propertyId,
            'active'      => true,
            'metrics'     => $metrics,
            'series'      => $this->series($propertyId, $period, $cur, $prev),
            'top_pages'   => $list('pagePath', ['screenPageViews', 'totalUsers'],
                fn ($path, $m) => ['path' => $path, 'views' => (int) $m[0], 'visitors' => (int) ($m[1] ?? 0)]),
            'sources'     => $list('sessionSource', ['sessions'],
                fn ($source, $m) => ['source' => $source === '(direct)' ? 'Direct' : $source, 'visits' => (int) $m[0]]),
            'devices'     => $list('deviceCategory', ['sessions'],
                fn ($device, $m) => ['device' => $device, 'visits' => (int) $m[0]]),
        ];
    }

    /** [current, previous] GA4 date ranges: the last N whole days ending today, and the N before. */
    private function ranges(Period $period): array
    {
        $today = Carbon::today();
        $start = $today->copy()->subDays($period->days - 1);
        $prevEnd = $start->copy()->subDay();

        return [
            ['startDate' => $start->toDateString(), 'endDate' => $today->toDateString()],
            ['startDate' => $prevEnd->copy()->subDays($period->days - 1)->toDateString(), 'endDate' => $prevEnd->toDateString()],
        ];
    }

    private function totals(string $propertyId, array $range): array
    {
        $res = $this->ga4->runReport($propertyId, [
            'dateRanges' => [$range],
            'metrics'    => array_map(fn ($m) => ['name' => $m], ['sessions', 'totalUsers', 'screenPageViews', 'averageSessionDuration', 'bounceRate']),
        ]);
        $v = array_map(fn ($x) => (float) ($x['value'] ?? 0), $res['rows'][0]['metricValues'] ?? []);
        $visits = (int) ($v[0] ?? 0);
        $views  = (int) ($v[2] ?? 0);

        return [
            'visits'          => $visits,
            'unique_visitors' => (int) ($v[1] ?? 0),
            'page_views'      => $views,
            'pages_per_visit' => $visits ? round($views / $visits, 2) : null,
            'visit_duration'  => $visits ? (int) round($v[3] ?? 0) : null,
            'bounce_rate'     => $visits ? round(($v[4] ?? 0) * 100, 1) : null,
        ];
    }

    /**
     * Daily (weekly for 90d, monthly for 12m) visits and page views for the
     * window and the previous one, aligned bucket by bucket for the chart.
     */
    private function series(string $propertyId, Period $period, array $cur, array $prev): array
    {
        $bucket = $period->days > 120 ? 'month' : ($period->days > 31 ? 'week' : 'day');
        $step   = ['day' => 1, 'week' => 7, 'month' => 30][$bucket];
        $count  = (int) ceil($period->days / $step);

        $fill = function (array $range) use ($propertyId, $step, $count) {
            $res = $this->ga4->runReport($propertyId, [
                'dateRanges' => [$range],
                'dimensions' => [['name' => 'date']],
                'metrics'    => [['name' => 'sessions'], ['name' => 'screenPageViews']],
                'limit'      => 400,
            ]);
            $byDay = [];
            foreach ($res['rows'] ?? [] as $row) {
                $byDay[$row['dimensionValues'][0]['value']] = [(int) $row['metricValues'][0]['value'], (int) $row['metricValues'][1]['value']];
            }
            $from = Carbon::parse($range['startDate']);
            $out = [];
            for ($i = 0; $i < $count; $i++) {
                $a = $from->copy()->addDays($i * $step);
                $visits = $views = 0;
                for ($d = 0; $d < $step; $d++) {
                    [$s, $pv] = $byDay[$a->copy()->addDays($d)->format('Ymd')] ?? [0, 0];
                    $visits += $s;
                    $views  += $pv;
                }
                $out[] = ['date' => $a->toDateString(), 'visits' => $visits, 'page_views' => $views];
            }

            return $out;
        };

        return ['bucket' => $bucket, 'current' => $fill($cur), 'previous' => $fill($prev)];
    }

    /**
     * Sample data mode: generated visits for a project without tracking, tagged
     * demo. Totals are computed from the same generated days, so they agree.
     */
    private function sample(MiraProject $project, Period $period): array
    {
        mt_srand(crc32($project->domain . $period->key));
        $bucket = $period->days > 120 ? 'month' : ($period->days > 31 ? 'week' : 'day');
        $step   = ['day' => 1, 'week' => 7, 'month' => 30][$bucket];
        $n      = (int) ceil($period->days / $step);
        $gen = function ($from, $level) use ($n, $step) {
            $out = [];
            for ($i = 0; $i < $n; $i++) {
                $d = $from->copy()->addDays($i * $step);
                $weekday = in_array($d->dayOfWeek, [0, 6], true) ? .62 : 1;
                $visits = (int) round($level * $step * $weekday * mt_rand(82, 118) / 100);
                $out[] = ['date' => $d->toDateString(), 'visits' => $visits, 'page_views' => (int) round($visits * mt_rand(240, 330) / 100)];
            }

            return $out;
        };
        $cur  = $gen($period->start, mt_rand(380, 520));
        $prev = $gen($period->prevStart, mt_rand(300, 450));
        $tot  = function ($s, $dur, $bounce) {
            $v = array_sum(array_column($s, 'visits')); $pv = array_sum(array_column($s, 'page_views'));

            return ['visits' => $v, 'unique_visitors' => (int) round($v * .71), 'page_views' => $pv, 'pages_per_visit' => $v ? round($pv / $v, 2) : null,
                'visit_duration' => $dur, 'bounce_rate' => $bounce];
        };
        $c = $tot($cur, mt_rand(150, 260), mt_rand(380, 520) / 10);
        $p = $tot($prev, mt_rand(130, 240), mt_rand(400, 560) / 10);
        $metrics = [];
        foreach ($c as $k => $v) {
            $metrics[$k] = ['value' => $v, 'previous' => $p[$k], 'delta' => Period::delta($v, $p[$k])];
        }
        $v = $c['visits'];

        return [
            'connected' => true, 'demo' => true, 'source' => 'sample', 'site' => $project->domain, 'active' => true,
            'metrics' => $metrics, 'series' => ['bucket' => $bucket, 'current' => $cur, 'previous' => $prev],
            'top_pages' => array_map(fn ($path, $share) => ['path' => $path, 'views' => (int) ($c['page_views'] * $share), 'visitors' => (int) ($c['unique_visitors'] * $share * .8)],
                ['/', '/pricing', '/features', '/blog/team-communication', '/integrations', '/download'], [.31, .14, .11, .08, .06, .05]),
            'sources' => array_map(fn ($src, $share) => ['source' => $src, 'visits' => (int) ($v * $share)],
                ['google.com', 'Direct', 'chatgpt.com', 'linkedin.com', 'bing.com', 'perplexity.ai'], [.46, .27, .07, .06, .04, .02]),
            'devices' => [['device' => 'desktop', 'visits' => (int) ($v * .58)], ['device' => 'mobile', 'visits' => (int) ($v * .37)], ['device' => 'tablet', 'visits' => (int) ($v * .05)]],
        ];
    }

}
