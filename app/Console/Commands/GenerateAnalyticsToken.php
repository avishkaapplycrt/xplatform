<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiToken;

class GenerateAnalyticsToken extends Command
{
    protected $signature = 'analytics:token {name=Analytics Platform}';
    protected $description = 'Generate API token for analytics platform';

    public function handle(): int
    {
        $plainToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $plainToken);

        ApiToken::create([
            'name' => $this->argument('name'),
            'token' => $hashedToken,
            'abilities' => ['users:read', 'page-views:read', 'orders:read', 'events:read', 'stats:read'],
        ]);

        $this->info('Token generated successfully!');
        $this->newLine();
        $this->line('Plain Token (COPY THIS):');
        $this->warn($plainToken);
        $this->newLine();
        $this->line('Add this to your .env file on the target site:');
        $this->warn('ANALYTICS_API_KEY=' . $plainToken);

        return 0;
    }
}