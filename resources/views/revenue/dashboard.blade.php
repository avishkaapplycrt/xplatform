@extends('layouts.platform')

@section('title', 'Revenue Optimization')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  {{-- HEADER --}}
  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Revenue Optimization</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">AI-powered revenue intelligence & forecasting</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('client.revenue.pricing') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-[11px] font-medium text-gray-700 hover:bg-gray-50 transition">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        Pricing
      </a>
      <a href="{{ route('client.revenue.upsell') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-[11px] font-medium text-gray-700 hover:bg-gray-50 transition">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        Upsells
      </a>
      <a href="{{ route('client.revenue.forecast') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-[11px] font-medium text-gray-700 hover:bg-gray-50 transition">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Forecast
      </a>
    </div>
  </header>

  {{-- BODY --}}
  <div class="flex-1 overflow-y-auto px-5 py-5">

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      {{-- Current Month --}}
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
          <div>
            <p class="text-[11px] text-gray-500 font-medium">Current Month</p>
            <h3 class="text-[20px] font-bold text-gray-900">${{ number_format($currentRevenue['current_revenue'] ?? 0, 2) }}</h3>
          </div>
          <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
        </div>
        <p class="text-[11px] text-gray-400">{{ $currentRevenue['current_month'] ?? now()->format('Y-m') }}</p>
      </div>

      {{-- Monthly Growth --}}
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
          <div>
            <p class="text-[11px] text-gray-500 font-medium">Monthly Growth</p>
            <h3 class="text-[20px] font-bold {{ ($currentRevenue['monthly_growth_percentage'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
              {{ ($currentRevenue['monthly_growth_percentage'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($currentRevenue['monthly_growth_percentage'] ?? 0, 1) }}%
            </h3>
          </div>
          <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
          </div>
        </div>
        <p class="text-[11px] text-gray-400">vs last month</p>
      </div>

      {{-- Avg Order Value --}}
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
          <div>
            <p class="text-[11px] text-gray-500 font-medium">Avg Order Value</p>
            <h3 class="text-[20px] font-bold text-gray-900">${{ number_format($currentRevenue['average_order_value'] ?? 0, 2) }}</h3>
          </div>
          <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
        </div>
        <p class="text-[11px] text-gray-400">Per transaction</p>
      </div>

      {{-- Active Customers --}}
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
          <div>
            <p class="text-[11px] text-gray-500 font-medium">Active Customers</p>
            <h3 class="text-[20px] font-bold text-gray-900">{{ number_format($currentRevenue['active_customers'] ?? 0) }}</h3>
          </div>
          <div class="w-10 h-10 rounded-lg bg-cyan-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
        </div>
        <p class="text-[11px] text-gray-400">Rev/customer: ${{ number_format($currentRevenue['revenue_per_customer'] ?? 0, 2) }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      {{-- 6-Month Forecast --}}
      <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h6 class="text-[13px] font-semibold text-gray-800">6-Month Revenue Forecast</h6>
          <p class="text-[11px] text-gray-500 mt-0.5">Based on historical transaction data</p>
        </div>
        <div class="p-4">
          @if(!empty($forecast) && isset($forecast[0]['forecasted_revenue']) && $forecast[0]['forecasted_revenue'] > 0)
            <div class="overflow-x-auto">
              <table class="w-full text-[11px]">
                <thead>
                  <tr class="text-left text-gray-500 border-b border-gray-100">
                    <th class="pb-2 font-medium">Month</th>
                    <th class="pb-2 font-medium">Forecast</th>
                    <th class="pb-2 font-medium">Confidence</th>
                    <th class="pb-2 font-medium">Range</th>
                    <th class="pb-2 font-medium">Trend</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  @foreach($forecast as $f)
                  <tr>
                    <td class="py-2.5 font-medium text-gray-900">{{ $f['month'] }}</td>
                    <td class="py-2.5 font-bold text-blue-600">${{ number_format($f['forecasted_revenue'], 2) }}</td>
                    <td class="py-2.5">
                      <div class="flex items-center gap-2">
                        <div class="w-16 bg-gray-100 rounded-full h-1.5">
                          <div class="h-1.5 rounded-full {{ $f['confidence_level'] >= 70 ? 'bg-green-500' : ($f['confidence_level'] >= 40 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $f['confidence_level'] }}%"></div>
                        </div>
                        <span class="text-gray-500">{{ $f['confidence_level'] }}%</span>
                      </div>
                    </td>
                    <td class="py-2.5 text-gray-500">${{ number_format($f['lower_bound'], 0) }} - ${{ number_format($f['upper_bound'], 0) }}</td>
                    <td class="py-2.5">
                      @if($f['trend'] === 'increasing')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>Up</span>
                      @elseif($f['trend'] === 'decreasing')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>Down</span>
                      @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>Stable</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center py-8">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              </div>
              <p class="text-[12px] text-gray-500">No transaction data available for forecasting.</p>
              <p class="text-[11px] text-gray-400 mt-1">Add transactions to see revenue predictions.</p>
            </div>
          @endif
        </div>
      </div>

      {{-- Upsell Opportunities --}}
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h6 class="text-[13px] font-semibold text-gray-800">Top Upsell Opportunities</h6>
          <p class="text-[11px] text-gray-500 mt-0.5">AI-powered recommendations</p>
        </div>
        <div class="p-4">
          @if(!empty($upsellOpportunities))
            @foreach(array_slice($upsellOpportunities, 0, 5) as $opportunity)
            <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-50 last:border-0 last:mb-0 last:pb-0">
              <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                <span class="text-[10px] font-bold text-green-600">{{ substr($opportunity['customer_name'] ?? 'U', 0, 1) }}</span>
              </div>
              <div class="flex-grow min-w-0">
                <p class="text-[12px] font-medium text-gray-900 truncate">{{ $opportunity['customer_name'] ?? 'Customer' }}</p>
                <p class="text-[10px] text-gray-500">{{ count($opportunity['opportunities'] ?? []) }} opportunity(s)</p>
              </div>
              <span class="flex-shrink-0 px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700">{{ $opportunity['opportunities'][0]['compatibility_score'] ?? 0 }}% match</span>
            </div>
            @endforeach
            <a href="{{ route('client.revenue.upsell') }}" class="block text-center mt-3 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[11px] font-medium text-gray-700 hover:bg-gray-100 transition">View All Opportunities</a>
          @else
            <div class="text-center py-6">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
              </div>
              <p class="text-[12px] text-gray-500">No upsell opportunities yet.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Price Sensitivity --}}
    @if(!empty($priceSensitivity))
    <div class="mt-4 bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100">
        <h6 class="text-[13px] font-semibold text-gray-800">Price Sensitivity Analysis</h6>
        <p class="text-[11px] text-gray-500 mt-0.5">Identify optimal pricing points</p>
      </div>
      <div class="p-4">
        <div class="overflow-x-auto">
          <table class="w-full text-[11px]">
            <thead>
              <tr class="text-left text-gray-500 border-b border-gray-100">
                <th class="pb-2 font-medium">Product</th>
                <th class="pb-2 font-medium">Current</th>
                <th class="pb-2 font-medium">Elasticity</th>
                <th class="pb-2 font-medium">Optimal</th>
                <th class="pb-2 font-medium">Impact</th>
                <th class="pb-2 font-medium">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              @foreach($priceSensitivity as $item)
              <tr>
                <td class="py-2.5 font-medium text-gray-900">{{ $item['product_name'] }}</td>
                <td class="py-2.5">${{ number_format($item['current_price'], 2) }}</td>
                <td class="py-2.5">
                  @if($item['elasticity_label'] === 'elastic')
                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">Elastic ({{ $item['elasticity'] }})</span>
                  @elseif($item['elasticity_label'] === 'inelastic')
                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700">Inelastic ({{ $item['elasticity'] }})</span>
                  @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">Unit ({{ $item['elasticity'] }})</span>
                  @endif
                </td>
                <td class="py-2.5 font-bold text-blue-600">${{ number_format($item['optimal_price'], 2) }}</td>
                <td class="py-2.5">
                  @if($item['price_difference'] > 0)
                    <span class="text-green-600">+${{ number_format($item['price_difference'], 2) }}</span>
                  @else
                    <span class="text-red-600">${{ number_format($item['price_difference'], 2) }}</span>
                  @endif
                </td>
                <td class="py-2.5">
                  @if($item['price_difference'] > 0)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>Increase</span>
                  @elseif($item['price_difference'] < -0.5)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>Decrease</span>
                  @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">Maintain</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif

  </div>
</div>
@endsection
