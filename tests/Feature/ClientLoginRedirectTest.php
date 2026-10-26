<?php

namespace Tests\Feature;

use App\Models\Client;
use Tests\TestCase;

/** Where a client lands after signing in at /app/login, and after logging out. */
class ClientLoginRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_03_18_000002_create_clients_table.php',
            // The homepage lists industries and their starter questions.
            'database/migrations/2026_03_26_004647_create_industries_table.php',
            'database/migrations/2026_05_13_000002_drop_json_columns_from_industries_table.php',
            'database/migrations/2026_08_20_000002_create_chat_bot_questions_table.php',
        ]]);
        Client::forceCreate(['name' => 'Ria', 'company_name' => 'Acme', 'email' => 'r@acme.test',
            'password' => bcrypt('secret-pass'), 'status' => 'active', 'plan' => 'free']);
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    public function test_signing_in_goes_to_the_home_page(): void
    {
        $this->post('/app/login', ['email' => 'r@acme.test', 'password' => 'secret-pass'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticated('client');
    }

    public function test_signing_in_returns_to_the_page_that_asked_for_it(): void
    {
        $this->get('/app/dashboard')->assertRedirect(route('client.login'));

        $this->post('/app/login', ['email' => 'r@acme.test', 'password' => 'secret-pass'])
            ->assertRedirect(route('client.dashboard'));
    }

    public function test_the_homepage_logout_button_signs_out_and_returns_home(): void
    {
        $client = Client::where('email', 'r@acme.test')->first();
        $this->actingAs($client, 'client')->get('/')->assertOk()->assertSee('Log out')->assertDontSee('>Sign up<', false);

        $this->post('/app/logout', ['redirect' => 'home'])->assertRedirect(route('home'));
        $this->assertGuest('client');
        $this->get('/')->assertOk()->assertSee('Sign up')->assertDontSee('Log out');
    }

    public function test_logging_out_elsewhere_still_goes_to_sign_in(): void
    {
        $client = Client::where('email', 'r@acme.test')->first();
        $this->actingAs($client, 'client')->post('/app/logout')->assertRedirect(route('client.login'));
    }

    public function test_the_pricing_page_shows_the_right_account_buttons(): void
    {
        $this->get('/pricing')->assertOk()->assertSee('Sign in')->assertSee('Sign up')->assertDontSee('Log out');

        $client = Client::where('email', 'r@acme.test')->first();
        $this->actingAs($client, 'client')->get('/pricing')->assertOk()
            ->assertSee('Dashboard')->assertSee('Log out')->assertDontSee('>Sign up<', false);
    }

    public function test_the_premium_button_depends_on_sign_in(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<a class="mira-premium-btn" href="' . route('pricing') . '">&#9733; Try Premium Mira</a>', false);

        $client = Client::where('email', 'r@acme.test')->first();
        $this->actingAs($client, 'client')->get('/')->assertOk()
            ->assertSee('<a class="mira-premium-btn" href="' . route('mira-premium') . '">&#9733; Ask Mira Premium</a>', false)
            ->assertDontSee('<a class="mira-premium-btn" href="' . route('pricing') . '"', false);
    }

    public function test_wrong_password_stays_on_the_sign_in_page(): void
    {
        $this->from('/app/login')->post('/app/login', ['email' => 'r@acme.test', 'password' => 'nope'])
            ->assertRedirect('/app/login')->assertSessionHasErrors('email');
        $this->assertGuest('client');
    }
}
