<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstagramMessage extends Model
{
    protected $fillable = [
        'social_integration_id', 'message_id', 'conversation_id',
        'from_id', 'from_username', 'to_id', 'to_username',
        'is_from_business', 'message', 'attachments', 'sent_at', 'synced_at',
    ];

    protected $casts = [
        'is_from_business' => 'boolean',
        'attachments'      => 'array',
        'sent_at'          => 'datetime',
        'synced_at'        => 'datetime',
    ];

    public function socialIntegration()
    {
        return $this->belongsTo(SocialIntegration::class);
    }
}
