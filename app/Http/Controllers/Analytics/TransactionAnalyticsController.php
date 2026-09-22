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
            'refund_rate' => $this->getRefundRate($client, $days),
            'connected_count' => $this->getConnectedCount($client),
        ];
        return view('client.reports.transactions.overview', compact('data', 'period'));
    }

    public function revenue()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);
        $hasData = $this->hasTransactionData($client);

        $byDay = $hasData ? $this->revenueByDay($client, $days) : [];
        $current = array_sum(array_column($byDay, 'revenue'));
        $previous = $hasData ? $this->getTotalRevenue($client, $days, $days) : 0;
        $change = $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : ($current > 0 ? 100.0 : 0.0);

        $data = [
            'has_data' => $hasData,
            'by_day' => $byDay,
            'total_revenue' => $current,
            'previous_revenue' => $previous,
            'change_pct' => $change,
        ];
        return view('client.reports.transactions.revenue', compact('data', 'period'));
    }

    public function salesFunnel()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);
        $hasData = $this->hasTransactionData($client);

        $data = ['has_data' => $hasData, 'funnel' => $hasData ? $this->paymentFunnel($client, $days) : []];
        return view('client.reports.transactions.sales-funnel', compact('data', 'period'));
    }

    public function paymentMethods()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);
        $hasData = $this->hasTransactionData($client);

        $data = ['has_data' => $hasData, 'methods' => $hasData ? $this->paymentMethodBreakdown($client, $days) : []];
        return view('client.reports.transactions.payment-methods', compact('data', 'period'));
    }

    public function refunds()
    {
        $client = Auth::guard('client')->user();
        $period = request('period', '30d');
        $days = $this->getDaysFromPeriod($period);
        $hasData = $this->hasTransactionData($client);

        $data = [
            'has_data' => $hasData,
            'refund_rate' => $hasData ? $this->getRefundRate($client, $days) : 0,
            'refunded_amount' => $hasData ? $this->getRefundedAmount($client, $days) : 0,
            'refunded_count' => $hasData ? $this->getRefundedCount($client, $days) : 0,
            'rows' => $hasData ? $this->refundRows($client, $days) : [],
        ];
        return view('client.reports.transactions.refunds', compact('data', 'period'));
    }

    public function customers()
    {
        $client = Auth::guard('client')->user();
        $hasData = $this->hasTransactionData($client);
        $customers = $hasData ? $this->customerRows($client) : [];

        $data = [
            'has_data' => $hasData,
            'customers' => $customers,
            'total' => count($customers),
        ];
        return view('client.reports.transactions.customers', compact('data'));
    }

    public function customerLtv()
    {
        $client = Auth::guard('client')->user();
        $hasData = $this->hasTransactionData($client);
        $customers = $hasData ? $this->customerLtvRows($client) : [];
        $avgLtv = count($customers) > 0 ? round(array_sum(array_column($customers, 'ltv')) / count($customers), 2) : 0;

        $data = [
            'has_data' => $hasData,
            'customers' => $customers,
            'avg_ltv' => $avgLtv,
            'customer_count' => count($customers),
        ];
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

    /**
     * @param int $days How far back the window is.
     * @param int|null $offsetDays When set, shifts the whole window back by
     *                             this many days — used to compute the prior
     *                             period for a real (not guessed) % change.
     */
    private function getTotalRevenue($client, $days, $offsetDays = null)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('transactions')) return 0;
            $end = $offsetDays ? now()->subDays($offsetDays) : now();
            $start = (clone $end)->subDays($days);
            return (float) DB::table('transactions')->where('client_id', $client->id)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount');
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

    private function getRefundRate($client, $days)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('transactions')) return 0;
            $base = DB::table('transactions')->where('client_id', $client->id)
                ->whereIn('status', ['completed', 'refunded'])
                ->where('created_at', '>=', now()->subDays($days));
            $total = (clone $base)->count();
            if ($total === 0) return 0;
            $refunded = (clone $base)->where('status', 'refunded')->count();
            return round(($refunded / $total) * 100, 1);
        } catch (\Exception $e) { return 0; }
    }

    private function getRefundedAmount($client, $days)
    {
        try {
            return (float) DB::table('transactions')->where('client_id', $client->id)
                ->where('status', 'refunded')
                ->where('created_at', '>=', now()->subDays($days))
                ->sum('amount');
        } catch (\Exception $e) { return 0; }
    }

    private function getRefundedCount($client, $days)
    {
        try {
            return DB::table('transactions')->where('client_id', $client->id)
                ->where('status', 'refunded')
                ->where('created_at', '>=', now()->subDays($days))
                ->count();
        } catch (\Exception $e) { return 0; }
    }

    /**
     * One row per day in the window, oldest first — feeds the Revenue line chart.
     */
    private function revenueByDay($client, $days): array
    {
        try {
            $rows = DB::table('transactions')->where('client_id', $client->id)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subDays($days)->startOfDay())
                ->selectRaw('DATE(created_at) as day, SUM(amount) as revenue, COUNT(*) as orders')
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->keyBy('day');

            $out = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $row = $rows->get($date);
                $out[] = [
                    'date' => $date,
                    'revenue' => $row ? (float) $row->revenue : 0.0,
                    'orders' => $row ? (int) $row->orders : 0,
                ];
            }
            return $out;
        } catch (\Exception $e) { return []; }
    }

    /**
     * A real, honest funnel built from what this table actually records —
     * not fabricated visit/cart-stage data. "Attempted" is every payment
     * that reached Stripe regardless of outcome; "Succeeded" narrows to
     * completed+refunded (a refund only happens after a real success);
     * "Retained" narrows further to the ones never refunded back out.
     */
    private function paymentFunnel($client, $days): array
    {
        try {
            $base = DB::table('transactions')->where('client_id', $client->id)
                ->where('created_at', '>=', now()->subDays($days));

            $attempted = (clone $base)->count();
            $succeeded = (clone $base)->whereIn('status', ['completed', 'refunded'])->count();
            $retained = (clone $base)->where('status', 'completed')->count();

            return [
                ['stage' => 'Payment Attempts', 'count' => $attempted, 'pct' => 100.0],
                ['stage' => 'Successful Payments', 'count' => $succeeded, 'pct' => $attempted > 0 ? round($succeeded / $attempted * 100, 1) : 0],
                ['stage' => 'Retained (Not Refunded)', 'count' => $retained, 'pct' => $attempted > 0 ? round($retained / $attempted * 100, 1) : 0],
            ];
        } catch (\Exception $e) { return []; }
    }

    private function paymentMethodBreakdown($client, $days): array
    {
        try {
            return DB::table('transactions')->where('client_id', $client->id)
                ->whereIn('status', ['completed', 'refunded'])
                ->where('created_at', '>=', now()->subDays($days))
                ->selectRaw("COALESCE(NULLIF(payment_method, ''), 'unknown') as method, COUNT(*) as count, SUM(amount) as total")
                ->groupBy('method')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($r) => ['method' => $r->method, 'count' => (int) $r->count, 'total' => (float) $r->total])
                ->all();
        } catch (\Exception $e) { return []; }
    }

    private function refundRows($client, $days): array
    {
        try {
            return DB::table('transactions')->where('client_id', $client->id)
                ->where('status', 'refunded')
                ->where('created_at', '>=', now()->subDays($days))
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(['transaction_reference', 'amount', 'metadata', 'created_at'])
                ->map(function ($r) {
                    $meta = json_decode($r->metadata ?? '{}', true) ?: [];
                    return [
                        'reference' => $r->transaction_reference,
                        'amount' => (float) $r->amount,
                        'customer' => $meta['customer_email'] ?? '—',
                        'date' => $r->created_at,
                    ];
                })
                ->all();
        } catch (\Exception $e) { return []; }
    }

    /**
     * Groups by customer identity straight out of Stripe's own metadata,
     * since this table has no populated customer_id/customers relation to
     * join against. Falls back to the Stripe customer ID when no email was
     * The dedicated `transactions_customers` table, synced from Stripe's own
     * Customer objects (see StripeSyncService::syncCustomers) — actual
     * names/emails/phones, kept separate from the general `customers` table
     * used by unrelated features (Customer Success, onboarding, etc).
     */
    private function customerRows($client): array
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('transactions_customers')) return [];

            return DB::table('transactions_customers')->where('client_id', $client->id)
                ->orderByDesc('lifetime_value')
                ->limit(100)
                ->get(['name', 'email', 'phone', 'lifetime_value', 'orders_count', 'gateway_created_at'])
                ->map(fn ($c) => [
                    'name' => $c->name,
                    'email' => $c->email,
                    'phone' => $c->phone,
                    'lifetime_value' => (float) $c->lifetime_value,
                    'orders' => (int) $c->orders_count,
                    'since' => $c->gateway_created_at,
                ])
                ->all();
        } catch (\Exception $e) { return []; }
    }

    /**
     * Same table as customerRows(), narrowed to customers who've actually
     * completed an order, with real first/last purchase dates pulled from
     * their transactions.
     */
    private function customerLtvRows($client): array
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('transactions_customers')) return [];

            return DB::table('transactions_customers as tc')
                ->join('transactions as t', function ($join) use ($client) {
                    $join->on('t.customer_id', '=', 'tc.id')
                        ->where('t.client_id', $client->id)
                        ->where('t.status', 'completed');
                })
                ->where('tc.client_id', $client->id)
                ->where('tc.orders_count', '>', 0)
                ->groupBy('tc.id', 'tc.email', 'tc.name', 'tc.lifetime_value', 'tc.orders_count')
                ->selectRaw('tc.email, tc.name, tc.lifetime_value as ltv, tc.orders_count as orders, MIN(t.created_at) as first_seen, MAX(t.created_at) as last_seen')
                ->orderByDesc('tc.lifetime_value')
                ->limit(50)
                ->get()
                ->map(fn ($r) => [
                    'email' => $r->email ?: $r->name,
                    'orders' => (int) $r->orders,
                    'ltv' => (float) $r->ltv,
                    'avg_order' => $r->orders > 0 ? round($r->ltv / $r->orders, 2) : 0,
                    'first_seen' => $r->first_seen,
                    'last_seen' => $r->last_seen,
                ])
                ->all();
        } catch (\Exception $e) { return []; }
    }
}
