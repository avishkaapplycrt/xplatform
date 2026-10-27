<?php

namespace App\Services\MockMaster;

use Illuminate\Support\Facades\DB;

/**
 * Incrementally copies the 14 Mock Master source tables from the live PTE
 * Portal database (connection "mockmaster_live") into their local mm_*
 * mirrors on the default connection.
 *
 * Existing rows are kept. For each table, only source rows whose primary key
 * is greater than the local maximum primary key are fetched and inserted.
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

    // Rows fetched from the remote server per round trip.
    private const FETCH_CHUNK_SIZE = 5000;

    // Keep INSERT statements below MySQL's max_allowed_packet limit.
    private const INSERT_CHUNK_SIZE = 1000;

    /**
     * @return array{tables: array<int, array{table: string, rows: int}>, total_rows: int}
     */
    public function sync(): array
    {
        set_time_limit(0);
        DB::connection('mockmaster_live')->disableQueryLog();
        DB::connection()->disableQueryLog();

        $results = [];
        $totalRows = 0;

        foreach (self::TABLES as $table) {
            $rows = $this->syncTable($table['source'], $table['target'], $table['pk']);
            $results[] = ['table' => $table['target'], 'rows' => $rows];
            $totalRows += $rows;
        }

        return ['tables' => $results, 'total_rows' => $totalRows];
    }

    /**
     * Insert only rows that are newer than the last row already mirrored.
     * Returns the number of rows actually inserted, not the number scanned.
     */
    private function syncTable(string $source, string $target, string $pk): int
    {
        $lastSyncedId = DB::table($target)->max($pk);
        $rowCount = 0;

        $query = DB::connection('mockmaster_live')
            ->table($source)
            ->orderBy($pk);

        if ($lastSyncedId !== null) {
            $query->where($pk, '>', $lastSyncedId);
        }

        $query->chunkById(self::FETCH_CHUNK_SIZE, function ($chunk) use ($target, &$rowCount) {
            $rows = $chunk->map(fn ($row) => (array) $row)->all();

            // Commit each fetched chunk separately. If the request is interrupted,
            // completed chunks remain in place and the next run resumes after them.
            DB::transaction(function () use ($rows, $target, &$rowCount) {
                foreach (array_chunk($rows, self::INSERT_CHUNK_SIZE) as $insertBatch) {
                    // The PK/unique index protects against duplicates if two syncs
                    // overlap or a batch is retried.
                    $rowCount += DB::table($target)->insertOrIgnore($insertBatch);
                }
            });
        }, $pk, $pk);

        return $rowCount;
    }
}
