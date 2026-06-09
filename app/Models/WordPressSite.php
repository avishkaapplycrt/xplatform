<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class WordPressSite extends Model
{
    protected $table = 'wordpress_sites'; // Add this line
    
    protected $fillable = [
        'client_id',
        'site_name',
        'site_url',
        'site_id',
        'api_type',
        'auth_type',
        'auth_credentials',
        'connection_config',
        'is_active',
        'last_sync_at',
        'sync_frequency',
    ];

    protected $casts = [
        'connection_config' => 'array',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Get decrypted credentials
     */
    public function getDecryptedCredentialsAttribute(): array
    {
        if (empty($this->auth_credentials)) {
            return [];
        }
        return json_decode(Crypt::decryptString($this->auth_credentials), true) ?? [];
    }

    /**
     * Set encrypted credentials - accepts array or string
     */
    public function setAuthCredentialsAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['auth_credentials'] = Crypt::encryptString(json_encode($value));
        } elseif (is_string($value)) {
            // If already encrypted or raw string, store as-is or encrypt
            $this->attributes['auth_credentials'] = Crypt::encryptString($value);
        } else {
            $this->attributes['auth_credentials'] = null;
        }
    }

    public function events(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class, 'site_id');
    }

    public function aggregates(): HasMany
    {
        return $this->hasMany(AnalyticsAggregate::class, 'site_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function isWebhook(): bool
    {
        return $this->api_type === 'webhook';
    }

    public function isRestPoll(): bool
    {
        return $this->api_type === 'rest_poll';
    }

    public function isDbDirect(): bool
    {
        return $this->api_type === 'db_direct';
    }

    public function hasWooCommerce(): bool
    {
        return $this->connection_config['has_woocommerce'] ?? false;
    }
}