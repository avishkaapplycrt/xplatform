@extends('layouts.platform')
@section('title', 'Application Layer')

@push('styles')
<style>
  /* ── stat cards ── */
  .l7-card { flex:1; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:18px 20px 16px; }
  .l7-card-lbl { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#9ca3af; margin-top:10px; margin-bottom:5px; }
  .l7-card-val { font-size:38px; font-weight:700; line-height:1; }
  .l7-card-sub { font-size:11px; color:#9ca3af; margin-top:5px; }

  /* ── app module cards ── */
  .l7-module-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .l7-module-card {
    display:flex; align-items:center; gap:13px;
    background:#fff; border:1px solid #e5e7eb; border-radius:9px;
    padding:13px 16px; cursor:pointer; transition:box-shadow .15s, border-color .15s;
  }
  .l7-module-card:hover { box-shadow:0 2px 8px rgba(59,130,246,.10); border-color:#bfdbfe; }
  .l7-module-icon {
    width:36px; height:36px; border-radius:8px; background:#3b82f6;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
  }
  .l7-module-name { font-size:12px; font-weight:600; color:#111827; line-height:1.35; }
  .l7-module-sub  { font-size:10px; color:#9ca3af; margin-top:2px; line-height:1.35; }

  /* ── micro-signal grid ── */
  .l7-sig-row { display:grid; grid-template-columns:1fr 1fr; border-bottom:1px solid #f3f4f6; }
  .l7-sig-row:last-child { border-bottom:none; }
  .l7-sig-cell { display:flex; align-items:center; gap:7px; padding:10px 0; font-size:12px; color:#3b82f6; }
  .l7-sig-cell:first-child { padding-right:10px; border-right:1px solid #f3f4f6; }
  .l7-sig-cell:last-child  { padding-left:10px; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));

  $modules = [
    ['name'=>'Executive Dashboard',       'sub'=>'KPIs, trends, health metrics'],
    ['name'=>'Customer 360* profile',     'sub'=>'Full behavioral timeline per user'],
    ['name'=>'Audience Segmentation',     'sub'=>'Dynamic clustering + cohort builder'],
    ['name'=>'Funnel & Journey',          'sub'=>'Drop-off, stage conversion, paths'],
    ['name'=>'Prediction Center',         'sub'=>'Model scores + forecast charts'],
    ['name'=>'Alerts & Anomaly',          'sub'=>'Real time anomaly detection'],
    ['name'=>'Campaign / Rec Center',     'sub'=>'Next-best-action launcher'],
    ['name'=>'Report builder',            'sub'=>'Drag and Drop Analytics'],
    ['name'=>'API Management',            'sub'=>'Keys, webhooks, rate limits'],
    ['name'=>'Admin + Tenant Settings',   'sub'=>'Multi-tenant config + RBAC'],
  ];

  $signals = array_fill(0, 14, 'loreum ipsum');
@endphp

<div class="flex flex-col h-full overflow-hidden" style="background:#f9fafb">

{{-- ── HEADER ── --}}
<header style="flex-shrink:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;">
  <div>
    <h1 style="font-size:16px;font-weight:600;color:#111827;margin:0">Application Layer</h1>
    <p style="font-size:11px;color:#6b7280;margin:2px 0 0">
      Tenant: <span style="color:#0d9488;font-weight:500">{{ $cn }}</span>
      <span style="margin-left:8px;display:inline-flex;align-items:center;gap:4px;color:#16a34a;font-weight:500;font-size:10px">
        <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block"></span>Live
      </span>
    </p>
  </div>
  <div style="display:flex;align-items:center;gap:20px;font-size:11px;color:#6b7280">
    <span style="display:flex;align-items:center;gap:5px">
      <svg width="14" height="14" fill="none" stroke="#a78bfa" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
      </svg>
      <span style="font-weight:500;color:#374151">8.7M profiles</span>
    </span>
    <span style="display:flex;align-items:center;gap:5px">
      <svg width="14" height="14" fill="none" stroke="#f472b6" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span style="font-weight:500;color:#374151">94.1% accuracy</span>
    </span>
    <div style="width:32px;height:32px;border-radius:50%;background:#06b6d4;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700">{{ $av }}</div>
  </div>
</header>

{{-- ── SCROLLABLE BODY ── --}}
<div style="flex:1;overflow-y:auto;padding:16px 20px;display:flex;flex-direction:column;gap:16px;">

  {{-- STAT CARDS ROW --}}
  <div style="display:flex;gap:14px;">

    {{-- Active Rules --}}
    <div class="l7-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="#4ade80">
        <path d="M13 2L3 14h8l-1 8 11-12h-8z"/>
      </svg>
      <div class="l7-card-lbl">Active Rules</div>
      <div class="l7-card-val" style="color:#4ade80">24</div>
      <div class="l7-card-sub">6 triggered today</div>
    </div>

    {{-- Actions Fired --}}
    <div class="l7-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="#3b82f6">
        <path d="M12 2L20.66 7V17L12 22L3.34 17V7Z"/>
      </svg>
      <div class="l7-card-lbl">Actions Fired</div>
      <div class="l7-card-val" style="color:#3b82f6">16K</div>
      <div class="l7-card-sub">+1.2%</div>
    </div>

    {{-- Pending Approvals --}}
    <div class="l7-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="16" rx="2"/>
        <path d="M16 2v4M8 2v4M3 10h18"/>
        <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>
      </svg>
      <div class="l7-card-lbl">Pending Approvals</div>
      <div class="l7-card-val" style="color:#8b5cf6">3</div>
      <div class="l7-card-sub">Human in loop</div>
    </div>

    {{-- Campaign ROI --}}
    <div class="l7-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
      </svg>
      <div class="l7-card-lbl">Campaign ROI</div>
      <div class="l7-card-val" style="color:#f59e0b">4.2X</div>
      <div class="l7-card-sub">+0.3x</div>
    </div>

  </div>

  {{-- BOTTOM TWO-COLUMN --}}
  <div style="display:flex;gap:14px;align-items:flex-start;flex:1;">

    {{-- LEFT: APP MODULE CARDS GRID --}}
    <div style="flex:1;min-width:0;">
      <div class="l7-module-grid">
        @foreach($modules as $mod)
        <div class="l7-module-card">
          <div class="l7-module-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="20" x2="18" y2="10"/>
              <line x1="12" y1="20" x2="12" y2="4"/>
              <line x1="6"  y1="20" x2="6"  y2="14"/>
            </svg>
          </div>
          <div>
            <div class="l7-module-name">{{ $mod['name'] }}</div>
            <div class="l7-module-sub">{{ $mod['sub'] }}</div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- RIGHT: PLATFORM USAGE MICRO SIGNALS --}}
    <div style="width:390px;flex-shrink:0;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;">

      {{-- Section header --}}
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
        <div style="display:flex;align-items:center;gap:7px;">
          <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block;"></span>
          <span style="font-size:13px;font-weight:600;color:#111827;">Platform Usage Micro Signals</span>
        </div>
        <div style="display:flex;align-items:center;gap:5px;">
          <span style="font-size:13px;font-weight:700;color:#111827;">14 Signals</span>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
          </svg>
        </div>
      </div>

      {{-- Signal grid --}}
      @php $chunks = array_chunk($signals, 2); @endphp
      @foreach($chunks as $pair)
      <div class="l7-sig-row">
        <div class="l7-sig-cell">
          <span style="color:#3b82f6;font-size:9px;flex-shrink:0;">&#9670;</span>
          <span>{{ $pair[0] }}</span>
        </div>
        @if(isset($pair[1]))
        <div class="l7-sig-cell">
          <span style="color:#3b82f6;font-size:9px;flex-shrink:0;">&#9670;</span>
          <span>{{ $pair[1] }}</span>
        </div>
        @endif
      </div>
      @endforeach

    </div>

  </div>

</div>{{-- /scrollable body --}}
</div>

@endsection
