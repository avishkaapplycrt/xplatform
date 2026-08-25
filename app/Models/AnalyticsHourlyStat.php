<?php
// app/Models/AnalyticsHourlyStat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsHourlyStat extends Model
{
    protected $fillable = [
        'site_id', 'date', 'hour', 'pageviews', 'unique_visitors',
        'sessions', 'bounce_sessions', 'countries', 'devices',
        'browsers', 'oses', 'pages', 'referrers', 'utm_sources'
    ];
    
    protected $casts = [
        'date' => 'date',
        'countries' => 'array',
        'devices' => 'array',
        'browsers' => 'array',
        'oses' => 'array',
        'pages' => 'array',
        'referrers' => 'array',
        'utm_sources' => 'array'
    ];
}