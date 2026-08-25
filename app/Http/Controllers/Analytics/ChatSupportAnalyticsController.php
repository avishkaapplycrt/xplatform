<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatSupportAnalyticsController extends Controller
{
    public function index() { return redirect()->route('client.reports.chat-support.overview'); }

    public function overview()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = [
            'has_data' => $this->hasChatData($client),
            'total_conversations' => $this->getTotalConversations($client),
            'avg_response_time' => '4m 12s',
            'csat_score' => 4.2,
            'connected_count' => $this->getConnectedCount($client),
        ];
        return view('client.reports.chat-support.overview', compact('data', 'period'));
    }

    public function conversations()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasChatData($client)];
        return view('client.reports.chat-support.conversations', compact('data', 'period'));
    }

    public function responseTime()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasChatData($client)];
        return view('client.reports.chat-support.response-time', compact('data', 'period'));
    }

    public function satisfaction()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasChatData($client)];
        return view('client.reports.chat-support.satisfaction', compact('data', 'period'));
    }

    public function teamPerformance()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasChatData($client)];
        return view('client.reports.chat-support.team-performance', compact('data', 'period'));
    }

    public function getData(Request $request, string $metric)
    {
        return response()->json(['metric' => $metric, 'data' => []]);
    }

    public function export(Request $request, string $format)
    {
        return response()->json(['message' => 'Export not yet implemented']);
    }

    private function hasChatData($client): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('chat_support_connections')) return false;
            return DB::table('chat_support_connections')->where('client_id', $client->id)->where('status', 'connected')->exists();
        } catch (\Exception $e) { return false; }
    }

    private function getTotalConversations($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('chat_conversations')) return 0;
            return DB::table('chat_conversations')->where('client_id', $client->id)->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getConnectedCount($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('chat_support_connections')) return 0;
            return DB::table('chat_support_connections')->where('client_id', $client->id)->where('status', 'connected')->count();
        } catch (\Exception $e) { return 0; }
    }
}
