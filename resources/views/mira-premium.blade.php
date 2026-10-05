<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Premium Mira &ndash; X Platforms</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
/* Premium palette — ivory and gold. Uses the same variable names as the
   landing page so the shared Ask Mira styles (partials/ask-mira) pick it up.
   --white is the main text colour (the landing page's naming). */
:root{
  --body-bg:linear-gradient(135deg,#fdf8ec 0%,#f6e9cc 50%,#fdf8ec 100%);
  --bg:#fdf8ec;--bg2:rgba(246,233,204,.55);--bg3:rgba(240,224,188,.45);--card:rgba(255,255,255,.72);--card-h:rgba(255,255,255,.85);
  --blue:#a8781f;--blue2:#8f6516;--cyan:#c9962e;--violet:#d4a843;
  --emerald:#059669;--amber:#a16207;--rose:#e11d48;
  --white:#2a2112;--g100:#3d3220;--g200:#5a4b33;--g300:#7a6a4f;--g400:#9c8b6c;--g500:#e3d3ae;--g600:#efe3c6;
  --brd:rgba(184,134,43,.18);--brd2:rgba(184,134,43,.3);
  --acc-rgb:184,134,43;--on-acc:#1a1306;
  --f1:'Outfit',system-ui,sans-serif;--fm:'IBM Plex Mono',monospace;
  --ease:cubic-bezier(.16,1,.3,1);--mw:1200px;
}
html{scroll-behavior:smooth}
body{background:var(--body-bg);background-attachment:fixed;color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;min-height:100vh}
a{color:inherit;text-decoration:none}

/* NAV */
.nav{position:sticky;top:0;z-index:100;border-bottom:1px solid var(--brd);background:rgba(253,248,236,.85);backdrop-filter:blur(20px)}
.nav-in{max-width:var(--mw);margin:0 auto;padding:0 40px;height:64px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.nav-back{font-size:13.5px;color:var(--g200);transition:color .25s}
.nav-back:hover{color:var(--blue)}

/* SECTION HEADER (same structure as the landing page) */
.sec{position:relative;z-index:1;padding:72px 40px 96px;max-width:var(--mw);margin:0 auto}
.stag{font-family:var(--fm);font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--blue);margin-bottom:14px;display:flex;align-items:center;gap:10px}
.stag::before{content:'';width:16px;height:1px;background:var(--blue)}
.sh{font-weight:700;font-size:clamp(30px,3.6vw,46px);letter-spacing:-1.5px;line-height:1.12;margin-bottom:16px}
.ss{font-size:16px;color:var(--g300);max-width:460px;line-height:1.7;font-weight:350}
.stop{margin-bottom:48px}
/* #ask-mira prefix: beats the gradient-text rule on "#ask-mira .sh span". */
#ask-mira .sh .premium-badge{display:inline-block;vertical-align:middle;margin-left:10px;padding:4px 12px;border-radius:20px;font-size:13px;letter-spacing:.5px;font-weight:700;background:linear-gradient(135deg,#d4a843,#f3d98b);-webkit-background-clip:border-box;background-clip:border-box;-webkit-text-fill-color:#1a1306;color:#1a1306}

@include('partials.ask-mira.styles')

/* Premium touches on the shared chat */
.chat-card{box-shadow:0 10px 50px rgba(168,120,31,.14)}
.chat-sidebar{box-shadow:0 10px 40px rgba(168,120,31,.08)}
.chat-msg.user{background:linear-gradient(135deg,#d4a843,#e8c46a)}
.chat-send{background:linear-gradient(135deg,#d4a843,#e8c46a)}
.chat-upsell-aside{background:linear-gradient(135deg,rgba(212,168,67,.12),rgba(243,217,139,.05));border-color:rgba(212,168,67,.3)}
.a-modal-overlay,.lead-modal-overlay{background:rgba(42,33,18,.45)}
.a-modal,.lead-modal{background:#fffdf7;box-shadow:0 20px 60px rgba(42,33,18,.25)}
.site-box{background:var(--bg3);border:1px solid var(--g500);border-radius:10px;padding:10px 12px;font-size:12.5px;line-height:1.5;display:flex;flex-direction:column;gap:4px}
.site-empty{color:var(--g300)}
.site-title{color:var(--white);font-weight:600}
.site-url{color:var(--blue);word-break:break-all;text-decoration:underline}
/* Clickable suggested questions under a bot reply */
.chat-suggest{display:flex;flex-wrap:wrap;gap:8px;align-self:flex-start;max-width:80%;margin-top:-4px}
.suggest-chip{background:#fff;border:1px solid var(--brd2);border-radius:999px;padding:7px 14px;color:var(--blue2);font-family:var(--f1);font-size:13px;font-weight:500;line-height:1.4;text-align:left;cursor:pointer;transition:background .2s,border-color .2s}
.suggest-chip:hover{background:rgba(var(--acc-rgb),.1);border-color:var(--blue)}

/* Try asking: SEO / AEO / GEO group pills (sized to fit the 230px sidebar) */
.starter-pill{display:inline-flex;align-items:center;gap:6px;max-width:100%;margin:14px 0 8px;padding:3px 9px 3px 3px;border-radius:999px;border:1px solid var(--pill-brd);background:var(--pill-bg)}
.side-lbl + .starter-pill{margin-top:0}
.starter-icon{width:18px;height:18px;border-radius:50%;background:var(--pill-c);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.starter-icon svg{width:10px;height:10px;fill:none;stroke:#fff;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}
.starter-label{font-family:var(--fm);font-size:10px;font-weight:500;letter-spacing:1px;color:var(--pill-c);padding-right:6px;border-right:1px solid var(--pill-brd)}
.starter-tagline{font-size:11px;font-weight:500;color:var(--g100);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.starter-seo{--pill-c:#2f6fdb;--pill-bg:#e3edfb;--pill-brd:#bcd2f3}
.starter-aeo{--pill-c:#c8701e;--pill-bg:#fbe8d6;--pill-brd:#efc9a3}
.starter-geo{--pill-c:#1f8a5f;--pill-bg:#dcf1e6;--pill-brd:#b2dec7}

@media(max-width:640px){
  .nav-in,.sec{padding-left:20px;padding-right:20px}
  .sec{padding-top:48px;padding-bottom:64px}
}
</style>
</head>
<body>

<nav class="nav"><div class="nav-in">
  <a href="{{ url('/') }}"><img src="{{ asset('images/xplatforms_logo.jpeg') }}" alt="X Platforms" style="height:32px;width:auto;display:block"></a>
  <a href="{{ url('/') }}" class="nav-back">&larr; Back to home</a>
</div></nav>

<section class="sec" id="ask-mira">
  <div class="stop" style="text-align:center">
    <div class="stag" style="justify-content:center">Premium AI Assistant</div>
    <h2 class="sh" style="margin:0 auto 16px">Ask <span>Mira</span> <span class="premium-badge">Premium</span></h2>
    <p class="ss" style="margin:0 auto;max-width:560px">Paste your website URL, then ask anything about it. Answers focus on AEO (Answer Engine Optimization), getting your site picked as the answer in featured snippets, "People also ask" and voice search, and cover SEO and GEO (AI chatbot visibility) too.</p>
  </div>

  <div class="chat-page-wrap">
    <div class="chat-layout">

      <aside class="chat-sidebar">
        <div>
          <div class="side-lbl">Your website</div>
          <div class="site-box" id="siteBox"><span class="site-empty">No website yet. Paste your URL in the chat to start.</span></div>
        </div>
        <div>
          <div class="side-lbl">Try asking</div>
          @foreach([
            [
              'key' => 'seo', 'label' => 'SEO', 'tagline' => 'Get found on Google',
              'icon' => '<circle cx="11" cy="11" r="6"/><path d="M20 20l-4.5-4.5"/>',
              'questions' => [
                'How can I improve my title and meta description?',
                'Which keywords is my page targeting?',
                'What is holding my page back from ranking higher?',
              ],
            ],
            [
              'key' => 'aeo', 'label' => 'AEO', 'tagline' => 'Be the answer',
              'icon' => '<path d="M21 12a8 8 0 01-11.6 7.1L4 20l1-4.6A8 8 0 1121 12z"/><path d="M9 12h6"/>',
              'questions' => [
                'Which questions could we win the featured snippet for?',
                'Write an FAQ section for my homepage',
              ],
            ],
            [
              'key' => 'geo', 'label' => 'GEO', 'tagline' => 'Get recommended by AI',
              'icon' => '<path d="M10 3l1.8 4.7L16.5 9.5l-4.7 1.8L10 16l-1.8-4.7L3.5 9.5l4.7-1.8z"/><path d="M18 14l.9 2.1L21 17l-2.1.9L18 20l-.9-2.1L15 17l2.1-.9z"/>',
              'questions' => [
                'Would ChatGPT recommend my business? Why or why not?',
                'What facts and stats should my page include so AI cites it?',
                'Where should my brand be mentioned online to show up in AI answers?',
              ],
            ],
          ] as $group)
            <div class="starter-pill starter-{{ $group['key'] }}">
              <span class="starter-icon"><svg viewBox="0 0 24 24" aria-hidden="true">{!! $group['icon'] !!}</svg></span>
              <span class="starter-label">{{ $group['label'] }}</span>
              <span class="starter-tagline">{{ $group['tagline'] }}</span>
            </div>
            <div class="side-starters">
              @foreach($group['questions'] as $q)
                <button type="button" class="side-starter" onclick="askPremium({{ \Illuminate\Support\Js::from($q) }})">{{ $q }}</button>
              @endforeach
            </div>
          @endforeach
        </div>
      </aside>

      <div class="chat-card">
        <div class="chat-tabs">
          <div class="chat-tab-box active" id="tabBoxAsk">
            <button type="button" class="chat-tab" id="tabBtnAsk" onclick="switchChatTab('ask')">
              <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
              Ask Mira Premium
            </button>
            <button type="button" class="chat-reset" onclick="resetPremium()" title="New chat" aria-label="New chat">
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
            <div class="chat-msg bot">Hi, I'm Mira. Paste your website URL to start the chat</div>
          </div>
          <div class="chat-input-wrap">
            <textarea class="chat-input" id="chatInput" placeholder="https://yourwebsite.com" rows="1" onkeydown="if(event.key==='Enter'&amp;&amp;!event.shiftKey){event.preventDefault();sendPremium()}"></textarea>
            <button class="chat-send" id="chatSend" onclick="sendPremium()" aria-label="Send"><svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg></button>
          </div>
          <div class="chat-footer">
            <span class="mira-status {{ $premiumLive ? 'live' : 'basic' }}"><span class="status-dot"></span> Mira Premium &middot; {{ $premiumLive ? 'AEO' : 'Offline' }}</span>
          </div>
        </div>

        {{-- "Analyse my website": same free SEO/technical check as the landing
             page, driven by partials.ask-mira.script (chat.send / chat.analyze-lead). --}}
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

{{-- Shared Ask Mira script: provides switchChatTab, sendAnalyzeUrl, the lead
     modal, report rendering and renderBotMessage. The premium chat below
     reuses renderBotMessage. --}}
<script>
@include('partials.ask-mira.script')
</script>

<script>
const PREMIUM_GREETING = document.getElementById('chatMsgs').innerHTML;
let premiumBusy = false;

// The page's CSRF token goes stale when the session expires (SESSION_LIFETIME)
// or is replaced, e.g. by logging in or out in another tab. Laravel then
// answers 419 "CSRF token mismatch". On a 419, reload the page's HTML in the
// background (which also starts a fresh session), take its new token, update
// the meta tag so the shared Analyse script uses it too, and retry once.
function csrfToken(){
  return document.querySelector('meta[name="csrf-token"]').content;
}
async function refreshCsrfToken(){
  const html=await (await fetch(location.pathname,{credentials:'same-origin',cache:'no-store'})).text();
  const m=html.match(/<meta name="csrf-token" content="([^"]+)"/);
  if(m) document.querySelector('meta[name="csrf-token"]').content=m[1];
}
async function postJson(url, body){
  const send=()=>fetch(url,{method:'POST',credentials:'same-origin',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken()},
    body:JSON.stringify(body||{})});
  let res=await send();
  if(res.status===419){
    await refreshCsrfToken();
    res=await send();
  }
  return res;
}

function addMsg(cls, text){
  const msgs=document.getElementById('chatMsgs');
  const el=document.createElement('div');
  el.className='chat-msg '+cls;
  if(cls==='bot') renderBotMessage(el, text); else el.textContent=text;
  msgs.appendChild(el);
  msgs.scrollTop=msgs.scrollHeight;
  return el;
}

function setSite(site){
  const box=document.getElementById('siteBox');
  const input=document.getElementById('chatInput');
  box.innerHTML='';
  if(!site){
    const s=document.createElement('span');s.className='site-empty';
    s.textContent='No website yet. Paste your URL in the chat to start.';
    box.appendChild(s);
    input.placeholder='https://yourwebsite.com';
    return;
  }
  const t=document.createElement('div');t.className='site-title';t.textContent=site.title;
  const u=document.createElement('a');u.className='site-url';u.href=site.url;u.target='_blank';u.rel='noopener noreferrer';u.textContent=site.url;
  box.appendChild(t);box.appendChild(u);
  input.placeholder='Ask about your website...';
}

async function sendPremium(){
  const input=document.getElementById('chatInput'),msg=input.value.trim();
  if(!msg||premiumBusy) return;
  premiumBusy=true;
  input.value='';
  addMsg('user', msg);
  const typing=addMsg('bot typing', /^(https?:\/\/|www\.)/i.test(msg)||/^[a-z0-9-]+(\.[a-z0-9-]+)+(\/\S*)?$/i.test(msg) ? 'Reading your website...' : 'Thinking...');
  try{
    const res=await postJson('{{ route("mira-premium.send") }}',{message:msg});
    const data=await res.json();
    typing.remove();
    if(res.ok){
      addMsg('bot', data.reply);
      if(data.site) setSite(data.site);
      if(data.suggestions && data.suggestions.length) addSuggestions(data.suggestions);
    }else{
      addMsg('bot', data.error||data.message||'Something went wrong. Please try again.');
    }
  }catch(e){
    typing.remove();
    addMsg('bot', 'Could not reach the server. Check your connection and try again.');
  }
  premiumBusy=false;
}

function askPremium(q){
  document.getElementById('chatInput').value=q;
  sendPremium();
}

// Clickable question chips under a bot reply. The row is removed once one
// is picked, so it can't be sent twice.
function addSuggestions(questions){
  const msgs=document.getElementById('chatMsgs');
  const row=document.createElement('div');
  row.className='chat-suggest';
  questions.forEach(function(q){
    const btn=document.createElement('button');
    btn.type='button';
    btn.className='suggest-chip';
    btn.textContent=q;
    btn.addEventListener('click',function(){
      if(premiumBusy) return;
      row.remove();
      askPremium(q);
    });
    row.appendChild(btn);
  });
  msgs.appendChild(row);
  msgs.scrollTop=msgs.scrollHeight;
}

async function resetPremium(){
  await postJson('{{ route("mira-premium.reset") }}');
  document.getElementById('chatMsgs').innerHTML=PREMIUM_GREETING;
  setSite(null);
  // Also clear the "Analyse my website" tab, like the landing page's resetChat().
  document.getElementById('analysisPanel').classList.remove('show');
  document.getElementById('chatUpsellAside').classList.remove('show');
  document.getElementById('analyzeInput').value='';
  document.getElementById('analyzeStatus').textContent='';
  document.getElementById('analyzeStatus').classList.remove('err');
  switchChatTab('ask');
}
</script>
</body>
</html>
