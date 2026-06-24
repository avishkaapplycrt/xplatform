<?php

namespace App\Http\Controllers;

use App\Models\ClientDataSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class DataSourceController extends Controller
{
    public function index()
    {
        $sources = auth('client')->user()->dataSources()->latest()->get();
        return view('client.sources.index', compact('sources'));
    }

    public function create()
    {
        return view('client.sources.create');
    }

    public function store(Request $request)
    {
        $client = auth('client')->user();

        $validated = $request->validate([
            'connection_name' => 'required|string|max:255',
            'db_type'         => 'required|in:mysql,postgresql,mongodb,sqlserver,snowflake',
            'host'            => 'required|string|max:255',
            'port'            => 'required|integer',
            'database_name'   => 'required|string|max:255',
            'username'        => 'required|string|max:255',
            'password'        => 'nullable|string|max:255',
        ]);

        // Check limits
        $currentCount = $client->dataSources()->count();
        $maxAllowed   = ($client->limits['max_data_sources'] ?? null) ?? 3;

        if ($currentCount >= $maxAllowed) {
            return back()->with('error', "You have reached the maximum of {$maxAllowed} data sources.");
        }

        // Save directly without testing connection
        $source            = new ClientDataSource($validated);
        $source->client_id = $client->id;
        $source->status    = 'connected'; // 👈 set as connected directly
        $source->save();

        return redirect()->route('client.dashboard')  // 👈 redirect to dashboard
            ->with('success', 'Data source connected successfully!');
    }

    public function test(Request $request, ClientDataSource $source)
    {
        abort_if($source->client_id !== auth('client')->id(), 403);

        $success = $source->testConnection();

        return response()->json([
            'success' => $success,
            'status'  => $source->status,
            'error'   => $source->last_error,
        ]);
    }

    public function destroy(ClientDataSource $source)
    {
        abort_if($source->client_id !== auth('client')->id(), 403);
        $source->delete();

        return redirect()->route('client.sources.index')
            ->with('success', 'Data source removed.');
    }
}