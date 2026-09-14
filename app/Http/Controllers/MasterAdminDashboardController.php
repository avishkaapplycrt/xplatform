<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientDataSource;
use App\Models\AnalyticsMetric;
use App\Models\BusinessInsight;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MasterAdminDashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('admin:global_stats', 300, function () {
            return [
                'total_clients' => Client::count(),
                'active_clients' => Client::where('status', 'active')->count(),
                'pending_approval' => Client::where('status', 'pending')->count(),
                'suspended_clients' => Client::where('status', 'suspended')->count(),
                
                'data_sources' => [
                    'total' => ClientDataSource::count(),
                    'connected' => ClientDataSource::where('status', 'connected')->count(),
                    'failed' => ClientDataSource::where('status', 'failed')->count(),
                ],
                
                'metrics_tracked' => AnalyticsMetric::count(),
                'insights_generated' => BusinessInsight::count(),
                
                'revenue_estimate' => Client::where('plan', '!=', 'free')
                    ->where('status', 'active')
                    ->sum(DB::raw("CASE plan 
                        WHEN 'starter' THEN 29 
                        WHEN 'professional' THEN 99 
                        WHEN 'enterprise' THEN 299 
                        ELSE 0 
                    END")),
            ];
        });

        // Weekly growth data for chart
        $weeklyData = $this->getWeeklyGrowthData();
        
        // Plan distribution
        $planDistribution = Client::select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan')
            ->toArray();

        // Recent clients with rich data
        $recentClients = Client::withCount(['dataSources as connected_sources' => function ($q) {
                $q->where('status', 'connected');
            }])
            ->withCount('insights')
            ->withSum('insights as new_insights', DB::raw('CASE WHEN status = "new" THEN 1 ELSE 0 END'))
            ->latest()
            ->take(10)
            ->get();

        // Recent activity feed
        $recentActivity = $this->getRecentActivity();

        // System health metrics
        $health = [
            'database_size_mb' => round(DB::select('SELECT SUM(data_length + index_length) / 1024 / 1024 
                FROM information_schema.tables 
                WHERE table_schema = ?', [config('database.database')])[0]->size ?? 0, 2),
            'queue_size' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'avg_response_time' => '45ms', // Mock value
            'uptime' => '99.9%', // Mock value
        ];

        // Top performing clients
        $topClients = Client::withCount(['metrics' => function ($q) {
                $q->where('measured_at', '>=', now()->subDays(7));
            }])
            ->withMax('metrics as last_revenue', 'value')
            ->where('status', 'active')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'weeklyData',
            'planDistribution',
            'recentClients',
            'recentActivity',
            'health',
            'topClients'
        ));
    }

    private function getWeeklyGrowthData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $data[] = [
                'day' => $date->format('D'),
                'date' => $date->format('M d'),
                'new_clients' => Client::whereDate('created_at', $date)->count(),
                'active_users' => AnalyticsMetric::whereDate('measured_at', $date)
                    ->where('metric_type', 'active_users')
                    ->sum('value'),
            ];
        }
        return $data;
    }

    private function getRecentActivity(): array
    {
        $activities = [];
        
        // Recent client registrations
        $recentClients = Client::latest()->take(5)->get();
        foreach ($recentClients as $client) {
            $activities[] = [
                'type' => 'client_registered',
                'message' => "New client {$client->company_name} registered",
                'time' => $client->created_at,
                'icon' => 'user-plus',
                'color' => 'blue',
            ];
        }

        // Recent data source connections
        $recentSources = ClientDataSource::where('status', 'connected')
            ->whereNotNull('connected_at')
            ->latest('connected_at')
            ->take(3)
            ->get();
        foreach ($recentSources as $source) {
            $activities[] = [
                'type' => 'source_connected',
                'message' => "{$source->client->company_name} connected {$source->connection_name}",
                'time' => $source->connected_at,
                'icon' => 'database',
                'color' => 'green',
            ];
        }

        // Recent insights
        $recentInsights = BusinessInsight::whereNotNull('generated_at')->latest()->take(3)->get();
        foreach ($recentInsights as $insight) {
            $activities[] = [
                'type' => 'insight_generated',
                'message' => "New {$insight->category} insight for {$insight->client->company_name}",
                'time' => $insight->generated_at,
                'icon' => 'lightbulb',
                'color' => 'purple',
            ];
        }

        // Sort by time and take top 8
        usort($activities, fn($a, $b) => $b['time'] <=> $a['time']);
        return array_slice($activities, 0, 8);
    }
}