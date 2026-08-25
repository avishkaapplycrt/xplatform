<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGatewayConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'gateway_name',
        'display_name',
        'api_key',
        'api_secret',
        'webhook_secret',
        'account_id',
        'merchant_id',
        'environment',
        'currency',
        'webhook_url',
        'settings',
        'is_active',
        'is_connected',
        'connected_at',
        'last_synced_at',
        'notes',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_connected' => 'boolean',
        'connected_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeConnected($query)
    {
        return $query->where('is_connected', true);
    }

    public function scopeForGateway($query, string $gatewayName)
    {
        return $query->where('gateway_name', $gatewayName);
    }
}
