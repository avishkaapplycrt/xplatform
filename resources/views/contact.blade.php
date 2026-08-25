<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact &ndash; X Platforms</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;
  --blue:#4f8fff;--blue2:#2563eb;--blue-g:rgba(79,143,255,.08);
  --cyan:#38bdf8;--violet:#818cf8;--emerald:#34d399;
  --white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;
  --brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.15);
  --f1:'Outfit',system-ui,sans-serif;--f2:'Source Serif 4',Georgia,serif;--fm:'IBM Plex Mono',monospace;
  --ease:cubic-bezier(.16,1,.3,1);--mw:1200px;
}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}

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

/* PAGE WRAPPER */
.page{padding:120px 40px 80px;max-width:var(--mw);margin:0 auto}

/* BREADCRUMB */
.bc{font-size:12px;color:var(--g400);margin-bottom:40px;display:flex;align-items:center;gap:8px}
.bc a{color:var(--g400);transition:color .2s}.bc a:hover{color:var(--white)}

/* HERO */
.hero-label{display:flex;align-items:center;gap:10px;font-family:var(--fm);font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--g400);margin-bottom:20px}
.hero-label::before{content:'';display:block;width:28px;height:1px;background:var(--g400)}
.hero-h{font-size:clamp(32px,5vw,54px);font-weight:800;letter-spacing:-1.5px;line-height:1.1;margin-bottom:20px}
.hero-h .hl{color:var(--blue)}
.hero-sub{font-size:16px;color:var(--g300);line-height:1.65;max-width:520px;margin-bottom:64px}

/* TWO-COLUMN GRID */
.ct-grid{display:grid;grid-template-columns:1fr 1.15fr;gap:40px;align-items:start}

/* CONTACT OPTION CARDS */
.cc-list{display:flex;flex-direction:column;gap:12px}
.cc{background:var(--card);border:1px solid var(--brd);border-radius:14px;padding:22px 24px;display:flex;gap:18px;align-items:flex-start;transition:border-color .25s,background .25s}
.cc:hover{border-color:var(--brd2);background:var(--card-h)}
.cc-icon{width:40px;height:40px;border-radius:10px;background:rgba(79,143,255,.08);border:1px solid rgba(79,143,255,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.cc-icon svg{width:18px;height:18px;stroke:var(--blue);fill:none;stroke-width:1.6;stroke-linecap:round;stroke-linejoin:round}
.cc-body h4{font-size:14px;font-weight:600;margin-bottom:5px}
.cc-body p{font-size:12.5px;color:var(--g300);line-height:1.55;margin-bottom:10px}
.cc-link{font-size:12.5px;color:var(--blue);font-weight:500;transition:opacity .2s}
.cc-link:hover{opacity:.75}

/* MESSAGE FORM */
.mf{background:var(--card);border:1px solid var(--brd);border-radius:16px;padding:32px}
.mf h3{font-size:18px;font-weight:700;margin-bottom:6px}
.mf-sub{font-size:13px;color:var(--g400);margin-bottom:28px}
.mf-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
.fg{margin-bottom:14px}
.fg label{display:block;font-size:11px;font-weight:500;color:var(--g300);margin-bottom:7px;letter-spacing:.4px;text-transform:uppercase;font-family:var(--fm)}
.fg input,.fg select,.fg textarea{width:100%;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:9px;padding:11px 14px;font-family:var(--f1);font-size:13.5px;color:var(--white);outline:none;transition:border-color .2s}
.fg input::placeholder,.fg textarea::placeholder{color:var(--g500)}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:rgba(79,143,255,.35)}
.fg select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2364748b' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;cursor:pointer}
.fg select option{background:#0f1628;color:var(--white)}
.fg textarea{resize:vertical;min-height:110px;line-height:1.55}
.mf-btn{width:100%;padding:13px 24px;background:var(--blue);color:#fff;border:none;border-radius:9px;font-family:var(--f1);font-size:14px;font-weight:600;cursor:pointer;transition:all .25s;margin-top:6px;box-shadow:0 0 24px rgba(79,143,255,.2)}
.mf-btn:hover{box-shadow:0 0 40px rgba(79,143,255,.4)}
.mf-success{display:none;text-align:center;padding:32px 0}
.mf-success svg{width:48px;height:48px;stroke:var(--emerald);fill:none;stroke-width:1.6;margin:0 auto 16px;display:block}
.mf-success h4{font-size:17px;font-weight:700;margin-bottom:8px}
.mf-success p{font-size:13px;color:var(--g300)}

/* LOCATIONS */
.loc-section{margin-top:80px}
.loc-label{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g400);margin-bottom:24px}
.loc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.loc-card{background:var(--card);border:1px solid var(--brd);border-radius:12px;padding:24px;transition:border-color .25s}
.loc-card:hover{border-color:var(--brd2)}
.loc-code{font-family:var(--fm);font-size:11px;color:var(--g400);letter-spacing:2px;margin-bottom:12px}
.loc-city{font-size:15px;font-weight:600;margin-bottom:8px}
.loc-addr{font-size:12.5px;color:var(--g400);line-height:1.7}

/* FOOTER */
.foot{border-top:1px solid var(--brd);padding:64px 40px;background:var(--bg2)}
.foot-in{max-width:var(--mw);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:40px}
.foot-desc{font-size:13.5px;color:var(--g400);line-height:1.65;max-width:240px;margin-top:14px}
.foot-c h5{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--g500);margin-bottom:18px}
.foot-c a{display:block;font-size:13.5px;color:var(--g300);margin-bottom:11px;transition:color .2s}
.foot-c a:hover{color:var(--white)}
.foot-b{max-width:var(--mw);margin:36px auto 0;padding-top:24px;border-top:1px solid var(--brd);display:flex;justify-content:space-between;font-size:11.5px;color:var(--g500)}
</style>
</head>
<body>

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

<!-- MAIN PAGE -->
<div class="page">

  <!-- BREADCRUMB -->
  <div class="bc">
    <a href="{{ route('home') }}">Home</a>
    <span style="color:var(--g600)">/</span>
    <span style="color:var(--g200)">Contact</span>
  </div>

  <!-- HERO -->
  <div class="hero-label">Get in touch</div>
  <h1 class="hero-h">We respond within <span class="hl">2 business hours</span></h1>
  <p class="hero-sub">Whether you're ready for a demo, have a question before signing up, or need technical support — our team is here. Pick the right channel below.</p>

  <!-- TWO-COLUMN -->
  <div class="ct-grid">

    <!-- LEFT: CONTACT OPTIONS -->
    <div class="cc-list">

      <div class="cc">
        <div class="cc-icon">
          <svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        </div>
        <div class="cc-body">
          <h4>Book a Demo</h4>
          <p>See X Platforms work on your actual customer data in a 30-minute live session. No slides. No pitch deck.</p>
          <a href="{{ route('book-demo') }}" class="cc-link">Book a demo &rarr;</a>
        </div>
      </div>

      <div class="cc">
        <div class="cc-icon">
          <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <div class="cc-body">
          <h4>Sales Enquiry</h4>
          <p>Pricing questions, enterprise requirements, or ready to start a proof of concept? Talk to our sales team.</p>
          <a href="mailto:sales@xplatforms.ai" class="cc-link">sales@xplatforms.ai</a>
        </div>
      </div>

      <div class="cc">
        <div class="cc-icon">
          <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.41 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.56a16 16 0 0 0 5.45 5.45l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/></svg>
        </div>
        <div class="cc-body">
          <h4>Technical Support</h4>
          <p>Existing customer with a technical issue? Our support team responds within 4 hours (Growth) or 1 hour (Enterprise).</p>
          <a href="mailto:support@xplatforms.ai" class="cc-link">support@xplatforms.ai</a>
        </div>
      </div>

      <div class="cc">
        <div class="cc-icon">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="cc-body">
          <h4>Partnerships</h4>
          <p>Agency partners, technology alliances, or reseller programme enquiries. We'd love to explore how we can work together.</p>
          <a href="mailto:partners@xplatforms.ai" class="cc-link">partners@xplatforms.ai</a>
        </div>
      </div>

      <div class="cc">
        <div class="cc-icon">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <div class="cc-body">
          <h4>Press &amp; Media</h4>
          <p>Journalist, podcast host, or analyst? We're happy to arrange interviews, provide data, and contribute to industry research.</p>
          <a href="mailto:press@xplatforms.ai" class="cc-link">press@xplatforms.ai</a>
        </div>
      </div>

    </div>

    <!-- RIGHT: SEND MESSAGE FORM -->
    <div class="mf">
      <h3>Send us a message</h3>
      <p class="mf-sub">We'll reply within 2 business hours.</p>

      <div id="mfForm">
        <div class="mf-row">
          <div class="fg"><label>First Name</label><input type="text" id="mf_fn" placeholder="Jane"></div>
          <div class="fg"><label>Last Name</label><input type="text" id="mf_ln" placeholder="Smith"></div>
        </div>
        <div class="fg"><label>Work Email</label><input type="email" id="mf_em" placeholder="jane@company.com"></div>
        <div class="fg"><label>Company</label><input type="text" id="mf_co" placeholder="Your company"></div>
        <div class="fg">
          <label>Enquiry Type</label>
          <select id="mf_type">
            <option value="" disabled selected>Select type</option>
            <option>Book a Demo</option>
            <option>Sales Enquiry</option>
            <option>Technical Support</option>
            <option>Partnership</option>
            <option>Press &amp; Media</option>
            <option>General Question</option>
          </select>
        </div>
        <div class="fg"><label>Message</label><textarea id="mf_msg" placeholder="Tell us how we can help…"></textarea></div>
        <button class="mf-btn" onclick="mfSubmit()">Send Message &rarr;</button>
      </div>

      <div class="mf-success" id="mfSuccess">
        <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <h4>Message sent!</h4>
        <p>We'll be in touch within 2 business hours.</p>
      </div>
    </div>

  </div>

  <!-- OFFICE LOCATIONS -->
  <div class="loc-section">
    <p class="loc-label">Our offices</p>
    <div class="loc-grid">
      <div class="loc-card">
        <div class="loc-code">AU</div>
        <div class="loc-city">Melbourne HQ</div>
        <div class="loc-addr">Level 12, 1 Collins Street<br>Melbourne VIC 3000</div>
      </div>
      <div class="loc-card">
        <div class="loc-code">SG</div>
        <div class="loc-city">Singapore</div>
        <div class="loc-addr">138 Market Street<br>#28-01 CapitaGreen, 048946</div>
      </div>
      <div class="loc-card">
        <div class="loc-code">GB</div>
        <div class="loc-city">London</div>
        <div class="loc-addr">8 Finsbury Circus<br>London EC2M 7EA</div>
      </div>
    </div>
  </div>

</div>

<!-- FOOTER -->
<footer class="foot"><div class="foot-in">
  <div><a href="{{ url('/') }}" class="logo"><img src="{{ asset('images/xplatforms_logo.jpeg') }}" alt="X Platforms" style="height:32px;width:auto;display:block"></a>><p class="foot-desc">The world's first 8-layer AI intelligence engine.</p></div>
  <div class="foot-c"><h5>Product</h5><a href="{{ route('platform.architecture') }}">Architecture</a><a href="#">Integrations</a><a href="{{ route('industries') }}">Industries</a><a href="{{ route('pricing') }}">Pricing</a></div>
  <div class="foot-c"><h5>Company</h5><a href="{{ route('about') }}">About</a><a href="{{ route('careers') }}">Careers</a><a href="{{ route('blog') }}">Blog</a><a href="{{ route('contact') }}">Contact</a></div>
  <div class="foot-c"><h5>Resources</h5><a href="#">Documentation</a><a href="{{ route('case-studies') }}">Case Studies</a><a href="#">API Reference</a><a href="{{ route('security') }}">Security</a></div>
</div><div class="foot-b"><span>&copy; {{ date('Y') }} X Platforms.</span><span><a href="{{ route('privacy') }}" style="color:inherit">Privacy</a> &middot; <a href="{{ route('terms') }}" style="color:inherit">Terms</a> &middot; <a href="{{ route('security') }}" style="color:inherit">Security</a></span></div></footer>

<script>
function mfSubmit(){
  const fn=document.getElementById('mf_fn').value.trim();
  const em=document.getElementById('mf_em').value.trim();
  if(!fn){alert('Please enter your first name.');return}
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)){alert('Please enter a valid work email.');return}
  const btn=document.querySelector('.mf-btn');
  btn.disabled=true;btn.textContent='Sending…';
  setTimeout(()=>{
    document.getElementById('mfForm').style.display='none';
    document.getElementById('mfSuccess').style.display='block';
  },700);
}
</script>
</body>
</html>
