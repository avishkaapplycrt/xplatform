<?php

namespace App\Services;

use App\Models\ClientDataSource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EdTechBehavioralEngine
{
    private ?ClientDataSource $dataSource = null;
    private array $tableMap = [];
    private array $columnMap = [];
    private bool $connected = false;

    // Hardcoded table mappings for your specific database
    private const TABLE_MAPPINGS = [
        'students'      => 'studentuser',
        'login_logs'    => 'login_history',
        'quiz_attempts' => 'mock_test_results',  // ← CORRECT
        'payments'      => 'payments',
    ];

    // Exact column mappings based on your schema
    private const COLUMN_OVERRIDES = [
        'studentuser' => [
            'pk'        => 'studentId',
            'name'      => 'first_name',
            'last_name' => 'last_name',
            'email'     => 'email',
            'date'      => 'create_date',
            'last_login'=> 'last_login',
        ],
        'login_history' => [
            'user_id'   => 'user_id',
            'date'      => 'login_time',
        ],
        'mock_test_results' => [
            'user_id'   => 'studentId',
            'score'     => 'overall_score',
            'date'      => 'create_date',
            'speaking_score' => 'speaking_score',
            'reading_score' => 'reading_score',
            'listening_score' => 'listening_score',
            'writing_score' => 'writing_score',
        ],
        'payments' => [
            'user_id'   => 'buyerid',
            'amount'    => 'amount',
            'status'    => 'status',
            'date'      => 'create_date',
        ],
    ];

    public function __construct(?ClientDataSource $dataSource = null)
    {
        $this->dataSource = $dataSource;
        if ($dataSource) {
            $this->discoverSchema();
        }
    }

    public function discoverSchema(): bool
    {
        if (!$this->dataSource) return false;

        try {
            $config = [
                'driver'    => $this->dataSource->db_type,
                'host'      => $this->dataSource->host,
                'port'      => $this->dataSource->port,
                'database'  => $this->dataSource->database_name,
                'username'  => $this->dataSource->username,
                'password'  => $this->dataSource->password ?? '',
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ];

            config()->set('database.connections.edtech_temp', $config);
            $conn = DB::connection('edtech_temp');

            // Verify required tables exist
            $tables = $conn->select("SHOW TABLES");
            $allTables = [];
            foreach ($tables as $table) {
                $vals = array_values((array)$table);
                $allTables[] = strtolower($vals[0]);
            }

            Log::info('Discovered tables: ' . implode(', ', $allTables));

            // Map required tables
            foreach (self::TABLE_MAPPINGS as $concept => $tableName) {
                if (in_array(strtolower($tableName), $allTables)) {
                    $this->tableMap[$concept] = $tableName;
                    $this->discoverColumns($conn, $tableName, $concept);
                } else {
                    Log::warning("Required table {$tableName} not found for concept {$concept}");
                }
            }

            $this->connected = !empty($this->tableMap['students']);
            
            Log::info('Table map: ' . json_encode($this->tableMap));
            Log::info('Column map: ' . json_encode($this->columnMap));

            return $this->connected;

        } catch (\Exception $e) {
            Log::error('Schema discovery failed: ' . $e->getMessage());
            return false;
        }
    }

    private function discoverColumns($conn, string $table, string $concept): void
    {
        try {
            $columns = $conn->select("SHOW COLUMNS FROM `{$table}`");
            $colNames = [];
            foreach ($columns as $col) {
                $colNames[] = strtolower($col->Field);
            }
            $this->columnMap[$concept] = $colNames;
            Log::info("Columns for {$table} ({$concept}): " . implode(', ', $colNames));
        } catch (\Exception $e) {
            Log::warning("Column discovery failed for {$table}: " . $e->getMessage());
            $this->columnMap[$concept] = [];
        }
    }

    /**
     * Find actual column name from a list of possibilities
     */
    private function findColumn(string $concept, array $possibleNames): ?string
    {
        $cols = $this->columnMap[$concept] ?? [];
        foreach ($possibleNames as $name) {
            if (in_array(strtolower($name), $cols)) {
                return $name;
            }
        }
        return null;
    }

    /**
     * Get column with fallback to override mapping
     */
    private function getColumn(string $concept, string $type, array $fallbacks = []): ?string
    {
        // First check override mapping
        $tableName = $this->tableMap[$concept] ?? null;
        if ($tableName && isset(self::COLUMN_OVERRIDES[$tableName][$type])) {
            $override = self::COLUMN_OVERRIDES[$tableName][$type];
            $cols = $this->columnMap[$concept] ?? [];
            if (in_array(strtolower($override), $cols)) {
                return $override;
            }
        }
        
        // Then try fallbacks
        return $this->findColumn($concept, $fallbacks);
    }

    public function getTable(string $concept): ?string
    {
        return $this->tableMap[$concept] ?? null;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function getStudentProfiles(int $limit = 100): array
    {
        if (!$this->connected) {
            Log::warning('Not connected');
            return [];
        }

        $studentsTable = $this->getTable('students');
        if (!$studentsTable) {
            Log::error('No students table');
            return [];
        }

        $loginLogsTable = $this->getTable('login_logs');
        $quizAttemptsTable = $this->getTable('quiz_attempts');
        $paymentsTable = $this->getTable('payments');

        try {
            $conn = DB::connection('edtech_temp');

            // Get student columns from overrides
            $pkCol = self::COLUMN_OVERRIDES['studentuser']['pk'];        // studentId
            $nameCol = self::COLUMN_OVERRIDES['studentuser']['name'];    // first_name
            $lastNameCol = self::COLUMN_OVERRIDES['studentuser']['last_name']; // last_name
            $emailCol = self::COLUMN_OVERRIDES['studentuser']['email'];  // email
            $dateCol = self::COLUMN_OVERRIDES['studentuser']['date'];    // create_date
            $lastLoginCol = self::COLUMN_OVERRIDES['studentuser']['last_login']; // last_login

            Log::info("Student columns - PK: {$pkCol}, Name: {$nameCol}, LastName: {$lastNameCol}, Email: {$emailCol}, Date: {$dateCol}, LastLogin: {$lastLoginCol}");

            // Build select with CONCAT for full name
            $select = [
                $pkCol . ' as id',
                "CONCAT({$nameCol}, ' ', {$lastNameCol}) as name",
                $emailCol . ' as email',
                $dateCol . ' as joined_at',
                $lastLoginCol . ' as last_login'
            ];

            $students = $conn->table($studentsTable)
                ->selectRaw(implode(', ', $select))
                ->limit($limit)
                ->get();

            Log::info("Fetched " . $students->count() . " students from {$studentsTable}");

            if ($students->isEmpty()) {
                return [];
            }

            $profiles = [];
            foreach ($students as $student) {
                $sid = $student->id;

                // ── LOGIN HISTORY (user_id = studentId) ──
                $loginCount = 0;
                $lastLogin = $student->last_login ?? null;
                
                if ($loginLogsTable) {
                    $loginUserIdCol = self::COLUMN_OVERRIDES['login_history']['user_id']; // user_id
                    $loginDateCol = self::COLUMN_OVERRIDES['login_history']['date'];     // login_time
                    
                    try {
                        $loginCount = $conn->table($loginLogsTable)
                            ->where($loginUserIdCol, $sid)
                            ->where($loginDateCol, '>=', now()->subDays(30))
                            ->count();

                        $dbLastLogin = $conn->table($loginLogsTable)
                            ->where($loginUserIdCol, $sid)
                            ->latest($loginDateCol)
                            ->value($loginDateCol);

                        if ($dbLastLogin) {
                            $lastLogin = $dbLastLogin;
                        }
                    } catch (\Exception $e) {
                        Log::warning("Login query failed for {$sid}: " . $e->getMessage());
                    }
                }

                // ── QUIZ/MOCK TEST RESULTS (user_id = studentId) ──
                $quizAttemptCount = 0;
                $avgQuizScore = 0;
                $bestQuizScore = 0;
                
                if ($quizAttemptsTable) {
                    $quizUserIdCol = self::COLUMN_OVERRIDES['mock_test_results']['user_id']; // studentId
                    $scoreCol = self::COLUMN_OVERRIDES['mock_test_results']['score'];        // overall_score
                    
                    try {
                        $quizStats = $conn->table($quizAttemptsTable)
                            ->where($quizUserIdCol, $sid)
                            ->selectRaw("COUNT(*) as attempt_count, AVG({$scoreCol}) as avg_score, MAX({$scoreCol}) as best_score")
                            ->first();

                        $quizAttemptCount = $quizStats->attempt_count ?? 0;
                        $avgQuizScore = $quizStats->avg_score ?? 0;
                        $bestQuizScore = $quizStats->best_score ?? 0;
                    } catch (\Exception $e) {
                        Log::warning("Quiz query failed for {$sid}: " . $e->getMessage());
                    }
                }

                // ── PAYMENTS (buyerid = studentId) ──
                $totalSpent = 0;
                
                if ($paymentsTable) {
                    $paymentUserIdCol = self::COLUMN_OVERRIDES['payments']['user_id'];  // buyerid
                    $amountCol = self::COLUMN_OVERRIDES['payments']['amount'];         // amount
                    $statusCol = self::COLUMN_OVERRIDES['payments']['status'];         // status
                    
                    try {
                        $query = $conn->table($paymentsTable)
                            ->where($paymentUserIdCol, $sid);
                        
                        // status = 1 means successful payment (based on tinyint)
                        $query->where($statusCol, 1);
                        
                        $totalSpent = $query->sum($amountCol) ?? 0;
                    } catch (\Exception $e) {
                        Log::warning("Payment query failed for {$sid}: " . $e->getMessage());
                    }
                }

                // ── SCORING ──
                $engagementScore = min(100, $loginCount * 5 + ($lastLogin && strtotime($lastLogin) > strtotime('-3 days') ? 30 : 0));
                $completionScore = 0; // No enrollments table in your DB
                $performanceScore = min(100, $avgQuizScore);
                $loyaltyScore = min(100, ($totalSpent / 100) * 10 + ($quizAttemptCount * 2));

                $churnScore = $this->calcChurn($lastLogin, $loginCount, 0, $avgQuizScore);
                $intentScore = $this->calcIntent($lastLogin, 0, $avgQuizScore, $totalSpent);
                $readinessScore = min(100, ($engagementScore * 0.5) + ($performanceScore * 0.5));

                $profiles[] = [
                    'id'                  => $sid,
                    'name'                => $student->name ?? 'Student #' . $sid,
                    'email'               => $student->email ?? '',
                    'joined_at'           => $student->joined_at ?? null,
                    'login_count_30d'     => $loginCount,
                    'last_login'          => $lastLogin,
                    'enrollment_count'    => 0,
                    'completed_courses'   => 0,
                    'avg_progress'        => 0,
                    'quiz_attempts'       => $quizAttemptCount,
                    'avg_quiz_score'      => round($avgQuizScore, 1),
                    'best_quiz_score'     => round($bestQuizScore, 1),
                    'total_spent'         => $totalSpent,
                    'engagement_score'    => round($engagementScore),
                    'completion_score'    => round($completionScore),
                    'performance_score'   => round($performanceScore),
                    'loyalty_score'       => round($loyaltyScore),
                    'churn_score'         => round($churnScore),
                    'intent_score'        => round($intentScore),
                    'readiness_score'     => round($readinessScore),
                    'overall_score'       => round(($engagementScore + $performanceScore + $loyaltyScore) / 3),
                    'segment'             => $this->determineSegment($churnScore, $intentScore, $loyaltyScore, $engagementScore, $performanceScore),
                    'frustration_score'   => $this->calcFrustration($avgQuizScore, 0, $loginCount),
                    'reactivation_potential' => $this->calcReactivation($churnScore, $loyaltyScore, $lastLogin),
                    'dropoff_risk'        => $this->calcDropoff(0, $loginCount, $lastLogin),
                    'trust_score'         => min(100, 50 + ($totalSpent / 50) + ($loginCount * 2)),
                    'buying_readiness'    => round($readinessScore),
                ];
            }

            Log::info("Generated " . count($profiles) . " profiles");
            return $profiles;

        } catch (\Exception $e) {
            Log::error('Profile generation error: ' . $e->getMessage());
            return [];
        }
    }

    // ── Scoring Methods ──
    private function calcChurn(?string $lastLogin, int $loginCount, float $avgProgress, float $avgQuiz): float
    {
        $score = 0;
        if ($lastLogin) {
            $score += min(40, now()->diffInDays($lastLogin) * 2);
        } else {
            $score += 40;
        }
        $score += min(20, max(0, 20 - $loginCount));
        $score += min(25, (100 - $avgProgress) * 0.25);
        $score += min(15, max(0, (50 - $avgQuiz) * 0.3));
        return min(100, $score);
    }

    private function calcIntent(?string $lastLogin, float $avgProgress, float $avgQuiz, float $totalSpent): float
    {
        $score = 0;
        if ($lastLogin) {
            $score += max(0, 30 - now()->diffInDays($lastLogin) * 3);
        }
        $score += min(25, $avgProgress * 0.25);
        $score += min(25, $avgQuiz * 0.25);
        $score += min(20, $totalSpent / 50);
        return min(100, $score);
    }

    private function calcFrustration(float $avgQuiz, float $avgProgress, int $loginCount): float
    {
        $score = 0;
        if ($avgQuiz < 50 && $avgQuiz > 0) $score += 40;
        if ($avgProgress > 10 && $avgProgress < 50) $score += 30;
        if ($loginCount > 5 && $avgProgress < 30) $score += 30;
        return min(100, $score);
    }

    private function calcReactivation(float $churnScore, float $loyaltyScore, ?string $lastLogin): float
    {
        if ($churnScore > 50 && $loyaltyScore > 50) {
            return min(100, 70 + ($loyaltyScore - 50) * 0.6);
        }
        if ($lastLogin && now()->diffInDays($lastLogin) < 30) {
            return min(100, 60 + (30 - now()->diffInDays($lastLogin)) * 2);
        }
        return max(0, 100 - $churnScore);
    }

    private function calcDropoff(float $avgProgress, int $loginCount, ?string $lastLogin): float
    {
        $score = 0;
        if ($avgProgress > 0 && $avgProgress < 30) $score += 50;
        if ($loginCount > 3 && $avgProgress < 20) $score += 30;
        if ($lastLogin && now()->diffInDays($lastLogin) > 7) $score += 20;
        return min(100, $score);
    }

    private function determineSegment(float $churnScore, float $intentScore, float $loyaltyScore, float $engagementScore, float $performanceScore): string
    {
        if ($churnScore >= 65 && $loyaltyScore < 50) return 'at_risk';
        if ($intentScore >= 80) return 'champion';
        if ($loyaltyScore >= 72 && $engagementScore >= 60) return 'loyal';
        if ($churnScore >= 50 && $intentScore >= 60) return 'new';
        if ($engagementScore < 20 && $churnScore < 50) return 'dormant';
        if ($intentScore >= 70 && $engagementScore >= 50) return 'champion';
        if ($performanceScore >= 80 && $engagementScore >= 60) return 'loyal';
        return 'new';
    }
}