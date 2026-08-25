<?php

namespace App\Jobs;

use App\Models\Client;
use App\Services\AnalyticsEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    public function __construct(
        public int $clientId
    ) {}

    public function handle(AnalyticsEngine $engine): void
    {
        $client = Client::find($this->clientId);
        
        if (!$client || !$client->is_active) {
            return;
        }

        $activeSources = $client->activeDataSources;
        
        if ($activeSources->isEmpty()) {
            return;
        }

        $engine->forClient($client)
            ->fromSources($activeSources)
            ->getRealtimeMetrics(['active_users', 'revenue_today', 'orders_today']);

        // Generate insights periodically
        if (now()->minute === 0) { // Every hour
            $engine->generateInsights();
        }
    }
}