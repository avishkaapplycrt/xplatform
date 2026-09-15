<?php

namespace App\Services\Llm;

use App\Models\BrevoDeliveredRecipient;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\CrmIntegration;
use App\Services\RealAccountsService;
use Illuminate\Support\Collection;

/**
 * Answers the Sales agent predefined prompts that the deterministic
 * dashPromptAnswer() switch in business-helpers.blade.php can't really
 * answer: whether a named account's priority has genuinely changed
 * recently (needs a real recency diff, not just the current snapshot), and
 * the eight objection-response prompts spread across the Pitch ("how should
 * I respond if...") and Overcome ("I'm not interested", "Contact me
 * later", ...) categories — previously generic scripts with zero
 * per-account data. Every other predefined Sales prompt already reads real
 * scored data via rankedFor()/classifySales() and is left untouched — see
 * HANDLED_KEYS.
 *
 * Pulls fresh crm_contacts (incl. raw synced HubSpot properties), crm_deals
 * and crm_integrations (provider/last sync — per the client's confirmed
 * choice over the separate, unused crm_connections table), then has OpenAI
 * write the answer from that real snapshot. Falls back to a plain, still
 * real-data-grounded (or, for the objection scripts, the original curated)
 * answer when OPENAI_API_KEY isn't set or the call fails.
 */
class SalesPromptInsightsService
{
    /**
     * Pitch-category "how should I respond if..." prompts map onto the same
     * underlying objection as their Overcome-category counterpart — same
     * real data, same response, just reached from a different question.
     */
    private const OBJECTION_ALIASES = [
        'craft:not_ready_response' => 'handle:not_right_now',
        'craft:too_expensive_response' => 'handle:too_expensive',
        'craft:competitor_response' => 'handle:use_competitor',
    ];

    private const OBJECTION_FALLBACKS = [
        'handle:not_interested' => 'Don\'t argue the "no" — get curious instead. '
            . '"Fair enough — can I ask what\'s not landing? I\'d rather know than guess." '
            . 'If they engage, you\'ve found the real objection underneath. If they don\'t, let them go without pushing.',
        'handle:not_right_now' => '"Understood — what would need to change for the timing to be right?" '
            . 'Get a real reason and a real date, then set a callback for that date rather than a vague follow-up.',
        'handle:why_need_this' => 'Don\'t defend the product — ask what "fine as-is" is actually costing them. '
            . '"What happens if this stays the same for another six months?" Let their own answer make the case.',
        'handle:use_competitor' => '"Good to know — what\'s working well with them, and what would you change if you could?" '
            . 'Listen for the gap, then show only the part of your offer that closes it.',
        'handle:too_expensive' => '"Compared to what this is costing you today, what would make the number feel fair?" '
            . 'Reframe to value before touching the price. Offer a low-risk start before a discount.',
    ];

    public const HANDLED_KEYS = [
        'understand:priority_changed',
        'craft:not_ready_response',
        'craft:too_expensive_response',
        'craft:competitor_response',
        'handle:not_interested',
        'handle:not_right_now',
        'handle:why_need_this',
        'handle:use_competitor',
        'handle:too_expensive',
    ];

    public function __construct(
        private readonly OpenAiClient $client,
        private readonly RealAccountsService $accounts,
    ) {
    }

    public static function handles(string $step, string $prompt): bool
    {
        return in_array("{$step}:{$prompt}", self::HANDLED_KEYS, true);
    }

    /**
     * @return array{answer: string, ai_used: bool}
     */
    public function answer(string $step, string $prompt, ?string $name, string $label): array
    {
        $key = "{$step}:{$prompt}";

        if (!in_array($key, self::HANDLED_KEYS, true)) {
            return ['answer' => "I don't have a ready-made answer for that yet.", 'ai_used' => false];
        }

        $context = $this->gatherContext($key, $name, $label);

        if (!$this->client->isConfigured()) {
            return ['answer' => $this->fallback($key, $context), 'ai_used' => false];
        }

        try {
            return ['answer' => $this->ask($key, $context), 'ai_used' => true];
        } catch (OpenAiException $e) {
            report($e);
            // A broken AI call should never take down a chat that has real
            // data behind it — surface the same grounded fallback answer.
            return ['answer' => $this->fallback($key, $context), 'ai_used' => false];
        }
    }

    private function gatherContext(string $key, ?string $name, string $label): array
    {
        $integration = CrmIntegration::byProvider('hubspot')->connected()->first();
        $contact = $name ? $this->findContact($name) : null;
        $deals = $contact ? $this->dealsFor($contact) : collect();
        $emailRows = $contact && $contact->email ? $this->emailsFor($contact->email) : collect();

        $context = [
            'question' => $label,
            'crm_connection' => $integration ? [
                'provider' => $integration->provider,
                'status' => $integration->status,
                'last_synced' => $integration->last_sync_at?->diffForHumans(),
                'sync_count' => $integration->sync_count,
            ] : null,
            'contact' => $contact ? [
                'name' => trim($contact->first_name . ' ' . $contact->last_name) ?: $contact->company,
                'company' => $contact->company,
                'email' => $contact->email,
                'last_activity' => $contact->last_activity_at?->diffForHumans(),
                'synced_hubspot_properties' => $contact->raw_data['properties'] ?? null,
            ] : null,
            'deals' => $deals->map(fn (CrmDeal $d) => [
                'name' => $d->name,
                'value' => (float) $d->value,
                'stage' => $d->stage,
                'status' => $d->status,
                'close_date' => $d->close_date?->toDateString(),
                'last_updated' => $d->updated_at?->diffForHumans(),
            ])->values()->all(),
            'email_activity' => $emailRows->map(fn (BrevoDeliveredRecipient $r) => [
                'delivered_at' => $r->delivered_at?->toDateTimeString(),
                'opened_at' => $r->opened_at?->toDateTimeString(),
                'clicked' => (bool) $r->clicked,
                'unsubscribed_at' => $r->unsubscribed_at?->toDateTimeString(),
            ])->values()->all(),
        ];

        if ($key === 'understand:priority_changed') {
            // No historical priority snapshot is stored anywhere in this
            // system, so "changed recently" is answered honestly from what
            // actually IS recorded: whether this specific contact's record
            // or their matched deal(s) were touched in the last 7 days.
            $context['contact_recently_updated'] = $contact
                && $contact->updated_at
                && $contact->updated_at->greaterThanOrEqualTo(now()->subDays(7));

            $context['deals_recently_updated'] = $deals
                ->filter(fn (CrmDeal $d) => $d->updated_at && $d->updated_at->greaterThanOrEqualTo(now()->subDays(7)))
                ->map(fn (CrmDeal $d) => [
                    'name' => $d->name,
                    'stage' => $d->stage,
                    'status' => $d->status,
                    'updated' => $d->updated_at?->diffForHumans(),
                ])->values()->all();
        }

        return $context;
    }

    private function findContact(string $name): ?CrmContact
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        return CrmContact::query()
            ->where('company', $name)
            ->orWhereRaw("TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) = ?", [$name])
            ->first();
    }

    private function dealsFor(CrmContact $contact): Collection
    {
        if (!$contact->company) {
            return collect();
        }

        return CrmDeal::where('name', 'like', $contact->company . '%')->get();
    }

    private function emailsFor(string $email): Collection
    {
        return BrevoDeliveredRecipient::whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();
    }

    private function ask(string $key, array $context): string
    {
        $system = 'You are the Sales copilot inside a B2B analytics platform. '
            . 'Answer the sales rep\'s question using ONLY the data provided as JSON — '
            . 'never invent a name, number, company, or fact that isn\'t in that data. '
            . 'If something needed to answer precisely is missing or empty in the data, say so plainly rather than guessing. '
            . 'Be brief, concrete, and practical — under 130 words, plain English, no headers or markdown.';

        // The raw prompt label is ambiguous out of context for the objection
        // prompts — "Need help from my boss" style wording reads as the
        // rep's own boss unless framed as the prospect's objection.
        $isObjection = str_starts_with($key, 'handle:') || array_key_exists($key, self::OBJECTION_ALIASES);
        $question = $isObjection
            ? 'The prospect the rep is talking to just raised this objection: "' . $context['question'] . '". '
                . 'How should the rep respond, given the real account data below?'
            : $context['question'];

        $prompt = $question . "\n\nData:\n"
            . json_encode(collect($context)->except('question')->all(), JSON_PRETTY_PRINT);

        return $this->client->chat($system, $prompt, ['max_tokens' => 320]);
    }

    private function fallback(string $key, array $context): string
    {
        if ($key === 'understand:priority_changed') {
            return $this->fallbackPriorityChanged($context);
        }

        return $this->fallbackObjection($key, $context);
    }

    private function fallbackPriorityChanged(array $context): string
    {
        $contact = $context['contact'];

        if (!$contact) {
            return 'No matching CRM contact found for that name yet.';
        }

        $dealsChanged = $context['deals_recently_updated'] ?? [];
        $contactChanged = $context['contact_recently_updated'] ?? false;

        if (!$contactChanged && empty($dealsChanged)) {
            return $contact['name'] . ' — no contact or deal updates recorded in the last 7 days. '
                . 'This system doesn\'t store a historical priority score to compare against, so "unchanged" here means no new CRM activity, not a confirmed same ranking.';
        }

        $lines = [];
        if ($contactChanged) {
            $lines[] = 'their contact record was updated ' . ($contact['last_activity'] ?? 'recently');
        }
        foreach ($dealsChanged as $d) {
            $lines[] = 'deal "' . $d['name'] . '" moved to ' . $d['stage'] . ' (' . $d['status'] . '), updated ' . $d['updated'];
        }

        return $contact['name'] . ' has recent activity: ' . implode('; ', $lines) . '. '
            . 'No historical priority snapshot is stored, so treat this as "something moved," not a confirmed rank change.';
    }

    private function fallbackObjection(string $key, array $context): string
    {
        $resolvedKey = self::OBJECTION_ALIASES[$key] ?? $key;
        $text = self::OBJECTION_FALLBACKS[$resolvedKey] ?? "I don't have a ready-made answer for that yet.";

        $deal = $context['deals'][0] ?? null;
        $dealNote = $deal ? " (their current deal: \${$deal['value']} at the {$deal['stage']} stage)" : '';

        return $text . $dealNote;
    }

    /**
     * Powers the Overcome category's structured playbook — "when the
     * salesperson selects a client" — diagnosing the single most likely
     * objection from real account scores + CRM/deal detail, then giving the
     * 4-part breakdown (objection, real barrier, recommended response,
     * proof to use). Uses OpenAI when configured, with a deterministic
     * rule-based fallback (same real signals, no invented facts) when it
     * isn't or the call fails.
     *
     * @return array{objection: string, confidence: string, why: string, barrier: string, response: string, proof: string, ai_used: bool}
     */
    public function objectionPlaybook(?string $name): array
    {
        $accounts = $this->accounts->build();
        $account = $name
            ? $accounts->first(fn (array $a) => $a['name'] === $name)
            : $accounts->first();

        if (!$account) {
            return [
                'objection' => null, 'confidence' => null, 'why' => null,
                'barrier' => null, 'response' => null, 'proof' => null,
                'ai_used' => false,
            ];
        }

        $contact = $this->findContact($account['name']);
        $deals = $contact ? $this->dealsFor($contact) : collect();
        $emailRows = $contact && $contact->email ? $this->emailsFor($contact->email) : collect();

        $context = [
            'account' => $account,
            'deals' => $deals->map(fn (CrmDeal $d) => [
                'name' => $d->name, 'value' => (float) $d->value,
                'stage' => $d->stage, 'status' => $d->status,
            ])->values()->all(),
            'email_activity' => $emailRows->map(fn (BrevoDeliveredRecipient $r) => [
                'opened_at' => $r->opened_at?->toDateTimeString(),
                'clicked' => (bool) $r->clicked,
                'unsubscribed_at' => $r->unsubscribed_at?->toDateTimeString(),
            ])->values()->all(),
        ];

        if ($this->client->isConfigured()) {
            try {
                $parsed = $this->askObjectionJson($context);
                if ($parsed !== null) {
                    return $parsed + ['ai_used' => true];
                }
            } catch (OpenAiException $e) {
                report($e);
                // Fall through to the deterministic diagnosis below.
            }
        }

        return $this->fallbackObjectionPlaybook($account) + ['ai_used' => false];
    }

    /**
     * @return array{objection: string, confidence: string, why: string, barrier: string, response: string, proof: string}|null
     */
    private function askObjectionJson(array $context): ?array
    {
        $system = 'You are a sales objection-diagnosis assistant inside a B2B analytics platform. '
            . 'Given one account\'s real CRM data as JSON, diagnose the single most likely objection this '
            . 'prospect would raise right now, using ONLY the data given — never invent a fact. '
            . 'Respond with ONLY a raw JSON object, no markdown fences, no prose outside it, matching exactly this shape: '
            . '{"objection": string, "confidence": "High"|"Medium"|"Low", "why": string, "barrier": string, "response": string, "proof": string}. '
            . '"objection" must be one of exactly: "Not interested", "Contact me later", "Why do we need this?", '
            . '"We\'re happy with our current provider", "It\'s too expensive compared to other options". '
            . '"why" cites the real numbers/signals in the data to justify the diagnosis and confidence level. '
            . '"barrier" explains what is really holding them back underneath the stated objection. '
            . '"response" is one natural line the rep could actually say. '
            . '"proof" names the single best category of evidence for this account (ROI/cost saving, a relevant customer result, '
            . 'a product comparison, a specific feature, a case study, or their own previous activity) and why it fits.';

        $prompt = "Account data:\n" . json_encode($context, JSON_PRETTY_PRINT);

        $raw = $this->client->chat($system, $prompt, ['max_tokens' => 500, 'temperature' => 0.2]);
        $clean = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($raw)));
        $decoded = json_decode($clean, true);

        if (!is_array($decoded) || !isset($decoded['objection'], $decoded['response'])) {
            return null;
        }

        return [
            'objection' => (string) $decoded['objection'],
            'confidence' => (string) ($decoded['confidence'] ?? 'Medium'),
            'why' => (string) ($decoded['why'] ?? ''),
            'barrier' => (string) ($decoded['barrier'] ?? ''),
            'response' => (string) $decoded['response'],
            'proof' => (string) ($decoded['proof'] ?? ''),
        ];
    }

    /**
     * @return array{objection: string, confidence: string, why: string, barrier: string, response: string, proof: string}
     */
    private function fallbackObjectionPlaybook(array $account): array
    {
        $s = $account['scores'];
        $hasDeal = $account['deal_stage'] !== null;
        $trust = $s['trust'];
        $engagement = $s['engagement'];
        $readiness = $s['buying_readiness'];

        if (!$hasDeal && $engagement < 40) {
            return [
                'objection' => 'Not interested',
                'confidence' => 'Medium',
                'why' => "No active deal on file and engagement is only {$engagement} — low signal of genuine interest.",
                'barrier' => 'Either a genuine lack of interest, or relevance was never established for their specific situation.',
                'response' => '"Fair enough — can I ask what\'s not landing? I\'d rather know than guess."',
                'proof' => 'A relevant customer result — a concrete example close to their own situation, to re-establish relevance.',
            ];
        }

        if (!$hasDeal) {
            return [
                'objection' => 'Why do we need this?',
                'confidence' => 'Medium',
                'why' => "No active deal yet, but engagement is {$engagement} — they're paying attention without a clear reason to act.",
                'barrier' => 'The value case hasn\'t been made concrete for their specific situation yet.',
                'response' => '"What happens if this stays the same for another six months?"',
                'proof' => 'ROI / cost saving — quantify what standing still is costing them.',
            ];
        }

        if ($trust < 65 && $readiness >= 50) {
            return [
                'objection' => "It's too expensive compared to other options",
                'confidence' => ($readiness - $trust) >= 20 ? 'High' : 'Medium',
                'why' => "Buying readiness ({$readiness}) is ahead of trust ({$trust}) — they believe the problem is real but aren't yet convinced this is worth the price.",
                'barrier' => 'Price feels high because the value hasn\'t fully landed yet — this reads as a value gap, not a hard budget ceiling.',
                'response' => '"Compared to what this is costing you today, what would make the number feel fair?"',
                'proof' => 'ROI / cost saving — show how a similar customer reduced costs using the recommended package.',
            ];
        }

        if ($engagement < 50) {
            return [
                'objection' => 'Contact me later',
                'confidence' => 'Medium',
                'why' => "There's an open deal ({$account['deal_stage_label']}) but engagement has cooled to {$engagement} — momentum has stalled.",
                'barrier' => 'Likely timing rather than lack of interest — competing priorities right now, not a lost cause.',
                'response' => '"Understood — what would need to change for the timing to be right?"',
                'proof' => 'A relevant customer result — a timely example to re-open the conversation.',
            ];
        }

        return [
            'objection' => "We're happy with our current provider",
            'confidence' => 'Low',
            'why' => "Trust ({$trust}) and readiness ({$readiness}) are both solid but the deal ({$account['deal_stage_label']}) hasn't moved — they may be quietly comparing alternatives.",
            'barrier' => 'Perceived parity with an existing solution, not a real blocker.',
            'response' => '"Good to know — what\'s working well with them, and what would you change if you could?"',
            'proof' => 'Product comparison — a direct comparison on whatever specific gap they name.',
        ];
    }
}
