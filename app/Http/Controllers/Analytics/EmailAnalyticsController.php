<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\EmailConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmailAnalyticsController extends Controller
{
    public function index() { return redirect()->route('client.reports.email.overview'); }

    public function overview()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $sent      = $this->getTotalSent($client, $days);
        $delivered = $this->getTotalDelivered($client, $days);
        $opens     = $this->getTotalOpens($client, $days);
        $clicks    = $this->getTotalClicks($client, $days);
        $unsubs    = $this->getTotalUnsubscribes($client, $days);
        $bounced   = max(0, $sent - $delivered);

        $data = [
            'has_data'          => $this->hasEmailData($client),
            'total_sent'        => $sent,
            'total_delivered'   => $delivered,
            'total_opens'       => $opens,
            'total_clicks'      => $clicks,
            'open_rate'         => $sent > 0 ? round(($opens / $sent) * 100, 2) : 0,
            'click_rate'        => $sent > 0 ? round(($clicks / $sent) * 100, 2) : 0,
            'bounce_rate'       => $sent > 0 ? round(($bounced / $sent) * 100, 2) : 0,
            'unsubscribe_rate'  => $sent > 0 ? round(($unsubs / $sent) * 100, 2) : 0,
            'trend_data'        => $this->getEmailTrendData($client, $days),
            'engagement_data'   => $this->getEngagementBreakdown($client, $days),
        ];

        return view('client.reports.email.overview', compact('data', 'period'));
    }

    public function campaigns()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $data = [
            'has_data'  => $this->hasEmailData($client),
            'campaigns' => $this->getCampaignsData($client, $days),
        ];

        return view('client.reports.email.campaigns', compact('data', 'period'));
    }

    public function campaignDetail($campaignId)
    {
        $client = Auth::guard('client')->user();
        $data = ['has_data' => $this->hasEmailData($client), 'campaign' => null];
        return view('client.reports.email.campaign-detail', compact('data', 'campaignId'));
    }

    public function audience()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $recipients = $this->getAudienceData($client, $days);

        $data = [
            'has_data'          => $this->hasEmailData($client),
            'total_subscribers' => $recipients->count(),
            'active_subscribers'=> $recipients->where('unsubscribed', false)->count(),
            'unsubscribed'      => $recipients->where('unsubscribed', true)->count(),
            'avg_open_rate'     => $recipients->count() > 0
                ? round($recipients->avg('open_rate'), 2)
                : 0,
            'recipients'        => $recipients,
        ];

        return view('client.reports.email.audience', compact('data', 'period'));
    }

    public function engagement()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $opens = $this->getTotalOpens($client, $days);
        $clicks = $this->getTotalClicks($client, $days);

        $data = [
            'has_data'             => $this->hasEmailData($client),
            'total_opens'          => $opens,
            'total_clicks'         => $clicks,
            'click_to_open_rate'   => $opens > 0 ? round(($clicks / $opens) * 100, 2) : 0,
            'avg_time_to_open_min' => $this->getAvgTimeToOpenMinutes($client, $days),
            'engaged'              => $this->getEngagedRecipients($client, $days),
        ];

        return view('client.reports.email.engagement', compact('data', 'period'));
    }

    public function deliverability()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $sent = $this->getTotalSent($client, $days);
        $delivered = $this->getTotalDelivered($client, $days);
        $bounced = max(0, $sent - $delivered);
        $unsubs = $this->getTotalUnsubscribes($client, $days);

        $data = [
            'has_data'         => $this->hasEmailData($client),
            'total_sent'       => $sent,
            'total_delivered'  => $delivered,
            'total_bounced'    => $bounced,
            'bounce_rate'      => $sent > 0 ? round(($bounced / $sent) * 100, 2) : 0,
            'unsubscribe_rate' => $sent > 0 ? round(($unsubs / $sent) * 100, 2) : 0,
            'by_provider'      => $this->getDeliverabilityByProvider($client, $days),
            'unsubscribes'     => $this->getUnsubscribeList($client, $days),
        ];

        return view('client.reports.email.deliverability', compact('data', 'period'));
    }

    public function getData(Request $request, string $metric)
    {
        return response()->json(['metric' => $metric, 'data' => []]);
    }

    public function export(Request $request, string $format)
    {
        return response()->json(['message' => 'Export not yet implemented']);
    }

    private function getDaysFromPeriod(string $period): int
    {
        return match($period) { '7d' => 7, '30d' => 30, '90d' => 90, '1y' => 365, default => 30 };
    }

    /**
     * "Has data" now means a real, active Email Engagement connection
     * (email_connections, e.g. Brevo) with actual synced rows in
     * email_logs_providers — not just the presence of the generic
     * email_logs table, which can hold unrelated/seeded rows unconnected
     * to any real provider.
     */
    private function hasEmailData($client): bool
    {
        try {
            if (!$this->hasActiveConnection($client)) return false;
            if (!DB::getSchemaBuilder()->hasTable('email_logs_providers')) return false;
            return DB::table('email_logs_providers')->where('client_id', $client->id)->exists();
        } catch (\Exception $e) { return false; }
    }

    private function hasActiveConnection($client): bool
    {
        try {
            return EmailConnection::where('client_id', $client->id)
                ->where('status', 'active')
                ->exists();
        } catch (\Exception $e) { return false; }
    }

    private function providerRows($client, $days)
    {
        return DB::table('email_logs_providers')
            ->where('client_id', $client->id)
            ->where('created_at', '>=', now()->subDays($days));
    }

    private function getTotalSent($client, $days)
    {
        try {
            return $this->providerRows($client, $days)->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalDelivered($client, $days)
    {
        try {
            return $this->providerRows($client, $days)->whereNotNull('delivered_at')->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalOpens($client, $days)
    {
        try {
            return $this->providerRows($client, $days)->whereNotNull('opened_at')->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalClicks($client, $days)
    {
        try {
            return $this->providerRows($client, $days)->where('clicked', 1)->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalUnsubscribes($client, $days)
    {
        try {
            return $this->providerRows($client, $days)->whereNotNull('unsubscribed_at')->count();
        } catch (\Exception $e) { return 0; }
    }

    /**
     * Splits every delivered row into exactly one bucket — clicked (implies
     * opened), opened-only, or delivered-with-no-engagement — plus
     * unsubscribes tracked separately since they can happen alongside any
     * of those. Feeds the "Engagement Breakdown" doughnut chart.
     */
    private function getEngagementBreakdown($client, $days): array
    {
        try {
            $delivered = $this->providerRows($client, $days)->whereNotNull('delivered_at');
            $clicked = (clone $delivered)->where('clicked', 1)->count();
            $openedOnly = (clone $delivered)->whereNotNull('opened_at')->where('clicked', '!=', 1)->count();
            $noEngagement = (clone $delivered)->whereNull('opened_at')->where('clicked', '!=', 1)->count();

            return [
                'clicked'       => $clicked,
                'opened_only'   => $openedOnly,
                'no_engagement' => $noEngagement,
                'unsubscribed'  => $this->getTotalUnsubscribes($client, $days),
            ];
        } catch (\Exception $e) {
            return ['clicked' => 0, 'opened_only' => 0, 'no_engagement' => 0, 'unsubscribed' => 0];
        }
    }

    /**
     * One row per day in range: how many were delivered that day, and how
     * many of those were opened — feeds the "Email Trend" line chart.
     */
    private function getEmailTrendData($client, $days)
    {
        try {
            return DB::table('email_logs_providers')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as sent'),
                    DB::raw('SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opens')
                )
                ->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();
        } catch (\Exception $e) { return collect(); }
    }

    /**
     * One row per distinct campaign_id — recipients, delivered, opened,
     * clicked, unsubscribed, and the derived rates. Feeds the Campaigns tab.
     */
    private function getCampaignsData($client, $days)
    {
        try {
            return DB::table('email_logs_providers')
                ->select(
                    'campaign_id',
                    'provider_name',
                    DB::raw('COUNT(*) as recipients'),
                    DB::raw('SUM(CASE WHEN delivered_at IS NOT NULL THEN 1 ELSE 0 END) as delivered'),
                    DB::raw('SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened'),
                    DB::raw('SUM(CASE WHEN clicked = 1 THEN 1 ELSE 0 END) as clicked'),
                    DB::raw('SUM(CASE WHEN unsubscribed_at IS NOT NULL THEN 1 ELSE 0 END) as unsubscribed'),
                    DB::raw('MAX(delivered_at) as last_sent_at')
                )
                ->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('campaign_id', 'provider_name')
                ->orderByDesc('last_sent_at')
                ->get()
                ->map(function ($row) {
                    $row->open_rate = $row->recipients > 0 ? round(($row->opened / $row->recipients) * 100, 1) : 0;
                    $row->click_rate = $row->recipients > 0 ? round(($row->clicked / $row->recipients) * 100, 1) : 0;
                    return $row;
                });
        } catch (\Exception $e) { return collect(); }
    }

    /**
     * One row per distinct recipient email — how many campaigns they've
     * received, their open/click activity, and whether they've
     * unsubscribed. Feeds the Audience tab.
     */
    private function getAudienceData($client, $days)
    {
        try {
            return DB::table('email_logs_providers')
                ->select(
                    'email',
                    DB::raw('MAX(name) as name'),
                    DB::raw('COUNT(*) as campaigns_received'),
                    DB::raw('SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opens'),
                    DB::raw('SUM(CASE WHEN clicked = 1 THEN 1 ELSE 0 END) as clicks'),
                    DB::raw('MAX(unsubscribed_at) as unsubscribed_at'),
                    DB::raw('MAX(COALESCE(opened_at, delivered_at)) as last_activity_at')
                )
                ->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('email')
                ->orderByDesc('last_activity_at')
                ->get()
                ->map(function ($row) {
                    $row->unsubscribed = !is_null($row->unsubscribed_at);
                    $row->open_rate = $row->campaigns_received > 0
                        ? round(($row->opens / $row->campaigns_received) * 100, 1)
                        : 0;
                    return $row;
                });
        } catch (\Exception $e) { return collect(); }
    }

    /**
     * Every row that was opened or clicked, most recent first — the
     * Engagement tab's activity table.
     */
    private function getEngagedRecipients($client, $days)
    {
        try {
            return $this->providerRows($client, $days)
                ->where(function ($q) {
                    $q->whereNotNull('opened_at')->orWhere('clicked', 1);
                })
                ->orderByDesc(DB::raw('COALESCE(opened_at, delivered_at)'))
                ->limit(100)
                ->get(['email', 'name', 'campaign_id', 'opened_at', 'clicked', 'delivered_at']);
        } catch (\Exception $e) { return collect(); }
    }

    /**
     * Average minutes between delivery and open, across every row that was
     * actually opened — only meaningful figure available from the columns
     * this table has (no explicit "sent at" separate from delivered_at).
     */
    private function getAvgTimeToOpenMinutes($client, $days): ?float
    {
        try {
            $avg = $this->providerRows($client, $days)
                ->whereNotNull('opened_at')
                ->whereNotNull('delivered_at')
                ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, delivered_at, opened_at)'));

            return $avg !== null ? round((float) $avg, 1) : null;
        } catch (\Exception $e) { return null; }
    }

    /**
     * Delivered/bounced/opened counts broken down by connected provider —
     * there's currently only ever one active connection at a time, but this
     * stays correct if that changes. Feeds the Deliverability tab.
     */
    private function getDeliverabilityByProvider($client, $days)
    {
        try {
            return DB::table('email_logs_providers')
                ->select(
                    'provider_name',
                    DB::raw('COUNT(*) as sent'),
                    DB::raw('SUM(CASE WHEN delivered_at IS NOT NULL THEN 1 ELSE 0 END) as delivered'),
                    DB::raw('SUM(CASE WHEN delivered_at IS NULL THEN 1 ELSE 0 END) as bounced')
                )
                ->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('provider_name')
                ->get();
        } catch (\Exception $e) { return collect(); }
    }

    private function getUnsubscribeList($client, $days)
    {
        try {
            return $this->providerRows($client, $days)
                ->whereNotNull('unsubscribed_at')
                ->orderByDesc('unsubscribed_at')
                ->get(['email', 'name', 'campaign_id', 'unsubscribed_at']);
        } catch (\Exception $e) { return collect(); }
    }
}
