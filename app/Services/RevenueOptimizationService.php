<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\UpsellRecommendation;
use App\Models\RevenueForecast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class RevenueOptimizationService
{
    /**
     * Get current revenue metrics for a client
     */
    public function getCurrentRevenue($clientId)
    {
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');

        $currentRevenue = Transaction::where('client_id', $clientId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $lastMonthRevenue = Transaction::where('client_id', $clientId)
            ->where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<', now()->startOfMonth())
            ->sum('amount');

        $monthlyGrowth = $lastMonthRevenue > 0
            ? (($currentRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;

        return [
            'current_month' => $currentMonth,
            'current_revenue' => $currentRevenue,
            'last_month_revenue' => $lastMonthRevenue,
            'monthly_growth_percentage' => round($monthlyGrowth, 2),
            'average_order_value' => $this->calculateAverageOrderValue($clientId),
            'revenue_per_customer' => $this->calculateRevenuePerCustomer($clientId),
            'total_transactions' => Transaction::where('client_id', $clientId)->count(),
            'active_customers' => Customer::where('client_id', $clientId)->where('is_active', true)->count()
        ];
    }

    /**
     * Calculate Average Order Value
     */
    protected function calculateAverageOrderValue($clientId)
    {
        $totalRevenue = Transaction::where('client_id', $clientId)->sum('amount');
        $totalOrders = Transaction::where('client_id', $clientId)->count();

        return $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;
    }

    /**
     * Calculate Revenue Per Customer
     */
    protected function calculateRevenuePerCustomer($clientId)
    {
        $totalRevenue = Transaction::where('client_id', $clientId)->sum('amount');
        $activeCustomers = Customer::where('client_id', $clientId)
            ->where('is_active', true)
            ->count();

        return $activeCustomers > 0 ? round($totalRevenue / $activeCustomers, 2) : 0;
    }

    /**
     * Get 6-month revenue forecast with confidence intervals
     */
    public function getRevenueForecast($clientId)
    {
        $revenueData = Transaction::where('client_id', $clientId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('revenue', 'month')
            ->toArray();

        if (empty($revenueData)) {
            return $this->generateDefaultForecast();
        }

        $lastRevenue = end($revenueData);
        $growthRate = $this->calculateGrowthRate($revenueData);

        $forecasts = [];
        for ($i = 1; $i <= 6; $i++) {
            $month = Carbon::now()->addMonths($i)->format('Y-m');
            $forecast = $lastRevenue * pow(1 + $growthRate, $i);
            $confidence = $this->calculateConfidence($forecast, $revenueData);

            $forecasts[] = [
                'month' => $month,
                'forecasted_revenue' => round($forecast, 2),
                'confidence_level' => round($confidence, 2),
                'lower_bound' => round($forecast * 0.85, 2),
                'upper_bound' => round($forecast * 1.15, 2),
                'trend' => $growthRate > 0 ? 'increasing' : ($growthRate < 0 ? 'decreasing' : 'stable')
            ];
        }

        return $forecasts;
    }

    /**
     * Default forecast when no historical data exists
     */
    protected function generateDefaultForecast()
    {
        $forecasts = [];

        for ($i = 1; $i <= 6; $i++) {
            $month = Carbon::now()->addMonths($i)->format('Y-m');
            $forecasts[] = [
                'month' => $month,
                'forecasted_revenue' => 0,
                'confidence_level' => 50,
                'lower_bound' => 0,
                'upper_bound' => 0,
                'trend' => 'unknown'
            ];
        }

        return $forecasts;
    }

    /**
     * Calculate average growth rate from historical data
     */
    protected function calculateGrowthRate($revenueData)
    {
        $revenues = array_values($revenueData);

        if (count($revenues) < 2) return 0.05;

        $growthRates = [];
        for ($i = 1; $i < count($revenues); $i++) {
            if ($revenues[$i - 1] > 0) {
                $growth = ($revenues[$i] - $revenues[$i - 1]) / $revenues[$i - 1];
                $growthRates[] = $growth;
            }
        }

        return count($growthRates) > 0 ? array_sum($growthRates) / count($growthRates) : 0.05;
    }

    /**
     * Calculate confidence level for a forecast
     */
    protected function calculateConfidence($forecast, $historicalData)
    {
        $historicalRevenues = array_values($historicalData);
        if (empty($historicalRevenues)) return 50;

        $range = max($historicalRevenues) - min($historicalRevenues);
        $lastRevenue = end($historicalRevenues);

        if ($range == 0) {
            return $forecast >= $lastRevenue ? 80 : 40;
        }

        $distanceFromRange = abs($forecast - $lastRevenue);

        return min(100, max(30, 100 - ($distanceFromRange / $range * 50)));
    }

    /**
     * Calculate standard deviation
     */
    protected function calculateStandardDeviation($values)
    {
        $count = count($values);
        if ($count < 2) return 0;

        $mean = array_sum($values) / $count;
        $sum = 0;

        foreach ($values as $value) {
            $sum += pow($value - $mean, 2);
        }

        return sqrt($sum / $count);
    }

    /**
     * Get upsell opportunities for all active customers
     */
    public function getUpsellOpportunities($clientId)
    {
        $customers = Customer::where('client_id', $clientId)
            ->where('is_active', true)
            ->with(['transactions', 'recentEvents'])
            ->get();

        $opportunities = [];

        foreach ($customers as $customer) {
            $customerOpportunities = $this->generateCustomerUpsellOpportunities($customer);
            if (!empty($customerOpportunities)) {
                $opportunities[] = [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'email' => $customer->email,
                    'lifetime_value' => $customer->lifetime_value ?? 0,
                    'opportunities' => $customerOpportunities
                ];
            }
        }

        return $opportunities;
    }

    /**
     * Generate upsell opportunities for a specific customer
     */
    protected function generateCustomerUpsellOpportunities($customer)
    {
        $opportunities = [];
        $recentProducts = $customer->transactions()
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->pluck('product_id')
            ->toArray();

        $products = Product::where('client_id', $customer->client_id)
            ->where('is_active', true)
            ->whereNotIn('id', $recentProducts)
            ->get();

        foreach ($products as $product) {
            $compatibilityScore = $this->calculateProductCompatibility($product, $customer);
            if ($compatibilityScore > 0.6) {
                $opportunities[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'compatibility_score' => round($compatibilityScore, 2),
                    'strategy' => 'complementary',
                    'expected_revenue' => round($product->price * 2, 2)
                ];
            }
        }

        usort($opportunities, function ($a, $b) {
            return $b['compatibility_score'] <=> $a['compatibility_score'];
        });

        return array_slice($opportunities, 0, 3);
    }

    /**
     * Calculate product compatibility score for a customer
     */
    protected function calculateProductCompatibility($product, $customer)
    {
        $categoryMatch = 0;
        $priceRangeMatch = 0;
        $purchaseFrequencyMatch = 0;

        // Category match (50% weight)
        $customerCategories = $customer->transactions()
            ->with('product.category')
            ->get()
            ->pluck('product.category.name')
            ->filter()
            ->unique()
            ->toArray();

        if (in_array($product->category->name ?? 'uncategorized', $customerCategories)) {
            $categoryMatch = 0.5;
        }

        // Price range match (30% weight)
        $avgOrderValue = $customer->transactions()->avg('amount') ?? 0;
        $priceRatio = $avgOrderValue > 0 ? $product->price / $avgOrderValue : 1;

        if ($priceRatio >= 0.8 && $priceRatio <= 1.5) {
            $priceRangeMatch = 0.3;
        } elseif ($priceRatio < 0.5) {
            $priceRangeMatch = 0.2;
        } elseif ($priceRatio > 2) {
            $priceRangeMatch = 0.1;
        }

        // Purchase frequency match (20% weight)
        $purchaseCount = $customer->transactions()->count();
        if ($purchaseCount > 10) {
            $purchaseFrequencyMatch = 0.2;
        } elseif ($purchaseCount > 5) {
            $purchaseFrequencyMatch = 0.15;
        } else {
            $purchaseFrequencyMatch = 0.1;
        }

        return $categoryMatch + $priceRangeMatch + $purchaseFrequencyMatch;
    }

    /**
     * Generate a new upsell recommendation
     */
    public function generateUpsellRecommendation($customerId, $productId, $strategy, $confidenceScore)
    {
        $customer = Customer::findOrFail($customerId);
        $product = Product::findOrFail($productId);

        $recommendation = UpsellRecommendation::create([
            'client_id' => $customer->client_id,
            'customer_id' => $customerId,
            'product_id' => $productId,
            'original_product_id' => $this->getOriginalProductId($customerId),
            'strategy' => $strategy,
            'confidence_score' => $confidenceScore,
            'expected_revenue' => $this->calculateExpectedRevenue($customerId, $productId, $strategy),
            'message' => $this->generateRecommendationMessage($customer, $product, $strategy),
            'status' => 'pending'
        ]);

        return $recommendation;
    }

    /**
     * Get the most recent product purchased by customer
     */
    protected function getOriginalProductId($customerId)
    {
        $lastTransaction = Transaction::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastTransaction ? $lastTransaction->product_id : null;
    }

    /**
     * Calculate expected revenue for an upsell
     */
    protected function calculateExpectedRevenue($customerId, $productId, $strategy)
    {
        $product = Product::find($productId);
        if (!$product) return 0;

        $basePrice = $product->price;
        $multiplier = 1;

        switch ($strategy) {
            case 'upgrade':
                $multiplier = 1.3;
                break;
            case 'bundle':
                $multiplier = 0.9;
                break;
            case 'accessory':
                $multiplier = 0.4;
                break;
            case 'complementary':
                $multiplier = 0.7;
                break;
        }

        return round($basePrice * $multiplier, 2);
    }

    /**
     * Generate personalized recommendation message
     */
    protected function generateRecommendationMessage($customer, $product, $strategy)
    {
        $messages = [
            'upgrade' => "Upgrade your plan to {$product->name} for enhanced features!",
            'bundle' => "Bundle deal: Get {$product->name} with your current purchase!",
            'accessory' => "Complete your setup with {$product->name}!",
            'complementary' => "Customers who bought this also purchased {$product->name}!"
        ];

        return $messages[$strategy] ?? "Check out our special offer on {$product->name}!";
    }

    /**
     * Execute an upsell recommendation (simulate or process)
     */
    public function executeUpsellRecommendation($recommendation)
    {
        $customer = $recommendation->customer;
        $product = $recommendation->product;

        return [
            'success' => true,
            'recommendation_id' => $recommendation->id,
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'strategy' => $recommendation->strategy,
            'simulated_revenue' => $recommendation->expected_revenue,
            'timestamp' => now()
        ];
    }

    /**
     * Analyze pricing for all products
     */
    public function analyzePricing($products)
    {
        $analysis = [];

        foreach ($products as $product) {
            $analysis[$product->id] = [
                'product_name' => $product->name,
                'current_price' => $product->price,
                'category' => $product->category->name ?? 'Uncategorized',
                'competitor_prices' => $this->getCompetitorPrices($product),
                'price_elasticity' => $this->calculatePriceElasticity($product),
                'optimal_price_range' => $this->calculateOptimalPriceRange($product),
                'revenue_impact' => $this->calculateRevenueImpact($product),
                'recommendation' => $this->generatePricingRecommendation($product)
            ];
        }

        return $analysis;
    }

    /**
     * Get mock competitor prices by category
     */
    protected function getCompetitorPrices($product)
    {
        $categoryPrices = [
            'software' => [49.99, 59.99, 69.99, 79.99],
            'ecommerce' => [19.99, 24.99, 29.99, 34.99],
            'consulting' => [99.00, 149.00, 199.00, 249.00],
            'default' => [29.99, 39.99, 49.99, 59.99]
        ];

        $category = $product->category->name ?? 'default';
        return $categoryPrices[$category] ?? $categoryPrices['default'];
    }

    /**
     * Calculate price elasticity of demand
     */
    protected function calculatePriceElasticity($product)
    {
        $salesData = Transaction::where('product_id', $product->id)
            ->selectRaw('price, COUNT(*) as count')
            ->groupBy('price')
            ->orderBy('price')
            ->get();

        if ($salesData->count() < 2) return 1.0;

        $prices = $salesData->pluck('price')->toArray();
        $quantities = $salesData->pluck('count')->toArray();

        $priceChanges = [];
        $quantityChanges = [];

        for ($i = 1; $i < count($prices); $i++) {
            $priceChange = (($prices[$i] - $prices[$i - 1]) / $prices[$i - 1]) * 100;
            $quantityChange = (($quantities[$i] - $quantities[$i - 1]) / $quantities[$i - 1]) * 100;

            $priceChanges[] = $priceChange;
            $quantityChanges[] = $quantityChange;
        }

        $elasticity = 0;
        $count = 0;

        for ($i = 0; $i < count($priceChanges); $i++) {
            if ($priceChanges[$i] != 0) {
                $elasticity += abs($quantityChanges[$i] / $priceChanges[$i]);
                $count++;
            }
        }

        return $count > 0 ? $elasticity / $count : 1.0;
    }

    /**
     * Calculate optimal price range based on elasticity
     */
    protected function calculateOptimalPriceRange($product)
    {
        $elasticity = $this->calculatePriceElasticity($product);
        $currentPrice = $product->price;

        if ($elasticity > 1.5) {
            $lowerBound = $currentPrice * 0.8;
            $upperBound = $currentPrice * 0.95;
            $recommendedPrice = $this->roundToNearest($currentPrice * 0.9, 5);
        } elseif ($elasticity < 0.7) {
            $lowerBound = $currentPrice * 1.05;
            $upperBound = $currentPrice * 1.2;
            $recommendedPrice = $this->roundToNearest($currentPrice * 1.1, 5);
        } else {
            $lowerBound = $currentPrice * 0.9;
            $upperBound = $currentPrice * 1.1;
            $recommendedPrice = $this->roundToNearest($currentPrice * 1.05, 5);
        }

        return [
            'lower_bound' => round($lowerBound, 2),
            'upper_bound' => round($upperBound, 2),
            'current_price' => $currentPrice,
            'recommended_price' => $recommendedPrice
        ];
    }

    /**
     * Calculate revenue impact of price change
     */
    protected function calculateRevenueImpact($product)
    {
        $optimalRange = $this->calculateOptimalPriceRange($product);
        $currentRevenue = $this->calculateProductRevenue($product->id);
        $optimalRevenue = $this->calculateProductRevenueAtPrice($product->id, $optimalRange['recommended_price']);

        $potentialIncrease = $optimalRevenue - $currentRevenue;
        $percentageIncrease = $currentRevenue > 0 ? ($potentialIncrease / $currentRevenue) * 100 : 0;

        return [
            'current_revenue' => round($currentRevenue, 2),
            'optimal_revenue' => round($optimalRevenue, 2),
            'potential_increase' => round($potentialIncrease, 2),
            'percentage_increase' => round($percentageIncrease, 2)
        ];
    }

    /**
     * Calculate total revenue for a product
     */
    protected function calculateProductRevenue($productId)
    {
        return Transaction::where('product_id', $productId)->sum('amount');
    }

    /**
     * Estimate revenue at a different price point
     */
    protected function calculateProductRevenueAtPrice($productId, $price)
    {
        $product = Product::find($productId);
        if (!$product || $product->price == 0) return 0;

        $salesCount = Transaction::where('product_id', $productId)->count();
        $elasticity = $this->calculatePriceElasticity($product);

        $priceChange = ($price - $product->price) / $product->price;
        $adjustedSales = $salesCount * (1 - ($elasticity * $priceChange));

        return max(0, $price * $adjustedSales);
    }

    /**
     * Generate pricing recommendation for a product
     */
    protected function generatePricingRecommendation($product)
    {
        $optimalRange = $this->calculateOptimalPriceRange($product);
        $revenueImpact = $this->calculateRevenueImpact($product);
        $recommendations = [];

        if ($revenueImpact['potential_increase'] > 1000) {
            $recommendations[] = [
                'action' => 'price_increase',
                'new_price' => $optimalRange['recommended_price'],
                'expected_increase' => $revenueImpact['potential_increase'],
                'percentage_increase' => $revenueImpact['percentage_increase']
            ];
        }

        if ($revenueImpact['percentage_increase'] < -5) {
            $recommendations[] = [
                'action' => 'price_decrease',
                'new_price' => $optimalRange['recommended_price'],
                'reason' => 'Current price is too high, reducing demand'
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'action' => 'maintain',
                'current_price' => $optimalRange['current_price'],
                'reason' => 'Price is within optimal range'
            ];
        }

        return $recommendations;
    }

    /**
     * Get current forecast summary for dashboard
     */
    public function getCurrentForecast($clientId)
    {
        $forecasts = $this->getRevenueForecast($clientId);
        $nextMonth = $forecasts[0] ?? null;

        return [
            'next_month_forecast' => $nextMonth['forecasted_revenue'] ?? 0,
            'confidence_level' => $nextMonth['confidence_level'] ?? 50,
            'trend' => $nextMonth['trend'] ?? 'unknown',
            'forecast_periods' => $forecasts
        ];
    }

    /**
     * Get price sensitivity analysis for dashboard
     */
    public function getPriceSensitivityAnalysis($clientId)
    {
        $products = Product::where('client_id', $clientId)
            ->where('is_active', true)
            ->get();

        $analysis = [];
        foreach ($products as $product) {
            $elasticity = $this->calculatePriceElasticity($product);
            $optimalRange = $this->calculateOptimalPriceRange($product);

            $analysis[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_price' => $product->price,
                'elasticity' => round($elasticity, 2),
                'elasticity_label' => $elasticity > 1.5 ? 'elastic' : ($elasticity < 0.7 ? 'inelastic' : 'unit'),
                'optimal_price' => $optimalRange['recommended_price'],
                'price_difference' => round($optimalRange['recommended_price'] - $product->price, 2)
            ];
        }

        return $analysis;
    }

    /**
     * Round a value to the nearest multiple
     */
    protected function roundToNearest($value, $nearest)
    {
        return round($value / $nearest) * $nearest;
    }
}
