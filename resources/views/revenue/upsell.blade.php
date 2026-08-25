@extends('layouts.platform')

@section('title', 'Upsell Opportunities')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Upsell Opportunities</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">AI-powered product recommendations</p>
    </div>
    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white rounded-lg text-[11px] font-medium hover:bg-green-700 transition" onclick="generateRecommendations()">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
      Generate AI Recommendations
    </button>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center mx-auto mb-2"><svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
        <h3 class="text-[20px] font-bold text-gray-900">{{ $recommendations->total() ?? 0 }}</h3>
        <p class="text-[11px] text-gray-500">Total Recommendations</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center mx-auto mb-2"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <h3 class="text-[20px] font-bold text-gray-900">{{ $recommendations->where('status', 'executed')->count() ?? 0 }}</h3>
        <p class="text-[11px] text-gray-500">Executed</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center mx-auto mb-2"><svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <h3 class="text-[20px] font-bold text-gray-900">{{ $recommendations->where('status', 'pending')->count() ?? 0 }}</h3>
        <p class="text-[11px] text-gray-500">Pending</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-cyan-50 flex items-center justify-center mx-auto mb-2"><svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <h3 class="text-[20px] font-bold text-gray-900">${{ number_format($recommendations->sum('expected_revenue') ?? 0, 0) }}</h3>
        <p class="text-[11px] text-gray-500">Potential Revenue</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
      <!-- Customers -->
      <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h5 class="text-[13px] font-semibold text-gray-800">Customers</h5>
        </div>
        <div class="p-4">
          @if(!empty($customers) && $customers->count() > 0)
            @foreach($customers as $customer)
            <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-50 last:border-0">
              <div class="w-9 h-9 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0"><span class="text-[11px] font-bold text-green-600">{{ substr($customer->name ?? 'U', 0, 1) }}</span></div>
              <div class="flex-grow min-w-0">
                <p class="text-[12px] font-medium text-gray-900">{{ $customer->name ?? 'Unknown' }}</p>
                <p class="text-[10px] text-gray-500">LTV: ${{ number_format($customer->lifetime_value ?? 0, 0) }}</p>
              </div>
              <button class="w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50" onclick="showOpportunities({{ $customer->id }})"><svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
            </div>
            @endforeach
            {{ $customers->links() }}
          @else
            <div class="text-center py-6">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
              <p class="text-[12px] text-gray-500">No customers found.</p>
            </div>
          @endif
        </div>
      </div>

      <!-- Recommendations -->
      <div class="lg:col-span-3 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <h5 class="text-[13px] font-semibold text-gray-800">AI Recommendations</h5>
          <select class="border border-gray-200 rounded-lg text-[10px] px-2 py-1 focus:outline-none">
            <option>All Strategies</option>
            <option>Upgrade</option>
            <option>Bundle</option>
            <option>Complementary</option>
          </select>
        </div>
        <div class="p-4">
          @if(!empty($recommendations) && $recommendations->count() > 0)
            @foreach($recommendations as $rec)
            <div class="mb-3 p-3 bg-gray-50 rounded-lg">
              <div class="flex items-start justify-between gap-3">
                <div class="flex-grow min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $rec->strategy === 'upgrade' ? 'bg-blue-100 text-blue-700' : ($rec->strategy === 'bundle' ? 'bg-green-100 text-green-700' : 'bg-cyan-100 text-cyan-700') }}">{{ ucfirst($rec->strategy) }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600"><svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>{{ $rec->confidence_score }}%</span>
                  </div>
                  <h6 class="text-[12px] font-medium text-gray-900">{{ $rec->customer->name ?? 'Customer' }} → {{ $rec->product->name ?? 'Product' }}</h6>
                  <p class="text-[10px] text-gray-500 mt-0.5">{{ $rec->message }}</p>
                  <div class="flex gap-3 mt-1.5 text-[10px] text-gray-500">
                    <span><svg class="w-3 h-3 inline mr-0.5 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Expected: ${{ number_format($rec->expected_revenue, 2) }}</span>
                    <span><svg class="w-3 h-3 inline mr-0.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>Original: {{ $rec->originalProduct->name ?? 'N/A' }}</span>
                  </div>
                </div>
                <div class="flex-shrink-0 text-right">
                  @if($rec->status === 'pending')
                  <button class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white rounded-lg text-[10px] font-medium hover:bg-green-700 transition" onclick="executeUpsell({{ $rec->id }})"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>Execute</button>
                  @else
                  <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700">Executed</span>
                  @endif
                  <p class="text-[10px] text-gray-400 mt-1">{{ $rec->created_at->diffForHumans() }}</p>
                </div>
              </div>
            </div>
            @endforeach
            {{ $recommendations->links() }}
          @else
            <div class="text-center py-8">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg></div>
              <h5 class="text-[13px] font-medium text-gray-700">No recommendations yet.</h5>
              <p class="text-[11px] text-gray-500">Click "Generate AI Recommendations" to create upsell suggestions.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@section('scripts')
<script>
function generateRecommendations() {
    if(confirm('Generate AI-powered upsell recommendations for all customers?')) {
        alert('Generating recommendations...');
    }
}
function executeUpsell(recId) {
    if(confirm('Execute this upsell recommendation?')) {
        fetch(`/app/revenue/upsell/${recId}/execute`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(() => location.reload());
    }
}
function showOpportunities(customerId) {
    alert('Showing opportunities for customer ' + customerId);
}
</script>
@endsection
