<?php

namespace App\Services\MockMaster;

use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use App\Services\MockMaster\Concerns\AnswersWithAi;
use Illuminate\Support\Facades\DB;

/**
 * Sales questions answered from the real mm_payments / mm_purchases /
 * mm_coupon_usage tables — "who tried to buy and failed", "who's about to
 * expire", the exact examples the user gave when asking for this build.
 */
class MmSalesService
{
    use AnswersWithAi;

    private string $systemRole = 'You are the Sales copilot for a PTE exam-prep platform (MockMaster), reading its real payment and subscription data.';

    /** @return array<int, array{slug: string, label: string}> */
    public function quickPrompts(): array
    {
        return [
            ['slug' => 'failed_payments_recent', 'label' => 'Who tried to buy a subscription and failed recently?'],
            ['slug' => 'expiring_soon', 'label' => 'Which subscriptions expire in the next 3 days?'],
            ['slug' => 'coupon_performance', 'label' => 'Which coupon codes drove the most purchases?'],
            ['slug' => 'avg_deal_value', 'label' => "What's the average successful payment amount this month?"],
        ];
    }

    public function answer(string $slug): array
    {
        return match ($slug) {
            'failed_payments_recent' => $this->failedPaymentsRecent(),
            'expiring_soon' => $this->expiringSoon(),
            'coupon_performance' => $this->couponPerformance(),
            'avg_deal_value' => $this->avgDealValue(),
            default => ['rows' => [], 'answer' => 'Unknown question.', 'ai_used' => false],
        };
    }

    private function failedPaymentsRecent(): array
    {
        $rows = DB::table('mm_payments as pay')
            ->join('mm_studentuser as s', 'pay.buyerid', '=', 's.studentId')
            ->where('pay.status', 0)
            ->where('pay.create_date', '>=', now()->subDays(30))
            ->orderByDesc('pay.create_date')
            ->limit(20)
            ->select('s.first_name', 's.last_name', 's.email', 's.phone', 'pay.product', 'pay.amount', 'pay.create_date')
            ->get()
            ->map(fn ($r) => [
                'name' => trim("{$r->first_name} {$r->last_name}"),
                'email' => $r->email,
                'phone' => $r->phone,
                'product' => $r->product,
                'amount' => (float) $r->amount,
                'attempted_at' => $r->create_date,
            ])
            ->all();

        $emptyMessage = 'No failed payment attempts in the last 30 days.';
        if (empty($rows)) {
            return ['rows' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $plain = count($rows) . ' failed checkout attempt(s) in the last 30 days — most recent: '
            . "{$rows[0]['name']} ({$rows[0]['email']}) for \"{$rows[0]['product']}\" at \${$rows[0]['amount']} on {$rows[0]['attempted_at']}.";

        return $this->buildAnswer($rows, 'Who tried to buy a subscription and failed recently?', $emptyMessage, fn () => $plain);
    }

    private function expiringSoon(): array
    {
        $rows = DB::table('mm_purchases as p')
            ->join('mm_studentuser as s', 'p.studentid', '=', 's.studentId')
            ->where('p.is_expired', 0)
            ->whereBetween('p.expire_date', [now(), now()->addDays(3)])
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

        $emptyMessage = 'No subscriptions expiring in the next 3 days.';
        if (empty($rows)) {
            return ['rows' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $plain = count($rows) . ' subscription(s) expire in the next 3 days — next up: '
            . "{$rows[0]['name']} ({$rows[0]['email']}), \"{$rows[0]['product']}\" expires {$rows[0]['expires_at']}.";

        return $this->buildAnswer($rows, 'Which subscriptions expire in the next 3 days?', $emptyMessage, fn () => $plain);
    }

    private function couponPerformance(): array
    {
        $rows = DB::table('mm_coupon_usage')
            ->select('coupon_code', DB::raw('COUNT(*) as uses'))
            ->groupBy('coupon_code')
            ->orderByDesc('uses')
            ->limit(10)
            ->get()
            ->map(fn ($r) => ['coupon_code' => $r->coupon_code, 'uses' => (int) $r->uses])
            ->all();

        $emptyMessage = 'No coupon redemptions on file yet — mm_coupon_usage is empty.';
        if (empty($rows)) {
            return ['rows' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $top = $rows[0];
        $plain = "\"{$top['coupon_code']}\" is the most-used coupon — {$top['uses']} redemption(s).";

        return $this->buildAnswer($rows, 'Which coupon codes drove the most purchases?', $emptyMessage, fn () => $plain);
    }

    private function avgDealValue(): array
    {
        $thisMonth = DB::table('mm_payments')->where('status', 1)->where('create_date', '>=', now()->startOfMonth());
        $count = (clone $thisMonth)->count();
        $avg = (clone $thisMonth)->avg('amount');

        $rows = $count > 0 ? [['completed_payments' => $count, 'avg_amount' => round((float) $avg, 2)]] : [];
        $emptyMessage = 'No completed payments yet this month.';
        if (empty($rows)) {
            return ['rows' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $plain = 'Average successful payment this month is $' . number_format($rows[0]['avg_amount'], 2)
            . " across {$rows[0]['completed_payments']} completed payment(s).";

        return $this->buildAnswer($rows, "What's the average successful payment amount this month?", $emptyMessage, fn () => $plain);
    }

    /**
     * @return array{answer: string, rows: array, ai_used: bool}
     */
    public function ask(string $question): array
    {
        $question = trim($question);
        if ($question === '') {
            return ['answer' => 'Ask me something about MockMaster payments, expiries or coupons.', 'rows' => [], 'ai_used' => false];
        }

        $snapshot = [
            'failed_payments_last_30_days' => DB::table('mm_payments')->where('status', 0)->where('create_date', '>=', now()->subDays(30))->count(),
            'completed_payments_last_30_days' => DB::table('mm_payments')->where('status', 1)->where('create_date', '>=', now()->subDays(30))->count(),
            'expiring_next_7_days' => DB::table('mm_purchases')->where('is_expired', 0)->whereBetween('expire_date', [now(), now()->addDays(7)])->count(),
            'active_subscriptions' => DB::table('mm_purchases')->where('is_expired', 0)->count(),
            'coupon_redemptions_total' => DB::table('mm_coupon_usage')->count(),
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
