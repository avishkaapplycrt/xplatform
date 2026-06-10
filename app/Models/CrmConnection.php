<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class CrmConnection extends Model
{
    protected $fillable = [
        'client_id',
        'provider',
        'account_name',
        'hub_domain',
        'hub_id',
        'instance_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'status',
        'last_error',
        'last_sync_at',
        'contacts_synced',
    ];

    protected $casts = [
        'scopes'           => 'json',
        'token_expires_at' => 'datetime',
        'last_sync_at'     => 'datetime',
    ];

    public function setAccessTokenAttribute(string $value): void
    {
        $this->attributes['access_token'] = Crypt::encryptString($value);
    }

    public function getAccessTokenAttribute(string $value): string
    {
        return Crypt::decryptString($value);
    }

    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getRefreshTokenAttribute(?string $value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public function providerLabel(): string
    {
        return match ($this->provider) {
            'hubspot'    => 'HubSpot',
            'zoho'       => 'Zoho CRM',
            'salesforce' => 'Salesforce',
            'pipedrive'  => 'Pipedrive',
            default      => ucfirst($this->provider),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'connected' => 'emerald',
            'syncing'   => 'blue',
            'error'     => 'red',
            default     => 'gray',
        };
    }
}
