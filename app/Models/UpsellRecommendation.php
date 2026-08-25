<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpsellRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'customer_id',
        'product_id',
        'original_product_id',
        'strategy',
        'confidence_score',
        'expected_revenue',
        'message',
        'status',
        'executed_at'
    ];

    protected $casts = [
        'confidence_score' => 'integer',
        'expected_revenue' => 'decimal:2',
        'executed_at' => 'datetime',
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function originalProduct()
    {
        return $this->belongsTo(Product::class, 'original_product_id');
    }
}
