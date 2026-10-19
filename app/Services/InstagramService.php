<?php

namespace App\Services;

use App\Models\InstagramAccountInsight;
use App\Models\InstagramMedia;
use App\Models\InstagramMessage;
use App\Models\SocialIntegration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pulls real post + insight data for a connected Instagram account and
 * upserts it into instagram_media / instagram_account_insights.
 *
 * Mirrors HubSpotService's shape: a plain service with no queue awareness of
 * its own — SyncInstagramPosts (the job) owns connection status/error
 * tracking, this class only owns talking to the Instagram API and writing
 * rows.
 */
class InstagramService
{
    private const BASE_URL = 'https://graph.instagram.com/v21.0';

    /**
     * Refreshes profile counts, today's account-level insight snapshot, and
     * every post's own insight. Returns the number of posts synced.
     */
    public function syncPosts(SocialIntegration $connection): int
    {
        $token = $connection->access_token ? Crypt::decryptString($connection->access_token) : null;

        if (!$token) {
            throw new \RuntimeException('No Instagram access token stored for this connection.');
        }

        $followers = $this->syncProfile($connection, $token);
        $this->syncAccountInsight($connection, $token, $followers);

        $synced = $this->syncMedia($connection, $token);

        // DMs need instagram_business_manage_messages; a connection made before
        // that scope was added just logs a warning instead of failing the sync.
        $this->syncMessages($connection, $token);

        return $synced;
    }

    // ── Profile counts ───────────────────────────────────────────────────────

    private function syncProfile(SocialIntegration $connection, string $token): int
    {
        $res = Http::get(self::BASE_URL . '/me', [
            'fields'       => 'followers_count,media_count',
            'access_token' => $token,
        ]);

        $followers = $connection->metrics['followers'] ?? 0;

        if ($res->successful()) {
            $metrics = $connection->metrics ?? [];
            $followers = $res->json('followers_count') ?? $followers;
            $metrics['followers']   = $followers;
            $metrics['posts_count'] = $res->json('media_count') ?? ($metrics['posts_count'] ?? 0);
            $connection->update(['metrics' => $metrics]);
        } else {
            Log::error('Instagram profile fetch failed', [
                'connection_id' => $connection->id,
                'status'        => $res->status(),
                'body'          => $res->body(),
            ]);
        }

        return $followers;
    }

    // ── Account-level daily insight ──────────────────────────────────────────

    private function syncAccountInsight(SocialIntegration $connection, string $token, int $followers): void
    {
        // metric_type=total_value is required for profile_views (without it the
        // API silently leaves it out) and gives one total instead of a per-day list.
        $res = Http::get(self::BASE_URL . "/{$connection->account_id}/insights", [
            'metric'       => 'reach,profile_views',
            'period'       => 'day',
            'metric_type'  => 'total_value',
            'access_token' => $token,
        ]);

        if (!$res->successful()) {
            Log::error('Instagram account insights fetch failed', [
                'connection_id' => $connection->id,
                'status'        => $res->status(),
                'body'          => $res->body(),
            ]);
            return;
        }

        $byName = collect($res->json('data', []))->keyBy('name');

        InstagramAccountInsight::updateOrCreate(
            ['social_integration_id' => $connection->id, 'date' => now()->toDateString()],
            [
                'followers_count' => $followers,
                'reach'           => data_get($byName->get('reach'), 'total_value.value', 0),
                'profile_views'   => data_get($byName->get('profile_views'), 'total_value.value', 0),
            ]
        );
    }

    // ── Posts + per-post insight ─────────────────────────────────────────────

    /**
     * Pages through every post on the account (Graph API cursor pagination),
     * upserting each into instagram_media. Returns the count synced.
     */
    private function syncMedia(SocialIntegration $connection, string $token): int
    {
        $synced = 0;
        $after  = null;

        do {
            $params = [
                'fields'       => 'id,caption,media_type,permalink,timestamp,like_count,comments_count',
                'access_token' => $token,
                'limit'        => 50,
            ];
            if ($after) {
                $params['after'] = $after;
            }

            $res = Http::get(self::BASE_URL . '/me/media', $params);

            if (!$res->successful()) {
                Log::error('Instagram media fetch failed', [
                    'connection_id' => $connection->id,
                    'status'        => $res->status(),
                    'body'          => $res->body(),
                ]);
                break;
            }

            $data = $res->json();

            foreach ($data['data'] ?? [] as $item) {
                $this->upsertMedia($item, $connection, $token);
                $synced++;
            }

            $after = (!empty($data['paging']['next']) && !empty($data['paging']['cursors']['after']))
                ? $data['paging']['cursors']['after']
                : null;

        } while ($after);

        return $synced;
    }

    // ── Direct messages ──────────────────────────────────────────────────────

    /**
     * Pages through every DM conversation and upserts its messages into
     * instagram_messages. The API only exposes the 20 most recent messages of
     * each conversation, so older history builds up across repeated syncs.
     * Returns the number of messages synced.
     */
    public function syncMessages(SocialIntegration $connection, string $token): int
    {
        $synced = 0;
        $after  = null;
        $businessUsername = ltrim((string) $connection->username, '@');

        // Without this permission Instagram answers /me/conversations with an
        // empty list (not an error), so check what was granted at connect time.
        $granted = $connection->settings['permissions'] ?? [];
        if (!in_array('instagram_business_manage_messages', (array) $granted, true)) {
            Log::warning('Instagram DMs not synced: messages permission not granted — reconnect Instagram', [
                'connection_id' => $connection->id,
                'granted'       => $granted,
            ]);
            $metrics = $connection->metrics ?? [];
            $metrics['messages_synced']   = 0;
            $metrics['messages_access']   = false;
            $connection->update(['metrics' => $metrics]);
            return 0;
        }

        do {
            $params = ['platform' => 'instagram', 'fields' => 'id,updated_time', 'limit' => 50, 'access_token' => $token];
            if ($after) {
                $params['after'] = $after;
            }

            $res = Http::get(self::BASE_URL . '/me/conversations', $params);

            if (!$res->successful()) {
                Log::warning('Instagram conversations fetch failed (reconnect to grant instagram_business_manage_messages?)', [
                    'connection_id' => $connection->id,
                    'status'        => $res->status(),
                    'body'          => $res->body(),
                ]);
                break;
            }

            foreach ($res->json('data', []) as $conversation) {
                $synced += $this->syncConversation($connection, $token, $conversation['id'], $businessUsername);
            }

            $after = ($res->json('paging.next') && $res->json('paging.cursors.after'))
                ? $res->json('paging.cursors.after')
                : null;

        } while ($after);

        $metrics = $connection->metrics ?? [];
        $metrics['messages_synced'] = $synced;
        $metrics['messages_access'] = true;
        $connection->update(['metrics' => $metrics]);

        return $synced;
    }

    private function syncConversation(SocialIntegration $connection, string $token, string $conversationId, string $businessUsername): int
    {
        $res = Http::get(self::BASE_URL . "/{$conversationId}", ['fields' => 'messages', 'access_token' => $token]);

        if (!$res->successful()) {
            Log::warning('Instagram conversation fetch failed', [
                'connection_id'   => $connection->id,
                'conversation_id' => $conversationId,
                'body'            => $res->body(),
            ]);
            return 0;
        }

        $synced = 0;

        foreach ($res->json('messages.data', []) as $ref) {
            $msg = Http::get(self::BASE_URL . "/{$ref['id']}", [
                'fields'       => 'id,created_time,from,to,message,attachments',
                'access_token' => $token,
            ]);

            // Some message types reject the attachments field — retry without it.
            if (!$msg->successful()) {
                $msg = Http::get(self::BASE_URL . "/{$ref['id']}", [
                    'fields'       => 'id,created_time,from,to,message',
                    'access_token' => $token,
                ]);
            }

            if (!$msg->successful()) {
                Log::warning('Instagram message fetch failed', ['message_id' => $ref['id'], 'body' => $msg->body()]);
                continue;
            }

            $m      = $msg->json();
            $fromId = data_get($m, 'from.id');

            InstagramMessage::updateOrCreate(
                ['message_id' => $m['id']],
                [
                    'social_integration_id' => $connection->id,
                    'conversation_id'  => $conversationId,
                    'from_id'          => $fromId,
                    'from_username'    => data_get($m, 'from.username'),
                    'to_id'            => data_get($m, 'to.data.0.id'),
                    'to_username'      => data_get($m, 'to.data.0.username'),
                    'is_from_business' => $fromId === (string) $connection->account_id
                        || ($businessUsername !== '' && data_get($m, 'from.username') === $businessUsername),
                    'message'          => $m['message'] ?? null,
                    'attachments'      => data_get($m, 'attachments.data'),
                    'sent_at'          => isset($m['created_time']) ? \Carbon\Carbon::parse($m['created_time']) : null,
                    'synced_at'        => now(),
                ]
            );
            $synced++;
        }

        return $synced;
    }

    private function upsertMedia(array $item, SocialIntegration $connection, string $token): void
    {
        $insights = Http::get(self::BASE_URL . "/{$item['id']}/insights", [
            'metric'       => 'reach,saved',
            'access_token' => $token,
        ]);

        $pByName = collect($insights->json('data', []))->keyBy('name');

        InstagramMedia::updateOrCreate(
            ['media_id' => $item['id']],
            [
                'social_integration_id' => $connection->id,
                'media_type'      => $item['media_type'] ?? null,
                'caption'         => $item['caption'] ?? null,
                'permalink'       => $item['permalink'] ?? null,
                'posted_at'       => $item['timestamp'] ?? null,
                'like_count'      => $item['like_count'] ?? 0,
                'comments_count'  => $item['comments_count'] ?? 0,
                'reach'           => data_get($pByName->get('reach'), 'values.0.value', 0),
                'saved'           => data_get($pByName->get('saved'), 'values.0.value', 0),
                'synced_at'       => now(),
            ]
        );
    }
}
