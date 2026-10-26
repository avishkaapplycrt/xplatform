<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlackChannel extends Model
{
    protected $fillable = [
        'chat_support_integration_id', 'channel_id', 'name',
        'is_private', 'is_archived', 'member_count', 'synced_at',
    ];

    protected $casts = [
        'is_private'   => 'boolean',
        'is_archived'  => 'boolean',
        'synced_at'    => 'datetime',
    ];

    public function integration()
    {
        return $this->belongsTo(ChatSupportIntegration::class, 'chat_support_integration_id');
    }

    public function messages()
    {
        return $this->hasMany(SlackMessage::class);
    }
}
