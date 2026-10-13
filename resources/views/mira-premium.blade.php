<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Premium Mira &ndash; X Platforms</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
/* Premium palette — light, like the basic (homepage) Ask Mira, with gold
   accents. Uses the same variable names as the landing page so the shared Ask
   Mira styles (partials/ask-mira) pick it up: --white is the main text colour
   and --blue the accent (the landing page's naming), so here they hold near-
   black and gold. --gold-2/--gold-3 stay bright for gradients and buttons. */
:root{
  --body-bg:#fafafa;
  --bg:#fff;--bg2:#fff;--bg3:#f5f5f7;--card:#fff;--card-h:#fff;
  --blue:#a8801f;--blue2:#8a6814;--cyan:#8a6814;--violet:#a8801f;
  --emerald:#16a34a;--amber:#d97706;--rose:#e0564c;
  --white:#17181a;--g100:#2a2a2e;--g200:#4a4a50;--g300:#6b6b70;--g400:#9a9a9e;--g500:#d9d9de;--g600:#ececef;
  --brd:#e6e6ea;--brd2:#e2c77e;
  --acc-rgb:168,128,31;--on-acc:#1a1406;
  --gold:#a8801f;--gold-2:#f6dd98;--gold-3:#b8902f;
  --f1:'Outfit',system-ui,sans-serif;--fm:'IBM Plex Mono',monospace;
  --ease:cubic-bezier(.16,1,.3,1);--mw:1200px;
}
html{scroll-behavior:smooth}
body{background:var(--body-bg);color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;min-height:100vh;overflow-x:hidden}
/* Soft gold glow and a fine grid that fades out down the page. */
body::before{content:"";position:fixed;inset:0;pointer-events:none;z-index:0;
  background:radial-gradient(900px 480px at 50% -120px,rgba(232,196,104,.22),transparent 70%),
             radial-gradient(600px 400px at 100% 40%,rgba(232,196,104,.05),transparent 70%),
             radial-gradient(600px 400px at 0% 70%,rgba(111,155,255,.04),transparent 70%)}
body::after{content:"";position:fixed;inset:0;pointer-events:none;z-index:0;opacity:.6;
  background-image:linear-gradient(var(--brd) 1px,transparent 1px),linear-gradient(90deg,var(--brd) 1px,transparent 1px);background-size:64px 64px;
  -webkit-mask-image:radial-gradient(ellipse at 50% 0%,#000 0%,transparent 60%);mask-image:radial-gradient(ellipse at 50% 0%,#000 0%,transparent 60%)}
a{color:inherit;text-decoration:none}

/* NAV */
.nav{position:relative;z-index:2}
.nav-in{max-width:var(--mw);margin:0 auto;padding:0 40px;height:72px;display:flex;align-items:center;justify-content:space-between;gap:16px;border-bottom:1px solid var(--brd)}
.nav-logo{display:flex;align-items:center;gap:10px;font-weight:700;font-size:19px;letter-spacing:-.02em;color:#17181a}
.nav-logo svg{width:26px;height:26px;display:block}
.nav-back{font-size:14.5px;color:var(--g300);transition:color .2s}
.nav-back:hover{color:var(--gold)}

/* HERO */
.sec{position:relative;z-index:1;padding:0 40px 80px;max-width:var(--mw);margin:0 auto}
.hero{text-align:center;padding:40px 0 34px}
.crown{display:inline-flex;align-items:center;gap:8px;padding:6px 14px 6px 8px;border-radius:999px;border:1px solid var(--brd2);background:#fdf6e3;font-size:13px;color:#8a6814;font-weight:500;letter-spacing:.02em}
.crown i{width:20px;height:20px;border-radius:50%;display:grid;place-items:center;font-style:normal;font-size:11px;background:linear-gradient(135deg,var(--gold-2),var(--gold-3));color:#1a1406}
.hero-title{margin:18px 0 12px;font-size:clamp(40px,6vw,72px);font-weight:800;letter-spacing:-.045em;line-height:1}
.hero-title .mira{background:linear-gradient(180deg,var(--gold-2) 10%,var(--gold) 50%,var(--gold-3) 100%);-webkit-background-clip:text;background-clip:text;color:transparent;filter:drop-shadow(0 8px 24px rgba(184,144,47,.25))}
.hero-sub{max-width:760px;margin:0 auto;color:var(--g300);font-size:17px;font-weight:300;line-height:1.6}
.hero-sub b{color:var(--white);font-weight:500}
.hero-pills{display:flex;justify-content:center;gap:10px;margin-top:22px;flex-wrap:wrap}
.hero-pill{display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:999px;border:1px solid var(--brd);background:var(--card);box-shadow:0 1px 2px rgba(0,0,0,.04);font-size:13px;color:var(--g300)}
.hero-pill::before{content:"";width:7px;height:7px;border-radius:50%;background:var(--c);box-shadow:0 0 10px var(--c)}
.hero-pill b{color:var(--white);font-weight:600;font-family:var(--fm);font-size:12px;letter-spacing:.06em}

@include('partials.ask-mira.styles')

/* ============ Compare with a competitor (Analyse tab) ============ */
@include('partials.ask-mira.compare-styles')
/* Result panel: the shared .cx-* report in the basic widget's light greys, with gold accents. */
#miraComparePanel{
  --cx-accent:#a8801f;--cx-accent-soft:#fbf4e2;--cx-accent-line:#ecd9a6;--cx-on-accent:#fff;
  margin-top:16px;border:1px solid var(--brd);border-radius:22px;padding:24px 26px;color:var(--white);font-family:var(--f1)}
.mira-compare-vs{text-align:center;font-family:var(--fm);font-size:11px;font-weight:500;letter-spacing:2px;color:var(--g400);text-transform:uppercase;margin:2px 0 -2px}
.mira-compare{display:flex;flex-direction:column;gap:12px}
.mira-compare[hidden]{display:none}
#panelAnalyze .analyze-status{--spin-c:var(--gold)}
/* Google preview title in Google's link colour. */
#ask-mira,#miraComparePanel,.a-modal{--serp-title:#1a0dab}
/* Toggle: dark pill with a thin gold gradient border and a gold icon circle. */
.mira-compare-toggle{align-self:center;display:inline-flex;align-items:center;gap:10px;padding:6px 16px 6px 6px;border:1.5px solid transparent;border-radius:999px;
  cursor:pointer;font-family:var(--f1);font-size:13.5px;font-weight:600;color:var(--white);
  background:linear-gradient(#141217,#141217) padding-box,linear-gradient(135deg,var(--gold-2),var(--gold-3)) border-box;
  box-shadow:0 4px 16px rgba(232,196,104,.15);transition:transform .15s,box-shadow .2s}
.mira-compare-toggle:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(232,196,104,.28)}
.mira-compare-toggle:focus-visible{outline:2px solid var(--gold);outline-offset:3px}
.mct-icon{width:28px;height:28px;border-radius:50%;display:grid;place-items:center;flex-shrink:0;background:linear-gradient(135deg,var(--gold-2),var(--gold-3))}
.mct-icon svg{width:14px;height:14px;fill:none;stroke:#17120a;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round;grid-area:1/1}
.mct-i-remove{display:none}
.mira-compare-toggle.on{color:var(--g200);background:linear-gradient(#141217,#141217) padding-box,linear-gradient(var(--brd),var(--brd)) border-box;box-shadow:none}
.mira-compare-toggle.on .mct-icon{background:rgba(255,255,255,.08)}
.mira-compare-toggle.on .mct-icon svg{stroke:var(--g200)}
.mira-compare-toggle.on .mct-i-compare{display:none}
.mira-compare-toggle.on .mct-i-remove{display:block}
/* Comparing: only the competitor box keeps a send button; the first box keeps its height. */
#panelAnalyze.comparing .analyze-form > .chat-input-wrap > .chat-send{display:none}
#panelAnalyze.comparing .analyze-form > .chat-input-wrap{min-height:62px;padding-right:18px}

/* PANELS */
.chat-sidebar,.chat-card{background:linear-gradient(180deg,var(--card-h),var(--card));border:1px solid var(--brd);border-radius:22px;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px)}
.chat-sidebar{padding:20px;scrollbar-width:thin;scrollbar-color:#2a2a30 transparent}
.side-lbl{font-family:var(--fm);font-size:10.5px;letter-spacing:.16em;color:var(--g400)}

/* Your website card */
.site-box{display:flex;gap:12px;align-items:center;padding:12px;border-radius:16px;background:rgba(0,0,0,.35);border:1px solid var(--brd);font-size:12.5px;line-height:1.4}
.site-empty{color:var(--g300)}
.site-fav{width:38px;height:38px;border-radius:12px;display:grid;place-items:center;flex:none;background:linear-gradient(135deg,#1d3a8a,#6f9bff);color:#fff;font-weight:700;font-size:14px}
.site-meta{min-width:0}
.site-title{color:var(--white);font-weight:600;font-size:14px;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.site-url{display:flex;align-items:center;gap:6px;color:var(--g300);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.site-url::before{content:"";width:6px;height:6px;border-radius:50%;flex:none;background:var(--emerald);box-shadow:0 0 8px var(--emerald)}
.site-url:hover{color:var(--gold)}

/* Try asking: SEO / AEO / GEO group pills and question cards */
.starter-pill{display:inline-flex;align-items:center;gap:6px;max-width:100%;margin:14px 0 8px;padding:3px 10px 3px 3px;border-radius:999px;
  border:1px solid color-mix(in srgb,var(--pill-c) 35%,transparent);background:color-mix(in srgb,var(--pill-c) 10%,transparent)}
.side-lbl + .starter-pill{margin-top:0}
.starter-icon{width:18px;height:18px;border-radius:50%;background:var(--pill-c);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.starter-icon svg{width:10px;height:10px;fill:none;stroke:#0b0b0e;stroke-width:2.6;stroke-linecap:round;stroke-linejoin:round}
.starter-label{font-family:var(--fm);font-size:10px;font-weight:500;letter-spacing:1px;color:var(--pill-c);padding-right:6px;border-right:1px solid color-mix(in srgb,var(--pill-c) 35%,transparent)}
.starter-tagline{font-size:11px;font-weight:500;color:var(--white);opacity:.85;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.starter-seo{--pill-c:#6f9bff}
.starter-aeo{--pill-c:#f2a64a}
.starter-geo{--pill-c:#3fc495}
.starter-other{--pill-c:#e8c468}
#ask-mira .side-starter{display:flex;gap:8px;align-items:flex-start;background:rgba(255,255,255,.025);border:1px solid var(--brd);border-radius:14px;color:var(--g100);transition:all .2s ease}
#ask-mira .side-starter::before{content:"\2192";color:var(--g400);flex:none;transition:all .2s}
#ask-mira .side-starter:hover{border-color:var(--brd2);background:rgba(232,196,104,.06);color:var(--white);transform:translateX(3px)}
#ask-mira .side-starter:hover::before{color:var(--gold)}

/* Chat header: pill tab switcher + new-chat button */
.chat-tabs{justify-content:space-between;padding:14px 16px}
.tab-seg{display:inline-flex;padding:4px;border-radius:14px;background:rgba(0,0,0,.4);border:1px solid var(--brd);min-width:0}
.tab-seg .chat-tab-box{flex:none;background:none;border:0;padding:0;border-radius:10px}
.tab-seg .chat-tab-box.active{background:linear-gradient(180deg,#24211a,#18160f);box-shadow:inset 0 0 0 1px var(--brd2),0 4px 14px rgba(0,0,0,.4)}
.tab-seg .chat-tab{padding:9px 16px;color:var(--g300);font-weight:500;font-size:14px}
.tab-seg .chat-tab-box.active .chat-tab{color:var(--gold-2)}
.tab-seg .chat-tab-box:not(.active):hover .chat-tab{color:var(--white)}
.icon-btn{width:38px;height:38px;border-radius:12px;border:1px solid var(--brd);background:var(--card);display:grid;place-items:center;cursor:pointer;color:var(--g300);transition:all .2s;flex:none}
.icon-btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2}
.icon-btn:hover{color:var(--gold);border-color:var(--brd2)}

/* Messages: gold user bubbles, Mira replies with an "M" avatar */
.chat-messages{padding:26px 26px 12px;gap:18px;scrollbar-width:none}
.chat-messages::-webkit-scrollbar{display:none}
.chat-msg.user{background:linear-gradient(135deg,var(--gold-2),var(--gold) 60%,var(--gold-3));color:#17120a;font-weight:500;border-radius:18px 18px 4px 18px;box-shadow:0 10px 30px rgba(232,196,104,.18)}
.chat-msg.bot{position:relative;margin-left:44px;max-width:calc(85% - 44px);background:rgba(255,255,255,.04);border:1px solid var(--brd);color:var(--g100);border-radius:4px 18px 18px 18px}
.chat-msg.bot::before{content:"M";position:absolute;left:-44px;top:0;width:32px;height:32px;border-radius:10px;display:grid;place-items:center;
  font-weight:800;font-size:14px;font-style:normal;color:#17120a;background:radial-gradient(circle at 30% 25%,var(--gold-2),var(--gold-3));box-shadow:0 0 0 1px var(--brd2),0 6px 18px rgba(232,196,104,.25)}
.chat-msg.bot a{color:var(--gold);text-decoration:none;border-bottom:1px dashed var(--brd2)}
.chat-msg.typing{color:var(--g300)}

/* Clickable suggested questions under a reply */
.chat-suggest{display:flex;flex-wrap:wrap;gap:8px;align-self:flex-start;max-width:calc(85% - 44px);margin:-6px 0 0 44px}
.suggest-chip{background:none;border:1px solid var(--brd);border-radius:999px;padding:7px 13px;color:var(--g300);font-family:var(--f1);font-size:13px;line-height:1.4;text-align:left;cursor:pointer;transition:all .2s}
.suggest-chip:hover{color:var(--gold-2);border-color:var(--brd2);background:rgba(232,196,104,.06)}

/* Composer */
.chat-card .chat-input-wrap{margin:12px 20px 6px;padding:8px 8px 8px 18px;border:1px solid var(--brd);border-radius:18px;background:rgba(0,0,0,.45);align-items:center;transition:border-color .2s,box-shadow .2s}
.chat-card .analyze-form .chat-input-wrap{margin:0}
.chat-card .chat-input-wrap:focus-within{border-color:var(--brd2);box-shadow:0 0 0 4px rgba(232,196,104,.08)}
.chat-card .chat-input{background:none;border:0;padding:10px 0;color:var(--white)}
.chat-card .chat-input::placeholder{color:var(--g400)}
/* Chrome paints autofilled fields light blue with dark text, overriding the
   page's colours. An inset shadow in the field's own colour covers it, and the
   long transition stops Chrome's background from showing through. */
.chat-card .chat-input:-webkit-autofill,
.lead-modal-field input:-webkit-autofill{
  -webkit-text-fill-color:var(--white);caret-color:var(--white);
  -webkit-box-shadow:0 0 0 1000px #0b0b0e inset;box-shadow:0 0 0 1000px #0b0b0e inset;
  transition:background-color 9999s ease-in-out 0s}
.chat-card .chat-input:-webkit-autofill:focus,
.lead-modal-field input:-webkit-autofill:focus{-webkit-box-shadow:0 0 0 1000px #0b0b0e inset;box-shadow:0 0 0 1000px #0b0b0e inset}
.chat-send{width:44px;height:44px;border-radius:13px;align-self:center;background:linear-gradient(135deg,var(--gold-2),var(--gold-3));box-shadow:0 6px 18px rgba(232,196,104,.3);transition:transform .15s}
.chat-send:hover{opacity:1;transform:translateY(-1px) scale(1.03)}
.chat-footer{border-top:0;padding:4px 20px 14px;justify-content:flex-end}
.mira-status{background:rgba(255,255,255,.05)}

/* Analyse tab, report and pop-ups */
.analysis-panel{border-radius:22px;background:linear-gradient(180deg,var(--card-h),var(--card))}
.a-modal-overlay,.lead-modal-overlay{background:rgba(23,24,26,.45)}
.a-modal,.lead-modal{background:#121216;border-color:var(--brd);box-shadow:0 20px 60px rgba(0,0,0,.6);color-scheme:dark;scrollbar-width:thin;scrollbar-color:#3a3a42 transparent}

/* ============ Light Ask Mira area ============
   The chat widget, reports and pop-ups use the basic (homepage) Ask Mira's
   light look — white cards and dark text — on a warm grey (sidebar #ebe7de,
   chat #e9e5dc) that suits the gold, a little darker than the basic widget, on
   the light page. Gold stays the accent, a shade darker so it reads on light
   backgrounds. */
.chat-layout,#miraComparePanel,.analysis-panel,.a-modal,.lead-modal{
  --white:#17181a;--g100:#2a2a2e;--g200:#4a4a50;--g300:#6b6b70;--g400:#9a9a9e;--g500:#d9d9de;--g600:#ececef;
  --bg3:#f5f5f7;--card:#fff;--card-h:#fff;--brd:#e6e6ea;--brd2:#e2c77e;
  --gold:#a8801f;--blue:#a8801f;--blue2:#8a6814;--acc-rgb:168,128,31;
  --emerald:#16a34a;--amber:#d97706;--rose:#e0564c;
  color:var(--white)}
.chat-sidebar,.chat-card{backdrop-filter:none;-webkit-backdrop-filter:none;border-color:#ddd6c6;box-shadow:0 20px 50px -24px rgba(23,24,26,.18)}
.chat-sidebar{background:#ebe7de;scrollbar-color:#cdc5b4 transparent}
.chat-card{background:#e9e5dc}
.chat-card .chat-tabs{border-bottom-color:#ddd6c6}
.side-lbl{color:#8a8a8e}
.site-box{background:#fff}
.site-fav{background:linear-gradient(135deg,#3f6fd8,#6f9bff)}
.starter-seo{--pill-c:#3f6fd8}
.starter-aeo{--pill-c:#c77a12}
.starter-geo{--pill-c:#1f9e6e}
.starter-other{--pill-c:#a8801f}
.starter-icon svg{stroke:#fff}
#ask-mira .side-starter{background:#fff;color:#2a2a2e}
#ask-mira .side-starter:hover{background:#fdf8ea;border-color:#e2c77e;color:#17181a}
.tab-seg{background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.05)}
.tab-seg .chat-tab-box.active{background:linear-gradient(135deg,#f6dd98,#e8c468 55%,#c9a13a);box-shadow:0 4px 14px rgba(184,144,47,.3)}
.tab-seg .chat-tab-box.active .chat-tab{color:#1a1406;font-weight:600}
.icon-btn{background:#fff}
.chat-msg.bot{background:#fff;border-color:#e6e6ea;color:#2a2a2e;box-shadow:0 1px 2px rgba(0,0,0,.04)}
.chat-msg.bot a{color:#8a6814}
.suggest-chip{background:#fff;color:#4a4a50}
.suggest-chip:hover{background:#fdf8ea;color:#8a6814}
.chat-card .chat-input-wrap{background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.05)}
.chat-card .chat-input:-webkit-autofill,
.lead-modal-field input:-webkit-autofill,
.chat-card .chat-input:-webkit-autofill:focus,
.lead-modal-field input:-webkit-autofill:focus{-webkit-text-fill-color:#17181a;caret-color:#17181a;-webkit-box-shadow:0 0 0 1000px #fff inset;box-shadow:0 0 0 1000px #fff inset}
.mira-status{background:rgba(0,0,0,.05)}
.mira-compare-toggle,.mira-compare-toggle.on{color:#17181a;box-shadow:0 2px 10px rgba(184,144,47,.15)}
.mira-compare-toggle{background:linear-gradient(#fff,#fff) padding-box,linear-gradient(135deg,var(--gold-2),var(--gold-3)) border-box}
.mira-compare-toggle.on{color:#4a4a50;background:linear-gradient(#fff,#fff) padding-box,linear-gradient(#e6e6ea,#e6e6ea) border-box}
.mira-compare-toggle.on .mct-icon{background:#f1f1f4}
.mira-compare-toggle.on .mct-icon svg{stroke:#4a4a50}
.analysis-panel{background:#f2f2f3}
.a-modal,.lead-modal{background:#fff;border-color:#e6e6ea;color-scheme:light;scrollbar-color:#cfcfd4 transparent}
#miraComparePanel{border-color:#e6e6ea;box-shadow:0 20px 50px -24px rgba(23,24,26,.18)}

/* ============ Welcome: animated gold orb ============
   The homepage Ask Mira's orb and greeting, in gold: the same orb image
   recoloured with a sepia filter, a gold aura, sweeping ring, ripples and
   orbiting sparks. Shown in an empty chat; removed on the first message. */
.prem-empty{margin:auto;display:flex;flex-direction:column;align-items:center;text-align:center;padding:0 24px 6px}
.prem-orb-wrap{position:relative;width:104px;height:104px;margin:38px 0 46px;display:grid;place-items:center;isolation:isolate}
.prem-orb{position:relative;z-index:2;width:104px;height:104px;display:block;
  --orb-tint:sepia(1) saturate(2.4) hue-rotate(-12deg);
  filter:var(--orb-tint) drop-shadow(0 10px 30px rgba(212,175,55,.35));
  animation:premOrbFloat 6s ease-in-out infinite,premOrbSpin 24s linear infinite,premOrbGlow 4s ease-in-out infinite}
@keyframes premOrbFloat{0%,100%{translate:0 0}50%{translate:0 -10px}}
@keyframes premOrbSpin{to{rotate:360deg}}
@keyframes premOrbGlow{
  0%,100%{filter:var(--orb-tint) drop-shadow(0 10px 30px rgba(212,175,55,.35))}
  50%{filter:var(--orb-tint) drop-shadow(0 14px 44px rgba(212,175,55,.6)) drop-shadow(0 0 22px rgba(246,221,152,.5))}}
.prem-orb-wrap::before{content:"";position:absolute;inset:-42px;border-radius:50%;z-index:0;
  background:radial-gradient(circle,rgba(232,196,104,.38) 0%,rgba(246,221,152,.18) 42%,transparent 70%);
  animation:premAura 4s ease-in-out infinite}
.prem-orb-wrap::after{content:"";position:absolute;inset:-9px;border-radius:50%;z-index:1;
  background:conic-gradient(from 0deg,transparent 0deg,rgba(212,175,55,.85) 70deg,rgba(246,221,152,.8) 130deg,transparent 200deg,transparent 360deg);
  -webkit-mask:radial-gradient(circle,transparent calc(50% - 2px),#000 calc(50% - 1px));
          mask:radial-gradient(circle,transparent calc(50% - 2px),#000 calc(50% - 1px));
  animation:premOrbSpin 5s linear infinite}
.prem-orb-ripple{position:absolute;inset:-4px;border-radius:50%;z-index:0;border:1.5px solid rgba(212,175,55,.45);animation:premRipple 3.6s ease-out infinite}
.prem-orb-ripple.r2{animation-delay:1.8s}
.prem-orb-orbit{position:absolute;inset:-22px;z-index:3;animation:premOrbSpin 7s linear infinite}
.prem-orb-orbit::before{content:"";position:absolute;top:0;left:50%;width:7px;height:7px;margin-left:-3.5px;border-radius:50%;
  background:#fff;box-shadow:0 0 8px 2px rgba(212,175,55,.95),0 0 16px 4px rgba(246,221,152,.6)}
.prem-orb-orbit.o2{inset:-32px;animation-duration:11s;animation-direction:reverse}
.prem-orb-orbit.o2::before{width:5px;height:5px;margin-left:-2.5px;box-shadow:0 0 6px 2px rgba(232,196,104,.95)}
@keyframes premAura{0%,100%{transform:scale(.92);opacity:.75}50%{transform:scale(1.08);opacity:1}}
@keyframes premRipple{0%{transform:scale(.9);opacity:.7}100%{transform:scale(1.75);opacity:0}}
.prem-empty h3{font-size:24px;font-weight:700;color:#17181a;letter-spacing:-.3px;line-height:1.3}
.prem-empty h3 .prem-accent{background:linear-gradient(180deg,#d9b24a,#a8801f);-webkit-background-clip:text;background-clip:text;color:transparent}
.prem-empty p{margin-top:8px;font-size:15px;color:#6b6b70;max-width:420px;line-height:1.55}
@media (max-width:640px){.prem-orb-wrap,.prem-orb{width:90px;height:90px}.prem-empty h3{font-size:20px}}
@media (prefers-reduced-motion:reduce){
  .prem-orb,.prem-orb-wrap::before,.prem-orb-wrap::after,.prem-orb-orbit{animation:none}
  .prem-orb-ripple{display:none}}

@media(max-width:640px){
  .nav-in,.sec{padding-left:16px;padding-right:16px}
  .sec{padding-bottom:56px}
  .hero{padding:28px 0 24px}
  .hero-sub{font-size:15.5px}
  .chat-messages{padding:20px 14px 8px}
}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{transition:none!important}}
</style>
</head>
<body>

<nav class="nav"><div class="nav-in">
  <a href="{{ url('/') }}" class="nav-logo">
    {{-- XPlatforms mark (the logo images have a dark background, so it's drawn here for the light page). --}}
    <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke-width="4.6" stroke-linecap="round">
      <path d="M4 4l5 5" stroke="#34a853"/><path d="M20 4l-5 5" stroke="#4285f4"/>
      <path d="M4 20l5-5" stroke="#fbbc05"/><path d="M20 20l-5-5" stroke="#ea4335"/>
    </svg>XPlatforms</a>
  <a href="{{ url('/') }}" class="nav-back">&larr; Back to home</a>
</div></nav>

<section class="sec" id="ask-mira">
  <div class="hero">
    <span class="crown"><i>&#9733;</i> Premium AI Assistant</span>
    <h1 class="hero-title">Ask <span class="mira">Mira</span></h1>
    <p class="hero-sub">Paste your website URL, then ask anything about it. Answers focus on <b>AEO (Answer Engine Optimization)</b>, getting your site picked as the answer in featured snippets, "People also ask" and voice search, and cover <b>SEO and GEO</b> (AI chatbot visibility) too.</p>
    <div class="hero-pills">
      <span class="hero-pill" style="--c:#6f9bff"><b>SEO</b> Get found</span>
      <span class="hero-pill" style="--c:#f2a64a"><b>AEO</b> Be the answer</span>
      <span class="hero-pill" style="--c:#3fc495"><b>GEO</b> Get recommended by AI</span>
    </div>
  </div>

  <div class="chat-page-wrap">
    <div class="chat-layout">

      <aside class="chat-sidebar">
        <div>
          <div class="side-lbl">Your website</div>
          <div class="site-box" id="siteBox"><span class="site-empty">No website yet. Paste your URL in the chat to start.</span></div>
        </div>
        <div>
          <div class="side-lbl">Try asking</div>
          {{-- Questions come from askmirap_predefined_prompts (placement = sidebar),
               grouped by category. This list only holds each category's look and
               display order; a category without an entry here still shows, with
               a plain pill. --}}
          @php
            $groupDesign = [
              'seo' => ['label' => 'SEO', 'tagline' => 'Get found on Google',
                        'icon' => '<circle cx="11" cy="11" r="6"/><path d="M20 20l-4.5-4.5"/>'],
              'aeo' => ['label' => 'AEO', 'tagline' => 'Be the answer',
                        'icon' => '<path d="M21 12a8 8 0 01-11.6 7.1L4 20l1-4.6A8 8 0 1121 12z"/><path d="M9 12h6"/>'],
              'geo' => ['label' => 'GEO', 'tagline' => 'Get recommended by AI',
                        'icon' => '<path d="M10 3l1.8 4.7L16.5 9.5l-4.7 1.8L10 16l-1.8-4.7L3.5 9.5l4.7-1.8z"/><path d="M18 14l.9 2.1L21 17l-2.1.9L18 20l-.9-2.1L15 17l2.1-.9z"/>'],
            ];
            $categories = collect(array_keys($groupDesign))
              ->concat($sidebarPrompts->keys())
              ->unique()
              ->filter(fn ($c) => $sidebarPrompts->has($c));
          @endphp
          @foreach($categories as $category)
            @php
              $design = $groupDesign[$category] ?? ['label' => strtoupper($category), 'tagline' => null,
                        'icon' => '<circle cx="12" cy="12" r="4"/>'];
            @endphp
            <div class="starter-pill starter-{{ isset($groupDesign[$category]) ? $category : 'other' }}">
              <span class="starter-icon"><svg viewBox="0 0 24 24" aria-hidden="true">{!! $design['icon'] !!}</svg></span>
              <span class="starter-label" @unless($design['tagline']) style="border-right:none;padding-right:0" @endunless>{{ $design['label'] }}</span>
              @if($design['tagline'])
                <span class="starter-tagline">{{ $design['tagline'] }}</span>
              @endif
            </div>
            <div class="side-starters">
              @foreach($sidebarPrompts[$category] as $q)
                <button type="button" class="side-starter" onclick="askPremium({{ \Illuminate\Support\Js::from($q) }})">{{ $q }}</button>
              @endforeach
            </div>
          @endforeach
        </div>
      </aside>

      <div class="chat-card">
        <div class="chat-tabs">
          {{-- Tab boxes keep their ids: the shared script's switchChatTab() toggles .active on them. --}}
          <div class="tab-seg">
            <div class="chat-tab-box active" id="tabBoxAsk">
              <button type="button" class="chat-tab" id="tabBtnAsk" onclick="switchChatTab('ask')">
                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                Ask Mira Premium
              </button>
            </div>
            <div class="chat-tab-box" id="tabBoxAnalyze">
              <button type="button" class="chat-tab" id="tabBtnAnalyze" onclick="switchChatTab('analyze')">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                Analyse my website
              </button>
            </div>
          </div>
          <button type="button" class="icon-btn" onclick="resetPremium()" title="New chat" aria-label="New chat">
            <svg viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          </button>
        </div>

        <div class="chat-tab-panel" id="panelAsk">
          <div class="chat-messages" id="chatMsgs">
            {{-- Welcome (removed by sendPremium on the first message, restored by resetPremium). --}}
            <div class="prem-empty" id="premEmpty">
              <div class="prem-orb-wrap" aria-hidden="true">
                <span class="prem-orb-ripple"></span><span class="prem-orb-ripple r2"></span>
                <span class="prem-orb-orbit"></span><span class="prem-orb-orbit o2"></span>
                <img src="{{ asset('images/homepage/mira/orb.png') }}" class="prem-orb" alt="">
              </div>
              <h3>Hey, I'm <span class="prem-accent">Mira.</span> How can I help you today?</h3>
              <p>Paste your website URL to start the chat. I'll answer with SEO, AEO and GEO in mind.</p>
            </div>
          </div>
          <div class="chat-input-wrap">
            <textarea class="chat-input" id="chatInput" placeholder="https://yourwebsite.com" rows="1" onkeydown="if(event.key==='Enter'&amp;&amp;!event.shiftKey){event.preventDefault();sendPremium()}"></textarea>
            <button class="chat-send" id="chatSend" onclick="sendPremium()" aria-label="Send"><svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg></button>
          </div>
          <div class="chat-footer">
            <span class="mira-status {{ $premiumLive ? 'live' : 'basic' }}"><span class="status-dot"></span> Mira Premium</span>
          </div>
        </div>

        {{-- "Analyse my website": same free SEO/technical check as the landing
             page, driven by partials.ask-mira.script (chat.send / chat.analyze-lead). --}}
        <div class="chat-tab-panel" id="panelAnalyze" style="display:none">
          <div class="analyze-form">
            <p class="analyze-hint" id="analyzeHint">Paste your website URL — I'll run a real SEO, Technical and Speed check and show the full report below.</p>
            <div class="chat-input-wrap">
              <input type="text" class="chat-input" id="analyzeInput" placeholder="https://yourwebsite.com" onkeydown="if(event.key==='Enter'){event.preventDefault();premAnalyze()}">
              <button class="chat-send" onclick="premAnalyze()" aria-label="Analyse"><svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg></button>
            </div>
            {{-- Competitor comparison: hidden until "Compare with a competitor" is clicked. --}}
            <div class="mira-compare" id="premCompareRow" hidden>
              <div class="mira-compare-vs">vs</div>
              <div class="chat-input-wrap">
                <input type="text" class="chat-input" id="premCompetitorInput" placeholder="Your competitor's website, e.g. https://competitor.com" onkeydown="if(event.key==='Enter'){event.preventDefault();premAnalyze()}">
                <button class="chat-send" onclick="premAnalyze()" aria-label="Compare"><svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg></button>
              </div>
            </div>
            <button type="button" class="mira-compare-toggle" id="premCompareToggle" onclick="premToggleCompare()" aria-pressed="false">
              <span class="mct-icon" aria-hidden="true">
                {{-- Off: two opposing arrows (compare). On: left arrow (back to a single-site analysis). --}}
                <svg class="mct-i-compare" viewBox="0 0 24 24"><path d="M7 4L3 8l4 4"/><path d="M3 8h13"/><path d="M17 20l4-4-4-4"/><path d="M21 16H8"/></svg>
                <svg class="mct-i-remove" viewBox="0 0 24 24"><path d="M19 12H5"/><path d="M11 18l-6-6 6-6"/></svg>
              </span>
              <span class="mct-label" id="premCompareLabel">Compare with a competitor</span>
            </button>
            <p class="analyze-status" id="analyzeStatus"></p>
          </div>
          <div class="chat-footer">
            <span class="mira-status {{ $miraLive ? 'live' : 'basic' }}"><span class="status-dot"></span> Mira Premium</span>
          </div>
        </div>
      </div>


    </div>

    <div class="analysis-panel" id="analysisPanel">
      <div class="a-summary">
        <div class="analysis-url" id="analysisUrl"></div>
      </div>
      <div class="a-pie-grid" id="analysisPieGrid"></div>
    </div>

    {{-- Competitor comparison result (open — Premium), drawn by
         miraRenderComparison() in partials.ask-mira.compare-script. --}}
    <div class="mira-compare-panel" id="miraComparePanel" hidden></div>
  </div>
</section>

<div class="a-modal-overlay" id="analysisModalOverlay" onclick="if(event.target===this) closeAnalysisModal()">
  <div class="a-modal">
    <button type="button" class="a-modal-close" onclick="closeAnalysisModal()" aria-label="Close">&times;</button>
    <div class="a-modal-header">
      <div class="a-modal-pct" id="modalPct"></div>
      <div>
        <div class="a-modal-title" id="modalTitle"></div>
        <div class="a-modal-desc" id="modalDesc"></div>
      </div>
    </div>
    <div class="a-modal-checks" id="modalChecks"></div>
  </div>
</div>

<div class="lead-modal-overlay" id="leadModalOverlay" onclick="if(event.target===this) closeLeadModal()">
  <div class="lead-modal">
    <button type="button" class="lead-modal-close" onclick="closeLeadModal()" aria-label="Close">&times;</button>
    <div class="lead-modal-title">Before we analyze your site</div>
    <p class="lead-modal-sub">Enter your details and we'll run the free check right after.</p>
    <div class="lead-modal-field">
      <label for="leadName">Name</label>
      <input type="text" id="leadName" placeholder="Your name">
    </div>
    <div class="lead-modal-field">
      <label for="leadEmail">Email</label>
      <input type="email" id="leadEmail" placeholder="you@company.com">
    </div>
    <p class="lead-modal-error" id="leadModalError"></p>
    <button type="button" class="lead-modal-submit" id="leadModalSubmit" onclick="submitLeadAndAnalyze()">Continue</button>
  </div>
</div>

{{-- Shared Ask Mira script: provides switchChatTab, sendAnalyzeUrl, the lead
     modal, report rendering and renderBotMessage. The premium chat below
     reuses renderBotMessage. --}}
<script>
@include('partials.ask-mira.script', ['premium' => true])

/* Competitor comparison result (shared with the homepage): miraRenderComparison(). */
@include('partials.ask-mira.compare-script')
</script>

<script>
const PREMIUM_GREETING = document.getElementById('chatMsgs').innerHTML;
let premiumBusy = false;

// The page's CSRF token goes stale when the session expires (SESSION_LIFETIME)
// or is replaced, e.g. by logging in or out in another tab. Laravel then
// answers 419 "CSRF token mismatch". On a 419, reload the page's HTML in the
// background (which also starts a fresh session), take its new token, update
// the meta tag so the shared Analyse script uses it too, and retry once.
function csrfToken(){
  return document.querySelector('meta[name="csrf-token"]').content;
}
async function refreshCsrfToken(){
  const html=await (await fetch(location.pathname,{credentials:'same-origin',cache:'no-store'})).text();
  const m=html.match(/<meta name="csrf-token" content="([^"]+)"/);
  if(m) document.querySelector('meta[name="csrf-token"]').content=m[1];
}
async function postJson(url, body){
  const send=()=>fetch(url,{method:'POST',credentials:'same-origin',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},
    body:JSON.stringify(body||{})});
  let res=await send();
  if(res.status===419){
    await refreshCsrfToken();
    res=await send();
  }
  return res;
}

function addMsg(cls, text){
  const msgs=document.getElementById('chatMsgs');
  const el=document.createElement('div');
  el.className='chat-msg '+cls;
  if(cls==='bot') renderBotMessage(el, text); else el.textContent=text;
  msgs.appendChild(el);
  msgs.scrollTop=msgs.scrollHeight;
  return el;
}

// Shows `site` ({title, url}) in the sidebar's "Your website" card, or the empty
// state for null. chatReady=false (an Analyse-tab result) leaves the chat
// placeholder alone, since the chat hasn't loaded that site yet.
function setSite(site, chatReady){
  if(chatReady===undefined) chatReady=true;
  const box=document.getElementById('siteBox');
  const input=document.getElementById('chatInput');
  box.innerHTML='';
  if(!site){
    const s=document.createElement('span');s.className='site-empty';
    s.textContent='No website yet. Paste your URL in the chat to start.';
    box.appendChild(s);
    input.placeholder='https://yourwebsite.com';
    return;
  }
  // Initials badge: first letters of the first two words of the site's title.
  const words=(site.title||'').replace(/[^\p{L}\p{N}\s]/gu,' ').trim().split(/\s+/).filter(Boolean);
  const initials=(words.length>1 ? words[0][0]+words[1][0] : (words[0]||'?').slice(0,2)).toUpperCase();
  const fav=document.createElement('div');fav.className='site-fav';fav.textContent=initials;
  const meta=document.createElement('div');meta.className='site-meta';
  const t=document.createElement('div');t.className='site-title';t.textContent=site.title;
  const u=document.createElement('a');u.className='site-url';u.href=site.url;u.target='_blank';u.rel='noopener noreferrer';
  u.textContent=site.url.replace(/^https?:\/\/(www\.)?/i,'').replace(/\/$/,'');
  meta.appendChild(t);meta.appendChild(u);
  box.appendChild(fav);box.appendChild(meta);
  if(chatReady) input.placeholder='Ask Mira anything about your website...';
}

// Called by the shared script after an "Analyse my website" report renders.
function onAnalysisReport(report){
  let title=report.title||'';
  if(!title){ try{ title=new URL(report.url).hostname.replace(/^www\./,''); }catch(e){ title=report.url; } }
  setSite({title:title, url:report.url}, false);
}

/* ============ Analyse tab: compare with a competitor ============
   Same feature as the homepage, unlocked: route('chat.compare') with
   premium:true returns the full gap report with every fix, drawn by
   miraRenderComparison() (partials.ask-mira.compare-script). Visitors who
   aren't logged in give their name and email first (shared lead form, which
   hands 'compare' submissions to onLeadCompare). */
let premCompareMode=false, premPendingCompetitor=null;

function premAnalyze(){
  if(premCompareMode) premSendCompare(); else sendAnalyzeUrl();
}

function premToggleCompare(forceOff){
  premCompareMode = forceOff===true ? false : !premCompareMode;
  document.getElementById('premCompareRow').hidden=!premCompareMode;
  const toggle=document.getElementById('premCompareToggle');
  document.getElementById('premCompareLabel').textContent = premCompareMode ? 'Back' : 'Compare with a competitor';
  toggle.classList.toggle('on', premCompareMode);
  toggle.setAttribute('aria-pressed', premCompareMode ? 'true' : 'false');
  document.getElementById('panelAnalyze').classList.toggle('comparing', premCompareMode);
  document.getElementById('analyzeHint').textContent = premCompareMode
    ? "Enter your website and a competitor's — I'll compare them and show you exactly what to fix to beat them."
    : "Paste your website URL — I'll run a real SEO, Technical and Speed check and show the full report below.";
  document.getElementById('analyzeInput').placeholder = premCompareMode ? 'Your website, e.g. https://yourwebsite.com' : 'https://yourwebsite.com';
  const status=document.getElementById('analyzeStatus');
  status.textContent=''; status.classList.remove('err');
  if(premCompareMode){
    document.getElementById('premCompetitorInput').focus();
  }else{
    document.getElementById('premCompetitorInput').value='';
    // Clear a comparison, but keep a single-site report.
    const panel=document.getElementById('miraComparePanel');
    if(!panel.classList.contains('cx-single')){ panel.hidden=true; panel.innerHTML=''; }
  }
}

function premSendCompare(){
  const url=document.getElementById('analyzeInput').value.trim();
  const competitor=document.getElementById('premCompetitorInput').value.trim();
  const status=document.getElementById('analyzeStatus');
  status.classList.remove('err');
  const fail=function(msg){ status.textContent=msg; status.classList.add('err'); };
  if(!url) return fail('Enter your website URL first.');
  if(!competitor) return fail("Enter your competitor's website URL.");
  if(!/^https?:\/\//i.test(url) || !/^https?:\/\//i.test(competitor)) return fail('Include http:// or https:// at the start of both addresses.');
  if(url.replace(/\/+$/,'').toLowerCase()===competitor.replace(/\/+$/,'').toLowerCase()) return fail("Your competitor's website must be different from yours.");
  if(!IS_LOGGED_IN){ premPendingCompetitor=competitor; openLeadModal(url, 'compare'); return; }
  premRunCompare(url, competitor, null, null);
}

// Called by the shared lead form after a visitor gives their name and email.
function onLeadCompare(url, name, email){
  premRunCompare(url, premPendingCompetitor, name, email);
  premPendingCompetitor=null;
}

async function premRunCompare(url, competitor, name, email){
  const status=document.getElementById('analyzeStatus');
  status.classList.remove('err');
  status.textContent='Comparing both websites — this can take up to a minute…';
  try{
    const res=await postJson('{{ route("chat.compare") }}', {url:url, competitor_url:competitor, name:name, email:email, premium:true});
    const data=await res.json();
    if(res.ok){
      status.textContent=data.reply;
      miraRenderComparison(data.comparison);
    }else{
      status.textContent=data.error||data.message||'Something went wrong. Please try again.';
      status.classList.add('err');
    }
  }catch(e){
    status.textContent='Could not reach the server. Check your connection and try again.';
    status.classList.add('err');
  }
}

// Shared compare-script hook: show the visitor's own site in the sidebar card.
function onComparisonReport(c){
  setSite({title:c.you.name, url:c.you.url}, false);
}

async function sendPremium(){
  const input=document.getElementById('chatInput'),msg=input.value.trim();
  if(!msg||premiumBusy) return;
  premiumBusy=true;
  input.value='';
  // The welcome orb makes way for the conversation; older unused suggestion
  // buttons no longer fit it.
  document.getElementById('premEmpty')?.remove();
  document.querySelectorAll('#chatMsgs .chat-suggest').forEach(function(row){ row.remove(); });
  addMsg('user', msg);
  const typing=addMsg('bot typing', /^(https?:\/\/|www\.)/i.test(msg)||/^[a-z0-9-]+(\.[a-z0-9-]+)+(\/\S*)?$/i.test(msg) ? 'Reading your website...' : 'Thinking...');
  try{
    const res=await postJson('{{ route("mira-premium.send") }}',{message:msg});
    const data=await res.json();
    typing.remove();
    if(res.ok){
      addMsg('bot', data.reply);
      if(data.site) setSite(data.site);
      if(data.suggestions && data.suggestions.length) addSuggestions(data.suggestions);
    }else{
      addMsg('bot', data.error||data.message||'Something went wrong. Please try again.');
    }
  }catch(e){
    typing.remove();
    addMsg('bot', 'Could not reach the server. Check your connection and try again.');
  }
  premiumBusy=false;
}

function askPremium(q){
  document.getElementById('chatInput').value=q;
  sendPremium();
}

// Clickable question chips under a bot reply. The row is removed once one
// is picked, so it can't be sent twice.
function addSuggestions(questions){
  const msgs=document.getElementById('chatMsgs');
  const row=document.createElement('div');
  row.className='chat-suggest';
  questions.forEach(function(q){
    const btn=document.createElement('button');
    btn.type='button';
    btn.className='suggest-chip';
    btn.textContent=q;
    btn.addEventListener('click',function(){
      if(premiumBusy) return;
      row.remove();
      askPremium(q);
    });
    row.appendChild(btn);
  });
  msgs.appendChild(row);
  msgs.scrollTop=msgs.scrollHeight;
}

async function resetPremium(){
  await postJson('{{ route("mira-premium.reset") }}');
  document.getElementById('chatMsgs').innerHTML=PREMIUM_GREETING;
  setSite(null);
  // Also clear the "Analyse my website" tab, like the landing page's resetChat().
  document.getElementById('analysisPanel').classList.remove('show');
  document.getElementById('analyzeInput').value='';
  document.getElementById('analyzeStatus').textContent='';
  document.getElementById('analyzeStatus').classList.remove('err');
  // ...and the website report or competitor comparison.
  const reportPanel=document.getElementById('miraComparePanel');
  reportPanel.hidden=true; reportPanel.innerHTML=''; reportPanel.classList.remove('cx-single');
  premToggleCompare(true);
  switchChatTab('ask');
}
</script>
</body>
</html>
