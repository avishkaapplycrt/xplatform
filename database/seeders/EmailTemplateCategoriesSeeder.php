<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $client = DB::table('clients')->first();

        if (!$client) {
            $this->command->warn('No client found. Skipping email template categories.');
            return;
        }

        $defaultCategories = [
            ['name' => 'General', 'slug' => 'general', 'color' => '#374151', 'bg_color' => '#f3f4f6', 'sort_order' => 1, 'is_default' => true],
            ['name' => 'Intervention', 'slug' => 'intervention', 'color' => '#92400e', 'bg_color' => '#fef3c7', 'sort_order' => 2, 'is_default' => true],
            ['name' => 'Win-Back', 'slug' => 'winback', 'color' => '#991b1b', 'bg_color' => '#fee2e2', 'sort_order' => 3, 'is_default' => true],
            ['name' => 'Upsell', 'slug' => 'upsell', 'color' => '#166534', 'bg_color' => '#dcfce7', 'sort_order' => 4, 'is_default' => true],
            ['name' => 'Onboarding', 'slug' => 'onboarding', 'color' => '#1e40af', 'bg_color' => '#dbeafe', 'sort_order' => 5, 'is_default' => true],
        ];

        foreach ($defaultCategories as $cat) {
            DB::table('email_template_categories')->insert(array_merge($cat, [
                'client_id' => $client->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}