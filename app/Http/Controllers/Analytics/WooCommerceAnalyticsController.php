<?php
// app/Http/Controllers/Analytics/WooCommerceAnalyticsController.php
namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\WordPressSite;
use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WooCommerceAnalyticsController extends Controller
{
    public function ecommerceOverview($siteId)
    {
        $site = WordPressSite::findOrFail($siteId);
        $days = request('days', 30);
        $startDate = Carbon::now()->subDays($days);

        // Revenue metrics
        $revenue = AnalyticsEvent::where('site_id', $site->id)
            ->where('event_type', 'like', 'wc_order_%')
            ->where('wp_created_at', '>=', $startDate)
            ->selectRaw('
                event_type,
                COUNT(*) as count,
                COALESCE(SUM(JSON_UNQUOTE(JSON_EXTRACT(payload, "$.total"))), 0) as revenue
            ')
            ->groupBy('event_type')
            ->get();

        // Sales trend
        $salesTrend = AnalyticsEvent::where('site_id', $site->id)
            ->where('event_type', 'like', 'wc_order_completed%')
            ->where('wp_created_at', '>=', $startDate)
            ->selectRaw('
                DATE(wp_created_at) as date,
                COUNT(*) as orders,
                SUM(JSON_UNQUOTE(JSON_EXTRACT(payload, "$.total"))) as revenue,
                AVG(JSON_UNQUOTE(JSON_EXTRACT(payload, "$.total"))) as avg_order
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top products
        $topProducts = AnalyticsEvent::where('site_id', $site->id)
            ->where('event_type', 'like', 'wc_order_%')
            ->where('wp_created_at', '>=', $startDate)
            ->get()
            ->flatMap(function ($event) {
                $items = json_decode($event->payload, true)['items'] ?? [];
                return collect($items)->map(fn($item) => [
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'revenue' => $item['total'],
                ]);
            })
            ->groupBy('product_id')
            ->map(function ($items) {
                return [
                    'name' => $items->first()['name'],
                    'sku' => $items->first()['sku'],
                    'total_sold' => $items->sum('quantity'),
                    'total_revenue' => $items->sum('revenue'),
                ];
            })
            ->sortByDesc('total_revenue')
            ->take(20)
            ->values();

        // Customer metrics
        $customerMetrics = AnalyticsEvent::where('site_id', $site->id)
            ->where('event_type', 'like', 'wc_order_%')
            ->where('wp_created_at', '>=', $startDate)
            ->selectRaw('
                COUNT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT(payload, "$.customer_id"))) as unique_customers,
                COUNT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT(payload, "$.billing.email"))) as unique_emails,
                COUNT(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(payload, "$.customer_id")) = 0 THEN 1 END) as guest_orders
            ')
            ->first();

        // Geographic distribution
        $geoDistribution = AnalyticsEvent::where('site_id', $site->id)
            ->where('event_type', 'like', 'wc_order_%')
            ->where('wp_created_at', '>=', $startDate)
            ->selectRaw('
                JSON_UNQUOTE(JSON_EXTRACT(payload, "$.billing.country")) as country,
                COUNT(*) as orders,
                SUM(JSON_UNQUOTE(JSON_EXTRACT(payload, "$.total"))) as revenue
            ')
            ->groupBy('country')
            ->orderByDesc('revenue')
            ->get();

        // Payment methods
        $paymentMethods = AnalyticsEvent::where('site_id', $site->id)
            ->where('event_type', 'like', 'wc_order_%')
            ->where('wp_created_at', '>=', $startDate)
            ->selectRaw('
                JSON_UNQUOTE(JSON_EXTRACT(payload, "$.payment_method_title")) as method,
                COUNT(*) as count,
                SUM(JSON_UNQUOTE(JSON_EXTRACT(payload, "$.total"))) as revenue
            ')
            ->groupBy('method')
            ->orderByDesc('count')
            ->get();

        // Coupon usage
        $couponUsage = AnalyticsEvent::where('site_id', $site->id)
            ->where('event_type', 'like', 'wc_order_%')
            ->where('wp_created_at', '>=', $startDate)
            ->whereRaw('JSON_LENGTH(JSON_EXTRACT(payload, "$.coupon_lines")) > 0')
            ->get()
            ->flatMap(function ($event) {
                $coupons = json_decode($event->payload, true)['coupon_lines'] ?? [];
                return collect($coupons)->map(fn($c) => [
                    'code' => $c['code'],
                    'discount' => $c['discount'],
                ]);
            })
            ->groupBy('code')
            ->map(function ($items) {
                return [
                    'usage_count' => $items->count(),
                    'total_discount' => $items->sum('discount'),
                ];
            })
            ->sortByDesc('usage_count');

        return view('analytics.woocommerce.overview', compact(
            'site', 'revenue', 'salesTrend', 'topProducts',
            'customerMetrics', 'geoDistribution', 'paymentMethods', 'couponUsage'
        ));
    }

    public function productAnalytics($siteId)
    {
        $site = WordPressSite::findOrFail($siteId);

        // Current product catalog
        $products = AnalyticsEvent::where('site_id', $site->id)
            ->where('event_type', 'wc_product')
            ->latest('wp_created_at')
            ->get()
            ->map(function ($event) {
                $data = json_decode($event->payload, true);
                return [
                    'product_id' => $data['product_id'],
                    'name' => $data['name'],
                    'sku' => $data['sku'],
                    'type' => $data['type'],
                    'price' => $data['price'],
                    'regular_price' => $data['regular_price'],
                    'sale_price' => $data['sale_price'],
                    'on_sale' => $data['on_sale'],
                    'stock_status' => $data['stock_status'],
                    'stock_quantity' => $data['stock_quantity'],
                    'total_sales' => $data['total_sales'],
                    'rating_count' => $data['rating_count'],
                    'average_rating' => $data['average_rating'],
                    'categories' => $data['categories'],
                    'last_updated' => $event->wp_created_at,
                ];
            })
            ->keyBy('product_id');

        // Sales velocity per product
        $salesVelocity = AnalyticsEvent::where('site_id', $site->id)
            ->where('event_type', 'like', 'wc_order_completed%')
            ->where('wp_created_at', '>=', Carbon::now()->subDays(30))
            ->get()
            ->flatMap(function ($event) {
                $items = json_decode($event->payload, true)['items'] ?? [];
                return collect($items)->map(fn($item) => [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'revenue' => $item['total'],
                    'date' => Carbon::parse($event->wp_created_at)->toDateString(),
                ]);
            })
            ->groupBy('product_id')
            ->map(function ($sales) {
                return [
                    'units_30d' => $sales->sum('quantity'),
                    'revenue_30d' => $sales->sum('revenue'),
                    'avg_daily' => round($sales->sum('quantity') / 30, 2),
                    'days_with_sales' => $sales->groupBy('date')->count(),
                ];
            });

        // Inventory alerts
        $inventoryAlerts = $products->filter(function ($product) {
            return $product['stock_status'] !== 'instock' 
                || ($product['stock_quantity'] !== null && $product['stock_quantity'] < 5);
        })->map(function ($product) {
            return [
                'name' => $product['name'],
                'sku' => $product['sku'],
                'stock_status' => $product['stock_status'],
                'stock_quantity' => $product['stock_quantity'],
                'total_sales' => $product['total_sales'],
            ];
        });

        return view('analytics.woocommerce.products', compact(
            'site', 'products', 'salesVelocity', 'inventoryAlerts'
        ));
    }

    public function customerAnalytics($siteId)
    {
        $site = WordPressSite::findOrFail($siteId);
        $days = request('days', 90);

        // Customer lifetime value
        $customers = AnalyticsEvent::where('site_id', $site->id)
            ->where('event_type', 'like', 'wc_order_completed%')
            ->where('wp_created_at', '>=', Carbon::now()->subDays($days))
            ->get()
            ->groupBy(function ($event) {
                return json_decode($event->payload, true)['customer_id'] 
                    ?? json_decode($event->payload, true)['billing']['email'] 
                    ?? 'guest';
            })
            ->map(function ($orders) {
                $first = json_decode($orders->first()->payload, true);
                return [
                    'customer_id' => $first['customer_id'] ?? 0,
                    'email' => $first['billing']['email'] ?? 'guest',
                    'name' => trim(($first['billing']['first_name'] ?? '') . ' ' . ($first['billing']['last_name'] ?? '')),
                    'country' => $first['billing']['country'] ?? 'N/A',
                    'orders_count' => $orders->count(),
                    'total_spent' => $orders->sum(fn($o) => json_decode($o->payload, true)['total']),
                    'avg_order' => $orders->avg(fn($o) => json_decode($o->payload, true)['total']),
                    'first_order' => $orders->min('wp_created_at'),
                    'last_order' => $orders->max('wp_created_at'),
                    'days_since_last' => Carbon::parse($orders->max('wp_created_at'))->diffInDays(now()),
                ];
            })
            ->sortByDesc('total_spent');

        // RFM Segmentation
        $segments = [
            'champions' => $customers->filter(fn($c) => $c['orders_count'] >= 5 && $c['days_since_last'] <= 30),
            'loyal' => $customers->filter(fn($c) => $c['orders_count'] >= 3 && $c['days_since_last'] <= 60),
            'potential' => $customers->filter(fn($c) => $c['orders_count'] >= 2 && $c['days_since_last'] <= 90),
            'at_risk' => $customers->filter(fn($c) => $c['orders_count'] >= 2 && $c['days_since_last'] > 90),
            'new' => $customers->filter(fn($c) => $c['orders_count'] === 1 && $c['days_since_last'] <= 30),
            'lost' => $customers->filter(fn($c) => $c['days_since_last'] > 180),
        ];

        return view('analytics.woocommerce.customers', compact(
            'site', 'customers', 'segments'
        ));
    }
}