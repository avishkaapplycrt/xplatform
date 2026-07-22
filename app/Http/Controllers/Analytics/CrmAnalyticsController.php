<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrmAnalyticsController extends Controller
{
    public function index() { return redirect()->route('client.reports.crm.overview'); }

    public function overview()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);

        $data = [
            'has_data'         => $this->hasCrmData($client),
            'total_contacts'   => $this->getTotalContacts($client),
            'total_deals'      => $this->getTotalDeals($client, $days),
            'pipeline_value'   => $this->getPipelineValue($client),
            'win_rate'         => $this->getWinRate($client, $days),
            'connected_count'  => $this->getConnectedCount($client),
        ];

        return view('client.reports.crm.overview', compact('data', 'period'));
    }

    public function pipeline()
    {
        $client = Auth::guard('client')->user();
        $data = ['has_data' => $this->hasCrmData($client)];
        return view('client.reports.crm.pipeline', compact('data'));
    }

    public function deals()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasCrmData($client)];
        return view('client.reports.crm.deals', compact('data', 'period'));
    }

    public function contacts()
    {
        $client = Auth::guard('client')->user();
        $data = ['has_data' => $this->hasCrmData($client), 'total_contacts' => $this->getTotalContacts($client)];
        return view('client.reports.crm.contacts', compact('data'));
    }

    public function activities()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasCrmData($client)];
        return view('client.reports.crm.activities', compact('data', 'period'));
    }

    public function forecast()
    {
        $client = Auth::guard('client')->user();
        $data = ['has_data' => $this->hasCrmData($client)];
        return view('client.reports.crm.forecast', compact('data'));
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

    private function hasCrmData($client): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_connections')) return false;
            return DB::table('crm_connections')->where('client_id', $client->id)->where('status', 'connected')->exists();
        } catch (\Exception $e) { return false; }
    }

    private function getTotalContacts($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_contacts')) return 0;
            return DB::table('crm_contacts')->where('client_id', $client->id)->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalDeals($client, $days)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_deals')) return 0;
            return DB::table('crm_deals')->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getPipelineValue($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_deals')) return 0;
            return DB::table('crm_deals')->where('client_id', $client->id)->sum('value') ?? 0;
        } catch (\Exception $e) { return 0; }
    }

    private function getWinRate($client, $days)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_deals')) return 0;
            $total = DB::table('crm_deals')->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days))->count();
            $won = DB::table('crm_deals')->where('client_id', $client->id)
                ->where('status', 'won')
                ->where('created_at', '>=', now()->subDays($days))->count();
            return $total > 0 ? round(($won / $total) * 100, 2) : 0;
        } catch (\Exception $e) { return 0; }
    }

    private function getConnectedCount($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('crm_connections')) return 0;
            return DB::table('crm_connections')->where('client_id', $client->id)->where('status', 'connected')->count();
        } catch (\Exception $e) { return 0; }
    }
}
