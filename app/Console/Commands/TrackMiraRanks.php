<?php

namespace App\Console\Commands;

use App\Models\MiraPremium\MiraProject;
use App\Services\MiraPremium\RankTracker;
use Illuminate\Console\Command;

/**
 * Daily Mira Premium rank check: every tracked keyword not yet checked today
 * gets its Google position (scheduled in routes/console.php).
 */
class TrackMiraRanks extends Command
{
    protected $signature = 'mira:track-ranks {--project= : Only this project id}';

    protected $description = 'Check today\'s Google positions for Mira Premium tracked keywords';

    public function handle(RankTracker $tracker): int
    {
        $projects = MiraProject::whereHas('trackedKeywords')
            ->when($this->option('project'), fn ($q, $id) => $q->where('id', $id))
            ->get();

        $total = 0;
        foreach ($projects as $project) {
            try {
                $n = $tracker->check($project, null, true);
                $total += $n;
                $this->line("{$project->domain}: {$n} keyword(s) checked");
            } catch (\Throwable $e) {
                $this->error("{$project->domain}: {$e->getMessage()}");
            }
        }

        $this->info("Done: {$total} keyword(s) checked across {$projects->count()} project(s).");

        return self::SUCCESS;
    }
}
