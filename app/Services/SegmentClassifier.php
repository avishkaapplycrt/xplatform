<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Rule-based ML classifier that assigns a behavioural segment from computed scores.
 *
 * Uses a priority-ordered decision tree — same conceptual structure as a trained
 * decision tree classifier, but with domain-informed thresholds instead of learned ones.
 *
 * Priority order:
 *   1. At Risk  — churn signal overrides everything else
 *   2. Dormant  — inactive + low engagement
 *   3. Champion — high loyalty + engagement + intent, no churn signal
 *   4. Loyal    — solid recurring customer
 *   5. New      — all activity very recent
 *   6. Default  — residual classification by churn score
 */
class SegmentClassifier
{
    public function classify(array $scores, ?Carbon $lastLogin): string
    {
        $daysSince = $lastLogin
            ? max(0, (int) abs(now()->diffInDays($lastLogin)))
            : 90;

        // 1. Definite churn signal
        if ($scores['churn_score'] >= 65) {
            return 'at_risk';
        }

        // 2. Dormant — inactive and disengaged but not actively churning
        if ($daysSince >= 35 && $scores['engagement_score'] < 30) {
            return 'dormant';
        }

        // 3. Champion — sustained high performance across all positive dimensions
        if (
            $scores['loyalty_score']    >= 68 &&
            $scores['engagement_score'] >= 60 &&
            $scores['intent_score']     >= 65 &&
            $scores['churn_score']       < 25
        ) {
            return 'champion';
        }

        // 4. Loyal — solid recurring customer with low risk
        if (
            $scores['loyalty_score']    >= 48 &&
            $scores['engagement_score'] >= 40 &&
            $scores['churn_score']       < 50
        ) {
            return 'loyal';
        }

        // 5. New — all activity very recent
        if ($daysSince <= 10) {
            return 'new';
        }

        // 6. Residual — moderate risk defaults
        return $scores['churn_score'] >= 40 ? 'at_risk' : 'loyal';
    }
}
