<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\BusinessIntelligenceService;
use App\Services\Llm\AnthropicException;
use App\Services\Llm\AnthropicRefusedException;
use Illuminate\Console\Command;

/**
 * Regenerates the AI business health score and insight list for one or all
 * active clients. Scheduled daily (routes/console.php) and also reachable
 * on-demand from the dashboard via a "Regenerate" action.
 */
class GenerateBusinessInsights extends Command
{
    protected $signature = 'insights:generate {client? : A specific client ID; omit to run for every active client with data}';

    protected $description = 'Generate AI business health scores and insights from each client\'s own data';

    public function handle(BusinessIntelligenceService $service): int
    {
        if (!app(\App\Services\Llm\AnthropicClient::class)->isConfigured()) {
            $this->error('ANTHROPIC_API_KEY is not set — nothing to do.');
            return self::FAILURE;
        }

        $clients = $this->argument('client')
            ? Client::where('id', $this->argument('client'))->get()
            : Client::active()->get();

        if ($clients->isEmpty()) {
            $this->info('No matching clients.');
            return self::SUCCESS;
        }

        $generated = 0;
        $skipped   = 0;
        $failed    = 0;

        foreach ($clients as $client) {
            if (!$service->hasEnoughData($client)) {
                $skipped++;
                continue;
            }

            try {
                $service->generate($client);
                $generated++;
                $this->line("  {$client->company_name}: generated");
            } catch (AnthropicRefusedException $e) {
                $failed++;
                $this->warn("  {$client->company_name}: declined by safety classifier — will retry next run");
            } catch (AnthropicException $e) {
                $failed++;
                $this->error("  {$client->company_name}: {$e->getMessage()}");
            }
        }

        $this->info("Done. {$generated} generated, {$skipped} skipped (no data yet), {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
