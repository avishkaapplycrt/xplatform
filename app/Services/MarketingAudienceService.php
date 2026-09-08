<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use Illuminate\Support\Collection;

/**
 * Answers the Marketing agent's Audience questions —
 * "Who must be excluded from every send, and why?" and
 * "Who is in a live sales cycle — leave them alone?" — from two real
 * tables:
 *   - crm_contacts → identity (name, company, last activity)
 *   - crm_deals    → deal value, stage and status, matched to a contact by
 *                    "deal name starts with company name" (the same
 *                    heuristic used by RetentionSaveFirstService and
 *                    SalesCustomerIntelligenceService, since neither table
 *                    stores a real foreign key between them)
 *
 * "Exclude from every send" flags a contact whose own deal history already
 * shows a negative signal — a lost deal, or an open deal that has gone
 * quiet for a long stretch — because a marketing touch there either lands
 * on someone who has already said no, or on an account that has drifted
 * into churn risk and belongs to Retention, not Marketing.
 *
 * "Live sales cycle" flags the opposite: a contact with an open deal in an
 * active, late-funnel stage who has been active recently — Sales is
 * already mid-conversation with them, so a marketing send would step on
 * that conversation rather than help it.
 *
 * Both rankings are pure arithmetic over crm_contacts + crm_deals — no AI
 * required. An OpenAI key (config('services.openai')) is only used, when
 * configured, to turn the ranked list into a short written answer; without
 * a key this still returns a correct, data-grounded plain-text summary.
 */
class MarketingAudienceService
{
    private const STALLED_DAYS_THRESHOLD = 90;
    private const RECENT_DAYS_THRESHOLD = 30;

    private const ACTIVE_LATE_STAGES = [
        'qualifiedtobuy',
        'appointmentscheduled',
        'presentationscheduled',
        'decisionmakerboughtin',
    ];

    private const STAGE_LABELS = [
        'qualifiedtobuy' => 'Qualified to buy',
        'appointmentscheduled' => 'Appointment scheduled',
        'presentationscheduled' => 'Presentation scheduled',
        'decisionmakerboughtin' => 'Decision-maker bought in',
    ];

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function excludeFromEverySend(int $limit = 8): array
    {
        return $this->buildAnswer(
            $this->rankExcluded($limit),
            'Who must be excluded from every send, and why?',
            'Nobody needs excluding right now — no synced contact has a lost deal or a stalled, gone-quiet deal on file.',
            fn (array $ranked) => $this->plainExcludedSummary($ranked)
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function liveSalesCycle(int $limit = 8): array
    {
        return $this->buildAnswer(
            $this->rankLiveSalesCycle($limit),
            'Who is in a live sales cycle — leave them alone?',
            'Nobody is currently in a live, active-stage sales cycle — the pool is safe to send to.',
            fn (array $ranked) => $this->plainLiveSalesCycleSummary($ranked)
        );
    }

    private function buildAnswer(array $ranked, string $question, string $emptyMessage, callable $plainSummary): array
    {
        if (empty($ranked)) {
            return ['ranked' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $client = app(OpenAiClient::class);

        if ($client->isConfigured()) {
            try {
                return [
                    'ranked' => $ranked,
                    'answer' => $this->askOpenAi($client, $question, $ranked),
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

    /**
     * @return array<int, array>
     */
    private function rankExcluded(int $limit): array
    {
        return $this->contactsWithDeals()
            ->map(fn (array $row) => $this->classifyExcluded($row))
            ->filter(fn (?array $row) => $row !== null)
            ->sortByDesc('deal_value')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array>
     */
    private function rankLiveSalesCycle(int $limit): array
    {
        return $this->contactsWithDeals()
            ->map(fn (array $row) => $this->classifyLiveSalesCycle($row))
            ->filter(fn (?array $row) => $row !== null)
            ->sortByDesc('deal_value')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * One row per unique company: identity from crm_contacts, deal facts
     * (value, stage, status, days since last activity) from crm_deals.
     *
     * @return Collection<int, array>
     */
    private function contactsWithDeals(): Collection
    {
        $deals = CrmDeal::all();

        return CrmContact::whereNotNull('company')
            ->whereNotNull('email')
            ->orderByDesc('last_activity_at')
            ->get()
            ->unique('company')
            ->map(function (CrmContact $contact) use ($deals) {
                $matchedDeals = $deals->filter(
                    fn (CrmDeal $d) => str_starts_with((string) $d->name, (string) $contact->company)
                );

                $openDeal = $matchedDeals->first(fn (CrmDeal $d) => $d->status === 'open');
                $lostDeal = $matchedDeals->first(fn (CrmDeal $d) => $d->status === 'lost');
                $wonDeal = $matchedDeals->first(fn (CrmDeal $d) => $d->status === 'won');

                $daysSinceActivity = $contact->last_activity_at
                    ? (int) $contact->last_activity_at->diffInDays(now())
                    : 999;

                return [
                    'name' => trim($contact->first_name . ' ' . $contact->last_name) ?: $contact->company,
                    'company' => $contact->company,
                    'email' => $contact->email,
                    'deal_value' => (float) $matchedDeals->sum('value'),
                    'open_deal' => $openDeal,
                    'lost_deal' => $lostDeal,
                    'won_deal' => $wonDeal,
                    'days_since_activity' => $daysSinceActivity,
                ];
            })
            ->values();
    }

    private function classifyExcluded(array $row): ?array
    {
        $stalled = $row['open_deal'] !== null && $row['days_since_activity'] > self::STALLED_DAYS_THRESHOLD;

        if ($row['lost_deal'] === null && !$stalled) {
            return null;
        }

        $reason = $row['lost_deal'] !== null
            ? 'Deal marked lost — a marketing send here reads tone-deaf'
            : "Open deal stalled and quiet for {$row['days_since_activity']} days — this is Retention's account to work, not Marketing's";

        return [
            'name' => $row['name'],
            'company' => $row['company'],
            'deal_value' => $row['deal_value'],
            'days_since_activity' => $row['days_since_activity'],
            'reason' => $reason,
        ];
    }

    private function classifyLiveSalesCycle(array $row): ?array
    {
        $deal = $row['open_deal'];

        if ($deal === null || !in_array($deal->stage, self::ACTIVE_LATE_STAGES, true)) {
            return null;
        }

        if ($row['days_since_activity'] > self::RECENT_DAYS_THRESHOLD) {
            return null;
        }

        $stageLabel = self::STAGE_LABELS[$deal->stage] ?? $deal->stage;

        return [
            'name' => $row['name'],
            'company' => $row['company'],
            'deal_value' => $row['deal_value'],
            'stage' => $stageLabel,
            'days_since_activity' => $row['days_since_activity'],
            'reason' => "In active '{$stageLabel}' stage, last active {$row['days_since_activity']} days ago — a live sales conversation is already running",
        ];
    }

    private function plainExcludedSummary(array $ranked): string
    {
        $lines = array_map(function (array $row, int $i) {
            return ($i + 1) . '. ' . $row['name'] . ' (' . $row['company'] . ') — ' .
                '$' . number_format($row['deal_value']) . ' — ' . $row['reason'] . '.';
        }, $ranked, array_keys($ranked));

        return "Exclude from every send:\n" . implode("\n", $lines);
    }

    private function plainLiveSalesCycleSummary(array $ranked): string
    {
        $lines = array_map(function (array $row, int $i) {
            return ($i + 1) . '. ' . $row['name'] . ' (' . $row['company'] . ') — ' . $row['reason'] . '.';
        }, $ranked, array_keys($ranked));

        return "In a live sales cycle — leave them alone:\n" . implode("\n", $lines);
    }

    private function askOpenAi(OpenAiClient $client, string $question, array $ranked): string
    {
        $system = 'You are the Marketing copilot inside a B2B analytics platform. '
            . 'Answer the marketer\'s question using ONLY the ranked account data provided as JSON — '
            . 'never invent a name, number, or reason that isn\'t in that data. '
            . 'Be brief and concrete: name who\'s involved and why, in plain English, under 120 words.';

        $prompt = "Question: {$question}\n\nRanked accounts:\n" . json_encode($ranked, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 300]);
    }
}
