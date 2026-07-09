<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialIntegration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'social_integrations';

    protected $fillable = [
        'platform',
        'connection_name',
        'status',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'page_id',
        'account_id',
        'channel_id',
        'profile_url',
        'username',
        'profile_image',
        'settings',
        'sync_config',
        'metrics',
        'last_sync_at',
        'sync_count',
        'last_error',
        'created_by',
    ];

    protected $casts = [
        'settings'     => 'array',
        'sync_config'  => 'array',
        'metrics'      => 'array',
        'token_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    // Platform metadata
    public static function platforms(): array
    {
        return [
            'facebook' => [
                'name'        => 'Facebook',
                'icon'        => 'facebook',
                'color'       => '#1877F2',
                'bg_color'    => '#E7F3FF',
                'description' => 'Connect your Facebook Page to sync posts, comments, reactions, and audience insights.',
                'features'    => ['Page Posts', 'Comments Sync', 'Reactions', 'Audience Insights', 'Messenger', 'Ads Data'],
                'auth_type'   => 'oauth2',
                'docs_url'    => 'https://developers.facebook.com/docs/graph-api',
                'scopes'      => ['pages_read_engagement', 'pages_manage_posts', 'read_insights'],
            ],
            'instagram' => [
                'name'        => 'Instagram',
                'icon'        => 'instagram',
                'color'       => '#E4405F',
                'bg_color'    => '#FCE4EC',
                'description' => 'Connect your Instagram Business account to sync posts, stories, reels, and engagement metrics.',
                'features'    => ['Posts Sync', 'Stories & Reels', 'Comments', 'DMs', 'Insights', 'Hashtag Tracking'],
                'auth_type'   => 'oauth2',
                'docs_url'    => 'https://developers.facebook.com/docs/instagram-api',
                'scopes'      => ['instagram_basic', 'instagram_content_publish', 'instagram_manage_insights'],
            ],
            'tiktok' => [
                'name'        => 'TikTok',
                'icon'        => 'tiktok',
                'color'       => '#000000',
                'bg_color'    => '#F5F5F5',
                'description' => 'Connect your TikTok for Business account to sync videos, comments, and analytics data.',
                'features'    => ['Video Sync', 'Comments', 'Likes & Shares', 'Analytics', 'Hashtag Trends', 'Sound Tracking'],
                'auth_type'   => 'oauth2',
                'docs_url'    => 'https://developers.tiktok.com/doc/overview',
                'scopes'      => ['user.info.basic', 'video.list', 'video.insights'],
            ],
            'youtube' => [
                'name'        => 'YouTube',
                'icon'        => 'youtube',
                'color'       => '#FF0000',
                'bg_color'    => '#FFEBEE',
                'description' => 'Connect your YouTube channel to sync videos, comments, subscribers, and analytics.',
                'features'    => ['Video Sync', 'Comments', 'Subscribers', 'Analytics', 'Playlists', 'Live Streams'],
                'auth_type'   => 'oauth2',
                'docs_url'    => 'https://developers.google.com/youtube/v3',
                'scopes'      => ['youtube.readonly', 'youtube.force-ssl', 'youtube.upload'],
            ],
            'linkedin' => [
                'name'        => 'LinkedIn',
                'icon'        => 'linkedin',
                'color'       => '#0A66C2',
                'bg_color'    => '#E3F2FD',
                'description' => 'Connect your LinkedIn Page to sync posts, comments, reactions, and company analytics.',
                'features'    => ['Page Posts', 'Comments', 'Reactions', 'Followers', 'Analytics', 'Employee Advocacy'],
                'auth_type'   => 'oauth2',
                'docs_url'    => 'https://learn.microsoft.com/en-us/linkedin/',
                'scopes'      => ['r_organization_social', 'r_organization_admin', 'w_organization_social'],
            ],
        ];
    }

    public function getPlatformMetaAttribute(): ?array
    {
        return self::platforms()[$this->platform] ?? null;
    }

    public function getIsConnectedAttribute(): bool
    {
        return $this->status === 'connected';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public function scopeConnected($query)
    {
        return $query->where('status', 'connected');
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'connected')->whereNull('deleted_at');
    }

    // Format follower count
    public function getFormattedFollowersAttribute(): string
    {
        $followers = $this->metrics['followers'] ?? 0;
        if ($followers >= 1000000) {
            return number_format($followers / 1000000, 1) . 'M';
        } elseif ($followers >= 1000) {
            return number_format($followers / 1000, 1) . 'K';
        }
        return number_format($followers);
    }

    // Format engagement rate
    public function getEngagementRateAttribute(): string
    {
        $rate = $this->metrics['engagement_rate'] ?? 0;
        return number_format($rate, 2) . '%';
    }
}