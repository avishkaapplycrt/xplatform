{{-- Mira Premium workspace styles (mira-premium.blade.php). Light, quiet,
     data-first: white cards on a warm canvas, 1px hairlines, ink numbers,
     and gold reserved for what is premium (plan, upgrade, selection).
     Tokens first; components reference them, never raw colours. --}}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  /* surfaces */
  --canvas:#f6f5f1;--card:#fff;--raise:#fbfaf7;--line:#e8e6e0;--line-2:#dcd9d1;
  /* text */
  --ink:#141417;--ink-2:#3a3a40;--muted:#6d6b72;--dim:#a2a0a7;
  /* accent: gold is premium */
  --gold:#a8801f;--gold-2:#c9a13a;--gold-3:#e6c977;--gold-soft:#f7efd9;--gold-line:#ead9a8;
  --foil:linear-gradient(135deg,#f3dc9a 0%,#d9b558 45%,#b08a35 100%);
  /* status */
  --good:#1f8a5b;--good-soft:#e7f4ed;--warn:#b7791f;--warn-soft:#fbf1e1;--bad:#c8473f;--bad-soft:#fbeceb;--info:#3f6fd8;--info-soft:#ecf1fd;
  /* type + rhythm */
  --f-ui:'Inter',system-ui,-apple-system,sans-serif;--f-num:'Space Grotesk','Inter',sans-serif;--f-display:'Instrument Serif',Georgia,serif;
  --r-sm:10px;--r-md:14px;--r-lg:20px;--r-xl:26px;
  --shadow:0 1px 2px rgba(20,20,30,.04),0 10px 30px -18px rgba(20,20,30,.18);
  --shadow-lg:0 2px 4px rgba(20,20,30,.04),0 30px 70px -36px rgba(20,20,30,.35);
  --ease:cubic-bezier(.16,1,.3,1);--mw:1240px;

  /* shared Ask Mira partials (modals, report) read these names */
  --white:var(--ink);--g100:var(--ink-2);--g200:#4a4a50;--g300:var(--muted);--g400:var(--dim);--g500:#d9d9de;--g600:#ececef;
  --bg3:#f5f4f0;--brd:var(--line);--brd2:var(--gold-line);--blue:var(--gold);--blue2:#8a6814;--cyan:#8a6814;
  --acc-rgb:168,128,31;--on-acc:#fff;--emerald:var(--good);--amber:var(--warn);--rose:var(--bad);
  --f1:var(--f-ui);--fm:var(--f-num);--serp-title:#1a0dab;
}
html{scroll-behavior:smooth}
body{background:var(--canvas);color:var(--ink);font-family:var(--f-ui);font-size:15px;line-height:1.5;-webkit-font-smoothing:antialiased;min-height:100vh}
a{color:inherit;text-decoration:none}
button{font:inherit;color:inherit;cursor:pointer}
input,textarea,select{font:inherit;color:inherit}
:focus-visible{outline:2px solid var(--gold-2);outline-offset:2px}
.num{font-family:var(--f-num);font-variant-numeric:tabular-nums;letter-spacing:-.01em}
.muted{color:var(--muted)}
.sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
[hidden]{display:none!important}

/* ===================== Buttons (one system, four intents) ===================== */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:40px;padding:0 18px;border-radius:999px;border:1px solid transparent;
  font-size:14px;font-weight:600;letter-spacing:-.005em;white-space:nowrap;transition:transform .15s var(--ease),box-shadow .2s,background .2s,border-color .2s,opacity .2s}
.btn svg{width:16px;height:16px;flex:none;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.btn:disabled{opacity:.55;cursor:not-allowed}
.btn-sm{height:32px;padding:0 13px;font-size:13px}
.btn-lg{height:48px;padding:0 24px;font-size:15px}
/* primary: ink with a hairline of gold light on top */
.btn-primary{background:linear-gradient(180deg,#26262b,#141417);color:#fff;border-color:#141417;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.12),0 1px 2px rgba(20,20,30,.2),0 8px 20px -10px rgba(20,20,30,.6)}
.btn-primary:not(:disabled):hover{transform:translateY(-1px);box-shadow:inset 0 1px 0 rgba(255,255,255,.14),0 2px 4px rgba(20,20,30,.2),0 14px 26px -12px rgba(20,20,30,.6)}
.btn-primary .spark{color:var(--gold-3)}
/* premium: champagne foil, for upgrade / unlock */
.btn-premium{background:var(--foil);color:#1d1606;border-color:#c9a13a;box-shadow:inset 0 1px 0 rgba(255,255,255,.55),0 8px 22px -10px rgba(176,138,53,.75)}
.btn-premium:not(:disabled):hover{transform:translateY(-1px);box-shadow:inset 0 1px 0 rgba(255,255,255,.6),0 14px 28px -12px rgba(176,138,53,.85)}
.btn-secondary{background:var(--card);border-color:var(--line-2);color:var(--ink);box-shadow:0 1px 2px rgba(20,20,30,.05)}
.btn-secondary:not(:disabled):hover{border-color:var(--ink-2)}
.btn-ghost{background:transparent;color:var(--muted)}
.btn-ghost:not(:disabled):hover{color:var(--ink);background:rgba(20,20,30,.04)}
.btn-danger{background:transparent;color:var(--bad)}
.btn-danger:hover{background:var(--bad-soft)}
.btn.loading{position:relative;color:transparent!important}
.btn.loading::after{content:"";position:absolute;width:16px;height:16px;border-radius:50%;border:2px solid currentColor;border-color:#fff #fff transparent transparent;animation:spin .7s linear infinite}
.btn-secondary.loading::after{border-color:var(--ink) var(--ink) transparent transparent}
@keyframes spin{to{transform:rotate(360deg)}}

/* ===================== Badges & chips ===================== */
.badge{display:inline-flex;align-items:center;gap:6px;height:24px;padding:0 10px;border-radius:999px;font-size:11.5px;font-weight:600;letter-spacing:.02em;white-space:nowrap}
.badge-plan{background:var(--gold-soft);color:#7a5d14;box-shadow:inset 0 0 0 1px var(--gold-line)}
.badge-plan::before{content:"";width:6px;height:6px;border-radius:50%;background:var(--gold-2)}
.badge-preview{background:#f1f0ec;color:var(--muted);box-shadow:inset 0 0 0 1px var(--line)}
.badge-good{background:var(--good-soft);color:var(--good)}
.badge-warn{background:var(--warn-soft);color:var(--warn)}
.badge-bad{background:var(--bad-soft);color:var(--bad)}
.badge-info{background:var(--info-soft);color:var(--info)}
.badge-lock{background:#141417;color:var(--gold-3)}
.badge-lock svg{width:11px;height:11px;fill:none;stroke:currentColor;stroke-width:2.4}
.delta{font-family:var(--f-num);font-size:13px;font-weight:600;white-space:nowrap}
.delta.up{color:var(--good)}.delta.down{color:var(--bad)}.delta.flat{color:var(--dim)}

/* ===================== Top bar ===================== */
.topbar{position:sticky;top:0;z-index:40;background:rgba(246,245,241,.86);backdrop-filter:saturate(1.4) blur(14px);-webkit-backdrop-filter:saturate(1.4) blur(14px);border-bottom:1px solid var(--line)}
.topbar-in{max-width:var(--mw);margin:0 auto;height:64px;padding:0 28px;display:flex;align-items:center;gap:18px}
.brand{display:flex;align-items:center;gap:10px;flex:none}
.brand img{height:28px;width:auto;display:block}
.brand-sep{width:1px;height:22px;background:var(--line-2)}
.brand-name{font-family:var(--f-display);font-style:italic;font-size:24px;line-height:1;letter-spacing:-.01em}
.brand-name b{font-family:var(--f-ui);font-style:normal;font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);margin-left:8px;vertical-align:4px}
.project-switch{position:relative;margin-left:6px}
.project-btn{display:flex;align-items:center;gap:10px;height:38px;padding:0 12px 0 6px;border-radius:12px;border:1px solid var(--line);background:var(--card);max-width:280px}
.project-btn:hover{border-color:var(--line-2)}
.project-fav{width:26px;height:26px;border-radius:8px;display:grid;place-items:center;flex:none;font-size:11px;font-weight:700;color:#fff;background:linear-gradient(135deg,#2b2b31,#141417)}
.project-btn .pn{font-size:13.5px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.project-btn svg{width:14px;height:14px;flex:none;stroke:var(--muted);fill:none;stroke-width:2}
.menu{position:absolute;top:calc(100% + 8px);left:0;min-width:300px;background:var(--card);border:1px solid var(--line);border-radius:var(--r-md);box-shadow:var(--shadow-lg);padding:6px;z-index:60}
.menu-item{display:flex;align-items:center;gap:10px;width:100%;padding:9px 10px;border:0;background:none;border-radius:10px;text-align:left;font-size:13.5px}
.menu-item:hover,.menu-item[aria-current="true"]{background:var(--raise)}
.menu-item small{display:block;color:var(--dim);font-size:12px}
.menu-sep{height:1px;background:var(--line);margin:6px 4px}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.usage-pill{display:flex;align-items:center;gap:10px;height:34px;padding:0 12px;border-radius:999px;border:1px solid var(--line);background:var(--card);font-size:12.5px;color:var(--muted)}
.usage-pill .bar{width:56px;height:5px;border-radius:5px;background:#eeece6;overflow:hidden}
.usage-pill .bar i{display:block;height:100%;background:var(--ink);border-radius:5px;transition:width .6s var(--ease)}
.usage-pill.low .bar i{background:var(--bad)}
@media (max-width:980px){.usage-pill{display:none}}
@media (max-width:760px){.topbar-in{padding:0 16px;gap:10px}.brand img,.brand-sep{display:none}.project-switch{display:none}.topbar-right .btn-ghost{display:none}}

/* ===================== Hero + scan bar ===================== */
.wrap{max-width:var(--mw);margin:0 auto;padding:0 28px}
@media (max-width:760px){.wrap{padding:0 16px}}
.hero{padding:44px 0 26px;display:grid;grid-template-columns:1.1fr .9fr;gap:40px;align-items:end}
@media (max-width:980px){.hero{grid-template-columns:1fr;gap:18px;padding-top:30px}}
.eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:11.5px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gold)}
.eyebrow::before{content:"";width:18px;height:1px;background:var(--gold-2)}
.hero h1{margin-top:12px;font-family:var(--f-num);font-weight:500;font-size:clamp(34px,4.6vw,54px);line-height:1.02;letter-spacing:-.045em}
.hero h1 em{font-family:var(--f-display);font-weight:400;letter-spacing:-.01em;color:var(--gold)}
.hero p{margin-top:14px;max-width:560px;color:var(--muted);font-size:16px;line-height:1.6}
.hero-proof{display:flex;flex-wrap:wrap;gap:8px 18px;justify-content:flex-end;font-size:13px;color:var(--muted)}
.hero-proof span{display:inline-flex;align-items:center;gap:7px}
.hero-proof svg{width:15px;height:15px;stroke:var(--gold);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
@media (max-width:980px){.hero-proof{justify-content:flex-start}}

.scanbar{background:var(--card);border:1px solid var(--line);border-radius:var(--r-xl);box-shadow:var(--shadow-lg);padding:10px;display:grid;grid-template-columns:1fr auto auto;gap:10px;align-items:center}
.scanbar.with-comp{grid-template-columns:1fr 1fr auto auto}
.field{display:flex;align-items:center;gap:10px;height:52px;padding:0 16px;border-radius:16px;background:var(--raise);border:1px solid transparent;transition:border-color .2s,box-shadow .2s,background .2s}
.field:focus-within{background:var(--card);border-color:var(--gold-line);box-shadow:0 0 0 4px rgba(201,161,58,.12)}
.field svg{width:18px;height:18px;flex:none;stroke:var(--dim);fill:none;stroke-width:2;stroke-linecap:round}
.field input{flex:1;min-width:0;border:0;outline:0;background:transparent;font-size:15px}
.field input::placeholder{color:var(--dim)}
.field .tag{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--dim)}
.scan-note{display:flex;flex-wrap:wrap;align-items:center;gap:8px 16px;margin:12px 6px 0;font-size:13px;color:var(--muted)}
.scan-note .err{color:var(--bad);font-weight:500}
#turnstileSlot:empty{display:none}
@media (max-width:860px){.scanbar,.scanbar.with-comp{grid-template-columns:1fr}.scanbar .btn{width:100%}}

/* ===================== Tabs ===================== */
.tabs{display:flex;gap:4px;margin:30px 0 22px;border-bottom:1px solid var(--line);overflow-x:auto;scrollbar-width:none}
.tab{position:relative;display:inline-flex;align-items:center;gap:8px;height:44px;padding:0 14px;border:0;background:none;font-size:14px;font-weight:600;color:var(--muted);white-space:nowrap}
.tab:hover{color:var(--ink)}
.tab[aria-selected="true"]{color:var(--ink)}
.tab[aria-selected="true"]::after{content:"";position:absolute;left:10px;right:10px;bottom:-1px;height:2px;border-radius:2px;background:var(--ink)}
.tab .badge-lock{height:18px;padding:0 6px}
.tab .count{font-family:var(--f-num);font-size:11.5px;color:var(--dim)}
.panel{animation:fadeIn .35s var(--ease)}
@keyframes fadeIn{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:none}}

/* ===================== Cards & KPI tiles ===================== */
.card{background:var(--card);border:1px solid var(--line);border-radius:var(--r-lg);box-shadow:var(--shadow)}
.card-hd{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 22px 0}
.card-hd h2,.card-hd h3{font-size:15px;font-weight:650;letter-spacing:-.01em}
.card-hd .sub{font-size:12.5px;color:var(--muted)}
.card-bd{padding:16px 22px 22px}
.grid{display:grid;gap:14px}
.kpis{grid-template-columns:repeat(4,1fr)}
@media (max-width:1080px){.kpis{grid-template-columns:repeat(2,1fr)}}
@media (max-width:560px){.kpis{grid-template-columns:1fr}}
.kpi{position:relative;padding:20px 22px;overflow:hidden}
.kpi-lbl{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
.kpi-lbl .i{display:inline-grid;place-items:center;width:14px;height:14px;border-radius:50%;font-size:9px;font-weight:700;letter-spacing:0;color:var(--dim);box-shadow:inset 0 0 0 1px var(--line-2);cursor:help;text-transform:none}
.kpi-val{display:flex;align-items:baseline;gap:10px;margin:12px 0 6px}
.kpi-val .big{font-family:var(--f-num);font-size:40px;font-weight:500;line-height:1;letter-spacing:-.045em}
.kpi-val .unit{font-family:var(--f-num);font-size:16px;color:var(--dim)}
.kpi-sub{display:flex;flex-wrap:wrap;align-items:center;gap:6px 12px;font-size:12.5px;color:var(--muted)}
.kpi-sub b{font-family:var(--f-num);font-weight:600;color:var(--ink)}
.kpi .spark{position:absolute;right:16px;top:18px;width:96px;height:34px}
.kpi.locked .kpi-val .big{color:#d4d2cc}
.grade{display:inline-grid;place-items:center;min-width:26px;height:22px;padding:0 6px;border-radius:7px;font-family:var(--f-num);font-size:12px;font-weight:700;background:var(--ink);color:#fff}
.meter{height:6px;border-radius:6px;background:#efede7;overflow:hidden}
.meter i{display:block;height:100%;border-radius:6px;background:var(--ink);transition:width .7s var(--ease)}
.meters{display:grid;gap:12px;margin-top:12px}
.meter-row{display:grid;gap:6px;font-size:12.5px}
.meter-row .top{display:flex;justify-content:space-between;gap:8px;color:var(--muted)}
.meter-row .top b{font-family:var(--f-num);color:var(--ink);font-weight:600}
.meter-row.low .meter i{background:var(--bad)}

.split{display:grid;grid-template-columns:1.35fr 1fr;gap:14px;margin-top:14px}
@media (max-width:1080px){.split{grid-template-columns:1fr}}

/* Next best actions */
.actions{display:grid}
.action{display:grid;grid-template-columns:auto 1fr;gap:4px 14px;padding:14px 0;border-top:1px solid var(--line)}
.action:first-child{border-top:0;padding-top:4px}
.action .n{grid-row:span 2;width:26px;height:26px;border-radius:8px;display:grid;place-items:center;font-family:var(--f-num);font-size:12.5px;font-weight:600;background:var(--raise);box-shadow:inset 0 0 0 1px var(--line)}
.action h4{font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.action p{font-size:13.5px;color:var(--ink-2);line-height:1.55}

/* Stage bars */
.stages{display:grid;gap:12px}
.stage-row{display:grid;grid-template-columns:110px 1fr 44px;align-items:center;gap:12px;font-size:13.5px}
.stage-row .lbl{font-weight:500}
.stage-row .track{height:6px;border-radius:6px;background:#efede7;overflow:hidden}
.stage-row .track i{display:block;height:100%;border-radius:6px;transition:width .8s var(--ease)}
.stage-row .val{text-align:right;font-family:var(--f-num);font-weight:600}
.tone-good{background:var(--good)}.tone-warn{background:var(--warn)}.tone-bad{background:var(--bad)}.tone-none{background:#d8d6cf}
.c-good{color:var(--good)}.c-warn{color:var(--warn)}.c-bad{color:var(--bad)}

/* ===================== Empty / loading / locked states ===================== */
.state{display:grid;place-items:center;text-align:center;padding:56px 24px;gap:12px}
.state h3{font-family:var(--f-num);font-weight:500;font-size:24px;letter-spacing:-.03em}
.state p{max-width:460px;color:var(--muted);font-size:14.5px}
.state .icon{width:52px;height:52px;border-radius:16px;display:grid;place-items:center;background:var(--raise);box-shadow:inset 0 0 0 1px var(--line)}
.state .icon svg{width:22px;height:22px;stroke:var(--ink-2);fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.progress{width:min(420px,100%);display:grid;gap:10px;justify-items:center}
.progress .track{width:100%;height:4px;border-radius:4px;background:#eeece6;overflow:hidden;position:relative}
.progress .track i{position:absolute;inset:0 auto 0 0;width:38%;border-radius:4px;background:var(--ink);animation:indeterminate 1.4s var(--ease) infinite}
@keyframes indeterminate{0%{left:-40%}100%{left:100%}}
.steps{display:flex;flex-wrap:wrap;justify-content:center;gap:8px 18px;font-size:12.5px;color:var(--dim)}
.steps .on{color:var(--ink);font-weight:500}
.steps .done{color:var(--good)}
.skeleton{background:linear-gradient(90deg,#f0eee9 0%,#f8f7f3 50%,#f0eee9 100%);background-size:200% 100%;animation:shimmer 1.3s linear infinite;border-radius:8px}
@keyframes shimmer{from{background-position:200% 0}to{background-position:-200% 0}}

/* Locked (premium) */
.locked-card{position:relative;overflow:hidden;border-radius:var(--r-xl);border:1px solid var(--gold-line);
  background:radial-gradient(120% 120% at 100% 0%,#fdf7e6 0%,#fff 55%);box-shadow:var(--shadow);padding:40px;display:grid;grid-template-columns:1.1fr .9fr;gap:36px;align-items:center}
@media (max-width:900px){.locked-card{grid-template-columns:1fr;padding:28px}}
.locked-card h3{font-family:var(--f-num);font-size:30px;font-weight:500;letter-spacing:-.04em;line-height:1.1;margin-top:10px}
.locked-card h3 em{font-family:var(--f-display);font-weight:400;color:var(--gold)}
.locked-card p{margin-top:12px;color:var(--muted);font-size:15px;max-width:520px}
.locked-card ul{list-style:none;display:grid;gap:10px;margin-top:20px}
.locked-card li{display:flex;gap:10px;font-size:14px;color:var(--ink-2)}
.locked-card li svg{width:18px;height:18px;flex:none;stroke:var(--gold);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;margin-top:1px}
.locked-card .cta{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px}
.locked-preview{border-radius:var(--r-lg);border:1px solid var(--line);background:var(--card);padding:18px;box-shadow:var(--shadow);filter:saturate(.9);position:relative}
.locked-preview::after{content:"";position:absolute;inset:0;border-radius:inherit;background:linear-gradient(180deg,transparent 40%,rgba(255,255,255,.92))}

/* ===================== Tables ===================== */
.table{width:100%;border-collapse:collapse;font-size:13.5px}
.table th{font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--dim);text-align:left;padding:0 12px 10px;border-bottom:1px solid var(--line)}
.table td{padding:12px;border-bottom:1px solid var(--line);vertical-align:middle}
.table tr:last-child td{border-bottom:0}
.table tbody tr{transition:background .15s}
.table tbody tr.click{cursor:pointer}
.table tbody tr.click:hover{background:var(--raise)}
.table .r{text-align:right}
.table .url{max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.table-wrap{overflow-x:auto}

/* ===================== AI visibility ===================== */
.vis-grid{display:grid;grid-template-columns:1fr 1.25fr;gap:14px;margin-top:14px}
@media (max-width:1080px){.vis-grid{grid-template-columns:1fr}}
.sov{display:grid;gap:12px}
.sov-row{display:grid;grid-template-columns:140px 1fr 56px;gap:12px;align-items:center;font-size:13.5px}
.sov-row .nm{display:flex;align-items:center;gap:8px;font-weight:500;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}
.sov-row .nm .dot{width:8px;height:8px;border-radius:50%;flex:none;background:var(--dim)}
.sov-row.you .nm .dot{background:var(--gold-2)}
.sov-row .track{height:10px;border-radius:10px;background:#efede7;overflow:hidden}
.sov-row .track i{display:block;height:100%;border-radius:10px;background:#c9c6be;transition:width .8s var(--ease)}
.sov-row.you .track i{background:var(--foil)}
.sov-row .v{text-align:right;font-family:var(--f-num);font-weight:600}
.prompt-list{display:grid;max-height:460px;overflow:auto;scrollbar-width:thin}
.prompt{display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:center;padding:10px 4px;border-top:1px solid var(--line);font-size:13.5px}
.prompt:first-child{border-top:0}
.prompt.off{opacity:.5}
.prompt .meta{display:flex;gap:6px;margin-top:3px}
.switch{position:relative;width:34px;height:20px;border-radius:20px;background:#dcd9d1;border:0;transition:background .2s;flex:none}
.switch::after{content:"";position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.2);transition:transform .2s var(--ease)}
.switch[aria-checked="true"]{background:var(--ink)}
.switch[aria-checked="true"]::after{transform:translateX(14px)}
.add-row{display:flex;gap:8px;margin-top:14px}
.add-row .field{height:44px;flex:1}
.add-row select{height:44px;border:1px solid var(--line);border-radius:12px;background:var(--raise);padding:0 10px;font-size:13px}
.answer{border-top:1px solid var(--line);padding:14px 0}
.answer:first-child{border-top:0}
.answer summary{list-style:none;display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:center;cursor:pointer;font-size:13.5px}
.answer summary::-webkit-details-marker{display:none}
.answer .flag{width:24px;height:24px;border-radius:8px;display:grid;place-items:center;font-size:12px;font-weight:700}
.answer .flag.yes{background:var(--good-soft);color:var(--good)}
.answer .flag.no{background:#f1f0ec;color:var(--dim)}
.answer .body{margin-top:12px;padding:14px 16px;border-radius:12px;background:var(--raise);font-size:13.5px;line-height:1.65;white-space:pre-wrap;color:var(--ink-2)}
.answer mark{background:var(--gold-soft);color:inherit;padding:0 2px;border-radius:3px;box-shadow:inset 0 -2px 0 var(--gold-3)}
.chips{display:flex;flex-wrap:wrap;gap:6px}
.chip{display:inline-flex;align-items:center;height:24px;padding:0 9px;border-radius:999px;background:#f1f0ec;font-size:12px;color:var(--ink-2)}
.chip.you{background:var(--gold-soft);color:#7a5d14}
.note{display:flex;gap:10px;align-items:flex-start;padding:12px 14px;border-radius:12px;background:var(--raise);border:1px solid var(--line);font-size:12.5px;color:var(--muted)}
.note svg{width:16px;height:16px;flex:none;stroke:var(--muted);fill:none;stroke-width:2;margin-top:1px}
.editor{display:grid;gap:14px}
.editor label{display:grid;gap:6px;font-size:12.5px;font-weight:600;color:var(--muted)}
.editor .field{height:44px}
.comp-row{display:grid;grid-template-columns:1fr 1fr auto;gap:8px}

/* ===================== Ask Mira (chat) ===================== */
.chat{display:grid;grid-template-columns:280px 1fr;min-height:620px;overflow:hidden}
@media (max-width:900px){.chat{grid-template-columns:1fr}}
.chat-side{border-right:1px solid var(--line);background:var(--raise);padding:20px;display:grid;align-content:start;gap:18px;max-height:720px;overflow:auto}
@media (max-width:900px){.chat-side{border-right:0;border-bottom:1px solid var(--line);max-height:none}}
.side-h{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--dim);margin-bottom:8px}
.site-card{display:flex;gap:12px;align-items:center;padding:12px;border-radius:14px;background:var(--card);border:1px solid var(--line);font-size:12.5px}
.site-card .project-fav{width:34px;height:34px;border-radius:10px;font-size:12px}
.site-card .t{font-weight:600;font-size:13.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.site-card .u{color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.starter-group{display:grid;gap:6px;margin-top:10px}
.starter-tag{font-family:var(--f-num);font-size:11px;font-weight:700;letter-spacing:.12em;color:var(--sc,var(--gold))}
.starter{width:100%;text-align:left;border:0;background:none;padding:8px 10px;border-radius:10px;font-size:13px;color:var(--ink-2);line-height:1.45;box-shadow:inset 2px 0 0 color-mix(in srgb,var(--sc,var(--gold)) 40%,transparent)}
.starter:hover{background:var(--card);color:var(--ink);box-shadow:inset 2px 0 0 var(--sc,var(--gold)),0 1px 2px rgba(20,20,30,.05)}
.chat-main{display:flex;flex-direction:column;min-width:0;height:720px}
@media (max-width:900px){.chat-main{height:70vh;min-height:520px}}
.chat-hd{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 20px;border-bottom:1px solid var(--line)}
.chat-hd .t{font-weight:600;font-size:14px;display:flex;align-items:center;gap:10px}
.mira-mark{width:30px;height:30px;border-radius:50%;flex:none;display:grid;place-items:center;font-family:var(--f-display);font-style:italic;font-size:17px;color:#1d1606;background:var(--foil);box-shadow:0 0 0 3px #fff,0 0 0 4px var(--gold-line)}
#chatMsgs{flex:1;overflow-y:auto;padding:24px 24px 8px;display:flex;flex-direction:column;gap:16px;scrollbar-width:thin}
.chat-msg{max-width:76%;padding:12px 16px;border-radius:16px;font-size:14px;line-height:1.65;white-space:pre-wrap;overflow-wrap:anywhere;animation:fadeIn .3s var(--ease)}
.chat-msg.user{align-self:flex-end;background:var(--ink);color:#fff;border-bottom-right-radius:5px}
.chat-msg.bot{position:relative;align-self:flex-start;margin-left:42px;max-width:calc(82% - 42px);background:var(--card);border:1px solid var(--line);color:var(--ink-2);border-bottom-left-radius:5px}
.chat-msg.bot::before{content:"M";position:absolute;left:-42px;top:0;width:30px;height:30px;border-radius:50%;display:grid;place-items:center;font-family:var(--f-display);font-style:italic;font-size:16px;color:#1d1606;background:var(--foil)}
.chat-msg.bot a{color:#8a6814;text-decoration:underline}
.chat-msg.typing{color:var(--dim)}
.chat-msg.notice{align-self:center;margin:0;max-width:92%;background:var(--gold-soft);border:1px solid var(--gold-line);color:#6b5110;font-size:13px;text-align:center}
.chat-msg.notice::before{content:none}
.chat-suggest{display:flex;flex-wrap:wrap;gap:8px;margin:-6px 0 0 42px}
.suggest-chip{border:1px solid var(--line-2);background:var(--card);border-radius:999px;padding:7px 13px;font-size:12.5px;color:var(--ink-2);text-align:left}
.suggest-chip:hover{border-color:var(--gold-2);color:var(--ink)}
.chat-empty{margin:auto;display:grid;justify-items:center;text-align:center;gap:10px;max-width:440px;padding:20px}
.chat-empty h3{font-family:var(--f-num);font-weight:500;font-size:26px;letter-spacing:-.035em}
.chat-empty h3 em{font-family:var(--f-display);font-weight:400;color:var(--gold)}
.chat-empty p{color:var(--muted);font-size:14px}
.composer{display:flex;align-items:flex-end;gap:10px;margin:12px 20px 6px;padding:8px 8px 8px 16px;border-radius:18px;border:1px solid var(--line-2);background:var(--card);transition:border-color .2s,box-shadow .2s}
.composer:focus-within{border-color:var(--gold-line);box-shadow:0 0 0 4px rgba(201,161,58,.12)}
.composer textarea{flex:1;min-width:0;border:0;outline:0;resize:none;background:transparent;padding:9px 0;max-height:140px;font-size:14.5px;line-height:1.5}
.composer .btn{height:40px;width:40px;padding:0;border-radius:12px}
.chat-foot{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:0 22px 14px;font-size:11.5px;color:var(--dim)}

/* ===================== Report (shared .cx-* renderer) ===================== */
#miraComparePanel{--cx-accent:var(--gold);--cx-accent-soft:var(--gold-soft);--cx-accent-line:var(--gold-line);--cx-on-accent:#fff;--cx-bg:transparent;
  background:transparent!important;padding:0}
.report-hd{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
.report-hd h2{font-family:var(--f-num);font-weight:500;font-size:22px;letter-spacing:-.03em}
.report-hd .meta{display:flex;flex-wrap:wrap;gap:8px;align-items:center;font-size:12.5px;color:var(--muted)}
.upsell-strip{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;padding:16px 20px;margin-bottom:16px;border-radius:var(--r-lg);
  background:linear-gradient(90deg,#141417,#2a2620);color:#f4efe2;box-shadow:var(--shadow)}
.upsell-strip b{color:var(--gold-3)}
.upsell-strip p{font-size:14px}

/* Modals from the shared partial, on the light theme */
.a-modal-overlay,.lead-modal-overlay{background:rgba(20,20,26,.45)}
.a-modal,.lead-modal{background:#fff;border-color:var(--line);color-scheme:light;border-radius:var(--r-lg)}

/* Toasts */
.toasts{position:fixed;right:20px;bottom:20px;display:grid;gap:10px;z-index:100}
.toast{display:flex;gap:12px;align-items:flex-start;max-width:380px;padding:14px 16px;border-radius:14px;background:#141417;color:#f4f2ec;box-shadow:var(--shadow-lg);font-size:13.5px;animation:fadeIn .3s var(--ease)}
.toast.err{background:#3a1715}
.toast a{color:var(--gold-3);font-weight:600;white-space:nowrap}

/* ===================== Site overview (ATELIER metric panels) ===================== */
.ov-head{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:12px;margin-bottom:14px}
.ov-title{font-family:var(--f-num);font-weight:500;font-size:24px;letter-spacing:-.03em}
.ov-domain{font-family:var(--f-ui);font-size:14px;font-weight:500;color:var(--muted);margin-left:6px;letter-spacing:0}
.ov-head .sub{font-size:13px;color:var(--muted)}
.seg{display:inline-flex;padding:3px;border-radius:12px;background:var(--card);border:1px solid var(--line);box-shadow:0 1px 2px rgba(20,20,30,.04)}
.seg button{border:0;background:none;height:30px;padding:0 13px;border-radius:9px;font-family:var(--f-num);font-size:12.5px;font-weight:600;color:var(--muted)}
.seg button:hover{color:var(--ink)}
.seg button[aria-checked="true"]{background:var(--ink);color:#fff}
.section-h{font-family:var(--f-num);font-weight:500;font-size:20px;letter-spacing:-.03em;margin:34px 0 14px}
.ov-grid{display:grid;grid-template-columns:1.05fr 1fr 1fr;gap:14px}
@media (max-width:1180px){.ov-grid{grid-template-columns:1fr 1fr}.ov-grid #pnl-ai{grid-column:1/-1}}
@media (max-width:760px){.ov-grid{grid-template-columns:1fr}}
.pnl{padding:22px 24px;min-width:0}
.pnl-wide{margin-top:14px}
.pnl-hd{display:flex;align-items:center;gap:8px;margin-bottom:18px}
.pnl-hd h3{font-size:16px;font-weight:650;letter-spacing:-.01em}
.pnl-hd .i{display:inline-grid;place-items:center;width:14px;height:14px;border-radius:50%;font-size:9px;font-weight:700;color:var(--dim);box-shadow:inset 0 0 0 1px var(--line-2);cursor:help}
.pnl-hd .right{margin-left:auto;display:flex;align-items:center;gap:8px;font-size:12px;color:var(--dim)}
.mgrid{display:grid;grid-template-columns:1fr 1fr;gap:18px 20px}
.mgrid.three{grid-template-columns:repeat(3,1fr)}
.mgrid.six{grid-template-columns:repeat(6,1fr);gap:0}
@media (max-width:1080px){.mgrid.six{grid-template-columns:repeat(3,1fr);row-gap:18px}}
@media (max-width:560px){.mgrid.six{grid-template-columns:repeat(2,1fr)}}
.mgrid.six .m{padding:0 18px;border-left:1px solid var(--line)}
.mgrid.six .m:first-child{border-left:0;padding-left:0}
/* Four-up metric row (Search Console) and the Google connection controls */
.mgrid.four{grid-template-columns:repeat(4,1fr);gap:0}
.mgrid.four .m{padding:0 18px;border-left:1px solid var(--line)}
.mgrid.four .m:first-child{border-left:0;padding-left:0}
@media (max-width:760px){.mgrid.four{grid-template-columns:repeat(2,1fr);row-gap:18px}.mgrid.four .m:nth-child(3){border-left:0;padding-left:0}}
.connect .ghost-metrics.four{grid-template-columns:repeat(4,1fr)}
@media (max-width:560px){.connect .ghost-metrics.four{grid-template-columns:1fr 1fr}}
.g-mark{display:inline-grid;place-items:center;width:18px;height:18px;border-radius:50%;background:var(--card);color:var(--ink);font:700 11px/1 var(--f-num, inherit);border:1px solid var(--line);margin-right:2px}
.btn-primary .g-mark{border-color:transparent}
.g-actions{display:flex;flex-wrap:wrap;align-items:center;gap:10px 14px}
.g-actions .muted{font-size:12.5px}
.g-head-actions{display:inline-flex;align-items:center;gap:8px}
.g-picker{display:grid;gap:14px;margin-top:16px;padding:18px;border-radius:16px;background:var(--raise);border:1px solid var(--line)}
.g-who{display:flex;align-items:center;gap:8px;font-size:13.5px;color:var(--muted)}
.g-who b{color:var(--ink);font-weight:600}
.g-row{display:grid;grid-template-columns:minmax(180px,240px) 1fr;gap:14px;align-items:center}
.g-row > span{font-size:13.5px;font-weight:600;color:var(--ink);display:grid;gap:2px}
.g-row small{font-size:12px;font-weight:400;color:var(--dim)}
.g-row select{height:44px;min-width:0;width:100%;border:1px solid var(--line);border-radius:12px;background:var(--card);padding:0 12px;font-size:13.5px}
.g-row select:focus{outline:0;border-color:var(--gold-line);box-shadow:0 0 0 4px rgba(201,161,58,.12)}
@media (max-width:640px){.g-row{grid-template-columns:1fr;gap:6px}}
.g-tables{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:24px}
.g-tables .ptable td:first-child{max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
@media (max-width:900px){.g-tables{grid-template-columns:1fr}}
.m-lbl{display:flex;align-items:center;gap:5px;font-size:14px;color:var(--ink-2)}
.m-lbl .i{font-size:9px;color:var(--dim);cursor:help;vertical-align:super}
.m-val{display:flex;align-items:baseline;gap:9px;margin:6px 0 4px;flex-wrap:wrap}
.m-val .v{font-family:var(--f-num);font-size:34px;font-weight:500;line-height:1.05;letter-spacing:-.04em;color:var(--ink)}
.m-val .v.sm{font-size:28px}
.m-sub{display:flex;align-items:baseline;gap:6px;flex-wrap:wrap;font-size:13px;color:var(--muted)}
.m-sub b{font-family:var(--f-num);font-weight:600;color:var(--ink)}
.m-sub .delta{font-size:12px}
.pnl hr{border:0;border-top:1px solid var(--line);margin:20px 0}
.ptable{width:100%;border-collapse:collapse;font-size:13.5px;margin-top:18px}
.ptable th{font-size:12px;font-weight:500;color:var(--muted);text-align:left;padding:0 0 8px;border-bottom:1px solid var(--line)}
.ptable th.r,.ptable td.r{text-align:right}
.ptable td{padding:8px 0;border-bottom:1px solid #f1efea}
.ptable tr:last-child td{border-bottom:0}
.ptable td .num{font-weight:600}
.ptable tr.off td{color:var(--dim)}
.ptable .plat{display:flex;align-items:center;gap:9px}
.ptable .pdot{width:8px;height:8px;border-radius:50%;background:var(--ink);flex:none}
.ptable tr.off .pdot{background:#dcd9d1}
.ptable .delta{margin-left:6px;font-size:11.5px}
.donut-row{display:flex;align-items:center;gap:16px}
.donut{width:62px;height:62px;flex:none;transform:rotate(-90deg)}
.connect{display:grid;gap:10px;align-content:start}
.connect p{font-size:13.5px;color:var(--muted);line-height:1.55}
.connect .ghost-metrics{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:4px 0 6px}
.connect .ghost-metrics div{font-size:13px;color:var(--dim)}
.connect .ghost-metrics b{display:block;font-family:var(--f-num);font-size:26px;font-weight:500;color:#d8d5cd;letter-spacing:-.03em}
.connect code{font-size:11.5px;background:var(--raise);border:1px solid var(--line);border-radius:6px;padding:2px 6px;color:var(--ink-2);word-break:break-all}
.stack{display:flex;height:10px;border-radius:10px;overflow:hidden;background:#efede7;margin-top:8px}
.stack i{display:block;height:100%}
.stack-legend{display:flex;flex-wrap:wrap;gap:6px 14px;margin-top:10px;font-size:12px;color:var(--muted)}
.stack-legend span{display:inline-flex;align-items:center;gap:6px}
.stack-legend s{width:8px;height:8px;border-radius:2px;display:inline-block;text-decoration:none}
.chart-wrap{position:relative;margin-top:18px}
.chart-tip{position:absolute;pointer-events:none;background:var(--ink);color:#fff;font-size:12px;line-height:1.45;padding:8px 10px;border-radius:9px;box-shadow:var(--shadow-lg);white-space:nowrap;transform:translate(-50%,-110%);opacity:0;transition:opacity .12s}
.chart-tip b{font-family:var(--f-num)}
.chart-tip .p{color:#bdbab2}
.chart-legend{display:flex;gap:16px;font-size:12px;color:var(--muted);margin-top:8px}
.chart-legend span{display:inline-flex;align-items:center;gap:6px}
.chart-legend i{width:14px;height:0;border-top:2px solid var(--ink)}
.chart-legend i.prev{border-top:2px dashed #b9b6ae}
.traffic-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:24px;margin-top:6px}
@media (max-width:1080px){.traffic-grid{grid-template-columns:1fr}}
.mini-list{display:grid;gap:8px;font-size:13px}
.mini-list .row{display:grid;grid-template-columns:1fr auto;gap:10px;align-items:center}
.mini-list .row .bar{grid-column:1/-1;height:4px;border-radius:4px;background:#efede7;overflow:hidden}
.mini-list .row .bar i{display:block;height:100%;background:var(--ink);border-radius:4px}
.mini-list .row .t{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.tag-src{display:inline-flex;align-items:center;gap:6px;height:22px;padding:0 9px;border-radius:999px;background:var(--raise);border:1px solid var(--line);font-size:11.5px;color:var(--muted)}
.tag-src.sandbox{background:var(--warn-soft);border-color:#f0d9b0;color:var(--warn)}
.metric-toggle{display:inline-flex;gap:4px}
.metric-toggle button{border:1px solid var(--line);background:var(--card);height:28px;padding:0 10px;border-radius:8px;font-size:12px;font-weight:600;color:var(--muted)}
.metric-toggle button[aria-pressed="true"]{border-color:var(--ink);color:var(--ink)}

/* ===================== Site Explorer reports ===================== */
.rep-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:14px}
.rep-grid.three{grid-template-columns:repeat(3,minmax(0,1fr))}
@media (max-width:1080px){.rep-grid,.rep-grid.three{grid-template-columns:1fr}}
.rep-grid > .pnl,.rep-full{min-width:0}
.rep-full{margin-top:14px}
.legend{display:flex;flex-wrap:wrap;gap:6px 16px;font-size:12px;color:var(--muted);margin-top:10px}
.legend span{display:inline-flex;align-items:center;gap:6px}
.legend i{width:10px;height:10px;border-radius:3px;display:inline-block}
.legend button{border:1px solid var(--line);background:var(--card);height:28px;padding:0 10px;border-radius:8px;font-size:12px;font-weight:600;color:var(--muted);display:inline-flex;align-items:center;gap:6px}
.legend button[aria-pressed="true"]{border-color:var(--ink);color:var(--ink)}
.legend button[aria-pressed="false"] i{opacity:.35}
.split-bar{display:flex;height:12px;border-radius:12px;overflow:hidden;background:#efede7}
.split-bar i{display:block;height:100%}
.kv{display:grid;grid-template-columns:1fr auto auto;gap:8px 14px;align-items:center;font-size:13.5px;margin-top:12px}
.kv .k{display:flex;align-items:center;gap:8px}
.kv .k s{width:9px;height:9px;border-radius:3px;display:inline-block}
.kv .v{font-family:var(--f-num);font-weight:600;text-align:right}
.kv .m{color:var(--muted);text-align:right;font-family:var(--f-num)}
.hist{display:flex;align-items:flex-end;gap:6px;height:140px;margin-top:14px}
.hist .col{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;height:100%;justify-content:flex-end}
.hist .col i{display:block;width:100%;border-radius:5px 5px 2px 2px;background:var(--ink);min-height:2px;transition:height .6s var(--ease)}
.hist .col span{font-size:10.5px;color:var(--dim);font-family:var(--f-num)}
.hist .col b{font-size:11px;font-family:var(--f-num);font-weight:600}
.flag{font-size:16px;line-height:1}
.chip-you{display:inline-flex;height:18px;padding:0 7px;border-radius:999px;background:var(--gold-soft);color:#7a5d14;font-size:11px;font-weight:600;align-items:center;margin-left:6px}
.chip-comp{display:inline-flex;height:18px;padding:0 7px;border-radius:999px;background:var(--info-soft);color:var(--info);font-size:11px;font-weight:600;align-items:center;margin-left:6px}
.sent{display:flex;gap:4px;height:10px;border-radius:10px;overflow:hidden}
.sent i{display:block;height:100%}
.gap-row{display:grid;grid-template-columns:1fr auto;gap:10px;padding:10px 0;border-top:1px solid #f1efea;font-size:13.5px}
.gap-row:first-child{border-top:0}
.def{font-size:12.5px;color:var(--muted);line-height:1.55}
.def b{color:var(--ink);font-weight:600}
.truncate{max-width:360px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:bottom}

/* Footer */
.foot{max-width:var(--mw);margin:56px auto 0;padding:24px 28px 40px;border-top:1px solid var(--line);display:flex;flex-wrap:wrap;justify-content:space-between;gap:12px;font-size:12.5px;color:var(--dim)}
@media (prefers-reduced-motion:reduce){*,*::before,*::after{animation:none!important;transition:none!important}}

/* Data sources strip (top of Overview / Organic search / Backlinks) */
.src-strip{margin:0 0 18px}
.src-details{border:1px solid var(--line);border-radius:14px;background:var(--card)}
.src-details summary{display:flex;flex-wrap:wrap;align-items:center;gap:8px 10px;padding:11px 16px;cursor:pointer;list-style:none;font-size:13px}
.src-details summary::-webkit-details-marker{display:none}
.src-title{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--dim)}
.src-market{font-size:12.5px;color:var(--muted)}
.src-toggle{margin-left:auto;font-size:12.5px;font-weight:600;color:var(--ink);border-bottom:1px solid var(--line)}
.src-details[open] summary{border-bottom:1px solid var(--line)}
.src-details .table-wrap{padding:4px 16px 0}
.src-table td{vertical-align:top;font-size:13px;line-height:1.5}
.src-report{font-weight:600;color:var(--ink);width:26%}
.src-what{color:var(--muted)}
.src-kind{display:inline-block;font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:2px 7px;border-radius:6px;margin-right:8px;background:var(--raise);border:1px solid var(--line);color:var(--ink-2)}
.src-kind.k-dfs{background:#f4efe2;border-color:var(--gold-line);color:#7a5d17}
.src-name{color:var(--ink-2)}
.src-foot{padding:10px 16px 14px;font-size:12px;color:var(--dim);margin:0}
@media (max-width:720px){.src-table thead{display:none}.src-table tr{display:grid;gap:4px;padding:10px 0}.src-table td{padding:0;border:0}.src-report{width:auto}.src-toggle{margin-left:0}}
