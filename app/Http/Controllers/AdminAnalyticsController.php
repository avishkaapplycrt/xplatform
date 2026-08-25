<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\AnalyticsMetric;
use App\Models\BusinessInsight;
use App\Models\ClientDataSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    public function index()
    {
        // Global metrics across all clients
        $totalMetrics = AnalyticsMetric::count();
        
        // Metrics by type
        $metricsByType = AnalyticsMetric::select('metric_type', DB::raw('count(*) as count'))
            ->groupBy('metric_type')
            ->pluck('count', 'metric_type')
            ->toArray();
        
        // Daily active users trend (last 30 days)
        $dailyActiveUsers = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dailyActiveUsers[] = [
                'date' => $date->format('M d'),
                'value' => AnalyticsMetric::whereDate('measured_at', $date)
                    ->where('metric_type', 'active_users')
                    ->sum('value'),
            ];
        }
        
        // Revenue trend
        $revenueTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenueTrend[] = [
                'date' => $date->format('M d'),
                'value' => AnalyticsMetric::whereDate('measured_at', $date)
                    ->where('metric_type', 'revenue_today')
                    ->sum('value'),
            ];
        }
        
        // Top performing clients by revenue
        $topClientsByRevenue = Client::whereHas('metrics', function ($q) {
                $q->where('metric_type', 'revenue_today')
                  ->where('measured_at', '>=', now()->subDays(7));
            })
            ->withSum(['metrics as weekly_revenue' => function ($q) {
                $q->where('metric_type', 'revenue_today')
                  ->where('measured_at', '>=', now()->subDays(7));
            }], 'value')
            ->orderByDesc('weekly_revenue')
            ->take(10)
            ->get();
        
        // Insights generated over time
        $insightsByCategory = BusinessInsight::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
        
        // Data source health
        $dataSourceHealth = [
            'connected' => ClientDataSource::where('status', 'connected')->count(),
            'failed' => ClientDataSource::where('status', 'failed')->count(),
            'disconnected' => ClientDataSource::where('status', 'disconnected')->count(),
            'pending' => ClientDataSource::where('status', 'pending')->count(),
        ];
        
        // Recent high-impact insights
        $recentInsights = BusinessInsight::with('client')
            ->where('impact_level', 'high')
            ->latest()
            ->take(10)
            ->get();
        
        return view('admin.analytics.index', compact(
            'totalMetrics',
            'metricsByType',
            'dailyActiveUsers',
            'revenueTrend',
            'topClientsByRevenue',
            'insightsByCategory',
            'dataSourceHealth',
            'recentInsights'
        ));
    }
}