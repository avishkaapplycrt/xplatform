<?php

namespace App\Console\Commands;

use App\Services\SalesCustomerIntelligenceService;
use Illuminate\Console\Command;

/**
 * Rebuilds sales_customer_intelligence from crm_contacts + crm_deals +
 * email_logs_brevo. Run manually, or automatically after a HubSpot/Brevo
 * sync completes (see CrmConnectionController::syncHubSpot() and
 * EmailConnectionController::syncBrevo()).
 */
class SyncSalesCustomerIntelligence extends Command
{
    protected $signature = 'sales-intelligence:sync';

    protected $description = 'Rebuild sales_customer_intelligence from crm_contacts, crm_deals and email_logs_brevo';

    public function handle(SalesCustomerIntelligenceService $service): int
    {
        $count = $service->rebuild();

        $this->info("sales_customer_intelligence rebuilt: {$count} contacts processed.");

        return self::SUCCESS;
    }
}
