@extends('layouts.platform')
@section('title', 'Identity Resolution')

@push('styles')
<style>
  .tp-grid { display:grid; grid-template-columns:140px 1fr auto 90px; align-items:center; gap:10px; padding:11px 0; border-bottom:1px solid #f3f4f6; }
  .tp-grid:last-child { border-bottom:none; }
  .tp-dash { border:none; border-top:1.5px dashed #d1d5db; height:0; }
  .sig-row { display:grid; grid-template-columns:1fr 1fr; border-bottom:1px solid #dbeafe; }
  .sig-row:last-child { border-bottom:none; }
  .sig-cell { display:flex; align-items:center; gap:7px; padding:10px 0; font-size:12px; color:#3b82f6; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));

  $touchpoints = [
    ['Website Visit',  'anon_a9f2', 'cookie_id'],
    ['Email Opened',   'anon_a9f2', 'email_hash'],
    ['App Signup',     'uid_88210', 'deterministic'],
    ['App Purchase',   'uid_88210', 'session_link'],
    ['Support Chat',   'uid_88210', 'merged'],
  ];

  $signals = array_fill(0, 14, 'lorem ipsum');

  $donuts = [
    ['ID Stitching',  '#34d399', '#f0fdf4'],
    ['Device Match',  '#60a5fa', '#eff6ff'],
    ['Anon → known',  '#a78bfa', '#f5f3ff'],
  ];
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

{{-- HEADER --}}
<header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
  <div>
    <h1 class="text-[16px] font-semibold text-gray-900">Identity Resolution</h1>
    <p class="text-[11px] text-gray-500 mt-0.5">
      Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
      <span class="ml-2 inline-flex items-center gap-1 text-green-600 font-medium text-[10px]">
        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Live
      </span>
    </p>
  </div>
  <div class="flex items-center gap-5 text-[11px] text-gray-500">
    <span class="flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5 text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
      </svg>
      <span class="font-medium text-gray-600">8.7M profiles</span>
    </span>
    <span class="flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5 text-pink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span class="font-medium text-gray-600">94.1% accuracy</span>
    </span>
    <div class="w-8 h-8 rounded-full bg-cyan-500 flex items-center justify-center text-white text-xs font-bold">{{ $av }}</div>
  </div>
</header>

{{-- SCROLLABLE BODY --}}
<div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

  {{-- KPI CARDS --}}
  <div class="grid grid-cols-4 gap-4">

    {{-- Profiles Resolved --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-emerald-400 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Profiles Resolved</p>
      <p class="text-[24px] font-bold text-emerald-400 leading-tight mt-1">8.7M</p>
      <p class="text-[11px] text-gray-400 mt-1.5">98.2% match</p>
    </div>

    {{-- Devices Linked --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-blue-400 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Devices Linked</p>
      <p class="text-[24px] font-bold text-blue-500 leading-tight mt-1">4.2M</p>
      <p class="text-[11px] text-gray-400 mt-1.5">+3.2M / month</p>
    </div>

    {{-- Anon → Known --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-violet-400 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Anon-Known</p>
      <p class="text-[24px] font-bold text-violet-500 leading-tight mt-1">8.7M</p>
      <p class="text-[11px] text-gray-400 mt-1.5">16% conversion</p>
    </div>

    {{-- Merge Conflicts --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-amber-400 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Merge Conflicts</p>
      <p class="text-[24px] font-bold text-amber-500 leading-tight mt-1">0.8%</p>
      <p class="text-[11px] text-gray-400 mt-1.5">-0.3%</p>
    </div>

  </div>

  {{-- MAIN CONTENT: two columns --}}
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

    {{-- LEFT: CROSS TOUCHPOINT IDENTITY STITCHING --}}
    <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:22px 24px">

      {{-- Section header --}}
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px">
        <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block"></span>
        <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#3b82f6;white-space:nowrap">Cross Touchpoint Identity Stitching</span>
        <span style="flex:1;height:1px;background:#bfdbfe;display:block"></span>
      </div>

      {{-- Touchpoint rows --}}
      @foreach($touchpoints as [$event, $id, $type])
      <div class="tp-grid">
        <span style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151">
          <span style="width:7px;height:7px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block"></span>
          {{ $event }}
        </span>
        <span class="tp-dash"></span>
        <span style="font-size:12px;color:#6b7280;white-space:nowrap">{{ $id }}</span>
        <span style="font-size:11px;color:#9ca3af;text-align:right;white-space:nowrap">{{ $type }}</span>
      </div>
      @endforeach

      {{-- Resolved user profile --}}
      <div style="margin-top:24px">
        <p style="font-size:28px;font-weight:400;color:#111827;line-height:1.2;letter-spacing:-0.3px">
          uid_88210 <span style="color:#374151;font-weight:400;margin:0 4px">•</span> Sarah Chen
        </p>
        <p style="font-size:12px;color:#9ca3af;margin-top:8px">
          3 devices &nbsp;&nbsp; 5 touchpoints &nbsp;&nbsp; 12 days &nbsp;&nbsp;
          <span style="color:#3b82f6;font-weight:500">identity score 98</span>
        </p>
      </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div style="display:flex;flex-direction:column;gap:16px">

      {{-- All Identity micro-Signals --}}
      <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:16px 20px">

        {{-- Header row --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <div style="display:flex;align-items:center;gap:7px">
            <span style="width:7px;height:7px;border-radius:50%;background:#3b82f6;display:inline-block"></span>
            <span style="font-size:12px;font-weight:500;color:#374151">All Identity micro-Signals</span>
          </div>
          <div style="display:flex;align-items:center;gap:6px;border:1px solid #e5e7eb;border-radius:8px;padding:4px 10px">
            <span style="font-size:12px;font-weight:500;color:#374151">14 Signals</span>
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
            </svg>
          </div>
        </div>

        {{-- 2-column signal grid --}}
        @php $rows = array_chunk($signals, 2); @endphp
        @foreach($rows as $row)
        <div class="sig-row">
          @foreach($row as $sig)
          <div class="sig-cell">
            <svg width="9" height="9" viewBox="0 0 10 10" fill="#3b82f6"><polygon points="5,0 10,5 5,10 0,5"/></svg>
            <span>{{ $sig }}</span>
          </div>
          @endforeach
        </div>
        @endforeach
      </div>

      {{-- 3 Donut Charts --}}
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
        @foreach($donuts as [$label, $stroke, $track])
        @php
          $r      = 44;
          $circ   = 2 * M_PI * $r;
          $filled = round($circ * 0.93, 2);
          $gap    = round($circ * 0.07, 2);
        @endphp
        <div style="border:1px solid #e5e7eb;border-radius:16px;background:#fff;padding:22px 10px;text-align:center">
          <div style="position:relative;width:120px;height:120px;margin:0 auto">
            <svg viewBox="0 0 120 120" width="120" height="120">
              <circle cx="60" cy="60" r="{{ $r }}" fill="none"
                      stroke="{{ $track }}" stroke-width="11"/>
              <circle cx="60" cy="60" r="{{ $r }}" fill="none"
                      stroke="{{ $stroke }}" stroke-width="11"
                      stroke-dasharray="{{ $filled }} {{ $gap }}"
                      stroke-linecap="round"
                      transform="rotate(-40 60 60)"/>
            </svg>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">
              <span style="font-size:22px;font-weight:600;color:{{ $stroke }}">98%</span>
            </div>
          </div>
          <p style="font-size:12px;color:#6b7280;margin-top:12px;font-weight:400">{{ $label }}</p>
        </div>
        @endforeach
      </div>

    </div>{{-- /right column --}}
  </div>

</div>{{-- /scrollable body --}}
</div>

@endsection
