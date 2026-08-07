<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadScore;
use App\Models\LeadActivity;
use App\Models\WebsiteEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class LeadScoringService
{
    /**
     * Score a single lead based on multiple factors
     */
    public function scoreLead($leadId)
    {
        $lead = Lead::with('activities')->find($leadId);
        if (!$lead) return null;

        // Calculate individual score components
        $behaviorScore = $this->calculateBehaviorScore($lead);
        $demographicScore = $this->calculateDemographicScore($lead);
        $engagementScore = $this->calculateEngagementScore($lead);

        // Weighted total (weights can be configured per client)
        $totalScore = round(
            ($behaviorScore * 0.40) +
            ($demographicScore * 0.30) +
            ($engagementScore * 0.30),
            2
        );

        // Determine qualification status
        $qualification = $this->determineQualification($totalScore);

        // Calculate conversion probability using simple heuristic
        $conversionProbability = $this->calculateConversionProbability($lead, $totalScore);

        // Create score record
        $score = LeadScore::create([
            'client_id' => $lead->client_id,
            'lead_id' => $lead->id,
            'behavior_score' => $behaviorScore,
            'demographic_score' => $demographicScore,
            'engagement_score' => $engagementScore,
            'total_score' => $totalScore,
            'qualification_status' => $qualification,
            'conversion_probability' => $conversionProbability,
            'factors' => [
                'page_views' => $lead->activities->where('type', 'page_view')->count(),
                'form_fills' => $lead->activities->where('type', 'form_fill')->count(),
                'email_opens' => $lead->activities->where('type', 'email_open')->count(),
                'email_clicks' => $lead->activities->where('type', 'email_click')->count(),
                'time_on_site' => $lead->activities->sum('duration_seconds') ?? 0,
                'has_company' => !empty($lead->company) ? 1 : 0,
                'has_job_title' => !empty($lead->job_title) ? 1 : 0,
                'source_quality' => $this->getSourceQuality($lead->source),
            ],
        ]);

        // Update lead with latest qualification
        $lead->update([
            'qualification_status' => $qualification,
            'last_scored_at' => now(),
        ]);

        // Auto-route hot leads if enabled
        if ($qualification === 'hot' && $this->shouldAutoRoute($lead->client_id)) {
            $this->routeToSales($lead);
        }

        return $score;
    }

    /**
     * Calculate behavior score (0-100) based on website activity
     */
    protected function calculateBehaviorScore(Lead $lead)
    {
        $score = 0;
        $activities = $lead->activities;

        // Page views (max 25 points)
        $pageViews = $activities->where('type', 'page_view')->count();
        $score += min($pageViews * 2.5, 25);

        // Form fills (max 30 points)
        $formFills = $activities->where('type', 'form_fill')->count();
        $score += min($formFills * 15, 30);

        // Pricing page visits (max 20 points)
        $pricingViews = $activities->where('type', 'page_view')
            ->where('data->page', 'like', '%pricing%')
            ->count();
        $score += min($pricingViews * 10, 20);

        // Demo requests (max 25 points)
        $demoRequests = $activities->where('type', 'demo_request')->count();
        $score += min($demoRequests * 25, 25);

        return min(100, $score);
    }

    /**
     * Calculate demographic score (0-100) based on lead profile
     */
    protected function calculateDemographicScore(Lead $lead)
    {
        $score = 0;

        // Company info (max 30 points)
        if (!empty($lead->company)) $score += 20;
        if (!empty($lead->job_title)) $score += 10;

        // Source quality (max 40 points)
        $sourceQuality = $this->getSourceQuality($lead->source);
        $score += $sourceQuality;

        // Phone provided (max 15 points)
        if (!empty($lead->phone)) $score += 15;

        // Email domain quality (max 15 points)
        if ($this->isBusinessEmail($lead->email)) $score += 15;

        return min(100, $score);
    }

    /**
     * Calculate engagement score (0-100) based on email/chat interactions
     */
    protected function calculateEngagementScore(Lead $lead)
    {
        $score = 0;
        $activities = $lead->activities;

        // Email opens (max 20 points)
        $emailOpens = $activities->where('type', 'email_open')->count();
        $score += min($emailOpens * 4, 20);

        // Email clicks (max 30 points)
        $emailClicks = $activities->where('type', 'email_click')->count();
        $score += min($emailClicks * 10, 30);

        // Time on site (max 25 points)
        $totalTime = $activities->sum('duration_seconds') ?? 0;
        $score += min($totalTime / 60, 25); // 1 point per minute, max 25

        // Return visits (max 25 points)
        $uniqueDays = $activities->where('type', 'page_view')
            ->pluck('created_at')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->unique()
            ->count();
        $score += min($uniqueDays * 5, 25);

        return min(100, $score);
    }

    /**
     * Get source quality score (0-40)
     */
    protected function getSourceQuality($source)
    {
        $qualityMap = [
            'referral' => 40,
            'organic_search' => 35,
            'paid_search' => 30,
            'direct' => 30,
            'linkedin' => 35,
            'webinar' => 35,
            'trade_show' => 35,
            'email_campaign' => 25,
            'social_media' => 20,
            'content_download' => 30,
            'cold_outreach' => 15,
            'website' => 25,
            'other' => 20,
        ];

        return $qualityMap[strtolower($source)] ?? 20;
    }

    /**
     * Check if email is business domain
     */
    protected function isBusinessEmail($email)
    {
        $personalDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'aol.com', 'icloud.com'];
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        return !in_array($domain, $personalDomains);
    }

    /**
     * Determine qualification status based on total score
     */
    protected function determineQualification($totalScore)
    {
        if ($totalScore >= 75) return 'hot';
        if ($totalScore >= 50) return 'warm';
        if ($totalScore >= 25) return 'cold';
        return 'unqualified';
    }

    /**
     * Calculate conversion probability (0-100)
     */
    public function calculateConversionProbability(Lead $lead, $totalScore = null)
    {
        if ($totalScore === null) {
            $latestScore = $lead->latestScore;
            $totalScore = $latestScore ? $latestScore->total_score : 50;
        }

        // Base probability from score
        $probability = $totalScore * 0.8;

        // Boost for recent activity
        $lastActivity = $lead->activities()->latest()->first();
        if ($lastActivity && $lastActivity->created_at->diffInDays() <= 7) {
            $probability += 10;
        }

        // Boost for multiple touchpoints
        $touchpointCount = $lead->activities()->distinct('type')->count();
        if ($touchpointCount >= 4) $probability += 10;

        return min(100, round($probability, 1));
    }

    /**
     * Get score distribution for dashboard
     */
    public function getScoreDistribution($clientId)
    {
        $ranges = [
            '90-100' => ['min' => 90, 'max' => 100, 'color' => 'green'],
            '80-89' => ['min' => 80, 'max' => 89, 'color' => 'emerald'],
            '70-79' => ['min' => 70, 'max' => 79, 'color' => 'teal'],
            '60-69' => ['min' => 60, 'max' => 69, 'color' => 'blue'],
            '50-59' => ['min' => 50, 'max' => 59, 'color' => 'cyan'],
            '40-49' => ['min' => 40, 'max' => 49, 'color' => 'amber'],
            '30-39' => ['min' => 30, 'max' => 39, 'color' => 'orange'],
            '0-29' => ['min' => 0, 'max' => 29, 'color' => 'red'],
        ];

        $distribution = [];
        foreach ($ranges as $label => $range) {
            $count = LeadScore::where('client_id', $clientId)
                ->whereBetween('total_score', [$range['min'], $range['max']])
                ->count();
            $distribution[] = [
                'label' => $label,
                'count' => $count,
                'color' => $range['color'],
            ];
        }

        return $distribution;
    }

    /**
     * Bulk score all unscored leads
     */
    public function bulkScoreLeads($clientId)
    {
        $leads = Lead::where('client_id', $clientId)
            ->where(function ($query) {
                $query->whereNull('last_scored_at')
                      ->orWhere('last_scored_at', '<', now()->subDays(7));
            })
            ->get();

        $count = 0;
        foreach ($leads as $lead) {
            $this->scoreLead($lead->id);
            $count++;
        }

        return $count;
    }

    /**
     * Route hot lead to sales team
     */
    public function routeToSales(Lead $lead)
    {
        // Log the routing
        Log::info("Routing hot lead to sales", [
            'lead_id' => $lead->id,
            'client_id' => $lead->client_id,
            'score' => $lead->latestScore?->total_score,
        ]);

        // Create routing record
        LeadActivity::create([
            'client_id' => $lead->client_id,
            'lead_id' => $lead->id,
            'type' => 'sales_routed',
            'data' => [
                'score' => $lead->latestScore?->total_score,
                'qualification' => $lead->qualification_status,
                'routed_at' => now()->toIso8601String(),
            ],
        ]);

        // TODO: Implement actual Slack/email notification
        // $this->sendSlackNotification($lead);
        // $this->sendEmailNotification($lead);

        return true;
    }

    /**
     * Check if auto-routing is enabled for client
     */
    protected function shouldAutoRoute($clientId)
    {
        // TODO: Check client settings
        return true;
    }

    /**
     * Record lead activity
     */
    public function recordActivity($leadId, $type, $data = [], $duration = null)
    {
        $lead = Lead::find($leadId);
        if (!$lead) return null;

        return LeadActivity::create([
            'client_id' => $lead->client_id,
            'lead_id' => $leadId,
            'type' => $type,
            'data' => $data,
            'duration_seconds' => $duration,
        ]);
    }
}
