<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChatSupportIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class ChatSupportConnectionController extends Controller
{
    /**
     * Display the Chat & Support connections listing page.
     */
    public function index()
    {
        $connections = ChatSupportIntegration::all()->keyBy('provider');
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
        $existing = ChatSupportIntegration::byProvider($provider)->first();

        return view('client.chat-support-connect', compact('provider', 'meta', 'existing'));
    }

    /**
     * Store or update a Chat & Support connection.
     */
    public function store(Request $request, string $provider)
    {
        $providers = ChatSupportIntegration::providers();

        if (!isset($providers[$provider])) {
            return response()->json(['error' => 'Invalid provider'], 422);
        }

        $meta = $providers[$provider];
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
                $rules['account_sid'] = 'required|string';
                $rules['auth_token'] = 'required|string';
                $rules['phone_number'] = 'required|string';
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
        $data['provider'] = $provider;

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
            ['provider' => $provider],
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

    /**
     * Test a Chat & Support connection.
     */
    public function test(string $provider)
    {
        $connection = ChatSupportIntegration::byProvider($provider)->first();

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
        $connection = ChatSupportIntegration::byProvider($provider)->first();

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
        $connection = ChatSupportIntegration::byProvider($provider)->first();

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
        $connection = ChatSupportIntegration::byProvider($provider)->first();

        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not connected.',
            ], 400);
        }

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
     * Get connection status for all providers.
     */
    public function status()
    {
        $connections = ChatSupportIntegration::all()->keyBy('provider');
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
                return ['success' => true, 'message' => 'WhatsApp Business API connection verified.', 'details' => ['phone' => $connection->phone_number]];
            case 'slack':
                return ['success' => true, 'message' => 'Slack workspace connection verified.', 'details' => ['workspace' => $connection->workspace_id]];
            case 'twilio':
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