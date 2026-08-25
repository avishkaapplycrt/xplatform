<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    public function run(): void
    {
        $industries = [
            'EdTech', 'E-commerce', 'SaaS', 'Recruitment', 'Finance',
            'Healthcare', 'Travel', 'Gaming', 'Media', 'Retail',
            'Insurance', 'Real Estate', 'Marketing / AdTech', 'Government', 'Manufacturing',
        ];

        foreach ($industries as $name) {
            Industry::firstOrCreate(['name' => $name]);
        }
    }
}
