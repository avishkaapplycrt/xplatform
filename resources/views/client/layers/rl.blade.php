@extends('layouts.platform')
@section('title', 'RL Engine')

@push('styles')
<style>
/* ── RL Engine — light mode ── */
.rl-wrap { flex:1; overflow-y:auto; padding:20px 24px; display:flex; flex-direction:column; gap:16px; background:#f9fafb; }

/* metric cards */
.rl-metric { flex:1; min-width:120px; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:14px 16px; }
.rl-metric.hl { border-color:#c7d2fe; background:#eef2ff; }
.rl-m-lbl { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#9ca3af; margin-bottom:6px; }
.rl-m-val { font-size:26px; font-weight:700; line-height:1; letter-spacing:-.5px; }
.rl-m-sub { font-size:11px; color:#9ca3af; margin-top:4px; }

/* connection flow */
.rl-connect { display:flex; align-items:center; gap:6px; flex-wrap:wrap; padding:12px 14px;
  background:#f5f3ff; border:1px solid #ddd6fe; border-radius:10px; }
.rl-node { padding:5px 12px; border-radius:6px; font-size:11.5px; font-weight:500; }
.rn-orig { background:#fff; border:1px solid #e5e7eb; color:#374151; }
.rn-new  { background:#ede9fe; border:1px solid #c4b5fd; color:#6d28d9; }
.rn-arr  { color:#c4b5fd; font-size:14px; font-weight:700; }

/* segment buttons */
.rl-seg-btn { padding:5px 11px; border-radius:6px; border:1px solid #e5e7eb; background:#fff;
  font-size:11.5px; color:#6b7280; cursor:pointer; font-family:inherit; transition:all .15s; }
.rl-seg-btn:hover { border-color:#a5b4fc; color:#4338ca; }
.rl-seg-btn.on { border-color:#818cf8; background:#eef2ff; color:#4338ca; font-weight:600; }

/* action cards */
.rl-ac-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
.rl-ac { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:12px 10px;
  text-align:center; cursor:pointer; transition:all .2s; position:relative; }
.rl-ac:hover { background:#f9fafb; border-color:#c7d2fe; }
.rl-ac.winner { border-color:#818cf8; background:#eef2ff; }
.rl-ac.chosen { border-color:#10b981; background:#ecfdf5; }
.rl-ac-icon { font-size:20px; margin-bottom:4px; }
.rl-ac-name { font-size:11.5px; font-weight:500; color:#374151; margin-bottom:3px; }
.rl-ac-est  { font-size:10px; color:#9ca3af; font-family:'IBM Plex Mono',monospace; }
.rl-ac-bar  { height:3px; background:#e5e7eb; border-radius:2px; overflow:hidden; margin-top:6px; }
.rl-ac-fill { height:100%; background:linear-gradient(90deg,#818cf8,#3b82f6); border-radius:2px; transition:width .6s ease; }
.rl-ac-badge { position:absolute; top:-6px; right:-6px; font-size:8px; padding:2px 5px;
  border-radius:3px; background:#6366f1; color:#fff; font-weight:700; font-family:'IBM Plex Mono',monospace; }

/* controls */
.rl-run-btn { padding:8px 18px; border-radius:8px; background:linear-gradient(135deg,#818cf8,#3b82f6);
  color:#fff; border:none; font-weight:600; font-size:12px; cursor:pointer; font-family:inherit; transition:opacity .2s; }
.rl-run-btn:hover { opacity:.88; }
.rl-run-btn:disabled { opacity:.4; cursor:not-allowed; }
.rl-auto-btn { padding:8px 14px; border-radius:8px; background:#fff; border:1px solid #e5e7eb;
  color:#6b7280; font-size:12px; cursor:pointer; font-family:inherit; transition:all .2s; }
.rl-auto-btn:hover { border-color:#a5b4fc; color:#4338ca; }
.rl-auto-btn.running { border-color:#10b981; color:#059669; background:#ecfdf5; }
.rl-ep-stat { font-size:11px; color:#9ca3af; flex:1; font-family:'IBM Plex Mono',monospace; }

/* policy list */
.rl-policy-row { display:flex; align-items:center; gap:10px; padding:9px 12px;
  background:#fff; border:1px solid #e5e7eb; border-radius:8px; transition:background .2s; }
.rl-policy-row:hover { background:#f9fafb; }
.rl-pr-seg  { font-size:12.5px; color:#374151; flex:1; }
.rl-pr-act  { font-size:10px; font-family:'IBM Plex Mono',monospace; padding:2px 9px; border-radius:4px;
  background:#eef2ff; color:#6366f1; border:1px solid #c7d2fe; white-space:nowrap; }
.rl-pr-bar  { width:80px; height:4px; background:#e5e7eb; border-radius:2px; overflow:hidden; }
.rl-pr-fill { height:100%; background:linear-gradient(90deg,#818cf8,#3b82f6); border-radius:2px; transition:width .6s; }
.rl-pr-pct  { font-size:11px; color:#9ca3af; width:34px; text-align:right; font-family:'IBM Plex Mono',monospace; }

/* episode log */
.rl-log-item { display:flex; gap:8px; align-items:center; padding:6px 0;
  border-bottom:1px solid #f3f4f6; font-size:11.5px; }
.rl-log-item:last-child { border-bottom:none; }
.rl-li-ep   { font-family:'IBM Plex Mono',monospace; font-size:10px; color:#9ca3af; width:34px; flex-shrink:0; }
.rl-li-seg  { color:#6b7280; flex:1; }
.rl-li-act  { font-family:'IBM Plex Mono',monospace; font-size:10px; padding:2px 7px; border-radius:4px;
  background:#eef2ff; color:#6366f1; border:1px solid #c7d2fe; flex-shrink:0; }
.rl-li-r    { font-family:'IBM Plex Mono',monospace; font-size:11px; font-weight:600; width:30px; text-align:right; }

/* section label */
.rl-sec { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.09em;
  color:#9ca3af; display:flex; align-items:center; gap:8px; margin-bottom:8px; }
.rl-sec::after { content:''; flex:1; height:1px; background:#e5e7eb; }

/* info box */
.rl-info { padding:10px 14px; border-radius:8px; font-size:12px; line-height:1.6;
  background:#eef2ff; border:1px solid #c7d2fe; color:#4338ca; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $cn), 0, 2))));
@endphp

<div class="flex flex-col h-full overflow-hidden" style="background:#f9fafb">

{{-- ── HEADER ── --}}
<header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
  <div>
    <h1 class="text-[16px] font-semibold text-gray-900 flex items-center gap-2">
      RL Engine
      <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-200">Add-on Module</span>
      <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-violet-50 text-violet-600 border border-violet-200">Thompson Sampling</span>
    </h1>
    <p class="text-[11px] text-gray-500 mt-0.5">
      Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
      <span class="ml-2 inline-flex items-center gap-1 text-indigo-600 font-medium text-[10px]">
        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 inline-block"></span>Contextual Bandit · Active
      </span>
    </p>
  </div>
  <div class="flex items-center gap-4 text-[11px] text-gray-500">
    <a href="{{ route('client.dashboard') }}"
       class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
       title="Home">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
    </a>
    <span class="flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
      </svg>
      <span class="font-medium text-gray-600">+44% vs rules</span>
    </span>
    <span class="flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span class="font-medium text-gray-600">1,847 episodes today</span>
    </span>

    {{-- Avatar dropdown --}}
    <div class="relative" data-dd-wrap>
      <button onclick="xpDd('ddRl')"
              class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-400">
        {{ $av }}
      </button>
      <div id="ddRl" data-dd-menu
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
          <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-red-600 hover:bg-red-50">
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

{{-- ── SCROLLABLE BODY ── --}}
<div class="rl-wrap">

  {{-- Connection flow --}}
  <div class="rl-connect">
    <div class="rl-node rn-orig">L5 predictions</div><div class="rn-arr">→</div>
    <div class="rl-node rn-new">RL state</div><div class="rn-arr">→</div>
    <div class="rl-node rn-new">Agent picks action</div><div class="rn-arr">→</div>
    <div class="rl-node rn-orig">L6 executes</div><div class="rn-arr">→</div>
    <div class="rl-node rn-orig">L8 logs outcome</div><div class="rn-arr">→</div>
    <div class="rl-node rn-new">Reward updates policy</div>
  </div>

  {{-- Metric cards --}}
  <div style="display:flex;gap:12px;flex-wrap:wrap">
    <div class="rl-metric hl">
      <div class="rl-m-lbl">Total episodes run</div>
      <div class="rl-m-val" style="color:#6366f1">24,841</div>
    </div>
    <div class="rl-metric hl">
      <div class="rl-m-lbl">Cumulative reward</div>
      <div class="rl-m-val" style="color:#6366f1">14,203</div>
    </div>
    <div class="rl-metric hl">
      <div class="rl-m-lbl">vs rule baseline</div>
      <div class="rl-m-val" style="color:#10b981">+44%</div>
    </div>
    <div class="rl-metric hl">
      <div class="rl-m-lbl">Policy confidence</div>
      <div class="rl-m-val" style="color:#6366f1">High</div>
      <div class="rl-m-sub">After 24K+ episodes</div>
    </div>
  </div>

  {{-- Segment selector --}}
  <div>
    <div class="rl-sec">Active Segment</div>
    <div style="display:flex;gap:6px;flex-wrap:wrap" id="rlSegBtns">
      <button class="rl-seg-btn on" onclick="rlSelectSeg(0,this)">High Value · At Risk</button>
      <button class="rl-seg-btn" onclick="rlSelectSeg(1,this)">Mid Value · Disengaged</button>
      <button class="rl-seg-btn" onclick="rlSelectSeg(2,this)">Price Sensitive</button>
      <button class="rl-seg-btn" onclick="rlSelectSeg(3,this)">New Customer</button>
      <button class="rl-seg-btn" onclick="rlSelectSeg(4,this)">Loyalty Champion</button>
    </div>
  </div>

  {{-- Action cards --}}
  <div>
    <div class="rl-sec">Action Selection — Thompson Sampling Estimates</div>
    <div class="rl-ac-grid" id="rlActionCards">
      <div class="rl-ac" data-i="0">
        <div class="rl-ac-icon">🚫</div>
        <div class="rl-ac-name">Do Nothing</div>
        <div class="rl-ac-est" id="rle0">p̂ = 0.50</div>
        <div class="rl-ac-bar"><div class="rl-ac-fill" id="rlf0" style="width:50%"></div></div>
      </div>
      <div class="rl-ac" data-i="1">
        <div class="rl-ac-icon">📧</div>
        <div class="rl-ac-name">Send Email</div>
        <div class="rl-ac-est" id="rle1">p̂ = 0.50</div>
        <div class="rl-ac-bar"><div class="rl-ac-fill" id="rlf1" style="width:50%"></div></div>
      </div>
      <div class="rl-ac" data-i="2">
        <div class="rl-ac-icon">💰</div>
        <div class="rl-ac-name">Offer Discount</div>
        <div class="rl-ac-est" id="rle2">p̂ = 0.50</div>
        <div class="rl-ac-bar"><div class="rl-ac-fill" id="rlf2" style="width:50%"></div></div>
      </div>
      <div class="rl-ac" data-i="3">
        <div class="rl-ac-icon">📞</div>
        <div class="rl-ac-name">CSM Call</div>
        <div class="rl-ac-est" id="rle3">p̂ = 0.50</div>
        <div class="rl-ac-bar"><div class="rl-ac-fill" id="rlf3" style="width:50%"></div></div>
      </div>
      <div class="rl-ac" data-i="4">
        <div class="rl-ac-icon">🚀</div>
        <div class="rl-ac-name">Feature Upsell</div>
        <div class="rl-ac-est" id="rle4">p̂ = 0.50</div>
        <div class="rl-ac-bar"><div class="rl-ac-fill" id="rlf4" style="width:50%"></div></div>
      </div>
      <div class="rl-ac" data-i="5">
        <div class="rl-ac-icon">🔇</div>
        <div class="rl-ac-name">Ad Suppress</div>
        <div class="rl-ac-est" id="rle5">p̂ = 0.50</div>
        <div class="rl-ac-bar"><div class="rl-ac-fill" id="rlf5" style="width:50%"></div></div>
      </div>
    </div>
  </div>

  {{-- Run controls --}}
  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <button class="rl-run-btn" id="rlRunBtn" onclick="rlRunEp()">▶ Run 1 episode</button>
    <button class="rl-auto-btn" id="rlAutoBtn" onclick="rlToggleAuto()">Auto-run 100</button>
    <button class="rl-auto-btn" onclick="rlReset()">Reset</button>
    <span class="rl-ep-stat" id="rlEpStat">Episodes: 0 · Success rate: —</span>
  </div>

  {{-- Learned policy --}}
  <div>
    <div class="rl-sec">Learned Policy — Best Action per Segment</div>
    <div style="display:flex;flex-direction:column;gap:5px" id="rlPolicyList">
      <div class="rl-policy-row"><div class="rl-pr-seg">High Value · At Risk</div><span class="rl-pr-act">Learning...</span><div class="rl-pr-bar"><div class="rl-pr-fill" style="width:0%"></div></div><div class="rl-pr-pct">—</div></div>
      <div class="rl-policy-row"><div class="rl-pr-seg">Mid Value · Disengaged</div><span class="rl-pr-act">Learning...</span><div class="rl-pr-bar"><div class="rl-pr-fill" style="width:0%"></div></div><div class="rl-pr-pct">—</div></div>
      <div class="rl-policy-row"><div class="rl-pr-seg">Price Sensitive</div><span class="rl-pr-act">Learning...</span><div class="rl-pr-bar"><div class="rl-pr-fill" style="width:0%"></div></div><div class="rl-pr-pct">—</div></div>
      <div class="rl-policy-row"><div class="rl-pr-seg">New Customer</div><span class="rl-pr-act">Learning...</span><div class="rl-pr-bar"><div class="rl-pr-fill" style="width:0%"></div></div><div class="rl-pr-pct">—</div></div>
      <div class="rl-policy-row"><div class="rl-pr-seg">Loyalty Champion</div><span class="rl-pr-act">Learning...</span><div class="rl-pr-bar"><div class="rl-pr-fill" style="width:0%"></div></div><div class="rl-pr-pct">—</div></div>
    </div>
  </div>

  {{-- Episode log --}}
  <div>
    <div class="rl-sec">Episode Log</div>
    <div id="rlEpLog" style="max-height:160px;overflow-y:auto;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:10px 14px;">
      <div style="text-align:center;padding:18px 0;font-size:12px;color:#9ca3af">Run episodes to see the log</div>
    </div>
  </div>

  {{-- Learning curve --}}
  <div>
    <div class="rl-sec">Learning Curve — Cumulative Reward</div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px 18px;">
      <canvas id="rlLcCanvas" style="width:100%;height:100px;display:block"></canvas>
      <div style="display:flex;gap:16px;margin-top:8px">
        <div style="display:flex;align-items:center;gap:5px;font-size:10px;color:#9ca3af">
          <div style="width:14px;height:2px;background:#818cf8;border-radius:1px"></div>RL Agent
        </div>
        <div style="display:flex;align-items:center;gap:5px;font-size:10px;color:#9ca3af">
          <div style="width:14px;height:2px;background:#d1d5db;border-radius:1px;border-top:1px dashed #9ca3af"></div>Rule baseline
        </div>
      </div>
    </div>
  </div>

  {{-- Info note --}}
  <div class="rl-info">
    Every outcome automatically feeds the RL Engine as a reward signal — no manual steps. Positive outcomes
    increment the Beta distribution alpha, negative outcomes increment beta, sharpening the agent's policy
    for each segment. This module enhances <strong>L6 Decision &amp; Actions</strong> by replacing static
    threshold rules with a continuously learned policy.
  </div>

</div>{{-- /rl-wrap --}}
</div>{{-- /flex col --}}
@endsection

@push('scripts')
<script>
// ── Thompson Sampling ──────────────────────────────────────────────────────
const RL_TRUE_P = [
  [0.10,0.25,0.45,0.72,0.15,0.08],
  [0.08,0.35,0.38,0.30,0.42,0.12],
  [0.06,0.20,0.65,0.18,0.10,0.05],
  [0.05,0.40,0.20,0.25,0.55,0.10],
  [0.20,0.30,0.15,0.45,0.60,0.05]
];
const RL_SEG_NAMES = ['High Value · At Risk','Mid Value · Disengaged','Price Sensitive','New Customer','Loyalty Champion'];
const RL_ACT_NAMES = ['Do Nothing','Send Email','Offer Discount','CSM Call','Feature Upsell','Ad Suppress'];

let rlP = Array.from({length:5}, () => Array.from({length:6}, () => ({a:1,b:1})));
let rlCurSeg = 0, rlTotalEp = 0, rlCumR = 0, rlAutoIv = null;
let rlHist = [0], rlBlHist = [0];

function rlRandn(){let u=0,v=0;while(!u)u=Math.random();while(!v)v=Math.random();return Math.sqrt(-2*Math.log(u))*Math.cos(2*Math.PI*v);}
function rlGamma(s){if(s<1)return rlGamma(1+s)*Math.pow(Math.random(),1/s);const d=s-1/3,c=1/Math.sqrt(9*d);for(;;){let x,v;do{x=rlRandn();v=1+c*x;}while(v<=0);v=v*v*v;const u=Math.random();if(u<1-0.0331*x*x*x*x)return d*v;if(Math.log(u)<0.5*x*x+d*(1-v+Math.log(v)))return d*v;}}
function rlBeta(a,b){const g1=rlGamma(a),g2=rlGamma(b);return g1/(g1+g2);}
function rlMean(s,a){const{a:al,b:bl}=rlP[s][a];return al/(al+bl);}

function rlSelectAction(s){
  const samples=rlP[s].map(p=>rlBeta(p.a,p.b));
  return samples.indexOf(Math.max(...samples));
}

function rlSelectSeg(i,btn){
  rlCurSeg=i;
  document.querySelectorAll('.rl-seg-btn').forEach(b=>b.classList.remove('on'));
  btn.classList.add('on');
  rlRefreshCards(-1,-1);
}

function rlRunEp(){
  const action=rlSelectAction(rlCurSeg);
  const reward=Math.random()<RL_TRUE_P[rlCurSeg][action]?1:0;
  const baseR =Math.random()<RL_TRUE_P[rlCurSeg][2]?1:0;
  if(reward)rlP[rlCurSeg][action].a++;else rlP[rlCurSeg][action].b++;
  rlTotalEp++;rlCumR+=reward;
  rlHist.push(rlHist[rlHist.length-1]+reward);
  rlBlHist.push(rlBlHist[rlBlHist.length-1]+baseR);
  rlRefreshCards(action,reward);
  rlAddLog(rlTotalEp,rlCurSeg,action,reward);
  rlUpdatePolicy();
  rlDrawChart();
  document.getElementById('rlEpStat').textContent=`Episodes: ${rlTotalEp} · Success rate: ${((rlCumR/rlTotalEp)*100).toFixed(0)}%`;
}

function rlRefreshCards(chosenAct,reward){
  const cards=document.querySelectorAll('.rl-ac');
  let bestEst=-1,bestIdx=0;
  cards.forEach((c,i)=>{
    c.classList.remove('winner','chosen');
    const old=c.querySelector('.rl-ac-badge');if(old)old.remove();
    const est=rlMean(rlCurSeg,i);
    document.getElementById('rle'+i).textContent='p̂ = '+est.toFixed(2);
    document.getElementById('rlf'+i).style.width=(est*100).toFixed(0)+'%';
    if(est>bestEst){bestEst=est;bestIdx=i;}
  });
  cards[bestIdx].classList.add('winner');
  const badge=document.createElement('div');badge.className='rl-ac-badge';badge.textContent='Best';
  cards[bestIdx].appendChild(badge);
  if(chosenAct>=0)cards[chosenAct].classList.add('chosen');
}

function rlUpdatePolicy(){
  const rows=document.querySelectorAll('#rlPolicyList .rl-policy-row');
  rows.forEach((row,s)=>{
    const ests=Array.from({length:6},(_,a)=>rlMean(s,a));
    const best=ests.indexOf(Math.max(...ests));
    const pct=(ests[best]*100).toFixed(0);
    const total=rlP[s][best].a+rlP[s][best].b-2;
    row.querySelector('.rl-pr-act').textContent=total>0?RL_ACT_NAMES[best]:'Learning...';
    row.querySelector('.rl-pr-fill').style.width=pct+'%';
    row.querySelector('.rl-pr-pct').textContent=total>0?pct+'%':'—';
  });
}

function rlAddLog(ep,seg,act,reward){
  const log=document.getElementById('rlEpLog');
  const placeholder=log.querySelector('div[style]');if(placeholder)placeholder.remove();
  const row=document.createElement('div');row.className='rl-log-item';
  row.innerHTML=`<span class="rl-li-ep">#${ep}</span><span class="rl-li-seg">${RL_SEG_NAMES[seg].split(' · ')[0]}</span><span class="rl-li-act">${RL_ACT_NAMES[act]}</span><span class="rl-li-r" style="color:${reward?'#10b981':'#f43f5e'}">${reward?'+1 ✓':'0 ✗'}</span>`;
  log.insertBefore(row,log.firstChild);
  if(log.children.length>60)log.removeChild(log.lastChild);
}

function rlDrawChart(){
  const canvas=document.getElementById('rlLcCanvas');if(!canvas)return;
  const dpr=window.devicePixelRatio||1;
  const rect=canvas.getBoundingClientRect();
  canvas.width=rect.width*dpr;canvas.height=100*dpr;
  const ctx=canvas.getContext('2d');ctx.scale(dpr,dpr);
  const W=rect.width,H=100;
  ctx.clearRect(0,0,W,H);
  const maxR=Math.max(...rlHist,...rlBlHist,1);
  const pts=rlHist.length;if(pts<2)return;
  function toX(i){return(i/(pts-1))*(W-20)+10;}
  function toY(v){return H-8-(v/maxR)*(H-16);}
  ctx.strokeStyle='rgba(0,0,0,.04)';ctx.lineWidth=1;
  [.25,.5,.75,1].forEach(p=>{ctx.beginPath();ctx.moveTo(10,toY(maxR*p));ctx.lineTo(W-10,toY(maxR*p));ctx.stroke();});
  ctx.setLineDash([4,4]);ctx.strokeStyle='#d1d5db';ctx.lineWidth=1.5;
  ctx.beginPath();rlBlHist.forEach((v,i)=>i===0?ctx.moveTo(toX(i),toY(v)):ctx.lineTo(toX(i),toY(v)));ctx.stroke();
  ctx.setLineDash([]);
  ctx.beginPath();ctx.strokeStyle='#818cf8';ctx.lineWidth=2;
  rlHist.forEach((v,i)=>i===0?ctx.moveTo(toX(i),toY(v)):ctx.lineTo(toX(i),toY(v)));ctx.stroke();
  if(pts>1){ctx.beginPath();ctx.arc(toX(pts-1),toY(rlHist[pts-1]),4,0,Math.PI*2);ctx.fillStyle='#818cf8';ctx.fill();}
}

function rlToggleAuto(){
  const btn=document.getElementById('rlAutoBtn');
  if(rlAutoIv){
    clearInterval(rlAutoIv);rlAutoIv=null;
    btn.textContent='Auto-run 100';btn.classList.remove('running');
    document.getElementById('rlRunBtn').disabled=false;
  }else{
    btn.textContent='⏸ Stop';btn.classList.add('running');
    document.getElementById('rlRunBtn').disabled=true;
    let count=0;
    rlAutoIv=setInterval(()=>{
      rlCurSeg=count%5;
      document.querySelectorAll('.rl-seg-btn').forEach((b,i)=>b.classList.toggle('on',i===rlCurSeg));
      rlRunEp();count++;
      if(count>=100)rlToggleAuto();
    },80);
  }
}

function rlReset(){
  if(rlAutoIv){clearInterval(rlAutoIv);rlAutoIv=null;}
  document.getElementById('rlAutoBtn').textContent='Auto-run 100';
  document.getElementById('rlAutoBtn').classList.remove('running');
  document.getElementById('rlRunBtn').disabled=false;
  rlP=Array.from({length:5},()=>Array.from({length:6},()=>({a:1,b:1})));
  rlTotalEp=0;rlCumR=0;rlHist=[0];rlBlHist=[0];
  document.getElementById('rlEpStat').textContent='Episodes: 0 · Success rate: —';
  document.getElementById('rlEpLog').innerHTML='<div style="text-align:center;padding:18px 0;font-size:12px;color:#9ca3af">Run episodes to see the log</div>';
  for(let i=0;i<6;i++){document.getElementById('rle'+i).textContent='p̂ = 0.50';document.getElementById('rlf'+i).style.width='50%';}
  document.querySelectorAll('.rl-ac').forEach(c=>{c.classList.remove('winner','chosen');const b=c.querySelector('.rl-ac-badge');if(b)b.remove();});
  document.querySelectorAll('#rlPolicyList .rl-pr-act').forEach(el=>el.textContent='Learning...');
  document.querySelectorAll('#rlPolicyList .rl-pr-fill').forEach(el=>el.style.width='0%');
  document.querySelectorAll('#rlPolicyList .rl-pr-pct').forEach(el=>el.textContent='—');
  rlDrawChart();
}

window.addEventListener('resize',rlDrawChart);
rlDrawChart();
</script>
@endpush
