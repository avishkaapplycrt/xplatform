@extends('layouts.platform')

@section('title', 'Revenue Forecast')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Revenue Forecast</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">Predictive revenue analytics & planning</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('client.revenue.dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-[11px] font-medium text-gray-700 hover:bg-gray-50 transition">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Dashboard
      </a>
    </div>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <!-- Current Forecast Summary -->
    @if(!empty($currentForecast))
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[11px] text-gray-500 font-medium mb-1">Next Month Forecast</p>
        <h3 class="text-[24px] font-bold text-blue-600">${{ number_format($currentForecast['next_month_forecast'] ?? 0, 0) }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[11px] text-gray-500 font-medium mb-1">Confidence Level</p>
        <div class="flex items-center gap-2">
          <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="h-2 rounded-full {{ ($currentForecast['confidence_level'] ?? 50) >= 70 ? 'bg-green-500' : 'bg-amber-500' }}" style="width: {{ $currentForecast['confidence_level'] ?? 50 }}%"></div>
          </div>
          <span class="text-[13px] font-bold text-gray-900">{{ $currentForecast['confidence_level'] ?? 50 }}%</span>
        </div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[11px] text-gray-500 font-medium mb-1">Trend</p>
        @if(($currentForecast['trend'] ?? 'unknown') === 'increasing')
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>Increasing</span>
        @elseif(($currentForecast['trend'] ?? 'unknown') === 'decreasing')
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>Decreasing</span>
        @else
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>Stable</span>
        @endif
      </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Forecast Table -->
      <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <h5 class="text-[13px] font-semibold text-gray-800">Forecast Periods</h5>
          <span class="text-[10px] text-gray-400">Auto-generated from transaction history</span>
        </div>
        <div class="p-4">
          @if(!empty($currentForecast['forecast_periods']))
            <div class="overflow-x-auto">
              <table class="w-full text-[11px]">
                <thead>
                  <tr class="text-left text-gray-500 border-b border-gray-100">
                    <th class="pb-2 font-medium">Month</th>
                    <th class="pb-2 font-medium">Forecasted</th>
                    <th class="pb-2 font-medium">Confidence</th>
                    <th class="pb-2 font-medium">Lower</th>
                    <th class="pb-2 font-medium">Upper</th>
                    <th class="pb-2 font-medium">Trend</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  @foreach($currentForecast['forecast_periods'] as $period)
                  <tr>
                    <td class="py-2.5 font-medium text-gray-900">{{ $period['month'] }}</td>
                    <td class="py-2.5 font-bold text-blue-600">${{ number_format($period['forecasted_revenue'], 2) }}</td>
                    <td class="py-2.5">
                      <div class="flex items-center gap-2">
                        <div class="w-12 bg-gray-100 rounded-full h-1.5">
                          <div class="h-1.5 rounded-full {{ $period['confidence_level'] >= 70 ? 'bg-green-500' : ($period['confidence_level'] >= 40 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $period['confidence_level'] }}%"></div>
                        </div>
                        <span class="text-gray-500">{{ $period['confidence_level'] }}%</span>
                      </div>
                    </td>
                    <td class="py-2.5 text-gray-500">${{ number_format($period['lower_bound'], 0) }}</td>
                    <td class="py-2.5 text-gray-500">${{ number_format($period['upper_bound'], 0) }}</td>
                    <td class="py-2.5">
                      @if($period['trend'] === 'increasing')
                        <span class="inline-flex items-center gap-1 text-green-600"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>Up</span>
                      @elseif($period['trend'] === 'decreasing')
                        <span class="inline-flex items-center gap-1 text-red-600"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>Down</span>
                      @else
                        <span class="text-gray-500">Stable</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center py-8">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
              <p class="text-[12px] text-gray-500">No forecast data available.</p>
            </div>
          @endif
        </div>
      </div>

      <!-- Manual Forecasts -->
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h5 class="text-[13px] font-semibold text-gray-800">Manual Forecasts</h5>
        </div>
        <div class="p-4">
          @if(!empty($forecasts) && $forecasts->count() > 0)
            @foreach($forecasts as $forecast)
            <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-50 last:border-0">
              <div>
                <p class="text-[12px] font-medium text-gray-900">{{ $forecast->forecast_date->format('M Y') }}</p>
                <p class="text-[10px] text-gray-500">Confidence: {{ $forecast->confidence_level }}%</p>
              </div>
              <span class="font-bold text-blue-600">${{ number_format($forecast->expected_revenue, 0) }}</span>
            </div>
            @endforeach
            {{ $forecasts->links() }}
          @else
            <div class="text-center py-6">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></div>
              <p class="text-[12px] text-gray-500">No manual forecasts.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
