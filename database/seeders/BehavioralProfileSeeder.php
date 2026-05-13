<?php

namespace Database\Seeders;

use App\Models\BehavioralProfile;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BehavioralProfileSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        BehavioralProfile::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $clientId = Client::value('id');

        // Formula: intent*.18 + engagement*.18 + loyalty*.14 + trust*.14 + buying*.10
        //        + (100-churn)*.12 + (100-frust)*.08 + (100-dropoff)*.06
        $calc = fn($i,$e,$c,$l,$t,$f,$b,$d) => (int) round(
            $i*.18 + $e*.18 + $l*.14 + $t*.14 + $b*.10 +
            (100-$c)*.12 + (100-$f)*.08 + (100-$d)*.06
        );

        // [name, email, segment, intent, engage, churn, loyal, trust, frust, buying, dropoff, react, last_active]
        $users = [
            // ── Champions ────────────────────────────────────────────────────
            ['Priya Sharma',       'priya.sharma@gmail.com',        'champion', 91, 95,  8, 94, 92, 10, 85,  8, 25, now()->subHours(1)],
            ['Emma Rodriguez',     'emma.rodriguez@gmail.com',      'champion', 89, 92, 12, 88, 91, 15, 78, 10, 30, now()->subHours(2)],
            ['Liam Park',          'liam.park@yahoo.com',           'champion', 85, 88, 18, 82, 87, 20, 72, 14, 35, now()->subHours(5)],
            ['Marcus Chen',        'marcus.chen@outlook.com',       'champion', 83, 86, 22, 79, 84, 22, 70, 18, 40, now()->subDays(1)],
            // ── Loyal ─────────────────────────────────────────────────────────
            ['Aisha Patel',        'aisha.patel@gmail.com',         'loyal',    78, 75, 25, 78, 80, 28, 68, 22, 42, now()->subDays(1)],
            ['Sarah Kim',          'sarah.kim@gmail.com',           'loyal',    75, 78, 28, 80, 75, 30, 65, 25, 45, now()->subDays(2)],
            ['Yuki Tanaka',        'yuki.tanaka@gmail.com',         'loyal',    72, 74, 30, 76, 73, 32, 63, 27, 48, now()->subDays(2)],
            ['Daniel Torres',      'daniel.torres@hotmail.com',     'loyal',    70, 72, 32, 74, 70, 35, 60, 28, 50, now()->subDays(3)],
            ['James Wilson',       'james.wilson@yahoo.com',        'loyal',    68, 70, 35, 72, 68, 38, 58, 32, 52, now()->subDays(4)],
            // ── At Risk ───────────────────────────────────────────────────────
            ['Kevin O\'Brien',     'kevin.obrien@yahoo.com',        'at_risk',  50, 35, 65, 38, 45, 58, 42, 62, 50, now()->subDays(14)],
            ['Ryan Mitchell',      'ryan.mitchell@gmail.com',       'at_risk',  45, 28, 72, 30, 40, 65, 35, 68, 55, now()->subDays(18)],
            ['Natalie Brooks',     'natalie.brooks@gmail.com',      'at_risk',  42, 30, 70, 28, 38, 68, 32, 70, 58, now()->subDays(20)],
            ['Sofia Hernandez',    'sofia.hernandez@outlook.com',   'at_risk',  38, 22, 80, 22, 32, 75, 28, 75, 60, now()->subDays(22)],
            // ── Dormant ───────────────────────────────────────────────────────
            ['Tom Erikson',        'tom.erikson@gmail.com',         'dormant',  38, 25, 42, 52, 58, 38, 28, 40, 75, now()->subDays(38)],
            ['Ben Okafor',         'ben.okafor@gmail.com',          'dormant',  35, 20, 45, 55, 60, 40, 25, 42, 78, now()->subDays(45)],
            ['Mia Santos',         'mia.santos@outlook.com',        'dormant',  32, 15, 48, 44, 52, 42, 22, 45, 80, now()->subDays(55)],
            ['Chloe Nguyen',       'chloe.nguyen@hotmail.com',      'dormant',  40, 18, 50, 48, 55, 35, 30, 48, 82, now()->subDays(60)],
            // ── New ───────────────────────────────────────────────────────────
            ['Jordan Lee',         'jordan.lee@yahoo.com',          'new',      60, 62, 38, 50, 55, 35, 55, 35, 45, now()->subDays(5)],
            ['Alex Johnson',       'alex.johnson@gmail.com',        'new',      55, 58, 40, 45, 50, 40, 52, 38, 50, now()->subDays(7)],
            ['Maya Thompson',      'maya.thompson@gmail.com',       'new',      52, 55, 42, 42, 48, 42, 48, 40, 52, now()->subDays(10)],
        ];

        foreach ($users as [$name,$email,$seg,$i,$e,$c,$l,$t,$f,$b,$d,$r,$lastActive]) {
            BehavioralProfile::create([
                'client_id'             => $clientId,
                'name'                  => $name,
                'email'                 => $email,
                'segment'               => $seg,
                'intent_score'          => $i,
                'engagement_score'      => $e,
                'churn_score'           => $c,
                'loyalty_score'         => $l,
                'trust_score'           => $t,
                'frustration_score'     => $f,
                'buying_readiness'      => $b,
                'dropoff_risk'          => $d,
                'reactivation_potential'=> $r,
                'overall_score'         => $calc($i,$e,$c,$l,$t,$f,$b,$d),
                'last_active_at'        => $lastActive,
            ]);
        }
    }
}
