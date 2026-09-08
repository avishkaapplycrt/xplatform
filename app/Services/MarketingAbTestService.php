<?php

namespace App\Services;

use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use App\Services\Marketing\Concerns\ScoresMqlPool;

/**
 * Answers the Marketing agent's A/B test questions about the "MQL → Sales"
 * pool. Pool scoring (buying_readiness, trust, at-risk) comes from
 * crm_contacts + crm_deals via the ScoresMqlPool trait — see that trait's
 * docblock for how the two scores are derived and what "in MQL → Sales"
 * means.
 *
 * Every test recommendation is grounded in the pool's real size:
 *   - "proof vs offer" reuses the same trust test MarketingInsightsService
 *     and MarketingCampaignService use, so the A/B test step never proposes
 *     testing something Insights has already settled with confidence
 *   - "per-arm size" and "is my holdout enough" both work from the actual
 *     count of synced MQL-ready contacts, not a fixed assumption — a
 *     15% holdout means something very different against 6 accounts than
 *     against 600
 *
 * The pool scoring is pure arithmetic — no AI required. An OpenAI key
 * (config('services.openai')) is only used, when configured, to turn that
 * grounding into the written recommendation; without a key this still
 * returns a correct, data-grounded plain-text answer.
 */
class MarketingAbTestService
{
    use ScoresMqlPool;

    /**
     * A result below this size is too small to trust even a clear-looking
     * winner — used to flag "directional only" rather than invent a
     * textbook minimum sample size crm_contacts/crm_deals can't support.
     */
    private const RELIABLE_POOL_SIZE = 30;

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function proofVsOfferTest(): array
    {
        $pool = $this->mqlReadyPool();

        if ($pool->isEmpty()) {
            return ['ranked' => [], 'answer' => 'No accounts are in MQL → Sales right now to design a test for.', 'ai_used' => false];
        }

        $avgTrust = (int) round($pool->avg('trust'));
        $gap = abs($avgTrust - self::TRUST_THRESHOLD);
        $tooClose = $gap <= 10;

        $plainSummary = $tooClose
            ? "Test proof vs offer head-to-head — average trust {$avgTrust} across " . $pool->count() . ' MQL-ready account(s) sits close to the ' . self::TRUST_THRESHOLD . ' bar, so it is not clear-cut enough to call without a test.'
            : ($avgTrust < self::TRUST_THRESHOLD
                ? "No need to test — average trust {$avgTrust} across " . $pool->count() . ' MQL-ready account(s) is clearly below the ' . self::TRUST_THRESHOLD . ' bar; lead with proof, that call is already confident enough.'
                : "No need to test — average trust {$avgTrust} across " . $pool->count() . ' MQL-ready account(s) is clearly above the ' . self::TRUST_THRESHOLD . ' bar; an offer is already the confident call.');

        return $this->buildAnswer(
            $pool->sortByDesc('deal_value')->values()->all(),
            'Should I test proof vs offer on MQL → Sales?',
            'No accounts are in MQL → Sales right now to design a test for.',
            fn () => $plainSummary,
            ['avg_trust' => $avgTrust, 'trust_threshold' => self::TRUST_THRESHOLD, 'too_close_to_call' => $tooClose, 'pool_size' => $pool->count()]
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function sampleSizePerArm(): array
    {
        $pool = $this->mqlReadyPool();

        if ($pool->isEmpty()) {
            return ['ranked' => [], 'answer' => 'No accounts are in MQL → Sales right now to split into test arms.', 'ai_used' => false];
        }

        $poolSize = $pool->count();
        $perArm = (int) floor($poolSize / 2);
        $reliable = $poolSize >= self::RELIABLE_POOL_SIZE;

        $plainSummary = "Split the {$poolSize} account(s) currently in MQL → Sales evenly: {$perArm} per arm. "
            . ($reliable
                ? 'That is enough to treat a clear result as a real signal, not noise.'
                : "That is below the ~" . self::RELIABLE_POOL_SIZE . ' accounts needed for a statistically solid read — treat this test as directional, and let it run longer before acting on a narrow win.');

        return $this->buildAnswer(
            $pool->sortByDesc('deal_value')->values()->all(),
            'How many per arm do I need for MQL → Sales?',
            'No accounts are in MQL → Sales right now to split into test arms.',
            fn () => $plainSummary,
            ['pool_size' => $poolSize, 'per_arm' => $perArm, 'reliable_pool_size' => self::RELIABLE_POOL_SIZE, 'statistically_reliable' => $reliable]
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function holdoutEnough(int $holdoutPercent = 15): array
    {
        $pool = $this->mqlReadyPool();

        if ($pool->isEmpty()) {
            return ['ranked' => [], 'answer' => 'No accounts are in MQL → Sales right now to hold out from.', 'ai_used' => false];
        }

        $poolSize = $pool->count();
        $holdoutCount = (int) round($poolSize * $holdoutPercent / 100);
        $enough = $holdoutCount >= 5;

        $plainSummary = "A {$holdoutPercent}% holdout on {$poolSize} account(s) is {$holdoutCount} account(s). "
            . ($enough
                ? 'That is workable for a directional read, but with a pool this size the margin of error is still wide — treat a borderline lift as inconclusive rather than a clear win.'
                : "That is too small to mean anything — {$holdoutCount} account(s) can swing entirely on one or two outcomes. Either raise the holdout percentage or wait for the pool to grow before trusting a lift number.");

        return $this->buildAnswer(
            $pool->sortByDesc('deal_value')->values()->all(),
            "Is my {$holdoutPercent}% holdout enough for MQL → Sales?",
            'No accounts are in MQL → Sales right now to hold out from.',
            fn () => $plainSummary,
            ['pool_size' => $poolSize, 'holdout_percent' => $holdoutPercent, 'holdout_count' => $holdoutCount, 'enough' => $enough]
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
                // Fall through to the plain-text answer below — a broken AI
                // call should never take down a page that has real data.
            }
        }

        return ['ranked' => $ranked, 'answer' => $plainSummary($ranked), 'ai_used' => false];
    }

    private function askOpenAi(OpenAiClient $client, string $question, array $ranked, array $context): string
    {
        $system = 'You are the Marketing copilot inside a B2B analytics platform, advising on an A/B test design for '
            . 'the "MQL → Sales" hand-off pool. Answer using ONLY the JSON data provided — never invent a name, '
            . 'number, or statistic that is not in it. Output PLAIN TEXT only, never JSON or markdown — a short, '
            . 'direct paragraph. Be brief, concrete and honest about small-sample limits, in plain English, under '
            . '120 words.';

        $prompt = "Question: {$question}\n\nContext:\n" . json_encode($context, JSON_PRETTY_PRINT)
            . "\n\nAccounts:\n" . json_encode($ranked, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 300]);
    }
}
