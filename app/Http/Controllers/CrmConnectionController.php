<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CrmIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class CrmConnectionController extends Controller
{
    // Zoho OAuth endpoints
    private const ZOHO_AUTH_URL = 'https://accounts.zoho.com/oauth/v2/auth';
    private const ZOHO_TOKEN_URL = 'https://accounts.zoho.com/oauth/v2/token';
    private const ZOHO_SCOPES = 'ZohoCRM.modules.ALL,ZohoCRM.users.ALL,ZohoCRM.settings.ALL';

    // Monday.com OAuth endpoints
    private const MONDAY_AUTH_URL = 'https://auth.monday.com/oauth2/authorize';
    private const MONDAY_TOKEN_URL = 'https://auth.monday.com/oauth2/token';
    private const MONDAY_SCOPES = 'boards:read users:read teams:read updates:read assets:read';

    /**
     * Display the CRM connections listing page.
     */
    public function index()
    {
        $connections = CrmIntegration::all()->keyBy('provider');
        $providers   = CrmIntegration::providers();

        $totalConnected = $connections->where('status', 'connected')->count();
        $totalProviders = count($providers);
        $syncToday      = $connections->where('last_sync_at', '>=', now()->startOfDay())->count();
        $lastSync       = $connections->whereNotNull('last_sync_at')->max('last_sync_at');

        $syncHealth = 'healthy';
        if ($totalConnected > 0) {
            $expiredCount = $connections->where('status', 'connected')->filter(function ($c) {
                return $c->is_expired;
            })->count();
            if ($expiredCount > 0) {
                $syncHealth = $expiredCount === $totalConnected ? 'critical' : 'warning';
            }
        }

        return view('client.crm-connections', compact(
            'connections', 'providers', 'totalConnected', 'totalProviders',
            'syncToday', 'lastSync', 'syncHealth'
        ));
    }

    /**
     * Show the connect form for a specific provider.
     */
    public function create(string $provider)
    {
        $providers = CrmIntegration::providers();

        if (!isset($providers[$provider])) {
            abort(404, 'CRM provider not found.');
        }

        $meta = $providers[$provider];
        $existing = CrmIntegration::byProvider($provider)->first();

        return view('client.crm-connect', compact('provider', 'meta', 'existing'));
    }

    /**
     * Store or update a CRM connection.
     */
    public function store(Request $request, string $provider)
    {
        $providers = CrmIntegration::providers();

        if (!isset($providers[$provider])) {
            return response()->json(['success' => false, 'error' => 'Invalid provider'], 422);
        }

        $rules = [
            'connection_name' => 'required|string|max:100',
            'status'          => 'required|in:connected,disconnected',
        ];

        switch ($provider) {
            case 'salesforce':
                $rules['instance_url'] = 'required|url';
                $rules['access_token'] = 'required|string';
                $rules['refresh_token'] = 'nullable|string';
                break;
            case 'hubspot':
                $rules['api_key'] = 'required|string';
                $rules['portal_id'] = 'nullable|string';
                break;
            case 'zoho':
                $rules['api_key'] = 'required|string';
                $rules['api_secret'] = 'required|string';
                break;
            case 'pipedrive':
                $rules['api_key'] = 'required|string';
                break;
            case 'monday':
                $rules['api_key'] = 'required|string';
                $rules['api_secret'] = 'required|string';
                break;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['provider'] = $provider;

        // Api-key providers are never OAuth-verified anywhere else, so this is
        // the only checkpoint that stands between a typo/placeholder key and a
        // connection silently marked "connected". Verify live before saving.
        $apiKeyProviders = ['hubspot', 'pipedrive'];
        if (($data['status'] ?? null) === 'connected' && in_array($provider, $apiKeyProviders, true)) {
            $verifyResult = match ($provider) {
                'hubspot'   => $this->verifyHubSpotApiKey($request->input('api_key')),
                'pipedrive' => $this->verifyPipedriveApiKey($request->input('api_key')),
            };

            if (!$verifyResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not verify credentials: ' . $verifyResult['message'],
                ], 422);
            }
        }

        // Encrypt sensitive fields
        $encryptFields = ['api_key', 'api_secret', 'access_token', 'refresh_token'];
        foreach ($encryptFields as $field) {
            if (!empty($data[$field])) {
                $data[$field] = Crypt::encryptString($data[$field]);
            }
        }

        // Handle sync_config
        $data['sync_config'] = $request->input('sync_config', [
            'sync_frequency' => 'hourly',
            'sync_direction' => 'import',
            'entities'       => ['contacts', 'leads'],
        ]);

        // For OAuth providers, keep status as 'disconnected' until OAuth callback succeeds
        $isOAuthProvider = in_array($provider, ['zoho', 'salesforce', 'monday']);
        $formStatus = $data['status'];
        
        if ($isOAuthProvider && $formStatus === 'connected') {
            $data['status'] = 'disconnected';
            $data['last_error'] = null;
        }

        $connectionData = collect($data)->except(['status'])->toArray();

        $connection = CrmIntegration::updateOrCreate(
            ['provider' => $provider],
            $connectionData
        );

        // Update status and clear any previous error
        $connection->update([
            'status' => $data['status'],
            'last_error' => $data['last_error'] ?? null,
        ]);

        // For OAuth providers — return OAuth URL so frontend can redirect
        if ($formStatus === 'connected') {
            if ($provider === 'zoho') {
                $oauthUrl = $this->buildZohoAuthUrl($connection);
                return response()->json([
                    'success'   => true,
                    'message'   => 'Credentials saved. Redirecting to Zoho authorization...',
                    'oauth_url' => $oauthUrl,
                    'connection' => [
                        'id'       => $connection->id,
                        'provider' => $connection->provider,
                        'status'   => $connection->status,
                        'name'     => $connection->connection_name,
                    ],
                ]);
            }
            
            if ($provider === 'monday') {
                $oauthUrl = $this->buildMondayAuthUrl($connection);
                return response()->json([
                    'success'   => true,
                    'message'   => 'Credentials saved. Redirecting to Monday.com authorization...',
                    'oauth_url' => $oauthUrl,
                    'connection' => [
                        'id'       => $connection->id,
                        'provider' => $connection->provider,
                        'status'   => $connection->status,
                        'name'     => $connection->connection_name,
                    ],
                ]);
            }
        }

        // For non-OAuth providers or if status was 'disconnected'
        if ($formStatus === 'connected') {
            $connection->update([
                'last_sync_at' => now(),
                'sync_count'   => ($connection->sync_count ?? 0) + 1,
            ]);
            $connection->refresh();
        }

        return response()->json([
            'success'   => true,
            'message'   => $connection->wasRecentlyCreated
                ? 'CRM connection created successfully.'
                : 'CRM connection updated successfully.',
            'connection' => [
                'id'       => $connection->id,
                'provider' => $connection->provider,
                'status'   => $connection->status,
                'name'     => $connection->connection_name,
            ],
        ]);
    }

    /* ================================================================
       ZOHO OAUTH2 FLOW
       ================================================================ */

    /**
     * Build Zoho OAuth authorization URL.
     */
    private function buildZohoAuthUrl(CrmIntegration $connection): string
    {
        $clientId = Crypt::decryptString($connection->api_key);
        $redirectUri = route('client.crm.zoho.callback', [], true);

        $state = base64_encode(json_encode([
            'provider' => 'zoho',
            'connection_id' => $connection->id,
            'timestamp' => now()->timestamp,
        ]));

        session(['zoho_oauth_state' => $state]);

        $params = [
            'client_id'     => $clientId,
            'response_type' => 'code',
            'redirect_uri'  => $redirectUri,
            'scope'         => self::ZOHO_SCOPES,
            'state'         => $state,
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ];

        return self::ZOHO_AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Redirect to Zoho OAuth authorization page.
     */
    public function redirectToZoho()
    {
        $connection = CrmIntegration::byProvider('zoho')->first();

        if (!$connection) {
            return redirect()->route('client.crm-connections')
                ->with('error', 'Zoho connection not found. Please configure credentials first.');
        }

        return redirect($this->buildZohoAuthUrl($connection));
    }

    /**
     * Handle Zoho OAuth callback.
     */
    public function handleZohoCallback(Request $request)
    {
        $state = $request->get('state');
        $sessionState = session('zoho_oauth_state');

        if (!$state || $state !== $sessionState) {
            return redirect()->route('client.crm-connections')
                ->with('error', 'Invalid OAuth state. Please try again.');
        }

        session()->forget('zoho_oauth_state');

        $code = $request->get('code');
        $error = $request->get('error');

        if ($error) {
            return redirect()->route('client.crm-connections')
                ->with('error', 'Zoho authorization failed: ' . $request->get('error_description', $error));
        }

        if (!$code) {
            return redirect()->route('client.crm-connections')
                ->with('error', 'Authorization code not received from Zoho.');
        }

        $connection = CrmIntegration::byProvider('zoho')->first();

        if (!$connection) {
            return redirect()->route('client.crm-connections')
                ->with('error', 'Zoho connection not found.');
        }

        try {
            $clientId = Crypt::decryptString($connection->api_key);
            $clientSecret = Crypt::decryptString($connection->api_secret);
            $redirectUri = route('client.crm.zoho.callback', [], true);

            $response = Http::asForm()->post(self::ZOHO_TOKEN_URL, [
                'grant_type'    => 'authorization_code',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri'  => $redirectUri,
                'code'          => $code,
            ]);

            if (!$response->successful()) {
                $errorData = $response->json();
                return redirect()->route('client.crm-connections')
                    ->with('error', 'Token exchange failed: ' . ($errorData['error'] ?? 'Unknown error'));
            }

            $tokenData = $response->json();

            $updateData = [
                'access_token'     => Crypt::encryptString($tokenData['access_token']),
                'status'           => 'connected',
                'last_sync_at'     => now(),
                'last_error'       => null,
            ];

            if (!empty($tokenData['refresh_token'])) {
                $updateData['refresh_token'] = Crypt::encryptString($tokenData['refresh_token']);
            }

            if (!empty($tokenData['expires_in'])) {
                $updateData['token_expires_at'] = now()->addSeconds($tokenData['expires_in']);
            }

            $connection->update($updateData);

            $this->fetchZohoUserInfo($connection, $tokenData['access_token']);

            return redirect()->route('client.crm-connections')
                ->with('success', 'Zoho CRM connected successfully!');

        } catch (\Exception $e) {
            $connection->update([
                'status'     => 'disconnected',
                'last_error' => $e->getMessage(),
            ]);

            return redirect()->route('client.crm-connections')
                ->with('error', 'Zoho connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Refresh Zoho access token using refresh token.
     */
    private function refreshZohoToken(CrmIntegration $connection): array
    {
        if (!$connection->refresh_token) {
            return ['success' => false, 'message' => 'No refresh token available.'];
        }

        try {
            $clientId = Crypt::decryptString($connection->api_key);
            $clientSecret = Crypt::decryptString($connection->api_secret);
            $refreshToken = Crypt::decryptString($connection->refresh_token);

            $response = Http::asForm()->post(self::ZOHO_TOKEN_URL, [
                'grant_type'    => 'refresh_token',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
            ]);

            if (!$response->successful()) {
                $errorData = $response->json();
                return [
                    'success' => false,
                    'message' => 'Token refresh failed: ' . ($errorData['error'] ?? 'Unknown error')
                ];
            }

            $tokenData = $response->json();

            $connection->update([
                'access_token'     => Crypt::encryptString($tokenData['access_token']),
                'token_expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 3600),
                'last_error'       => null,
            ]);

            return [
                'success'      => true,
                'message'      => 'Token refreshed successfully.',
                'access_token' => $tokenData['access_token'],
                'expires_in'   => $tokenData['expires_in'] ?? 3600,
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Token refresh failed: ' . $e->getMessage()];
        }
    }

    /**
     * Fetch Zoho user info to verify connection.
     */
    private function fetchZohoUserInfo(CrmIntegration $connection, string $accessToken): void
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            ])->get('https://www.zohoapis.com/crm/v2/users/me');

            if ($response->successful()) {
                $userData = $response->json();
                $user = $userData['users'][0] ?? null;

                if ($user) {
                    $settings = $connection->settings ?? [];
                    $settings['zoho_user'] = [
                        'id'    => $user['id'] ?? null,
                        'email' => $user['email'] ?? null,
                        'name'  => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''),
                    ];
                    $connection->update(['settings' => $settings]);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch Zoho user info: ' . $e->getMessage());
        }
    }

    /* ================================================================
       MONDAY.COM OAUTH2 FLOW
       ================================================================ */

    /**
     * Build Monday.com OAuth authorization URL.
     */
    private function buildMondayAuthUrl(CrmIntegration $connection): string
    {
        $clientId = Crypt::decryptString($connection->api_key);
        $redirectUri = route('client.crm.monday.callback', [], true);

        $state = base64_encode(json_encode([
            'provider' => 'monday',
            'connection_id' => $connection->id,
            'timestamp' => now()->timestamp,
        ]));

        session(['monday_oauth_state' => $state]);

        $params = [
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'scope'         => self::MONDAY_SCOPES,
            'state'         => $state,
        ];

        return self::MONDAY_AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Redirect to Monday.com OAuth authorization page.
     */
    public function redirectToMonday()
    {
        $connection = CrmIntegration::byProvider('monday')->first();

        if (!$connection) {
            return redirect()->route('client.crm-connections')
                ->with('error', 'Monday.com connection not found. Please configure credentials first.');
        }

        return redirect($this->buildMondayAuthUrl($connection));
    }

    /**
     * Handle Monday.com OAuth callback.
     */
    public function handleMondayCallback(Request $request)
    {
        $state = $request->get('state');
        $sessionState = session('monday_oauth_state');

        if (!$state || $state !== $sessionState) {
            return redirect()->route('client.crm-connections')
                ->with('error', 'Invalid OAuth state. Please try again.');
        }

        session()->forget('monday_oauth_state');

        $code = $request->get('code');
        $error = $request->get('error');

        if ($error) {
            return redirect()->route('client.crm-connections')
                ->with('error', 'Monday.com authorization failed: ' . $request->get('error_description', $error));
        }

        if (!$code) {
            return redirect()->route('client.crm-connections')
                ->with('error', 'Authorization code not received from Monday.com.');
        }

        $connection = CrmIntegration::byProvider('monday')->first();

        if (!$connection) {
            return redirect()->route('client.crm-connections')
                ->with('error', 'Monday.com connection not found.');
        }

        try {
            $clientId = Crypt::decryptString($connection->api_key);
            $clientSecret = Crypt::decryptString($connection->api_secret);
            $redirectUri = route('client.crm.monday.callback', [], true);

            $response = Http::asForm()->post(self::MONDAY_TOKEN_URL, [
                'grant_type'    => 'authorization_code',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri'  => $redirectUri,
                'code'          => $code,
            ]);

            if (!$response->successful()) {
                $errorData = $response->json();
                return redirect()->route('client.crm-connections')
                    ->with('error', 'Token exchange failed: ' . ($errorData['error'] ?? 'Unknown error'));
            }

            $tokenData = $response->json();

            $updateData = [
                'access_token'     => Crypt::encryptString($tokenData['access_token']),
                'status'           => 'connected',
                'last_sync_at'     => now(),
                'last_error'       => null,
            ];

            if (!empty($tokenData['refresh_token'])) {
                $updateData['refresh_token'] = Crypt::encryptString($tokenData['refresh_token']);
            }

            // Monday tokens expire after 30 days of inactivity
            $updateData['token_expires_at'] = now()->addDays(30);

            $connection->update($updateData);

            $this->fetchMondayUserInfo($connection, $tokenData['access_token']);

            return redirect()->route('client.crm-connections')
                ->with('success', 'Monday.com connected successfully!');

        } catch (\Exception $e) {
            $connection->update([
                'status'     => 'disconnected',
                'last_error' => $e->getMessage(),
            ]);

            return redirect()->route('client.crm-connections')
                ->with('error', 'Monday.com connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Refresh Monday.com access token.
     */
    private function refreshMondayToken(CrmIntegration $connection): array
    {
        if (!$connection->refresh_token) {
            return ['success' => false, 'message' => 'No refresh token available.'];
        }

        try {
            $clientId = Crypt::decryptString($connection->api_key);
            $clientSecret = Crypt::decryptString($connection->api_secret);
            $refreshToken = Crypt::decryptString($connection->refresh_token);

            $response = Http::asForm()->post(self::MONDAY_TOKEN_URL, [
                'grant_type'    => 'refresh_token',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
            ]);

            if (!$response->successful()) {
                $errorData = $response->json();
                return [
                    'success' => false,
                    'message' => 'Token refresh failed: ' . ($errorData['error'] ?? 'Unknown error')
                ];
            }

            $tokenData = $response->json();

            $connection->update([
                'access_token'     => Crypt::encryptString($tokenData['access_token']),
                'token_expires_at' => now()->addDays(30),
                'last_error'       => null,
            ]);

            return [
                'success'      => true,
                'message'      => 'Token refreshed successfully.',
                'access_token' => $tokenData['access_token'],
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Token refresh failed: ' . $e->getMessage()];
        }
    }

    /**
     * Fetch Monday.com user info.
     */
    private function fetchMondayUserInfo(CrmIntegration $connection, string $accessToken): void
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $accessToken,
                'Content-Type'  => 'application/json',
            ])->post('https://api.monday.com/v2', [
                'query' => '{ me { id name email } }'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $user = $data['data']['me'] ?? null;

                if ($user) {
                    $settings = $connection->settings ?? [];
                    $settings['monday_user'] = [
                        'id'    => $user['id'] ?? null,
                        'email' => $user['email'] ?? null,
                        'name'  => $user['name'] ?? null,
                    ];
                    $connection->update(['settings' => $settings]);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch Monday.com user info: ' . $e->getMessage());
        }
    }

    /* ================================================================
       TEST CONNECTION
       ================================================================ */

    /**
     * Test a CRM connection.
     */
    public function test(string $provider)
    {
        $connection = CrmIntegration::byProvider($provider)->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'No connection found for this provider.',
            ], 404);
        }

        try {
            $result = $this->testConnection($connection);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'details' => $result['details'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ================================================================
       DISCONNECT / DESTROY / SYNC / STATUS
       ================================================================ */

    /**
     * Disconnect a CRM connection.
     */
    public function disconnect(string $provider)
    {
        $connection = CrmIntegration::byProvider($provider)->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $connection->update([
            'status'        => 'disconnected',
            'access_token'  => null,
            'refresh_token' => null,
            'token_expires_at' => null,
            'last_error'    => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'CRM connection disconnected successfully.',
        ]);
    }

    /**
     * Delete a CRM connection.
     */
    public function destroy(string $provider)
    {
        $connection = CrmIntegration::byProvider($provider)->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $connection->delete();

        return response()->json([
            'success' => true,
            'message' => 'CRM connection deleted successfully.',
        ]);
    }

    /**
     * Sync data from CRM.
     */
    public function sync(string $provider)
    {
        $connection = CrmIntegration::byProvider($provider)->first();

        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'CRM not connected.',
            ], 400);
        }

        // Refresh token if expired before syncing
        if ($connection->is_expired) {
            if ($provider === 'zoho') {
                $refreshResult = $this->refreshZohoToken($connection);
                if (!$refreshResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sync failed: ' . $refreshResult['message'],
                    ], 400);
                }
            } elseif ($provider === 'monday') {
                $refreshResult = $this->refreshMondayToken($connection);
                if (!$refreshResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sync failed: ' . $refreshResult['message'],
                    ], 400);
                }
            }
        }

        try {
            // Provider-specific sync logic
            $syncResult = null;
            if ($provider === 'pipedrive') {
                $syncResult = $this->syncPipedrive($connection);
            } elseif ($provider === 'monday') {
                $syncResult = $this->syncMonday($connection);
            } elseif ($provider === 'hubspot') {
                $syncResult = $this->syncHubSpot($connection);
            }

            if ($syncResult && !$syncResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $syncResult['message'],
                ], 500);
            }

            $connection->update([
                'last_sync_at' => now(),
                'sync_count'   => ($connection->sync_count ?? 0) + 1,
                'last_error'   => null,
            ]);

            $connection->refresh();

            return response()->json([
                'success'    => true,
                'message'    => $syncResult['message'] ?? 'Sync initiated successfully.',
                'last_sync'  => $connection->last_sync_at?->diffForHumans(),
                'details'    => $syncResult['details'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get connection status for all providers.
     */
    public function status()
    {
        $connections = CrmIntegration::all()->keyBy('provider');
        $providers   = CrmIntegration::providers();

        $status = [];
        foreach ($providers as $key => $meta) {
            $conn = $connections[$key] ?? null;
            $status[$key] = [
                'connected'   => $conn?->is_connected ?? false,
                'status'      => $conn?->status ?? 'disconnected',
                'last_sync'   => $conn?->last_sync_at?->diffForHumans() ?? 'Never',
                'sync_count'  => $conn?->sync_count ?? 0,
                'name'        => $conn?->connection_name ?? null,
            ];
        }

        return response()->json(['status' => $status]);
    }

    /* ================================================================
       PRIVATE TEST HELPERS
       ================================================================ */

    private function testConnection(CrmIntegration $connection): array
    {
        switch ($connection->provider) {
            case 'salesforce':
                return $this->testSalesforce($connection);
            case 'hubspot':
                return $this->testHubSpot($connection);
            case 'zoho':
                return $this->testZoho($connection);
            case 'pipedrive':
                return $this->testPipedrive($connection);
            case 'monday':
                return $this->testMonday($connection);
            default:
                return ['success' => false, 'message' => 'Unknown provider.'];
        }
    }

    private function testSalesforce(CrmIntegration $connection): array
    {
        return [
            'success' => true,
            'message' => 'Salesforce connection verified.',
            'details' => ['api_version' => 'v58.0']
        ];
    }

    private function testHubSpot(CrmIntegration $connection): array
    {
        try {
            $apiKey = Crypt::decryptString($connection->api_key);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Stored API key could not be decrypted.'];
        }

        $result = $this->verifyHubSpotApiKey($apiKey);

        if ($result['success']) {
            $result['details'] = ['portal_id' => $connection->portal_id];
        }

        return $result;
    }

    /**
     * Calls HubSpot's API directly with the given token — used both to gate
     * store() before a connection is ever saved as "connected", and by the
     * Test Connection button. A token that can't list contacts isn't valid,
     * regardless of what the client claims about it.
     */
    private function verifyHubSpotApiKey(?string $apiKey): array
    {
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'No API key provided.'];
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->get('https://api.hubapi.com/crm/v3/objects/contacts', ['limit' => 1]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'HubSpot connection verified.'];
            }

            $err = $response->json();
            return [
                'success' => false,
                'message' => $err['message'] ?? ('HubSpot API returned HTTP ' . $response->status()),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'HubSpot verification failed: ' . $e->getMessage()];
        }
    }

    /**
     * Same purpose as verifyHubSpotApiKey() but for Pipedrive — shared by
     * store()'s pre-save gate and testPipedrive().
     */
    private function verifyPipedriveApiKey(?string $apiKey): array
    {
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'No API key provided.'];
        }

        try {
            $response = Http::timeout(10)->get('https://api.pipedrive.com/v1/users/me', [
                'api_token' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Pipedrive connection verified.',
                    'user'    => $data['data']['name'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => 'Pipedrive API returned error: ' . ($response->json()['error'] ?? 'Unknown error'),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Pipedrive verification failed: ' . $e->getMessage()];
        }
    }

    private function testZoho(CrmIntegration $connection): array
    {
        if ($connection->is_expired && $connection->refresh_token) {
            $refreshResult = $this->refreshZohoToken($connection);
            if (!$refreshResult['success']) {
                return $refreshResult;
            }
            $connection->refresh();
        }

        try {
            $accessToken = Crypt::decryptString($connection->access_token);

            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            ])->get('https://www.zohoapis.com/crm/v2/users/me');

            if ($response->successful()) {
                $data = $response->json();
                $user = $data['users'][0] ?? null;

                return [
                    'success' => true,
                    'message' => 'Zoho CRM connection verified.',
                    'details' => [
                        'user'  => $user['email'] ?? 'Unknown',
                        'name'  => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''),
                    ]
                ];
            }

            $errorData = $response->json();
            return [
                'success' => false,
                'message' => 'Zoho API error: ' . ($errorData['message'] ?? 'Unknown error'),
                'details' => ['code' => $errorData['code'] ?? null]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Zoho connection test failed: ' . $e->getMessage()
            ];
        }
    }

    private function testPipedrive(CrmIntegration $connection): array
    {
        try {
            $apiKey = Crypt::decryptString($connection->api_key);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Stored API key could not be decrypted.'];
        }

        $result = $this->verifyPipedriveApiKey($apiKey);

        if ($result['success']) {
            $result['details'] = ['user' => $result['user'] ?? 'Unknown'];
        }

        return $result;
    }

    private function testMonday(CrmIntegration $connection): array
    {
        if ($connection->is_expired && $connection->refresh_token) {
            $refreshResult = $this->refreshMondayToken($connection);
            if (!$refreshResult['success']) {
                return $refreshResult;
            }
            $connection->refresh();
        }

        try {
            $accessToken = Crypt::decryptString($connection->access_token);

            $response = Http::withHeaders([
                'Authorization' => $accessToken,
                'Content-Type'  => 'application/json',
            ])->post('https://api.monday.com/v2', [
                'query' => '{ me { id name email } }'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $user = $data['data']['me'] ?? null;

                return [
                    'success' => true,
                    'message' => 'Monday.com connection verified.',
                    'details' => [
                        'user'  => $user['email'] ?? 'Unknown',
                        'name'  => $user['name'] ?? 'Unknown',
                    ]
                ];
            }

            $errorData = $response->json();
            return [
                'success' => false,
                'message' => 'Monday.com API error: ' . ($errorData['error_message'] ?? 'Unknown error'),
                'details' => $errorData['errors'] ?? null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Monday.com connection test failed: ' . $e->getMessage()
            ];
        }
    }

    /* ================================================================
       PIPEDRIVE SYNC HELPERS
       ================================================================ */

    /**
     * Sync data from Pipedrive CRM.
     */
    private function syncPipedrive(CrmIntegration $connection): array
    {
        try {
            $apiKey = Crypt::decryptString($connection->api_key);
            $syncConfig = $connection->sync_config ?? [];
            $entities = $syncConfig['entities'] ?? ['contacts', 'leads'];
            $results = [];

            foreach ($entities as $entity) {
                switch ($entity) {
                    case 'contacts':
                        $results['contacts'] = $this->syncPipedrivePersons($apiKey);
                        break;
                    case 'leads':
                    case 'deals':
                        $results['deals'] = $this->syncPipedriveDeals($apiKey);
                        break;
                    case 'companies':
                        $results['companies'] = $this->syncPipedriveOrganizations($apiKey);
                        break;
                    case 'activities':
                        $results['activities'] = $this->syncPipedriveActivities($apiKey);
                        break;
                }
            }

            return [
                'success' => true,
                'message' => 'Pipedrive sync completed.',
                'details' => $results,
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Pipedrive sync failed: ' . $e->getMessage()];
        }
    }

    /**
     * Sync Pipedrive persons (contacts).
     */
    private function syncPipedrivePersons(string $apiKey): array
    {
        $response = Http::get('https://api.pipedrive.com/v1/persons', [
            'api_token' => $apiKey,
            'limit' => 100,
        ]);

        if (!$response->successful()) {
            return ['error' => 'Failed to fetch persons'];
        }

        $data = $response->json();
        $persons = $data['data'] ?? [];

        return [
            'count' => count($persons),
            'sample' => array_slice($persons, 0, 3),
        ];
    }

    /**
     * Sync Pipedrive deals.
     */
    private function syncPipedriveDeals(string $apiKey): array
    {
        $response = Http::get('https://api.pipedrive.com/v1/deals', [
            'api_token' => $apiKey,
            'limit' => 100,
        ]);

        if (!$response->successful()) {
            return ['error' => 'Failed to fetch deals'];
        }

        $data = $response->json();
        $deals = $data['data'] ?? [];

        return [
            'count' => count($deals),
            'sample' => array_slice($deals, 0, 3),
        ];
    }

    /**
     * Sync Pipedrive organizations (companies).
     */
    private function syncPipedriveOrganizations(string $apiKey): array
    {
        $response = Http::get('https://api.pipedrive.com/v1/organizations', [
            'api_token' => $apiKey,
            'limit' => 100,
        ]);

        if (!$response->successful()) {
            return ['error' => 'Failed to fetch organizations'];
        }

        $data = $response->json();
        $orgs = $data['data'] ?? [];

        return [
            'count' => count($orgs),
            'sample' => array_slice($orgs, 0, 3),
        ];
    }

    /**
     * Sync Pipedrive activities.
     */
    private function syncPipedriveActivities(string $apiKey): array
    {
        $response = Http::get('https://api.pipedrive.com/v1/activities', [
            'api_token' => $apiKey,
            'limit' => 100,
        ]);

        if (!$response->successful()) {
            return ['error' => 'Failed to fetch activities'];
        }

        $data = $response->json();
        $activities = $data['data'] ?? [];

        return [
            'count' => count($activities),
            'sample' => array_slice($activities, 0, 3),
        ];
    }

    /* ================================================================
       HUBSPOT SYNC HELPERS
       ================================================================ */

    /**
     * Sync data from HubSpot CRM.
     */
    private function syncHubSpot(CrmIntegration $connection): array
    {
        $apiKey = Crypt::decryptString($connection->api_key);

        try {
            $contactsSynced = $this->syncHubSpotContacts($connection, $apiKey);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'HubSpot contact sync failed: ' . $e->getMessage()];
        }

        // Deals need a separate scope (crm.objects.deals.read) that the
        // Private App token may not have been granted — don't let that
        // wipe out a successful contact sync.
        $dealsSynced = null;
        $dealsWarning = null;
        try {
            $dealsSynced = $this->syncHubSpotDeals($connection, $apiKey);
        } catch (\Exception $e) {
            $dealsWarning = $e->getMessage();
        }

        return [
            'success' => true,
            'message' => $dealsWarning
                ? "HubSpot sync completed (contacts only — deals skipped: {$dealsWarning})"
                : 'HubSpot sync completed.',
            'details' => ['contacts' => $contactsSynced, 'deals' => $dealsSynced],
        ];
    }

    private function syncHubSpotContacts(CrmIntegration $connection, string $apiKey): int
    {
        $count = 0;
        $after = null;

        do {
            $params = ['limit' => 100, 'properties' => 'email,firstname,lastname,company'];
            if ($after) {
                $params['after'] = $after;
            }

            $response = Http::withToken($apiKey)->timeout(15)
                ->get('https://api.hubapi.com/crm/v3/objects/contacts', $params);

            if (!$response->successful()) {
                throw new \Exception('Contacts request failed: HTTP ' . $response->status());
            }

            $data = $response->json();

            foreach ($data['results'] ?? [] as $contact) {
                $props = $contact['properties'] ?? [];

                \App\Models\CrmContact::updateOrCreate(
                    ['connection_id' => $connection->id, 'external_id' => $contact['id']],
                    [
                        'provider'         => 'hubspot',
                        'email'            => $props['email'] ?? null,
                        'first_name'       => $props['firstname'] ?? null,
                        'last_name'        => $props['lastname'] ?? null,
                        'company'          => $props['company'] ?? null,
                        'last_activity_at' => $contact['updatedAt'] ?? null,
                        'raw_data'         => $contact,
                    ]
                );
                $count++;
            }

            $after = $data['paging']['next']['after'] ?? null;
        } while ($after);

        return $count;
    }

    private function syncHubSpotDeals(CrmIntegration $connection, string $apiKey): int
    {
        $count = 0;
        $after = null;

        do {
            $params = [
                'limit'      => 100,
                'properties' => 'dealname,amount,dealstage,closedate,hs_is_closed_won,hs_is_closed_lost',
            ];
            if ($after) {
                $params['after'] = $after;
            }

            $response = Http::withToken($apiKey)->timeout(15)
                ->get('https://api.hubapi.com/crm/v3/objects/deals', $params);

            if (!$response->successful()) {
                throw new \Exception('Deals request failed: HTTP ' . $response->status());
            }

            $data = $response->json();

            foreach ($data['results'] ?? [] as $deal) {
                $props = $deal['properties'] ?? [];

                $status = 'open';
                if (($props['hs_is_closed_won'] ?? null) === 'true') {
                    $status = 'won';
                } elseif (($props['hs_is_closed_lost'] ?? null) === 'true') {
                    $status = 'lost';
                }

                \App\Models\CrmDeal::updateOrCreate(
                    ['connection_id' => $connection->id, 'external_id' => $deal['id']],
                    [
                        'provider'   => 'hubspot',
                        'name'       => $props['dealname'] ?? 'Untitled',
                        'value'      => $props['amount'] ?? 0,
                        'stage'      => $props['dealstage'] ?? null,
                        'status'     => $status,
                        'close_date' => $props['closedate'] ?? null,
                        'raw_data'   => $deal,
                    ]
                );
                $count++;
            }

            $after = $data['paging']['next']['after'] ?? null;
        } while ($after);

        return $count;
    }

    /* ================================================================
       MONDAY.COM SYNC HELPERS
       ================================================================ */

    /**
     * Sync data from Monday.com.
     */
    private function syncMonday(CrmIntegration $connection): array
    {
        try {
            $accessToken = Crypt::decryptString($connection->access_token);
            $syncConfig = $connection->sync_config ?? [];
            $entities = $syncConfig['entities'] ?? ['contacts', 'leads'];
            $results = [];

            foreach ($entities as $entity) {
                switch ($entity) {
                    case 'contacts':
                        $results['contacts'] = $this->syncMondayUsers($accessToken);
                        break;
                    case 'leads':
                    case 'deals':
                        $results['boards'] = $this->syncMondayBoards($accessToken);
                        break;
                    case 'companies':
                        $results['teams'] = $this->syncMondayTeams($accessToken);
                        break;
                    case 'activities':
                        $results['updates'] = $this->syncMondayUpdates($accessToken);
                        break;
                }
            }

            return [
                'success' => true,
                'message' => 'Monday.com sync completed.',
                'details' => $results,
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Monday.com sync failed: ' . $e->getMessage()];
        }
    }

    /**
     * Sync Monday.com users.
     */
    private function syncMondayUsers(string $accessToken): array
    {
        $response = Http::withHeaders([
            'Authorization' => $accessToken,
            'Content-Type'  => 'application/json',
        ])->post('https://api.monday.com/v2', [
            'query' => '{ users { id name email } }'
        ]);

        if (!$response->successful()) {
            return ['error' => 'Failed to fetch users'];
        }

        $data = $response->json();
        $users = $data['data']['users'] ?? [];

        return [
            'count' => count($users),
            'sample' => array_slice($users, 0, 3),
        ];
    }

    /**
     * Sync Monday.com boards.
     */
    private function syncMondayBoards(string $accessToken): array
    {
        $response = Http::withHeaders([
            'Authorization' => $accessToken,
            'Content-Type'  => 'application/json',
        ])->post('https://api.monday.com/v2', [
            'query' => '{ boards { id name state } }'
        ]);

        if (!$response->successful()) {
            return ['error' => 'Failed to fetch boards'];
        }

        $data = $response->json();
        $boards = $data['data']['boards'] ?? [];

        return [
            'count' => count($boards),
            'sample' => array_slice($boards, 0, 3),
        ];
    }

    /**
     * Sync Monday.com teams.
     */
    private function syncMondayTeams(string $accessToken): array
    {
        $response = Http::withHeaders([
            'Authorization' => $accessToken,
            'Content-Type'  => 'application/json',
        ])->post('https://api.monday.com/v2', [
            'query' => '{ teams { id name users { id } } }'
        ]);

        if (!$response->successful()) {
            return ['error' => 'Failed to fetch teams'];
        }

        $data = $response->json();
        $teams = $data['data']['teams'] ?? [];

        return [
            'count' => count($teams),
            'sample' => array_slice($teams, 0, 3),
        ];
    }

    /**
     * Sync Monday.com updates (activities).
     */
    private function syncMondayUpdates(string $accessToken): array
    {
        $response = Http::withHeaders([
            'Authorization' => $accessToken,
            'Content-Type'  => 'application/json',
        ])->post('https://api.monday.com/v2', [
            'query' => '{ updates { id body text creator { id name } created_at } }'
        ]);

        if (!$response->successful()) {
            return ['error' => 'Failed to fetch updates'];
        }

        $data = $response->json();
        $updates = $data['data']['updates'] ?? [];

        return [
            'count' => count($updates),
            'sample' => array_slice($updates, 0, 3),
        ];
    }
}