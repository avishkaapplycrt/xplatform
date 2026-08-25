<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\LaravelSite;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class LaravelSiteManagementController extends Controller
{
    public function create()
    {
        $client = auth()->guard('client')->user();
        return view('client.auth.laravel-create', compact('client'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:100',
            'site_url' => 'required|url',
            'api_token' => 'required|string',
            'sync_frequency' => 'required|in:hourly,6hours,daily,weekly',
        ]);

        $siteId = 'laravel_' . Str::random(8);

        $site = LaravelSite::create([
            'client_id' => auth()->guard('client')->id(),
            'site_name' => $validated['site_name'],
            'site_url' => rtrim($validated['site_url'], '/'),
            'site_id' => $siteId,
            'api_type' => 'rest_poll',
            'auth_credentials' => ['api_token' => $validated['api_token']],
            'sync_frequency' => $validated['sync_frequency'],
            'is_active' => true,
        ]);

        return redirect()->route('client.analytics.laravel.index')
            ->with('success', 'Laravel site "' . $site->site_name . '" connected successfully!');
    }

    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'api_token' => 'required|string',
        ]);

        try {
            $response = Http::withToken($validated['api_token'])
                ->timeout(10)
                ->get(rtrim($validated['url'], '/') . '/api/analytics/ping');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
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

    public function index()
    {
        $sites = LaravelSite::where('client_id', auth()->guard('client')->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.auth.laravel-index', compact('sites'));
    }

    public function show(LaravelSite $site)
    {
        return view('client.auth.laravel-show', compact('site'));
    }

    public function destroy(LaravelSite $site)
    {
        $site->delete();
        return redirect()->route('client.analytics.laravel.index')
            ->with('success', 'Site deleted successfully');
    }
}