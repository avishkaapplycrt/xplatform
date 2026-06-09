<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    protected $table = 'analytics_events';

    protected $fillable = [
        'site_id',
        'event_type',
        'wp_entity_id',
        'payload',
        'wp_created_at',
        'session_id',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'wp_created_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(WordPressSite::class, 'site_id');
    }
}