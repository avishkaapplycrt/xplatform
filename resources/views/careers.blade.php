<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Careers at X Platforms &ndash; Join the Team Building the Future of AI Customer Intelligence</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="description" content="Join X Platforms. We're a team of 180+ engineers, data scientists, and operators building the world's first 8-layer AI customer intelligence engine. Offices in Melbourne, Singapore, and London.">
<meta name="robots" content="index, follow"><link rel="canonical" href="{{ url('/careers') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;--blue:#4f8fff;--blue2:#2563eb;--blue-g:rgba(79,143,255,.08);--cyan:#38bdf8;--violet:#818cf8;--emerald:#34d399;--amber:#fbbf24;--rose:#f472b6;--white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;--brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.15);--f1:'Outfit',system-ui,sans-serif;--f2:'Source Serif 4',Georgia,serif;--fm:'IBM Plex Mono',monospace;--ease:cubic-bezier(.16,1,.3,1);--mw:1200px}
html{scroll-behavior:smooth}body{background:var(--bg);color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;overflow-x:hidden}a{color:inherit;text-decoration:none}canvas#neural{position:fixed;inset:0;z-index:0;pointer-events:none}
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
.page{position:relative;z-index:1;padding:120px 40px 80px;max-width:var(--mw);margin:0 auto}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:24px;letter-spacing:.5px}.breadcrumb a{color:var(--g300)}.breadcrumb a:hover{color:var(--blue)}
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:12px;display:flex;align-items:center;gap:10px}.stag::before{content:'';width:16px;height:1px;background:var(--blue)}
.sec{position:relative;z-index:1;padding:72px 40px;max-width:var(--mw);margin:0 auto}
.sec-f{position:relative;z-index:1;padding:72px 40px;border-top:1px solid var(--brd);background:var(--bg2)}.sec-f .sw{max-width:var(--mw);margin:0 auto}
h1{font-weight:800;font-size:clamp(32px,4vw,56px);line-height:1.08;letter-spacing:-2px;margin-bottom:16px}
h1 span,h2 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
h2{font-weight:700;font-size:clamp(22px,2.6vw,32px);letter-spacing:-1px;margin-bottom:12px;line-height:1.2}
h3{font-weight:600;font-size:16px;margin-bottom:8px;letter-spacing:-.1px}
p{font-size:15px;color:var(--g200);line-height:1.75;margin-bottom:14px;font-weight:350}p:last-child{margin-bottom:0}
.hero-p{font-size:17px;max-width:560px;color:var(--g200);line-height:1.75;margin-bottom:40px;font-weight:350}
.btn-fill{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:11px;font-weight:600;font-size:14px;border:none;cursor:pointer;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 24px rgba(79,143,255,.25);font-family:var(--f1);transition:all .25s}.btn-fill:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(79,143,255,.35)}
.btn-g{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:11px;font-weight:500;font-size:14px;background:rgba(255,255,255,.04);color:var(--g100);border:1px solid var(--g500);cursor:pointer;font-family:var(--f1);transition:all .25s}.btn-g:hover{border-color:var(--blue);background:var(--blue-g)}
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2)}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:260px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}
@media(max-width:1024px){.foot-in{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.nav-l{display:none}.nav-in{padding:0 20px}.page,.sec,.sec-f{padding-left:20px;padding-right:20px}.page{padding-top:100px}.foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}}
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
.perks-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:48px}
.perk{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:28px;transition:all .3s}
.perk:hover{border-color:var(--brd2);background:var(--card-h)}
.perk-icon{width:44px;height:44px;border-radius:11px;background:var(--blue-g);border:1px solid var(--brd2);display:flex;align-items:center;justify-content:center;margin-bottom:16px}
.perk-icon svg{width:20px;height:20px;stroke:var(--blue);fill:none;stroke-width:1.6}
.perk h3{font-size:15px;font-weight:600;margin-bottom:6px}.perk p{font-size:13px;color:var(--g300);line-height:1.6;margin:0}
.dept-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:28px}
.dt{padding:8px 18px;border-radius:8px;border:1px solid var(--brd);background:transparent;color:var(--g300);font-size:13px;cursor:pointer;transition:all .2s;font-family:var(--f1)}
.dt:hover{border-color:var(--brd2);color:var(--g100)}.dt.active{border-color:var(--blue);background:var(--blue-g);color:var(--white)}
.job-list{display:flex;flex-direction:column;gap:10px}
.job-card{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:24px;display:grid;grid-template-columns:1fr auto;align-items:center;gap:20px;transition:all .3s;cursor:pointer}
.job-card:hover{border-color:var(--brd2);background:var(--card-h)}
.jc-dept{font-family:var(--fm);font-size:10px;color:var(--blue);letter-spacing:1px;text-transform:uppercase;margin-bottom:6px}
.jc-title{font-weight:600;font-size:16px;margin-bottom:4px;letter-spacing:-.1px}
.jc-meta{display:flex;gap:16px;flex-wrap:wrap}
.jc-tag{font-family:var(--fm);font-size:10.5px;color:var(--g400);background:rgba(255,255,255,.03);border:1px solid var(--brd);padding:3px 10px;border-radius:5px}
.jc-cta{padding:10px 20px;border-radius:8px;background:var(--blue-g);border:1px solid var(--brd2);color:var(--blue);font-size:13px;font-weight:500;white-space:nowrap;transition:all .2s}
.job-card:hover .jc-cta{background:var(--blue);color:#fff;border-color:var(--blue)}
.offices{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:48px}
.office-card{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:28px;transition:all .3s}
.office-card:hover{border-color:var(--brd2);background:var(--card-h)}
.oc-flag{font-size:28px;margin-bottom:12px}.oc-city{font-weight:700;font-size:17px;margin-bottom:3px}
.oc-country{font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:.5px;margin-bottom:12px}
.oc-teams{font-size:13px;color:var(--g300);line-height:1.6}
.hidden{display:none}
@media(max-width:640px){.perks-grid,.offices{grid-template-columns:1fr}.job-card{grid-template-columns:1fr}.jc-cta{width:fit-content}}
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
<section class="page">
  <nav class="breadcrumb"><a href="{{ route('home') }}">Home</a> / <strong>Careers</strong></nav>
  <div class="rv">
    <div class="stag">We&rsquo;re Hiring</div>
    <h1>Build the <span>intelligence engine</span> with us</h1>
    <p class="hero-p">We&rsquo;re a team of 180+ engineers, data scientists, product builders, and operators working across Melbourne, Singapore, and London. If you want to work on genuinely hard problems that make an immediate difference to how businesses understand their customers &mdash; we&rsquo;d love to hear from you.</p>
    <div style="display:flex;gap:12px;flex-wrap:wrap"><a href="#open-roles" class="btn-fill">View Open Roles</a><a href="{{ route('about') }}" class="btn-g">Learn About Us</a></div>
  </div>
</section>

<div style="position:relative;z-index:1;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--bg2);padding:48px 40px">
  <div style="max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:0;text-align:center">
    <div style="padding:20px;border-right:1px solid var(--brd)"><div style="font-weight:800;font-size:32px;letter-spacing:-1px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">180+</div><div style="font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:1px;text-transform:uppercase;margin-top:4px">Team Members</div></div>
    <div style="padding:20px;border-right:1px solid var(--brd)"><div style="font-weight:800;font-size:32px;letter-spacing:-1px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">3</div><div style="font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:1px;text-transform:uppercase;margin-top:4px">Global Offices</div></div>
    <div style="padding:20px;border-right:1px solid var(--brd)"><div style="font-weight:800;font-size:32px;letter-spacing:-1px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">28</div><div style="font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:1px;text-transform:uppercase;margin-top:4px">Open Roles</div></div>
    <div style="padding:20px"><div style="font-weight:800;font-size:32px;letter-spacing:-1px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">4.8&#9733;</div><div style="font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:1px;text-transform:uppercase;margin-top:4px">Glassdoor Rating</div></div>
  </div>
</div>

<section class="sec-f"><div class="sw">
  <div class="rv" style="margin-bottom:48px"><div class="stag">Why X Platforms</div><h2>What makes us different</h2><p style="font-size:15px;color:var(--g300);max-width:480px;line-height:1.7">We&rsquo;re not another growth-at-all-costs startup. We build deliberately, hire carefully, and invest in our team for the long term.</p></div>
  <div class="perks-grid rv">
    <div class="perk"><div class="perk-icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><h3>Hard problems that matter</h3><p>Every line of code we ship creates measurable business impact for real companies. You&rsquo;ll see the effect of your work in weeks, not quarters.</p></div>
    <div class="perk"><div class="perk-icon"><svg viewBox="0 0 24 24"><path d="M4 4v5h5M20 20v-5h-5"/><path d="M20.49 9A9 9 0 005.64 5.64L4 4m16 16l-1.64-1.64A9 9 0 013.51 15"/></svg></div><h3>Equity that means something</h3><p>Meaningful options with a 4-year vest and 1-year cliff. We&rsquo;ve structured our equity to be genuinely valuable at our current trajectory.</p></div>
    <div class="perk"><div class="perk-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><h3>Real flexibility</h3><p>Hybrid-first with no mandated office days. We trust you to do your best work wherever that happens to be. Core hours 10am&ndash;3pm in your timezone.</p></div>
    <div class="perk"><div class="perk-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg></div><h3>Learning budget</h3><p>$3,000 AUD per year for conferences, courses, books, and professional development. Plus dedicated 20% time for exploration each quarter.</p></div>
    <div class="perk"><div class="perk-icon"><svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div><h3>Competitive compensation</h3><p>Top-of-market salaries benchmarked against FAANG and leading Australian tech companies. Reviewed annually. Transparent salary bands by level.</p></div>
    <div class="perk"><div class="perk-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><h3>Health &amp; wellbeing</h3><p>Premium private health insurance, mental health days, EAP programme, ergonomic home office setup allowance, and generous parental leave.</p></div>
  </div>
</div></section>

<section class="sec" id="open-roles">
  <div class="rv" style="margin-bottom:32px"><div class="stag">Open Roles</div><h2>28 positions open now</h2></div>
  <div class="dept-tabs rv">
    <button class="dt active" onclick="filterJobs('all',this)">All (28)</button>
    <button class="dt" onclick="filterJobs('engineering',this)">Engineering (12)</button>
    <button class="dt" onclick="filterJobs('data',this)">Data Science (6)</button>
    <button class="dt" onclick="filterJobs('product',this)">Product (4)</button>
    <button class="dt" onclick="filterJobs('gtm',this)">Sales &amp; CS (4)</button>
    <button class="dt" onclick="filterJobs('ops',this)">Operations (2)</button>
  </div>
  <div class="job-list rv" id="jobList">
    <div class="job-card" data-dept="engineering"><div><div class="jc-dept">Engineering</div><div class="jc-title">Senior ML Engineer &ndash; Prediction Models</div><div class="jc-meta"><span class="jc-tag">Melbourne &middot; Hybrid</span><span class="jc-tag">Full-time</span><span class="jc-tag">$180K&ndash;$220K</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="engineering"><div><div class="jc-dept">Engineering</div><div class="jc-title">Staff Software Engineer &ndash; Data Pipeline</div><div class="jc-meta"><span class="jc-tag">Melbourne &middot; Hybrid</span><span class="jc-tag">Full-time</span><span class="jc-tag">$200K&ndash;$240K</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="engineering"><div><div class="jc-dept">Engineering</div><div class="jc-title">Backend Engineer &ndash; API Platform</div><div class="jc-meta"><span class="jc-tag">Singapore &middot; Remote-first</span><span class="jc-tag">Full-time</span><span class="jc-tag">$120K&ndash;$160K SGD</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="engineering"><div><div class="jc-dept">Engineering</div><div class="jc-title">Frontend Engineer &ndash; Dashboard &amp; Visualisation</div><div class="jc-meta"><span class="jc-tag">Remote (APAC)</span><span class="jc-tag">Full-time</span><span class="jc-tag">$140K&ndash;$175K</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="engineering"><div><div class="jc-dept">Engineering</div><div class="jc-title">Platform Engineer &ndash; Infrastructure &amp; Security</div><div class="jc-meta"><span class="jc-tag">Melbourne &middot; Hybrid</span><span class="jc-tag">Full-time</span><span class="jc-tag">$160K&ndash;$200K</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="data"><div><div class="jc-dept">Data Science</div><div class="jc-title">Lead Data Scientist &ndash; Healthcare Model</div><div class="jc-meta"><span class="jc-tag">Melbourne &middot; Hybrid</span><span class="jc-tag">Full-time</span><span class="jc-tag">$170K&ndash;$210K</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="data"><div><div class="jc-dept">Data Science</div><div class="jc-title">Senior Data Scientist &ndash; Retail &amp; E-Commerce</div><div class="jc-meta"><span class="jc-tag">Melbourne &middot; Hybrid</span><span class="jc-tag">Full-time</span><span class="jc-tag">$150K&ndash;$185K</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="data"><div><div class="jc-dept">Data Science</div><div class="jc-title">ML Researcher &ndash; Pattern Detection</div><div class="jc-meta"><span class="jc-tag">Remote (Global)</span><span class="jc-tag">Full-time</span><span class="jc-tag">Competitive</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="product"><div><div class="jc-dept">Product</div><div class="jc-title">Senior Product Manager &ndash; Core Platform</div><div class="jc-meta"><span class="jc-tag">Melbourne &middot; Hybrid</span><span class="jc-tag">Full-time</span><span class="jc-tag">$160K&ndash;$195K</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="product"><div><div class="jc-dept">Product</div><div class="jc-title">Product Designer &ndash; Data Visualisation</div><div class="jc-meta"><span class="jc-tag">Remote (APAC)</span><span class="jc-tag">Full-time</span><span class="jc-tag">$130K&ndash;$165K</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="gtm"><div><div class="jc-dept">Sales &amp; Customer Success</div><div class="jc-title">Enterprise Account Executive &ndash; Financial Services</div><div class="jc-meta"><span class="jc-tag">Sydney &middot; Hybrid</span><span class="jc-tag">Full-time</span><span class="jc-tag">$140K + commission</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="gtm"><div><div class="jc-dept">Sales &amp; Customer Success</div><div class="jc-title">Customer Success Manager &ndash; Healthcare &amp; EdTech</div><div class="jc-meta"><span class="jc-tag">Melbourne &middot; Hybrid</span><span class="jc-tag">Full-time</span><span class="jc-tag">$120K&ndash;$150K</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
    <div class="job-card" data-dept="ops"><div><div class="jc-dept">Operations</div><div class="jc-title">Head of People &amp; Culture</div><div class="jc-meta"><span class="jc-tag">Melbourne &middot; Hybrid</span><span class="jc-tag">Full-time</span><span class="jc-tag">$180K&ndash;$220K</span></div></div><div class="jc-cta">Apply &rarr;</div></div>
  </div>
</section>

<section class="sec-f"><div class="sw">
  <div class="rv" style="margin-bottom:48px"><div class="stag">Our Offices</div><h2>Where we work</h2></div>
  <div class="offices rv">
    <div class="office-card"><div class="oc-flag">🇦🇺</div><div class="oc-city">Melbourne</div><div class="oc-country">AUSTRALIA &middot; HEADQUARTERS</div><div class="oc-teams">Engineering, Data Science, Product, Leadership, Finance, Legal, HR</div></div>
    <div class="office-card"><div class="oc-flag">🇸🇬</div><div class="oc-city">Singapore</div><div class="oc-country">SINGAPORE &middot; APAC HUB</div><div class="oc-teams">Sales (APAC), Customer Success, Engineering (Platform)</div></div>
    <div class="office-card"><div class="oc-flag">🇬🇧</div><div class="oc-city">London</div><div class="oc-country">UNITED KINGDOM &middot; EMEA HUB</div><div class="oc-teams">Sales (EMEA), Data Science (Research), Partnerships</div></div>
  </div>
</div></section>

<div style="position:relative;z-index:1;border-top:1px solid var(--brd);background:linear-gradient(135deg,rgba(79,143,255,.04),rgba(129,140,248,.02));padding:80px 40px;text-align:center">
  <div style="max-width:560px;margin:0 auto" class="rv">
    <h2 style="margin-bottom:12px">Don&rsquo;t see the right role?</h2>
    <p style="font-size:15px;color:var(--g300);margin-bottom:28px">We&rsquo;re always interested in exceptional people. Send us your details and we&rsquo;ll keep you in mind for future opportunities.</p>
    <a href="mailto:careers@xplatforms.ai" class="btn-fill">Send Us Your CV</a>
  </div>
</div>

<footer class="foot"><div class="foot-in">
  <div><a href="{{ url('/') }}" class="logo"><img src="{{ asset('images/xplatforms_logo.jpeg') }}" alt="X Platforms" style="height:32px;width:auto;display:block"></a><p class="foot-desc">The world's first 8-layer AI intelligence engine.</p></div>
  <div class="foot-c"><h5>Product</h5><a href="{{ route('platform.architecture') }}">Architecture</a><a href="#">Integrations</a><a href="{{ route('industries') }}">Industries</a><a href="{{ route('pricing') }}">Pricing</a></div>
  <div class="foot-c"><h5>Company</h5><a href="{{ route('about') }}">About</a><a href="{{ route('careers') }}">Careers</a><a href="{{ route('blog') }}">Blog</a><a href="{{ route('contact') }}">Contact</a></div>
  <div class="foot-c"><h5>Resources</h5><a href="#">Documentation</a><a href="{{ route('case-studies') }}">Case Studies</a><a href="#">API Reference</a><a href="{{ route('security') }}">Security</a></div>
</div><div class="foot-b"><span>&copy; {{ date('Y') }} X Platforms.</span><span><a href="{{ route('privacy') }}" style="color:inherit">Privacy</a> &middot; <a href="{{ route('terms') }}" style="color:inherit">Terms</a> &middot; <a href="{{ route('security') }}" style="color:inherit">Security</a></span></div></footer>
<script>
function filterJobs(dept, btn) {
  document.querySelectorAll('.dt').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.job-card').forEach(c=>{
    c.classList.toggle('hidden', dept !== 'all' && c.dataset.dept !== dept);
  });
}
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
