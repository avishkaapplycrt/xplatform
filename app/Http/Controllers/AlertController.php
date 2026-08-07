<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user(); // FIXED: auth:client guard means user IS the client

        return view('alerts.dashboard', [
            'criticalCount' => 0,
            'highCount' => 0,
            'acknowledgedCount' => 0,
            'rules' => [],
            'alerts' => collect([])
        ]);
    }

    public function createRule(Request $request)
    {
        return redirect()->back();
    }

    public function acknowledgeAlert($alert)
    {
        return response()->json(['success' => true]);
    }

    public function toggleRule($rule, Request $request)
    {
        return response()->json(['success' => true]);
    }
}
