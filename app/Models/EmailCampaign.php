<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    protected $fillable = [
        'campaign_name',
        'sent_count', 'delivered_count', 'open_count',
        'clicked_count', 'converted_count',
        'mobile_opens', 'desktop_opens', 'tablet_opens', 'other_opens',
        'time_lt1h', 'time_1to4h', 'time_4to24h', 'time_gt24h',
        'sent_date',
    ];

    protected $casts = ['sent_date' => 'date'];

    public static function deliveryStats(): array
    {
        $t = self::selectRaw('
            SUM(sent_count)       as total_sent,
            SUM(delivered_count)  as total_delivered,
            SUM(open_count)       as total_open,
            SUM(clicked_count)    as total_clicked,
            SUM(converted_count)  as total_converted,
            SUM(mobile_opens)     as total_mobile,
            SUM(desktop_opens)    as total_desktop,
            SUM(tablet_opens)     as total_tablet,
            SUM(other_opens)      as total_other,
            SUM(time_lt1h)        as total_lt1h,
            SUM(time_1to4h)       as total_1to4h,
            SUM(time_4to24h)      as total_4to24h,
            SUM(time_gt24h)       as total_gt24h
        ')->first();

        $sent      = max(1, (int) $t->total_sent);
        $totalOpen = max(1, (int) $t->total_open);
        $totalTime = max(1, (int)($t->total_lt1h + $t->total_1to4h + $t->total_4to24h + $t->total_gt24h));

        $fmt = fn(int $n): string => $n >= 1_000_000
            ? round($n / 1_000_000, 1) . 'M'
            : ($n >= 1_000 ? round($n / 1_000, 1) . 'K' : (string) $n);

        $emailFunnel = [
            ['label' => 'Sent',      'count' => $fmt($t->total_sent),      'pct' => 100,                                              'color' => '#5eead4'],
            ['label' => 'Delivered', 'count' => $fmt($t->total_delivered),  'pct' => round($t->total_delivered / $sent * 100),         'color' => '#60a5fa'],
            ['label' => 'Open',      'count' => $fmt($t->total_open),       'pct' => round($t->total_open      / $sent * 100),         'color' => '#a78bfa'],
            ['label' => 'Clicked',   'count' => $fmt($t->total_clicked),    'pct' => round($t->total_clicked   / $sent * 100),         'color' => '#ec4899'],
            ['label' => 'Converted', 'count' => $fmt($t->total_converted),  'pct' => round($t->total_converted / $sent * 100),         'color' => '#6366f1'],
        ];

        $emailDevices = [
            ['label' => 'Mobile',  'count' => $fmt($t->total_mobile),  'color' => '#f59e0b'],
            ['label' => 'Desktop', 'count' => $fmt($t->total_desktop), 'color' => '#10b981'],
            ['label' => 'Other',   'count' => $fmt($t->total_other),   'color' => '#8b5cf6'],
            ['label' => 'Tablet',  'count' => $fmt($t->total_tablet),  'color' => '#6366f1'],
        ];

        $emailTimeToOpen = [
            ['label' => '< 1 hour',     'count' => $fmt($t->total_lt1h),   'pct' => round($t->total_lt1h   / $totalTime * 100), 'color' => '#5eead4'],
            ['label' => '1 – 4 hours',  'count' => $fmt($t->total_1to4h),  'pct' => round($t->total_1to4h  / $totalTime * 100), 'color' => '#60a5fa'],
            ['label' => '4 – 24 hours', 'count' => $fmt($t->total_4to24h), 'pct' => round($t->total_4to24h / $totalTime * 100), 'color' => '#a78bfa'],
            ['label' => '> 24 hours',   'count' => $fmt($t->total_gt24h),  'pct' => round($t->total_gt24h  / $totalTime * 100), 'color' => '#f59e0b'],
        ];

        return compact('emailFunnel', 'emailDevices', 'emailTimeToOpen');
    }
}
