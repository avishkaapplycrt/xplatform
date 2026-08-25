<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class BehavioralProfile extends Model
{
    protected $fillable = [
        'client_id', 'name', 'email', 'segment',
        'intent_score', 'engagement_score', 'churn_score', 'loyalty_score',
        'trust_score', 'frustration_score', 'buying_readiness', 'dropoff_risk',
        'reactivation_potential', 'overall_score', 'last_active_at',
    ];

    protected $casts = ['last_active_at' => 'datetime'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function userEvents(): HasMany
    {
        return $this->hasMany(UserEvent::class);
    }

    public function initials(): string
    {
        $words = explode(' ', $this->name);
        return strtoupper(($words[0][0] ?? '') . ($words[1][0] ?? ''));
    }

    public function segmentLabel(): string
    {
        return match($this->segment) {
            'champion' => 'Champion',
            'loyal'    => 'Loyal',
            'at_risk'  => 'At Risk',
            'dormant'  => 'Dormant',
            'new'      => 'New',
            default    => 'Unknown',
        };
    }

    public function segmentColor(): string
    {
        return match($this->segment) {
            'champion' => '#10b981',
            'loyal'    => '#3b82f6',
            'at_risk'  => '#ef4444',
            'dormant'  => '#f59e0b',
            'new'      => '#8b5cf6',
            default    => '#6b7280',
        };
    }

    public function segmentColorAlpha(): string
    {
        return match($this->segment) {
            'champion' => 'rgba(16, 185, 129, 0.15)',
            'loyal'    => 'rgba(59, 130, 246, 0.15)',
            'at_risk'  => 'rgba(239, 68, 68, 0.15)',
            'dormant'  => 'rgba(245, 158, 11, 0.15)',
            'new'      => 'rgba(139, 92, 246, 0.15)',
            default    => 'rgba(107, 114, 128, 0.15)',
        };
    }

    public function segmentBg(): string
    {
        return match($this->segment) {
            'champion' => '#f0fdf4',
            'loyal'    => '#eff6ff',
            'at_risk'  => '#fef2f2',
            'dormant'  => '#fffbeb',
            'new'      => '#faf5ff',
            default    => '#f9fafb',
        };
    }

    public function segmentBorder(): string
    {
        return match($this->segment) {
            'champion' => '#a7f3d0',
            'loyal'    => '#bfdbfe',
            'at_risk'  => '#fecaca',
            'dormant'  => '#fde68a',
            'new'      => '#ddd6fe',
            default    => '#e5e7eb',
        };
    }

    public function overallColor(): string
    {
        return $this->overall_score >= 70 ? '#10b981'
             : ($this->overall_score >= 45 ? '#f59e0b'
             : '#ef4444');
    }

    public function radarValues(): array
    {
        return [
            $this->intent_score,
            $this->engagement_score,
            $this->loyalty_score,
            $this->trust_score,
            $this->buying_readiness,
            100 - $this->churn_score,
            100 - $this->frustration_score,
            100 - $this->dropoff_risk,
            $this->reactivation_potential,
        ];
    }

    public function recommendation(): string
    {
        return match($this->segment) {
            'champion' => 'Maintain engagement with VIP perks and early access to new features.',
            'loyal'    => 'Upsell opportunity detected — present premium tier or bundle offer.',
            'at_risk'  => 'Immediate intervention required. Trigger personalised win-back campaign.',
            'dormant'  => 'Activate re-engagement with seasonal or interest-based promotion.',
            'new'      => 'Accelerate onboarding — guide toward first key value moment.',
            default    => 'Monitor profile for behavioural shifts.',
        };
    }

    public function predictedAction(): string
    {
        if ($this->buying_readiness >= 70 && $this->churn_score < 40) {
            return 'Likely to convert within 7 days';
        }
        if ($this->churn_score >= 65) {
            return 'Cancellation risk within 14 days';
        }
        if ($this->reactivation_potential >= 70 && $this->engagement_score < 30) {
            return 'May respond to re-engagement in 7–14 days';
        }
        if ($this->intent_score >= 80) {
            return 'High purchase intent — conversion imminent';
        }
        if ($this->engagement_score >= 85) {
            return 'Strong engagement — prime for upsell';
        }
        if ($this->frustration_score >= 60) {
            return 'Support intervention recommended';
        }
        return 'Continue standard engagement flow';
    }

    public static function allProfiles(): Collection
    {
        return self::orderByDesc('overall_score')->get();
    }
}
