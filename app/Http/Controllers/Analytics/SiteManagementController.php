<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\WordPressSite;
use App\Models\Client; // or whatever your client model is
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class SiteManagementController extends Controller
{
    /**
     * Show form to create new WordPress site (REST polling method)
     */
    public function create()
    {
        // For client users, only show their own client record
        // For admin users, show all clients
        if (auth()->guard('client')->check()) {
            $clients = collect([auth()->guard('client')->user()]);
        } else {
            $clients = \App\Models\Client::all();
        }
        
        return view('client.auth.create-rest', compact('clients'));
    }

    /**
     * Store new WordPress site
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'site_name' => 'required|string|max:100',
            'site_url' => 'required|url',
            'api_type' => 'required|in:rest_poll,webhook,db_direct',
            'auth_type' => 'required|in:application_password,basic,bearer,jwt',
            'credentials' => 'required|array',
            'credentials.username' => 'required|string',
            'credentials.password' => 'required|string',
            'has_woocommerce' => 'boolean',
            'sync_frequency' => 'required|in:hourly,6hours,daily,weekly',
            'connection_config' => 'nullable|array',
        ]);

        // Generate unique site ID
        $siteId = 'site_' . \Illuminate\Support\Str::random(8);

        // Build credentials array
        $credentials = [
            'username' => $validated['credentials']['username'],
            'password' => $validated['credentials']['password'],
        ];

        // Add WooCommerce credentials if provided
        if (!empty($validated['credentials']['wc_consumer_key'])) {
            $credentials['wc_consumer_key'] = $validated['credentials']['wc_consumer_key'];
        }
        if (!empty($validated['credentials']['wc_consumer_secret'])) {
            $credentials['wc_consumer_secret'] = $validated['credentials']['wc_consumer_secret'];
        }

        // Build connection config
        $connectionConfig = array_merge(
            $validated['connection_config'] ?? [],
            ['has_woocommerce' => $request->has('has_woocommerce')]
        );

        $site = WordPressSite::create([
            'client_id' => $validated['client_id'],
            'site_name' => $validated['site_name'],
            'site_url' => rtrim($validated['site_url'], '/'),
            'site_id' => $siteId,
            'api_type' => $validated['api_type'],
            'auth_type' => $validated['auth_type'],
            'auth_credentials' => $credentials, // Pass as array - mutator will encrypt
            'connection_config' => $connectionConfig,
            'sync_frequency' => $validated['sync_frequency'],
            'is_active' => true,
        ]);

        return redirect()->route('client.analytics.sites.index')
            ->with('success', 'Site "' . $site->site_name . '" added successfully! Site ID: ' . $siteId);
    }

    /**
     * Test connection before saving
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $response = Http::withBasicAuth($validated['username'], $validated['password'])
                ->timeout(10)
                ->get(rtrim($validated['url'], '/') . '/wp-json/wp/v2');

            if ($response->successful()) {
                $data = $response->json();
                $namespaces = $data['namespaces'] ?? [];
                
                return response()->json([
                    'success' => true,
                    'wp_version' => $data['authentication']['application-passwords']['endpoints']['authorization'] ?? 'unknown',
                    'has_woocommerce' => in_array('wc/v3', $namespaces),
                    'has_jetpack' => in_array('jetpack/v4', $namespaces),
                    'namespaces' => $namespaces,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'HTTP ' . $response->status() . ': ' . $response->body()
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all sites
     */
    public function index()
    {
        $sites = WordPressSite::with('client')
            ->where('client_id', auth()->guard('client')->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.auth.index', compact('sites'));
    }

    /**
     * Show single site details
     */
    public function show(WordPressSite $site)
    {
        return view('client.auth.show', compact('site')); // Update this path too if needed
    }

    /**
     * Regenerate API key
     */
    public function regenerateKey(WordPressSite $site)
    {
        $apiKey = Str::random(64);
        $site->update(['auth_credentials' => Crypt::encryptString(json_encode([
            'api_key' => $apiKey
        ]))]);

        return back()
            ->with('success', 'API key regenerated successfully!')
            ->with('api_key', $apiKey);
    }

    /**
     * Toggle site active status
     */
    public function toggleStatus(WordPressSite $site)
    {
        $site->update(['is_active' => !$site->is_active]);
        
        return back()->with('success', 
            $site->is_active ? 'Site activated' : 'Site deactivated'
        );
    }

    /**
     * Delete site
     */
    public function destroy(WordPressSite $site)
    {
        $site->delete();
        return redirect()->route('analytics.sites.index')
            ->with('success', 'Site deleted successfully');
    }
}