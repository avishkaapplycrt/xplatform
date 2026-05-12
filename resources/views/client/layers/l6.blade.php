@extends('layouts.platform')
@section('title', 'Decision & Actions')

@push('styles')
<style>
  /* ── stat cards ── */
  .l6-card { flex:1; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:18px 20px 16px; }
  .l6-card-lbl { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#9ca3af; margin-top:10px; margin-bottom:5px; }
  .l6-card-val { font-size:38px; font-weight:700; line-height:1; }
  .l6-card-sub { font-size:11px; color:#9ca3af; margin-top:5px; }

  /* ── rule rows ── */
  .l6-rule-row { display:flex; align-items:flex-start; gap:10px; padding:9px 0; border-top:1px solid #f3f4f6; }
  .l6-rule-row:first-child { border-top:none; }
  .l6-rule-dot { width:9px; height:9px; border-radius:50%; background:#22c55e; flex-shrink:0; margin-top:3px; display:inline-block; }
  .l6-rule-name { font-size:12px; font-weight:600; color:#111827; line-height:1.3; }
  .l6-rule-cond { font-size:11px; color:#9ca3af; margin-top:2px; line-height:1.3; }
  .l6-fired-count { font-size:13px; font-weight:700; color:#f59e0b; white-space:nowrap; text-align:right; }
  .l6-fired-sub { font-size:10px; color:#9ca3af; white-space:nowrap; text-align:right; margin-top:1px; }

  /* ── micro-signal grid ── */
  .l6-sig-row { display:grid; grid-template-columns:1fr 1fr; border-bottom:1px solid #f3f4f6; }
  .l6-sig-row:last-child { border-bottom:none; }
  .l6-sig-cell { display:flex; align-items:center; gap:7px; padding:10px 0; font-size:12px; color:#3b82f6; }
  .l6-sig-cell:first-child { padding-right:10px; border-right:1px solid #f3f4f6; }
  .l6-sig-cell:last-child  { padding-left:10px; }

  /* ── approval row ── */
  .l6-approval-row { display:flex; align-items:center; justify-content:space-between; padding:11px 0; border-top:1px solid #f3f4f6; }
  .l6-approval-row:first-child { border-top:none; }
  .l6-approve-btn { width:26px; height:26px; border-radius:5px; background:#16a34a; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .l6-reject-btn  { background:none; border:none; cursor:pointer; color:#ef4444; font-size:16px; font-weight:700; line-height:1; padding:0 2px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));

  $rules = [
    ['name'=>'Win-Back VIP Users',       'cond'=>'Churn70% + LTV>$500', 'action'=>'Coupon + priority support', 'fired'=>'1,243'],
    ['name'=>'Route Unhappy Users',       'cond'=>'Churn70% + LTV>$500', 'action'=>'Coupon + priority support', 'fired'=>'1,243'],
    ['name'=>'Assign Hot Leads',          'cond'=>'Churn70% + LTV>$500', 'action'=>'Coupon + priority support', 'fired'=>'1,243'],
    ['name'=>'Suppress Converted Ads',    'cond'=>'Churn70% + LTV>$500', 'action'=>'Coupon + priority support', 'fired'=>'1,243'],
    ['name'=>'Reactivation Nudge',        'cond'=>'Churn70% + LTV>$500', 'action'=>'Coupon + priority support', 'fired'=>'1,243'],
    ['name'=>'Fraud Alert',               'cond'=>'Churn70% + LTV>$500', 'action'=>'Coupon + priority support', 'fired'=>'1,243'],
  ];

  $approvals = [
    ['label'=>'$50 voucher to 1,247 VIP at risk users', 'value'=>'$62K'],
    ['label'=>'$50 voucher to 1,247 VIP at risk users', 'value'=>'$62K'],
  ];

  $signals = array_fill(0, 14, 'loreum ipsum');
@endphp

<div class="flex flex-col h-full overflow-hidden" style="background:#f9fafb">

{{-- ── HEADER ── --}}
<header style="flex-shrink:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;">
  <div>
    <h1 style="font-size:16px;font-weight:600;color:#111827;margin:0">Decision & Actions</h1>
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
    <div class="l6-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="#4ade80">
        <path d="M13 2L3 14h8l-1 8 11-12h-8z"/>
      </svg>
      <div class="l6-card-lbl">Active Rules</div>
      <div class="l6-card-val" style="color:#4ade80">24</div>
      <div class="l6-card-sub">6 triggered today</div>
    </div>

    {{-- Actions Fired --}}
    <div class="l6-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="#3b82f6">
        <path d="M12 2L20.66 7V17L12 22L3.34 17V7Z"/>
      </svg>
      <div class="l6-card-lbl">Actions Fired</div>
      <div class="l6-card-val" style="color:#3b82f6">16K</div>
      <div class="l6-card-sub">+1.2%</div>
    </div>

    {{-- Pending Approvals --}}
    <div class="l6-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="16" rx="2"/>
        <path d="M16 2v4M8 2v4M3 10h18"/>
        <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>
      </svg>
      <div class="l6-card-lbl">Pending Approvals</div>
      <div class="l6-card-val" style="color:#8b5cf6">3</div>
      <div class="l6-card-sub">Human in loop</div>
    </div>

    {{-- Campaign ROI --}}
    <div class="l6-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
      </svg>
      <div class="l6-card-lbl">Campaign ROI</div>
      <div class="l6-card-val" style="color:#f59e0b">4.2X</div>
      <div class="l6-card-sub">+0.3x</div>
    </div>

  </div>

  {{-- BOTTOM TWO-COLUMN --}}
  <div style="display:flex;gap:14px;align-items:flex-start;flex:1;">

    {{-- LEFT: two stacked cards --}}
    <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:14px;">

      {{-- Card 1: Active Rule Workflows --}}
      <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;">

        {{-- Section header --}}
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
          <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block;"></span>
          <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#0d9488;">ACTIVE RULE WORKFLOWS</span>
          <span style="flex:1;height:1.5px;background:linear-gradient(to right,#99f6e4,transparent);display:block;"></span>
          <button style="background:#16a34a;color:#fff;font-size:11px;font-weight:700;border:none;border-radius:7px;padding:7px 16px;cursor:pointer;white-space:nowrap;letter-spacing:.02em;flex-shrink:0;">
            + NEW RULE
          </button>
        </div>

        {{-- Rules list --}}
        <div>
          @foreach($rules as $rule)
          <div class="l6-rule-row">
            <span class="l6-rule-dot"></span>
            <div style="flex:1;min-width:0;">
              <div class="l6-rule-name">{{ $rule['name'] }}</div>
              <div class="l6-rule-cond">IF: <span style="color:#f59e0b;">{{ $rule['cond'] }}</span><span style="color:#6b7280;"> →</span> <span style="color:#10b981;">{{ $rule['action'] }}</span></div>
            </div>
            <div style="flex-shrink:0;text-align:right;">
              <div class="l6-fired-count">{{ $rule['fired'] }}</div>
              <div class="l6-fired-sub">Fired Today</div>
            </div>
          </div>
          @endforeach
        </div>

      </div>

      {{-- Card 2: Human In-Loop Approvals --}}
      <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;">

        {{-- Section header --}}
        <div style="display:flex;align-items:center;gap:7px;margin-bottom:10px;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="#f59e0b" style="flex-shrink:0;">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
            <line x1="12" y1="17" x2="12.01" y2="17" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
          <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#f59e0b;">HUMAN IN-LOOP APPROVALS</span>
        </div>

        {{-- Approval rows --}}
        @foreach($approvals as $approval)
        <div class="l6-approval-row">
          <span style="font-size:12px;color:#374151;">{{ $approval['label'] }}</span>
          <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
            <span style="font-size:13px;font-weight:700;color:#f59e0b;">{{ $approval['value'] }}</span>
            <button class="l6-approve-btn" title="Approve">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </button>
            <button class="l6-reject-btn" title="Reject">&#x2715;</button>
          </div>
        </div>
        @endforeach

      </div>

    </div>

    {{-- RIGHT: ACTION MICRO SIGNALS --}}
    <div style="width:390px;flex-shrink:0;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;">

      {{-- Section header --}}
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
        <div style="display:flex;align-items:center;gap:7px;">
          <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block;"></span>
          <span style="font-size:13px;font-weight:600;color:#111827;">Action micro signals</span>
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
      <div class="l6-sig-row">
        <div class="l6-sig-cell">
          <span style="color:#3b82f6;font-size:9px;flex-shrink:0;">&#9670;</span>
          <span>{{ $pair[0] }}</span>
        </div>
        @if(isset($pair[1]))
        <div class="l6-sig-cell">
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
