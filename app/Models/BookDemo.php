<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookDemo extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'email', 'company_name', 'job_title',
        'company_size', 'industry', 'monthly_active_customers', 'monthly_revenue',
        'primary_challenge', 'data_sources', 'demo_notes',
        'demo_date', 'demo_time', 'timezone', 'status',
    ];

    protected $casts = [
        'demo_date' => 'date',
    ];
}
