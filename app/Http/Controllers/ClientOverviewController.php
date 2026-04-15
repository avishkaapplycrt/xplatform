<?php

namespace App\Http\Controllers;

class ClientOverviewController extends Controller
{
    public function show()
    {
        $client = auth('client')->user();

        // Static platform layers matching the design
        $allLayers = [
            ['code' => 'L1', 'name' => 'Data Collection',        'color' => '#10b981', 'summary' => '12 sources  •  100+ micro-signals  •  4.2M events/day',       'dots' => 4],
            ['code' => 'L2', 'name' => 'Identity Resolution',     'color' => '#3b82f6', 'summary' => '14 identifier types  •  cross-device stitching  •  98.2%',    'dots' => 4],
            ['code' => 'L3', 'name' => 'Data Processing',         'color' => '#8b5cf6', 'summary' => 'Real-time + batch  •  6 store types  •  14 quality signals',  'dots' => 4],
            ['code' => 'L4', 'name' => 'Behavioral Intelligence',  'color' => '#f59e0b', 'summary' => '9 behavioral scores  •  40+ micro-signal inputs',             'dots' => 4],
            ['code' => 'L5', 'name' => 'AI/ML Predictions',       'color' => '#f97316', 'summary' => '11 models  •  16 prediction micro-signals  •  94.1% avg',     'dots' => 4],
            ['code' => 'L6', 'name' => 'Decision & Actions',      'color' => '#14b8a6', 'summary' => '14 action micro-signals  •  rule engine + AI recs',           'dots' => 3],
            ['code' => 'L7', 'name' => 'Application Layer',       'color' => '#ef4444', 'summary' => '15 platform usage signals  •  10 modules',                    'dots' => 3],
            ['code' => 'L8', 'name' => 'Governance & Security',   'color' => '#6b7280', 'summary' => '14 compliance signals  •  RBAC  •  Audit  •  Consent',       'dots' => 3],
        ];

        // Static tenant mini-cards
        $allClients = [
            ['name' => 'Acme Retail',  'plan' => 'ENTERPRISE', 'plan_bg' => '#faf5ff', 'plan_text' => '#7e22ce'],
            ['name' => 'Bank North',   'plan' => 'ENTERPRISE', 'plan_bg' => '#faf5ff', 'plan_text' => '#7e22ce'],
            ['name' => 'HealthPlus',   'plan' => 'BUSINESS',   'plan_bg' => '#eff6ff', 'plan_text' => '#1d4ed8'],
            ['name' => 'AutoBrand',    'plan' => 'BUSINESS',   'plan_bg' => '#eff6ff', 'plan_text' => '#1d4ed8'],
        ];

        return view('client.overview', compact('client', 'allLayers', 'allClients'));
    }

}
