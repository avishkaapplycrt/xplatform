<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrevoDeliveredRecipient extends Model
{
    protected $table = 'email_logs_brevo';

    protected $fillable = [
        'client_id',
        'campaign_id',
        'email',
        'name',
        'delivered_at',
        'opened_at',
        'clicked',
        'unsubscribed_at',
    ];

    protected $casts = [
        'delivered_at'    => 'datetime',
        'opened_at'       => 'datetime',
        'clicked'         => 'boolean',
        'unsubscribed_at' => 'datetime',
    ];
}
