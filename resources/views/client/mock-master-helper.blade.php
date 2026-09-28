{{-- resources/views/client/mock-master-helper.blade.php --}}
@extends('layouts.platform')

@section('title', 'Mock Master Helper')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
@endpush

@section('content')

@php
$client     = auth('client')->user();
$clientName = $client?->company_name ?? 'Acme Retail';
$initials   = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $clientName), 0, 2))));

// Real data (mkStudents/mkKpis/mkSegments/mkInsights/slProspects/slClose/
// chAtRisk/chWatchlist/chRootCauses) is computed by MockMasterDataService
// and passed in from the route, querying only the mm_* tables. A/B test
// tabs stay an honest "no data source yet" placeholder — there is no A/B
// test table in the mm_* schema, so nothing is fabricated there. Scripts/
// objections/offers below are advisory copy (not data claims), kept static.

// ── Marketing ──────────────────────────────────────────────────────────
$mkSteps = [
    ['key' => 'campaign',    'label' => 'Campaign'],
    ['key' => 'performance', 'label' => 'Performance'],
    ['key' => 'audience',    'label' => 'Audience'],
    ['key' => 'insights',    'label' => 'Insights'],
    ['key' => 'abtest',      'label' => 'A/B test'],
];

// $mkPrompts is passed in from the route — sourced from agents_pre_defined_prompts
// where is_mock_master = 1 (see routes/web.php).

// ── Sales ──────────────────────────────────────────────────────────────
$slSteps = [
    ['key' => 'today',      'label' => 'Today'],
    ['key' => 'accounts',   'label' => 'Accounts'],
    ['key' => 'scripts',    'label' => 'Scripts'],
    ['key' => 'objections', 'label' => 'Objections'],
    ['key' => 'close',      'label' => 'Close & grow'],
];

$slScripts = [
    ['label' => 'Call script',     'text' => 'Open by asking which exam they\'re preparing for and their target date — then map our mock test package directly to that deadline.'],
    ['label' => 'Email script',    'text' => 'Lead with a free diagnostic mock test offer, then follow up with their score and a recommended package 48 hours later.'],
    ['label' => 'WhatsApp script', 'text' => 'Short and direct: "Hi {name}, saw you started a free mock test — want help picking the right package for your target band?"'],
];

$slObjections = [
    ['q' => '"It\'s too expensive."',            'a' => 'Break the package cost down per mock test versus a private tutor session — usually 5-10x cheaper per attempt.'],
    ['q' => '"I want to try free resources first."', 'a' => 'Offer one free diagnostic mock test with a real band score, then show how paid packages unlock full feedback.'],
    ['q' => '"My exam date isn\'t confirmed yet."',  'a' => 'Recommend the flexible/monthly package instead of the fixed-term one so they aren\'t locked in early.'],
];

// $slPrompts is passed in from the route — sourced from agents_pre_defined_prompts
// where is_mock_master = 1 (see routes/web.php).

// ── Customer Retention ────────────────────────────────────────────────
$chSteps = [
    ['key' => 'savefirst', 'label' => 'Save first'],
    ['key' => 'rootcause', 'label' => 'Root cause'],
    ['key' => 'offers',    'label' => 'Offers'],
    ['key' => 'watchlist', 'label' => 'Watchlist'],
    ['key' => 'abtest',    'label' => 'A/B test'],
];

$chOffers = [
    ['name' => 'One free extra mock test',        'meta' => 'For students inactive 10-20 days'],
    ['name' => '15% renewal discount',              'meta' => 'For high-value students at risk'],
    ['name' => 'Free 15-min coaching call',          'meta' => 'For students with declining scores'],
];

// $chPrompts is passed in from the route — sourced from agents_pre_defined_prompts
// where is_mock_master = 1 (see routes/web.php).

$agents = [
    ['key' => 'mk', 'letter' => 'M', 'label' => 'Marketing'],
    ['key' => 'sl', 'letter' => 'S', 'label' => 'Sales'],
    ['key' => 'ch', 'letter' => 'R', 'label' => 'Customer Retention'],
];
@endphp

<div class="flex flex-col h-full overflow-hidden bg-gray-50">

    {{-- Page Header --}}
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Mock Master Helper</h1>
            <p class="text-xs text-gray-400 mt-0.5">Guided playbook for your Mock Master student data</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="mmSyncData(this)" id="mmSyncBtn" title="Re-fetch the latest Mock Master data"
               class="flex items-center gap-2 px-3 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors text-xs font-medium">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-2.64-6.36M21 4v6h-6"/></svg>
                <span>Sync Data</span>
            </button>
            <button type="button" onclick="toggleSidebarCollapse()" id="mmFullBtn" title="Collapse sidebar"
               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
            </button>
            <a href="{{ route('client.dashboard') }}"
               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
               title="Dashboard">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>

            <div class="relative" id="mmAvatarWrap">
                <button onclick="var d=document.getElementById('mmDropdown');d.style.display=d.style.display==='block'?'none':'block'"
                        class="w-8 h-8 rounded-full bg-cyan-500 flex items-center justify-center text-white text-xs font-bold hover:bg-cyan-600 transition-colors">
                    {{ $initials ?: 'JD' }}
                </button>
                <div id="mmDropdown" class="hidden absolute right-0 top-10 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                    <div class="px-4 py-2 border-b border-gray-50">
                        <p class="text-xs font-semibold text-gray-900 truncate">{{ $clientName }}</p>
                        <p class="text-[10px] text-gray-400">Client Account</p>
                    </div>
                    <a href="{{ route('client.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-600 hover:bg-gray-50 transition-colors">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile Settings
                    </a>
                    <form method="POST" action="{{ route('client.logout') }}" class="border-t border-gray-50">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-red-500 hover:bg-red-50 transition-colors text-left">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Helper Interface Card --}}
    <div class="flex-1 overflow-hidden p-6">
    <div id="bhRoot" class="h-full flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" data-agent="mk">

        {{-- Selector bar: agent tabs --}}
        <div class="bar">
            <div class="atabs">
                @foreach($agents as $i => $a)
                <button type="button" class="atab {{ $i === 0 ? 'on' : '' }}" id="mmAgentTab-{{ $a['key'] }}" onclick="mmSetAgent('{{ $a['key'] }}')">
                    <span class="amono">{{ $a['letter'] }}</span><span class="a2">{{ $a['label'] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- MARKETING --}}
        <div class="dash on" id="mmDash-mk" data-dash="mk">
            <aside class="dash-left">
                <div class="left-resize" role="separator" aria-orientation="vertical" aria-label="Resize steps panel (arrow keys)" tabindex="0" title="Drag to resize — drag left to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmLeft('restore')" title="Expand steps" aria-label="Expand steps panel">&raquo;</button>
                    <span class="col-rail-label">Steps</span>
                </div>
                <div class="g-body">
                    @foreach($mkSteps as $i => $step)
                    <div class="flowst {{ $i === 0 ? 'cur' : '' }}" data-step="mk-{{ $step['key'] }}" onclick="mmSelectStep('mk','{{ $step['key'] }}')">
                        <div class="flowst-dot">{{ $i + 1 }}</div>
                        <div class="flowst-t">{{ $step['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </aside>
            <div class="dash-main">
                <div class="dash-vtabs">
                    @foreach($mkSteps as $i => $step)
                    <button type="button" class="dvt {{ $i === 0 ? 'on' : '' }}" data-tab="mk-{{ $step['key'] }}" onclick="mmSelectStep('mk','{{ $step['key'] }}')">{{ strtoupper($step['label']) }}</button>
                    @endforeach
                </div>
                <div class="dash-view">

                    <div class="mm-panel" data-panel="mk-campaign">
                        <div class="stack-intro">
                            <div class="si-h">WHAT YOU'RE LOOKING AT</div>
                            <div class="si-p">Your real renewal-ready students, each with the outreach call that matters most for them: whether to lead with <b>proof</b> or an <b>offer</b>, based on their own real trust score — not the pool average. Sorted by package value, biggest first.</div>
                        </div>
                        <table class="dtbl">
                            <thead><tr><th>Student</th><th>Package value</th><th>Stage</th><th>Readiness</th><th>Trust</th><th>Approach</th><th>Last active</th></tr></thead>
                            <tbody>
                                @forelse($mkStudents as $s)
                                <tr>
                                    <td class="acctn">{{ $s['name'] }} <span style="color:var(--g3);font-weight:400">({{ $s['sub'] }})</span></td>
                                    <td>{{ $s['value'] }}</td>
                                    <td>{{ $s['stage'] }}</td>
                                    <td>{{ $s['readiness'] }}</td>
                                    <td>{{ $s['trust'] }}</td>
                                    <td>@if($s['approach'] === 'Offer-led')<span style="color:#0e7a35;font-weight:600">Offer-led</span>@else<span style="color:var(--warn);font-weight:600">Proof-led</span>@endif</td>
                                    <td>{{ $s['lastActive'] }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="7" style="color:var(--g3);padding:20px">No renewal-ready students found right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mm-panel" data-panel="mk-performance" style="display:none">
                        <div class="stack-intro"><div class="si-h">PLATFORM PERFORMANCE</div><div class="si-p">Headline numbers across your Mock Master student base.</div></div>
                        <div class="mg-grid">
                            @foreach($mkKpis as $k)
                            <div class="mg-cell"><div class="mg-h">{{ $k['label'] }}</div><div class="mg-kpi">{{ $k['value'] }} <small>{{ $k['sub'] }}</small></div></div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mm-panel" data-panel="mk-audience" style="display:none">
                        <div class="stack-intro"><div class="si-h">STUDENT SEGMENTS</div><div class="si-p">Grouped by behavior and lifecycle stage.</div></div>
                        @forelse($mkSegments as $seg)
                        <div class="act"><div><div class="act-t">{{ $seg['name'] }}</div><div class="act-d">{{ $seg['meta'] }}</div></div></div>
                        @empty
                        <div class="act"><div class="act-t" style="color:var(--g3)">No segment data available.</div></div>
                        @endforelse
                    </div>

                    <div class="mm-panel" data-panel="mk-insights" style="display:none">
                        <div class="stack-intro"><div class="si-h">KEY INSIGHTS</div><div class="si-p">Patterns worth acting on.</div></div>
                        @forelse($mkInsights as $ins)
                        <div class="act"><div class="act-t">{{ $ins }}</div></div>
                        @empty
                        <div class="act"><div class="act-t" style="color:var(--g3)">Not enough data yet to compute insights.</div></div>
                        @endforelse
                    </div>

                    <div class="mm-panel" data-panel="mk-abtest" style="display:none">
                        <div class="stack-intro">
                            <div class="si-h">A/B TESTS</div>
                            <div class="si-p">Not available yet — there is no experiment/results table in the Mock Master data source, so no numbers are shown here rather than inventing them.</div>
                        </div>
                    </div>

                </div>
            </div>
            <aside class="dash-mira">
                <div class="mira-resize" role="separator" aria-orientation="vertical" aria-label="Resize helper panel (arrow keys)" tabindex="0" title="Drag to resize — drag right to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-hd">
                    <span class="dm-dot"></span>
                    <div><div class="dm-t">Marketing helper</div><div class="dm-s">ENGINE + AI · GROUNDED IN LIVE DATA</div></div>
                    <span class="dm-ready">Ready</span>
                    <div class="mira-tools">
                        <button type="button" class="mira-btn" onclick="mmMira('min')" title="Minimise" aria-label="Minimise helper panel">&minus;</button>
                        <button type="button" class="mira-btn" data-act="max" onclick="mmMira('max')" title="Maximise" aria-label="Maximise helper panel">&#9974;</button>
                    </div>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmMira('restore')" title="Expand helper" aria-label="Expand helper panel">&laquo;</button>
                    <span class="col-rail-label">Helper</span>
                </div>
                <div class="dm-chat" id="mmChat-mk"></div>
                <div class="row-resize" role="separator" aria-orientation="horizontal" aria-label="Resize suggestions panel (arrow keys)" tabindex="0" title="Drag to resize — drag down to hide">
                    <span class="row-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-quick-hd">
                    <span id="mmQuickHd-mk">ASK MIRA · CAMPAIGN</span>
                    <button type="button" class="dm-quick-min" onclick="mmCollapseQuick()" title="Hide suggestions" aria-label="Hide suggestions">&#9660;</button>
                </div>
                <div class="dm-quick" id="mmQuick-mk">
                    @foreach($mkPrompts as $p)
                    <button type="button" class="qk" onclick="mmAsk('mk', {{ Js::from($p) }})">{{ $p }}</button>
                    @endforeach
                </div>
                <div class="dm-quick-reopen" onclick="mmExpandQuick()" title="Show suggestions" aria-label="Show suggestions">&#9650; Show suggestions</div>
                <div class="dm-inbar">
                    <input class="in" id="mmInput-mk" type="text" placeholder="Ask anything — plain answers, no jargon..." autocomplete="off" onkeydown="if(event.key==='Enter'){mmAsk('mk', this.value); this.value='';}">
                    <button type="button" class="send" onclick="mmAsk('mk', document.getElementById('mmInput-mk').value); document.getElementById('mmInput-mk').value='';" aria-label="Send">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </aside>
        </div>

        {{-- SALES --}}
        <div class="dash" id="mmDash-sl" data-dash="sl">
            <aside class="dash-left">
                <div class="left-resize" role="separator" aria-orientation="vertical" aria-label="Resize steps panel (arrow keys)" tabindex="0" title="Drag to resize — drag left to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmLeft('restore')" title="Expand steps" aria-label="Expand steps panel">&raquo;</button>
                    <span class="col-rail-label">Steps</span>
                </div>
                <div class="g-body">
                    @foreach($slSteps as $i => $step)
                    <div class="flowst {{ $i === 0 ? 'cur' : '' }}" data-step="sl-{{ $step['key'] }}" onclick="mmSelectStep('sl','{{ $step['key'] }}')">
                        <div class="flowst-dot">{{ $i + 1 }}</div>
                        <div class="flowst-t">{{ $step['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </aside>
            <div class="dash-main">
                <div class="dash-vtabs">
                    @foreach($slSteps as $i => $step)
                    <button type="button" class="dvt {{ $i === 0 ? 'on' : '' }}" data-tab="sl-{{ $step['key'] }}" onclick="mmSelectStep('sl','{{ $step['key'] }}')">{{ strtoupper($step['label']) }}</button>
                    @endforeach
                </div>
                <div class="dash-view">

                    <div class="mm-panel" data-panel="sl-today">
                        <div class="stack-intro">
                            <div class="si-h">WHAT YOU'RE LOOKING AT</div>
                            <div class="si-p">The prospects that need your attention right now, ranked by <b>buying readiness</b> and <b>intent</b>. Use this to decide who to call today versus who to nurture.</div>
                        </div>
                        <table class="dtbl">
                            <thead><tr><th>Prospect</th><th>Readiness</th><th>Intent</th><th>Trust</th><th>Play</th></tr></thead>
                            <tbody>
                                @forelse($slProspects as $p)
                                <tr>
                                    <td class="acctn">{{ $p['name'] }} <span style="color:var(--g3);font-weight:400">({{ $p['sub'] }})</span></td>
                                    <td>{{ $p['readiness'] }}</td>
                                    <td>{{ $p['intent'] }}</td>
                                    <td>{{ $p['trust'] }}</td>
                                    <td><span class="stk-play {{ $p['play'] === 'Call' ? 'call' : 'onboarding' }}">{{ $p['play'] }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" style="color:var(--g3);padding:20px">No trial-only prospects found right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mm-panel" data-panel="sl-accounts" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Every prospect in your pipeline, in one place.</div></div>
                        <table class="dtbl">
                            <thead><tr><th>Prospect</th><th>Readiness</th><th>Intent</th><th>Trust</th><th>Play</th></tr></thead>
                            <tbody>
                                @forelse($slProspects as $p)
                                <tr><td class="acctn">{{ $p['name'] }}</td><td>{{ $p['readiness'] }}</td><td>{{ $p['intent'] }}</td><td>{{ $p['trust'] }}</td><td>{{ $p['play'] }}</td></tr>
                                @empty
                                <tr><td colspan="5" style="color:var(--g3);padding:20px">No prospects found right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mm-panel" data-panel="sl-scripts" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Personalized call, email, and WhatsApp scripts.</div></div>
                        @foreach($slScripts as $sc)
                        <div class="act"><div><div class="act-t">{{ $sc['label'] }}</div><div class="act-d">{{ $sc['text'] }}</div></div></div>
                        @endforeach
                    </div>

                    <div class="mm-panel" data-panel="sl-objections" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Common objections and how to answer them.</div></div>
                        @foreach($slObjections as $o)
                        <div class="act"><div><div class="act-t">{{ $o['q'] }}</div><div class="act-d">{{ $o['a'] }}</div></div></div>
                        @endforeach
                    </div>

                    <div class="mm-panel" data-panel="sl-close" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Prospects most likely to close this week.</div></div>
                        @forelse($slClose as $c)
                        <div class="act"><div><div class="act-t">{{ $c['name'] }}</div><div class="act-d">{{ $c['detail'] }}</div></div></div>
                        @empty
                        <div class="act"><div class="act-t" style="color:var(--g3)">No close-ready candidates found right now.</div></div>
                        @endforelse
                    </div>

                </div>
            </div>
            <aside class="dash-mira">
                <div class="mira-resize" role="separator" aria-orientation="vertical" aria-label="Resize helper panel (arrow keys)" tabindex="0" title="Drag to resize — drag right to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-hd">
                    <span class="dm-dot"></span>
                    <div><div class="dm-t">Sales helper</div><div class="dm-s">ENGINE + AI · GROUNDED IN LIVE DATA</div></div>
                    <span class="dm-ready">Ready</span>
                    <div class="mira-tools">
                        <button type="button" class="mira-btn" onclick="mmMira('min')" title="Minimise" aria-label="Minimise helper panel">&minus;</button>
                        <button type="button" class="mira-btn" data-act="max" onclick="mmMira('max')" title="Maximise" aria-label="Maximise helper panel">&#9974;</button>
                    </div>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmMira('restore')" title="Expand helper" aria-label="Expand helper panel">&laquo;</button>
                    <span class="col-rail-label">Helper</span>
                </div>
                <div class="dm-chat" id="mmChat-sl"></div>
                <div class="row-resize" role="separator" aria-orientation="horizontal" aria-label="Resize suggestions panel (arrow keys)" tabindex="0" title="Drag to resize — drag down to hide">
                    <span class="row-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-quick-hd">
                    <span id="mmQuickHd-sl">ASK MIRA · TODAY</span>
                    <button type="button" class="dm-quick-min" onclick="mmCollapseQuick()" title="Hide suggestions" aria-label="Hide suggestions">&#9660;</button>
                </div>
                <div class="dm-quick" id="mmQuick-sl">
                    @foreach($slPrompts as $p)
                    <button type="button" class="qk" onclick="mmAsk('sl', {{ Js::from($p) }})">{{ $p }}</button>
                    @endforeach
                </div>
                <div class="dm-quick-reopen" onclick="mmExpandQuick()" title="Show suggestions" aria-label="Show suggestions">&#9650; Show suggestions</div>
                <div class="dm-inbar">
                    <input class="in" id="mmInput-sl" type="text" placeholder="Ask anything — plain answers, no jargon..." autocomplete="off" onkeydown="if(event.key==='Enter'){mmAsk('sl', this.value); this.value='';}">
                    <button type="button" class="send" onclick="mmAsk('sl', document.getElementById('mmInput-sl').value); document.getElementById('mmInput-sl').value='';" aria-label="Send">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </aside>
        </div>

        {{-- CUSTOMER RETENTION --}}
        <div class="dash" id="mmDash-ch" data-dash="ch">
            <aside class="dash-left">
                <div class="left-resize" role="separator" aria-orientation="vertical" aria-label="Resize steps panel (arrow keys)" tabindex="0" title="Drag to resize — drag left to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmLeft('restore')" title="Expand steps" aria-label="Expand steps panel">&raquo;</button>
                    <span class="col-rail-label">Steps</span>
                </div>
                <div class="g-body">
                    @foreach($chSteps as $i => $step)
                    <div class="flowst {{ $i === 0 ? 'cur' : '' }}" data-step="ch-{{ $step['key'] }}" onclick="mmSelectStep('ch','{{ $step['key'] }}')">
                        <div class="flowst-dot">{{ $i + 1 }}</div>
                        <div class="flowst-t">{{ $step['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </aside>
            <div class="dash-main">
                <div class="dash-vtabs">
                    @foreach($chSteps as $i => $step)
                    <button type="button" class="dvt {{ $i === 0 ? 'on' : '' }}" data-tab="ch-{{ $step['key'] }}" onclick="mmSelectStep('ch','{{ $step['key'] }}')">{{ strtoupper($step['label']) }}</button>
                    @endforeach
                </div>
                <div class="dash-view">

                    <div class="mm-panel" data-panel="ch-savefirst">
                        <div class="stack-intro">
                            <div class="si-h">RANKED STACK — WHO TO SAVE, IN ORDER</div>
                            <div class="si-p">Sorted by <b>churn risk × value at stake</b>. Click through and reach out before they lapse.</div>
                        </div>
                        <table class="dtbl">
                            <thead><tr><th>Student</th><th>Inactive for</th><th>Value at risk</th><th>Risk score</th></tr></thead>
                            <tbody>
                                @forelse($chAtRisk as $r)
                                <tr>
                                    <td class="acctn">{{ $r['name'] }} <span style="color:var(--g3);font-weight:400">({{ $r['sub'] }})</span></td>
                                    <td>{{ $r['inactiveDays'] }}d</td>
                                    <td>{{ $r['valueAtRisk'] }}</td>
                                    <td><span style="color:var(--crit);font-weight:600">{{ $r['risk'] }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="4" style="color:var(--g3);padding:20px">Nothing urgent right now — no packages expiring in the next 7 days.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mm-panel" data-panel="ch-rootcause" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Why students are actually leaving, ranked by how many it affects.</div></div>
                        @forelse($chRootCauses as $rc)
                        <div class="act"><div><div class="act-t">{{ $rc['name'] }}</div><div class="act-d">{{ $rc['meta'] }}</div></div></div>
                        @empty
                        <div class="act"><div class="act-t" style="color:var(--g3)">No root-cause data available.</div></div>
                        @endforelse
                    </div>

                    <div class="mm-panel" data-panel="ch-offers" style="display:none">
                        <div class="stack-intro"><div class="si-h">WHAT YOU'RE LOOKING AT</div><div class="si-p">Offers that have worked to win students back.</div></div>
                        @foreach($chOffers as $o)
                        <div class="act"><div><div class="act-t">{{ $o['name'] }}</div><div class="act-d">{{ $o['meta'] }}</div></div></div>
                        @endforeach
                    </div>

                    <div class="mm-panel" data-panel="ch-watchlist" style="display:none">
                        <div class="stack-intro"><div class="si-h">WATCH — CHURN CREEPING UP</div><div class="si-p">Not urgent yet, but trending the wrong way.</div></div>
                        <table class="dtbl">
                            <thead><tr><th>Student</th><th>Inactive for</th><th>Value at risk</th><th>Risk score</th></tr></thead>
                            <tbody>
                                @forelse($chWatchlist as $r)
                                <tr><td class="acctn">{{ $r['name'] }}</td><td>{{ $r['inactiveDays'] }}d</td><td>{{ $r['valueAtRisk'] }}</td><td>{{ $r['risk'] }}</td></tr>
                                @empty
                                <tr><td colspan="4" style="color:var(--g3);padding:20px">Nothing trending toward churn right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mm-panel" data-panel="ch-abtest" style="display:none">
                        <div class="stack-intro">
                            <div class="si-h">A/B TESTS</div>
                            <div class="si-p">Not available yet — there is no experiment/results table in the Mock Master data source, so no numbers are shown here rather than inventing them.</div>
                        </div>
                    </div>

                </div>
            </div>
            <aside class="dash-mira">
                <div class="mira-resize" role="separator" aria-orientation="vertical" aria-label="Resize helper panel (arrow keys)" tabindex="0" title="Drag to resize — drag right to hide">
                    <span class="mira-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-hd">
                    <span class="dm-dot"></span>
                    <div><div class="dm-t">Retention helper</div><div class="dm-s">ENGINE + AI · GROUNDED IN LIVE DATA</div></div>
                    <span class="dm-ready">Ready</span>
                    <div class="mira-tools">
                        <button type="button" class="mira-btn" onclick="mmMira('min')" title="Minimise" aria-label="Minimise helper panel">&minus;</button>
                        <button type="button" class="mira-btn" data-act="max" onclick="mmMira('max')" title="Maximise" aria-label="Maximise helper panel">&#9974;</button>
                    </div>
                </div>
                <div class="col-rail">
                    <button type="button" class="col-toggle" onclick="mmMira('restore')" title="Expand helper" aria-label="Expand helper panel">&laquo;</button>
                    <span class="col-rail-label">Helper</span>
                </div>
                <div class="dm-chat" id="mmChat-ch"></div>
                <div class="row-resize" role="separator" aria-orientation="horizontal" aria-label="Resize suggestions panel (arrow keys)" tabindex="0" title="Drag to resize — drag down to hide">
                    <span class="row-grip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </span>
                </div>
                <div class="dm-quick-hd">
                    <span id="mmQuickHd-ch">ASK MIRA · SAVE FIRST</span>
                    <button type="button" class="dm-quick-min" onclick="mmCollapseQuick()" title="Hide suggestions" aria-label="Hide suggestions">&#9660;</button>
                </div>
                <div class="dm-quick" id="mmQuick-ch">
                    @foreach($chPrompts as $p)
                    <button type="button" class="qk" onclick="mmAsk('ch', {{ Js::from($p) }})">{{ $p }}</button>
                    @endforeach
                </div>
                <div class="dm-quick-reopen" onclick="mmExpandQuick()" title="Show suggestions" aria-label="Show suggestions">&#9650; Show suggestions</div>
                <div class="dm-inbar">
                    <input class="in" id="mmInput-ch" type="text" placeholder="Ask anything — plain answers, no jargon..." autocomplete="off" onkeydown="if(event.key==='Enter'){mmAsk('ch', this.value); this.value='';}">
                    <button type="button" class="send" onclick="mmAsk('ch', document.getElementById('mmInput-ch').value); document.getElementById('mmInput-ch').value='';" aria-label="Send">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </aside>
        </div>

    </div>
    </div>
</div>

<style>
/* Reuses the same visual language as the Business Helpers "dash" layout. */
#bhRoot{
    --f1:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
    --fm:'IBM Plex Mono',ui-monospace,monospace;
    --p1:#f9fafb;--p2:#f3f4f6;--ink:#111827;--g2:#6b7280;--g3:#9ca3af;--g4:#d1d5db;
    --ln:#e5e7eb;--ln2:#d1d5db;--sig:#059669;--warn:#d97706;--crit:#dc2626;
    --ac:#7c3aed;--ac-l:#f5f3ff;--ac-m:#ddd6fe;--ac-d:#6d28d9;
    font-family:var(--f1);color:var(--ink);
}
#bhRoot[data-agent="sl"]{--ac:#2563eb;--ac-l:#eff6ff;--ac-m:#bfdbfe;--ac-d:#1d4ed8}
#bhRoot[data-agent="ch"]{--ac:#e11d48;--ac-l:#fff1f2;--ac-m:#fecdd3;--ac-d:#be123c}

#bhRoot .bar{display:flex;align-items:stretch;gap:1px;background:var(--ln);border-bottom:1px solid var(--ln);flex-wrap:wrap;flex-shrink:0}
#bhRoot .atabs{display:flex;gap:1px;background:var(--ln);flex:1}
#bhRoot .atab{background:#fff;padding:0 18px;cursor:pointer;transition:all .15s;text-align:left;border:none;font-family:var(--f1);display:flex;align-items:center;justify-content:center;gap:10px;min-height:56px;flex:1;position:relative}
#bhRoot .atab:hover{background:var(--p1)}
#bhRoot .atab.on{background:var(--ac-l)}
#bhRoot .atab.on::after{content:'';position:absolute;left:0;right:0;bottom:0;height:2px;background:var(--ac)}
#bhRoot .amono{width:28px;height:28px;flex-shrink:0;display:grid;place-items:center;font-size:12px;font-weight:700;background:var(--p2);color:var(--g2);border-radius:8px;transition:all .15s}
#bhRoot .atab.on .amono{background:var(--ac);color:#fff}
#bhRoot .atab .a2{font-size:13px;font-weight:600;color:var(--ink)}
#bhRoot .atab.on .a2{color:var(--ac-d)}

#bhRoot .dash{display:none;grid-template-columns:var(--bh-left-w,220px) 1fr var(--bh-mira-w,320px);gap:1px;background:var(--ln);flex:1;min-height:0;overflow:hidden}
#bhRoot .dash.on{display:grid}
@media(max-width:1180px){#bhRoot .dash{grid-template-columns:190px 1fr}}
@media(max-width:820px){#bhRoot .dash{grid-template-columns:1fr;overflow-y:auto}}

#bhRoot .dash-left{background:#fff;display:flex;flex-direction:column;overflow-y:auto;min-height:0;padding-top:8px;position:relative}
#bhRoot .flowst{display:flex;align-items:center;gap:13px;padding:12px 20px;cursor:pointer}
#bhRoot .flowst:hover{background:var(--p1)}
#bhRoot .flowst.cur{background:var(--ac-l);border-left:2px solid var(--ac);padding-left:18px}
#bhRoot .flowst-dot{width:27px;height:27px;border-radius:50%;border:1.5px solid var(--ln2);background:#fff;display:grid;place-items:center;font-family:var(--fm);font-size:11px;font-weight:700;color:var(--g3);flex-shrink:0}
#bhRoot .flowst.cur .flowst-dot{background:var(--ac);border-color:var(--ac);color:#fff}
#bhRoot .flowst-t{font-size:13.5px;font-weight:600;color:var(--ink)}
@media(max-width:820px){#bhRoot .dash-left{max-height:280px}}

#bhRoot .dash-main{background:#fff;display:flex;flex-direction:column;overflow:hidden;min-height:0}
#bhRoot .dash-vtabs{display:flex;gap:1px;background:var(--ln);border-bottom:1px solid var(--ln);flex-shrink:0;flex-wrap:wrap}
#bhRoot .dvt{flex:1;min-width:78px;font-family:var(--fm);font-size:10.5px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--g2);padding:13px 6px;background:#fff;border:none;cursor:pointer;text-align:center;transition:all .15s}
#bhRoot .dvt.on{background:var(--ac);color:#fff}
#bhRoot .dvt:hover:not(.on){background:var(--p1);color:var(--ink)}
#bhRoot .dash-view{flex:1;overflow-y:auto}

#bhRoot .stack-intro{padding:20px 20px 18px}
#bhRoot .si-h{font-family:var(--fm);font-size:11px;font-weight:700;letter-spacing:2px;color:var(--ink);margin-bottom:10px}
#bhRoot .si-p{font-size:12.5px;color:var(--g2);line-height:1.75;max-width:640px}
#bhRoot .si-p b{color:var(--ink);font-weight:600}

#bhRoot .dtbl{width:100%;border-collapse:collapse;font-size:12px}
#bhRoot .dtbl th{font-size:10px;letter-spacing:.5px;text-transform:uppercase;color:var(--g3);text-align:left;padding:9px 14px;border-bottom:1px solid var(--ln);background:var(--p1);white-space:nowrap;font-weight:700}
#bhRoot .dtbl td{padding:9px 14px;border-bottom:1px solid var(--p2);vertical-align:middle}
#bhRoot .dtbl tr:hover td{background:var(--p1)}
#bhRoot .dtbl .acctn{font-weight:600;color:var(--ink)}

#bhRoot .stk-play{font-size:9.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;padding:2px 8px;border-radius:99px;display:inline-block}
#bhRoot .stk-play.call{background:var(--ac-l);color:var(--ac-d);border:1px solid var(--ac-m)}
#bhRoot .stk-play.onboarding{background:#f3eefc;color:#6d28d9;border:1px solid #e2d5f7}

#bhRoot .act{border:1px solid var(--ln);border-left:3px solid var(--ac);background:#fff;padding:12px 20px;margin:0 20px 10px;display:flex;gap:10px;align-items:center;border-radius:8px}
#bhRoot .act-t{font-size:12.5px;font-weight:600;color:var(--ink);line-height:1.5}
#bhRoot .act-d{font-size:11px;color:var(--g2);margin-top:2px}

#bhRoot .mg-grid{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--ln)}
#bhRoot .mg-cell{background:#fff;padding:16px 20px}
#bhRoot .mg-h{font-size:10px;font-weight:700;letter-spacing:1px;color:var(--g3);text-transform:uppercase;margin-bottom:10px}
#bhRoot .mg-kpi{font-size:20px;font-weight:700;color:var(--ink)}
#bhRoot .mg-kpi small{font-size:10px;color:var(--g3);font-weight:500;margin-left:4px}
@media(max-width:900px){#bhRoot .mg-grid{grid-template-columns:1fr}}

#bhRoot .dash-mira{background:#fff;display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden;min-height:0;position:relative}
#bhRoot .dm-hd{display:flex;align-items:center;gap:11px;padding:16px 18px;border-bottom:1px solid var(--ln);background:var(--p1);flex-shrink:0}
#bhRoot .dm-dot{width:7px;height:7px;border-radius:50%;background:var(--ac);flex-shrink:0;animation:mmblink 1.8s infinite}
@keyframes mmblink{0%,100%{opacity:1}50%{opacity:.2}}
@keyframes mmspin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
#bhRoot .dm-t{font-size:13px;font-weight:700;letter-spacing:.2px;color:var(--ink)}
#bhRoot .dm-s{font-family:var(--fm);font-size:8.5px;letter-spacing:.5px;color:var(--g3);margin-top:3px}
#bhRoot .dm-ready{margin-left:auto;font-family:var(--fm);font-size:9.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--sig);background:#ecfdf5;border:1px solid #a7f3d0;border-radius:99px;padding:3px 10px;flex-shrink:0}
#bhRoot .dm-chat{flex:1;overflow-y:auto;padding:18px;display:flex;flex-direction:column;gap:13px;min-height:120px}
#bhRoot .dm-quick-hd{padding:10px 16px 4px;border-top:1px solid var(--ln);font-family:var(--fm);font-size:9.5px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--g3);flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:8px}
#bhRoot .dm-quick-min{width:20px;height:20px;padding:0;border:1px solid var(--ln2);background:#fff;border-radius:6px;cursor:pointer;display:grid;place-items:center;color:var(--g3);flex-shrink:0}
#bhRoot .dm-quick-min:hover{color:var(--ac-d);border-color:var(--ac-m);background:var(--ac-l)}
#bhRoot .dm-quick{padding:6px 16px 14px;display:flex;flex-direction:column;gap:7px;flex-shrink:0;max-height:var(--bh-quick-h,220px);overflow-y:auto}
#bhRoot .dm-quick .qk{width:100%;text-align:left;padding:10px 12px;font-size:12px;white-space:normal;line-height:1.35;border-radius:8px;background:#fff;border:1px solid var(--ln);cursor:pointer}
#bhRoot .dm-quick .qk:hover{border-color:var(--ac-m);background:var(--ac-l);color:var(--ac-d)}
#bhRoot .dm-quick-hd.dm-quick-collapsed,#bhRoot .dm-quick.dm-quick-collapsed{display:none}
#bhRoot .dm-quick-reopen{display:none;align-items:center;justify-content:center;gap:5px;padding:7px 16px;border-top:1px solid var(--ln);font-family:var(--fm);font-size:10px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--g3);cursor:pointer;flex-shrink:0;background:#fff}
#bhRoot .dm-quick-reopen:hover{color:var(--ac-d);background:var(--ac-l)}
#bhRoot .dm-quick-reopen.dm-quick-reopen-show{display:flex}
#bhRoot .dm-inbar{display:flex;gap:1px;border-top:1px solid var(--ln);background:var(--ln);flex-shrink:0;position:sticky;bottom:0;z-index:2}

/* Resize handles, minimise/maximise, collapse rails — ported from Business Helpers */
#bhRoot .mira-resize{position:absolute;left:0;top:0;bottom:0;width:12px;z-index:20;cursor:col-resize;display:flex;align-items:center;justify-content:center;touch-action:none}
#bhRoot .mira-resize::before{content:'';position:absolute;left:0;top:0;bottom:0;width:1px;background:var(--ln2);transition:background .15s}
#bhRoot .mira-resize:hover::before,#bhRoot .mira-resize.dragging::before{background:var(--ac)}
#bhRoot .mira-grip{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;width:12px;height:22px;border:1px solid var(--ln2);border-radius:4px;background:#fff;color:var(--g3);transition:all .15s}
#bhRoot .mira-resize:hover .mira-grip,#bhRoot .mira-resize.dragging .mira-grip{color:var(--ac-d);border-color:var(--ac-m)}
#bhRoot .mira-resize:focus-visible{outline:2px solid var(--ac);outline-offset:-1px}
#bhRoot .mira-grip svg{width:12px;height:12px;display:block}
#bhRoot .mira-tools{display:flex;gap:4px;flex-shrink:0}
#bhRoot .mira-btn{width:24px;height:24px;padding:0;border:1px solid var(--ln2);background:#fff;border-radius:7px;cursor:pointer;display:grid;place-items:center;font-size:12px;line-height:1;color:var(--g2);font-family:var(--fm);transition:all .15s}
#bhRoot .mira-btn:hover{color:var(--ac-d);border-color:var(--ac-m);background:var(--ac-l)}
#bhRoot .col-rail{display:none;flex:1;flex-direction:column;align-items:center;gap:14px;padding:12px 0;background:#fff;overflow:hidden}
#bhRoot .col-rail .col-toggle{width:26px;height:26px;flex-shrink:0;border:1px solid var(--ln2);background:#fff;border-radius:7px;cursor:pointer;display:grid;place-items:center;font-size:13px;line-height:1;color:var(--g2);font-family:var(--fm);transition:all .15s;padding:0}
#bhRoot .col-rail .col-toggle:hover{color:var(--ac-d);border-color:var(--ac-m);background:var(--ac-l)}
#bhRoot .col-rail-label{writing-mode:vertical-rl;font-family:var(--fm);font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--g3)}
#bhRoot.bh-mira-min .dash-mira > *:not(.col-rail):not(.mira-resize){display:none}
#bhRoot.bh-mira-min .dash-mira > .col-rail{display:flex}
#bhRoot.bh-left-min .dash-left > *:not(.col-rail):not(.left-resize){display:none}
#bhRoot.bh-left-min .dash-left > .col-rail{display:flex}
@media(min-width:1181px){
  #bhRoot.bh-mira-min .dash{grid-template-columns:var(--bh-left-w,220px) 1fr 40px}
  #bhRoot.bh-left-min .dash{grid-template-columns:40px 1fr var(--bh-mira-w,320px)}
  #bhRoot.bh-left-min.bh-mira-min .dash{grid-template-columns:40px 1fr 40px}
}
@media(max-width:1180px){
  #bhRoot .mira-resize{display:none}
  #bhRoot .left-resize{display:none}
}
#bhRoot .left-resize{position:absolute;right:0;top:0;bottom:0;width:12px;z-index:20;cursor:col-resize;display:flex;align-items:center;justify-content:center;touch-action:none;transform:translateX(50%)}
#bhRoot .left-resize::before{content:'';position:absolute;left:50%;top:0;bottom:0;width:1px;background:var(--ln2);transition:background .15s}
#bhRoot .left-resize:hover::before,#bhRoot .left-resize.dragging::before{background:var(--ac)}
#bhRoot .left-resize:focus-visible{outline:2px solid var(--ac);outline-offset:-1px}
#bhRoot .row-resize{height:10px;flex-shrink:0;cursor:row-resize;display:flex;align-items:center;justify-content:center;touch-action:none;position:relative}
#bhRoot .row-resize::before{content:'';position:absolute;left:0;right:0;top:50%;height:1px;background:var(--ln2);transition:background .15s}
#bhRoot .row-resize:hover::before,#bhRoot .row-resize.dragging::before{background:var(--ac)}
#bhRoot .row-resize:focus-visible{outline:2px solid var(--ac);outline-offset:-1px}
#bhRoot .row-grip{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;width:22px;height:12px;border:1px solid var(--ln2);border-radius:4px;background:#fff;color:var(--g3);transition:all .15s}
#bhRoot .row-resize:hover .row-grip,#bhRoot .row-resize.dragging .row-grip{color:var(--ac-d);border-color:var(--ac-m)}
#bhRoot .row-grip svg{width:12px;height:12px;display:block;transform:rotate(90deg)}
#bhRoot .in{flex:1;border:none;padding:12px 14px;font-family:var(--f1);font-size:12.5px;outline:none;min-width:0;background:#fff}
#bhRoot .send{width:44px;border:none;background:var(--ac);color:#fff;cursor:pointer;display:grid;place-items:center;transition:background .15s;flex-shrink:0}
#bhRoot .send:hover{background:var(--ac-d)}
#bhRoot .send svg{width:13px;height:13px;stroke:#fff;fill:none;stroke-width:2.5;stroke-linecap:round}

#bhRoot .msg{max-width:94%;padding:11px 13px;font-size:12.5px;line-height:1.65;border-radius:10px}
#bhRoot .msg.user{background:var(--ink);color:#fff;align-self:flex-end}
#bhRoot .msg.bot{background:var(--p1);border:1px solid var(--ln);align-self:flex-start;color:var(--ink)}
</style>

<script>
var MM_STEP_LABELS = {
    'mk-campaign':'CAMPAIGN','mk-performance':'PERFORMANCE','mk-audience':'AUDIENCE','mk-insights':'INSIGHTS','mk-abtest':'A/B TEST',
    'sl-today':'TODAY','sl-accounts':'ACCOUNTS','sl-scripts':'SCRIPTS','sl-objections':'OBJECTIONS','sl-close':'CLOSE & GROW',
    'ch-savefirst':'SAVE FIRST','ch-rootcause':'ROOT CAUSE','ch-offers':'OFFERS','ch-watchlist':'WATCHLIST','ch-abtest':'A/B TEST'
};

function mmSetAgent(agent) {
    document.getElementById('bhRoot').setAttribute('data-agent', agent);
    document.querySelectorAll('#bhRoot .atab').forEach(function (n) { n.classList.remove('on'); });
    document.getElementById('mmAgentTab-' + agent).classList.add('on');
    document.querySelectorAll('#bhRoot .dash').forEach(function (n) { n.classList.remove('on'); });
    document.getElementById('mmDash-' + agent).classList.add('on');
}

function mmSelectStep(agent, key) {
    var full = agent + '-' + key;
    var dash = document.getElementById('mmDash-' + agent);
    dash.querySelectorAll('.flowst').forEach(function (n) { n.classList.remove('cur'); });
    dash.querySelectorAll('.dvt').forEach(function (n) { n.classList.remove('on'); });
    dash.querySelectorAll('[data-step="' + full + '"]').forEach(function (n) { n.classList.add('cur'); });
    dash.querySelectorAll('[data-tab="' + full + '"]').forEach(function (n) { n.classList.add('on'); });
    dash.querySelectorAll('.mm-panel').forEach(function (n) { n.style.display = 'none'; });
    var panel = dash.querySelector('.mm-panel[data-panel="' + full + '"]');
    if (panel) panel.style.display = '';
    var hd = document.getElementById('mmQuickHd-' + agent);
    if (hd) hd.textContent = 'ASK MIRA · ' + (MM_STEP_LABELS[full] || key.toUpperCase());
}

var MM_AGENT_KEY = { mk: 'marketing', sl: 'sales', ch: 'retention' };

function mmAsk(agent, text) {
    text = (text || '').trim();
    if (!text) return;
    var chat = document.getElementById('mmChat-' + agent);

    var userBubble = document.createElement('div');
    userBubble.className = 'msg user';
    userBubble.textContent = text;
    chat.appendChild(userBubble);

    var botBubble = document.createElement('div');
    botBubble.className = 'msg bot';
    botBubble.innerHTML = '<span style="color:var(--g3)">Thinking…</span>';
    chat.appendChild(botBubble);
    chat.scrollTop = chat.scrollHeight;

    fetch('{{ route('client.mock-master-helper.ask') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ agent: MM_AGENT_KEY[agent], question: text })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        botBubble.textContent = data.answer || "I couldn't get an answer just now.";
        chat.scrollTop = chat.scrollHeight;
    })
    .catch(function () {
        botBubble.textContent = "I couldn't reach the AI just now — try again in a moment.";
        chat.scrollTop = chat.scrollHeight;
    });
}

/* ── Sync Data — pulls the 14 live Mock Master source tables from the
   remote PTE Portal database into their local mm_* mirrors (see
   App\Services\MockMaster\MockMasterSyncService), then reloads the page
   so every panel reflects the freshly-synced data. ── */
function mmSyncData(btn) {
    btn.disabled = true;
    var label = btn.querySelector('span');
    var icon = btn.querySelector('svg');
    if (label) label.textContent = 'Syncing…';
    if (icon) icon.style.animation = 'mmspin 0.8s linear infinite';

    fetch('{{ route('client.mock-master-helper.sync') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.ok) {
            if (label) label.textContent = 'Synced';
            window.location.reload();
        } else {
            if (label) label.textContent = 'Sync failed';
            if (icon) icon.style.animation = '';
            btn.disabled = false;
            alert(data.message || 'Sync failed — please try again.');
        }
    })
    .catch(function () {
        if (label) label.textContent = 'Sync failed';
        if (icon) icon.style.animation = '';
        btn.disabled = false;
        alert("Couldn't reach the server — please try again.");
    });
}

/* ── Collapse the whole left sidebar (same icon/behavior as Business Helpers) ── */
var MM_ICON_EXPAND = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>';
var MM_ICON_COMPRESS = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3v3a2 2 0 0 1-2 2H4"/><path d="M15 3v3a2 2 0 0 0 2 2h3"/><path d="M9 21v-3a2 2 0 0 0-2-2H4"/><path d="M15 21v-3a2 2 0 0 1 2-2h3"/></svg>';
function toggleSidebarCollapse() {
    var sidebar = document.getElementById('platformSidebar');
    if (!sidebar) return;
    var collapsed = sidebar.classList.toggle('bh-sidebar-collapsed');
    if (collapsed) {
        sidebar.style.width = '0px'; sidebar.style.minWidth = '0px';
        sidebar.style.overflow = 'hidden'; sidebar.style.borderRightWidth = '0px';
    } else {
        sidebar.style.width = ''; sidebar.style.minWidth = '';
        sidebar.style.overflow = ''; sidebar.style.borderRightWidth = '';
    }
    var btn = document.getElementById('mmFullBtn');
    btn.innerHTML = collapsed ? MM_ICON_COMPRESS : MM_ICON_EXPAND;
    btn.title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
}

/* ── Suggestions panel collapse/reopen — one shared state, applied to
   whichever agent's panel is currently visible (only one .dash is ever
   showing at a time, so operating on every match is harmless). ── */
function mmCollapseQuick(){
    document.querySelectorAll('#bhRoot .dm-quick-hd').forEach(function (n) { n.classList.add('dm-quick-collapsed'); });
    document.querySelectorAll('#bhRoot .dm-quick').forEach(function (n) { n.classList.add('dm-quick-collapsed'); });
    document.querySelectorAll('#bhRoot .dm-quick-reopen').forEach(function (n) { n.classList.add('dm-quick-reopen-show'); });
}
function mmExpandQuick(){
    document.querySelectorAll('#bhRoot .dm-quick-hd').forEach(function (n) { n.classList.remove('dm-quick-collapsed'); });
    document.querySelectorAll('#bhRoot .dm-quick').forEach(function (n) { n.classList.remove('dm-quick-collapsed'); });
    document.querySelectorAll('#bhRoot .dm-quick-reopen').forEach(function (n) { n.classList.remove('dm-quick-reopen-show'); });
}

/* ── Helper (Mira) panel: drag-resize, minimise, maximise. Shared state on
   #bhRoot's --bh-mira-w custom property, so it applies to whichever agent
   dash is currently visible. ── */
var root = document.getElementById('bhRoot');
var BH_MIRA_MIN = 250, BH_MIRA_DEFAULT = 320;
var bhMiraState = { w: BH_MIRA_DEFAULT, min: false, maxed: false, prev: null };
function bhMiraCap(){
    var dash = document.querySelector('#bhRoot .dash.on');
    var total = dash ? dash.clientWidth : 1200;
    return Math.max(BH_MIRA_MIN + 40, total - 460);
}
function bhMiraApply(){
    var w = Math.min(Math.max(bhMiraState.w, BH_MIRA_MIN), bhMiraCap());
    root.style.setProperty('--bh-mira-w', w + 'px');
    root.classList.toggle('bh-mira-min', bhMiraState.min);
    document.querySelectorAll('#bhRoot .mira-btn[data-act="max"]').forEach(function (btn) {
        btn.innerHTML = bhMiraState.maxed ? '&#10005;' : '&#9974;';
        btn.title = bhMiraState.maxed ? 'Restore' : 'Maximise';
    });
}
function mmMira(action){
    if (action === 'min'){ bhMiraState.min = true; bhMiraState.maxed = false; }
    else if (action === 'restore'){ bhMiraState.min = false; }
    else if (action === 'max'){
        if (bhMiraState.maxed){ bhMiraState.maxed = false; bhMiraState.w = bhMiraState.prev || BH_MIRA_DEFAULT; }
        else { bhMiraState.prev = bhMiraState.w; bhMiraState.maxed = true; bhMiraState.min = false; bhMiraState.w = bhMiraCap(); }
    }
    bhMiraApply();
}
document.querySelectorAll('#bhRoot .mira-resize').forEach(function (h) {
    var dragging = false, startX = 0, startW = 0;
    h.addEventListener('pointerdown', function (e){
        dragging = true; startX = e.clientX;
        startW = parseFloat(getComputedStyle(root).getPropertyValue('--bh-mira-w')) || BH_MIRA_DEFAULT;
        h.classList.add('dragging'); try { h.setPointerCapture(e.pointerId); } catch (_) {}
        document.body.style.userSelect = 'none'; e.preventDefault();
    });
    h.addEventListener('pointermove', function (e){
        if (!dragging) return;
        var w = startW + (startX - e.clientX);
        bhMiraState.maxed = false;
        if (w < BH_MIRA_MIN){ bhMiraState.min = true; root.classList.add('bh-mira-min'); }
        else {
            bhMiraState.min = false;
            bhMiraState.w = Math.min(Math.max(w, BH_MIRA_MIN), bhMiraCap());
            root.classList.remove('bh-mira-min');
            root.style.setProperty('--bh-mira-w', bhMiraState.w + 'px');
        }
    });
    function stop(e){
        if (!dragging) return;
        dragging = false; h.classList.remove('dragging'); document.body.style.userSelect = '';
        try { h.releasePointerCapture(e.pointerId); } catch (_) {}
        bhMiraApply();
    }
    h.addEventListener('pointerup', stop);
    h.addEventListener('pointercancel', stop);
    h.addEventListener('dblclick', function (){ bhMiraState.w = BH_MIRA_DEFAULT; bhMiraState.maxed = false; bhMiraApply(); });
    h.addEventListener('keydown', function (e){
        if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
        e.preventDefault();
        var step = (e.shiftKey ? 48 : 16) * (e.key === 'ArrowLeft' ? 1 : -1);
        bhMiraState.w = Math.min(Math.max(bhMiraState.w + step, BH_MIRA_MIN), bhMiraCap());
        bhMiraState.maxed = false; bhMiraApply();
    });
});
window.addEventListener('resize', function (){ bhMiraApply(); });

/* ── Steps panel: drag-resize + collapse. Same pattern as the helper panel,
   mirrored on the other side, sharing --bh-left-w. ── */
var BH_LEFT_MIN = 170, BH_LEFT_DEFAULT = 220;
var bhLeftState = { w: BH_LEFT_DEFAULT, min: false };
function bhLeftCap(){
    var dash = document.querySelector('#bhRoot .dash.on');
    var total = dash ? dash.clientWidth : 1200;
    return Math.max(BH_LEFT_MIN + 40, total - 460);
}
function bhLeftApply(){
    var w = Math.min(Math.max(bhLeftState.w, BH_LEFT_MIN), bhLeftCap());
    root.style.setProperty('--bh-left-w', w + 'px');
    root.classList.toggle('bh-left-min', bhLeftState.min);
}
function mmLeft(action){
    if (action === 'min') bhLeftState.min = true;
    else if (action === 'restore') bhLeftState.min = false;
    bhLeftApply();
}
document.querySelectorAll('#bhRoot .left-resize').forEach(function (h) {
    var dragging = false, startX = 0, startW = 0;
    h.addEventListener('pointerdown', function (e){
        dragging = true; startX = e.clientX;
        startW = parseFloat(getComputedStyle(root).getPropertyValue('--bh-left-w')) || BH_LEFT_DEFAULT;
        h.classList.add('dragging'); try { h.setPointerCapture(e.pointerId); } catch (_) {}
        document.body.style.userSelect = 'none'; e.preventDefault();
    });
    h.addEventListener('pointermove', function (e){
        if (!dragging) return;
        var w = startW + (e.clientX - startX);
        if (w < BH_LEFT_MIN){ bhLeftState.min = true; root.classList.add('bh-left-min'); }
        else {
            bhLeftState.min = false;
            bhLeftState.w = Math.min(Math.max(w, BH_LEFT_MIN), bhLeftCap());
            root.classList.remove('bh-left-min');
            root.style.setProperty('--bh-left-w', bhLeftState.w + 'px');
        }
    });
    function stop(e){
        if (!dragging) return;
        dragging = false; h.classList.remove('dragging'); document.body.style.userSelect = '';
        try { h.releasePointerCapture(e.pointerId); } catch (_) {}
        bhLeftApply();
    }
    h.addEventListener('pointerup', stop);
    h.addEventListener('pointercancel', stop);
    h.addEventListener('dblclick', function (){ bhLeftState.w = BH_LEFT_DEFAULT; bhLeftState.min = false; bhLeftApply(); });
    h.addEventListener('keydown', function (e){
        if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
        e.preventDefault();
        var step = (e.shiftKey ? 48 : 16) * (e.key === 'ArrowLeft' ? -1 : 1);
        bhLeftState.w = Math.min(Math.max(bhLeftState.w + step, BH_LEFT_MIN), bhLeftCap());
        bhLeftApply();
    });
});
window.addEventListener('resize', function (){ bhLeftApply(); });

/* ── Suggestions row height: drag-resize, collapsing past a threshold. ── */
var BH_QUICK_MIN = 90, BH_QUICK_DEFAULT = 220;
var bhQuickState = { h: BH_QUICK_DEFAULT };
function bhQuickApply(){
    root.style.setProperty('--bh-quick-h', Math.max(bhQuickState.h, BH_QUICK_MIN) + 'px');
}
document.querySelectorAll('#bhRoot .row-resize').forEach(function (h) {
    var dragging = false, startY = 0, startH = 0;
    function isCollapsed(){
        var q = h.parentElement.querySelector('.dm-quick');
        return !q || q.classList.contains('dm-quick-collapsed');
    }
    h.addEventListener('pointerdown', function (e){
        dragging = true; startY = e.clientY;
        var wasCollapsed = isCollapsed();
        var q = h.parentElement.querySelector('.dm-quick');
        startH = wasCollapsed ? BH_QUICK_MIN : Math.max((q || {}).offsetHeight || BH_QUICK_DEFAULT, BH_QUICK_MIN);
        h.classList.add('dragging'); try { h.setPointerCapture(e.pointerId); } catch (_) {}
        document.body.style.userSelect = 'none'; e.preventDefault();
    });
    h.addEventListener('pointermove', function (e){
        if (!dragging) return;
        var newH = startH + (startY - e.clientY);
        if (newH < BH_QUICK_MIN) { mmCollapseQuick(); }
        else { mmExpandQuick(); bhQuickState.h = Math.max(newH, BH_QUICK_MIN); root.style.setProperty('--bh-quick-h', bhQuickState.h + 'px'); }
    });
    function stop(e){
        if (!dragging) return;
        dragging = false; h.classList.remove('dragging'); document.body.style.userSelect = '';
        try { h.releasePointerCapture(e.pointerId); } catch (_) {}
        if (!isCollapsed()) bhQuickApply();
    }
    h.addEventListener('pointerup', stop);
    h.addEventListener('pointercancel', stop);
    h.addEventListener('dblclick', function (){ mmExpandQuick(); bhQuickState.h = BH_QUICK_DEFAULT; bhQuickApply(); });
});

bhMiraApply(); bhLeftApply(); bhQuickApply();
</script>

@endsection
