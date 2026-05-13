<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MicroSignal extends Model
{
    protected $fillable = [
        'micro_signal_category_id',
        'industry_id',
        'name',
        'slug',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MicroSignalCategory::class, 'micro_signal_category_id');
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function analyticsConfigs(): BelongsToMany
    {
        return $this->belongsToMany(
            AnalyticsConfig::class,
            'analytics_config_micro_signal'
        )->withTimestamps();
    }
}
