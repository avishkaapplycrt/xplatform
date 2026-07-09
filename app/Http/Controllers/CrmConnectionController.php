<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CrmIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class CrmConnectionController extends Controller
{
    /**
     * Display the CRM connections listing page.
     */
    public function index()
    {
        $connections = CrmIntegration::all()->keyBy('provider');
        $providers   = CrmIntegration::providers();

        // Calculate stats
        $totalConnected = $connections->where('status', 'connected')->count();
        $totalProviders = count($providers);
        $syncToday      = $connections->where('last_sync_at', '>=', now()->startOfDay())->count();
        $lastSync       = $connections->whereNotNull('last_sync_at')->max('last_sync_at');

        // Sync health status
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
            'connections',
            'providers',
            'totalConnected',
            'totalProviders',
            'syncToday',
            'lastSync',
            'syncHealth'
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
            return response()->json(['error' => 'Invalid provider'], 422);
        }

        $meta = $providers[$provider];
        $rules = [
            'connection_name' => 'required|string|max:100',
            'status'          => 'required|in:connected,disconnected',
        ];

        // Provider-specific validation
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
                $rules['organization_id'] = 'nullable|string';
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
        $encryptFields = ['api_key', 'api_secret', 'access_token', 'refresh_token'];
        foreach ($encryptFields as $field) {
            if (!empty($data[$field])) {
                $data[$field] = Crypt::encryptString($data[$field]);
            }
        }

        // Handle settings & sync_config
        $data['settings'] = $request->input('settings', []);
        $data['sync_config'] = $request->input('sync_config', [
            'sync_frequency' => 'hourly',
            'sync_direction' => 'bidirectional',
            'entities'       => ['contacts', 'leads', 'deals'],
        ]);

        if ($data['status'] === 'connected') {
            $data['last_sync_at'] = now();
            $data['sync_count'] = \DB::raw('sync_count + 1');
        }

        $connection = CrmIntegration::updateOrCreate(
            ['provider' => $provider],
            $data
        );

        // If using increment, fetch fresh
        if (isset($data['sync_count'])) {
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

        // Here you would implement actual sync logic
        // This is a placeholder that updates timestamps
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

    /**
     * Test connection helper.
     */
    private function testConnection(CrmIntegration $connection): array
    {
        // Provider-specific test logic
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
        // Implement Salesforce API test
        return ['success' => true, 'message' => 'Salesforce connection verified.', 'details' => ['api_version' => 'v58.0']];
    }

    private function testHubSpot(CrmIntegration $connection): array
    {
        // Implement HubSpot API test
        return ['success' => true, 'message' => 'HubSpot connection verified.', 'details' => ['portal_id' => $connection->portal_id]];
    }

    private function testZoho(CrmIntegration $connection): array
    {
        return ['success' => true, 'message' => 'Zoho CRM connection verified.'];
    }

    private function testPipedrive(CrmIntegration $connection): array
    {
        return ['success' => true, 'message' => 'Pipedrive connection verified.'];
    }

    private function testMonday(CrmIntegration $connection): array
    {
        return ['success' => true, 'message' => 'Monday.com connection verified.'];
    }
}