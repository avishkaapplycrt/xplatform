<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Demo Booked — X Platforms</title>
<meta name="robots" content="noindex">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#05070e;--bg2:#090d19;--bg3:#0d1224;--card:#0f1628;--card-h:#141d36;
  --blue:#4f8fff;--blue2:#2563eb;--blue-g:rgba(79,143,255,.08);--blue-g2:rgba(79,143,255,.04);
  --cyan:#38bdf8;--violet:#818cf8;--violet-g:rgba(129,140,248,.08);
  --emerald:#34d399;--em-g:rgba(52,211,153,.08);--em-b:rgba(52,211,153,.2);
  --amber:#fbbf24;--rose:#f472b6;
  --white:#f1f5f9;--g100:#cbd5e1;--g200:#94a3b8;--g300:#64748b;--g400:#475569;--g500:#334155;--g600:#1e293b;
  --brd:rgba(79,143,255,.08);--brd2:rgba(79,143,255,.18);
  --f1:'Outfit',system-ui,sans-serif;--f2:'Source Serif 4',Georgia,serif;--fm:'IBM Plex Mono',monospace;
  --ease:cubic-bezier(.16,1,.3,1);
}
html,body{height:100%}
body{background:var(--bg);color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;display:flex;flex-direction:column;min-height:100vh;overflow-x:hidden}

.bg-dots{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:radial-gradient(circle,rgba(79,143,255,.07) 1px,transparent 1px);background-size:28px 28px}
.bg-glow{position:fixed;top:-200px;left:50%;transform:translateX(-50%);width:900px;height:600px;background:radial-gradient(ellipse,rgba(129,140,248,.06) 0%,transparent 65%);z-index:0;pointer-events:none}

/* NAV */
.nav{position:relative;z-index:10;padding:18px 40px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--brd);background:rgba(5,7,14,.8);backdrop-filter:blur(40px)}
.logo{display:flex;align-items:center;gap:9px;font-weight:700;font-size:14.5px;color:var(--white)}
.logo-m{width:26px;height:26px;border-radius:7px;background:linear-gradient(135deg,var(--blue),var(--violet));display:grid;place-items:center;font-weight:800;font-size:12px;color:#fff;box-shadow:0 0 16px rgba(79,143,255,.3)}
.nav-link{font-size:13px;color:var(--g300);transition:color .2s;display:flex;align-items:center;gap:5px}.nav-link:hover{color:var(--white)}
.nav-link svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2}

/* MAIN */
.main{flex:1;display:flex;align-items:center;justify-content:center;padding:48px 20px;position:relative;z-index:1}
.container{width:100%;max-width:560px;text-align:center}

/* SUCCESS RING */
.ring-wrap{position:relative;width:88px;height:88px;margin:0 auto 26px;animation:scaleIn .55s var(--ease) both}
@keyframes scaleIn{from{opacity:0;transform:scale(.4)}to{opacity:1;transform:scale(1)}}
.ring-pulse{position:absolute;inset:-10px;border-radius:50%;border:1.5px solid rgba(52,211,153,.15);animation:pulse-ring 2s ease-out .5s infinite}
.ring-pulse2{position:absolute;inset:-20px;border-radius:50%;border:1px solid rgba(52,211,153,.07);animation:pulse-ring 2s ease-out .8s infinite}
@keyframes pulse-ring{0%{transform:scale(.9);opacity:1}100%{transform:scale(1.15);opacity:0}}
.ring{width:88px;height:88px;border-radius:50%;background:var(--em-g);border:1.5px solid var(--em-b);display:grid;place-items:center;position:relative;z-index:1}
.ring svg{width:38px;height:38px;stroke:var(--emerald);fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
.ring svg polyline{stroke-dasharray:44;stroke-dashoffset:44;animation:drawCheck .45s ease .55s both}
@keyframes drawCheck{to{stroke-dashoffset:0}}

/* TEXT */
.tag{font-family:var(--fm);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--emerald);margin-bottom:12px;animation:fadeUp .45s var(--ease) .3s both}
h1{font-family:var(--f2);font-weight:600;font-size:clamp(28px,5vw,42px);line-height:1.1;letter-spacing:-.025em;color:var(--white);margin-bottom:14px;animation:fadeUp .45s var(--ease) .38s both}
h1 em{font-style:italic;font-weight:400;color:var(--g300)}
.subtext{font-size:15px;line-height:1.78;color:var(--g200);max-width:420px;margin:0 auto 28px;animation:fadeUp .45s var(--ease) .46s both}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}

/* BOOKING CARD */
.booking-card{background:var(--card);border:1.5px solid var(--brd);border-radius:16px;overflow:hidden;margin-bottom:24px;text-align:left;animation:fadeUp .45s var(--ease) .54s both;box-shadow:0 4px 32px rgba(0,0,0,.3)}
.bc-header{padding:12px 18px;background:var(--bg3);border-bottom:1px solid var(--brd);display:flex;align-items:center;gap:8px}
.bc-dot{width:8px;height:8px;border-radius:50%;background:var(--emerald);box-shadow:0 0 8px var(--emerald)}
.bc-header-title{font-family:var(--fm);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--g300)}
.bc-row{display:flex;justify-content:space-between;align-items:center;padding:11px 18px;border-bottom:1px solid var(--brd);font-size:13.5px}
.bc-row:last-child{border-bottom:none}
.bc-lbl{color:var(--g300);font-size:12.5px}
.bc-val{color:var(--white);font-weight:500}
.bc-val.green{color:var(--emerald)}

/* WHAT'S NEXT */
.next-section{margin-bottom:24px;text-align:left;animation:fadeUp .45s var(--ease) .62s both}
.ns-label{font-family:var(--fm);font-size:9.5px;letter-spacing:1.8px;text-transform:uppercase;color:var(--g400);margin-bottom:12px;padding:0 2px}
.steps{display:flex;flex-direction:column;gap:8px}
.step{display:flex;gap:14px;padding:14px 16px;background:var(--card);border:1px solid var(--brd);border-radius:12px;transition:border-color .2s;box-shadow:0 1px 8px rgba(0,0,0,.2)}
.step:hover{border-color:var(--brd2)}
.step-num{width:28px;height:28px;border-radius:8px;display:grid;place-items:center;flex-shrink:0;font-family:var(--fm);font-size:11px;font-weight:500;margin-top:1px}
.step-body .step-title{font-size:13.5px;font-weight:600;color:var(--white);margin-bottom:3px}
.step-body .step-desc{font-size:12.5px;color:var(--g200);line-height:1.6}

/* ACTIONS */
.actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:28px;animation:fadeUp .45s var(--ease) .7s both}
.btn-primary{padding:12px 24px;border-radius:9px;background:var(--blue);color:#fff;font-weight:600;font-size:14px;border:none;cursor:pointer;font-family:var(--f1);transition:all .2s;display:inline-flex;align-items:center;gap:6px;text-decoration:none;box-shadow:0 0 24px rgba(79,143,255,.25)}
.btn-primary:hover{background:#3a7aff;transform:translateY(-1px);box-shadow:0 6px 28px rgba(79,143,255,.4)}
.btn-outline{padding:12px 24px;border-radius:9px;background:transparent;color:var(--g200);font-weight:500;font-size:14px;border:1.5px solid var(--brd);cursor:pointer;font-family:var(--f1);transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-outline:hover{border-color:var(--brd2);color:var(--white)}

/* SOCIAL PROOF */
.proof{animation:fadeUp .45s var(--ease) .78s both}
.proof-label{font-family:var(--fm);font-size:9.5px;letter-spacing:1.8px;text-transform:uppercase;color:var(--g400);margin-bottom:12px}
.testimonials{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.tcard{background:var(--card);border:1px solid var(--brd);border-radius:12px;padding:14px 16px;text-align:left;box-shadow:0 1px 8px rgba(0,0,0,.2)}
.tc-quote{font-family:var(--f2);font-size:13px;font-style:italic;color:var(--g200);line-height:1.65;margin-bottom:10px}
.tc-author{display:flex;align-items:center;gap:8px}
.tc-av{width:26px;height:26px;border-radius:50%;display:grid;place-items:center;font-weight:700;font-size:10px;color:#fff;flex-shrink:0}
.tc-name{font-size:12px;font-weight:600;color:var(--white)}
.tc-role{font-size:10.5px;color:var(--g300)}

/* FOOTER */
.footer{position:relative;z-index:1;padding:18px 40px;border-top:1px solid var(--brd);background:var(--bg2);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px}
.footer-copy{font-size:12px;color:var(--g400)}
.footer-links{display:flex;gap:20px}
.footer-links a{font-size:12px;color:var(--g400);transition:color .2s;text-decoration:none}.footer-links a:hover{color:var(--white)}

/* CELEBRATION */
.dot{position:fixed;border-radius:50%;pointer-events:none;z-index:999;opacity:0;animation:floatUp 2.5s ease forwards}
@keyframes floatUp{0%{opacity:0;transform:translateY(0) scale(0) rotate(0deg)}15%{opacity:.9}85%{opacity:.4}100%{opacity:0;transform:translateY(-140px) scale(1.3) rotate(360deg)}}

@media(max-width:520px){.testimonials{grid-template-columns:1fr}.nav{padding:14px 20px}.main{padding:32px 16px}.footer{padding:14px 20px;flex-direction:column;text-align:center}}
</style>
</head>
<body>

<div class="bg-dots"></div>
<div class="bg-glow"></div>

<nav class="nav">
  <a href="{{ route('home') }}" class="logo"><span class="logo-m">X</span>Platforms</a>
  <a href="{{ route('home') }}" class="nav-link">
    <svg viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Back to home
  </a>
</nav>

<main class="main">
  <div class="container">

    <div class="ring-wrap">
      <div class="ring-pulse2"></div>
      <div class="ring-pulse"></div>
      <div class="ring">
        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
    </div>

    <div class="tag">Demo confirmed</div>
    <h1>Thanks for booking.<br><em>We'll be in touch shortly.</em></h1>
    <p class="subtext">Your demo is confirmed. Our team will reach out within 2 hours with a calendar invite and a short questionnaire to tailor the session to your business.</p>

    <div class="booking-card">
      <div class="bc-header">
        <div class="bc-dot"></div>
        <div class="bc-header-title">Booking summary</div>
      </div>
      <div class="bc-row"><div class="bc-lbl">Session type</div><div class="bc-val">Live demo — your data</div></div>
      <div class="bc-row"><div class="bc-lbl">Duration</div><div class="bc-val">30 minutes</div></div>
      <div class="bc-row"><div class="bc-lbl">Confirmed for</div><div class="bc-val" id="confirmedDT">{{ $demoDate ?? '—' }}</div></div>
      <div class="bc-row"><div class="bc-lbl">Format</div><div class="bc-val">Google Meet / Zoom</div></div>
      <div class="bc-row"><div class="bc-lbl">Calendar invite</div><div class="bc-val green">✓ Arriving within 2 hours</div></div>
    </div>

    <div class="next-section">
      <div class="ns-label">What happens next</div>
      <div class="steps">
        <div class="step">
          <div class="step-num" style="background:var(--blue-g);color:var(--blue)">1</div>
          <div class="step-body">
            <div class="step-title">Check your inbox</div>
            <div class="step-desc">A calendar invite and a short pre-demo questionnaire arrive within 2 hours — so we can connect your data and make the session as specific to your goals as possible.</div>
          </div>
        </div>
        <div class="step">
          <div class="step-num" style="background:var(--violet-g);color:var(--violet)">2</div>
          <div class="step-body">
            <div class="step-title">We connect your data before the call</div>
            <div class="step-desc">Share your platforms in the questionnaire (Salesforce, Shopify, Stripe, etc.) and our team connects them before the session — so you see predictions on your real customers, not a demo account.</div>
          </div>
        </div>
        <div class="step">
          <div class="step-num" style="background:var(--em-g);color:var(--emerald)">3</div>
          <div class="step-body">
            <div class="step-title">Live on your data — 30 minutes</div>
            <div class="step-desc">We walk through churn predictions, CLV scores, and recommended actions for your actual customer base. No slides, no scripts — just your data through the engine.</div>
          </div>
        </div>
      </div>
    </div>

    <div class="actions">
      <a href="{{ route('simulator') }}" class="btn-primary">
        Try the simulator
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="{{ route('case-studies') }}" class="btn-outline">Read case studies</a>
    </div>

    <div class="proof">
      <div class="proof-label">What others say after their demo</div>
      <div class="testimonials">
        <div class="tcard">
          <div class="tc-quote">"They connected our Shopify store before the call and showed us customers about to churn that we had no idea about. We signed within a week."</div>
          <div class="tc-author">
            <div class="tc-av" style="background:linear-gradient(135deg,#2563eb,#6366f1)">PK</div>
            <div><div class="tc-name">Priya Kapoor</div><div class="tc-role">CPO · MockMaster · 41% churn reduction</div></div>
          </div>
        </div>
        <div class="tcard">
          <div class="tc-quote">"X Platforms gave our relationship managers a ranked call list every Monday with the full context on each account. Revenue per call tripled."</div>
          <div class="tc-author">
            <div class="tc-av" style="background:linear-gradient(135deg,#6366f1,#e11d48)">JL</div>
            <div><div class="tc-name">James Liu</div><div class="tc-role">CTO · Herald Bank · 28% churn reduction</div></div>
          </div>
        </div>
      </div>
    </div>

  </div>
</main>

<footer class="footer">
  <span class="footer-copy">&copy; {{ date('Y') }} X Platforms Pty Ltd · Melbourne, Australia</span>
  <div class="footer-links">
    <a href="{{ route('privacy') }}">Privacy Policy</a>
    <a href="{{ route('terms') }}">Terms</a>
    <a href="mailto:support@xplatforms.ai">support@xplatforms.ai</a>
  </div>
</footer>

<script>
(function(){
  const colors = ['#4f8fff','#818cf8','#34d399','#38bdf8','#fbbf24','#f472b6'];
  for (let i = 0; i < 22; i++) {
    setTimeout(function(){
      var el = document.createElement('div');
      el.className = 'dot';
      var size = 5 + Math.random() * 9;
      el.style.cssText = [
        'width:' + size + 'px',
        'height:' + size + 'px',
        'background:' + colors[Math.floor(Math.random() * colors.length)],
        'left:' + (8 + Math.random() * 84) + '%',
        'top:' + (15 + Math.random() * 50) + '%',
        'animation-delay:' + (Math.random() * 0.6) + 's',
        'animation-duration:' + (2 + Math.random() * 1.2) + 's'
      ].join(';');
      document.body.appendChild(el);
      setTimeout(function(){ el.remove(); }, 4000);
    }, i * 60);
  }
})();
</script>
</body>
</html>
