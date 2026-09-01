@extends('layouts.platform')
@section('title', 'Data Collection')

@push('styles')
<style>
  .src { border:1px solid #e5e7eb; border-radius:10px; background:#fff; }
  .src-link { display:flex; align-items:center; gap:10px; flex:1; min-width:0; padding:16px 18px; text-decoration:none; border-radius:9px 0 0 9px; cursor:pointer; transition:background .15s, box-shadow .15s; }
  .src-link:hover { background:#f0fdfa; }
  .src.active .src-link { background:#f0fdfa; box-shadow: inset 0 0 0 2px #14b8a6; }
  .src-add { position:relative; display:flex; align-items:center; justify-content:center; flex-shrink:0; width:44px; border-left:1px solid #e5e7eb; color:#9ca3af; text-decoration:none; border-radius:0 9px 9px 0; transition:color .15s, background .15s, border-color .15s; }
  .src-add:hover { color:#0d9488; background:#f0fdfa; border-left-color:#5eead4; }
  .src-tip { position:absolute; bottom:calc(100% + 8px); right:0; background:#111827; color:#fff; font-size:11px; font-weight:500; line-height:1; white-space:nowrap; padding:6px 10px; border-radius:6px; opacity:0; visibility:hidden; transform:translateY(4px); transition:opacity .15s, transform .15s, visibility .15s; pointer-events:none; z-index:20; }
  .src-tip::after { content:""; position:absolute; top:100%; right:14px; border:5px solid transparent; border-top-color:#111827; }
  .src-add:hover .src-tip { opacity:1; visibility:visible; transform:translateY(0); }
  .tab-btn { padding:10px 16px; font-size:11px; white-space:nowrap; border-bottom:2px solid transparent; color:#9ca3af; cursor:pointer; background:none; border-top:none; border-left:none; border-right:none; }
  .tab-btn.active { border-bottom-color:#111827; color:#111827; font-weight:600; }
  .panel { display:none; }
  .panel.active { display:block; }
  .bar-wrap { width:100%; height:6px; background:#f3f4f6; border-radius:9999px; overflow:hidden; margin-top:6px; }
  .bar-fill { height:6px; border-radius:9999px; transition:width .5s ease; }
  .mini { border:1px solid #e2e8f0; border-radius:10px; background:#fff; padding:14px 10px; text-align:center; }
  .section-header { display:flex; align-items:center; gap:8px; margin-bottom:16px; }
  .section-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
  .section-line { flex:1; height:1px; background:#bfdbfe; }
  .section-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#3b82f6; white-space:nowrap; }
  .panel-card { border:1px solid #edf0f2; border-radius:12px; background:#fff; padding:16px; }
  .globe-panel { padding:40px 20px; text-align:center; }
  .teal-section-dot { background:#10b981; }
  .teal-section-line { background:#a7f3d0; }
  .teal-section-title { color:#10b981; }
  .violet-section-dot { background:#8b5cf6; }
  .violet-section-line { background:#ddd6fe; }
  .violet-section-title { color:#8b5cf6; }
  .crm-col-hdr { font-size:10px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em; }
  .crm-stage  { font-size:13px;color:#111827;font-weight:500; }
  .crm-days   { font-size:14px;font-weight:700;color:#8b5cf6;text-align:right; }
  .crm-deals  { font-size:12px;color:#9ca3af;text-align:right; }

  /* Email Engagement grid (live Brevo data) */
  .be-header { display:flex; align-items:center; justify-content:space-between; padding:14px 24px; border-bottom:1px solid #e5e7eb; }
  .be-header-title { display:flex; align-items:center; gap:10px; }
  .be-header-icon { width:28px; height:28px; border-radius:8px; background:#e6f6f0; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .be-synced-badge { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:#6b7280; background:#f9fafb; border:1px solid #eef0f2; border-radius:999px; padding:4px 10px; }
  .be-sync-btn { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#0b996e; background:#e6f6f0; border:1px solid #cdeee2; border-radius:999px; padding:5px 11px; cursor:pointer; transition:background .15s; }
  .be-sync-btn:hover { background:#d7f2e7; }
  .be-sync-btn:disabled { opacity:.6; cursor:default; }
  .be-sync-btn svg { flex-shrink:0; }
  .be-sync-btn.syncing svg { animation:be-spin 1s linear infinite; }
  @keyframes be-spin { to { transform:rotate(360deg); } }
  .be-synced-dot { width:6px; height:6px; border-radius:50%; background:#0b996e; flex-shrink:0; box-shadow:0 0 0 3px #0b996e22; }
  .be-grid { display:grid; grid-template-columns:repeat(4,1fr); }
  @media (max-width: 760px) { .be-grid { grid-template-columns:repeat(2,1fr); } }
  .be-cell { padding:20px 22px; border-right:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb; transition:background .15s; }
  .be-cell:hover { background:#fafbfc; }
  .be-cell:nth-child(4) { border-right:none; }
  @media (max-width: 760px) { .be-cell:nth-child(2n) { border-right:none; } }
  .be-icon-wrap { width:26px; height:26px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-bottom:12px; }
  .be-gauge { position:relative; width:56px; height:56px; flex-shrink:0; margin-bottom:12px; }
  .be-gauge canvas { display:block; width:56px !important; height:56px !important; }
  .be-gauge-icon { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; }
  .be-label { display:flex; align-items:center; gap:5px; font-size:11px; color:#9ca3af; font-weight:700; text-transform:uppercase; letter-spacing:.05em; }
  .be-label .be-hint { width:14px; height:14px; border-radius:50%; border:1px solid #d1d5db; color:#9ca3af; font-size:9px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; cursor:help; flex-shrink:0; text-transform:none; letter-spacing:0; }
  .be-value-row { display:flex; align-items:center; gap:10px; margin-top:8px; }
  .be-value { font-size:25px; font-weight:800; color:#111827; line-height:1; letter-spacing:-.3px; }
  .be-view { display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:700; padding:3px 9px; border-radius:999px; cursor:pointer; border:none; transition:filter .15s, transform .15s; }
  .be-view:hover { filter:brightness(.94); transform:translateY(-1px); }
  .be-sub-label { font-size:11px; color:#9ca3af; font-weight:500; margin-top:18px; }
  .be-sub-value { display:inline-flex; font-size:12px; font-weight:800; padding:3px 10px; border-radius:999px; margin-top:6px; }
  .be-row2 { border-top:none; padding:18px 22px; background:linear-gradient(180deg,#fff,#fef6f6); }
  .be-modal-overlay { position:fixed; inset:0; background:rgba(17,24,39,.45); display:flex; align-items:center; justify-content:center; z-index:100; }
  .be-modal { background:#fff; border-radius:14px; width:420px; max-width:92vw; max-height:80vh; overflow:hidden; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,.25); transition:width .15s; }
  .be-modal.be-modal-wide { width:720px; }
  .be-modal-row { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 20px; border-bottom:1px solid #f3f4f6; }
  .be-modal-row:last-child { border-bottom:none; }
  .be-empty-icon { width:56px; height:56px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

{{-- HEADER --}}
<header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
  <div>
    <h1 class="text-[16px] font-semibold text-gray-900">Data Collection</h1>
    <p class="text-[11px] text-gray-500 mt-0.5">
      Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
      <span class="ml-2 inline-flex items-center gap-1 text-green-600 font-medium text-[10px]">
        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Live
      </span>
    </p>
  </div>
  <div class="flex items-center gap-4 text-[11px] text-gray-500">
    {{-- Home button --}}
    <a href="{{ route('client.dashboard') }}"
       class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
       title="Home">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
    </a>

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

    {{-- Avatar with dropdown --}}
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
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Log Out
          </button>
        </form>
      </div>
    </div>
  </div>
</header>

{{-- SCROLLABLE BODY --}}
<div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

  {{-- KPI CARDS --}}
  <div class="grid grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-emerald-400 mb-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l2.09 4.26L19 7.27l-3.5 3.41.83 4.82L12 13.25l-4.33 2.25.83-4.82L5 7.27l4.91-.01z"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Total Micro-Signals</p>
      <p class="text-[24px] font-bold text-emerald-400 leading-tight mt-1">150+</p>
      <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
        <span class="text-emerald-500 text-xs">▲</span> Across all 8 layers
      </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-amber-400 mb-3" fill="currentColor" viewBox="0 0 24 24">
        <path d="M13 2L3 14h8l-1 8 11-14h-8z"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Events / Day</p>
      <p class="text-[24px] font-bold text-blue-500 leading-tight mt-1">4.2M</p>
      <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
        <span class="text-emerald-500 text-xs">▲</span> 18% WoW
      </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-violet-400 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Profiles Resolved</p>
      <p class="text-[24px] font-bold text-violet-500 leading-tight mt-1">8.7M</p>
      <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
        <span class="text-emerald-500 text-xs">▲</span> 98.2% match rate
      </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
      <svg class="w-[18px] h-[18px] text-gray-400 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
      </svg>
      <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Active Tenants</p>
      <p class="text-[24px] font-bold text-amber-500 leading-tight mt-1">4</p>
      <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
        <span class="text-emerald-500 text-xs">▲</span> 2 added this Q
      </p>
    </div>
  </div>

  {{-- SOURCE CARDS --}}
  @php
  $sources = [
    ['id'=>'website',      'name'=>'Website Events',     'value'=>'840',   'unit'=>'messages'],
    ['id'=>'email',        'name'=>'Email Engagement',   'value'=>'1,900', 'unit'=>'opens'],
    ['id'=>'crm',          'name'=>'CRM Data',           'value'=>'3200',  'unit'=>'syncs'],
    ['id'=>'social',       'name'=>'Social Signals',     'value'=>'500',   'unit'=>'mentions'],
    ['id'=>'chat',         'name'=>'Chat & Support',     'value'=>'840',   'unit'=>'messages'],
    ['id'=>'transactions', 'name'=>'Transactions',       'value'=>'1540',  'unit'=>'payments'],
  ];
  @endphp
  <div class="grid grid-cols-3 gap-4">
    @foreach($sources as $s)
    @if($s['id'] === 'website')
    <div id="src-website" class="src active" style="padding:0; display:flex; align-items:stretch;">
      <div class="src-link" onclick="selectSource('website')">
        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[13px] font-semibold text-gray-700 truncate leading-none">{{ $s['name'] }}</p>
        </div>
      </div>
      <a href="{{ route('client.website-connections') }}" class="src-add">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="src-tip">Add Website provider</span>
      </a>
    </div>
    @elseif($s['id'] === 'email')
    <div id="src-email" class="src" style="padding:0; display:flex; align-items:stretch;">
      <div class="src-link" onclick="selectSource('email')">
        <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[13px] font-semibold text-gray-700 truncate leading-none">{{ $s['name'] }}</p>
        </div>
      </div>
      <a href="{{ route('client.email-connections') }}" class="src-add">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="src-tip">Add Email provider</span>
      </a>
    </div>
    @elseif($s['id'] === 'crm')
    <div id="src-crm" class="src" style="padding:0; display:flex; align-items:stretch;">
      <div class="src-link" onclick="selectSource('crm')">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[13px] font-semibold text-gray-700 truncate leading-none">{{ $s['name'] }}</p>
        </div>
      </div>
      <a href="{{ route('client.crm-connections') }}" class="src-add">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="src-tip">Add CRM provider</span>
      </a>
    </div>
    @elseif($s['id'] === 'social')
    <div id="src-social" class="src" style="padding:0; display:flex; align-items:stretch;">
      <div class="src-link" onclick="selectSource('social')">
        <div class="w-10 h-10 rounded-lg bg-pink-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m5.106 9.197l-2.816.94a2 2 0 01-2.53-1.158l-1.18-3.543a2 2 0 011.158-2.53l2.816-.94a2 2 0 012.53 1.158l1.18 3.543a2 2 0 01-1.158 2.53z"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[13px] font-semibold text-gray-700 truncate leading-none">{{ $s['name'] }}</p>
        </div>
      </div>
      <a href="{{ route('client.social-connections') }}" class="src-add">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="src-tip">Add Social provider</span>
      </a>
    </div>
    @elseif($s['id'] === 'chat')
    <div id="src-chat" class="src" style="padding:0; display:flex; align-items:stretch;">
      <div class="src-link" onclick="selectSource('chat')">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[13px] font-semibold text-gray-700 truncate leading-none">{{ $s['name'] }}</p>
        </div>
      </div>
      <a href="{{ route('client.chat-support-connections') }}" class="src-add">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="src-tip">Add Chat provider</span>
      </a>
    </div>
    @elseif($s['id'] === 'transactions')
    <div id="src-transactions" class="src" style="padding:0; display:flex; align-items:stretch;">
      <div class="src-link" onclick="selectSource('transactions')">
        <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[13px] font-semibold text-gray-700 truncate leading-none">{{ $s['name'] }}</p>
        </div>
      </div>
      <a href="{{ route('client.payment-gateway-connections.index') }}" class="src-add">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="src-tip">Add Transactions provider</span>
      </a>
    </div>
    @else
    <div id="src-{{ $s['id'] }}" class="src {{ $s['id']==='website' ? 'active' : '' }}"
         onclick="selectSource('{{ $s['id'] }}')"
         style="padding:16px 18px;">
      <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-10 h-10 rounded-lg bg-gray-100 flex-shrink-0"></div>
          <div class="min-w-0">
            <p class="text-[13px] font-semibold text-gray-700 truncate leading-none">{{ $s['name'] }}</p>
          </div>
        </div>
      </div>
    </div>
    @endif
    @endforeach
  </div>

  {{-- EMAIL ENGAGEMENT (live Brevo data) --}}
  <div id="email-engagement-panel" class="bg-white rounded-xl border border-gray-200" style="display:none;overflow:hidden;">
    <div class="be-header">
      <div class="be-header-title">
        <div class="be-header-icon">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
            <rect x="3" y="4" width="18" height="16" rx="3" fill="#0b996e"/>
            <path d="M7 8l5 4 5-4" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7 12l5 4 5-4" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.6"/>
          </svg>
        </div>
        <span style="font-size:13px; font-weight:700; color:#111827;">BREVO</span>
        <button type="button" id="be-sync-btn" class="be-sync-btn" onclick="syncBrevoData()">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 11-2.64-6.36M21 4v6h-6"/></svg>
          <span id="be-sync-btn-label">Sync Data</span>
        </button>
      </div>
      <span id="be-synced-wrap" class="be-synced-badge" style="display:none;">
        <span class="be-synced-dot"></span>
        <span id="be-synced"></span>
      </span>
    </div>

    <div id="be-loading" style="padding:52px 24px; text-align:center;">
      <div class="be-empty-icon" style="background:#eff6ff;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" class="animate-spin"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3a9 9 0 109 9"/></svg>
      </div>
      <p style="font-size:13px; color:#9ca3af;">Loading engagement data from Brevo…</p>
    </div>

    <div id="be-error" style="display:none; padding:44px 24px; text-align:center;">
      <div class="be-empty-icon" style="background:#fef2f2;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376C1.83 17.815 2.865 19.5 4.398 19.5h15.204c1.533 0 2.568-1.685 1.7-3.374L13.7 4.126c-.766-1.5-2.633-1.5-3.399 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
      </div>
      <p id="be-error-message" style="font-size:13px; color:#ef4444; margin-bottom:12px;">Could not load Brevo data.</p>
      <button type="button" onclick="loadBrevoEngagementStats(true)" style="font-size:12px; font-weight:700; color:#6366f1; background:#eef2ff; border:none; cursor:pointer;padding:8px 16px;border-radius:8px;">Retry</button>
    </div>

    <div id="be-content" style="display:none;">
      <div class="be-grid">
        <div class="be-cell">
          <div class="be-gauge">
            <canvas id="be-gauge-delivered"></canvas>
            <div class="be-gauge-icon" style="color:#14b8a6;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M22 2L11 13"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            </div>
          </div>
          <div class="be-label">Delivered</div>
          <div class="be-value-row">
            <span class="be-value" id="be-delivered-value">–</span>
            <button type="button" class="be-view" style="background:#ccfbf1;color:#0f766e;" onclick="openBrevoTopModal('delivered')">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              View
            </button>
          </div>
          <div class="be-sub-label">Delivery rate</div>
          <div class="be-sub-value" id="be-delivery-rate" style="background:#ccfbf1;color:#0f766e;">–</div>
        </div>
        <div class="be-cell">
          <div class="be-gauge">
            <canvas id="be-gauge-opens"></canvas>
            <div class="be-gauge-icon" style="color:#f97316;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
          </div>
          <div class="be-label">Opens <span class="be-hint" title="Unique opens — recipients who opened at least once">?</span></div>
          <div class="be-value-row">
            <span class="be-value" id="be-opens-value">–</span>
            <button type="button" class="be-view" style="background:#ffedd5;color:#c2410c;" onclick="openBrevoTopModal('opens')">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              View
            </button>
          </div>
          <div class="be-sub-label">Open rate <span class="be-hint" title="Unique opens ÷ delivered">?</span></div>
          <div class="be-sub-value" id="be-open-rate" style="background:#ffedd5;color:#c2410c;">–</div>
        </div>
        <div class="be-cell">
          <div class="be-gauge">
            <canvas id="be-gauge-clicks"></canvas>
            <div class="be-gauge-icon" style="color:#2563eb;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"/></svg>
            </div>
          </div>
          <div class="be-label">Clicks <span class="be-hint" title="Unique clicks — recipients who clicked at least once">?</span></div>
          <div class="be-value-row">
            <span class="be-value" id="be-clicks-value">–</span>
            <button type="button" class="be-view" style="background:#dbeafe;color:#1d4ed8;" onclick="openBrevoTopModal('clicks')">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              View
            </button>
          </div>
          <div class="be-sub-label">Click-through rate</div>
          <div class="be-sub-value" id="be-click-rate" style="background:#dbeafe;color:#1d4ed8;">–</div>
        </div>
        <div class="be-cell">
          <div class="be-gauge">
            <canvas id="be-gauge-conversions"></canvas>
            <div class="be-gauge-icon" style="color:#6366f1;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
            </div>
          </div>
          <div class="be-label">Conversions</div>
          <div class="be-value-row">
            <span class="be-value" id="be-conversions-value">0</span>
          </div>
          <div class="be-sub-label">Conversion rate</div>
          <div class="be-sub-value" id="be-conversion-rate" style="background:#e0e7ff;color:#4338ca;">0%</div>
        </div>
      </div>

      <div class="be-row2">
        <div class="be-gauge" style="margin-bottom:10px;">
          <canvas id="be-gauge-unsub"></canvas>
          <div class="be-gauge-icon" style="color:#ef4444;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0-4l-4 4"/></svg>
          </div>
        </div>
        <div class="be-label">Unsubscribes</div>
        <div class="be-value-row">
          <span class="be-value" id="be-unsub-value">–</span>
          <button type="button" class="be-view" style="background:#fee2e2;color:#b91c1c;" onclick="openBrevoTopModal('unsubscribes')">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            View
          </button>
        </div>
        <div class="be-sub-label">Unsubscribe rate</div>
        <div class="be-sub-value" id="be-unsub-rate" style="background:#fee2e2;color:#b91c1c;">–</div>
      </div>
    </div>
  </div>

  {{-- Email Engagement "View" modal: top campaigns behind the clicked metric --}}
  <div id="be-modal-overlay" class="be-modal-overlay" style="display:none;" onclick="if(event.target===this) closeBrevoTopModal()">
    <div class="be-modal" id="be-modal">
      <div style="display:flex; align-items:center; gap:12px; padding:14px 20px; border-bottom:1px solid #e5e7eb; flex-shrink:0;">
        <div id="be-modal-dot" style="width:8px;height:8px;border-radius:50%;flex-shrink:0;"></div>
        <span id="be-modal-title" style="font-size:13px; font-weight:700; flex:1;"></span>
        <span id="be-modal-badge" style="font-size:10px;font-weight:700;border-radius:20px;padding:3px 11px;flex-shrink:0;"></span>
        <button type="button" onclick="closeBrevoTopModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; padding:0; display:flex; flex-shrink:0;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div id="be-modal-body" style="overflow-y:auto;"></div>
    </div>
  </div>

  {{-- TAB BAR + PANELS --}}
  <div id="tabs-panels-card" class="bg-white rounded-xl border border-gray-200 overflow-hidden">

    {{-- Website tab bar --}}
    <div id="tabs-website" class="border-b border-gray-200 overflow-x-auto" style="display:flex;">
      @foreach([
        ['scroll-depth','Scroll Depth'],['page-views','Page Views'],
        ['time-on-page','Time on Page'],['mouse-heatmap','Mouse Heatmap'],
        ['rage-clicks','Rage Clicks'],['exit-intent','Exit Intent'],
        ['form-analytics','Form Analytics'],['internal-search','Internal Search'],
        ['video-events','Video Events'],['tab-visibility','Tab Visibility'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='scroll-depth' ? 'active' : '' }}"
              onclick="selectTab('website','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- Mobile tab bar --}}
    <div id="tabs-mobile-app" class="border-b border-gray-200 overflow-x-auto" style="display:none;">
      @foreach([
        ['session','Session & Duration'],['gesture','Gesture Tracking'],
        ['push','Push Notifications'],['crashes','Crashes & Errors'],
        ['feature','Feature Discovery'],['permissions','Permissions'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='session' ? 'active' : '' }}"
              onclick="selectTab('mobile-app','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- CRM tab bar --}}
    <div id="tabs-crm" class="border-b border-gray-200 overflow-x-auto" style="display:none;">
      @foreach([
        ['stage-transitions','Stage Transitions'],
        ['field-completion','Field Completion'],
        ['owner-changes','Owner Changes'],
        ['opt-in-out','Opt in/out history'],
        ['manual-notes','Manual Notes'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='stage-transitions' ? 'active' : '' }}"
              onclick="selectTab('crm','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- Chat & Support tab bar --}}
    <div id="tabs-chat" class="border-b border-gray-200 overflow-x-auto" style="display:none;">
      @foreach([
        ['conversation-events','Conversation Events'],
        ['sentiment-tracking','Sentiment Tracking'],
        ['resolution-metrics','Resolution Metrics'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='conversation-events' ? 'active' : '' }}"
              onclick="selectTab('chat','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- Transactions tab bar --}}
    <div id="tabs-transactions" class="border-b border-gray-200 overflow-x-auto" style="display:none;">
      @foreach([
        ['cart-analytics','Cart Analytics'],
        ['payment-methods','Payment Methods'],
        ['failure-codes','Failure Codes'],
        ['refunds','Refunds'],
        ['repeat-purchase','Repeat Purchase'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='cart-analytics' ? 'active' : '' }}"
              onclick="selectTab('transactions','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- Social Signals tab bar --}}
    <div id="tabs-social" class="border-b border-gray-200 overflow-x-auto" style="display:none;">
      @foreach([
        ['brand-mentions',   'Brand Mentions'],
        ['hashtag-tracking', 'Hashtag Tracking'],
        ['sentiment',        'Sentiment Analysis'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='brand-mentions' ? 'active' : '' }}"
              onclick="selectTab('social','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- Call Center tab bar --}}
    <div id="tabs-callcenter" class="border-b border-gray-200 overflow-x-auto" style="display:none;">
      @foreach([
        ['call-events',       'Call Events'],
        ['ivr-path-analysis', 'IVR Path Analysis'],
        ['speech-sentiment',  'Speech Sentiment'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='call-events' ? 'active' : '' }}"
              onclick="selectTab('callcenter','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- Email Engagement tab bar --}}
    <div id="tabs-email" class="border-b border-gray-200 overflow-x-auto" style="display:none;">
      @foreach([
        ['delivery-opens', 'Delivery & Opens'],
        ['link-clicks',    'Link Clicks'],
        ['unsubscribe',    'Unsubscribe'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='delivery-opens' ? 'active' : '' }}"
              onclick="selectTab('email','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- Ad Campaigns tab bar --}}
    <div id="tabs-ads" class="border-b border-gray-200 overflow-x-auto" style="display:none;">
      @foreach([
        ['impression',  'Impression'],
        ['clicks-ctr',  'Clicks & CTR'],
        ['conversions', 'Conversions'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='impression' ? 'active' : '' }}"
              onclick="selectTab('ads','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- Surveys & Feedback tab bar --}}
    <div id="tabs-surveys" class="border-b border-gray-200 overflow-x-auto" style="display:none;">
      @foreach([
        ['nps-score',       'NPS Score'],
        ['csat',            'CSAT'],
        ['open-text',       'Open Text Sentiment'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='nps-score' ? 'active' : '' }}"
              onclick="selectTab('surveys','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- Loyalty / Referral tab bar --}}
    <div id="tabs-loyalty" class="border-b border-gray-200 overflow-x-auto" style="display:none;">
      @foreach([
        ['points-activity',   'Points Activity'],
        ['tier-movements',    'Tier Movements'],
        ['referral-tracking', 'Referral Tracking'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='points-activity' ? 'active' : '' }}"
              onclick="selectTab('loyalty','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- POS / Offline tab bar --}}
    <div id="tabs-pos" class="border-b border-gray-200 overflow-x-auto" style="display:none;">
      @foreach([
        ['store-visits',    'Store Visits'],
        ['basket-analysis', 'Basket Analysis'],
        ['pos-events',      'POS Events'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='store-visits' ? 'active' : '' }}"
              onclick="selectTab('pos','{{ $tid }}')">{{ $tlabel }}</button>
      @endforeach
    </div>

    {{-- Generic source tab bar --}}
    <div id="tabs-generic" class="border-b border-gray-200" style="display:none;">
      <button class="tab-btn active">Overview</button>
    </div>

    {{-- ═══════════════════════════════════════
         WEBSITE PANELS
         ═══════════════════════════════════════ --}}

    {{-- Scroll Depth --}}
    <div id="panel-website-scroll-depth" class="panel active p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Scroll Depth Distribution</span>
            <div class="section-line"></div>
          </div>
          @foreach([['0–25% (shallow)',18,'#5eead4'],['25–59%',24,'#60a5fa'],['50–75%',31,'#a78bfa'],['75–100% (deep)',27,'#f59e0b']] as [$l,$v,$c])
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <span style="font-size:12px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Scroll Depth by Page Type</span>
            <div class="section-line"></div>
          </div>
          @foreach(['Landing Pages','Blog Post','Product Pages','Pricing Pages','Checkout Flow'] as $page)
          <div style="margin-bottom:14px">
            <p style="font-size:12px;color:#6b7280;margin-bottom:4px">{{ $page }}</p>
            <div style="display:flex;height:22px;border-radius:4px;overflow:hidden">
              <div style="width:16%;background:#f87171"></div>
              <div style="width:24%;background:#fb923c"></div>
              <div style="width:16%;background:#fbbf24"></div>
              <div style="width:28%;background:#60a5fa"></div>
              <div style="width:16%;background:#5eead4"></div>
            </div>
          </div>
          @endforeach
          <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:8px;font-size:10px;color:#9ca3af">
            @foreach([['#f87171','0–25'],['#fb923c','25–50%'],['#fbbf24','50–75%'],['#60a5fa','75–90%'],['#5eead4','90–100']] as [$c,$l])
            <span style="display:flex;align-items:center;gap:4px">
              <span style="width:8px;height:8px;border-radius:2px;background:{{ $c }};display:inline-block"></span>{{ $l }}
            </span>
            @endforeach
          </div>
        </div>
      </div>
      <div style="margin-top:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px">
        <p style="font-size:11px;font-weight:600;color:#16a34a">● Key Insight</p>
        <p style="font-size:13px;color:#4b5563;margin-top:4px">58% scroll past fold. Pricing CTA at 70% depth achieves 3.2× higher interaction vs top-fold.</p>
      </div>
    </div>

    {{-- Page Views --}}
    <div id="panel-website-page-views" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Top Pages by Views</span>
            <div class="section-line"></div>
          </div>
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;font-size:10px;color:#9ca3af;text-transform:uppercase;padding-bottom:8px;border-bottom:1px solid #f3f4f6;margin-bottom:4px">
            <span>Page</span><span>Views</span><span>Bounce</span><span>Avg Time</span>
          </div>
          @foreach([['/home','48,200','32%','2m14s'],['/ pricing','24,100','28%','3m42s'],['/features','18,900','41%','1m58s'],['/blog','14,700','55%','4m22s'],['/docs','11,200','22%','5m10s'],['/about','8,400','61%','0m58s']] as [$pg,$v,$b,$t])
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;font-size:12px;padding:9px 0;border-bottom:1px solid #f9fafb;align-items:center">
            <span style="color:#3b82f6;font-weight:500">{{ $pg }}</span>
            <span style="color:#374151;font-weight:600">{{ $v }}</span>
            <span style="color:{{ (int)$b > 50 ? '#ef4444' : '#10b981' }}">{{ $b }}</span>
            <span style="color:#9ca3af">{{ $t }}</span>
          </div>
          @endforeach
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Traffic Source Breakdown</span>
            <div class="section-line"></div>
          </div>
          @foreach([['Direct',38,'#5eead4'],['Organic Search',29,'#60a5fa'],['Referral',14,'#a78bfa'],['Social Media',11,'#f59e0b'],['Email',5,'#ec4899'],['Paid',3,'#f97316']] as [$l,$v,$c])
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <span style="font-size:11px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v * 2 }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px">
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#3b82f6">126K</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Total Views Today</p></div>
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">38%</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Avg Bounce Rate</p></div>
          </div>
        </div>
      </div>
    </div>

    {{-- Time on Page --}}
    <div id="panel-website-time-on-page" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Time on Page Distribution</span>
            <div class="section-line"></div>
          </div>
          @foreach([['< 10 sec (bounced)','27.2k users',18,'#5eead4'],['10 s – 1 min','38.4k users',24,'#60a5fa'],['1 – 3 min','34.7k users',31,'#a78bfa'],['3 – 5 min','14.9k users',27,'#f59e0b'],['> 5 min (deep read)','8.7k users',27,'#ec4899']] as [$l,$u,$v,$c])
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <span style="font-size:10px;color:#9ca3af">{{ $u }}</span>
              <span style="font-size:11px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px">
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#f59e0b">2m 38s</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Avg time on page</p></div>
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">6m 12s</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Median for Converters</p></div>
          </div>
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Scroll Depth by Page Type</span>
            <div class="section-line"></div>
          </div>
          @foreach([['< 30s','0.4% CVR'],['30s – 1m','1.2% CVR'],['1–2m','3.8% CVR'],['2–4m','7.2% CVR'],['4–6m','11.4% CVR'],['> 6m','14.1% CVR']] as [$l,$v])
          <div style="display:flex;justify-content:space-between;align-items:center;background:#f9fafb;border-radius:8px;padding:7px 12px;margin-bottom:4px">
            <span style="font-size:12px;color:#6b7280">{{ $l }}</span>
            <span style="font-size:12px;font-weight:700;color:#f59e0b">{{ $v }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Mouse Heatmap --}}
    <div id="panel-website-mouse-heatmap" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#f97316"></div>
            <span class="section-title" style="color:#f97316">Mouse Movement Heatmap – Pricing</span>
            <div class="section-line" style="background:#fed7aa"></div>
          </div>
          <div style="background:#fafafa;border-radius:8px;padding:20px 16px;display:flex;flex-direction:column;gap:14px;align-items:center">
            @foreach([
              ['Hero CTA – Hot', 'rgba(185,60,10,0.82)', '180px', '48px'],
              ['Pricing Tool',   'rgba(185,60,10,0.92)', '240px', '56px'],
              ['Feature List',   'rgba(194,75,15,0.72)', '190px', '48px'],
              ['FAQ Section',    'rgba(210,95,25,0.62)', '160px', '44px'],
            ] as [$label, $bg, $w, $h])
            <div style="position:relative;width:{{ $w }};height:{{ $h }};display:flex;align-items:center;justify-content:center">
              <div style="position:absolute;inset:0;background:{{ $bg }};border-radius:50%;filter:blur(10px)"></div>
              <span style="position:relative;z-index:1;font-size:11px;font-weight:600;color:#fff;white-space:nowrap">{{ $label }}</span>
            </div>
            @endforeach
          </div>
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Scroll Depth by Page Type</span>
            <div class="section-line"></div>
          </div>
          @foreach([['< 30s','0.4% CVR'],['30s – 1m','1.2% CVR'],['1–2m','3.8% CVR'],['2–4m','7.2% CVR'],['4–6m','11.4% CVR'],['> 6m','14.1% CVR']] as [$l,$v])
          <div style="display:flex;justify-content:space-between;align-items:center;background:#f9fafb;border-radius:8px;padding:7px 12px;margin-bottom:4px">
            <span style="font-size:12px;color:#6b7280">{{ $l }}</span>
            <span style="font-size:12px;font-weight:700;color:#f59e0b">{{ $v }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Form Analytics --}}
    <div id="panel-website-form-analytics" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Form Field Analytics – Checkout</span>
            <div class="section-line"></div>
          </div>
          @foreach([['Email',18,'#5eead4',75],['Full Name',24,'#60a5fa',80],['Card No.',31,'#a78bfa',75],['Expiry',27,'#f59e0b',75],['CVV',27,'#f59e0b',75],['Zip Code',27,'#f59e0b',75]] as [$l,$v,$c,$f])
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <span style="font-size:11px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div class="bar-wrap"><div class="bar-fill bg-blue-400" style="width:{{ $f }}%"></div></div>
          </div>
          @endforeach
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Form Field Analytics – Checkout</span>
            <div class="section-line"></div>
          </div>
          @foreach([['Form Shown','-19%','#f87171',0],['First Field','24%','#60a5fa',80],['50% Complete','31%','#a78bfa',75],['90% Complete','27%','#f59e0b',75],['Submitted','27%','#f59e0b',75],['Confirmed','27%','#f59e0b',75]] as [$l,$v,$c,$f])
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <span style="font-size:11px;font-weight:700;color:{{ $c }}">{{ $v }}</span>
            </div>
            <div class="bar-wrap"><div class="bar-fill bg-blue-400" style="width:{{ $f }}%"></div></div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Internal Search --}}
    <div id="panel-website-internal-search" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot teal-section-dot"></div>
            <span class="section-title teal-section-title">Top Search Queries</span>
            <div class="section-line teal-section-line"></div>
          </div>
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;font-size:10px;color:#9ca3af;text-transform:uppercase;padding-bottom:8px;border-bottom:1px solid #f3f4f6;margin-bottom:4px">
            <span>Query</span><span>Searches</span><span>Clicks</span><span>Exit %</span>
          </div>
          @foreach([['pricing plans','4,820','3,940','18%'],['API integrations','2,410','2,180','9%'],['refund policy','1,840','1,240','41%'],['enterprise','1,620','1,480','8%'],['how to cancel','1,210','980','62%'],['data export','940','820','12%']] as [$q,$s,$c,$e])
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;font-size:12px;padding:9px 0;border-bottom:1px solid #f9fafb">
            <span style="color:#14b8a6;font-weight:500">{{ $q }}</span>
            <span style="color:#6b7280">{{ $s }}</span>
            <span style="color:#14b8a6">{{ $c }}</span>
            <span style="color:{{ (int)$e > 30 ? '#ef4444' : '#14b8a6' }}">{{ $e }}</span>
          </div>
          @endforeach
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot teal-section-dot"></div>
            <span class="section-title teal-section-title">Zero Result Searches (Intent Gaps)</span>
            <div class="section-line teal-section-line"></div>
          </div>
          @foreach(['"white label"','"SAML SSO Setup"','"bulk import csv"','"mobile SDK"','"custom domain"'] as $t)
          <div style="display:flex;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f9fafb">
            <span style="font-size:12px;font-weight:500;color:#f59e0b">{{ $t }}</span>
            <span style="font-size:10px;font-weight:700;color:#ef4444">NO RESULTS</span>
          </div>
          @endforeach
          <div style="border:1px solid #e5e7eb;border-radius:10px;padding:18px;text-align:center;margin-top:16px">
            <p style="font-size:26px;font-weight:700;color:#14b8a6">63%</p>
            <p style="font-size:11px;color:#9ca3af;margin-top:4px">searches use autocomplete</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Rage Clicks --}}
    <div id="panel-website-rage-clicks" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#ef4444"></div>
            <span class="section-title" style="color:#ef4444">Top Rage-Clicked Elements</span>
            <div class="section-line" style="background:#fecaca"></div>
          </div>
          @foreach([['#pricing-cta','1,240 sessions',18,'#ef4444'],['  .disabled-btn','980 sessions',14,'#f97316'],['#promo-banner','840 sessions',12,'#f59e0b'],['  .collapsed-menu','720 sessions',10,'#a78bfa'],['#submit-form','580 sessions',8,'#60a5fa']] as [$el,$s,$v,$c])
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <span style="font-size:11px;font-weight:600;color:#374151;font-family:monospace">{{ $el }}</span>
              <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:10px;color:#9ca3af">{{ $s }}</span>
                <span style="font-size:11px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
              </div>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v * 4 }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#ef4444"></div>
            <span class="section-title" style="color:#ef4444">Rage Clicks by Page</span>
            <div class="section-line" style="background:#fecaca"></div>
          </div>
          @foreach([['/checkout',42,'#ef4444'],['/pricing',28,'#f97316'],['/signup',16,'#f59e0b'],['/settings',9,'#a78bfa'],['Other',5,'#9ca3af']] as [$p,$v,$c])
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px">
              <span style="font-size:12px;color:#374151">{{ $p }}</span>
              <span style="font-size:11px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v * 2 }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
          <div style="margin-top:16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 14px">
            <p style="font-size:11px;font-weight:600;color:#ef4444">● Key Insight</p>
            <p style="font-size:12px;color:#4b5563;margin-top:4px">Most rage clicks on disabled checkout CTA — suggest enabling earlier in the payment flow.</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Exit Intent --}}
    <div id="panel-website-exit-intent" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#f97316"></div>
            <span class="section-title" style="color:#f97316">Exit Intent Triggers by Page</span>
            <div class="section-line" style="background:#fed7aa"></div>
          </div>
          @foreach([['/pricing','3,840',38,'#f97316'],['/checkout','2,920',29,'#ef4444'],['/features','1,480',15,'#f59e0b'],['/signup','1,020',10,'#a78bfa'],['Other','840',8,'#9ca3af']] as [$p,$cnt,$v,$c])
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <span style="font-size:12px;color:#374151">{{ $p }}</span>
              <div style="display:flex;gap:10px;align-items:center">
                <span style="font-size:10px;color:#9ca3af">{{ $cnt }}</span>
                <span style="font-size:11px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
              </div>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v * 2 }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#f97316"></div>
            <span class="section-title" style="color:#f97316">Exit Offer Conversion Funnel</span>
            <div class="section-line" style="background:#fed7aa"></div>
          </div>
          @foreach([['Intent Detected','10,100','100%','#9ca3af'],['Overlay Shown','8,420','83%','#f97316'],['Offer Engaged','2,950','35%','#f59e0b'],['Converted','840','29%','#10b981']] as [$l,$cnt,$pct,$c])
          <div style="display:flex;justify-content:space-between;align-items:center;background:#f9fafb;border-radius:8px;padding:11px 14px;margin-bottom:8px">
            <span style="font-size:12px;color:#6b7280">{{ $l }}</span>
            <div style="display:flex;gap:10px;align-items:center">
              <span style="font-size:12px;font-weight:600;color:#374151">{{ $cnt }}</span>
              <span style="font-size:11px;font-weight:700;color:{{ $c }}">{{ $pct }}</span>
            </div>
          </div>
          @endforeach
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px">
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">8.3%</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Overall Save Rate</p></div>
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#f97316">2.4×</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">CVR vs No Overlay</p></div>
          </div>
        </div>
      </div>
    </div>

    {{-- Video Events --}}
    <div id="panel-website-video-events" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#8b5cf6"></div>
            <span class="section-title" style="color:#8b5cf6">Video Engagement Funnel</span>
            <div class="section-line" style="background:#ddd6fe"></div>
          </div>
          @foreach([['Play Pressed','18,200','100%','#8b5cf6'],[' 25% Watched','13,840','76%','#6366f1'],['50% Watched','10,200','56%','#3b82f6'],['75% Watched','7,480','41%','#60a5fa'],['Completed','4,920','27%','#5eead4']] as [$l,$cnt,$pct,$c])
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <div style="display:flex;gap:10px;align-items:center">
                <span style="font-size:10px;color:#9ca3af">{{ $cnt }}</span>
                <span style="font-size:11px;font-weight:700;color:{{ $c }}">{{ $pct }}</span>
              </div>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ (int)$pct }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px">
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#8b5cf6">27%</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Completion Rate</p></div>
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">4m 18s</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Avg Watch Time</p></div>
          </div>
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#8b5cf6"></div>
            <span class="section-title" style="color:#8b5cf6">Top Video Content</span>
            <div class="section-line" style="background:#ddd6fe"></div>
          </div>
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr;font-size:10px;color:#9ca3af;text-transform:uppercase;padding-bottom:8px;border-bottom:1px solid #f3f4f6;margin-bottom:4px">
            <span>Video</span><span>Plays</span><span>Completion</span>
          </div>
          @foreach([['Product Demo','8,420','72%'],['Onboarding Walkthrough','5,180','84%'],['Tutorial #1','3,920','64%'],['Case Study – Acme','2,840','55%'],['Feature Deep Dive','1,960','48%']] as [$n,$p,$c])
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr;font-size:12px;padding:9px 0;border-bottom:1px solid #f9fafb;align-items:center">
            <span style="color:#374151;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $n }}</span>
            <span style="color:#6b7280">{{ $p }}</span>
            <span style="color:{{ (int)$c > 60 ? '#10b981' : '#f59e0b' }};font-weight:700">{{ $c }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Tab Visibility --}}
    <div id="panel-website-tab-visibility" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#14b8a6"></div>
            <span class="section-title teal-section-title">Tab Active / Inactive Breakdown</span>
            <div class="section-line teal-section-line"></div>
          </div>
          @foreach([['Always Active (tab never hidden)',42,'#14b8a6'],['Mostly Active (> 80% visible)',28,'#60a5fa'],['Mixed (40 – 80% visible)',18,'#a78bfa'],['Mostly Inactive (< 40%)',12,'#f59e0b']] as [$l,$v,$c])
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <span style="font-size:12px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v * 2 }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px">
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#14b8a6">4.2×</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Tab blur events / session</p></div>
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#8b5cf6">34%</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Multi-tab users</p></div>
          </div>
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#14b8a6"></div>
            <span class="section-title teal-section-title">Impact on Session Metrics</span>
            <div class="section-line teal-section-line"></div>
          </div>
          @foreach([['Active Sessions – Avg duration','6m 42s','#10b981'],['Inactive Sessions – Avg duration','2m 14s','#ef4444'],['Active → Conversion rate','72%','#10b981'],['Inactive → Conversion rate','14%','#ef4444'],['Tab regain after blur','58%','#f59e0b'],['Abandoned after 3+ blurs','24%','#f97316']] as [$l,$v,$c])
          <div style="display:flex;justify-content:space-between;align-items:center;background:#f9fafb;border-radius:8px;padding:7px 12px;margin-bottom:4px">
            <span style="font-size:12px;color:#6b7280">{{ $l }}</span>
            <span style="font-size:12px;font-weight:700;color:{{ $c }}">{{ $v }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════
         MOBILE APP PANELS
         ═══════════════════════════════════════ --}}

    {{-- Session & Duration --}}
    <div id="panel-mobile-app-session" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Session Duration Distribution</span>
            <div class="section-line"></div>
          </div>
          @foreach([['< 30 sec',18,'#5eead4'],['30s – 2min',24,'#60a5fa'],['2 – 5min',31,'#a78bfa'],['5 – 10 min',27,'#f59e0b'],['> 10 min',27,'#4f46e5']] as [$l,$v,$c])
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <span style="font-size:12px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-top:16px">
            <div class="mini"><p style="font-size:16px;font-weight:700;color:#f59e0b">4.8</p><p style="font-size:10px;color:#9ca3af;margin-top:3px">Avg Sessions / day</p></div>
            <div class="mini"><p style="font-size:16px;font-weight:700;color:#10b981">6m12s</p><p style="font-size:10px;color:#9ca3af;margin-top:3px">Avg Duration</p></div>
            <div class="mini"><p style="font-size:16px;font-weight:700;color:#10b981">6m12s</p><p style="font-size:10px;color:#9ca3af;margin-top:3px">Background Resume</p></div>
          </div>
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Scroll Depth by Page Type</span>
            <div class="section-line"></div>
          </div>
          @foreach([['Foreground × Background switched/day','4.2x'],['background resume rate','68%'],['Avg background duration','14 min'],['Abandoned after background','32%']] as [$l,$v])
          <div style="display:flex;justify-content:space-between;align-items:center;background:#f9fafb;border-radius:8px;padding:7px 12px;margin-bottom:4px">
            <span style="font-size:12px;color:#6b7280">{{ $l }}</span>
            <span style="font-size:12px;font-weight:700;color:#f59e0b">{{ $v }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Push Notifications --}}
    <div id="panel-mobile-app-push" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Push Notification Funnel</span>
            <div class="section-line"></div>
          </div>
          @foreach([['Sent',18,'#5eead4'],['Delivered',24,'#60a5fa'],['Open',31,'#a78bfa'],['Acted on',27,'#f59e0b'],['Dismissed',27,'#ec4899']] as [$l,$v,$c])
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <div style="display:flex;gap:12px;align-items:center">
                <span style="font-size:10px;color:#9ca3af">8.7K</span>
                <span style="font-size:12px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
              </div>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title">Open Rate by Notification Type</span>
            <div class="section-line"></div>
          </div>
          @foreach([['Transaction Alert',18,'#5eead4'],['Personalized offer',24,'#60a5fa'],['Re-engagement',31,'#a78bfa'],['Product Update',27,'#f59e0b'],['Weekly digest',27,'#ec4899'],['Generic promo',27,'#22d3ee']] as [$l,$v,$c])
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <span style="font-size:12px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Feature Discovery --}}
    <div id="panel-mobile-app-feature" class="panel p-4">
      <div class="grid grid-cols-4 gap-3">
        @foreach([['Dashboard View',1],['Create Segment',2],['Export Report',3],['AI Predictions',4],['API Setup',1],['Custom Alerts',2]] as [$n,$d])
        <div class="panel-card">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;color:#111827">{{ $n }}</span>
            <span style="font-size:10px;color:#10b981;font-weight:600">Day {{ $d }}</span>
          </div>
          <div style="display:flex;gap:20px">
            <div><p style="font-size:17px;font-weight:700;color:#10b981">94%</p><p style="font-size:10px;color:#9ca3af">discovered</p></div>
            <div><p style="font-size:17px;font-weight:700;color:#10b981">82%</p><p style="font-size:10px;color:#9ca3af">retained</p></div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Gesture Tracking --}}
    <div id="panel-mobile-app-gesture" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#6366f1"></div>
            <span class="section-title" style="color:#6366f1">Gesture Type Distribution</span>
            <div class="section-line" style="background:#c7d2fe"></div>
          </div>
          @foreach([['Tap',58,'#5eead4'],['Swipe – Horizontal',22,'#60a5fa'],['Swipe – Vertical',12,'#a78bfa'],['Pinch / Zoom',6,'#f59e0b'],['Long Press',2,'#ec4899']] as [$l,$v,$c])
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <span style="font-size:12px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px">
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#6366f1">83K</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Gestures today</p></div>
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">14.2</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Gestures / session</p></div>
          </div>
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#6366f1"></div>
            <span class="section-title" style="color:#6366f1">Gestures by Screen</span>
            <div class="section-line" style="background:#c7d2fe"></div>
          </div>
          @foreach([['Home / Feed','14,200','#6366f1'],['Product List','10,800','#3b82f6'],['Cart','8,400','#5eead4'],['Checkout','6,200','#10b981'],['Profile','4,100','#a78bfa'],['Settings','2,800','#f59e0b']] as [$scr,$cnt,$c])
          <div style="display:flex;justify-content:space-between;align-items:center;background:#f9fafb;border-radius:8px;padding:7px 12px;margin-bottom:4px">
            <span style="font-size:12px;color:#6b7280">{{ $scr }}</span>
            <span style="font-size:12px;font-weight:700;color:{{ $c }}">{{ $cnt }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Crashes & Errors --}}
    <div id="panel-mobile-app-crashes" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#ef4444"></div>
            <span class="section-title" style="color:#ef4444">Crash Rate by OS Version</span>
            <div class="section-line" style="background:#fecaca"></div>
          </div>
          @foreach([['iOS 17','0.12%','#10b981'],['iOS 16','0.84%','#f59e0b'],['Android 14','0.19%','#10b981'],['Android 13','0.42%','#f59e0b'],['Android 12','1.24%','#f97316'],['Android 11','2.18%','#ef4444']] as [$os,$rate,$c])
          <div style="display:flex;justify-content:space-between;align-items:center;background:#f9fafb;border-radius:8px;padding:7px 12px;margin-bottom:4px">
            <span style="font-size:12px;color:#374151;font-weight:500">{{ $os }}</span>
            <span style="font-size:12px;font-weight:700;color:{{ $c }}">{{ $rate }}</span>
          </div>
          @endforeach
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px">
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">0.31%</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Overall Crash Rate</p></div>
            <div class="mini"><p style="font-size:20px;font-weight:700;color:#ef4444">284</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Crashes today</p></div>
          </div>
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#ef4444"></div>
            <span class="section-title" style="color:#ef4444">Top Error Types</span>
            <div class="section-line" style="background:#fecaca"></div>
          </div>
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr;font-size:10px;color:#9ca3af;text-transform:uppercase;padding-bottom:8px;border-bottom:1px solid #f3f4f6;margin-bottom:4px">
            <span>Error</span><span>Count</span><span>Platform</span>
          </div>
          @foreach([['NullPointerException','840','Both'],['NetworkTimeoutError','620','Both'],['OutOfMemoryError','410','Android'],['UIViewControllerDealloc','280','iOS'],['JSONParseException','190','Both'],['DatabaseLockException','140','Android']] as [$err,$cnt,$plat])
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr;font-size:12px;padding:9px 0;border-bottom:1px solid #f9fafb;align-items:center">
            <span style="color:#ef4444;font-weight:500;font-size:11px;font-family:monospace">{{ $err }}</span>
            <span style="color:#374151;font-weight:600">{{ $cnt }}</span>
            <span style="color:#9ca3af">{{ $plat }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Permissions --}}
    <div id="panel-mobile-app-permissions" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot teal-section-dot"></div>
            <span class="section-title teal-section-title">Permission Grant Rate</span>
            <div class="section-line teal-section-line"></div>
          </div>
          @foreach([['Push Notifications',68,'#14b8a6'],['Location (Precise)',41,'#f59e0b'],['Camera',74,'#10b981'],['Microphone',55,'#60a5fa'],['Contacts',28,'#f97316'],['Biometric / Face ID',82,'#8b5cf6']] as [$l,$v,$c])
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px">
              <span style="font-size:12px;color:#374151">{{ $l }}</span>
              <span style="font-size:11px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $v }}%;background:{{ $c }}"></div></div>
          </div>
          @endforeach
        </div>
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot teal-section-dot"></div>
            <span class="section-title teal-section-title">Opt-in / Opt-out Funnel</span>
            <div class="section-line teal-section-line"></div>
          </div>
          @foreach([['Prompt Shown','24,200','100%','#9ca3af'],['Granted','16,456','68%','#10b981'],['Denied','5,808','24%','#ef4444'],['Dismissed','1,936','8%','#f59e0b']] as [$l,$cnt,$pct,$c])
          <div style="display:flex;justify-content:space-between;align-items:center;background:#f9fafb;border-radius:8px;padding:11px 14px;margin-bottom:8px">
            <span style="font-size:12px;color:#6b7280">{{ $l }}</span>
            <div style="display:flex;gap:10px;align-items:center">
              <span style="font-size:12px;font-weight:600;color:#374151">{{ $cnt }}</span>
              <span style="font-size:11px;font-weight:700;color:{{ $c }}">{{ $pct }}</span>
            </div>
          </div>
          @endforeach
          <div style="margin-top:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 14px">
            <p style="font-size:11px;font-weight:600;color:#16a34a">● Key Insight</p>
            <p style="font-size:12px;color:#4b5563;margin-top:4px">Users who grant push are 3.4× more likely to return within 7 days of install.</p>
          </div>
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════
         CRM PANELS
         ═══════════════════════════════════════ --}}

    {{-- Stage Transitions --}}
    <div id="panel-crm-stage-transitions" class="panel p-4">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:stretch">

        {{-- Left: velocity table --}}
        <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:16px">
          {{-- Section header --}}
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
            <span style="width:10px;height:10px;border-radius:50%;background:#8b5cf6;flex-shrink:0;display:inline-block"></span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#8b5cf6;white-space:nowrap">Sales Stage Transition Velocity</span>
            <span style="flex:1;height:1px;background:#ddd6fe;display:block"></span>
          </div>

          {{-- Column headers --}}
          <div style="display:grid;grid-template-columns:1fr 80px 100px;padding-bottom:10px;border-bottom:1px solid #f3f4f6">
            <span style="font-size:10px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Stage</span>
            <span style="font-size:10px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;text-align:right">Avg Days</span>
            <span style="font-size:10px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;text-align:right">Deals</span>
          </div>

          @foreach([
            ['Lead → MQL',       '3.2d','1,840 deals'],
            ['MQL → SQL',        '3.2d','1,840 deals'],
            ['SQL → Protocol',   '3.2d','1,840 deals'],
            ['Protocol → Close', '3.2d','1,840 deals'],
            ['Close → Won',      '3.2d','1,840 deals'],
          ] as [$stage,$days,$deals])
          <div style="display:grid;grid-template-columns:1fr 80px 100px;align-items:center;padding:14px 0;border-bottom:1px solid #f3f4f6">
            <span style="font-size:13px;color:#111827;font-weight:500">{{ $stage }}</span>
            <span style="font-size:14px;font-weight:700;color:#10b981;text-align:right">{{ $days }}</span>
            <span style="font-size:12px;color:#9ca3af;text-align:right">{{ $deals }}</span>
          </div>
          @endforeach
        </div>

        {{-- Right: area chart + stat boxes --}}
        <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:16px;display:flex;flex-direction:column">
          {{-- Section header --}}
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
            <span style="width:10px;height:10px;border-radius:50%;background:#8b5cf6;flex-shrink:0;display:inline-block"></span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#8b5cf6;white-space:nowrap">Deal Velocity Trend</span>
            <span style="flex:1;height:1px;background:#ddd6fe;display:block"></span>
          </div>

          {{-- SVG smooth single-peak mountain curve --}}
          <div style="flex:1;margin-bottom:16px">
            <svg viewBox="0 0 400 130" width="100%" height="160" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <linearGradient id="crm-grad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#c4b5fd" stop-opacity="0.5"/>
                  <stop offset="100%" stop-color="#ede9fe" stop-opacity="0.02"/>
                </linearGradient>
              </defs>
              {{-- Smooth single-peak: steep left rise, gradual right descent --}}
              <path d="M0,108 C55,108 90,20 145,10 S 300,108 400,100 L400,130 L0,130 Z" fill="url(#crm-grad)"/>
              <path d="M0,108 C55,108 90,20 145,10 S 300,108 400,100" fill="none" stroke="#8b5cf6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="145" cy="10" r="5" fill="#8b5cf6"/>
            </svg>
          </div>

          {{-- Stat boxes --}}
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:20px 12px;text-align:center">
              <p style="font-size:34px;font-weight:700;color:#f59e0b;line-height:1;margin:0">68</p>
              <p style="font-size:11px;color:#9ca3af;margin-top:8px;line-height:1.5">deals stalled &gt; 30 days</p>
            </div>
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:20px 12px;text-align:center">
              <p style="font-size:34px;font-weight:700;color:#10b981;line-height:1;margin:0">241</p>
              <p style="font-size:11px;color:#9ca3af;margin-top:8px;line-height:1.5">Deals closed this month</p>
            </div>
          </div>
        </div>

      </div>
    </div>

    {{-- CRM globe panels --}}
    @foreach([
      ['field-completion', 'CRM – Field Completion'],
      ['owner-changes',    'CRM – Owner Changes'],
      ['opt-in-out',       'CRM – Opt-in/Out History'],
      ['manual-notes',     'CRM – Manual Notes'],
    ] as [$tid, $title])
    <div id="panel-crm-{{ $tid }}" class="panel p-6">
      <div style="text-align:center;padding:32px 0">
        <div style="width:72px;height:72px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.4">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 2C12 2 8 6 8 12s4 10 4 10M12 2c0 0 4 4 4 10s-4 10-4 10"/>
            <path d="M2 12h20M3.5 7h17M3.5 17h17"/>
          </svg>
        </div>
        <h3 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:6px">{{ $title }}</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:28px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:500px;margin:0 auto">
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#10b981">1.2M/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>
    @endforeach

    {{-- ═══════════════════════════════════════
         CHAT & SUPPORT PANELS
         ═══════════════════════════════════════ --}}

    {{-- Conversation Events --}}
    <div id="panel-chat-conversation-events" class="panel p-5">
      <div class="globe-panel">
        <div style="width:64px;height:64px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
          <svg width="32" height="32" fill="none" stroke="#3b82f6" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><ellipse cx="12" cy="12" rx="4" ry="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M2 7c2.5 1 5 1.5 10 1.5S19.5 8 22 7"/><path d="M2 17c2.5-1 5-1.5 10-1.5S19.5 16 22 17"/>
          </svg>
        </div>
        <h3 style="font-size:18px;font-weight:700;color:#111827">Chat &amp; Support – Conversation Events</h3>
        <p style="font-size:12px;color:#9ca3af;margin-top:4px">Analytics panel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:480px;margin:24px auto 0">
          <div class="mini"><p style="font-size:17px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Events today</p></div>
          <div class="mini"><p style="font-size:17px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Status</p></div>
          <div class="mini"><p style="font-size:17px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- Sentiment Tracking --}}
    <div id="panel-chat-sentiment-tracking" class="panel p-4">
      @php
      $sentimentRows = [
        ['Credit card',     52, '#10b981'],
        ['Apple / Google pay', 24, '#3b82f6'],
        ['Paypal',          14, '#8b5cf6'],
        ['bank transfer',   55, '#f59e0b'],
        ['Buy Now pay later',66, '#ec4899'],
      ];
      @endphp
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- Left: Chat Sentiment Distribution --}}
        <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:16px">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px">
            <span style="width:10px;height:10px;border-radius:50%;background:#10b981;flex-shrink:0;display:inline-block"></span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#10b981;white-space:nowrap">Chat Sentiment Distribution</span>
            <span style="flex:1;height:1px;background:#a7f3d0;display:block"></span>
          </div>
          @foreach($sentimentRows as [$l,$v,$c])
          <div style="margin-bottom:18px">
            <div style="display:flex;justify-content:space-between;margin-bottom:7px">
              <span style="font-size:13px;color:#374151">{{ $l }}</span>
              <span style="font-size:13px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div style="height:7px;background:#f3f4f6;border-radius:999px;overflow:hidden">
              <div style="height:7px;width:{{ $v }}%;background:{{ $c }};border-radius:999px"></div>
            </div>
          </div>
          @endforeach
        </div>

        {{-- Right: Sentiment By Topic --}}
        <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:16px">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px">
            <span style="width:10px;height:10px;border-radius:50%;background:#10b981;flex-shrink:0;display:inline-block"></span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#10b981;white-space:nowrap">Sentiment By Topic</span>
            <span style="flex:1;height:1px;background:#a7f3d0;display:block"></span>
          </div>
          @foreach($sentimentRows as [$l,$v,$c])
          <div style="margin-bottom:18px">
            <div style="display:flex;justify-content:space-between;margin-bottom:7px">
              <span style="font-size:13px;color:#374151">{{ $l }}</span>
              <span style="font-size:13px;font-weight:700;color:{{ $c }}">{{ $v }}%</span>
            </div>
            <div style="height:7px;background:#f3f4f6;border-radius:999px;overflow:hidden">
              <div style="height:7px;width:{{ $v }}%;background:{{ $c }};border-radius:999px"></div>
            </div>
          </div>
          @endforeach
        </div>

      </div>
    </div>

    {{-- Resolution Metrics --}}
    <div id="panel-chat-resolution-metrics" class="panel p-5">
      <div class="globe-panel">
        <div style="width:64px;height:64px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
          <svg width="32" height="32" fill="none" stroke="#3b82f6" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><ellipse cx="12" cy="12" rx="4" ry="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M2 7c2.5 1 5 1.5 10 1.5S19.5 8 22 7"/><path d="M2 17c2.5-1 5-1.5 10-1.5S19.5 16 22 17"/>
          </svg>
        </div>
        <h3 style="font-size:18px;font-weight:700;color:#111827">Chat &amp; Support – Resolution Metrics</h3>
        <p style="font-size:12px;color:#9ca3af;margin-top:4px">Analytics panel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:480px;margin:24px auto 0">
          <div class="mini"><p style="font-size:17px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Events today</p></div>
          <div class="mini"><p style="font-size:17px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Status</p></div>
          <div class="mini"><p style="font-size:17px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════
         TRANSACTIONS PANELS
         ═══════════════════════════════════════ --}}

    {{-- Cart Analytics --}}
    <div id="panel-transactions-cart-analytics" class="panel p-4">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- Left: Cart Abandonment by Step --}}
        <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:16px">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
            <span style="width:10px;height:10px;border-radius:50%;background:#10b981;flex-shrink:0;display:inline-block"></span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#10b981;white-space:nowrap">Cart Abandonment By Step</span>
            <span style="flex:1;height:1px;background:#a7f3d0;display:block"></span>
          </div>
          @foreach([
            ['Item added',       '48,200', null,   null],
            ['Cart viewed',      '41,800', '-13%', '#ef4444'],
            ['Checkout started', '32,400', '-22%', '#ef4444'],
            ['Address entered',  '28,900', '-11%', '#ef4444'],
            ['Payment reached',  '22,100', '-24%', '#ef4444'],
            ['Order confirmed',  '18,900', '-14%', '#ef4444'],
          ] as [$step,$count,$change,$cc])
          <div style="display:grid;grid-template-columns:1fr 80px 60px;align-items:center;padding:13px 0;border-bottom:1px solid #f3f4f6">
            <span style="font-size:13px;color:#111827;font-weight:500">{{ $step }}</span>
            <span style="font-size:13px;color:#374151;text-align:right">{{ $count }}</span>
            @if($change)
            <span style="font-size:12px;font-weight:700;color:{{ $cc }};text-align:right">{{ $change }}</span>
            @else
            <span></span>
            @endif
          </div>
          @endforeach
        </div>

        {{-- Right: Items Left in Abandoned Carts --}}
        <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:16px">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
            <span style="width:10px;height:10px;border-radius:50%;background:#10b981;flex-shrink:0;display:inline-block"></span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#10b981;white-space:nowrap">Items Left in Abandoned Carts</span>
            <span style="flex:1;height:1px;background:#a7f3d0;display:block"></span>
          </div>
          @foreach([
            ['Electronics',   '4,200 items','4200','#10b981', 78],
            ['Apparel',       '4,200 items','4200','#3b82f6', 70],
            ['Home & Living', '4,200 items','4200','#8b5cf6', 65],
            ['Beauty',        '4,200 items','4200','#f59e0b', 58],
            ['Sports',        '4,200 items','4200','#ec4899', 52],
          ] as [$cat,$items,$val,$c,$w])
          <div style="margin-bottom:12px">
            <div style="display:grid;grid-template-columns:1fr auto auto;gap:0 12px;align-items:center;margin-bottom:5px">
              <span style="font-size:12px;color:#374151;font-weight:500">{{ $cat }}</span>
              <span style="font-size:11px;color:#9ca3af">{{ $items }}</span>
              <span style="font-size:13px;font-weight:700;color:{{ $c }};min-width:40px;text-align:right">{{ $val }}</span>
            </div>
            <div style="height:5px;background:#f3f4f6;border-radius:999px;overflow:hidden">
              <div style="height:5px;width:{{ $w }}%;background:{{ $c }};border-radius:999px"></div>
            </div>
          </div>
          @endforeach
          <div style="margin-top:18px;padding-top:14px;border-top:1px solid #f3f4f6">
            <p style="font-size:28px;font-weight:700;color:#f59e0b;line-height:1;margin:0">$2.8M</p>
            <p style="font-size:11px;color:#9ca3af;margin-top:6px">cart value abandoned today — recoverable via re-engagement</p>
          </div>
        </div>

      </div>
    </div>

    {{-- Payment Methods --}}
    <div id="panel-transactions-payment-methods" class="panel p-4">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- Left: Payment Method Distribution --}}
        <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:16px">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
            <span style="width:10px;height:10px;border-radius:50%;background:#10b981;flex-shrink:0;display:inline-block"></span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#10b981;white-space:nowrap">Payment Method Distribution</span>
            <span style="flex:1;height:1px;background:#a7f3d0;display:block"></span>
          </div>
          @foreach([
            ['Credit card',        52, '#10b981'],
            ['Apple / Google Pay', 24, '#3b82f6'],
            ['PayPal',             14, '#8b5cf6'],
            ['Bank Transfer',      55, '#f59e0b'],
            ['Buy Now Pay Later',  66, '#ec4899'],
          ] as [$method,$pct,$c])
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <span style="font-size:12px;color:#374151">{{ $method }}</span>
              <span style="font-size:12px;font-weight:700;color:{{ $c }}">{{ $pct }}%</span>
            </div>
            <div style="height:5px;background:#f3f4f6;border-radius:999px;overflow:hidden">
              <div style="height:5px;width:{{ $pct }}%;background:{{ $c }};border-radius:999px"></div>
            </div>
          </div>
          @endforeach
        </div>

        {{-- Right: Payment Failure Reason Codes --}}
        <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:16px">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
            <span style="width:10px;height:10px;border-radius:50%;background:#10b981;flex-shrink:0;display:inline-block"></span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#10b981;white-space:nowrap">Payment Failure Reason Codes</span>
            <span style="flex:1;height:1px;background:#a7f3d0;display:block"></span>
          </div>
          <div style="display:grid;grid-template-columns:1fr 80px 70px;font-size:10px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;padding-bottom:8px;border-bottom:1px solid #f3f4f6">
            <span>Code</span><span style="text-align:right">Count</span><span style="text-align:right">Rate</span>
          </div>
          @foreach([
            ['insufficient_funds', '842', '0.51%', '#f59e0b'],
            ['card_declined',      '842', '0.51%', '#ef4444'],
            ['expired_card',       '842', '0.51%', '#9ca3af'],
            ['network_error',      '842', '0.51%', '#14b8a6'],
            ['do_not_honor',       '842', '0.51%', '#6366f1'],
          ] as [$code,$count,$rate,$c])
          <div style="display:grid;grid-template-columns:1fr 80px 70px;align-items:center;padding:12px 0;border-bottom:1px solid #f9fafb">
            <span style="font-size:12px;font-weight:600;color:{{ $c }};font-family:monospace">{{ $code }}</span>
            <span style="font-size:12px;color:#374151;text-align:right">{{ $count }}</span>
            <span style="font-size:12px;font-weight:600;color:{{ $c }};text-align:right">{{ $rate }}</span>
          </div>
          @endforeach
        </div>

      </div>
    </div>

    {{-- Failure Codes, Refunds, Repeat Purchase — globe panels --}}
    @foreach([
      ['failure-codes',   'Transactions – Failure Codes',  '340k/day'],
      ['refunds',         'Transactions – Refunds',         '340k/day'],
      ['repeat-purchase', 'Transactions – Repeat Purchase', '340k/day'],
    ] as [$tid,$title,$val])
    <div id="panel-transactions-{{ $tid }}" class="panel p-6">
      <div style="text-align:center;padding:32px 0">
        <div style="width:72px;height:72px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.4">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 2C12 2 8 6 8 12s4 10 4 10M12 2c0 0 4 4 4 10s-4 10-4 10"/>
            <path d="M2 12h20M3.5 7h17M3.5 17h17"/>
          </svg>
        </div>
        <h3 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:6px">{{ $title }}</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:28px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:500px;margin:0 auto">
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#10b981">{{ $val }}</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>
    @endforeach

    {{-- ═══════════════════════════════════════
         SOCIAL SIGNALS PANELS
         ═══════════════════════════════════════ --}}

    {{-- Call Events --}}
    <div id="panel-social-call-events" class="panel p-6">
      <div style="text-align:center;padding:32px 0">
        <div style="width:72px;height:72px;border-radius:50%;background:#ccfbf1;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#14b8a6" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
          </svg>
        </div>
        <h3 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:6px">Social Signals – Call Events</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:28px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:500px;margin:0 auto">
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- IVR Path Analysis --}}
    <div id="panel-social-ivr-path-analysis" class="panel p-6">
      <div style="text-align:center;padding:32px 0">
        <div style="width:72px;height:72px;border-radius:50%;background:#ccfbf1;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#14b8a6" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
          </svg>
        </div>
        <h3 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:6px">Social Signals – IVR Path Analysis</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:28px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:500px;margin:0 auto">
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- Speech Sentiment --}}
    <div id="panel-social-speech-sentiment" class="panel p-6">
      <div style="text-align:center;padding:32px 0">
        <div style="width:72px;height:72px;border-radius:50%;background:#ccfbf1;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#14b8a6" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
          </svg>
        </div>
        <h3 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:6px">Social Signals – Speech Sentiment</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:28px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:500px;margin:0 auto">
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════
         EMAIL ENGAGEMENT PANELS
         ═══════════════════════════════════════ --}}

    {{-- Delivery & Opens --}}
    <div id="panel-email-delivery-opens" class="panel p-4" style="position:relative;">

      {{-- MAIN VIEW --}}
      <div id="do-main-view" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;height:100%">

        {{-- LEFT: Funnel Bars --}}
        <div class="panel-card">
          <div class="section-header" style="margin-bottom:18px">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title" style="color:#3b82f6">Email Delivery &amp; Open Funnel</span>
            <div class="section-line" style="background:#bfdbfe"></div>
          </div>
          @foreach($emailFunnel as $item)
          @php $key = strtolower($item['label']); @endphp
          <div id="funnel-row-{{ $key }}"
               onclick="showFunnelEmails('{{ $key }}')"
               style="margin-bottom:16px;cursor:pointer;border-radius:6px;padding:5px 7px;transition:background .15s;"
               onmouseenter="this.style.background='#f9fafb'"
               onmouseleave="this.style.background='transparent'">
            <div style="display:flex;align-items:center;margin-bottom:5px">
              <span style="font-size:12px;color:#374151;flex:1">{{ $item['label'] }}</span>
              <span style="font-size:10px;color:#9ca3af;margin-right:6px;display:flex;align-items:center;gap:2px;">
                View IDs
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
              </span>
              <span style="font-size:11px;color:#9ca3af;margin-right:10px">{{ $item['count'] }}</span>
              <span style="font-size:12px;font-weight:700;color:{{ $item['color'] }};min-width:32px;text-align:right">{{ $item['pct'] }}%</span>
            </div>
            <div class="bar-wrap" style="height:4px">
              <div class="bar-fill" style="width:{{ max(3,$item['pct']) }}%;background:{{ $item['color'] }};height:4px"></div>
            </div>
          </div>
          @endforeach
        </div>

        {{-- RIGHT: Device Stats + Time to Open --}}
        <div style="display:flex;flex-direction:column;gap:14px">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            @foreach($emailDevices as $item)
            <div class="mini" style="padding:16px 12px">
              <p style="font-size:20px;font-weight:800;color:{{ $item['color'] }};letter-spacing:-0.5px">{{ $item['count'] }}</p>
              <p style="font-size:11px;color:#9ca3af;margin-top:5px">{{ $item['label'] }}</p>
            </div>
            @endforeach
          </div>
          <div class="panel-card" style="flex:1">
            <div class="section-header" style="margin-bottom:18px">
              <div class="section-dot" style="background:#3b82f6"></div>
              <span class="section-title" style="color:#3b82f6">Time to Open Distributions</span>
              <div class="section-line" style="background:#bfdbfe"></div>
            </div>
            @foreach($emailTimeToOpen as $item)
            <div style="margin-bottom:20px">
              <div style="display:flex;align-items:center;margin-bottom:5px">
                <span style="font-size:12px;color:#374151;flex:1">{{ $item['label'] }}</span>
                <span style="font-size:11px;color:#9ca3af;margin-right:10px">{{ $item['count'] }}</span>
                <span style="font-size:12px;font-weight:700;color:{{ $item['color'] }};min-width:32px;text-align:right">{{ $item['pct'] }}%</span>
              </div>
              <div class="bar-wrap" style="height:4px">
                <div class="bar-fill" style="width:{{ max(3,$item['pct']) }}%;background:{{ $item['color'] }};height:4px"></div>
              </div>
            </div>
            @endforeach
          </div>
        </div>

      </div>{{-- /do-main-view --}}

      {{-- EMAIL VIEW (full-panel, hidden by default) --}}
      <div id="do-email-view" style="display:none;flex-direction:column;height:100%;">

        {{-- Header bar --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-shrink:0;">
          <button onclick="hideFunnelEmails()"
                  style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:#6b7280;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:7px;padding:5px 12px;cursor:pointer;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back
          </button>
          <div id="do-email-dot" style="width:8px;height:8px;border-radius:50%;flex-shrink:0;"></div>
          <span id="do-email-title" style="font-size:13px;font-weight:700;"></span>
          <span id="do-email-badge" style="font-size:10px;font-weight:700;border-radius:20px;padding:3px 11px;"></span>
        </div>

        {{-- Full-width email grid --}}
        <div id="do-email-list" style="flex:1;overflow-y:auto;display:grid;grid-template-columns:1fr 1fr;gap:10px;align-content:start;"></div>

      </div>{{-- /do-email-view --}}

      <script>
        var currentFunnelKey = null;
        var funnelEmailGroups = @json($emailGroups);
        var funnelMeta = {
          sent:      { label:'Sent',      color:'#5eead4', bg:'#f0fdf4', badge:'#16a34a' },
          delivered: { label:'Delivered', color:'#60a5fa', bg:'#dbeafe', badge:'#3b82f6' },
          open:      { label:'Opened',    color:'#a78bfa', bg:'#ede9fe', badge:'#7c3aed' },
          clicked:   { label:'Clicked',   color:'#ec4899', bg:'#fce7f3', badge:'#db2777' },
          converted: { label:'Converted', color:'#6366f1', bg:'#eef2ff', badge:'#4f46e5' },
        };

        function showFunnelEmails(key) {
          currentFunnelKey = key;
          var meta   = funnelMeta[key];
          var emails = funnelEmailGroups[key] || [];

          document.getElementById('do-email-dot').style.background   = meta.color;
          document.getElementById('do-email-title').textContent       = meta.label + ' — Email IDs';
          document.getElementById('do-email-title').style.color       = meta.color;
          var badge = document.getElementById('do-email-badge');
          badge.textContent       = emails.length + ' emails';
          badge.style.color       = meta.badge;
          badge.style.background  = meta.bg;

          var list = document.getElementById('do-email-list');
          list.innerHTML = emails.length === 0
            ? '<div style="grid-column:1/-1;text-align:center;padding:40px 0;color:#9ca3af;font-size:12px;">No emails in this category.</div>'
            : emails.map(function(email) {
                return '<div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;">'
                  + '<div style="width:28px;height:28px;border-radius:50%;background:' + meta.bg + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">'
                  +   '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="' + meta.color + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
                  +     '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>'
                  +   '</svg>'
                  + '</div>'
                  + '<span style="font-size:12px;color:#111827;font-weight:500;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + email + '</span>'
                  + '<span style="font-size:10px;font-weight:600;color:' + meta.badge + ';background:' + meta.bg + ';border-radius:4px;padding:2px 7px;flex-shrink:0;">' + meta.label + '</span>'
                  + '</div>';
              }).join('');

          document.getElementById('do-main-view').style.display  = 'none';
          document.getElementById('do-email-view').style.display = 'flex';
        }

        function hideFunnelEmails() {
          currentFunnelKey = null;
          document.getElementById('do-email-view').style.display = 'none';
          document.getElementById('do-main-view').style.display  = 'grid';
        }
      </script>

    </div>

    {{-- Link Clicks --}}
    <div id="panel-email-link-clicks" class="panel p-4" style="position:relative;">
      @php
        $openCount      = collect($emailFunnel)->firstWhere('label','Open')['count']      ?? 0;
        $clickedCount   = collect($emailFunnel)->firstWhere('label','Clicked')['count']   ?? 0;
        $convertedCount = collect($emailFunnel)->firstWhere('label','Converted')['count'] ?? 0;
        $baseOpen       = max(1, (int)$openCount);
        $clickBars = [
          ['label'=>'Opened',    'key'=>'open',      'count'=>$openCount,      'pct'=>100,                                           'color'=>'#a78bfa','bg'=>'#ede9fe','badge'=>'#7c3aed'],
          ['label'=>'Clicked',   'key'=>'clicked',   'count'=>$clickedCount,   'pct'=>round((int)$clickedCount   / $baseOpen * 100), 'color'=>'#ec4899','bg'=>'#fce7f3','badge'=>'#db2777'],
          ['label'=>'Converted', 'key'=>'converted', 'count'=>$convertedCount, 'pct'=>round((int)$convertedCount / $baseOpen * 100), 'color'=>'#6366f1','bg'=>'#eef2ff','badge'=>'#4f46e5'],
        ];
      @endphp

      {{-- MAIN VIEW --}}
      <div id="lc-main-view" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;height:100%">
        <div class="panel-card">
          <div class="section-header" style="margin-bottom:18px">
            <div class="section-dot" style="background:#ec4899"></div>
            <span class="section-title" style="color:#ec4899">Link Click Funnel</span>
            <div class="section-line" style="background:#fce7f3"></div>
          </div>
          @foreach($clickBars as $bar)
          <div id="lc-row-{{ $bar['key'] }}"
               onclick="showClickEmails('{{ $bar['key'] }}')"
               style="margin-bottom:20px;cursor:pointer;border-radius:6px;padding:5px 7px;transition:background .15s;"
               onmouseenter="this.style.background='#f9fafb'"
               onmouseleave="this.style.background='transparent'">
            <div style="display:flex;align-items:center;margin-bottom:5px">
              <span style="font-size:12px;color:#374151;flex:1">{{ $bar['label'] }}</span>
              <span style="font-size:10px;color:#9ca3af;margin-right:6px;display:flex;align-items:center;gap:2px;">
                View IDs
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
              </span>
              <span style="font-size:11px;color:#9ca3af;margin-right:10px">{{ $bar['count'] }}</span>
              <span style="font-size:12px;font-weight:700;color:{{ $bar['color'] }};min-width:32px;text-align:right">{{ $bar['pct'] }}%</span>
            </div>
            <div class="bar-wrap" style="height:4px">
              <div class="bar-fill" style="width:{{ max(3,$bar['pct']) }}%;background:{{ $bar['color'] }};height:4px"></div>
            </div>
          </div>
          @endforeach
        </div>
        <div></div>
      </div>

      {{-- EMAIL VIEW (full-panel flip) --}}
      <div id="lc-email-view" style="display:none;flex-direction:column;height:100%;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-shrink:0;">
          <button onclick="hideClickEmails()"
                  style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:#6b7280;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:7px;padding:5px 12px;cursor:pointer;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back
          </button>
          <div id="lc-email-dot" style="width:8px;height:8px;border-radius:50%;flex-shrink:0;"></div>
          <span id="lc-email-title" style="font-size:13px;font-weight:700;"></span>
          <span id="lc-email-badge" style="font-size:10px;font-weight:700;border-radius:20px;padding:3px 11px;"></span>
        </div>
        <div id="lc-email-list" style="flex:1;overflow-y:auto;display:grid;grid-template-columns:1fr 1fr;gap:10px;align-content:start;"></div>
      </div>

      <script>
        var clickMeta = {
          open:      { label:'Opened',    color:'#a78bfa', bg:'#ede9fe', badge:'#7c3aed' },
          clicked:   { label:'Clicked',   color:'#ec4899', bg:'#fce7f3', badge:'#db2777' },
          converted: { label:'Converted', color:'#6366f1', bg:'#eef2ff', badge:'#4f46e5' },
        };

        function showClickEmails(key) {
          var meta   = clickMeta[key];
          var emails = funnelEmailGroups[key] || [];

          document.getElementById('lc-email-dot').style.background  = meta.color;
          document.getElementById('lc-email-title').textContent      = meta.label + ' — Email IDs';
          document.getElementById('lc-email-title').style.color      = meta.color;
          var badge = document.getElementById('lc-email-badge');
          badge.textContent      = emails.length + ' emails';
          badge.style.color      = meta.badge;
          badge.style.background = meta.bg;

          var list = document.getElementById('lc-email-list');
          list.innerHTML = emails.length === 0
            ? '<div style="grid-column:1/-1;text-align:center;padding:40px 0;color:#9ca3af;font-size:12px;">No emails in this category.</div>'
            : emails.map(function(email) {
                return '<div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;">'
                  + '<div style="width:28px;height:28px;border-radius:50%;background:' + meta.bg + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">'
                  +   '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="' + meta.color + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
                  +     '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>'
                  +   '</svg>'
                  + '</div>'
                  + '<span style="font-size:12px;color:#111827;font-weight:500;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + email + '</span>'
                  + '<span style="font-size:10px;font-weight:600;color:' + meta.badge + ';background:' + meta.bg + ';border-radius:4px;padding:2px 7px;flex-shrink:0;">' + meta.label + '</span>'
                  + '</div>';
              }).join('');

          document.getElementById('lc-main-view').style.display  = 'none';
          document.getElementById('lc-email-view').style.display = 'flex';
        }

        function hideClickEmails() {
          document.getElementById('lc-email-view').style.display = 'none';
          document.getElementById('lc-main-view').style.display  = 'grid';
        }
      </script>

    </div>

    {{-- Unsubscribe --}}
    <div id="panel-email-unsubscribe" class="panel p-6">

      {{-- Header --}}
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:24px">
        <div style="width:10px;height:10px;border-radius:50%;background:#ef4444"></div>
        <span style="font-size:15px;font-weight:700;color:#111827;letter-spacing:.3px">Email – Unsubscribe</span>
        <div style="flex:1;height:1px;background:#fee2e2;margin-left:8px"></div>
        <span style="font-size:12px;font-weight:600;color:#ef4444;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:2px 10px">Live</span>
      </div>

      {{-- 4 Stat Cards --}}
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px">
        <div class="panel-card" style="text-align:center;padding:16px 10px">
          <p style="font-size:24px;font-weight:800;color:#3b82f6;line-height:1">{{ $sent }}</p>
          <p style="font-size:11px;color:#6b7280;margin-top:6px;font-weight:500">Total Sent</p>
        </div>
        <div class="panel-card" style="text-align:center;padding:16px 10px">
          <p style="font-size:24px;font-weight:800;color:#ef4444;line-height:1">{{ $unsubscribed }}</p>
          <p style="font-size:11px;color:#6b7280;margin-top:6px;font-weight:500">Unsubscribed</p>
        </div>
        <div class="panel-card" style="text-align:center;padding:16px 10px">
          <p style="font-size:24px;font-weight:800;color:#10b981;line-height:1">{{ $retained }}</p>
          <p style="font-size:11px;color:#6b7280;margin-top:6px;font-weight:500">Retained</p>
        </div>
        <div class="panel-card" style="text-align:center;padding:16px 10px">
          <p style="font-size:24px;font-weight:800;color:#f59e0b;line-height:1">{{ $unsubscribeRate }}%</p>
          <p style="font-size:11px;color:#6b7280;margin-top:6px;font-weight:500">Unsub Rate</p>
        </div>
      </div>

      {{-- Two charts side by side --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- Left: Bar chart --}}
        <div class="panel-card" style="padding:24px">
          <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:4px">Retention Overview</p>
          <p style="font-size:11px;color:#9ca3af;margin-bottom:20px">Total emails sent breakdown</p>
          <div style="position:relative;height:260px">
            <canvas id="unsubBarChart"></canvas>
          </div>
        </div>

        {{-- Right: Donut chart --}}
        <div class="panel-card" style="padding:24px;display:flex;flex-direction:column;align-items:center">
          <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;align-self:flex-start">Sent vs Unsubscribed Ratio</p>
          <p style="font-size:11px;color:#9ca3af;margin-bottom:16px;align-self:flex-start">Proportion of retained vs churned</p>
          <div style="position:relative;width:200px;height:200px;margin:10px auto 0">
            <canvas id="unsubDonutChart"></canvas>
            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none">
              <p style="font-size:28px;font-weight:800;color:#111827;line-height:1">{{ $unsubscribeRate }}%</p>
              <p style="font-size:10px;color:#9ca3af;margin-top:3px">Unsub Rate</p>
            </div>
          </div>
          <div style="display:flex;gap:20px;margin-top:20px">
            <div style="display:flex;align-items:center;gap:7px">
              <div style="width:11px;height:11px;border-radius:3px;background:#10b981"></div>
              <span style="font-size:12px;color:#374151">Retained ({{ $retained }})</span>
            </div>
            <div style="display:flex;align-items:center;gap:7px">
              <div style="width:11px;height:11px;border-radius:3px;background:#ef4444"></div>
              <span style="font-size:12px;color:#374151">Unsubscribed ({{ $unsubscribed }})</span>
            </div>
          </div>
        </div>

      </div>

    </div>

    {{-- ═══════════════════════════════════════
         AD CAMPAIGNS PANELS
         ═══════════════════════════════════════ --}}

    {{-- Impression --}}
    <div id="panel-ads-impression" class="panel p-4">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

        {{-- Left: Impression & Viewability Metrics --}}
        <div class="panel-card">
          <div class="section-header" style="margin-bottom:16px">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title" style="color:#3b82f6">Impression &amp; Viewability Metrics</span>
            <div class="section-line" style="background:#bfdbfe"></div>
          </div>
          @foreach([
            ['Total Impressions',    '4.2M'],
            ['Viewable Impressions', '68M'],
            ['Viewability rate',     '14%'],
            ['Avg Frequency User',   '32x'],
          ] as [$label,$val])
          <div style="display:flex;justify-content:space-between;align-items:center;background:#f9fafb;border-radius:8px;padding:12px 16px;margin-bottom:8px">
            <span style="font-size:13px;color:#374151">{{ $label }}</span>
            <span style="font-size:14px;font-weight:700;color:#f59e0b">{{ $val }}</span>
          </div>
          @endforeach
        </div>

        {{-- Right: Ad Format CTR / CVR --}}
        <div class="panel-card">
          <div class="section-header" style="margin-bottom:16px">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title" style="color:#3b82f6">Impression &amp; Viewability Metrics</span>
            <div class="section-line" style="background:#bfdbfe"></div>
          </div>
          @foreach([
            'Video (pre-roll)',
            'Carousel',
            'Static Banner',
            'Native',
            'Responsive Search',
          ] as $format)
          <div style="display:flex;align-items:center;padding:13px 0;border-bottom:1px solid #f3f4f6">
            <span style="font-size:13px;color:#374151;flex:1">{{ $format }}</span>
            <span style="font-size:13px;font-weight:700;color:#10b981;width:100px;text-align:center">CTR 2.8%</span>
            <span style="font-size:13px;font-weight:600;color:#f59e0b;width:80px;text-align:right">CVR 1.2%</span>
          </div>
          @endforeach
        </div>

      </div>
    </div>

    {{-- Clicks & CTR --}}
    <div id="panel-ads-clicks-ctr" class="panel p-6">
      <div style="text-align:center;padding:32px 0">
        <div style="width:72px;height:72px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 11l19-9-9 19-2-8-8-2z"/>
          </svg>
        </div>
        <h3 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:6px">Ad Campaigns – Clicks &amp; CTR</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:28px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:500px;margin:0 auto">
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- Conversions --}}
    <div id="panel-ads-conversions" class="panel p-6">
      <div style="text-align:center;padding:32px 0">
        <div style="width:72px;height:72px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 11l19-9-9 19-2-8-8-2z"/>
          </svg>
        </div>
        <h3 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:6px">Ad Campaigns – Conversions</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:28px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:500px;margin:0 auto">
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════
         SURVEYS & FEEDBACK PANELS
         ═══════════════════════════════════════ --}}

    {{-- NPS Score --}}
    <div id="panel-surveys-nps-score" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">

        {{-- Left: NPS Score Distribution --}}
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#14b8a6"></div>
            <span class="section-title teal-section-title">NPS Score Distribution</span>
            <div class="section-line teal-section-line"></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:20px">
            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px 10px;text-align:center">
              <p style="font-size:26px;font-weight:800;color:#14b8a6;line-height:1">18%</p>
              <p style="font-size:11px;font-weight:600;color:#374151;margin-top:6px">Detractors</p>
              <p style="font-size:9px;color:#9ca3af;margin-top:3px;text-transform:uppercase;letter-spacing:.05em">Score 0–6</p>
            </div>
            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px 10px;text-align:center">
              <p style="font-size:26px;font-weight:800;color:#3b82f6;line-height:1">$2.8M</p>
              <p style="font-size:11px;font-weight:600;color:#374151;margin-top:6px">Passives</p>
              <p style="font-size:9px;color:#9ca3af;margin-top:3px;text-transform:uppercase;letter-spacing:.05em">Score 7–8</p>
            </div>
            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px 10px;text-align:center">
              <p style="font-size:26px;font-weight:800;color:#10b981;line-height:1">$2.8M</p>
              <p style="font-size:11px;font-weight:600;color:#374151;margin-top:6px">Promoters</p>
              <p style="font-size:9px;color:#9ca3af;margin-top:3px;text-transform:uppercase;letter-spacing:.05em">Score 9–12</p>
            </div>
          </div>
          <div style="text-align:center;padding:18px 0 6px">
            <p style="font-size:32px;font-weight:800;color:#f59e0b;line-height:1">$2.8M</p>
            <p style="font-size:12px;font-weight:500;color:#374151;margin-top:6px">Net promoter score</p>
            <p style="font-size:11px;color:#10b981;margin-top:5px">▲ +14% vs last quarter</p>
          </div>
        </div>

        {{-- Right: NPS Score Distribution by survey type --}}
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#14b8a6"></div>
            <span class="section-title teal-section-title">NPS Score Distribution</span>
            <div class="section-line teal-section-line"></div>
          </div>
          @foreach([
            ['Post Purchase',    '6,230','8,420', 74, '#14b8a6', '18%'],
            ['On-boarding exit', '2,100','3,840', 55, '#f59e0b', '24%'],
            ['Annual NPS',       '2,100','7,840', 27, '#3b82f6', '31%'],
            ['Feature Feedback', '2,100','3,840', 55, '#8b5cf6', '27%'],
          ] as [$label,$curr,$total,$bar,$color,$pct])
          <div style="margin-bottom:18px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
              <span style="font-size:12px;color:#374151;font-weight:500">{{ $label }}</span>
              <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:11px;color:#9ca3af">{{ $curr }}/{{ $total }}</span>
                <span style="font-size:11px;font-weight:700;color:{{ $color }}">{{ $pct }}</span>
              </div>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ $bar }}%;background:{{ $color }}"></div></div>
          </div>
          @endforeach
        </div>

      </div>
    </div>

    {{-- CSAT --}}
    <div id="panel-surveys-csat" class="panel p-5">
      <div class="globe-panel" style="max-width:560px;margin:0 auto;padding:48px 40px">
        <div style="width:72px;height:72px;border-radius:16px;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="36" height="36" fill="none" stroke="#3b82f6" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
          </svg>
        </div>
        <h3 style="font-size:22px;font-weight:700;color:#111827;margin-bottom:8px">Surveys – CSAT</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:32px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- Open Text Sentiment --}}
    <div id="panel-surveys-open-text" class="panel p-5">
      <div class="globe-panel" style="max-width:560px;margin:0 auto;padding:48px 40px">
        <div style="width:72px;height:72px;border-radius:16px;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="36" height="36" fill="none" stroke="#3b82f6" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
          </svg>
        </div>
        <h3 style="font-size:22px;font-weight:700;color:#111827;margin-bottom:8px">Surveys – Open Text Sentiment</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:32px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════
         LOYALTY / REFERRAL PANELS
         ═══════════════════════════════════════ --}}

    {{-- Points Activity --}}
    <div id="panel-loyalty-points-activity" class="panel p-4">
      <div class="grid grid-cols-2 gap-4">

        {{-- Left: Points Earned by Action Type --}}
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#14b8a6"></div>
            <span class="section-title teal-section-title">Points Earned By Action Type</span>
            <div class="section-line teal-section-line"></div>
          </div>
          @foreach([
            ['Purchase',         280000, 280000, '#14b8a6'],
            ['Referral',          34000,  24000, '#3b82f6'],
            ['Review',           310000, 310000, '#f59e0b'],
            ['Social Share',      17000,  27000, '#f97316'],
            ['Profile Complete',  27000,  27000, '#ef4444'],
          ] as [$label,$pts,$target,$color])
          <div style="margin-bottom:16px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
              <span style="font-size:12px;color:#374151;font-weight:500">{{ $label }}</span>
              <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:11px;color:#9ca3af">{{ number_format($pts) }} pts</span>
                <span style="font-size:11px;font-weight:700;color:{{ $color }}">{{ number_format($target) }}</span>
              </div>
            </div>
            <div class="bar-wrap"><div class="bar-fill" style="width:{{ min(100, round($pts/$target*100)) }}%;background:{{ $color }}"></div></div>
          </div>
          @endforeach
        </div>

        {{-- Right: Tier Movement Events --}}
        <div class="panel-card">
          <div class="section-header">
            <div class="section-dot" style="background:#14b8a6"></div>
            <span class="section-title teal-section-title">Tier Movements Events</span>
            <div class="section-line teal-section-line"></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            @foreach([
              ['1,240', '#14b8a6', 'Upgraded',   'Bronze → Silver'],
              ['423',   '#3b82f6', 'Passives',   'Score 7–8'],
              ['211',   '#14b8a6', 'Detractors', 'Score 0–6'],
              ['542',   '#3b82f6', 'Passives',   'Score 7–8'],
            ] as [$val,$color,$label,$sub])
            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px 12px;text-align:center">
              <p style="font-size:28px;font-weight:800;color:{{ $color }};line-height:1">{{ $val }}</p>
              <p style="font-size:11px;font-weight:600;color:#374151;margin-top:6px">{{ $label }}</p>
              <p style="font-size:9px;color:#9ca3af;margin-top:3px;text-transform:uppercase;letter-spacing:.05em">{{ $sub }}</p>
            </div>
            @endforeach
          </div>
        </div>

      </div>
    </div>

    {{-- Tier Movements --}}
    <div id="panel-loyalty-tier-movements" class="panel p-5">
      <div class="globe-panel" style="max-width:560px;margin:0 auto;padding:48px 40px">
        <div style="width:72px;height:72px;border-radius:16px;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="36" height="36" fill="none" stroke="#3b82f6" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
          </svg>
        </div>
        <h3 style="font-size:22px;font-weight:700;color:#111827;margin-bottom:8px">Loyalty – Tier Movements</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:32px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- Referral Tracking --}}
    <div id="panel-loyalty-referral-tracking" class="panel p-5">
      <div class="globe-panel" style="max-width:560px;margin:0 auto;padding:48px 40px">
        <div style="width:72px;height:72px;border-radius:16px;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="36" height="36" fill="none" stroke="#3b82f6" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
          </svg>
        </div>
        <h3 style="font-size:22px;font-weight:700;color:#111827;margin-bottom:8px">Loyalty – Referral Tracking</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:32px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════
         SOCIAL SIGNALS PANELS
         ═══════════════════════════════════════ --}}

    @foreach([
      ['brand-mentions',   'Social Signals – Brand Mentions'],
      ['hashtag-tracking', 'Social Signals – Hashtag Tracking'],
      ['sentiment',        'Social Signals – Sentiment Analysis'],
    ] as [$tid, $title])
    <div id="panel-social-{{ $tid }}" class="panel p-5">
      <div class="globe-panel" style="max-width:560px;margin:0 auto;padding:48px 40px">
        <div style="width:72px;height:72px;border-radius:16px;background:#fce7f3;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="36" height="36" fill="none" stroke="#ec4899" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
          </svg>
        </div>
        <h3 style="font-size:22px;font-weight:700;color:#111827;margin-bottom:8px">{{ $title }}</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:32px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>
    @endforeach

    {{-- ═══════════════════════════════════════
         CALL CENTER PANELS
         ═══════════════════════════════════════ --}}

    {{-- Call Events (dynamic) --}}
    <div id="panel-callcenter-call-events" class="panel p-6">

      {{-- Header --}}
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:24px">
        <div style="width:10px;height:10px;border-radius:50%;background:#3b82f6"></div>
        <span style="font-size:15px;font-weight:700;color:#111827;letter-spacing:.3px">Call Center – Call Events</span>
        <div style="flex:1;height:1px;background:#bfdbfe;margin-left:8px"></div>
        <span style="font-size:12px;font-weight:600;color:#3b82f6;background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:2px 10px">Live</span>
      </div>

      {{-- 5 Stat Cards --}}
      <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:24px">
        <div class="panel-card" style="text-align:center;padding:16px 8px">
          <p style="font-size:22px;font-weight:800;color:#3b82f6;line-height:1">{{ $callTotal }}</p>
          <p style="font-size:11px;color:#6b7280;margin-top:6px;font-weight:500">Total Calls</p>
        </div>
        <div class="panel-card" style="text-align:center;padding:16px 8px">
          <p style="font-size:22px;font-weight:800;color:#6366f1;line-height:1">{{ $callInbound }}</p>
          <p style="font-size:11px;color:#6b7280;margin-top:6px;font-weight:500">Inbound</p>
        </div>
        <div class="panel-card" style="text-align:center;padding:16px 8px">
          <p style="font-size:22px;font-weight:800;color:#8b5cf6;line-height:1">{{ $callOutbound }}</p>
          <p style="font-size:11px;color:#6b7280;margin-top:6px;font-weight:500">Outbound</p>
        </div>
        <div class="panel-card" style="text-align:center;padding:16px 8px">
          <p style="font-size:22px;font-weight:800;color:#10b981;line-height:1">{{ $callAnswered }}</p>
          <p style="font-size:11px;color:#6b7280;margin-top:6px;font-weight:500">Answered</p>
        </div>
        <div class="panel-card" style="text-align:center;padding:16px 8px">
          <p style="font-size:22px;font-weight:800;color:#ef4444;line-height:1">{{ $callMissed }}</p>
          <p style="font-size:11px;color:#6b7280;margin-top:6px;font-weight:500">Missed</p>
        </div>
      </div>

      {{-- Secondary metrics row --}}
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px">
        <div class="panel-card" style="display:flex;align-items:center;gap:12px;padding:14px 16px">
          <div style="width:36px;height:36px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <p style="font-size:16px;font-weight:700;color:#111827">${{ $callTotalCost }}</p>
            <p style="font-size:11px;color:#6b7280">Total Cost</p>
          </div>
        </div>
        <div class="panel-card" style="display:flex;align-items:center;gap:12px;padding:14px 16px">
          <div style="width:36px;height:36px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <p style="font-size:16px;font-weight:700;color:#111827">{{ $callAnswerRate }}%</p>
            <p style="font-size:11px;color:#6b7280">Answer Rate</p>
          </div>
        </div>
        <div class="panel-card" style="display:flex;align-items:center;gap:12px;padding:14px 16px">
          <div style="width:36px;height:36px;border-radius:10px;background:#faf5ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="18" height="18" fill="none" stroke="#8b5cf6" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <p style="font-size:16px;font-weight:700;color:#111827">{{ gmdate('i:s', $callAvgDuration) }}</p>
            <p style="font-size:11px;color:#6b7280">Avg Handle Time</p>
          </div>
        </div>
        <div class="panel-card" style="display:flex;align-items:center;gap:12px;padding:14px 16px">
          <div style="width:36px;height:36px;border-radius:10px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="18" height="18" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          </div>
          <div>
            <p style="font-size:16px;font-weight:700;color:#111827">{{ $callAvgWait }}s</p>
            <p style="font-size:11px;color:#6b7280">Avg Wait Time</p>
          </div>
        </div>
      </div>

      {{-- Charts + breakdown --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- Left: Daily volume bar chart --}}
        <div class="panel-card" style="padding:20px">
          <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:2px">Daily Call Volume</p>
          <p style="font-size:11px;color:#9ca3af;margin-bottom:16px">Last 14 days — inbound vs outbound</p>
          <div style="position:relative;height:220px">
            <canvas id="callVolumeChart"></canvas>
          </div>
          <div style="display:flex;gap:16px;margin-top:12px">
            <div style="display:flex;align-items:center;gap:6px">
              <div style="width:10px;height:10px;border-radius:2px;background:#6366f1"></div>
              <span style="font-size:11px;color:#374151">Inbound</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
              <div style="width:10px;height:10px;border-radius:2px;background:#8b5cf6"></div>
              <span style="font-size:11px;color:#374151">Outbound</span>
            </div>
          </div>
        </div>

        {{-- Right: Outcome bars + department table --}}
        <div style="display:flex;flex-direction:column;gap:14px">

          {{-- Call Outcomes --}}
          <div class="panel-card" style="padding:20px">
            <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:14px">Call Outcomes</p>
            @foreach($callOutcomes as $o)
            <div style="margin-bottom:12px">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
                <div style="display:flex;align-items:center;gap:7px">
                  <div style="width:8px;height:8px;border-radius:50%;background:{{ $o['color'] }}"></div>
                  <span style="font-size:12px;color:#374151;font-weight:500">{{ $o['label'] }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                  <span style="font-size:12px;font-weight:700;color:#111827">{{ $o['count'] }}</span>
                  <span style="font-size:11px;color:#9ca3af;width:38px;text-align:right">{{ $o['pct'] }}%</span>
                </div>
              </div>
              <div class="bar-wrap">
                <div class="bar-fill" style="width:{{ max(3, $o['pct']) }}%;background:{{ $o['color'] }}"></div>
              </div>
            </div>
            @endforeach
          </div>

          {{-- Department Breakdown --}}
          <div class="panel-card" style="padding:20px">
            <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:12px">By Department</p>
            <div style="display:flex;flex-direction:column;gap:1px">
              <div style="display:grid;grid-template-columns:1fr 60px 70px 64px;padding:6px 8px">
                <span style="font-size:10px;font-weight:600;color:#9ca3af;text-transform:uppercase">Dept</span>
                <span style="font-size:10px;font-weight:600;color:#9ca3af;text-align:center">Calls</span>
                <span style="font-size:10px;font-weight:600;color:#9ca3af;text-align:center">Avg Time</span>
                <span style="font-size:10px;font-weight:600;color:#9ca3af;text-align:right">Cost</span>
              </div>
              @foreach($callDeptStats as $dept)
              <div style="display:grid;grid-template-columns:1fr 60px 70px 64px;padding:9px 8px;background:#f9fafb;border-radius:6px;margin-bottom:4px">
                <span style="font-size:12px;font-weight:600;color:#374151">{{ $dept['department'] }}</span>
                <span style="font-size:12px;color:#6b7280;text-align:center">{{ $dept['total'] }}</span>
                <span style="font-size:12px;color:#6b7280;text-align:center">{{ gmdate('i:s', $dept['avg_dur']) }}</span>
                <span style="font-size:12px;font-weight:600;color:#10b981;text-align:right">${{ $dept['dept_cost'] }}</span>
              </div>
              @endforeach
            </div>
          </div>

        </div>
      </div>
    </div>

    {{-- IVR Path Analysis (placeholder) --}}
    <div id="panel-callcenter-ivr-path-analysis" class="panel p-5">
      <div class="globe-panel" style="max-width:560px;margin:0 auto;padding:48px 40px">
        <div style="width:72px;height:72px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" fill="none" stroke="#3b82f6" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
          </svg>
        </div>
        <h3 style="font-size:22px;font-weight:700;color:#111827;margin-bottom:8px">Call Center – IVR Path Analysis</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:32px">Analytics panel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- Speech Sentiment (placeholder) --}}
    <div id="panel-callcenter-speech-sentiment" class="panel p-5">
      <div class="globe-panel" style="max-width:560px;margin:0 auto;padding:48px 40px">
        <div style="width:72px;height:72px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" fill="none" stroke="#3b82f6" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
          </svg>
        </div>
        <h3 style="font-size:22px;font-weight:700;color:#111827;margin-bottom:8px">Call Center – Speech Sentiment</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:32px">Analytics panel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════
         POS / OFFLINE PANELS
         ═══════════════════════════════════════ --}}

    @foreach([
      ['store-visits',    'Offline POS – Store Visits'],
      ['basket-analysis', 'Offline POS – Basket Analysis'],
      ['pos-events',      'Offline POS – POS Events'],
    ] as [$tid, $title])
    <div id="panel-pos-{{ $tid }}" class="panel p-5">
      <div class="globe-panel" style="max-width:560px;margin:0 auto;padding:48px 40px">
        <div style="width:72px;height:72px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-1"/>
            <path d="M15 3H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l4-4h4a2 2 0 002-2V5a2 2 0 00-2-2z"/>
          </svg>
        </div>
        <h3 style="font-size:22px;font-weight:700;color:#111827;margin-bottom:8px">{{ $title }}</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:32px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:20px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>
    @endforeach

    {{-- ═══════════════════════════════════════
         GENERIC SOURCE PANEL
         ═══════════════════════════════════════ --}}
    <div id="panel-generic" class="panel p-5">
      <div class="globe-panel">
        <div style="width:64px;height:64px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
          <svg width="32" height="32" fill="none" stroke="#3b82f6" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><ellipse cx="12" cy="12" rx="4" ry="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M2 7c2.5 1 5 1.5 10 1.5S19.5 8 22 7"/><path d="M2 17c2.5-1 5-1.5 10-1.5S19.5 16 22 17"/>
          </svg>
        </div>
        <h3 id="generic-title" style="font-size:18px;font-weight:700;color:#111827">Source – Overview</h3>
        <p style="font-size:12px;color:#9ca3af;margin-top:4px">Analytics panel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:480px;margin:24px auto 0">
          <div class="mini"><p style="font-size:17px;font-weight:700;color:#10b981" id="generic-val">—/day</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Events today</p></div>
          <div class="mini"><p style="font-size:17px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Status</p></div>
          <div class="mini"><p style="font-size:17px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:4px">Signal Quality</p></div>
        </div>
      </div>
    </div>

  </div>{{-- /tab+panel card --}}
</div>{{-- /scrollable body --}}
</div>

@endsection

@push('scripts')
<script>
const sourceMeta = {
  'website':      {tabs:'website',  firstTab:'scroll-depth'},
  'mobile-app':   {tabs:'mobile-app', firstTab:'session'},
  'crm':          {tabs:'crm',      firstTab:'stage-transitions'},
  'transactions': {tabs:'transactions', firstTab:'cart-analytics'},
  'chat':         {tabs:'chat', firstTab:'conversation-events'},
  'email':        {tabs:'email',    firstTab:'delivery-opens'},
  'ads':          {tabs:'ads',      firstTab:'impression'},
  'social':       {tabs:'social',   firstTab:'brand-mentions'},
  'surveys':      {tabs:'surveys',  firstTab:'nps-score'},
  'loyalty':      {tabs:'loyalty',  firstTab:'points-activity'},
  'callcenter':   {tabs:'callcenter', firstTab:'call-events'},
  'pos':          {tabs:'pos', firstTab:'store-visits'},
};

let currentSrc = 'website';
let currentTab = 'scroll-depth';

function selectSource(id) {
  // update card highlight
  document.querySelectorAll('.src').forEach(el => el.classList.remove('active'));
  document.getElementById('src-' + id).classList.add('active');

  currentSrc = id;

  document.getElementById('tabs-panels-card').style.display = '';

  if (id === 'email') {
    // Panel stays hidden until loadBrevoEngagementStats confirms a provider
    // is actually connected — no placeholder card when nothing is connected.
    // Forced (not cached) so the numbers are live from Brevo on every visit,
    // without the user having to press "Sync Data" themselves.
    loadBrevoEngagementStats(true);
  } else {
    document.getElementById('email-engagement-panel').style.display = 'none';
  }

  const meta = sourceMeta[id];

  // switch tab bars
  ['website','mobile-app','crm','transactions','chat','social','email','ads','surveys','loyalty','callcenter','pos','generic'].forEach(t => {
    document.getElementById('tabs-' + t).style.display = 'none';
  });
  document.getElementById('tabs-' + meta.tabs).style.display = 'flex';

  // reset all panels
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));

  if (meta.tabs === 'generic') {
    document.getElementById('generic-title').textContent = meta.name + ' – Overview';
    document.getElementById('generic-val').textContent   = meta.val + '/day';
    document.getElementById('panel-generic').classList.add('active');
    currentTab = 'overview';
  } else {
    currentTab = meta.firstTab;
    document.getElementById('panel-' + meta.tabs + '-' + currentTab).classList.add('active');
    // reset tab button highlights
    document.querySelectorAll('#tabs-' + meta.tabs + ' .tab-btn').forEach(b => b.classList.remove('active'));
    const firstBtn = document.querySelector('#tabs-' + meta.tabs + ' .tab-btn');
    if (firstBtn) firstBtn.classList.add('active');
  }
}

/* ── Email Engagement (live Brevo data) ─────────────────────────────────── */
let brevoStats = null;
let brevoStatsLoaded = false;

function beShow(state) {
  ['be-loading','be-error','be-content'].forEach(id => {
    document.getElementById(id).style.display = 'none';
  });
  document.getElementById(state).style.display = state === 'be-content' ? '' : 'block';
}

function loadBrevoEngagementStats(force) {
  if (brevoStatsLoaded && !force) {
    // Data's already cached from an earlier load — just make sure the card
    // (hidden when the user switched to another source) is visible again.
    document.getElementById('email-engagement-panel').style.display = '';
    return Promise.resolve();
  }

  const panel = document.getElementById('email-engagement-panel');

  // Only show a loading spinner when the card is already visible (a retry
  // or refresh) — on first load we don't yet know whether a provider is
  // connected, so the whole card stays hidden until that's confirmed.
  if (panel.style.display !== 'none') {
    beShow('be-loading');
  }

  document.getElementById('be-synced').textContent = '';
  document.getElementById('be-synced-wrap').style.display = 'none';

  const url = '{{ route('client.email-connections.brevo.engagement-stats') }}' + (force ? '?refresh=1' : '');

  return fetch(url, {
    headers: { 'Accept': 'application/json' },
  })
    .then(r => r.json())
    .then(res => {
      if (!res.connected) {
        panel.style.display = 'none';
        return;
      }

      panel.style.display = '';

      if (!res.success) {
        document.getElementById('be-error-message').textContent = res.message || 'Could not load Brevo data.';
        beShow('be-error');
        return;
      }

      brevoStats = res.data;
      brevoStatsLoaded = true;
      renderBrevoStats(res.data);
      beShow('be-content');
    })
    .catch(() => {
      // Connection status unknown on a network failure — fail closed rather
      // than showing a card that might belong to no connected provider.
      panel.style.display = 'none';
    });
}

function syncBrevoData() {
  const btn = document.getElementById('be-sync-btn');
  const label = document.getElementById('be-sync-btn-label');
  if (btn.disabled) return;

  btn.disabled = true;
  btn.classList.add('syncing');
  label.textContent = 'Syncing…';

  // Kick off a fresh recipient export in the background (used by the "View"
  // email-ID lists) — fire-and-forget, since it can take a while and the
  // KPI refresh below doesn't need to wait on it.
  fetch('{{ route('client.email-connections.brevo.engagement-contacts', ['metric' => 'delivered']) }}?refresh=1', {
    headers: { 'Accept': 'application/json' },
  }).catch(() => {});

  loadBrevoEngagementStats(true).finally(() => {
    btn.disabled = false;
    btn.classList.remove('syncing');
    label.textContent = 'Sync Data';
  });
}

function beFormatNumber(n) {
  return Number(n || 0).toLocaleString('en-US');
}

function renderBrevoStats(data) {
  document.getElementById('be-delivered-value').textContent = beFormatNumber(data.delivered);
  document.getElementById('be-delivery-rate').textContent   = data.delivery_rate + '%';

  document.getElementById('be-opens-value').textContent = beFormatNumber(data.opens);
  document.getElementById('be-open-rate').textContent   = data.open_rate + '%';

  document.getElementById('be-clicks-value').textContent = beFormatNumber(data.clicks);
  document.getElementById('be-click-rate').textContent   = data.click_rate + '%';

  document.getElementById('be-conversions-value').textContent = beFormatNumber(data.conversions);
  document.getElementById('be-conversion-rate').textContent   = data.conversion_rate + '%';

  document.getElementById('be-unsub-value').textContent = beFormatNumber(data.unsubscribes);
  document.getElementById('be-unsub-rate').textContent  = data.unsubscribe_rate + '%';

  const synced = new Date(data.synced_at);
  document.getElementById('be-synced').textContent =
    'Synced from Brevo · ' + data.campaign_count + ' campaigns · ' + synced.toLocaleString();
  document.getElementById('be-synced-wrap').style.display = 'inline-flex';

  beRenderGauge('be-gauge-delivered',    data.delivery_rate,    '#14b8a6');
  beRenderGauge('be-gauge-opens',        data.open_rate,        '#f97316');
  beRenderGauge('be-gauge-clicks',       data.click_rate,       '#2563eb');
  beRenderGauge('be-gauge-conversions',  data.conversion_rate,  '#6366f1');
  beRenderGauge('be-gauge-unsub',        data.unsubscribe_rate, '#ef4444');
}

let beGaugeCharts = {};

function beRenderGauge(canvasId, rate, color) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;

  const pct = Math.max(0, Math.min(100, Number(rate) || 0));

  if (beGaugeCharts[canvasId]) {
    beGaugeCharts[canvasId].data.datasets[0].data = [pct, 100 - pct];
    beGaugeCharts[canvasId].update();
    return;
  }

  beGaugeCharts[canvasId] = new Chart(canvas, {
    type: 'doughnut',
    data: {
      datasets: [{
        data: [pct, 100 - pct],
        backgroundColor: [color, '#f1f5f9'],
        borderWidth: 0,
      }],
    },
    options: {
      cutout: '72%',
      plugins: { legend: { display: false }, tooltip: { enabled: false } },
      animation: { animateRotate: true, duration: 700 },
    },
  });
}

function openBrevoTopModal(metric) {
  openBrevoEngagementContactsModal(metric);
}

function beEscapeHtml(s) {
  return String(s || '').replace(/[&<>"']/g, function (c) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
  });
}

/* ── Delivered/Opens/Clicks/Unsubscribes — Email IDs (built from Brevo per-campaign exports) ── */
const BE_CONTACTS_METRIC_LABELS = {
  delivered:    'Delivered',
  opens:        'Opens',
  clicks:       'Clicks',
  unsubscribes: 'Unsubscribes',
};
const BE_CONTACTS_METRIC_STYLE = {
  delivered:    { color:'#3b82f6', bg:'#eff6ff' },
  opens:        { color:'#7c3aed', bg:'#f5f3ff' },
  clicks:       { color:'#db2777', bg:'#fdf2f8' },
  unsubscribes: { color:'#ef4444', bg:'#fef2f2' },
};

let beContactsCurrentMetric = null;
let beContactsPollTimer = null;
let beContactsPollAttempts = 0;
const BE_CONTACTS_MAX_POLL_ATTEMPTS = 150; // ~10 minutes at 4s intervals

function openBrevoEngagementContactsModal(metric, forceRefresh) {
  if (!BE_CONTACTS_METRIC_LABELS[metric]) return; // e.g. 'conversions' has no per-recipient data in Brevo

  clearTimeout(beContactsPollTimer);
  beContactsPollAttempts = 0;
  beContactsCurrentMetric = metric;

  const style = BE_CONTACTS_METRIC_STYLE[metric];
  document.getElementById('be-modal').classList.add('be-modal-wide');
  document.getElementById('be-modal-dot').style.background = style.color;
  document.getElementById('be-modal-title').textContent = BE_CONTACTS_METRIC_LABELS[metric] + ' — Email IDs';
  document.getElementById('be-modal-title').style.color = style.color;
  const badge = document.getElementById('be-modal-badge');
  badge.textContent = '';
  badge.style.background = style.bg;
  badge.style.color = style.color;

  const body = document.getElementById('be-modal-body');
  body.innerHTML = '<div style="padding:40px 20px;text-align:center;color:#9ca3af;font-size:12px;">Loading ' + BE_CONTACTS_METRIC_LABELS[metric].toLowerCase() + ' emails…</div>';
  document.getElementById('be-modal-overlay').style.display = 'flex';

  fetchBrevoContactsStatus(metric, !!forceRefresh);
}

function fetchBrevoContactsStatus(metric, refresh) {
  const body = document.getElementById('be-modal-body');
  const url = '{{ route('client.email-connections.brevo.engagement-contacts', ['metric' => '__METRIC__']) }}'
    .replace('__METRIC__', metric) + (refresh ? '?refresh=1' : '');

  fetch(url, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(res => {
      if (beContactsCurrentMetric !== metric) return; // modal moved on to a different metric meanwhile

      if (!res.connected || !res.success) {
        body.innerHTML = '<div style="padding:40px 20px;text-align:center;color:#ef4444;font-size:12px;">'
          + beEscapeHtml(res.message || 'Could not load emails.') + '</div>';
        return;
      }

      if (res.building) {
        renderContactsBuildingState(res.total || 0, res.done || 0);

        beContactsPollAttempts++;
        if (beContactsPollAttempts < BE_CONTACTS_MAX_POLL_ATTEMPTS) {
          beContactsPollTimer = setTimeout(function () { fetchBrevoContactsStatus(metric, false); }, 4000);
        } else {
          body.innerHTML += '<p style="text-align:center;color:#9ca3af;font-size:11px;margin-top:12px;">'
            + 'Still building — close this and check back later.</p>';
        }
        return;
      }

      renderContactsList(res.data || [], res.built_at, metric);
    })
    .catch(() => {
      body.innerHTML = '<div style="padding:40px 20px;text-align:center;color:#ef4444;font-size:12px;">Could not reach the server. Please try again.</div>';
    });
}

function renderContactsBuildingState(total, done) {
  const pct = total > 0 ? Math.round((done / total) * 100) : 0;
  document.getElementById('be-modal-body').innerHTML =
    '<div style="padding:40px 24px;text-align:center;">'
    + '<p style="font-size:12px;color:#374151;font-weight:600;margin-bottom:6px;">Building your email engagement lists from Brevo…</p>'
    + '<p style="font-size:11px;color:#9ca3af;margin-bottom:14px;">' + done + ' / ' + total + ' campaigns processed. This can take a while — feel free to close this and check back later.</p>'
    + '<div class="bar-wrap" style="height:6px;max-width:320px;margin:0 auto;"><div class="bar-fill" style="width:' + Math.max(3, pct) + '%;background:#3b82f6;height:6px;"></div></div>'
    + '</div>';
}

let beContactsAllEmails      = [];
let beContactsFilteredEmails = [];
let beContactsCurrentPage    = 1;
let beContactsBuiltAt        = null;
const BE_CONTACTS_PAGE_SIZE  = 100;

function renderContactsList(rows, builtAt, metric) {
  const label = BE_CONTACTS_METRIC_LABELS[metric];

  document.getElementById('be-modal-badge').textContent = beFormatNumber(rows.length) + ' emails';

  beContactsCurrentMetric  = metric;
  beContactsAllEmails      = rows;
  beContactsFilteredEmails = rows;
  beContactsCurrentPage    = 1;
  beContactsBuiltAt        = builtAt;

  const body = document.getElementById('be-modal-body');

  if (rows.length === 0) {
    body.innerHTML = '<div style="padding:40px 20px;text-align:center;color:#9ca3af;font-size:12px;">No '
      + label.toLowerCase() + ' emails found.</div>';
    return;
  }

  body.innerHTML =
      '<div style="padding:16px 20px 0;">'
    +   '<div style="position:relative;">'
    +     '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>'
    +     '<input type="text" id="be-contacts-search" placeholder="Search email…" oninput="beFilterContacts(this.value)" '
    +       'style="width:100%;font-size:12px;padding:8px 10px 8px 32px;border:1px solid #e5e7eb;border-radius:8px;outline:none;box-sizing:border-box;">'
    +   '</div>'
    + '</div>'
    + '<div id="be-contacts-rows" style="padding:12px 20px 4px;display:flex;flex-direction:column;gap:8px;"></div>'
    + '<div id="be-contacts-pagination" style="display:flex;align-items:center;justify-content:center;gap:6px;padding:8px 20px;"></div>'
    + '<div style="padding:10px 20px;border-top:1px solid #f3f4f6;">'
    +   '<span style="font-size:10px;color:#9ca3af;">' + (builtAt ? 'Built ' + new Date(builtAt).toLocaleString() : '') + '</span>'
    + '</div>';

  beRenderContactsPage();
}

function beFilterContacts(term) {
  const q = term.trim().toLowerCase();
  beContactsFilteredEmails = q
    ? beContactsAllEmails.filter(function (r) {
        return r.email.toLowerCase().includes(q) || String(r.campaign_id).includes(q);
      })
    : beContactsAllEmails;
  beContactsCurrentPage = 1;
  beRenderContactsPage();
}

function beRenderContactsPage() {
  const metric = beContactsCurrentMetric;
  const style  = BE_CONTACTS_METRIC_STYLE[metric];
  const label  = BE_CONTACTS_METRIC_LABELS[metric];

  const rowsEl  = document.getElementById('be-contacts-rows');
  const pagerEl = document.getElementById('be-contacts-pagination');
  if (!rowsEl || !pagerEl) return;

  const total      = beContactsFilteredEmails.length;
  const totalPages = Math.max(1, Math.ceil(total / BE_CONTACTS_PAGE_SIZE));
  if (beContactsCurrentPage > totalPages) beContactsCurrentPage = totalPages;

  if (total === 0) {
    rowsEl.innerHTML  = '<div style="text-align:center;padding:24px 0;color:#9ca3af;font-size:12px;">No emails match your search.</div>';
    pagerEl.innerHTML = '';
    return;
  }

  const start    = (beContactsCurrentPage - 1) * BE_CONTACTS_PAGE_SIZE;
  const pageRows = beContactsFilteredEmails.slice(start, start + BE_CONTACTS_PAGE_SIZE);

  const mailIcon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="' + style.color + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
    + '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>'
    + '</svg>';

  rowsEl.innerHTML = pageRows.map(function (row) {
    const safeEmail    = beEscapeHtml(row.email);
    const safeCampaign = beEscapeHtml(String(row.campaign_id));
    return '<div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;">'
      + '<div style="width:28px;height:28px;border-radius:50%;background:' + style.bg + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">' + mailIcon + '</div>'
      + '<span style="font-size:12px;color:#111827;font-weight:500;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + safeEmail + '</span>'
      + '<span style="font-size:10px;font-weight:600;color:#6b7280;background:#f3f4f6;border-radius:4px;padding:2px 7px;flex-shrink:0;" title="Campaign ID">#' + safeCampaign + '</span>'
      + '<span style="font-size:10px;font-weight:600;color:' + style.color + ';background:' + style.bg + ';border-radius:4px;padding:2px 7px;flex-shrink:0;">' + label + '</span>'
      + '</div>';
  }).join('');

  const prevDisabled = beContactsCurrentPage <= 1;
  const nextDisabled = beContactsCurrentPage >= totalPages;

  pagerEl.innerHTML =
      '<button type="button" onclick="beGoToContactsPage(' + (beContactsCurrentPage - 1) + ')" ' + (prevDisabled ? 'disabled' : '')
    +   ' style="font-size:11px;font-weight:600;color:#374151;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:5px 10px;cursor:pointer;' + (prevDisabled ? 'opacity:.4;cursor:default;' : '') + '">Prev</button>'
    + '<span style="font-size:11px;color:#6b7280;margin:0 8px;">Page ' + beContactsCurrentPage + ' of ' + totalPages + '</span>'
    + '<button type="button" onclick="beGoToContactsPage(' + (beContactsCurrentPage + 1) + ')" ' + (nextDisabled ? 'disabled' : '')
    +   ' style="font-size:11px;font-weight:600;color:#374151;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:5px 10px;cursor:pointer;' + (nextDisabled ? 'opacity:.4;cursor:default;' : '') + '">Next</button>';
}

function beGoToContactsPage(page) {
  const totalPages = Math.max(1, Math.ceil(beContactsFilteredEmails.length / BE_CONTACTS_PAGE_SIZE));
  if (page < 1 || page > totalPages) return;
  beContactsCurrentPage = page;
  beRenderContactsPage();
}

function closeBrevoTopModal() {
  clearTimeout(beContactsPollTimer);
  beContactsCurrentMetric = null;
  document.getElementById('be-modal-overlay').style.display = 'none';
}

function selectTab(src, tabId) {
  // hide all panels for this source
  document.querySelectorAll('[id^="panel-' + src + '-"]').forEach(p => p.classList.remove('active'));
  document.getElementById('panel-' + src + '-' + tabId).classList.add('active');
  currentTab = tabId;

  // update tab button highlight
  document.querySelectorAll('#tabs-' + src + ' .tab-btn').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
}
</script>

<script>
// ── Call Center Charts ─────────────────────────────────────────────────────
(function() {
  var dailyVolume = @json($callDailyVolume);
  var labels   = dailyVolume.map(function(d){ return d.date; });
  var inbound  = dailyVolume.map(function(d){ return d.inbound; });
  var outbound = dailyVolume.map(function(d){ return d.outbound; });

  var callCtx = document.getElementById('callVolumeChart');
  if (!callCtx) return;
  new Chart(callCtx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Inbound',
          data: inbound,
          backgroundColor: '#6366f1',
          borderRadius: 4,
          borderSkipped: false,
        },
        {
          label: 'Outbound',
          data: outbound,
          backgroundColor: '#8b5cf6',
          borderRadius: 4,
          borderSkipped: false,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { mode: 'index', intersect: false }
      },
      scales: {
        x: {
          stacked: false,
          grid: { display: false },
          ticks: { font: { size: 10 }, color: '#9ca3af', maxRotation: 45 }
        },
        y: {
          beginAtZero: true,
          grid: { color: '#f3f4f6' },
          ticks: { font: { size: 11 }, color: '#9ca3af', stepSize: 1, precision: 0 }
        }
      },
      animation: { duration: 800, easing: 'easeOutQuart' }
    }
  });
})();

// ── Unsubscribe Charts ──────────────────────────────────────────────────────
(function() {
  var retained     = {{ $retained }};
  var unsubscribed = {{ $unsubscribed }};
  var sent         = {{ $sent }};

  // Bar chart
  var barCtx = document.getElementById('unsubBarChart');
  if (barCtx) {
    new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: ['Total Sent', 'Retained', 'Unsubscribed'],
        datasets: [{
          data: [sent, retained, unsubscribed],
          backgroundColor: ['#3b82f6', '#10b981', '#ef4444'],
          borderRadius: 8,
          borderSkipped: false,
          barThickness: 52,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(ctx) {
                var pct = sent > 0 ? Math.round(ctx.parsed.y / sent * 100) : 0;
                return ' ' + ctx.parsed.y + ' emails (' + pct + '%)';
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: Math.ceil(sent * 1.2),
            grid: { color: '#f3f4f6' },
            ticks: { font: { size: 11 }, color: '#9ca3af', stepSize: Math.ceil(sent / 5) }
          },
          x: {
            grid: { display: false },
            ticks: { font: { size: 12, weight: '600' }, color: '#374151' }
          }
        },
        animation: { duration: 900, easing: 'easeOutQuart' }
      }
    });
  }

  // Donut chart
  var donutCtx = document.getElementById('unsubDonutChart');
  if (donutCtx) {
    new Chart(donutCtx, {
      type: 'doughnut',
      data: {
        labels: ['Retained', 'Unsubscribed'],
        datasets: [{
          data: [retained, unsubscribed],
          backgroundColor: ['#10b981', '#ef4444'],
          borderWidth: 0,
          hoverOffset: 8,
        }]
      },
      options: {
        cutout: '70%',
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(ctx) {
                var total = ctx.dataset.data.reduce(function(a,b){return a+b;},0);
                var pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
              }
            }
          }
        },
        animation: { animateRotate: true, duration: 900 }
      }
    });
  }
})();

document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush

