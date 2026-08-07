<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Customer;
use App\Models\RetentionCampaign;
use App\Services\ChurnPredictionService;
use Illuminate\Http\Request;

class RetentionController extends Controller
{
    protected $churnService;

    public function __construct(ChurnPredictionService $churnService)
    {
        $this->churnService = $churnService;
    }

    /**
     * Display the retention dashboard.
     * Route: client.retention.dashboard
     */
    public function dashboard(Request $request)
    {
        $client = $request->user(); // FIXED: auth:client guard means user IS the client

        // Get high risk customers for this client only
        $highRiskCustomers = Customer::where('client_id', $client->id)
            ->where('is_active', true)
            ->get();

        $campaigns = RetentionCampaign::where('client_id', $client->id)
            ->latest()
            ->get();

        return view('retention.dashboard', [
            'client' => $client,
            'highRisk' => $highRiskCustomers,
            'campaigns' => $campaigns
        ]);
    }

    /**
     * Predict churn for a specific customer.
     */
    public function predictChurn($customerId)
    {
        $prediction = $this->churnService->predict($customerId);
        return response()->json($prediction);
    }

    /**
     * Create a new retention campaign.
     */
    public function createCampaign(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|in:score_threshold,behavioral_pattern',
            'threshold_score' => 'nullable|integer|min:1|max:100',
            'email_template_id' => 'nullable|exists:email_templates,id',
            'sms_template' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $campaign = RetentionCampaign::create([
            'client_id' => $request->user()->id, // FIXED
            'name' => $validated['name'],
            'trigger_type' => $validated['trigger_type'],
            'threshold_score' => $validated['threshold_score'] ?? null,
            'email_template_id' => $validated['email_template_id'] ?? null,
            'sms_template' => $validated['sms_template'] ?? '',
            'is_active' => $validated['is_active'] ?? true
        ]);

        return redirect()->back()->with('success', 'Retention campaign created successfully!');
    }

    /**
     * Execute a retention campaign.
     */
    public function sendCampaign($campaignId)
    {
        $campaign = RetentionCampaign::findOrFail($campaignId);
        $this->churnService->executeCampaign($campaign);

        $campaign->update(['last_triggered_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Campaign executed successfully']);
    }
}
