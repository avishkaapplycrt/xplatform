<?php

namespace App\Services;

use App\Models\BrevoDeliveredRecipient;
use App\Models\EmailLog;
use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;

/**
 * Answers the Marketing agent's A/B test questions about email format —
 * subject line, send time, and the full test backlog — from two real
 * tables:
 *   - email_logs           → one row per send, with subject, sent_at,
 *                            opened_at, clicked_at and device_type
 *   - email_logs_providers → delivery/unsubscribe signal from the
 *                            connected ESP (Brevo), independent of what
 *                            this app itself sent
 *
 * Every recommendation is read off actual historical performance in these
 * two tables, not a textbook assumption:
 *   - subject line   → every sent subject is classified as "urgency" or
 *                      "standard" framing by keyword, then each bucket's
 *                      real open rate decides which style is worth testing
 *   - send time      → every send's hour is bucketed (morning / midday /
 *                      afternoon / evening / night), and the bucket with
 *                      the best real open rate (given enough volume) is
 *                      the recommended send window
 *   - all test ideas → click-through of opens, mobile share of opens, and
 *                      the ESP's own unsubscribe rate are compared against
 *                      simple thresholds to decide which tests are worth
 *                      running right now, on top of the subject-line and
 *                      send-time signal above
 *
 * The analysis is pure arithmetic — no AI required. An OpenAI key
 * (config('services.openai')) is only used, when configured, to turn the
 * numbers into a short written recommendation; without a key this still
 * returns a correct, data-grounded plain-text answer.
 */
class MarketingEmailTestService
{
    private const URGENCY_KEYWORDS = [
        'limited', 'last chance', 'expires', 'flash sale', 'do not miss', "don't miss",
        'hurry', 'ending soon', 'deadline', 'at risk', 'only',
    ];

    private const HOUR_BUCKETS = [
        ['label' => 'Night (12am–6am)', 'from' => 0, 'to' => 6],
        ['label' => 'Morning (6am–11am)', 'from' => 6, 'to' => 11],
        ['label' => 'Midday (11am–2pm)', 'from' => 11, 'to' => 14],
        ['label' => 'Afternoon (2pm–5pm)', 'from' => 14, 'to' => 17],
        ['label' => 'Evening (5pm–9pm)', 'from' => 17, 'to' => 21],
        ['label' => 'Night (9pm–12am)', 'from' => 21, 'to' => 24],
    ];

    private const RELIABLE_SEND_VOLUME = 20;
    private const CLICK_THROUGH_LOW = 40.0;
    private const MOBILE_SHARE_HIGH = 40.0;
    private const UNSUB_RATE_HIGH = 10.0;

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool, detail: array}
     */
    public function subjectLineTest(): array
    {
        $result = $this->computeSubjectLineTest();

        $answer = $this->buildAnswer(
            $result['ranked'],
            'Which subject-line test is worth running on MQL → Sales?',
            $result['empty_message'],
            fn () => $result['plain_summary'],
            $result['context']
        );

        // The emails behind each bucket never go to OpenAI — they're only
        // for the "view the accounts behind this" drill-down in the UI.
        return $answer + ['detail' => $result['detail'] ?? []];
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool, detail: array}
     */
    public function touch1SendTime(): array
    {
        $result = $this->computeSendTime();

        $answer = $this->buildAnswer(
            $result['ranked'],
            'When should MQL → Sales receive touch 1?',
            $result['empty_message'],
            fn () => $result['plain_summary'],
            $result['context']
        );

        return $answer + ['detail' => $result['detail'] ?? []];
    }

    /**
     * @return array{ranked: array<int, array>, answer: string, ai_used: bool}
     */
    public function allTestIdeas(): array
    {
        $sent = EmailLog::count();

        if ($sent === 0) {
            return ['ranked' => [], 'answer' => 'No sent emails are on file yet to base test ideas on.', 'ai_used' => false];
        }

        $opened = EmailLog::whereNotNull('opened_at')->count();
        $clicked = EmailLog::whereNotNull('clicked_at')->count();
        $clickThroughOfOpens = $opened > 0 ? round($clicked / $opened * 100, 1) : 0.0;

        $mobileOpens = EmailLog::whereNotNull('opened_at')->where('device_type', 'mobile')->count();
        $mobileShareOfOpens = $opened > 0 ? round($mobileOpens / $opened * 100, 1) : 0.0;

        $delivered = BrevoDeliveredRecipient::whereNotNull('delivered_at')->count();
        $unsubscribed = BrevoDeliveredRecipient::whereNotNull('unsubscribed_at')->count();
        $unsubRate = $delivered > 0 ? round($unsubscribed / $delivered * 100, 1) : null;

        $subject = $this->computeSubjectLineTest();
        $sendTime = $this->computeSendTime();

        $ideas = [
            ['idea' => 'Subject line framing (urgency vs standard)', 'metric' => 'open rate', 'why' => $subject['plain_summary']],
            ['idea' => 'Send time window', 'metric' => 'open rate', 'why' => $sendTime['plain_summary']],
        ];

        if ($clickThroughOfOpens < self::CLICK_THROUGH_LOW) {
            $ideas[] = [
                'idea' => 'CTA framing inside the email',
                'metric' => 'click-through of opens',
                'why' => "Only {$clickThroughOfOpens}% of opens click through ({$clicked}/{$opened}) — below the " . self::CLICK_THROUGH_LOW . '% bar, so the open is working but the ask inside isn\'t.',
            ];
        }

        if ($mobileShareOfOpens >= self::MOBILE_SHARE_HIGH) {
            $ideas[] = [
                'idea' => 'Mobile-first subject length and layout',
                'metric' => 'device share of opens',
                'why' => "{$mobileShareOfOpens}% of opens are on mobile ({$mobileOpens}/{$opened}) — high enough that a mobile-truncated subject or layout could be costing opens/clicks.",
            ];
        }

        if ($unsubRate !== null && $unsubRate >= self::UNSUB_RATE_HIGH) {
            $ideas[] = [
                'idea' => 'Send frequency / cadence',
                'metric' => 'unsubscribe rate',
                'why' => "{$unsubRate}% of delivered contacts unsubscribed ({$unsubscribed}/{$delivered}) — worth testing a lower send frequency before volume grows.",
            ];
        }

        $plainSummary = "All test ideas worth running for MQL → Sales, based on current email performance:\n"
            . collect($ideas)->map(fn (array $i, $idx) => ($idx + 1) . ". {$i['idea']} — {$i['why']}")->implode("\n");

        return $this->buildAnswer(
            $ideas,
            'All test ideas for MQL → Sales',
            'No sent emails are on file yet to base test ideas on.',
            fn () => $plainSummary,
            [
                'sent' => $sent, 'opened' => $opened, 'clicked' => $clicked,
                'click_through_of_opens' => $clickThroughOfOpens, 'mobile_share_of_opens' => $mobileShareOfOpens,
                'unsubscribe_rate' => $unsubRate,
            ]
        );
    }

    /**
     * @return array{ranked: array<int, array>, plain_summary: string, empty_message: string, context: array}
     */
    private function computeSubjectLineTest(): array
    {
        $emptyMessage = 'No sent emails with a subject line are on file yet to test from.';
        $rows = EmailLog::query()->whereNotNull('subject')->get(['subject', 'sent_at', 'opened_at']);

        if ($rows->isEmpty()) {
            return ['ranked' => [], 'plain_summary' => $emptyMessage, 'empty_message' => $emptyMessage, 'context' => [], 'detail' => []];
        }

        $buckets = ['urgency' => ['sent' => 0, 'opened' => 0], 'standard' => ['sent' => 0, 'opened' => 0]];
        $emailsByBucket = ['urgency' => [], 'standard' => []];

        foreach ($rows as $row) {
            $key = $this->isUrgencySubject((string) $row->subject) ? 'urgency' : 'standard';
            $buckets[$key]['sent']++;
            $opened = $row->opened_at !== null;
            if ($opened) {
                $buckets[$key]['opened']++;
            }
            $emailsByBucket[$key][] = [
                'subject' => $row->subject,
                'sent_at' => $row->sent_at?->format('Y-m-d H:i'),
                'opened' => $opened,
            ];
        }

        $ranked = [];
        foreach ($buckets as $style => $d) {
            $ranked[] = [
                'style' => $style,
                'sent' => $d['sent'],
                'opened' => $d['opened'],
                'open_rate' => $d['sent'] > 0 ? round($d['opened'] / $d['sent'] * 100, 1) : 0.0,
            ];
        }
        usort($ranked, fn ($a, $b) => $b['open_rate'] <=> $a['open_rate']);

        $winner = $ranked[0];
        $loser = $ranked[1];
        $gap = round($winner['open_rate'] - $loser['open_rate'], 1);

        $plainSummary = "\"{$winner['style']}\"-style subjects open at {$winner['open_rate']}% ({$winner['opened']}/{$winner['sent']} sent) vs "
            . "{$loser['open_rate']}% ({$loser['opened']}/{$loser['sent']} sent) for \"{$loser['style']}\"-style — a {$gap}-point gap. "
            . "Worth testing more \"{$winner['style']}\" subjects against the current \"{$loser['style']}\" mix.";

        return [
            'ranked' => $ranked,
            'plain_summary' => $plainSummary,
            'empty_message' => $emptyMessage,
            'context' => ['winner' => $winner['style'], 'gap_points' => $gap],
            'detail' => $emailsByBucket,
        ];
    }

    /**
     * @return array{ranked: array<int, array>, plain_summary: string, empty_message: string, context: array}
     */
    private function computeSendTime(): array
    {
        $emptyMessage = 'No sent emails are on file yet to read a best send time from.';
        $rows = EmailLog::query()->whereNotNull('sent_at')->get(['subject', 'sent_at', 'opened_at']);

        if ($rows->isEmpty()) {
            return ['ranked' => [], 'plain_summary' => $emptyMessage, 'empty_message' => $emptyMessage, 'context' => [], 'detail' => []];
        }

        $detail = [];

        $ranked = collect(self::HOUR_BUCKETS)->map(function (array $bucket) use ($rows, &$detail) {
            $inBucket = $rows->filter(function ($row) use ($bucket) {
                $hour = (int) $row->sent_at->format('G');
                return $hour >= $bucket['from'] && $hour < $bucket['to'];
            });

            $sent = $inBucket->count();
            $opened = $inBucket->filter(fn ($row) => $row->opened_at !== null)->count();

            $detail[$bucket['label']] = $inBucket->map(fn ($row) => [
                'subject' => $row->subject,
                'sent_at' => $row->sent_at?->format('Y-m-d H:i'),
                'opened' => $row->opened_at !== null,
            ])->values()->all();

            return [
                'window' => $bucket['label'],
                'sent' => $sent,
                'opened' => $opened,
                'open_rate' => $sent > 0 ? round($opened / $sent * 100, 1) : 0.0,
                'reliable' => $sent >= self::RELIABLE_SEND_VOLUME,
            ];
        })->filter(fn (array $row) => $row['sent'] > 0)->values();

        if ($ranked->isEmpty()) {
            return ['ranked' => [], 'plain_summary' => $emptyMessage, 'empty_message' => $emptyMessage, 'context' => [], 'detail' => []];
        }

        $reliableOnly = $ranked->where('reliable', true);
        $candidates = $reliableOnly->isNotEmpty() ? $reliableOnly : $ranked;
        $best = $candidates->sortByDesc('open_rate')->first();

        $plainSummary = "Send touch 1 in the {$best['window']} window — {$best['open_rate']}% open rate ({$best['opened']}/{$best['sent']} sent), the best of the windows on file"
            . ($best['reliable'] ? '.' : ', though volume there is still thin — treat this as directional.');

        return [
            'ranked' => $ranked->sortByDesc('open_rate')->values()->all(),
            'plain_summary' => $plainSummary,
            'empty_message' => $emptyMessage,
            'context' => ['best_window' => $best['window'], 'reliable_volume' => self::RELIABLE_SEND_VOLUME],
            'detail' => $detail,
        ];
    }

    private function isUrgencySubject(string $subject): bool
    {
        $subject = mb_strtolower($subject);

        foreach (self::URGENCY_KEYWORDS as $word) {
            if (str_contains($subject, $word)) {
                return true;
            }
        }

        return false;
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
                // Fall through to the plain-text answer below — a broken AI
                // call should never take down a page that has real data.
            }
        }

        return ['ranked' => $ranked, 'answer' => $plainSummary($ranked), 'ai_used' => false];
    }

    private function askOpenAi(OpenAiClient $client, string $question, array $ranked, array $context): string
    {
        $system = 'You are the Marketing copilot inside a B2B analytics platform, advising on email A/B tests for '
            . 'the "MQL → Sales" hand-off pool. Answer using ONLY the JSON data provided — never invent a name, '
            . 'number, or statistic that is not in it. Output PLAIN TEXT only, never JSON or markdown — a short, '
            . 'direct paragraph (or a short numbered list only for the "all test ideas" question). Be brief and '
            . 'concrete, in plain English, under 150 words.';

        $prompt = "Question: {$question}\n\nContext:\n" . json_encode($context, JSON_PRETTY_PRINT)
            . "\n\nData:\n" . json_encode($ranked, JSON_PRETTY_PRINT);

        return $client->chat($system, $prompt, ['max_tokens' => 350]);
    }
}
