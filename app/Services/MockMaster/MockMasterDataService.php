<?php

namespace App\Services\MockMaster;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Real-data backing for the Mock Master Helper page. Queries only the
 * mm_* tables (mm_studentuser, mm_purchases, mm_payments, mm_packages,
 * mm_mock_test_results, mm_login_history, etc.) — never crm_contacts,
 * crm_deals, or any of this client's own CRM/email data. Keeping this
 * fully separate from RealAccountsService / the Business Helpers
 * services on purpose, since Mock Master is a different student
 * platform's data, not this client's own.
 */
class MockMasterDataService
{
    /** Campaign step — renewal-ready students, ranked by package value. */
    /** Distinct subscription/package names, for the Campaign tab's subscription filter. */
    public function subscriptionOptions(): array
    {
        return DB::table('mm_purchases')
            ->whereNotNull('product')
            ->where('product', '!=', '')
            ->distinct()
            ->orderBy('product')
            ->pluck('product')
            ->all();
    }

    public function campaignStudents(int $limit = 10, int $offset = 0, ?string $subscription = null, ?string $from = null, ?string $to = null): array
    {
        return $this->mapCampaignRows(
            $this->campaignCandidates($subscription, $from, $to)->slice($offset, $limit)
        );
    }

    /**
     * Paged view of the Campaign tab. Pagination runs over the same
     * 2,000 most-recent-purchase candidates as campaignStudents(), so
     * "total" is capped at that candidate pool, not the full student base.
     *
     * @return array{students: array, total: int, page: int, last_page: int}
     */
    public function campaignStudentsPage(int $page, int $perPage, ?string $subscription = null, ?string $from = null, ?string $to = null): array
    {
        $candidates = $this->campaignCandidates($subscription, $from, $to);
        $total = $candidates->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);

        return [
            'students' => $this->mapCampaignRows($candidates->slice(($page - 1) * $perPage, $perPage)),
            'total' => $total,
            'page' => $page,
            'last_page' => $lastPage,
        ];
    }

    private function campaignCandidates(?string $subscription, ?string $from, ?string $to)
    {
        $query = DB::table('mm_purchases as p')
            ->join('mm_studentuser as s', 's.studentId', '=', 'p.studentid')
            ->leftJoin('mm_payments as pay', 'pay.id', '=', 'p.paymentid')
            ->select('p.studentid', 'p.product', 'p.expire_date', 'p.is_expired', 's.first_name', 's.last_name', 's.last_login', 's.email', 's.phone', 's.country_code')
            ->addSelect('pay.amount as amount', 'pay.create_date as payment_date')
            ->whereNotNull('s.first_name');

        if ($subscription) {
            $query->where('p.product', $subscription);
        }
        if ($from) {
            $query->where('pay.create_date', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            $query->where('pay.create_date', '<=', Carbon::parse($to)->endOfDay());
        }

        return $query
            ->orderByDesc('p.create_date')
            ->limit(2000)
            ->get()
            ->unique('studentid')
            ->values();
    }

    private function mapCampaignRows($rows): array
    {
        if ($rows->isEmpty()) {
            return [];
        }

        $studentIds = $rows->pluck('studentid')->all();
        $avgScores = $this->avgScoresByStudent($studentIds);
        $paymentHealth = $this->paymentHealthByStudent($studentIds);

        return $rows->map(function ($r) use ($avgScores, $paymentHealth) {
            $readiness = (int) round($avgScores[$r->studentid] ?? 0);
            $trust = (int) round($paymentHealth[$r->studentid] ?? 50);
            $daysToExpire = $r->expire_date ? (int) Carbon::parse($r->expire_date)->diffInDays(now(), false) : null;

            $stage = $r->is_expired
                ? 'Expired'
                : ($daysToExpire !== null && $daysToExpire >= -14 && $daysToExpire <= 0 ? 'Renewal Due' : 'Active');

            return [
                'name' => trim($r->first_name . ' ' . $r->last_name) ?: 'Student #' . $r->studentid,
                'sub' => $r->product ?: 'Package',
                'value' => '$' . number_format((float) ($r->amount ?? 0), 0),
                'paymentDate' => $r->payment_date ? Carbon::parse($r->payment_date)->format('d M Y') : '—',
                'stage' => $stage,
                'readiness' => $readiness,
                'trust' => $trust,
                'approach' => $trust < 65 ? 'Proof-led' : 'Offer-led',
                'lastActive' => $this->relativeLogin($r->last_login),
                'email' => $r->email ?: null,
                'phone' => $this->formatPhone($r->country_code, $r->phone),
            ];
        })->values()->all();
    }

    /** Performance step — headline KPIs. */
    public function performanceKpis(): array
    {
        $totalStudents = DB::table('mm_studentuser')->whereNull('deleted_at')->count();
        $activeStudents = DB::table('mm_purchases')->where('is_expired', 0)->distinct('studentid')->count('studentid');
        $mockTests30d = DB::table('mm_mock_test_results')->where('create_date', '>=', now()->subDays(30))->count();
        $avgScore = DB::table('mm_mock_test_results')->whereNotNull('overall_score')->avg('overall_score');
        $totalPurchases = DB::table('mm_purchases')->count();
        $renewalRate = $totalPurchases > 0
            ? round(DB::table('mm_purchases')->where('is_expired', 0)->count() / $totalPurchases * 100)
            : 0;

        return [
            ['label' => 'Active Students',   'value' => number_format($activeStudents), 'sub' => number_format($totalStudents) . ' total registered'],
            ['label' => 'Mock Tests Taken',  'value' => number_format($mockTests30d),    'sub' => 'last 30 days'],
            ['label' => 'Avg Overall Score', 'value' => $avgScore ? number_format($avgScore, 1) : '—', 'sub' => 'across all results'],
            ['label' => 'Active Packages',   'value' => round($renewalRate) . '%',        'sub' => 'of all purchases not expired'],
        ];
    }

    /** Audience step — student segments. */
    public function audienceSegments(): array
    {
        $highScorers = DB::table('mm_mock_test_results')
            ->select('studentId')
            ->whereNotNull('overall_score')
            ->groupBy('studentId')
            ->havingRaw('AVG(overall_score) >= 75')
            ->get()->count();

        $expiringSoon = DB::table('mm_purchases')
            ->where('is_expired', 0)
            ->whereBetween('expire_date', [now(), now()->addDays(7)])
            ->distinct('studentid')->count('studentid');

        $expiringWatch = DB::table('mm_purchases')
            ->where('is_expired', 0)
            ->whereBetween('expire_date', [now()->addDays(8), now()->addDays(30)])
            ->distinct('studentid')->count('studentid');

        $newStudents = DB::table('mm_studentuser')
            ->whereNotNull('create_date')
            ->where('create_date', '>=', now()->subDays(14)->toDateString())
            ->count();

        return [
            ['name' => 'High Scorers',          'meta' => number_format($highScorers) . ' students · avg score 75+'],
            ['name' => 'Package Expiring Soon', 'meta' => number_format($expiringSoon) . ' students · next 7 days'],
            ['name' => 'Renewal Watch',         'meta' => number_format($expiringWatch) . ' students · next 8-30 days'],
            ['name' => 'New Students',          'meta' => number_format($newStudents) . ' students · last 14 days'],
        ];
    }

    /** Audience · named list backing "Who are our high-scoring students?" (avg overall_score >= 75). */
    public function topScorers(int $limit = 10, int $offset = 0): array
    {
        $rows = DB::table('mm_mock_test_results')
            ->select('studentId')
            ->selectRaw('AVG(overall_score) as avg_score')
            ->whereNotNull('overall_score')
            ->groupBy('studentId')
            ->havingRaw('AVG(overall_score) >= 75')
            ->orderByDesc('avg_score')
            ->offset($offset)
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $students = DB::table('mm_studentuser')->whereIn('studentId', $rows->pluck('studentId'))->get()->keyBy('studentId');

        return $rows->map(function ($r) use ($students) {
            $s = $students[$r->studentId] ?? null;
            return [
                'name' => $s ? (trim($s->first_name . ' ' . $s->last_name) ?: 'Student #' . $r->studentId) : 'Student #' . $r->studentId,
                'avg_score' => round((float) $r->avg_score, 1),
                'lastActive' => $s ? $this->relativeLogin($s->last_login) : '—',
                'email' => $s->email ?? null,
                'phone' => $s ? $this->formatPhone($s->country_code, $s->phone) : null,
            ];
        })->values()->all();
    }

    /** Audience · named list backing "Which students joined in the last 14 days?" */
    public function newStudents(int $limit = 10, int $offset = 0): array
    {
        $rows = DB::table('mm_studentuser')
            ->whereNotNull('create_date')
            ->where('create_date', '>=', now()->subDays(14)->toDateString())
            ->orderByDesc('create_date')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return $rows->map(fn ($s) => [
            'name' => trim($s->first_name . ' ' . $s->last_name) ?: 'Student #' . $s->studentId,
            'sub' => $s->student_course_type ?: '—',
            'joined' => $s->create_date,
            'email' => $s->email ?: null,
            'phone' => $this->formatPhone($s->country_code, $s->phone),
        ])->values()->all();
    }

    /** Real student names offered in the "Which student?" picker for the Sales · Accounts prompts. */
    public function candidateStudentNames(int $limit = 60): array
    {
        return DB::table('mm_studentuser')
            ->whereNull('deleted_at')
            ->whereNotNull('first_name')
            ->where('first_name', '!=', '')
            ->orderByDesc('last_login')
            ->limit($limit)
            ->get(['first_name', 'last_name'])
            ->map(fn ($s) => trim($s->first_name . ' ' . $s->last_name))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** Insights step — real computed findings, not fabricated claims. */
    public function insights(): array
    {
        $avgScore = DB::table('mm_mock_test_results')->whereNotNull('overall_score')->avg('overall_score');
        $totalPurchases = DB::table('mm_purchases')->count();
        $expiredPct = $totalPurchases > 0
            ? round(DB::table('mm_purchases')->where('is_expired', 1)->count() / $totalPurchases * 100)
            : 0;
        $recentTests = DB::table('mm_mock_test_results')->where('create_date', '>=', now()->subDays(7))->count();
        $failedPayments = DB::table('mm_payments')->where('status', 0)->count();
        $totalPayments = DB::table('mm_payments')->count();

        return array_filter([
            $avgScore ? sprintf('Average mock test overall score across all results is %.1f.', $avgScore) : null,
            sprintf('%d%% of all issued packages have already expired.', $expiredPct),
            sprintf('%s mock tests were taken in the last 7 days.', number_format($recentTests)),
            $totalPayments > 0 ? sprintf('%d of %d payments (%.0f%%) are marked unpaid/failed.', $failedPayments, $totalPayments, $totalPayments ? $failedPayments / $totalPayments * 100 : 0) : null,
        ]);
    }

    /** Sales — students with no active paid package yet (prospects). */
    public function salesProspects(int $limit = 10, int $offset = 0): array
    {
        $freePackageIds = DB::table('mm_packages')->where('cost', 0)->pluck('packageid');

        $studentIds = DB::table('mm_purchases')
            ->whereIn('productid', $freePackageIds)
            ->whereNotIn('studentid', function ($q) use ($freePackageIds) {
                $q->select('studentid')->from('mm_purchases')->whereNotIn('productid', $freePackageIds);
            })
            ->distinct()->orderBy('studentid')->pluck('studentid')->take(2000);

        if ($studentIds->isEmpty()) {
            return [];
        }

        $students = DB::table('mm_studentuser')->whereIn('studentId', $studentIds)->orderBy('studentId')->offset($offset)->limit($limit)->get();
        $avgScores = $this->avgScoresByStudent($students->pluck('studentId')->all());

        return $students->map(function ($s) use ($avgScores) {
            $readiness = (int) round($avgScores[$s->studentId] ?? 0);
            $intent = $s->profile_completed ? min(100, $readiness + 20) : max(10, $readiness - 20);
            $trust = $s->otp_verified ? 70 : 40;

            return [
                'name' => trim($s->first_name . ' ' . $s->last_name) ?: 'Student #' . $s->studentId,
                'sub' => 'Interested in ' . ($s->student_course_type ?: 'a course'),
                'readiness' => $readiness,
                'intent' => $intent,
                'trust' => $trust,
                'play' => $readiness >= 50 ? 'Call' : 'Nurture',
                'email' => $s->email ?: null,
                'phone' => $this->formatPhone($s->country_code, $s->phone),
            ];
        })->values()->all();
    }

    /** Sales — students on a free/trial package who show real recent activity (upsell candidates). */
    public function salesCloseCandidates(int $limit = 5, int $offset = 0): array
    {
        $activeStudentIds = DB::table('mm_mock_test_results')
            ->where('create_date', '>=', now()->subDays(14))
            ->distinct('studentId')->pluck('studentId')->take(2000);

        $freePackageIds = DB::table('mm_packages')->where('cost', 0)->pluck('packageid');

        $candidates = DB::table('mm_purchases')
            ->whereIn('studentid', $activeStudentIds)
            ->whereIn('productid', $freePackageIds)
            ->distinct('studentid')->orderBy('studentid')->pluck('studentid');

        if ($candidates->isEmpty()) {
            return [];
        }

        $candidates = $candidates->slice($offset, $limit)->values();
        if ($candidates->isEmpty()) {
            return [];
        }

        $students = DB::table('mm_studentuser')->whereIn('studentId', $candidates)->get();
        $testCounts = DB::table('mm_mock_test_results')
            ->whereIn('studentId', $candidates)
            ->where('create_date', '>=', now()->subDays(14))
            ->selectRaw('studentId, COUNT(*) as cnt')
            ->groupBy('studentId')->pluck('cnt', 'studentId');

        return $students->map(fn ($s) => [
            'name' => trim($s->first_name . ' ' . $s->last_name) ?: 'Student #' . $s->studentId,
            'detail' => ($testCounts[$s->studentId] ?? 0) . ' free mock tests in the last 14 days — hasn\'t purchased a paid package yet.',
            'email' => $s->email ?: null,
            'phone' => $this->formatPhone($s->country_code, $s->phone),
        ])->values()->all();
    }

    /** Retention — students whose active package expires soonest (highest value at risk first). */
    public function retentionAtRisk(int $limit = 6, int $offset = 0): array
    {
        return $this->retentionByExpiryWindow(now(), now()->addDays(7), $limit, $offset);
    }

    public function retentionWatchlist(int $limit = 6, int $offset = 0): array
    {
        return $this->retentionByExpiryWindow(now()->addDays(8), now()->addDays(30), $limit, $offset);
    }

    private function retentionByExpiryWindow($from, $to, int $limit, int $offset = 0): array
    {
        $rows = DB::table('mm_purchases as p')
            ->join('mm_studentuser as s', 's.studentId', '=', 'p.studentid')
            ->select('p.studentid', 'p.product', 'p.expire_date', 's.first_name', 's.last_name', 's.last_login', 's.email', 's.phone', 's.country_code')
            ->selectRaw('(select max(pay.amount) from mm_payments pay where pay.id = p.paymentid) as amount')
            ->where('p.is_expired', 0)
            ->whereBetween('p.expire_date', [$from, $to])
            ->orderBy('p.expire_date')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return $rows->map(function ($r) {
            $inactiveDays = $r->last_login ? (int) Carbon::parse($r->last_login)->diffInDays(now()) : 999;
            $risk = min(100, max(10, $inactiveDays + (int) Carbon::parse($r->expire_date)->diffInDays(now(), true)));

            return [
                'name' => trim($r->first_name . ' ' . $r->last_name) ?: 'Student #' . $r->studentid,
                'sub' => $r->product ?: 'Package',
                'inactiveDays' => min($inactiveDays, 999),
                'valueAtRisk' => '$' . number_format((float) ($r->amount ?? 0), 0),
                'risk' => (int) $risk,
                'email' => $r->email ?: null,
                'phone' => $this->formatPhone($r->country_code, $r->phone),
            ];
        })->values()->all();
    }

    /** Retention — real root-cause breakdown, not illustrative examples. */
    public function retentionRootCauses(): array
    {
        $failedPayments = DB::table('mm_payments')->where('status', 0)->count();

        $inactiveStudentIds = DB::table('mm_purchases')->where('is_expired', 0)->distinct('studentid')->pluck('studentid');
        $recentlyActiveIds = DB::table('mm_mock_test_logs')->where('create_date', '>=', now()->subDays(14))->distinct('studentId')->pluck('studentId');
        $inactiveCount = $inactiveStudentIds->diff($recentlyActiveIds)->count();

        $lowScoreCount = DB::table('mm_mock_test_results')
            ->select('studentId')->whereNotNull('overall_score')
            ->groupBy('studentId')->havingRaw('AVG(overall_score) < 50')->get()->count();

        return [
            ['name' => 'Payment failed',                     'meta' => number_format($failedPayments) . ' payments marked unpaid/failed'],
            ['name' => 'No mock test activity in 14+ days',  'meta' => number_format($inactiveCount) . ' students with an active package but no recent test'],
            ['name' => 'Low score, low confidence',           'meta' => number_format($lowScoreCount) . ' students averaging below 50 overall'],
        ];
    }

    private function avgScoresByStudent(array $studentIds): array
    {
        return DB::table('mm_mock_test_results')
            ->whereIn('studentId', $studentIds)
            ->whereNotNull('overall_score')
            ->selectRaw('studentId, AVG(overall_score) as avg_score')
            ->groupBy('studentId')
            ->pluck('avg_score', 'studentId')
            ->all();
    }

    private function paymentHealthByStudent(array $studentIds): array
    {
        $rows = DB::table('mm_purchases as p')
            ->join('mm_payments as pay', 'pay.id', '=', 'p.paymentid')
            ->whereIn('p.studentid', $studentIds)
            ->selectRaw('p.studentid, AVG(pay.status) * 100 as health')
            ->groupBy('p.studentid')
            ->pluck('health', 'studentid');

        return $rows->map(fn ($v) => (float) $v)->all();
    }

    private function relativeLogin(?string $lastLogin): string
    {
        if (!$lastLogin) {
            return 'Never';
        }
        $days = (int) Carbon::parse($lastLogin)->diffInDays(now());
        return $days <= 7 ? 'This week' : $days . 'd ago';
    }

    /** Real contact number for the "who to call" style answers — country code + number, or null if none on file. */
    private function formatPhone(?string $countryCode, $phone): ?string
    {
        if (!$phone) {
            return null;
        }
        return trim(($countryCode ? $countryCode . ' ' : '') . $phone);
    }

    /**
     * A single, compact, real-data snapshot drawn from all 14 mm_* tables,
     * for the "Ask Mira" free-text chat to ground its answer in. Large
     * tables (mm_mock_test_logs: ~770k rows, mm_login_history: ~185k rows)
     * are aggregated into counts rather than dumped row-by-row, to keep the
     * prompt a reasonable size — every number here is still real, just
     * summarised instead of enumerated.
     */
    /**
     * A real, targeted lookup for a specific student named or emailed in a
     * chat question — the aggregated chatSnapshot() deliberately excludes
     * the full 58k+ student directory (too large to send every time), so a
     * question like "what's Samina's email" has nothing to match against
     * unless we search for that name directly.
     */
    /**
     * Paid subscriptions (mm_payments.status = 1) whose payment date
     * (mm_payments.create_date) falls inside [$start, $end]. Used by the
     * Ask Mira chat for date-range questions like "paid subscriptions last
     * month" — the aggregated snapshot has no per-period breakdown.
     */
    public function paidPaymentsBetween(Carbon $start, Carbon $end, int $listLimit = 200): array
    {
        $base = DB::table('mm_payments as pay')
            ->where('pay.status', 1)
            ->whereBetween('pay.create_date', [$start, $end]);

        $count = (clone $base)->count();
        $totalAmount = (float) (clone $base)->sum('pay.amount');

        $byProduct = (clone $base)
            ->select('pay.product', DB::raw('COUNT(*) as payments'), DB::raw('SUM(pay.amount) as amount'))
            ->groupBy('pay.product')
            ->orderByDesc('payments')
            ->limit(15)
            ->get()
            ->map(fn ($r) => ['product' => $r->product, 'payments' => (int) $r->payments, 'amount' => (float) $r->amount])
            ->values()
            ->all();

        $payments = (clone $base)
            ->leftJoin('mm_studentuser as s', 's.studentId', '=', 'pay.buyerid')
            ->select('pay.create_date', 'pay.product', 'pay.amount', 's.first_name', 's.last_name', 's.email')
            ->orderByDesc('pay.create_date')
            ->limit($listLimit)
            ->get()
            ->map(fn ($r) => [
                'paid_on' => Carbon::parse($r->create_date)->format('Y-m-d'),
                'student' => trim($r->first_name . ' ' . $r->last_name) ?: null,
                'email' => $r->email ?: null,
                'product' => $r->product,
                'amount' => (float) $r->amount,
            ])
            ->values()
            ->all();

        return [
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'paid_payments_count' => $count,
            'total_paid_amount' => round($totalAmount, 2),
            'by_product' => $byProduct,
            'payments_list' => $payments,
            'payments_list_truncated' => $count > $listLimit,
        ];
    }

    public function searchStudents(array $terms, int $limit = 8): array
    {
        if (empty($terms)) {
            return [];
        }

        $query = DB::table('mm_studentuser')->whereNull('deleted_at');
        $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->orWhere('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            }
        });

        $students = $query->limit($limit)->get([
            'studentId', 'first_name', 'last_name', 'email', 'phone',
            'student_course_type', 'desired_band', 'status', 'last_login', 'create_date',
        ]);

        if ($students->isEmpty()) {
            return [];
        }

        $ids = $students->pluck('studentId')->all();
        $avgScores = $this->avgScoresByStudent($ids);

        $activePurchases = DB::table('mm_purchases')
            ->whereIn('studentid', $ids)
            ->where('is_expired', 0)
            ->select('studentid', 'product', 'expire_date')
            ->get()
            ->groupBy('studentid');

        return $students->map(function ($s) use ($avgScores, $activePurchases) {
            return [
                'name' => trim($s->first_name . ' ' . $s->last_name),
                'email' => $s->email,
                'phone' => $s->phone,
                'course_type' => $s->student_course_type,
                'desired_band' => $s->desired_band,
                'avg_mock_test_score' => isset($avgScores[$s->studentId]) ? round($avgScores[$s->studentId], 1) : null,
                'last_login' => $s->last_login,
                'registered_on' => $s->create_date,
                'active_packages' => ($activePurchases[$s->studentId] ?? collect())->pluck('product')->all(),
            ];
        })->values()->all();
    }

    /** Most recent real login record, for "who last logged in" style questions. */
    public function mostRecentLogins(int $limit = 5): array
    {
        return DB::table('mm_login_history as l')
            ->join('mm_studentuser as s', 's.studentId', '=', 'l.user_id')
            ->select('s.first_name', 's.last_name', 'l.email', 'l.login_time', 'l.ip_address')
            ->orderByDesc('l.login_time')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => trim($r->first_name . ' ' . $r->last_name),
                'email' => $r->email,
                'login_time' => $r->login_time,
                'ip_address' => $r->ip_address,
            ])->values()->all();
    }

    public function chatSnapshot(): array
    {
        return [
            'kpis' => $this->performanceKpis(),
            'segments' => $this->audienceSegments(),
            'insights' => $this->insights(),
            'top_students_at_risk' => $this->retentionAtRisk(10),
            'watchlist' => $this->retentionWatchlist(10),
            'root_causes' => $this->retentionRootCauses(),
            'top_prospects' => $this->salesProspects(10),
            'close_candidates' => $this->salesCloseCandidates(10),

            'students' => [
                'total_registered' => DB::table('mm_studentuser')->whereNull('deleted_at')->count(),
                'deleted_count' => DB::table('mm_deleted_students')->count(),
            ],

            'purchases' => [
                'total' => DB::table('mm_purchases')->count(),
                'active' => DB::table('mm_purchases')->where('is_expired', 0)->count(),
                'expired' => DB::table('mm_purchases')->where('is_expired', 1)->count(),
            ],

            'payments' => [
                'total' => DB::table('mm_payments')->count(),
                'paid' => DB::table('mm_payments')->where('status', 1)->count(),
                'unpaid_or_failed' => DB::table('mm_payments')->where('status', 0)->count(),
                'total_revenue' => (int) DB::table('mm_payments')->where('status', 1)->sum('amount'),
            ],

            'packages' => DB::table('mm_packages')
                ->select('package_name', 'cost', 'usage_type', 'package_category')
                ->where('status', 1)
                ->limit(25)
                ->get(),

            'coupon_usage' => [
                'total_redemptions' => DB::table('mm_coupon_usage')->count(),
            ],

            'mock_tests' => [
                'total_results' => DB::table('mm_mock_test_results')->count(),
                'last_7_days' => DB::table('mm_mock_test_results')->where('create_date', '>=', now()->subDays(7))->count(),
                'last_30_days' => DB::table('mm_mock_test_results')->where('create_date', '>=', now()->subDays(30))->count(),
                'avg_overall_score' => round((float) DB::table('mm_mock_test_results')->whereNotNull('overall_score')->avg('overall_score'), 1),
                'activity_log_total' => DB::table('mm_mock_test_logs')->count(),
                'activity_log_last_7_days' => DB::table('mm_mock_test_logs')->where('create_date', '>=', now()->subDays(7))->count(),
            ],

            'logins' => [
                'total_recorded' => DB::table('mm_login_history')->count(),
                'unique_students_last_7_days' => DB::table('mm_login_history')->where('login_time', '>=', now()->subDays(7))->distinct('user_id')->count('user_id'),
                'unique_students_last_30_days' => DB::table('mm_login_history')->where('login_time', '>=', now()->subDays(30))->distinct('user_id')->count('user_id'),
                'most_recent_logins' => $this->mostRecentLogins(5),
            ],

            'meetings' => [
                'total' => DB::table('mm_meetings')->count(),
                'upcoming' => DB::table('mm_meetings')
                    ->select('name', 'from_date', 'to_date', 'course', 'language')
                    ->where('from_date', '>=', now())
                    ->orderBy('from_date')
                    ->limit(10)
                    ->get(),
            ],

            'feedbacks' => [
                'total' => DB::table('mm_feedbacks')->count(),
                'recent' => DB::table('mm_feedbacks')
                    ->select('question_type', 'feedback', 'create_date')
                    ->orderByDesc('create_date')
                    ->limit(10)
                    ->get(),
            ],

            'notifications' => [
                'total' => DB::table('mm_notifications')->count(),
                'unread' => DB::table('mm_notifications')->where('is_read', 0)->count(),
                'total_seen_records' => DB::table('mm_notifications_seen')->count(),
            ],

            'scheduled_emails' => [
                'total' => DB::table('mm_scheduled_emails')->count(),
                'upcoming' => DB::table('mm_scheduled_emails')
                    ->select('template', 'scheduled_at')
                    ->where('scheduled_at', '>=', now())
                    ->orderBy('scheduled_at')
                    ->limit(10)
                    ->get(),
            ],
        ];
    }
}
