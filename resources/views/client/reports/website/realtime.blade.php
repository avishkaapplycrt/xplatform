@extends('layouts.platform')

@section('title', 'Website Analytics - Real-time')

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
        <h1 class="text-[16px] font-semibold text-gray-900">Real-time Analytics</h1>
        <p class="text-[11px] text-gray-500 mt-0.5">See who is on your site right now</p>
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
      <a href="{{ route('client.reports.website.overview') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Overview
      </a>
      <a href="{{ route('client.reports.website.traffic-sources') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Traffic Sources
      </a>
      <a href="{{ route('client.reports.website.pages') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Pages
      </a>
      <a href="{{ route('client.reports.website.user-behavior') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        User Behavior
      </a>
      <a href="{{ route('client.reports.website.conversions') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Conversions
      </a>
      <a href="{{ route('client.reports.website.realtime') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-blue-600 border-b-2 border-blue-600 bg-blue-50/50" style="text-decoration: none;">
        Real-time
      </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[11px] text-gray-500 font-medium mb-1">Active Users</p>
        <p class="text-[20px] font-bold text-gray-900">{{ number_format($data['active_users'] ?? 0) }}</p>
      </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
      <p class="text-[14px] text-gray-600">Real-time data refreshes automatically every 30 seconds.</p>
    </div>

  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endsection
