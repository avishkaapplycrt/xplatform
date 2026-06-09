<?php
// app/Services/WordPressPollingService.php
namespace App\Services;

use App\Models\WordPressSite;
use App\Models\AnalyticsEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WordPressPollingService
{
    private array $errors = [];

    public function pollSite(WordPressSite $site): array
    {
        if (!$site->isRestPoll() || !$site->is_active) {
            return ['skipped' => true, 'reason' => 'Not configured for polling'];
        }

        $credentials = $site->decrypted_credentials;
        $config = $site->connection_config;
        $results = [
            'site_id' => $site->id,
            'site_name' => $site->site_name,
            'events_fetched' => 0,
            'events_stored' => 0,
            'errors' => [],
        ];

        try {
            // 1. Poll Posts/Content
            $postsResult = $this->pollPosts($site, $credentials, $config);
            $results['events_stored'] += $postsResult['stored'];

            // 2. Poll Users
            $usersResult = $this->pollUsers($site, $credentials, $config);
            $results['events_stored'] += $usersResult['stored'];

            // 3. Poll WooCommerce (if enabled)
            if ($site->hasWooCommerce()) {
                $wcResult = $this->pollWooCommerce($site, $credentials, $config);
                $results['events_stored'] += $wcResult['stored'];
                $results['woocommerce'] = $wcResult;
            }

            // 4. Poll Comments (engagement)
            $commentsResult = $this->pollComments($site, $credentials, $config);
            $results['events_stored'] += $commentsResult['stored'];

            // 5. Poll WP Statistics plugin (if available)
            $statsResult = $this->pollStatistics($site, $credentials, $config);
            $results['events_stored'] += $statsResult['stored'];

            $site->update(['last_sync_at' => now()]);

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error("WP Poll failed for {$site->site_name}: " . $e->getMessage());
        }

        return $results;
    }

    // ─── HTTP Client Builder ───

    private function buildClient(WordPressSite $site, array $credentials): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(30)->withOptions([
            'verify' => $site->connection_config['ssl_verify'] ?? true,
        ]);

        switch ($site->auth_type) {
            case 'application_password':
                // WordPress 5.6+ Application Passwords
                $client = $client->withBasicAuth(
                    $credentials['username'],
                    $credentials['app_password']
                );
                break;

            case 'bearer':
                $client = $client->withToken($credentials['token']);
                break;

            case 'basic':
                $client = $client->withBasicAuth(
                    $credentials['username'],
                    $credentials['password']
                );
                break;

            case 'jwt':
                $token = $this->getJwtToken($site, $credentials);
                $client = $client->withToken($token, 'Bearer');
                break;
        }

        return $client;
    }

    private function getJwtToken(WordPressSite $site, array $credentials): string
    {
        $response = Http::post("{$site->site_url}/wp-json/jwt-auth/v1/token", [
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ]);

        return $response->json('token');
    }

    // ─── Posts/Content Polling ───

    private function pollPosts(WordPressSite $site, array $credentials, array $config): array
    {
        $since = $site->last_sync_at ?? Carbon::now()->subDays(7);
        $page = 1;
        $stored = 0;

        do {
            $response = $this->buildClient($site, $credentials)
                ->get("{$site->site_url}/wp-json/wp/v2/posts", [
                    'per_page' => 100,
                    'page' => $page,
                    'after' => $since->toIso8601String(),
                    'orderby' => 'modified',
                    'order' => 'asc',
                    '_embed' => true,
                ]);

            if (!$response->successful()) {
                throw new \Exception("Posts API failed: " . $response->body());
            }

            $posts = $response->json();
            if (empty($posts)) break;

            $events = [];
            foreach ($posts as $post) {
                $events[] = [
                    'site_id' => $site->id,
                    'event_type' => $post['modified'] === $post['date'] ? 'post_publish' : 'post_update',
                    'wp_entity_id' => $post['id'],
                    'payload' => json_encode([
                        'post_id' => $post['id'],
                        'title' => $post['title']['rendered'],
                        'slug' => $post['slug'],
                        'status' => $post['status'],
                        'type' => $post['type'],
                        'author_id' => $post['author'],
                        'categories' => array_column($post['_embedded']['wp:term'][0] ?? [], 'name'),
                        'tags' => array_column($post['_embedded']['wp:term'][1] ?? [], 'name'),
                        'featured_media' => $post['featured_media'],
                        'comment_count' => $post['comment_count'] ?? 0,
                        'word_count' => str_word_count(strip_tags($post['content']['rendered'])),
                        'excerpt' => strip_tags($post['excerpt']['rendered']),
                        'link' => $post['link'],
                        'seo_title' => $post['yoast_head_json']['title'] ?? null,
                        'seo_description' => $post['yoast_head_json']['description'] ?? null,
                    ]),
                    'wp_created_at' => Carbon::parse($post['modified'])->toDateTimeString(),
                    'synced_at' => now(),
                ];
            }

            AnalyticsEvent::insert($events);
            $stored += count($events);
            $page++;

        } while (count($posts) === 100);

        return ['stored' => $stored];
    }

    // ─── Users Polling ───

    private function pollUsers(WordPressSite $site, array $credentials, array $config): array
    {
        $since = $site->last_sync_at ?? Carbon::now()->subDays(7);
        $page = 1;
        $stored = 0;

        do {
            $response = $this->buildClient($site, $credentials)
                ->get("{$site->site_url}/wp-json/wp/v2/users", [
                    'per_page' => 100,
                    'page' => $page,
                    'orderby' => 'registered',
                    'order' => 'asc',
                ]);

            if (!$response->successful()) {
                if ($response->status() === 403) {
                    return ['stored' => 0, 'note' => 'Users endpoint requires auth'];
                }
                throw new \Exception("Users API failed: " . $response->body());
            }

            $users = $response->json();
            if (empty($users)) break;

            $events = [];
            foreach ($users as $user) {
                $registered = Carbon::parse($user['registered_date'] ?? $user['registered']);
                
                if ($registered->greaterThan($since)) {
                    $events[] = [
                        'site_id' => $site->id,
                        'event_type' => 'user_registration',
                        'wp_entity_id' => $user['id'],
                        'payload' => json_encode([
                            'wp_user_id' => $user['id'],
                            'username' => $user['name'],
                            'email' => $user['email'] ?? null,
                            'role' => $user['roles'][0] ?? 'subscriber',
                            'avatar' => $user['avatar_urls']['96'] ?? null,
                            'link' => $user['link'],
                        ]),
                        'wp_created_at' => $registered->toDateTimeString(),
                        'synced_at' => now(),
                    ];
                }
            }

            if (!empty($events)) {
                AnalyticsEvent::insert($events);
                $stored += count($events);
            }
            $page++;

        } while (count($users) === 100);

        return ['stored' => $stored];
    }

    // ─── Comments Polling ───

    private function pollComments(WordPressSite $site, array $credentials, array $config): array
    {
        $since = $site->last_sync_at ?? Carbon::now()->subDays(7);
        
        $response = $this->buildClient($site, $credentials)
            ->get("{$site->site_url}/wp-json/wp/v2/comments", [
                'per_page' => 100,
                'after' => $since->toIso8601String(),
                'orderby' => 'date',
                'order' => 'asc',
            ]);

        if (!$response->successful()) {
            return ['stored' => 0, 'note' => 'Comments endpoint unavailable'];
        }

        $comments = $response->json();
        $events = [];

        foreach ($comments as $comment) {
            $events[] = [
                'site_id' => $site->id,
                'event_type' => 'comment',
                'wp_entity_id' => $comment['id'],
                'payload' => json_encode([
                    'comment_id' => $comment['id'],
                    'post_id' => $comment['post'],
                    'author_name' => $comment['author_name'],
                    'author_email' => $comment['author_email'],
                    'author_url' => $comment['author_url'],
                    'content' => strip_tags($comment['content']['rendered']),
                    'status' => $comment['status'],
                    'parent' => $comment['parent'],
                    'link' => $comment['link'],
                ]),
                'wp_created_at' => Carbon::parse($comment['date'])->toDateTimeString(),
                'synced_at' => now(),
            ];
        }

        if (!empty($events)) {
            AnalyticsEvent::insert($events);
        }

        return ['stored' => count($events)];
    }

    // ─── WP Statistics Plugin Polling ───

    private function pollStatistics(WordPressSite $site, array $credentials, array $config): array
    {
        // Check if WP Statistics plugin is available
        $response = $this->buildClient($site, $credentials)
            ->get("{$site->site_url}/wp-json/wp-statistics/v1/summary");

        if (!$response->successful()) {
            return ['stored' => 0, 'note' => 'WP Statistics plugin not installed'];
        }

        $stats = $response->json();
        
        $events = [[
            'site_id' => $site->id,
            'event_type' => 'site_stats',
            'wp_entity_id' => null,
            'payload' => json_encode([
                'total_visitors' => $stats['visitors'] ?? 0,
                'total_views' => $stats['visits'] ?? 0,
                'today_visitors' => $stats['visitors_today'] ?? 0,
                'today_views' => $stats['visits_today'] ?? 0,
                'yesterday_visitors' => $stats['visitors_yesterday'] ?? 0,
                'yesterday_views' => $stats['visits_yesterday'] ?? 0,
                'total_posts' => $stats['posts'] ?? 0,
                'total_pages' => $stats['pages'] ?? 0,
                'total_comments' => $stats['comments'] ?? 0,
                'total_users' => $stats['users'] ?? 0,
            ]),
            'wp_created_at' => now()->toDateTimeString(),
            'synced_at' => now(),
        ]];

        AnalyticsEvent::insert($events);

        return ['stored' => 1];
    }
}