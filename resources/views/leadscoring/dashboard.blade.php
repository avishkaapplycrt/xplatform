@extends('layouts.platform')

@section('title', 'Lead Scoring & Qualification')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Lead Scoring & Qualification</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">AI-powered lead scoring, qualification, and routing</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('client.leadscoring.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-[11px] font-medium text-gray-700 hover:bg-gray-50 transition">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        All Leads
      </a>
      <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[11px] font-medium hover:bg-blue-700 transition" onclick="document.getElementById('addLeadModal').style.display='block'">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Lead
      </button>
      <form action="{{ route('client.leadscoring.bulk-score') }}" method="POST" class="inline">
        @csrf
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white rounded-lg text-[11px] font-medium hover:bg-green-700 transition">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
          Bulk Score
        </button>
      </form>
    </div>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[11px] text-gray-500 font-medium mb-1">Total Leads</p>
        <h3 class="text-[20px] font-bold text-gray-900">{{ $stats['total_leads'] ?? 0 }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[11px] text-red-600 font-medium mb-1">Hot Leads</p>
        <h3 class="text-[20px] font-bold text-red-600">{{ $stats['hot_leads'] ?? 0 }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[11px] text-amber-600 font-medium mb-1">Warm Leads</p>
        <h3 class="text-[20px] font-bold text-amber-600">{{ $stats['warm_leads'] ?? 0 }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[11px] text-blue-600 font-medium mb-1">Cold Leads</p>
        <h3 class="text-[20px] font-bold text-blue-600">{{ $stats['cold_leads'] ?? 0 }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[11px] text-green-600 font-medium mb-1">Converted</p>
        <h3 class="text-[20px] font-bold text-green-600">{{ $stats['converted_leads'] ?? 0 }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[11px] text-purple-600 font-medium mb-1">Avg Score</p>
        <h3 class="text-[20px] font-bold text-purple-600">{{ $stats['avg_score'] ?? 0 }}</h3>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Score Distribution -->
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h5 class="text-[13px] font-semibold text-gray-800">Score Distribution</h5>
        </div>
        <div class="p-4">
          @if(!empty($scoreDistribution))
            @foreach($scoreDistribution as $range)
            <div class="flex items-center gap-3 mb-2">
              <span class="text-[11px] text-gray-500 w-12">{{ $range['label'] }}</span>
              <div class="flex-grow bg-gray-100 rounded-full h-2">
                @php
                  $maxCount = max(array_column($scoreDistribution, 'count')) ?: 1;
                  $width = ($range['count'] / $maxCount) * 100;
                @endphp
                <div class="h-2 rounded-full bg-{{ $range['color'] }}-500" style="width: {{ $width }}%"></div>
              </div>
              <span class="text-[11px] font-medium text-gray-700 w-8 text-right">{{ $range['count'] }}</span>
            </div>
            @endforeach
          @else
            <div class="text-center py-6">
              <p class="text-[12px] text-gray-500">No score data available.</p>
            </div>
          @endif
        </div>
      </div>

      <!-- Hot Leads -->
      <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <h5 class="text-[13px] font-semibold text-gray-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
            Hot Leads (Score 75+)
          </h5>
          <a href="{{ route('client.leadscoring.index') }}?qualification=hot" class="text-[11px] text-blue-600 hover:text-blue-700 font-medium">View All →</a>
        </div>
        <div class="p-4">
          @if(!empty($hotLeads) && $hotLeads->count() > 0)
            <div class="divide-y divide-gray-50">
              @foreach($hotLeads as $lead)
              <div class="flex items-center gap-3 py-3">
                <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                  <span class="text-[11px] font-bold text-red-600">{{ substr($lead->name, 0, 1) }}</span>
                </div>
                <div class="flex-grow min-w-0">
                  <p class="text-[12px] font-medium text-gray-900">{{ $lead->name }}</p>
                  <p class="text-[10px] text-gray-500">{{ $lead->email }} | {{ $lead->company ?? 'No Company' }}</p>
                </div>
                <div class="flex-shrink-0 text-right">
                  <span class="text-[13px] font-bold text-red-600">{{ $lead->latestScore?->total_score ?? 'N/A' }}</span>
                  <p class="text-[10px] text-gray-400">{{ $lead->latestScore?->conversion_probability ?? 0 }}% conv.</p>
                </div>
                <button class="flex-shrink-0 w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50" onclick="routeToSales({{ $lead->id }})" title="Route to Sales">
                  <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
              </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-8">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <p class="text-[12px] text-gray-500">No hot leads detected.</p>
              <p class="text-[11px] text-gray-400 mt-1">Add leads and run scoring to see hot prospects.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Recent Leads -->
    <div class="mt-4 bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h5 class="text-[13px] font-semibold text-gray-800">Recent Leads</h5>
        <a href="{{ route('client.leadscoring.index') }}" class="text-[11px] text-blue-600 hover:text-blue-700 font-medium">View All →</a>
      </div>
      <div class="p-4">
        @if(!empty($recentLeads) && $recentLeads->count() > 0)
          <div class="overflow-x-auto">
            <table class="w-full text-[11px]">
              <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100">
                  <th class="pb-2 font-medium">Lead</th>
                  <th class="pb-2 font-medium">Company</th>
                  <th class="pb-2 font-medium">Source</th>
                  <th class="pb-2 font-medium">Score</th>
                  <th class="pb-2 font-medium">Qualification</th>
                  <th class="pb-2 font-medium">Status</th>
                  <th class="pb-2 font-medium">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                @foreach($recentLeads as $lead)
                <tr>
                  <td class="py-2.5">
                    <div class="flex items-center gap-2">
                      <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-[10px] font-bold text-gray-600">{{ substr($lead->name, 0, 1) }}</span>
                      </div>
                      <div>
                        <p class="font-medium text-gray-900">{{ $lead->name }}</p>
                        <p class="text-[10px] text-gray-400">{{ $lead->email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="py-2.5 text-gray-600">{{ $lead->company ?? '-' }}</td>
                  <td class="py-2.5"><span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-50 text-gray-600">{{ $lead->source }}</span></td>
                  <td class="py-2.5">
                    <div class="flex items-center gap-2">
                      <div class="w-10 bg-gray-100 rounded-full h-1.5">
                        @php $score = $lead->latestScore?->total_score ?? 0; @endphp
                        <div class="h-1.5 rounded-full {{ $score >= 75 ? 'bg-red-500' : ($score >= 50 ? 'bg-amber-500' : ($score >= 25 ? 'bg-blue-500' : 'bg-gray-400')) }}" style="width: {{ $score }}%"></div>
                      </div>
                      <span class="font-bold {{ $score >= 75 ? 'text-red-600' : ($score >= 50 ? 'text-amber-600' : 'text-gray-600') }}">{{ round($score, 0) }}</span>
                    </div>
                  </td>
                  <td class="py-2.5">
                    @if($lead->qualification_status === 'hot')
                      <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700">Hot</span>
                    @elseif($lead->qualification_status === 'warm')
                      <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">Warm</span>
                    @elseif($lead->qualification_status === 'cold')
                      <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-700">Cold</span>
                    @else
                      <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">{{ ucfirst($lead->qualification_status) }}</span>
                    @endif
                  </td>
                  <td class="py-2.5">
                    <span class="px-2 py-0.5 rounded text-[10px] font-medium {{ $lead->status === 'converted' ? 'bg-green-100 text-green-700' : ($lead->status === 'lost' ? 'bg-gray-100 text-gray-600' : 'bg-blue-50 text-blue-700') }}">{{ ucfirst($lead->status) }}</span>
                  </td>
                  <td class="py-2.5">
                    <div class="flex gap-1">
                      <a href="{{ route('client.leadscoring.show', $lead->id) }}" class="w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50" title="View">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      </a>
                      <button class="w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50" onclick="rescoreLead({{ $lead->id }})" title="Re-score">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-8">
            <p class="text-[12px] text-gray-500">No leads yet. Add your first lead to get started.</p>
          </div>
        @endif
      </div>
    </div>

  </div>
</div>

<!-- Add Lead Modal -->
<div id="addLeadModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
  <div class="flex items-center justify-center min-h-screen px-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h5 class="text-[13px] font-semibold text-gray-800">Add New Lead</h5>
        <button onclick="document.getElementById('addLeadModal').style.display='none'" class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </div>
      <form action="{{ route('client.leadscoring.store') }}" method="POST" class="p-4">
        @csrf
        <div class="grid grid-cols-2 gap-3 mb-3">
          <div>
            <label class="block text-[11px] font-medium text-gray-700 mb-1">Name *</label>
            <input type="text" name="name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500" required>
          </div>
          <div>
            <label class="block text-[11px] font-medium text-gray-700 mb-1">Email *</label>
            <input type="email" name="email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500" required>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-3">
          <div>
            <label class="block text-[11px] font-medium text-gray-700 mb-1">Phone</label>
            <input type="text" name="phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500">
          </div>
          <div>
            <label class="block text-[11px] font-medium text-gray-700 mb-1">Source *</label>
            <select name="source" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500" required>
              <option value="website">Website</option>
              <option value="referral">Referral</option>
              <option value="organic_search">Organic Search</option>
              <option value="paid_search">Paid Search</option>
              <option value="linkedin">LinkedIn</option>
              <option value="email_campaign">Email Campaign</option>
              <option value="social_media">Social Media</option>
              <option value="webinar">Webinar</option>
              <option value="trade_show">Trade Show</option>
              <option value="cold_outreach">Cold Outreach</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-3">
          <div>
            <label class="block text-[11px] font-medium text-gray-700 mb-1">Company</label>
            <input type="text" name="company" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500">
          </div>
          <div>
            <label class="block text-[11px] font-medium text-gray-700 mb-1">Job Title</label>
            <input type="text" name="job_title" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500">
          </div>
        </div>
        <div class="mb-3">
          <label class="block text-[11px] font-medium text-gray-700 mb-1">Source Detail</label>
          <input type="text" name="source_detail" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500" placeholder="e.g., Landing page, Campaign name">
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('addLeadModal').style.display='none'" class="px-3 py-1.5 border border-gray-200 rounded-lg text-[11px] font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
          <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[11px] font-medium hover:bg-blue-700">Add Lead</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function rescoreLead(leadId) {
    fetch(`/app/lead-scoring/${leadId}/rescore`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(r => r.json()).then(data => {
        if(data.success) {
            alert(`Lead re-scored!\nScore: ${data.score}\nQualification: ${data.qualification}\nConversion Probability: ${data.conversion_probability}%`);
            location.reload();
        }
    });
}

function routeToSales(leadId) {
    if(confirm('Route this hot lead to the sales team?')) {
        fetch(`/app/lead-scoring/${leadId}/route-to-sales`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(r => r.json()).then(data => {
            alert(data.message);
        });
    }
}
</script>
@endsection
