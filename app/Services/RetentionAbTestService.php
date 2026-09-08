<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Answers the Customer Retention agent's "A/B test" questions — about testing
 * SAVE PLAYS, not marketing sends:
 *
 *   - Which save-play test is worth running?
 *   - Should I test discount vs no-discount?
 *   - How many at-risk accounts per arm do I need?
 *   - Call-first or email-first?
 *   - Is my holdout big enough?
 *   - All test ideas
 *
 * The only sample the retention team can test on is the at-risk book, so
 * every answer starts from a pool built out of the two synced CRM tables:
 *
 *   - crm_contacts → identity + last_activity_at
 *   - crm_deals    → value, stage, won / open / lost, close date
 *
 * Each at-risk account is bucketed by SITUATION (gone quiet after the win, a
 * deal stalling in the pipeline, or already lost) — that mix is what decides
 * which test is even worth running.
 *
 * There is no save-outcome table in the synced data, so this service is
 * honest that the numbers only support a DIRECTIONAL read, never statistical
 * significance, at the pool sizes a single client actually has. OpenAI
 * phrases the answer; a correct plain-text version is returned without a key.
 */
class RetentionAbTestService
{
    private const EARLY_STAGES = ['appointmentscheduled', 'qualifiedtobuy'];
    private const LATE_STAGES  = ['decisionmakerboughtin', 'presentationscheduled', 'contractsent'];

    /** Rough per-arm sample needed to detect a 50%→65% save-rate lift at 80% power. */
    private const ARM_FOR_SIGNIFICANCE = 170;

    // ─────────────────────────────────────────────────────────────────────────
    //  Q1 — Which save-play test is worth running?
    // ─────────────────────────────────────────────────────────────────────────

    public function whichTestWorthRunning(): array
    {
        $question = 'Which save-play test is worth running?';
        $pool = $this->pool();

        if ($pool['count'] < 4) {
            return $this->tiny($question, $pool);
        }

        $dominant = $pool['dominant'];
        $test = match ($dominant) {
            'silent_after_win' => [
                'Re-onboarding working session vs. a check-in email',
                'Most of the pool went quiet after buying — the question is whether a live session beats a nudge at getting them back to value.',
            ],
            'deal_stalling' => [
                'Name-the-blocker call vs. a written "here are the options" email',
                'The pool is mostly stalled deals — test whether a direct call surfaces the blocker faster than laying the options out in writing.',
            ],
            'lost' => [
                'A smaller re-proposal vs. a straight "what did we get wrong" feedback ask',
                'The pool is mostly already-lost accounts — test whether leading with a new offer beats leading with genuine curiosity.',
            ],
            default => [
                'Call-first vs. email-first sequencing',
                'The pool is a mix of situations, so test the most universal lever: whether the first touch is a call or an email.',
            ],
        };

        $per = intdiv($pool['count'], 2);
        $plain = "Run: {$test[0]}. {$test[1]} Split the {$pool['count']} at-risk accounts evenly — about {$per} per arm. "
            . "That is a directional read only; log every outcome so the signal compounds over quarters.";

        return $this->withAnswer($question, $pool, [
            'recommended_test' => $test[0],
            'why'              => $test[1],
            'per_arm'          => $per,
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'pool' => $pool['count'], 'situation_mix' => $pool['breakdown'],
            'recommended_test' => $test[0], 'why' => $test[1], 'per_arm' => $per,
        ], 'Name the one test worth running and why, then the split. Two or three sentences. Make clear it is directional, not significant.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q2 — Should I test discount vs no-discount?
    // ─────────────────────────────────────────────────────────────────────────

    public function discountVsNoDiscount(): array
    {
        $question = 'Should I test discount vs no-discount?';
        $pool = $this->pool();
        $priceRelevant = $pool['price_relevant'];

        if ($priceRelevant >= 4) {
            $per = intdiv($priceRelevant, 2);
            $plain = "Yes — {$priceRelevant} accounts in the pool are stalled where price is actually the question. "
                . "Arm A: a time-boxed ~10% tied to a longer commitment. Arm B: a value re-frame plus an exec call, no discount. "
                . "About {$per} per arm, directional only. Track save rate and the revenue you kept, not just whether they stayed.";
        } else {
            $plain = "Not yet — only {$priceRelevant} account" . ($priceRelevant === 1 ? '' : 's') . ' in the pool are stalled on price. '
                . 'Testing discount vs no-discount on that few is just noise. Default to no-discount, and log which saves genuinely needed one — '
                . 'that log is what tells you when a real test is worth setting up.';
        }

        return $this->withAnswer($question, $pool, [
            'price_relevant_accounts' => $priceRelevant,
            'recommendation'          => $priceRelevant >= 4 ? 'run_it' : 'not_yet',
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'pool' => $pool['count'], 'price_relevant_accounts' => $priceRelevant, 'situation_mix' => $pool['breakdown'],
        ], 'Answer yes or not-yet in the first sentence based on the price_relevant_accounts count, then how to split the arms or what to do instead.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q3 — How many at-risk accounts per arm do I need?
    // ─────────────────────────────────────────────────────────────────────────

    public function accountsPerArm(): array
    {
        $question = 'How many at-risk accounts per arm do I need?';
        $pool = $this->pool();

        $twoArm      = intdiv($pool['count'], 2);
        $withHoldout = intdiv((int) round($pool['count'] * 0.85), 2);
        $need        = self::ARM_FOR_SIGNIFICANCE;

        $plain = "Your testable at-risk pool is {$pool['count']} accounts. An even two-arm split is {$twoArm} per arm "
            . "({$withHoldout} per arm if you also hold 15% back). "
            . "For a statistically solid result you would need roughly {$need} per arm, so treat any winner here as a hypothesis to "
            . "re-run next quarter, not a proven result. Log every save outcome — that is how a small book earns a real answer over time.";

        return $this->withAnswer($question, $pool, [
            'per_arm_even'        => $twoArm,
            'per_arm_with_holdout' => $withHoldout,
            'per_arm_for_significance' => $need,
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'pool' => $pool['count'], 'per_arm_even' => $twoArm, 'per_arm_with_holdout' => $withHoldout,
            'per_arm_for_significance' => $need,
        ], 'Give the pool size and the per-arm number, then state plainly that this is directional not significant and why.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q4 — Call-first or email-first?
    // ─────────────────────────────────────────────────────────────────────────

    public function callFirstOrEmailFirst(): array
    {
        $question = 'Call-first or email-first?';
        $pool = $this->pool();

        $silentShare = $pool['count'] > 0 ? $pool['breakdown']['silent_after_win']['accounts'] / $pool['count'] : 0;

        $lean = $silentShare >= 0.4
            ? ['Call-first', 'Most of the pool is already ignoring email — a call is the only touch likely to land.']
            : ['Either works, so test it', 'The pool is responsive enough that email-first is viable — which makes this a fair thing to A/B.'];

        $per = intdiv($pool['count'], 2);
        $plain = "{$lean[0]}. {$lean[1]} Test the split: arm A gets a call within 24 hours then a recap email; "
            . "arm B gets the email first and a call only if there is no reply in 48 hours. About {$per} per arm — directional read, log the reply and save rates for both.";

        return $this->withAnswer($question, $pool, [
            'lean'    => $lean[0],
            'why'     => $lean[1],
            'per_arm' => $per,
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'pool' => $pool['count'], 'situation_mix' => $pool['breakdown'],
            'lean' => $lean[0], 'why' => $lean[1], 'per_arm' => $per,
        ], 'State the lean and why in one sentence, then describe the two arms and the split.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q5 — Is my holdout big enough?
    // ─────────────────────────────────────────────────────────────────────────

    public function holdoutBigEnough(): array
    {
        $question = 'Is my holdout big enough?';
        $pool = $this->pool();

        $holdout = (int) round($pool['count'] * 0.15);

        $plain = $holdout < 3
            ? "No — a 15% holdout of a {$pool['count']}-account pool is only {$holdout} accounts, which tells you nothing. "
              . 'Skip the holdout at this size and use last quarter\'s save rate as your baseline instead.'
            : "A 15% holdout is {$holdout} accounts left untouched. Workable as a rough baseline, but with only {$pool['count']} accounts "
              . 'in total the margin of error is wide — read a borderline result as inconclusive, not as a loss.';

        return $this->withAnswer($question, $pool, [
            'holdout_15pct' => $holdout,
            'viable'        => $holdout >= 3,
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'pool' => $pool['count'], 'holdout_15pct' => $holdout, 'viable' => $holdout >= 3,
        ], 'Answer yes or no in the first sentence based on whether the holdout is viable, then what to do.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Q6 — All test ideas
    // ─────────────────────────────────────────────────────────────────────────

    public function allTestIdeas(): array
    {
        $question = 'All save-play test ideas';
        $pool = $this->pool();

        $ideas = [
            ['lever' => 'Sequencing', 'test' => 'Call-first vs. email-first as the opening touch'],
            ['lever' => 'Channel',    'test' => 'Phone vs. WhatsApp vs. email for the check-in'],
            ['lever' => 'Offer',      'test' => 'Time-boxed discount vs. value re-frame vs. downgrade-to-save'],
            ['lever' => 'Timing',     'test' => 'Same-day vs. next-day first touch after the risk flag'],
            ['lever' => 'Messenger',  'test' => 'Rep-led vs. executive-sponsor-led outreach on the top accounts'],
            ['lever' => 'The ask',    'test' => 'Soft "how\'s it going" vs. direct "what would make you stay"'],
        ];

        $plain = "Levers you can test on the save play, run one at a time:\n"
            . collect($ideas)->map(fn ($i) => "- {$i['lever']}: {$i['test']}")->implode("\n")
            . "\n\nYour at-risk pool is {$pool['count']} accounts — enough for a directional read on one test at a time, not several at once.";

        return $this->withAnswer($question, $pool, [
            'ideas' => $ideas,
        ], $plain, fn (OpenAiClient $c) => $this->ask($c, $question, [
            'pool' => $pool['count'], 'situation_mix' => $pool['breakdown'], 'ideas' => $ideas,
        ], 'List the test ideas as short lines, then one closing line on running them one at a time given the pool size. Line breaks are fine, no markdown symbols.'));
    }

    /**
     * Lightweight pool figures for the A/B test dashboard pane — the same
     * at-risk pool the prompts compute, so the pane and the answers agree.
     *
     * @return array{count:int, value:float, breakdown:array}
     */
    public function poolSummary(): array
    {
        $pool = $this->pool();

        return [
            'count'     => $pool['count'],
            'value'     => round(array_sum(array_column($pool['breakdown'], 'value')), 2),
            'breakdown' => $pool['breakdown'],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  The at-risk pool (crm_contacts + crm_deals only)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{count:int, dominant:?string, breakdown:array, accounts:array, price_relevant:int}
     */
    private function pool(): array
    {
        $deals = CrmDeal::all();

        $scored = CrmContact::query()
            ->whereNotNull('company')->where('company', '!=', '')
            ->orderByDesc('last_activity_at')
            ->get()
            ->unique(fn (CrmContact $c) => Str::lower($c->company))
            ->map(fn (CrmContact $c) => $this->situationFor($c, $deals))
            ->filter(fn (array $s) => in_array($s['situation'], ['silent_after_win', 'deal_stalling', 'lost'], true))
            ->values();

        $labels = [
            'silent_after_win' => 'gone quiet after the win',
            'deal_stalling'    => 'deal stalling in the pipeline',
            'lost'             => 'already lost',
        ];

        $breakdown = [];
        foreach (array_keys($labels) as $key) {
            $group = $scored->where('situation', $key);
            $breakdown[$key] = [
                'label'    => $labels[$key],
                'accounts' => $group->count(),
                'value'    => round((float) $group->sum('deal_value'), 2),
            ];
        }

        $dominant = collect($breakdown)
            ->sortByDesc(fn ($b) => [$b['accounts'], $b['value']])
            ->keys()
            ->first(fn ($k) => $breakdown[$k]['accounts'] > 0);

        return [
            'count'          => $scored->count(),
            'dominant'       => $dominant,
            'breakdown'      => $breakdown,
            'price_relevant' => $scored->where('situation', 'deal_stalling')->where('price_relevant', true)->count(),
            'accounts'       => $scored->sortByDesc('deal_value')->take(12)->map(fn ($s) => $s['row'])->values()->all(),
        ];
    }

    private function situationFor(CrmContact $contact, Collection $deals): array
    {
        $company = trim((string) $contact->company);
        $matched = $deals->filter(fn (CrmDeal $d) => Str::startsWith((string) $d->name, $company))->values();

        $won  = $matched->filter(fn (CrmDeal $d) => $d->status === 'won' || $d->stage === 'closedwon')->values();
        $lost = $matched->filter(fn (CrmDeal $d) => $d->status === 'lost' || Str::contains((string) $d->stage, 'lost'))->values();
        $open = $matched->filter(fn (CrmDeal $d) => !$won->contains($d) && !$lost->contains($d))->values();

        $now = now();
        $overdue  = $open->filter(fn (CrmDeal $d) => $d->close_date && $d->close_date->lt($now));
        $slipping = $open->filter(fn (CrmDeal $d) => $d->close_date
            && $d->close_date->between($now, $now->copy()->addDays(21))
            && in_array($d->stage, self::EARLY_STAGES, true));

        $daysSince = $contact->last_activity_at ? (int) $contact->last_activity_at->diffInDays($now) : null;
        $dealValue = (float) $matched->sum('value');
        $stage     = ($open->first() ?? $won->first() ?? $matched->first())?->stage ?? '—';

        $situation = match (true) {
            $matched->isEmpty()                                              => 'no_relationship',
            $lost->isNotEmpty() && $won->isEmpty()                           => 'lost',
            $won->isNotEmpty() && $daysSince !== null && $daysSince > 45      => 'silent_after_win',
            $open->isNotEmpty() && ($overdue->isNotEmpty() || $slipping->isNotEmpty()) => 'deal_stalling',
            default                                                          => 'healthy',
        };

        $priceRelevant = $open->contains(fn (CrmDeal $d) => in_array($d->stage, self::LATE_STAGES, true));

        $name = trim($contact->first_name . ' ' . $contact->last_name);

        $situationLabel = [
            'silent_after_win' => 'Gone quiet after win',
            'deal_stalling'    => 'Deal stalling',
            'lost'             => 'Already lost',
            'healthy'          => 'Healthy',
            'no_relationship'  => 'No deal',
        ][$situation] ?? $situation;

        return [
            'situation'      => $situation,
            'deal_value'     => round($dealValue, 2),
            'price_relevant' => $priceRelevant,
            'row' => [
                'name'                => $name !== '' ? $name : $company,
                'company'             => $company,
                'situation'           => $situationLabel,
                'deal_value'          => round($dealValue, 2),
                'stage'               => $stage,
                'days_since_activity' => $daysSince ?? '—',
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Response shaping
    // ─────────────────────────────────────────────────────────────────────────

    private function tiny(string $question, array $pool): array
    {
        $answer = "Your testable at-risk pool is only {$pool['count']} account" . ($pool['count'] === 1 ? '' : 's')
            . " right now — too small for any A/B test. Run one save play as well as you can, log every outcome "
            . '(saved / lost / and what you tried), and revisit this once you have 20 or more at-risk accounts to split.';

        return [
            'question'  => $question,
            'matched'   => true,
            'answer'    => $answer,
            'ai_used'   => false,
            'pool'      => $pool['count'],
            'breakdown' => $pool['breakdown'],
            'accounts'  => $pool['accounts'],
        ];
    }

    private function withAnswer(string $question, array $pool, array $payload, string $plain, callable $askAi): array
    {
        $base = [
            'question'  => $question,
            'matched'   => true,
            'pool'      => $pool['count'],
            'breakdown' => $pool['breakdown'],
            'accounts'  => $pool['accounts'],
        ] + $payload;

        if ($pool['count'] === 0) {
            return $base + [
                'answer'  => 'There are no at-risk accounts in the synced CRM data right now, so there is nothing to A/B test. This will populate as deals stall or customers go quiet.',
                'ai_used' => false,
            ];
        }

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
            . 'Use ONLY the JSON data provided as input — never invent a number or a bucket that is not in it. '
            . 'You are advising on A/B testing SAVE PLAYS on a small at-risk book, so never imply statistical significance is achievable at these sizes. '
            . 'Plain text only: no markdown, no headings, no bullet characters, no JSON. Under 110 words. '
            . $instruction;

        $prompt = "Question: {$question}\n\nData:\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $reply = trim($client->chat($system, $prompt, ['max_tokens' => 360, 'temperature' => 0.3]));

        if (Str::startsWith($reply, '{') && ($decoded = json_decode($reply, true)) !== null) {
            $reply = (string) (array_values($decoded)[0] ?? $reply);
        }

        return trim($reply);
    }
}
