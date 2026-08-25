<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'steps',
        'is_active'
    ];

    protected $casts = [
        'steps' => 'array',
        'is_active' => 'boolean'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'onboarding_workflow_id');
    }
}
