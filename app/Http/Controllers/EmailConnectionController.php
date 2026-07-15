<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
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
            'auth_method'    => 'oauth',
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

    /* ═══════════════════════════════════════════════════════
       PRIVATE HELPERS
       ═══════════════════════════════════════════════════════ */

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
            case 'mailerlite':
                return $this->validateMailerLiteApiKey($apiKey);
            case 'moosend':
                return $this->validateMoosendApiKey($apiKey);
            case 'constantcontact':
                return $this->validateConstantContactApiKey($apiKey);
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

    private function validateMailerLiteApiKey(string $apiKey): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])->timeout(10)->get('https://api.mailerlite.com/api/v2/stats');
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

    private function validateConstantContactApiKey(string $apiKey): bool
    {
        try {
            $response = Http::withToken($apiKey)
                ->timeout(10)->get('https://api.cc.email/v3/account_info');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function syncMailChimp($connection): void
    {
        $apiKey = decrypt($connection->api_key);
        $settings = $connection->settings ?? [];
        $dc = $settings['datacenter'] ?? 'us1';

        if (!str_contains($apiKey, '-')) {
            return;
        }

        list($key, $dc) = explode('-', $apiKey, 2);
        $baseUrl = "https://{$dc}.api.mailchimp.com/3.0";

        // Fetch campaigns
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

        // Fetch lists/audiences
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

        // Fetch campaigns
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

        // Fetch contacts/lists
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

    private function syncMailerLite($connection): void
    {
        $apiKey = decrypt($connection->api_key);

        $campaigns = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
        ])->get('https://api.mailerlite.com/api/v2/campaigns');

        if ($campaigns->successful()) {
            foreach ($campaigns->json() ?? [] as $campaign) {
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
                        'send_time'     => $campaign['date'] ?? null,
                        'emails_sent'   => $campaign['total_sent'] ?? 0,
                        'platform'      => 'mailerlite',
                        'raw_data'      => json_encode($campaign),
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
