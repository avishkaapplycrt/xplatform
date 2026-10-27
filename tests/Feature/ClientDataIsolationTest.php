<?php

namespace Tests\Feature;

use App\Models\BrevoDeliveredRecipient;
use App\Models\Client;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\CrmIntegration;
use App\Models\EmailLog;
use App\Models\InstagramAccountInsight;
use App\Models\InstagramMedia;
use App\Models\SocialIntegration;
use App\Services\RealAccountsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Each client only sees their own CRM, social and email data. Before this,
 * CRM and social connections had no owner, so a newly registered client saw
 * everyone's contacts and deals in Business Helpers.
 */
class ClientDataIsolationTest extends TestCase
{
    private Client $alice;
    private Client $bob;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_03_18_000002_create_clients_table.php',
            'database/migrations/2026_07_09_081744_create_crm_integrations_table.php',
            'database/migrations/2026_07_09_090221_create_social_integrations_table.php',
            'database/migrations/2026_08_25_000003_create_crm_contacts_table.php',
            'database/migrations/2026_08_25_000004_create_crm_deals_table.php',
            'database/migrations/2026_09_15_010457_create_instagram_media_table.php',
            'database/migrations/2026_09_15_010458_create_instagram_account_insights_table.php',
        ]]);
        // The email tables' migration history is long; these hold the columns used here.
        Schema::create('email_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id');
            $t->string('email_address');
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
        });
        Schema::create('email_logs_providers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->nullable();
            $t->string('campaign_id')->nullable();
            $t->string('email');
            $t->timestamps();
        });

        $this->alice = $this->makeClient(1, 'test@test.com', 'Alice Co');
        $this->bob   = $this->makeClient(2, 'bob@bob.test', 'Bob Co');
        $this->runIsolationMigration();
    }

    protected function tearDown(): void
    {
        Auth::guard('client')->logout();
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_logs_providers');
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    private function makeClient(int $id, string $email, string $company): Client
    {
        return Client::forceCreate(['id' => $id, 'name' => $company, 'company_name' => $company, 'email' => $email,
            'password' => bcrypt('x'), 'status' => 'active', 'plan' => 'free']);
    }

    private function runIsolationMigration(): void
    {
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_10_27_000001_add_client_id_to_crm_and_social_integrations.php',
            'database/migrations/2026_10_27_000002_add_client_id_to_synced_data_tables.php',
        ]]);
    }

    /** One client's CRM, Instagram and email data, saved while that client is signed in. */
    private function seedDataFor(Client $client, string $company): void
    {
        Auth::guard('client')->setUser($client);

        $crm = CrmIntegration::create(['provider' => 'hubspot', 'connection_name' => 'HubSpot', 'status' => 'connected']);
        CrmContact::create(['connection_id' => $crm->id, 'provider' => 'hubspot', 'external_id' => 'c-' . $client->id,
            'email' => 'buyer@' . $client->id . '.test', 'company' => $company, 'last_activity_at' => now()]);
        CrmDeal::create(['connection_id' => $crm->id, 'provider' => 'hubspot', 'external_id' => 'd-' . $client->id,
            'name' => $company . ' renewal', 'value' => 1000 * $client->id, 'status' => 'open']);

        $ig = SocialIntegration::create(['platform' => 'instagram', 'connection_name' => 'Instagram', 'status' => 'connected']);
        InstagramMedia::create(['social_integration_id' => $ig->id, 'media_id' => 'm-' . $client->id, 'posted_at' => now()]);
        InstagramAccountInsight::create(['social_integration_id' => $ig->id, 'date' => now()->toDateString(), 'followers_count' => 10]);

        EmailLog::create(['email_address' => 'reader@' . $client->id . '.test', 'sent_at' => now()]);
        BrevoDeliveredRecipient::create(['campaign_id' => '1', 'email' => 'reader@' . $client->id . '.test']);

        Auth::guard('client')->logout();
    }

    public function test_new_rows_are_saved_under_the_signed_in_client(): void
    {
        $this->seedDataFor($this->bob, 'Bob Co');

        $this->assertSame([2], CrmIntegration::pluck('client_id')->unique()->values()->all());
        $this->assertSame([2], SocialIntegration::pluck('client_id')->unique()->values()->all());
        $this->assertSame([2], EmailLog::pluck('client_id')->unique()->values()->all());
        $this->assertSame([2], BrevoDeliveredRecipient::pluck('client_id')->unique()->values()->all());
        // Synced rows carry their owner too, taken from their connection.
        $this->assertSame([2], CrmContact::pluck('client_id')->unique()->values()->all());
        $this->assertSame([2], CrmDeal::pluck('client_id')->unique()->values()->all());
        $this->assertSame([2], InstagramMedia::pluck('client_id')->unique()->values()->all());
        $this->assertSame([2], InstagramAccountInsight::pluck('client_id')->unique()->values()->all());
    }

    public function test_rows_saved_with_nobody_signed_in_get_the_owner_of_their_connection(): void
    {
        // Queued syncs run with nobody signed in.
        $crm = CrmIntegration::forceCreate(['client_id' => 2, 'provider' => 'hubspot', 'connection_name' => 'HubSpot', 'status' => 'connected']);
        $contact = CrmContact::create(['connection_id' => $crm->id, 'provider' => 'hubspot', 'external_id' => 'x1']);
        $ig = SocialIntegration::forceCreate(['client_id' => 1, 'platform' => 'instagram', 'connection_name' => 'IG', 'status' => 'connected']);
        $media = InstagramMedia::create(['social_integration_id' => $ig->id, 'media_id' => 'm1']);

        $this->assertSame(2, (int) $contact->client_id);
        $this->assertSame(1, (int) $media->client_id);
    }

    public function test_a_signed_in_client_only_sees_their_own_data(): void
    {
        $this->seedDataFor($this->alice, 'Alice Co');
        $this->seedDataFor($this->bob, 'Bob Co');

        Auth::guard('client')->setUser($this->bob);

        $this->assertSame(['Bob Co'], CrmContact::pluck('company')->all());
        $this->assertSame(['Bob Co renewal'], CrmDeal::pluck('name')->all());
        $this->assertEquals(2000, CrmDeal::sum('value'));
        $this->assertSame(1, CrmIntegration::count());
        $this->assertSame(1, SocialIntegration::count());
        $this->assertSame(['m-2'], InstagramMedia::pluck('media_id')->all());
        $this->assertSame(1, InstagramAccountInsight::count());
        $this->assertSame(['reader@2.test'], EmailLog::pluck('email_address')->all());
        $this->assertSame(['reader@2.test'], BrevoDeliveredRecipient::pluck('email')->all());

        // Raw queries that start from the model are filtered too.
        $this->assertSame(1, CrmContact::query()->toBase()->count());
        // Another client's connection can't be opened by id.
        $aliceCrm = CrmIntegration::withoutGlobalScopes()->where('client_id', 1)->value('id');
        $this->assertNull(CrmIntegration::find($aliceCrm));
    }

    public function test_business_helpers_accounts_only_show_the_signed_in_clients_customers(): void
    {
        $this->seedDataFor($this->alice, 'Alice Co');
        $this->seedDataFor($this->bob, 'Bob Co');

        Auth::guard('client')->setUser($this->bob);
        $names = collect(app(RealAccountsService::class)->build())->pluck('name')->all();

        $this->assertContains('Bob Co', $names);
        $this->assertNotContains('Alice Co', $names);
    }

    public function test_a_new_client_with_no_crm_sees_nothing(): void
    {
        $this->seedDataFor($this->alice, 'Alice Co');
        $carol = $this->makeClient(3, 'carol@carol.test', 'Carol Co');

        Auth::guard('client')->setUser($carol);

        $this->assertSame(0, CrmContact::count());
        $this->assertSame(0, CrmDeal::count());
        $this->assertSame(0, InstagramMedia::count());
        $this->assertSame(0, EmailLog::count());
        $this->assertSame([], collect(app(RealAccountsService::class)->build())->all());
    }

    public function test_with_nobody_signed_in_all_rows_are_visible_and_for_client_picks_one(): void
    {
        $this->seedDataFor($this->alice, 'Alice Co');
        $this->seedDataFor($this->bob, 'Bob Co');

        // Queued jobs, console commands and webhooks run with nobody signed in.
        $this->assertSame(2, CrmContact::count());
        $this->assertSame(2, InstagramMedia::count());

        $this->assertSame(['Alice Co'], CrmContact::forClient($this->alice)->pluck('company')->all());
        $this->assertSame(1, CrmIntegration::forClient(1)->count());
        $this->assertSame(1, EmailLog::forClient($this->bob)->count());

        // forClient() ignores whoever is signed in.
        Auth::guard('client')->setUser($this->bob);
        $this->assertSame(['Alice Co'], CrmContact::forClient($this->alice)->pluck('company')->all());
    }

    public function test_migration_gives_existing_connections_to_client_one(): void
    {
        $this->artisan('migrate:rollback', ['--path' => [
            'database/migrations/2026_10_27_000002_add_client_id_to_synced_data_tables.php',
            'database/migrations/2026_10_27_000001_add_client_id_to_crm_and_social_integrations.php',
        ]]);
        DB::table('crm_integrations')->insert(['provider' => 'zoho', 'connection_name' => 'Zoho', 'status' => 'connected']);
        DB::table('social_integrations')->insert(['platform' => 'instagram', 'connection_name' => 'IG', 'status' => 'connected']);

        $this->runIsolationMigration();

        $this->assertSame([1], DB::table('crm_integrations')->pluck('client_id')->map(fn ($v) => (int) $v)->all());
        $this->assertSame([1], DB::table('social_integrations')->pluck('client_id')->map(fn ($v) => (int) $v)->all());
    }
}
