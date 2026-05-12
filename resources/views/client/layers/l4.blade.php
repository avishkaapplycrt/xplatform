@extends('layouts.platform')
@section('title', 'Behavioral Intelligence')

@push('styles')
<style>
  .score-card { cursor:pointer; border:1px solid #e5e7eb; border-radius:12px; background:#fff; padding:14px 16px; text-align:center; min-width:108px; flex-shrink:0; transition:all .15s; user-select:none; }
  .score-card:hover:not(.active) { border-color:#5eead4; }
  .score-card.active { background:linear-gradient(145deg,#34d399,#10b981); border-color:transparent; }
  .score-card .s-num { font-size:38px; font-weight:700; line-height:1; color:#9ca3af; }
  .score-card .s-lbl { font-size:11px; margin-top:5px; color:#9ca3af; }
  .score-card.active .s-num { color:#fff; }
  .score-card.active .s-lbl { color:rgba(255,255,255,.85); }
  .score-panel { display:none; }
  .score-panel.active { display:grid; }
  .bi-bar-wrap { width:100%; height:5px; background:#f3f4f6; border-radius:9999px; overflow:hidden; margin-top:7px; }
  .bi-bar-fill { height:5px; border-radius:9999px; transition:width .4s ease; }
  .sig-row { display:flex; align-items:center; gap:10px; padding:11px 0; border-bottom:1px solid #f3f4f6; }
  .sig-row:last-child { border-bottom:none; }
  .sig-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));

  $scores = [
    [
      'id'      => 'intent',
      'label'   => 'Intent Score',
      'score'   => 82,
      'dot'     => '#10b981',
      'signals' => [
        'product page revisits within 24h',
        'comparison page views',
        'pricing page views',
        'feature page time spent',
        'search query specificity score',
        'wishlist additions',
        'saved items count',
      ],
    ],
    [
      'id'      => 'engagement',
      'label'   => 'Engagement',
      'score'   => 91,
      'dot'     => '#3b82f6',
      'signals' => [
        'sessions per week trend',
        'app open frequency',
        'feature interaction rate',
        'content consumed per session',
        'notification click-through',
        'scroll depth average',
      ],
    ],
    [
      'id'      => 'churn',
      'label'   => 'Churn Score',
      'score'   => 24,
      'dot'     => '#3b82f6',
      'signals' => [
        'login frequency drop',
        'session length declining',
        'support ticket opened',
        'negative review posted',
        'unsubscribed from comms',
        'last purchase recency increasing',
      ],
    ],
    [
      'id'      => 'loyalty',
      'label'   => 'Loyalty',
      'score'   => 78,
      'dot'     => '#3b82f6',
      'signals' => [
        'consecutive weeks active',
        'referral sent',
        'repeat purchase count',
        'loyalty reward usage',
        'positive review submitted',
      ],
    ],
    [
      'id'      => 'trust',
      'label'   => 'Trust Score',
      'score'   => 86,
      'dot'     => '#3b82f6',
      'signals' => [
        'payment method saved',
        'auto renewal enabled',
        'personal data voluntarily added',
        'high-value transaction without friction',
      ],
    ],
    [
      'id'      => 'frustration',
      'label'   => 'Frustation',
      'score'   => 31,
      'dot'     => '#3b82f6',
      'signals' => [
        'payment method saved',
        'auto renewal enabled',
        'personal data voluntarily added',
        'high-value transaction without friction',
      ],
    ],
    [
      'id'      => 'buying',
      'label'   => 'Buying Ready',
      'score'   => 67,
      'dot'     => '#3b82f6',
      'signals' => [
        'cart filled + not checked out',
        'pricing page 2x in session',
        'coupon searched',
        'checkout page reached',
        'payment method viewed',
      ],
    ],
    [
      'id'      => 'dropoff',
      'label'   => 'Drop-off Risk',
      'score'   => 19,
      'dot'     => '#3b82f6',
      'signals' => [
        'funnel stop exited',
        'hesitation time spike',
        'error during checkout',
        'comparison not concluded',
      ],
    ],
    [
      'id'      => 'reactivation',
      'label'   => 'Reactivation',
      'score'   => 55,
      'dot'     => '#3b82f6',
      'signals' => [
        'seasonal pattern match',
        "browsed but didn't buy 30d",
        'opened win-back email',
        'clicked retargeting ad',
      ],
    ],
  ];

  $overview = [
    ['label'=>'Intent score',  'pct'=>82, 'color'=>'#10b981'],
    ['label'=>'Engagement',    'pct'=>24, 'color'=>'#3b82f6'],
    ['label'=>'Churn score',   'pct'=>22, 'color'=>'#8b5cf6'],
    ['label'=>'loyalty',       'pct'=>12, 'color'=>'#f59e0b'],
    ['label'=>'Trust score',   'pct'=>76, 'color'=>'#ef4444'],
    ['label'=>'Frustation',    'pct'=>76, 'color'=>'#8b5cf6'],
    ['label'=>'Buying ready',  'pct'=>76, 'color'=>'#f59e0b'],
    ['label'=>'drop-off risk', 'pct'=>76, 'color'=>'#3b82f6'],
    ['label'=>'reactivation',  'pct'=>76, 'color'=>'#10b981'],
  ];
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

{{-- HEADER --}}
<header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
  <div>
    <h1 class="text-[16px] font-semibold text-gray-900">Behavioral Intelligence</h1>
    <p class="text-[11px] text-gray-500 mt-0.5">
      Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
      <span class="ml-2 inline-flex items-center gap-1 text-green-600 font-medium text-[10px]">
        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Live
      </span>
    </p>
  </div>
  <div class="flex items-center gap-5 text-[11px] text-gray-500">
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
    <div class="w-8 h-8 rounded-full bg-cyan-500 flex items-center justify-center text-white text-xs font-bold">{{ $av }}</div>
  </div>
</header>

{{-- SCROLLABLE BODY --}}
<div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

  {{-- SCORE CARDS ROW --}}
  <div class="flex gap-3 overflow-x-auto pb-1">
    @foreach($scores as $s)
    <div id="card-{{ $s['id'] }}"
         class="score-card {{ $loop->first ? 'active' : '' }}"
         onclick="selectScore('{{ $s['id'] }}')">
      <div class="s-num">{{ $s['score'] }}</div>
      <div class="s-lbl">{{ $s['label'] }}</div>
    </div>
    @endforeach
  </div>

  {{-- SCORE PANELS --}}
  @foreach($scores as $s)
  <div id="panel-{{ $s['id'] }}"
       class="score-panel {{ $loop->first ? 'active' : '' }}"
       style="grid-template-columns:1fr 1fr;gap:20px;align-items:start">

    {{-- LEFT: INPUT MICRO-SIGNALS --}}
    <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:22px 24px">
      <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#10b981;margin-bottom:10px">Input Micro-Signals</p>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px">
        <h2 style="font-size:22px;font-weight:700;color:#111827;line-height:1.2">{{ $s['label'] }}</h2>
        <div style="text-align:right;line-height:1;flex-shrink:0;margin-left:12px">
          <span style="font-size:40px;font-weight:700;color:#3b82f6;line-height:1;display:block">{{ $s['score'] }}</span>
          <span style="font-size:11px;color:#9ca3af;display:block;margin-top:2px">/100</span>
        </div>
      </div>
      <div style="margin-top:8px">
        @foreach($s['signals'] as $sig)
        <div class="sig-row">
          <span class="sig-dot" style="background:{{ $s['dot'] }}"></span>
          <span style="font-size:13px;color:#374151">{{ $sig }}</span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- RIGHT: ALL 9 SCORE OVERVIEW --}}
    <div style="border:1px solid #e5e7eb;border-radius:12px;background:#fff;padding:22px 24px">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:22px">
        <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;display:inline-block"></span>
        <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#374151;white-space:nowrap">All 9 Score Overview</span>
        <span style="flex:1;height:1px;background:#bfdbfe;display:block"></span>
      </div>
      @foreach($overview as $ov)
      <div style="margin-bottom:18px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <span style="font-size:12px;color:#374151">{{ $ov['label'] }}</span>
          <span style="font-size:12px;font-weight:700;color:{{ $ov['color'] }}">{{ $ov['pct'] }}%</span>
        </div>
        <div class="bi-bar-wrap">
          <div class="bi-bar-fill" style="width:{{ $ov['pct'] }}%;background:{{ $ov['color'] }}"></div>
        </div>
      </div>
      @endforeach
    </div>

  </div>
  @endforeach

</div>{{-- /scrollable body --}}
</div>

@endsection

@push('scripts')
<script>
function selectScore(id) {
  document.querySelectorAll('.score-card').forEach(c => c.classList.remove('active'));
  document.getElementById('card-' + id).classList.add('active');
  document.querySelectorAll('.score-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('panel-' + id).classList.add('active');
}
</script>
@endpush
