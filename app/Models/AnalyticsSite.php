<?php
// app/Models/AnalyticsSite.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsSite extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'client_id', 'domain', 'name', 'tracking_id', 
        'api_key', 'is_active', 'settings'
    ];
    
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean'
    ];
    
    public function pageviews(): HasMany
    {
        return $this->hasMany(AnalyticsPageview::class, 'site_id');
    }
    
    public function sessions(): HasMany
    {
        return $this->hasMany(AnalyticsSession::class, 'site_id');
    }
    
    public function hourlyStats(): HasMany
    {
        return $this->hasMany(AnalyticsHourlyStat::class, 'site_id');
    }
    
    public function dailyStats(): HasMany
    {
        return $this->hasMany(AnalyticsDailyStat::class, 'site_id');
    }
    
    public function generateTrackingId(): string
    {
        return 'st_' . substr(md5(uniqid() . $this->client_id), 0, 16); // Total 19 chars
    }
    
    public function generateApiKey(): string
    {
        return 'ak_' . substr(bin2hex(random_bytes(16)), 0, 30); // Total 33 chars (within 64 limit)
    }
}