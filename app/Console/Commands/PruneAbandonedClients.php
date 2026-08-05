<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

/**
 * The old registration flow created a Client row at step 1 with a placeholder
 * email (pending_<uniqid>@pending.com) and only set the real credentials at
 * step 8 — so every abandoned registration left an orphan row behind.
 *
 * The current flow collects credentials up front and never creates these, so
 * this command exists to clear out the legacy rows. Not scheduled by default:
 * run it once you're happy with what --dry-run reports.
 */
class PruneAbandonedClients extends Command
{
    protected $signature = 'clients:prune-abandoned
                            {--days=7 : Only delete placeholder accounts older than this many days}
                            {--dry-run : List what would be deleted without deleting it}';

    protected $description = 'Delete placeholder client rows left behind by abandoned registrations';

    public function handle(): int
    {
        $days   = max(0, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $clients = Client::where('email', 'like', 'pending\_%@pending.com')
            ->where('created_at', '<', $cutoff)
            ->orderBy('created_at')
            ->get();

        if ($clients->isEmpty()) {
            $this->info("No abandoned registrations older than {$days} day(s).");
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Company', 'Placeholder email', 'Created'],
            $clients->map(fn($c) => [
                $c->id,
                $c->company_name,
                $c->email,
                $c->created_at?->toDateTimeString(),
            ])->all()
        );

        if ($this->option('dry-run')) {
            $this->comment("Dry run — {$clients->count()} row(s) would be deleted.");
            return self::SUCCESS;
        }

        if (!$this->confirm("Delete {$clients->count()} abandoned client row(s)?", false)) {
            $this->comment('Aborted.');
            return self::SUCCESS;
        }

        $deleted = Client::whereIn('id', $clients->pluck('id'))->delete();

        $this->info("Deleted {$deleted} abandoned client row(s).");

        return self::SUCCESS;
    }
}
