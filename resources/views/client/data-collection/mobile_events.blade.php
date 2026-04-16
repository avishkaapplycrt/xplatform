@extends('layouts.platform')
@section('title', 'Data Collection')

@push('styles')
<style>
  .src { cursor:pointer; border:1px solid #e5e7eb; border-radius:10px; background:#fff; transition:all .15s; }
  .src:hover { border-color:#5eead4; }
  .src.active { border:2px solid #14b8a6; background:#f0fdfa; }
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
  .globe-panel { border:1px solid #edf0f2; border-radius:12px; background:#fff; padding:40px 20px; text-align:center; }
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
    ['id'=>'mobile-app',   'name'=>'Mobile App Events',  'value'=>'4,281', 'unit'=>'today'],
    ['id'=>'crm',          'name'=>'CRM Data',           'value'=>'3200',  'unit'=>'syncs'],
    ['id'=>'transactions', 'name'=>'Transactions',       'value'=>'1540',  'unit'=>'payments'],
    ['id'=>'chat',         'name'=>'Chat & Support',     'value'=>'840',   'unit'=>'messages'],
    ['id'=>'email',        'name'=>'Email Engagement',   'value'=>'1,900', 'unit'=>'opens'],
    ['id'=>'ads',          'name'=>'Ad Campaigns',       'value'=>'2,100', 'unit'=>'clicks'],
    ['id'=>'social',       'name'=>'Social Signals',     'value'=>'500',   'unit'=>'mentions'],
    ['id'=>'surveys',      'name'=>'Surveys & Feedback', 'value'=>'340',   'unit'=>'responses'],
    ['id'=>'loyalty',      'name'=>'Loyalty / Referral', 'value'=>'220',   'unit'=>'referrals'],
    ['id'=>'callcenter',   'name'=>'Call Center',        'value'=>'430',   'unit'=>'calls'],
    ['id'=>'pos',          'name'=>'POS / Offline',      'value'=>'310',   'unit'=>'purchases'],
  ];
  @endphp
  <div class="grid grid-cols-4 gap-3">
    @foreach($sources as $s)
    <div id="src-{{ $s['id'] }}" class="src {{ $s['id']==='website' ? 'active' : '' }}"
         onclick="selectSource('{{ $s['id'] }}')"
         style="padding:10px 12px;">
      <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-8 h-8 rounded-lg bg-gray-100 flex-shrink-0"></div>
          <div class="min-w-0">
            <p class="text-[11px] text-gray-500 truncate leading-none">{{ $s['name'] }}</p>
            <p class="mt-0.5 leading-none">
              <span class="text-[14px] font-semibold text-gray-800">{{ $s['value'] }}</span>
              <span class="text-[10px] text-gray-400"> / {{ $s['unit'] }}</span>
            </p>
          </div>
        </div>
        <span class="text-[10px] font-semibold text-emerald-500 flex-shrink-0">▲ 12%</span>
      </div>
    </div>
    @endforeach
  </div>

  {{-- TAB BAR + PANELS --}}
  <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

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
        ['call-events',       'Call Events'],
        ['ivr-path-analysis', 'IVR Path Analysis'],
        ['speech-sentiment',  'Speech Sentiment'],
      ] as [$tid,$tlabel])
      <button class="tab-btn {{ $tid==='call-events' ? 'active' : '' }}"
              onclick="selectTab('social','{{ $tid }}')">{{ $tlabel }}</button>
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
    <div id="panel-email-delivery-opens" class="panel p-4">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;height:100%">

        {{-- LEFT: Email Delivery & Open Funnel --}}
        <div class="panel-card">
          <div class="section-header" style="margin-bottom:18px">
            <div class="section-dot" style="background:#3b82f6"></div>
            <span class="section-title" style="color:#3b82f6">Email Delivery &amp; Open Funnel</span>
            <div class="section-line" style="background:#bfdbfe"></div>
          </div>
          @foreach([
            ['Sent',        18, '#5eead4'],
            ['Delivered',   24, '#60a5fa'],
            ['Open',        31, '#a78bfa'],
            ['Unique Open', 27, '#f59e0b'],
            ['Clicked',     27, '#ec4899'],
            ['Converted',   27, '#6366f1'],
          ] as [$label,$pct,$color])
          <div style="margin-bottom:16px">
            <div style="display:flex;align-items:center;margin-bottom:5px">
              <span style="font-size:12px;color:#374151;flex:1">{{ $label }}</span>
              <span style="font-size:11px;color:#9ca3af;margin-right:10px">8.7K</span>
              <span style="font-size:12px;font-weight:700;color:{{ $color }};min-width:32px;text-align:right">{{ $pct }}%</span>
            </div>
            <div class="bar-wrap" style="height:4px">
              <div class="bar-fill" style="width:{{ $pct * 3 }}%;background:{{ $color }};height:4px"></div>
            </div>
          </div>
          @endforeach
        </div>

        {{-- RIGHT: Stats Grid + Time to Open --}}
        <div style="display:flex;flex-direction:column;gap:14px">

          {{-- 2×2 Stats Cards --}}
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            @foreach([
              ['$2.8M','Mobile',  '#f59e0b'],
              ['$2.8M','Desktop', '#10b981'],
              ['$2.8M','Other',   '#8b5cf6'],
              ['$2.8M','Tablet',  '#8b5cf6'],
            ] as [$val,$lbl,$color])
            <div class="mini" style="padding:16px 12px">
              <p style="font-size:20px;font-weight:800;color:{{ $color }};letter-spacing:-0.5px">{{ $val }}</p>
              <p style="font-size:11px;color:#9ca3af;margin-top:5px">{{ $lbl }}</p>
            </div>
            @endforeach
          </div>

          {{-- Time to Open Distributions --}}
          <div class="panel-card" style="flex:1">
            <div class="section-header" style="margin-bottom:18px">
              <div class="section-dot" style="background:#3b82f6"></div>
              <span class="section-title" style="color:#3b82f6">Time to Open Distributions</span>
              <div class="section-line" style="background:#bfdbfe"></div>
            </div>
            @foreach([
              ['< 1 hour',    18, '#5eead4'],
              ['1 – 4 hour',  24, '#60a5fa'],
              ['4 – 24 hours',31, '#a78bfa'],
              ['> 24 hours',  27, '#f59e0b'],
            ] as [$label,$pct,$color])
            <div style="margin-bottom:20px">
              <div style="display:flex;align-items:center;margin-bottom:5px">
                <span style="font-size:12px;color:#374151;flex:1">{{ $label }}</span>
                <span style="font-size:11px;color:#9ca3af;margin-right:10px">8.7K</span>
                <span style="font-size:12px;font-weight:700;color:{{ $color }};min-width:32px;text-align:right">{{ $pct }}%</span>
              </div>
              <div class="bar-wrap" style="height:4px">
                <div class="bar-fill" style="width:{{ $pct * 3 }}%;background:{{ $color }};height:4px"></div>
              </div>
            </div>
            @endforeach
          </div>

        </div>{{-- /right col --}}
      </div>
    </div>

    {{-- Link Clicks --}}
    <div id="panel-email-link-clicks" class="panel p-6">
      <div style="text-align:center;padding:32px 0">
        <div style="width:72px;height:72px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
        </div>
        <h3 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:6px">Email – Link Clicks</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:28px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:500px;margin:0 auto">
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
        </div>
      </div>
    </div>

    {{-- Unsubscribe --}}
    <div id="panel-email-unsubscribe" class="panel p-6">
      <div style="text-align:center;padding:32px 0">
        <div style="width:72px;height:72px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
        </div>
        <h3 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:6px">Email – Unsubscribe</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:28px">Analytics pannel for this micro signal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:500px;margin:0 auto">
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#10b981">340k/day</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Events today</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#3b82f6">live</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Status</p></div>
          <div class="mini"><p style="font-size:18px;font-weight:700;color:#8b5cf6">97%</p><p style="font-size:11px;color:#9ca3af;margin-top:6px">Signal Quality</p></div>
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
  'social':       {tabs:'social',   firstTab:'call-events'},
  'surveys':      {tabs:'surveys',  firstTab:'nps-score'},
  'loyalty':      {tabs:'loyalty',  firstTab:'points-activity'},
  'callcenter':   {tabs:'generic',  firstTab:null, name:'Call Center', val:'430'},
  'pos':          {tabs:'generic',  firstTab:null, name:'POS / Offline', val:'310'},
};

let currentSrc = 'website';
let currentTab = 'scroll-depth';

function selectSource(id) {
  // update card highlight
  document.querySelectorAll('.src').forEach(el => el.classList.remove('active'));
  document.getElementById('src-' + id).classList.add('active');

  const meta = sourceMeta[id];
  currentSrc = id;

  // switch tab bars
  ['website','mobile-app','crm','transactions','chat','social','email','ads','surveys','loyalty','generic'].forEach(t => {
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
@endpush
