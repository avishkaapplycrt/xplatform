<?php

namespace App\Services;

use App\Models\BrevoDeliveredRecipient;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use Illuminate\Support\Collection;

/**
 * Answers the Customer Retention agent's Risk radar questions —
 * "Who do I save first this week?" and "Who is drifting onto the
 * watchlist?" — by joining three real tables:
 *   - crm_contacts        → identity (name, company, last activity)
 *   - crm_deals           → value at stake, matched to a contact by
 *                           "deal name starts with company name" (the same
 *                           heuristic already used by
 *                           SalesCustomerIntelligenceService, since neither
 *                           table stores a real foreign key between them)
 *   - email_logs_providers → engagement signal: unsubscribed, never opened,
 *                           or gone quiet, matched by exact email address
 *
 * Every contact is scored once (rankAll()). "Save first" is the top slice
 * of that ranking — the highest value/engagement risk right now. "Watchlist"
 * is the next slice down: not urgent yet, but the same risk signals are
 * present at a lower level, which is exactly what "drifting toward risk"
 * means with the data actually available (there is no separate trend/delta
 * signal in these three tables, so rank position is the trend proxy).
 *
 * The ranking itself is pure arithmetic — no AI required. An OpenAI key
 * (config('services.openai')) is only used, when configured, to turn the
 * ranked list into a short written answer; without a key this still returns
 * a correct, data-grounded plain-text summary.
 */
class RetentionSaveFirstService
{
    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function answer(int $limit = 5): array
    {
        return $this->buildAnswer(
            $this->rank($limit),
            'Who do I save first this week?',
            'Nobody stands out to save first this week — no synced contacts show an at-risk signal (no unsubscribes, no long-silent opens, no stalled deals).',
            fn (array $ranked) => $this->plainSaveFirstSummary($ranked)
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function watchlistAnswer(int $limit = 5, int $urgentCount = 5): array
    {
        return $this->buildAnswer(
            $this->rankWatchlist($limit, $urgentCount),
            'Who is drifting onto the watchlist?',
            'Nobody is drifting onto the watchlist right now — every synced contact is either healthy or already in the urgent save-first tier.',
            fn (array $ranked) => $this->plainWatchlistSummary($ranked)
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
                    'ranked'  => $ranked,
                    'answer'  => $this->askOpenAi($client, $question, $ranked),
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
     * Top $limit contacts by risk_score — the urgent, save-first tier.
     */
    public function rank(int $limit = 5): array
    {
        return array_slice($this->rankAll(), 0, $limit);
    }

    /**
     * The $limit contacts ranked just below the top $urgentCount — present
     * the same risk signals, just not as severe yet.
     */
    public function rankWatchlist(int $limit = 5, int $urgentCount = 5): array
    {
        return array_slice($this->rankAll(), $urgentCount, $limit);
    }

    /**
     * Scores every synced contact once, highest risk first. Contacts with a
     * risk_score of 0 (no deal, no negative email signal, freshly active)
     * are dropped — there is nothing to rank them on.
     */
    private function rankAll(): array
    {
        $deals = CrmDeal::all();
        $maxDealValue = max(1.0, (float) $deals->max('value'));
        $emailsByAddress = BrevoDeliveredRecipient::query()
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy(fn ($r) => strtolower((string) $r->email));

        return CrmContact::whereNotNull('company')
            ->whereNotNull('email')
            ->orderByDesc('last_activity_at')
            ->get()
            ->unique('company')
            ->map(function (CrmContact $contact) use ($deals, $emailsByAddress, $maxDealValue) {
                return $this->scoreContact($contact, $deals, $emailsByAddress, $maxDealValue);
            })
            ->filter(fn ($row) => $row['risk_score'] > 0)
            ->sortByDesc('risk_score')
            ->values()
            ->all();
    }

    private function scoreContact(CrmContact $contact, Collection $deals, Collection $emailsByAddress, float $maxDealValue): array
    {
        $matchedDeals = $deals->filter(
            fn (CrmDeal $d) => str_starts_with((string) $d->name, (string) $contact->company)
        );

        $dealValue = (float) $matchedDeals->sum('value');
        $wonDeal = $matchedDeals->first(fn (CrmDeal $d) => $d->status === 'won');
        $openDeal = $matchedDeals->first(fn (CrmDeal $d) => $d->status === 'open');

        $emailRows = $emailsByAddress->get(strtolower((string) $contact->email), collect());
        $unsubscribed = $emailRows->contains(fn ($r) => $r->unsubscribed_at !== null);
        $delivered = $emailRows->filter(fn ($r) => $r->delivered_at !== null);
        $everOpened = $emailRows->contains(fn ($r) => $r->opened_at !== null);
        $lastOpenedAt = $emailRows->max('opened_at');

        $daysSinceActivity = $contact->last_activity_at
            ? (int) $contact->last_activity_at->diffInDays(now())
            : 120;

        // Value at stake, scaled relative to the largest deal on the book —
        // an existing paying (won) customer's deal value matters most; a
        // stalled open deal is worth half as much until it actually closes.
        $relativeValue = ($dealValue / $maxDealValue) * 100;
        $valueScore = $wonDeal ? $relativeValue : ($openDeal ? $relativeValue * 0.5 : 0);

        // Engagement risk: silence is the loudest signal available from a
        // provider's delivery log — unsubscribing is worse than simply
        // never opening, which is worse than a distant-but-real open.
        $engagementRisk = match (true) {
            $unsubscribed => 100,
            $delivered->isNotEmpty() && !$everOpened => 65,
            $lastOpenedAt !== null => min(60, (int) $lastOpenedAt->diffInDays(now())),
            default => 0,
        };

        $recencyRisk = min(100, $daysSinceActivity * 1.2);

        $riskScore = $valueScore * 0.45 + $engagementRisk * 0.35 + $recencyRisk * 0.2;

        return [
            'name'                => trim($contact->first_name . ' ' . $contact->last_name) ?: $contact->company,
            'company'             => $contact->company,
            'email'               => $contact->email,
            'deal_value'          => $dealValue,
            'deal_status'         => $wonDeal?->status ?? $openDeal?->status,
            'unsubscribed'        => $unsubscribed,
            'ever_opened'         => $everOpened,
            'days_since_activity' => $daysSinceActivity,
            'risk_score'          => round($riskScore, 1),
        ];
    }

    private function plainSaveFirstSummary(array $ranked): string
    {
        $lines = array_map(function ($row, $i) {
            return ($i + 1) . '. ' . $row['name'] . ' (' . $row['company'] . ') — ' .
                '$' . number_format($row['deal_value']) . ' at stake, ' . $this->reasonFor($row) . '.';
        }, $ranked, array_keys($ranked));

        return "Save these first this week:\n" . implode("\n", $lines);
    }

    private function plainWatchlistSummary(array $ranked): string
    {
        $lines = array_map(function ($row, $i) {
            return ($i + 1) . '. ' . $row['name'] . ' (' . $row['company'] . ') — ' .
                '$' . number_format($row['deal_value']) . ' at stake, ' . $this->reasonFor($row) . '.';
        }, $ranked, array_keys($ranked));

        return "Drifting toward risk — not urgent yet, worth a check-in this week:\n" . implode("\n", $lines);
    }

    private function reasonFor(array $row): string
    {
        return $row['unsubscribed']
            ? 'unsubscribed from email'
            : (!$row['ever_opened'] ? 'never opens what\'s sent' : $row['days_since_activity'] . ' days since last activity');
    }

    private function askOpenAi(OpenAiClient $client, string $question, array $ranked): string
    {
        $system = 'You are the Customer Retention copilot inside a B2B analytics platform. '
            . 'Answer the retention rep\'s question using ONLY the ranked account data provided as JSON — '
            . 'never invent a name, number, or reason that isn\'t in that data. '
            . 'Be brief and concrete: name who\'s involved and why, in plain English, under 120 words.';

        $prompt = "Question: {$question}\n\nRanked accounts (highest risk first):\n"
            . json_encode($ranked, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 300]);
    }
}
