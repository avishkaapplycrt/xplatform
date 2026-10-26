<?php

namespace App\Services\MockMaster;

use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use Illuminate\Support\Carbon;

/**
 * Answers free-typed questions in Mock Master Helper's "Ask Mira" chat box,
 * grounded in a real snapshot of the mm_* tables (see
 * MockMasterDataService::chatSnapshot()) — never this client's own CRM/email
 * data. Same real-data-first, narrate-second pattern as SalesChatService:
 * the data is computed first, OpenAI is only ever asked to phrase an answer
 * from it, never to invent or estimate a number itself.
 */
class MockMasterChatService
{
    private ?array $periodResults = null;

    public function __construct(
        private readonly MockMasterDataService $data,
        private readonly OpenAiClient $client,
    ) {
    }

    /**
     * @return array{answer: string, ai_used: bool}
     */
    public function answer(string $agent, string $question): array
    {
        $question = trim($question);

        if ($question === '') {
            return [
                'answer' => 'Ask me something about your Mock Master students — for example, who is most at risk of churning, or how mock test scores are trending.',
                'ai_used' => false,
            ];
        }

        if (!$this->client->isConfigured()) {
            return [
                'answer' => "The AI assistant isn't configured yet — try one of the suggested prompts above.",
                'ai_used' => false,
            ];
        }

        try {
            $answer = $this->ask($agent, $question);
            return ['answer' => $answer, 'ai_used' => true, 'results' => $this->periodResults];
        } catch (OpenAiException $e) {
            report($e);
            // A broken AI call should never take down a chat that has real
            // data behind it — surface a plain retry message instead.
            return [
                'answer' => "I couldn't reach the AI just now — try again in a moment, or use one of the suggested prompts above.",
                'ai_used' => false,
            ];
        }
    }

    private function ask(string $agent, string $question): string
    {
        $snapshot = $this->data->chatSnapshot();

        // The aggregated snapshot above deliberately excludes the full 58k+
        // student directory — too large to send on every question. If the
        // question looks like it names a specific person, run a real,
        // targeted search instead of leaving the AI with nothing to match.
        $matchedStudents = $this->data->searchStudents($this->extractNameTerms($question));
        if (!empty($matchedStudents)) {
            $snapshot['matched_students_for_this_question'] = $matchedStudents;
        }

        $period = $this->extractPeriod($question);
        if ($period !== null) {
            $requested = $this->extractRequestedCount($question);
            $snapshot['paid_payments_for_this_period'] = $this->data->paidPaymentsBetween(
                $period['start'], $period['end'], $requested ?? 200
            );
            $snapshot['paid_payments_for_this_period']['requested_count'] = $requested;
            $snapshot['paid_payments_for_this_period']['period_label'] = $period['label'];
            $this->periodResults = $snapshot['paid_payments_for_this_period'];
        }

        $persona = match ($agent) {
            'sales' => 'You are the Mock Master Sales copilot — help the rep decide who to call, nurture, or close.',
            'retention' => 'You are the Mock Master Retention copilot — help the team decide who to save first and why.',
            default => 'You are the Mock Master Marketing copilot — help the team decide who to target and what is working.',
        };

        $system = $persona . ' '
            . 'Answer using ONLY the data provided as JSON below — never invent a student name, number, or fact that isn\'t in it. '
            . 'The data covers real Mock Master platform tables: students, purchases, payments, packages, coupon usage, '
            . 'mock test results and activity logs, login activity, meetings, feedback, notifications, and scheduled emails. '
            . 'If the question names a specific person, check "matched_students_for_this_question" first — it is a real, '
            . 'targeted lookup for that name/email, separate from the aggregated stats elsewhere in the data. '
            . 'If "paid_payments_for_this_period" is present, it is the authoritative answer for the date window in the question: '
            . 'paid_payments_count, total_paid_amount, by_product, and payments_list (each with paid_on, student, email, product, amount) '
            . 'are all real payments with status = paid, dated by their payment date. For period questions, reply with ONE short sentence giving only the paid_payments_count and total_paid_amount for the period — do NOT list products, individual students, emails, or payments, and do not add any other sentences; the user sees the full details via a button under the answer. '
            . 'If that key is absent or empty and the question needs an individual student\'s details, say the search found '
            . 'no matching student rather than guessing. For everything else, if the data doesn\'t contain what\'s needed to '
            . 'answer precisely, say so plainly. Be brief and concrete — name specific students/numbers where relevant, '
            . 'in plain English, under 130 words.';

        $prompt = "Question: {$question}\n\ndata:\n" . json_encode($snapshot, JSON_PRETTY_PRINT);

        return $this->client->chat($system, $prompt, ['max_tokens' => 350]);
    }

    /**
     * A number the user explicitly asked for alongside the list — "5 paid
     * users", "top 10 payments" — or null when they didn't ask for one.
     */
    private function extractRequestedCount(string $question): ?int
    {
        if (preg_match('/\b(?:top|first|show|give|list)?\s*(\d{1,3})\s+(?:paid|payments?|subscriptions?|users?|students?|customers?|results?|people|names?)\b/i', $question, $m)) {
            return max(1, (int) $m[1]);
        }
        return null;
    }

    /**
     * Turns the date wording in a free-typed question into a real window.
     * Covers relative ("last month", "last two months", "past 3 weeks",
     * "previous quarter", "this year", "ytd", "today", "yesterday"),
     * named ("september 2026", "sept 26", "in 2026"), numeric ("2026-09",
     * "09/2026", "01/09/2026") and ranged ("from 1 sept to 15 sept",
     * "between 2026-08-01 and 2026-09-30") phrasings. Returns null if the
     * question names no period at all.
     *
     * @return array{start: Carbon, end: Carbon, label: string}|null
     */
    private function extractPeriod(string $question): ?array
    {
        $q = ' ' . trim(preg_replace('/\s+/', ' ', mb_strtolower(str_replace(',', ' ', $question)))) . ' ';
        $now = now();

        $range = $this->extractExplicitRange($q);
        if ($range !== null) {
            return $range;
        }

        $units = '(days?|weeks?|months?|quarters?|years?)';
        $numWord = '(\d{1,3}|' . implode('|', array_keys(self::NUMBER_WORDS)) . ')';

        if (preg_match('/\b(?:last|past|previous|prior|the last|the past)\s+' . $numWord . '\s+' . $units . '\b/', $q, $m)) {
            $n = $this->toNumber($m[1]);
            return $this->lastNUnits($n, $m[2], $now);
        }

        if (preg_match('/\b(today|yesterday)\b/', $q, $m)) {
            $d = $m[1] === 'today' ? $now->copy() : $now->copy()->subDay();
            return ['start' => $d->copy()->startOfDay(), 'end' => $d->copy()->endOfDay(), 'label' => $m[1] === 'today' ? 'today' : 'yesterday'];
        }

        if (preg_match('/\b(?:this|current)\s+(week|month|quarter|year)\b/', $q, $m)) {
            return $this->currentUnit($m[1], $now);
        }
        if (preg_match('/\b(?:last|previous|prior)\s+(week|month|quarter|year)\b/', $q, $m)) {
            return $this->previousUnit($m[1], $now);
        }
        if (preg_match('/\b(year to date|ytd)\b/', $q)) {
            return ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfDay(), 'label' => 'year to date (' . $now->format('Y') . ')'];
        }

        $monthPattern = implode('|', array_keys(self::MONTHS));
        if (preg_match('/\b(' . $monthPattern . ')\.?\s*(\d{4}|\d{2})?\b/', $q, $m)) {
            $monthNum = self::MONTHS[$m[1]];
            if ($m[1] === 'may' && empty($m[2])) {
                return null;
            }
            $year = $this->fullYear($m[2] ?? null, $now);
            $start = Carbon::create($year, $monthNum, 1)->startOfMonth();
            return ['start' => $start, 'end' => $start->copy()->endOfMonth(), 'label' => $start->format('F Y')];
        }

        if (preg_match('/\b(\d{4})-(\d{1,2})\b/', $q, $m) || preg_match('/\b(\d{1,2})\/(\d{4})\b/', $q, $m)) {
            [$year, $monthNum] = str_contains($m[0], '/') ? [(int) $m[2], (int) $m[1]] : [(int) $m[1], (int) $m[2]];
            if ($monthNum >= 1 && $monthNum <= 12) {
                $start = Carbon::create($year, $monthNum, 1)->startOfMonth();
                return ['start' => $start, 'end' => $start->copy()->endOfMonth(), 'label' => $start->format('F Y')];
            }
        }

        if (preg_match('/\b(?:in|during|for|of)\s+(\d{4})\b/', $q, $m)) {
            $year = Carbon::create((int) $m[1], 1, 1);
            return ['start' => $year->copy()->startOfYear(), 'end' => $year->copy()->endOfYear(), 'label' => $m[1]];
        }

        return null;
    }

    private const MONTHS = [
        'january' => 1, 'jan' => 1, 'february' => 2, 'feb' => 2, 'march' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4, 'may' => 5, 'june' => 6, 'jun' => 6, 'july' => 7, 'jul' => 7,
        'august' => 8, 'aug' => 8, 'september' => 9, 'sept' => 9, 'sep' => 9,
        'october' => 10, 'oct' => 10, 'november' => 11, 'nov' => 11, 'december' => 12, 'dec' => 12,
    ];

    private const NUMBER_WORDS = [
        'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6,
        'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10, 'eleven' => 11, 'twelve' => 12,
        'a' => 1, 'an' => 1,
    ];

    private function toNumber(string $token): int
    {
        return ctype_digit($token) ? (int) $token : (self::NUMBER_WORDS[$token] ?? 1);
    }

    private function fullYear(?string $token, Carbon $now): int
    {
        if ($token === null || $token === '') {
            return (int) $now->format('Y');
        }
        return strlen($token) === 2 ? 2000 + (int) $token : (int) $token;
    }

    /** "last N months" = the N whole calendar months before this one. */
    private function lastNUnits(int $n, string $unit, Carbon $now): array
    {
        $unit = rtrim($unit, 's');
        if (in_array($unit, ['month', 'quarter', 'year'], true)) {
            $months = match ($unit) { 'quarter' => $n * 3, 'year' => $n * 12, default => $n };
            $start = $now->copy()->subMonthsNoOverflow($months)->startOfMonth();
            $end = $now->copy()->subMonthNoOverflow()->endOfMonth();
            if ($n === 1 && $unit === 'month') {
                return ['start' => $start, 'end' => $end, 'label' => 'last month (' . $end->format('F Y') . ')'];
            }
            return ['start' => $start, 'end' => $end, 'label' => 'last ' . $n . ' ' . $unit . (($n === 1) ? '' : 's') . ' (' . $start->format('M Y') . ' – ' . $end->format('M Y') . ')'];
        }

        $days = $unit === 'week' ? $n * 7 : $n;
        return ['start' => $now->copy()->subDays($days)->startOfDay(), 'end' => $now->copy()->endOfDay(), 'label' => 'last ' . $n . ' ' . $unit . 's'];
    }

    private function currentUnit(string $unit, Carbon $now): array
    {
        return match ($unit) {
            'week' => ['start' => $now->copy()->startOfWeek(), 'end' => $now->copy()->endOfDay(), 'label' => 'this week'],
            'month' => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfDay(), 'label' => 'this month (' . $now->format('F Y') . ')'],
            'quarter' => ['start' => $now->copy()->startOfQuarter(), 'end' => $now->copy()->endOfDay(), 'label' => 'this quarter'],
            default => ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfDay(), 'label' => 'this year (' . $now->format('Y') . ')'],
        };
    }

    private function previousUnit(string $unit, Carbon $now): array
    {
        return match ($unit) {
            'week' => ['start' => $now->copy()->subWeek()->startOfWeek(), 'end' => $now->copy()->subWeek()->endOfWeek(), 'label' => 'last week'],
            'month' => $this->lastNUnits(1, 'month', $now),
            'quarter' => ['start' => $now->copy()->subQuarter()->startOfQuarter(), 'end' => $now->copy()->subQuarter()->endOfQuarter(), 'label' => 'last quarter'],
            default => ['start' => $now->copy()->subYear()->startOfYear(), 'end' => $now->copy()->subYear()->endOfYear(), 'label' => 'last year (' . $now->copy()->subYear()->format('Y') . ')'],
        };
    }

    /** "from 1 sept to 15 sept 2026" / "between 2026-08-01 and 2026-09-30". */
    private function extractExplicitRange(string $q): ?array
    {
        $datePart = '(\d{4}-\d{1,2}-\d{1,2}|\d{1,2}\/\d{1,2}\/\d{4}|\d{1,2}\s+(?:' . implode('|', array_keys(self::MONTHS)) . ')(?:\s+\d{4})?|(?:' . implode('|', array_keys(self::MONTHS)) . ')\s+\d{1,2}(?:,?\s*\d{4})?)';
        if (!preg_match('/\b(?:from|between)\s+' . $datePart . '\s+(?:to|and|until|-)\s+' . $datePart . '/', $q, $m)) {
            return null;
        }
        try {
            $start = Carbon::parse($m[1])->startOfDay();
            $end = Carbon::parse($m[2])->endOfDay();
        } catch (\Throwable $e) {
            return null;
        }
        if ($end->lt($start)) {
            return null;
        }
        return ['start' => $start, 'end' => $end, 'label' => $start->format('d M Y') . ' – ' . $end->format('d M Y')];
    }

    /**
     * Pulls out likely name/email tokens from a free-typed question — e.g.
     * "tell me the last name of Bharati" -> ["Bharati"], "Samina's email" ->
     * ["Samina"]. Deliberately simple (capitalised words, plus anything
     * containing "@"), not NLP — good enough to catch the common "who is
     * X" / "X's email" phrasing this chat actually receives.
     */
    private function extractNameTerms(string $question): array
    {
        $terms = [];

        if (preg_match_all('/[\w.+-]+@[\w-]+\.[\w.-]+/', $question, $emailMatches)) {
            $terms = array_merge($terms, $emailMatches[0]);
        }

        $words = preg_split('/[^\p{L}\']+/u', $question, -1, PREG_SPLIT_NO_EMPTY);
        $stopwords = ['I', 'The', 'What', 'Who', 'Which', 'Give', 'Tell', 'Send', 'Me', 'Is', 'Are', 'Do', 'Does'];

        foreach ($words as $word) {
            $bare = rtrim($word, "'s");
            if (mb_strlen($bare) >= 3 && ctype_upper(mb_substr($bare, 0, 1)) && !in_array($bare, $stopwords, true)) {
                $terms[] = $bare;
            }
        }

        return array_values(array_unique($terms));
    }
}
