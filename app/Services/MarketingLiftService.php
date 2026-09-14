<?php

namespace App\Services;

use App\Models\BrevoDeliveredRecipient;
use App\Models\EmailLog;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use App\Services\Marketing\Concerns\ScoresMqlPool;
use Illuminate\Support\Collection;

/**
 * Answers the Marketing agent's Performance questions that compare the
 * "MQL → Sales" pool against a control group, drawing on all four connected
 * tables:
 *   - crm_contacts + crm_deals             → who is in the MQL → Sales pool
 *     vs everyone else (the "holdout"), via the ScoresMqlPool trait
 *   - email_logs + email_logs_providers    → real send/open/unsubscribe
 *     history, matched back to a contact by email address
 *
 * "What lift did MQL → Sales get vs its holdout?" needs the two sides
 * joined by email: the MQL pool's own open rate against the rest of the
 * CRM pool's open rate. "Which audience has the worst unsubscribe rate?"
 * needs the same join, comparing unsubscribe rate across MQL-ready,
 * at-risk and everyone-else.
 *
 * Right now, on this client's connected data, crm_contacts' email
 * addresses and email_logs / email_logs_providers' email addresses do not
 * overlap at all — they are two separately-synced contact universes (a
 * CRM integration and an email-sending/ESP integration that have never
 * exchanged a send). Rather than fabricate a match, both methods attempt
 * the real join first and report the true, honest state when it comes up
 * empty — the same pattern this app already uses for other not-yet-linked
 * signals (see RetentionSaveFirstService, MarketingAudienceService). Where
 * the CRM join has nothing to show, the unsubscribe question falls back to
 * comparing the real audiences the email data itself can support (Brevo
 * campaign_id groups within email_logs_providers), so the answer is still
 * grounded in real numbers rather than empty.
 *
 * The analysis is pure arithmetic — no AI required. An OpenAI key
 * (config('services.openai')) is only used, when configured, to turn the
 * numbers into a short written answer; without a key this still returns a
 * correct, data-grounded plain-text summary.
 */
class MarketingLiftService
{
    use ScoresMqlPool;

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function mqlLiftVsHoldout(): array
    {
        $mql = $this->mqlReadyPool();
        $holdout = $this->scoredContacts()->reject(
            fn (array $row) => $row['buying_readiness'] >= self::READY_THRESHOLD && !$row['at_risk']
        )->values();

        $mqlEngagement = $this->engagementRateFor($mql);
        $holdoutEngagement = $this->engagementRateFor($holdout);

        if ($mqlEngagement === null || $holdoutEngagement === null) {
            $emptyMessage = "MQL → Sales has {$mql->count()} account(s) and its holdout has {$holdout->count()}, but none of their crm_contacts email "
                . 'addresses appear in the connected email_logs or email_logs_providers history — the CRM sync and the email sync do not '
                . 'currently share any contacts, so a real lift cannot be measured yet. This closes once campaign sends target the same '
                . 'synced contacts the CRM tracks.';

            return ['ranked' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $liftPoints = round($mqlEngagement['rate'] - $holdoutEngagement['rate'], 1);

        $ranked = [
            ['group' => 'MQL → Sales'] + $mqlEngagement,
            ['group' => 'Holdout'] + $holdoutEngagement,
        ];

        $plainSummary = "MQL → Sales opens at {$mqlEngagement['rate']}% ({$mqlEngagement['opened']}/{$mqlEngagement['matched']} matched to email history) vs "
            . "{$holdoutEngagement['rate']}% ({$holdoutEngagement['opened']}/{$holdoutEngagement['matched']}) for its holdout — a {$liftPoints}-point lift.";

        return $this->buildAnswer(
            $ranked,
            'What lift did MQL → Sales get vs its holdout?',
            '',
            fn () => $plainSummary,
            ['lift_points' => $liftPoints]
        );
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function worstUnsubscribeAudience(): array
    {
        $scored = $this->scoredContacts();
        $audiences = [
            'MQL-ready' => $scored->filter(fn (array $r) => $r['buying_readiness'] >= self::READY_THRESHOLD && !$r['at_risk']),
            'At-risk' => $scored->filter(fn (array $r) => $r['at_risk']),
            'Other' => $scored->filter(fn (array $r) => $r['buying_readiness'] < self::READY_THRESHOLD && !$r['at_risk']),
        ];

        $ranked = [];
        foreach ($audiences as $label => $rows) {
            $stat = $this->unsubscribeRateFor($rows);
            if ($stat !== null) {
                $ranked[] = ['audience' => $label] + $stat;
            }
        }

        if (!empty($ranked)) {
            usort($ranked, fn ($a, $b) => $b['unsub_rate'] <=> $a['unsub_rate']);
            $worst = $ranked[0];

            $plainSummary = "\"{$worst['audience']}\" has the worst unsubscribe rate at {$worst['unsub_rate']}% ({$worst['unsubscribed']}/{$worst['matched']} matched to email history).";

            return $this->buildAnswer(
                $ranked,
                'Which audience has the worst unsubscribe rate?',
                '',
                fn () => $plainSummary,
                ['source' => 'crm_segment']
            );
        }

        // No CRM audience currently links to any email history — fall back to
        // the real audiences the email data itself can support (this client's
        // Brevo campaign groups), so the answer stays grounded in real numbers.
        $byCampaign = BrevoDeliveredRecipient::whereNotNull('delivered_at')
            ->get()
            ->groupBy('campaign_id')
            ->map(function (Collection $rows, $campaignId) {
                $delivered = $rows->count();
                $unsub = $rows->filter(fn ($r) => $r->unsubscribed_at !== null)->count();

                return [
                    'audience' => "Campaign {$campaignId}",
                    'delivered' => $delivered,
                    'unsubscribed' => $unsub,
                    'unsub_rate' => $delivered > 0 ? round($unsub / $delivered * 100, 1) : 0.0,
                ];
            })
            ->values();

        if ($byCampaign->isEmpty()) {
            $emptyMessage = 'No CRM audience currently links to any email history (crm_contacts and the connected email data do not share '
                . 'contacts yet), and no delivered email campaign data is on file either, so an unsubscribe comparison cannot be made yet.';

            return ['ranked' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $worstCampaign = $byCampaign->sortByDesc('unsub_rate')->first();
        $plainSummary = "The connected CRM segments (MQL-ready, at-risk, other) don't currently link to any email history, so this compares the "
            . "real email campaigns instead: \"{$worstCampaign['audience']}\" has the worst unsubscribe rate at {$worstCampaign['unsub_rate']}% "
            . "({$worstCampaign['unsubscribed']}/{$worstCampaign['delivered']} delivered) — note the sample is small, so treat this as directional.";

        return $this->buildAnswer(
            $byCampaign->all(),
            'Which audience has the worst unsubscribe rate?',
            '',
            fn () => $plainSummary,
            ['source' => 'email_campaign_fallback']
        );
    }

    /**
     * @return array{rate: float, opened: int, matched: int}|null null when
     *   none of the group's contacts have any matching email history
     */
    private function engagementRateFor(Collection $pool): ?array
    {
        $emails = $pool->pluck('email')->filter()->map(fn ($e) => mb_strtolower($e))->unique();

        if ($emails->isEmpty()) {
            return null;
        }

        $logRows = EmailLog::whereIn('email_address', $emails->all())->get(['email_address', 'opened_at']);
        $providerRows = BrevoDeliveredRecipient::whereIn('email', $emails->all())->get(['email', 'opened_at']);

        $matched = $logRows->pluck('email_address')->merge($providerRows->pluck('email'))
            ->map(fn ($e) => mb_strtolower($e))->unique();

        if ($matched->isEmpty()) {
            return null;
        }

        $openedEmails = $logRows->filter(fn ($r) => $r->opened_at !== null)->pluck('email_address')
            ->merge($providerRows->filter(fn ($r) => $r->opened_at !== null)->pluck('email'))
            ->map(fn ($e) => mb_strtolower($e))->unique();

        return [
            'matched' => $matched->count(),
            'opened' => $openedEmails->count(),
            'rate' => round($openedEmails->count() / $matched->count() * 100, 1),
        ];
    }

    /**
     * @return array{matched: int, unsubscribed: int, unsub_rate: float}|null
     */
    private function unsubscribeRateFor(Collection $pool): ?array
    {
        $emails = $pool->pluck('email')->filter()->map(fn ($e) => mb_strtolower($e))->unique();

        if ($emails->isEmpty()) {
            return null;
        }

        $logRows = EmailLog::whereIn('email_address', $emails->all())->get(['email_address', 'unsubscribed_at']);
        $providerRows = BrevoDeliveredRecipient::whereIn('email', $emails->all())->get(['email', 'unsubscribed_at']);

        $matched = $logRows->pluck('email_address')->merge($providerRows->pluck('email'))
            ->map(fn ($e) => mb_strtolower($e))->unique();

        if ($matched->isEmpty()) {
            return null;
        }

        $unsubEmails = $logRows->filter(fn ($r) => $r->unsubscribed_at !== null)->pluck('email_address')
            ->merge($providerRows->filter(fn ($r) => $r->unsubscribed_at !== null)->pluck('email'))
            ->map(fn ($e) => mb_strtolower($e))->unique();

        return [
            'matched' => $matched->count(),
            'unsubscribed' => $unsubEmails->count(),
            'unsub_rate' => round($unsubEmails->count() / $matched->count() * 100, 1),
        ];
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
                // Fall through to the plain-text summary below — a broken AI
                // call should never take down a page that has real data.
            }
        }

        return ['ranked' => $ranked, 'answer' => $plainSummary($ranked), 'ai_used' => false];
    }

    private function askOpenAi(OpenAiClient $client, string $question, array $ranked, array $context): string
    {
        $system = 'You are the Marketing copilot inside a B2B analytics platform, reporting real campaign '
            . 'performance numbers. Answer using ONLY the JSON data provided — never invent a name, number, or '
            . 'reason that is not in it. Output PLAIN TEXT only, never JSON or markdown — a short, direct paragraph. '
            . 'Be brief and concrete, in plain English, under 120 words.';

        $prompt = "Question: {$question}\n\nContext:\n" . json_encode($context, JSON_PRETTY_PRINT)
            . "\n\nData:\n" . json_encode($ranked, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 300]);
    }
}
