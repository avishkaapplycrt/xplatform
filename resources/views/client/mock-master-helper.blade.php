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

// Presentation-only helpers for the tables: an initial avatar whose colour
// comes from the name, and a pill style picked from a stage label.
$mmAvColors = ['#3b5bdb', '#7c5cfc', '#f97316', '#1e3a8a', '#0284c7', '#334155', '#16a34a', '#db2777', '#0d9488'];
$mmAvColor  = fn ($name) => $mmAvColors[crc32((string) $name) % count($mmAvColors)];
$mmInitial  = fn ($name) => mb_strtoupper(mb_substr(trim((string) $name), 0, 1)) ?: '?';
$mmStageKind = fn ($label) => preg_match('/won|active|renew/i', (string) $label) ? 'good'
    : (preg_match('/lost|expired|fail/i', (string) $label) ? 'bad'
    : (preg_match('/decision|bought/i', (string) $label) ? 'violet' : 'info'));
@endphp

<div class="bh-page flex flex-col h-full overflow-hidden">

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
    <div id="bhRoot" class="h-full flex flex-col overflow-hidden" data-agent="mk">

        {{-- Selector bar: agent tabs --}}
        <div class="bar">
            <div class="atabs">
                @foreach($agents as $i => $a)
                <button type="button" class="atab {{ $i === 0 ? 'on' : '' }}" id="mmAgentTab-{{ $a['key'] }}" onclick="mmSetAgent('{{ $a['key'] }}')">
                    <span class="amono">
                        @if($a['key'] === 'mk')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l15-6v14L3 13z"/><path d="M3 11v2"/><path d="M7.5 13.8V18a2 2 0 0 0 4 0v-3"/><path d="M21 9v6"/></svg>
                        @elseif($a['key'] === 'sl')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 20v-6"/><path d="M10 20V10"/><path d="M14 20V4"/><path d="M18 20v-9"/></svg>
                        @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8"/><path d="M18 14.2a6.5 6.5 0 0 1 3.5 5.8"/></svg>
                        @endif
                    </span><span class="a2">{{ $a['label'] }}</span>
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
                        <form id="mmCampaignFilter" method="GET" action="{{ route('client.mock-master-helper') }}" onsubmit="return mmCampaignSubmit(event)" class="mm-filter-bar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;padding:12px 14px;border-bottom:1px solid var(--ln);background:var(--p1)">
                            <label style="display:flex;flex-direction:column;gap:4px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--g3)">Subscription
                                <select name="subscription" style="min-width:220px;padding:6px 8px;border:1px solid var(--ln);border-radius:6px;font-size:12px;background:#fff">
                                    <option value="">All subscriptions</option>
                                    @foreach($mkSubscriptions as $sub)
                                        <option value="{{ $sub }}" {{ ($mkFilters['subscription'] ?? '') === $sub ? 'selected' : '' }}>{{ $sub }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label style="display:flex;flex-direction:column;gap:4px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--g3)">Payment from
                                <input type="date" name="from" value="{{ $mkFilters['from'] ?? '' }}" style="padding:6px 8px;border:1px solid var(--ln);border-radius:6px;font-size:12px;background:#fff">
                            </label>
                            <label style="display:flex;flex-direction:column;gap:4px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--g3)">Payment to
                                <input type="date" name="to" value="{{ $mkFilters['to'] ?? '' }}" style="padding:6px 8px;border:1px solid var(--ln);border-radius:6px;font-size:12px;background:#fff">
                            </label>
                            <button type="submit" style="padding:7px 14px;border-radius:6px;border:none;background:#7c3aed;color:#fff;font-size:12px;font-weight:600;cursor:pointer">Apply</button>
                            <button type="button" onclick="mmCampaignReset()" style="padding:7px 14px;border-radius:6px;border:1px solid var(--ln);color:var(--g3);font-size:12px;font-weight:600;cursor:pointer;background:#fff">Reset</button>
                        </form>
                        <table class="dtbl">
                            <thead><tr><th>Student</th><th>Package value</th><th>Payment date</th><th>Stage</th><th>Readiness</th><th>Trust</th><th>Approach</th><th>Last active</th></tr></thead>
                            <tbody id="mmCampaignBody">
                                @forelse($mkStudents as $s)
                                <tr>
                                    <td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($s['name']) }}">{{ $mmInitial($s['name']) }}</span><div><div class="bh-acct-n">{{ $s['name'] }}</div><div class="bh-acct-c">({{ $s['sub'] }})</div></div></div></td>
                                    <td>{{ $s['value'] }}</td>
                                    <td>{{ $s['paymentDate'] }}</td>
                                    <td><span class="bh-pill {{ $mmStageKind($s['stage']) }}">{{ $s['stage'] }}</span></td>
                                    <td>{{ $s['readiness'] }}</td>
                                    <td>{{ $s['trust'] }}</td>
                                    <td>@if($s['approach'] === 'Offer-led')<span class="bh-pill good">Offer-led</span>@else<span class="bh-pill warn">Proof-led</span>@endif</td>
                                    <td>{{ $s['lastActive'] }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="8" style="color:var(--g3);padding:20px">No renewal-ready students found right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div id="mmCampaignPager" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 14px;border-top:1px solid var(--ln);font-size:12px;color:var(--g3)">
                            <span id="mmCampaignPageInfo">Page {{ $mkPaged['page'] }} of {{ $mkPaged['last_page'] }} · {{ number_format($mkPaged['total']) }} students</span>
                            <div style="display:flex;gap:6px">
                                <button type="button" id="mmCampaignPrev" onclick="mmCampaignGo({{ max(1, $mkPaged['page'] - 1) }})" {{ $mkPaged['page'] <= 1 ? 'disabled' : '' }} style="padding:5px 12px;border-radius:6px;border:1px solid var(--ln);background:#fff;font-size:12px;font-weight:600;cursor:pointer">Previous</button>
                                <button type="button" id="mmCampaignNext" onclick="mmCampaignGo({{ min($mkPaged['last_page'], $mkPaged['page'] + 1) }})" {{ $mkPaged['page'] >= $mkPaged['last_page'] ? 'disabled' : '' }} style="padding:5px 12px;border-radius:6px;border:1px solid var(--ln);background:#fff;font-size:12px;font-weight:600;cursor:pointer">Next</button>
                            </div>
                        </div>
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
                    <span class="dm-spark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 2.5l1.9 5.6 5.6 1.9-5.6 1.9L10 17.5l-1.9-5.6L2.5 10l5.6-1.9z"/><path d="M18.5 13l.95 2.55L22 16.5l-2.55.95L18.5 20l-.95-2.55L15 16.5l2.55-.95z"/></svg></span>
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
                <div class="dm-quick" id="mmQuick-mk"></div>
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
                                    <td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($p['name']) }}">{{ $mmInitial($p['name']) }}</span><div><div class="bh-acct-n">{{ $p['name'] }}</div><div class="bh-acct-c">({{ $p['sub'] }})</div></div></div></td>
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
                                <tr><td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($p['name']) }}">{{ $mmInitial($p['name']) }}</span><div class="bh-acct-n">{{ $p['name'] }}</div></div></td><td>{{ $p['readiness'] }}</td><td>{{ $p['intent'] }}</td><td>{{ $p['trust'] }}</td><td>{{ $p['play'] }}</td></tr>
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
                    <span class="dm-spark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 2.5l1.9 5.6 5.6 1.9-5.6 1.9L10 17.5l-1.9-5.6L2.5 10l5.6-1.9z"/><path d="M18.5 13l.95 2.55L22 16.5l-2.55.95L18.5 20l-.95-2.55L15 16.5l2.55-.95z"/></svg></span>
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
                <div class="dm-quick" id="mmQuick-sl"></div>
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
                                    <td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($r['name']) }}">{{ $mmInitial($r['name']) }}</span><div><div class="bh-acct-n">{{ $r['name'] }}</div><div class="bh-acct-c">({{ $r['sub'] }})</div></div></div></td>
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
                                <tr><td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:{{ $mmAvColor($r['name']) }}">{{ $mmInitial($r['name']) }}</span><div class="bh-acct-n">{{ $r['name'] }}</div></div></td><td>{{ $r['inactiveDays'] }}d</td><td>{{ $r['valueAtRisk'] }}</td><td>{{ $r['risk'] }}</td></tr>
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
                    <span class="dm-spark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 2.5l1.9 5.6 5.6 1.9-5.6 1.9L10 17.5l-1.9-5.6L2.5 10l5.6-1.9z"/><path d="M18.5 13l.95 2.55L22 16.5l-2.55.95L18.5 20l-.95-2.55L15 16.5l2.55-.95z"/></svg></span>
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
                <div class="dm-quick" id="mmQuick-ch"></div>
                <div class="dm-quick-reopen" onclick="mmExpandQuick()" title="Show suggestions" aria-label="Show suggestions">&#9650; Show suggestions</div>
                <div class="dm-inbar">
                    <input class="in" id="mmInput-ch" type="text" placeholder="Ask anything — plain answers, no jargon..." autocomplete="off" onkeydown="if(event.key==='Enter'){mmAsk('ch', this.value); this.value='';}">
                    <button type="button" class="send" onclick="mmAsk('ch', document.getElementById('mmInput-ch').value); document.getElementById('mmInput-ch').value='';" aria-label="Send">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </aside>
        </div>

        {{-- Popup for "which/who" style answers — a clean table of the real
             students behind the answer, instead of a run-on paragraph. --}}
        <div class="mm-list-modal-overlay" id="mmListModalOverlay" onclick="if(event.target===this) closeMmListModal()">
            <div class="mm-list-modal">
                <div class="mm-list-modal-hd">
                    <span id="mmListModalTitle"></span>
                    <button type="button" onclick="closeMmListModal()" aria-label="Close">✕</button>
                </div>
                <div class="mm-list-modal-body" id="mmListModalBody"></div>
            </div>
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

.risk-modal-overlay{display:none;position:fixed;inset:0;background:rgba(17,24,39,.45);z-index:10000;align-items:center;justify-content:center;padding:24px}
.risk-modal-overlay.show{display:flex}
.risk-modal{background:#fff;border-radius:12px;max-width:820px;width:100%;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25)}
.risk-modal-hd{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:13px;color:#111827}
.risk-modal-hd button{border:none;background:none;font-size:16px;color:#9ca3af;cursor:pointer;line-height:1;padding:4px}
.risk-modal-hd button:hover{color:#111827}
.risk-modal-body{overflow:auto;padding:12px 18px 18px}
.risk-modal-body table{width:100%;border-collapse:collapse;font-size:12px}
.risk-modal-body th,.risk-modal-body td{padding:7px 10px;border-bottom:1px solid #f0f0f0;text-align:left;white-space:nowrap}
.risk-modal-body th{font-size:10.5px;letter-spacing:.5px;text-transform:uppercase;color:#6b7280;background:#f9fafb}

/* ── Sync overlay — blurs the whole interface while a sync is running so
   nothing looks interactive mid-copy, and lifts the moment it finishes. ── */
body.mm-syncing > *:not(#mmSyncOverlay){filter:blur(5px);pointer-events:none;user-select:none;transition:filter .25s ease}
#mmSyncOverlay{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;background:rgba(15,15,20,.22)}
body.mm-syncing #mmSyncOverlay{display:flex}
#mmSyncOverlay .mm-sync-card{background:#fff;border-radius:14px;padding:26px 40px;display:flex;flex-direction:column;align-items:center;gap:10px;box-shadow:0 20px 60px rgba(0,0,0,.28)}
#mmSyncOverlay .mm-sync-spinner{width:32px;height:32px;border:3px solid #e5e7eb;border-top-color:#4f46e5;border-radius:50%;animation:mmspin .8s linear infinite}
#mmSyncOverlay .mm-sync-spinner.done{border:none;animation:none}
#mmSyncOverlay .mm-sync-text{font-size:13px;font-weight:700;color:#111827}
#mmSyncOverlay .mm-sync-sub{font-size:11.5px;color:#6b7280}
#bhRoot .dm-t{font-size:13px;font-weight:700;letter-spacing:.2px;color:var(--ink)}
#bhRoot .dm-s{font-family:var(--fm);font-size:8.5px;letter-spacing:.5px;color:var(--g3);margin-top:3px}
#bhRoot .dm-ready{margin-left:auto;font-family:var(--fm);font-size:9.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--sig);background:#ecfdf5;border:1px solid #a7f3d0;border-radius:99px;padding:3px 10px;flex-shrink:0}
#bhRoot .dm-chat{flex:1;overflow-y:auto;padding:18px;display:flex;flex-direction:column;gap:13px;min-height:120px}
#bhRoot .dm-quick-hd{padding:10px 16px 4px;border-top:1px solid var(--ln);font-family:var(--fm);font-size:9.5px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--g3);flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:8px}
#bhRoot .dm-quick-min{width:20px;height:20px;padding:0;border:1px solid var(--ln2);background:#fff;border-radius:6px;cursor:pointer;display:grid;place-items:center;color:var(--g3);flex-shrink:0}
#bhRoot .dm-quick-min:hover{color:var(--ac-d);border-color:var(--ac-m);background:var(--ac-l)}
#bhRoot .dm-quick{padding:6px 16px 14px;display:flex;flex-direction:column;gap:7px;flex-shrink:0;max-height:var(--bh-quick-h,220px);overflow-y:auto}
#bhRoot .qk{font-size:11px;font-weight:600;color:var(--g2);padding:7px 12px;border:1px solid var(--ln);cursor:pointer;background:#fff;transition:all .15s;border-radius:99px}
#bhRoot .qk:hover{border-color:var(--ac-m);background:var(--ac-l);color:var(--ac-d)}
#bhRoot .qk:disabled{opacity:.6;cursor:default}
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
#bhRoot .msg.bot p{margin:0 0 8px}
#bhRoot .msg.bot p:last-child{margin-bottom:0}

/* "View list" popup — same pattern as Business Helpers' risk modal, so a
   list-shaped answer (who/which questions) is a clean table, not a run-on
   paragraph. */
.mm-list-modal-overlay{display:none;position:fixed;inset:0;background:rgba(17,24,39,.45);z-index:200;align-items:center;justify-content:center;padding:24px}
.mm-list-modal-overlay.show{display:flex}
.mm-list-modal{background:#fff;border-radius:12px;max-width:820px;width:100%;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25)}
.mm-list-modal-hd{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #e5e7eb;font-weight:600;font-size:13px;color:#111827}
.mm-list-modal-hd button{border:none;background:none;font-size:16px;color:#9ca3af;cursor:pointer;line-height:1;padding:4px}
.mm-list-modal-hd button:hover{color:#111827}
.mm-list-modal-body{overflow:auto;padding:12px 18px 18px}
.mm-list-modal-body table{width:100%;border-collapse:collapse;font-size:12px}
.mm-list-modal-body th,.mm-list-modal-body td{padding:7px 10px;border-bottom:1px solid #f0f0f0;text-align:left;white-space:nowrap}
.mm-list-modal-body th{font-size:10.5px;letter-spacing:.5px;text-transform:uppercase;color:#6b7280;background:#f9fafb}
.mm-list-modal-body #mmListMoreBtn{margin-top:14px}

/* Student picker for the Sales · Accounts prompts — a real name list via
   <datalist>, same pattern as Business Helpers' Retention name form. */
#bhRoot .nmform{display:flex;gap:6px;margin-top:8px;align-items:stretch}
#bhRoot .nmin{flex:1;min-width:0;border:1px solid var(--ln2);border-radius:8px;padding:8px 10px;font-family:var(--f1);font-size:12px;color:var(--ink);outline:none;background:#fff}
#bhRoot .nmin:focus{border-color:var(--ac-m);box-shadow:0 0 0 3px var(--ac-l)}
#bhRoot .nmin:disabled{background:var(--p2);color:var(--g3)}
#bhRoot .nmform .qk{flex-shrink:0;align-self:center}

/* ══════════════════════════════════════════════════════════════════════
   THEME — same floating-card layout as Business Helpers. Visual overrides
   only: every class, id and handler above is unchanged.
   ══════════════════════════════════════════════════════════════════════ */
.bh-page{background:#f5f7fb}

#bhRoot{--ln:#e6e9f0;--ln2:#d9dee8;--p1:#f7f8fb;--p2:#f1f3f8;--g2:#5b6475;--card-r:14px;--card-sh:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.04)}

/* Agent tabs — one white strip, the active agent is a solid accent block */
#bhRoot .bar{background:transparent;border:none;gap:0;margin-bottom:16px}
#bhRoot .atabs{background:#fff;gap:0;border:1px solid var(--ln);border-radius:var(--card-r);box-shadow:var(--card-sh);padding:0;overflow:hidden}
#bhRoot .atab{min-height:48px;background:#fff;gap:10px;border-radius:0}
#bhRoot .atab + .atab{border-left:1px solid var(--ln)}
#bhRoot .atab:hover{background:var(--p1)}
#bhRoot .atab.on,#bhRoot .atab.on:hover{background:var(--ac);border-radius:var(--card-r);border-left-color:transparent}
#bhRoot .atab.on + .atab{border-left-color:transparent}
#bhRoot .atab.on::after{display:none}
#bhRoot .amono{width:auto;height:auto;background:none;border-radius:0;color:#475569}
#bhRoot .amono svg{width:17px;height:17px;display:block}
#bhRoot .atab.on .amono{background:none;color:#fff}
#bhRoot .atab .a2{font-size:13px;font-weight:600;color:#0f172a}
#bhRoot .atab.on .a2{color:#fff}

/* Three floating cards instead of one card split by hairlines */
#bhRoot .dash{gap:16px;background:transparent}
#bhRoot .dash-left,#bhRoot .dash-main,#bhRoot .dash-mira{background:#fff;border:1px solid var(--ln);border-radius:var(--card-r);box-shadow:var(--card-sh)}
#bhRoot .dash-main{overflow:hidden}
#bhRoot .left-resize::before,#bhRoot .mira-resize::before,#bhRoot .row-resize::before{background:transparent}
#bhRoot .left-resize{transform:translateX(0)}
#bhRoot .mira-grip{height:26px;border-radius:6px;box-shadow:0 1px 2px rgba(16,24,40,.06)}

/* Steps list */
#bhRoot .dash-left{padding:10px 0}
#bhRoot .flowst{margin:2px 10px;padding:10px 10px;border-radius:10px;gap:12px;transition:background .15s}
#bhRoot .flowst.cur{background:var(--ac-l);border-left:none;padding-left:10px}
#bhRoot .flowst-dot{width:27px;height:27px;border:1.5px solid var(--ln2);font-family:'Inter',sans-serif;font-size:11px;font-weight:600;color:#64748b}
#bhRoot .flowst.cur .flowst-dot{box-shadow:0 0 0 4px var(--ac-l)}
#bhRoot .flowst-t{font-size:13.5px;font-weight:600;color:#0f172a}
#bhRoot .col-rail{background:transparent}

/* View tabs across the middle card (active tab: tint + underline) */
#bhRoot .dash-vtabs{gap:0;background:#fff;padding:6px 6px 0}
#bhRoot .dvt{font-family:'Inter',sans-serif;font-size:10.5px;font-weight:600;letter-spacing:1.5px;color:#475569;padding:12px 6px;background:#fff;border-radius:8px 8px 0 0}
#bhRoot .dvt + .dvt{box-shadow:inset 1px 0 0 var(--ln)}
#bhRoot .dvt.on{background:var(--ac-l);color:var(--ac-d);box-shadow:inset 0 -2.5px 0 var(--ac)}
#bhRoot .dvt.on + .dvt{box-shadow:none}
#bhRoot .dvt:hover:not(.on){background:var(--p1);color:#0f172a}

/* "What you're looking at" intro */
#bhRoot .stack-intro{padding:20px 20px 16px}
#bhRoot .si-h{font-family:'Inter',sans-serif;font-size:11px;font-weight:700;letter-spacing:2px;color:#0f172a;margin-bottom:8px}
#bhRoot .si-p{font-size:12.5px;color:#475569;line-height:1.75;max-width:680px}
#bhRoot .si-p b{color:#0f172a}

/* Data tables — a rounded inset table inside the card */
#bhRoot .dtbl{width:calc(100% - 40px);margin:0 20px 20px;border-collapse:separate;border-spacing:0;border:1px solid var(--ln);border-radius:10px;overflow:hidden;font-size:12px}
#bhRoot .dtbl th{font-size:10px;font-weight:700;letter-spacing:.5px;color:#64748b;background:var(--p1);padding:9px 14px;border-bottom:1px solid var(--ln)}
#bhRoot .dtbl td{padding:9px 14px;border-bottom:1px solid var(--ln);color:#1e293b;font-variant-numeric:tabular-nums}
#bhRoot .dtbl tbody tr:last-child td{border-bottom:none}
#bhRoot .dtbl tr:hover td{background:#fafbfd}
#bhRoot .bh-acct{display:flex;align-items:center;gap:10px;min-width:160px}
#bhRoot .bh-av{width:28px;height:28px;border-radius:7px;display:grid;place-items:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0}
#bhRoot .bh-acct-n{font-weight:600;color:#0f172a;line-height:1.3}
#bhRoot .bh-acct-c{font-size:11px;font-weight:400;color:#64748b;line-height:1.35;margin-top:1px}
#bhRoot .bh-pill{display:inline-block;font-size:11px;font-weight:600;line-height:1.3;padding:3px 8px;border-radius:6px;white-space:normal;max-width:130px}
#bhRoot .bh-pill.good{background:#e8f7ee;color:#15803d}
#bhRoot .bh-pill.warn{background:#fff1e6;color:#c2410c}
#bhRoot .bh-pill.bad{background:#fdecec;color:#b91c1c}
#bhRoot .bh-pill.info{background:#e8f0fe;color:#1d4ed8}
#bhRoot .bh-pill.violet{background:#f1ecfe;color:#6d28d9}
#bhRoot .stk-play{font-size:9.5px;padding:3px 8px;border-radius:6px;border:none}

/* Segment / insight / offer cards and KPI grid */
#bhRoot .act{border:1px solid var(--ln);border-left:3px solid var(--ac);border-radius:10px;padding:12px 18px;margin:0 20px 10px}
#bhRoot .act-t{font-size:12.5px}
#bhRoot .act-d{font-size:11px;margin-top:2px}
#bhRoot .mg-grid{gap:12px;background:transparent;padding:0 20px 20px}
#bhRoot .mg-cell{border:1px solid var(--ln);border-radius:12px;padding:16px 18px}
#bhRoot .mg-h{font-size:10px;letter-spacing:1px;color:#64748b}
#bhRoot .mg-kpi{font-size:20px;color:#0f172a}

/* Helper panel (right card) */
#bhRoot .dm-hd{background:#fff;padding:14px 16px;gap:10px}
#bhRoot .dm-spark{width:18px;height:18px;color:var(--ac);flex-shrink:0;display:grid;place-items:center}
#bhRoot .dm-spark svg{width:17px;height:17px}
#bhRoot .dm-t{font-size:13px;font-weight:700;letter-spacing:.2px;color:#0f172a}
#bhRoot .dm-s{font-family:'Inter',sans-serif;font-size:8.5px;font-weight:600;letter-spacing:.5px;color:#64748b;margin-top:3px}
#bhRoot .dm-ready{font-family:'Inter',sans-serif;font-size:9.5px;letter-spacing:1px;color:#15803d;background:#e8f7ee;border:none;padding:3px 10px}
#bhRoot .mira-tools{gap:4px}
#bhRoot .mira-btn{width:24px;height:24px;border-radius:7px;font-size:12px;color:#334155;border-color:var(--ln2)}
#bhRoot .dm-chat{padding:18px}

/* Friendly greeting while a chat is empty (pure CSS, disappears on first message) */
#bhRoot .dm-chat:empty{flex-direction:row;align-items:flex-start;gap:10px}
#bhRoot .dm-chat:empty::before{content:'\1F44B';width:30px;height:30px;flex-shrink:0;border-radius:50%;background:#fff;border:1px solid var(--ln);display:grid;place-items:center;font-size:14px;box-shadow:var(--card-sh)}
#bhRoot .dm-chat:empty::after{background:var(--p2);border-radius:10px;padding:11px 13px;font-size:12.5px;line-height:1.65;color:#1e293b;white-space:pre-line;max-width:300px}
#bhRoot #mmChat-mk:empty::after{content:"Hi! I'm your Marketing helper.\A I can help you with campaigns, student segments, renewal copy, and more."}
#bhRoot #mmChat-sl:empty::after{content:"Hi! I'm your Sales helper.\A I can help you decide which students to contact, what to say, and how to convert them."}
#bhRoot #mmChat-ch:empty::after{content:"Hi! I'm your Customer Retention helper.\A I can help you spot at-risk students, plan saves, and choose offers."}

/* Chat bubbles */
#bhRoot .dm-chat .msg{font-size:12.5px;border-radius:10px;padding:11px 13px}
#bhRoot .dm-chat .msg.bot{background:var(--p2);border:none}
#bhRoot .dm-chat .msg.user{background:var(--ac);color:#fff}

/* Suggested prompts */
#bhRoot .dm-quick-hd{font-family:'Inter',sans-serif;font-size:9.5px;font-weight:700;letter-spacing:1.5px;color:#4c5a8a;padding:10px 16px 4px;border-top:1px solid var(--ln)}
#bhRoot .dm-quick-min{width:20px;height:20px;border-radius:6px;color:#475569;font-size:8px}
#bhRoot .dm-quick{padding:6px 16px 14px;gap:7px}
#bhRoot .dm-quick .qk{position:relative;padding:10px 30px 10px 12px;font-size:12px;font-weight:500;color:#1e293b;border:1px solid var(--ln2);border-radius:8px}
#bhRoot .dm-quick .qk::after{content:'';position:absolute;right:12px;top:50%;width:6px;height:6px;border-right:1.8px solid #64748b;border-top:1.8px solid #64748b;transform:translateY(-50%) rotate(45deg)}
#bhRoot .dm-quick .qk:hover::after{border-color:var(--ac-d)}
#bhRoot .dm-quick-reopen{font-family:'Inter',sans-serif;font-size:10px;letter-spacing:.5px}

/* Composer — rounded input with a separate square send button */
#bhRoot .dm-inbar{gap:8px;padding:10px 16px 14px;background:#fff;border-top:none}
#bhRoot .dm-inbar .in{border:1px solid var(--ln2);border-radius:10px;padding:0 14px;min-height:42px;font-size:12.5px;transition:border-color .15s,box-shadow .15s}
#bhRoot .dm-inbar .in:focus{border-color:var(--ac-m);box-shadow:0 0 0 3px var(--ac-l)}
#bhRoot .dm-inbar .send{width:42px;border-radius:10px;box-shadow:0 4px 12px rgba(16,24,40,.18)}
#bhRoot .dm-inbar .send svg{width:14px;height:14px;fill:#fff;stroke:#fff;stroke-width:1.5}

/* ══ Compact tiers for laptops ══
   At 100% browser zoom, laptops with Windows display scaling (125% / 150%)
   have a narrower CSS viewport (~1536px / ~1280px), so everything looks
   bigger. These tiers step the sizes down as the viewport narrows; wide
   screens keep the sizes above. Visual only. */
@media (max-width:1600px){
  .bh-page > .p-6{padding:16px}
  #bhRoot .bar{margin-bottom:12px}
  #bhRoot .dash{gap:12px}
  #bhRoot .atab{min-height:44px;gap:8px}
  #bhRoot .amono svg{width:16px;height:16px}
  #bhRoot .atab .a2{font-size:12.5px}
  #bhRoot .dash-left{padding:8px 0}
  #bhRoot .flowst{margin:1px 8px;padding:8px 8px;gap:10px}
  #bhRoot .flowst.cur{padding-left:8px}
  #bhRoot .flowst-dot{width:24px;height:24px;font-size:10.5px}
  #bhRoot .flowst-t{font-size:12.5px}
  #bhRoot .dvt{font-size:10px;letter-spacing:1.2px;padding:10px 4px;min-width:64px}
  #bhRoot .stack-intro{padding:16px 16px 12px}
  #bhRoot .si-h{font-size:10.5px;letter-spacing:1.6px;margin-bottom:6px}
  #bhRoot .si-p{font-size:12px;line-height:1.65}
  #bhRoot .dtbl{width:calc(100% - 32px);margin:0 16px 16px;font-size:11.5px}
  #bhRoot .dtbl th{font-size:9.5px;padding:8px 10px}
  #bhRoot .dtbl td{padding:7px 10px}
  #bhRoot .bh-acct{gap:8px;min-width:140px}
  #bhRoot .bh-av{width:24px;height:24px;font-size:11px;border-radius:6px}
  #bhRoot .bh-acct-c{font-size:10.5px}
  #bhRoot .bh-pill{font-size:10.5px;padding:2px 7px}
  #bhRoot .act{padding:10px 14px;margin:0 16px 8px}
  #bhRoot .act-t{font-size:12px}
  #bhRoot .mg-grid{gap:10px;padding:0 16px 16px}
  #bhRoot .mg-kpi{font-size:18px}
  #bhRoot .dm-hd{padding:12px 14px;gap:8px}
  #bhRoot .dm-t{font-size:12.5px}
  #bhRoot .dm-chat{padding:14px}
  #bhRoot .dm-chat .msg{font-size:12px;padding:10px 12px}
  #bhRoot .dm-chat:empty::after{font-size:12px;padding:10px 12px}
  #bhRoot .dm-quick-hd{padding:8px 14px 4px}
  #bhRoot .dm-quick{padding:4px 14px 10px;gap:6px}
  #bhRoot .dm-quick .qk{font-size:11.5px;padding:8px 26px 8px 10px}
  #bhRoot .dm-inbar{padding:8px 14px 12px}
  #bhRoot .dm-inbar .in{min-height:38px;font-size:12px;padding:0 12px}
  #bhRoot .dm-inbar .send{width:38px}
}
@media (max-width:1366px){
  .bh-page > .p-6{padding:12px}
  #bhRoot .dash{gap:10px}
  #bhRoot .atab{min-height:40px}
  #bhRoot .atab .a2{font-size:12px}
  #bhRoot .flowst-t{font-size:12px}
  #bhRoot .flowst-dot{width:22px;height:22px;font-size:10px}
  #bhRoot .dvt{font-size:9.5px;letter-spacing:1px;padding:9px 3px;min-width:56px}
  #bhRoot .si-p{font-size:11.5px}
  #bhRoot .dtbl{font-size:11px}
  #bhRoot .dtbl th{font-size:9px}
  #bhRoot .dm-t{font-size:12px}
  #bhRoot .dm-chat .msg{font-size:11.5px}
  #bhRoot .dm-chat:empty::after{font-size:11.5px}
  #bhRoot .dm-quick .qk{font-size:11px}
  #bhRoot .dm-inbar .in{min-height:36px;font-size:11.5px}
  #bhRoot .dm-inbar .send{width:36px}
}
@media(max-width:1180px){#bhRoot .dash{gap:12px}}
</style>

<script>
function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = String(s == null ? '' : s);
    return d.innerHTML;
}

var MM_STEP_LABELS = {
    'mk-campaign':'CAMPAIGN','mk-performance':'PERFORMANCE','mk-audience':'AUDIENCE','mk-insights':'INSIGHTS','mk-abtest':'A/B TEST',
    'sl-today':'TODAY','sl-accounts':'ACCOUNTS','sl-scripts':'SCRIPTS','sl-objections':'OBJECTIONS','sl-close':'CLOSE & GROW',
    'ch-savefirst':'SAVE FIRST','ch-rootcause':'ROOT CAUSE','ch-offers':'OFFERS','ch-watchlist':'WATCHLIST','ch-abtest':'A/B TEST'
};

/* Real, step-grouped "Ask Mira" prompts from agents_pre_defined_prompts
   (is_mock_master = 1) — see routes/web.php's mock-master-helper route and
   database/seeders/MockMasterPredefinedPromptsSeeder. Keyed by the exact
   step_title stored in the DB, which is why MM_STEP_TITLE below must match
   those titles precisely (not the uppercase header labels). A/B test has no
   rows on purpose — no A/B-testing data source exists for Mock Master. */
var MM_PROMPTS_BY_AGENT = {
    mk: @json($mkPrompts),
    sl: @json($slPrompts),
    ch: @json($chPrompts)
};
var MM_STEP_TITLE = {
    'mk-campaign':'Campaign','mk-performance':'Performance','mk-audience':'Audience','mk-insights':'Insights','mk-abtest':'A/B test',
    'sl-today':'Today','sl-accounts':'Accounts','sl-scripts':'Scripts','sl-objections':'Objections','sl-close':'Close & grow',
    'ch-savefirst':'Save first','ch-rootcause':'Root cause','ch-offers':'Offers','ch-watchlist':'Watchlist','ch-abtest':'A/B test'
};
/* Prompts that name a specific student — a real name is picked from a
   list (see MM_CONTACT_NAMES) via mmNameForm(), then substituted for the
   literal "[name]" placeholder before the question is sent. */
var MM_NAME_PROMPT_SLUGS = {
    'mm-sl-acct-lookup-status': 1, 'mm-sl-acct-lookup-lastactive': 1, 'mm-sl-acct-lookup-contact': 1
};
var MM_CONTACT_NAMES = @json($mmContactNames ?? []);

/* Prompts that are answerable straight from data we've already computed
   server-side (see routes/web.php) — clicking these opens the "view list"
   popup with the real rows instantly, no AI round-trip and nothing
   paraphrased into a paragraph. Every other prompt still goes through
   mmAsk() (the free-text AI chat, grounded in MockMasterChatService). */
var MM_LISTS = {
    chAtRisk: @json($chAtRisk),
    chWatchlist: @json($chWatchlist),
    slProspects: @json($slProspects),
    slClose: @json($slClose),
    mkTopScorers: @json($mkTopScorers),
    mkNewStudents: @json($mkNewStudents)
};
var MM_LIST_SLUGS = {
    'mm-mk-aud-highscorers':        { list: 'mkTopScorers', noun: 'student' },
    'mm-mk-aud-expiring-7d':        { list: 'chAtRisk',     noun: 'student' },
    'mm-mk-aud-renewal-watch-30d':  { list: 'chWatchlist',  noun: 'student' },
    'mm-mk-aud-new-14d':            { list: 'mkNewStudents',noun: 'student' },
    'mm-sl-today-who-call':         { list: 'slProspects',  noun: 'prospect' },
    'mm-sl-today-ready-upgrade':    { list: 'slClose',       noun: 'student' },
    'mm-sl-today-active-no-package':{ list: 'slClose',       noun: 'student' },
    'mm-sl-close-trial-convert':    { list: 'slClose',       noun: 'student' },
    'mm-sl-close-most-tests-no-upgrade': { list: 'slClose',  noun: 'student' },
    'mm-sl-close-renewal-upsell':   { list: 'chWatchlist',  noun: 'student' },
    'mm-ch-save-who-churn':         { list: 'chAtRisk',     noun: 'student' },
    'mm-ch-save-inactive-highrisk': { list: 'chAtRisk',     noun: 'student' },
    'mm-ch-watch-drifting':         { list: 'chWatchlist',  noun: 'student' },
    'mm-ch-watch-highvalue':        { list: 'chWatchlist',  noun: 'student' }
};
/* Column labels for the "view list" popup — only keys actually present on
   the rows are shown, so one table works for every dataset above. */
var MM_LIST_COL_LABELS = {
    name: 'Student', sub: 'Package / interest', value: 'Package value', stage: 'Stage',
    readiness: 'Readiness', trust: 'Trust', approach: 'Approach', lastActive: 'Last active',
    intent: 'Intent', play: 'Play', detail: 'Detail', avg_score: 'Avg score', joined: 'Joined',
    inactiveDays: 'Days inactive', valueAtRisk: 'Value at risk', risk: 'Risk',
    email: 'Email', phone: 'Mobile number'
};
/* Columns always shown first (contact info), regardless of where they fall
   in MM_LIST_COL_LABELS above — every list here is a list of people to
   actually reach out to, so email/phone should never be scrolled out of
   view in a wide table. */
var MM_LIST_COL_PRIORITY = ['name', 'email', 'phone'];
var MM_LIST_MORE_BASE = '{{ url('/app/mock-master-helper/more') }}';

function mmRenderQuick(agent, key) {
    var full = agent + '-' + key;
    var title = MM_STEP_TITLE[full];
    var list = (MM_PROMPTS_BY_AGENT[agent] && MM_PROMPTS_BY_AGENT[agent][title]) || [];
    var box = document.getElementById('mmQuick-' + agent);
    if (!box) return;
    if (!list.length) {
        box.innerHTML = '<div style="padding:14px 4px;color:var(--g3);font-size:12px">No suggested questions for this step yet.</div>';
        return;
    }
    box.innerHTML = list.map(function (p) {
        return '<button type="button" class="qk" onclick="mmAskPrompt(\'' + agent + '\',\'' + p.slug + '\')">' + p.label.replace(/</g, '&lt;') + '</button>';
    }).join('');
}

function mmFindPrompt(agent, slug) {
    var list = [].concat.apply([], Object.values(MM_PROMPTS_BY_AGENT[agent] || {}));
    return list.find(function (p) { return p.slug === slug; });
}

function mmAskPrompt(agent, slug) {
    var prompt_ = mmFindPrompt(agent, slug);
    if (!prompt_) return;

    if (MM_NAME_PROMPT_SLUGS[slug]) {
        return mmNameForm(agent, slug, prompt_.label);
    }
    if (MM_LIST_SLUGS[slug]) {
        return mmShowList(agent, slug, prompt_.label);
    }
    mmAsk(agent, prompt_.label);
}

/* "Which student?" picker — a real name list (MM_CONTACT_NAMES) via
   <datalist>, same pattern as Business Helpers' Retention name form.
   Replaces a free-text prompt() dialog with a pick-from-list input right
   inside the chat. */
function mmNameForm(agent, slug, label) {
    var chat = document.getElementById('mmChat-' + agent);
    var fid = 'mmnm' + Math.random().toString(36).slice(2, 8);
    var opts = MM_CONTACT_NAMES.map(function (n) { return '<option value="' + n.replace(/"/g, '&quot;') + '"></option>'; }).join('');

    var wrap = document.createElement('div');
    wrap.className = 'msg bot';
    wrap.innerHTML = '<p>Which student?</p>' +
        '<div class="nmform">' +
            '<input class="nmin" id="' + fid + '" list="' + fid + '-l" placeholder="Start typing a student name…" autocomplete="off">' +
            '<datalist id="' + fid + '-l">' + opts + '</datalist>' +
            '<button type="button" class="qk" onclick="mmNameSubmit(\'' + agent + '\',\'' + fid + '\',\'' + slug + '\')">Ask</button>' +
        '</div>';
    chat.appendChild(wrap);
    chat.scrollTop = chat.scrollHeight;

    var input = document.getElementById(fid);
    input.focus();
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); mmNameSubmit(agent, fid, slug); }
    });
}

function mmNameSubmit(agent, fid, slug) {
    var input = document.getElementById(fid);
    var name = input ? input.value.trim() : '';
    if (!name) { if (input) input.focus(); return; }
    if (input) input.disabled = true;
    var prompt_ = mmFindPrompt(agent, slug);
    var label = (prompt_ ? prompt_.label : '').replace(/\[name\]/i, name);
    mmAsk(agent, label);
}

/* "View list" popup — the real rows behind a who/which question, shown as
   a clean table instead of a paragraph. No AI call: this data is already
   computed server-side (see routes/web.php's mock-master-helper route). */
function mmShowList(agent, slug, label) {
    var chat = document.getElementById('mmChat-' + agent);
    var cfg = MM_LIST_SLUGS[slug];
    var rows = MM_LISTS[cfg.list] || [];

    var userBubble = document.createElement('div');
    userBubble.className = 'msg user';
    userBubble.textContent = label;
    chat.appendChild(userBubble);

    var botBubble = document.createElement('div');
    botBubble.className = 'msg bot';
    if (!rows.length) {
        botBubble.innerHTML = '<p>No ' + cfg.noun + 's match this right now.</p>';
    } else {
        var id = 'mmlist' + Math.random().toString(36).slice(2, 9);
        // offset picks up where the page's initial embed left off — the
        // "Load more" button fetches from here onward, straight from the DB.
        MM_LIST_CACHE[id] = { rows: rows.slice(), noun: cfg.noun, label: label, dataset: cfg.list, offset: rows.length, hasMore: true };
        botBubble.innerHTML = '<p>' + rows.length + ' ' + cfg.noun + (rows.length === 1 ? '' : 's') + ' found.</p>' +
            '<button type="button" class="qk" onclick="openMmListModal(\'' + id + '\')">View the list →</button>';
    }
    chat.appendChild(botBubble);
    chat.scrollTop = chat.scrollHeight;
}

var MM_LIST_CACHE = {};
function mmListCols(rows) {
    var present = Object.keys(MM_LIST_COL_LABELS).filter(function (k) { return k in rows[0]; });
    if (!present.length) present = Object.keys(rows[0]);
    var priority = MM_LIST_COL_PRIORITY.filter(function (k) { return present.indexOf(k) > -1; });
    var rest = present.filter(function (k) { return priority.indexOf(k) === -1; });
    return priority.concat(rest);
}
function mmRenderListTable(entry) {
    var rows = entry.rows;
    var cols = mmListCols(rows);
    var head = '<tr><th>#</th>' + cols.map(function (k) {
        var label = MM_LIST_COL_LABELS[k] || k.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
        return '<th>' + label + '</th>';
    }).join('') + '</tr>';
    var body = rows.map(function (r, i) {
        return '<tr><td>' + (i + 1) + '</td>' + cols.map(function (k) {
            var v = r[k];
            return '<td>' + (v === null || v === undefined || v === '' ? '—' : escapeHtml(v)) + '</td>';
        }).join('') + '</tr>';
    }).join('');

    var footer = entry.hasMore
        ? '<button type="button" class="qk" id="mmListMoreBtn" onclick="loadMoreMmList(\'' + entry._id + '\')">Load more →</button>'
        : '<p style="color:var(--g3);font-size:11.5px;margin-top:10px">That\'s everyone — no more results.</p>';

    document.getElementById('mmListModalBody').innerHTML = '<table>' + head + body + '</table>' + footer;
}
function openMmListModal(id) {
    var entry = MM_LIST_CACHE[id];
    if (!entry || !entry.rows.length) return;
    entry._id = id;

    document.getElementById('mmListModalTitle').textContent = entry.label;
    mmRenderListTable(entry);
    document.getElementById('mmListModalOverlay').classList.add('show');
}
function loadMoreMmList(id) {
    var entry = MM_LIST_CACHE[id];
    if (!entry) return;
    var btn = document.getElementById('mmListMoreBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Loading…'; }

    fetch(MM_LIST_MORE_BASE + '/' + encodeURIComponent(entry.dataset) + '?offset=' + entry.offset)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var newRows = data.rows || [];
            entry.rows = entry.rows.concat(newRows);
            entry.offset += newRows.length;
            entry.hasMore = !!data.hasMore;
            mmRenderListTable(entry);
        })
        .catch(function () {
            if (btn) { btn.disabled = false; btn.textContent = 'Load more → (try again)'; }
        });
}
function closeMmListModal() {
    var overlay = document.getElementById('mmListModalOverlay');
    if (overlay) overlay.classList.remove('show');
}

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
    mmRenderQuick(agent, key);
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
        var answer = data.answer || "I couldn't get an answer just now.";
        botBubble.innerHTML = '<p>' + escapeHtml(answer).replace(/\n{2,}/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
        if (data.results && data.results.payments_list && data.results.payments_list.length) {
            mmAddResultsButton(botBubble, data.results);
        }
        chat.scrollTop = chat.scrollHeight;
    })
    .catch(function () {
        botBubble.innerHTML = '<p>I couldn\'t reach the AI just now — try again in a moment.</p>';
        chat.scrollTop = chat.scrollHeight;
    });
}

/* ── Chat results — "View results" button under an answer built from a
   date-range lookup, opening the full real list in a modal (same pattern
   as Business Helpers' "accounts behind this" modal). ── */
function mmAddResultsButton(bubble, results) {
    var btn = document.createElement('button');
    btn.type = 'button';
    var shown = results.requested_count ? results.payments_list.length : results.paid_payments_count;
    btn.textContent = 'View the ' + Number(shown).toLocaleString() + ' payment' + (shown === 1 ? '' : 's') + ' behind this →';
    btn.style.cssText = 'margin-top:10px;padding:6px 12px;border-radius:6px;border:none;background:#7c3aed;color:#fff;font-size:12px;font-weight:600;cursor:pointer';
    btn.onclick = function () { mmShowResultsModal(results); };
    bubble.appendChild(btn);
}

function mmShowResultsModal(results) {
    var overlay = document.getElementById('mmResultsModal');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'mmResultsModal';
        overlay.className = 'risk-modal-overlay';
        overlay.onclick = function (e) { if (e.target === overlay) mmCloseResultsModal(); };
        overlay.innerHTML =
            '<div class="risk-modal">' +
                '<div class="risk-modal-hd"><span id="mmResultsTitle"></span>' +
                '<button type="button" onclick="mmCloseResultsModal()" aria-label="Close">✕</button></div>' +
                '<div class="risk-modal-body" id="mmResultsBody"></div>' +
            '</div>';
        document.body.appendChild(overlay);
    }

    var rows = results.payments_list.map(function (p, i) {
        return '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + escapeHtml(p.paid_on) + '</td>' +
            '<td>' + escapeHtml(p.student || '—') + '</td>' +
            '<td>' + escapeHtml(p.email || '—') + '</td>' +
            '<td>' + escapeHtml(p.product || '—') + '</td>' +
            '<td>$' + Number(p.amount).toLocaleString() + '</td>' +
            '</tr>';
    }).join('');

    var note = results.payments_list_truncated
        ? '<p style="font-size:11px;color:#6b7280;margin:8px 0 0">Showing the first ' + results.payments_list.length + ' of ' + Number(results.paid_payments_count).toLocaleString() + ' payments.</p>'
        : '';

    document.getElementById('mmResultsTitle').textContent = 'Paid subscriptions — ' + (results.period_label || '') + ' · ' + Number(results.paid_payments_count).toLocaleString() + ' payments · $' + Number(results.total_paid_amount).toLocaleString() + ' total';
    document.getElementById('mmResultsBody').innerHTML =
        '<table><thead><tr><th>#</th><th>Paid on</th><th>Student</th><th>Email</th><th>Subscription</th><th>Amount</th></tr></thead><tbody>' + rows + '</tbody></table>' + note;
    overlay.classList.add('show');
}

function mmCloseResultsModal() {
    var overlay = document.getElementById('mmResultsModal');
    if (overlay) overlay.classList.remove('show');
}

/* ── Campaign filter — re-renders only the Campaign table rows from the
   JSON endpoint, so the rest of the page never reloads. ── */
var MM_CAMPAIGN_URL = '{{ route('client.mock-master-helper.campaign-students') }}';

function mmEsc(v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

function mmCampaignPager(data) {
    var info = document.getElementById('mmCampaignPageInfo');
    var prev = document.getElementById('mmCampaignPrev');
    var next = document.getElementById('mmCampaignNext');
    if (info) info.textContent = 'Page ' + data.page + ' of ' + data.last_page + ' · ' + Number(data.total).toLocaleString() + ' students';
    if (prev) { prev.disabled = data.page <= 1; prev.onclick = function () { mmCampaignGo(data.page - 1); }; }
    if (next) { next.disabled = data.page >= data.last_page; next.onclick = function () { mmCampaignGo(data.page + 1); }; }
}

function mmCampaignRender(data) {
    var body = document.getElementById('mmCampaignBody');
    if (!body) return;
    var students = data.students;
    mmCampaignPager(data);
    if (!students.length) {
        body.innerHTML = '<tr><td colspan="8" style="color:var(--g3);padding:20px">No renewal-ready students match these filters.</td></tr>';
        return;
    }
    body.innerHTML = students.map(function (s) {
        var approach = s.approach === 'Offer-led'
            ? '<span class="bh-pill good">Offer-led</span>'
            : '<span class="bh-pill warn">Proof-led</span>';
        return '<tr>' +
            '<td class="acctn"><div class="bh-acct"><span class="bh-av" style="background:' + mmEsc(s.color) + '">' + mmEsc(s.initial) + '</span>' +
                '<div><div class="bh-acct-n">' + mmEsc(s.name) + '</div><div class="bh-acct-c">(' + mmEsc(s.sub) + ')</div></div></div></td>' +
            '<td>' + mmEsc(s.value) + '</td>' +
            '<td>' + mmEsc(s.paymentDate) + '</td>' +
            '<td><span class="bh-pill ' + mmEsc(s.stageKind) + '">' + mmEsc(s.stage) + '</span></td>' +
            '<td>' + mmEsc(s.readiness) + '</td>' +
            '<td>' + mmEsc(s.trust) + '</td>' +
            '<td>' + approach + '</td>' +
            '<td>' + mmEsc(s.lastActive) + '</td>' +
            '</tr>';
    }).join('');
}

function mmCampaignLoad(query) {
    var body = document.getElementById('mmCampaignBody');
    if (body) body.innerHTML = '<tr><td colspan="8" style="color:var(--g3);padding:20px">Loading…</td></tr>';
    fetch(MM_CAMPAIGN_URL + (query ? '?' + query : ''), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
    .then(mmCampaignRender)
    .catch(function () {
        if (body) body.innerHTML = '<tr><td colspan="8" style="color:#b91c1c;padding:20px">Couldn\'t load students — please try again.</td></tr>';
    });
}

function mmCampaignParams(page) {
    var form = document.getElementById('mmCampaignFilter');
    var params = new URLSearchParams();
    new FormData(form).forEach(function (v, k) { if (v) params.append(k, v); });
    if (page && page > 1) params.set('page', page);
    return params;
}

function mmCampaignGo(page) {
    var params = mmCampaignParams(page);
    mmCampaignLoad(params.toString());
    if (window.history && history.replaceState) {
        history.replaceState(null, '', '?' + params.toString());
    }
}

function mmCampaignSubmit(e) {
    e.preventDefault();
    mmCampaignGo(1);
    return false;
}

function mmCampaignReset() {
    var form = document.getElementById('mmCampaignFilter');
    if (form) form.querySelectorAll('select, input').forEach(function (el) { el.value = ''; });
    mmCampaignLoad('');
    if (window.history && history.replaceState) {
        history.replaceState(null, '', window.location.pathname);
    }
}

/* ── Sync Data — pulls the 14 live Mock Master source tables from the
   remote PTE Portal database into their local mm_* mirrors (see
   App\Services\MockMaster\MockMasterSyncService), then reloads the page
   so every panel reflects the freshly-synced data. While it runs, the
   whole interface is blurred out via #mmSyncOverlay so nothing looks
   clickable mid-copy. ── */
function mmSyncOverlay() {
    var el = document.getElementById('mmSyncOverlay');
    if (!el) {
        el = document.createElement('div');
        el.id = 'mmSyncOverlay';
        el.innerHTML =
            '<div class="mm-sync-card">' +
                '<div class="mm-sync-spinner" id="mmSyncSpinner"></div>' +
                '<div class="mm-sync-text" id="mmSyncText">Syncing Mock Master data…</div>' +
                '<div class="mm-sync-sub" id="mmSyncSub">Pulling the latest 14 tables from the live server</div>' +
            '</div>';
        document.body.appendChild(el);
    }
    return el;
}

function mmSyncData(btn) {
    btn.disabled = true;
    var label = btn.querySelector('span');
    var icon = btn.querySelector('svg');
    if (label) label.textContent = 'Syncing…';
    if (icon) icon.style.animation = 'mmspin 0.8s linear infinite';

    mmSyncOverlay();
    document.body.classList.add('mm-syncing');

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
            var spinner = document.getElementById('mmSyncSpinner');
            var text = document.getElementById('mmSyncText');
            var sub = document.getElementById('mmSyncSub');
            if (spinner) { spinner.classList.add('done'); spinner.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>'; }
            if (text) text.textContent = 'Sync complete';
            if (sub) sub.textContent = data.total_rows ? data.total_rows.toLocaleString() + ' rows updated' : 'Reloading…';
            // Brief pause so the "complete" state is actually visible before
            // the reload clears it, rather than blur-to-blank in one frame.
            setTimeout(function () { window.location.reload(); }, 700);
        } else {
            document.body.classList.remove('mm-syncing');
            if (label) label.textContent = 'Sync failed';
            if (icon) icon.style.animation = '';
            btn.disabled = false;
            alert(data.message || 'Sync failed — please try again.');
        }
    })
    .catch(function () {
        document.body.classList.remove('mm-syncing');
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

mmRenderQuick('mk', 'campaign');
mmRenderQuick('sl', 'today');
mmRenderQuick('ch', 'savefirst');
bhMiraApply(); bhLeftApply(); bhQuickApply();
</script>

@endsection
