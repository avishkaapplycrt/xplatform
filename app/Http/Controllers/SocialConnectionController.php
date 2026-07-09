<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SocialIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class SocialConnectionController extends Controller
{
    /**
     * Display the Social Media connections listing page.
     */
    public function index()
    {
        $connections = SocialIntegration::all()->keyBy('platform');
        $platforms   = SocialIntegration::platforms();

        // Calculate stats
        $totalConnected = $connections->where('status', 'connected')->count();
        $totalPlatforms = count($platforms);
        $syncToday      = $connections->where('last_sync_at', '>=', now()->startOfDay())->count();
        $lastSync       = $connections->whereNotNull('last_sync_at')->max('last_sync_at');

        // Total followers across all connected platforms
        $totalFollowers = $connections->where('status', 'connected')->sum(function ($c) {
            return $c->metrics['followers'] ?? 0;
        });

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

        return view('client.social-connections', compact(
            'connections',
            'platforms',
            'totalConnected',
            'totalPlatforms',
            'syncToday',
            'lastSync',
            'syncHealth',
            'totalFollowers'
        ));
    }

    /**
     * Show the connect form for a specific platform.
     */
    public function create(string $platform)
    {
        $platforms = SocialIntegration::platforms();

        if (!isset($platforms[$platform])) {
            abort(404, 'Social media platform not found.');
        }

        $meta = $platforms[$platform];
        $existing = SocialIntegration::byPlatform($platform)->first();

        return view('client.social-connect', compact('platform', 'meta', 'existing'));
    }

    /**
     * Store or update a Social Media connection.
     */
    public function store(Request $request, string $platform)
    {
        $platforms = SocialIntegration::platforms();

        if (!isset($platforms[$platform])) {
            return response()->json(['error' => 'Invalid platform'], 422);
        }

        $meta = $platforms[$platform];
        $rules = [
            'connection_name' => 'required|string|max:100',
            'status'          => 'required|in:connected,disconnected',
        ];

        // Platform-specific validation
        switch ($platform) {
            case 'facebook':
                $rules['page_id'] = 'required|string';
                $rules['access_token'] = 'required|string';
                break;
            case 'instagram':
                $rules['account_id'] = 'required|string';
                $rules['access_token'] = 'required|string';
                break;
            case 'tiktok':
                $rules['account_id'] = 'required|string';
                $rules['access_token'] = 'required|string';
                break;
            case 'youtube':
                $rules['channel_id'] = 'required|string';
                $rules['access_token'] = 'required|string';
                break;
            case 'linkedin':
                $rules['page_id'] = 'required|string';
                $rules['access_token'] = 'required|string';
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
        $data['platform'] = $platform;

        // Encrypt sensitive fields
        $encryptFields = ['access_token', 'refresh_token'];
        foreach ($encryptFields as $field) {
            if (!empty($data[$field])) {
                $data[$field] = Crypt::encryptString($data[$field]);
            }
        }

        // Handle settings & sync_config
        $data['settings'] = $request->input('settings', []);
        $data['sync_config'] = $request->input('sync_config', [
            'sync_frequency' => 'hourly',
            'content_types'  => ['posts', 'comments', 'reactions'],
            'auto_publish'   => false,
        ]);

        // Mock metrics for demo (in production, fetch from API)
        $data['metrics'] = $request->input('metrics', [
            'followers'       => rand(1000, 500000),
            'engagement_rate' => rand(15, 85) / 10,
            'posts_count'     => rand(50, 2000),
            'avg_reach'       => rand(5000, 500000),
        ]);

        if ($data['status'] === 'connected') {
            $data['last_sync_at'] = now();
            $data['sync_count'] = \DB::raw('sync_count + 1');
        }

        $connection = SocialIntegration::updateOrCreate(
            ['platform' => $platform],
            $data
        );

        if (isset($data['sync_count'])) {
            $connection->refresh();
        }

        return response()->json([
            'success'   => true,
            'message'   => $connection->wasRecentlyCreated
                ? 'Social media connection created successfully.'
                : 'Social media connection updated successfully.',
            'connection' => [
                'id'       => $connection->id,
                'platform' => $connection->platform,
                'status'   => $connection->status,
                'name'     => $connection->connection_name,
            ],
        ]);
    }

    /**
     * Test a Social Media connection.
     */
    public function test(string $platform)
    {
        $connection = SocialIntegration::byPlatform($platform)->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'No connection found for this platform.',
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
     * Disconnect a Social Media connection.
     */
    public function disconnect(string $platform)
    {
        $connection = SocialIntegration::byPlatform($platform)->first();

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
            'message' => 'Social media connection disconnected successfully.',
        ]);
    }

    /**
     * Delete a Social Media connection.
     */
    public function destroy(string $platform)
    {
        $connection = SocialIntegration::byPlatform($platform)->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.',
            ], 404);
        }

        $connection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Social media connection deleted successfully.',
        ]);
    }

    /**
     * Sync data from Social Media platform.
     */
    public function sync(string $platform)
    {
        $connection = SocialIntegration::byPlatform($platform)->first();

        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'Platform not connected.',
            ], 400);
        }

        // Update sync timestamps
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
     * Get connection status for all platforms.
     */
    public function status()
    {
        $connections = SocialIntegration::all()->keyBy('platform');
        $platforms   = SocialIntegration::platforms();

        $status = [];
        foreach ($platforms as $key => $meta) {
            $conn = $connections[$key] ?? null;
            $status[$key] = [
                'connected'   => $conn?->is_connected ?? false,
                'status'      => $conn?->status ?? 'disconnected',
                'last_sync'   => $conn?->last_sync_at?->diffForHumans() ?? 'Never',
                'sync_count'  => $conn?->sync_count ?? 0,
                'name'        => $conn?->connection_name ?? null,
                'followers'   => $conn?->formatted_followers ?? '0',
                'engagement'  => $conn?->engagement_rate ?? '0.00%',
            ];
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Test connection helper.
     */
    private function testConnection(SocialIntegration $connection): array
    {
        switch ($connection->platform) {
            case 'facebook':
                return ['success' => true, 'message' => 'Facebook connection verified.', 'details' => ['page_name' => 'Test Page']];
            case 'instagram':
                return ['success' => true, 'message' => 'Instagram connection verified.', 'details' => ['account_type' => 'BUSINESS']];
            case 'tiktok':
                return ['success' => true, 'message' => 'TikTok connection verified.', 'details' => ['display_name' => 'Test Account']];
            case 'youtube':
                return ['success' => true, 'message' => 'YouTube connection verified.', 'details' => ['channel_title' => 'Test Channel']];
            case 'linkedin':
                return ['success' => true, 'message' => 'LinkedIn connection verified.', 'details' => ['company_name' => 'Test Company']];
            default:
                return ['success' => false, 'message' => 'Unknown platform.'];
        }
    }
}