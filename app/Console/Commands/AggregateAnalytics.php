<?php
// app/Console/Commands/AggregateAnalytics.php

namespace App\Console\Commands;

use App\Jobs\AggregateAnalyticsStats;
use Illuminate\Console\Command;

class AggregateAnalytics extends Command
{
    protected $signature = 'analytics:aggregate';
    protected $description = 'Aggregate analytics data into hourly/daily stats';

    public function handle(): void
    {
        $this->info('Starting analytics aggregation...');
        dispatch(new AggregateAnalyticsStats());
        $this->info('Aggregation job dispatched.');
    }
}