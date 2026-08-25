<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Customer;
use App\Models\EmailCampaign;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlertNotification;
use App\Notifications\AlertNotification as AlertNotificationChannel;

class AlertService
{
    protected $checkInterval = 300; // 5 minutes

    public function checkAllRules()
    {
        $activeRules = AlertRule::where('is_active', true)->get();

        foreach ($activeRules as $rule) {
            $this->checkRule($rule);
        }
    }

    public function checkRule(AlertRule $rule)
    {
        $customers = $this->getCustomersForMetric($rule->metric);

        foreach ($customers as $customer) {
            $value = $this->getMetricValue($rule->metric, $customer);

            if ($this->meetsThreshold($value, $rule->threshold_value, $rule->comparison_operator)) {
                $this->triggerAlert($rule, $customer, $value);
            }
        }
    }

    protected function getCustomersForMetric($metric)
    {
        return Customer::where('client_id', $clientId)
        ->where('is_active', true)
        ->get();
    }

    protected function getMetricValue($metric, $customer)
    {
        switch ($metric) {
            case 'churn_risk':
                return $customer->churn_risk_score ?? 0;
            case 'engagement_drop':
                return $customer->engagement_score_recent_change ?? 0;
            case 'transaction_anomaly':
                return $customer->avg_transaction_amount_recent ?? 0;
            case 'low_email_open_rate':
                return $customer->email_open_rate_last_30_days ?? 100;
            default:
                return 0;
        }
    }

    protected function meetsThreshold($value, $threshold, $operator)
    {
        switch ($operator) {
            case 'gt':
                return $value > $threshold;
            case 'lt':
                return $value < $threshold;
            case 'eq':
                return $value == $threshold;
            case 'gte':
                return $value >= $threshold;
            case 'lte':
                return $value <= $threshold;
            default:
                return false;
        }
    }

    protected function triggerAlert(AlertRule $rule, Customer $customer, $actualValue)
    {
        // Check if alert already exists and is not resolved
        $existingAlert = Alert::where('rule_id', $rule->id)
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['new', 'acknowledged'])
            ->first();

        if ($existingAlert) {
            return $existingAlert;
        }

        $alert = Alert::create([
            'client_id' => $rule->client_id,
            'rule_id' => $rule->id,
            'customer_id' => $customer->id,
            'metric' => $rule->metric,
            'threshold_value' => $rule->threshold_value,
            'actual_value' => $actualValue,
            'message' => $this->generateAlertMessage($rule, $customer, $actualValue),
            'status' => 'new',
            'priority' => $this->calculatePriority($rule->metric, $actualValue)
        ]);

        $this->sendNotifications($alert);

        return $alert;
    }

    protected function generateAlertMessage(AlertRule $rule, Customer $customer, $actualValue)
    {
        $messages = [
            'churn_risk' => "High churn risk detected for {$customer->name} ({$actualValue}% risk)",
            'engagement_drop' => "Engagement drop detected for {$customer->name} ({$actualValue} points decrease)",
            'transaction_anomaly' => "Transaction anomaly detected for {$customer->name} (${$actualValue} avg)",
            'low_email_open_rate' => "Low email open rate for {$customer->name} ({$actualValue}% open rate)"
        ];

        return $messages[$rule->metric] ?? "Alert triggered for {$customer->name}";
    }

    protected function calculatePriority($metric, $actualValue)
    {
        $priority = 'medium';

        if ($metric === 'churn_risk' && $actualValue >= 80) {
            $priority = 'high';
        } elseif ($metric === 'engagement_drop' && $actualValue < -50) {
            $priority = 'high';
        } elseif ($metric === 'transaction_anomaly' && $actualValue > 1000) {
            $priority = 'high';
        } elseif ($metric === 'low_email_open_rate' && $actualValue < 20) {
            $priority = 'high';
        }

        return $priority;
    }

    protected function sendNotifications(Alert $alert)
    {
        $rule = $alert->rule;
        $customer = $alert->customer;

        // Send email notifications
        if (in_array('email', $rule->notification_channels)) {
            $this->sendEmailNotification($alert);
        }

        // Send SMS notifications
        if (in_array('sms', $rule->notification_channels)) {
            $this->sendSmsNotification($alert);
        }

        // Send Slack notifications
        if (in_array('slack', $rule->notification_channels) && $rule->client->slack_webhook_url) {
            $this->sendSlackNotification($alert);
        }

        // Send in-app notifications
        if (in_array('in_app', $rule->notification_channels)) {
            $this->sendInAppNotification($alert);
        }
    }

    protected function sendEmailNotification(Alert $alert)
    {
        try {
            $recipients = [$alert->client->support_email];

            if ($alert->rule->assigned_team_member) {
                $recipients[] = $alert->rule->assignedTeamMember->email;
            }

            Mail::to($recipients)
                ->send(new AlertNotification($alert));

        } catch (\Exception $e) {
            Log::error("Failed to send email alert: " . $e->getMessage());
        }
    }

    protected function sendSmsNotification(Alert $alert)
    {
        // Implement SMS gateway integration
        // Example: Twilio, Nexmo, or AWS SNS
    }

    protected function sendSlackNotification(Alert $alert)
    {
        // Implement Slack webhook integration
    }

    protected function sendInAppNotification(Alert $alert)
    {
        // Implement in-app notification system
        // Store notification in database for real-time delivery
    }

    public function processEscalations()
    {
        $timeThreshold = now()->subMinutes(5);
        $alerts = Alert::where('status', 'acknowledged')
            ->where('acknowledged_at', '<', $timeThreshold)
            ->whereHas('rule', function($query) {
                $query->where('escalation_minutes', '>', 0);
            })
            ->with('rule')
            ->get();

        foreach ($alerts as $alert) {
            $this->escalateAlert($alert);
        }
    }

    protected function escalateAlert(Alert $alert)
    {
        $alert->update(['status' => 'escalated']);

        // Send additional notifications to higher priority recipients
        // Log escalation in audit trail
    }
}