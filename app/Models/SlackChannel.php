<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;

class SlackChannel extends Model
{
    // Belongs to the client who owns the Slack connection it was synced from (client_id).
    use BelongsToClient;

    /** Owner of the Slack connection it was synced from. */
    public function clientIdFromParent(): ?int
    {
        $id = ChatSupportIntegration::withoutGlobalScopes()->whereKey($this->chat_support_integration_id)->value('client_id');

        return $id === null ? null : (int) $id;
    }

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
