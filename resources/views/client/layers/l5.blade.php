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

  // ── NEW: Speaking Score Intervention counts ──────────────────────────────
  $speakingUsers = ($userGroups['speaking_low'] ?? collect())->count();
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

        {{-- ── NEW: Speaking Score Intervention Card ─────────────────────────── --}}
        @if($speakingUsers > 0)
          <div class="l5-scenario" style="border-left:3px solid #A855F7;" data-scenario="speaking">
            <div class="l5-sc-hd">
              <div class="l5-sc-ico" style="background:#A855F720; color:#A855F7;">🎤</div>
              <div style="flex:1;">
                <div class="l5-sc-name">Speaking Score Intervention — {{ $speakingUsers }} Students</div>
                <div class="l5-sc-meta">Speaking score < 30 · Needs 1-on-1 coaching</div>
              </div>
              <div class="l5-sc-rev" style="color:#A855F7;">${{ number_format($speakingUsers * 150) }}</div>
            </div>
            
            <div class="l5-sc-urg" style="color:#F59E0B;">
              <span style="background:#F59E0B; width:6px; height:6px; border-radius:50%; display:inline-block;"></span>
              Intervention required
            </div>

            <div class="l5-banner" style="background:#A855F710; color:#A855F7; border-left:3px solid #A855F7;">
              {{ $speakingUsers }} students have speaking scores below 30. Personalised 1-on-1 coaching sessions can improve their scores by an average of 15 points. Early intervention prevents dropout.
            </div>

            <div class="l5-kpi-grid">
              <div class="l5-kpi">
                <div class="l5-kpi-val" style="color:#A855F7;">{{ $speakingUsers }}</div>
                <div class="l5-kpi-lbl">Students Need Help</div>
                <div class="l5-kpi-bar">
                  <div style="width:{{ min(100, $speakingUsers * 5) }}%; background:#A855F7;"></div>
                </div>
              </div>
              <div class="l5-kpi">
                <div class="l5-kpi-val" style="color:#10B981;">+15</div>
                <div class="l5-kpi-lbl">Avg Score Improvement</div>
                <div class="l5-kpi-bar">
                  <div style="width:75%; background:#10B981;"></div>
                </div>
              </div>
              <div class="l5-kpi">
                <div class="l5-kpi-val" style="color:#F59E0B;">65%</div>
                <div class="l5-kpi-lbl">Retention with Coaching</div>
                <div class="l5-kpi-bar">
                  <div style="width:65%; background:#F59E0B;"></div>
                </div>
              </div>
              <div class="l5-kpi">
                <div class="l5-kpi-val" style="color:#F43F5E;">18%</div>
                <div class="l5-kpi-lbl">Dropout without Help</div>
                <div class="l5-kpi-bar">
                  <div style="width:18%; background:#F43F5E;"></div>
                </div>
              </div>
            </div>

            <div class="l5-dec">
              <div class="l5-dec-row good">
                <span class="l5-dec-lbl">Assign speaking tutor + personalised plan</span>
                <span class="l5-dec-val" style="color:#10B981;">+15 pts</span>
                <span class="l5-dec-sub">Average improvement · 65% retention</span>
              </div>
              <div class="l5-dec-row bad">
                <span class="l5-dec-lbl">No intervention — standard curriculum</span>
                <span class="l5-dec-val" style="color:#F43F5E;">-2 pts</span>
                <span class="l5-dec-sub">Score stagnation · 18% dropout risk</span>
              </div>
            </div>

            <div class="l5-out">
              <div class="l5-out-hd">Expected Outcome</div>
              <div class="l5-out-row">
                <span class="l5-out-lbl">Students receiving coaching</span>
                <span class="l5-out-val">{{ $speakingUsers }}</span>
              </div>
              <div class="l5-out-row">
                <span class="l5-out-lbl">Expected score improvement</span>
                <span class="l5-out-val">+15 points average</span>
              </div>
              <div class="l5-out-row">
                <span class="l5-out-lbl">Retention rate</span>
                <span class="l5-out-val">65% (vs 82% without)</span>
              </div>
              <div class="l5-out-row">
                <span class="l5-out-lbl">Revenue protected</span>
                <span class="l5-out-val">${{ number_format($speakingUsers * 150) }}</span>
              </div>
            </div>

            <div class="l5-cta-wrap">
              <button class="l5-cta" style="border-color:#10B981;" onclick="loadUsers('speaking_low', 'speaking')">
                <span class="l5-cta-ico">👨‍🏫</span>
                <div class="l5-cta-body">
                  <div class="l5-cta-tit">Assign speaking tutor to all {{ $speakingUsers }} students</div>
                  <div class="l5-cta-desc">Personal tutor + simplified study plan + practice quizzes. Within 24 hours.</div>
                </div>
                <span class="l5-cta-badge" style="background:#10B98110; color:#10B981;">{{ round($speakingUsers * 0.65) }} recoveries</span>
              </button>
              <button class="l5-cta" style="border-color:#0EA5E9;" onclick="loadUsers('speaking_low', 'speaking')">
                <span class="l5-cta-ico">📋</span>
                <div class="l5-cta-body">
                  <div class="l5-cta-tit">View detailed speaking score breakdown</div>
                  <div class="l5-cta-desc">See each student's score, history, and recommended intervention.</div>
                </div>
                <span class="l5-cta-badge" style="background:#0EA5E910; color:#0EA5E9;">{{ $speakingUsers }} students</span>
              </button>
            </div>
          </div>
        @endif

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

  <!-- Email Modal (Shared: Single & Bulk) -->
  <div id="emailModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:100; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:560px; max-width:92%; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 20px 25px -5px rgba(0,0,0,.1);">
      <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between;">
        <h3 id="emailModalTitle" style="margin:0; font-size:14px; font-weight:600; color:#111827;">Send Email</h3>
        <button onclick="closeEmailModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; padding:4px; font-size:16px;">✕</button>
      </div>
      <div style="padding:20px; overflow-y:auto;">
        
        <!-- Template Selection -->
        <div style="margin-bottom:16px;">
          <label style="display:block; font-size:11px; font-weight:600; color:#374151; margin-bottom:6px;">Email Template</label>
          <select id="emailTemplateSelect" onchange="applyTemplate()" style="width:100%; padding:8px 12px; border:1px solid #e5e7eb; border-radius:6px; font-size:12px; outline:none; background:#fff; cursor:pointer;">
            <option value="">-- Select a template --</option>
            @foreach($emailTemplates ?? [] as $template)
              <option value="{{ $template['id'] }}" data-subject="{{ $template['subject'] }}" data-body="{{ $template['body'] }}" data-category="{{ $template['category'] }}">
                {{ $template['name'] }} ({{ ucfirst($template['category']) }})
              </option>
            @endforeach
          </select>
        </div>
        
        <!-- Template Preview Badge -->
        <div id="templatePreviewBadge" style="display:none; margin-bottom:14px; padding:8px 12px; background:#f0fdf4; border:1px solid #86efac; border-radius:6px; font-size:11px; color:#166534;">
          <span style="font-weight:600;">✓ Template loaded:</span> <span id="templatePreviewName"></span>
          <button onclick="clearTemplate()" style="margin-left:8px; background:none; border:none; color:#166534; cursor:pointer; font-size:11px; text-decoration:underline;">Clear</button>
        </div>
        
        <div style="margin-bottom:14px;">
          <label style="display:block; font-size:11px; font-weight:600; color:#374151; margin-bottom:4px;">To</label>
          <input type="text" id="emailTo" readonly style="width:100%; padding:8px 12px; border:1px solid #e5e7eb; border-radius:6px; font-size:12px; background:#f9fafb; color:#6b7280; box-sizing:border-box;">
        </div>
        
        <div style="margin-bottom:14px;">
          <label style="display:block; font-size:11px; font-weight:600; color:#374151; margin-bottom:4px;">Subject <span style="color:#F43F5E;">*</span></label>
          <input type="text" id="emailSubject" placeholder="Enter email subject..." style="width:100%; padding:8px 12px; border:1px solid #e5e7eb; border-radius:6px; font-size:12px; outline:none; box-sizing:border-box;">
        </div>
        
        <div style="margin-bottom:14px;">
          <label style="display:block; font-size:11px; font-weight:600; color:#374151; margin-bottom:4px;">Message <span style="color:#F43F5E;">*</span></label>
          <textarea id="emailBody" rows="8" placeholder="Type your message here or select a template above..." style="width:100%; padding:8px 12px; border:1px solid #e5e7eb; border-radius:6px; font-size:12px; outline:none; resize:vertical; box-sizing:border-box; font-family:monospace;"></textarea>
          <div style="margin-top:4px; font-size:10px; color:#9ca3af;">Available variables: [[student_name]], [[speaking_score]], [[intent_score]], [[churn_score]], [[loyalty_score]], [[overall_score]], [[completed_courses]], [[days_since_login]], [[company_name]]</div>
        </div>
      </div>
      <div style="padding:12px 20px; border-top:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
        <span id="emailCharCount" style="font-size:10px; color:#9ca3af;">0 characters</span>
        <div style="display:flex; gap:8px;">
          <button onclick="closeEmailModal()" style="padding:8px 16px; border:1px solid #e5e7eb; border-radius:6px; background:#fff; color:#374151; font-size:12px; font-weight:600; cursor:pointer;">Cancel</button>
          <button id="sendEmailBtn" style="padding:8px 16px; border:none; border-radius:6px; background:#0EA5E9; color:#fff; font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
            📧 Send Email
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ── Dropdown helper ──────────────────────────────────────────────────────────
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

// ── Escape HTML ──────────────────────────────────────────────────────────────
function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// ── Global user groups cache (for event delegation) ──────────────────────────
let _userGroupsCache = {};
let _currentViewKey = '';

// ── Load users into side panel ───────────────────────────────────────────────
function loadUsers(viewKey, scenarioId) {
  const panel = document.getElementById('userDetailsPanel');
  const userGroups = @json($userGroups ?? []);
  _userGroupsCache = userGroups;
  _currentViewKey = viewKey;
  const users = userGroups[viewKey] || [];

  // Highlight active scenario
  document.querySelectorAll('.l5-scenario').forEach(el => el.classList.remove('active'));
  const activeScenario = document.querySelector('[data-scenario="' + scenarioId + '"]');
  if (activeScenario) activeScenario.classList.add('active');

  if (users.length === 0) {
    panel.innerHTML = `
      <svg style="width:48px; height:48px; margin-bottom:10px; opacity:.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
      </svg>
      <p style="font-size:12px;">No users found for this view.<br>Connect database to see live data.</p>
    `;
    panel.className = 'l5-user-empty';
    updateBulkActionBar(0);
    return;
  }

  panel.className = '';
  
  // Build user cards with checkboxes
  const cardsHtml = users.map((u, idx) => {
    const hasEmail = u.email && u.email !== 'N/A' && u.email !== '';
    return `
      <div class="l5-user-card" data-user-idx="${idx}" data-view-key="${escapeHtml(viewKey)}" data-has-email="${hasEmail ? '1' : '0'}">
        <div style="display:flex; align-items:flex-start; gap:8px;">
          <div style="padding-top:4px;">
            <input type="checkbox" class="l5-user-checkbox" data-user-idx="${idx}" data-email="${escapeHtml(u.email || '')}" data-name="${escapeHtml(u.name || 'Student')}" ${!hasEmail ? 'disabled' : ''} style="width:16px; height:16px; cursor:pointer; accent-color:#0EA5E9;">
          </div>
          <div style="flex:1; min-width:0;">
            <div class="l5-user-hd">
              <div class="l5-user-avatar" style="background:${u.avatarColor || '#0EA5E9'};">${u.initials || '?'}</div>
              <div class="l5-user-info">
                <div class="l5-user-name">${escapeHtml(u.name || 'Unknown')}</div>
                <div class="l5-user-co">${escapeHtml(u.company || '')} · ${escapeHtml(u.industry || '')}</div>
              </div>
              <div class="l5-user-amt" style="color:${u.tierColor || '#0EA5E9'};">${escapeHtml(u.amountLabel || '')}</div>
            </div>
            ${(u.tags || []).map(t => `<span class="l5-user-tag" style="background:${u.tierColor || '#0EA5E9'}10; color:${u.tierColor || '#0EA5E9'};">${escapeHtml(t)}</span>`).join('')}
            ${(u.fields || []).map(f => `
              <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:11px; border-top:1px solid #f3f4f6; margin-top:6px;">
                <span style="color:#6b7280;">${escapeHtml(f[0])}</span>
                <span style="font-weight:600; color:#111827;">${escapeHtml(f[1])}</span>
              </div>
            `).join('')}
            
            <!-- Individual Send Email Button -->
            <div style="margin-top:10px; padding-top:8px; border-top:1px solid #e5e7eb;">
              <button class="l5-send-email-btn" data-user-idx="${idx}" data-view-key="${escapeHtml(viewKey)}"
                      style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:6px; border:none; background:#0EA5E9; color:#fff; font-size:11px; font-weight:600; cursor:pointer; transition:all .15s;"
                      ${!hasEmail ? 'disabled' : ''}>
                <svg style="width:12px; height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Send Email
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');

  panel.innerHTML = `
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
      <span style="font-size:11px; color:#6b7280;">${users.length} users loaded</span>
      <label style="display:flex; align-items:center; gap:6px; font-size:11px; color:#374151; cursor:pointer; font-weight:500;">
        <input type="checkbox" id="selectAllUsers" style="width:16px; height:16px; cursor:pointer; accent-color:#0EA5E9;">
        Select All
      </label>
    </div>
    ${cardsHtml}
  `;
  
  // Attach select all listener
  document.getElementById('selectAllUsers')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.l5-user-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkActionBar();
  });
  
  updateBulkActionBar();
}

// ── Update bulk action bar visibility ──────────────────────────────────────────
function updateBulkActionBar() {
  const selected = document.querySelectorAll('.l5-user-checkbox:checked');
  let bar = document.getElementById('bulkActionBar');
  
  if (selected.length === 0) {
    if (bar) bar.style.display = 'none';
    return;
  }
  
  if (!bar) {
    bar = document.createElement('div');
    bar.id = 'bulkActionBar';
    bar.style.cssText = 'position:fixed; bottom:20px; left:50%; transform:translateX(-50%); background:#111827; color:#fff; padding:12px 24px; border-radius:10px; display:flex; align-items:center; gap:16px; box-shadow:0 10px 15px -3px rgba(0,0,0,.2); z-index:50; font-size:12px;';
    document.body.appendChild(bar);
  }
  
  bar.style.display = 'flex';
  bar.innerHTML = `
    <span style="font-weight:600;">${selected.length} selected</span>
    <button onclick="openBulkEmailModal()" style="display:inline-flex; align-items:center; gap:6px; padding:6px 16px; border-radius:6px; border:none; background:#0EA5E9; color:#fff; font-size:11px; font-weight:600; cursor:pointer;">
      <svg style="width:12px; height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
      </svg>
      Send Bulk Email
    </button>
    <button onclick="clearAllSelections()" style="background:none; border:none; color:#9ca3af; cursor:pointer; font-size:11px; padding:4px;">Clear</button>
  `;
}

// ── Clear all selections ───────────────────────────────────────────────────────
function clearAllSelections() {
  document.querySelectorAll('.l5-user-checkbox').forEach(cb => cb.checked = false);
  const selectAll = document.getElementById('selectAllUsers');
  if (selectAll) selectAll.checked = false;
  updateBulkActionBar();
}

// ── Event Delegation for Checkboxes and Send Email Buttons ─────────────────────
document.addEventListener('change', function(e) {
  if (e.target.classList.contains('l5-user-checkbox')) {
    updateBulkActionBar();
    // Uncheck "Select All" if any individual is unchecked
    if (!e.target.checked) {
      const selectAll = document.getElementById('selectAllUsers');
      if (selectAll) selectAll.checked = false;
    }
  }
});

document.addEventListener('click', function(e) {
  const btn = e.target.closest('.l5-send-email-btn');
  if (!btn) return;
  
  e.preventDefault();
  e.stopPropagation();
  
  if (btn.disabled) {
    showToast('No email address available for this student', 'error');
    return;
  }
  
  const viewKey = btn.getAttribute('data-view-key');
  const userIdx = parseInt(btn.getAttribute('data-user-idx'));
  const users = _userGroupsCache[viewKey] || [];
  const user = users[userIdx];
  
  if (!user || !user.email) {
    showToast('No email address available for this student', 'error');
    return;
  }
  
  openEmailModal(user.email, user.name);
});

// ── Open individual email modal ────────────────────────────────────────────────
function openEmailModal(userEmail, userName) {
  if (!userEmail) {
    showToast('No email address available', 'error');
    return;
  }
  
  const modal = document.getElementById('emailModal');
  if (!modal) {
    console.error('Email modal not found in DOM');
    return;
  }
  
  document.getElementById('emailModalTitle').textContent = 'Send Email to ' + (userName || 'Student');
  document.getElementById('emailTo').value = userEmail;
  document.getElementById('emailTo').readOnly = true;
  document.getElementById('emailSubject').value = '';
  document.getElementById('emailBody').value = '';
  document.getElementById('sendEmailBtn').onclick = sendSingleEmail;
  modal.style.display = 'flex';
}

// ── Open bulk email modal ────────────────────────────────────────────────────
function openBulkEmailModal() {
  const selected = document.querySelectorAll('.l5-user-checkbox:checked');
  if (selected.length === 0) {
    showToast('Please select at least one student', 'error');
    return;
  }
  
  const emails = [];
  const names = [];
  selected.forEach(cb => {
    const email = cb.getAttribute('data-email');
    const name = cb.getAttribute('data-name');
    if (email && email !== 'N/A') {
      emails.push(email);
      names.push(name);
    }
  });
  
  if (emails.length === 0) {
    showToast('No valid email addresses found in selection', 'error');
    return;
  }
  
  const modal = document.getElementById('emailModal');
  if (!modal) {
    console.error('Email modal not found in DOM');
    return;
  }
  
  document.getElementById('emailModalTitle').textContent = 'Send Bulk Email (' + emails.length + ' recipients)';
  document.getElementById('emailTo').value = emails.join(', ');
  document.getElementById('emailTo').readOnly = true;
  document.getElementById('emailSubject').value = '';
  document.getElementById('emailBody').value = '';
  
  // Store bulk data for sending
  modal.setAttribute('data-bulk-emails', JSON.stringify(emails));
  document.getElementById('sendEmailBtn').onclick = sendBulkEmail;
  
  modal.style.display = 'flex';
}

// ── Close email modal ────────────────────────────────────────────────────────
function closeEmailModal() {
  const modal = document.getElementById('emailModal');
  if (modal) {
    modal.style.display = 'none';
    modal.removeAttribute('data-bulk-emails');
  }
}

// ── Send single email ──────────────────────────────────────────────────────────
function sendSingleEmail() {
  const to = document.getElementById('emailTo').value;
  const subject = document.getElementById('emailSubject').value;
  const body = document.getElementById('emailBody').value;

  if (!to) { showToast('Recipient email is missing', 'error'); return; }
  if (!subject.trim()) { showToast('Please enter a subject', 'error'); return; }
  if (!body.trim()) { showToast('Please enter a message', 'error'); return; }

  const btn = document.getElementById('sendEmailBtn');
  const originalText = btn.innerHTML;
  btn.innerHTML = '<span style="display:inline-flex; align-items:center; gap:4px;">⏳ Sending...</span>';
  btn.disabled = true;

  fetch('{{ route("client.email.send") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ to: to, subject: subject, body: body })
  })
  .then(r => {
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.json();
  })
  .then(data => {
    btn.innerHTML = originalText;
    btn.disabled = false;
    if (data.success) {
      closeEmailModal();
      showToast('✓ Email sent successfully to ' + to, 'success');
    } else {
      showToast(data.message || 'Failed to send email', 'error');
    }
  })
  .catch(err => {
    btn.innerHTML = originalText;
    btn.disabled = false;
    console.error('Email send error:', err);
    showToast('Error sending email: ' + (err.message || 'Unknown error'), 'error');
  });
}

// ── Send bulk email ───────────────────────────────────────────────────────────
function sendBulkEmail() {
  const modal = document.getElementById('emailModal');
  const emailsJson = modal.getAttribute('data-bulk-emails');
  const emails = emailsJson ? JSON.parse(emailsJson) : [];
  const subject = document.getElementById('emailSubject').value;
  const body = document.getElementById('emailBody').value;

  if (emails.length === 0) { showToast('No recipients selected', 'error'); return; }
  if (!subject.trim()) { showToast('Please enter a subject', 'error'); return; }
  if (!body.trim()) { showToast('Please enter a message', 'error'); return; }

  const btn = document.getElementById('sendEmailBtn');
  const originalText = btn.innerHTML;
  btn.innerHTML = '<span style="display:inline-flex; align-items:center; gap:4px;">⏳ Sending to ' + emails.length + ' recipients...</span>';
  btn.disabled = true;

  fetch('{{ route("client.email.send.bulk") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ emails: emails, subject: subject, body: body })
  })
  .then(r => {
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.json();
  })
  .then(data => {
    btn.innerHTML = originalText;
    btn.disabled = false;
    if (data.success) {
      closeEmailModal();
      clearAllSelections();
      showToast('✓ Email sent successfully to ' + data.sent_count + ' of ' + emails.length + ' recipients', 'success');
    } else {
      showToast(data.message || 'Failed to send bulk email', 'error');
    }
  })
  .catch(err => {
    btn.innerHTML = originalText;
    btn.disabled = false;
    console.error('Bulk email send error:', err);
    showToast('Error sending bulk email: ' + (err.message || 'Unknown error'), 'error');
  });
}

// ── Toast notification ───────────────────────────────────────────────────────
function showToast(message, type) {
  document.querySelectorAll('.l5-toast').forEach(t => t.remove());
  
  const toast = document.createElement('div');
  toast.className = 'l5-toast';
  toast.style.cssText = 'position:fixed; top:20px; right:20px; padding:12px 20px; border-radius:8px; font-size:12px; font-weight:600; color:#fff; z-index:99999; box-shadow:0 4px 12px rgba(0,0,0,.15); transition:opacity .3s, transform .3s; ' + 
    (type === 'success' ? 'background:#10B981;' : 'background:#F43F5E;');
  toast.textContent = message;
  document.body.appendChild(toast);
  
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// ── Auto-select first scenario ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  const firstScenario = document.querySelector('[data-scenario]');
  if (firstScenario) firstScenario.classList.add('active');
});

// ── Template handling ──────────────────────────────────────────────────────────
function applyTemplate() {
  const select = document.getElementById('emailTemplateSelect');
  const option = select.options[select.selectedIndex];
  if (!option.value) return;
  
  const subject = option.getAttribute('data-subject');
  const body = option.getAttribute('data-body');
  
  // Get current student data for variable substitution
  const modal = document.getElementById('emailModal');
  const isBulk = modal.hasAttribute('data-bulk-emails');
  
  let student = {};
  if (!isBulk) {
    // Single email - get from cached data
    const viewKey = _currentViewKey;
    const users = _userGroupsCache[viewKey] || [];
    // Find student by email matching the To field
    const toEmail = document.getElementById('emailTo').value;
    student = users.find(u => u.email === toEmail) || {};
  } else {
    // Bulk - use first selected student as sample, or empty
    const selected = document.querySelectorAll('.l5-user-checkbox:checked');
    if (selected.length > 0) {
      const firstIdx = parseInt(selected[0].getAttribute('data-user-idx'));
      student = _userGroupsCache[_currentViewKey][firstIdx] || {};
    }
  }
  
  // Apply variables
  const processedSubject = applyTemplateVars(subject, student);
  const processedBody = applyTemplateVars(body, student);
  
  document.getElementById('emailSubject').value = processedSubject;
  document.getElementById('emailBody').value = processedBody;
  
  // Show preview badge
  document.getElementById('templatePreviewBadge').style.display = 'block';
  document.getElementById('templatePreviewName').textContent = option.text.split('(')[0].trim();
  
  updateCharCount();
}

function clearTemplate() {
  document.getElementById('emailTemplateSelect').selectedIndex = 0;
  document.getElementById('templatePreviewBadge').style.display = 'none';
}

function applyTemplateVars(content, student) {
  if (!content) return '';
  
  const vars = {
    '[[student_name]]': student.name || 'Student',
    '[[student_id]]': student.id || '',
    '[[email]]': student.email || '',
    '[[speaking_score]]': student.speaking_score || 'N/A',
    '[[intent_score]]': student.intent_score || 'N/A',
    '[[churn_score]]': student.churn_score || 'N/A',
    '[[loyalty_score]]': student.loyalty_score || 'N/A',
    '[[engagement_score]]': student.engagement_score || 'N/A',
    '[[overall_score]]': student.overall_score || 'N/A',
    '[[completed_courses]]': student.completed_courses || '0',
    '[[best_score]]': student.best_score || 'N/A',
    '[[days_since_login]]': student.last_login ? Math.floor((Date.now() - new Date(student.last_login).getTime()) / (1000 * 60 * 60 * 24)) : 'N/A',
    '[[company_name]]': '{{ $cn ?? "Your Learning Platform" }}',
    '[[booking_link]]': window.location.origin + '/book-session',
    '[[upgrade_link]]': window.location.origin + '/upgrade',
    '[[test_link]]': window.location.origin + '/mock-test',
    '[[referral_link]]': window.location.origin + '/refer',
    '[[calendar_link]]': window.location.origin + '/schedule-call',
    '[[support_phone]]': '+1-800-LEARN',
    '[[offer_price]]': '$99',
    '[[discount_amount]]': '$50',
    '[[referral_bonus]]': '$20',
  };
  
  let result = content;
  for (const [key, val] of Object.entries(vars)) {
    result = result.split(key).join(val);
  }
  return result;
}

function updateCharCount() {
  const body = document.getElementById('emailBody').value;
  document.getElementById('emailCharCount').textContent = body.length + ' characters';
}

// Update char count on input
document.addEventListener('input', function(e) {
  if (e.target.id === 'emailBody') {
    updateCharCount();
  }
});
</script>

@endsection