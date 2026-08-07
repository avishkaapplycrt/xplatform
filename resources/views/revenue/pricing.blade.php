@extends('layouts.platform')

@section('title', 'Pricing Intelligence')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Pricing Intelligence</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">Elasticity analysis & optimal pricing</p>
    </div>
    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-[11px] font-medium text-gray-700 hover:bg-gray-50 transition" onclick="refreshAnalysis()">
      <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
      Refresh Analysis
    </button>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center mx-auto mb-2"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
        <h3 class="text-[20px] font-bold text-green-600">{{ $priceIncreaseCount ?? 0 }}</h3>
        <p class="text-[11px] text-green-600">Price Increase Recommended</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center mx-auto mb-2"><svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg></div>
        <h3 class="text-[20px] font-bold text-red-600">{{ $priceDecreaseCount ?? 0 }}</h3>
        <p class="text-[11px] text-red-600">Price Decrease Recommended</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center mx-auto mb-2"><svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg></div>
        <h3 class="text-[20px] font-bold text-gray-600">{{ $maintainCount ?? 0 }}</h3>
        <p class="text-[11px] text-gray-600">Maintain Current Price</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-cyan-50 flex items-center justify-center mx-auto mb-2"><svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <h3 class="text-[20px] font-bold text-cyan-600">${{ number_format($totalPotentialIncrease ?? 0, 0) }}</h3>
        <p class="text-[11px] text-cyan-600">Total Potential Revenue</p>
      </div>
    </div>

    <!-- Pricing Analysis Table -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-5">
      <div class="px-4 py-3 border-b border-gray-100">
        <h5 class="text-[13px] font-semibold text-gray-800">Product Pricing Analysis</h5>
        <p class="text-[11px] text-gray-500 mt-0.5">Elasticity analysis and optimal pricing recommendations</p>
      </div>
      <div class="p-4">
        @if(!empty($pricingAnalysis) && count($pricingAnalysis) > 0)
          <div class="overflow-x-auto">
            <table class="w-full text-[11px]">
              <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100">
                  <th class="pb-2 font-medium">Product</th>
                  <th class="pb-2 font-medium">Category</th>
                  <th class="pb-2 font-medium">Current</th>
                  <th class="pb-2 font-medium">Elasticity</th>
                  <th class="pb-2 font-medium">Optimal</th>
                  <th class="pb-2 font-medium">Impact</th>
                  <th class="pb-2 font-medium">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                @foreach($pricingAnalysis as $productId => $analysis)
                <tr>
                  <td class="py-2.5 font-medium text-gray-900">{{ $analysis['product_name'] }}</td>
                  <td class="py-2.5"><span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-50 text-gray-600">{{ $analysis['category'] }}</span></td>
                  <td class="py-2.5">${{ number_format($analysis['current_price'], 2) }}</td>
                  <td class="py-2.5">
                    @if($analysis['price_elasticity'] > 1.5)
                      <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">Elastic ({{ number_format($analysis['price_elasticity'], 2) }})</span>
                    @elseif($analysis['price_elasticity'] < 0.7)
                      <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700">Inelastic ({{ number_format($analysis['price_elasticity'], 2) }})</span>
                    @else
                      <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">Unit ({{ number_format($analysis['price_elasticity'], 2) }})</span>
                    @endif
                  </td>
                  <td class="py-2.5 font-bold text-blue-600">${{ number_format($analysis['optimal_price_range']['recommended_price'], 2) }}</td>
                  <td class="py-2.5">
                    @if($analysis['revenue_impact']['potential_increase'] > 0)
                      <span class="text-green-600 font-medium">+${{ number_format($analysis['revenue_impact']['potential_increase'], 0) }} <span class="text-gray-400">({{ $analysis['revenue_impact']['percentage_increase'] }}%)</span></span>
                    @else
                      <span class="text-red-600">${{ number_format($analysis['revenue_impact']['potential_increase'], 0) }}</span>
                    @endif
                  </td>
                  <td class="py-2.5">
                    @if(!empty($analysis['recommendation']))
                      @php $rec = $analysis['recommendation'][0]; @endphp
                      @if($rec['action'] === 'price_increase')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>Increase</span>
                      @elseif($rec['action'] === 'price_decrease')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>Decrease</span>
                      @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">Maintain</span>
                      @endif
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-8">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg></div>
            <h5 class="text-[13px] font-medium text-gray-700">No products to analyze.</h5>
            <p class="text-[11px] text-gray-500">Add products to see pricing intelligence.</p>
          </div>
        @endif
      </div>
    </div>

    <!-- Competitor Prices -->
    @if(!empty($pricingAnalysis))
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100">
        <h5 class="text-[13px] font-semibold text-gray-800">Competitor Price Comparison</h5>
      </div>
      <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          @foreach($pricingAnalysis as $productId => $analysis)
          <div class="p-3 bg-gray-50 rounded-lg">
            <h6 class="text-[12px] font-semibold text-gray-900 mb-2">{{ $analysis['product_name'] }}</h6>
            <div class="flex justify-between text-[11px] mb-1">
              <span class="text-gray-500">Your Price:</span>
              <span class="font-bold text-gray-900">${{ number_format($analysis['current_price'], 2) }}</span>
            </div>
            <div class="flex justify-between text-[11px]">
              <span class="text-gray-500">Competitors:</span>
              <div class="flex gap-1">
                @foreach($analysis['competitor_prices'] as $price)
                <span class="px-1.5 py-0.5 rounded bg-white text-gray-600 text-[10px]">${{ $price }}</span>
                @endforeach
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif

  </div>
</div>
@endsection

@section('scripts')
<script>
function refreshAnalysis() {
    location.reload();
}
</script>
@endsection
