<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LaravelSite;
use App\Services\LaravelPollingService;

class PollLaravelSites extends Command
{
    protected $signature = 'laravel:poll {site?}';
    protected $description = 'Poll Laravel sites for analytics data';

    public function handle(LaravelPollingService $service): int
    {
        $query = LaravelSite::where('is_active', true);
        
        if ($this->argument('site')) {
            $query->where('id', $this->argument('site'));
        }

        $sites = $query->get();

        foreach ($sites as $site) {
            $this->info("Polling: {$site->site_name}");
            $result = $service->pollSite($site);
            $this->info("Stored: {$result['events_stored']} events");
        }

        return 0;
    }
}