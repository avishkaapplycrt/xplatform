@extends('layouts.platform')

@section('title', 'Customer Health Scores')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Customer Health Scores</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">Monitor customer engagement & risk levels</p>
    </div>
    <form action="{{ route('client.success.health.recalculate') }}" method="POST" class="d-inline">
      @csrf
      <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white rounded-lg text-[11px] font-medium hover:bg-green-700 transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Recalculate All
      </button>
    </form>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <!-- Distribution Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-2"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <h2 class="text-[24px] font-bold text-green-600">{{ $distribution['healthy']['count'] ?? 0 }}</h2>
        <p class="text-[12px] text-green-600 font-medium">Healthy (70-100)</p>
        <p class="text-[11px] text-gray-400">{{ $distribution['healthy']['percentage'] ?? 0 }}%</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-2"><svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <h2 class="text-[24px] font-bold text-amber-600">{{ $distribution['at_risk']['count'] ?? 0 }}</h2>
        <p class="text-[12px] text-amber-600 font-medium">At Risk (40-69)</p>
        <p class="text-[11px] text-gray-400">{{ $distribution['at_risk']['percentage'] ?? 0 }}%</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-2"><svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <h2 class="text-[24px] font-bold text-red-600">{{ $distribution['critical']['count'] ?? 0 }}</h2>
        <p class="text-[12px] text-red-600 font-medium">Critical (0-39)</p>
        <p class="text-[11px] text-gray-400">{{ $distribution['critical']['percentage'] ?? 0 }}%</p>
      </div>
    </div>

    @php
      $totalDist = ($distribution['healthy']['count'] ?? 0) + ($distribution['at_risk']['count'] ?? 0) + ($distribution['critical']['count'] ?? 0);
      $healthyPct = $totalDist > 0 ? ($distribution['healthy']['count'] / $totalDist) * 100 : 0;
      $atRiskPct = $totalDist > 0 ? ($distribution['at_risk']['count'] / $totalDist) * 100 : 0;
      $criticalPct = $totalDist > 0 ? ($distribution['critical']['count'] / $totalDist) * 100 : 0;
    @endphp
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-5">
      <h6 class="text-[12px] font-semibold text-gray-800 mb-3">Distribution</h6>
      <div class="flex h-3 rounded-full overflow-hidden">
        <div class="bg-green-500" style="width: {{ $healthyPct }}%"></div>
        <div class="bg-amber-500" style="width: {{ $atRiskPct }}%"></div>
        <div class="bg-red-500" style="width: {{ $criticalPct }}%"></div>
      </div>
      <div class="flex gap-4 mt-2 text-[10px] text-gray-500">
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span>Healthy {{ round($healthyPct) }}%</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span>At Risk {{ round($atRiskPct) }}%</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span>Critical {{ round($criticalPct) }}%</span>
      </div>
    </div>

    <!-- Customer Health Table -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h5 class="text-[13px] font-semibold text-gray-800">Customer Health Details</h5>
        <div class="relative">
          <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input type="text" class="pl-8 pr-3 py-1.5 border border-gray-200 rounded-lg text-[11px] w-52 focus:outline-none focus:border-blue-500" placeholder="Search customers...">
        </div>
      </div>
      <div class="p-4">
        @if(!empty($scores) && $scores->count() > 0)
          <div class="overflow-x-auto">
            <table class="w-full text-[11px]">
              <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100">
                  <th class="pb-2 font-medium">Customer</th>
                  <th class="pb-2 font-medium">Overall</th>
                  <th class="pb-2 font-medium">Engage</th>
                  <th class="pb-2 font-medium">Trans</th>
                  <th class="pb-2 font-medium">Support</th>
                  <th class="pb-2 font-medium">NPS</th>
                  <th class="pb-2 font-medium">Status</th>
                  <th class="pb-2 font-medium">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                @foreach($scores as $score)
                <tr>
                  <td class="py-2.5">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0"><span class="text-[10px] font-bold text-blue-600">{{ substr($score->customer->name ?? 'U', 0, 1) }}</span></div>
                      <div>
                        <p class="font-medium text-gray-900">{{ $score->customer->name ?? 'Unknown' }}</p>
                        <p class="text-[10px] text-gray-400">{{ $score->customer->email ?? '' }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="py-2.5">
                    <div class="flex items-center gap-2">
                      <div class="w-12 bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $score->score >= 70 ? 'bg-green-500' : ($score->score >= 40 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $score->score }}%"></div>
                      </div>
                      <span class="font-bold text-gray-900">{{ $score->score }}</span>
                    </div>
                  </td>
                  <td class="py-2.5"><span class="px-1.5 py-0.5 rounded bg-gray-50 text-gray-600">{{ $score->engagement_score }}</span></td>
                  <td class="py-2.5"><span class="px-1.5 py-0.5 rounded bg-gray-50 text-gray-600">{{ $score->transaction_score }}</span></td>
                  <td class="py-2.5"><span class="px-1.5 py-0.5 rounded bg-gray-50 text-gray-600">{{ $score->support_score }}</span></td>
                  <td class="py-2.5"><span class="px-1.5 py-0.5 rounded bg-gray-50 text-gray-600">{{ $score->nps_score }}</span></td>
                  <td class="py-2.5">
                    @if($score->status === 'healthy')
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Healthy</span>
                    @elseif($score->status === 'at_risk')
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>At Risk</span>
                    @else
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>Critical</span>
                    @endif
                  </td>
                  <td class="py-2.5">
                    <button class="w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50" onclick="viewDetails({{ $score->customer_id }})"><svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          {{ $scores->links() }}
        @else
          <div class="text-center py-8">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg></div>
            <h5 class="text-[13px] font-medium text-gray-700">No health scores calculated yet.</h5>
            <p class="text-[11px] text-gray-500">Click "Recalculate All" to generate scores.</p>
          </div>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection

@section('scripts')
<script>
function viewDetails(customerId) {
    fetch(`/app/success/health/calculate/${customerId}`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
    })
    .then(r => r.json())
    .then(data => {
        alert(`Score: ${data.score}\nRecommendations: ${data.recommendations.join(', ')}`);
    });
}
</script>
@endsection
