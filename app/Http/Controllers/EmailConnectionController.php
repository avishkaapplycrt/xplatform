<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

class EmailConnectionController extends Controller
{
    /**
     * Display the email connections page.
     */
    public function index()
    {
        $client = Auth::guard('client')->user();
        $connections = $this->getConnections($client->id);

        $providers = [
            [
                'id'          => 'mailchimp',
                'name'        => 'MailChimp',
                'description' => 'Connect your MailChimp account to sync email campaigns, audiences, and engagement data.',
                'color'       => '#ffe01b',
                'text_color'  => '#241c15',
                'connected'   => $connections->where('platform', 'mailchimp')->isNotEmpty(),
                'connection'  => $connections->where('platform', 'mailchimp')->first(),
            ],
            [
                'id'          => 'brevo',
                'name'        => 'Brevo',
                'description' => 'Integrate with Brevo (formerly Sendinblue) for transactional and marketing email tracking.',
                'color'       => '#0b996e',
                'text_color'  => '#ffffff',
                'connected'   => $connections->where('platform', 'brevo')->isNotEmpty(),
                'connection'  => $connections->where('platform', 'brevo')->first(),
            ],
            [
                'id'          => 'constantcontact',
                'name'        => 'Constant Contact',
                'description' => 'Sync your Constant Contact lists, campaigns, and email performance metrics.',
                'color'       => '#1856ed',
                'text_color'  => '#ffffff',
                'connected'   => $connections->where('platform', 'constantcontact')->isNotEmpty(),
                'connection'  => $connections->where('platform', 'constantcontact')->first(),
            ],
            [
                'id'          => 'mailerlite',
                'name'        => 'MailerLite',
                'description' => 'Connect MailerLite to track subscriber activity, automations, and campaign stats.',
                'color'       => '#00aaff',
                'text_color'  => '#ffffff',
                'connected'   => $connections->where('platform', 'mailerlite')->isNotEmpty(),
                'connection'  => $connections->where('platform', 'mailerlite')->first(),
            ],
            [
                'id'          => 'moosend',
                'name'        => 'Moosend',
                'description' => 'Integrate Moosend for email marketing analytics and subscriber behavior tracking.',
                'color'       => '#00d4aa',
                'text_color'  => '#ffffff',
                'connected'   => $connections->where('platform', 'moosend')->isNotEmpty(),
                'connection'  => $connections->where('platform', 'moosend')->first(),
            ],
        ];

        return view('client.email_connections', compact('providers', 'connections'));
    }

    /**
     * Display the MailChimp connection setup page.
     */
    public function mailchimp()
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\EmailConnection::where('client_id', $client->id)
            ->where('platform', 'mailchimp')->first();

        return view('client.email_mailchimp_connection', [
            'provider'       => 'mailchimp',
            'provider_name'  => 'MailChimp',
            'brand_color'    => '#ffe01b',
            'text_color'     => '#241c15',
            'connection'     => $connection,
            'api_docs_url'   => 'https://mailchimp.com/developer/marketing/api/root/',
            'auth_method'    => 'api_key',
            'scopes'         => ['audiences', 'campaigns', 'reports', 'automations'],
        ]);
    }

    /**
     * Display the Brevo connection setup page.
     */
    public function brevo()
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\EmailConnection::where('client_id', $client->id)
            ->where('platform', 'brevo')->first();

        return view('client.email_brevo_connection', [
            'provider'       => 'brevo',
            'provider_name'  => 'Brevo',
            'brand_color'    => '#0b996e',
            'text_color'     => '#ffffff',
            'connection'     => $connection,
            'api_docs_url'   => 'https://developers.brevo.com/',
            'auth_method'    => 'api_key',
            'scopes'         => ['smtp', 'contacts', 'campaigns', 'transactional'],
        ]);
    }

    /**
     * Display the Constant Contact connection setup page.
     */
    public function constantcontact()
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\EmailConnection::where('client_id', $client->id)
            ->where('platform', 'constantcontact')->first();

        return view('client.email_constantcontact_connection', [
            'provider'       => 'constantcontact',
            'provider_name'  => 'Constant Contact',
            'brand_color'    => '#1856ed',
            'text_color'     => '#ffffff',
            'connection'     => $connection,
            'api_docs_url'   => 'https://developer.constantcontact.com/',
            'auth_method'    => 'api_key',
            'scopes'         => ['contact_data', 'campaign_data', 'account_info'],
        ]);
    }

    /**
     * Display the MailerLite connection setup page.
     */
    public function mailerlite()
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\EmailConnection::where('client_id', $client->id)
            ->where('platform', 'mailerlite')->first();

        return view('client.email_mailerlite_connection', [
            'provider'       => 'mailerlite',
            'provider_name'  => 'MailerLite',
            'brand_color'    => '#00aaff',
            'text_color'     => '#ffffff',
            'connection'     => $connection,
            'api_docs_url'   => 'https://developers.mailerlite.com/',
            'auth_method'    => 'api_key',
            'scopes'         => ['subscribers', 'campaigns', 'groups', 'automations'],
        ]);
    }

    /**
     * Display the Moosend connection setup page.
     */
    public function moosend()
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\EmailConnection::where('client_id', $client->id)
            ->where('platform', 'moosend')->first();

        return view('client.email_moosend_connection', [
            'provider'       => 'moosend',
            'provider_name'  => 'Moosend',
            'brand_color'    => '#00d4aa',
            'text_color'     => '#ffffff',
            'connection'     => $connection,
            'api_docs_url'   => 'https://moosend.com/developers/',
            'auth_method'    => 'api_key',
            'scopes'         => ['mailing_lists', 'campaigns', 'subscribers', 'reports'],
        ]);
    }

    /**
     * Store a new email connection (API Key method).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'platform'    => 'required|string|in:mailchimp,brevo,constantcontact,mailerlite,moosend',
            'api_key'     => 'required|string|max:500',
            'account_name'=> 'nullable|string|max:255',
            'settings'    => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $client = Auth::guard('client')->user();

        $existing = \App\Models\EmailConnection::where('client_id', $client->id)
            ->where('platform', $request->platform)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This provider is already connected. Please disconnect first.',
            ], 409);
        }

        // Validate API key by testing connection
        $isValid = $this->validateApiKey($request->platform, $request->api_key);
        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key. Please check and try again.',
            ], 401);
        }

        $this->ensureEmailLogsTable($request->platform);

        $connection = \App\Models\EmailConnection::create([
            'client_id'     => $client->id,
            'tenant_id'     => $client->tenant_id ?? null,
            'platform'      => $request->platform,
            'api_key'       => encrypt($request->api_key),
            'account_name'  => $request->account_name ?? $request->platform,
            'settings'      => $request->settings ? json_decode($request->settings, true) : [],
            'status'        => 'active',
            'connected_at'  => now(),
            'last_sync_at'  => now(),
        ]);

        $this->logConnectionEvent($client->id, $connection->id, 'connected', $request->platform);

        return response()->json([
            'success'     => true,
            'message'     => 'Email provider connected successfully.',
            'connection'  => $connection,
        ], 201);
    }

    /**
     * Disconnect an email connection.
     */
    public function destroy($id)
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\EmailConnection::where('client_id', $client->id)
            ->where('id', $id)->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $this->logConnectionEvent($client->id, $connection->id, 'disconnected', $connection->platform);
        $connection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email provider disconnected successfully.',
        ]);
    }

    /**
     * Verify connection health.
     */
    public function verify($id)
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\EmailConnection::where('client_id', $client->id)
            ->where('id', $id)->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $isHealthy = $this->validateApiKey(
            $connection->platform,
            decrypt($connection->api_key)
        );

        $connection->update([
            'last_sync_at' => now(),
            'status'       => $isHealthy ? 'active' : 'error',
        ]);

        return response()->json([
            'success' => true,
            'healthy' => $isHealthy,
            'message' => $isHealthy ? 'Connection is healthy.' : 'Connection failed.',
        ]);
    }

    /**
     * Sync data from email provider.
     */
    public function sync($id)
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\EmailConnection::where('client_id', $client->id)
            ->where('id', $id)->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        // Trigger sync based on provider
        switch ($connection->platform) {
            case 'mailchimp':
                $this->syncMailChimp($connection);
                break;
            case 'brevo':
                $this->syncBrevo($connection);
                break;
            case 'constantcontact':
                $this->syncConstantContact($connection);
                break;
            case 'mailerlite':
                $this->syncMailerLite($connection);
                break;
            case 'moosend':
                $this->syncMoosend($connection);
                break;
        }

        $connection->update(['last_sync_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Sync completed successfully.',
        ]);
    }

    /**
     * Live Email Engagement grid (Delivered / Opens / Clicks / Conversions / Unsubscribes)
     * for the "Email Engagement" card on the Data Collection page, sourced from the
     * client's connected Brevo account.
     */
    public function brevoEngagementStats(Request $request)
    {
        $client = Auth::guard('client')->user();
        $connection = \App\Models\EmailConnection::where('client_id', $client->id)
            ->where('platform', 'brevo')
            ->where('status', 'active')
            ->first();

        if (!$connection) {
            return response()->json([
                'success'   => false,
                'connected' => false,
                'message'   => 'Brevo is not connected yet.',
            ], 200);
        }

        $cacheKey = "brevo_engagement_stats_{$connection->id}";

        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }

        try {
            $stats = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($connection) {
                return $this->fetchBrevoEngagementStats($connection);
            });
        } catch (\App\Exceptions\BrevoRateLimitedException $e) {
            return response()->json([
                'success'   => false,
                'connected' => true,
                'message'   => 'Brevo is temporarily rate-limiting requests. Please try again in a few minutes.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success'   => false,
                'connected' => true,
                'message'   => 'Could not reach Brevo right now. Please try again shortly.',
            ], 200);
        }

        if ($stats === null) {
            return response()->json([
                'success'   => false,
                'connected' => true,
                'message'   => 'Brevo rejected the stored API key. Please reconnect Brevo.',
            ], 200);
        }

        return response()->json([
            'success'   => true,
            'connected' => true,
            'data'      => $stats,
        ]);
    }

    /**
     * Sum globalStats across every "sent" Brevo campaign to build one aggregate
     * engagement snapshot.
     */
    private function fetchBrevoEngagementStats($connection): ?array
    {
        $apiKey = decrypt($connection->api_key);

        $totals = [
            'sent' => 0, 'delivered' => 0, 'uniqueViews' => 0,
            'uniqueClicks' => 0, 'unsubscriptions' => 0,
        ];
        $campaignCount = 0;
        $offset = 0;
        $limit  = 100;
        $total  = null;

        do {
            $response = Http::withHeaders(['api-key' => $apiKey])
                ->timeout(20)
                ->get('https://api.brevo.com/v3/emailCampaigns', [
                    'limit'      => $limit,
                    'offset'     => $offset,
                    'status'     => 'sent',
                    'statistics' => 'globalStats',
                ]);

            if ($response->status() === 429) {
                throw new \App\Exceptions\BrevoRateLimitedException($this->brevoRateLimitRetryAfter($response));
            }

            if (!$response->successful()) {
                return null;
            }

            $data  = $response->json();
            $total = $data['count'] ?? 0;
            $batch = $data['campaigns'] ?? [];

            foreach ($batch as $campaign) {
                $g = $campaign['statistics']['globalStats'] ?? [];
                foreach ($totals as $key => $value) {
                    $totals[$key] += $g[$key] ?? 0;
                }
                $campaignCount++;
            }

            $offset += $limit;
        } while ($total !== null && $offset < $total);

        $delivered      = $totals['delivered'];
        $baseSent       = max(1, $totals['sent']);
        $baseDelivered  = max(1, $delivered);

        return [
            'delivered'         => $delivered,
            'opens'             => $totals['uniqueViews'],
            'clicks'            => $totals['uniqueClicks'],
            'conversions'       => 0,
            'unsubscribes'      => $totals['unsubscriptions'],
            'delivery_rate'     => round($delivered / $baseSent * 100, 2),
            'open_rate'         => round($totals['uniqueViews'] / $baseDelivered * 100, 2),
            'click_rate'        => round($totals['uniqueClicks'] / $baseDelivered * 100, 2),
            'conversion_rate'   => 0,
            'unsubscribe_rate'  => round($totals['unsubscriptions'] / $baseDelivered * 100, 2),
            'campaign_count'    => $campaignCount,
            'account_name'      => $connection->account_name,
            'synced_at'         => now()->toIso8601String(),
        ];
    }

    private function brevoRateLimitRetryAfter($response): int
    {
        $reset = $response->header('x-sib-ratelimit-reset');
        return $reset !== null && $reset !== '' ? max(30, (int) $reset) : 300;
    }

    /**
     * List of email addresses actually delivered, for the "View" drill-down
     * on the Delivered / Opens / Clicks / Unsubscribes rows of the Email
     * Engagement card.
     *
     * Brevo has no single live endpoint for "every delivered/opened/clicked/
     * unsubscribed recipient across all campaigns" — the only way to get real
     * per-recipient data is an async per-campaign export (see
     * ExportBrevoCampaignDeliveries), and one such export already carries all
     * four signals per recipient. So the first call for ANY of these metrics
     * kicks off one shared background export run across every sent campaign
     * and reports "building" progress; the frontend polls this same endpoint
     * until the run completes, at which point every metric reads instantly
     * from local storage, filtered accordingly.
     *
     * There is no "conversions" variant: Brevo campaigns don't track
     * per-recipient conversions at all (the aggregate conversions stat above
     * is always 0 for the same reason), so there is nothing real to list.
     */
    public function brevoEngagementContacts(Request $request, string $metric)
    {
        if (!in_array($metric, ['delivered', 'opens', 'clicks', 'unsubscribes'], true)) {
            return response()->json([
                'success'   => false,
                'connected' => true,
                'message'   => 'Unknown metric.',
            ], 200);
        }

        $client = Auth::guard('client')->user();
        $connection = \App\Models\EmailConnection::where('client_id', $client->id)
            ->where('platform', 'brevo')
            ->where('status', 'active')
            ->first();

        if (!$connection) {
            return response()->json([
                'success'   => false,
                'connected' => false,
                'message'   => 'Brevo is not connected yet.',
            ], 200);
        }

        $sync = $this->currentOrNewBrevoSync($connection, $client->id, $request->boolean('refresh'));

        if ($sync === null) {
            return response()->json([
                'success'   => false,
                'connected' => true,
                'message'   => 'Could not reach Brevo to list campaigns. Please try again shortly.',
            ], 200);
        }

        if ($sync['status'] !== 'completed') {
            return response()->json([
                'success'   => true,
                'connected' => true,
                'building'  => true,
                'total'     => $sync['total'],
                'done'      => $sync['done'],
            ]);
        }

        $query = \App\Models\BrevoDeliveredRecipient::where('client_id', $client->id);

        match ($metric) {
            'opens'        => $query->whereNotNull('opened_at'),
            'clicks'       => $query->where('clicked', true),
            'unsubscribes' => $query->whereNotNull('unsubscribed_at'),
            default        => null, // 'delivered' — every stored row is already a delivered recipient
        };

        // Not deduped by email alone — the same recipient can show up under more
        // than one campaign for this metric, and the campaign_id is what lets the
        // UI tell those apart instead of silently collapsing them into one row.
        $rows = $query->select('email', 'campaign_id')
            ->orderByRaw('CAST(campaign_id AS UNSIGNED) asc')
            ->orderBy('email')
            ->get()
            ->map(fn ($row) => ['email' => $row->email, 'campaign_id' => $row->campaign_id]);

        return response()->json([
            'success'   => true,
            'connected' => true,
            'building'  => false,
            'data'      => $rows,
            'built_at'  => optional($sync['completed_at'])->toIso8601String(),
        ]);
    }

    /**
     * Return the client's current sync state, starting a new export batch if
     * needed. There is no dedicated "runs" table — whether a sync has ever
     * completed is derived straight from email_logs_brevo (keyed by
     * client_id, stable across Brevo disconnect/reconnect), and while a batch
     * is actively running its progress (total/done) lives in the cache under
     * per-client keys. Starting a batch is wrapped in a per-client lock so two
     * near-simultaneous requests (e.g. a page load racing a manual retry)
     * can't both decide nothing is running and each dispatch a duplicate
     * batch of export jobs.
     */
    private function currentOrNewBrevoSync($connection, int $clientId, bool $refresh): ?array
    {
        return Cache::lock("brevo_export_run_start_{$clientId}", 30)->block(10, function () use ($connection, $clientId, $refresh) {
            $status = Cache::get("brevo_sync_status_{$clientId}");

            if ($status === 'running') {
                return [
                    'status' => 'running',
                    'total'  => (int) Cache::get("brevo_sync_total_{$clientId}", 0),
                    'done'   => (int) Cache::get("brevo_sync_done_{$clientId}", 0),
                ];
            }

            $lastSyncedAt = \App\Models\BrevoDeliveredRecipient::where('client_id', $clientId)->max('updated_at');

            if ($lastSyncedAt !== null && $status !== 'failed') {
                $completedAt = \Carbon\Carbon::parse($lastSyncedAt);

                // A plain (non-refresh) request always reuses whatever's
                // already local, however old — only an explicit refresh
                // (the "Sync Data" button / in-modal Refresh) re-syncs from
                // Brevo, so a normal page load never triggers the export.
                if (!$refresh) {
                    return ['status' => 'completed', 'completed_at' => $completedAt];
                }
            }

            return $this->startBrevoDeliveredSync($connection, $clientId);
        });
    }

    /**
     * Fetch every sent campaign id straight from Brevo (not the local cache,
     * which this app's ESP sync doesn't actually persist) and dispatch one
     * export job per campaign, staggered so they don't all hit Brevo at once.
     * Progress is tracked in the cache (not a DB table) since it only needs
     * to survive for the lifetime of the batch itself.
     */
    private function startBrevoDeliveredSync($connection, int $clientId): ?array
    {
        $apiKey = decrypt($connection->api_key);

        $campaignIds = [];
        $offset = 0;
        $limit  = 100;
        $total  = null;

        do {
            $response = Http::withHeaders(['api-key' => $apiKey])
                ->timeout(20)
                ->get('https://api.brevo.com/v3/emailCampaigns', [
                    'limit'  => $limit,
                    'offset' => $offset,
                    'status' => 'sent',
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data  = $response->json();
            $total = $data['count'] ?? 0;

            foreach ($data['campaigns'] ?? [] as $campaign) {
                if (isset($campaign['id'])) {
                    $campaignIds[] = (string) $campaign['id'];
                }
            }

            $offset += $limit;
        } while ($total !== null && $offset < $total);

        if (empty($campaignIds)) {
            Cache::forget("brevo_sync_status_{$clientId}");
            return ['status' => 'completed', 'completed_at' => now()];
        }

        Cache::forever("brevo_sync_status_{$clientId}", 'running');
        Cache::forever("brevo_sync_total_{$clientId}", count($campaignIds));
        Cache::forever("brevo_sync_done_{$clientId}", 0);

        foreach ($campaignIds as $i => $campaignId) {
            \App\Jobs\ExportBrevoCampaignDeliveries::dispatch($clientId, $campaignId)
                ->delay(now()->addSeconds($i * 6));
        }

        return ['status' => 'running', 'total' => count($campaignIds), 'done' => 0];
    }

    /* ═══════════════════════════════════════════════════════
       PRIVATE HELPERS
       ═══════════════════════════════════════════════════════ */

    /**
     * Create email_logs_{platform} the first time any client connects that
     * provider — e.g. email_logs_brevo, email_logs_mailchimp. $platform is
     * always one of the values already whitelisted by store()'s validator,
     * so it's safe to use directly in the table name. Idempotent: once a
     * provider's table exists it's shared by every client that connects to
     * it (rows are scoped internally by client_id), so this never recreates
     * or alters an existing table.
     */
    private function ensureEmailLogsTable(string $platform): void
    {
        $table = "email_logs_{$platform}";

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('client_id');
            $t->string('campaign_id', 50);
            $t->string('email');
            $t->timestamp('delivered_at')->nullable();
            $t->timestamp('opened_at')->nullable();
            $t->boolean('clicked')->default(false);
            $t->timestamp('unsubscribed_at')->nullable();
            $t->timestamps();

            $t->unique(['client_id', 'campaign_id', 'email']);
            $t->index(['client_id', 'email']);
        });
    }

    private function getConnections(int $clientId)
    {
        return \App\Models\EmailConnection::where('client_id', $clientId)
            ->orderBy('connected_at', 'desc')
            ->get();
    }

    private function validateApiKey(string $platform, string $apiKey): bool
    {
        if (empty($apiKey) || strlen($apiKey) < 8) {
            return false;
        }

        switch ($platform) {
            case 'mailchimp':
                return $this->validateMailChimpApiKey($apiKey);
            case 'brevo':
                return $this->validateBrevoApiKey($apiKey);
            case 'constantcontact':
                return $this->validateConstantContactApiKey($apiKey);
            case 'mailerlite':
                return $this->validateMailerLiteApiKey($apiKey);
            case 'moosend':
                return $this->validateMoosendApiKey($apiKey);
            default:
                return true;
        }
    }

    private function validateMailChimpApiKey(string $apiKey): bool
    {
        if (!str_contains($apiKey, '-')) {
            return false;
        }

        list($key, $dc) = explode('-', $apiKey, 2);

        try {
            $response = Http::withBasicAuth('anystring', $apiKey)
                ->timeout(10)
                ->get("https://{$dc}.api.mailchimp.com/3.0/");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function validateBrevoApiKey(string $apiKey): bool
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
            ])->timeout(10)->get('https://api.brevo.com/v3/account');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function validateConstantContactApiKey(string $apiKey): bool
    {
        try {
            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->get('https://api.cc.email/v3/account_info');
            if ($response->successful()) {
                return true;
            }
        } catch (\Exception $e) {
            // Fall through
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
            ])->timeout(10)->get('https://api.cc.email/v3/account_info');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function validateMailerLiteApiKey(string $apiKey): bool
    {
        try {
            $response = Http::withHeaders([
                'X-MailerLite-ApiKey' => $apiKey,
            ])->timeout(10)->get('https://api.mailerlite.com/api/v2/stats');
            if ($response->successful()) {
                return true;
            }
        } catch (\Exception $e) {
            // Fall through
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])->timeout(10)->get('https://api.mailerlite.com/api/v2/stats');
            if ($response->successful()) {
                return true;
            }
        } catch (\Exception $e) {
            // Fall through
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])->timeout(10)->get('https://connect.mailerlite.com/api/subscribers');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function validateMoosendApiKey(string $apiKey): bool
    {
        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $apiKey,
            ])->timeout(10)->get('https://api.moosend.com/v3/lists.json');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function syncMailChimp($connection): void
    {
        $apiKey = decrypt($connection->api_key);

        if (!str_contains($apiKey, '-')) {
            return;
        }

        list($key, $dc) = explode('-', $apiKey, 2);
        $baseUrl = "https://{$dc}.api.mailchimp.com/3.0";

        $campaigns = Http::withBasicAuth('anystring', $apiKey)
            ->get("{$baseUrl}/campaigns", ['count' => 100, 'status' => 'sent']);

        if ($campaigns->successful()) {
            foreach ($campaigns->json()['campaigns'] ?? [] as $campaign) {
                \App\Models\EmailCampaign::updateOrCreate(
                    [
                        'connection_id' => $connection->id,
                        'external_id'   => $campaign['id'],
                    ],
                    [
                        'client_id'     => $connection->client_id,
                        'tenant_id'     => $connection->tenant_id,
                        'name'          => $campaign['settings']['title'] ?? $campaign['settings']['subject_line'] ?? 'Untitled',
                        'subject'       => $campaign['settings']['subject_line'] ?? '',
                        'status'        => $campaign['status'],
                        'send_time'     => $campaign['send_time'] ?? null,
                        'emails_sent'   => $campaign['emails_sent'] ?? 0,
                        'platform'      => 'mailchimp',
                        'raw_data'      => json_encode($campaign),
                    ]
                );
            }
        }

        $lists = Http::withBasicAuth('anystring', $apiKey)
            ->get("{$baseUrl}/lists", ['count' => 100]);

        if ($lists->successful()) {
            foreach ($lists->json()['lists'] ?? [] as $list) {
                \App\Models\EmailAudience::updateOrCreate(
                    [
                        'connection_id' => $connection->id,
                        'external_id'   => $list['id'],
                    ],
                    [
                        'client_id'     => $connection->client_id,
                        'tenant_id'     => $connection->tenant_id,
                        'name'          => $list['name'],
                        'member_count'  => $list['stats']['member_count'] ?? 0,
                        'open_rate'     => $list['stats']['open_rate'] ?? 0,
                        'click_rate'    => $list['stats']['click_rate'] ?? 0,
                        'platform'      => 'mailchimp',
                    ]
                );
            }
        }
    }

    private function syncBrevo($connection): void
    {
        $apiKey = decrypt($connection->api_key);

        $campaigns = Http::withHeaders(['api-key' => $apiKey])
            ->get('https://api.brevo.com/v3/emailCampaigns', ['limit' => 100, 'status' => 'sent']);

        if ($campaigns->successful()) {
            foreach ($campaigns->json()['campaigns'] ?? [] as $campaign) {
                \App\Models\EmailCampaign::updateOrCreate(
                    [
                        'connection_id' => $connection->id,
                        'external_id'   => (string) $campaign['id'],
                    ],
                    [
                        'client_id'     => $connection->client_id,
                        'tenant_id'     => $connection->tenant_id,
                        'name'          => $campaign['name'] ?? 'Untitled',
                        'subject'       => $campaign['subject'] ?? '',
                        'status'        => $campaign['status'] ?? 'sent',
                        'send_time'     => $campaign['sentDate'] ?? null,
                        'emails_sent'   => $campaign['sent'] ?? 0,
                        'platform'      => 'brevo',
                        'raw_data'      => json_encode($campaign),
                    ]
                );
            }
        }

        $lists = Http::withHeaders(['api-key' => $apiKey])
            ->get('https://api.brevo.com/v3/contacts/lists', ['limit' => 100]);

        if ($lists->successful()) {
            foreach ($lists->json()['lists'] ?? [] as $list) {
                \App\Models\EmailAudience::updateOrCreate(
                    [
                        'connection_id' => $connection->id,
                        'external_id'   => (string) $list['id'],
                    ],
                    [
                        'client_id'     => $connection->client_id,
                        'tenant_id'     => $connection->tenant_id,
                        'name'          => $list['name'],
                        'member_count'  => $list['totalSubscribers'] ?? $list['totalBlacklisted'] ?? 0,
                        'open_rate'     => 0,
                        'click_rate'    => 0,
                        'platform'      => 'brevo',
                    ]
                );
            }
        }
    }

    private function syncConstantContact($connection): void
    {
        $apiKey = decrypt($connection->api_key);

        $headers = ['Authorization' => 'Bearer ' . $apiKey];

        $campaigns = Http::withHeaders($headers)
            ->get('https://api.cc.email/v3/email_campaigns', ['limit' => 100, 'status' => 'SENT']);

        if (!$campaigns->successful()) {
            $headers = ['x-api-key' => $apiKey];
            $campaigns = Http::withHeaders($headers)
                ->get('https://api.cc.email/v3/email_campaigns', ['limit' => 100, 'status' => 'SENT']);
        }

        if ($campaigns->successful()) {
            foreach ($campaigns->json()['campaigns'] ?? [] as $campaign) {
                \App\Models\EmailCampaign::updateOrCreate(
                    [
                        'connection_id' => $connection->id,
                        'external_id'   => $campaign['campaign_id'],
                    ],
                    [
                        'client_id'     => $connection->client_id,
                        'tenant_id'     => $connection->tenant_id,
                        'name'          => $campaign['name'] ?? 'Untitled',
                        'subject'       => $campaign['subject'] ?? '',
                        'status'        => $campaign['status'] ?? 'SENT',
                        'send_time'     => $campaign['last_sent_at'] ?? null,
                        'emails_sent'   => $campaign['sent_count'] ?? 0,
                        'platform'      => 'constantcontact',
                        'raw_data'      => json_encode($campaign),
                    ]
                );
            }
        }

        $lists = Http::withHeaders($headers)
            ->get('https://api.cc.email/v3/contact_lists', ['limit' => 100]);

        if ($lists->successful()) {
            foreach ($lists->json()['lists'] ?? [] as $list) {
                \App\Models\EmailAudience::updateOrCreate(
                    [
                        'connection_id' => $connection->id,
                        'external_id'   => $list['list_id'],
                    ],
                    [
                        'client_id'     => $connection->client_id,
                        'tenant_id'     => $connection->tenant_id,
                        'name'          => $list['name'],
                        'member_count'  => $list['membership_count'] ?? 0,
                        'open_rate'     => 0,
                        'click_rate'    => 0,
                        'platform'      => 'constantcontact',
                    ]
                );
            }
        }
    }

    private function syncMailerLite($connection): void
    {
        $apiKey = decrypt($connection->api_key);

        $campaigns = Http::withHeaders([
            'X-MailerLite-ApiKey' => $apiKey,
        ])->get('https://api.mailerlite.com/api/v2/campaigns');

        if (!$campaigns->successful()) {
            $campaigns = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])->get('https://api.mailerlite.com/api/v2/campaigns');
        }

        if ($campaigns->successful()) {
            foreach ($campaigns->json() ?? [] as $campaign) {
                \App\Models\EmailCampaign::updateOrCreate(
                    [
                        'connection_id' => $connection->id,
                        'external_id'   => (string) ($campaign['id'] ?? ''),
                    ],
                    [
                        'client_id'     => $connection->client_id,
                        'tenant_id'     => $connection->tenant_id,
                        'name'          => $campaign['name'] ?? 'Untitled',
                        'subject'       => $campaign['subject'] ?? '',
                        'status'        => $campaign['status'] ?? 'sent',
                        'send_time'     => $campaign['date'] ?? null,
                        'emails_sent'   => $campaign['total_sent'] ?? 0,
                        'platform'      => 'mailerlite',
                        'raw_data'      => json_encode($campaign),
                    ]
                );
            }
        }

        $groups = Http::withHeaders([
            'X-MailerLite-ApiKey' => $apiKey,
        ])->get('https://api.mailerlite.com/api/v2/groups');

        if (!$groups->successful()) {
            $groups = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])->get('https://api.mailerlite.com/api/v2/groups');
        }

        if ($groups->successful()) {
            foreach ($groups->json() ?? [] as $group) {
                \App\Models\EmailAudience::updateOrCreate(
                    [
                        'connection_id' => $connection->id,
                        'external_id'   => (string) ($group['id'] ?? ''),
                    ],
                    [
                        'client_id'     => $connection->client_id,
                        'tenant_id'     => $connection->tenant_id,
                        'name'          => $group['name'] ?? 'Untitled',
                        'member_count'  => $group['total'] ?? 0,
                        'open_rate'     => 0,
                        'click_rate'    => 0,
                        'platform'      => 'mailerlite',
                    ]
                );
            }
        }
    }

    private function syncMoosend($connection): void
    {
        $apiKey = decrypt($connection->api_key);

        $campaigns = Http::withHeaders([
            'X-Api-Key' => $apiKey,
        ])->get('https://api.moosend.com/v3/campaigns.json');

        if ($campaigns->successful()) {
            foreach ($campaigns->json() ?? [] as $campaign) {
                \App\Models\EmailCampaign::updateOrCreate(
                    [
                        'connection_id' => $connection->id,
                        'external_id'   => (string) ($campaign['ID'] ?? $campaign['id'] ?? ''),
                    ],
                    [
                        'client_id'     => $connection->client_id,
                        'tenant_id'     => $connection->tenant_id,
                        'name'          => $campaign['Name'] ?? $campaign['name'] ?? 'Untitled',
                        'subject'       => $campaign['Subject'] ?? $campaign['subject'] ?? '',
                        'status'        => $campaign['Status'] ?? $campaign['status'] ?? 'sent',
                        'send_time'     => $campaign['DateCreated'] ?? $campaign['dateCreated'] ?? null,
                        'emails_sent'   => $campaign['TotalSent'] ?? $campaign['totalSent'] ?? 0,
                        'platform'      => 'moosend',
                        'raw_data'      => json_encode($campaign),
                    ]
                );
            }
        }

        // Fetch mailing lists
        $lists = Http::withHeaders([
            'X-Api-Key' => $apiKey,
        ])->get('https://api.moosend.com/v3/lists.json');

        if ($lists->successful()) {
            foreach ($lists->json() ?? [] as $list) {
                \App\Models\EmailAudience::updateOrCreate(
                    [
                        'connection_id' => $connection->id,
                        'external_id'   => (string) ($list['ID'] ?? $list['id'] ?? ''),
                    ],
                    [
                        'client_id'     => $connection->client_id,
                        'tenant_id'     => $connection->tenant_id,
                        'name'          => $list['Name'] ?? $list['name'] ?? 'Untitled',
                        'member_count'  => $list['ActiveMemberCount'] ?? $list['activeMemberCount'] ?? 0,
                        'open_rate'     => 0,
                        'click_rate'    => 0,
                        'platform'      => 'moosend',
                    ]
                );
            }
        }
    }

    private function logConnectionEvent(int $clientId, int $connectionId, string $event, string $platform): void
    {
        \App\Models\EmailConnectionLog::create([
            'client_id'     => $clientId,
            'connection_id' => $connectionId,
            'event'         => $event,
            'platform'      => $platform,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
            'created_at'    => now(),
        ]);
    }
}
