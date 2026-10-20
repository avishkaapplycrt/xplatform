<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\MiraPremium\MiraGoogleConnection;
use App\Models\MiraPremium\MiraProject;
use App\Services\MiraPremium\GoogleDataService;
use App\Services\MiraPremium\Period;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * "Connect Google": OAuth with signed state + PKCE, encrypted token storage,
 * property matching and selection, the Search Console panel, GA4 as the
 * Traffic source, token refresh / revocation and disconnect.
 */
class MiraPremiumGoogleTest extends TestCase
{
    private Client $owner;
    private MiraProject $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_03_18_000002_create_clients_table.php',
            'database/migrations/2026_06_17_000001_create_analytics_tables.php',
            'database/migrations/2026_10_15_000001_create_mira_premium_tables.php',
            'database/migrations/2026_10_16_000001_create_mira_seo_snapshots_table.php',
            'database/migrations/2026_10_17_000001_add_insights_to_mira_visibility_answers.php',
            'database/migrations/2026_10_19_000001_add_tracking_controls_to_mira_tables.php',
            'database/migrations/2026_10_20_000001_create_mira_google_connections_table.php',
        ]]);

        config(['mira_premium.dataforseo.demo' => false, 'mira_premium.unlock_all' => null, 'cache.default' => 'array',
            'services.google.client_id' => 'cid.apps.googleusercontent.com', 'services.google.client_secret' => 'secret',
            'services.google.redirect' => 'http://127.0.0.1:8000/app/integrations/google/callback']);

        $this->owner = Client::forceCreate(['name' => 'Ria', 'company_name' => 'Acme', 'email' => 'r@acme.test', 'password' => 'x', 'status' => 'active', 'plan' => 'professional']);
        $this->project = MiraProject::create(['client_id' => $this->owner->id, 'name' => 'Acme', 'url' => 'https://acme.test/', 'domain' => 'acme.test', 'brand_terms' => ['Acme']]);
        $this->actingAs($this->owner, 'client');
    }

    protected function tearDown(): void
    {
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    private function idToken(array $claims): string
    {
        $b = fn ($v) => rtrim(strtr(base64_encode(json_encode($v)), '+/', '-_'), '=');

        return $b(['alg' => 'RS256']) . '.' . $b($claims) . '.sig';
    }

    private function connect(array $attrs = []): MiraGoogleConnection
    {
        return MiraGoogleConnection::create($attrs + [
            'client_id' => $this->owner->id, 'email' => 'ria@acme.test', 'access_token' => 'at-1', 'refresh_token' => 'rt-1',
            'expires_at' => now()->addHour(), 'status' => 'active', 'connected_at' => now(),
            'scopes' => 'openid email https://www.googleapis.com/auth/webmasters.readonly https://www.googleapis.com/auth/analytics.readonly',
        ]);
    }

    /** A fake Google: account listings, Search Console analytics and GA4 reports keyed by what the request asks for. */
    private function fakeGoogle(array $overrides = []): void
    {
        Http::fake(function (HttpRequest $req) use ($overrides) {
            $url = $req->url();
            foreach ($overrides as $prefix => $response) {
                if (str_starts_with($url, $prefix)) {
                    return is_callable($response) ? $response($req) : $response;
                }
            }
            if (str_starts_with($url, 'https://www.googleapis.com/webmasters/v3/sites') && !str_contains($url, 'searchAnalytics')) {
                return Http::response(['siteEntry' => [
                    ['siteUrl' => 'sc-domain:acme.test', 'permissionLevel' => 'siteOwner'],
                    ['siteUrl' => 'https://other.test/', 'permissionLevel' => 'siteFullUser'],
                    ['siteUrl' => 'https://pending.test/', 'permissionLevel' => 'siteUnverifiedUser'],
                ]]);
            }
            if (str_contains($url, '/searchAnalytics/query')) {
                $body = $req->data();
                $dims = $body['dimensions'] ?? [];
                $isCur = $body['startDate'] === now()->subDays(30)->toDateString();
                if ($dims === []) {
                    return Http::response(['rows' => [$isCur
                        ? ['clicks' => 1200, 'impressions' => 40000, 'ctr' => 0.03, 'position' => 8.24]
                        : ['clicks' => 1000, 'impressions' => 40000, 'ctr' => 0.025, 'position' => 9.5]]]);
                }
                if ($dims === ['date']) {
                    return Http::response(['rows' => [
                        ['keys' => [now()->subDays(3)->toDateString()], 'clicks' => 30, 'impressions' => 1000, 'ctr' => .03, 'position' => 8],
                        ['keys' => [now()->subDays(2)->toDateString()], 'clicks' => 10, 'impressions' => 3000, 'ctr' => .0033, 'position' => 12],
                    ]]);
                }
                $rows = ['query' => [['keys' => ['acme crm'], 'clicks' => 300, 'impressions' => 2000, 'ctr' => .15, 'position' => 1.2]],
                    'page' => [['keys' => ['https://acme.test/pricing?ref=x'], 'clicks' => 120, 'impressions' => 900, 'ctr' => .133, 'position' => 3.4]],
                    'country' => [['keys' => ['aus'], 'clicks' => 700, 'impressions' => 20000, 'ctr' => .035, 'position' => 7.1]],
                    'device' => [['keys' => ['MOBILE'], 'clicks' => 500, 'impressions' => 21000, 'ctr' => .024, 'position' => 9.0]]][$dims[0]];

                return Http::response(['rows' => $rows]);
            }
            if (str_starts_with($url, 'https://analyticsadmin.googleapis.com/v1beta/accountSummaries')) {
                return Http::response(['accountSummaries' => [['displayName' => 'Acme Pty', 'propertySummaries' => [
                    ['property' => 'properties/111', 'displayName' => 'acme.test – GA4'], ['property' => 'properties/222', 'displayName' => 'Blog'],
                ]]]]);
            }
            if (str_contains($url, 'analyticsdata.googleapis.com/v1beta/properties/111:runReport')) {
                return Http::response($this->ga4Report($req->data()));
            }

            return Http::response(['error' => ['message' => 'unexpected ' . $url]], 500);
        });
    }

    private function ga4Report(array $body): array
    {
        $dims = array_column($body['dimensions'] ?? [], 'name');
        $mets = array_column($body['metrics'], 'name');
        $isCur = $body['dateRanges'][0]['startDate'] === now()->subDays(30)->toDateString();
        $row = fn (array $d, array $m) => ['dimensionValues' => array_map(fn ($v) => ['value' => $v], $d), 'metricValues' => array_map(fn ($v) => ['value' => (string) $v], $m)];
        $rows = match ($dims[0] ?? null) {
            null             => [$row([], $isCur ? [500, 400, 1500, 3, 95.4, 0.42] : [400, 330, 1100, 2.75, 80, 0.5])],
            'date'           => [$row([now()->subDays(2)->format('Ymd')], [20, 60])],
            'pagePath'       => [$row(['/pricing'], [300, 120])],
            'sessionSource'  => in_array('sessions', $mets, true) && ($body['limit'] ?? 0) === 500
                ? ($isCur ? [$row(['google'], [300]), $row(['chatgpt.com'], [40]), $row(['perplexity.ai'], [5])] : [$row(['chatgpt.com'], [25])])
                : [$row(['google'], [300]), $row(['(direct)'], [120])],
            'deviceCategory' => [$row(['desktop'], [320])],
            'landingPage'    => [$row(['/pricing'], [30])],
        };

        return ['dimensionHeaders' => array_map(fn ($n) => ['name' => $n], $dims), 'metricHeaders' => array_map(fn ($n) => ['name' => $n], $mets), 'rows' => $rows];
    }

    // ------------------------------------------------------------- oauth flow

    public function test_connect_redirects_to_google_with_signed_state_and_pkce(): void
    {
        $res = $this->get('/app/integrations/google/connect?project=' . $this->project->id)->assertRedirect();
        $url = $res->headers->get('Location');
        parse_str(parse_url($url, PHP_URL_QUERY), $q);

        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $url);
        $this->assertSame('cid.apps.googleusercontent.com', $q['client_id']);
        $this->assertSame('http://127.0.0.1:8000/app/integrations/google/callback', $q['redirect_uri']);
        $this->assertSame(['offline', 'consent', 'S256'], [$q['access_type'], $q['prompt'], $q['code_challenge_method']]);
        $this->assertStringContainsString('https://www.googleapis.com/auth/webmasters.readonly', $q['scope']);
        $this->assertStringContainsString('https://www.googleapis.com/auth/analytics.readonly', $q['scope']);
        $this->assertCount(2, explode('.', $q['state']));
    }

    public function test_connect_requires_sign_in_and_an_owned_project(): void
    {
        $other = Client::forceCreate(['name' => 'Bo', 'company_name' => 'B', 'email' => 'b@b.test', 'password' => 'x', 'status' => 'active', 'plan' => 'professional']);
        $foreign = MiraProject::create(['client_id' => $other->id, 'name' => 'B', 'url' => 'https://b.test/', 'domain' => 'b.test']);

        $this->get('/app/integrations/google/connect?project=' . $foreign->id)->assertRedirect()
            ->assertRedirectContains('google=error');

        auth('client')->logout();
        $this->get('/app/integrations/google/connect')->assertRedirect(route('client.login'));
    }

    public function test_callback_stores_encrypted_tokens_and_preselects_matching_properties(): void
    {
        $location = $this->get('/app/integrations/google/connect?project=' . $this->project->id)->headers->get('Location');
        parse_str(parse_url($location, PHP_URL_QUERY), $q);

        $this->fakeGoogle(['https://oauth2.googleapis.com/token' => function (HttpRequest $req) use ($q) {
            // PKCE: the verifier sent now must hash to the challenge sent earlier.
            $this->assertSame($q['code_challenge'], rtrim(strtr(base64_encode(hash('sha256', $req->data()['code_verifier'], true)), '+/', '-_'), '='));
            $this->assertSame('the-code', $req->data()['code']);

            return Http::response(['access_token' => 'at-new', 'refresh_token' => 'rt-new', 'expires_in' => 3599, 'scope' => implode(' ', config('mira_premium.google.scopes')),
                'id_token' => $this->idToken(['sub' => '1234', 'email' => 'ria@acme.test'])]);
        }]);

        // The callback does not rely on the session (the redirect host can differ from the page's).
        auth('client')->logout();
        $this->get('/app/integrations/google/callback?' . http_build_query(['code' => 'the-code', 'state' => $q['state']]))
            ->assertRedirect()->assertRedirectContains('/mira-premium?project=' . $this->project->id)->assertRedirectContains('google=connected');

        $conn = MiraGoogleConnection::where('client_id', $this->owner->id)->firstOrFail();
        $this->assertSame(['ria@acme.test', '1234', 'at-new', 'rt-new', 'active'], [$conn->email, $conn->google_sub, $conn->access_token, $conn->refresh_token, $conn->status]);
        $raw = DB::table('mira_google_connections')->first();
        $this->assertStringNotContainsString('at-new', $raw->access_token);
        $this->assertStringNotContainsString('rt-new', $raw->refresh_token);

        $this->project->refresh();
        $this->assertSame('sc-domain:acme.test', $this->project->gsc_site_url);
        $this->assertSame('properties/111', $this->project->ga4_property);

        // The same state can't be replayed.
        $this->get('/app/integrations/google/callback?' . http_build_query(['code' => 'the-code', 'state' => $q['state']]))
            ->assertRedirectContains('google=error');
    }

    public function test_callback_rejects_tampered_state_and_reports_cancel(): void
    {
        $location = $this->get('/app/integrations/google/connect')->headers->get('Location');
        parse_str(parse_url($location, PHP_URL_QUERY), $q);
        [$payload, $sig] = explode('.', $q['state']);
        $forged = rtrim(strtr(base64_encode(json_encode(['c' => 999] + json_decode(base64_decode(strtr($payload, '-_', '+/')), true))), '+/', '-_'), '=') . '.' . $sig;
        Http::fake();

        $this->get('/app/integrations/google/callback?' . http_build_query(['code' => 'x', 'state' => $forged]))->assertRedirectContains('google=error');
        $this->get('/app/integrations/google/callback?' . http_build_query(['error' => 'access_denied', 'state' => $q['state']]))
            ->assertRedirectContains('cancelled');
        $this->assertSame(0, MiraGoogleConnection::count());
        Http::assertNothingSent();
    }

    // ---------------------------------------------------------- picker + panel

    public function test_status_lists_properties_and_select_only_accepts_the_accounts_own(): void
    {
        $this->connect();
        $this->fakeGoogle();
        $id = $this->project->id;

        $g = $this->getJson("/mira-premium/api/projects/{$id}/google")->assertOk()->json('google');
        $this->assertTrue($g['connected']);
        $this->assertSame(['https://other.test/', 'sc-domain:acme.test'], array_column($g['sites'], 'site_url'));
        $this->assertSame(['gsc_site_url' => 'sc-domain:acme.test', 'ga4_property' => 'properties/111'], $g['suggested']);

        $this->patchJson("/mira-premium/api/projects/{$id}/google", ['gsc_site_url' => 'https://pending.test/'])->assertStatus(422)->assertJsonPath('error.field', 'gsc_site_url');
        $this->patchJson("/mira-premium/api/projects/{$id}/google", ['ga4_property' => 'properties/999'])->assertStatus(422);
        $this->patchJson("/mira-premium/api/projects/{$id}/google", ['gsc_site_url' => 'https://other.test/', 'ga4_property' => 'properties/222'])->assertOk()
            ->assertJsonPath('google.selected.gsc_site_url', 'https://other.test/');
        $this->assertSame('properties/222', $this->project->fresh()->ga4_property);
    }

    public function test_search_console_panel_states(): void
    {
        $id = $this->project->id;
        $this->getJson("/mira-premium/api/projects/{$id}/panels/gsc")->assertOk()->assertJsonPath('data.reason', 'not_connected')
            ->assertJsonPath('data.connect_url', route('mira-premium.google.connect', ['project' => $id]));

        $this->connect();
        $this->getJson("/mira-premium/api/projects/{$id}/panels/gsc")->assertJsonPath('data.reason', 'pick_site');

        MiraGoogleConnection::first()->update(['status' => 'revoked']);
        $this->getJson("/mira-premium/api/projects/{$id}/panels/gsc")->assertJsonPath('data.reason', 'reconnect');
    }

    public function test_search_console_panel_reports_googles_numbers_with_deltas(): void
    {
        $this->connect();
        $this->project->update(['gsc_site_url' => 'sc-domain:acme.test']);
        $this->fakeGoogle();

        $d = $this->getJson("/mira-premium/api/projects/{$this->project->id}/panels/gsc?period=30d")->assertOk()->json('data');
        $this->assertTrue($d['connected']);
        $this->assertSame(['value' => 1200, 'previous' => 1000, 'delta' => 200], $d['metrics']['clicks']);
        $this->assertEquals(3.0, $d['metrics']['ctr']['value']);
        $this->assertEquals(0.5, $d['metrics']['ctr']['delta']);
        $this->assertEquals(8.2, $d['metrics']['position']['value']);
        $this->assertEquals(-1.3, $d['metrics']['position']['delta']);
        $this->assertSame('acme crm', $d['queries'][0]['query']);
        $this->assertSame('/pricing?ref=x', $d['pages'][0]['path']);
        $this->assertSame('aus', $d['countries'][0]['country']);
        $this->assertSame(now()->subDays(2)->toDateString(), $d['data_through']);
        $this->assertCount(30, $d['series']['current']);
        // Position per bucket is impression-weighted, CTR recomputed from the bucket's totals.
        $day = collect($d['series']['current'])->firstWhere('date', now()->subDays(2)->toDateString());
        $this->assertSame(10, $day['clicks']);
        $this->assertEquals(12.0, $day['position']);

        // Cached: a second view makes no new Google calls.
        Http::fake(fn () => throw new \RuntimeException('should be cached'));
        $this->getJson("/mira-premium/api/projects/{$this->project->id}/panels/gsc?period=30d")->assertOk();
    }

    public function test_twelve_months_has_no_partial_previous_year(): void
    {
        $this->connect();
        $this->project->update(['gsc_site_url' => 'sc-domain:acme.test']);
        $this->fakeGoogle();

        $d = $this->getJson("/mira-premium/api/projects/{$this->project->id}/panels/gsc?period=12m")->assertOk()->json('data');
        $this->assertFalse($d['previous_complete']);
        $this->assertNull($d['metrics']['clicks']['previous']);
        $this->assertNull($d['metrics']['clicks']['delta']);
    }

    // -------------------------------------------------------------------- ga4

    public function test_traffic_uses_ga4_when_there_is_no_tracking_script(): void
    {
        $this->connect();
        $this->project->update(['ga4_property' => 'properties/111']);
        $this->fakeGoogle();

        $d = $this->getJson("/mira-premium/api/projects/{$this->project->id}/panels/traffic?period=30d")->assertOk()->json('data');
        $this->assertSame('ga4', $d['source']);
        $this->assertSame(['value' => 500, 'previous' => 400, 'delta' => 100], $d['metrics']['visits']);
        $this->assertEquals(42.0, $d['metrics']['bounce_rate']['value']);
        $this->assertSame(95, $d['metrics']['visit_duration']['value']);
        $this->assertSame('Direct', $d['sources'][1]['source']);
        $this->assertSame(['value' => 45, 'delta' => 20], $d['ai_traffic']['visits']);
        $this->assertSame('ChatGPT', $d['ai_traffic']['platforms'][0]['platform']);
        $this->assertSame(20, collect($d['series']['current'])->firstWhere('date', now()->subDays(2)->toDateString())['visits']);
    }

    public function test_tracking_script_wins_over_ga4(): void
    {
        $this->connect();
        $this->project->update(['ga4_property' => 'properties/111']);
        DB::table('analytics_sites')->insert(['client_id' => $this->owner->id, 'domain' => 'acme.test', 'name' => 'Acme', 'tracking_id' => 'XP-1', 'api_key' => 'k1', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        Http::fake();

        $this->assertSame('tracking_script', $this->getJson("/mira-premium/api/projects/{$this->project->id}/panels/traffic")->json('data.source'));
        Http::assertNothingSent();
    }

    // ---------------------------------------------------- refresh + disconnect

    public function test_expired_token_is_refreshed_and_a_refused_refresh_marks_revoked(): void
    {
        $conn = $this->connect(['expires_at' => now()->subMinutes(5)]);
        $this->fakeGoogle(['https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'at-2', 'expires_in' => 3600])]);
        $this->getJson("/mira-premium/api/projects/{$this->project->id}/google")->assertOk()->assertJsonCount(2, 'google.sites');
        $this->assertSame('at-2', $conn->fresh()->access_token);
        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'webmasters') && $r->hasHeader('Authorization', 'Bearer at-2'));

        $conn->refresh()->update(['expires_at' => now()->subMinutes(5)]);
        Http::swap(new \Illuminate\Http\Client\Factory());
        $this->fakeGoogle(['https://oauth2.googleapis.com/token' => Http::response(['error' => 'invalid_grant'], 400)]);
        $g = $this->getJson("/mira-premium/api/projects/{$this->project->id}/google")->assertOk()->json('google');
        $this->assertStringContainsString('revoked', $g['errors']['search_console']);
        $this->assertSame('revoked', $conn->fresh()->status);
    }

    public function test_disconnect_revokes_at_google_and_deletes_tokens(): void
    {
        $this->connect();
        Http::fake(['https://oauth2.googleapis.com/revoke' => Http::response([], 200)]);

        $this->postJson('/mira-premium/api/google/disconnect')->assertOk()->assertJsonPath('google.connected', false);
        $this->assertSame(0, MiraGoogleConnection::count());
        Http::assertSent(fn (HttpRequest $r) => $r->url() === 'https://oauth2.googleapis.com/revoke' && $r->data()['token'] === 'rt-1');
    }

    public function test_property_matching(): void
    {
        $this->assertSame('sc-domain:acme.test', GoogleDataService::matchSite(['https://acme.test/', 'sc-domain:acme.test'], 'www.acme.test'));
        $this->assertSame('https://www.acme.test/', GoogleDataService::matchSite(['https://www.acme.test/', 'https://x.test/'], 'acme.test'));
        $this->assertNull(GoogleDataService::matchSite(['https://x.test/'], 'acme.test'));
        $this->assertNull(GoogleDataService::matchProperty([['property' => 'properties/1', 'name' => 'Acme A'], ['property' => 'properties/2', 'name' => 'Acme B']], $this->project));
        $this->assertSame('properties/3', GoogleDataService::matchProperty([['property' => 'properties/3', 'name' => 'Anything']], $this->project));

        $this->travelTo('2026-10-20 10:00:00');
        $this->assertSame(['2026-09-20', '2026-10-19', '2026-08-21', '2026-09-19'], GoogleDataService::dates(Period::fromKey('30d')));
    }
}
