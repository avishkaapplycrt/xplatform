<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomatedCheckin extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'customer_id',
        'template',
        'scheduled_at',
        'sent_at',
        'subject',
        'message',
        'channel',
        'status',
        'metadata'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'metadata' => 'array',
        'is_active' => 'boolean'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
