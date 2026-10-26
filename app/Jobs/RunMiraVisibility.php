<?php

namespace App\Jobs;

use App\Models\MiraPremium\MiraVisibilityRun;
use App\Services\MiraPremium\VisibilityEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** Runs one queued AI visibility run (VisibilityEngine::execute). */
class RunMiraVisibility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 900;
    public int $tries   = 1;

    public function __construct(public int $runId, public string $subject, public string $period) {}

    public function handle(VisibilityEngine $engine): void
    {
        $run = MiraVisibilityRun::find($this->runId);

        if ($run !== null) {
            $engine->execute($run, $this->subject, $this->period);
        }
    }
}
