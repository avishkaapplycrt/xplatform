<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'client_id',
        'email_template_id',
        'email_address',
        'recipient_name',
        'subject',
        'body',
        'type',
        'bulk_count',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'converted_at',
        'unsubscribed_at',
        'device_type',
        'time_to_open_minutes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'sent_at'         => 'datetime',
        'delivered_at'    => 'datetime',
        'opened_at'       => 'datetime',
        'clicked_at'      => 'datetime',
        'converted_at'    => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWithType($query, $type)
    {
        return $query->where('type', $type);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'sent' => '#10B981',
            'failed' => '#F43F5E',
            'queued' => '#F59E0B',
            'bounced' => '#A855F7',
            default => '#6b7280',
        };
    }

    public function getStatusBgColorAttribute(): string
    {
        return match($this->status) {
            'sent' => '#dcfce7',
            'failed' => '#fef2f2',
            'queued' => '#fef3c7',
            'bounced' => '#f3e8ff',
            default => '#f3f4f6',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'single' => '#0EA5E9',
            'bulk' => '#A855F7',
            default => '#6b7280',
        };
    }

    public function getTypeBgColorAttribute(): string
    {
        return match($this->type) {
            'single' => '#e0f2fe',
            'bulk' => '#f3e8ff',
            default => '#f3f4f6',
        };
    }

    // ── Existing deliveryStats method (preserved) ───────────────────────────

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

    // ── New: Stats for email logs page ───────────────────────────────────────

    public static function statsForClient(int $clientId): array
    {
        $total = self::forClient($clientId)->count();
        $sent = self::forClient($clientId)->whereNotNull('sent_at')->count();
        $failed = self::forClient($clientId)->where('status', 'failed')->count();
        $bulk = self::forClient($clientId)->where('type', 'bulk')->count();
        $today = self::forClient($clientId)->whereDate('created_at', today())->count();

        $totalSent = max(1, $sent);
        $opened = self::forClient($clientId)->whereNotNull('opened_at')->count();

        return [
            'total' => $total,
            'total_sent' => $sent,
            'total_failed' => $failed,
            'total_bulk' => $bulk,
            'today_count' => $today,
            'open_rate' => round(($opened / $totalSent) * 100, 1),
            'last_7_days' => self::forClient($clientId)->where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }
}