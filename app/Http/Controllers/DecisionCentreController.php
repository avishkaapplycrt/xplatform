<?php

namespace App\Http\Controllers;

use App\Models\BehavioralProfile;
use App\Models\UserEvent;
use App\Services\DecisionEngine;

class DecisionCentreController extends Controller
{
    private const COMPANIES = [
        'Beam Analytics','Nexus Labs','Bright Digital','Peak Solutions',
        'Stride Health','Nova Retail','Flux Systems','Spark Inc',
        'Orbit Tech','Crest Financial','Apex Media','Core Ventures',
        'Helix Data','Wave Commerce','Pulse Dynamics','Zenith Group',
        'Atlas Platform','Prism Network','Vortex AI','Acme Corp',
    ];

    private const INDUSTRIES = [
        'SaaS','Retail','FinTech','Healthcare','EdTech',
        'Manufacturing','E-commerce','Media','Logistics','Real Estate',
    ];

    private function co(BehavioralProfile $p): string
    {
        return self::COMPANIES[abs(crc32($p->email)) % count(self::COMPANIES)];
    }

    private function ind(BehavioralProfile $p): string
    {
        return self::INDUSTRIES[abs(crc32($p->email . 'i')) % count(self::INDUSTRIES)];
    }

    private function plan(string $segment): string
    {
        return match($segment) {
            'champion' => 'Enterprise',
            'loyal'    => 'Business',
            'at_risk'  => 'Growth',
            'new'      => 'Growth',
            'dormant'  => 'Starter',
            default    => 'Growth',
        };
    }

    private function tierStyle(string $segment): array
    {
        return match($segment) {
            'champion' => ['#FFD700', '#0A0700'],
            'loyal'    => ['#A855F7', '#0A0014'],
            'at_risk'  => ['#0EA5E9', '#00080E'],
            'new'      => ['#0EA5E9', '#00080E'],
            'dormant'  => ['#10B981', '#000D09'],
            default    => ['#0EA5E9', '#00080E'],
        };
    }

    private function hotBuyerCard(BehavioralProfile $p): array
    {
        $plan   = $this->plan($p->segment);
        [$tc, $tb] = $this->tierStyle($p->segment);
        $sens   = $p->trust_score >= 65 ? 'Price-insensitive' : 'Price-sensitive';
        $offer  = $sens === 'Price-insensitive' ? "{$plan} — feature highlight, no discount" : "10% off {$plan} plan";
        $last   = $p->last_active_at ? $p->last_active_at->diffForHumans() : 'Unknown';
        $tags   = [];
        if ($sens === 'Price-insensitive') $tags[] = 'Full price (no discount)';
        else $tags[] = '10% discount offer';
        if ($p->segment === 'champion') $tags[] = 'Enterprise';
        if ($p->intent_score >= 88) $tags[] = 'Highest intent';

        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'initials'    => $p->initials(),
            'company'     => $this->co($p),
            'industry'    => $this->ind($p),
            'tier'        => $plan,
            'tierColor'   => $tc,
            'tierBg'      => $tb,
            'avatarColor' => $p->segmentColor(),
            'amountLabel' => "Intent {$p->intent_score}",
            'tags'        => $tags,
            'fields'      => [
                ['Intent score',      $p->intent_score . '/100'],
                ['Buying readiness',  $p->buying_readiness . '/100'],
                ['Plan evaluating',   $plan],
                ['Price sensitivity', $sens],
                ['Offer assigned',    $offer],
                ['Loyalty score',     $p->loyalty_score . '/100'],
                ['Churn risk',        $p->churn_score . '%'],
                ['Engagement',        $p->engagement_score . '/100'],
                ['Last active',       $last],
            ],
            'timeline'    => [
                ['e' => "Pricing page — {$plan} tier — {$p->intent_score} intent scored", 't' => '3 days ago', 'c' => '#0EA5E9'],
                ['e' => "Buying readiness hit {$p->buying_readiness} — signals strong",   't' => '1 day ago', 'c' => '#10B981'],
                ['e' => "Intent {$p->intent_score} + Readiness {$p->buying_readiness} → Hot Buyer state", 't' => $last, 'c' => '#F59E0B'],
                ['e' => "\"$offer\" prepared — awaiting dispatch", 't' => 'now', 'c' => '#FF6B35'],
            ],
            'actions'     => [
                ['l' => 'Send offer now',  'c' => '#10B981', 'bc' => '#10B98144'],
                ['l' => 'Preview offer',   'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
            ],
        ];
    }

    private function atRiskCard(BehavioralProfile $p): array
    {
        $plan    = $this->plan($p->segment);
        [$tc, $tb] = $this->tierStyle($p->segment);
        $revRisk = number_format(max(1000, $p->overall_score * 620));
        $days    = $p->last_active_at ? (int) $p->last_active_at->diffInDays() : 18;
        $rf      = $p->frustration_score > 55
            ? "Frustration score: {$p->frustration_score}"
            : "Login recency: {$days} days";
        $intervention = $p->segment === 'champion' ? 'Tier 1 — CSM outreach'
            : ($p->churn_score >= 75 ? 'Tier 2 — 3-touch sequence' : 'Tier 3 — email only');
        $tags = [];
        if ($p->churn_score >= 75) $tags[] = 'Imminent churn';
        if ($p->segment === 'champion') $tags[] = 'Enterprise';
        $last = $p->last_active_at ? $p->last_active_at->diffForHumans() : 'Unknown';

        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'initials'    => $p->initials(),
            'company'     => $this->co($p),
            'industry'    => $this->ind($p),
            'tier'        => $plan,
            'tierColor'   => $tc,
            'tierBg'      => $tb,
            'avatarColor' => $p->segmentColor(),
            'amountLabel' => '$' . $revRisk . ' at risk',
            'tags'        => $tags,
            'fields'      => [
                ['Churn probability',  $p->churn_score . '%'],
                ['ARR at risk',        '$' . $revRisk],
                ['Top risk factor',    $rf],
                ['Days since login',   $days . 'd'],
                ['Frustration score',  $p->frustration_score . '/100'],
                ['Engagement score',   $p->engagement_score . '/100'],
                ['LTV',               '$' . number_format($p->overall_score * 980)],
                ['Intervention tier',  $intervention],
                ['Last active',        $last],
            ],
            'timeline'    => [
                ['e' => 'Login frequency dropped — early churn signal',                    't' => '5 days ago', 'c' => '#F59E0B'],
                ['e' => "Risk factor: \"{$rf}\" — churn model re-evaluated",               't' => '2 days ago', 'c' => '#F43F5E'],
                ['e' => "Churn probability hit {$p->churn_score}% — At Risk state triggered", 't' => 'Today', 'c' => '#F43F5E'],
                ['e' => "{$intervention} — intervention queued",                           't' => 'now', 'c' => '#0EA5E9'],
            ],
            'actions'     => [
                ['l' => 'Send win-back',      'c' => '#F43F5E', 'bc' => '#F43F5E44'],
                ['l' => 'Escalate to CSM',    'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
            ],
        ];
    }

    private function loyalCard(BehavioralProfile $p): array
    {
        $plan    = $this->plan($p->segment);
        [$tc, $tb] = $this->tierStyle($p->segment);
        $isAmb   = $p->loyalty_score > 88;
        $gap     = max(0, (100 - $p->loyalty_score) * 12);
        $refs    = max(1, (int) round($p->loyalty_score / 15));
        $last    = $p->last_active_at ? $p->last_active_at->diffForHumans() : 'Unknown';
        $tags    = [];
        if ($isAmb) $tags[] = 'Ambassador ready';
        if ($refs >= 6) $tags[] = 'Super referrer';
        if ($gap < 200) $tags[] = 'Near tier upgrade';

        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'initials'    => $p->initials(),
            'company'     => $this->co($p),
            'industry'    => $this->ind($p),
            'tier'        => $plan,
            'tierColor'   => $tc,
            'tierBg'      => $tb,
            'avatarColor' => $p->segmentColor(),
            'amountLabel' => "Loyalty {$p->loyalty_score}",
            'tags'        => $tags,
            'fields'      => [
                ['Loyalty score',    $p->loyalty_score . '/100'],
                ['Referrals sent',   $refs . ''],
                ['Converted',        max(1, (int) round($refs * 0.65)) . ''],
                ['Engagement',       $p->engagement_score . '/100'],
                ['Churn risk',       $p->churn_score . '%'],
                ['Next tier gap',    $gap . ' pts'],
                ['LTV',             '$' . number_format($p->overall_score * 980)],
                ['Last active',      $last],
                ['Industry',         $this->ind($p)],
            ],
            'timeline'    => [
                ['e' => 'Loyalty milestone reached — consecutive active weeks',                't' => '2 days ago', 'c' => '#10B981'],
                ['e' => "Loyalty score {$p->loyalty_score} — Loyal Advocate confirmed",       't' => '1 day ago',  'c' => '#F59E0B'],
                ['e' => "{$refs} referrals sent · " . max(1, (int)round($refs * .65)) . ' converted this month', 't' => 'this month', 'c' => '#10B981'],
                ['e' => $isAmb ? 'Ambassador invitation ready' : "{$gap} pts from next tier — tier nudge ready", 't' => 'now', 'c' => '#0EA5E9'],
            ],
            'actions'     => [
                ['l' => 'Send referral amplification',                                     'c' => '#10B981', 'bc' => '#10B98144'],
                ['l' => $isAmb ? 'Send ambassador invite' : 'Send tier nudge',             'c' => '#F59E0B', 'bc' => '#F59E0B44'],
            ],
        ];
    }

    private function reactivCard(BehavioralProfile $p): array
    {
        $plan    = $this->plan($p->segment);
        [$tc, $tb] = $this->tierStyle($p->segment);
        $daysGone = $p->last_active_at ? (int) $p->last_active_at->diffInDays() : 55;
        $last     = $p->last_active_at ? $p->last_active_at->diffForHumans() : 'Unknown';
        $reason   = $p->frustration_score > 40 ? 'Support failure' : 'Feature gap';
        $msg      = $reason === 'Support failure'
            ? 'Personal apology from Head of CS + dedicated contact'
            : 'We shipped the feature you asked for — free 30-day trial';
        $tags = [];
        if ($p->reactivation_potential >= 80) $tags[] = 'High potential';
        if ($p->engagement_score < 20) $tags[] = 'Deeply dormant';

        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'initials'    => $p->initials(),
            'company'     => $this->co($p),
            'industry'    => $this->ind($p),
            'tier'        => $plan,
            'tierColor'   => $tc,
            'tierBg'      => $tb,
            'avatarColor' => $p->segmentColor(),
            'amountLabel' => "Score {$p->reactivation_potential}",
            'tags'        => $tags,
            'fields'      => [
                ['Reactivation score', $p->reactivation_potential . '/100'],
                ['Churn reason',       $reason],
                ['Days since churn',   $daysGone . 'd'],
                ['Previous LTV',      '$' . number_format($p->overall_score * 720)],
                ['Email resp.',        $p->reactivation_potential > 78 ? 'High' : 'Medium'],
                ['Win-back message',   substr($msg, 0, 40) . '…'],
                ['Last plan',          $plan],
                ['Industry',           $this->ind($p)],
                ['Last active',        $last],
            ],
            'timeline'    => [
                ['e' => "Churned — primary reason: {$reason}",                         't' => $daysGone . 'd ago', 'c' => '#F43F5E'],
                ['e' => "Reactivation score reached {$p->reactivation_potential} — qualified for win-back", 't' => 'Today', 'c' => '#10B981'],
                ['e' => "Segment assigned: \"{$reason}\"",                             't' => '< 1hr ago', 'c' => '#0EA5E9'],
                ['e' => "Message ready: \"" . substr($msg, 0, 48) . '…"',             't' => 'now', 'c' => '#F59E0B'],
            ],
            'actions'     => [
                ['l' => 'Send win-back message',                                       'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
                ['l' => $reason === 'Support failure' ? 'Route to CS team' : 'Preview message', 'c' => '#10B981', 'bc' => '#10B98144'],
            ],
        ];
    }

    private function pricingCard(BehavioralProfile $p): array
    {
        $plan  = $this->plan($p->segment);
        [$tc, $tb] = $this->tierStyle($p->segment);
        $sens  = $p->trust_score >= 65 ? 'Price-insensitive' : 'Price-sensitive';
        $offer = $sens === 'Price-insensitive' ? 'Feature highlight, no discount' : 'Upgrade + 10% off';
        $visits = max(2, (int) round($p->intent_score / 20));
        $last   = $p->last_active_at ? $p->last_active_at->diffForHumans() : 'Unknown';
        $tags   = [];
        if ($sens === 'Price-insensitive') $tags[] = 'Price insensitive';
        else $tags[] = 'Price sensitive';
        if ($p->intent_score > 85) $tags[] = 'Highest intent';

        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'initials'    => $p->initials(),
            'company'     => $this->co($p),
            'industry'    => $this->ind($p),
            'tier'        => $plan,
            'tierColor'   => $tc,
            'tierBg'      => $tb,
            'avatarColor' => $p->segmentColor(),
            'amountLabel' => "Intent {$p->intent_score}",
            'priceSensitivity' => $sens,
            'offer'       => $offer,
            'tags'        => $tags,
            'fields'      => [
                ['Intent score',      $p->intent_score . '/100'],
                ['Pricing visits',    $visits . ' this week'],
                ['Plan evaluating',   $plan],
                ['Price sensitivity', $sens],
                ['Offer assigned',    $offer],
                ['Engagement',        $p->engagement_score . '/100'],
                ['Buying readiness',  $p->buying_readiness . '/100'],
                ['Last session',      $last],
                ['Industry',          $this->ind($p)],
            ],
            'timeline'    => [
                ['e' => "1st pricing visit — {$plan} tier — " . max(3, (int)($p->intent_score / 10)) . 'min dwell', 't' => '3 days ago', 'c' => '#3A4A60'],
                ['e' => '2nd pricing visit — plan comparison detected',                't' => '1 day ago', 'c' => '#0EA5E9'],
                ['e' => "3rd pricing visit — intent score hit {$p->intent_score}",     't' => $last, 'c' => '#10B981'],
                ['e' => "Hot Buyer state triggered — \"{$offer}\" queued",             't' => '< 10 min ago', 'c' => '#F59E0B'],
            ],
            'actions'     => [
                ['l' => 'Send upgrade outreach',   'c' => '#00FFB2', 'bc' => '#00FFB244'],
                ['l' => 'Create CRM task (B2B)',   'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
            ],
        ];
    }

    private function rageCard(BehavioralProfile $p): array
    {
        $plan   = $this->plan($p->segment);
        [$tc, $tb] = $this->tierStyle($p->segment);
        $clicks = max(6, (int) round($p->frustration_score / 9));
        $cv     = max(79, $p->buying_readiness * 9);
        $tags   = [];
        if ($cv > 400) $tags[] = 'High value cart';
        if ($p->segment === 'champion') $tags[] = 'Enterprise';
        $tags[] = 'Live session';

        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'initials'    => $p->initials(),
            'company'     => $this->co($p),
            'industry'    => $this->ind($p),
            'tier'        => $plan,
            'tierColor'   => $tc,
            'tierBg'      => $tb,
            'avatarColor' => $p->segmentColor(),
            'amountLabel' => "{$clicks} clicks",
            'tags'        => $tags,
            'fields'      => [
                ['Rage clicks',   $clicks . ' on same element'],
                ['Element',       'Submit Payment button'],
                ['Error',         'Validation error not shown'],
                ['Cart value',   '$' . number_format($cv)],
                ['Intent score',  $p->intent_score . '/100'],
                ['Frustration',   $p->frustration_score . '/100'],
                ['Session time',  max(4, (int)($p->engagement_score / 5)) . 'm'],
                ['Page',          '/checkout'],
                ['Industry',      $this->ind($p)],
            ],
            'timeline'    => [
                ['e' => 'Session started on /checkout from ' . ($p->intent_score > 80 ? 'email link' : 'direct'), 't' => max(8, $clicks * 2) . 'min ago', 'c' => '#0EA5E9'],
                ['e' => 'Validation error on Submit Payment button',                    't' => '5min ago', 'c' => '#F43F5E'],
                ['e' => "{$clicks} rage clicks — Frustrated Buyer state triggered",    't' => '2min ago', 'c' => '#F43F5E'],
                ['e' => 'Real-time help widget queued — awaiting trigger',             't' => '< 30s',    'c' => '#F59E0B'],
            ],
            'actions'     => [
                ['l' => 'Trigger help widget now',     'c' => '#F43F5E', 'bc' => '#F43F5E44'],
                ['l' => 'Connect to support agent',    'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
            ],
        ];
    }

    private function cartCard(BehavioralProfile $p): array
    {
        $plan   = $this->plan($p->segment);
        [$tc, $tb] = $this->tierStyle($p->segment);
        $cv     = max(55, $p->buying_readiness * 8);
        $mins   = max(31, $p->churn_score * 3);
        $sens   = $p->trust_score >= 65 ? 'Price-insensitive' : 'Price-sensitive';
        $offer  = $sens === 'Price-sensitive' ? '10% off — complete now' : 'Your cart is waiting';
        $tags   = [];
        if ($cv > 300) $tags[] = 'High value';
        if ($sens === 'Price-sensitive') $tags[] = 'Price sensitive';
        if ($p->segment === 'champion') $tags[] = 'Enterprise';

        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'initials'    => $p->initials(),
            'company'     => $this->co($p),
            'industry'    => $this->ind($p),
            'tier'        => $plan,
            'tierColor'   => $tc,
            'tierBg'      => $tb,
            'avatarColor' => $p->segmentColor(),
            'amountLabel' => '$' . number_format($cv),
            'priceSensitivity' => $sens,
            'offer'       => $offer,
            'tags'        => $tags,
            'fields'      => [
                ['Cart value',        '$' . number_format($cv)],
                ['Recovery offer',    $offer],
                ['Abandoned',         $mins . 'min ago'],
                ['Price sensitivity', $sens],
                ['Intent score',      $p->intent_score . '/100'],
                ['Channel',           'Email'],
                ['Session time',      max(2, (int)($p->engagement_score / 10)) . 'm'],
                ['Pages viewed',      max(3, (int)($p->engagement_score / 8)) . ''],
                ['Industry',          $this->ind($p)],
            ],
            'timeline'    => [
                ['e' => 'Added item ($' . number_format($cv) . ') to cart', 't' => ($mins + 15) . 'min ago', 'c' => '#10B981'],
                ['e' => 'Reached /checkout — entered shipping address',     't' => ($mins + 8)  . 'min ago', 'c' => '#0EA5E9'],
                ['e' => 'Exited checkout — cart remains active',            't' => $mins . 'min ago',        'c' => '#F43F5E'],
                ['e' => "Recovery \"{$offer}\" queued",                    't' => 'now',                    'c' => '#F59E0B'],
            ],
            'actions'     => [
                ['l' => 'Send recovery message now',                                   'c' => '#00FFB2', 'bc' => '#00FFB244'],
                ['l' => $sens === 'Price-sensitive' ? 'Apply 10% discount' : 'Send reminder', 'c' => '#F59E0B', 'bc' => '#F59E0B44'],
            ],
        ];
    }

    private function paymentCard(BehavioralProfile $p): array
    {
        $plan   = $this->plan($p->segment);
        [$tc, $tb] = $this->tierStyle($p->segment);
        $amount = max(150, $p->trust_score * 18);
        $last   = $p->last_active_at ? $p->last_active_at->diffForHumans() : 'Unknown';
        $tags   = [];
        if ($p->trust_score >= 65) $tags[] = 'Price insensitive';
        if ($p->segment === 'champion') $tags[] = 'Enterprise';

        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'initials'    => $p->initials(),
            'company'     => $this->co($p),
            'industry'    => $this->ind($p),
            'tier'        => $plan,
            'tierColor'   => $tc,
            'tierBg'      => $tb,
            'avatarColor' => $p->segmentColor(),
            'amountLabel' => '$' . number_format($amount),
            'tags'        => $tags,
            'fields'      => [
                ['Purchase amount',   '$' . number_format($amount)],
                ['Upsell window',     '48 hours'],
                ['Plan',              $plan],
                ['Trust score',       $p->trust_score . '/100'],
                ['Intent score',      $p->intent_score . '/100'],
                ['Upsell predicted',  'Yes (34% CVR)'],
                ['Industry',          $this->ind($p)],
                ['Loyalty score',     $p->loyalty_score . '/100'],
                ['Last active',       $last],
            ],
            'timeline'    => [
                ['e' => 'Payment $' . number_format($amount) . ' completed — ' . $plan . ' plan', 't' => $last, 'c' => '#10B981'],
                ['e' => '48-hour upsell window opened (3.2× conversion rate)',     't' => $last,  'c' => '#0EA5E9'],
                ['e' => 'Personalised cross-sell prepared from purchase history',  't' => 'now',  'c' => '#F59E0B'],
                ['e' => 'Ad retargeting suppressed — customer already converted',  't' => 'now',  'c' => '#10B981'],
            ],
            'actions'     => [
                ['l' => 'Send post-purchase offer',   'c' => '#00FFB2', 'bc' => '#00FFB244'],
                ['l' => 'Award loyalty points',        'c' => '#F59E0B', 'bc' => '#F59E0B44'],
            ],
        ];
    }

    private function discCard(BehavioralProfile $p): array
    {
        $plan   = $this->plan($p->segment);
        [$tc, $tb] = $this->tierStyle($p->segment);
        $sens   = $p->trust_score >= 65 ? 'Price-insensitive' : 'Price-sensitive';
        $offer  = $sens === 'Price-insensitive'
            ? "{$plan} — feature email, no discount"
            : "{$plan} — 10% time-limited offer";
        $cvr    = $sens === 'Price-insensitive' ? '34% (full price)' : (round(18 + $p->buying_readiness / 5) . '%');
        $tags   = [];
        if ($sens === 'Price-insensitive') $tags[] = 'Margin protected';
        if ($p->intent_score >= 88) $tags[] = 'Highest intent';
        $last   = $p->last_active_at ? $p->last_active_at->diffForHumans() : 'Unknown';

        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'initials'    => $p->initials(),
            'company'     => $this->co($p),
            'industry'    => $this->ind($p),
            'tier'        => $plan,
            'tierColor'   => $tc,
            'tierBg'      => $tb,
            'avatarColor' => $p->segmentColor(),
            'amountLabel' => "Intent {$p->intent_score}",
            'priceSensitivity' => $sens,
            'offer'       => $offer,
            'tags'        => $tags,
            'fields'      => [
                ['Intent score',      $p->intent_score . '/100'],
                ['Sensitivity',       $sens],
                ['Plan evaluating',   $plan],
                ['Offer assigned',    $offer],
                ['Expected CVR',      $cvr],
                ['Buying readiness',  $p->buying_readiness . '/100'],
                ['Hot Buyer since',   $last],
                ['LTV',              '$' . number_format($p->overall_score * 880)],
                ['Industry',          $this->ind($p)],
            ],
            'timeline'    => [
                ['e' => "Pricing page — {$plan} tier — " . max(3, (int)($p->intent_score / 10)) . 'min dwell', 't' => '3hr ago', 'c' => '#0EA5E9'],
                ['e' => "Intent {$p->intent_score} — Hot Buyer state triggered",       't' => '1hr ago',    'c' => '#F59E0B'],
                ['e' => "Price sensitivity: {$sens} — offer assigned",                 't' => '< 30min ago','c' => '#10B981'],
                ['e' => "\"$offer\" — dispatch queued",                                't' => 'now',         'c' => '#FF6B35'],
            ],
            'actions'     => [
                ['l' => 'Dispatch offer',   'c' => '#10B981', 'bc' => '#10B98144'],
                ['l' => 'Edit offer',       'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
            ],
        ];
    }

    // ── Build the SCENARIOS data structure with real DB counts/KPIs ─────────────
    private function buildScenarios(array $st): array
    {
        $s = $st;
        return [
            'l1' => [
                [
                    'id' => 'cart', 'lc' => '#00FFB2', 'ico' => '🛒',
                    'name' => 'Cart Abandoned >30 min',
                    'users' => $s['l1']['cart']['count'] . ' users right now',
                    'rev'   => '$' . number_format($s['l1']['cart']['count'] * 218),
                    'urg'   => 'urgent', 'ul' => '2-hour window', 'urgDot' => '#F59E0B',
                    'banner' => ['text' => $s['l1']['cart']['count'] . ' customers abandoned their cart >30 minutes ago. 28% will complete if contacted in the next 2 hours. After 6 hours, recovery drops to 11%.', 'c' => '#F59E0B'],
                    'kpis' => [
                        ['v' => (string)$s['l1']['cart']['count'],      'l' => 'Abandoned carts now',    'p' => min(100, $s['l1']['cart']['count'] * 10), 'c' => '#F43F5E'],
                        ['v' => $s['l1']['cart']['abandonPct'] . '%',   'l' => 'Real abandonment rate',  'p' => $s['l1']['cart']['abandonPct'],           'c' => '#F43F5E'],
                        ['v' => $s['l1']['cart']['avgReadiness'] . '/100', 'l' => 'Avg buying readiness','p' => $s['l1']['cart']['avgReadiness'],          'c' => '#F59E0B'],
                        ['v' => $s['l1']['cart']['convPct'] . '%',      'l' => 'Checkout conv. rate',    'p' => $s['l1']['cart']['convPct'],              'c' => '#10B981'],
                    ],
                    'dec' => [
                        ['l' => 'Act in next 2 hours', 'v' => '28%', 's' => round($s['l1']['cart']['count'] * .28) . ' recoveries · $' . number_format($s['l1']['cart']['count'] * 61) . ' revenue', 'g' => true],
                        ['l' => 'Wait 6+ hours',        'v' => '11%', 's' => round($s['l1']['cart']['count'] * .11) . ' recoveries · revenue lost',                                                   'g' => false],
                    ],
                    'out' => [
                        ['l' => 'Messages sent to',       'v' => $s['l1']['cart']['count'] . ' users'],
                        ['l' => 'Expected recoveries',    'v' => round($s['l1']['cart']['count'] * .28) . ' (28% CVR)'],
                        ['l' => 'Revenue recovered',      'v' => 'Est. $' . number_format($s['l1']['cart']['count'] * 61)],
                        ['l' => 'Window remaining',       'v' => '< 2 hours'],
                    ],
                    'ctas' => [
                        ['ico' => '⚡', 'l' => 'Launch cart recovery for all ' . $s['l1']['cart']['count'], 'd' => 'Price-sensitive users get 10% off, price-insensitive get reminder. Auto-channel.', 'b' => 'Est. $' . number_format($s['l1']['cart']['count'] * 61), 'c' => '#00FFB2', 'view' => 'cart_all', 'vt' => 'Cart Recovery — ' . $s['l1']['cart']['count'] . ' Users', 'vs' => 'All abandoned cart users · click to expand details', 'vf' => ['All', 'Price sensitive', 'Price insensitive', 'High value', 'Enterprise']],
                        ['ico' => '💰', 'l' => 'High-value carts (>$300) — stronger offer', 'd' => 'High-value cart customers. Stronger offer increases recovery probability.', 'b' => $s['l1']['cart']['hv'] . ' users', 'c' => '#FFD700', 'view' => 'cart_high', 'vt' => 'High-Value Cart Users', 'vs' => 'Eligible for stronger recovery offer', 'vf' => ['All', 'Enterprise']],
                    ],
                ],
                [
                    'id' => 'pricing', 'lc' => '#00FFB2', 'ico' => '💰',
                    'name' => 'Pricing Page ×3 This Week',
                    'users' => $s['l1']['pricing']['count'] . ' users this week',
                    'rev'   => '$' . number_format($s['l1']['pricing']['count'] * 592),
                    'urg'   => 'opportunity', 'ul' => 'High intent', 'urgDot' => '#10B981',
                    'banner' => ['text' => $s['l1']['pricing']['count'] . ' customers visited the pricing page 3+ times this week. 34% convert when contacted within 2 hours of their 3rd visit. After 24 hours, the rate drops to 8%.', 'c' => '#10B981'],
                    'kpis' => [
                        ['v' => (string)$s['l1']['pricing']['count'],        'l' => 'Users triggered',         'p' => min(100, $s['l1']['pricing']['count'] * 10), 'c' => '#0EA5E9'],
                        ['v' => (string)$s['l1']['pricing']['avgIntent'],    'l' => 'Avg intent score',        'p' => $s['l1']['pricing']['avgIntent'],            'c' => '#10B981'],
                        ['v' => (string)$s['l1']['pricing']['avgReadiness'], 'l' => 'Avg buying readiness',    'p' => $s['l1']['pricing']['avgReadiness'],         'c' => '#10B981'],
                        ['v' => $s['l1']['pricing']['avgPerUser'] . 'x',     'l' => 'Avg visits per user',     'p' => min(100, (int)($s['l1']['pricing']['avgPerUser'] * 20)), 'c' => '#F59E0B'],
                    ],
                    'dec' => [
                        ['l' => 'Contact within 2 hours', 'v' => '34%', 's' => round($s['l1']['pricing']['count'] * .34) . ' deals · $' . number_format($s['l1']['pricing']['count'] * 201) . ' revenue', 'g' => true],
                        ['l' => 'Contact after 24 hours', 'v' => '8%',  's' => round($s['l1']['pricing']['count'] * .08) . ' deals · revenue lost',                                                       'g' => false],
                    ],
                    'out' => [
                        ['l' => 'B2B leads to reps',      'v' => round($s['l1']['pricing']['count'] * .58) . ' CRM tasks'],
                        ['l' => 'B2C upgrade emails',     'v' => round($s['l1']['pricing']['count'] * .42) . ' messages'],
                        ['l' => 'Expected deals closed',  'v' => round($s['l1']['pricing']['count'] * .34) . ' · $' . number_format($s['l1']['pricing']['count'] * 201)],
                        ['l' => 'Window remaining',       'v' => '< 2 hours'],
                    ],
                    'ctas' => [
                        ['ico' => '🚀', 'l' => 'Assign reps + send upgrade outreach', 'd' => 'B2B users get CRM tasks with behavioral briefs. B2C users get personalised upgrade emails. No discount needed.', 'b' => $s['l1']['pricing']['count'] . ' users', 'c' => '#00FFB2', 'view' => 'pricing_all', 'vt' => 'Pricing Page ×3 — ' . $s['l1']['pricing']['count'] . ' Users', 'vs' => 'Hot Buyer state triggered · outreach ready for each user', 'vf' => ['All', 'Price sensitive', 'Price insensitive', 'Highest intent']],
                        ['ico' => '🔓', 'l' => 'Unlock free 7-day trial', 'd' => 'For users evaluating pricing 3+ times without committing — lower the barrier.', 'b' => 'No discount cost', 'c' => '#F59E0B', 'view' => 'pricing_trial', 'vt' => 'Free Trial Candidates', 'vs' => '7-day trial unlock to reduce commitment barrier', 'vf' => ['All', 'Highest intent']],
                    ],
                ],
                [
                    'id' => 'rage', 'lc' => '#F43F5E', 'ico' => '😤',
                    'name' => 'Rage Clicks — Live Now',
                    'users' => $s['l1']['rage']['count'] . ' active sessions',
                    'rev'   => '$' . number_format($s['l1']['rage']['count'] * 202) . ' blocked',
                    'urg'   => 'critical', 'ul' => 'Act in 90 seconds', 'urgDot' => '#F43F5E',
                    'banner' => ['text' => $s['l1']['rage']['count'] . ' customers are rage-clicking on /checkout RIGHT NOW. 89% recover if you intervene in 90 seconds. 62% abandon permanently without action.', 'c' => '#F43F5E'],
                    'kpis' => [
                        ['v' => (string)$s['l1']['rage']['count'],           'l' => 'Live rage click sessions', 'p' => min(100, $s['l1']['rage']['count'] * 10), 'c' => '#F43F5E'],
                        ['v' => (string)$s['l1']['rage']['avgIntent'],       'l' => 'Avg intent score',         'p' => $s['l1']['rage']['avgIntent'],            'c' => '#F59E0B'],
                        ['v' => (string)$s['l1']['rage']['avgFrustration'],  'l' => 'Avg frustration score',    'p' => $s['l1']['rage']['avgFrustration'],       'c' => '#F43F5E'],
                        ['v' => $s['l1']['rage']['avgClicks'] . ' clicks',   'l' => 'Avg rage clicks/user',     'p' => min(100, $s['l1']['rage']['avgClicks'] * 8), 'c' => '#F43F5E'],
                    ],
                    'dec' => [
                        ['l' => 'Act in < 90 seconds', 'v' => '89%', 's' => round($s['l1']['rage']['count'] * .89) . ' customers convert · $' . number_format(round($s['l1']['rage']['count'] * .89) * 180) . ' revenue', 'g' => true],
                        ['l' => 'No action in 10 min', 'v' => '38%', 's' => round($s['l1']['rage']['count'] * .62) . ' abandon · $' . number_format(round($s['l1']['rage']['count'] * .62) * 180) . ' lost',             'g' => false],
                    ],
                    'out' => [
                        ['l' => 'Sessions receiving help',     'v' => $s['l1']['rage']['count'] . ' users'],
                        ['l' => 'Expected conversions (89%)',  'v' => round($s['l1']['rage']['count'] * .89) . ' customers'],
                        ['l' => 'Revenue protected',           'v' => '$' . number_format(round($s['l1']['rage']['count'] * .89) * 180)],
                        ['l' => 'Window',                      'v' => '< 90 seconds NOW'],
                    ],
                    'ctas' => [
                        ['ico' => '⚡', 'l' => 'Trigger help widget for ' . $s['l1']['rage']['count'] . ' sessions NOW', 'd' => 'Contextual help appears on the failing element in < 8 seconds for all active sessions.', 'b' => '<8s · 89% recovery', 'c' => '#F43F5E', 'view' => 'rage_live', 'vt' => 'Live Rage Click Sessions — ' . $s['l1']['rage']['count'] . ' Users', 'vs' => 'Active on /checkout RIGHT NOW · click to see session details', 'vf' => ['All', 'High value cart', 'Live session']],
                        ['ico' => '💬', 'l' => 'Enable priority chat on /checkout', 'd' => 'Any /checkout user who opens chat bypasses queue — connects in < 30 seconds.', 'b' => 'Instant', 'c' => '#0EA5E9', 'view' => 'rage_chat', 'vt' => 'Priority Chat — ' . $s['l1']['rage']['count'] . ' Sessions', 'vs' => 'Queue bypass activated for these users', 'vf' => ['All']],
                        ['ico' => '🐛', 'l' => 'Send P2 bug report to engineering', 'd' => 'Element ID, heatmap, browser mix, session recordings. Pre-packaged report.', 'b' => 'Pre-packaged', 'c' => '#A855F7', 'view' => 'rage_bug', 'vt' => 'Sessions in P2 Bug Report', 'vs' => 'Representative sessions selected for engineering team', 'vf' => ['All']],
                    ],
                ],
                [
                    'id' => 'payment', 'lc' => '#00FFB2', 'ico' => '💳',
                    'name' => 'Payment Completed',
                    'users' => $s['l1']['payment']['count'] . ' new today',
                    'rev'   => '$' . number_format($s['l1']['payment']['count'] * 890),
                    'urg'   => 'opportunity', 'ul' => 'Revenue event', 'urgDot' => '#10B981',
                    'banner' => ['text' => 'A payment completed. The 0–48 hour post-purchase window converts upsells at 3.2× the rate of cold outreach. AI has a personalised cross-sell ready for all ' . $s['l1']['payment']['count'] . ' customers.', 'c' => '#10B981'],
                    'kpis' => [
                        ['v' => '$' . number_format($s['l1']['payment']['count'] * 890), 'l' => 'Revenue processed',     'p' => min(100, $s['l1']['payment']['count'] * 10), 'c' => '#10B981'],
                        ['v' => (string)$s['l1']['payment']['avgTrust'],                 'l' => 'Avg trust score',        'p' => $s['l1']['payment']['avgTrust'],             'c' => '#10B981'],
                        ['v' => (string)$s['l1']['payment']['avgIntent'],                'l' => 'Avg intent score',       'p' => $s['l1']['payment']['avgIntent'],            'c' => '#0EA5E9'],
                        ['v' => '48hr',                                                  'l' => 'Upsell window',          'p' => 80,                                          'c' => '#F59E0B'],
                    ],
                    'dec' => [
                        ['l' => 'Act in 0–48 hours', 'v' => '34%', 's' => round($s['l1']['payment']['count'] * .34) . ' upsells · $' . number_format(round($s['l1']['payment']['count'] * .34) * 890) . ' revenue', 'g' => true],
                        ['l' => 'Wait >48 hours',    'v' => '8%',  's' => round($s['l1']['payment']['count'] * .08) . ' upsells · $' . number_format(round($s['l1']['payment']['count'] * .26) * 890) . ' lost',    'g' => false],
                    ],
                    'out' => [
                        ['l' => 'Expected upsell conversions', 'v' => round($s['l1']['payment']['count'] * .34) . ' customers'],
                        ['l' => 'Avg deal value',              'v' => '$890'],
                        ['l' => 'Revenue this cycle',          'v' => '$' . number_format(round($s['l1']['payment']['count'] * .34) * 890)],
                        ['l' => 'Ad retargeting',              'v' => 'Suppressed ✓'],
                    ],
                    'ctas' => [
                        ['ico' => '🎁', 'l' => 'Send personalised post-purchase offer', 'd' => 'Personalised to what was purchased. No discount needed. Dispatched in 2 hours.', 'b' => 'Est. $' . number_format(round($s['l1']['payment']['count'] * .34) * 890), 'c' => '#00FFB2', 'view' => 'payment_upsell', 'vt' => 'Post-Purchase Upsell Queue', 'vs' => $s['l1']['payment']['count'] . ' users in optimal upsell window (0–48hrs post-purchase)', 'vf' => ['All', 'Price insensitive', 'Enterprise']],
                        ['ico' => '⭐', 'l' => 'Award loyalty points + tier check', 'd' => 'Process loyalty points and evaluate tier elevation if eligible.', 'b' => 'Automated', 'c' => '#F59E0B', 'view' => 'payment_loyalty', 'vt' => 'Loyalty Processing Queue', 'vs' => 'All recent purchasers eligible for loyalty points and tier review', 'vf' => ['All', 'Price insensitive']],
                    ],
                ],
            ],
            'l4' => [
                [
                    'id' => 'hotbuyer', 'lc' => '#FF6B35', 'ico' => '🔥',
                    'name' => 'Hot Buyer — ' . $s['l4']['hotbuyer']['count'] . ' Users',
                    'users' => 'Intent >80 · Readiness >75',
                    'rev'   => $s['l4']['hotbuyer']['revEst'] . ' · 48hr',
                    'urg'   => 'opportunity', 'ul' => 'Revenue ready', 'urgDot' => '#10B981',
                    'banner' => ['text' => $s['l4']['hotbuyer']['count'] . ' customers are in active buying mode right now. AI prepared personalised offers for each. 48-hour window: 34% CVR now vs 8% after. No broad discount needed.', 'c' => '#FF6B35'],
                    'kpis' => [
                        ['v' => (string)$s['l4']['hotbuyer']['count'],        'l' => 'Hot Buyer users',       'p' => min(100, $s['l4']['hotbuyer']['count'] * 12), 'c' => '#FF6B35'],
                        ['v' => (string)$s['l4']['hotbuyer']['avgIntent'],    'l' => 'Avg intent score',      'p' => $s['l4']['hotbuyer']['avgIntent'],            'c' => '#10B981'],
                        ['v' => (string)$s['l4']['hotbuyer']['avgReadiness'], 'l' => 'Avg buying readiness', 'p' => $s['l4']['hotbuyer']['avgReadiness'],         'c' => '#FF6B35'],
                        ['v' => $s['l4']['hotbuyer']['fullPricePct'] . '%',   'l' => 'Full-price (no disc)', 'p' => $s['l4']['hotbuyer']['fullPricePct'],         'c' => '#10B981'],
                    ],
                    'dec' => [
                        ['l' => 'Act within 48 hours', 'v' => '34%', 's' => round($s['l4']['hotbuyer']['count'] * .34) . ' deals · ' . $s['l4']['hotbuyer']['revEst'] . ' revenue', 'g' => true],
                        ['l' => 'Act after 48 hours',  'v' => '8%',  's' => round($s['l4']['hotbuyer']['count'] * .08) . ' deals · revenue lost',                                   'g' => false],
                    ],
                    'out' => [
                        ['l' => 'Full-price (no discount)',   'v' => $s['l4']['hotbuyer']['fullPrice'] . ' users'],
                        ['l' => 'Calibrated discount offer',  'v' => $s['l4']['hotbuyer']['discount']  . ' users'],
                        ['l' => 'Expected conversions',       'v' => round($s['l4']['hotbuyer']['count'] * .34) . ' · ' . $s['l4']['hotbuyer']['revEst']],
                        ['l' => 'Window',                     'v' => '48 hours'],
                    ],
                    'ctas' => [
                        ['ico' => '🚀', 'l' => 'Dispatch offers to all ' . $s['l4']['hotbuyer']['count'] . ' Hot Buyers', 'd' => $s['l4']['hotbuyer']['fullPrice'] . ' full-price feature emails + ' . $s['l4']['hotbuyer']['discount'] . ' calibrated discount offers. All dispatched within 2 hours.', 'b' => 'Est. ' . $s['l4']['hotbuyer']['revEst'], 'c' => '#FF6B35', 'view' => 'hb_all', 'vt' => 'Hot Buyers — All ' . $s['l4']['hotbuyer']['count'] . ' Users', 'vs' => 'Click any user to see intent breakdown, offer assigned, and channel', 'vf' => ['All', 'Full price (no discount)', '10% discount offer', 'Enterprise', 'Highest intent']],
                        ['ico' => '📞', 'l' => 'Flag Enterprise Hot Buyers for CSM', 'd' => 'Enterprise-tier customers in this cohort. Direct CSM contact justified by deal size.', 'b' => $s['l4']['hotbuyer']['entCount'] . ' accounts', 'c' => '#A855F7', 'view' => 'hb_ent', 'vt' => 'Enterprise Hot Buyers', 'vs' => 'CSM outreach recommended — deal size justifies human contact', 'vf' => ['All']],
                    ],
                ],
                [
                    'id' => 'atrisk', 'lc' => '#F43F5E', 'ico' => '⚠️',
                    'name' => 'At Risk — ' . $s['l4']['atrisk']['count'] . ' Users',
                    'users' => 'Churn >65 · Engage <30',
                    'rev'   => $s['l4']['atrisk']['revRisk'] . ' at risk',
                    'urg'   => 'critical', 'ul' => '72-hour window', 'urgDot' => '#F43F5E',
                    'banner' => ['text' => $s['l4']['atrisk']['count'] . ' customers crossed the churn threshold. ' . $s['l4']['atrisk']['revRisk'] . ' ARR at risk. Act within 72 hours: 41% retention CVR. After 72 hours it drops below 20%. Every hour reduces recovery.', 'c' => '#F43F5E'],
                    'kpis' => [
                        ['v' => (string)$s['l4']['atrisk']['count'],           'l' => 'At Risk users',          'p' => min(100, $s['l4']['atrisk']['count'] * 8), 'c' => '#F43F5E'],
                        ['v' => $s['l4']['atrisk']['revRisk'],                 'l' => 'ARR at risk',            'p' => 80,                                        'c' => '#F43F5E'],
                        ['v' => (string)$s['l4']['atrisk']['avgChurn'],        'l' => 'Avg churn score',        'p' => $s['l4']['atrisk']['avgChurn'],            'c' => '#F43F5E'],
                        ['v' => (string)$s['l4']['atrisk']['avgFrustration'],  'l' => 'Avg frustration score',  'p' => $s['l4']['atrisk']['avgFrustration'],      'c' => '#F59E0B'],
                    ],
                    'dec' => [
                        ['l' => 'Act within 72 hours',  'v' => '41%', 's' => round($s['l4']['atrisk']['count'] * .41) . ' retained · ARR protected',   'g' => true],
                        ['l' => 'Wait beyond 72hrs',    'v' => '<20%','s' => round($s['l4']['atrisk']['count'] * .20) . ' retained · ' . $s['l4']['atrisk']['revRisk'] . ' at risk', 'g' => false],
                    ],
                    'out' => [
                        ['l' => 'Users in win-back plan',  'v' => $s['l4']['atrisk']['count'] . ''],
                        ['l' => 'Expected retentions',     'v' => round($s['l4']['atrisk']['count'] * .41) . ' (41%)'],
                        ['l' => 'ARR protected',           'v' => 'Est. ' . $s['l4']['atrisk']['revRisk']],
                        ['l' => 'Decision window',         'v' => '72 hours'],
                    ],
                    'ctas' => [
                        ['ico' => '⚡', 'l' => 'Launch 3-tier win-back plan', 'd' => 'Enterprise → CSM + retention offer. Growth → 3-touch sequence. Starter → email. All within 1 hour.', 'b' => $s['l4']['atrisk']['revRisk'] . ' ARR protected', 'c' => '#F43F5E', 'view' => 'ar_all', 'vt' => 'At Risk — All ' . $s['l4']['atrisk']['count'] . ' Users', 'vs' => 'Ranked by churn risk · click to see risk factors, ARR at risk, intervention tier', 'vf' => ['All', 'Imminent churn', 'Enterprise']],
                        ['ico' => '📊', 'l' => 'Top users by Revenue at Risk', 'd' => 'Ranked by churn probability × estimated LTV. These represent the majority of the risk.', 'b' => 'Priority list', 'c' => '#F59E0B', 'view' => 'ar_top50', 'vt' => 'Top Users by Revenue at Risk', 'vs' => 'Highest priority — ranked by churn × LTV', 'vf' => ['All']],
                        ['ico' => '🎁', 'l' => 'Approve retention discount offer', 'd' => 'Users with LTV > threshold eligible for 15% off. Recovers an additional 12% who won\'t respond to standard sequence.', 'b' => $s['l4']['atrisk']['count'] . ' eligible', 'c' => '#00FFB2', 'view' => 'ar_disc', 'vt' => 'Retention Offer — At-Risk Users', 'vs' => 'Discount approved · awaiting dispatch', 'vf' => ['All', 'Imminent churn']],
                    ],
                ],
                [
                    'id' => 'loyal', 'lc' => '#10B981', 'ico' => '⭐',
                    'name' => 'Loyal Advocates — ' . $s['l4']['loyal']['count'],
                    'users' => 'Loyalty >72 · Active referrals',
                    'rev'   => '+$' . number_format($s['l4']['loyal']['count'] * 1480) . ' potential',
                    'urg'   => 'opportunity', 'ul' => 'Amplify', 'urgDot' => '#10B981',
                    'banner' => ['text' => $s['l4']['loyal']['count'] . ' customers in Loyal Advocate state — 40% lower churn, 3.2× LTV. Referral amplification, tier nudges, and ambassador invitations are all ready to send.', 'c' => '#10B981'],
                    'kpis' => [
                        ['v' => (string)$s['l4']['loyal']['count'],                      'l' => 'Loyal Advocates',        'p' => 100,                                'c' => '#10B981'],
                        ['v' => (string)$s['l4']['loyal']['avgLoyalty'],                 'l' => 'Avg loyalty score',      'p' => $s['l4']['loyal']['avgLoyalty'],    'c' => '#10B981'],
                        ['v' => (string)$s['l4']['loyal']['avgEngagement'],              'l' => 'Avg engagement score',   'p' => $s['l4']['loyal']['avgEngagement'], 'c' => '#F59E0B'],
                        ['v' => '+$' . number_format($s['l4']['loyal']['count'] * 1480), 'l' => 'Referral rev. potential','p' => 65,                                 'c' => '#0EA5E9'],
                    ],
                    'dec' => [
                        ['l' => 'Amplify programme now',  'v' => '+$' . number_format($s['l4']['loyal']['count'] * 1480), 's' => 'Additional referral revenue this month', 'g' => true],
                        ['l' => 'No action',              'v' => '$0',  's' => 'Momentum declines · revenue missed',                                                       'g' => false],
                    ],
                    'out' => [
                        ['l' => 'Advocates notified',         'v' => $s['l4']['loyal']['count'] . ''],
                        ['l' => 'Near-tier nudges',           'v' => round($s['l4']['loyal']['count'] * .4) . ''],
                        ['l' => 'Ambassador invitations',     'v' => $s['l4']['loyal']['ambassadors'] . ''],
                        ['l' => 'Expected referral revenue',  'v' => '+$' . number_format($s['l4']['loyal']['count'] * 1480) . '/month'],
                    ],
                    'ctas' => [
                        ['ico' => '⭐', 'l' => 'Launch referral amplification', 'd' => 'All ' . $s['l4']['loyal']['count'] . ' get personalised referral message + bonus points. Near-threshold users get tier nudge.', 'b' => '+$' . number_format($s['l4']['loyal']['count'] * 1480) . ' revenue', 'c' => '#10B981', 'view' => 'loyal_all', 'vt' => 'Loyal Advocates — All ' . $s['l4']['loyal']['count'], 'vs' => 'Click any user to see loyalty score, referrals, tier gap, and milestone', 'vf' => ['All', 'Super referrer', 'Near tier upgrade', 'Ambassador ready']],
                        ['ico' => '🎖️', 'l' => 'Ambassador invitations — top ' . $s['l4']['loyal']['ambassadors'], 'd' => 'Top advocates with highest loyalty scores. Early feature access + dedicated CSM.', 'b' => $s['l4']['loyal']['ambassadors'] . ' invitations', 'c' => '#FFD700', 'view' => 'loyal_amb', 'vt' => 'Ambassador Candidates — Top ' . $s['l4']['loyal']['ambassadors'], 'vs' => 'Highest loyalty · early access + dedicated CSM', 'vf' => ['All']],
                    ],
                ],
                [
                    'id' => 'reactready', 'lc' => '#0EA5E9', 'ico' => '🔄',
                    'name' => 'Reactivation Ready — ' . $s['l4']['reactready']['count'],
                    'users' => 'Churned · Score >' . max(0, $s['l4']['reactready']['avgReactivation'] - 5),
                    'rev'   => $s['l4']['reactready']['revRec'] . ' recoverable',
                    'urg'   => 'opportunity', 'ul' => 'Revenue recovery', 'urgDot' => '#10B981',
                    'banner' => ['text' => $s['l4']['reactready']['count'] . ' churned customers scored high on reactivation. AI wrote personalised win-back messages by churn reason. Personalised CVR: 31% vs 8% generic. All messages ready.', 'c' => '#0EA5E9'],
                    'kpis' => [
                        ['v' => (string)$s['l4']['reactready']['count'],           'l' => 'Reactivation candidates',  'p' => min(100, $s['l4']['reactready']['count'] * 12), 'c' => '#0EA5E9'],
                        ['v' => (string)$s['l4']['reactready']['avgReactivation'], 'l' => 'Avg reactivation score',   'p' => $s['l4']['reactready']['avgReactivation'],       'c' => '#0EA5E9'],
                        ['v' => (string)$s['l4']['reactready']['avgEngagement'],   'l' => 'Avg engagement (dormant)', 'p' => $s['l4']['reactready']['avgEngagement'],         'c' => '#F43F5E'],
                        ['v' => $s['l4']['reactready']['revRec'],                  'l' => 'Re-ARR potential',         'p' => 75,                                              'c' => '#F59E0B'],
                    ],
                    'dec' => [
                        ['l' => 'Personalised (4 variants)', 'v' => '31%', 's' => round($s['l4']['reactready']['count'] * .31) . ' return · ' . $s['l4']['reactready']['revRec'] . '/year', 'g' => true],
                        ['l' => 'Generic campaign',          'v' => '8%',  's' => round($s['l4']['reactready']['count'] * .08) . ' return · revenue missed',                                  'g' => false],
                    ],
                    'out' => [
                        ['l' => 'Segments deployed',         'v' => '4 personalised'],
                        ['l' => 'Expected reactivations',    'v' => round($s['l4']['reactready']['count'] * .31) . ' (' . round(31) . '%)'],
                        ['l' => 'Re-ARR',                    'v' => $s['l4']['reactready']['revRec'] . '/year'],
                        ['l' => 'Net ROI',                   'v' => 'Positive from month 3'],
                    ],
                    'ctas' => [
                        ['ico' => '🔄', 'l' => 'Launch personalised win-back', 'd' => 'Price → feature → support apology → competitive comparison. All in 24hrs.', 'b' => $s['l4']['reactready']['revRec'] . ' re-ARR', 'c' => '#0EA5E9', 'view' => 'reactiv_all', 'vt' => 'Win-Back Campaign — ' . $s['l4']['reactready']['count'] . ' Users', 'vs' => '4 segments · click any user to see churn reason and personalised message', 'vf' => ['All', 'High potential', 'Deeply dormant']],
                        ['ico' => '📞', 'l' => 'Route support-failure to CS team', 'd' => 'Support-failure users respond better to a human call than automated email.', 'b' => round($s['l4']['reactready']['count'] * .35) . ' users', 'c' => '#F43F5E', 'view' => 'reactiv_sup', 'vt' => 'Support-Failure Segment', 'vs' => 'Human outreach recommended · CS team notified', 'vf' => ['All']],
                    ],
                ],
            ],
            'l5' => [
                [
                    'id' => 'disc10', 'lc' => '#FF6B35', 'ico' => '💰',
                    'name' => 'Scenario: 10% Discount → Hot Buyers',
                    'users' => $s['l4']['hotbuyer']['count'] . ' Hot Buyers modelled',
                    'rev'   => '$' . number_format($s['l5']['netRevSmart']) . ' net',
                    'urg'   => 'opportunity', 'ul' => 'Revenue scenario', 'urgDot' => '#10B981',
                    'banner' => ['text' => 'Smart segmentation saves $' . number_format($s['l5']['marginSaved']) . ' in margin vs blanket discount. ' . $s['l5']['fullPrice'] . ' users (' . $s['l5']['fullPct'] . '%) convert at full price. ' . $s['l5']['discount'] . ' users (' . $s['l5']['discPct'] . '%) respond to the discount. Net: $' . number_format($s['l5']['netRevSmart']) . ' vs $' . number_format($s['l5']['netRevBlanket']) . ' blanket.', 'c' => '#FF6B35'],
                    'kpis' => [
                        ['v' => '88%',                                          'l' => 'Model confidence',       'p' => 88, 'c' => '#0EA5E9'],
                        ['v' => $s['l5']['fullPrice'] . ' (' . $s['l5']['fullPct'] . '%)', 'l' => 'Full-price users', 'p' => $s['l5']['fullPct'], 'c' => '#10B981'],
                        ['v' => $s['l5']['discount']  . ' (' . $s['l5']['discPct'] . '%)', 'l' => 'Discount users',  'p' => $s['l5']['discPct'], 'c' => '#F59E0B'],
                        ['v' => '$' . number_format($s['l5']['marginSaved']), 'l' => 'Margin saved vs blanket', 'p' => 55, 'c' => '#10B981'],
                    ],
                    'dec' => [
                        ['l' => 'Smart segmented discount', 'v' => '$' . number_format($s['l5']['netRevSmart']),   's' => 'Net after discount cost · saves $' . number_format($s['l5']['marginSaved']), 'g' => true],
                        ['l' => 'Blanket 10% to all',       'v' => '$' . number_format($s['l5']['netRevBlanket']), 's' => 'Full discount cost · $' . number_format($s['l5']['marginSaved']) . ' wasted',  'g' => false],
                    ],
                    'out' => [
                        ['l' => 'Full-price conversions',   'v' => round($s['l5']['fullPrice'] * .34) . ' users (no discount)'],
                        ['l' => 'Discount conversions',     'v' => round($s['l5']['discount']  * .34) . ' users (10%)'],
                        ['l' => 'Total discount cost',      'v' => '$' . number_format($s['l5']['discountCost']) . ' (not $' . number_format($s['l5']['blanketCost']) . ')'],
                        ['l' => 'Net revenue',              'v' => '$' . number_format($s['l5']['netRevSmart'])],
                    ],
                    'ctas' => [
                        ['ico' => '🚀', 'l' => 'Execute smart segmented campaign', 'd' => $s['l5']['fullPrice'] . ' full-price feature emails + ' . $s['l5']['discount'] . ' time-limited 10% offers. A/B holdout maintained. Dispatch in 2hrs.', 'b' => '$' . number_format($s['l5']['netRevSmart']) . ' net revenue', 'c' => '#FF6B35', 'view' => 'disc_run', 'vt' => 'Smart Discount Campaign — ' . $s['l4']['hotbuyer']['count'] . ' Users', 'vs' => 'Price sensitivity pre-applied · see offer assigned for each user', 'vf' => ['All', 'Full price (no discount)', '10% discount offer', 'Margin protected', 'Highest intent']],
                        ['ico' => '🧪', 'l' => 'Run A/B test: 10% vs 5%', 'd' => 'Test whether 5% converts as well — potentially saving more in margin.', 'b' => 'A/B test', 'c' => '#A855F7', 'view' => 'disc_ab', 'vt' => 'A/B Test: 10% vs 5% Discount', 'vs' => 'Equal split per variant · results expected in 72 hours', 'vf' => ['All', 'Margin protected', 'Highest intent']],
                    ],
                ],
                [
                    'id' => 'nochurn', 'lc' => '#F43F5E', 'ico' => '🛡️',
                    'name' => 'Scenario: Win-Back Now vs Wait',
                    'users' => $s['l4']['atrisk']['count'] . ' At-Risk users modelled',
                    'rev'   => '$' . number_format($s['l5']['retentionNow']) . ' saved',
                    'urg'   => 'critical', 'ul' => 'Churn scenario', 'urgDot' => '#F43F5E',
                    'banner' => ['text' => 'Acting now saves $' . number_format($s['l5']['retentionNow']) . ' more ARR than waiting 72hrs. Churn model computes ' . $s['l5']['avgPNow'] . '% avg retention probability now vs ' . $s['l5']['avgPWait'] . '% after the window. Every 24hr delay costs $' . number_format((int)(($s['l5']['retentionNow'] - $s['l5']['retentionWait']) / 3)) . '.', 'c' => '#F43F5E'],
                    'kpis' => [
                        ['v' => $s['l5']['avgPNow'] . '%',  'l' => 'Model: avg retention CVR now',   'p' => min(100, (int)$s['l5']['avgPNow'] * 2), 'c' => '#10B981'],
                        ['v' => $s['l5']['avgPWait'] . '%', 'l' => 'Model: avg CVR after 72hrs',     'p' => min(100, (int)$s['l5']['avgPWait'] * 2), 'c' => '#F43F5E'],
                        ['v' => '$' . number_format($s['l5']['retentionNow']),  'l' => 'EV: ARR saved (act now)', 'p' => 80, 'c' => '#10B981'],
                        ['v' => '$' . number_format($s['l5']['retentionWait']), 'l' => 'EV: ARR saved (wait)',    'p' => 30, 'c' => '#F43F5E'],
                    ],
                    'dec' => [
                        ['l' => 'Act now (72hr window)', 'v' => '$' . number_format($s['l5']['retentionNow']),  's' => 'Model P(retain|now): ' . $s['l5']['avgPNow'] . '% avg across ' . $s['l4']['atrisk']['count'] . ' users', 'g' => true],
                        ['l' => 'Wait beyond 72hrs',     'v' => '$' . number_format($s['l5']['retentionWait']), 's' => 'Model P(retain|wait): ' . $s['l5']['avgPWait'] . '% avg · window degrades rapidly',                      'g' => false],
                    ],
                    'out' => [
                        ['l' => 'Users in win-back now', 'v' => $s['l4']['atrisk']['count'] . ''],
                        ['l' => 'Avg retention P (EV model)', 'v' => $s['l5']['avgPNow'] . '%'],
                        ['l' => 'ARR protected',          'v' => '$' . number_format($s['l5']['retentionNow'])],
                        ['l' => 'Cost of waiting',        'v' => '-$' . number_format($s['l5']['retentionNow'] - $s['l5']['retentionWait']) . ' ARR'],
                    ],
                    'ctas' => [
                        ['ico' => '⚡', 'l' => 'Execute win-back NOW', 'd' => 'All ' . $s['l4']['atrisk']['count'] . ' at-risk users enter intervention immediately. Tiered by ARR risk.', 'b' => '$' . number_format($s['l5']['retentionNow']) . ' ARR saved', 'c' => '#F43F5E', 'view' => 'nochurn_all', 'vt' => 'Win-Back Now — All At-Risk Users', 'vs' => 'Ranked by churn probability · see ARR at risk per user', 'vf' => ['All', 'Imminent churn', 'Enterprise']],
                        ['ico' => '⏳', 'l' => 'Schedule for next week (modelled)', 'd' => 'See the revenue impact of delaying intervention by 7 days.', 'b' => '-$' . number_format($s['l5']['retentionNow'] - $s['l5']['retentionWait']) . ' cost', 'c' => '#F59E0B', 'view' => 'nochurn_wait', 'vt' => 'Delayed Win-Back — Impact Model', 'vs' => 'Same users · modelled after 72hr window', 'vf' => ['All']],
                    ],
                ],
                [
                    'id' => 'reactval', 'lc' => '#0EA5E9', 'ico' => '📈',
                    'name' => 'Scenario: Reactivation Value',
                    'users' => $s['l4']['reactready']['count'] . ' churned users modelled',
                    'rev'   => $s['l4']['reactready']['revRec'] . ' re-ARR',
                    'urg'   => 'opportunity', 'ul' => 'Revenue recovery', 'urgDot' => '#10B981',
                    'banner' => ['text' => 'Personalised win-back delivers ' . round($s['l5']['avgPPers'] / max(0.1, $s['l5']['avgPGen']), 1) . '× the ROI of generic campaigns for these ' . $s['l4']['reactready']['count'] . ' churned users. EV model: ' . $s['l5']['avgPPers'] . '% personalised vs ' . $s['l5']['avgPGen'] . '% generic CVR. Net Year 1 ROI is strongly positive after campaign cost.', 'c' => '#0EA5E9'],
                    'kpis' => [
                        ['v' => round($s['l5']['avgPPers'] / max(0.1, $s['l5']['avgPGen']), 1) . '×', 'l' => 'Model: personalised ROI multiplier', 'p' => 90, 'c' => '#10B981'],
                        ['v' => $s['l5']['avgPPers'] . '%', 'l' => 'Model: avg personalised CVR',      'p' => min(100, (int)($s['l5']['avgPPers'] * 2)), 'c' => '#10B981'],
                        ['v' => $s['l5']['avgPGen']  . '%', 'l' => 'Model: avg generic CVR',           'p' => min(100, (int)($s['l5']['avgPGen']  * 2)), 'c' => '#F43F5E'],
                        ['v' => $s['l4']['reactready']['revRec'], 'l' => 'EV: Net Year 1 re-ARR',      'p' => 75, 'c' => '#F59E0B'],
                    ],
                    'dec' => [
                        ['l' => 'Personalised (4 variants)', 'v' => $s['l4']['reactready']['revRec'], 's' => round($s['l4']['reactready']['count'] * $s['l5']['avgPPers'] / 100) . ' return · ' . $s['l5']['avgPPers'] . '% model CVR', 'g' => true],
                        ['l' => 'Generic campaign', 'v' => '$' . number_format((int)(str_replace(['$',','], '', $s['l4']['reactready']['revRec']) * ($s['l5']['avgPGen'] / max(1, $s['l5']['avgPPers'])))), 's' => round($s['l4']['reactready']['count'] * $s['l5']['avgPGen'] / 100) . ' return · ' . round($s['l5']['avgPPers'] / max(0.1, $s['l5']['avgPGen']), 1) . '× less effective', 'g' => false],
                    ],
                    'out' => [
                        ['l' => 'Segments deployed',           'v' => '4 personalised'],
                        ['l' => 'Avg EV CVR (personalised)',   'v' => $s['l5']['avgPPers'] . '%'],
                        ['l' => 'Re-ARR Year 1',               'v' => $s['l4']['reactready']['revRec']],
                        ['l' => 'Break-even',                  'v' => 'Month 2–3'],
                    ],
                    'ctas' => [
                        ['ico' => '🔄', 'l' => 'Launch personalised win-back', 'd' => '4 message variants by churn reason. All ' . $s['l4']['reactready']['count'] . ' users covered within 24hrs.', 'b' => $s['l4']['reactready']['revRec'] . ' re-ARR', 'c' => '#0EA5E9', 'view' => 'reactiv_all', 'vt' => 'Reactivation Value — ' . $s['l4']['reactready']['count'] . ' Users', 'vs' => 'Personalised by churn reason · full ROI model', 'vf' => ['All', 'High potential', 'Deeply dormant']],
                    ],
                ],
                [
                    'id' => 'loyboost', 'lc' => '#10B981', 'ico' => '🏆',
                    'name' => 'Scenario: Loyalty ROI',
                    'users' => $s['l4']['loyal']['count'] . ' Advocates modelled',
                    'rev'   => '+$' . number_format($s['l4']['loyal']['count'] * 1480) . '/mo',
                    'urg'   => 'opportunity', 'ul' => 'Revenue amplifier', 'urgDot' => '#10B981',
                    'banner' => ['text' => 'Your ' . $s['l4']['loyal']['count'] . ' Loyal Advocates generate 3.2× LTV vs average and have 40% lower churn. Amplifying the referral programme could add $' . number_format($s['l4']['loyal']['count'] * 1480) . '/month in new revenue with zero acquisition cost.', 'c' => '#10B981'],
                    'kpis' => [
                        ['v' => '3.2×',  'l' => 'LTV vs non-advocates',       'p' => 90, 'c' => '#10B981'],
                        ['v' => '40%',   'l' => 'Lower churn rate',           'p' => 30, 'c' => '#10B981'],
                        ['v' => '+$' . number_format($s['l4']['loyal']['count'] * 1480), 'l' => 'Monthly referral potential', 'p' => 65, 'c' => '#F59E0B'],
                        ['v' => (string)$s['l4']['loyal']['ambassadors'], 'l' => 'Ambassador candidates', 'p' => min(100, $s['l4']['loyal']['ambassadors'] * 20), 'c' => '#FFD700'],
                    ],
                    'dec' => [
                        ['l' => 'Amplify now',  'v' => '+$' . number_format($s['l4']['loyal']['count'] * 1480 * 12), 's' => 'Annual referral revenue · zero acquisition cost', 'g' => true],
                        ['l' => 'Do nothing',   'v' => '$0',  's' => 'Advocate momentum declines · natural churn erodes base',                                                'g' => false],
                    ],
                    'out' => [
                        ['l' => 'Advocates amplified',        'v' => $s['l4']['loyal']['count'] . ''],
                        ['l' => 'Ambassador programme',       'v' => $s['l4']['loyal']['ambassadors'] . ' invited'],
                        ['l' => 'Expected monthly referrals', 'v' => '+$' . number_format($s['l4']['loyal']['count'] * 1480)],
                        ['l' => 'Annual revenue uplift',      'v' => '+$' . number_format($s['l4']['loyal']['count'] * 1480 * 12)],
                    ],
                    'ctas' => [
                        ['ico' => '⭐', 'l' => 'Launch loyalty amplification', 'd' => 'All ' . $s['l4']['loyal']['count'] . ' advocates get referral message + bonus points. Ambassador invitations for top ' . $s['l4']['loyal']['ambassadors'] . '.', 'b' => '+$' . number_format($s['l4']['loyal']['count'] * 1480) . '/mo', 'c' => '#10B981', 'view' => 'loyboost_all', 'vt' => 'Loyalty Amplification — All ' . $s['l4']['loyal']['count'] . ' Advocates', 'vs' => 'Referral programme active · see each advocate score and tier gap', 'vf' => ['All', 'Super referrer', 'Near tier upgrade', 'Ambassador ready']],
                        ['ico' => '🎖️', 'l' => 'Send ambassador invitations', 'd' => 'Top ' . $s['l4']['loyal']['ambassadors'] . ' advocates — early feature access + dedicated CSM contact.', 'b' => $s['l4']['loyal']['ambassadors'] . ' invitations', 'c' => '#FFD700', 'view' => 'loyboost_top', 'vt' => 'Ambassador Candidates — Top ' . $s['l4']['loyal']['ambassadors'], 'vs' => 'Highest loyalty scores · early access programme', 'vf' => ['All']],
                    ],
                ],
            ],
        ];
    }

    // ── Shared data preparation ────────────────────────────────────────────────
    private function prepareData(): array
    {
        $all = BehavioralProfile::orderByDesc('overall_score')->get();

        // ── L4 Cohorts ──────────────────────────────────────────────────────────
        $hotBuyers  = $all->filter(fn($p) => $p->buying_readiness >= 70 && $p->intent_score >= 80);
        $atRisk     = $all->filter(fn($p) => $p->churn_score >= 65);
        $loyals     = $all->filter(fn($p) => $p->loyalty_score >= 72);
        $reactivate = $all->filter(fn($p) => $p->reactivation_potential >= 70 && $p->engagement_score < 30);

        // ── L1 Cohorts from UserEvent ───────────────────────────────────────────
        $pricingEmails = UserEvent::where('event_type', 'pricing_view')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('email, COUNT(*) as cnt')
            ->groupBy('email')
            ->having('cnt', '>=', 2)
            ->pluck('email');
        $pricingUsers  = $all->whereIn('email', $pricingEmails->toArray());

        $rageEmails   = UserEvent::where('event_type', 'rage_click')
            ->where('created_at', '>=', now()->subDays(7))
            ->distinct()->pluck('email');
        $rageUsers    = $all->whereIn('email', $rageEmails->toArray());

        $cartEmails   = UserEvent::where('event_type', 'cart_add')->distinct()->pluck('email');
        $cartUsers    = $all->whereIn('email', $cartEmails->toArray());

        $paymentEmails = UserEvent::where('event_type', 'checkout_complete')->distinct()->pluck('email');
        $paymentUsers  = $all->whereIn('email', $paymentEmails->toArray());

        // ── Stats ───────────────────────────────────────────────────────────────
        $hbFullPrice = $hotBuyers->filter(fn($p) => $p->trust_score >= 65)->count();
        $hbDiscount  = $hotBuyers->filter(fn($p) => $p->trust_score < 65)->count();
        $hbTotal     = $hotBuyers->count();
        $hbRevEst    = '$' . number_format($hbTotal * 294);
        $fullPct     = $hbTotal > 0 ? (int) round($hbFullPrice / $hbTotal * 100) : 38;
        $discPct     = 100 - $fullPct;

        // ── Extended stats from real profile scores ─────────────────────────────
        $hbAvgReadiness    = (int) round($hotBuyers->avg('buying_readiness') ?? 0);
        $arAvgChurn        = (int) round($atRisk->avg('churn_score') ?? 0);
        $arAvgFrustration  = (int) round($atRisk->avg('frustration_score') ?? 0);
        $loAvgLoyalty      = (int) round($loyals->avg('loyalty_score') ?? 0);
        $loAvgEngagement   = (int) round($loyals->avg('engagement_score') ?? 0);
        $rvAvgReactivation = (int) round($reactivate->avg('reactivation_potential') ?? 0);
        $rvAvgEngagement   = (int) round($reactivate->avg('engagement_score') ?? 0);
        $prAvgIntent       = (int) round($pricingUsers->avg('intent_score') ?? 0);
        $prAvgReadiness    = (int) round($pricingUsers->avg('buying_readiness') ?? 0);
        $raAvgIntent       = (int) round($rageUsers->avg('intent_score') ?? 0);
        $raAvgFrustration  = (int) round($rageUsers->avg('frustration_score') ?? 0);
        $caAvgReadiness    = (int) round($cartUsers->avg('buying_readiness') ?? 0);
        $caAvgIntent       = (int) round($cartUsers->avg('intent_score') ?? 0);
        $paAvgTrust        = (int) round($paymentUsers->avg('trust_score') ?? 0);
        $paAvgIntent       = (int) round($paymentUsers->avg('intent_score') ?? 0);

        // Real cart abandonment rate from UserEvent table (90-day window)
        $cartAddEmails  = UserEvent::where('event_type', 'cart_add')
            ->where('created_at', '>=', now()->subDays(90))
            ->distinct()->pluck('email')->toArray();
        $checkoutEmails = UserEvent::where('event_type', 'checkout_complete')
            ->where('created_at', '>=', now()->subDays(90))
            ->distinct()->pluck('email')->toArray();
        $realAbandonPct = count($cartAddEmails) > 0
            ? (int) round(count(array_diff($cartAddEmails, $checkoutEmails)) / count($cartAddEmails) * 100)
            : 72;
        $realConvPct    = 100 - $realAbandonPct;

        // Avg pricing views per triggered user (last 30 days)
        $totalPricingViews = UserEvent::where('event_type', 'pricing_view')
            ->where('created_at', '>=', now()->subDays(30))->count();
        $avgPricingPerUser = $pricingUsers->count() > 0
            ? round($totalPricingViews / $pricingUsers->count(), 1)
            : 3.4;

        // Avg rage click events per triggered user (last 7 days)
        $totalRageClicks   = UserEvent::where('event_type', 'rage_click')
            ->where('created_at', '>=', now()->subDays(7))->count();
        $avgRagePerUser    = $rageUsers->count() > 0
            ? (int) round($totalRageClicks / $rageUsers->count())
            : 8;

        // L5 — per-user Expected Value model via DecisionEngine
        $engine  = new DecisionEngine();
        $l5Stats = $engine->computeL5Stats($hotBuyers, $atRisk, $reactivate, 294);

        $arRevRisk = '$' . number_format($atRisk->sum(fn($p) => max(1000, $p->overall_score * 620)));

        $stats = [
            'l4' => [
                'hotbuyer'  => [
                    'count'        => $hbTotal,
                    'avgIntent'    => (int) round($hotBuyers->avg('intent_score') ?? 0),
                    'avgReadiness' => $hbAvgReadiness,
                    'fullPrice'    => $hbFullPrice,
                    'fullPricePct' => $fullPct,
                    'discount'     => $hbDiscount,
                    'revEst'       => $hbRevEst,
                    'entCount'     => $hotBuyers->filter(fn($p) => $p->segment === 'champion')->count(),
                ],
                'atrisk'    => [
                    'count'          => $atRisk->count(),
                    'revRisk'        => $arRevRisk,
                    'avgChurn'       => $arAvgChurn,
                    'avgFrustration' => $arAvgFrustration,
                ],
                'loyal'     => [
                    'count'         => $loyals->count(),
                    'avgLoyalty'    => $loAvgLoyalty,
                    'avgEngagement' => $loAvgEngagement,
                    'ambassadors'   => $loyals->filter(fn($p) => $p->loyalty_score > 88)->count(),
                ],
                'reactready' => [
                    'count'          => $reactivate->count(),
                    'revRec'         => '$' . number_format($reactivate->count() * 4200),
                    'avgReactivation'=> $rvAvgReactivation,
                    'avgEngagement'  => $rvAvgEngagement,
                ],
            ],
            'l1' => [
                'cart'    => [
                    'count'        => $cartUsers->count(),
                    'hv'           => $cartUsers->filter(fn($p) => $p->buying_readiness >= 50)->count(),
                    'avgReadiness' => $caAvgReadiness,
                    'avgIntent'    => $caAvgIntent,
                    'abandonPct'   => $realAbandonPct,
                    'convPct'      => $realConvPct,
                ],
                'pricing' => [
                    'count'        => $pricingUsers->count(),
                    'avgIntent'    => $prAvgIntent,
                    'avgReadiness' => $prAvgReadiness,
                    'avgPerUser'   => $avgPricingPerUser,
                ],
                'rage'    => [
                    'count'          => $rageUsers->count(),
                    'avgIntent'      => $raAvgIntent,
                    'avgFrustration' => $raAvgFrustration,
                    'avgClicks'      => $avgRagePerUser,
                ],
                'payment' => [
                    'count'     => $paymentUsers->count(),
                    'avgTrust'  => $paAvgTrust,
                    'avgIntent' => $paAvgIntent,
                ],
            ],
            'l5' => $l5Stats,
        ];

        // ── User card groups ────────────────────────────────────────────────────
        $hbCards = $hotBuyers->map(fn($p) => $this->hotBuyerCard($p))->values();
        $arCards = $atRisk->map(fn($p)    => $this->atRiskCard($p))->values();
        $loCards = $loyals->map(fn($p)    => $this->loyalCard($p))->values();
        $rvCards = $reactivate->map(fn($p)=> $this->reactivCard($p))->values();
        $prCards = $pricingUsers->map(fn($p) => $this->pricingCard($p))->values();
        $raCards = $rageUsers->map(fn($p)    => $this->rageCard($p))->values();
        $caCards = $cartUsers->map(fn($p)    => $this->cartCard($p))->values();
        $paCards = $paymentUsers->map(fn($p) => $this->paymentCard($p))->values();
        $dcCards = $hotBuyers->map(fn($p)    => $this->discCard($p))->values();

        $userGroups = [
            'hb_all'        => $hbCards,
            'hb_ent'        => $hbCards->filter(fn($c) => $c['tier'] === 'Enterprise')->values(),
            'ar_all'        => $arCards,
            'ar_top50'      => $arCards->sortByDesc('scoreValue')->take(5)->values(),
            'ar_disc'       => $arCards,
            'loyal_all'     => $loCards,
            'loyal_amb'     => $loCards->filter(fn($c) => in_array('Ambassador ready', $c['tags']))->values(),
            'loyboost_all'  => $loCards,
            'loyboost_top'  => $loCards->filter(fn($c) => in_array('Ambassador ready', $c['tags']))->values(),
            'reactiv_all'   => $rvCards,
            'reactiv_sup'   => $rvCards,
            'reactval_all'  => $rvCards,
            'pricing_all'   => $prCards,
            'pricing_trial' => $prCards,
            'demo_all'      => $prCards,
            'demo_review'   => $prCards,
            'rage_live'     => $raCards,
            'rage_chat'     => $raCards,
            'rage_bug'      => $raCards,
            'cart_all'      => $caCards,
            'cart_high'     => $caCards->filter(fn($c) => in_array('High value', $c['tags']))->values(),
            'cart_ent'      => $caCards->filter(fn($c) => $c['tier'] === 'Enterprise')->values(),
            'nologin_all'   => $arCards,
            'nologin_ent'   => $arCards->filter(fn($c) => $c['tier'] === 'Enterprise')->values(),
            'payment_upsell'  => $paCards,
            'payment_loyalty' => $paCards,
            'disc_run'      => $dcCards,
            'disc_ab'       => $dcCards,
            'nochurn_all'   => $arCards,
            'nochurn_wait'  => $arCards,
        ];

        $scenarios = $this->buildScenarios($stats);

        return compact('scenarios', 'userGroups');
    }

    public function index()
    {
        return view('client.decision-centre', $this->prepareData() + ['activeLayer' => 'l4']);
    }

    public function l5()
    {
        return view('client.decision-centre', $this->prepareData() + ['activeLayer' => 'l5']);
    }
}
