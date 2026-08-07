<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\WebsiteEvent;
use App\Models\EmailLog;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class ChurnPredictionService
{
    public function predict($customerId)
    {
        $customer = Customer::findOrFail($customerId);

        // Calculate behavioral features
        $recentActivity = WebsiteEvent::where('customer_id', $customerId)
            ->where('created_at', '>', now()->subDays(30))
            ->count();

        $emailEngagement = EmailLog::where('customer_id', $customerId)
            ->where('opened', true)
            ->where('created_at', '>', now()->subDays(30))
            ->count();

        $purchaseFrequency = Transaction::where('customer_id', $customerId)
            ->where('created_at', '>', now()->subDays(90))
            ->avg('amount');

        // Apply ML model (simplified for example)
        $churnScore = $this->calculateChurnScore([
            'recent_activity' => $recentActivity,
            'email_engagement' => $emailEngagement,
            'purchase_frequency' => $purchaseFrequency,
            'days_since_last_purchase' => $customer->last_purchase_days,
            'support_tickets' => $customer->support_tickets_count
        ]);

        return [
            'customer_id' => $customerId,
            'customer_name' => $customer->name,
            'churn_score' => $churnScore,
            'risk_level' => $this->getRiskLevel($churnScore),
            'recommendations' => $this->getRecommendations($churnScore, $customer)
        ];
    }

    public function getHighRiskCustomers($threshold = 70)
    {
        return Customer::where('is_active', true)
            ->with(['client', 'recentEvents', 'recentTransactions'])
            ->whereHas('recentEvents', function($query) {
                $query->where('created_at', '>', now()->subDays(7));
            })
            ->get()
            ->filter(function($customer) use ($threshold) {
                $score = $this->calculateChurnScoreForCustomer($customer);
                return $score >= $threshold;
            })
            ->sortByDesc('churn_risk_score')
            ->take(50);
    }

    public function executeCampaign($campaign)
    {
        // Find customers matching campaign criteria
        $customers = $this->getCampaignAudience($campaign);

        // Send personalized retention offers
        foreach ($customers as $customer) {
            $this->sendRetentionOffer($customer, $campaign);
        }
    }

    protected function calculateChurnScore(array $features)
    {
        // Simplified scoring - in production use ML model
        $score = 0;

        // Activity factors
        $score += (30 - $features['recent_activity']) * 2; // Less activity = higher risk
        $score += (10 - $features['email_engagement']) * 1.5;
        $score += ($features['days_since_last_purchase'] > 60) ? 25 : 0;

        // Purchase behavior
        $score += ($features['purchase_frequency'] < 50) ? 15 : 0;

        // Support interactions
        $score += ($features['support_tickets'] > 3) ? 10 : 0;

        return min(100, max(0, $score));
    }

    protected function getRecommendations($score, $customer)
    {
        $recommendations = [];

        if ($score >= 80) {
            $recommendations[] = 'Immediate outreach - high churn risk';
            $recommendations[] = 'Offer exclusive discount or upgrade';
        }

        if ($customer->last_purchase_days > 45) {
            $recommendations[] = 'Win-back campaign recommended';
        }

        if ($customer->support_tickets_count > 2) {
            $recommendations[] = 'Proactive customer success call needed';
        }

        return $recommendations;
    }
}