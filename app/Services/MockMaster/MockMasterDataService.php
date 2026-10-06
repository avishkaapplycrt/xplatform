<?php

namespace App\Services\MockMaster;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
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
    public function campaignStudentsPage(int $page, int $perPage, ?string $subscription = null, ?string $from = null, ?string $to = null, ?string $sort = null, string $dir = 'asc'): array
    {
        $candidates = $this->campaignCandidates($subscription, $from, $to);
        $total = $candidates->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);

        if ($sort && in_array($sort, self::CAMPAIGN_SORT_KEYS, true)) {
            $sorted = collect($this->mapCampaignRows($candidates))
                ->sortBy(fn ($r) => $r['sort'][$sort] ?? null, SORT_REGULAR, $dir === 'desc')
                ->values();
            $students = $sorted->slice(($page - 1) * $perPage, $perPage)->values()->all();
        } else {
            $students = $this->mapCampaignRows($candidates->slice(($page - 1) * $perPage, $perPage));
        }

        return [
            'students' => array_map(fn ($r) => array_diff_key($r, ['sort' => true]), $students),
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
                'sort' => [
                    'value' => (float) ($r->amount ?? 0),
                    'payment' => $r->payment_date ? Carbon::parse($r->payment_date)->timestamp : null,
                    'stage' => ['Active' => 0, 'Renewal Due' => 1, 'Expired' => 2][$stage],
                    'readiness' => $readiness,
                    'trust' => $trust,
                    'approach' => $trust < 65 ? 0 : 1,
                    'last_active' => $r->last_login ? Carbon::parse($r->last_login)->timestamp : null,
                ],
            ];
        })->values()->all();
    }

    private const CAMPAIGN_SORT_KEYS = ['value', 'payment', 'stage', 'readiness', 'trust', 'approach', 'last_active'];

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
            ['key' => 'active_students', 'label' => 'Active Students',   'value' => number_format($activeStudents), 'sub' => number_format($totalStudents) . ' total registered'],
            ['key' => 'mock_tests',      'label' => 'Mock Tests Taken',  'value' => number_format($mockTests30d),    'sub' => 'last 30 days'],
            ['key' => 'avg_score',       'label' => 'Avg Overall Score', 'value' => $avgScore ? number_format($avgScore, 1) : '—', 'sub' => 'across all results'],
            ['key' => 'active_packages', 'label' => 'Active Packages',   'value' => round($renewalRate) . '%',        'sub' => 'of all purchases not expired'],
        ];
    }

    /**
     * Drill-down rows behind one Performance KPI card. Each card's detail
     * is the real rows its headline number was counted from.
     *
     * @return array{title: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array>, total: int}
     */
    public function kpiDetails(string $key, int $limit = 300): array
    {
        $name = "TRIM(CONCAT(COALESCE(s.first_name,''), ' ', COALESCE(s.last_name,'')))";

        return match ($key) {
            'active_students' => $this->kpiActiveStudents($name, $limit),
            'mock_tests' => $this->kpiMockTests($name, $limit),
            'avg_score' => $this->kpiAvgScore($name, $limit),
            'active_packages' => $this->kpiActivePackages($limit),
            default => ['title' => 'Unknown', 'columns' => [], 'rows' => [], 'total' => 0],
        };
    }

    private function kpiActiveStudents(string $name, int $limit): array
    {
        $base = DB::table('mm_purchases as p')->where('p.is_expired', 0);
        $total = (clone $base)->distinct('p.studentid')->count('p.studentid');

        $rows = (clone $base)
            ->leftJoin('mm_studentuser as s', 's.studentId', '=', 'p.studentid')
            ->groupBy('p.studentid', 's.first_name', 's.last_name', 's.email', 's.last_login')
            ->select('s.email', 's.last_login')
            ->selectRaw($name . ' as name')
            ->selectRaw('COUNT(*) as active_packages')
            ->selectRaw('MAX(p.expire_date) as expires')
            ->orderByDesc('expires')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name ?: '—',
                'email' => $r->email ?: '—',
                'active_packages' => (int) $r->active_packages,
                'expires' => $r->expires ? Carbon::parse($r->expires)->format('d M Y') : '—',
                'last_login' => $this->relativeLogin($r->last_login),
            ])->all();

        return [
            'title' => 'Active students',
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'active_packages', 'label' => 'Active packages'],
                ['key' => 'expires', 'label' => 'Latest expiry'],
                ['key' => 'last_login', 'label' => 'Last active'],
            ],
            'rows' => $rows,
            'total' => $total,
        ];
    }

    private function kpiMockTests(string $name, int $limit): array
    {
        $base = DB::table('mm_mock_test_results as r')->where('r.create_date', '>=', now()->subDays(30));
        $total = (clone $base)->count();

        $rows = (clone $base)
            ->leftJoin('mm_studentuser as s', 's.studentId', '=', 'r.studentId')
            ->selectRaw($name . ' as name')
            ->addSelect('s.email', 'r.mock_series', 'r.mock_test_id', 'r.overall_score', 'r.create_date')
            ->orderByDesc('r.create_date')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name ?: '—',
                'email' => $r->email ?: '—',
                'mock_series' => $r->mock_series ?: '—',
                'mock_test_id' => $r->mock_test_id ?: '—',
                'overall_score' => $r->overall_score !== null ? number_format((float) $r->overall_score, 1) : '—',
                'taken_on' => Carbon::parse($r->create_date)->format('d M Y'),
            ])->all();

        return [
            'title' => 'Mock tests taken — last 30 days',
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'mock_series', 'label' => 'Series'],
                ['key' => 'mock_test_id', 'label' => 'Test'],
                ['key' => 'overall_score', 'label' => 'Score'],
                ['key' => 'taken_on', 'label' => 'Taken on'],
            ],
            'rows' => $rows,
            'total' => $total,
        ];
    }

    private function kpiAvgScore(string $name, int $limit): array
    {
        $base = DB::table('mm_mock_test_results as r')->whereNotNull('r.overall_score');
        $total = (clone $base)->distinct('r.studentId')->count('r.studentId');

        $rows = (clone $base)
            ->leftJoin('mm_studentuser as s', 's.studentId', '=', 'r.studentId')
            ->groupBy('r.studentId', 's.first_name', 's.last_name', 's.email')
            ->selectRaw($name . ' as name')
            ->addSelect('s.email')
            ->selectRaw('COUNT(*) as tests')
            ->selectRaw('AVG(r.overall_score) as avg_score')
            ->selectRaw('MAX(r.overall_score) as best_score')
            ->orderByDesc('avg_score')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name ?: '—',
                'email' => $r->email ?: '—',
                'tests' => (int) $r->tests,
                'avg_score' => number_format((float) $r->avg_score, 1),
                'best_score' => number_format((float) $r->best_score, 1),
            ])->all();

        return [
            'title' => 'Average overall score — by student',
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'tests', 'label' => 'Tests'],
                ['key' => 'avg_score', 'label' => 'Average'],
                ['key' => 'best_score', 'label' => 'Best'],
            ],
            'rows' => $rows,
            'total' => $total,
        ];
    }

    private function kpiActivePackages(int $limit): array
    {
        $rows = DB::table('mm_purchases')
            ->select('product')
            ->selectRaw('COUNT(*) as purchases')
            ->selectRaw('SUM(CASE WHEN is_expired = 0 THEN 1 ELSE 0 END) as active')
            ->groupBy('product')
            ->orderByDesc('active')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'product' => $r->product ?: '—',
                'active' => (int) $r->active,
                'purchases' => (int) $r->purchases,
                'active_pct' => $r->purchases > 0 ? round($r->active / $r->purchases * 100) . '%' : '—',
            ])->all();

        return [
            'title' => 'Active packages — by subscription',
            'columns' => [
                ['key' => 'product', 'label' => 'Package'],
                ['key' => 'active', 'label' => 'Active'],
                ['key' => 'purchases', 'label' => 'All purchases'],
                ['key' => 'active_pct', 'label' => '% active'],
            ],
            'rows' => $rows,
            'total' => count($rows),
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
            ['key' => 'high_scorers',    'name' => 'High Scorers',          'meta' => number_format($highScorers) . ' students · avg score 75+'],
            ['key' => 'expiring_soon',   'name' => 'Package Expiring Soon', 'meta' => number_format($expiringSoon) . ' students · next 7 days'],
            ['key' => 'renewal_watch',   'name' => 'Renewal Watch',         'meta' => number_format($expiringWatch) . ' students · next 8-30 days'],
            ['key' => 'new_students',    'name' => 'New Students',          'meta' => number_format($newStudents) . ' students · last 14 days'],
        ];
    }

    /**
     * Drill-down rows behind one Audience segment card — the same rules
     * audienceSegments() counts with, listed out.
     *
     * @return array{title: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array>, total: int}
     */
    public function segmentDetails(string $key, int $limit = 300): array
    {
        $name = "TRIM(CONCAT(COALESCE(s.first_name,''), ' ', COALESCE(s.last_name,'')))";

        return match ($key) {
            'high_scorers' => $this->segmentHighScorers($name, $limit),
            'expiring_soon' => $this->segmentExpiring($name, now(), now()->addDays(7), 'Package expiring soon — next 7 days', $limit),
            'renewal_watch' => $this->segmentExpiring($name, now()->addDays(8), now()->addDays(30), 'Renewal watch — next 8–30 days', $limit),
            'new_students' => $this->segmentNewStudents($name, $limit),
            default => ['title' => 'Unknown', 'columns' => [], 'rows' => [], 'total' => 0],
        };
    }

    private function segmentHighScorers(string $name, int $limit): array
    {
        $base = DB::table('mm_mock_test_results as r')->whereNotNull('r.overall_score');
        $total = (clone $base)->select('r.studentId')->groupBy('r.studentId')->havingRaw('AVG(r.overall_score) >= 75')->get()->count();

        $rows = (clone $base)
            ->leftJoin('mm_studentuser as s', 's.studentId', '=', 'r.studentId')
            ->groupBy('r.studentId', 's.first_name', 's.last_name', 's.email')
            ->havingRaw('AVG(r.overall_score) >= 75')
            ->selectRaw($name . ' as name')
            ->addSelect('s.email')
            ->selectRaw('COUNT(*) as tests')
            ->selectRaw('AVG(r.overall_score) as avg_score')
            ->orderByDesc('avg_score')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name ?: '—',
                'email' => $r->email ?: '—',
                'tests' => (int) $r->tests,
                'avg_score' => number_format((float) $r->avg_score, 1),
            ])->all();

        return [
            'title' => 'High scorers — avg score 75+',
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'tests', 'label' => 'Tests'],
                ['key' => 'avg_score', 'label' => 'Average'],
            ],
            'rows' => $rows,
            'total' => $total,
        ];
    }

    private function segmentExpiring(string $name, Carbon $from, Carbon $to, string $title, int $limit): array
    {
        $base = DB::table('mm_purchases as p')
            ->where('p.is_expired', 0)
            ->whereBetween('p.expire_date', [$from, $to]);
        $total = (clone $base)->distinct('p.studentid')->count('p.studentid');

        $rows = (clone $base)
            ->leftJoin('mm_studentuser as s', 's.studentId', '=', 'p.studentid')
            ->select('p.product', 'p.expire_date', 's.email')
            ->selectRaw($name . ' as name')
            ->orderBy('p.expire_date')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name ?: '—',
                'email' => $r->email ?: '—',
                'product' => $r->product ?: '—',
                'expires' => Carbon::parse($r->expire_date)->format('d M Y'),
            ])->all();

        return [
            'title' => $title,
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'product', 'label' => 'Package'],
                ['key' => 'expires', 'label' => 'Expires'],
            ],
            'rows' => $rows,
            'total' => $total,
        ];
    }

    private function segmentNewStudents(string $name, int $limit): array
    {
        $base = DB::table('mm_studentuser as s')
            ->whereNotNull('s.create_date')
            ->where('s.create_date', '>=', now()->subDays(14)->toDateString());
        $total = (clone $base)->count();

        $rows = (clone $base)
            ->select('s.email', 's.country_code', 's.create_date')
            ->selectRaw($name . ' as name')
            ->orderByDesc('s.create_date')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name ?: '—',
                'email' => $r->email ?: '—',
                'country' => $r->country_code ?: '—',
                'joined' => Carbon::parse($r->create_date)->format('d M Y'),
            ])->all();

        return [
            'title' => 'New students — last 14 days',
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'country', 'label' => 'Country'],
                ['key' => 'joined', 'label' => 'Joined'],
            ],
            'rows' => $rows,
            'total' => $total,
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
        return array_column($this->insightItems(), 'text');
    }

    /**
     * Key Insights as clickable items — each text is the same sentence
     * insights() returns, and each key drives its drill-down list.
     *
     * @return array<int, array{key: string, text: string}>
     */
    public function insightItems(): array
    {
        $avgScore = DB::table('mm_mock_test_results')->whereNotNull('overall_score')->avg('overall_score');
        $totalPurchases = DB::table('mm_purchases')->count();
        $expiredPct = $totalPurchases > 0
            ? round(DB::table('mm_purchases')->where('is_expired', 1)->count() / $totalPurchases * 100)
            : 0;
        $recentTests = DB::table('mm_mock_test_results')->where('create_date', '>=', now()->subDays(7))->count();
        $failedPayments = DB::table('mm_payments')->where('status', 0)->count();
        $totalPayments = DB::table('mm_payments')->count();

        return array_values(array_filter([
            $avgScore ? ['key' => 'avg_score', 'text' => sprintf('Average mock test overall score across all results is %.1f.', $avgScore)] : null,
            ['key' => 'expired_packages', 'text' => sprintf('%d%% of all issued packages have already expired.', $expiredPct)],
            ['key' => 'recent_mock_tests', 'text' => sprintf('%s mock tests were taken in the last 7 days.', number_format($recentTests))],
            $totalPayments > 0 ? ['key' => 'failed_payments', 'text' => sprintf('%d of %d payments (%.0f%%) are marked unpaid/failed.', $failedPayments, $totalPayments, $totalPayments ? $failedPayments / $totalPayments * 100 : 0)] : null,
        ]));
    }

    /**
     * Drill-down rows behind one Key Insight — the same rows each sentence
     * is counted from.
     *
     * @return array{title: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array>, total: int}
     */
    public function insightDetails(string $key, int $limit = 300): array
    {
        $name = "TRIM(CONCAT(COALESCE(s.first_name,''), ' ', COALESCE(s.last_name,'')))";

        return match ($key) {
            'avg_score' => $this->insightAvgScore($name, $limit),
            'expired_packages' => $this->insightExpiredPackages($name, $limit),
            'recent_mock_tests' => $this->insightRecentTests($name, $limit),
            'failed_payments' => $this->insightFailedPayments($name, $limit),
            default => ['title' => 'Unknown', 'columns' => [], 'rows' => [], 'total' => 0],
        };
    }

    private function insightAvgScore(string $name, int $limit): array
    {
        $base = DB::table('mm_mock_test_results as r')->whereNotNull('r.overall_score');
        $total = (clone $base)->count();

        $rows = (clone $base)
            ->leftJoin('mm_studentuser as s', 's.studentId', '=', 'r.studentId')
            ->selectRaw($name . ' as name')
            ->addSelect('s.email', 'r.mock_series', 'r.overall_score', 'r.create_date')
            ->orderByDesc('r.create_date')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name ?: '—',
                'email' => $r->email ?: '—',
                'mock_series' => $r->mock_series ?: '—',
                'score' => number_format((float) $r->overall_score, 1),
                'taken_on' => Carbon::parse($r->create_date)->format('d M Y'),
            ])->all();

        return [
            'title' => 'Average mock test score — all results',
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'mock_series', 'label' => 'Series'],
                ['key' => 'score', 'label' => 'Score'],
                ['key' => 'taken_on', 'label' => 'Taken on'],
            ],
            'rows' => $rows,
            'total' => $total,
        ];
    }

    private function insightExpiredPackages(string $name, int $limit): array
    {
        $base = DB::table('mm_purchases as p')->where('p.is_expired', 1);
        $total = (clone $base)->count();

        $rows = (clone $base)
            ->leftJoin('mm_studentuser as s', 's.studentId', '=', 'p.studentid')
            ->select('p.product', 'p.expire_date', 's.email')
            ->selectRaw($name . ' as name')
            ->orderByDesc('p.expire_date')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name ?: '—',
                'email' => $r->email ?: '—',
                'product' => $r->product ?: '—',
                'expired_on' => $r->expire_date ? Carbon::parse($r->expire_date)->format('d M Y') : '—',
            ])->all();

        return [
            'title' => 'Expired packages — all purchases',
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'product', 'label' => 'Package'],
                ['key' => 'expired_on', 'label' => 'Expired on'],
            ],
            'rows' => $rows,
            'total' => $total,
        ];
    }

    private function insightRecentTests(string $name, int $limit): array
    {
        $base = DB::table('mm_mock_test_results as r')->where('r.create_date', '>=', now()->subDays(7));
        $total = (clone $base)->count();

        $rows = (clone $base)
            ->leftJoin('mm_studentuser as s', 's.studentId', '=', 'r.studentId')
            ->selectRaw($name . ' as name')
            ->addSelect('s.email', 'r.mock_series', 'r.mock_test_id', 'r.overall_score', 'r.create_date')
            ->orderByDesc('r.create_date')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name ?: '—',
                'email' => $r->email ?: '—',
                'mock_series' => $r->mock_series ?: '—',
                'mock_test_id' => $r->mock_test_id ?: '—',
                'score' => $r->overall_score !== null ? number_format((float) $r->overall_score, 1) : '—',
                'taken_on' => Carbon::parse($r->create_date)->format('d M Y'),
            ])->all();

        return [
            'title' => 'Mock tests taken — last 7 days',
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'mock_series', 'label' => 'Series'],
                ['key' => 'mock_test_id', 'label' => 'Test'],
                ['key' => 'score', 'label' => 'Score'],
                ['key' => 'taken_on', 'label' => 'Taken on'],
            ],
            'rows' => $rows,
            'total' => $total,
        ];
    }

    private function insightFailedPayments(string $name, int $limit): array
    {
        $base = DB::table('mm_payments as pay')->where('pay.status', 0);
        $total = (clone $base)->count();

        $rows = (clone $base)
            ->leftJoin('mm_studentuser as s', 's.studentId', '=', 'pay.buyerid')
            ->select('pay.product', 'pay.amount', 'pay.create_date', 's.email')
            ->selectRaw($name . ' as name')
            ->orderByDesc('pay.create_date')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name ?: '—',
                'email' => $r->email ?: '—',
                'product' => $r->product ?: '—',
                'amount' => '$' . number_format((float) $r->amount, 0),
                'created_on' => Carbon::parse($r->create_date)->format('d M Y'),
            ])->all();

        return [
            'title' => 'Unpaid / failed payments',
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'product', 'label' => 'Package'],
                ['key' => 'amount', 'label' => 'Amount'],
                ['key' => 'created_on', 'label' => 'Created on'],
            ],
            'rows' => $rows,
            'total' => $total,
        ];
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

    // ─────────────────────────────────────────────────────────────────────
    //  Sales · Close & grow
    //
    //  Package types (mm_packages):
    //    • free trial  — usage_type = 'free' ("1 FREE MOCK TEST")
    //    • paid        — cost > 0 (memberships, Practice Pro, Success Bundle)
    //    • enrolled    — cost = 0 and not 'free' (granted to enrolled
    //                    coaching students, so never treated as prospects)
    //  mm_payments.status: 1 = paid, 0 = checkout started but not paid.
    //  Results are memoised per request — the activity scans touch the
    //  large mm_mock_test_logs / mm_login_history tables.
    // ─────────────────────────────────────────────────────────────────────

    private array $memo = [];

    private function memo(string $key, callable $fn)
    {
        return $this->memo[$key] ??= $fn();
    }

    private function freeTrialPackageIds(): array
    {
        return $this->memo('pk_free', fn () => DB::table('mm_packages')->where('usage_type', 'free')->pluck('packageid')->all());
    }

    private function paidPackageIds(): array
    {
        return $this->memo('pk_paid', fn () => DB::table('mm_packages')->where('cost', '>', 0)->pluck('packageid')->all());
    }

    private function enrolledPackageIds(): array
    {
        return $this->memo('pk_enrolled', fn () => DB::table('mm_packages')->where('cost', 0)->where('usage_type', '!=', 'free')->pluck('packageid')->all());
    }

    /** Student ids that must never be shown: soft-deleted, in mm_deleted_students, or throwaway test emails. */
    private function excludedStudentIds(): array
    {
        return $this->memo('excluded', function () {
            $deleted = DB::table('mm_deleted_students')->pluck('studentId')->all();
            $softDeleted = DB::table('mm_studentuser')
                ->where(function ($q) {
                    $q->whereNotNull('deleted_at')
                      ->orWhere('email', 'like', '%@yopmail.%')
                      ->orWhere('email', 'like', '%@mailinator.%');
                })
                ->pluck('studentId')->all();

            return array_flip(array_merge($deleted, $softDeleted));
        });
    }

    /**
     * Per-student activity signals for a set of students:
     * tests attempted / logins / notifications seen in a recent window,
     * scored results, average score, and last activity time.
     */
    private function activitySignals(array $ids, int $days = 14): array
    {
        if (empty($ids)) {
            return [];
        }
        $w = $days >= 30 ? '30' : '14';
        $act = $this->recentActivity();
        $avg = $this->avgScoresByStudent($ids);

        $out = [];
        foreach ($ids as $id) {
            $t = $act['tests'][$id] ?? null;
            $l = $act['logins'][$id] ?? null;
            $out[$id] = [
                'tests' => (int) ($t->{'tests' . $w} ?? 0),
                'results' => (int) ($act['results'][$id]->{'res' . $w} ?? 0),
                'logins' => (int) ($l->{'logins' . $w} ?? 0),
                'seen' => (int) ($act['seen'][$id] ?? 0),
                'avg' => isset($avg[$id]) ? (int) round($avg[$id]) : null,
                'last_at' => max($t->last_at ?? null, $l->last_at ?? null) ?: null,
            ];
        }

        return $out;
    }

    /**
     * One grouped scan per activity table for the last 30 days, with 14-day
     * and 30-day counts side by side — shared by every Close & grow list so
     * the large log tables are read once per request, not once per list.
     */
    private const ACTIVITY_CACHE_KEY = 'mm:close-grow:activity';

    /** Called after a Mock Master sync so the next page load rescans fresh data. */
    public static function forgetCachedActivity(): void
    {
        Cache::forget(self::ACTIVITY_CACHE_KEY);
    }

    private function recentActivity(): array
    {
        // Cached for 10 minutes (and cleared on sync): the mm_* tables only
        // change when Sync Data runs, and this scan is the slow part.
        return $this->memo('activity', fn () => Cache::remember(self::ACTIVITY_CACHE_KEY, 600, function () {
            $d30 = now()->subDays(30);
            $d14 = now()->subDays(14)->toDateTimeString();

            return [
                'tests' => DB::table('mm_mock_test_logs')->where('create_date', '>=', $d30)
                    ->selectRaw('studentId, COUNT(DISTINCT mock_test_id) as tests30, COUNT(DISTINCT CASE WHEN create_date >= ? THEN mock_test_id END) as tests14, MAX(create_date) as last_at', [$d14])
                    ->groupBy('studentId')->get()->keyBy('studentId')->all(),
                'logins' => DB::table('mm_login_history')->where('login_time', '>=', $d30)
                    ->selectRaw('user_id, COUNT(*) as logins30, SUM(login_time >= ?) as logins14, MAX(login_time) as last_at', [$d14])
                    ->groupBy('user_id')->get()->keyBy('user_id')->all(),
                'results' => DB::table('mm_mock_test_results')->where('create_date', '>=', $d30)
                    ->selectRaw('studentId, COUNT(*) as res30, SUM(create_date >= ?) as res14', [$d14])
                    ->groupBy('studentId')->get()->keyBy('studentId')->all(),
                'seen' => DB::table('mm_notifications_seen')->where('seen_at', '>=', $d30)
                    ->selectRaw('student_id, COUNT(*) as cnt')->groupBy('student_id')->pluck('cnt', 'student_id')->all(),
            ];
        }));
    }

    /** Short human summary of activity, e.g. "4 tests · 6 logins (14d) · avg 61". */
    private function activityText(array $a): string
    {
        $parts = [];
        $parts[] = $a['tests'] . ' test' . ($a['tests'] === 1 ? '' : 's');
        $parts[] = $a['logins'] . ' login' . ($a['logins'] === 1 ? '' : 's') . ' (14d)';
        if ($a['avg'] !== null) {
            $parts[] = 'avg score ' . $a['avg'];
        }

        return implode(' · ', $parts);
    }

    /** Extra context flags: an upcoming scheduled reminder email, or a past coupon redemption. */
    private function contactFlags(int $id): string
    {
        $scheduled = $this->memo('scheduled', fn () => array_flip(DB::table('mm_scheduled_emails')
            ->where('scheduled_at', '>=', now())->distinct()->pluck('student_id')->all()));
        $coupon = $this->memo('coupon', fn () => array_flip(DB::table('mm_coupon_usage')->distinct()->pluck('studentid')->all()));

        $flags = [];
        if (isset($scheduled[$id])) {
            $flags[] = 'reminder email already scheduled';
        }
        if (isset($coupon[$id])) {
            $flags[] = 'has used a coupon before';
        }

        return $flags ? ' · ' . implode(' · ', $flags) : '';
    }

    private function studentRows(array $ids): \Illuminate\Support\Collection
    {
        return DB::table('mm_studentuser')->whereIn('studentId', $ids)
            ->get(['studentId', 'first_name', 'last_name', 'email', 'phone', 'country_code', 'desired_band', 'profile_completed', 'otp_verified', 'last_login'])
            ->keyBy('studentId');
    }

    private function studentName($s, $id): string
    {
        return $s ? (trim($s->first_name . ' ' . $s->last_name) ?: 'Student #' . $id) : 'Student #' . $id;
    }

    /** Students with an unpaid checkout (mm_payments.status = 0) still open, keyed by student id. */
    private function openCheckouts(int $days = 30): array
    {
        return $this->memo('checkouts_' . $days, function () use ($days) {
            $rows = DB::table('mm_payments')
                ->where('status', 0)
                ->where('create_date', '>=', now()->subDays($days))
                ->orderByDesc('create_date')
                ->get(['buyerid', 'product', 'amount', 'create_date']);

            $paidAfter = DB::table('mm_payments')->where('status', 1)
                ->whereIn('buyerid', $rows->pluck('buyerid')->unique()->all())
                ->selectRaw('buyerid, MAX(create_date) as last_paid')->groupBy('buyerid')->pluck('last_paid', 'buyerid');

            $activePaid = array_flip(DB::table('mm_purchases')
                ->whereIn('studentid', $rows->pluck('buyerid')->unique()->all())
                ->whereIn('productid', $this->paidPackageIds())
                ->where('expire_date', '>', now())
                ->pluck('studentid')->all());

            $excluded = $this->excludedStudentIds();
            $out = [];
            foreach ($rows as $r) {
                $id = $r->buyerid;
                if (isset($excluded[$id]) || isset($activePaid[$id])) {
                    continue;
                }
                if (isset($paidAfter[$id]) && $paidAfter[$id] >= $r->create_date) {
                    continue; // they completed a payment afterwards
                }
                if (!isset($out[$id])) {
                    $out[$id] = ['product' => $r->product, 'amount' => (int) $r->amount, 'at' => $r->create_date, 'attempts' => 0];
                }
                $out[$id]['attempts']++;
            }

            return $out;
        });
    }

    /**
     * Close — free-trial students most likely to buy, ranked by a conversion
     * score built from real activity. Only students whose purchases are all
     * the free trial (no paid package, not enrolled in coaching) and who were
     * active in the last 14 days are considered.
     *
     * Score (0–100): distinct mock tests attempted (10 each, max 30)
     *  + scored result recorded (15) + logins (5 each, max 20)
     *  + notifications seen in 30d (5) + profile completed (10)
     *  + phone verified (5) + open unpaid checkout (15).
     */
    private function convertCandidatesAll(): array
    {
        return $this->memo('convert_all', function () {
            $act = $this->recentActivity();
            $active = array_values(array_unique(array_merge(
                array_keys(array_filter($act['tests'], fn ($r) => $r->tests14 > 0)),
                array_keys(array_filter($act['logins'], fn ($r) => $r->logins14 > 0)),
                array_keys(array_filter($act['results'], fn ($r) => $r->res14 > 0)),
            )));
            if (empty($active)) {
                return [];
            }

            $trial = array_flip(DB::table('mm_purchases')->whereIn('studentid', $active)
                ->whereIn('productid', $this->freeTrialPackageIds())->distinct()->pluck('studentid')->all());
            $other = array_flip(DB::table('mm_purchases')->whereIn('studentid', $active)
                ->whereIn('productid', array_merge($this->paidPackageIds(), $this->enrolledPackageIds()))
                ->distinct()->pluck('studentid')->all());
            $excluded = $this->excludedStudentIds();

            $ids = array_values(array_filter($active, fn ($id) => isset($trial[$id]) && !isset($other[$id]) && !isset($excluded[$id])));
            if (empty($ids)) {
                return [];
            }

            $students = $this->studentRows($ids);
            $signals = $this->activitySignals($ids);
            $checkouts = $this->openCheckouts();
            $rows = [];

            foreach ($ids as $id) {
                $s = $students[$id] ?? null;
                if (!$s) {
                    continue;
                }
                $a = $signals[$id];
                $hasCheckout = isset($checkouts[$id]);
                $score = min(30, $a['tests'] * 10) + ($a['results'] > 0 ? 15 : 0) + min(20, $a['logins'] * 5)
                    + ($a['seen'] > 0 ? 5 : 0) + ($s->profile_completed ? 10 : 0) + ($s->otp_verified ? 5 : 0)
                    + ($hasCheckout ? 15 : 0);
                // desired_band is an IELTS-style band (5–9), not a PTE score,
                // so it's shown for context only — never compared to the score.
                $band = $s->desired_band ? (int) $s->desired_band : null;

                $why = $this->activityText($a);
                if ($band) {
                    $why .= ' · target band ' . $band;
                }
                if ($hasCheckout) {
                    $why .= ' · started checkout for ' . $checkouts[$id]['product'];
                }
                $why .= $this->contactFlags($id);

                $rows[] = [
                    'id' => $id,
                    'name' => $this->studentName($s, $id),
                    'score' => min(100, $score),
                    'signals' => $why,
                    'lastActive' => $this->relativeLogin($a['last_at']),
                    'action' => $hasCheckout ? 'Call today — help them finish checkout'
                        : ($a['avg'] === null ? 'Call — help them take a full scored mock'
                        : ($a['avg'] >= 65 ? 'Pitch a 1-month plan — scoring ' . $a['avg'] . ', ready to polish'
                        : 'Pitch a 3-month plan — scoring ' . $a['avg'] . ', needs steady practice')),
                    'email' => $s->email ?: null,
                    'phone' => $this->formatPhone($s->country_code, $s->phone),
                ];
            }

            usort($rows, fn ($x, $y) => [$y['score'], $x['id']] <=> [$x['score'], $y['id']]);

            return $rows;
        });
    }

    /** Close — ranked free-trial students likely to convert (kept for the Ask Mira lists and /more). */
    public function salesCloseCandidates(int $limit = 5, int $offset = 0): array
    {
        return array_map(fn ($r) => array_diff_key($r, ['id' => 1]), array_slice($this->convertCandidatesAll(), $offset, $limit));
    }

    /** Close — open unpaid checkouts in the last 30 days, most recent first. */
    private function abandonedCheckoutsAll(): array
    {
        return $this->memo('abandoned_all', function () {
            $checkouts = $this->openCheckouts();
            if (empty($checkouts)) {
                return [];
            }
            $ids = array_keys($checkouts);
            $students = $this->studentRows($ids);
            $signals = $this->activitySignals($ids);
            $rows = [];
            foreach ($checkouts as $id => $c) {
                $s = $students[$id] ?? null;
                if (!$s) {
                    continue;
                }
                $rows[] = [
                    'name' => $this->studentName($s, $id),
                    'package' => $c['product'],
                    'amount' => '$' . number_format($c['amount']),
                    'attempted' => Carbon::parse($c['at'])->diffForHumans() . ($c['attempts'] > 1 ? ' (' . $c['attempts'] . ' tries)' : ''),
                    'signals' => $this->activityText($signals[$id]) . $this->contactFlags($id),
                    'action' => Carbon::parse($c['at'])->gt(now()->subDays(2)) ? 'Call today — payment still fresh' : 'WhatsApp a payment link and offer help',
                    'email' => $s->email ?: null,
                    'phone' => $this->formatPhone($s->country_code, $s->phone),
                    '_amount' => $c['amount'],
                ];
            }

            return $rows;
        });
    }

    public function salesAbandonedCheckouts(int $limit = 10, int $offset = 0): array
    {
        return array_map(fn ($r) => array_diff_key($r, ['_amount' => 1]), array_slice($this->abandonedCheckoutsAll(), $offset, $limit));
    }

    /**
     * Retention · Renew & win back — active paid packages expiring in the
     * next 30 days. Students who are still practising get an upgrade
     * suggestion; quiet ones a renewal check-in.
     */
    private function renewalsDueAll(): array
    {
        return $this->memo('renewals_all', function () {
            $rows = DB::table('mm_purchases as p')
                ->join('mm_packages as k', 'k.packageid', '=', 'p.productid')
                ->leftJoin('mm_payments as pay', 'pay.id', '=', 'p.paymentid')
                ->where('k.cost', '>', 0)
                ->whereBetween('p.expire_date', [now(), now()->addDays(30)])
                ->orderBy('p.expire_date')
                ->get(['p.studentid', 'p.product', 'p.expire_date', 'k.cost', 'pay.amount']);

            // A later paid package means they've already renewed.
            $later = DB::table('mm_purchases')->whereIn('studentid', $rows->pluck('studentid')->unique()->all())
                ->whereIn('productid', $this->paidPackageIds())->where('expire_date', '>', now()->addDays(30))
                ->distinct()->pluck('studentid')->flip();
            $excluded = $this->excludedStudentIds();
            $rows = $rows->reject(fn ($r) => isset($later[$r->studentid]) || isset($excluded[$r->studentid]))->unique('studentid')->values();
            if ($rows->isEmpty()) {
                return [];
            }

            $ids = $rows->pluck('studentid')->all();
            $students = $this->studentRows($ids);
            $signals = $this->activitySignals($ids);
            $out = [];
            foreach ($rows as $r) {
                $s = $students[$r->studentid] ?? null;
                if (!$s) {
                    continue;
                }
                $a = $signals[$r->studentid];
                $days = (int) ceil(now()->diffInHours(Carbon::parse($r->expire_date)) / 24);
                $value = (int) ($r->amount ?: $r->cost);
                $busy = $a['tests'] >= 2 || $a['logins'] >= 4;
                $out[] = [
                    'name' => $this->studentName($s, $r->studentid),
                    'package' => $r->product,
                    'expires' => $days <= 0 ? 'today' : 'in ' . $days . ' day' . ($days === 1 ? '' : 's'),
                    'amount' => '$' . number_format($value),
                    'signals' => $this->activityText($a),
                    'action' => $busy ? 'Upsell a longer plan — still practising actively' : 'Renewal check-in — activity has slowed',
                    'email' => $s->email ?: null,
                    'phone' => $this->formatPhone($s->country_code, $s->phone),
                    '_amount' => $value,
                ];
            }

            return $out;
        });
    }

    public function retentionRenewalsDue(int $limit = 10, int $offset = 0): array
    {
        return array_map(fn ($r) => array_diff_key($r, ['_amount' => 1]), array_slice($this->renewalsDueAll(), $offset, $limit));
    }

    /**
     * Retention · Renew & win back — paid package expired in the last 60
     * days, nothing active now, but the student still logs in or practises
     * (last 30 days).
     */
    private function winBackAll(): array
    {
        return $this->memo('winback_all', function () {
            $rows = DB::table('mm_purchases as p')
                ->join('mm_packages as k', 'k.packageid', '=', 'p.productid')
                ->leftJoin('mm_payments as pay', 'pay.id', '=', 'p.paymentid')
                ->where('k.cost', '>', 0)
                ->whereBetween('p.expire_date', [now()->subDays(60), now()])
                ->orderByDesc('p.expire_date')
                ->get(['p.studentid', 'p.product', 'p.expire_date', 'k.cost', 'pay.amount']);

            $ids = $rows->pluck('studentid')->unique()->all();
            $activeNow = DB::table('mm_purchases')->whereIn('studentid', $ids)
                ->whereIn('productid', array_merge($this->paidPackageIds(), $this->enrolledPackageIds()))
                ->where('expire_date', '>', now())->distinct()->pluck('studentid')->flip();
            $excluded = $this->excludedStudentIds();
            $rows = $rows->reject(fn ($r) => isset($activeNow[$r->studentid]) || isset($excluded[$r->studentid]))->unique('studentid')->values();
            if ($rows->isEmpty()) {
                return [];
            }

            $ids = $rows->pluck('studentid')->all();
            $students = $this->studentRows($ids);
            $signals = $this->activitySignals($ids, 30);
            $out = [];
            foreach ($rows as $r) {
                $s = $students[$r->studentid] ?? null;
                $a = $signals[$r->studentid];
                if (!$s || ($a['tests'] === 0 && $a['logins'] === 0)) {
                    continue; // only students who are still coming back
                }
                $days = (int) Carbon::parse($r->expire_date)->diffInDays(now());
                $value = (int) ($r->amount ?: $r->cost);
                $out[] = [
                    'name' => $this->studentName($s, $r->studentid),
                    'package' => $r->product,
                    'expired' => $days === 0 ? 'today' : $days . ' day' . ($days === 1 ? '' : 's') . ' ago',
                    'amount' => '$' . number_format($value),
                    'signals' => str_replace('(14d)', '(30d)', $this->activityText($a)),
                    'action' => $a['tests'] > 0 ? 'Still practising — offer a renewal today' : 'Logging in without a plan — send a win-back offer',
                    'email' => $s->email ?: null,
                    'phone' => $this->formatPhone($s->country_code, $s->phone),
                    '_amount' => $value,
                ];
            }

            return $out;
        });
    }

    public function retentionWinBack(int $limit = 10, int $offset = 0): array
    {
        return array_map(fn ($r) => array_diff_key($r, ['_amount' => 1]), array_slice($this->winBackAll(), $offset, $limit));
    }

    /** Headline counts for Sales · Close & grow (convert + open checkouts). */
    public function closeGrowSummary(): array
    {
        $abandoned = $this->abandonedCheckoutsAll();

        return [
            'convert' => count($this->convertCandidatesAll()),
            'abandoned' => count($abandoned),
            'abandoned_value' => array_sum(array_column($abandoned, '_amount')),
        ];
    }

    /** Headline counts and revenue for Retention · Renew & win back. */
    public function renewWinBackSummary(): array
    {
        $renewals = $this->renewalsDueAll();
        $winback = $this->winBackAll();

        return [
            'renewals' => count($renewals),
            'renewals_value' => array_sum(array_column($renewals, '_amount')),
            'winback' => count($winback),
            'winback_value' => array_sum(array_column($winback, '_amount')),
        ];
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
            'close_grow_summary' => $this->closeGrowSummary(),
            'abandoned_checkouts' => $this->salesAbandonedCheckouts(10),
            'renew_win_back_summary' => $this->renewWinBackSummary(),
            'renewals_due' => $this->retentionRenewalsDue(10),
            'win_back' => $this->retentionWinBack(10),

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
