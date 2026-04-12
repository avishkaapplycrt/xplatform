<?php

namespace App\Http\Controllers;

use App\Models\AnalysisLayer;
use App\Models\Client;
use App\Models\ClientLayerStat;

class ClientOverviewController extends Controller
{
    public function show()
    {
        $client = auth('client')->user()->load([
            'industry',
            'analysisLayers',
            'selectedSources',
            'actions',
            'analyticsConfig.microSignals',
            'latestPlatformMetric',
        ]);

        // All layers for the sidebar and data flow table
        $allLayers = AnalysisLayer::where('is_active', true)->orderBy('sort_order')->get();

        // Total micro-signals from the analytics config relationship (null-safe)
        $totalMicroSignals = $client->analyticsConfig
            ? $client->analyticsConfig->microSignals->count()
            : 0;

        // Latest layer stats for this client, one row per layer
        $latestDate = ClientLayerStat::where('client_id', $client->id)->max('recorded_date');

        $layerStats = $latestDate
            ? ClientLayerStat::where('client_id', $client->id)
                ->where('recorded_date', $latestDate)
                ->get()
                ->keyBy('analysis_layer_id')
            : collect();

        // Platform-wide tenant counts
        $activeTenants    = Client::where('status', 'active')->count();
        $addedThisQuarter = Client::where('status', 'active')
            ->where('created_at', '>=', now()->startOfQuarter())
            ->count();

        // All active clients for the bottom mini-cards
        $allClients = Client::with('analysisLayers')
            ->where('status', 'active')
            ->orderBy('company_name')
            ->get();

        return view('client.overview', compact(
            'client',
            'allLayers',
            'totalMicroSignals',
            'layerStats',
            'activeTenants',
            'addedThisQuarter',
            'allClients'
        ));
    }
}
