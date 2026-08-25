<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Case Studies &ndash; Real Results with X Platforms AI</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="description" content="See how MockMaster, ScoreMentor, and OneAustralia used X Platforms' 8-layer AI engine to reduce churn, grow revenue, and predict customer behaviour.">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;
  --blue:#4f8fff;--blue2:#2563eb;--blue-g:rgba(79,143,255,.08);--blue-g2:rgba(79,143,255,.04);
  --cyan:#38bdf8;--violet:#818cf8;--emerald:#34d399;--amber:#fbbf24;--rose:#f472b6;
  --white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;
  --brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.15);
  --glow:0 0 60px rgba(79,143,255,.12);--glow2:0 0 80px rgba(79,143,255,.08);
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
.hero{position:relative;z-index:1;padding:140px 40px 80px;text-align:center}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:32px;letter-spacing:.5px}
.breadcrumb a{color:var(--g300);transition:color .2s}.breadcrumb a:hover{color:var(--blue)}
.hero h1{font-weight:800;font-size:clamp(36px,5vw,64px);line-height:1.08;letter-spacing:-2px;max-width:780px;margin:0 auto 24px;opacity:0;animation:fadeUp .7s var(--ease) .15s forwards}
.hero h1 span{background:linear-gradient(135deg,var(--blue),var(--cyan),var(--violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{font-size:17px;line-height:1.75;color:var(--g200);max-width:540px;margin:0 auto 48px;font-weight:350;opacity:0;animation:fadeUp .7s var(--ease) .3s forwards}
.hero-agg{display:flex;justify-content:center;gap:0;border:1px solid var(--brd);border-radius:16px;overflow:hidden;max-width:680px;margin:0 auto;opacity:0;animation:fadeUp .7s var(--ease) .45s forwards}
.ha-item{flex:1;padding:24px 20px;text-align:center;border-right:1px solid var(--brd)}
.ha-item:last-child{border-right:none}
.ha-n{font-weight:800;font-size:28px;letter-spacing:-1px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.ha-l{font-family:var(--fm);font-size:9.5px;color:var(--g400);letter-spacing:1.2px;text-transform:uppercase;margin-top:4px}

/* CLIENT CARDS */
.cs-index{position:relative;z-index:1;padding:0 40px 80px;max-width:var(--mw);margin:0 auto;display:flex;flex-direction:column;gap:12px}
.cs-card{border:1px solid var(--brd);border-radius:16px;background:var(--card);display:grid;grid-template-columns:280px 1fr;overflow:hidden;transition:border-color .3s,box-shadow .3s;cursor:pointer}
.cs-card:hover{border-color:var(--brd2);box-shadow:var(--glow2)}
.cs-card-left{padding:32px;border-right:1px solid var(--brd);display:flex;flex-direction:column;gap:16px;justify-content:center}
.cs-logo-wrap{display:flex;align-items:center;gap:12px}
.cs-logo-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;color:#fff;flex-shrink:0}
.cs-company{font-weight:700;font-size:18px;letter-spacing:-.2px}
.cs-industry-badge{display:inline-block;font-family:var(--fm);font-size:10px;letter-spacing:1px;text-transform:uppercase;padding:4px 12px;border-radius:6px;border:1px solid var(--brd)}
.cs-card-right{padding:32px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;align-items:center}
.cs-stat{text-align:center}
.cs-stat-n{font-weight:800;font-size:32px;letter-spacing:-1px}
.cs-stat-l{font-family:var(--fm);font-size:9.5px;color:var(--g400);letter-spacing:1px;text-transform:uppercase;margin-top:4px}
.cs-stat-change{font-family:var(--fm);font-size:10px;margin-top:2px}
.cs-cta{display:flex;align-items:center;justify-content:center}
.cs-read{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border:1px solid var(--brd2);border-radius:8px;font-size:13px;font-weight:500;color:var(--g200);transition:all .25s}
.cs-read:hover{border-color:var(--blue);color:var(--white);background:var(--blue-g)}
.cs-arrow{font-size:14px;transition:transform .2s}
.cs-read:hover .cs-arrow{transform:translateX(3px)}

/* FULL CASE STUDY */
.cs-full{position:relative;z-index:1;display:none}
.cs-full.active{display:block}
.cs-back{position:relative;z-index:2;padding:20px 40px 0;max-width:var(--mw);margin:0 auto}
.back-btn{display:inline-flex;align-items:center;gap:8px;font-size:13px;color:var(--g300);border:1px solid var(--brd);padding:8px 16px;border-radius:8px;transition:all .2s;background:transparent;cursor:pointer;font-family:var(--f1)}
.back-btn:hover{color:var(--white);border-color:var(--brd2);background:var(--blue-g)}
.csh{position:relative;z-index:1;padding:60px 40px 80px;max-width:var(--mw);margin:0 auto}
.csh-top{display:flex;align-items:center;gap:20px;margin-bottom:32px}
.csh-logo{width:64px;height:64px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:26px;color:#fff}
.csh-name{font-weight:800;font-size:24px;letter-spacing:-.3px}
.csh-tags{display:flex;gap:8px;margin-top:6px;flex-wrap:wrap}
.csh-tag{font-family:var(--fm);font-size:10px;padding:3px 10px;border-radius:5px;border:1px solid var(--brd);color:var(--g300);letter-spacing:.5px}
.csh h1{font-family:var(--f2);font-weight:600;font-size:clamp(26px,3.5vw,44px);line-height:1.18;letter-spacing:-.5px;max-width:760px;margin-bottom:24px}
.csh-intro{font-size:17px;line-height:1.8;color:var(--g200);max-width:680px;font-weight:350;margin-bottom:40px}
.kms{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--brd);border:1px solid var(--brd);border-radius:16px;overflow:hidden;margin-bottom:64px}
.km{background:var(--card);padding:28px 24px;text-align:center}
.km-n{font-weight:800;font-size:36px;letter-spacing:-1.5px}
.km-l{font-family:var(--fm);font-size:9.5px;color:var(--g400);letter-spacing:1px;text-transform:uppercase;margin-top:4px}
.km-base{font-size:11px;color:var(--g500);margin-top:4px}
.cs-body{max-width:var(--mw);margin:0 auto;padding:0 40px}
.cs-layout{display:grid;grid-template-columns:1fr 320px;gap:56px;align-items:start}
.cs-sidebar{position:sticky;top:88px}
.cs-sidebar-card{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:24px;margin-bottom:16px}
.sc-title{font-family:var(--fm);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g400);margin-bottom:16px}
.sc-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--brd);font-size:13px}
.sc-row:last-child{border-bottom:none;padding-bottom:0}
.sc-row span:first-child{color:var(--g300)}
.sc-row span:last-child{color:var(--g100);font-weight:500}
.sc-layers{display:flex;flex-direction:column;gap:6px;margin-top:4px}
.sc-layer{display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:6px;background:rgba(255,255,255,.02);border:1px solid var(--brd);font-family:var(--fm);font-size:10px;color:var(--g300)}
.sc-layer .sl-dot{width:5px;height:5px;border-radius:50%}
.sc-layer .sl-status{margin-left:auto;color:var(--emerald)}
.cs-section{margin-bottom:52px}
.cs-section-label{font-family:var(--fm);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--blue);display:flex;align-items:center;gap:10px;margin-bottom:16px}
.cs-section-label::before{content:'';width:16px;height:1px;background:var(--blue)}
.cs-section h2{font-weight:700;font-size:clamp(22px,2.5vw,30px);letter-spacing:-1px;margin-bottom:16px;line-height:1.2}
.cs-section p{font-size:15px;color:var(--g200);line-height:1.8;margin-bottom:14px;font-weight:350}
.cs-section p:last-child{margin-bottom:0}
.challenge-list{display:flex;flex-direction:column;gap:12px;margin-top:8px}
.cl-item{display:flex;gap:14px;padding:16px 20px;border-radius:10px;background:rgba(244,114,182,.04);border:1px solid rgba(244,114,182,.1)}
.cl-icon{width:32px;height:32px;border-radius:8px;background:rgba(244,114,182,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.cl-icon svg{width:16px;height:16px;stroke:var(--rose);fill:none;stroke-width:2}
.cl-text{font-size:14px;color:var(--g200);line-height:1.6;padding-top:4px}
.layers-used{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
.lu-item{padding:14px 16px;border-radius:10px;border:1px solid var(--brd);background:var(--card);transition:all .3s}
.lu-item:hover{border-color:var(--brd2);background:var(--card-h)}
.lu-top{display:flex;align-items:center;gap:8px;margin-bottom:6px}
.lu-dot{width:7px;height:7px;border-radius:50%}
.lu-num{font-family:var(--fm);font-size:9px;color:var(--g500);letter-spacing:1px}
.lu-name{font-size:13px;font-weight:600}
.lu-desc{font-size:12px;color:var(--g300);line-height:1.55}
.results-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:12px}
.rg-item{padding:20px;border-radius:12px;background:var(--card);border:1px solid var(--brd);transition:all .3s}
.rg-item:hover{border-color:var(--brd2);background:var(--card-h)}
.rg-n{font-weight:800;font-size:32px;letter-spacing:-1.5px;margin-bottom:4px}
.rg-l{font-size:13px;color:var(--g300);margin-bottom:6px}
.rg-bar{height:4px;border-radius:2px;background:var(--g600)}
.rg-fill{height:100%;border-radius:2px;transition:width 1.2s var(--ease)}
.ba-compare{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px}
.ba-col{border-radius:12px;padding:24px;border:1px solid}
.ba-before{background:rgba(100,116,139,.04);border-color:var(--g600)}
.ba-head{font-family:var(--fm);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.ba-head .bh-dot{width:8px;height:8px;border-radius:50%}
.ba-list{display:flex;flex-direction:column;gap:10px}
.ba-row{display:flex;align-items:flex-start;gap:10px;font-size:13px;color:var(--g200);line-height:1.55}
.ba-row .br-icon{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
.ba-row .br-icon svg{width:10px;height:10px;stroke-width:2.5;fill:none}
.timeline{display:flex;flex-direction:column;gap:0;margin-top:12px;position:relative}
.timeline::before{content:'';position:absolute;left:15px;top:20px;bottom:20px;width:1px;background:linear-gradient(180deg,var(--blue),var(--violet));opacity:.3}
.tl-item{display:grid;grid-template-columns:40px 1fr;gap:16px;padding-bottom:28px}
.tl-item:last-child{padding-bottom:0}
.tl-marker{display:flex;flex-direction:column;align-items:center;position:relative;z-index:1}
.tl-dot{width:32px;height:32px;border-radius:50%;border:2px solid var(--blue);background:var(--bg);display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:10px;color:var(--blue);font-weight:500;flex-shrink:0}
.tl-content{padding-top:4px}
.tl-month{font-family:var(--fm);font-size:10px;color:var(--blue);letter-spacing:1px;margin-bottom:4px}
.tl-title{font-weight:600;font-size:14px;margin-bottom:4px}
.tl-text{font-size:13px;color:var(--g300);line-height:1.6}
.tl-badge{display:inline-flex;align-items:center;gap:6px;margin-top:8px;font-family:var(--fm);font-size:10px;padding:4px 10px;border-radius:5px;background:var(--blue-g);border:1px solid var(--brd);color:var(--blue)}
.quote-block{border-left:3px solid;padding:24px 28px;border-radius:0 12px 12px 0;background:rgba(255,255,255,.02);margin:28px 0}
.qb-text{font-family:var(--f2);font-size:17px;font-style:italic;line-height:1.7;color:var(--g100);margin-bottom:16px}
.qb-author{display:flex;align-items:center;gap:12px}
.qb-avatar{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#fff}
.qb-name{font-size:13px;font-weight:600}
.qb-role{font-size:12px;color:var(--g400)}
.data-table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid var(--brd);border-radius:12px;overflow:hidden;margin-top:12px}
.data-table th{background:var(--bg3);padding:11px 16px;text-align:left;font-family:var(--fm);font-size:9.5px;letter-spacing:1px;text-transform:uppercase;color:var(--g400);border-bottom:1px solid var(--brd)}
.data-table td{padding:11px 16px;border-bottom:1px solid var(--brd);color:var(--g200);font-size:13px;background:var(--card)}
.data-table tr:last-child td{border-bottom:none}
.data-table td.hi{font-weight:600}
.data-table td.pos{color:var(--emerald)}
.data-table td.neg{color:var(--rose)}
.cs-divider{border:none;border-top:1px solid var(--brd);margin:64px 0}

/* MORE CASE STUDIES */
.more-cs{position:relative;z-index:1;padding:80px 40px;background:var(--bg2);border-top:1px solid var(--brd)}
.more-cs-in{max-width:var(--mw);margin:0 auto}
.more-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:48px}
.more-card{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:28px;transition:all .35s;cursor:pointer}
.more-card:hover{border-color:var(--brd2);background:var(--card-h);transform:translateY(-3px)}
.more-card .mc-logo{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;color:#fff;margin-bottom:16px}
.more-card h3{font-size:16px;font-weight:700;margin-bottom:4px}
.more-card .mc-tag{font-family:var(--fm);font-size:10px;color:var(--g400);letter-spacing:.5px;margin-bottom:12px}
.more-card p{font-size:13px;color:var(--g300);line-height:1.6}
.more-card .mc-stat{margin-top:16px;padding-top:16px;border-top:1px solid var(--brd);font-family:var(--fm);font-size:10px;color:var(--g400)}
.more-card .mc-stat strong{color:var(--g100);font-weight:600}

/* CTA BANNER */
.cta-bar{position:relative;z-index:1;padding:100px 40px;text-align:center}
.cta-bar::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:400px;background:radial-gradient(circle,rgba(79,143,255,.05),transparent 65%);pointer-events:none}
.cta-bar h2{font-weight:800;font-size:clamp(28px,3.8vw,48px);letter-spacing:-1.5px;line-height:1.1;margin-bottom:16px;position:relative}
.cta-bar h2 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.cta-bar p{font-size:16px;color:var(--g300);max-width:440px;margin:0 auto 32px;line-height:1.7;position:relative}
/* FOOTER */
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2)}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:260px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}
.btn-b{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;border-radius:12px;font-weight:600;font-size:14.5px;border:none;cursor:pointer;transition:all .25s;font-family:var(--f1)}
.btn-fill{background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 32px rgba(79,143,255,.3)}
.btn-fill:hover{transform:translateY(-2px);box-shadow:0 8px 48px rgba(79,143,255,.4)}
.btn-g{background:rgba(255,255,255,.04);color:var(--g100);border:1px solid var(--g500)}
.btn-g:hover{border-color:var(--blue);background:var(--blue-g2)}
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:14px;display:flex;align-items:center;gap:10px}
.stag::before{content:'';width:16px;height:1px;background:var(--blue)}
.sh{font-weight:700;font-size:clamp(28px,3.2vw,40px);letter-spacing:-1.5px;line-height:1.12;margin-bottom:12px}
@keyframes fadeUp{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:translateY(0)}}
.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}
@media(max-width:1024px){
  .cs-card{grid-template-columns:1fr}.cs-card-left{border-right:none;border-bottom:1px solid var(--brd)}
  .cs-layout{grid-template-columns:1fr}.cs-sidebar{position:static}
  .kms{grid-template-columns:1fr 1fr}.more-grid{grid-template-columns:1fr}
  .results-grid,.layers-used,.ba-compare{grid-template-columns:1fr}
  .hero-agg{flex-wrap:wrap}.ha-item{min-width:120px}
}
@media(max-width:640px){
  .nav-l{display:none}.nav-in,.hero,.cs-index,.cs-back,.csh,.cs-body,.more-cs-in,.cta-bar{padding-left:20px;padding-right:20px}
  .hero{padding-top:120px}.kms{grid-template-columns:1fr}.cs-card-right{grid-template-columns:1fr 1fr}
  .foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}
}
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
</style>
</head>
<body>
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

<!-- HERO -->
<header class="hero" id="top">
  <nav class="breadcrumb"><a href="{{ url('/') }}">Home</a> / <strong>Case Studies</strong></nav>
  <h1>Real companies. <span>Measurable results.</span></h1>
  <p>See how EdTech, SaaS, and eLearning businesses used X Platforms' 8-layer AI engine to reduce churn, predict growth, and unlock revenue they never knew existed.</p>
  <div class="hero-agg rv">
    <div class="ha-item"><div class="ha-n">3</div><div class="ha-l">Companies</div></div>
    <div class="ha-item"><div class="ha-n">$14.2M</div><div class="ha-l">Revenue Unlocked</div></div>
    <div class="ha-item"><div class="ha-n">41%</div><div class="ha-l">Avg Churn Drop</div></div>
    <div class="ha-item"><div class="ha-n">90 Days</div><div class="ha-l">Avg Time to ROI</div></div>
  </div>
</header>

<!-- INDEX -->
<section class="cs-index">
  <div class="cs-card rv" onclick="openCase('mm')">
    <div class="cs-card-left">
      <div class="cs-logo-wrap"><div class="cs-logo-icon" style="background:linear-gradient(135deg,#4f8fff,#38bdf8)">M</div><div><div class="cs-company">MockMaster</div></div></div>
      <div class="cs-industry-badge" style="color:var(--blue);border-color:var(--brd2);background:var(--blue-g)">EdTech &middot; Online Test Prep</div>
      <p style="font-size:13px;color:var(--g300);line-height:1.6">AI-powered exam preparation platform serving 180,000+ students across Australia and Southeast Asia.</p>
    </div>
    <div class="cs-card-right">
      <div class="cs-stat"><div class="cs-stat-n" style="color:var(--blue)">41%</div><div class="cs-stat-l">Churn Reduction</div><div class="cs-stat-change" style="color:var(--emerald)">&darr; From 18% &rarr; 10.6%</div></div>
      <div class="cs-stat"><div class="cs-stat-n" style="color:var(--cyan)">68%</div><div class="cs-stat-l">Completion Lift</div><div class="cs-stat-change" style="color:var(--emerald)">&uarr; Course completions</div></div>
      <div class="cs-stat"><div class="cs-stat-n" style="color:var(--violet)">$4.8M</div><div class="cs-stat-l">Revenue Impact</div><div class="cs-stat-change" style="color:var(--emerald)">&uarr; Year 1 ARR</div></div>
      <div class="cs-cta"><div class="cs-read">Read Case Study <span class="cs-arrow">&rarr;</span></div></div>
    </div>
  </div>

  <div class="cs-card rv" onclick="openCase('sm')">
    <div class="cs-card-left">
      <div class="cs-logo-wrap"><div class="cs-logo-icon" style="background:linear-gradient(135deg,#818cf8,#f472b6)">S</div><div><div class="cs-company">ScoreMentor</div></div></div>
      <div class="cs-industry-badge" style="color:var(--violet);border-color:rgba(129,140,248,.2);background:rgba(129,140,248,.06)">SaaS &middot; Learning Management</div>
      <p style="font-size:13px;color:var(--g300);line-height:1.6">B2B SaaS platform providing personalised coaching and assessment tools to 2,400+ enterprise clients.</p>
    </div>
    <div class="cs-card-right">
      <div class="cs-stat"><div class="cs-stat-n" style="color:var(--violet)">3.4&times;</div><div class="cs-stat-l">MRR Growth</div><div class="cs-stat-change" style="color:var(--emerald)">&uarr; $280K &rarr; $952K MRR</div></div>
      <div class="cs-stat"><div class="cs-stat-n" style="color:var(--rose)">28%</div><div class="cs-stat-l">CAC Reduction</div><div class="cs-stat-change" style="color:var(--emerald)">&darr; Cost to acquire</div></div>
      <div class="cs-stat"><div class="cs-stat-n" style="color:var(--amber)">4.1&times;</div><div class="cs-stat-l">Expansion Revenue</div><div class="cs-stat-change" style="color:var(--emerald)">&uarr; Upsell &amp; cross-sell</div></div>
      <div class="cs-cta"><div class="cs-read">Read Case Study <span class="cs-arrow">&rarr;</span></div></div>
    </div>
  </div>

  <div class="cs-card rv" onclick="openCase('oa')">
    <div class="cs-card-left">
      <div class="cs-logo-wrap"><div class="cs-logo-icon" style="background:linear-gradient(135deg,#34d399,#38bdf8)">O</div><div><div class="cs-company">OneAustralia</div></div></div>
      <div class="cs-industry-badge" style="color:var(--emerald);border-color:rgba(52,211,153,.2);background:rgba(52,211,153,.06)">eLearning &middot; Course Marketplace</div>
      <p style="font-size:13px;color:var(--g300);line-height:1.6">Australia's largest independent online course marketplace with 3,200+ courses across 28 categories.</p>
    </div>
    <div class="cs-card-right">
      <div class="cs-stat"><div class="cs-stat-n" style="color:var(--emerald)">3&times;</div><div class="cs-stat-l">Course Completions</div><div class="cs-stat-change" style="color:var(--emerald)">&uarr; 24% &rarr; 74% rate</div></div>
      <div class="cs-stat"><div class="cs-stat-n" style="color:var(--cyan)">2.2&times;</div><div class="cs-stat-l">Revenue Per Student</div><div class="cs-stat-change" style="color:var(--emerald)">&uarr; $142 &rarr; $312 avg</div></div>
      <div class="cs-stat"><div class="cs-stat-n" style="color:var(--blue)">$5.4M</div><div class="cs-stat-l">Additional Revenue</div><div class="cs-stat-change" style="color:var(--emerald)">&uarr; Year 1 impact</div></div>
      <div class="cs-cta"><div class="cs-read">Read Case Study <span class="cs-arrow">&rarr;</span></div></div>
    </div>
  </div>
</section>

<!-- MOCKMASTER -->
<div class="cs-full" id="case-mm">
<div class="cs-back"><button class="back-btn" onclick="closeCase()">&larr; Back to All Case Studies</button></div>
<div class="csh">
  <div class="csh-top">
    <div class="csh-logo" style="background:linear-gradient(135deg,#4f8fff,#38bdf8)">M</div>
    <div class="csh-meta">
      <div class="csh-name">MockMaster</div>
      <div class="csh-tags"><span class="csh-tag">EdTech</span><span class="csh-tag">Online Test Prep</span><span class="csh-tag">B2C Subscription</span><span class="csh-tag">Australia &amp; SEA</span></div>
    </div>
  </div>
  <h1>How MockMaster cut student churn by 41% and unlocked $4.8M in retained revenue</h1>
  <p class="csh-intro">MockMaster is Australia's leading AI-powered exam preparation platform, helping students prepare for IELTS, UCAT, HSC, and professional licensing exams. With 180,000+ active subscribers and a rapidly growing student base across Southeast Asia, they faced a challenge every EdTech company dreads: high churn from disengaged students who subscribed, lost momentum, and cancelled before ever reaching their exam.</p>
  <div class="kms">
    <div class="km"><div class="km-n" style="color:var(--blue)">41%</div><div class="km-l">Churn Reduction</div><div class="km-base">18% &rarr; 10.6% monthly churn</div></div>
    <div class="km"><div class="km-n" style="color:var(--cyan)">68%</div><div class="km-l">Completion Rate Lift</div><div class="km-base">34% &rarr; 57% course completions</div></div>
    <div class="km"><div class="km-n" style="color:var(--violet)">$4.8M</div><div class="km-l">Revenue Recovered</div><div class="km-base">Year 1 ARR impact</div></div>
    <div class="km"><div class="km-n" style="color:var(--emerald)">82 Days</div><div class="km-l">Time to First ROI</div><div class="km-base">From go-live to positive return</div></div>
  </div>
</div>
<div class="cs-body"><div class="cs-layout">
<div class="cs-main">
  <div class="cs-section rv">
    <div class="cs-section-label">The Challenge</div>
    <h2>A leaky bucket nobody could see</h2>
    <p>MockMaster knew students were leaving. They just didn't know who, when, or why until it was too late. Their existing analytics showed aggregate churn rates but couldn't identify at-risk individuals before they cancelled. The team was reactive &mdash; sending blanket discount emails after a cancellation was already submitted, with a 4% recovery rate.</p>
    <p>The core problem was fragmentation. Student behaviour lived across four separate systems: the learning management system, the mobile app, a Zendesk support instance, and a Mailchimp email stack. No single view existed of a student's engagement health.</p>
    <div class="challenge-list">
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">Student data siloed across LMS, mobile app, support, and email &mdash; no unified profile possible</div></div>
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">18% monthly churn rate &mdash; significantly above the EdTech industry benchmark of 9&ndash;12%</div></div>
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">Retention campaigns were generic blasts with no personalisation or timing intelligence</div></div>
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">No ability to predict which students were at-risk weeks before cancellation decisions</div></div>
    </div>
  </div>
  <div class="cs-section rv">
    <div class="cs-section-label">The Solution</div>
    <h2>Eight layers of intelligence, built around the student journey</h2>
    <p>X Platforms connected all four of MockMaster's data sources in under two weeks, immediately resolving 87% of cross-platform identities into single student profiles. The AI engine then began building behavioural maps for every subscriber &mdash; tracking study session frequency, module completion velocity, support interactions, and email responsiveness.</p>
    <p>Within 30 days, the predictive engine was generating weekly churn risk scores for all 180,000+ active subscribers, with 93.2% backtested accuracy. The system identified a pattern that MockMaster's team had never seen: students who skipped two or more consecutive study days in their first three weeks had a 74% probability of churning within the following 21 days &mdash; regardless of how active they were before.</p>
    <div class="layers-used">
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--blue)"></div><div class="lu-num">L1</div><div class="lu-name">Data Ingestion</div></div><div class="lu-desc">LMS, mobile app, Zendesk, Mailchimp &mdash; all streaming in real time</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--cyan)"></div><div class="lu-num">L2</div><div class="lu-name">Data Unification</div></div><div class="lu-desc">87% of cross-device student identities resolved into golden profiles</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--rose)"></div><div class="lu-num">L3</div><div class="lu-name">Behavioural Mapping</div></div><div class="lu-desc">Study session graphs, streak patterns, and engagement trajectories mapped</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--amber)"></div><div class="lu-num">L4</div><div class="lu-name">Pattern Detection</div></div><div class="lu-desc">"Two-day skip in week 1&ndash;3" pattern discovered as primary churn predictor</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--violet)"></div><div class="lu-num">L5</div><div class="lu-name">Predictive Engine</div></div><div class="lu-desc">Weekly churn risk scores for all 180K+ subscribers at 93.2% accuracy</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--emerald)"></div><div class="lu-num">L6</div><div class="lu-name">Strategy Intelligence</div></div><div class="lu-desc">Personalised intervention playbooks per student risk tier and exam type</div></div>
    </div>
  </div>
  <div class="quote-block" style="border-color:var(--blue)">
    <div class="qb-text">&ldquo;Within the first month, X Platforms showed us something we'd never been able to see: a silent drop-off pattern in week two that was killing our retention. We'd been trying to fix a problem we couldn't even see clearly.&rdquo;</div>
    <div class="qb-author"><div class="qb-avatar" style="background:linear-gradient(135deg,var(--blue),var(--cyan))">PK</div><div><div class="qb-name">Priya Kapoor</div><div class="qb-role">Chief Product Officer, MockMaster</div></div></div>
  </div>
  <div class="cs-section rv">
    <div class="cs-section-label">Implementation Timeline</div>
    <h2>From integration to impact in 82 days</h2>
    <div class="timeline">
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot">W1</div></div><div class="tl-content"><div class="tl-month">Week 1&ndash;2</div><div class="tl-title">Data Integration &amp; Unification</div><div class="tl-text">All four data sources connected. 180,000+ student profiles unified. Historical 18-month data ingested for model training.</div><div class="tl-badge">&#10003; 87% identity match rate achieved</div></div></div>
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot">W3</div></div><div class="tl-content"><div class="tl-month">Week 3&ndash;4</div><div class="tl-title">Behavioural Modelling</div><div class="tl-text">Study journey graphs built for all active subscribers. Engagement health scores assigned. First churn risk cohorts identified.</div><div class="tl-badge">&#10003; "Week 2 skip" pattern discovered</div></div></div>
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot">W5</div></div><div class="tl-content"><div class="tl-month">Week 5&ndash;7</div><div class="tl-title">Predictive Engine Go-Live</div><div class="tl-text">Weekly churn risk scores deployed. First automated interventions triggered &mdash; personalised re-engagement emails with study plans.</div><div class="tl-badge">&#10003; 93.2% backtested prediction accuracy</div></div></div>
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot">M3</div></div><div class="tl-content"><div class="tl-month">Month 3</div><div class="tl-title">First Measurable Results</div><div class="tl-text">Monthly churn rate dropped from 18% to 13.4% within first 60 days. Completion rates up 22%. First ROI milestone reached.</div><div class="tl-badge">&#10003; Positive ROI at day 82</div></div></div>
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot">M6</div></div><div class="tl-content"><div class="tl-month">Month 6</div><div class="tl-title">Full Programme Maturity</div><div class="tl-text">All 8 layers fully active. Autonomous execution triggering 1,200+ personalised interventions per week. Models continuously improving.</div><div class="tl-badge">&#10003; 41% total churn reduction achieved</div></div></div>
    </div>
  </div>
  <div class="cs-section rv">
    <div class="cs-section-label">Before vs After</div>
    <h2>What changed for MockMaster's students</h2>
    <div class="ba-compare">
      <div class="ba-col ba-before">
        <div class="ba-head"><span class="bh-dot" style="background:var(--rose)"></span>Before X Platforms</div>
        <div class="ba-list">
          <div class="ba-row"><div class="br-icon" style="background:rgba(244,114,182,.1)"><svg viewBox="0 0 24 24" stroke="var(--rose)"><path d="M18 6L6 18M6 6l12 12"/></svg></div>18% monthly churn &mdash; well above industry benchmark</div>
          <div class="ba-row"><div class="br-icon" style="background:rgba(244,114,182,.1)"><svg viewBox="0 0 24 24" stroke="var(--rose)"><path d="M18 6L6 18M6 6l12 12"/></svg></div>Generic retention emails with 4% recovery rate</div>
          <div class="ba-row"><div class="br-icon" style="background:rgba(244,114,182,.1)"><svg viewBox="0 0 24 24" stroke="var(--rose)"><path d="M18 6L6 18M6 6l12 12"/></svg></div>No early warning system for at-risk students</div>
          <div class="ba-row"><div class="br-icon" style="background:rgba(244,114,182,.1)"><svg viewBox="0 0 24 24" stroke="var(--rose)"><path d="M18 6L6 18M6 6l12 12"/></svg></div>34% course completion rate</div>
          <div class="ba-row"><div class="br-icon" style="background:rgba(244,114,182,.1)"><svg viewBox="0 0 24 24" stroke="var(--rose)"><path d="M18 6L6 18M6 6l12 12"/></svg></div>4 disconnected data systems, no unified student view</div>
        </div>
      </div>
      <div class="ba-col" style="background:rgba(52,211,153,.02);border-color:var(--brd2)">
        <div class="ba-head"><span class="bh-dot" style="background:var(--emerald)"></span>After X Platforms</div>
        <div class="ba-list">
          <div class="ba-row"><div class="br-icon" style="background:rgba(52,211,153,.1)"><svg viewBox="0 0 24 24" stroke="var(--emerald)"><polyline points="20 6 9 17 4 12"/></svg></div>10.6% monthly churn &mdash; below industry benchmark</div>
          <div class="ba-row"><div class="br-icon" style="background:rgba(52,211,153,.1)"><svg viewBox="0 0 24 24" stroke="var(--emerald)"><polyline points="20 6 9 17 4 12"/></svg></div>Personalised interventions with 34% recovery rate</div>
          <div class="ba-row"><div class="br-icon" style="background:rgba(52,211,153,.1)"><svg viewBox="0 0 24 24" stroke="var(--emerald)"><polyline points="20 6 9 17 4 12"/></svg></div>3-week predictive window before any cancellation signal</div>
          <div class="ba-row"><div class="br-icon" style="background:rgba(52,211,153,.1)"><svg viewBox="0 0 24 24" stroke="var(--emerald)"><polyline points="20 6 9 17 4 12"/></svg></div>57% course completion rate</div>
          <div class="ba-row"><div class="br-icon" style="background:rgba(52,211,153,.1)"><svg viewBox="0 0 24 24" stroke="var(--emerald)"><polyline points="20 6 9 17 4 12"/></svg></div>Single unified student intelligence profile per subscriber</div>
        </div>
      </div>
    </div>
  </div>
  <div class="cs-section rv">
    <div class="cs-section-label">Results Breakdown</div>
    <h2>The numbers, twelve months later</h2>
    <div class="results-grid">
      <div class="rg-item"><div class="rg-n" style="color:var(--blue)">41%</div><div class="rg-l">Reduction in monthly churn rate</div><div class="rg-bar"><div class="rg-fill" style="background:var(--blue);width:82%"></div></div></div>
      <div class="rg-item"><div class="rg-n" style="color:var(--cyan)">68%</div><div class="rg-l">Increase in course completions</div><div class="rg-bar"><div class="rg-fill" style="background:var(--cyan);width:68%"></div></div></div>
      <div class="rg-item"><div class="rg-n" style="color:var(--violet)">34%</div><div class="rg-l">Intervention recovery rate (up from 4%)</div><div class="rg-bar"><div class="rg-fill" style="background:var(--violet);width:68%"></div></div></div>
      <div class="rg-item"><div class="rg-n" style="color:var(--emerald)">2.1&times;</div><div class="rg-l">Average student lifetime value increase</div><div class="rg-bar"><div class="rg-fill" style="background:var(--emerald);width:75%"></div></div></div>
      <div class="rg-item"><div class="rg-n" style="color:var(--amber)">93.2%</div><div class="rg-l">Churn prediction model accuracy</div><div class="rg-bar"><div class="rg-fill" style="background:var(--amber);width:93%"></div></div></div>
      <div class="rg-item"><div class="rg-n" style="color:var(--rose)">$4.8M</div><div class="rg-l">Year 1 revenue impact</div><div class="rg-bar"><div class="rg-fill" style="background:var(--rose);width:80%"></div></div></div>
    </div>
    <table class="data-table" style="margin-top:28px">
      <thead><tr><th>Metric</th><th>Before</th><th>After</th><th>Change</th></tr></thead>
      <tbody>
        <tr><td>Monthly Churn Rate</td><td>18.0%</td><td class="hi">10.6%</td><td class="pos">&darr; 41%</td></tr>
        <tr><td>Course Completion Rate</td><td>34%</td><td class="hi">57%</td><td class="pos">&uarr; 68%</td></tr>
        <tr><td>Intervention Recovery Rate</td><td>4%</td><td class="hi">34%</td><td class="pos">&uarr; 750%</td></tr>
        <tr><td>Avg Student Lifetime Value</td><td>$184</td><td class="hi">$386</td><td class="pos">&uarr; 110%</td></tr>
        <tr><td>Weekly At-Risk Students Identified</td><td>0</td><td class="hi">2,400+</td><td class="pos">New capability</td></tr>
        <tr><td>NPS Score</td><td>34</td><td class="hi">61</td><td class="pos">&uarr; 79%</td></tr>
      </tbody>
    </table>
  </div>
  <div class="quote-block" style="border-color:var(--cyan)">
    <div class="qb-text">&ldquo;The ROI calculation was simple. We were losing $4.8M a year to preventable churn. X Platforms cost a fraction of that and delivered results within 90 days. It wasn't a tough decision once we saw the numbers.&rdquo;</div>
    <div class="qb-author"><div class="qb-avatar" style="background:linear-gradient(135deg,var(--cyan),var(--blue))">JM</div><div><div class="qb-name">James Morrow</div><div class="qb-role">CEO, MockMaster</div></div></div>
  </div>
</div>
<div class="cs-sidebar">
  <div class="cs-sidebar-card"><div class="sc-title">Company Overview</div>
    <div class="sc-row"><span>Industry</span><span>EdTech</span></div>
    <div class="sc-row"><span>Founded</span><span>2018</span></div>
    <div class="sc-row"><span>Headquarters</span><span>Sydney, Australia</span></div>
    <div class="sc-row"><span>Active Subscribers</span><span>180,000+</span></div>
    <div class="sc-row"><span>Markets</span><span>AU, NZ, SG, IN</span></div>
    <div class="sc-row"><span>ARR at Deployment</span><span>$8.2M</span></div>
  </div>
  <div class="cs-sidebar-card"><div class="sc-title">Data Sources Connected</div>
    <div class="sc-row"><span>LMS Platform</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Mobile App</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Zendesk Support</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Mailchimp Email</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Payment Gateway</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>App Store Reviews</span><span style="color:var(--amber)">Monitoring</span></div>
  </div>
  <div class="cs-sidebar-card"><div class="sc-title">Layers Activated</div>
    <div class="sc-layers">
      <div class="sc-layer"><span class="sl-dot" style="background:var(--blue)"></span>L1 Data Ingestion<span class="sl-status">Active</span></div>
      <div class="sc-layer"><span class="sl-dot" style="background:var(--cyan)"></span>L2 Unification<span class="sl-status">Active</span></div>
      <div class="sc-layer"><span class="sl-dot" style="background:var(--rose)"></span>L3 Behavioural Map<span class="sl-status">Active</span></div>
      <div class="sc-layer"><span class="sl-dot" style="background:var(--amber)"></span>L4 Pattern Detection<span class="sl-status">Active</span></div>
      <div class="sc-layer"><span class="sl-dot" style="background:var(--violet)"></span>L5 Prediction<span class="sl-status">Active</span></div>
      <div class="sc-layer"><span class="sl-dot" style="background:var(--emerald)"></span>L6 Strategy<span class="sl-status">Active</span></div>
      <div class="sc-layer" style="opacity:.4"><span class="sl-dot" style="background:var(--g400)"></span>L7 Execution<span class="sl-status" style="color:var(--g500)">Q2 2026</span></div>
      <div class="sc-layer"><span class="sl-dot" style="background:#60a5fa"></span>L8 Learning<span class="sl-status">Active</span></div>
    </div>
  </div>
  <div class="cs-sidebar-card" style="background:var(--blue-g);border-color:var(--brd2)">
    <div class="sc-title">Similar to MockMaster?</div>
    <p style="font-size:13px;color:var(--g200);line-height:1.6;margin-bottom:14px">If you're in EdTech or running a subscription learning product, see how X Platforms can work for your student base.</p>
    <a href="{{ route('client.register') }}" class="btn-b btn-fill" style="font-size:13px;padding:10px 20px;width:100%;justify-content:center">Book an EdTech Demo</a>
  </div>
</div>
</div></div>
</div>

<!-- SCOREMENTOR -->
<div class="cs-full" id="case-sm">
<div class="cs-back"><button class="back-btn" onclick="closeCase()">&larr; Back to All Case Studies</button></div>
<div class="csh">
  <div class="csh-top">
    <div class="csh-logo" style="background:linear-gradient(135deg,#818cf8,#f472b6)">S</div>
    <div class="csh-meta">
      <div class="csh-name">ScoreMentor</div>
      <div class="csh-tags"><span class="csh-tag">SaaS</span><span class="csh-tag">B2B Learning Management</span><span class="csh-tag">Enterprise</span><span class="csh-tag">Global</span></div>
    </div>
  </div>
  <h1>How ScoreMentor grew MRR 3.4&times; and reduced customer acquisition cost by 28%</h1>
  <p class="csh-intro">ScoreMentor is a B2B SaaS platform offering AI-powered coaching, assessment tools, and team performance analytics to enterprise learning and development teams. With 2,400+ client organisations &mdash; from mid-market to Fortune 500 &mdash; they had strong product-market fit but were struggling to translate it into predictable, scalable revenue growth.</p>
  <div class="kms">
    <div class="km"><div class="km-n" style="color:var(--violet)">3.4&times;</div><div class="km-l">MRR Growth</div><div class="km-base">$280K &rarr; $952K monthly</div></div>
    <div class="km"><div class="km-n" style="color:var(--rose)">28%</div><div class="km-l">CAC Reduction</div><div class="km-base">$3,200 &rarr; $2,304 per customer</div></div>
    <div class="km"><div class="km-n" style="color:var(--amber)">4.1&times;</div><div class="km-l">Expansion Revenue</div><div class="km-base">Net revenue retention 142%</div></div>
    <div class="km"><div class="km-n" style="color:var(--emerald)">67 Days</div><div class="km-l">Time to First ROI</div><div class="km-base">Fastest deployment to date</div></div>
  </div>
</div>
<div class="cs-body"><div class="cs-layout">
<div class="cs-main">
  <div class="cs-section rv">
    <div class="cs-section-label">The Challenge</div>
    <h2>Growing fast but flying blind on revenue signals</h2>
    <p>ScoreMentor's sales team was closing well but the company had a hidden problem: they couldn't predict which of their 2,400 clients were about to expand their licences, and which were quietly disengaging before renewal. Their CRM showed last-contacted dates and deal stages but nothing about how clients were actually using the product.</p>
    <p>Equally problematic: their marketing spend was spread across 11 channels with no intelligence about which were attracting clients with genuine long-term value versus low-quality accounts that churned in year two.</p>
    <div class="challenge-list">
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">No visibility into which clients were expansion-ready vs at-risk of non-renewal</div></div>
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">Marketing budget across 11 channels with no CLV-adjusted attribution model</div></div>
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">Sales team spending 60% of time on low-probability expansion conversations</div></div>
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">NRR sitting at 108% &mdash; strong but well below best-in-class SaaS benchmark of 130%+</div></div>
    </div>
  </div>
  <div class="cs-section rv">
    <div class="cs-section-label">The Solution</div>
    <h2>From gut-feel sales to AI-precision revenue intelligence</h2>
    <p>X Platforms ingested ScoreMentor's product usage data, CRM, support logs, billing history, and marketing attribution events. Within three weeks, every client account had a live health score and a predictive expansion index &mdash; a composite score predicting the probability and timing of upsell or churn.</p>
    <p>The strategic intelligence layer generated account-specific playbooks: which clients to call this week, what to offer them, and what the projected value of each conversation was. The marketing intelligence reweighted channel attribution based on which sources delivered clients with the highest 24-month LTV &mdash; cutting spend on high-volume, low-LTV channels by 44%.</p>
    <div class="layers-used">
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--blue)"></div><div class="lu-num">L1</div><div class="lu-name">Data Ingestion</div></div><div class="lu-desc">Product usage, CRM, billing, support, and 11 marketing channel feeds connected</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--violet)"></div><div class="lu-num">L5</div><div class="lu-name">Predictive Engine</div></div><div class="lu-desc">Expansion index + churn risk scored weekly for all 2,400 accounts</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--emerald)"></div><div class="lu-num">L6</div><div class="lu-name">Strategy Intelligence</div></div><div class="lu-desc">Weekly account-specific growth playbooks delivered to sales team</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--amber)"></div><div class="lu-num">L4</div><div class="lu-name">Pattern Detection</div></div><div class="lu-desc">LTV-adjusted marketing attribution revealed true channel ROI</div></div>
    </div>
  </div>
  <div class="quote-block" style="border-color:var(--violet)">
    <div class="qb-text">&ldquo;X Platforms gave our sales team a priority list every Monday morning. Instead of guessing who to call, they knew exactly which 40 accounts were expansion-ready that week and what to offer each one. Revenue per sales hour tripled.&rdquo;</div>
    <div class="qb-author"><div class="qb-avatar" style="background:linear-gradient(135deg,var(--violet),var(--rose))">AT</div><div><div class="qb-name">Alexandra Tan</div><div class="qb-role">VP Revenue, ScoreMentor</div></div></div>
  </div>
  <div class="cs-section rv">
    <div class="cs-section-label">Implementation Timeline</div>
    <h2>67 days from integration to measurable ROI</h2>
    <div class="timeline">
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot" style="border-color:var(--violet);color:var(--violet)">W1</div></div><div class="tl-content"><div class="tl-month" style="color:var(--violet)">Week 1&ndash;2</div><div class="tl-title">Data Integration</div><div class="tl-text">CRM, product DB, billing, support, and all 11 marketing channel APIs connected. 24-month historical data ingested.</div><div class="tl-badge" style="color:var(--violet);background:rgba(129,140,248,.06);border-color:rgba(129,140,248,.2)">&#10003; 2,400 client profiles unified</div></div></div>
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot" style="border-color:var(--violet);color:var(--violet)">W3</div></div><div class="tl-content"><div class="tl-month" style="color:var(--violet)">Week 3&ndash;5</div><div class="tl-title">Account Health Scoring</div><div class="tl-text">Expansion index and churn risk models trained. First weekly account priority list generated for sales team.</div><div class="tl-badge" style="color:var(--violet);background:rgba(129,140,248,.06);border-color:rgba(129,140,248,.2)">&#10003; 89.4% prediction accuracy</div></div></div>
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot" style="border-color:var(--violet);color:var(--violet)">W6</div></div><div class="tl-content"><div class="tl-month" style="color:var(--violet)">Week 6&ndash;8</div><div class="tl-title">Marketing Reallocation</div><div class="tl-text">CLV-adjusted attribution deployed. High-LTV channel spend increased. Low-LTV sources cut by 44%. First week: CAC dropped 12%.</div><div class="tl-badge" style="color:var(--violet);background:rgba(129,140,248,.06);border-color:rgba(129,140,248,.2)">&#10003; ROI positive at day 67</div></div></div>
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot" style="border-color:var(--violet);color:var(--violet)">M4</div></div><div class="tl-content"><div class="tl-month" style="color:var(--violet)">Month 4&ndash;6</div><div class="tl-title">Full Revenue Engine Active</div><div class="tl-text">Automated expansion opportunity alerts, churn prevention playbooks, and NRR tracking all running. MRR growth accelerating.</div><div class="tl-badge" style="color:var(--violet);background:rgba(129,140,248,.06);border-color:rgba(129,140,248,.2)">&#10003; MRR reached $952K (from $280K)</div></div></div>
    </div>
  </div>
  <div class="cs-section rv">
    <div class="cs-section-label">Results Breakdown</div>
    <h2>Twelve months of compounding impact</h2>
    <table class="data-table">
      <thead><tr><th>Metric</th><th>Before</th><th>After (12M)</th><th>Change</th></tr></thead>
      <tbody>
        <tr><td>Monthly Recurring Revenue</td><td>$280K</td><td class="hi">$952K</td><td class="pos">&uarr; 3.4&times;</td></tr>
        <tr><td>Customer Acquisition Cost</td><td>$3,200</td><td class="hi">$2,304</td><td class="pos">&darr; 28%</td></tr>
        <tr><td>Net Revenue Retention</td><td>108%</td><td class="hi">142%</td><td class="pos">&uarr; 34pp</td></tr>
        <tr><td>Expansion Revenue / Month</td><td>$18K</td><td class="hi">$74K</td><td class="pos">&uarr; 4.1&times;</td></tr>
        <tr><td>Sales Cycle Length</td><td>47 days</td><td class="hi">29 days</td><td class="pos">&darr; 38%</td></tr>
        <tr><td>Accounts with Health Score</td><td>0</td><td class="hi">2,400+</td><td class="pos">New capability</td></tr>
        <tr><td>Revenue per Sales Hour</td><td>$840</td><td class="hi">$2,520</td><td class="pos">&uarr; 3&times;</td></tr>
      </tbody>
    </table>
  </div>
  <div class="quote-block" style="border-color:var(--rose)">
    <div class="qb-text">&ldquo;We'd been investing equally in 11 marketing channels. X Platforms showed us that two of them were delivering clients with 4&times; the lifetime value of the others. We reallocated in week 8. Our CAC never recovered to the old number &mdash; in the best possible way.&rdquo;</div>
    <div class="qb-author"><div class="qb-avatar" style="background:linear-gradient(135deg,var(--rose),var(--amber))">RN</div><div><div class="qb-name">Rahul Nair</div><div class="qb-role">CMO, ScoreMentor</div></div></div>
  </div>
</div>
<div class="cs-sidebar">
  <div class="cs-sidebar-card"><div class="sc-title">Company Overview</div>
    <div class="sc-row"><span>Industry</span><span>B2B SaaS</span></div>
    <div class="sc-row"><span>Founded</span><span>2020</span></div>
    <div class="sc-row"><span>Headquarters</span><span>Melbourne, Australia</span></div>
    <div class="sc-row"><span>Client Accounts</span><span>2,400+</span></div>
    <div class="sc-row"><span>MRR at Deployment</span><span>$280K</span></div>
    <div class="sc-row"><span>Team Size</span><span>68 employees</span></div>
  </div>
  <div class="cs-sidebar-card"><div class="sc-title">Data Sources Connected</div>
    <div class="sc-row"><span>Product Database</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Salesforce CRM</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Billing (Stripe)</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Intercom Support</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Marketing Channels</span><span style="color:var(--emerald)">&#10003; 11 sources</span></div>
  </div>
  <div class="cs-sidebar-card" style="background:rgba(129,140,248,.04);border-color:rgba(129,140,248,.15)">
    <div class="sc-title">Similar to ScoreMentor?</div>
    <p style="font-size:13px;color:var(--g200);line-height:1.6;margin-bottom:14px">Growing B2B SaaS with expansion revenue you're leaving on the table? Let's calculate your opportunity.</p>
    <a href="{{ route('client.register') }}" class="btn-b btn-fill" style="font-size:13px;padding:10px 20px;width:100%;justify-content:center">Book a SaaS Demo</a>
  </div>
</div>
</div></div>
</div>

<!-- ONEAUSTRALIA -->
<div class="cs-full" id="case-oa">
<div class="cs-back"><button class="back-btn" onclick="closeCase()">&larr; Back to All Case Studies</button></div>
<div class="csh">
  <div class="csh-top">
    <div class="csh-logo" style="background:linear-gradient(135deg,#34d399,#38bdf8)">O</div>
    <div class="csh-meta">
      <div class="csh-name">OneAustralia</div>
      <div class="csh-tags"><span class="csh-tag">eLearning Marketplace</span><span class="csh-tag">Course Platform</span><span class="csh-tag">B2C</span><span class="csh-tag">3,200+ Courses</span></div>
    </div>
  </div>
  <h1>How OneAustralia tripled course completions and doubled revenue per student in 12 months</h1>
  <p class="csh-intro">OneAustralia is the country's largest independent online course marketplace, connecting 420,000 registered learners with 3,200+ courses across 28 categories. Despite strong top-of-funnel numbers, the platform suffered from a completion crisis: most students who enrolled never finished.</p>
  <div class="kms">
    <div class="km"><div class="km-n" style="color:var(--emerald)">3&times;</div><div class="km-l">Completion Rate</div><div class="km-base">24% &rarr; 74% avg completion</div></div>
    <div class="km"><div class="km-n" style="color:var(--cyan)">2.2&times;</div><div class="km-l">Revenue Per Student</div><div class="km-base">$142 &rarr; $312 average</div></div>
    <div class="km"><div class="km-n" style="color:var(--blue)">$5.4M</div><div class="km-l">Additional Revenue</div><div class="km-base">Year 1 platform impact</div></div>
    <div class="km"><div class="km-n" style="color:var(--amber)">91 Days</div><div class="km-l">Time to First ROI</div><div class="km-base">Full deployment to impact</div></div>
  </div>
</div>
<div class="cs-body"><div class="cs-layout">
<div class="cs-main">
  <div class="cs-section rv">
    <div class="cs-section-label">The Challenge</div>
    <h2>A marketplace with an invisible engagement crisis</h2>
    <p>OneAustralia's homepage metrics looked healthy &mdash; 12,000 new enrolments per month, strong Google rankings, a diverse course catalogue. But buried beneath the acquisition numbers was a crisis: only 24% of enrolled students ever finished a course. The other 76% enrolled, started, then silently disappeared.</p>
    <p>Instructors who invested months building courses were earning less than projected. The marketplace's review quality was suffering because only a fraction of students reached the review-writing stage. And cross-sell opportunities were almost nonexistent because the platform had no intelligence about who was thriving and who had disengaged.</p>
    <div class="challenge-list">
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">76% of students never completed an enrolled course &mdash; creating a review and revenue gap</div></div>
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">No intelligence on which learners were at-risk of dropout vs likely to purchase again</div></div>
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">Cross-sell rate below 8% &mdash; most students bought once and never returned</div></div>
      <div class="cl-item"><div class="cl-icon"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div><div class="cl-text">Content quality variance: some courses had 80% completion, others had 12% &mdash; no insight why</div></div>
    </div>
  </div>
  <div class="cs-section rv">
    <div class="cs-section-label">The Solution</div>
    <h2>Building an intelligence layer across the learner journey</h2>
    <p>X Platforms connected OneAustralia's LMS, payment platform, email system, mobile app, and support data into a unified learner intelligence engine. The behavioural mapping layer built individual learning profiles for all 420,000 registered users.</p>
    <p>A key insight from the pattern detection layer transformed their product strategy: students who watched at least 40% of a course within their first 7 days completed at a 91% rate. Those who didn't hit 40% in week one had only an 11% completion rate regardless of subsequent interventions. This "40/7 rule" became the foundation of OneAustralia's entire learner success programme.</p>
    <div class="layers-used">
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--blue)"></div><div class="lu-num">L1</div><div class="lu-name">Data Ingestion</div></div><div class="lu-desc">LMS, Stripe payments, email, mobile, support &mdash; all unified in real time</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--rose)"></div><div class="lu-num">L3</div><div class="lu-name">Behavioural Mapping</div></div><div class="lu-desc">Individual learning momentum profiles for 420,000 registered learners</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--amber)"></div><div class="lu-num">L4</div><div class="lu-name">Pattern Detection</div></div><div class="lu-desc">"40/7 rule" discovered &mdash; the critical engagement threshold in week one</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--violet)"></div><div class="lu-num">L5</div><div class="lu-name">Predictive Engine</div></div><div class="lu-desc">Completion probability and cross-sell readiness scored daily per learner</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:var(--emerald)"></div><div class="lu-num">L6</div><div class="lu-name">Strategy Intelligence</div></div><div class="lu-desc">Personalised learning paths, nudge timing, and cross-sell offer sequencing</div></div>
      <div class="lu-item"><div class="lu-top"><div class="lu-dot" style="background:#fb923c"></div><div class="lu-num">L7</div><div class="lu-name">Autonomous Execution</div></div><div class="lu-desc">Automated personalised nudges, milestone emails, and cross-sell triggers</div></div>
    </div>
  </div>
  <div class="quote-block" style="border-color:var(--emerald)">
    <div class="qb-text">&ldquo;The '40/7 rule' that X Platforms discovered changed our entire onboarding strategy. We redesigned the first seven days of every course around it. Completion rates jumped before we even touched the intervention engine.&rdquo;</div>
    <div class="qb-author"><div class="qb-avatar" style="background:linear-gradient(135deg,var(--emerald),var(--cyan))">LP</div><div><div class="qb-name">Lena Park</div><div class="qb-role">Head of Product, OneAustralia</div></div></div>
  </div>
  <div class="cs-section rv">
    <div class="cs-section-label">Implementation Timeline</div>
    <h2>Full deployment across a 420,000-user platform</h2>
    <div class="timeline">
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot" style="border-color:var(--emerald);color:var(--emerald)">W1</div></div><div class="tl-content"><div class="tl-month" style="color:var(--emerald)">Week 1&ndash;3</div><div class="tl-title">Data Integration at Scale</div><div class="tl-text">LMS, payment platform, email, and mobile app connected. 18-month historical learning data processed for all 420,000 registered users.</div><div class="tl-badge" style="color:var(--emerald);background:rgba(52,211,153,.06);border-color:rgba(52,211,153,.2)">&#10003; 420,000 learner profiles unified</div></div></div>
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot" style="border-color:var(--emerald);color:var(--emerald)">W4</div></div><div class="tl-content"><div class="tl-month" style="color:var(--emerald)">Week 4&ndash;6</div><div class="tl-title">Pattern Discovery</div><div class="tl-text">40/7 rule identified. Course-level completion variance explained. Top and bottom performing content ranked by behavioural signals.</div><div class="tl-badge" style="color:var(--emerald);background:rgba(52,211,153,.06);border-color:rgba(52,211,153,.2)">&#10003; Critical engagement threshold found</div></div></div>
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot" style="border-color:var(--emerald);color:var(--emerald)">W8</div></div><div class="tl-content"><div class="tl-month" style="color:var(--emerald)">Week 8&ndash;10</div><div class="tl-title">Intervention Engine Go-Live</div><div class="tl-text">Automated nudge system launched. Personalised emails and push notifications triggered based on engagement risk.</div><div class="tl-badge" style="color:var(--emerald);background:rgba(52,211,153,.06);border-color:rgba(52,211,153,.2)">&#10003; Completion rate +18% in first 2 weeks</div></div></div>
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot" style="border-color:var(--emerald);color:var(--emerald)">M4</div></div><div class="tl-content"><div class="tl-month" style="color:var(--emerald)">Month 4</div><div class="tl-title">Cross-Sell Engine Activated</div><div class="tl-text">Dynamic second-course recommendation system deployed. Cross-sell rate climbed from 8% to 31% in 60 days.</div><div class="tl-badge" style="color:var(--emerald);background:rgba(52,211,153,.06);border-color:rgba(52,211,153,.2)">&#10003; ROI positive at day 91</div></div></div>
      <div class="tl-item"><div class="tl-marker"><div class="tl-dot" style="border-color:var(--emerald);color:var(--emerald)">M12</div></div><div class="tl-content"><div class="tl-month" style="color:var(--emerald)">Month 12</div><div class="tl-title">Full Platform Intelligence</div><div class="tl-text">All 8 layers active. 74% average completion rate. Revenue per student more than doubled. Instructor earnings up 84%.</div><div class="tl-badge" style="color:var(--emerald);background:rgba(52,211,153,.06);border-color:rgba(52,211,153,.2)">&#10003; $5.4M additional annual revenue</div></div></div>
    </div>
  </div>
  <div class="cs-section rv">
    <div class="cs-section-label">Results Breakdown</div>
    <h2>What the numbers look like at 12 months</h2>
    <div class="results-grid">
      <div class="rg-item"><div class="rg-n" style="color:var(--emerald)">3&times;</div><div class="rg-l">Course completion rate (24% &rarr; 74%)</div><div class="rg-bar"><div class="rg-fill" style="background:var(--emerald);width:74%"></div></div></div>
      <div class="rg-item"><div class="rg-n" style="color:var(--cyan)">2.2&times;</div><div class="rg-l">Revenue per student ($142 &rarr; $312)</div><div class="rg-bar"><div class="rg-fill" style="background:var(--cyan);width:80%"></div></div></div>
      <div class="rg-item"><div class="rg-n" style="color:var(--blue)">31%</div><div class="rg-l">Cross-sell rate (up from 8%)</div><div class="rg-bar"><div class="rg-fill" style="background:var(--blue);width:62%"></div></div></div>
      <div class="rg-item"><div class="rg-n" style="color:var(--violet)">84%</div><div class="rg-l">Instructor revenue increase</div><div class="rg-bar"><div class="rg-fill" style="background:var(--violet);width:84%"></div></div></div>
      <div class="rg-item"><div class="rg-n" style="color:var(--amber)">4.8&times;</div><div class="rg-l">Review volume (more completions)</div><div class="rg-bar"><div class="rg-fill" style="background:var(--amber);width:70%"></div></div></div>
      <div class="rg-item"><div class="rg-n" style="color:var(--rose)">$5.4M</div><div class="rg-l">Year 1 additional revenue</div><div class="rg-bar"><div class="rg-fill" style="background:var(--rose);width:88%"></div></div></div>
    </div>
    <table class="data-table" style="margin-top:28px">
      <thead><tr><th>Metric</th><th>Before</th><th>After (12M)</th><th>Change</th></tr></thead>
      <tbody>
        <tr><td>Avg Course Completion Rate</td><td>24%</td><td class="hi">74%</td><td class="pos">&uarr; 208%</td></tr>
        <tr><td>Revenue per Student</td><td>$142</td><td class="hi">$312</td><td class="pos">&uarr; 120%</td></tr>
        <tr><td>Cross-Sell Rate</td><td>8%</td><td class="hi">31%</td><td class="pos">&uarr; 288%</td></tr>
        <tr><td>Instructor Average Earnings</td><td>$3,200/mo</td><td class="hi">$5,888/mo</td><td class="pos">&uarr; 84%</td></tr>
        <tr><td>Student Review Volume</td><td>2,400/mo</td><td class="hi">11,520/mo</td><td class="pos">&uarr; 380%</td></tr>
        <tr><td>Platform NPS</td><td>38</td><td class="hi">71</td><td class="pos">&uarr; 87%</td></tr>
        <tr><td>7-Day Activation Rate</td><td>21%</td><td class="hi">68%</td><td class="pos">&uarr; 224%</td></tr>
      </tbody>
    </table>
  </div>
  <div class="quote-block" style="border-color:var(--cyan)">
    <div class="qb-text">&ldquo;Our instructors were almost ready to leave the platform because their earnings weren't matching their expectations. Twelve months after deploying X Platforms, their average monthly revenue increased 84%. That's what I'm most proud of.&rdquo;</div>
    <div class="qb-author"><div class="qb-avatar" style="background:linear-gradient(135deg,var(--cyan),var(--emerald))">DW</div><div><div class="qb-name">David Wong</div><div class="qb-role">CEO, OneAustralia</div></div></div>
  </div>
</div>
<div class="cs-sidebar">
  <div class="cs-sidebar-card"><div class="sc-title">Company Overview</div>
    <div class="sc-row"><span>Industry</span><span>eLearning Marketplace</span></div>
    <div class="sc-row"><span>Founded</span><span>2017</span></div>
    <div class="sc-row"><span>Headquarters</span><span>Brisbane, Australia</span></div>
    <div class="sc-row"><span>Registered Learners</span><span>420,000+</span></div>
    <div class="sc-row"><span>Courses Listed</span><span>3,200+</span></div>
    <div class="sc-row"><span>Active Instructors</span><span>840</span></div>
  </div>
  <div class="cs-sidebar-card"><div class="sc-title">Data Sources Connected</div>
    <div class="sc-row"><span>LMS Platform</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Stripe Payments</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Mobile App (iOS + Android)</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Klaviyo Email</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Intercom Chat</span><span style="color:var(--emerald)">&#10003; Live</span></div>
    <div class="sc-row"><span>Social Reviews</span><span style="color:var(--amber)">Monitoring</span></div>
  </div>
  <div class="cs-sidebar-card" style="background:rgba(52,211,153,.04);border-color:rgba(52,211,153,.15)">
    <div class="sc-title">Similar to OneAustralia?</div>
    <p style="font-size:13px;color:var(--g200);line-height:1.6;margin-bottom:14px">Running a course marketplace or eLearning platform? Let's find your completion rate opportunity.</p>
    <a href="{{ route('client.register') }}" class="btn-b btn-fill" style="font-size:13px;padding:10px 20px;width:100%;justify-content:center">Book an eLearning Demo</a>
  </div>
</div>
</div></div>
</div>

<!-- MORE CASE STUDIES -->
<section class="more-cs" id="more">
  <div class="more-cs-in">
    <div class="rv"><div class="stag">More Results</div><h2 class="sh">Results across every industry</h2><p style="font-size:15px;color:var(--g300);max-width:500px;line-height:1.7">These three represent a fraction of the outcomes being achieved across X Platforms' 15 supported industries.</p></div>
    <div class="more-grid rv">
      <div class="more-card"><div class="mc-logo" style="background:linear-gradient(135deg,var(--blue),var(--cyan))">H</div><h3>Herald Bank</h3><div class="mc-tag">Banking &amp; Finance</div><p>Identified $84K/year at-risk accounts before they churned and deployed relationship manager interventions with a 72% save rate.</p><div class="mc-stat"><strong>&darr; 31% churn</strong> &middot; <strong>&uarr; $4.1M ARR</strong> &middot; <strong>94% accuracy</strong></div></div>
      <div class="more-card"><div class="mc-logo" style="background:linear-gradient(135deg,var(--violet),var(--rose))">P</div><h3>Prism Health</h3><div class="mc-tag">Healthcare</div><p>Improved patient follow-up compliance from 62% to 79%, reducing care gaps across 3 clinic networks serving 28,000 patients.</p><div class="mc-stat"><strong>&uarr; 28% compliance</strong> &middot; <strong>&darr; 18% readmissions</strong> &middot; <strong>2.1&times; patient LTV</strong></div></div>
      <div class="more-card"><div class="mc-logo" style="background:linear-gradient(135deg,var(--amber),var(--rose))">V</div><h3>Vantage Telecom</h3><div class="mc-tag">Telecom</div><p>Predicted subscriber churn 30 days in advance with 84% accuracy, saving $1.2M/year in previously-lost recurring revenue.</p><div class="mc-stat"><strong>&darr; 34% churn</strong> &middot; <strong>&uarr; $1.2M saved</strong> &middot; <strong>72% save rate</strong></div></div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-bar">
  <h2 class="rv">Ready to write your <span>case study?</span></h2>
  <p class="rv">Every business on this page started with one conversation. See what X Platforms can find in your data.</p>
  <div class="rv" style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
    <a href="{{ route('client.register') }}" class="btn-b btn-fill">Book a Free Discovery Call</a>
    <a href="{{ url('/') }}#journey" class="btn-b btn-g">Try the Simulator First</a>
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
for(let i=0;i<Math.min(60,Math.floor(W*H/20000));i++)nd.push({x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.28,vy:(Math.random()-.5)*.28,r:Math.random()*1.4+.6,p:Math.random()*6.28});
let mmx=-1e3,mmy=-1e3;document.addEventListener('mousemove',e=>{mmx=e.clientX;mmy=e.clientY});
(function draw(){cx.clearRect(0,0,W,H);for(let i=0;i<nd.length;i++){const n=nd[i];n.x+=n.vx;n.y+=n.vy;n.p+=.01;
if(n.x<0||n.x>W)n.vx*=-1;if(n.y<0||n.y>H)n.vy*=-1;
for(let j=i+1;j<nd.length;j++){const m=nd[j],d=Math.hypot(n.x-m.x,n.y-m.y);if(d<150){cx.beginPath();cx.moveTo(n.x,n.y);cx.lineTo(m.x,m.y);cx.strokeStyle='rgba(79,143,255,'+(1-d/150)*.09+')';cx.lineWidth=.5;cx.stroke()}}
const g=Math.hypot(n.x-mmx,n.y-mmy)<160?(1-Math.hypot(n.x-mmx,n.y-mmy)/160)*.45:0;
cx.beginPath();cx.arc(n.x,n.y,n.r+Math.sin(n.p)*.35,0,6.28);cx.fillStyle='rgba(79,143,255,'+(0.22+g)+')';cx.fill()}
requestAnimationFrame(draw)})();

function openCase(id){
  document.querySelector('.cs-index').style.display='none';
  document.querySelectorAll('.cs-full').forEach(el=>el.classList.remove('active'));
  document.getElementById('case-'+id).classList.add('active');
  document.getElementById('top').style.display='none';
  document.querySelector('.hero').style.display='none';
  window.scrollTo({top:0,behavior:'smooth'});
}
function closeCase(){
  document.querySelectorAll('.cs-full').forEach(el=>el.classList.remove('active'));
  document.querySelector('.cs-index').style.display='flex';
  document.getElementById('top').style.display='block';
  document.querySelector('.hero').style.display='block';
  window.scrollTo({top:0,behavior:'smooth'});
}

const obs=new IntersectionObserver(e=>{e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('vis');obs.unobserve(x.target)}})},{threshold:.1,rootMargin:'0px 0px -40px 0px'});
document.querySelectorAll('.rv').forEach(el=>obs.observe(el));
addEventListener('scroll',()=>{document.querySelector('.nav-bg').style.background=scrollY>40?'rgba(5,7,14,.9)':'rgba(5,7,14,.75)'});
</script>
</body>
</html>
