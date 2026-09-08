<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Answers the Customer Retention agent's "Root cause" questions:
 *
 *   - Why is [name] leaving?
 *   - Is [name] a price problem or a product problem?
 *   - What is the top churn driver across the book?
 *
 * This is deliberately separate from RetentionSaveFirstService: the Risk
 * radar questions rank *who* is at risk (and lean on email engagement
 * signal), whereas these questions explain *why*, and do it purely from the
 * two CRM tables the client actually syncs:
 *
 *   - crm_contacts → identity + last_activity_at (recency of any CRM touch)
 *   - crm_deals    → pipeline: value, stage, won/open/lost, close date, and
 *                    (from raw_data) when HubSpot last modified the deal
 *
 * A deal is tied to a contact with the same "deal name starts with the
 * contact's company name" heuristic used elsewhere in this codebase, since
 * the two tables carry no real foreign key.
 *
 * The reasoning is plain arithmetic. The OpenAI key
 * (config('services.openai')) is used only to phrase the final answer; with
 * no key configured every method still returns a correct, data-grounded
 * plain-text summary.
 */
class RetentionRootCauseService
{
    /** Late funnel stages — the prospect is at the table, weighing the number. */
    private const LATE_STAGES = ['decisionmakerboughtin', 'presentationscheduled', 'contractsent'];

    /** Early funnel stages — still deciding whether the product is even a fit. */
    private const EARLY_STAGES = ['appointmentscheduled', 'qualifiedtobuy'];

    // ─────────────────────────────────────────────────────────────────────────
    //  Q1 — Why is [name] leaving?
    // ─────────────────────────────────────────────────────────────────────────

    public function whyLeaving(?string $name): array
    {
        $question = 'Why is ' . ($name ?: 'this customer') . ' leaving?';
        $contact = $name ? $this->resolveContact($name) : null;

        if (!$contact) {
            return $this->noMatch($question, $name);
        }

        $f = $this->features($contact, $this->allDeals());
        $signals = $this->leavingSignals($f);
        $churn = $this->churnScore($f);

        $plain = $signals
            ? $f['display_name'] . ' — ' . implode(' ', $signals)
            : $f['display_name'] . ' shows no churn signal in the CRM right now: recent activity, no lost or stalled deals.';

        return $this->withAnswer(
            $question,
            [
                'matched'      => true,
                'name'         => $f['display_name'],
                'company'      => $contact->company,
                'churn_score'  => $churn,
                'signals'      => $signals,
                'facts'        => $this->facts($f),
                'accounts'     => [$this->accountRow($f, $churn)],
            ],
            $plain,
            fn (OpenAiClient $c) => $this->askOpenAi($c, $question, [
                'name'        => $f['display_name'],
                'company'     => $contact->company,
                'churn_score' => $churn,
                'signals'     => $signals,
                'facts'       => $this->facts($f),
            ], 'Explain in 2-3 sentences why this customer looks like a churn risk, using ONLY the entries in "signals". '
                . 'Do not turn a fact into a risk unless it appears in "signals" — recent activity is normal, not a warning. '
                . 'If "signals" is empty, say plainly that the CRM shows no churn risk for this customer.')
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q2 — Is [name] a price problem or a product problem?
    // ─────────────────────────────────────────────────────────────────────────

    public function priceOrProduct(?string $name): array
    {
        $question = 'Is ' . ($name ?: 'this customer') . ' having a price problem or a product problem?';
        $contact = $name ? $this->resolveContact($name) : null;

        if (!$contact) {
            return $this->noMatch($question, $name);
        }

        $f = $this->features($contact, $this->allDeals());
        [$verdict, $confidence, $reasons] = $this->priceOrProductVerdict($f);

        $label = match ($verdict) {
            'price'   => 'a price problem',
            'product' => 'a product problem',
            default   => 'unclear from the CRM data',
        };

        $plain = $f['display_name'] . ' looks like ' . $label . ' — ' . implode(' ', $reasons);

        return $this->withAnswer(
            $question,
            [
                'matched'    => true,
                'name'       => $f['display_name'],
                'company'    => $contact->company,
                'verdict'    => $verdict,
                'confidence' => $confidence,
                'signals'    => $reasons,
                'facts'      => $this->facts($f),
                'accounts'   => [$this->accountRow($f, $this->churnScore($f))],
            ],
            $plain,
            fn (OpenAiClient $c) => $this->askOpenAi($c, $question, [
                'name'       => $f['display_name'],
                'company'    => $contact->company,
                'verdict'    => $verdict,
                'confidence' => $confidence,
                'reasons'    => $reasons,
                'facts'      => $this->facts($f),
            ], "State the verdict ({$verdict}) in the first sentence, then justify it from the reasons, then give one line of what to do about it. Under 90 words.")
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q3 — What is the top churn driver across the book?
    // ─────────────────────────────────────────────────────────────────────────

    public function topChurnDriver(): array
    {
        $question = 'What is the top churn driver across the book?';
        $deals = $this->allDeals();

        $atRisk = $this->contacts()
            ->map(fn (CrmContact $c) => $this->features($c, $deals))
            ->map(fn (array $f) => $f + ['churn_score' => $this->churnScore($f), 'driver' => $this->dominantDriver($f)])
            ->filter(fn (array $f) => $f['churn_score'] >= 20 && $f['driver'] !== null)
            ->values();

        if ($atRisk->isEmpty()) {
            return [
                'question' => $question,
                'matched'  => true,
                'answer'   => 'No dominant churn driver across the book right now — no synced customer clears the risk threshold (no lost deals, no stalled pipeline, no long silences).',
                'ai_used'  => false,
                'driver'   => null,
                'breakdown' => [],
                'accounts' => [],
            ];
        }

        $labels = [
            'lost_deals'       => 'lost deals',
            'stalled_pipeline' => 'stalled pipeline',
            'gone_silent'      => 'customers gone silent',
            'no_pipeline'      => 'contacts with no active pipeline',
        ];

        $breakdown = $atRisk->groupBy('driver')
            ->map(fn (Collection $g, string $driver) => [
                'driver'   => $driver,
                'label'    => $labels[$driver] ?? $driver,
                'accounts' => $g->count(),
                'value'    => round((float) $g->sum('deal_value'), 2),
            ])
            ->sortByDesc(fn ($b) => [$b['accounts'], $b['value']])
            ->values();

        $top = $breakdown->first();
        $topAccounts = $atRisk->where('driver', $top['driver'])
            ->sortByDesc('deal_value')
            ->take(8)
            ->map(fn (array $f) => $this->accountRow($f, $f['churn_score']))
            ->values()
            ->all();

        $plain = 'The top churn driver across the book is ' . $top['label'] . ' — '
            . $top['accounts'] . ' ' . Str::plural('account', $top['accounts'])
            . ' ($' . number_format($top['value']) . ' at stake). '
            . ($breakdown->count() > 1
                ? 'Next: ' . $breakdown->slice(1)->map(fn ($b) => $b['label'] . ' (' . $b['accounts'] . ')')->implode(', ') . '.'
                : '');

        return $this->withAnswer(
            $question,
            [
                'matched'   => true,
                'driver'    => $top['driver'],
                'breakdown' => $breakdown->all(),
                'accounts'  => $topAccounts,
            ],
            $plain,
            fn (OpenAiClient $c) => $this->askOpenAi($c, $question, [
                'breakdown'   => $breakdown->all(),
                'top_accounts' => $topAccounts,
            ], 'Name the single biggest churn driver and the number of accounts / dollars behind it, then list the runners-up in one line. Under 90 words.')
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Contact resolution
    // ─────────────────────────────────────────────────────────────────────────

    public function resolveContact(string $name): ?CrmContact
    {
        $q = Str::lower(trim($name));
        if ($q === '') {
            return null;
        }

        $contacts = $this->contacts();

        $full = fn (CrmContact $c) => Str::lower(trim($c->first_name . ' ' . $c->last_name));

        return $contacts->first(fn ($c) => $full($c) === $q)
            ?? $contacts->first(fn ($c) => $full($c) !== '' && Str::contains($full($c), $q))
            ?? $contacts->first(fn ($c) => $c->company && Str::contains(Str::lower($c->company), $q))
            ?? $contacts->first(fn ($c) => ($c->first_name && Str::contains(Str::lower($c->first_name), $q))
                || ($c->last_name && Str::contains(Str::lower($c->last_name), $q)));
    }

    /** Display names for the name-input datalist and the no-match hint. */
    public function candidateNames(int $limit = 40): array
    {
        return $this->contacts()
            ->map(fn (CrmContact $c) => trim($c->first_name . ' ' . $c->last_name) ?: (string) $c->company)
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->take($limit)
            ->values()
            ->all();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Feature extraction
    // ─────────────────────────────────────────────────────────────────────────

    private function contacts(): Collection
    {
        return CrmContact::query()
            ->where(fn ($q) => $q->whereNotNull('company')->orWhereNotNull('first_name'))
            ->orderByDesc('last_activity_at')
            ->get()
            ->unique(fn (CrmContact $c) => Str::lower($c->company ?: ($c->first_name . ' ' . $c->last_name)))
            ->values();
    }

    private function allDeals(): Collection
    {
        return CrmDeal::all();
    }

    /**
     * Everything the three questions need about one contact, derived only
     * from crm_contacts + crm_deals.
     */
    private function features(CrmContact $contact, Collection $deals): array
    {
        $company = trim((string) $contact->company);
        $matched = $company === ''
            ? collect()
            : $deals->filter(fn (CrmDeal $d) => Str::startsWith((string) $d->name, $company))->values();

        $won  = $matched->filter(fn (CrmDeal $d) => $this->isWon($d))->values();
        $lost = $matched->filter(fn (CrmDeal $d) => $this->isLost($d))->values();
        $open = $matched->filter(fn (CrmDeal $d) => !$this->isWon($d) && !$this->isLost($d))->values();

        $daysSince = $contact->last_activity_at
            ? (int) $contact->last_activity_at->diffInDays(now())
            : null;

        $now = now();
        $overdue = $open->filter(fn (CrmDeal $d) => $d->close_date && $d->close_date->lt($now))->values();
        $staleOpen = $open->filter(function (CrmDeal $d) {
            $modified = $this->lastModified($d);
            return $modified && $modified->diffInDays(now()) > 30;
        })->values();
        // Early-stage deal whose close date is within three weeks — the math
        // says it will not get there, so it is about to slip.
        $slipping = $open->filter(fn (CrmDeal $d) => $d->close_date
            && $d->close_date->gte($now)
            && $d->close_date->lte($now->copy()->addDays(21))
            && in_array($d->stage, self::EARLY_STAGES, true))->values();

        $displayName = trim($contact->first_name . ' ' . $contact->last_name);

        return [
            'contact'       => $contact,
            'display_name'  => $displayName !== '' ? $displayName : ($company !== '' ? $company : (string) $contact->email),
            'company'       => $company,
            'days_since'    => $daysSince,
            'last_activity' => $contact->last_activity_at,
            'deal_count'    => $matched->count(),
            'deal_value'    => round((float) $matched->sum('value'), 2),
            'open_value'    => round((float) $open->sum('value'), 2),
            'won_value'     => round((float) $won->sum('value'), 2),
            'won'           => $won,
            'lost'          => $lost,
            'open'          => $open,
            'overdue'       => $overdue,
            'stale_open'    => $staleOpen,
            'slipping'      => $slipping,
            'at_late_stage' => $open->contains(fn (CrmDeal $d) => in_array($d->stage, self::LATE_STAGES, true)),
            'at_early_stage' => $open->contains(fn (CrmDeal $d) => in_array($d->stage, self::EARLY_STAGES, true)),
            'stages'        => $matched->pluck('stage')->filter()->unique()->values()->all(),
        ];
    }

    private function isWon(CrmDeal $d): bool
    {
        return $d->status === 'won'
            || $d->stage === 'closedwon'
            || data_get($d->raw_data, 'properties.hs_is_closed_won') === 'true';
    }

    private function isLost(CrmDeal $d): bool
    {
        return $d->status === 'lost'
            || Str::contains((string) $d->stage, 'lost')
            || data_get($d->raw_data, 'properties.hs_is_closed_lost') === 'true';
    }

    private function lastModified(CrmDeal $d): ?Carbon
    {
        $raw = data_get($d->raw_data, 'properties.hs_lastmodifieddate')
            ?? data_get($d->raw_data, 'updatedAt')
            ?? $d->updated_at;

        try {
            return $raw ? Carbon::parse($raw) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Scoring & narrative
    // ─────────────────────────────────────────────────────────────────────────

    private function churnScore(array $f): int
    {
        $score = 0;

        $score += match (true) {
            $f['days_since'] === null => 15,
            $f['days_since'] > 90     => 45,
            $f['days_since'] > 45     => 30,
            $f['days_since'] > 21     => 15,
            default                   => 0,
        };

        if ($f['lost']->isNotEmpty()) {
            $score += 40;
        }
        if ($f['overdue']->isNotEmpty()) {
            $score += 25;
        }
        if ($f['slipping']->isNotEmpty()) {
            $score += 22;
        }
        if ($f['stale_open']->isNotEmpty()) {
            $score += 15;
        }
        if ($f['deal_count'] === 0) {
            $score += 8;
        }
        if ($f['won']->isNotEmpty() && $f['days_since'] !== null && $f['days_since'] <= 30) {
            $score -= 20; // active paying customer — healthy
        }

        return max(0, min(100, $score));
    }

    /** Human-readable churn signals for "why is X leaving". */
    private function leavingSignals(array $f): array
    {
        $out = [];

        if ($f['days_since'] === null) {
            $out[] = 'There is no recorded CRM activity for this contact at all.';
        } elseif ($f['days_since'] > 90) {
            $out[] = "No CRM activity for {$f['days_since']} days (last touch " . $f['last_activity']->toFormattedDateString() . ').';
        } elseif ($f['days_since'] > 45) {
            $out[] = "Going quiet — {$f['days_since']} days since the last CRM activity.";
        }

        if ($f['lost']->isNotEmpty()) {
            $d = $f['lost']->first();
            $out[] = 'A $' . number_format((float) $d->value) . ' deal was marked lost'
                . ($d->close_date ? ' (' . $d->close_date->toFormattedDateString() . ')' : '') . '.';
        }

        if ($f['overdue']->isNotEmpty()) {
            $d = $f['overdue']->first();
            $out[] = 'Their $' . number_format((float) $d->value) . ' deal has slipped past its expected close date'
                . ($d->close_date ? ' (' . $d->close_date->toFormattedDateString() . ')' : '')
                . " and is still sitting in {$d->stage}.";
        } elseif ($f['slipping']->isNotEmpty()) {
            $d = $f['slipping']->first();
            $out[] = 'A $' . number_format((float) $d->value) . ' deal is booked to close ' . $d->close_date->toFormattedDateString()
                . " but is still only at {$d->stage} — on the current pace it will slip.";
        } elseif ($f['stale_open']->isNotEmpty()) {
            $d = $f['stale_open']->first();
            $out[] = 'An open $' . number_format((float) $d->value) . " deal in {$d->stage} has had no movement in over a month.";
        }

        if ($f['won']->isNotEmpty() && $f['days_since'] !== null && $f['days_since'] > 45) {
            $out[] = 'They are a won customer ($' . number_format($f['won_value']) . ') but have gone quiet since — a sign they may not be seeing the value.';
        }

        if ($f['deal_count'] === 0) {
            $out[] = 'No deal is on record for this account — it never really entered the pipeline.';
        }

        return $out;
    }

    /** @return array{0:string,1:string,2:array<int,string>} verdict, confidence, reasons */
    private function priceOrProductVerdict(array $f): array
    {
        $price = 0;
        $product = 0;
        $reasons = [];

        if ($f['at_late_stage'] && ($f['overdue']->isNotEmpty() || $f['stale_open']->isNotEmpty())) {
            $price += 3;
            $reasons[] = 'A deal reached a late stage (decision-maker bought in / presentation) and then stalled — they see the value but have not agreed the number.';
        }
        if ($f['lost']->isNotEmpty() && array_intersect($f['stages'], self::LATE_STAGES)) {
            $price += 2;
            $reasons[] = 'The lost deal had already progressed deep into the funnel before it fell over.';
        }
        if ($f['open_value'] > 0 && $f['open_value'] >= $this->bookOpenValueP75()) {
            $price += 1;
            $reasons[] = 'The open deal value is large relative to the rest of the book, so the number itself carries weight.';
        }

        if ($f['won']->isNotEmpty() && $f['days_since'] !== null && $f['days_since'] > 60) {
            $product += 3;
            $reasons[] = 'They already bought but have been silent for ' . $f['days_since'] . ' days — a usage / value problem, not a pricing one.';
        }
        if ($f['at_early_stage'] && ($f['stale_open']->isNotEmpty() || $f['slipping']->isNotEmpty() || ($f['days_since'] ?? 0) > 45)) {
            $product += 2;
            $reasons[] = 'The deal is stuck early in the funnel — they are still not convinced the product fits.';
        }
        if ($f['lost']->isNotEmpty() && array_intersect($f['stages'], self::EARLY_STAGES) && !array_intersect($f['stages'], self::LATE_STAGES)) {
            $product += 2;
            $reasons[] = 'The deal was lost early, before pricing would normally come up.';
        }
        if ($f['deal_count'] === 0 && ($f['days_since'] === null || $f['days_since'] > 45)) {
            $product += 1;
            $reasons[] = 'With no pipeline and little activity, the likelier gap is fit / value rather than price.';
        }

        if ($price === 0 && $product === 0) {
            return ['unclear', 'low', ['The CRM data does not show a clear pricing or product signal for this account yet.']];
        }

        $verdict = $price >= $product ? 'price' : 'product';
        $spread = abs($price - $product);
        $confidence = $spread >= 3 ? 'high' : ($spread >= 1 ? 'medium' : 'low');

        return [$verdict, $confidence, $reasons];
    }

    private function dominantDriver(array $f): ?string
    {
        return match (true) {
            $f['lost']->isNotEmpty()                              => 'lost_deals',
            $f['overdue']->isNotEmpty() || $f['slipping']->isNotEmpty() || $f['stale_open']->isNotEmpty() => 'stalled_pipeline',
            $f['days_since'] !== null && $f['days_since'] > 45     => 'gone_silent',
            $f['deal_count'] === 0                                => 'no_pipeline',
            default                                              => null,
        };
    }

    private function bookOpenValueP75(): float
    {
        return once(function () {
            $deals = $this->allDeals();
            $open = $deals
                ->filter(fn (CrmDeal $d) => !$this->isWon($d) && !$this->isLost($d))
                ->pluck('value')
                ->map(fn ($v) => (float) $v)
                ->sort()
                ->values();

            if ($open->isEmpty()) {
                return 0.0;
            }

            $idx = (int) floor(0.75 * ($open->count() - 1));

            return (float) $open[$idx];
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Shaping the response
    // ─────────────────────────────────────────────────────────────────────────

    private function facts(array $f): array
    {
        return [
            'days_since_activity' => $f['days_since'],
            'last_activity'       => $f['last_activity']?->toDateString(),
            'deal_count'          => $f['deal_count'],
            'deal_value'          => $f['deal_value'],
            'won_deals'           => $f['won']->count(),
            'open_deals'          => $f['open']->count(),
            'lost_deals'          => $f['lost']->count(),
            'overdue_deals'       => $f['overdue']->count(),
            'slipping_deals'      => $f['slipping']->count(),
            'stages'              => $f['stages'],
        ];
    }

    private function accountRow(array $f, int $churn): array
    {
        $status = $f['lost']->isNotEmpty() ? 'lost'
            : ($f['won']->isNotEmpty() ? 'won'
            : ($f['open']->isNotEmpty() ? 'open' : null));

        return [
            'name'                => $f['display_name'],
            'company'             => $f['company'] ?: '—',
            'deal_value'          => $f['deal_value'],
            'deal_status'         => $status,
            'stage'               => $f['stages'][0] ?? '—',
            'days_since_activity' => $f['days_since'] ?? '—',
            'churn_score'         => $churn,
        ];
    }

    private function noMatch(string $question, ?string $name): array
    {
        $hint = $name
            ? "I couldn't find a customer matching \"{$name}\". Try their full name or company."
            : 'Give me a customer name and I\'ll work out the root cause.';

        return [
            'question'    => $question,
            'matched'     => false,
            'answer'      => $hint,
            'ai_used'     => false,
            'suggestions' => $this->candidateNames(12),
            'accounts'    => [],
        ];
    }

    /**
     * Wrap a computed payload with a written answer — OpenAI when configured,
     * otherwise the plain-text summary.
     */
    private function withAnswer(string $question, array $payload, string $plain, callable $askAi): array
    {
        $base = ['question' => $question] + $payload;

        $client = app(OpenAiClient::class);
        if ($client->isConfigured()) {
            try {
                return $base + ['answer' => $askAi($client), 'ai_used' => true];
            } catch (OpenAiException $e) {
                report($e);
            }
        }

        return $base + ['answer' => $plain, 'ai_used' => false];
    }

    private function askOpenAi(OpenAiClient $client, string $question, array $data, string $instruction): string
    {
        $system = 'You are the Customer Retention copilot inside a B2B analytics platform. '
            . 'Answer using ONLY the JSON data provided as input — never invent a name, number, stage or reason that is not in it. '
            . 'Reply with plain prose sentences a colleague would say out loud. '
            . 'Do NOT return JSON, markdown, bullet points, headings, quotes or key/value pairs. '
            . $instruction;

        $prompt = "Question: {$question}\n\nData:\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $reply = $client->chat($system, $prompt, ['max_tokens' => 320, 'temperature' => 0.2]);

        // Defensive: if the model wrapped its answer in JSON anyway, unwrap it.
        $trimmed = trim($reply);
        if (Str::startsWith($trimmed, '{') && ($decoded = json_decode($trimmed, true)) !== null) {
            $reply = (string) (array_values($decoded)[0] ?? $reply);
        }

        return trim($reply);
    }
}
