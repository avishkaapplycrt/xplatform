<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsEngine;
use Illuminate\Support\Facades\Cache;

class ClientDashboardController extends Controller
{
    public function __construct(
        private AnalyticsEngine $analytics
    ) {}

    public function index()
    {
        $client = auth('client')->user();
        $dataSources = $client->activeDataSources;

        if ($dataSources->isEmpty()) {
            return redirect()->route('client.sources.create')
                ->with('info', 'Welcome! Let\'s connect your first data source.');
        }

        $metrics = Cache::remember(
            "client:{$client->id}:dashboard_metrics",
            60,
            function () use ($client, $dataSources) {
                return $this->analytics
                    ->forClient($client)
                    ->fromSources($dataSources)
                    ->getRealtimeMetrics(['active_users', 'revenue_today', 'orders_today']);
            }
        );

        $insights = $client->insights()
            ->where('status', 'new')
            ->where('generated_at', '>=', now()->subDays(7))
            ->take(5)
            ->get();

        $chartData = $this->analytics
            ->forClient($client)
            ->fromSources($dataSources)
            ->getHistoricalData(days: 30, granularity: 'daily');

        return view('client.dashboard', [
            'client' => $client,
            'dataSources' => $dataSources,
            'metrics' => $metrics,
            'insights' => $insights,
            'chartData' => $chartData,
            'wsToken' => $this->generateWebSocketToken($client),
        ]);
    }

    private function generateWebSocketToken($client): string
    {
        return encrypt(json_encode([
            'client_id' => $client->id,
            'expires' => now()->addHours(2)->timestamp,
        ]));
    }
}