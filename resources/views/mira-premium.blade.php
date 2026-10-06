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
/* Premium palette — dark with gold accents. Uses the same variable names as
   the landing page so the shared Ask Mira styles (partials/ask-mira) pick it
   up: --white is the main text colour and --blue the accent (the landing
   page's naming), so here they hold warm off-white and gold. */
:root{
  --body-bg:#07070a;
  --bg:#0d0d12;--bg2:#0d0d12;--bg3:rgba(255,255,255,.04);--card:rgba(255,255,255,.035);--card-h:rgba(255,255,255,.06);
  --blue:#e8c468;--blue2:#f6dd98;--cyan:#f6dd98;--violet:#e8c468;
  --emerald:#3fc495;--amber:#f2c14a;--rose:#ff7a6b;
  --white:#f4f1ea;--g100:#dcd8cf;--g200:#cfcbc2;--g300:#9a968d;--g400:#6b6862;--g500:rgba(255,255,255,.08);--g600:rgba(255,255,255,.06);
  --brd:rgba(255,255,255,.08);--brd2:rgba(232,196,104,.28);
  --acc-rgb:232,196,104;--on-acc:#17120a;
  --gold:#e8c468;--gold-2:#f6dd98;--gold-3:#b8902f;
  --f1:'Outfit',system-ui,sans-serif;--fm:'IBM Plex Mono',monospace;
  --ease:cubic-bezier(.16,1,.3,1);--mw:1200px;
}
html{scroll-behavior:smooth}
body{background:var(--body-bg);color:var(--white);font-family:var(--f1);-webkit-font-smoothing:antialiased;min-height:100vh;overflow-x:hidden}
/* Ambient gold glow and a fine grid that fades out down the page. */
body::before{content:"";position:fixed;inset:0;pointer-events:none;z-index:0;
  background:radial-gradient(900px 480px at 50% -120px,rgba(232,196,104,.18),transparent 70%),
             radial-gradient(600px 400px at 100% 40%,rgba(232,196,104,.05),transparent 70%),
             radial-gradient(600px 400px at 0% 70%,rgba(111,155,255,.04),transparent 70%)}
body::after{content:"";position:fixed;inset:0;pointer-events:none;z-index:0;opacity:.25;
  background-image:linear-gradient(var(--brd) 1px,transparent 1px),linear-gradient(90deg,var(--brd) 1px,transparent 1px);background-size:64px 64px;
  -webkit-mask-image:radial-gradient(ellipse at 50% 0%,#000 0%,transparent 60%);mask-image:radial-gradient(ellipse at 50% 0%,#000 0%,transparent 60%)}
a{color:inherit;text-decoration:none}

/* NAV */
.nav{position:relative;z-index:2}
.nav-in{max-width:var(--mw);margin:0 auto;padding:0 40px;height:72px;display:flex;align-items:center;justify-content:space-between;gap:16px;border-bottom:1px solid var(--brd)}
.nav-back{font-size:14.5px;color:var(--g300);transition:color .2s}
.nav-back:hover{color:var(--gold)}

/* HERO */
.sec{position:relative;z-index:1;padding:0 40px 80px;max-width:var(--mw);margin:0 auto}
.hero{text-align:center;padding:40px 0 34px}
.crown{display:inline-flex;align-items:center;gap:8px;padding:6px 14px 6px 8px;border-radius:999px;border:1px solid var(--brd2);background:rgba(232,196,104,.07);font-size:13px;color:var(--gold-2);font-weight:500;letter-spacing:.02em}
.crown i{width:20px;height:20px;border-radius:50%;display:grid;place-items:center;font-style:normal;font-size:11px;background:linear-gradient(135deg,var(--gold-2),var(--gold-3));color:#1a1406}
.hero-title{margin:18px 0 12px;font-size:clamp(40px,6vw,72px);font-weight:800;letter-spacing:-.045em;line-height:1}
.hero-title .mira{background:linear-gradient(180deg,var(--gold-2) 10%,var(--gold) 50%,var(--gold-3) 100%);-webkit-background-clip:text;background-clip:text;color:transparent;filter:drop-shadow(0 8px 30px rgba(232,196,104,.25))}
.hero-sub{max-width:760px;margin:0 auto;color:var(--g300);font-size:17px;font-weight:300;line-height:1.6}
.hero-sub b{color:var(--white);font-weight:500}
.hero-pills{display:flex;justify-content:center;gap:10px;margin-top:22px;flex-wrap:wrap}
.hero-pill{display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:999px;border:1px solid var(--brd);background:var(--card);font-size:13px;color:var(--g300)}
.hero-pill::before{content:"";width:7px;height:7px;border-radius:50%;background:var(--c);box-shadow:0 0 10px var(--c)}
.hero-pill b{color:var(--white);font-weight:600;font-family:var(--fm);font-size:12px;letter-spacing:.06em}

@include('partials.ask-mira.styles')

/* PANELS */
.chat-sidebar,.chat-card{background:linear-gradient(180deg,var(--card-h),var(--card));border:1px solid var(--brd);border-radius:22px;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px)}
.chat-sidebar{padding:20px;scrollbar-width:thin;scrollbar-color:#2a2a30 transparent}
.side-lbl{font-family:var(--fm);font-size:10.5px;letter-spacing:.16em;color:var(--g400)}

/* Your website card */
.site-box{display:flex;gap:12px;align-items:center;padding:12px;border-radius:16px;background:rgba(0,0,0,.35);border:1px solid var(--brd);font-size:12.5px;line-height:1.4}
.site-empty{color:var(--g300)}
.site-fav{width:38px;height:38px;border-radius:12px;display:grid;place-items:center;flex:none;background:linear-gradient(135deg,#1d3a8a,#6f9bff);color:#fff;font-weight:700;font-size:14px}
.site-meta{min-width:0}
.site-title{color:var(--white);font-weight:600;font-size:14px;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.site-url{display:flex;align-items:center;gap:6px;color:var(--g300);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.site-url::before{content:"";width:6px;height:6px;border-radius:50%;flex:none;background:var(--emerald);box-shadow:0 0 8px var(--emerald)}
.site-url:hover{color:var(--gold)}

/* Try asking: SEO / AEO / GEO group pills and question cards */
.starter-pill{display:inline-flex;align-items:center;gap:6px;max-width:100%;margin:14px 0 8px;padding:3px 10px 3px 3px;border-radius:999px;
  border:1px solid color-mix(in srgb,var(--pill-c) 35%,transparent);background:color-mix(in srgb,var(--pill-c) 10%,transparent)}
.side-lbl + .starter-pill{margin-top:0}
.starter-icon{width:18px;height:18px;border-radius:50%;background:var(--pill-c);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.starter-icon svg{width:10px;height:10px;fill:none;stroke:#0b0b0e;stroke-width:2.6;stroke-linecap:round;stroke-linejoin:round}
.starter-label{font-family:var(--fm);font-size:10px;font-weight:500;letter-spacing:1px;color:var(--pill-c);padding-right:6px;border-right:1px solid color-mix(in srgb,var(--pill-c) 35%,transparent)}
.starter-tagline{font-size:11px;font-weight:500;color:var(--white);opacity:.85;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.starter-seo{--pill-c:#6f9bff}
.starter-aeo{--pill-c:#f2a64a}
.starter-geo{--pill-c:#3fc495}
.starter-other{--pill-c:#e8c468}
#ask-mira .side-starter{display:flex;gap:8px;align-items:flex-start;background:rgba(255,255,255,.025);border:1px solid var(--brd);border-radius:14px;color:var(--g100);transition:all .2s ease}
#ask-mira .side-starter::before{content:"\2192";color:var(--g400);flex:none;transition:all .2s}
#ask-mira .side-starter:hover{border-color:var(--brd2);background:rgba(232,196,104,.06);color:var(--white);transform:translateX(3px)}
#ask-mira .side-starter:hover::before{color:var(--gold)}

/* Chat header: pill tab switcher + new-chat button */
.chat-tabs{justify-content:space-between;padding:14px 16px}
.tab-seg{display:inline-flex;padding:4px;border-radius:14px;background:rgba(0,0,0,.4);border:1px solid var(--brd);min-width:0}
.tab-seg .chat-tab-box{flex:none;background:none;border:0;padding:0;border-radius:10px}
.tab-seg .chat-tab-box.active{background:linear-gradient(180deg,#24211a,#18160f);box-shadow:inset 0 0 0 1px var(--brd2),0 4px 14px rgba(0,0,0,.4)}
.tab-seg .chat-tab{padding:9px 16px;color:var(--g300);font-weight:500;font-size:14px}
.tab-seg .chat-tab-box.active .chat-tab{color:var(--gold-2)}
.tab-seg .chat-tab-box:not(.active):hover .chat-tab{color:var(--white)}
.icon-btn{width:38px;height:38px;border-radius:12px;border:1px solid var(--brd);background:var(--card);display:grid;place-items:center;cursor:pointer;color:var(--g300);transition:all .2s;flex:none}
.icon-btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2}
.icon-btn:hover{color:var(--gold);border-color:var(--brd2)}

/* Messages: gold user bubbles, Mira replies with an "M" avatar */
.chat-messages{padding:26px 26px 12px;gap:18px}
.chat-msg.user{background:linear-gradient(135deg,var(--gold-2),var(--gold) 60%,var(--gold-3));color:#17120a;font-weight:500;border-radius:18px 18px 4px 18px;box-shadow:0 10px 30px rgba(232,196,104,.18)}
.chat-msg.bot{position:relative;margin-left:44px;max-width:calc(85% - 44px);background:rgba(255,255,255,.04);border:1px solid var(--brd);color:var(--g100);border-radius:4px 18px 18px 18px}
.chat-msg.bot::before{content:"M";position:absolute;left:-44px;top:0;width:32px;height:32px;border-radius:10px;display:grid;place-items:center;
  font-weight:800;font-size:14px;font-style:normal;color:#17120a;background:radial-gradient(circle at 30% 25%,var(--gold-2),var(--gold-3));box-shadow:0 0 0 1px var(--brd2),0 6px 18px rgba(232,196,104,.25)}
.chat-msg.bot a{color:var(--gold);text-decoration:none;border-bottom:1px dashed var(--brd2)}
.chat-msg.typing{color:var(--g300)}

/* Clickable suggested questions under a reply */
.chat-suggest{display:flex;flex-wrap:wrap;gap:8px;align-self:flex-start;max-width:calc(85% - 44px);margin:-6px 0 0 44px}
.suggest-chip{background:none;border:1px solid var(--brd);border-radius:999px;padding:7px 13px;color:var(--g300);font-family:var(--f1);font-size:13px;line-height:1.4;text-align:left;cursor:pointer;transition:all .2s}
.suggest-chip:hover{color:var(--gold-2);border-color:var(--brd2);background:rgba(232,196,104,.06)}

/* Composer */
.chat-card .chat-input-wrap{margin:12px 20px 6px;padding:8px 8px 8px 18px;border:1px solid var(--brd);border-radius:18px;background:rgba(0,0,0,.45);align-items:center;transition:border-color .2s,box-shadow .2s}
.chat-card .analyze-form .chat-input-wrap{margin:0}
.chat-card .chat-input-wrap:focus-within{border-color:var(--brd2);box-shadow:0 0 0 4px rgba(232,196,104,.08)}
.chat-card .chat-input{background:none;border:0;padding:10px 0;color:var(--white)}
.chat-card .chat-input::placeholder{color:var(--g400)}
/* Chrome paints autofilled fields light blue with dark text, overriding the
   page's colours. An inset shadow in the field's own colour covers it, and the
   long transition stops Chrome's background from showing through. */
.chat-card .chat-input:-webkit-autofill,
.lead-modal-field input:-webkit-autofill{
  -webkit-text-fill-color:var(--white);caret-color:var(--white);
  -webkit-box-shadow:0 0 0 1000px #0b0b0e inset;box-shadow:0 0 0 1000px #0b0b0e inset;
  transition:background-color 9999s ease-in-out 0s}
.chat-card .chat-input:-webkit-autofill:focus,
.lead-modal-field input:-webkit-autofill:focus{-webkit-box-shadow:0 0 0 1000px #0b0b0e inset;box-shadow:0 0 0 1000px #0b0b0e inset}
.chat-send{width:44px;height:44px;border-radius:13px;align-self:center;background:linear-gradient(135deg,var(--gold-2),var(--gold-3));box-shadow:0 6px 18px rgba(232,196,104,.3);transition:transform .15s}
.chat-send:hover{opacity:1;transform:translateY(-1px) scale(1.03)}
.chat-footer{border-top:0;padding:4px 20px 14px}
.chat-fine{font-size:12px;color:var(--g400)}
.mira-status{background:rgba(255,255,255,.05)}

/* Analyse tab, report and pop-ups */
.analysis-panel{border-radius:22px;background:linear-gradient(180deg,var(--card-h),var(--card))}
.a-modal-overlay,.lead-modal-overlay{background:rgba(0,0,0,.7)}
.a-modal,.lead-modal{background:#121216;border-color:var(--brd);box-shadow:0 20px 60px rgba(0,0,0,.6);color-scheme:dark;scrollbar-width:thin;scrollbar-color:#3a3a42 transparent}

@media(max-width:640px){
  .nav-in,.sec{padding-left:16px;padding-right:16px}
  .sec{padding-bottom:56px}
  .hero{padding:28px 0 24px}
  .hero-sub{font-size:15.5px}
  .chat-messages{padding:20px 14px 8px}
}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{transition:none!important}}
</style>
</head>
<body>

<nav class="nav"><div class="nav-in">
  <a href="{{ url('/') }}"><img src="{{ asset('images/xplatforms_logo.jpeg') }}" alt="X Platforms" style="height:32px;width:auto;display:block"></a>
  <a href="{{ url('/') }}" class="nav-back">&larr; Back to home</a>
</div></nav>

<section class="sec" id="ask-mira">
  <div class="hero">
    <span class="crown"><i>&#9733;</i> Premium AI Assistant</span>
    <h1 class="hero-title">Ask <span class="mira">Mira</span></h1>
    <p class="hero-sub">Paste your website URL, then ask anything about it. Answers focus on <b>AEO (Answer Engine Optimization)</b>, getting your site picked as the answer in featured snippets, "People also ask" and voice search, and cover <b>SEO and GEO</b> (AI chatbot visibility) too.</p>
    <div class="hero-pills">
      <span class="hero-pill" style="--c:#6f9bff"><b>SEO</b> Get found</span>
      <span class="hero-pill" style="--c:#f2a64a"><b>AEO</b> Be the answer</span>
      <span class="hero-pill" style="--c:#3fc495"><b>GEO</b> Get recommended by AI</span>
    </div>
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
          {{-- Questions come from askmirap_predefined_prompts (placement = sidebar),
               grouped by category. This list only holds each category's look and
               display order; a category without an entry here still shows, with
               a plain pill. --}}
          @php
            $groupDesign = [
              'seo' => ['label' => 'SEO', 'tagline' => 'Get found on Google',
                        'icon' => '<circle cx="11" cy="11" r="6"/><path d="M20 20l-4.5-4.5"/>'],
              'aeo' => ['label' => 'AEO', 'tagline' => 'Be the answer',
                        'icon' => '<path d="M21 12a8 8 0 01-11.6 7.1L4 20l1-4.6A8 8 0 1121 12z"/><path d="M9 12h6"/>'],
              'geo' => ['label' => 'GEO', 'tagline' => 'Get recommended by AI',
                        'icon' => '<path d="M10 3l1.8 4.7L16.5 9.5l-4.7 1.8L10 16l-1.8-4.7L3.5 9.5l4.7-1.8z"/><path d="M18 14l.9 2.1L21 17l-2.1.9L18 20l-.9-2.1L15 17l2.1-.9z"/>'],
            ];
            $categories = collect(array_keys($groupDesign))
              ->concat($sidebarPrompts->keys())
              ->unique()
              ->filter(fn ($c) => $sidebarPrompts->has($c));
          @endphp
          @foreach($categories as $category)
            @php
              $design = $groupDesign[$category] ?? ['label' => strtoupper($category), 'tagline' => null,
                        'icon' => '<circle cx="12" cy="12" r="4"/>'];
            @endphp
            <div class="starter-pill starter-{{ isset($groupDesign[$category]) ? $category : 'other' }}">
              <span class="starter-icon"><svg viewBox="0 0 24 24" aria-hidden="true">{!! $design['icon'] !!}</svg></span>
              <span class="starter-label" @unless($design['tagline']) style="border-right:none;padding-right:0" @endunless>{{ $design['label'] }}</span>
              @if($design['tagline'])
                <span class="starter-tagline">{{ $design['tagline'] }}</span>
              @endif
            </div>
            <div class="side-starters">
              @foreach($sidebarPrompts[$category] as $q)
                <button type="button" class="side-starter" onclick="askPremium({{ \Illuminate\Support\Js::from($q) }})">{{ $q }}</button>
              @endforeach
            </div>
          @endforeach
        </div>
      </aside>

      <div class="chat-card">
        <div class="chat-tabs">
          {{-- Tab boxes keep their ids: the shared script's switchChatTab() toggles .active on them. --}}
          <div class="tab-seg">
            <div class="chat-tab-box active" id="tabBoxAsk">
              <button type="button" class="chat-tab" id="tabBtnAsk" onclick="switchChatTab('ask')">
                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                Ask Mira Premium
              </button>
            </div>
            <div class="chat-tab-box" id="tabBoxAnalyze">
              <button type="button" class="chat-tab" id="tabBtnAnalyze" onclick="switchChatTab('analyze')">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                Analyse my website
              </button>
            </div>
          </div>
          <button type="button" class="icon-btn" onclick="resetPremium()" title="New chat" aria-label="New chat">
            <svg viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          </button>
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
            <span class="chat-fine">Mira can make mistakes. Check important changes before publishing.</span>
            <span class="mira-status {{ $premiumLive ? 'live' : 'basic' }}"><span class="status-dot"></span> Mira Premium</span>
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
            <span class="mira-status {{ $miraLive ? 'live' : 'basic' }}"><span class="status-dot"></span> Mira Premium</span>
          </div>
        </div>
      </div>


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
@include('partials.ask-mira.script', ['premium' => true])
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

// Shows `site` ({title, url}) in the sidebar's "Your website" card, or the empty
// state for null. chatReady=false (an Analyse-tab result) leaves the chat
// placeholder alone, since the chat hasn't loaded that site yet.
function setSite(site, chatReady){
  if(chatReady===undefined) chatReady=true;
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
  // Initials badge: first letters of the first two words of the site's title.
  const words=(site.title||'').replace(/[^\p{L}\p{N}\s]/gu,' ').trim().split(/\s+/).filter(Boolean);
  const initials=(words.length>1 ? words[0][0]+words[1][0] : (words[0]||'?').slice(0,2)).toUpperCase();
  const fav=document.createElement('div');fav.className='site-fav';fav.textContent=initials;
  const meta=document.createElement('div');meta.className='site-meta';
  const t=document.createElement('div');t.className='site-title';t.textContent=site.title;
  const u=document.createElement('a');u.className='site-url';u.href=site.url;u.target='_blank';u.rel='noopener noreferrer';
  u.textContent=site.url.replace(/^https?:\/\/(www\.)?/i,'').replace(/\/$/,'');
  meta.appendChild(t);meta.appendChild(u);
  box.appendChild(fav);box.appendChild(meta);
  if(chatReady) input.placeholder='Ask Mira anything about your website...';
}

// Called by the shared script after an "Analyse my website" report renders.
function onAnalysisReport(report){
  let title=report.title||'';
  if(!title){ try{ title=new URL(report.url).hostname.replace(/^www\./,''); }catch(e){ title=report.url; } }
  setSite({title:title, url:report.url}, false);
}

async function sendPremium(){
  const input=document.getElementById('chatInput'),msg=input.value.trim();
  if(!msg||premiumBusy) return;
  premiumBusy=true;
  input.value='';
  // Older unused suggestion buttons no longer fit the conversation.
  document.querySelectorAll('#chatMsgs .chat-suggest').forEach(function(row){ row.remove(); });
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
  document.getElementById('analyzeInput').value='';
  document.getElementById('analyzeStatus').textContent='';
  document.getElementById('analyzeStatus').classList.remove('err');
  switchChatTab('ask');
}
</script>
</body>
</html>
