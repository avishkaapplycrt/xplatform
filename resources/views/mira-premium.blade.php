<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Mira Premium &ndash; AI search visibility for your website | X Platforms</title>
<meta name="description" content="Mira Premium audits your website for Google and AI search, tracks how often AI assistants recommend you against your competitors, and tells you exactly what to fix.">
<link rel="canonical" href="{{ url('/mira-premium') }}">
<meta property="og:type" content="website">
<meta property="og:title" content="Mira Premium — be the answer in Google and AI search">
<meta property="og:description" content="Site audits, competitor gaps and AI visibility tracking with fix-by-fix guidance.">
<meta property="og:url" content="{{ url('/mira-premium') }}">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
{{-- Entity data for search engines and AI assistants (what the product is, who makes it). --}}
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'SoftwareApplication',
  'name' => 'Mira Premium',
  'applicationCategory' => 'BusinessApplication',
  'operatingSystem' => 'Web',
  'url' => url('/mira-premium'),
  'description' => 'Website audits for Google and AI search, competitor gap analysis and AI visibility tracking.',
  'publisher' => ['@type' => 'Organization', 'name' => 'X Platforms', 'url' => url('/')],
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
<style>
/* Shared Ask Mira styles (modals, report renderer) first; the workspace's own styles override them. */
@include('partials.ask-mira.styles')
@include('partials.ask-mira.compare-styles')
@include('partials.mira-premium.styles')
.analysis-panel{display:none!important}
</style>
</head>
<body>

{{-- Server state for the first paint (subscription, usage, projects) — the same
     payload as GET mira-premium/api/me, inlined so no extra request is needed. --}}
<script type="application/json" id="miraBoot">{!! json_encode($boot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

<header class="topbar">
  <div class="topbar-in">
    <a href="{{ url('/') }}" class="brand" aria-label="X Platforms home">
      <img src="{{ asset('images/xplatforms_logo.jpeg') }}" alt="X Platforms">
      <span class="brand-sep"></span>
      <span class="brand-name">Mira<b>Premium</b></span>
    </a>

    <div class="project-switch" id="projectSwitch" hidden>
      <button type="button" class="project-btn" id="projectBtn" aria-haspopup="menu" aria-expanded="false">
        <span class="project-fav" id="projectFav">—</span>
        <span class="pn" id="projectName">Choose project</span>
        <svg viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
      </button>
      <div class="menu" id="projectMenu" role="menu" hidden></div>
    </div>

    <div class="topbar-right">
      <div class="usage-pill" id="usagePill" title="Site scans used this period" hidden>
        <span id="usagePillText">Scans</span>
        <span class="bar"><i id="usagePillBar" style="width:0"></i></span>
      </div>
      <span class="badge" id="planBadge">Preview</span>
      <a class="btn btn-ghost btn-sm" id="manageBtn" hidden>Manage plan</a>
      <a class="btn btn-ghost btn-sm" id="signInBtn" href="{{ route('client.login') }}" hidden>Sign in</a>
      <a class="btn btn-premium btn-sm mira-ev-upgrade_click" data-ev-from="topbar" id="upgradeBtn" href="{{ config('mira_premium.upgrade_url') }}">
        <svg viewBox="0 0 24 24"><path d="M12 2l2.9 6.6 7.1.6-5.4 4.7 1.6 7.1L12 17.3 5.8 21l1.6-7.1L2 9.2l7.1-.6z"/></svg>
        Upgrade
      </a>
    </div>
  </div>
</header>

<main class="wrap">
  <section class="hero">
    <div>
      <span class="eyebrow">AI search visibility</span>
      <h1>Be the answer in Google <em>and</em> AI search.</h1>
      <p>Audit any page against 13 categories of checks, see exactly where competitors beat you, and track how often ChatGPT-style assistants recommend your brand — with the fix for every gap.</p>
    </div>
    <div class="hero-proof">
      <span><svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>Real checks, no estimates</span>
      <span><svg viewBox="0 0 24 24"><path d="M3 12h4l3 8 4-16 3 8h4"/></svg>Scores tracked over time</span>
      <span><svg viewBox="0 0 24 24"><path d="M12 3l1.8 4.7 4.7 1.8-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8z"/></svg>AI visibility tracking</span>
    </div>
  </section>

  {{-- Scan bar: the two-phase protocol's phase 1 (POST scans → signed handle). --}}
  <form class="scanbar" id="scanForm" autocomplete="off" novalidate>
    <label class="field">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 010 18M12 3a14 14 0 000 18"/></svg>
      <span class="sr">Your website</span>
      <input id="scanUrl" name="url" type="text" inputmode="url" placeholder="yourwebsite.com" required>
    </label>
    <label class="field" id="compField" hidden>
      <span class="tag">vs</span>
      <span class="sr">Competitor website</span>
      <input id="scanComp" name="competitor_url" type="text" inputmode="url" placeholder="competitor.com">
    </label>
    <button type="button" class="btn btn-secondary" id="compToggle" aria-pressed="false">
      <svg viewBox="0 0 24 24"><path d="M7 4L3 8l4 4M3 8h13M17 20l4-4-4-4M21 16H8"/></svg>
      <span id="compToggleText">Compare</span>
    </button>
    <button type="submit" class="btn btn-primary btn-lg" id="scanBtn">
      <svg class="spark" viewBox="0 0 24 24"><path d="M12 3l1.8 4.7 4.7 1.8-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8z"/></svg>
      Run audit
    </button>
  </form>
  <div class="scan-note" id="scanNote" aria-live="polite">
    <span id="scanQuota"></span>
    <span id="scanMsg"></span>
    <div id="turnstileSlot"></div>
  </div>

  <nav class="tabs" role="tablist" aria-label="Workspace">
    <button class="tab" role="tab" data-tab="overview" aria-selected="true">Overview</button>
    <button class="tab" role="tab" data-tab="organic" aria-selected="false">Organic search <span class="badge badge-lock" data-lock><svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg></span></button>
    <button class="tab" role="tab" data-tab="backlinks" aria-selected="false">Backlinks <span class="badge badge-lock" data-lock><svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg></span></button>
    <button class="tab" role="tab" data-tab="audit" aria-selected="false">Site audit</button>
    <button class="tab" role="tab" data-tab="visibility" aria-selected="false">AI visibility <span class="badge badge-lock" data-lock><svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg></span></button>
    <button class="tab" role="tab" data-tab="chat" aria-selected="false">Ask Mira</button>
    <button class="tab" role="tab" data-tab="history" aria-selected="false">History <span class="badge badge-lock" data-lock><svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg></span></button>
  </nav>

  {{-- ============ Overview ============ --}}
  <section class="panel" id="tab-overview" role="tabpanel">
    {{-- Site overview: four data panels for the selected window vs the previous one
         (GET projects/{id}/panels/{ai|authority|search|traffic}). --}}
    <div class="ov-head">
      <div>
        <h2 class="ov-title">Site overview <span class="ov-domain" id="ovDomain"></span></h2>
        <span class="sub" id="ovPeriodLabel">Last 30 days vs previous 30</span>
      </div>
      <div class="seg" id="periodSeg" role="radiogroup" aria-label="Comparison period">
        <button type="button" role="radio" data-period="7d">7D</button>
        <button type="button" role="radio" data-period="30d" aria-checked="true">30D</button>
        <button type="button" role="radio" data-period="90d">90D</button>
        <button type="button" role="radio" data-period="12m">12M</button>
      </div>
    </div>
    <div id="ovBody">
      <div class="ov-grid">
        <div class="card pnl" id="pnl-ai"></div>
        <div class="card pnl" id="pnl-authority"></div>
        <div class="card pnl" id="pnl-search"></div>
      </div>
      <div class="card pnl pnl-wide" id="pnl-traffic"></div>
      <div class="card pnl pnl-wide" id="pnl-keywords" hidden></div>
      <div class="card pnl pnl-wide" id="pnl-performance"></div>
      <div class="card pnl pnl-wide" id="pnl-positions"></div>
    </div>

    <h2 class="section-h">Site audit health</h2>
    <div class="grid kpis" id="kpis"></div>
    <div class="split">
      <div class="card">
        <div class="card-hd"><h3>Next best actions</h3><span class="sub" id="actionsSub"></span></div>
        <div class="card-bd" id="actions"></div>
      </div>
      <div class="card">
        <div class="card-hd"><h3>How Google sees the site</h3><span class="sub">Score per pipeline stage</span></div>
        <div class="card-bd" id="stages"></div>
      </div>
    </div>
  </section>

  {{-- ============ Organic search (Site Explorer: insights, countries, pages, competitors, paid) ============ --}}
  <section class="panel" id="tab-organic" role="tabpanel" hidden>
    <div id="orgBody"></div>
  </section>

  {{-- ============ Backlinks (profile, new/lost, authority distribution, referring domains, anchors) ============ --}}
  <section class="panel" id="tab-backlinks" role="tabpanel" hidden>
    <div id="blBody"></div>
  </section>

  {{-- ============ Site audit (shared .cx report renderer) ============ --}}
  <section class="panel" id="tab-audit" role="tabpanel" hidden>
    <div id="auditState"></div>
    <div id="auditWrap" hidden>
      <div class="report-hd">
        <h2 id="auditTitle">Site audit</h2>
        <div class="meta" id="auditMeta"></div>
      </div>
      <div class="upsell-strip" id="auditUpsell" hidden>
        <p><b>Preview report.</b> Mira Premium adds the exact fix for every failing check, written for your site, plus score tracking.</p>
        <a class="btn btn-premium btn-sm mira-ev-upgrade_click" data-ev-from="audit" href="{{ config('mira_premium.upgrade_url') }}">Unlock fixes</a>
      </div>
      {{-- miraRenderSiteReport / miraRenderComparison draw here. --}}
      <div class="mira-compare-panel" id="miraComparePanel" hidden></div>
    </div>
    {{-- The shared renderer clears this older panel; kept hidden. --}}
    <div class="analysis-panel" id="analysisPanel"><div id="analysisUrl"></div><div id="analysisPieGrid"></div></div>
  </section>

  {{-- ============ AI visibility ============ --}}
  <section class="panel" id="tab-visibility" role="tabpanel" hidden>
    <div id="visBody"></div>
  </section>

  {{-- ============ Ask Mira ============ --}}
  <section class="panel" id="tab-chat" role="tabpanel" hidden>
    <div class="card chat">
      <aside class="chat-side">
        <div>
          <div class="side-h">Your website</div>
          <div class="site-card" id="siteBox"><span class="muted">No website yet. Paste your URL in the chat to start.</span></div>
        </div>
        <div>
          <div class="side-h">Try asking</div>
          @php
            $groupDesign = [
              'seo' => ['label' => 'SEO', 'tagline' => 'Get found on Google', 'color' => '#3f6fd8'],
              'aeo' => ['label' => 'AEO', 'tagline' => 'Be the answer', 'color' => '#b7791f'],
              'geo' => ['label' => 'GEO', 'tagline' => 'Get recommended by AI', 'color' => '#1f8a5b'],
            ];
            $categories = collect(array_keys($groupDesign))->concat($sidebarPrompts->keys())->unique()->filter(fn ($c) => $sidebarPrompts->has($c));
          @endphp
          @forelse($categories as $category)
            @php $d = $groupDesign[$category] ?? ['label' => strtoupper($category), 'tagline' => null, 'color' => '#a8801f']; @endphp
            <div class="starter-group" style="--sc:{{ $d['color'] }}">
              <div class="starter-tag">{{ $d['label'] }} @if($d['tagline'])<span class="muted" style="font-family:var(--f-ui);letter-spacing:0;font-weight:500"> · {{ $d['tagline'] }}</span>@endif</div>
              @foreach($sidebarPrompts[$category] as $q)
                <button type="button" class="starter" data-ask="{{ $q }}">{{ $q }}</button>
              @endforeach
            </div>
          @empty
            <p class="muted" style="font-size:13px">Ask about your site's SEO, AEO (featured snippets, People also ask) or GEO (AI assistants).</p>
          @endforelse
        </div>
      </aside>
      <div class="chat-main">
        <div class="chat-hd">
          <span class="t"><span class="mira-mark">M</span> Ask Mira <span class="badge {{ $premiumLive ? 'badge-good' : 'badge-warn' }}">{{ $premiumLive ? 'Live' : 'Offline' }}</span></span>
          <button type="button" class="btn btn-ghost btn-sm" id="chatReset"><svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>New chat</button>
        </div>
        <div id="chatMsgs"></div>
        <form class="composer" id="chatForm">
          <textarea id="chatInput" rows="1" placeholder="Paste your website URL to start" aria-label="Message Mira"></textarea>
          <button type="submit" class="btn btn-primary" id="chatSend" aria-label="Send"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
        </form>
        <div class="chat-foot"><span>Mira can make mistakes. Check important changes before publishing.</span><span id="chatQuota"></span></div>
      </div>
    </div>
  </section>

  {{-- ============ History ============ --}}
  <section class="panel" id="tab-history" role="tabpanel" hidden>
    <div id="historyBody"></div>
  </section>
</main>

<footer class="foot">
  <span>&copy; {{ date('Y') }} X Platforms · Mira Premium</span>
  <span>AI visibility figures are modeled estimates from sampled prompts, not measured audience reach.</span>
</footer>

<div class="toasts" id="toasts" aria-live="polite"></div>

{{-- Shared Ask Mira modals: category pop-up (report) and lead form. --}}
<div class="a-modal-overlay" id="analysisModalOverlay" onclick="if(event.target===this) closeAnalysisModal()">
  <div class="a-modal">
    <button type="button" class="a-modal-close" onclick="closeAnalysisModal()" aria-label="Close">&times;</button>
    <div class="a-modal-header">
      <div class="a-modal-pct" id="modalPct"></div>
      <div><div class="a-modal-title" id="modalTitle"></div><div class="a-modal-desc" id="modalDesc"></div></div>
    </div>
    <div class="a-modal-checks" id="modalChecks"></div>
  </div>
</div>
<div class="lead-modal-overlay" id="leadModalOverlay" onclick="if(event.target===this) closeLeadModal()">
  <div class="lead-modal">
    <button type="button" class="lead-modal-close" onclick="closeLeadModal()" aria-label="Close">&times;</button>
    <div class="lead-modal-title">Before we analyze your site</div>
    <p class="lead-modal-sub">Enter your details and we'll run the check right after.</p>
    <div class="lead-modal-field"><label for="leadName">Name</label><input type="text" id="leadName" placeholder="Your name"></div>
    <div class="lead-modal-field"><label for="leadEmail">Email</label><input type="email" id="leadEmail" placeholder="you@company.com"></div>
    <p class="lead-modal-error" id="leadModalError"></p>
    <button type="button" class="lead-modal-submit" id="leadModalSubmit" onclick="submitLeadAndAnalyze()">Continue</button>
  </div>
</div>
{{-- Ids the shared script expects on load. --}}
<input type="hidden" id="analyzeInput"><p id="analyzeStatus" hidden></p>

<script>
@include('partials.ask-mira.script', ['premium' => true])
window.MIRA_STAGE_DASHBOARD = true;
@include('partials.ask-mira.compare-script')
</script>
<script>
@include('partials.mira-premium.app-script')
</script>
</body>
</html>
