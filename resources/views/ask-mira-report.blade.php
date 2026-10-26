{{-- Ask Mira report page (routes mira.report / mira.report.pending): one
     "Analyse my website" or "Compare with a competitor" result on its own
     page, drawn by partials.ask-mira.compare-script exactly as it used to be
     under the chat widget. $report is null while the result is still being
     prepared (the tab the widget opens on click), then the widget moves this
     tab to the stored report. $premium picks Mira Premium's look. --}}
@php
  $isComparison = ($report['type'] ?? null) === 'comparison';
  $heading = $report === null ? 'Preparing your report' : ($isComparison ? 'Competitor comparison' : 'Website report');
  $backUrl = $premium ? route('mira-premium') : url('/') . '#chatbot-slot';
  $shared  = (bool) ($report['shared'] ?? false);   // a Mira Premium "Share report" link
  $autoPrint = (bool) ($autoPrint ?? false);       // "Download PDF": open the print dialog once drawn
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>{{ $heading }} &ndash; Ask Mira{{ $premium ? ' Premium' : '' }}</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
@if($premium)
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
@else
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
@endif
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --page-bg:#fafafa;--text:#17181a;--muted:#6b6b70;--line:#e6e6ea;
  --accent:{{ $premium ? '#a8801f' : '#4f80f0' }};
  --font:{!! $premium ? "'Outfit',system-ui,sans-serif" : "'Inter','Segoe UI',system-ui,sans-serif" !!};
}
body{background:var(--page-bg);color:var(--text);font-family:var(--font);-webkit-font-smoothing:antialiased;min-height:100vh}
@if($premium)
body::before{content:"";position:fixed;inset:0;pointer-events:none;z-index:0;
  background:radial-gradient(900px 420px at 50% -140px,rgba(232,196,104,.22),transparent 70%)}
@endif
a{color:inherit;text-decoration:none}
.wrap{position:relative;z-index:1;max-width:1160px;margin:0 auto;padding:0 24px 64px}
.nav{display:flex;align-items:center;justify-content:space-between;gap:16px;height:72px;border-bottom:1px solid var(--line);margin-bottom:28px}
.brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:19px;letter-spacing:-.02em}
.brand svg{width:26px;height:26px;display:block}
.brand small{font-size:13px;font-weight:600;color:var(--accent);padding:3px 10px;border-radius:999px;
  background:{{ $premium ? '#fdf6e3' : '#eef3ff' }};border:1px solid {{ $premium ? '#e2c77e' : '#d6e2fd' }};letter-spacing:0}
.nav-actions{display:flex;align-items:center;gap:10px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:999px;font:inherit;font-size:14px;font-weight:500;
  border:1px solid var(--line);background:#fff;color:var(--text);cursor:pointer;transition:border-color .2s,color .2s}
.btn:hover{border-color:var(--accent);color:var(--accent)}
.head{margin-bottom:22px}
.head h1{font-size:clamp(26px,4vw,36px);font-weight:700;letter-spacing:-.03em}
.head p{margin-top:6px;color:var(--muted);font-size:15px}
#miraComparePanel{border-radius:24px;padding:24px;border:1px solid var(--line);box-shadow:0 20px 50px -24px rgba(23,24,26,.18)}
@if($premium)
/* Mira Premium's gold accents on the shared report. */
#miraComparePanel{--cx-accent:#a8801f;--cx-accent-soft:#fbf4e2;--cx-accent-line:#ecd9a6;--cx-on-accent:#fff;font-family:var(--font)}
@endif
@include('partials.ask-mira.compare-styles')

/* Waiting for the result. */
.pending{display:flex;flex-direction:column;align-items:center;text-align:center;gap:14px;padding:80px 24px;background:#fff;
  border:1px solid var(--line);border-radius:24px;box-shadow:0 20px 50px -24px rgba(23,24,26,.18)}
.spinner{width:44px;height:44px;border-radius:50%;border:4px solid {{ $premium ? '#f1e4bf' : '#dfe8fb' }};border-top-color:var(--accent);animation:spin .9s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.pending h2{font-size:20px;font-weight:700}
.pending p{color:var(--muted);font-size:15px;max-width:420px;line-height:1.55}
@media print{.nav-actions,body::before{display:none}#miraComparePanel{box-shadow:none;border:0;padding:0}
  .cx-panel,.cx-hero,.cx-verdict{break-inside:avoid}}
@media (prefers-reduced-motion:reduce){.spinner{animation-duration:2.5s}}
</style>
</head>
<body>
<div class="wrap">
  <nav class="nav">
    <a href="{{ url('/') }}" class="brand">
      <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke-width="4.6" stroke-linecap="round">
        <path d="M4 4l5 5" stroke="#34a853"/><path d="M20 4l-5 5" stroke="#4285f4"/>
        <path d="M4 20l5-5" stroke="#fbbc05"/><path d="M20 20l-5-5" stroke="#ea4335"/>
      </svg>
      XPlatforms
      <small>{{ $premium ? 'Ask Mira Premium' : 'Ask Mira' }}</small>
    </a>
    <div class="nav-actions">
      @if($report !== null)
        <button type="button" class="btn" onclick="window.print()">Download PDF</button>
      @endif
      <a href="{{ $backUrl }}" class="btn">{!! $shared ? 'Try Mira Premium &rarr;' : '&larr; Back to Ask Mira' !!}</a>
    </div>
  </nav>

  @if($report === null)
    <div class="pending" role="status" aria-live="polite">
      <div class="spinner" aria-hidden="true"></div>
      <h2>Mira is preparing your report…</h2>
      <p>This can take up to a minute. Keep this tab open — your report will appear here as soon as it's ready.</p>
    </div>
  @else
    <div class="head">
      <h1>{{ $heading }}</h1>
      <p>
        @if($isComparison)
          {{ $report['data']['you']['url'] ?? '' }} vs {{ $report['data']['competitor']['url'] ?? '' }}
        @else
          {{ $report['data']['url'] ?? '' }}
        @endif
      </p>
    </div>
    <div class="mira-compare-panel" id="miraComparePanel" hidden></div>
  @endif
</div>

@if($report !== null)
<script>
// This page shows the stored result itself, so the renderers draw it here
// instead of opening another tab.
window.ASK_MIRA_REPORT_PAGE = true;
@include('partials.ask-mira.compare-script')

(function () {
  var data = @json($report['data']);
  if (@json($isComparison)) miraRenderReport(data);
  else miraRenderSiteReport(data);
  window.scrollTo(0, 0);
  // "Download PDF" opens this page with ?print=1: print once the report (and its fonts) are drawn.
  if (@json($autoPrint)) window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 700); });
})();
</script>
@endif
</body>
</html>
