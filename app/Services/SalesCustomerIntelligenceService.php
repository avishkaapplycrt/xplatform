<?php

namespace App\Services;

use App\Models\BrevoDeliveredRecipient;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\EmailConnection;
use App\Models\SalesCustomerIntelligence;
use Illuminate\Support\Collection;

/**
 * Builds sales_customer_intelligence — the single table the Sales agent reads
 * from instead of joining crm_contacts + crm_deals + email_logs_brevo live on
 * every request. One row per CRM contact, combining:
 *   - crm_contacts   → identity (name, company, HubSpot ids)
 *   - crm_deals      → deal_count / total_deal_value / current_deal_stage etc.,
 *                      matched to a contact by "deal name starts with company
 *                      name" — the same heuristic already used elsewhere in
 *                      this app, since neither table stores a real foreign key
 *                      between a deal and a contact.
 *   - email_logs_brevo → emails_delivered/opened/clicked, matched by exact
 *                      email address. Today there is no real customer who
 *                      exists in both the connected HubSpot and Brevo test
 *                      data, so these columns legitimately compute to zero
 *                      for every row — this will start populating itself the
 *                      moment a contact's email exists in both sources, no
 *                      code change required.
 *
 * Call rebuild() any time after a CRM or Brevo sync completes to refresh it.
 */
class SalesCustomerIntelligenceService
{
    private const STAGE_READINESS = [
        'closedwon' => 92,
        'decisionmakerboughtin' => 78,
        'presentationscheduled' => 68,
        'qualifiedtobuy' => 58,
        'appointmentscheduled' => 50,
    ];

    /**
     * Rebuilds every row. Returns the number of contacts processed.
     */
    public function rebuild(): int
    {
        $deals = CrmDeal::all();
        $brevoByEmail = BrevoDeliveredRecipient::all()
            ->groupBy(fn ($r) => strtolower($r->email));
        $fallbackClientId = EmailConnection::where('platform', 'brevo')->value('client_id') ?? 1;

        $processed = 0;

        CrmContact::whereNotNull('email')->chunk(200, function (Collection $contacts) use ($deals, $brevoByEmail, $fallbackClientId, &$processed) {
            foreach ($contacts as $contact) {
                $this->upsertContact($contact, $deals, $brevoByEmail, $fallbackClientId);
                $processed++;
            }
        });

        return $processed;
    }

    /**
     * Rebuild just the rows for one email — cheap enough to call right after
     * a single contact's data changes, without rebuilding the whole table.
     */
    public function rebuildForEmail(string $email): void
    {
        $contact = CrmContact::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
        if (! $contact) {
            return;
        }

        $deals = CrmDeal::all();
        $brevoByEmail = BrevoDeliveredRecipient::all()->groupBy(fn ($r) => strtolower($r->email));
        $fallbackClientId = EmailConnection::where('platform', 'brevo')->value('client_id') ?? 1;

        $this->upsertContact($contact, $deals, $brevoByEmail, $fallbackClientId);
    }

    private function upsertContact(CrmContact $contact, Collection $deals, Collection $brevoByEmail, int $fallbackClientId): void
    {
        $matchedDeals = $contact->company
            ? $deals->filter(fn (CrmDeal $d) => str_starts_with((string) $d->name, (string) $contact->company))
            : collect();

        $wonDeal = $matchedDeals->first(fn (CrmDeal $d) => $d->status === 'won');
        $bestOpenDeal = $matchedDeals
            ->filter(fn (CrmDeal $d) => $d->status === 'open')
            ->sortByDesc(fn (CrmDeal $d) => self::STAGE_READINESS[$d->stage] ?? 0)
            ->first();

        $dealCount = $matchedDeals->count();
        $totalDealValue = (float) $matchedDeals->sum('value');
        $highestDealValue = (float) $matchedDeals->max('value');
        $currentDealStage = $wonDeal?->stage ?? $bestOpenDeal?->stage;
        $nearestCloseDate = $matchedDeals
            ->filter(fn (CrmDeal $d) => $d->status === 'open' && $d->close_date)
            ->sortBy('close_date')
            ->first()?->close_date;

        $emailRows = $brevoByEmail->get(strtolower($contact->email), collect());
        $emailsDelivered = $emailRows->count();
        $emailsOpened = $emailRows->filter(fn ($r) => $r->opened_at !== null)->count();
        $emailsClicked = $emailRows->filter(fn ($r) => (bool) $r->clicked)->count();
        $lastDeliveredAt = $emailRows->max('delivered_at');
        $lastOpenedAt = $emailRows->max('opened_at');
        // No distinct clicked_at column exists on email_logs_brevo — the
        // opened_at of the most recent clicked row is the closest real
        // timestamp available for "last clicked".
        $lastClickedAt = $emailRows->filter(fn ($r) => (bool) $r->clicked)->max('opened_at');
        $unsubscribedAt = $emailRows->max('unsubscribed_at');

        $crmScore = match (true) {
            $wonDeal !== null => 95.0,
            $bestOpenDeal !== null => (float) (self::STAGE_READINESS[$bestOpenDeal->stage] ?? 40),
            $dealCount > 0 => 40.0,
            default => 10.0,
        };

        if ($emailsDelivered === 0) {
            $emailEngagementScore = 0.0;
        } elseif ($unsubscribedAt !== null) {
            $emailEngagementScore = 5.0;
        } else {
            $emailEngagementScore = round(
                ($emailsOpened / $emailsDelivered) * 50 + ($emailsClicked / $emailsDelivered) * 50,
                2
            );
        }

        $buyingIntentScore = round($crmScore * 0.6 + $emailEngagementScore * 0.4, 2);

        $valueBoost = $totalDealValue > 0 ? min(15.0, round(log10($totalDealValue + 1) * 5, 2)) : 0.0;
        $salesPriorityScore = min(100.0, round($buyingIntentScore + $valueBoost, 2));

        $priorityLevel = match (true) {
            $salesPriorityScore >= 70 => 'high',
            $salesPriorityScore >= 40 => 'medium',
            default => 'low',
        };

        $recommendedAction = $this->recommendAction($priorityLevel, $bestOpenDeal, $wonDeal, $unsubscribedAt, $emailsOpened);

        $brevoClientId = $emailRows->first()?->client_id;

        SalesCustomerIntelligence::updateOrCreate(
            ['email' => $contact->email],
            [
                'client_id' => $brevoClientId ?? $fallbackClientId,
                'connection_id' => $contact->connection_id,
                'crm_contact_id' => $contact->id,
                'external_contact_id' => $contact->external_id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'company' => $contact->company,
                'last_activity_at' => $contact->last_activity_at,
                'deal_count' => $dealCount,
                'total_deal_value' => $totalDealValue,
                'highest_deal_value' => $highestDealValue,
                'current_deal_stage' => $currentDealStage,
                'nearest_close_date' => $nearestCloseDate,
                'emails_delivered' => $emailsDelivered,
                'emails_opened' => $emailsOpened,
                'emails_clicked' => $emailsClicked,
                'last_email_delivered_at' => $lastDeliveredAt,
                'last_email_opened_at' => $lastOpenedAt,
                'last_email_clicked_at' => $lastClickedAt,
                'unsubscribed_at' => $unsubscribedAt,
                'crm_score' => $crmScore,
                'email_engagement_score' => $emailEngagementScore,
                'sales_priority_score' => $salesPriorityScore,
                'buying_intent_score' => $buyingIntentScore,
                'priority_level' => $priorityLevel,
                'recommended_action' => $recommendedAction,
                'intelligence_updated_at' => now(),
            ]
        );
    }

    private function recommendAction(string $priorityLevel, ?CrmDeal $bestOpenDeal, ?CrmDeal $wonDeal, $unsubscribedAt, int $emailsOpened): string
    {
        if ($priorityLevel === 'high' && $bestOpenDeal !== null) {
            return 'Prioritize — active deal in progress with strong signals.';
        }

        if ($priorityLevel === 'high' && $wonDeal !== null) {
            return 'Upsell opportunity — existing customer with strong signals.';
        }

        if ($priorityLevel === 'high') {
            return 'Reach out — strong signals, no active deal yet.';
        }

        if ($unsubscribedAt !== null) {
            return 'Low priority — unsubscribed from email, re-engage via another channel.';
        }

        if ($priorityLevel === 'medium') {
            return 'Nurture — monitor engagement and follow up.';
        }

        if ($wonDeal !== null) {
            return 'Retain — existing customer, no urgent sales action needed.';
        }

        return 'Low priority — limited signal, deprioritize for now.';
    }
}
