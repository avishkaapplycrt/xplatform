<?php

namespace App\Services\MockMaster;

use Illuminate\Support\Facades\DB;

/**
 * Copies the 14 Mock Master source tables from the live PTE Portal database
 * (connection "mockmaster_live", see config/database.php) into their local
 * mm_* mirrors on the default connection. Triggered by the "Sync Data"
 * button on the Mock Master Helper page.
 *
 * Each table is fully replaced (truncate + re-insert) inside its own
 * transaction, chunked by primary key so the large tables (mock_test_logs,
 * login_history, purchases, studentuser) don't load into memory at once.
 * The 14 local mm_* tables are read-only mirrors of the live source, so a
 * full replace is safe — nothing else writes to them.
 */
class MockMasterSyncService
{
    /** @var array<string, array{source: string, target: string, pk: string}> */
    private const TABLES = [
        ['source' => 'coupon_usage', 'target' => 'mm_coupon_usage', 'pk' => 'id'],
        ['source' => 'deleted_students', 'target' => 'mm_deleted_students', 'pk' => 'studentId'],
        ['source' => 'feedbacks', 'target' => 'mm_feedbacks', 'pk' => 'id'],
        ['source' => 'login_history', 'target' => 'mm_login_history', 'pk' => 'id'],
        ['source' => 'meetings', 'target' => 'mm_meetings', 'pk' => 'id'],
        ['source' => 'mock_test_logs', 'target' => 'mm_mock_test_logs', 'pk' => 'meta_id'],
        ['source' => 'mock_test_results', 'target' => 'mm_mock_test_results', 'pk' => 'id'],
        ['source' => 'notifications', 'target' => 'mm_notifications', 'pk' => 'id'],
        ['source' => 'notifications_seen', 'target' => 'mm_notifications_seen', 'pk' => 'id'],
        ['source' => 'packages', 'target' => 'mm_packages', 'pk' => 'packageid'],
        ['source' => 'payments', 'target' => 'mm_payments', 'pk' => 'id'],
        ['source' => 'purchases', 'target' => 'mm_purchases', 'pk' => 'purchaseid'],
        ['source' => 'scheduled_emails', 'target' => 'mm_scheduled_emails', 'pk' => 'id'],
        ['source' => 'studentuser', 'target' => 'mm_studentuser', 'pk' => 'studentId'],
    ];

    private const CHUNK_SIZE = 1000;

    /**
     * @return array{tables: array<int, array{table: string, rows: int}>, total_rows: int}
     */
    public function sync(): array
    {
        // Copying all 14 remote tables easily exceeds the default 60s
        // max_execution_time, so allow up to 5 minutes for a full sync.
        set_time_limit(300);

        $results = [];
        $totalRows = 0;

        foreach (self::TABLES as $table) {
            $rows = $this->syncTable($table['source'], $table['target'], $table['pk']);
            $results[] = ['table' => $table['target'], 'rows' => $rows];
            $totalRows += $rows;
        }

        return ['tables' => $results, 'total_rows' => $totalRows];
    }

    private function syncTable(string $source, string $target, string $pk): int
    {
        // TRUNCATE causes an implicit commit in MySQL, so it can't live
        // inside the same transaction as the inserts that follow it.
        DB::table($target)->truncate();

        $rowCount = 0;

        DB::connection('mockmaster_live')
            ->table($source)
            ->orderBy($pk)
            ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($target, &$rowCount) {
                $rows = $chunk->map(fn ($row) => (array) $row)->all();
                if (!empty($rows)) {
                    DB::table($target)->insert($rows);
                    $rowCount += count($rows);
                }
            }, $pk, $pk);

        return $rowCount;
    }
}
