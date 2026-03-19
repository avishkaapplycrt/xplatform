<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessInsight extends Model
{
    protected $fillable = [
        'client_id',
        'data_source_id',
        'category',
        'title',
        'description',
        'recommendation',
        'evidence',
        'confidence_score',
        'impact_level',
        'status',
        'viewed_at',
        'actioned_by',
        'action_notes',
        'generated_at',
    ];

    protected $casts = [
        'evidence' => 'json',
        'confidence_score' => 'decimal:2',
        'viewed_at' => 'datetime',
        'generated_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(ClientDataSource::class, 'data_source_id');
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'actioned_by');
    }
}