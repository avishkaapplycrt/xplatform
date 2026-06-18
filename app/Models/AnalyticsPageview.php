<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsPageview extends Model
{
    protected $fillable = [
        'site_id', 'session_id', 'visitor_id', 'url', 'path',
        'title', 'referrer', 'referrer_domain', 'utm_source',
        'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'country', 'country_name', 'city', 'region',
        'latitude', 'longitude', 'device_type', 'browser',
        'browser_version', 'os', 'os_version', 'screen_width',
        'screen_height', 'load_time_ms', 'created_at'
    ];
    
    public $timestamps = false;
}