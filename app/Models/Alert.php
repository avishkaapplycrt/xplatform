<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'rule_id', 'customer_id', 'metric', 'threshold_value',
        'actual_value', 'message', 'status', 'priority', 'acknowledged_at', 'resolved_at'
    ];

    protected $casts = [
        'threshold_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function rule()
    {
        return $this->belongsTo(AlertRule::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}