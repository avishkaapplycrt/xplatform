<?php
// app/Models/AnalyticsSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsSession extends Model
{
    protected $fillable = [
        'site_id', 'session_id', 'visitor_id', 'first_page',
        'last_page', 'referrer', 'country', 'device_type',
        'browser', 'os', 'pageviews', 'duration_seconds',
        'is_bounce', 'started_at', 'ended_at'
    ];
    
    protected $casts = [
        'is_bounce' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];
}