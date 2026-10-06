<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>X Platforms &ndash; AI Customer Intelligence</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Instrument+Serif:ital@1&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
<style>
/*
  Matches the Figma "xplatorm Homepage" frame section-for-section (text,
  layout, colors, spacing). Real image assets (public/images/homepage/)
  were cropped directly from the client-supplied full-page export:
    1. Hero earth/space background photo -> public/images/homepage/hero-earth.png
    2. XPlatforms logo mark (colored X)   -> public/images/homepage/logo-mark-colored.png
    3. 6 "Connect sources" icons           -> public/images/homepage/sources/*.png
    4. 8-layer process icons (Ingest..Learn) -> public/images/homepage/layers/*.png
  Still pending real exports (using initials as a stand-in for now):
    5. 3 avatar photos ("Still have questions?") + "JS" profile photo
*/
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth;overflow-x:hidden}
body{
  background:#000;color:#fff;font-family:'Inter',system-ui,sans-serif;
  -webkit-font-smoothing:antialiased;
}
a{color:inherit;text-decoration:none}
img,svg{display:block}
.wrap{max-width:1200px;margin:0 auto;padding:0 60px}
@media (max-width:900px){.wrap{padding:0 24px}}

/* Accent italic serif — used for every colored/handwritten-style accent phrase
   ("signals everywhere.", "Intelligence engine.", "Revenue.", "Motion.", etc.) */
.accent{
  font-family:'Instrument Serif',Georgia,serif;font-style:italic;font-weight:400;
  background:linear-gradient(90deg,#F6F9FC,#93CEFF,#FFBFFF,#00EAF4,#F6F9FC);
  -webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;
}

/* ============ NAV ============ */
.nav{display:flex;align-items:center;justify-content:space-between;padding:22px 60px;position:relative;z-index:20}
@media (max-width:900px){.nav{padding:18px 24px}}
.brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:18px}
/* Real XPlatforms logo mark (colored X petals), extracted from Figma export */
.logo-mark{width:22px;height:22px;border-radius:5px}
.nav-links{display:flex;align-items:center;gap:36px;list-style:none}
.nav-links a{font-size:14px;color:#c7c9d1;transition:color .2s}
.nav-links a:hover{color:#fff}
.nav-right{display:flex;align-items:center;gap:14px}
.nav-burger{display:none;width:38px;height:38px;border-radius:8px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.04);align-items:center;justify-content:center;flex-shrink:0}
.nav-burger span,.nav-burger span::before,.nav-burger span::after{content:'';display:block;width:16px;height:2px;background:#fff;border-radius:2px;position:relative;transition:transform .2s,opacity .2s}
.nav-burger span::before{position:absolute;top:-5px}
.nav-burger span::after{position:absolute;top:5px}
.nav-burger.open span{background:transparent}
.nav-burger.open span::before{transform:translateY(5px) rotate(45deg)}
.nav-burger.open span::after{transform:translateY(-5px) rotate(-45deg)}
@media (max-width:900px){
  .nav-links{position:absolute;top:100%;left:0;right:0;flex-direction:column;align-items:flex-start;gap:0;background:#0a0a0d;border-bottom:1px solid rgba(255,255,255,.08);max-height:0;overflow:hidden;transition:max-height .25s ease}
  .nav-links.open{max-height:320px}
  .nav-links li{width:100%}
  .nav-links a{display:block;width:100%;padding:16px 24px;border-bottom:1px solid rgba(255,255,255,.05)}
  .nav-burger{display:flex}
}
.book-demo{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border:1px solid rgba(255,255,255,.18);border-radius:999px;font-size:13.5px;font-weight:500;color:#fff;background:rgba(255,255,255,.04);transition:background .2s;white-space:nowrap}
.book-demo:hover{background:rgba(255,255,255,.1)}
@media (max-width:480px){.book-demo span.label{display:none}}

/* ============ HERO ============ */
.hero{position:relative;padding:70px 60px 110px;text-align:center;overflow:hidden;min-height:640px}
@media (max-width:900px){.hero{padding:50px 24px 80px}}
/* Real earth/space photo, extracted from Figma export */
.hero-bg{position:absolute;inset:0;z-index:0;overflow:hidden;background:#000}
.hero-bg img{width:100%;height:100%;object-fit:cover;object-position:center 30%;
  -webkit-mask-image:linear-gradient(to bottom,black 60%,transparent 100%);
  mask-image:linear-gradient(to bottom,black 60%,transparent 100%);
}
.hero-inner{position:relative;z-index:1}
.hero h1{font-family:'Space Grotesk',sans-serif;font-size:160px;line-height:.86;font-weight:500;letter-spacing:-3.2px}
.hero h1 .accent{font-size:1em;display:block;font-weight:400;letter-spacing:-.022em}
@media (max-width:1200px){.hero h1{font-size:110px}}
@media (max-width:900px){.hero h1{font-size:64px}}
@media (max-width:480px){.hero h1{font-size:42px}}
.hero-sub{margin-top:22px;font-size:26px;color:#9aa0ac;line-height:1.3}
.hero-sub strong{display:block;color:#e7e9ee;font-weight:500}
@media (max-width:900px){.hero-sub{font-size:17px}}
@media (max-width:480px){.hero-sub{font-size:15px}}

/* ============ ASK MIRA — chatbot widget (precise rebuild of the Figma
   "Ask Mira" card: light-themed floating panel, independent of the dark
   site theme around it). Wired to the same live backend as the classic
   homepage widget: route('chat.send') / route('chat.reset') —
   see App\Http\Controllers\PublicChatController. ============ */
.chatbot-section{padding-top:0;padding-bottom:40px}
/* Try Premium Mira — under New Chat, links to /mira-premium. */
.mira-premium-btn{
  display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:12px 14px;border-radius:999px;
  background:linear-gradient(135deg,#f0d27a,#d4af37 55%,#b8902f);color:#1a1406;font-family:'Inter',sans-serif;font-size:14px;font-weight:600;
  text-decoration:none;box-shadow:0 4px 14px rgba(212,175,55,.35);transition:transform .15s,box-shadow .2s;margin-top:-14px}
.mira-premium-btn:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(212,175,55,.45)}

.mira-widget{
  display:flex;background:#fff;border-radius:28px;overflow:hidden;
  box-shadow:0 30px 80px -20px rgba(0,0,0,.55);min-height:560px;
}
@media (max-width:760px){.mira-widget{flex-direction:column;border-radius:20px}}

/* ---- Sidebar ---- */
.mira-sidebar{width:260px;flex-shrink:0;background:#f3f3f3;padding:22px 18px;display:flex;flex-direction:column;gap:26px}
@media (max-width:760px){.mira-sidebar{width:auto;flex-direction:row;flex-wrap:wrap;align-items:flex-start;gap:14px;padding:16px}}
.mira-newchat{
  display:flex;align-items:center;justify-content:center;gap:8px;width:100%;
  background:#fff;border:none;border-radius:999px;padding:12px 16px;
  font-family:'Inter',sans-serif;font-size:14px;font-weight:600;color:#17181a;
  cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,.06);transition:box-shadow .15s;
}
.mira-newchat:hover{box-shadow:0 2px 8px rgba(0,0,0,.1)}
.mira-newchat svg{width:15px;height:15px;stroke:#17181a;fill:none;stroke-width:2}
.mira-group{display:flex;flex-direction:column;gap:10px}
.mira-label{font-size:13px;color:#8a8a8e;font-weight:500}
.mira-select-wrap{position:relative}
.mira-select{
  appearance:none;-webkit-appearance:none;width:100%;background:#fff;border:none;
  border-radius:10px;padding:11px 36px 11px 14px;font-family:'Inter',sans-serif;
  font-size:13.5px;font-weight:500;color:#17181a;cursor:pointer;
  box-shadow:0 1px 2px rgba(0,0,0,.05);
}
.mira-select-wrap svg{position:absolute;right:12px;top:50%;transform:translateY(-50%);width:13px;height:13px;stroke:#8a8a8e;fill:none;stroke-width:2;pointer-events:none}
.mira-starters{display:flex;flex-direction:column;gap:8px}
.mira-starter{
  text-align:left;background:#fff;border:none;border-radius:10px;padding:11px 14px;
  font-family:'Inter',sans-serif;font-size:13px;color:#2c2d30;line-height:1.4;
  cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,.05);transition:box-shadow .15s;
}
.mira-starter:hover{box-shadow:0 2px 8px rgba(0,0,0,.1)}

/* ---- Main panel ---- */
.mira-main{flex:1;background:#f2f2f3;display:flex;flex-direction:column;min-width:0}
.mira-tabs{display:flex;justify-content:flex-end;padding:18px 22px 0}
.mira-tabswitch{display:flex;align-items:center;gap:4px;background:#fff;border-radius:999px;padding:5px;box-shadow:0 1px 2px rgba(0,0,0,.05)}
.mira-tab{
  border:none;background:none;cursor:pointer;font-family:'Inter',sans-serif;
  font-size:13px;font-weight:600;color:#6b6b70;padding:9px 16px;border-radius:999px;
  transition:background .15s,color .15s;
}
.mira-tab.active{background:#5383EC;color:#fff}

.mira-panel{flex:1;display:flex;flex-direction:column;min-height:0}
.mira-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px 24px 10px}
.mira-orb{width:118px;height:118px;margin-bottom:18px;filter:drop-shadow(0 10px 30px rgba(83,131,236,.35));
  /* Three layered loops on separate properties so they don't override each
     other: a float (translate), a slow spin (rotate) and a breathing glow (filter). */
  animation:miraOrbFloat 6s ease-in-out infinite,miraOrbSpin 24s linear infinite,miraOrbGlow 4s ease-in-out infinite}
@keyframes miraOrbFloat{0%,100%{translate:0 0}50%{translate:0 -10px}}
@keyframes miraOrbSpin{to{rotate:360deg}}
@keyframes miraOrbGlow{
  0%,100%{filter:drop-shadow(0 10px 30px rgba(83,131,236,.35))}
  50%{filter:drop-shadow(0 14px 44px rgba(83,131,236,.6)) drop-shadow(0 0 22px rgba(196,132,252,.35))}}
/* Orb surroundings (all behind/around the image, purely decorative):
   ::before  soft aura that pulses
   ::after   thin gradient light ring sweeping around
   .mira-orb-ripple  rings expanding outward and fading (two, offset in time)
   .mira-orb-orbit   tiny glowing particles circling the orb (two, opposite directions) */
.mira-orb-wrap{position:relative;width:118px;height:118px;margin-bottom:22px;display:grid;place-items:center;isolation:isolate}
.mira-orb-wrap .mira-orb{position:relative;z-index:2;margin:0}
.mira-orb-wrap::before{content:"";position:absolute;inset:-42px;border-radius:50%;z-index:0;
  background:radial-gradient(circle,rgba(83,131,236,.30) 0%,rgba(196,132,252,.14) 42%,transparent 70%);
  animation:miraAura 4s ease-in-out infinite}
.mira-orb-wrap::after{content:"";position:absolute;inset:-9px;border-radius:50%;z-index:1;
  background:conic-gradient(from 0deg,transparent 0deg,rgba(83,131,236,.75) 70deg,rgba(196,132,252,.7) 130deg,transparent 200deg,transparent 360deg);
  -webkit-mask:radial-gradient(circle,transparent calc(50% - 2px),#000 calc(50% - 1px));
          mask:radial-gradient(circle,transparent calc(50% - 2px),#000 calc(50% - 1px));
  animation:miraOrbSpin 5s linear infinite}
.mira-orb-ripple{position:absolute;inset:-4px;border-radius:50%;z-index:0;border:1.5px solid rgba(83,131,236,.35);
  animation:miraRipple 3.6s ease-out infinite}
.mira-orb-ripple.r2{animation-delay:1.8s}
.mira-orb-orbit{position:absolute;inset:-22px;z-index:3;animation:miraOrbSpin 7s linear infinite}
.mira-orb-orbit::before{content:"";position:absolute;top:0;left:50%;width:7px;height:7px;margin-left:-3.5px;border-radius:50%;
  background:#fff;box-shadow:0 0 8px 2px rgba(83,131,236,.9),0 0 16px 4px rgba(196,132,252,.5)}
.mira-orb-orbit.o2{inset:-32px;animation-duration:11s;animation-direction:reverse}
.mira-orb-orbit.o2::before{width:5px;height:5px;margin-left:-2.5px;box-shadow:0 0 6px 2px rgba(196,132,252,.9)}
@keyframes miraAura{0%,100%{transform:scale(.92);opacity:.75}50%{transform:scale(1.08);opacity:1}}
@keyframes miraRipple{0%{transform:scale(.9);opacity:.7}100%{transform:scale(1.75);opacity:0}}
@media (max-width:640px){.mira-orb-wrap{width:90px;height:90px}}
@media (prefers-reduced-motion:reduce){
  .mira-orb,.mira-orb-wrap::before,.mira-orb-wrap::after,.mira-orb-orbit{animation:none}
  .mira-orb-ripple{display:none}}
.mira-empty h3{font-family:'Inter',sans-serif;font-size:23px;font-weight:700;color:#121214;letter-spacing:-.2px}
.mira-empty h3 .mira-accent{color:#5383EC}
.mira-empty p{margin-top:8px;font-family:'Inter',sans-serif;font-size:14px;color:#8f8f94;max-width:360px;line-height:1.5}

.mira-messages{flex:1;overflow-y:auto;padding:22px 28px 6px;display:flex;flex-direction:column;gap:14px}
.mira-msg{max-width:72%;padding:12px 16px;border-radius:16px;font-family:'Inter',sans-serif;font-size:14px;line-height:1.55}
.mira-msg.user{align-self:flex-end;background:#5383EC;color:#fff;border-bottom-right-radius:4px}
.mira-msg.bot{align-self:flex-start;background:#fff;color:#1c1c1f;border-bottom-left-radius:4px;box-shadow:0 1px 2px rgba(0,0,0,.05)}
.mira-msg.bot.typing{color:#9a9a9e;font-style:italic}
.mira-msg a{color:#5383EC;text-decoration:underline}

.mira-input-row{display:flex;align-items:flex-end;gap:10px;background:#fff;border-radius:18px;margin:16px 24px 20px;padding:16px 16px 16px 20px;box-shadow:0 1px 2px rgba(0,0,0,.05)}
.mira-input{flex:1;border:none;outline:none;resize:none;font-family:'Inter',sans-serif;font-size:14px;color:#17181a;background:transparent;max-height:120px;line-height:1.5}
.mira-input::placeholder{color:#9a9a9e}
.mira-send{flex-shrink:0;width:38px;height:38px;border-radius:50%;background:#5383EC;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:opacity .15s}
.mira-send:hover{opacity:.9}
.mira-send svg{width:16px;height:16px;fill:#fff}
.mira-analyze-hint{font-family:'Inter',sans-serif;font-size:13px;color:#8f8f94;text-align:center;margin:0 24px 8px}
.mira-analyze-status{font-family:'Inter',sans-serif;font-size:13px;color:#6b6b70;text-align:center;margin:0 24px 12px;min-height:16px}
.mira-analyze-status.err{color:#e0564c}

.mira-sources{padding:4px 24px 26px;text-align:center}
.mira-sources-label{font-family:'Inter',sans-serif;font-size:14px;font-weight:600;color:#17181a;margin-bottom:14px}
.mira-sources-row{display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap}
.src-tile{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.src-tile svg{width:22px;height:22px}
.src-tile.t-stripe{background:#eef8ff}
.src-tile.t-shopify{background:#fffaef}
.src-tile.t-globe{background:#fff3ee}
.src-tile.t-hubspot{background:#eafbf9}
.src-tile.t-mail{background:#fef3fd}
.src-tile.t-bank{background:#edf5ff}

@media (max-width:480px){
  .mira-empty h3{font-size:19px}
  .mira-orb{width:90px;height:90px}
  .mira-input-row{margin:12px 14px 16px}
  .mira-sources{padding:4px 14px 20px}
}

/* ============ SECTION HEADINGS (shared, exact Figma spec: Space Grotesk 500 / 68px,
   Instrument Serif italic accent, both same size, tight 92.4px line-height) ============ */
.sec{padding:100px 60px}
@media (max-width:900px){.sec{padding:60px 24px}}
.sec-hd h2{font-family:'Space Grotesk',sans-serif;font-size:68px;font-weight:500;line-height:.94;letter-spacing:-3.52px;color:#F6F9FC}
.sec-hd .accent{font-size:1em;display:block;letter-spacing:-.052em}
.sec-hd p{margin-top:18px;max-width:560px;color:#9aa0ac;font-size:15.5px;line-height:1.65}
@media (max-width:1200px){.sec-hd h2{font-size:52px}}
@media (max-width:900px){.sec-hd h2{font-size:38px;letter-spacing:-1.5px}}

/* ============ SIGNALS DIAGRAM ============
   NOTE: .wrap caps content at 1200px (minus 60px*2 padding = 1080px usable),
   on every desktop viewport no matter how wide — so the diagram must always
   fit in ~1080px to stay on one line, never just at "large" viewports. The
   compact sizing below is therefore the default (not a shrink-on-mobile
   override); only the ≤900px tier switches to a stacked layout, and ≤480px
   shrinks further for small phones. */
.signals-flow{display:flex;align-items:center;gap:32px;margin-top:70px;flex-wrap:wrap}
.signals-diagram{position:relative;width:380px;height:418px;flex-shrink:0}
.signals-diagram svg{position:absolute;inset:0;width:100%;height:100%;overflow:visible}
.signal-pills{display:flex;flex-direction:column;gap:12px;width:208px;position:relative;z-index:2}
.signal-pill{position:relative;height:59px;box-sizing:border-box;display:flex;align-items:center;
  padding:10px 18px;border-radius:16px;background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.08);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px)}
.signal-pill span{
  font-family:'Space Grotesk',sans-serif;font-weight:500;font-size:32px;line-height:34px;
  letter-spacing:-1.2px;display:inline-block;background-clip:text;-webkit-background-clip:text;
  color:transparent;-webkit-text-fill-color:transparent;
}
.signal-pill:not(.c-sales) span{background-image:linear-gradient(90deg,#FFD0BF,#FF8A3D)}
.signal-pill.c-sales span{background-image:linear-gradient(90deg,#D6ECFF,#4CA0F6)}
.signals-node{display:flex;flex-direction:column;align-items:center;gap:9px;flex-shrink:0;margin-left:-10px}
.signals-circle{width:84px;height:84px;border-radius:50%;background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.08);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
  box-shadow:0 0 36px rgba(110,130,200,.1);display:flex;align-items:center;justify-content:center}
.signals-circle img{width:46px;height:46px}
.signals-node-label{font-family:'Inter',sans-serif;font-size:15px;font-weight:500;color:#fff;line-height:1}
.signals-eq{font-family:'Space Grotesk',sans-serif;font-size:60px;font-weight:500;letter-spacing:-2.4px;
  color:rgba(255,255,255,.35);line-height:1;flex-shrink:0;margin:0 8px}
.signals-result{flex:1;min-width:280px}
.signals-result h3{font-family:'Space Grotesk',sans-serif;font-size:52px;font-weight:500;
  line-height:1.04;letter-spacing:-2.1px;color:#F6F9FC}
.signals-result .accent{
  font-family:'Instrument Serif',Georgia,serif;font-style:italic;font-weight:400;
  font-size:52px;letter-spacing:-1.1px;line-height:1.04;
  background:linear-gradient(90deg,#F6F9FC,#93CEFF,#FFBFFF,#00EAF4,#F6F9FC);
  -webkit-background-clip:text;background-clip:text;color:transparent;display:inline-block;
}
@media (max-width:1100px){
  .signals-flow{gap:18px}
  .signals-diagram{width:300px;height:330px}
  .signal-pills{width:164px;gap:9px}
  .signal-pill{height:47px;padding:8px 14px;border-radius:12px}
  .signal-pill span{font-size:25px;line-height:27px;letter-spacing:-1px}
  .signals-circle{width:66px;height:66px}
  .signals-circle img{width:36px;height:36px}
  .signals-node-label{font-size:13px}
  .signals-eq{font-size:44px;margin:0 2px}
  .signals-result{min-width:190px}
  .signals-result h3,.signals-result .accent{font-size:36px;line-height:1.08}
}
@media (max-width:900px){
  .signals-flow{flex-direction:column;align-items:flex-start;gap:28px}
  .signals-diagram{width:100%;max-width:380px}
  .signals-eq{display:none}
  .signals-result h3,.signals-result .accent{font-size:42px;line-height:1.1}
}
@media (max-width:480px){
  .signals-diagram{height:370px}
  .signal-pills{width:180px;gap:10px}
  .signal-pill{height:50px;padding:8px 14px}
  .signal-pill span{font-size:24px;line-height:26px;letter-spacing:-.8px}
  .signals-circle{width:68px;height:68px}
  .signals-circle img{width:36px;height:36px}
}

/* ============ STAT CARDS (3-up) ============ */
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:20px}
@media (max-width:900px){.stat-row{grid-template-columns:1fr}}
.stat-card{position:relative;padding:24px 26px;border-radius:14px;border:1px solid rgba(255,255,255,.07);overflow:hidden}
.stat-card .tag{position:absolute;top:16px;right:18px;font-size:10px;letter-spacing:.06em;color:rgba(255,255,255,.35);display:flex;align-items:center;gap:5px}
.stat-card .tag::before{content:'';width:5px;height:5px;border-radius:50%;background:currentColor}
.stat-card .num{font-family:'Space Grotesk',sans-serif;font-size:42px;font-weight:500}
.stat-card .lbl{margin-top:6px;font-size:14px;color:#aeb2bc}
.stat-card.blue{background:linear-gradient(135deg,#0c1d2c,#05070c)}
.stat-card.blue .num{color:#7dd3fc}
.stat-card.gold{background:linear-gradient(135deg,#241d09,#05070c)}
.stat-card.gold .num{color:#f2c14e}
.stat-card.orange{background:linear-gradient(135deg,#2a1509,#05070c)}
.stat-card.orange .num{color:#f2905f}

/* ============ 8-LAYER ROW ============ */
.layer-row{display:flex;align-items:flex-start;gap:0;margin-top:60px;overflow-x:auto;-webkit-overflow-scrolling:touch;padding-bottom:8px}
.layer-item{display:flex;flex-direction:column;align-items:center;text-align:center;width:165px;flex-shrink:0}
.layer-icon{width:60px;height:60px;border-radius:14px;overflow:hidden;margin-bottom:16px}
.layer-icon img{width:100%;height:100%;object-fit:cover}
.layer-item h4{font-family:'Space Grotesk',sans-serif;font-size:20px;font-weight:500}
.layer-item p{margin-top:6px;font-size:13px;color:#8a8f99;line-height:1.5}
.layer-arrow{flex-shrink:0;width:32px;padding-top:16px;color:#4b4f58;display:flex;justify-content:center}
@media (max-width:900px){.layer-arrow{display:none}}

/* ============ 2x2 FEATURE GRID (Raw data -> Revenue) ============
   Precisely rebuilt from the Figma reference: each card has a soft
   colored radial glow in its top-right corner (matching its own accent),
   large saturated icon tiles, a real arrow-diagram (not just labels),
   cascading overlapped prediction rows with photo avatars, and a smooth
   dotted revenue curve with area fill. ============ */
.feature-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:60px}
@media (max-width:900px){.feature-grid{grid-template-columns:1fr}}
.feature-card{padding:32px;border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#0a0a0a;position:relative;overflow:hidden}
.feature-card::before{content:'';position:absolute;top:0;right:0;width:70%;height:55%;pointer-events:none;border-radius:0 16px 0 0}
.feature-card.f1::before{background:radial-gradient(ellipse at top right,rgba(125,211,252,.16),transparent 70%)}
.feature-card.f2::before{background:radial-gradient(ellipse at top right,rgba(242,193,78,.16),transparent 70%)}
.feature-card.f3::before{background:radial-gradient(ellipse at top right,rgba(247,107,107,.14),transparent 70%)}
.feature-card.f4::before{background:radial-gradient(ellipse at top right,rgba(147,197,253,.14),transparent 70%)}
.feature-card>*{position:relative;z-index:1}
.feature-card .fno{font-family:'Space Grotesk',sans-serif;font-size:11px;font-weight:700;letter-spacing:.05em}
.feature-card.f1 .fno{color:#7dd3fc}
.feature-card.f2 .fno{color:#f2c14e}
.feature-card.f3 .fno{color:#f76b6b}
.feature-card.f4 .fno{color:#93c5fd}
.feature-card h3{margin-top:10px;font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:500;color:#f6f9fc}
.feature-card p.desc{margin-top:8px;color:#9aa0ac;font-size:13.5px;line-height:1.6;max-width:360px}

/* Connect sources icon row — large saturated icon on a dark tile tinted
   to match the icon's own hue (sampled directly from the Figma export) */
.src-icons{display:flex;gap:10px;margin-top:28px;flex-wrap:wrap}
.src-tile2{width:56px;height:56px;border-radius:14px;overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.src-tile2 svg{width:26px;height:26px}
.src-tile2.t-stripe{background:linear-gradient(160deg,#17222c,#0c1218)}
.src-tile2.t-shopify{background:linear-gradient(160deg,#241e0d,#14110a)}
.src-tile2.t-globe{background:linear-gradient(160deg,#2a160f,#160f0b)}
.src-tile2.t-hubspot{background:linear-gradient(160deg,#0e2622,#0a1613)}
.src-tile2.t-mail{background:linear-gradient(160deg,#241a28,#140f16)}
.src-tile2.t-bank{background:linear-gradient(160deg,#10202e,#0a141c)}
.connect-hint{margin-top:22px;font-size:12.5px;color:#8a8f99;display:flex;align-items:center;gap:8px}
.connect-hint .plus{width:18px;height:18px;border-radius:50%;border:1px solid #555;display:flex;align-items:center;justify-content:center;font-size:11px}

/* AI analyses diagram — center X-mark node with 4 colored gradient
   arrows fanning out to labeled pills, matching the Figma composition */
.analyse-diagram{position:relative;height:230px;margin-top:18px}
.analyse-diagram svg{position:absolute;inset:0;width:100%;height:100%;overflow:visible}
.analyse-center{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:70px;height:70px;border-radius:50%;
  background:radial-gradient(circle,#1a1c22,#0a0a0d 72%);box-shadow:0 0 30px rgba(110,130,200,.18);
  display:flex;align-items:center;justify-content:center;z-index:2}
.analyse-center img{width:30px;height:30px}
.analyse-node{position:absolute;padding:9px 16px;border-radius:8px;background:#0d0d11;border:1px solid rgba(255,255,255,.08);
  font-family:'Space Grotesk',sans-serif;font-weight:500;font-size:16px;z-index:2}
.analyse-node.behaviour{top:8px;left:0;color:#6ee7b7}
.analyse-node.patterns{top:8px;right:0;color:#93c5fd}
.analyse-node.intent{bottom:8px;left:0;color:#f2c14e}
.analyse-node.context{bottom:8px;right:0;color:#f76b6b}
.analyse-foot{margin-top:10px;font-size:12.5px;color:#8a8f99}

/* Get predictions — cascading stack of overlapping cards (back = narrowest
   + highest, front = full-width + lowest), each tinted to its own signal
   color, with circular avatar photos and Google-palette percentages */
.pred-stack{position:relative;height:250px;margin-top:26px}
.pred-row{position:absolute;left:0;right:0;display:flex;align-items:center;gap:14px;padding:16px 18px;border-radius:12px;
  border:1px solid rgba(255,255,255,.07);box-shadow:0 12px 24px -12px rgba(0,0,0,.6)}
.pred-row.risk{top:0;right:8%;background:linear-gradient(100deg,#2a0f0d 0%,#120a0a 55%);z-index:1}
.pred-row.intent{top:76px;right:4%;background:linear-gradient(100deg,#0c2415 0%,#0a1210 55%);z-index:2}
.pred-row.engage{top:152px;right:0;background:linear-gradient(100deg,#0d1b2e 0%,#0a0e14 55%);z-index:3}
.pred-row .avatar{width:40px;height:40px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;overflow:hidden}
.pred-row .avatar svg{width:100%;height:100%}
.pred-row .body{flex:1;min-width:0}
.pred-row .body h5{font-size:14px;font-weight:700;color:#f0f1f3}
.pred-row .body p{font-size:11.5px;color:#9aa0ac;margin-top:2px;line-height:1.4}
.pred-row .pct{font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:500;flex-shrink:0}
.pred-row.risk .pct{color:#ea4335}
.pred-row.intent .pct{color:#34a853}
.pred-row.engage .pct{color:#4285f4}

/* Grow revenue chart card */
.rev-card{background:linear-gradient(160deg,#0f1922,#070b10);border-radius:14px;padding:22px;border:1px solid rgba(255,255,255,.07)}
.rev-top{display:flex;align-items:center;justify-content:space-between;font-size:11.5px;color:#8a8f99}
.rev-select{padding:5px 12px;border-radius:999px;background:#171b24;font-size:11px}
.rev-num{font-family:'Space Grotesk',sans-serif;font-size:46px;font-weight:500;margin-top:6px}
.rev-num .rev-plus{color:#f6f9fc}
.rev-num .rev-pct{background:linear-gradient(90deg,#b9e3ff,#7dd3fc);-webkit-background-clip:text;background-clip:text;color:transparent}
.rev-chart{height:96px;margin-top:10px;width:100%}
.rev-mini{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:18px}
.rev-mini div{background:rgba(255,255,255,.04);border-radius:10px;padding:12px}
.rev-mini .v{font-family:'Space Grotesk',sans-serif;font-size:17px;font-weight:500;color:#f0f1f3}
.rev-mini .l{font-size:10.5px;color:#8a8f99;margin-top:3px}

/* ============ CONSOLE MONITOR ============ */
.console{margin-top:60px;border-radius:14px;overflow:hidden;border:1px solid rgba(255,255,255,.08);display:grid;grid-template-columns:1fr 300px;background:#07080b}
@media (max-width:900px){.console{grid-template-columns:1fr}}
.console-main{padding:0}
.console-hd{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.06);font-family:ui-monospace,monospace;font-size:12px;color:#8a8f99}
.console-dots{display:flex;gap:6px}
.console-dots span{width:9px;height:9px;border-radius:50%}
.console-dots span:nth-child(1){background:#f76b6b}
.console-dots span:nth-child(2){background:#f2c14e}
.console-dots span:nth-child(3){background:#6ee7b7}
.console-live{margin-left:auto;color:#6ee7b7;display:flex;align-items:center;gap:5px}
.console-live::before{content:'';width:6px;height:6px;border-radius:50%;background:#6ee7b7}
.console-log{padding:14px 18px;font-family:ui-monospace,monospace;font-size:11.5px;line-height:2;color:#7a7f89;max-height:270px;overflow-y:auto}
.console-log .t{color:#565a63;margin-right:10px}
.console-log .tagpill{display:inline-block;font-size:9.5px;font-weight:700;padding:1px 7px;border-radius:4px;margin-right:8px;min-width:52px;text-align:center}
.tagpill.MAP{background:#3d1a1a;color:#f76b6b}
.tagpill.DETECT{background:#3d2f0a;color:#f2c14e}
.tagpill.PREDICT{background:#1a2340;color:#93c5fd}
.tagpill.PLAN{background:#123322;color:#6ee7b7}
.tagpill.EXEC{background:#3d2f0a;color:#f2c14e}
.tagpill.LEARN{background:#1a2340;color:#93c5fd}
.tagpill.INGEST{background:#12203d;color:#7dd3fc}
.tagpill.UNIFY{background:#123322;color:#6ee7b7}
.console-side{border-left:1px solid rgba(255,255,255,.06);padding:18px}
.side-block{margin-bottom:16px}
.side-block .l{font-size:11px;color:#8a8f99}
.side-block .v{font-size:24px;font-weight:800;margin-top:4px}
.side-block .v small{font-size:12px;font-weight:400;color:#8a8f99}
.side-bar{height:4px;border-radius:2px;background:#1c2029;margin-top:8px;overflow:hidden}
.side-bar i{display:block;height:100%;border-radius:2px}
.side-block.confidence .v{color:#6ee7b7}
.side-block.confidence .side-bar i{background:#6ee7b7;width:94%}
.side-block.prediction .v{color:#f2c14e}
.side-block.prediction .side-bar i{background:#f2c14e;width:70%}
.layer-status{display:flex;align-items:center;justify-content:space-between;padding:6px 0;font-size:11.5px;color:#aeb2bc}
.layer-status .dot{width:6px;height:6px;border-radius:50%;margin-right:8px;display:inline-block}
.layer-status .state{font-size:9.5px;color:#565a63}
.layer-status .state.active{color:#6ee7b7}

/* ============ RAW DATA TABLE + PROFILE ============ */
.rawdata-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:20px;margin-top:30px}
@media (max-width:900px){.rawdata-grid{grid-template-columns:1fr}}
.toggle-row{display:flex;align-items:center;gap:12px;font-size:13px;color:#9aa0ac;margin-bottom:20px}
.toggle{width:38px;height:22px;border-radius:999px;background:#2563eb;position:relative;flex-shrink:0}
.toggle::after{content:'';position:absolute;top:2px;right:2px;width:18px;height:18px;border-radius:50%;background:#fff}
/* Horizontal scroll on narrow screens instead of squeezing/breaking columns */
.dtable-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:12px}
.dtable{width:100%;min-width:560px;border-collapse:collapse;font-size:12.5px;background:#0a0a0d;border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.06)}
.dtable th{text-align:left;padding:12px 16px;font-size:10.5px;color:#6a6e77;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid rgba(255,255,255,.06);white-space:nowrap}
.dtable td{padding:11px 16px;border-bottom:1px solid rgba(255,255,255,.04);color:#c7c9d1;white-space:nowrap}
.dtable td.src{color:#6ee7b7}
.dtable td.pt{color:#6ee7b7}
.dtable td.st{color:#6ee7b7}
.profile-card{background:#0a0a0d;border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:22px}
.profile-hd{display:flex;align-items:center;gap:12px}
.profile-hd .av{width:40px;height:40px;border-radius:50%;background:#2a2d35;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px}
.profile-hd h4{font-size:15px}
.profile-hd p{font-size:12px;color:#8a8f99}
.profile-hd .badge{margin-left:auto;font-size:10px;background:#123322;color:#6ee7b7;padding:4px 10px;border-radius:999px}
.profile-pct{font-size:52px;font-weight:800;color:#93c5fd;margin-top:16px}
.profile-lbl{font-size:15px;font-weight:600;margin-top:2px}
.profile-mini{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:20px}
.profile-mini div{background:#111318;border-radius:8px;padding:12px;text-align:center}
.profile-mini .v{font-size:15px;font-weight:700}
.profile-mini .l{font-size:10px;color:#8a8f99;margin-top:3px}

/* ============ INDUSTRY SELECTOR + STAT TILES ============ */
.industry-bar{display:flex;align-items:center;gap:10px;padding:18px 24px;border-radius:14px;background:#0a0a0d;border:1px solid rgba(255,255,255,.07);margin-top:40px;overflow-x:auto}
.industry-bar .sel-label{font-size:14px;font-weight:600;margin-right:8px;flex-shrink:0}
.industry-chip{padding:7px 16px;border-radius:999px;font-size:12.5px;color:#9aa0ac;background:#15161b;flex-shrink:0;white-space:nowrap}
.industry-chip.active{background:#1d2a44;color:#93c5fd}
.tile-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:20px}
@media (max-width:900px){.tile-row{grid-template-columns:1fr 1fr}}
.tile{padding:22px;border-radius:12px;background:#0a0a0d;border:1px solid rgba(255,255,255,.06)}
.tile .v{font-size:26px;font-weight:800}
.tile .l{font-size:11px;color:#8a8f99;margin-top:5px;text-transform:uppercase;letter-spacing:.04em}
.tile.c-blue .v{color:#93c5fd}
.tile.c-green .v{color:#6ee7b7}
.tile.c-gold .v{color:#f2c14e}
.tile.c-cyan .v{color:#7dd3fc}

/* Predictive intelligence banner card */
.predictive-card{margin-top:24px;padding:34px;border-radius:16px;background:linear-gradient(135deg,#0d1420,#05070c);border:1px solid rgba(255,255,255,.07)}
.predictive-card h3{font-size:26px;font-weight:700}
.predictive-card p{margin-top:8px;color:#9aa0ac;font-size:14px;max-width:640px;line-height:1.6}
.predictive-tiles{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:26px}
@media (max-width:900px){.predictive-tiles{grid-template-columns:1fr}}
.predictive-tiles div{background:#0f1420;border-radius:10px;padding:18px 20px}
.predictive-tiles .v{font-size:30px;font-weight:800;color:#93c5fd}
.predictive-tiles .l{font-size:10.5px;color:#8a8f99;text-transform:uppercase;letter-spacing:.04em;margin-top:4px}

/* ============ PRICING ============ */
.pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:50px}
@media (max-width:900px){.pricing-grid{grid-template-columns:1fr}}
.price-card{padding:32px 28px;border-radius:16px;background:#0a0a0d;border:1px solid rgba(255,255,255,.07)}
.price-card.featured{background:linear-gradient(180deg,#0d1a30,#05070c);border-color:rgba(147,197,253,.3);transform:translateY(-10px)}
@media (max-width:900px){.price-card.featured{transform:none}}
.price-card h3{font-family:'Space Grotesk',sans-serif;font-size:26px;font-weight:500}
.price-card .plan-desc{margin-top:8px;font-size:13px;color:#9aa0ac;line-height:1.55;min-height:40px}
.price-card .price{font-family:'Space Grotesk',sans-serif;font-size:42px;font-weight:500;margin-top:20px}
.price-card .price small{font-size:12px;font-weight:400;color:#8a8f99}
.price-btn{display:block;text-align:center;margin-top:18px;padding:12px;border-radius:10px;font-size:13.5px;font-weight:600;background:#171b24;color:#fff;border:1px solid rgba(255,255,255,.1)}
.price-card.featured .price-btn{background:linear-gradient(90deg,#2563eb,#60a5fa);border:none}
.price-feat{margin-top:24px;padding-top:20px;border-top:1px solid rgba(255,255,255,.07)}
.price-feat .ttl{font-size:11.5px;font-weight:700;color:#9aa0ac;margin-bottom:10px}
.price-feat ul{list-style:none;display:flex;flex-direction:column;gap:9px}
.price-feat li{font-size:12.5px;color:#c7c9d1;display:flex;gap:8px;align-items:flex-start}
.price-feat li::before{content:'✓';color:#6ee7b7;flex-shrink:0}

/* ============ FAQ ============ */
.faq-grid{display:grid;grid-template-columns:340px 1fr;gap:24px;margin-top:50px}
@media (max-width:900px){.faq-grid{grid-template-columns:1fr}}
.faq-help{background:linear-gradient(160deg,#12203a,#05070c);border-radius:16px;padding:26px}
.faq-avatars{display:flex}
.faq-avatars span{width:34px;height:34px;border-radius:50%;background:#2a2d35;border:2px solid #0a0f1a;margin-left:-8px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700}
.faq-avatars span:first-child{margin-left:0}
.faq-avatars .you{background:#2563eb}
.faq-help h4{margin-top:16px;font-size:17px;font-weight:700}
.faq-help p{margin-top:6px;font-size:13px;color:#9aa0ac}
.faq-btn{display:inline-block;margin-top:18px;padding:10px 18px;border-radius:8px;border:1px solid rgba(255,255,255,.15);font-size:13px;font-weight:600}
.faq-item{border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:20px 22px;margin-bottom:12px;background:#0a0a0d;cursor:pointer}
.faq-item.open{background:#0d1420}
.faq-q{display:flex;align-items:center;justify-content:space-between;font-size:15px;font-weight:600}
.faq-q .ic{width:26px;height:26px;border-radius:50%;border:1px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.faq-a{margin-top:12px;font-size:13.5px;color:#9aa0ac;line-height:1.6;display:none}
.faq-item.open .faq-a{display:block}

/* ============ FOOTER ============ */
.footer{position:relative;padding:80px 60px 0;overflow:hidden}
.footer-grid{display:grid;grid-template-columns:1.4fr repeat(4,1fr);gap:30px;position:relative;z-index:1}
@media (max-width:900px){.footer-grid{grid-template-columns:1fr 1fr}}
.footer-brand h4{font-size:16px;font-weight:700}
.footer-brand p{margin-top:10px;font-size:13px;color:#8a8f99;line-height:1.6;max-width:260px}
.footer-social{display:flex;gap:12px;margin-top:16px}
.footer-social span{width:30px;height:30px;border-radius:8px;border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center}
.footer-col .ttl{font-size:11px;color:#6a6e77;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px}
.footer-col ul{list-style:none;display:flex;flex-direction:column;gap:11px}
.footer-col a{font-size:13px;color:#c7c9d1}
.footer-col a:hover{color:#fff}
.footer-bottom{position:relative;z-index:1;margin-top:70px;padding:22px 0;border-top:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:space-between;font-size:12px;color:#6a6e77}
.footer-bottom .links{display:flex;gap:22px}
.footer-word{font-size:min(20vw,240px);font-weight:800;color:rgba(255,255,255,.03);line-height:1;letter-spacing:-.03em;white-space:nowrap;margin-top:20px;user-select:none}
@media (max-width:620px){.footer-bottom{flex-direction:column;align-items:flex-start;gap:12px}}

/* ============ SMALL PHONES (<=480px) — extra refinements on top of the
   900px tablet/mobile breakpoint already applied throughout ============ */
@media (max-width:480px){
  .sec{padding:48px 18px}
  .sec-hd h2{font-size:28px}
  .wrap{padding:0 18px}
  .nav{padding:14px 18px}
  .stat-card .num{font-size:32px}
  .tile-row{grid-template-columns:1fr}
  .footer-grid{grid-template-columns:1fr 1fr;gap:24px}
  .predictive-card{padding:24px 20px}
  .price-card{padding:24px 20px}
  .faq-help,.faq-item{padding:18px}
  .analyse-diagram{height:200px}
  .analyse-node{font-size:13px;padding:7px 11px}
  .pred-stack{height:230px}
  .pred-row{padding:12px 14px;gap:10px}
  .pred-row .body h5{font-size:13px}
  .pred-row .body p{font-size:10.5px}
  .pred-row .pct{font-size:20px}
}
/* ============ ASK MIRA — website analysis report + pop-ups ============
   Shared styles from partials/ask-mira (report cards, category pop-up with
   animated pie, name/email form), coloured for this page's light widget via
   the variables below. Driven by partials.ask-mira.report-script. */
@include('partials.ask-mira.styles')
.mira-report,.a-modal-overlay,.lead-modal-overlay{
  --card:#fff;--card-h:#fff;--bg3:#f5f5f7;--white:#17181a;
  --g100:#2a2a2e;--g200:#4a4a50;--g300:#6b6b70;--g400:#9a9a9e;--g500:#d9d9de;--g600:#ececef;
  --brd:#ececef;--brd2:#e2e2e6;--blue:#5383EC;--blue2:#3f6fd8;--acc-rgb:83,131,236;--on-acc:#fff;
  --emerald:#16a34a;--amber:#d97706;--rose:#e0564c;
  --f1:'Inter',system-ui,sans-serif;--fm:'Space Grotesk','Inter',sans-serif;--ease:cubic-bezier(.16,1,.3,1);
  font-family:'Inter',system-ui,sans-serif;color:#17181a}
.mira-report{margin-top:20px;border-radius:28px;padding:26px 28px;box-shadow:0 30px 80px -20px rgba(0,0,0,.55)}
.mira-report .a-summary{display:flex;align-items:center;justify-content:space-between;gap:12px}
.mira-report .analysis-url{font-weight:600;color:#17181a;font-size:14px}
.mira-report .a-pie-card{background:#f5f5f7;border-color:#ececef}
/* "Want the full picture?" — a row at the bottom of the report on this page. */
.mira-report .chat-upsell-aside{width:auto;margin-top:18px;flex-direction:row;align-items:center;justify-content:space-between;gap:16px;
  background:linear-gradient(135deg,rgba(83,131,236,.1),rgba(83,131,236,.04));border:1px solid rgba(83,131,236,.25);border-radius:18px}
.mira-report .a-upsell-btn{flex-shrink:0;background:#5383EC;color:#fff;border-radius:999px;padding:10px 20px}
@media (max-width:640px){.mira-report{padding:20px 16px;border-radius:20px}.mira-report .chat-upsell-aside{flex-direction:column;align-items:flex-start}}
.a-modal,.lead-modal{background:#fff;border-color:#ececef;box-shadow:0 20px 60px rgba(0,0,0,.35)}
</style>
</head>
<body>

{{-- ============ NAV ============ --}}
<nav class="nav">
  <div class="brand">
    <img src="{{ asset('images/homepage/logo-mark-colored.png') }}" class="logo-mark" alt="XPlatforms">
    XPlatforms
  </div>
  <ul class="nav-links" id="navLinks">
    <li><a href="#">Home</a></li>
    <li><a href="#">Industries</a></li>
    <li><a href="{{ route('simulator') }}">Simulator</a></li>
    <li><a href="{{ route('pricing') }}">Pricing</a></li>
  </ul>
  <div class="nav-right">
    <a href="{{ route('book-demo') }}" class="book-demo"><span class="label">Book Demo</span> ↗</a>
    <button type="button" class="nav-burger" id="navBurger" aria-label="Toggle menu" aria-expanded="false" aria-controls="navLinks"><span></span></button>
  </div>
</nav>

{{-- ============ HERO ============ --}}
<section class="hero">
  <div class="hero-bg"><img src="{{ asset('images/homepage/hero-earth.jpg') }}" alt=""></div>
  <div class="hero-inner">
    <h1><span class="accent">AI Customer</span>Intelligence</h1>
    <p class="hero-sub">
      Businesses collected data.
      <strong>X Platforms turns it into intelligence.</strong>
    </p>
  </div>
</section>

{{-- ============ ASK MIRA — chatbot widget ============
     Precise rebuild of the Figma "Ask Mira" card. Wired to the same live
     backend as the classic homepage widget: route('chat.send') /
     route('chat.reset') — see App\Http\Controllers\PublicChatController. --}}
<section class="sec chatbot-section" id="chatbot-slot">
  <div class="wrap">
    <div class="mira-widget">

      <aside class="mira-sidebar">
        <button type="button" class="mira-newchat" onclick="miraResetChat()">
          <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          New Chat
        </button>
        <a class="mira-premium-btn" href="{{ route('mira-premium') }}">&#9733; Try Premium Mira</a>

        <div class="mira-group">
          <div class="mira-label">Industry</div>
          <div class="mira-select-wrap">
            <select id="miraIndustry" class="mira-select" onchange="miraRenderStarters(this.value)">
              <option value="">All Industry</option>
              @foreach($industries as $industry)
                <option value="{{ $industry->id }}">{{ $industry->name }}</option>
              @endforeach
            </select>
            <svg viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
          </div>
        </div>

        <div class="mira-group">
          <div class="mira-label">Try Asking</div>
          <div class="mira-starters" id="miraStarters">
            @foreach(($chatQuestionsByIndustry['all'] ?? collect(['What does X Platform do?','How much does it cost?']))->take(6) as $q)
              <button type="button" class="mira-starter" onclick="miraAskSuggested({{ \Illuminate\Support\Js::from($q) }})">{{ $q }}</button>
            @endforeach
          </div>
        </div>
      </aside>

      <div class="mira-main">
        <div class="mira-tabs">
          <div class="mira-tabswitch">
            <button type="button" class="mira-tab" id="miraTabAnalyze" onclick="miraSwitchTab('analyze')">Analyse my website</button>
            <button type="button" class="mira-tab active" id="miraTabAsk" onclick="miraSwitchTab('ask')">Ask Mira</button>
          </div>
        </div>

        <div class="mira-panel" id="miraPanelAsk">
          <div class="mira-empty" id="miraEmpty">
            <div class="mira-orb-wrap" aria-hidden="true">
              <span class="mira-orb-ripple"></span><span class="mira-orb-ripple r2"></span>
              <span class="mira-orb-orbit"></span><span class="mira-orb-orbit o2"></span>
              <img src="{{ asset('images/homepage/mira/orb.png') }}" class="mira-orb" alt="">
            </div>
            <h3>Hey, I'm <span class="mira-accent">Mira.</span> How can I help you today?</h3>
            <p>Understand your customers, discover insights, and make smarter decisions.</p>
          </div>
          <div class="mira-messages" id="miraMessages" style="display:none"></div>

          <div class="mira-input-row">
            <textarea id="miraInput" class="mira-input" rows="1" placeholder="Ask Mira about your customers, data, insights, or predictions…" onkeydown="if(event.key==='Enter'&amp;&amp;!event.shiftKey){event.preventDefault();miraSendChat()}"></textarea>
            <button type="button" class="mira-send" onclick="miraSendChat()"><svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg></button>
          </div>
        </div>

        <div class="mira-panel" id="miraPanelAnalyze" style="display:none">
          <div class="mira-empty">
            <div class="mira-orb-wrap" aria-hidden="true">
              <span class="mira-orb-ripple"></span><span class="mira-orb-ripple r2"></span>
              <span class="mira-orb-orbit"></span><span class="mira-orb-orbit o2"></span>
              <img src="{{ asset('images/homepage/mira/orb.png') }}" class="mira-orb" alt="">
            </div>
            <h3>Paste your <span class="mira-accent">website.</span> I'll take a look</h3>
            <p>I'll run a quick check and tell you what stands out.</p>
          </div>
          <p class="mira-analyze-hint">Paste your website URL and I'll analyse it for you.</p>
          <div class="mira-input-row">
            <input type="text" id="miraAnalyzeInput" class="mira-input" placeholder="https://yourwebsite.com" onkeydown="if(event.key==='Enter'){event.preventDefault();miraSendAnalyze()}">
            <button type="button" class="mira-send" onclick="miraSendAnalyze()"><svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg></button>
          </div>
          <p class="mira-analyze-status" id="miraAnalyzeStatus"></p>
        </div>

        <div class="mira-sources">
          <div class="mira-sources-label">Connect Sources</div>
          <div class="mira-sources-row">
            <span class="src-tile t-stripe" title="Payments">
              <svg viewBox="0 0 24 24" fill="none" stroke="#6ec3fa" stroke-width="2" stroke-linecap="round"><path d="M12 3a9 9 0 018 13"/><path d="M12 21a9 9 0 01-8-13"/><line x1="5" y1="12" x2="19" y2="12"/><line x1="7" y1="16" x2="15" y2="16"/><circle cx="19" cy="7" r="1" fill="#6ec3fa" stroke="none"/><circle cx="5" cy="17" r="1" fill="#6ec3fa" stroke="none"/></svg>
            </span>
            <span class="src-tile t-shopify" title="Shopify">
              <svg viewBox="0 0 24 24" fill="none" stroke="#f0b63f" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7.5 8c0-2.2 1.8-4.5 4.5-4.5S16.5 5.8 16.5 8"/><path d="M6 8h12l1 12a2 2 0 01-2 2H7a2 2 0 01-2-2L6 8z"/><path d="M10.8 13c0-.7.6-1.1 1.3-1.1s1.3.4 1.3.9-.5.8-1.3 1c-.8.2-1.3.5-1.3 1.1s.6 1.1 1.3 1.1 1.3-.3 1.3-.9" stroke-width="1.2"/></svg>
            </span>
            <span class="src-tile t-globe" title="Website">
              <svg viewBox="0 0 24 24" fill="none" stroke="#ea6b3e" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="12" y1="3" x2="12" y2="21"/></svg>
            </span>
            <span class="src-tile t-hubspot" title="CRM">
              <svg viewBox="0 0 24 24" fill="none" stroke="#39c7bb" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="15.5" r="3"/><line x1="12.8" y1="13" x2="17" y2="7.5"/><circle cx="18" cy="6.3" r="1.4" fill="#39c7bb" stroke="none"/><line x1="9.3" y1="16.8" x2="6" y2="18.3"/><circle cx="5" cy="18.8" r="1.2" fill="#39c7bb" stroke="none"/></svg>
            </span>
            <span class="src-tile t-mail" title="Email">
              <svg viewBox="0 0 24 24" fill="none" stroke="#ef8fe0" stroke-width="1.7"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 7l9 7 9-7"/></svg>
            </span>
            <span class="src-tile t-bank" title="Transactions">
              <svg viewBox="0 0 24 24" fill="none" stroke="#4f8ff7" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="1.5"/><line x1="8" y1="8.5" x2="8" y2="14"/><line x1="11" y1="8.5" x2="11" y2="14"/><line x1="14" y1="8.5" x2="14" y2="14"/><line x1="17" y1="9.5" x2="17" y2="14"/><path d="M7 16c1.2-1 2.7-1.5 4-1.5"/></svg>
            </span>
          </div>
        </div>
      </div>
    </div>

    {{-- "Analyse my website" report: shown by renderAnalysisReport()
         (partials.ask-mira.report-script). No recommendations on this free
         widget — Premium Mira adds those. --}}
    <div class="analysis-panel mira-report" id="analysisPanel">
      <div class="a-summary">
        <div class="analysis-url" id="analysisUrl"></div>
      </div>
      <div class="a-pie-grid" id="analysisPieGrid"></div>
      <aside class="chat-upsell-aside" id="chatUpsellAside">
        <div class="a-upsell-text">
          <strong>Want the full picture?</strong>
          A multi-page site crawl and Lighthouse speed scoring are available on our paid plans.
        </div>
        <a href="{{ route('pricing') }}" class="a-upsell-btn">View Pricing</a>
      </aside>
    </div>
  </div>
</section>

{{-- Category pop-up (click a report card) and the name/email form shown
     before analysing a site for visitors who aren't logged in. --}}
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

<div class="lead-modal-overlay" id="leadModalOverlay" onclick="if(event.target===this) miraCloseLead()">
  <div class="lead-modal">
    <button type="button" class="lead-modal-close" onclick="miraCloseLead()" aria-label="Close">&times;</button>
    <div class="lead-modal-title">Before we analyze your site</div>
    <p class="lead-modal-sub">Enter your details and we'll run the free check right after.</p>
    <div class="lead-modal-field">
      <label for="leadName">Name</label>
      <input type="text" id="leadName" placeholder="Your name">
    </div>
    <div class="lead-modal-field">
      <label for="leadEmail">Email</label>
      <input type="email" id="leadEmail" placeholder="you@company.com" onkeydown="if(event.key==='Enter'){event.preventDefault();miraSubmitLead()}">
    </div>
    <p class="lead-modal-error" id="leadModalError"></p>
    <button type="button" class="lead-modal-submit" id="leadModalSubmit" onclick="miraSubmitLead()">Continue</button>
  </div>
</div>

{{-- ============ SIGNALS EVERYWHERE ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="sec-hd">
      <h2>Your customers are leaving<span class="accent">signals everywhere.</span></h2>
      <p>Every click, visit, purchase, and conversation tells you something. X Platforms brings those signals together to reveal what your customers need, want, and are likely to do next.</p>
    </div>

    <div class="signals-flow">
      <div class="signals-diagram">
        <svg viewBox="0 0 494 542" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="flowOrange" gradientUnits="userSpaceOnUse" x1="270" y1="0" x2="474" y2="0">
              <stop offset="0" stop-color="#ff8a3d"/>
              <stop offset="1" stop-color="#4a4640" stop-opacity=".35"/>
            </linearGradient>
            <filter id="dotGlow" x="-200%" y="-200%" width="500%" height="500%">
              <feGaussianBlur stdDeviation="3"/>
            </filter>
          </defs>
          {{-- Curved connectors: one per pill, converging to the node point (474,271). All six share the
               same warm orange tone in the Figma design -- only the pill label text colour differs (Sales = blue). --}}
          <path d="M270,38.5 C375,38.5 375,271 474,271" stroke="url(#flowOrange)" stroke-width="1.5"/>
          <path d="M270,131.5 C375,131.5 375,271 474,271" stroke="url(#flowOrange)" stroke-width="1.5"/>
          <path d="M270,224.5 C375,224.5 375,271 474,271" stroke="url(#flowOrange)" stroke-width="1.5"/>
          <path d="M270,317.5 C375,317.5 375,271 474,271" stroke="url(#flowOrange)" stroke-width="1.5"/>
          <path d="M270,410.5 C375,410.5 375,271 474,271" stroke="url(#flowOrange)" stroke-width="1.5"/>
          <path d="M270,503.5 C375,503.5 375,271 474,271" stroke="url(#flowOrange)" stroke-width="1.5"/>
          {{-- Glowing dots at each pill's edge --}}
          <circle cx="270" cy="38.5" r="7" fill="#ff8a3d" opacity=".4" filter="url(#dotGlow)"/>
          <circle cx="270" cy="38.5" r="4" fill="#ff8a3d"/>
          <circle cx="270" cy="131.5" r="7" fill="#ff8a3d" opacity=".4" filter="url(#dotGlow)"/>
          <circle cx="270" cy="131.5" r="4" fill="#ff8a3d"/>
          <circle cx="270" cy="224.5" r="7" fill="#ff8a3d" opacity=".4" filter="url(#dotGlow)"/>
          <circle cx="270" cy="224.5" r="4" fill="#ff8a3d"/>
          <circle cx="270" cy="317.5" r="7" fill="#ff8a3d" opacity=".4" filter="url(#dotGlow)"/>
          <circle cx="270" cy="317.5" r="4" fill="#ff8a3d"/>
          <circle cx="270" cy="410.5" r="7" fill="#ff8a3d" opacity=".4" filter="url(#dotGlow)"/>
          <circle cx="270" cy="410.5" r="4" fill="#ff8a3d"/>
          <circle cx="270" cy="503.5" r="7" fill="#ff8a3d" opacity=".4" filter="url(#dotGlow)"/>
          <circle cx="270" cy="503.5" r="4" fill="#ff8a3d"/>
          {{-- Thin outline chevron where the lines converge (not a solid arrowhead) --}}
          <path d="M460,258 L478,271 L460,284" stroke="#e4e4e8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        </svg>
        <div class="signal-pills">
          <div class="signal-pill c-website"><span>Website</span></div>
          <div class="signal-pill c-app"><span>App</span></div>
          <div class="signal-pill c-social"><span>Social</span></div>
          <div class="signal-pill c-email"><span>Email</span></div>
          <div class="signal-pill c-ads"><span>Ads</span></div>
          <div class="signal-pill c-sales"><span>Sales</span></div>
        </div>
      </div>

      <div class="signals-node">
        <div class="signals-circle"><img src="{{ asset('images/homepage/logo-mark-colored.png') }}" alt="XPlatforms"></div>
        <div class="signals-node-label">X Platforms</div>
      </div>

      <div class="signals-eq">=</div>

      <div class="signals-result">
        <h3>Signals<br><span class="accent">become</span><br>Intelligence</h3>
      </div>
    </div>

    {{-- Stat cards --}}
    <div class="stat-row">
      <div class="stat-card blue"><span class="tag">XPLATFORM</span><div class="num">42</div><div class="lbl">Active Nodes</div></div>
      <div class="stat-card gold"><span class="tag">XPLATFORM</span><div class="num">1.2M</div><div class="lbl">Events processed / min</div></div>
      <div class="stat-card orange"><span class="tag">XPLATFORM</span><div class="num">1.2B+</div><div class="lbl">Signals analysed</div></div>
    </div>
  </div>
</section>

{{-- ============ EIGHT LAYERS ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="sec-hd">
      <h2>Eight layers and One<span class="accent">Intelligence engine.</span></h2>
      <p>From raw customer signals to intelligent action, X Platforms connects every layer of the decision-making process.</p>
    </div>

    <div class="layer-row">
      @php
        $layers = [
          ['ingest','Ingest','Bring customer data together from every relevant source.'],
          ['unify','Unify','Connect fragmented data into one consistent customer view.'],
          ['map','Map','Understand relationships, behaviours, and customer journeys.'],
          ['detect','Detect','Identify patterns, signals, anomalies, and emerging intent.'],
          ['predict','Predict','Turn behaviour into predictions about what happens next.'],
          ['plan','Plan','Determine the next best action for each customer.'],
          ['execute','Execute','Activate intelligence across the channels that matter.'],
          ['learn','Learn','Feed outcomes back into the system so intelligence keeps improving.'],
        ];
      @endphp
      @foreach($layers as $i => $l)
        <div class="layer-item">
          <div class="layer-icon"><img src="{{ asset('images/homepage/layers/'.$l[0].'.png') }}" alt="{{ $l[1] }}"></div>
          <h4>{{ $l[1] }}</h4>
          <p>{{ $l[2] }}</p>
        </div>
        @if(!$loop->last)
          <div class="layer-arrow"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
        @endif
      @endforeach
    </div>
  </div>
</section>

{{-- ============ RAW DATA TO REVENUE (2x2) ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="sec-hd">
      <h2>From Raw data to<span class="accent">Revenue.</span></h2>
      <p>Connect your customer data, let AI find what matters, and turn every prediction into an opportunity to grow.</p>
    </div>

    <div class="feature-grid">
      {{-- 01 Connect sources --}}
      <div class="feature-card f1">
        <div class="fno">01</div>
        <h3>Connect sources</h3>
        <p class="desc">Bring data together from your website, app, CRM, social channels, email, ads, and other customer touchpoints.</p>
        <div class="src-icons">
          <span class="src-tile2 t-stripe">
            <svg viewBox="0 0 24 24" fill="none" stroke="#82d9ff" stroke-width="2" stroke-linecap="round"><path d="M12 3a9 9 0 018 13"/><path d="M12 21a9 9 0 01-8-13"/><line x1="5" y1="12" x2="19" y2="12"/><line x1="7" y1="16" x2="15" y2="16"/><circle cx="19" cy="7" r="1" fill="#82d9ff" stroke="none"/><circle cx="5" cy="17" r="1" fill="#82d9ff" stroke="none"/></svg>
          </span>
          <span class="src-tile2 t-shopify">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fabd07" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7.5 8c0-2.2 1.8-4.5 4.5-4.5S16.5 5.8 16.5 8"/><path d="M6 8h12l1 12a2 2 0 01-2 2H7a2 2 0 01-2-2L6 8z"/><path d="M10.8 13c0-.7.6-1.1 1.3-1.1s1.3.4 1.3.9-.5.8-1.3 1c-.8.2-1.3.5-1.3 1.1s.6 1.1 1.3 1.1 1.3-.3 1.3-.9" stroke-width="1.2"/></svg>
          </span>
          <span class="src-tile2 t-globe">
            <svg viewBox="0 0 24 24" fill="none" stroke="#ff6125" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="12" y1="3" x2="12" y2="21"/></svg>
          </span>
          <span class="src-tile2 t-hubspot">
            <svg viewBox="0 0 24 24" fill="none" stroke="#12e5da" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="15.5" r="3"/><line x1="12.8" y1="13" x2="17" y2="7.5"/><circle cx="18" cy="6.3" r="1.4" fill="#12e5da" stroke="none"/><line x1="9.3" y1="16.8" x2="6" y2="18.3"/><circle cx="5" cy="18.8" r="1.2" fill="#12e5da" stroke="none"/></svg>
          </span>
          <span class="src-tile2 t-mail">
            <svg viewBox="0 0 24 24" fill="none" stroke="#f6b9f7" stroke-width="1.7"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 7l9 7 9-7"/></svg>
          </span>
          <span class="src-tile2 t-bank">
            <svg viewBox="0 0 24 24" fill="none" stroke="#11a1f9" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="1.5"/><line x1="8" y1="8.5" x2="8" y2="14"/><line x1="11" y1="8.5" x2="11" y2="14"/><line x1="14" y1="8.5" x2="14" y2="14"/><line x1="17" y1="9.5" x2="17" y2="14"/><path d="M7 16c1.2-1 2.7-1.5 4-1.5"/></svg>
          </span>
        </div>
        <div class="connect-hint"><span class="plus">+</span> Connect your existing data stack</div>
      </div>

      {{-- 02 AI analyses --}}
      <div class="feature-card f2">
        <div class="fno">02</div>
        <h3>AI analyses</h3>
        <p class="desc">X Platforms processes every signal to understand customer behaviour, intent, patterns, and context.</p>
        <div class="analyse-diagram">
          <svg viewBox="0 0 460 230" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="arrowGreen" gradientUnits="userSpaceOnUse" x1="230" y1="115" x2="60" y2="35">
                <stop offset="0" stop-color="#34d399"/><stop offset="1" stop-color="#6ee7b7"/>
              </linearGradient>
              <linearGradient id="arrowBlue" gradientUnits="userSpaceOnUse" x1="230" y1="115" x2="400" y2="35">
                <stop offset="0" stop-color="#60a5fa"/><stop offset="1" stop-color="#93c5fd"/>
              </linearGradient>
              <linearGradient id="arrowGold" gradientUnits="userSpaceOnUse" x1="230" y1="115" x2="60" y2="195">
                <stop offset="0" stop-color="#f2c14e"/><stop offset="1" stop-color="#fde68a"/>
              </linearGradient>
              <linearGradient id="arrowRed" gradientUnits="userSpaceOnUse" x1="230" y1="115" x2="400" y2="195">
                <stop offset="0" stop-color="#f76b6b"/><stop offset="1" stop-color="#fca5a5"/>
              </linearGradient>
            </defs>
            <path d="M205,95 C150,75 110,58 75,42" stroke="url(#arrowGreen)" stroke-width="2"/>
            <path d="M255,95 C310,75 350,58 385,42" stroke="url(#arrowBlue)" stroke-width="2"/>
            <path d="M205,135 C150,155 110,172 75,188" stroke="url(#arrowGold)" stroke-width="2"/>
            <path d="M255,135 C310,155 350,172 385,188" stroke="url(#arrowRed)" stroke-width="2"/>
            <circle cx="205" cy="95" r="5" fill="#34d399"/>
            <circle cx="255" cy="95" r="5" fill="#60a5fa"/>
            <circle cx="205" cy="135" r="5" fill="#f2c14e"/>
            <circle cx="255" cy="135" r="5" fill="#f76b6b"/>
            <path d="M68,37 L78,42 L72,51" stroke="#6ee7b7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            <path d="M392,37 L382,42 L388,51" stroke="#93c5fd" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            <path d="M68,193 L78,188 L72,179" stroke="#fde68a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            <path d="M392,193 L382,188 L388,179" stroke="#fca5a5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
          </svg>
          <div class="analyse-node behaviour">Behaviour</div>
          <div class="analyse-node patterns">Patterns</div>
          <div class="analyse-center">
            <img src="{{ asset('images/homepage/logo-mark-colored.png') }}" alt="">
          </div>
          <div class="analyse-node intent">Intent</div>
          <div class="analyse-node context">Context</div>
        </div>
        <div class="analyse-foot">Connect every signal to the customer and their journey.</div>
      </div>

      {{-- 03 Get predictions --}}
      <div class="feature-card f3">
        <div class="fno">03</div>
        <h3>Get predictions</h3>
        <p class="desc">Turn customer intelligence into clear predictions about what is likely to happen next.</p>
        <div class="pred-stack">
          <div class="pred-row risk">
            <div class="avatar">
              <svg viewBox="0 0 40 40"><rect width="40" height="40" fill="#4a3b36"/><circle cx="20" cy="16" r="7" fill="#d8b8a3"/><path d="M6 38c0-9 6-14 14-14s14 5 14 14" fill="#d8b8a3"/></svg>
            </div>
            <div class="body"><h5>Churn Risk</h5><p>Identify customers who may be ready to leave.</p></div>
            <div class="pct">87%</div>
          </div>
          <div class="pred-row intent">
            <div class="avatar">
              <svg viewBox="0 0 40 40"><rect width="40" height="40" fill="#3d3530"/><circle cx="20" cy="15" r="7" fill="#e8c4a0"/><path d="M5 38c0-9.5 6.5-15 15-15s15 5.5 15 15" fill="#2b2320"/><path d="M8 38c0-8 5.5-13 12-13s12 5 12 13" fill="#e8c4a0"/></svg>
            </div>
            <div class="body"><h5>Purchase intent</h5><p>Spot customers showing strong signals to buy.</p></div>
            <div class="pct">92%</div>
          </div>
          <div class="pred-row engage">
            <div class="avatar">
              <svg viewBox="0 0 40 40"><rect width="40" height="40" fill="#1f2937"/><circle cx="20" cy="15" r="7" fill="#c89a78"/><path d="M5 38c0-9.5 6.5-15 15-15s15 5.5 15 15" fill="#1e293b"/></svg>
            </div>
            <div class="body"><h5>Engagement</h5><p>Predict future engagement and likelihood to convert.</p></div>
            <div class="pct">76%</div>
          </div>
        </div>
      </div>

      {{-- 04 Grow revenue --}}
      <div class="feature-card f4">
        <div class="fno">04</div>
        <h3>Grow revenue</h3>
        <p class="desc">Turn predictions into timely actions that improve customer outcomes and drive measurable growth.</p>
        <div class="rev-card" style="margin-top:22px">
          <div class="rev-top"><span>Revenue growth</span><span class="rev-select">Last 6 months ⌄</span></div>
          <div class="rev-num"><span class="rev-plus">+</span><span class="rev-pct">32%</span></div>
          <svg class="rev-chart" viewBox="0 0 300 96" preserveAspectRatio="none">
            <defs>
              <linearGradient id="revFill" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#7dd3fc" stop-opacity=".35"/>
                <stop offset="1" stop-color="#7dd3fc" stop-opacity="0"/>
              </linearGradient>
            </defs>
            <path d="M0,58 C20,30 35,12 55,14 C75,16 80,50 100,58 C120,66 130,74 148,74 C166,74 172,52 192,46 C212,40 222,44 240,40 C256,36 268,30 300,32 L300,96 L0,96 Z" fill="url(#revFill)"/>
            <path d="M0,58 C20,30 35,12 55,14 C75,16 80,50 100,58 C120,66 130,74 148,74 C166,74 172,52 192,46 C212,40 222,44 240,40 C256,36 268,30 300,32" fill="none" stroke="#7dd3fc" stroke-width="2" stroke-linecap="round"/>
            @foreach([[0,58],[55,14],[100,58],[148,74],[192,46],[240,40],[300,32]] as $pt)
              <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="3.2" fill="#eaf6ff"/>
            @endforeach
          </svg>
          <div class="rev-mini">
            <div><div class="v">+32%</div><div class="l">Revenue lift</div></div>
            <div><div class="v">-34%</div><div class="l">Churn reduction</div></div>
            <div><div class="v">+2.8x</div><div class="l">Customer LTV</div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ============ WATCH INTELLIGENCE IN MOTION ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="sec-hd">
      <h2>Watch intelligence in<span class="accent">Motion.</span></h2>
      <p>See how X Platforms turns live customer signals into decisions in real time.</p>
    </div>

    <div class="console">
      <div class="console-main">
        <div class="console-hd">
          <div class="console-dots"><span></span><span></span><span></span></div>
          <span>x-platforms :: processing monitor</span>
          <span class="console-live">LIVE</span>
        </div>
        <div class="console-log">
          @php
            $logs = [
              ['14:19:43','MAP','Behavioural graph: 24,183 nodes mapped'],
              ['14:19:46','DETECT','Anomaly: 3.2σ deviation in funnel'],
              ['14:19:48','PREDICT','Churn scored 847 customers @ 94.7%'],
              ['14:19:50','PLAN','3 retention strategies generated'],
              ['14:19:52','EXEC','Triggered offers for 234 at-risk'],
              ['14:19:54','LEARN','Models retrained — accuracy +0.3%'],
              ['14:19:57','INGEST','6,132 POS transactions received'],
              ['14:19:59','PREDICT','1,429 cross-sell targets identified'],
              ['14:11:03','DETECT','Mobile ⇄ 2.4x conversion pattern found'],
              ['14:12:03','EXEC','Ad spend shifted $12K to high-intent'],
              ['14:13:03','INGEST','Streaming 4,218 events from CRM'],
              ['14:13:58','UNIFY','892 profiles merged across channels'],
            ];
          @endphp
          @foreach($logs as $log)
            <div><span class="t">{{ $log[0] }}</span><span class="tagpill {{ $log[1] }}">{{ $log[1] }}</span>{{ $log[2] }}</div>
          @endforeach
        </div>
      </div>
      <div class="console-side">
        <div class="side-block confidence">
          <div class="l">Confidence</div>
          <div class="v">94.1%</div>
          <div class="side-bar"><i></i></div>
        </div>
        <div class="side-block prediction">
          <div class="l">Prediction</div>
          <div class="v">2,603 <small>/mo</small></div>
          <div class="side-bar"><i></i></div>
        </div>
        @php
          $stages = [
            ['L1 Ingest','#7dd3fc','IDLE'],['L2 Unify','#6ee7b7','IDLE'],['L3 Map','#f76b6b','IDLE'],
            ['L4 Detect','#f2c14e','IDLE'],['L5 Predict','#a78bfa','IDLE'],['L6 Plan','#6ee7b7','ACTIVE'],
            ['L7 Exec','#f2c14e','IDLE'],['L8 Learn','#93c5fd','IDLE'],
          ];
        @endphp
        <div style="margin-top:8px">
          @foreach($stages as $s)
            <div class="layer-status">
              <span><span class="dot" style="background:{{ $s[1] }}"></span>{{ $s[0] }}</span>
              <span class="state {{ $s[2] === 'ACTIVE' ? 'active' : '' }}">{{ $s[2] }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ============ RAW DATA TO AI PREDICTIONS ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="sec-hd">
      <h2>Raw data to<span class="accent">AI predictions.</span></h2>
      <p>X Platforms turns disconnected customer signals into clean, actionable intelligence.</p>
    </div>

    <div class="toggle-row" style="margin-top:30px">
      <span class="toggle"></span> AI Processed
    </div>

    <div class="rawdata-grid">
      <div class="dtable-scroll">
      <table class="dtable">
        <thead>
          <tr><th>Customer</th><th>Source</th><th>Data Point</th><th>Status</th><th>Value</th></tr>
        </thead>
        <tbody>
          @php
            $rows = [
              ['J. Smith','CRM','john.smith@, ph: 0412-XXX','Verified'],
              ['john_s_92','Website','3 page views, bounced','Active'],
              ['Customer #4821','Call Centre','Complaint: billing issue','Resolved'],
              ['@johnsmith','Social','Negative sentiment tweet','Improving'],
              ['ID: 90125','POS','$42.50 purchase 03/12','Loyal'],
              ['j.smith','Email','Opened 2/10 campaigns','Actionable'],
              ['john_s_92','Website','3 page views, bounced','Resolved'],
              ['Customer #5521','Social','$12.50 purchase 03/12','Isolated'],
            ];
          @endphp
          @foreach($rows as $r)
            <tr>
              <td>{{ $r[0] }}</td>
              <td class="src">{{ $r[1] }}</td>
              <td class="pt">{{ $r[2] }}</td>
              <td class="st">{{ $r[3] }}</td>
              <td><a href="#" style="color:#9aa0ac">View</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
      </div>

      <div class="profile-card">
        <div class="profile-hd">
          <div class="av">JS</div>
          <div><h4>John Smith</h4><p>John.smith@gmail.com</p></div>
          <span class="badge">High value</span>
        </div>
        <div class="profile-pct">87%</div>
        <div class="profile-lbl">Purchase Intent</div>
        <div class="profile-mini">
          <div><div class="v">24</div><div class="l">Total Visit</div></div>
          <div><div class="v">$82.50</div><div class="l">Last Purchase</div></div>
          <div><div class="v">High</div><div class="l">Engagement</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ============ BUILT AROUND YOUR BUSINESS ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="sec-hd">
      <h2>Built around your<span class="accent">Business.</span></h2>
      <p>Every industry has different customers, signals, and decisions. X Platforms adapts its intelligence to what matters most.</p>
    </div>

    <div class="industry-bar">
      <span class="sel-label">Select your Industry</span>
      @php $industries = ['Retail','Finance','SaaS','Education','Healthcare','Travel','Telecom','Energy']; @endphp
      @foreach($industries as $i => $ind)
        <span class="industry-chip {{ $i===0 ? 'active' : '' }}">{{ $ind }} ⌄</span>
      @endforeach
    </div>

    <div class="tile-row">
      <div class="tile c-blue"><div class="v">34%</div><div class="l">Churn Reduction</div></div>
      <div class="tile c-green"><div class="v">3.5 X</div><div class="l">Revenue Lift</div></div>
      <div class="tile c-gold"><div class="v">$2.4M</div><div class="l">Annual Impact</div></div>
      <div class="tile c-cyan"><div class="v">96%</div><div class="l">Model Accuracy</div></div>
    </div>

    <div class="predictive-card">
      <h3>Predictive <span class="accent" style="font-size:26px">Customer Intelligence</span></h3>
      <p>Know which customers will churn, who's ready to convert, and which offer will land before it happens.</p>
      <div class="predictive-tiles">
        <div><div class="v">97%</div><div class="l">Accuracy</div></div>
        <div><div class="v">3.5x</div><div class="l">Revenue Lift</div></div>
        <div><div class="v">&lt;200ms</div><div class="l">Response</div></div>
      </div>
    </div>
  </div>
</section>

{{-- ============ PRICING ============ --}}
<section class="sec" id="pricing-preview">
  <div class="wrap">
    <div class="sec-hd">
      <h2>Simple<span class="accent">Transparent Pricing</span></h2>
      <p>Start free, scale as you grow. All plans include your 30 day proof of concept with real data.</p>
    </div>

    <div class="pricing-grid">
      <div class="price-card">
        <h3>Starter</h3>
        <p class="plan-desc">For growing teams ready to put AI to work on their customer data.</p>
        <div class="price">$299<small>/ month, billed annually</small></div>
        <a href="#" class="price-btn">Get Started</a>
        <div class="price-feat">
          <div class="ttl">What Include:</div>
          <ul>
            <li>Up to 500K customer profiles</li>
            <li>5 AI prediction models</li>
            <li>3 data source integrations</li>
            <li>Real-time scoring &amp; segmentation</li>
            <li>Email &amp; chat support</li>
            <li>Standard dashboards</li>
          </ul>
        </div>
      </div>

      <div class="price-card featured">
        <h3>Growth</h3>
        <p class="plan-desc">For teams that need the full 8-layer engine across multiple channels.</p>
        <div class="price">$799<small>/ month, billed annually</small></div>
        <a href="#" class="price-btn">Start Free Trial</a>
        <div class="price-feat">
          <div class="ttl">What Include:</div>
          <ul>
            <li>Up to 5M customer profiles</li>
            <li>All 8 AI layers unlocked</li>
            <li>Unlimited integrations</li>
            <li>Predictive audiences &amp; journeys</li>
            <li>Priority support &amp; onboarding</li>
            <li>Custom dashboards &amp; exports</li>
            <li>A/B testing &amp; attribution</li>
          </ul>
        </div>
      </div>

      <div class="price-card">
        <h3>Custom</h3>
        <p class="plan-desc">For large organisations needing dedicated infrastructure, compliance, and SLAs.</p>
        <div class="price">$---<small>/ month, billed annually</small></div>
        <a href="{{ route('contact') }}" class="price-btn">Contact Sales</a>
        <div class="price-feat">
          <div class="ttl">What Include:</div>
          <ul>
            <li>Unlimited profiles &amp; predictions</li>
            <li>Private cloud or on-premise deploy</li>
            <li>SOC 2 / ISO 27001 / GDPR controls</li>
            <li>Dedicated customer success manager</li>
            <li>99.99% uptime SLA</li>
            <li>Custom model training</li>
            <li>SSO &amp; role-based access</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ============ FAQ ============ --}}
<section class="sec">
  <div class="wrap">
    <div class="sec-hd" style="max-width:none">
      <h2>Frequently<br>asked questions</h2>
      <p>Find quick answers to common questions about the platform, pricing, and security.</p>
    </div>

    <div class="faq-grid">
      <div class="faq-help">
        <div class="faq-avatars">
          {{-- PLACEHOLDER 5: real avatar photos --}}
          <span>A</span><span>B</span><span>C</span><span class="you">You</span>
        </div>
        <h4>Still have questions?</h4>
        <p>Reach out, and our team will guide you.</p>
        <a href="{{ route('contact') }}" class="faq-btn">Talk to our team</a>
      </div>

      <div class="faq-list">
        @php
          $faqs = [
            ['What is X Platforms?','X Platforms is an AI customer intelligence platform that connects your customer data, identifies important signals, predicts behaviour, and helps your team take the right action.', true],
            ['What data can I connect?', 'Website, app, CRM, social channels, email, ads, POS, and call centre data — via native integrations or our API.', false],
            ['How does X Platforms turn data into intelligence?', 'Every signal flows through 8 AI layers — Ingest, Unify, Map, Detect, Predict, Plan, Execute, Learn — turning raw events into a ranked next-best-action.', false],
            ['What can X Platforms predict?', 'Churn risk, purchase intent, engagement likelihood, lifetime value, and the next best action for each customer.', false],
            ['Do I need a data science team to use it?', 'No — X Platforms ships with pre-trained models and dashboards designed for marketing, sales, and retention teams to use directly.', false],
          ];
        @endphp
        @foreach($faqs as $f)
          <div class="faq-item {{ $f[2] ? 'open' : '' }}">
            <div class="faq-q">{{ $f[0] }} <span class="ic">{{ $f[2] ? '−' : '+' }}</span></div>
            <div class="faq-a">{{ $f[1] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- ============ FOOTER ============ --}}
<footer class="footer">
  <div class="footer-grid">
    <div class="footer-brand">
      <h4>XPlatforms</h4>
      <p>AI customer intelligence for businesses that want to understand, predict, and act on every customer signal.</p>
      <div class="footer-social">
        <span>𝕏</span><span>◎</span><span>in</span>
      </div>
    </div>
    <div class="footer-col">
      <div class="ttl">Product</div>
      <ul><li><a href="#">Architecture</a></li><li><a href="#">Integrations</a></li><li><a href="#">Industries</a></li><li><a href="{{ route('pricing') }}">Pricing</a></li></ul>
    </div>
    <div class="footer-col">
      <div class="ttl">Company</div>
      <ul><li><a href="{{ route('about') }}">About</a></li><li><a href="{{ route('careers') }}">Careers</a></li><li><a href="{{ route('blog') }}">Blogs</a></li><li><a href="{{ route('contact') }}">Contact</a></li></ul>
    </div>
    <div class="footer-col">
      <div class="ttl">Resources</div>
      <ul><li><a href="#">Documentation</a></li><li><a href="{{ route('case-studies') }}">Case Studies</a></li><li><a href="#">API Reference</a></li><li><a href="{{ route('security') }}">Security</a></li></ul>
    </div>
    <div class="footer-col">
      <div class="ttl">Legal</div>
      <ul><li><a href="{{ route('privacy') }}">Privacy</a></li><li><a href="{{ route('terms') }}">Terms</a></li></ul>
    </div>
  </div>

  <div class="footer-bottom">
    <span>© {{ date('Y') }} XPlatforms. All rights reserved.</span>
    <span class="links">
      <a href="{{ route('privacy') }}">Privacy Policy</a>
      <a href="{{ route('terms') }}">Terms of Service</a>
      <a href="#top">↑ Back to top</a>
    </span>
  </div>
  <div class="footer-word">xplatforms</div>
</footer>

<script>
var navBurger = document.getElementById('navBurger');
var navLinks = document.getElementById('navLinks');
if (navBurger && navLinks) {
  navBurger.addEventListener('click', function () {
    var isOpen = navLinks.classList.toggle('open');
    navBurger.classList.toggle('open', isOpen);
    navBurger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
  navLinks.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', function () {
      navLinks.classList.remove('open');
      navBurger.classList.remove('open');
      navBurger.setAttribute('aria-expanded', 'false');
    });
  });
}

document.querySelectorAll('.faq-item').forEach(function (item) {
  item.addEventListener('click', function () {
    var isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(function (i) {
      i.classList.remove('open');
      i.querySelector('.ic').textContent = '+';
    });
    if (!isOpen) {
      item.classList.add('open');
      item.querySelector('.ic').textContent = '−';
    }
  });
});

/* ============ ASK MIRA — chatbot widget ============
   Talks to the same live backend as the classic homepage widget:
   route('chat.send') / route('chat.reset'). See
   App\Http\Controllers\PublicChatController for the request/response shape. */
var MIRA_CHAT_QUESTIONS_BY_INDUSTRY = @json($chatQuestionsByIndustry ?? []);
var MIRA_DEFAULT_STARTERS = ['What does X Platform do?', 'How much does it cost?'];
var MIRA_LOGGED_IN = {{ !empty($loggedIn) ? 'true' : 'false' }};

/* Website analysis report + category pop-up, shared with the classic
   homepage and Premium Mira: renderAnalysisReport(), openAnalysisModal(),
   closeAnalysisModal(), etc. */
@include('partials.ask-mira.report-script')

function miraLooksLikeUrl(text) {
  return /^https?:\/\/\S+$/i.test(text.trim());
}

/* ---- Name/email form before analysing (visitors who aren't logged in) ----
   Posts to route('chat.analyze-lead'), which saves the lead and returns the
   same reply + report as chat.send. `source` says where the URL came from
   ('chat' or 'analyze') so the result shows up in the right place. */
var miraPendingUrl = null, miraPendingSource = null;

function miraOpenLead(url, source) {
  miraPendingUrl = url; miraPendingSource = source;
  document.getElementById('leadName').value = '';
  document.getElementById('leadEmail').value = '';
  document.getElementById('leadModalError').textContent = '';
  document.getElementById('leadModalOverlay').classList.add('show');
  setTimeout(function () { document.getElementById('leadName').focus(); }, 50);
}

function miraCloseLead() {
  document.getElementById('leadModalOverlay').classList.remove('show');
  miraPendingUrl = null; miraPendingSource = null;
}

async function miraSubmitLead() {
  var name = document.getElementById('leadName').value.trim();
  var email = document.getElementById('leadEmail').value.trim();
  var errEl = document.getElementById('leadModalError');
  if (!name) { errEl.textContent = 'Please enter your name.'; return; }
  if (!/^\S+@\S+\.\S+$/.test(email)) { errEl.textContent = 'Please enter a valid email address.'; return; }

  var url = miraPendingUrl, source = miraPendingSource;
  var btn = document.getElementById('leadModalSubmit');
  btn.disabled = true; btn.textContent = 'Analysing…';
  errEl.textContent = '';
  var status = document.getElementById('miraAnalyzeStatus');
  var typing = null;
  if (source === 'analyze') {
    status.classList.remove('err');
    status.textContent = 'Checking your site — this can take a moment…';
  } else {
    typing = miraAddMessage('bot typing', 'Checking your site…');
  }
  document.getElementById('leadModalOverlay').classList.remove('show');

  try {
    var res = await fetch('{{ route("chat.analyze-lead") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ name: name, email: email, url: url, industry_id: (document.getElementById('miraIndustry') || {}).value || null })
    });
    var data = await res.json();
    var text = res.ok ? data.reply : (data.error || data.message || 'Something went wrong. Please try again.');
    if (typing) typing.remove();
    if (source === 'analyze') {
      status.textContent = text;
      status.classList.toggle('err', !res.ok);
    } else {
      miraAddMessage('bot', text);
    }
    if (res.ok && data.report) renderAnalysisReport(data.report);
  } catch (e) {
    if (typing) typing.remove();
    if (source === 'analyze') { status.textContent = 'Could not reach the server. Check your connection and try again.'; status.classList.add('err'); }
    else miraAddMessage('bot', 'Could not reach the server. Check your connection and try again.');
  }
  btn.disabled = false; btn.textContent = 'Continue';
  miraPendingUrl = null; miraPendingSource = null;
}

function miraRenderStarters(industryId) {
  var key = industryId ? String(industryId) : 'all';
  var questions = MIRA_CHAT_QUESTIONS_BY_INDUSTRY[key];
  if (!questions || !questions.length) questions = MIRA_CHAT_QUESTIONS_BY_INDUSTRY['all'] || MIRA_DEFAULT_STARTERS;
  var wrap = document.getElementById('miraStarters');
  if (!wrap) return;
  wrap.innerHTML = '';
  questions.slice(0, 6).forEach(function (q) {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'mira-starter';
    btn.textContent = q;
    btn.addEventListener('click', function () { miraAskSuggested(q); });
    wrap.appendChild(btn);
  });
}

function miraSwitchTab(tab) {
  var isAsk = tab === 'ask';
  document.getElementById('miraTabAsk').classList.toggle('active', isAsk);
  document.getElementById('miraTabAnalyze').classList.toggle('active', !isAsk);
  document.getElementById('miraPanelAsk').style.display = isAsk ? 'flex' : 'none';
  document.getElementById('miraPanelAnalyze').style.display = isAsk ? 'none' : 'flex';
}

/* Bot replies may contain a real URL — render it as a clickable <a> using
   DOM APIs only, never innerHTML on reply text (model-influenced content). */
function miraRenderBotMessage(el, text) {
  var urlRe = /https?:\/\/[^\s]+/g, lastIndex = 0, match;
  while ((match = urlRe.exec(text)) !== null) {
    if (match.index > lastIndex) el.appendChild(document.createTextNode(text.slice(lastIndex, match.index)));
    var trailing = match[0].match(/[.,;:!?)]+$/);
    var url = trailing ? match[0].slice(0, -trailing[0].length) : match[0];
    var a = document.createElement('a');
    a.href = url; a.textContent = url; a.target = '_blank'; a.rel = 'noopener noreferrer';
    el.appendChild(a);
    if (trailing) el.appendChild(document.createTextNode(trailing[0]));
    lastIndex = urlRe.lastIndex;
  }
  if (lastIndex < text.length) el.appendChild(document.createTextNode(text.slice(lastIndex)));
}

async function miraPostToChat(message) {
  var res = await fetch('{{ route("chat.send") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    body: JSON.stringify({ message: message, industry_id: (document.getElementById('miraIndustry') || {}).value || null })
  });
  var data = await res.json();
  return { ok: res.ok, data: data };
}

function miraShowMessages() {
  document.getElementById('miraEmpty').style.display = 'none';
  document.getElementById('miraMessages').style.display = 'flex';
}

/* Appends a chat bubble ('user', 'bot' or 'bot typing') and returns it. */
function miraAddMessage(cls, text) {
  miraShowMessages();
  var msgs = document.getElementById('miraMessages');
  var el = document.createElement('div');
  el.className = 'mira-msg ' + cls;
  if (cls === 'bot') miraRenderBotMessage(el, text); else el.textContent = text;
  msgs.appendChild(el);
  msgs.scrollTop = msgs.scrollHeight;
  return el;
}

async function miraSendChat() {
  var input = document.getElementById('miraInput'), msg = input.value.trim();
  if (!msg) return;
  input.value = ''; input.style.height = 'auto';
  miraAddMessage('user', msg);
  // A pasted website URL is analysed; visitors who aren't logged in give their details first.
  if (!MIRA_LOGGED_IN && miraLooksLikeUrl(msg)) { miraOpenLead(msg, 'chat'); return; }
  var typing = miraAddMessage('bot typing', 'Thinking…');
  try {
    var result = await miraPostToChat(msg);
    typing.remove();
    miraAddMessage('bot', result.ok ? result.data.reply : (result.data.error || 'Something went wrong. Please try again.'));
    if (result.ok && result.data.report) renderAnalysisReport(result.data.report);
  } catch (e) {
    typing.remove();
    miraAddMessage('bot', 'Could not reach the server. Check your connection and try again.');
  }
}

async function miraSendAnalyze() {
  var input = document.getElementById('miraAnalyzeInput'), url = input.value.trim();
  var status = document.getElementById('miraAnalyzeStatus');
  status.classList.remove('err');
  if (!url) { status.textContent = 'Enter a website URL first.'; status.classList.add('err'); return; }
  if (!/^https?:\/\//i.test(url)) { status.textContent = 'Include http:// or https:// at the start.'; status.classList.add('err'); return; }
  if (!MIRA_LOGGED_IN) { miraOpenLead(url, 'analyze'); return; }
  status.textContent = 'Checking your site — this can take a moment…';
  try {
    var result = await miraPostToChat(url);
    if (result.ok) {
      status.textContent = result.data.reply;
      if (result.data.report) renderAnalysisReport(result.data.report);
    } else {
      status.textContent = result.data.error || 'Something went wrong. Please try again.';
      status.classList.add('err');
    }
  } catch (e) {
    status.textContent = 'Could not reach the server. Check your connection and try again.';
    status.classList.add('err');
  }
}

function miraAskSuggested(q) {
  document.getElementById('miraInput').value = q;
  miraSendChat();
}

async function miraResetChat() {
  try {
    await fetch('{{ route("chat.reset") }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
  } catch (e) {}
  var msgs = document.getElementById('miraMessages');
  if (msgs) { msgs.innerHTML = ''; msgs.style.display = 'none'; }
  var empty = document.getElementById('miraEmpty');
  if (empty) empty.style.display = 'flex';
  var analyzeInput = document.getElementById('miraAnalyzeInput');
  if (analyzeInput) analyzeInput.value = '';
  var analyzeStatus = document.getElementById('miraAnalyzeStatus');
  if (analyzeStatus) { analyzeStatus.textContent = ''; analyzeStatus.classList.remove('err'); }
  // Clear any website report too.
  document.getElementById('analysisPanel').classList.remove('show');
  document.getElementById('chatUpsellAside').classList.remove('show');
  closeAnalysisModal();
  miraSwitchTab('ask');
}

var miraInputEl = document.getElementById('miraInput');
if (miraInputEl) {
  miraInputEl.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
  });
}
</script>
</body>

</html>
