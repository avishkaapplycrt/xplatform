<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class LaravelSite extends Model
{
    protected $table = 'laravel_sites';

    protected $fillable = [
        'client_id',
        'site_name',
        'site_url',
        'site_id',
        'api_type',
        'auth_credentials',
        'connection_config',
        'sync_frequency',
        'is_active',
        'last_sync_at',
    ];

    protected $casts = [
        'connection_config' => 'array',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    public function getDecryptedCredentialsAttribute(): array
    {
        if (empty($this->auth_credentials)) {
            return [];
        }
        return json_decode(Crypt::decryptString($this->auth_credentials), true) ?? [];
    }

    public function setAuthCredentialsAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['auth_credentials'] = Crypt::encryptString(json_encode($value));
        } elseif (is_string($value)) {
            $this->attributes['auth_credentials'] = Crypt::encryptString($value);
        } else {
            $this->attributes['auth_credentials'] = null;
        }
    }

    public function events(): HasMany
    {
        return $this->hasMany(LaravelEvent::class, 'site_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}