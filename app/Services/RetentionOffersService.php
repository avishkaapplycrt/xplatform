<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Answers the Customer Retention agent's "Offers" questions for one named
 * customer:
 *
 *   - Can I discount [name]?
 *   - What am I allowed to offer [name]?
 *   - What give-get should I ask for?
 *
 * (The other two Offers prompts — "Is a concession worth it" and
 * "Downgrade-to-save option" — are left on their existing UI answers.)
 *
 * Everything is derived from the two CRM tables the client syncs:
 *
 *   - crm_contacts → identity + last_activity_at
 *   - crm_deals    → the pricing relationship: value, stage, won / open /
 *                    lost, close date; a deal is tied to a contact by the
 *                    "deal name starts with company name" heuristic used
 *                    across this codebase.
 *
 * There is no contract-term, MRR or renewal-date field in the synced data,
 * so the guidance is framed around what CAN be read: whether they are a
 * paying (won) customer, the size of the deal relative to the book, whether
 * a deal is still open, and how recently the CRM saw them.
 *
 * Contact resolution and the name datalist are shared with
 * RetentionRootCauseService. The OpenAI key only phrases the final answer;
 * with no key every method still returns a correct plain-text answer.
 */
class RetentionOffersService
{
    public function __construct(private readonly RetentionRootCauseService $contacts)
    {
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q1 — Can I discount [name]?
    // ─────────────────────────────────────────────────────────────────────────

    public function canDiscount(?string $name): array
    {
        $question = 'Can I discount ' . ($name ?: 'this customer') . '?';
        $contact = $name ? $this->contacts->resolveContact($name) : null;
        if (!$contact) {
            return $this->noMatch($question, $name);
        }

        $p = $this->profile($contact);

        [$verdict, $ceiling, $reasons] = match (true) {
            $p['deal_count'] === 0 => [
                'no_relationship', null,
                ['There is no deal on record for this account, so there is nothing to discount — confirm they are actually a paying customer first.'],
            ],
            $p['has_open'] && !$p['is_customer'] => [
                'sales_decision', null,
                [
                    'Their $' . number_format($p['open_value']) . ' deal is still open (' . $p['primary_stage'] . ') — it has not closed.',
                    'Discounting an open deal is a Sales call, not a retention save; a retention discount only applies to an existing paying customer.',
                ],
            ],
            $p['has_lost'] && !$p['is_customer'] => [
                'winback_only', $this->ceiling($p),
                [
                    'Their deal was already lost, so this is a win-back, not a save.',
                    'A discount can reopen the conversation, but only inside a fresh proposal with new scope — not as a reactive price cut.',
                ],
            ],
            default => [
                'yes_if_traded', $this->ceiling($p),
                array_values(array_filter([
                    'They are a paying customer (won deal ' . '$' . number_format($p['won_value']) . ').',
                    'A discount is a legitimate save lever here — but only traded for something, never given just to stop a complaint.',
                    'Keep it time-boxed and tied to a longer commitment; a permanent cut resets what they expect to pay from now on.',
                    $p['days_since'] !== null && $p['days_since'] > 45
                        ? 'They have also gone quiet (' . $p['days_since'] . ' days) — fix the disengagement first; discounting a product they are not using only delays the churn.'
                        : null,
                ])),
            ],
        };

        $verdictLabel = [
            'no_relationship' => 'nothing to discount',
            'sales_decision'  => 'not a retention discount',
            'winback_only'    => 'only as a win-back offer',
            'yes_if_traded'   => 'yes — but only in exchange for a commitment',
        ][$verdict];

        $plain = ucfirst($p['name']) . ': ' . $verdictLabel . '. ' . implode(' ', $reasons)
            . ($ceiling ? " Ceiling: about {$ceiling}, and only against an annual or multi-year commitment." : '');

        return $this->withAnswer($question, [
            'matched'          => true,
            'name'             => $p['name'],
            'company'          => $p['company'],
            'verdict'          => $verdict,
            'suggested_ceiling' => $ceiling,
            'signals'          => $reasons,
            'facts'            => $this->facts($p),
            'accounts'         => [$this->accountRow($p)],
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'name' => $p['name'], 'company' => $p['company'],
            'verdict' => $verdict, 'suggested_ceiling' => $ceiling,
            'reasons' => $reasons, 'facts' => $this->facts($p),
        ], 'Give the answer (yes / no / not yet) in the first sentence, then the reason, then the one condition that must be attached. Under 90 words.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q2 — What am I allowed to offer [name]?
    // ─────────────────────────────────────────────────────────────────────────

    public function whatAllowedToOffer(?string $name): array
    {
        $question = 'What am I allowed to offer ' . ($name ?: 'this customer') . '?';
        $contact = $name ? $this->contacts->resolveContact($name) : null;
        if (!$contact) {
            return $this->noMatch($question, $name);
        }

        $p = $this->profile($contact);
        $offers = [];

        if ($p['is_customer']) {
            $offers[] = ['offer' => 'Time-boxed term discount', 'when' => 'traded for an annual or multi-year commitment', 'note' => 'e.g. two months free on a 12-month renewal — expires if they do not commit'];
            $offers[] = ['offer' => 'Right-size the plan (downgrade-to-save)', 'when' => 'if they are paying for more than they use', 'note' => 'keeping ~60% of the revenue beats losing 100%'];
            $offers[] = ['offer' => 'Pause instead of cancel', 'when' => 'if the trigger is a budget freeze or a paused project', 'note' => '30–90 day hold, no billing, their data kept'];
            if ($p['days_since'] !== null && $p['days_since'] > 45) {
                $offers[] = ['offer' => 'Added onboarding / success hours', 'when' => 'they have gone quiet and stopped seeing value', 'note' => 're-activation fixes the real problem; a discount does not'];
            }
        }
        if ($p['has_open'] && !$p['is_customer']) {
            $offers[] = ['offer' => 'Extended / milestone payment terms', 'when' => 'if the blocker is cash flow rather than price', 'note' => 'quarterly or on-delivery billing'];
            $offers[] = ['offer' => 'Pilot or trial extension', 'when' => 'if they need more proof before committing', 'note' => 'time-boxed, with one agreed success metric'];
        }
        if ($p['has_lost']) {
            $offers[] = ['offer' => 'Win-back proposal', 'when' => 'presented as a fresh package, not a reactive cut', 'note' => 'new scope and new price, framed as a restart'];
        }
        if (!$offers) {
            $offers[] = ['offer' => 'Nothing yet', 'when' => 'no pricing relationship is on record', 'note' => 'confirm they are a customer before offering anything'];
        }

        $avoid = [
            'A permanent percentage discount with nothing traded for it',
            'Matching a competitor quote line-for-line without renegotiating the scope',
            'A concession offered before you know what actually triggered the risk',
        ];

        $plain = ucfirst($p['name']) . ' — you can put on the table: '
            . collect($offers)->map(fn ($o) => $o['offer'] . ' (' . $o['when'] . ')')->implode('; ')
            . '. Do not offer: ' . implode('; ', $avoid) . '.';

        return $this->withAnswer($question, [
            'matched'  => true,
            'name'     => $p['name'],
            'company'  => $p['company'],
            'offers'   => $offers,
            'avoid'    => $avoid,
            'facts'    => $this->facts($p),
            'accounts' => [$this->accountRow($p)],
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'name' => $p['name'], 'company' => $p['company'],
            'allowed_offers' => $offers, 'do_not_offer' => $avoid, 'facts' => $this->facts($p),
        ], 'List what the rep may offer this specific customer as a short prose sentence or two, most appropriate first, then one line on what NOT to offer. Under 110 words.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q3 — What give-get should I ask for?
    // ─────────────────────────────────────────────────────────────────────────

    public function giveGetToAskFor(?string $name): array
    {
        $question = 'What give-get should I ask for' . ($name ? ' with ' . $name : '') . '?';
        $contact = $name ? $this->contacts->resolveContact($name) : null;
        if (!$contact) {
            return $this->noMatch($question, $name);
        }

        $p = $this->profile($contact);

        $gives = $p['is_customer']
            ? ['A time-boxed term discount', 'A temporary right-size / downgrade', 'A short billing pause']
            : ['Extended payment terms', 'A pilot extension', 'A one-off implementation credit'];

        $gets = match ($p['value_tier']) {
            'large' => ['A 2–3 year commitment', 'A public case study or logo rights', 'An executive reference call', 'A company-wide or multi-team rollout'],
            'mid'   => ['An annual (not monthly) commitment', 'A named reference we can use on sales calls', 'A quarterly business review booked in the calendar'],
            default => ['A written testimonial or G2 review', 'A warm referral introduction', 'A firm 12-month term'],
        };

        $plain = ucfirst($p['name']) . ' ($' . number_format($p['deal_value']) . ', ' . $p['value_tier'] . ' account) — '
            . 'if you give ' . lcfirst($gives[0]) . ', ask for ' . lcfirst($gets[0])
            . ' (or ' . lcfirst($gets[1] ?? $gets[0]) . '). '
            . 'No concession leaves the room without a get — otherwise the account learns that pushing back on price works.';

        return $this->withAnswer($question, [
            'matched'  => true,
            'name'     => $p['name'],
            'company'  => $p['company'],
            'gives'    => $gives,
            'gets'     => $gets,
            'facts'    => $this->facts($p),
            'accounts' => [$this->accountRow($p)],
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'name' => $p['name'], 'company' => $p['company'], 'value_tier' => $p['value_tier'],
            'things_you_can_give' => $gives, 'things_to_ask_for' => $gets, 'facts' => $this->facts($p),
        ], 'Pair one realistic give with the strongest get to ask for from this specific customer, in one or two sentences, then state the rule that every concession must be traded. Under 90 words.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CRM profile (crm_contacts + crm_deals only)
    // ─────────────────────────────────────────────────────────────────────────

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

        $dealValue = (float) $matched->sum('value');
        $displayName = trim($contact->first_name . ' ' . $contact->last_name);

        $tier = match (true) {
            $dealValue >= $maxValue * 0.5  => 'large',
            $dealValue >= $maxValue * 0.15 => 'mid',
            default                        => 'small',
        };

        return [
            'contact'       => $contact,
            'name'          => $displayName !== '' ? $displayName : ($company !== '' ? $company : (string) $contact->email),
            'company'       => $company ?: '—',
            'deal_count'    => $matched->count(),
            'deal_value'    => round($dealValue, 2),
            'won_value'     => round((float) $won->sum('value'), 2),
            'open_value'    => round((float) $open->sum('value'), 2),
            'is_customer'   => $won->isNotEmpty(),
            'has_open'      => $open->isNotEmpty(),
            'has_lost'      => $lost->isNotEmpty(),
            'primary_stage' => ($open->first() ?? $won->first() ?? $matched->first())?->stage ?? '—',
            'value_tier'    => $tier,
            'days_since'    => $contact->last_activity_at ? (int) $contact->last_activity_at->diffInDays(now()) : null,
        ];
    }

    private function ceiling(array $p): string
    {
        return match ($p['value_tier']) {
            'large' => '12–15%',
            'mid'   => '10–12%',
            default => 'up to 10%',
        };
    }

    private function facts(array $p): array
    {
        return [
            'is_paying_customer'  => $p['is_customer'],
            'deal_count'          => $p['deal_count'],
            'deal_value'          => $p['deal_value'],
            'open_deal_value'     => $p['open_value'],
            'primary_stage'       => $p['primary_stage'],
            'value_tier'          => $p['value_tier'],
            'days_since_activity' => $p['days_since'],
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

    private function noMatch(string $question, ?string $name): array
    {
        return [
            'question'    => $question,
            'matched'     => false,
            'answer'      => $name
                ? "I couldn't find a customer matching \"{$name}\". Try their full name or company."
                : 'Give me a customer name and I\'ll tell you what you can offer them.',
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
            . 'Answer using ONLY the JSON data provided as input — never invent a name, number, stage or offer that is not in it. '
            . 'Reply with plain prose sentences a colleague would say out loud. '
            . 'Do NOT return JSON, markdown, bullet points, headings, quotes or key/value pairs. '
            . $instruction;

        $prompt = "Question: {$question}\n\nData:\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $reply = trim($client->chat($system, $prompt, ['max_tokens' => 340, 'temperature' => 0.2]));

        if (Str::startsWith($reply, '{') && ($decoded = json_decode($reply, true)) !== null) {
            $reply = (string) (array_values($decoded)[0] ?? $reply);
        }

        return trim($reply);
    }
}
