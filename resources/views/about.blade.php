<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About X Platforms — Mission, Team & Story Behind the AI Intelligence Engine</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="description" content="X Platforms was founded on a simple belief: every business deserves to know what their customers are about to do next. Learn about our mission, team, and the journey behind the world's first 8-layer AI intelligence engine.">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ url('/about') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;
  --blue:#4f8fff;--blue2:#2563eb;--blue-g:rgba(79,143,255,.08);
  --cyan:#38bdf8;--violet:#818cf8;--emerald:#34d399;--amber:#fbbf24;--rose:#f472b6;
  --white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;
  --brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.15);
  --f1:'Outfit',system-ui,sans-serif;--f2:'Source Serif 4',Georgia,serif;--fm:'IBM Plex Mono',monospace;
  --ease:cubic-bezier(.16,1,.3,1);--mw:1200px;
}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
canvas#neural{position:fixed;inset:0;z-index:0;pointer-events:none}
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

/* HERO */
.hero{position:relative;z-index:1;padding:140px 40px 80px;text-align:center;max-width:var(--mw);margin:0 auto}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:24px;letter-spacing:.5px}
.breadcrumb a{color:var(--g300)}.breadcrumb a:hover{color:var(--blue)}
.hero h1{font-weight:800;font-size:clamp(36px,5vw,64px);line-height:1.06;letter-spacing:-2px;max-width:760px;margin:0 auto 20px}
.hero h1 span{background:linear-gradient(135deg,var(--blue),var(--cyan),var(--violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-sub{font-size:17px;line-height:1.75;color:var(--g200);max-width:560px;margin:0 auto;font-weight:350}

/* MISSION */
.mission{position:relative;z-index:1;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--bg2);padding:80px 40px}
.mission-in{max-width:800px;margin:0 auto;text-align:center}
.mission-label{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:20px;display:flex;align-items:center;justify-content:center;gap:10px}
.mission-label::before,.mission-label::after{content:'';width:40px;height:1px;background:var(--blue);opacity:.4}
.mission-quote{font-family:var(--f2);font-size:clamp(20px,2.8vw,30px);line-height:1.5;color:var(--white);font-style:italic;font-weight:400;margin-bottom:16px}
.mission-sub{font-size:15px;color:var(--g300);line-height:1.7}

/* STATS */
.stats-row{position:relative;z-index:1;max-width:var(--mw);margin:0 auto;padding:64px 40px;display:grid;grid-template-columns:repeat(4,1fr);gap:0;border-bottom:1px solid var(--brd)}
.stat-item{text-align:center;padding:0 20px;border-right:1px solid var(--brd)}
.stat-item:last-child{border-right:none}
.stat-n{font-weight:800;font-size:40px;letter-spacing:-1.5px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:6px}
.stat-l{font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:1.5px;text-transform:uppercase}

/* STORY */
.story{position:relative;z-index:1;max-width:var(--mw);margin:0 auto;padding:80px 40px;display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center}
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:14px;display:flex;align-items:center;gap:10px}
.stag::before{content:'';width:16px;height:1px;background:var(--blue)}
.story-content h2{font-weight:700;font-size:clamp(26px,3vw,38px);letter-spacing:-1.2px;line-height:1.15;margin-bottom:20px}
.story-content p{font-size:15px;color:var(--g200);line-height:1.8;margin-bottom:16px;font-weight:350}
.story-content p:last-child{margin-bottom:0}
.story-visual{background:var(--card);border:1px solid var(--brd);border-radius:20px;padding:32px;position:relative;overflow:hidden}
.story-visual::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--blue),var(--violet))}
.sv-year{font-family:var(--fm);font-size:10px;color:var(--blue);letter-spacing:1px;margin-bottom:6px}
.sv-event{font-weight:600;font-size:15px;margin-bottom:4px}
.sv-desc{font-size:13px;color:var(--g300);line-height:1.6;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--brd)}
.sv-desc:last-child{margin-bottom:0;padding-bottom:0;border-bottom:none}

/* VALUES */
.values-sec{position:relative;z-index:1;border-top:1px solid var(--brd);background:var(--bg2);padding:80px 40px}
.values-in{max-width:var(--mw);margin:0 auto}
.values-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:48px}
.val-card{background:var(--card);border:1px solid var(--brd);border-radius:16px;padding:32px;transition:all .3s}
.val-card:hover{border-color:var(--brd2);background:var(--card-h)}
.val-icon{width:44px;height:44px;border-radius:12px;background:var(--blue-g);border:1px solid var(--brd2);display:flex;align-items:center;justify-content:center;margin-bottom:18px}
.val-icon svg{width:20px;height:20px;stroke:var(--blue);fill:none;stroke-width:1.6}
.val-card h3{font-size:17px;font-weight:700;margin-bottom:8px;letter-spacing:-.2px}
.val-card p{font-size:13.5px;color:var(--g300);line-height:1.65}

/* TEAM */
.team-sec{position:relative;z-index:1;max-width:var(--mw);margin:0 auto;padding:80px 40px}
.team-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:48px}
.team-card{background:var(--card);border:1px solid var(--brd);border-radius:16px;padding:24px;text-align:center;transition:all .3s}
.team-card:hover{border-color:var(--brd2);background:var(--card-h);transform:translateY(-2px)}
.tc-avatar{width:64px;height:64px;border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:22px;color:#fff}
.tc-name{font-size:15px;font-weight:700;margin-bottom:3px;letter-spacing:-.1px}
.tc-role{font-size:12px;color:var(--blue);font-weight:500;margin-bottom:8px;font-family:var(--fm);letter-spacing:.3px}
.tc-prev{font-size:12px;color:var(--g400);line-height:1.5}
.tc-linkedin{display:inline-flex;align-items:center;gap:4px;margin-top:12px;font-family:var(--fm);font-size:10px;color:var(--g400);padding:4px 10px;border:1px solid var(--brd);border-radius:5px;transition:all .2s}
.tc-linkedin:hover{border-color:var(--brd2);color:var(--blue)}

/* INVESTORS */
.investors-sec{position:relative;z-index:1;border-top:1px solid var(--brd);background:var(--bg2);padding:64px 40px;text-align:center}
.investors-in{max-width:var(--mw);margin:0 auto}
.inv-grid{display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-top:40px}
.inv-card{background:var(--card);border:1px solid var(--brd);border-radius:12px;padding:20px 32px;transition:all .3s;min-width:160px;text-align:center}
.inv-card:hover{border-color:var(--brd2);background:var(--card-h)}
.inv-name{font-weight:700;font-size:15px;margin-bottom:3px}
.inv-type{font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:.5px}

/* PRESS */
.press-sec{position:relative;z-index:1;max-width:var(--mw);margin:0 auto;padding:80px 40px}
.press-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:48px}
.press-card{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:24px;transition:all .3s}
.press-card:hover{border-color:var(--brd2);background:var(--card-h)}
.pc-pub{font-family:var(--fm);font-size:10px;color:var(--blue);letter-spacing:1px;text-transform:uppercase;margin-bottom:10px}
.pc-headline{font-size:15px;font-weight:600;line-height:1.4;margin-bottom:8px;letter-spacing:-.1px}
.pc-date{font-family:var(--fm);font-size:10px;color:var(--g500);letter-spacing:.3px}

/* CTA */
.cta{position:relative;z-index:1;border-top:1px solid var(--brd);background:var(--bg2);padding:96px 40px;text-align:center}
.cta::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:360px;background:radial-gradient(circle,rgba(79,143,255,.05),transparent 65%);pointer-events:none}
.cta h2{font-weight:800;font-size:clamp(28px,3.5vw,44px);letter-spacing:-1.5px;margin-bottom:14px;position:relative}
.cta h2 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.cta p{font-size:16px;color:var(--g300);max-width:420px;margin:0 auto 30px;line-height:1.7;position:relative}
.btn-fill{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;border-radius:12px;font-weight:600;font-size:14.5px;border:none;cursor:pointer;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 32px rgba(79,143,255,.3);font-family:var(--f1);transition:all .25s}
.btn-fill:hover{transform:translateY(-2px);box-shadow:0 8px 48px rgba(79,143,255,.4)}
.btn-g{display:inline-flex;align-items:center;padding:14px 32px;border-radius:12px;font-weight:500;font-size:14.5px;background:rgba(255,255,255,.04);color:var(--g100);border:1px solid var(--g500);cursor:pointer;font-family:var(--f1);transition:all .25s}
.btn-g:hover{border-color:var(--blue);background:var(--blue-g)}

/* FOOTER */
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2)}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:260px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}

@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}
@media(max-width:1024px){.story{grid-template-columns:1fr}.stats-row{grid-template-columns:1fr 1fr}.values-grid,.team-grid{grid-template-columns:1fr 1fr}.press-grid{grid-template-columns:1fr}.foot-in{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.nav-l{display:none}.nav-in{padding:0 20px}.hero,.mission,.values-sec,.team-sec,.press-sec,.cta,.investors-sec{padding-left:20px;padding-right:20px}.hero{padding-top:120px}.stats-row{grid-template-columns:1fr 1fr;padding:40px 20px}.values-grid,.team-grid{grid-template-columns:1fr}.foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}}
</style>
</head>
<body>
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

<header class="hero">
  <nav class="breadcrumb"><a href="{{ url('/') }}">Home</a> / <strong>About</strong></nav>
  <h1>We built this because <span>businesses deserve to know</span> what's coming</h1>
  <p class="hero-sub">X Platforms was founded on a conviction: the gap between what businesses know about their customers and what they could know is costing them billions — and it's completely solvable.</p>
</header>

<!-- MISSION -->
<div class="mission">
  <div class="mission-in rv">
    <div class="mission-label">Our Mission</div>
    <div class="mission-quote">"Every business deserves to know what their customers are about to do — before they do it."</div>
    <p class="mission-sub">We exist to close the intelligence gap between large enterprises with data science teams and the businesses that should have the same advantage but don't. X Platforms puts AI-driven customer predictions in the hands of any team, in any industry, within weeks.</p>
  </div>
</div>

<!-- STATS -->
<div class="stats-row rv">
  <div class="stat-item"><div class="stat-n">2021</div><div class="stat-l">Founded</div></div>
  <div class="stat-item"><div class="stat-n">180+</div><div class="stat-l">Team members</div></div>
  <div class="stat-item"><div class="stat-n">15</div><div class="stat-l">Industries served</div></div>
  <div class="stat-item"><div class="stat-n">$42M</div><div class="stat-l">Raised to date</div></div>
</div>

<!-- STORY -->
<section class="story">
  <div class="story-content rv">
    <div class="stag">Our Story</div>
    <h2>From a data science frustration to a platform used by hundreds of businesses</h2>
    <p>X Platforms started when our founders — a team of data scientists and product leaders who'd spent careers inside large banks, retailers, and telcos — kept seeing the same problem. Companies were sitting on enormous customer datasets with no way to turn them into predictions their teams could actually act on.</p>
    <p>Existing tools required months of custom development, expensive data engineering teams, and produced models that were too slow, too generic, and too hard to maintain. The intelligence existed in the data. The capability to extract it didn't.</p>
    <p>So we built X Platforms to solve that problem permanently — an 8-layer engine that connects every data source, trains on industry-specific journey patterns, and delivers predictions and actions without any of the complexity that made this previously impossible for most businesses.</p>
  </div>
  <div class="story-visual rv">
    <div class="sv-year">2021 — Founded</div>
    <div class="sv-event">The Problem Validated</div>
    <div class="sv-desc">After interviewing 200+ CMOs and CDOs, the founding team confirmed the same pattern: enormous data, zero predictions. X Platforms founded in Melbourne, Australia.</div>
    <div class="sv-year">2022 — First Product</div>
    <div class="sv-event">8-Layer Architecture Built</div>
    <div class="sv-desc">The first version of the 8-layer engine deployed with 3 beta customers. 97% prediction accuracy achieved within 90 days of training.</div>
    <div class="sv-year">2023 — Series A</div>
    <div class="sv-event">$18M Raised · 15 Industries</div>
    <div class="sv-desc">Series A funded by Blackbird Ventures and Square Peg. Industry-specific models built for 15 verticals. Customer base reached 40 companies.</div>
    <div class="sv-year">2024 — Scale</div>
    <div class="sv-event">$24M Series B · Global Expansion</div>
    <div class="sv-desc">Series B led by Sequoia Southeast Asia. Operations expanded to Singapore and London. Team grew to 180+ people across 4 offices.</div>
    <div class="sv-year">2026 — Today</div>
    <div class="sv-event">200+ Clients · 1.2B Predictions</div>
    <div class="sv-desc">Over 1.2 billion predictions generated. $14.2M in verified revenue impact delivered to customers. The engine is just getting started.</div>
  </div>
</section>

<!-- VALUES -->
<section class="values-sec">
  <div class="values-in">
    <div class="rv" style="text-align:center;max-width:560px;margin:0 auto 0"><div class="stag" style="justify-content:center">Values</div><h2 style="font-weight:700;font-size:clamp(24px,2.8vw,36px);letter-spacing:-1.2px;margin-bottom:12px">What we believe</h2><p style="font-size:15px;color:var(--g300);line-height:1.7">Four principles that shape every decision we make — about the product, about the team, and about our customers.</p></div>
    <div class="values-grid rv">
      <div class="val-card"><div class="val-icon"><svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></div><h3>Radical transparency</h3><p>Every prediction comes with a confidence score, an explanation of the signals that drove it, and an honest assessment of uncertainty. We never hide behind black boxes.</p></div>
      <div class="val-card"><div class="val-icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><h3>Speed over perfection</h3><p>A good prediction delivered today beats a perfect prediction delivered in six months. We obsess over getting intelligence into the hands of decision-makers faster.</p></div>
      <div class="val-card"><div class="val-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div><h3>Customer outcomes first</h3><p>We don't measure success by features shipped or revenue closed. We measure it by revenue impact created for our customers. Their success is our scorecard.</p></div>
      <div class="val-card"><div class="val-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><h3>Data you can trust</h3><p>Security and compliance are never afterthoughts. Every layer of X Platforms is built to the highest data protection standards — because your customers' data deserves it.</p></div>
      <div class="val-card"><div class="val-icon"><svg viewBox="0 0 24 24"><path d="M4 4v5h5M20 20v-5h-5"/><path d="M20.49 9A9 9 0 005.64 5.64L4 4m16 16l-1.64-1.64A9 9 0 013.51 15"/></svg></div><h3>Continuous improvement</h3><p>The engine that ships today should be measurably smarter next week. We apply the same continuous learning philosophy to our product that we build into our AI.</p></div>
      <div class="val-card"><div class="val-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 014 10 15 15 0 01-4 10"/></svg></div><h3>Democratise intelligence</h3><p>Enterprise-grade AI should not require an enterprise-scale data science team. We build for the business that has great data and zero time to become an ML organisation.</p></div>
    </div>
  </div>
</section>

<!-- TEAM -->
<section class="team-sec">
  <div class="rv" style="margin-bottom:48px"><div class="stag">Leadership Team</div><h2 style="font-weight:700;font-size:clamp(24px,2.8vw,36px);letter-spacing:-1.2px;margin-bottom:12px">The people building it</h2><p style="font-size:15px;color:var(--g300);max-width:460px;line-height:1.7">A team of operators, data scientists, and engineers who've lived inside the problem we're solving.</p></div>
  <div class="team-grid rv">
    <div class="team-card"><div class="tc-avatar" style="background:linear-gradient(135deg,var(--blue),var(--violet))">MR</div><div class="tc-name">Marcus Reid</div><div class="tc-role">CEO & Co-founder</div><div class="tc-prev">Prev: VP Product, Atlassian · ML lead, NAB</div><a href="#" class="tc-linkedin"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg> LinkedIn</a></div>
    <div class="team-card"><div class="tc-avatar" style="background:linear-gradient(135deg,var(--violet),var(--rose))">SK</div><div class="tc-name">Sophie Kim</div><div class="tc-role">CTO & Co-founder</div><div class="tc-prev">Prev: Principal Engineer, Google · PhD AI, UNSW</div><a href="#" class="tc-linkedin"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg> LinkedIn</a></div>
    <div class="team-card"><div class="tc-avatar" style="background:linear-gradient(135deg,var(--emerald),var(--cyan))">JP</div><div class="tc-name">James Park</div><div class="tc-role">CPO</div><div class="tc-prev">Prev: Head of Product, Canva · PM Lead, Airbnb</div><a href="#" class="tc-linkedin"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg> LinkedIn</a></div>
    <div class="team-card"><div class="tc-avatar" style="background:linear-gradient(135deg,var(--amber),var(--rose))">AL</div><div class="tc-name">Amara Lin</div><div class="tc-role">Chief Revenue Officer</div><div class="tc-prev">Prev: VP Sales, Salesforce APAC · GM, HubSpot ANZ</div><a href="#" class="tc-linkedin"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg> LinkedIn</a></div>
    <div class="team-card"><div class="tc-avatar" style="background:linear-gradient(135deg,#60a5fa,var(--blue))">DN</div><div class="tc-name">David Nguyen</div><div class="tc-role">VP Engineering</div><div class="tc-prev">Prev: Staff Engineer, Stripe · Senior SWE, Palantir</div><a href="#" class="tc-linkedin"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg> LinkedIn</a></div>
    <div class="team-card"><div class="tc-avatar" style="background:linear-gradient(135deg,var(--rose),var(--violet))">TC</div><div class="tc-name">Tara Chen</div><div class="tc-role">Chief Data Scientist</div><div class="tc-prev">Prev: Lead DS, Commonwealth Bank · Researcher, CSIRO</div><a href="#" class="tc-linkedin"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg> LinkedIn</a></div>
    <div class="team-card"><div class="tc-avatar" style="background:linear-gradient(135deg,var(--cyan),var(--emerald))">RO</div><div class="tc-name">Raj Okafor</div><div class="tc-role">VP Customer Success</div><div class="tc-prev">Prev: Director CS, Zendesk · Head of CX, Xero</div><a href="#" class="tc-linkedin"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg> LinkedIn</a></div>
    <div class="team-card"><div class="tc-avatar" style="background:linear-gradient(135deg,var(--amber),#fb923c)">MW</div><div class="tc-name">Maya Watson</div><div class="tc-role">General Counsel</div><div class="tc-prev">Prev: Tech Counsel, King & Wood Mallesons · Legal, Afterpay</div><a href="#" class="tc-linkedin"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg> LinkedIn</a></div>
  </div>
</section>

<!-- INVESTORS -->
<section class="investors-sec">
  <div class="investors-in">
    <div class="rv"><div class="stag" style="justify-content:center">Investors</div><h2 style="font-weight:700;font-size:clamp(22px,2.5vw,32px);letter-spacing:-1px;margin-bottom:12px">Backed by the best</h2><p style="font-size:14px;color:var(--g300);max-width:440px;margin:0 auto">$42M raised from leading venture capital firms across Australia, Southeast Asia, and the US.</p></div>
    <div class="inv-grid rv">
      <div class="inv-card"><div class="inv-name">Blackbird Ventures</div><div class="inv-type">Series A Lead · 2023</div></div>
      <div class="inv-card"><div class="inv-name">Sequoia SEA</div><div class="inv-type">Series B Lead · 2024</div></div>
      <div class="inv-card"><div class="inv-name">Square Peg Capital</div><div class="inv-type">Series A · 2023</div></div>
      <div class="inv-card"><div class="inv-name">Reinventure</div><div class="inv-type">Seed · 2021</div></div>
      <div class="inv-card"><div class="inv-name">Main Sequence</div><div class="inv-type">Seed · 2021</div></div>
    </div>
  </div>
</section>

<!-- PRESS -->
<section class="press-sec">
  <div class="rv" style="margin-bottom:48px"><div class="stag">In The Press</div><h2 style="font-weight:700;font-size:clamp(22px,2.5vw,32px);letter-spacing:-1px;margin-bottom:12px">What people are writing</h2></div>
  <div class="press-grid rv">
    <div class="press-card"><div class="pc-pub">The Australian Financial Review</div><div class="pc-headline">"X Platforms is building the intelligence layer Australia's enterprise sector has been missing"</div><div class="pc-date">March 2026</div></div>
    <div class="press-card"><div class="pc-pub">TechCrunch</div><div class="pc-headline">"The Melbourne startup predicting customer churn before it happens — and getting it right 97% of the time"</div><div class="pc-date">January 2026</div></div>
    <div class="press-card"><div class="pc-pub">Forbes Australia</div><div class="pc-headline">"How X Platforms raised $42M to democratise enterprise AI for mid-market businesses"</div><div class="pc-date">November 2025</div></div>
  </div>
</section>

<!-- CTA -->
<section class="cta">
  <h2 class="rv">Join the companies already <span>seeing the future.</span></h2>
  <p class="rv">Book a demo and see X Platforms process your actual customers in a 30-minute live session.</p>
  <div class="rv" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a href="{{ route('book-demo') }}" class="btn-fill">Book a Demo</a>
    <a href="{{ route('case-studies') }}" class="btn-g">Read Case Studies</a>
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
const cv=document.getElementById('neural'),cx=cv.getContext('2d');let W,H,nd=[];
function rsz(){W=cv.width=innerWidth;H=cv.height=innerHeight}addEventListener('resize',rsz);rsz();
for(let i=0;i<50;i++)nd.push({x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.28,vy:(Math.random()-.5)*.28,r:Math.random()*1.4+.6,p:Math.random()*6.28});
(function draw(){cx.clearRect(0,0,W,H);nd.forEach((n,i)=>{n.x+=n.vx;n.y+=n.vy;n.p+=.01;if(n.x<0||n.x>W)n.vx*=-1;if(n.y<0||n.y>H)n.vy*=-1;nd.forEach((m,j)=>{if(j<=i)return;const d=Math.hypot(n.x-m.x,n.y-m.y);if(d<150){cx.beginPath();cx.moveTo(n.x,n.y);cx.lineTo(m.x,m.y);cx.strokeStyle=`rgba(79,143,255,${(1-d/150)*.09})`;cx.lineWidth=.5;cx.stroke()}});cx.beginPath();cx.arc(n.x,n.y,n.r+Math.sin(n.p)*.35,0,6.28);cx.fillStyle='rgba(79,143,255,.2)';cx.fill()});requestAnimationFrame(draw)})();
const obs=new IntersectionObserver(e=>{e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('vis');obs.unobserve(x.target)}})},{threshold:.08,rootMargin:'0px 0px -40px 0px'});
document.querySelectorAll('.rv').forEach(el=>obs.observe(el));
addEventListener('scroll',()=>{document.querySelector('.nav-bg').style.background=scrollY>40?'rgba(5,7,14,.92)':'rgba(5,7,14,.8)'});
</script>
</body>
</html>
