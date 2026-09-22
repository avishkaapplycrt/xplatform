<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A customer as known by a connected payment gateway (currently Stripe) —
 * separate from the general Customer model, which is shared by unrelated
 * features (Customer Success, onboarding, health scores) and isn't synced
 * from any payment gateway.
 */
class TransactionCustomer extends Model
{
    protected $table = 'transactions_customers';

    protected $fillable = [
        'client_id',
        'gateway_customer_id',
        'gateway',
        'name',
        'email',
        'phone',
        'lifetime_value',
        'orders_count',
        'gateway_created_at',
    ];

    protected $casts = [
        'lifetime_value' => 'decimal:2',
        'orders_count' => 'integer',
        'gateway_created_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }
}
