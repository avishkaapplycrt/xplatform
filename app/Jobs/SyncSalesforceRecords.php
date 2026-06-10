<?php

namespace App\Jobs;

use App\Models\CrmConnection;
use App\Services\SalesforceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncSalesforceRecords implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(public readonly int $connectionId) {}

    public function handle(SalesforceService $salesforce): void
    {
        $connection = CrmConnection::findOrFail($this->connectionId);

        $connection->update(['status' => 'syncing', 'last_error' => null]);

        try {
            $synced = $salesforce->syncRecords($connection);

            $connection->update([
                'status'          => 'connected',
                'last_sync_at'    => now(),
                'contacts_synced' => $synced,
                'last_error'      => null,
            ]);

        } catch (\Throwable $e) {
            $connection->update([
                'status'     => 'error',
                'last_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
