<?php

namespace App\Services\MiraPremium;

use App\Models\MiraPremium\MiraProject;
use Illuminate\Support\Facades\DB;

/**
 * The Traffic analytics panel, from the app's own first-party tracking
 * (analytics_sites / analytics_sessions / analytics_pageviews, written by the
 * X Platforms tracking script). A project uses the tracked site whose domain
 * matches and that belongs to the same client — never another client's data.
 *
 *   visits          sessions started in the window
 *   unique visitors distinct visitor ids among those sessions
 *   page views      page views recorded in the window
 *   pages / visit   page views ÷ visits
 *   visit duration  mean session duration (seconds)
 *   bounce rate     % of sessions marked as a bounce
 *
 * Windows are half-open [start, end), so a visit on a boundary counts once.
 * Measured, not estimated. Google Analytics 4 is the alternative source for
 * sites without the script (not connected yet — the panel says so).
 */
class TrafficService
{
    public function traffic(MiraProject $project, Period $period): array
    {
        $site = DB::table('analytics_sites')
            ->where('client_id', $project->client_id)
            ->whereIn('domain', [$project->domain, 'www.' . $project->domain])
            ->first(['id', 'domain', 'is_active', 'created_at']);

        if ($site === null && (bool) config('mira_premium.dataforseo.demo') && !app()->environment('production')) {
            return $this->sample($project, $period);
        }

        if ($site === null) {
            return [
                'connected' => false,
                'source'    => null,
                'message'   => "Visits, visitors and engagement need the X Platforms tracking script on {$project->domain} — or a Google Analytics 4 connection.",
                'setup'     => 'Add the site under Websites → Connect, then install the tracking script.',
            ];
        }

        $cur  = $this->totals($site->id, $period->start, $period->end);
        $prev = $this->totals($site->id, $period->prevStart, $period->start);

        $metrics = [];
        foreach ($cur as $key => $value) {
            $metrics[$key] = ['value' => $value, 'previous' => $prev[$key], 'delta' => Period::delta($value, $prev[$key])];
        }

        return [
            'connected'  => true,
            'source'     => 'tracking_script',
            'site'       => $site->domain,
            'active'     => (bool) $site->is_active,
            'metrics'    => $metrics,
            'series'     => $this->series($site->id, $period),
            'top_pages'  => DB::table('analytics_pageviews')->where('site_id', $site->id)
                ->where('created_at', '>=', $period->start)->where('created_at', '<', $period->end)
                ->selectRaw('path, count(*) as views, count(distinct visitor_id) as visitors')
                ->groupBy('path')->orderByDesc('views')->limit(8)->get()->map(fn ($r) => (array) $r)->all(),
            'sources'    => DB::table('analytics_pageviews')->where('site_id', $site->id)
                ->where('created_at', '>=', $period->start)->where('created_at', '<', $period->end)
                ->selectRaw("coalesce(nullif(utm_source, ''), nullif(referrer_domain, ''), 'Direct') as source, count(distinct session_id) as visits")
                ->groupBy('source')->orderByDesc('visits')->limit(6)->get()->map(fn ($r) => (array) $r)->all(),
            'devices'    => DB::table('analytics_sessions')->where('site_id', $site->id)
                ->where('started_at', '>=', $period->start)->where('started_at', '<', $period->end)
                ->selectRaw("coalesce(nullif(device_type, ''), 'unknown') as device, count(*) as visits")
                ->groupBy('device')->orderByDesc('visits')->get()->map(fn ($r) => (array) $r)->all(),
        ];
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

    private function totals(int $siteId, $from, $to): array
    {
        $s = DB::table('analytics_sessions')->where('site_id', $siteId)->where('started_at', '>=', $from)->where('started_at', '<', $to)
            ->selectRaw('count(*) as visits, count(distinct visitor_id) as visitors, avg(duration_seconds) as duration, avg(case when is_bounce then 1 else 0 end) as bounce')
            ->first();
        $views  = DB::table('analytics_pageviews')->where('site_id', $siteId)->where('created_at', '>=', $from)->where('created_at', '<', $to)->count();
        $visits = (int) $s->visits;

        return [
            'visits'          => $visits,
            'unique_visitors' => (int) $s->visitors,
            'page_views'      => $views,
            'pages_per_visit' => $visits ? round($views / $visits, 2) : null,
            'visit_duration'  => $visits ? (int) round((float) $s->duration) : null,
            'bounce_rate'     => $visits ? round((float) $s->bounce * 100, 1) : null,
        ];
    }

    /**
     * Daily (or weekly for 90d, monthly for 12m) visits and page views for the
     * window and the previous one, aligned bucket by bucket for the chart.
     */
    private function series(int $siteId, Period $period): array
    {
        $bucket = $period->days > 120 ? 'month' : ($period->days > 31 ? 'week' : 'day');
        $step   = ['day' => 1, 'week' => 7, 'month' => 30][$bucket];
        $count  = (int) ceil($period->days / $step);

        $fill = function ($from) use ($siteId, $step, $count) {
            $out = [];
            for ($i = 0; $i < $count; $i++) {
                $a = $from->copy()->addDays($i * $step);
                $b = $a->copy()->addDays($step);
                $out[] = [
                    'date'       => $a->toDateString(),
                    'visits'     => DB::table('analytics_sessions')->where('site_id', $siteId)->where('started_at', '>=', $a)->where('started_at', '<', $b)->count(),
                    'page_views' => DB::table('analytics_pageviews')->where('site_id', $siteId)->where('created_at', '>=', $a)->where('created_at', '<', $b)->count(),
                ];
            }

            return $out;
        };

        return ['bucket' => $bucket, 'current' => $fill($period->start), 'previous' => $fill($period->prevStart)];
    }
}
