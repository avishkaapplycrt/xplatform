<?php

namespace Database\Seeders;

use App\Models\BehavioralProfile;
use App\Models\UserEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserEventSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        UserEvent::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        /**
         * Event templates per segment.
         * Format: [event_type, count_min, count_max, max_days_ago, min_days_ago]
         *
         * max_days_ago: how far back events can go
         * min_days_ago: how recent events can be (0 = can happen today)
         *
         * Segments tell the story:
         *   champion — heavy recent usage, purchases, referrals, no negatives
         *   loyal    — regular usage, some purchases, low negatives
         *   at_risk  — logins have gone old (14d+ ago), frustration events still recent
         *   dormant  — all activity 38d+ ago, EXCEPT email opens in last 14d (re-engagement)
         *   new      — everything in the last 10 days only
         */
        $templates = [
            'champion' => [
                ['login',             40, 55,  90,  0],
                ['page_view',         60, 80,  90,  0],
                ['product_view',      15, 22,  90,  0],
                ['pricing_view',       3,  5,  30,  0],  // recent — evaluating upgrade
                ['feature_click',     25, 38,  90,  0],
                ['search',            10, 16,  30,  0],
                ['wishlist_add',       2,  4,  30,  0],
                ['cart_add',           3,  5,  90,  0],
                ['checkout_start',     3,  5,  90,  0],
                ['checkout_complete',  3,  5,  90,  0],  // full conversion
                ['referral_send',      2,  3,  90,  0],
                ['positive_review',    1,  2,  90,  0],
                ['email_open',         8, 12,  90,  0],
                ['email_click',        4,  7,  90,  0],
            ],
            'loyal' => [
                ['login',             25, 35,  90,  0],
                ['page_view',         40, 60,  90,  0],
                ['product_view',       8, 14,  90,  0],
                ['pricing_view',       2,  3,  30,  0],
                ['feature_click',     15, 22,  90,  0],
                ['search',             5, 10,  30,  0],
                ['wishlist_add',       1,  3,  30,  0],
                ['cart_add',           2,  4,  90,  0],
                ['checkout_start',     2,  3,  90,  0],
                ['checkout_complete',  2,  3,  90,  0],
                ['referral_send',      1,  2,  90,  0],
                ['positive_review',    1,  1,  90,  0],
                ['email_open',         5,  8,  90,  0],
                ['email_click',        2,  4,  90,  0],
                ['support_ticket',     0,  1,  90,  0],  // rare friction
                ['form_abandon',       0,  1,  90,  0],
            ],
            'at_risk' => [
                // Login and browsing activity has gone stale (14d+ ago)
                ['login',              4,  8,  90, 14],
                ['page_view',          8, 16,  90, 14],
                ['product_view',       1,  4,  90, 30],
                ['feature_click',      2,  6,  90, 20],
                ['search',             0,  3,  90, 30],
                ['cart_add',           0,  1,  90, 45],
                ['checkout_start',     0,  1,  90, 60],
                // Frustration events — still happening (recent)
                ['support_ticket',     2,  4,  90,  0],
                ['rage_click',         3,  6,  90,  0],
                ['form_abandon',       2,  4,  90,  0],
                ['negative_review',    1,  2,  90,  0],
                ['unsubscribe',        1,  1,  60, 10],
            ],
            'dormant' => [
                // All engagement is old
                ['login',              2,  4,  90, 38],
                ['page_view',          5, 10,  90, 38],
                ['product_view',       1,  3,  90, 50],
                // Re-engagement signals — RECENT (user is waking up)
                ['email_open',         2,  4,  14,  0],
                ['email_click',        1,  2,  14,  0],
            ],
            'new' => [
                // Everything within last 10 days — exploring the product
                ['login',             10, 15,  10,  0],
                ['page_view',         20, 35,  10,  0],
                ['product_view',       5, 10,  10,  0],
                ['pricing_view',       1,  2,  10,  0],
                ['feature_click',      8, 15,  10,  0],
                ['search',             3,  8,  10,  0],
                ['wishlist_add',       0,  2,  10,  0],
                ['cart_add',           0,  2,  10,  0],
                ['checkout_start',     0,  1,  10,  0],
                ['checkout_complete',  0,  1,  10,  0],
                ['email_open',         2,  4,  10,  0],
            ],
        ];

        $profiles = BehavioralProfile::all();
        $rows     = [];

        foreach ($profiles as $profile) {
            $template = $templates[$profile->segment] ?? $templates['new'];

            foreach ($template as [$type, $min, $max, $maxDays, $minDays]) {
                $count = rand($min, $max);
                for ($i = 0; $i < $count; $i++) {
                    $daysAgo = rand($minDays, $maxDays);
                    $rows[]  = [
                        'client_id'              => $profile->client_id,
                        'behavioral_profile_id'  => $profile->id,
                        'email'                  => $profile->email,
                        'event_type'             => $type,
                        'created_at'             => now()->subDays($daysAgo)
                                                         ->subHours(rand(0, 23))
                                                         ->subMinutes(rand(0, 59)),
                        'updated_at'             => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            UserEvent::insert($chunk);
        }

        $this->command->info('   Created ' . count($rows) . ' user events across ' . $profiles->count() . ' profiles.');
    }
}
