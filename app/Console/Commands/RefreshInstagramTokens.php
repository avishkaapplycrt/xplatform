<?php

namespace App\Console\Commands;

use App\Models\SocialIntegration;
use App\Services\InstagramTokenRefresher;
use Illuminate\Console\Command;

/**
 * Daily: renews every client's Instagram token that expires within --days
 * (scheduled in routes/console.php), so connections don't lapse after 60 days.
 */
class RefreshInstagramTokens extends Command
{
    protected $signature = 'instagram:refresh-tokens {--days=10 : Refresh tokens expiring within this many days}';

    protected $description = 'Refresh long-lived Instagram access tokens before they expire';

    public function handle(InstagramTokenRefresher $refresher): int
    {
        $days = max(1, (int) $this->option('days'));
        $renewed = 0;
        $checked = 0;

        SocialIntegration::byPlatform('instagram')->connected()->whereNotNull('access_token')
            ->each(function (SocialIntegration $connection) use ($refresher, $days, &$renewed, &$checked) {
                $checked++;
                if ($refresher->refreshIfExpiring($connection, $days)) {
                    $renewed++;
                    $this->line("Renewed {$connection->username} (client {$connection->client_id})");
                }
            });

        $this->info("Done: {$renewed} of {$checked} Instagram connection(s) renewed.");

        return self::SUCCESS;
    }
}
