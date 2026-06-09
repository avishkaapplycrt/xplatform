<?php
// app/Services/WooCommercePollingService.php
namespace App\Services;

use App\Models\WordPressSite;
use App\Models\AnalyticsEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WooCommercePollingService
{
    public function pollWooCommerce(WordPressSite $site, array $credentials, array $config): array
    {
        $results = [
            'orders' => ['stored' => 0],
            'products' => ['stored' => 0],
            'customers' => ['stored' => 0],
            'coupons' => ['stored' => 0],
            'reviews' => ['stored' => 0],
        ];

        try {
            $results['orders'] = $this->pollOrders($site, $credentials);
            $results['products'] = $this->pollProducts($site, $credentials);
            $results['customers'] = $this->pollCustomers($site, $credentials);
            $results['coupons'] = $this->pollCoupons($site, $credentials);
            $results['reviews'] = $this->pollReviews($site, $credentials);
        } catch (\Exception $e) {
            Log::error("WooCommerce poll failed for {$site->site_name}: " . $e->getMessage());
            $results['error'] = $e->getMessage();
        }

        $totalStored = array_sum(array_column($results, 'stored'));
        return array_merge($results, ['stored' => $totalStored]);
    }

    private function buildWcClient(WordPressSite $site, array $credentials): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout(30)
            ->withBasicAuth(
                $credentials['wc_consumer_key'],
                $credentials['wc_consumer_secret']
            );
    }

    // ─── Orders ───

    private function pollOrders(WordPressSite $site, array $credentials): array
    {
        $since = $site->last_sync_at ?? Carbon::now()->subDays(7);
        $page = 1;
        $stored = 0;

        do {
            $response = $this->buildWcClient($site, $credentials)
                ->get("{$site->site_url}/wp-json/wc/v3/orders", [
                    'per_page' => 100,
                    'page' => $page,
                    'after' => $since->toIso8601String(),
                    'orderby' => 'date',
                    'order' => 'asc',
                    'status' => 'completed,processing,on-hold,refunded',
                ]);

            if (!$response->successful()) {
                throw new \Exception("WC Orders API: " . $response->body());
            }

            $orders = $response->json();
            if (empty($orders)) break;

            $events = [];
            foreach ($orders as $order) {
                $events[] = [
                    'site_id' => $site->id,
                    'event_type' => 'wc_order_' . $order['status'],
                    'wp_entity_id' => $order['id'],
                    'payload' => json_encode([
                        'order_id' => $order['id'],
                        'status' => $order['status'],
                        'currency' => $order['currency'],
                        'total' => (float) $order['total'],
                        'subtotal' => (float) ($order['line_items'][0]['subtotal'] ?? 0),
                        'discount_total' => (float) $order['discount_total'],
                        'shipping_total' => (float) $order['shipping_total'],
                        'tax_total' => (float) $order['total_tax'],
                        'refund_total' => (float) ($order['refunds'][0]['total'] ?? 0),
                        'customer_id' => $order['customer_id'],
                        'customer_ip' => $order['customer_ip_address'],
                        'billing' => [
                            'first_name' => $order['billing']['first_name'],
                            'last_name' => $order['billing']['last_name'],
                            'email' => $order['billing']['email'],
                            'phone' => $order['billing']['phone'],
                            'country' => $order['billing']['country'],
                            'state' => $order['billing']['state'],
                            'city' => $order['billing']['city'],
                        ],
                        'shipping' => [
                            'country' => $order['shipping']['country'],
                            'state' => $order['shipping']['state'],
                        ],
                        'payment_method' => $order['payment_method'],
                        'payment_method_title' => $order['payment_method_title'],
                        'transaction_id' => $order['transaction_id'],
                        'coupon_lines' => array_map(fn($c) => [
                            'code' => $c['code'],
                            'discount' => $c['discount'],
                        ], $order['coupon_lines'] ?? []),
                        'items' => array_map(fn($item) => [
                            'product_id' => $item['product_id'],
                            'variation_id' => $item['variation_id'],
                            'name' => $item['name'],
                            'sku' => $item['sku'],
                            'quantity' => $item['quantity'],
                            'price' => (float) $item['price'],
                            'total' => (float) $item['total'],
                            'subtotal' => (float) $item['subtotal'],
                            'tax_class' => $item['tax_class'],
                        ], $order['line_items']),
                        'date_created' => $order['date_created'],
                        'date_modified' => $order['date_modified'],
                        'date_completed' => $order['date_completed'],
                        'date_paid' => $order['date_paid'],
                        'cart_hash' => $order['cart_hash'],
                    ]),
                    'wp_created_at' => Carbon::parse($order['date_created'])->toDateTimeString(),
                    'synced_at' => now(),
                ];
            }

            AnalyticsEvent::insert($events);
            $stored += count($events);
            $page++;

        } while (count($orders) === 100);

        return ['stored' => $stored];
    }

    // ─── Products ───

    private function pollProducts(WordPressSite $site, array $credentials): array
    {
        $page = 1;
        $stored = 0;

        do {
            $response = $this->buildWcClient($site, $credentials)
                ->get("{$site->site_url}/wp-json/wc/v3/products", [
                    'per_page' => 100,
                    'page' => $page,
                    'orderby' => 'modified',
                    'order' => 'asc',
                ]);

            if (!$response->successful()) {
                throw new \Exception("WC Products API: " . $response->body());
            }

            $products = $response->json();
            if (empty($products)) break;

            $events = [];
            foreach ($products as $product) {
                $events[] = [
                    'site_id' => $site->id,
                    'event_type' => 'wc_product',
                    'wp_entity_id' => $product['id'],
                    'payload' => json_encode([
                        'product_id' => $product['id'],
                        'name' => $product['name'],
                        'slug' => $product['slug'],
                        'sku' => $product['sku'],
                        'type' => $product['type'], // simple, variable, grouped
                        'status' => $product['status'],
                        'featured' => $product['featured'],
                        'catalog_visibility' => $product['catalog_visibility'],
                        'price' => (float) $product['price'],
                        'regular_price' => (float) $product['regular_price'],
                        'sale_price' => $product['sale_price'] ? (float) $product['sale_price'] : null,
                        'on_sale' => $product['on_sale'],
                        'stock_status' => $product['stock_status'],
                        'stock_quantity' => $product['stock_quantity'],
                        'manage_stock' => $product['manage_stock'],
                        'backorders' => $product['backorders'],
                        'sold_individually' => $product['sold_individually'],
                        'weight' => $product['weight'],
                        'dimensions' => $product['dimensions'],
                        'categories' => array_column($product['categories'], 'name'),
                        'tags' => array_column($product['tags'], 'name'),
                        'images' => array_column($product['images'], 'src'),
                        'attributes' => array_map(fn($attr) => [
                            'name' => $attr['name'],
                            'options' => $attr['options'],
                        ], $product['attributes']),
                        'variations' => $product['variations'],
                        'rating_count' => $product['rating_count'],
                        'average_rating' => $product['average_rating'],
                        'total_sales' => $product['total_sales'],
                        'date_created' => $product['date_created'],
                        'date_modified' => $product['date_modified'],
                    ]),
                    'wp_created_at' => Carbon::parse($product['date_modified'])->toDateTimeString(),
                    'synced_at' => now(),
                ];
            }

            AnalyticsEvent::insert($events);
            $stored += count($events);
            $page++;

        } while (count($products) === 100);

        return ['stored' => $stored];
    }

    // ─── Customers ───

    private function pollCustomers(WordPressSite $site, array $credentials): array
    {
        $since = $site->last_sync_at ?? Carbon::now()->subDays(7);
        $page = 1;
        $stored = 0;

        do {
            $response = $this->buildWcClient($site, $credentials)
                ->get("{$site->site_url}/wp-json/wc/v3/customers", [
                    'per_page' => 100,
                    'page' => $page,
                    'role' => 'all',
                ]);

            if (!$response->successful()) {
                throw new \Exception("WC Customers API: " . $response->body());
            }

            $customers = $response->json();
            if (empty($customers)) break;

            $events = [];
            foreach ($customers as $customer) {
                $created = Carbon::parse($customer['date_created']);
                
                if ($created->greaterThan($since)) {
                    $events[] = [
                        'site_id' => $site->id,
                        'event_type' => 'wc_customer',
                        'wp_entity_id' => $customer['id'],
                        'payload' => json_encode([
                            'customer_id' => $customer['id'],
                            'email' => $customer['email'],
                            'first_name' => $customer['first_name'],
                            'last_name' => $customer['last_name'],
                            'role' => $customer['role'],
                            'username' => $customer['username'],
                            'billing' => $customer['billing'],
                            'shipping' => $customer['shipping'],
                            'is_paying_customer' => $customer['is_paying_customer'],
                            'orders_count' => $customer['orders_count'],
                            'total_spent' => (float) $customer['total_spent'],
                            'avatar_url' => $customer['avatar_url'],
                        ]),
                        'wp_created_at' => $created->toDateTimeString(),
                        'synced_at' => now(),
                    ];
                }
            }

            if (!empty($events)) {
                AnalyticsEvent::insert($events);
                $stored += count($events);
            }
            $page++;

        } while (count($customers) === 100);

        return ['stored' => $stored];
    }

    // ─── Coupons ───

    private function pollCoupons(WordPressSite $site, array $credentials): array
    {
        $response = $this->buildWcClient($site, $credentials)
            ->get("{$site->site_url}/wp-json/wc/v3/coupons", [
                'per_page' => 100,
            ]);

        if (!$response->successful()) {
            return ['stored' => 0, 'note' => 'Coupons endpoint unavailable'];
        }

        $coupons = $response->json();
        $events = [];

        foreach ($coupons as $coupon) {
            $events[] = [
                'site_id' => $site->id,
                'event_type' => 'wc_coupon',
                'wp_entity_id' => $coupon['id'],
                'payload' => json_encode([
                    'coupon_id' => $coupon['id'],
                    'code' => $coupon['code'],
                    'amount' => (float) $coupon['amount'],
                    'discount_type' => $coupon['discount_type'],
                    'usage_count' => $coupon['usage_count'],
                    'usage_limit' => $coupon['usage_limit'],
                    'usage_limit_per_user' => $coupon['usage_limit_per_user'],
                    'date_expires' => $coupon['date_expires'],
                    'free_shipping' => $coupon['free_shipping'],
                    'minimum_amount' => $coupon['minimum_amount'],
                    'maximum_amount' => $coupon['maximum_amount'],
                    'product_ids' => $coupon['product_ids'],
                    'excluded_product_ids' => $coupon['excluded_product_ids'],
                ]),
                'wp_created_at' => now()->toDateTimeString(),
                'synced_at' => now(),
            ];
        }

        if (!empty($events)) {
            AnalyticsEvent::insert($events);
        }

        return ['stored' => count($events)];
    }

    // ─── Reviews ───

    private function pollReviews(WordPressSite $site, array $credentials): array
    {
        $since = $site->last_sync_at ?? Carbon::now()->subDays(7);
        
        $response = $this->buildWcClient($site, $credentials)
            ->get("{$site->site_url}/wp-json/wc/v3/products/reviews", [
                'per_page' => 100,
                'after' => $since->toIso8601String(),
            ]);

        if (!$response->successful()) {
            return ['stored' => 0, 'note' => 'Reviews endpoint unavailable'];
        }

        $reviews = $response->json();
        $events = [];

        foreach ($reviews as $review) {
            $events[] = [
                'site_id' => $site->id,
                'event_type' => 'wc_review',
                'wp_entity_id' => $review['id'],
                'payload' => json_encode([
                    'review_id' => $review['id'],
                    'product_id' => $review['product_id'],
                    'product_name' => $review['product_name'],
                    'reviewer' => $review['reviewer'],
                    'reviewer_email' => $review['reviewer_email'],
                    'review' => $review['review'],
                    'rating' => $review['rating'],
                    'verified' => $review['verified'],
                    'status' => $review['status'],
                ]),
                'wp_created_at' => Carbon::parse($review['date_created'])->toDateTimeString(),
                'synced_at' => now(),
            ];
        }

        if (!empty($events)) {
            AnalyticsEvent::insert($events);
        }

        return ['stored' => count($events)];
    }
}