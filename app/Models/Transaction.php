<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'customer_id',
        'product_id',
        'amount',
        'quantity',
        'status',
        'payment_method',
        'transaction_reference',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'integer',
        'metadata' => 'array'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
