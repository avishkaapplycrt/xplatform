<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrowthReportController extends Controller
{
    public function index() { return redirect()->route('client.reports.growth.business-health'); }

    public function businessHealth()
    {
        $client = Auth::guard('client')->user();
        $data = [
            'health_score' => $this->calculateHealthScore($client),
            'strengths' => [],
            'weaknesses' => [],
            'opportunities' => [],
        ];
        return view('client.reports.growth.business-health', compact('data'));
    }

    public function crossChannel()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => true];
        return view('client.reports.growth.cross-channel', compact('data', 'period'));
    }

    public function recommendations()
    {
        $client = Auth::guard('client')->user();
        $data = ['recommendations' => $this->getRecommendations($client)];
        return view('client.reports.growth.recommendations', compact('data'));
    }

    public function benchmarks()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => true];
        return view('client.reports.growth.benchmarks', compact('data', 'period'));
    }

    public function trends()
    {
        $client = Auth::guard('client')->user();
        $data = ['has_data' => true];
        return view('client.reports.growth.trends', compact('data'));
    }

    public function getData(Request $request, string $metric)
    {
        return response()->json(['metric' => $metric, 'data' => []]);
    }

    public function export(Request $request, string $format)
    {
        return response()->json(['message' => 'Export not yet implemented']);
    }

    private function calculateHealthScore($client): int
    {
        return 78;
    }

    private function getRecommendations($client): array
    {
        $recs = [];
        try {
            if (!DB::getSchemaBuilder()->hasTable('website_events') ||
                !DB::table('website_events')->where('client_id', $client->id)->exists()) {
                $recs[] = ['title' => 'Connect Website', 'message' => 'Track visitors and conversions', 'priority' => 'high'];
            }
        } catch (\Exception $e) {}
        return $recs;
    }
}
