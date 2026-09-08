<?php

namespace App\Services;

use App\Models\BrevoDeliveredRecipient;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use Illuminate\Support\Collection;

/**
 * Builds the "real accounts" dataset that drives the Sales/Marketing/
 * Retention agent panels on business-helpers: one row per real CRM contact,
 * enriched with its matched crm_deals row (matched by "deal name starts with
 * company name", since neither table stores a real foreign key between
 * them) and Brevo delivery signal (matched by exact email), then scored into
 * the intent/engagement/buying_readiness/churn/loyalty/trust/frustration
 * numbers every agent's classifier reads.
 *
 * Extracted out of the business-helpers route so the same scoring logic can
 * also be handed to the Sales agent's AI chat (SalesChatService) as grounding
 * data, without duplicating it.
 */
class RealAccountsService
{
    private const STAGE_READINESS = [
        'closedwon' => 92,
        'decisionmakerboughtin' => 78,
        'presentationscheduled' => 68,
        'qualifiedtobuy' => 58,
        'appointmentscheduled' => 50,
    ];

    public function build(int $limit = 14): Collection
    {
        $deals = CrmDeal::all();
        $brevoByEmail = BrevoDeliveredRecipient::query()
            ->orderByDesc('updated_at')
            ->get()
            ->keyBy(fn ($r) => strtolower($r->email));

        return CrmContact::whereNotNull('company')
            ->orderByDesc('last_activity_at')
            ->get()
            ->unique('company')
            ->take($limit)
            ->map(function ($contact) use ($deals, $brevoByEmail) {
                $deal = $deals->first(fn ($d) => str_starts_with((string) $d->name, (string) $contact->company));

                $daysAgo = $contact->last_activity_at
                    ? $contact->last_activity_at->diffInDays(now())
                    : 120;

                $engagement = max(8, min(95, (int) round(100 - $daysAgo * 2)));
                $buyingReadiness = $deal ? (self::STAGE_READINESS[$deal->stage] ?? 45) : 35;
                $intent = (int) round($buyingReadiness * 0.55 + $engagement * 0.45);
                $won = $deal && $deal->status === 'won';

                if ($daysAgo > 90 && $deal) {
                    $seg = 'at_risk';
                } elseif ($won && $daysAgo <= 45) {
                    $seg = 'champion';
                } elseif ($won) {
                    $seg = 'loyal';
                } elseif ($daysAgo > 60) {
                    $seg = 'dormant';
                } else {
                    $seg = 'new';
                }

                $churn = match (true) {
                    $seg === 'at_risk' => min(95, 70 + max(0, $daysAgo - 90)),
                    $seg === 'dormant' => 55,
                    default => max(10, min(50, (int) round((100 - $engagement) * 0.5))),
                };
                $loyalty = $won ? 85 : ($engagement > 60 ? 60 : 40);

                $brevo = $brevoByEmail->get(strtolower((string) $contact->email));
                $trust = $brevo
                    ? ($brevo->unsubscribed_at ? 25 : ($brevo->opened_at ? 80 : 60))
                    : 55;
                $frustration = $brevo && $brevo->unsubscribed_at ? 72 : 15;

                $name = trim($contact->first_name . ' ' . $contact->last_name);

                return [
                    'name' => $name !== '' ? $name : $contact->company,
                    'company' => $contact->company,
                    'email' => $contact->email,
                    'seg' => $seg,
                    'mrr' => $deal ? (int) round($deal->value) : 800,
                    'scores' => [
                        'intent' => $intent,
                        'engagement' => $engagement,
                        'buying_readiness' => $buyingReadiness,
                        'churn' => $churn,
                        'loyalty' => $loyalty,
                        'trust' => $trust,
                        'frustration' => $frustration,
                    ],
                ];
            })
            ->values();
    }
}
