<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteEventsProviderHeader extends Model
{
    protected $table = 'website_events_provider_header';

    protected $fillable = [
        'client_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'customer_status',
        'created_date',
        'last_order_date',
        'total_orders',
        'total_spent',
        'average_order_value',
        'days_since_last_order',
        'marketing_opt_in',
        'number_of_products_purchased',
        'default_country',
        'default_city',
        'tags',
        'customer_note',
        'x_platforms_risk_score',
        'x_platforms_risk_level',
    ];

    protected $casts = [
        'created_date'         => 'datetime',
        'last_order_date'      => 'datetime',
        'total_spent'          => 'decimal:2',
        'average_order_value'  => 'decimal:2',
        'marketing_opt_in'     => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function details()
    {
        return $this->hasMany(WebsiteEventsProviderDetail::class, 'header_id');
    }
}
