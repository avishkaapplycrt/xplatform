<?php

namespace App\Http\Controllers;

class ClientOverviewController extends Controller
{
    // Display-only metadata keyed by layer code
    private const LAYER_META = [
        'L1' => ['summary' => '12 sources  •  100+ micro-signals  •  4.2M events/day',      'dots' => 4],
        'L2' => ['summary' => '14 identifier types  •  cross-device stitching  •  98.2%',   'dots' => 4],
        'L3' => ['summary' => 'Real-time + batch  •  6 store types  •  14 quality signals', 'dots' => 4],
        'L4' => ['summary' => '9 behavioral scores  •  40+ micro-signal inputs',            'dots' => 4],
        'L5' => ['summary' => '11 models  •  16 prediction micro-signals  •  94.1% avg',    'dots' => 4],
        'L6' => ['summary' => '14 action micro-signals  •  rule engine + AI recs',          'dots' => 3],
        'L7' => ['summary' => '15 platform usage signals  •  10 modules',                   'dots' => 3],
        'L8' => ['summary' => '14 compliance signals  •  RBAC  •  Audit  •  Consent',      'dots' => 3],
    ];

    public function show()
    {
        $client = auth('client')->user()->load('analysisLayers');

        $selectedLayers = $client->analysisLayers->sortBy('sort_order');

        // Fall back to all active layers for clients who skipped the registration flow
        if ($selectedLayers->isEmpty()) {
            $selectedLayers = \App\Models\AnalysisLayer::where('is_active', 1)->orderBy('sort_order')->get();
        }

        $allLayers = $selectedLayers->map(function ($layer) {
                $meta = self::LAYER_META[$layer->code] ?? ['summary' => $layer->description ?? '', 'dots' => 3];
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

        $allClients = [
            ['name' => 'Acme Retail', 'plan' => 'ENTERPRISE', 'plan_bg' => '#faf5ff', 'plan_text' => '#7e22ce'],
            ['name' => 'Bank North',  'plan' => 'ENTERPRISE', 'plan_bg' => '#faf5ff', 'plan_text' => '#7e22ce'],
            ['name' => 'HealthPlus',  'plan' => 'BUSINESS',   'plan_bg' => '#eff6ff', 'plan_text' => '#1d4ed8'],
            ['name' => 'AutoBrand',   'plan' => 'BUSINESS',   'plan_bg' => '#eff6ff', 'plan_text' => '#1d4ed8'],
        ];

        return view('client.overview', compact('client', 'allLayers', 'allClients'));
    }
}
