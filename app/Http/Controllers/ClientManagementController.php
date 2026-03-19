<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientManagementController extends Controller
{
    public function index()
    {
        $clients = Client::withCount('dataSources')->latest()->paginate(20);
        return view('admin.clients.index', compact('clients'));
    }

    public function pending()
    {
        $clients = Client::where('status', 'pending')->latest()->get();
        return view('admin.clients.pending', compact('clients'));
    }

    public function show(Client $client)
    {
        $client->load(['dataSources', 'metrics' => function ($q) {
            $q->latest()->take(10);
        }]);
        return view('admin.clients.show', compact('client'));
    }

    public function approve(Client $client)
    {
        $client->update(['status' => 'active']);
        return back()->with('success', 'Client approved successfully.');
    }

    public function suspend(Client $client)
    {
        $client->update(['status' => 'suspended']);
        return back()->with('success', 'Client suspended.');
    }

    public function activate(Client $client)
    {
        $client->update(['status' => 'active']);
        return back()->with('success', 'Client activated.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted.');
    }
}