{{--
    Business Helpers — the Mock Master Helper look & animations (Part 9, Nov 2026).
    Included only by client/business-helpers.blade.php. Design and motion only: it adds classes, indicators and
    chips around what the page already renders, and wraps a few of its window.* functions without changing
    what they do. No new data. The sidebar rules apply on this page only.
--}}
<style>
:root{--mmx-ease:cubic-bezier(.22,.8,.26,1);--mmx-spring:cubic-bezier(.34,1.56,.64,1)}
@keyframes mmxRise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@keyframes mmxRowIn{from{opacity:0;transform:translateX(-10px)}to{opacity:1;transform:none}}
@keyframes mmxSlideR{from{opacity:0;transform:translateX(-14px)}to{opacity:1;transform:none}}
@keyframes mmxSlideL{from{opacity:0;transform:translateX(18px)}to{opacity:1;transform:none}}
@keyframes mmxBubIn{from{opacity:0;transform:translateY(8px) scale(.94)}to{opacity:1;transform:none}}
@keyframes mmxPop{from{opacity:0;transform:scale(.94) translateY(8px)}to{opacity:1;transform:none}}
@keyframes mmxChipIn{from{opacity:0;transform:scale(.6)}to{opacity:1;transform:none}}
@keyframes mmxFade{from{opacity:0}to{opacity:1}}
@keyframes mmxGrowX{from{transform:scaleX(0)}to{transform:scaleX(1)}}
@keyframes mmxPulse{0%{box-shadow:0 0 0 0 rgba(22,163,74,.5)}70%{box-shadow:0 0 0 6px rgba(22,163,74,0)}100%{box-shadow:0 0 0 0 rgba(22,163,74,0)}}
@keyframes mmxBlink{0%,80%,100%{opacity:.25;transform:translateY(0)}40%{opacity:1;transform:translateY(-3px)}}
@keyframes mmxTwinkle{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(.82);opacity:.75}}
@keyframes mmxSpin{to{transform:rotate(360deg)}}

/* Header buttons lift on hover */
.bh-page > header button,.bh-page > header a{transition:transform .18s var(--mmx-ease),box-shadow .2s,background-color .2s,color .2s}
.bh-page > header button:hover,.bh-page > header a:hover{transform:translateY(-1px);box-shadow:0 4px 12px -6px rgba(15,23,42,.3)}
.bh-page > header button:active{transform:scale(.97)}

/* Agent tabs: padded white bar, rounded sliding background with a soft gradient (same colours as before) */
#bhRoot .atabs{padding:5px;gap:4px;border-radius:var(--card-r,14px)}
#bhRoot .atab,#bhRoot .atab + .atab{border-left:none!important;border-radius:10px;min-height:46px;z-index:1;transition:color .3s}
#bhRoot .atabs.mmx-slid .atab,#bhRoot .atabs.mmx-slid .atab.on,#bhRoot .atabs.mmx-slid .atab.on:hover{background:transparent}
#bhRoot .atabs.mmx-slid .atab:hover:not(.on){background:var(--p1)}
#bhRoot .atab .amono svg{transition:transform .4s var(--mmx-spring)}
#bhRoot .atab.on .amono svg{transform:scale(1.15) rotate(-8deg)}
#bhRoot .mmx-at-slider{position:absolute;top:5px;bottom:5px;left:0;z-index:0;border-radius:10px;background-color:var(--ac);background-image:linear-gradient(135deg,var(--ac) 0%,color-mix(in srgb,var(--ac) 58%,#fff) 100%);box-shadow:0 8px 20px -10px var(--ac);transition:transform .45s var(--mmx-spring),width .45s var(--mmx-spring),background-color .4s,box-shadow .4s;pointer-events:none}

/* Steps: slide in when the agent changes, the active number springs, hover nudge */
#bhRoot .bhx-in .flowst{animation:mmxSlideR .45s var(--mmx-ease) both}
#bhRoot .bhx-in .flowst:nth-child(2){animation-delay:.05s}#bhRoot .bhx-in .flowst:nth-child(3){animation-delay:.1s}#bhRoot .bhx-in .flowst:nth-child(4){animation-delay:.15s}#bhRoot .bhx-in .flowst:nth-child(5){animation-delay:.2s}#bhRoot .bhx-in .flowst:nth-child(n+6){animation-delay:.25s}
#bhRoot .flowst{transition:background .25s,transform .25s var(--mmx-ease)}
#bhRoot .flowst:hover{transform:translateX(3px)}
#bhRoot .flowst-dot{transition:background .3s,border-color .3s,color .3s,transform .35s var(--mmx-spring),box-shadow .3s}
#bhRoot .flowst.cur .flowst-dot{transform:scale(1.1)}
#bhRoot .dvt{transition:background .25s,color .25s}

/* Middle panel: table rows slide in, cards rise, hover details */
#bhRoot .dtbl tbody tr{animation:mmxRowIn .4s var(--mmx-ease) both}
#bhRoot .dtbl tbody tr:nth-child(2){animation-delay:.03s}#bhRoot .dtbl tbody tr:nth-child(3){animation-delay:.06s}#bhRoot .dtbl tbody tr:nth-child(4){animation-delay:.09s}#bhRoot .dtbl tbody tr:nth-child(5){animation-delay:.12s}#bhRoot .dtbl tbody tr:nth-child(6){animation-delay:.15s}#bhRoot .dtbl tbody tr:nth-child(7){animation-delay:.18s}#bhRoot .dtbl tbody tr:nth-child(8){animation-delay:.21s}#bhRoot .dtbl tbody tr:nth-child(n+9){animation-delay:.24s}
#bhRoot .dtbl td{transition:background .2s}
#bhRoot .dtbl td button{transition:transform .2s var(--mmx-spring),box-shadow .2s}
#bhRoot .dtbl td button:hover{transform:scale(1.05);box-shadow:0 6px 14px -6px var(--ac)}
#bhRoot .bh-av{transition:transform .3s var(--mmx-spring)}
#bhRoot .bh-acct:hover .bh-av{transform:scale(1.12) rotate(-6deg)}
#bhRoot .stkrow{animation:mmxRise .45s var(--mmx-ease) both}
#bhRoot .stkrow:nth-child(2){animation-delay:.04s}#bhRoot .stkrow:nth-child(3){animation-delay:.08s}#bhRoot .stkrow:nth-child(4){animation-delay:.12s}#bhRoot .stkrow:nth-child(5){animation-delay:.16s}#bhRoot .stkrow:nth-child(n+6){animation-delay:.2s}
#bhRoot .mg-cell{animation:mmxRise .5s var(--mmx-ease) both;transition:transform .25s var(--mmx-ease),box-shadow .25s,background .15s,border-color .15s}
#bhRoot .mg-cell:nth-child(2){animation-delay:.05s}#bhRoot .mg-cell:nth-child(3){animation-delay:.1s}#bhRoot .mg-cell:nth-child(n+4){animation-delay:.15s}
#bhRoot .mg-cell:hover{transform:translateY(-3px);box-shadow:0 12px 26px -16px rgba(15,23,42,.35)}
#bhRoot .act{transition:transform .25s var(--mmx-ease),box-shadow .25s,border-color .2s}
#bhRoot .act:hover{transform:translateY(-2px);box-shadow:0 10px 22px -14px rgba(15,23,42,.3)}
#bhRoot .bh-cf-btn{transition:background .15s,border-color .15s,color .15s,transform .2s var(--mmx-spring)}
#bhRoot .bh-cf-btn:hover{transform:scale(1.1)}
.bh-cf-menu{animation:mmxPop .22s var(--mmx-spring)}

/* Applied-filter chips under each filter bar (Deal stage, Last active dates, ▾ column filters, sort) */
#bhRoot .mmx-chips{display:flex;flex-wrap:wrap;align-items:center;gap:6px;padding:8px 14px;border-bottom:1px solid #eef0f5;background:#fff}
#bhRoot .mmx-chips-l{font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#94a3b8;margin-right:2px}
#bhRoot .mmx-chip{display:inline-flex;align-items:center;gap:4px;background:var(--ac-l);color:var(--ac-d);border:1px solid var(--ac-m);border-radius:20px;padding:3px 4px 3px 10px;font-size:11.5px;font-weight:600;line-height:1.3;animation:mmxChipIn .35s var(--mmx-spring);transition:transform .2s var(--mmx-spring),box-shadow .2s}
#bhRoot .mmx-chip:hover{transform:translateY(-1px);box-shadow:0 4px 10px -6px var(--ac)}
#bhRoot .mmx-chip-e{border:none;background:none;color:inherit;font:inherit;padding:0;cursor:pointer}
#bhRoot .mmx-chip-e b{font-weight:700}
#bhRoot .mmx-chip-x{width:18px;height:18px;border:none;border-radius:50%;background:transparent;color:inherit;font-size:13px;line-height:1;cursor:pointer;display:grid;place-items:center;padding:0;transition:background .2s,transform .2s}
#bhRoot .mmx-chip-x:hover{background:var(--ac-m);transform:rotate(90deg)}
#bhRoot .mmx-chip.all{background:#fff;color:#64748b;border-color:#e6e9f0;padding-right:10px;cursor:pointer}
#bhRoot .mmx-chip.all:hover{color:#b91c1c;border-color:#fecaca;background:#fef2f2;box-shadow:none}

/* Mira helper: turning ring, pulsing Ready, questions slide in, messages pop, typing dots */
#bhRoot .dm-spark{position:relative}
#bhRoot .dm-spark svg{animation:mmxTwinkle 3s ease-in-out infinite}
#bhRoot .dm-spark::before{content:"";position:absolute;inset:-5px;border-radius:50%;background:conic-gradient(from 0deg,var(--ac),transparent 55%,var(--ac));-webkit-mask:radial-gradient(farthest-side,transparent calc(100% - 2px),#000 calc(100% - 1.5px));mask:radial-gradient(farthest-side,transparent calc(100% - 2px),#000 calc(100% - 1.5px));animation:mmxSpin 4s linear infinite;opacity:.8}
#bhRoot .dm-ready{display:inline-flex;align-items:center;gap:5px}
#bhRoot .dm-ready::before{content:"";width:6px;height:6px;border-radius:50%;background:#16a34a;animation:mmxPulse 2s infinite}
#bhRoot .dm-quick .qk{animation:mmxSlideL .45s var(--mmx-ease) both;transition:transform .25s var(--mmx-ease),box-shadow .25s,border-color .2s,background .2s,color .2s}
#bhRoot .dm-quick .qk:nth-child(2){animation-delay:.07s}#bhRoot .dm-quick .qk:nth-child(3){animation-delay:.14s}#bhRoot .dm-quick .qk:nth-child(4){animation-delay:.21s}#bhRoot .dm-quick .qk:nth-child(n+5){animation-delay:.28s}
#bhRoot .dm-quick .qk:hover{transform:translateX(-3px);box-shadow:0 6px 16px -10px var(--ac)}
#bhRoot .dm-chat .msg{animation:mmxBubIn .4s var(--mmx-spring) both}
#bhRoot .dm-chat:empty::after,#bhRoot .dm-chat:empty::before{animation:mmxBubIn .45s var(--mmx-spring) both}
#bhRoot .mmx-typing{display:inline-flex;gap:4px;padding:4px 2px}
#bhRoot .mmx-typing i{width:6px;height:6px;border-radius:50%;background:#94a3b8;animation:mmxBlink 1.2s infinite}
#bhRoot .mmx-typing i:nth-child(2){animation-delay:.15s}#bhRoot .mmx-typing i:nth-child(3){animation-delay:.3s}
#bhRoot .dm-inbar .in{transition:border-color .2s,box-shadow .2s}
#bhRoot .dm-inbar .in:focus{border-color:var(--ac);box-shadow:0 0 0 3px var(--ac-l)}
#bhRoot .dm-inbar .send{transition:transform .2s var(--mmx-spring)}
#bhRoot .dm-inbar .send:hover{transform:scale(1.08) rotate(-10deg)}
#bhRoot .mira-btn{transition:transform .2s var(--mmx-spring),background .2s}
#bhRoot .mira-btn:hover{transform:scale(1.1)}
#bhRoot.mmx-gridanim .dash{transition:grid-template-columns .4s var(--mmx-ease)}

/* Popups scale in; the list popup gets maximise / minimise */
.risk-modal-overlay.show,.bh-stu-overlay.show{animation:mmxFade .25s ease}
.risk-modal-overlay.show .risk-modal,.bh-stu-overlay.show .bh-stu-card{animation:mmxPop .35s var(--mmx-spring)}
.mmx-max-btn{display:inline-grid;place-items:center;width:28px;height:28px;margin-left:auto!important;border:1px solid #e5e7eb!important;border-radius:8px;background:#fff!important;color:#64748b!important;padding:0!important;cursor:pointer;flex-shrink:0;transition:background .15s,border-color .15s,color .15s,transform .2s}
.mmx-max-btn + button{margin-left:8px!important}
.mmx-max-btn:hover{background:#f5f3ff!important;border-color:#c4b5fd!important;color:#6d28d9!important;transform:scale(1.06)}
.mmx-max-btn svg{width:15px;height:15px}
.mmx-max-btn .mmx-i-min,.mmx-max .mmx-max-btn .mmx-i-max{display:none}
.mmx-max .mmx-max-btn .mmx-i-min{display:block}
.risk-modal{transition:max-width .3s var(--mmx-ease)}
.risk-modal.mmx-max{width:96vw;max-width:1500px;height:92vh;max-height:92vh;animation:mmxPop .3s var(--mmx-spring)}
.risk-modal.mmx-max .risk-modal-body{flex:1;min-height:0}
.mmx-max .risk-modal-hd{font-size:14.5px}
.mmx-max table{font-size:13px!important}
.mmx-max th,.mmx-max td{padding:9px 12px!important}
.risk-modal-hd{user-select:none}
.mmx-send-btn{display:inline-flex;align-items:center;gap:6px;margin-left:auto!important;height:28px;border:1px solid #7c3aed!important;border-radius:8px;background:#fff!important;color:#7c3aed!important;padding:0 11px!important;font:600 12px Inter,-apple-system,'Segoe UI',sans-serif!important;line-height:1!important;cursor:pointer;flex-shrink:0;white-space:nowrap;transition:background .15s,color .15s,transform .2s}
.mmx-send-btn:hover{background:#7c3aed!important;color:#fff!important;transform:translateY(-1px)}
.mmx-send-btn + .mmx-max-btn{margin-left:8px!important}

/* Sidebar (this page only): items slide in and nudge on hover, groups open smoothly, data-sources card */
#platformSidebar nav > *{animation:mmxSlideR .4s var(--mmx-ease) both}
#platformSidebar nav > :nth-child(2){animation-delay:.04s}#platformSidebar nav > :nth-child(3){animation-delay:.08s}#platformSidebar nav > :nth-child(4){animation-delay:.12s}#platformSidebar nav > :nth-child(5){animation-delay:.16s}#platformSidebar nav > :nth-child(6){animation-delay:.2s}#platformSidebar nav > :nth-child(7){animation-delay:.24s}#platformSidebar nav > :nth-child(n+8){animation-delay:.28s}
#platformSidebar nav a,#platformSidebar nav button,#platformSidebar a[href]{transition:background-color .2s,color .2s,transform .2s var(--mmx-ease)}
#platformSidebar nav a:hover,#platformSidebar nav button:hover,#platformSidebar > div > a[href]:hover{transform:translateX(2px)}
#platformSidebar a.border-l-2{animation:mmxSlideR .45s var(--mmx-ease) both}
#platformSidebar svg[id$="Chevron"]{transition:transform .3s var(--mmx-ease)}
#platformSidebar [id$="Submenu"]:not(.hidden) > *{animation:mmxSlideR .3s var(--mmx-ease) both}
#platformSidebar [id$="Submenu"]:not(.hidden) > :nth-child(2){animation-delay:.03s}#platformSidebar [id$="Submenu"]:not(.hidden) > :nth-child(3){animation-delay:.06s}#platformSidebar [id$="Submenu"]:not(.hidden) > :nth-child(4){animation-delay:.09s}#platformSidebar [id$="Submenu"]:not(.hidden) > :nth-child(n+5){animation-delay:.12s}
#platformSidebar [data-dd-menu]{animation:mmxPop .25s var(--mmx-spring)}
#platformSidebar .mmx-src-host > :not(.mmx-src){display:none}
#platformSidebar .mmx-src-host{background:transparent;border-top:none;padding:10px}
.mmx-src{background:#fafafb;border:1px solid #ececf2;border-radius:12px;padding:10px 10px 9px;animation:mmxRise .5s var(--mmx-ease) both .3s}
.mmx-src-t{font-size:11px;color:#64748b;line-height:1.35}
.mmx-src-t b{color:#0f172a;font-weight:700}
.mmx-src-bar{height:6px;border-radius:6px;background:#ececf2;margin:8px 0 7px;overflow:hidden}
.mmx-src-bar i{display:block;height:100%;border-radius:6px;background:linear-gradient(90deg,#16a34a,#34d399);transform-origin:left;animation:mmxGrowX 1s var(--mmx-ease) both .5s}
.mmx-src-s{font-size:10.5px;color:#94a3b8;line-height:1.4}
.mmx-src a{display:inline-block;margin-top:4px;font-size:10.5px;color:#0d9488}

/* Narrow windows (for example the browser snapped to half the screen): the page scrolls and the panels stack */
@media (max-width:1180px){
  .bh-page{overflow-y:auto!important;overflow-x:hidden!important}
  .bh-page > header{flex-wrap:wrap;gap:10px 12px;padding:14px 16px}
  .bh-page > header > div:first-child{flex:1 1 220px;min-width:0}
  .bh-page > header > div:last-child{flex-wrap:wrap;gap:8px}
  .bh-page > .flex-1{flex:none!important;overflow:visible!important;padding:16px!important}
  #bhRoot{height:auto!important;overflow:visible!important}
  #bhRoot .dash.on{display:flex!important;flex-direction:column;flex:none;overflow:visible;gap:12px}
  #bhRoot .dash-left{flex:none;max-height:none;overflow:visible}
  #bhRoot .dash-left .g-body{display:flex;flex-wrap:wrap;gap:2px 0}
  #bhRoot .dash-left .flowst{flex:1 1 170px;min-width:0}
  #bhRoot .dash-main{flex:none;overflow:hidden}
  #bhRoot .dash-view{flex:none;overflow-x:auto}
  #bhRoot .dash-mira{flex:none;height:560px}
  #bhRoot.bh-mira-min .dash-mira,#bhRoot.bh-left-min .dash-left{height:auto}
}
@media (max-width:760px){
  .bh-page > .flex-1{padding:12px!important}
  #bhRoot .atab{padding:0 8px;gap:6px}
  #bhRoot .atab .a2{white-space:normal;line-height:1.2;text-align:center}
}

@media (prefers-reduced-motion:reduce){
  #bhRoot *,#bhRoot *::before,#bhRoot *::after,#platformSidebar *,.bh-page > header *,.risk-modal-overlay *,.bh-stu-overlay *{animation-duration:.01ms!important;animation-delay:0s!important;animation-iteration-count:1!important;transition-duration:.01ms!important}
}
</style>
<script>
(function () {
    var REDUCE = !!(window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches);
    var EASE = 'cubic-bezier(.22,.8,.26,1)';
    var root = document.getElementById('bhRoot');
    if (!root) return;
    var esc = function (v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    function wrap(name, after, before) {
        var orig = window[name];
        if (typeof orig !== 'function') return;
        window[name] = function () {
            if (before) { try { before.apply(this, arguments); } catch (e) {} }
            var r = orig.apply(this, arguments);
            if (after) { try { after.apply(this, arguments); } catch (e) {} }
            return r;
        };
    }
    var READY = false;
    function slide(el, open, apply) {
        if (!el || REDUCE || !READY || !el.animate) { apply(); return; }
        if (open) {
            apply();
            var h = el.scrollHeight;
            if (!h) return;
            el.style.overflow = 'hidden';
            var a = el.animate([{ height: '0px', opacity: 0 }, { height: h + 'px', opacity: 1 }], { duration: 380, easing: EASE });
            a.onfinish = a.oncancel = function () { el.style.overflow = ''; };
        } else {
            var h2 = el.offsetHeight;
            if (!h2) { apply(); return; }
            el.style.overflow = 'hidden';
            var b = el.animate([{ height: h2 + 'px', opacity: 1 }, { height: '0px', opacity: 0 }], { duration: 300, easing: EASE, fill: 'forwards' });
            b.onfinish = function () { apply(); b.cancel(); el.style.overflow = ''; };
        }
    }

    /* Agent tabs: the coloured background slides to whichever tab is .on */
    var tabs = root.querySelector('.atabs');
    function atSlider() {
        if (!tabs) return;
        var s = tabs.querySelector('.mmx-at-slider'), fresh = !s;
        if (fresh) { s = document.createElement('span'); s.className = 'mmx-at-slider'; tabs.insertBefore(s, tabs.firstChild); tabs.classList.add('mmx-slid'); }
        var on = tabs.querySelector('.atab.on');
        if (!on) return;
        if (fresh) s.style.transition = 'none';
        s.style.width = on.offsetWidth + 'px';
        s.style.transform = 'translateX(' + on.offsetLeft + 'px)';
        if (fresh) { void s.offsetWidth; s.style.transition = ''; }
    }
    if (tabs) {
        new MutationObserver(function () { requestAnimationFrame(atSlider); }).observe(tabs, { subtree: true, attributes: true, attributeFilter: ['class'] });
        if (window.ResizeObserver) new ResizeObserver(atSlider).observe(tabs);
    }

    /* Steps slide in when the agent changes; the Mira panel and the steps panel open / close smoothly */
    wrap('setAgent', function () {
        var g = document.getElementById('dashGBody');
        if (!g || REDUCE) return;
        g.classList.remove('bhx-in'); void g.offsetWidth; g.classList.add('bhx-in');
        clearTimeout(g.__bhx); g.__bhx = setTimeout(function () { g.classList.remove('bhx-in'); }, 900);
    });
    function gridAnim() {
        if (REDUCE) return;
        root.classList.add('mmx-gridanim');
        clearTimeout(gridAnim.t);
        gridAnim.t = setTimeout(function () { root.classList.remove('mmx-gridanim'); }, 450);
    }
    wrap('bhMira', null, gridAnim);
    wrap('bhLeft', null, gridAnim);
    var origCQ = window.collapseDashQuicks, origEQ = window.expandDashQuicks;
    var quick = function () { return document.getElementById('dashQuick'); };
    var resizing = function () { return document.body.style.userSelect === 'none'; };
    if (origCQ) window.collapseDashQuicks = function () {
        var q = quick();
        if (!q || resizing() || q.classList.contains('dm-quick-collapsed')) return origCQ.apply(this, arguments);
        slide(q, false, origCQ);
    };
    if (origEQ) window.expandDashQuicks = function () {
        var q = quick();
        if (!q || resizing() || !q.classList.contains('dm-quick-collapsed')) return origEQ.apply(this, arguments);
        slide(q, true, origEQ);
    };

    /* Ask Mira: typing dots while it thinks */
    ['dashChat', 'bhChat'].forEach(function (id) {
        var chat = document.getElementById(id);
        if (!chat) return;
        new MutationObserver(function (muts) {
            muts.forEach(function (m) {
                m.addedNodes.forEach(function (n) {
                    if (n.nodeType === 1 && n.classList.contains('msg') && n.classList.contains('bot') && n.textContent.trim() === 'Thinking…') {
                        n.innerHTML = '<span class="mmx-typing" role="status" aria-label="Thinking…"><i></i><i></i><i></i></span>';
                    }
                });
            });
        }).observe(chat, { childList: true });
    });

    /* Applied-filter chips under each account list's filter bar (stage, dates, ▾ column filters, sort) */
    var api = function () { return window.BH_FILTER_API; };
    function fmtDate(v) { var p = String(v).split('-'); var d = new Date(+p[0], +p[1] - 1, +p[2]); return isNaN(d) ? v : d.toLocaleDateString('en-AU', { day: '2-digit', month: 'short', year: 'numeric' }); }
    function chipsFor(list) {
        var A = api();
        if (!A) return [];
        var out = [], f = A.lf[list] || {}, st = A.col[list] || { f: {}, sort: null }, defs = A.cols[list] || {};
        var bar = function () { return root.querySelector('.bh-lf[data-list="' + list + '"]'); };
        var setField = function (name) {
            return function () {
                var b = bar(), el = b && b.querySelector('[name="' + name + '"]');
                if (el) { el.value = ''; window.bhLfApply(b.querySelector('button[onclick^="bhLfApply"]')); }
            };
        };
        if (f.stage) out.push({ k: 'stage', label: 'Deal stage', value: f.stage, clear: setField('stage'), edit: function () { var b = bar(); if (b) b.querySelector('[name="stage"]').focus(); } });
        if (f.from) out.push({ k: 'from', label: 'Last active from', value: fmtDate(f.from), clear: setField('from'), edit: function () { var b = bar(); if (b) b.querySelector('[name="from"]').focus(); } });
        if (f.to) out.push({ k: 'to', label: 'Last active to', value: fmtDate(f.to), clear: setField('to'), edit: function () { var b = bar(); if (b) b.querySelector('[name="to"]').focus(); } });
        var openCol = function (k) {
            return function () {
                var btn = root.querySelector('.dash-view .bh-cf-btn[onclick*="\'' + list + '\',\'' + k + '\'"]');
                if (btn) btn.click();
            };
        };
        Object.keys(st.f).forEach(function (k) {
            if (!defs[k]) return;
            var lbl = A.label(defs[k], st.f[k]);
            out.push({ k: 'col' + k + lbl, label: defs[k].label, value: lbl.replace(defs[k].label, '').replace(/^[:\s]+/, ''), edit: openCol(k),
                clear: function () { delete st.f[k]; A.redraw(list); } });
        });
        if (st.sort && defs[st.sort.k]) out.push({ k: 'sort' + st.sort.k + st.sort.dir, label: 'Sorted', value: defs[st.sort.k].label + (st.sort.dir === 'desc' ? ' ↓' : ' ↑'),
            edit: openCol(st.sort.k), clear: function () { st.sort = null; A.redraw(list); } });
        return out;
    }
    var SEEN = {};
    function chipsRefresh() {
        root.querySelectorAll('.dash-view .bh-lf[data-list]').forEach(function (bar) {
            var list = bar.getAttribute('data-list'), items = chipsFor(list);
            var box = bar.nextElementSibling && bar.nextElementSibling.classList.contains('mmx-chips') ? bar.nextElementSibling : null;
            if (!items.length) { if (box) box.remove(); SEEN[list] = {}; return; }
            if (!box) { box = document.createElement('div'); box.className = 'mmx-chips'; box.setAttribute('aria-label', 'Applied filters'); bar.after(box); }
            var seen = SEEN[list] || {}, keys = {};
            box.innerHTML = '<span class="mmx-chips-l">Filters</span>';
            items.forEach(function (c) {
                keys[c.k] = 1;
                var el = document.createElement('span');
                el.className = 'mmx-chip';
                if (seen[c.k]) el.style.animation = 'none';
                el.innerHTML = '<button type="button" class="mmx-chip-e" title="Change this filter">' + esc(c.label) + ': <b>' + esc(c.value) + '</b></button>' +
                    '<button type="button" class="mmx-chip-x" title="Remove" aria-label="Remove ' + esc(c.label) + ' filter">×</button>';
                el.querySelector('.mmx-chip-e').onclick = function (e) { e.stopPropagation(); c.edit(); };
                el.querySelector('.mmx-chip-x').onclick = function () { c.clear(); };
                box.appendChild(el);
            });
            if (items.length > 1) {
                var all = document.createElement('button');
                all.type = 'button';
                all.className = 'mmx-chip all';
                all.textContent = 'Clear all';
                all.onclick = function () { window.bhLfReset(bar.querySelector('button[onclick^="bhLfReset"]')); };
                box.appendChild(all);
            }
            SEEN[list] = keys;
        });
    }
    var view = document.getElementById('dashView');
    if (view) new MutationObserver(function () { requestAnimationFrame(chipsRefresh); }).observe(view, { childList: true });

    /* The list popup ("… behind this answer"): column filters and maximise / minimise */
    var MAX_ICONS = '<svg class="mmx-i-max" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>' +
        '<svg class="mmx-i-min" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>';
    var ov = document.getElementById('riskModalOverlay'), box = ov && ov.querySelector('.risk-modal'), hd = box && box.querySelector('.risk-modal-hd');
    function setMax(on) {
        box.classList.toggle('mmx-max', on);
        var b = hd.querySelector('.mmx-max-btn');
        b.title = on ? 'Minimise' : 'Maximise';
        b.setAttribute('aria-label', b.title);
    }
    if (hd) {
        var mb = document.createElement('button');
        mb.type = 'button';
        mb.className = 'mmx-max-btn';
        mb.title = 'Maximise';
        mb.setAttribute('aria-label', 'Maximise');
        mb.innerHTML = MAX_ICONS;
        mb.addEventListener('click', function (e) { e.stopPropagation(); setMax(!box.classList.contains('mmx-max')); });
        hd.insertBefore(mb, hd.querySelector('button'));
        hd.addEventListener('dblclick', function (e) { if (!e.target.closest('button')) setMax(!box.classList.contains('mmx-max')); });
        new MutationObserver(function () { if (!ov.classList.contains('show') && box.classList.contains('mmx-max')) setMax(false); })
            .observe(ov, { attributes: true, attributeFilter: ['class'] }); // closed → normal size next time
    }
    var RISK = { id: null, tf: {} };
    wrap('openRiskModal', function (id) {
        RISK = { id: id, tf: {} };
        var table = document.querySelector('#riskModalBody table');
        if (table && window.MMXTF) MMXTF.attachDom(table, RISK.tf);
    }, function () { if (window.MMXTF) MMXTF.close(); });

    /* Part 10: Send to all in the list popup — emails the people in it who match its column filters,
       through the page's own Send to all window and email sender. */
    if (hd) {
        var sb = document.createElement('button');
        sb.type = 'button';
        sb.className = 'mmx-send-btn';
        sb.title = 'Email everyone in this list who matches the column filters';
        sb.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg><span>Send to all</span>';
        hd.insertBefore(sb, hd.querySelector('.mmx-max-btn'));
        sb.addEventListener('click', function (e) {
            e.stopPropagation();
            var A = api();
            if (!A || !A.riskRows || !window.XBulk) return;
            var all = A.riskRows(RISK.id), table = document.querySelector('#riskModalBody table');
            if (!table) return;
            var head = Array.prototype.find.call(table.querySelectorAll('tr'), function (tr) { return tr.querySelector('th'); });
            var trs = Array.prototype.filter.call(table.querySelectorAll('tr'), function (tr) { return tr !== head && !tr.classList.contains('mmx-tf-none'); });
            var rows = all.filter(function (r, i) { return trs[i] && !trs[i].classList.contains('mmx-tf-hide'); });
            if (!rows.length) { alert('Nobody matches the current filters in this list.'); return; }
            var filters = Object.keys(RISK.tf).filter(function (k) { return MMXTF.active(RISK.tf[k]); }).map(function (k) {
                var th = head && head.children[+k.slice(1)];
                var name = th ? ((th.childNodes[0] && th.childNodes[0].textContent) || th.textContent).trim() : k;
                return { label: name + ': ' + MMXTF.label(RISK.tf[k]), test: function () { return true; } }; // already applied in the popup
            });
            var title = (document.getElementById('riskModalTitle') || {}).textContent || 'this list';
            XBulk.open({
                title: 'Send to all — ' + title,
                reportTitle: 'Business Helpers — Send to all report (' + title + ')',
                fileBase: 'business-helpers-list',
                channels: ['email'],
                groupLabel: 'Stage',
                filters: filters,
                templates: A.templates,
                fill: function (text, r) { return A.fill(text, { name: r.raw.name, company: r.raw.company, deal_value: r.raw.deal_value != null ? r.raw.deal_value : r.raw.mrr }); },
                note: '{first}, {company} and {deal_value} are filled in for each contact.',
                send: A.bulkSend,
                load: function () {
                    return rows.map(function (a, i) {
                        var email = /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(String(a.email || '')) ? a.email : '';
                        return { id: String(i), name: a.name || a.company || '—', email: email, phone: null, group: a.stage || a.deal_status || a.situation || '—',
                                 date: a.days_since_activity != null ? Math.round(a.days_since_activity) + 'd silent' : (a.sent_at || ''), dateSub: a.company || '',
                                 search: Object.keys(a).map(function (k) { return a[k]; }).join(' '), raw: a };
                    });
                }
            });
            var xo = document.getElementById('xbOverlay');
            if (xo) document.body.appendChild(xo); // on top of the list popup
        });
    }

    /* Sidebar: data-sources card (count and company as before) and smooth closing of the groups */
    function sidebarDecor() {
        var sb = document.getElementById('platformSidebar');
        if (!sb) return;
        var host = Array.prototype.find.call(sb.children, function (c) { return /data sources connected/.test(c.textContent); });
        if (host && !host.querySelector('.mmx-src')) {
            var m = host.textContent.match(/(\d+)\s+of\s+(\d+)\s+data sources/);
            var n = m ? +m[1] : 0, tot = m ? +m[2] : 5;
            var comp = host.querySelector('p'), link = host.querySelector('a');
            var card = document.createElement('div');
            card.className = 'mmx-src';
            card.innerHTML = '<div class="mmx-src-t"><b>' + n + ' of ' + tot + ' data sources</b> connected</div>' +
                '<div class="mmx-src-bar"><i style="width:' + Math.round(n / (tot || 1) * 100) + '%"></i></div>' +
                '<div class="mmx-src-s">' + esc(comp ? comp.textContent.trim() : '') + '</div>' +
                (link ? '<a href="' + esc(link.getAttribute('href')) + '">' + esc(link.textContent.trim()) + '</a>' : '');
            host.classList.add('mmx-src-host');
            host.appendChild(card);
        }
        ['Email', 'Reports', 'Arch', 'Revenue', 'Mockmaster', 'Success', 'Alerts'].forEach(function (g) {
            var name = 'toggle' + g + 'Menu', orig = window[name];
            if (typeof orig !== 'function' || orig.__mmx) return;
            var id = g.charAt(0).toLowerCase() + g.slice(1) + 'Submenu';
            var fn = function () {
                var sub = document.getElementById(id);
                if (!sub || sub.classList.contains('hidden') || REDUCE) return orig.apply(this, arguments);
                var chev = document.getElementById(id.replace('Submenu', 'Chevron'));
                if (chev) chev.classList.remove('rotate-180');
                slide(sub, false, function () { orig(); });
            };
            fn.__mmx = true;
            window[name] = fn;
        });
    }

    function init() {
        atSlider();
        chipsRefresh();
        sidebarDecor();
        if (document.fonts && document.fonts.ready) document.fonts.ready.then(atSlider);
        setTimeout(function () { READY = true; }, 400);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
