<?php
// app/Console/Commands/CleanupAnalytics.php

namespace App\Console\Commands;

use App\Models\AnalyticsPageview;
use Illuminate\Console\Command;

class CleanupAnalytics extends Command
{
    protected $signature = 'analytics:cleanup {--days=7}';
    protected $description = 'Clean up old raw analytics data';

    public function handle(): void
    {
        $days = $this->option('days');
        $cutoff = now()->subDays($days);
        
        $count = AnalyticsPageview::where('created_at', '<', $cutoff)->delete();
        
        $this->info("Deleted {$count} old pageview records.");
    }
}