<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebsiteConnection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'website_connections';

    protected $fillable = [
        'client_id',
        'tenant_id',
        'platform',
        'site_url',
        'site_name',
        'tracking_code',
        'api_key',
        'settings',
        'status',
        'connected_at',
        'last_sync_at',
    ];

    protected $casts = [
        'settings'     => 'array',
        'connected_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Get the client that owns this connection.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get connection logs.
     */
    public function logs()
    {
        return $this->hasMany(ConnectionLog::class, 'connection_id');
    }

    /**
     * Scope for active connections.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for a specific platform.
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }
}
