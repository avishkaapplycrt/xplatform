<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstagramMedia extends Model
{
    protected $fillable = [
        'social_integration_id', 'media_id', 'media_type', 'caption',
        'permalink', 'posted_at', 'like_count', 'comments_count',
        'reach', 'saved', 'synced_at',
    ];

    protected $casts = ['posted_at' => 'datetime', 'synced_at' => 'datetime'];

    public function socialIntegration()
    {
        return $this->belongsTo(SocialIntegration::class);
    }

    // (likes + comments + saves) / reach — the same shape as the Retention
    // services' engagement math, applied to a single post instead of an account.
    public function getEngagementRateAttribute(): float
    {
        $reach = max(1, $this->reach);
        return round((($this->like_count + $this->comments_count + $this->saved) / $reach) * 100, 2);
    }

    /**
     * Real numbers for the Social Signals micro-signal panel on the L1 Data
     * Collection layer explorer — same shape as EmailLog::deliveryStats() /
     * CallLog::callStats(): a flat array merged straight into the view.
     *
     * "Brand mentions" from other accounts and comment-level sentiment
     * aren't synced (no scope/model built for either), so those two stay
     * honestly empty rather than showing invented numbers — only what's
     * actually in instagram_media / instagram_account_insights is real here.
     */
    public static function socialStats(): array
    {
        $connection = SocialIntegration::where('platform', 'instagram')->connected()->first();
        $posts      = $connection
            ? self::where('social_integration_id', $connection->id)->orderByDesc('posted_at')->get()
            : collect();

        $socialConnected  = (bool) $connection;
        $socialFollowers  = $connection->metrics['followers'] ?? 0;
        $socialTotalPosts = $posts->count();
        $socialTotalLikes = (int) $posts->sum('like_count');
        $socialTotalComments = (int) $posts->sum('comments_count');
        $socialAvgEngagement = $posts->isNotEmpty() ? round($posts->avg->engagement_rate, 2) : 0.0;

        $socialTopPosts = $posts->sortByDesc(fn ($p) => $p->like_count + $p->comments_count)
            ->take(5)->values()
            ->map(fn ($p) => [
                'caption'    => \Illuminate\Support\Str::limit((string) $p->caption, 70) ?: '(no caption)',
                'likes'      => $p->like_count,
                'comments'   => $p->comments_count,
                'engagement' => $p->engagement_rate,
                'permalink'  => $p->permalink,
                'posted_at'  => $p->posted_at?->diffForHumans(),
            ])->all();

        // Real hashtags pulled from each post's actual caption text — the one
        // "Hashtag Tracking" metric this data genuinely supports.
        $hashtagCounts = [];
        foreach ($posts as $post) {
            preg_match_all('/#([\p{L}0-9_]+)/u', (string) $post->caption, $matches);
            foreach ($matches[1] as $tag) {
                $tag = mb_strtolower($tag);
                $hashtagCounts[$tag] = ($hashtagCounts[$tag] ?? 0) + 1;
            }
        }
        arsort($hashtagCounts);
        $socialTopHashtags = collect($hashtagCounts)->take(8)
            ->map(fn ($count, $tag) => ['tag' => $tag, 'count' => $count])
            ->values()->all();

        $latestInsight = $connection
            ? InstagramAccountInsight::where('social_integration_id', $connection->id)
                ->orderByDesc('date')->first()
            : null;

        return compact(
            'socialConnected', 'socialFollowers', 'socialTotalPosts', 'socialTotalLikes',
            'socialTotalComments', 'socialAvgEngagement', 'socialTopPosts', 'socialTopHashtags'
        ) + [
            'socialReachToday'  => $latestInsight->reach ?? 0,
            'socialViewsToday'  => $latestInsight->profile_views ?? 0,
        ];
    }
}
