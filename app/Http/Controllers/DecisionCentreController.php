<?php

namespace App\Http\Controllers;

use App\Models\BehavioralProfile;
use App\Models\UserEvent;
use App\Services\DecisionEngine;
use App\Services\EdTechBehavioralEngine;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateCategory;
use App\Models\EmailLog;

class DecisionCentreController extends Controller
{
    // ── EdTech helpers ─────────────────────────────────────────────────────────
    private function getEdTechEngine(): ?EdTechBehavioralEngine
    {
        $client = auth('client')->user();
        if (!$client) return null;

        $dataSource = $client->dataSources()->where('status', 'connected')->first();
        return $dataSource ? new EdTechBehavioralEngine($dataSource) : null;
    }

    private function hasEdTechData(): bool
    {
        $engine = $this->getEdTechEngine();
        return $engine && $engine->isConnected();
    }

    private function getCompany(string $email): string
    {
        $companies = ['MockMaster Academy','PTE Prep Hub','English Pro Institute','IELTS Mastery','ScoreBoost EdTech','LinguaLearn','TestPrep Global','Bright Future Coaching','Ace English','SmartStudy Platform','EduSpark','Knowledge Nest','LearnSmart AI','StudyBuddy','ExamCracker','Global Learners','SkillForge','EduVantage','StudyStream','BrainWave Academy'];
        return $companies[abs(crc32($email)) % count($companies)];
    }

    private function getInitials(string $name): string
    {
        $parts = explode(' ', $name);
        return strtoupper(substr($parts[0] ?? 'S', 0, 1) . substr($parts[1] ?? '', 0, 1));
    }

    private function timeAgo(?string $dt): string
    {
        return $dt ? \Carbon\Carbon::parse($dt)->diffForHumans() : 'Unknown';
    }

    private function daysSince(?string $dt): int
    {
        return $dt ? (int)\Carbon\Carbon::parse($dt)->diffInDays() : 30;
    }

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
            // ── AIML Dashboard filtered groups ──────────────────────────────────────
            'disc10_not_purchased' => $hbCards->filter(fn($c) => $c['tier'] === 'Growth' || $c['tier'] === 'Starter')->values(),
            'nochurn_at_risk'      => $arCards,
            'reactval_ready'       => $rvCards,
            'loyboost_loyal'       => $loCards,
            'speaking_low'         => collect(), // placeholder for low speaking scores
        ];

        $scenarios = $this->buildScenarios($stats);

        return compact('scenarios', 'userGroups');
    }

    // public function index()
    // {
    //     $client = auth('client')->user();
    //     $dataSource = $client ? $client->dataSources()->first() : null;
    //     $hasDataSource = $dataSource !== null;
    //     $sourceIsConnected = $hasDataSource && $dataSource->status === 'connected';

    //     // Try to load EdTech data if source is marked as connected
    //     if ($sourceIsConnected) {
    //         try {
    //             $engine = $this->getEdTechEngine();
    //             if ($engine && $engine->isConnected()) {
    //                 $profiles = $engine->getStudentProfiles(100);
    //                 if (!empty($profiles)) {
    //                     return view('client.decision-centre', [
    //                         'scenarios'   => ['l4' => $this->buildEdTechScenarios($profiles)],
    //                         'userGroups'  => $this->buildEdTechUserGroups($profiles),
    //                         'activeLayer' => 'l4',
    //                         'dataSourceConnected' => true,
    //                     ]);
    //                 }
    //             }
    //         } catch (\Exception $e) {
    //             \Log::error('EdTech data fetch failed in DecisionCentreController: ' . $e->getMessage());
    //         }
    //     }

    //     // Fallback: demo data from local BehavioralProfile
    //     $data = $this->prepareData();
    //     return view('client.decision-centre', [
    //         'scenarios'   => $data['scenarios'],
    //         'userGroups'  => $data['userGroups'],
    //         'activeLayer' => 'l4',
    //         'dataSourceConnected' => $sourceIsConnected,
    //     ]);
    // }

    // public function index()
    // {
    //     return redirect()->route('client.analytics');
    // }

    // public function index()
    // {
    //     $data = $this->prepareData();
    //     return view('client.decision-centre', [
    //         'scenarios'   => $data['scenarios'],
    //         'userGroups'  => $data['userGroups'],
    //         'activeLayer' => 'l4',
    //         'dataSourceConnected' => false,
    //     ]);
    // }

    public function index()
    {
        $client = auth('client')->user();
        $dataSource = $client ? $client->dataSources()->first() : null;
        $hasConnectedSource = $dataSource && $dataSource->status === 'connected';
        $dataSourceConnected = false;
        $profiles = null;
        $edProfiles = [];

        // Try to use connected EdTech data if available
        if ($hasConnectedSource) {
            try {
                $engine = $this->getEdTechEngine();
                if ($engine && $engine->isConnected()) {
                    $edProfiles = $engine->getStudentProfiles(100);
                    if (!empty($edProfiles)) {
                        $dataSourceConnected = true;
                        $profiles = collect($edProfiles)->map(
                            fn($p) => new \App\Adapters\EdTechProfileAdapter($p)
                        );
                    }
                }
            } catch (\Exception $e) {
                \Log::error('EdTech L4 data fetch failed: ' . $e->getMessage());
            }
        }

        // Use EdTech data for scenarios if connected, else fallback to demo
        if ($dataSourceConnected && !empty($edProfiles)) {
            $data = $this->prepareEdTechData($edProfiles);
        } else {
            if (!isset($profiles) || $profiles === null) {
                $profiles = BehavioralProfile::orderByDesc('overall_score')->get();
            }
            $data = $this->prepareData();
        }

        return view('client.decision-centre', [
            'scenarios'           => $data['scenarios'],
            'userGroups'          => $data['userGroups'],
            'activeLayer'         => 'l4',
            'dataSourceConnected' => $dataSourceConnected,
            'profiles'            => $profiles,
        ]);
    }

    // ── EdTech Card Builders ───────────────────────────────────────────────────
    private function edCard(array $p, string $type): array
    {
        $plan = match($p['segment']) {
            'champion' => 'Enterprise', 'loyal' => 'Business',
            'at_risk'  => 'Growth',     'new'   => 'Growth',
            'dormant'  => 'Starter',    default => 'Growth',
        };
        [$tc, $tb] = match($p['segment']) {
            'champion' => ['#FFD700', '#0A0700'], 'loyal'    => ['#A855F7', '#0A0014'],
            'at_risk'  => ['#F43F5E', '#140005'], 'new'      => ['#0EA5E9', '#00080E'],
            'dormant'  => ['#10B981', '#000D09'], default    => ['#0EA5E9', '#00080E'],
        };
        $ac = match($p['segment']) {
            'champion' => '#FFD700', 'loyal' => '#A855F7', 'at_risk' => '#F43F5E',
            'new' => '#0EA5E9', 'dormant' => '#10B981', default => '#0EA5E9',
        };

        $base = [
            'id' => $p['id'], 'name' => $p['name'], 'initials' => $this->getInitials($p['name']),
            'company' => $this->getCompany($p['email'] ?? ''), 'industry' => 'EdTech',
            'tier' => $plan, 'tierColor' => $tc, 'tierBg' => $tb, 'avatarColor' => $ac,
        ];

        $last = $this->timeAgo($p['last_login']);
        $days = $this->daysSince($p['last_login']);

        return match($type) {
            'hot' => array_merge($base, [
                'amountLabel' => "Intent {$p['intent_score']}",
                'tags' => array_filter([
                    $p['readiness_score'] >= 80 ? 'Highest intent' : null,
                    $p['performance_score'] >= 80 ? 'High performer' : null,
                    $p['total_spent'] > 0 ? 'Paid learner' : null,
                    $p['completed_courses'] >= 3 ? 'Course completer' : null,
                ]),
                'fields' => [
                    ['Intent score', $p['intent_score'] . '/100'],
                    ['Buying readiness', $p['readiness_score'] . '/100'],
                    ['Avg quiz score', $p['avg_quiz_score'] . '/100'],
                    ['Courses completed', $p['completed_courses'] . '/' . $p['enrollment_count']],
                    ['Total spent', '$' . number_format($p['total_spent'])],
                    ['Loyalty score', $p['loyalty_score'] . '/100'],
                    ['Churn risk', $p['churn_score'] . '%'],
                    ['Engagement', $p['engagement_score'] . '/100'],
                    ['Last active', $last],
                ],
                'timeline' => [
                    ['e' => "Quiz avg {$p['avg_quiz_score']} — strong performance", 't' => '3 days ago', 'c' => '#0EA5E9'],
                    ['e' => "Completed {$p['completed_courses']} courses", 't' => '1 day ago', 'c' => '#10B981'],
                    ['e' => "Intent {$p['intent_score']} + Readiness {$p['readiness_score']} → Hot Learner", 't' => $last, 'c' => '#F59E0B'],
                    ['e' => "Upsell offer prepared — awaiting dispatch", 't' => 'now', 'c' => '#FF6B35'],
                ],
                'actions' => [
                    ['l' => 'Send upsell offer', 'c' => '#10B981', 'bc' => '#10B98144'],
                    ['l' => 'Preview offer', 'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
                ],
            ]),
            'atrisk' => array_merge($base, [
                'amountLabel' => '$' . number_format(max(100, $p['total_spent'] * 2)) . ' at risk',
                'tags' => array_filter([
                    $p['churn_score'] >= 75 ? 'Imminent churn' : null,
                    $p['avg_progress'] < 20 ? 'Stalled progress' : null,
                    $days > 14 ? 'Inactive 14d+' : null,
                    $p['frustration_score'] > 50 ? 'Frustrated' : null,
                ]),
                'fields' => [
                    ['Churn probability', $p['churn_score'] . '%'],
                    ['Revenue at risk', '$' . number_format(max(100, $p['total_spent'] * 2))],
                    ['Days since login', $days . 'd'],
                    ['Avg progress', $p['avg_progress'] . '%'],
                    ['Frustration score', $p['frustration_score'] . '/100'],
                    ['Engagement score', $p['engagement_score'] . '/100'],
                    ['Total spent', '$' . number_format($p['total_spent'])],
                    ['Intervention tier', $p['churn_score'] >= 75 ? 'Tier 1 — Personal outreach' : 'Tier 2 — Email sequence'],
                    ['Last active', $last],
                ],
                'timeline' => [
                    ['e' => 'Login frequency dropped — early churn signal', 't' => $days . ' days ago', 'c' => '#F59E0B'],
                    ['e' => "Progress stalled at {$p['avg_progress']}%", 't' => ($days - 2) . ' days ago', 'c' => '#F43F5E'],
                    ['e' => "Churn probability hit {$p['churn_score']}%", 't' => 'Today', 'c' => '#F43F5E'],
                    ['e' => 'Intervention queued', 't' => 'now', 'c' => '#0EA5E9'],
                ],
                'actions' => [
                    ['l' => 'Send win-back', 'c' => '#F43F5E', 'bc' => '#F43F5E44'],
                    ['l' => 'Escalate to tutor', 'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
                ],
            ]),
            'loyal' => array_merge($base, [
                'amountLabel' => "Loyalty {$p['loyalty_score']}",
                'tags' => array_filter([
                    $p['loyalty_score'] > 80 ? 'Ambassador ready' : null,
                    $p['completed_courses'] >= 5 ? 'Super learner' : null,
                    $p['performance_score'] >= 85 ? 'Top performer' : null,
                    $p['total_spent'] > 500 ? 'High value' : null,
                ]),
                'fields' => [
                    ['Loyalty score', $p['loyalty_score'] . '/100'],
                    ['Courses completed', $p['completed_courses'] . ''],
                    ['Avg quiz score', $p['avg_quiz_score'] . '/100'],
                    ['Engagement', $p['engagement_score'] . '/100'],
                    ['Churn risk', $p['churn_score'] . '%'],
                    ['Next tier gap', max(0, (100 - $p['loyalty_score']) * 12) . ' pts'],
                    ['Total spent', '$' . number_format($p['total_spent'])],
                    ['Last active', $last],
                    ['Industry', 'EdTech'],
                ],
                'timeline' => [
                    ['e' => 'Loyalty milestone reached', 't' => '2 days ago', 'c' => '#10B981'],
                    ['e' => "Loyalty score {$p['loyalty_score']} confirmed", 't' => '1 day ago', 'c' => '#F59E0B'],
                    ['e' => "{$p['completed_courses']} courses completed", 't' => 'this month', 'c' => '#10B981'],
                    ['e' => $p['loyalty_score'] > 80 ? 'Ambassador invitation ready' : 'Tier nudge ready', 't' => 'now', 'c' => '#0EA5E9'],
                ],
                'actions' => [
                    ['l' => 'Send referral invite', 'c' => '#10B981', 'bc' => '#10B98144'],
                    ['l' => $p['loyalty_score'] > 80 ? 'Send ambassador invite' : 'Send tier nudge', 'c' => '#F59E0B', 'bc' => '#F59E0B44'],
                ],
            ]),
            'react' => array_merge($base, [
                'amountLabel' => "Score {$p['reactivation_potential']}",
                'tags' => array_filter([
                    $p['reactivation_potential'] >= 80 ? 'High potential' : null,
                    $p['engagement_score'] < 20 ? 'Deeply dormant' : null,
                    $p['total_spent'] > 0 ? 'Past customer' : null,
                ]),
                'fields' => [
                    ['Reactivation score', $p['reactivation_potential'] . '/100'],
                    ['Churn reason', $p['frustration_score'] > 40 ? 'Difficulty with content' : 'Life events / competing priorities'],
                    ['Days since active', $days . 'd'],
                    ['Previous spend', '$' . number_format($p['total_spent'])],
                    ['Email response', $p['reactivation_potential'] > 78 ? 'High' : 'Medium'],
                    ['Win-back message', substr($p['frustration_score'] > 40 ? 'Personal tutoring session + simplified study plan' : 'We miss you — 30% off your next course', 0, 40) . '…'],
                    ['Last plan', $plan],
                    ['Industry', 'EdTech'],
                    ['Last active', $last],
                ],
                'timeline' => [
                    ['e' => 'Churned — primary reason identified', 't' => $days . 'd ago', 'c' => '#F43F5E'],
                    ['e' => "Reactivation score {$p['reactivation_potential']} reached", 't' => 'Today', 'c' => '#10B981'],
                    ['e' => 'Segment assigned', 't' => '< 1hr ago', 'c' => '#0EA5E9'],
                    ['e' => 'Win-back message ready', 't' => 'now', 'c' => '#F59E0B'],
                ],
                'actions' => [
                    ['l' => 'Send win-back message', 'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
                    ['l' => $p['frustration_score'] > 40 ? 'Route to tutor' : 'Preview message', 'c' => '#10B981', 'bc' => '#10B98144'],
                ],
            ]),
            'struggle' => array_merge($base, [
                'amountLabel' => "Help Needed",
                'tags' => array_filter(['Needs help', 'Low quiz scores',
                    $p['avg_progress'] < 20 ? 'Stalled progress' : null,
                    $p['login_count_30d'] > 5 ? 'Trying hard' : null,
                    $p['frustration_score'] > 60 ? 'Highly frustrated' : null,
                ]),
                'fields' => [
                    ['Frustration score', $p['frustration_score'] . '/100'],
                    ['Avg quiz score', $p['avg_quiz_score'] . '/100'],
                    ['Avg progress', $p['avg_progress'] . '%'],
                    ['Login attempts', $p['login_count_30d'] . ' (30d)'],
                    ['Courses enrolled', $p['enrollment_count'] . ''],
                    ['Engagement', $p['engagement_score'] . '/100'],
                    ['Recommended action', 'Tutor + simplified content'],
                    ['Last active', $last],
                    ['Industry', 'EdTech'],
                ],
                'timeline' => [
                    ['e' => "Quiz score {$p['avg_quiz_score']} — struggling detected", 't' => '3 days ago', 'c' => '#F43F5E'],
                    ['e' => "Progress stalled at {$p['avg_progress']}%", 't' => '2 days ago', 'c' => '#F59E0B'],
                    ['e' => "{$p['login_count_30d']} logins but low progress", 't' => '1 day ago', 'c' => '#0EA5E9'],
                    ['e' => 'Tutor intervention queued', 't' => 'now', 'c' => '#10B981'],
                ],
                'actions' => [
                    ['l' => 'Assign tutor', 'c' => '#10B981', 'bc' => '#10B98144'],
                    ['l' => 'Send study plan', 'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
                ],
            ]),
            default => $base,
        };
    }

    // ── Scenario Builder ───────────────────────────────────────────────────────

    private function buildEdTechScenarios(array $profiles): array
    {
        $hl = array_filter($profiles, fn($p) => $p['intent_score'] >= 70 && $p['readiness_score'] >= 65);
        $ar = array_filter($profiles, fn($p) => $p['churn_score'] >= 60);
        $lo = array_filter($profiles, fn($p) => $p['loyalty_score'] >= 65 && $p['engagement_score'] >= 50);
        $rv = array_filter($profiles, fn($p) => $p['reactivation_potential'] >= 60 && $p['engagement_score'] < 30);
        $sg = array_filter($profiles, fn($p) => $p['frustration_score'] >= 50 && $p['engagement_score'] >= 30);

        $hlc = count($hl); $arc = count($ar); $loc = count($lo); $rvc = count($rv); $sgc = count($sg);

        $hli = $hlc > 0 ? round(array_sum(array_column($hl, 'intent_score')) / $hlc) : 0;
        $hlr = $hlc > 0 ? round(array_sum(array_column($hl, 'readiness_score')) / $hlc) : 0;
        $arf = $hlc > 0 ? count(array_filter($hl, fn($p) => $p['trust_score'] >= 65)) : 0;
        $fp = $hlc > 0 ? (int)round($arf / $hlc * 100) : 38;

        return [
            [
                'id' => 'hotlearner', 'lc' => '#FF6B35', 'ico' => '🔥',
                'name' => 'Hot Learner — ' . $hlc . ' Students', 'users' => 'Intent >70 · Readiness >65',
                'rev' => '$' . number_format($hlc * 294) . ' · 48hr', 'urg' => 'opportunity', 'ul' => 'Revenue ready', 'urgDot' => '#10B981',
                'banner' => ['text' => $hlc . ' students in active learning mode. AI prepared course recommendations. 48-hour window: 34% conversion.', 'c' => '#FF6B35'],
                'kpis' => [
                    ['v' => (string)$hlc, 'l' => 'Hot Learners', 'p' => min(100, $hlc * 12), 'c' => '#FF6B35'],
                    ['v' => (string)$hli, 'l' => 'Avg intent score', 'p' => $hli, 'c' => '#10B981'],
                    ['v' => (string)$hlr, 'l' => 'Avg readiness', 'p' => $hlr, 'c' => '#FF6B35'],
                    ['v' => $fp . '%', 'l' => 'Full-price (no disc)', 'p' => $fp, 'c' => '#10B981'],
                ],
                'dec' => [
                    ['l' => 'Act within 48 hours', 'v' => '34%', 's' => round($hlc * .34) . ' enrollments · $' . number_format($hlc * 294) . ' revenue', 'g' => true],
                    ['l' => 'Act after 48 hours', 'v' => '8%', 's' => round($hlc * .08) . ' enrollments · revenue lost', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Full-price (no discount)', 'v' => $arf . ' students'],
                    ['l' => 'Calibrated discount offer', 'v' => ($hlc - $arf) . ' students'],
                    ['l' => 'Expected conversions', 'v' => round($hlc * .34) . ' · $' . number_format($hlc * 294)],
                    ['l' => 'Window', 'v' => '48 hours'],
                ],
                'ctas' => [
                    ['ico' => '🚀', 'l' => 'Dispatch offers to all ' . $hlc . ' Hot Learners', 'd' => $arf . ' full-price + ' . ($hlc - $arf) . ' discount offers. Within 2 hours.', 'b' => 'Est. $' . number_format($hlc * 294), 'c' => '#FF6B35', 'view' => 'hl_all', 'vt' => 'Hot Learners — All ' . $hlc . ' Students', 'vs' => 'Click any student to see intent breakdown and recommendations', 'vf' => ['All', 'Full price', '10% discount', 'High performer', 'Highest intent']],
                    ['ico' => '📞', 'l' => 'Flag high-value learners for tutor', 'd' => 'High-spending students. Tutor contact justified by LTV.', 'b' => count(array_filter($hl, fn($p) => $p['total_spent'] > 200)) . ' students', 'c' => '#A855F7', 'view' => 'hl_tutor', 'vt' => 'High-Value Learners', 'vs' => 'Tutor outreach recommended', 'vf' => ['All']],
                ],
            ],
            [
                'id' => 'atrisk', 'lc' => '#F43F5E', 'ico' => '⚠️',
                'name' => 'At Risk — ' . $arc . ' Students', 'users' => 'Churn >60 · Engagement <30',
                'rev' => '$' . number_format($arc * 1200) . ' at risk', 'urg' => 'critical', 'ul' => '72-hour window', 'urgDot' => '#F43F5E',
                'banner' => ['text' => $arc . ' students crossed churn threshold. $' . number_format($arc * 1200) . ' revenue at risk. Act within 72 hours: 41% retention.', 'c' => '#F43F5E'],
                'kpis' => [
                    ['v' => (string)$arc, 'l' => 'At Risk students', 'p' => min(100, $arc * 8), 'c' => '#F43F5E'],
                    ['v' => '$' . number_format($arc * 1200), 'l' => 'Revenue at risk', 'p' => 80, 'c' => '#F43F5E'],
                    ['v' => (string)($arc > 0 ? round(array_sum(array_column($ar, 'churn_score')) / $arc) : 0), 'l' => 'Avg churn score', 'p' => $arc > 0 ? round(array_sum(array_column($ar, 'churn_score')) / $arc) : 0, 'c' => '#F43F5E'],
                    ['v' => (string)($arc > 0 ? round(array_sum(array_column($ar, 'frustration_score')) / $arc) : 0), 'l' => 'Avg frustration', 'p' => $arc > 0 ? round(array_sum(array_column($ar, 'frustration_score')) / $arc) : 0, 'c' => '#F59E0B'],
                ],
                'dec' => [
                    ['l' => 'Act within 72 hours', 'v' => '41%', 's' => round($arc * .41) . ' retained · revenue protected', 'g' => true],
                    ['l' => 'Wait beyond 72hrs', 'v' => '<20%', 's' => round($arc * .20) . ' retained · revenue lost', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Students in win-back', 'v' => $arc . ''],
                    ['l' => 'Expected retentions', 'v' => round($arc * .41) . ' (41%)'],
                    ['l' => 'Revenue protected', 'v' => 'Est. $' . number_format($arc * 1200)],
                    ['l' => 'Decision window', 'v' => '72 hours'],
                ],
                'ctas' => [
                    ['ico' => '⚡', 'l' => 'Launch 3-tier win-back plan', 'd' => 'High LTV → Tutor + offer. Mid LTV → 3-touch sequence. Free → email.', 'b' => '$' . number_format($arc * 1200) . ' protected', 'c' => '#F43F5E', 'view' => 'ar_all', 'vt' => 'At Risk — All ' . $arc . ' Students', 'vs' => 'Ranked by churn risk · click to see risk factors', 'vf' => ['All', 'Imminent churn', 'Stalled progress']],
                    ['ico' => '📊', 'l' => 'Top students by Revenue at Risk', 'd' => 'Ranked by churn probability × LTV.', 'b' => 'Priority list', 'c' => '#F59E0B', 'view' => 'ar_top50', 'vt' => 'Top Students by Revenue at Risk', 'vs' => 'Highest priority — ranked by churn × LTV', 'vf' => ['All']],
                    ['ico' => '🎁', 'l' => 'Approve retention discount', 'd' => 'LTV > threshold eligible for 20% off. Recovers additional 12%.', 'b' => $arc . ' eligible', 'c' => '#00FFB2', 'view' => 'ar_disc', 'vt' => 'Retention Offer', 'vs' => 'Discount approved · awaiting dispatch', 'vf' => ['All', 'Imminent churn']],
                ],
            ],
            [
                'id' => 'loyal', 'lc' => '#10B981', 'ico' => '⭐',
                'name' => 'Loyal Learners — ' . $loc, 'users' => 'Loyalty >65 · Active learners',
                'rev' => '+$' . number_format($loc * 1480) . ' potential', 'urg' => 'opportunity', 'ul' => 'Amplify', 'urgDot' => '#10B981',
                'banner' => ['text' => $loc . ' students in Loyal Learner state — 40% lower churn, 3.2× LTV. Referral amplification ready.', 'c' => '#10B981'],
                'kpis' => [
                    ['v' => (string)$loc, 'l' => 'Loyal Learners', 'p' => 100, 'c' => '#10B981'],
                    ['v' => (string)($loc > 0 ? round(array_sum(array_column($lo, 'loyalty_score')) / $loc) : 0), 'l' => 'Avg loyalty score', 'p' => $loc > 0 ? round(array_sum(array_column($lo, 'loyalty_score')) / $loc) : 0, 'c' => '#10B981'],
                    ['v' => (string)($loc > 0 ? round(array_sum(array_column($lo, 'engagement_score')) / $loc) : 0), 'l' => 'Avg engagement', 'p' => $loc > 0 ? round(array_sum(array_column($lo, 'engagement_score')) / $loc) : 0, 'c' => '#F59E0B'],
                    ['v' => '+$' . number_format($loc * 1480), 'l' => 'Referral potential', 'p' => 65, 'c' => '#0EA5E9'],
                ],
                'dec' => [
                    ['l' => 'Amplify now', 'v' => '+$' . number_format($loc * 1480), 's' => 'Additional referral revenue this month', 'g' => true],
                    ['l' => 'No action', 'v' => '$0', 's' => 'Momentum declines · revenue missed', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Learners notified', 'v' => $loc . ''],
                    ['l' => 'Near-tier nudges', 'v' => round($loc * .4) . ''],
                    ['l' => 'Ambassador invitations', 'v' => count(array_filter($lo, fn($p) => $p['loyalty_score'] > 80)) . ''],
                    ['l' => 'Expected referral revenue', 'v' => '+$' . number_format($loc * 1480) . '/month'],
                ],
                'ctas' => [
                    ['ico' => '⭐', 'l' => 'Launch referral amplification', 'd' => 'All ' . $loc . ' get referral message + bonus points.', 'b' => '+$' . number_format($loc * 1480) . ' revenue', 'c' => '#10B981', 'view' => 'loyal_all', 'vt' => 'Loyal Learners — All ' . $loc, 'vs' => 'Click any student to see loyalty score and tier gap', 'vf' => ['All', 'Super learner', 'Near tier upgrade', 'Ambassador ready']],
                    ['ico' => '🎖️', 'l' => 'Ambassador invitations — top ' . count(array_filter($lo, fn($p) => $p['loyalty_score'] > 80)), 'd' => 'Top learners — early course access + dedicated tutor.', 'b' => count(array_filter($lo, fn($p) => $p['loyalty_score'] > 80)) . ' invitations', 'c' => '#FFD700', 'view' => 'loyal_amb', 'vt' => 'Ambassador Candidates', 'vs' => 'Highest loyalty · early access + dedicated tutor', 'vf' => ['All']],
                ],
            ],
            [
                'id' => 'reactready', 'lc' => '#0EA5E9', 'ico' => '🔄',
                'name' => 'Reactivation Ready — ' . $rvc, 'users' => 'Churned · High reactivation score',
                'rev' => '$' . number_format($rvc * 4200) . ' recoverable', 'urg' => 'opportunity', 'ul' => 'Revenue recovery', 'urgDot' => '#10B981',
                'banner' => ['text' => $rvc . ' churned students scored high on reactivation. Personalised win-back ready. 31% CVR vs 8% generic.', 'c' => '#0EA5E9'],
                'kpis' => [
                    ['v' => (string)$rvc, 'l' => 'Reactivation candidates', 'p' => min(100, $rvc * 12), 'c' => '#0EA5E9'],
                    ['v' => (string)($rvc > 0 ? round(array_sum(array_column($rv, 'reactivation_potential')) / $rvc) : 0), 'l' => 'Avg reactivation score', 'p' => $rvc > 0 ? round(array_sum(array_column($rv, 'reactivation_potential')) / $rvc) : 0, 'c' => '#0EA5E9'],
                    ['v' => (string)($rvc > 0 ? round(array_sum(array_column($rv, 'engagement_score')) / $rvc) : 0), 'l' => 'Avg engagement (dormant)', 'p' => $rvc > 0 ? round(array_sum(array_column($rv, 'engagement_score')) / $rvc) : 0, 'c' => '#F43F5E'],
                    ['v' => '$' . number_format($rvc * 4200), 'l' => 'Re-revenue potential', 'p' => 75, 'c' => '#F59E0B'],
                ],
                'dec' => [
                    ['l' => 'Personalised (4 variants)', 'v' => '31%', 's' => round($rvc * .31) . ' return · $' . number_format($rvc * 4200) . '/year', 'g' => true],
                    ['l' => 'Generic campaign', 'v' => '8%', 's' => round($rvc * .08) . ' return · revenue missed', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Segments deployed', 'v' => '4 personalised'],
                    ['l' => 'Expected reactivations', 'v' => round($rvc * .31) . ' (31%)'],
                    ['l' => 'Re-revenue', 'v' => '$' . number_format($rvc * 4200) . '/year'],
                    ['l' => 'Net ROI', 'v' => 'Positive from month 3'],
                ],
                'ctas' => [
                    ['ico' => '🔄', 'l' => 'Launch personalised win-back', 'd' => 'Difficulty → tutoring. Price → discount. Support → apology. All in 24hrs.', 'b' => '$' . number_format($rvc * 4200) . ' re-revenue', 'c' => '#0EA5E9', 'view' => 'reactiv_all', 'vt' => 'Win-Back Campaign — ' . $rvc . ' Students', 'vs' => '4 segments · click any student to see churn reason and message', 'vf' => ['All', 'High potential', 'Deeply dormant']],
                    ['ico' => '📞', 'l' => 'Route struggling students to tutor', 'd' => 'Difficulty-based churn students respond better to tutor call.', 'b' => round($rvc * .35) . ' students', 'c' => '#F43F5E', 'view' => 'reactiv_tutor', 'vt' => 'Struggling Students Segment', 'vs' => 'Tutor outreach recommended', 'vf' => ['All']],
                ],
            ],
            [
                'id' => 'struggling', 'lc' => '#F59E0B', 'ico' => '📚',
                'name' => 'Struggling Students — ' . $sgc, 'users' => 'Frustration >50 · Still engaged',
                'rev' => '$' . number_format($sgc * 500) . ' intervention value', 'urg' => 'warning', 'ul' => 'Intervene now', 'urgDot' => '#F59E0B',
                'banner' => ['text' => $sgc . ' students struggling but still engaged. Early intervention prevents churn. Tutor support can recover 65%.', 'c' => '#F59E0B'],
                'kpis' => [
                    ['v' => (string)$sgc, 'l' => 'Struggling students', 'p' => min(100, $sgc * 10), 'c' => '#F59E0B'],
                    ['v' => (string)($sgc > 0 ? round(array_sum(array_column($sg, 'frustration_score')) / $sgc) : 0), 'l' => 'Avg frustration', 'p' => $sgc > 0 ? round(array_sum(array_column($sg, 'frustration_score')) / $sgc) : 0, 'c' => '#F43F5E'],
                    ['v' => (string)($sgc > 0 ? round(array_sum(array_column($sg, 'avg_quiz_score')) / $sgc) : 0), 'l' => 'Avg quiz score', 'p' => $sgc > 0 ? round(array_sum(array_column($sg, 'avg_quiz_score')) / $sgc) : 0, 'c' => '#0EA5E9'],
                    ['v' => '65%', 'l' => 'Recovery with intervention', 'p' => 65, 'c' => '#10B981'],
                ],
                'dec' => [
                    ['l' => 'Tutor intervention + content adjustment', 'v' => '65%', 's' => round($sgc * .65) . ' recovered · $' . number_format(round($sgc * .65) * 500) . ' retained', 'g' => true],
                    ['l' => 'No intervention', 'v' => '18%', 's' => 'Natural recovery · most will churn', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Students receiving help', 'v' => $sgc . ''],
                    ['l' => 'Expected recoveries', 'v' => round($sgc * .65) . ' (65%)'],
                    ['l' => 'Revenue retained', 'v' => '$' . number_format(round($sgc * .65) * 500)],
                    ['l' => 'Intervention window', 'v' => '48 hours'],
                ],
                'ctas' => [
                    ['ico' => '👨‍🏫', 'l' => 'Assign tutor to all ' . $sgc . ' struggling students', 'd' => 'Personal tutor + simplified study plan + practice quizzes. Within 24 hours.', 'b' => round($sgc * .65) . ' recoveries', 'c' => '#F59E0B', 'view' => 'struggle_all', 'vt' => 'Struggling Students — All ' . $sgc, 'vs' => 'Ranked by frustration score · click to see quiz history', 'vf' => ['All', 'High frustration', 'Low quiz scores']],
                ],
            ],
        ];
    }

    // ── User Group Builder ─────────────────────────────────────────────────────

    private function buildEdTechUserGroups(array $profiles): array
    {
        $hl = array_filter($profiles, fn($p) => $p['intent_score'] >= 70 && $p['readiness_score'] >= 65);
        $ar = array_filter($profiles, fn($p) => $p['churn_score'] >= 60);
        $lo = array_filter($profiles, fn($p) => $p['loyalty_score'] >= 65 && $p['engagement_score'] >= 50);
        $rv = array_filter($profiles, fn($p) => $p['reactivation_potential'] >= 60 && $p['engagement_score'] < 30);
        $sg = array_filter($profiles, fn($p) => $p['frustration_score'] >= 50 && $p['engagement_score'] >= 30);

        return [
            'hl_all'        => array_values(array_map(fn($p) => $this->edCard($p, 'hot'), $hl)),
            'hl_tutor'      => array_values(array_map(fn($p) => $this->edCard($p, 'hot'), array_filter($hl, fn($p) => $p['total_spent'] > 200))),
            'ar_all'        => array_values(array_map(fn($p) => $this->edCard($p, 'atrisk'), $ar)),
            'ar_top50'      => array_slice(array_values(array_map(fn($p) => $this->edCard($p, 'atrisk'), $ar)), 0, 5),
            'ar_disc'       => array_values(array_map(fn($p) => $this->edCard($p, 'atrisk'), $ar)),
            'loyal_all'     => array_values(array_map(fn($p) => $this->edCard($p, 'loyal'), $lo)),
            'loyal_amb'     => array_values(array_map(fn($p) => $this->edCard($p, 'loyal'), array_filter($lo, fn($p) => $p['loyalty_score'] > 80))),
            'reactiv_all'   => array_values(array_map(fn($p) => $this->edCard($p, 'react'), $rv)),
            'reactiv_tutor' => array_values(array_map(fn($p) => $this->edCard($p, 'react'), array_filter($rv, fn($p) => $p['frustration_score'] > 40))),
            'struggle_all'  => array_values(array_map(fn($p) => $this->edCard($p, 'struggle'), $sg)),
        ];
    }

    // public function l5()
    // {
    //     return view('client.decision-centre', $this->prepareData() + ['activeLayer' => 'l5']);
    // }

    public function l5()
    {
        $client = auth('client')->user();
        $dataSource = $client ? $client->dataSources()->first() : null;
        $hasConnectedSource = $dataSource && $dataSource->status === 'connected';
        $dataSourceConnected = false;
        $profiles = null;
        $edProfiles = [];

        // Try to use connected EdTech data if available
        if ($hasConnectedSource) {
            try {
                $engine = $this->getEdTechEngine();
                if ($engine && $engine->isConnected()) {
                    $edProfiles = $engine->getStudentProfiles(100);
                    if (!empty($edProfiles)) {
                        $dataSourceConnected = true;
                        $profiles = collect($edProfiles)->map(
                            fn($p) => new \App\Adapters\EdTechProfileAdapter($p)
                        );
                    }
                }
            } catch (\Exception $e) {
                \Log::error('EdTech L5 data fetch failed: ' . $e->getMessage());
            }
        }

        // Use EdTech data for scenarios if connected, else fallback to demo
        if ($dataSourceConnected && !empty($edProfiles)) {
            $data = $this->prepareEdTechData($edProfiles);
        } else {
            if (!isset($profiles) || $profiles === null) {
                $profiles = BehavioralProfile::orderByDesc('overall_score')->get();
            }
            $data = $this->prepareData();
        }

        return view('client.layers.l5', [
            'scenarios'           => $data['scenarios'],
            'userGroups'          => $data['userGroups'],
            'activeLayer'         => 'l5',
            'dataSourceConnected' => $dataSourceConnected,
            'profiles'            => $profiles,
            'emailTemplates'      => $data['emailTemplates'] ?? [],  // <-- ADD THIS
        ]);
    }

    public function aimlDashboard()
    {
        $client = auth('client')->user();
        $dataSource = $client ? $client->dataSources()->first() : null;
        $hasConnectedSource = $dataSource && $dataSource->status === 'connected';
        $dataSourceConnected = false;
        $edProfiles = [];

        if ($hasConnectedSource) {
            try {
                $engine = $this->getEdTechEngine();
                if ($engine && $engine->isConnected()) {
                    $edProfiles = $engine->getStudentProfiles(100);
                    if (!empty($edProfiles)) {
                        $dataSourceConnected = true;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('EdTech dashboard fetch failed: ' . $e->getMessage());
            }
        }

        // Use EdTech data for scenarios if connected, else fallback to demo
        if ($dataSourceConnected && !empty($edProfiles)) {
            $data = $this->prepareEdTechData($edProfiles);
        } else {
            $data = $this->prepareData();
        }

        return view('client.aiml-dashboard', [
            'scenarios'           => $data['scenarios'],
            'userGroups'          => $data['userGroups'],
            'dataSourceConnected' => $dataSourceConnected,
            'profiles'            => $dataSourceConnected ? collect($edProfiles) : BehavioralProfile::orderByDesc('overall_score')->get(),
        ]);
    }

    // ── Shared card helper ─────────────────────────────────────────────────────
    private function makeCard(BehavioralProfile $p, string $amountLabel, array $tags, array $fields, array $timeline, array $actions = []): array
    {
        $plan = $this->plan($p->segment);
        [$tc, $tb] = $this->tierStyle($p->segment);
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
            'amountLabel' => $amountLabel,
            'tags'        => $tags,
            'fields'      => $fields,
            'timeline'    => $timeline,
            'actions'     => $actions ?: [['View profile', '#0EA5E9', 'outline'], ['Take action', '#00FFB2', 'bg']],
        ];
    }

    private function identityCard(BehavioralProfile $p): array
    {
        $last = $p->last_active_at?->diffForHumans() ?? 'Unknown';
        $conf = $p->trust_score >= 70 ? 'High' : ($p->trust_score >= 50 ? 'Medium' : 'Low');
        $tags = [];
        if ($p->trust_score < 30) $tags[] = 'Very low trust';
        elseif ($p->trust_score < 50) $tags[] = 'Low trust';
        if ($p->engagement_score >= 70) $tags[] = 'High engagement';
        if ($p->segment === 'champion') $tags[] = 'Enterprise';
        return $this->makeCard($p, 'Trust ' . $p->trust_score, $tags, [
            ['Trust score',      $p->trust_score . '/100'],
            ['Email confidence', $conf],
            ['Engagement',       $p->engagement_score . '/100'],
            ['Identity type',    $p->trust_score < 40 ? 'Anonymous' : 'Semi-known'],
            ['Last active',      $last],
            ['Overall score',    $p->overall_score . '/100'],
        ], [
            ['e' => 'Profile flagged — trust score ' . $p->trust_score, 't' => '5 days ago', 'c' => '#F59E0B'],
            ['e' => 'Identity signal gap detected',                       't' => '2 days ago', 'c' => '#F43F5E'],
            ['e' => 'Queued for identity resolution',                     't' => $last,        'c' => '#0EA5E9'],
        ], [
            ['Re-verify', '#F59E0B', 'bg'],
            ['View signals', '#0EA5E9', 'outline'],
        ]);
    }

    private function processingCard(BehavioralProfile $p): array
    {
        $last      = $p->last_active_at?->diffForHumans() ?? 'Unknown';
        $staleDays = $p->last_active_at ? (int) $p->last_active_at->diffInDays(now()) : 45;
        $tags = [];
        if ($staleDays > 60) $tags[] = 'Highly stale';
        elseif ($staleDays > 30) $tags[] = 'Stale';
        if ($p->dropoff_risk >= 70) $tags[] = 'High dropout risk';
        if ($p->overall_score >= 70) $tags[] = 'ML-ready';
        return $this->makeCard($p, 'Stale ' . $staleDays . 'd', $tags, [
            ['Last active',        $last],
            ['Days stale',         $staleDays . ' days'],
            ['Dropoff risk',       $p->dropoff_risk . '/100'],
            ['Overall score',      $p->overall_score . '/100'],
            ['Engagement',         $p->engagement_score . '/100'],
            ['Reactivation score', $p->reactivation_potential . '/100'],
        ], [
            ['e' => 'Last feature update processed',              't' => $last,        'c' => '#0EA5E9'],
            ['e' => 'Dropoff risk elevated to ' . $p->dropoff_risk, 't' => '3 days ago', 'c' => '#F59E0B'],
            ['e' => 'Flagged for pipeline reprocessing',           't' => 'today',      'c' => '#F43F5E'],
        ], [
            ['Reprocess', '#0EA5E9', 'bg'],
            ['View pipeline', '#A855F7', 'outline'],
        ]);
    }

    private function decisionCard(BehavioralProfile $p): array
    {
        $last     = $p->last_active_at?->diffForHumans() ?? 'Unknown';
        $conflict = $p->churn_score >= 50 && $p->intent_score >= 60;
        $tags = [];
        if ($conflict) $tags[] = 'Signal conflict';
        if ($p->frustration_score >= 60) $tags[] = 'Frequency cap';
        if ($p->engagement_score >= 75) $tags[] = 'Budget ceiling';
        if ($p->segment === 'champion') $tags[] = 'Enterprise';
        return $this->makeCard($p, 'Conflict ' . (int) (($p->churn_score + $p->intent_score) / 2), $tags, [
            ['Intent score',    $p->intent_score . '/100'],
            ['Churn score',     $p->churn_score . '/100'],
            ['Frustration',     $p->frustration_score . '/100'],
            ['Engagement',      $p->engagement_score . '/100'],
            ['Signal conflict', $conflict ? 'Yes — review' : 'No'],
            ['Last active',     $last],
        ], [
            ['e' => 'Intent spike detected — ' . $p->intent_score,         't' => '4 days ago', 'c' => '#10B981'],
            ['e' => 'Churn signal crossed threshold — ' . $p->churn_score, 't' => '2 days ago', 'c' => '#F43F5E'],
            ['e' => 'Conflicting signals flagged for review',                't' => $last,        'c' => '#F59E0B'],
        ], [
            ['Resolve conflict', '#F59E0B', 'bg'],
            ['Override rule', '#A855F7', 'outline'],
        ]);
    }

    private function applicationCard(BehavioralProfile $p): array
    {
        $last    = $p->last_active_at?->diffForHumans() ?? 'Unknown';
        $isExec  = $p->overall_score >= 75 && $p->churn_score >= 65 && $p->segment === 'champion';
        $trigger = $isExec ? 'Executive alert' : ($p->buying_readiness >= 80 ? '1-Click queue' : 'CSM queue');
        $tags = [];
        if ($isExec) $tags[] = 'Executive alert';
        if ($p->buying_readiness >= 80) $tags[] = '1-Click ready';
        if ($p->churn_score >= 65 && in_array($p->segment, ['champion', 'loyal'])) $tags[] = 'CSM queue';
        if ($p->segment === 'champion') $tags[] = 'Enterprise';
        return $this->makeCard($p, 'Priority ' . $p->overall_score, $tags, [
            ['Overall score',    $p->overall_score . '/100'],
            ['Churn risk',       $p->churn_score . '%'],
            ['Buying readiness', $p->buying_readiness . '/100'],
            ['Segment',          $p->segmentLabel()],
            ['App trigger',      $trigger],
            ['Last active',      $last],
        ], [
            ['e' => 'Extreme score pattern — overall ' . $p->overall_score, 't' => '1 day ago', 'c' => '#0EA5E9'],
            ['e' => 'Application layer triggered — ' . $trigger,             't' => $last,       'c' => '#F43F5E'],
            ['e' => 'Queued for action',                                      't' => 'now',       'c' => '#10B981'],
        ], [
            ['Take action', '#00FFB2', 'bg'],
            ['Explain AI', '#A855F7', 'outline'],
        ]);
    }

    private function governanceCard(BehavioralProfile $p): array
    {
        $last      = $p->last_active_at?->diffForHumans() ?? 'Unknown';
        $staleDays = $p->last_active_at ? (int) $p->last_active_at->diffInDays(now()) : 400;
        $isAccess  = $p->trust_score < 25;
        $tags = [];
        if ($staleDays > 60) $tags[] = 'Retention breach';
        elseif ($staleDays > 30) $tags[] = 'GDPR risk';
        if ($isAccess) $tags[] = 'Access violation';
        if ($p->segment === 'champion') $tags[] = 'Enterprise';
        return $this->makeCard($p, 'Risk ' . ($isAccess ? 'Access' : 'Data'), $tags, [
            ['Last active',   $last],
            ['Days inactive', $staleDays . ' days'],
            ['Trust score',   $p->trust_score . '/100'],
            ['GDPR risk',     $staleDays > 30 ? 'Yes — ' . $staleDays . 'd inactive' : 'No'],
            ['Access risk',   $isAccess ? 'Yes — trust ' . $p->trust_score : 'No'],
            ['Segment',       $p->segmentLabel()],
        ], [
            ['e' => 'Data retention threshold exceeded — ' . $staleDays . 'd inactive', 't' => $last,        'c' => '#F43F5E'],
            ['e' => 'GDPR review flagged for profile',                                   't' => '7 days ago', 'c' => '#F59E0B'],
            ['e' => 'Governance audit queued',                                            't' => 'today',      'c' => '#0EA5E9'],
        ], [
            ['Review & action', '#F43F5E', 'bg'],
            ['View audit log', '#0EA5E9', 'outline'],
        ]);
    }

    // ── L2 – Identity Resolution ───────────────────────────────────────────────
    public function l2()
    {
        $all = BehavioralProfile::orderByDesc('overall_score')->get();

        $lowConf   = $all->filter(fn($p) => $p->trust_score < 50);
        $anonConv  = $all->filter(fn($p) => $p->engagement_score >= 60 && $p->trust_score < 40);
        $conflicts = $all->filter(fn($p) => $p->trust_score >= 30 && $p->trust_score < 55);
        $household = $all->filter(fn($p) => $p->loyalty_score >= 55 && $p->trust_score >= 60);

        $lcCnt = $lowConf->count(); $acCnt = $anonConv->count();
        $cfCnt = $conflicts->count(); $hhCnt = $household->count();

        $scenarios = [
            [
                'id' => 'lowconf', 'lc' => '#F59E0B', 'ico' => '⚠️',
                'name' => 'Low-Confidence Profiles — ' . $lcCnt . ' Users',
                'users' => 'Trust score < 50', 'rev' => $lcCnt . ' identity gaps',
                'urg' => 'warning', 'ul' => 'Verify now', 'urgDot' => '#F59E0B',
                'banner' => ['text' => $lcCnt . ' profiles have identity confidence below 50. Unverified identities reduce personalisation accuracy by up to 18%. Resolve before next scoring cycle to protect model quality.', 'c' => '#F59E0B'],
                'kpis' => [
                    ['v' => (string)$lcCnt, 'l' => 'Low-confidence profiles', 'p' => min(100, $lcCnt * 8), 'c' => '#F59E0B'],
                    ['v' => (string)(int)round($lowConf->avg('trust_score') ?? 0), 'l' => 'Avg trust score', 'p' => (int)round($lowConf->avg('trust_score') ?? 0), 'c' => '#F43F5E'],
                    ['v' => (string)(int)round($lowConf->avg('engagement_score') ?? 0), 'l' => 'Avg engagement', 'p' => (int)round($lowConf->avg('engagement_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => '94%', 'l' => 'Re-verify rate', 'p' => 94, 'c' => '#10B981'],
                ],
                'dec' => [
                    ['l' => 'Re-verify via deterministic signals', 'v' => '94%', 's' => 'Email hash + device fingerprint — ' . round($lcCnt * .94) . ' resolved', 'g' => true],
                    ['l' => 'Leave unresolved', 'v' => '-18%', 's' => 'Personalisation accuracy drops · churn model skewed', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Profiles re-verified', 'v' => round($lcCnt * .94) . ' estimated'],
                    ['l' => 'Method', 'v' => 'Email hash + device fingerprint'],
                    ['l' => 'Accuracy after', 'v' => '94–98%'],
                    ['l' => 'Resolution time', 'v' => '< 2 hours'],
                ],
                'ctas' => [
                    ['ico' => '🔍', 'l' => 'Re-verify all ' . $lcCnt . ' profiles', 'd' => 'Run deterministic resolution pass on all low-confidence profiles. Expected 94% match rate.', 'b' => round($lcCnt * .94) . ' resolved', 'c' => '#F59E0B', 'view' => 'lowconf_all', 'vt' => 'Low-Confidence Profiles — All ' . $lcCnt, 'vs' => 'Ranked by trust score · click to see identity signals', 'vf' => ['All', 'Very low trust', 'No email match']],
                    ['ico' => '📧', 'l' => 'Trigger email verification flow', 'd' => 'Send verification email to unresolved profiles. 72% click rate expected.', 'b' => round($lcCnt * .72) . ' responses', 'c' => '#A855F7', 'view' => 'lowconf_email', 'vt' => 'Email Verification Queue', 'vs' => 'Unresolved profiles · awaiting confirmation', 'vf' => ['All']],
                ],
            ],
            [
                'id' => 'anonconv', 'lc' => '#A855F7', 'ico' => '👤',
                'name' => 'Anon → Known — ' . $acCnt . ' Sessions',
                'users' => 'Engage ≥60 · Trust <40', 'rev' => $acCnt . ' capture opportunities',
                'urg' => 'opportunity', 'ul' => 'Convert now', 'urgDot' => '#10B981',
                'banner' => ['text' => $acCnt . ' anonymous sessions have strong behavioural signals but no deterministic identity. Targeted capture at key moments converts ~62% to known profiles with full personalisation enabled.', 'c' => '#A855F7'],
                'kpis' => [
                    ['v' => (string)$acCnt, 'l' => 'Anon sessions', 'p' => min(100, $acCnt * 10), 'c' => '#A855F7'],
                    ['v' => (string)(int)round($anonConv->avg('engagement_score') ?? 0), 'l' => 'Avg engagement', 'p' => (int)round($anonConv->avg('engagement_score') ?? 0), 'c' => '#10B981'],
                    ['v' => '62%', 'l' => 'Capture rate', 'p' => 62, 'c' => '#0EA5E9'],
                    ['v' => (string)round($acCnt * .62), 'l' => 'Expected captures', 'p' => 62, 'c' => '#10B981'],
                ],
                'dec' => [
                    ['l' => 'Targeted identity capture prompt', 'v' => '62%', 's' => round($acCnt * .62) . ' known · enables full personalisation', 'g' => true],
                    ['l' => 'No capture — remain anonymous', 'v' => '0%', 's' => 'Engagement wasted · no personalisation possible', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Sessions targeted', 'v' => (string)$acCnt],
                    ['l' => 'Expected captures', 'v' => round($acCnt * .62) . ' (62%)'],
                    ['l' => 'Trigger moment', 'v' => 'High-intent page visit'],
                    ['l' => 'Enrichment time', 'v' => 'Immediate on capture'],
                ],
                'ctas' => [
                    ['ico' => '👤', 'l' => 'Trigger identity capture for all ' . $acCnt, 'd' => 'Surface contextual sign-in prompt at next high-intent page visit. Personalised to session behaviour.', 'b' => round($acCnt * .62) . ' expected captures', 'c' => '#A855F7', 'view' => 'anon_all', 'vt' => 'Anon → Known Capture Queue', 'vs' => 'High-engagement sessions · awaiting identity capture trigger', 'vf' => ['All', 'High engagement']],
                ],
            ],
            [
                'id' => 'conflicts', 'lc' => '#F43F5E', 'ico' => '⚡',
                'name' => 'Merge Conflicts — ' . $cfCnt . ' Profiles',
                'users' => 'Trust 30–55 · duplicate signals', 'rev' => $cfCnt . ' conflicts pending',
                'urg' => 'critical', 'ul' => 'Resolve conflicts', 'urgDot' => '#F43F5E',
                'banner' => ['text' => $cfCnt . ' profiles have conflicting identity signals. Duplicate records inflate cohort counts and distort scoring. Auto-resolve via deterministic priority — manual review for edge cases.', 'c' => '#F43F5E'],
                'kpis' => [
                    ['v' => (string)$cfCnt, 'l' => 'Merge conflicts', 'p' => min(100, $cfCnt * 8), 'c' => '#F43F5E'],
                    ['v' => (string)(int)round($conflicts->avg('trust_score') ?? 0), 'l' => 'Avg trust score', 'p' => (int)round($conflicts->avg('trust_score') ?? 0), 'c' => '#F59E0B'],
                    ['v' => '89%', 'l' => 'Auto-resolve rate', 'p' => 89, 'c' => '#10B981'],
                    ['v' => (string)round($cfCnt * .11), 'l' => 'Manual review needed', 'p' => 11, 'c' => '#F59E0B'],
                ],
                'dec' => [
                    ['l' => 'Auto-resolve + flag edge cases', 'v' => '89%', 's' => round($cfCnt * .89) . ' resolved · ' . round($cfCnt * .11) . ' flagged for review', 'g' => true],
                    ['l' => 'Manual review all', 'v' => 'Days', 's' => 'Backlog grows · scoring accuracy degrades', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Auto-resolved', 'v' => round($cfCnt * .89) . ' profiles'],
                    ['l' => 'Manual review queue', 'v' => round($cfCnt * .11) . ' profiles'],
                    ['l' => 'Resolution method', 'v' => 'Deterministic priority'],
                    ['l' => 'Time to resolve', 'v' => '< 30 min (auto)'],
                ],
                'ctas' => [
                    ['ico' => '⚡', 'l' => 'Auto-resolve ' . round($cfCnt * .89) . ' conflicts', 'd' => 'Run deterministic priority merge. Flag ' . round($cfCnt * .11) . ' edge cases for manual review.', 'b' => round($cfCnt * .89) . ' resolved', 'c' => '#F43F5E', 'view' => 'conflict_auto', 'vt' => 'Merge Conflict Queue', 'vs' => 'Auto-resolve pass · click user to see conflicting signals', 'vf' => ['All', 'Auto-resolvable', 'Manual review']],
                ],
            ],
            [
                'id' => 'household', 'lc' => '#10B981', 'ico' => '🏠',
                'name' => 'Household Groups — ' . $hhCnt . ' Profiles',
                'users' => 'Loyalty ≥55 · Trust ≥60', 'rev' => '+' . $hhCnt . ' household identities',
                'urg' => 'opportunity', 'ul' => 'Enrich', 'urgDot' => '#10B981',
                'banner' => ['text' => $hhCnt . ' profiles share household-level signals. Grouping enables household personalisation, suppresses duplicate offers, and improves attribution by 12%.', 'c' => '#10B981'],
                'kpis' => [
                    ['v' => (string)$hhCnt, 'l' => 'Household profiles', 'p' => 80, 'c' => '#10B981'],
                    ['v' => (string)(int)round($household->avg('loyalty_score') ?? 0), 'l' => 'Avg loyalty score', 'p' => (int)round($household->avg('loyalty_score') ?? 0), 'c' => '#10B981'],
                    ['v' => (string)(int)round($household->avg('trust_score') ?? 0), 'l' => 'Avg trust score', 'p' => (int)round($household->avg('trust_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => '2.3', 'l' => 'Avg household size', 'p' => 60, 'c' => '#A855F7'],
                ],
                'dec' => [
                    ['l' => 'Group + suppress duplicate outreach', 'v' => '-38%', 's' => 'Duplicate sends eliminated · household CVR +12%', 'g' => true],
                    ['l' => 'Treat as individual profiles', 'v' => 'Current', 's' => 'Duplicate offers · attribution noise', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Household groups created', 'v' => ceil($hhCnt / 2.3) . ' households'],
                    ['l' => 'Duplicate offers suppressed', 'v' => round($hhCnt * .38) . ' sends saved'],
                    ['l' => 'Attribution improvement', 'v' => '+12% accuracy'],
                    ['l' => 'Processing time', 'v' => '< 1 hour'],
                ],
                'ctas' => [
                    ['ico' => '🏠', 'l' => 'Create household groups + suppress duplicates', 'd' => 'Link household profiles, deduplicate outreach for all ' . $hhCnt . ' profiles.', 'b' => round($hhCnt * .38) . ' sends saved', 'c' => '#10B981', 'view' => 'household_all', 'vt' => 'Household Groups — All Profiles', 'vs' => 'Grouped by shared identity signals · see members per household', 'vf' => ['All', 'High loyalty']],
                ],
            ],
        ];

        $lcCards = $lowConf->take(40)->map(fn($p)   => $this->identityCard($p))->values();
        $acCards = $anonConv->take(40)->map(fn($p)  => $this->identityCard($p))->values();
        $cfCards = $conflicts->take(40)->map(fn($p) => $this->identityCard($p))->values();
        $hhCards = $household->take(40)->map(fn($p) => $this->identityCard($p))->values();

        $userGroups = [
            'lowconf_all'   => $lcCards,
            'lowconf_email' => $lcCards->filter(fn($c) => in_array('Very low trust', $c['tags']))->values(),
            'anon_all'      => $acCards,
            'conflict_auto' => $cfCards,
            'household_all' => $hhCards,
        ];

        return view('client.layers.l2', compact('scenarios', 'userGroups'));
    }

    // ── L3 – Data Processing ───────────────────────────────────────────────────
    public function l3()
    {
        $all = BehavioralProfile::orderByDesc('overall_score')->get();

        $stale      = $all->filter(fn($p) => $p->last_active_at && $p->last_active_at->lt(now()->subDays(14)));
        $highDropoff = $all->filter(fn($p) => $p->dropoff_risk >= 65);
        $mlReady    = $all->filter(fn($p) => $p->overall_score >= 70 && $p->engagement_score >= 50);
        $newEntries = $all->filter(fn($p) => $p->segment === 'new');

        $stCnt = $stale->count(); $hdCnt = $highDropoff->count();
        $mlCnt = $mlReady->count(); $neCnt = $newEntries->count();

        $scenarios = [
            [
                'id' => 'stale', 'lc' => '#F59E0B', 'ico' => '⏱️',
                'name' => 'Stale Features — ' . $stCnt . ' Profiles',
                'users' => 'Inactive > 14 days', 'rev' => $stCnt . ' profiles need refresh',
                'urg' => 'warning', 'ul' => 'Reprocess', 'urgDot' => '#F59E0B',
                'banner' => ['text' => $stCnt . ' profiles have features older than 14 days. Stale features degrade model accuracy by up to 22%. Trigger reprocessing to restore scoring quality before next prediction cycle.', 'c' => '#F59E0B'],
                'kpis' => [
                    ['v' => (string)$stCnt, 'l' => 'Stale profiles', 'p' => min(100, $stCnt * 8), 'c' => '#F59E0B'],
                    ['v' => (string)(int)round($stale->avg('dropoff_risk') ?? 0), 'l' => 'Avg dropoff risk', 'p' => (int)round($stale->avg('dropoff_risk') ?? 0), 'c' => '#F43F5E'],
                    ['v' => (string)(int)round($stale->avg('reactivation_potential') ?? 0), 'l' => 'Avg reactivation score', 'p' => (int)round($stale->avg('reactivation_potential') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => '< 2hr', 'l' => 'Reprocess time', 'p' => 80, 'c' => '#10B981'],
                ],
                'dec' => [
                    ['l' => 'Trigger reprocessing now', 'v' => '+22%', 's' => 'Model accuracy restored · scoring cycle uninterrupted', 'g' => true],
                    ['l' => 'Wait for next scheduled cycle', 'v' => '-22%', 's' => 'Stale features persist · downstream accuracy degrades', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Profiles reprocessed', 'v' => (string)$stCnt],
                    ['l' => 'Feature refresh method', 'v' => 'Incremental pipeline run'],
                    ['l' => 'Model accuracy restored', 'v' => '+22%'],
                    ['l' => 'Processing time', 'v' => '< 2 hours'],
                ],
                'ctas' => [
                    ['ico' => '⚡', 'l' => 'Trigger reprocessing for all ' . $stCnt . ' stale profiles', 'd' => 'Run incremental feature refresh for all profiles inactive > 14 days.', 'b' => '+22% model accuracy', 'c' => '#F59E0B', 'view' => 'stale_all', 'vt' => 'Stale Feature Profiles — All ' . $stCnt, 'vs' => 'Ranked by inactivity · click to see feature staleness', 'vf' => ['All', 'Highly stale', 'High dropout risk']],
                    ['ico' => '📅', 'l' => 'Schedule daily reprocess for stale profiles', 'd' => 'Automate daily incremental refresh to prevent staleness accumulation.', 'b' => 'Automated', 'c' => '#0EA5E9', 'view' => 'stale_sched', 'vt' => 'Schedule Reprocessing', 'vs' => 'Daily automation · prevents feature drift', 'vf' => ['All']],
                ],
            ],
            [
                'id' => 'dropoff', 'lc' => '#F43F5E', 'ico' => '📉',
                'name' => 'High Dropout Risk — ' . $hdCnt . ' Profiles',
                'users' => 'Dropoff risk ≥65', 'rev' => '$' . number_format($hdCnt * 2200) . ' pipeline value',
                'urg' => 'critical', 'ul' => 'Intervene now', 'urgDot' => '#F43F5E',
                'banner' => ['text' => $hdCnt . ' profiles scored ≥65 dropoff risk. Pipeline lag causes score decay — act within 24 hours to retain accuracy. Estimated pipeline value at risk: $' . number_format($hdCnt * 2200) . '.', 'c' => '#F43F5E'],
                'kpis' => [
                    ['v' => (string)$hdCnt, 'l' => 'High dropout profiles', 'p' => min(100, $hdCnt * 6), 'c' => '#F43F5E'],
                    ['v' => (string)(int)round($highDropoff->avg('dropoff_risk') ?? 0), 'l' => 'Avg dropoff risk', 'p' => (int)round($highDropoff->avg('dropoff_risk') ?? 0), 'c' => '#F43F5E'],
                    ['v' => (string)(int)round($highDropoff->avg('engagement_score') ?? 0), 'l' => 'Avg engagement', 'p' => (int)round($highDropoff->avg('engagement_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => '$' . number_format($hdCnt * 2200), 'l' => 'Pipeline at risk', 'p' => 75, 'c' => '#F59E0B'],
                ],
                'dec' => [
                    ['l' => 'Re-route through priority pipeline', 'v' => '78%', 's' => round($hdCnt * .78) . ' profiles rescued · value retained', 'g' => true],
                    ['l' => 'Standard queue — accept lag', 'v' => '-31%', 's' => 'Score decay · downstream prediction error', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Profiles rescued', 'v' => round($hdCnt * .78) . ' (78%)'],
                    ['l' => 'Pipeline lag eliminated', 'v' => '< 30 min'],
                    ['l' => 'Value protected', 'v' => '$' . number_format($hdCnt * 2200)],
                    ['l' => 'Method', 'v' => 'Priority queue bypass'],
                ],
                'ctas' => [
                    ['ico' => '🚀', 'l' => 'Route all ' . $hdCnt . ' to priority pipeline', 'd' => 'Bypass standard queue for high-dropout profiles. Restore scoring accuracy within 30 minutes.', 'b' => '$' . number_format($hdCnt * 2200) . ' protected', 'c' => '#F43F5E', 'view' => 'dropoff_all', 'vt' => 'High Dropout Risk — All ' . $hdCnt, 'vs' => 'Ranked by dropoff risk · click to see feature gaps', 'vf' => ['All', 'High dropout risk', 'ML-ready']],
                ],
            ],
            [
                'id' => 'mlready', 'lc' => '#10B981', 'ico' => '🤖',
                'name' => 'ML-Ready Features — ' . $mlCnt . ' Profiles',
                'users' => 'Overall ≥70 · Engage ≥50', 'rev' => $mlCnt . ' candidates for model refresh',
                'urg' => 'opportunity', 'ul' => 'Refresh model', 'urgDot' => '#10B981',
                'banner' => ['text' => $mlCnt . ' profiles have high-quality feature vectors ready for model training. Refreshing with these profiles improves prediction accuracy by 8–14%.', 'c' => '#10B981'],
                'kpis' => [
                    ['v' => (string)$mlCnt, 'l' => 'ML-ready profiles', 'p' => min(100, $mlCnt * 5), 'c' => '#10B981'],
                    ['v' => (string)(int)round($mlReady->avg('overall_score') ?? 0), 'l' => 'Avg overall score', 'p' => (int)round($mlReady->avg('overall_score') ?? 0), 'c' => '#10B981'],
                    ['v' => (string)(int)round($mlReady->avg('engagement_score') ?? 0), 'l' => 'Avg engagement', 'p' => (int)round($mlReady->avg('engagement_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => '+12%', 'l' => 'Expected accuracy gain', 'p' => 65, 'c' => '#10B981'],
                ],
                'dec' => [
                    ['l' => 'Trigger model refresh now', 'v' => '+12%', 's' => 'Improved prediction accuracy across all downstream models', 'g' => true],
                    ['l' => 'Wait for scheduled refresh', 'v' => 'Current', 's' => 'No accuracy gain until next scheduled window', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Training samples added', 'v' => (string)$mlCnt],
                    ['l' => 'Expected accuracy gain', 'v' => '+8–14%'],
                    ['l' => 'Models affected', 'v' => 'Churn · Intent · Readiness'],
                    ['l' => 'Training time', 'v' => '< 4 hours'],
                ],
                'ctas' => [
                    ['ico' => '🤖', 'l' => 'Trigger model refresh with ' . $mlCnt . ' new samples', 'd' => 'Add all high-quality feature vectors to training pipeline. Expected +12% accuracy.', 'b' => '+12% model accuracy', 'c' => '#10B981', 'view' => 'ml_all', 'vt' => 'ML-Ready Profiles — All ' . $mlCnt, 'vs' => 'High-quality feature vectors · ready for training', 'vf' => ['All', 'High overall score', 'High engagement']],
                ],
            ],
            [
                'id' => 'newentry', 'lc' => '#A855F7', 'ico' => '✨',
                'name' => 'New Profile Entries — ' . $neCnt . ' Users',
                'users' => 'Segment: New', 'rev' => $neCnt . ' profiles to onboard',
                'urg' => 'opportunity', 'ul' => 'Onboard', 'urgDot' => '#10B981',
                'banner' => ['text' => $neCnt . ' new profiles entered the system. Fast feature initialisation within 2 hours improves first-event prediction by 34% vs cold start. Onboard now to accelerate value.', 'c' => '#A855F7'],
                'kpis' => [
                    ['v' => (string)$neCnt, 'l' => 'New profiles', 'p' => min(100, $neCnt * 12), 'c' => '#A855F7'],
                    ['v' => (string)(int)round($newEntries->avg('engagement_score') ?? 0), 'l' => 'Avg engagement', 'p' => (int)round($newEntries->avg('engagement_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => '+34%', 'l' => 'First-event prediction gain', 'p' => 68, 'c' => '#10B981'],
                    ['v' => '< 2hr', 'l' => 'Onboarding time', 'p' => 90, 'c' => '#10B981'],
                ],
                'dec' => [
                    ['l' => 'Fast-track feature initialisation', 'v' => '+34%', 's' => 'First-event prediction improved · personalisation enabled faster', 'g' => true],
                    ['l' => 'Cold start — standard queue', 'v' => 'Current', 's' => 'Delayed personalisation · prediction quality low', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Profiles initialised', 'v' => (string)$neCnt],
                    ['l' => 'First-event prediction', 'v' => '+34% accuracy'],
                    ['l' => 'Personalisation enabled', 'v' => 'Within 2 hours'],
                    ['l' => 'Next scoring cycle', 'v' => 'Included'],
                ],
                'ctas' => [
                    ['ico' => '✨', 'l' => 'Fast-track onboarding for all ' . $neCnt . ' new profiles', 'd' => 'Initialise feature vectors immediately. All profiles included in next scoring cycle.', 'b' => '+34% prediction accuracy', 'c' => '#A855F7', 'view' => 'new_all', 'vt' => 'New Profile Onboarding Queue', 'vs' => 'Fast-track initialisation · all included in next cycle', 'vf' => ['All']],
                ],
            ],
        ];

        $stCards = $stale->take(40)->map(fn($p)       => $this->processingCard($p))->values();
        $hdCards = $highDropoff->take(40)->map(fn($p) => $this->processingCard($p))->values();
        $mlCards = $mlReady->take(40)->map(fn($p)     => $this->processingCard($p))->values();
        $neCards = $newEntries->take(40)->map(fn($p)  => $this->processingCard($p))->values();

        $userGroups = [
            'stale_all'   => $stCards,
            'stale_sched' => $stCards,
            'dropoff_all' => $hdCards,
            'ml_all'      => $mlCards,
            'new_all'     => $neCards,
        ];

        return view('client.layers.l3', compact('scenarios', 'userGroups'));
    }

    // ── L6 – Decision & Actions ────────────────────────────────────────────────
    public function l6()
    {
        $all = BehavioralProfile::orderByDesc('overall_score')->get();

        $sigConflicts = $all->filter(fn($p) => $p->churn_score >= 50 && $p->intent_score >= 60);
        $freqCap      = $all->filter(fn($p) => $p->frustration_score >= 60);
        $budgetCeil   = $all->filter(fn($p) => $p->engagement_score >= 75 && $p->intent_score >= 65);
        $learning     = $all->filter(fn($p) => $p->segment === 'new' || ($p->overall_score >= 50 && $p->overall_score < 65));

        $coCnt = $sigConflicts->count(); $fcCnt = $freqCap->count();
        $bcCnt = $budgetCeil->count();   $llCnt = $learning->count();

        $scenarios = [
            [
                'id' => 'sigconflict', 'lc' => '#F43F5E', 'ico' => '⚡',
                'name' => 'Signal Conflicts — ' . $coCnt . ' Users',
                'users' => 'Churn ≥50 · Intent ≥60', 'rev' => '$' . number_format($coCnt * 3100) . ' revenue affected',
                'urg' => 'critical', 'ul' => 'Resolve now', 'urgDot' => '#F43F5E',
                'banner' => ['text' => $coCnt . ' users show conflicting signals — high intent alongside high churn risk. Sending without resolving risks the wrong message to the wrong user. AI recommends conflict-resolution override.', 'c' => '#F43F5E'],
                'kpis' => [
                    ['v' => (string)$coCnt, 'l' => 'Signal conflicts', 'p' => min(100, $coCnt * 8), 'c' => '#F43F5E'],
                    ['v' => (string)(int)round($sigConflicts->avg('intent_score') ?? 0), 'l' => 'Avg intent score', 'p' => (int)round($sigConflicts->avg('intent_score') ?? 0), 'c' => '#10B981'],
                    ['v' => (string)(int)round($sigConflicts->avg('churn_score') ?? 0), 'l' => 'Avg churn score', 'p' => (int)round($sigConflicts->avg('churn_score') ?? 0), 'c' => '#F43F5E'],
                    ['v' => '$' . number_format($coCnt * 3100), 'l' => 'Revenue affected', 'p' => 70, 'c' => '#F59E0B'],
                ],
                'dec' => [
                    ['l' => 'Resolve via AI priority rule', 'v' => '+28%', 's' => 'Correct action taken · revenue protected · churn reduced', 'g' => true],
                    ['l' => 'Send using highest-scoring signal', 'v' => '-15%', 's' => 'Wrong action for ~30% of conflicts · revenue and retention impact', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Conflicts resolved', 'v' => (string)$coCnt],
                    ['l' => 'Priority rule applied', 'v' => 'Churn > Intent (protect first)'],
                    ['l' => 'Revenue protected', 'v' => '$' . number_format($coCnt * 3100)],
                    ['l' => 'Resolution time', 'v' => '< 1 hour'],
                ],
                'ctas' => [
                    ['ico' => '⚡', 'l' => 'Resolve all ' . $coCnt . ' conflicts via AI priority', 'd' => 'Apply priority rule: churn intervention takes precedence over intent activation. All conflicts resolved in < 1 hour.', 'b' => '$' . number_format($coCnt * 3100) . ' protected', 'c' => '#F43F5E', 'view' => 'conflict_all', 'vt' => 'Signal Conflicts — All ' . $coCnt, 'vs' => 'Ranked by conflict severity · click to see conflicting signals', 'vf' => ['All', 'Signal conflict', 'Enterprise']],
                    ['ico' => '🔧', 'l' => 'Override rule for specific users', 'd' => 'Manually override AI priority for users where context suggests a different action.', 'b' => 'Manual review', 'c' => '#A855F7', 'view' => 'conflict_manual', 'vt' => 'Manual Override Queue', 'vs' => 'Override available · choose action per user', 'vf' => ['All']],
                ],
            ],
            [
                'id' => 'freqcap', 'lc' => '#F59E0B', 'ico' => '🔕',
                'name' => 'Frequency Cap Suppressions — ' . $fcCnt . ' Users',
                'users' => 'Frustration ≥60', 'rev' => $fcCnt . ' users protected from burnout',
                'urg' => 'warning', 'ul' => 'Suppress now', 'urgDot' => '#F59E0B',
                'banner' => ['text' => $fcCnt . ' users have frustration scores ≥60. Sending additional messages risks accelerating churn. AI recommends frequency cap — no outreach for 7 days.', 'c' => '#F59E0B'],
                'kpis' => [
                    ['v' => (string)$fcCnt, 'l' => 'Cap suppressions', 'p' => min(100, $fcCnt * 6), 'c' => '#F59E0B'],
                    ['v' => (string)(int)round($freqCap->avg('frustration_score') ?? 0), 'l' => 'Avg frustration score', 'p' => (int)round($freqCap->avg('frustration_score') ?? 0), 'c' => '#F43F5E'],
                    ['v' => '-31%', 'l' => 'Churn risk reduction', 'p' => 69, 'c' => '#10B981'],
                    ['v' => '7 days', 'l' => 'Suppression window', 'p' => 50, 'c' => '#0EA5E9'],
                ],
                'dec' => [
                    ['l' => 'Apply 7-day frequency cap', 'v' => '-31%', 's' => 'Churn risk reduced · ' . round($fcCnt * .31) . ' users retained', 'g' => true],
                    ['l' => 'Continue standard outreach', 'v' => '+14%', 's' => 'Frustration increases · accelerated churn risk', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Users suppressed', 'v' => (string)$fcCnt],
                    ['l' => 'Suppression window', 'v' => '7 days'],
                    ['l' => 'Expected churn reduction', 'v' => round($fcCnt * .31) . ' users retained'],
                    ['l' => 'Re-engagement trigger', 'v' => 'Frustration score < 40'],
                ],
                'ctas' => [
                    ['ico' => '🔕', 'l' => 'Apply 7-day frequency cap to all ' . $fcCnt, 'd' => 'Suppress all outreach for 7 days. Re-engage when frustration score drops below 40.', 'b' => round($fcCnt * .31) . ' users retained', 'c' => '#F59E0B', 'view' => 'freqcap_all', 'vt' => 'Frequency Cap Suppressions', 'vs' => 'High-frustration users · suppressed from all outreach', 'vf' => ['All', 'Frequency cap', 'High frustration']],
                ],
            ],
            [
                'id' => 'budget', 'lc' => '#0EA5E9', 'ico' => '💰',
                'name' => 'Budget Ceiling Alerts — ' . $bcCnt . ' Users',
                'users' => 'Engage ≥75 · Intent ≥65', 'rev' => '$' . number_format($bcCnt * 890) . ' at ceiling',
                'urg' => 'warning', 'ul' => 'Review allocation', 'urgDot' => '#0EA5E9',
                'banner' => ['text' => $bcCnt . ' high-value users are consuming more marketing budget than allocated. AI recommends reallocating to highest-intent users to maximise ROI within the cap.', 'c' => '#0EA5E9'],
                'kpis' => [
                    ['v' => (string)$bcCnt, 'l' => 'Budget ceiling users', 'p' => min(100, $bcCnt * 8), 'c' => '#0EA5E9'],
                    ['v' => (string)(int)round($budgetCeil->avg('engagement_score') ?? 0), 'l' => 'Avg engagement', 'p' => (int)round($budgetCeil->avg('engagement_score') ?? 0), 'c' => '#10B981'],
                    ['v' => (string)(int)round($budgetCeil->avg('intent_score') ?? 0), 'l' => 'Avg intent score', 'p' => (int)round($budgetCeil->avg('intent_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => '+22%', 'l' => 'ROI after reallocation', 'p' => 80, 'c' => '#10B981'],
                ],
                'dec' => [
                    ['l' => 'Reallocate to highest-intent users', 'v' => '+22%', 's' => 'Budget efficiency +22% · ROI maximised within cap', 'g' => true],
                    ['l' => 'Pause all high-engagement outreach', 'v' => '-18%', 's' => 'Revenue opportunity lost · competitors fill the gap', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Users reallocated', 'v' => round($bcCnt * .6) . ' (highest intent)'],
                    ['l' => 'Paused', 'v' => round($bcCnt * .4) . ' (lower priority)'],
                    ['l' => 'Budget efficiency', 'v' => '+22% ROI'],
                    ['l' => 'Reallocation method', 'v' => 'Intent × Readiness ranking'],
                ],
                'ctas' => [
                    ['ico' => '💰', 'l' => 'Reallocate budget to top ' . round($bcCnt * .6) . ' intent users', 'd' => 'Pause lowest-priority contacts. Concentrate budget on highest intent × readiness users.', 'b' => '+22% ROI', 'c' => '#0EA5E9', 'view' => 'budget_all', 'vt' => 'Budget Ceiling — Reallocation Queue', 'vs' => 'Sorted by intent × readiness · reallocate budget to top performers', 'vf' => ['All', 'Budget ceiling', 'Highest intent']],
                ],
            ],
            [
                'id' => 'learning', 'lc' => '#10B981', 'ico' => '🔄',
                'name' => 'Learning Loop — ' . $llCnt . ' Profiles',
                'users' => 'New + Mid-range scores', 'rev' => $llCnt . ' profiles improving model',
                'urg' => 'opportunity', 'ul' => 'Feed model', 'urgDot' => '#10B981',
                'banner' => ['text' => $llCnt . ' new and mid-range profiles provide high-value signal diversity for the learning loop. Feeding these into the model improves emerging segment prediction accuracy by 16%.', 'c' => '#10B981'],
                'kpis' => [
                    ['v' => (string)$llCnt, 'l' => 'Learning loop profiles', 'p' => min(100, $llCnt * 4), 'c' => '#10B981'],
                    ['v' => (string)(int)round($learning->avg('overall_score') ?? 0), 'l' => 'Avg overall score', 'p' => (int)round($learning->avg('overall_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => '+16%', 'l' => 'Emerging segment accuracy', 'p' => 65, 'c' => '#10B981'],
                    ['v' => '< 6hr', 'l' => 'Learning cycle time', 'p' => 80, 'c' => '#0EA5E9'],
                ],
                'dec' => [
                    ['l' => 'Feed into learning loop now', 'v' => '+16%', 's' => 'Emerging segment accuracy improved · model generalises better', 'g' => true],
                    ['l' => 'Wait for scheduled cycle', 'v' => 'Current', 's' => 'Signal value decays · accuracy improvement delayed', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Profiles fed to model', 'v' => (string)$llCnt],
                    ['l' => 'Emerging segment accuracy', 'v' => '+16%'],
                    ['l' => 'Learning cycle', 'v' => '< 6 hours'],
                    ['l' => 'Models updated', 'v' => 'All downstream models'],
                ],
                'ctas' => [
                    ['ico' => '🔄', 'l' => 'Feed ' . $llCnt . ' profiles to learning loop now', 'd' => 'Submit all new and mid-range profiles to the learning loop. Improves emerging segment predictions.', 'b' => '+16% accuracy', 'c' => '#10B981', 'view' => 'learning_all', 'vt' => 'Learning Loop — All ' . $llCnt . ' Profiles', 'vs' => 'New and mid-range profiles · improving emerging segment model', 'vf' => ['All']],
                ],
            ],
        ];

        $coCards = $sigConflicts->take(40)->map(fn($p) => $this->decisionCard($p))->values();
        $fcCards = $freqCap->take(40)->map(fn($p)      => $this->decisionCard($p))->values();
        $bcCards = $budgetCeil->take(40)->map(fn($p)   => $this->decisionCard($p))->values();
        $llCards = $learning->take(40)->map(fn($p)     => $this->decisionCard($p))->values();

        $userGroups = [
            'conflict_all'    => $coCards,
            'conflict_manual' => $coCards->filter(fn($c) => in_array('Signal conflict', $c['tags']))->values(),
            'freqcap_all'     => $fcCards,
            'budget_all'      => $bcCards,
            'learning_all'    => $llCards,
        ];

        return view('client.layers.l6', compact('scenarios', 'userGroups'));
    }

    // ── L7 – Application Layer ─────────────────────────────────────────────────
    public function l7()
    {
        $all = BehavioralProfile::orderByDesc('overall_score')->get();

        $execAlerts = $all->filter(fn($p) => $p->overall_score >= 75 && $p->churn_score >= 65);
        $oneClick   = $all->filter(fn($p) => $p->buying_readiness >= 80 && $p->intent_score >= 75);
        $csmQueue   = $all->filter(fn($p) => $p->churn_score >= 65 && in_array($p->segment, ['champion', 'loyal']));
        $explainAi  = $all->filter(fn($p) => abs($p->intent_score - $p->churn_score) < 15 && $p->overall_score >= 50);

        $eaCnt = $execAlerts->count(); $ocCnt = $oneClick->count();
        $cqCnt = $csmQueue->count();   $exCnt = $explainAi->count();

        $scenarios = [
            [
                'id' => 'exec', 'lc' => '#F43F5E', 'ico' => '🚨',
                'name' => 'Executive Alerts — ' . $eaCnt . ' Accounts',
                'users' => 'Overall ≥75 · Churn ≥65', 'rev' => '$' . number_format($eaCnt * 18000) . ' ARR at risk',
                'urg' => 'critical', 'ul' => 'Immediate', 'urgDot' => '#F43F5E',
                'banner' => ['text' => $eaCnt . ' high-value accounts crossed both the churn threshold and overall score threshold. These require executive-level escalation. Total ARR at risk: $' . number_format($eaCnt * 18000) . '.', 'c' => '#F43F5E'],
                'kpis' => [
                    ['v' => (string)$eaCnt, 'l' => 'Executive alerts', 'p' => min(100, $eaCnt * 15), 'c' => '#F43F5E'],
                    ['v' => (string)(int)round($execAlerts->avg('churn_score') ?? 0), 'l' => 'Avg churn score', 'p' => (int)round($execAlerts->avg('churn_score') ?? 0), 'c' => '#F43F5E'],
                    ['v' => (string)(int)round($execAlerts->avg('overall_score') ?? 0), 'l' => 'Avg overall score', 'p' => (int)round($execAlerts->avg('overall_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => '$' . number_format($eaCnt * 18000), 'l' => 'ARR at risk', 'p' => 80, 'c' => '#F59E0B'],
                ],
                'dec' => [
                    ['l' => 'Escalate to executive team now', 'v' => '58%', 's' => 'Executive contact within 2hr · ' . round($eaCnt * .58) . ' accounts retained', 'g' => true],
                    ['l' => 'Standard CSM process', 'v' => '28%', 's' => 'Delayed response · revenue loss accelerates', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Accounts escalated', 'v' => (string)$eaCnt],
                    ['l' => 'Response SLA', 'v' => '< 2 hours'],
                    ['l' => 'Expected retention', 'v' => round($eaCnt * .58) . ' (58%)'],
                    ['l' => 'ARR protected', 'v' => '$' . number_format(round($eaCnt * .58) * 18000)],
                ],
                'ctas' => [
                    ['ico' => '🚨', 'l' => 'Escalate all ' . $eaCnt . ' accounts to executive team', 'd' => 'Alert VP CS and CEO dashboard. Trigger emergency retention protocol for all flagged accounts.', 'b' => '$' . number_format(round($eaCnt * .58) * 18000) . ' ARR saved', 'c' => '#F43F5E', 'view' => 'exec_all', 'vt' => 'Executive Alerts — All ' . $eaCnt . ' Accounts', 'vs' => 'High-value + high-churn · requires immediate executive contact', 'vf' => ['All', 'Executive alert', 'Enterprise']],
                    ['ico' => '📞', 'l' => 'Route to dedicated CSM + call now', 'd' => 'Assign dedicated CSM to each account. Initiate call within 30 minutes.', 'b' => round($eaCnt * .58) . ' accounts', 'c' => '#A855F7', 'view' => 'exec_csm', 'vt' => 'CSM Escalation Queue', 'vs' => 'Dedicated CSM assigned · calls in progress', 'vf' => ['All']],
                ],
            ],
            [
                'id' => 'oneclick', 'lc' => '#10B981', 'ico' => '🚀',
                'name' => '1-Click Queue — ' . $ocCnt . ' Users',
                'users' => 'Readiness ≥80 · Intent ≥75', 'rev' => '$' . number_format($ocCnt * 890) . ' conversion ready',
                'urg' => 'opportunity', 'ul' => '48hr window', 'urgDot' => '#10B981',
                'banner' => ['text' => $ocCnt . ' users are in peak buying state — readiness ≥80 and intent ≥75. 1-click offer dispatch converts at 41% vs 12% for standard campaign. 48-hour window applies.', 'c' => '#10B981'],
                'kpis' => [
                    ['v' => (string)$ocCnt, 'l' => '1-Click ready users', 'p' => min(100, $ocCnt * 12), 'c' => '#10B981'],
                    ['v' => (string)(int)round($oneClick->avg('buying_readiness') ?? 0), 'l' => 'Avg buying readiness', 'p' => (int)round($oneClick->avg('buying_readiness') ?? 0), 'c' => '#10B981'],
                    ['v' => (string)(int)round($oneClick->avg('intent_score') ?? 0), 'l' => 'Avg intent score', 'p' => (int)round($oneClick->avg('intent_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => '41%', 'l' => 'Conversion rate', 'p' => 82, 'c' => '#10B981'],
                ],
                'dec' => [
                    ['l' => '1-Click dispatch (48hr window)', 'v' => '41%', 's' => round($ocCnt * .41) . ' conversions · $' . number_format(round($ocCnt * .41) * 890) . ' revenue', 'g' => true],
                    ['l' => 'Standard campaign', 'v' => '12%', 's' => round($ocCnt * .12) . ' conversions · revenue opportunity missed', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Users in 1-click queue', 'v' => (string)$ocCnt],
                    ['l' => 'Expected conversions', 'v' => round($ocCnt * .41) . ' (41%)'],
                    ['l' => 'Revenue', 'v' => '$' . number_format(round($ocCnt * .41) * 890)],
                    ['l' => 'Dispatch window', 'v' => '48 hours'],
                ],
                'ctas' => [
                    ['ico' => '🚀', 'l' => 'Dispatch 1-click offers to all ' . $ocCnt, 'd' => 'Pre-filled offer pages sent to each user. Personalised to plan evaluating. No friction checkout.', 'b' => '$' . number_format(round($ocCnt * .41) * 890) . ' revenue', 'c' => '#10B981', 'view' => 'oneclick_all', 'vt' => '1-Click Queue — All ' . $ocCnt . ' Users', 'vs' => 'Peak buying state · pre-filled offers ready to send', 'vf' => ['All', '1-Click ready', 'Highest intent']],
                ],
            ],
            [
                'id' => 'csmqueue', 'lc' => '#A855F7', 'ico' => '📞',
                'name' => 'CSM Queue — ' . $cqCnt . ' Accounts',
                'users' => 'Churn ≥65 · Champion/Loyal', 'rev' => '$' . number_format($cqCnt * 8400) . ' ARR at risk',
                'urg' => 'critical', 'ul' => '72hr window', 'urgDot' => '#F43F5E',
                'banner' => ['text' => $cqCnt . ' loyal and champion-tier accounts crossed the churn threshold. CSM contact within 72 hours retains 41%. Delay beyond 72 hours: retention drops below 20%.', 'c' => '#A855F7'],
                'kpis' => [
                    ['v' => (string)$cqCnt, 'l' => 'CSM queue accounts', 'p' => min(100, $cqCnt * 8), 'c' => '#A855F7'],
                    ['v' => (string)(int)round($csmQueue->avg('churn_score') ?? 0), 'l' => 'Avg churn score', 'p' => (int)round($csmQueue->avg('churn_score') ?? 0), 'c' => '#F43F5E'],
                    ['v' => (string)(int)round($csmQueue->avg('loyalty_score') ?? 0), 'l' => 'Avg loyalty score', 'p' => (int)round($csmQueue->avg('loyalty_score') ?? 0), 'c' => '#10B981'],
                    ['v' => '$' . number_format($cqCnt * 8400), 'l' => 'ARR at risk', 'p' => 75, 'c' => '#F59E0B'],
                ],
                'dec' => [
                    ['l' => 'CSM contact within 72hrs', 'v' => '41%', 's' => round($cqCnt * .41) . ' retained · $' . number_format(round($cqCnt * .41) * 8400) . ' ARR protected', 'g' => true],
                    ['l' => 'After 72hrs', 'v' => '<20%', 's' => 'Window closed · recovery rate drops dramatically', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Accounts assigned to CSM', 'v' => (string)$cqCnt],
                    ['l' => 'Expected retentions', 'v' => round($cqCnt * .41) . ' (41%)'],
                    ['l' => 'ARR protected', 'v' => '$' . number_format(round($cqCnt * .41) * 8400)],
                    ['l' => 'Action window', 'v' => '72 hours'],
                ],
                'ctas' => [
                    ['ico' => '📞', 'l' => 'Assign all ' . $cqCnt . ' to CSM + call now', 'd' => 'Auto-assign to available CSM. Personalised call script ready. Retention offer approved.', 'b' => '$' . number_format(round($cqCnt * .41) * 8400) . ' protected', 'c' => '#A855F7', 'view' => 'csm_all', 'vt' => 'CSM Queue — All ' . $cqCnt . ' Accounts', 'vs' => 'Ranked by churn × LTV · personalised call script ready', 'vf' => ['All', 'CSM queue', 'Enterprise']],
                ],
            ],
            [
                'id' => 'explainai', 'lc' => '#0EA5E9', 'ico' => '🧠',
                'name' => 'Explain AI — ' . $exCnt . ' Profiles',
                'users' => 'Ambiguous score pattern', 'rev' => $exCnt . ' decisions to explain',
                'urg' => 'warning', 'ul' => 'Explain', 'urgDot' => '#0EA5E9',
                'banner' => ['text' => $exCnt . ' profiles have ambiguous score patterns. Explain AI provides human-readable explanations of why the model scored each profile, enabling CSM review and override.', 'c' => '#0EA5E9'],
                'kpis' => [
                    ['v' => (string)$exCnt, 'l' => 'Profiles to explain', 'p' => min(100, $exCnt * 5), 'c' => '#0EA5E9'],
                    ['v' => (string)(int)round($explainAi->avg('overall_score') ?? 0), 'l' => 'Avg overall score', 'p' => (int)round($explainAi->avg('overall_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => '< 5s', 'l' => 'Explanation time', 'p' => 90, 'c' => '#10B981'],
                    ['v' => '92%', 'l' => 'CSM acceptance rate', 'p' => 92, 'c' => '#10B981'],
                ],
                'dec' => [
                    ['l' => 'Generate explanations + allow override', 'v' => '92%', 's' => 'Right action taken · model trust increases', 'g' => true],
                    ['l' => 'Black-box — no explanation', 'v' => '-22%', 's' => 'Wrong actions on ambiguous profiles · CSM trust erodes', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Profiles explained', 'v' => (string)$exCnt],
                    ['l' => 'CSM acceptance rate', 'v' => '92%'],
                    ['l' => 'Override rate', 'v' => '8% (human judgment)'],
                    ['l' => 'Explanation time', 'v' => '< 5s per profile'],
                ],
                'ctas' => [
                    ['ico' => '🧠', 'l' => 'Generate explanations for all ' . $exCnt . ' profiles', 'd' => 'AI explains each scoring decision in plain language. CSM can accept or override.', 'b' => '92% acceptance rate', 'c' => '#0EA5E9', 'view' => 'explain_all', 'vt' => 'Explain AI — All ' . $exCnt . ' Profiles', 'vs' => 'Ambiguous patterns · explanation + override available', 'vf' => ['All']],
                ],
            ],
        ];

        $eaCards = $execAlerts->take(40)->map(fn($p) => $this->applicationCard($p))->values();
        $ocCards = $oneClick->take(40)->map(fn($p)   => $this->applicationCard($p))->values();
        $cqCards = $csmQueue->take(40)->map(fn($p)   => $this->applicationCard($p))->values();
        $exCards = $explainAi->take(40)->map(fn($p)  => $this->applicationCard($p))->values();

        $userGroups = [
            'exec_all'     => $eaCards,
            'exec_csm'     => $eaCards,
            'oneclick_all' => $ocCards,
            'csm_all'      => $cqCards,
            'explain_all'  => $exCards,
        ];

        return view('client.layers.l7', compact('scenarios', 'userGroups'));
    }

    // ── L8 – Governance & Security ─────────────────────────────────────────────
    public function l8()
    {
        $all = BehavioralProfile::orderByDesc('overall_score')->get();

        $gdprRisk    = $all->filter(fn($p) => $p->last_active_at && $p->last_active_at->lt(now()->subDays(30)));
        $accessViol  = $all->filter(fn($p) => $p->trust_score < 25);
        $auditTrail  = $all->filter(fn($p) => $p->overall_score >= 60 && $p->last_active_at && $p->last_active_at->gt(now()->subDays(30)));
        $retentionBr = $all->filter(fn($p) => $p->last_active_at && $p->last_active_at->lt(now()->subDays(60)));

        $grCnt = $gdprRisk->count();    $avCnt = $accessViol->count();
        $atCnt = $auditTrail->count();  $rbCnt = $retentionBr->count();

        $scenarios = [
            [
                'id' => 'gdpr', 'lc' => '#F43F5E', 'ico' => '⚖️',
                'name' => 'GDPR Issues — ' . $grCnt . ' Profiles',
                'users' => 'Inactive > 30 days', 'rev' => $grCnt . ' compliance risks',
                'urg' => 'critical', 'ul' => 'Compliance', 'urgDot' => '#F43F5E',
                'banner' => ['text' => $grCnt . ' profiles have not been active in 30+ days and may require GDPR review. Unreviewed profiles risk non-compliance with data minimisation and right-to-erasure obligations.', 'c' => '#F43F5E'],
                'kpis' => [
                    ['v' => (string)$grCnt, 'l' => 'GDPR risk profiles', 'p' => min(100, $grCnt * 5), 'c' => '#F43F5E'],
                    ['v' => (string)(int)round($gdprRisk->avg('trust_score') ?? 0), 'l' => 'Avg trust score', 'p' => (int)round($gdprRisk->avg('trust_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => (string)$gdprRisk->filter(fn($p) => $p->last_active_at?->lt(now()->subDays(60)))->count(), 'l' => '> 60 days inactive', 'p' => 50, 'c' => '#F59E0B'],
                    ['v' => '72hr', 'l' => 'Compliance SLA', 'p' => 60, 'c' => '#F43F5E'],
                ],
                'dec' => [
                    ['l' => 'Review + apply data minimisation', 'v' => 'Compliant', 's' => 'GDPR obligations met · risk eliminated', 'g' => true],
                    ['l' => 'No action — defer review', 'v' => 'Risk', 's' => 'Non-compliance risk · potential regulatory action', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Profiles reviewed', 'v' => (string)$grCnt],
                    ['l' => 'Data minimisation applied', 'v' => 'Automatic where eligible'],
                    ['l' => 'Right-to-erasure', 'v' => 'Flagged for manual review'],
                    ['l' => 'Compliance SLA', 'v' => '72 hours'],
                ],
                'ctas' => [
                    ['ico' => '⚖️', 'l' => 'Run GDPR compliance review for all ' . $grCnt, 'd' => 'Auto-apply data minimisation where eligible. Flag right-to-erasure candidates for manual review.', 'b' => 'Compliance achieved', 'c' => '#F43F5E', 'view' => 'gdpr_all', 'vt' => 'GDPR Review Queue — All ' . $grCnt, 'vs' => 'Inactive profiles · ranked by risk level', 'vf' => ['All', 'GDPR risk', 'Retention breach']],
                    ['ico' => '🗑️', 'l' => 'Erasure queue — confirmed opt-outs', 'd' => 'Process confirmed right-to-erasure requests. Reviewed before execution.', 'b' => 'Legal obligation', 'c' => '#F59E0B', 'view' => 'gdpr_erase', 'vt' => 'Erasure Queue', 'vs' => 'Confirmed opt-outs · awaiting deletion', 'vf' => ['All']],
                ],
            ],
            [
                'id' => 'access', 'lc' => '#F59E0B', 'ico' => '🔐',
                'name' => 'Access Violations — ' . $avCnt . ' Profiles',
                'users' => 'Trust score < 25', 'rev' => $avCnt . ' security risks',
                'urg' => 'critical', 'ul' => 'Revoke access', 'urgDot' => '#F43F5E',
                'banner' => ['text' => $avCnt . ' profiles have trust scores below 25 — indicating potential unauthorised access or compromised credentials. Immediate review and access suspension recommended.', 'c' => '#F59E0B'],
                'kpis' => [
                    ['v' => (string)$avCnt, 'l' => 'Access violations', 'p' => min(100, $avCnt * 15), 'c' => '#F59E0B'],
                    ['v' => (string)(int)round($accessViol->avg('trust_score') ?? 0), 'l' => 'Avg trust score', 'p' => (int)round($accessViol->avg('trust_score') ?? 0), 'c' => '#F43F5E'],
                    ['v' => (string)(int)round($accessViol->avg('engagement_score') ?? 0), 'l' => 'Avg engagement', 'p' => (int)round($accessViol->avg('engagement_score') ?? 0), 'c' => '#0EA5E9'],
                    ['v' => 'Immediate', 'l' => 'Response SLA', 'p' => 100, 'c' => '#F43F5E'],
                ],
                'dec' => [
                    ['l' => 'Suspend access + review', 'v' => 'Secure', 's' => 'Risk eliminated · accounts reviewed within 24hrs', 'g' => true],
                    ['l' => 'Monitor only — no suspension', 'v' => 'Risk', 's' => 'Exposure continues · potential data breach', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Access suspended', 'v' => (string)$avCnt . ' profiles'],
                    ['l' => 'Review SLA', 'v' => '24 hours'],
                    ['l' => 'Re-verification required', 'v' => 'Multi-factor + identity check'],
                    ['l' => 'Security team notified', 'v' => 'Automated alert sent'],
                ],
                'ctas' => [
                    ['ico' => '🔐', 'l' => 'Suspend access for all ' . $avCnt . ' profiles', 'd' => 'Immediately suspend session access for all low-trust profiles. Notify security team. Require re-verification.', 'b' => 'Security achieved', 'c' => '#F59E0B', 'view' => 'access_all', 'vt' => 'Access Violations — All ' . $avCnt, 'vs' => 'Low-trust profiles · access suspended · review required', 'vf' => ['All', 'Access violation', 'Very low trust']],
                ],
            ],
            [
                'id' => 'audit', 'lc' => '#10B981', 'ico' => '📋',
                'name' => 'AI Audit Trail — ' . $atCnt . ' Decisions',
                'users' => 'Active last 30 days · Score ≥60', 'rev' => $atCnt . ' decisions auditable',
                'urg' => 'opportunity', 'ul' => 'Audit', 'urgDot' => '#10B981',
                'banner' => ['text' => $atCnt . ' recent high-score profiles have AI-driven decisions in the last 30 days. Full audit trail available for compliance review, model governance, and regulatory reporting.', 'c' => '#10B981'],
                'kpis' => [
                    ['v' => (string)$atCnt, 'l' => 'Recent AI decisions', 'p' => min(100, $atCnt * 5), 'c' => '#10B981'],
                    ['v' => (string)(int)round($auditTrail->avg('overall_score') ?? 0), 'l' => 'Avg overall score', 'p' => (int)round($auditTrail->avg('overall_score') ?? 0), 'c' => '#10B981'],
                    ['v' => '100%', 'l' => 'Audit trail completeness', 'p' => 100, 'c' => '#10B981'],
                    ['v' => 'GDPR Art.22', 'l' => 'Regulation met', 'p' => 100, 'c' => '#0EA5E9'],
                ],
                'dec' => [
                    ['l' => 'Export full audit trail + report', 'v' => 'Compliant', 's' => 'All decisions documented · regulatory ready', 'g' => true],
                    ['l' => 'No audit — model only', 'v' => 'Risk', 's' => 'GDPR Art.22 non-compliance · unexplainable AI', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Decisions audited', 'v' => (string)$atCnt],
                    ['l' => 'Audit trail format', 'v' => 'JSON + PDF report'],
                    ['l' => 'Regulation covered', 'v' => 'GDPR Art.22 + ISO 27001'],
                    ['l' => 'Export time', 'v' => '< 5 minutes'],
                ],
                'ctas' => [
                    ['ico' => '📋', 'l' => 'Export AI audit trail for all ' . $atCnt . ' decisions', 'd' => 'Generate full audit report with decision rationale, model version, and data sources for each profile.', 'b' => 'Compliance report', 'c' => '#10B981', 'view' => 'audit_all', 'vt' => 'AI Audit Trail — Recent Decisions', 'vs' => 'All AI decisions documented · export ready', 'vf' => ['All', 'High score', 'Recent activity']],
                ],
            ],
            [
                'id' => 'retention', 'lc' => '#A855F7', 'ico' => '⏳',
                'name' => 'Data Retention Breaches — ' . $rbCnt . ' Profiles',
                'users' => 'Inactive > 60 days', 'rev' => $rbCnt . ' retention breaches',
                'urg' => 'critical', 'ul' => 'Review policy', 'urgDot' => '#F43F5E',
                'banner' => ['text' => $rbCnt . ' profiles exceed the 60-day data retention policy threshold. Retaining data beyond policy creates compliance liability. Auto-anonymise or delete as policy dictates.', 'c' => '#A855F7'],
                'kpis' => [
                    ['v' => (string)$rbCnt, 'l' => 'Retention breaches', 'p' => min(100, $rbCnt * 5), 'c' => '#A855F7'],
                    ['v' => (string)$retentionBr->filter(fn($p) => $p->last_active_at?->lt(now()->subDays(90)))->count(), 'l' => '> 90 days inactive', 'p' => 50, 'c' => '#F43F5E'],
                    ['v' => 'Auto', 'l' => 'Anonymisation available', 'p' => 100, 'c' => '#10B981'],
                    ['v' => '72hr', 'l' => 'Resolution SLA', 'p' => 60, 'c' => '#F59E0B'],
                ],
                'dec' => [
                    ['l' => 'Auto-anonymise + archive', 'v' => 'Compliant', 's' => 'Policy met · data minimised · no liability', 'g' => true],
                    ['l' => 'Retain beyond policy', 'v' => 'Breach', 's' => 'Compliance liability · regulatory risk', 'g' => false],
                ],
                'out' => [
                    ['l' => 'Profiles anonymised', 'v' => round($rbCnt * .8) . ' (auto)'],
                    ['l' => 'Manual review flagged', 'v' => round($rbCnt * .2) . ' (edge cases)'],
                    ['l' => 'Policy status', 'v' => 'Compliant after processing'],
                    ['l' => 'Processing time', 'v' => '< 1 hour'],
                ],
                'ctas' => [
                    ['ico' => '⏳', 'l' => 'Auto-anonymise ' . round($rbCnt * .8) . ' profiles now', 'd' => 'Apply automated anonymisation to all eligible profiles. Flag ' . round($rbCnt * .2) . ' edge cases for manual review.', 'b' => 'Compliance achieved', 'c' => '#A855F7', 'view' => 'retention_all', 'vt' => 'Retention Breach Queue — All ' . $rbCnt, 'vs' => 'Inactive profiles · ranked by days inactive', 'vf' => ['All', 'Retention breach', 'GDPR risk']],
                ],
            ],
        ];

        $grCards = $gdprRisk->take(40)->map(fn($p)    => $this->governanceCard($p))->values();
        $avCards = $accessViol->take(40)->map(fn($p)  => $this->governanceCard($p))->values();
        $atCards = $auditTrail->take(40)->map(fn($p)  => $this->governanceCard($p))->values();
        $rbCards = $retentionBr->take(40)->map(fn($p) => $this->governanceCard($p))->values();

        $userGroups = [
            'gdpr_all'      => $grCards,
            'gdpr_erase'    => $grCards->filter(fn($c) => in_array('GDPR risk', $c['tags']))->values(),
            'access_all'    => $avCards,
            'audit_all'     => $atCards,
            'retention_all' => $rbCards,
        ];

        return view('client.layers.l8', compact('scenarios', 'userGroups'));
    }

    // ── Analytics Dashboard ──────────────────────────────────────────────────────
    // public function analytics()
    // {
    //     $all = BehavioralProfile::orderByDesc('overall_score')->get();

    //     $totalProfiles = $all->count();
    //     $avgIntent = round($all->avg('intent_score') ?? 0);
    //     $avgChurn = round($all->avg('churn_score') ?? 0);
    //     $avgLoyalty = round($all->avg('loyalty_score') ?? 0);
    //     $avgEngagement = round($all->avg('engagement_score') ?? 0);

    //     // Segment distribution
    //     $segments = [
    //         'champion' => $all->where('segment', 'champion')->count(),
    //         'loyal' => $all->where('segment', 'loyal')->count(),
    //         'at_risk' => $all->where('segment', 'at_risk')->count(),
    //         'new' => $all->where('segment', 'new')->count(),
    //         'dormant' => $all->where('segment', 'dormant')->count(),
    //     ];

    //     // Score distributions for charts
    //     $intentDistribution = [
    //         '0-20' => $all->whereBetween('intent_score', [0, 20])->count(),
    //         '21-40' => $all->whereBetween('intent_score', [21, 40])->count(),
    //         '41-60' => $all->whereBetween('intent_score', [41, 60])->count(),
    //         '61-80' => $all->whereBetween('intent_score', [61, 80])->count(),
    //         '81-100' => $all->whereBetween('intent_score', [81, 100])->count(),
    //     ];

    //     $churnDistribution = [
    //         'Low (0-25)' => $all->whereBetween('churn_score', [0, 25])->count(),
    //         'Medium (26-50)' => $all->whereBetween('churn_score', [26, 50])->count(),
    //         'High (51-75)' => $all->whereBetween('churn_score', [51, 75])->count(),
    //         'Critical (76-100)' => $all->whereBetween('churn_score', [76, 100])->count(),
    //     ];

    //     // Top profiles
    //     $topIntent = $all->sortByDesc('intent_score')->take(5)->values();
    //     $topChurnRisk = $all->sortByDesc('churn_score')->take(5)->values();
    //     $topLoyal = $all->sortByDesc('loyalty_score')->take(5)->values();

    //     // Recent activity
    //     $recentActive = $all->where('last_active_at', '>=', now()->subDays(7))->count();
    //     $recentInactive = $all->where('last_active_at', '<', now()->subDays(30))->count();

    //     // Revenue metrics
    //     $totalLTV = $all->sum(fn($p) => $p->overall_score * 980);
    //     $atRiskLTV = $all->where('churn_score', '>=', 65)->sum(fn($p) => $p->overall_score * 620);

    //     // Weekly trend data
    //     $trendLabels = [];
    //     $trendIntent = [];
    //     $trendChurn = [];
    //     $trendEngagement = [];
    //     for ($i = 6; $i >= 0; $i--) {
    //         $trendLabels[] = now()->subDays($i * 7)->format('M d');
    //         $trendIntent[] = max(0, min(100, $avgIntent + rand(-10, 10)));
    //         $trendChurn[] = max(0, min(100, $avgChurn + rand(-8, 8)));
    //         $trendEngagement[] = max(0, min(100, $avgEngagement + rand(-5, 5)));
    //     }

    //     return view('client.behavioral-analytics', compact(
    //         'totalProfiles', 'avgIntent', 'avgChurn', 'avgLoyalty', 'avgEngagement',
    //         'segments', 'intentDistribution', 'churnDistribution',
    //         'topIntent', 'topChurnRisk', 'topLoyal',
    //         'recentActive', 'recentInactive',
    //         'totalLTV', 'atRiskLTV',
    //         'trendLabels', 'trendIntent', 'trendChurn', 'trendEngagement'
    //     ));
    // }

    public function analytics()
    {
        $client = auth('client')->user();
        $dataSource = $client ? $client->dataSources()->first() : null;
        $hasConnectedSource = $dataSource && $dataSource->status === 'connected';
        $dataSourceConnected = false;

        // Try to use connected EdTech data if available
        if ($hasConnectedSource) {
            try {
                $engine = $this->getEdTechEngine();
                if ($engine && $engine->isConnected()) {
                    $profiles = $engine->getStudentProfiles(100);
                    if (!empty($profiles)) {
                        $dataSourceConnected = true;
                        $all = collect($profiles)->map(function($p) {
                            return (object) $p;
                        });
                    }
                }
            } catch (\Exception $e) {
                \Log::error('EdTech analytics failed: ' . $e->getMessage());
            }
        }

        // Fallback: local BehavioralProfile demo data
        if (!isset($all)) {
            $all = BehavioralProfile::orderByDesc('overall_score')->get();
        }

        $totalProfiles = $all->count();
        $avgIntent = round($all->avg('intent_score') ?? 0);
        $avgChurn = round($all->avg('churn_score') ?? 0);
        $avgLoyalty = round($all->avg('loyalty_score') ?? 0);
        $avgEngagement = round($all->avg('engagement_score') ?? 0);

        // Segment distribution
        $segments = [
            'champion' => $all->where('segment', 'champion')->count(),
            'loyal' => $all->where('segment', 'loyal')->count(),
            'at_risk' => $all->where('segment', 'at_risk')->count(),
            'new' => $all->where('segment', 'new')->count(),
            'dormant' => $all->where('segment', 'dormant')->count(),
        ];

        // Score distributions for charts
        $intentDistribution = [
            '0-20' => $all->whereBetween('intent_score', [0, 20])->count(),
            '21-40' => $all->whereBetween('intent_score', [21, 40])->count(),
            '41-60' => $all->whereBetween('intent_score', [41, 60])->count(),
            '61-80' => $all->whereBetween('intent_score', [61, 80])->count(),
            '81-100' => $all->whereBetween('intent_score', [81, 100])->count(),
        ];

        $churnDistribution = [
            'Low (0-25)' => $all->whereBetween('churn_score', [0, 25])->count(),
            'Medium (26-50)' => $all->whereBetween('churn_score', [26, 50])->count(),
            'High (51-75)' => $all->whereBetween('churn_score', [51, 75])->count(),
            'Critical (76-100)' => $all->whereBetween('churn_score', [76, 100])->count(),
        ];

        // Top profiles
        $topIntent = $all->sortByDesc('intent_score')->take(5)->values();
        $topChurnRisk = $all->sortByDesc('churn_score')->take(5)->values();
        $topLoyal = $all->sortByDesc('loyalty_score')->take(5)->values();

        // Recent activity - handle both EdTech and local data
        if ($dataSourceConnected) {
            $recentActive = $all->filter(fn($p) => 
                !empty($p->last_login) && \Carbon\Carbon::parse($p->last_login)->gte(now()->subDays(7))
            )->count();
            $recentInactive = $all->filter(fn($p) => 
                empty($p->last_login) || \Carbon\Carbon::parse($p->last_login)->lt(now()->subDays(30))
            )->count();
        } else {
            $recentActive = $all->where('last_active_at', '>=', now()->subDays(7))->count();
            $recentInactive = $all->where('last_active_at', '<', now()->subDays(30))->count();
        }

        // Revenue metrics
        $totalLTV = $all->sum(fn($p) => ($p->overall_score ?? 0) * 980);
        $atRiskLTV = $all->where('churn_score', '>=', 65)->sum(fn($p) => ($p->overall_score ?? 0) * 620);

        // Weekly trend data
        $trendLabels = [];
        $trendIntent = [];
        $trendChurn = [];
        $trendEngagement = [];
        for ($i = 6; $i >= 0; $i--) {
            $trendLabels[] = now()->subDays($i * 7)->format('M d');
            $trendIntent[] = max(0, min(100, $avgIntent + rand(-10, 10)));
            $trendChurn[] = max(0, min(100, $avgChurn + rand(-8, 8)));
            $trendEngagement[] = max(0, min(100, $avgEngagement + rand(-5, 5)));
        }

        return view('client.behavioral-analytics', compact(
            'totalProfiles', 'avgIntent', 'avgChurn', 'avgLoyalty', 'avgEngagement',
            'segments', 'intentDistribution', 'churnDistribution',
            'topIntent', 'topChurnRisk', 'topLoyal',
            'recentActive', 'recentInactive',
            'totalLTV', 'atRiskLTV',
            'trendLabels', 'trendIntent', 'trendChurn', 'trendEngagement',
            'dataSourceConnected'
        ));
    }

    private function renderAnalyticsFromEdTech(array $profiles, bool $isLive)
    {
        $totalProfiles = count($profiles);
        $avgIntent = $totalProfiles > 0 ? round(array_sum(array_column($profiles, 'intent_score')) / $totalProfiles) : 0;
        $avgChurn = $totalProfiles > 0 ? round(array_sum(array_column($profiles, 'churn_score')) / $totalProfiles) : 0;
        $avgLoyalty = $totalProfiles > 0 ? round(array_sum(array_column($profiles, 'loyalty_score')) / $totalProfiles) : 0;
        $avgEngagement = $totalProfiles > 0 ? round(array_sum(array_column($profiles, 'engagement_score')) / $totalProfiles) : 0;
        
        // Segment distribution from EdTech segments
        $segments = [
            'champion' => count(array_filter($profiles, fn($p) => $p['segment'] === 'champion')),
            'loyal' => count(array_filter($profiles, fn($p) => $p['segment'] === 'loyal')),
            'at_risk' => count(array_filter($profiles, fn($p) => $p['segment'] === 'at_risk')),
            'new' => count(array_filter($profiles, fn($p) => $p['segment'] === 'new')),
            'dormant' => count(array_filter($profiles, fn($p) => $p['segment'] === 'dormant')),
        ];
        
        // ... rest of calculations adapted for array data ...
        
        return view('client.behavioral-analytics', compact(
            'totalProfiles', 'avgIntent', 'avgChurn', 'avgLoyalty', 'avgEngagement',
            'segments', 'intentDistribution', 'churnDistribution',
            'topIntent', 'topChurnRisk', 'topLoyal',
            'recentActive', 'recentInactive',
            'totalLTV', 'atRiskLTV',
            'trendLabels', 'trendIntent', 'trendChurn', 'trendEngagement'
        ) + ['dataSourceConnected' => $isLive]);
    }

    private function renderAnalyticsFromLocal()
    {
        $all = BehavioralProfile::orderByDesc('overall_score')->get();
        // ... existing analytics() code ...
        
        return view('client.behavioral-analytics', compact(
            // ... all existing variables ...
        ) + ['dataSourceConnected' => false]);
    }

    // ── EdTech Data Preparation for Decision Centre ──────────────────────────
    private function prepareEdTechData(array $profiles): array
    {
        $all = collect($profiles);

        // Ensure edtech_temp connection exists
        $client = auth('client')->user();
        $studentMeta = []; // [studentId => ['create_date' => ..., 'email' => ...]]

        if ($client) {
            $dataSource = $client->dataSources()->where('status', 'connected')->first();
            if ($dataSource) {
                $config = [
                    'driver'    => $dataSource->db_type,
                    'host'      => $dataSource->host,
                    'port'      => $dataSource->port,
                    'database'  => $dataSource->database_name,
                    'username'  => $dataSource->username,
                    'password'  => $dataSource->password ?? '',
                    'charset'   => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix'    => '',
                    'strict'    => true,
                    'engine'    => null,
                ];
                config()->set('database.connections.edtech_temp', $config);

                // Fetch studentuser metadata (create_date + email) in ONE query
                try {
                    $studentMeta = \DB::connection('edtech_temp')
                        ->table('studentuser')
                        ->select('studentId', 'create_date', 'email')
                        ->whereNotNull('create_date')
                        ->get()
                        ->keyBy('studentId')
                        ->map(fn($row) => ['create_date' => $row->create_date, 'email' => $row->email])
                        ->toArray();
                    
                    \Log::info('Student metadata fetched: ' . count($studentMeta) . ' records');
                } catch (\Exception $e) {
                    \Log::warning('Could not fetch studentuser metadata: ' . $e->getMessage());
                }
            }
        }

        // Fetch speaking scores from mock_test_results (lowest score per student)
        $speakingScores = [];
        try {
            $speakingScores = \DB::connection('edtech_temp')
                ->table('mock_test_results')
                ->select('studentId', \DB::raw('MIN(speaking_score) as lowest_speaking_score'))
                ->whereNotNull('speaking_score')
                ->groupBy('studentId')
                ->pluck('lowest_speaking_score', 'studentId')
                ->toArray();
        } catch (\Exception $e) {
            \Log::warning('Could not fetch speaking scores: ' . $e->getMessage());
        }

        // Merge all external data into profiles
        $all = $all->map(function($p) use ($speakingScores, $studentMeta) {
            $possibleIds = array_filter([
                $p['id'] ?? null,
                $p['studentId'] ?? null,
                $p['student_id'] ?? null,
                $p['user_id'] ?? null,
                $p['studentID'] ?? null,
            ]);
            
            $p['speaking_score'] = null;
            $p['create_date'] = null;
            $p['email'] = $p['email'] ?? null;
            
            foreach ($possibleIds as $possibleId) {
                if (isset($speakingScores[$possibleId])) {
                    $p['speaking_score'] = $speakingScores[$possibleId];
                }
                if (isset($studentMeta[$possibleId])) {
                    $p['create_date'] = $studentMeta[$possibleId]['create_date'];
                    $p['email'] = $studentMeta[$possibleId]['email'] ?? $p['email'];
                }
            }
            
            return $p;
        });

        // ── L4 Cohorts from EdTech data ─────────────────────────────────────
        // Hot Buyers: Students who have NOT purchased (total_spent <= 0 means no payment record)
        $hotBuyers  = $all->filter(fn($p) =>
            ($p['total_spent'] ?? 0) <= 0 &&
            ($p['intent_score'] ?? 0) >= 40
        );

        // At Risk: Students with high churn score (any student, purchased or not)
        $atRisk = $all->filter(fn($p) =>
            ($p['churn_score'] ?? 0) >= 65
        );

        // Loyals: Students with high loyalty score OR high overall score as fallback
        $loyals = $all->filter(fn($p) =>
            ($p['loyalty_score'] ?? 0) >= 40 ||
            ($p['overall_score'] ?? 0) >= 60
        );

        // Reactivation: Students with high reactivation potential and low engagement
        $reactivate = $all->filter(fn($p) =>
            ($p['reactivation_potential'] ?? 0) >= 70 &&
            ($p['engagement_score'] ?? 0) < 30
        );

        // Speaking Score Intervention: Students with speaking_score < 30
        $speakingLow = $all->filter(function($p) {
            $score = $p['speaking_score'] ?? null;
            return $score !== null && is_numeric($score) && (float)$score < 30;
        });

        // ── L1 Cohorts (derived from EdTech behavior patterns) ───────────────
        $pricingUsers = $all->filter(fn($p) =>
            ($p['total_spent'] ?? 0) <= 0 &&
            ($p['intent_score'] ?? 0) >= 60 &&
            ($p['buying_readiness'] ?? 0) >= 40
        );
        $rageUsers    = $all->filter(fn($p) => ($p['frustration_score'] ?? 0) >= 50);
        $cartUsers    = $all->filter(fn($p) =>
            ($p['total_spent'] ?? 0) <= 0 &&
            ($p['buying_readiness'] ?? 0) >= 30 &&
            ($p['intent_score'] ?? 0) >= 40
        );
        $paymentUsers = $all->filter(fn($p) => ($p['total_spent'] ?? 0) > 0);

        // Stats
        $hbFullPrice = $hotBuyers->filter(fn($p) => ($p['trust_score'] ?? 0) >= 65)->count();
        $hbDiscount  = $hotBuyers->filter(fn($p) => ($p['trust_score'] ?? 0) < 65)->count();
        $hbTotal     = $hotBuyers->count();
        $hbRevEst    = '$' . number_format($hbTotal * 294);
        $fullPct     = $hbTotal > 0 ? (int) round($hbFullPrice / $hbTotal * 100) : 38;
        $discPct     = 100 - $fullPct;

        $hbAvgReadiness    = $hbTotal > 0 ? (int) round($hotBuyers->avg('buying_readiness') ?? 0) : 0;
        $arAvgChurn        = $atRisk->count() > 0 ? (int) round($atRisk->avg('churn_score') ?? 0) : 0;
        $arAvgFrustration  = $atRisk->count() > 0 ? (int) round($atRisk->avg('frustration_score') ?? 0) : 0;
        $loAvgLoyalty      = $loyals->count() > 0 ? (int) round($loyals->avg('loyalty_score') ?? 0) : 0;
        $loAvgEngagement   = $loyals->count() > 0 ? (int) round($loyals->avg('engagement_score') ?? 0) : 0;
        $rvAvgReactivation = $reactivate->count() > 0 ? (int) round($reactivate->avg('reactivation_potential') ?? 0) : 0;
        $rvAvgEngagement   = $reactivate->count() > 0 ? (int) round($reactivate->avg('engagement_score') ?? 0) : 0;
        $prAvgIntent       = $pricingUsers->count() > 0 ? (int) round($pricingUsers->avg('intent_score') ?? 0) : 0;
        $prAvgReadiness    = $pricingUsers->count() > 0 ? (int) round($pricingUsers->avg('buying_readiness') ?? 0) : 0;
        $raAvgIntent       = $rageUsers->count() > 0 ? (int) round($rageUsers->avg('intent_score') ?? 0) : 0;
        $raAvgFrustration  = $rageUsers->count() > 0 ? (int) round($rageUsers->avg('frustration_score') ?? 0) : 0;
        $caAvgReadiness    = $cartUsers->count() > 0 ? (int) round($cartUsers->avg('buying_readiness') ?? 0) : 0;
        $caAvgIntent       = $cartUsers->count() > 0 ? (int) round($cartUsers->avg('intent_score') ?? 0) : 0;
        $paAvgTrust        = $paymentUsers->count() > 0 ? (int) round($paymentUsers->avg('trust_score') ?? 0) : 0;
        $paAvgIntent       = $paymentUsers->count() > 0 ? (int) round($paymentUsers->avg('intent_score') ?? 0) : 0;

        $realAbandonPct = 72;
        $realConvPct    = 28;
        $avgPricingPerUser = $pricingUsers->count() > 0 ? 3.4 : 3.4;
        $avgRagePerUser    = $rageUsers->count() > 0 ? 8 : 8;

        $marginSaved = (int) round($hbFullPrice * 294 * 0.1);
        $blanketCost = (int) round($hbTotal * 294 * 0.1);
        $discountCost = (int) round($hbDiscount * 294 * 0.1);
        $netRevSmart = (int) round($hbTotal * 294 - $discountCost);
        $netRevBlanket = (int) round($hbTotal * 294 - $blanketCost);
        $retentionNow = (int) round($atRisk->count() * 4200 * 0.41);
        $retentionWait = (int) round($atRisk->count() * 4200 * 0.20);
        $avgPNow = 41; $avgPWait = 20; $avgPPers = 31; $avgPGen = 8;

        $arRevRisk = '$' . number_format($atRisk->sum(fn($p) => max(1000, ($p['overall_score'] ?? 0) * 620)));

        $stats = [
            'l4' => [
                'hotbuyer'  => [
                    'count' => $hbTotal, 'avgIntent' => $hotBuyers->count() > 0 ? (int) round($hotBuyers->avg('intent_score') ?? 0) : 0,
                    'avgReadiness' => $hbAvgReadiness, 'fullPrice' => $hbFullPrice, 'fullPricePct' => $fullPct,
                    'discount' => $hbDiscount, 'revEst' => $hbRevEst,
                    'entCount' => $hotBuyers->filter(fn($p) => ($p['segment'] ?? '') === 'champion')->count(),
                ],
                'atrisk'    => [
                    'count' => $atRisk->count(), 'revRisk' => $arRevRisk,
                    'avgChurn' => $arAvgChurn, 'avgFrustration' => $arAvgFrustration,
                ],
                'loyal'     => [
                    'count' => $loyals->count(), 'avgLoyalty' => $loAvgLoyalty,
                    'avgEngagement' => $loAvgEngagement,
                    'ambassadors' => $loyals->filter(fn($p) => ($p['loyalty_score'] ?? 0) > 88)->count(),
                ],
                'reactready' => [
                    'count' => $reactivate->count(), 'revRec' => '$' . number_format($reactivate->count() * 4200),
                    'avgReactivation' => $rvAvgReactivation, 'avgEngagement' => $rvAvgEngagement,
                ],
            ],
            'l1' => [
                'cart'    => [
                    'count' => $cartUsers->count(), 'hv' => $cartUsers->filter(fn($p) => ($p['buying_readiness'] ?? 0) >= 50)->count(),
                    'avgReadiness' => $caAvgReadiness, 'avgIntent' => $caAvgIntent,
                    'abandonPct' => $realAbandonPct, 'convPct' => $realConvPct,
                ],
                'pricing' => [
                    'count' => $pricingUsers->count(), 'avgIntent' => $prAvgIntent,
                    'avgReadiness' => $prAvgReadiness, 'avgPerUser' => $avgPricingPerUser,
                ],
                'rage'    => [
                    'count' => $rageUsers->count(), 'avgIntent' => $raAvgIntent,
                    'avgFrustration' => $raAvgFrustration, 'avgClicks' => $avgRagePerUser,
                ],
                'payment' => [
                    'count' => $paymentUsers->count(), 'avgTrust' => $paAvgTrust, 'avgIntent' => $paAvgIntent,
                ],
            ],
            'l5' => [
                'fullPrice' => $hbFullPrice, 'fullPct' => $fullPct, 'discount' => $hbDiscount, 'discPct' => $discPct,
                'marginSaved' => $marginSaved, 'blanketCost' => $blanketCost, 'discountCost' => $discountCost,
                'netRevSmart' => $netRevSmart, 'netRevBlanket' => $netRevBlanket,
                'retentionNow' => $retentionNow, 'retentionWait' => $retentionWait,
                'avgPNow' => $avgPNow, 'avgPWait' => $avgPWait, 'avgPPers' => $avgPPers, 'avgPGen' => $avgPGen,
            ],
        ];

        $scenarios = $this->buildScenarios($stats);

        // Build cards with create_date for sorting
        $hbCards = $hotBuyers->map(fn($p) => $this->edHotBuyerCard($p))->values();
        $arCards = $atRisk->map(fn($p) => $this->edAtRiskCard($p))->values();
        $loCards = $loyals->map(fn($p) => $this->edLoyalCard($p))->values();
        $rvCards = $reactivate->map(fn($p) => $this->edReactivCard($p))->values();
        $prCards = $pricingUsers->map(fn($p) => $this->edPricingCard($p))->values();
        $raCards = $rageUsers->map(fn($p) => $this->edRageCard($p))->values();
        $caCards = $cartUsers->map(fn($p) => $this->edCartCard($p))->values();
        $paCards = $paymentUsers->map(fn($p) => $this->edPaymentCard($p))->values();
        $dcCards = $hotBuyers->map(fn($p) => $this->edDiscCard($p))->values();
        $spCards = $speakingLow->map(fn($p) => $this->edSpeakingCard($p))->values();

        // Sort ALL card collections by create_date descending
        $sortByCreateDateDesc = function($collection) {
            return $collection->sortByDesc(function($card) {
                $createDate = $card['create_date_raw'] ?? null;
                if ($createDate) {
                    try {
                        return \Carbon\Carbon::parse($createDate)->timestamp;
                    } catch (\Exception $e) {
                        return 0;
                    }
                }
                return 0;
            })->values();
        };

        $hbCards = $sortByCreateDateDesc($hbCards);
        $arCards = $sortByCreateDateDesc($arCards);
        $loCards = $sortByCreateDateDesc($loCards);
        $rvCards = $sortByCreateDateDesc($rvCards);
        $prCards = $sortByCreateDateDesc($prCards);
        $raCards = $sortByCreateDateDesc($raCards);
        $caCards = $sortByCreateDateDesc($caCards);
        $paCards = $sortByCreateDateDesc($paCards);
        $dcCards = $sortByCreateDateDesc($dcCards);
        $spCards = $sortByCreateDateDesc($spCards);

        $userGroups = [
            'hb_all' => $hbCards, 'hb_ent' => $hbCards->filter(fn($c) => $c['tier'] === 'Enterprise')->values(),
            'ar_all' => $arCards, 'ar_top50' => $arCards->sortByDesc('scoreValue')->take(5)->values(), 'ar_disc' => $arCards,
            'loyal_all' => $loCards, 'loyal_amb' => $loCards->filter(fn($c) => in_array('Ambassador ready', $c['tags']))->values(),
            'loyboost_all' => $loCards, 'loyboost_top' => $loCards->filter(fn($c) => in_array('Ambassador ready', $c['tags']))->values(),
            'reactiv_all' => $rvCards, 'reactiv_sup' => $rvCards, 'reactval_all' => $rvCards,
            'pricing_all' => $prCards, 'pricing_trial' => $prCards, 'demo_all' => $prCards, 'demo_review' => $prCards,
            'rage_live' => $raCards, 'rage_chat' => $raCards, 'rage_bug' => $raCards,
            'cart_all' => $caCards, 'cart_high' => $caCards->filter(fn($c) => in_array('High value', $c['tags']))->values(),
            'cart_ent' => $caCards->filter(fn($c) => $c['tier'] === 'Enterprise')->values(),
            'nologin_all' => $arCards, 'nologin_ent' => $arCards->filter(fn($c) => $c['tier'] === 'Enterprise')->values(),
            'payment_upsell' => $paCards, 'payment_loyalty' => $paCards,
            'disc_run' => $dcCards, 'disc_ab' => $dcCards,
            'nochurn_all' => $arCards, 'nochurn_wait' => $arCards,
            'disc10_not_purchased' => $hbCards->filter(fn($c) => in_array('Paid learner', $c['tags']) === false)->values(),
            'nochurn_at_risk'      => $arCards,
            'reactval_ready'       => $rvCards,
            'loyboost_loyal'       => $loCards,
            'speaking_low'         => $spCards,
        ];

        return compact('scenarios', 'userGroups') + ['emailTemplates' => $this->getEmailTemplates()];
    }

    // ── EdTech Card Builders ─────────────────────────────────────────────────
    private function edHotBuyerCard(array $p): array
    {
        $plan = $this->plan($p['segment'] ?? 'new');
        [$tc, $tb] = $this->tierStyle($p['segment'] ?? 'new');
        $sens = ($p['trust_score'] ?? 0) >= 65 ? 'Price-insensitive' : 'Price-sensitive';
        $offer = $sens === 'Price-insensitive' ? "{$plan} — feature highlight, no discount" : "10% off {$plan} plan";
        $last = !empty($p['last_login']) ? \Carbon\Carbon::parse($p['last_login'])->diffForHumans() : 'Unknown';
        $email = $p['email'] ?? 'N/A';
        $createDate = !empty($p['create_date']) ? \Carbon\Carbon::parse($p['create_date'])->format('Y-m-d H:i') : 'N/A';
        $tags = [];
        if ($sens === 'Price-insensitive') $tags[] = 'Full price (no discount)'; else $tags[] = '10% discount offer';
        if (($p['segment'] ?? '') === 'champion') $tags[] = 'Enterprise';
        if (($p['intent_score'] ?? 0) >= 88) $tags[] = 'Highest intent';
        return [
            'id' => $p['id'], 'name' => $p['name'] ?? 'Student #' . $p['id'], 'email' => $email,
            'initials' => $this->getInitials($p['name'] ?? ''), 'company' => $this->getCompany($p['email'] ?? ''),
            'industry' => 'EdTech', 'tier' => $plan, 'tierColor' => $tc, 'tierBg' => $tb,
            'avatarColor' => $this->edSegmentColor($p['segment'] ?? 'new'),
            'amountLabel' => "Intent " . ($p['intent_score'] ?? 0), 'tags' => $tags,
            'create_date_raw' => $p['create_date'] ?? null,
            'fields' => [
                ['Intent score', ($p['intent_score'] ?? 0) . '/100'], ['Buying readiness', ($p['readiness_score'] ?? 0) . '/100'],
                ['Email', $email], ['Registration date', $createDate],
                ['Plan evaluating', $plan], ['Price sensitivity', $sens], ['Offer assigned', $offer],
                ['Loyalty score', ($p['loyalty_score'] ?? 0) . '/100'], ['Churn risk', ($p['churn_score'] ?? 0) . '%'],
                ['Engagement', ($p['engagement_score'] ?? 0) . '/100'], ['Last active', $last],
            ],
            'timeline' => [
                ['e' => "Quiz avg " . ($p['avg_quiz_score'] ?? 0) . " — strong performance", 't' => '3 days ago', 'c' => '#0EA5E9'],
                ['e' => "Completed " . ($p['completed_courses'] ?? 0) . " courses", 't' => '1 day ago', 'c' => '#10B981'],
                ['e' => "Intent " . ($p['intent_score'] ?? 0) . " + Readiness " . ($p['readiness_score'] ?? 0) . " → Hot Learner", 't' => $last, 'c' => '#F59E0B'],
                ['e' => "Upsell offer prepared — awaiting dispatch", 't' => 'now', 'c' => '#FF6B35'],
            ],
            'actions' => [
                ['l' => 'Send offer now', 'c' => '#10B981', 'bc' => '#10B98144'],
                ['l' => 'Preview offer', 'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
            ],
        ];
    }

    private function edAtRiskCard(array $p): array
    {
        $plan = $this->plan($p['segment'] ?? 'new');
        [$tc, $tb] = $this->tierStyle($p['segment'] ?? 'new');
        $revRisk = number_format(max(100, ($p['overall_score'] ?? 0) * 620));
        $days = !empty($p['last_login']) ? (int) \Carbon\Carbon::parse($p['last_login'])->diffInDays() : 18;
        $rf = ($p['frustration_score'] ?? 0) > 55 ? "Frustration score: {$p['frustration_score']}" : "Login recency: {$days} days";
        $intervention = ($p['segment'] ?? '') === 'champion' ? 'Tier 1 — CSM outreach' : (($p['churn_score'] ?? 0) >= 75 ? 'Tier 2 — 3-touch sequence' : 'Tier 3 — email only');
        $tags = []; if (($p['churn_score'] ?? 0) >= 75) $tags[] = 'Imminent churn'; if (($p['segment'] ?? '') === 'champion') $tags[] = 'Enterprise';
        $last = !empty($p['last_login']) ? \Carbon\Carbon::parse($p['last_login'])->diffForHumans() : 'Unknown';
        $email = $p['email'] ?? 'N/A';
        $createDate = !empty($p['create_date']) ? \Carbon\Carbon::parse($p['create_date'])->format('Y-m-d H:i') : 'N/A';
        return [
            'id' => $p['id'], 'name' => $p['name'] ?? 'Student #' . $p['id'], 'email' => $email,
            'initials' => $this->getInitials($p['name'] ?? ''), 'company' => $this->getCompany($p['email'] ?? ''),
            'industry' => 'EdTech', 'tier' => $plan, 'tierColor' => $tc, 'tierBg' => $tb,
            'avatarColor' => $this->edSegmentColor($p['segment'] ?? 'new'),
            'amountLabel' => '$' . $revRisk . ' at risk', 'tags' => $tags,
            'create_date_raw' => $p['create_date'] ?? null,
            'fields' => [
                ['Churn probability', ($p['churn_score'] ?? 0) . '%'], ['Revenue at risk', '$' . $revRisk],
                ['Email', $email], ['Registration date', $createDate],
                ['Top risk factor', $rf], ['Days since login', $days . 'd'],
                ['Frustration score', ($p['frustration_score'] ?? 0) . '/100'], ['Engagement score', ($p['engagement_score'] ?? 0) . '/100'],
                ['LTV', '$' . number_format(($p['overall_score'] ?? 0) * 980)], ['Intervention tier', $intervention], ['Last active', $last],
            ],
            'timeline' => [
                ['e' => 'Login frequency dropped — early churn signal', 't' => '5 days ago', 'c' => '#F59E0B'],
                ['e' => "Risk factor: \"{$rf}\" — churn model re-evaluated", 't' => '2 days ago', 'c' => '#F43F5E'],
                ['e' => "Churn probability hit " . ($p['churn_score'] ?? 0) . "% — At Risk state triggered", 't' => 'Today', 'c' => '#F43F5E'],
                ['e' => "{$intervention} — intervention queued", 't' => 'now', 'c' => '#0EA5E9'],
            ],
            'actions' => [
                ['l' => 'Send win-back', 'c' => '#F43F5E', 'bc' => '#F43F5E44'],
                ['l' => 'Escalate to CSM', 'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
            ],
        ];
    }

    private function edLoyalCard(array $p): array
    {
        $plan = $this->plan($p['segment'] ?? 'new');
        [$tc, $tb] = $this->tierStyle($p['segment'] ?? 'new');
        $isAmb = ($p['loyalty_score'] ?? 0) > 88; $gap = max(0, (100 - ($p['loyalty_score'] ?? 0)) * 12);
        $refs = max(1, (int) round(($p['loyalty_score'] ?? 0) / 15));
        $last = !empty($p['last_login']) ? \Carbon\Carbon::parse($p['last_login'])->diffForHumans() : 'Unknown';
        $email = $p['email'] ?? 'N/A';
        $createDate = !empty($p['create_date']) ? \Carbon\Carbon::parse($p['create_date'])->format('Y-m-d H:i') : 'N/A';
        $tags = []; if ($isAmb) $tags[] = 'Ambassador ready'; if ($refs >= 6) $tags[] = 'Super referrer'; if ($gap < 200) $tags[] = 'Near tier upgrade';
        return [
            'id' => $p['id'], 'name' => $p['name'] ?? 'Student #' . $p['id'], 'email' => $email,
            'initials' => $this->getInitials($p['name'] ?? ''), 'company' => $this->getCompany($p['email'] ?? ''),
            'industry' => 'EdTech', 'tier' => $plan, 'tierColor' => $tc, 'tierBg' => $tb,
            'avatarColor' => $this->edSegmentColor($p['segment'] ?? 'new'),
            'amountLabel' => "Loyalty " . ($p['loyalty_score'] ?? 0), 'tags' => $tags,
            'create_date_raw' => $p['create_date'] ?? null,
            'fields' => [
                ['Loyalty score', ($p['loyalty_score'] ?? 0) . '/100'], ['Referrals sent', $refs . ''],
                ['Email', $email], ['Registration date', $createDate],
                ['Converted', max(1, (int) round($refs * 0.65)) . ''], ['Engagement', ($p['engagement_score'] ?? 0) . '/100'],
                ['Churn risk', ($p['churn_score'] ?? 0) . '%'], ['Next tier gap', $gap . ' pts'],
                ['LTV', '$' . number_format(($p['overall_score'] ?? 0) * 980)], ['Last active', $last], ['Industry', 'EdTech'],
            ],
            'timeline' => [
                ['e' => 'Loyalty milestone reached — consecutive active weeks', 't' => '2 days ago', 'c' => '#10B981'],
                ['e' => "Loyalty score " . ($p['loyalty_score'] ?? 0) . " — Loyal Advocate confirmed", 't' => '1 day ago', 'c' => '#F59E0B'],
                ['e' => "{$refs} referrals sent · " . max(1, (int)round($refs * .65)) . ' converted this month', 't' => 'this month', 'c' => '#10B981'],
                ['e' => $isAmb ? 'Ambassador invitation ready' : "{$gap} pts from next tier — tier nudge ready", 't' => 'now', 'c' => '#0EA5E9'],
            ],
            'actions' => [
                ['l' => 'Send referral amplification', 'c' => '#10B981', 'bc' => '#10B98144'],
                ['l' => $isAmb ? 'Send ambassador invite' : 'Send tier nudge', 'c' => '#F59E0B', 'bc' => '#F59E0B44'],
            ],
        ];
    }

    private function edReactivCard(array $p): array
    {
        $plan = $this->plan($p['segment'] ?? 'new');
        [$tc, $tb] = $this->tierStyle($p['segment'] ?? 'new');
        $daysGone = !empty($p['last_login']) ? (int) \Carbon\Carbon::parse($p['last_login'])->diffInDays() : 55;
        $last = !empty($p['last_login']) ? \Carbon\Carbon::parse($p['last_login'])->diffForHumans() : 'Unknown';
        $reason = ($p['frustration_score'] ?? 0) > 40 ? 'Support failure' : 'Feature gap';
        $msg = $reason === 'Support failure' ? 'Personal apology from Head of CS + dedicated contact' : 'We shipped the feature you asked for — free 30-day trial';
        $tags = []; if (($p['reactivation_potential'] ?? 0) >= 80) $tags[] = 'High potential'; if (($p['engagement_score'] ?? 0) < 20) $tags[] = 'Deeply dormant';
        $email = $p['email'] ?? 'N/A';
        $createDate = !empty($p['create_date']) ? \Carbon\Carbon::parse($p['create_date'])->format('Y-m-d H:i') : 'N/A';
        return [
            'id' => $p['id'], 'name' => $p['name'] ?? 'Student #' . $p['id'], 'email' => $email,
            'initials' => $this->getInitials($p['name'] ?? ''), 'company' => $this->getCompany($p['email'] ?? ''),
            'industry' => 'EdTech', 'tier' => $plan, 'tierColor' => $tc, 'tierBg' => $tb,
            'avatarColor' => $this->edSegmentColor($p['segment'] ?? 'new'),
            'amountLabel' => "Score " . ($p['reactivation_potential'] ?? 0), 'tags' => $tags,
            'create_date_raw' => $p['create_date'] ?? null,
            'fields' => [
                ['Reactivation score', ($p['reactivation_potential'] ?? 0) . '/100'], ['Churn reason', $reason],
                ['Email', $email], ['Registration date', $createDate],
                ['Days since churn', $daysGone . 'd'], ['Previous LTV', '$' . number_format(($p['overall_score'] ?? 0) * 720)],
                ['Email resp.', ($p['reactivation_potential'] ?? 0) > 78 ? 'High' : 'Medium'],
                ['Win-back message', substr($msg, 0, 40) . '…'], ['Last plan', $plan], ['Industry', 'EdTech'], ['Last active', $last],
            ],
            'timeline' => [
                ['e' => "Churned — primary reason: {$reason}", 't' => $daysGone . 'd ago', 'c' => '#F43F5E'],
                ['e' => "Reactivation score reached " . ($p['reactivation_potential'] ?? 0) . " — qualified for win-back", 't' => 'Today', 'c' => '#10B981'],
                ['e' => 'Segment assigned: "' . $reason . '"', 't' => '< 1hr ago', 'c' => '#0EA5E9'],
                ['e' => 'Message ready: "' . substr($msg, 0, 48) . '"…"', 't' => 'now', 'c' => '#F59E0B'],
            ],
            'actions' => [
                ['l' => 'Send win-back message', 'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
                ['l' => $reason === 'Support failure' ? 'Route to CS team' : 'Preview message', 'c' => '#10B981', 'bc' => '#10B98144'],
            ],
        ];
    }

    private function edPricingCard(array $p): array
    {
        $plan = $this->plan($p['segment'] ?? 'new');
        [$tc, $tb] = $this->tierStyle($p['segment'] ?? 'new');
        $sens = ($p['trust_score'] ?? 0) >= 65 ? 'Price-insensitive' : 'Price-sensitive';
        $offer = $sens === 'Price-insensitive' ? 'Feature highlight, no discount' : 'Upgrade + 10% off';
        $visits = max(2, (int) round(($p['intent_score'] ?? 0) / 20));
        $last = !empty($p['last_login']) ? \Carbon\Carbon::parse($p['last_login'])->diffForHumans() : 'Unknown';
        $tags = []; if ($sens === 'Price-insensitive') $tags[] = 'Price insensitive'; else $tags[] = 'Price sensitive'; if (($p['intent_score'] ?? 0) > 85) $tags[] = 'Highest intent';
        $email = $p['email'] ?? 'N/A';
        $createDate = !empty($p['create_date']) ? \Carbon\Carbon::parse($p['create_date'])->format('Y-m-d H:i') : 'N/A';
        return [
            'id' => $p['id'], 'name' => $p['name'] ?? 'Student #' . $p['id'], 'email' => $email,
            'initials' => $this->getInitials($p['name'] ?? ''), 'company' => $this->getCompany($p['email'] ?? ''),
            'industry' => 'EdTech', 'tier' => $plan, 'tierColor' => $tc, 'tierBg' => $tb,
            'avatarColor' => $this->edSegmentColor($p['segment'] ?? 'new'),
            'amountLabel' => "Intent " . ($p['intent_score'] ?? 0), 'priceSensitivity' => $sens, 'offer' => $offer, 'tags' => $tags,
            'create_date_raw' => $p['create_date'] ?? null,
            'fields' => [
                ['Intent score', ($p['intent_score'] ?? 0) . '/100'], ['Pricing visits', $visits . ' this week'],
                ['Email', $email], ['Registration date', $createDate],
                ['Plan evaluating', $plan], ['Price sensitivity', $sens], ['Offer assigned', $offer],
                ['Engagement', ($p['engagement_score'] ?? 0) . '/100'], ['Buying readiness', ($p['buying_readiness'] ?? 0) . '/100'],
                ['Last session', $last], ['Industry', 'EdTech'],
            ],
            'timeline' => [
                ['e' => "1st pricing visit — {$plan} tier — " . max(3, (int)(($p['intent_score'] ?? 0) / 10)) . 'min dwell', 't' => '3 days ago', 'c' => '#3A4A60'],
                ['e' => '2nd pricing visit — plan comparison detected', 't' => '1 day ago', 'c' => '#0EA5E9'],
                ['e' => "3rd pricing visit — intent score hit " . ($p['intent_score'] ?? 0), 't' => $last, 'c' => '#10B981'],
                ['e' => "Hot Buyer state triggered — \"{$offer}\" queued", 't' => '< 10 min ago', 'c' => '#F59E0B'],
            ],
            'actions' => [
                ['l' => 'Send upgrade outreach', 'c' => '#00FFB2', 'bc' => '#00FFB244'],
                ['l' => 'Create CRM task (B2B)', 'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
            ],
        ];
    }

    private function edRageCard(array $p): array
    {
        $plan = $this->plan($p['segment'] ?? 'new');
        [$tc, $tb] = $this->tierStyle($p['segment'] ?? 'new');
        $clicks = max(6, (int) round(($p['frustration_score'] ?? 0) / 9));
        $cv = max(79, ($p['buying_readiness'] ?? 0) * 9);
        $tags = []; if ($cv > 400) $tags[] = 'High value cart'; if (($p['segment'] ?? '') === 'champion') $tags[] = 'Enterprise'; $tags[] = 'Live session';
        $email = $p['email'] ?? 'N/A';
        $createDate = !empty($p['create_date']) ? \Carbon\Carbon::parse($p['create_date'])->format('Y-m-d H:i') : 'N/A';
        return [
            'id' => $p['id'], 'name' => $p['name'] ?? 'Student #' . $p['id'], 'email' => $email,
            'initials' => $this->getInitials($p['name'] ?? ''), 'company' => $this->getCompany($p['email'] ?? ''),
            'industry' => 'EdTech', 'tier' => $plan, 'tierColor' => $tc, 'tierBg' => $tb,
            'avatarColor' => $this->edSegmentColor($p['segment'] ?? 'new'),
            'amountLabel' => "{$clicks} clicks", 'tags' => $tags,
            'create_date_raw' => $p['create_date'] ?? null,
            'fields' => [
                ['Rage clicks', $clicks . ' on same element'], ['Element', 'Submit Payment button'],
                ['Email', $email], ['Registration date', $createDate],
                ['Error', 'Validation error not shown'], ['Cart value', '$' . number_format($cv)],
                ['Intent score', ($p['intent_score'] ?? 0) . '/100'], ['Frustration', ($p['frustration_score'] ?? 0) . '/100'],
                ['Session time', max(4, (int)(($p['engagement_score'] ?? 0) / 5)) . 'm'], ['Page', '/checkout'], ['Industry', 'EdTech'],
            ],
            'timeline' => [
                ['e' => 'Session started on /checkout from ' . (($p['intent_score'] ?? 0) > 80 ? 'email link' : 'direct'), 't' => max(8, $clicks * 2) . 'min ago', 'c' => '#0EA5E9'],
                ['e' => 'Validation error on Submit Payment button', 't' => '5min ago', 'c' => '#F43F5E'],
                ['e' => "{$clicks} rage clicks — Frustrated Buyer state triggered", 't' => '2min ago', 'c' => '#F43F5E'],
                ['e' => 'Real-time help widget queued — awaiting trigger', 't' => '< 30s', 'c' => '#F59E0B'],
            ],
            'actions' => [
                ['l' => 'Trigger help widget now', 'c' => '#F43F5E', 'bc' => '#F43F5E44'],
                ['l' => 'Connect to support agent', 'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
            ],
        ];
    }

    private function edCartCard(array $p): array
    {
        $plan = $this->plan($p['segment'] ?? 'new');
        [$tc, $tb] = $this->tierStyle($p['segment'] ?? 'new');
        $cv = max(55, ($p['buying_readiness'] ?? 0) * 8);
        $mins = max(31, ($p['churn_score'] ?? 0) * 3);
        $sens = ($p['trust_score'] ?? 0) >= 65 ? 'Price-insensitive' : 'Price-sensitive';
        $offer = $sens === 'Price-sensitive' ? '10% off — complete now' : 'Your cart is waiting';
        $tags = []; if ($cv > 300) $tags[] = 'High value'; if ($sens === 'Price-sensitive') $tags[] = 'Price sensitive'; if (($p['segment'] ?? '') === 'champion') $tags[] = 'Enterprise';
        $email = $p['email'] ?? 'N/A';
        $createDate = !empty($p['create_date']) ? \Carbon\Carbon::parse($p['create_date'])->format('Y-m-d H:i') : 'N/A';
        return [
            'id' => $p['id'], 'name' => $p['name'] ?? 'Student #' . $p['id'], 'email' => $email,
            'initials' => $this->getInitials($p['name'] ?? ''), 'company' => $this->getCompany($p['email'] ?? ''),
            'industry' => 'EdTech', 'tier' => $plan, 'tierColor' => $tc, 'tierBg' => $tb,
            'avatarColor' => $this->edSegmentColor($p['segment'] ?? 'new'),
            'amountLabel' => '$' . number_format($cv), 'priceSensitivity' => $sens, 'offer' => $offer, 'tags' => $tags,
            'create_date_raw' => $p['create_date'] ?? null,
            'fields' => [
                ['Cart value', '$' . number_format($cv)], ['Recovery offer', $offer], ['Abandoned', $mins . 'min ago'],
                ['Email', $email], ['Registration date', $createDate],
                ['Price sensitivity', $sens], ['Intent score', ($p['intent_score'] ?? 0) . '/100'], ['Channel', 'Email'],
                ['Session time', max(2, (int)(($p['engagement_score'] ?? 0) / 10)) . 'm'],
                ['Pages viewed', max(3, (int)(($p['engagement_score'] ?? 0) / 8)) . ''], ['Industry', 'EdTech'],
            ],
            'timeline' => [
                ['e' => 'Added item ($' . number_format($cv) . ') to cart', 't' => ($mins + 15) . 'min ago', 'c' => '#10B981'],
                ['e' => 'Reached /checkout — entered shipping address', 't' => ($mins + 8) . 'min ago', 'c' => '#0EA5E9'],
                ['e' => 'Exited checkout — cart remains active', 't' => $mins . 'min ago', 'c' => '#F43F5E'],
                ['e' => "Recovery \"{$offer}\" queued", 't' => 'now', 'c' => '#F59E0B'],
            ],
            'actions' => [
                ['l' => 'Send recovery message now', 'c' => '#00FFB2', 'bc' => '#00FFB244'],
                ['l' => $sens === 'Price-sensitive' ? 'Apply 10% discount' : 'Send reminder', 'c' => '#F59E0B', 'bc' => '#F59E0B44'],
            ],
        ];
    }

    private function edPaymentCard(array $p): array
    {
        $plan = $this->plan($p['segment'] ?? 'new');
        [$tc, $tb] = $this->tierStyle($p['segment'] ?? 'new');
        $amount = max(150, ($p['trust_score'] ?? 0) * 18);
        $last = !empty($p['last_login']) ? \Carbon\Carbon::parse($p['last_login'])->diffForHumans() : 'Unknown';
        $tags = []; if (($p['trust_score'] ?? 0) >= 65) $tags[] = 'Price insensitive'; if (($p['segment'] ?? '') === 'champion') $tags[] = 'Enterprise';
        $email = $p['email'] ?? 'N/A';
        $createDate = !empty($p['create_date']) ? \Carbon\Carbon::parse($p['create_date'])->format('Y-m-d H:i') : 'N/A';
        return [
            'id' => $p['id'], 'name' => $p['name'] ?? 'Student #' . $p['id'], 'email' => $email,
            'initials' => $this->getInitials($p['name'] ?? ''), 'company' => $this->getCompany($p['email'] ?? ''),
            'industry' => 'EdTech', 'tier' => $plan, 'tierColor' => $tc, 'tierBg' => $tb,
            'avatarColor' => $this->edSegmentColor($p['segment'] ?? 'new'),
            'amountLabel' => '$' . number_format($amount), 'tags' => $tags,
            'create_date_raw' => $p['create_date'] ?? null,
            'fields' => [
                ['Purchase amount', '$' . number_format($amount)], ['Upsell window', '48 hours'], ['Plan', $plan],
                ['Email', $email], ['Registration date', $createDate],
                ['Trust score', ($p['trust_score'] ?? 0) . '/100'], ['Intent score', ($p['intent_score'] ?? 0) . '/100'],
                ['Upsell predicted', 'Yes (34% CVR)'], ['Industry', 'EdTech'],
                ['Loyalty score', ($p['loyalty_score'] ?? 0) . '/100'], ['Last active', $last],
            ],
            'timeline' => [
                ['e' => 'Payment $' . number_format($amount) . ' completed — ' . $plan . ' plan', 't' => $last, 'c' => '#10B981'],
                ['e' => '48-hour upsell window opened (3.2× conversion rate)', 't' => $last, 'c' => '#0EA5E9'],
                ['e' => 'Personalised cross-sell prepared from purchase history', 't' => 'now', 'c' => '#F59E0B'],
                ['e' => 'Ad retargeting suppressed — customer already converted', 't' => 'now', 'c' => '#10B981'],
            ],
            'actions' => [
                ['l' => 'Send post-purchase offer', 'c' => '#00FFB2', 'bc' => '#00FFB244'],
                ['l' => 'Award loyalty points', 'c' => '#F59E0B', 'bc' => '#F59E0B44'],
            ],
        ];
    }

    private function edDiscCard(array $p): array
    {
        $plan = $this->plan($p['segment'] ?? 'new');
        [$tc, $tb] = $this->tierStyle($p['segment'] ?? 'new');
        $sens = ($p['trust_score'] ?? 0) >= 65 ? 'Price-insensitive' : 'Price-sensitive';
        $offer = $sens === 'Price-insensitive' ? "{$plan} — feature email, no discount" : "{$plan} — 10% time-limited offer";
        $cvr = $sens === 'Price-insensitive' ? '34% (full price)' : (round(18 + ($p['buying_readiness'] ?? 0) / 5) . '%');
        $tags = []; if ($sens === 'Price-insensitive') $tags[] = 'Margin protected'; if (($p['intent_score'] ?? 0) >= 88) $tags[] = 'Highest intent';
        $last = !empty($p['last_login']) ? \Carbon\Carbon::parse($p['last_login'])->diffForHumans() : 'Unknown';
        $email = $p['email'] ?? 'N/A';
        $createDate = !empty($p['create_date']) ? \Carbon\Carbon::parse($p['create_date'])->format('Y-m-d H:i') : 'N/A';
        return [
            'id' => $p['id'], 'name' => $p['name'] ?? 'Student #' . $p['id'], 'email' => $email,
            'initials' => $this->getInitials($p['name'] ?? ''), 'company' => $this->getCompany($p['email'] ?? ''),
            'industry' => 'EdTech', 'tier' => $plan, 'tierColor' => $tc, 'tierBg' => $tb,
            'avatarColor' => $this->edSegmentColor($p['segment'] ?? 'new'),
            'amountLabel' => "Intent " . ($p['intent_score'] ?? 0), 'priceSensitivity' => $sens, 'offer' => $offer, 'tags' => $tags,
            'create_date_raw' => $p['create_date'] ?? null,
            'fields' => [
                ['Intent score', ($p['intent_score'] ?? 0) . '/100'], ['Sensitivity', $sens], ['Plan evaluating', $plan],
                ['Email', $email], ['Registration date', $createDate],
                ['Offer assigned', $offer], ['Expected CVR', $cvr], ['Buying readiness', ($p['buying_readiness'] ?? 0) . '/100'],
                ['Hot Buyer since', $last], ['LTV', '$' . number_format(($p['overall_score'] ?? 0) * 880)], ['Industry', 'EdTech'],
            ],
            'timeline' => [
                ['e' => "Pricing page — {$plan} tier — " . max(3, (int)(($p['intent_score'] ?? 0) / 10)) . 'min dwell', 't' => '3hr ago', 'c' => '#0EA5E9'],
                ['e' => "Intent " . ($p['intent_score'] ?? 0) . " — Hot Buyer state triggered", 't' => '1hr ago', 'c' => '#F59E0B'],
                ['e' => "Price sensitivity: {$sens} — offer assigned", 't' => '< 30min ago', 'c' => '#10B981'],
                ['e' => '"' . $offer . '" — dispatch queued', 't' => 'now', 'c' => '#FF6B35'],
            ],
            'actions' => [
                ['l' => 'Dispatch offer', 'c' => '#10B981', 'bc' => '#10B98144'],
                ['l' => 'Edit offer', 'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
            ],
        ];
    }

    private function edSpeakingCard(array $p): array
    {
        $plan = $this->plan($p['segment'] ?? 'new');
        [$tc, $tb] = $this->tierStyle($p['segment'] ?? 'new');
        $last = !empty($p['last_login']) ? \Carbon\Carbon::parse($p['last_login'])->diffForHumans() : 'Unknown';
        $speakingScore = $p['speaking_score'] ?? 0;
        $email = $p['email'] ?? 'N/A';
        $createDate = !empty($p['create_date']) ? \Carbon\Carbon::parse($p['create_date'])->format('Y-m-d H:i') : 'N/A';
        
        return [
            'id' => $p['id'],
            'name' => $p['name'] ?? 'Student #' . $p['id'], 'email' => $email,
            'initials' => $this->getInitials($p['name'] ?? ''),
            'company' => $this->getCompany($p['email'] ?? ''),
            'industry' => 'EdTech',
            'tier' => $plan,
            'tierColor' => $tc,
            'tierBg' => $tb,
            'avatarColor' => $this->edSegmentColor($p['segment'] ?? 'new'),
            'amountLabel' => "Speaking {$speakingScore}",
            'tags' => ['Low speaking score', 'Needs tutoring', '1-on-1 coaching'],
            'create_date_raw' => $p['create_date'] ?? null,
            'fields' => [
                ['Speaking score', "{$speakingScore}/90"],
                ['Email', $email],
                ['Registration date', $createDate],
                ['Overall score', ($p['overall_score'] ?? 0) . '/90'],
                ['Student ID', $p['id']],
                ['Last active', $last],
                ['Recommended action', 'Personalised tutoring session'],
                ['Intervention type', 'Speaking coaching'],
            ],
            'timeline' => [
                ['e' => "Speaking score {$speakingScore} detected — below threshold (30)", 't' => 'Recently', 'c' => '#F43F5E'],
                ['e' => 'Flagged for speaking intervention', 't' => '2 days ago', 'c' => '#F59E0B'],
                ['e' => 'Personalised tutoring plan prepared', 't' => '1 day ago', 'c' => '#0EA5E9'],
                ['e' => '1-on-1 coaching session recommended', 't' => 'now', 'c' => '#10B981'],
            ],
            'actions' => [
                ['l' => 'Assign speaking tutor', 'c' => '#10B981', 'bc' => '#10B98144'],
                ['l' => 'Send study plan', 'c' => '#0EA5E9', 'bc' => '#0EA5E944'],
            ],
        ];
    }

    private function edSegmentColor(string $segment): string
    {
        return match($segment) {
            'champion' => '#FFD700', 'loyal' => '#A855F7', 'at_risk' => '#F43F5E',
            'new' => '#0EA5E9', 'dormant' => '#10B981', default => '#0EA5E9',
        };
    }

    /**
     * Send email to a student (with logging)
     */
    public function sendEmail(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'template_id' => 'nullable|integer|exists:email_templates,id',
        ]);

        $client = auth('client')->user();
        $data = $request->only(['to', 'subject', 'body', 'template_id']);

        try {
            // Send email
            \Mail::raw($data['body'], function ($message) use ($data) {
                $message->to($data['to'])
                        ->subject($data['subject']);
            });

            // Log success
            $log = EmailLog::create([
                'client_id' => $client->id,
                'email_template_id' => $data['template_id'] ?? null,
                'email_address' => $data['to'],
                'subject' => $data['subject'],
                'body' => $data['body'],
                'type' => 'single',
                'bulk_count' => 1,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully',
                'log_id' => $log->id,
            ]);

        } catch (\Exception $e) {
            // Log failure
            EmailLog::create([
                'client_id' => $client->id,
                'email_template_id' => $data['template_id'] ?? null,
                'email_address' => $data['to'],
                'subject' => $data['subject'],
                'body' => $data['body'],
                'type' => 'single',
                'bulk_count' => 1,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send bulk email to multiple students (with logging)
     */
    public function sendBulkEmail(Request $request)
    {
        $request->validate([
            'emails' => 'required|array|min:1',
            'emails.*' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'template_id' => 'nullable|integer|exists:email_templates,id',
        ]);

        $client = auth('client')->user();
        $emails = $request->input('emails');
        $subject = $request->input('subject');
        $body = $request->input('body');
        $templateId = $request->input('template_id');
        $sent = 0;
        $failedEmails = [];

        foreach ($emails as $email) {
            try {
                \Mail::raw($body, function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
                $sent++;

                // Log individual success
                EmailLog::create([
                    'client_id' => $client->id,
                    'email_template_id' => $templateId,
                    'email_address' => $email,
                    'subject' => $subject,
                    'body' => $body,
                    'type' => 'single',
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

            } catch (\Exception $e) {
                $failedEmails[] = $email;

                // Log individual failure
                EmailLog::create([
                    'client_id' => $client->id,
                    'email_template_id' => $templateId,
                    'email_address' => $email,
                    'subject' => $subject,
                    'body' => $body,
                    'type' => 'single',
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        // Log bulk summary
        EmailLog::create([
            'client_id' => $client->id,
            'email_template_id' => $templateId,
            'email_address' => implode(', ', array_slice($emails, 0, 3)) . (count($emails) > 3 ? ' + ' . (count($emails) - 3) . ' more' : ''),
            'subject' => $subject,
            'body' => $body,
            'type' => 'bulk',
            'bulk_count' => count($emails),
            'status' => $sent > 0 ? 'sent' : 'failed',
            'sent_at' => $sent > 0 ? now() : null,
        ]);

        return response()->json([
            'success' => $sent > 0,
            'sent_count' => $sent,
            'failed_count' => count($failedEmails),
            'failed_emails' => $failedEmails,
            'message' => $sent > 0 
                ? 'Email sent to ' . $sent . ' of ' . count($emails) . ' recipients' 
                : 'Failed to send email to all recipients',
        ]);
    }

    /**
     * Get email templates for the current client
     */
    private function getEmailTemplates(): array
    {
        $client = auth('client')->user();
        if (!$client) return [];
        
        return EmailTemplate::where('client_id', $client->id)
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'subject' => $t->subject,
                'body' => $t->body,
                'category' => $t->category,
            ])
            ->toArray();
    }

    /**
     * Apply template variables to content
     */
    private function applyTemplateVars(string $content, array $student): string
    {
        $vars = [
            '{{student_name}}' => $student['name'] ?? 'Student',
            '{{student_id}}' => $student['id'] ?? '',
            '{{email}}' => $student['email'] ?? '',
            '{{speaking_score}}' => $student['speaking_score'] ?? 'N/A',
            '{{intent_score}}' => $student['intent_score'] ?? 'N/A',
            '{{churn_score}}' => $student['churn_score'] ?? 'N/A',
            '{{loyalty_score}}' => $student['loyalty_score'] ?? 'N/A',
            '{{engagement_score}}' => $student['engagement_score'] ?? 'N/A',
            '{{overall_score}}' => $student['overall_score'] ?? 'N/A',
            '{{completed_courses}}' => $student['completed_courses'] ?? '0',
            '{{best_score}}' => $student['best_score'] ?? 'N/A',
            '{{days_since_login}}' => !empty($student['last_login']) ? \Carbon\Carbon::parse($student['last_login'])->diffInDays() : 'N/A',
            '{{company_name}}' => auth('client')->user()?->company_name ?? 'Your Learning Platform',
            '{{booking_link}}' => url('/book-session'),
            '{{upgrade_link}}' => url('/upgrade'),
            '{{test_link}}' => url('/mock-test'),
            '{{referral_link}}' => url('/refer'),
            '{{calendar_link}}' => url('/schedule-call'),
            '{{support_phone}}' => '+1-800-LEARN',
            '{{offer_price}}' => '$99',
            '{{discount_amount}}' => '$50',
            '{{referral_bonus}}' => '$20',
        ];

        return str_replace(array_keys($vars), array_values($vars), $content);
    }

    /**
     * Show email templates management page
     */
    public function emailTemplates()
    {
        $client = auth('client')->user();
        $templates = EmailTemplate::where('client_id', $client->id)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
            
        $categories = EmailTemplateCategory::where('client_id', $client->id)
            ->orderBy('sort_order')
            ->get();

        return view('client.email-templates', compact('templates', 'categories'));
    }

    /**
     * Store new email template
     */
    public function storeTemplate(Request $request)
    {
        $client = auth('client')->user();
        
        // Get all valid category slugs for this client
        $validCategories = EmailTemplateCategory::where('client_id', $client->id)
            ->pluck('slug')
            ->toArray();
        
        // Always include 'general' as fallback
        if (!in_array('general', $validCategories)) {
            $validCategories[] = 'general';
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'required|in:' . implode(',', $validCategories),
            'is_active' => 'boolean',
        ]);

        EmailTemplate::create([
            'client_id' => $client->id,
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $request->body,
            'category' => $request->category,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('client.email.templates')->with('success', 'Template created successfully');
    }

    /**
     * Update email template
     */
    public function updateTemplate(Request $request, EmailTemplate $template)
    {
        // Verify ownership
        if ($template->client_id !== auth('client')->id()) {
            abort(403, 'Unauthorized');
        }
        
        $client = auth('client')->user();
        
        // Get all valid category slugs for this client
        $validCategories = EmailTemplateCategory::where('client_id', $client->id)
            ->pluck('slug')
            ->toArray();
        
        // Always include 'general' as fallback
        if (!in_array('general', $validCategories)) {
            $validCategories[] = 'general';
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'required|in:' . implode(',', $validCategories),
            'is_active' => 'boolean',
        ]);

        $template->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $request->body,
            'category' => $request->category,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('client.email.templates')->with('success', 'Template updated successfully');
    }

    /**
     * Delete email template
     */
    public function deleteTemplate(EmailTemplate $template)
    {
        // Verify ownership
        if ($template->client_id !== auth('client')->id()) {
            abort(403, 'Unauthorized');
        }
        
        $template->delete();
        
        return redirect()->route('client.email.templates')->with('success', 'Template deleted');
    }

    /**
     * Get all categories for current client
     */
    private function getEmailTemplateCategories(): array
    {
        $client = auth('client')->user();
        if (!$client) return [];

        return EmailTemplateCategory::where('client_id', $client->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'color' => $c->color,
                'bg_color' => $c->bg_color,
                'is_default' => $c->is_default,
                'template_count' => EmailTemplate::where('client_id', $client->id)
                    ->where('category', $c->slug)
                    ->count(),
            ])
            ->toArray();
    }

    /**
     * Show category management page
     */
    public function emailTemplateCategories()
    {
        $client = auth('client')->user();
        $categories = EmailTemplateCategory::where('client_id', $client->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('client.email-template-categories', compact('categories'));
    }

    /**
     * Store new category
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:email_template_categories,name,NULL,id,client_id,' . auth('client')->id(),
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'bg_color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $client = auth('client')->user();

        $slug = \Str::slug($request->name);
        $baseSlug = $slug;
        $counter = 1;

        // Ensure unique slug
        while (EmailTemplateCategory::where('client_id', $client->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $maxOrder = EmailTemplateCategory::where('client_id', $client->id)->max('sort_order') ?? 0;

        EmailTemplateCategory::create([
            'client_id' => $client->id,
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color,
            'bg_color' => $request->bg_color,
            'sort_order' => $maxOrder + 1,
            'is_default' => false,
        ]);

        return redirect()->route('client.email.template.categories')->with('success', 'Category created');
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, EmailTemplateCategory $category)
    {
        if ($category->client_id !== auth('client')->id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:50|unique:email_template_categories,name,' . $category->id . ',id,client_id,' . auth('client')->id(),
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'bg_color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'required|integer|min:0',
        ]);

        // If name changed, update slug
        $slug = $category->slug;
        if ($request->name !== $category->name) {
            $slug = \Str::slug($request->name);
            $baseSlug = $slug;
            $counter = 1;
            while (EmailTemplateCategory::where('client_id', $category->client_id)
                ->where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
        }

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color,
            'bg_color' => $request->bg_color,
            'sort_order' => $request->sort_order,
        ]);

        return redirect()->route('client.email.template.categories')->with('success', 'Category updated');
    }

    /**
     * Delete category (and move templates to General)
     */
    public function deleteCategory(EmailTemplateCategory $category)
    {
        if ($category->client_id !== auth('client')->id()) {
            abort(403);
        }

        if ($category->is_default) {
            return redirect()->back()->with('error', 'Cannot delete default categories');
        }

        // Move templates to 'general'
        EmailTemplate::where('client_id', $category->client_id)
            ->where('category', $category->slug)
            ->update(['category' => 'general']);

        $category->delete();

        return redirect()->route('client.email.template.categories')->with('success', 'Category deleted. Templates moved to General.');
    }

    // ── Email Logs ─────────────────────────────────────────────────────────────

    /**
     * Show email logs/history page
     */
    public function emailLogs(Request $request)
    {
        $client = auth('client')->user();
        
        $query = EmailLog::forClient($client->id)
            ->with('template')
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->withStatus($request->status);
        }

        // Filter by type
        if ($request->filled('type') && $request->type !== 'all') {
            $query->withType($request->type);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Search by email or subject
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('email_address', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        // Stats
        $stats = EmailLog::statsForClient($client->id);

        return view('client.email-logs', compact('logs', 'stats'));
    }

    /**
     * Show email log detail (AJAX modal)
     */
    public function emailLogDetail(EmailLog $log)
    {
        if ($log->client_id !== auth('client')->id()) {
            abort(403, 'Unauthorized');
        }

        return view('client.email-log-detail', compact('log'));
    }

    /**
     * Retry failed email
     */
    public function retryEmail(EmailLog $log)
    {
        if ($log->client_id !== auth('client')->id()) {
            abort(403, 'Unauthorized');
        }

        if ($log->status !== 'failed') {
            return redirect()->back()->with('error', 'Only failed emails can be retried');
        }

        try {
            \Mail::raw($log->body ?? 'No content', function ($message) use ($log) {
                $message->to($log->email_address)
                        ->subject($log->subject ?? 'No subject');
            });

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);

            return redirect()->back()->with('success', 'Email resent successfully');

        } catch (\Exception $e) {
            $log->update([
                'error_message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to resend: ' . $e->getMessage());
        }
    }

    /**
     * Delete email log
     */
    public function deleteEmailLog(EmailLog $log)
    {
        if ($log->client_id !== auth('client')->id()) {
            abort(403, 'Unauthorized');
        }

        $log->delete();

        return redirect()->route('client.email.logs')->with('success', 'Email log deleted');
    }

    /**
     * Track email open (pixel tracking)
     */
    public function trackOpen(string $token)
    {
        try {
            $logId = decrypt($token);
            $log = EmailLog::find($logId);
            
            if ($log && !$log->opened_at) {
                $log->update([
                    'opened_at' => now(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail for tracking pixel
        }

        // Return 1x1 transparent pixel
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        
        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
