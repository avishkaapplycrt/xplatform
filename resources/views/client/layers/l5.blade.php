@extends('layouts.platform')
@section('title', 'AI/ML Predictions — L5 Decision Scenarios')

@push('styles')
<style>
  /* ── Root layout fix for scrolling ── */
  .l5-root {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
    background: #f9fafb;
  }

  .l5-main-scroll {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 16px 20px;
  }

  /* ── Top bar ── */
  .l5-topbar {
    flex-shrink: 0;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 10;
  }

  .l5-topbar-title { font-size: 16px; font-weight: 600; color: #111827; }
  .l5-topbar-sub { font-size: 11px; color: #6b7280; margin-top: 2px; }
  .l5-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 999px; }
  .l5-pill.live { background: #dcfce7; color: #166534; }
  .l5-pill.demo { background: #fef3c7; color: #92400e; }

  /* ── layout ── */
  .l5-wrap { display: flex; gap: 16px; min-height: 0; }
  .l5-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 14px; }
  .l5-side { width: 360px; flex-shrink: 0; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; overflow-y: auto; max-height: calc(100vh - 140px); }

  /* ── scenario cards ── */
  .l5-scenario { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px; cursor: pointer; transition: box-shadow .15s; }
  .l5-scenario:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }
  .l5-scenario.active { border-color: currentColor; box-shadow: 0 0 0 2px currentColor; }
  .l5-sc-hd { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
  .l5-sc-ico { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
  .l5-sc-name { font-size: 13px; font-weight: 600; color: #111827; line-height: 1.3; }
  .l5-sc-meta { font-size: 11px; color: #6b7280; margin-top: 2px; }
  .l5-sc-rev { font-size: 12px; font-weight: 700; margin-top: 6px; }
  .l5-sc-urg { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; margin-top: 8px; }
  .l5-sc-urg::before { content: ''; width: 6px; height: 6px; border-radius: 50%; display: inline-block; }

  /* ── banner ── */
  .l5-banner { border-radius: 8px; padding: 10px 14px; font-size: 11px; line-height: 1.5; margin-top: 12px; }

  /* ── KPI grid ── */
  .l5-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 14px; }
  .l5-kpi { background: #f9fafb; border-radius: 8px; padding: 12px; }
  .l5-kpi-val { font-size: 18px; font-weight: 700; line-height: 1; }
  .l5-kpi-lbl { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-top: 6px; }
  .l5-kpi-bar { height: 3px; border-radius: 2px; margin-top: 8px; overflow: hidden; background: #e5e7eb; }
  .l5-kpi-bar > div { height: 100%; border-radius: 2px; }

  /* ── decision table ── */
  .l5-dec { margin-top: 14px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
  .l5-dec-row { display: flex; align-items: center; padding: 10px 14px; font-size: 12px; border-bottom: 1px solid #f3f4f6; }
  .l5-dec-row:last-child { border-bottom: none; }
  .l5-dec-row.good { background: #f0fdf4; }
  .l5-dec-row.bad { background: #fef2f2; }
  .l5-dec-lbl { flex: 1; font-weight: 500; color: #374151; }
  .l5-dec-val { font-weight: 700; margin-right: 12px; }
  .l5-dec-sub { font-size: 10px; color: #6b7280; }

  /* ── outcome ── */
  .l5-out { margin-top: 14px; }
  .l5-out-hd { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-bottom: 8px; }
  .l5-out-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 12px; border-bottom: 1px solid #f3f4f6; }
  .l5-out-row:last-child { border-bottom: none; }
  .l5-out-lbl { color: #6b7280; }
  .l5-out-val { font-weight: 600; color: #111827; }

  /* ── CTAs ── */
  .l5-cta-wrap { margin-top: 14px; display: flex; flex-direction: column; gap: 8px; }
  .l5-cta { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; border: 1px solid #e5e7eb; cursor: pointer; transition: all .15s; background: #fff; }
  .l5-cta:hover { border-color: currentColor; transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,.06); }
  .l5-cta-ico { font-size: 14px; }
  .l5-cta-body { flex: 1; min-width: 0; }
  .l5-cta-tit { font-size: 12px; font-weight: 600; color: #111827; }
  .l5-cta-desc { font-size: 10px; color: #6b7280; margin-top: 1px; line-height: 1.4; }
  .l5-cta-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; white-space: nowrap; }

  /* ── side panel ── */
  .l5-side-hd { font-size: 13px; font-weight: 600; color: #111827; margin-bottom: 4px; }
  .l5-side-sub { font-size: 11px; color: #9ca3af; margin-bottom: 14px; }
  .l5-user-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #9ca3af; text-align: center; }
  .l5-user-empty svg { width: 48px; height: 48px; margin-bottom: 10px; opacity: .4; }

  /* ── user cards ── */
  .l5-user-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 8px; cursor: pointer; transition: box-shadow .15s; }
  .l5-user-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.06); }
  .l5-user-hd { display: flex; align-items: center; gap: 10px; }
  .l5-user-avatar { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
  .l5-user-info { flex: 1; min-width: 0; }
  .l5-user-name { font-size: 12px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .l5-user-co { font-size: 10px; color: #6b7280; }
  .l5-user-amt { font-size: 12px; font-weight: 700; }
  .l5-user-tags { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px; }
  .l5-user-tag { font-size: 9px; font-weight: 600; padding: 2px 6px; border-radius: 4px; }

  /* ── connection toggle ── */
  .l5-toggle { display: inline-flex; align-items: center; gap: 6px; background: #f3f4f6; border-radius: 6px; padding: 3px; }
  .l5-toggle-btn { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 4px; border: none; cursor: pointer; background: transparent; color: #6b7280; }
  .l5-toggle-btn.active { background: #fff; color: #111827; box-shadow: 0 1px 3px rgba(0,0,0,.08); }

  /* ── filter chips ── */
  .l5-filters { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
  .l5-filter { font-size: 10px; font-weight: 600; padding: 4px 10px; border-radius: 999px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; color: #6b7280; }
  .l5-filter.active { background: #111827; color: #fff; border-color: #111827; }

  /* ── Responsive ── */
  @media (max-width: 768px) {
    .l5-wrap { flex-direction: column; }
    .l5-side { width: 100%; max-height: none; }
    .l5-kpi-grid { grid-template-columns: repeat(2, 1fr); }
  }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Test Company';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
  $isLive = $dataSourceConnected ?? false;

  $layerScenarios = $scenarios['l5'] ?? [];
  $selectedScenario = $layerScenarios[0] ?? null;
@endphp

<div class="l5-root">

  {{-- ── TOP BAR ── --}}
  <div class="l5-topbar">
    <div>
      <div class="l5-topbar-title">AI/ML Predictions</div>
      <div style="font-size:11px; color:#6b7280; margin-top:2px;">
        Tenant: <span style="color:#0d9488; font-weight:500;">{{ $cn }}</span>
        @if($isLive)
          <span class="l5-pill live" style="margin-left:8px;">
            <span style="width:6px; height:6px; border-radius:50%; background:#22c55e; display:inline-block;"></span>Live
          </span>
        @else
          <span class="l5-pill demo" style="margin-left:8px;">
            <span style="width:6px; height:6px; border-radius:50%; background:#f59e0b; display:inline-block;"></span>Demo Data
          </span>
        @endif
      </div>
    </div>
    <div style="display:flex; align-items:center; gap:16px; font-size:11px; color:#6b7280;">
      {{-- Data Toggle --}}
      <div class="l5-toggle">
        <button class="l5-toggle-btn {{ !$isLive ? 'active' : '' }}" onclick="window.location='{{ route('client.layer.l5') }}'">Demo Data</button>
        <button class="l5-toggle-btn {{ $isLive ? 'active' : '' }}" onclick="window.location='{{ route('client.sources.index') }}'">Connect Database →</button>
      </div>

      {{-- Home button --}}
      <a href="{{ route('client.dashboard') }}"
         style="display:flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; color:#9ca3af; text-decoration:none;"
         onmouseover="this.style.background='#f3f4f6'; this.style.color='#374151';"
         onmouseout="this.style.background='transparent'; this.style.color='#9ca3af';"
         title="Home">
        <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </a>

      <span style="display:flex; align-items:center; gap:4px;">
        <svg style="width:14px; height:14px; color:#a78bfa;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span style="font-weight:600; color:#374151;">{{ number_format(($profiles ?? collect())->count()) }} profiles</span>
      </span>

      <span style="display:flex; align-items:center; gap:4px;">
        <svg style="width:14px; height:14px; color:#f472b6;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span style="font-weight:600; color:#374151;">94.1% accuracy</span>
      </span>

      {{-- Avatar --}}
      <div style="position:relative;" data-dd-wrap>
        <button onclick="xpDd('ddL5')"
                style="width:32px; height:32px; border-radius:50%; background:#06b6d4; display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; font-weight:700; border:none; cursor:pointer;">
          {{ $av }}
        </button>
        <div id="ddL5" data-dd-menu
             style="position:absolute; right:0; top:42px; width:192px; background:#fff; border-radius:8px; box-shadow:0 10px 15px -3px rgba(0,0,0,.1); border:1px solid #e5e7eb; padding:4px 0; z-index:50; display:none;">
          <div style="padding:8px 16px; border-bottom:1px solid #f3f4f6;">
            <p style="font-size:12px; font-weight:600; color:#1f2937; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $cn }}</p>
            <p style="font-size:10px; color:#9ca3af; margin:2px 0 0;">Client Account</p>
          </div>
          <a href="{{ route('client.dashboard') }}"
             style="display:flex; align-items:center; gap:8px; padding:8px 16px; font-size:12px; color:#374151; text-decoration:none;"
             onmouseover="this.style.background='#f9fafb';"
             onmouseout="this.style.background='transparent';">
            <svg style="width:14px; height:14px; color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Profile Settings
          </a>
          <hr style="margin:4px 0; border:none; border-top:1px solid #f3f4f6;">
          <form method="POST" action="{{ route('client.logout') }}" style="margin:0;">
            @csrf
            <button type="submit"
                    style="width:100%; display:flex; align-items:center; gap:8px; padding:8px 16px; font-size:12px; color:#dc2626; background:none; border:none; cursor:pointer; text-align:left;"
                    onmouseover="this.style.background='#fef2f2';"
                    onmouseout="this.style.background='transparent';">
              <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              Log Out
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- ── SCROLLABLE BODY ── --}}
  <div class="l5-main-scroll">
    <div class="l5-wrap">

      {{-- LEFT: Scenarios --}}
      <div class="l5-main">

        {{-- Layer label --}}
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#0d9488;">L5 — Decision Scenarios</span>
          <span style="flex:1; height:1.5px; background:linear-gradient(to right,#99f6e4,transparent); display:block; max-width:120px;"></span>
        </div>

        @forelse($layerScenarios as $scenario)
          @php
            $scenarioColor = $scenario['lc'] ?? '#0EA5E9';
          @endphp
          <div class="l5-scenario" style="border-left:3px solid {{ $scenarioColor }};" data-scenario="{{ $scenario['id'] }}">

            {{-- Header row --}}
            <div class="l5-sc-hd">
              <div class="l5-sc-ico" style="background:{{ $scenarioColor }}20; color:{{ $scenarioColor }};">
                {{ $scenario['ico'] ?? '🔹' }}
              </div>
              <div style="flex:1;">
                <div class="l5-sc-name">{{ $scenario['name'] }}</div>
                <div class="l5-sc-meta">{{ $scenario['users'] ?? '' }}</div>
              </div>
              <div class="l5-sc-rev" style="color:{{ $scenarioColor }};">{{ $scenario['rev'] ?? '' }}</div>
            </div>

            {{-- Urgency badge --}}
            @if(isset($scenario['urg']))
              <div class="l5-sc-urg" style="color:{{ $scenario['urgDot'] ?? $scenarioColor }};">
                <span style="background:{{ $scenario['urgDot'] ?? $scenarioColor }}; width:6px; height:6px; border-radius:50%; display:inline-block;"></span>
                {{ $scenario['ul'] ?? '' }}
              </div>
            @endif

            {{-- Banner --}}
            @if(isset($scenario['banner']))
              <div class="l5-banner" style="background:{{ $scenario['banner']['c'] ?? $scenarioColor }}10; color:{{ $scenario['banner']['c'] ?? $scenarioColor }}; border-left:3px solid {{ $scenario['banner']['c'] ?? $scenarioColor }};">
                {{ $scenario['banner']['text'] ?? '' }}
              </div>
            @endif

            {{-- KPIs --}}
            @if(isset($scenario['kpis']) && count($scenario['kpis']) > 0)
              <div class="l5-kpi-grid">
                @foreach($scenario['kpis'] as $kpi)
                  <div class="l5-kpi">
                    <div class="l5-kpi-val" style="color:{{ $kpi['c'] ?? '#111827' }};">{{ $kpi['v'] ?? '0' }}</div>
                    <div class="l5-kpi-lbl">{{ $kpi['l'] ?? '' }}</div>
                    <div class="l5-kpi-bar">
                      <div style="width:{{ min(100, $kpi['p'] ?? 0) }}%; background:{{ $kpi['c'] ?? '#0EA5E9' }};"></div>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif

            {{-- Decision options --}}
            @if(isset($scenario['dec']) && count($scenario['dec']) > 0)
              <div class="l5-dec">
                @foreach($scenario['dec'] as $dec)
                  <div class="l5-dec-row {{ ($dec['g'] ?? false) ? 'good' : 'bad' }}">
                    <span class="l5-dec-lbl">{{ $dec['l'] ?? '' }}</span>
                    <span class="l5-dec-val" style="color:{{ ($dec['g'] ?? false) ? '#10B981' : '#F43F5E' }};">{{ $dec['v'] ?? '' }}</span>
                    <span class="l5-dec-sub">{{ $dec['s'] ?? '' }}</span>
                  </div>
                @endforeach
              </div>
            @endif

            {{-- Expected outcome --}}
            @if(isset($scenario['out']) && count($scenario['out']) > 0)
              <div class="l5-out">
                <div class="l5-out-hd">Expected Outcome</div>
                @foreach($scenario['out'] as $out)
                  <div class="l5-out-row">
                    <span class="l5-out-lbl">{{ $out['l'] ?? '' }}</span>
                    <span class="l5-out-val">{{ $out['v'] ?? '' }}</span>
                  </div>
                @endforeach
              </div>
            @endif

            {{-- CTAs --}}
            @if(isset($scenario['ctas']) && count($scenario['ctas']) > 0)
              <div class="l5-cta-wrap">
                @foreach($scenario['ctas'] as $cta)
                  <button class="l5-cta" 
                          style="border-color:{{ $cta['c'] ?? '#e5e7eb' }};"
                          onclick="loadUsers('{{ $cta['view'] ?? '' }}', '{{ $scenario['id'] }}')">
                    <span class="l5-cta-ico">{{ $cta['ico'] ?? '▶' }}</span>
                    <div class="l5-cta-body">
                      <div class="l5-cta-tit">{{ $cta['l'] ?? '' }}</div>
                      <div class="l5-cta-desc">{{ $cta['d'] ?? '' }}</div>
                    </div>
                    <span class="l5-cta-badge" style="background:{{ $cta['c'] ?? '#0EA5E9' }}10; color:{{ $cta['c'] ?? '#0EA5E9' }};">{{ $cta['b'] ?? '' }}</span>
                  </button>
                @endforeach
              </div>
            @endif

          </div>
        @empty
          <div style="text-align:center; padding:40px; color:#9ca3af;">
            <p>No scenarios available. Connect a database to see AI/ML predictions.</p>
            <a href="{{ route('client.sources.index') }}" style="display:inline-block; margin-top:10px; padding:8px 16px; background:#0EA5E9; color:#fff; border-radius:6px; font-size:12px; text-decoration:none;">Connect Database</a>
          </div>
        @endforelse

      </div>

      {{-- RIGHT: User Details Panel --}}
      <div class="l5-side">
        <div class="l5-side-hd">User Details</div>
        <div class="l5-side-sub">Click any action button to load affected users</div>

        <div id="userDetailsPanel" class="l5-user-empty">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
          <p style="font-size:12px;">Click any action button<br>to see the affected users<br>with full details</p>
        </div>
      </div>

    </div>
  </div>

</div>

<script>
// Dropdown helper
function xpDd(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('[data-dd-wrap]')) {
    document.querySelectorAll('[data-dd-menu]').forEach(m => m.style.display = 'none');
  }
});

// Load users into side panel
function loadUsers(viewKey, scenarioId) {
  const panel = document.getElementById('userDetailsPanel');
  const userGroups = @json($userGroups ?? []);
  const users = userGroups[viewKey] || [];

  if (users.length === 0) {
    panel.innerHTML = `
      <svg style="width:48px; height:48px; margin-bottom:10px; opacity:.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
      </svg>
      <p style="font-size:12px;">No users found for this view.<br>Connect database to see live data.</p>
    `;
    panel.className = 'l5-user-empty';
    return;
  }

  panel.className = '';
  panel.innerHTML = '<div style="font-size:11px; color:#6b7280; margin-bottom:10px;">' + users.length + ' users loaded</div>' +
    users.map(u => `
      <div class="l5-user-card">
        <div class="l5-user-hd">
          <div class="l5-user-avatar" style="background:${u.avatarColor || '#0EA5E9'};">${u.initials || '?'}</div>
          <div class="l5-user-info">
            <div class="l5-user-name">${u.name || 'Unknown'}</div>
            <div class="l5-user-co">${u.company || ''} · ${u.industry || ''}</div>
          </div>
          <div class="l5-user-amt" style="color:${u.tierColor || '#0EA5E9'};">${u.amountLabel || ''}</div>
        </div>
        ${(u.tags || []).map(t => `<span class="l5-user-tag" style="background:${u.tierColor || '#0EA5E9'}10; color:${u.tierColor || '#0EA5E9'};">${t}</span>`).join('')}
      </div>
    `).join('');
}

// Auto-select first scenario
@if(isset($layerScenarios[0]))
document.addEventListener('DOMContentLoaded', function() {
  const firstScenario = document.querySelector('[data-scenario]');
  if (firstScenario) firstScenario.classList.add('active');
});
@endif
</script>

@endsection
