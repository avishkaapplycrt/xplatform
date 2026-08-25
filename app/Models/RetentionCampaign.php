<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetentionCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'name', 'trigger_type', 'threshold_score',
        'email_template_id', 'sms_template', 'is_active', 'last_triggered_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
}