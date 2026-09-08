<?php

namespace App\Services\Llm;

use App\Models\BrevoDeliveredRecipient;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\CrmIntegration;
use Illuminate\Support\Collection;

/**
 * Answers the handful of Sales agent predefined prompts that the
 * deterministic dashPromptAnswer() switch in business-helpers.blade.php
 * can't really answer: "What changed since yesterday?" (no real recency
 * diff), "What does [name] care about most?" (previously read an undefined
 * field), the email script variant (no real Brevo engagement lookup), and
 * the six objection-handling prompts (previously generic scripts with zero
 * per-account data). Every other predefined Sales prompt already reads real
 * scored data via rankedFor()/classifySales() and is left untouched — see
 * HANDLED_KEYS.
 *
 * Pulls fresh crm_contacts (incl. raw synced HubSpot properties), crm_deals,
 * crm_integrations (provider/last sync — per the client's confirmed choice
 * over the separate, unused crm_connections table) and, for the email
 * variant, email_logs_providers (formerly email_logs_brevo), then has
 * OpenAI write the answer from that real snapshot. Falls back to a plain,
 * still real-data-grounded (or, for the objection scripts, the original
 * curated) answer when OPENAI_API_KEY isn't set or the call fails.
 */
class SalesPromptInsightsService
{
    public const HANDLED_KEYS = [
        'prioritise:changed_yesterday',
        'understand:cares_about',
        'craft:email_version',
        'handle:too_expensive',
        'handle:not_right_now',
        'handle:use_competitor',
        'handle:send_info',
        'handle:no_budget',
        'handle:need_boss',
    ];

    public function __construct(private readonly OpenAiClient $client)
    {
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

        if ($key === 'prioritise:changed_yesterday') {
            $context['recently_updated_contacts'] = CrmContact::where('updated_at', '>=', now()->subDay())
                ->get()
                ->map(fn (CrmContact $c) => [
                    'name' => trim($c->first_name . ' ' . $c->last_name) ?: $c->company,
                    'company' => $c->company,
                    'updated' => $c->updated_at?->diffForHumans(),
                ])->values()->all();

            $context['recently_updated_deals'] = CrmDeal::where('updated_at', '>=', now()->subDay())
                ->get()
                ->map(fn (CrmDeal $d) => [
                    'name' => $d->name,
                    'stage' => $d->stage,
                    'status' => $d->status,
                    'value' => (float) $d->value,
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

        // The raw prompt label is ambiguous out of context for these six —
        // "Need my boss" reads as the rep's own boss unless framed as the
        // prospect's objection.
        $question = str_starts_with($key, 'handle:')
            ? 'The prospect the rep is talking to just raised this objection: "' . $context['question'] . '". '
                . 'How should the rep respond, given the real account data below?'
            : $context['question'];

        $prompt = $question . "\n\nData:\n"
            . json_encode(collect($context)->except('question')->all(), JSON_PRETTY_PRINT);

        return $this->client->chat($system, $prompt, ['max_tokens' => 320]);
    }

    private function fallback(string $key, array $context): string
    {
        return match ($key) {
            'prioritise:changed_yesterday' => $this->fallbackChangedYesterday($context),
            'understand:cares_about' => $this->fallbackCaresAbout($context),
            'craft:email_version' => $this->fallbackEmailVersion($context),
            default => $this->fallbackObjection($key, $context),
        };
    }

    private function fallbackChangedYesterday(array $context): string
    {
        $contacts = $context['recently_updated_contacts'] ?? [];
        $deals = $context['recently_updated_deals'] ?? [];

        if (empty($contacts) && empty($deals)) {
            return 'Nothing has changed in the last 24 hours — no contact activity and no deal updates since yesterday.';
        }

        $lines = [];
        foreach ($contacts as $c) {
            $lines[] = $c['name'] . ' (' . ($c['company'] ?: 'no company on file') . ') — activity ' . $c['updated'];
        }
        foreach ($deals as $d) {
            $lines[] = 'Deal "' . $d['name'] . '" — ' . $d['stage'] . ' (' . $d['status'] . '), updated ' . $d['updated'];
        }

        return "Since yesterday:\n" . implode("\n", $lines);
    }

    private function fallbackCaresAbout(array $context): string
    {
        $contact = $context['contact'];

        if (!$contact) {
            return 'No matching CRM contact found for that name yet.';
        }

        $props = $contact['synced_hubspot_properties'] ?? [];
        $signals = array_filter([
            !empty($props['num_unique_conversion_events'] ?? null)
                ? $props['num_unique_conversion_events'] . ' conversion event(s) logged' : null,
            !empty($props['notes_last_contacted'] ?? null)
                ? 'last contacted ' . $props['notes_last_contacted'] : null,
            !empty($props['hs_email_last_click_date'] ?? null)
                ? 'last clicked an email on ' . $props['hs_email_last_click_date'] : null,
        ]);

        if (empty($signals)) {
            return $contact['name'] . ' — no specific interest signal has synced from the CRM yet beyond company and email. Ask directly on the next touch.';
        }

        return $contact['name'] . ' — ' . implode('; ', $signals) . '.';
    }

    private function fallbackEmailVersion(array $context): string
    {
        $contact = $context['contact'];
        $name = $contact['name'] ?? 'there';
        $emailActivity = $context['email_activity'] ?? [];

        $engagementNote = empty($emailActivity)
            ? 'No prior email activity on file for this contact yet.'
            : (collect($emailActivity)->contains(fn ($e) => !empty($e['opened_at']))
                ? "They've opened previous emails, so a direct, short subject line works well."
                : 'No opens on file yet — lead with a subject line that states the value plainly.');

        return "Subject: Quick question about {$name}'s next step\n\n"
            . "Hi {$name} — noticed the recent activity on your end and wanted to check in directly rather than let it go quiet. "
            . "What would need to be true for this to be a clear yes?\n\n"
            . $engagementNote;
    }

    private function fallbackObjection(string $key, array $context): string
    {
        $deal = $context['deals'][0] ?? null;
        $dealNote = $deal ? " (their current deal: \${$deal['value']} at the {$deal['stage']} stage)" : '';

        return match ($key) {
            'handle:too_expensive' => '"Compared to what this is costing you today, what would make the number feel fair?" '
                . 'Reframe to value before touching the price. Offer a low-risk start before a discount.' . $dealNote,
            'handle:not_right_now' => '"Understood — what would need to change for the timing to be right?" '
                . 'Get a real reason and a real date, then set a callback for that date rather than a vague follow-up.' . $dealNote,
            'handle:use_competitor' => '"Good to know — what\'s working well with them, and what would you change if you could?" '
                . 'Listen for the gap, then show only the part of your offer that closes it.' . $dealNote,
            'handle:send_info' => '"Send me some info" is often a polite no. Send one short, specific thing (not a brochure) '
                . 'and set a defined follow-up date rather than waiting for them to reply.' . $dealNote,
            'handle:no_budget' => 'Separate "no budget" from "not a priority yet." Ask what it would need to deliver to justify '
                . 'finding the budget — if the answer is vague, it\'s priority, not price.' . $dealNote,
            'handle:need_boss' => 'Ask to join that conversation, or arm them with a one-page summary of the case for their boss. '
                . 'Deals that go dark after "I\'ll check" usually needed that help and didn\'t get it.' . $dealNote,
            default => "I don't have a ready-made answer for that yet.",
        };
    }
}
