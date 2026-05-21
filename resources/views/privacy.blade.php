<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Privacy Policy &ndash; X Platforms</title>
<meta name="description" content="X Platforms Privacy Policy. How we collect, use, and protect your personal data. GDPR compliant. Last updated May 2026.">
<meta name="robots" content="index, follow"><link rel="canonical" href="{{ url('/privacy') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;--blue:#4f8fff;--blue2:#2563eb;--blue-g:rgba(79,143,255,.08);--cyan:#38bdf8;--violet:#818cf8;--emerald:#34d399;--amber:#fbbf24;--rose:#f472b6;--white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;--brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.15);--f1:'Outfit',system-ui,sans-serif;--f2:'Source Serif 4',Georgia,serif;--fm:'IBM Plex Mono',monospace;--ease:cubic-bezier(.16,1,.3,1);--mw:1200px}
html{scroll-behavior:smooth}body{background:var(--bg);color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;overflow-x:hidden}a{color:inherit;text-decoration:none}canvas#neural{position:fixed;inset:0;z-index:0;pointer-events:none}
.nav{position:fixed;top:0;width:100%;z-index:100;border-bottom:1px solid var(--brd)}.nav-bg{position:absolute;inset:0;background:rgba(5,7,14,.8);backdrop-filter:blur(40px)}.nav-in{position:relative;max-width:var(--mw);margin:0 auto;padding:0 40px;height:64px;display:flex;align-items:center;justify-content:space-between}.logo{display:flex;align-items:center;gap:11px;font-weight:700;font-size:15px}.logo-m{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--blue),var(--violet));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff}.nav-l{display:flex;align-items:center;gap:28px;list-style:none}.nav-l a{font-size:13.5px;color:var(--g200);transition:color .25s}.nav-l a:hover{color:var(--white)}.nav-cta{padding:8px 22px;background:var(--blue);color:#fff!important;border-radius:8px;font-weight:600!important;font-size:13px!important}
.page{position:relative;z-index:1;padding:120px 40px 80px;max-width:var(--mw);margin:0 auto}
.breadcrumb{font-family:var(--fm);font-size:12px;color:var(--g400);margin-bottom:24px;letter-spacing:.5px}.breadcrumb a{color:var(--g300)}.breadcrumb a:hover{color:var(--blue)}
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:12px;display:flex;align-items:center;gap:10px}.stag::before{content:'';width:16px;height:1px;background:var(--blue)}
h1{font-weight:800;font-size:clamp(32px,4vw,56px);line-height:1.08;letter-spacing:-2px;margin-bottom:16px}
h1 span,h2 span{background:linear-gradient(135deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
h2{font-weight:700;font-size:clamp(22px,2.6vw,32px);letter-spacing:-1px;margin-bottom:12px;line-height:1.2}
p{font-size:15px;color:var(--g200);line-height:1.75;margin-bottom:14px;font-weight:350}p:last-child{margin-bottom:0}
.btn-fill{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:11px;font-weight:600;font-size:14px;border:none;cursor:pointer;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;box-shadow:0 4px 24px rgba(79,143,255,.25);font-family:var(--f1);transition:all .25s}.btn-fill:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(79,143,255,.35)}
.foot{border-top:1px solid var(--brd);background:var(--bg2);position:relative;z-index:1}.foot-top{max-width:var(--mw);margin:0 auto;padding:64px 40px 48px;display:grid;grid-template-columns:280px 1fr;gap:64px}.foot-logo{display:inline-flex;align-items:center;gap:11px;font-weight:700;font-size:15px;margin-bottom:14px;color:var(--white)}.foot-logo-m{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--blue),var(--violet));display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff;flex-shrink:0}.foot-desc{font-size:13px;color:var(--g400);line-height:1.65;max-width:240px}.foot-cols{display:grid;grid-template-columns:repeat(4,1fr);gap:32px}.foot-col h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:16px;font-weight:500}.foot-col a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:10px;transition:color .2s}.foot-col a:hover{color:var(--white)}.foot-bottom{max-width:var(--mw);margin:0 auto;padding:20px 40px 28px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;align-items:center;font-size:12px;color:var(--g500);flex-wrap:wrap;gap:12px}.foot-bottom-links a{color:var(--g500);margin-left:20px;transition:color .2s}.foot-bottom-links a:hover{color:var(--g200)}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}.rv{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease)}.rv.vis{opacity:1;transform:translateY(0)}
@media(max-width:1024px){.foot-top{grid-template-columns:1fr}.foot-cols{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.nav-l{display:none}.nav-in{padding:0 20px}.page{padding-left:20px;padding-right:20px;padding-top:100px}.foot-top{padding:48px 20px 32px}.foot-bottom{padding:16px 20px 24px;flex-direction:column;text-align:center}.foot-cols{grid-template-columns:1fr 1fr}}
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
  <a href="{{ route('home') }}" class="logo"><span class="logo-m">X</span> Platforms</a>
  <ul class="nav-l">
    <li><a href="{{ route('home') }}">Home</a></li>
    <li><a href="{{ route('industries') }}">Industries</a></li>
    <li><a href="{{ route('pricing') }}">Pricing</a></li>
    <li><a href="{{ route('contact') }}" class="nav-cta">Book a Demo</a></li>
  </ul>
</div></nav>
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
<footer class="foot"><div class="foot-top">
  <div><a href="{{ route('home') }}" class="foot-logo"><span class="foot-logo-m">X</span>Platforms</a><p class="foot-desc">The world&rsquo;s first 8-layer AI intelligence engine. Every customer signal. One brain. 15 industries.</p></div>
  <div class="foot-cols">
    <div class="foot-col"><h5>Product</h5><a href="{{ route('simulator') }}">Simulator</a><a href="{{ route('industries') }}">Industries</a><a href="{{ route('pricing') }}">Pricing</a><a href="{{ route('security') }}">Security</a></div>
    <div class="foot-col"><h5>Industries</h5><a href="{{ route('industries') }}">Retail</a><a href="{{ route('banking') }}">Banking</a><a href="{{ route('industries') }}">Healthcare</a><a href="{{ route('industries') }}">All 15 &rarr;</a></div>
    <div class="foot-col"><h5>Company</h5><a href="{{ route('about') }}">About</a><a href="{{ route('careers') }}">Careers</a><a href="#">Blog</a><a href="{{ route('contact') }}">Contact</a></div>
    <div class="foot-col"><h5>Legal</h5><a href="{{ route('security') }}">Security</a><a href="{{ route('privacy') }}" style="color:var(--white)">Privacy Policy</a><a href="{{ route('terms') }}">Terms of Service</a><a href="#">Cookie Policy</a></div>
  </div>
</div><div class="foot-bottom"><span>&copy; {{ date('Y') }} X Platforms Pty Ltd. All rights reserved.</span><span class="foot-bottom-links"><a href="{{ route('privacy') }}">Privacy</a><a href="{{ route('terms') }}">Terms</a><a href="{{ route('security') }}">Security</a></span></div></footer>
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
