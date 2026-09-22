<?php

namespace App\Jobs;

use App\Models\ChatSupportIntegration;
use App\Services\SlackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncSlackMessages implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(public readonly int $connectionId) {}

    public function handle(SlackService $slack): void
    {
        $connection = ChatSupportIntegration::findOrFail($this->connectionId);

        $connection->update(['status' => 'syncing', 'last_error' => null]);

        try {
            $synced = $slack->syncMessages($connection);

            $metrics = $connection->metrics ?? [];
            $metrics['messages_synced'] = $synced;

            $connection->update([
                'status'       => 'connected',
                'last_sync_at' => now(),
                'metrics'      => $metrics,
                'last_error'   => null,
                'sync_count'   => $connection->sync_count + 1,
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
