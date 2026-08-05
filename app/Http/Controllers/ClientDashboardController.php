<?php

namespace App\Http\Controllers;

use App\Services\OnboardingChecklist;

class ClientDashboardController extends Controller
{
    public function index(OnboardingChecklist $checklist)
    {
        $client = auth('client')->user()->load(['industry', 'actions', 'selectedSources']);

        return view('client.dashboard', [
            'client'    => $client,
            'checklist' => $checklist->for($client),
        ]);
    }
}
