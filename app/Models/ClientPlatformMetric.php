<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPlatformMetric extends Model
{
    protected $fillable = [
        'client_id',
        'events_per_day',
        'events_wow_pct',
        'profiles_resolved',
        'match_rate_pct',
        'recorded_date',
    ];

    protected $casts = [
        'events_per_day'    => 'integer',
        'events_wow_pct'    => 'float',
        'profiles_resolved' => 'integer',
        'match_rate_pct'    => 'float',
        'recorded_date'     => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Format a large number to a human-readable string (e.g. 4200000 → "4.2M").
     */
    public function formatNumber(int|null $value): string
    {
        if ($value === null) return '—';
        if ($value >= 1_000_000) return round($value / 1_000_000, 1) . 'M';
        if ($value >= 1_000)     return round($value / 1_000, 1) . 'K';
        return (string) $value;
    }
}
