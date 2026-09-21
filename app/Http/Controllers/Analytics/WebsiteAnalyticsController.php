<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebsiteAnalyticsController extends Controller
{
    public function index()
    {
        return redirect()->route('client.reports.website.overview');
    }

    public function overview()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $data = [
            'has_data'            => $this->hasWebsiteData($client),
            'total_visitors'      => $this->getTotalVisitors($client, $days),
            'unique_visitors'     => $this->getUniqueVisitors($client, $days),
            'total_pageviews'     => $this->getTotalPageviews($client, $days),
            'avg_session_duration'=> $this->getAvgSessionDuration($client, $days),
            'bounce_rate'         => $this->getBounceRate($client, $days),
            'pages_per_session'   => $this->getPagesPerSession($client, $days),
            'new_vs_returning'    => $this->getNewVsReturning($client, $days),
            'device_breakdown'    => $this->getDeviceBreakdown($client, $days),
            'top_pages'           => $this->getTopPages($client, $days, 10),
            'trend_data'          => $this->getTrendData($client, $days),
        ];

        return view('client.reports.website.overview', compact('data', 'period'));
    }

    public function trafficSources()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $data = [
            'has_data'    => $this->hasWebsiteData($client),
            'sources'     => $this->getTrafficSourcesBreakdown($client, $days),
            'referrers'   => $this->getTopReferrers($client, $days),
        ];

        return view('client.reports.website.traffic-sources', compact('data', 'period'));
    }

    public function pages()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $data = [
            'has_data'     => $this->hasWebsiteData($client),
            'top_pages'    => $this->getTopPages($client, $days, 20),
            'entry_pages'  => $this->getEntryPages($client, $days),
            'exit_pages'   => $this->getExitPages($client, $days),
        ];

        return view('client.reports.website.pages', compact('data', 'period'));
    }

    public function userBehavior()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $data = [
            'has_data' => $this->hasWebsiteData($client),
            'sessions' => $this->getSessionDurationDistribution($client, $days),
        ];

        return view('client.reports.website.user-behavior', compact('data', 'period'));
    }

    public function conversions()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $data = [
            'has_data' => $this->hasWebsiteData($client),
            'total_conversions' => $this->getTotalConversions($client, $days),
            'conversion_rate'   => $this->getConversionRate($client, $days),
        ];

        return view('client.reports.website.conversions', compact('data', 'period'));
    }

    public function realtime()
    {
        $client = Auth::guard('client')->user();

        $data = [
            'active_users' => $this->getActiveUsersRealtime($client),
        ];

        return view('client.reports.website.realtime', compact('data'));
    }

    public function getData(Request $request, string $metric)
    {
        $client = Auth::guard('client')->user();
        $period = $request->get('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        return response()->json([
            'metric' => $metric,
            'data' => $this->getMetricData($client, $metric, $days)
        ]);
    }

    public function export(Request $request, string $format)
    {
        return response()->json(['message' => 'Export not yet implemented']);
    }

    /* ─── Data Methods ─────────────────────────────────────────── */

    private function getDaysFromPeriod(string $period): int
    {
        return match($period) {
            '7d'  => 7,
            '30d' => 30,
            '90d' => 90,
            '1y'  => 365,
            default => 30,
        };
    }

    /**
     * Requires both an active connection AND at least one event — a client
     * with only stale events from a since-disconnected/deleted connection
     * should fall back to the "connect your website" empty state, not keep
     * showing old data forever.
     */
    private function hasWebsiteData($client): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('website_events')) return false;

            $hasActiveConnection = \App\Models\WebsiteConnection::where('client_id', $client->id)
                ->where('status', 'active')
                ->exists();

            if (!$hasActiveConnection) return false;

            return DB::table('website_events')->where('client_id', $client->id)->exists();
        } catch (\Exception $e) { return false; }
    }

    private function getTotalVisitors($client, $days)
    {
        try {
            return DB::table('website_events')
                ->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))
                ->distinct('ip_address')
                ->count('ip_address');
        } catch (\Exception $e) { return 0; }
    }

    private function getUniqueVisitors($client, $days)
    {
        try {
            return DB::table('website_events')
                ->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))
                ->distinct('ip_address')
                ->count('ip_address');
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalPageviews($client, $days)
    {
        try {
            return DB::table('website_events')
                ->where('client_id', $client->id)
                ->where('event_type', 'page_view')
                ->where('created_at', '>=', now()->subDays($days))
                ->count();
        } catch (\Exception $e) { return 0; }
    }

    /**
     * A "session" is approximated as one visitor (ip_address) on one
     * calendar day — website_events has no session_id column, so this is
     * the closest real grouping available from what the tracking script
     * actually sends.
     */
    private function getSessions($client, $days)
    {
        return DB::table('website_events')
            ->select('ip_address', DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as event_count'), DB::raw("SUM(CASE WHEN event_type = 'page_view' THEN 1 ELSE 0 END) as pageview_count"))
            ->where('client_id', $client->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('ip_address')
            ->groupBy('ip_address', DB::raw('DATE(created_at)'))
            ->get();
    }

    private function getAvgSessionDuration($client, $days)
    {
        try {
            $avgSeconds = DB::table('website_events')
                ->where('client_id', $client->id)
                ->where('event_type', 'time_on_page')
                ->where('created_at', '>=', now()->subDays($days))
                ->get()
                ->avg(fn ($row) => json_decode($row->data ?? '{}', true)['duration'] ?? 0);

            $avgSeconds = (int) round($avgSeconds ?? 0);
            return sprintf('%dm %ds', intdiv($avgSeconds, 60), $avgSeconds % 60);
        } catch (\Exception $e) { return '0m 0s'; }
    }

    private function getBounceRate($client, $days)
    {
        try {
            $sessions = $this->getSessions($client, $days);
            if ($sessions->isEmpty()) return 0;
            $bounced = $sessions->where('pageview_count', '<=', 1)->count();
            return round($bounced / $sessions->count() * 100, 1);
        } catch (\Exception $e) { return 0; }
    }

    private function getPagesPerSession($client, $days)
    {
        try {
            $sessions = $this->getSessions($client, $days);
            if ($sessions->isEmpty()) return 0;
            return round($sessions->sum('pageview_count') / $sessions->count(), 1);
        } catch (\Exception $e) { return 0; }
    }

    private function getNewVsReturning($client, $days)
    {
        try {
            $sessions = $this->getSessions($client, $days);
            if ($sessions->isEmpty()) return ['new' => 0, 'returning' => 0];

            $periodStart = now()->subDays($days);
            $ips = $sessions->pluck('ip_address')->unique();

            $firstSeenByIp = DB::table('website_events')
                ->select('ip_address', DB::raw('MIN(created_at) as first_seen'))
                ->where('client_id', $client->id)
                ->whereIn('ip_address', $ips)
                ->groupBy('ip_address')
                ->pluck('first_seen', 'ip_address');

            $newCount = $ips->filter(function ($ip) use ($firstSeenByIp, $periodStart) {
                $firstSeen = $firstSeenByIp[$ip] ?? null;
                return $firstSeen && \Carbon\Carbon::parse($firstSeen)->gte($periodStart);
            })->count();
            $total = $ips->count();
            $newPct = $total > 0 ? round($newCount / $total * 100) : 0;

            return ['new' => $newPct, 'returning' => 100 - $newPct];
        } catch (\Exception $e) { return ['new' => 0, 'returning' => 0]; }
    }

    private function getDeviceBreakdown($client, $days)
    {
        try {
            $rows = DB::table('website_events')
                ->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))
                ->whereNotNull('user_agent')
                ->pluck('user_agent');

            if ($rows->isEmpty()) return ['desktop' => 0, 'mobile' => 0, 'tablet' => 0];

            $counts = ['desktop' => 0, 'mobile' => 0, 'tablet' => 0];
            foreach ($rows as $ua) {
                if (preg_match('/iPad|Tablet/i', $ua)) {
                    $counts['tablet']++;
                } elseif (preg_match('/Mobile|Android|iPhone/i', $ua)) {
                    $counts['mobile']++;
                } else {
                    $counts['desktop']++;
                }
            }

            $total = array_sum($counts);
            return $total > 0 ? array_map(fn ($c) => round($c / $total * 100), $counts) : $counts;
        } catch (\Exception $e) { return ['desktop' => 0, 'mobile' => 0, 'tablet' => 0]; }
    }

    private function getTopPages($client, $days, $limit)
    {
        try {
            return DB::table('website_events')
                ->select('page_url', DB::raw('COUNT(*) as views'))
                ->where('client_id', $client->id)
                ->where('event_type', 'page_view')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereNotNull('page_url')
                ->groupBy('page_url')
                ->orderByDesc('views')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) { return collect(); }
    }

    private function getTrendData($client, $days)
    {
        try {
            return DB::table('website_events')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->where('client_id', $client->id)
                ->where('event_type', 'page_view')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();
        } catch (\Exception $e) { return collect(); }
    }

    /**
     * page_view events carry document.referrer in their JSON data column.
     * Classify each referrer host into a source bucket.
     */
    private function getPageViewReferrers($client, $days)
    {
        return DB::table('website_events')
            ->where('client_id', $client->id)
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('data')
            ->pluck('data')
            ->map(fn ($json) => json_decode($json, true)['referrer'] ?? '')
            ->filter();
    }

    private function classifyReferrer(string $referrer): string
    {
        $host = parse_url($referrer, PHP_URL_HOST) ?? '';
        if ($host === '') return 'Direct';

        $search = ['google.', 'bing.', 'yahoo.', 'duckduckgo.'];
        $social = ['facebook.', 'instagram.', 'twitter.', 'x.com', 'tiktok.', 'linkedin.', 'pinterest.'];

        foreach ($search as $needle) if (str_contains($host, $needle)) return 'Search';
        foreach ($social as $needle) if (str_contains($host, $needle)) return 'Social';

        return 'Referral';
    }

    private function getTrafficSourcesBreakdown($client, $days)
    {
        try {
            $referrers = $this->getPageViewReferrers($client, $days);
            $totalSessions = $this->getSessions($client, $days)->count();
            $directCount = max(0, $totalSessions - $referrers->count());

            $buckets = ['Direct' => $directCount, 'Search' => 0, 'Social' => 0, 'Referral' => 0];
            foreach ($referrers as $ref) {
                $buckets[$this->classifyReferrer($ref)]++;
            }

            return collect($buckets)->filter()->map(fn ($count, $source) => [
                'source' => $source,
                'count'  => $count,
            ])->values();
        } catch (\Exception $e) { return collect(); }
    }

    private function getTopReferrers($client, $days)
    {
        try {
            $hosts = $this->getPageViewReferrers($client, $days)
                ->map(fn ($ref) => parse_url($ref, PHP_URL_HOST) ?? 'Direct')
                ->countBy();

            return $hosts->map(fn ($count, $host) => (object) ['referrer' => $host, 'count' => $count])
                ->sortByDesc('count')
                ->take(10)
                ->values();
        } catch (\Exception $e) { return collect(); }
    }

    private function getEntryPages($client, $days)
    {
        try {
            $firstPerSession = DB::table('website_events')
                ->select('ip_address', DB::raw('DATE(created_at) as day'), DB::raw('MIN(created_at) as first_at'))
                ->where('client_id', $client->id)
                ->where('event_type', 'page_view')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereNotNull('ip_address')
                ->groupBy('ip_address', DB::raw('DATE(created_at)'))
                ->get();

            $pageUrls = [];
            foreach ($firstPerSession as $session) {
                $page = DB::table('website_events')
                    ->where('client_id', $client->id)
                    ->where('ip_address', $session->ip_address)
                    ->where('event_type', 'page_view')
                    ->where('created_at', $session->first_at)
                    ->value('page_url');
                if ($page) $pageUrls[] = $page;
            }

            return collect($pageUrls)->countBy()->sortDesc()->take(10)
                ->map(fn ($views, $url) => (object) ['page_url' => $url, 'views' => $views])->values();
        } catch (\Exception $e) { return collect(); }
    }

    private function getExitPages($client, $days)
    {
        try {
            $lastPerSession = DB::table('website_events')
                ->select('ip_address', DB::raw('DATE(created_at) as day'), DB::raw('MAX(created_at) as last_at'))
                ->where('client_id', $client->id)
                ->where('event_type', 'page_view')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereNotNull('ip_address')
                ->groupBy('ip_address', DB::raw('DATE(created_at)'))
                ->get();

            $pageUrls = [];
            foreach ($lastPerSession as $session) {
                $page = DB::table('website_events')
                    ->where('client_id', $client->id)
                    ->where('ip_address', $session->ip_address)
                    ->where('event_type', 'page_view')
                    ->where('created_at', $session->last_at)
                    ->value('page_url');
                if ($page) $pageUrls[] = $page;
            }

            return collect($pageUrls)->countBy()->sortDesc()->take(10)
                ->map(fn ($views, $url) => (object) ['page_url' => $url, 'views' => $views])->values();
        } catch (\Exception $e) { return collect(); }
    }

    private function getSessionDurationDistribution($client, $days)
    {
        try {
            $durations = DB::table('website_events')
                ->select('ip_address', DB::raw('DATE(created_at) as day'), 'data')
                ->where('client_id', $client->id)
                ->where('event_type', 'time_on_page')
                ->where('created_at', '>=', now()->subDays($days))
                ->get()
                ->groupBy(fn ($r) => $r->ip_address . '|' . $r->day)
                ->map(fn ($rows) => $rows->sum(fn ($r) => json_decode($r->data ?? '{}', true)['duration'] ?? 0));

            if ($durations->isEmpty()) return collect();

            $buckets = ['0-10s' => 0, '10-30s' => 0, '30s-1m' => 0, '1-5m' => 0, '5m+' => 0];
            foreach ($durations as $seconds) {
                if ($seconds < 10) $buckets['0-10s']++;
                elseif ($seconds < 30) $buckets['10-30s']++;
                elseif ($seconds < 60) $buckets['30s-1m']++;
                elseif ($seconds < 300) $buckets['1-5m']++;
                else $buckets['5m+']++;
            }

            return collect($buckets)->map(fn ($count, $bucket) => (object) ['bucket' => $bucket, 'count' => $count])->values();
        } catch (\Exception $e) { return collect(); }
    }

    /**
     * No dedicated "conversion" event exists yet — form_submit is the
     * closest signal the tracking script actually sends.
     */
    private function getTotalConversions($client, $days)
    {
        try {
            return DB::table('website_events')
                ->where('client_id', $client->id)
                ->where('event_type', 'form_submit')
                ->where('created_at', '>=', now()->subDays($days))
                ->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getConversionRate($client, $days)
    {
        try {
            $sessions = $this->getSessions($client, $days)->count();
            if ($sessions === 0) return 0;
            $conversions = $this->getTotalConversions($client, $days);
            return round($conversions / $sessions * 100, 1);
        } catch (\Exception $e) { return 0; }
    }

    private function getActiveUsersRealtime($client)
    {
        try {
            return DB::table('website_events')
                ->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->distinct('ip_address')
                ->count('ip_address');
        } catch (\Exception $e) { return 0; }
    }

    private function getMetricData($client, $metric, $days)
    {
        return match ($metric) {
            'active-users' => $this->getActiveUsersRealtime($client),
            default => [],
        };
    }
}
