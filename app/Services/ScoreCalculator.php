<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Computes all 9 behavioural scores for a single user from their raw event log.
 *
 * Scoring windows
 *   - Short  (30 d): intent, engagement, buying readiness — recent signals matter most
 *   - Long   (90 d): loyalty, trust, frustration, drop-off — patterns built over time
 *   - Re-eng (14 d): reactivation — did a dormant user just wake up?
 */
class ScoreCalculator
{
    public function __construct(
        private readonly Collection $events,
        private readonly ?Carbon    $lastLogin,
    ) {}

    // ── Public API ────────────────────────────────────────────────────────────

    public function compute(): array
    {
        return [
            'intent_score'            => $this->intent(),
            'engagement_score'        => $this->engagement(),
            'churn_score'             => $this->churn(),
            'loyalty_score'           => $this->loyalty(),
            'trust_score'             => $this->trust(),
            'frustration_score'       => $this->frustration(),
            'buying_readiness'        => $this->buyingReadiness(),
            'dropoff_risk'            => $this->dropoffRisk(),
            'reactivation_potential'  => $this->reactivation(),
        ];
    }

    public function overallScore(array $scores): int
    {
        // Positive dimensions weighted in, negative dimensions inverted
        return (int) round(
            $scores['intent_score']      * 0.18 +
            $scores['engagement_score']  * 0.18 +
            $scores['loyalty_score']     * 0.14 +
            $scores['trust_score']       * 0.14 +
            $scores['buying_readiness']  * 0.10 +
            (100 - $scores['churn_score'])       * 0.12 +
            (100 - $scores['frustration_score']) * 0.08 +
            (100 - $scores['dropoff_risk'])      * 0.06
        );
    }

    // ── Score formulas ────────────────────────────────────────────────────────

    /**
     * Purchase intent — high-value pre-purchase signals in last 30 days.
     *   pricing_view ×15  (strong intent — comparing cost)
     *   product_view ×4   (researching features)
     *   wishlist_add ×12  (saving for later = intent)
     *   search       ×2   (targeted browsing)
     */
    private function intent(): int
    {
        return $this->clamp(
            $this->cnt('pricing_view', 30) * 15 +
            $this->cnt('product_view', 30) * 4  +
            $this->cnt('wishlist_add', 30) * 12 +
            $this->cnt('search', 30)       * 2
        );
    }

    /**
     * Platform engagement — how actively the user uses the product in last 30 days.
     *   login         ×4  (return visits)
     *   page_view     ×1  (breadth of exploration)
     *   feature_click ×3  (depth of usage)
     */
    private function engagement(): int
    {
        return $this->clamp(
            $this->cnt('login', 30)         * 4 +
            $this->cnt('page_view', 30)     * 1 +
            $this->cnt('feature_click', 30) * 3
        );
    }

    /**
     * Churn risk — higher score = more likely to leave.
     *   Inactivity:    days since last login × 1.2 (max 60 pts from 50 days)
     *   support_ticket ×12  (unhappy users raise tickets)
     *   unsubscribe    ×25  (strong signal of disengagement)
     *   negative_review ×8  (expressed dissatisfaction)
     */
    private function churn(): int
    {
        $inactivity = min(60, $this->daysSinceLogin()) * 1.2;
        $negatives  = $this->cnt('support_ticket', 30)  * 12
                    + $this->cnt('unsubscribe', 90)      * 25
                    + $this->cnt('negative_review', 90)  * 8;

        return $this->clamp($inactivity + $negatives);
    }

    /**
     * Loyalty — long-term positive relationship signals over 90 days.
     *   checkout_complete ×12  (repeat purchases)
     *   referral_send     ×22  (advocating = highest loyalty signal)
     *   positive_review   ×16  (publicly endorsing)
     *   login (all-time)  ×0.8 (sustained engagement baseline)
     */
    private function loyalty(): int
    {
        return $this->clamp(
            $this->cnt('checkout_complete', 90) * 12 +
            $this->cnt('referral_send', 90)     * 22 +
            $this->cnt('positive_review', 90)   * 16 +
            $this->cnt('login', 90)             * 0.8
        );
    }

    /**
     * Trust — willingness to share data and commit financially over 90 days.
     *   checkout_complete ×14  (trusted the platform with real money)
     *   cart_add          ×4   (intent to transact)
     *   email_click       ×3   (trusts our communications)
     */
    private function trust(): int
    {
        return $this->clamp(
            $this->cnt('checkout_complete', 90) * 14 +
            $this->cnt('cart_add', 90)          * 4  +
            $this->cnt('email_click', 90)       * 3
        );
    }

    /**
     * Frustration — higher score = more frustrated user over 90 days.
     *   support_ticket  ×16  (actively seeking help = friction)
     *   rage_click      ×14  (UI frustration detected)
     *   form_abandon    ×9   (gave up mid-flow)
     *   negative_review ×22  (vocalised frustration)
     */
    private function frustration(): int
    {
        return $this->clamp(
            $this->cnt('support_ticket', 90)  * 16 +
            $this->cnt('rage_click', 90)      * 14 +
            $this->cnt('form_abandon', 90)    * 9  +
            $this->cnt('negative_review', 90) * 22
        );
    }

    /**
     * Buying readiness — imminent purchase signals in last 30 days.
     *   cart_add        ×12  (selected items)
     *   checkout_start  ×20  (initiated payment flow)
     *   pricing_view    ×10  (evaluating cost right now)
     */
    private function buyingReadiness(): int
    {
        return $this->clamp(
            $this->cnt('cart_add', 30)        * 12 +
            $this->cnt('checkout_start', 30)  * 20 +
            $this->cnt('pricing_view', 30)    * 10
        );
    }

    /**
     * Drop-off risk — abandonment patterns over 90 days.
     *   abandoned checkouts (starts minus completes) ×25
     *   form_abandon ×12
     */
    private function dropoffRisk(): int
    {
        $abandons = max(0,
            $this->cnt('checkout_start', 90) - $this->cnt('checkout_complete', 90)
        );

        return $this->clamp(
            $abandons                      * 25 +
            $this->cnt('form_abandon', 90) * 12
        );
    }

    /**
     * Reactivation potential — dormant user showing signs of returning.
     *   Dormancy signal:     days inactive × 1.0 (max 60 pts at 60+ days)
     *   Re-engagement (14d): email_open ×15, email_click ×20, page_view ×2
     * A high score means: user has been away AND is starting to respond.
     */
    private function reactivation(): int
    {
        $dormancy     = min(60, $this->daysSinceLogin());
        $reengagement = min(50,
            $this->cnt('email_open', 14)  * 15 +
            $this->cnt('email_click', 14) * 20 +
            $this->cnt('page_view', 14)   * 2
        );

        return $this->clamp($dormancy + $reengagement);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function cnt(string $type, int $days = 90): int
    {
        $since = now()->subDays($days);
        return $this->events
            ->where('event_type', $type)
            ->filter(fn($e) => $e->created_at >= $since)
            ->count();
    }

    private function clamp(float $value): int
    {
        return max(0, min(100, (int) round($value)));
    }

    private function daysSinceLogin(): int
    {
        // abs() because Carbon diffInDays can be signed when lastLogin is fractionally in the future
        return $this->lastLogin
            ? max(0, (int) abs(now()->diffInDays($this->lastLogin)))
            : 90;
    }
}
