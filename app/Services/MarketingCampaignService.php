<?php

namespace App\Services;

use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use App\Services\Marketing\Concerns\ScoresMqlPool;
use Illuminate\Support\Collection;

/**
 * Answers the Marketing agent's Campaign questions — the actual outreach
 * copy for the "MQL → Sales" pool. Pool scoring (buying_readiness, trust,
 * at-risk) comes from crm_contacts + crm_deals via the ScoresMqlPool trait
 * — see that trait's docblock for how the two scores are derived.
 *
 * Every piece of copy is grounded in the same real signal:
 *   - the pool's dominant funnel stage decides what the touch should be
 *     about (a proof point for an early stage, a direct close for a late
 *     one)
 *   - the pool's average trust decides the tone (evidence-led when trust
 *     is still behind readiness, a direct offer when it is not) — the same
 *     proof-vs-offer test MarketingInsightsService uses for
 *     "Is MQL → Sales a proof or an offer audience?", so the Campaign step
 *     never contradicts the Insights step it follows
 *   - the single highest-value account currently in the pool is used as
 *     the real example addressed in one-to-one formats (WhatsApp, SMS)
 *
 * The pool scoring is pure arithmetic — no AI required. An OpenAI key
 * (config('services.openai')) is only used, when configured, to turn that
 * grounding into the actual written copy; without a key this still returns
 * a correct, data-grounded plain-text draft.
 */
class MarketingCampaignService
{
    use ScoresMqlPool;

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function emailSequence(): array
    {
        $pool = $this->mqlReadyPool();

        if ($pool->isEmpty()) {
            return ['ranked' => [], 'answer' => 'No accounts are in MQL → Sales right now, so there is nothing to sequence yet.', 'ai_used' => false];
        }

        [$modalLabel, $avgTrust, $verdict] = $this->poolSignal($pool);
        $sample = $pool->sortByDesc('deal_value')->take(5)->values()->all();

        $plainSummary = "3-touch sequence for the " . $pool->count() . " account(s) in MQL → Sales (mostly at \"{$modalLabel}\", average trust {$avgTrust}, a {$verdict}):\n"
            . "1. What's new / what they've been missing — no ask.\n"
            . '2. One proof point matched to the "' . $modalLabel . "\" stage they're sitting at.\n"
            . '3. A direct, low-risk invitation to talk to Sales' . ($verdict === 'offer audience' ? ', with a time-boxed incentive to accelerate.' : ', proof-led, no discount yet.');

        return $this->buildAnswer(
            $sample,
            'Write the 3-touch email sequence for MQL → Sales',
            'No accounts are in MQL → Sales right now, so there is nothing to sequence yet.',
            fn () => $plainSummary,
            ['modal_stage' => $modalLabel, 'avg_trust' => $avgTrust, 'verdict' => $verdict, 'pool_size' => $pool->count()],
            'Write a 3-touch B2B email sequence for the MQL → Sales hand-off pool described in the context and account '
                . 'sample. Output PLAIN TEXT only, never JSON or markdown — three short paragraphs, each starting with '
                . '"Touch 1:", "Touch 2:", "Touch 3:" on its own line. Ground the proof point in the stage most of the '
                . 'pool sits at, and match the tone (proof-led vs offer-led) to the verdict given.'
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function whatsappOneLiner(): array
    {
        return $this->oneToOneCopy(
            'Give me the WhatsApp one-liner for MQL → Sales',
            'No accounts are in MQL → Sales right now to write a WhatsApp message for.',
            'Write ONE short WhatsApp message (under 300 characters, casual but professional, one emoji at most) to the '
                . 'named account in the context, referencing their real stage naturally without sounding like a template.'
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function smsOptout(): array
    {
        return $this->oneToOneCopy(
            'SMS version with opt-out for MQL → Sales',
            'No accounts are in MQL → Sales right now to write an SMS for.',
            'Write ONE short SMS (under 160 characters) to the named account in the context, and it MUST end with an '
                . 'opt-out instruction (e.g. "Reply STOP to opt out").'
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function discountOrProof(): array
    {
        $pool = $this->mqlReadyPool();

        if ($pool->isEmpty()) {
            return ['ranked' => [], 'answer' => 'No accounts are in MQL → Sales right now to judge this by.', 'ai_used' => false];
        }

        [, $avgTrust, $verdict] = $this->poolSignal($pool);
        $ranked = $pool->sortByDesc('deal_value')->values()->all();

        $plainSummary = $verdict === 'proof audience'
            ? "Proof only — average trust {$avgTrust} across " . $pool->count() . ' MQL-ready account(s) is still below the ' . self::TRUST_THRESHOLD . ' bar, so a case study or result beats a discount right now.'
            : "A discount is safe to offer — average trust {$avgTrust} across " . $pool->count() . ' MQL-ready account(s) is solid, so a time-boxed incentive can accelerate the decision.';

        return $this->buildAnswer(
            $ranked,
            'Can MQL → Sales get a discount, or proof only?',
            'No accounts are in MQL → Sales right now to judge this by.',
            fn () => $plainSummary,
            ['verdict' => $verdict, 'avg_trust' => $avgTrust, 'trust_threshold' => self::TRUST_THRESHOLD]
        );
    }

    /**
     * Shared shape for the one-to-one copy questions (WhatsApp, SMS): both
     * address the single highest-value account currently in the MQL pool,
     * with the same stage/trust grounding as the sequence and discount
     * questions.
     *
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    private function oneToOneCopy(string $question, string $emptyMessage, string $instruction): array
    {
        $pool = $this->mqlReadyPool();

        if ($pool->isEmpty()) {
            return ['ranked' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $top = $pool->sortByDesc('deal_value')->first();
        [, , $verdict] = $this->poolSignal($pool);

        $plainSummary = "\"Hi {$top['name']} 👋 — noticed {$top['company']} has been active on our side, at the "
            . "'{$top['stage_label']}' stage. Happy to help directly here if useful.\"";

        return $this->buildAnswer(
            [$top],
            $question,
            $emptyMessage,
            fn () => $plainSummary,
            ['verdict' => $verdict],
            $instruction
        );
    }

    /**
     * @return array{0: string, 1: int, 2: string} [modal stage label, average trust, 'proof audience'|'offer audience']
     */
    private function poolSignal(Collection $pool): array
    {
        $modalStage = $pool->countBy('stage')->sortDesc()->keys()->first();
        $modalLabel = self::STAGE_LABELS[$modalStage] ?? ($modalStage ?? 'no deal on file');
        $avgTrust = (int) round($pool->avg('trust'));
        $verdict = $avgTrust < self::TRUST_THRESHOLD ? 'proof audience' : 'offer audience';

        return [$modalLabel, $avgTrust, $verdict];
    }

    private function buildAnswer(array $ranked, string $question, string $emptyMessage, callable $plainSummary, array $context = [], ?string $instruction = null): array
    {
        if (empty($ranked)) {
            return ['ranked' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $client = app(OpenAiClient::class);

        if ($client->isConfigured()) {
            try {
                return [
                    'ranked' => $ranked,
                    'answer' => $this->askOpenAi($client, $question, $ranked, $context, $instruction),
                    'ai_used' => true,
                ];
            } catch (OpenAiException $e) {
                report($e);
                // Fall through to the plain-text draft below — a broken AI
                // call should never take down a page that has real data.
            }
        }

        return ['ranked' => $ranked, 'answer' => $plainSummary($ranked), 'ai_used' => false];
    }

    private function askOpenAi(OpenAiClient $client, string $question, array $ranked, array $context, ?string $instruction): string
    {
        $system = 'You are the Marketing copilot inside a B2B analytics platform, writing outreach copy for the '
            . '"MQL → Sales" hand-off pool. Use ONLY the JSON data provided — never invent a name, number, or detail '
            . 'that is not in it. Unless told otherwise below, output PLAIN TEXT only, never JSON or markdown. '
            . ($instruction ?? 'Answer briefly and concretely, in plain English, under 120 words.');

        $prompt = "Question: {$question}\n\nContext:\n" . json_encode($context, JSON_PRETTY_PRINT)
            . "\n\nAccount(s):\n" . json_encode($ranked, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 300]);
    }
}
