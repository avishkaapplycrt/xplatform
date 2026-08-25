{{-- resources/views/client/business-helpers.blade.php --}}
@extends('layouts.platform')

@section('title', 'Business Helpers')

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

        {{-- Main: guide (left) + console (right) --}}
        <div class="main">
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

    </div>
    </div>

</div>

<style>
#bhRoot{
    --f1:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
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
#bhRoot .msg.bot .tag{font-size:10.5px;font-weight:700;margin-bottom:8px;color:var(--ac-d)}
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
  intro:"<p>I help you run campaigns that actually work — using what the platform knows about your customers.</p><p>Right now I can see <strong>2 customers who went quiet but have a good chance of coming back</strong>, and <strong>2 new customers</strong> who need a proper welcome so they stick around.</p><p>Pick a job below, or just ask me anything.</p>",
  quicks:[
    {label:"Win back quiet customers", q:"Design a win-back campaign for my dormant segment"},
    {label:"Welcome new customers", q:"Create an onboarding email sequence for new accounts"},
    {label:"Get referrals", q:"Design a referral campaign using my champion accounts"}]},
sl: {name:"Sales", sub:"Know who to call · What to say",
  intro:"<p>I tell you who's ready to buy and what to say to them.</p><p>Right now: <strong>Kite Travel and Trellis Insurance look ready to buy</strong> — but both are still deciding whether to trust you, so the approach matters. I'll give you the exact words.</p><p>Pick a job below, or ask me about any customer.</p>",
  quicks:[
    {label:"Who do I call today?", q:"Prioritise my pipeline — who should I contact today and why?"},
    {label:"Who'll buy more?", q:"Which existing accounts are ready for an upsell?"},
    {label:"Words for my best lead", q:"Write me an outreach script for my highest-readiness account"}]},
ch: {name:"Customer Retention", sub:"Spot who's leaving · Save them",
  intro:"<p>I spot which customers are about to leave and tell you exactly how to keep them.</p><p><strong>Right now $8,900/month is at risk</strong> across three customers. The biggest one — Meridian Health — isn't leaving over price: something keeps breaking for them. A discount would make it worse. A fix and a phone call will save them.</p><p>Pick a job below, or ask about any customer.</p>",
  quicks:[
    {label:"Who's about to leave?", q:"Triage my at-risk accounts — what do I do about each one this week?"},
    {label:"Win back quiet ones", q:"Build a win-back plan for my dormant accounts"},
    {label:"Why is Meridian unhappy?", q:"Why is Meridian Health churning and what exactly should I do?"}]}
};

/* ═══ PLAYBOOKS — canned rich answers behind the quick-action buttons ═══ */
var PLAYBOOKS = {
"Design a win-back campaign for my dormant segment": {agent:"mk", tag:"Marketing · Win-back plan",
 html:"<p><strong>What I noticed —</strong> Quarry Analytics and Lumen EdTech ($2,600/month between them) both stopped using you — but neither has walked away. Quarry visited your help pages from their office <em>last week</em>, and Lumen still opens most of your emails. The door is open.</p><p><strong>The plan —</strong> three friendly emails, a few days apart: <strong>1)</strong> “Here's what's new since you've been away” — momentum, not apology. <strong>2)</strong> Something useful their own account data found. <strong>3)</strong> A short personal note from you offering a 15-minute catch-up. <strong>No discount in the first email</strong> — people come back for value; discounts come out only if they open twice without clicking.</p>",
 how:{title:"How to set it up", watch:"They start logging in again — expect roughly 3× the usual comeback rate", steps:[
  ["Find them: your customer list → the “gone quiet” group, sorted by win-back chance.","Customers page"],
  ["Create the campaign, choose these 2 customers, channel: email.","Campaigns → New"],
  ["Paste in the three messages — press the button below and I'll write them.","Campaign editor"],
  ["Space the emails 3–5 days apart; stop automatically if they come back.","Campaign settings"],
  ["Launch it.","Campaigns → Launch"],
  ["Add a warning so you're told the moment either of them logs back in.","Alerts → New rule"]]},
 acts:[{title:"Set up the win-back emails",desc:"2 customers · $2,600/month at stake · 3 friendly messages",route:"Campaigns → New",btn:"Set up"},
       {title:"Warn me when they return",desc:"So you can welcome them back personally, same day",route:"Alerts → New rule",btn:"Set up"}]},

"Create an onboarding email sequence for new accounts": {agent:"mk", tag:"Marketing · New-customer welcome",
 html:"<p><strong>What I noticed —</strong> Trellis Insurance and Kite Travel both signed up recently and are keen — but neither has properly settled in. Trellis got 60% through setup and <em>stalled at connecting their data</em>. Keen-but-stalled is exactly when new customers quietly give up.</p><p><strong>The plan —</strong> four short emails over ten days: <strong>Day 0:</strong> one single ask — finish that setup step, nothing else. <strong>Day 2:</strong> show them their own first result (their data beats any brochure). <strong>Day 5:</strong> invite them to the weekly summary — that habit is what keeps customers for years. <strong>Day 10:</strong> a gentle look at what the next tier unlocks.</p>",
 how:{title:"How to set it up", watch:"New customers finishing setup within 2 days and using you weekly by week 2", steps:[
  ["Check your “new customers” group — you'll see where each one stalled.","Customers page"],
  ["Create the campaign for the new-customer group, 4 emails, days 0/2/5/10.","Campaigns → New"],
  ["Set it to stop nagging anyone who completes setup.","Campaign settings"],
  ["Launch, then watch opens and completions in the results page.","Campaigns → Results"]]},
 acts:[{title:"Set up the welcome series",desc:"2 new customers · 4 emails · stops when they're settled",route:"Campaigns → New",btn:"Set up"}]},

"Design a referral campaign using my champion accounts": {agent:"mk", tag:"Marketing · Referrals",
 html:"<p><strong>What I noticed —</strong> Solstice Energy and BluePeak SaaS love you — top satisfaction, growing usage, one gave you a 9/10 rating. Happy customers like these will recommend you — <em>but only if you ask properly</em>.</p><p><strong>The ask that works —</strong> don't send a “share this link” email. Ask each one personally: <em>“Who's one business owner you respect who struggles with keeping customers?”</em> Asking for ONE name gets about 3× more introductions than asking them to share. Then: personal intro → you give the new customer VIP treatment → report the good result back to whoever introduced them.</p>",
 how:{title:"How to set it up", watch:"Replies with names in them — and new signups tagged as referrals", steps:[
  ["Confirm your happiest customers in the “fans” group.","Customers page"],
  ["Create a small campaign: just these 2, personal email from you.","Campaigns → New"],
  ["One follow-up after 3 days if no reply — then stop. Never chase fans twice.","Campaign settings"],
  ["Tag anyone they introduce, so you can see the referrals working.","New customer → tag"],
  ["Check the results in a month or two.","Reports"]]},
 acts:[{title:"Send the referral asks",desc:"2 happy customers · personal one-name ask",route:"Campaigns → New",btn:"Set up"}]},

"Prioritise my pipeline — who should I contact today and why?": {agent:"sl", tag:"Sales · Today's call list",
 html:"<p><strong>Your call list, in order:</strong></p><table><tr><th>#</th><th>Who</th><th>Why now</th><th>Approach</th></tr><tr><td>1</td><td><strong>Kite Travel</strong></td><td>Checked your prices 3× this month, watched your demo twice</td><td>Interested but not convinced it's you — show a success story, offer an easy low-risk start</td></tr><tr><td>2</td><td><strong>Trellis Insurance</strong></td><td>New signup, very engaged, came via a comparison site</td><td>Same — proof first, then a simple yes</td></tr><tr><td>3</td><td><strong>BluePeak SaaS</strong></td><td>Grew from 12 to 19 seats, asking about enterprise features</td><td>Existing fan — just ask directly about the bigger plan</td></tr></table><p><strong>Skip today:</strong> anyone marked at-risk — selling to an unhappy customer makes it worse.</p>",
 how:{title:"How to do this yourself any morning", watch:"Calls turning into meetings — and tomorrow's list building itself", steps:[
  ["Open your customer list, sort by “ready to buy”.","Customers page"],
  ["Keep only the genuinely interested ones — the interest column.","Same page"],
  ["Check trust before each call: low = lead with proof; high = just ask.","Customer profile"],
  ["Ask me for the words for any call — button below.","Agent panel"],
  ["Log each call so the platform learns what works for you.","Customer → Log contact"]]},
 acts:[{title:"Get the words for Kite Travel",desc:"Success-story opener + easy-start close",route:"Agent panel",btn:"Write it"},
       {title:"Warn me about hot leads",desc:"Instant alert whenever anyone becomes very ready to buy",route:"Alerts → New rule",btn:"Set up"}]},

"Which existing accounts are ready for an upsell?": {agent:"sl", tag:"Sales · Who'll buy more",
 html:"<p><strong>Three checks before offering anyone more:</strong> ready to buy? trusts you? not annoyed about anything? All three must be yes.</p><ul style='margin:4px 0 8px 15px'><li style='margin-bottom:3px'><strong>BluePeak SaaS — yes, yes, yes.</strong> Grew from 12 to 19 seats and reading about your bigger plan. Just ask directly.</li><li style='margin-bottom:3px'><strong>Solstice Energy — yes.</strong> Already made 2 expansion enquiries. Frame it around what they use most.</li><li><strong>Fable Media — nearly.</strong> Invite them to try the new module rather than talking price.</li></ul><p>Never offer more to anyone who's currently annoyed — nobody on this list is.</p>",
 how:{title:"How to do this", watch:"Extra monthly revenue — with satisfaction staying high", steps:[
  ["Look only at your happy groups — fans and regulars.","Customers page"],
  ["Run the three checks on each: ready / trusts you / not annoyed.","Customer profiles"],
  ["See what the platform suggests offering each one.","Profile → Suggestions"],
  ["Make the offers — I can write each message.","Agent panel"],
  ["A month later: did they buy, and are they still happy? Both matter.","Reports"]]},
 acts:[{title:"Offer BluePeak the bigger plan",desc:"Already using 19 seats and reading about it",route:"Agent panel",btn:"Write it"},
       {title:"Invite Fable to try the new module",desc:"Soft invitation, no price talk",route:"Agent panel",btn:"Write it"}]},

"Write me an outreach script for my highest-readiness account": {agent:"sl", tag:"Sales · Your words for Kite Travel",
 html:"<p><strong>The situation —</strong> Kite Travel really wants to solve this problem (prices checked 3×, demo watched twice, they even asked about data ownership in chat). But they don't fully trust you yet. So: don't push — prove.</p><p><strong>Opening:</strong> <em>“I noticed your team's been looking at us a few times this month — that usually means the problem's real but something's holding the decision back. Can I guess what it is?”</em></p><p><strong>The proof:</strong> <em>“Travel businesses using us typically see repeat bookings climb within two months — happy to show you real numbers.”</em></p><p><strong>The easy yes:</strong> <em>“Start on the small plan. If your numbers don't move in 30 days, walk away and keep everything we found.”</em></p>",
 how:{title:"Before you call", watch:"Trust score climbing after the call — and a yes to the easy start", steps:[
  ["Glance at their profile — scores change daily.","Customer profile"],
  ["Have your one success story ready — same industry if possible.","Your notes"],
  ["Make the call, offer the low-risk start.","Phone"],
  ["Log the call afterwards so results are tracked.","Customer → Log contact"],
  ["Set a warning for when their trust rises — that's when to talk bigger plans.","Alerts → New rule"]]},
 acts:[{title:"Log this as today's priority call",desc:"Script attached · low-risk close",route:"Customer → Log contact",btn:"Queue"}]},

"Triage my at-risk accounts — what do I do about each one this week?": {agent:"ch", tag:"Customer Retention · Who's about to leave",
 html:"<p><strong>$8,900/month is at risk across three customers.</strong> Each one is leaving for a different reason — so each needs a different rescue:</p><table><tr><th>Customer</th><th>Worth</th><th>What's really going on</th><th>This week</th></tr><tr><td><strong>Meridian Health</strong></td><td>$4,200/mo</td><td>Something keeps breaking for them — 14 error sessions, 3 unhappy support calls</td><td>Fix the broken thing, then YOU call them. <strong>No discount</strong>.</td></tr><tr><td><strong>NovaPay</strong></td><td>$2,800/mo</td><td>Stuck on a technical step for 6 days, main contact just left the company</td><td>Solve the ticket + get introduced to the new person</td></tr><tr><td><strong>Cartwheel</strong></td><td>$1,900/mo</td><td>Things breaking AND their card is failing</td><td>Fix first, then a goodwill offer</td></tr></table><p><strong>Order matters:</strong> fix Meridian's problem BEFORE calling, so the call opens with “it's done” — not “we're working on it”.</p>",
 how:{title:"How to run the rescues", watch:"Risk scores falling week by week · all three renewing", steps:[
  ["Open each customer's profile and confirm what's annoying them.","Customer profiles"],
  ["For Meridian: find exactly where it breaks for them.","Profile → Journey view"],
  ["Set up a rescue plan per customer: fix → personal call → 30-day check-in.","Campaigns → New"],
  ["Start each one.","Campaign → Launch"],
  ["Add daily warnings on all three so you know immediately if anyone gets worse.","Alerts → New rule"],
  ["In 30 days, see who stayed. The platform learns from every rescue.","Reports"]]},
 acts:[{title:"Rescue Meridian ($4,200/mo)",desc:"Fix the errors first, then a personal call — no discount",route:"Campaigns → New",btn:"Start"},
       {title:"Rescue NovaPay + Cartwheel",desc:"$4,700/mo combined · different fix for each",route:"Campaigns → New",btn:"Start"},
       {title:"Daily warnings on all three",desc:"Know same-day if anyone slips further",route:"Alerts → New rule",btn:"Set up"}]},

"Build a win-back plan for my dormant accounts": {agent:"ch", tag:"Customer Retention · Bring them back",
 html:"<p><strong>Good news hiding in your quiet list —</strong> Quarry Analytics and Lumen EdTech ($2,600/month) both went quiet, but the signs say they're winnable: Quarry looked at your help pages from work last week, and Lumen opens nearly every email you send. They haven't left — they've just drifted.</p><p><strong>The 3-week plan —</strong> Week 1: “here's what's new” email + one useful thing their own account found. Week 2: if opened but not clicked, send a small offer; if clicked, invite to a 15-minute restart chat. Week 3: one short personal note from you — then stop, and let the alert watch for their return.</p>",
 how:{title:"How to set it up", watch:"Quiet customers active again — expect ~3× the normal comeback rate", steps:[
  ["Your quiet list, sorted by win-back chance — these two are on top.","Customers page"],
  ["Create the campaign with the 3 weekly messages.","Campaigns → New"],
  ["Add the smart rule: opened-not-clicked → offer · clicked → invite to chat.","Campaign settings"],
  ["Launch.","Campaign → Launch"],
  ["Warning on: tell me the moment either logs in.","Alerts → New rule"],
  ["Count the comebacks in a month.","Reports"]]},
 acts:[{title:"Start the welcome-back series",desc:"Quarry + Lumen · $2,600/mo · 3 gentle touches",route:"Campaigns → New",btn:"Start"},
       {title:"Tell me when they return",desc:"So you can say hello personally, same day",route:"Alerts → New rule",btn:"Set up"}]},

"Why is Meridian Health churning and what exactly should I do?": {agent:"ch", tag:"Customer Retention · Why Meridian is leaving",
 html:"<p><strong>What's happening —</strong> Meridian Health is $4,200/month and their risk score is the highest of any customer you have.</p><p><strong>Why —</strong> not price. Their team hit an error loop in your reports module 14 times this month and session depth dropped 62% — something is genuinely broken for them. They've called support 3 times in 30 days, sounding unhappy each time. No account check-in has been logged in 90 days, and their email opens have fallen from 34% to 8% — they're pulling away quietly. Their renewal is in 41 days.</p><p><strong>What to do —</strong> fix the reports error first, then call personally — in that order. A discount here would tell them their broken product is fine, which makes it worse, not better.</p>",
 how:{title:"This week", watch:"Their risk score falling — and the renewal going through", steps:[
  ["Open Meridian's profile and find exactly where the reports module breaks for them.","Customer profile → Journey view"],
  ["Get that fixed — flag it as priority given what's at stake.","Support → Escalate"],
  ["Call them yourself once it's fixed, not before.","Phone"],
  ["Log the call, then set a 30-day check-in.","Customer → Log contact"],
  ["Add a daily warning so you hear immediately if they get worse before then.","Alerts → New rule"]]},
 acts:[{title:"Escalate the reports-module fix",desc:"14 error sessions this month — root cause of the churn risk",route:"Support → Escalate",btn:"Escalate"},
       {title:"Queue the personal call",desc:"After the fix ships — opens with “it's done”, not “we're working on it”",route:"Customer → Log contact",btn:"Queue"}]}
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

function setAgent(key) {
    state.agent = key;
    state.taskIndex = 0;
    root.setAttribute('data-agent', key);
    renderAgentTabs();
    renderGuide();
    document.getElementById('bhWsName').textContent = AGENTS[key].name;
    document.getElementById('bhWsSub').textContent = AGENTS[key].sub;
    resetChat();
}

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

window.setAgent = setAgent;
window.toggleStep = toggleStep;
window.handleQuick = handleQuick;
window.sendMsg = sendMsg;

setAgent('mk');

})();
</script>
@endsection
