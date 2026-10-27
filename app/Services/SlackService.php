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

        $synced     = 0;
        $read       = 0;
        $unreadable = [];
        foreach ($channels as $channel) {
            $count = $this->syncChannelMessages($channel, $token);
            if ($count === null) {
                $unreadable[] = $channel->name;
                continue;
            }
            $synced += $count;
            $read++;
        }

        // Which channels could be read, for the "Sync Now" message, plus the
        // three numbers on the connection card.
        $metrics = array_merge($connection->metrics ?? [], $this->cardMetrics($connection));
        $metrics['channels_read']       = $read;
        $metrics['channels_unreadable'] = $unreadable;
        $connection->update(['metrics' => $metrics]);

        return $synced;
    }

    /**
     * The connection card's numbers, from slack_messages:
     * - messages_today: messages posted today (app timezone)
     * - avg_response_time: seconds until someone else answers in the same
     *   channel (gaps over 24 h don't count as a reply)
     * - satisfaction_score: 0–10 tone of the latest messages, rated by
     *   OpenAI; Slack has no ratings, so without a key the old value stays
     */
    private function cardMetrics(ChatSupportIntegration $connection): array
    {
        $channelIds = SlackChannel::where('chat_support_integration_id', $connection->id)->pluck('id');
        $messages   = SlackMessage::whereIn('slack_channel_id', $channelIds)
            ->where('posted_at', '>=', now()->subDays(30))
            ->orderBy('posted_at')->get(['slack_channel_id', 'user_id', 'text', 'posted_at']);

        $gaps = [];
        foreach ($messages->groupBy('slack_channel_id') as $channelMessages) {
            $prev = null;
            foreach ($channelMessages as $m) {
                if ($prev && $m->user_id && $prev->user_id && $m->user_id !== $prev->user_id) {
                    $gap = $prev->posted_at->diffInSeconds($m->posted_at);
                    if ($gap <= 86400) {
                        $gaps[] = $gap;
                    }
                }
                $prev = $m;
            }
        }

        $metrics = [
            'messages_today'    => $messages->filter(fn ($m) => $m->posted_at->isToday())->count(),
            'messages_30d'      => $messages->count(),
            'avg_response_time' => $gaps ? (int) round(array_sum($gaps) / count($gaps)) : 0,
        ];

        // Re-rate the tone only when the rated messages changed (new, edited or deleted).
        $recent = $messages->sortByDesc('posted_at')->take(50);
        $print  = md5($recent->map(fn ($m) => $m->posted_at->timestamp . ':' . $m->text)->implode("
"));
        if ($recent->isEmpty()) {
            $metrics['satisfaction_score'] = 0;
        } elseif ($print !== ($connection->metrics['satisfaction_rated_for'] ?? null)) {
            $score = $this->rateSatisfaction($recent->pluck('text')->filter()->values()->all());
            if ($score !== null) {
                $metrics['satisfaction_score']     = $score;
                $metrics['satisfaction_rated_for'] = $print;
            }
        }

        return $metrics;
    }

    /** 0–10 (one decimal) for how satisfied the people in these messages sound, or null without OpenAI. */
    private function rateSatisfaction(array $texts): ?float
    {
        $client = app(\App\Services\Llm\OpenAiClient::class);
        if ($texts === [] || !$client->isConfigured()) {
            return null;
        }

        try {
            $answer = $client->chat(
                'You rate customer and team satisfaction in chat messages. Reply with one number from 0 to 10 '
                . '(0 = very unhappy, 5 = neutral, 10 = very happy) and nothing else.',
                "Messages, newest first:\n- " . implode("\n- ", array_map(fn ($t) => mb_substr($t, 0, 300), $texts)),
                ['max_tokens' => 5, 'temperature' => 0]
            );
        } catch (\Throwable $e) {
            Log::warning('Slack satisfaction rating failed', ['message' => $e->getMessage()]);
            return null;
        }

        return preg_match('/\d+(\.\d+)?/', $answer, $m) ? round(max(0, min(10, (float) $m[0])), 1) : null;
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
            // One row per channel per connection, so two clients can connect the same workspace.
            return SlackChannel::updateOrCreate(
                ['chat_support_integration_id' => $connection->id, 'channel_id' => $c['id']],
                [
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
     * Pulls the last 30 days of history for one channel. The bot must be a
     * member of a channel to read its history — on "not_in_channel" it joins
     * the (public) channel once and retries; that needs the channels:join
     * scope. Returns null when the channel can't be read at all.
     */
    private function syncChannelMessages(SlackChannel $channel, string $token): ?int
    {
        $synced = 0;
        $cursor  = null;
        $since   = now()->subDays(30);
        $oldest  = (string) $since->timestamp;
        $joined  = false;
        $seen    = [];   // ts of every message Slack still has in the window

        while (true) {
            $params = ['channel' => $channel->channel_id, 'oldest' => $oldest, 'limit' => 200];
            if ($cursor) {
                $params['cursor'] = $cursor;
            }

            $res = Http::withToken($token)->get(self::BASE_URL . '/conversations.history', $params);

            if ($res->json('error') === 'not_in_channel' && !$joined && !$channel->is_private) {
                $joined = true;
                $join   = Http::withToken($token)->asForm()->post(self::BASE_URL . '/conversations.join', ['channel' => $channel->channel_id]);
                if ($join->json('ok') === true) {
                    continue;   // retry the same page now that the bot is a member
                }
                Log::warning('Slack bot could not join channel (needs the channels:join scope, or invite it with /invite)', [
                    'channel' => $channel->name,
                    'error'   => $join->json('error'),
                ]);

                return null;
            }

            if (!$res->successful() || $res->json('ok') !== true) {
                Log::error('Slack conversations.history failed', [
                    'channel' => $channel->channel_id,
                    'error'   => $res->json('error'),
                ]);

                return $synced > 0 ? $synced : null;
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
                $seen[] = $m['ts'];
                $synced++;
            }

            $cursor = $res->json('response_metadata.next_cursor') ?: null;
            if (!$cursor) {
                break;
            }
        }

        // Only after the whole window was read: rows Slack no longer returns were
        // deleted there (deleted messages, or ones now hidden as system notices).
        $deleted = SlackMessage::where('slack_channel_id', $channel->id)
            ->where('posted_at', '>=', $since)
            ->whereNotIn('ts', $seen)
            ->delete();
        if ($deleted > 0) {
            Log::info('Slack messages removed after deletion in Slack', ['channel' => $channel->name, 'count' => $deleted]);
        }

        return $synced;
    }
}
