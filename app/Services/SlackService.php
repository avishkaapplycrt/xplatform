<?php

namespace App\Services;

use App\Models\ChatSupportIntegration;
use App\Models\SlackChannel;
use App\Models\SlackMessage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pulls real channel + message data for a connected Slack workspace and
 * upserts it into slack_channels / slack_messages.
 *
 * Same shape as InstagramService: a plain service with no queue awareness
 * of its own — SyncSlackMessages (the job) owns connection status/error
 * tracking, this class only owns talking to the Slack Web API and writing
 * rows.
 */
class SlackService
{
    private const BASE_URL = 'https://slack.com/api';

    /**
     * Syncs every channel the bot can see, and the recent message history
     * for each. Returns the number of messages synced.
     */
    public function syncMessages(ChatSupportIntegration $connection): int
    {
        $token = $this->resolveToken($connection);

        if (!$token) {
            throw new \RuntimeException('No usable Slack access token — neither SLACK_BOT_TOKEN in .env nor a decryptable stored token.');
        }

        $channels = $this->syncChannels($connection, $token);

        $synced = 0;
        foreach ($channels as $channel) {
            $synced += $this->syncChannelMessages($channel, $token);
        }

        return $synced;
    }

    /**
     * Prefers the bot token configured directly in .env (SLACK_BOT_TOKEN) —
     * a fixed, single-workspace token that skips the OAuth dance entirely.
     * Falls back to the per-connection token saved during OAuth, in case
     * that one is still decryptable (it can stop being so if APP_KEY is
     * ever rotated after the token was encrypted — that's a real, separate
     * gap to fix if OAuth reconnection ever matters here).
     */
    private function resolveToken(ChatSupportIntegration $connection): ?string
    {
        $envToken = config('services.slack.notifications.bot_user_oauth_token');
        if (!empty($envToken)) {
            return $envToken;
        }

        if (empty($connection->access_token)) {
            return null;
        }

        try {
            return Crypt::decryptString($connection->access_token);
        } catch (\Throwable $e) {
            Log::warning('Stored Slack access_token could not be decrypted (APP_KEY likely rotated since it was saved) — set SLACK_BOT_TOKEN in .env instead.', [
                'connection_id' => $connection->id,
            ]);
            return null;
        }
    }

    // ── Channels ──────────────────────────────────────────────────────────

    /** @return \Illuminate\Support\Collection<SlackChannel> */
    private function syncChannels(ChatSupportIntegration $connection, string $token): \Illuminate\Support\Collection
    {
        // Public channels only — the bot token currently only has
        // channels:read/channels:history. Private channels need groups:read
        // (+ groups:history for messages) added in the Slack app's OAuth
        // scopes and the app reinstalled to the workspace before
        // 'private_channel' can be added back to this types list.
        $res = Http::withToken($token)->get(self::BASE_URL . '/conversations.list', [
            'types' => 'public_channel',
            'limit' => 200,
        ]);

        if (!$res->successful() || $res->json('ok') !== true) {
            Log::error('Slack conversations.list failed', [
                'connection_id' => $connection->id,
                'status'        => $res->status(),
                'body'          => $res->body(),
            ]);
            return collect();
        }

        return collect($res->json('channels', []))->map(function ($c) use ($connection) {
            return SlackChannel::updateOrCreate(
                ['channel_id' => $c['id']],
                [
                    'chat_support_integration_id' => $connection->id,
                    'name'         => $c['name'] ?? null,
                    'is_private'   => $c['is_private'] ?? false,
                    'is_archived'  => $c['is_archived'] ?? false,
                    'member_count' => $c['num_members'] ?? 0,
                    'synced_at'    => now(),
                ]
            );
        });
    }

    // ── Messages ──────────────────────────────────────────────────────────

    /**
     * Pulls the last 30 days of history for one channel. The bot must
     * actually be a member of a channel to read its history — Slack
     * returns ok:false ("not_in_channel") otherwise, which is logged and
     * skipped rather than failing the whole sync.
     */
    private function syncChannelMessages(SlackChannel $channel, string $token): int
    {
        $synced = 0;
        $cursor  = null;
        $oldest  = (string) now()->subDays(30)->timestamp;

        do {
            $params = ['channel' => $channel->channel_id, 'oldest' => $oldest, 'limit' => 200];
            if ($cursor) {
                $params['cursor'] = $cursor;
            }

            $res = Http::withToken($token)->get(self::BASE_URL . '/conversations.history', $params);

            if (!$res->successful() || $res->json('ok') !== true) {
                if ($res->json('error') !== 'not_in_channel') {
                    Log::error('Slack conversations.history failed', [
                        'channel' => $channel->channel_id,
                        'error'   => $res->json('error'),
                    ]);
                }
                break;
            }

            foreach ($res->json('messages', []) as $m) {
                // Skip channel-join/leave system messages — not real conversation.
                if (!empty($m['subtype']) && $m['subtype'] !== 'bot_message') {
                    continue;
                }

                SlackMessage::updateOrCreate(
                    ['slack_channel_id' => $channel->id, 'ts' => $m['ts']],
                    [
                        'user_id'        => $m['user'] ?? null,
                        'text'           => $m['text'] ?? null,
                        'reply_count'    => $m['reply_count'] ?? 0,
                        'reaction_count' => collect($m['reactions'] ?? [])->sum('count'),
                        'posted_at'      => \Carbon\Carbon::createFromTimestamp((float) $m['ts']),
                        'synced_at'      => now(),
                    ]
                );
                $synced++;
            }

            $cursor = $res->json('response_metadata.next_cursor') ?: null;
        } while ($cursor);

        return $synced;
    }
}
