<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI for Banking & Finance – Churn Prediction, CLV & Product Intelligence | X Platforms</title>
<meta name="description" content="X Platforms predicts banking customer churn 45 days ahead, identifies product upsell windows, and scores every account for revenue risk – with 94% accuracy. Built for retail banking, wealth management, and neobanks.">
<meta name="keywords" content="AI for banking, bank customer churn prediction, financial services AI, CLV banking, next best product banking, retail banking AI platform, wealth management AI, neobank customer intelligence">
<meta name="robots" content="index, follow, max-snippet:-1">
<link rel="canonical" href="<?php echo e(url('/banking')); ?>">
<meta property="og:title" content="AI for Banking & Finance – X Platforms">
<meta property="og:description" content="Predict which accounts will churn 45 days ahead. Score every customer for product affinity. Deliver the right offer at the right moment.">
<meta property="og:url" content="<?php echo e(url('/banking')); ?>">
<meta property="og:site_name" content="X Platforms">
<meta name="twitter:card" content="summary_large_image">
<script type="application/ld+json">
{"<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>":"https://schema.org","@type":"WebPage","name":"X Platforms for Banking & Finance","description":"AI customer intelligence platform for banks, neobanks, and wealth managers. Predicts churn, identifies upsell windows, and scores product affinity with 94% accuracy.","url":"https://xplatforms.ai/industries/banking","publisher":{"@type":"Organization","name":"X Platforms","url":"https://xplatforms.ai"}}
</script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;
  --blue:#4f8fff;--blue2:#2563eb;--cyan:#38bdf8;--violet:#818cf8;--emerald:#34d399;--amber:#fbbf24;--rose:#f472b6;
  --white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;
  --brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.15);
  --IND:#818cf8;--IND2:#f472b6;--IND-g:rgba(129,140,248,.08);--IND-brd:rgba(129,140,248,.18);
  --glow2:0 0 40px rgba(129,140,248,.1);
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
.hero{position:relative;z-index:1;padding:136px 40px 0;max-width:var(--mw);margin:0 auto}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:24px;letter-spacing:.5px}
.breadcrumb a{color:var(--g300)}.breadcrumb a:hover{color:var(--IND)}
.hero-inner{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;padding-bottom:72px}
.hero-badge{display:inline-flex;align-items:center;gap:8px;padding:5px 14px 5px 8px;background:var(--IND-g);border:1px solid var(--IND-brd);border-radius:100px;font-family:var(--fm);font-size:10.5px;letter-spacing:.8px;text-transform:uppercase;color:var(--IND);margin-bottom:20px}
.badge-dot{width:7px;height:7px;border-radius:50%;background:var(--IND);box-shadow:0 0 10px var(--IND);animation:blink 2.5s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
.hero h1{font-weight:800;font-size:clamp(32px,3.8vw,52px);line-height:1.08;letter-spacing:-2px;margin-bottom:20px}
.hero h1 em{font-style:normal;background:linear-gradient(135deg,var(--IND),var(--IND2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-desc{font-size:16px;color:var(--g200);line-height:1.75;font-weight:350;margin-bottom:32px;max-width:480px}
.hero-btns{display:flex;gap:12px;flex-wrap:wrap}
.btn-fill{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:11px;font-weight:600;font-size:14px;border:none;cursor:pointer;background:linear-gradient(135deg,var(--IND),#6366f1);color:#fff;box-shadow:0 4px 24px rgba(129,140,248,.25);font-family:var(--f1);transition:all .25s}
.btn-fill:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(129,140,248,.35)}
.btn-g{display:inline-flex;align-items:center;padding:13px 28px;border-radius:11px;font-weight:500;font-size:14px;background:rgba(255,255,255,.04);color:var(--g100);border:1px solid var(--g500);cursor:pointer;font-family:var(--f1);transition:all .25s}
.btn-g:hover{border-color:var(--IND);background:var(--IND-g)}
.ahc{background:var(--card);border:1px solid var(--IND-brd);border-radius:18px;overflow:hidden;box-shadow:var(--glow2)}
.ahc-bar{display:flex;align-items:center;gap:8px;padding:12px 18px;border-bottom:1px solid var(--brd);background:rgba(255,255,255,.015)}
.ahc-dot{width:8px;height:8px;border-radius:50%}
.ahc-title{font-family:var(--fm);font-size:11px;color:var(--g400);margin-left:6px}
.ahc-live{margin-left:auto;font-family:var(--fm);font-size:10px;color:var(--emerald);display:flex;align-items:center;gap:5px}
.ahc-live-dot{width:5px;height:5px;border-radius:50%;background:var(--emerald);box-shadow:0 0 6px var(--emerald);animation:blink 2s ease-in-out infinite}
.ahc-body{padding:20px}
.ahc-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.ahc-h-label{font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:1px;text-transform:uppercase}
.ahc-h-val{font-family:var(--fm);font-size:11px;color:var(--g300)}
.account-row{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;margin-bottom:6px;border:1px solid transparent;transition:all .3s}
.account-row:hover{background:rgba(255,255,255,.02);border-color:var(--brd)}
.ar-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;flex-shrink:0;font-family:var(--f1)}
.ar-name{font-size:13px;font-weight:500;flex:1}
.ar-value{font-family:var(--fm);font-size:11px;color:var(--g300);margin-right:8px}
.ar-risk{font-family:var(--fm);font-size:10px;padding:3px 8px;border-radius:4px;font-weight:500}
.risk-hi{background:rgba(244,114,182,.1);color:var(--rose)}
.risk-med{background:rgba(251,191,36,.1);color:var(--amber)}
.risk-lo{background:rgba(52,211,153,.1);color:var(--emerald)}
.ahc-footer{margin-top:14px;padding-top:14px;border-top:1px solid var(--brd);display:grid;grid-template-columns:1fr 1fr 1fr;gap:0}
.ahcf-item{text-align:center;padding:8px 0}
.ahcf-item:not(:last-child){border-right:1px solid var(--brd)}
.ahcf-n{font-weight:700;font-size:18px;letter-spacing:-.5px}
.ahcf-l{font-family:var(--fm);font-size:9px;color:var(--g500);letter-spacing:.8px;text-transform:uppercase;margin-top:2px}
.trust-bar{position:relative;z-index:1;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--bg2);padding:28px 40px}
.trust-in{max-width:var(--mw);margin:0 auto;display:flex;align-items:center;justify-content:center;gap:40px;flex-wrap:wrap}
.trust-item{display:flex;align-items:center;gap:10px;font-size:13px;color:var(--g300)}
.trust-item svg{width:16px;height:16px;stroke:var(--IND);fill:none;stroke-width:1.8}
.sec{position:relative;z-index:1;padding:80px 40px;max-width:var(--mw);margin:0 auto}
.sec-f{position:relative;z-index:1;padding:80px 40px;border-top:1px solid var(--brd);background:var(--bg2)}
.sec-f .sw{max-width:var(--mw);margin:0 auto}
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--IND);margin-bottom:12px;display:flex;align-items:center;gap:10px}
.stag::before{content:'';width:16px;height:1px;background:var(--IND)}
.sh{font-weight:700;font-size:clamp(24px,2.8vw,36px);letter-spacing:-1.2px;line-height:1.15;margin-bottom:12px}
.ss{font-size:15px;color:var(--g300);line-height:1.7;max-width:460px;font-weight:350}
.stop{margin-bottom:48px}
.uc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.uc{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:26px;transition:all .35s}
.uc:hover{border-color:var(--IND-brd);background:var(--card-h)}
.uc-icon{width:40px;height:40px;border-radius:10px;background:var(--IND-g);border:1px solid var(--IND-brd);display:flex;align-items:center;justify-content:center;margin-bottom:14px}
.uc-icon svg{width:18px;height:18px;stroke:var(--IND);fill:none;stroke-width:1.6}
.uc h3{font-size:15px;font-weight:600;margin-bottom:6px}
.uc p{font-size:13px;color:var(--g300);line-height:1.6}
.uc-stat{margin-top:14px;padding-top:14px;border-top:1px solid var(--brd);font-family:var(--fm);font-size:10px;color:var(--g400)}
.uc-stat strong{color:var(--IND)}
.signals-wrap{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px}
.sig-group{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:24px}
.sg-title{font-family:var(--fm);font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--g400);margin-bottom:16px}
.sig-item{display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid var(--brd);font-size:13px}
.sig-item:last-child{border-bottom:none;padding-bottom:0}
.si-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;margin-top:4px}
.si-text{color:var(--g200);flex:1;line-height:1.5}
.si-weight{font-family:var(--fm);font-size:10px;padding:2px 8px;border-radius:4px;color:var(--IND);background:var(--IND-g);border:1px solid var(--IND-brd);white-space:nowrap}
.src-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px}
.src{display:flex;align-items:center;gap:10px;padding:13px 16px;border-radius:10px;border:1px solid var(--brd);background:var(--card);transition:all .25s}
.src:hover{border-color:var(--IND-brd);background:var(--card-h)}
.src-dot{width:6px;height:6px;border-radius:50%;background:var(--IND);box-shadow:0 0 8px rgba(129,140,248,.3);flex-shrink:0}
.src span{font-size:13px;color:var(--g100)}
.kms{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--brd);border:1px solid var(--brd);border-radius:14px;overflow:hidden}
.km{background:var(--card);padding:24px 20px;text-align:center}
.km-n{font-weight:800;font-size:32px;letter-spacing:-1px;background:linear-gradient(135deg,var(--IND),var(--IND2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.km-l{font-family:var(--fm);font-size:9.5px;color:var(--g400);letter-spacing:1px;text-transform:uppercase;margin-top:4px}
.km-sub{font-size:11px;color:var(--g500);margin-top:3px}
.ba-compare{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px}
.ba-col{border-radius:12px;padding:24px;border:1px solid}
.ba-before{background:rgba(100,116,139,.04);border-color:var(--g600)}
.ba-after{background:rgba(129,140,248,.03);border-color:var(--IND-brd)}
.ba-head{font-family:var(--fm);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.bh-dot{width:8px;height:8px;border-radius:50%}
.ba-list{display:flex;flex-direction:column;gap:10px}
.ba-row{display:flex;align-items:flex-start;gap:10px;font-size:13px;color:var(--g200);line-height:1.5}
.br-icon{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
.br-icon svg{width:10px;height:10px;fill:none;stroke-width:2.5}
.cs-pull{background:var(--card);border:1px solid var(--IND-brd);border-radius:18px;padding:40px;display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
.csp-logo{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:22px;color:#fff;margin-bottom:16px}
.csp-left h3{font-weight:700;font-size:22px;letter-spacing:-.5px;margin-bottom:10px;line-height:1.2}
.csp-left p{font-size:14px;color:var(--g300);line-height:1.7;margin-bottom:20px}
.csp-right{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.csp-stat{background:var(--bg3);border:1px solid var(--brd);border-radius:12px;padding:18px;text-align:center}
.csp-n{font-weight:800;font-size:28px;letter-spacing:-1px;background:linear-gradient(135deg,var(--IND),var(--IND2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:3px}
.csp-l{font-family:var(--fm);font-size:9px;color:var(--g400);letter-spacing:.8px;text-transform:uppercase}
.quote-block{border-left:3px solid var(--IND);padding:22px 26px;border-radius:0 12px 12px 0;background:var(--IND-g);margin:24px 0}
.qb-text{font-family:var(--f2);font-size:16px;font-style:italic;line-height:1.7;color:var(--g100);margin-bottom:14px}
.qb-author{display:flex;align-items:center;gap:10px}
.qb-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--IND),var(--IND2));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff}
.qb-name{font-size:13px;font-weight:600}
.qb-role{font-size:11.5px;color:var(--g400)}
.faq-list{display:flex;flex-direction:column;gap:8px;max-width:720px;margin:0 auto}
.faq-item{border:1px solid var(--brd);border-radius:12px;overflow:hidden;background:var(--card)}
.faq-item.open{border-color:var(--IND-brd)}
.faq-q{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;cursor:pointer;font-weight:500;font-size:14.5px;color:var(--g100);user-select:none;gap:16px}
.faq-q svg{width:18px;height:18px;stroke:var(--g400);fill:none;stroke-width:2;transition:transform .3s;flex-shrink:0}
.faq-item.open .faq-q svg{transform:rotate(45deg);stroke:var(--IND)}
.faq-a{max-height:0;overflow:hidden;transition:max-height .4s var(--ease)}
.faq-item.open .faq-a{max-height:300px;padding:0 22px 18px}
.faq-a p{font-size:14px;color:var(--g300);line-height:1.7}
.other-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.og{background:var(--card);border:1px solid var(--brd);border-radius:12px;padding:20px;transition:all .3s;text-align:center}
.og:hover{border-color:var(--brd2);background:var(--card-h)}
.og-icon{width:36px;height:36px;border-radius:9px;background:rgba(79,143,255,.08);display:flex;align-items:center;justify-content:center;margin:0 auto 10px}
.og-icon svg{width:16px;height:16px;stroke:var(--blue);fill:none;stroke-width:1.6}
.og-name{font-size:13px;font-weight:600;margin-bottom:4px}
.og-stat{font-family:var(--fm);font-size:10px;color:var(--g400)}
.cta{position:relative;z-index:1;padding:96px 40px;text-align:center;border-top:1px solid var(--brd)}
.cta::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:360px;background:radial-gradient(circle,rgba(129,140,248,.06),transparent 65%);pointer-events:none}
.cta h2{font-weight:800;font-size:clamp(28px,3.5vw,44px);letter-spacing:-1.5px;line-height:1.1;margin-bottom:14px;position:relative}
.cta h2 span{background:linear-gradient(135deg,var(--IND),var(--IND2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.cta p{font-size:16px;color:var(--g300);max-width:420px;margin:0 auto 30px;line-height:1.7;position:relative}
.foot{border-top:1px solid var(--brd);background:var(--bg2);position:relative;z-index:1}
.foot-top{max-width:var(--mw);margin:0 auto;padding:64px 40px 48px;display:grid;grid-template-columns:280px 1fr;gap:64px}
.foot-logo{display:inline-flex;align-items:center;gap:11px;font-weight:700;font-size:15px;letter-spacing:-.3px;margin-bottom:14px;color:var(--white)}
.foot-logo-m{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#4f8fff,#818cf8);display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff;flex-shrink:0}
.foot-desc{font-size:13px;color:var(--g400);line-height:1.65;max-width:240px;margin-bottom:20px}
.foot-social{display:flex;gap:10px}
.fs{width:34px;height:34px;border-radius:8px;border:1px solid var(--brd);background:rgba(255,255,255,.02);display:flex;align-items:center;justify-content:center;color:var(--g400);transition:all .2s}
.fs:hover{border-color:var(--brd2);color:var(--white);background:rgba(79,143,255,.08)}
.fs svg{width:14px;height:14px}
.foot-cols{display:grid;grid-template-columns:repeat(4,1fr);gap:32px}
.foot-col h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:16px;font-weight:500}
.foot-col a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:10px;transition:color .2s;font-weight:400}
.foot-col a:hover{color:var(--white)}
.foot-bottom{max-width:var(--mw);margin:0 auto;padding:20px 40px 28px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;align-items:center;font-size:12px;color:var(--g500);flex-wrap:wrap;gap:12px}
.foot-bottom-links a{color:var(--g500);margin-left:20px;transition:color .2s}
.foot-bottom-links a:hover{color:var(--g200)}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}
@media(max-width:1024px){.hero-inner{grid-template-columns:1fr}.hero-right{display:none}.uc-grid{grid-template-columns:1fr 1fr}.signals-wrap,.ba-compare,.cs-pull{grid-template-columns:1fr}.kms{grid-template-columns:1fr 1fr}.other-grid{grid-template-columns:1fr 1fr}.foot-top{grid-template-columns:1fr}.foot-cols{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.nav-l{display:none}.nav-in{padding:0 20px}.hero{padding-top:110px}.sec,.sec-f,.cta{padding-left:20px;padding-right:20px}.uc-grid{grid-template-columns:1fr}.other-grid{grid-template-columns:1fr 1fr}.foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}}
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
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2)}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:260px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}
</style>
</head>
<body>
<canvas id="neural"></canvas>

<nav class="nav"><div class="nav-bg"></div><div class="nav-in">
  <a href="<?php echo e(url('/')); ?>" class="logo"><span class="logo-m">X</span> Platforms</a>
  <ul class="nav-l">
    <li><a href="<?php echo e(url('/')); ?>">Home</a></li>
    <li><a href="<?php echo e(route('industries')); ?>">Industries</a></li>
    <li><a href="<?php echo e(route('simulator')); ?>">Simulator</a></li>
    <li><a href="<?php echo e(route('pricing')); ?>">Pricing</a></li>
    <li style="display:flex;align-items:center;gap:10px">
      <a href="<?php echo e(route('client.login')); ?>" class="nav-login"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg> Log in</a>
      <a href="<?php echo e(route('book-demo')); ?>" class="nav-cta">Book a Demo</a>
    </li>
  </ul>
  <button class="nav-ham" id="navHam" onclick="toggleNav()" aria-label="Open menu"><span></span><span></span><span></span></button>
</div></nav>
<div class="nav-mob" id="navMob">
  <button class="nav-mob-close" onclick="toggleNav()" aria-label="Close">&#215;</button>
  <div class="nav-mob-inner">
    <a class="nav-mob-link" href="<?php echo e(url('/')); ?>">Home</a>
    <a class="nav-mob-link" href="<?php echo e(route('industries')); ?>">Industries</a>
    <a class="nav-mob-link" href="<?php echo e(route('simulator')); ?>">Simulator</a>
    <a class="nav-mob-link" href="<?php echo e(route('pricing')); ?>">Pricing</a>
    <a class="nav-mob-link" href="<?php echo e(route('client.login')); ?>">Log in</a>
    <a class="nav-mob-cta" href="<?php echo e(route('book-demo')); ?>">Book a Demo</a>
  </div>
</div>
<script>function toggleNav(){var m=document.getElementById('navMob'),h=document.getElementById('navHam');m.classList.toggle('open');h.classList.toggle('open');document.body.style.overflow=m.classList.contains('open')?'hidden':''}</script>

<section class="hero">
  <nav class="breadcrumb"><a href="<?php echo e(route('home')); ?>">Home</a> / <a href="<?php echo e(route('industries')); ?>">Industries</a> / <strong>Banking &amp; Finance</strong></nav>
  <div class="hero-inner">
    <div class="hero-left rv" style="opacity:1;transform:none">
      <div class="hero-badge"><span class="badge-dot"></span> Pre-trained Banking Model &middot; APRA &middot; FCA &middot; ASIC Ready</div>
      <h1>Know which accounts will churn <em>45 days before they act</em></h1>
      <p class="hero-desc">X Platforms monitors every digital signal &mdash; transaction frequency, app behaviour, competitor research, support sentiment &mdash; and predicts exactly which customers are leaving, what they need, and when to reach them.</p>
      <div class="hero-btns">
        <a href="<?php echo e(route('simulator')); ?>" class="btn-fill">Run Banking Simulation</a>
        <a href="<?php echo e(route('pricing')); ?>" class="btn-g">View Pricing</a>
      </div>
    </div>
    <div class="hero-right rv" style="opacity:1;transform:none">
      <div class="ahc">
        <div class="ahc-bar"><div class="ahc-dot" style="background:#ff5f57"></div><div class="ahc-dot" style="background:#febc2e"></div><div class="ahc-dot" style="background:#28c840"></div><div class="ahc-title">Account Health Dashboard &middot; Live</div><div class="ahc-live"><span class="ahc-live-dot"></span>LIVE</div></div>
        <div class="ahc-body">
          <div class="ahc-header"><span class="ahc-h-label">At-Risk Accounts</span><span class="ahc-h-val">Week of 28 Apr</span></div>
          <div class="account-row"><div class="ar-avatar" style="background:linear-gradient(135deg,var(--IND),var(--IND2))">SK</div><div class="ar-name">Sarah K.</div><div class="ar-value">$124K AUM</div><div class="ar-risk risk-hi">74% churn</div></div>
          <div class="account-row"><div class="ar-avatar" style="background:linear-gradient(135deg,#fb923c,var(--amber))">MR</div><div class="ar-name">Marcus R.</div><div class="ar-value">$68K AUM</div><div class="ar-risk risk-med">52% churn</div></div>
          <div class="account-row"><div class="ar-avatar" style="background:linear-gradient(135deg,var(--emerald),var(--cyan))">AL</div><div class="ar-name">Amy L.</div><div class="ar-value">$312K AUM</div><div class="ar-risk risk-lo">8% churn</div></div>
          <div class="account-row"><div class="ar-avatar" style="background:linear-gradient(135deg,#60a5fa,var(--IND))">JP</div><div class="ar-name">James P.</div><div class="ar-value">$89K AUM</div><div class="ar-risk risk-hi">61% churn</div></div>
          <div class="account-row"><div class="ar-avatar" style="background:linear-gradient(135deg,var(--rose),var(--IND2))">TN</div><div class="ar-name">Tara N.</div><div class="ar-value">$47K AUM</div><div class="ar-risk risk-med">43% churn</div></div>
          <div class="ahc-footer">
            <div class="ahcf-item"><div class="ahcf-n" style="color:var(--rose)">23</div><div class="ahcf-l">High Risk</div></div>
            <div class="ahcf-item"><div class="ahcf-n" style="color:var(--amber)">41</div><div class="ahcf-l">Medium Risk</div></div>
            <div class="ahcf-item"><div class="ahcf-n" style="color:var(--emerald)">$4.2M</div><div class="ahcf-l">AUM at Risk</div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="trust-bar">
  <div class="trust-in rv">
    <div class="trust-item"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>SOC 2 Type II</div>
    <div class="trust-item"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/></svg>GDPR Compliant</div>
    <div class="trust-item"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>AES-256 Encrypted</div>
    <div class="trust-item"><svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>APRA / FCA Aligned</div>
    <div class="trust-item"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>30-Day POC Included</div>
  </div>
</div>

<section class="sec">
  <div class="kms rv">
    <div class="km"><div class="km-n">28%</div><div class="km-l">Avg Churn Reduction</div><div class="km-sub">Across all banking deployments</div></div>
    <div class="km"><div class="km-n">45 Days</div><div class="km-l">Avg Prediction Window</div><div class="km-sub">Before customer acts</div></div>
    <div class="km"><div class="km-n">94%</div><div class="km-l">Model Accuracy</div><div class="km-sub">Backtested on 12M journeys</div></div>
    <div class="km"><div class="km-n">2.8&times;</div><div class="km-l">Revenue Lift</div><div class="km-sub">Avg across banking clients</div></div>
  </div>
</section>

<section class="sec-f"><div class="sw">
  <div class="stop rv"><div class="stag">Use Cases</div><h2 class="sh">What banking teams use X Platforms for</h2><p class="ss">Six intelligence capabilities pre-trained specifically on banking and financial services customer journeys.</p></div>
  <div class="uc-grid rv">
    <div class="uc"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div><h3>Churn Risk Scoring</h3><p>Score every account weekly with a churn probability and revenue-at-risk figure. Know which relationships need attention before customers start shopping alternatives.</p><div class="uc-stat">Avg prediction window: <strong>45 days</strong> ahead of action</div></div>
    <div class="uc"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 16l4-6 4 3 5-7"/></svg></div><h3>Next Best Product</h3><p>Identify which customers are ready for a savings upgrade, home loan pre-approval, or investment product &mdash; before they go looking elsewhere.</p><div class="uc-stat">Product affinity accuracy: <strong>89%</strong></div></div>
    <div class="uc"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div><h3>Account Unification</h3><p>Merge digital, branch, and phone identities into a single customer view. Finally understand the full relationship value across all products and channels.</p><div class="uc-stat">Identity match rate: <strong>87%+</strong></div></div>
    <div class="uc"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><h3>Relationship Manager Alerts</h3><p>RM dashboards updated daily with priority accounts, recommended talking points, and the right offer for each conversation &mdash; no manual prep required.</p><div class="uc-stat">RM efficiency lift: <strong>3&times;</strong> revenue per call</div></div>
    <div class="uc"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><h3>Competitive Switch Detection</h3><p>Detect competitor rate page visits, banking comparison searches, and app disengagement clusters that together signal an imminent switch &mdash; often 6 weeks ahead.</p><div class="uc-stat">Switch signal accuracy: <strong>91%</strong></div></div>
    <div class="uc"><div class="uc-icon"><svg viewBox="0 0 24 24"><path d="M4 4v5h5M20 20v-5h-5"/><path d="M20.49 9A9 9 0 005.64 5.64L4 4m16 16l-1.64-1.64A9 9 0 013.51 15"/></svg></div><h3>Continuous Model Learning</h3><p>Every outcome &mdash; retained account, product acceptance, churn &mdash; feeds back to sharpen predictions. Models improve every week from your own data.</p><div class="uc-stat">Weekly accuracy improvement: <strong>+0.3%</strong></div></div>
  </div>
</div></section>

<section class="sec">
  <div class="stop rv"><div class="stag">Intelligence Signals</div><h2 class="sh">The patterns your CRM can&rsquo;t see</h2><p class="ss">X Platforms joins signals across all your systems and reads the combinations that predict customer outcomes weeks before they happen.</p></div>
  <div class="signals-wrap rv">
    <div class="sig-group">
      <div class="sg-title">Churn Risk Signals</div>
      <div class="sig-item"><div class="si-dot" style="background:var(--rose)"></div><div class="si-text">App login frequency decline over 30+ days</div><div class="si-weight">High</div></div>
      <div class="sig-item"><div class="si-dot" style="background:var(--rose)"></div><div class="si-text">Competitor rate comparison page visits (2+)</div><div class="si-weight">High</div></div>
      <div class="sig-item"><div class="si-dot" style="background:var(--rose)"></div><div class="si-text">Unresolved support complaint in last 30 days</div><div class="si-weight">High</div></div>
      <div class="sig-item"><div class="si-dot" style="background:var(--amber)"></div><div class="si-text">Recurring transfers to external account starting</div><div class="si-weight">Med</div></div>
      <div class="sig-item"><div class="si-dot" style="background:var(--amber)"></div><div class="si-text">Transaction volume declining vs 90-day baseline</div><div class="si-weight">Med</div></div>
      <div class="sig-item"><div class="si-dot" style="background:var(--amber)"></div><div class="si-text">No product engagement after recent push notification</div><div class="si-weight">Med</div></div>
    </div>
    <div class="sig-group">
      <div class="sg-title">Upsell Opportunity Signals</div>
      <div class="sig-item"><div class="si-dot" style="background:var(--IND)"></div><div class="si-text">Monthly surplus sitting in low-interest savings</div><div class="si-weight">High</div></div>
      <div class="sig-item"><div class="si-dot" style="background:var(--IND)"></div><div class="si-text">Savings rate above 20% for 3+ months</div><div class="si-weight">High</div></div>
      <div class="sig-item"><div class="si-dot" style="background:var(--IND)"></div><div class="si-text">Property search activity detected (external signal)</div><div class="si-weight">High</div></div>
      <div class="sig-item"><div class="si-dot" style="background:var(--emerald)"></div><div class="si-text">Investment product page browsed 3+ times</div><div class="si-weight">Med</div></div>
      <div class="sig-item"><div class="si-dot" style="background:var(--emerald)"></div><div class="si-text">Income increase detected via payroll analysis</div><div class="si-weight">Med</div></div>
      <div class="sig-item"><div class="si-dot" style="background:var(--emerald)"></div><div class="si-text">Life event signal: address change + savings spike</div><div class="si-weight">Med</div></div>
    </div>
  </div>
</section>

<section class="sec-f"><div class="sw">
  <div class="stop rv"><div class="stag">Integrations</div><h2 class="sh">Every banking signal, unified</h2><p class="ss">200+ pre-built connectors for the banking tech stack. One-click setup, no data engineering required.</p></div>
  <div class="src-grid rv">
    <div class="src"><span class="src-dot"></span><span>Core Banking System</span></div>
    <div class="src"><span class="src-dot"></span><span>Mobile Banking App</span></div>
    <div class="src"><span class="src-dot"></span><span>Internet Banking Portal</span></div>
    <div class="src"><span class="src-dot"></span><span>Salesforce FS Cloud</span></div>
    <div class="src"><span class="src-dot"></span><span>Microsoft Dynamics</span></div>
    <div class="src"><span class="src-dot"></span><span>Zendesk / ServiceNow</span></div>
    <div class="src"><span class="src-dot"></span><span>Wealth Management Platform</span></div>
    <div class="src"><span class="src-dot"></span><span>Digital Onboarding Tool</span></div>
    <div class="src"><span class="src-dot"></span><span>Call Centre (Voice AI)</span></div>
    <div class="src"><span class="src-dot"></span><span>Email Marketing (Braze)</span></div>
    <div class="src"><span class="src-dot"></span><span>Push Notifications</span></div>
    <div class="src"><span class="src-dot"></span><span>Transaction Database</span></div>
    <div class="src"><span class="src-dot"></span><span>Card / POS Data</span></div>
    <div class="src"><span class="src-dot"></span><span>Open Banking API</span></div>
    <div class="src"><span class="src-dot"></span><span>Credit Decisioning</span></div>
    <div class="src"><span class="src-dot"></span><span>Branch CRM</span></div>
  </div>
</div></section>

<section class="sec">
  <div class="stop rv"><div class="stag">The Difference</div><h2 class="sh">Before and after X Platforms</h2></div>
  <div class="ba-compare rv">
    <div class="ba-col ba-before">
      <div class="ba-head"><span class="bh-dot" style="background:var(--rose)"></span>Without X Platforms</div>
      <div class="ba-list">
        <div class="ba-row"><div class="br-icon" style="background:rgba(244,114,182,.1)"><svg viewBox="0 0 24 24" stroke="var(--rose)"><path d="M18 6L6 18M6 6l12 12"/></svg></div>Churn discovered after the customer has already left</div>
        <div class="ba-row"><div class="br-icon" style="background:rgba(244,114,182,.1)"><svg viewBox="0 0 24 24" stroke="var(--rose)"><path d="M18 6L6 18M6 6l12 12"/></svg></div>RMs spend 60% of time on low-probability conversations</div>
        <div class="ba-row"><div class="br-icon" style="background:rgba(244,114,182,.1)"><svg viewBox="0 0 24 24" stroke="var(--rose)"><path d="M18 6L6 18M6 6l12 12"/></svg></div>Generic product offers sent to the whole customer base</div>
        <div class="ba-row"><div class="br-icon" style="background:rgba(244,114,182,.1)"><svg viewBox="0 0 24 24" stroke="var(--rose)"><path d="M18 6L6 18M6 6l12 12"/></svg></div>Customer data siloed across 5+ systems with no unified view</div>
        <div class="ba-row"><div class="br-icon" style="background:rgba(244,114,182,.1)"><svg viewBox="0 0 24 24" stroke="var(--rose)"><path d="M18 6L6 18M6 6l12 12"/></svg></div>Monthly reporting cycle &mdash; insights already 30 days stale</div>
      </div>
    </div>
    <div class="ba-col ba-after">
      <div class="ba-head"><span class="bh-dot" style="background:var(--emerald)"></span>With X Platforms</div>
      <div class="ba-list">
        <div class="ba-row"><div class="br-icon" style="background:rgba(52,211,153,.1)"><svg viewBox="0 0 24 24" stroke="var(--emerald)"><polyline points="20 6 9 17 4 12"/></svg></div>At-risk accounts flagged 45 days before any visible sign</div>
        <div class="ba-row"><div class="br-icon" style="background:rgba(52,211,153,.1)"><svg viewBox="0 0 24 24" stroke="var(--emerald)"><polyline points="20 6 9 17 4 12"/></svg></div>RMs receive a ranked priority list with recommended actions every Monday</div>
        <div class="ba-row"><div class="br-icon" style="background:rgba(52,211,153,.1)"><svg viewBox="0 0 24 24" stroke="var(--emerald)"><polyline points="20 6 9 17 4 12"/></svg></div>Each customer gets the right product at the right moment &mdash; 89% affinity match</div>
        <div class="ba-row"><div class="br-icon" style="background:rgba(52,211,153,.1)"><svg viewBox="0 0 24 24" stroke="var(--emerald)"><polyline points="20 6 9 17 4 12"/></svg></div>One golden profile per customer across every system and channel</div>
        <div class="ba-row"><div class="br-icon" style="background:rgba(52,211,153,.1)"><svg viewBox="0 0 24 24" stroke="var(--emerald)"><polyline points="20 6 9 17 4 12"/></svg></div>Real-time signals processed in under 200ms &mdash; act the same day</div>
      </div>
    </div>
  </div>
</section>

<section class="sec-f"><div class="sw">
  <div class="stop rv"><div class="stag">Case Study</div><h2 class="sh">Herald Bank: 72% save rate on at-risk accounts</h2></div>
  <div class="cs-pull rv">
    <div class="csp-left">
      <div class="csp-logo" style="background:linear-gradient(135deg,var(--IND),var(--IND2))">H</div>
      <h3>How Herald Bank used X Platforms to protect $4.1M in annual revenue</h3>
      <p>Herald Bank connected their core banking system, mobile app, CRM, and call centre to X Platforms. Within 60 days the churn prediction model was identifying at-risk accounts 45 days in advance with 91.8% accuracy &mdash; giving relationship managers the time and context to intervene effectively.</p>
      <p>The key discovery: accounts showing &ldquo;complaint + competitor research + app decline&rdquo; simultaneously had a 74% switch probability within 45 days. Before X Platforms, this pattern was invisible.</p>
      <div class="quote-block">
        <div class="qb-text">&ldquo;X Platforms gave our relationship managers a ranked call list every Monday with the full context on each account &mdash; what they need, what to offer, and why they&rsquo;re at risk. Revenue per call tripled in 90 days.&rdquo;</div>
        <div class="qb-author"><div class="qb-avatar">JL</div><div><div class="qb-name">James Liu</div><div class="qb-role">CTO, Herald Bank</div></div></div>
      </div>
      <a href="<?php echo e(route('case-studies')); ?>" class="btn-fill" style="margin-top:4px;display:inline-flex">Read Full Case Study &rarr;</a>
    </div>
    <div class="csp-right">
      <div class="csp-stat"><div class="csp-n">72%</div><div class="csp-l">Account Save Rate</div></div>
      <div class="csp-stat"><div class="csp-n">45 Days</div><div class="csp-l">Prediction Window</div></div>
      <div class="csp-stat"><div class="csp-n">$4.1M</div><div class="csp-l">Revenue Protected</div></div>
      <div class="csp-stat"><div class="csp-n">91.8%</div><div class="csp-l">Model Accuracy</div></div>
    </div>
  </div>
</div></section>

<section class="sec">
  <div class="stop rv" style="text-align:center"><div class="stag" style="justify-content:center">Banking FAQ</div><h2 class="sh" style="margin:0 auto 8px;max-width:500px;text-align:center">Common questions from banking teams</h2></div>
  <div class="faq-list rv">
    <div class="faq-item open"><div class="faq-q">How does X Platforms predict bank customer churn?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>X Platforms monitors transaction frequency, app login patterns, competitor research signals, and support interaction sentiment. When these signals converge into a known churn pattern &mdash; trained on millions of banking journeys &mdash; a risk score is generated and the appropriate intervention is triggered, typically 30&ndash;45 days before a customer would act.</p></div></div>
    <div class="faq-item"><div class="faq-q">Is X Platforms compliant with banking regulations?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Yes. X Platforms is SOC 2 Type II certified and supports GDPR, ASIC, FCA, and APRA compliance requirements. Enterprise deployments include custom Data Processing Agreements, dedicated infrastructure options, and full data residency controls. We don&rsquo;t process or share customer financial data &mdash; predictions are generated from behavioural signals only.</p></div></div>
    <div class="faq-item"><div class="faq-q">What banking systems can X Platforms connect to?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>200+ pre-built connectors cover core banking, mobile banking, internet banking, Salesforce Financial Services Cloud, Microsoft Dynamics, Zendesk, ServiceNow, Braze, wealth management platforms, call centre systems, and digital onboarding tools. Custom API connections are available for proprietary systems. Setup requires no data engineering.</p></div></div>
    <div class="faq-item"><div class="faq-q">How long before we see results?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Most banking deployments connect all core data sources within 2 weeks and receive their first weekly risk-scored account list within 30 days. Measurable churn improvement typically appears within 60&ndash;90 days as intervention programmes take effect. The 30-day proof of concept includes live predictions from your actual customer data.</p></div></div>
    <div class="faq-item"><div class="faq-q">Does X Platforms work for neobanks and challenger banks?<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div><div class="faq-a"><p>Yes &mdash; neobanks often see faster results because their data infrastructure is more modern and API-first. The same churn prediction, product affinity, and CLV scoring models apply. Neobank deployments typically go from connection to first predictions in under 2 weeks.</p></div></div>
  </div>
</section>

<section class="sec-f"><div class="sw">
  <div class="stop rv"><div class="stag">Explore More</div><h2 class="sh">Other industries we serve</h2></div>
  <div class="other-grid rv">
    <a href="<?php echo e(route('industries')); ?>" class="og"><div class="og-icon"><svg viewBox="0 0 24 24" stroke="var(--blue)" fill="none" stroke-width="1.6"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18"/></svg></div><div class="og-name">Retail</div><div class="og-stat">34% churn reduction</div></a>
    <a href="<?php echo e(route('industries')); ?>" class="og"><div class="og-icon"><svg viewBox="0 0 24 24" stroke="var(--emerald)" fill="none" stroke-width="1.6"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div><div class="og-name">Healthcare</div><div class="og-stat">28% compliance lift</div></a>
    <a href="<?php echo e(route('industries')); ?>" class="og"><div class="og-icon"><svg viewBox="0 0 24 24" stroke="var(--violet)" fill="none" stroke-width="1.6"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15"/></svg></div><div class="og-name">Education</div><div class="og-stat">41% dropout reduction</div></a>
    <a href="<?php echo e(route('industries')); ?>" class="og"><div class="og-icon"><svg viewBox="0 0 24 24" stroke="var(--blue)" fill="none" stroke-width="1.6"><path d="M12 2L2 7l10 5 10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div><div class="og-name">All 15 Industries</div><div class="og-stat">View all verticals &rarr;</div></a>
  </div>
</div></section>

<section class="cta">
  <h2 class="rv">See which accounts you&rsquo;re about to lose &mdash; <span>before it&rsquo;s too late.</span></h2>
  <p class="rv">Connect your banking data and get your first at-risk account list within 30 days. No code, no credit card.</p>
  <div class="rv" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a href="<?php echo e(route('simulator')); ?>" class="btn-fill">Run a Banking Simulation</a>
    <a href="<?php echo e(route('pricing')); ?>" class="btn-g">View Pricing</a>
  </div>
</section>

<footer class="foot"><div class="foot-in">
  <div><a href="<?php echo e(url('/')); ?>" class="logo"><span class="logo-m">X</span> Platforms</a><p class="foot-desc">The world's first 8-layer AI intelligence engine.</p></div>
  <div class="foot-c"><h5>Product</h5><a href="<?php echo e(route('platform.architecture')); ?>">Architecture</a><a href="#">Integrations</a><a href="<?php echo e(route('industries')); ?>">Industries</a><a href="<?php echo e(route('pricing')); ?>">Pricing</a></div>
  <div class="foot-c"><h5>Company</h5><a href="<?php echo e(route('about')); ?>">About</a><a href="<?php echo e(route('careers')); ?>">Careers</a><a href="<?php echo e(route('blog')); ?>">Blog</a><a href="<?php echo e(route('contact')); ?>">Contact</a></div>
  <div class="foot-c"><h5>Resources</h5><a href="#">Documentation</a><a href="<?php echo e(route('case-studies')); ?>">Case Studies</a><a href="#">API Reference</a><a href="<?php echo e(route('security')); ?>">Security</a></div>
</div><div class="foot-b"><span>&copy; <?php echo e(date('Y')); ?> X Platforms.</span><span><a href="<?php echo e(route('privacy')); ?>" style="color:inherit">Privacy</a> &middot; <a href="<?php echo e(route('terms')); ?>" style="color:inherit">Terms</a> &middot; <a href="<?php echo e(route('security')); ?>" style="color:inherit">Security</a></span></div></footer>

<script>
const cv=document.getElementById('neural'),cx=cv.getContext('2d');let W,H,nd=[];
function rsz(){W=cv.width=innerWidth;H=cv.height=innerHeight}addEventListener('resize',rsz);rsz();
for(let i=0;i<Math.min(55,Math.floor(W*H/22000));i++)nd.push({x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.28,vy:(Math.random()-.5)*.28,r:Math.random()*1.4+.6,p:Math.random()*6.28});
let mmx=-1e3,mmy=-1e3;document.addEventListener('mousemove',e=>{mmx=e.clientX;mmy=e.clientY});
(function draw(){cx.clearRect(0,0,W,H);nd.forEach((n,i)=>{n.x+=n.vx;n.y+=n.vy;n.p+=.01;if(n.x<0||n.x>W)n.vx*=-1;if(n.y<0||n.y>H)n.vy*=-1;nd.forEach((m,j)=>{if(j<=i)return;const d=Math.hypot(n.x-m.x,n.y-m.y);if(d<150){cx.beginPath();cx.moveTo(n.x,n.y);cx.lineTo(m.x,m.y);cx.strokeStyle=`rgba(129,140,248,${(1-d/150)*.08})`;cx.lineWidth=.5;cx.stroke()}});const g=Math.hypot(n.x-mmx,n.y-mmy)<160?(1-Math.hypot(n.x-mmx,n.y-mmy)/160)*.4:0;cx.beginPath();cx.arc(n.x,n.y,n.r+Math.sin(n.p)*.35,0,6.28);cx.fillStyle=`rgba(129,140,248,${.18+g})`;cx.fill()});requestAnimationFrame(draw)})();
document.querySelectorAll('.faq-q').forEach(q=>{q.addEventListener('click',()=>{const it=q.parentElement,was=it.classList.contains('open');document.querySelectorAll('.faq-item').forEach(i=>i.classList.remove('open'));if(!was)it.classList.add('open')})});
const obs=new IntersectionObserver(e=>{e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('vis');obs.unobserve(x.target)}})},{threshold:.08,rootMargin:'0px 0px -40px 0px'});
document.querySelectorAll('.rv').forEach(el=>obs.observe(el));
addEventListener('scroll',()=>{document.querySelector('.nav-bg').style.background=scrollY>40?'rgba(5,7,14,.92)':'rgba(5,7,14,.8)'});
</script>
</body>
</html>
