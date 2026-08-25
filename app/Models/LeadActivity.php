<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    use HasFactory;

    protected $table = 'lead_activities';

    protected $fillable = [
        'client_id',
        'lead_id',
        'type',
        'data',
        'duration_seconds',
    ];

    protected $casts = [
        'data' => 'array',
        'duration_seconds' => 'integer',
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
