<?php

namespace App\Services\MockMaster;

use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use App\Services\MockMaster\Concerns\AnswersWithAi;
use Illuminate\Support\Facades\DB;

/**
 * Customer Retention questions answered from the real mm_studentuser
 * (last_login), mm_purchases (expire_date), mm_feedbacks and mm_notifications
 * tables — "which students are inactive since 7 days" and "call the customer
 * one day before subscription end" are the literal examples the user asked
 * for when requesting this build.
 */
class MmRetentionService
{
    use AnswersWithAi;

    private string $systemRole = 'You are the Customer Retention copilot for a PTE exam-prep platform (MockMaster), reading its real activity and subscription data.';

    private const NEGATIVE_KEYWORDS = ['bad', 'poor', 'worst', 'issue', 'problem', 'not working', 'refund', 'cancel', 'disappoint', 'slow', 'bug', 'error', 'crash', 'unhappy', 'frustrat'];

    /** @return array<int, array{slug: string, label: string}> */
    public function quickPrompts(): array
    {
        return [
            ['slug' => 'inactive_7_days', 'label' => 'Which students are not active since 7 days?'],
            ['slug' => 'call_before_expiry', 'label' => 'Who should be called one day before their subscription ends?'],
            ['slug' => 'negative_feedback', 'label' => 'Which recent feedback looks negative and needs follow-up?'],
            ['slug' => 'unread_notifications', 'label' => 'Who has unread notifications open for more than 3 days?'],
        ];
    }

    public function answer(string $slug): array
    {
        return match ($slug) {
            'inactive_7_days' => $this->inactive7Days(),
            'call_before_expiry' => $this->callBeforeExpiry(),
            'negative_feedback' => $this->negativeFeedback(),
            'unread_notifications' => $this->unreadNotifications(),
            default => ['rows' => [], 'answer' => 'Unknown question.', 'ai_used' => false],
        };
    }

    private function inactive7Days(): array
    {
        $rows = DB::table('mm_studentuser')
            ->whereNotNull('last_login')
            ->where('status', 1)
            ->where('last_login', '<', now()->subDays(7))
            ->orderBy('last_login')
            ->limit(20)
            ->select('first_name', 'last_name', 'email', 'phone', 'last_login')
            ->get()
            ->map(fn ($r) => [
                'name' => trim("{$r->first_name} {$r->last_name}"),
                'email' => $r->email,
                'phone' => $r->phone,
                'last_login' => $r->last_login,
                'days_inactive' => (int) abs(now()->diffInDays($r->last_login)),
            ])
            ->all();

        $total = DB::table('mm_studentuser')->whereNotNull('last_login')->where('status', 1)->where('last_login', '<', now()->subDays(7))->count();

        $emptyMessage = 'No active students have been inactive for 7+ days.';
        if (empty($rows)) {
            return ['rows' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $plain = number_format($total) . ' active student(s) have not logged in for 7+ days — longest: '
            . "{$rows[0]['name']} ({$rows[0]['days_inactive']} days, last seen {$rows[0]['last_login']}).";

        return $this->buildAnswer($rows, 'Which students are not active since 7 days?', $emptyMessage, fn () => $plain, ['total_inactive' => $total]);
    }

    private function callBeforeExpiry(): array
    {
        $tomorrowStart = now()->addDay()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        $rows = DB::table('mm_purchases as p')
            ->join('mm_studentuser as s', 'p.studentid', '=', 's.studentId')
            ->where('p.is_expired', 0)
            ->whereBetween('p.expire_date', [$tomorrowStart, $tomorrowEnd])
            ->orderBy('p.expire_date')
            ->limit(30)
            ->select('s.first_name', 's.last_name', 's.email', 's.phone', 'p.product', 'p.expire_date')
            ->get()
            ->map(fn ($r) => [
                'name' => trim("{$r->first_name} {$r->last_name}"),
                'email' => $r->email,
                'phone' => $r->phone,
                'product' => $r->product,
                'expires_at' => $r->expire_date,
            ])
            ->all();

        $emptyMessage = 'No subscriptions expire tomorrow — nobody to call today for this.';
        if (empty($rows)) {
            return ['rows' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $plain = count($rows) . ' student(s) should be called today because their subscription expires tomorrow — starting with '
            . "{$rows[0]['name']} ({$rows[0]['phone']}), \"{$rows[0]['product']}\" expires {$rows[0]['expires_at']}.";

        return $this->buildAnswer(
            $rows,
            'Who should be called one day before their subscription ends?',
            $emptyMessage,
            fn () => $plain,
            ['today' => now()->toDateString()],
            "Today's date is " . now()->toDateString() . '. Every student in the data list expires tomorrow, so every one of '
                . 'them should be called TODAY as a retention save call — do not say no one needs to be called. '
                . 'List the names and phones briefly, plain text, under 120 words.'
        );
    }

    private function negativeFeedback(): array
    {
        $rows = DB::table('mm_feedbacks as f')
            ->join('mm_studentuser as s', 'f.user_id', '=', 's.studentId')
            ->orderByDesc('f.create_date')
            ->limit(50)
            ->select('s.first_name', 's.last_name', 's.email', 'f.feedback', 'f.question_type', 'f.create_date')
            ->get()
            ->filter(fn ($r) => $r->feedback && $this->looksNegative($r->feedback))
            ->take(20)
            ->map(fn ($r) => [
                'name' => trim("{$r->first_name} {$r->last_name}"),
                'email' => $r->email,
                'question_type' => $r->question_type,
                'feedback' => $r->feedback,
                'given_at' => $r->create_date,
            ])
            ->values()
            ->all();

        $emptyMessage = 'No recent feedback contains obvious negative language.';
        if (empty($rows)) {
            return ['rows' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $plain = count($rows) . ' recent feedback entr(y/ies) look negative and may need follow-up — most recent: '
            . "{$rows[0]['name']}: \"" . mb_substr($rows[0]['feedback'], 0, 100) . '"';

        return $this->buildAnswer($rows, 'Which recent feedback looks negative and needs follow-up?', $emptyMessage, fn () => $plain);
    }

    private function looksNegative(string $text): bool
    {
        $text = mb_strtolower($text);
        foreach (self::NEGATIVE_KEYWORDS as $kw) {
            if (str_contains($text, $kw)) {
                return true;
            }
        }
        return false;
    }

    private function unreadNotifications(): array
    {
        $rows = DB::table('mm_notifications as n')
            ->join('mm_studentuser as s', 'n.student_id', '=', 's.studentId')
            ->where('n.is_read', 0)
            ->where('n.created_at', '<', now()->subDays(3))
            ->orderBy('n.created_at')
            ->limit(20)
            ->select('s.first_name', 's.last_name', 's.email', 'n.title', 'n.created_at')
            ->get()
            ->map(fn ($r) => [
                'name' => trim("{$r->first_name} {$r->last_name}"),
                'email' => $r->email,
                'notification' => $r->title,
                'sent_at' => $r->created_at,
                'days_unread' => (int) abs(now()->diffInDays($r->created_at)),
            ])
            ->all();

        $emptyMessage = 'No notifications have been sitting unread for more than 3 days.';
        if (empty($rows)) {
            return ['rows' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $plain = count($rows) . ' notification(s) unread for 3+ days — oldest: '
            . "{$rows[0]['name']}: \"{$rows[0]['notification']}\" ({$rows[0]['days_unread']} days unread).";

        return $this->buildAnswer($rows, 'Who has unread notifications open for more than 3 days?', $emptyMessage, fn () => $plain);
    }

    /**
     * @return array{answer: string, rows: array, ai_used: bool}
     */
    public function ask(string $question): array
    {
        $question = trim($question);
        if ($question === '') {
            return ['answer' => 'Ask me something about MockMaster student activity or retention.', 'rows' => [], 'ai_used' => false];
        }

        $snapshot = [
            'inactive_7_plus_days' => DB::table('mm_studentuser')->whereNotNull('last_login')->where('status', 1)->where('last_login', '<', now()->subDays(7))->count(),
            'expiring_tomorrow' => DB::table('mm_purchases')->where('is_expired', 0)->whereBetween('expire_date', [now()->addDay()->startOfDay(), now()->addDay()->endOfDay()])->count(),
            'unread_notifications_3_plus_days' => DB::table('mm_notifications')->where('is_read', 0)->where('created_at', '<', now()->subDays(3))->count(),
            'feedback_total' => DB::table('mm_feedbacks')->count(),
            'active_subscriptions' => DB::table('mm_purchases')->where('is_expired', 0)->count(),
        ];

        $client = app(OpenAiClient::class);
        if (!$client->isConfigured()) {
            return [
                'answer' => 'Free-text answers need an OpenAI key configured (OPENAI_API_KEY). Until then, try one of the buttons above.',
                'rows' => [],
                'ai_used' => false,
            ];
        }

        try {
            $answer = $client->chat(
                $this->systemRole . ' Answer using ONLY the JSON snapshot provided — never invent a name, number or fact. '
                    . 'If the data cannot answer the question, say so plainly. Output plain text only, under 150 words.',
                "Question: {$question}\n\nSnapshot:\n" . json_encode($snapshot, JSON_PRETTY_PRINT),
                ['max_tokens' => 400]
            );
        } catch (OpenAiException $e) {
            report($e);
            return ['answer' => "Couldn't reach the AI to answer that just now — try again in a moment.", 'rows' => [], 'ai_used' => false];
        }

        return ['answer' => $answer, 'rows' => [], 'ai_used' => true];
    }
}
