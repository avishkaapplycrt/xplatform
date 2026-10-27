<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\CrmIntegration;
use App\Services\DataCollectionStats;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Data Collection (Layer 1) CRM, Chat & Support and Transactions tabs: real
 * figures from each client's own synced data, and an empty state (not sample
 * numbers) for a client with nothing synced.
 */
class DataCollectionStatsTest extends TestCase
{
    private Client $alice;
    private Client $bob;
    private DataCollectionStats $stats;

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
            'database/migrations/2026_08_06_102029_create_transactions_table.php',
            'database/migrations/2026_09_15_081127_create_transactions_customers_table.php',
            'database/migrations/2026_03_26_042439_create_analysis_layers_table.php',
            'database/migrations/2026_05_06_000002_create_behavioral_profiles_table.php',
            'database/migrations/2026_05_06_000003_create_user_events_table.php',
            'database/migrations/2026_05_13_000005_add_client_id_to_behavioral_profiles.php',
            'database/migrations/2026_05_13_000006_add_relations_to_user_events.php',
            'database/migrations/2026_07_09_063024_create_email_connections_table.php',
            'database/migrations/2026_09_15_010457_create_instagram_media_table.php',
        ]]);
        // email_logs' migration history is long; these are the columns used here.
        Schema::create('email_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id');
            $t->string('email_address');
            $t->timestamp('unsubscribed_at')->nullable();
            $t->timestamps();
        });
        // chat_support_integrations' client_id migration is MySQL-only; these are the columns used here.
        Schema::create('chat_support_integrations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->nullable();
            $t->string('provider');
            $t->string('connection_name');
            $t->string('status')->default('disconnected');
            $t->timestamps();
            $t->softDeletes();
        });
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_09_21_061533_create_slack_channels_table.php',
            'database/migrations/2026_09_21_061533_create_slack_messages_table.php',
            'database/migrations/2026_10_27_000002_add_client_id_to_synced_data_tables.php',
        ]]);
        $this->alice = $this->makeClient(1, 'Alice Co');
        $this->bob = $this->makeClient(2, 'Bob Co');
        $this->stats = app(DataCollectionStats::class);
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:reset');
        Schema::dropIfExists('chat_support_integrations');
        Schema::dropIfExists('email_logs');
        parent::tearDown();
    }

    private function makeClient(int $id, string $name): Client
    {
        return Client::forceCreate(['id' => $id, 'name' => $name, 'company_name' => $name, 'email' => "c{$id}@test.test",
            'password' => bcrypt('x'), 'status' => 'active', 'plan' => 'free']);
    }

    private function seedCrm(Client $client): void
    {
        $crm = CrmIntegration::forceCreate(['client_id' => $client->id, 'provider' => 'hubspot', 'connection_name' => 'HubSpot', 'status' => 'connected']);
        CrmContact::create(['connection_id' => $crm->id, 'provider' => 'hubspot', 'external_id' => 'c1', 'email' => 'a@x.test',
            'first_name' => 'Ann', 'last_name' => 'Lee', 'company' => 'X', 'last_activity_at' => now()]);
        CrmContact::create(['connection_id' => $crm->id, 'provider' => 'hubspot', 'external_id' => 'c2', 'first_name' => 'Bo']);
        foreach ([['qualifiedtobuy', 'open', 1000], ['qualifiedtobuy', 'open', 500], ['closedwon', 'won', 2000], ['closedlost', 'lost', 300]] as $i => [$stage, $status, $value]) {
            CrmDeal::create(['connection_id' => $crm->id, 'provider' => 'hubspot', 'external_id' => "d$i", 'name' => "Deal $i",
                'stage' => $stage, 'status' => $status, 'value' => $value, 'close_date' => now()]);
        }
    }

    private function seedSlack(Client $client): void
    {
        $integration = DB::table('chat_support_integrations')->insertGetId(['client_id' => $client->id, 'provider' => 'slack',
            'connection_name' => 'Slack', 'status' => 'connected', 'created_at' => now(), 'updated_at' => now()]);
        $channel = DB::table('slack_channels')->insertGetId(['chat_support_integration_id' => $integration, 'channel_id' => 'C' . $client->id,
            'name' => 'support', 'created_at' => now(), 'updated_at' => now()]);
        foreach ([[2, 1, 'U1'], [0, 0, 'U2'], [0, 3, 'U1']] as $i => [$replies, $reactions, $user]) {
            DB::table('slack_messages')->insert(['slack_channel_id' => $channel, 'ts' => "{$client->id}.$i", 'user_id' => $user, 'text' => "Message $i",
                'reply_count' => $replies, 'reaction_count' => $reactions, 'posted_at' => now()->subDays($i), 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function seedPayments(Client $client): void
    {
        $repeat = DB::table('transactions_customers')->insertGetId(['client_id' => $client->id, 'gateway_customer_id' => 'cus_1',
            'name' => 'Ria', 'email' => 'ria@x.test', 'lifetime_value' => 150, 'orders_count' => 2, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('transactions_customers')->insert(['client_id' => $client->id, 'gateway_customer_id' => 'cus_2',
            'name' => 'Sam', 'lifetime_value' => 0, 'orders_count' => 0, 'created_at' => now(), 'updated_at' => now()]);
        foreach ([[100, 'completed', 'visa'], [50, 'completed', 'mastercard'], [20, 'failed', 'visa'], [30, 'refunded', 'visa']] as $i => [$amount, $status, $brand]) {
            DB::table('transactions')->insert(['client_id' => $client->id, 'customer_id' => $repeat, 'amount' => $amount, 'quantity' => 1,
                'status' => $status, 'payment_method' => 'card', 'transaction_reference' => "ch_{$client->id}_$i",
                'metadata' => json_encode(['currency' => 'AUD', 'card_brand' => $brand]), 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function stat(array $panel, string $label): string
    {
        return collect($panel['stats'])->firstWhere(0, $label)[1];
    }

    public function test_crm_panels_use_the_clients_own_deals_and_contacts(): void
    {
        $this->seedCrm($this->alice);
        $this->seedCrm($this->bob);   // must not be counted for Alice

        $crm = $this->stats->crm($this->alice);

        $pipeline = $crm['stage-transitions'];
        $this->assertSame('2', $this->stat($pipeline, 'Open deals'));
        $this->assertSame('$1,500', $this->stat($pipeline, 'Open pipeline'));
        $this->assertSame('50%', $this->stat($pipeline, 'Win rate'));
        $this->assertContains('Qualified to buy', array_column($pipeline['tables'][0]['rows'], 0));

        $fields = $crm['field-completion'];
        $this->assertSame('2', $this->stat($fields, 'Contacts'));
        $this->assertSame('50%', $this->stat($fields, 'Fully complete'));
        $this->assertSame('1', $this->stat($fields, 'Missing email'));
        $this->assertSame(['Email', '50%', 50], $fields['bars'][0]['rows'][0]);
    }

    public function test_chat_panels_use_the_clients_slack_messages(): void
    {
        $this->seedSlack($this->alice);
        $this->seedSlack($this->bob);

        $chat = $this->stats->chat($this->alice);

        $this->assertSame('3', $this->stat($chat['conversation-events'], 'Messages'));
        $this->assertSame('2', $this->stat($chat['conversation-events'], 'People posting'));
        $this->assertSame(['#support', '3', 100], $chat['conversation-events']['bars'][0]['rows'][0]);
        $this->assertSame('2', $this->stat($chat['resolution-metrics'], 'Replies'));
        $this->assertSame('67%', $this->stat($chat['resolution-metrics'], 'Got a response'));
    }

    public function test_transaction_panels_use_the_clients_payments(): void
    {
        $this->seedPayments($this->alice);
        $this->seedPayments($this->bob);

        $txn = $this->stats->transactions($this->alice);

        $this->assertSame('A$150.00', $this->stat($txn['cart-analytics'], 'Revenue'));
        $this->assertSame('2', $this->stat($txn['cart-analytics'], 'Orders'));
        $this->assertSame('ria@x.test', $txn['cart-analytics']['tables'][0]['rows'][0][1]);   // customer record when the payment has no email
        $this->assertSame('Card · Visa', $this->stat($txn['payment-methods'], 'Most used'));
        $this->assertSame('1', $this->stat($txn['failure-codes'], 'Failed'));
        $this->assertSame('25%', $this->stat($txn['failure-codes'], 'Failure rate'));
        $this->assertSame('A$30.00', $this->stat($txn['refunds'], 'Refunded amount'));
        $this->assertSame('33%', $this->stat($txn['refunds'], 'Refund rate'));
        $this->assertSame('100%', $this->stat($txn['repeat-purchase'], 'Repeat rate'));
        $this->assertSame('2', $this->stat($txn['repeat-purchase'], 'Customers'));
    }

    public function test_a_client_with_nothing_synced_gets_empty_states_not_numbers(): void
    {
        $this->seedCrm($this->alice);
        $this->seedSlack($this->alice);
        $this->seedPayments($this->alice);

        $panels = array_merge($this->stats->crm($this->bob), $this->stats->chat($this->bob), $this->stats->transactions($this->bob));

        $this->assertCount(9, $panels);
        foreach ($panels as $tab => $panel) {
            $this->assertArrayHasKey('empty', $panel, $tab);
            $this->assertArrayNotHasKey('stats', $panel, $tab);
            // Nothing connected: tell them to connect the service, with a button to do it.
            $this->assertStringStartsWith('Connect ', $panel['empty'], $tab);
            $this->assertSame('Not connected', $panel['tag'], $tab);
            $this->assertNotEmpty($panel['action'][0], $tab);
        }
    }

    public function test_architecture_overview_layer_lines_use_the_clients_own_data(): void
    {
        $this->seedCrm($this->alice);
        $this->seedPayments($this->alice);
        DB::table('transactions_customers')->where('client_id', 1)->update(['email' => 'a@x.test']);   // same person as a CRM contact
        \App\Models\BehavioralProfile::forceCreate(['client_id' => 1, 'name' => 'Ann', 'email' => 'a@x.test', 'segment' => 'loyal',
            'intent_score' => 50, 'engagement_score' => 50, 'churn_score' => 70, 'loyalty_score' => 50, 'trust_score' => 50,
            'frustration_score' => 10, 'buying_readiness' => 85, 'dropoff_risk' => 20, 'reactivation_potential' => 30, 'overall_score' => 60]);
        $this->seedCrm($this->bob);

        $lines = app(\App\Services\LayerSummaries::class)->forClient($this->alice);

        $this->assertStringContainsString('2 of 5 sources connected', $lines['L1']['summary']);
        $this->assertStringContainsString('6 CRM records', $lines['L1']['summary']);
        $this->assertStringContainsString('4 payments', $lines['L1']['summary']);
        $this->assertSame('1 person identified by email  •  1 matched across 2+ sources', $lines['L2']['summary']);
        $this->assertStringContainsString('1 behaviour profile', $lines['L4']['summary']);
        $this->assertStringContainsString('1 at churn risk', $lines['L5']['summary']);
        $this->assertGreaterThan(0, $lines['L1']['dots']);
    }

    public function test_a_new_client_sees_no_sample_numbers_on_the_overview(): void
    {
        $this->seedCrm($this->alice);
        $this->seedPayments($this->alice);

        $lines = app(\App\Services\LayerSummaries::class)->forClient($this->bob);

        $this->assertSame('No sources connected yet — connect one in Data Collection', $lines['L1']['summary']);
        foreach (['L2', 'L3', 'L4', 'L5', 'L6', 'L8'] as $code) {
            $this->assertSame('No data yet — fills in once your data is connected', $lines[$code]['summary'], $code);
            $this->assertSame(0, $lines[$code]['dots'], $code);
        }
    }
}
