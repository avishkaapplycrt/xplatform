<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Customer;
use App\Models\OnboardingWorkflow;
use App\Models\CustomerHealthScore;
use App\Models\AutomatedCheckin;
use App\Models\NpsSurvey;
use App\Models\NpsResponse;
use App\Models\Transaction;
use App\Models\WebsiteEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerSuccessService
{
    // ==================== ONBOARDING ====================

    /**
     * Trigger onboarding steps for a new customer
     */
    public function triggerOnboardingSteps(Customer $customer)
    {
        $workflow = $customer->onboardingWorkflow;
        if (!$workflow || !$workflow->is_active) return;

        $steps = $workflow->steps ?? [];
        $startedAt = $customer->onboarding_started_at ?? now();

        foreach ($steps as $index => $step) {
            $scheduledAt = $startedAt->copy()->addDays($step['delay_days'] ?? 0);

            AutomatedCheckin::create([
                'client_id' => $customer->client_id,
                'customer_id' => $customer->id,
                'template' => 'onboarding_step_' . $index,
                'scheduled_at' => $scheduledAt,
                'subject' => $step['title'] ?? 'Welcome!',
                'message' => $step['description'] ?? '',
                'channel' => $step['action_type'] === 'email' ? 'email' : 'in_app',
                'status' => 'scheduled',
                'metadata' => [
                    'workflow_id' => $workflow->id,
                    'step_index' => $index,
                    'step_data' => $step
                ]
            ]);
        }
    }

    /**
     * Get onboarding completion rate for a client
     */
    public function getOnboardingCompletionRate($clientId)
    {
        $total = Customer::where('client_id', $clientId)
            ->whereNotNull('onboarding_workflow_id')
            ->count();

        if ($total === 0) return 0;

        $completed = Customer::where('client_id', $clientId)
            ->where('onboarding_status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    // ==================== HEALTH SCORING ====================

    /**
     * Calculate health score for a customer (0-100)
     */
    public function calculateHealthScore($customerId)
    {
        $customer = Customer::find($customerId);
        if (!$customer) return null;

        $engagementScore = $this->calculateEngagementScore($customer);
        $transactionScore = $this->calculateTransactionScore($customer);
        $supportScore = $this->calculateSupportScore($customer);
        $npsScore = $this->calculateNpsContribution($customer);

        // Weighted average
        $totalScore = round(
            ($engagementScore * 0.30) +
            ($transactionScore * 0.35) +
            ($supportScore * 0.20) +
            ($npsScore * 0.15),
            2
        );

        $recommendations = $this->generateHealthRecommendations(
            $engagementScore,
            $transactionScore,
            $supportScore,
            $npsScore
        );

        $healthScore = CustomerHealthScore::updateOrCreate(
            [
                'client_id' => $customer->client_id,
                'customer_id' => $customerId
            ],
            [
                'score' => $totalScore,
                'engagement_score' => $engagementScore,
                'transaction_score' => $transactionScore,
                'support_score' => $supportScore,
                'nps_score' => $npsScore,
                'status' => $this->getHealthStatus($totalScore),
                'recommendations' => $recommendations,
                'calculated_at' => now()
            ]
        );

        return (object)[
            'score' => $totalScore,
            'breakdown' => [
                'engagement' => $engagementScore,
                'transactions' => $transactionScore,
                'support' => $supportScore,
                'nps' => $npsScore
            ],
            'recommendations' => $recommendations,
            'status' => $healthScore->status
        ];
    }

    /**
     * Engagement score (0-100) based on recent activity
     */
    protected function calculateEngagementScore(Customer $customer)
    {
        $recentEvents = WebsiteEvent::where('customer_id', $customer->id)
            ->where('created_at', '>', now()->subDays(30))
            ->count();

        $lastLogin = $customer->last_login_at;
        $daysSinceLogin = $lastLogin ? now()->diffInDays($lastLogin) : 999;

        $score = 50; // Base score

        // Activity points
        if ($recentEvents > 50) $score += 25;
        elseif ($recentEvents > 20) $score += 15;
        elseif ($recentEvents > 5) $score += 5;
        else $score -= 10;

        // Recency points
        if ($daysSinceLogin <= 7) $score += 25;
        elseif ($daysSinceLogin <= 14) $score += 15;
        elseif ($daysSinceLogin <= 30) $score += 5;
        else $score -= 20;

        return min(100, max(0, $score));
    }

    /**
     * Transaction score (0-100) based on purchase behavior
     */
    protected function calculateTransactionScore(Customer $customer)
    {
        $totalTransactions = Transaction::where('customer_id', $customer->id)->count();
        $recentTransactions = Transaction::where('customer_id', $customer->id)
            ->where('created_at', '>', now()->subDays(90))
            ->count();
        $totalSpent = Transaction::where('customer_id', $customer->id)->sum('amount');

        $score = 30; // Base score

        // Frequency
        if ($totalTransactions > 20) $score += 25;
        elseif ($totalTransactions > 10) $score += 15;
        elseif ($totalTransactions > 3) $score += 5;

        // Recency
        if ($recentTransactions > 5) $score += 25;
        elseif ($recentTransactions > 2) $score += 15;
        elseif ($recentTransactions > 0) $score += 5;
        else $score -= 15;

        // Value
        if ($totalSpent > 5000) $score += 20;
        elseif ($totalSpent > 1000) $score += 10;
        elseif ($totalSpent > 100) $score += 5;

        return min(100, max(0, $score));
    }

    /**
     * Support score (0-100) - higher is better (fewer tickets)
     */
    protected function calculateSupportScore(Customer $customer)
    {
        $ticketCount = $customer->support_tickets_count ?? 0;
        $resolvedCount = $customer->resolved_tickets_count ?? 0;

        $score = 80; // Start high

        // Deduct for open tickets
        if ($ticketCount > 5) $score -= 30;
        elseif ($ticketCount > 3) $score -= 15;
        elseif ($ticketCount > 1) $score -= 5;

        // Deduct for unresolved
        $unresolved = $ticketCount - $resolvedCount;
        if ($unresolved > 2) $score -= 20;
        elseif ($unresolved > 0) $score -= 10;

        return min(100, max(0, $score));
    }

    /**
     * NPS contribution to health score (0-100)
     */
    protected function calculateNpsContribution(Customer $customer)
    {
        $latestResponse = NpsResponse::where('customer_id', $customer->id)
            ->latest()
            ->first();

        if (!$latestResponse) return 50; // Neutral if no NPS data

        // Map 0-10 to 0-100
        return $latestResponse->score * 10;
    }

    /**
     * Get health status label
     */
    protected function getHealthStatus($score)
    {
        if ($score >= 70) return 'healthy';
        if ($score >= 40) return 'at_risk';
        return 'critical';
    }

    /**
     * Generate recommendations based on score breakdown
     */
    protected function generateHealthRecommendations($engagement, $transaction, $support, $nps)
    {
        $recommendations = [];

        if ($engagement < 40) {
            $recommendations[] = 'Low engagement detected - schedule re-engagement campaign';
        }
        if ($transaction < 40) {
            $recommendations[] = 'Declining purchase activity - offer personalized discount';
        }
        if ($support < 50) {
            $recommendations[] = 'Multiple open support tickets - escalate to success team';
        }
        if ($nps < 50) {
            $recommendations[] = 'Low NPS score - conduct feedback call';
        }
        if (empty($recommendations)) {
            $recommendations[] = 'Customer is healthy - maintain current engagement';
        }

        return $recommendations;
    }

    /**
     * Recalculate health scores for all customers of a client
     */
    public function recalculateAllHealthScores($clientId)
    {
        $customers = Customer::where('client_id', $clientId)
            ->where('is_active', true)
            ->get();

        $count = 0;
        foreach ($customers as $customer) {
            $this->calculateHealthScore($customer->id);
            $count++;
        }

        return $count;
    }

    /**
     * Get health score distribution for dashboard
     */
    public function getHealthScoreDistribution($clientId)
    {
        $healthy = CustomerHealthScore::where('client_id', $clientId)->where('score', '>=', 70)->count();
        $atRisk = CustomerHealthScore::where('client_id', $clientId)->whereBetween('score', [40, 69])->count();
        $critical = CustomerHealthScore::where('client_id', $clientId)->where('score', '<', 40)->count();
        $total = $healthy + $atRisk + $critical;

        return [
            'healthy' => ['count' => $healthy, 'percentage' => $total > 0 ? round(($healthy / $total) * 100, 1) : 0],
            'at_risk' => ['count' => $atRisk, 'percentage' => $total > 0 ? round(($atRisk / $total) * 100, 1) : 0],
            'critical' => ['count' => $critical, 'percentage' => $total > 0 ? round(($critical / $total) * 100, 1) : 0]
        ];
    }

    // ==================== AUTOMATED CHECK-INS ====================

    /**
     * Execute a scheduled check-in
     */
    public function executeCheckin(AutomatedCheckin $checkin)
    {
        $customer = $checkin->customer;
        if (!$customer || !$customer->is_active) {
            $checkin->update(['status' => 'cancelled']);
            return false;
        }

        try {
            switch ($checkin->channel) {
                case 'email':
                    $this->sendCheckinEmail($checkin);
                    break;
                case 'sms':
                    $this->sendCheckinSms($checkin);
                    break;
                case 'in_app':
                    $this->sendCheckinInApp($checkin);
                    break;
            }

            $checkin->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Check-in failed: " . $e->getMessage());
            $checkin->update(['status' => 'failed']);
            return false;
        }
    }

    protected function sendCheckinEmail($checkin)
    {
        // Implement email sending logic
        // Mail::to($checkin->customer->email)->send(new CheckinEmail($checkin));
    }

    protected function sendCheckinSms($checkin)
    {
        // Implement SMS gateway
    }

    protected function sendCheckinInApp($checkin)
    {
        // Store in-app notification
    }

    /**
     * Get available check-in templates
     */
    public function getCheckinTemplates()
    {
        return [
            'welcome' => [
                'name' => 'Welcome Check-in',
                'subject' => 'How is your experience so far?',
                'message' => 'Hi {name}, we hope you are enjoying our service. Let us know if you need anything!'
            ],
            '30_day' => [
                'name' => '30-Day Check-in',
                'subject' => 'Your first month with us',
                'message' => 'Hi {name}, it has been 30 days! We would love to hear your feedback.'
            ],
            '90_day' => [
                'name' => '90-Day Review',
                'subject' => 'Quarterly business review',
                'message' => 'Hi {name}, let us schedule a quick review of your progress.'
            ],
            'renewal' => [
                'name' => 'Renewal Reminder',
                'subject' => 'Your subscription is renewing soon',
                'message' => 'Hi {name}, your plan renews in 30 days. Any questions?'
            ],
            'win_back' => [
                'name' => 'Win-Back',
                'subject' => 'We miss you!',
                'message' => 'Hi {name}, we noticed you have not been around. Is everything okay?'
            ]
        ];
    }

    // ==================== NPS SURVEYS ====================

    /**
     * Distribute NPS survey to target audience
     */
    public function distributeNpsSurvey(NpsSurvey $survey)
    {
        $customers = $this->getSurveyAudience($survey);

        foreach ($customers as $customer) {
            // Create survey link/token
            $token = bin2hex(random_bytes(16));

            // Send survey invitation
            $this->sendNpsInvitation($customer, $survey, $token);
        }

        $survey->update(['sent_count' => $customers->count(), 'sent_at' => now()]);
    }

    /**
     * Get target audience for a survey
     */
    protected function getSurveyAudience(NpsSurvey $survey)
    {
        $query = Customer::where('client_id', $survey->client_id)
            ->where('is_active', true);

        if ($survey->send_to === 'segment' && $survey->segment_id) {
            $query->whereHas('segments', function ($q) use ($survey) {
                $q->where('customer_segments.id', $survey->segment_id);
            });
        }

        // Exclude customers who responded in last 30 days
        $query->whereDoesntHave('npsResponses', function ($q) {
            $q->where('created_at', '>', now()->subDays(30));
        });

        return $query->get();
    }

    /**
     * Send NPS invitation
     */
    protected function sendNpsInvitation($customer, $survey, $token)
    {
        // Implement email/SMS sending
        // Include unique survey link with token
    }

    /**
     * Categorize NPS score
     */
    public function categorizeNpsScore($score)
    {
        if ($score >= 9) return 'promoter';
        if ($score >= 7) return 'passive';
        return 'detractor';
    }

    /**
     * Calculate overall NPS for a client (-100 to +100)
     */
    public function calculateOverallNps($clientId)
    {
        $responses = NpsResponse::where('client_id', $clientId)
            ->where('created_at', '>', now()->subDays(90))
            ->get();

        if ($responses->isEmpty()) return null;

        $total = $responses->count();
        $promoters = $responses->where('category', 'promoter')->count();
        $detractors = $responses->where('category', 'detractor')->count();

        $nps = round((($promoters / $total) - ($detractors / $total)) * 100, 1);

        return [
            'score' => $nps,
            'total_responses' => $total,
            'promoters' => $promoters,
            'passives' => $responses->where('category', 'passive')->count(),
            'detractors' => $detractors,
            'response_rate' => $this->calculateResponseRate($clientId, $total)
        ];
    }

    /**
     * Calculate survey response rate
     */
    protected function calculateResponseRate($clientId, $responseCount)
    {
        $totalCustomers = Customer::where('client_id', $clientId)
            ->where('is_active', true)
            ->count();

        return $totalCustomers > 0 ? round(($responseCount / $totalCustomers) * 100, 1) : 0;
    }

    /**
     * Generate detailed NPS report for a survey
     */
    public function generateNpsReport(NpsSurvey $survey)
    {
        $responses = $survey->responses;
        $total = $responses->count();

        if ($total === 0) {
            return ['error' => 'No responses yet'];
        }

        $promoters = $responses->where('category', 'promoter')->count();
        $passives = $responses->where('category', 'passive')->count();
        $detractors = $responses->where('category', 'detractor')->count();

        $scoreDistribution = [];
        for ($i = 0; $i <= 10; $i++) {
            $scoreDistribution[$i] = $responses->where('score', $i)->count();
        }

        $commonFeedback = $responses->whereNotNull('feedback')
            ->pluck('feedback')
            ->take(20)
            ->toArray();

        return [
            'survey_name' => $survey->name,
            'total_responses' => $total,
            'nps_score' => round((($promoters / $total) - ($detractors / $total)) * 100, 1),
            'breakdown' => [
                'promoters' => ['count' => $promoters, 'percentage' => round(($promoters / $total) * 100, 1)],
                'passives' => ['count' => $passives, 'percentage' => round(($passives / $total) * 100, 1)],
                'detractors' => ['count' => $detractors, 'percentage' => round(($detractors / $total) * 100, 1)]
            ],
            'score_distribution' => $scoreDistribution,
            'common_feedback' => $commonFeedback,
            'response_trend' => $this->getNpsResponseTrend($survey)
        ];
    }

    /**
     * Get NPS response trend over time
     */
    protected function getNpsResponseTrend(NpsSurvey $survey)
    {
        return $survey->responses()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, AVG(score) as avg_score')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => $row->date,
                    'responses' => $row->count,
                    'average_score' => round($row->avg_score, 1)
                ];
            });
    }
}
