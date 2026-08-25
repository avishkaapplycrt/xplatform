ï»¿<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Customer Journey Simulator â Watch 8 Layers of Intelligence in Action | X Platforms</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="description" content="Interactive simulator showing exactly how X Platforms processes your customers through 8 AI layers â from raw data ingestion to live predictions. Choose your industry, pick a scenario, watch intelligence happen.">
<meta name="keywords" content="AI customer journey simulator, predictive analytics demo, 8 layer AI engine, customer intelligence platform, churn prediction demo, EdTech AI, SaaS churn, eLearning analytics, X Platforms">
<meta name="robots" content="index, follow, max-snippet:-1">
<link rel="canonical" href="https://xplatforms.ai/simulator">
<meta property="og:type" content="website">
<meta property="og:title" content="AI Customer Journey Simulator â X Platforms">
<meta property="og:description" content="Pick an industry. Pick a scenario. Watch the AI process a real customer through all 8 layers live.">
<meta property="og:url" content="https://xplatforms.ai/simulator">
<meta property="og:site_name" content="X Platforms">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="AI Customer Journey Simulator â X Platforms">
<meta name="twitter:description" content="See how 8 layers of AI turn raw customer data into predictions that drive revenue. Interactive demo, all 15 industries.">
@verbatim
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebApplication","name":"X Platforms Customer Journey Simulator","description":"Interactive AI simulator demonstrating how X Platforms 8-layer intelligence engine processes customer journeys across 15 industries, from data ingestion to predictive insights and autonomous execution.","url":"https://xplatforms.ai/simulator","applicationCategory":"BusinessApplication","operatingSystem":"Web Browser","offers":{"@type":"Offer","price":"0","priceCurrency":"USD"},"publisher":{"@type":"Organization","name":"X Platforms","url":"https://xplatforms.ai"}}
</script>
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"What industries does the simulator support?","acceptedAnswer":{"@type":"Answer","text":"The simulator supports all 15 industries served by X Platforms: Retail, Banking, Healthcare, Telecom, Travel, Insurance, Manufacturing, Energy, Education, Real Estate, Media, Pharma, Automotive, Food & Beverage, and Construction."}},{"@type":"Question","name":"Is this using real AI?","acceptedAnswer":{"@type":"Answer","text":"The simulator demonstrates the actual architecture and logic of X Platforms' 8-layer engine with realistic, industry-specific data scenarios. For a live demonstration using your actual customer data, book a personalised demo."}},{"@type":"Question","name":"Can I simulate multiple scenarios?","acceptedAnswer":{"@type":"Answer","text":"Yes â each industry has multiple customer scenarios you can run. Switch industries or scenarios and run the simulation as many times as you like to explore different use cases."}},{"@type":"Question","name":"How accurate are the predictions shown?","acceptedAnswer":{"@type":"Answer","text":"The simulator uses representative scenarios. In production, X Platforms achieves 97% average prediction accuracy across churn, purchase propensity, and lifetime value models, verified through live A/B testing and backtesting."}},{"@type":"Question","name":"How long does real implementation take?","acceptedAnswer":{"@type":"Answer","text":"Most customers are live within 2-4 weeks. One-click connectors handle data source integration, and pre-trained industry models begin generating predictions within hours of connecting your data."}}]}
</script>
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
  --ease:cubic-bezier(.16,1,.3,1);--mw:1260px;
  --L1:var(--blue);--L2:var(--cyan);--L3:var(--rose);--L4:var(--amber);
  --L5:var(--violet);--L6:var(--emerald);--L7:#fb923c;--L8:#60a5fa;
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
.hero-stats,.sim-wrap,.fe-data,.sg-item,.eb-card,.flow-wrap,.ltm,.ltc-visual,.roi-card,.roi-result,.rrb,.uc,.cst,.faq-item{backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur)}
html{scroll-behavior:smooth}
body{background:var(--body-bg);background-attachment:fixed;color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
button,select,input{font-family:var(--f1)}
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
.nav-login{display:inline-flex;align-items:center;gap:7px;padding:8px 18px;border:1px solid var(--g500);color:var(--g200)!important;border-radius:8px;font-weight:500!important;font-size:13px!important;transition:all .25s;background:transparent;cursor:pointer;font-family:var(--f1)}
.nav-login:hover{border-color:var(--brd2);color:var(--white)!important;background:rgba(79,143,255,.05)}
.appearance-toggle{width:36px;height:36px;border-radius:50%;background:var(--bg3);border:1px solid var(--brd2);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--g200);transition:all .2s;flex-shrink:0;padding:0}
.appearance-toggle:hover{color:var(--blue);border-color:var(--blue)}
.appearance-toggle svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2}

/* HERO */
.hero{position:relative;z-index:1;padding:128px 40px 0;text-align:center;max-width:var(--mw);margin:0 auto}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:24px;letter-spacing:.5px}
.breadcrumb a{color:var(--g300);transition:color .2s}.breadcrumb a:hover{color:var(--blue)}
.hero-badge{display:inline-flex;align-items:center;gap:8px;padding:5px 14px 5px 8px;background:var(--blue-g);border:1px solid var(--brd2);border-radius:100px;font-family:var(--fm);font-size:10.5px;letter-spacing:.8px;text-transform:uppercase;color:var(--blue);margin-bottom:22px;opacity:0;animation:fadeUp .6s var(--ease) .1s forwards}
.badge-dot{width:7px;height:7px;border-radius:50%;background:var(--emerald);box-shadow:0 0 10px var(--emerald);animation:blink 2.5s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
.hero h1{font-weight:800;font-size:clamp(34px,4.8vw,60px);line-height:1.08;letter-spacing:-2px;max-width:760px;margin:0 auto 18px;opacity:0;animation:fadeUp .7s var(--ease) .2s forwards}
.hero h1 span{background:linear-gradient(135deg,var(--blue),var(--cyan),var(--violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-sub{font-size:16px;line-height:1.75;color:var(--g200);max-width:520px;margin:0 auto 28px;font-weight:350;opacity:0;animation:fadeUp .7s var(--ease) .35s forwards}
.hero-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;margin-bottom:36px;opacity:0;animation:fadeUp .7s var(--ease) .5s forwards}

/* HERO STATS BAR */
.hero-stats{display:flex;justify-content:center;gap:0;border:1px solid var(--brd);border-radius:14px;overflow:hidden;max-width:680px;margin:0 auto 56px;opacity:0;animation:fadeUp .7s var(--ease) .65s forwards;background:var(--card)}
.hs-item{flex:1;padding:18px 16px;text-align:center;border-right:1px solid var(--brd);position:relative}
.hs-item:last-child{border-right:none}
.hs-item::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;opacity:0;transition:opacity .3s}
.hs-item:hover::before{opacity:1}
.hs-n{font-weight:800;font-size:24px;letter-spacing:-1px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hs-l{font-family:var(--fm);font-size:9.5px;color:var(--g400);letter-spacing:1.2px;text-transform:uppercase;margin-top:3px}

/* HOW TO USE STRIP */
.how-strip{position:relative;z-index:1;max-width:var(--mw);margin:0 auto;padding:0 40px 48px;display:flex;gap:0;justify-content:center}
.hs-step{display:flex;align-items:center;gap:12px}
.hs-num{width:26px;height:26px;border-radius:50%;background:var(--blue-g);border:1px solid var(--brd2);display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:10px;color:var(--blue);font-weight:600;flex-shrink:0}
.hs-text{font-size:13px;color:var(--g300);white-space:nowrap}
.hs-sep{width:32px;height:1px;background:var(--brd);margin:0 12px;flex-shrink:0}

/* SIMULATOR SHELL */
.sim-shell{position:relative;z-index:1;max-width:var(--mw);margin:0 auto;padding:0 40px 80px}
.sim-wrap{background:var(--card);border:1px solid var(--brd);border-radius:20px;overflow:hidden;box-shadow:0 0 80px rgba(79,143,255,.06)}

/* TOP BAR */
.sim-topbar{display:flex;align-items:center;gap:8px;padding:12px 20px;border-bottom:1px solid var(--brd);background:rgba(255,255,255,.015)}
.stb-dot{width:10px;height:10px;border-radius:50%}
.stb-title{font-family:var(--fm);font-size:11px;color:var(--g400);margin-left:8px;letter-spacing:.5px;flex:1}
.stb-live{display:flex;align-items:center;gap:6px;font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:.5px;transition:color .3s}
.stb-live.running{color:var(--emerald)}
.stb-live.done{color:var(--blue)}
.live-dot{width:6px;height:6px;border-radius:50%;background:var(--g500);transition:background .3s}
.stb-live.running .live-dot{background:var(--emerald);box-shadow:0 0 8px var(--emerald);animation:blink 1.5s ease-in-out infinite}
.stb-live.done .live-dot{background:var(--blue);box-shadow:0 0 8px rgba(79,143,255,.6)}

/* CONTROLS */
.sim-controls{display:flex;align-items:center;gap:12px;padding:20px 24px;border-bottom:1px solid var(--brd);flex-wrap:wrap;background:rgba(255,255,255,.01)}
.ctrl-group{display:flex;flex-direction:column;gap:5px;min-width:0}
.ctrl-label{font-family:var(--fm);font-size:9px;letter-spacing:1.2px;text-transform:uppercase;color:var(--g500)}
.ctrl-select{background:var(--bg3);border:1px solid var(--g500);border-radius:8px;padding:9px 14px;color:var(--white);font-size:13.5px;cursor:pointer;outline:none;transition:border-color .2s;min-width:0}
.ctrl-select:focus{border-color:var(--blue)}
.ctrl-select option{background:var(--bg3)}
.ctrl-sep{width:1px;background:var(--brd);height:36px;flex-shrink:0}
.ctrl-speed{display:flex;align-items:flex-end;gap:5px}
.spd-btn{padding:8px 14px;background:var(--bg3);border:1px solid var(--g500);border-radius:7px;color:var(--g300);font-family:var(--fm);font-size:11px;cursor:pointer;transition:all .2s}
.spd-btn.active{border-color:var(--blue);color:var(--blue);background:var(--blue-g)}
.sim-run-btn{margin-left:auto;padding:10px 28px;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;border:none;border-radius:10px;font-weight:600;font-size:13.5px;cursor:pointer;transition:all .25s;box-shadow:0 4px 20px rgba(79,143,255,.2);white-space:nowrap;display:flex;align-items:center;gap:8px}
.sim-run-btn:hover{box-shadow:0 6px 28px rgba(79,143,255,.35);transform:translateY(-1px)}
.sim-run-btn:disabled{opacity:.4;cursor:not-allowed;transform:none;box-shadow:none}
.sim-reset-btn{padding:10px 18px;background:transparent;border:1px solid var(--g500);border-radius:10px;color:var(--g300);font-size:13px;cursor:pointer;transition:all .2s;white-space:nowrap}
.sim-reset-btn:hover{border-color:var(--g400);color:var(--white)}

/* PIPELINE STEPS */
.sim-pipe{display:flex;gap:0;overflow-x:auto;border-bottom:1px solid var(--brd);padding:0;background:var(--bg2)}
.sp{flex:1;min-width:110px;padding:16px 10px 14px;text-align:center;border-right:1px solid var(--brd);position:relative;transition:all .45s var(--ease);cursor:default;overflow:hidden}
.sp:last-child{border-right:none}
.sp::before{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;transform:scaleX(0);transform-origin:left;transition:transform .8s var(--ease)}
.sp.active::before{transform:scaleX(1)}
.sp.active{background:var(--blue-g2)}
.sp.done{background:rgba(52,211,153,.02)}
.sp.done::before{transform:scaleX(1);background:var(--emerald)!important}
.sp-num{font-family:var(--fm);font-size:9px;color:var(--g500);letter-spacing:1px;margin-bottom:6px;transition:color .3s}
.sp.active .sp-num{color:var(--blue)}
.sp.done .sp-num{color:var(--emerald)}
.sp-icon{width:28px;height:28px;border-radius:8px;margin:0 auto 6px;display:flex;align-items:center;justify-content:center;border:1px solid var(--brd);background:rgba(255,255,255,.02);transition:all .4s}
.sp.active .sp-icon{border-color:var(--blue);background:var(--blue-g);box-shadow:0 0 16px rgba(79,143,255,.15)}
.sp.done .sp-icon{border-color:var(--emerald);background:rgba(52,211,153,.06)}
.sp-icon svg{width:13px;height:13px;fill:none;stroke-width:1.8;stroke:var(--g400);transition:stroke .3s}
.sp.active .sp-icon svg{stroke:var(--blue)}
.sp.done .sp-icon svg{stroke:var(--emerald)}
.sp-name{font-size:11px;font-weight:600;color:var(--g400);transition:color .3s}
.sp.active .sp-name{color:var(--white)}
.sp.done .sp-name{color:var(--emerald)}
.sp-done-check{position:absolute;top:8px;right:8px;width:14px;height:14px;border-radius:50%;background:var(--emerald);display:flex;align-items:center;justify-content:center;opacity:0;transform:scale(0);transition:all .3s var(--ease)}
.sp.done .sp-done-check{opacity:1;transform:scale(1)}
.sp-done-check svg{width:8px;height:8px;stroke:#fff;fill:none;stroke-width:3}

/* MAIN BODY */
.sim-body{display:grid;grid-template-columns:1fr 300px;min-height:440px}
.sim-feed-col{border-right:1px solid var(--brd);display:flex;flex-direction:column;overflow:hidden}
.sim-feed-scroll{flex:1;overflow-y:auto;padding:0;max-height:440px;scroll-behavior:smooth}
.sim-feed-scroll::-webkit-scrollbar{width:4px}
.sim-feed-scroll::-webkit-scrollbar-track{background:transparent}
.sim-feed-scroll::-webkit-scrollbar-thumb{background:var(--brd2);border-radius:2px}

/* EMPTY STATE */
.sim-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;padding:60px 32px;text-align:center;gap:16px}
.se-icon{width:64px;height:64px;border-radius:16px;background:var(--blue-g);border:1px solid var(--brd2);display:flex;align-items:center;justify-content:center}
.se-icon svg{width:28px;height:28px;stroke:var(--blue);fill:none;stroke-width:1.5}
.se-title{font-weight:600;font-size:16px;color:var(--g100)}
.se-sub{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:280px}
.se-hints{display:flex;flex-direction:column;gap:8px;margin-top:8px;width:100%;max-width:320px}
.se-hint{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:8px;background:rgba(255,255,255,.02);border:1px solid var(--brd);text-align:left}
.sh-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
.sh-text{font-size:12.5px;color:var(--g300);line-height:1.5}

/* FEED ENTRIES */
.feed-entry{padding:18px 22px;border-bottom:1px solid var(--brd);opacity:0;animation:entryIn .5s var(--ease) forwards}
@keyframes entryIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.fe-header{display:flex;align-items:center;gap:8px;margin-bottom:10px}
.fe-tag{padding:2px 8px;border-radius:4px;font-family:var(--fm);font-size:9.5px;letter-spacing:.5px;font-weight:500;flex-shrink:0}
.fe-layer-num{font-family:var(--fm);font-size:10px;color:var(--g500);letter-spacing:.5px}
.fe-time{font-family:var(--fm);font-size:10px;color:var(--g500);margin-left:auto;letter-spacing:.3px}
.fe-title{font-weight:600;font-size:14.5px;letter-spacing:-.1px;margin-bottom:6px;color:var(--white)}
.fe-desc{font-size:13px;color:var(--g300);line-height:1.65;margin-bottom:12px}
.fe-data{background:var(--bg3);border:1px solid var(--brd);border-radius:10px;padding:14px 16px;font-family:var(--fm);font-size:11.5px;line-height:1.9;color:var(--g300)}
.fe-data strong{color:var(--g100);font-weight:500}
.fe-data .fdd-key{color:var(--g400)}
.fe-data .fdd-val{color:var(--g100)}
.fe-data .fdd-pos{color:var(--emerald)}
.fe-data .fdd-neg{color:var(--rose)}
.fe-data .fdd-hi{color:var(--blue)}
.fe-insight{display:flex;align-items:flex-start;gap:10px;margin-top:10px;padding:10px 14px;border-radius:8px;background:var(--blue-g);border:1px solid var(--brd2);font-size:12.5px;color:var(--g200);line-height:1.55}
.fe-insight svg{width:14px;height:14px;stroke:var(--blue);fill:none;stroke-width:2;flex-shrink:0;margin-top:1px}

/* TAG COLOURS */
.tag-l1{background:rgba(79,143,255,.1);color:var(--L1)}
.tag-l2{background:rgba(56,189,248,.1);color:var(--L2)}
.tag-l3{background:rgba(244,114,182,.1);color:var(--L3)}
.tag-l4{background:rgba(251,191,36,.1);color:var(--L4)}
.tag-l5{background:rgba(129,140,248,.1);color:var(--L5)}
.tag-l6{background:rgba(52,211,153,.1);color:var(--L6)}
.tag-l7{background:rgba(251,146,60,.1);color:var(--L7)}
.tag-l8{background:rgba(96,165,250,.1);color:var(--L8)}

/* SIDEBAR */
.sim-sidebar{padding:20px;display:flex;flex-direction:column;gap:12px;overflow-y:auto;max-height:440px}
.sim-sidebar::-webkit-scrollbar{width:4px}
.sim-sidebar::-webkit-scrollbar-thumb{background:var(--brd);border-radius:2px}

.ss-metric{padding:14px 16px;border-radius:10px;background:rgba(255,255,255,.02);border:1px solid var(--brd);transition:border-color .4s}
.ss-metric.pulse{border-color:var(--brd2)}
.ss-m-label{font-family:var(--fm);font-size:9px;letter-spacing:1.2px;text-transform:uppercase;color:var(--g500);margin-bottom:5px}
.ss-m-val{font-weight:700;font-size:22px;letter-spacing:-.5px;transition:all .5s var(--ease)}
.ss-m-bar{height:3px;border-radius:2px;background:var(--g600);margin-top:8px;overflow:hidden}
.ss-m-fill{height:100%;border-radius:2px;width:0;transition:width 1.2s var(--ease)}
.ss-m-change{font-family:var(--fm);font-size:10px;margin-top:4px;color:var(--g500)}

.ss-layers-title{font-family:var(--fm);font-size:9px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g500);margin:4px 0 8px}
.ss-layer-row{display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:7px;background:rgba(255,255,255,.015);border:1px solid transparent;transition:all .35s;margin-bottom:4px}
.ss-layer-row.active{border-color:var(--brd2);background:var(--blue-g)}
.ss-layer-row.done-l{border-color:rgba(52,211,153,.12);background:rgba(52,211,153,.03)}
.ssl-dot{width:5px;height:5px;border-radius:50%;flex-shrink:0}
.ssl-name{font-family:var(--fm);font-size:10px;color:var(--g400);flex:1;transition:color .3s}
.ss-layer-row.active .ssl-name{color:var(--blue)}
.ss-layer-row.done-l .ssl-name{color:var(--emerald)}
.ssl-status{font-family:var(--fm);font-size:9px;color:var(--g600);letter-spacing:.5px;transition:color .3s}
.ss-layer-row.active .ssl-status{color:var(--emerald)}
.ss-layer-row.done-l .ssl-status{color:var(--emerald)}

/* SUMMARY */
.sim-summary{display:none;padding:24px;border-top:1px solid var(--brd);background:rgba(52,211,153,.02)}
.sim-summary.show{display:block;animation:entryIn .5s var(--ease) forwards}
.sum-head{display:flex;align-items:center;gap:10px;margin-bottom:20px}
.sum-badge{display:inline-flex;align-items:center;gap:6px;font-family:var(--fm);font-size:10px;letter-spacing:1px;text-transform:uppercase;padding:5px 14px;border-radius:100px;background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);color:var(--emerald)}
.sum-badge svg{width:12px;height:12px;stroke:var(--emerald);fill:none;stroke-width:2.5}
.sum-title{font-weight:700;font-size:16px;margin-left:8px}
.sum-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px}
.sg-item{padding:16px;border-radius:10px;background:var(--bg3);border:1px solid var(--brd);text-align:center;transition:border-color .3s}
.sg-item:hover{border-color:var(--brd2)}
.sgi-n{font-weight:700;font-size:22px;letter-spacing:-.5px;margin-bottom:2px}
.sgi-l{font-family:var(--fm);font-size:9px;color:var(--g400);letter-spacing:.8px;text-transform:uppercase}
.sum-actions{display:flex;gap:10px;flex-wrap:wrap}
.sa-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;transition:all .2s;border:none}
.sa-primary{background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 16px rgba(79,143,255,.2)}
.sa-primary:hover{transform:translateY(-1px);box-shadow:0 6px 24px rgba(79,143,255,.3)}
.sa-secondary{background:transparent;border:1px solid var(--brd2);color:var(--blue)}
.sa-secondary:hover{background:var(--blue-g)}
.sa-ghost{background:rgba(255,255,255,.04);border:1px solid var(--g500);color:var(--g200)}
.sa-ghost:hover{border-color:var(--g400);color:var(--white)}

/* PROGRESS BAR */
.sim-progress{height:2px;background:var(--bg3);position:relative;overflow:hidden}
.sp-fill{height:100%;background:linear-gradient(90deg,var(--blue),var(--violet));width:0;transition:width .6s var(--ease)}

/* WHAT YOU'RE SEEING */
.explain-band{position:relative;z-index:1;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--bg2);padding:56px 40px}
.eb-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:300px 1fr;gap:48px;align-items:start}
.eb-left{}
.eb-left h2{font-weight:700;font-size:clamp(20px,2.4vw,28px);letter-spacing:-1px;margin-bottom:12px;line-height:1.2}
.eb-left p{font-size:14px;color:var(--g300);line-height:1.7;font-weight:350}
.eb-right{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.eb-card{background:var(--card);border:1px solid var(--brd);border-radius:12px;padding:20px;transition:all .3s}
.eb-card:hover{border-color:var(--brd2);background:var(--card-h)}
.eb-card h4{font-size:13.5px;font-weight:600;margin-bottom:6px;letter-spacing:-.1px}
.eb-card p{font-size:12.5px;color:var(--g300);line-height:1.6}
.eb-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:12px}
.eb-icon svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:1.8}

/* DATA FLOW CANVAS */
.flow-section{position:relative;z-index:1;padding:80px 40px;max-width:var(--mw);margin:0 auto}
.flow-wrap{border:1px solid var(--brd);border-radius:20px;overflow:hidden;background:var(--card);position:relative}
canvas#flow{display:block;width:100%;height:260px}
.flow-labels{position:absolute;bottom:16px;left:0;right:0;display:flex;justify-content:space-around;pointer-events:none}
.fl-label{font-family:var(--fm);font-size:10px;color:var(--g400);text-align:center;letter-spacing:.5px}
.fl-label strong{display:block;font-size:11px;color:var(--g200);font-weight:500;margin-bottom:2px}

/* 8 LAYERS DEEP DIVE */
.layers-section{position:relative;z-index:1;padding:0 40px 80px;max-width:var(--mw);margin:0 auto}
.layer-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:24px}
.lt-btn{padding:8px 16px;border-radius:8px;border:1px solid var(--brd);background:transparent;color:var(--g300);font-size:13px;cursor:pointer;transition:all .2s;font-weight:450}
.lt-btn:hover{border-color:var(--brd2);color:var(--g100)}
.lt-btn.active{border-color:var(--blue);background:var(--blue-g);color:var(--white)}
.lt-content{display:none}
.lt-content.active{display:grid;grid-template-columns:1fr 1fr;gap:24px;animation:fadeUp .4s var(--ease)}
.ltc-info{}
.ltc-info h3{font-weight:700;font-size:clamp(20px,2.5vw,26px);letter-spacing:-1px;margin-bottom:12px}
.ltc-info p{font-size:14px;color:var(--g300);line-height:1.75;margin-bottom:16px;font-weight:350}
.ltc-tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px}
.ltc-tag{font-family:var(--fm);font-size:10px;padding:4px 12px;border-radius:6px;background:var(--blue-g);border:1px solid var(--brd);color:var(--g200);letter-spacing:.4px}
.ltc-metrics{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.ltm{padding:14px;border-radius:10px;border:1px solid var(--brd);background:var(--card);text-align:center}
.ltm-n{font-weight:700;font-size:22px;letter-spacing:-.5px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.ltm-l{font-family:var(--fm);font-size:9px;color:var(--g400);text-transform:uppercase;letter-spacing:.8px;margin-top:3px}
.ltc-visual{background:var(--bg3);border:1px solid var(--brd);border-radius:16px;padding:28px;display:flex;flex-direction:column;gap:10px}
.ltv-title{font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:1px;text-transform:uppercase;margin-bottom:6px}
.ltv-row{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:8px;background:rgba(255,255,255,.02);border:1px solid var(--brd);font-size:13px;transition:all .3s}
.ltv-row:hover{border-color:var(--brd2);background:var(--card)}
.ltv-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.ltv-label{color:var(--g200);flex:1}
.ltv-val{font-family:var(--fm);font-size:11px;color:var(--blue)}

/* ROI PERSONALIZER */
.roi-section{position:relative;z-index:1;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--bg2);padding:80px 40px}
.roi-in{max-width:var(--mw);margin:0 auto}
.roi-card{background:var(--card);border:1px solid var(--brd);border-radius:20px;padding:48px;display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center}
.roi-left h2{font-weight:700;font-size:clamp(22px,2.8vw,32px);letter-spacing:-1px;margin-bottom:12px;line-height:1.2}
.roi-left p{font-size:14px;color:var(--g300);line-height:1.7;margin-bottom:28px}
.rc-inputs{display:flex;flex-direction:column;gap:22px}
.rc-group{display:flex;flex-direction:column;gap:8px}
.rc-label-row{display:flex;justify-content:space-between;align-items:center}
.rc-label{font-size:13px;font-weight:500;color:var(--g200)}
.rc-val{font-family:var(--fm);font-size:12px;color:var(--blue)}
input[type=range]{width:100%;-webkit-appearance:none;height:4px;border-radius:2px;background:var(--g600);outline:none;cursor:pointer}
input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:16px;height:16px;border-radius:50%;background:var(--blue);box-shadow:0 0 10px rgba(79,143,255,.4);cursor:pointer}
.roi-result{background:var(--bg3);border:1px solid var(--brd2);border-radius:16px;padding:32px;text-align:center}
.rr-label{font-family:var(--fm);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g400);margin-bottom:8px}
.rr-val{font-weight:800;font-size:52px;letter-spacing:-2.5px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;margin-bottom:6px}
.rr-sub{font-size:13px;color:var(--g400);margin-bottom:24px}
.rr-breakdown{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.rrb{padding:14px;border-radius:10px;background:var(--card);border:1px solid var(--brd)}
.rrb-n{font-weight:700;font-size:18px;color:var(--white);margin-bottom:2px}
.rrb-l{font-family:var(--fm);font-size:9px;color:var(--g400);text-transform:uppercase;letter-spacing:.8px}

/* USE CASES */
.uc-section{position:relative;z-index:1;padding:80px 40px;max-width:var(--mw);margin:0 auto}
.uc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.uc{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:26px;transition:all .35s;cursor:pointer}
.uc:hover{border-color:var(--brd2);background:var(--card-h);transform:translateY(-2px)}
.uc-icon{width:38px;height:38px;border-radius:10px;background:var(--blue-g);display:flex;align-items:center;justify-content:center;margin-bottom:14px;border:1px solid var(--brd)}
.uc-icon svg{width:17px;height:17px;stroke:var(--blue);fill:none;stroke-width:1.8}
.uc h3{font-size:15px;font-weight:600;margin-bottom:6px;letter-spacing:-.1px}
.uc p{font-size:13px;color:var(--g300);line-height:1.6}
.uc-arrow{display:flex;align-items:center;gap:4px;margin-top:14px;font-family:var(--fm);font-size:10px;color:var(--blue);letter-spacing:.5px;opacity:0;transition:opacity .2s}
.uc:hover .uc-arrow{opacity:1}
.uc-arrow svg{width:12px;height:12px;stroke:var(--blue);fill:none;stroke-width:2;transition:transform .2s}
.uc:hover .uc-arrow svg{transform:translateX(2px)}

/* CASE STUDY TEASER */
.cs-teaser{position:relative;z-index:1;border-top:1px solid var(--brd);background:var(--bg2);padding:64px 40px}
.cs-teaser-in{max-width:var(--mw);margin:0 auto}
.cs-tease-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:40px}
.cst{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:26px;transition:all .35s}
.cst:hover{border-color:var(--brd2);background:var(--card-h)}
.cst-logo{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;color:#fff;margin-bottom:16px}
.cst h3{font-size:15px;font-weight:700;margin-bottom:4px}
.cst-ind{font-family:var(--fm);font-size:10px;color:var(--g400);margin-bottom:12px;letter-spacing:.5px}
.cst-stat{font-size:13px;color:var(--g300);line-height:1.6;margin-bottom:16px}
.cst-metric{display:flex;gap:16px;padding-top:14px;border-top:1px solid var(--brd)}
.cm-n{font-weight:700;font-size:20px;letter-spacing:-.5px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.cm-l{font-family:var(--fm);font-size:9px;color:var(--g400);letter-spacing:.8px;text-transform:uppercase;margin-top:2px}
.cst-link{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--blue);margin-top:12px;transition:gap .2s}
.cst-link:hover{gap:8px}

/* FAQ */
.faq-section{position:relative;z-index:1;padding:80px 40px}
.faq-in{max-width:700px;margin:0 auto}
.faq-list{display:flex;flex-direction:column;gap:8px;margin-top:40px}
.faq-item{border:1px solid var(--brd);border-radius:12px;overflow:hidden;background:var(--card)}
.faq-item.open{border-color:var(--brd2)}
.faq-q{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;cursor:pointer;font-weight:500;font-size:14.5px;color:var(--g100);user-select:none;gap:16px}
.faq-q svg{width:18px;height:18px;stroke:var(--g400);fill:none;stroke-width:2;transition:transform .3s;flex-shrink:0}
.faq-item.open .faq-q svg{transform:rotate(45deg);stroke:var(--blue)}
.faq-a{max-height:0;overflow:hidden;transition:max-height .4s var(--ease)}
.faq-item.open .faq-a{max-height:300px;padding:0 22px 18px}
.faq-a p{font-size:14px;color:var(--g300);line-height:1.7}

/* CTA */
.cta{position:relative;z-index:1;padding:100px 40px;text-align:center;border-top:1px solid var(--brd)}
.cta::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:400px;background:radial-gradient(circle,rgba(79,143,255,.05),transparent 65%);pointer-events:none}
.cta h2{font-weight:800;font-size:clamp(28px,3.8vw,48px);letter-spacing:-1.5px;line-height:1.1;margin-bottom:14px;position:relative}
.cta h2 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.cta p{font-size:16px;color:var(--g300);max-width:400px;margin:0 auto 32px;line-height:1.7;position:relative}
.cta-btns{display:flex;gap:12px;justify-content:center;position:relative;flex-wrap:wrap}
.btn-fill{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;border-radius:12px;font-weight:600;font-size:14.5px;border:none;cursor:pointer;transition:all .25s;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 32px rgba(79,143,255,.3);font-family:var(--f1)}
.btn-fill:hover{transform:translateY(-2px);box-shadow:0 8px 48px rgba(79,143,255,.4)}
.btn-g{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;border-radius:12px;font-weight:500;font-size:14.5px;background:rgba(255,255,255,.04);color:var(--g100);border:1px solid var(--g500);cursor:pointer;transition:all .25s;font-family:var(--f1)}
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
.stop{margin-bottom:48px}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}

/* RESPONSIVE */
@media(max-width:1024px){
  .sim-body{grid-template-columns:1fr}
  .sim-sidebar{border-top:1px solid var(--brd);max-height:none;flex-direction:row;flex-wrap:wrap}
  .ss-metric{min-width:140px;flex:1}
  .eb-in{grid-template-columns:1fr}
  .lt-content.active{grid-template-columns:1fr}
  .roi-card{grid-template-columns:1fr;gap:32px}
  .uc-grid,.cs-tease-grid{grid-template-columns:1fr 1fr}
  .sum-grid{grid-template-columns:1fr 1fr}
  .flow-section,.layers-section,.uc-section{padding-left:24px;padding-right:24px}
}
@media(max-width:640px){
  .nav-l{display:none}.nav-in{padding:0 20px}
  .hero,.sim-shell,.explain-band,.roi-section,.cs-teaser,.faq-section,.cta{padding-left:20px;padding-right:20px}
  .hero{padding-top:110px}
  .sim-pipe{flex-wrap:wrap}.sp{min-width:80px}
  .how-strip{display:none}
  .uc-grid,.cs-tease-grid{grid-template-columns:1fr}
  .sum-grid{grid-template-columns:1fr 1fr}
  .sum-actions{flex-direction:column}
  .foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}
  .hero-stats{flex-wrap:wrap}
  .hs-item{min-width:120px}
  .nav-ham{display:flex}
  .lt-content.active{grid-template-columns:1fr}
  .eb-right{grid-template-columns:1fr}
}
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
@endverbatim
</head>
<body>
<script>try{if(localStorage.getItem('xp-appearance')==='dark')document.body.classList.add('theme-dark')}catch(e){}</script>
<canvas id="neural"></canvas>

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

<!-- HERO -->
<header class="hero rv" style="opacity:1;transform:none">
  <nav class="breadcrumb"><a href="{{ route('home') }}">Home</a> / <strong>Simulator</strong></nav>
  <div class="hero-badge"><span class="badge-dot"></span> Free Interactive Demo Â· No login required</div>
  <h1>Watch <span>8 layers of AI</span> process a real customer</h1>
  <p class="hero-sub">Pick an industry, choose a scenario, and watch X Platforms turn raw customer data into predictions and actions â live, step by step.</p>
  <div class="hero-btns">
    <a href="{{ route('client.register') }}" class="btn-fill">Book a Demo</a>
    <a href="{{ route('pricing') }}" class="btn-g">View Pricing</a>
  </div>
  <div class="hero-stats">
    <div class="hs-item"><div class="hs-n">15</div><div class="hs-l">Industries</div></div>
    <div class="hs-item"><div class="hs-n">8</div><div class="hs-l">AI Layers</div></div>
    <div class="hs-item"><div class="hs-n">97%</div><div class="hs-l">Accuracy</div></div>
    <div class="hs-item"><div class="hs-n">&lt;200ms</div><div class="hs-l">Real Processing</div></div>
  </div>
</header>

<!-- HOW TO USE -->
<div class="how-strip rv">
  <div class="hs-step"><div class="hs-num">1</div><span class="hs-text">Choose your industry</span></div>
  <div class="hs-sep"></div>
  <div class="hs-step"><div class="hs-num">2</div><span class="hs-text">Pick a customer scenario</span></div>
  <div class="hs-sep"></div>
  <div class="hs-step"><div class="hs-num">3</div><span class="hs-text">Hit Run Simulation</span></div>
  <div class="hs-sep"></div>
  <div class="hs-step"><div class="hs-num">4</div><span class="hs-text">Watch 8 layers process live</span></div>
  <div class="hs-sep"></div>
  <div class="hs-step"><div class="hs-num">5</div><span class="hs-text">See predictions & actions</span></div>
</div>

<!-- SIMULATOR -->
<section class="sim-shell">
<div class="sim-wrap rv">

  <!-- TOP BAR -->
  <div class="sim-topbar">
    <div class="stb-dot" style="background:#ff5f57"></div>
    <div class="stb-dot" style="background:#febc2e"></div>
    <div class="stb-dot" style="background:#28c840"></div>
    <div class="stb-title">x-platforms Â· customer journey simulator v2.4</div>
    <div class="stb-live" id="stbLive"><span class="live-dot"></span><span id="stbStatus">READY</span></div>
  </div>

  <!-- CONTROLS -->
  <div class="sim-controls">
    <div class="ctrl-group">
      <div class="ctrl-label">Industry</div>
      <select class="ctrl-select" id="selInd" onchange="onIndChange()">
        <option value="retail">Retail & E-Commerce</option>
        <option value="banking">Banking & Finance</option>
        <option value="healthcare">Healthcare</option>
        <option value="telecom">Telecom</option>
        <option value="travel">Travel & Hospitality</option>
        <option value="insurance">Insurance</option>
        <option value="manufacturing">Manufacturing</option>
        <option value="energy">Energy & Utilities</option>
        <option value="education">Education</option>
        <option value="realestate">Real Estate</option>
        <option value="media">Media & Entertainment</option>
        <option value="pharma">Pharma & Life Sciences</option>
        <option value="automotive">Automotive</option>
        <option value="food">Food & Beverage</option>
        <option value="construction">Construction</option>
      </select>
    </div>
    <div class="ctrl-group">
      <div class="ctrl-label">Customer Scenario</div>
      <select class="ctrl-select" id="selScen" style="min-width:220px"></select>
    </div>
    <div class="ctrl-sep"></div>
    <div class="ctrl-group">
      <div class="ctrl-label">Speed</div>
      <div class="ctrl-speed">
        <button class="spd-btn active" data-spd="1">1Ã</button>
        <button class="spd-btn" data-spd="2">2Ã</button>
        <button class="spd-btn" data-spd="3">3Ã</button>
      </div>
    </div>
    <button class="sim-reset-btn" id="resetBtn" onclick="resetSim()">Reset</button>
    <button class="sim-run-btn" id="runBtn" onclick="runSim()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><polygon points="5,3 19,12 5,21"/></svg>
      Run Simulation
    </button>
  </div>

  <!-- PROGRESS BAR -->
  <div class="sim-progress"><div class="sp-fill" id="progBar"></div></div>

  <!-- PIPELINE -->
  <div class="sim-pipe" id="simPipe"></div>

  <!-- BODY -->
  <div class="sim-body">
    <div class="sim-feed-col">
      <div class="sim-feed-scroll" id="simFeed">
        <div class="sim-empty" id="simEmpty">
          <div class="se-icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
          <div class="se-title">Ready to simulate</div>
          <div class="se-sub">Select an industry and scenario, then run the simulation to see the AI engine process a customer in real time.</div>
          <div class="se-hints">
            <div class="se-hint"><div class="sh-dot" style="background:var(--blue)"></div><div class="sh-text">Each of the 8 layers shows real data, patterns, and decisions</div></div>
            <div class="se-hint"><div class="sh-dot" style="background:var(--violet)"></div><div class="sh-text">Every industry has unique scenarios and customer profiles</div></div>
            <div class="se-hint"><div class="sh-dot" style="background:var(--emerald)"></div><div class="sh-text">The final layer shows what action was triggered and the outcome</div></div>
          </div>
        </div>
      </div>
    </div>
    <div class="sim-sidebar" id="simSidebar">
      <div class="ss-metric" id="ssData"><div class="ss-m-label">Data Processed</div><div class="ss-m-val" style="color:var(--blue)">â</div><div class="ss-m-bar"><div class="ss-m-fill" style="background:var(--blue)"></div></div><div class="ss-m-change">Waiting to start</div></div>
      <div class="ss-metric" id="ssConf"><div class="ss-m-label">Model Confidence</div><div class="ss-m-val" style="color:var(--emerald)">â</div><div class="ss-m-bar"><div class="ss-m-fill" style="background:var(--emerald)"></div></div><div class="ss-m-change">â</div></div>
      <div class="ss-metric" id="ssPred"><div class="ss-m-label">Prediction Value</div><div class="ss-m-val" style="color:var(--violet)">â</div><div class="ss-m-bar"><div class="ss-m-fill" style="background:var(--violet)"></div></div><div class="ss-m-change">â</div></div>
      <div class="ss-layers-title">Layer Status</div>
      <div id="ssLayers"></div>
    </div>
  </div>

  <!-- SUMMARY -->
  <div class="sim-summary" id="simSummary">
    <div class="sum-head">
      <div class="sum-badge"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Simulation Complete</div>
      <div class="sum-title" id="sumTitle">All 8 layers processed successfully</div>
    </div>
    <div class="sum-grid" id="sumGrid"></div>
    <div class="sum-actions">
      <button class="sa-btn sa-primary" onclick="document.getElementById('roi-section').scrollIntoView({behavior:'smooth'})">Calculate My ROI â</button>
      <button class="sa-btn sa-secondary" onclick="runSim()">Run Another Scenario</button>
      <button class="sa-btn sa-ghost" onclick="copyResults()">Share Results</button>
    </div>
  </div>

</div>
</section>

<!-- WHAT YOU'RE SEEING -->
<section class="explain-band">
<div class="eb-in rv">
  <div class="eb-left">
    <div class="stag">What you're watching</div>
    <h2>This is exactly what happens to your customers' data</h2>
    <p>Every entry in the simulation feed is a real step that X Platforms runs on every customer, every day. This isn't a demo built for show â it's the actual engine architecture processing representative scenarios.</p>
  </div>
  <div class="eb-right">
    <div class="eb-card"><div class="eb-icon" style="background:var(--blue-g);color:var(--blue)"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div><h4>Real data architecture</h4><p>Each layer represents a genuine processing stage in X Platforms' production system. Same logic, same decisions.</p></div>
    <div class="eb-card"><div class="eb-icon" style="background:rgba(52,211,153,.08);color:var(--emerald)"><svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div><h4>Industry-specific intelligence</h4><p>Each industry runs different models, different patterns, different thresholds. The simulation reflects your actual vertical.</p></div>
    <div class="eb-card"><div class="eb-icon" style="background:rgba(129,140,248,.08);color:var(--violet)"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><h4>97% prediction accuracy</h4><p>The predictions shown are calibrated to real-world outcomes, backtested across millions of customer journeys.</p></div>
    <div class="eb-card"><div class="eb-icon" style="background:rgba(251,191,36,.08);color:var(--amber)"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><h4>Under 200ms in production</h4><p>What takes seconds to display in the simulator runs in under 200ms on live customer data in the production engine.</p></div>
  </div>
</div>
</section>

<!-- DATA FLOW CANVAS -->
<section class="flow-section rv">
  <div class="stop"><div class="stag">Architecture</div><h2 class="sh">Data flowing through the intelligence engine</h2><p style="font-size:15px;color:var(--g300);max-width:460px;line-height:1.7">Watch how data particles move through each layer, transforming from raw events into actionable predictions.</p></div>
  <div class="flow-wrap">
    <canvas id="flow"></canvas>
    <div class="flow-labels" id="flowLabels"></div>
  </div>
</section>

<!-- 8 LAYERS DEEP DIVE -->
<section class="layers-section">
  <div class="stop rv"><div class="stag">Deep Dive</div><h2 class="sh">Explore each intelligence layer</h2></div>
  <div class="layer-tabs rv" id="layerTabs"></div>
  <div id="layerContents"></div>
</section>

<!-- ROI PERSONALIZER -->
<section class="roi-section" id="roi-section">
<div class="roi-in">
  <div class="stop rv" style="text-align:center;max-width:560px;margin:0 auto 40px"><div class="stag" style="justify-content:center">Your Numbers</div><h2 class="sh" style="margin:0 auto 12px">What would this mean for your business?</h2><p style="font-size:15px;color:var(--g300);line-height:1.7">Adjust the sliders to see projected impact based on your actual metrics.</p></div>
  <div class="roi-card rv">
    <div class="roi-left">
      <h2>Personalise your<br>projected impact</h2>
      <p>Based on average results across similar businesses. Every deployment is different â these are benchmarks, not guarantees.</p>
      <div class="rc-inputs">
        <div class="rc-group"><div class="rc-label-row"><span class="rc-label">Monthly Revenue</span><span class="rc-val" id="rvRevDisplay">$500K</span></div><input type="range" id="rvRev" min="50" max="10000" value="500" step="50" oninput="calcROI()"></div>
        <div class="rc-group"><div class="rc-label-row"><span class="rc-label">Total Customers</span><span class="rc-val" id="rvCustDisplay">10,000</span></div><input type="range" id="rvCust" min="1000" max="1000000" value="10000" step="1000" oninput="calcROI()"></div>
        <div class="rc-group"><div class="rc-label-row"><span class="rc-label">Current Churn Rate</span><span class="rc-val" id="rvChurnDisplay">8%</span></div><input type="range" id="rvChurn" min="1" max="35" value="8" oninput="calcROI()"></div>
        <div class="rc-group"><div class="rc-label-row"><span class="rc-label">Data Sources</span><span class="rc-val" id="rvSrcDisplay">5</span></div><input type="range" id="rvSrc" min="1" max="20" value="5" oninput="calcROI()"></div>
      </div>
    </div>
    <div class="roi-result">
      <div class="rr-label">Projected Annual Impact</div>
      <div class="rr-val" id="roiTotal">$1.92M</div>
      <div class="rr-sub">Based on average customer results</div>
      <div class="rr-breakdown">
        <div class="rrb"><div class="rrb-n" id="roiChurn">$720K</div><div class="rrb-l">Churn Reduction</div></div>
        <div class="rrb"><div class="rrb-n" id="roiGrowth">$480K</div><div class="rrb-l">Revenue Growth</div></div>
        <div class="rrb"><div class="rrb-n" id="roiCross">$360K</div><div class="rrb-l">Cross-Sell</div></div>
        <div class="rrb"><div class="rrb-n" id="roiSave">$360K</div><div class="rrb-l">Cost Savings</div></div>
      </div>
    </div>
  </div>
</div>
</section>

<!-- USE CASES -->
<section class="uc-section">
  <div class="stop rv"><div class="stag">Use Cases</div><h2 class="sh">What the simulator reveals</h2><p style="font-size:15px;color:var(--g300);max-width:460px;line-height:1.7">Run simulations across different industries and scenarios to explore these high-impact intelligence patterns.</p></div>
  <div class="uc-grid rv">
    <div class="uc" onclick="setAndRun('retail','0')"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div><h3>Churn Prevention</h3><p>See how the AI identifies customers at risk weeks before they cancel and triggers personalised retention actions automatically.</p><div class="uc-arrow"><span>Try Retail scenario</span><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div></div>
    <div class="uc" onclick="setAndRun('banking','0')"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div><h3>Revenue Prediction</h3><p>Watch the engine score each customer's purchase propensity and lifetime value, generating revenue forecasts with confidence intervals.</p><div class="uc-arrow"><span>Try Banking scenario</span><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div></div>
    <div class="uc" onclick="setAndRun('healthcare','0')"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div><h3>Engagement Recovery</h3><p>Observe how behavioural mapping detects disengagement signals and deploys personalised interventions before a relationship breaks.</p><div class="uc-arrow"><span>Try Healthcare scenario</span><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div></div>
    <div class="uc" onclick="setAndRun('retail','1')"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 16l4-6 4 3 5-7"/></svg></div><h3>Cross-Sell Intelligence</h3><p>See the prediction engine identify which customers are ready to buy more, what to offer them, and the optimal timing window.</p><div class="uc-arrow"><span>Try Cross-sell scenario</span><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div></div>
    <div class="uc" onclick="setAndRun('travel','0')"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M4 4v5h5M20 20v-5h-5"/><path d="M20.49 9A9 9 0 005.64 5.64L4 4m16 16l-1.64-1.64A9 9 0 013.51 15"/></svg></div><h3>Continuous Learning</h3><p>Watch Layer 8 feed outcome data back into the model, demonstrating how predictions sharpen with every customer interaction.</p><div class="uc-arrow"><span>Try Travel scenario</span><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div></div>
    <div class="uc" onclick="setAndRun('education','0')"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><h3>Data Unification</h3><p>See fragmented records across 4+ platforms merge into a single golden profile â and watch how accuracy improves with every source added.</p><div class="uc-arrow"><span>Try Education scenario</span><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div></div>
  </div>
</section>

<!-- CASE STUDY TEASER -->
<section class="cs-teaser">
<div class="cs-teaser-in">
  <div class="rv"><div class="stag">Proven Results</div><h2 class="sh">Companies using X Platforms right now</h2><p style="font-size:15px;color:var(--g300);max-width:460px;line-height:1.7">The simulator shows the architecture. These are the outcomes.</p></div>
  <div class="cs-tease-grid rv">
    <div class="cst"><div class="cst-logo" style="background:linear-gradient(135deg,#4f8fff,#38bdf8)">M</div><h3>MockMaster</h3><div class="cst-ind">EdTech Â· Online Test Prep</div><div class="cst-stat">Discovered the "Week 2 skip" churn pattern that was invisible before X Platforms. Deployed automated interventions and recovered $4.8M in year one.</div><div class="cst-metric"><div><div class="cm-n">41%</div><div class="cm-l">Churn Drop</div></div><div><div class="cm-n">$4.8M</div><div class="cm-l">Recovered</div></div></div><a href="{{ route('case-studies') }}" class="cst-link">Read case study â</a></div>
    <div class="cst"><div class="cst-logo" style="background:linear-gradient(135deg,#818cf8,#f472b6)">S</div><h3>ScoreMentor</h3><div class="cst-ind">SaaS Â· Learning Management</div><div class="cst-stat">Used expansion intelligence to identify which accounts were ready to upsell each week. MRR grew from $280K to $952K in 12 months.</div><div class="cst-metric"><div><div class="cm-n">3.4Ã</div><div class="cm-l">MRR Growth</div></div><div><div class="cm-n">28%</div><div class="cm-l">CAC Drop</div></div></div><a href="{{ route('case-studies') }}" class="cst-link">Read case study â</a></div>
    <div class="cst"><div class="cst-logo" style="background:linear-gradient(135deg,#34d399,#38bdf8)">O</div><h3>OneAustralia</h3><div class="cst-ind">eLearning Â· Course Marketplace</div><div class="cst-stat">The "40/7 rule" â discovered by X Platforms â transformed their entire onboarding. Completion rates tripled. Revenue per student doubled.</div><div class="cst-metric"><div><div class="cm-n">3Ã</div><div class="cm-l">Completions</div></div><div><div class="cm-n">$5.4M</div><div class="cm-l">New Revenue</div></div></div><a href="{{ route('case-studies') }}" class="cst-link">Read case study â</a></div>
  </div>
</div>
</section>

<!-- FAQ -->
<section class="faq-section">
<div class="faq-in">
  <div class="rv" style="text-align:center"><div class="stag" style="justify-content:center">Questions</div><h2 class="sh" style="margin:0 auto 8px">About the simulator</h2></div>
  <div class="faq-list rv">
    <div class="faq-item open"><div class="faq-q">Is this the real AI engine or just a demo?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>The simulator demonstrates the actual architecture and decision logic of X Platforms' 8-layer engine using realistic, industry-calibrated scenarios. The processing steps, pattern discoveries, and prediction outputs reflect real behaviour of the production system. For a live demo running on your actual customer data, book a personalised session with our team.</p></div></div>
    <div class="faq-item"><div class="faq-q">Can I try different industries and scenarios?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Yes â all 15 industries are available and each has multiple customer scenarios. Switch industry, switch scenario, and run as many simulations as you like. Each combination produces unique insights, patterns, and predictions relevant to that vertical.</p></div></div>
    <div class="faq-item"><div class="faq-q">How accurate are the predictions shown?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>The simulator uses representative data calibrated to real-world outcomes. In production, X Platforms achieves 97% average accuracy across churn prediction, purchase propensity, and customer lifetime value scoring â verified through live A/B testing and backtesting across millions of customer journeys.</p></div></div>
    <div class="faq-item"><div class="faq-q">How long does real implementation take?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Most businesses are live within 2â4 weeks. One-click connectors handle data source integration with no code required. Pre-trained industry models start generating predictions within hours of connecting your first data source. You'll see results before the month is out.</p></div></div>
    <div class="faq-item"><div class="faq-q">Can I simulate with my own business data?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>The interactive simulator uses representative scenarios. To see predictions generated from your actual customer data â your real churn risks, your real purchase propensity scores, your real revenue opportunities â book a 30-day proof of concept. No credit card required, and results are typically visible within the first two weeks.</p></div></div>
    <div class="faq-item"><div class="faq-q">What happens after I run a simulation?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>After each simulation you'll see a summary of all 8 layers, the key predictions generated, and the action triggered. You can then use the ROI calculator on this page to personalise the projected impact for your business, or jump straight to booking a live demo to see it run on your real data.</p></div></div>
  </div>
</div>
</section>

<!-- CTA -->
<section class="cta">
  <h2 class="rv">You've seen the AI work.<br>Now let it work for <span>your customers.</span></h2>
  <p class="rv">Connect your data, get your first predictions, and see measurable results â all within 30 days. No credit card required.</p>
  <div class="cta-btns rv">
    <a href="#" class="btn-fill">Start 30-Day Free Trial</a>
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
@verbatim
<script>
// ââ NEURAL NET ââ
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

const ncv=document.getElementById('neural'),nctx=ncv.getContext('2d');let NW,NH,NND=[];
function nrsz(){NW=ncv.width=innerWidth;NH=ncv.height=innerHeight}addEventListener('resize',nrsz);nrsz();
for(let i=0;i<Math.min(60,Math.floor(NW*NH/22000));i++)NND.push({x:Math.random()*NW,y:Math.random()*NH,vx:(Math.random()-.5)*.28,vy:(Math.random()-.5)*.28,r:Math.random()*1.4+.6,p:Math.random()*6.28});
let NMX=-1e3,NMY=-1e3;document.addEventListener('mousemove',e=>{NMX=e.clientX;NMY=e.clientY});
(function ndraw(){nctx.clearRect(0,0,NW,NH);NND.forEach((n,i)=>{n.x+=n.vx;n.y+=n.vy;n.p+=.01;if(n.x<0||n.x>NW)n.vx*=-1;if(n.y<0||n.y>NH)n.vy*=-1;NND.forEach((m,j)=>{if(j<=i)return;const d=Math.hypot(n.x-m.x,n.y-m.y);if(d<150){nctx.beginPath();nctx.moveTo(n.x,n.y);nctx.lineTo(m.x,m.y);nctx.strokeStyle=`rgba(79,143,255,${(1-d/150)*.09})`;nctx.lineWidth=.5;nctx.stroke()}});const g=Math.hypot(n.x-NMX,n.y-NMY)<160?(1-Math.hypot(n.x-NMX,n.y-NMY)/160)*.4:0;nctx.beginPath();nctx.arc(n.x,n.y,n.r+Math.sin(n.p)*.35,0,6.28);nctx.fillStyle=`rgba(79,143,255,${.2+g})`;nctx.fill()});requestAnimationFrame(ndraw)})();

// ââ LAYER CONFIG ââ
const LAYERS=[
  {name:'Ingest',short:'L1',color:'var(--L1)',icon:'<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>'},
  {name:'Unify',short:'L2',color:'var(--L2)',icon:'<path d="M4 7h16M4 12h16M4 17h16"/>'},
  {name:'Map',short:'L3',color:'var(--L3)',icon:'<circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>'},
  {name:'Detect',short:'L4',color:'var(--L4)',icon:'<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>'},
  {name:'Predict',short:'L5',color:'var(--L5)',icon:'<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>'},
  {name:'Plan',short:'L6',color:'var(--L6)',icon:'<path d="M3 3v18h18"/><path d="M7 16l4-6 4 3 5-7"/>'},
  {name:'Execute',short:'L7',color:'var(--L7)',icon:'<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'},
  {name:'Learn',short:'L8',color:'var(--L8)',icon:'<path d="M4 4v5h5M20 20v-5h-5"/><path d="M20.49 9A9 9 0 005.64 5.64L4 4m16 16l-1.64-1.64A9 9 0 013.51 15"/>'}
];
const TAG_CLS=['tag-l1','tag-l2','tag-l3','tag-l4','tag-l5','tag-l6','tag-l7','tag-l8'];

// Build pipeline
const pipeEl=document.getElementById('simPipe');
LAYERS.forEach((l,i)=>{pipeEl.innerHTML+=`<div class="sp" data-i="${i}"><div class="sp-done-check"><svg viewBox="0 0 24 24" stroke="white" fill="none" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div><div class="sp-num">0${i+1}</div><div class="sp-icon"><svg viewBox="0 0 24 24" stroke="none" fill="none" stroke-width="1.8">${l.icon}</svg></div><div class="sp-name">${l.name}</div><div class="sp" style="display:none"></div><div style="position:absolute;bottom:0;left:0;right:0;height:2px;background:${l.color}"></div></div>`});
// Fix - rebuild properly
pipeEl.innerHTML='';
LAYERS.forEach((l,i)=>{
  const d=document.createElement('div');
  d.className='sp';d.dataset.i=i;
  d.innerHTML=`<div class="sp-done-check"><svg viewBox="0 0 24 24" stroke="white" fill="none" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div><div class="sp-num">0${i+1}</div><div class="sp-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.8">${l.icon}</svg></div><div class="sp-name">${l.name}</div>`;
  d.style.setProperty('--lc',l.color);
  d.querySelector('::before');
  // set border color on active via class
  pipeEl.appendChild(d);
});

// Build sidebar layers
const ssL=document.getElementById('ssLayers');
LAYERS.forEach((l,i)=>{ssL.innerHTML+=`<div class="ss-layer-row" data-i="${i}"><span class="ssl-dot" style="background:${l.color}"></span><span class="ssl-name">L${i+1} ${l.name}</span><span class="ssl-status">IDLE</span></div>`});

// ââ SIM DATA ââ
const SCENARIOS={
retail:{
  label:'Retail & E-Commerce',
  scenarios:['At-risk Cart Abandoner','High-Value Cross-Sell','Win-Back Lapsed Buyer'],
  data:[
    // Scenario 0: Cart Abandoner
    [[
      {title:'Streaming Customer Touchpoints',desc:'Pulling live event data from all connected retail platforms.',data:'<span class="fdd-key">Web sessions:</span> <span class="fdd-val">3,201</span> Â· <span class="fdd-key">Cart events:</span> <span class="fdd-val">847</span> Â· <span class="fdd-key">POS txns:</span> <span class="fdd-val">2,100</span>\n<span class="fdd-key">Email opens:</span> <span class="fdd-val">12,400</span> Â· <span class="fdd-key">Latency:</span> <span class="fdd-pos">42ms</span>\n<span class="fdd-key">Total events:</span> <span class="fdd-val">18,548</span> in last 24hrs',insight:'All 5 data sources streaming. Historical 18-month dataset loaded for model training.'},
      {title:'Identity Resolution Complete',desc:'Cross-device and cross-channel identities merged into unified profiles.',data:'<span class="fdd-key">Raw records:</span> <span class="fdd-val">4,820</span> â <span class="fdd-pos">2,891 golden profiles</span>\n<span class="fdd-key">Match method:</span> <span class="fdd-val">Email hash + device fingerprint</span>\n<span class="fdd-key">Duplicate rate:</span> <span class="fdd-neg">58.4%</span> â <span class="fdd-pos">0%</span>',insight:'Customer #C-4821 identified across web, mobile, and email â single golden record created.'},
      {title:'Behavioural Journey Mapped',desc:'Rich interaction graph built from every micro-event for this customer.',data:'<span class="fdd-key">Journey nodes:</span> <span class="fdd-val">24,103 mapped</span>\n<span class="fdd-key">Pattern:</span> <span class="fdd-neg">Cart abandoned at checkout step 3</span>\n<span class="fdd-key">Sentiment:</span> <span class="fdd-val">Neutral â Positive (product page) â Friction (payment)</span>\n<span class="fdd-key">Session depth:</span> <span class="fdd-val">6 pages, 4.2 min</span>',insight:'User reached cart with $58 of items â stopped at payment form. No error triggered, likely UX friction.'},
      {title:'Pattern Cluster Matched',desc:'ML model matches this behaviour against 2.4M historical patterns.',data:'<span class="fdd-key">Pattern matched:</span> <span class="fdd-hi">"Price-sensitive high-intent abandoner"</span>\n<span class="fdd-key">Cluster size:</span> <span class="fdd-val">12,847 similar journeys</span>\n<span class="fdd-key">Anomaly:</span> <span class="fdd-neg">Mobile Safari cart abandonment +23% vs desktop</span>\n<span class="fdd-key">Correlation:</span> <span class="fdd-pos">Email open within 2hrs â 67% higher recovery</span>',insight:'This exact pattern has a documented conversion response: time-sensitive offer within 2 hours recovers 34% of carts.'},
      {title:'Purchase Prediction Generated',desc:'Forward-looking score for this specific customer profile.',data:'<span class="fdd-key">Purchase probability:</span> <span class="fdd-pos">78%</span> within 48hrs (confidence: <span class="fdd-pos">94.2%</span>)\n<span class="fdd-key">Customer LTV:</span> <span class="fdd-val">$342</span> Â± $28\n<span class="fdd-key">Churn risk:</span> <span class="fdd-pos">Low (12%)</span>\n<span class="fdd-key">Price sensitivity:</span> <span class="fdd-neg">High â $10 threshold triggers action</span>',insight:'78% purchase probability is above the 65% activation threshold. Next-best-action: personalised discount offer.'},
      {title:'Strategy: Cart Recovery + Upsell',desc:'AI-generated playbook tailored to this customer and pattern.',data:'<span class="fdd-key">Strategy:</span> <span class="fdd-hi">"Price-Sensitive Recovery"</span>\n<span class="fdd-key">Offer:</span> <span class="fdd-val">10% discount + free shipping on orders over $50</span>\n<span class="fdd-key">Upsell:</span> <span class="fdd-val">Related item (+$18 AOV lift projected)</span>\n<span class="fdd-key">Projected ROI:</span> <span class="fdd-pos">8.4Ã on offer cost</span>',insight:'Free shipping threshold set at $50 against current cart of $58 â no price reduction needed. Margin preserved.'},
      {title:'Personalised Offer Triggered',desc:'Real-time actions deployed across optimal channels.',data:'<span class="fdd-key">Email sent:</span> <span class="fdd-val">Cart recovery with dynamic product imagery</span>\n<span class="fdd-key">Push notification:</span> <span class="fdd-val">"Your items are waiting â free shipping added"</span>\n<span class="fdd-key">Ad retargeting:</span> <span class="fdd-pos">Paused (save spend â high purchase probability)</span>\n<span class="fdd-key">Time to action:</span> <span class="fdd-pos">1.2 seconds from prediction</span>',insight:'Ad spend paused automatically â no point paying for retargeting when recovery email has 78% success probability.'},
      {title:'Outcome Recorded & Model Updated',desc:'Purchase confirmed. All models update from this outcome.',data:'<span class="fdd-key">Outcome:</span> <span class="fdd-pos">Purchase confirmed â $58.40</span> (12hrs after offer)\n<span class="fdd-key">Model accuracy update:</span> <span class="fdd-pos">Cart recovery model â 96.1% (+0.3%)</span>\n<span class="fdd-key">New pattern stored:</span> <span class="fdd-val">Safari mobile friction â UX team flagged</span>\n<span class="fdd-key">Next prediction:</span> <span class="fdd-val">Cross-sell in 14 days â 64% probability</span>',insight:'Every outcome makes the next prediction more accurate. This customer now has a cross-sell window flagged.'}
    ]],
    // Scenario 1: Cross-Sell
    [[
      {title:'Streaming Customer Touchpoints',desc:'Pulling purchase history, browse, and email engagement.',data:'<span class="fdd-key">Purchases (90 days):</span> <span class="fdd-val">3 orders, AOV $84</span>\n<span class="fdd-key">Browse sessions:</span> <span class="fdd-val">14 in last 30 days</span>\n<span class="fdd-key">Email CTR:</span> <span class="fdd-pos">24% (4Ã avg)</span>',insight:'High engagement signals across all channels. This customer is in an active buying cycle.'},
      {title:'Identity Unified',desc:'Full cross-channel profile assembled.',data:'<span class="fdd-key">Channels linked:</span> <span class="fdd-val">Web + App + Email + POS</span>\n<span class="fdd-key">Profile completeness:</span> <span class="fdd-pos">94%</span>',insight:'Purchase history, preferences, and channel behaviour all visible in one profile.'},
      {title:'Purchase Pattern Mapped',desc:'Category affinity and buying cycle frequency charted.',data:'<span class="fdd-key">Primary category:</span> <span class="fdd-val">Homewares (72% of purchases)</span>\n<span class="fdd-key">Avg reorder cycle:</span> <span class="fdd-val">28 days</span>\n<span class="fdd-key">Browse-to-buy ratio:</span> <span class="fdd-pos">1:4 sessions (high intent)</span>',insight:'Customer is 3 days into their typical 28-day reorder window. High cross-sell receptivity signal.'},
      {title:'Cross-Category Correlation Found',desc:'Customers with this profile buy across 2 additional categories at high rates.',data:'<span class="fdd-key">Correlation:</span> <span class="fdd-hi">Homewares buyers â Kitchen â 68% cross-buy within 60 days</span>\n<span class="fdd-key">Affinity score:</span> <span class="fdd-pos">Kitchen: 0.82 Â· Bedding: 0.71 Â· Outdoor: 0.44</span>',insight:'The Kitchen category cross-sell is highly validated â 68% conversion rate in this exact customer cohort.'},
      {title:'Cross-Sell Prediction Generated',desc:'Specific product recommendation with conversion probability.',data:'<span class="fdd-key">Recommended product:</span> <span class="fdd-hi">Kitchen ceramic set (SKU-4821)</span>\n<span class="fdd-key">Conversion probability:</span> <span class="fdd-pos">74%</span> within 7 days\n<span class="fdd-key">Predicted AOV:</span> <span class="fdd-val">$127 (+$51 vs baseline)</span>',insight:'74% conversion probability is above trigger threshold. Personalised recommendation ready to deploy.'},
      {title:'Strategy: Targeted Cross-Sell',desc:'Personalised offer timed to reorder window.',data:'<span class="fdd-key">Tactic:</span> <span class="fdd-val">Featured recommendation email with 15% first-category discount</span>\n<span class="fdd-key">Channel:</span> <span class="fdd-val">Email (highest CTR for this customer)</span>\n<span class="fdd-key">Projected revenue lift:</span> <span class="fdd-pos">$51 per customer</span>',insight:'Discount applied only to the new category â no margin loss on existing purchases.'},
      {title:'Recommendation Email Sent',desc:'Personalised email deployed with dynamic product block.',data:'<span class="fdd-key">Subject:</span> <span class="fdd-val">"Complete your kitchen â you\'ll love these"</span>\n<span class="fdd-key">Dynamic block:</span> <span class="fdd-val">SKU-4821 + 2 complementary items</span>\n<span class="fdd-key">Send time:</span> <span class="fdd-pos">Optimised for 9:14am (peak open time for this customer)</span>',insight:'Send time personalised to this customer\'s historical open pattern. Opens at 9am on weekdays.'},
      {title:'Outcome: Cross-Sell Confirmed',desc:'Purchase recorded and cross-category model updated.',data:'<span class="fdd-key">Outcome:</span> <span class="fdd-pos">Purchased SKU-4821 â $124</span> (day 3)\n<span class="fdd-key">Second item added to cart:</span> <span class="fdd-pos">+$38 unplanned addition</span>\n<span class="fdd-key">Total order:</span> <span class="fdd-val">$162 (AOV +93% vs baseline)</span>\n<span class="fdd-key">Model update:</span> <span class="fdd-pos">Kitchen cross-sell accuracy â 97.2%</span>',insight:'Customer added an unplanned item â a signal the recommendation built trust. Next cross-sell window: Bedding in 30 days.'}
    ]],
    // Scenario 2: Win-Back
    [[
      {title:'Lapsed Customer Data Ingested',desc:'Pulling data for customer with no purchase in 90+ days.',data:'<span class="fdd-key">Last purchase:</span> <span class="fdd-neg">94 days ago</span>\n<span class="fdd-key">Email engagement:</span> <span class="fdd-neg">0 opens in last 60 days</span>\n<span class="fdd-key">App sessions:</span> <span class="fdd-neg">0 in last 45 days</span>',insight:'All engagement signals have gone dark. Classic lapsed customer pattern beginning to emerge.'},
      {title:'Historical Profile Reconstructed',desc:'Full purchase and engagement history unified.',data:'<span class="fdd-key">Lifetime purchases:</span> <span class="fdd-val">8 orders over 18 months</span>\n<span class="fdd-key">Average order:</span> <span class="fdd-val">$96</span>\n<span class="fdd-key">Best category:</span> <span class="fdd-val">Activewear (6/8 orders)</span>',insight:'Strong historical value customer. Lifetime spend $768. Worth a win-back investment.'},
      {title:'Lapse Behaviour Mapped',desc:'Drop-off timeline and trigger event identified.',data:'<span class="fdd-key">Lapse trigger:</span> <span class="fdd-neg">Negative support interaction (day -97)</span>\n<span class="fdd-key">Behaviour post-trigger:</span> <span class="fdd-neg">3 email opens, no clicks, then silence</span>',insight:'Lapse followed a support ticket â indicates experience issue, not product fatigue. Higher win-back probability.'},
      {title:'Win-Back Pattern Matched',desc:'Historical win-back cohort analysis applied.',data:'<span class="fdd-key">Pattern:</span> <span class="fdd-hi">"Service-triggered lapse" â 41% win-back rate vs 12% for price-triggered</span>\n<span class="fdd-key">Optimal re-engagement:</span> <span class="fdd-val">Day 90â105 window</span>',insight:'Customer is at day 94 â inside the optimal win-back window. Success rate drops sharply after day 120.'},
      {title:'Win-Back Prediction Scored',desc:'Probability and optimal offer calculated.',data:'<span class="fdd-key">Win-back probability:</span> <span class="fdd-pos">41%</span> with personalised apology + offer\n<span class="fdd-key">Expected reactivation value:</span> <span class="fdd-val">$580 (6 orders projected)</span>\n<span class="fdd-key">Break-even offer cost:</span> <span class="fdd-val">$24 (20% discount on $120 order)</span>',insight:'Expected lifetime value of a reactivated customer is $580 â making a $24 offer investment clearly justified.'},
      {title:'Strategy: Service Recovery + Incentive',desc:'Empathetic win-back sequence designed.',data:'<span class="fdd-key">Email 1:</span> <span class="fdd-val">Personal apology from customer service lead + 20% code</span>\n<span class="fdd-key">Email 2 (if no open):</span> <span class="fdd-val">Day +5: "We miss you" with new Activewear arrivals</span>\n<span class="fdd-key">Email 3 (if no click):</span> <span class="fdd-val">Day +12: Final offer â code extended 7 days</span>',insight:'3-touch sequence timed around non-engagement. Stops automatically if customer opens or clicks.'},
      {title:'Win-Back Sequence Triggered',desc:'Personalised emails deployed with timing logic.',data:'<span class="fdd-key">Email 1 sent:</span> <span class="fdd-val">Service acknowledgment + 20% code "WELCOME20"</span>\n<span class="fdd-key">Dynamic content:</span> <span class="fdd-val">New Activewear arrivals matching past purchase category</span>',insight:'Email personalised with their most-purchased category â Activewear â and new arrivals since last visit.'},
      {title:'Reactivation Confirmed',desc:'Customer returned and purchased. Models updated.',data:'<span class="fdd-key">Outcome:</span> <span class="fdd-pos">Email 1 opened day 2, purchased day 4 â $114</span>\n<span class="fdd-key">Discount used:</span> <span class="fdd-val">WELCOME20 (-$22.80)</span>\n<span class="fdd-key">Net revenue:</span> <span class="fdd-pos">$91.20</span>\n<span class="fdd-key">Model update:</span> <span class="fdd-pos">Service-lapse win-back model â 43% accuracy (+2%)</span>',insight:'Win-back sequence 2 and 3 automatically cancelled. Customer flagged for 30-day follow-up NPS survey.'}
    ]]
  ]
},
banking:{label:'Banking & Finance',scenarios:['High-Value Churn Risk','Savings Product Upsell'],data:[[[
  {title:'Transaction & Engagement Data Ingested',desc:'Streaming from core banking, mobile app, branch CRM, and call centre.',data:'<span class="fdd-key">Transactions (30d):</span> <span class="fdd-val">47 Â· avg value $340</span>\n<span class="fdd-key">Mobile logins:</span> <span class="fdd-neg">2/week (was 8/week 60 days ago)</span>\n<span class="fdd-key">Call centre:</span> <span class="fdd-neg">1 complaint (billing dispute)</span>',insight:'Login frequency decline is the most reliable early churn signal in banking. This customer has dropped 75%.'},
  {title:'Multi-System Identity Unified',desc:'Digital, branch, and phone identities linked to one profile.',data:'<span class="fdd-key">Systems linked:</span> <span class="fdd-val">Mobile banking + Web + Branch CRM + Support</span>\n<span class="fdd-key">Account value:</span> <span class="fdd-val">$124,000 AUM</span>',insight:'$124K account under management. This is a high-value relationship â churn cost is significant.'},
  {title:'Engagement Decline Trajectory Mapped',desc:'Engagement health score over 90 days plotted.',data:'<span class="fdd-key">Engagement score:</span> <span class="fdd-neg">8.2 â 3.1 (90 days)</span>\n<span class="fdd-key">Trigger event:</span> <span class="fdd-neg">Billing dispute (unresolved, day -18)</span>\n<span class="fdd-key">Competitor signal:</span> <span class="fdd-neg">3 competitor rate comparison pages visited</span>',insight:'Competitor rate page visits combined with complaint = classic pre-switch behaviour. Window is closing.'},
  {title:'Competitive Switch Pattern Detected',desc:'Pattern matching against 840K historical banking churn events.',data:'<span class="fdd-key">Pattern:</span> <span class="fdd-hi">"Complaint + competitor research" â 5.2Ã churn rate</span>\n<span class="fdd-key">Historical outcomes:</span> <span class="fdd-val">78% of accounts with this pattern switch within 45 days</span>',insight:'This exact pattern has been seen 12,400 times. Without intervention, 78% switch. With relationship manager contact: 62% retention.'},
  {title:'Churn Risk Score: Critical',desc:'Account-level prediction with revenue at risk.',data:'<span class="fdd-key">Churn probability:</span> <span class="fdd-neg">74%</span> within 45 days (confidence: <span class="fdd-pos">91.8%</span>)\n<span class="fdd-key">Revenue at risk:</span> <span class="fdd-neg">$7,200/year</span>\n<span class="fdd-key">Intervention window:</span> <span class="fdd-val">Optimal: next 7â14 days</span>',insight:'$7,200 annual revenue at risk. Maximum intervention investment justified: $720 (10Ã ROI break-even).'},
  {title:'Strategy: Relationship Save Programme',desc:'Personalised retention plan generated for this account.',data:'<span class="fdd-key">Action 1:</span> <span class="fdd-val">Assign relationship manager Sarah T. â immediate briefing</span>\n<span class="fdd-key">Action 2:</span> <span class="fdd-val">Resolve billing dispute proactively â credit $45</span>\n<span class="fdd-key">Action 3:</span> <span class="fdd-val">Offer premium savings rate 4.8% (vs current 4.2%)</span>\n<span class="fdd-key">Retention probability with intervention:</span> <span class="fdd-pos">62%</span>',insight:'Dispute resolution comes first â cannot offer upsell while grievance is unresolved. Sequence matters.'},
  {title:'Intervention Deployed',desc:'RM briefed and outreach initiated.',data:'<span class="fdd-key">RM notification:</span> <span class="fdd-val">Full context brief + recommended talking points sent</span>\n<span class="fdd-key">Billing credit:</span> <span class="fdd-pos">$45 applied automatically â customer notified by SMS</span>\n<span class="fdd-key">Rate upgrade offer:</span> <span class="fdd-val">Prepared for RM conversation</span>',insight:'Billing credit sent before RM call â defuses tension and signals the bank is proactively addressing the issue.'},
  {title:'Customer Retained',desc:'Outcome recorded. Churn model updated.',data:'<span class="fdd-key">Outcome:</span> <span class="fdd-pos">Customer retained â accepted premium rate offer (day 8)</span>\n<span class="fdd-key">NPS post-intervention:</span> <span class="fdd-pos">3.1 â 8.4</span>\n<span class="fdd-key">Account value change:</span> <span class="fdd-pos">+$18,000 deposit (new savings product)</span>\n<span class="fdd-key">Model update:</span> <span class="fdd-pos">Banking churn model accuracy â 93.4% (+0.6%)</span>',insight:'Customer added $18K to savings after retention â net positive outcome far exceeding cost of intervention.'}
],[
  {title:'Account Behaviour Data Ingested',desc:'Streaming savings, spending, and engagement data.',data:'<span class="fdd-key">Current products:</span> <span class="fdd-val">Transaction account, basic savings</span>\n<span class="fdd-key">Monthly surplus:</span> <span class="fdd-val">Avg $2,400 sitting in 0.1% savings</span>',insight:'$2,400/month earning near-zero interest. This customer is losing money to inflation without knowing it.'},
  {title:'Financial Profile Assembled',desc:'Full financial behaviour mapped across all accounts.',data:'<span class="fdd-key">Income:</span> <span class="fdd-val">$9,800/month (payroll detected)</span>\n<span class="fdd-key">Savings rate:</span> <span class="fdd-val">24% of income â disciplined saver</span>\n<span class="fdd-key">Investment products:</span> <span class="fdd-neg">None</span>',insight:'High savings rate with no investment products = significant cross-sell opportunity.'},
  {title:'Financial Journey Mapped',desc:'Life stage and financial behaviour trajectory charted.',data:'<span class="fdd-key">Life stage signals:</span> <span class="fdd-val">Age 34, recent address change, increased savings rate</span>\n<span class="fdd-key">Goal inference:</span> <span class="fdd-val">Likely property purchase in 2â3 years</span>',insight:'Address change + increased savings = property planning signal. Home loan pre-approval is a relevant next product.'},
  {title:'Product Affinity Pattern Identified',desc:'Similar customers show strong propensity for premium savings and investment products.',data:'<span class="fdd-key">Cohort:</span> <span class="fdd-val">Income $8Kâ$12K, savings rate >20%, no investment</span>\n<span class="fdd-key">Product adoption rate:</span> <span class="fdd-pos">High-yield savings: 68% Â· Term deposit: 44%</span>',insight:'This exact cohort converts to premium savings at 68%. Recommendation has strong historical validation.'},
  {title:'Upsell Opportunity Scored',desc:'Product recommendations ranked by predicted conversion and customer benefit.',data:'<span class="fdd-key">Recommendation 1:</span> <span class="fdd-hi">High-yield savings â 4.8% (conversion: 72%)</span>\n<span class="fdd-key">Recommendation 2:</span> <span class="fdd-val">Term deposit 12mo â 5.1% (conversion: 48%)</span>\n<span class="fdd-key">Incremental interest earned by customer:</span> <span class="fdd-pos">+$1,140/year</span>',insight:'Framing this as customer benefit ($1,140 extra interest) increases conversion significantly over rate-only messaging.'},
  {title:'Strategy: Financial Wellness Upsell',desc:'Relationship-led upsell approach designed around customer benefit.',data:'<span class="fdd-key">Angle:</span> <span class="fdd-val">"Your savings are working too hard to earn so little"</span>\n<span class="fdd-key">Channel:</span> <span class="fdd-val">In-app notification + personalised email</span>\n<span class="fdd-key">Urgency:</span> <span class="fdd-val">Rate offer valid 30 days</span>',insight:'No discount needed â this is a genuinely better product for the customer. Framing is financial wellness, not a sales pitch.'},
  {title:'Personalised Offer Deployed',desc:'In-app notification and email sent with custom interest projection.',data:'<span class="fdd-key">In-app:</span> <span class="fdd-val">"You could earn $1,140 more this year â see how"</span>\n<span class="fdd-key">Email:</span> <span class="fdd-val">Personalised interest calculator showing current vs potential earnings</span>',insight:'Calculator email personalised with their exact balance and surplus â makes the $1,140 feel tangible and real.'},
  {title:'Upsell Accepted',desc:'Customer opened high-yield account. Models updated.',data:'<span class="fdd-key">Outcome:</span> <span class="fdd-pos">High-yield savings opened â $34,000 transferred</span>\n<span class="fdd-key">AUM increase:</span> <span class="fdd-pos">+$34,000</span>\n<span class="fdd-key">Revenue impact:</span> <span class="fdd-pos">+$612/year net interest margin</span>\n<span class="fdd-key">Model update:</span> <span class="fdd-pos">Savings upsell model â 74.1% accuracy</span>',insight:'Next product window: term deposit in 6 months when this customer\'s financial planning horizon comes into focus.'}
]]]},
healthcare:{label:'Healthcare',scenarios:['Care Gap Prevention','Patient Re-engagement'],data:[[[
  {title:'Patient Interaction Data Ingested',desc:'Collecting from patient portal, EHR, call centre, and SMS systems.',data:'<span class="fdd-key">Portal logins:</span> <span class="fdd-neg">0 in last 21 days (was 4/week)</span>\n<span class="fdd-key">Appointment:</span> <span class="fdd-val">Post-procedure check-up due in 8 days</span>\n<span class="fdd-key">SMS response:</span> <span class="fdd-neg">No response to appointment reminder</span>',insight:'All engagement signals dropped after procedure. Classic care gap precursor pattern.'},
  {title:'Patient Profile Unified',desc:'Records across 3 clinic systems merged into single patient timeline.',data:'<span class="fdd-key">Records merged:</span> <span class="fdd-val">3 clinic systems â 1 patient timeline</span>\n<span class="fdd-key">Care history:</span> <span class="fdd-val">Procedure completed 18 days ago â follow-up critical</span>',insight:'Post-procedure follow-up non-compliance can lead to readmission. Clinical risk is real.'},
  {title:'Engagement Decline Mapped',desc:'Patient engagement trajectory charted against care compliance norms.',data:'<span class="fdd-key">Portal activity:</span> <span class="fdd-neg">Week 1: Active â Week 2: Declining â Week 3: Zero</span>\n<span class="fdd-key">Missed reschedule:</span> <span class="fdd-neg">1 appointment rescheduled, not yet rebooked</span>',insight:'Rescheduling without rebooking is a strong care gap signal â 72% of these patients miss the follow-up.'},
  {title:'Care Gap Risk Pattern Matched',desc:'Historical compliance data applied to this patient profile.',data:'<span class="fdd-key">Pattern:</span> <span class="fdd-hi">"Portal silence + reschedule without rebook" â 72% care gap</span>\n<span class="fdd-key">Risk elevation:</span> <span class="fdd-neg">Without intervention: readmission risk 2.3Ã</span>',insight:'Early intervention reduces readmission risk significantly. Day 7â14 post-procedure is the critical window.'},
  {title:'Care Gap Probability Scored',desc:'Individual patient risk score generated with intervention timing.',data:'<span class="fdd-key">Care gap probability:</span> <span class="fdd-neg">72%</span> without intervention (confidence: <span class="fdd-pos">91.2%</span>)\n<span class="fdd-key">Optimal intervention:</span> <span class="fdd-val">Day 3â7 post-signal drop</span>\n<span class="fdd-key">Best channel:</span> <span class="fdd-pos">SMS (82% response for this age/demographic)</span>',insight:'Patient is at day 4 of the intervention window. SMS outperforms email by 3Ã for this patient cohort.'},
  {title:'Strategy: Tiered Outreach Programme',desc:'3-tier intervention sequence generated based on clinical risk level.',data:'<span class="fdd-key">Tier 1:</span> <span class="fdd-val">Automated SMS reminder (today)</span>\n<span class="fdd-key">Tier 2:</span> <span class="fdd-val">Care coordinator call (day +3 if no response)</span>\n<span class="fdd-key">Tier 3:</span> <span class="fdd-val">Provider-signed letter (day +7)</span>\n<span class="fdd-key">Projected compliance improvement:</span> <span class="fdd-pos">+28%</span>',insight:'Escalating intervention stops as soon as patient responds â prevents over-contacting and respects patient autonomy.'},
  {title:'SMS Reminder Dispatched',desc:'Personalised outreach sent. HIPAA-compliant pipeline.',data:'<span class="fdd-key">SMS sent:</span> <span class="fdd-val">"Hi [Name], your post-procedure check is due. Book in 2 taps:"</span>\n<span class="fdd-key">Coordinator queue:</span> <span class="fdd-val">Tier 2 scheduled for day +3 if no booking confirmed</span>\n<span class="fdd-key">HIPAA log:</span> <span class="fdd-pos">All communications logged and compliant</span>',insight:'One-tap booking link in SMS removes friction entirely â from reminder to booked in under 30 seconds.'},
  {title:'Appointment Booked. Model Updated.',desc:'Patient responded and rebooked. Care gap prevented.',data:'<span class="fdd-key">Outcome:</span> <span class="fdd-pos">Appointment booked via SMS link (4hrs after message)</span>\n<span class="fdd-key">Tier 2/3:</span> <span class="fdd-pos">Cancelled automatically</span>\n<span class="fdd-key">Readmission risk:</span> <span class="fdd-pos">Returned to baseline</span>\n<span class="fdd-key">Model update:</span> <span class="fdd-pos">Care compliance model â 92.8% accuracy</span>',insight:'One SMS message prevented a potential readmission. Care gap model now stronger for similar patients.'}
],[{title:'Patient Re-engagement Data Loaded',desc:'',data:'<span class="fdd-key">Last appointment:</span> <span class="fdd-neg">14 months ago</span>\n<span class="fdd-key">Chronic condition:</span> <span class="fdd-val">Type 2 diabetes â annual review overdue</span>',insight:'Chronic condition management patients who lapse care have significantly higher acute event risk.'},{title:'Patient History Reconstructed',desc:'',data:'<span class="fdd-key">Condition history:</span> <span class="fdd-val">3 years of records unified</span>\n<span class="fdd-key">Last HbA1c:</span> <span class="fdd-val">62 mmol/mol (borderline)</span>',insight:'Borderline HbA1c at last visit â clinical urgency for re-engagement is high.'},{title:'Lapse Pattern Mapped',desc:'',data:'<span class="fdd-key">Lapse trigger:</span> <span class="fdd-neg">Wait time complaint (month -14)</span>\n<span class="fdd-key">No contact since:</span> <span class="fdd-neg">GP, specialist, or telehealth</span>',insight:'Experience-triggered lapse â higher re-engagement probability than condition-fatigue lapse.'},{title:'Re-engagement Window Identified',desc:'',data:'<span class="fdd-key">Pattern:</span> <span class="fdd-hi">Chronic patients re-engage at 2Ã rate when contacted by GP (not admin)</span>\n<span class="fdd-key">Optimal month:</span> <span class="fdd-val">Month 12â15 â annual review framing</span>',insight:'Annual review framing removes stigma of "you missed appointments" â patient more likely to respond.'},{title:'Re-engagement Probability Scored',desc:'',data:'<span class="fdd-key">Re-engagement probability:</span> <span class="fdd-pos">58%</span> with GP-led outreach\n<span class="fdd-key">Clinical urgency:</span> <span class="fdd-neg">High â HbA1c review overdue</span>',insight:'58% re-engagement rate is above the 40% threshold for GP-letter investment.'},{title:'Strategy: GP-Led Annual Review',desc:'',data:'<span class="fdd-key">Outreach:</span> <span class="fdd-val">GP-signed letter: "Annual diabetes review â let\'s check in"</span>\n<span class="fdd-key">Offer:</span> <span class="fdd-val">Telehealth option + same-week appointment available</span>',insight:'Telehealth option removes the wait time barrier that caused the original lapse.'},{title:'GP Letter Dispatched',desc:'',data:'<span class="fdd-key">Letter sent:</span> <span class="fdd-val">Personalised from patient\'s named GP</span>\n<span class="fdd-key">Telehealth link:</span> <span class="fdd-val">Included for immediate booking</span>',insight:'Personalised from the GP the patient already knows â not a generic clinic letter.'},{title:'Patient Re-engaged',desc:'',data:'<span class="fdd-key">Outcome:</span> <span class="fdd-pos">Telehealth appointment booked (day 6)</span>\n<span class="fdd-key">HbA1c reviewed:</span> <span class="fdd-val">Medication adjusted â clinical intervention successful</span>\n<span class="fdd-key">Model update:</span> <span class="fdd-pos">Re-engagement model â 61.4% accuracy</span>',insight:'Medication adjustment prevented a likely acute event. Healthcare AI delivering direct clinical value.'}]]],
},
education:{label:'Education',scenarios:['Dropout Risk Prevention','Course Completion Boost'],data:[[[
  {title:'Student Engagement Data Ingested',desc:'LMS, portal logins, assignment system, and forum activity collected.',data:'<span class="fdd-key">LMS logins this week:</span> <span class="fdd-neg">1 (was 5/week)</span>\n<span class="fdd-key">Assignments:</span> <span class="fdd-neg">2 missed deadlines</span>\n<span class="fdd-key">Forum posts:</span> <span class="fdd-neg">0 in 14 days (was 3/week)</span>',insight:'All three engagement dimensions dropped simultaneously. Multi-signal dropout indicator active.'},
  {title:'Student Profile Unified',desc:'LMS, SIS, and support systems linked to single student record.',data:'<span class="fdd-key">Systems linked:</span> <span class="fdd-val">LMS + SIS + Wellbeing + Finance</span>\n<span class="fdd-key">Student stage:</span> <span class="fdd-val">Week 4 of 12 â critical early engagement window</span>',insight:'Week 4 is statistically the highest dropout risk point. Early intervention has highest ROI here.'},
  {title:'Disengagement Trajectory Mapped',desc:'Engagement health score over 4 weeks plotted.',data:'<span class="fdd-key">Engagement score:</span> <span class="fdd-neg">9.2 â 2.8 over 21 days</span>\n<span class="fdd-key">Last active period:</span> <span class="fdd-val">Week 2 (assignment submitted late but submitted)</span>',insight:'Gradual decline accelerating. Without intervention, probability of no submission next week is 84%.'},
  {title:'Dropout Pattern Identified',desc:'Pattern matching against 280,000 historical student journeys.',data:'<span class="fdd-key">Pattern:</span> <span class="fdd-hi">"Week 4 multi-signal drop" â 74% dropout within 3 weeks</span>\n<span class="fdd-key">Protective factors present:</span> <span class="fdd-val">Scholarship holder (financial stake), no prior complaints</span>',insight:'Scholarship status reduces dropout probability â student has financial motivation to persist.'},
  {title:'Dropout Risk Scored',desc:'Individual dropout probability with intervention recommendation.',data:'<span class="fdd-key">Dropout risk:</span> <span class="fdd-neg">74%</span> without intervention (confidence: <span class="fdd-pos">89.1%</span>)\n<span class="fdd-key">With advisor contact:</span> <span class="fdd-pos">28% dropout (46pp improvement)</span>\n<span class="fdd-key">Optimal timing:</span> <span class="fdd-val">Within 48 hours</span>',insight:'Advisor contact is the highest-leverage intervention for this pattern â 46pp improvement in retention rate.'},
  {title:'Strategy: Tiered Support Intervention',desc:'3-tier student support programme generated.',data:'<span class="fdd-key">Tier 1:</span> <span class="fdd-val">Automated check-in email with resources (today)</span>\n<span class="fdd-key">Tier 2:</span> <span class="fdd-val">Academic advisor call (day +2)</span>\n<span class="fdd-key">Tier 3:</span> <span class="fdd-val">Peer mentor assignment + wellbeing referral (day +5)</span>',insight:'Tiered approach respects student autonomy â escalates only if student does not self-resolve.'},
  {title:'Check-In Email & Advisor Alert Deployed',desc:'Multi-channel intervention initiated.',data:'<span class="fdd-key">Email sent:</span> <span class="fdd-val">"We noticed you might need some support â resources inside"</span>\n<span class="fdd-key">Advisor dashboard:</span> <span class="fdd-val">Priority flag with full engagement history</span>\n<span class="fdd-key">LMS:</span> <span class="fdd-val">Catch-up pathway unlocked â 4 micro-modules</span>',insight:'Catch-up micro-modules reduce the psychological barrier of a large assignment backlog.'},
  {title:'Student Re-engaged',desc:'Advisor call made. Student returned to active status.',data:'<span class="fdd-key">Outcome:</span> <span class="fdd-pos">Advisor called day 2 â student disclosed workload pressure</span>\n<span class="fdd-key">Resolution:</span> <span class="fdd-pos">Extension granted + study plan created</span>\n<span class="fdd-key">Week 5 engagement:</span> <span class="fdd-pos">Returned to 4 logins, 1 assignment submitted</span>\n<span class="fdd-key">Model update:</span> <span class="fdd-pos">Dropout model â 91.2% accuracy</span>',insight:'Early disclosure of workload pressure â most common hidden dropout driver. Now captured as a predictor signal.'}
],[]]],
},
travel:{label:'Travel & Hospitality',scenarios:['High-Value Booking Prediction','Loyalty Re-engagement'],data:[[[
  {title:'Traveller Data Ingested',desc:'Booking history, search, loyalty, and partner APIs collected.',data:'<span class="fdd-key">Searches (14 days):</span> <span class="fdd-val">Tokyo Ã4, Singapore Ã2, Seoul Ã1</span>\n<span class="fdd-key">Loyalty tier:</span> <span class="fdd-val">Platinum (top 2%)</span>\n<span class="fdd-key">Annual trips:</span> <span class="fdd-val">14 (primarily business)</span>',insight:'APAC destination cluster forming in search history. Booking window approaching based on historical patterns.'},
  {title:'Traveller Profile Assembled',desc:'Loyalty, booking, and preference data unified across 3 platforms.',data:'<span class="fdd-key">Preferences mapped:</span> <span class="fdd-val">Aisle seats, early flights, boutique hotels, business class</span>\n<span class="fdd-key">Avg spend per trip:</span> <span class="fdd-val">$3,200</span>',insight:'Strong preference profile enables highly personalised offers â generic promotions consistently underperform for Platinum members.'},
  {title:'Destination Intent Trajectory Mapped',desc:'Search pattern analysed for booking probability signals.',data:'<span class="fdd-key">Search cluster:</span> <span class="fdd-hi">Tokyo dominant (57% of APAC searches)</span>\n<span class="fdd-key">Historical pattern:</span> <span class="fdd-val">This search depth converts to booking within 21 days at 89%</span>',insight:'Traveller has researched Tokyo 4 times in 14 days â depth of research signals genuine booking intent, not casual browsing.'},
  {title:'Booking Intent Pattern Confirmed',desc:'Pattern matching against 4.2M historical travel journeys.',data:'<span class="fdd-key">Pattern:</span> <span class="fdd-hi">"APAC search cluster + Platinum status" â 89% booking within 21 days</span>\n<span class="fdd-key">Q2 correlation:</span> <span class="fdd-val">Business travel to Tokyo peaks AprilâMay for this corporate segment</span>',insight:'Seasonal confirmation: Q2 is peak Tokyo business travel. Offer window is now.'},
  {title:'Booking Prediction Generated',desc:'Destination, timing, and spend predicted with high confidence.',data:'<span class="fdd-key">Destination:</span> <span class="fdd-pos">Tokyo â 89% confidence</span>\n<span class="fdd-key">Travel window:</span> <span class="fdd-val">Next 21 days</span>\n<span class="fdd-key">Predicted spend:</span> <span class="fdd-val">$3,200 Â± $280</span>\n<span class="fdd-key">Optimal package:</span> <span class="fdd-val">Flight + boutique hotel + lounge + 5,000 bonus miles</span>',insight:'Bonus miles threshold set at 5,000 â exactly enough to trigger the next tier upgrade, a known Platinum member motivator.'},
  {title:'Strategy: Platinum Tokyo Experience',desc:'Premium curated offer designed around traveller preferences.',data:'<span class="fdd-key">Package:</span> <span class="fdd-val">Business class + Shibuya boutique + Priority Pass + 5K miles</span>\n<span class="fdd-key">Pricing:</span> <span class="fdd-val">$3,180 (2% below predicted WTP)</span>\n<span class="fdd-key">Channel:</span> <span class="fdd-val">Email with personalised Tokyo imagery</span>',insight:'Dynamic pricing set 2% below willingness-to-pay to maximise conversion while protecting margin.'},
  {title:'Personalised Offer Deployed',desc:'Multi-channel campaign triggered across loyalty touchpoints.',data:'<span class="fdd-key">Email:</span> <span class="fdd-val">"Your Tokyo Trip Awaits â curated for Platinum members"</span>\n<span class="fdd-key">App banner:</span> <span class="fdd-val">Persistent offer card with one-tap booking</span>\n<span class="fdd-key">Partner sync:</span> <span class="fdd-pos">Seat preference pre-loaded with airline API</span>',insight:'Seat preference already pre-loaded in the booking â removes 2 steps from checkout. Conversion friction minimised.'},
  {title:'Booking Confirmed. Profile Enriched.',desc:'Trip booked. Traveller preferences updated with new signals.',data:'<span class="fdd-key">Outcome:</span> <span class="fdd-pos">Tokyo business class booked â $3,400</span> (above predicted spend)\n<span class="fdd-key">Preference added:</span> <span class="fdd-val">Shibuya boutique hotel â added to preferred properties</span>\n<span class="fdd-key">Model update:</span> <span class="fdd-pos">APAC intent model â 91.3% accuracy</span>\n<span class="fdd-key">Next prediction:</span> <span class="fdd-val">Return trip in 6 months â 74%</span>',insight:'Spent $200 above predicted â indicates offer had room to be slightly higher. Pricing model adjusted for next trip.'}
],[]]],
},
telecom:{label:'Telecom',scenarios:['Subscriber Churn Prevention'],data:[[[
  {title:'Subscriber Data Ingested',desc:'Usage records, app telemetry, billing, and support tickets streaming.',data:'<span class="fdd-key">Data usage:</span> <span class="fdd-neg">â 240% (new streaming behaviour)</span>\n<span class="fdd-key">Complaints (30d):</span> <span class="fdd-neg">2 (app crashes + billing dispute)</span>\n<span class="fdd-key">App logins:</span> <span class="fdd-neg">â 60% over 30 days</span>',insight:'Three independent negative signals converging simultaneously. Classic churn cluster.'},
  {title:'Subscriber Profile Unified',desc:'Billing, usage, support, and app data merged.',data:'<span class="fdd-key">ARPU:</span> <span class="fdd-val">$65/month</span>\n<span class="fdd-key">Tenure:</span> <span class="fdd-val">3.2 years</span>\n<span class="fdd-key">Plan:</span> <span class="fdd-val">10GB data (consistently hitting limit)</span>',insight:'Consistently hitting data limit = plan mismatch. Customer frustration is structural, not incidental.'},
  {title:'Dissatisfaction Trajectory Mapped',desc:'Engagement health over 90 days showing decline triggers.',data:'<span class="fdd-key">Engagement score:</span> <span class="fdd-neg">7.4 â 2.9 (90 days)</span>\n<span class="fdd-key">Competitor signals:</span> <span class="fdd-neg">3 competitor comparison pages visited</span>',insight:'Competitor page visits in context of data limit frustration = active plan comparison behaviour.'},
  {title:'Churn Signal Cluster Detected',desc:'ML pattern matching against 2.8M subscriber histories.',data:'<span class="fdd-key">Pattern:</span> <span class="fdd-hi">"Data cap frustration + complaint + competitor research" â 84% churn in 30 days</span>\n<span class="fdd-key">Historical validation:</span> <span class="fdd-val">Seen 18,400 times â 78% ported out within 30 days without intervention</span>',insight:'The structural cause (data cap) can be fixed. Proactive upgrade offer has 72% save rate for this pattern.'},
  {title:'Churn Risk: Critical',desc:'Subscriber-level churn prediction with revenue at risk.',data:'<span class="fdd-key">Churn probability:</span> <span class="fdd-neg">84%</span> within 30 days (confidence: <span class="fdd-pos">93.1%</span>)\n<span class="fdd-key">ARPU at risk:</span> <span class="fdd-neg">$65/month ($780/year)</span>\n<span class="fdd-key">Save probability with offer:</span> <span class="fdd-pos">72%</span>',insight:'Expected value of intervention: $780 Ã 72% = $562. Cost of upgrade offer: $0 (plan revenue neutral). Clear ROI.'},
  {title:'Strategy: Proactive Upgrade + Credit',desc:'Personalised retention offer addressing the root cause.',data:'<span class="fdd-key">Offer:</span> <span class="fdd-val">Unlimited data upgrade (same monthly cost)</span>\n<span class="fdd-key">Service credit:</span> <span class="fdd-val">$20 applied to next bill (goodwill gesture)</span>\n<span class="fdd-key">Channel:</span> <span class="fdd-val">Push notification (highest response for this segment)</span>',insight:'Upgrade is revenue neutral â no margin loss. $20 credit costs $20 but saves $780/year. 39Ã ROI.'},
  {title:'Offer Pushed',desc:'Proactive push notification and account update deployed.',data:'<span class="fdd-key">Push:</span> <span class="fdd-val">"We\'ve upgraded your plan â unlimited data, no extra cost ð"</span>\n<span class="fdd-key">Credit:</span> <span class="fdd-pos">$20 applied automatically</span>\n<span class="fdd-key">Support flag:</span> <span class="fdd-val">"Retention priority" tag added to account</span>',insight:'"Retention priority" tag means if this subscriber calls, any agent sees the full context and does not disconnect without escalation.'},
  {title:'Subscriber Retained',desc:'Subscriber engaged and retained. Churn model refined.',data:'<span class="fdd-key">Outcome:</span> <span class="fdd-pos">Push opened in 3hrs â plan accepted</span>\n<span class="fdd-key">NPS (30d post):</span> <span class="fdd-pos">3 â 7</span>\n<span class="fdd-key">App engagement:</span> <span class="fdd-pos">â 18% over next 14 days</span>\n<span class="fdd-key">Model update:</span> <span class="fdd-pos">Telecom churn model â 95.2% accuracy</span>',insight:'Engagement recovery after intervention is itself a training signal â confirms this pattern resolution works.'}
]]]
}
};
// Fill remaining industries with simplified data
['insurance','manufacturing','energy','realestate','media','pharma','automotive','food','construction'].forEach(k=>{
  SCENARIOS[k]={label:k.charAt(0).toUpperCase()+k.slice(1),scenarios:['Primary Scenario'],data:[[[
    {title:'Data Ingestion',desc:'Streaming all customer touchpoints for this industry.',data:'<span class="fdd-key">Sources connected:</span> <span class="fdd-val">5 active data feeds</span>\n<span class="fdd-key">Events (24hr):</span> <span class="fdd-val">12,400+</span>\n<span class="fdd-key">Latency:</span> <span class="fdd-pos">Under 60ms</span>',insight:'All data sources live. Historical data loaded for model training.'},
    {title:'Identity Unification',desc:'Cross-platform identities merged into golden profiles.',data:'<span class="fdd-key">Profiles created:</span> <span class="fdd-val">8,200 golden records</span>\n<span class="fdd-key">Duplicate rate resolved:</span> <span class="fdd-pos">0%</span>',insight:'Single unified view per customer across all systems.'},
    {title:'Behavioural Mapping',desc:'Journey graphs and engagement trajectories built.',data:'<span class="fdd-key">Journey nodes mapped:</span> <span class="fdd-val">18,400</span>\n<span class="fdd-key">Key pattern detected:</span> <span class="fdd-val">Engagement decline trajectory</span>',insight:'Customer behaviour mapped at micro-interaction level.'},
    {title:'Pattern Detection',desc:'ML surfacing hidden correlations and risk signals.',data:'<span class="fdd-key">Patterns matched:</span> <span class="fdd-val">3 significant correlations found</span>\n<span class="fdd-key">Risk signal:</span> <span class="fdd-neg">Elevated churn indicator</span>',insight:'Pattern cluster matches historical at-risk profile with 89% confidence.'},
    {title:'Prediction Generated',desc:'Forward-looking scores for churn, value, and action.',data:'<span class="fdd-key">Churn probability:</span> <span class="fdd-neg">68%</span> within 60 days\n<span class="fdd-key">Confidence:</span> <span class="fdd-pos">91.4%</span>\n<span class="fdd-key">Customer value at risk:</span> <span class="fdd-val">$4,200/year</span>',insight:'Churn probability above 65% activation threshold. Intervention triggered.'},
    {title:'Strategy Generated',desc:'Industry-specific retention playbook created.',data:'<span class="fdd-key">Strategy:</span> <span class="fdd-val">Personalised retention offer</span>\n<span class="fdd-key">Projected save rate:</span> <span class="fdd-pos">64%</span>\n<span class="fdd-key">ROI:</span> <span class="fdd-pos">12.4Ã offer cost</span>',insight:'Playbook tuned to industry norms and this customer\'s specific profile.'},
    {title:'Action Executed',desc:'Automated intervention deployed across optimal channels.',data:'<span class="fdd-key">Action:</span> <span class="fdd-val">Personalised email + in-app notification sent</span>\n<span class="fdd-key">Time to action:</span> <span class="fdd-pos">1.4 seconds from prediction</span>',insight:'Real-time execution with no human bottleneck required.'},
    {title:'Outcome Recorded',desc:'Result fed back to continuously improve models.',data:'<span class="fdd-key">Outcome:</span> <span class="fdd-pos">Customer retained</span>\n<span class="fdd-key">Model accuracy:</span> <span class="fdd-pos">+0.4% improvement</span>\n<span class="fdd-key">Next prediction:</span> <span class="fdd-val">Upsell window in 30 days</span>',insight:'Every outcome makes the next prediction more accurate. The engine gets smarter continuously.'}
  ]]]};
});

// ââ SIMULATOR STATE ââ
let simRunning=false,simSpeed=1,currentStep=-1,simTimer=null;

// Populate scenarios on industry change
function onIndChange(){
  const ind=document.getElementById('selInd').value;
  const sc=document.getElementById('selScen');
  sc.innerHTML='';
  SCENARIOS[ind].scenarios.forEach((s,i)=>{const o=document.createElement('option');o.value=i;o.textContent=s;sc.appendChild(o)});
}
onIndChange();

document.querySelectorAll('.spd-btn').forEach(b=>{b.addEventListener('click',()=>{document.querySelectorAll('.spd-btn').forEach(x=>x.classList.remove('active'));b.classList.add('active');simSpeed=parseInt(b.dataset.spd)})});

function setAndRun(ind,scenIdx){
  document.getElementById('selInd').value=ind;onIndChange();
  document.getElementById('selScen').value=scenIdx;
  document.getElementById('simulator')?.scrollIntoView({behavior:'smooth'});
  document.querySelector('.sim-shell').scrollIntoView({behavior:'smooth'});
  setTimeout(runSim,400);
}

function resetSim(){
  if(simTimer)clearTimeout(simTimer);simRunning=false;currentStep=-1;
  document.getElementById('runBtn').disabled=false;document.getElementById('runBtn').innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="white"><polygon points="5,3 19,12 5,21"/></svg>Run Simulation';
  document.getElementById('simFeed').innerHTML='<div class="sim-empty" id="simEmpty"><div class="se-icon"><svg viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><div class="se-title">Ready to simulate</div><div class="se-sub">Select an industry and scenario, then run the simulation to see the AI engine process a customer in real time.</div><div class="se-hints"><div class="se-hint"><div class="sh-dot" style="background:var(--blue)"></div><div class="sh-text">Each layer shows real data, patterns, and decisions</div></div><div class="se-hint"><div class="sh-dot" style="background:var(--violet)"></div><div class="sh-text">Every industry has unique scenarios</div></div><div class="se-hint"><div class="sh-dot" style="background:var(--emerald)"></div><div class="sh-text">Layer 7 shows what action was triggered</div></div></div></div>';
  document.querySelectorAll('.sp').forEach(s=>{s.classList.remove('active','done');s.style.removeProperty('--lc')});
  document.querySelectorAll('.ss-layer-row').forEach(l=>{l.classList.remove('active','done-l');l.querySelector('.ssl-status').textContent='IDLE';l.querySelector('.ssl-name').style.color=''});
  document.getElementById('progBar').style.width='0';
  ['ssData','ssConf','ssPred'].forEach(id=>{const el=document.getElementById(id);el.querySelector('.ss-m-val').textContent='â';el.querySelector('.ss-m-fill').style.width='0';el.querySelector('.ss-m-change').textContent='â'});
  document.getElementById('simSummary').classList.remove('show');
  const live=document.getElementById('stbLive');live.className='stb-live';document.getElementById('stbStatus').textContent='READY';
}

function runSim(){
  if(simRunning)return;resetSim();simRunning=true;
  const ind=document.getElementById('selInd').value;
  const scenIdx=parseInt(document.getElementById('selScen').value)||0;
  const data=SCENARIOS[ind].data[0][scenIdx]||SCENARIOS[ind].data[0][0];
  const runBtn=document.getElementById('runBtn');
  runBtn.disabled=true;runBtn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M10 15l-3-3 3-3M14 9l3 3-3 3"/></svg>Running...';
  document.getElementById('simFeed').innerHTML='';
  const live=document.getElementById('stbLive');live.className='stb-live running';document.getElementById('stbStatus').textContent='PROCESSING';
  const steps=document.querySelectorAll('.sp');
  const layerRows=document.querySelectorAll('.ss-layer-row');
  const delay=1100/simSpeed;
  data.forEach((entry,i)=>{
    simTimer=setTimeout(()=>{
      if(!simRunning)return;
      currentStep=i;
      // pipeline
      steps.forEach((s,j)=>{s.classList.remove('active');if(j<i)s.classList.add('done');if(j===i)s.classList.add('active')});
      // layer sidebar
      layerRows.forEach((l,j)=>{l.classList.remove('active','done-l');const st=l.querySelector('.ssl-status');if(j===i){l.classList.add('active');st.textContent='ACTIVE';st.style.color='var(--emerald)'}else if(j<i){l.classList.add('done-l');st.textContent='DONE';st.style.color='var(--blue)'}else{st.textContent='IDLE';st.style.color=''}});
      // progress bar
      document.getElementById('progBar').style.width=((i+1)/8*100)+'%';
      // metrics
      const dataAmt=Math.floor(2000+i*2800);
      const conf=(88+i*1.1).toFixed(1);
      const predVal=i>=4?'$'+Math.floor(800+i*600).toLocaleString():'â';
      setMetric('ssData',dataAmt.toLocaleString()+' events','var(--blue)',Math.min(95,20+i*12),`Layer ${i+1} processing`);
      setMetric('ssConf',conf+'%','var(--emerald)',parseFloat(conf),`${conf}% confidence`);
      if(i>=4)setMetric('ssPred',predVal,'var(--violet)',Math.min(88,40+i*10),'Prediction active');
      // feed entry
      const ts=new Date(),time=String(ts.getHours()).padStart(2,'0')+':'+String(ts.getMinutes()).padStart(2,'0')+':'+String(ts.getSeconds()).padStart(2,'0');
      const feed=document.getElementById('simFeed');
      const div=document.createElement('div');div.className='feed-entry';
      div.innerHTML=`<div class="fe-header"><span class="fe-tag ${TAG_CLS[i]}">${LAYERS[i].short} ${LAYERS[i].name.toUpperCase()}</span><span class="fe-layer-num">Layer ${String(i+1).padStart(2,'0')} of 08</span><span class="fe-time">${time}</span></div><div class="fe-title">${entry.title}</div>${entry.desc?`<div class="fe-desc">${entry.desc}</div>`:''}<div class="fe-data">${entry.data.replace(/\n/g,'<br>')}</div>${entry.insight?`<div class="fe-insight"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>${entry.insight}</div>`:''}`;
      feed.appendChild(div);feed.scrollTop=feed.scrollHeight;
      // done
      if(i===7){
        steps[7].classList.remove('active');steps[7].classList.add('done');
        layerRows[7].classList.remove('active');layerRows[7].classList.add('done-l');
        layerRows[7].querySelector('.ssl-status').textContent='DONE';layerRows[7].querySelector('.ssl-status').style.color='var(--blue)';
        live.className='stb-live done';document.getElementById('stbStatus').textContent='COMPLETE';
        runBtn.disabled=false;runBtn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="white"><polygon points="5,3 19,12 5,21"/></svg>Run Again';
        simRunning=false;
        showSummary(conf,SCENARIOS[ind].label);
      }
    },delay*i);
  });
}

function setMetric(id,val,col,pct,change){
  const el=document.getElementById(id);
  el.querySelector('.ss-m-val').textContent=val;el.querySelector('.ss-m-val').style.color=col;
  el.querySelector('.ss-m-fill').style.width=pct+'%';
  el.querySelector('.ss-m-change').textContent=change||'';
  el.classList.add('pulse');setTimeout(()=>el.classList.remove('pulse'),400);
}

function showSummary(conf,industry){
  const sum=document.getElementById('simSummary');sum.classList.add('show');
  document.getElementById('sumTitle').textContent=`${industry} â all 8 layers complete`;
  document.getElementById('sumGrid').innerHTML=`
    <div class="sg-item"><div class="sgi-n" style="color:var(--blue)">8/8</div><div class="sgi-l">Layers Complete</div></div>
    <div class="sg-item"><div class="sgi-n" style="color:var(--emerald)">${conf}%</div><div class="sgi-l">Model Confidence</div></div>
    <div class="sg-item"><div class="sgi-n" style="color:var(--violet)">&lt;200ms</div><div class="sgi-l">Latency</div></div>
    <div class="sg-item"><div class="sgi-n" style="color:var(--amber)">Live</div><div class="sgi-l">Action Triggered</div></div>`;
}

function copyResults(){
  navigator.clipboard?.writeText(`X Platforms Simulator Results\nIndustry: ${document.getElementById('selInd').options[document.getElementById('selInd').selectedIndex].text}\nAll 8 layers processed. Try it at xplatforms.ai/simulator`);
  const btn=event.target;btn.textContent='Copied!';setTimeout(()=>btn.textContent='Share Results',2000);
}

// ââ DATA FLOW CANVAS ââ
const fc=document.getElementById('flow');let FW,FH;
function resizeFlow(){FW=fc.width=fc.parentElement.clientWidth;FH=fc.height=fc.parentElement.clientHeight||260}
resizeFlow();addEventListener('resize',resizeFlow);
const FX=nctx.constructor===CanvasRenderingContext2D?fc.getContext('2d'):null;
const flowCtx=fc.getContext('2d');
// Build flow labels
const flowLabels=document.getElementById('flowLabels');
LAYERS.forEach(l=>{flowLabels.innerHTML+=`<div class="fl-label"><strong>${l.name}</strong><span>Layer</span></div>`});
// Flow particles
let fParticles=[];
const NODE_COLORS=['#4f8fff','#38bdf8','#f472b6','#fbbf24','#818cf8','#34d399','#fb923c','#60a5fa'];
function getNodeX(i){return(FW/(LAYERS.length+1))*(i+1)}
const NODE_Y=()=>FH*.45;
setInterval(()=>{if(fParticles.length<40){const from=Math.floor(Math.random()*7);fParticles.push({from,to:from+1,t:0,spd:.008+Math.random()*.012,c:NODE_COLORS[from],size:Math.random()*3+1.5})}},120);
(function drawFlow(){
  flowCtx.clearRect(0,0,FW,FH);
  const ny=NODE_Y();
  // connections
  for(let i=0;i<LAYERS.length-1;i++){flowCtx.beginPath();flowCtx.moveTo(getNodeX(i),ny);flowCtx.lineTo(getNodeX(i+1),ny);flowCtx.strokeStyle='rgba(79,143,255,0.12)';flowCtx.lineWidth=1.5;flowCtx.stroke()}
  // particles
  fParticles=fParticles.filter(p=>{p.t+=p.spd;if(p.t>1)return false;
    const x=getNodeX(p.from)+(getNodeX(p.to)-getNodeX(p.from))*p.t;
    const wave=Math.sin(p.t*Math.PI)*-20;
    flowCtx.beginPath();flowCtx.arc(x,ny+wave,p.size,0,6.28);
    flowCtx.fillStyle=p.c.replace('#','').length===6?`rgba(${parseInt(p.c.slice(1,3),16)},${parseInt(p.c.slice(3,5),16)},${parseInt(p.c.slice(5,7),16)},${0.7-p.t*.4})`:'rgba(79,143,255,.6)';
    flowCtx.fill();
    // glow
    flowCtx.beginPath();flowCtx.arc(x,ny+wave,p.size+4,0,6.28);
    const glowC=p.c.replace('#','').length===6?`rgba(${parseInt(p.c.slice(1,3),16)},${parseInt(p.c.slice(3,5),16)},${parseInt(p.c.slice(5,7),16)},0.1)`:'rgba(79,143,255,.08)';
    flowCtx.fillStyle=glowC;flowCtx.fill();return true});
  // nodes
  LAYERS.forEach((l,i)=>{const x=getNodeX(i),c=NODE_COLORS[i];
    flowCtx.beginPath();flowCtx.arc(x,ny,8,0,6.28);flowCtx.fillStyle=c.replace('#','').length===6?`rgba(${parseInt(c.slice(1,3),16)},${parseInt(c.slice(3,5),16)},${parseInt(c.slice(5,7),16)},0.8)`:'rgba(79,143,255,.8)';flowCtx.fill();
    flowCtx.beginPath();flowCtx.arc(x,ny,16,0,6.28);flowCtx.fillStyle=c.replace('#','').length===6?`rgba(${parseInt(c.slice(1,3),16)},${parseInt(c.slice(3,5),16)},${parseInt(c.slice(5,7),16)},0.08)`:'rgba(79,143,255,.08)';flowCtx.fill();
  });
  requestAnimationFrame(drawFlow)})();

// ââ 8 LAYERS CONTENT ââ
const LAYER_DATA=[
  {desc:'The gateway to intelligence. X Platforms connects to every customer-facing platform your business runs â websites, mobile apps, social media, CRM, POS, email, call centres, ad platforms, IoT sensors â via 200+ pre-built one-click connectors. Data streams in real time with zero ETL code required.',tags:['Real-time Streaming','200+ Connectors','Zero-code Setup','Historical Backfill'],metrics:[{n:'200+',l:'Connectors'},{n:'&lt;60ms',l:'Ingestion Lag'},{n:'18mo',l:'History Loaded'},{n:'Zero',l:'Code Required'}],visual:[{label:'Web Analytics',val:'Real-time'},{label:'CRM (Salesforce, HubSpot)',val:'Live sync'},{label:'Email Platform',val:'Event stream'},{label:'Mobile App',val:'SDK events'},{label:'POS Systems',val:'Transaction feed'},{label:'Call Centre',val:'Sentiment API'}]},
  {desc:'Raw data arrives fragmented â the same customer appears 4â12 times across different platforms with different IDs, names, and formats. X Platforms uses probabilistic and deterministic matching to merge every identity into one golden record per real person. The result: zero duplicates, complete customer visibility.',tags:['Identity Resolution','Deduplication','Golden Record','Cross-device Matching'],metrics:[{n:'87%',l:'Avg Match Rate'},{n:'0%',l:'Duplicate Rate'},{n:'Real-time',l:'Resolution'},{n:'100%',l:'Privacy Compliant'}],visual:[{label:'Email match',val:'Deterministic'},{label:'Device fingerprint',val:'Probabilistic'},{label:'Phone number hash',val:'Deterministic'},{label:'Behavioural similarity',val:'ML-scored'},{label:'Golden record created',val:'â Unified'}]},
  {desc:'Once unified, every micro-interaction is mapped into a rich behavioural graph. Study session frequency, purchase journey paths, support sentiment trajectories, channel preferences, and time-of-day patterns are all tracked and structured. This layer transforms raw events into customer intelligence.',tags:['Journey Graphs','Sentiment Analysis','Intent Signals','Micro-event Tracking'],metrics:[{n:'24K+',l:'Journey Nodes'},{n:'12',l:'Sentiment Dims'},{n:'Real-time',l:'Updates'},{n:'4.2Ã',l:'CLV Lift vs No Map'}],visual:[{label:'Session depth & scroll',val:'Tracked'},{label:'Sentiment trajectory',val:'NLP scored'},{label:'Channel preference',val:'Learned'},{label:'Purchase intent signals',val:'Active'},{label:'Lifecycle stage',val:'Mapped'}]},
  {desc:'Machine learning algorithms work across the unified behavioural data to surface correlations, anomalies, and predictive patterns that human analysts cannot see at scale. The "Week 2 skip" pattern at MockMaster, the "40/7 rule" at OneAustralia â both were discovered here.',tags:['ML Algorithms','Anomaly Detection','Correlation Mining','Pattern Discovery'],metrics:[{n:'2.4M+',l:'Patterns Indexed'},{n:'3.2Ï',l:'Anomaly Threshold'},{n:'&lt;200ms',l:'Detection Speed'},{n:'97%',l:'Signal Accuracy'}],visual:[{label:'Churn precursor cluster',val:'Detected'},{label:'Seasonal correlation',val:'Mapped'},{label:'Channel effectiveness',val:'Scored'},{label:'Competitor signals',val:'Flagged'},{label:'Product affinity graph',val:'Built'}]},
  {desc:'The core intelligence layer. Using patterns discovered in Layer 4, X Platforms generates forward-looking predictions for every customer: churn probability, purchase propensity, customer lifetime value, expansion readiness, and optimal next-best-action â all with calibrated confidence intervals.',tags:['Churn Prediction','CLV Scoring','Purchase Propensity','Next-Best-Action'],metrics:[{n:'97%',l:'Avg Accuracy'},{n:'21 Days',l:'Avg Prediction Window'},{n:'&lt;200ms',l:'Score Generation'},{n:'15',l:'Model Types'}],visual:[{label:'Churn risk score',val:'0â100'},{label:'Purchase probability',val:'0â100%'},{label:'CLV 12-month',val:'$0â$50K'},{label:'Expansion score',val:'0â100'},{label:'Confidence interval',val:'Â±3pp avg'}]},
  {desc:'Raw predictions alone aren\'t enough. Layer 6 translates each prediction into a concrete, industry-specific strategy: which offer, what message, which channel, and at what price. Each recommendation includes a projected ROI so teams can prioritise by expected impact.',tags:['Industry Playbooks','ROI Projections','Offer Design','Channel Optimisation'],metrics:[{n:'15',l:'Industry Playbooks'},{n:'8.4Ã',l:'Avg Projected ROI'},{n:'A/B',l:'Testing Built-in'},{n:'Real-time',l:'Strategy Updates'}],visual:[{label:'Recommended offer',val:'Dynamic'},{label:'Optimal channel',val:'ML selected'},{label:'Message personalisation',val:'AI written'},{label:'Send time',val:'Behaviour-tuned'},{label:'Projected ROI',val:'Calculated'}]},
  {desc:'When predictions meet activation thresholds, Layer 7 fires real-time actions: personalised emails, push notifications, CRM updates, ad spend reallocation, support routing changes. All triggered automatically in under 2 seconds from the prediction generating in Layer 5.',tags:['Auto-triggers','Real-time Actions','CRM Updates','Ad Rebalancing'],metrics:[{n:'&lt;2s',l:'Trigger Speed'},{n:'1,200+',l:'Avg Actions/Week'},{n:'Zero',l:'Manual Steps'},{n:'99.9%',l:'Delivery Rate'}],visual:[{label:'Email personalisation',val:'AI-generated'},{label:'Push notification',val:'Behaviour-timed'},{label:'CRM record update',val:'Automatic'},{label:'Ad spend rebalance',val:'Real-time'},{label:'Support priority flag',val:'Instant'}]},
  {desc:'Every outcome â purchase, churn, click, conversion â feeds back into the system. Models retrain continuously, pattern weights update, and the accuracy of every prediction improves with each interaction. X Platforms gets measurably smarter every day it runs on your data.',tags:['Self-improving Models','Feedback Loops','Continuous Retraining','Accuracy Compounding'],metrics:[{n:'+0.3%',l:'Avg Weekly Accuracy Lift'},{n:'Daily',l:'Model Retraining'},{n:'18mo',l:'Learning History'},{n:'97%',l:'Peak Accuracy'}],visual:[{label:'Outcome recorded',val:'All interactions'},{label:'Weight adjustment',val:'Automated'},{label:'Backtesting frequency',val:'Daily'},{label:'A/B test integration',val:'Continuous'},{label:'Accuracy trajectory',val:'Improving'}]}
];
const tabsEl=document.getElementById('layerTabs');
const contEl=document.getElementById('layerContents');
LAYERS.forEach((l,i)=>{
  tabsEl.innerHTML+=`<button class="lt-btn${i===0?' active':''}" data-i="${i}" onclick="showLayer(${i})" style="border-color:${i===0?'var(--blue)':'var(--brd)'}">${l.short} ${l.name}</button>`;
  const d=LAYER_DATA[i];
  contEl.innerHTML+=`<div class="lt-content${i===0?' active':''}" id="ltc-${i}">
    <div class="ltc-info">
      <div class="stag" style="color:${l.color}">Layer 0${i+1}</div>
      <h3>${l.name}</h3>
      <p>${d.desc}</p>
      <div class="ltc-tags">${d.tags.map(t=>`<span class="ltc-tag">${t}</span>`).join('')}</div>
      <div class="ltc-metrics">${d.metrics.map(m=>`<div class="ltm"><div class="ltm-n">${m.n}</div><div class="ltm-l">${m.l}</div></div>`).join('')}</div>
    </div>
    <div class="ltc-visual">
      <div class="ltv-title">What this layer produces</div>
      ${d.visual.map(v=>`<div class="ltv-row"><span class="ltv-dot" style="background:${l.color}"></span><span class="ltv-label">${v.label}</span><span class="ltv-val">${v.val}</span></div>`).join('')}
    </div>
  </div>`;
});

function showLayer(i){
  document.querySelectorAll('.lt-btn').forEach((b,j)=>{b.classList.toggle('active',j===i);b.style.borderColor=j===i?LAYERS[i].color:'var(--brd)'});
  document.querySelectorAll('.lt-content').forEach((c,j)=>c.classList.toggle('active',j===i));
}

// ââ ROI CALC ââ
function fmtM(n){return n>=1e6?'$'+(n/1e6).toFixed(1)+'M':n>=1e3?'$'+(n/1e3).toFixed(0)+'K':'$'+n}
function calcROI(){
  const rev=parseInt(document.getElementById('rvRev').value)*1000;
  const cust=parseInt(document.getElementById('rvCust').value);
  const churn=parseInt(document.getElementById('rvChurn').value)/100;
  const src=parseInt(document.getElementById('rvSrc').value);
  document.getElementById('rvRevDisplay').textContent=fmtM(rev);
  document.getElementById('rvCustDisplay').textContent=cust.toLocaleString();
  document.getElementById('rvChurnDisplay').textContent=document.getElementById('rvChurn').value+'%';
  document.getElementById('rvSrcDisplay').textContent=src;
  const churnSave=rev*12*churn*.4;
  const growth=rev*12*.08*(src/5);
  const cross=rev*12*.06*(cust/10000);
  const save=rev*12*.05;
  const total=Math.round(churnSave+growth+cross+save);
  document.getElementById('roiTotal').textContent=fmtM(total);
  document.getElementById('roiChurn').textContent=fmtM(Math.round(churnSave));
  document.getElementById('roiGrowth').textContent=fmtM(Math.round(growth));
  document.getElementById('roiCross').textContent=fmtM(Math.round(cross));
  document.getElementById('roiSave').textContent=fmtM(Math.round(save));
}
calcROI();

// ââ FAQ ââ
document.querySelectorAll('.faq-q').forEach(q=>{q.addEventListener('click',()=>{const it=q.parentElement,was=it.classList.contains('open');document.querySelectorAll('.faq-item').forEach(i=>i.classList.remove('open'));if(!was)it.classList.add('open')})});

// ââ REVEALS ââ
const obs=new IntersectionObserver(e=>{e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('vis');obs.unobserve(x.target)}})},{threshold:.08,rootMargin:'0px 0px -40px 0px'});
document.querySelectorAll('.rv').forEach(el=>obs.observe(el));

// ââ NAV ââ
document.querySelectorAll('a[href^="#"]').forEach(a=>{a.addEventListener('click',e=>{const h=a.getAttribute('href');if(h==='#')return;e.preventDefault();document.querySelector(h)?.scrollIntoView({behavior:'smooth'})})});
addEventListener('scroll',()=>{document.querySelector('.nav-bg').style.background=scrollY>40?'var(--chrome-bg)':'var(--chrome-bg-soft)'});

</script>
@endverbatim
</body>
</html>
