@extends('layouts.platform')
@section('title', 'AI/ML Predictions Dashboard')

@push('styles')
<style>
  /* ── Root layout fix for scrolling ── */
  .aiml-root {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
    background: #f9fafb;
  }

  .aiml-main {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 24px;
  }

  .aiml-wrap {
    max-width: 1200px;
    margin: 0 auto;
  }

  /* ── Top bar / header ── */
  .aiml-topbar {
    flex-shrink: 0;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 10;
  }

  .aiml-topbar-title {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
  }

  .aiml-topbar-sub {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
  }

  .aiml-topbar-actions {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  /* ── Connection status card ── */
  .aiml-conn {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .aiml-conn-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .aiml-conn-icon.connected {
    background: #dcfce7;
    color: #166534;
  }

  .aiml-conn-icon.disconnected {
    background: #fef3c7;
    color: #92400e;
  }

  .aiml-conn-body {
    flex: 1;
  }

  .aiml-conn-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
  }

  .aiml-conn-desc {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
  }

  .aiml-conn-btn {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    flex-shrink: 0;
  }

  .aiml-conn-btn.connect {
    background: #0ea5e9;
    color: #fff;
  }

  .aiml-conn-btn.view {
    background: #111827;
    color: #fff;
  }

  /* ── Stats grid ── */
  .aiml-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
  }

  .aiml-stat {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 18px;
  }

  .aiml-stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
  }

  .aiml-stat-val {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    line-height: 1;
  }

  .aiml-stat-lbl {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-top: 8px;
  }

  .aiml-stat-delta {
    font-size: 11px;
    font-weight: 600;
    margin-top: 4px;
  }

  .aiml-stat-delta.up {
    color: #10b981;
  }

  .aiml-stat-delta.down {
    color: #f43f5e;
  }

  /* ── Models table ── */
  .aiml-section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
  }

  .aiml-section-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
  }

  .aiml-section-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
  }

  .aiml-section-action {
    font-size: 12px;
    color: #0ea5e9;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
  }

  .aiml-tbl {
    width: 100%;
    border-collapse: collapse;
  }

  .aiml-tbl th {
    text-align: left;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #9ca3af;
    padding: 0 8px 10px 0;
  }

  .aiml-tbl td {
    padding: 10px 8px 10px 0;
    border-top: 1px solid #f3f4f6;
    font-size: 12px;
    vertical-align: middle;
  }

  .aiml-tbl tr:first-child td {
    border-top: none;
  }

  .aiml-model-name {
    font-weight: 600;
    color: #111827;
  }

  .aiml-model-type {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding: 2px 8px;
    border-radius: 4px;
    display: inline-block;
  }

  .aiml-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
  }

  .aiml-status::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
  }

  .aiml-status.live {
    color: #10b981;
  }

  .aiml-status.live::before {
    background: #10b981;
  }

  .aiml-status.beta {
    color: #f59e0b;
  }

  .aiml-status.beta::before {
    background: #f59e0b;
  }

  .aiml-btn-sm {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    background: #fff;
    cursor: pointer;
    color: #374151;
  }

  .aiml-btn-sm:hover {
    background: #f9fafb;
  }

  /* ── Scenario cards ── */
  .aiml-scenarios {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }

  .aiml-sc {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px;
    cursor: pointer;
    transition: all .15s;
    text-decoration: none;
    color: inherit;
    display: block;
  }

  .aiml-sc:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
    transform: translateY(-1px);
  }

  .aiml-sc-hd {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
  }

  .aiml-sc-ico {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }

  .aiml-sc-title {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
  }

  .aiml-sc-desc {
    font-size: 11px;
    color: #6b7280;
    line-height: 1.4;
    margin-bottom: 10px;
  }

  .aiml-sc-meta {
    display: flex;
    gap: 12px;
    font-size: 11px;
  }

  .aiml-sc-meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .aiml-sc-meta-label {
    color: #9ca3af;
  }

  .aiml-sc-meta-val {
    font-weight: 600;
    color: #111827;
  }

  /* ── Quick actions ── */
  .aiml-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }

  .aiml-action {
    flex: 1;
    min-width: 200px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px;
    cursor: pointer;
    transition: all .15s;
    text-decoration: none;
    color: inherit;
    display: block;
  }

  .aiml-action:hover {
    border-color: #0ea5e9;
    box-shadow: 0 2px 8px rgba(14, 165, 233, .08);
  }

  .aiml-action-ico {
    font-size: 20px;
    margin-bottom: 8px;
  }

  .aiml-action-title {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
  }

  .aiml-action-desc {
    font-size: 11px;
    color: #6b7280;
    margin-top: 2px;
  }

  /* ── Responsive ── */
  @media (max-width: 768px) {
    .aiml-stats {
      grid-template-columns: repeat(2, 1fr);
    }
    .aiml-scenarios {
      grid-template-columns: 1fr;
    }
    .aiml-actions {
      flex-direction: column;
    }
    .aiml-action {
      min-width: 100%;
    }
  }
</style>
@endpush

@section('content')
@php
  $isLive = $dataSourceConnected ?? false;
  $cn = auth('client')->user()?->company_name ?? 'Test Company';
  $profileCount = ($profiles ?? collect())->count();

  // ── NEW: Filtered user counts for each scenario ─────────────────────────────
  $disc10Users     = ($userGroups['disc10_not_purchased'] ?? collect())->count();
  $nochurnUsers    = ($userGroups['nochurn_at_risk'] ?? collect())->count();
  $reactvalUsers   = ($userGroups['reactval_ready'] ?? collect())->count();
  $loyboostUsers   = ($userGroups['loyboost_loyal'] ?? collect())->count();
  $speakingUsers   = ($userGroups['speaking_low'] ?? collect())->count();

  $models = [
    ['name'=>'Churn Prediction', 'type'=>'CLASSIFICATION', 'tc'=>'#10b981', 'accuracy'=>94.2, 'status'=>'live'],
    ['name'=>'Purchase Propensity', 'type'=>'CLASSIFICATION', 'tc'=>'#10b981', 'accuracy'=>91.8, 'status'=>'live'],
    ['name'=>'Upsell Probability', 'type'=>'REGRESSION', 'tc'=>'#3b82f6', 'accuracy'=>88.5, 'status'=>'live'],
    ['name'=>'LTV Forecasting', 'type'=>'REGRESSION', 'tc'=>'#3b82f6', 'accuracy'=>87.3, 'status'=>'live'],
    ['name'=>'Subscription Renewal', 'type'=>'CLASSIFICATION', 'tc'=>'#10b981', 'accuracy'=>92.1, 'status'=>'live'],
    ['name'=>'Support Escalation', 'type'=>'SEQUENCE', 'tc'=>'#f59e0b', 'accuracy'=>85.7, 'status'=>'beta'],
    ['name'=>'Fraud / Anomaly', 'type'=>'ANOMALY', 'tc'=>'#ef4444', 'accuracy'=>96.4, 'status'=>'live'],
    ['name'=>'Sentiment / NLP', 'type'=>'NLP', 'tc'=>'#10b981', 'accuracy'=>89.2, 'status'=>'live'],
    ['name'=>'Next-Best-Action LLM', 'type'=>'LLM', 'tc'=>'#8b5cf6', 'accuracy'=>84.1, 'status'=>'beta'],
    ['name'=>'Lead Qualification', 'type'=>'CLASSIFICATION', 'tc'=>'#10b981', 'accuracy'=>90.5, 'status'=>'live'],
    ['name'=>'Recommendation Engine', 'type'=>'RECOMMENDER', 'tc'=>'#f97316', 'accuracy'=>87.9, 'status'=>'live'],
  ];

  $scenarios = [
    ['id'=>'disc10', 'ico'=>'💰', 'color'=>'#FF6B35', 'title'=>'10% Discount → Hot Buyers (Not Purchased)', 'desc'=>'Students who created an account but have not purchased any plan. Pitch them with a 10% discount to convert.', 'users'=>$disc10Users, 'revenue'=>'$' . number_format($disc10Users * 294)],
    ['id'=>'nochurn', 'ico'=>'🛡️', 'color'=>'#F43F5E', 'title'=>'Win-Back Now vs Wait', 'desc'=>'Acting now saves more ARR than waiting 72hrs. Churn model computes retention probability now vs after window.', 'users'=>$nochurnUsers, 'revenue'=>'$' . number_format($nochurnUsers * 4200)],
    ['id'=>'reactval', 'ico'=>'📈', 'color'=>'#0EA5E9', 'title'=>'Reactivation Value', 'desc'=>'Personalised win-back delivers 3.9× the ROI of generic campaigns. EV model: personalised vs generic CVR.', 'users'=>$reactvalUsers, 'revenue'=>'$' . number_format($reactvalUsers * 4200)],
    ['id'=>'loyboost', 'ico'=>'🏆', 'color'=>'#10B981', 'title'=>'Loyalty ROI', 'desc'=>'Loyal Advocates generate 3.2× LTV vs average. Amplifying referral programme adds monthly revenue with zero acquisition cost.', 'users'=>$loyboostUsers, 'revenue'=>'$' . number_format($loyboostUsers * 1480)],
    ['id'=>'speaking', 'ico'=>'🎤', 'color'=>'#A855F7', 'title'=>'Speaking Score Intervention', 'desc'=>'Students with low speaking scores who need 1-on-1 coaching sessions. Pitch personalised tutoring to improve scores.', 'users'=>$speakingUsers, 'revenue'=>'$' . number_format($speakingUsers * 150)],
  ];
@endphp

<div class="aiml-root">

  {{-- ── TOP BAR ── --}}
  <div class="aiml-topbar">
    <div>
      <div class="aiml-topbar-title">AI/ML Predictions Dashboard</div>
      <div class="aiml-topbar-sub">Decision scenarios, model registry, and prediction micro-signals for {{ $cn }}</div>
    </div>
    <div class="aiml-topbar-actions">
      {{-- Data Toggle --}}
      <div style="display:inline-flex; align-items:center; gap:6px; background:#f3f4f6; border-radius:6px; padding:3px;">
        <button style="font-size:11px; font-weight:600; padding:4px 10px; border-radius:4px; border:none; cursor:pointer; background:{{ !$isLive ? '#fff' : 'transparent' }}; color:{{ !$isLive ? '#111827' : '#6b7280' }}; box-shadow:{{ !$isLive ? '0 1px 3px rgba(0,0,0,.08)' : 'none' }};" onclick="window.location='{{ route('client.aiml.dashboard') }}'">Demo Data</button>
        <button style="font-size:11px; font-weight:600; padding:4px 10px; border-radius:4px; border:none; cursor:pointer; background:{{ $isLive ? '#fff' : 'transparent' }}; color:{{ $isLive ? '#111827' : '#6b7280' }}; box-shadow:{{ $isLive ? '0 1px 3px rgba(0,0,0,.08)' : 'none' }};" onclick="window.location='{{ route('client.sources.index') }}'">Connect Database →</button>
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

      {{-- Stats pills --}}
      <span style="display:flex; align-items:center; gap:4px; font-size:11px; color:#6b7280;">
        <svg style="width:14px; height:14px; color:#a78bfa;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span style="font-weight:600; color:#374151;">{{ number_format($profileCount) }} profiles</span>
      </span>

      <span style="display:flex; align-items:center; gap:4px; font-size:11px; color:#6b7280;">
        <svg style="width:14px; height:14px; color:#f472b6;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span style="font-weight:600; color:#374151;">94.1% accuracy</span>
      </span>

      {{-- Avatar --}}
      @php
        $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
      @endphp
      <div style="position:relative;" data-dd-wrap>
        <button onclick="xpDd('ddAiml')"
                style="width:32px; height:32px; border-radius:50%; background:#06b6d4; display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; font-weight:700; border:none; cursor:pointer;">
          {{ $av }}
        </button>
        <div id="ddAiml" data-dd-menu
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

  {{-- ── SCROLLABLE MAIN CONTENT ── --}}
  <div class="aiml-main">
    <div class="aiml-wrap">

      {{-- Connection Status --}}
      <div class="aiml-conn">
        <div class="aiml-conn-icon {{ $isLive ? 'connected' : 'disconnected' }}">
          @if($isLive)
            <svg style="width:24px; height:24px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
          @else
            <svg style="width:24px; height:24px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          @endif
        </div>
        <div class="aiml-conn-body">
          <div class="aiml-conn-title">{{ $isLive ? 'Database Connected' : 'Using Demo Data' }}</div>
          <div class="aiml-conn-desc">
            @if($isLive)
              Live data from connected database. {{ number_format($profileCount) }} profiles loaded. Predictions are computed in real-time.
            @else
              Showing simulated demo data. Connect your database to see real predictions and decision scenarios.
            @endif
          </div>
        </div>
        @if($isLive)
          <a href="{{ route('client.layer.l5') }}" class="aiml-conn-btn view">Open Decision Centre →</a>
        @else
          <a href="{{ route('client.sources.index') }}" class="aiml-conn-btn connect">Connect Database →</a>
        @endif
      </div>

      {{-- Stats Row --}}
      <div class="aiml-stats">
        <div class="aiml-stat">
          <div class="aiml-stat-icon" style="background:#dcfce7; color:#166534;">
            <svg style="width:20px; height:20px;" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h8l-1 8 11-12h-8z"/></svg>
          </div>
          <div class="aiml-stat-val" style="color:#166534;">11</div>
          <div class="aiml-stat-lbl">Active Models</div>
          <div class="aiml-stat-delta up">+2 this month</div>
        </div>
        <div class="aiml-stat">
          <div class="aiml-stat-icon" style="background:#dbeafe; color:#1e40af;">
            <svg style="width:20px; height:20px;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L20.66 7V17L12 22L3.34 17V7Z"/></svg>
          </div>
          <div class="aiml-stat-val" style="color:#1e40af;">{{ $isLive ? number_format($profileCount) : '8.7M' }}</div>
          <div class="aiml-stat-lbl">Profiles Scored</div>
          <div class="aiml-stat-delta up">+12% this week</div>
        </div>
        <div class="aiml-stat">
          <div class="aiml-stat-icon" style="background:#f3e8ff; color:#6b21a8;">
            <svg style="width:20px; height:20px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v6c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/><path d="M3 11v6c0 1.66 4.03 3 9 3s9-1.34 9-3v-6"/></svg>
          </div>
          <div class="aiml-stat-val" style="color:#6b21a8;">{{ $isLive ? number_format($profileCount * 8) : '620K' }}</div>
          <div class="aiml-stat-lbl">Predictions/Day</div>
          <div class="aiml-stat-delta up">+18% this week</div>
        </div>
        <div class="aiml-stat">
          <div class="aiml-stat-icon" style="background:#fef3c7; color:#92400e;">
            <svg style="width:20px; height:20px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div class="aiml-stat-val" style="color:#92400e;">0</div>
          <div class="aiml-stat-lbl">Drift Alerts</div>
          <div class="aiml-stat-delta up">All Nominal</div>
        </div>
      </div>

      {{-- Decision Scenarios --}}
      <div class="aiml-section">
        <div class="aiml-section-hd">
          <span class="aiml-section-title">🎯 Decision Scenarios</span>
          <a href="{{ route('client.layer.l5') }}" class="aiml-section-action">View All →</a>
        </div>
        <div class="aiml-scenarios">
          @foreach($scenarios as $sc)
            <a href="{{ route('client.layer.l5') }}" class="aiml-sc">
              <div class="aiml-sc-hd">
                <div class="aiml-sc-ico" style="background:{{ $sc['color'] }}20; color:{{ $sc['color'] }};">{{ $sc['ico'] }}</div>
                <div class="aiml-sc-title">{{ $sc['title'] }}</div>
              </div>
              <div class="aiml-sc-desc">{{ $sc['desc'] }}</div>
              <div class="aiml-sc-meta">
                <div class="aiml-sc-meta-item">
                  <span class="aiml-sc-meta-label">Users:</span>
                  <span class="aiml-sc-meta-val">{{ number_format($sc['users']) }}</span>
                </div>
                <div class="aiml-sc-meta-item">
                  <span class="aiml-sc-meta-label">Revenue:</span>
                  <span class="aiml-sc-meta-val">{{ $sc['revenue'] }}</span>
                </div>
              </div>
            </a>
          @endforeach
        </div>
      </div>

      {{-- Model Registry --}}
      <div class="aiml-section">
        <div class="aiml-section-hd">
          <span class="aiml-section-title">🤖 Model Registry</span>
          <span class="aiml-section-action">{{ count(array_filter($models, fn($m)=>$m['status']==='live')) }} Live, {{ count(array_filter($models, fn($m)=>$m['status']==='beta')) }} Beta</span>
        </div>
        <table class="aiml-tbl">
          <thead>
            <tr>
              <th style="width:30%">Model</th>
              <th style="width:18%">Type</th>
              <th style="width:14%">Accuracy</th>
              <th style="width:14%">Status</th>
              <th style="width:24%">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($models as $m)
            <tr>
              <td><span class="aiml-model-name">{{ $m['name'] }}</span></td>
              <td><span class="aiml-model-type" style="background:{{ $m['tc'] }}20; color:{{ $m['tc'] }};">{{ $m['type'] }}</span></td>
              <td style="font-weight:600; color:#111827;">{{ $m['accuracy'] }}%</td>
              <td>
                <span class="aiml-status {{ $m['status'] }}">
                  {{ $m['status'] === 'live' ? '● LIVE' : '● BETA' }}
                </span>
              </td>
              <td>
                <div style="display:flex; gap:6px;">
                  <button class="aiml-btn-sm">Inspect</button>
                  <button class="aiml-btn-sm">Retrain</button>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Quick Actions --}}
      <div class="aiml-section">
        <div class="aiml-section-hd">
          <span class="aiml-section-title">⚡ Quick Actions</span>
        </div>
        <div class="aiml-actions">
          <a href="{{ route('client.layer.l5') }}" class="aiml-action">
            <div class="aiml-action-ico">🎯</div>
            <div class="aiml-action-title">Decision Centre</div>
            <div class="aiml-action-desc">Run scenarios and view affected users</div>
          </a>
          <a href="{{ route('client.layer.l4') }}" class="aiml-action">
            <div class="aiml-action-ico">🧠</div>
            <div class="aiml-action-title">Behavioral Intelligence</div>
            <div class="aiml-action-desc">Hot buyers, at-risk, loyal advocates</div>
          </a>
          <a href="{{ route('client.sources.index') }}" class="aiml-action">
            <div class="aiml-action-ico">🔌</div>
            <div class="aiml-action-title">Data Sources</div>
            <div class="aiml-action-desc">Connect or manage database connections</div>
          </a>
          <a href="{{ route('client.analytics') }}" class="aiml-action">
            <div class="aiml-action-ico">📊</div>
            <div class="aiml-action-title">Analytics</div>
            <div class="aiml-action-desc">View behavioral analytics dashboard</div>
          </a>
        </div>
      </div>

      {{-- Footer spacer for bottom scroll padding --}}
      <div style="height:40px;"></div>

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
</script>

@endsection
