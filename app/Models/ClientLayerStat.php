<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientLayerStat extends Model
{
    protected $fillable = [
        'client_id',
        'analysis_layer_id',
        'stats',
        'health_score',
        'status',
        'recorded_date',
    ];

    protected $casts = [
        'stats'         => 'array',
        'health_score'  => 'integer',
        'recorded_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function analysisLayer(): BelongsTo
    {
        return $this->belongsTo(AnalysisLayer::class);
    }

    /**
     * Return the stats array joined with " • " for display.
     */
    public function getStatsSummaryAttribute(): string
    {
        return implode(' • ', $this->stats ?? []);
    }

    /**
     * Return the number of filled dots out of 6 based on health_score (0–100).
     */
    public function getFilledDotsAttribute(): int
    {
        return (int) round($this->health_score / 100 * 6);
    }
}
