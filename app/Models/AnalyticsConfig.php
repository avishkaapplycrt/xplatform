<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsConfig extends Model
{
    protected $fillable = [
        'client_id',
        'data_source_id',
        'widget_layout',
        'kpis',
        'alert_rules',
        'realtime_mode',
        'refresh_interval',
        'ai_insights_enabled',
        'insight_preferences',
    ];

    protected $casts = [
        'widget_layout' => 'json',
        'kpis' => 'json',
        'alert_rules' => 'json',
        'insight_preferences' => 'json',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}