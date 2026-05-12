@extends('layouts.platform')
@section('title', 'Data Processing')

@push('styles')
<style>
  .sig-row  { display:grid; grid-template-columns:1fr 1fr; border-bottom:1px solid #dbeafe; }
  .sig-row:last-child { border-bottom:none; }
  .sig-cell { display:flex; align-items:center; gap:7px; padding:10px 0; font-size:12px; color:#3b82f6; }
  .store-row { display:flex; align-items:center; gap:10px; padding:13px 0; border-bottom:1px solid #f3f4f6; }
  .store-row:last-child { border-bottom:none; }
  .pipe-row  { display:flex; align-items:center; gap:8px; padding:11px 0; border-bottom:1px solid #f3f4f6; font-size:12px; color:#374151; }
  .pipe-row:last-child { border-bottom:none; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));

  $signals = array_fill(0, 14, 'lorem ipsum');

  $stores = [
    ['Event Store',    'Raw activity logo',            '1.2TB',  '#10b981'],
    ['Relational db',  'platform metadata + tenants',  '1.2TB',  '#3b82f6'],
    ['Data Warehouse', 'large-scale analytics',        '1.2TB',  '#14b8a6'],
    ['feature store',  'ML-ready feature',             '1.2TB',  '#f59e0b'],
    ['Profile store',  'Unified customer 360*',        '1.2TB',  '#f97316'],
    ['Vector Store',   'Semantic behaviour + text',    '340 GB', '#ef4444'],
  ];
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

{{-- HEADER --}}
<header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
  <div>
    <h1 class="text-[16px] font-semibold text-gray-900">Data Processing</h1>
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

    {{-- Throughput --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-emerald-400 mb-3" fill="currentColor" viewBox="0 0 24 24">
        <path d="M13 2L3 14h8l-1 8 11-14h-8z"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Throughput</p>
      <p class="text-[24px] font-bold text-emerald-400 leading-tight mt-1">62K/s</p>
      <p class="text-[11px] text-gray-400 mt-1.5">peak 89K/s</p>
    </div>

    {{-- Batch Jobs/Day --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-blue-400 mb-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Batch Jobs/Day</p>
      <p class="text-[24px] font-bold text-blue-500 leading-tight mt-1">248</p>
      <p class="text-[11px] text-gray-400 mt-1.5">100% success</p>
    </div>

    {{-- Feature Store --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-violet-400 mb-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7c0-1.1 3.6-2 8-2s8 .9 8 2v2c0 1.1-3.6 2-8 2s-8-.9-8-2V7z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 11v4c0 1.1 3.6 2 8 2s8-.9 8-2v-4"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 15v4c0 1.1 3.6 2 8 2s8-.9 8-2v-4"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Feature Store</p>
      <p class="text-[24px] font-bold text-violet-500 leading-tight mt-1">2.1TB</p>
      <p class="text-[11px] text-gray-400 mt-1.5">+120GB/wk</p>
    </div>

    {{-- Query P99 --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-amber-400 mb-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Querry P99</p>
      <p class="text-[24px] font-bold text-amber-500 leading-tight mt-1">18ms</p>
      <p class="text-[11px] text-gray-400 mt-1.5">-4 ms</p>
    </div>

  </div>

  {{-- MAIN CONTENT: Left + Right columns --}}
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

    {{-- LEFT COLUMN --}}
    <div style="display:flex;flex-direction:column;gap:16px">

      {{-- Pipeline Micro Signals --}}
      <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:16px 20px">

        {{-- Header --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <div style="display:flex;align-items:center;gap:7px">
            <span style="width:7px;height:7px;border-radius:50%;background:#10b981;display:inline-block"></span>
            <span style="font-size:12px;font-weight:500;color:#374151">Pipe line micro signals</span>
          </div>
          <div style="display:flex;align-items:center;gap:6px;border:1px solid #e5e7eb;border-radius:8px;padding:4px 10px">
            <span style="font-size:12px;font-weight:500;color:#374151">14 Signals</span>
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
            </svg>
          </div>
        </div>

        {{-- Signal grid --}}
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

      {{-- Pipeline Cards --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- Real-Time Pipeline --}}
        <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:18px 20px">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
            <h3 style="font-size:18px;font-weight:400;color:#111827;line-height:1.3">Real-Time<br>Pipeline</h3>
            <span style="font-size:10px;font-weight:600;color:#10b981;white-space:nowrap">&lt; 100MS</span>
          </div>
          @foreach(array_fill(0, 6, 'Kafka event bus') as $item)
          <div class="pipe-row">
            <span style="width:6px;height:6px;border-radius:50%;background:#10b981;flex-shrink:0;display:inline-block"></span>
            <span>{{ $item }}</span>
          </div>
          @endforeach
        </div>

        {{-- Batch Pipeline --}}
        <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:18px 20px">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
            <h3 style="font-size:18px;font-weight:400;color:#111827;line-height:1.3">Batch<br>Pipeline</h3>
            <span style="font-size:10px;font-weight:600;color:#10b981;white-space:nowrap">HOURLY / DAILY</span>
          </div>
          @foreach(array_fill(0, 6, 'Kafka event bus') as $item)
          <div class="pipe-row">
            <span style="width:6px;height:6px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block"></span>
            <span>{{ $item }}</span>
          </div>
          @endforeach
        </div>

      </div>
    </div>

    {{-- RIGHT COLUMN: Storage Architectural Types --}}
    <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:20px 24px">

      {{-- Section header --}}
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px">
        <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block"></span>
        <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#3b82f6;white-space:nowrap">6 Storage Architectural Type</span>
        <span style="flex:1;height:1px;background:#bfdbfe;display:block"></span>
      </div>

      {{-- Storage rows --}}
      @foreach($stores as [$name, $sub, $size, $color])
      <div class="store-row">
        <span style="width:9px;height:9px;border-radius:50%;background:{{ $color }};flex-shrink:0;display:inline-block"></span>
        <div style="flex:1;min-width:0">
          <p style="font-size:13px;font-weight:500;color:#111827;line-height:1.2">{{ $name }}</p>
          <p style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $sub }}</p>
        </div>
        <span style="font-size:13px;font-weight:600;color:{{ $color }};flex-shrink:0">{{ $size }}</span>
      </div>
      @endforeach

    </div>

  </div>

</div>{{-- /scrollable body --}}
</div>

@endsection
