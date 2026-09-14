<?php

namespace App\Services;

use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use App\Services\Marketing\Concerns\ScoresMqlPool;

/**
 * Answers the Marketing agent's Insights questions about the "MQL → Sales"
 * pool — the group of contacts qualified enough to hand off to Sales.
 * Pool scoring (buying_readiness, trust, at-risk) comes from crm_contacts +
 * crm_deals via the ScoresMqlPool trait — see that trait's docblock for how
 * the two scores are derived and what "in MQL → Sales" means.
 *
 * The scoring itself is pure arithmetic — no AI required. An OpenAI key
 * (config('services.openai')) is only used, when configured, to turn the
 * scored pool into a short written answer; without a key this still
 * returns a correct, data-grounded plain-text summary.
 */
class MarketingInsightsService
{
    use ScoresMqlPool;

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function proofOrOfferAudience(): array
    {
        $pool = $this->mqlReadyPool();

        if ($pool->isEmpty()) {
            return ['ranked' => [], 'answer' => 'No accounts are in MQL → Sales right now to judge this by.', 'ai_used' => false];
        }

        $avgTrust = (int) round($pool->avg('trust'));
        $verdict = $avgTrust < self::TRUST_THRESHOLD ? 'proof audience' : 'offer audience';

        $ranked = $pool->sortByDesc('deal_value')->values()->all();

        $plainSummary = $avgTrust < self::TRUST_THRESHOLD
            ? "Proof audience — average trust {$avgTrust} across " . $pool->count() . ' MQL-ready account(s) is still below the ' . self::TRUST_THRESHOLD . ' bar, so lead with evidence, not an offer.'
            : "Offer audience — average trust {$avgTrust} across " . $pool->count() . ' MQL-ready account(s) is solid, so a time-boxed incentive can safely accelerate the decision.';

        return $this->buildAnswer(
            $ranked,
            'Is MQL → Sales a proof audience or an offer audience?',
            'No accounts are in MQL → Sales right now to judge this by.',
            fn () => $plainSummary,
            ['verdict' => $verdict, 'avg_trust' => $avgTrust, 'trust_threshold' => self::TRUST_THRESHOLD]
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function oneLever(): array
    {
        $pool = $this->mqlReadyPool();

        if ($pool->isEmpty()) {
            return ['ranked' => [], 'answer' => 'No accounts are in MQL → Sales right now, so there is no lever to pull yet.', 'ai_used' => false];
        }

        $modalStage = $pool->countBy('stage')->sortDesc()->keys()->first();
        $modalLabel = self::STAGE_LABELS[$modalStage] ?? $modalStage;
        $avgTrust = (int) round($pool->avg('trust'));

        $leverByStage = [
            'appointmentscheduled' => 'getting the scheduled call to actually happen and land on a concrete next step',
            'qualifiedtobuy' => 'a second, more concrete touch — a proof point matched to their stated concern — to move them into a scheduled conversation',
            'presentationscheduled' => 'a tight follow-up right after the demo, while the value is still fresh, rather than waiting for them to reach out',
            'decisionmakerboughtin' => 'a direct, low-risk close — the buyer is already convinced, so the only thing left to move is the paperwork',
            'closedwon' => 'a fast, personal handoff into onboarding — the deal is already won, the risk now is losing momentum before day one',
        ];
        $lever = $leverByStage[$modalStage] ?? 'a second touch that matches where most of the pool actually sits in the funnel';

        $ranked = $pool->sortByDesc('deal_value')->values()->all();

        $plainSummary = 'The pool of ' . $pool->count() . " MQL-ready account(s) is concentrated at \"{$modalLabel}\" (average trust {$avgTrust}). "
            . 'The single lever that moves the most accounts right now: ' . $lever . '.';

        return $this->buildAnswer(
            $ranked,
            'What is the one lever that moves MQL → Sales?',
            'No accounts are in MQL → Sales right now, so there is no lever to pull yet.',
            fn () => $plainSummary,
            ['modal_stage' => $modalLabel, 'avg_trust' => $avgTrust, 'lever' => $lever]
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function whyNameNotWithSales(string $name): array
    {
        $needle = trim(mb_strtolower($name));
        $match = $this->scoredContacts()->first(
            fn (array $row) => mb_strtolower($row['name']) === $needle || mb_strtolower($row['company']) === $needle
        );

        if ($match === null) {
            return ['ranked' => [], 'answer' => "No synced contact matches \"{$name}\".", 'ai_used' => false];
        }

        $gap = self::READY_THRESHOLD - $match['buying_readiness'];

        $plainSummary = $match['at_risk']
            ? "{$match['name']} ({$match['company']}) is flagged at-risk — {$match['days_since_activity']} days quiet with a deal on file — so it routes to Retention instead of a Sales hand-off, regardless of readiness."
            : ($gap > 0
                ? "{$match['name']} ({$match['company']}) is at '{$match['stage_label']}' — buying readiness {$match['buying_readiness']}, still {$gap} point(s) short of the " . self::READY_THRESHOLD . ' bar Sales hands off from.'
                : "{$match['name']} ({$match['company']}) actually clears the readiness bar ({$match['buying_readiness']} ≥ " . self::READY_THRESHOLD . ') — it should already be with Sales; worth checking why it has not moved.');

        return $this->buildAnswer(
            [$match],
            "Why is {$name} here and not with Sales?",
            "No synced contact matches \"{$name}\".",
            fn () => $plainSummary,
            ['ready_threshold' => self::READY_THRESHOLD]
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function rulePutPeopleIntoMqlSales(): array
    {
        $pool = $this->mqlReadyPool();

        $plainSummary = 'An account becomes an MQL hand-off once buying readiness ≥ ' . self::READY_THRESHOLD
            . ' and it is not flagged at-risk (quiet ' . self::AT_RISK_DAYS . '+ days with a deal on file). '
            . 'That is the same bar Sales uses for its own "call" tier, so nothing gets double-worked. '
            . $pool->count() . ' synced account(s) currently clear it.';

        return $this->buildAnswer(
            $pool->sortByDesc('deal_value')->values()->all(),
            'What rule put people into MQL → Sales?',
            'No accounts currently clear the MQL → Sales rule.',
            fn () => $plainSummary,
            ['ready_threshold' => self::READY_THRESHOLD, 'at_risk_days' => self::AT_RISK_DAYS, 'pool_size' => $pool->count()]
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function changedLast7Days(): array
    {
        $recent = $this->recentlyQualifiedPool(7);

        if ($recent->isEmpty()) {
            return ['ranked' => [], 'answer' => 'No new accounts crossed the MQL bar in the last 7 days.', 'ai_used' => false];
        }

        $top = $recent->first();
        $plainSummary = "{$top['name']} ({$top['company']}) crossed the MQL bar most recently — active {$top['days_since_activity']} day(s) ago at '{$top['stage_label']}', readiness {$top['buying_readiness']}. "
            . ($recent->count() > 1 ? ($recent->count() - 1) . ' more account(s) also moved into the pool this week.' : 'Nobody else moved into the pool this week.');

        return $this->buildAnswer(
            $recent->all(),
            'What changed in the last 7 days?',
            'No new accounts crossed the MQL bar in the last 7 days.',
            fn () => $plainSummary
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
        $system = 'You are the Marketing copilot inside a B2B analytics platform, answering questions about the '
            . '"MQL → Sales" hand-off pool. Answer using ONLY the JSON data provided — never invent a name, '
            . 'number, or reason that is not in it. Output PLAIN TEXT only, never JSON or markdown — a short, direct '
            . 'paragraph. Be brief and concrete, in plain English, under 120 words.';

        $prompt = "Question: {$question}\n\nContext:\n" . json_encode($context, JSON_PRETTY_PRINT)
            . "\n\nAccounts:\n" . json_encode($ranked, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 300]);
    }
}
