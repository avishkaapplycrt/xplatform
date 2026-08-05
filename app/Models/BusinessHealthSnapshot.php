<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** The latest AI-generated health score for a client. See BusinessIntelligenceService. */
class BusinessHealthSnapshot extends Model
{
    protected $fillable = [
        'client_id',
        'health_score',
        'summary',
        'strengths',
        'weaknesses',
        'opportunities',
        'generated_at',
    ];

    protected $casts = [
        'strengths'     => 'array',
        'weaknesses'    => 'array',
        'opportunities' => 'array',
        'generated_at'  => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
