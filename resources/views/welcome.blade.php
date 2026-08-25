<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>X Platforms &ndash; The Intelligence Layer</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
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
  --glow:0 0 60px rgba(79,143,255,.12);--glow2:0 0 80px rgba(79,143,255,.08);
  --glass-blur:none;
  --chrome-bg:rgba(5,7,14,.97);--chrome-bg-soft:rgba(5,7,14,.75);--panel-tint:rgba(9,13,25,.6);
}
.dp-card,.dp-head,.dp-scard,.bordered,.gmap-wrap,.hw,.monitor,.jsim,.jsim-output,.ind,.pz-card,.fc,.quiz,.tc,.faq-item,.pc,.foot,.chat-sidebar,.chat-card,.analysis-panel,.a-pie-card,.a-modal{backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur)}
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

/* DEMO MODAL */
.dp-wrap{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(5,7,14,.82);backdrop-filter:blur(14px);opacity:0;visibility:hidden;transition:opacity .28s,visibility .28s}
.dp-wrap.dp-open{opacity:1;visibility:visible}
.dp-wrap.dp-open .dp-card{transform:translateY(0) scale(1)}
.dp-card{background:var(--card);border:1px solid rgba(79,143,255,.18);border-radius:20px;width:100%;max-width:480px;max-height:92vh;overflow-y:auto;transform:translateY(20px) scale(.97);transition:transform .34s cubic-bezier(.16,1,.3,1);box-shadow:0 40px 100px rgba(0,0,0,.6);scrollbar-width:thin;scrollbar-color:rgba(79,143,255,.15) transparent;position:relative}
.dp-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#4f8fff,#818cf8,#38bdf8);border-radius:20px 20px 0 0;z-index:1}
.dp-head{padding:22px 26px 18px;border-bottom:1px solid rgba(79,143,255,.08);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:var(--card);z-index:10;border-radius:20px 20px 0 0}
.dp-brand{display:flex;align-items:center;gap:10px}
.dp-mark{width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#4f8fff,#818cf8);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;color:#fff;font-family:var(--f1);flex-shrink:0}
.dp-title{font-family:var(--f1);font-weight:700;font-size:15px;color:var(--white);letter-spacing:-.2px}
.dp-sub{font-family:var(--f1);font-size:12px;color:var(--g300);margin-top:1px}
.dp-close{width:30px;height:30px;border-radius:8px;border:none;background:rgba(255,255,255,.05);color:var(--g300);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;transition:all .2s;flex-shrink:0}
.dp-close:hover{background:rgba(255,255,255,.1);color:var(--white)}
.dp-badges{display:flex;gap:6px;padding:12px 26px;border-bottom:1px solid rgba(79,143,255,.05);flex-wrap:wrap}
.dp-badge{font-family:var(--fm);font-size:9.5px;padding:4px 11px;border-radius:5px;background:rgba(52,211,153,.07);border:1px solid rgba(52,211,153,.18);color:#34d399;letter-spacing:.3px}
.dp-si{display:flex;align-items:center;gap:8px;margin-bottom:22px}
.dp-si-dot{width:8px;height:8px;border-radius:50%;background:var(--g400);transition:all .3s}
.dp-si-dot.on{background:#4f8fff;box-shadow:0 0 10px rgba(79,143,255,.4);width:24px;border-radius:4px}
.dp-si-dot.dn{background:#34d399}
.dp-si-lbl{font-family:var(--fm);font-size:10px;color:var(--g300);margin-left:2px;letter-spacing:.3px}
.dp-body{padding:22px 26px 26px}
.dp-step{display:none}.dp-step.on{display:block;animation:dpIn .22s ease both}
@keyframes dpIn{from{opacity:0;transform:translateX(8px)}to{opacity:1;transform:translateX(0)}}
.dp-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.dp-grp{margin-bottom:14px}
.dp-lbl{display:block;font-family:var(--f1);font-size:12.5px;font-weight:500;color:var(--g200);margin-bottom:5px}
.dp-inp,.dp-sel,.dp-ta{width:100%;background:var(--bg2);border:1px solid var(--g500);border-radius:9px;padding:10px 13px;color:var(--white);font-family:var(--f1);font-size:13.5px;outline:none;transition:border-color .2s,box-shadow .2s}
.dp-inp::placeholder,.dp-ta::placeholder{color:var(--g400)}
.dp-inp:focus,.dp-sel:focus,.dp-ta:focus{border-color:#4f8fff;box-shadow:0 0 0 3px rgba(79,143,255,.1)}
.dp-inp.er,.dp-sel.er{border-color:#f472b6;box-shadow:0 0 0 3px rgba(244,114,182,.1)}
.dp-sel{-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%234a5572' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:34px;cursor:pointer}
.dp-sel option{background:var(--bg2)}
.dp-ta{resize:vertical;min-height:76px;max-height:130px}
.dp-btns{display:flex;gap:10px;margin-top:6px}
.dp-btn{flex:1;padding:12px 18px;background:linear-gradient(135deg,#4f8fff,#2563eb);color:#fff;border:none;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;font-family:var(--f1);display:flex;align-items:center;justify-content:center;gap:7px;transition:all .22s;box-shadow:0 4px 18px rgba(79,143,255,.25)}
.dp-btn:hover{transform:translateY(-1px);box-shadow:0 8px 28px rgba(79,143,255,.4)}
.dp-btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
.dp-bk{padding:12px 14px;background:transparent;border:1px solid #1e2a42;border-radius:10px;color:var(--g300);font-weight:500;font-size:14px;cursor:pointer;font-family:var(--f1);transition:all .2s;flex-shrink:0}
.dp-bk:hover{border-color:#4f8fff;color:var(--white)}
.dp-cal-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.dp-cal-hd span{font-family:var(--f1);font-weight:600;font-size:13.5px;color:var(--white)}
.dp-arrow{width:28px;height:28px;border-radius:7px;background:rgba(255,255,255,.04);border:1px solid #1e2a42;color:var(--g300);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;transition:all .2s}
.dp-arrow:hover{border-color:#4f8fff;color:var(--white)}
.dp-dow{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;margin-bottom:3px}
.dp-dh{font-family:var(--fm);font-size:9px;color:var(--g300);text-align:center;padding:4px 0;letter-spacing:.5px}
.dp-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;margin-bottom:14px}
.dp-day{aspect-ratio:1;display:flex;align-items:center;justify-content:center;border-radius:7px;font-family:var(--f1);font-size:12.5px;color:var(--g300);cursor:pointer;transition:all .18s;border:1px solid transparent}
.dp-day:hover:not(.dp-off):not(.dp-blank){background:rgba(79,143,255,.1);color:var(--white);border-color:rgba(79,143,255,.18)}
.dp-day.dp-today{color:#4f8fff;font-weight:600}
.dp-day.dp-sel{background:var(--blue)!important;color:#fff!important;border-color:transparent!important;font-weight:600;box-shadow:0 2px 10px rgba(79,143,255,.4)}
.dp-day.dp-off{opacity:.2;cursor:default}
.dp-day.dp-blank{cursor:default}
.dp-times{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;margin-top:6px}
.dp-t{padding:9px 6px;text-align:center;border-radius:8px;border:1px solid #1e2a42;background:var(--bg2);font-family:var(--f1);font-size:12px;color:var(--g300);cursor:pointer;transition:all .18s}
.dp-t:hover:not(.dp-toff){border-color:#4f8fff;color:var(--white);background:rgba(79,143,255,.06)}
.dp-t.dp-tsel{border-color:#4f8fff;background:rgba(79,143,255,.12);color:#4f8fff;font-weight:600}
.dp-t.dp-toff{opacity:.25;cursor:default}
.dp-note{text-align:center;font-family:var(--f1);font-size:11.5px;color:var(--g300);margin-top:12px}
.dp-note a{color:#4f8fff}
.dp-success{display:none;padding:16px 0 8px;text-align:center}
.dp-success.on{display:block;animation:dpIn .3s ease both}
.dp-sico{width:62px;height:62px;border-radius:50%;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
.dp-sico svg{width:28px;height:28px;stroke:#34d399;fill:none;stroke-width:2.2}
.dp-stitle{font-family:var(--f1);font-weight:700;font-size:19px;color:var(--white);letter-spacing:-.2px;margin-bottom:7px}
.dp-ssub{font-family:var(--f1);font-size:13.5px;color:var(--g300);line-height:1.65;max-width:310px;margin:0 auto 18px}
.dp-scard{background:var(--bg2);border:1px solid rgba(79,143,255,.09);border-radius:12px;padding:14px 16px;text-align:left;margin-bottom:18px}
.dp-srow{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid rgba(79,143,255,.06);font-family:var(--f1);font-size:13px}
.dp-srow:last-child{border-bottom:none}
.dp-slbl{color:var(--g300)}.dp-sval{color:var(--g100);font-weight:500}
.dp-slink{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:9px;background:rgba(79,143,255,.08);border:1px solid rgba(79,143,255,.15);color:#4f8fff;font-family:var(--f1);font-size:13px;font-weight:500;transition:background .2s}
.dp-slink:hover{background:rgba(79,143,255,.14)}
@media(max-width:520px){.dp-row{grid-template-columns:1fr}.dp-times{grid-template-columns:repeat(2,1fr)}}

/* HERO */
.hero{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:140px 40px 80px}
.hero-badge{display:inline-flex;align-items:center;gap:10px;padding:7px 18px 7px 10px;background:var(--blue-g);border:1px solid var(--brd2);border-radius:100px;font-family:var(--fm);font-size:11px;letter-spacing:1px;text-transform:uppercase;color:var(--blue);margin-bottom:36px;opacity:0;animation:fadeUp .7s var(--ease) .2s forwards}
.badge-dot{width:8px;height:8px;border-radius:50%;background:var(--blue);box-shadow:0 0 12px var(--blue);animation:blink 2.5s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
.hero h1{font-weight:800;font-size:clamp(44px,6vw,80px);line-height:1.05;letter-spacing:-2.5px;max-width:820px;margin-bottom:28px;opacity:0;animation:fadeUp .7s var(--ease) .35s forwards}
.hero h1 span{background:linear-gradient(135deg,var(--blue),var(--cyan),var(--violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-p{font-size:17px;line-height:1.75;color:var(--g200);max-width:520px;font-weight:350;margin-bottom:48px;opacity:0;animation:fadeUp .7s var(--ease) .5s forwards}
.hero-btns{display:flex;gap:14px;opacity:0;animation:fadeUp .7s var(--ease) .65s forwards}
.btn-b{display:inline-flex;align-items:center;gap:8px;padding:15px 34px;border-radius:12px;font-weight:600;font-size:14.5px;border:none;cursor:pointer;transition:all .25s;font-family:var(--f1)}
.btn-fill{background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 32px rgba(79,143,255,.3)}
.btn-fill:hover{transform:translateY(-2px);box-shadow:0 8px 48px rgba(79,143,255,.4)}
.btn-g{background:rgba(255,255,255,.04);color:var(--g100);border:1px solid var(--g500)}
.btn-g:hover{border-color:var(--blue);background:var(--blue-g2)}

/* PIPELINE */
.pipeline{margin-top:72px;display:flex;align-items:center;gap:0;opacity:0;animation:fadeUp .8s var(--ease) .8s forwards;flex-wrap:wrap;justify-content:center}
.pipe-node{display:flex;flex-direction:column;align-items:center;gap:8px;padding:14px 12px;min-width:96px}
.pipe-icon{width:44px;height:44px;border-radius:12px;border:1px solid var(--brd2);background:var(--blue-g);display:flex;align-items:center;justify-content:center;transition:all .35s}
.pipe-node:hover .pipe-icon{border-color:var(--blue);box-shadow:0 0 24px rgba(79,143,255,.2);transform:scale(1.08)}
.pipe-icon svg{width:20px;height:20px;stroke:var(--blue);fill:none;stroke-width:1.5}
.pipe-label{font-size:10px;font-weight:500;color:var(--g300);letter-spacing:.5px}
.pipe-num{font-family:var(--fm);font-size:9px;color:var(--g500);letter-spacing:1px}
.pipe-arrow svg{width:16px;height:16px;stroke:var(--blue);stroke-width:1.5;fill:none;opacity:.3}

/* GLOBAL COUNTER */
.gcounter{position:relative;z-index:1;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--panel-tint);backdrop-filter:blur(20px);padding:40px;text-align:center}
.gc-num{font-weight:800;font-size:clamp(36px,5vw,64px);letter-spacing:-2px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-variant-numeric:tabular-nums}
.gc-label{font-family:var(--fm);font-size:11px;color:var(--g400);letter-spacing:2px;text-transform:uppercase;margin-top:8px}
.gc-sub{font-size:13px;color:var(--g500);margin-top:4px}

/* SECTIONS */
.sec{position:relative;z-index:1;padding:120px 40px;max-width:var(--mw);margin:0 auto}
.sec-f{position:relative;z-index:1;padding:120px 40px}
.sec-f .sw{max-width:var(--mw);margin:0 auto}
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:14px;display:flex;align-items:center;gap:10px}
.stag::before{content:'';width:16px;height:1px;background:var(--blue)}
.sh{font-weight:700;font-size:clamp(30px,3.6vw,46px);letter-spacing:-1.5px;line-height:1.12;margin-bottom:16px}
.ss{font-size:16px;color:var(--g300);max-width:460px;line-height:1.7;font-weight:350}
.stop{margin-bottom:64px}
.bordered{border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--bg2)}

/* GLOBAL MAP */
.gmap-wrap{position:relative;border-radius:20px;overflow:hidden;border:1px solid var(--brd);background:var(--card);height:420px}
canvas#gmap{width:100%;height:100%}
.gmap-legend{position:absolute;bottom:16px;left:16px;display:flex;gap:16px;font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:.5px}
.gmap-legend span{display:flex;align-items:center;gap:6px}
.gmap-legend .gl-dot{width:6px;height:6px;border-radius:50%}
.gmap-stats{position:absolute;top:16px;right:16px;display:flex;flex-direction:column;gap:8px}
.gmap-stat{background:var(--panel-tint);backdrop-filter:blur(12px);padding:10px 16px;border-radius:8px;border:1px solid var(--brd);font-family:var(--fm);font-size:11px}
.gmap-stat strong{color:var(--blue);font-weight:600}

/* HOW */
.how-row{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid var(--brd);border-radius:16px;overflow:hidden}
.hw{padding:40px 28px;border-right:1px solid var(--brd);transition:background .3s}
.hw:last-child{border-right:none}
.hw:hover{background:var(--card-h)}
.hw-n{font-family:var(--f2);font-size:38px;font-weight:600;font-style:italic;color:var(--blue);margin-bottom:18px;line-height:1}
.hw-t{font-weight:600;font-size:15px;margin-bottom:8px}
.hw-d{font-size:13px;color:var(--g300);line-height:1.65;font-weight:350}

/* MONITOR */
.monitor{border-radius:20px;overflow:hidden;border:1px solid var(--brd);background:var(--card);box-shadow:var(--glow2)}
.mon-bar{display:flex;align-items:center;gap:8px;padding:12px 20px;border-bottom:1px solid var(--brd);background:rgba(255,255,255,.015)}
.mon-bar .md{width:8px;height:8px;border-radius:50%}
.mon-bar-title{font-family:var(--fm);font-size:11px;color:var(--g400);margin-left:8px}
.mon-bar-status{margin-left:auto;display:flex;align-items:center;gap:6px;font-family:var(--fm);font-size:10px;color:var(--emerald)}
.mon-bar-status .pulse-live{width:6px;height:6px;border-radius:50%;background:var(--emerald);box-shadow:0 0 8px var(--emerald);animation:blink 2s ease-in-out infinite}
.mon-body{display:grid;grid-template-columns:1fr 280px;min-height:340px}
.mon-feed{border-right:1px solid var(--brd);overflow:hidden}
.mon-feed-inner{padding:16px 20px;display:flex;flex-direction:column;gap:6px;font-family:var(--fm);font-size:11.5px;line-height:1.7;color:var(--g300);max-height:340px;overflow:hidden}
.mon-line{opacity:0;animation:feedIn .4s var(--ease) forwards;display:flex;gap:10px;align-items:flex-start}
.mon-line .ml-time{color:var(--g500);min-width:52px;flex-shrink:0}
.mon-line .ml-tag{padding:1px 6px;border-radius:3px;font-size:9.5px;letter-spacing:.5px;min-width:48px;text-align:center;flex-shrink:0}
.ml-ingest{background:rgba(79,143,255,.1);color:var(--blue)}
.ml-unify{background:rgba(56,189,248,.1);color:var(--cyan)}
.ml-map{background:rgba(244,114,182,.1);color:var(--rose)}
.ml-detect{background:rgba(251,191,36,.1);color:var(--amber)}
.ml-predict{background:rgba(129,140,248,.1);color:var(--violet)}
.ml-plan{background:rgba(52,211,153,.1);color:var(--emerald)}
.ml-exec{background:rgba(251,146,60,.1);color:#fb923c}
.ml-learn{background:rgba(96,165,250,.1);color:#60a5fa}
@keyframes feedIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.mon-side{padding:20px;display:flex;flex-direction:column;gap:16px}
.mon-metric{padding:14px 16px;border-radius:10px;background:rgba(255,255,255,.02);border:1px solid var(--brd)}
.mon-metric .mm-label{font-family:var(--fm);font-size:9px;letter-spacing:1.2px;text-transform:uppercase;color:var(--g400);margin-bottom:6px}
.mon-metric .mm-val{font-weight:700;font-size:22px;letter-spacing:-.5px}
.mon-metric .mm-bar{height:4px;border-radius:2px;background:var(--g600);margin-top:8px;overflow:hidden}
.mon-metric .mm-fill{height:100%;border-radius:2px;transition:width 1s var(--ease)}
.mon-layers{display:flex;flex-direction:column;gap:4px;margin-top:auto}
.mon-layer{display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:6px;font-family:var(--fm);font-size:10px;color:var(--g300);background:rgba(255,255,255,.015);border:1px solid transparent;transition:all .3s}
.mon-layer.active{border-color:var(--brd2);background:var(--blue-g)}
.mon-layer .ml-dot{width:5px;height:5px;border-radius:50%;flex-shrink:0}
.mon-layer .ml-status{margin-left:auto;font-size:9px}

/* JOURNEY SIMULATOR */
.jsim{background:var(--card);border:1px solid var(--brd);border-radius:20px;padding:40px;overflow:hidden}
.jsim-controls{display:flex;align-items:center;gap:16px;margin-bottom:32px;flex-wrap:wrap}
.jsim-select{background:var(--bg3);border:1px solid var(--g500);border-radius:8px;padding:10px 16px;color:var(--white);font-family:var(--f1);font-size:14px;cursor:pointer;outline:none;min-width:180px}
.jsim-select option{background:var(--bg3);color:var(--white)}
.jsim-run{padding:10px 24px;background:var(--blue);color:#fff;border:none;border-radius:8px;font-weight:600;font-size:13px;cursor:pointer;font-family:var(--f1);transition:all .2s}
.jsim-run:hover{box-shadow:0 0 24px rgba(79,143,255,.3)}
.jsim-run:disabled{opacity:.4;cursor:not-allowed}
.jsim-pipeline{display:flex;gap:4px;margin-bottom:24px;overflow-x:auto}
.jsim-step{flex:1;min-width:100px;padding:16px 12px;border-radius:10px;border:1px solid var(--brd);background:var(--bg3);text-align:center;transition:all .5s var(--ease);position:relative}
.jsim-step.active{border-color:var(--blue);background:var(--blue-g);box-shadow:0 0 20px rgba(79,143,255,.1)}
.jsim-step.done{border-color:var(--emerald);background:rgba(52,211,153,.06)}
.jsim-step .js-num{font-family:var(--fm);font-size:9px;color:var(--g500);letter-spacing:1px;margin-bottom:4px}
.jsim-step .js-name{font-size:11px;font-weight:500;color:var(--g200)}
.jsim-step.active .js-name{color:var(--blue)}
.jsim-step.done .js-name{color:var(--emerald)}
.jsim-output{background:var(--bg3);border:1px solid var(--brd);border-radius:12px;padding:20px;font-family:var(--fm);font-size:12px;color:var(--g300);line-height:1.8;min-height:120px;white-space:pre-line}

/* BEFORE/AFTER TABLE */
.ba-toggle{display:flex;align-items:center;gap:12px;margin-bottom:24px}
.ba-label{font-size:14px;font-weight:500;color:var(--g300)}
.ba-label.active-l{color:var(--white)}
.ba-switch{width:52px;height:28px;border-radius:14px;background:var(--g600);cursor:pointer;position:relative;transition:background .3s;border:none;padding:0}
.ba-switch.on{background:var(--blue)}
.ba-switch::after{content:'';position:absolute;top:3px;left:3px;width:22px;height:22px;border-radius:50%;background:#fff;transition:transform .3s var(--ease)}
.ba-switch.on::after{transform:translateX(24px)}
.ba-table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid var(--brd);border-radius:12px;overflow:hidden;font-size:13px}
.ba-table th{background:var(--bg3);padding:12px 16px;text-align:left;font-family:var(--fm);font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--g400);font-weight:500;border-bottom:1px solid var(--brd)}
.ba-table td{padding:11px 16px;border-bottom:1px solid var(--brd);color:var(--g200);background:var(--card)}
.ba-table tr:last-child td{border-bottom:none}
.ba-raw{color:var(--rose)!important;font-family:var(--fm);font-size:11px}
.ba-clean{color:var(--emerald)!important}
.ba-pred{background:var(--blue-g)!important;font-weight:500;color:var(--blue)!important}

/* INDUSTRIES */
.ind-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px}
.ind{padding:24px 16px;border-radius:12px;border:1px solid var(--brd);background:var(--card);text-align:center;transition:all .35s var(--ease);cursor:default}
.ind:hover{border-color:var(--brd2);background:var(--card-h);transform:translateY(-3px);box-shadow:var(--glow2)}
.ind-i{width:40px;height:40px;margin:0 auto 12px;border-radius:10px;background:var(--blue-g);display:flex;align-items:center;justify-content:center}
.ind-i svg{width:18px;height:18px;stroke:var(--blue);fill:none;stroke-width:1.5}
.ind-n{font-size:12.5px;font-weight:500;color:var(--g100)}

/* PERSONALIZER */
.pz-bar{display:flex;align-items:center;gap:12px;margin-bottom:40px;padding:16px 20px;border-radius:12px;background:var(--blue-g);border:1px solid var(--brd2)}
.pz-bar label{font-size:13px;color:var(--g200);font-weight:500}
.pz-select{background:var(--bg3);border:1px solid var(--g500);border-radius:8px;padding:8px 14px;color:var(--white);font-family:var(--f1);font-size:13px;cursor:pointer;outline:none}
.pz-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.pz-card{background:var(--card);border:1px solid var(--brd);border-radius:12px;padding:20px;text-align:center;transition:all .3s}
.pz-card .pz-n{font-weight:700;font-size:28px;letter-spacing:-1px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:4px}
.pz-card .pz-l{font-family:var(--fm);font-size:9px;color:var(--g400);letter-spacing:1px;text-transform:uppercase}

/* FEATURES */
.feat-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.fc{background:var(--card);border:1px solid var(--brd);border-radius:16px;padding:36px;transition:all .35s var(--ease)}
.fc:hover{border-color:var(--brd2);background:var(--card-h)}
.fc.big{grid-column:span 2}
.fc-bar{width:32px;height:3px;border-radius:2px;background:linear-gradient(90deg,var(--blue),var(--violet));margin-bottom:22px}
.fc h3{font-weight:600;font-size:18px;letter-spacing:-.3px;margin-bottom:10px}
.fc p{font-size:13.5px;color:var(--g300);line-height:1.7;font-weight:350}
.fc-metrics{display:flex;gap:40px;margin-top:24px;flex-wrap:wrap}
.fm-n{font-weight:700;font-size:28px;letter-spacing:-1px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.fm-l{font-family:var(--fm);font-size:10px;color:var(--g400);margin-top:2px;letter-spacing:.8px;text-transform:uppercase}

/* QUIZ */
.quiz{background:var(--card);border:1px solid var(--brd);border-radius:20px;padding:48px}
.quiz-q{margin-bottom:32px}
.quiz-q label{display:block;font-size:14px;font-weight:500;margin-bottom:12px}
.quiz-opts{display:flex;gap:8px;flex-wrap:wrap}
.quiz-opt{padding:10px 20px;border-radius:8px;border:1px solid var(--g500);background:transparent;color:var(--g200);font-family:var(--f1);font-size:13px;cursor:pointer;transition:all .2s}
.quiz-opt:hover{border-color:var(--blue);color:var(--white)}
.quiz-opt.sel{border-color:var(--blue);background:var(--blue-g);color:var(--blue)}
.quiz-submit{margin-top:16px;padding:14px 36px;background:var(--blue);color:#fff;border:none;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;font-family:var(--f1);transition:all .2s}
.quiz-submit:hover{box-shadow:0 0 24px rgba(79,143,255,.3)}
.quiz-result{display:none;text-align:center;padding:40px}
.qr-score{font-weight:800;font-size:72px;letter-spacing:-3px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.qr-label{font-family:var(--fm);font-size:11px;color:var(--g400);letter-spacing:1.5px;text-transform:uppercase;margin:8px 0 20px}
.qr-text{font-size:15px;color:var(--g200);line-height:1.7;max-width:480px;margin:0 auto}
.qr-bars{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-top:28px;max-width:480px;margin-left:auto;margin-right:auto}
.qr-bar-item{text-align:center}
.qr-bar-track{height:60px;background:var(--g600);border-radius:6px;overflow:hidden;display:flex;align-items:flex-end;margin-bottom:6px}
.qr-bar-fill{width:100%;border-radius:6px;transition:height .8s var(--ease)}
.qr-bar-label{font-family:var(--fm);font-size:8px;color:var(--g400);letter-spacing:.5px}

/* TESTIMONIALS */
.test-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.tc{background:var(--card);border:1px solid var(--brd);border-radius:16px;padding:32px;transition:all .3s;display:flex;flex-direction:column}
.tc:hover{border-color:var(--brd2);background:var(--card-h)}
.tc-stars{color:var(--amber);font-size:14px;letter-spacing:2px;margin-bottom:16px}
.tc-q{font-family:var(--f2);font-size:15px;font-style:italic;line-height:1.65;color:var(--g100);flex:1;margin-bottom:20px}
.tc-author{display:flex;align-items:center;gap:12px;border-top:1px solid var(--brd);padding-top:16px}
.tc-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--violet));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#fff;flex-shrink:0}
.tc-name{font-size:13px;font-weight:600}
.tc-role{font-size:11.5px;color:var(--g400)}

/* FAQ */
.faq-list{max-width:720px;margin:0 auto;display:flex;flex-direction:column;gap:8px}
.faq-item{border:1px solid var(--brd);border-radius:12px;overflow:hidden;background:var(--card)}
.faq-item.open{border-color:var(--brd2)}
.faq-q{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;cursor:pointer;font-weight:500;font-size:15px;color:var(--g100);user-select:none}
.faq-q svg{width:18px;height:18px;stroke:var(--g400);fill:none;stroke-width:2;transition:transform .3s;flex-shrink:0}
.faq-item.open .faq-q svg{transform:rotate(45deg);stroke:var(--blue)}
.faq-a{max-height:0;overflow:hidden;transition:max-height .4s var(--ease)}
.faq-item.open .faq-a{max-height:300px;padding:0 24px 20px}
.faq-a p{font-size:14px;color:var(--g300);line-height:1.7}

/* CTA */
.cta{position:relative;z-index:1;padding:140px 40px;text-align:center}
.cta::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:600px;height:600px;background:radial-gradient(circle,rgba(79,143,255,.06),transparent 65%);border-radius:50%;pointer-events:none}
.cta-h{font-weight:800;font-size:clamp(34px,4.5vw,56px);letter-spacing:-2px;line-height:1.1;margin-bottom:20px;position:relative}
.cta-h span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.cta-p{font-size:17px;color:var(--g300);max-width:440px;margin:0 auto 40px;line-height:1.7;font-weight:350;position:relative}

/* PRICING */
.pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;max-width:1000px;margin:0 auto}
.pc{background:var(--card);border:1px solid var(--brd);border-radius:20px;padding:36px 32px;display:flex;flex-direction:column;position:relative;transition:border-color .3s,box-shadow .3s}
.pc:hover{border-color:var(--brd2);box-shadow:var(--glow)}
.pc.popular{border-color:var(--blue);box-shadow:0 0 40px rgba(79,143,255,.12)}
.pc-badge{position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;padding:5px 16px;border-radius:100px;white-space:nowrap}
.pc-name{font-family:var(--fm);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--blue);margin-bottom:12px}
.pc-price{font-weight:800;font-size:48px;letter-spacing:-2px;line-height:1;margin-bottom:4px}
.pc-price sup{font-size:22px;letter-spacing:0;vertical-align:top;margin-top:8px;display:inline-block;font-weight:600;color:var(--g200)}
.pc-period{font-size:13px;color:var(--g400);margin-bottom:6px}
.pc-desc{font-size:13.5px;color:var(--g300);line-height:1.6;margin-bottom:28px;padding-bottom:28px;border-bottom:1px solid var(--brd)}
.pc-features{list-style:none;display:flex;flex-direction:column;gap:12px;flex:1;margin-bottom:32px}
.pc-features li{display:flex;align-items:flex-start;gap:10px;font-size:13.5px;color:var(--g200);line-height:1.45}
.pc-features li::before{content:'';width:16px;height:16px;border-radius:50%;background:rgba(79,143,255,.12);border:1px solid var(--blue);flex-shrink:0;margin-top:1px;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='20 6 9 17 4 12' stroke='%234f8fff' stroke-width='2.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");background-size:10px;background-repeat:no-repeat;background-position:center}
.pc-cta{display:block;text-align:center;padding:13px 24px;border-radius:10px;font-weight:600;font-size:14px;transition:all .25s}
.pc-cta.outline{border:1px solid var(--g500);color:var(--g100)}.pc-cta.outline:hover{border-color:var(--blue);background:var(--blue-g2)}
.pc-cta.solid{background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 24px rgba(79,143,255,.25)}.pc-cta.solid:hover{transform:translateY(-2px);box-shadow:0 8px 40px rgba(79,143,255,.4)}
@media(max-width:900px){.pricing-grid{grid-template-columns:1fr}}

/* FOOTER */
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2)}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:260px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}

/* TOAST */
.toast-container{position:fixed;bottom:24px;left:24px;z-index:90;display:flex;flex-direction:column;gap:8px;pointer-events:none}
.toast{background:var(--card);backdrop-filter:blur(16px);border:1px solid var(--brd2);border-radius:12px;padding:14px 20px;display:flex;align-items:center;gap:12px;font-size:13px;color:var(--g100);box-shadow:var(--glow);animation:toastIn .5s var(--ease) forwards,toastOut .4s var(--ease) 4s forwards;max-width:360px}
.toast-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
@keyframes toastIn{from{opacity:0;transform:translateY(16px) scale(.95)}to{opacity:1;transform:translateY(0) scale(1)}}
@keyframes toastOut{to{opacity:0;transform:translateX(-20px)}}

/* ASK MIRA */
#ask-mira .sh span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.chat-page-wrap{position:relative;z-index:1;max-width:1020px;margin:0 auto}
.chat-layout{display:flex;gap:16px;align-items:flex-start}
.chat-sidebar{width:230px;flex-shrink:0;background:var(--card);border:1px solid var(--brd2);border-radius:16px;padding:18px;display:flex;flex-direction:column;gap:18px;max-height:70vh;overflow-y:auto}
.side-lbl{font-family:var(--fm);font-size:10.5px;letter-spacing:1px;text-transform:uppercase;color:var(--g400);margin-bottom:8px}
.side-select{width:100%;background:var(--bg3);border:1px solid var(--g500);border-radius:8px;padding:9px 10px;color:var(--white);font-family:var(--f1);font-size:13px;outline:none}
.side-select:focus{border-color:var(--blue)}
.side-starters{display:flex;flex-direction:column;gap:8px}
.side-starter{background:var(--bg3);border:1px solid var(--g500);border-radius:10px;padding:10px 12px;color:var(--g100);font-family:var(--f1);font-size:12.5px;line-height:1.5;text-align:left;cursor:pointer;transition:all .2s}
.side-starter:hover{border-color:var(--brd2);color:var(--white);background:rgba(79,143,255,.08)}
.chat-card{flex:1;min-width:0;background:var(--card);border:1px solid var(--brd2);border-radius:16px;box-shadow:0 0 60px rgba(79,143,255,.12);display:flex;flex-direction:column;height:70vh;min-height:480px;overflow:hidden}
.chat-tabs{display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--brd);flex-shrink:0;padding:12px 16px}
.chat-tab-box{display:flex;align-items:center;flex:1;min-width:0;background:var(--bg3);border:1px solid var(--brd2);border-radius:10px;padding:2px 6px 2px 4px;transition:all .2s}
.chat-tab-box.active{background:rgba(79,143,255,.1);border-color:rgba(79,143,255,.35)}
.chat-tab-box:not(.active):hover{border-color:var(--g500)}
.chat-tab{display:flex;align-items:center;gap:7px;flex:1;min-width:0;background:none;border:none;color:var(--g400);font-family:var(--f1);font-size:13px;font-weight:600;padding:9px 10px;cursor:pointer;transition:color .2s;text-align:left}
.chat-tab svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:2;flex-shrink:0}
.chat-tab-box.active .chat-tab{color:var(--blue)}
.chat-tab-box:not(.active):hover .chat-tab{color:var(--g200)}
.chat-reset{background:none;border:none;color:var(--g400);cursor:pointer;padding:8px;border-radius:8px;transition:all .2s;flex-shrink:0}
.chat-reset:hover{color:var(--white);background:rgba(79,143,255,.12)}
.chat-reset svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:2;display:block}
.chat-tab-panel{flex:1;display:flex;flex-direction:column;min-height:0}
.chat-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 20px;border-top:1px solid var(--brd);font-size:11px;color:var(--g400);flex-shrink:0;flex-wrap:wrap}
.mira-status{display:inline-flex;align-items:center;gap:6px;font-weight:600;padding:3px 10px;border-radius:20px;background:var(--bg3);margin-left:auto}
.mira-status.live{color:var(--emerald)}
.mira-status.basic{color:var(--amber)}
.status-dot{width:6px;height:6px;border-radius:50%;background:currentColor;box-shadow:0 0 6px currentColor}
.analyze-form{flex:1;display:flex;flex-direction:column;justify-content:center;padding:24px 22px;gap:14px}
.analyze-hint{font-size:13.5px;color:var(--g300);line-height:1.6;text-align:center;max-width:420px;margin:0 auto}
.analyze-status{font-size:12.5px;color:var(--g400);text-align:center;min-height:1em}
.analyze-status.err{color:var(--rose)}
.chat-messages{flex:1;overflow-y:auto;padding:20px 22px;display:flex;flex-direction:column;gap:14px}
.chat-msg{max-width:80%;padding:12px 16px;border-radius:12px;font-size:14px;line-height:1.65;white-space:pre-wrap;word-wrap:break-word}
.chat-msg.bot{background:var(--bg3);color:var(--g100);align-self:flex-start;border-bottom-left-radius:4px}
.chat-msg.user{background:var(--blue);color:#fff;align-self:flex-end;border-bottom-right-radius:4px}
.chat-msg.typing{color:var(--g400);font-style:italic}
.chat-msg a{color:var(--cyan);text-decoration:underline}
.chat-input-wrap{padding:16px 20px;border-top:1px solid var(--brd);display:flex;gap:10px;flex-shrink:0}
.chat-input{flex:1;background:var(--bg3);border:1px solid var(--g500);border-radius:10px;padding:12px 16px;color:var(--white);font-family:var(--f1);font-size:14px;outline:none;resize:none;max-height:140px}
.chat-input:focus{border-color:var(--blue)}
.chat-send{width:42px;height:42px;border-radius:10px;background:var(--blue);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;align-self:flex-end;transition:opacity .2s}
.chat-send:hover{opacity:.9}
.chat-send svg{width:18px;height:18px;fill:#fff}
.analysis-panel{display:none;margin-top:16px;background:var(--card);border:1px solid var(--brd2);border-radius:16px;padding:24px}
.analysis-panel.show{display:block}
.a-summary{padding-bottom:16px;border-bottom:1px solid var(--brd)}
.analysis-url{font-size:13px;color:var(--g300);word-break:break-all}
.a-pie-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;padding-top:16px}
.a-pie-card{background:var(--bg3);border:1px solid var(--g600);border-radius:14px;padding:16px;display:flex;flex-direction:column;align-items:center;gap:10px}
.a-pie-svg{width:96px;height:96px}
.a-pie-track{fill:none;stroke:var(--g600);stroke-width:9}
.a-pie-fill{fill:none;stroke-width:9;stroke-linecap:round;transform:rotate(-90deg);transform-origin:50px 50px;transition:stroke-dashoffset .6s var(--ease)}
.a-pie-pct{font-family:var(--fm);font-size:17px;font-weight:800;fill:var(--white)}
.a-pie-label{font-size:13px;font-weight:700;color:var(--white);text-align:center}
.a-pie-checks{width:100%;display:flex;flex-direction:column;gap:4px;padding-top:8px;border-top:1px solid var(--brd)}
.a-pie-check-row{display:flex;justify-content:space-between;gap:8px;font-size:11px}
.a-pie-check-name{color:var(--g300)}
.a-pie-check-detail{color:var(--g100);font-weight:600;text-align:right;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px}
.a-pie-card{cursor:pointer;transition:transform .15s,border-color .15s}
.a-pie-card:hover{transform:translateY(-2px);border-color:var(--blue)}
.chat-upsell-aside{display:none;width:230px;flex-shrink:0;flex-direction:column;justify-content:center;gap:16px;background:linear-gradient(135deg,rgba(79,143,255,.1),rgba(56,189,248,.06));border:1px solid rgba(79,143,255,.3);border-radius:16px;padding:20px}
.chat-upsell-aside.show{display:flex}
.a-upsell-text{font-size:13px;color:var(--g200);line-height:1.5}
.a-upsell-text strong{color:var(--white);display:block;margin-bottom:4px;font-size:14.5px}
.a-upsell-btn{background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;font-weight:600;font-size:13px;padding:10px 16px;border-radius:10px;white-space:nowrap;text-align:center;transition:transform .2s,box-shadow .2s}
.a-upsell-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(79,143,255,.35)}
.a-modal-overlay{display:none;position:fixed;inset:0;background:rgba(6,10,20,.75);backdrop-filter:blur(4px);z-index:200;align-items:center;justify-content:center;padding:20px}
.a-modal-overlay.show{display:flex}
.a-modal{background:var(--card);border:1px solid var(--brd2);border-radius:16px;padding:28px;max-width:440px;width:100%;max-height:80vh;overflow-y:auto;position:relative;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.a-modal-close{position:absolute;top:14px;right:14px;background:none;border:none;color:var(--g400);font-size:22px;line-height:1;cursor:pointer;padding:4px 8px;border-radius:8px}
.a-modal-close:hover{color:var(--white);background:rgba(79,143,255,.08)}
.a-modal-header{display:flex;align-items:center;gap:16px;padding-bottom:16px;border-bottom:1px solid var(--brd);margin-bottom:14px}
.a-modal-pct{font-family:var(--fm);font-size:26px;font-weight:800;flex-shrink:0}
.a-modal-title{font-size:17px;font-weight:700;color:var(--white)}
.a-modal-desc{font-size:12.5px;color:var(--g300);margin-top:4px;line-height:1.5}
.a-modal-checks{display:flex;flex-direction:column;gap:10px}
.a-modal-check-row{display:flex;align-items:center;justify-content:space-between;gap:12px;font-size:13px;padding:6px 0;border-bottom:1px solid var(--brd)}
.a-modal-check-row:last-child{border-bottom:none}
.a-modal-check-left{display:flex;align-items:center;gap:8px;min-width:0}
.a-modal-check-badge{font-family:var(--fm);font-size:9px;font-weight:700;letter-spacing:.5px;padding:2px 6px;border-radius:5px;flex-shrink:0;white-space:nowrap}
.a-modal-check-badge.pass{background:rgba(52,211,153,.15);color:var(--emerald)}
.a-modal-check-badge.warn{background:rgba(251,191,36,.15);color:var(--amber)}
.a-modal-check-badge.fail{background:rgba(244,114,182,.15);color:var(--rose)}
.a-modal-check-badge.info{background:rgba(148,163,184,.15);color:var(--g300)}
.a-modal-check-name{color:var(--g200)}
.a-modal-check-detail{color:var(--g100);font-weight:600;text-align:right;flex-shrink:0}
.lead-modal-overlay{display:none;position:fixed;inset:0;background:rgba(6,10,20,.75);backdrop-filter:blur(4px);z-index:210;align-items:center;justify-content:center;padding:20px}
.lead-modal-overlay.show{display:flex}
.lead-modal{background:var(--card);border:1px solid var(--brd2);border-radius:16px;padding:28px;max-width:380px;width:100%;position:relative;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.lead-modal-close{position:absolute;top:14px;right:14px;background:none;border:none;color:var(--g400);font-size:22px;line-height:1;cursor:pointer;padding:4px 8px;border-radius:8px}
.lead-modal-close:hover{color:var(--white);background:rgba(79,143,255,.08)}
.lead-modal-title{font-size:17px;font-weight:700;color:var(--white);margin-bottom:6px}
.lead-modal-sub{font-size:12.5px;color:var(--g300);line-height:1.5;margin-bottom:18px}
.lead-modal-field{margin-bottom:14px}
.lead-modal-field label{display:block;font-size:12px;font-weight:600;color:var(--g200);margin-bottom:6px}
.lead-modal-field input{width:100%;background:var(--bg3);border:1px solid var(--g500);border-radius:9px;padding:11px 14px;color:var(--white);font-family:var(--f1);font-size:13.5px;outline:none;transition:border-color .2s}
.lead-modal-field input:focus{border-color:var(--blue)}
.lead-modal-error{font-size:12px;color:var(--rose);min-height:1em;margin-bottom:8px}
.lead-modal-submit{width:100%;padding:12px;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;border:none;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;font-family:var(--f1);transition:opacity .2s}
.lead-modal-submit:hover{opacity:.9}
.lead-modal-submit:disabled{opacity:.6;cursor:not-allowed}
@media(max-width:768px){
  .chat-layout{flex-direction:column}
  .chat-sidebar{width:100%;max-height:none}
  .chat-upsell-aside{width:100%}
}
@media(max-width:640px){
  .chat-card{height:65vh}
}
@media(max-width:480px){
  .a-pie-grid{grid-template-columns:1fr}
  .a-modal{padding:20px}
  .a-upsell{flex-direction:column;align-items:flex-start}
  .a-upsell-btn{width:100%;text-align:center}
}

/* ANIM & RESPONSIVE */
@keyframes fadeUp{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:translateY(0)}}
.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}
@media(max-width:1024px){
  .hero{padding:140px 32px 60px}.how-row,.pz-metrics{grid-template-columns:1fr 1fr}
  .ind-grid{grid-template-columns:repeat(3,1fr)}.feat-grid,.test-grid{grid-template-columns:1fr}
  .fc.big{grid-column:span 1}.mon-body{grid-template-columns:1fr}
  .mon-side{border-top:1px solid var(--brd)}.foot-in{grid-template-columns:1fr 1fr}
  .jsim-pipeline{flex-wrap:wrap}
}
@media(max-width:640px){
  .nav-l{display:none}.nav-in,.sec,.sec-f{padding-left:20px;padding-right:20px}
  .hero{padding:120px 20px 40px}.sec,.sec-f{padding-top:80px;padding-bottom:80px}
  .how-row,.pz-metrics{grid-template-columns:1fr}.ind-grid{grid-template-columns:repeat(2,1fr)}
  .pipe-node{min-width:70px;padding:10px 6px}.pipe-label{font-size:8px}
  .foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}
  .fc-metrics{gap:24px}.gmap-wrap{height:280px}.qr-bars{grid-template-columns:repeat(3,1fr)}
}
/* MOBILE NAV */
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
      <button class="nav-cta" onclick="openDemoModal()">Book a Demo</button>
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
    <!-- <a class="nav-mob-link" href="{{ route('client.login') }}">Log in</a> -->
    <button type="button" class="appearance-toggle" id="appearanceToggleMob" onclick="toggleAppearance()" title="Switch appearance" aria-label="Switch appearance" style="margin-top:20px"></button>
    <button class="nav-mob-cta" onclick="toggleNav();openDemoModal()" style="width:100%;border:none;cursor:pointer">Book a Demo</button>
  </div>
</div>
<script>function toggleNav(){var m=document.getElementById('navMob'),h=document.getElementById('navHam');m.classList.toggle('open');h.classList.toggle('open');document.body.style.overflow=m.classList.contains('open')?'hidden':''}</script>

<!-- ASK MIRA -->
<section class="sec" id="ask-mira">
  <div class="stop rv" style="text-align:center"><div class="stag" style="justify-content:center">AI Assistant</div><h2 class="sh" style="margin:0 auto 16px">Ask <span>Mira</span></h2><p class="ss" style="margin:0 auto">Pricing, features, industries we serve, or paste your website for a real analysis — ask below.</p></div>

  <div class="chat-page-wrap rv">
    <div class="chat-layout">

      <aside class="chat-sidebar">
        <div>
          <div class="side-lbl">Your industry</div>
          <select id="industrySelect" class="side-select" onchange="renderStarterQuestions(this.value)">
            <option value="">All industries</option>
            @foreach($industries as $industry)
              <option value="{{ $industry->id }}">{{ $industry->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <div class="side-lbl">Try asking</div>
          <div class="side-starters" id="sideStarters">
            @foreach(($chatQuestionsByIndustry['all'] ?? collect(["What does X Platforms do?", "How much does it cost?"]))->take(6) as $q)
              <button type="button" class="side-starter" onclick="askSuggested({{ \Illuminate\Support\Js::from($q) }})">{{ $q }}</button>
            @endforeach
          </div>
        </div>
      </aside>

      <div class="chat-card">
        <div class="chat-tabs">
          <div class="chat-tab-box active" id="tabBoxAsk">
            <button type="button" class="chat-tab" id="tabBtnAsk" onclick="switchChatTab('ask')">
              <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
              Ask Mira
            </button>
            <button type="button" class="chat-reset" onclick="resetChat()" title="New chat" aria-label="New chat">
              <svg viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
          </div>
          <div class="chat-tab-box" id="tabBoxAnalyze">
            <button type="button" class="chat-tab" id="tabBtnAnalyze" onclick="switchChatTab('analyze')">
              <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
              Analyse my website
            </button>
          </div>
        </div>

        <div class="chat-tab-panel" id="panelAsk">
          <div class="chat-messages" id="chatMsgs">
            <div class="chat-msg bot">Hi, I'm Mira. How can I help you today?</div>
          </div>
          <div class="chat-input-wrap">
            <textarea class="chat-input" id="chatInput" placeholder="Ask Mira..." rows="1" onkeydown="if(event.key==='Enter'&amp;&amp;!event.shiftKey){event.preventDefault();sendChat()}"></textarea>
            <button class="chat-send" onclick="sendChat()"><svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg></button>
          </div>
          <div class="chat-footer">
            <span class="mira-status {{ $miraLive ? 'live' : 'basic' }}"><span class="status-dot"></span> Mira &middot; {{ $miraLive ? 'Live' : 'Basic' }}</span>
          </div>
        </div>

        <div class="chat-tab-panel" id="panelAnalyze" style="display:none">
          <div class="analyze-form">
            <p class="analyze-hint">Paste your website URL — I'll run a real SEO, Technical and Speed check and show the full report below.</p>
            <div class="chat-input-wrap">
              <input type="text" class="chat-input" id="analyzeInput" placeholder="https://yourwebsite.com" onkeydown="if(event.key==='Enter'){event.preventDefault();sendAnalyzeUrl()}">
              <button class="chat-send" onclick="sendAnalyzeUrl()"><svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg></button>
            </div>
            <p class="analyze-status" id="analyzeStatus"></p>
          </div>
          <div class="chat-footer">
            <span class="mira-status {{ $miraLive ? 'live' : 'basic' }}"><span class="status-dot"></span> Mira &middot; {{ $miraLive ? 'Live' : 'Basic' }}</span>
          </div>
        </div>
      </div>

      <aside class="chat-upsell-aside" id="chatUpsellAside">
        <div class="a-upsell-text">
          <strong>Want the full picture?</strong>
          A multi-page site crawl and Lighthouse speed scoring are available on our paid plans.
        </div>
        <a href="{{ route('pricing') }}" class="a-upsell-btn">View Pricing</a>
      </aside>

    </div>

    <div class="analysis-panel" id="analysisPanel">
      <div class="a-summary">
        <div class="analysis-url" id="analysisUrl"></div>
      </div>
      <div class="a-pie-grid" id="analysisPieGrid"></div>
    </div>
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

<!-- HERO -->
<section class="hero">
  <div class="hero-badge"><span class="badge-dot"></span> AI-Powered Intelligence Engine</div>
  <h1>The AI that sees your <span>entire customer universe</span></h1>
  <p class="hero-p">X Platforms connects every data source &mdash; websites, social media, CRM, call centres &mdash; runs it through 8 AI layers, and delivers predictions that drive revenue growth across 15 industries.</p>
  <div class="hero-btns">
    <button onclick="openDemoModal()" class="btn-b btn-fill" style="border:none;cursor:pointer">Request Demo</button>
    <a href="{{ route('pricing') }}" class="btn-b btn-g">View Pricing</a>
    <a href="#arch" class="btn-b btn-g">Explore Architecture</a>
  </div>
  <div class="pipeline">
    <div class="pipe-node"><div class="pipe-icon"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></div><div class="pipe-label">Ingest</div><div class="pipe-num">01</div></div>
    <div class="pipe-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
    <div class="pipe-node"><div class="pipe-icon"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></div><div class="pipe-label">Unify</div><div class="pipe-num">02</div></div>
    <div class="pipe-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
    <div class="pipe-node"><div class="pipe-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg></div><div class="pipe-label">Map</div><div class="pipe-num">03</div></div>
    <div class="pipe-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
    <div class="pipe-node"><div class="pipe-icon"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div><div class="pipe-label">Detect</div><div class="pipe-num">04</div></div>
    <div class="pipe-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
    <div class="pipe-node"><div class="pipe-icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><div class="pipe-label">Predict</div><div class="pipe-num">05</div></div>
    <div class="pipe-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
    <div class="pipe-node"><div class="pipe-icon"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 16l4-6 4 3 5-7"/></svg></div><div class="pipe-label">Plan</div><div class="pipe-num">06</div></div>
    <div class="pipe-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
    <div class="pipe-node"><div class="pipe-icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div><div class="pipe-label">Execute</div><div class="pipe-num">07</div></div>
    <div class="pipe-arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
    <div class="pipe-node"><div class="pipe-icon"><svg viewBox="0 0 24 24"><path d="M4 4v5h5M20 20v-5h-5"/><path d="M20.49 9A9 9 0 005.64 5.64L4 4m16 16l-1.64-1.64A9 9 0 013.51 15"/></svg></div><div class="pipe-label">Learn</div><div class="pipe-num">08</div></div>
  </div>
</section>

<!-- GLOBAL COUNTER -->
<div class="gcounter">
  <div class="gc-num" id="globalCount">1,284,739,201</div>
  <div class="gc-label">Total AI Predictions Generated</div>
  <div class="gc-sub">and counting &mdash; updated live</div>
</div>

<!-- GLOBAL DATA MAP -->
<section class="sec" id="arch">
  <div class="stop rv"><div class="stag">Global Network</div><h2 class="sh">Intelligence flowing worldwide</h2><p class="ss">Data streams from 40+ countries, processed through our AI engine in real time.</p></div>
  <div class="gmap-wrap rv">
    <canvas id="gmap"></canvas>
    <div class="gmap-legend">
      <span><span class="gl-dot" style="background:var(--blue)"></span> Data Node</span>
      <span><span class="gl-dot" style="background:var(--emerald)"></span> Active Stream</span>
      <span><span class="gl-dot" style="background:var(--violet)"></span> AI Processing</span>
    </div>
    <div class="gmap-stats">
      <div class="gmap-stat"><strong id="gmapNodes">42</strong> active nodes</div>
      <div class="gmap-stat"><strong id="gmapStreams">1.2M</strong> events/min</div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="sec-f bordered" id="how"><div class="sw">
  <div class="stop rv"><div class="stag">Process</div><h2 class="sh">From raw data to revenue</h2><p class="ss">No code. No data science team. Connect your platforms and the AI handles everything.</p></div>
  <div class="how-row rv">
    <div class="hw"><div class="hw-n">01</div><div class="hw-t">Connect Sources</div><div class="hw-d">One-click integrations to CRM, web analytics, social, POS, call centres &mdash; every touchpoint.</div></div>
    <div class="hw"><div class="hw-n">02</div><div class="hw-t">AI Analyses</div><div class="hw-d">The 8-layer engine ingests, unifies, and maps customer behaviour across every channel.</div></div>
    <div class="hw"><div class="hw-n">03</div><div class="hw-t">Get Predictions</div><div class="hw-d">Receive AI predictions on churn, purchase intent, lifetime value, and next-best-action.</div></div>
    <div class="hw"><div class="hw-n">04</div><div class="hw-t">Grow Revenue</div><div class="hw-d">Execute data-driven strategies that increase acquisition, retention, and revenue.</div></div>
  </div>
</div></section>

<!-- AI MONITOR -->
<section class="sec">
  <div class="stop rv"><div class="stag">Live Engine</div><h2 class="sh">Watch the intelligence work</h2><p class="ss">A real-time view inside the X Platforms engine as it processes, predicts, and learns.</p></div>
  <div class="monitor rv">
    <div class="mon-bar">
      <div class="md" style="background:#ff5f57"></div>
      <div class="md" style="background:#febc2e"></div>
      <div class="md" style="background:#28c840"></div>
      <div class="mon-bar-title">x-platforms :: processing monitor</div>
      <div class="mon-bar-status"><span class="pulse-live"></span> LIVE</div>
    </div>
    <div class="mon-body">
      <div class="mon-feed"><div class="mon-feed-inner" id="monFeed"></div></div>
      <div class="mon-side">
        <div class="mon-metric"><div class="mm-label">Throughput</div><div class="mm-val" style="color:var(--blue)" id="monTP">18,432 <span style="font-size:12px;color:var(--g400);font-weight:400">evt/s</span></div><div class="mm-bar"><div class="mm-fill" style="width:78%;background:var(--blue)"></div></div></div>
        <div class="mon-metric"><div class="mm-label">Confidence</div><div class="mm-val" style="color:var(--emerald)" id="monCF">94.7%</div><div class="mm-bar"><div class="mm-fill" style="width:94.7%;background:var(--emerald)"></div></div></div>
        <div class="mon-metric"><div class="mm-label">Predictions</div><div class="mm-val" style="color:var(--violet)" id="monPR">2,847</div><div class="mm-bar"><div class="mm-fill" style="width:62%;background:var(--violet)"></div></div></div>
        <div class="mon-layers" id="monLayers">
          <div class="mon-layer active"><span class="ml-dot" style="background:var(--blue)"></span>L1 Ingest<span class="ml-status" style="color:var(--emerald)">ACTIVE</span></div>
          <div class="mon-layer"><span class="ml-dot" style="background:var(--cyan)"></span>L2 Unify<span class="ml-status" style="color:var(--g500)">IDLE</span></div>
          <div class="mon-layer"><span class="ml-dot" style="background:var(--rose)"></span>L3 Map<span class="ml-status" style="color:var(--g500)">IDLE</span></div>
          <div class="mon-layer"><span class="ml-dot" style="background:var(--amber)"></span>L4 Detect<span class="ml-status" style="color:var(--g500)">IDLE</span></div>
          <div class="mon-layer"><span class="ml-dot" style="background:var(--violet)"></span>L5 Predict<span class="ml-status" style="color:var(--g500)">IDLE</span></div>
          <div class="mon-layer"><span class="ml-dot" style="background:var(--emerald)"></span>L6 Plan<span class="ml-status" style="color:var(--g500)">IDLE</span></div>
          <div class="mon-layer"><span class="ml-dot" style="background:#fb923c"></span>L7 Exec<span class="ml-status" style="color:var(--g500)">IDLE</span></div>
          <div class="mon-layer"><span class="ml-dot" style="background:#60a5fa"></span>L8 Learn<span class="ml-status" style="color:var(--g500)">IDLE</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CUSTOMER JOURNEY SIMULATOR -->
<section class="sec-f bordered" id="journey"><div class="sw">
  <div class="stop rv"><div class="stag">Journey Simulator</div><h2 class="sh">Watch AI process a real customer</h2><p class="ss">Pick an industry and see how a single customer flows through all 8 layers with live predictions.</p></div>
  <div class="jsim rv">
    <div class="jsim-controls">
      <select class="jsim-select" id="jsimInd">
        <option value="retail">Retail &amp; E-Commerce</option>
        <option value="banking">Banking &amp; Finance</option>
        <option value="healthcare">Healthcare</option>
        <option value="telecom">Telecom</option>
        <option value="travel">Travel &amp; Hospitality</option>
      </select>
      <button class="jsim-run" id="jsimRun" onclick="runJourney()">&#9654; Run Simulation</button>
    </div>
    <div class="jsim-pipeline" id="jsimPipe">
      <div class="jsim-step" data-s="0"><div class="js-num">01</div><div class="js-name">Ingest</div></div>
      <div class="jsim-step" data-s="1"><div class="js-num">02</div><div class="js-name">Unify</div></div>
      <div class="jsim-step" data-s="2"><div class="js-num">03</div><div class="js-name">Map</div></div>
      <div class="jsim-step" data-s="3"><div class="js-num">04</div><div class="js-name">Detect</div></div>
      <div class="jsim-step" data-s="4"><div class="js-num">05</div><div class="js-name">Predict</div></div>
      <div class="jsim-step" data-s="5"><div class="js-num">06</div><div class="js-name">Plan</div></div>
      <div class="jsim-step" data-s="6"><div class="js-num">07</div><div class="js-name">Execute</div></div>
      <div class="jsim-step" data-s="7"><div class="js-num">08</div><div class="js-name">Learn</div></div>
    </div>
    <div class="jsim-output" id="jsimOut">Select an industry and click "Run Simulation" to begin...</div>
  </div>
  <div style="text-align:center;margin-top:28px">
    <a href="{{ route('simulator') }}" style="display:inline-flex;align-items:center;gap:8px;padding:13px 30px;background:linear-gradient(135deg,#4f8fff,#2563eb);color:#fff;border-radius:10px;font-weight:600;font-size:14px;text-decoration:none;box-shadow:0 4px 20px rgba(79,143,255,.25);transition:all .25s" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 32px rgba(79,143,255,.4)'" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 20px rgba(79,143,255,.25)'">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="white"><polygon points="5,3 19,12 5,21"/></svg>
      Try Full Simulator â All 15 Industries
    </a>
  </div>
</div></section>

<!-- BEFORE/AFTER DATA TABLE -->
<section class="sec">
  <div class="stop rv"><div class="stag">Data Transformation</div><h2 class="sh">Raw chaos &rarr; AI predictions</h2><p class="ss">See how X Platforms transforms messy, disconnected data into clean, actionable intelligence.</p></div>
  <div class="rv">
    <div class="ba-toggle">
      <span class="ba-label active-l" id="baLabelRaw">Raw Data</span>
      <button class="ba-switch" id="baSwitch" onclick="toggleBA()"></button>
      <span class="ba-label" id="baLabelClean">AI Processed</span>
    </div>
    <table class="ba-table">
      <thead><tr><th>Customer</th><th>Source</th><th>Data Point</th><th>Status</th><th id="baCol5">Value</th></tr></thead>
      <tbody id="baBody"></tbody>
    </table>
  </div>
</section>

<!-- INDUSTRY PERSONALIZER -->
<section class="sec-f bordered" id="ind"><div class="sw">
  <div class="stop rv"><div class="stag">Industry Intelligence</div><h2 class="sh">Tailored for your vertical</h2><p class="ss">Select your industry to see performance benchmarks and AI impact metrics.</p></div>
  <div class="rv">
    <div class="pz-bar"><label>Select your industry:</label>
      <select class="pz-select" id="pzSelect" onchange="updatePersonalizer()">
        <option value="retail">Retail &amp; E-Commerce</option>
        <option value="banking">Banking &amp; Finance</option>
        <option value="healthcare">Healthcare</option>
        <option value="telecom">Telecom</option>
        <option value="travel">Travel &amp; Hospitality</option>
        <option value="energy">Energy &amp; Utilities</option>
        <option value="insurance">Insurance</option>
        <option value="pharma">Pharma</option>
      </select>
    </div>
    <div class="pz-metrics" id="pzMetrics">
      <div class="pz-card"><div class="pz-n" id="pz1">34%</div><div class="pz-l">Churn Reduction</div></div>
      <div class="pz-card"><div class="pz-n" id="pz2">3.2&times;</div><div class="pz-l">Revenue Lift</div></div>
      <div class="pz-card"><div class="pz-n" id="pz3">$2.4M</div><div class="pz-l">Annual Impact</div></div>
      <div class="pz-card"><div class="pz-n" id="pz4">94%</div><div class="pz-l">Model Accuracy</div></div>
    </div>
  </div>
</div></section>

<!-- FEATURES -->
<section class="sec" id="feat">
  <div class="stop rv"><div class="stag">Capabilities</div><h2 class="sh">Intelligence, not dashboards</h2></div>
  <div class="feat-grid">
    <div class="fc big rv"><div class="fc-bar"></div><h3>Predictive Customer Intelligence</h3><p>Know which customers will churn, who's ready to convert, and which offer will land &mdash; before it happens.</p><div class="fc-metrics"><div><div class="fm-n">97%</div><div class="fm-l">Accuracy</div></div><div><div class="fm-n">3.2&times;</div><div class="fm-l">Revenue Lift</div></div><div><div class="fm-n">&lt;200ms</div><div class="fm-l">Response</div></div></div></div>
    <div class="fc rv"><div class="fc-bar"></div><h3>Real-Time Decisioning</h3><p>Sub-second insight delivery with automated triggers.</p></div>
    <div class="fc rv"><div class="fc-bar"></div><h3>Revenue Playbooks</h3><p>AI strategies per vertical with projected ROI.</p></div>
    <div class="fc rv"><div class="fc-bar"></div><h3>Omnichannel Identity</h3><p>Every touchpoint stitched into a single golden record.</p></div>
    <div class="fc rv"><div class="fc-bar"></div><h3>Enterprise Security</h3><p>SOC 2 Type II. GDPR. E2E encryption.</p></div>
    <div class="fc rv"><div class="fc-bar"></div><h3>Self-Improving AI</h3><p>Models retrain continuously. Predictions sharpen every cycle.</p></div>
  </div>
</section>

<!-- AI READINESS QUIZ -->
<section class="sec-f bordered" id="quiz"><div class="sw">
  <div class="stop rv"><div class="stag">AI Readiness</div><h2 class="sh">How ready is your business for AI?</h2><p class="ss">Answer 5 quick questions and get a personalised readiness score with recommendations.</p></div>
  <div class="quiz rv" id="quizForm">
    <div class="quiz-q"><label>1. How many data sources do you currently use?</label><div class="quiz-opts" data-q="0"><button class="quiz-opt" data-v="1">1&ndash;2</button><button class="quiz-opt" data-v="2">3&ndash;5</button><button class="quiz-opt" data-v="3">6&ndash;10</button><button class="quiz-opt" data-v="4">10+</button></div></div>
    <div class="quiz-q"><label>2. How do you currently analyse customer data?</label><div class="quiz-opts" data-q="1"><button class="quiz-opt" data-v="1">Spreadsheets</button><button class="quiz-opt" data-v="2">BI Tools</button><button class="quiz-opt" data-v="3">Custom Analytics</button><button class="quiz-opt" data-v="4">AI/ML Models</button></div></div>
    <div class="quiz-q"><label>3. Can you predict which customers will churn?</label><div class="quiz-opts" data-q="2"><button class="quiz-opt" data-v="1">Not at all</button><button class="quiz-opt" data-v="2">Gut feeling</button><button class="quiz-opt" data-v="3">Basic scoring</button><button class="quiz-opt" data-v="4">ML predictions</button></div></div>
    <div class="quiz-q"><label>4. How unified is your customer view across channels?</label><div class="quiz-opts" data-q="3"><button class="quiz-opt" data-v="1">Completely siloed</button><button class="quiz-opt" data-v="2">Partially linked</button><button class="quiz-opt" data-v="3">Mostly unified</button><button class="quiz-opt" data-v="4">Single view</button></div></div>
    <div class="quiz-q"><label>5. How fast can you act on customer insights?</label><div class="quiz-opts" data-q="4"><button class="quiz-opt" data-v="1">Days/weeks</button><button class="quiz-opt" data-v="2">Same day</button><button class="quiz-opt" data-v="3">Hours</button><button class="quiz-opt" data-v="4">Real-time</button></div></div>
    <button class="quiz-submit" onclick="submitQuiz()">Get My Readiness Score</button>
  </div>
  <div class="quiz-result rv" id="quizResult">
    <div class="qr-score" id="qrScore">0</div>
    <div class="qr-label">AI Readiness Score</div>
    <div class="qr-text" id="qrText"></div>
    <div class="qr-bars" id="qrBars"></div>
    <button class="quiz-submit" style="margin-top:28px" onclick="resetQuiz()">Retake Assessment</button>
  </div>
</div></section>

<!-- TESTIMONIALS -->
<section class="sec" id="testimonials">
  <div class="stop rv"><div class="stag">Results</div><h2 class="sh">What our customers say</h2></div>
  <div class="test-grid rv">
    <div class="tc"><div class="tc-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div><div class="tc-q">&ldquo;X Platforms replaced four analytics tools. Within 90 days, churn dropped 34% and CLV increased 2.8&times;.&rdquo;</div><div class="tc-author"><div class="tc-avatar">SC</div><div><div class="tc-name">Sarah Chen</div><div class="tc-role">VP Growth, Atlas Group</div></div></div></div>
    <div class="tc"><div class="tc-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div><div class="tc-q">&ldquo;We went from guessing to knowing. The AI predicted our top segment with 94% accuracy before launch.&rdquo;</div><div class="tc-author"><div class="tc-avatar">MR</div><div><div class="tc-name">Marco Rossi</div><div class="tc-role">CMO, Nuvola</div></div></div></div>
    <div class="tc"><div class="tc-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div><div class="tc-q">&ldquo;The 8-layer architecture is no gimmick. Our data team was blown away by the pattern detection.&rdquo;</div><div class="tc-author"><div class="tc-avatar">JL</div><div><div class="tc-name">James Liu</div><div class="tc-role">CTO, Herald Bank</div></div></div></div>
  </div>
</section>

<!-- FAQ -->
<section class="sec-f bordered"><div class="sw">
  <div class="stop rv" style="text-align:center"><div class="stag" style="justify-content:center">FAQ</div><h2 class="sh" style="margin:0 auto 16px">Frequently asked questions</h2></div>
  <div class="faq-list rv">
    <div class="faq-item"><div class="faq-q">How long does implementation take?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Most customers are fully onboarded within 2&ndash;4 weeks with one-click integrations.</p></div></div>
    <div class="faq-item"><div class="faq-q">Do I need a data science team?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>No. The 8-layer engine handles all data processing, model training, and prediction generation automatically.</p></div></div>
    <div class="faq-item"><div class="faq-q">How accurate are the predictions?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>97% accuracy on average across churn, purchase propensity, and lifetime value predictions.</p></div></div>
    <div class="faq-item"><div class="faq-q">Is my data secure?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>SOC 2 Type II, GDPR compliant, ISO 27001. AES-256 encryption at rest and in transit.</p></div></div>
    <div class="faq-item"><div class="faq-q">Can I try before committing?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Yes &mdash; 30-day proof of concept with your real data, no obligation.</p></div></div>
  </div>
</div></section>

<!-- PRICING -->
<section class="sec-f bordered" id="pricing"><div class="sw">
  <div class="stop rv" style="text-align:center">
    <div class="stag" style="justify-content:center">Pricing</div>
    <h2 class="sh" style="margin:0 auto 16px">Simple, transparent pricing</h2>
    <p style="font-size:16px;color:var(--g300);max-width:480px;margin:0 auto;line-height:1.7">Start free, scale as you grow. All plans include your 30-day proof of concept with real data.</p>
  </div>
  <div class="pricing-grid rv">

    <!-- STARTER -->
    <div class="pc">
      <div class="pc-name">Starter</div>
      <div class="pc-price"><sup>$</sup>299</div>
      <div class="pc-period">/ month, billed annually</div>
      <p class="pc-desc">For growing teams ready to put AI to work on their customer data.</p>
      <ul class="pc-features">
        <li>Up to 500K customer profiles</li>
        <li>5 AI prediction models</li>
        <li>3 data source integrations</li>
        <li>Real-time scoring &amp; segmentation</li>
        <li>Email &amp; chat support</li>
        <li>Standard dashboards</li>
      </ul>
      <a href="{{ route('client.register') }}" class="pc-cta outline">Get Started</a>
    </div>

    <!-- GROWTH (popular) -->
    <div class="pc popular">
      <div class="pc-badge">Most Popular</div>
      <div class="pc-name">Growth</div>
      <div class="pc-price"><sup>$</sup>799</div>
      <div class="pc-period">/ month, billed annually</div>
      <p class="pc-desc">For teams that need the full 8-layer engine across multiple channels.</p>
      <ul class="pc-features">
        <li>Up to 5M customer profiles</li>
        <li>All 8 AI layers unlocked</li>
        <li>Unlimited integrations</li>
        <li>Predictive audiences &amp; journeys</li>
        <li>Priority support &amp; onboarding</li>
        <li>Custom dashboards &amp; exports</li>
        <li>A/B testing &amp; attribution</li>
      </ul>
      <a href="{{ route('client.register') }}" class="pc-cta solid">Start Free Trial</a>
    </div>

    <!-- ENTERPRISE -->
    <div class="pc">
      <div class="pc-name">Enterprise</div>
      <div class="pc-price" style="font-size:38px;padding-top:8px">Custom</div>
      <div class="pc-period">&nbsp;</div>
      <p class="pc-desc">For large organisations needing dedicated infrastructure, compliance, and SLAs.</p>
      <ul class="pc-features">
        <li>Unlimited profiles &amp; predictions</li>
        <li>Private cloud or on-premise deploy</li>
        <li>SOC 2 / ISO 27001 / GDPR controls</li>
        <li>Dedicated customer success manager</li>
        <li>99.99% uptime SLA</li>
        <li>Custom model training</li>
        <li>SSO &amp; role-based access</li>
      </ul>
      <a href="{{ route('client.register') }}" class="pc-cta outline">Contact Sales</a>
    </div>

  </div>
</div></section>

<!-- CTA -->
<section class="cta">
  <h2 class="cta-h rv">Stop guessing.<br>Let AI <span>decide.</span></h2>
  <p class="cta-p rv">Join the companies using X Platforms to turn every customer signal into measurable growth.</p>
  <div class="rv" style="display:flex;gap:14px;justify-content:center">
    <button onclick="openDemoModal()" class="btn-b btn-fill" style="font-size:15px;padding:16px 42px;border:none;cursor:pointer">Book Your Demo</button>
    <a href="#quiz" class="btn-b btn-g" style="font-size:15px;padding:16px 42px">Check AI Readiness</a>
  </div>
</section>

<!-- FOOTER -->
<footer class="foot"><div class="foot-in">
  <div><a href="{{ url('/') }}" class="logo"><img src="{{ asset('images/xplatforms_logo.jpeg') }}" alt="X Platforms" style="height:32px;width:auto;display:block"></a><p class="foot-desc">The world's first 8-layer AI intelligence engine.</p></div>
  <div class="foot-c"><h5>Product</h5><a href="{{ route('platform.architecture') }}">Architecture</a><a href="#">Integrations</a><a href="{{ route('industries') }}">Industries</a><a href="{{ route('pricing') }}">Pricing</a></div>
  <div class="foot-c"><h5>Company</h5><a href="{{ route('about') }}">About</a><a href="{{ route('careers') }}">Careers</a><a href="{{ route('blog') }}">Blog</a><a href="{{ route('contact') }}">Contact</a></div>
  <div class="foot-c"><h5>Resources</h5><a href="#">Documentation</a><a href="{{ route('case-studies') }}">Case Studies</a><a href="#">API Reference</a><a href="{{ route('security') }}">Security</a></div>
</div><div class="foot-b"><span>&copy; {{ date('Y') }} X Platforms.</span><span><a href="{{ route('privacy') }}" style="color:inherit">Privacy</a> &middot; <a href="{{ route('terms') }}" style="color:inherit">Terms</a> &middot; <a href="{{ route('security') }}" style="color:inherit">Security</a></span></div></footer>

<!-- BOOK A DEMO MODAL -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="dp-wrap" id="dpWrap" onclick="if(event.target===this)closeDemoModal()">
  <div class="dp-card" id="dpCard">

    <!-- STICKY HEADER -->
    <div class="dp-head">
      <div class="dp-brand">
        <div class="dp-mark">X</div>
        <div>
          <div class="dp-title">Book your demo</div>
          <div class="dp-sub">Response within 2 hrs &middot; Most demos in 3 days</div>
        </div>
      </div>
      <button class="dp-close" onclick="closeDemoModal()" aria-label="Close">&#x2715;</button>
    </div>

    <!-- BADGES -->
    <div class="dp-badges">
      <span class="dp-badge">Free &middot; No commitment</span>
      <span class="dp-badge">30 minutes</span>
      <span class="dp-badge">Live on your data</span>
      <span class="dp-badge">Response within 2 hrs</span>
    </div>

    <!-- FORM BODY -->
    <div class="dp-body" id="dpFormBody">
      <!-- Step indicator -->
      <div class="dp-si">
        <div class="dp-si-dot on" id="dpd0"></div>
        <div class="dp-si-dot" id="dpd1"></div>
        <div class="dp-si-dot" id="dpd2"></div>
        <span class="dp-si-lbl" id="dpdLbl">Step 1 of 3 &mdash; About you</span>
      </div>

      <!-- STEP 1: ABOUT YOU -->
      <div class="dp-step on" id="dpStep1">
        <div class="dp-row">
          <div class="dp-grp"><label class="dp-lbl">First Name *</label><input class="dp-inp" id="dp_fn" type="text" placeholder="Jane"></div>
          <div class="dp-grp"><label class="dp-lbl">Last Name *</label><input class="dp-inp" id="dp_ln" type="text" placeholder="Smith"></div>
        </div>
        <div class="dp-grp"><label class="dp-lbl">Work Email *</label><input class="dp-inp" id="dp_em" type="email" placeholder="jane@company.com"></div>
        <div class="dp-grp"><label class="dp-lbl">Company Name *</label><input class="dp-inp" id="dp_co" type="text" placeholder="Your company"></div>
        <div class="dp-row">
          <div class="dp-grp"><label class="dp-lbl">Job Title *</label><input class="dp-inp" id="dp_jt" type="text" placeholder="VP Marketing"></div>
          <div class="dp-grp"><label class="dp-lbl">Company Size *</label>
            <select class="dp-sel" id="dp_cs">
              <option value="">Select size</option>
              <option>1&ndash;50 employees</option><option>51&ndash;200 employees</option>
              <option>201&ndash;1,000 employees</option><option>1,001&ndash;5,000 employees</option><option>5,000+ employees</option>
            </select>
          </div>
        </div>
        <div class="dp-grp"><label class="dp-lbl">Industry *</label>
          <select class="dp-sel" id="dp_ind">
            <option value="">Select your industry</option>
            <option>Retail &amp; E-Commerce</option><option>Banking &amp; Finance</option><option>Healthcare</option>
            <option>Telecom</option><option>Travel &amp; Hospitality</option><option>Insurance</option>
            <option>Manufacturing</option><option>Energy &amp; Utilities</option><option>Education / EdTech</option>
            <option>Real Estate</option><option>Media &amp; Entertainment</option><option>Pharma</option>
            <option>Automotive</option><option>Food &amp; Beverage</option><option>SaaS</option><option>Other</option>
          </select>
        </div>
        <div class="dp-btns">
          <button class="dp-btn" onclick="dpGoStep(2)">Continue <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></button>
        </div>
      </div>

      <!-- STEP 2: YOUR SITUATION -->
      <div class="dp-step" id="dpStep2">
        <div class="dp-grp"><label class="dp-lbl">Monthly Active Customers</label>
          <select class="dp-sel" id="dp_mac">
            <option value="">Select range</option>
            <option>Under 1,000</option><option>1,000&ndash;10,000</option><option>10,000&ndash;100,000</option>
            <option>100,000&ndash;500,000</option><option>500,000&ndash;1M</option><option>1M+</option>
          </select>
        </div>
        <div class="dp-grp"><label class="dp-lbl">Monthly Revenue</label>
          <select class="dp-sel" id="dp_rev">
            <option value="">Select range</option>
            <option>Under $50K</option><option>$50K&ndash;$200K</option><option>$200K&ndash;$500K</option>
            <option>$500K&ndash;$2M</option><option>$2M&ndash;$10M</option><option>$10M+</option>
          </select>
        </div>
        <div class="dp-grp"><label class="dp-lbl">Primary challenge you want to solve</label>
          <select class="dp-sel" id="dp_chal">
            <option value="">Select your challenge</option>
            <option>Reducing customer churn</option><option>Improving conversion rates</option>
            <option>Increasing customer lifetime value</option><option>Unifying customer data</option>
            <option>Identifying upsell opportunities</option><option>Predicting customer behaviour</option>
            <option>Reducing acquisition cost</option><option>Other</option>
          </select>
        </div>
        <div class="dp-grp"><label class="dp-lbl">Data sources you currently use (optional)</label>
          <input class="dp-inp" id="dp_ds" type="text" placeholder="e.g. Salesforce, Shopify, Klaviyo, Zendesk">
        </div>
        <div class="dp-grp"><label class="dp-lbl">Anything specific for the demo? (optional)</label>
          <textarea class="dp-ta" id="dp_notes" placeholder="e.g. Focus on churn prediction for our SaaS platform&hellip;"></textarea>
        </div>
        <div class="dp-btns">
          <button class="dp-bk" onclick="dpGoStep(1)">&#8592; Back</button>
          <button class="dp-btn" onclick="dpGoStep(3)">Choose a Time <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></button>
        </div>
      </div>

      <!-- STEP 3: PICK A TIME -->
      <div class="dp-step" id="dpStep3">
        <div class="dp-grp">
          <label class="dp-lbl">Select a date</label>
          <div class="dp-cal-hd">
            <button class="dp-arrow" onclick="dpChangeMonth(-1)">&#8249;</button>
            <span id="dpMonthLbl"></span>
            <button class="dp-arrow" onclick="dpChangeMonth(1)">&#8250;</button>
          </div>
          <div class="dp-dow">
            <div class="dp-dh">S</div><div class="dp-dh">M</div><div class="dp-dh">T</div>
            <div class="dp-dh">W</div><div class="dp-dh">T</div><div class="dp-dh">F</div><div class="dp-dh">S</div>
          </div>
          <div class="dp-cal-grid" id="dpCalGrid"></div>
        </div>
        <div class="dp-grp" id="dpTimeSection" style="display:none">
          <label class="dp-lbl">Available times <span style="font-family:var(--fm);font-size:10px;color:var(--g300)">(AEST)</span></label>
          <div class="dp-times">
            <div class="dp-t" onclick="dpSelTime(this)">9:00 AM</div>
            <div class="dp-t" onclick="dpSelTime(this)">9:30 AM</div>
            <div class="dp-t dp-toff">10:00 AM</div>
            <div class="dp-t" onclick="dpSelTime(this)">10:30 AM</div>
            <div class="dp-t dp-toff">11:00 AM</div>
            <div class="dp-t" onclick="dpSelTime(this)">11:30 AM</div>
            <div class="dp-t" onclick="dpSelTime(this)">1:00 PM</div>
            <div class="dp-t dp-toff">1:30 PM</div>
            <div class="dp-t" onclick="dpSelTime(this)">2:00 PM</div>
            <div class="dp-t" onclick="dpSelTime(this)">2:30 PM</div>
            <div class="dp-t" onclick="dpSelTime(this)">3:00 PM</div>
            <div class="dp-t dp-toff">3:30 PM</div>
          </div>
        </div>
        <div class="dp-grp">
          <label class="dp-lbl">Your timezone</label>
          <select class="dp-sel" id="dp_tz">
            <option>AEST (UTC+10) &mdash; Sydney, Melbourne</option>
            <option>AEDT (UTC+11) &mdash; Daylight saving</option>
            <option>SGT (UTC+8) &mdash; Singapore</option>
            <option>IST (UTC+5:30) &mdash; India</option>
            <option>GMT (UTC+0) &mdash; London</option>
            <option>EST (UTC-5) &mdash; New York</option>
            <option>PST (UTC-8) &mdash; Los Angeles</option>
          </select>
        </div>
        <div class="dp-btns">
          <button class="dp-bk" onclick="dpGoStep(2)">&#8592;</button>
          <button class="dp-btn" id="dpConfirmBtn" onclick="dpConfirm()">Confirm Demo &#8594;</button>
        </div>
        <p class="dp-note">Calendar invite sent within 15 minutes &middot; No payment required &middot; <a href="{{ route('privacy') }}">Privacy Policy</a></p>
      </div>
    </div>

    <!-- SUCCESS -->
    <div class="dp-success" id="dpSuccess">
      <div class="dp-sico"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div class="dp-stitle">You&rsquo;re booked!</div>
      <div class="dp-ssub">Check your inbox &mdash; a calendar invite is on its way. Our team will send a short pre-demo questionnaire to make the session as relevant as possible.</div>
      <div class="dp-scard" id="dpSuccessCard"></div>
      <a href="{{ route('simulator') }}" class="dp-slink">Try the simulator while you wait <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>

  </div>
</div>

<!-- SOCIAL PROOF TOASTS -->
<div class="toast-container" id="toasts"></div>

<script>
const IS_LOGGED_IN = {{ $loggedIn ? 'true' : 'false' }};
const CHAT_QUESTIONS_BY_INDUSTRY = @json($chatQuestionsByIndustry);
const DEFAULT_STARTER_QUESTIONS = ["What does X Platforms do?", "How much does it cost?"];

function renderStarterQuestions(industryId){
  const key = industryId ? String(industryId) : 'all';
  let questions = CHAT_QUESTIONS_BY_INDUSTRY[key];
  if(!questions || !questions.length) questions = CHAT_QUESTIONS_BY_INDUSTRY['all'] || DEFAULT_STARTER_QUESTIONS;
  const wrap = document.getElementById('sideStarters');
  if(!wrap) return;
  wrap.innerHTML = '';
  questions.slice(0, 6).forEach(function(q){
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'side-starter';
    btn.textContent = q;
    btn.addEventListener('click', function(){ askSuggested(q); });
    wrap.appendChild(btn);
  });
}

// APPEARANCE TOGGLE
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

// NEURAL NETWORK
const cv=document.getElementById('neural'),cx=cv.getContext('2d');let W,H,nodes=[];
function resize(){W=cv.width=innerWidth;H=cv.height=innerHeight}addEventListener('resize',resize);resize();
const NC=Math.min(70,Math.floor(W*H/18000));
for(let i=0;i<NC;i++)nodes.push({x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.3,vy:(Math.random()-.5)*.3,r:Math.random()*1.5+.8,p:Math.random()*6.28});
let mx=-1e3,my=-1e3;document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY});
(function draw(){cx.clearRect(0,0,W,H);for(let i=0;i<nodes.length;i++){const n=nodes[i];n.x+=n.vx;n.y+=n.vy;n.p+=.012;
if(n.x<0||n.x>W)n.vx*=-1;if(n.y<0||n.y>H)n.vy*=-1;
for(let j=i+1;j<nodes.length;j++){const m=nodes[j],dx=n.x-m.x,dy=n.y-m.y,d=Math.sqrt(dx*dx+dy*dy);if(d<160){cx.beginPath();cx.moveTo(n.x,n.y);cx.lineTo(m.x,m.y);cx.strokeStyle='rgba(79,143,255,'+(1-d/160)*.1+')';cx.lineWidth=.5;cx.stroke()}}
const md=Math.sqrt((n.x-mx)**2+(n.y-my)**2),g=md<180?(1-md/180)*.5:0;
cx.beginPath();cx.arc(n.x,n.y,n.r+Math.sin(n.p)*.4,0,6.28);cx.fillStyle='rgba(79,143,255,'+(0.25+g)+')';cx.fill()}
requestAnimationFrame(draw)})();

// GLOBAL COUNTER
let gcVal=1284739201;
setInterval(()=>{gcVal+=Math.floor(Math.random()*47)+12;document.getElementById('globalCount').textContent=gcVal.toLocaleString()},100);

// GLOBAL DATA MAP
const gmc=document.getElementById('gmap'),gmx=gmc.getContext('2d');
const cities=[{x:.15,y:.35,n:'New York'},{x:.12,y:.42,n:'Miami'},{x:.42,y:.28,n:'London'},{x:.44,y:.32,n:'Paris'},{x:.48,y:.27,n:'Berlin'},{x:.55,y:.35,n:'Dubai'},{x:.65,y:.38,n:'Mumbai'},{x:.72,y:.42,n:'Singapore'},{x:.78,y:.35,n:'Tokyo'},{x:.8,y:.55,n:'Sydney'},{x:.08,y:.3,n:'Toronto'},{x:.35,y:.3,n:'Lisbon'},{x:.5,y:.55,n:'Nairobi'},{x:.18,y:.55,n:'Sao Paulo'},{x:.1,y:.22,n:'Vancouver'}];
let particles=[];
function resizeMap(){gmc.width=gmc.parentElement.clientWidth;gmc.height=gmc.parentElement.clientHeight}
resizeMap();addEventListener('resize',resizeMap);
function spawnParticle(){const a=cities[Math.floor(Math.random()*cities.length)],b=cities[Math.floor(Math.random()*cities.length)];if(a===b)return;particles.push({ax:a.x,ay:a.y,bx:b.x,by:b.y,t:0,spd:.005+Math.random()*.008,c:['79,143,255','56,189,248','129,140,248','52,211,153'][Math.floor(Math.random()*4)]})}
(function drawMap(){const w=gmc.width,h=gmc.height;gmx.clearRect(0,0,w,h);
cities.forEach(c=>{const cx2=c.x*w,cy2=c.y*h;gmx.beginPath();gmx.arc(cx2,cy2,3,0,6.28);gmx.fillStyle='rgba(79,143,255,.6)';gmx.fill();gmx.beginPath();gmx.arc(cx2,cy2,8,0,6.28);gmx.fillStyle='rgba(79,143,255,.08)';gmx.fill()});
if(Math.random()<.15)spawnParticle();
particles=particles.filter(p=>{p.t+=p.spd;if(p.t>1)return false;const px=p.ax+(p.bx-p.ax)*p.t,py=p.ay+(p.by-p.ay)*p.t-.08*Math.sin(p.t*3.14);
gmx.beginPath();gmx.arc(px*w,py*h,2,0,6.28);gmx.fillStyle='rgba('+p.c+','+(1-p.t*.5)+')';gmx.fill();
gmx.beginPath();gmx.arc(px*w,py*h,6,0,6.28);gmx.fillStyle='rgba('+p.c+',.1)';gmx.fill();return true});
requestAnimationFrame(drawMap)})();

// AI MONITOR
const feedEl=document.getElementById('monFeed'),layerEls=document.querySelectorAll('#monLayers .mon-layer');
const feedData=[
  {t:'ml-ingest',l:'INGEST',m:'Streaming 4,218 events from CRM'},
  {t:'ml-unify',l:'UNIFY',m:'892 profiles merged across channels'},
  {t:'ml-map',l:'MAP',m:'Behavioural graph: 24,103 nodes mapped'},
  {t:'ml-detect',l:'DETECT',m:'Anomaly: 3.2Ï deviation in funnel'},
  {t:'ml-predict',l:'PREDICT',m:'Churn scored 847 customers â 94.7%'},
  {t:'ml-plan',l:'PLAN',m:'3 retention strategies generated'},
  {t:'ml-exec',l:'EXEC',m:'Triggered offers for 234 at-risk'},
  {t:'ml-learn',l:'LEARN',m:'Models retrained â accuracy +0.3%'},
  {t:'ml-ingest',l:'INGEST',m:'6,102 POS transactions received'},
  {t:'ml-predict',l:'PREDICT',m:'1,429 cross-sell targets identified'},
  {t:'ml-detect',l:'DETECT',m:'Mobile â 2.4Ã conversion pattern found'},
  {t:'ml-exec',l:'EXEC',m:'Ad spend shifted $12K to high-intent'}
];
const lMap={INGEST:0,UNIFY:1,MAP:2,DETECT:3,PREDICT:4,PLAN:5,EXEC:6,LEARN:7};let fIdx=0;
function addFeed(){const d=feedData[fIdx%feedData.length],t=new Date(),ts=String(t.getHours()).padStart(2,'0')+':'+String(t.getMinutes()).padStart(2,'0')+':'+String(t.getSeconds()).padStart(2,'0');
const l=document.createElement('div');l.className='mon-line';l.innerHTML='<span class="ml-time">'+ts+'</span><span class="ml-tag '+d.t+'">'+d.l+'</span><span class="ml-msg">'+d.m+'</span>';
feedEl.appendChild(l);if(feedEl.children.length>16)feedEl.removeChild(feedEl.firstChild);
layerEls.forEach((el,i)=>{if(i===lMap[d.l]){el.classList.add('active');el.querySelector('.ml-status').textContent='ACTIVE';el.querySelector('.ml-status').style.color='var(--emerald)'}else{el.classList.remove('active');el.querySelector('.ml-status').textContent='IDLE';el.querySelector('.ml-status').style.color='var(--g500)'}});
document.getElementById('monTP').innerHTML=Math.floor(15000+Math.random()*8000).toLocaleString()+' <span style="font-size:12px;color:var(--g400);font-weight:400">evt/s</span>';
document.getElementById('monCF').textContent=(92+Math.random()*5).toFixed(1)+'%';
document.getElementById('monPR').textContent=Math.floor(2000+Math.random()*2000).toLocaleString();fIdx++}
const mObs=new IntersectionObserver(e=>{e.forEach(x=>{if(x.isIntersecting){for(let i=0;i<6;i++)setTimeout(addFeed,i*200);setInterval(addFeed,2200);mObs.unobserve(x.target)}})},{threshold:.2});
const monEl=document.querySelector('.monitor');if(monEl)mObs.observe(monEl);

// JOURNEY SIMULATOR
const jData={
  retail:['Ingesting: 3,200 web sessions, 847 cart events, 12 POS feeds...','Unifying: Matched 2,891 cross-device profiles â single IDs','Mapping: Cart abandoner at step 3 â high intent, price sensitive','Detecting: Pattern â users who view 3+ items in 5min convert at 4.2Ã','Predicting: 78% purchase probability within 48hrs. CLV: $342','Planning: Recommend 10% discount + free shipping, projected ROI: 8.4Ã','Executing: Triggered personalised email + push notification','Learning: Conversion confirmed. Model accuracy updated to 96.1%'],
  banking:['Ingesting: 12,400 transactions, 340 app sessions, 89 support calls...','Unifying: Linked mobile, web, and branch data for 8,200 customers','Mapping: High-value customer showing reduced engagement pattern','Detecting: Anomaly â 3 competitors researched in last 14 days','Predicting: 67% churn risk within 60 days. Account value: $84K/yr','Planning: Assign relationship manager + premium rate offer','Executing: RM notified, personalised retention package queued','Learning: Customer retained after intervention. Pattern added to model'],
  healthcare:['Ingesting: 4,800 patient interactions, 1,200 portal logins, 340 calls...','Unifying: Consolidated records across 3 clinic systems','Mapping: Patient journey: symptom search â booking â follow-up pattern','Detecting: 12% of patients missing follow-up appointments','Predicting: 234 patients at risk of care gap â 91% confidence','Planning: Automated reminder sequence + care coordinator alert','Executing: SMS reminders sent, coordinator dashboard updated','Learning: Follow-up compliance improved 28%. Model retrained'],
  telecom:['Ingesting: 28,000 usage records, 5,400 app events, 890 tickets...','Unifying: 14,200 subscribers matched across billing + support systems','Mapping: Heavy data user, frequent app crashes, 2 recent complaints','Detecting: Usage spike + complaint pattern = 84% churn signal','Predicting: Subscriber likely to switch within 30 days. ARPU: $65/mo','Planning: Proactive upgrade offer + network priority + credit','Executing: Push notification with upgrade CTA + $20 credit applied','Learning: Save rate 72% for this pattern. Confidence increased'],
  travel:['Ingesting: 6,100 searches, 2,800 bookings, 940 reviews analysed...','Unifying: Loyalty profile merged with web + app + partner data','Mapping: Business traveller, prefers aisle seats, books 2wks ahead','Detecting: Destination interest shift: EU â Asia-Pacific','Predicting: 89% likely to book Tokyo in next 21 days. Avg spend: $3,200','Planning: Curated Tokyo package + lounge access + bonus miles','Executing: Personalised email campaign triggered with dynamic content','Learning: Booking confirmed at $3,400. Preference model updated']
};
function runJourney(){const ind=document.getElementById('jsimInd').value,steps=jData[ind],pipe=document.querySelectorAll('.jsim-step'),out=document.getElementById('jsimOut'),btn=document.getElementById('jsimRun');
btn.disabled=true;out.textContent='Initializing simulation...';pipe.forEach(s=>{s.classList.remove('active','done')});
steps.forEach((msg,i)=>{setTimeout(()=>{pipe.forEach((s,j)=>{s.classList.remove('active');if(j<i)s.classList.add('done');if(j===i)s.classList.add('active')});
out.textContent+='\n\n[Layer '+(i+1).toString().padStart(2,'0')+'] '+msg;out.scrollTop=out.scrollHeight;
if(i===steps.length-1){pipe[i].classList.remove('active');pipe[i].classList.add('done');btn.disabled=false;out.textContent+='\n\nâ Simulation complete â all 8 layers processed successfully.'}},800*(i+1))})}

// BEFORE/AFTER TABLE
const rawData=[
  ['J. Smith','CRM','john.smith@, ph: 0412-XXX','Duplicate','â'],
  ['john_s_92','Website','3 page views, bounced','Unknown','â'],
  ['Customer #4821','Call Centre','Complaint: billing issue','Unresolved','â'],
  ['@johnsmith','Social','Negative sentiment tweet','Unlinked','â'],
  ['ID: 90125','POS','$42.50 purchase 03/12','Isolated','â'],
  ['j.smith','Email','Opened 2/10 campaigns','Low engage','â']
];
const cleanData=[
  ['John Smith','Unified Profile','Golden Record: ID-4821','Verified','CLV: $2,840'],
  ['John Smith','Web + Mobile','12 sessions, 34 pages, 2 carts','Active','Intent: High'],
  ['John Smith','Support','Billing resolved, NPS: 7â9','Resolved','Risk: Low'],
  ['John Smith','Social + Email','Sentiment: Recovering (+0.3)','Improving','Engagement: â42%'],
  ['John Smith','Purchases','$847 LTV, 4.2 orders/yr','Loyal','Next: Cross-sell'],
  ['John Smith','AI Prediction','78% purchase in 14 days','Actionable','Offer: Premium tier']
];
let baState=false;
function renderBA(){const body=document.getElementById('baBody'),data=baState?cleanData:rawData;
body.innerHTML='';data.forEach(r=>{const tr=document.createElement('tr');
r.forEach((c,i)=>{const td=document.createElement('td');td.textContent=c;
if(!baState&&i>=2)td.className='ba-raw';if(baState&&i===4)td.className='ba-pred';if(baState)td.className=(td.className||'')+' ba-clean';tr.appendChild(td)});body.appendChild(tr)});
document.getElementById('baCol5').textContent=baState?'AI Prediction':'Value';
document.getElementById('baLabelRaw').classList.toggle('active-l',!baState);document.getElementById('baLabelClean').classList.toggle('active-l',baState)}
function toggleBA(){baState=!baState;document.getElementById('baSwitch').classList.toggle('on',baState);renderBA()}
renderBA();

// INDUSTRY PERSONALIZER
const pzData={
  retail:{c:'34%',r:'3.2Ã',i:'$2.4M',a:'96%'},
  banking:{c:'28%',r:'2.8Ã',i:'$4.1M',a:'94%'},
  healthcare:{c:'22%',r:'2.1Ã',i:'$1.8M',a:'93%'},
  telecom:{c:'31%',r:'3.5Ã',i:'$3.6M',a:'95%'},
  travel:{c:'26%',r:'2.9Ã',i:'$2.1M',a:'97%'},
  energy:{c:'19%',r:'2.4Ã',i:'$5.2M',a:'92%'},
  insurance:{c:'24%',r:'2.6Ã',i:'$3.8M',a:'94%'},
  pharma:{c:'18%',r:'2.2Ã',i:'$6.1M',a:'91%'}
};
function updatePersonalizer(){const d=pzData[document.getElementById('pzSelect').value];
document.getElementById('pz1').textContent=d.c;document.getElementById('pz2').textContent=d.r;
document.getElementById('pz3').textContent=d.i;document.getElementById('pz4').textContent=d.a}

// AI READINESS QUIZ
const qAnswers=[0,0,0,0,0];
document.querySelectorAll('.quiz-opt').forEach(btn=>{btn.addEventListener('click',()=>{const q=parseInt(btn.parentElement.dataset.q),v=parseInt(btn.dataset.v);qAnswers[q]=v;
btn.parentElement.querySelectorAll('.quiz-opt').forEach(b=>b.classList.remove('sel'));btn.classList.add('sel')})});
const qLabels=['Data Sources','Analytics Maturity','Churn Prediction','Data Unification','Action Speed'];
function submitQuiz(){const total=qAnswers.reduce((a,b)=>a+b,0),score=Math.round(total/20*100);
if(total===0)return;
document.getElementById('quizForm').style.display='none';const r=document.getElementById('quizResult');r.style.display='block';r.classList.add('vis');
document.getElementById('qrScore').textContent=score;
const txt=score>=80?'Excellent! Your business is highly AI-ready. X Platforms can amplify your existing capabilities and deliver immediate ROI.':score>=50?'Good foundation! You have the basics in place. X Platforms can fill the gaps and accelerate your AI journey significantly.':'Early stage â and that\'s okay. X Platforms is designed for exactly this. We\'ll unify your data and get predictions running within weeks.';
document.getElementById('qrText').textContent=txt;
const bars=document.getElementById('qrBars');bars.innerHTML='';
qAnswers.forEach((v,i)=>{const col=['var(--blue)','var(--cyan)','var(--violet)','var(--emerald)','var(--amber)'][i];
bars.innerHTML+='<div class="qr-bar-item"><div class="qr-bar-track"><div class="qr-bar-fill" style="height:0;background:'+col+'"></div></div><div class="qr-bar-label">'+qLabels[i]+'</div></div>'});
setTimeout(()=>{bars.querySelectorAll('.qr-bar-fill').forEach((f,i)=>{f.style.height=qAnswers[i]/4*100+'%'})},100)}
function resetQuiz(){document.getElementById('quizForm').style.display='block';document.getElementById('quizResult').style.display='none';
document.querySelectorAll('.quiz-opt').forEach(b=>b.classList.remove('sel'));qAnswers.fill(0)}

// SOCIAL PROOF TOASTS
const toastMsgs=[
  {dot:'var(--emerald)',msg:'<strong>Atlas Group</strong> reduced churn by 34% in 90 days'},
  {dot:'var(--blue)',msg:'<strong>Nuvola</strong> connected 12 new data sources'},
  {dot:'var(--violet)',msg:'<strong>Herald Bank</strong> generated 2,847 predictions today'},
  {dot:'var(--amber)',msg:'<strong>Cortex</strong> achieved 97.2% model accuracy'},
  {dot:'var(--cyan)',msg:'<strong>Meridian</strong> increased CLV by 2.8Ã in Q1'},
  {dot:'var(--emerald)',msg:'<strong>Prism Health</strong> improved follow-up compliance 28%'},
  {dot:'var(--blue)',msg:'<strong>Vantage</strong> saved $1.2M through churn prevention'},
];
let tIdx=0;
function showToast(){const d=toastMsgs[tIdx%toastMsgs.length],el=document.createElement('div');
el.className='toast';el.innerHTML='<span class="toast-dot" style="background:'+d.dot+';box-shadow:0 0 8px '+d.dot+'"></span>'+d.msg;
document.getElementById('toasts').appendChild(el);setTimeout(()=>el.remove(),5000);tIdx++}
setTimeout(()=>{showToast();setInterval(showToast,8000)},5000);

// ASK MIRA
const CHAT_GREETING = document.getElementById('chatMsgs').innerHTML;

// Bot replies may contain a real URL (e.g. the signup link). Render it as a
// clickable <a> using DOM APIs only — never innerHTML on reply text, since
// this is model-influenced content and must never be parsed as markup.
function renderBotMessage(el, text){
  const urlRe=/https?:\/\/[^\s]+/g;
  let lastIndex=0, match;
  while((match=urlRe.exec(text))!==null){
    if(match.index>lastIndex) el.appendChild(document.createTextNode(text.slice(lastIndex,match.index)));
    const trailing=match[0].match(/[.,;:!?)]+$/);
    const url=trailing?match[0].slice(0,-trailing[0].length):match[0];
    const a=document.createElement('a');
    a.href=url;a.textContent=url;a.target='_blank';a.rel='noopener noreferrer';
    el.appendChild(a);
    if(trailing) el.appendChild(document.createTextNode(trailing[0]));
    lastIndex=urlRe.lastIndex;
  }
  if(lastIndex<text.length) el.appendChild(document.createTextNode(text.slice(lastIndex)));
}

function askSuggested(q){
  document.getElementById('chatInput').value=q;
  sendChat();
}

function scoreColor(score){
  if(score===null||score===undefined) return 'var(--g500)';
  if(score>=90) return 'var(--emerald)';
  if(score>=50) return 'var(--amber)';
  return 'var(--rose)';
}

let pendingAnalyzeUrl = null;
let pendingAnalyzeSource = null;

function looksLikeUrl(text){
  return /https?:\/\/\S+/i.test(text);
}

function openLeadModal(url, source){
  pendingAnalyzeUrl = url;
  pendingAnalyzeSource = source;
  document.getElementById('leadName').value = '';
  document.getElementById('leadEmail').value = '';
  document.getElementById('leadModalError').textContent = '';
  document.getElementById('leadModalOverlay').classList.add('show');
}

function closeLeadModal(){
  document.getElementById('leadModalOverlay').classList.remove('show');
  pendingAnalyzeUrl = null;
  pendingAnalyzeSource = null;
}

async function submitLeadAndAnalyze(){
  const name = document.getElementById('leadName').value.trim();
  const email = document.getElementById('leadEmail').value.trim();
  const errEl = document.getElementById('leadModalError');
  errEl.textContent = '';
  if(!name){ errEl.textContent = 'Please enter your name.'; return; }
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ errEl.textContent = 'Please enter a valid email.'; return; }

  const submitBtn = document.getElementById('leadModalSubmit');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Analyzing...';

  const url = pendingAnalyzeUrl;
  const source = pendingAnalyzeSource;

  try{
    const res = await fetch('{{ route("chat.analyze-lead") }}', {method:'POST',
      headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
      body: JSON.stringify({name:name, email:email, url:url, industry_id:(document.getElementById('industrySelect')||{}).value||null})
    });
    const data = await res.json();

    if(!res.ok){
      errEl.textContent = data.error || 'Something went wrong. Please try again.';
      submitBtn.disabled = false;
      submitBtn.textContent = 'Continue';
      return;
    }

    closeLeadModal();

    if(source === 'analyze'){
      const status = document.getElementById('analyzeStatus');
      status.classList.remove('err');
      status.textContent = data.reply;
      if(data.report) renderAnalysisReport(data.report);
    } else {
      const msgs = document.getElementById('chatMsgs');
      const bEl = document.createElement('div');
      bEl.className = 'chat-msg bot';
      renderBotMessage(bEl, data.reply);
      msgs.appendChild(bEl);
      if(data.report) renderAnalysisReport(data.report);
      msgs.scrollTop = msgs.scrollHeight;
    }
  }catch(e){
    errEl.textContent = 'Could not reach the server. Check your connection and try again.';
  }finally{
    submitBtn.disabled = false;
    submitBtn.textContent = 'Continue';
  }
}

const CATEGORY_INFO = {
  overview: "Your overall SEO health score at a glance.",
  titles: "How your page shows up in Google search results.",
  structure: "Is your content organized the way Google likes?",
  images: "Are your images helping or hurting your SEO?",
  security: "Is your site safe and trusted by browsers?",
  mobile: "Does your site work well on phones?",
  links: "Are your links helping visitors and Google understand your site?",
  discoverability: "Can Google actually find and index your pages?",
  social: "How your page looks when shared on Facebook/Twitter.",
  richresults: "Can your site show up with stars, prices, etc. in Google?"
};

function openAnalysisModal(key, label, pct, checkRows){
  document.getElementById('modalTitle').textContent = label;
  document.getElementById('modalDesc').textContent = CATEGORY_INFO[key] || '';
  const pctEl = document.getElementById('modalPct');
  pctEl.textContent = (pct===null||pct===undefined) ? '—' : pct+'%';
  pctEl.style.color = scoreColor(pct);

  const checksEl = document.getElementById('modalChecks');
  checksEl.innerHTML = '';
  checkRows.forEach(function(row){
    const rowEl = document.createElement('div');
    rowEl.className = 'a-modal-check-row';
    const left = document.createElement('div');
    left.className = 'a-modal-check-left';
    const badge = document.createElement('span');
    badge.className = 'a-modal-check-badge ' + (row.status || 'info');
    badge.textContent = (row.status || 'info').toUpperCase();
    const n = document.createElement('span'); n.className = 'a-modal-check-name'; n.textContent = row.name;
    left.appendChild(badge); left.appendChild(n);
    const d = document.createElement('span'); d.className = 'a-modal-check-detail'; d.textContent = row.detail;
    rowEl.appendChild(left); rowEl.appendChild(d);
    checksEl.appendChild(rowEl);
  });

  document.getElementById('analysisModalOverlay').classList.add('show');
}

function closeAnalysisModal(){
  document.getElementById('analysisModalOverlay').classList.remove('show');
}

function buildPieCard(label, pct, checkRows, key){
  const r=42, circumference=2*Math.PI*r;
  const p=(pct===null||pct===undefined)?0:pct;
  const color=scoreColor(pct);
  const card=document.createElement('div');
  card.className='a-pie-card';
  card.innerHTML =
    '<svg class="a-pie-svg" viewBox="0 0 100 100">'+
      '<circle class="a-pie-track" cx="50" cy="50" r="'+r+'"></circle>'+
      '<circle class="a-pie-fill" cx="50" cy="50" r="'+r+'" style="stroke:'+color+';stroke-dasharray:'+circumference+';stroke-dashoffset:'+(circumference*(1-p/100))+'"></circle>'+
      '<text class="a-pie-pct" x="50" y="50" text-anchor="middle" dominant-baseline="central">'+((pct===null||pct===undefined)?'—':pct+'%')+'</text>'+
    '</svg>'+
    '<div class="a-pie-label"></div>';
  card.querySelector('.a-pie-label').textContent=label;
  const checksEl=document.createElement('div');
  checksEl.className='a-pie-checks';
  checkRows.forEach(function(row){
    const rowEl=document.createElement('div');
    rowEl.className='a-pie-check-row';
    const n=document.createElement('span');n.className='a-pie-check-name';n.textContent=row.name;
    const d=document.createElement('span');d.className='a-pie-check-detail';d.textContent=row.detail;
    d.title=row.detail;
    rowEl.appendChild(n);rowEl.appendChild(d);
    checksEl.appendChild(rowEl);
  });
  card.appendChild(checksEl);
  card.addEventListener('click', function(){
    openAnalysisModal(key, label, pct, checkRows);
  });
  return card;
}

// Renders a WebsiteAnalyzerService report (real SEO/Technical checks)
// as a grid of per-category donut chart cards — never as a giant chat bubble.
function renderAnalysisReport(report){
  const panel=document.getElementById('analysisPanel');
  document.getElementById('analysisUrl').textContent=report.url;

  const gridEl=document.getElementById('analysisPieGrid');
  gridEl.innerHTML='';

  const c=report.counts;
  gridEl.appendChild(buildPieCard('Site Overview', report.overall, [
    {name:'Grade', detail:report.grade, status:'info'},
    {name:'Pass', detail:String(c.pass), status:'pass'},
    {name:'Warn', detail:String(c.warn), status:'warn'},
    {name:'Fail', detail:String(c.fail), status:'fail'}
  ], 'overview'));

  report.categories.forEach(function(cat){
    gridEl.appendChild(buildPieCard(cat.label, cat.score, (cat.checks||[]).map(function(ch){
      return {name:ch.name, detail:ch.detail, status:ch.status};
    }), cat.key));
  });

  panel.classList.add('show');
  panel.scrollIntoView({behavior:'smooth',block:'nearest'});
  document.getElementById('chatUpsellAside').classList.add('show');
}

function switchChatTab(tab){
  const isAsk=tab==='ask';
  document.getElementById('tabBoxAsk').classList.toggle('active',isAsk);
  document.getElementById('tabBoxAnalyze').classList.toggle('active',!isAsk);
  document.getElementById('panelAsk').style.display=isAsk?'flex':'none';
  document.getElementById('panelAnalyze').style.display=isAsk?'none':'flex';
}

// Shared by both tabs — one message endpoint, two front ends.
async function postToChat(message){
  const res=await fetch('{{ route("chat.send") }}',{method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
    body:JSON.stringify({message:message, industry_id:(document.getElementById('industrySelect')||{}).value||null})});
  const data=await res.json();
  return {ok:res.ok, data:data};
}

async function sendChat(){
  const input=document.getElementById('chatInput'),msg=input.value.trim();if(!msg)return;
  if(!IS_LOGGED_IN && looksLikeUrl(msg)){
    input.value = '';
    openLeadModal(msg, 'chat');
    return;
  }
  const msgs=document.getElementById('chatMsgs');
  const uEl=document.createElement('div');uEl.className='chat-msg user';uEl.textContent=msg;msgs.appendChild(uEl);input.value='';
  const typing=document.createElement('div');typing.className='chat-msg bot typing';typing.textContent='Thinking...';msgs.appendChild(typing);msgs.scrollTop=msgs.scrollHeight;
  try{
    const {ok,data}=await postToChat(msg);
    typing.remove();
    const bEl=document.createElement('div');bEl.className='chat-msg bot';
    renderBotMessage(bEl, ok?data.reply:(data.error||'Something went wrong. Please try again.'));
    msgs.appendChild(bEl);
    if(ok && data.report) renderAnalysisReport(data.report);
  }catch(e){typing.remove();
    const bEl=document.createElement('div');bEl.className='chat-msg bot';bEl.textContent='Could not reach the server. Check your connection and try again.';msgs.appendChild(bEl)}
  msgs.scrollTop=msgs.scrollHeight
}

async function sendAnalyzeUrl(){
  const input=document.getElementById('analyzeInput'),url=input.value.trim();
  const status=document.getElementById('analyzeStatus');
  status.classList.remove('err');
  if(!url){status.textContent='Enter a website URL first.';status.classList.add('err');return;}
  if(!/^https?:\/\//i.test(url)){status.textContent='Include http:// or https:// at the start.';status.classList.add('err');return;}
  if(!IS_LOGGED_IN){
    openLeadModal(url, 'analyze');
    return;
  }
  status.textContent='Checking your site — this can take a moment...';
  try{
    const {ok,data}=await postToChat(url);
    if(ok){
      status.textContent=data.reply;
      if(data.report) renderAnalysisReport(data.report);
    }else{
      status.textContent=data.error||'Something went wrong. Please try again.';
      status.classList.add('err');
    }
  }catch(e){
    status.textContent='Could not reach the server. Check your connection and try again.';
    status.classList.add('err');
  }
}

async function resetChat(){
  await fetch('{{ route("chat.reset") }}',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});
  document.getElementById('chatMsgs').innerHTML=CHAT_GREETING;
  document.getElementById('analysisPanel').classList.remove('show');
  document.getElementById('chatUpsellAside').classList.remove('show');
  document.getElementById('analyzeInput').value='';
  document.getElementById('analyzeStatus').textContent='';
  document.getElementById('analyzeStatus').classList.remove('err');
  switchChatTab('ask');
}

// FAQ
document.querySelectorAll('.faq-q').forEach(q=>{q.addEventListener('click',()=>{const item=q.parentElement,was=item.classList.contains('open');document.querySelectorAll('.faq-item').forEach(i=>i.classList.remove('open'));if(!was)item.classList.add('open')})});

// SCROLL REVEALS
const obs=new IntersectionObserver(e=>{e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('vis');obs.unobserve(x.target)}})},{threshold:.1,rootMargin:'0px 0px -50px 0px'});
document.querySelectorAll('.rv').forEach(el=>obs.observe(el));

// SMOOTH SCROLL
document.querySelectorAll('a[href^="#"]').forEach(a=>{a.addEventListener('click',e=>{const h=a.getAttribute('href');if(h==='#')return;e.preventDefault();document.querySelector(h)&&document.querySelector(h).scrollIntoView({behavior:'smooth'})})});

// NAV BG
addEventListener('scroll',()=>{document.querySelector('.nav-bg').style.background=scrollY>40?'var(--chrome-bg)':'var(--chrome-bg-soft)'});

// ── DEMO MODAL ──────────────────────────────────────────────────────────────
let dpSelDate=null,dpChosenTime=null,dpViewYear=new Date().getFullYear(),dpViewMonth=new Date().getMonth();
const dpMonths=['January','February','March','April','May','June','July','August','September','October','November','December'];

function openDemoModal(){
  document.getElementById('dpWrap').classList.add('dp-open');
  document.body.style.overflow='hidden';
  dpBuildCal();
}
function closeDemoModal(){
  document.getElementById('dpWrap').classList.remove('dp-open');
  document.body.style.overflow='';
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeDemoModal()});

function dpErr(id){const el=document.getElementById(id);if(el){el.classList.add('er');setTimeout(()=>el.classList.remove('er'),2200)}}
function dpValEmail(e){return/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)}

function dpSetSI(n){
  [0,1,2].forEach(i=>{
    const d=document.getElementById('dpd'+i);
    d.classList.remove('on','dn');
    if(i<n-1)d.classList.add('dn');
    else if(i===n-1)d.classList.add('on');
  });
  document.getElementById('dpdLbl').textContent=`Step ${n} of 3 — ${['About you','Your situation','Pick a time'][n-1]}`;
}

function dpGoStep(n){
  if(n===2){
    let ok=true;
    ['dp_fn','dp_ln','dp_co','dp_jt'].forEach(id=>{if(!document.getElementById(id).value.trim()){dpErr(id);ok=false}});
    if(!dpValEmail(document.getElementById('dp_em').value.trim())){dpErr('dp_em');ok=false}
    if(!document.getElementById('dp_cs').value){dpErr('dp_cs');ok=false}
    if(!document.getElementById('dp_ind').value){dpErr('dp_ind');ok=false}
    if(!ok)return;
  }
  document.querySelectorAll('.dp-step').forEach(s=>s.classList.remove('on'));
  document.getElementById('dpStep'+n).classList.add('on');
  dpSetSI(n);
  if(n===3)dpBuildCal();
  document.getElementById('dpCard').scrollTop=0;
}

function dpChangeMonth(dir){
  dpViewMonth+=dir;
  if(dpViewMonth>11){dpViewMonth=0;dpViewYear++}
  if(dpViewMonth<0){dpViewMonth=11;dpViewYear--}
  dpBuildCal();
}

function dpBuildCal(){
  document.getElementById('dpMonthLbl').textContent=`${dpMonths[dpViewMonth]} ${dpViewYear}`;
  const grid=document.getElementById('dpCalGrid');grid.innerHTML='';
  const first=new Date(dpViewYear,dpViewMonth,1).getDay(),days=new Date(dpViewYear,dpViewMonth+1,0).getDate(),today=new Date();
  for(let i=0;i<first;i++){const d=document.createElement('div');d.className='dp-day dp-blank';grid.appendChild(d)}
  for(let d=1;d<=days;d++){
    const cell=document.createElement('div');cell.className='dp-day';
    const date=new Date(dpViewYear,dpViewMonth,d);
    const isPast=date<new Date(today.getFullYear(),today.getMonth(),today.getDate());
    const isWknd=date.getDay()===0||date.getDay()===6;
    if(isPast||isWknd){cell.classList.add('dp-off')}else{
      if(date.toDateString()===today.toDateString())cell.classList.add('dp-today');
      cell.onclick=()=>{
        document.querySelectorAll('#dpCalGrid .dp-day').forEach(c=>c.classList.remove('dp-sel'));
        cell.classList.add('dp-sel');
        dpSelDate=`${d} ${dpMonths[dpViewMonth]} ${dpViewYear}`;
        document.getElementById('dpTimeSection').style.display='block';
        dpChosenTime=null;
        document.querySelectorAll('.dp-t').forEach(t=>t.classList.remove('dp-tsel'));
      };
    }
    cell.textContent=d;grid.appendChild(cell);
  }
}

function dpSelTime(el){
  document.querySelectorAll('.dp-t').forEach(t=>t.classList.remove('dp-tsel'));
  el.classList.add('dp-tsel');
  dpChosenTime=el.textContent;
}

function dpConfirm(){
  if(!dpSelDate||!dpChosenTime){alert('Please select a date and time for your demo.');return}
  const btn=document.getElementById('dpConfirmBtn');
  btn.disabled=true;btn.textContent='Saving…';
  const payload={
    first_name:document.getElementById('dp_fn').value.trim(),
    last_name:document.getElementById('dp_ln').value.trim(),
    email:document.getElementById('dp_em').value.trim(),
    company_name:document.getElementById('dp_co').value.trim(),
    job_title:document.getElementById('dp_jt').value.trim(),
    company_size:document.getElementById('dp_cs').value,
    industry:document.getElementById('dp_ind').value,
    monthly_active_customers:document.getElementById('dp_mac').value,
    monthly_revenue:document.getElementById('dp_rev').value,
    primary_challenge:document.getElementById('dp_chal').value,
    data_sources:document.getElementById('dp_ds').value.trim(),
    demo_notes:document.getElementById('dp_notes').value.trim(),
    demo_date:dpSelDate,
    demo_time:dpChosenTime,
    timezone:document.getElementById('dp_tz').value,
  };
  fetch('{{ route("book-demo.store") }}',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
    body:JSON.stringify(payload)
  })
  .then(r=>r.json())
  .then(data=>{
    if(data.redirect){
      window.location.href=data.redirect;
    }else if(data.success){
      document.getElementById('dpFormBody').style.display='none';
      document.getElementById('dpSuccessCard').innerHTML=
        `<div class="dp-srow"><span class="dp-slbl">Name</span><span class="dp-sval">${payload.first_name} ${payload.last_name}</span></div>`+
        `<div class="dp-srow"><span class="dp-slbl">Date</span><span class="dp-sval">${dpSelDate}</span></div>`+
        `<div class="dp-srow"><span class="dp-slbl">Time</span><span class="dp-sval">${dpChosenTime} (${payload.timezone.split(' ')[0]})</span></div>`+
        `<div class="dp-srow"><span class="dp-slbl">Format</span><span class="dp-sval">Google Meet / Zoom</span></div>`;
      document.getElementById('dpSuccess').classList.add('on');
    }else{
      btn.disabled=false;btn.textContent='Confirm Demo →';
      alert(data.message||'Something went wrong. Please try again.');
    }
  })
  .catch(()=>{btn.disabled=false;btn.textContent='Confirm Demo →';alert('Something went wrong. Please try again.')});
}

</script>
</body>
</html>