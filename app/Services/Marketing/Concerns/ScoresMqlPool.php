<?php

namespace App\Services\Marketing\Concerns;

use App\Models\CrmContact;
use App\Models\CrmDeal;
use Illuminate\Support\Collection;

/**
 * Shared scoring for the "MQL → Sales" pool, used by every Marketing agent
 * question that reasons about that pool (Insights and Campaign steps alike)
 * — kept in one place so a contact that qualifies on one question qualifies
 * the same way on all of them.
 *
 * Scores every unique crm_contacts company against its matching crm_deals
 * row (matched by "deal name starts with company name", the same heuristic
 * used throughout this app's marketing/sales/retention services, since
 * neither table stores a real foreign key between them):
 *   - buying_readiness → how close the deal's own stage is to closing (the
 *     same stage → readiness map used to build the dashboard's "Today's
 *     Stack" in routes/web.php)
 *   - trust             → a stand-in for how much rapport has been built,
 *     inferred from how far into the sales conversation the stage sits,
 *     since crm_contacts/crm_deals carry no engagement signal of their own
 *
 * A contact is "in MQL → Sales" once buying_readiness clears READY_THRESHOLD
 * and it isn't flagged at-risk (quiet AT_RISK_DAYS+ days with a deal on
 * file) — the same bar Sales itself uses for its own "call" tier elsewhere
 * in this app, so nothing gets double-worked.
 */
trait ScoresMqlPool
{
    private const READY_THRESHOLD = 65;
    private const TRUST_THRESHOLD = 65;
    private const AT_RISK_DAYS = 90;

    private const STAGE_READINESS = [
        'closedwon' => 92,
        'decisionmakerboughtin' => 78,
        'presentationscheduled' => 68,
        'qualifiedtobuy' => 58,
        'appointmentscheduled' => 50,
    ];
    private const NO_DEAL_READINESS = 35;

    private const STAGE_TRUST = [
        'closedwon' => 88,
        'decisionmakerboughtin' => 75,
        'presentationscheduled' => 60,
        'qualifiedtobuy' => 45,
        'appointmentscheduled' => 35,
    ];
    private const NO_DEAL_TRUST = 50;

    private const STAGE_LABELS = [
        'closedwon' => 'Closed won',
        'decisionmakerboughtin' => 'Decision-maker bought in',
        'presentationscheduled' => 'Presentation scheduled',
        'qualifiedtobuy' => 'Qualified to buy',
        'appointmentscheduled' => 'Appointment scheduled',
    ];

    /**
     * The MQL → Sales pool: readiness clears the bar, and the contact isn't
     * flagged at-risk.
     */
    private function mqlReadyPool(): Collection
    {
        return $this->scoredContacts()
            ->filter(fn (array $row) => $row['buying_readiness'] >= self::READY_THRESHOLD && !$row['at_risk'])
            ->values();
    }

    /**
     * The slice of the MQL pool that qualified recently — crm_contacts /
     * crm_deals carry no "date this deal reached this stage" history, so
     * recent activity (last_activity_at within $days) is used as the proxy
     * for "just became an MQL", the same assumption changed_last_7_days
     * already makes.
     */
    private function recentlyQualifiedPool(int $days = 7): Collection
    {
        return $this->scoredContacts()
            ->filter(fn (array $row) => $row['days_since_activity'] <= $days && $row['buying_readiness'] >= self::READY_THRESHOLD && !$row['at_risk'])
            ->sortBy('days_since_activity')
            ->values();
    }

    /**
     * One row per unique company, scored purely from crm_contacts +
     * crm_deals — see trait docblock for how buying_readiness and trust
     * are derived.
     *
     * @return Collection<int, array>
     */
    private function scoredContacts(): Collection
    {
        $deals = CrmDeal::all();

        return CrmContact::whereNotNull('company')
            ->whereNotNull('email')
            ->orderByDesc('last_activity_at')
            ->get()
            ->unique('company')
            ->map(function (CrmContact $contact) use ($deals) {
                $deal = $deals->first(
                    fn (CrmDeal $d) => str_starts_with((string) $d->name, (string) $contact->company)
                );

                $daysSinceActivity = $contact->last_activity_at
                    ? (int) $contact->last_activity_at->diffInDays(now())
                    : 999;

                $stage = $deal->stage ?? null;
                $buyingReadiness = $stage !== null
                    ? (self::STAGE_READINESS[$stage] ?? self::NO_DEAL_READINESS)
                    : self::NO_DEAL_READINESS;
                $trust = $stage !== null
                    ? (self::STAGE_TRUST[$stage] ?? self::NO_DEAL_TRUST)
                    : self::NO_DEAL_TRUST;

                $atRisk = $daysSinceActivity > self::AT_RISK_DAYS && $deal !== null;

                return [
                    'name' => trim($contact->first_name . ' ' . $contact->last_name) ?: $contact->company,
                    'company' => $contact->company,
                    'email' => $contact->email,
                    'deal_value' => $deal ? (float) $deal->value : 0.0,
                    'stage' => $stage,
                    'stage_label' => $stage !== null ? (self::STAGE_LABELS[$stage] ?? $stage) : 'No deal on file',
                    'buying_readiness' => $buyingReadiness,
                    'trust' => $trust,
                    'days_since_activity' => $daysSinceActivity,
                    'at_risk' => $atRisk,
                ];
            })
            ->values();
    }
}
