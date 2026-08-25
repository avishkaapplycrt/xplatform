<?php

namespace Database\Seeders;

use App\Models\CallLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CallLogSeeder extends Seeder
{
    public function run(): void
    {
        CallLog::truncate();

        $agents = [
            'Sarah Chen', 'Mike Torres', 'Priya Patel', 'James Okafor',
            'Emma Liu', 'David Kim', 'Rachel Nguyen', 'Tom Walsh',
        ];

        $departments = ['sales', 'support', 'billing', 'general'];
        $deptWeights = [30, 45, 15, 10];

        $rows = [];

        for ($i = 0; $i < 80; $i++) {
            $calledAt = Carbon::now()->subDays(rand(0, 89))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            // 60% inbound, 40% outbound
            $callType = rand(1, 100) <= 60 ? 'inbound' : 'outbound';

            // 75% answered, 18% missed, 7% abandoned
            $roll = rand(1, 100);
            if ($roll <= 75)       $status = 'answered';
            elseif ($roll <= 93)   $status = 'missed';
            else                   $status = 'abandoned';

            // Duration only for answered calls
            $duration = null;
            $cost     = 0;
            $agent    = null;
            if ($status === 'answered') {
                $duration = rand(60, 900);
                $cost     = round($duration * 0.00022, 4); // ~$0.013/min VoIP rate
                $agent    = $agents[array_rand($agents)];
            }

            // Wait time: longer for missed/abandoned
            $wait = match($status) {
                'answered'  => rand(8,  90),
                'missed'    => rand(0,  25),
                'abandoned' => rand(30, 180),
            };

            // Weighted department pick
            $rand = rand(1, 100);
            $cumulative = 0;
            $dept = 'general';
            foreach ($departments as $idx => $d) {
                $cumulative += $deptWeights[$idx];
                if ($rand <= $cumulative) { $dept = $d; break; }
            }

            $rows[] = [
                'call_type'        => $callType,
                'status'           => $status,
                'duration_seconds' => $duration,
                'wait_time_seconds'=> $wait,
                'agent_name'       => $agent,
                'department'       => $dept,
                'cost_usd'         => $cost,
                'called_at'        => $calledAt,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        CallLog::insert($rows);
    }
}
