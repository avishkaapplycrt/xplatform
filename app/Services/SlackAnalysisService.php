<?php

namespace App\Services;

use App\Models\ChatSupportIntegration;
use App\Models\SlackChannel;
use App\Models\SlackMessage;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use Illuminate\Support\Collection;

/**
 * Real numbers for the Chat & Support "Slack" report — same shape as
 * InstagramAnalysisService: plain arithmetic over slack_channels /
 * slack_messages first, OpenAI only narrates it, and every method still
 * returns a correct plain-text answer with no key configured.
 */
class SlackAnalysisService
{
    public function report(int $clientId): array
    {
        $connection = ChatSupportIntegration::where('client_id', $clientId)
            ->where('provider', 'slack')
            ->where('status', 'connected')
            ->first();

        if (!$connection) {
            return ['connected' => false, 'answer' => 'No Slack workspace connected yet.', 'ai_used' => false];
        }

        $channelIds = SlackChannel::where('chat_support_integration_id', $connection->id)->pluck('id');
        $messages = SlackMessage::whereIn('slack_channel_id', $channelIds)
            ->orderByDesc('posted_at')->limit(2000)->get();

        $payload = [
            'connected'        => true,
            'workspace'        => $connection->workspace_name,
            'channel_count'    => $channelIds->count(),
            'message_count'    => $messages->count(),
            'messages_per_day' => $this->messagesPerDay($messages),
            'busiest_hour'     => $this->busiestHour($messages),
            'top_channels'     => $this->topChannels($connection),
            'top_contributors' => $this->topContributors($messages),
        ];

        $plain = $this->plainSummary($payload);
        $client = app(OpenAiClient::class);

        if ($client->isConfigured() && $messages->isNotEmpty()) {
            try {
                return $payload + ['answer' => $this->askOpenAi($client, $payload), 'ai_used' => true];
            } catch (OpenAiException $e) {
                report($e);
            }
        }

        return $payload + ['answer' => $plain, 'ai_used' => false];
    }

    private function messagesPerDay(Collection $messages): float
    {
        if ($messages->isEmpty()) return 0.0;
        $days = max(1, $messages->min('posted_at')->diffInDays(now()));
        return round($messages->count() / $days, 1);
    }

    private function busiestHour(Collection $messages): ?array
    {
        $withTime = $messages->filter(fn ($m) => $m->posted_at);
        if ($withTime->isEmpty()) return null;

        return $withTime->groupBy(fn ($m) => $m->posted_at->format('G'))
            ->map(fn ($group, $hour) => ['hour' => (int) $hour, 'messages' => $group->count()])
            ->sortByDesc('messages')->values()->first();
    }

    private function topChannels(ChatSupportIntegration $connection, int $limit = 5): array
    {
        return SlackChannel::where('chat_support_integration_id', $connection->id)
            ->withCount('messages')
            ->orderByDesc('messages_count')
            ->take($limit)->get()
            ->map(fn ($c) => ['name' => $c->name ?: $c->channel_id, 'messages' => $c->messages_count])
            ->all();
    }

    private function topContributors(Collection $messages, int $limit = 5): array
    {
        return $messages->filter(fn ($m) => $m->user_id)
            ->groupBy('user_id')
            ->map(fn ($group, $userId) => ['user_id' => $userId, 'messages' => $group->count()])
            ->sortByDesc('messages')->take($limit)->values()->all();
    }

    private function plainSummary(array $p): string
    {
        if ($p['message_count'] === 0) {
            return 'No messages synced yet — run a sync from Chat & Support once the bot has joined some channels.';
        }

        $top = $p['top_channels'][0] ?? null;
        $topLine = $top ? " Most active channel: #{$top['name']} ({$top['messages']} messages)." : '';

        return "{$p['workspace']}: {$p['message_count']} messages across {$p['channel_count']} channels, "
            . "averaging {$p['messages_per_day']}/day.{$topLine}";
    }

    private function askOpenAi(OpenAiClient $client, array $data): string
    {
        $system = 'You are the Chat & Support copilot inside a B2B analytics platform. '
            . 'Answer using ONLY the JSON data provided — never invent a channel, number, or user that is not in it. '
            . 'Plain prose, under 100 words, no markdown. Note that Slack user IDs are opaque identifiers, not names — '
            . "don't pretend to know who a user is beyond their ID.";

        $prompt = "Slack workspace activity summary:\n" . json_encode($data, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 280]);
    }
}
