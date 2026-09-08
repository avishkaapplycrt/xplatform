<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use Illuminate\Support\Str;

/**
 * Answers the Customer Retention agent's "Save play" questions for one named
 * customer:
 *
 *   - Save plan for [name]
 *   - Save email for [name]
 *   - WhatsApp check-in for [name]
 *   - What are the first 48 hours for [name]?
 *
 * ("Should this go to an executive sponsor?" and "Exec brief for [name]" keep
 * their existing UI answers.)
 *
 * Everything is grounded in the two CRM tables the client syncs:
 *
 *   - crm_contacts → identity + last_activity_at (recency of any CRM touch)
 *   - crm_deals    → the relationship: value, stage, won / open / lost, close
 *                    date. A deal is tied to a contact by the "deal name
 *                    starts with company name" heuristic used across the app.
 *
 * From those the service reads a "situation" — gone quiet after the win, a
 * deal stalling in the pipeline, an already-lost account, healthy, or no
 * relationship on record — and every save artifact (plan, email, WhatsApp,
 * 48-hour checklist) is written for that specific situation.
 *
 * Contact resolution / the name datalist are shared with
 * RetentionRootCauseService. The OpenAI key only phrases the artifact; with
 * no key each method still returns a usable plain-text version.
 */
class RetentionSavePlayService
{
    private const LATE_STAGES  = ['decisionmakerboughtin', 'presentationscheduled', 'contractsent'];
    private const EARLY_STAGES = ['appointmentscheduled', 'qualifiedtobuy'];

    public function __construct(private readonly RetentionRootCauseService $contacts)
    {
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q1 — Save plan for [name]
    // ─────────────────────────────────────────────────────────────────────────

    public function savePlan(?string $name): array
    {
        $question = 'Save plan for ' . ($name ?: 'this customer');
        [$p, $miss] = $this->profileFor($name);
        if ($miss) {
            return $miss;
        }

        $sequence = [
            'Call today — no email first. Ask one open question about what changed and then listen.',
            'Within the hour, send a short written recap: what you heard, what you will do, by when.',
            'Do the one thing you promised inside 48 hours — a fix, an intro, a plan — and show proof it happened.',
            'Book the next check-in before you hang up, so there is a defined next touch, not a vague "I\'ll follow up".',
        ];
        if ($p['escalate']) {
            array_splice($sequence, 1, 0, [
                'Loop in an executive sponsor now — $' . number_format($p['deal_value']) . ' and this situation justify their time.',
            ]);
        }

        $plain = ucfirst($p['name']) . ' — ' . $p['situation_text'] . ' '
            . 'Fix first: ' . $p['fix_first'] . ' '
            . 'Then: ' . implode(' ', $sequence);

        return $this->withAnswer($question, [
            'matched'   => true,
            'name'      => $p['name'],
            'company'   => $p['company'],
            'situation' => $p['situation'],
            'fix_first' => $p['fix_first'],
            'sequence'  => $sequence,
            'escalate'  => $p['escalate'],
            'facts'     => $this->facts($p),
            'accounts'  => [$this->accountRow($p)],
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'name' => $p['name'], 'company' => $p['company'],
            'situation' => $p['situation_text'], 'fix_first' => $p['fix_first'],
            'sequence' => $sequence, 'escalate' => $p['escalate'], 'facts' => $this->facts($p),
        ], 'Write the save plan as short prose: one line naming the situation, one line on the single thing to fix first, then the numbered steps in order with the timing on each. Under 130 words. You may use line breaks between steps but no markdown symbols.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q2 — Save email for [name]
    // ─────────────────────────────────────────────────────────────────────────

    public function saveEmail(?string $name): array
    {
        $question = 'Save email for ' . ($name ?: 'this customer');
        [$p, $miss] = $this->profileFor($name);
        if ($miss) {
            return $miss;
        }

        $subject = match ($p['situation']) {
            'silent_after_win' => 'Checking in properly — not a newsletter',
            'deal_stalling'    => 'Where things stand with ' . ($p['company'] !== '—' ? $p['company'] : 'your rollout'),
            'lost'             => 'A cleaner offer for ' . ($p['company'] !== '—' ? $p['company'] : 'you'),
            'no_relationship'  => 'Quick check on where we stand',
            default            => 'A quick, direct check-in',
        };

        $body = $this->plainEmailBody($p);
        $plain = "Subject: {$subject}\n\n{$body}";

        return $this->withAnswer($question, [
            'matched'  => true,
            'name'     => $p['name'],
            'company'  => $p['company'],
            'subject'  => $subject,
            'body'     => $body,
            'facts'    => $this->facts($p),
            'accounts' => [$this->accountRow($p)],
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'name' => $p['name'], 'company' => $p['company'],
            'situation' => $p['situation_text'], 'suggested_subject' => $subject, 'facts' => $this->facts($p),
        ], 'Write a save email of about 90 to 130 words. Start with a "Subject:" line, then a blank line, then the body. '
            . 'Direct and human, no apologising or grovelling, one clear ask (a short call this week). '
            . 'Reference the real situation only. Line breaks are fine; no markdown symbols.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q3 — WhatsApp check-in for [name]
    // ─────────────────────────────────────────────────────────────────────────

    public function whatsappCheckin(?string $name): array
    {
        $question = 'WhatsApp check-in for ' . ($name ?: 'this customer');
        [$p, $miss] = $this->profileFor($name);
        if ($miss) {
            return $miss;
        }

        $first = explode(' ', trim($p['name']))[0] ?: 'there';
        $plain = match ($p['situation']) {
            'silent_after_win' => "Hi {$first} 👋 been a while — how's it going your end? Anything getting in the way I can help sort?",
            'deal_stalling'    => "Hi {$first} 👋 quick one on the {$p['company']} plan — where's your head at? Happy to jump on a 10-min call if useful.",
            'lost'             => "Hi {$first} 👋 no pitch — just wondered how things worked out after we last spoke. Open to a quick catch-up?",
            default            => "Hi {$first} 👋 quick check-in — all good on your side? Shout if there's anything I can help with.",
        };

        return $this->withAnswer($question, [
            'matched'  => true,
            'name'     => $p['name'],
            'company'  => $p['company'],
            'facts'    => $this->facts($p),
            'accounts' => [$this->accountRow($p)],
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'name' => $p['name'], 'first_name' => $first, 'company' => $p['company'],
            'situation' => $p['situation_text'], 'facts' => $this->facts($p),
        ], 'Write one WhatsApp message, two short sentences at most, first-name only, casual and low-pressure, '
            . 'ending in a question. One relaxed emoji is fine. No links, no hard ask. Just the message text.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q4 — What are the first 48 hours for [name]?
    // ─────────────────────────────────────────────────────────────────────────

    public function first48Hours(?string $name): array
    {
        $question = 'What are the first 48 hours for ' . ($name ?: 'this customer') . '?';
        [$p, $miss] = $this->profileFor($name);
        if ($miss) {
            return $miss;
        }

        $checklist = [
            '0–2 h'   => 'Call ' . $p['name'] . '. One question: what changed? Take notes, do not pitch. ' . ($p['escalate'] ? 'Brief the exec sponsor straight after.' : ''),
            '2–24 h'  => 'Send the written recap — what you heard, the one thing you will do, the date you will do it by. Start on ' . $p['fix_first_short'] . '.',
            '24–48 h' => 'Deliver the promised action and send proof it is done. Confirm the next check-in is in the calendar.',
        ];

        $plain = collect($checklist)->map(fn ($v, $k) => "{$k}: {$v}")->implode("\n");

        return $this->withAnswer($question, [
            'matched'   => true,
            'name'      => $p['name'],
            'company'   => $p['company'],
            'situation' => $p['situation'],
            'checklist' => $checklist,
            'facts'     => $this->facts($p),
            'accounts'  => [$this->accountRow($p)],
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'name' => $p['name'], 'company' => $p['company'],
            'situation' => $p['situation_text'], 'fix_first' => $p['fix_first'],
            'checklist' => $checklist, 'escalate' => $p['escalate'], 'facts' => $this->facts($p),
        ], 'Give the first 48 hours as three time-boxed lines: "0-2 h:", "2-24 h:", "24-48 h:", each one concrete action for this customer. '
            . 'One line per block, line breaks between them, no markdown symbols. Under 100 words.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CRM profile (crm_contacts + crm_deals only)
    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{0: array, 1: array|null}  [profile, noMatchResponse|null] */
    private function profileFor(?string $name): array
    {
        $contact = $name ? $this->contacts->resolveContact($name) : null;
        if (!$contact) {
            return [[], $this->noMatch($name)];
        }

        return [$this->profile($contact), null];
    }

    private function profile(CrmContact $contact): array
    {
        $deals = CrmDeal::all();
        $maxValue = max(1.0, (float) $deals->max('value'));

        $company = trim((string) $contact->company);
        $matched = $company === ''
            ? collect()
            : $deals->filter(fn (CrmDeal $d) => Str::startsWith((string) $d->name, $company))->values();

        $won  = $matched->filter(fn (CrmDeal $d) => $d->status === 'won' || $d->stage === 'closedwon')->values();
        $lost = $matched->filter(fn (CrmDeal $d) => $d->status === 'lost' || Str::contains((string) $d->stage, 'lost'))->values();
        $open = $matched->filter(fn (CrmDeal $d) => !$won->contains($d) && !$lost->contains($d))->values();

        $now = now();
        $overdue  = $open->filter(fn (CrmDeal $d) => $d->close_date && $d->close_date->lt($now))->values();
        $slipping = $open->filter(fn (CrmDeal $d) => $d->close_date
            && $d->close_date->between($now, $now->copy()->addDays(21))
            && in_array($d->stage, self::EARLY_STAGES, true))->values();

        $dealValue = (float) $matched->sum('value');
        $daysSince = $contact->last_activity_at ? (int) $contact->last_activity_at->diffInDays(now()) : null;

        $displayName = trim($contact->first_name . ' ' . $contact->last_name);
        $tier = match (true) {
            $dealValue >= $maxValue * 0.5  => 'large',
            $dealValue >= $maxValue * 0.15 => 'mid',
            default                        => 'small',
        };

        // Which save situation is this?
        [$situation, $situationText, $fixFirst, $fixFirstShort] = match (true) {
            $matched->isEmpty() => [
                'no_relationship',
                'there is no deal on record, so confirm the relationship is real before running a save.',
                'confirming they are actually a customer and who the real contact is.',
                'confirming the relationship',
            ],
            $lost->isNotEmpty() && $won->isEmpty() => [
                'lost',
                'the deal is already lost, so this is a win-back, not a rescue.',
                'understanding why it fell over last time before proposing anything new.',
                'the reason it was lost',
            ],
            $won->isNotEmpty() && $daysSince !== null && $daysSince > 45 => [
                'silent_after_win',
                'they bought but have gone quiet for ' . $daysSince . ' days — an adoption or value problem, not a price one.',
                'getting them back to first value — a working session, not a discount.',
                're-onboarding to first value',
            ],
            $open->isNotEmpty() && ($overdue->isNotEmpty() || $slipping->isNotEmpty()) => [
                'deal_stalling',
                'their $' . number_format((float) $open->sum('value')) . ' deal is stalling in ' . ($open->first()->stage ?? 'the pipeline') . ' around its close date.',
                'the one blocker holding the deal — name it on the call, do not guess.',
                'the deal blocker',
            ],
            default => [
                'healthy',
                'the CRM shows no strong churn signal — treat this as a proactive check-in, not a rescue.',
                'confirming nothing is quietly wrong and re-agreeing the next milestone.',
                'confirming health',
            ],
        };

        $escalate = $tier === 'large' && in_array($situation, ['silent_after_win', 'deal_stalling', 'lost'], true);

        return [
            'contact'         => $contact,
            'name'            => $displayName !== '' ? $displayName : ($company !== '' ? $company : (string) $contact->email),
            'company'         => $company ?: '—',
            'deal_count'      => $matched->count(),
            'deal_value'      => round($dealValue, 2),
            'open_value'      => round((float) $open->sum('value'), 2),
            'is_customer'     => $won->isNotEmpty(),
            'has_open'        => $open->isNotEmpty(),
            'has_lost'        => $lost->isNotEmpty(),
            'primary_stage'   => ($open->first() ?? $won->first() ?? $matched->first())?->stage ?? '—',
            'value_tier'      => $tier,
            'days_since'      => $daysSince,
            'situation'       => $situation,
            'situation_text'  => $situationText,
            'fix_first'       => $fixFirst,
            'fix_first_short' => $fixFirstShort,
            'escalate'        => $escalate,
        ];
    }

    private function plainEmailBody(array $p): string
    {
        $first = explode(' ', trim($p['name']))[0] ?: 'there';

        return match ($p['situation']) {
            'silent_after_win' =>
                "Hi {$first},\n\nWe haven't spoken in a while and I'd rather hear how it's actually going than assume it's fine. "
                . "If something got in the way of getting value out of this, that's on me to help fix.\n\n"
                . "Do you have 15 minutes this week? I'll come with a plan, not a pitch.",
            'deal_stalling' =>
                "Hi {$first},\n\nI don't want the "
                . ($p['company'] !== '—' ? $p['company'] . ' ' : '')
                . "conversation to drift. If there's one thing holding it up, tell me straight and I'll work the problem rather than chase you.\n\n"
                . "15 minutes this week to line it up?",
            'lost' =>
                "Hi {$first},\n\nNo pitch. Last time didn't land and I'd genuinely like to understand what we got wrong.\n\n"
                . "If you're open to it, give me 15 minutes and I'll bring a cleaner, smaller proposal — or nothing at all if that's the right call.",
            default =>
                "Hi {$first},\n\nQuick, direct check-in: is everything working the way you expected? "
                . "If not, I'd rather know now than at renewal.\n\n15 minutes this week?",
        };
    }

    private function facts(array $p): array
    {
        return [
            'is_paying_customer'  => $p['is_customer'],
            'deal_count'          => $p['deal_count'],
            'deal_value'          => $p['deal_value'],
            'primary_stage'       => $p['primary_stage'],
            'value_tier'          => $p['value_tier'],
            'days_since_activity' => $p['days_since'],
            'situation'           => $p['situation'],
        ];
    }

    private function accountRow(array $p): array
    {
        return [
            'name'                => $p['name'],
            'company'             => $p['company'],
            'deal_value'          => $p['deal_value'],
            'deal_status'         => $p['is_customer'] ? 'won' : ($p['has_open'] ? 'open' : ($p['has_lost'] ? 'lost' : null)),
            'stage'               => $p['primary_stage'],
            'days_since_activity' => $p['days_since'] ?? '—',
        ];
    }

    private function noMatch(?string $name): array
    {
        return [
            'question'    => 'Save play',
            'matched'     => false,
            'answer'      => $name
                ? "I couldn't find a customer matching \"{$name}\". Try their full name or company."
                : 'Give me a customer name and I\'ll build the save play.',
            'ai_used'     => false,
            'suggestions' => $this->contacts->candidateNames(12),
            'accounts'    => [],
        ];
    }

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

    private function ask(OpenAiClient $client, string $question, array $data, string $instruction): string
    {
        $system = 'You are the Customer Retention copilot inside a B2B analytics platform. '
            . 'Use ONLY the JSON data provided as input — never invent a name, number, stage or fact that is not in it. '
            . 'Write for a retention rep to use as-is. Plain text only: no markdown, no headings, no bullet characters, no JSON. '
            . $instruction;

        $prompt = "Task: {$question}\n\nData:\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $reply = trim($client->chat($system, $prompt, ['max_tokens' => 420, 'temperature' => 0.35]));

        if (Str::startsWith($reply, '{') && ($decoded = json_decode($reply, true)) !== null) {
            $reply = (string) (array_values($decoded)[0] ?? $reply);
        }

        return trim($reply);
    }
}
