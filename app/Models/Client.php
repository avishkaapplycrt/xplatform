<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'company_name',
        'email',
        'password',
        'phone',
        'address',
        'status',
        'timezone',
        'currency',
        'settings',
        'avatar',
        'plan',
        'trial_ends_at',
        'subscription_ends_at',
        'limits',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'settings' => 'json',
        'limits' => 'json',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
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
}