<?php

namespace App\Jobs;

use App\Models\MiraPremium\MiraScan;
use App\Services\MiraPremium\ScanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** Runs one queued Mira Premium scan (ScanService::run). */
class RunMiraScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 300;
    public int $tries   = 1;

    public function __construct(public int $scanId, public bool $aiRecommendations) {}

    public function handle(ScanService $scans): void
    {
        $scan = MiraScan::find($this->scanId);

        if ($scan !== null) {
            $scans->run($scan, $this->aiRecommendations);
        }
    }
}
