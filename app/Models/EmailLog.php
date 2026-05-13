<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'email_address',
        'sent_at', 'delivered_at', 'opened_at', 'clicked_at', 'converted_at', 'unsubscribed_at',
        'device_type', 'time_to_open_minutes',
    ];

    protected $casts = [
        'sent_at'         => 'datetime',
        'delivered_at'    => 'datetime',
        'opened_at'       => 'datetime',
        'clicked_at'      => 'datetime',
        'converted_at'    => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public static function deliveryStats(): array
    {
        $sent      = self::count();
        $delivered = self::whereNotNull('delivered_at')->count();
        $open      = self::whereNotNull('opened_at')->count();
        $clicked   = self::whereNotNull('clicked_at')->count();
        $converted = self::whereNotNull('converted_at')->count();

        $baseSent = max(1, $sent);
        $baseOpen = max(1, $open);

        // Device breakdown among opened emails
        $deviceCounts = self::whereNotNull('opened_at')
            ->selectRaw('device_type, COUNT(*) as cnt')
            ->groupBy('device_type')
            ->pluck('cnt', 'device_type');

        // Time-to-open buckets
        $lt1h   = self::whereNotNull('opened_at')->where('time_to_open_minutes', '<', 60)->count();
        $t1to4h = self::whereNotNull('opened_at')->whereBetween('time_to_open_minutes', [60, 240])->count();
        $t4to24h = self::whereNotNull('opened_at')->whereBetween('time_to_open_minutes', [241, 1440])->count();
        $gt24h  = self::whereNotNull('opened_at')->where('time_to_open_minutes', '>', 1440)->count();
        $baseTime = max(1, $lt1h + $t1to4h + $t4to24h + $gt24h);

        $emailFunnel = [
            ['label' => 'Sent',      'count' => $sent,      'pct' => 100,                                    'color' => '#5eead4'],
            ['label' => 'Delivered', 'count' => $delivered, 'pct' => round($delivered / $baseSent * 100),    'color' => '#60a5fa'],
            ['label' => 'Open',      'count' => $open,      'pct' => round($open      / $baseSent * 100),    'color' => '#a78bfa'],
            ['label' => 'Clicked',   'count' => $clicked,   'pct' => round($clicked   / $baseSent * 100),    'color' => '#ec4899'],
            ['label' => 'Converted', 'count' => $converted, 'pct' => round($converted / $baseSent * 100),    'color' => '#6366f1'],
        ];

        $emailDevices = [
            ['label' => 'Mobile',  'count' => $deviceCounts['mobile']  ?? 0, 'color' => '#f59e0b'],
            ['label' => 'Desktop', 'count' => $deviceCounts['desktop'] ?? 0, 'color' => '#10b981'],
            ['label' => 'Other',   'count' => $deviceCounts['other']   ?? 0, 'color' => '#8b5cf6'],
            ['label' => 'Tablet',  'count' => $deviceCounts['tablet']  ?? 0, 'color' => '#6366f1'],
        ];

        $emailTimeToOpen = [
            ['label' => '< 1 hour',     'count' => $lt1h,    'pct' => round($lt1h    / $baseTime * 100), 'color' => '#5eead4'],
            ['label' => '1 – 4 hours',  'count' => $t1to4h,  'pct' => round($t1to4h  / $baseTime * 100), 'color' => '#60a5fa'],
            ['label' => '4 – 24 hours', 'count' => $t4to24h, 'pct' => round($t4to24h / $baseTime * 100), 'color' => '#a78bfa'],
            ['label' => '> 24 hours',   'count' => $gt24h,   'pct' => round($gt24h   / $baseTime * 100), 'color' => '#f59e0b'],
        ];

        $emailClickers = self::whereNotNull('clicked_at')
            ->orderBy('clicked_at')
            ->pluck('email_address')
            ->toArray();

        $emailGroups = [
            'sent'      => self::orderBy('sent_at')->pluck('email_address')->toArray(),
            'delivered' => self::whereNotNull('delivered_at')->orderBy('delivered_at')->pluck('email_address')->toArray(),
            'open'      => self::whereNotNull('opened_at')->orderBy('opened_at')->pluck('email_address')->toArray(),
            'clicked'   => self::whereNotNull('clicked_at')->orderBy('clicked_at')->pluck('email_address')->toArray(),
            'converted' => self::whereNotNull('converted_at')->orderBy('converted_at')->pluck('email_address')->toArray(),
        ];

        // Unsubscribe stats
        $unsubscribed    = self::whereNotNull('unsubscribed_at')->count();
        $retained        = $sent - $unsubscribed;
        $unsubscribeRate = round($unsubscribed / $baseSent * 100, 1);

        return compact(
            'emailFunnel', 'emailDevices', 'emailTimeToOpen', 'emailClickers', 'emailGroups',
            'unsubscribed', 'retained', 'unsubscribeRate', 'sent'
        );
    }
}
