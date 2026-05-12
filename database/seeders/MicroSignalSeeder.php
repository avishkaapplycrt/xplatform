<?php

namespace Database\Seeders;

use App\Models\MicroSignal;
use App\Models\MicroSignalCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MicroSignalSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // CREATE CATEGORIES
        // =========================
        $engagement = $this->category('engagement-signals', 'ENGAGEMENT SIGNALS', 1);
        $conversion = $this->category('conversion-signals', 'CONVERSION SIGNALS', 2);
        $interaction = $this->category('interaction-signals', 'INTERACTION SIGNALS', 3);
        $learning = $this->category('learning-signals', 'LEARNING SIGNALS', 4);
        $commerce = $this->category('commerce-signals', 'COMMERCE SIGNALS', 5);

        // =========================
        // GLOBAL SIGNALS (ALL INDUSTRIES)
        // =========================
        $this->insertSignals($engagement->id, [
            'Page visits',
            'Session duration',
            'Feature usage',
            'Scroll depth',
        ], null);

        $this->insertSignals($conversion->id, [
            'Purchases',
            'Form submissions',
            'Sign-ups',
            'Plan upgrades',
        ], null);

        $this->insertSignals($interaction->id, [
            'Email opens',
            'Chat messages',
            'Support tickets',
            'Call bookings',
        ], null);

        // =========================
        // EDTECH ONLY
        // =========================
        $this->insertSignals($learning->id, [
            'Lesson completion',
            'Practice tests',
            'Study consistency',
            'Mentor sessions',
        ], 'EdTech');

        // =========================
        // ECOMMERCE + RETAIL
        // =========================
        $commerceSignals = [
            'Cart additions',
            'Wishlist saves',
            'Coupon usage',
            'Review reads',
        ];

        $this->insertSignals($commerce->id, $commerceSignals, 'E-commerce');
        $this->insertSignals($commerce->id, $commerceSignals, 'Retail');
    }

    // =========================
    // CREATE CATEGORY
    // =========================
    private function category($slug, $name, $order)
    {
        return MicroSignalCategory::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'sort_order' => $order,
            ]
        );
    }

    // =========================
    // INSERT SIGNALS (SAFE VERSION)
    // =========================
    private function insertSignals(int $categoryId, array $signals, ?string $industry): void
    {
        foreach ($signals as $index => $name) {

            // 🔥 UNIQUE SLUG (VERY IMPORTANT)
            $slug = Str::slug($name . '-' . ($industry ?? 'global'));

            MicroSignal::updateOrCreate(
                [
                    'slug' => $slug,
                ],
                [
                    'micro_signal_category_id' => $categoryId,
                    'name' => $name,
                    'industry' => $industry,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}