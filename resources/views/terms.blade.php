<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Terms of Service &ndash; X Platforms</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="description" content="X Platforms Terms of Service. The terms governing your use of the X Platforms AI intelligence platform. Last updated May 2026.">
<meta name="robots" content="index, follow"><link rel="canonical" href="{{ url('/terms') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;--blue:#4f8fff;--blue2:#2563eb;--blue-g:rgba(79,143,255,.08);--cyan:#38bdf8;--violet:#818cf8;--emerald:#34d399;--amber:#fbbf24;--rose:#f472b6;--white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;--brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.15);--f1:'Outfit',system-ui,sans-serif;--f2:'Source Serif 4',Georgia,serif;--fm:'IBM Plex Mono',monospace;--ease:cubic-bezier(.16,1,.3,1);--mw:1200px}
html{scroll-behavior:smooth}body{background:var(--bg);color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;overflow-x:hidden}a{color:inherit;text-decoration:none}canvas#neural{position:fixed;inset:0;z-index:0;pointer-events:none}
.nav{position:fixed;top:0;width:100%;z-index:100;border-bottom:1px solid var(--brd)}.nav-bg{position:absolute;inset:0;background:rgba(5,7,14,.75);backdrop-filter:blur(40px)}.nav-in{position:relative;max-width:var(--mw);margin:0 auto;padding:0 40px;height:64px;display:flex;align-items:center;justify-content:space-between}.logo{display:flex;align-items:center;gap:11px;font-weight:700;font-size:15px;letter-spacing:-.3px}.logo-m{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--blue),var(--violet));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff;box-shadow:0 0 20px rgba(79,143,255,.25)}.nav-l{display:flex;align-items:center;gap:32px;list-style:none}.nav-l a{font-size:13.5px;font-weight:450;color:var(--g200);transition:color .25s}.nav-l a:hover{color:var(--white)}.nav-cta{padding:8px 22px;background:var(--blue);color:#fff!important;border-radius:8px;font-weight:600!important;font-size:13px!important;box-shadow:0 0 24px rgba(79,143,255,.25);transition:all .25s;cursor:pointer;border:none;font-family:var(--f1)}.nav-cta:hover{box-shadow:0 0 36px rgba(79,143,255,.4)}.nav-login{display:inline-flex;align-items:center;gap:7px;padding:8px 18px;border:1px solid var(--g500);color:var(--g200)!important;border-radius:8px;font-weight:500!important;font-size:13px!important;transition:all .25s;background:transparent;cursor:pointer;font-family:var(--f1)}.nav-login:hover{border-color:var(--brd2);color:var(--white)!important;background:rgba(79,143,255,.05)}
.page{position:relative;z-index:1;padding:120px 40px 80px;max-width:var(--mw);margin:0 auto}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:24px;letter-spacing:.5px}.breadcrumb a{color:var(--g300)}.breadcrumb a:hover{color:var(--blue)}
h1{font-weight:800;font-size:clamp(32px,4vw,56px);line-height:1.08;letter-spacing:-2px;margin-bottom:16px}
h1 span,h2 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
h2{font-weight:700;font-size:clamp(22px,2.6vw,32px);letter-spacing:-1px;margin-bottom:12px;line-height:1.2}
p{font-size:15px;color:var(--g200);line-height:1.75;margin-bottom:14px;font-weight:350}p:last-child{margin-bottom:0}
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2);position:relative;z-index:1}.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:260px;margin-top:14px}.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}.foot-c a:hover{color:var(--white)}.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}
@media(max-width:1024px){.foot-in{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.nav-l{display:none}.nav-in{padding:0 20px}.page{padding-left:20px;padding-right:20px;padding-top:100px}.foot-in{grid-template-columns:1fr}.foot-b{flex-direction:column;gap:8px}}
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
.legal-layout{display:grid;grid-template-columns:220px 1fr;gap:48px;align-items:start}
.legal-nav{position:sticky;top:88px;background:var(--card);border:1px solid var(--brd);border-radius:12px;padding:20px}
.legal-nav h5{font-family:var(--fm);font-size:9px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g500);margin-bottom:14px;font-weight:500}
.legal-nav a{display:block;font-size:13px;color:var(--g300);padding:6px 0;border-bottom:1px solid var(--brd);transition:color .2s}
.legal-nav a:last-child{border-bottom:none}
.legal-nav a:hover{color:var(--blue)}
.legal-body section{margin-bottom:48px}
.legal-body section h2{font-size:20px;font-weight:700;letter-spacing:-.3px;margin-bottom:14px;padding-top:16px}
.legal-body p,.legal-body li{font-size:14.5px;color:var(--g200);line-height:1.8;margin-bottom:12px}
.legal-body ul{padding-left:20px;margin-bottom:16px}
.legal-body li{margin-bottom:6px}
.legal-meta{background:var(--card);border:1px solid var(--brd);border-radius:10px;padding:16px 20px;margin-bottom:40px;font-family:var(--fm);font-size:11px;color:var(--g400);display:flex;gap:32px;flex-wrap:wrap}
.lm-item strong{color:var(--g200);display:block;margin-bottom:2px}
@media(max-width:1024px){.legal-layout{grid-template-columns:1fr}.legal-nav{position:static}}
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
  <nav class="breadcrumb"><a href="{{ route('home') }}">Home</a> / <strong>Terms of Service</strong></nav>
  <h1 style="font-size:clamp(28px,3.5vw,44px)">Terms of <span>Service</span></h1>
  <div class="legal-meta"><div class="lm-item"><strong>Last Updated</strong>15 May 2026</div><div class="lm-item"><strong>Version</strong>2.4</div><div class="lm-item"><strong>Effective Date</strong>15 May 2026</div><div class="lm-item"><strong>Governing Law</strong>Victoria, Australia</div></div>
  <div class="legal-layout">
    <div class="legal-nav">
      <h5>Contents</h5>
      <a href="#acceptance">1. Acceptance</a>
      <a href="#services">2. The Services</a>
      <a href="#accounts">3. Accounts</a>
      <a href="#payment">4. Payment</a>
      <a href="#data">5. Your Data</a>
      <a href="#ip">6. Intellectual Property</a>
      <a href="#prohibited">7. Prohibited Use</a>
      <a href="#warranty">8. Warranty</a>
      <a href="#liability">9. Liability</a>
      <a href="#termination">10. Termination</a>
      <a href="#governing">11. Governing Law</a>
      <a href="#contact">12. Contact</a>
    </div>
    <div class="legal-body">
      <section id="acceptance"><h2>1. Acceptance of Terms</h2><p>By accessing or using the X Platforms platform and associated services (&ldquo;Services&rdquo;), you agree to be bound by these Terms of Service (&ldquo;Terms&rdquo;). If you are entering into these Terms on behalf of an organisation, you represent that you have authority to bind that organisation. If you do not agree to these Terms, do not use the Services.</p></section>
      <section id="services"><h2>2. The Services</h2><p>X Platforms provides an AI-powered customer intelligence platform that ingests, unifies, and analyses customer data to generate predictions and recommendations (the &ldquo;Services&rdquo;). The specific features available to you depend on your subscription plan.</p><p>We reserve the right to modify, suspend, or discontinue any aspect of the Services with reasonable notice. Material changes will be communicated at least 30 days in advance.</p></section>
      <section id="accounts"><h2>3. Accounts and Access</h2><p>You are responsible for maintaining the security of your account credentials. You must notify us immediately of any unauthorised access or breach. You are responsible for all activity that occurs under your account.</p><ul><li>You must provide accurate and current account information</li><li>You may not share credentials between users without authorisation from X Platforms</li><li>You must be at least 18 years old to use the Services</li><li>Each subscription covers the number of users specified in your plan</li></ul></section>
      <section id="payment"><h2>4. Payment and Billing</h2><p>Subscription fees are billed in advance on a monthly or annual basis, as selected during sign-up. All fees are exclusive of applicable taxes.</p><ul><li>Annual subscriptions are non-refundable after 14 days from the start of the subscription period</li><li>Monthly subscriptions may be cancelled at any time, effective at the end of the current billing period</li><li>We reserve the right to modify pricing with 60 days advance notice to existing subscribers</li><li>Overdue payments may result in suspension of the Services after 14 days</li></ul></section>
      <section id="data"><h2>5. Your Data</h2><p>You retain all rights to the data you submit to X Platforms (&ldquo;Customer Data&rdquo;). By using the Services, you grant X Platforms a limited licence to process Customer Data solely to provide the Services.</p><p>X Platforms will process Customer Data in accordance with our Data Processing Agreement (DPA), our Privacy Policy, and applicable data protection law. We will not use Customer Data for any purpose outside the scope of providing the Services, including training models for other customers or selling data to third parties.</p></section>
      <section id="ip"><h2>6. Intellectual Property</h2><p>X Platforms retains all rights, title, and interest in the Services, including all AI models, software, documentation, and associated intellectual property. Nothing in these Terms grants you any right to the X Platforms platform itself beyond the licence to use the Services during your subscription.</p><p>Insights, predictions, and reports generated by the Services from your Customer Data are owned by you.</p></section>
      <section id="prohibited"><h2>7. Prohibited Use</h2><p>You may not use the Services to:</p><ul><li>Violate any applicable law or regulation</li><li>Process personal data without appropriate legal basis or in violation of applicable privacy law</li><li>Attempt to reverse-engineer, decompile, or extract the underlying models or algorithms</li><li>Use the Services for purposes competitive with X Platforms without prior written consent</li><li>Transmit malware, viruses, or other malicious code</li><li>Attempt to gain unauthorised access to any X Platforms system or another customer&rsquo;s data</li><li>Facilitate discrimination, harassment, or unlawful activities</li></ul></section>
      <section id="warranty"><h2>8. Warranty Disclaimer</h2><p>THE SERVICES ARE PROVIDED &ldquo;AS IS&rdquo; WITHOUT WARRANTY OF ANY KIND. TO THE MAXIMUM EXTENT PERMITTED BY LAW, X PLATFORMS DISCLAIMS ALL WARRANTIES, EXPRESS OR IMPLIED, INCLUDING WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.</p><p>X Platforms does not warrant that the Services will be error-free, uninterrupted, or that any particular result or accuracy level will be achieved. Prediction accuracy may vary by customer, industry, and data quality.</p></section>
      <section id="liability"><h2>9. Limitation of Liability</h2><p>TO THE MAXIMUM EXTENT PERMITTED BY LAW, X PLATFORMS&rsquo; TOTAL LIABILITY TO YOU FOR ANY CLAIMS ARISING FROM THESE TERMS OR THE SERVICES SHALL NOT EXCEED THE AMOUNT YOU PAID TO X PLATFORMS IN THE 12 MONTHS PRECEDING THE CLAIM.</p><p>IN NO EVENT SHALL X PLATFORMS BE LIABLE FOR INDIRECT, INCIDENTAL, CONSEQUENTIAL, SPECIAL, OR PUNITIVE DAMAGES, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.</p></section>
      <section id="termination"><h2>10. Termination</h2><p>Either party may terminate the subscription at any time. Upon termination:</p><ul><li>Your access to the Services will cease at the end of the current billing period</li><li>X Platforms will make Customer Data available for export for 30 days following termination</li><li>After 30 days, Customer Data will be deleted from X Platforms systems</li><li>Provisions that by their nature survive termination (including IP, liability, and governing law) will continue to apply</li></ul></section>
      <section id="governing"><h2>11. Governing Law</h2><p>These Terms are governed by the laws of Victoria, Australia. Any disputes will be resolved in the courts of Victoria, Australia. If you are an Enterprise customer, alternative dispute resolution provisions may be specified in your Master Services Agreement.</p></section>
      <section id="contact"><h2>12. Contact</h2><p>For questions about these Terms: <a href="mailto:legal@xplatforms.ai" style="color:var(--blue)">legal@xplatforms.ai</a><br>X Platforms Pty Ltd, Level 12, 1 Collins Street, Melbourne VIC 3000, Australia.</p></section>
    </div>
  </div>
</section>
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
