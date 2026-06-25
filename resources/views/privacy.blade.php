<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Privacy Policy &ndash; X Platforms</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="description" content="X Platforms Privacy Policy. How we collect, use, and protect your personal data. GDPR compliant. Last updated May 2026.">
<meta name="robots" content="index, follow"><link rel="canonical" href="{{ url('/privacy') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;--blue:#4f8fff;--blue2:#2563eb;--blue-g:rgba(79,143,255,.08);--cyan:#38bdf8;--violet:#818cf8;--emerald:#34d399;--amber:#fbbf24;--rose:#f472b6;--white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;--brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.15);--f1:'Outfit',system-ui,sans-serif;--f2:'Source Serif 4',Georgia,serif;--fm:'IBM Plex Mono',monospace;--ease:cubic-bezier(.16,1,.3,1);--mw:1200px}
html{scroll-behavior:smooth}body{background:var(--bg);color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;overflow-x:hidden}a{color:inherit;text-decoration:none}canvas#neural{position:fixed;inset:0;z-index:0;pointer-events:none}
.nav{position:fixed;top:0;width:100%;z-index:100;border-bottom:1px solid var(--brd)}.nav-bg{position:absolute;inset:0;background:rgba(5,7,14,.75);backdrop-filter:blur(40px)}.nav-in{position:relative;max-width:var(--mw);margin:0 auto;padding:0 40px;height:64px;display:flex;align-items:center;justify-content:space-between}.logo{display:flex;align-items:center;gap:11px;font-weight:700;font-size:15px;letter-spacing:-.3px}.logo-m{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--blue),var(--violet));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff;box-shadow:0 0 20px rgba(79,143,255,.25)}.nav-l{display:flex;align-items:center;gap:32px;list-style:none}.nav-l a{font-size:13.5px;font-weight:450;color:var(--g200);transition:color .25s}.nav-l a:hover{color:var(--white)}.nav-cta{padding:8px 22px;background:var(--blue);color:#fff!important;border-radius:8px;font-weight:600!important;font-size:13px!important;box-shadow:0 0 24px rgba(79,143,255,.25);transition:all .25s;cursor:pointer;border:none;font-family:var(--f1)}.nav-cta:hover{box-shadow:0 0 36px rgba(79,143,255,.4)}.nav-login{display:inline-flex;align-items:center;gap:7px;padding:8px 18px;border:1px solid var(--g500);color:var(--g200)!important;border-radius:8px;font-weight:500!important;font-size:13px!important;transition:all .25s;background:transparent;cursor:pointer;font-family:var(--f1)}.nav-login:hover{border-color:var(--brd2);color:var(--white)!important;background:rgba(79,143,255,.05)}
.page{position:relative;z-index:1;padding:120px 40px 80px;max-width:var(--mw);margin:0 auto}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:24px;letter-spacing:.5px}.breadcrumb a{color:var(--g300)}.breadcrumb a:hover{color:var(--blue)}
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:12px;display:flex;align-items:center;gap:10px}.stag::before{content:'';width:16px;height:1px;background:var(--blue)}
h1{font-weight:800;font-size:clamp(32px,4vw,56px);line-height:1.08;letter-spacing:-2px;margin-bottom:16px}
h1 span,h2 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
h2{font-weight:700;font-size:clamp(22px,2.6vw,32px);letter-spacing:-1px;margin-bottom:12px;line-height:1.2}
p{font-size:15px;color:var(--g200);line-height:1.75;margin-bottom:14px;font-weight:350}p:last-child{margin-bottom:0}
.btn-fill{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:11px;font-weight:600;font-size:14px;border:none;cursor:pointer;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 24px rgba(79,143,255,.25);font-family:var(--f1);transition:all .25s}.btn-fill:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(79,143,255,.35)}
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
.legal-nav a:last-child{border-bottom:none}.legal-nav a:hover{color:var(--blue)}
.legal-body section{margin-bottom:48px}
.legal-body section h2{font-size:20px;font-weight:700;letter-spacing:-.3px;margin-bottom:14px;padding-top:16px}
.legal-body p,.legal-body li{font-size:14.5px;color:var(--g200);line-height:1.8;margin-bottom:12px}
.legal-body ul{padding-left:20px;margin-bottom:16px}.legal-body li{margin-bottom:6px}
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
    <a class="nav-mob-link" href="{{ route('client.login') }}">Log in</a>
    <a class="nav-mob-cta" href="{{ route('book-demo') }}">Book a Demo</a>
  </div>
</div>
<script>function toggleNav(){var m=document.getElementById('navMob'),h=document.getElementById('navHam');m.classList.toggle('open');h.classList.toggle('open');document.body.style.overflow=m.classList.contains('open')?'hidden':''}</script>
<section class="page">
  <nav class="breadcrumb"><a href="{{ route('home') }}">Home</a> / <strong>Privacy Policy</strong></nav>
  <h1 style="font-size:clamp(28px,3.5vw,44px)">Privacy <span>Policy</span></h1>
  <div class="legal-meta"><div class="lm-item"><strong>Last Updated</strong>15 May 2026</div><div class="lm-item"><strong>Version</strong>3.2</div><div class="lm-item"><strong>Effective Date</strong>15 May 2026</div><div class="lm-item"><strong>Contact</strong>privacy@xplatforms.ai</div></div>
  <div class="legal-layout">
    <div class="legal-nav">
      <h5>Contents</h5>
      <a href="#who-we-are">1. Who We Are</a>
      <a href="#data-collect">2. Data We Collect</a>
      <a href="#how-we-use">3. How We Use Data</a>
      <a href="#data-sharing">4. Data Sharing</a>
      <a href="#data-retention">5. Data Retention</a>
      <a href="#your-rights">6. Your Rights</a>
      <a href="#security">7. Security</a>
      <a href="#cookies">8. Cookies</a>
      <a href="#international">9. International Transfers</a>
      <a href="#children">10. Children&rsquo;s Privacy</a>
      <a href="#changes">11. Policy Changes</a>
      <a href="#contact">12. Contact Us</a>
    </div>
    <div class="legal-body">
      <section id="who-we-are"><h2>1. Who We Are</h2><p>X Platforms Pty Ltd (ACN 654 321 987) (&ldquo;X Platforms&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo;, &ldquo;our&rdquo;) is an Australian company headquartered at Level 12, 1 Collins Street, Melbourne VIC 3000. We operate the X Platforms AI customer intelligence platform and associated services (the &ldquo;Services&rdquo;).</p><p>X Platforms is the data processor for our customers&rsquo; customer data and the data controller for data collected through our own website, marketing activities, and business operations.</p></section>
      <section id="data-collect"><h2>2. Data We Collect</h2><p><strong>Information you provide to us:</strong></p><ul><li>Contact information (name, email address, phone number, company name, job title)</li><li>Account credentials and preferences</li><li>Payment and billing information (processed by our payment processor &mdash; we do not store card details)</li><li>Communication records when you contact our team</li><li>Information submitted through forms, including demo requests and support tickets</li></ul><p><strong>Information collected automatically:</strong></p><ul><li>Usage data about how you interact with our website and platform</li><li>Device information (browser type, operating system, IP address)</li><li>Cookies and similar tracking technologies (see Section 8)</li><li>Log data including access times, pages viewed, and actions taken</li></ul><p><strong>Customer data we process on your behalf:</strong></p><p>When you use the X Platforms platform, you provide us with your customers&rsquo; data to process. This data is processed strictly as a data processor under your instructions, governed by our Data Processing Agreement (DPA). We never use your customers&rsquo; data for any purpose other than providing the Services to you.</p></section>
      <section id="how-we-use"><h2>3. How We Use Your Data</h2><ul><li>To provide, maintain, and improve the X Platforms Services</li><li>To process transactions and send billing-related communications</li><li>To send product updates, security alerts, and support messages</li><li>To respond to your enquiries and provide customer support</li><li>To send marketing communications where you have consented or where we have a legitimate interest</li><li>To analyse usage patterns to improve the platform (using aggregated, anonymised data)</li><li>To comply with legal obligations</li><li>To protect the security and integrity of our Services</li></ul></section>
      <section id="data-sharing"><h2>4. Data Sharing</h2><p>We do not sell, rent, or trade your personal information. We may share data with:</p><ul><li><strong>Service providers:</strong> Third-party vendors who assist us in providing the Services (cloud hosting, payment processing, email delivery), all bound by data processing agreements</li><li><strong>Business transfers:</strong> In connection with a merger, acquisition, or sale of assets</li><li><strong>Legal requirements:</strong> When required by law, court order, or to protect our legal rights</li><li><strong>With your consent:</strong> For any other purpose with your explicit consent</li></ul><p>Your customers&rsquo; data is never shared with other X Platforms customers or third parties outside the scope of providing the Services.</p></section>
      <section id="data-retention"><h2>5. Data Retention</h2><p>We retain personal data for as long as necessary to provide the Services and fulfil the purposes outlined in this policy, unless a longer retention period is required by law.</p><ul><li>Account data: retained for the duration of your subscription plus 90 days after termination</li><li>Customer data processed on your behalf: deleted or returned within 30 days of contract termination</li><li>Billing records: retained for 7 years as required by Australian tax law</li><li>Communication records: retained for 2 years</li></ul></section>
      <section id="your-rights"><h2>6. Your Rights</h2><p>Depending on your location, you may have the following rights regarding your personal data:</p><ul><li><strong>Access:</strong> Request a copy of the personal data we hold about you</li><li><strong>Rectification:</strong> Request correction of inaccurate data</li><li><strong>Erasure:</strong> Request deletion of your data in certain circumstances</li><li><strong>Portability:</strong> Request your data in a machine-readable format</li><li><strong>Restriction:</strong> Request restriction of processing in certain circumstances</li><li><strong>Objection:</strong> Object to processing based on legitimate interests</li><li><strong>Withdraw consent:</strong> Where processing is based on consent, withdraw it at any time</li></ul><p>To exercise any of these rights, email <a href="mailto:privacy@xplatforms.ai" style="color:var(--blue)">privacy@xplatforms.ai</a>. We will respond within 30 days.</p></section>
      <section id="security"><h2>7. Security</h2><p>We implement industry-standard security measures to protect your data, including AES-256 encryption at rest, TLS 1.3 in transit, role-based access controls, and continuous security monitoring. We hold SOC 2 Type II and ISO 27001 certifications. See our <a href="{{ route('security') }}" style="color:var(--blue)">Security page</a> for full details.</p></section>
      <section id="cookies"><h2>8. Cookies</h2><p>We use cookies and similar technologies to operate our website and improve your experience. Categories of cookies we use:</p><ul><li><strong>Essential:</strong> Required for the website to function. Cannot be disabled.</li><li><strong>Analytics:</strong> Help us understand how visitors interact with our site (Google Analytics, Mixpanel). Can be disabled.</li><li><strong>Marketing:</strong> Used to deliver relevant advertising (Google Ads, LinkedIn). Can be disabled.</li></ul><p>You can manage cookie preferences through our cookie consent banner or your browser settings.</p></section>
      <section id="international"><h2>9. International Data Transfers</h2><p>X Platforms is based in Australia. If you are located in the EU/EEA, the UK, or other jurisdictions with data transfer restrictions, your data may be transferred to and processed in Australia and other countries. We ensure such transfers comply with applicable law through Standard Contractual Clauses (SCCs) or other approved transfer mechanisms. Enterprise customers can request EU or Australian data residency.</p></section>
      <section id="children"><h2>10. Children&rsquo;s Privacy</h2><p>Our Services are not directed to individuals under 16 years of age. We do not knowingly collect personal information from children. If you believe we have inadvertently collected such information, please contact us immediately.</p></section>
      <section id="changes"><h2>11. Policy Changes</h2><p>We may update this Privacy Policy from time to time. We will notify you of material changes by email or through a prominent notice on our website at least 30 days before the changes take effect. Your continued use of the Services after the effective date constitutes acceptance of the updated policy.</p></section>
      <section id="contact"><h2>12. Contact Us</h2><p>For privacy-related questions or to exercise your rights:</p><ul><li><strong>Email:</strong> <a href="mailto:privacy@xplatforms.ai" style="color:var(--blue)">privacy@xplatforms.ai</a></li><li><strong>Post:</strong> Privacy Officer, X Platforms Pty Ltd, Level 12, 1 Collins Street, Melbourne VIC 3000, Australia</li><li><strong>Data Protection Officer:</strong> For EU/UK matters: <a href="mailto:dpo@xplatforms.ai" style="color:var(--blue)">dpo@xplatforms.ai</a></li></ul></section>
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
