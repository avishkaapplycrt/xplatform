<?php

namespace App\Services\MockMaster;

use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;
use App\Services\MockMaster\Concerns\AnswersWithAi;
use Illuminate\Support\Facades\DB;

/**
 * Marketing questions answered from the real, imported MockMaster PTE Portal
 * tables (mm_studentuser, mm_payments, mm_purchases, mm_packages) — the same
 * mm_* import the user loaded directly into analytics_platform. Every number
 * here is a live SQL aggregate against those tables; OpenAI (when
 * OPENAI_API_KEY is set) only turns the aggregate into readable copy.
 */
class MmMarketingService
{
    use AnswersWithAi;

    private string $systemRole = 'You are the Marketing copilot for a PTE exam-prep platform (MockMaster), reading its real signup and payment data.';

    /** @return array<int, array{slug: string, label: string}> */
    public function quickPrompts(): array
    {
        return [
            ['slug' => 'top_converting_plan', 'label' => 'Which plan converted the most paying students this month?'],
            ['slug' => 'signup_no_payment', 'label' => 'How many students signed up but never paid?'],
            ['slug' => 'top_package_active', 'label' => 'Which package has the most active subscriptions right now?'],
            ['slug' => 'signup_trend_30d', 'label' => 'Are signups trending up or down over the last 30 days?'],
        ];
    }

    public function answer(string $slug): array
    {
        return match ($slug) {
            'top_converting_plan' => $this->topConvertingPlan(),
            'signup_no_payment' => $this->signupNoPayment(),
            'top_package_active' => $this->topPackageActive(),
            'signup_trend_30d' => $this->signupTrend30d(),
            default => ['rows' => [], 'answer' => 'Unknown question.', 'ai_used' => false],
        };
    }

    private function topConvertingPlan(): array
    {
        $rows = DB::table('mm_payments')
            ->where('status', 1)
            ->where('create_date', '>=', now()->startOfMonth())
            ->select('product', DB::raw('COUNT(*) as conversions'), DB::raw('SUM(amount) as revenue'))
            ->groupBy('product')
            ->orderByDesc('conversions')
            ->limit(5)
            ->get()
            ->map(fn ($r) => ['product' => $r->product, 'conversions' => (int) $r->conversions, 'revenue' => round((float) $r->revenue, 2)])
            ->all();

        $emptyMessage = 'No completed payments recorded yet this month.';
        if (empty($rows)) {
            return ['rows' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $top = $rows[0];
        $plain = "\"{$top['product']}\" is the top-converting plan this month — {$top['conversions']} completed payment(s), $"
            . number_format($top['revenue'], 2) . ' in revenue'
            . (count($rows) > 1 ? ", ahead of \"{$rows[1]['product']}\" ({$rows[1]['conversions']})." : '.');

        return $this->buildAnswer(
            $rows,
            'Which plan converted the most paying students this month?',
            $emptyMessage,
            fn () => $plain,
            ['month' => now()->format('F Y')]
        );
    }

    private function signupNoPayment(): array
    {
        $totalStudents = DB::table('mm_studentuser')->count();
        $payingStudentIds = DB::table('mm_payments')->where('status', 1)->distinct()->pluck('buyerid');
        $paying = DB::table('mm_studentuser')->whereIn('studentId', $payingStudentIds)->count();
        $neverPaid = $totalStudents - $paying;
        $pct = $totalStudents > 0 ? round($neverPaid / $totalStudents * 100, 1) : 0.0;

        $rows = [['total_students' => $totalStudents, 'paying_students' => $paying, 'never_paid' => $neverPaid, 'never_paid_pct' => $pct]];
        $plain = number_format($neverPaid) . ' of ' . number_format($totalStudents) . " students ({$pct}%) have signed up but never completed a payment.";

        return $this->buildAnswer(
            $rows,
            'How many students signed up but never completed a payment?',
            'No student data on file.',
            fn () => $plain
        );
    }

    private function topPackageActive(): array
    {
        $rows = DB::table('mm_purchases as p')
            ->join('mm_packages as k', 'p.productid', '=', 'k.packageid')
            ->where('p.is_expired', 0)
            ->select('k.package_name', DB::raw('COUNT(*) as active_count'))
            ->groupBy('k.package_name')
            ->orderByDesc('active_count')
            ->limit(5)
            ->get()
            ->map(fn ($r) => ['package' => $r->package_name, 'active_subscriptions' => (int) $r->active_count])
            ->all();

        $emptyMessage = 'No active (non-expired) purchases on file.';
        if (empty($rows)) {
            return ['rows' => [], 'answer' => $emptyMessage, 'ai_used' => false];
        }

        $top = $rows[0];
        $plain = "\"{$top['package']}\" has the most active subscriptions right now — {$top['active_subscriptions']} student(s)"
            . (count($rows) > 1 ? ", ahead of \"{$rows[1]['package']}\" ({$rows[1]['active_subscriptions']})." : '.');

        return $this->buildAnswer($rows, 'Which package has the most active subscriptions right now?', $emptyMessage, fn () => $plain);
    }

    private function signupTrend30d(): array
    {
        $last30 = DB::table('mm_studentuser')->where('create_date', '>=', now()->subDays(30))->count();
        $prev30 = DB::table('mm_studentuser')->whereBetween('create_date', [now()->subDays(60), now()->subDays(30)])->count();
        $deltaPct = $prev30 > 0 ? round((($last30 - $prev30) / $prev30) * 100, 1) : null;

        $rows = [['last_30_days' => $last30, 'prior_30_days' => $prev30, 'change_pct' => $deltaPct]];

        $plain = number_format($last30) . ' new student(s) signed up in the last 30 days';
        $plain .= $deltaPct === null
            ? '.'
            : (', ' . ($deltaPct >= 0 ? 'up ' : 'down ') . abs($deltaPct) . '% versus the 30 days before (' . number_format($prev30) . ').');

        return $this->buildAnswer($rows, 'Are signups trending up or down over the last 30 days?', 'No signup data on file.', fn () => $plain);
    }

    /**
     * Free-text "Ask anything" box — needs OPENAI_API_KEY configured, same
     * shape as MarketingAskService: a bounded real snapshot handed to the
     * model with a strict "use only this data" instruction, no non-AI
     * fallback for arbitrary phrasing.
     *
     * @return array{answer: string, rows: array, ai_used: bool}
     */
    public function ask(string $question): array
    {
        $question = trim($question);
        if ($question === '') {
            return ['answer' => 'Ask me something about MockMaster signups, payments or packages.', 'rows' => [], 'ai_used' => false];
        }

        $snapshot = [
            'total_students' => DB::table('mm_studentuser')->count(),
            'signups_last_30_days' => DB::table('mm_studentuser')->where('create_date', '>=', now()->subDays(30))->count(),
            'completed_payments_last_30_days' => DB::table('mm_payments')->where('status', 1)->where('create_date', '>=', now()->subDays(30))->count(),
            'failed_payments_last_30_days' => DB::table('mm_payments')->where('status', 0)->where('create_date', '>=', now()->subDays(30))->count(),
            'top_plans_this_month' => DB::table('mm_payments')->where('status', 1)->where('create_date', '>=', now()->startOfMonth())
                ->select('product', DB::raw('COUNT(*) as conversions'))->groupBy('product')->orderByDesc('conversions')->limit(5)->get(),
            'active_packages' => DB::table('mm_purchases as p')->join('mm_packages as k', 'p.productid', '=', 'k.packageid')
                ->where('p.is_expired', 0)->select('k.package_name', DB::raw('COUNT(*) as active_count'))
                ->groupBy('k.package_name')->orderByDesc('active_count')->limit(8)->get(),
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
