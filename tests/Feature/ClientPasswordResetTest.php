<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Notifications\ClientResetPassword;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** "Forgot password" for clients: the email links to the client reset page, and the reset works. */
class ClientPasswordResetTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_03_18_000002_create_clients_table.php']);
        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });
        $this->client = Client::forceCreate(['name' => 'Ria', 'company_name' => 'Acme', 'email' => 'r@acme.test',
            'password' => bcrypt('oldpass123'), 'status' => 'active', 'plan' => 'free']);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    public function test_reset_link_email_points_to_the_client_reset_page_and_resets_the_password(): void
    {
        Notification::fake();

        $this->post('/app/forgot-password', ['email' => 'r@acme.test'])
            ->assertRedirect()->assertSessionHas('success');

        $url = null;
        Notification::assertSentTo($this->client, ClientResetPassword::class, function ($n) use (&$url) {
            $url = $n->toMail($this->client)->actionUrl;

            return true;
        });
        $this->assertStringContainsString('/app/reset-password/', $url);
        $this->assertStringContainsString('email=r%40acme.test', $url);

        $path = parse_url($url, PHP_URL_PATH) . '?' . parse_url($url, PHP_URL_QUERY);
        $this->get($path)->assertOk();

        $token = basename(parse_url($url, PHP_URL_PATH));
        $this->post('/app/reset-password', ['token' => $token, 'email' => 'r@acme.test',
            'password' => 'newpass456', 'password_confirmation' => 'newpass456'])
            ->assertRedirect(route('client.login'));

        $this->assertTrue(Hash::check('newpass456', $this->client->fresh()->password));
    }

    public function test_unknown_email_gets_the_same_message(): void
    {
        Notification::fake();

        $this->post('/app/forgot-password', ['email' => 'nobody@acme.test'])
            ->assertRedirect()->assertSessionHas('success');
        Notification::assertNothingSent();
    }
}
