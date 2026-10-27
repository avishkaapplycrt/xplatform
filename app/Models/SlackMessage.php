<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;

class SlackMessage extends Model
{
    // Belongs to the client who owns the Slack channel it was synced from (client_id).
    use BelongsToClient;

    /** Owner of the Slack channel it was synced from. */
    public function clientIdFromParent(): ?int
    {
        $id = SlackChannel::withoutGlobalScopes()->whereKey($this->slack_channel_id)->value('client_id');

        return $id === null ? null : (int) $id;
    }

    protected $fillable = [
        'slack_channel_id', 'ts', 'user_id', 'text',
        'reply_count', 'reaction_count', 'posted_at', 'synced_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(SlackChannel::class, 'slack_channel_id');
    }

    // replies + reactions — a simple engagement proxy, same shape as the
    // Instagram post engagement_rate accessor.
    public function getEngagementAttribute(): int
    {
        return $this->reply_count + $this->reaction_count;
    }
}
