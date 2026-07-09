<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailConnection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'email_connections';

    protected $fillable = [
        'client_id',
        'tenant_id',
        'platform',
        'api_key',
        'account_name',
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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function logs()
    {
        return $this->hasMany(EmailConnectionLog::class, 'connection_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }
}
