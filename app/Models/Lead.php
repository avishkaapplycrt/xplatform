<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'email',
        'phone',
        'company',
        'job_title',
        'source',
        'source_detail',
        'status',
        'qualification_status',
        'assigned_to',
        'last_scored_at',
        'converted_at',
        'metadata',
    ];

    protected $casts = [
        'last_scored_at' => 'datetime',
        'converted_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scores()
    {
        return $this->hasMany(LeadScore::class)->orderBy('created_at', 'desc');
    }

    public function latestScore()
    {
        return $this->hasOne(LeadScore::class)->latestOfMany();
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->orderBy('created_at', 'desc');
    }
}
