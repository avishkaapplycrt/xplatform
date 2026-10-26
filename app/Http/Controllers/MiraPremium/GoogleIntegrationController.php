<?php

namespace App\Http\Controllers\MiraPremium;

use App\Http\Controllers\Controller;
use App\Models\MiraPremium\MiraProject;
use App\Services\MiraPremium\Entitlement;
use App\Services\MiraPremium\GoogleClient;
use App\Services\MiraPremium\GoogleDataService;
use App\Services\MiraPremium\MiraApiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * "Connect Google" for Mira Premium: each customer connects their own Google
 * account (read-only Search Console + Analytics 4), then picks, per project,
 * which Search Console property and GA4 property belong to it.
 *
 *   GET   app/integrations/google/connect?project=  → Google consent screen
 *   GET   app/integrations/google/callback          ← Google; stores the tokens, back to /mira-premium
 *   GET   mira-premium/api/projects/{id}/google     status + selectable properties (+ suggested match)
 *   PATCH mira-premium/api/projects/{id}/google     choose gsc_site_url / ga4_property
 *   POST  mira-premium/api/google/disconnect        revoke at Google and forget the tokens
 */
class GoogleIntegrationController extends Controller
{
    public function __construct(private GoogleClient $google, private GoogleDataService $data) {}

    // --------------------------------------------------------- browser flow

    public function connect(Request $request): RedirectResponse
    {
        $client = auth('client')->user();
        if ($client === null) {
            return redirect()->guest(route('client.login'));
        }

        $projectId = $request->integer('project') ?: null;
        $back = url('/mira-premium') . ($projectId ? '?project=' . $projectId : '');

        try {
            Entitlement::forRequest($request)->require('projects', 'Google Search Console and Analytics');
            if (!$this->google->isConfigured()) {
                throw new MiraApiException('Unavailable', 'Google sign-in is not set up on this server yet.');
            }
            if ($projectId && !MiraProject::where('id', $projectId)->where('client_id', $client->id)->exists()) {
                throw new MiraApiException('NotFound', 'That project was not found.');
            }
        } catch (MiraApiException $e) {
            return redirect($this->withQuery($back, ['google' => 'error', 'google_message' => $e->getMessage()]));
        }

        return redirect()->away($this->google->authorizationUrl($client->id, $projectId, $back));
    }

    public function callback(Request $request): RedirectResponse
    {
        $fallback = url('/mira-premium');

        if ($request->filled('error') || !$request->filled('code') || !$request->filled('state')) {
            // access_denied: the person pressed Cancel on Google's screen.
            $msg = $request->query('error') === 'access_denied' ? 'Google connection cancelled.' : 'Google did not complete the sign-in.';

            return redirect($this->withQuery($this->returnUrl($request->query('state')) ?? $fallback, ['google' => 'error', 'google_message' => $msg]));
        }

        try {
            ['state' => $state, 'tokens' => $tokens] = $this->google->exchange((string) $request->query('state'), (string) $request->query('code'));
            $conn = $this->google->store((int) $state['c'], $tokens);

            // First connection for a project: pre-select the properties that clearly match its domain.
            $project = !empty($state['p']) ? MiraProject::where('id', $state['p'])->where('client_id', $state['c'])->first() : null;
            if ($project !== null && (!$project->gsc_site_url || !$project->ga4_property)) {
                $status = $this->data->status((int) $state['c'], $project);
                $project->fill(array_filter([
                    'gsc_site_url' => $project->gsc_site_url ?: ($status['suggested']['gsc_site_url'] ?? null),
                    'ga4_property' => $project->ga4_property ?: ($status['suggested']['ga4_property'] ?? null),
                ]))->save();
            }

            $missing = [];
            if (!$conn->hasScope(GoogleClient::SCOPE_GSC)) {
                $missing[] = 'Search Console';
            }
            if (!$conn->hasScope(GoogleClient::SCOPE_GA)) {
                $missing[] = 'Analytics';
            }

            return redirect($this->withQuery($this->safeReturn($state['r']) ?? $fallback, array_filter([
                'google' => 'connected',
                'google_message' => $missing ? 'Connected, but ' . implode(' and ', $missing) . ' access was not allowed — reconnect and tick every box to see that data.' : null,
            ])));
        } catch (MiraApiException $e) {
            return redirect($this->withQuery($this->returnUrl($request->query('state')) ?? $fallback, ['google' => 'error', 'google_message' => $e->getMessage()]));
        } catch (\Throwable $e) {
            Log::error('Mira Premium Google callback failed', ['error' => $e->getMessage()]);

            return redirect($this->withQuery($fallback, ['google' => 'error', 'google_message' => 'Google connection failed. Please try again.']));
        }
    }

    // ------------------------------------------------------------------ api

    public function status(Request $request, int $id): JsonResponse
    {
        $who = Entitlement::forRequest($request);

        return response()->json(['google' => $this->data->status($who->client->id, $this->ownedProject($who, $id))]);
    }

    public function select(Request $request, int $id): JsonResponse
    {
        $who     = Entitlement::forRequest($request);
        $project = $this->ownedProject($who, $id);
        $v = validator($request->all(), [
            'gsc_site_url' => 'sometimes|nullable|string|max:255',
            'ga4_property' => ['sometimes', 'nullable', 'string', 'max:40', 'regex:/^properties\/\d+$/'],
        ]);
        if ($v->fails()) {
            throw new MiraApiException('InvalidInput', $v->errors()->first(), ['field' => array_key_first($v->errors()->toArray())]);
        }
        $in = $v->validated();

        $conn = $this->data->connection($who->client->id);
        if ($conn === null || $conn->status !== 'active') {
            throw new MiraApiException('NotFound', 'Connect Google first.');
        }
        // Only properties this Google account can actually read.
        if (!empty($in['gsc_site_url']) && !in_array($in['gsc_site_url'], array_column($this->google->sites($conn), 'site_url'), true)) {
            throw new MiraApiException('InvalidInput', 'That Search Console property is not available to the connected Google account.', ['field' => 'gsc_site_url']);
        }
        if (!empty($in['ga4_property']) && !in_array($in['ga4_property'], array_column($this->google->properties($conn), 'property'), true)) {
            throw new MiraApiException('InvalidInput', 'That Analytics property is not available to the connected Google account.', ['field' => 'ga4_property']);
        }

        $project->update($in);

        return response()->json(['google' => $this->data->status($who->client->id, $project->fresh())]);
    }

    public function disconnect(Request $request): JsonResponse
    {
        $who = Entitlement::forRequest($request);
        if ($who->client === null) {
            throw new MiraApiException('NotEntitled', 'Sign in to manage your Google connection.');
        }
        if ($conn = $this->data->connection($who->client->id)) {
            $this->google->disconnect($conn);
        }

        return response()->json(['google' => $this->data->status($who->client->id, null)]);
    }

    // -------------------------------------------------------------- helpers

    private function ownedProject(Entitlement $who, int $id): MiraProject
    {
        if ($who->client === null) {
            throw new MiraApiException('NotEntitled', 'Sign in to use saved projects.', ['upgrade_url' => config('mira_premium.upgrade_url')]);
        }
        $project = MiraProject::where('id', $id)->where('client_id', $who->client->id)->first();
        if ($project === null) {
            throw new MiraApiException('NotFound', 'That project was not found.');
        }
        $who->require('projects', 'Saved projects');

        return $project;
    }

    /** The return URL inside a state, read only to send an error back to the right page. */
    private function returnUrl(?string $state): ?string
    {
        $parts = explode('.', (string) $state);
        if (count($parts) !== 2 || !hash_equals(hash_hmac('sha256', $parts[0], 'mira-premium-google-state|' . config('app.key')), $parts[1])) {
            return null;
        }
        $data = json_decode((string) base64_decode(strtr($parts[0], '-_', '+/')), true);

        return $this->safeReturn(is_array($data) ? ($data['r'] ?? null) : null);
    }

    /** Only back to /mira-premium on this app's own hosts (the state is signed; this is defence in depth). */
    private function safeReturn(?string $url): ?string
    {
        $parts = parse_url((string) $url);
        if (!$parts || !isset($parts['host']) || ($parts['path'] ?? '') !== '/mira-premium') {
            return null;
        }
        $hosts = array_filter([parse_url((string) config('app.url'), PHP_URL_HOST), parse_url((string) config('services.google.redirect'), PHP_URL_HOST), request()->getHost()]);
        if (!app()->environment('production')) {
            $hosts = array_merge($hosts, ['localhost', '127.0.0.1']);
        }

        return in_array(strtolower($parts['host']), array_map('strtolower', $hosts), true) ? $url : null;
    }

    private function withQuery(string $url, array $query): string
    {
        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
    }
}
