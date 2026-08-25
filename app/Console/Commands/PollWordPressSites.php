<?php
// app/Console/Commands/PollWordPressSites.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WordPressSite;
use App\Services\WordPressPollingService;
use App\Services\WooCommercePollingService;

class PollWordPressSites extends Command
{
    protected $signature = 'wp:poll 
                            {site? : Specific site ID}
                            {--type= : Filter by type (rest_poll, db_direct)}
                            {--wc-only : Poll only WooCommerce data}
                            {--dry-run : Show what would be fetched without storing}';

    protected $description = 'Poll WordPress sites for analytics data';

    public function handle(
        WordPressPollingService $pollingService,
        WooCommercePollingService $wcService
    ): int {
        $query = WordPressSite::where('is_active', true);

        if ($this->argument('site')) {
            $query->where('id', $this->argument('site'));
        }

        if ($this->option('type')) {
            $query->where('api_type', $this->option('type'));
        } else {
            $query->whereIn('api_type', ['rest_poll', 'db_direct']);
        }

        $sites = $query->get();

        if ($sites->isEmpty()) {
            $this->warn('No sites configured for polling.');
            return 0;
        }

        $this->info("Polling {$sites->count()} site(s)...");

        foreach ($sites as $site) {
            $this->info("\n📡 {$site->site_name} ({$site->site_url})");

            if ($this->option('dry-run')) {
                $this->info("   [DRY RUN] Would poll: {$site->api_type}");
                continue;
            }

            if ($this->option('wc-only') && $site->hasWooCommerce()) {
                $credentials = $site->decrypted_credentials;
                $config = $site->connection_config;
                $result = $wcService->pollWooCommerce($site, $credentials, $config);
                
                $this->info("   WooCommerce:");
                $this->info("   - Orders: {$result['orders']['stored']}");
                $this->info("   - Products: {$result['products']['stored']}");
                $this->info("   - Customers: {$result['customers']['stored']}");
            } else {
                $result = $pollingService->pollSite($site);
                
                if (isset($result['skipped'])) {
                    $this->warn("   Skipped: {$result['reason']}");
                    continue;
                }

                $this->info("   Events stored: {$result['events_stored']}");
                
                if (isset($result['woocommerce'])) {
                    $wc = $result['woocommerce'];
                    $this->info("   WooCommerce: {$wc['stored']} events");
                }

                if (!empty($result['errors'])) {
                    foreach ($result['errors'] as $error) {
                        $this->error("   Error: {$error}");
                    }
                }
            }
        }

        $this->info("\n✅ Polling complete!");
        return 0;
    }
}