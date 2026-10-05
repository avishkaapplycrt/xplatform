{{-- Free Ask Mira chat on the landing page. Premium Mira has its own chat
     in mira-premium.blade.php and shares only partials.ask-mira.styles. --}}
<!-- ASK MIRA -->
<section class="sec" id="ask-mira">
  <div class="stop rv" style="text-align:center"><div class="stag" style="justify-content:center">AI Assistant</div><h2 class="sh" style="margin:0 auto 16px">Ask <span>Mira</span></h2><p class="ss" style="margin:0 auto">Pricing, features, industries we serve, or paste your website for a real analysis — ask below.</p></div>

  <div class="chat-page-wrap rv">
    <div class="chat-layout">

      <aside class="chat-sidebar">
        <a class="side-premium" href="{{ route('mira-premium') }}">&#9733; Try Premium Mira</a>
        <div>
          <div class="side-lbl">Your industry</div>
          <select id="industrySelect" class="side-select" onchange="renderStarterQuestions(this.value)">
            <option value="">All industries</option>
            @foreach($industries as $industry)
              <option value="{{ $industry->id }}">{{ $industry->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <div class="side-lbl">Try asking</div>
          <div class="side-starters" id="sideStarters">
            @foreach(($chatQuestionsByIndustry['all'] ?? collect(["What does X Platforms do?", "How much does it cost?"]))->take(6) as $q)
              <button type="button" class="side-starter" onclick="askSuggested({{ \Illuminate\Support\Js::from($q) }})">{{ $q }}</button>
            @endforeach
          </div>
        </div>
      </aside>

      <div class="chat-card">
        <div class="chat-tabs">
          <div class="chat-tab-box active" id="tabBoxAsk">
            <button type="button" class="chat-tab" id="tabBtnAsk" onclick="switchChatTab('ask')">
              <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
              Ask Mira
            </button>
            <button type="button" class="chat-reset" onclick="resetChat()" title="New chat" aria-label="New chat">
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
            <div class="chat-msg bot">Hi, I'm Mira. How can I help you today?</div>
          </div>
          <div class="chat-input-wrap">
            <textarea class="chat-input" id="chatInput" placeholder="Ask Mira..." rows="1" onkeydown="if(event.key==='Enter'&amp;&amp;!event.shiftKey){event.preventDefault();sendChat()}"></textarea>
            <button class="chat-send" onclick="sendChat()"><svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg></button>
          </div>
          <div class="chat-footer">
            <span class="mira-status {{ $miraLive ? 'live' : 'basic' }}"><span class="status-dot"></span> Mira &middot; {{ $miraLive ? 'Live' : 'Basic' }}</span>
          </div>
        </div>

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
