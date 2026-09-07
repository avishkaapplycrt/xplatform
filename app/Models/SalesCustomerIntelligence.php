<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesCustomerIntelligence extends Model
{
    protected $table = 'sales_customer_intelligence';

    protected $fillable = [
        'client_id',
        'connection_id',
        'crm_contact_id',
        'external_contact_id',
        'email',
        'first_name',
        'last_name',
        'company',
        'lifecycle_stage',
        'lead_status',
        'last_activity_at',
        'deal_count',
        'total_deal_value',
        'highest_deal_value',
        'current_deal_stage',
        'nearest_close_date',
        'emails_delivered',
        'emails_opened',
        'emails_clicked',
        'last_email_delivered_at',
        'last_email_opened_at',
        'last_email_clicked_at',
        'unsubscribed_at',
        'crm_score',
        'email_engagement_score',
        'sales_priority_score',
        'buying_intent_score',
        'priority_level',
        'recommended_action',
        'intelligence_updated_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'nearest_close_date' => 'datetime',
        'last_email_delivered_at' => 'datetime',
        'last_email_opened_at' => 'datetime',
        'last_email_clicked_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'intelligence_updated_at' => 'datetime',
    ];
}
