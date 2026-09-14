<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailProviderCampaign extends Model
{
    protected $fillable = [
        'connection_id',
        'client_id',
        'tenant_id',
        'platform',
        'external_id',
        'name',
        'subject',
        'status',
        'send_time',
        'sent_count',
        'delivered_count',
        'opens_count',
        'clicks_count',
        'raw_data',
    ];

    protected $casts = [
        'send_time' => 'datetime',
        'raw_data'  => 'array',
    ];

    public function connection()
    {
        return $this->belongsTo(EmailConnection::class, 'connection_id');
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Real engagement numbers for a client with an active email provider
     * connection (Brevo, MailChimp, ...), in the same shape as
     * EmailLog::deliveryStats() so the L1 view can consume either.
     * Returns null when the client has no active connection, so the
     * caller can fall back to the platform's own demo data instead.
     * Device and time-to-open breakdowns aren't available from a
     * provider's campaign-level sync (no per-recipient data without a
     * webhook receiver), so they're returned empty rather than faked.
     */
    public static function deliveryStatsForClient(int $clientId): ?array
    {
        $hasConnection = EmailConnection::where('client_id', $clientId)
            ->where('status', 'active')
            ->exists();

        if (!$hasConnection) {
            return null;
        }

        $campaigns = self::forClient($clientId)->get();

        $sent      = (int) $campaigns->sum('sent_count');
        $delivered = (int) $campaigns->sum('delivered_count');
        $opens     = (int) $campaigns->sum('opens_count');
        $clicks    = (int) $campaigns->sum('clicks_count');
        $baseSent  = max(1, $sent);

        $emailFunnel = [
            ['label' => 'Sent',      'count' => $sent,      'pct' => 100,                                        'color' => '#5eead4'],
            ['label' => 'Delivered', 'count' => $delivered, 'pct' => round($delivered / $baseSent * 100),        'color' => '#60a5fa'],
            ['label' => 'Open',      'count' => $opens,     'pct' => round($opens      / $baseSent * 100),       'color' => '#a78bfa'],
            ['label' => 'Clicked',   'count' => $clicks,    'pct' => round($clicks     / $baseSent * 100),       'color' => '#ec4899'],
            ['label' => 'Converted', 'count' => 0,          'pct' => 0,                                          'color' => '#6366f1'],
        ];

        $emailDevices = [
            ['label' => 'Mobile',  'count' => 0, 'color' => '#f59e0b'],
            ['label' => 'Desktop', 'count' => 0, 'color' => '#10b981'],
            ['label' => 'Other',   'count' => 0, 'color' => '#8b5cf6'],
            ['label' => 'Tablet',  'count' => 0, 'color' => '#6366f1'],
        ];

        $emailTimeToOpen = [
            ['label' => '< 1 hour',     'count' => 0, 'pct' => 0, 'color' => '#5eead4'],
            ['label' => '1 – 4 hours',  'count' => 0, 'pct' => 0, 'color' => '#60a5fa'],
            ['label' => '4 – 24 hours', 'count' => 0, 'pct' => 0, 'color' => '#a78bfa'],
            ['label' => '> 24 hours',   'count' => 0, 'pct' => 0, 'color' => '#f59e0b'],
        ];

        $emailGroups = ['sent' => [], 'delivered' => [], 'open' => [], 'clicked' => [], 'converted' => []];

        $unsubscribed = 0;
        $retained = $sent - $unsubscribed;

        return compact(
            'emailFunnel', 'emailDevices', 'emailTimeToOpen', 'emailGroups',
            'unsubscribed', 'retained', 'sent'
        ) + ['emailClickers' => [], 'unsubscribeRate' => 0.0];
    }
}
