@extends('layouts.platform')

@section('title', 'Real-Time Alerts')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Real-Time Alerts</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">Monitor and manage system alerts</p>
    </div>
    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 text-white rounded-lg text-[11px] font-medium hover:bg-red-700 transition" data-bs-toggle="modal" data-bs-target="#createRuleModal">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Create Alert Rule
    </button>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] text-red-600 font-medium">Critical Alerts</p>
          <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center"><svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        </div>
        <h3 class="text-[20px] font-bold text-red-600">{{ $criticalCount ?? 0 }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] text-amber-600 font-medium">High Priority</p>
          <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center"><svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
        </div>
        <h3 class="text-[20px] font-bold text-amber-600">{{ $highCount ?? 0 }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] text-blue-600 font-medium">Acknowledged</p>
          <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <h3 class="text-[20px] font-bold text-blue-600">{{ $acknowledgedCount ?? 0 }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] text-green-600 font-medium">Active Rules</p>
          <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center"><svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
        </div>
        <h3 class="text-[20px] font-bold text-green-600">{{ count($rules ?? []) }}</h3>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Active Alerts -->
      <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <h5 class="text-[13px] font-semibold text-gray-800">Active Alerts</h5>
          <div class="flex gap-1">
            <button class="px-2 py-1 text-[10px] rounded bg-gray-100 text-gray-700 font-medium">All</button>
            <button class="px-2 py-1 text-[10px] rounded hover:bg-gray-50 text-gray-500">New</button>
            <button class="px-2 py-1 text-[10px] rounded hover:bg-gray-50 text-gray-500">Acknowledged</button>
          </div>
        </div>
        <div class="p-4">
          @if(!empty($alerts) && $alerts->count() > 0)
            <div class="divide-y divide-gray-50">
              @foreach($alerts as $alert)
              <div class="flex items-center gap-3 py-3">
                <div class="flex-shrink-0">
                  @if($alert->priority === 'high')
                    <span class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center"><svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg></span>
                  @elseif($alert->priority === 'medium')
                    <span class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center"><svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></span>
                  @else
                    <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center"><svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                  @endif
                </div>
                <div class="flex-grow min-w-0">
                  <h6 class="text-[12px] font-medium text-gray-900">{{ $alert->message }}</h6>
                  <p class="text-[10px] text-gray-500">{{ ucfirst($alert->metric) }} | Threshold: {{ $alert->threshold_value }} | Actual: {{ $alert->actual_value }}</p>
                </div>
                <div class="flex-shrink-0 text-right">
                  <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium {{ $alert->status === 'new' ? 'bg-red-100 text-red-700' : ($alert->status === 'acknowledged' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">{{ ucfirst($alert->status) }}</span>
                  <p class="text-[10px] text-gray-400 mt-0.5">{{ $alert->created_at->diffForHumans() }}</p>
                </div>
                @if($alert->status === 'new')
                <button class="flex-shrink-0 w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50" onclick="acknowledgeAlert({{ $alert->id }})"><svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></button>
                @endif
              </div>
              @endforeach
            </div>
            {{ $alerts->links() }}
          @else
            <div class="text-center py-8">
              <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
              <h5 class="text-[13px] font-medium text-gray-700">All Clear!</h5>
              <p class="text-[11px] text-gray-500">No active alerts at the moment.</p>
            </div>
          @endif
        </div>
      </div>

      <!-- Alert Rules -->
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h5 class="text-[13px] font-semibold text-gray-800">Alert Rules</h5>
        </div>
        <div class="p-4">
          @if(!empty($rules) && count($rules) > 0)
            @foreach($rules as $rule)
            <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-50 last:border-0">
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" {{ $rule->is_active ? 'checked' : '' }} onchange="toggleRule({{ $rule->id }}, this.checked)">
                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
              <div class="flex-grow min-w-0">
                <h6 class="text-[12px] font-medium text-gray-900">{{ $rule->name }}</h6>
                <p class="text-[10px] text-gray-500">{{ ucfirst(str_replace('_', ' ', $rule->metric)) }} {{ $rule->comparison_operator }} {{ $rule->threshold_value }}</p>
              </div>
              <span class="flex-shrink-0 px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700">{{ $rule->alerts_count ?? 0 }} alerts</span>
            </div>
            @endforeach
          @else
            <div class="text-center py-6">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg></div>
              <p class="text-[12px] text-gray-500">No alert rules configured.</p>
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
function acknowledgeAlert(alertId) {
    fetch(`/app/alerts/${alertId}/acknowledge`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
    }).then(() => location.reload());
}
function toggleRule(ruleId, isActive) {
    fetch(`/app/alerts/rules/${ruleId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({is_active: isActive})
    });
}
</script>
@endsection
