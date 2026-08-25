<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blog &amp; Insights &ndash; X Platforms | AI Customer Intelligence, Churn &amp; Growth</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="description" content="Deep dives on AI customer intelligence, churn prediction, industry analytics, and growth strategy. Published every Tuesday by the team building the 8-layer engine. 12,000+ readers.">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ url('/blog') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;
  --blue:#4f8fff;--blue2:#2563eb;--blue-g:rgba(79,143,255,.08);--blue-g2:rgba(79,143,255,.04);
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

/* HERO */
.hero{position:relative;z-index:1;padding:120px 40px 48px;max-width:var(--mw);margin:0 auto}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:24px;letter-spacing:.5px}
.breadcrumb a{color:var(--g300)}.breadcrumb a:hover{color:var(--blue)}
.hero-row{display:flex;align-items:flex-end;justify-content:space-between;gap:32px;flex-wrap:wrap}
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:12px;display:flex;align-items:center;gap:10px}
.stag::before{content:'';width:16px;height:1px;background:var(--blue)}
h1{font-weight:800;font-size:clamp(32px,4vw,52px);line-height:1.08;letter-spacing:-2px;margin-bottom:12px}
h1 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-sub{font-size:16px;color:var(--g200);line-height:1.7;max-width:480px;font-weight:350}
.hero-right{display:flex;align-items:center;gap:12px;flex-shrink:0}
.rss-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;border:1px solid var(--brd);background:transparent;color:var(--g400);font-size:12.5px;font-family:var(--f1);transition:all .2s;cursor:pointer}
.rss-btn:hover{border-color:var(--brd2);color:var(--g100)}
.rss-btn svg{width:14px;height:14px}

/* STATS BAR */
.stats-bar{position:relative;z-index:1;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--bg2);padding:20px 40px}
.stats-bar-in{max-width:var(--mw);margin:0 auto;display:flex;align-items:center;gap:40px;flex-wrap:wrap}
.sb-stat{display:flex;align-items:center;gap:10px}
.sb-n{font-weight:700;font-size:16px;letter-spacing:-.3px}
.sb-l{font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:.5px}
.sb-div{width:1px;height:20px;background:var(--brd)}
.sb-live{display:flex;align-items:center;gap:8px;font-family:var(--fm);font-size:10.5px;color:var(--emerald);margin-left:auto}
.live-dot{width:6px;height:6px;border-radius:50%;background:var(--emerald);box-shadow:0 0 8px var(--emerald);animation:blink 2s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}

/* FEATURED POST */
.featured-section{position:relative;z-index:1;max-width:var(--mw);margin:0 auto;padding:48px 40px 0}
.featured-post{display:grid;grid-template-columns:1fr 1fr;gap:0;align-items:center;background:var(--card);border:1px solid var(--brd2);border-radius:20px;overflow:hidden;position:relative}
.featured-post::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--blue),var(--violet),var(--cyan))}
.fp-image{height:320px;background:linear-gradient(135deg,rgba(79,143,255,.15),rgba(129,140,248,.1));display:flex;align-items:center;justify-content:center;font-size:64px;position:relative;border-right:1px solid var(--brd)}
.fp-badge{position:absolute;top:20px;left:20px;font-family:var(--fm);font-size:9.5px;padding:4px 12px;border-radius:5px;background:rgba(79,143,255,.15);border:1px solid var(--brd2);color:var(--blue);letter-spacing:.8px;text-transform:uppercase}
.fp-read-time{position:absolute;bottom:20px;right:20px;font-family:var(--fm);font-size:10px;color:var(--g400);background:rgba(5,7,14,.7);padding:4px 10px;border-radius:5px;backdrop-filter:blur(8px);border:1px solid var(--brd)}
.fp-content{padding:40px}
.fp-category{font-family:var(--fm);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--blue);margin-bottom:12px;display:flex;align-items:center;gap:8px}
.fp-cat-dot{width:5px;height:5px;border-radius:50%;background:var(--blue)}
.fp-title{font-weight:700;font-size:clamp(18px,2vw,24px);letter-spacing:-.4px;line-height:1.25;margin-bottom:14px}
.fp-excerpt{font-size:14.5px;color:var(--g300);line-height:1.72;margin-bottom:20px}
.fp-meta{display:flex;align-items:center;gap:0;margin-bottom:24px}
.fp-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;color:#fff;margin-right:10px;flex-shrink:0}
.fp-author-name{font-size:13px;font-weight:600}
.fp-author-role{font-family:var(--fm);font-size:10px;color:var(--g400)}
.fp-date{font-family:var(--fm);font-size:10.5px;color:var(--g500);margin-left:auto}
.read-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;background:var(--blue-g);border:1px solid var(--brd2);border-radius:10px;font-size:13.5px;font-weight:500;color:var(--blue);transition:all .2s}
.read-btn:hover{background:var(--blue);color:#fff}
.read-btn svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;transition:transform .2s}
.read-btn:hover svg{transform:translateX(3px)}

/* MAIN LAYOUT */
.main-layout{position:relative;z-index:1;max-width:var(--mw);margin:0 auto;padding:48px 40px 80px;display:grid;grid-template-columns:1fr 320px;gap:48px;align-items:start}

/* SEARCH + FILTER */
.search-filter-bar{display:flex;gap:12px;align-items:center;margin-bottom:28px;flex-wrap:wrap}
.search-wrap{position:relative;flex:1;min-width:200px}
.search-wrap svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;stroke:var(--g400);fill:none;stroke-width:2;pointer-events:none}
.blog-search{width:100%;background:var(--bg3);border:1px solid var(--g500);border-radius:9px;padding:9px 12px 9px 38px;color:var(--white);font-family:var(--f1);font-size:13.5px;outline:none;transition:border-color .2s}
.blog-search:focus{border-color:var(--blue)}
.blog-search::placeholder{color:var(--g600)}
.filter-pills{display:flex;gap:6px;flex-wrap:wrap}
.fp-pill{padding:6px 14px;border-radius:7px;border:1px solid var(--brd);background:transparent;color:var(--g300);font-size:12.5px;cursor:pointer;transition:all .2s;font-family:var(--f1)}
.fp-pill:hover{border-color:var(--brd2);color:var(--g100)}
.fp-pill.active{border-color:var(--blue);background:var(--blue-g);color:var(--white)}
.results-label{font-family:var(--fm);font-size:11px;color:var(--g500)}

/* ARTICLE CARDS */
.articles-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.ac{background:var(--card);border:1px solid var(--brd);border-radius:16px;overflow:hidden;transition:all .35s;display:flex;flex-direction:column}
.ac:hover{border-color:var(--brd2);background:var(--card-h);transform:translateY(-3px)}
.ac-img{height:140px;position:relative;overflow:hidden;flex-shrink:0}
.ac-img-inner{position:absolute;inset:0}
.ac-img-emoji{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:36px}
.ac-body{padding:20px;flex:1;display:flex;flex-direction:column}
.ac-tag{font-family:var(--fm);font-size:9.5px;letter-spacing:1px;text-transform:uppercase;color:var(--blue);margin-bottom:8px;display:flex;align-items:center;gap:6px}
.ac-tag-dot{width:5px;height:5px;border-radius:50%;background:var(--blue)}
.ac-title{font-weight:600;font-size:14.5px;letter-spacing:-.1px;margin-bottom:7px;line-height:1.35;flex:1}
.ac-excerpt{font-size:12.5px;color:var(--g300);line-height:1.6;margin-bottom:12px}
.ac-footer{display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid var(--brd);margin-top:auto}
.ac-meta{font-family:var(--fm);font-size:10px;color:var(--g500);display:flex;flex-direction:column;gap:2px}
.ac-share{display:flex;gap:6px}
.share-btn{width:26px;height:26px;border-radius:6px;border:1px solid var(--brd);background:transparent;color:var(--g400);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s}
.share-btn:hover{border-color:var(--brd2);color:var(--white);background:var(--blue-g)}
.share-btn svg{width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2}

/* LOAD MORE */
.load-more-wrap{text-align:center;margin-top:32px;padding-top:24px;border-top:1px solid var(--brd)}
.load-more-btn{padding:12px 32px;border:1px solid var(--brd);background:transparent;color:var(--g200);font-family:var(--f1);font-size:14px;border-radius:10px;cursor:pointer;transition:all .25s;display:inline-flex;align-items:center;gap:8px}
.load-more-btn:hover{border-color:var(--blue);color:var(--blue);background:var(--blue-g)}
.articles-count{font-family:var(--fm);font-size:11px;color:var(--g500);margin-top:12px}

/* SIDEBAR */
.sidebar{display:flex;flex-direction:column;gap:20px;position:sticky;top:88px}
.snl{background:linear-gradient(135deg,rgba(79,143,255,.08),rgba(129,140,248,.04));border:1px solid var(--brd2);border-radius:16px;padding:24px}
.snl-icon{font-size:28px;margin-bottom:12px}
.snl-title{font-weight:700;font-size:16px;letter-spacing:-.2px;margin-bottom:4px}
.snl-sub{font-size:12.5px;color:var(--g300);line-height:1.55;margin-bottom:16px}
.snl-social-proof{font-family:var(--fm);font-size:10px;color:var(--g400);margin-bottom:14px;display:flex;align-items:center;gap:6px}
.snl-dot{width:5px;height:5px;border-radius:50%;background:var(--emerald)}
.snl-input{width:100%;background:var(--bg3);border:1px solid var(--g500);border-radius:8px;padding:10px 13px;color:var(--white);font-family:var(--f1);font-size:13px;outline:none;transition:border-color .2s;margin-bottom:8px}
.snl-input:focus{border-color:var(--blue)}
.snl-input::placeholder{color:var(--g600)}
.snl-btn{width:100%;padding:11px;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;border:none;border-radius:8px;font-weight:600;font-size:13.5px;cursor:pointer;font-family:var(--f1);transition:all .25s}
.snl-btn:hover{transform:translateY(-1px);box-shadow:0 4px 20px rgba(79,143,255,.3)}
.snl-note{font-size:11px;color:var(--g500);text-align:center;margin-top:8px}
.snl-success{display:none;text-align:center;padding:12px}
.snl-success-icon{font-size:32px;margin-bottom:8px}
.snl-success-text{font-size:13px;color:var(--emerald);font-weight:500}
.sb-widget{background:var(--card);border:1px solid var(--brd);border-radius:16px;padding:22px}
.sw-title{font-family:var(--fm);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g400);margin-bottom:18px;display:flex;align-items:center;gap:8px}
.sw-title::after{content:'';flex:1;height:1px;background:var(--brd)}
.popular-post{display:flex;gap:12px;padding:10px 0;border-bottom:1px solid var(--brd)}
.popular-post:last-child{border-bottom:none;padding-bottom:0}
.pp-rank{font-family:var(--fm);font-size:18px;font-weight:700;color:var(--g600);min-width:24px;line-height:1.2;padding-top:2px}
.pp-title{font-size:13px;font-weight:500;line-height:1.4;margin-bottom:4px}
.pp-meta{font-family:var(--fm);font-size:9.5px;color:var(--g500)}
.topic-cloud{display:flex;flex-wrap:wrap;gap:6px;margin-top:14px}
.topic-pill{padding:5px 12px;border-radius:6px;border:1px solid var(--brd);background:transparent;color:var(--g300);font-size:12px;cursor:pointer;transition:all .2s;font-family:var(--f1)}
.topic-pill:hover{border-color:var(--brd2);color:var(--white);background:var(--blue-g)}
.author-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--brd)}
.author-row:last-child{border-bottom:none;padding-bottom:0}
.ar-avatar{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;flex-shrink:0}
.ar-name{font-size:13px;font-weight:600;margin-bottom:2px}
.ar-count{font-family:var(--fm);font-size:10px;color:var(--g400)}

/* BY INDUSTRY */
.by-industry{position:relative;z-index:1;border-top:1px solid var(--brd);background:var(--bg2);padding:64px 40px}
.bi-in{max-width:var(--mw);margin:0 auto}
.bi-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-top:40px}
.bi-card{background:var(--card);border:1px solid var(--brd);border-radius:12px;padding:18px 14px;text-align:center;transition:all .3s;cursor:pointer}
.bi-card:hover{border-color:var(--brd2);background:var(--card-h);transform:translateY(-2px)}
.bi-icon{font-size:24px;margin-bottom:8px}
.bi-name{font-size:13px;font-weight:600;margin-bottom:3px}
.bi-count{font-family:var(--fm);font-size:9.5px;color:var(--g400)}

/* CTA */
.cta-section{position:relative;z-index:1;border-top:1px solid var(--brd);padding:80px 40px;text-align:center}
.cta-section::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:360px;background:radial-gradient(circle,rgba(79,143,255,.05),transparent 65%);pointer-events:none}
.cta-section h2{font-weight:800;font-size:clamp(24px,3vw,38px);letter-spacing:-1.2px;line-height:1.15;margin-bottom:12px;position:relative}
.cta-section h2 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.cta-section p{font-size:15px;color:var(--g300);max-width:400px;margin:0 auto 24px;line-height:1.7;position:relative}
.btn-fill{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:11px;font-weight:600;font-size:14px;border:none;cursor:pointer;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 24px rgba(79,143,255,.25);font-family:var(--f1);transition:all .25s}
.btn-fill:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(79,143,255,.35)}
.btn-g{display:inline-flex;align-items:center;padding:13px 28px;border-radius:11px;font-weight:500;font-size:14px;background:rgba(255,255,255,.04);color:var(--g100);border:1px solid var(--g500);cursor:pointer;font-family:var(--f1);transition:all .25s}
.btn-g:hover{border-color:var(--blue);background:var(--blue-g)}

/* FOOTER */
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2);position:relative;z-index:1}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:260px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}

/* UTILS */
.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}
.hidden{display:none!important}

@media(max-width:1024px){.main-layout{grid-template-columns:1fr}.sidebar{position:static}.articles-grid{grid-template-columns:1fr 1fr}.featured-post{grid-template-columns:1fr}.fp-image{border-right:none;border-bottom:1px solid var(--brd);height:200px}.bi-grid{grid-template-columns:repeat(3,1fr)}.foot-in{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.nav-l{display:none}.nav-in{padding:0 20px}.hero,.featured-section,.main-layout,.by-industry,.cta-section,.foot{padding-left:20px;padding-right:20px}.hero{padding-top:100px}.articles-grid{grid-template-columns:1fr}.bi-grid{grid-template-columns:repeat(2,1fr)}.stats-bar{padding:16px 20px}.stats-bar-in{gap:16px}.foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}.hero-row{flex-direction:column;align-items:flex-start}}
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
  <nav class="breadcrumb"><a href="{{ route('home') }}">Home</a> / <strong>Blog &amp; Insights</strong></nav>
  <div class="hero-row rv">
    <div class="hero-left">
      <div class="stag">Insights</div>
      <h1>AI, data &amp; <span>growth strategy</span></h1>
      <p class="hero-sub">Deep dives on customer intelligence, churn prediction, and AI-driven growth. Every Tuesday. From the team building the engine.</p>
    </div>
    <div class="hero-right">
      <a href="#" class="rss-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 11a9 9 0 019 9M4 4a16 16 0 0116 16"/><circle cx="5" cy="19" r="1" fill="currentColor"/></svg>
        RSS Feed
      </a>
      <a href="#" class="rss-btn">
        <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
        Follow
      </a>
    </div>
  </div>
</header>

<div class="stats-bar">
  <div class="stats-bar-in rv">
    <div class="sb-stat"><span class="sb-n" style="background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">47</span><span class="sb-l">Articles Published</span></div>
    <div class="sb-div"></div>
    <div class="sb-stat"><span class="sb-n" style="background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">12K+</span><span class="sb-l">Weekly Readers</span></div>
    <div class="sb-div"></div>
    <div class="sb-stat"><span class="sb-n" style="background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">Every Tue</span><span class="sb-l">New Articles</span></div>
    <div class="sb-div"></div>
    <div class="sb-stat"><span class="sb-n" style="background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">5 Topics</span><span class="sb-l">AI &middot; Data &middot; Industry &middot; Eng &middot; Product</span></div>
    <div class="sb-live"><span class="live-dot"></span>Latest: 8 May 2026</div>
  </div>
</div>

<section class="featured-section">
  <div class="featured-post rv">
    <div class="fp-image">
      <div class="fp-badge">&#9733; Editor's Pick</div>
      🧠
      <div class="fp-read-time">&#9201; 12 min read</div>
    </div>
    <div class="fp-content">
      <div class="fp-category"><span class="fp-cat-dot"></span>AI Strategy &middot; Featured</div>
      <div class="fp-title">Why 97% Prediction Accuracy Is the Wrong Goal &mdash; And What to Optimise For Instead</div>
      <div class="fp-excerpt">Most AI platform conversations start with accuracy. Ours start with a different question: accurate about what, and in what window? Here's why prediction timing beats accuracy &mdash; and how we build models that change actual business outcomes rather than benchmark leaderboards.</div>
      <div class="fp-meta">
        <div class="fp-avatar" style="background:linear-gradient(135deg,var(--violet),var(--rose))">SK</div>
        <div><div class="fp-author-name">Sophie Kim</div><div class="fp-author-role">CTO &amp; Co-founder</div></div>
        <div class="fp-date">8 May 2026</div>
      </div>
      <a href="#" class="read-btn">Read article <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>
</section>

<div class="main-layout">
  <!-- ARTICLES COLUMN -->
  <div>
    <div class="search-filter-bar rv">
      <div class="search-wrap">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input class="blog-search" id="blogSearch" type="text" placeholder="Search articles..." oninput="filterArticles()">
      </div>
    </div>
    <div class="filter-pills rv" id="filterPills">
      <button class="fp-pill active" data-cat="all" onclick="setCat('all',this)">All (12)</button>
      <button class="fp-pill" data-cat="ai" onclick="setCat('ai',this)">AI Strategy (3)</button>
      <button class="fp-pill" data-cat="case" onclick="setCat('case',this)">Case Studies (3)</button>
      <button class="fp-pill" data-cat="eng" onclick="setCat('eng',this)">Engineering (2)</button>
      <button class="fp-pill" data-cat="industry" onclick="setCat('industry',this)">Industry Deep Dives (3)</button>
      <button class="fp-pill" data-cat="product" onclick="setCat('product',this)">Product (1)</button>
    </div>
    <div class="results-label rv" id="resultsLabel" style="margin-bottom:20px">Showing 12 articles</div>

    <div class="articles-grid rv" id="articlesGrid">

      <div class="ac" data-cat="case" data-title="week 2 skip pattern mockmaster churn">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(52,211,153,.12),rgba(56,189,248,.08))"></div><div class="ac-img-emoji">📊</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot"></span>Case Study</div>
          <div class="ac-title">The "Week 2 Skip" Pattern: How MockMaster Reduced Churn 41% in 90 Days</div>
          <div class="ac-excerpt">A deep dive into the pattern discovery that changed MockMaster's entire retention strategy &mdash; and why it was invisible before X Platforms.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>Marcus Reid</span><span>2 May &middot; 9 min</span></div>
            <div class="ac-share">
              <button class="share-btn" title="Share on LinkedIn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn" title="Share on X"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn" title="Copy link"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <div class="ac" data-cat="industry" data-title="banking churn signals 45 days account switching">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(129,140,248,.12),rgba(244,114,182,.08))"></div><div class="ac-img-emoji">🏦</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot" style="background:var(--violet)"></span><span style="color:var(--violet)">Industry Deep Dive</span></div>
          <div class="ac-title">Banking Churn in 2026: The Five Signals That Predict Account Switching 45 Days Ahead</div>
          <div class="ac-excerpt">We analysed 18 million banking customer journeys to identify signal combinations that predict churn with 94% accuracy.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>Tara Chen</span><span>28 Apr &middot; 14 min</span></div>
            <div class="ac-share">
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <div class="ac" data-cat="eng" data-title="8-layer architecture 18000 events per second engineering">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(251,191,36,.1),rgba(251,146,60,.08))"></div><div class="ac-img-emoji">⚙️</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot" style="background:var(--amber)"></span><span style="color:var(--amber)">Engineering</span></div>
          <div class="ac-title">Building an 8-Layer AI Architecture That Processes 18,000 Events Per Second</div>
          <div class="ac-excerpt">How we designed the X Platforms processing engine to handle millions of customer events in real time without sacrificing prediction accuracy.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>David Nguyen</span><span>21 Apr &middot; 18 min</span></div>
            <div class="ac-share">
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <div class="ac" data-cat="ai" data-title="descriptive predictive prescriptive analytics maturity">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(79,143,255,.12),rgba(56,189,248,.08))"></div><div class="ac-img-emoji">📈</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot"></span>AI Strategy</div>
          <div class="ac-title">Descriptive vs Predictive vs Prescriptive Analytics: Why Most Businesses Are Still on Step One</div>
          <div class="ac-excerpt">The three stages of analytics maturity &mdash; and why jumping straight to predictive is both possible and essential for competitive survival.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>James Park</span><span>14 Apr &middot; 10 min</span></div>
            <div class="ac-share">
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <div class="ac" data-cat="industry" data-title="40/7 rule oneaustralia online course completion edtech">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(52,211,153,.1),rgba(129,140,248,.08))"></div><div class="ac-img-emoji">📚</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot" style="background:var(--violet)"></span><span style="color:var(--violet)">Industry Deep Dive</span></div>
          <div class="ac-title">The 40/7 Rule: What OneAustralia Learned About Online Course Completion</div>
          <div class="ac-excerpt">OneAustralia tripled their completion rates after X Platforms discovered a critical engagement threshold. Full analysis with benchmarks.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>Raj Okafor</span><span>8 Apr &middot; 11 min</span></div>
            <div class="ac-share">
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <div class="ac" data-cat="product" data-title="customer journey simulator product build">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(244,114,182,.1),rgba(251,191,36,.08))"></div><div class="ac-img-emoji">🛠️</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot" style="background:var(--rose)"></span><span style="color:var(--rose)">Product</span></div>
          <div class="ac-title">How We Built the X Platforms Customer Journey Simulator &mdash; And What We Learned</div>
          <div class="ac-excerpt">Inside the technical decisions, data philosophy, and product thinking behind building an interactive AI simulation for 15 industries.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>Sophie Kim</span><span>1 Apr &middot; 15 min</span></div>
            <div class="ac-share">
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <div class="ac" data-cat="ai" data-title="CLV customer lifetime value prediction model guide">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(79,143,255,.1),rgba(52,211,153,.08))"></div><div class="ac-img-emoji">💰</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot"></span>AI Strategy</div>
          <div class="ac-title">The Definitive Guide to Predicting Customer Lifetime Value Before the First Purchase</div>
          <div class="ac-excerpt">How the best businesses model CLV in the first week of a customer relationship &mdash; and use it to make smarter acquisition decisions.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>Tara Chen</span><span>25 Mar &middot; 16 min</span></div>
            <div class="ac-share">
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <div class="ac" data-cat="case" data-title="scorementor SaaS MRR growth churn prediction">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(129,140,248,.1),rgba(56,189,248,.08))"></div><div class="ac-img-emoji">🚀</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot"></span>Case Study</div>
          <div class="ac-title">ScoreMentor's 3.4&times; MRR Growth: How Churn Prediction Changed Their Entire Growth Model</div>
          <div class="ac-excerpt">ScoreMentor went from $280K to $952K MRR in 18 months. Churn prediction wasn't an add-on &mdash; it was the growth engine. Here's how.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>Marcus Reid</span><span>18 Mar &middot; 12 min</span></div>
            <div class="ac-share">
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <div class="ac" data-cat="eng" data-title="identity resolution probabilistic matching golden record engineering">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(251,146,60,.1),rgba(251,191,36,.08))"></div><div class="ac-img-emoji">🔗</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot" style="background:var(--amber)"></span><span style="color:var(--amber)">Engineering</span></div>
          <div class="ac-title">How We Built Identity Resolution That Matches 87% of Customers Across 10+ Data Sources</div>
          <div class="ac-excerpt">The technical architecture behind X Platforms' identity layer &mdash; probabilistic matching, deduplication, and building the golden customer record.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>David Nguyen</span><span>11 Mar &middot; 20 min</span></div>
            <div class="ac-share">
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <div class="ac" data-cat="industry" data-title="healthcare patient care gap HIPAA AI compliance">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(52,211,153,.12),rgba(56,189,248,.06))"></div><div class="ac-img-emoji">🏥</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot" style="background:var(--violet)"></span><span style="color:var(--violet)">Industry Deep Dive</span></div>
          <div class="ac-title">Closing Care Gaps at Scale: How AI Is Changing Patient Follow-Up in Healthcare</div>
          <div class="ac-excerpt">Why 28% of patients miss critical follow-up care &mdash; and how AI intervention at the right moment changes the outcome for patients and providers.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>Tara Chen</span><span>4 Mar &middot; 13 min</span></div>
            <div class="ac-share">
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <div class="ac" data-cat="ai" data-title="real-time AI personalisation customer experience triggers">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(244,114,182,.1),rgba(129,140,248,.08))"></div><div class="ac-img-emoji">⚡</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot"></span>AI Strategy</div>
          <div class="ac-title">Real-Time vs Batch Predictions: Why the Timing of Your AI Matters as Much as the Accuracy</div>
          <div class="ac-excerpt">A prediction delivered 48 hours too late is often worse than no prediction at all. Here's how to think about AI trigger timing.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>Sophie Kim</span><span>25 Feb &middot; 8 min</span></div>
            <div class="ac-share">
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <div class="ac" data-cat="case" data-title="oneaustralia elearning completion 3x revenue">
        <div class="ac-img"><div class="ac-img-inner" style="background:linear-gradient(135deg,rgba(56,189,248,.1),rgba(129,140,248,.08))"></div><div class="ac-img-emoji">🎓</div></div>
        <div class="ac-body">
          <div class="ac-tag"><span class="ac-tag-dot"></span>Case Study</div>
          <div class="ac-title">OneAustralia: From 24% to 74% Course Completion Rate in One Year</div>
          <div class="ac-excerpt">How one of Australia's largest eLearning providers used X Platforms to triple completion rates and 2.2&times; revenue per enrolled student.</div>
          <div class="ac-footer">
            <div class="ac-meta"><span>Raj Okafor</span><span>18 Feb &middot; 10 min</span></div>
            <div class="ac-share">
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
              <button class="share-btn"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg></button>
            </div>
          </div>
        </div>
      </div>

    </div>

    <div id="noResults" class="hidden" style="text-align:center;padding:64px 20px">
      <div style="font-size:36px;margin-bottom:16px">🔍</div>
      <div style="font-weight:600;font-size:17px;margin-bottom:8px">No articles found</div>
      <div style="font-size:14px;color:var(--g300)">Try a different search or <a href="#" onclick="clearAll(event)" style="color:var(--blue)">clear filters</a>.</div>
    </div>

    <div class="load-more-wrap rv" id="loadMoreWrap">
      <button class="load-more-btn" onclick="loadMore()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h5M20 20v-5h-5"/><path d="M20.49 9A9 9 0 005.64 5.64L4 4m16 16l-1.64-1.64A9 9 0 013.51 15"/></svg>
        Load more articles
      </button>
      <div class="articles-count" id="loadMoreCount">Showing 12 of 47 articles</div>
    </div>
  </div>

  <!-- SIDEBAR -->
  <aside class="sidebar">

    <div class="snl rv">
      <div class="snl-icon">💬</div>
      <div class="snl-title">The Weekly Intelligence</div>
      <div class="snl-sub">Every Tuesday: one deep dive, one data insight, one industry signal you won't find anywhere else.</div>
      <div class="snl-social-proof"><span class="snl-dot"></span>12,400 subscribers &middot; 4.8&#9733; average rating</div>
      <div id="snlForm">
        <input class="snl-input" id="snlEmail" type="email" placeholder="your@email.com">
        <button class="snl-btn" onclick="subscribeNL()">Subscribe Free &rarr;</button>
        <div class="snl-note">No spam. Unsubscribe anytime.</div>
      </div>
      <div class="snl-success" id="snlSuccess">
        <div class="snl-success-icon">🎉</div>
        <div class="snl-success-text">You're in! First issue lands this Tuesday.</div>
      </div>
    </div>

    <div class="sb-widget rv">
      <div class="sw-title">Most Read</div>
      <div class="popular-post"><div class="pp-rank">01</div><div><div class="pp-title">Why 97% Accuracy Is the Wrong Goal</div><div class="pp-meta">Sophie Kim &middot; 4.2K reads</div></div></div>
      <div class="popular-post"><div class="pp-rank">02</div><div><div class="pp-title">Banking Churn: 5 Signals That Predict Switching</div><div class="pp-meta">Tara Chen &middot; 3.8K reads</div></div></div>
      <div class="popular-post"><div class="pp-rank">03</div><div><div class="pp-title">The Week 2 Skip Pattern (MockMaster)</div><div class="pp-meta">Marcus Reid &middot; 3.1K reads</div></div></div>
      <div class="popular-post"><div class="pp-rank">04</div><div><div class="pp-title">CLV Prediction Before the First Purchase</div><div class="pp-meta">Tara Chen &middot; 2.9K reads</div></div></div>
      <div class="popular-post"><div class="pp-rank">05</div><div><div class="pp-title">8-Layer Architecture: 18K Events/Second</div><div class="pp-meta">David Nguyen &middot; 2.4K reads</div></div></div>
    </div>

    <div class="sb-widget rv">
      <div class="sw-title">Topics</div>
      <div class="topic-cloud">
        <button class="topic-pill" onclick="setCat('ai',document.querySelector('[data-cat=ai]'))">AI Strategy</button>
        <button class="topic-pill" onclick="setCat('case',document.querySelector('[data-cat=case]'))">Case Studies</button>
        <button class="topic-pill" onclick="setCat('industry',document.querySelector('[data-cat=industry]'))">Industry Dives</button>
        <button class="topic-pill" onclick="setCat('eng',document.querySelector('[data-cat=eng]'))">Engineering</button>
        <button class="topic-pill" onclick="setCat('product',document.querySelector('[data-cat=product]'))">Product</button>
        <button class="topic-pill">Churn Prediction</button>
        <button class="topic-pill">CLV Modelling</button>
        <button class="topic-pill">Data Unification</button>
        <button class="topic-pill">Real-time AI</button>
        <button class="topic-pill">Banking AI</button>
        <button class="topic-pill">Healthcare AI</button>
        <button class="topic-pill">Retail AI</button>
        <button class="topic-pill">EdTech AI</button>
      </div>
    </div>

    <div class="sb-widget rv">
      <div class="sw-title">Our Writers</div>
      <div class="author-row"><div class="ar-avatar" style="background:linear-gradient(135deg,var(--violet),var(--rose))">SK</div><div><div class="ar-name">Sophie Kim</div><div class="ar-count">CTO &middot; 14 articles</div></div></div>
      <div class="author-row"><div class="ar-avatar" style="background:linear-gradient(135deg,var(--blue),var(--cyan))">MR</div><div><div class="ar-name">Marcus Reid</div><div class="ar-count">CEO &middot; 11 articles</div></div></div>
      <div class="author-row"><div class="ar-avatar" style="background:linear-gradient(135deg,var(--rose),var(--violet))">TC</div><div><div class="ar-name">Tara Chen</div><div class="ar-count">Chief DS &middot; 10 articles</div></div></div>
      <div class="author-row"><div class="ar-avatar" style="background:linear-gradient(135deg,var(--amber),#fb923c)">DN</div><div><div class="ar-name">David Nguyen</div><div class="ar-count">VP Eng &middot; 7 articles</div></div></div>
      <div class="author-row"><div class="ar-avatar" style="background:linear-gradient(135deg,var(--emerald),var(--cyan))">RO</div><div><div class="ar-name">Raj Okafor</div><div class="ar-count">VP CS &middot; 5 articles</div></div></div>
    </div>

    <div class="sb-widget rv">
      <div class="sw-title">Related Resources</div>
      <div style="display:flex;flex-direction:column;gap:12px;margin-top:4px">
        <a href="{{ route('case-studies') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg3);border-radius:9px;border:1px solid var(--brd);transition:border-color .2s" onmouseover="this.style.borderColor='var(--brd2)'" onmouseout="this.style.borderColor='var(--brd)'">
          <span style="font-size:20px">📁</span>
          <div><div style="font-size:13px;font-weight:600;margin-bottom:2px">Case Studies</div><div style="font-family:var(--fm);font-size:10px;color:var(--g400)">Full customer stories &rarr;</div></div>
        </a>
        <a href="{{ route('simulator') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg3);border-radius:9px;border:1px solid var(--brd);transition:border-color .2s" onmouseover="this.style.borderColor='var(--brd2)'" onmouseout="this.style.borderColor='var(--brd)'">
          <span style="font-size:20px">🎮</span>
          <div><div style="font-size:13px;font-weight:600;margin-bottom:2px">Try the Simulator</div><div style="font-family:var(--fm);font-size:10px;color:var(--g400)">See predictions live &rarr;</div></div>
        </a>
        <a href="{{ route('book-demo') }}" style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg3);border-radius:9px;border:1px solid var(--brd);transition:border-color .2s" onmouseover="this.style.borderColor='var(--brd2)'" onmouseout="this.style.borderColor='var(--brd)'">
          <span style="font-size:20px">📅</span>
          <div><div style="font-size:13px;font-weight:600;margin-bottom:2px">Book a Demo</div><div style="font-family:var(--fm);font-size:10px;color:var(--g400)">30 min &middot; Your data &rarr;</div></div>
        </a>
      </div>
    </div>

  </aside>
</div>

<section class="by-industry">
  <div class="bi-in rv">
    <div style="text-align:center;margin-bottom:0">
      <div style="font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:12px;display:flex;align-items:center;justify-content:center;gap:10px"><span style="width:16px;height:1px;background:var(--blue);display:inline-block"></span>Browse by Industry<span style="width:16px;height:1px;background:var(--blue);display:inline-block"></span></div>
      <h2 style="font-weight:700;font-size:clamp(22px,2.6vw,30px);letter-spacing:-1px;margin-bottom:8px">Find articles for your vertical</h2>
      <p style="font-size:15px;color:var(--g300);max-width:440px;margin:0 auto;line-height:1.65">Industry-specific insights, benchmarks, and case studies for all 15 verticals we serve.</p>
    </div>
    <div class="bi-grid">
      <div class="bi-card" onclick="location.href='{{ route('banking') }}'"><div class="bi-icon">🏦</div><div class="bi-name">Banking</div><div class="bi-count">6 articles</div></div>
      <div class="bi-card" onclick="location.href='{{ route('industries') }}'"><div class="bi-icon">🛍️</div><div class="bi-name">Retail</div><div class="bi-count">5 articles</div></div>
      <div class="bi-card" onclick="location.href='{{ route('industries') }}'"><div class="bi-icon">🏥</div><div class="bi-name">Healthcare</div><div class="bi-count">4 articles</div></div>
      <div class="bi-card" onclick="location.href='{{ route('industries') }}'"><div class="bi-icon">📚</div><div class="bi-name">Education</div><div class="bi-count">5 articles</div></div>
      <div class="bi-card" onclick="location.href='{{ route('industries') }}'"><div class="bi-icon">💻</div><div class="bi-name">SaaS</div><div class="bi-count">7 articles</div></div>
      <div class="bi-card" onclick="location.href='{{ route('industries') }}'"><div class="bi-icon">📡</div><div class="bi-name">Telecom</div><div class="bi-count">3 articles</div></div>
      <div class="bi-card" onclick="location.href='{{ route('industries') }}'"><div class="bi-icon">✈️</div><div class="bi-name">Travel</div><div class="bi-count">3 articles</div></div>
      <div class="bi-card" onclick="location.href='{{ route('industries') }}'"><div class="bi-icon">🛡️</div><div class="bi-name">Insurance</div><div class="bi-count">2 articles</div></div>
      <div class="bi-card" onclick="location.href='{{ route('industries') }}'"><div class="bi-icon">🎬</div><div class="bi-name">Media</div><div class="bi-count">2 articles</div></div>
      <div class="bi-card" onclick="location.href='{{ route('industries') }}'"><div class="bi-icon">⚡</div><div class="bi-name">All Industries</div><div class="bi-count">View all 15 &rarr;</div></div>
    </div>
  </div>
</section>

<section class="cta-section">
  <h2 class="rv">Ready to stop reading about AI <span>and start using it?</span></h2>
  <p class="rv">Book a 30-minute demo and see X Platforms predict your actual customers &mdash; live, in your industry, on your data.</p>
  <div class="rv" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a href="{{ route('book-demo') }}" class="btn-fill">Book a Demo</a>
    <a href="{{ route('simulator') }}" class="btn-g">Try the Simulator</a>
  </div>
</section>

<footer class="foot"><div class="foot-in">
  <div><a href="{{ url('/') }}" class="logo"><img src="{{ asset('images/xplatforms_logo.jpeg') }}" alt="X Platforms" style="height:32px;width:auto;display:block"></a><p class="foot-desc">The world's first 8-layer AI intelligence engine.</p></div>
  <div class="foot-c"><h5>Product</h5><a href="{{ route('platform.architecture') }}">Architecture</a><a href="#">Integrations</a><a href="{{ route('industries') }}">Industries</a><a href="{{ route('pricing') }}">Pricing</a></div>
  <div class="foot-c"><h5>Company</h5><a href="{{ route('about') }}">About</a><a href="{{ route('careers') }}">Careers</a><a href="{{ route('blog') }}">Blog</a><a href="{{ route('contact') }}">Contact</a></div>
  <div class="foot-c"><h5>Resources</h5><a href="#">Documentation</a><a href="{{ route('case-studies') }}">Case Studies</a><a href="#">API Reference</a><a href="{{ route('security') }}">Security</a></div>
</div><div class="foot-b"><span>&copy; {{ date('Y') }} X Platforms.</span><span><a href="{{ route('privacy') }}" style="color:inherit">Privacy</a> &middot; <a href="{{ route('terms') }}" style="color:inherit">Terms</a> &middot; <a href="{{ route('security') }}" style="color:inherit">Security</a></span></div></footer>

<script>
const cv=document.getElementById('neural'),cx=cv.getContext('2d');
let W,H,nd=[];
function rsz(){W=cv.width=innerWidth;H=cv.height=innerHeight;}
addEventListener('resize',rsz);rsz();
for(let i=0;i<50;i++)nd.push({x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.28,vy:(Math.random()-.5)*.28,r:Math.random()*1.4+.6,p:Math.random()*6.28});
(function draw(){
  cx.clearRect(0,0,W,H);
  nd.forEach((n,i)=>{
    n.x+=n.vx;n.y+=n.vy;n.p+=.01;
    if(n.x<0||n.x>W)n.vx*=-1;
    if(n.y<0||n.y>H)n.vy*=-1;
    nd.forEach((m,j)=>{
      if(j<=i)return;
      const d=Math.hypot(n.x-m.x,n.y-m.y);
      if(d<150){cx.beginPath();cx.moveTo(n.x,n.y);cx.lineTo(m.x,m.y);cx.strokeStyle=`rgba(79,143,255,${(1-d/150)*.09})`;cx.lineWidth=.5;cx.stroke();}
    });
    cx.beginPath();cx.arc(n.x,n.y,n.r+Math.sin(n.p)*.35,0,6.28);cx.fillStyle='rgba(79,143,255,.2)';cx.fill();
  });
  requestAnimationFrame(draw);
})();

let currentCat='all';
function setCat(cat,btn){
  currentCat=cat;
  document.querySelectorAll('.fp-pill').forEach(b=>b.classList.remove('active'));
  const target=document.querySelector(`.fp-pill[data-cat="${cat}"]`);
  if(target)target.classList.add('active');
  filterArticles();
}
function filterArticles(){
  const search=document.getElementById('blogSearch').value.toLowerCase();
  const cards=document.querySelectorAll('.ac');
  let visible=0;
  cards.forEach(c=>{
    const catMatch=currentCat==='all'||c.dataset.cat===currentCat;
    const searchMatch=!search||c.dataset.title.includes(search)||c.querySelector('.ac-title').textContent.toLowerCase().includes(search);
    const show=catMatch&&searchMatch;
    c.classList.toggle('hidden',!show);
    if(show)visible++;
  });
  document.getElementById('resultsLabel').textContent=`Showing ${visible} article${visible!==1?'s':''}`;
  document.getElementById('noResults').classList.toggle('hidden',visible>0);
  document.getElementById('loadMoreWrap').classList.toggle('hidden',visible===0||search||currentCat!=='all');
}
function clearAll(e){
  e.preventDefault();
  document.getElementById('blogSearch').value='';
  currentCat='all';
  document.querySelectorAll('.fp-pill').forEach(b=>b.classList.remove('active'));
  document.querySelector('[data-cat="all"]').classList.add('active');
  filterArticles();
}
let loadCount=0;
function loadMore(){
  loadCount++;
  const btn=document.querySelector('.load-more-btn');
  btn.innerHTML='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg> Loading...';
  setTimeout(()=>{
    btn.innerHTML='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h5M20 20v-5h-5"/><path d="M20.49 9A9 9 0 005.64 5.64L4 4m16 16l-1.64-1.64A9 9 0 013.51 15"/></svg> Load more articles';
    document.getElementById('loadMoreCount').textContent=`Showing ${12+loadCount*6} of 47 articles`;
    if(loadCount>=5)document.getElementById('loadMoreWrap').style.display='none';
  },800);
}
function subscribeNL(){
  const email=document.getElementById('snlEmail').value;
  if(!email||!email.includes('@')){
    document.getElementById('snlEmail').style.borderColor='var(--rose)';
    setTimeout(()=>document.getElementById('snlEmail').style.borderColor='',2000);
    return;
  }
  document.getElementById('snlForm').style.display='none';
  document.getElementById('snlSuccess').style.display='block';
}
document.querySelectorAll('.share-btn').forEach(btn=>{
  btn.addEventListener('click',e=>{
    e.stopPropagation();
    if(btn.title==='Copy link'){
      navigator.clipboard?.writeText(window.location.href).then(()=>{
        btn.style.background='var(--emerald)';
        setTimeout(()=>btn.style.background='',1500);
      });
    }
  });
});
const obs=new IntersectionObserver(e=>{
  e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('vis');obs.unobserve(x.target);}});
},{threshold:.07,rootMargin:'0px 0px -40px 0px'});
document.querySelectorAll('.rv').forEach(el=>obs.observe(el));
addEventListener('scroll',()=>{document.querySelector('.nav-bg').style.background=scrollY>40?'rgba(5,7,14,.92)':'rgba(5,7,14,.8)';});
const style=document.createElement('style');
style.textContent='@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}';
document.head.appendChild(style);
</script>
</body>
</html>
