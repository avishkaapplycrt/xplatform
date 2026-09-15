<?php

namespace App\Services;

use App\Models\InstagramAccountInsight;
use App\Models\InstagramMedia;
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

        return $this->syncMedia($connection, $token);
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
        $res = Http::get(self::BASE_URL . "/{$connection->account_id}/insights", [
            'metric'       => 'reach,profile_views',
            'period'       => 'day',
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
                'reach'           => data_get($byName->get('reach'), 'values.0.value', 0),
                'profile_views'   => data_get($byName->get('profile_views'), 'values.0.value', 0),
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
