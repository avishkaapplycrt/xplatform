<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerHealthScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'customer_id',
        'score',
        'engagement_score',
        'transaction_score',
        'support_score',
        'nps_score',
        'status',
        'recommendations',
        'calculated_at'
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'engagement_score' => 'decimal:2',
        'transaction_score' => 'decimal:2',
        'support_score' => 'decimal:2',
        'nps_score' => 'decimal:2',
        'recommendations' => 'array',
        'calculated_at' => 'datetime'
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
