<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\ChatSupportIntegration;
use App\Models\SlackChannel;
use App\Models\SlackMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatSupportAnalyticsController extends Controller
{
    public function index() { return redirect()->route('client.reports.chat-support.overview'); }

    public function overview()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '90d');

        $slack = app(\App\Services\SlackAnalysisService::class)->report($client->id);
        $connected = $this->connectedIntegrations($client);

        $data = [
            'has_data' => $connected->isNotEmpty(),
            'total_conversations' => $slack['channel_count'] ?? 0,
            'avg_response_time' => $this->avgResponseTime($connected),
            'csat_score' => null, // no real CSAT survey data source exists — see satisfaction()
            'open_tickets' => 0,  // Slack has no "ticket" concept; always 0, not fabricated
            'connected_count' => $connected->count(),
            'slack' => $slack, // real payload: answer, message_count, top_channels, top_contributors, busiest_hour
        ];
        return view('client.reports.chat-support.overview', compact('data', 'period'));
    }

    public function conversations()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '90d');
        $connected = $this->connectedIntegrations($client);
        $messages = $this->recentMessages($connected, $period);

        $data = [
            'has_data' => $connected->isNotEmpty(),
            'total' => $messages->count(),
            'messages' => $messages,
        ];
        return view('client.reports.chat-support.conversations', compact('data', 'period'));
    }

    public function responseTime()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '90d');
        $connected = $this->connectedIntegrations($client);

        $data = [
            'has_data' => $connected->isNotEmpty(),
            'avg_response_time' => $this->avgResponseTime($connected),
            'by_channel' => $this->responseTimeByChannel($connected),
        ];
        return view('client.reports.chat-support.response-time', compact('data', 'period'));
    }

    public function satisfaction()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '90d');
        $connected = $this->connectedIntegrations($client);

        $data = [
            'has_data' => $connected->isNotEmpty(),
            // No CSAT survey integration exists (Slack gives no satisfaction
            // signal on its own) — shown honestly rather than a fabricated score.
            'csat_available' => false,
        ];
        return view('client.reports.chat-support.satisfaction', compact('data', 'period'));
    }

    public function teamPerformance()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '90d');
        $connected = $this->connectedIntegrations($client);
        $messages = $this->recentMessages($connected, $period);

        $contributors = $messages->filter(fn ($m) => $m->user_id)
            ->groupBy('user_id')
            ->map(fn ($g, $userId) => ['user_id' => $userId, 'messages' => $g->count()])
            ->sortByDesc('messages')->values();

        $data = [
            'has_data' => $connected->isNotEmpty(),
            'contributors' => $contributors,
        ];
        return view('client.reports.chat-support.team-performance', compact('data', 'period'));
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
     * Real connected chat/support integrations (chat_support_integrations) —
     * the previous version of this controller checked chat_support_connections
     * and chat_conversations, two tables that don't exist in this database,
     * so every page here silently reported "no data" no matter what was
     * connected. Same bug shape SocialAnalyticsController had.
     */
    private function connectedIntegrations($client)
    {
        return ChatSupportIntegration::where('client_id', $client->id)
            ->where('status', 'connected')
            ->get();
    }

    private function periodDays(string $period): int
    {
        return match ($period) {
            '7d' => 7,
            '30d' => 30,
            '1y' => 365,
            default => 90,
        };
    }

    private function recentMessages($connected, string $period)
    {
        $channelIds = SlackChannel::whereIn('chat_support_integration_id', $connected->pluck('id'))->pluck('id');
        if ($channelIds->isEmpty()) return collect();

        return SlackMessage::whereIn('slack_channel_id', $channelIds)
            ->where('posted_at', '>=', now()->subDays($this->periodDays($period)))
            ->orderByDesc('posted_at')
            ->get();
    }

    /**
     * A real, honestly-labeled proxy: the median gap between consecutive
     * messages in the same channel, across every connected integration.
     * Slack itself exposes no "ticket response time" concept, so this is
     * the closest real signal available from message timestamps alone.
     */
    private function avgResponseTime($connected): ?string
    {
        $channelIds = SlackChannel::whereIn('chat_support_integration_id', $connected->pluck('id'))->pluck('id');
        if ($channelIds->isEmpty()) return null;

        $gaps = [];
        foreach ($channelIds as $channelId) {
            $timestamps = SlackMessage::where('slack_channel_id', $channelId)
                ->whereNotNull('posted_at')
                ->orderBy('posted_at')
                ->pluck('posted_at');

            for ($i = 1; $i < $timestamps->count(); $i++) {
                $gaps[] = abs($timestamps[$i]->diffInSeconds($timestamps[$i - 1]));
            }
        }

        if (empty($gaps)) return null;

        sort($gaps);
        $median = $gaps[(int) floor(count($gaps) / 2)];

        $minutes = intdiv($median, 60);
        $seconds = $median % 60;
        return $minutes > 0 ? "{$minutes}m {$seconds}s" : "{$seconds}s";
    }

    private function responseTimeByChannel($connected): array
    {
        return SlackChannel::whereIn('chat_support_integration_id', $connected->pluck('id'))
            ->withCount('messages')
            ->having('messages_count', '>', 1)
            ->get()
            ->map(function (SlackChannel $c) {
                $timestamps = $c->messages()->whereNotNull('posted_at')->orderBy('posted_at')->pluck('posted_at');
                $gaps = [];
                for ($i = 1; $i < $timestamps->count(); $i++) {
                    $gaps[] = abs($timestamps[$i]->diffInSeconds($timestamps[$i - 1]));
                }
                sort($gaps);
                $median = $gaps[(int) floor(count($gaps) / 2)] ?? 0;
                return [
                    'name' => $c->name ?: $c->channel_id,
                    'messages' => $c->messages_count,
                    'median_gap_seconds' => $median,
                ];
            })->values()->all();
    }
}
