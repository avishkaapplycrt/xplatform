<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaravelEvent extends Model
{
    protected $table = 'laravel_events';

    protected $fillable = [
        'site_id',
        'event_type',
        'entity_id',
        'payload',
        'event_created_at',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'event_created_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(LaravelSite::class, 'site_id');
    }
}