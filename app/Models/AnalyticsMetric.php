<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsMetric extends Model
{
    protected $fillable = [
        'client_id',
        'data_source_id',
        'metric_type',
        'dimensions',
        'value',
        'unit',
        'measured_at',
        'granularity',
        'metadata',
    ];

    protected $casts = [
        'dimensions' => 'json',
        'metadata' => 'json',
        'measured_at' => 'datetime',
        'value' => 'decimal:4',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(ClientDataSource::class, 'data_source_id');
    }
}