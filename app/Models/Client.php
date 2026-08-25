<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\AnalysisLayer;
use App\Models\DataSource;
use App\Models\Industry;
use App\Models\Prediction;
use App\Models\ClientPlatformMetric;
use App\Models\ClientLayerStat;
use App\Models\BehavioralProfile;
use App\Models\UserEvent;

class Client extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'company_name',
        'email',
        'size',
        'country',
        'industry_id', // ← fixed from 'industry'
        'password',
        'phone',
        'address',
        'status',
        'onboarding_dismissed_at',
        'timezone',
        'currency',
        'settings',
        'avatar',
        'logo',
        'plan',
        'trial_ends_at',
        'subscription_ends_at',
        'limits',
        'last_login_at',
        'last_login_ip',
        'website',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'       => 'datetime',
        'onboarding_dismissed_at' => 'datetime',
        'settings'             => 'json',
        'limits'               => 'json',
        'trial_ends_at'        => 'datetime',
        'subscription_ends_at' => 'datetime',
        'last_login_at'        => 'datetime',
        'password'             => 'hashed',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('trial_ends_at')
                  ->orWhere('trial_ends_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('subscription_ends_at')
                  ->orWhere('subscription_ends_at', '>', now());
            });
    }

    public function dataSources(): HasMany
    {
        return $this->hasMany(ClientDataSource::class);
    }

    public function activeDataSources(): HasMany
    {
        return $this->dataSources()->where('status', 'connected');
    }

    public function analyticsConfig(): HasOne
    {
        return $this->hasOne(AnalyticsConfig::class);
    }

    public function businessHealthSnapshot(): HasOne
    {
        return $this->hasOne(BusinessHealthSnapshot::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(AnalyticsMetric::class)->latest();
    }

    public function insights(): HasMany
    {
        return $this->hasMany(BusinessInsight::class)->latest();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class)->latest();
    }

    public function savedReports(): HasMany
    {
        return $this->hasMany(SavedReport::class);
    }

    public function getIsActiveAttribute(): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->trial_ends_at && $this->trial_ends_at->isPast()) return false;
        if ($this->subscription_ends_at && $this->subscription_ends_at->isPast()) return false;
        return true;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?? $this->email;
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function behavioralProfiles()
    {
        return $this->hasMany(BehavioralProfile::class);
    }

    public function userEvents()
    {
        return $this->hasMany(UserEvent::class);
    }

    public function analysisLayers()
    {
        return $this->belongsToMany(AnalysisLayer::class, 'client_analysis_layers')->withTimestamps();
    }

    public function selectedSources()
    {
        return $this->belongsToMany(DataSource::class, 'client_selected_sources')->withTimestamps();
    }

    public function predictions()
    {
        return $this->belongsToMany(Prediction::class, 'client_predictions')
            ->withPivot('industry_id')
            ->withTimestamps();
    }

    public function actions()
    {
        return $this->belongsToMany(Action::class, 'client_actions')->withTimestamps();
    }

    public function crmConnections(): HasMany
    {
        return $this->hasMany(CrmConnection::class);
    }

    public function platformMetrics(): HasMany
    {
        return $this->hasMany(ClientPlatformMetric::class);
    }

    public function latestPlatformMetric(): HasOne
    {
        return $this->hasOne(ClientPlatformMetric::class)->latestOfMany('recorded_date');
    }

    public function layerStats(): HasMany
    {
        return $this->hasMany(ClientLayerStat::class);
    }
}