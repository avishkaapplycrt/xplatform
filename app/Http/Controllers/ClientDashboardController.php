<?php

namespace App\Http\Controllers;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $client = auth('client')->user()->load(['industry', 'actions', 'selectedSources']);

        return view('client.dashboard', compact('client'));
    }
}
