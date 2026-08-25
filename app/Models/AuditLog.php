<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'client_id',
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'before',
        'after',
        'ip_address',
        'user_agent',
        'context',
        'created_at',
    ];

    protected $casts = [
        'before' => 'json',
        'after' => 'json',
        'context' => 'json',
        'created_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }
}