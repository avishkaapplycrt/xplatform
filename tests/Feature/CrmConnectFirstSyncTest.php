<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureClientOnboarded;
use App\Models\Client;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\CrmIntegration;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Connecting a CRM pulls its data straight away (it used to wait for the
 * Sync button, so a new client saw nothing after connecting HubSpot), and the
 * data belongs to the client who connected it.
 */
class CrmConnectFirstSyncTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_03_18_000002_create_clients_table.php',
            'database/migrations/2026_07_09_081744_create_crm_integrations_table.php',
            'database/migrations/2026_07_09_090221_create_social_integrations_table.php',
            'database/migrations/2026_08_25_000003_create_crm_contacts_table.php',
            'database/migrations/2026_08_25_000004_create_crm_deals_table.php',
            'database/migrations/2026_10_27_000001_add_client_id_to_crm_and_social_integrations.php',
            'database/migrations/2026_10_27_000002_add_client_id_to_synced_data_tables.php',
        ]]);
        $this->withoutMiddleware(EnsureClientOnboarded::class);
        $this->client = Client::forceCreate(['id' => 10, 'name' => 'New', 'company_name' => 'New Co', 'email' => 'new@new.test',
            'password' => bcrypt('x'), 'status' => 'active', 'plan' => 'free']);
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    private function fakeHubSpot(int $dealStatus = 200): void
    {
        Http::fake([
            'https://api.hubapi.com/crm/v3/objects/contacts*' => Http::response(['results' => [
                ['id' => '101', 'properties' => ['email' => 'ann@x.test', 'firstname' => 'Ann', 'lastname' => 'Lee', 'company' => 'X'], 'updatedAt' => now()->toIso8601String()],
                ['id' => '102', 'properties' => ['email' => 'bo@y.test', 'firstname' => 'Bo'], 'updatedAt' => now()->toIso8601String()],
            ]]),
            'https://api.hubapi.com/crm/v3/objects/deals*' => Http::response(['results' => [
                ['id' => '201', 'properties' => ['dealname' => 'X renewal', 'amount' => '5000', 'dealstage' => 'qualifiedtobuy'], 'createdAt' => now()->toIso8601String()],
            ]], $dealStatus),
        ]);
    }

    public function test_connecting_hubspot_syncs_contacts_and_deals_for_that_client(): void
    {
        $this->fakeHubSpot();

        $this->actingAs($this->client, 'client')
            ->postJson('/app/crm-connections/hubspot', ['connection_name' => 'HubSpot', 'status' => 'connected', 'api_key' => 'pat-test'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'CRM connection created successfully and synced 2 contacts and 1 deal.');

        $connection = CrmIntegration::withoutGlobalScopes()->sole();
        $this->assertSame(10, (int) $connection->client_id);
        $this->assertNotNull($connection->last_sync_at);
        $this->assertSame(2, CrmContact::forClient($this->client)->count());
        $this->assertSame(1, CrmDeal::forClient($this->client)->count());
    }

    public function test_a_failed_first_sync_still_saves_the_connection_and_says_so(): void
    {
        Http::fake([
            'https://api.hubapi.com/crm/v3/objects/contacts?limit=1' => Http::response(['results' => []]),   // key check passes
            'https://api.hubapi.com/*' => Http::response([], 500),                                          // sync fails
        ]);

        $response = $this->actingAs($this->client, 'client')
            ->postJson('/app/crm-connections/hubspot', ['connection_name' => 'HubSpot', 'status' => 'connected', 'api_key' => 'pat-test'])
            ->assertOk();

        $this->assertStringContainsString("the first sync didn't finish", $response->json('message'));
        $connection = CrmIntegration::withoutGlobalScopes()->sole();
        $this->assertSame('connected', $connection->status);
        $this->assertNull($connection->last_sync_at);   // no fake "synced just now"
        $this->assertNotNull($connection->last_error);
    }
}
