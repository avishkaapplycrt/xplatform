<?php

namespace App\Console\Commands;

use App\Jobs\RefreshUserScore;
use App\Models\UserEvent;
use Illuminate\Console\Command;

/**
 * Batch-recomputes all behavioural scores from raw UserEvent data.
 *
 * Usage:
 *   php artisan scores:refresh             — refresh all users
 *   php artisan scores:refresh --email=x   — refresh one user
 *
 * The command runs RefreshUserScore synchronously (bypassing the queue) so
 * results are immediately visible without needing a queue worker.
 */
class RefreshBehavioralScores extends Command
{
    protected $signature   = 'scores:refresh {--email= : Refresh a single user by email}';
    protected $description = 'Recompute behavioural scores and segments from raw event data';

    public function handle(): int
    {
        $emails = $this->option('email')
            ? collect([$this->option('email')])
            : UserEvent::distinct()->pluck('email');

        if ($emails->isEmpty()) {
            $this->warn('No UserEvent records found — run seeders first.');
            return self::FAILURE;
        }

        $this->info("Refreshing {$emails->count()} profile(s)…");
        $bar = $this->output->createProgressBar($emails->count());
        $bar->start();

        foreach ($emails as $email) {
            // dispatchSync bypasses the queue and runs inline — no worker needed
            RefreshUserScore::dispatchSync($email);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done — all scores and segments updated from live event data.');

        return self::SUCCESS;
    }
}
