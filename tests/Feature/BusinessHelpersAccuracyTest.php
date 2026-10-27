<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureClientOnboarded;
use App\Models\Client;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\CrmIntegration;
use App\Services\RealAccountsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

/**
 * Business Helpers shows the client's own figures: an account's value is its
 * real CRM deal value (nothing invented when there's no deal), and typed
 * Sales / Retention questions are answered from the client's own accounts —
 * not from pre-written answers about fictional companies.
 */
class BusinessHelpersAccuracyTest extends TestCase
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
            'database/migrations/2026_08_06_102029_create_transactions_table.php',
            'database/migrations/2026_09_15_081127_create_transactions_customers_table.php',
        ]]);
        // Brevo delivery rows (read for the trust score); its migration history is long.
        Schema::create('email_logs_providers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->nullable();
            $t->string('email');
            $t->timestamp('opened_at')->nullable();
            $t->timestamp('unsubscribed_at')->nullable();
            $t->timestamps();
        });
        $this->withoutMiddleware(EnsureClientOnboarded::class);

        $this->client = Client::forceCreate(['id' => 10, 'name' => 'New', 'company_name' => 'New Co', 'email' => 'new@new.test',
            'password' => bcrypt('x'), 'status' => 'active', 'plan' => 'free']);
        $crm = CrmIntegration::forceCreate(['client_id' => 10, 'provider' => 'hubspot', 'connection_name' => 'HubSpot', 'status' => 'connected']);
        CrmContact::create(['connection_id' => $crm->id, 'provider' => 'hubspot', 'external_id' => 'c1', 'first_name' => 'David', 'last_name' => 'Silva',
            'company' => 'Nimbus Tech', 'email' => 'david@nimbus.test', 'last_activity_at' => now()->subDays(3)->subHours(7)]);
        CrmContact::create(['connection_id' => $crm->id, 'provider' => 'hubspot', 'external_id' => 'c2', 'first_name' => 'Ana', 'last_name' => 'Morales',
            'company' => 'BrightPath', 'email' => 'ana@brightpath.test', 'last_activity_at' => now()]);
        CrmDeal::create(['connection_id' => $crm->id, 'provider' => 'hubspot', 'external_id' => 'd1', 'name' => 'Nimbus Tech - New Business',
            'value' => 52000, 'stage' => 'presentationscheduled', 'status' => 'open']);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('email_logs_providers');
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    public function test_account_value_is_the_real_deal_value_and_nothing_is_invented(): void
    {
        Auth::guard('client')->setUser($this->client);
        $accounts = collect(app(RealAccountsService::class)->build())->keyBy('company');

        $this->assertSame(52000, $accounts['Nimbus Tech']['mrr']);
        $this->assertSame(0, $accounts['BrightPath']['mrr']);            // no deal → 0, not a made-up $800
        $this->assertSame(3, $accounts['Nimbus Tech']['last_activity_days']);   // whole days
    }

    public function test_typed_retention_question_is_answered_from_the_clients_own_accounts(): void
    {
        config(['services.openai.api_key' => 'test-key']);
        Http::fake(['https://api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => 'David Silva at Nimbus Tech.']]]])]);

        $this->actingAs($this->client, 'client')
            ->postJson('/app/business-helpers/sales/ask', ['question' => 'Who is most at risk of leaving?', 'agent' => 'ch'])
            ->assertOk()
            ->assertJsonPath('answer', 'David Silva at Nimbus Tech.');

        Http::assertSent(function ($request) {
            $body = json_encode($request->data());

            return str_contains($body, 'Customer Retention copilot')   // retention role
                && str_contains($body, 'Nimbus Tech')                   // the client's own account data
                && !str_contains($body, 'Kite Travel');
        });
    }

    public function test_the_page_has_no_pre_written_answers_about_fictional_companies(): void
    {
        $page = file_get_contents(resource_path('views/client/business-helpers.blade.php'));

        foreach (['Kite Travel', 'Trellis Insurance', 'Meridian Health', 'Quarry Analytics', 'NovaPay', '/mo', 'MRR'] as $text) {
            $this->assertStringNotContainsString($text, $page, $text);
        }
    }
}
