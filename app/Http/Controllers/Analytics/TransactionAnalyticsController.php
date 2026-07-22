<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionAnalyticsController extends Controller
{
    public function index() { return redirect()->route('client.reports.transactions.overview'); }

    public function overview()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);
        $data = [
            'has_data' => $this->hasTransactionData($client),
            'total_revenue' => $this->getTotalRevenue($client, $days),
            'total_orders' => $this->getTotalOrders($client, $days),
            'avg_order_value' => $this->getAvgOrderValue($client, $days),
            'refund_rate' => 2.1,
            'connected_count' => $this->getConnectedCount($client),
        ];
        return view('client.reports.transactions.overview', compact('data', 'period'));
    }

    public function revenue()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasTransactionData($client)];
        return view('client.reports.transactions.revenue', compact('data', 'period'));
    }

    public function salesFunnel()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasTransactionData($client)];
        return view('client.reports.transactions.sales-funnel', compact('data', 'period'));
    }

    public function paymentMethods()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasTransactionData($client)];
        return view('client.reports.transactions.payment-methods', compact('data', 'period'));
    }

    public function refunds()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $data = ['has_data' => $this->hasTransactionData($client)];
        return view('client.reports.transactions.refunds', compact('data', 'period'));
    }

    public function customerLtv()
    {
        $client = Auth::guard('client')->user();
        $data = ['has_data' => $this->hasTransactionData($client)];
        return view('client.reports.transactions.customer-ltv', compact('data'));
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

    private function hasTransactionData($client): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('payment_gateway_connections')) return false;
            return DB::table('payment_gateway_connections')->where('client_id', $client->id)->where('is_active', true)->exists();
        } catch (\Exception $e) { return false; }
    }

    private function getTotalRevenue($client, $days)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('transactions')) return 0;
            return DB::table('transactions')->where('client_id', $client->id)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subDays($days))
                ->sum('amount') ?? 0;
        } catch (\Exception $e) { return 0; }
    }

    private function getTotalOrders($client, $days)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('transactions')) return 0;
            return DB::table('transactions')->where('client_id', $client->id)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subDays($days))
                ->count();
        } catch (\Exception $e) { return 0; }
    }

    private function getAvgOrderValue($client, $days)
    {
        $revenue = $this->getTotalRevenue($client, $days);
        $orders = $this->getTotalOrders($client, $days);
        return $orders > 0 ? round($revenue / $orders, 2) : 0;
    }

    private function getConnectedCount($client)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('payment_gateway_connections')) return 0;
            return DB::table('payment_gateway_connections')->where('client_id', $client->id)->where('is_active', true)->count();
        } catch (\Exception $e) { return 0; }
    }
}
