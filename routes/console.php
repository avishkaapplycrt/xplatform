<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessAnalyticsJob;
use App\Models\Client;

Schedule::call(function () {
    // Process analytics for all active clients every minute
    Client::active()
        ->whereHas('dataSources', fn($q) => $q->where('status', 'connected'))
        ->chunk(100, function ($clients) {
            foreach ($clients as $client) {
                ProcessAnalyticsJob::dispatch($client->id);
            }
        });
})->everyMinute();