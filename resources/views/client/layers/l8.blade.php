@extends('layouts.platform')
@section('title', 'Governance & Security')

@push('styles')
<style>
  /* ── stat cards ── */
  .l8-card { flex:1; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:18px 20px 16px; }
  .l8-card-lbl { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#9ca3af; margin-top:10px; margin-bottom:5px; }
  .l8-card-val { font-size:38px; font-weight:700; line-height:1; }
  .l8-card-sub { font-size:11px; color:#9ca3af; margin-top:5px; }

  /* ── governance signal grid ── */
  .l8-sig-row { display:grid; grid-template-columns:1fr 1fr; border-bottom:1px solid #f3f4f6; }
  .l8-sig-row:last-child { border-bottom:none; }
  .l8-sig-cell { display:flex; align-items:center; gap:7px; padding:10px 0; font-size:12px; color:#3b82f6; }
  .l8-sig-cell:first-child { padding-right:10px; border-right:1px solid #f3f4f6; }
  .l8-sig-cell:last-child  { padding-left:10px; }

  /* ── checklist items ── */
  .l8-check-row { display:flex; align-items:center; gap:8px; padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:12px; color:#374151; }
  .l8-check-row:last-child { border-bottom:none; }

  /* ── audit log table ── */
  .l8-tbl { width:100%; border-collapse:collapse; }
  .l8-tbl th { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; padding:0 8px 10px 0; text-align:left; white-space:nowrap; }
  .l8-tbl td { font-size:11px; padding:8px 8px 8px 0; border-top:1px solid #f3f4f6; vertical-align:middle; }
  .l8-tbl tbody tr:first-child td { border-top:none; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));

  $signals = array_fill(0, 14, 'loreum ipsum');

  $checkItems = array_fill(0, 7, 'RBAC');

  $auditLog = [
    ['time'=>'12:40:00', 'actor'=>'jane.doe', 'action'=>'MODEL_RETRAIN', 'resource'=>'churn_v4',   'tenant'=>'Acme'],
    ['time'=>'12:40:00', 'actor'=>'api_88',   'action'=>'PROFILE_READ',  'resource'=>'uuid_88210', 'tenant'=>'BankN'],
  ];

  $checkSections = ['Access & Identity', 'Data & Privacy', 'AI & Compliance'];
@endphp

<div class="flex flex-col h-full overflow-hidden" style="background:#f9fafb">

{{-- ── HEADER ── --}}
<header style="flex-shrink:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;">
  <div>
    <h1 style="font-size:16px;font-weight:600;color:#111827;margin:0">Governance & Security</h1>
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

    {{-- Security Score --}}
    <div class="l8-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="#4ade80">
        <path d="M13 2L3 14h8l-1 8 11-12h-8z"/>
      </svg>
      <div class="l8-card-lbl">Security Score</div>
      <div class="l8-card-val" style="color:#4ade80">24</div>
      <div class="l8-card-sub">ISO 87789 ready</div>
    </div>

    {{-- GDPR Compliance --}}
    <div class="l8-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="#3b82f6">
        <path d="M12 2L20.66 7V17L12 22L3.34 17V7Z"/>
      </svg>
      <div class="l8-card-lbl">GDPR Compliance</div>
      <div class="l8-card-val" style="color:#3b82f6">16K</div>
      <div class="l8-card-sub">Consent tracked</div>
    </div>

    {{-- Open Audit Items --}}
    <div class="l8-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="16" rx="2"/>
        <path d="M16 2v4M8 2v4M3 10h18"/>
        <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>
      </svg>
      <div class="l8-card-lbl">Open Audit Items</div>
      <div class="l8-card-val" style="color:#8b5cf6">3</div>
      <div class="l8-card-sub">All resolved</div>
    </div>

    {{-- Uptime SLA --}}
    <div class="l8-card">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
      </svg>
      <div class="l8-card-lbl">Uptime SLA</div>
      <div class="l8-card-val" style="color:#f59e0b">4.2X</div>
      <div class="l8-card-sub">+0.3% vs target</div>
    </div>

  </div>

  {{-- BOTTOM TWO-COLUMN --}}
  <div style="display:flex;gap:14px;align-items:stretch;">

    {{-- LEFT: Governance Signals + 3 Checklist Columns --}}
    <div style="flex:1;min-width:0;max-width:50%;display:flex;flex-direction:column;gap:14px;">

      {{-- Governance Signals Panel --}}
      <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;">

        {{-- Panel header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
          <div style="display:flex;align-items:center;gap:7px;">
            <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block;"></span>
            <span style="font-size:13px;font-weight:600;color:#111827;">Governance signals</span>
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
        <div class="l8-sig-row">
          <div class="l8-sig-cell">
            <span style="color:#3b82f6;font-size:9px;flex-shrink:0;">&#9670;</span>
            <span>{{ $pair[0] }}</span>
          </div>
          @if(isset($pair[1]))
          <div class="l8-sig-cell">
            <span style="color:#3b82f6;font-size:9px;flex-shrink:0;">&#9670;</span>
            <span>{{ $pair[1] }}</span>
          </div>
          @endif
        </div>
        @endforeach

      </div>

      {{-- 3 Checklist Columns --}}
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
        @foreach($checkSections as $section)
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;">

          {{-- Section title --}}
          <div style="display:flex;align-items:center;gap:6px;margin-bottom:10px;">
            <span style="width:7px;height:7px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block;"></span>
            <span style="font-size:12px;font-weight:700;color:#111827;line-height:1.3;">{{ $section }}</span>
          </div>

          {{-- Checklist items --}}
          @foreach($checkItems as $item)
          <div class="l8-check-row">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span>{{ $item }}</span>
          </div>
          @endforeach

        </div>
        @endforeach
      </div>

    </div>

    {{-- RIGHT: MODEL REGISTRY Audit Table --}}
    <div style="flex:1;min-width:0;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;display:flex;flex-direction:column;">

      {{-- Section header --}}
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
        <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block;"></span>
        <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#0d9488;">MODEL REGISTRY</span>
        <span style="flex:1;height:1.5px;background:linear-gradient(to right,#99f6e4,transparent);display:block;"></span>
      </div>

      {{-- Audit log table --}}
      <table class="l8-tbl">
        <thead>
          <tr>
            <th>Time</th>
            <th>Actor</th>
            <th>Action</th>
            <th>Resource</th>
            <th>Tenant</th>
            <th>Result</th>
          </tr>
        </thead>
        <tbody>
          @foreach($auditLog as $log)
          <tr>
            <td style="color:#9ca3af;white-space:nowrap;font-size:11px;">{{ $log['time'] }}</td>
            <td style="color:#374151;">{{ $log['actor'] }}</td>
            <td style="color:#374151;font-weight:500;white-space:nowrap;">{{ $log['action'] }}</td>
            <td style="color:#374151;">{{ $log['resource'] }}</td>
            <td style="color:#374151;">{{ $log['tenant'] }}</td>
            <td>
              <span style="display:inline-flex;align-items:center;gap:4px;color:#10b981;font-weight:600;font-size:11px;">
                <span style="width:5px;height:5px;border-radius:50%;background:#10b981;display:inline-block;flex-shrink:0;"></span>LIVE
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

    </div>

  </div>

</div>{{-- /scrollable body --}}
</div>

@endsection
