<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Poll REST sites every 6 hours
        $schedule->command('wp:poll --type=rest_poll')
            ->everySixHours()
            ->withoutOverlapping()
            ->runInBackground();

        // Poll WooCommerce data hourly for active shops
        $schedule->command('wp:poll --wc-only')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        // Aggregate all sites at 2 AM
        $schedule->command('analytics:aggregate')
            ->dailyAt('02:00');

        $schedule->command('laravel:poll')->everySixHours();

        $schedule->command('analytics:process')->everyMinute();
        $schedule->command('analytics:aggregate')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}