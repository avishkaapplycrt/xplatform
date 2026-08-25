@extends('layouts.platform')

@section('title', 'Churn & Retention')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Churn & Retention</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">Predict churn risk & manage retention campaigns</p>
    </div>
    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 text-white rounded-lg text-[11px] font-medium hover:bg-red-700 transition" onclick="document.getElementById('campaignModal').style.display='block'">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Create Campaign
    </button>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] text-red-600 font-medium">High Risk Customers</p>
          <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center"><svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        </div>
        <h3 class="text-[20px] font-bold text-red-600">{{ count($highRisk ?? []) }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] text-blue-600 font-medium">Active Campaigns</p>
          <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg></div>
        </div>
        <h3 class="text-[20px] font-bold text-blue-600">{{ count($campaigns ?? []) }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] text-green-600 font-medium">Retention Rate</p>
          <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center"><svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <h3 class="text-[20px] font-bold text-green-600">{{ $retentionRate ?? 100 }}%</h3>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <!-- High Risk Customers -->
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h5 class="text-[13px] font-semibold text-gray-800">High Risk Customers</h5>
        </div>
        <div class="p-4">
          @if(!empty($highRisk) && count($highRisk) > 0)
            <div class="divide-y divide-gray-50">
              @foreach($highRisk as $customer)
              <div class="flex items-center gap-3 py-3">
                <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0"><span class="text-[10px] font-bold text-red-600">{{ substr($customer->name ?? 'U', 0, 1) }}</span></div>
                <div class="flex-grow min-w-0">
                  <p class="text-[12px] font-medium text-gray-900">{{ $customer->name ?? 'Unknown' }}</p>
                  <p class="text-[10px] text-gray-500">{{ $customer->email ?? '' }}</p>
                </div>
                <span class="flex-shrink-0 px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700">High Risk</span>
              </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-8">
              <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
              <p class="text-[12px] text-gray-500">No high risk customers detected.</p>
            </div>
          @endif
        </div>
      </div>

      <!-- Campaigns -->
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h5 class="text-[13px] font-semibold text-gray-800">Retention Campaigns</h5>
        </div>
        <div class="p-4">
          @if(!empty($campaigns) && count($campaigns) > 0)
            <div class="divide-y divide-gray-50">
              @foreach($campaigns as $campaign)
              <div class="flex items-center justify-between py-3">
                <div>
                  <p class="text-[12px] font-medium text-gray-900">{{ $campaign->name }}</p>
                  <p class="text-[10px] text-gray-500">{{ ucfirst(str_replace('_', ' ', $campaign->trigger_type ?? 'manual')) }}</p>
                </div>
                <div class="flex items-center gap-2">
                  <span class="px-2 py-0.5 rounded text-[10px] font-medium {{ $campaign->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $campaign->is_active ? 'Active' : 'Inactive' }}</span>
                  <button class="w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50" onclick="sendCampaign({{ $campaign->id }})"><svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg></button>
                </div>
              </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-8">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg></div>
              <p class="text-[12px] text-gray-500">No campaigns created yet.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Create Campaign Modal -->
<div id="campaignModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
  <div class="flex items-center justify-center min-h-screen px-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h5 class="text-[13px] font-semibold text-gray-800">Create Retention Campaign</h5>
        <button onclick="document.getElementById('campaignModal').style.display='none'" class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </div>
      <form action="{{ route('client.retention.campaign.create') }}" method="POST" class="p-4">
        @csrf
        <div class="mb-3">
          <label class="block text-[11px] font-medium text-gray-700 mb-1">Campaign Name</label>
          <input type="text" name="name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500" placeholder="e.g., Win-Back Campaign" required>
        </div>
        <div class="mb-3">
          <label class="block text-[11px] font-medium text-gray-700 mb-1">Trigger Type</label>
          <select name="trigger_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500">
            <option value="score_threshold">Score Threshold</option>
            <option value="behavioral_pattern">Behavioral Pattern</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="block text-[11px] font-medium text-gray-700 mb-1">Threshold Score (1-100)</label>
          <input type="number" name="threshold_score" min="1" max="100" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500">
        </div>
        <div class="mb-3">
          <label class="block text-[11px] font-medium text-gray-700 mb-1">SMS Template</label>
          <textarea name="sms_template" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500" placeholder="Enter SMS message..."></textarea>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('campaignModal').style.display='none'" class="px-3 py-1.5 border border-gray-200 rounded-lg text-[11px] font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
          <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-[11px] font-medium hover:bg-red-700">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function sendCampaign(campaignId) {
    if(confirm('Execute this campaign now?')) {
        fetch(`/app/retention/campaigns/${campaignId}/execute`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(r => r.json()).then(data => {
            alert(data.message);
        });
    }
}
</script>
@endsection
