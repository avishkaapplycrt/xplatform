<?php

namespace App\Services;

use App\Models\BehavioralProfile;
use Illuminate\Support\Collection;

/**
 * Expected-Value decision model for L5 Decision Scenarios.
 *
 * For each user the engine computes P(success | action) from their actual
 * behavioural scores, then multiplies by the monetary outcome to get an
 * Expected Value (EV).  The recommended action is whichever EV is higher.
 *
 * This replaces hardcoded industry-average CVR rates (34%, 41%, 31%) with
 * per-user probability estimates that change as scores change — i.e. the
 * system is genuinely data-driven rather than benchmark-driven.
 *
 * P(convert) formula
 *   base_p = (buying_readiness × 0.55 + intent_score × 0.45) / 100 × 0.55
 *   Price-sensitive (trust < 65):
 *     full_price  → base_p × 0.72   (less likely to commit without incentive)
 *     discount    → base_p × 1.38   (discount significantly boosts conversion)
 *   Price-insensitive (trust ≥ 65):
 *     full_price  → base_p           (they'll buy at full price)
 *     discount    → base_p × 0.97   (marginal benefit — mostly wasted margin)
 */
class DecisionEngine
{
    // ── Per-user discount EV ──────────────────────────────────────────────────

    public function evDiscount(BehavioralProfile $p, float $dealValue, float $discountPct = 0.10): array
    {
        $priceSensitive = $p->trust_score < 65;

        $baseP      = ($p->buying_readiness * 0.55 + $p->intent_score * 0.45) / 100 * 0.55;
        $pFull      = $priceSensitive ? $baseP * 0.72  : $baseP;
        $pDisc      = $priceSensitive ? $baseP * 1.38  : $baseP * 0.97;

        $evFull     = $pFull * $dealValue;
        $evDisc     = $pDisc * ($dealValue * (1 - $discountPct));

        return [
            'recommend' => $evFull >= $evDisc ? 'full_price' : 'discount',
            'ev_full'   => round($evFull, 2),
            'ev_disc'   => round($evDisc, 2),
            'p_full'    => round($pFull * 100, 1),
            'p_disc'    => round($pDisc * 100, 1),
        ];
    }

    // ── Per-user churn-intervention EV ───────────────────────────────────────

    public function evChurnIntervention(BehavioralProfile $p, float $arr): array
    {
        // Retention probability decays with churn score; frustration amplifies it
        $baseFail   = $p->churn_score / 100;
        $frustMod   = min(0.15, $p->frustration_score / 100 * 0.2);
        $pNow       = max(0.05, (1 - $baseFail - $frustMod) * 0.90);
        $pWait      = max(0.02, $pNow * 0.38); // 72-hour window degrades ~62%

        return [
            'ev_now'  => round($pNow  * $arr, 0),
            'ev_wait' => round($pWait * $arr, 0),
            'p_now'   => round($pNow  * 100, 1),
            'p_wait'  => round($pWait * 100, 1),
        ];
    }

    // ── Per-user reactivation EV ─────────────────────────────────────────────

    public function evReactivation(BehavioralProfile $p, float $prevArr): array
    {
        $pPers = ($p->reactivation_potential / 100) * 0.46;
        $pGen  = $pPers * 0.25; // generic campaign ~4× less effective

        return [
            'ev_personalised' => round($pPers * $prevArr, 0),
            'ev_generic'      => round($pGen  * $prevArr, 0),
            'p_personalised'  => round($pPers * 100, 1),
            'p_generic'       => round($pGen  * 100, 1),
        ];
    }

    // ── Cohort aggregations (used by DecisionCentreController) ───────────────

    /**
     * Returns all L5 stats for $stats['l5'] by running per-user EV models
     * over the supplied cohorts rather than using hardcoded CVR rates.
     */
    public function computeL5Stats(
        Collection $hotBuyers,
        Collection $atRisk,
        Collection $reactivate,
        float      $avgDeal = 294
    ): array {
        // ── Discount scenario ────────────────────────────────────────────────
        $discDecisions  = $hotBuyers->map(fn($p) => $this->evDiscount($p, $avgDeal));
        $fullPriceUsers = $discDecisions->where('recommend', 'full_price');
        $discountUsers  = $discDecisions->where('recommend', 'discount');

        $fullPrice      = $fullPriceUsers->count();
        $discount       = $discountUsers->count();
        $total          = $hotBuyers->count();
        $fullPct        = $total > 0 ? (int) round($fullPrice / $total * 100) : 50;
        $discPct        = 100 - $fullPct;

        $netRevSmart    = (int) ($fullPriceUsers->sum('ev_full') + $discountUsers->sum('ev_disc'));
        $blanketCost    = (int) round($total    * $avgDeal * 0.10);
        $discountCost   = (int) round($discount * $avgDeal * 0.10);
        $netRevBlanket  = (int) ($discDecisions->sum('ev_disc')); // everyone gets discount
        $marginSaved    = max(0, $blanketCost - $discountCost);

        // ── Churn-intervention scenario ──────────────────────────────────────
        $churnDecisions = $atRisk->map(fn($p) =>
            $this->evChurnIntervention($p, max(1000, $p->overall_score * 620))
        );
        $retentionNow   = (int) $churnDecisions->sum('ev_now');
        $retentionWait  = (int) $churnDecisions->sum('ev_wait');
        $avgPNow        = round($churnDecisions->avg('p_now')  ?? 41, 1);
        $avgPWait       = round($churnDecisions->avg('p_wait') ?? 20, 1);

        // ── Reactivation scenario ────────────────────────────────────────────
        $prevArr        = $reactivate->count() > 0 ? 4200 : 0; // avg prev ARR per user
        $reactDecisions = $reactivate->map(fn($p) => $this->evReactivation($p, $prevArr));
        $avgPPers       = round($reactDecisions->avg('p_personalised') ?? 31, 1);
        $avgPGen        = round($reactDecisions->avg('p_generic')      ?? 8,  1);

        return [
            'fullPrice'      => $fullPrice,
            'fullPct'        => $fullPct,
            'discount'       => $discount,
            'discPct'        => $discPct,
            'netRevSmart'    => $netRevSmart,
            'netRevBlanket'  => $netRevBlanket,
            'discountCost'   => $discountCost,
            'blanketCost'    => $blanketCost,
            'marginSaved'    => $marginSaved,
            'retentionNow'   => $retentionNow,
            'retentionWait'  => $retentionWait,
            'avgPNow'        => $avgPNow,
            'avgPWait'       => $avgPWait,
            'avgPPers'       => $avgPPers,
            'avgPGen'        => $avgPGen,
        ];
    }
}
