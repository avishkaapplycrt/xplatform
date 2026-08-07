@extends('layouts.platform')

@section('title', 'Customer Segments')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Customer Segments</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">Create & manage customer segments</p>
    </div>
    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cyan-600 text-white rounded-lg text-[11px] font-medium hover:bg-cyan-700 transition" data-bs-toggle="modal" data-bs-target="#createSegmentModal">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Create Segment
    </button>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <!-- Segment Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
      @if(!empty($segments) && $segments->count() > 0)
        @foreach($segments as $segment)
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-cyan-50 flex items-center justify-center">
              <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" class="sr-only peer" {{ $segment->is_active ? 'checked' : '' }}>
              <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-cyan-600"></div>
            </label>
          </div>
          <h5 class="text-[13px] font-semibold text-gray-900 mb-1">{{ $segment->name }}</h5>
          <p class="text-[11px] text-gray-500 mb-3">{{ $segment->description }}</p>
          <div class="flex gap-2 mb-3">
            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-cyan-50 text-cyan-700">{{ $segment->customer_count }} customers</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-50 text-gray-600">{{ count($segment->rules ?? []) }} rules</span>
          </div>
          <div class="flex gap-2">
            <a href="{{ route('client.segmentation.customers', $segment->id) }}" class="flex-grow text-center px-3 py-1.5 bg-cyan-50 text-cyan-700 rounded-lg text-[11px] font-medium hover:bg-cyan-100 transition">View</a>
            <button class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50" onclick="recalculateSegment({{ $segment->id }})"><svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
          </div>
        </div>
        @endforeach
      @else
        <div class="col-span-full bg-white border border-gray-200 rounded-xl text-center py-10">
          <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div>
          <h5 class="text-[13px] font-medium text-gray-700">No segments created yet.</h5>
          <p class="text-[11px] text-gray-500">Create your first customer segment to start grouping customers.</p>
        </div>
      @endif
    </div>

    <!-- Smart Segment Presets -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100">
        <h5 class="text-[13px] font-semibold text-gray-800">Smart Segment Presets</h5>
        <p class="text-[11px] text-gray-500 mt-0.5">One-click create pre-built segments</p>
      </div>
      <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div>
              <h6 class="text-[12px] font-semibold text-gray-900">High-Value Customers</h6>
              <p class="text-[10px] text-gray-500">LTV > $1,000</p>
            </div>
            <button class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-[10px] font-medium hover:bg-green-700 transition" onclick="createPreset('high_value')">Create</button>
          </div>
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div>
              <h6 class="text-[12px] font-semibold text-gray-900">At-Risk Customers</h6>
              <p class="text-[10px] text-gray-500">Declining engagement</p>
            </div>
            <button class="px-3 py-1.5 bg-amber-500 text-white rounded-lg text-[10px] font-medium hover:bg-amber-600 transition" onclick="createPreset('at_risk')">Create</button>
          </div>
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div>
              <h6 class="text-[12px] font-semibold text-gray-900">New Customers</h6>
              <p class="text-[10px] text-gray-500">Acquired < 30 days</p>
            </div>
            <button class="px-3 py-1.5 bg-blue-500 text-white rounded-lg text-[10px] font-medium hover:bg-blue-600 transition" onclick="createPreset('new')">Create</button>
          </div>
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div>
              <h6 class="text-[12px] font-semibold text-gray-900">Frequent Buyers</h6>
              <p class="text-[10px] text-gray-500">4+ purchases / 60 days</p>
            </div>
            <button class="px-3 py-1.5 bg-purple-500 text-white rounded-lg text-[10px] font-medium hover:bg-purple-600 transition" onclick="createPreset('frequent')">Create</button>
          </div>
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div>
              <h6 class="text-[12px] font-semibold text-gray-900">Cart Abandoners</h6>
              <p class="text-[10px] text-gray-500">Abandoned cart < 7 days</p>
            </div>
            <button class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-[10px] font-medium hover:bg-red-600 transition" onclick="createPreset('abandoned')">Create</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@section('scripts')
<script>
function recalculateSegment(segmentId) {
    fetch(`/app/segments/${segmentId}/recalculate`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
    }).then(() => location.reload());
}
function createPreset(type) {
    if(confirm('Create smart segment: ' + type.replace('_', ' ') + '?')) {
        alert('Creating preset segment...');
    }
}
</script>
@endsection
