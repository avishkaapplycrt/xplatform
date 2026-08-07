<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'lead_id',
        'behavior_score',
        'demographic_score',
        'engagement_score',
        'total_score',
        'qualification_status',
        'conversion_probability',
        'factors',
    ];

    protected $casts = [
        'behavior_score' => 'decimal:2',
        'demographic_score' => 'decimal:2',
        'engagement_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'conversion_probability' => 'decimal:2',
        'factors' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
