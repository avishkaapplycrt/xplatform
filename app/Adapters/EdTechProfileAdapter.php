<?php

namespace App\Adapters;

use Carbon\Carbon;

/**
 * Adapter that wraps EdTech array data to provide the same interface
 * as BehavioralProfile model for use in l4.blade.php and decision centre cards
 */
class EdTechProfileAdapter
{
    private array $data;

    public function __construct(array $data)
    {
        // Convert last_login string to Carbon for compatibility
        if (!empty($data['last_login'])) {
            try {
                $data['last_active_at'] = Carbon::parse($data['last_login']);
            } catch (\Exception $e) {
                $data['last_active_at'] = null;
            }
        } else {
            $data['last_active_at'] = null;
        }

        // Ensure all score fields have defaults
        $scoreFields = [
            'intent_score', 'engagement_score', 'churn_score', 'loyalty_score',
            'trust_score', 'frustration_score', 'buying_readiness', 'dropoff_risk',
            'reactivation_potential', 'overall_score', 'readiness_score',
            'performance_score', 'completion_score', 'avg_progress',
            'quiz_attempts', 'avg_quiz_score', 'best_quiz_score',
            'total_spent', 'login_count_30d', 'enrollment_count', 'completed_courses',
        ];
        foreach ($scoreFields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = 0;
            }
        }

        // Ensure string fields
        if (empty($data['name'])) {
            $data['name'] = 'Student #' . ($data['id'] ?? 'Unknown');
        }
        if (empty($data['email'])) {
            $data['email'] = '';
        }
        if (empty($data['segment'])) {
            $data['segment'] = 'new';
        }

        $this->data = $data;
    }

    // ── Magic getter for any property ───────────────────────────────────────
    public function __get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    // ── Identity ───────────────────────────────────────────────────────────
    public function getKey(): mixed
    {
        return $this->data['id'] ?? null;
    }

    // ── Methods matching BehavioralProfile interface ──────────────────────
    public function initials(): string
    {
        $name = $this->data['name'] ?? 'Student';
        $parts = explode(' ', $name);
        return strtoupper(substr($parts[0] ?? 'S', 0, 1) . substr($parts[1] ?? '', 0, 1));
    }

    public function segmentColor(): string
    {
        return match($this->data['segment'] ?? 'new') {
            'champion' => '#f59e0b',
            'loyal'    => '#8b5cf6',
            'at_risk'  => '#ef4444',
            'new'      => '#3b82f6',
            'dormant'  => '#10b981',
            default    => '#3b82f6',
        };
    }

    public function segmentColorAlpha(): string
    {
        return $this->segmentColor() . '33'; // 20% opacity
    }

    public function segmentBg(): string
    {
        return match($this->data['segment'] ?? 'new') {
            'champion' => '#fffbeb',
            'loyal'    => '#f5f3ff',
            'at_risk'  => '#fef2f2',
            'new'      => '#eff6ff',
            'dormant'  => '#f0fdf4',
            default    => '#eff6ff',
        };
    }

    public function segmentBorder(): string
    {
        return match($this->data['segment'] ?? 'new') {
            'champion' => '#fcd34d',
            'loyal'    => '#c4b5fd',
            'at_risk'  => '#fca5a5',
            'new'      => '#93c5fd',
            'dormant'  => '#86efac',
            default    => '#93c5fd',
        };
    }

    public function segmentLabel(): string
    {
        return match($this->data['segment'] ?? 'new') {
            'champion' => 'Champion',
            'loyal'    => 'Loyal',
            'at_risk'  => 'At Risk',
            'new'      => 'New',
            'dormant'  => 'Dormant',
            default    => 'New',
        };
    }

    public function radarValues(): array
    {
        return [
            $this->data['intent_score']        ?? 0,
            $this->data['engagement_score']    ?? 0,
            $this->data['loyalty_score']       ?? 0,
            $this->data['trust_score']         ?? 0,
            $this->data['buying_readiness']    ?? 0,
            max(0, 100 - ($this->data['churn_score'] ?? 0)),
            max(0, 100 - ($this->data['frustration_score'] ?? 0)),
            max(0, 100 - ($this->data['dropoff_risk'] ?? 0)),
            $this->data['reactivation_potential'] ?? 0,
        ];
    }

    public function recommendation(): string
    {
        $churn = $this->data['churn_score'] ?? 0;
        $intent = $this->data['intent_score'] ?? 0;
        $readiness = $this->data['readiness_score'] ?? 0;

        if ($churn >= 65) return 'High churn risk. Immediate intervention recommended.';
        if ($intent >= 80 && $readiness >= 75) return 'Hot learner. Upsell opportunity.';
        if (($this->data['loyalty_score'] ?? 0) >= 72) return 'Loyal learner. Referral program recommended.';
        if (($this->data['frustration_score'] ?? 0) >= 50) return 'Frustration detected. Tutor support recommended.';
        if (($this->data['reactivation_potential'] ?? 0) >= 60) return 'Reactivation candidate. Win-back message recommended.';
        return 'Engagement growing. Continue nurturing.';
    }

    public function predictedAction(): string
    {
        $churn = $this->data['churn_score'] ?? 0;
        $intent = $this->data['intent_score'] ?? 0;
        $readiness = $this->data['readiness_score'] ?? 0;

        if ($churn >= 65) return 'Send retention offer';
        if ($intent >= 80 && $readiness >= 75) return 'Send upsell offer';
        if (($this->data['loyalty_score'] ?? 0) >= 80) return 'Send referral invite';
        if (($this->data['frustration_score'] ?? 0) >= 50) return 'Assign tutor';
        if (($this->data['reactivation_potential'] ?? 0) >= 60) return 'Send win-back';
        return 'Continue nurturing';
    }
}
