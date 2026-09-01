@extends('layouts.platform')

@section('title', 'CRM Analytics - Deals')

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Test Company';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
  $period = request('period', '30d');
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('client.reports.executive-dashboard') }}"
         class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
         title="Back to Dashboard">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <div>
        <h1 class="text-[16px] font-semibold text-gray-900">Deal Analytics</h1>
        <p class="text-[11px] text-gray-500 mt-0.5">Track deal performance and conversion</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <select class="form-input" style="width: 130px; cursor: pointer;" onchange="window.location.href='{ request()->url() }?period='+this.value">
        <option value="7d" { $period == '7d' ? 'selected' : '' }>Last 7 Days</option>
        <option value="30d" { $period == '30d' ? 'selected' : '' }>Last 30 Days</option>
        <option value="90d" { $period == '90d' ? 'selected' : '' }>Last 90 Days</option>
        <option value="1y" { $period == '1y' ? 'selected' : '' }>Last Year</option>
      </select>
      <a href="{ request()->url() }/export/pdf" class="btn-secondary flex items-center gap-2" style="text-decoration: none;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export
      </a>
      <div style="position:relative" id="l1AvatarWrap">
        <button onclick="var d=document.getElementById('l1Dropdown');d.style.display=d.style.display==='block'?'none':'block'"
                style="width:32px;height:32px;border-radius:50%;background:#06b6d4;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;border:none;cursor:pointer">
          {{ $av }}
        </button>
        <div id="l1Dropdown"
             style="display:none;position:absolute;right:0;top:40px;width:192px;background:#fff;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.12);border:1px solid #e5e7eb;padding:4px 0;z-index:999">
          <div style="padding:8px 16px;border-bottom:1px solid #f3f4f6">
            <p style="font-size:12px;font-weight:600;color:#111827;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cn }}</p>
            <p style="font-size:10px;color:#9ca3af;margin:2px 0 0">Client Account</p>
          </div>
          <a href="{{ route('client.dashboard') }}"
             style="display:flex;align-items:center;gap:8px;padding:8px 16px;font-size:12px;color:#374151;text-decoration:none"
             onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
            <svg width="14" height="14" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Profile Settings
          </a>
          <hr style="margin:4px 0;border:none;border-top:1px solid #f3f4f6">
          <form method="POST" action="{{ route('client.logout') }}">
            @csrf
            <button type="submit"
                    style="width:100%;display:flex;align-items:center;gap:8px;padding:8px 16px;font-size:12px;color:#dc2626;background:none;border:none;cursor:pointer;text-align:left"
                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
              <svg width="14" height="14" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              Log Out
            </button>
          </form>
        </div>
      </div>
    </div>
  </header>

  <div class="flex-1 overflow-y-auto px-5 py-5">

    <div class="flex items-center gap-1 mb-5 border-b border-gray-200 pb-0">
      <a href="{{ route('client.reports.crm.overview') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Overview
      </a>
      <a href="{{ route('client.reports.crm.pipeline') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Pipeline
      </a>
      <a href="{{ route('client.reports.crm.deals') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-blue-600 border-b-2 border-blue-600 bg-blue-50/50" style="text-decoration: none;">
        Deals
      </a>
      <a href="{{ route('client.reports.crm.contacts') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Contacts
      </a>
      <a href="{{ route('client.reports.crm.activities') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Activities
      </a>
      <a href="{{ route('client.reports.crm.forecast') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Forecast
      </a>
    </div>

    @if($data['has_data'] ?? false)
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100">
        <h6 class="text-[13px] font-semibold text-gray-800">Deals</h6>
      </div>

      @if(($deals ?? collect())->isEmpty())
      <div class="p-8 text-center">
        @if($data['synced'] ?? false)
        <p class="text-[13px] text-gray-500">No deals found in your connected CRM.</p>
        <p class="text-[11px] text-gray-400 mt-1">Your last sync completed successfully — there just aren't any deals recorded there yet.</p>
        @else
        <p class="text-[13px] text-gray-500">Connected, but no deals have been synced yet.</p>
        <p class="text-[11px] text-gray-400 mt-1">Hit "Sync Now" on the CRM Connections page to pull deals in.</p>
        @endif
      </div>
      @else
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-gray-100">
              <th class="px-4 py-2.5 text-[11px] font-medium text-gray-500 uppercase tracking-wide">Deal</th>
              <th class="px-4 py-2.5 text-[11px] font-medium text-gray-500 uppercase tracking-wide">Value</th>
              <th class="px-4 py-2.5 text-[11px] font-medium text-gray-500 uppercase tracking-wide">Stage</th>
              <th class="px-4 py-2.5 text-[11px] font-medium text-gray-500 uppercase tracking-wide">Status</th>
              <th class="px-4 py-2.5 text-[11px] font-medium text-gray-500 uppercase tracking-wide">Close Date</th>
            </tr>
          </thead>
          <tbody>
            @foreach($deals as $deal)
            <tr class="border-b border-gray-50 last:border-b-0">
              <td class="px-4 py-2.5 text-[13px] text-gray-800">{{ $deal->name }}</td>
              <td class="px-4 py-2.5 text-[13px] text-gray-600">${{ number_format($deal->value) }}</td>
              <td class="px-4 py-2.5 text-[12px] text-gray-500">{{ $deal->stage ?? '—' }}</td>
              <td class="px-4 py-2.5 text-[12px]">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                  {{ $deal->status === 'won' ? 'bg-emerald-50 text-emerald-700' : ($deal->status === 'lost' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                  {{ ucfirst($deal->status ?? 'open') }}
                </span>
              </td>
              <td class="px-4 py-2.5 text-[12px] text-gray-500">{{ $deal->close_date ? \Carbon\Carbon::parse($deal->close_date)->format('M j, Y') : '—' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
    @else
    <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
      <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <h3 class="text-[14px] font-semibold text-gray-800 mb-1">No CRM data yet</h3>
      <p class="text-[12px] text-gray-500 mb-4">Connect your CRM account to start tracking analytics.</p>
      <a href="{{ route('client.crm-connections') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-600 text-white text-[12px] font-medium rounded-lg hover:bg-cyan-700 transition" style="text-decoration: none;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Connect CRM
      </a>
    </div>
    @endif

  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush
