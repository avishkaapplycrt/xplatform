<?php

namespace App\Services;

use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use App\Services\Marketing\Concerns\ScoresMqlPool;

/**
 * Answers the Marketing agent's Performance questions about who has newly
 * qualified into the "MQL → Sales" pool. Pool scoring (buying_readiness,
 * trust, at-risk) comes from crm_contacts + crm_deals via the ScoresMqlPool
 * trait — see that trait's docblock for how the scores are derived.
 *
 * Neither table stores a log of campaign sends or a history of when a deal
 * reached its current stage, so "since the last send" and "this week" are
 * both answered off the same proxy: recent last_activity_at (within 7 days)
 * on a contact that already clears the MQL bar — the same assumption
 * MarketingInsightsService::changedLast7Days() makes, via the trait's
 * recentlyQualifiedPool(), so all three "what's new in the pool" questions
 * agree with each other.
 *
 * The scoring is pure arithmetic — no AI required. An OpenAI key
 * (config('services.openai')) is only used, when configured, to turn that
 * list into a short written answer; without a key this still returns a
 * correct, data-grounded plain-text summary.
 */
class MarketingPerformanceService
{
    use ScoresMqlPool;

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function whoBecameMqlSinceLastSend(): array
    {
        $recent = $this->recentlyQualifiedPool(7);

        if ($recent->isEmpty()) {
            return ['ranked' => [], 'answer' => 'Nobody became an MQL since the last send — no synced contact both qualifies and has been active in the last 7 days.', 'ai_used' => false];
        }

        $plainSummary = $recent->count() . ' account(s) became an MQL since the last send: '
            . $recent->map(fn (array $row) => "{$row['name']} ({$row['company']}, {$row['stage_label']}, active {$row['days_since_activity']}d ago)")->implode('; ') . '.';

        return $this->buildAnswer(
            $recent->all(),
            'Who became an MQL since the last send?',
            'Nobody became an MQL since the last send — no synced contact both qualifies and has been active in the last 7 days.',
            fn () => $plainSummary,
            ['window_days' => 7, 'ready_threshold' => self::READY_THRESHOLD]
        );
    }

    /**
     * Marketing has no write access to Sales' pipeline, so "push" here means
     * confirm exactly who is ready and why — the same list
     * whoBecameMqlSinceLastSend() surfaces, framed as a hand-off action
     * instead of a status check.
     *
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function pushWeekMqlsToSales(): array
    {
        $recent = $this->recentlyQualifiedPool(7);

        if ($recent->isEmpty()) {
            return ['ranked' => [], 'answer' => 'Nothing to push to Sales this week — no synced contact both qualifies and has been active in the last 7 days.', 'ai_used' => false];
        }

        $plainSummary = 'Ready to push to Sales: '
            . $recent->map(fn (array $row) => "{$row['name']} ({$row['company']}) — {$row['stage_label']}, readiness {$row['buying_readiness']}")->implode('; ')
            . '. Confirm each still clears the readiness ≥ ' . self::READY_THRESHOLD . ' bar before handing off.';

        return $this->buildAnswer(
            $recent->all(),
            "Push this week's MQLs to Sales",
            'Nothing to push to Sales this week — no synced contact both qualifies and has been active in the last 7 days.',
            fn () => $plainSummary,
            ['window_days' => 7, 'ready_threshold' => self::READY_THRESHOLD, 'action' => 'hand_off_to_sales']
        );
    }

    private function buildAnswer(array $ranked, string $question, string $emptyMessage, callable $plainSummary, array $context = []): array
    {
        if (empty($ranked)) {
            return ['ranked' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $client = app(OpenAiClient::class);

        if ($client->isConfigured()) {
            try {
                return [
                    'ranked' => $ranked,
                    'answer' => $this->askOpenAi($client, $question, $ranked, $context),
                    'ai_used' => true,
                ];
            } catch (OpenAiException $e) {
                report($e);
                // Fall through to the plain-text summary below — a broken
                // AI call should never take down a page that has real data.
            }
        }

        return ['ranked' => $ranked, 'answer' => $plainSummary($ranked), 'ai_used' => false];
    }

    private function askOpenAi(OpenAiClient $client, string $question, array $ranked, array $context): string
    {
        $system = 'You are the Marketing copilot inside a B2B analytics platform, reporting on the "MQL → Sales" '
            . 'hand-off pool. Answer using ONLY the JSON data provided — never invent a name, number, or reason that '
            . 'is not in it. Output PLAIN TEXT only, never JSON or markdown — a short, direct paragraph. Name the '
            . 'accounts involved. Be brief and concrete, in plain English, under 120 words.';

        $prompt = "Question: {$question}\n\nContext:\n" . json_encode($context, JSON_PRETTY_PRINT)
            . "\n\nAccounts:\n" . json_encode($ranked, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 300]);
    }
}
