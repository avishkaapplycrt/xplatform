<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnalysisLayer;

class AnalysisLayerSeeder extends Seeder
{
    public function run(): void
    {
        $layers = [
            ['code' => 'L1', 'name' => 'Data Collection',        'description' => 'Capture raw events',          'sort_order' => 1, 'is_active' => 1, 'color' => '#10b981'],
            ['code' => 'L2', 'name' => 'Identity Resolution',    'description' => 'Unify across touchpoints',    'sort_order' => 2, 'is_active' => 1, 'color' => '#3b82f6'],
            ['code' => 'L3', 'name' => 'Data Processing',        'description' => 'Clean, transform, store',     'sort_order' => 3, 'is_active' => 1, 'color' => '#8b5cf6'],
            ['code' => 'L4', 'name' => 'Behavioral Intelligence','description' => 'Detect patterns',             'sort_order' => 4, 'is_active' => 1, 'color' => '#f59e0b'],
            ['code' => 'L5', 'name' => 'AI/ML Predictions',      'description' => 'Forecast outcomes',           'sort_order' => 5, 'is_active' => 1, 'color' => '#f97316'],
            ['code' => 'L6', 'name' => 'Decision & Actions',     'description' => 'Trigger actions',             'sort_order' => 6, 'is_active' => 1, 'color' => '#059669'],
            ['code' => 'L7', 'name' => 'Application Layer',      'description' => 'Visualize and integrate',     'sort_order' => 7, 'is_active' => 1, 'color' => '#ef4444'],
            ['code' => 'L8', 'name' => 'Governance & Security',  'description' => 'Audit and comply',            'sort_order' => 8, 'is_active' => 1, 'color' => '#6b7280'],
        ];

        foreach ($layers as $layer) {
            AnalysisLayer::updateOrCreate(['code' => $layer['code']], $layer);
        }
    }
}
