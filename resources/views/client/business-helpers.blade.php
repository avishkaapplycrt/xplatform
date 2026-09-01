{{-- resources/views/client/business-helpers.blade.php --}}
@extends('layouts.platform')

@section('title', 'Business Helpers')

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

    {{-- Page Header --}}
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Business Helpers</h1>
            <p class="text-xs text-gray-400 mt-0.5">Guided playbooks that tell you exactly what to do next</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="toggleSidebarCollapse()" id="bhFullBtn" title="Collapse sidebar"
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

            <div class="relative" id="bhAvatarWrap">
                <button onclick="var d=document.getElementById('bhDropdown');d.style.display=d.style.display==='block'?'none':'block'"
                        class="w-8 h-8 rounded-full bg-cyan-500 flex items-center justify-center text-white text-xs font-bold hover:bg-cyan-600 transition-colors">
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

        {{-- Selector bar: agent tabs + task tabs --}}
        <div class="bar">
            <div class="atabs">
                <button type="button" class="atab" id="bhAgentTab-mk" onclick="setAgent('mk')">
                    <span class="amono">M</span><span class="a2">Marketing</span>
                </button>
                <button type="button" class="atab" id="bhAgentTab-sl" onclick="setAgent('sl')">
                    <span class="amono">S</span><span class="a2">Sales</span>
                </button>
                <button type="button" class="atab" id="bhAgentTab-ch" onclick="setAgent('ch')">
                    <span class="amono">R</span><span class="a2">Customer Retention</span>
                </button>
            </div>
        </div>

        {{-- CLASSIC: guide (left) + console (right) — used by Customer Retention --}}
        <div class="main" id="bhClassic">
            <div class="guide">
                <div class="g-hd" id="bhGHd"></div>
                <div class="g-body" id="bhGBody"></div>
            </div>
            <div class="c-main">
                <div class="cons-hd">
                    <div>
                        <div class="ch-name" id="bhWsName"></div>
                        <div class="ch-sub" id="bhWsSub"></div>
                    </div>
                    <span class="ch-live">Ready</span>
                </div>
                <div class="chat" id="bhChat"></div>
                <div class="quick" id="bhQuick"></div>
                <div class="inbar">
                    <input class="in" id="bhInput" type="text" placeholder="Ask anything — plain answers, no jargon..." autocomplete="off">
                    <button type="button" class="send" onclick="sendMsg()" aria-label="Send">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- DASHBOARD: step guide + KPI strip/stack/accounts/scripts/forecast/manager + Mira — used by Marketing & Sales --}}
        <div class="dash" id="bhDash">
            <aside class="dash-left">
                <div class="g-body" id="dashGBody"></div>
            </aside>
            <div class="dash-main">
                <div class="dash-vtabs" id="dashVtabs"></div>
                <div class="dash-view" id="dashView"></div>
            </div>
            <aside class="dash-mira">
                <div class="dm-hd">
                    <span class="dm-dot"></span>
                    <div><div class="dm-t" id="dmTitle"></div><div class="dm-s">ENGINE + AI · GROUNDED IN LIVE DATA</div></div>
                    <span class="dm-ready">Ready</span>
                </div>
                <div class="dm-chat" id="dashChat"></div>
                <div class="dm-quick-hd" id="dashQuickHd"></div>
                <div class="dm-quick" id="dashQuick"></div>
                <div class="dm-inbar">
                    <input class="in" id="dashInput" type="text" placeholder="Ask anything — plain answers, no jargon..." autocomplete="off">
                    <button type="button" class="send" onclick="dashSend()" aria-label="Send">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </aside>
        </div>

    </div>
    </div>

</div>

<style>
#bhRoot{
    --f1:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
    --fm:'IBM Plex Mono',ui-monospace,monospace;
    --p1:#f9fafb;--p2:#f3f4f6;--ink:#111827;--g2:#6b7280;--g3:#9ca3af;--g4:#d1d5db;
    --ln:#e5e7eb;--ln2:#d1d5db;--sig:#059669;--warn:#d97706;--crit:#dc2626;
    --ac:#4f46e5;--ac-l:#eef2ff;--ac-m:#c7d2fe;--ac-d:#4338ca;
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

#bhRoot .main{display:grid;grid-template-columns:1fr 400px;gap:1px;background:var(--ln);flex:1;min-height:0;overflow:hidden}
@media(max-width:900px){#bhRoot .main{grid-template-columns:1fr}}

#bhRoot .guide{background:#fff;display:flex;flex-direction:column;overflow:hidden;min-height:0}
#bhRoot .g-hd{background:#fff;border-bottom:1px solid var(--ln);padding:16px 20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;flex-shrink:0}
#bhRoot .g-t{font-size:15px;font-weight:600;color:var(--ink);flex:1;min-width:180px}
#bhRoot .g-w{font-size:12px;color:var(--g2);display:block;margin-top:3px;font-weight:400}
#bhRoot .g-prog{display:flex;align-items:center;gap:10px}
#bhRoot .g-count{font-size:11.5px;color:var(--g2);white-space:nowrap}
#bhRoot .g-count b{color:var(--ink);font-weight:600}
#bhRoot .g-bar{width:100px;height:6px;background:var(--p2);overflow:hidden;border-radius:99px}
#bhRoot .g-fill{height:100%;width:0;background:var(--sig);transition:width .35s ease;border-radius:99px}
#bhRoot .g-body{flex:1;overflow-y:auto;padding:6px 0}

#bhRoot .st{display:flex;padding:0 20px;position:relative;cursor:pointer}
#bhRoot .st:hover .st-txt{background:var(--p1)}
#bhRoot .st.cur .st-txt{background:var(--ac-l);border-left:2px solid var(--ac);margin-left:8px;padding-left:14px}
#bhRoot .st.cur .st-dot{border-color:var(--ac);color:var(--ac);box-shadow:0 0 0 4px var(--ac-l)}
#bhRoot .st.cur .st-title{color:var(--ac-d)}
#bhRoot .st-next{display:inline-block;font-size:10.5px;font-weight:600;color:var(--ac-d);border:1px solid var(--ac-m);background:var(--ac-l);border-radius:99px;padding:2px 9px;margin-left:10px;vertical-align:2px}
#bhRoot .st-rail{display:flex;flex-direction:column;align-items:center;flex-shrink:0;width:36px}
#bhRoot .st-dot{width:24px;height:24px;border:1.5px solid var(--ln2);background:#fff;border-radius:50%;display:grid;place-items:center;font-size:10px;font-weight:600;color:var(--g2);transition:all .2s;z-index:1;flex-shrink:0;margin-top:13px}
#bhRoot .st-line{width:1.5px;flex:1;background:var(--ln);min-height:8px}
#bhRoot .st:last-of-type .st-line{background:transparent}
#bhRoot .st.done .st-dot{background:var(--sig);border-color:var(--sig);color:#fff}
#bhRoot .st.done .st-line{background:var(--sig)}
#bhRoot .st.done .st-title{color:var(--g3);font-weight:600}
#bhRoot .st-txt{flex:1;padding:11px 14px 13px;margin-left:10px;transition:background .15s;border-radius:8px}
#bhRoot .st-title{font-size:13.5px;font-weight:600;color:var(--ink);margin-bottom:3px;transition:color .2s}
#bhRoot .st-desc{font-size:12px;color:var(--g2);line-height:1.6;max-width:600px}
#bhRoot .st-where{display:inline-flex;align-items:center;gap:6px;margin-top:7px;font-size:11px;font-weight:600;color:var(--ac-d);background:var(--ac-l);border:1px solid var(--ac-m);border-radius:6px;padding:3px 10px}
#bhRoot .st-where::before{content:'\2192';color:var(--ac)}

#bhRoot .c-main{background:#fff;display:flex;flex-direction:column;overflow:hidden;min-height:0}
#bhRoot .cons-hd{padding:12px 16px;border-bottom:1px solid var(--ln);display:flex;align-items:center;gap:12px;background:var(--p1);flex-shrink:0}
#bhRoot .ch-name{font-size:13px;font-weight:600;color:var(--ink);display:flex;align-items:center;gap:8px}
#bhRoot .ch-name::before{content:'';width:8px;height:8px;border-radius:50%;background:var(--ac)}
#bhRoot .ch-sub{font-size:11px;color:var(--g3);margin-top:2px}
#bhRoot .ch-live{margin-left:auto;font-size:11px;font-weight:600;color:var(--sig);background:#ecfdf5;border:1px solid #a7f3d0;border-radius:99px;padding:3px 10px;display:flex;align-items:center;gap:6px;flex-shrink:0}
#bhRoot .ch-live::before{content:'';width:6px;height:6px;background:var(--sig);border-radius:50%;animation:bhblink 1.8s infinite}
@keyframes bhblink{0%,100%{opacity:1}50%{opacity:.2}}
#bhRoot .chat{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:11px}
#bhRoot .msg{max-width:94%;padding:11px 13px;font-size:12.5px;line-height:1.65;border-radius:10px}
#bhRoot .msg.user{background:var(--ink);color:#fff;align-self:flex-end}
#bhRoot .msg.bot{background:var(--p1);border:1px solid var(--ln);align-self:flex-start;color:var(--ink)}
#bhRoot .msg.bot .tag{font-family:var(--fm);font-size:9.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:9px;color:var(--ac-d)}
#bhRoot .msg.bot p{margin-bottom:8px}
#bhRoot .msg.bot p:last-child{margin-bottom:0}
#bhRoot .msg.bot strong{color:var(--ink)}
#bhRoot .msg.bot table{width:100%;border-collapse:collapse;margin:7px 0;font-size:11px}
#bhRoot .msg.bot th,#bhRoot .msg.bot td{border:1px solid var(--ln);padding:5px 7px;text-align:left;vertical-align:top}
#bhRoot .msg.bot th{background:var(--p2);font-size:10px;font-weight:600;color:var(--g2)}
#bhRoot .how{align-self:stretch;border:1px solid var(--ln);background:#fff;margin-top:9px;border-radius:8px;overflow:hidden}
#bhRoot .how-hd{font-size:11px;font-weight:600;color:var(--ac-d);background:var(--ac-l);padding:8px 12px}
#bhRoot .how .hstep{display:flex;gap:10px;padding:9px 12px;border-bottom:1px solid var(--ln);font-size:11.5px;line-height:1.55}
#bhRoot .how .hstep:last-of-type{border-bottom:none}
#bhRoot .hstep-n{font-size:10.5px;font-weight:600;color:var(--g3);flex-shrink:0;padding-top:2px;width:16px}
#bhRoot .hstep-route{display:inline-block;font-size:10.5px;font-weight:500;background:var(--p1);border:1px solid var(--ln);border-radius:5px;padding:2px 7px;margin-top:4px;color:var(--g2)}
#bhRoot .how-watch{padding:9px 12px;background:var(--p1);border-top:1px solid var(--ln);font-size:11px;color:var(--g2);line-height:1.7}
#bhRoot .how-watch b{color:var(--sig);font-weight:600;display:block;font-size:12px;margin-top:2px}

#bhRoot .acts{display:flex;flex-direction:column;gap:6px;align-self:stretch;margin-top:9px}
#bhRoot .act{border:1px solid var(--ln);border-left:3px solid var(--ac);background:#fff;padding:10px 12px;display:flex;gap:10px;align-items:center;border-radius:8px}
#bhRoot .act-t{font-size:12px;font-weight:600;color:var(--ink)}
#bhRoot .act-d{font-size:10.5px;color:var(--g2);margin-top:2px}
#bhRoot .act-meta{font-size:10px;color:var(--g3);margin-top:4px}
#bhRoot .act-btn{margin-left:auto;flex-shrink:0;font-size:11px;font-weight:600;border-radius:6px;padding:7px 12px;border:1px solid var(--ac);background:var(--ac);color:#fff;cursor:pointer;transition:all .15s;white-space:nowrap}
#bhRoot .act-btn:hover{background:var(--ac-d);border-color:var(--ac-d)}
#bhRoot .act-btn.done{background:#ecfdf5;border-color:#a7f3d0;color:var(--sig);cursor:default}
#bhRoot .quick{padding:10px 12px;border-top:1px solid var(--ln);display:flex;gap:6px;flex-wrap:wrap;flex-shrink:0}
#bhRoot .qk{font-size:11px;font-weight:600;color:var(--g2);padding:7px 12px;border:1px solid var(--ln);cursor:pointer;background:#fff;transition:all .15s;border-radius:99px}
#bhRoot .qk:hover{color:var(--ac-d);border-color:var(--ac-m);background:var(--ac-l)}
#bhRoot .inbar{display:flex;gap:1px;border-top:1px solid var(--ln);background:var(--ln);flex-shrink:0}
#bhRoot .in{flex:1;border:none;padding:12px 14px;font-family:var(--f1);font-size:12.5px;outline:none;min-width:0;background:#fff}
#bhRoot .send{width:44px;border:none;background:var(--ac);color:#fff;cursor:pointer;display:grid;place-items:center;transition:background .15s;flex-shrink:0}
#bhRoot .send:hover{background:var(--ac-d)}
#bhRoot .send svg{width:13px;height:13px;stroke:#fff;fill:none;stroke-width:2.5;stroke-linecap:round}

/* ══ DASHBOARD (Marketing / Sales) ══ */
#bhRoot .dash{display:none;grid-template-columns:minmax(190px,220px) 1fr minmax(300px,340px);gap:1px;background:var(--ln);flex:1;min-height:0;overflow:hidden}
#bhRoot .dash.on{display:grid}
@media(max-width:1180px){#bhRoot .dash{grid-template-columns:190px 1fr}}
@media(max-width:820px){#bhRoot .dash{grid-template-columns:1fr;overflow-y:auto}}

#bhRoot .dash-left{background:#fff;display:flex;flex-direction:column;overflow-y:auto;min-height:0;padding-top:8px}
#bhRoot .flowst{display:flex;align-items:center;gap:13px;padding:12px 20px;cursor:pointer}
#bhRoot .flowst:hover{background:var(--p1)}
#bhRoot .flowst.cur{background:var(--ac-l);border-left:2px solid var(--ac);padding-left:18px}
#bhRoot .flowst-dot{width:27px;height:27px;border-radius:50%;border:1.5px solid var(--ln2);background:#fff;display:grid;place-items:center;font-family:var(--fm);font-size:11px;font-weight:700;color:var(--g3);flex-shrink:0}
#bhRoot .flowst.cur .flowst-dot{background:var(--ac);border-color:var(--ac);color:#fff}
#bhRoot .flowst.done .flowst-dot{background:var(--sig);border-color:var(--sig);color:#fff}
#bhRoot .flowst-t{font-size:13.5px;font-weight:600;color:var(--ink)}
#bhRoot .flowst-c{font-size:11.5px;color:var(--g3);margin-top:2px}
@media(max-width:820px){#bhRoot .dash-left{max-height:280px}}

#bhRoot .dash-main{background:#fff;display:flex;flex-direction:column;overflow:hidden;min-height:0}
#bhRoot .dash-vtabs{display:flex;gap:1px;background:var(--ln);border-bottom:1px solid var(--ln);flex-shrink:0;flex-wrap:wrap}
#bhRoot .dvt{flex:1;min-width:110px;font-family:var(--fm);font-size:10.5px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--g2);padding:13px 8px;background:#fff;border:none;cursor:pointer;text-align:center;transition:all .15s}
#bhRoot .dvt.on{background:var(--ac);color:#fff}
#bhRoot .dvt:hover:not(.on){background:var(--p1);color:var(--ink)}
#bhRoot .dash-view{flex:1;overflow-y:auto}

#bhRoot .stkrow{display:grid;grid-template-columns:30px 108px 1.2fr 1.2fr auto;gap:18px;padding:22px 20px;border-bottom:1px solid var(--p2);align-items:center}
#bhRoot .stkrow:last-child{border-bottom:none}
#bhRoot .stk-n{font-family:var(--fm);font-size:17px;font-weight:600;color:var(--g4)}
#bhRoot .stk-call{font-family:var(--fm);font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:10px 10px;border:1px solid var(--ac);background:var(--ac);color:#fff;cursor:pointer;border-radius:7px;white-space:nowrap;width:100%}
#bhRoot .stk-call:hover{background:var(--ac-d);border-color:var(--ac-d)}
#bhRoot .stk-call.done{background:#ecfdf5;border-color:#a7f3d0;color:var(--sig);cursor:default}
#bhRoot .stk-acct{font-size:14px;font-weight:600;color:var(--ink)}
#bhRoot .stk-play{font-size:9.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;padding:2px 8px;border-radius:99px;margin-left:7px;display:inline-block;vertical-align:2px}
#bhRoot .stk-play.call{background:var(--ac-l);color:var(--ac-d);border:1px solid var(--ac-m)}
#bhRoot .stk-play.upsell{background:#ecfdf5;color:#0e7a35;border:1px solid #a7f3d0}
#bhRoot .stk-play.winback{background:#fdf6e3;color:#9a6700;border:1px solid #f1dfae}
#bhRoot .stk-play.onboarding{background:#f3eefc;color:#6d28d9;border:1px solid #e2d5f7}
#bhRoot .stk-play.referral{background:#fdeef2;color:#c11d48;border:1px solid #f5cdd8}
#bhRoot .stk-mrr{font-family:var(--fm);font-size:10.5px;color:var(--g3);margin-top:4px;letter-spacing:.3px}
#bhRoot .stk-scores{display:flex;gap:18px;margin-top:12px}
#bhRoot .sc{text-align:center}
#bhRoot .sc-v{font-family:var(--fm);font-size:15px;font-weight:700}
#bhRoot .sc-l{font-family:var(--fm);font-size:8px;color:var(--g4);text-transform:uppercase;letter-spacing:.5px;margin-top:2px}
#bhRoot .stk-why{font-size:12px;color:var(--g2);line-height:1.65}
#bhRoot .stk-why b{color:var(--ink)}
#bhRoot .stkbtn{font-family:var(--fm);font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:9px 14px;border:1px solid var(--ink);background:var(--ink);color:#fff;cursor:pointer;border-radius:7px;white-space:nowrap}
#bhRoot .stkbtn:hover{background:#000;border-color:#000}
#bhRoot .stkbtn.ghost{background:#fff;color:var(--g2);border-color:var(--ln2)}
#bhRoot .stkbtn.ghost:hover{color:var(--ink);border-color:var(--ink)}
#bhRoot .stk-actions{display:flex;flex-direction:column;gap:8px}
@media(max-width:900px){#bhRoot .stkrow{grid-template-columns:1fr 1fr;grid-auto-flow:row}}

#bhRoot .stack-intro{padding:20px 20px 18px}
#bhRoot .si-h{font-family:var(--fm);font-size:11px;font-weight:700;letter-spacing:2px;color:var(--ink);margin-bottom:10px}
#bhRoot .si-p{font-size:12.5px;color:var(--g2);line-height:1.75;max-width:640px}
#bhRoot .si-p b{color:var(--ink);font-weight:600}
#bhRoot .sectionh{padding:11px 20px;background:var(--p1);border-top:1px solid var(--ln);border-bottom:1px solid var(--ln);font-family:var(--fm);font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--g2);display:flex;justify-content:space-between;align-items:center}
#bhRoot .sectionh span{font-weight:500;letter-spacing:1px;text-transform:none;color:var(--g3)}
#bhRoot .badge-new{font-family:var(--fm);font-size:8.5px;font-weight:700;letter-spacing:1px;color:var(--ac-d);background:var(--ac-l);border:1px solid var(--ac-m);border-radius:4px;padding:2px 7px;margin-left:8px;display:inline-block;vertical-align:2px}
#bhRoot .sectionh.warn{color:var(--warn)}
#bhRoot .sectionh.bad{color:var(--crit)}

#bhRoot .dtbl{width:100%;border-collapse:collapse;font-size:12px}
#bhRoot .dtbl th{font-size:10px;letter-spacing:.5px;text-transform:uppercase;color:var(--g3);text-align:left;padding:9px 14px;border-bottom:1px solid var(--ln);background:var(--p1);white-space:nowrap;font-weight:700}
#bhRoot .dtbl td{padding:9px 14px;border-bottom:1px solid var(--p2);vertical-align:middle}
#bhRoot .dtbl tr:hover td{background:var(--p1)}
#bhRoot .dtbl .acctn{font-weight:600;color:var(--ink);cursor:pointer}
#bhRoot .dtbl .acctn:hover{color:var(--ac)}
#bhRoot .segtag{font-size:9.5px;font-weight:700;text-transform:uppercase;padding:2px 8px;border-radius:99px}

#bhRoot .ss-grid{display:grid;grid-template-columns:230px 1fr;height:100%;min-height:0}
#bhRoot .ss-list{border-right:1px solid var(--ln);overflow-y:auto}
#bhRoot .ss-item{padding:11px 14px;border-bottom:1px solid var(--p2);cursor:pointer}
#bhRoot .ss-item:hover{background:var(--p1)}
#bhRoot .ss-item.on{background:var(--ac-l);border-left:3px solid var(--ac)}
#bhRoot .ss-item .n{font-size:12px;font-weight:600;color:var(--ink)}
#bhRoot .ss-item .m{font-size:10px;color:var(--g3);margin-top:2px}
#bhRoot .ss-out{padding:18px;overflow-y:auto}
#bhRoot .ss-chan{display:flex;gap:1px;background:var(--ln);border:1px solid var(--ln);width:max-content;margin-bottom:14px;border-radius:8px;overflow:hidden}
#bhRoot .ss-chan button{font-size:11px;font-weight:600;text-transform:uppercase;padding:7px 15px;border:none;background:#fff;color:var(--g2);cursor:pointer}
#bhRoot .ss-chan button.on{background:var(--ink);color:#fff}
#bhRoot .ss-script{border:1px solid var(--ln2);border-radius:10px;overflow:hidden}
#bhRoot .ss-shd{font-size:10.5px;font-weight:700;letter-spacing:1px;color:#fff;background:var(--ink);padding:9px 14px}
#bhRoot .ss-beat{padding:13px 16px;border-bottom:1px solid var(--ln);font-size:13px;line-height:1.65}
#bhRoot .ss-beat:last-child{border-bottom:none}
#bhRoot .ss-beat-l{font-size:9.5px;font-weight:700;letter-spacing:1px;color:var(--ac-d);text-transform:uppercase;margin-bottom:5px}

#bhRoot .fc-grid{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--ln)}
#bhRoot .fc-cell{background:#fff;padding:18px 20px}
#bhRoot .fc-h{font-size:10px;font-weight:700;letter-spacing:1px;color:var(--g3);text-transform:uppercase;margin-bottom:12px}
#bhRoot .fc-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--p2);font-size:13px}
#bhRoot .fc-row:last-child{border-bottom:none}
#bhRoot .fc-row b{color:var(--ink)}
#bhRoot .fc-tot{border-top:2px solid var(--ink);margin-top:6px;padding-top:10px;font-weight:700}
@media(max-width:900px){#bhRoot .fc-grid{grid-template-columns:1fr}}

#bhRoot .mg-grid{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--ln)}
#bhRoot .mg-cell{background:#fff;padding:16px 20px}
#bhRoot .mg-h{font-size:10px;font-weight:700;letter-spacing:1px;color:var(--g3);text-transform:uppercase;margin-bottom:10px}
#bhRoot .mg-kpi{font-size:20px;font-weight:700;color:var(--ink)}
#bhRoot .mg-kpi small{font-size:10px;color:var(--g3);font-weight:500;margin-left:4px}
@media(max-width:900px){#bhRoot .mg-grid{grid-template-columns:1fr}}

#bhRoot .dash-mira{background:#fff;display:flex;flex-direction:column;overflow:hidden;min-height:0}
#bhRoot .dm-hd{display:flex;align-items:center;gap:11px;padding:16px 18px;border-bottom:1px solid var(--ln);background:var(--p1);flex-shrink:0}
#bhRoot .dm-dot{width:7px;height:7px;border-radius:50%;background:var(--ac);flex-shrink:0;animation:bhblink 1.8s infinite}
#bhRoot .dm-t{font-size:13px;font-weight:700;letter-spacing:.2px;color:var(--ink)}
#bhRoot .dm-s{font-family:var(--fm);font-size:8.5px;letter-spacing:.5px;color:var(--g3);margin-top:3px}
#bhRoot .dm-ready{margin-left:auto;font-family:var(--fm);font-size:9.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--sig);background:#ecfdf5;border:1px solid #a7f3d0;border-radius:99px;padding:3px 10px;flex-shrink:0}
#bhRoot .dm-chat{flex:1;overflow-y:auto;padding:18px;display:flex;flex-direction:column;gap:13px;min-height:120px}
#bhRoot .dm-quick-hd{padding:10px 16px 4px;border-top:1px solid var(--ln);font-family:var(--fm);font-size:9.5px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--g3);flex-shrink:0}
#bhRoot .dm-quick{padding:6px 16px 14px;display:flex;flex-direction:column;gap:7px;flex-shrink:0;max-height:220px;overflow-y:auto}
#bhRoot .dm-quick .qk{width:100%;text-align:left;padding:10px 12px;font-size:12px;white-space:normal;line-height:1.35;border-radius:8px;background:#fff;border:1px solid var(--ln)}
#bhRoot .dm-quick .qk:hover{border-color:var(--ac-m);background:var(--ac-l);color:var(--ac-d)}
#bhRoot .dm-inbar{display:flex;gap:1px;border-top:1px solid var(--ln);background:var(--ln);flex-shrink:0}
</style>

<script>
(function () {

/* ═══ TASKS — plain English, per helper ═══ */
var TASKS = {
mk: [
 {n:"Win back customers who've gone quiet", w:"For customers who stopped showing up",
  steps:[
   {t:"See who's gone quiet", d:"Open your customer list and look at the “gone quiet” group — these are people who used to buy from you but stopped.", w:"Customers page → “Gone quiet” group"},
   {t:"Pick the winnable ones", d:"Focus on the ones marked “good chance of coming back” — the platform works this out for you from their behaviour.", w:"Same page — sort by “win-back chance”"},
   {t:"Start a win-back campaign", d:"Give it a name, choose these customers, and pick email as the channel.", w:"Campaigns → New campaign"},
   {t:"Write the messages", d:"Three friendly emails: what's new since they left, something useful for them, and a personal note. The agent can write these for you — press the button below.", w:"Inside your new campaign"},
   {t:"Set the timing", d:"Space the emails a few days apart, and stop automatically if they come back.", w:"Campaign settings → Schedule"},
   {t:"Switch it on", d:"Press launch, then check it shows up in your campaign list as running.", w:"Campaigns → your campaign → Launch"}],
  watch:"More of these customers logging in or buying again over the next few weeks"},
 {n:"Get warned when a customer loses interest", w:"So you find out before it's too late",
  steps:[
   {t:"Open your alerts", d:"Alerts are automatic warnings — the platform watches your customers so you don't have to.", w:"Alerts page"},
   {t:"Create the warning rule", d:"Ask to be told whenever a regular customer's activity drops sharply. That drop usually happens weeks before they actually leave.", w:"Alerts → New rule"},
   {t:"Choose how you're notified", d:"Email or your team chat — wherever someone will actually see it the same day.", w:"Inside the rule settings"},
   {t:"Act on each warning", d:"When a warning comes in, reach out to that customer personally, then mark the warning as handled.", w:"Alerts list → tick when done"}],
  watch:"You hearing about unhappy customers weeks earlier than before"},
 {n:"Check if your campaign made money", w:"After it's been running a week or more",
  steps:[
   {t:"Open the campaign results", d:"See how many people received, opened, and clicked each message.", w:"Campaigns → your campaign → Results"},
   {t:"See what actually worked", d:"The platform shows which message got customers to come back — not just who opened it.", w:"Results → “What changed behaviour”"},
   {t:"Read the money summary", d:"A plain summary: what the campaign cost, what it brought back, whether it was worth it.", w:"Reports → Summary"},
   {t:"Let it learn", d:"The platform remembers what worked and automatically does more of it next time. Nothing for you to do here.", w:"Automatic"}],
  watch:"Money brought back vs what the campaign cost you"}
],
sl: [
 {n:"Get today's call list", w:"Start every day knowing who to contact",
  steps:[
   {t:"Open your customer list", d:"Sort by “ready to buy” — the platform scores every customer on how close they are to saying yes.", w:"Customers page → sort by “Ready to buy”"},
   {t:"Skip the window-shoppers", d:"Only call people who are both ready AND genuinely interested — the list shows you both.", w:"Same page — “Interest” column"},
   {t:"Check how much they trust you", d:"If trust is low, don't push — show proof first (a success story) and offer a low-risk way to start. If trust is high, just ask directly.", w:"Customer profile → Trust"},
   {t:"Get a script for each call", d:"The agent writes you a script matched to each customer's situation — press the button below.", w:"Agent panel on the right"},
   {t:"Log every call", d:"Record each contact so the platform can learn which calls actually lead to sales.", w:"Customer profile → Log contact"}],
  watch:"How many calls turn into meetings, and meetings into sales"},
 {n:"Find customers ready to spend more", w:"Your happiest customers often want more — if you ask",
  steps:[
   {t:"Look at your happy customers", d:"Only your regulars and fans — never try to upsell someone who's having problems.", w:"Customers page → “Happy” groups"},
   {t:"Apply the three checks", d:"Ready to buy? Trusts you? Not frustrated? All three must be yes.", w:"Customer profiles"},
   {t:"See what to offer", d:"The platform suggests what each customer is most likely to want next.", w:"Customer profile → Suggestions"},
   {t:"Make the offer", d:"Reach out — the agent can write the message. Log it so results are tracked.", w:"Log contact after sending"},
   {t:"Check it worked", d:"A month later: did they buy? And importantly — are they still happy?", w:"Reports"}],
  watch:"Extra sales from existing customers — without any of them getting annoyed"},
 {n:"Never miss a hot lead again", w:"Get told the moment someone's ready",
  steps:[
   {t:"Open your alerts", d:"You can't watch every customer every day. This does it for you.", w:"Alerts page"},
   {t:"Create the “hot lead” warning", d:"Get notified instantly when any customer becomes very ready to buy.", w:"Alerts → New rule"},
   {t:"Add a daily digest", d:"A morning summary of everyone whose interest is climbing — your warm list.", w:"Alerts → New rule"},
   {t:"Call fast, then tick it off", d:"Hot leads cool quickly — aim to call within a few hours. Mark each alert handled.", w:"Alerts list"}],
  watch:"Time between the warning and your call — keep it under 4 hours"}
],
ch: [
 {n:"Save a customer who's about to leave", w:"When the platform flags someone as high risk",
  steps:[
   {t:"Find out WHY they're unhappy", d:"Open their profile. If frustration is high, something's broken for them — fix that first, don't offer discounts. If frustration is low but they're still leaving, they don't see the value — show them what they're getting.", w:"Customer profile → scores"},
   {t:"See what the platform suggests", d:"Based on similar customers, it recommends the approach most likely to work for this one.", w:"Customer profile → Suggested action"},
   {t:"Set up the save plan", d:"A simple sequence: fix their problem → personal call → check in after 30 days.", w:"Campaigns → New campaign"},
   {t:"Start it", d:"Nothing happens until you press go.", w:"Your campaign → Launch"},
   {t:"Watch them daily", d:"Set a warning so you're told immediately if they get worse mid-rescue.", w:"Alerts → New rule"},
   {t:"See if it worked", d:"After 30 days check: did they stay? The platform learns from every save — win or lose.", w:"Reports"}],
  watch:"Their risk score falling week by week — and the renewal going through"},
 {n:"Bring back customers who stopped coming", w:"Old customers are easier to win than new ones",
  steps:[
   {t:"Find the winnable ones", d:"Your “gone quiet” list, sorted by win-back chance. Above 70 means roughly 3× the normal odds.", w:"Customers page → “Gone quiet”"},
   {t:"Build the welcome-back series", d:"Message 1: what's new. Message 2: something their account found while they were away. Message 3: a short personal note from you.", w:"Campaigns → New campaign"},
   {t:"Add the smart branch", d:"If they open but don't click — send a small offer. If they click — invite them to a quick restart call.", w:"Campaign settings → Rules"},
   {t:"Switch it on", d:"Messages go out a few days apart automatically.", w:"Your campaign → Launch"},
   {t:"Catch them coming back", d:"Get told the moment any of them logs in, so you can say hello personally that same day.", w:"Alerts → New rule"},
   {t:"Count the wins", d:"After a month: how many came back, and what they're now worth.", w:"Reports"}],
  watch:"How many quiet customers became active again — aim for 3× your usual rate"},
 {n:"Understand why a customer is unhappy", w:"Do this BEFORE trying to fix anything",
  steps:[
   {t:"Open their full picture", d:"All their scores in one place — the pattern tells the story.", w:"Customer profile"},
   {t:"Read the pattern", d:"High frustration = something's broken for them. Low frustration but leaving anyway = they've stopped seeing the value. Both, plus low trust = serious, act today.", w:"Their score pattern"},
   {t:"Find what's actually breaking", d:"See exactly where on your website or product they keep getting stuck.", w:"Customer profile → Journey view"},
   {t:"Check the trust level", d:"Low trust: a goodwill gesture or discount genuinely helps. High trust: they don't want money off — they want it fixed.", w:"Customer profile → Trust"},
   {t:"Get the full rescue plan", d:"The agent writes the whole plan for this customer — press the button below.", w:"Agent panel on the right"}],
  watch:"Whether your fix matches their real problem — the scores will tell you within days"}
]
};

/* ═══ AGENTS ═══ */
var AGENTS = {
mk: {name:"Marketing", sub:"Bring customers back · Promote smarter",
  intro:"<p>I help you run campaigns that actually work — using what the platform knows about your customers.</p><p>Right now I can see <strong>4 customers who went quiet but still have decent trust scores</strong>, and <strong>2 new customers</strong> who are keen but stalling — right when new customers quietly give up.</p><p>Pick a job below, or just ask me anything.</p>",
  quicks:[
    {label:"Win back quiet customers", q:"Design a win-back campaign for my dormant segment"},
    {label:"Welcome new customers", q:"Create an onboarding email sequence for new accounts"},
    {label:"Get referrals", q:"Design a referral campaign using my champion accounts"},
    {label:"Top plays this hour", q:"Design a win-back campaign for my dormant segment"}]},
sl: {name:"Sales", sub:"Know who to call · What to say",
  intro:"<p>I tell you who's ready to buy and what to say to them.</p><p>Right now: <strong>Jordan Lee and Alex Johnson are in the buying window</strong> — but trust hasn't fully caught up with their interest yet, so the approach matters. I'll give you the exact words.</p><p>Pick a job below, or ask me about any customer.</p>",
  quicks:[
    {label:"Who do I call today?", q:"Prioritise my pipeline — who should I contact today and why?"},
    {label:"Who'll buy more?", q:"Which existing accounts are ready for an upsell?"},
    {label:"Words for my best lead", q:"Write me an outreach script for my highest-readiness account"},
    {label:"Top 5 for this hour", q:"Prioritise my pipeline — who should I contact today and why?"}]},
ch: {name:"Customer Retention", sub:"Spot who's leaving · Save them",
  intro:"<p>I spot which customers are about to leave and tell you exactly how to keep them.</p><p><strong>4 customers are flagged at-risk right now.</strong> The most urgent — Sofia Hernandez — isn't leaving over price: trust is down at 32 and frustration is up at 75. A discount wouldn't fix either one. A real fix and a personal call will.</p><p>Pick a job below, or ask about any customer.</p>",
  quicks:[
    {label:"Who's about to leave?", q:"Triage my at-risk accounts — what do I do about each one this week?"},
    {label:"Win back quiet ones", q:"Build a win-back plan for my dormant accounts"},
    {label:"Why is Sofia at risk?", q:"Why is Sofia Hernandez churning and what exactly should I do?"}]}
};

/* ═══ PLAYBOOKS — canned rich answers behind the quick-action buttons ═══ */
var PLAYBOOKS = {
"Design a win-back campaign for my dormant segment": {agent:"mk", tag:"Marketing · Win-back plan",
 html:"<p><strong>What I noticed —</strong> Tom Erikson and Ben Okafor have both gone quiet, but neither has walked away — trust is still 58 and 60, well above where someone who's truly checked out would sit. The door is open.</p><p><strong>The plan —</strong> three friendly emails, a few days apart: <strong>1)</strong> “Here's what's new since you've been away” — momentum, not apology. <strong>2)</strong> Something useful their own account data found. <strong>3)</strong> A short personal note from you offering a 15-minute catch-up. <strong>No discount in the first email</strong> — people come back for value; discounts come out only if they open twice without clicking.</p>",
 how:{title:"How to set it up", watch:"They start logging in again — watch trust and engagement climb over the following weeks", steps:[
  ["Find them: your customer list → the “gone quiet” group, sorted by win-back chance.","Customers page"],
  ["Create the campaign, choose these 2 customers, channel: email.","Campaigns → New"],
  ["Paste in the three messages — press the button below and I'll write them.","Campaign editor"],
  ["Space the emails 3–5 days apart; stop automatically if they come back.","Campaign settings"],
  ["Launch it.","Campaigns → Launch"],
  ["Add a warning so you're told the moment either of them logs back in.","Alerts → New rule"]]},
 acts:[{title:"Set up the win-back emails",desc:"2 customers · trust still 58+ on both · 3 friendly messages",route:"Campaigns → New",btn:"Set up"},
       {title:"Warn me when they return",desc:"So you can welcome them back personally, same day",route:"Alerts → New rule",btn:"Set up"}]},

"Create an onboarding email sequence for new accounts": {agent:"mk", tag:"Marketing · New-customer welcome",
 html:"<p><strong>What I noticed —</strong> Alex Johnson and Maya Thompson both signed up recently and are keen (readiness 52 and 48) — but engagement is only 58 and 55. Keen-but-stalled is exactly when new customers quietly give up.</p><p><strong>The plan —</strong> four short emails over ten days: <strong>Day 0:</strong> one single ask — finish whichever setup step they're stuck on. <strong>Day 2:</strong> show them their own first result (their data beats any brochure). <strong>Day 5:</strong> invite them to the weekly summary — that habit is what keeps customers for years. <strong>Day 10:</strong> a gentle look at what the next tier unlocks.</p>",
 how:{title:"How to set it up", watch:"New customers finishing setup within 2 days and using you weekly by week 2", steps:[
  ["Check your “new customers” group — you'll see where each one stalled.","Customers page"],
  ["Create the campaign for the new-customer group, 4 emails, days 0/2/5/10.","Campaigns → New"],
  ["Set it to stop nagging anyone who completes setup.","Campaign settings"],
  ["Launch, then watch opens and completions in the results page.","Campaigns → Results"]]},
 acts:[{title:"Set up the welcome series",desc:"2 new customers · 4 emails · stops when they're settled",route:"Campaigns → New",btn:"Set up"}]},

"Design a referral campaign using my champion accounts": {agent:"mk", tag:"Marketing · Referrals",
 html:"<p><strong>What I noticed —</strong> Priya Sharma and Emma Rodriguez love you — loyalty 94 and 88, frustration only 10 and 15. Happy customers like these will recommend you — <em>but only if you ask properly</em>.</p><p><strong>The ask that works —</strong> don't send a “share this link” email. Ask each one personally: <em>“Who's one person you know who struggles with this?”</em> Asking for ONE name gets far more introductions than asking them to share broadly. Then: personal intro → you give the new customer VIP treatment → report the good result back to whoever introduced them.</p>",
 how:{title:"How to set it up", watch:"Replies with names in them — and new signups tagged as referrals", steps:[
  ["Confirm your happiest customers in the “champions” group.","Customers page"],
  ["Create a small campaign: just these 2, personal email from you.","Campaigns → New"],
  ["One follow-up after 3 days if no reply — then stop. Never chase champions twice.","Campaign settings"],
  ["Tag anyone they introduce, so you can see the referrals working.","New customer → tag"],
  ["Check the results in a month or two.","Reports"]]},
 acts:[{title:"Send the referral asks",desc:"2 happy customers · personal one-name ask",route:"Campaigns → New",btn:"Set up"}]},

"Prioritise my pipeline — who should I contact today and why?": {agent:"sl", tag:"Sales · Today's call list",
 html:"<p><strong>Your call list, in order:</strong></p><table><tr><th>#</th><th>Who</th><th>Why now</th><th>Approach</th></tr><tr><td>1</td><td><strong>Jordan Lee</strong></td><td>Readiness 55, intent 60 — trust (55) is keeping pace with readiness, not lagging behind it</td><td>No trust gap to close — a direct ask works better than another proof point</td></tr><tr><td>2</td><td><strong>Alex Johnson</strong></td><td>Readiness 52, intent 55 — trust (50) sits just behind readiness</td><td>Lead with a short proof point, then a simple, low-risk ask</td></tr><tr><td>3</td><td><strong>Priya Sharma</strong></td><td>Upsell-qualified: readiness 85, trust 92, frustration only 10</td><td>Existing champion — just ask directly about the bigger plan</td></tr></table><p><strong>Skip today:</strong> anyone marked at-risk — selling to an unhappy customer makes it worse.</p>",
 how:{title:"How to do this yourself any morning", watch:"Calls turning into meetings — and tomorrow's list building itself", steps:[
  ["Open your customer list, sort by “ready to buy”.","Customers page"],
  ["Keep only the genuinely interested ones — the interest column.","Same page"],
  ["Check trust before each call: low = lead with proof; high = just ask.","Customer profile"],
  ["Ask me for the words for any call — button below.","Agent panel"],
  ["Log each call so the platform learns what works for you.","Customer → Log contact"]]},
 acts:[{title:"Get the words for Jordan Lee",desc:"Direct-ask opener — no trust gap to close",route:"Agent panel",btn:"Write it"},
       {title:"Warn me about hot leads",desc:"Instant alert whenever anyone becomes very ready to buy",route:"Alerts → New rule",btn:"Set up"}]},

"Which existing accounts are ready for an upsell?": {agent:"sl", tag:"Sales · Who'll buy more",
 html:"<p><strong>Three checks before offering anyone more:</strong> ready to buy? trusts you? not frustrated? All three must be yes.</p><ul style='margin:4px 0 8px 15px'><li style='margin-bottom:3px'><strong>Priya Sharma — yes, yes, yes.</strong> Readiness 85, trust 92, frustration only 10. Just ask directly.</li><li style='margin-bottom:3px'><strong>Emma Rodriguez — yes.</strong> Readiness 78, trust 91, frustration 15. Frame it around what they use most.</li><li><strong>Aisha Patel — also qualified.</strong> Readiness 68, trust 80 — a good candidate to invite into the next tier rather than talking price outright.</li></ul><p>Never offer more to anyone who's currently frustrated — nobody on this list is.</p>",
 how:{title:"How to do this", watch:"Extra revenue from existing customers — with satisfaction staying high", steps:[
  ["Look only at your happy groups — champions and loyal.","Customers page"],
  ["Run the three checks on each: ready / trusts you / not frustrated.","Customer profiles"],
  ["See what the platform suggests offering each one.","Profile → Suggestions"],
  ["Make the offers — I can write each message.","Agent panel"],
  ["A month later: did they buy, and are they still happy? Both matter.","Reports"]]},
 acts:[{title:"Offer Priya the bigger plan",desc:"Readiness 85, trust 92 — a direct ask",route:"Agent panel",btn:"Write it"},
       {title:"Invite Aisha to try the next tier",desc:"Soft invitation, no price talk",route:"Agent panel",btn:"Write it"}]},

"Write me an outreach script for my highest-readiness account": {agent:"sl", tag:"Sales · Your words for Jordan Lee",
 html:"<p><strong>The situation —</strong> Jordan Lee has the highest readiness (55) and intent (60) of anyone currently in the buying window. Trust (55) is keeping pace with that readiness rather than trailing behind it — so this isn't a trust problem. Don't lead with proof; ask directly.</p><p><strong>Opening:</strong> <em>“You've clearly been looking into this seriously — I'd like to talk about what's next.”</em></p><p><strong>The direct ask:</strong> <em>“Given where you're at, starting now makes sense. Want me to set it up?”</em></p><p><strong>If they hesitate:</strong> <em>“Start on the smaller plan — if it doesn't earn its place in 30 days, walk away and keep everything we found.”</em></p>",
 how:{title:"Before you call", watch:"Trust and readiness both holding steady or climbing after the call", steps:[
  ["Glance at their profile — scores change daily.","Customer profile"],
  ["Have your one success story ready, just in case they want it.","Your notes"],
  ["Make the call, ask directly — don't over-prove a trust gap that isn't there.","Phone"],
  ["Log the call afterwards so results are tracked.","Customer → Log contact"],
  ["Set a warning for when their readiness climbs further — that's when to close.","Alerts → New rule"]]},
 acts:[{title:"Log this as today's priority call",desc:"Script attached · direct-ask close",route:"Customer → Log contact",btn:"Queue"}]},

"Triage my at-risk accounts — what do I do about each one this week?": {agent:"ch", tag:"Customer Retention · Who's about to leave",
 html:"<p><strong>4 customers are flagged at-risk.</strong> Each one is leaving for a different reason — so each needs a different rescue:</p><table><tr><th>Customer</th><th>Churn</th><th>What's really going on</th><th>This week</th></tr><tr><td><strong>Sofia Hernandez</strong></td><td>80</td><td>Trust is down at 32 and frustration is up at 75 — both bad at once</td><td>Find and fix what's broken, then YOU call. <strong>No discount</strong>.</td></tr><tr><td><strong>Ryan Mitchell</strong></td><td>72</td><td>Trust 40, frustration 65 — same pattern, slightly less severe</td><td>Same approach: fix first, then a personal call</td></tr><tr><td><strong>Natalie Brooks</strong></td><td>70</td><td>Trust 38, frustration 68 — close behind Ryan</td><td>Fix first, then a goodwill call</td></tr></table><p><strong>Order matters:</strong> fix Sofia's problem BEFORE calling, so the call opens with “it's done” — not “we're working on it”.</p>",
 how:{title:"How to run the rescues", watch:"Churn scores falling week by week · all three renewing", steps:[
  ["Open each customer's profile and confirm what's frustrating them.","Customer profiles"],
  ["For Sofia: find exactly where it breaks for her.","Profile → Journey view"],
  ["Set up a rescue plan per customer: fix → personal call → 30-day check-in.","Campaigns → New"],
  ["Start each one.","Campaign → Launch"],
  ["Add daily warnings on all three so you know immediately if anyone gets worse.","Alerts → New rule"],
  ["In 30 days, see who stayed. The platform learns from every rescue.","Reports"]]},
 acts:[{title:"Rescue Sofia Hernandez",desc:"Fix what's broken first, then a personal call — no discount",route:"Campaigns → New",btn:"Start"},
       {title:"Rescue Ryan + Natalie",desc:"Same trust/frustration pattern · different fix for each",route:"Campaigns → New",btn:"Start"},
       {title:"Daily warnings on all three",desc:"Know same-day if anyone slips further",route:"Alerts → New rule",btn:"Set up"}]},

"Build a win-back plan for my dormant accounts": {agent:"ch", tag:"Customer Retention · Bring them back",
 html:"<p><strong>Good news hiding in your quiet list —</strong> Tom Erikson and Ben Okafor have both gone quiet, but the signs say they're winnable: trust is still 58 and 60 — well above where someone who's truly checked out would sit. They haven't left — they've just drifted.</p><p><strong>The 3-week plan —</strong> Week 1: “here's what's new” email + one useful thing their own account found. Week 2: if opened but not clicked, send a small offer; if clicked, invite to a 15-minute restart chat. Week 3: one short personal note from you — then stop, and let the alert watch for their return.</p>",
 how:{title:"How to set it up", watch:"Quiet customers active again — trust and engagement climbing", steps:[
  ["Your quiet list, sorted by win-back chance — these two are on top.","Customers page"],
  ["Create the campaign with the 3 weekly messages.","Campaigns → New"],
  ["Add the smart rule: opened-not-clicked → offer · clicked → invite to chat.","Campaign settings"],
  ["Launch.","Campaign → Launch"],
  ["Warning on: tell me the moment either logs in.","Alerts → New rule"],
  ["Count the comebacks in a month.","Reports"]]},
 acts:[{title:"Start the welcome-back series",desc:"Tom + Ben · trust still 58+ on both · 3 gentle touches",route:"Campaigns → New",btn:"Start"},
       {title:"Tell me when they return",desc:"So you can say hello personally, same day",route:"Alerts → New rule",btn:"Set up"}]},

"Why is Sofia Hernandez churning and what exactly should I do?": {agent:"ch", tag:"Customer Retention · Why Sofia is leaving",
 html:"<p><strong>What's happening —</strong> Sofia Hernandez has the highest churn score of any customer you have, at 80.</p><p><strong>Why —</strong> not price. Trust is down at 32 and frustration is up at 75 — both are bad, and together they usually mean something is genuinely broken for her, and she's stopped believing it'll get fixed. Engagement has fallen to 22 and loyalty to 22 as well — she's pulling away on every measure at once, not just one.</p><p><strong>What to do —</strong> find and fix whatever's actually broken first, then call personally — in that order. A discount here would tell her the broken thing is fine, which makes it worse, not better.</p>",
 how:{title:"This week", watch:"Trust and frustration moving back toward normal — and the renewal going through", steps:[
  ["Open Sofia's profile and look for exactly where things are breaking for her.","Customer profile → Journey view"],
  ["Get that fixed — flag it as priority given how far the scores have slipped.","Support → Escalate"],
  ["Call her yourself once it's fixed, not before.","Phone"],
  ["Log the call, then set a 30-day check-in.","Customer → Log contact"],
  ["Add a daily warning so you hear immediately if she gets worse before then.","Alerts → New rule"]]},
 acts:[{title:"Escalate the underlying issue",desc:"Trust 32, frustration 75 — root cause of the churn risk",route:"Support → Escalate",btn:"Escalate"},
       {title:"Queue the personal call",desc:"After the fix ships — opens with “it's done”, not “we're working on it”",route:"Customer → Log contact",btn:"Queue"}]},

"Why is Jordan Lee ready to buy right now?": {agent:"sl", tag:"Sales · Understand — Jordan Lee",
 html:"<p><strong>The signal —</strong> readiness 55, intent 60 — the highest combination of anyone currently in the buying window. That's someone actively weighing up whether to commit.</p><p><strong>No gap to close —</strong> trust sits at 55, keeping pace with readiness rather than trailing behind it. That's different from a typical hesitant lead: they already believe the problem is real AND that you're the one to solve it. A hard close here doesn't backfire the way it would on a low-trust account.</p><p><strong>What it means for the call —</strong> skip the extended proof section and ask directly. Keep a success story in your back pocket only in case they ask.</p>",
 how:{title:"Read the gap before you dial", watch:"Trust and readiness both holding steady or climbing after the call", steps:[
  ["Open the account and check trust vs. readiness — a wide gap means proof-first, a narrow one means ask directly.","Customer profile → scores"],
  ["Have one success story ready just in case, but don't lead with it.","Your notes"],
  ["Make the direct ask — a start date, not just a pilot.","Call"]]},
 acts:[{title:"Get the script for Jordan Lee",desc:"Direct-ask opener, no trust gap to close",route:"Agent panel",btn:"Write it"}]},

"They said the price is too high — what do I say?": {agent:"sl", tag:"Sales · Overcome — price objection",
 html:"<p><strong>What's really being said —</strong> \"too high\" almost always means \"not yet convinced it's worth it\" rather than a hard budget ceiling. Arguing the number rarely works; showing the return does.</p><p><strong>The reframe —</strong> <em>\"Compared to what you're losing by not fixing this, what would make the number feel fair?\"</em> — this moves the conversation from price to value without sounding defensive.</p><p><strong>The de-risked close —</strong> <em>\"Start on the smaller plan. If it doesn't pay for itself in 30 days, walk away — you keep everything we find.\"</em> Removes the risk instead of cutting the price.</p><p>Only discount as a last resort, and never before the value case has been made.</p>",
 how:{title:"Before you respond", watch:"Whether the objection was really about price, or about trust", steps:[
  ["Ask what number they had in mind, and why — don't guess.","Call"],
  ["Reframe to value: what does the problem cost them today?","Call"],
  ["Offer the low-risk start before offering a discount.","Call"],
  ["Log the objection so the next rep sees it too.","Customer → Log contact"]]},
 acts:[{title:"Log this objection",desc:"So the pattern is visible across the account",route:"Customer → Log contact",btn:"Log"}]},

"Why did Tom Erikson and Ben Okafor go quiet?": {agent:"mk", tag:"Marketing · Understand — why they went quiet",
 html:"<p><strong>The pattern —</strong> both stopped logging in as often, but neither looks actually gone — trust is still 58 and 60 for Tom and Ben. Quiet isn't the same as gone.</p><p><strong>Why it happens —</strong> usage drops off when a customer solves the immediate problem they signed up for and nothing pulls them back in. It's rarely dissatisfaction — trust for both is still comfortably above where a truly unhappy customer would sit.</p><p><strong>What it means —</strong> the win-back message shouldn't apologise or discount first; it should remind them what's changed and give them one easy reason to log back in.</p>",
 how:{title:"Before you send anything", watch:"Whether they open the first email — that tells you if the door is really open", steps:[
  ["Check engagement AND trust — low engagement with high trust means dormant, not lost.","Customer profile → scores"],
  ["Look for any quiet-period activity (help pages, email opens) — a sign they're still nearby.","Customer profile → activity"],
  ["Lead the first message with what's new, not with why they left.","Campaign editor"]]},
 acts:[{title:"See the full win-back plan",desc:"Quarry + Lumen, 3-email sequence",route:"Campaigns → New",btn:"Open plan"}]},

"Who should I not call this week, and why?": {agent:"sl", tag:"Sales · Prioritise — who to skip",
 html:"<p><strong>Skip these, and why —</strong> anyone flagged at-risk (churn above 60) doesn't belong on a sales call this week — a sales touch on an unhappy customer reads tone-deaf and can push them toward leaving. In the current stack that's Sofia Hernandez, Ryan Mitchell, Natalie Brooks and Kevin O'Brien — all four are Retention's, not Sales', to work right now.</p><p><strong>Also hold off on —</strong> anyone you contacted inside the last 3 days (cool-off) and anyone outside your contact hours — calling either burns trust for a marginal chance of reaching them.</p>",
 how:{title:"How the skip list is built", watch:"Fewer wasted calls, and no complaints about being contacted too often", steps:[
  ["Check churn first — anything above 60 routes to Retention automatically.","Today's Stack → Hold section"],
  ["Check last-contact date — inside the cool-off window, it's demoted not removed.","Customer profile"],
  ["Check local time — outside contact hours, it's queued for later.","Customer profile → time zone"]]},
 acts:[{title:"Review the Hold section",desc:"Accounts currently routed away from Sales",route:"Today's Stack → Hold",btn:"Open"}]},

"What changed in my pipeline since yesterday?": {agent:"sl", tag:"Sales · Prioritise — what moved",
 html:"<p><strong>This view doesn't track day-over-day history yet</strong> — what you're seeing is always today's scores, re-ranked live. Right now Jordan Lee leads the buying-window list on readiness and intent, with Alex Johnson close behind.</p><p><strong>What actually moves someone up the list —</strong> their readiness or intent score rising, trust closing the gap with readiness, or a contact cool-off period ending. Log outcomes as you work the list, and the ranking will reflect it the next time you check.</p>",
 how:{title:"How to check this yourself each morning", watch:"Rank changes and any new Hold flags", steps:[
  ["Open Today's Stack — the order reflects the latest scores.","Today's Stack"],
  ["Click any score to see what moved it.","Stack row → score"],
  ["Check the Hold section for anything newly routed to Retention.","Today's Stack → Hold"]]},
 acts:[{title:"Open today's stack",desc:"See the current order and what's new",route:"Today's Stack",btn:"Open"}]},

"Who should I not target this week, and why?": {agent:"mk", tag:"Marketing · Prioritise — who to skip",
 html:"<p><strong>Skip these, and why —</strong> anyone flagged at-risk (churn above 60) shouldn't get a marketing push — a campaign lands badly on someone already unhappy, and it's Retention's account to work, not Marketing's. In the current stack that's Sofia Hernandez, Ryan Mitchell, Natalie Brooks and Kevin O'Brien.</p><p><strong>Go carefully with —</strong> anyone contacted in the last few days — repeat touches too close together read as spam rather than care.</p>",
 how:{title:"How the skip list is built", watch:"Fewer opt-outs, and no overlap with Retention's outreach", steps:[
  ["Check churn first — anything above 60 routes to Retention automatically.","Today's Campaign Stack → Hold"],
  ["Check recent contact history before adding anyone to a new send.","Customer profile"]]},
 acts:[{title:"Review the Hold section",desc:"Accounts currently routed away from Marketing",route:"Today's Campaign Stack → Hold",btn:"Open"}]},

"What changed in my campaigns since yesterday?": {agent:"mk", tag:"Marketing · Prioritise — what moved",
 html:"<p><strong>This view doesn't track day-over-day history yet</strong> — what you're seeing is always today's scores, re-ranked live. Right now Alex Johnson and Maya Thompson lead the onboarding list on readiness, and Priya Sharma, Emma Rodriguez and Liam Park are referral-qualified on loyalty above 80 with low frustration.</p><p><strong>What actually moves someone up the list —</strong> engagement dropping further (onboarding risk rising), or loyalty and frustration shifting enough to gain or lose referral qualification. Log sends as you work the list, and the ranking will reflect it next time you check.</p>",
 how:{title:"How to check this yourself each morning", watch:"Rank changes and any new Hold flags", steps:[
  ["Open Today's Campaign Stack — the order reflects the latest scores.","Today's Campaign Stack"],
  ["Click any score to see what moved it.","Stack row → score"],
  ["Check the Hold section for anything newly routed to Retention.","Today's Campaign Stack → Hold"]]},
 acts:[{title:"Open today's campaign stack",desc:"See the current order and what's new",route:"Today's Campaign Stack",btn:"Open"}]},

"They said they don't trust us yet — what do I send?": {agent:"mk", tag:"Marketing · Handle — trust objection",
 html:"<p><strong>What's behind it —</strong> a new or stalled customer questioning trust is usually reacting to a specific unanswered question (data ownership, proof it works, what happens if it doesn't) — not a general feeling.</p><p><strong>What to send —</strong> a short, specific answer to that exact question, plus one piece of independent proof (a case study or number from a similar customer) — not a generic reassurance email.</p><p><strong>What not to do —</strong> don't lead with a discount. A discount answers a price objection, not a trust one, and can make the hesitation look justified.</p>",
 how:{title:"Before you reply", watch:"Whether the specific question gets asked again — if not, trust moved", steps:[
  ["Find the exact concern raised (chat log, email reply, form note).","Customer profile → activity"],
  ["Answer that concern directly in the first line.","Message draft"],
  ["Attach one piece of proof relevant to their situation.","Message draft"]]},
 acts:[{title:"Draft the trust-repair message",desc:"Answers the specific concern, plus one proof point",route:"Campaigns → New",btn:"Draft"}]}
};

/* ═══ STATE + RENDER ═══ */
var root = document.getElementById('bhRoot');
var state = { agent: 'mk', taskIndex: 0 };
var doneSteps = {};

function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = String(s == null ? '' : s);
    return d.innerHTML;
}

function nameAttr(n) { return String(n).replace(/'/g, "\\'"); }

function stepKey() { return state.agent + ':' + state.taskIndex; }

function currentTask() { return TASKS[state.agent][state.taskIndex]; }

function renderAgentTabs() {
    ['mk', 'sl', 'ch'].forEach(function (k) {
        document.getElementById('bhAgentTab-' + k).classList.toggle('on', k === state.agent);
    });
}

function renderGuide() {
    var task = currentTask();
    var done = doneSteps[stepKey()] || {};
    var doneCount = Object.keys(done).filter(function (k) { return done[k]; }).length;
    var firstOpen = -1;
    for (var i = 0; i < task.steps.length; i++) {
        if (!done[i]) { firstOpen = i; break; }
    }
    var pct = Math.round((doneCount / task.steps.length) * 100);

    document.getElementById('bhGHd').innerHTML =
        '<div class="g-t">' + escapeHtml(task.n) + '<span class="g-w">' + escapeHtml(task.w) + '</span></div>' +
        '<div class="g-prog"><span class="g-count"><b>' + doneCount + '</b> / ' + task.steps.length + ' done</span>' +
        '<div class="g-bar"><div class="g-fill" style="width:' + pct + '%"></div></div></div>';

    document.getElementById('bhGBody').innerHTML = task.steps.map(function (step, i) {
        var isDone = !!done[i];
        var isCur = !isDone && i === firstOpen;
        var cls = 'st' + (isDone ? ' done' : '') + (isCur ? ' cur' : '');
        return '<div class="' + cls + '" onclick="toggleStep(' + i + ')">' +
               '<div class="st-rail"><div class="st-dot">' + (isDone ? '&check;' : (i + 1)) + '</div><div class="st-line"></div></div>' +
               '<div class="st-txt"><div class="st-title">' + escapeHtml(step.t) + (isCur ? '<span class="st-next">Do this next</span>' : '') + '</div>' +
               '<div class="st-desc">' + escapeHtml(step.d) + '</div>' +
               '<span class="st-where">' + escapeHtml(step.w) + '</span></div></div>';
    }).join('');
}

function toggleStep(i) {
    var key = stepKey();
    if (!doneSteps[key]) doneSteps[key] = {};
    doneSteps[key][i] = !doneSteps[key][i];
    renderGuide();
}

var DASH_FLOW = {
  sl: {label:"Today's call list", steps:[
    {t:'Prioritise', c:'who, in order'},
    {t:'Understand', c:'why this one'},
    {t:'Pitch', c:'what to say'},
    {t:'Overcome', c:'they said "X"'},
    {t:'Close & grow', c:'the yes, or upgrade'}]},
  mk: {label:"Today's campaign list", steps:[
    {t:'Prioritise', c:'which play, first'},
    {t:'Understand', c:'why they went quiet'},
    {t:'Craft', c:'the message'},
    {t:'Handle', c:'objections'},
    {t:'Launch & grow', c:'send it, then expand'}]}
};
var FLOW_TABS = ['today', 'accounts', 'scripts', 'forecast', 'manager'];
var PROMPT_STEP_ORDER = ['prioritise', 'understand', 'craft', 'handle', 'launch'];
var PROMPT_SETS = {
  prioritise: [
    {key:'contact_today', label:'Who should I contact today?'},
    {key:'top5_hour', label:'Give me my top 5 for this hour'},
    {key:'buying_window', label:"Who's in the buying window right now?"},
    {key:'not_call', label:'Who should I NOT call this week, and why?'},
    {key:'changed_yesterday', label:'What changed since yesterday?'}
  ],
  understand: [
    {key:'why_ranked', label:'Why is {name} ranked here?'},
    {key:'been_doing', label:'What has {name} been doing?'},
    {key:'holding_back', label:"What's holding {name} back?"},
    {key:'ready_or_researching', label:'Is {name} ready or just researching?'},
    {key:'cares_about', label:'What does {name} care about most?'}
  ],
  craft: [
    {key:'script_for', label:'Script for {name}'},
    {key:'opener_30s', label:'30-second opener for {name}'},
    {key:'whatsapp_version', label:'WhatsApp / DM version'},
    {key:'email_version', label:'Email version'},
    {key:'proof_to_show', label:'What proof should I show {name}?'},
    {key:'shorter_less_salesy', label:'Make it shorter / less salesy'}
  ],
  handle: [
    {key:'too_expensive', label:'Too expensive'},
    {key:'not_right_now', label:'Not right now'},
    {key:'use_competitor', label:'We use a competitor'},
    {key:'send_info', label:'Send me some info'},
    {key:'no_budget', label:'No budget'},
    {key:'need_boss', label:'Need my boss'},
    {key:'something_else', label:'They said something else…'}
  ],
  launch: [
    {key:'how_close', label:'How do I close {name}?'},
    {key:'smallest_ask', label:'Smallest ask I can make to {name}?'},
    {key:'ready_upgrade', label:"Who's ready for an upgrade?"},
    {key:'offer_discount', label:'Should I offer a discount?'},
    {key:'weighted_pipeline', label:"What's my weighted pipeline?"},
    {key:'at_risk_no_touch', label:"What's at risk that I shouldn't touch?"}
  ]
};
function topPrimaryName(agent){
  var r = rankedFor(agent);
  var primaryPlays = agent==='sl' ? ['call'] : ['winback', 'onboarding'];
  var primary = r.filter(function(x){ return primaryPlays.indexOf(x.c.play) !== -1; });
  return dashState.lead || (primary[0] && primary[0].a.name) || (r[0] && r[0].a.name) || 'this account';
}
function renderDashQuicks(){
  var idx = FLOW_TABS.indexOf(dashState.view);
  if (idx === -1) idx = 0;
  var stepKey = PROMPT_STEP_ORDER[idx];
  var stepTitle = DASH_FLOW[dashState.agent].steps[idx].t;
  var name = topPrimaryName(dashState.agent);
  var hd = document.getElementById('dashQuickHd');
  var q = document.getElementById('dashQuick');
  if (!q) return;
  if (hd) hd.textContent = 'Ask Mira · ' + stepTitle;
  q.innerHTML = PROMPT_SETS[stepKey].map(function(p){
    var label = p.label.replace('{name}', name);
    return '<button type="button" class="qk" onclick="dashPromptClick(\''+stepKey+'\',\''+p.key+'\',\''+nameAttr(label)+'\')">'+escapeHtml(label)+'</button>';
  }).join('');
}
function dashPromptClick(stepKey, promptKey, label){
  dashPushMsg('user', escapeHtml(label));
  var agent = dashState.agent;
  var name = topPrimaryName(agent);
  var account = ACCOUNTS.filter(function(x){ return x.name === name; })[0];
  var c = account ? classifyFor(agent, account) : null;
  dashPushMsg('bot', dashPromptAnswer(agent, stepKey, promptKey, name, account, c));
}
function dashPromptAnswer(agent, stepKey, promptKey, name, account, c){
  var r = rankedFor(agent);
  var isSl = agent === 'sl';
  var tag = (isSl ? 'Sales' : 'Marketing') + ' · ' + DASH_FLOW[agent].steps[PROMPT_STEP_ORDER.indexOf(stepKey)].t;
  var head = '<div class="tag">' + escapeHtml(tag) + '</div>';
  var primaryPlays = isSl ? ['call'] : ['winback', 'onboarding'];
  var primary = r.filter(function(x){ return primaryPlays.indexOf(x.c.play) !== -1; });
  var secondaryPlays = isSl ? ['upsell'] : ['referral'];
  var secondary = r.filter(function(x){ return secondaryPlays.indexOf(x.c.play) !== -1; });
  var hold = r.filter(function(x){ return x.c.play === 'hold'; });
  var s = account ? account.scores : null;

  switch (stepKey + ':' + promptKey) {
    case 'prioritise:contact_today':
      return head + '<p>' + (primary.length ? primary.map(function(x,i){ return '<b>'+(i+1)+'. '+escapeHtml(x.a.name)+'</b> — '+x.c.why[0]; }).join('</p><p>') : 'Nothing urgent right now — check Accounts for the full list.') + '</p>';
    case 'prioritise:top5_hour':
      var top5 = r.slice(0, 5);
      return head + '<p><strong>Top 5 by priority right now:</strong></p><ul style="margin:4px 0 0 16px">' + top5.map(function(x){ return '<li>'+escapeHtml(x.a.name)+' — priority '+x.c.priority+' ('+PLAY_LABEL[x.c.play]+')</li>'; }).join('') + '</ul>';
    case 'prioritise:buying_window':
      return head + '<p>' + (primary.length ? primary.map(function(x){ return '<b>'+escapeHtml(x.a.name)+'</b>: '+x.c.why[0]; }).join('</p><p>') : 'No one is in the buying window right now.') + '</p>';
    case 'prioritise:not_call':
      return head + '<p>' + (hold.length ? hold.map(function(x){ return '<b>'+escapeHtml(x.a.name)+'</b> — '+x.c.why[0]; }).join('</p><p>') : 'Nobody is on hold right now — the whole stack is safe to work.') + '</p>';
    case 'prioritise:changed_yesterday':
      return head + '<p>The stack re-ranks every time a score or contact outcome changes. Right now <b>'+escapeHtml((primary[0]&&primary[0].a.name)||'the top account')+'</b> leads with priority '+((primary[0]&&primary[0].c.priority)||'—')+'. Log outcomes as you work the list so tomorrow\'s ranking reflects today\'s calls.</p>';

    case 'understand:why_ranked':
      return head + (c ? '<p><b>'+escapeHtml(name)+'</b> — '+c.why.join(' ')+'</p>' : '<p>Pick an account to see why it ranks where it does.</p>');
    case 'understand:been_doing':
      var sig = account && account.l1 ? Object.keys(account.l1).map(function(k){ return escapeHtml(k)+': '+escapeHtml(account.l1[k]); }) : [];
      return head + '<p><b>'+escapeHtml(name)+'</b> — ' + (sig.length ? sig.join('. ') : 'no recent activity on file.') + '</p>';
    case 'understand:holding_back':
      return head + '<p>' + (s ? 'Trust is '+s.trust+' against a readiness of '+s.buying_readiness+' — '+(s.buying_readiness-s.trust>=20 ? 'they believe the problem is real, not yet that '+escapeHtml(name)+' has the answer.' : 'the gap is small; hesitation is more likely price or timing than trust.') : 'Select an account to see what\'s holding them back.') + '</p>';
    case 'understand:ready_or_researching':
      return head + '<p>' + (s ? (s.buying_readiness>=DTH.ready && s.intent>=DTH.intent ? '<b>'+escapeHtml(name)+'</b> is ready — readiness '+s.buying_readiness+' and intent '+s.intent+' both clear the bar.' : '<b>'+escapeHtml(name)+'</b> is still researching — readiness '+s.buying_readiness+', intent '+s.intent+'. One useful touch, no hard ask yet.') : 'Select an account first.') + '</p>';
    case 'understand:cares_about':
      var lead = account && account.l1 ? Object.values(account.l1)[0] : null;
      return head + '<p><b>'+escapeHtml(name)+'</b> — ' + (lead ? 'their own activity points to it: '+escapeHtml(lead) : 'no strong signal yet — ask directly on the next touch.') + '</p>';

    case 'craft:script_for':
      var beats = scriptBeats(agent, account || {scores:{trust:60,buying_readiness:60}, name:name}, c || {play: isSl ? 'call' : 'winback'}, 'call');
      return head + '<div class="ss-script"><div class="ss-shd">'+escapeHtml(name.toUpperCase())+'</div>' + beats.map(function(b){ return '<div class="ss-beat"><div class="ss-beat-l">'+b[0]+'</div>'+b[1]+'</div>'; }).join('') + '</div>';
    case 'craft:opener_30s':
      return head + '<p><em>"'+escapeHtml(name)+' — quick one. I noticed you\'ve been active on this lately, and most people at that stage are weighing up whether it\'s worth solving now. Is that where you\'re at?"</em></p><p style="color:var(--g3);font-size:11.5px">Under 30 seconds, ends in a question — keeps them talking.</p>';
    case 'craft:whatsapp_version':
      return head + '<p><em>"Hi '+escapeHtml(name)+' 👋 saw you\'ve been looking into this — happy to answer anything directly here, no pressure. What\'s the main thing you\'re weighing up?"</em></p>';
    case 'craft:email_version':
      return head + '<p><strong>Subject:</strong> Quick question about {'+escapeHtml(name)+'}\'s next step</p><p><em>"Hi — noticed the recent activity on your end and wanted to check in directly rather than let it go quiet. What would need to be true for this to be a clear yes?"</em></p>';
    case 'craft:proof_to_show':
      return head + '<p>' + (s && s.trust < DTH.trust ? 'Trust is the gap here (score '+s.trust+') — lead with one concrete result from a similar customer, not a feature list.' : 'Trust is solid — a quick reference or case study is a nice-to-have, not a requirement. A direct ask works.') + '</p>';
    case 'craft:shorter_less_salesy':
      return head + '<p><em>"'+escapeHtml(name)+' — worth a 10-minute call this week?"</em></p><p style="color:var(--g3);font-size:11.5px">Strip it back to one line and one question — the shorter version usually gets a faster reply.</p>';

    case 'handle:too_expensive':
      return head + '<p><em>"Compared to what this is costing you today, what would make the number feel fair?"</em> Reframe to value before touching the price. Offer a low-risk start before a discount.</p>';
    case 'handle:not_right_now':
      return head + '<p><em>"Understood — what would need to change for the timing to be right?"</em> Get a real reason and a real date, then set a callback for that date rather than a vague follow-up.</p>';
    case 'handle:use_competitor':
      return head + '<p><em>"Good to know — what\'s working well with them, and what would you change if you could?"</em> Listen for the gap, then show only the part of your offer that closes it.</p>';
    case 'handle:send_info':
      return head + '<p>"Send me some info" is often a polite no. Send one short, specific thing (not a brochure) and set a defined follow-up date rather than waiting for them to reply.</p>';
    case 'handle:no_budget':
      return head + '<p>Separate "no budget" from "not a priority yet." Ask what it would need to deliver to justify finding the budget — if the answer is vague, it\'s priority, not price.</p>';
    case 'handle:need_boss':
      return head + '<p>Ask to join that conversation, or arm them with a one-page summary of the case for their boss. Deals that go dark after "I\'ll check" usually needed that help and didn\'t get it.</p>';
    case 'handle:something_else':
      return head + '<p>Type the objection into the chat box below and I\'ll match it against similar accounts and give you a specific response.</p>';

    case 'launch:how_close':
      return head + '<p>' + (s && s.trust < DTH.trust ? 'Trust is still behind readiness for '+escapeHtml(name)+' — close on a low-risk start, not the full commitment.' : '<b>'+escapeHtml(name)+'</b> has the trust to support a direct ask — propose the plan and a start date.') + '</p>';
    case 'launch:smallest_ask':
      return head + '<p>The smallest reasonable next step for <b>'+escapeHtml(name)+'</b>: a 30-day pilot or a single-team rollout — small enough to say yes to this week, big enough to prove the case.</p>';
    case 'launch:ready_upgrade':
      return head + '<p>' + (secondary.length ? secondary.map(function(x){ return '<b>'+escapeHtml(x.a.name)+'</b> — '+x.c.why[0]; }).join('</p><p>') : 'No accounts are upgrade-ready right now.') + '</p>';
    case 'launch:offer_discount':
      return head + '<p>' + (s && s.trust >= DTH.trust ? 'No — trust is already high; a discount here signals the price was inflated. Ask directly instead.' : 'Only as a last resort, and only after the value case has been made — lead with a low-risk start first.') + '</p>';
    case 'launch:weighted_pipeline':
      var weighted = r.length ? Math.round(r.reduce(function(sum,x){ return sum + x.c.priority; }, 0) / r.length) : 0;
      return head + '<p>Weighted pipeline score across the current stack: <b>'+weighted+'</b> (average priority, which is itself each account\'s readiness weighted against its trust and intent).</p>';
    case 'launch:at_risk_no_touch':
      return head + '<p>' + (hold.length ? hold.map(function(x){ return '<b>'+escapeHtml(x.a.name)+'</b> — '+x.c.why[0]; }).join('</p><p>') : 'Nothing currently flagged as at-risk in the stack.') + '</p>';

    default:
      return head + '<p>I don\'t have a ready-made answer for that yet — try rephrasing in the chat box below.</p>';
  }
}
function renderDashGuide() {
    var agent = state.agent;
    var flow = DASH_FLOW[agent];
    var current = FLOW_TABS.indexOf(dashState.view);
    if (current === -1) current = 0;

    document.getElementById('dashGBody').innerHTML = flow.steps.map(function (step, i) {
        var isCur = i === current;
        var cls = 'flowst' + (isCur ? ' cur' : '');
        return '<div class="' + cls + '" onclick="showDashView(\'' + FLOW_TABS[i] + '\')">' +
               '<div class="flowst-dot">' + (i + 1) + '</div>' +
               '<div><div class="flowst-t">' + escapeHtml(step.t) + '</div><div class="flowst-c">' + escapeHtml(step.c) + '</div></div></div>';
    }).join('');
}

function setAgent(key) {
    state.agent = key;
    state.taskIndex = 0;
    root.setAttribute('data-agent', key);
    renderAgentTabs();

    var isDash = (key === 'mk' || key === 'sl');
    document.getElementById('bhClassic').style.display = isDash ? 'none' : 'grid';
    document.getElementById('bhDash').classList.toggle('on', isDash);

    if (isDash) {
        initDash(key);
    } else {
        renderGuide();
        document.getElementById('bhWsName').textContent = AGENTS[key].name;
        document.getElementById('bhWsSub').textContent = AGENTS[key].sub;
        resetChat();
    }
}

/* ══════════════════════════════════════════════════════════════
   DASHBOARD ENGINE — Marketing & Sales
   Shared account pool + a per-domain classifier/renderer.
   ══════════════════════════════════════════════════════════════ */
/* Real customers, pulled from BehavioralProfile by the route — no fake
   accounts, no MRR (there's no real revenue figure behind these profiles
   yet, so none is shown rather than inventing one). */
var ACCOUNTS = @json($accounts)
  .map(function (p) {
    return {
      name: p.name,
      email: p.email,
      seg: p.segment,
      scores: {
        intent: p.intent_score,
        engagement: p.engagement_score,
        buying_readiness: p.buying_readiness,
        churn: p.churn_score,
        loyalty: p.loyalty_score,
        trust: p.trust_score,
        frustration: p.frustration_score
      }
    };
  });
/* Thresholds calibrated to this real customer base's actual score spread
   (ready/intent were tuned for a wider demo distribution and left every
   real account either in "hold" or "upsell", with nobody ever reaching
   "call" — the buying-window bucket a fresh sales day starts from). */
var DTH = {trust:65, ready:45, intent:55, up:55, fr:40, churn:60};
var SEG_LABEL = {champion:"Champion",loyal:"Loyal",at_risk:"At risk",dormant:"Dormant","new":"New"};
var SEG_COLOR = {champion:"#0e7a35",loyal:"#1d4ed8",at_risk:"#b42332",dormant:"#9a6700","new":"#6d28d9"};
var dashState = { agent: 'sl', view: 'today', lead: null };
var dashDone = {}; /* per-account logged outcomes, session only */

/* ── SALES classifier: who to call, and why ── */
function classifySales(a){
  var s = a.scores, why = [], play='none', prio=0;
  if (a.seg==='at_risk' || s.churn > DTH.churn){
    play='hold'; why.push('Churn '+s.churn+' — this account belongs to Retention right now, not a sales call.');
  } else if ((a.seg==='champion'||a.seg==='loyal') && s.buying_readiness>DTH.up && s.trust>=DTH.trust && s.frustration<DTH.fr){
    play='upsell'; prio = Math.round(s.buying_readiness*0.7 + s.trust*0.3);
    why.push('Upsell-qualified: readiness '+s.buying_readiness+' with trust '+s.trust+' and frustration only '+s.frustration+'.');
  } else if (s.buying_readiness>=DTH.ready && s.intent>=DTH.intent){
    play='call'; prio = Math.round(s.buying_readiness*0.6 + s.intent*0.4);
    why.push('In the buying window: readiness '+s.buying_readiness+' × intent '+s.intent+'.');
    var gap = s.buying_readiness - s.trust;
    if (gap>=25) why.push('Trust is '+gap+' points behind readiness — they believe the problem is real, not yet that you\'re the fix.');
    else if (s.trust<DTH.trust) why.push('Trust is close behind readiness here — a direct ask lands better than another proof point. Skip the case study.');
    else why.push('Trust '+s.trust+' is high — skip the warm-up, ask directly.');
    if (a.event_days!=null){ prio += Math.round((30-a.event_days)/2); }
  } else if (s.buying_readiness>=45 || s.intent>=DTH.intent){
    play='nurture'; prio=(s.buying_readiness+s.intent)/4;
    why.push('Warming, not ready yet: readiness '+s.buying_readiness+', intent '+s.intent+'. One useful touch, no ask.');
  } else {
    why.push('No buying signal yet (readiness '+s.buying_readiness+', intent '+s.intent+').');
  }
  return {play:play, priority:Math.round(prio), why:why};
}
/* ── MARKETING classifier: which campaign play fits this account ── */
function classifyMarketing(a){
  var s = a.scores, why = [], play='none', prio=0;
  if (a.seg==='at_risk' || s.churn > DTH.churn){
    play='hold'; why.push('Churn '+s.churn+' — hand this one to Retention; a marketing push on an unhappy customer backfires.');
  } else if (a.seg==='dormant'){
    play='winback'; prio = (100-s.engagement)*0.5 + s.trust*0.3 + (60-s.churn>0?60-s.churn:0)*0.2;
    why.push('Gone quiet (engagement '+s.engagement+') but trust is still '+s.trust+' — the door is open. A short win-back series, no discount up front.');
  } else if (a.seg==='new' && s.buying_readiness>=45 && s.engagement<60){
    play='onboarding'; prio = s.buying_readiness*0.6 + (100-s.engagement)*0.4;
    why.push('New and keen (readiness '+s.buying_readiness+') but engagement is only '+s.engagement+' — keen-but-stalled is exactly when new customers quietly give up.');
  } else if (a.seg==='champion' && s.loyalty>=80){
    play='referral'; prio = s.loyalty*0.6 + (100-s.frustration)*0.4;
    why.push('Loyalty '+s.loyalty+', frustration only '+s.frustration+' — ask for one personal introduction, not a generic share link.');
  } else if (s.engagement>=45){
    play='nurture'; prio = s.engagement*0.5;
    why.push('Steady but not primed for a campaign yet (engagement '+s.engagement+'). Keep them warm.');
  } else {
    why.push('No clear campaign signal right now.');
  }
  return {play:play, priority:Math.round(prio), why:why};
}
function classifyFor(agent, a){ return agent==='sl' ? classifySales(a) : classifyMarketing(a); }
function rankedFor(agent){
  return ACCOUNTS.map(function(a){ return {a:a, c:classifyFor(agent,a)}; })
    .sort(function(x,y){ return y.c.priority - x.c.priority; });
}

var DASH_VTABS = [
  {k:'today', sl:"Today's stack", mk:"Today's stack"},
  {k:'accounts', sl:'Accounts', mk:'Accounts'},
  {k:'scripts', sl:'Script studio', mk:'Message studio'},
  {k:'forecast', sl:'Forecast', mk:'Forecast'},
  {k:'manager', sl:'Manager', mk:'Manager'}
];
var PLAY_LABEL = {call:'Call', upsell:'Upsell', hold:'Hold', nurture:'Nurture', winback:'Win-back', onboarding:'Onboarding', referral:'Referral', none:'—'};
var PLAY_BTN = {call:'Call now', upsell:'Call now', winback:'Launch →', onboarding:'Launch →', referral:'Launch →', nurture:'Queue touch', hold:'Hand to Retention'};

function initDash(agent){
  dashState.agent = agent;
  document.getElementById('dmTitle').textContent = AGENTS[agent].name + ' helper';
  renderDashGuide();
  renderDashVtabs();
  showDashView(dashState.view || 'today');
  dashResetChat();
}
function renderDashVtabs(){
  document.getElementById('dashVtabs').innerHTML = DASH_VTABS.map(function(t){
    var on = t.k===dashState.view ? ' on' : '';
    return '<button type="button" class="dvt'+on+'" onclick="showDashView(\''+t.k+'\')">'+escapeHtml(t[dashState.agent])+'</button>';
  }).join('');
}
function showDashView(v){
  dashState.view = v;
  renderDashVtabs();
  renderDashGuide();
  renderDashQuicks();
  var el = document.getElementById('dashView');
  if (v==='today') el.innerHTML = renderTodayStack();
  else if (v==='accounts') el.innerHTML = renderAccountsTable();
  else if (v==='scripts') el.innerHTML = renderScriptStudio();
  else if (v==='forecast') el.innerHTML = renderForecast();
  else if (v==='manager') el.innerHTML = renderManager();
  if (v==='scripts') selectScriptAccount(rankedFor(dashState.agent)[0].a.name, 'call');
}
var STACK_INTRO = {
  sl: {h:'RANKED STACK — WHO, IN ORDER', p:'Sorted by <b>readiness × intent</b>, trust-adjusted, then <b>time</b> (deadlines, callbacks due), <b>contact memory</b> (a cool-off after a touch) and <b>contact rules</b> (hours, do-not-call). Click any score to see what moved it — log the outcome after each call and I\'ll re-rank for tomorrow.'},
  mk: {h:'RANKED STACK — WHICH PLAY, FIRST', p:'Sorted by <b>segment fit × trust</b>, then <b>how long they\'ve been quiet or stalled</b> and <b>contact memory</b> (no back-to-back touches on the same account). Click any score to see what moved it — log the send and I\'ll re-rank tomorrow\'s list.'}
};
function metaLine(a, c){
  var bits = ['Priority '+c.priority, SEG_LABEL[a.seg]];
  if (a.event_days!=null) bits.push('Event in '+a.event_days+'d');
  else if (a.callback_due) bits.push('Callback due');
  if (a.city) bits.push(a.city);
  return bits.join(' · ');
}
function renderTodayStack(){
  var agent = dashState.agent;
  var r = rankedFor(agent);
  var primaryPlays = agent==='sl' ? ['call'] : ['winback','onboarding'];
  var secondaryPlays = agent==='sl' ? ['upsell'] : ['referral'];
  var primary = r.filter(function(x){ return primaryPlays.indexOf(x.c.play)!==-1; });
  var secondary = r.filter(function(x){ return secondaryPlays.indexOf(x.c.play)!==-1; });
  var shown = primary.slice(0, 2);
  var nurture = r.filter(function(x){ return x.c.play==='nurture'; });
  var hold = r.filter(function(x){ return x.c.play==='hold'; });
  function row(x, i){
    var a=x.a, c=x.c, s=a.scores, key=agent+':'+a.name;
    var logged = dashDone[key];
    var callBtn = logged
      ? '<button type="button" class="stk-call done" disabled>&check; Logged</button>'
      : '<button type="button" class="stk-call" onclick="logOutcome(\''+nameAttr(a.name)+'\')">'+PLAY_BTN[c.play]+'</button>';
    return '<div class="stkrow">'+
      '<div class="stk-n">'+String(i+1).padStart(2,'0')+'</div>'+
      callBtn+
      '<div><div class="stk-acct">'+escapeHtml(a.name)+(a.seg==='new'?'<span class="badge-new">NEW</span>':'')+'</div>'+
      '<div class="stk-mrr">'+metaLine(a,c)+'</div>'+
      '<div class="stk-scores">'+
        '<div class="sc"><div class="sc-v" style="color:'+scoreCol(s.buying_readiness)+'">'+s.buying_readiness+'</div><div class="sc-l">Ready</div></div>'+
        '<div class="sc"><div class="sc-v" style="color:'+scoreCol(s.intent)+'">'+s.intent+'</div><div class="sc-l">Intent</div></div>'+
        '<div class="sc"><div class="sc-v" style="color:'+scoreCol(s.trust)+'">'+s.trust+'</div><div class="sc-l">Trust</div></div>'+
      '</div></div>'+
      '<div class="stk-why">'+c.why.join(' ')+'</div>'+
      '<div class="stk-actions">'+
        '<button type="button" class="stkbtn" onclick="openScriptFor(\''+nameAttr(a.name)+'\')">'+(agent==='sl'?'Script →':'Copy →')+'</button>'+
        (logged?'':'<button type="button" class="stkbtn ghost" onclick="logOutcome(\''+nameAttr(a.name)+'\')">Log outcome</button>')+
      '</div></div>';
  }
  var intro = STACK_INTRO[agent];
  var html = '<div class="stack-intro"><div class="si-h">'+intro.h+'</div><div class="si-p">'+intro.p+'</div></div>';
  html += '<div class="sectionh">'+(agent==='sl'?"TODAY'S CONTACT STACK":"TODAY'S CAMPAIGN STACK")+'<span>'+shown.length+' of '+primary.length+' shown</span></div>';
  html += shown.length ? shown.map(row).join('') : '<div style="padding:20px;color:var(--g3);font-size:12.5px">Nothing urgent right now — check Accounts for the full list.</div>';
  if (secondary.length){ html += '<div class="sectionh">'+(agent==='sl'?'UPSELL — READY TO EXPAND':'REFERRAL — ASK FOR ONE NAME')+'</div>' + secondary.map(row).join(''); }
  if (nurture.length){ html += '<div class="sectionh warn">NURTURE — NOT READY YET</div>' + nurture.map(row).join(''); }
  if (hold.length){ html += '<div class="sectionh bad">HOLD — ROUTE TO RETENTION</div>' + hold.map(row).join(''); }
  return html;
}
function scoreCol(v){ return v>=70?'#0e7a35':v>=50?'#9a6700':'#b42332'; }
function renderAccountsTable(){
  var agent = dashState.agent;
  var r = rankedFor(agent);
  var rows = r.map(function(x){
    var a=x.a, s=a.scores;
    return '<tr><td class="acctn" onclick="openScriptFor(\''+nameAttr(a.name)+'\')">'+escapeHtml(a.name)+'</td>'+
      '<td><span class="segtag" style="background:'+SEG_COLOR[a.seg]+'22;color:'+SEG_COLOR[a.seg]+'">'+SEG_LABEL[a.seg]+'</span></td>'+
      '<td>'+s.buying_readiness+'</td><td>'+s.intent+'</td><td>'+s.trust+'</td><td>'+s.churn+'</td>'+
      '<td><span class="stk-play '+x.c.play+'">'+PLAY_LABEL[x.c.play]+'</span></td></tr>';
  }).join('');
  return '<table class="dtbl"><thead><tr><th>Account</th><th>Segment</th><th>Ready</th><th>Intent</th><th>Trust</th><th>Churn</th><th>Play</th></tr></thead><tbody>'+rows+'</tbody></table>';
}
var scriptAcct = null, scriptChan = 'call';
function renderScriptStudio(){
  var r = rankedFor(dashState.agent);
  var list = r.map(function(x){
    return '<div class="ss-item" data-name="'+nameAttr(x.a.name)+'" onclick="selectScriptAccount(\''+nameAttr(x.a.name)+'\')"><div class="n">'+escapeHtml(x.a.name)+'</div><div class="m">'+PLAY_LABEL[x.c.play]+' · Priority '+x.c.priority+'</div></div>';
  }).join('');
  return '<div class="ss-grid"><div class="ss-list">'+list+'</div><div class="ss-out" id="ssOut"></div></div>';
}
function selectScriptAccount(name, chan){
  scriptAcct = name; scriptChan = chan || 'call';
  document.querySelectorAll('.ss-item').forEach(function(el){ el.classList.toggle('on', el.getAttribute('data-name')===name); });
  var agent = dashState.agent;
  var chans = agent==='sl' ? ['call','email','linkedin'] : ['email','sms','ad'];
  var a = ACCOUNTS.filter(function(x){ return x.name===name; })[0];
  var c = classifyFor(agent, a);
  var chanBtns = chans.map(function(ch){ return '<button type="button" class="'+(ch===scriptChan?'on':'')+'" onclick="selectScriptAccount(\''+nameAttr(name)+'\',\''+ch+'\')">'+ch+'</button>'; }).join('');
  var beats = scriptBeats(agent, a, c, scriptChan);
  document.getElementById('ssOut').innerHTML =
    '<div class="ss-chan">'+chanBtns+'</div>'+
    '<div class="ss-script"><div class="ss-shd">'+escapeHtml(name.toUpperCase())+' · '+PLAY_LABEL[c.play].toUpperCase()+'</div>'+
    beats.map(function(b){ return '<div class="ss-beat"><div class="ss-beat-l">'+b[0]+'</div>'+b[1]+'</div>'; }).join('') +
    '</div>';
  dashState.lead = name;
}
function openScriptFor(name){ showDashView('scripts'); selectScriptAccount(name); }
function scriptBeats(agent, a, c, chan){
  var s = a.scores;
  if (c.play==='hold') return [['DO NOT PITCH','<em>Churn '+s.churn+'.</em> Route to Retention — this account needs a fix, not an offer.']];
  if (agent==='sl'){
    if (s.trust < DTH.trust){
      return [
        ['OPENER','<em>"I noticed your team\'s been looking at us a few times recently — that usually means the problem\'s real but something\'s holding the decision back. Can I ask what it is?"</em>'],
        ['PROOF','<em>"Businesses like yours typically see results within the first couple of months — happy to walk you through the numbers."</em>'],
        ['LOW-RISK CLOSE','<em>"Start on the smaller plan. If it doesn\'t move the numbers in 30 days, walk away and keep everything we found."</em>']
      ];
    }
    return [
      ['OPENER','<em>"You\'ve clearly found value already — I\'d like to talk about what\'s next."</em>'],
      ['DIRECT ASK','<em>"Given where you\'re at, the bigger plan pays for itself. Want me to set it up?"</em>']
    ];
  }
  if (c.play==='winback') return [
    ['SUBJECT','<em>"Here\'s what\'s new since you\'ve been away"</em>'],
    ['BODY','No apology, no discount yet — lead with momentum. Show one thing their own account found while they were gone.'],
    ['FOLLOW-UP','A short personal note 3–5 days later. A small offer only if they open twice without clicking.']
  ];
  if (c.play==='onboarding') return [
    ['SUBJECT','<em>"One thing left to finish"</em>'],
    ['BODY','A single, specific ask — the exact step they stalled on. Nothing else in this email.'],
    ['FOLLOW-UP','Day 2: show their own first result. Day 5: invite them into the weekly habit that keeps customers for years.']
  ];
  if (c.play==='referral') return [
    ['ASK','<em>"Who\'s one business owner you respect who struggles with this?"</em> — one name beats a share link roughly 3×.'],
    ['FOLLOW-UP','Personal intro → VIP treatment for the new account → report the result back to them.']
  ];
  return [['TOUCH','Keep it useful and low-pressure — one relevant resource, no ask yet.']];
}
function avgScore(list, key){ return list.length ? Math.round(list.reduce(function(s,x){return s+x.a.scores[key];},0)/list.length) : 0; }
function renderForecast(){
  var agent = dashState.agent;
  var r = rankedFor(agent);
  if (agent==='sl'){
    var call = r.filter(function(x){return x.c.play==='call';});
    var up = r.filter(function(x){return x.c.play==='upsell';});
    return '<div class="fc-grid">'+
      '<div class="fc-cell"><div class="fc-h">IN THE BUYING WINDOW</div>'+
      (call.length ? call.map(function(x){return '<div class="fc-row"><span><b>'+escapeHtml(x.a.name)+'</b></span><span>Ready '+x.a.scores.buying_readiness+'</span></div>';}).join('') : '<div class="fc-row"><span>Nobody in the buying window right now</span></div>')+
      '<div class="fc-row fc-tot"><span>Average readiness</span><span>'+avgScore(call,'buying_readiness')+' <small>across '+call.length+' account'+(call.length===1?'':'s')+'</small></span></div></div>'+
      '<div class="fc-cell"><div class="fc-h">UPSELL-QUALIFIED</div>'+
      (up.length ? up.map(function(x){return '<div class="fc-row"><span><b>'+escapeHtml(x.a.name)+'</b></span><span>Ready '+x.a.scores.buying_readiness+'</span></div>';}).join('') : '<div class="fc-row"><span>Nobody upsell-qualified right now</span></div>')+
      '<div class="fc-row fc-tot"><span>Average readiness</span><span>'+avgScore(up,'buying_readiness')+' <small>across '+up.length+' account'+(up.length===1?'':'s')+'</small></span></div></div>'+
      '</div>';
  }
  var wb = r.filter(function(x){return x.c.play==='winback';});
  var ob = r.filter(function(x){return x.c.play==='onboarding';});
  return '<div class="fc-grid">'+
    '<div class="fc-cell"><div class="fc-h">WIN-BACK CANDIDATES</div>'+
    (wb.length ? wb.map(function(x){return '<div class="fc-row"><span><b>'+escapeHtml(x.a.name)+'</b></span><span>Trust '+x.a.scores.trust+'</span></div>';}).join('') : '<div class="fc-row"><span>Nobody dormant-and-winnable right now</span></div>')+
    '<div class="fc-row fc-tot"><span>Accounts</span><span>'+wb.length+'</span></div></div>'+
    '<div class="fc-cell"><div class="fc-h">ONBOARDING AT RISK OF STALLING</div>'+
    (ob.length ? ob.map(function(x){return '<div class="fc-row"><span><b>'+escapeHtml(x.a.name)+'</b></span><span>Engagement '+x.a.scores.engagement+'</span></div>';}).join('') : '<div class="fc-row"><span>No new accounts currently stalling</span></div>')+
    '<div class="fc-row fc-tot"><span>Accounts</span><span>'+ob.length+'</span></div></div>'+
    '</div>';
}
function renderManager(){
  var agent = dashState.agent;
  var r = rankedFor(agent);
  var loggedCount = Object.keys(dashDone).filter(function(k){return k.indexOf(agent+':')===0;}).length;
  var active = r.filter(function(x){ return ['call','upsell','winback','onboarding','referral'].indexOf(x.c.play)!==-1; });
  return '<div class="mg-grid">'+
    '<div class="mg-cell"><div class="mg-h">STACK ADHERENCE TODAY</div>'+
    '<div class="mg-kpi">'+loggedCount+' <small>of '+active.length+' actioned</small></div>'+
    '<div style="font-size:11.5px;color:var(--g2);margin-top:8px;line-height:1.6">Every "'+(agent==='sl'?'Call now':'Launch')+'" pressed on Today\'s Stack counts here — it\'s how you tell whether the stack is actually being worked.</div></div>'+
    '<div class="mg-cell"><div class="mg-h">AVERAGE READINESS IN STACK</div>'+
    '<div class="mg-kpi">'+avgScore(active,'buying_readiness')+' <small>of 100</small></div>'+
    '<div style="font-size:11.5px;color:var(--g2);margin-top:8px;line-height:1.6">Average buying-readiness score across accounts currently ranked as an active play.</div></div>'+
    '</div>';
}
function logOutcome(name){
  dashDone[dashState.agent+':'+name] = true;
  showDashView(dashState.view);
}

/* ── Mira panel (dashboard) — reuses PLAYBOOKS where the question matches ── */
function dashResetChat(){
  var chat = document.getElementById('dashChat');
  chat.innerHTML = '';
  renderDashQuicks();
}
function dashPushMsg(role, html){
  var chat = document.getElementById('dashChat');
  var el = document.createElement('div');
  el.className = 'msg ' + role;
  el.innerHTML = html;
  chat.appendChild(el);
  chat.scrollTop = chat.scrollHeight;
}
function dashQuick(q){
  dashPushMsg('user', escapeHtml(q));
  var pb = PLAYBOOKS[q];
  if (pb) { dashPushMsg('bot', '<div class="tag">'+pb.tag+'</div>'+pb.html + (pb.how?renderHow(pb.how):'') + (pb.acts?renderActs(pb.acts):'')); return; }
  dashPushMsg('bot', "I don't have a ready-made playbook for that one yet — try one of the buttons above.");
}
function dashSend(){
  var input = document.getElementById('dashInput');
  var text = input.value.trim();
  if (!text) return;
  dashPushMsg('user', escapeHtml(text));
  input.value = '';
  var inWords = normWords(text), best=null, bestScore=0;
  Object.keys(PLAYBOOKS).forEach(function(k){
    if (PLAYBOOKS[k].agent !== dashState.agent) return;
    var score = normWords(k).filter(function(w){ return inWords.indexOf(w)!==-1; }).length;
    if (score>bestScore){ bestScore=score; best=k; }
  });
  if (bestScore>=2){ var pb=PLAYBOOKS[best]; dashPushMsg('bot', '<div class="tag">'+pb.tag+'</div>'+pb.html + (pb.how?renderHow(pb.how):'') + (pb.acts?renderActs(pb.acts):'')); return; }
  dashPushMsg('bot', "I don't have a ready-made playbook for that yet — try a customer's name, or use one of the buttons above.");
}
document.getElementById('dashInput') && document.getElementById('dashInput').addEventListener('keydown', function(e){
  if (e.key === 'Enter') { e.preventDefault(); dashSend(); }
});
window.showDashView = showDashView;
window.selectScriptAccount = selectScriptAccount;
window.openScriptFor = openScriptFor;
window.logOutcome = logOutcome;
window.dashQuick = dashQuick;
window.dashSend = dashSend;
window.dashPromptClick = dashPromptClick;

function renderHow(how) {
    var stepsHtml = how.steps.map(function (s, i) {
        return '<div class="hstep"><span class="hstep-n">' + (i + 1) + '</span><div>' + s[0] + '<span class="hstep-route">' + s[1] + '</span></div></div>';
    }).join('');
    return '<div class="how"><div class="how-hd">' + how.title + '</div>' + stepsHtml +
           '<div class="how-watch">How you’ll know it’s working<b>' + how.watch + '</b></div></div>';
}

function renderActs(acts) {
    return '<div class="acts">' + acts.map(function (a) {
        return '<div class="act"><div><div class="act-t">' + a.title + '</div><div class="act-d">' + a.desc + '</div>' +
               '<div class="act-meta">' + a.route + '</div></div>' +
               '<button type="button" class="act-btn" onclick="this.textContent=\'✓ Done\';this.classList.add(\'done\')">' + a.btn + '</button></div>';
    }).join('') + '</div>';
}

function pushPlaybook(pb) {
    var html = '<div class="tag">' + pb.tag + '</div>' + pb.html;
    if (pb.how) html += renderHow(pb.how);
    if (pb.acts) html += renderActs(pb.acts);
    pushMsg('bot', html);
}

function pushMsg(role, html) {
    var chat = document.getElementById('bhChat');
    var el = document.createElement('div');
    el.className = 'msg ' + role;
    el.innerHTML = html;
    chat.appendChild(el);
    chat.scrollTop = chat.scrollHeight;
}

function resetChat() {
    document.getElementById('bhChat').innerHTML = '';
    pushMsg('bot', '<div class="tag">' + escapeHtml(AGENTS[state.agent].name) + ' helper</div>' + AGENTS[state.agent].intro);
    var q = document.getElementById('bhQuick');
    q.innerHTML = AGENTS[state.agent].quicks.map(function (qk) {
        return '<button type="button" class="qk" onclick="handleQuick(\'' + qk.q.replace(/'/g, "\\'") + '\')">' + escapeHtml(qk.label) + '</button>';
    }).join('');
}

function handleQuick(q) {
    pushMsg('user', escapeHtml(q));
    var pb = PLAYBOOKS[q];
    if (pb) { pushPlaybook(pb); return; }
    pushMsg('bot', "I don't have a ready-made playbook for that one yet — try one of the buttons above.");
}

function normWords(s) {
    return s.toLowerCase().replace(/[^a-z0-9 ]/g, ' ').split(/\s+/).filter(function (w) { return w.length > 3; });
}

function matchPlaybook(text) {
    var inWords = normWords(text);
    var best = null, bestScore = 0;
    Object.keys(PLAYBOOKS).forEach(function (k) {
        if (PLAYBOOKS[k].agent !== state.agent) return;
        var score = normWords(k).filter(function (w) { return inWords.indexOf(w) !== -1; }).length;
        if (score > bestScore) { bestScore = score; best = k; }
    });
    return bestScore >= 2 ? PLAYBOOKS[best] : null;
}

function sendMsg() {
    var input = document.getElementById('bhInput');
    var text = input.value.trim();
    if (!text) return;
    pushMsg('user', escapeHtml(text));
    input.value = '';
    var pb = matchPlaybook(text);
    if (pb) { pushPlaybook(pb); return; }
    pushMsg('bot', "I don't have a ready-made playbook for that yet — try rephrasing with a customer's name, or use one of the buttons above.");
}

document.getElementById('bhInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); sendMsg(); }
});

document.addEventListener('click', function (e) {
    var wrap = document.getElementById('bhAvatarWrap');
    var drop = document.getElementById('bhDropdown');
    if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});

var ICON_EXPAND = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>';
var ICON_COMPRESS = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3v3a2 2 0 0 1-2 2H4"/><path d="M15 3v3a2 2 0 0 0 2 2h3"/><path d="M9 21v-3a2 2 0 0 0-2-2H4"/><path d="M15 21v-3a2 2 0 0 1 2-2h3"/></svg>';

function toggleSidebarCollapse() {
    var sidebar = document.getElementById('platformSidebar');
    if (!sidebar) return;
    var collapsed = sidebar.classList.toggle('bh-sidebar-collapsed');
    if (collapsed) {
        sidebar.style.width = '0px';
        sidebar.style.minWidth = '0px';
        sidebar.style.overflow = 'hidden';
        sidebar.style.borderRightWidth = '0px';
    } else {
        sidebar.style.width = '';
        sidebar.style.minWidth = '';
        sidebar.style.overflow = '';
        sidebar.style.borderRightWidth = '';
    }
    document.getElementById('bhFullBtn').innerHTML = collapsed ? ICON_COMPRESS : ICON_EXPAND;
    document.getElementById('bhFullBtn').title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
}

window.setAgent = setAgent;
window.toggleStep = toggleStep;
window.handleQuick = handleQuick;
window.sendMsg = sendMsg;
window.toggleSidebarCollapse = toggleSidebarCollapse;

setAgent('mk');

})();
</script>
@endsection
