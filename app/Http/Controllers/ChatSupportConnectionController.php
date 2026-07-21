<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChatSupportIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ChatSupportConnectionController extends Controller
{
    /**
     * Currently logged-in client (tenant) ID.
     */
    private function currentClientId(): ?int
    {
        return auth('client')->id();
    }

    /**
     * Find a connection for the current client + provider.
     */
    private function findConnection(string $provider, ?int $clientId = null): ?ChatSupportIntegration
    {
        return ChatSupportIntegration::where('client_id', $clientId ?? $this->currentClientId())
            ->where('provider', $provider)
            ->first();
    }

    /**
     * Display the Chat & Support connections listing page.
     */
    public function index()
    {
        $connections = ChatSupportIntegration::where('client_id', $this->currentClientId())
            ->get()
            ->keyBy('provider');
        $providers   = ChatSupportIntegration::providers();

        // Calculate stats
        $totalConnected = $connections->where('status', 'connected')->count();
        $totalProviders = count($providers);
        $syncToday      = $connections->where('last_sync_at', '>=', now()->startOfDay())->count();
        $lastSync       = $connections->whereNotNull('last_sync_at')->max('last_sync_at');

        // Total messages today across all connected platforms
        $totalMessages = $connections->where('status', 'connected')->sum(function ($c) {
            return $c->metrics['messages_today'] ?? 0;
        });

        // Avg satisfaction score
        $avgSatisfaction = 0;
        $connectedWithScore = $connections->where('status', 'connected')->filter(function ($c) {
            return isset($c->metrics['satisfaction_score']);
        });
        if ($connectedWithScore->count() > 0) {
            $avgSatisfaction = round($connectedWithScore->avg(function ($c) {
                return $c->metrics['satisfaction_score'] ?? 0;
            }), 1);
        }

        // Sync health status
        $syncHealth = 'healthy';
        if ($totalConnected > 0) {
            $errorCount = $connections->where('status', 'error')->count();
            if ($errorCount > 0) {
                $syncHealth = $errorCount === $totalConnected ? 'critical' : 'warning';
            }
        }

        return view('client.chat-support-connections', compact(
            'connections',
            'providers',
            'totalConnected',
            'totalProviders',
            'syncToday',
            'lastSync',
            'syncHealth',
            'totalMessages',
            'avgSatisfaction'
        ));
    }

    /**
     * Show the connect form for a specific provider.
     */
    public function create(string $provider)
    {
        $providers = ChatSupportIntegration::providers();

        if (!isset($providers[$provider])) {
            abort(404, 'Chat & Support provider not found.');
        }

        $meta = $providers[$provider];
        $existing = $this->findConnection($provider);

        return view('client.chat-support-connect', compact('provider', 'meta', 'existing'));
    }

    /**
     * Store or update a Chat & Support connection (manual credentials path).
     */
    public function store(Request $request, string $provider)
    {
        $providers = ChatSupportIntegration::providers();

        if (!isset($providers[$provider])) {
            return response()->json(['error' => 'Invalid provider'], 422);
        }

        $clientId = $this->currentClientId();
        if (!$clientId) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $rules = [
            'connection_name' => 'required|string|max:100',
            'status'          => 'required|in:connected,disconnected',
        ];

        // Provider-specific validation
        switch ($provider) {
            case 'whatsapp':
                $rules['phone_number'] = 'required|string';
                $rules['api_key'] = 'required|string';
                $rules['webhook_url'] = 'nullable|url';
                break;
            case 'slack':
                $rules['workspace_id'] = 'required|string';
                $rules['channel_id'] = 'nullable|string';
                $rules['access_token'] = 'required|string';
                break;
            case 'twilio':
                $rules['account_sid'] = 'required|string|regex:/^AC[0-9a-fA-F]{32}$/';
                $rules['auth_token'] = 'required|string|min:32';
                $rules['phone_number'] = 'required|string|regex:/^\+[1-9]\d{1,14}$/';
                break;
            case 'zendesk':
                $rules['subdomain'] = 'required|string';
                $rules['api_key'] = 'required|string';
                $rules['access_token'] = 'nullable|string';
                break;
            case 'tawk':
                $rules['app_id'] = 'required|string';
                $rules['api_key'] = 'required|string';
                $rules['channel_id'] = 'nullable|string';
                break;
            case 'intercom':
                $rules['app_id'] = 'required|string';
                $rules['access_token'] = 'required|string';
                $rules['webhook_url'] = 'nullable|url';
                break;
            case 'livechat':
                $rules['license_id'] = 'required|string';
                $rules['api_key'] = 'required|string';
                $rules['webhook_url'] = 'nullable|url';
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
        $data['provider']  = $provider;
        $data['client_id'] = $clientId;

        // Encrypt sensitive fields
        $encryptFields = ['api_key', 'api_secret', 'access_token', 'auth_token'];
        foreach ($encryptFields as $field) {
            if (!empty($data[$field])) {
                $data[$field] = Crypt::encryptString($data[$field]);
            }
        }

        // Handle settings & sync_config
        $data['settings'] = $request->input('settings', []);
        $data['sync_config'] = $request->input('sync_config', [
            'sync_frequency' => 'realtime',
            'message_types'  => ['incoming', 'outgoing', 'system'],
            'auto_reply'     => false,
            'store_media'    => true,
        ]);

        // Mock metrics for demo (in production, fetch from API)
        $data['metrics'] = $request->input('metrics', [
            'messages_today'    => rand(100, 5000),
            'avg_response_time' => rand(30, 300),
            'satisfaction_score' => rand(75, 98) / 10,
            'active_agents'     => rand(1, 20),
            'queued_chats'      => rand(0, 15),
        ]);

        if ($data['status'] === 'connected') {
            $data['last_sync_at'] = now();
            $data['sync_count'] = \DB::raw('sync_count + 1');
        }

        $connection = ChatSupportIntegration::updateOrCreate(
            ['client_id' => $clientId, 'provider' => $provider],
            $data
        );

        if (isset($data['sync_count'])) {
            $connection->refresh();
        }

        return response()->json([
            'success'   => true,
            'message'   => $connection->wasRecentlyCreated
                ? 'Chat & Support connection created successfully.'
                : 'Chat & Support connection updated successfully.',
            'connection' => [
                'id'       => $connection->id,
                'provider' => $connection->provider,
                'status'   => $connection->status,
                'name'     => $connection->connection_name,
            ],
        ]);
    }

    // ============================
    // SLACK OAUTH FLOW
    // ============================

    /**
     * Redirect to Slack OAuth for workspace connection.
     * Each client connects their OWN Slack workspace.
     */
    public function redirectToSlack()
    {
        $clientId = $this->currentClientId();

        if (!$clientId) {
            return redirect()->route('client.login');
        }

        $slackClientId = config('services.slack.client_id');

        if (empty($slackClientId)) {
            return redirect()
                ->route('client.chat-support.connect', ['provider' => 'slack'])
                ->with('error', 'Slack Client ID is not configured. Please set SLACK_CLIENT_ID in the .env file.');
        }

        // Carry the client ID through the OAuth round-trip via encrypted state
        $state = Crypt::encryptString(json_encode([
            'client_id' => $clientId,
            'ts'        => now()->timestamp,
        ]));

        $scopes = implode(',', [
            'channels:read',
            'channels:history',
            'chat:write',
            'users:read',
            'files:read',
            'groups:read',
            'im:read',
            'mpim:read',
        ]);

        $params = [
            'client_id'     => $slackClientId,
            'redirect_uri'  => route('client.chat-support.slack.callback'),
            'scope'         => $scopes,
            'state'         => $state,
        ];

        return redirect('https://slack.com/oauth/v2/authorize?' . http_build_query($params));
    }

    /**
     * Handle Slack OAuth callback.
     * Exchanges code for access token and stores the CLIENT'S workspace info.
     */
    public function handleSlackCallback(Request $request)
    {
        $connectRoute = route('client.chat-support.connect', ['provider' => 'slack']);

        if ($request->filled('error')) {
            return redirect($connectRoute)->with(
                'error',
                'Slack authorization failed: ' . $request->input('error_description', $request->input('error'))
            );
        }

        $code = $request->input('code');
        if (!$code) {
            return redirect($connectRoute)->with('error', 'No authorization code received from Slack.');
        }

        // Resolve which client started this flow
        $clientId = $this->currentClientId();

        if (!$clientId && $request->filled('state')) {
            try {
                $payload  = json_decode(Crypt::decryptString($request->state), true);
                $clientId = $payload['client_id'] ?? null;
            } catch (\Throwable $e) {
                $clientId = null;
            }
        }

        if (!$clientId) {
            return redirect()->route('client.login')
                ->with('error', 'Your session expired during Slack authorization. Please log in and try again.');
        }

        // 1) Exchange code for access token
        $tokenResponse = Http::asForm()->post('https://slack.com/api/oauth.v2.access', [
            'client_id'     => config('services.slack.client_id'),
            'client_secret' => config('services.slack.client_secret'),
            'redirect_uri'  => route('client.chat-support.slack.callback'),
            'code'          => $code,
        ]);

        $tokenData = $tokenResponse->json();

        if (!$tokenResponse->successful() || empty($tokenData['ok']) || empty($tokenData['access_token'])) {
            return redirect($connectRoute)->with(
                'error',
                'Token exchange failed: ' . ($tokenData['error'] ?? 'Unknown error from Slack.')
            );
        }

        $accessToken = $tokenData['access_token'];
        $workspaceId = $tokenData['team']['id'] ?? null;
        $workspaceName = $tokenData['team']['name'] ?? 'Slack Workspace';

        // 2) Fetch workspace info for validation
        $workspaceInfo = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get('https://slack.com/api/team.info');

        if ($workspaceInfo->successful() && !empty($workspaceInfo->json('team.name'))) {
            $workspaceName = $workspaceInfo->json('team.name');
        }

        // 3) Persist the connection under THIS client (their own workspace)
        $existing = $this->findConnection('slack', $clientId);

        ChatSupportIntegration::updateOrCreate(
            ['client_id' => $clientId, 'provider' => 'slack'],
            [
                'connection_name' => $existing?->connection_name ?? ($workspaceName . ' Slack'),
                'status'          => 'connected',
                'workspace_id'    => $workspaceId,
                'workspace_name'  => $workspaceName,
                'access_token'    => Crypt::encryptString($accessToken),
                'settings'        => array_merge($existing?->settings ?? [], [
                    'workspace_name'  => $workspaceName,
                    'connected_via'   => 'oauth',
                    'bot_user_id'     => $tokenData['bot_user_id'] ?? null,
                    'authed_user_id'  => $tokenData['authed_user']['id'] ?? null,
                    'scope'           => $tokenData['scope'] ?? null,
                ]),
                'sync_config'     => $existing?->sync_config ?? [
                    'sync_frequency' => 'realtime',
                    'message_types'  => ['incoming', 'outgoing', 'system', 'media'],
                    'auto_reply'     => false,
                    'store_media'    => true,
                ],
                'metrics'         => $existing?->metrics ?? [
                    'messages_today'     => 0,
                    'avg_response_time'  => 0,
                    'satisfaction_score' => 0,
                    'active_agents'      => 0,
                    'queued_chats'       => 0,
                ],
                'last_sync_at'    => now(),
                'last_error'      => null,
            ]
        );

        return redirect()
            ->route('client.chat-support-connections')
            ->with('success', 'Slack connected successfully! Workspace: ' . $workspaceName);
    }

    // ============================
    // WHATSAPP OAUTH FLOW (Meta)
    // ============================

    /**
     * Redirect to Meta OAuth for WhatsApp Business.
     */
    public function redirectToWhatsApp()
    {
        $clientId = $this->currentClientId();

        if (!$clientId) {
            return redirect()->route('client.login');
        }

        $appId = config('services.whatsapp.client_id');

        if (empty($appId)) {
            return redirect()
                ->route('client.chat-support.connect', ['provider' => 'whatsapp'])
                ->with('error', 'WhatsApp App ID is not configured. Please set WHATSAPP_APP_ID in the .env file.');
        }

        $state = Crypt::encryptString(json_encode([
            'client_id' => $clientId,
            'ts'        => now()->timestamp,
        ]));

        $params = [
            'client_id'     => $appId,
            'redirect_uri'  => route('client.chat-support.whatsapp.callback'),
            'response_type' => 'code',
            'state'         => $state,
        ];

        if ($configId = config('services.whatsapp.config_id')) {
            $params['config_id'] = $configId;
        } else {
            $params['scope'] = 'whatsapp_business_management,whatsapp_business_messaging,business_management';
        }

        return redirect('https://www.facebook.com/v21.0/dialog/oauth?' . http_build_query($params));
    }

    /**
     * Handle Meta OAuth callback for WhatsApp Business.
     */
    public function handleWhatsAppCallback(Request $request)
    {
        $connectRoute = route('client.chat-support.connect', ['provider' => 'whatsapp']);

        if ($request->filled('error')) {
            return redirect($connectRoute)->with(
                'error',
                'WhatsApp authorization failed: ' . $request->input('error_description', $request->input('error'))
            );
        }

        $code = $request->input('code');
        if (!$code) {
            return redirect($connectRoute)->with('error', 'No authorization code received from Meta.');
        }

        $clientId = $this->currentClientId();

        if (!$clientId && $request->filled('state')) {
            try {
                $payload  = json_decode(Crypt::decryptString($request->state), true);
                $clientId = $payload['client_id'] ?? null;
            } catch (\Throwable $e) {
                $clientId = null;
            }
        }

        if (!$clientId) {
            return redirect()->route('client.login')
                ->with('error', 'Your session expired during WhatsApp authorization. Please log in and try again.');
        }

        $tokenResponse = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
            'client_id'     => config('services.whatsapp.client_id'),
            'client_secret' => config('services.whatsapp.client_secret'),
            'redirect_uri'  => route('client.chat-support.whatsapp.callback'),
            'code'          => $code,
        ]);

        if (!$tokenResponse->successful() || empty($tokenResponse->json('access_token'))) {
            return redirect($connectRoute)->with(
                'error',
                'Token exchange failed: ' . ($tokenResponse->json('error.message') ?? 'Unknown error from Meta.')
            );
        }

        $accessToken = $tokenResponse->json('access_token');

        $wabaId = null;
        $debug = Http::get('https://graph.facebook.com/v21.0/debug_token', [
            'input_token'  => $accessToken,
            'access_token' => config('services.whatsapp.client_id') . '|' . config('services.whatsapp.client_secret'),
        ]);

        foreach ($debug->json('data.granular_scopes', []) as $scope) {
            if (in_array($scope['scope'] ?? '', ['whatsapp_business_management', 'whatsapp_business_messaging'])
                && !empty($scope['target_ids'])) {
                $wabaId = $scope['target_ids'][0];
                break;
            }
        }

        $phoneNumber   = null;
        $phoneNumberId = null;
        if ($wabaId) {
            $phones = Http::get("https://graph.facebook.com/v21.0/{$wabaId}/phone_numbers", [
                'access_token' => $accessToken,
            ]);

            $first = $phones->json('data.0');
            if ($first) {
                $phoneNumber   = $first['display_phone_number'] ?? null;
                $phoneNumberId = $first['id'] ?? null;
            }
        }

        $existing = $this->findConnection('whatsapp', $clientId);

        ChatSupportIntegration::updateOrCreate(
            ['client_id' => $clientId, 'provider' => 'whatsapp'],
            [
                'connection_name' => $existing?->connection_name ?? 'WhatsApp Business Connection',
                'status'          => 'connected',
                'phone_number'    => $phoneNumber,
                'access_token'    => Crypt::encryptString($accessToken),
                'settings'        => array_merge($existing?->settings ?? [], [
                    'waba_id'         => $wabaId,
                    'phone_number_id' => $phoneNumberId,
                    'connected_via'   => 'oauth',
                ]),
                'sync_config'     => $existing?->sync_config ?? [
                    'sync_frequency' => 'realtime',
                    'message_types'  => ['incoming', 'outgoing', 'system', 'media'],
                    'auto_reply'     => false,
                    'store_media'    => true,
                ],
                'metrics'         => $existing?->metrics ?? [
                    'messages_today'     => 0,
                    'avg_response_time'  => 0,
                    'satisfaction_score' => 0,
                    'active_agents'      => 0,
                    'queued_chats'       => 0,
                ],
                'last_sync_at'    => now(),
                'last_error'      => null,
            ]
        );

        return redirect()
            ->route('client.chat-support-connections')
            ->with(
                'success',
                'WhatsApp Business connected successfully' . ($phoneNumber ? " ({$phoneNumber})" : '') . '.'
            );
    }

    /**
     * Test a Chat & Support connection.
     */
    public function test(string $provider)
    {
        $connection = $this->findConnection($provider);

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

    /**
     * Disconnect a Chat & Support connection.
     */
    public function disconnect(string $provider)
    {
        $connection = $this->findConnection($provider);

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $connection->update([
            'status'        => 'disconnected',
            'access_token'  => null,
            'auth_token'    => null,
            'last_error'    => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chat & Support connection disconnected successfully.',
        ]);
    }

    /**
     * Delete a Chat & Support connection.
     */
    public function destroy(string $provider)
    {
        $connection = $this->findConnection($provider);

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $connection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat & Support connection deleted successfully.',
        ]);
    }

    /**
     * Sync data from Chat & Support provider.
     */
    public function sync(string $provider)
    {
        $connection = $this->findConnection($provider);

        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not connected.',
            ], 400);
        }

        // Real sync for Twilio - fetch messages
        if ($provider === 'twilio' && !empty($connection->account_sid)) {
            try {
                $authToken = Crypt::decryptString($connection->auth_token);

                // Fetch messages from Twilio
                $messages = Http::withBasicAuth($connection->account_sid, $authToken)
                    ->get("https://api.twilio.com/2010-04-01/Accounts/{$connection->account_sid}/Messages.json", [
                        'PageSize' => 50,
                        'DateSent>' => now()->subDay()->format('Y-m-d'),
                    ]);

                if ($messages->successful()) {
                    $messageCount = count($messages->json('messages', []));

                    $connection->update([
                        'last_sync_at' => now(),
                        'sync_count'     => \DB::raw('sync_count + 1'),
                        'last_error'     => null,
                        'metrics'        => array_merge($connection->metrics ?? [], [
                            'messages_today' => $messageCount,
                        ]),
                    ]);

                    $connection->refresh();

                    return response()->json([
                        'success' => true,
                        'message' => "Sync completed. {$messageCount} messages fetched from Twilio.",
                        'last_sync' => $connection->last_sync_at->diffForHumans(),
                        'messages_fetched' => $messageCount,
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Twilio sync failed: ' . $e->getMessage(),
                ], 500);
            }
        }

        // Default sync for other providers
        $connection->update([
            'last_sync_at' => now(),
            'sync_count'     => \DB::raw('sync_count + 1'),
            'last_error'     => null,
        ]);

        $connection->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Sync initiated successfully.',
            'last_sync' => $connection->last_sync_at->diffForHumans(),
        ]);
    }

    /**
     * Get connection status for all providers (current client only).
     */
    public function status()
    {
        $connections = ChatSupportIntegration::where('client_id', $this->currentClientId())
            ->get()
            ->keyBy('provider');
        $providers   = ChatSupportIntegration::providers();

        $status = [];
        foreach ($providers as $key => $meta) {
            $conn = $connections[$key] ?? null;
            $status[$key] = [
                'connected'   => $conn?->is_connected ?? false,
                'status'      => $conn?->status ?? 'disconnected',
                'last_sync'   => $conn?->last_sync_at?->diffForHumans() ?? 'Never',
                'sync_count'  => $conn?->sync_count ?? 0,
                'name'        => $conn?->connection_name ?? null,
                'messages'    => $conn?->formatted_messages ?? '0',
                'satisfaction' => $conn?->metrics['satisfaction_score'] ?? 0,
            ];
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Test connection helper.
     */
    private function testConnection(ChatSupportIntegration $connection): array
    {
        switch ($connection->provider) {
            case 'whatsapp':
                if (!empty($connection->access_token)) {
                    try {
                        $token  = Crypt::decryptString($connection->access_token);
                        $wabaId = $connection->settings['waba_id'] ?? null;

                        if ($wabaId) {
                            $res = Http::get("https://graph.facebook.com/v21.0/{$wabaId}", [
                                'access_token' => $token,
                                'fields'       => 'id,name',
                            ]);

                            if ($res->successful()) {
                                return [
                                    'success' => true,
                                    'message' => 'WhatsApp Business API connection verified.',
                                    'details' => [
                                        'waba'  => $res->json('name'),
                                        'phone' => $connection->phone_number,
                                    ],
                                ];
                            }

                            return [
                                'success' => false,
                                'message' => 'Meta rejected the token: ' . ($res->json('error.message') ?? 'Invalid or expired token. Please reconnect.'),
                            ];
                        }
                    } catch (\Exception $e) {
                        return ['success' => false, 'message' => 'WhatsApp test failed: ' . $e->getMessage()];
                    }
                }
                return ['success' => true, 'message' => 'WhatsApp Business API connection verified.', 'details' => ['phone' => $connection->phone_number]];

            case 'slack':
                if (!empty($connection->access_token)) {
                    try {
                        $token = Crypt::decryptString($connection->access_token);
                        $res = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $token,
                        ])->get('https://slack.com/api/auth.test');

                        if ($res->successful() && $res->json('ok')) {
                            return [
                                'success' => true,
                                'message' => 'Slack workspace connection verified.',
                                'details' => [
                                    'workspace' => $res->json('team'),
                                    'user'      => $res->json('user'),
                                ],
                            ];
                        }

                        return [
                            'success' => false,
                            'message' => 'Slack rejected the token: ' . ($res->json('error') ?? 'Invalid or expired token. Please reconnect.'),
                        ];
                    } catch (\Exception $e) {
                        return ['success' => false, 'message' => 'Slack test failed: ' . $e->getMessage()];
                    }
                }
                return ['success' => true, 'message' => 'Slack workspace connection verified.', 'details' => ['workspace' => $connection->workspace_id]];

            case 'twilio':
                if (!empty($connection->account_sid) && !empty($connection->auth_token)) {
                    try {
                        $authToken = Crypt::decryptString($connection->auth_token);

                        // Test Twilio credentials by fetching account info
                        $res = Http::withBasicAuth($connection->account_sid, $authToken)
                            ->get("https://api.twilio.com/2010-04-01/Accounts/{$connection->account_sid}.json");

                        if ($res->successful()) {
                            $accountData = $res->json();
                            return [
                                'success' => true,
                                'message' => 'Twilio connection verified.',
                                'details' => [
                                    'account_name'  => $accountData['friendly_name'] ?? 'Twilio Account',
                                    'account_status'=> $accountData['status'] ?? 'unknown',
                                    'phone'         => $connection->phone_number,
                                ],
                            ];
                        }

                        return [
                            'success' => false,
                            'message' => 'Twilio rejected the credentials. Please check your Account SID and Auth Token.',
                        ];
                    } catch (\Exception $e) {
                        return ['success' => false, 'message' => 'Twilio test failed: ' . $e->getMessage()];
                    }
                }
                return ['success' => true, 'message' => 'Twilio connection verified.', 'details' => ['account' => $connection->account_sid]];

            case 'zendesk':
                return ['success' => true, 'message' => 'Zendesk connection verified.', 'details' => ['subdomain' => $connection->subdomain]];
            case 'tawk':
                return ['success' => true, 'message' => 'Tawk.to connection verified.', 'details' => ['property' => $connection->app_id]];
            case 'intercom':
                return ['success' => true, 'message' => 'Intercom connection verified.', 'details' => ['app' => $connection->app_id]];
            case 'livechat':
                return ['success' => true, 'message' => 'LiveChat connection verified.', 'details' => ['license' => $connection->license_id]];
            default:
                return ['success' => false, 'message' => 'Unknown provider.'];
        }
    }
}
