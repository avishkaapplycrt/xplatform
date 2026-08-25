<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Platform Architecture &ndash; X Platforms</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;
  --blue:#4f8fff;--blue2:#2563eb;
  --cyan:#38bdf8;--violet:#818cf8;--emerald:#34d399;--amber:#fbbf24;--rose:#f472b6;--orange:#fb923c;
  --white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;
  --brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.15);
  --f1:'Outfit',system-ui,sans-serif;--f2:'Source Serif 4',Georgia,serif;--fm:'IBM Plex Mono',monospace;
  --ease:cubic-bezier(.16,1,.3,1);--mw:1200px;
}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}

/* NAV */
.nav{position:fixed;top:0;width:100%;z-index:100;border-bottom:1px solid var(--brd)}
.nav-bg{position:absolute;inset:0;background:rgba(5,7,14,.75);backdrop-filter:blur(40px)}
.nav-in{position:relative;max-width:var(--mw);margin:0 auto;padding:0 40px;height:64px;display:flex;align-items:center;justify-content:space-between}
.logo{display:flex;align-items:center;gap:11px;font-weight:700;font-size:15px;letter-spacing:-.3px}
.logo-m{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--blue),var(--violet));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff;box-shadow:0 0 20px rgba(79,143,255,.25)}
.nav-l{display:flex;align-items:center;gap:32px;list-style:none}
.nav-l a{font-size:13.5px;font-weight:450;color:var(--g200);transition:color .25s}
.nav-l a:hover{color:var(--white)}
.nav-cta{padding:8px 22px;background:var(--blue);color:#fff!important;border-radius:8px;font-weight:600!important;font-size:13px!important;box-shadow:0 0 24px rgba(79,143,255,.25);transition:all .25s;cursor:pointer;border:none;font-family:var(--f1)}
.nav-cta:hover{box-shadow:0 0 36px rgba(79,143,255,.4)}
.nav-login{display:inline-flex;align-items:center;gap:7px;padding:8px 18px;border:1px solid var(--g500);color:var(--g200)!important;border-radius:8px;font-weight:500!important;font-size:13px!important;transition:all .25s;background:transparent;cursor:pointer;font-family:var(--f1)}
.nav-login:hover{border-color:var(--brd2);color:var(--white)!important;background:rgba(79,143,255,.05)}
.nav-ham{display:none;flex-direction:column;gap:5px;background:none;border:none;cursor:pointer;padding:8px;position:relative;z-index:102}
.nav-ham span{display:block;width:22px;height:2px;background:var(--g200);border-radius:2px;transition:all .3s}
.nav-ham.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
.nav-ham.open span:nth-child(2){opacity:0}
.nav-ham.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
.nav-mob{display:none;position:fixed;inset:0;z-index:99;background:rgba(5,7,14,.97);backdrop-filter:blur(20px);flex-direction:column;align-items:center;justify-content:center;overflow-y:auto}
.nav-mob.open{display:flex}.nav-mob-inner{width:100%;max-width:340px;padding:24px}
.nav-mob-link{display:block;font-size:18px;font-weight:500;color:var(--g200);padding:16px 0;border-bottom:1px solid var(--brd);transition:color .2s;text-align:center}
.nav-mob-link:first-child{border-top:1px solid var(--brd)}.nav-mob-link:hover{color:var(--white)}
.nav-mob-cta{display:block;text-align:center;margin-top:28px;padding:14px 0;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;border-radius:10px;font-weight:600;font-size:15px}
.nav-mob-cta:hover{opacity:.9;color:#fff}
.nav-mob-close{position:absolute;top:20px;right:20px;background:none;border:none;cursor:pointer;color:var(--g300);font-size:32px;line-height:1;z-index:100;width:44px;height:44px;display:flex;align-items:center;justify-content:center}
@media(max-width:640px){.nav-ham{display:flex}}

/* PAGE */
.page{padding:120px 40px 0;max-width:var(--mw);margin:0 auto}

/* BREADCRUMB */
.bc{font-size:12px;color:var(--g400);margin-bottom:40px;display:flex;align-items:center;gap:8px}
.bc a{color:var(--g400);transition:color .2s}.bc a:hover{color:var(--white)}

/* HERO */
.hero{text-align:center;padding-bottom:80px}
.hero-label{display:inline-flex;align-items:center;gap:10px;font-family:var(--fm);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--g400);margin-bottom:24px}
.hero-label::before,.hero-label::after{content:'';display:block;width:24px;height:1px;background:var(--g500)}
.hero-h{font-size:clamp(36px,5.5vw,60px);font-weight:800;letter-spacing:-2px;line-height:1.1;margin-bottom:22px}
.hero-h .hl{color:var(--blue)}
.hero-sub{font-size:16px;color:var(--g300);line-height:1.7;max-width:580px;margin:0 auto 36px}
.btn-row{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.btn-fill{padding:13px 32px;background:var(--blue);color:#fff;border:none;border-radius:9px;font-family:var(--f1);font-size:14px;font-weight:600;cursor:pointer;transition:all .25s;box-shadow:0 0 24px rgba(79,143,255,.25)}
.btn-fill:hover{box-shadow:0 0 40px rgba(79,143,255,.4)}
.btn-out{padding:13px 32px;background:transparent;color:var(--g100);border:1px solid var(--g500);border-radius:9px;font-family:var(--f1);font-size:14px;font-weight:500;cursor:pointer;transition:all .25s}
.btn-out:hover{border-color:var(--brd2);color:var(--white)}

/* LAYERS SECTION */
.layers-wrap{max-width:var(--mw);margin:0 auto;padding:0 40px 100px}
.layers-grid{display:grid;grid-template-columns:52px 1fr;gap:0}

/* LEFT RAIL */
.lr{display:flex;flex-direction:column;align-items:center;padding-top:10px}
.lr-dot{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:11px;font-weight:500;flex-shrink:0;color:#fff;margin-bottom:0;position:relative;z-index:1}
.lr-line{width:1px;flex:1;min-height:24px}

/* LAYER ROWS */
.layer-rows{display:flex;flex-direction:column;gap:0}
.lrow{display:grid;grid-template-columns:1fr 180px;gap:24px;padding:20px 0 20px 24px;border-bottom:1px solid var(--brd);align-items:start}
.lrow:last-child{border-bottom:none}
.lrow-left{}
.lrow-name{font-size:16px;font-weight:700;margin-bottom:10px}
.lrow-desc{font-size:13.5px;color:var(--g300);line-height:1.7;margin-bottom:14px}
.lrow-tags{display:flex;flex-wrap:wrap;gap:6px}
.ltag{font-family:var(--fm);font-size:10px;padding:4px 10px;border-radius:5px;border:1px solid;letter-spacing:.2px}
.lrow-stats{display:flex;flex-direction:column;gap:16px;padding-top:4px}
.lstat-val{font-size:22px;font-weight:800;letter-spacing:-0.5px;line-height:1}
.lstat-lbl{font-family:var(--fm);font-size:9px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g400);margin-top:3px}

/* PER-LAYER COLOURS */
.c-blue  {color:var(--blue)}
.c-violet{color:var(--violet)}
.c-rose  {color:var(--rose)}
.c-amber {color:var(--amber)}
.c-cyan  {color:var(--cyan)}
.c-green {color:var(--emerald)}
.c-orange{color:var(--orange)}
.c-blue2 {color:var(--blue)}

.dot-blue  {background:rgba(79,143,255,.15);border:1px solid rgba(79,143,255,.35)}
.dot-violet{background:rgba(129,140,248,.15);border:1px solid rgba(129,140,248,.35)}
.dot-rose  {background:rgba(244,114,182,.15);border:1px solid rgba(244,114,182,.35)}
.dot-amber {background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.3)}
.dot-cyan  {background:rgba(56,189,248,.12);border:1px solid rgba(56,189,248,.3)}
.dot-green {background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3)}
.dot-orange{background:rgba(251,146,60,.12);border:1px solid rgba(251,146,60,.3)}

.line-blue  {background:linear-gradient(to bottom,rgba(79,143,255,.25),rgba(79,143,255,.06))}
.line-violet{background:linear-gradient(to bottom,rgba(129,140,248,.25),rgba(129,140,248,.06))}
.line-rose  {background:linear-gradient(to bottom,rgba(244,114,182,.25),rgba(244,114,182,.06))}
.line-amber {background:linear-gradient(to bottom,rgba(251,191,36,.2),rgba(251,191,36,.05))}
.line-cyan  {background:linear-gradient(to bottom,rgba(56,189,248,.2),rgba(56,189,248,.05))}
.line-green {background:linear-gradient(to bottom,rgba(52,211,153,.18),rgba(52,211,153,.04))}
.line-orange{background:linear-gradient(to bottom,rgba(251,146,60,.2),rgba(251,146,60,.05))}
.line-end   {background:linear-gradient(to bottom,rgba(79,143,255,.2),transparent)}

.tag-blue  {background:rgba(79,143,255,.07);border-color:rgba(79,143,255,.2);color:var(--blue)}
.tag-violet{background:rgba(129,140,248,.07);border-color:rgba(129,140,248,.2);color:var(--violet)}
.tag-rose  {background:rgba(244,114,182,.07);border-color:rgba(244,114,182,.2);color:var(--rose)}
.tag-amber {background:rgba(251,191,36,.07);border-color:rgba(251,191,36,.2);color:var(--amber)}
.tag-cyan  {background:rgba(56,189,248,.07);border-color:rgba(56,189,248,.2);color:var(--cyan)}
.tag-green {background:rgba(52,211,153,.07);border-color:rgba(52,211,153,.2);color:var(--emerald)}
.tag-orange{background:rgba(251,146,60,.07);border-color:rgba(251,146,60,.2);color:var(--orange)}

/* PERFORMANCE SECTION */
.perf{background:var(--bg2);border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);padding:80px 40px}
.perf-in{max-width:var(--mw);margin:0 auto}
.sec-label{display:flex;align-items:center;justify-content:center;gap:12px;font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g400);margin-bottom:20px}
.sec-label::before,.sec-label::after{content:'';display:block;width:32px;height:1px;background:var(--g500)}
.sec-h{font-size:clamp(28px,4vw,42px);font-weight:800;letter-spacing:-1px;text-align:center;margin-bottom:56px}
.perf-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.perf-card{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:28px 24px;transition:border-color .25s}
.perf-card:hover{border-color:var(--brd2)}
.perf-val{font-size:28px;font-weight:800;letter-spacing:-1px;margin-bottom:6px}
.perf-key{font-family:var(--fm);font-size:9px;letter-spacing:2px;text-transform:uppercase;color:var(--g400);margin-bottom:10px}
.perf-desc{font-size:12.5px;color:var(--g300);line-height:1.55}

/* CTA SECTION */
.cta{text-align:center;padding:100px 40px}
.cta-h{font-size:clamp(32px,5vw,52px);font-weight:800;letter-spacing:-1.5px;margin-bottom:18px}
.cta-h .hl{color:var(--cyan)}
.cta-sub{font-size:16px;color:var(--g300);max-width:480px;margin:0 auto 36px;line-height:1.65}

/* FOOTER */
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2)}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:240px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}
</style>
</head>
<body>

<!-- NAV -->
<nav class="nav"><div class="nav-bg"></div><div class="nav-in">
  <!-- <a href="{{ url('/') }}" class="logo"><span class="logo-m">X</span> Platforms</a> -->
  <a href="{{ url('/') }}" class="logo"><img src="{{ asset('images/xplatforms_logo.jpeg') }}" alt="X Platforms" style="height:32px;width:auto;display:block"></a>
  <ul class="nav-l">
    <li><a href="{{ url('/') }}">Home</a></li>
    <li><a href="{{ route('industries') }}">Industries</a></li>
    <li><a href="{{ route('simulator') }}">Simulator</a></li>
    <li><a href="{{ route('pricing') }}">Pricing</a></li>
    <li style="display:flex;align-items:center;gap:10px">
      <!-- <a href="{{ route('client.login') }}" class="nav-login"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg> Log in</a> -->
      <a href="{{ route('book-demo') }}" class="nav-cta">Book a Demo</a>
    </li>
  </ul>
  <button class="nav-ham" id="navHam" onclick="toggleNav()" aria-label="Open menu"><span></span><span></span><span></span></button>
</div></nav>
<div class="nav-mob" id="navMob">
  <button class="nav-mob-close" onclick="toggleNav()" aria-label="Close">&#215;</button>
  <div class="nav-mob-inner">
    <a class="nav-mob-link" href="{{ url('/') }}">Home</a>
    <a class="nav-mob-link" href="{{ route('industries') }}">Industries</a>
    <a class="nav-mob-link" href="{{ route('simulator') }}">Simulator</a>
    <a class="nav-mob-link" href="{{ route('pricing') }}">Pricing</a>
    <a class="nav-mob-link" href="{{ route('client.login') }}">Log in</a>
    <a class="nav-mob-cta" href="{{ route('book-demo') }}">Book a Demo</a>
  </div>
</div>
<script>function toggleNav(){var m=document.getElementById('navMob'),h=document.getElementById('navHam');m.classList.toggle('open');h.classList.toggle('open');document.body.style.overflow=m.classList.contains('open')?'hidden':''}</script>

<!-- HERO -->
<div class="page">
  <div class="bc">
    <a href="{{ route('home') }}">Home</a>
    <span style="color:var(--g600)">/</span>
    <span style="color:var(--g200)">Platform Architecture</span>
  </div>
  <div class="hero">
    <div class="hero-label">8-Layer AI Engine</div>
    <h1 class="hero-h">The architecture that turns<br><span class="hl">every signal into intelligence</span></h1>
    <p class="hero-sub">X Platforms is built on eight interconnected AI layers, each purpose-built to transform your raw customer data into predictions and actions — in under 200ms.</p>
    <div class="btn-row">
      <a href="{{ route('simulator') }}" class="btn-fill">See It Work Live</a>
      <a href="{{ route('book-demo') }}" class="btn-out">Book a Demo</a>
    </div>
  </div>
</div>

<!-- LAYERS -->
<div class="layers-wrap">
  <div class="layers-grid">

    <!-- LEFT RAIL -->
    <div class="lr">
      <div class="lr-dot dot-blue" style="color:var(--blue)">L1</div>
      <div class="lr-line line-blue"></div>
      <div class="lr-dot dot-violet" style="color:var(--violet)">L2</div>
      <div class="lr-line line-violet"></div>
      <div class="lr-dot dot-rose" style="color:var(--rose)">L3</div>
      <div class="lr-line line-rose"></div>
      <div class="lr-dot dot-amber" style="color:var(--amber)">L4</div>
      <div class="lr-line line-amber"></div>
      <div class="lr-dot dot-cyan" style="color:var(--cyan)">L5</div>
      <div class="lr-line line-cyan"></div>
      <div class="lr-dot dot-green" style="color:var(--emerald)">L6</div>
      <div class="lr-line line-green"></div>
      <div class="lr-dot dot-orange" style="color:var(--orange)">L7</div>
      <div class="lr-line line-orange"></div>
      <div class="lr-dot dot-blue" style="color:var(--blue)">L8</div>
      <div class="lr-line line-end"></div>
    </div>

    <!-- RIGHT: LAYER ROWS -->
    <div class="layer-rows">

      <!-- L1 -->
      <div class="lrow">
        <div class="lrow-left">
          <div class="lrow-name c-blue">Data Ingestion</div>
          <div class="lrow-desc">Connects to every customer-facing platform via 200+ pre-built connectors. Streams real-time events and ingests historical data from websites, apps, CRM, POS, call centres, social media, email, IoT, and more. Zero ETL code required — our connectors handle schema mapping, deduplication, and normalisation automatically.</div>
          <div class="lrow-tags">
            <span class="ltag tag-blue">200+ Connectors</span>
            <span class="ltag tag-blue">Real-time Streaming</span>
            <span class="ltag tag-blue">Zero-code Setup</span>
            <span class="ltag tag-blue">Historical Backfill</span>
            <span class="ltag tag-blue">Schema Auto-mapping</span>
          </div>
        </div>
        <div class="lrow-stats">
          <div><div class="lstat-val c-blue">200+</div><div class="lstat-lbl">Connectors</div></div>
          <div><div class="lstat-val c-blue">&lt;60ms</div><div class="lstat-lbl">Ingestion Lag</div></div>
        </div>
      </div>

      <!-- L2 -->
      <div class="lrow">
        <div class="lrow-left">
          <div class="lrow-name c-violet">Data Unification</div>
          <div class="lrow-desc">Merges customer identities across every data source into a single golden record per real person. Uses probabilistic matching (email hashes, device fingerprints, behavioural similarity) and deterministic matching (login IDs, phone numbers) to achieve 87%+ identity resolution rates. Eliminates duplicate records that inflate customer counts and distort analytics.</div>
          <div class="lrow-tags">
            <span class="ltag tag-violet">Identity Resolution</span>
            <span class="ltag tag-violet">Probabilistic Matching</span>
            <span class="ltag tag-violet">Golden Record</span>
            <span class="ltag tag-violet">Cross-device</span>
          </div>
        </div>
        <div class="lrow-stats">
          <div><div class="lstat-val c-violet">87%+</div><div class="lstat-lbl">Match Rate</div></div>
          <div><div class="lstat-val c-violet">0%</div><div class="lstat-lbl">Duplicates</div></div>
        </div>
      </div>

      <!-- L3 -->
      <div class="lrow">
        <div class="lrow-left">
          <div class="lrow-name c-rose">Behavioural Mapping</div>
          <div class="lrow-desc">Constructs rich behavioural graphs from every micro-interaction — page views, clicks, session depth, purchase journeys, support interactions, sentiment trajectories, and channel preferences. Maps customer intent signals, lifecycle stage, and engagement velocity into a structured representation the prediction engine can work with.</div>
          <div class="lrow-tags">
            <span class="ltag tag-rose">Journey Graphs</span>
            <span class="ltag tag-rose">Sentiment NLP</span>
            <span class="ltag tag-rose">Intent Signals</span>
            <span class="ltag tag-rose">Lifecycle Stage</span>
          </div>
        </div>
        <div class="lrow-stats">
          <div><div class="lstat-val c-rose">24K+</div><div class="lstat-lbl">Behaviours</div></div>
          <div><div class="lstat-val c-rose">Real-time</div><div class="lstat-lbl">Graph Updates</div></div>
        </div>
      </div>

      <!-- L4 -->
      <div class="lrow">
        <div class="lrow-left">
          <div class="lrow-name c-amber">Pattern Detection</div>
          <div class="lrow-desc">Machine learning algorithms surface hidden correlations, anomalies, and predictive patterns across millions of customer journeys. Trained on industry-specific historical data, the pattern engine identifies signal combinations that human analysts cannot see at scale — like the "Week 2 skip" pattern that predicted churn at MockMaster, or the "40/7 rule" at OneAustralia.</div>
          <div class="lrow-tags">
            <span class="ltag tag-amber">ML Algorithms</span>
            <span class="ltag tag-amber">Anomaly Detection</span>
            <span class="ltag tag-amber">Correlation Mining</span>
            <span class="ltag tag-amber">Industry Models</span>
          </div>
        </div>
        <div class="lrow-stats">
          <div><div class="lstat-val c-amber">2.4M+</div><div class="lstat-lbl">Pattern Scans</div></div>
          <div><div class="lstat-val c-amber">&lt;200ms</div><div class="lstat-lbl">Analysis Time</div></div>
        </div>
      </div>

      <!-- L5 -->
      <div class="lrow">
        <div class="lrow-left">
          <div class="lrow-name c-cyan">Predictive Engine</div>
          <div class="lrow-desc">Generates forward-looking predictions for every customer — churn probability, purchase propensity, customer lifetime value, expansion readiness, and optimal next-best-action — each with calibrated confidence intervals. Uses ensemble methods combining gradient boosting, deep learning, and survival analysis models tuned for each industry vertical.</div>
          <div class="lrow-tags">
            <span class="ltag tag-cyan">Churn Prediction</span>
            <span class="ltag tag-cyan">CLV Scoring</span>
            <span class="ltag tag-cyan">Purchase Propensity</span>
            <span class="ltag tag-cyan">Confidence Intervals</span>
            <span class="ltag tag-cyan">Ensemble Methods</span>
          </div>
        </div>
        <div class="lrow-stats">
          <div><div class="lstat-val c-cyan">97%</div><div class="lstat-lbl">Avg Accuracy</div></div>
          <div><div class="lstat-val c-cyan">15</div><div class="lstat-lbl">Model Types</div></div>
        </div>
      </div>

      <!-- L6 -->
      <div class="lrow">
        <div class="lrow-left">
          <div class="lrow-name c-green">Strategy Intelligence</div>
          <div class="lrow-desc">Translates raw predictions into concrete, industry-specific strategies — which offer to make, which channel to use, at what price, with what message, at what time. Generates projected ROI for each recommended action so teams can prioritise by expected impact. Includes A/B testing variants generated automatically for each intervention.</div>
          <div class="lrow-tags">
            <span class="ltag tag-green">Industry Playbooks</span>
            <span class="ltag tag-green">ROI Projections</span>
            <span class="ltag tag-green">Channel Optimisation</span>
            <span class="ltag tag-green">A/B Variants</span>
          </div>
        </div>
        <div class="lrow-stats">
          <div><div class="lstat-val c-green">8.4×</div><div class="lstat-lbl">Avg Projected ROI</div></div>
          <div><div class="lstat-val c-green">Real-time</div><div class="lstat-lbl">Strategy Updates</div></div>
        </div>
      </div>

      <!-- L7 -->
      <div class="lrow">
        <div class="lrow-left">
          <div class="lrow-name c-orange">Autonomous Execution</div>
          <div class="lrow-desc">When predictions cross configurable activation thresholds, Layer 7 fires real-time actions: personalised emails, push notifications, CRM record updates, ad spend rebalancing, support queue routing, pricing adjustments. All triggered automatically in under 2 seconds from the prediction generating in Layer 5. Actions can be configured as automated, semi-automated, or recommendation-only.</div>
          <div class="lrow-tags">
            <span class="ltag tag-orange">Auto-triggers</span>
            <span class="ltag tag-orange">Real-time Actions</span>
            <span class="ltag tag-orange">CRM Sync</span>
            <span class="ltag tag-orange">Ad Rebalancing</span>
            <span class="ltag tag-orange">Configurable Thresholds</span>
          </div>
        </div>
        <div class="lrow-stats">
          <div><div class="lstat-val c-orange">&lt;2s</div><div class="lstat-lbl">Trigger Speed</div></div>
          <div><div class="lstat-val c-orange">99.9%</div><div class="lstat-lbl">Delivery Rate</div></div>
        </div>
      </div>

      <!-- L8 -->
      <div class="lrow">
        <div class="lrow-left">
          <div class="lrow-name c-blue">Continuous Learning</div>
          <div class="lrow-desc">Every outcome — purchase, churn, conversion, click, NPS score — feeds back into the system. Models retrain continuously on new data, pattern weights update, and prediction accuracy improves with every interaction. The engine is genuinely self-improving: the longer it runs on your data, the better it gets at predicting your specific customers.</div>
          <div class="lrow-tags">
            <span class="ltag tag-blue">Self-improving</span>
            <span class="ltag tag-blue">Continuous Retraining</span>
            <span class="ltag tag-blue">Feedback Loops</span>
            <span class="ltag tag-blue">A/B Outcome Integration</span>
          </div>
        </div>
        <div class="lrow-stats">
          <div><div class="lstat-val c-blue">+0.3%</div><div class="lstat-lbl">Weekly Accuracy Gain</div></div>
          <div><div class="lstat-val c-blue">Daily</div><div class="lstat-lbl">Model Retraining</div></div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- PERFORMANCE -->
<div class="perf">
  <div class="perf-in">
    <div class="sec-label">Performance</div>
    <h2 class="sec-h">Engine specifications</h2>
    <div class="perf-grid">
      <div class="perf-card">
        <div class="perf-val c-blue">18K+</div>
        <div class="perf-key">Events/Second</div>
        <div class="perf-desc">Sustained throughput under production load</div>
      </div>
      <div class="perf-card">
        <div class="perf-val c-cyan">&lt;200ms</div>
        <div class="perf-key">End-to-End Latency</div>
        <div class="perf-desc">From event ingestion to prediction output</div>
      </div>
      <div class="perf-card">
        <div class="perf-val c-green">99.95%</div>
        <div class="perf-key">Uptime SLA</div>
        <div class="perf-desc">Growth plan. 99.99% for Enterprise</div>
      </div>
      <div class="perf-card">
        <div class="perf-val c-violet">AES-256</div>
        <div class="perf-key">Encryption Standard</div>
        <div class="perf-desc">At rest and in transit, all data</div>
      </div>
      <div class="perf-card">
        <div class="perf-val c-blue">1.2B+</div>
        <div class="perf-key">Predictions Generated</div>
        <div class="perf-desc">Across all customers to date</div>
      </div>
      <div class="perf-card">
        <div class="perf-val c-amber">200+</div>
        <div class="perf-key">Pre-built Connectors</div>
        <div class="perf-desc">Zero-code data source integration</div>
      </div>
      <div class="perf-card">
        <div class="perf-val c-rose">15</div>
        <div class="perf-key">Industry Models</div>
        <div class="perf-desc">Pre-trained, ready from day one</div>
      </div>
      <div class="perf-card">
        <div class="perf-val c-orange">Daily</div>
        <div class="perf-key">Model Retraining</div>
        <div class="perf-desc">Continuous learning from your outcomes</div>
      </div>
    </div>
  </div>
</div>

<!-- CTA -->
<div class="cta">
  <h2 class="cta-h">See all 8 layers work on <span class="hl">your data</span></h2>
  <p class="cta-sub">Book a demo and watch the engine process your actual customers — from raw data to live predictions — in 30 minutes.</p>
  <div class="btn-row">
    <a href="{{ route('book-demo') }}" class="btn-fill">Book a Demo</a>
    <a href="{{ route('simulator') }}" class="btn-out">Try the Simulator</a>
  </div>
</div>

<!-- FOOTER -->
<footer class="foot"><div class="foot-in">
  <div><a href="{{ url('/') }}" class="logo"><img src="{{ asset('images/xplatforms_logo.jpeg') }}" alt="X Platforms" style="height:32px;width:auto;display:block"></a><p class="foot-desc">The world's first 8-layer AI intelligence engine.</p></div>
  <div class="foot-c"><h5>Product</h5><a href="{{ route('platform.architecture') }}">Architecture</a><a href="#">Integrations</a><a href="{{ route('industries') }}">Industries</a><a href="{{ route('pricing') }}">Pricing</a></div>
  <div class="foot-c"><h5>Company</h5><a href="{{ route('about') }}">About</a><a href="{{ route('careers') }}">Careers</a><a href="{{ route('blog') }}">Blog</a><a href="{{ route('contact') }}">Contact</a></div>
  <div class="foot-c"><h5>Resources</h5><a href="#">Documentation</a><a href="{{ route('case-studies') }}">Case Studies</a><a href="#">API Reference</a><a href="{{ route('security') }}">Security</a></div>
</div><div class="foot-b"><span>&copy; {{ date('Y') }} X Platforms.</span><span><a href="{{ route('privacy') }}" style="color:inherit">Privacy</a> &middot; <a href="{{ route('terms') }}" style="color:inherit">Terms</a> &middot; <a href="{{ route('security') }}" style="color:inherit">Security</a></span></div></footer>

</body>
</html>
