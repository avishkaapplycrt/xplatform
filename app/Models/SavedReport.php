<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedReport extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'description',
        'configuration',
        'schedule',
        'last_generated_at',
        'next_scheduled_at',
    ];

    protected $casts = [
        'configuration' => 'json',
        'last_generated_at' => 'datetime',
        'next_scheduled_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}