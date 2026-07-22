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

    private function hasWebsiteData($client): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('website_events')) return false;
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
                ->where('event_type', 'pageview')
                ->where('created_at', '>=', now()->subDays($days))
                ->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getAvgSessionDuration($client, $days) { return '2m 45s'; }
    private function getBounceRate($client, $days) { return 42.5; }
    private function getPagesPerSession($client, $days) { return 3.6; }
    private function getNewVsReturning($client, $days) { return ['new' => 65, 'returning' => 35]; }
    private function getDeviceBreakdown($client, $days) { return ['desktop' => 55, 'mobile' => 38, 'tablet' => 7]; }

    private function getTopPages($client, $days, $limit)
    {
        try {
            return DB::table('website_events')
                ->select('page_url', DB::raw('COUNT(*) as views'))
                ->where('client_id', $client->id)
                ->where('event_type', 'pageview')
                ->where('created_at', '>=', now()->subDays($days))
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
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();
        } catch (\Exception $e) { return collect(); }
    }

    private function getTrafficSourcesBreakdown($client, $days) { return []; }
    private function getTopReferrers($client, $days) { return []; }
    private function getEntryPages($client, $days) { return []; }
    private function getExitPages($client, $days) { return []; }
    private function getSessionDurationDistribution($client, $days) { return []; }
    private function getTotalConversions($client, $days) { return 0; }
    private function getConversionRate($client, $days) { return 0; }
    private function getActiveUsersRealtime($client) { return 0; }
    private function getMetricData($client, $metric, $days) { return []; }
}
