<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AnalyticsConfig extends Model
{
    protected $fillable = [
        'client_id',
        'industry_id',
        'config_name',
        'status',
        'widget_layout',
        'kpis',
        'alert_rules',
        'realtime_mode',
        'refresh_interval',
        'ai_insights_enabled',
        'insight_preferences',
    ];

    protected $casts = [
        'widget_layout'       => 'json',
        'kpis'                => 'json',
        'alert_rules'         => 'json',
        'insight_preferences' => 'json',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function microSignals(): BelongsToMany
    {
        return $this->belongsToMany(
            MicroSignal::class,
            'analytics_config_micro_signal'
        )->withTimestamps();
    }
}
