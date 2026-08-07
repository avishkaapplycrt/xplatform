@extends('layouts.platform')

@section('title', 'Automated Check-ins')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-100">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Automated Check-ins</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">Schedule and manage customer touchpoints</p>
    </div>
    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[11px] font-medium hover:bg-blue-700 transition" onclick="document.getElementById('scheduleModal').style.display='block'">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Schedule Check-in
    </button>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] text-gray-500 font-medium">Total Check-ins</p>
          <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg></div>
        </div>
        <h3 class="text-[20px] font-bold text-gray-900">{{ $checkins->total() ?? 0 }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] text-gray-500 font-medium">Upcoming</p>
          <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center"><svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <h3 class="text-[20px] font-bold text-amber-600">{{ $upcomingCount ?? 0 }}</h3>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] text-gray-500 font-medium">Templates</p>
          <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center"><svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
        </div>
        <h3 class="text-[20px] font-bold text-green-600">{{ count($templates ?? []) }}</h3>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Check-ins List -->
      <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <h5 class="text-[13px] font-semibold text-gray-800">Scheduled Check-ins</h5>
          <div class="flex gap-1">
            <button class="px-2 py-1 text-[10px] rounded bg-gray-100 text-gray-700 font-medium">All</button>
            <button class="px-2 py-1 text-[10px] rounded hover:bg-gray-50 text-gray-500">Scheduled</button>
            <button class="px-2 py-1 text-[10px] rounded hover:bg-gray-50 text-gray-500">Sent</button>
          </div>
        </div>
        <div class="p-4">
          @if(!empty($checkins) && $checkins->count() > 0)
            <div class="divide-y divide-gray-50">
              @foreach($checkins as $checkin)
              <div class="flex items-center gap-3 py-3">
                <div class="flex-shrink-0">
                  @if($checkin->channel === 'email')
                    <span class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span>
                  @elseif($checkin->channel === 'sms')
                    <span class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center"><svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></span>
                  @else
                    <span class="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center"><svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg></span>
                  @endif
                </div>
                <div class="flex-grow min-w-0">
                  <h6 class="text-[12px] font-medium text-gray-900">{{ $checkin->subject }}</h6>
                  <p class="text-[10px] text-gray-500">{{ $checkin->customer->name ?? 'Customer' }} | {{ ucfirst($checkin->channel) }} | {{ $checkin->scheduled_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="flex-shrink-0 text-right">
                  <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium {{ $checkin->status === 'scheduled' ? 'bg-amber-100 text-amber-700' : ($checkin->status === 'sent' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">{{ ucfirst($checkin->status) }}</span>
                </div>
                @if($checkin->status === 'scheduled')
                <button class="flex-shrink-0 w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50" onclick="sendNow({{ $checkin->id }})"><svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg></button>
                @endif
              </div>
              @endforeach
            </div>
            {{ $checkins->links() }}
          @else
            <div class="text-center py-8">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg></div>
              <p class="text-[12px] text-gray-500">No check-ins scheduled.</p>
            </div>
          @endif
        </div>
      </div>

      <!-- Templates -->
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
          <h5 class="text-[13px] font-semibold text-gray-800">Templates</h5>
        </div>
        <div class="p-4">
          @if(!empty($templates))
            @foreach($templates as $key => $template)
            <div class="mb-3 p-3 bg-gray-50 rounded-lg">
              <h6 class="text-[12px] font-semibold text-gray-900">{{ $template['name'] }}</h6>
              <p class="text-[10px] text-gray-500 mt-0.5">{{ $template['subject'] }}</p>
              <p class="text-[10px] text-gray-400 mt-1 italic">"{{ Str::limit($template['message'], 60) }}"</p>
            </div>
            @endforeach
          @else
            <div class="text-center py-6">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
              <p class="text-[12px] text-gray-500">No templates available.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Schedule Modal -->
<div id="scheduleModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" style="display:none;">
  <div class="flex items-center justify-center min-h-screen px-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h5 class="text-[13px] font-semibold text-gray-800">Schedule Check-in</h5>
        <button onclick="document.getElementById('scheduleModal').style.display='none'" class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </div>
      <form action="{{ route('client.success.checkins.schedule') }}" method="POST" class="p-4">
        @csrf
        <div class="mb-3">
          <label class="block text-[11px] font-medium text-gray-700 mb-1">Customer</label>
          <select name="customer_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500">
            <option>Select customer...</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="block text-[11px] font-medium text-gray-700 mb-1">Template</label>
          <select name="template" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500">
            @foreach($templates ?? [] as $key => $template)
            <option value="{{ $key }}">{{ $template['name'] }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="block text-[11px] font-medium text-gray-700 mb-1">Scheduled At</label>
          <input type="datetime-local" name="scheduled_at" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500">
        </div>
        <div class="mb-3">
          <label class="block text-[11px] font-medium text-gray-700 mb-1">Channel</label>
          <select name="channel" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] focus:outline-none focus:border-blue-500">
            <option value="email">Email</option>
            <option value="sms">SMS</option>
            <option value="in_app">In-App</option>
          </select>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('scheduleModal').style.display='none'" class="px-3 py-1.5 border border-gray-200 rounded-lg text-[11px] font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
          <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-[11px] font-medium hover:bg-blue-700">Schedule</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function sendNow(checkinId) {
    if(confirm('Send this check-in now?')) {
        fetch(`/app/success/checkins/${checkinId}/send`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(() => location.reload());
    }
}
</script>
@endsection
