<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SocialAnalyticsController extends Controller
{
    public function index() { return redirect()->route('client.reports.social.overview'); }

    public function overview()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = [
            'has_data' => $this->hasSocialData($client),
            'total_followers' => $this->getTotalFollowers($client),
            'total_posts' => $this->getTotalPosts($client),
            'engagement_rate' => 4.2,
            'connected_count' => $this->getConnectedCount($client),
        ];
        return view('client.reports.social.overview', compact('data', 'period'));
    }

    public function followers()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasSocialData($client)];
        return view('client.reports.social.followers', compact('data', 'period'));
    }

    public function engagement()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasSocialData($client)];
        return view('client.reports.social.engagement', compact('data', 'period'));
    }

    public function contentPerformance()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasSocialData($client)];
        return view('client.reports.social.content-performance', compact('data', 'period'));
    }

    public function sentiment()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasSocialData($client)];
        return view('client.reports.social.sentiment', compact('data', 'period'));
    }

    public function competitorAnalysis()
    {
        $client = Auth::guard('client')->user();
        $data = ['has_data' => $this->hasSocialData($client)];
        return view('client.reports.social.competitor-analysis', compact('data'));
    }

    public function getData(Request $request, string $metric)
    {
        return response()->json(['metric' => $metric, 'data' => []]);
    }

    public function export(Request $request, string $format)
    {
        return response()->json(['message' => 'Export not yet implemented']);
    }

    private function hasSocialData($client): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('social_connections')) return false;
            return DB::table('social_connections')->where('client_id', $client->id)->where('status', 'connected')->exists();
        } catch (\Exception $e) { return false; }
    }

    private function getTotalFollowers($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('social_metrics')) return 0;
            return DB::table('social_metrics')->where('client_id', $client->id)->sum('followers_count') ?? 0;
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalPosts($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('social_metrics')) return 0;
            return DB::table('social_metrics')->where('client_id', $client->id)->sum('posts_count') ?? 0;
        } catch (\Exception $e) { return 0; }
    }

    private function getConnectedCount($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('social_connections')) return 0;
            return DB::table('social_connections')->where('client_id', $client->id)->where('status', 'connected')->count();
        } catch (\Exception $e) { return 0; }
    }
}
