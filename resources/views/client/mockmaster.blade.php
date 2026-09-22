{{-- resources/views/client/mockmaster.blade.php --}}
@extends('layouts.platform')

@section('title', 'MockMaster')

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
@endphp

<div class="flex flex-col h-full overflow-hidden bg-gray-50">

    {{-- Page Header — same shell as Business Helpers --}}
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">MockMaster</h1>
            <p class="text-xs text-gray-400 mt-0.5">Ask anything — free-form, no predefined questions yet, grounded in your real MockMaster PTE Portal data</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="toggleSidebarCollapse()" id="bhFullBtn" title="Collapse sidebar"
               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
            </button>
            <a href="{{ route('client.business-helpers') }}"
               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
               title="Business Helpers">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </a>
            <a href="{{ route('client.dashboard') }}"
               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
               title="Dashboard">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>

            <div class="relative" id="bhAvatarWrap">
                <button onclick="var d=document.getElementById('bhDropdown');d.style.display=d.style.display==='block'?'none':'block'"
                        class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white text-xs font-bold hover:bg-amber-600 transition-colors">
                    {{ $initials ?: 'JD' }}
                </button>
                <div id="bhDropdown" class="hidden absolute right-0 top-10 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
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
    <div id="bhRoot" class="h-full flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        {{-- Selector bar: agent tabs --}}
        <div class="bar">
            <div class="atabs">
                <button type="button" class="atab" id="bhAgentTab-mk" onclick="mmSetAgent('mk')">
                    <span class="amono">M</span><span class="a2">Marketing</span>
                </button>
                <button type="button" class="atab" id="bhAgentTab-sl" onclick="mmSetAgent('sl')">
                    <span class="amono">S</span><span class="a2">Sales</span>
                </button>
                <button type="button" class="atab" id="bhAgentTab-ch" onclick="mmSetAgent('ch')">
                    <span class="amono">R</span><span class="a2">Customer Retention</span>
                </button>
            </div>
        </div>

        {{-- Free-form: single chat panel, no predefined questions/steps —
             those will be attached back later. --}}
        <div class="mm-solo">
            <div class="cons-hd">
                <div>
                    <div class="ch-name" id="mmName"></div>
                    <div class="ch-sub" id="mmSub">Ask anything — no predefined questions yet</div>
                </div>
                <span class="ch-live">Ready</span>
            </div>
            <div class="chat" id="mmChat"></div>
            <div class="inbar">
                <input class="in" id="mmInput" type="text" placeholder="Ask anything about MockMaster data..." autocomplete="off">
                <button type="button" class="send" onclick="mmSend()" aria-label="Send">
                    <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </div>
        </div>

    </div>
    </div>

</div>

<style>
/* ══ Same design system as Business Helpers (#bhRoot) — same tokens, same
   chat bubble/agent-tab styling. The predefined step list / quick-suggestion
   dashboard has been removed for now — free-text ask only. ══ */
#bhRoot{
    --f1:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
    --fm:'IBM Plex Mono',ui-monospace,monospace;
    --p1:#f9fafb;--p2:#f3f4f6;--ink:#111827;--g2:#6b7280;--g3:#9ca3af;--g4:#d1d5db;
    --ln:#e5e7eb;--ln2:#d1d5db;--sig:#059669;--warn:#d97706;--crit:#dc2626;
    --ac:#4f46e5;--ac-l:#eef2ff;--ac-m:#c7d2fe;--ac-d:#4338ca;
    font-family:var(--f1);color:var(--ink);
    display:flex;flex-direction:column;
}
#bhRoot[data-agent="sl"]{--ac:#2563eb;--ac-l:#eff6ff;--ac-m:#bfdbfe;--ac-d:#1d4ed8}
#bhRoot[data-agent="ch"]{--ac:#e11d48;--ac-l:#fff1f2;--ac-m:#fecdd3;--ac-d:#be123c}

#bhRoot .bar{display:flex;align-items:stretch;gap:1px;background:var(--ln);border-bottom:1px solid var(--ln);flex-wrap:wrap;flex-shrink:0}
#bhRoot .atabs{display:flex;gap:1px;background:var(--ln);flex:1;position:relative}
#bhRoot .atab{background:#fff;padding:0 18px;cursor:pointer;transition:all .15s;text-align:left;border:none;font-family:var(--f1);display:flex;align-items:center;justify-content:center;gap:10px;min-height:56px;flex:1;position:relative}
#bhRoot .atab:hover{background:var(--p1)}
#bhRoot .atab.on{background:var(--ac-l)}
#bhRoot .atab.on::after{content:'';position:absolute;left:0;right:0;bottom:0;height:2px;background:var(--ac)}
#bhRoot .amono{width:28px;height:28px;flex-shrink:0;display:grid;place-items:center;font-size:12px;font-weight:700;background:var(--p2);color:var(--g2);border-radius:8px;transition:all .15s}
#bhRoot .atab.on .amono{background:var(--ac);color:#fff}
#bhRoot .atab .a2{font-size:13px;font-weight:600;color:var(--ink)}
#bhRoot .atab.on .a2{color:var(--ac-d)}

#bhRoot .mm-solo{background:#fff;display:flex;flex-direction:column;overflow:hidden;flex:1;min-height:0}
#bhRoot .cons-hd{padding:12px 16px;border-bottom:1px solid var(--ln);display:flex;align-items:center;gap:12px;background:var(--p1);flex-shrink:0}
#bhRoot .ch-name{font-size:13px;font-weight:600;color:var(--ink);display:flex;align-items:center;gap:8px}
#bhRoot .ch-name::before{content:'';width:8px;height:8px;border-radius:50%;background:var(--ac)}
#bhRoot .ch-sub{font-size:11px;color:var(--g3);margin-top:2px}
#bhRoot .ch-live{margin-left:auto;font-size:11px;font-weight:600;color:var(--sig);background:#ecfdf5;border:1px solid #a7f3d0;border-radius:99px;padding:3px 10px;display:flex;align-items:center;gap:6px;flex-shrink:0}
#bhRoot .ch-live::before{content:'';width:6px;height:6px;background:var(--sig);border-radius:50%;animation:bhblink 1.8s infinite}
@keyframes bhblink{0%,100%{opacity:1}50%{opacity:.2}}

#bhRoot .chat{flex:1;overflow-y:auto;padding:18px;display:flex;flex-direction:column;gap:13px}
#bhRoot .msg{max-width:78%;padding:11px 13px;font-size:12.5px;line-height:1.65;border-radius:10px}
#bhRoot .msg.user{background:var(--ink);color:#fff;align-self:flex-end}
#bhRoot .msg.bot{background:var(--p1);border:1px solid var(--ln);align-self:flex-start;color:var(--ink)}
#bhRoot .msg.bot .tag{font-family:var(--fm);font-size:9.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:9px;color:var(--ac-d)}
#bhRoot .msg.bot p{margin-bottom:8px;white-space:pre-line}
#bhRoot .msg.bot p:last-child{margin-bottom:0}
#bhRoot .msg.bot .ai-badge{display:inline-block;margin-top:6px;font-size:9.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--ac-d);background:var(--ac-l);border:1px solid var(--ac-m);border-radius:99px;padding:2px 8px}

#bhRoot .inbar{display:flex;gap:1px;border-top:1px solid var(--ln);background:var(--ln);flex-shrink:0}
#bhRoot .in{flex:1;border:none;padding:12px 14px;font-family:var(--f1);font-size:12.5px;outline:none;min-width:0;background:#fff}
#bhRoot .send{width:44px;border:none;background:var(--ac);color:#fff;cursor:pointer;display:grid;place-items:center;transition:background .15s;flex-shrink:0}
#bhRoot .send:hover{background:var(--ac-d)}
#bhRoot .send svg{width:13px;height:13px;stroke:#fff;fill:none;stroke-width:2.5;stroke-linecap:round}

/* Sliding active agent-tab indicator */
#bhRoot .bh-slide-ind{position:absolute;bottom:0;height:3px;border-radius:3px 3px 0 0;background:var(--ac);pointer-events:none;z-index:2;opacity:0;
  transition:left .3s cubic-bezier(.4,0,.2,1),width .3s cubic-bezier(.4,0,.2,1),opacity .2s}

@keyframes bhFadeSlide{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
#bhRoot .bh-fade-in{animation:bhFadeSlide .3s cubic-bezier(.4,0,.2,1)}
@media (prefers-reduced-motion: reduce){
  #bhRoot .bh-slide-ind{transition:none}
  #bhRoot .bh-fade-in{animation:none}
}
</style>

<script>
(function () {

var AGENT_META = {
    mk: { key: 'mk', name: 'Marketing' },
    sl: { key: 'sl', name: 'Sales' },
    ch: { key: 'ch', name: 'Customer Retention' }
};
var BACKEND_AGENT = { mk: 'marketing', sl: 'sales', ch: 'retention' };
var ASK_ENDPOINT = @json(route('client.mockmaster.ask'));
var CSRF = document.querySelector('meta[name="csrf-token"]').content;

var root = document.getElementById('bhRoot');
var mmState = { agent: 'mk' };

function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = String(s == null ? '' : s);
    return d.innerHTML;
}

window.mmSetAgent = function (key) {
    mmState.agent = key;
    root.setAttribute('data-agent', key);
    ['mk', 'sl', 'ch'].forEach(function (k) {
        document.getElementById('bhAgentTab-' + k).classList.toggle('on', k === key);
    });
    document.getElementById('mmName').textContent = AGENT_META[key].name + ' helper';
    resetChat();
};

function resetChat() {
    var chat = document.getElementById('mmChat');
    chat.innerHTML = '';
    pushMsg('bot', '<div class="tag">' + escapeHtml(AGENT_META[mmState.agent].name) + ' helper</div>' +
        '<p>Ask me anything about MockMaster ' + escapeHtml(AGENT_META[mmState.agent].name.toLowerCase()) + ' data — no predefined questions yet, just ask in plain English.</p>');
}

function pushMsg(role, html) {
    var chat = document.getElementById('mmChat');
    var el = document.createElement('div');
    el.className = 'msg ' + role;
    el.innerHTML = html;
    chat.appendChild(el);
    chat.scrollTop = chat.scrollHeight;
    return el;
}

window.mmSend = function () {
    var input = document.getElementById('mmInput');
    var text = input.value.trim();
    if (!text) return;
    pushMsg('user', escapeHtml(text));
    input.value = '';
    var el = pushMsg('bot', '<p style="color:var(--g3)">Thinking…</p>');
    fetch(ASK_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ agent: BACKEND_AGENT[mmState.agent] || mmState.agent, question: text })
    }).then(function (r) { return r.json(); }).then(function (data) {
        el.innerHTML = '<p>' + escapeHtml(data.answer || "I couldn't get an answer just now.").replace(/\n/g, '<br>') + '</p>' +
            (data.ai_used ? '<span class="ai-badge">AI narrated</span>' : '');
    }).catch(function () {
        el.innerHTML = "<p>I couldn't reach the server just now — try again in a moment.</p>";
    });
};

document.getElementById('mmInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); mmSend(); }
});

document.addEventListener('click', function (e) {
    var wrap = document.getElementById('bhAvatarWrap');
    var drop = document.getElementById('bhDropdown');
    if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});

var ICON_EXPAND = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>';
var ICON_COMPRESS = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3v3a2 2 0 0 1-2 2H4"/><path d="M15 3v3a2 2 0 0 0 2 2h3"/><path d="M9 21v-3a2 2 0 0 0-2-2H4"/><path d="M15 21v-3a2 2 0 0 1 2-2h3"/></svg>';
window.toggleSidebarCollapse = function () {
    var sidebar = document.getElementById('platformSidebar');
    if (!sidebar) return;
    var collapsed = sidebar.classList.toggle('bh-sidebar-collapsed');
    if (collapsed) {
        sidebar.style.width = '0px'; sidebar.style.minWidth = '0px'; sidebar.style.overflow = 'hidden'; sidebar.style.borderRightWidth = '0px';
    } else {
        sidebar.style.width = ''; sidebar.style.minWidth = ''; sidebar.style.overflow = ''; sidebar.style.borderRightWidth = '';
    }
    document.getElementById('bhFullBtn').innerHTML = collapsed ? ICON_COMPRESS : ICON_EXPAND;
    document.getElementById('bhFullBtn').title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
};

/* Sliding active-tab indicator + fade-in-on-change — read-only observers,
   same approach as Business Helpers. */
function bhMountSlideIndicator(container){
    if (!container || container.__bhSlide) return;
    container.__bhSlide = true;
    var ind = document.createElement('span');
    ind.className = 'bh-slide-ind';
    container.appendChild(ind);
    function place(){
        var active = container.querySelector('.on');
        if (!active) { ind.style.opacity = '0'; return; }
        ind.style.opacity = '1';
        ind.style.left = active.offsetLeft + 'px';
        ind.style.width = active.offsetWidth + 'px';
    }
    new MutationObserver(function (){
        if (!container.contains(ind)) container.appendChild(ind);
        requestAnimationFrame(place);
    }).observe(container, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
    window.addEventListener('resize', place);
    requestAnimationFrame(place);
}
bhMountSlideIndicator(document.querySelector('.atabs'));

function bhAnimateOnChange(el){
    if (!el || el.__bhAnim) return;
    el.__bhAnim = true;
    new MutationObserver(function (){
        el.classList.remove('bh-fade-in');
        void el.offsetWidth;
        el.classList.add('bh-fade-in');
    }).observe(el, { childList: true });
}
bhAnimateOnChange(document.getElementById('mmChat'));

mmSetAgent(@json($agent === 'sales' ? 'sl' : ($agent === 'retention' ? 'ch' : 'mk')));

})();
</script>

@endsection
