<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\WordPressSite;
use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $sites = WordPressSite::where('client_id', auth()->guard('client')->id())
            ->where('is_active', true)
            ->get();

        $totalSites = $sites->count();
        $totalEvents = AnalyticsEvent::whereIn('site_id', $sites->pluck('id'))->count();
        $last24hEvents = AnalyticsEvent::whereIn('site_id', $sites->pluck('id'))
            ->where('synced_at', '>=', Carbon::now()->subDay())
            ->count();

        return view('client.analytics.dashboard', compact('sites', 'totalSites', 'totalEvents', 'last24hEvents'));
    }

    public function show($siteId)
    {
        $site = WordPressSite::where('client_id', auth()->guard('client')->id())
            ->findOrFail($siteId);

        $days = request('days', 30);
        $startDate = Carbon::now()->subDays($days);

        // Traffic metrics
        $traffic = [
            'page_views' => AnalyticsEvent::where('site_id', $site->id)
                ->where('event_type', 'page_view')
                ->where('wp_created_at', '>=', $startDate)
                ->count(),
            'unique_visitors' => AnalyticsEvent::where('site_id', $site->id)
                ->where('event_type', 'page_view')
                ->where('wp_created_at', '>=', $startDate)
                ->distinct('session_id')
                ->count('session_id'),
            'user_logins' => AnalyticsEvent::where('site_id', $site->id)
                ->where('event_type', 'user_login')
                ->where('wp_created_at', '>=', $startDate)
                ->count(),
            'registrations' => AnalyticsEvent::where('site_id', $site->id)
                ->where('event_type', 'user_registration')
                ->where('wp_created_at', '>=', $startDate)
                ->count(),
        ];

        // Content metrics
        $content = [
            'total_posts' => AnalyticsEvent::where('site_id', $site->id)
                ->where('event_type', 'post_publish')
                ->count(),
            'posts_this_month' => AnalyticsEvent::where('site_id', $site->id)
                ->where('event_type', 'post_publish')
                ->where('wp_created_at', '>=', Carbon::now()->subDays(30))
                ->count(),
            'total_comments' => AnalyticsEvent::where('site_id', $site->id)
                ->where('event_type', 'comment')
                ->count(),
        ];

        // Recent events
        $recentEvents = AnalyticsEvent::where('site_id', $site->id)
            ->latest('synced_at')
            ->limit(50)
            ->get();

        // Daily trend (last 30 days)
        $dailyTrend = AnalyticsEvent::where('site_id', $site->id)
            ->where('wp_created_at', '>=', $startDate)
            ->selectRaw('DATE(wp_created_at) as date, event_type, COUNT(*) as count')
            ->groupBy('date', 'event_type')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return view('client.analytics.site-dashboard', compact(
            'site', 'traffic', 'content', 'recentEvents', 'dailyTrend', 'days'
        ));
    }

    public function compare(Request $request)
    {
        $siteIds = $request->input('sites', []);
        $metric = $request->input('metric', 'page_views');
        $days = $request->input('days', 30);

        $sites = WordPressSite::where('client_id', auth()->guard('client')->id())
            ->whereIn('id', $siteIds)
            ->get();

        $comparison = [];
        foreach ($sites as $site) {
            $eventType = match($metric) {
                'page_views' => 'page_view',
                'user_logins' => 'user_login',
                'registrations' => 'user_registration',
                'purchases' => 'purchase',
                default => 'page_view'
            };

            $comparison[$site->site_name] = AnalyticsEvent::where('site_id', $site->id)
                ->where('event_type', $eventType)
                ->where('wp_created_at', '>=', Carbon::now()->subDays($days))
                ->selectRaw('DATE(wp_created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
        }

        return view('client.analytics.compare', compact('sites', 'comparison', 'metric', 'days'));
    }
}