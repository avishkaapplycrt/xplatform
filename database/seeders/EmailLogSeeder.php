<?php

namespace Database\Seeders;

use App\Models\EmailLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EmailLogSeeder extends Seeder
{
    public function run(): void
    {
        EmailLog::truncate();

        $emails = [
            'alice.morgan@gmail.com',      'brian.teo@yahoo.com',
            'carol.james@outlook.com',     'david.park@hotmail.com',
            'emma.watson@gmail.com',       'frank.liu@icloud.com',
            'grace.kim@gmail.com',         'henry.chen@yahoo.com',
            'isabella.scott@outlook.com',  'james.rivera@gmail.com',
            'karen.nguyen@hotmail.com',    'liam.patel@gmail.com',
            'mia.johnson@yahoo.com',       'noah.brown@outlook.com',
            'olivia.davis@gmail.com',      'peter.wilson@icloud.com',
            'quinn.taylor@gmail.com',      'rachel.moore@yahoo.com',
            'samuel.anderson@outlook.com', 'tina.thomas@gmail.com',
            'umar.jackson@hotmail.com',    'vera.white@gmail.com',
            'william.harris@yahoo.com',    'xena.martin@outlook.com',
            'yasmine.garcia@gmail.com',    'zack.martinez@icloud.com',
            'amy.robinson@gmail.com',      'ben.clark@yahoo.com',
            'cindy.rodriguez@gmail.com',   'derek.lewis@outlook.com',
            'eleanor.lee@gmail.com',       'felix.walker@hotmail.com',
            'gina.hall@gmail.com',         'harry.allen@yahoo.com',
            'irene.young@outlook.com',     'jake.hernandez@gmail.com',
            'kate.king@icloud.com',        'leo.wright@gmail.com',
            'mary.lopez@yahoo.com',        'neil.hill@outlook.com',
            'oscar.scott@gmail.com',       'paula.green@hotmail.com',
            'raj.adams@gmail.com',         'sara.baker@yahoo.com',
            'tom.gonzalez@gmail.com',
        ];

        $devices = ['mobile', 'desktop', 'tablet', 'other'];
        $deviceWeights = [45, 38, 10, 7]; // % probability

        // Default client_id - adjust this based on your actual clients table
        // If you have a clients table with seeded data, fetch a real ID:
        // $clientId = \App\Models\Client::first()?->id ?? 1;
        $clientId = 1;

        $rows = [];

        foreach ($emails as $email) {
            $sentAt = Carbon::now()->subDays(rand(1, 90))->subHours(rand(0, 23));

            // 93% delivery rate
            $isDelivered = rand(1, 100) <= 93;
            $deliveredAt = $isDelivered
                ? $sentAt->copy()->addMinutes(rand(2, 20))
                : null;

            // 30% open rate of delivered
            $isOpened = $isDelivered && rand(1, 100) <= 30;
            $timeToOpen = $isOpened ? rand(5, 2100) : null;
            $openedAt   = $isOpened ? $deliveredAt->copy()->addMinutes($timeToOpen) : null;

            // device weighted pick
            $device = null;
            if ($isOpened) {
                $rand = rand(1, 100);
                $cumulative = 0;
                foreach ($devices as $idx => $d) {
                    $cumulative += $deviceWeights[$idx];
                    if ($rand <= $cumulative) { $device = $d; break; }
                }
            }

            // 40% click rate of opened
            $isClicked = $isOpened && rand(1, 100) <= 40;
            $clickedAt = $isClicked
                ? $openedAt->copy()->addMinutes(rand(1, 45))
                : null;

            // 35% conversion rate of clicked
            $isConverted = $isClicked && rand(1, 100) <= 35;
            $convertedAt = $isConverted
                ? $clickedAt->copy()->addMinutes(rand(5, 180))
                : null;

            // ~10% unsubscribe rate of delivered (can unsubscribe without opening)
            $isUnsubscribed = $isDelivered && rand(1, 100) <= 10;
            $unsubscribedAt = $isUnsubscribed
                ? $sentAt->copy()->addDays(rand(1, 30))->addHours(rand(0, 23))
                : null;

            $rows[] = [
                'client_id'            => $clientId,  // <-- ADDED THIS LINE
                'email_address'        => $email,
                'sent_at'              => $sentAt,
                'delivered_at'         => $deliveredAt,
                'opened_at'            => $openedAt,
                'clicked_at'           => $clickedAt,
                'converted_at'         => $convertedAt,
                'unsubscribed_at'      => $unsubscribedAt,
                'device_type'          => $device,
                'time_to_open_minutes' => $timeToOpen,
                'created_at'           => now(),
                'updated_at'           => now(),
            ];
        }

        EmailLog::insert($rows);
    }
}