@extends('layouts.platform')

@section('title', 'Growth Intelligence - Business Health')

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
        <h1 class="text-[16px] font-semibold text-gray-900">Business Health Score</h1>
        <p class="text-[11px] text-gray-500 mt-0.5">Overall assessment of your business performance</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <select class="form-input" style="width: 130px; cursor: pointer;" onchange="window.location.href='{{ request()->url() }}?period='+this.value">
        <option value="7d" {{ $period == '7d' ? 'selected' : '' }}>Last 7 Days</option>
        <option value="30d" {{ $period == '30d' ? 'selected' : '' }}>Last 30 Days</option>
        <option value="90d" {{ $period == '90d' ? 'selected' : '' }}>Last 90 Days</option>
        <option value="1y" {{ $period == '1y' ? 'selected' : '' }}>Last Year</option>
      </select>
      @if($data['has_snapshot'])
      <form method="POST" action="{{ route('client.reports.growth.generate') }}">
        @csrf
        <button type="submit" class="btn-secondary flex items-center gap-2" style="text-decoration: none;">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Regenerate
        </button>
      </form>
      @endif
      <a href="{{ request()->url() }}/export/pdf" class="btn-secondary flex items-center gap-2" style="text-decoration: none;">
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
      <a href="{{ route('client.reports.growth.business-health') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-blue-600 border-b-2 border-blue-600 bg-blue-50/50" style="text-decoration: none;">
        Health Score
      </a>
      <a href="{{ route('client.reports.growth.cross-channel') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Cross-Channel
      </a>
      <a href="{{ route('client.reports.growth.recommendations') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Recommendations
      </a>
      <a href="{{ route('client.reports.growth.benchmarks') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Benchmarks
      </a>
      <a href="{{ route('client.reports.growth.trends') }}" class="px-4 py-2.5 text-[12px] font-medium rounded-t-lg transition text-gray-500 hover:text-gray-700 hover:bg-gray-50" style="text-decoration: none;">
        Trends
      </a>
    </div>

    @if(session('success'))
      <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-[12px] px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-[12px] px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    @if(!$data['has_snapshot'])
      <div class="bg-white border border-dashed border-gray-300 rounded-xl p-10 text-center">
        <h3 class="text-[14px] font-semibold text-gray-700 mb-1">No health report yet</h3>
        <p class="text-[12px] text-gray-500 mb-4 max-w-md mx-auto">
          @if($data['has_data'])
            You have data to work with — generate your first AI-powered health report below.
          @else
            Connect a data source (website tracking, email, or CRM) to generate a report from real activity.
          @endif
        </p>
        @if($data['has_data'])
          <form method="POST" action="{{ route('client.reports.growth.generate') }}">
            @csrf
            <button type="submit" class="btn-primary">Generate report</button>
          </form>
        @else
          <a href="{{ route('client.website-connections') }}" class="btn-primary" style="text-decoration:none;">Connect a data source</a>
        @endif
      </div>
    @else

      @if($data['summary'])
        <div class="bg-white border border-gray-200 rounded-xl p-4 mb-4">
          <p class="text-[12px] text-gray-600">{{ $data['summary'] }}</p>
          <p class="text-[10px] text-gray-400 mt-2">Generated {{ $data['generated_at']->diffForHumans() }}</p>
        </div>
      @endif

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-6 text-center">
          <div class="w-20 h-20 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3">
            <span class="text-[24px] font-bold text-green-600">{{ $data['health_score'] }}</span>
          </div>
          <h3 class="text-[14px] font-semibold text-gray-800 mb-1">Health Score</h3>
          <p class="text-[11px] text-gray-500">Out of 100</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6">
          <h3 class="text-[13px] font-semibold text-gray-800 mb-3">Strengths</h3>
          <ul class="space-y-2">
            @forelse($data['strengths'] as $s)
            <li class="flex items-center gap-2 text-[12px] text-gray-600">
              <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
              {{ $s }}
            </li>
            @empty
            <li class="text-[12px] text-gray-400">No strengths identified yet</li>
            @endforelse
          </ul>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6">
          <h3 class="text-[13px] font-semibold text-gray-800 mb-3">Weaknesses</h3>
          <ul class="space-y-2">
            @forelse($data['weaknesses'] as $w)
            <li class="flex items-center gap-2 text-[12px] text-gray-600">
              <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              {{ $w }}
            </li>
            @empty
            <li class="text-[12px] text-gray-400">No weaknesses identified</li>
            @endforelse
          </ul>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6">
          <h3 class="text-[13px] font-semibold text-gray-800 mb-3">Opportunities</h3>
          <ul class="space-y-2">
            @forelse($data['opportunities'] as $o)
            <li class="flex items-center gap-2 text-[12px] text-gray-600">
              <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              {{ $o }}
            </li>
            @empty
            <li class="text-[12px] text-gray-400">No opportunities identified yet</li>
            @endforelse
          </ul>
        </div>
      </div>
    @endif

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
