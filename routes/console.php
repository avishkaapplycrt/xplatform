<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessAnalyticsJob;
use App\Models\Client;

Schedule::call(function () {
    Client::active()
        ->whereHas('dataSources', fn($q) => $q->where('status', 'connected'))
        ->chunk(100, function ($clients) {
            foreach ($clients as $client) {
                ProcessAnalyticsJob::dispatch($client->id);
            }
        });
})->everyMinute();

// Recompute all behavioural scores + segments from raw event data every 15 minutes.
// This ensures L4/L5 dashboards always reflect the latest user activity even if
// the queue worker is not running (dispatchSync inside the command runs inline).
Schedule::command('scores:refresh')->everyFifteenMinutes()->withoutOverlapping();