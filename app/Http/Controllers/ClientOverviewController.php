<?php

namespace App\Http\Controllers;

class ClientOverviewController extends Controller
{
    public function show()
    {
        $client = auth('client')->user()->load('analysisLayers');

        $selectedLayers = $client->analysisLayers->sortBy('sort_order');

        // Fall back to all active layers for clients who skipped the registration flow
        if ($selectedLayers->isEmpty()) {
            $selectedLayers = \App\Models\AnalysisLayer::where('is_active', 1)->orderBy('sort_order')->get();
        }

        // Each layer's line is the client's own figures (see App\Services\LayerSummaries).
        $summaries = app(\App\Services\LayerSummaries::class)->forClient($client);

        $allLayers = $selectedLayers->map(function ($layer) use ($summaries) {
                $meta = $summaries[$layer->code] ?? ['summary' => $layer->description ?? '', 'dots' => 0];
                return [
                    'code'    => $layer->code,
                    'name'    => $layer->name,
                    'color'   => $layer->color ?? '#6b7280',
                    'summary' => $meta['summary'],
                    'dots'    => $meta['dots'],
                ];
            })
            ->values()
            ->all();

        // The signed-in client's own counts for the cards at the top.
        $dcCounts = app(\App\Services\DataCollectionStats::class)->counts($client);

        return view('client.overview', compact('client', 'allLayers', 'dcCounts'));
    }
}
