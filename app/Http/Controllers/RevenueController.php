<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Customer;
use App\Models\Product;
use App\Models\RevenueForecast;
use App\Models\UpsellRecommendation;
use App\Services\RevenueOptimizationService;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    protected $revenueService;

    public function __construct(RevenueOptimizationService $revenueService)
    {
        $this->revenueService = $revenueService;
    }

    /**
     * Revenue dashboard with all key metrics
     */
    public function dashboard(Request $request)
    {
        $client = $request->user();

        $currentRevenue = $this->revenueService->getCurrentRevenue($client->id);
        $forecast = $this->revenueService->getRevenueForecast($client->id);
        $upsellOpportunities = $this->revenueService->getUpsellOpportunities($client->id);
        $priceSensitivity = $this->revenueService->getPriceSensitivityAnalysis($client->id);

        return view('revenue.dashboard', [
            'currentRevenue' => $currentRevenue,
            'forecast' => $forecast,
            'upsellOpportunities' => $upsellOpportunities,
            'priceSensitivity' => $priceSensitivity,
            'client' => $client // FIXED: pass single client object instead of with('users')
        ]);
    }

    /**
     * Pricing intelligence page
     */
    public function pricingIntelligence(Request $request)
    {
        $client = $request->user();
        $products = Product::where('client_id', $client->id)->with('category')->get();

        $pricingAnalysis = $this->revenueService->analyzePricing($products);

        return view('revenue.pricing', [
            'products' => $products,
            'pricingAnalysis' => $pricingAnalysis
        ]);
    }

    /**
     * Upsell recommendations listing
     */
    public function upsellRecommendations(Request $request)
    {
        $client = $request->user();
        $customers = Customer::where('client_id', $client->id)
            ->where('is_active', true)
            ->with(['transactions', 'recentEvents'])
            ->paginate(20);

        $recommendations = UpsellRecommendation::where('client_id', $client->id)
            ->with(['customer', 'product', 'originalProduct'])
            ->latest()
            ->paginate(20);

        return view('revenue.upsell', [
            'customers' => $customers,
            'recommendations' => $recommendations
        ]);
    }

    /**
     * Generate a new upsell recommendation via API
     */
    public function generateUpsell(Request $request, $customerId)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'strategy' => 'required|in:complementary,upgrade,accessory,bundle',
            'confidence_score' => 'required|integer|min:1|max:100'
        ]);

        $recommendation = $this->revenueService->generateUpsellRecommendation(
            $customerId,
            $validated['product_id'],
            $validated['strategy'],
            $validated['confidence_score']
        );

        return response()->json([
            'success' => true,
            'recommendation' => $recommendation
        ]);
    }

    /**
     * Execute an upsell recommendation
     */
    public function executeUpsell($recommendationId)
    {
        $recommendation = UpsellRecommendation::findOrFail($recommendationId);
        $result = $this->revenueService->executeUpsellRecommendation($recommendation);

        $recommendation->update(['status' => 'executed', 'executed_at' => now()]);

        return response()->json([
            'success' => true,
            'result' => $result
        ]);
    }

    /**
     * Revenue forecast page
     */
    public function revenueForecast(Request $request)
    {
        $client = $request->user();
        $forecasts = RevenueForecast::where('client_id', $client->id)
            ->orderBy('forecast_date', 'desc')
            ->paginate(10);

        $currentForecast = $this->revenueService->getCurrentForecast($client->id);

        return view('revenue.forecast', [
            'forecasts' => $forecasts,
            'currentForecast' => $currentForecast
        ]);
    }

    /**
     * Create a manual revenue forecast
     */
    public function createManualForecast(Request $request)
    {
        $validated = $request->validate([
            'forecast_date' => 'required|date|after_or_equal:today',
            'expected_revenue' => 'required|numeric|min:0',
            'confidence_level' => 'required|integer|min:1|max:100',
            'notes' => 'nullable|string'
        ]);

        $forecast = RevenueForecast::create([
            'client_id' => $request->user()->id,
            'forecast_date' => $validated['forecast_date'],
            'expected_revenue' => $validated['expected_revenue'],
            'confidence_level' => $validated['confidence_level'],
            'notes' => $validated['notes'] ?? ''
        ]);

        return redirect()->back()->with('success', 'Revenue forecast created successfully!');
    }
}
