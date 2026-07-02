<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            TestClientSeeder::class,
            EmailTemplateCategoriesSeeder::class,
            MasterAdminSeeder::class,
            IndustrySeeder::class,
            IndustryPredictionSeeder::class,
            AnalysisLayerSeeder::class,
            DataSourceSeeder::class,
            MicroSignalSeeder::class,
            ActionSeeder::class,
            EmailLogSeeder::class,
            CallLogSeeder::class,
            BehavioralProfileSeeder::class,
            UserEventSeeder::class,
        ]);

        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}