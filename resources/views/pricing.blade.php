<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pricing &mdash; X Platforms AI Intelligence Engine | Starter, Growth &amp; Enterprise Plans</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="description" content="Simple, transparent pricing for X Platforms' 8-layer AI intelligence engine. Starter from $499/month. Growth at $1,499/month. Enterprise custom pricing. All plans include a 30-day proof of concept.">
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
.billing-toggle,.pc,.addon,.roi-bar,.rs,.ent-card,.tc,.trust-badge,.faq-section,.faq-item,.foot{backdrop-filter:var(--glass-blur);-webkit-backdrop-filter:var(--glass-blur)}
html{scroll-behavior:smooth}
body{background:var(--body-bg);background-attachment:fixed;color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
canvas#neural{position:fixed;inset:0;z-index:0;pointer-events:none}
button{font-family:var(--f1)}

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
.hero{position:relative;z-index:1;padding:140px 40px 80px;text-align:center}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:28px;letter-spacing:.5px}
.breadcrumb a{color:var(--g300);transition:color .2s}.breadcrumb a:hover{color:var(--blue)}
.hero-badge{display:inline-flex;align-items:center;gap:8px;padding:6px 16px 6px 10px;background:var(--blue-g);border:1px solid var(--brd2);border-radius:100px;font-family:var(--fm);font-size:11px;letter-spacing:1px;text-transform:uppercase;color:var(--blue);margin-bottom:28px;opacity:0;animation:fadeUp .6s var(--ease) .1s forwards}
.badge-dot{width:7px;height:7px;border-radius:50%;background:var(--emerald);box-shadow:0 0 10px var(--emerald);animation:blink 2.5s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
.hero h1{font-weight:800;font-size:clamp(36px,5vw,64px);line-height:1.06;letter-spacing:-2px;max-width:700px;margin:0 auto 20px;opacity:0;animation:fadeUp .7s var(--ease) .2s forwards}
.hero h1 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{font-size:17px;line-height:1.75;color:var(--g200);max-width:500px;margin:0 auto 40px;font-weight:350;opacity:0;animation:fadeUp .7s var(--ease) .35s forwards}

/* BILLING TOGGLE */
.billing-toggle{display:inline-flex;align-items:center;gap:14px;background:var(--card);border:1px solid var(--brd);border-radius:100px;padding:6px 6px 6px 20px;font-size:13px;font-weight:500;color:var(--g200);opacity:0;animation:fadeUp .7s var(--ease) .5s forwards;margin-bottom:64px}
.bt-label{cursor:pointer;transition:color .2s}
.bt-label.active{color:var(--white)}
.bt-pill{width:52px;height:28px;border-radius:14px;background:var(--g600);cursor:pointer;position:relative;transition:background .3s;border:none;padding:0}
.bt-pill.yr{background:var(--blue)}
.bt-pill::after{content:'';position:absolute;top:3px;left:3px;width:22px;height:22px;border-radius:50%;background:#fff;transition:transform .3s var(--ease)}
.bt-pill.yr::after{transform:translateX(24px)}
.bt-save{padding:4px 12px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);border-radius:100px;font-family:var(--fm);font-size:10px;color:var(--emerald);letter-spacing:.5px;margin-left:4px}

/* PRICING CARDS */
.pricing-wrap{position:relative;z-index:1;padding:0 40px 80px;max-width:var(--mw);margin:0 auto}
.pricing-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;align-items:start}
.pc{background:var(--card);border:1px solid var(--brd);border-radius:20px;padding:36px;position:relative;overflow:hidden;transition:border-color .3s,box-shadow .3s}
.pc:hover{border-color:var(--brd2)}
.pc.featured{border-color:var(--blue);background:linear-gradient(180deg,rgba(79,143,255,.05),var(--card) 60%);box-shadow:0 0 60px rgba(79,143,255,.12)}
.pc.featured::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--blue),var(--cyan),transparent)}
.pc-badge{position:absolute;top:20px;right:20px;font-family:var(--fm);font-size:10px;letter-spacing:1px;text-transform:uppercase;padding:4px 12px;border-radius:100px}
.pc-badge.popular{background:rgba(79,143,255,.12);border:1px solid var(--brd2);color:var(--blue)}
.pc-badge.new{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);color:var(--emerald)}
.pc-tier{font-family:var(--fm);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--g300);margin-bottom:12px}
.pc-name{font-weight:800;font-size:24px;letter-spacing:-.5px;margin-bottom:6px}
.pc-desc{font-size:13.5px;color:var(--g300);line-height:1.55;margin-bottom:28px;min-height:44px}
.pc-price{margin-bottom:28px}
.pc-price .pp-amount{font-weight:800;font-size:48px;letter-spacing:-2px;line-height:1}
.pc-price .pp-currency{font-size:20px;font-weight:600;vertical-align:top;padding-top:10px;display:inline-block;color:var(--g200)}
.pc-price .pp-period{font-family:var(--fm);font-size:12px;color:var(--g400);margin-left:2px;letter-spacing:.5px}
.pc-price .pp-annual{font-family:var(--fm);font-size:11px;color:var(--g400);margin-top:4px;display:block}
.pc-price .pp-custom{font-weight:700;font-size:32px;letter-spacing:-1px;color:var(--white)}
.pc-btn{display:block;width:100%;padding:14px;border-radius:12px;font-weight:600;font-size:14px;text-align:center;cursor:pointer;transition:all .25s;border:none;margin-bottom:28px}
.pc-btn.outline{background:transparent;border:1px solid var(--g500);color:var(--g100)}
.pc-btn.outline:hover{border-color:var(--blue);background:var(--blue-g)}
.pc-btn.solid{background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 20px rgba(79,143,255,.25)}
.pc-btn.solid:hover{box-shadow:0 6px 32px rgba(79,143,255,.4);transform:translateY(-1px)}
.pc-btn.ghost{background:transparent;border:1px solid var(--brd2);color:var(--blue)}
.pc-btn.ghost:hover{background:var(--blue-g)}
.pc-divider{border:none;border-top:1px solid var(--brd);margin:0 0 20px}
.pc-features-label{font-family:var(--fm);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g400);margin-bottom:14px}
.pc-features{display:flex;flex-direction:column;gap:10px}
.pf-row{display:flex;align-items:flex-start;gap:10px;font-size:13.5px;color:var(--g200);line-height:1.4}
.pf-row .pf-icon{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
.pf-row .pf-icon svg{width:10px;height:10px;fill:none;stroke-width:2.5}
.pf-row.yes .pf-icon{background:rgba(52,211,153,.1)}
.pf-row.yes .pf-icon svg{stroke:var(--emerald)}
.pf-row.no .pf-icon{background:rgba(100,116,139,.08)}
.pf-row.no .pf-icon svg{stroke:var(--g500)}
.pf-row.no span{color:var(--g500)}
.pf-row.hi{color:var(--white);font-weight:500}
.pf-row .pf-new{font-family:var(--fm);font-size:9px;padding:2px 6px;border-radius:4px;background:var(--blue-g);color:var(--blue);border:1px solid var(--brd);margin-left:6px;letter-spacing:.3px;flex-shrink:0}

/* VOLUME INDICATOR */
.pc-volume{margin-bottom:24px}
.pv-label{font-family:var(--fm);font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--g400);margin-bottom:8px}
.pv-track{height:6px;background:var(--g600);border-radius:3px;overflow:hidden}
.pv-fill{height:100%;border-radius:3px;transition:width .8s var(--ease)}

/* COMPARISON TABLE */
.comp-section{position:relative;z-index:1;padding:0 40px 100px;max-width:var(--mw);margin:0 auto}
.comp-toggle{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.comp-toggle h2{font-weight:700;font-size:clamp(22px,2.5vw,30px);letter-spacing:-1px}
.expand-btn{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--blue);background:var(--blue-g);border:1px solid var(--brd2);padding:8px 16px;border-radius:8px;cursor:pointer;transition:all .2s;border-style:solid}
.expand-btn:hover{background:rgba(79,143,255,.12)}
.ct-wrap{border:1px solid var(--brd);border-radius:16px;overflow:hidden}
.ct-head{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;background:var(--bg3);border-bottom:1px solid var(--brd)}
.ct-head-cell{padding:20px 24px;font-size:14px;font-weight:600}
.ct-head-cell:first-child{color:var(--g300)}
.ct-group-label{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;background:rgba(79,143,255,.04);border-bottom:1px solid var(--brd);padding:10px 24px;font-family:var(--fm);font-size:10px;color:var(--blue);letter-spacing:1.5px;text-transform:uppercase;align-items:center}
.ct-row{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;border-bottom:1px solid var(--brd);transition:background .2s}
.ct-row:last-child{border-bottom:none}
.ct-row:hover{background:rgba(255,255,255,.015)}
.ct-cell{padding:14px 24px;font-size:13.5px;display:flex;align-items:center;gap:8px}
.ct-cell:first-child{color:var(--g200)}
.ct-cell .ct-check{color:var(--emerald);font-size:16px}
.ct-cell .ct-cross{color:var(--g500);font-size:16px}
.ct-cell .ct-val{color:var(--g100);font-weight:500}
.ct-cell .featured-col{color:var(--blue)}
.ct-head-cell.featured-col{color:var(--blue)}
.comp-hidden{display:none}

/* ADDONS */
.addons-section{position:relative;z-index:1;padding:0 40px 100px;max-width:var(--mw);margin:0 auto}
.addons-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.addon{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:24px;transition:all .3s}
.addon:hover{border-color:var(--brd2);background:var(--card-h)}
.addon-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px}
.addon-name{font-weight:600;font-size:15px;letter-spacing:-.1px}
.addon-price{font-family:var(--fm);font-size:12px;color:var(--blue);background:var(--blue-g);border:1px solid var(--brd);padding:3px 10px;border-radius:6px;white-space:nowrap}
.addon p{font-size:13px;color:var(--g300);line-height:1.6}

/* ROI CALLOUT */
.roi-bar{position:relative;z-index:1;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--bg2);padding:64px 40px}
.roi-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center}
.roi-left h2{font-weight:700;font-size:clamp(24px,3vw,36px);letter-spacing:-1px;line-height:1.2;margin-bottom:16px}
.roi-left p{font-size:15px;color:var(--g300);line-height:1.7;font-weight:350}
.roi-stats{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.rs{background:var(--card);border:1px solid var(--brd);border-radius:12px;padding:20px;text-align:center;transition:all .3s}
.rs:hover{border-color:var(--brd2)}
.rs-n{font-weight:800;font-size:28px;letter-spacing:-1px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:4px}
.rs-l{font-family:var(--fm);font-size:9.5px;color:var(--g400);letter-spacing:1px;text-transform:uppercase}

/* ENTERPRISE */
.ent-section{position:relative;z-index:1;padding:0 40px 100px;max-width:var(--mw);margin:0 auto}
.ent-card{background:var(--card);border:1px solid var(--brd);border-radius:20px;padding:0;overflow:hidden;display:grid;grid-template-columns:1fr 1fr}
.ent-left{padding:48px;border-right:1px solid var(--brd)}
.ent-right{padding:48px;background:rgba(79,143,255,.02)}
.ent-left h2{font-weight:700;font-size:clamp(24px,2.8vw,34px);letter-spacing:-1px;line-height:1.2;margin-bottom:14px}
.ent-left p{font-size:14.5px;color:var(--g300);line-height:1.7;font-weight:350;margin-bottom:24px}
.ent-features{display:flex;flex-direction:column;gap:12px}
.ef{display:flex;align-items:center;gap:12px;font-size:14px;color:var(--g100)}
.ef-dot{width:7px;height:7px;border-radius:50%;background:var(--blue);box-shadow:0 0 8px rgba(79,143,255,.4);flex-shrink:0}
.ent-form{display:flex;flex-direction:column;gap:14px}
.ef-label{font-family:var(--fm);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g400);margin-bottom:6px;display:block}
.ef-input{width:100%;background:var(--bg3);border:1px solid var(--g500);border-radius:10px;padding:12px 16px;color:var(--white);font-family:var(--f1);font-size:14px;outline:none;transition:border-color .2s}
.ef-input:focus{border-color:var(--blue)}
.ef-input::placeholder{color:var(--g500)}
.ef-select{width:100%;background:var(--bg3);border:1px solid var(--g500);border-radius:10px;padding:12px 16px;color:var(--white);font-family:var(--f1);font-size:14px;outline:none;cursor:pointer;transition:border-color .2s}
.ef-select:focus{border-color:var(--blue)}
.ent-submit{padding:14px;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;border:none;border-radius:12px;font-weight:600;font-size:14px;cursor:pointer;transition:all .25s;box-shadow:0 4px 20px rgba(79,143,255,.25)}
.ent-submit:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(79,143,255,.4)}
.ent-form-note{font-size:12px;color:var(--g400);text-align:center}

/* TESTIMONIALS */
.test-section{position:relative;z-index:1;padding:0 40px 80px;max-width:var(--mw);margin:0 auto}
.test-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.tc{background:var(--card);border:1px solid var(--brd);border-radius:16px;padding:28px;transition:all .3s}
.tc:hover{border-color:var(--brd2);background:var(--card-h)}
.tc-plan{font-family:var(--fm);font-size:9.5px;letter-spacing:1px;text-transform:uppercase;padding:3px 10px;border-radius:5px;background:var(--blue-g);border:1px solid var(--brd);color:var(--blue);display:inline-block;margin-bottom:14px}
.tc-q{font-family:var(--f2);font-size:14.5px;font-style:italic;line-height:1.65;color:var(--g100);margin-bottom:16px}
.tc-author{display:flex;align-items:center;gap:10px;border-top:1px solid var(--brd);padding-top:14px}
.tc-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;flex-shrink:0}
.tc-name{font-size:13px;font-weight:600}
.tc-role{font-size:11px;color:var(--g400)}
.tc-result{font-family:var(--fm);font-size:10px;color:var(--emerald);margin-top:2px}

/* TRUST */
.trust-row{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.trust-badge{display:flex;align-items:center;gap:12px;padding:16px 22px;border-radius:12px;border:1px solid var(--brd);background:var(--card);transition:all .3s}
.trust-badge:hover{border-color:var(--brd2);background:var(--card-h)}
.tb-icon{width:36px;height:36px;border-radius:8px;background:var(--blue-g);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.tb-icon svg{width:16px;height:16px;stroke:var(--blue);fill:none;stroke-width:1.5}
.tb-label{font-size:13px;font-weight:600}
.tb-sub{font-size:11px;color:var(--g400);margin-top:1px}

/* FAQ */
.faq-section{position:relative;z-index:1;padding:80px 40px;background:var(--bg2);border-top:1px solid var(--brd)}
.faq-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:300px 1fr;gap:64px;align-items:start}
.faq-left{position:sticky;top:88px}
.faq-left h2{font-weight:700;font-size:clamp(22px,2.5vw,30px);letter-spacing:-1px;margin-bottom:12px}
.faq-left p{font-size:14px;color:var(--g300);line-height:1.7;margin-bottom:20px}
.faq-list{display:flex;flex-direction:column;gap:8px}
.faq-item{border:1px solid var(--brd);border-radius:12px;overflow:hidden;background:var(--card)}
.faq-item.open{border-color:var(--brd2)}
.faq-q{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;cursor:pointer;font-weight:500;font-size:14.5px;color:var(--g100);user-select:none;gap:16px}
.faq-q svg{width:18px;height:18px;stroke:var(--g400);fill:none;stroke-width:2;transition:transform .3s;flex-shrink:0}
.faq-item.open .faq-q svg{transform:rotate(45deg);stroke:var(--blue)}
.faq-a{max-height:0;overflow:hidden;transition:max-height .4s var(--ease)}
.faq-item.open .faq-a{max-height:400px;padding:0 22px 18px}
.faq-a p{font-size:13.5px;color:var(--g300);line-height:1.7}

/* CTA */
.cta{position:relative;z-index:1;padding:120px 40px;text-align:center}
.cta::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:400px;background:radial-gradient(circle,rgba(79,143,255,.05),transparent 65%);pointer-events:none}
.cta h2{font-weight:800;font-size:clamp(28px,3.8vw,48px);letter-spacing:-1.5px;line-height:1.1;margin-bottom:14px;position:relative}
.cta h2 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.cta p{font-size:16px;color:var(--g300);max-width:420px;margin:0 auto 32px;line-height:1.7;position:relative}
.cta-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;position:relative}

/* FOOTER */
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2)}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:260px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}

/* BTN */
.btn-b{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;border-radius:12px;font-weight:600;font-size:14.5px;border:none;cursor:pointer;transition:all .25s;font-family:var(--f1)}
.btn-fill{background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 32px rgba(79,143,255,.3)}
.btn-fill:hover{transform:translateY(-2px);box-shadow:0 8px 48px rgba(79,143,255,.4)}
.btn-g{background:rgba(255,255,255,.04);color:var(--g100);border:1px solid var(--g500)}
.btn-g:hover{border-color:var(--blue);background:var(--blue-g2)}

/* SHARED */
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:14px;display:flex;align-items:center;gap:10px}
.stag::before{content:'';width:16px;height:1px;background:var(--blue)}
.sh{font-weight:700;font-size:clamp(24px,2.8vw,36px);letter-spacing:-1.2px;line-height:1.15;margin-bottom:12px}
.stop{margin-bottom:48px}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}

/* RESPONSIVE */
@media(max-width:1024px){
  .pricing-grid{grid-template-columns:1fr}
  .ct-head,.ct-group-label,.ct-row{grid-template-columns:1.5fr 1fr 1fr 1fr}
  .ent-card{grid-template-columns:1fr}
  .ent-left{border-right:none;border-bottom:1px solid var(--brd)}
  .test-grid{grid-template-columns:1fr}
  .addons-grid{grid-template-columns:1fr 1fr}
  .faq-in{grid-template-columns:1fr}.faq-left{position:static}
  .roi-in{grid-template-columns:1fr;gap:40px}
  .foot-in{grid-template-columns:1fr 1fr}
}
@media(max-width:640px){
  .nav-l{display:none}.nav-in{padding:0 20px}
  .hero,.pricing-wrap,.comp-section,.addons-section,.test-section,.ent-section,.cta{padding-left:20px;padding-right:20px}
  .hero{padding-top:120px}
  .ct-head,.ct-group-label,.ct-row{grid-template-columns:1.2fr 0.8fr 0.8fr 0.8fr}
  .ct-cell{padding:12px 10px;font-size:12px}
  .ct-head-cell{padding:14px 10px;font-size:12px}
  .addons-grid{grid-template-columns:1fr}
  .roi-stats{grid-template-columns:1fr 1fr}
  .foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}
  .trust-row{flex-direction:column;align-items:stretch}
  .billing-toggle{flex-wrap:wrap;border-radius:16px;padding:12px 16px}
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
<header class="hero">
  <nav class="breadcrumb"><a href="{{ route('home') }}">Home</a> / <strong>Pricing</strong></nav>
  <div class="hero-badge"><span class="badge-dot"></span> All plans include a 30-day proof of concept</div>
  <h1>Pricing that pays for <span>itself</span></h1>
  <p>Transparent plans for every stage of growth. From your first 5,000 customers to your five millionth &mdash; with ROI built into every tier.</p>
  <div class="billing-toggle">
    <span class="bt-label active" id="lblMonthly">Monthly</span>
    <button class="bt-pill" id="billingToggle" onclick="toggleBilling()"></button>
    <span class="bt-label" id="lblAnnual">Annual</span>
    <span class="bt-save" id="saveBadge" style="display:none">Save 20%</span>
  </div>
</header>

<!-- PRICING CARDS -->
<section class="pricing-wrap">
  <div class="pricing-grid">

    <!-- STARTER -->
    <div class="pc rv">
      <div class="pc-tier">Starter</div>
      <div class="pc-name">Foundation</div>
      <div class="pc-desc">For growing businesses ready to unify their data and get their first AI predictions.</div>
      <div class="pc-price">
        <span class="pp-currency">$</span><span class="pp-amount" id="price-starter">499</span><span class="pp-period">/mo</span>
        <span class="pp-annual" id="annual-starter" style="display:none">Billed annually &mdash; $5,988/year</span>
      </div>
      <div class="pc-volume">
        <div class="pv-label">Up to 25,000 customer profiles</div>
        <div class="pv-track"><div class="pv-fill" style="width:25%;background:linear-gradient(90deg,var(--blue),var(--cyan))"></div></div>
      </div>
      <button class="pc-btn outline" onclick="window.location='{{ route('client.register') }}'">Start Free Trial</button>
      <hr class="pc-divider">
      <div class="pc-features-label">What's included</div>
      <div class="pc-features">
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Up to <strong>25,000</strong> customer profiles</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Up to <strong>5 data sources</strong></span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Layers 1&ndash;5 (Ingest &rarr; Predict)</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Churn prediction &amp; CLV scoring</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>1 industry model</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Standard dashboard &amp; alerts</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Email support (48hr response)</span></div>
        <div class="pf-row no"><div class="pf-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><span>Strategy Intelligence (Layer 6)</span></div>
        <div class="pf-row no"><div class="pf-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><span>Autonomous Execution (Layer 7)</span></div>
        <div class="pf-row no"><div class="pf-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><span>Dedicated success manager</span></div>
      </div>
    </div>

    <!-- GROWTH (FEATURED) -->
    <div class="pc featured rv">
      <div class="pc-badge popular">Most Popular</div>
      <div class="pc-tier">Growth</div>
      <div class="pc-name">Intelligence</div>
      <div class="pc-desc">For scaling businesses that need the full 8-layer engine with automated execution.</div>
      <div class="pc-price">
        <span class="pp-currency">$</span><span class="pp-amount" id="price-growth">1,499</span><span class="pp-period">/mo</span>
        <span class="pp-annual" id="annual-growth" style="display:none">Billed annually &mdash; $17,988/year</span>
      </div>
      <div class="pc-volume">
        <div class="pv-label">Up to 250,000 customer profiles</div>
        <div class="pv-track"><div class="pv-fill" style="width:60%;background:linear-gradient(90deg,var(--blue),var(--violet))"></div></div>
      </div>
      <button class="pc-btn solid" onclick="window.location='{{ route('client.register') }}'">Start Free Trial</button>
      <hr class="pc-divider">
      <div class="pc-features-label">Everything in Starter, plus</div>
      <div class="pc-features">
        <div class="pf-row yes hi"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Up to <strong>250,000</strong> customer profiles</span></div>
        <div class="pf-row yes hi"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Up to <strong>20 data sources</strong></span></div>
        <div class="pf-row yes hi"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>All <strong>8 layers</strong> fully active</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Strategy Intelligence &amp; playbooks</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Autonomous Execution engine</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Up to <strong>3 industry models</strong></span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>A/B testing engine</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>ROI projections &amp; revenue tracking</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Priority support (4hr response)</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Slack integration <span class="pf-new">New</span></span></div>
        <div class="pf-row no"><div class="pf-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><span>Dedicated success manager</span></div>
        <div class="pf-row no"><div class="pf-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><span>Custom model training</span></div>
      </div>
    </div>

    <!-- ENTERPRISE -->
    <div class="pc rv">
      <div class="pc-badge new">Custom</div>
      <div class="pc-tier">Enterprise</div>
      <div class="pc-name">Intelligence+</div>
      <div class="pc-desc">For large organisations with complex data environments and mission-critical SLA requirements.</div>
      <div class="pc-price">
        <div class="pp-custom">Custom</div>
        <span class="pp-annual" style="display:block;margin-top:4px">Tailored to your volume &amp; needs</span>
      </div>
      <div class="pc-volume">
        <div class="pv-label">Unlimited customer profiles</div>
        <div class="pv-track"><div class="pv-fill" style="width:100%;background:linear-gradient(90deg,var(--violet),var(--rose))"></div></div>
      </div>
      <button class="pc-btn ghost" onclick="document.getElementById('enterprise').scrollIntoView({behavior:'smooth'})">Get a Custom Quote</button>
      <hr class="pc-divider">
      <div class="pc-features-label">Everything in Growth, plus</div>
      <div class="pc-features">
        <div class="pf-row yes hi"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span><strong>Unlimited</strong> customer profiles</span></div>
        <div class="pf-row yes hi"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span><strong>Unlimited</strong> data sources</span></div>
        <div class="pf-row yes hi"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span><strong>All 15</strong> industry models included</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Dedicated infrastructure (no shared)</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Custom model training on your data</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Dedicated Customer Success Manager</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>99.99% SLA uptime guarantee</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>On-premise / private cloud option</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>1hr emergency support response</span></div>
        <div class="pf-row yes"><div class="pf-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>DPA &amp; custom security review</span></div>
      </div>
    </div>

  </div>
</section>

<!-- FEATURE COMPARISON TABLE -->
<section class="comp-section">
  <div class="comp-toggle rv">
    <div><div class="stag" style="margin-bottom:8px">Full Comparison</div><h2 class="sh" style="margin-bottom:0">Compare all features</h2></div>
    <button class="expand-btn" id="expandBtn" onclick="toggleTable()">Show all features &darr;</button>
  </div>
  <div class="ct-wrap rv">
    <div class="ct-head">
      <div class="ct-head-cell">Feature</div>
      <div class="ct-head-cell">Starter<br><span style="font-size:11px;color:var(--g400);font-weight:400">$499/mo</span></div>
      <div class="ct-head-cell featured-col">Growth<br><span style="font-size:11px;color:var(--blue);font-weight:400">$1,499/mo</span></div>
      <div class="ct-head-cell">Enterprise<br><span style="font-size:11px;color:var(--g400);font-weight:400">Custom</span></div>
    </div>
    <div class="ct-group-label"><span>Capacity</span></div>
    <div class="ct-row"><div class="ct-cell">Customer profiles</div><div class="ct-cell"><span class="ct-val">25K</span></div><div class="ct-cell featured-col"><span class="ct-val">250K</span></div><div class="ct-cell"><span class="ct-val">Unlimited</span></div></div>
    <div class="ct-row"><div class="ct-cell">Data sources</div><div class="ct-cell"><span class="ct-val">5</span></div><div class="ct-cell featured-col"><span class="ct-val">20</span></div><div class="ct-cell"><span class="ct-val">Unlimited</span></div></div>
    <div class="ct-row"><div class="ct-cell">Industry models</div><div class="ct-cell"><span class="ct-val">1</span></div><div class="ct-cell featured-col"><span class="ct-val">3</span></div><div class="ct-cell"><span class="ct-val">All 15</span></div></div>
    <div class="ct-row"><div class="ct-cell">Data history ingested</div><div class="ct-cell"><span class="ct-val">12 months</span></div><div class="ct-cell featured-col"><span class="ct-val">36 months</span></div><div class="ct-cell"><span class="ct-val">Unlimited</span></div></div>
    <div class="ct-group-label"><span>8-Layer Architecture</span></div>
    <div class="ct-row"><div class="ct-cell">L1 Data Ingestion</div><div class="ct-cell"><span class="ct-check">&#10003;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row"><div class="ct-cell">L2 Data Unification</div><div class="ct-cell"><span class="ct-check">&#10003;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row"><div class="ct-cell">L3 Behavioural Mapping</div><div class="ct-cell"><span class="ct-check">&#10003;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row"><div class="ct-cell">L4 Pattern Detection</div><div class="ct-cell"><span class="ct-check">&#10003;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row"><div class="ct-cell">L5 Predictive Engine</div><div class="ct-cell"><span class="ct-check">&#10003;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row"><div class="ct-cell">L6 Strategy Intelligence</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row"><div class="ct-cell">L7 Autonomous Execution</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row"><div class="ct-cell">L8 Continuous Learning</div><div class="ct-cell"><span class="ct-check">&#10003;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <!-- HIDDEN ROWS -->
    <div class="ct-group-label comp-hidden"><span>AI Predictions</span></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Churn risk scoring</div><div class="ct-cell"><span class="ct-check">&#10003;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Customer Lifetime Value</div><div class="ct-cell"><span class="ct-check">&#10003;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Purchase propensity</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Next-best-action</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Revenue projections</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Custom prediction models</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-cross">&#10007;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-group-label comp-hidden"><span>Execution &amp; Automation</span></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Email trigger automation</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Push notification triggers</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">CRM auto-update</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">A/B testing engine</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Webhook integrations</div><div class="ct-cell"><span class="ct-val">5</span></div><div class="ct-cell featured-col"><span class="ct-val">50</span></div><div class="ct-cell"><span class="ct-val">Unlimited</span></div></div>
    <div class="ct-group-label comp-hidden"><span>Security &amp; Compliance</span></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">SOC 2 Type II</div><div class="ct-cell"><span class="ct-check">&#10003;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">GDPR compliance</div><div class="ct-cell"><span class="ct-check">&#10003;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">HIPAA compliant</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-cross">&#10007;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Private cloud / on-premise</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-cross">&#10007;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Custom DPA</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-cross">&#10007;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-group-label comp-hidden"><span>Support &amp; Onboarding</span></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">30-day proof of concept</div><div class="ct-cell"><span class="ct-check">&#10003;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Email support</div><div class="ct-cell"><span class="ct-val">48hr</span></div><div class="ct-cell featured-col"><span class="ct-val">4hr</span></div><div class="ct-cell"><span class="ct-val">1hr</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Live chat support</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-check">&#10003;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Dedicated success manager</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-cross">&#10007;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Custom onboarding programme</div><div class="ct-cell"><span class="ct-cross">&#10007;</span></div><div class="ct-cell featured-col"><span class="ct-cross">&#10007;</span></div><div class="ct-cell"><span class="ct-check">&#10003;</span></div></div>
    <div class="ct-row comp-hidden"><div class="ct-cell">Uptime SLA</div><div class="ct-cell"><span class="ct-val">99.9%</span></div><div class="ct-cell featured-col"><span class="ct-val">99.95%</span></div><div class="ct-cell"><span class="ct-val">99.99%</span></div></div>
  </div>
</section>

<!-- ADD-ONS -->
<section class="addons-section">
  <div class="stop rv"><div class="stag">Add-Ons</div><h2 class="sh">Extend your plan</h2><p style="font-size:15px;color:var(--g300);max-width:440px;line-height:1.7">Available on any plan. Add exactly what you need, remove what you don't.</p></div>
  <div class="addons-grid rv">
    <div class="addon"><div class="addon-top"><div class="addon-name">Extra Data Sources</div><div class="addon-price">$49 / source</div></div><p>Connect additional platforms beyond your plan limit. Each source includes real-time streaming and full historical backfill.</p></div>
    <div class="addon"><div class="addon-top"><div class="addon-name">Extra Industry Model</div><div class="addon-price">$299 / model / mo</div></div><p>Add a pre-trained model for any of our 15 supported verticals. Includes industry playbooks and benchmark data.</p></div>
    <div class="addon"><div class="addon-top"><div class="addon-name">Profile Volume Boost</div><div class="addon-price">$99 / 25K profiles</div></div><p>Scale your profile capacity in blocks of 25,000. No contract changes required &mdash; upgrade instantly from your dashboard.</p></div>
    <div class="addon"><div class="addon-top"><div class="addon-name">Historical Data Boost</div><div class="addon-price">$149 / year of history</div></div><p>Extend the historical data window for model training. Older data improves prediction accuracy for seasonal businesses.</p></div>
    <div class="addon"><div class="addon-top"><div class="addon-name">White-Label Dashboard</div><div class="addon-price">$499 / mo</div></div><p>Remove X Platforms branding and serve the dashboard under your own logo to internal stakeholders or clients.</p></div>
    <div class="addon"><div class="addon-top"><div class="addon-name">API Access (Advanced)</div><div class="addon-price">$199 / mo</div></div><p>Full REST &amp; GraphQL API access with higher rate limits, custom endpoints, and detailed developer documentation.</p></div>
  </div>
</section>

<!-- ROI CALLOUT -->
<div class="roi-bar">
  <div class="roi-in">
    <div class="roi-left rv">
      <div class="stag">ROI Reality Check</div>
      <h2>The average Growth plan customer sees <span style="color:var(--blue)">$280K</span> in Year 1 revenue impact</h2>
      <p>Across all Growth plan customers, the average annual revenue impact &mdash; from churn reduction, upsell conversion, and acquisition efficiency &mdash; is $280,000. At $1,499/month, that's a 15.6&times; return on your annual spend.</p>
    </div>
    <div class="roi-stats rv">
      <div class="rs"><div class="rs-n">15.6&times;</div><div class="rs-l">Avg ROI Year 1</div></div>
      <div class="rs"><div class="rs-n">82 days</div><div class="rs-l">Avg time to ROI</div></div>
      <div class="rs"><div class="rs-n">$280K</div><div class="rs-l">Avg revenue impact</div></div>
      <div class="rs"><div class="rs-n">97%</div><div class="rs-l">Renew after year 1</div></div>
    </div>
  </div>
</div>

<!-- TESTIMONIALS -->
<section class="test-section" style="padding-top:80px">
  <div class="stop rv" style="text-align:center"><div class="stag" style="justify-content:center">What customers say</div><h2 class="sh" style="margin:0 auto 12px;max-width:500px;text-align:center">Pricing that makes sense when you see the returns</h2></div>
  <div class="test-grid rv">
    <div class="tc">
      <div class="tc-plan">Starter Plan &rarr; Growth</div>
      <div class="tc-q">"We started on Starter to test the water. Within 60 days the predictions were so accurate we upgraded to Growth. The ROI on that upgrade paid for itself in the first month."</div>
      <div class="tc-author"><div class="tc-avatar" style="background:linear-gradient(135deg,var(--blue),var(--cyan))">PK</div><div><div class="tc-name">Priya Kapoor</div><div class="tc-role">CPO, MockMaster</div><div class="tc-result">&darr; 41% churn &middot; $4.8M recovered</div></div></div>
    </div>
    <div class="tc">
      <div class="tc-plan">Growth Plan</div>
      <div class="tc-q">"$1,499 a month felt like a lot until our first expansion revenue report came in. We made back the annual cost in the first six weeks from upsells the AI identified that our sales team had completely missed."</div>
      <div class="tc-author"><div class="tc-avatar" style="background:linear-gradient(135deg,var(--violet),var(--rose))">AT</div><div><div class="tc-name">Alexandra Tan</div><div class="tc-role">VP Revenue, ScoreMentor</div><div class="tc-result">&uarr; 3.4&times; MRR growth</div></div></div>
    </div>
    <div class="tc">
      <div class="tc-plan">Enterprise Plan</div>
      <div class="tc-q">"Enterprise pricing is custom for a reason &mdash; our requirements were complex. The team worked with us to build exactly what we needed. The first year ROI justified a 3-year commitment."</div>
      <div class="tc-author"><div class="tc-avatar" style="background:linear-gradient(135deg,var(--emerald),var(--cyan))">DW</div><div><div class="tc-name">David Wong</div><div class="tc-role">CEO, OneAustralia</div><div class="tc-result">3&times; completions &middot; $5.4M impact</div></div></div>
    </div>
  </div>
</section>

<!-- TRUST BADGES -->
<section style="position:relative;z-index:1;padding:0 40px 80px;max-width:var(--mw);margin:0 auto">
  <div class="trust-row rv">
    <div class="trust-badge"><div class="tb-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><div><div class="tb-label">SOC 2 Type II</div><div class="tb-sub">Audited annually</div></div></div>
    <div class="trust-badge"><div class="tb-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 014 10 15 15 0 01-4 10"/></svg></div><div><div class="tb-label">GDPR Compliant</div><div class="tb-sub">EU data protection</div></div></div>
    <div class="trust-badge"><div class="tb-icon"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></div><div><div class="tb-label">AES-256 Encrypted</div><div class="tb-sub">At rest and in transit</div></div></div>
    <div class="trust-badge"><div class="tb-icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div><div><div class="tb-label">30-Day POC</div><div class="tb-sub">No credit card required</div></div></div>
    <div class="trust-badge"><div class="tb-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/></svg></div><div><div class="tb-label">Cancel Anytime</div><div class="tb-sub">No lock-in contracts</div></div></div>
  </div>
</section>

<!-- ENTERPRISE FORM -->
<section class="ent-section" id="enterprise">
  <div class="ent-card rv">
    <div class="ent-left">
      <div class="stag">Enterprise</div>
      <h2>Built for organisations that can't afford to guess</h2>
      <p>Enterprise is custom-quoted based on your customer volume, number of data sources, required industry models, infrastructure preferences, and SLA needs. Every Enterprise plan includes dedicated infrastructure and a named Customer Success Manager.</p>
      <div class="ent-features">
        <div class="ef"><span class="ef-dot"></span>Unlimited customer profiles &amp; data sources</div>
        <div class="ef"><span class="ef-dot"></span>All 15 industry models, custom-trained on your data</div>
        <div class="ef"><span class="ef-dot"></span>Dedicated infrastructure &mdash; your data never on shared servers</div>
        <div class="ef"><span class="ef-dot"></span>99.99% uptime SLA with 1-hour emergency response</div>
        <div class="ef"><span class="ef-dot"></span>On-premise or private cloud deployment option</div>
        <div class="ef"><span class="ef-dot"></span>HIPAA, ISO 27001, custom DPA available</div>
        <div class="ef"><span class="ef-dot"></span>Named Customer Success Manager + quarterly business reviews</div>
        <div class="ef"><span class="ef-dot"></span>Custom security review and penetration testing</div>
      </div>
    </div>
    <div class="ent-right">
      <div class="stag" style="margin-bottom:24px">Get a Custom Quote</div>
      <div class="ent-form" id="entForm">
        <div><label class="ef-label">Full Name</label><input class="ef-input" type="text" placeholder="Jane Smith"></div>
        <div><label class="ef-label">Work Email</label><input class="ef-input" type="email" placeholder="jane@company.com"></div>
        <div><label class="ef-label">Company</label><input class="ef-input" type="text" placeholder="Your company name"></div>
        <div><label class="ef-label">Customer Volume</label>
          <select class="ef-select">
            <option value="">Select customer volume</option>
            <option>250K &ndash; 1M customers</option>
            <option>1M &ndash; 5M customers</option>
            <option>5M &ndash; 20M customers</option>
            <option>20M+ customers</option>
          </select>
        </div>
        <div><label class="ef-label">Industry</label>
          <select class="ef-select">
            <option value="">Select your industry</option>
            <option>Banking &amp; Finance</option><option>Retail &amp; E-Commerce</option><option>Healthcare</option>
            <option>Telecom</option><option>Travel &amp; Hospitality</option><option>Insurance</option>
            <option>Manufacturing</option><option>Energy &amp; Utilities</option><option>Education</option>
            <option>Real Estate</option><option>Media &amp; Entertainment</option><option>Pharma</option>
            <option>Automotive</option><option>Food &amp; Beverage</option><option>Construction</option>
          </select>
        </div>
        <button class="ent-submit" onclick="submitEntForm()">Request Enterprise Quote &rarr;</button>
        <p class="ent-form-note">We'll respond within 4 business hours with a tailored proposal.</p>
      </div>
      <div id="entSuccess" style="display:none;text-align:center;padding:40px 20px">
        <div style="font-size:40px;margin-bottom:16px">&#10003;</div>
        <div style="font-weight:700;font-size:18px;margin-bottom:8px">Request received</div>
        <p style="font-size:14px;color:var(--g300);line-height:1.6">Our enterprise team will be in touch within 4 business hours with a tailored proposal for your organisation.</p>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="faq-section" id="faq">
  <div class="faq-in">
    <div class="faq-left rv">
      <div class="stag">FAQ</div>
      <h2>Pricing questions answered</h2>
      <p>Can't find what you're looking for? Email us at <a href="mailto:pricing@xplatforms.ai" style="color:var(--blue)">pricing@xplatforms.ai</a></p>
      <a href="#" class="btn-b btn-g" style="margin-top:8px;font-size:13px;padding:10px 20px">Chat with Sales</a>
    </div>
    <div class="faq-list rv">
      <div class="faq-item open"><div class="faq-q">Is there a free trial?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Yes &mdash; every plan starts with a 30-day proof of concept using your actual data. We connect your sources, run the AI engine, and show you real predictions from your real customers. No credit card required. If you're not satisfied with the results, you pay nothing.</p></div></div>
      <div class="faq-item"><div class="faq-q">What exactly counts as a "customer profile"?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>A customer profile is one unified identity created by X Platforms &mdash; one real person, regardless of how many times they appear across your data sources. If someone exists in your CRM, your email list, and your app analytics, that's still one profile. Our deduplication is included at no extra charge.</p></div></div>
      <div class="faq-item"><div class="faq-q">Can I start on Starter and upgrade to Growth later?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Absolutely. You can upgrade at any time from your dashboard and the cost difference is prorated to your current billing cycle. Many customers start on Starter to validate the predictions, then upgrade once they're ready to activate the execution layers. Downgrading is available at your next renewal date.</p></div></div>
      <div class="faq-item"><div class="faq-q">How does annual billing work?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Annual billing is charged upfront for 12 months and saves you 20% compared to monthly billing. Starter drops from $499/mo to $399/mo ($4,788/year). Growth drops from $1,499/mo to $1,199/mo ($14,388/year). You can switch from monthly to annual at any renewal date.</p></div></div>
      <div class="faq-item"><div class="faq-q">What's the difference between Starter and Growth practically?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Starter gives you the prediction intelligence (Layers 1&ndash;5) &mdash; you see who's at risk and what the AI recommends. Growth adds Layers 6 and 7 &mdash; the engine automatically generates strategy playbooks and can trigger real-time actions like personalised emails, ad changes, and CRM updates without manual intervention. Most teams that see value in Starter upgrade within 60&ndash;90 days.</p></div></div>
      <div class="faq-item"><div class="faq-q">Do I need technical expertise to get started?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>No. Our one-click connectors require no code to set up. Most customers are fully connected and receiving predictions within 2 hours of signing up. For Enterprise deployments with custom infrastructure needs, our solutions team handles the full technical setup as part of onboarding.</p></div></div>
      <div class="faq-item"><div class="faq-q">Is my data safe and compliant?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Yes. All plans are SOC 2 Type II certified and GDPR compliant. Data is encrypted with AES-256 at rest and in transit. We never share, sell, or use your customer data for any purpose other than powering your own predictions. Enterprise plans add HIPAA compliance, ISO 27001 certification, and custom Data Processing Agreements.</p></div></div>
      <div class="faq-item"><div class="faq-q">What if I go over my profile limit?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>We'll notify you when you reach 80% of your limit. You can add profile capacity in blocks of 25,000 for $99/block at any time, or upgrade to a higher tier. We never suspend service without 14 days notice &mdash; your predictions keep running while you decide.</p></div></div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta">
  <h2 class="rv">Start with <span>your data.</span><br>See results in 30 days.</h2>
  <p class="rv">No credit card. No commitment. Connect your platforms today and get your first predictions before the month is out.</p>
  <div class="cta-btns rv">
    <a href="{{ route('client.register') }}" class="btn-b btn-fill">Start Free 30-Day Trial</a>
    <a href="{{ route('simulator') }}" class="btn-b btn-g">Try the Simulator First</a>
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

// NEURAL NET
const cv=document.getElementById('neural'),cx=cv.getContext('2d');let W,H,nd=[];
function rsz(){W=cv.width=innerWidth;H=cv.height=innerHeight}addEventListener('resize',rsz);rsz();
for(let i=0;i<Math.min(55,Math.floor(W*H/22000));i++)nd.push({x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.28,vy:(Math.random()-.5)*.28,r:Math.random()*1.4+.6,p:Math.random()*6.28});
let mmx=-1e3,mmy=-1e3;document.addEventListener('mousemove',e=>{mmx=e.clientX;mmy=e.clientY});
(function draw(){cx.clearRect(0,0,W,H);for(let i=0;i<nd.length;i++){const n=nd[i];n.x+=n.vx;n.y+=n.vy;n.p+=.01;
if(n.x<0||n.x>W)n.vx*=-1;if(n.y<0||n.y>H)n.vy*=-1;
for(let j=i+1;j<nd.length;j++){const m=nd[j],d=Math.hypot(n.x-m.x,n.y-m.y);if(d<150){cx.beginPath();cx.moveTo(n.x,n.y);cx.lineTo(m.x,m.y);cx.strokeStyle=`rgba(79,143,255,${(1-d/150)*.09})`;cx.lineWidth=.5;cx.stroke()}}
const g=Math.hypot(n.x-mmx,n.y-mmy)<160?(1-Math.hypot(n.x-mmx,n.y-mmy)/160)*.4:0;
cx.beginPath();cx.arc(n.x,n.y,n.r+Math.sin(n.p)*.35,0,6.28);cx.fillStyle=`rgba(79,143,255,${.2+g})`;cx.fill()}
requestAnimationFrame(draw)})();

// BILLING TOGGLE
let isAnnual=false;
const prices={starter:{m:499,a:399},growth:{m:1499,a:1199}};
function toggleBilling(){
  isAnnual=!isAnnual;
  document.getElementById('billingToggle').classList.toggle('yr',isAnnual);
  document.getElementById('lblMonthly').classList.toggle('active',!isAnnual);
  document.getElementById('lblAnnual').classList.toggle('active',isAnnual);
  document.getElementById('saveBadge').style.display=isAnnual?'inline-flex':'none';
  const s=isAnnual?prices.starter.a:prices.starter.m;
  const g=isAnnual?prices.growth.a:prices.growth.m;
  document.getElementById('price-starter').textContent=s.toLocaleString();
  document.getElementById('price-growth').textContent=g.toLocaleString();
  document.getElementById('annual-starter').style.display=isAnnual?'block':'none';
  document.getElementById('annual-growth').style.display=isAnnual?'block':'none';
  if(isAnnual){
    document.getElementById('annual-starter').textContent=`Billed annually — $${(s*12).toLocaleString()}/year`;
    document.getElementById('annual-growth').textContent=`Billed annually — $${(g*12).toLocaleString()}/year`;
  }
}

// COMPARISON TABLE EXPAND
let tableExpanded=false;
function toggleTable(){
  tableExpanded=!tableExpanded;
  document.querySelectorAll('.comp-hidden').forEach(el=>el.style.display=tableExpanded?'grid':'none');
  document.getElementById('expandBtn').textContent=tableExpanded?'Show less ↑':'Show all features ↓';
}

// ENTERPRISE FORM
function submitEntForm(){
  const inputs=document.querySelectorAll('#entForm input, #entForm select');
  for(const i of inputs){if(i.value.trim()===''){i.style.borderColor='var(--rose)';setTimeout(()=>i.style.borderColor='',2000);return}}
  document.getElementById('entForm').style.display='none';
  document.getElementById('entSuccess').style.display='block';
}

// FAQ
document.querySelectorAll('.faq-q').forEach(q=>{q.addEventListener('click',()=>{const it=q.parentElement,was=it.classList.contains('open');document.querySelectorAll('.faq-item').forEach(i=>i.classList.remove('open'));if(!was)it.classList.add('open')})});

// REVEALS
const obs=new IntersectionObserver(e=>{e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('vis');obs.unobserve(x.target)}})},{threshold:.08,rootMargin:'0px 0px -40px 0px'});
document.querySelectorAll('.rv').forEach(el=>obs.observe(el));

// SCROLL NAV
addEventListener('scroll',()=>{document.querySelector('.nav-bg').style.background=scrollY>40?'var(--chrome-bg)':'var(--chrome-bg-soft)'});

// SMOOTH ANCHOR SCROLL
document.querySelectorAll('a[href^="#"]').forEach(a=>{a.addEventListener('click',e=>{const h=a.getAttribute('href');if(h==='#')return;e.preventDefault();document.querySelector(h)?.scrollIntoView({behavior:'smooth'})})});
</script>
</body>
</html>
