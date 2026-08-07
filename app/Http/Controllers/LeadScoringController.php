<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadScore;
use App\Models\LeadQualification;
use App\Services\LeadScoringService;
use Illuminate\Http\Request;

class LeadScoringController extends Controller
{
    protected $scoringService;

    public function __construct(LeadScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Lead scoring dashboard
     */
    public function dashboard(Request $request)
    {
        $client = $request->user();

        $stats = [
            'total_leads' => Lead::where('client_id', $client->id)->count(),
            'hot_leads' => Lead::where('client_id', $client->id)->where('qualification_status', 'hot')->count(),
            'warm_leads' => Lead::where('client_id', $client->id)->where('qualification_status', 'warm')->count(),
            'cold_leads' => Lead::where('client_id', $client->id)->where('qualification_status', 'cold')->count(),
            'converted_leads' => Lead::where('client_id', $client->id)->where('status', 'converted')->count(),
            'avg_score' => round(LeadScore::where('client_id', $client->id)->avg('total_score') ?? 0, 1),
        ];

        $recentLeads = Lead::where('client_id', $client->id)
            ->with('latestScore')
            ->latest()
            ->take(10)
            ->get();

        $hotLeads = Lead::where('client_id', $client->id)
            ->where('qualification_status', 'hot')
            ->where('status', '!=', 'converted')
            ->with('latestScore')
            ->latest()
            ->take(10)
            ->get();

        $scoreDistribution = $this->scoringService->getScoreDistribution($client->id);

        return view('leadscoring.dashboard', [
            'stats' => $stats,
            'recentLeads' => $recentLeads,
            'hotLeads' => $hotLeads,
            'scoreDistribution' => $scoreDistribution,
        ]);
    }

    /**
     * List all leads with filtering
     */
    public function index(Request $request)
    {
        $client = $request->user();

        $query = Lead::where('client_id', $client->id)
            ->with('latestScore')
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('qualification')) {
            $query->where('qualification_status', $request->qualification);
        }
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('company', 'like', '%' . $request->search . '%');
            });
        }

        $leads = $query->paginate(25);
        $sources = Lead::where('client_id', $client->id)->distinct()->pluck('source');

        return view('leadscoring.index', [
            'leads' => $leads,
            'sources' => $sources,
        ]);
    }

    /**
     * Show lead detail with score history
     */
    public function show(Request $request, $leadId)
    {
        $client = $request->user();

        $lead = Lead::where('client_id', $client->id)
            ->with(['scores', 'activities'])
            ->findOrFail($leadId);

        $scoreHistory = LeadScore::where('lead_id', $leadId)
            ->orderBy('created_at')
            ->get();

        $conversionProbability = $this->scoringService->calculateConversionProbability($lead);

        return view('leadscoring.show', [
            'lead' => $lead,
            'scoreHistory' => $scoreHistory,
            'conversionProbability' => $conversionProbability,
        ]);
    }

    /**
     * Create a new lead
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'source' => 'required|string|max:100',
            'source_detail' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        $lead = Lead::create([
            'client_id' => $request->user()->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'source' => $validated['source'],
            'source_detail' => $validated['source_detail'] ?? null,
            'status' => 'new',
            'qualification_status' => 'unscored',
            'metadata' => $validated['metadata'] ?? [],
        ]);

        // Auto-score the new lead
        $this->scoringService->scoreLead($lead->id);

        return redirect()->route('client.leadscoring.show', $lead->id)
            ->with('success', 'Lead created and scored successfully!');
    }

    /**
     * Re-score a lead manually
     */
    public function rescore(Request $request, $leadId)
    {
        $client = $request->user();

        $lead = Lead::where('client_id', $client->id)->findOrFail($leadId);
        $score = $this->scoringService->scoreLead($lead->id);

        return response()->json([
            'success' => true,
            'score' => $score->total_score,
            'qualification' => $score->qualification_status,
            'conversion_probability' => $score->conversion_probability,
        ]);
    }

    /**
     * Bulk score all unscored leads
     */
    public function bulkScore(Request $request)
    {
        $client = $request->user();
        $count = $this->scoringService->bulkScoreLeads($client->id);

        return redirect()->back()->with('success', "Scored {$count} leads successfully!");
    }

    /**
     * Update lead status
     */
    public function updateStatus(Request $request, $leadId)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,contacted,qualified,converted,lost,nurturing',
        ]);

        $client = $request->user();
        $lead = Lead::where('client_id', $client->id)->findOrFail($leadId);
        $lead->update(['status' => $validated['status']]);

        return response()->json(['success' => true, 'status' => $validated['status']]);
    }

    /**
     * Route hot lead to sales team
     */
    public function routeToSales(Request $request, $leadId)
    {
        $client = $request->user();
        $lead = Lead::where('client_id', $client->id)->findOrFail($leadId);

        $this->scoringService->routeToSales($lead);

        return response()->json([
            'success' => true,
            'message' => 'Lead routed to sales team successfully!',
        ]);
    }

    /**
     * Lead scoring settings
     */
    public function settings(Request $request)
    {
        $client = $request->user();

        $settings = [
            'hot_threshold' => 75,
            'warm_threshold' => 50,
            'auto_route_hot' => true,
            'route_channel' => 'slack',
            'behavior_weight' => 40,
            'demographic_weight' => 30,
            'engagement_weight' => 30,
        ];

        return view('leadscoring.settings', [
            'settings' => $settings,
        ]);
    }
}
