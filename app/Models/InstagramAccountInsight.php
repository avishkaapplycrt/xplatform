<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstagramAccountInsight extends Model
{
    protected $fillable = [
        'social_integration_id', 'date', 'followers_count', 'reach', 'profile_views',
    ];

    protected $casts = ['date' => 'date'];

    public function socialIntegration()
    {
        return $this->belongsTo(SocialIntegration::class);
    }
}
