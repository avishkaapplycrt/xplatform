@extends('layouts.platform')
@section('title', 'Decision Centre')

@push('styles')
<style>
/* ── Decision Centre — full light mode ─────────────────────────────────────── */
.dc-wrap{display:flex;overflow:hidden;background:#f3f4f6;font-family:'DM Mono',monospace,ui-monospace,Menlo,Monaco,'Courier New';font-size:11px}
.dc-wrap *{box-sizing:border-box;margin:0;padding:0}

/* ── LEFT PANEL ── */
.dc-lp{width:240px;border-right:1px solid #e5e7eb;background:#fff;display:flex;flex-direction:column;flex-shrink:0}
.dc-lp-seg{padding:10px 14px;border-bottom:1px solid #f3f4f6;flex-shrink:0}
.dc-lp-seg-label{font-size:9px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.1em}
.dc-list{flex:1;overflow-y:auto;padding:8px}
.dc-list::-webkit-scrollbar{width:2px}
.dc-sc{cursor:pointer;padding:11px 12px;border-radius:10px;border:1px solid #e5e7eb;margin-bottom:6px;background:#fff;transition:all .14s;box-shadow:0 1px 2px rgba(0,0,0,.04)}
.dc-sc:hover{border-color:#d1d5db;box-shadow:0 2px 6px rgba(0,0,0,.06);background:#fafafa}
.dc-sc.on{border-left:3px solid var(--sc-lc,#10b981);background:var(--sc-lc-bg,#f0fdf4);border-color:var(--sc-lc,#10b981)}
.dc-sc-row{display:flex;align-items:flex-start;gap:8px;margin-bottom:5px}
.dc-sc-name{font-size:11px;font-weight:600;line-height:1.4;flex:1;color:#111827}
.dc-sc.on .dc-sc-name{color:var(--sc-lc,#10b981)}
.dc-sc-rev{font-size:10px;font-weight:700;color:var(--sc-lc,#10b981);white-space:nowrap}
.dc-sc-foot{display:flex;align-items:center;justify-content:space-between;gap:6px}
.dc-sc-urg{font-size:9px;color:#6b7280;display:flex;align-items:center;gap:4px}
.dc-udot{width:5px;height:5px;border-radius:50%;display:inline-block;flex-shrink:0}
.dc-sc-users{font-size:8.5px;color:#9ca3af;white-space:nowrap}

/* ── CENTER PANEL ── */
.dc-cp{width:310px;border-right:1px solid #e5e7eb;display:flex;flex-direction:column;flex-shrink:0;background:#fff}
.dc-cp-hd{padding:14px 16px;border-bottom:1px solid #f3f4f6;background:#fff;flex-shrink:0}
.dc-cp-title{font-size:15px;font-weight:700;margin-bottom:2px;color:#111827;line-height:1.3}
.dc-cp-sub{font-size:9.5px;color:#6b7280}
.dc-cp-banner{margin-top:10px;padding:10px 12px;border-radius:8px;font-size:10.5px;line-height:1.65;border:1px solid;font-weight:500}
.dc-cp-body{flex:1;overflow-y:auto;padding:14px 16px;background:#f9fafb}
.dc-cp-body::-webkit-scrollbar{width:2px}

/* section header */
.dc-sec{font-size:9px;color:#9ca3af;text-transform:uppercase;letter-spacing:.1em;display:flex;align-items:center;gap:8px;margin:14px 0 8px}
.dc-sec:first-child{margin-top:0}
.dc-sl{flex:1;height:1px;background:#e5e7eb}

/* KPI grid */
.dc-kgrid{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:10px}
.dc-kpi{padding:10px 11px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,.04)}
.dc-kpi-v{font-size:16px;font-weight:800;line-height:1;margin-bottom:3px}
.dc-kpi-l{font-size:8.5px;color:#6b7280}
.dc-kbar{height:3px;background:#f3f4f6;border-radius:2px;overflow:hidden;margin-top:5px}
.dc-kfill{height:100%;border-radius:2px;transition:width 1s ease}

/* Decision grid */
.dc-dgrid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px}
.dc-dm{padding:10px 12px;border-radius:8px;border:1px solid}
.dc-dm-l{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px}
.dc-dm-v{font-size:20px;font-weight:800;line-height:1;margin-bottom:3px}
.dc-dm-s{font-size:9px;color:#6b7280;line-height:1.5}

/* Outcome box */
.dc-obox{border-radius:8px;border:1px solid #e5e7eb;background:#fff;padding:10px 13px;margin-bottom:10px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
.dc-or{display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid #f3f4f6;font-size:10px;color:#374151}
.dc-or:last-child{border-bottom:none}
.dc-ov{font-weight:700;color:#111827}

/* CTAs */
.dc-cta{width:100%;padding:12px 14px;border-radius:10px;cursor:pointer;display:flex;align-items:center;gap:10px;margin-bottom:8px;transition:all .14s;text-align:left;border:1px solid;font-family:inherit;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.dc-cta:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.08)}
.dc-cta-ico{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;background:#f9fafb}
.dc-cta-txt{flex:1}
.dc-cta-action{font-size:11.5px;font-weight:700;margin-bottom:2px;line-height:1.3}
.dc-cta-detail{font-size:9px;color:#6b7280;line-height:1.5}
.dc-cta-badge{padding:3px 9px;border-radius:6px;font-size:8.5px;font-weight:700;background:#f3f4f6;border:1px solid #e5e7eb;white-space:nowrap;flex-shrink:0;align-self:flex-start;color:#374151}

/* ── RIGHT PANEL ── */
.dc-rp{flex:1;display:flex;flex-direction:column;overflow:hidden;background:#f9fafb}
.dc-rp-hd{padding:12px 16px;border-bottom:1px solid #e5e7eb;background:#fff;flex-shrink:0}
.dc-rp-title{font-size:13px;font-weight:700;margin-bottom:2px;color:#111827}
.dc-rp-sub{font-size:9px;color:#6b7280}
.dc-filters{display:flex;gap:5px;padding:8px 14px;border-bottom:1px solid #e5e7eb;flex-shrink:0;flex-wrap:wrap;background:#fff}
.dc-flt{padding:4px 10px;border-radius:20px;font-size:8.5px;font-weight:600;cursor:pointer;border:1px solid #e5e7eb;color:#6b7280;transition:all .12s;font-family:inherit;background:#fff}
.dc-flt.on{background:#f0fdf4;color:#059669;border-color:#a7f3d0}
.dc-rp-list{flex:1;overflow-y:auto;padding:12px 14px;background:#f9fafb}
.dc-rp-list::-webkit-scrollbar{width:2px}

/* ── USER CARD ── */
.dc-uc{border:1px solid #e5e7eb;border-radius:10px;margin-bottom:8px;overflow:hidden;background:#fff;cursor:pointer;transition:all .14s;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.dc-uc:hover{border-color:#d1d5db;box-shadow:0 3px 10px rgba(0,0,0,.08)}
.dc-uc.open{border-color:#a7f3d0;box-shadow:0 2px 8px rgba(16,185,129,.1)}
.dc-uc-top{display:flex;align-items:center;gap:10px;padding:11px 14px}
.dc-uc-av{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;color:#fff}
.dc-uc-info{flex:1;min-width:0}
.dc-uc-name{font-size:12px;font-weight:700;margin-bottom:2px;color:#111827}
.dc-uc-company{font-size:10px;color:#6b7280}
.dc-uc-summary{font-size:9px;color:#9ca3af;margin-top:2px}
.dc-uc-right{display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0}
.dc-tier{padding:3px 9px;border-radius:20px;font-size:9px;font-weight:600;border:1px solid}
.dc-amount{font-size:13px;font-weight:700}
.dc-tags{display:flex;gap:3px;flex-wrap:wrap;justify-content:flex-end;margin-top:2px}
.dc-tag{padding:2px 7px;border-radius:20px;font-size:8px;font-weight:600;border:1px solid;background:#f9fafb;color:#374151;border-color:#e5e7eb}

/* ── USER DETAIL ── */
.dc-detail{display:none;padding:0 14px 14px;border-top:1px solid #f3f4f6}
.dc-uc.open .dc-detail{display:block}
.dc-ud-grid{display:grid;grid-template-columns:1fr 1fr;gap:5px;margin:10px 0}
.dc-ud-item{padding:8px 10px;background:#f9fafb;border-radius:7px;border:1px solid #f3f4f6}
.dc-ud-l{font-size:8px;color:#9ca3af;margin-bottom:3px;text-transform:uppercase;letter-spacing:.07em}
.dc-ud-v{font-size:11px;font-weight:600;line-height:1.3;color:#111827}
.dc-tl-hd{font-size:8.5px;color:#9ca3af;text-transform:uppercase;letter-spacing:.09em;margin-bottom:8px;margin-top:4px;font-weight:600}
.dc-tl-row{display:flex;gap:8px;margin-bottom:7px;align-items:flex-start}
.dc-tl-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;margin-top:3px}
.dc-tl-txt{font-size:10px;color:#374151;flex:1;line-height:1.5}
.dc-tl-time{font-size:8.5px;color:#9ca3af;white-space:nowrap;flex-shrink:0;margin-left:6px}
.dc-action-row{display:flex;gap:6px;margin-top:10px}
.dc-ab{flex:1;padding:8px 10px;border-radius:8px;font-size:10px;font-weight:600;cursor:pointer;border:1px solid;text-align:center;transition:all .12s;font-family:inherit;background:#fff}
.dc-ab:hover{transform:translateY(-1px);box-shadow:0 2px 6px rgba(0,0,0,.08)}

/* ── EMPTY STATE ── */
.dc-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:#9ca3af;text-align:center;padding:24px}
.dc-empty-ico{font-size:40px;margin-bottom:14px;opacity:.25}

/* ── TOAST ── */
.dc-toast{position:fixed;bottom:20px;right:20px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:10px 14px;display:none;align-items:center;gap:10px;z-index:9999;max-width:300px;box-shadow:0 4px 16px rgba(0,0,0,.1)}
.dc-toast.show{display:flex}
.dc-t-ico{font-size:18px}
.dc-t-title{font-weight:700;color:#059669;margin-bottom:1px;font-size:10px}
.dc-t-body{color:#6b7280;font-size:9.5px}

@keyframes dc-fi{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
.dc-anim{animation:dc-fi .2s ease both}
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $cn), 0, 2))));
  $layerTitles = ['l4' => 'Behavioral Intelligence', 'l5' => 'AI/ML Predictions'];
  $headerTitle = $layerTitles[$activeLayer] ?? 'Decision Centre';
@endphp

{{-- Header --}}
<header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
  <div>
    <h1 class="text-[16px] font-semibold text-gray-900">{{ $headerTitle }}</h1>

    <div class="flex items-center gap-3 mt-1">
        @if($dataSourceConnected ?? false)
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Live EdTech Data
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                Demo Data
            </span>
            <a href="{{ route('client.sources.create') }}" class="text-[10px] text-indigo-600 hover:underline font-medium">
                Connect Database →
            </a>
        @endif
    </div>

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
    <div style="position:relative" id="dcAvatarWrap">
      <button onclick="var d=document.getElementById('dcDropdown');d.style.display=d.style.display==='block'?'none':'block'"
              style="width:32px;height:32px;border-radius:50%;background:#06b6d4;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;border:none;cursor:pointer">
        {{ $av }}
      </button>
      <div id="dcDropdown"
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

<div class="dc-wrap" style="height:calc(100% - 57px)">

  {{-- ── LEFT PANEL ───────────────────────────────────────────────────────────── --}}
  <div class="dc-lp">
    <div class="dc-lp-seg">
      <div class="dc-lp-seg-label" id="dc-lp-label"></div>
    </div>
    <div class="dc-list" id="dc-llist"></div>
  </div>

  {{-- ── CENTER PANEL ──────────────────────────────────────────────────────────── --}}
  <div class="dc-cp">
    <div class="dc-cp-hd" id="dc-cp-hd">
      <div class="dc-cp-title" style="color:#6b7280">Select a scenario</div>
      <div class="dc-cp-sub">Choose a segment from the left panel</div>
    </div>
    <div class="dc-cp-body" id="dc-cp-body">
      <div class="dc-empty">
        <div class="dc-empty-ico">🎯</div>
        <div style="font-size:11px;line-height:1.8">Select a segment on the left<br>to see the AI decision brief<br>and action plan</div>
      </div>
    </div>
  </div>

  {{-- ── RIGHT PANEL ───────────────────────────────────────────────────────────── --}}
  <div class="dc-rp">
    <div class="dc-rp-hd">
      <div class="dc-rp-title" id="dc-rp-title">User Details</div>
      <div class="dc-rp-sub"   id="dc-rp-sub">Click any action button to load affected users</div>
    </div>
    <div class="dc-filters" id="dc-filters" style="display:none"></div>
    <div class="dc-rp-list" id="dc-rp-list">
      <div class="dc-empty">
        <div class="dc-empty-ico">👥</div>
        <div style="font-size:11px;line-height:1.8">Click any action button<br>to see the affected users<br>with full details</div>
      </div>
    </div>
  </div>

</div>

{{-- ── TOAST ─────────────────────────────────────────────────────────────────── --}}
<div class="dc-toast" id="dc-toast">
  <span class="dc-t-ico" id="dc-t-ico">✅</span>
  <div>
    <div class="dc-t-title" id="dc-t-title"></div>
    <div class="dc-t-body"  id="dc-t-body"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// ── PHP → JS data ────────────────────────────────────────────────────────────
const DC_USERS     = @json($userGroups);
const DC_SCENARIOS = @json($scenarios);
const DC_ACTIVE    = @json($activeLayer);

const DC_LABELS = {
  l4: 'L4 — Behavioural Segments',
  l5: 'L5 — Decision Scenarios',
};

// ── State ────────────────────────────────────────────────────────────────────
let dcScen    = null;
let dcUsers   = [];
let dcToastTm = null;

// ── Render scenario cards in left panel ──────────────────────────────────────
function dcRenderScenarios() {
  document.getElementById('dc-lp-label').textContent = DC_LABELS[DC_ACTIVE] || '';
  const list = document.getElementById('dc-llist');
  const scenarios = DC_SCENARIOS[DC_ACTIVE] || [];
  list.innerHTML = scenarios.map(s => `
    <div id="dc-sc-${s.id}" class="dc-sc" style="--sc-lc:${s.lc};--sc-lc-bg:${s.lc}0D"
         onclick="dcSelectScen('${s.id}')">
      <div class="dc-sc-row">
        <span style="font-size:15px;flex-shrink:0">${s.ico}</span>
        <span class="dc-sc-name">${s.name}</span>
        <span class="dc-sc-rev">${s.rev}</span>
      </div>
      <div class="dc-sc-foot">
        <span class="dc-sc-urg">
          <span class="dc-udot" style="background:${s.urgDot}"></span>
          ${s.ul}
        </span>
        <span class="dc-sc-users">${s.users}</span>
      </div>
    </div>`).join('');
}

// ── Select scenario → render center panel, reset right panel ─────────────────
function dcSelectScen(scenId) {
  const scenarios = DC_SCENARIOS[DC_ACTIVE] || [];
  const s = scenarios.find(x => x.id === scenId);
  if (!s) {
      console.error('Scenario not found:', scenId, 'in layer', DC_ACTIVE);
      return;
  }
  // const s = (DC_SCENARIOS[DC_ACTIVE] || []).find(x => x.id === scenId);
  // if (!s) return;

  // Only reset right panel if a different scenario is selected
  if (!dcScen || dcScen.id !== scenId) {
    dcUsers = [];
    document.getElementById('dc-rp-title').textContent = 'User Details';
    document.getElementById('dc-rp-sub').textContent   = 'Click any action button to load affected users';
    document.getElementById('dc-filters').style.display = 'none';
    document.getElementById('dc-rp-list').innerHTML =
      '<div class="dc-empty"><div class="dc-empty-ico">👥</div>' +
      '<div style="font-size:11px;line-height:1.8">Click any action button<br>to see the affected users<br>with full details</div></div>';
  }

  dcScen = s;

  document.querySelectorAll('.dc-sc').forEach(el => el.classList.remove('on'));
  const card = document.getElementById('dc-sc-' + scenId);
  if (card) card.classList.add('on');

  document.getElementById('dc-cp-hd').innerHTML = `
    <div class="dc-cp-title">${s.ico} ${s.name}</div>
    <div class="dc-cp-sub" style="margin-top:2px">${s.users} · ${s.rev}</div>
    <div class="dc-cp-banner"
         style="color:${s.banner.c};border-color:${s.banner.c}33;background:${s.banner.c}0A;margin-top:10px">
      ${s.banner.text}
    </div>`;

  document.getElementById('dc-cp-body').innerHTML = dcCenterBody(s);
}

function dcCenterBody(s) {
  if (!s || !Array.isArray(s.kpis) || !Array.isArray(s.dec)) {
      console.error('Invalid scenario data:', s);
      return '<div class="dc-empty"><div class="dc-empty-ico">⚠️</div><div style="font-size:11px">Error loading scenario data</div></div>';
  }

  const kpis = `
    <div class="dc-sec"><span>Key Signals</span><div class="dc-sl"></div></div>
    <div class="dc-kgrid">
      ${s.kpis.map(k => `
        <div class="dc-kpi">
          <div class="dc-kpi-v" style="color:${k.c}">${k.v}</div>
          <div class="dc-kpi-l">${k.l}</div>
          <div class="dc-kbar"><div class="dc-kfill" style="width:${k.p}%;background:${k.c}"></div></div>
        </div>`).join('')}
    </div>`;

  const dec = `
    <div class="dc-sec"><span>Act Now vs Wait</span><div class="dc-sl"></div></div>
    <div class="dc-dgrid">
      ${s.dec.map(d => `
        <div class="dc-dm" style="border-color:${d.g?'#10B98133':'#F43F5E33'};background:${d.g?'#f0fdf4':'#fff5f5'}">
          <div class="dc-dm-l" style="color:${d.g?'#059669':'#e11d48'}">${d.l}</div>
          <div class="dc-dm-v" style="color:${d.g?'#059669':'#e11d48'}">${d.v}</div>
          <div class="dc-dm-s">${d.s}</div>
        </div>`).join('')}
    </div>`;

  const out = `
    <div class="dc-sec"><span>Expected Outcome</span><div class="dc-sl"></div></div>
    <div class="dc-obox">
      ${s.out.map(o => `
        <div class="dc-or">
          <span>${o.l}</span>
          <span class="dc-ov">${o.v}</span>
        </div>`).join('')}
    </div>`;

  const ctas = `
    <div class="dc-sec"><span>Take Action</span><div class="dc-sl"></div></div>
    ${s.ctas.map(c => {
      const vfJson = JSON.stringify(c.vf).replace(/"/g, '&quot;');
      const vt = c.vt.replace(/'/g, "\\'");
      const vs = c.vs.replace(/'/g, "\\'");
      return `
      <button class="dc-cta" style="border-color:${c.c}33"
              onclick="dcLoadUsers('${c.view}','${vt}','${vs}','${vfJson}')">
        <div class="dc-cta-ico" style="background:${c.c}12">${c.ico}</div>
        <div class="dc-cta-txt">
          <div class="dc-cta-action" style="color:${c.c}">${c.l}</div>
          <div class="dc-cta-detail">${c.d}</div>
        </div>
        <div class="dc-cta-badge" style="color:${c.c};border-color:${c.c}33;background:${c.c}0A">${c.b}</div>
      </button>`;
    }).join('')}`;

  return kpis + dec + out + ctas;
}

// ── Load users into right panel ───────────────────────────────────────────────
function dcLoadUsers(viewKey, title, sub, vfJson) {
  const filters = typeof vfJson === 'string' ? JSON.parse(vfJson.replace(/&quot;/g, '"')) : vfJson;
  const users = DC_USERS[viewKey] || [];
  dcUsers = users;

  document.getElementById('dc-rp-title').textContent = title;
  document.getElementById('dc-rp-sub').textContent   = users.length + ' users · ' + sub;

  const filtersEl = document.getElementById('dc-filters');
  filtersEl.style.display = 'flex';
  filtersEl.innerHTML = filters.map(f =>
    `<button class="dc-flt ${f==='All'?'on':''}"
             onclick="dcApplyFilter('${f.replace(/'/g,"\\'")}')">
       ${f}
     </button>`).join('');

  dcRenderList(users);
  dcToast('✅', 'Loaded', users.length + ' users loaded');
}

// ── Filter ────────────────────────────────────────────────────────────────────
function dcApplyFilter(filter) {
  document.querySelectorAll('.dc-flt').forEach(f => {
    f.classList.toggle('on', f.textContent.trim() === filter);
  });
  const filtered = filter === 'All'
    ? dcUsers
    : dcUsers.filter(u => (u.tags || []).includes(filter));
  dcRenderList(filtered);
}

// ── Render user list ──────────────────────────────────────────────────────────
function dcRenderList(users) {
  const list = document.getElementById('dc-rp-list');
  if (!users.length) {
    list.innerHTML = '<div class="dc-empty"><div class="dc-empty-ico">👤</div>' +
      '<div style="font-size:11px">No users match this filter</div></div>';
    return;
  }
  list.innerHTML = users.map(u => dcUserCard(u)).join('');
}

// ── User card ─────────────────────────────────────────────────────────────────
function dcUserCard(u) {
  const tags = (u.tags || []).map(t =>
    `<span class="dc-tag" style="color:${u.tierColor};border-color:${u.tierColor}33;background:${u.tierColor}0A">${t}</span>`
  ).join('');

  const fields = (u.fields || []).map(f =>
    `<div class="dc-ud-item">
       <div class="dc-ud-l">${f[0]}</div>
       <div class="dc-ud-v">${f[1]}</div>
     </div>`
  ).join('');

  const tl = (u.timeline || []).map(t =>
    `<div class="dc-tl-row">
       <div class="dc-tl-dot" style="background:${t.c}"></div>
       <span class="dc-tl-txt">${t.e}</span>
       <span class="dc-tl-time">${t.t}</span>
     </div>`
  ).join('');

  const actions = (u.actions || []).map(a => {
    const label = a.l.replace(/'/g, "\\'");
    const name  = (u.name || '').replace(/'/g, "\\'");
    return `<button class="dc-ab" style="color:${a.c};border-color:${a.c}33;background:${a.c}0A"
               onclick="event.stopPropagation();dcToast('✅','Done','${label} — queued for ${name}')">
             ${a.l}
           </button>`;
  }).join('');

  return `
    <div class="dc-uc dc-anim" onclick="this.classList.toggle('open')">
      <div class="dc-uc-top">
        <div class="dc-uc-av" style="background:${u.avatarColor}">${u.initials}</div>
        <div class="dc-uc-info">
          <div class="dc-uc-name">${u.name}</div>
          <div class="dc-uc-company">${u.company} · ${u.industry}</div>
          <div class="dc-uc-summary">${u.tier} · ${u.amountLabel}</div>
        </div>
        <div class="dc-uc-right">
          <span class="dc-tier" style="color:${u.tierColor};border-color:${u.tierColor}33;background:${u.tierColor}0A">${u.tier}</span>
          <div class="dc-amount" style="color:${u.tierColor}">${u.amountLabel}</div>
          <div class="dc-tags">${tags}</div>
        </div>
      </div>
      <div class="dc-detail">
        <div class="dc-ud-grid">${fields}</div>
        <div class="dc-tl-hd">Timeline</div>
        ${tl}
        <div class="dc-action-row">${actions}</div>
      </div>
    </div>`;
}

// ── Toast ─────────────────────────────────────────────────────────────────────
function dcToast(ico, title, body) {
  const el = document.getElementById('dc-toast');
  document.getElementById('dc-t-ico').textContent   = ico;
  document.getElementById('dc-t-title').textContent = title;
  document.getElementById('dc-t-body').textContent  = body;
  el.classList.add('show');
  clearTimeout(dcToastTm);
  dcToastTm = setTimeout(() => el.classList.remove('show'), 3000);
}

// ── Init ─────────────────────────────────────────────────────────────────────
// document.addEventListener('DOMContentLoaded', dcRenderScenarios);
// ── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    dcRenderScenarios();

    // Auto-select first scenario after ensuring DOM is ready
    setTimeout(function() {
        const scenarios = DC_SCENARIOS[DC_ACTIVE] || [];
        if (scenarios.length > 0) {
            dcSelectScen(scenarios[0].id);
        }
    }, 50);
});

document.addEventListener('click', function(e) {
  var wrap = document.getElementById('dcAvatarWrap');
  var drop = document.getElementById('dcDropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush
