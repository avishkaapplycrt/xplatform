<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Services\MiraPremium\Entitlement;
use Tests\TestCase;

/** Sign-up step 3: choosing a plan, which starts that plan's free trial. */
class SignupPlanStepTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_03_18_000002_create_clients_table.php',
            'database/migrations/2026_03_26_004647_create_industries_table.php',
            'database/migrations/2026_05_13_000002_drop_json_columns_from_industries_table.php',
            // The home page lists industries and their starter questions.
            'database/migrations/2026_08_20_000002_create_chat_bot_questions_table.php',
        ]]);
        config(['mira_premium.unlock_all' => null]);
        $this->client = Client::forceCreate(['name' => 'Ria', 'company_name' => 'Acme', 'email' => 'r@acme.test',
            'password' => bcrypt('x'), 'status' => 'pending', 'plan' => 'free']);
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    public function test_sign_up_shows_three_steps(): void
    {
        $this->get('/app/register')->assertOk()->assertSee('Step 1 of 3');
        $this->actingAs($this->client, 'client')->get('/app/plan')->assertOk()
            ->assertSee('Step 3 of 3')->assertSee('Choose your plan')
            ->assertSee('Starter')->assertSee('Growth')->assertSee('Enterprise');
    }

    public function test_choosing_a_plan_starts_its_free_trial(): void
    {
        $this->actingAs($this->client, 'client')
            ->post('/app/plan', ['plan' => 'professional'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $c = $this->client->fresh();
        $this->assertSame('professional', $c->plan);
        $this->assertTrue($c->trial_ends_at->between(now()->addDays(29), now()->addDays(31)));
        // The plan ends with the trial (until billing exists), so it can't stay active for good.
        $this->assertEquals($c->trial_ends_at->timestamp, $c->subscription_ends_at->timestamp);

        $who = Entitlement::resolve($c, 'guest:x');
        $this->assertSame('active_trial', $who->status);
        $this->assertTrue($who->isPremium());
    }

    public function test_after_the_trial_the_plan_has_lapsed(): void
    {
        $this->actingAs($this->client, 'client')->post('/app/plan', ['plan' => 'starter']);
        $this->travel(31)->days();

        $who = Entitlement::resolve($this->client->fresh(), 'guest:x');
        $this->assertSame('expired', $who->status);
        $this->assertFalse($who->isPremium());
    }

    public function test_switching_plan_keeps_the_running_trial(): void
    {
        $this->actingAs($this->client, 'client')->post('/app/plan', ['plan' => 'starter']);
        $first = $this->client->fresh()->trial_ends_at;
        $this->travel(5)->days();
        $this->post('/app/plan', ['plan' => 'enterprise']);

        $c = $this->client->fresh();
        $this->assertSame('enterprise', $c->plan);
        $this->assertEquals($first->timestamp, $c->trial_ends_at->timestamp);
    }

    public function test_continuing_with_a_plan_saves_it_for_billing_without_a_trial(): void
    {
        $this->actingAs($this->client, 'client')
            ->post('/app/plan', ['plan' => 'professional', 'action' => 'continue'])
            ->assertRedirect(route('home'))->assertSessionHas('success');

        $c = $this->client->fresh();
        $this->assertSame('professional', $c->plan);
        $this->assertNull($c->trial_ends_at);
        $this->assertFalse($c->subscription_ends_at->isFuture());   // awaiting billing

        // Not active until the payment gateway records a payment…
        $this->assertFalse(Entitlement::resolve($c, 'guest:x')->isPremium());

        // …which it does by moving subscription_ends_at forward.
        $c->forceFill(['subscription_ends_at' => now()->addMonth()])->save();
        $who = Entitlement::resolve($c->fresh(), 'guest:x');
        $this->assertSame('active', $who->status);
        $this->assertTrue($who->isPremium());
    }

    public function test_the_plan_page_offers_both_buttons(): void
    {
        $this->actingAs($this->client, 'client')->get('/app/plan')->assertOk()
            ->assertSee('Start free trial')->assertSee('Continue with');
    }

    public function test_the_home_page_shows_the_success_message(): void
    {
        $this->actingAs($this->client, 'client')->followingRedirects()
            ->post('/app/plan', ['plan' => 'starter', 'action' => 'continue'])
            ->assertOk()->assertSee("you've chosen the Starter plan")->assertSee('flash-ok', false);
    }

    public function test_an_unknown_plan_is_rejected(): void
    {
        $this->actingAs($this->client, 'client')->from('/app/plan')
            ->post('/app/plan', ['plan' => 'free'])->assertRedirect('/app/plan')->assertSessionHasErrors('plan');
        $this->assertSame('free', $this->client->fresh()->plan);
    }
}
