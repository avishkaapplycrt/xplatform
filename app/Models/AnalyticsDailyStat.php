<?php
// app/Models/AnalyticsDailyStat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsDailyStat extends Model
{
    protected $fillable = [
        'site_id', 'date', 'pageviews', 'unique_visitors',
        'sessions', 'bounce_sessions', 'avg_session_duration',
        'bounce_rate', 'countries', 'devices', 'browsers',
        'oses', 'pages', 'referrers', 'utm_sources'
    ];
    
    protected $casts = [
        'date' => 'date',
        'bounce_rate' => 'decimal:2',
        'avg_session_duration' => 'decimal:2',
        'countries' => 'array',
        'devices' => 'array',
        'browsers' => 'array',
        'oses' => 'array',
        'pages' => 'array',
        'referrers' => 'array',
        'utm_sources' => 'array'
    ];
}