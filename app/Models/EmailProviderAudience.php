<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailProviderAudience extends Model
{
    protected $fillable = [
        'connection_id',
        'client_id',
        'tenant_id',
        'platform',
        'external_id',
        'name',
        'member_count',
        'open_rate',
        'click_rate',
    ];

    public function connection()
    {
        return $this->belongsTo(EmailConnection::class, 'connection_id');
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }
}
