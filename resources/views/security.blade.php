<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Security &amp; Compliance &ndash; X Platforms | SOC 2, GDPR, HIPAA, ISO 27001</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="description" content="X Platforms is SOC 2 Type II certified, GDPR compliant, ISO 27001 certified, and HIPAA compliant. AES-256 encryption at rest and in transit. Full enterprise security documentation available.">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ url('/security') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;--blue:#4f8fff;--blue2:#2563eb;--blue-g:rgba(79,143,255,.08);--cyan:#38bdf8;--violet:#818cf8;--emerald:#34d399;--amber:#fbbf24;--rose:#f472b6;--white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;--brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.15);--f1:'Outfit',system-ui,sans-serif;--f2:'Source Serif 4',Georgia,serif;--fm:'IBM Plex Mono',monospace;--ease:cubic-bezier(.16,1,.3,1);--mw:1200px}
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
.page{position:relative;z-index:1;padding:120px 40px 80px;max-width:var(--mw);margin:0 auto}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:24px;letter-spacing:.5px}
.breadcrumb a{color:var(--g300)}
.breadcrumb a:hover{color:var(--blue)}
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:12px;display:flex;align-items:center;gap:10px}
.stag::before{content:'';width:16px;height:1px;background:var(--blue)}
.sec{position:relative;z-index:1;padding:72px 40px;max-width:var(--mw);margin:0 auto}
.sec-f{position:relative;z-index:1;padding:72px 40px;border-top:1px solid var(--brd);background:var(--bg2)}
.sec-f .sw{max-width:var(--mw);margin:0 auto}
h1{font-weight:800;font-size:clamp(32px,4vw,56px);line-height:1.08;letter-spacing:-2px;margin-bottom:16px}
h1 span,h2 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
h2{font-weight:700;font-size:clamp(22px,2.6vw,32px);letter-spacing:-1px;margin-bottom:12px;line-height:1.2}
h3{font-weight:600;font-size:16px;margin-bottom:8px;letter-spacing:-.1px}
p{font-size:15px;color:var(--g200);line-height:1.75;margin-bottom:14px;font-weight:350}
p:last-child{margin-bottom:0}
.hero-p{font-size:17px;max-width:560px;color:var(--g200);line-height:1.75;margin-bottom:40px;font-weight:350}
.btn-fill{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:11px;font-weight:600;font-size:14px;border:none;cursor:pointer;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 24px rgba(79,143,255,.25);font-family:var(--f1);transition:all .25s}
.btn-fill:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(79,143,255,.35)}
.btn-g{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:11px;font-weight:500;font-size:14px;background:rgba(255,255,255,.04);color:var(--g100);border:1px solid var(--g500);cursor:pointer;font-family:var(--f1);transition:all .25s}
.btn-g:hover{border-color:var(--blue);background:var(--blue-g)}
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2);position:relative;z-index:1}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:260px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}
.cert-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:48px}
.cert-card{background:var(--card);border:1px solid var(--brd);border-radius:16px;padding:32px;text-align:center;transition:all .3s}
.cert-card:hover{border-color:var(--brd2);background:var(--card-h)}
.cert-icon{width:56px;height:56px;border-radius:14px;background:var(--blue-g);border:1px solid var(--brd2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
.cert-icon svg{width:24px;height:24px;stroke:var(--blue);fill:none;stroke-width:1.5}
.cert-name{font-weight:700;font-size:17px;margin-bottom:4px}
.cert-status{font-family:var(--fm);font-size:10px;color:var(--emerald);background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.15);padding:3px 10px;border-radius:5px;display:inline-block;margin-bottom:12px;letter-spacing:.5px}
.cert-desc{font-size:13px;color:var(--g300);line-height:1.6}
.feature-row{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:12px}
.feat-item{display:flex;align-items:flex-start;gap:14px;padding:20px;background:var(--card);border:1px solid var(--brd);border-radius:12px;transition:all .3s}
.feat-item:hover{border-color:var(--brd2);background:var(--card-h)}
.fi-icon{width:36px;height:36px;border-radius:9px;background:var(--blue-g);border:1px solid var(--brd2);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.fi-icon svg{width:16px;height:16px;stroke:var(--blue);fill:none;stroke-width:1.8}
.fi-title{font-size:14px;font-weight:600;margin-bottom:4px}
.fi-desc{font-size:13px;color:var(--g300);line-height:1.55}
.pen-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:24px}
.pen-item{background:var(--card);border:1px solid var(--brd);border-radius:12px;padding:18px;text-align:center}
.pen-n{font-weight:700;font-size:22px;letter-spacing:-.5px;background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:4px}
.pen-l{font-family:var(--fm);font-size:9px;color:var(--g400);letter-spacing:.8px;text-transform:uppercase}
@media(max-width:1024px){.cert-grid,.feature-row{grid-template-columns:1fr 1fr}.pen-grid{grid-template-columns:1fr 1fr}.foot-in{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.nav-l{display:none}.nav-in{padding:0 20px}.page,.sec,.sec-f{padding-left:20px;padding-right:20px}.page{padding-top:100px}.cert-grid,.feature-row,.pen-grid{grid-template-columns:1fr}.foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}}
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
    <!-- <a class="nav-mob-link" href="{{ route('client.login') }}">Log in</a> -->
    <a class="nav-mob-cta" href="{{ route('book-demo') }}">Book a Demo</a>
  </div>
</div>
<script>function toggleNav(){var m=document.getElementById('navMob'),h=document.getElementById('navHam');m.classList.toggle('open');h.classList.toggle('open');document.body.style.overflow=m.classList.contains('open')?'hidden':''}</script>
<section class="page">
    <nav class="breadcrumb"><a href="{{ route('home') }}">Home</a> / <strong>Security &amp; Compliance</strong></nav>
    <div class="rv">
        <div class="stag">Security</div>
        <h1>Enterprise-grade security <span>at every layer</span></h1>
        <p class="hero-p">X Platforms is built for organisations that handle sensitive customer data. Every layer of our architecture &mdash; from data ingestion to model execution to output delivery &mdash; is engineered to the highest security standards.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <!-- <a href="{{ route('client.login') }}" class="btn-fill">Request Security Documentation</a> -->
            <a href="#certifications" class="btn-g">View Certifications</a>
        </div>
    </div>
</section>
<div style="position:relative;z-index:1;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);background:var(--bg2);padding:28px 40px">
    <div style="max-width:var(--mw);margin:0 auto;display:flex;align-items:center;justify-content:center;gap:40px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--g300)"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--emerald)" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg><span style="color:var(--emerald);font-weight:600">SOC 2 Type II</span> Certified</div>
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--g300)"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--emerald)" stroke-width="1.8"><path d="M9 11l3 3L22 4"/></svg><span style="color:var(--emerald);font-weight:600">ISO 27001</span> Certified</div>
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--g300)"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--emerald)" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/></svg><span style="color:var(--emerald);font-weight:600">GDPR</span> Compliant</div>
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--g300)"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--emerald)" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg><span style="color:var(--emerald);font-weight:600">HIPAA</span> Compliant</div>
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--g300)"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--emerald)" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg><span style="color:var(--emerald);font-weight:600">AES-256</span> Encrypted</div>
    </div>
</div>
<section class="sec" id="certifications">
    <div class="rv" style="margin-bottom:0">
        <div class="stag">Certifications</div>
        <h2>Industry certifications &amp; compliance</h2>
        <p style="font-size:15px;color:var(--g300);max-width:500px;line-height:1.7">Independently audited and certified annually. Documentation available for enterprise security reviews.</p>
    </div>
    <div class="cert-grid rv">
        <div class="cert-card"><div class="cert-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><div class="cert-name">SOC 2 Type II</div><div class="cert-status">&#10003; CURRENT</div><div class="cert-desc">Independently audited annually by a Big 4 accounting firm. Covers security, availability, processing integrity, confidentiality, and privacy trust service criteria.</div></div>
        <div class="cert-card"><div class="cert-icon"><svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div><div class="cert-name">ISO 27001</div><div class="cert-status">&#10003; CURRENT</div><div class="cert-desc">Certified Information Security Management System (ISMS). Covers all aspects of information security including risk assessment, incident management, and business continuity.</div></div>
        <div class="cert-card"><div class="cert-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 014 10 15 15 0 01-4 10"/></svg></div><div class="cert-name">GDPR Compliant</div><div class="cert-status">&#10003; CURRENT</div><div class="cert-desc">Full EU General Data Protection Regulation compliance. Data Processing Agreements (DPAs) provided as standard. EU data residency options available for Enterprise customers.</div></div>
        <div class="cert-card"><div class="cert-icon"><svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div><div class="cert-name">HIPAA Compliant</div><div class="cert-status">&#10003; CURRENT</div><div class="cert-desc">Full HIPAA compliance for healthcare deployments. Business Associate Agreements (BAAs) provided as standard. PHI is processed only within HIPAA-compliant infrastructure with full audit logging.</div></div>
        <div class="cert-card"><div class="cert-icon"><svg viewBox="0 0 24 24"><path d="M3 21h18M3 10h18M5 6l7-3 7 3"/></svg></div><div class="cert-name">APRA CPS 234</div><div class="cert-status">&#10003; ALIGNED</div><div class="cert-desc">Aligned with APRA CPS 234 information security requirements for Australian financial institutions. Supporting documentation available for APRA-regulated customers.</div></div>
        <div class="cert-card"><div class="cert-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><div class="cert-name">Privacy Act 1988</div><div class="cert-status">&#10003; COMPLIANT</div><div class="cert-desc">Full compliance with the Australian Privacy Act 1988 and Australian Privacy Principles (APPs). Privacy impact assessments available for enterprise deployments.</div></div>
    </div>
</section>
<section class="sec-f">
    <div class="sw rv">
        <div class="stag">Data Security</div>
        <h2 style="margin-bottom:16px">How we protect your customer data</h2>
        <div class="feature-row">
            <div class="feat-item"><div class="fi-icon"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></div><div><div class="fi-title">AES-256 Encryption at Rest</div><div class="fi-desc">All customer data stored in X Platforms is encrypted using AES-256 &mdash; the same standard used by financial institutions and government agencies worldwide.</div></div></div>
            <div class="feat-item"><div class="fi-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><div><div class="fi-title">TLS 1.3 In Transit</div><div class="fi-desc">All data transmitted between your systems and X Platforms is encrypted using TLS 1.3. Certificate pinning is enforced for all API connections.</div></div></div>
            <div class="feat-item"><div class="fi-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><div><div class="fi-title">Role-Based Access Control</div><div class="fi-desc">Granular RBAC across all X Platforms features. Every team member sees only the data and functionality their role requires. Full audit logs of all access events.</div></div></div>
            <div class="feat-item"><div class="fi-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg></div><div><div class="fi-title">Zero Data Sharing</div><div class="fi-desc">Your customer data is never shared with other X Platforms customers, used for training models for other organisations, or sold or licensed to any third party. Ever.</div></div></div>
            <div class="feat-item"><div class="fi-icon"><svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div><div><div class="fi-title">Dedicated Infrastructure (Enterprise)</div><div class="fi-desc">Enterprise customers receive dedicated compute and storage infrastructure &mdash; your data never sits on shared servers. Private cloud and on-premise deployment options available.</div></div></div>
            <div class="feat-item"><div class="fi-icon"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 16l4-6 4 3 5-7"/></svg></div><div><div class="fi-title">Continuous Security Monitoring</div><div class="fi-desc">24/7 automated threat detection, penetration testing quarterly, and a bug bounty programme. Security incidents are disclosed to affected customers within 72 hours.</div></div></div>
        </div>
    </div>
</section>
<section class="sec">
    <div class="rv">
        <div class="stag">SLA &amp; Uptime</div>
        <h2>Reliability you can depend on</h2>
    </div>
    <div class="pen-grid rv">
        <div class="pen-item"><div class="pen-n">99.99%</div><div class="pen-l">Uptime SLA (Enterprise)</div></div>
        <div class="pen-item"><div class="pen-n">99.95%</div><div class="pen-l">Uptime SLA (Growth)</div></div>
        <div class="pen-item"><div class="pen-n">72hrs</div><div class="pen-l">Max Breach Notification</div></div>
        <div class="pen-item"><div class="pen-n">Quarterly</div><div class="pen-l">Penetration Testing</div></div>
    </div>
</section>
<div style="position:relative;z-index:1;border-top:1px solid var(--brd);background:linear-gradient(135deg,rgba(79,143,255,.04),rgba(129,140,248,.02));padding:64px 40px;text-align:center">
    <div style="max-width:600px;margin:0 auto">
        <h2 class="rv" style="margin-bottom:12px">Need security documentation?</h2>
        <p class="rv" style="font-size:15px;color:var(--g300);margin-bottom:28px">We provide SOC 2 reports, penetration test summaries, Data Processing Agreements, and detailed security questionnaire responses for enterprise security reviews.</p>
        <div class="rv" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <!-- <a href="{{ route('client.login') }}" class="btn-fill">Request Documentation</a> -->
            <!-- <a href="{{ route('client.login') }}" class="btn-g">Contact Security Team</a> -->
        </div>
    </div>
</div>
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
