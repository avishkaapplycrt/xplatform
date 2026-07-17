<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SocialIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SocialConnectionController extends Controller
{
    /**
     * Display the Social Media connections listing page.
     */
    public function index()
    {
        $connections = SocialIntegration::all()->keyBy('platform');
        $platforms   = SocialIntegration::platforms();

        // Calculate stats
        $totalConnected = $connections->where('status', 'connected')->count();
        $totalPlatforms = count($platforms);
        $syncToday      = $connections->where('last_sync_at', '>=', now()->startOfDay())->count();
        $lastSync       = $connections->whereNotNull('last_sync_at')->max('last_sync_at');

        // Total followers across all connected platforms
        $totalFollowers = $connections->where('status', 'connected')->sum(function ($c) {
            return $c->metrics['followers'] ?? 0;
        });

        // Sync health status
        $syncHealth = 'healthy';
        if ($totalConnected > 0) {
            $expiredCount = $connections->where('status', 'connected')->filter(function ($c) {
                return $c->is_expired;
            })->count();
            if ($expiredCount > 0) {
                $syncHealth = $expiredCount === $totalConnected ? 'critical' : 'warning';
            }
        }

        return view('client.social-connections', compact(
            'connections',
            'platforms',
            'totalConnected',
            'totalPlatforms',
            'syncToday',
            'lastSync',
            'syncHealth',
            'totalFollowers'
        ));
    }

    /**
     * Show the connect form for a specific platform.
     */
    public function create(string $platform)
    {
        $platforms = SocialIntegration::platforms();

        if (!isset($platforms[$platform])) {
            abort(404, 'Social media platform not found.');
        }

        $meta = $platforms[$platform];
        $existing = SocialIntegration::byPlatform($platform)->first();

        return view('client.social-connect', compact('platform', 'meta', 'existing'));
    }

    /* ═══════════════════════════════════════════════════════════════
       FACEBOOK OAUTH 2.0
       ═══════════════════════════════════════════════════════════════ */

    public function redirectToFacebook()
    {
        $clientId = config('services.facebook.client_id');

        if (empty($clientId) || empty(config('services.facebook.client_secret'))) {
            return redirect()->route('client.social.connect', ['platform' => 'facebook'])
                ->with('error', 'Facebook App is not configured. Add FACEBOOK_APP_ID and FACEBOOK_APP_SECRET to your .env file.');
        }

        $state = Str::random(40);
        session(['facebook_oauth_state' => $state]);

        $params = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => config('services.facebook.redirect'),
            'state'         => $state,
            'response_type' => 'code',
            'scope'         => 'pages_show_list,pages_read_engagement,pages_manage_posts,read_insights,pages_manage_metadata,business_management',
        ]);

        return redirect('https://www.facebook.com/v21.0/dialog/oauth?' . $params);
    }

    public function handleFacebookCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('client.social.connect', ['platform' => 'facebook'])
                ->with('error', 'Facebook authorization was cancelled: ' . $request->input('error_message', $request->input('error')));
        }

        if (!$request->filled('state') || $request->input('state') !== session('facebook_oauth_state')) {
            return redirect()->route('client.social.connect', ['platform' => 'facebook'])
                ->with('error', 'Invalid OAuth state. Please try connecting again.');
        }
        session()->forget('facebook_oauth_state');

        if (!$request->filled('code')) {
            return redirect()->route('client.social.connect', ['platform' => 'facebook'])
                ->with('error', 'No authorization code received from Facebook.');
        }

        try {
            $tokenResponse = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
                'client_id'     => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'redirect_uri'  => config('services.facebook.redirect'),
                'code'          => $request->input('code'),
            ]);

            if ($tokenResponse->failed() || empty($tokenResponse->json('access_token'))) {
                throw new \Exception($tokenResponse->json('error.message') ?? 'Failed to obtain an access token from Facebook.');
            }

            $shortLivedToken = $tokenResponse->json('access_token');

            $longLivedResponse = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
                'grant_type'        => 'fb_exchange_token',
                'client_id'         => config('services.facebook.client_id'),
                'client_secret'     => config('services.facebook.client_secret'),
                'fb_exchange_token' => $shortLivedToken,
            ]);

            $userToken = $longLivedResponse->json('access_token') ?? $shortLivedToken;
            $expiresIn = $longLivedResponse->json('expires_in');

            $pagesResponse = Http::get('https://graph.facebook.com/v21.0/me/accounts', [
                'access_token' => $userToken,
                'fields'       => 'id,name,access_token,followers_count,fan_count,link',
                'limit'        => 100,
            ]);

            $pages = $pagesResponse->json('data') ?? [];

            if (empty($pages)) {
                return redirect()->route('client.social.connect', ['platform' => 'facebook'])
                    ->with('error', 'No Facebook Pages found. You must be an admin of at least one Facebook Page to connect.');
            }

            $page = $pages[0];

            $data = [
                'platform'         => 'facebook',
                'connection_name'  => $page['name'] . ' (Facebook)',
                'status'           => 'connected',
                'page_id'          => $page['id'],
                'access_token'     => Crypt::encryptString($page['access_token']),
                'profile_url'      => $page['link'] ?? ('https://facebook.com/' . $page['id']),
                'username'         => $page['name'],
                'token_expires_at' => $expiresIn ? now()->addSeconds((int) $expiresIn) : now()->addDays(60),
                'last_sync_at'     => now(),
                'last_error'       => null,
                'settings'         => ['connected_via' => 'oauth2'],
                'sync_config'      => [
                    'sync_frequency' => 'hourly',
                    'content_types'  => ['posts', 'comments', 'reactions'],
                    'auto_publish'   => false,
                ],
                'metrics'          => [
                    'followers'       => $page['followers_count'] ?? $page['fan_count'] ?? 0,
                    'engagement_rate' => 0,
                    'posts_count'     => 0,
                    'avg_reach'       => 0,
                ],
                'sync_count'       => \DB::raw('sync_count + 1'),
            ];

            $connection = SocialIntegration::updateOrCreate(['platform' => 'facebook'], $data);
            $connection->refresh();

            $extra = count($pages) > 1 ? ' — ' . count($pages) . ' pages found, connected "' . $page['name'] . '"' : '';

            return redirect()->route('client.social-connections')
                ->with('success', 'Facebook connected successfully. Page: ' . $page['name'] . $extra);

        } catch (\Exception $e) {
            return redirect()->route('client.social.connect', ['platform' => 'facebook'])
                ->with('error', 'Facebook connection failed: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       INSTAGRAM BUSINESS LOGIN
       ═══════════════════════════════════════════════════════════════ */

    public function redirectToInstagram()
    {
        $clientId = config('services.instagram.client_id');

        if (empty($clientId) || empty(config('services.instagram.client_secret'))) {
            return redirect()->route('client.social.connect', ['platform' => 'instagram'])
                ->with('error', 'Instagram App is not configured. Add INSTAGRAM_APP_ID and INSTAGRAM_APP_SECRET to your .env file.');
        }

        $state = Str::random(40);
        session(['instagram_oauth_state' => $state]);

        $params = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => config('services.instagram.redirect'),
            'state'         => $state,
            'response_type' => 'code',
            'scope'         => 'instagram_business_basic,instagram_business_manage_comments,instagram_business_manage_insights,instagram_business_content_publish',
        ]);

        return redirect('https://www.instagram.com/oauth/authorize?' . $params);
    }

    public function handleInstagramCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('client.social.connect', ['platform' => 'instagram'])
                ->with('error', 'Instagram authorization was cancelled: ' . $request->input('error_message', $request->input('error')));
        }

        if (!$request->filled('state') || $request->input('state') !== session('instagram_oauth_state')) {
            return redirect()->route('client.social.connect', ['platform' => 'instagram'])
                ->with('error', 'Invalid OAuth state. Please try connecting again.');
        }
        session()->forget('instagram_oauth_state');

        if (!$request->filled('code')) {
            return redirect()->route('client.social.connect', ['platform' => 'instagram'])
                ->with('error', 'No authorization code received from Instagram.');
        }

        try {
            $tokenResponse = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
                'client_id'     => config('services.instagram.client_id'),
                'client_secret' => config('services.instagram.client_secret'),
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => config('services.instagram.redirect'),
                'code'          => $request->input('code'),
            ]);

            $shortLivedToken = $tokenResponse->json('data.0.access_token');
            $igUserId        = $tokenResponse->json('data.0.user_id');

            if ($tokenResponse->failed() || empty($shortLivedToken)) {
                throw new \Exception(
                    $tokenResponse->json('error_message')
                    ?? $tokenResponse->json('error.message')
                    ?? 'Failed to obtain an access token from Instagram.'
                );
            }

            $longLivedResponse = Http::get('https://graph.instagram.com/access_token', [
                'grant_type'    => 'ig_exchange_token',
                'client_secret' => config('services.instagram.client_secret'),
                'access_token'  => $shortLivedToken,
            ]);

            $accessToken = $longLivedResponse->json('access_token') ?? $shortLivedToken;
            $expiresIn   = $longLivedResponse->json('expires_in');

            $profileResponse = Http::get('https://graph.instagram.com/v21.0/me', [
                'fields'       => 'user_id,username,name,account_type,followers_count,media_count,profile_picture_url',
                'access_token' => $accessToken,
            ]);

            if ($profileResponse->failed()) {
                throw new \Exception($profileResponse->json('error.message') ?? 'Failed to fetch Instagram profile.');
            }

            $igUsername = $profileResponse->json('username') ?? 'instagram_account';
            $igUserId   = $profileResponse->json('user_id') ?? $igUserId;

            $data = [
                'platform'         => 'instagram',
                'connection_name'  => '@' . $igUsername . ' (Instagram)',
                'status'           => 'connected',
                'account_id'       => (string) $igUserId,
                'access_token'     => Crypt::encryptString($accessToken),
                'profile_url'      => 'https://instagram.com/' . $igUsername,
                'username'         => '@' . $igUsername,
                'token_expires_at' => $expiresIn ? now()->addSeconds((int) $expiresIn) : now()->addDays(60),
                'last_sync_at'     => now(),
                'last_error'       => null,
                'settings'         => [
                    'connected_via' => 'instagram_business_login',
                    'account_type'  => $profileResponse->json('account_type'),
                ],
                'sync_config'      => [
                    'sync_frequency' => 'hourly',
                    'content_types'  => ['posts', 'comments', 'reactions'],
                    'auto_publish'   => false,
                ],
                'metrics'          => [
                    'followers'       => $profileResponse->json('followers_count') ?? 0,
                    'engagement_rate' => 0,
                    'posts_count'     => $profileResponse->json('media_count') ?? 0,
                    'avg_reach'       => 0,
                ],
                'sync_count'       => \DB::raw('sync_count + 1'),
            ];

            $connection = SocialIntegration::updateOrCreate(['platform' => 'instagram'], $data);
            $connection->refresh();

            return redirect()->route('client.social-connections')
                ->with('success', 'Instagram connected successfully. Account: @' . $igUsername);

        } catch (\Exception $e) {
            return redirect()->route('client.social.connect', ['platform' => 'instagram'])
                ->with('error', 'Instagram connection failed: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       TIKTOK OAUTH 2.0
       ═══════════════════════════════════════════════════════════════ */

    public function redirectToTikTok()
    {
        $clientKey = config('services.tiktok.client_key');

        if (empty($clientKey) || empty(config('services.tiktok.client_secret'))) {
            return redirect()->route('client.social.connect', ['platform' => 'tiktok'])
                ->with('error', 'TikTok App is not configured. Add TIKTOK_CLIENT_KEY and TIKTOK_CLIENT_SECRET to your .env file.');
        }

        $state = Str::random(40);
        session(['tiktok_oauth_state' => $state]);

        $params = http_build_query([
            'client_key'    => $clientKey,
            'redirect_uri'  => config('services.tiktok.redirect'),
            'state'         => $state,
            'response_type' => 'code',
            'scope'         => 'user.info.basic,user.info.stats,video.list',
        ]);

        return redirect('https://www.tiktok.com/v2/auth/authorize/?' . $params);
    }

    public function handleTikTokCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('client.social.connect', ['platform' => 'tiktok'])
                ->with('error', 'TikTok authorization was cancelled: ' . $request->input('error_description', $request->input('error')));
        }

        if (!$request->filled('state') || $request->input('state') !== session('tiktok_oauth_state')) {
            return redirect()->route('client.social.connect', ['platform' => 'tiktok'])
                ->with('error', 'Invalid OAuth state. Please try connecting again.');
        }
        session()->forget('tiktok_oauth_state');

        if (!$request->filled('code')) {
            return redirect()->route('client.social.connect', ['platform' => 'tiktok'])
                ->with('error', 'No authorization code received from TikTok.');
        }

        try {
            // 1. Exchange code for access token
            $tokenResponse = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
                'client_key'    => config('services.tiktok.client_key'),
                'client_secret' => config('services.tiktok.client_secret'),
                'code'          => $request->input('code'),
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => config('services.tiktok.redirect'),
            ]);

            $accessToken = $tokenResponse->json('access_token');

            if ($tokenResponse->failed() || empty($accessToken)) {
                throw new \Exception(
                    $tokenResponse->json('error_description')
                    ?? $tokenResponse->json('message')
                    ?? 'Failed to obtain an access token from TikTok.'
                );
            }

            // 2. Fetch user profile + stats
            $userResponse = Http::withToken($accessToken)->get('https://open.tiktokapis.com/v2/user/info/', [
                'fields' => 'open_id,union_id,avatar_url,display_name,username,follower_count,following_count,likes_count,video_count',
            ]);

            if ($userResponse->failed() || !empty($userResponse->json('error.code')) && $userResponse->json('error.code') !== 'ok') {
                throw new \Exception($userResponse->json('error.message') ?? 'Failed to fetch TikTok user info.');
            }

            $user        = $userResponse->json('data.user') ?? [];
            $displayName = $user['display_name'] ?? ($user['username'] ?? 'TikTok Account');

            // 3. Save (access token lives ~24h, refresh token ~1 year)
            $data = [
                'platform'         => 'tiktok',
                'connection_name'  => $displayName . ' (TikTok)',
                'status'           => 'connected',
                'account_id'       => $tokenResponse->json('open_id') ?? ($user['open_id'] ?? null),
                'access_token'     => Crypt::encryptString($accessToken),
                'refresh_token'    => $tokenResponse->json('refresh_token')
                    ? Crypt::encryptString($tokenResponse->json('refresh_token'))
                    : null,
                'profile_url'      => !empty($user['username']) ? 'https://www.tiktok.com/@' . $user['username'] : null,
                'username'         => !empty($user['username']) ? '@' . $user['username'] : $displayName,
                'token_expires_at' => now()->addSeconds((int) ($tokenResponse->json('expires_in') ?? 86400)),
                'last_sync_at'     => now(),
                'last_error'       => null,
                'settings'         => ['connected_via' => 'oauth2'],
                'sync_config'      => [
                    'sync_frequency' => 'hourly',
                    'content_types'  => ['posts', 'comments', 'reactions'],
                    'auto_publish'   => false,
                ],
                'metrics'          => [
                    'followers'       => $user['follower_count'] ?? 0,
                    'engagement_rate' => 0,
                    'posts_count'     => $user['video_count'] ?? 0,
                    'avg_reach'       => $user['likes_count'] ?? 0,
                ],
                'sync_count'       => \DB::raw('sync_count + 1'),
            ];

            $connection = SocialIntegration::updateOrCreate(['platform' => 'tiktok'], $data);
            $connection->refresh();

            return redirect()->route('client.social-connections')
                ->with('success', 'TikTok connected successfully. Account: ' . $displayName);

        } catch (\Exception $e) {
            return redirect()->route('client.social.connect', ['platform' => 'tiktok'])
                ->with('error', 'TikTok connection failed: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       YOUTUBE OAUTH 2.0 (Google)
       ═══════════════════════════════════════════════════════════════ */

    public function redirectToYoutube()
    {
        $clientId = config('services.youtube.client_id');

        if (empty($clientId) || empty(config('services.youtube.client_secret'))) {
            return redirect()->route('client.social.connect', ['platform' => 'youtube'])
                ->with('error', 'Google App is not configured. Add YOUTUBE_CLIENT_ID and YOUTUBE_CLIENT_SECRET to your .env file.');
        }

        $state = Str::random(40);
        session(['youtube_oauth_state' => $state]);

        $params = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => config('services.youtube.redirect'),
            'state'         => $state,
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/youtube.readonly',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    }

    public function handleYoutubeCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('client.social.connect', ['platform' => 'youtube'])
                ->with('error', 'YouTube authorization was cancelled: ' . $request->input('error'));
        }

        if (!$request->filled('state') || $request->input('state') !== session('youtube_oauth_state')) {
            return redirect()->route('client.social.connect', ['platform' => 'youtube'])
                ->with('error', 'Invalid OAuth state. Please try connecting again.');
        }
        session()->forget('youtube_oauth_state');

        if (!$request->filled('code')) {
            return redirect()->route('client.social.connect', ['platform' => 'youtube'])
                ->with('error', 'No authorization code received from Google.');
        }

        try {
            // 1. Exchange code for tokens (access ~1h + offline refresh token)
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id'     => config('services.youtube.client_id'),
                'client_secret' => config('services.youtube.client_secret'),
                'code'          => $request->input('code'),
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => config('services.youtube.redirect'),
            ]);

            $accessToken = $tokenResponse->json('access_token');

            if ($tokenResponse->failed() || empty($accessToken)) {
                throw new \Exception(
                    $tokenResponse->json('error_description')
                    ?? $tokenResponse->json('error')
                    ?? 'Failed to obtain an access token from Google.'
                );
            }

            // 2. Fetch the user's YouTube channel
            $channelResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'snippet,statistics',
                'mine' => 'true',
            ]);

            $channel = $channelResponse->json('items.0');

            if (!$channel) {
                return redirect()->route('client.social.connect', ['platform' => 'youtube'])
                    ->with('error', 'No YouTube channel found for this Google account. Create a channel on YouTube first, then try again.');
            }

            $title = $channel['snippet']['title'] ?? 'YouTube Channel';

            // 3. Save
            $data = [
                'platform'         => 'youtube',
                'connection_name'  => $title . ' (YouTube)',
                'status'           => 'connected',
                'channel_id'       => $channel['id'],
                'access_token'     => Crypt::encryptString($accessToken),
                'refresh_token'    => $tokenResponse->json('refresh_token')
                    ? Crypt::encryptString($tokenResponse->json('refresh_token'))
                    : null,
                'profile_url'      => 'https://www.youtube.com/channel/' . $channel['id'],
                'username'         => $channel['snippet']['customUrl'] ?? $title,
                'token_expires_at' => now()->addSeconds((int) ($tokenResponse->json('expires_in') ?? 3600)),
                'last_sync_at'     => now(),
                'last_error'       => null,
                'settings'         => ['connected_via' => 'oauth2'],
                'sync_config'      => [
                    'sync_frequency' => 'hourly',
                    'content_types'  => ['posts', 'comments', 'reactions'],
                    'auto_publish'   => false,
                ],
                'metrics'          => [
                    'followers'       => (int) ($channel['statistics']['subscriberCount'] ?? 0),
                    'engagement_rate' => 0,
                    'posts_count'     => (int) ($channel['statistics']['videoCount'] ?? 0),
                    'avg_reach'       => (int) ($channel['statistics']['viewCount'] ?? 0),
                ],
                'sync_count'       => \DB::raw('sync_count + 1'),
            ];

            $connection = SocialIntegration::updateOrCreate(['platform' => 'youtube'], $data);
            $connection->refresh();

            return redirect()->route('client.social-connections')
                ->with('success', 'YouTube connected successfully. Channel: ' . $title);

        } catch (\Exception $e) {
            return redirect()->route('client.social.connect', ['platform' => 'youtube'])
                ->with('error', 'YouTube connection failed: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       LINKEDIN OAUTH 2.0 (OpenID Connect)
       ═══════════════════════════════════════════════════════════════ */

    public function redirectToLinkedin()
    {
        $clientId = config('services.linkedin.client_id');

        if (empty($clientId) || empty(config('services.linkedin.client_secret'))) {
            return redirect()->route('client.social.connect', ['platform' => 'linkedin'])
                ->with('error', 'LinkedIn App is not configured. Add LINKEDIN_CLIENT_ID and LINKEDIN_CLIENT_SECRET to your .env file.');
        }

        $state = Str::random(40);
        session(['linkedin_oauth_state' => $state]);

        $params = http_build_query([
            'response_type' => 'code',
            'client_id'     => $clientId,
            'redirect_uri'  => config('services.linkedin.redirect'),
            'state'         => $state,
            'scope'         => 'openid profile email',
        ]);

        return redirect('https://www.linkedin.com/oauth/v2/authorization?' . $params);
    }

    public function handleLinkedinCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('client.social.connect', ['platform' => 'linkedin'])
                ->with('error', 'LinkedIn authorization was cancelled: ' . $request->input('error_description', $request->input('error')));
        }

        if (!$request->filled('state') || $request->input('state') !== session('linkedin_oauth_state')) {
            return redirect()->route('client.social.connect', ['platform' => 'linkedin'])
                ->with('error', 'Invalid OAuth state. Please try connecting again.');
        }
        session()->forget('linkedin_oauth_state');

        if (!$request->filled('code')) {
            return redirect()->route('client.social.connect', ['platform' => 'linkedin'])
                ->with('error', 'No authorization code received from LinkedIn.');
        }

        try {
            // 1. Exchange code for access token (~60 days)
            $tokenResponse = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'grant_type'    => 'authorization_code',
                'code'          => $request->input('code'),
                'client_id'     => config('services.linkedin.client_id'),
                'client_secret' => config('services.linkedin.client_secret'),
                'redirect_uri'  => config('services.linkedin.redirect'),
            ]);

            $accessToken = $tokenResponse->json('access_token');

            if ($tokenResponse->failed() || empty($accessToken)) {
                throw new \Exception(
                    $tokenResponse->json('error_description')
                    ?? $tokenResponse->json('error')
                    ?? 'Failed to obtain an access token from LinkedIn.'
                );
            }

            // 2. Fetch the member profile (OpenID userinfo)
            $profileResponse = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/userinfo');

            if ($profileResponse->failed() || empty($profileResponse->json('sub'))) {
                throw new \Exception($profileResponse->json('error_description') ?? 'Failed to fetch LinkedIn profile.');
            }

            $name = $profileResponse->json('name')
                ?: trim(($profileResponse->json('given_name') ?? '') . ' ' . ($profileResponse->json('family_name') ?? ''))
                ?: 'LinkedIn Account';

            // 3. Save
            $data = [
                'platform'         => 'linkedin',
                'connection_name'  => $name . ' (LinkedIn)',
                'status'           => 'connected',
                'page_id'          => $profileResponse->json('sub'),
                'access_token'     => Crypt::encryptString($accessToken),
                'profile_url'      => null,
                'username'         => $name,
                'token_expires_at' => now()->addSeconds((int) ($tokenResponse->json('expires_in') ?? 5184000)),
                'last_sync_at'     => now(),
                'last_error'       => null,
                'settings'         => [
                    'connected_via'    => 'oauth2_oidc',
                    'connection_type'  => 'member_profile',
                    'email'            => $profileResponse->json('email'),
                ],
                'sync_config'      => [
                    'sync_frequency' => 'hourly',
                    'content_types'  => ['posts', 'comments', 'reactions'],
                    'auto_publish'   => false,
                ],
                'metrics'          => [
                    'followers'       => 0,
                    'engagement_rate' => 0,
                    'posts_count'     => 0,
                    'avg_reach'       => 0,
                ],
                'sync_count'       => \DB::raw('sync_count + 1'),
            ];

            $connection = SocialIntegration::updateOrCreate(['platform' => 'linkedin'], $data);
            $connection->refresh();

            return redirect()->route('client.social-connections')
                ->with('success', 'LinkedIn connected successfully. Account: ' . $name);

        } catch (\Exception $e) {
            return redirect()->route('client.social.connect', ['platform' => 'linkedin'])
                ->with('error', 'LinkedIn connection failed: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       MANUAL STORE / TEST / DISCONNECT / DELETE / SYNC / STATUS
       ═══════════════════════════════════════════════════════════════ */

    /**
     * Store or update a Social Media connection.
     */
    public function store(Request $request, string $platform)
    {
        $platforms = SocialIntegration::platforms();

        if (!isset($platforms[$platform])) {
            return response()->json(['error' => 'Invalid platform'], 422);
        }

        $meta = $platforms[$platform];
        $rules = [
            'connection_name' => 'required|string|max:100',
            'status'          => 'required|in:connected,disconnected',
        ];

        // A token is already saved (e.g. via OAuth) — don't force re-entry
        $existingHasToken = SocialIntegration::byPlatform($platform)
            ->whereNotNull('access_token')
            ->exists();

        // Platform-specific validation
        switch ($platform) {
            case 'facebook':
                $rules['page_id'] = 'required|string';
                $rules['access_token'] = $existingHasToken ? 'nullable|string' : 'required|string';
                break;
            case 'instagram':
                $rules['account_id'] = 'required|string';
                $rules['access_token'] = $existingHasToken ? 'nullable|string' : 'required|string';
                break;
            case 'tiktok':
                $rules['account_id'] = 'required|string';
                $rules['access_token'] = $existingHasToken ? 'nullable|string' : 'required|string';
                break;
            case 'youtube':
                $rules['channel_id'] = 'required|string';
                $rules['access_token'] = $existingHasToken ? 'nullable|string' : 'required|string';
                break;
            case 'linkedin':
                $rules['page_id'] = 'required|string';
                $rules['access_token'] = $existingHasToken ? 'nullable|string' : 'required|string';
                break;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['platform'] = $platform;

        // Keep the existing token if the field was left blank
        if (array_key_exists('access_token', $data) && empty($data['access_token'])) {
            unset($data['access_token']);
        }

        // Encrypt sensitive fields
        $encryptFields = ['access_token', 'refresh_token'];
        foreach ($encryptFields as $field) {
            if (!empty($data[$field])) {
                $data[$field] = Crypt::encryptString($data[$field]);
            }
        }

        // Handle settings & sync_config
        $data['settings'] = $request->input('settings', []);
        $data['sync_config'] = $request->input('sync_config', [
            'sync_frequency' => 'hourly',
            'content_types'  => ['posts', 'comments', 'reactions'],
            'auto_publish'   => false,
        ]);

        // Mock metrics for demo (in production, fetch from API)
        $data['metrics'] = $request->input('metrics', [
            'followers'       => rand(1000, 500000),
            'engagement_rate' => rand(15, 85) / 10,
            'posts_count'     => rand(50, 2000),
            'avg_reach'       => rand(5000, 500000),
        ]);

        if ($data['status'] === 'connected') {
            $data['last_sync_at'] = now();
            $data['sync_count'] = \DB::raw('sync_count + 1');
        }

        $connection = SocialIntegration::updateOrCreate(
            ['platform' => $platform],
            $data
        );

        if (isset($data['sync_count'])) {
            $connection->refresh();
        }

        return response()->json([
            'success'   => true,
            'message'   => $connection->wasRecentlyCreated
                ? 'Social media connection created successfully.'
                : 'Social media connection updated successfully.',
            'connection' => [
                'id'       => $connection->id,
                'platform' => $connection->platform,
                'status'   => $connection->status,
                'name'     => $connection->connection_name,
            ],
        ]);
    }

    /**
     * Test a Social Media connection.
     */
    public function test(string $platform)
    {
        $connection = SocialIntegration::byPlatform($platform)->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'No connection found for this platform.',
            ], 404);
        }

        try {
            $result = $this->testConnection($connection);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'details' => $result['details'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Disconnect a Social Media connection.
     */
    public function disconnect(string $platform)
    {
        $connection = SocialIntegration::byPlatform($platform)->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $connection->update([
            'status'        => 'disconnected',
            'access_token'  => null,
            'refresh_token' => null,
            'token_expires_at' => null,
            'last_error'    => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Social media connection disconnected successfully.',
        ]);
    }

    /**
     * Delete a Social Media connection.
     */
    public function destroy(string $platform)
    {
        $connection = SocialIntegration::byPlatform($platform)->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $connection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Social media connection deleted successfully.',
        ]);
    }

    /**
     * Sync data from Social Media platform.
     */
    public function sync(string $platform)
    {
        $connection = SocialIntegration::byPlatform($platform)->first();

        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'Platform not connected.',
            ], 400);
        }

        try {
            switch ($platform) {
                case 'facebook':
                    $token = $connection->access_token ? Crypt::decryptString($connection->access_token) : null;
                    if ($token && $connection->page_id) {
                        $res = Http::get("https://graph.facebook.com/v21.0/{$connection->page_id}", [
                            'fields'       => 'name,followers_count,fan_count',
                            'access_token' => $token,
                        ]);
                        if ($res->successful()) {
                            $metrics = $connection->metrics ?? [];
                            $metrics['followers'] = $res->json('followers_count') ?? $res->json('fan_count') ?? ($metrics['followers'] ?? 0);
                            $connection->update(['metrics' => $metrics]);
                        }
                    }
                    break;

                case 'instagram':
                    $token = $connection->access_token ? Crypt::decryptString($connection->access_token) : null;
                    if ($token) {
                        $res = Http::get('https://graph.instagram.com/v21.0/me', [
                            'fields'       => 'followers_count,media_count',
                            'access_token' => $token,
                        ]);
                        if ($res->successful()) {
                            $metrics = $connection->metrics ?? [];
                            $metrics['followers']   = $res->json('followers_count') ?? ($metrics['followers'] ?? 0);
                            $metrics['posts_count'] = $res->json('media_count') ?? ($metrics['posts_count'] ?? 0);
                            $connection->update(['metrics' => $metrics]);
                        }
                    }
                    break;

                case 'tiktok':
                    $token = $this->getValidTikTokToken($connection);
                    if ($token) {
                        $res = Http::withToken($token)->get('https://open.tiktokapis.com/v2/user/info/', [
                            'fields' => 'follower_count,likes_count,video_count',
                        ]);
                        if ($res->successful()) {
                            $user = $res->json('data.user') ?? [];
                            $metrics = $connection->metrics ?? [];
                            $metrics['followers']   = $user['follower_count'] ?? ($metrics['followers'] ?? 0);
                            $metrics['posts_count'] = $user['video_count'] ?? ($metrics['posts_count'] ?? 0);
                            $metrics['avg_reach']   = $user['likes_count'] ?? ($metrics['avg_reach'] ?? 0);
                            $connection->update(['metrics' => $metrics]);
                        }
                    }
                    break;

                case 'youtube':
                    $token = $this->getValidGoogleToken($connection);
                    if ($token) {
                        $res = Http::withToken($token)->get('https://www.googleapis.com/youtube/v3/channels', [
                            'part' => 'statistics',
                            'mine' => 'true',
                        ]);
                        $ch = $res->json('items.0');
                        if ($ch) {
                            $metrics = $connection->metrics ?? [];
                            $metrics['followers']   = (int) ($ch['statistics']['subscriberCount'] ?? ($metrics['followers'] ?? 0));
                            $metrics['posts_count'] = (int) ($ch['statistics']['videoCount'] ?? ($metrics['posts_count'] ?? 0));
                            $metrics['avg_reach']   = (int) ($ch['statistics']['viewCount'] ?? ($metrics['avg_reach'] ?? 0));
                            $connection->update(['metrics' => $metrics]);
                        }
                    }
                    break;

                case 'linkedin':
                    // Member profile has no public follower metric via OIDC scopes — just ping userinfo
                    $token = $connection->access_token ? Crypt::decryptString($connection->access_token) : null;
                    if ($token) {
                        Http::withToken($token)->get('https://api.linkedin.com/v2/userinfo');
                    }
                    break;
            }
        } catch (\Exception $e) {
            // Silently continue — sync timestamp is still updated below
        }

        // Update sync timestamps
        $connection->update([
            'last_sync_at' => now(),
            'sync_count'     => \DB::raw('sync_count + 1'),
            'last_error'     => null,
        ]);

        $connection->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Sync initiated successfully.',
            'last_sync' => $connection->last_sync_at->diffForHumans(),
        ]);
    }

    /**
     * Get connection status for all platforms.
     */
    public function status()
    {
        $connections = SocialIntegration::all()->keyBy('platform');
        $platforms   = SocialIntegration::platforms();

        $status = [];
        foreach ($platforms as $key => $meta) {
            $conn = $connections[$key] ?? null;
            $status[$key] = [
                'connected'   => $conn?->is_connected ?? false,
                'status'      => $conn?->status ?? 'disconnected',
                'last_sync'   => $conn?->last_sync_at?->diffForHumans() ?? 'Never',
                'sync_count'  => $conn?->sync_count ?? 0,
                'name'        => $conn?->connection_name ?? null,
                'followers'   => $conn?->formatted_followers ?? '0',
                'engagement'  => $conn?->engagement_rate ?? '0.00%',
            ];
        }

        return response()->json(['status' => $status]);
    }

    /* ═══════════════════════════════════════════════════════════════
       TOKEN REFRESH HELPERS
       ═══════════════════════════════════════════════════════════════ */

    /**
     * Get a valid Google (YouTube) access token, refreshing if expired.
     */
    private function getValidGoogleToken(SocialIntegration $connection): ?string
    {
        $token = $connection->access_token ? Crypt::decryptString($connection->access_token) : null;
        if (!$token) {
            return null;
        }

        if (!$connection->is_expired || !$connection->refresh_token) {
            return $token;
        }

        $res = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id'     => config('services.youtube.client_id'),
            'client_secret' => config('services.youtube.client_secret'),
            'refresh_token' => Crypt::decryptString($connection->refresh_token),
            'grant_type'    => 'refresh_token',
        ]);

        if ($res->successful() && $res->json('access_token')) {
            $connection->update([
                'access_token'     => Crypt::encryptString($res->json('access_token')),
                'token_expires_at' => now()->addSeconds((int) ($res->json('expires_in') ?? 3600)),
            ]);
            return $res->json('access_token');
        }

        return null;
    }

    /**
     * Get a valid TikTok access token, refreshing if expired.
     */
    private function getValidTikTokToken(SocialIntegration $connection): ?string
    {
        $token = $connection->access_token ? Crypt::decryptString($connection->access_token) : null;
        if (!$token) {
            return null;
        }

        if (!$connection->is_expired || !$connection->refresh_token) {
            return $token;
        }

        $res = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
            'client_key'    => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'grant_type'    => 'refresh_token',
            'refresh_token' => Crypt::decryptString($connection->refresh_token),
        ]);

        if ($res->successful() && $res->json('access_token')) {
            $connection->update([
                'access_token'     => Crypt::encryptString($res->json('access_token')),
                'refresh_token'    => $res->json('refresh_token')
                    ? Crypt::encryptString($res->json('refresh_token'))
                    : $connection->refresh_token,
                'token_expires_at' => now()->addSeconds((int) ($res->json('expires_in') ?? 86400)),
            ]);
            return $res->json('access_token');
        }

        return null;
    }

    /**
     * Test connection helper.
     */
    private function testConnection(SocialIntegration $connection): array
    {
        switch ($connection->platform) {
            case 'facebook':
                try {
                    $token = $connection->access_token ? Crypt::decryptString($connection->access_token) : null;

                    if (!$token || !$connection->page_id) {
                        return ['success' => false, 'message' => 'No Facebook access token or Page ID stored.'];
                    }

                    $res = Http::get("https://graph.facebook.com/v21.0/{$connection->page_id}", [
                        'fields'       => 'name,followers_count,fan_count,link',
                        'access_token' => $token,
                    ]);

                    if ($res->successful()) {
                        return [
                            'success' => true,
                            'message' => 'Facebook connection verified.',
                            'details' => [
                                'page_name' => $res->json('name'),
                                'followers' => $res->json('followers_count') ?? $res->json('fan_count') ?? 0,
                                'url'       => $res->json('link'),
                            ],
                        ];
                    }

                    return ['success' => false, 'message' => 'Facebook API error: ' . ($res->json('error.message') ?? 'Unknown error')];
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => 'Facebook test failed: ' . $e->getMessage()];
                }

            case 'instagram':
                try {
                    $token = $connection->access_token ? Crypt::decryptString($connection->access_token) : null;

                    if (!$token) {
                        return ['success' => false, 'message' => 'No Instagram access token stored.'];
                    }

                    $res = Http::get('https://graph.instagram.com/v21.0/me', [
                        'fields'       => 'user_id,username,name,followers_count,media_count',
                        'access_token' => $token,
                    ]);

                    if ($res->successful()) {
                        return [
                            'success' => true,
                            'message' => 'Instagram connection verified.',
                            'details' => [
                                'username'  => '@' . ($res->json('username') ?? ''),
                                'followers' => $res->json('followers_count') ?? 0,
                                'posts'     => $res->json('media_count') ?? 0,
                            ],
                        ];
                    }

                    return ['success' => false, 'message' => 'Instagram API error: ' . ($res->json('error.message') ?? 'Unknown error')];
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => 'Instagram test failed: ' . $e->getMessage()];
                }

            case 'tiktok':
                try {
                    $token = $this->getValidTikTokToken($connection);

                    if (!$token) {
                        return ['success' => false, 'message' => 'No valid TikTok access token (refresh failed — reconnect).'];
                    }

                    $res = Http::withToken($token)->get('https://open.tiktokapis.com/v2/user/info/', [
                        'fields' => 'display_name,username,follower_count,video_count',
                    ]);

                    if ($res->successful()) {
                        $user = $res->json('data.user') ?? [];
                        return [
                            'success' => true,
                            'message' => 'TikTok connection verified.',
                            'details' => [
                                'display_name' => $user['display_name'] ?? '',
                                'username'     => isset($user['username']) ? '@' . $user['username'] : '',
                                'followers'    => $user['follower_count'] ?? 0,
                                'videos'       => $user['video_count'] ?? 0,
                            ],
                        ];
                    }

                    return ['success' => false, 'message' => 'TikTok API error: ' . ($res->json('error.message') ?? 'Unknown error')];
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => 'TikTok test failed: ' . $e->getMessage()];
                }

            case 'youtube':
                try {
                    $token = $this->getValidGoogleToken($connection);

                    if (!$token) {
                        return ['success' => false, 'message' => 'No valid Google access token (refresh failed — reconnect).'];
                    }

                    $res = Http::withToken($token)->get('https://www.googleapis.com/youtube/v3/channels', [
                        'part' => 'snippet,statistics',
                        'mine' => 'true',
                    ]);

                    $ch = $res->json('items.0');

                    if ($ch) {
                        return [
                            'success' => true,
                            'message' => 'YouTube connection verified.',
                            'details' => [
                                'channel'     => $ch['snippet']['title'] ?? '',
                                'subscribers' => (int) ($ch['statistics']['subscriberCount'] ?? 0),
                                'videos'      => (int) ($ch['statistics']['videoCount'] ?? 0),
                            ],
                        ];
                    }

                    return ['success' => false, 'message' => 'YouTube API error: no channel found for this account.'];
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => 'YouTube test failed: ' . $e->getMessage()];
                }

            case 'linkedin':
                try {
                    $token = $connection->access_token ? Crypt::decryptString($connection->access_token) : null;

                    if (!$token) {
                        return ['success' => false, 'message' => 'No LinkedIn access token stored.'];
                    }

                    $res = Http::withToken($token)->get('https://api.linkedin.com/v2/userinfo');

                    if ($res->successful()) {
                        return [
                            'success' => true,
                            'message' => 'LinkedIn connection verified.',
                            'details' => [
                                'name'  => $res->json('name') ?? '',
                                'email' => $res->json('email') ?? '',
                            ],
                        ];
                    }

                    return ['success' => false, 'message' => 'LinkedIn API error: ' . ($res->json('error_description') ?? 'Unknown error')];
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => 'LinkedIn test failed: ' . $e->getMessage()];
                }

            default:
                return ['success' => false, 'message' => 'Unknown platform.'];
        }
    }
}