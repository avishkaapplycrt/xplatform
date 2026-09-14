<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book a Demo &mdash; X Platforms AI Intelligence Engine</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="description" content="Book a personalised 30-minute demo of X Platforms. Connect your data, see live AI predictions for your customers, and understand the ROI opportunity within one session.">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
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
  --glow:0 0 60px rgba(79,143,255,.12);--glow2:0 0 80px rgba(79,143,255,.08);
  --glass-blur:none;
  --chrome-bg:rgba(5,7,14,.97);--chrome-bg-soft:rgba(5,7,14,.75);--panel-tint:rgba(9,13,25,.6);
}
.demo-quote,.form-card,.fs-detail,.alt-section,.alt-card,.foot{backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur)}
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

/* PAGE LAYOUT */
.page{position:relative;z-index:1;min-height:100vh;padding:100px 40px 80px;max-width:var(--mw);margin:0 auto}
.page-grid{display:grid;grid-template-columns:1fr 480px;gap:64px;align-items:start}

/* LEFT SIDE */
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:24px;letter-spacing:.5px}
.breadcrumb a{color:var(--g300)}.breadcrumb a:hover{color:var(--blue)}
.demo-badge{display:inline-flex;align-items:center;gap:8px;padding:5px 14px 5px 8px;background:var(--blue-g);border:1px solid var(--brd2);border-radius:100px;font-family:var(--fm);font-size:10.5px;letter-spacing:.8px;text-transform:uppercase;color:var(--blue);margin-bottom:20px}
.badge-pulse{width:7px;height:7px;border-radius:50%;background:var(--emerald);box-shadow:0 0 10px var(--emerald);animation:blink 2.5s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
.demo-left h1{font-weight:800;font-size:clamp(32px,3.8vw,50px);line-height:1.08;letter-spacing:-2px;margin-bottom:16px}
.demo-left h1 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.demo-left > p{font-size:16px;color:var(--g200);line-height:1.75;max-width:460px;font-weight:350;margin-bottom:36px}

/* WHAT TO EXPECT */
.expect-title{font-family:var(--fm);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g500);margin-bottom:16px}
.expect-list{display:flex;flex-direction:column;gap:14px;margin-bottom:40px}
.expect-item{display:flex;align-items:flex-start;gap:14px}
.ei-num{width:28px;height:28px;border-radius:8px;background:var(--blue-g);border:1px solid var(--brd2);display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:10px;color:var(--blue);font-weight:600;flex-shrink:0;margin-top:1px}
.ei-body{}.ei-title{font-size:14px;font-weight:600;margin-bottom:2px}
.ei-desc{font-size:13px;color:var(--g300);line-height:1.55}

/* SOCIAL PROOF */
.proof-row{display:flex;gap:0;border:1px solid var(--brd);border-radius:12px;overflow:hidden;margin-bottom:40px}
.proof-item{flex:1;padding:18px 16px;text-align:center;border-right:1px solid var(--brd)}
.proof-item:last-child{border-right:none}
.proof-n{font-weight:800;font-size:22px;letter-spacing:-1px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.proof-l{font-family:var(--fm);font-size:9.5px;color:var(--g400);letter-spacing:1px;text-transform:uppercase;margin-top:3px}

/* TESTIMONIAL */
.demo-quote{padding:20px 24px;border-radius:12px;background:var(--card);border:1px solid var(--brd);border-left:3px solid var(--blue)}
.dq-text{font-family:var(--f2);font-size:14px;font-style:italic;line-height:1.65;color:var(--g100);margin-bottom:12px}
.dq-author{display:flex;align-items:center;gap:10px}
.dq-av{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--violet));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;color:#fff}
.dq-name{font-size:12.5px;font-weight:600}.dq-role{font-size:11px;color:var(--g400)}

/* FORM CARD */
.form-card{background:var(--card);border:1px solid var(--brd2);border-radius:20px;overflow:hidden;position:sticky;top:88px;box-shadow:0 0 60px rgba(79,143,255,.08)}
.form-card-head{padding:28px 32px 24px;border-bottom:1px solid var(--brd);background:linear-gradient(135deg,rgba(79,143,255,.06),rgba(129,140,248,.03))}
.fch-title{font-weight:700;font-size:18px;letter-spacing:-.3px;margin-bottom:4px}
.fch-sub{font-size:13.5px;color:var(--g300)}
.fch-badges{display:flex;gap:8px;margin-top:14px;flex-wrap:wrap}
.fch-badge{font-family:var(--fm);font-size:10px;padding:3px 10px;border-radius:5px;background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.15);color:var(--emerald);letter-spacing:.3px}
.form-body{padding:28px 32px}
.form-step{display:none}.form-step.active{display:block}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px}
.form-group{margin-bottom:16px}
.fg-label{display:block;font-size:12.5px;font-weight:500;color:var(--g200);margin-bottom:6px;letter-spacing:.1px}
.fg-input,.fg-select,.fg-textarea{width:100%;background:var(--bg3);border:1px solid var(--g500);border-radius:9px;padding:11px 14px;color:var(--white);font-family:var(--f1);font-size:13.5px;outline:none;transition:border-color .2s}
.fg-input:focus,.fg-select:focus,.fg-textarea:focus{border-color:var(--blue)}
.fg-input::placeholder,.fg-textarea::placeholder{color:var(--g600)}
.fg-input.error,.fg-select.error,.fg-textarea.error{border-color:var(--rose)!important}
.fg-error{font-size:11px;color:var(--rose);margin-top:4px;display:none}
.fg-select{-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;cursor:pointer}
.fg-select option{background:var(--bg3)}
.fg-textarea{resize:vertical;min-height:80px}
.form-submit{width:100%;padding:14px;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;border:none;border-radius:10px;font-weight:700;font-size:15px;cursor:pointer;font-family:var(--f1);transition:all .25s;margin-top:4px;display:flex;align-items:center;justify-content:center;gap:8px}
.form-submit:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(79,143,255,.35)}
.form-submit:disabled{opacity:.6;cursor:not-allowed;transform:none}
.form-note{text-align:center;font-size:12px;color:var(--g400);margin-top:12px}
.form-note a{color:var(--blue)}

/* STEP INDICATOR */
.step-ind{display:flex;align-items:center;gap:8px;margin-bottom:24px}
.si-step{width:8px;height:8px;border-radius:50%;background:var(--g600);transition:all .3s}
.si-step.active{background:var(--blue);box-shadow:0 0 10px rgba(79,143,255,.4);width:24px;border-radius:4px}
.si-step.done{background:var(--emerald)}
.si-label{font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:.5px;margin-left:4px}

/* CALENDAR / TIME */
.time-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:8px}
.time-slot{padding:10px 8px;border-radius:8px;border:1px solid var(--g500);background:var(--bg3);text-align:center;cursor:pointer;transition:all .2s;font-size:12px;color:var(--g200)}
.time-slot:hover{border-color:var(--brd2);color:var(--white)}
.time-slot.sel{border-color:var(--blue);background:var(--blue-g);color:var(--blue)}
.time-slot.unavail{opacity:.3;cursor:not-allowed;pointer-events:none}
.date-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.date-nav button{background:transparent;border:1px solid var(--brd);border-radius:6px;width:28px;height:28px;color:var(--g300);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s}
.date-nav button:hover{border-color:var(--brd2);color:var(--white)}
.date-nav span{font-weight:600;font-size:14px}
.date-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:16px}
.date-cell{aspect-ratio:1;display:flex;align-items:center;justify-content:center;border-radius:6px;font-size:12px;cursor:pointer;transition:all .2s;color:var(--g300)}
.date-cell:hover:not(.unavail):not(.empty){background:var(--blue-g);color:var(--white);border:1px solid var(--brd2)}
.date-cell.sel{background:var(--blue);color:#fff!important}
.date-cell.today{color:var(--blue);font-weight:600}
.date-cell.unavail{opacity:.25;cursor:not-allowed}
.date-cell.empty{cursor:default}
.date-header{font-family:var(--fm);font-size:9px;color:var(--g500);letter-spacing:1px;text-align:center;margin-bottom:6px}

/* SUCCESS */
.form-success{display:none;padding:40px 32px;text-align:center}
.fs-icon{width:64px;height:64px;border-radius:50%;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);display:flex;align-items:center;justify-content:center;margin:0 auto 20px}
.fs-icon svg{width:28px;height:28px;stroke:var(--emerald);fill:none;stroke-width:2}
.fs-title{font-weight:700;font-size:20px;margin-bottom:8px;letter-spacing:-.3px}
.fs-text{font-size:14px;color:var(--g300);line-height:1.7;max-width:320px;margin:0 auto 24px}
.fs-detail{background:var(--bg3);border:1px solid var(--brd);border-radius:12px;padding:16px;text-align:left;margin-bottom:20px}
.fsd-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--brd);font-size:13px}
.fsd-row:last-child{border-bottom:none}
.fsd-label{color:var(--g400)}.fsd-val{color:var(--g100);font-weight:500}

/* ALT SECTION */
.alt-section{position:relative;z-index:1;border-top:1px solid var(--brd);background:var(--bg2);padding:64px 40px}
.alt-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.alt-card{background:var(--card);border:1px solid var(--brd);border-radius:16px;padding:28px;transition:all .3s;text-align:center}
.alt-card:hover{border-color:var(--brd2);background:var(--card-h)}
.alt-icon{width:48px;height:48px;border-radius:12px;background:var(--blue-g);border:1px solid var(--brd2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
.alt-icon svg{width:22px;height:22px;stroke:var(--blue);fill:none;stroke-width:1.6}
.alt-card h3{font-size:16px;font-weight:600;margin-bottom:8px}
.alt-card p{font-size:13px;color:var(--g300);line-height:1.6;margin-bottom:16px}
.alt-link{font-size:13.5px;color:var(--blue);font-weight:500;display:inline-flex;align-items:center;gap:4px}

/* FOOTER */
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2)}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:260px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}

.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}
@media(max-width:1024px){.page-grid{grid-template-columns:1fr}.form-card{position:static}.alt-in{grid-template-columns:1fr}.foot-in{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.nav-l{display:none}.nav-in,.page,.alt-section{padding-left:20px;padding-right:20px}.page{padding-top:100px}.form-row{grid-template-columns:1fr}.foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}.time-grid{grid-template-columns:1fr 1fr}}
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

<section class="page">
  <div class="page-grid">

    <!-- LEFT -->
    <div class="demo-left">
      <nav class="breadcrumb"><a href="{{ route('home') }}">Home</a> / <strong>Book a Demo</strong></nav>
      <div class="demo-badge"><span class="badge-pulse"></span> 30-Minute Live Session &middot; No Pitch Deck</div>
      <h1>See X Platforms work on <span>your customers</span></h1>
      <p>No slides. No generic walkthrough. We connect your real data &mdash; or the closest representative dataset you have &mdash; and show you live predictions for your actual customer base.</p>

      <div class="expect-title">What happens in your demo</div>
      <div class="expect-list rv">
        <div class="expect-item"><div class="ei-num">01</div><div class="ei-body"><div class="ei-title">We connect your data</div><div class="ei-desc">Before the session, we connect to one or two of your existing platforms. You'll see predictions from your actual customers &mdash; not sample data.</div></div></div>
        <div class="expect-item"><div class="ei-num">02</div><div class="ei-body"><div class="ei-title">The AI finds your patterns</div><div class="ei-desc">We run the 8-layer engine live and show you what it discovers &mdash; hidden churn signals, cross-sell opportunities, and customers you're about to lose.</div></div></div>
        <div class="expect-item"><div class="ei-num">03</div><div class="ei-body"><div class="ei-title">You see the ROI</div><div class="ei-desc">We calculate the projected annual impact based on your customer volume, churn rate, and revenue &mdash; specific to your business.</div></div></div>
        <div class="expect-item"><div class="ei-num">04</div><div class="ei-body"><div class="ei-title">We answer every question</div><div class="ei-desc">Integration timeline, compliance requirements, pricing, and the proof of concept process. No question is off the table.</div></div></div>
      </div>

      <div class="proof-row rv">
        <div class="proof-item"><div class="proof-n">82</div><div class="proof-l">Days avg to ROI</div></div>
        <div class="proof-item"><div class="proof-n">97%</div><div class="proof-l">Prediction accuracy</div></div>
        <div class="proof-item"><div class="proof-n">15.6&times;</div><div class="proof-l">Avg Year 1 ROI</div></div>
        <div class="proof-item"><div class="proof-n">97%</div><div class="proof-l">Renew after Year 1</div></div>
      </div>

      <div class="demo-quote rv">
        <div class="dq-text">&ldquo;The demo blew us away. They connected our Shopify store before the call and showed us which customers were about to churn &mdash; including three we had no idea about. We signed within a week.&rdquo;</div>
        <div class="dq-author"><div class="dq-av">PK</div><div><div class="dq-name">Priya Kapoor</div><div class="dq-role">CPO, MockMaster &middot; 41% churn reduction</div></div></div>
      </div>
    </div>

    <!-- RIGHT: FORM -->
    <div>
      <div class="form-card rv" style="opacity:1;transform:none">
        <div class="form-card-head">
          <div class="fch-title">Book your demo</div>
          <div class="fch-sub">Response within 2 business hours. Most demos happen within 3 days.</div>
          <div class="fch-badges">
            <span class="fch-badge">Free &middot; No commitment</span>
            <span class="fch-badge">30 minutes</span>
            <span class="fch-badge">Live on your data</span>
          </div>
        </div>

        <div class="form-body" id="formBody">
          <div class="step-ind">
            <div class="si-step active" id="si0"></div>
            <div class="si-step" id="si1"></div>
            <div class="si-step" id="si2"></div>
            <span class="si-label" id="siLabel">Step 1 of 3 &mdash; About you</span>
          </div>

          <!-- STEP 1: ABOUT YOU -->
          <div class="form-step active" id="step1">
            <div class="form-row">
              <div class="form-group">
                <label class="fg-label">First Name *</label>
                <input class="fg-input" id="fn" type="text" placeholder="Jane">
                <div class="fg-error" id="fn-err">First name is required.</div>
              </div>
              <div class="form-group">
                <label class="fg-label">Last Name *</label>
                <input class="fg-input" id="ln" type="text" placeholder="Smith">
                <div class="fg-error" id="ln-err">Last name is required.</div>
              </div>
            </div>
            <div class="form-group">
              <label class="fg-label">Work Email *</label>
              <input class="fg-input" id="em" type="email" placeholder="jane@company.com">
              <div class="fg-error" id="em-err">Please enter a valid email address.</div>
            </div>
            <div class="form-group">
              <label class="fg-label">Company Name *</label>
              <input class="fg-input" id="co" type="text" placeholder="Your company">
              <div class="fg-error" id="co-err">Company name is required.</div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="fg-label">Job Title *</label>
                <input class="fg-input" id="jt" type="text" placeholder="VP Marketing">
                <div class="fg-error" id="jt-err">Job title is required.</div>
              </div>
              <div class="form-group">
                <label class="fg-label">Company Size *</label>
                <select class="fg-select" id="cs">
                  <option value="">Select size</option>
                  <option>1&ndash;50 employees</option>
                  <option>51&ndash;200 employees</option>
                  <option>201&ndash;1,000 employees</option>
                  <option>1,001&ndash;5,000 employees</option>
                  <option>5,000+ employees</option>
                </select>
                <div class="fg-error" id="cs-err">Please select a company size.</div>
              </div>
            </div>
            <div class="form-group">
              <label class="fg-label">Industry *</label>
              <select class="fg-select" id="ind">
                <option value="">Select your industry</option>
                <option>Retail &amp; E-Commerce</option><option>Banking &amp; Finance</option><option>Healthcare</option>
                <option>Telecom</option><option>Travel &amp; Hospitality</option><option>Insurance</option>
                <option>Manufacturing</option><option>Energy &amp; Utilities</option><option>Education / EdTech</option>
                <option>Real Estate</option><option>Media &amp; Entertainment</option><option>Pharma</option>
                <option>Automotive</option><option>Food &amp; Beverage</option><option>SaaS</option><option>Other</option>
              </select>
              <div class="fg-error" id="ind-err">Please select your industry.</div>
            </div>
            <button class="form-submit" onclick="goStep(2)">Continue <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></button>
          </div>

          <!-- STEP 2: YOUR SITUATION -->
          <div class="form-step" id="step2">
            <div class="form-group">
              <label class="fg-label">Monthly Active Customers</label>
              <select class="fg-select" id="mac">
                <option value="">Select range</option>
                <option>Under 1,000</option><option>1,000 &ndash; 10,000</option><option>10,000 &ndash; 100,000</option>
                <option>100,000 &ndash; 500,000</option><option>500,000 &ndash; 1M</option><option>1M+</option>
              </select>
            </div>
            <div class="form-group">
              <label class="fg-label">Monthly Revenue</label>
              <select class="fg-select" id="rev">
                <option value="">Select range</option>
                <option>Under $50K</option><option>$50K &ndash; $200K</option><option>$200K &ndash; $500K</option>
                <option>$500K &ndash; $2M</option><option>$2M &ndash; $10M</option><option>$10M+</option>
              </select>
            </div>
            <div class="form-group">
              <label class="fg-label">Primary challenge you want to solve</label>
              <select class="fg-select" id="chal">
                <option value="">Select your challenge</option>
                <option>Reducing customer churn</option><option>Improving conversion rates</option>
                <option>Increasing customer lifetime value</option><option>Unifying customer data</option>
                <option>Identifying upsell opportunities</option><option>Predicting customer behaviour</option>
                <option>Reducing acquisition cost</option><option>Other</option>
              </select>
            </div>
            <div class="form-group">
              <label class="fg-label">Which data sources do you currently use? (optional)</label>
              <input class="fg-input" id="ds" type="text" placeholder="e.g. Salesforce, Shopify, Mailchimp, Zendesk">
            </div>
            <div class="form-group">
              <label class="fg-label">Anything specific you'd like to see in the demo?</label>
              <textarea class="fg-textarea" id="notes" placeholder="e.g. Focus on churn prediction for our SaaS platform, or show how it integrates with Stripe..."></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:4px">
              <button type="button" onclick="goStep(1)" style="flex-shrink:0;padding:14px 20px;background:transparent;border:1px solid var(--g500);color:var(--g200);border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;font-family:var(--f1);white-space:nowrap">&larr; Back</button>
              <button class="form-submit" onclick="goStep(3)" style="flex:1;margin-top:0">Choose a Time <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></button>
            </div>
          </div>

          <!-- STEP 3: PICK A TIME -->
          <div class="form-step" id="step3">
            <div class="form-group">
              <label class="fg-label">Select a date</label>
              <div class="date-nav">
                <button type="button" onclick="changeMonth(-1)"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>
                <span id="monthLabel">May 2026</span>
                <button type="button" onclick="changeMonth(1)"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
              </div>
              <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:6px">
                <div class="date-header">S</div><div class="date-header">M</div><div class="date-header">T</div>
                <div class="date-header">W</div><div class="date-header">T</div><div class="date-header">F</div><div class="date-header">S</div>
              </div>
              <div class="date-grid" id="dateGrid"></div>
            </div>
            <div class="form-group" id="timeSection" style="display:none">
              <label class="fg-label">Available times <span style="font-family:var(--fm);font-size:10px;color:var(--g400)">(AEST)</span></label>
              <div class="time-grid" id="timeGrid">
                <div class="time-slot" onclick="selTime(this)">9:00 AM</div>
                <div class="time-slot" onclick="selTime(this)">9:30 AM</div>
                <div class="time-slot unavail">10:00 AM</div>
                <div class="time-slot" onclick="selTime(this)">10:30 AM</div>
                <div class="time-slot unavail">11:00 AM</div>
                <div class="time-slot" onclick="selTime(this)">11:30 AM</div>
                <div class="time-slot" onclick="selTime(this)">1:00 PM</div>
                <div class="time-slot unavail">1:30 PM</div>
                <div class="time-slot" onclick="selTime(this)">2:00 PM</div>
                <div class="time-slot" onclick="selTime(this)">2:30 PM</div>
                <div class="time-slot" onclick="selTime(this)">3:00 PM</div>
                <div class="time-slot unavail">3:30 PM</div>
              </div>
            </div>
            <div class="form-group">
              <label class="fg-label">Your timezone</label>
              <select class="fg-select" id="tz">
                <option>AEST (UTC+10) &mdash; Sydney, Melbourne</option>
                <option>AEDT (UTC+11) &mdash; Daylight saving</option>
                <option>SGT (UTC+8) &mdash; Singapore</option>
                <option>IST (UTC+5:30) &mdash; India</option>
                <option>GMT (UTC+0) &mdash; London</option>
                <option>EST (UTC-5) &mdash; New York</option>
                <option>PST (UTC-8) &mdash; Los Angeles</option>
              </select>
            </div>
            <div style="display:flex;gap:10px;margin-top:4px">
              <button type="button" onclick="goStep(2)" style="flex-shrink:0;padding:14px 20px;background:transparent;border:1px solid var(--g500);color:var(--g200);border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;font-family:var(--f1);white-space:nowrap">&larr; Back</button>
              <button type="button" class="form-submit" id="confirmBtn" onclick="confirmDemo()" style="flex:1;margin-top:0">Confirm Demo &rarr;</button>
            </div>
            <p class="form-note">By booking you agree to our <a href="#">Privacy Policy</a>. We'll send a calendar invite within 15 minutes.</p>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- ALTERNATIVE OPTIONS -->
<section class="alt-section">
  <div class="alt-in rv">
    <div class="alt-card">
      <div class="alt-icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
      <h3>Try the simulator first</h3>
      <p>See X Platforms process realistic customer journeys across all 15 industries. No signup needed.</p>
      <a href="{{ route('simulator') }}" class="alt-link">Launch simulator <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    <div class="alt-card">
      <div class="alt-icon"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg></div>
      <h3>Chat with our team</h3>
      <p>Have a quick question before committing to a demo? Message us directly and get a response within 2 hours.</p>
      <a href="#" class="alt-link">Start a conversation <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    <div class="alt-card">
      <div class="alt-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
      <h3>Read the case studies</h3>
      <p>See detailed results from MockMaster, ScoreMentor, and OneAustralia before your demo call.</p>
      <a href="{{ route('case-studies') }}" class="alt-link">View case studies <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
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

// Neural network background
const cv = document.getElementById('neural'), cx = cv.getContext('2d');
let W, H, nd = [];
function rsz() { W = cv.width = innerWidth; H = cv.height = innerHeight; }
addEventListener('resize', rsz); rsz();
for (let i = 0; i < 50; i++) nd.push({ x: Math.random()*W, y: Math.random()*H, vx: (Math.random()-.5)*.28, vy: (Math.random()-.5)*.28, r: Math.random()*1.4+.6, p: Math.random()*6.28 });
(function draw() {
  cx.clearRect(0, 0, W, H);
  nd.forEach((n, i) => {
    n.x += n.vx; n.y += n.vy; n.p += .01;
    if (n.x < 0 || n.x > W) n.vx *= -1;
    if (n.y < 0 || n.y > H) n.vy *= -1;
    nd.forEach((m, j) => {
      if (j <= i) return;
      const d = Math.hypot(n.x-m.x, n.y-m.y);
      if (d < 150) { cx.beginPath(); cx.moveTo(n.x,n.y); cx.lineTo(m.x,m.y); cx.strokeStyle=`rgba(79,143,255,${(1-d/150)*.09})`; cx.lineWidth=.5; cx.stroke(); }
    });
    cx.beginPath(); cx.arc(n.x, n.y, n.r+Math.sin(n.p)*.35, 0, 6.28); cx.fillStyle='rgba(79,143,255,.2)'; cx.fill();
  });
  requestAnimationFrame(draw);
})();

// Form logic
let selectedDate = null, selectedTime = null;

function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function setError(id, msg) {
  const el = document.getElementById(id);
  const err = document.getElementById(id + '-err');
  if (el) el.classList.add('error');
  if (err) { err.textContent = msg || err.textContent; err.style.display = 'block'; }
}

function clearError(id) {
  const el = document.getElementById(id);
  const err = document.getElementById(id + '-err');
  if (el) el.classList.remove('error');
  if (err) err.style.display = 'none';
}

// Live validation
['fn','ln','co','jt'].forEach(id => {
  document.getElementById(id)?.addEventListener('input', () => {
    if (document.getElementById(id).value.trim()) clearError(id);
  });
});
document.getElementById('em')?.addEventListener('input', () => {
  if (validateEmail(document.getElementById('em').value.trim())) clearError('em');
});

function goStep(n) {
  if (n === 2) {
    let ok = true;
    const fn = document.getElementById('fn').value.trim();
    const ln = document.getElementById('ln').value.trim();
    const em = document.getElementById('em').value.trim();
    const co = document.getElementById('co').value.trim();
    const jt = document.getElementById('jt').value.trim();
    const cs = document.getElementById('cs').value;
    const ind = document.getElementById('ind').value;

    if (!fn) { setError('fn', 'First name is required.'); ok = false; } else clearError('fn');
    if (!ln) { setError('ln', 'Last name is required.'); ok = false; } else clearError('ln');
    if (!em || !validateEmail(em)) { setError('em', 'Please enter a valid email address.'); ok = false; } else clearError('em');
    if (!co) { setError('co', 'Company name is required.'); ok = false; } else clearError('co');
    if (!jt) { setError('jt', 'Job title is required.'); ok = false; } else clearError('jt');
    if (!cs) { setError('cs', 'Please select a company size.'); ok = false; } else clearError('cs');
    if (!ind) { setError('ind', 'Please select your industry.'); ok = false; } else clearError('ind');
    if (!ok) return;
  }

  document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
  document.getElementById('step' + n).classList.add('active');

  const labels = ['About you', 'Your situation', 'Pick a time'];
  document.querySelectorAll('.si-step').forEach((s, i) => {
    s.classList.remove('active', 'done');
    if (i < n - 1) s.classList.add('done');
    if (i === n - 1) s.classList.add('active');
  });
  document.getElementById('siLabel').textContent = `Step ${n} of 3 — ${labels[n-1]}`;
  if (n === 3) buildCalendar();
}

// Calendar
let viewYear = 2026, viewMonth = 4;
const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

function changeMonth(dir) {
  viewMonth += dir;
  if (viewMonth > 11) { viewMonth = 0; viewYear++; }
  if (viewMonth < 0)  { viewMonth = 11; viewYear--; }
  buildCalendar();
}

function buildCalendar() {
  document.getElementById('monthLabel').textContent = `${monthNames[viewMonth]} ${viewYear}`;
  const grid = document.getElementById('dateGrid'); grid.innerHTML = '';
  const first = new Date(viewYear, viewMonth, 1).getDay();
  const days  = new Date(viewYear, viewMonth + 1, 0).getDate();
  const today = new Date();

  for (let i = 0; i < first; i++) {
    const d = document.createElement('div'); d.className = 'date-cell empty'; grid.appendChild(d);
  }
  for (let d = 1; d <= days; d++) {
    const cell = document.createElement('div'); cell.className = 'date-cell';
    const date = new Date(viewYear, viewMonth, d);
    const isPast    = date < new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const isWeekend = date.getDay() === 0 || date.getDay() === 6;
    const isToday   = date.toDateString() === today.toDateString();
    cell.textContent = d;
    if (isPast || isWeekend) {
      cell.classList.add('unavail');
    } else {
      if (isToday) cell.classList.add('today');
      cell.onclick = () => {
        document.querySelectorAll('.date-cell').forEach(c => c.classList.remove('sel'));
        cell.classList.add('sel');
        selectedDate = `${d} ${monthNames[viewMonth]} ${viewYear}`;
        document.getElementById('timeSection').style.display = 'block';
        selectedTime = null;
        document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('sel'));
      };
    }
    grid.appendChild(cell);
  }
}

function selTime(el) {
  document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('sel'));
  el.classList.add('sel');
  selectedTime = el.textContent;
}

function confirmDemo() {
  if (!selectedDate || !selectedTime) {
    alert('Please select a date and time for your demo.');
    return;
  }

  const btn = document.getElementById('confirmBtn');
  btn.disabled = true;
  btn.textContent = 'Saving…';

  const payload = {
    first_name:               document.getElementById('fn').value.trim(),
    last_name:                document.getElementById('ln').value.trim(),
    email:                    document.getElementById('em').value.trim(),
    company_name:             document.getElementById('co').value.trim(),
    job_title:                document.getElementById('jt').value.trim(),
    company_size:             document.getElementById('cs').value,
    industry:                 document.getElementById('ind').value,
    monthly_active_customers: document.getElementById('mac').value,
    monthly_revenue:          document.getElementById('rev').value,
    primary_challenge:        document.getElementById('chal').value,
    data_sources:             document.getElementById('ds').value.trim(),
    demo_notes:               document.getElementById('notes').value.trim(),
    demo_date:                selectedDate,
    demo_time:                selectedTime,
    timezone:                 document.getElementById('tz').value,
  };

  fetch('{{ route("book-demo.store") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify(payload),
  })
  .then(r => r.json())
  .then(data => {
    if (data.redirect) {
      window.location.href = data.redirect;
    } else {
      btn.disabled = false;
      btn.textContent = 'Confirm Demo →';
      alert(data.message || 'Something went wrong. Please try again.');
    }
  })
  .catch(err => {
    btn.disabled = false;
    btn.textContent = 'Confirm Demo →';
    alert('Something went wrong. Please try again.');
    console.error(err);
  });
}

buildCalendar();
addEventListener('scroll', () => {
  document.querySelector('.nav-bg').style.background = scrollY > 40 ? 'var(--chrome-bg)' : 'var(--chrome-bg-soft)';
});
const obs = new IntersectionObserver(e => {
  e.forEach(x => { if (x.isIntersecting) { x.target.classList.add('vis'); obs.unobserve(x.target); } });
}, { threshold: .1 });
document.querySelectorAll('.rv').forEach(el => obs.observe(el));
</script>
</body>
</html>
