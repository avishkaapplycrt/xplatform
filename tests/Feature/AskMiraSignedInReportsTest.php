<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Ask Mira on the homepage: signed-in clients get the full "Analyse my
 * website" and "Compare with a competitor" reports (fixes included, nothing
 * locked); visitors who aren't signed in get the locked version.
 */
class AskMiraSignedInReportsTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_03_18_000002_create_clients_table.php',
            'database/migrations/2026_03_26_004647_create_industries_table.php',
            'database/migrations/2026_05_13_000002_drop_json_columns_from_industries_table.php',
            'database/migrations/2026_08_17_000000_create_chat_bots_table.php',
            'database/migrations/2026_08_20_000000_create_chat_bot_users_table.php',
            'database/migrations/2026_08_20_000001_change_website_url_to_text_on_chat_bot_users_table.php',
            'database/migrations/2026_10_15_000001_create_mira_premium_tables.php',
        ]]);
        config(['mira_premium.unlock_all' => null]);
        $this->client = Client::forceCreate(['name' => 'Ria', 'company_name' => 'Acme', 'email' => 'r@acme.test',
            'password' => bcrypt('x'), 'status' => 'active', 'plan' => 'free']);

        $page = '<!doctype html><html lang="en"><head><title>Test page title for the site</title></head><body><h1>Hi</h1><p>'
            . str_repeat('We help local businesses understand their customers with clear reports. ', 20) . '</p></body></html>';
        Http::fake([
            'https://www.googleapis.com/*' => Http::response([], 500),
            'https://api.openai.com/*'     => Http::response([], 500),   // AI fixes unavailable → built-in fixes
            'https://*/robots.txt'         => Http::response('', 404),
            'https://*/sitemap.xml'        => Http::response('', 404),
            'https://*/llms.txt'           => Http::response('', 404),
            'https://mine.test/*'          => Http::response($page),
            'https://rival.test/*'         => Http::response($page . '<meta name="description" content="' . str_repeat('x', 140) . '">'),
        ]);
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    public function test_signed_in_comparison_is_not_locked(): void
    {
        $this->actingAs($this->client, 'client')
            ->postJson('/chat/compare', ['url' => 'https://mine.test/', 'competitor_url' => 'https://rival.test/'])
            ->assertOk()->assertJsonPath('comparison.locked', false);
    }

    public function test_guest_comparison_is_still_locked(): void
    {
        // Sending premium: true doesn't unlock it either — that needs a Premium plan.
        $this->postJson('/chat/compare', ['url' => 'https://mine.test/', 'competitor_url' => 'https://rival.test/',
            'name' => 'Guest', 'email' => 'guest@example.invalid', 'premium' => true])
            ->assertOk()->assertJsonPath('comparison.locked', true);
    }

    public function test_signed_in_analysis_includes_the_fixes(): void
    {
        $res = $this->actingAs($this->client, 'client')
            ->postJson('/chat/send', ['message' => 'https://mine.test/'])->assertOk();

        $this->assertNotSame('none', $res->json('report.recommendations_source'));   // fixes included → not locked
        $fixes = collect($res->json('report.categories'))->flatMap(fn ($c) => $c['checks'])->pluck('recommendation')->filter();
        $this->assertNotEmpty($fixes);
    }

    public function test_guest_analysis_has_no_fixes(): void
    {
        $res = $this->postJson('/chat/analyze-lead', ['url' => 'https://mine.test/', 'name' => 'Guest', 'email' => 'guest@example.invalid'])
            ->assertOk();

        $this->assertSame('none', $res->json('report.recommendations_source'));   // locked behind Try Premium Mira
    }
}
