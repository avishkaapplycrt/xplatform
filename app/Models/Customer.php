<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'email',
        'phone',
        'is_active',
        'lifetime_value',
        'onboarding_workflow_id',
        'onboarding_status',
        'onboarding_started_at',
        'last_login_at',
        'support_tickets_count',
        'resolved_tickets_count',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lifetime_value' => 'decimal:2',
        'onboarding_started_at' => 'datetime',
        'last_login_at' => 'datetime',
        'support_tickets_count' => 'integer',
        'resolved_tickets_count' => 'integer',
        'metadata' => 'array'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function recentEvents()
    {
        return $this->hasMany(WebsiteEvent::class)->latest()->limit(10);
    }

    public function npsResponses()
    {
        return $this->hasMany(NpsResponse::class);
    }

    public function segments()
    {
        return $this->belongsToMany(CustomerSegment::class, 'customer_segment');
    }

    public function onboardingWorkflow()
    {
        return $this->belongsTo(OnboardingWorkflow::class, 'onboarding_workflow_id');
    }

    public function healthScores()
    {
        return $this->hasMany(CustomerHealthScore::class);
    }

    public function checkins()
    {
        return $this->hasMany(AutomatedCheckin::class);
    }
}
