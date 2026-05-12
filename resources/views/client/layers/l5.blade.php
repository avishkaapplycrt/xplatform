@extends('layouts.platform')
@section('title', 'AI/ML Predictions')

@push('styles')
<style>
  /* ── stat cards ── */
  .l5-card { flex:1; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:18px 20px 16px; }
  .l5-card-lbl { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#9ca3af; margin-top:10px; margin-bottom:5px; }
  .l5-card-val { font-size:38px; font-weight:700; line-height:1; }
  .l5-card-sub { font-size:11px; color:#9ca3af; margin-top:5px; }

  /* ── model table ── */
  .l5-tbl { width:100%; border-collapse:collapse; }
  .l5-tbl th { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; padding:0 6px 11px 0; text-align:left; white-space:nowrap; }
  .l5-tbl td { font-size:12px; padding:9px 6px 9px 0; border-top:1px solid #f3f4f6; vertical-align:middle; }
  .l5-tbl tbody tr:first-child td { border-top:none; }
  .l5-type { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; }
  .l5-live  { font-size:11px; font-weight:600; color:#10b981; }
  .l5-act   { font-size:10px; color:#6b7280; border:1px solid #e5e7eb; border-radius:4px; padding:3px 9px; background:#fff; cursor:pointer; white-space:nowrap; }
  .l5-act:hover { background:#f9fafb; }

  /* ── micro-signal grid ── */
  .l5-sig-row { display:grid; grid-template-columns:1fr 1fr; border-bottom:1px solid #f3f4f6; }
  .l5-sig-row:last-child { border-bottom:none; }
  .l5-sig-cell { display:flex; align-items:center; gap:7px; padding:10px 0; font-size:12px; color:#3b82f6; }
  .l5-sig-cell:first-child { padding-right:10px; border-right:1px solid #f3f4f6; }
  .l5-sig-cell:last-child  { padding-left:10px; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));

  $models = [
    ['name'=>'Churn Prediction',      'type'=>'CLASSIFICATION', 'tc'=>'#10b981'],
    ['name'=>'Purchase Propensity',   'type'=>'CLASSIFICATION', 'tc'=>'#10b981'],
    ['name'=>'Upsell Probability',    'type'=>'REGRESSION',     'tc'=>'#3b82f6'],
    ['name'=>'LTV Forcasting',        'type'=>'REGRESSION',     'tc'=>'#3b82f6'],
    ['name'=>'Subscription Renewal',  'type'=>'CLASSIFICATION', 'tc'=>'#10b981'],
    ['name'=>'Support Escalation',    'type'=>'SEQUENCE',       'tc'=>'#f59e0b'],
    ['name'=>'Fraud / Anomaly',       'type'=>'ANOMALY',        'tc'=>'#ef4444'],
    ['name'=>'Sentinent / NLP',       'type'=>'NLP',            'tc'=>'#10b981'],
    ['name'=>'Next-Best_action LLm',  'type'=>'LLM',            'tc'=>'#8b5cf6'],
    ['name'=>'Lead Qualification',    'type'=>'CLASSIFICATION', 'tc'=>'#10b981'],
    ['name'=>'Recommendation Engine', 'type'=>'RECOMMENDER',    'tc'=>'#f97316'],
  ];

  $signals = array_fill(0, 14, 'loreum ipsum');
@endphp

<div class="flex flex-col h-full overflow-hidden" style="background:#f9fafb">

{{-- ── HEADER ── --}}
<header style="flex-shrink:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;">
  <div>
    <h1 style="font-size:16px;font-weight:600;color:#111827;margin:0">AI/ML Predictions</h1>
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

    {{-- Active Models --}}
    <div class="l5-card">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="#4ade80">
        <path d="M13 2L3 14h8l-1 8 11-12h-8z"/>
      </svg>
      <div class="l5-card-lbl">Active Models</div>
      <div class="l5-card-val" style="color:#4ade80">11</div>
      <div class="l5-card-sub">1 in beta</div>
    </div>

    {{-- Avg Accuracy --}}
    <div class="l5-card">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="#3b82f6">
        <path d="M12 2L20.66 7V17L12 22L3.34 17V7Z"/>
      </svg>
      <div class="l5-card-lbl">Avg Accuracy</div>
      <div class="l5-card-val" style="color:#3b82f6">87.8%</div>
      <div class="l5-card-sub">+1.2% MoM</div>
    </div>

    {{-- Predictions/Day --}}
    <div class="l5-card">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <ellipse cx="12" cy="5" rx="9" ry="3"/>
        <path d="M3 5v6c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/>
        <path d="M3 11v6c0 1.66 4.03 3 9 3s9-1.34 9-3v-6"/>
      </svg>
      <div class="l5-card-lbl">Predictions/Day</div>
      <div class="l5-card-val" style="color:#8b5cf6">620K</div>
      <div class="l5-card-sub">+18%</div>
    </div>

    {{-- Drift Alerts --}}
    <div class="l5-card">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
      </svg>
      <div class="l5-card-lbl">Drift Alerts</div>
      <div class="l5-card-val" style="color:#f59e0b">0</div>
      <div class="l5-card-sub">All Nominal</div>
    </div>

  </div>

  {{-- BOTTOM TWO-COLUMN --}}
  <div style="display:flex;gap:14px;align-items:flex-start;flex:1;">

    {{-- LEFT: MODEL REGISTRY --}}
    <div style="flex:1;min-width:0;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:18px 22px;">

      {{-- Section header --}}
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
        <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block;"></span>
        <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#0d9488;">MODEL REGISTRY</span>
        <span style="flex:1;height:1.5px;background:linear-gradient(to right,#99f6e4,transparent);display:block;max-width:120px;"></span>
      </div>

      {{-- Table --}}
      <table class="l5-tbl">
        <thead>
          <tr>
            <th style="width:34%">Model</th>
            <th style="width:22%">Type</th>
            <th style="width:14%">Accuracy</th>
            <th style="width:14%">Status</th>
            <th style="width:16%">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($models as $m)
          <tr>
            <td style="color:#111827;font-weight:500">{{ $m['name'] }}</td>
            <td><span class="l5-type" style="color:{{ $m['tc'] }}">{{ $m['type'] }}</span></td>
            <td style="color:#6b7280">76.3%</td>
            <td><span class="l5-live">+LIVE</span></td>
            <td>
              <div style="display:flex;gap:4px;">
                <button class="l5-act">Inspect</button>
                <button class="l5-act">Restrain</button>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

    </div>

    {{-- RIGHT: PREDICTION MICRO SIGNALS --}}
    <div style="width:390px;flex-shrink:0;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;">

      {{-- Section header --}}
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
        <div style="display:flex;align-items:center;gap:7px;">
          <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block;"></span>
          <span style="font-size:13px;font-weight:600;color:#111827;">Prediction micro signals</span>
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
      <div class="l5-sig-row">
        <div class="l5-sig-cell">
          <span style="color:#3b82f6;font-size:9px;flex-shrink:0;">&#9670;</span>
          <span>{{ $pair[0] }}</span>
        </div>
        @if(isset($pair[1]))
        <div class="l5-sig-cell">
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
