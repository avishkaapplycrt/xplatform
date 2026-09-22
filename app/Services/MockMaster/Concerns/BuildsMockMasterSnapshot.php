<?php

namespace App\Services\MockMaster\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * One real, live-computed snapshot spanning every mm_* table the user
 * imported — mm_studentuser, mm_payments, mm_purchases, mm_packages,
 * mm_login_history, mm_mock_test_logs, mm_mock_test_results,
 * mm_coupon_usage, mm_feedbacks, mm_deleted_students, mm_meetings,
 * mm_scheduled_emails, mm_notifications, mm_notifications_seen — so a
 * free-text question in any agent's "Ask anything" box can be answered from
 * any of them, not just the tables that agent's own quick-prompt logic
 * happens to touch. Every value here is a plain SQL aggregate; OpenAI only
 * turns it into prose.
 */
trait BuildsMockMasterSnapshot
{
    /**
     * Common English words that show up in almost every question but are
     * never a real MockMaster student's first/last name — filtered out so a
     * question like "what is the last name of Monu" searches for "Monu"
     * only, not also for "last" or "name".
     */
    private const SEARCH_STOPWORDS = [
        'the', 'is', 'of', 'what', 'give', 'me', 'last', 'first', 'name', 'names', 'email', 'emails',
        'mail', 'mailid', 'id', 'how', 'many', 'who', 'which', 'are', 'was', 'were', 'with', 'their',
        'student', 'students', 'please', 'can', 'you', 'tell', 'show', 'list', 'find', 'about', 'does',
        'do', 'has', 'have', 'and', 'for', 'that', 'this', 'phone', 'number', 'details', 'info',
        'information', 'record', 'status', 'not', 'did',
    ];

    /**
     * Pulls out the likely name/keyword terms in a free-text question (any
     * word 3+ letters that isn't a common stopword) and looks them up
     * against real mm_studentuser rows by first name, last name or email —
     * so "what is the last name of Monu" or "give me the mailid of Gupta"
     * can be answered from an actual matching row instead of the aggregate
     * snapshot, which never carries individual names.
     *
     * @return array<int, array>
     */
    protected function mockMasterSearchStudents(string $question, int $limit = 10): array
    {
        $words = preg_split('/[^A-Za-z]+/', $question) ?: [];

        $terms = collect($words)
            ->filter(fn ($w) => mb_strlen($w) >= 3)
            ->filter(fn ($w) => !in_array(mb_strtolower($w), self::SEARCH_STOPWORDS, true))
            ->unique()
            ->values();

        if ($terms->isEmpty()) {
            return [];
        }

        $query = DB::table('mm_studentuser');
        $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->orWhere('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            }
        });

        return $query->limit($limit)
            ->get(['studentId', 'first_name', 'last_name', 'email', 'phone', 'status', 'last_login', 'create_date'])
            ->map(fn ($r) => [
                'student_id' => $r->studentId,
                'first_name' => trim((string) $r->first_name),
                'last_name' => trim((string) $r->last_name),
                'email' => $r->email,
                'phone' => $r->phone,
                'status' => $r->status,
                'last_login' => $r->last_login,
                'signed_up' => $r->create_date,
            ])
            ->all();
    }

    protected function mockMasterSnapshot(): array
    {
        return [
            'students' => [
                'total' => DB::table('mm_studentuser')->count(),
                'active_status' => DB::table('mm_studentuser')->where('status', 1)->count(),
                'new_last_30_days' => DB::table('mm_studentuser')->where('create_date', '>=', now()->subDays(30))->count(),
                'inactive_7_plus_days' => DB::table('mm_studentuser')->whereNotNull('last_login')->where('status', 1)->where('last_login', '<', now()->subDays(7))->count(),
                'deleted_total' => DB::table('mm_deleted_students')->count(),
            ],
            'payments' => [
                'completed_total' => DB::table('mm_payments')->where('status', 1)->count(),
                'failed_total' => DB::table('mm_payments')->where('status', 0)->count(),
                'completed_last_30_days' => DB::table('mm_payments')->where('status', 1)->where('create_date', '>=', now()->subDays(30))->count(),
                'failed_last_30_days' => DB::table('mm_payments')->where('status', 0)->where('create_date', '>=', now()->subDays(30))->count(),
                'revenue_last_30_days' => round((float) DB::table('mm_payments')->where('status', 1)->where('create_date', '>=', now()->subDays(30))->sum('amount'), 2),
                'top_products_this_month' => DB::table('mm_payments')->where('status', 1)->where('create_date', '>=', now()->startOfMonth())
                    ->select('product', DB::raw('COUNT(*) as conversions'))->groupBy('product')->orderByDesc('conversions')->limit(5)->get(),
            ],
            'purchases' => [
                'active_non_expired' => DB::table('mm_purchases')->where('is_expired', 0)->count(),
                'expiring_next_7_days' => DB::table('mm_purchases')->where('is_expired', 0)->whereBetween('expire_date', [now(), now()->addDays(7)])->count(),
                'top_active_packages' => DB::table('mm_purchases as p')->join('mm_packages as k', 'p.productid', '=', 'k.packageid')
                    ->where('p.is_expired', 0)->select('k.package_name', DB::raw('COUNT(*) as active_count'))
                    ->groupBy('k.package_name')->orderByDesc('active_count')->limit(8)->get(),
            ],
            'packages' => [
                'total_defined' => DB::table('mm_packages')->count(),
                'purchaseable' => DB::table('mm_packages')->where('is_purchaseable', 1)->count(),
            ],
            'coupons' => [
                'redemptions_total' => DB::table('mm_coupon_usage')->count(),
                'top_codes' => DB::table('mm_coupon_usage')->select('coupon_code', DB::raw('COUNT(*) as uses'))->groupBy('coupon_code')->orderByDesc('uses')->limit(5)->get(),
            ],
            'login_activity' => [
                'logins_last_30_days' => DB::table('mm_login_history')->where('login_time', '>=', now()->subDays(30))->count(),
                'logins_last_7_days' => DB::table('mm_login_history')->where('login_time', '>=', now()->subDays(7))->count(),
                'total_logins_on_file' => DB::table('mm_login_history')->count(),
            ],
            'mock_test_activity' => [
                'attempts_total' => DB::table('mm_mock_test_logs')->count(),
                'attempts_last_30_days' => DB::table('mm_mock_test_logs')->where('create_date', '>=', now()->subDays(30))->count(),
                'top_mock_series_last_30_days' => DB::table('mm_mock_test_logs')->where('create_date', '>=', now()->subDays(30))
                    ->select('mock_series', DB::raw('COUNT(*) as attempts'))->groupBy('mock_series')->orderByDesc('attempts')->limit(5)->get(),
            ],
            'mock_test_results' => [
                'results_total' => DB::table('mm_mock_test_results')->count(),
                'avg_overall_score' => round((float) DB::table('mm_mock_test_results')->avg('overall_score'), 2),
                'avg_writing_score' => round((float) DB::table('mm_mock_test_results')->avg('writing_score'), 2),
                'avg_speaking_score' => round((float) DB::table('mm_mock_test_results')->avg('speaking_score'), 2),
                'avg_listening_score' => round((float) DB::table('mm_mock_test_results')->avg('listening_score'), 2),
                'avg_reading_score' => round((float) DB::table('mm_mock_test_results')->avg('reading_score'), 2),
            ],
            'feedback' => [
                'total' => DB::table('mm_feedbacks')->count(),
                'last_30_days' => DB::table('mm_feedbacks')->where('create_date', '>=', now()->subDays(30))->count(),
            ],
            'meetings' => [
                'total' => DB::table('mm_meetings')->count(),
                'upcoming' => DB::table('mm_meetings')->where('from_date', '>=', now())->count(),
            ],
            'scheduled_emails' => [
                'total' => DB::table('mm_scheduled_emails')->count(),
                'last_30_days' => DB::table('mm_scheduled_emails')->where('scheduled_at', '>=', now()->subDays(30))->count(),
            ],
            'notifications' => [
                'total' => DB::table('mm_notifications')->count(),
                'unread' => DB::table('mm_notifications')->where('is_read', 0)->count(),
                'unread_3_plus_days' => DB::table('mm_notifications')->where('is_read', 0)->where('created_at', '<', now()->subDays(3))->count(),
                'seen_events_total' => DB::table('mm_notifications_seen')->count(),
            ],
        ];
    }
}
