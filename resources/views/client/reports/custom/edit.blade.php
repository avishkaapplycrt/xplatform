@extends('layouts.platform')

@section('title', 'Edit Custom Report')

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Test Company';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('client.reports.custom.index') }}"
         class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
         title="Back">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <div>
        <h1 class="text-[16px] font-semibold text-gray-900">Edit Report</h1>
        <p class="text-[11px] text-gray-500 mt-0.5">Update your custom report settings</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
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

    <form action="{{ route('client.reports.custom.update', $report->id ?? 1) }}" method="POST" class="max-w-3xl">
      @csrf
      @method('PUT')
      <div class="bg-white border border-gray-200 rounded-xl p-6 mb-4">
        <div class="mb-4">
          <label class="block text-[12px] font-semibold text-gray-700 mb-2">Report Name</label>
          <input type="text" name="name" value="{{ $report->name ?? '' }}" class="form-input w-full" required>
        </div>
        <div class="mb-4">
          <label class="block text-[12px] font-semibold text-gray-700 mb-2">Description</label>
          <textarea name="description" class="form-input w-full" rows="2">{{ $report->description ?? '' }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-[12px] font-semibold text-gray-700 mb-2">Date Range</label>
            <select name="date_range" class="form-input w-full">
              <option value="7d" {{ ($report->date_range ?? '') == '7d' ? 'selected' : '' }}>Last 7 Days</option>
              <option value="30d" {{ ($report->date_range ?? '') == '30d' ? 'selected' : '' }}>Last 30 Days</option>
              <option value="90d" {{ ($report->date_range ?? '') == '90d' ? 'selected' : '' }}>Last 90 Days</option>
              <option value="1y" {{ ($report->date_range ?? '') == '1y' ? 'selected' : '' }}>Last Year</option>
            </select>
          </div>
          <div>
            <label class="block text-[12px] font-semibold text-gray-700 mb-2">Chart Type</label>
            <select name="chart_type" class="form-input w-full">
              <option value="table" {{ ($report->chart_type ?? '') == 'table' ? 'selected' : '' }}>Table</option>
              <option value="line" {{ ($report->chart_type ?? '') == 'line' ? 'selected' : '' }}>Line Chart</option>
              <option value="bar" {{ ($report->chart_type ?? '') == 'bar' ? 'selected' : '' }}>Bar Chart</option>
              <option value="pie" {{ ($report->chart_type ?? '') == 'pie' ? 'selected' : '' }}>Pie Chart</option>
            </select>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white text-[12px] font-medium rounded-lg hover:bg-blue-700 transition">Update Report</button>
        <a href="{{ route('client.reports.custom.index') }}" class="px-4 py-2.5 border border-gray-200 text-gray-700 text-[12px] font-medium rounded-lg hover:bg-gray-50 transition" style="text-decoration: none;">Cancel</a>
      </div>
    </form>

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