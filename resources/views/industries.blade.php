<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Intelligence for 15 Industries — X Platforms | Pre-trained Industry Models</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="description" content="X Platforms delivers pre-trained AI intelligence models for 15 industries — Retail, Banking, Healthcare, Telecom, Travel, Insurance, Manufacturing, Energy and more. Industry-specific predictions, playbooks, and benchmarks out of the box.">
<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
<link rel="canonical" href="{{ url('/industries') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --body-bg:linear-gradient(135deg,#dbeafe 0%,#bfdbfe 50%,#dbeafe 100%);
  --bg:#dbeafe;--bg2:rgba(219,234,254,.55);--bg3:rgba(191,219,254,.5);--card:rgba(255,255,255,.55);--card-h:rgba(255,255,255,.68);
  --blue:#2563eb;--blue2:#1d4ed8;--blue-g:rgba(37,99,235,.08);--blue-g2:rgba(37,99,235,.04);
  --cyan:#0891b2;--violet:#7c3aed;--emerald:#059669;--amber:#b45309;--rose:#e11d48;
  --white:#0f172a;--g100:#334155;--g200:#475569;--g300:#64748b;--g400:#94a3b8;--g500:#cbd5e1;--g600:#e2e8f0;
  --brd:rgba(37,99,235,.16);--brd2:rgba(37,99,235,.24);
  --glow:0 0 60px rgba(37,99,235,.12);--glow2:0 0 80px rgba(37,99,235,.08);
  --glass-blur:blur(22px) saturate(1.5);
  --chrome-bg:rgba(219,234,254,.85);--chrome-bg-soft:rgba(219,234,254,.65);--panel-tint:rgba(219,234,254,.55);
  --f1:'Outfit',system-ui,sans-serif;--f2:'Source Serif 4',Georgia,serif;--fm:'IBM Plex Mono',monospace;
  --ease:cubic-bezier(.16,1,.3,1);--mw:1200px;
}
body.theme-dark{
  --body-bg:#05070e;
  --bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;
  --blue:#4f8fff;--blue2:#2563eb;--blue-g:rgba(79,143,255,.08);--blue-g2:rgba(79,143,255,.04);
  --cyan:#38bdf8;--violet:#818cf8;--emerald:#34d399;--amber:#fbbf24;--rose:#f472b6;
  --white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;
  --brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.15);
  --glow:0 0 60px rgba(79,143,255,.12);--glow2:0 0 40px rgba(79,143,255,.08);
  --glass-blur:none;
  --chrome-bg:rgba(5,7,14,.97);--chrome-bg-soft:rgba(5,7,14,.75);--panel-tint:rgba(9,13,25,.6);
}
.why-strip,.ind-card,.compare-strip,.pb,.foot{backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur)}
html{scroll-behavior:smooth}
body{background:var(--body-bg);background-attachment:fixed;color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
canvas#neural{position:fixed;inset:0;z-index:0;pointer-events:none}

/* NAV */
.nav{position:fixed;top:0;width:100%;z-index:100;border-bottom:1px solid var(--brd)}
.nav-bg{position:absolute;inset:0;background:var(--chrome-bg-soft);backdrop-filter:blur(40px)}
.nav-in{position:relative;max-width:var(--mw);margin:0 auto;padding:0 40px;height:64px;display:flex;align-items:center;justify-content:space-between}
.logo{display:flex;align-items:center;gap:11px;font-weight:700;font-size:15px;letter-spacing:-.3px}
.logo-m{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--blue),var(--violet));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff;box-shadow:0 0 20px rgba(79,143,255,.25)}
.nav-l{display:flex;align-items:center;gap:32px;list-style:none}
.nav-l a{font-size:13.5px;font-weight:450;color:var(--g200);transition:color .25s}
.nav-l a:hover{color:var(--white)}
.nav-cta{padding:8px 22px;background:var(--blue);color:#fff!important;border-radius:8px;font-weight:600!important;font-size:13px!important;box-shadow:0 0 24px rgba(79,143,255,.25);transition:all .25s;cursor:pointer;border:none;font-family:var(--f1)}
.nav-cta:hover{box-shadow:0 0 36px rgba(79,143,255,.4)}
.appearance-toggle{width:36px;height:36px;border-radius:50%;background:var(--bg3);border:1px solid var(--brd2);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--g200);transition:all .2s;flex-shrink:0;padding:0}
.appearance-toggle:hover{color:var(--blue);border-color:var(--blue)}
.appearance-toggle svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2}
.nav-login{display:inline-flex;align-items:center;gap:7px;padding:8px 18px;border:1px solid var(--g500);color:var(--g200)!important;border-radius:8px;font-weight:500!important;font-size:13px!important;transition:all .25s;background:transparent;cursor:pointer;font-family:var(--f1)}
.nav-login:hover{border-color:var(--brd2);color:var(--white)!important;background:rgba(79,143,255,.05)}

/* HERO */
.hero{position:relative;z-index:1;padding:136px 40px 72px;text-align:center;max-width:var(--mw);margin:0 auto}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:24px;letter-spacing:.5px}
.breadcrumb a{color:var(--g300);transition:color .2s}.breadcrumb a:hover{color:var(--blue)}
.hero-badge{display:inline-flex;align-items:center;gap:8px;padding:5px 14px 5px 8px;background:var(--blue-g);border:1px solid var(--brd2);border-radius:100px;font-family:var(--fm);font-size:10.5px;letter-spacing:.8px;text-transform:uppercase;color:var(--blue);margin-bottom:22px;opacity:0;animation:fadeUp .6s var(--ease) .1s forwards}
.badge-dot{width:7px;height:7px;border-radius:50%;background:var(--emerald);box-shadow:0 0 10px var(--emerald);animation:blink 2.5s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
.hero h1{font-weight:800;font-size:clamp(34px,4.8vw,60px);line-height:1.08;letter-spacing:-2px;max-width:780px;margin:0 auto 18px;opacity:0;animation:fadeUp .7s var(--ease) .2s forwards}
.hero h1 span{background:linear-gradient(135deg,var(--blue),var(--cyan),var(--violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{font-size:16px;line-height:1.75;color:var(--g200);max-width:520px;margin:0 auto 40px;font-weight:350;opacity:0;animation:fadeUp .7s var(--ease) .35s forwards}

/* WHY PRE-TRAINED */
.why-strip{position:relative;z-index:1;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--bg2);padding:48px 40px}
.why-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:0}
.wc{padding:24px 32px;border-right:1px solid var(--brd);text-align:center}
.wc:last-child{border-right:none}
.wc-icon{width:40px;height:40px;border-radius:10px;background:var(--blue-g);border:1px solid var(--brd2);display:flex;align-items:center;justify-content:center;margin:0 auto 14px}
.wc-icon svg{width:18px;height:18px;stroke:var(--blue);fill:none;stroke-width:1.6}
.wc h3{font-size:14px;font-weight:600;margin-bottom:6px}
.wc p{font-size:12.5px;color:var(--g300);line-height:1.6}

/* FILTER BAR */
.filter-bar{position:relative;z-index:1;max-width:var(--mw);margin:0 auto;padding:48px 40px 24px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.filter-label{font-family:var(--fm);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g400);margin-right:4px}
.filter-btn{padding:7px 16px;border-radius:8px;border:1px solid var(--brd);background:transparent;color:var(--g300);font-size:13px;cursor:pointer;transition:all .2s;font-family:var(--f1)}
.filter-btn:hover{border-color:var(--brd2);color:var(--g100)}
.filter-btn.active{border-color:var(--blue);background:var(--blue-g);color:var(--white)}
.filter-count{margin-left:auto;font-family:var(--fm);font-size:11px;color:var(--g400)}

/* INDUSTRIES GRID */
.ind-section{position:relative;z-index:1;max-width:var(--mw);margin:0 auto;padding:0 40px 80px}
.ind-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.ind-card{background:var(--card);border:1px solid var(--brd);border-radius:18px;overflow:hidden;transition:all .35s var(--ease);cursor:pointer;position:relative}
.ind-card:hover{border-color:var(--brd2);box-shadow:var(--glow2);transform:translateY(-3px)}
.ind-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;opacity:0;transition:opacity .35s}
.ind-card:hover::before{opacity:1}
.ic-top{padding:28px 28px 20px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
.ic-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid var(--brd)}
.ic-icon svg{width:22px;height:22px;fill:none;stroke-width:1.5}
.ic-badge{font-family:var(--fm);font-size:9.5px;letter-spacing:.8px;text-transform:uppercase;padding:4px 10px;border-radius:5px;border:1px solid var(--brd);color:var(--g400);white-space:nowrap}
.ic-body{padding:0 28px 20px}
.ic-name{font-weight:700;font-size:18px;letter-spacing:-.3px;margin-bottom:6px}
.ic-desc{font-size:13px;color:var(--g300);line-height:1.6;margin-bottom:18px}
.ic-stats{display:flex;gap:0;border-top:1px solid var(--brd);margin:0 -28px;padding:16px 28px 0}
.ics{flex:1;text-align:center}
.ics:not(:last-child){border-right:1px solid var(--brd)}
.ics-n{font-weight:700;font-size:18px;letter-spacing:-.5px}
.ics-l{font-family:var(--fm);font-size:9px;color:var(--g500);letter-spacing:.8px;text-transform:uppercase;margin-top:2px}
.ic-footer{padding:16px 28px;border-top:1px solid var(--brd);display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,.01)}
.ic-footer-tag{font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:.5px}
.ic-link{font-size:12.5px;font-weight:500;display:flex;align-items:center;gap:5px;transition:gap .2s}
.ind-card:hover .ic-link{gap:8px}
.ic-link svg{width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2;transition:transform .2s}

/* COMPARE STRIP */
.compare-strip{position:relative;z-index:1;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--bg2);padding:72px 40px}
.cs-in{max-width:var(--mw);margin:0 auto}
.cs-table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid var(--brd);border-radius:16px;overflow:hidden;margin-top:48px}
.cs-table th{background:var(--bg3);padding:14px 20px;text-align:left;font-family:var(--fm);font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--g400);border-bottom:1px solid var(--brd)}
.cs-table th:not(:first-child){text-align:center}
.cs-table td{padding:14px 20px;border-bottom:1px solid var(--brd);font-size:13.5px;background:var(--card)}
.cs-table tr:last-child td{border-bottom:none}
.cs-table tr:hover td{background:var(--card-h)}
.cs-table td:not(:first-child){text-align:center;font-family:var(--fm);font-size:12px}
.td-pos{color:var(--emerald)}
.td-ind{font-weight:600;color:var(--g100)}
.td-acc{color:var(--blue)}

/* PLAYBOOK SECTION */
.playbook-section{position:relative;z-index:1;max-width:var(--mw);margin:0 auto;padding:80px 40px}
.pb-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:48px}
.pb{background:var(--card);border:1px solid var(--brd);border-radius:16px;padding:28px;transition:all .3s}
.pb:hover{border-color:var(--brd2);background:var(--card-h)}
.pb-num{font-family:var(--f2);font-size:36px;font-style:italic;font-weight:600;color:var(--blue);margin-bottom:12px;line-height:1}
.pb h3{font-size:16px;font-weight:600;margin-bottom:8px;letter-spacing:-.1px}
.pb p{font-size:13.5px;color:var(--g300);line-height:1.65}

/* CTA */
.cta{position:relative;z-index:1;padding:100px 40px;text-align:center;border-top:1px solid var(--brd)}
.cta::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:400px;background:radial-gradient(circle,rgba(79,143,255,.05),transparent 65%);pointer-events:none}
.cta h2{font-weight:800;font-size:clamp(28px,3.8vw,48px);letter-spacing:-1.5px;line-height:1.1;margin-bottom:14px;position:relative}
.cta h2 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.cta p{font-size:16px;color:var(--g300);max-width:420px;margin:0 auto 32px;line-height:1.7;position:relative}
.cta-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.btn-fill{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;border-radius:12px;font-weight:600;font-size:14.5px;border:none;cursor:pointer;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 32px rgba(79,143,255,.3);font-family:var(--f1);transition:all .25s}
.btn-fill:hover{transform:translateY(-2px);box-shadow:0 8px 48px rgba(79,143,255,.4)}
.btn-g{display:inline-flex;align-items:center;padding:14px 32px;border-radius:12px;font-weight:500;font-size:14.5px;background:rgba(255,255,255,.04);color:var(--g100);border:1px solid var(--g500);cursor:pointer;font-family:var(--f1);transition:all .25s}
.btn-g:hover{border-color:var(--blue);background:var(--blue-g2)}

/* FOOTER */
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2)}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:260px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}

/* SHARED */
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:12px;display:flex;align-items:center;gap:10px}
.stag::before{content:'';width:16px;height:1px;background:var(--blue)}
.sh{font-weight:700;font-size:clamp(24px,2.8vw,36px);letter-spacing:-1.2px;line-height:1.15;margin-bottom:12px}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}
.hidden{display:none}

@media(max-width:1024px){.ind-grid{grid-template-columns:1fr 1fr}.why-in{grid-template-columns:1fr 1fr}.wc{border-bottom:1px solid var(--brd)}.pb-grid{grid-template-columns:1fr}.foot-in{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.nav-l{display:none}.nav-in{padding:0 20px}.hero,.ind-section,.playbook-section,.compare-strip,.cta,.filter-bar{padding-left:20px;padding-right:20px}.hero{padding-top:110px}.ind-grid{grid-template-columns:1fr}.why-in{grid-template-columns:1fr}.cs-table{font-size:11px}.cs-table th,.cs-table td{padding:10px 12px}.foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}}
.nav-ham{display:none;flex-direction:column;gap:5px;background:none;border:none;cursor:pointer;padding:8px;position:relative;z-index:102}
.nav-ham span{display:block;width:22px;height:2px;background:var(--g200);border-radius:2px;transition:all .3s}
.nav-ham.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
.nav-ham.open span:nth-child(2){opacity:0}
.nav-ham.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
.nav-mob{display:none;position:fixed;inset:0;z-index:99;background:var(--chrome-bg);backdrop-filter:blur(20px);flex-direction:column;align-items:center;justify-content:center;overflow-y:auto}
.nav-mob.open{display:flex}.nav-mob-inner{width:100%;max-width:340px;padding:24px}
.nav-mob-link{display:block;font-size:18px;font-weight:500;color:var(--g200);padding:16px 0;border-bottom:1px solid var(--brd);transition:color .2s;text-align:center}
.nav-mob-link:first-child{border-top:1px solid var(--brd)}.nav-mob-link:hover{color:var(--white)}
.nav-mob-cta{display:block;text-align:center;margin-top:28px;padding:14px 0;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;border-radius:10px;font-weight:600;font-size:15px}
.nav-mob-cta:hover{opacity:.9;color:#fff}
.nav-mob-close{position:absolute;top:20px;right:20px;background:none;border:none;cursor:pointer;color:var(--g300);font-size:32px;line-height:1;z-index:100;width:44px;height:44px;display:flex;align-items:center;justify-content:center}
@media(max-width:640px){.nav-ham{display:flex}}
</style>
</head>
<body>
<script>try{if(localStorage.getItem('xp-appearance')==='dark')document.body.classList.add('theme-dark')}catch(e){}</script>
<canvas id="neural"></canvas>

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
      <button type="button" class="appearance-toggle" id="appearanceToggle" onclick="toggleAppearance()" title="Switch appearance" aria-label="Switch appearance"></button>
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
    <button type="button" class="appearance-toggle" id="appearanceToggleMob" onclick="toggleAppearance()" title="Switch appearance" aria-label="Switch appearance" style="margin-top:20px"></button>
    <a class="nav-mob-cta" href="{{ route('book-demo') }}">Book a Demo</a>
  </div>
</div>
<script>function toggleNav(){var m=document.getElementById('navMob'),h=document.getElementById('navHam');m.classList.toggle('open');h.classList.toggle('open');document.body.style.overflow=m.classList.contains('open')?'hidden':''}</script>

<header class="hero">
  <nav class="breadcrumb"><a href="{{ url('/') }}">Home</a> / <strong>Industries</strong></nav>
  <div class="hero-badge"><span class="badge-dot"></span> 15 Pre-trained Industry Models</div>
  <h1>AI intelligence built for <span>your industry</span></h1>
  <p>Generic AI needs months of training before it understands your business. X Platforms ships with pre-trained models, benchmarks, and playbooks for 15 verticals — ready from day one.</p>
</header>

<!-- WHY PRE-TRAINED -->
<div class="why-strip">
<div class="why-in rv">
  <div class="wc"><div class="wc-icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><h3>Ready from day one</h3><p>Pre-trained on millions of industry-specific customer journeys. No cold start period.</p></div>
  <div class="wc"><div class="wc-icon"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div><h3>Industry benchmarks included</h3><p>Know immediately how your churn, CLV, and conversion rates compare to sector norms.</p></div>
  <div class="wc"><div class="wc-icon"><svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div><h3>Sector-specific playbooks</h3><p>Retention, acquisition, and upsell strategies proven in your vertical — not generic advice.</p></div>
  <div class="wc"><div class="wc-icon"><svg viewBox="0 0 24 24"><path d="M4 4v5h5M20 20v-5h-5"/><path d="M20.49 9A9 9 0 005.64 5.64L4 4m16 16l-1.64-1.64A9 9 0 013.51 15"/></svg></div><h3>Gets smarter in your context</h3><p>Models fine-tune on your own data continuously — beating generic AI within 30 days.</p></div>
</div>
</div>

<!-- FILTER -->
<div class="filter-bar rv">
  <span class="filter-label">Filter by:</span>
  <button class="filter-btn active" data-cat="all" onclick="filterIndustries('all')">All Industries</button>
  <button class="filter-btn" data-cat="b2c" onclick="filterIndustries('b2c')">B2C</button>
  <button class="filter-btn" data-cat="b2b" onclick="filterIndustries('b2b')">B2B</button>
  <button class="filter-btn" data-cat="subscription" onclick="filterIndustries('subscription')">Subscription</button>
  <button class="filter-btn" data-cat="transaction" onclick="filterIndustries('transaction')">Transactional</button>
  <button class="filter-btn" data-cat="regulated" onclick="filterIndustries('regulated')">Regulated</button>
  <span class="filter-count" id="filterCount">15 industries</span>
</div>

<!-- INDUSTRIES GRID -->
<section class="ind-section">
<div class="ind-grid" id="indGrid">

  <!-- RETAIL -->
  <div class="ind-card rv" data-cats="b2c transaction subscription">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#4f8fff,#38bdf8);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(79,143,255,.08);border-color:rgba(79,143,255,.15)"><svg viewBox="0 0 24 24" stroke="#4f8fff"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18"/><line x1="16" y1="10" x2="8" y2="10"/></svg></div>
      <span class="ic-badge">B2C · Transactional</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Retail & E-Commerce</div>
      <div class="ic-desc">Predict purchase intent, recover abandoned carts, identify cross-sell moments, and prevent churn before loyalty erodes.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#4f8fff">34%</div><div class="ics-l">Avg Churn Drop</div></div>
        <div class="ics"><div class="ics-n" style="color:#38bdf8">3.2×</div><div class="ics-l">Revenue Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#818cf8">96%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Cart recovery · CLV · Cross-sell</span>
      <a href="#" class="ic-link" style="color:#4f8fff">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- BANKING -->
  <div class="ind-card rv" data-cats="b2c b2b regulated subscription">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#818cf8,#f472b6);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(129,140,248,.08);border-color:rgba(129,140,248,.15)"><svg viewBox="0 0 24 24" stroke="#818cf8"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/></svg></div>
      <span class="ic-badge">B2C · Regulated</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Banking & Finance</div>
      <div class="ic-desc">Detect at-risk accounts weeks before they switch, identify upsell windows, and deliver next-best-product recommendations at the right moment.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#818cf8">28%</div><div class="ics-l">Churn Reduction</div></div>
        <div class="ics"><div class="ics-n" style="color:#f472b6">2.8×</div><div class="ics-l">Revenue Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#38bdf8">94%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Churn · Product upsell · CLV</span>
      <a href="{{ route('banking') }}" class="ic-link" style="color:#818cf8">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- HEALTHCARE -->
  <div class="ind-card rv" data-cats="b2c regulated subscription">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#34d399,#38bdf8);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(52,211,153,.08);border-color:rgba(52,211,153,.15)"><svg viewBox="0 0 24 24" stroke="#34d399"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
      <span class="ic-badge">B2C · Regulated</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Healthcare</div>
      <div class="ic-desc">Prevent care gaps, predict patient disengagement, improve follow-up compliance, and reduce costly readmissions through early AI intervention.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#34d399">28%</div><div class="ics-l">Compliance Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#38bdf8">2.1×</div><div class="ics-l">Patient LTV</div></div>
        <div class="ics"><div class="ics-n" style="color:#818cf8">93%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Care gaps · Compliance · Re-engagement</span>
      <a href="#" class="ic-link" style="color:#34d399">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- TELECOM -->
  <div class="ind-card rv" data-cats="b2c subscription">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#fbbf24,#fb923c);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(251,191,36,.08);border-color:rgba(251,191,36,.15)"><svg viewBox="0 0 24 24" stroke="#fbbf24"><path d="M5 12.55a11 11 0 0114 0M1.42 9a16 16 0 0121.16 0M8.53 16.11a6 6 0 016.95 0M12 20h.01"/></svg></div>
      <span class="ic-badge">B2C · Subscription</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Telecom</div>
      <div class="ic-desc">Predict subscriber churn 30 days ahead, detect plan mismatch before it becomes frustration, and deploy proactive retention offers at the optimal moment.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#fbbf24">31%</div><div class="ics-l">Churn Reduction</div></div>
        <div class="ics"><div class="ics-n" style="color:#fb923c">3.5×</div><div class="ics-l">Revenue Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#4f8fff">95%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Churn · ARPU · Plan optimisation</span>
      <a href="#" class="ic-link" style="color:#fbbf24">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- TRAVEL -->
  <div class="ind-card rv" data-cats="b2c transaction">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#38bdf8,#818cf8);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(56,189,248,.08);border-color:rgba(56,189,248,.15)"><svg viewBox="0 0 24 24" stroke="#38bdf8"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg></div>
      <span class="ic-badge">B2C · Transactional</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Travel & Hospitality</div>
      <div class="ic-desc">Predict booking intent from search behaviour, personalise offers for high-value loyalty members, and re-engage lapsed travellers at the right moment.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#38bdf8">26%</div><div class="ics-l">Loyalty Churn Drop</div></div>
        <div class="ics"><div class="ics-n" style="color:#818cf8">2.9×</div><div class="ics-l">Revenue Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#34d399">97%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Booking intent · Loyalty · CLV</span>
      <a href="#" class="ic-link" style="color:#38bdf8">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- INSURANCE -->
  <div class="ind-card rv" data-cats="b2c regulated subscription">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#f472b6,#818cf8);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(244,114,182,.08);border-color:rgba(244,114,182,.15)"><svg viewBox="0 0 24 24" stroke="#f472b6"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
      <span class="ic-badge">B2C · Regulated</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Insurance</div>
      <div class="ic-desc">Identify renewal risk early, detect competitor shopping behaviour, and trigger proactive bundle offers before policies lapse.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#f472b6">24%</div><div class="ics-l">Churn Reduction</div></div>
        <div class="ics"><div class="ics-n" style="color:#818cf8">2.6×</div><div class="ics-l">Revenue Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#38bdf8">94%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Renewal · Bundle · Risk scoring</span>
      <a href="#" class="ic-link" style="color:#f472b6">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- MANUFACTURING -->
  <div class="ind-card rv" data-cats="b2b transaction">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#fb923c,#fbbf24);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(251,146,60,.08);border-color:rgba(251,146,60,.15)"><svg viewBox="0 0 24 24" stroke="#fb923c"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg></div>
      <span class="ic-badge">B2B · Transactional</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Manufacturing</div>
      <div class="ic-desc">Predict distributor attrition, detect demand shifts, and identify reorder windows before competitors do. Built for B2B buying cycles.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#fb923c">22%</div><div class="ics-l">Attrition Drop</div></div>
        <div class="ics"><div class="ics-n" style="color:#fbbf24">2.4×</div><div class="ics-l">Revenue Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#4f8fff">91%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Distributor · Demand · Reorder</span>
      <a href="#" class="ic-link" style="color:#fb923c">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- ENERGY -->
  <div class="ind-card rv" data-cats="b2c regulated subscription">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#60a5fa,#38bdf8);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(96,165,250,.08);border-color:rgba(96,165,250,.15)"><svg viewBox="0 0 24 24" stroke="#60a5fa"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
      <span class="ic-badge">B2C · Regulated</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Energy & Utilities</div>
      <div class="ic-desc">Detect switching intent from smart meter and billing behaviour, match customers to optimal tariffs, and reduce churn during renewal seasons.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#60a5fa">19%</div><div class="ics-l">Switch Reduction</div></div>
        <div class="ics"><div class="ics-n" style="color:#38bdf8">2.4×</div><div class="ics-l">Revenue Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#34d399">92%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Switch risk · Tariff match · Renewal</span>
      <a href="#" class="ic-link" style="color:#60a5fa">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- EDUCATION -->
  <div class="ind-card rv" data-cats="b2c subscription">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#34d399,#818cf8);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(52,211,153,.08);border-color:rgba(52,211,153,.15)"><svg viewBox="0 0 24 24" stroke="#34d399"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></div>
      <span class="ic-badge">B2C · Subscription</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Education</div>
      <div class="ic-desc">Predict student dropout risk weeks in advance, personalise learning pathways, and trigger the right intervention at the critical Week 4 engagement window.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#34d399">41%</div><div class="ics-l">Dropout Reduction</div></div>
        <div class="ics"><div class="ics-n" style="color:#818cf8">68%</div><div class="ics-l">Completion Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#38bdf8">91%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Dropout · Engagement · Completion</span>
      <a href="#" class="ic-link" style="color:#34d399">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- REAL ESTATE -->
  <div class="ind-card rv" data-cats="b2c transaction">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#fbbf24,#f472b6);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(251,191,36,.08);border-color:rgba(251,191,36,.15)"><svg viewBox="0 0 24 24" stroke="#fbbf24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
      <span class="ic-badge">B2C · Transactional</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Real Estate</div>
      <div class="ic-desc">Identify purchase-ready buyers from search intensity, match them to optimal listings, and alert agents the moment a lead enters their buying window.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#fbbf24">91%</div><div class="ics-l">Intent Accuracy</div></div>
        <div class="ics"><div class="ics-n" style="color:#f472b6">2.7×</div><div class="ics-l">Conversion Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#4f8fff">89%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Buyer intent · Agent alerts · Matching</span>
      <a href="#" class="ic-link" style="color:#fbbf24">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- MEDIA -->
  <div class="ind-card rv" data-cats="b2c subscription">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#818cf8,#38bdf8);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(129,140,248,.08);border-color:rgba(129,140,248,.15)"><svg viewBox="0 0 24 24" stroke="#818cf8"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg></div>
      <span class="ic-badge">B2C · Subscription</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Media & Entertainment</div>
      <div class="ic-desc">Detect content fatigue before subscribers cancel, personalise recommendations to re-engage, and predict renewal probability at the 30, 60, and 90-day windows.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#818cf8">38%</div><div class="ics-l">Churn Reduction</div></div>
        <div class="ics"><div class="ics-n" style="color:#38bdf8">2.5×</div><div class="ics-l">Engagement Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#34d399">93%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Churn · Content affinity · Renewal</span>
      <a href="#" class="ic-link" style="color:#818cf8">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- PHARMA -->
  <div class="ind-card rv" data-cats="b2b regulated">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#34d399,#60a5fa);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(52,211,153,.08);border-color:rgba(52,211,153,.15)"><svg viewBox="0 0 24 24" stroke="#34d399"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg></div>
      <span class="ic-badge">B2B · Regulated</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Pharma & Life Sciences</div>
      <div class="ic-desc">Score HCP receptivity to clinical evidence, predict prescriber behaviour shifts, and orchestrate compliant engagement sequences that drive therapeutic adoption.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#34d399">84%</div><div class="ics-l">HCP Receptivity</div></div>
        <div class="ics"><div class="ics-n" style="color:#60a5fa">6.2×</div><div class="ics-l">Engagement ROI</div></div>
        <div class="ics"><div class="ics-n" style="color:#818cf8">91%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">HCP engagement · Rx prediction · MLR</span>
      <a href="#" class="ic-link" style="color:#34d399">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- AUTOMOTIVE -->
  <div class="ind-card rv" data-cats="b2c transaction">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#f472b6,#fbbf24);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(244,114,182,.08);border-color:rgba(244,114,182,.15)"><svg viewBox="0 0 24 24" stroke="#f472b6"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8l5 3-5 3zM1 12l4 4"/></svg></div>
      <span class="ic-badge">B2C · Transactional</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Automotive</div>
      <div class="ic-desc">Predict purchase readiness from configurator and dealer visit behaviour, identify trade-in timing, and close deals before leads go cold.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#f472b6">88%</div><div class="ics-l">Intent Prediction</div></div>
        <div class="ics"><div class="ics-n" style="color:#fbbf24">2.3×</div><div class="ics-l">Conversion Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#4f8fff">90%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Purchase intent · Trade-in · Finance</span>
      <a href="#" class="ic-link" style="color:#f472b6">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- FOOD & BEV -->
  <div class="ind-card rv" data-cats="b2c transaction subscription">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#fb923c,#f472b6);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(251,146,60,.08);border-color:rgba(251,146,60,.15)"><svg viewBox="0 0 24 24" stroke="#fb923c"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div>
      <span class="ic-badge">B2C · Transactional</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Food & Beverage</div>
      <div class="ic-desc">Detect frequency decline before loyalty erodes, identify optimal cross-sell moments, and re-engage lapsed customers before competitors win them permanently.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#fb923c">29%</div><div class="ics-l">Churn Reduction</div></div>
        <div class="ics"><div class="ics-n" style="color:#f472b6">1.8×</div><div class="ics-l">AOV Lift</div></div>
        <div class="ics"><div class="ics-n" style="color:#34d399">89%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Frequency · Cross-sell · Win-back</span>
      <a href="#" class="ic-link" style="color:#fb923c">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

  <!-- CONSTRUCTION -->
  <div class="ind-card rv" data-cats="b2b transaction">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#60a5fa,#818cf8);opacity:0;transition:opacity .35s"></div>
    <div class="ic-top">
      <div class="ic-icon" style="background:rgba(96,165,250,.08);border-color:rgba(96,165,250,.15)"><svg viewBox="0 0 24 24" stroke="#60a5fa"><path d="M12 2L2 7l10 5 10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>
      <span class="ic-badge">B2B · Transactional</span>
    </div>
    <div class="ic-body">
      <div class="ic-name">Construction</div>
      <div class="ic-desc">Predict client pipeline growth, detect supply chain disruption signals, and identify upsell opportunities from project scope expansion patterns.</div>
      <div class="ic-stats">
        <div class="ics"><div class="ics-n" style="color:#60a5fa">78%</div><div class="ics-l">Upsell Accuracy</div></div>
        <div class="ics"><div class="ics-n" style="color:#818cf8">94%</div><div class="ics-l">Client Retention</div></div>
        <div class="ics"><div class="ics-n" style="color:#34d399">88%</div><div class="ics-l">Model Accuracy</div></div>
      </div>
    </div>
    <div class="ic-footer">
      <span class="ic-footer-tag">Pipeline · Supply chain · Upsell</span>
      <a href="#" class="ic-link" style="color:#60a5fa">Explore <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>

</div>
</section>

<!-- BENCHMARK COMPARE TABLE -->
<div class="compare-strip">
<div class="cs-in rv">
  <div style="margin-bottom:48px"><div class="stag">Benchmarks</div><h2 class="sh">Industry performance benchmarks</h2><p style="font-size:15px;color:var(--g300);max-width:460px;line-height:1.7">Average outcomes across X Platforms deployments in each vertical. Updated quarterly.</p></div>
  <table class="cs-table">
    <thead><tr><th>Industry</th><th>Avg Churn Reduction</th><th>Revenue Lift</th><th>Time to ROI</th><th>Model Accuracy</th></tr></thead>
    <tbody>
      <tr><td class="td-ind">Retail & E-Commerce</td><td class="td-pos">34%</td><td class="td-pos">3.2×</td><td class="td-acc">76 days</td><td class="td-acc">96%</td></tr>
      <tr><td class="td-ind">Banking & Finance</td><td class="td-pos">28%</td><td class="td-pos">2.8×</td><td class="td-acc">84 days</td><td class="td-acc">94%</td></tr>
      <tr><td class="td-ind">Healthcare</td><td class="td-pos">22%</td><td class="td-pos">2.1×</td><td class="td-acc">91 days</td><td class="td-acc">93%</td></tr>
      <tr><td class="td-ind">Telecom</td><td class="td-pos">31%</td><td class="td-pos">3.5×</td><td class="td-acc">67 days</td><td class="td-acc">95%</td></tr>
      <tr><td class="td-ind">Travel & Hospitality</td><td class="td-pos">26%</td><td class="td-pos">2.9×</td><td class="td-acc">72 days</td><td class="td-acc">97%</td></tr>
      <tr><td class="td-ind">Insurance</td><td class="td-pos">24%</td><td class="td-pos">2.6×</td><td class="td-acc">88 days</td><td class="td-acc">94%</td></tr>
      <tr><td class="td-ind">Education</td><td class="td-pos">41%</td><td class="td-pos">2.2×</td><td class="td-acc">82 days</td><td class="td-acc">91%</td></tr>
      <tr><td class="td-ind">Media & Entertainment</td><td class="td-pos">38%</td><td class="td-pos">2.5×</td><td class="td-acc">61 days</td><td class="td-acc">93%</td></tr>
      <tr><td class="td-ind">Energy & Utilities</td><td class="td-pos">19%</td><td class="td-pos">2.4×</td><td class="td-acc">94 days</td><td class="td-acc">92%</td></tr>
      <tr><td class="td-ind">Pharma & Life Sciences</td><td class="td-pos">—</td><td class="td-pos">6.2× ROI</td><td class="td-acc">78 days</td><td class="td-acc">91%</td></tr>
    </tbody>
  </table>
</div>
</div>

<!-- HOW PLAYBOOKS WORK -->
<section class="playbook-section">
  <div style="margin-bottom:48px" class="rv"><div class="stag">Industry Playbooks</div><h2 class="sh">What "industry-specific" actually means</h2><p style="font-size:15px;color:var(--g300);max-width:460px;line-height:1.7">Every industry gets more than a renamed dashboard. Here's what's different under the hood.</p></div>
  <div class="pb-grid rv">
    <div class="pb"><div class="pb-num">01</div><h3>Pre-trained on your vertical's data</h3><p>Models trained on millions of customer journeys specific to your industry — not generic behaviour. A banking churn model understands rate-shopping signals. A retail model understands seasonal buying cycles. They're not interchangeable.</p></div>
    <div class="pb"><div class="pb-num">02</div><h3>Industry-calibrated thresholds</h3><p>What counts as "high churn risk" is different in telecom (30-day window) versus real estate (12-month window). X Platforms ships with industry-calibrated activation thresholds so alerts are meaningful, not noisy.</p></div>
    <div class="pb"><div class="pb-num">03</div><h3>Sector-specific data connectors prioritised</h3><p>Banking deployments pre-configure core banking and wealth management connections. Retail prioritises Shopify, Klaviyo, and POS. Each industry gets the connector stack it actually needs surfaced first.</p></div>
    <div class="pb"><div class="pb-num">04</div><h3>Regulatory compliance built into the model</h3><p>Healthcare models are HIPAA-compliant. Financial models respect FCA and ASIC guidance. Pharma models include MLR-approval checks. Compliance isn't an add-on — it's built into how predictions are generated and how actions are triggered.</p></div>
  </div>
</section>

<!-- CTA -->
<section class="cta">
  <h2 class="rv">Your industry. <span>Your predictions.</span></h2>
  <p class="rv">Pick your vertical and see exactly what X Platforms would predict for your customers — in 30 minutes or less.</p>
  <div class="cta-btns rv">
    <a href="{{ route('simulator') }}" class="btn-fill">Try the Industry Simulator</a>
    <a href="{{ route('pricing') }}" class="btn-g">View Pricing</a>
  </div>
</section>

<!-- FOOTER -->
<footer class="foot"><div class="foot-in">
  <div><a href="{{ url('/') }}" class="logo"><img src="{{ asset('images/xplatforms_logo.jpeg') }}" alt="X Platforms" style="height:32px;width:auto;display:block"></a><p class="foot-desc">The world's first 8-layer AI intelligence engine.</p></div>
  <div class="foot-c"><h5>Product</h5><a href="{{ route('platform.architecture') }}">Architecture</a><a href="#">Integrations</a><a href="{{ route('industries') }}">Industries</a><a href="{{ route('pricing') }}">Pricing</a></div>
  <div class="foot-c"><h5>Company</h5><a href="{{ route('about') }}">About</a><a href="{{ route('careers') }}">Careers</a><a href="{{ route('blog') }}">Blog</a><a href="{{ route('contact') }}">Contact</a></div>
  <div class="foot-c"><h5>Resources</h5><a href="#">Documentation</a><a href="{{ route('case-studies') }}">Case Studies</a><a href="#">API Reference</a><a href="{{ route('security') }}">Security</a></div>
</div><div class="foot-b"><span>&copy; {{ date('Y') }} X Platforms.</span><span><a href="{{ route('privacy') }}" style="color:inherit">Privacy</a> &middot; <a href="{{ route('terms') }}" style="color:inherit">Terms</a> &middot; <a href="{{ route('security') }}" style="color:inherit">Security</a></span></div></footer>

<script>
const APPEARANCE_SUN = '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>';
const APPEARANCE_MOON = '<svg viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>';
function applyAppearanceIcon(){
  const isDark = document.body.classList.contains('theme-dark');
  const icon = isDark ? APPEARANCE_MOON : APPEARANCE_SUN;
  ['appearanceToggle','appearanceToggleMob'].forEach(function(id){
    const el = document.getElementById(id);
    if (el) el.innerHTML = icon;
  });
}
function toggleAppearance(){
  const isDark = document.body.classList.toggle('theme-dark');
  localStorage.setItem('xp-appearance', isDark ? 'dark' : 'light');
  applyAppearanceIcon();
}
applyAppearanceIcon();

const cv=document.getElementById('neural'),cx=cv.getContext('2d');let W,H,nd=[];
function rsz(){W=cv.width=innerWidth;H=cv.height=innerHeight}addEventListener('resize',rsz);rsz();
for(let i=0;i<55;i++)nd.push({x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.28,vy:(Math.random()-.5)*.28,r:Math.random()*1.4+.6,p:Math.random()*6.28});
let mmx=-1e3,mmy=-1e3;document.addEventListener('mousemove',e=>{mmx=e.clientX;mmy=e.clientY});
(function draw(){cx.clearRect(0,0,W,H);nd.forEach((n,i)=>{n.x+=n.vx;n.y+=n.vy;n.p+=.01;if(n.x<0||n.x>W)n.vx*=-1;if(n.y<0||n.y>H)n.vy*=-1;nd.forEach((m,j)=>{if(j<=i)return;const d=Math.hypot(n.x-m.x,n.y-m.y);if(d<150){cx.beginPath();cx.moveTo(n.x,n.y);cx.lineTo(m.x,m.y);cx.strokeStyle=`rgba(79,143,255,${(1-d/150)*.09})`;cx.lineWidth=.5;cx.stroke()}});const g=Math.hypot(n.x-mmx,n.y-mmy)<160?(1-Math.hypot(n.x-mmx,n.y-mmy)/160)*.4:0;cx.beginPath();cx.arc(n.x,n.y,n.r+Math.sin(n.p)*.35,0,6.28);cx.fillStyle=`rgba(79,143,255,${.2+g})`;cx.fill()});requestAnimationFrame(draw)})();

document.querySelectorAll('.ind-card').forEach(card=>{
  const bar=card.querySelector('div[style*="height:2px"]');
  card.addEventListener('mouseenter',()=>{if(bar)bar.style.opacity='1'});
  card.addEventListener('mouseleave',()=>{if(bar)bar.style.opacity='0'});
});

function filterIndustries(cat){
  document.querySelectorAll('.filter-btn').forEach(b=>b.classList.toggle('active',b.dataset.cat===cat));
  const cards=document.querySelectorAll('.ind-card');
  let visible=0;
  cards.forEach(c=>{const show=cat==='all'||c.dataset.cats.includes(cat);c.style.display=show?'':'none';if(show)visible++});
  document.getElementById('filterCount').textContent=`${visible} ${visible===1?'industry':'industries'}`;
}

const obs=new IntersectionObserver(e=>{e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('vis');obs.unobserve(x.target)}})},{threshold:.08,rootMargin:'0px 0px -40px 0px'});
document.querySelectorAll('.rv').forEach(el=>obs.observe(el));
addEventListener('scroll',()=>{document.querySelector('.nav-bg').style.background=scrollY>40?'var(--chrome-bg)':'var(--chrome-bg-soft)'});
</script>
</body>
</html>
