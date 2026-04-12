<?php

namespace Database\Seeders;

use App\Models\Action;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ActionSeeder extends Seeder
{
    public function run(): void
    {
        $actions = [
            ['name' => 'Send Email',          'description' => 'Automated campaigns'],
            ['name' => 'Send SMS',             'description' => 'Text notifications'],
            ['name' => 'Push Notification',    'description' => 'In-app and mobile'],
            ['name' => 'Assign Sales Rep',     'description' => 'Route to sales team'],
            ['name' => 'Assign Mentor',        'description' => 'Connect with expert'],
            ['name' => 'Recommend Product',    'description' => 'Personalized picks'],
            ['name' => 'Offer Discount',       'description' => 'Dynamic pricing'],
            ['name' => 'Flag for Review',      'description' => 'Manual escalation'],
        ];

        foreach ($actions as $index => $action) {
            Action::updateOrCreate(
                ['slug' => Str::slug($action['name'])],
                [
                    'name'        => $action['name'],
                    'description' => $action['description'],
                    'is_active'   => true,
                    'sort_order'  => $index + 1,
                ]
            );
        }

        $this->command->info('✅ Actions seeded successfully!');
    }
}