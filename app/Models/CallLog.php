<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CallLog extends Model
{
    protected $fillable = [
        'call_type', 'status', 'duration_seconds', 'wait_time_seconds',
        'agent_name', 'department', 'cost_usd', 'called_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'cost_usd'  => 'float',
    ];

    public static function callStats(): array
    {
        $callTotal    = self::count();
        $callInbound  = self::where('call_type', 'inbound')->count();
        $callOutbound = self::where('call_type', 'outbound')->count();
        $callAnswered = self::where('status', 'answered')->count();
        $callMissed   = self::where('status', 'missed')->count();
        $callAbandoned= self::where('status', 'abandoned')->count();

        $base = max(1, $callTotal);
        $callAnswerRate  = round($callAnswered  / $base * 100, 1);
        $callMissRate    = round($callMissed    / $base * 100, 1);

        $callTotalCost   = round(self::sum('cost_usd'), 2);
        $callAvgCost     = $callTotal > 0 ? round($callTotalCost / $callTotal, 2) : 0;
        $callAvgDuration = round(self::where('status', 'answered')->avg('duration_seconds') ?? 0);
        $callAvgWait     = round(self::avg('wait_time_seconds') ?? 0);

        // Department breakdown
        $callDeptStats = self::selectRaw('department, COUNT(*) as total, ROUND(AVG(duration_seconds)) as avg_dur, SUM(cost_usd) as dept_cost')
            ->groupBy('department')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'department' => ucfirst($r->department),
                'total'      => (int) $r->total,
                'avg_dur'    => (int) ($r->avg_dur ?? 0),
                'dept_cost'  => round($r->dept_cost ?? 0, 2),
            ])->toArray();

        // Daily volume last 14 days — single query
        $start = now()->subDays(13)->startOfDay();
        $raw   = self::where('called_at', '>=', $start)
            ->selectRaw("DATE(called_at) as day, call_type, COUNT(*) as cnt")
            ->groupBy('day', 'call_type')
            ->get()
            ->groupBy('day');

        $callDailyVolume = [];
        for ($i = 13; $i >= 0; $i--) {
            $date    = now()->subDays($i)->format('Y-m-d');
            $dayData = $raw->get($date, collect());
            $callDailyVolume[] = [
                'date'     => now()->subDays($i)->format('M d'),
                'inbound'  => (int) $dayData->where('call_type', 'inbound')->sum('cnt'),
                'outbound' => (int) $dayData->where('call_type', 'outbound')->sum('cnt'),
            ];
        }

        // Outcome breakdown with pct
        $callOutcomes = [
            ['label' => 'Answered',  'count' => $callAnswered,  'pct' => $callAnswerRate, 'color' => '#10b981'],
            ['label' => 'Missed',    'count' => $callMissed,    'pct' => $callMissRate,   'color' => '#ef4444'],
            ['label' => 'Abandoned', 'count' => $callAbandoned, 'pct' => round($callAbandoned / $base * 100, 1), 'color' => '#f59e0b'],
        ];

        return compact(
            'callTotal', 'callInbound', 'callOutbound',
            'callAnswered', 'callMissed', 'callAbandoned',
            'callAnswerRate', 'callMissRate',
            'callTotalCost', 'callAvgCost', 'callAvgDuration', 'callAvgWait',
            'callDeptStats', 'callDailyVolume', 'callOutcomes'
        );
    }
}
