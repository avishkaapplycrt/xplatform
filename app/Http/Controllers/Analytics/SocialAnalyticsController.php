<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\InstagramMedia;
use App\Models\SocialIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialAnalyticsController extends Controller
{
    public function index() { return redirect()->route('client.reports.social.overview'); }

    public function overview()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '90d');
        $connected = $this->connectedIntegrations();
        $data = [
            'has_data' => $connected->isNotEmpty(),
            'total_followers' => $this->sumMetric($connected, 'followers'),
            'total_posts' => $this->sumMetric($connected, 'posts_count'),
            'engagement_rate' => $this->avgEngagementRate($connected),
            'sentiment_score' => 0,
            'connected_count' => $connected->count(),
        ];
        return view('client.reports.social.overview', compact('data', 'period'));
    }

    public function followers()
    {
        $period = request('period', '90d');
        $connected = $this->connectedIntegrations();
        $data = [
            'has_data' => $connected->isNotEmpty(),
            'total_followers' => $this->sumMetric($connected, 'followers'),
            'platforms' => $this->platformBreakdown($connected),
        ];
        return view('client.reports.social.followers', compact('data', 'period'));
    }

    public function engagement()
    {
        $period = request('period', '90d');
        $connected = $this->connectedIntegrations();
        $posts = $this->recentMedia($connected, $period);
        $data = [
            'has_data' => $connected->isNotEmpty(),
            'engagement_rate' => $this->avgEngagementRate($connected),
            'platforms' => $this->platformBreakdown($connected),
            'total_likes' => (int) $posts->sum('like_count'),
            'total_comments' => (int) $posts->sum('comments_count'),
            'total_reach' => (int) $posts->sum('reach'),
            'posts' => $posts,
        ];
        return view('client.reports.social.engagement', compact('data', 'period'));
    }

    public function contentPerformance()
    {
        $period = request('period', '90d');
        $connected = $this->connectedIntegrations();
        $posts = $this->recentMedia($connected, $period)->sortByDesc(fn ($p) => $p->like_count + $p->comments_count)->values();
        $data = [
            'has_data' => $connected->isNotEmpty(),
            'total_posts' => $posts->count(),
            'platforms' => $this->platformBreakdown($connected),
            'posts' => $posts,
        ];
        return view('client.reports.social.content-performance', compact('data', 'period'));
    }

    public function sentiment()
    {
        $period = request('period', '90d');
        $connected = $this->connectedIntegrations();
        $data = [
            'has_data' => $connected->isNotEmpty(),
            // No comment-text sentiment classifier is wired up yet (would need
            // to read each post's comments and run them through a sentiment
            // model) — shown honestly as "not available" rather than a made-up
            // score, same real-data-only rule as the rest of this app.
            'sentiment_available' => false,
        ];
        return view('client.reports.social.sentiment', compact('data', 'period'));
    }

    public function competitorAnalysis()
    {
        $connected = $this->connectedIntegrations();
        $data = [
            'has_data' => $connected->isNotEmpty(),
            // No competitor account is tracked anywhere in this app yet — this
            // would need its own connection (e.g. a second Instagram handle
            // explicitly marked as a competitor to watch). Shown honestly
            // rather than invented numbers.
            'competitors_available' => false,
        ];
        return view('client.reports.social.competitor-analysis', compact('data'));
    }

    public function getData(Request $request, string $metric)
    {
        return response()->json(['metric' => $metric, 'data' => []]);
    }

    public function export(Request $request, string $format)
    {
        return response()->json(['message' => 'Export not yet implemented']);
    }

    /**
     * The real connected platforms, straight from the same social_integrations
     * table the Social Connections page (SocialConnectionController) reads and
     * writes — this controller used to check social_connections/social_metrics,
     * two tables that don't exist in this database, so it silently reported
     * "no data" regardless of any real connection.
     */
    private function connectedIntegrations()
    {
        return SocialIntegration::where('status', 'connected')->forClient(auth('client')->id())->get();
    }

    private function sumMetric($connected, string $key): int
    {
        return (int) $connected->sum(fn (SocialIntegration $c) => $c->metrics[$key] ?? 0);
    }

    private function avgEngagementRate($connected): float
    {
        if ($connected->isEmpty()) return 0;
        $rates = $connected->map(fn (SocialIntegration $c) => (float) ($c->metrics['engagement_rate'] ?? 0));
        return round($rates->avg(), 2);
    }

    /**
     * Real synced posts (App\Jobs\SyncInstagramPosts) for the connected
     * integrations, within the selected period.
     */
    private function recentMedia($connected, string $period)
    {
        $ids = $connected->pluck('id');
        if ($ids->isEmpty()) return collect();

        $days = match ($period) {
            '7d' => 7,
            '90d' => 90,
            '1y' => 365,
            default => 30,
        };

        return InstagramMedia::whereIn('social_integration_id', $ids)
            ->where('posted_at', '>=', now()->subDays($days))
            ->orderByDesc('posted_at')
            ->get();
    }

    private function platformBreakdown($connected): array
    {
        return $connected->map(fn (SocialIntegration $c) => [
            'platform' => $c->platform,
            'name' => $c->connection_name,
            'followers' => $c->metrics['followers'] ?? 0,
            'posts_count' => $c->metrics['posts_count'] ?? 0,
            'engagement_rate' => $c->metrics['engagement_rate'] ?? 0,
        ])->values()->all();
    }
}
