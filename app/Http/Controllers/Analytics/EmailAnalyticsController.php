<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
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

        $data = [
            'has_data'        => $this->hasEmailData($client),
            'total_sent'      => $this->getTotalSent($client, $days),
            'total_delivered' => $this->getTotalDelivered($client, $days),
            'total_opens'     => $this->getTotalOpens($client, $days),
            'total_clicks'    => $this->getTotalClicks($client, $days),
            'open_rate'       => $this->getOpenRate($client, $days),
            'click_rate'      => $this->getClickRate($client, $days),
            'bounce_rate'     => $this->getBounceRate($client, $days),
            'trend_data'      => $this->getEmailTrendData($client, $days),
        ];

        return view('client.reports.email.overview', compact('data', 'period'));
    }

    public function campaigns()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasEmailData($client), 'campaigns' => []];
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
        $data = ['has_data' => $this->hasEmailData($client)];
        return view('client.reports.email.audience', compact('data'));
    }

    public function engagement()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasEmailData($client)];
        return view('client.reports.email.engagement', compact('data', 'period'));
    }

    public function deliverability()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasEmailData($client)];
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

    private function hasEmailData($client): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('email_logs')) return false;
            return DB::table('email_logs')->where('client_id', $client->id)->exists();
        } catch (\Exception $e) { return false; }
    }

    private function getTotalSent($client, $days)
    {
        try {
            return DB::table('email_logs')->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalDelivered($client, $days)
    {
        try {
            return DB::table('email_logs')->where('client_id', $client->id)
                ->where('status', '!=', 'bounced')
                ->where('created_at', '>=', now()->subDays($days))->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalOpens($client, $days)
    {
        try {
            return DB::table('email_logs')->where('client_id', $client->id)
                ->whereNotNull('opened_at')
                ->where('created_at', '>=', now()->subDays($days))->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalClicks($client, $days)
    {
        try {
            return DB::table('email_logs')->where('client_id', $client->id)
                ->whereNotNull('clicked_at')
                ->where('created_at', '>=', now()->subDays($days))->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getOpenRate($client, $days)
    {
        $sent = $this->getTotalSent($client, $days);
        $opens = $this->getTotalOpens($client, $days);
        return $sent > 0 ? round(($opens / $sent) * 100, 2) : 0;
    }

    private function getClickRate($client, $days)
    {
        $sent = $this->getTotalSent($client, $days);
        $clicks = $this->getTotalClicks($client, $days);
        return $sent > 0 ? round(($clicks / $sent) * 100, 2) : 0;
    }

    private function getBounceRate($client, $days)
    {
        try {
            $sent = DB::table('email_logs')->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))->count();
            $bounced = DB::table('email_logs')->where('client_id', $client->id)
                ->where('status', 'bounced')
                ->where('created_at', '>=', now()->subDays($days))->count();
            return $sent > 0 ? round(($bounced / $sent) * 100, 2) : 0;
        } catch (\Exception $e) { return 0; }
    }

    private function getEmailTrendData($client, $days)
    {
        try {
            return DB::table('email_logs')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();
        } catch (\Exception $e) { return collect(); }
    }
}
