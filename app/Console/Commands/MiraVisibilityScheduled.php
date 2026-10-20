<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\MiraPremium\MiraProject;
use App\Services\MiraPremium\Entitlement;
use App\Services\MiraPremium\MiraApiException;
use App\Services\MiraPremium\VisibilityEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Runs the AI visibility prompt sets of projects whose schedule is due
 * (daily / weekly / monthly — mira_projects.visibility_schedule). Each run is
 * charged to the owner's plan like a manual one; a project whose owner no
 * longer has quota or Premium is skipped and tried again next interval.
 * Scheduled hourly in routes/console.php.
 */
class MiraVisibilityScheduled extends Command
{
    protected $signature = 'mira:visibility-scheduled {--limit=50 : Most projects to run in one pass}';

    protected $description = 'Run scheduled Mira Premium AI visibility checks that are due';

    public function handle(VisibilityEngine $engine): int
    {
        $due = MiraProject::where('visibility_schedule', '!=', 'off')
            ->where(fn ($q) => $q->whereNull('next_visibility_run_at')->orWhere('next_visibility_run_at', '<=', now()))
            ->orderBy('next_visibility_run_at')->limit((int) $this->option('limit'))->get();

        foreach ($due as $project) {
            $days = MiraProject::SCHEDULES[$project->visibility_schedule] ?? null;
            if ($days === null) {
                continue;
            }
            // Next slot first, so a failing project can't be retried in a tight loop.
            $project->update(['next_visibility_run_at' => now()->addDays($days)]);

            $client = Client::find($project->client_id);
            $who    = Entitlement::resolve($client, 'client:' . $project->client_id);
            if ($client === null || !$who->allows('visibility_runs')) {
                $this->line("skip #{$project->id} {$project->domain}: plan has no AI visibility runs");
                continue;
            }

            try {
                $run = $engine->startRun($who, $project, 'schedule', true);
                $this->info("ran #{$project->id} {$project->domain}: {$run->status}, {$run->mentions}/{$run->answered_count} mentions");
            } catch (MiraApiException $e) {
                $this->warn("skip #{$project->id} {$project->domain}: {$e->getMessage()}");
            } catch (\Throwable $e) {
                Log::error('Scheduled Mira visibility run failed', ['project' => $project->id, 'message' => $e->getMessage()]);
                $this->error("fail #{$project->id} {$project->domain}: {$e->getMessage()}");
            }
        }

        $this->info($due->count() . ' due project(s) processed.');

        return self::SUCCESS;
    }
}
