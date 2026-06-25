@extends('layouts.platform')
@section('title', 'Behavioral Intelligence')

@push('styles')
<style>
  .user-item { display:flex; align-items:center; gap:10px; padding:10px 14px; cursor:pointer; border-bottom:1px solid #f9fafb; transition:background .12s; }
  .user-item:hover:not(.active) { background:#f9fafb; }
  .user-item.active { background:#f0fdf4; border-left:3px solid #10b981; }
  .seg-pill { font-size:10px; padding:3px 9px; border-radius:20px; border:1px solid #e5e7eb; background:#fff; color:#6b7280; cursor:pointer; transition:all .12s; font-weight:500; white-space:nowrap; }
  .seg-pill.active { background:#111827; border-color:#111827; color:#fff; font-weight:600; }
  .score-chip { flex-shrink:0; text-align:center; padding:10px 8px; border-radius:10px; cursor:pointer; transition:all .15s; border-width:1.5px; border-style:solid; min-width:82px; }
  .score-chip.selected { box-shadow:0 0 0 2px #3b82f6, 0 0 0 4px rgba(59,130,246,.15); }
  .bi-bar-wrap { width:100%; height:5px; background:#f3f4f6; border-radius:9999px; overflow:hidden; margin-top:5px; }
  .bi-bar-fill { height:5px; border-radius:9999px; transition:width .5s ease; }
  .sig-item { display:flex; align-items:flex-start; gap:8px; padding:8px 0; border-bottom:1px solid #f3f4f6; }
  .sig-item:last-child { border-bottom:none; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));

  // Helper: get last active display (works with both Carbon and string)
  $getLastActive = function($profile) {
      $la = $profile->last_active_at ?? null;
      if (!$la) return null;
      if (is_string($la)) {
          try { return \Carbon\Carbon::parse($la); } catch (\Exception $e) { return null; }
      }
      return $la; // Already Carbon
  };

  $scoreTypes = [
    ['key'=>'intent_score',          'label'=>'Intent',       'inverted'=>false],
    ['key'=>'engagement_score',      'label'=>'Engagement',   'inverted'=>false],
    ['key'=>'churn_score',           'label'=>'Churn Risk',   'inverted'=>true ],
    ['key'=>'loyalty_score',         'label'=>'Loyalty',      'inverted'=>false],
    ['key'=>'trust_score',           'label'=>'Trust',        'inverted'=>false],
    ['key'=>'frustration_score',     'label'=>'Frustration',  'inverted'=>true ],
    ['key'=>'buying_readiness',      'label'=>'Buying Ready', 'inverted'=>false],
    ['key'=>'dropoff_risk',          'label'=>'Drop-off Risk','inverted'=>true ],
    ['key'=>'reactivation_potential','label'=>'Reactivation', 'inverted'=>false],
  ];

  $signalDefs = [
    'intent_score' => [
      'Product page revisits within 24 hours',
      'Comparison & feature page deep-dives',
      'Pricing page engagement time',
      'Specification page time-on-page',
      'Targeted search queries detected',
      'Wishlist / save-for-later additions',
      'Demo or trial request submitted',
    ],
    'engagement_score' => [
      'Sessions per week (positive trend)',
      'App open frequency increase',
      'Feature interaction breadth (% used)',
      'Content consumed per session',
      'Notification click-through rate',
      'Scroll depth average across pages',
      'Return visit within 48 hours',
    ],
    'churn_score' => [
      'Login frequency declining week-over-week',
      'Average session length shrinking',
      'Support ticket volume increasing',
      'Negative sentiment detected in feedback',
      'Unsubscribed from email or push comms',
      'Days since last purchase increasing',
      'Cancellation or downgrade page visited',
    ],
    'loyalty_score' => [
      'Consecutive active weeks streak',
      'Referrals sent to other users',
      'Repeat purchase count (last 90 days)',
      'Loyalty rewards redeemed',
      'NPS score 8 or above submitted',
      'Community or forum participation',
    ],
    'trust_score' => [
      'Payment method saved to account',
      'Auto-renewal opted in',
      'Personal profile fully completed',
      'High-value transaction (>$500) completed',
      'Shared account access granted to others',
    ],
    'frustration_score' => [
      'Rage clicks detected in session',
      'Form abandonment count elevated',
      'Repeated encounters with same error',
      'Excessive back-navigation within a flow',
      'Live chat initiated mid-flow',
      'Negative NPS or low review submitted',
    ],
    'buying_readiness' => [
      'Cart has unpurchased items',
      'Pricing page visited 2+ times in session',
      'Coupon or discount code actively searched',
      'Checkout page reached but not completed',
      'Payment method page viewed',
      'Watched product demo or review video',
    ],
    'dropoff_risk' => [
      'Funnel exit pattern detected',
      'Long hesitation on action buttons (>10s)',
      'Error encountered during checkout',
      'Comparison loop without conversion',
      'Price sensitivity signals detected',
    ],
    'reactivation_potential' => [
      'Seasonal purchase pattern matched',
      'Browsed site but did not buy (30+ days ago)',
      'Opened re-engagement or win-back email',
      'Clicked retargeting advertisement',
      'Visited site after 30+ day absence',
    ],
  ];

  // JS data — map profile id → radar values + segment colours
  $profileDataForJs = $profiles->mapWithKeys(fn($p) => [
    $p->id => [
      'radar'             => $p->radarValues(),
      'segmentColor'      => $p->segmentColor(),
      'segmentColorAlpha' => $p->segmentColorAlpha(),
    ]
  ]);

  $firstProfile = $profiles->first();
@endphp

<div class="flex flex-col h-full overflow-hidden bg-gray-50">

{{-- ═══ HEADER ═══════════════════════════════════════════════════════════════ --}}
<header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
  <div>
    <h1 class="text-[16px] font-semibold text-gray-900">Behavioral Intelligence</h1>
    <p class="text-[11px] text-gray-500 mt-0.5">
      Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
      @if($dataSourceConnected ?? false)
          <span class="ml-2 inline-flex items-center gap-1 text-green-600 font-medium text-[10px]">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block animate-pulse"></span>Live EdTech Data
          </span>
      @else
          <span class="ml-2 inline-flex items-center gap-1 text-green-600 font-medium text-[10px]">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Live · ML Scoring Active
          </span>
      @endif
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
      <span class="font-medium text-gray-600">{{ $profiles->count() }} profiles</span>
    </span>
    <span class="flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5 text-pink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span class="font-medium text-gray-600">94.1% accuracy</span>
    </span>

    {{-- Avatar with dropdown --}}
    <div class="relative" data-dd-wrap>
      <button onclick="xpDd('ddL4')"
              class="w-8 h-8 rounded-full bg-cyan-500 flex items-center justify-center text-white text-xs font-bold focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-cyan-400">
        {{ $av }}
      </button>
      <div id="ddL4" data-dd-menu
           class="absolute right-0 top-10 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
           style="display:none">
        <div class="px-4 py-2 border-b border-gray-100">
          <p class="text-xs font-semibold text-gray-800 truncate">{{ $cn }}</p>
          <p class="text-[10px] text-gray-400 truncate">Client Account</p>
        </div>
        <a href="{{ route('client.dashboard') }}"
           class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
          <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          Profile Settings
        </a>
        <hr class="my-1 border-gray-100">
        <form method="POST" action="{{ route('client.logout') }}">
          @csrf
          <button type="submit"
                  class="w-full flex items-center gap-2 px-4 py-2 text-xs text-red-600 hover:bg-red-50">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Log Out
          </button>
        </form>
      </div>
    </div>
  </div>
</header>

{{-- ═══ BODY ════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-1 overflow-hidden">

  {{-- ─── LEFT: User Profile List ──────────────────────────────────────────── --}}
  <div style="width:256px;background:#fff;border-right:1px solid #e5e7eb;display:flex;flex-direction:column;overflow:hidden;flex-shrink:0">

    {{-- List header --}}
    <div style="padding:14px 16px;border-bottom:1px solid #f3f4f6">
      <p style="font-size:13px;font-weight:700;color:#111827">User Profiles</p>
      <p style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $profiles->count() }} behavioural records</p>
    </div>

    {{-- Search --}}
    <div style="padding:8px 12px;border-bottom:1px solid #f3f4f6">
      <input id="profile-search" type="text" placeholder="Search by name…"
             oninput="searchProfiles(this.value)"
             style="width:100%;font-size:12px;padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;outline:none;color:#374151;box-sizing:border-box" />
    </div>

    {{-- Segment filter --}}
    <div style="padding:8px 12px;border-bottom:1px solid #f3f4f6;display:flex;flex-wrap:wrap;gap:4px">
      @foreach(['all'=>'All','champion'=>'Champion','loyal'=>'Loyal','at_risk'=>'At Risk','dormant'=>'Dormant','new'=>'New'] as $seg=>$lbl)
      <button id="filter-{{ $seg }}" class="seg-pill {{ $seg==='all'?'active':'' }}"
              onclick="filterSegment('{{ $seg }}')">{{ $lbl }}</button>
      @endforeach
    </div>

    {{-- User list --}}
    <div style="flex:1;overflow-y:auto">
      @foreach($profiles as $p)
      @php
        $oc = $p->overall_score >= 70 ? '#10b981' : ($p->overall_score >= 45 ? '#f59e0b' : '#ef4444');
      @endphp
      <div id="user-item-{{ $p->id }}"
           class="user-item {{ $loop->first ? 'active' : '' }}"
           data-segment="{{ $p->segment }}"
           data-name="{{ strtolower($p->name) }}"
           onclick="selectProfile({{ $p->id }})">
        {{-- Avatar --}}
        <div style="width:36px;height:36px;border-radius:50%;background:{{ $p->segmentColor() }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <span style="font-size:12px;font-weight:700;color:#fff">{{ $p->initials() }}</span>
        </div>
        {{-- Info --}}
        <div style="flex:1;min-width:0">
          <p style="font-size:12px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $p->name }}</p>
          <p style="font-size:10px;color:{{ $p->segmentColor() }};font-weight:600;margin-top:1px">{{ $p->segmentLabel() }}</p>
        </div>
        {{-- Overall score --}}
        <span style="font-size:16px;font-weight:800;color:{{ $oc }};flex-shrink:0">{{ $p->overall_score }}</span>
      </div>
      @endforeach
    </div>

  </div>{{-- /left panel --}}

  {{-- ─── RIGHT: Profile Detail ────────────────────────────────────────────── --}}
  <div style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:0">

    @foreach($profiles as $profile)
    @php
      $oc = $profile->overall_score >= 70 ? '#10b981' : ($profile->overall_score >= 45 ? '#f59e0b' : '#ef4444');
      $lastActive = $getLastActive($profile);
    @endphp
    <div id="profile-{{ $profile->id }}" class="profile-detail"
         style="{{ $loop->first ? 'display:block' : 'display:none' }}">

      {{-- ── Profile Header Card ─────────────────────────────────────────── --}}
      <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px 24px;display:grid;grid-template-columns:auto 1fr auto auto;gap:20px;align-items:center;margin-bottom:16px">

        {{-- Avatar --}}
        <div style="width:60px;height:60px;border-radius:50%;background:{{ $profile->segmentColor() }};display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px;font-weight:700;color:#fff">
          {{ $profile->initials() }}
        </div>

        {{-- Name + meta --}}
        <div>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
            <h2 style="font-size:17px;font-weight:700;color:#111827">{{ $profile->name }}</h2>
            <span style="font-size:10px;font-weight:700;color:{{ $profile->segmentColor() }};background:{{ $profile->segmentBg() }};border:1px solid {{ $profile->segmentBorder() }};border-radius:20px;padding:2px 9px">{{ $profile->segmentLabel() }}</span>
          </div>
          <p style="font-size:12px;color:#6b7280;margin-bottom:4px">{{ $profile->email }}</p>
          <p style="font-size:11px;color:#9ca3af">
            Last active:
            @if($lastActive)
              {{ $lastActive->diffForHumans() }}
            @else
              Unknown
            @endif
          </p>
        </div>

        {{-- Overall score --}}
        <div style="text-align:center;padding:14px 20px;border:1px solid #f3f4f6;border-radius:12px;background:#fafafa">
          <p style="font-size:40px;font-weight:800;color:{{ $oc }};line-height:1">{{ $profile->overall_score }}</p>
          <p style="font-size:10px;color:#9ca3af;margin-top:3px;font-weight:500">Overall Score</p>
        </div>

        {{-- AI Recommendation --}}
        <div style="max-width:220px;padding:14px 16px;background:#f0f9ff;border-radius:12px;border:1px solid #bae6fd">
          <div style="display:flex;align-items:center;gap:5px;margin-bottom:6px">
            <svg width="13" height="13" fill="none" stroke="#0284c7" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            <span style="font-size:10px;font-weight:700;color:#0284c7;text-transform:uppercase;letter-spacing:.06em">AI Recommendation</span>
          </div>
          <p style="font-size:11px;color:#0f172a;line-height:1.5;margin-bottom:8px">{{ $profile->recommendation() }}</p>
          <div style="display:flex;align-items:center;gap:5px">
            <div style="width:5px;height:5px;border-radius:50%;background:#10b981;flex-shrink:0"></div>
            <p style="font-size:10px;color:#059669;font-weight:600">{{ $profile->predictedAction() }}</p>
          </div>
        </div>

      </div>

      {{-- ── 9 Score Chips ───────────────────────────────────────────────── --}}
      <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:4px;margin-bottom:16px">
        @foreach($scoreTypes as $idx => $st)
        @php
          $val = $profile->{$st['key']};
          $eff = $st['inverted'] ? (100 - $val) : $val;
          $col = $eff >= 70 ? '#10b981' : ($eff >= 40 ? '#f59e0b' : '#ef4444');
          $bg  = $eff >= 70 ? '#f0fdf4' : ($eff >= 40 ? '#fffbeb' : '#fef2f2');
          $brd = $eff >= 70 ? '#a7f3d0' : ($eff >= 40 ? '#fde68a' : '#fecaca');
        @endphp
        <div id="chip-{{ $profile->id }}-{{ $st['key'] }}"
             class="score-chip {{ $idx===0?'selected':'' }}"
             style="background:{{ $bg }};border-color:{{ $brd }}"
             onclick="selectChip({{ $profile->id }}, '{{ $st['key'] }}')">
          <p style="font-size:22px;font-weight:800;color:{{ $col }};line-height:1">{{ $val }}</p>
          <p style="font-size:10px;color:#6b7280;margin-top:3px;font-weight:500">{{ $st['label'] }}</p>
          @if($st['inverted'])<p style="font-size:9px;color:#9ca3af;margin-top:1px">↓ better</p>@endif
        </div>
        @endforeach
      </div>

      {{-- ── Main grid: Radar + Breakdown ───────────────────────────────── --}}
      <div style="display:grid;grid-template-columns:300px 1fr;gap:16px">

        {{-- Radar chart --}}
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px 20px 16px">
          <p style="font-size:12px;font-weight:600;color:#374151;margin-bottom:2px">Behavioural Radar</p>
          <p style="font-size:10px;color:#9ca3af;margin-bottom:14px">9-dimension ML profile</p>
          <div style="position:relative;height:260px">
            <canvas id="radar-{{ $profile->id }}"></canvas>
          </div>
        </div>

        {{-- Score bars + Signal panel --}}
        <div style="display:flex;flex-direction:column;gap:14px">

          {{-- Score bars --}}
          <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
            <p style="font-size:12px;font-weight:600;color:#374151;margin-bottom:14px">Score Breakdown</p>
            @foreach($scoreTypes as $st)
            @php
              $val    = $profile->{$st['key']};
              $eff    = $st['inverted'] ? (100 - $val) : $val;
              $col    = $eff >= 70 ? '#10b981' : ($eff >= 40 ? '#f59e0b' : '#ef4444');
              $barW   = max(3, $eff);
            @endphp
            <div style="margin-bottom:9px">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                <span style="font-size:12px;color:#374151">{{ $st['label'] }}@if($st['inverted'])<span style="font-size:9px;color:#9ca3af;margin-left:4px">(↓ better)</span>@endif</span>
                <span style="font-size:12px;font-weight:700;color:{{ $col }}">{{ $val }}</span>
              </div>
              <div class="bi-bar-wrap">
                <div class="bi-bar-fill" style="width:{{ $barW }}%;background:{{ $col }}"></div>
              </div>
            </div>
            @endforeach
          </div>

          {{-- Contributing Signals panel (JS-populated) --}}
          <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px;flex:1">
            <div id="signal-panel-{{ $profile->id }}">
              {{-- Populated on load / chip click --}}
            </div>
          </div>

        </div>{{-- /right col --}}
      </div>{{-- /main grid --}}

    </div>{{-- /profile detail --}}
    @endforeach

  </div>{{-- /right panel --}}
</div>{{-- /body --}}
</div>{{-- /page --}}
@endsection

@push('scripts')
<script>
// ── Data from PHP ───────────────────────────────────────────────────────────
const profileDataMap  = @json($profileDataForJs);
const signalDefs      = @json($signalDefs);
const scoreTypeMeta   = @json(collect($scoreTypes)->keyBy('key'));

// ── State ───────────────────────────────────────────────────────────────────
const radarCharts    = {};
const activeChips    = {};
let   currentProfile = {{ $firstProfile?->id ?? 'null' }};

// ── Radar init ──────────────────────────────────────────────────────────────
function initRadar(profileId) {
  if (radarCharts[profileId]) return;
  const canvas = document.getElementById('radar-' + profileId);
  if (!canvas || typeof Chart === 'undefined') return;
  const info  = profileDataMap[profileId];
  if (!info) return;
  radarCharts[profileId] = new Chart(canvas, {
    type: 'radar',
    data: {
      labels: ['Intent','Engagement','Loyalty','Trust','Buying Ready','Retention','Satisfaction','Stability','Reactivation'],
      datasets: [{
        data: info.radar,
        backgroundColor: info.segmentColorAlpha,
        borderColor: info.segmentColor,
        borderWidth: 2,
        pointBackgroundColor: info.segmentColor,
        pointRadius: 4,
        pointHoverRadius: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        r: {
          min: 0, max: 100,
          grid: { color: '#f3f4f6' },
          angleLines: { color: '#e5e7eb' },
          pointLabels: { font: { size: 10 }, color: '#6b7280' },
          ticks: { display: false, stepSize: 25 }
        }
      }
    }
  });
}

// ── Signal panel ────────────────────────────────────────────────────────────
function updateSignals(profileId, scoreKey) {
  const container = document.getElementById('signal-panel-' + profileId);
  if (!container) return;
  const signals = signalDefs[scoreKey] || [];
  const meta    = scoreTypeMeta[scoreKey] || {};
  const label   = meta.label || scoreKey;
  const inv     = meta.inverted ? ' <span style="font-size:9px;color:#9ca3af">(lower = better)</span>' : '';
  container.innerHTML =
    '<p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;margin-bottom:10px">' +
    'Contributing Signals — ' + label + inv + '</p>' +
    signals.map(s =>
      '<div class="sig-item">' +
        '<div style="width:6px;height:6px;border-radius:50%;background:#3b82f6;flex-shrink:0;margin-top:4px"></div>' +
        '<span style="font-size:12px;color:#374151;line-height:1.5">' + s + '</span>' +
      '</div>'
    ).join('');
}

// ── Chip selection ──────────────────────────────────────────────────────────
function selectChip(profileId, scoreKey) {
  // Remove selected from all chips of this profile
  document.querySelectorAll('[id^="chip-' + profileId + '-"]').forEach(c => c.classList.remove('selected'));
  const chip = document.getElementById('chip-' + profileId + '-' + scoreKey);
  if (chip) chip.classList.add('selected');
  activeChips[profileId] = scoreKey;
  updateSignals(profileId, scoreKey);
}

// ── Profile selection ───────────────────────────────────────────────────────
function selectProfile(profileId) {
  // Update left panel active state
  document.querySelectorAll('.user-item').forEach(u => u.classList.remove('active'));
  const item = document.getElementById('user-item-' + profileId);
  if (item) item.classList.add('active');
  // Show the right profile, hide others
  document.querySelectorAll('.profile-detail').forEach(p => p.style.display = 'none');
  const detail = document.getElementById('profile-' + profileId);
  if (detail) detail.style.display = 'block';
  currentProfile = profileId;
  // Init radar lazily
  initRadar(profileId);
  // Show default chip (intent or previously active)
  const defaultChip = activeChips[profileId] || 'intent_score';
  selectChip(profileId, defaultChip);
}

// ── Segment filter ──────────────────────────────────────────────────────────
function filterSegment(segment) {
  document.querySelectorAll('.seg-pill').forEach(p => p.classList.remove('active'));
  const pill = document.getElementById('filter-' + segment);
  if (pill) pill.classList.add('active');
  document.querySelectorAll('.user-item').forEach(item => {
    const match = segment === 'all' || item.dataset.segment === segment;
    item.style.display = match ? 'flex' : 'none';
  });
}

// ── Name search ─────────────────────────────────────────────────────────────
function searchProfiles(query) {
  const q = query.toLowerCase().trim();
  document.querySelectorAll('.user-item').forEach(item => {
    const name = item.dataset.name || '';
    item.style.display = (!q || name.includes(q)) ? 'flex' : 'none';
  });
}

// ── Init on page load ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  if (currentProfile) {
    initRadar(currentProfile);
    selectChip(currentProfile, 'intent_score');
  }
});
</script>
@endpush
