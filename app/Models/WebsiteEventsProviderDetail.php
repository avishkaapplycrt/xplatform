<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteEventsProviderDetail extends Model
{
    protected $table = 'website_events_provider_detail';

    protected $fillable = [
        'header_id',
        'order_id',
        'order_date',
        'order_status',
        'financial_status',
        'order_total',
        'currency',
        'product_name',
        'product_id',
        'sku',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'shipping',
        'payment_method',
        'fulfillment_status',
        'shipping_city',
        'shipping_country',
        'product_category',
    ];

    protected $casts = [
        'order_date'   => 'datetime',
        'order_total'  => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax'          => 'decimal:2',
        'shipping'     => 'decimal:2',
    ];

    public function header()
    {
        return $this->belongsTo(WebsiteEventsProviderHeader::class, 'header_id');
    }
}
