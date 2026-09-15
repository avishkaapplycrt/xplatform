<?php

namespace App\Jobs;

use App\Models\SocialIntegration;
use App\Services\InstagramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncInstagramPosts implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(public readonly int $connectionId) {}

    public function handle(InstagramService $instagram): void
    {
        $connection = SocialIntegration::findOrFail($this->connectionId);

        $connection->update(['status' => 'syncing', 'last_error' => null]);

        try {
            $synced = $instagram->syncPosts($connection);

            $metrics = $connection->metrics ?? [];
            $metrics['posts_synced'] = $synced;

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
