<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Customer;
use App\Models\OnboardingWorkflow;
use App\Models\CustomerHealthScore;
use App\Models\AutomatedCheckin;
use App\Models\NpsSurvey;
use App\Models\NpsResponse;
use App\Services\CustomerSuccessService;
use Illuminate\Http\Request;

class CustomerSuccessController extends Controller
{
    protected $successService;

    public function __construct(CustomerSuccessService $successService)
    {
        $this->successService = $successService;
    }

    // ==================== ONBOARDING ====================

    public function onboardingDashboard(Request $request)
    {
        $client = $request->user(); // FIXED: auth:client guard means user IS the client

        $workflows = OnboardingWorkflow::where('client_id', $client->id)
            ->withCount('customers')
            ->get();

        $activeOnboardings = Customer::where('client_id', $client->id)
            ->where('onboarding_status', 'in_progress')
            ->with('onboardingWorkflow')
            ->paginate(20);

        return view('customersuccess.onboarding', [
            'workflows' => $workflows,
            'activeOnboardings' => $activeOnboardings,
            'completionRate' => $this->successService->getOnboardingCompletionRate($client->id)
        ]);
    }

    public function createWorkflow(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'steps' => 'required|array|min:1',
            'steps.*.title' => 'required|string',
            'steps.*.description' => 'nullable|string',
            'steps.*.delay_days' => 'required|integer|min:0',
            'steps.*.action_type' => 'required|in:email,task,webinar,resource',
            'steps.*.action_data' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $workflow = OnboardingWorkflow::create([
            'client_id' => $request->user()->id, // FIXED
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'steps' => $validated['steps'],
            'is_active' => $validated['is_active'] ?? true
        ]);

        return redirect()->back()->with('success', 'Onboarding workflow created!');
    }

    public function assignWorkflow(Request $request, $customerId)
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:onboarding_workflows,id'
        ]);

        $customer = Customer::findOrFail($customerId);
        $customer->update([
            'onboarding_workflow_id' => $validated['workflow_id'],
            'onboarding_status' => 'in_progress',
            'onboarding_started_at' => now()
        ]);

        $this->successService->triggerOnboardingSteps($customer);

        return response()->json(['success' => true]);
    }

    // ==================== HEALTH SCORES ====================

    public function healthDashboard(Request $request)
    {
        $client = $request->user(); // FIXED

        $scores = CustomerHealthScore::where('client_id', $client->id)
            ->with('customer')
            ->latest()
            ->paginate(20);

        $distribution = $this->successService->getHealthScoreDistribution($client->id);

        return view('customersuccess.health', [
            'scores' => $scores,
            'distribution' => $distribution,
            'atRiskCount' => CustomerHealthScore::where('client_id', $client->id)
                ->where('score', '<', 40)->count(),
            'healthyCount' => CustomerHealthScore::where('client_id', $client->id)
                ->where('score', '>=', 70)->count()
        ]);
    }

    public function calculateHealthScore(Request $request, $customerId)
    {
        $score = $this->successService->calculateHealthScore($customerId);

        return response()->json([
            'success' => true,
            'score' => $score->score,
            'breakdown' => $score->breakdown,
            'recommendations' => $score->recommendations
        ]);
    }

    public function recalculateAllScores(Request $request)
    {
        $client = $request->user(); // FIXED
        $count = $this->successService->recalculateAllHealthScores($client->id);

        return redirect()->back()->with('success', "Recalculated health scores for {$count} customers.");
    }

    // ==================== AUTOMATED CHECK-INS ====================

    public function checkinsDashboard(Request $request)
    {
        $client = $request->user(); // FIXED

        $checkins = AutomatedCheckin::where('client_id', $client->id)
            ->with(['customer'])
            ->latest()
            ->paginate(20);

        $templates = $this->successService->getCheckinTemplates();

        return view('customersuccess.checkins', [
            'checkins' => $checkins,
            'templates' => $templates,
            'upcomingCount' => AutomatedCheckin::where('client_id', $client->id)
                ->where('scheduled_at', '>', now())
                ->where('status', 'scheduled')
                ->count()
        ]);
    }

    public function scheduleCheckin(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'template' => 'required|string',
            'scheduled_at' => 'required|date|after:now',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'channel' => 'required|in:email,sms,in_app'
        ]);

        $checkin = AutomatedCheckin::create([
            'client_id' => $request->user()->id, // FIXED
            'customer_id' => $validated['customer_id'],
            'template' => $validated['template'],
            'scheduled_at' => $validated['scheduled_at'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'channel' => $validated['channel'],
            'status' => 'scheduled'
        ]);

        return redirect()->back()->with('success', 'Check-in scheduled!');
    }

    public function sendCheckinNow($checkinId)
    {
        $checkin = AutomatedCheckin::findOrFail($checkinId);
        $this->successService->executeCheckin($checkin);

        return response()->json(['success' => true]);
    }

    // ==================== NPS SURVEYS ====================

    public function npsDashboard(Request $request)
    {
        $client = $request->user(); // FIXED

        $surveys = NpsSurvey::where('client_id', $client->id)
            ->withCount('responses')
            ->latest()
            ->paginate(10);

        $overallNps = $this->successService->calculateOverallNps($client->id);

        return view('customersuccess.nps', [
            'surveys' => $surveys,
            'overallNps' => $overallNps,
            'recentResponses' => NpsResponse::where('client_id', $client->id)
                ->with('customer')
                ->latest()
                ->take(10)
                ->get()
        ]);
    }

    public function createNpsSurvey(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'question' => 'required|string|max:500',
            'send_to' => 'required|in:all,segment,specific',
            'segment_id' => 'nullable|exists:customer_segments,id',
            'scheduled_at' => 'nullable|date|after:now',
            'is_active' => 'boolean'
        ]);

        $survey = NpsSurvey::create([
            'client_id' => $request->user()->id, // FIXED
            'name' => $validated['name'],
            'question' => $validated['question'],
            'send_to' => $validated['send_to'],
            'segment_id' => $validated['segment_id'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? now(),
            'is_active' => $validated['is_active'] ?? true
        ]);

        if (!$validated['scheduled_at']) {
            $this->successService->distributeNpsSurvey($survey);
        }

        return redirect()->back()->with('success', 'NPS survey created!');
    }

    public function submitNpsResponse(Request $request, $surveyId)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'score' => 'required|integer|min:0|max:10',
            'feedback' => 'nullable|string|max:2000'
        ]);

        $response = NpsResponse::create([
            'client_id' => $request->user()->id, // FIXED
            'survey_id' => $surveyId,
            'customer_id' => $validated['customer_id'],
            'score' => $validated['score'],
            'feedback' => $validated['feedback'] ?? '',
            'category' => $this->successService->categorizeNpsScore($validated['score'])
        ]);

        return response()->json(['success' => true, 'response' => $response]);
    }

    public function npsReport(Request $request, $surveyId)
    {
        $survey = NpsSurvey::with('responses.customer')->findOrFail($surveyId);
        $report = $this->successService->generateNpsReport($survey);

        return view('customersuccess.nps_report', [
            'survey' => $survey,
            'report' => $report
        ]);
    }
}
