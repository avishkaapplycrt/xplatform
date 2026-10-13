/* ============ Ask Mira — competitor comparison report (.cx-*) ============
   Shared by the homepage and Premium Mira; drawn by
   partials.ask-mira.compare-script. Reads top-down: score cards and verdict,
   Google's pipeline stages, section bars, the check-by-check fix list, then
   spotlights (Google / share previews, Core Web Vitals, AI crawlers,
   connected data). Colours are --cx-* variables whose fallbacks are the light
   theme — a page re-themes it by setting them on #miraComparePanel (Premium
   Mira does, for its dark theme). */
.mira-compare-panel{
  --cx-bg:#f2f2f3;--cx-surface:#fff;--cx-surface2:#f6f7fa;--cx-line:#eceef3;
  --cx-fg:#16181d;--cx-muted:#727786;--cx-accent:#4f80f0;--cx-accent-soft:#eef3ff;--cx-accent-line:#d6e2fd;--cx-on-accent:#fff;--cx-comp:#9096a6;
  --cx-good:#22a55b;--cx-good-soft:#e8f7ee;--cx-warn:#e3901f;--cx-warn-soft:#fdf4e6;--cx-bad:#e5574e;--cx-bad-soft:#fdeeec;
  --cx-track:#edeff4;--cx-serp-title:#1a0dab;--cx-shadow:0 1px 2px rgba(30,40,80,.04),0 6px 20px rgba(30,40,80,.05);
  background:var(--cx-bg)!important;color:var(--cx-fg);font-family:'Inter','Segoe UI',system-ui,sans-serif;font-size:15px;line-height:1.5;
  display:grid;gap:26px}
.mira-compare-panel[hidden]{display:none}
.mira-compare-panel *{box-sizing:border-box}
.mira-compare-panel h2,.mira-compare-panel h3{margin:0;letter-spacing:-.02em;font-weight:700;color:var(--cx-fg);text-wrap:balance}
.mira-compare-panel h2{font-size:1.25rem}
.mira-compare-panel button{font:inherit;color:inherit;cursor:pointer}
.mira-compare-panel button:focus-visible{outline:2px solid var(--cx-accent);outline-offset:2px}
.cx-muted{color:var(--cx-muted)}
.cx-eyebrow{font-size:.72rem;font-weight:600;letter-spacing:.09em;text-transform:uppercase;color:var(--cx-muted)}
.cx-num{font-variant-numeric:tabular-nums}
.cx-panel{background:var(--cx-surface);border-radius:24px;padding:24px;box-shadow:var(--cx-shadow);min-width:0}
.cx-head{display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:8px 16px}
.cx-head .cx-muted{font-size:.86rem}

/* score cards + verdict */
.cx-hero{display:grid;grid-template-columns:1fr auto 1fr;gap:16px;align-items:stretch}
.cx-site{display:flex;gap:18px;align-items:center}
.cx-site.you{background:var(--cx-accent-soft);box-shadow:0 0 0 1.5px var(--cx-accent-line) inset,var(--cx-shadow)}
.cx-site .cx-eyebrow{color:var(--cx-comp)}
.cx-site.you .cx-eyebrow{color:var(--cx-accent)}
.cx-site h3{font-size:1.1rem;margin-top:2px;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.cx-url{font-size:.86rem;color:var(--cx-muted);overflow-wrap:anywhere}
.cx-grade{font-size:.88rem;color:var(--cx-muted);margin-top:4px}
.cx-site-text{min-width:0}
.cx-ring{flex:none;position:relative;width:88px;height:88px}
.cx-ring svg{width:88px;height:88px;transform:rotate(-90deg)}
.cx-ring circle{transition:stroke-dashoffset .9s cubic-bezier(.16,1,.3,1)}
.cx-ring .cx-val{position:absolute;inset:0;display:grid;place-items:center;font-size:1.8rem;font-weight:700}
.cx-vs{align-self:center;font-weight:700;color:var(--cx-muted)}
.cx-verdict{display:flex;flex-wrap:wrap;gap:10px 24px;align-items:center;justify-content:space-between;background:var(--cx-surface);border-radius:99px;padding:16px 28px;box-shadow:var(--cx-shadow)}
.cx-verdict p{margin:0}
.cx-proj{font-size:.9rem;color:var(--cx-muted)}
.cx-proj b{color:var(--cx-good)}
.cx-cleaned{font-size:.76rem;color:var(--cx-muted)}

/* stages */
.cx-stages{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-top:12px}
.cx-stage{text-align:left;background:var(--cx-surface);border:0;border-radius:20px;padding:14px 16px;display:grid;gap:8px;align-content:start;box-shadow:var(--cx-shadow)}
.cx-stage:hover{box-shadow:0 0 0 1.5px var(--cx-accent-line) inset,var(--cx-shadow)}
.cx-stage[aria-pressed="true"]{background:var(--cx-accent-soft);box-shadow:0 0 0 2px var(--cx-accent) inset}
.cx-stage .cx-name{font-weight:600}
.cx-stage .cx-q{font-size:.78rem;color:var(--cx-muted);line-height:1.35;min-height:2.1em}
.cx-step{font-size:.7rem;color:var(--cx-muted);font-weight:500}
.cx-mini{display:grid;grid-template-columns:auto 1fr 2.2rem;gap:4px 6px;align-items:center;font-size:.74rem}
.cx-mini .cx-t{height:5px;background:var(--cx-track);border-radius:9px;overflow:hidden}
.cx-mini .cx-t i{display:block;height:100%;border-radius:9px}

/* section rows */
.cx-rows{display:grid;gap:4px;margin-top:14px}
.cx-row{display:grid;grid-template-columns:minmax(170px,1.2fr) 1fr 3.4rem 1fr 3.4rem;gap:6px 14px;align-items:center;padding:8px 12px;border-radius:14px;border:0;background:transparent;text-align:left;width:100%}
.cx-row:hover{background:var(--cx-surface2)}
.cx-row.head{padding-block:0;pointer-events:none}
.cx-row.head .you{color:var(--cx-accent)}
.cx-lbl{font-weight:500}
.cx-lbl small{display:block;font-size:.72rem;color:var(--cx-muted);font-weight:400}
.cx-track{height:9px;background:var(--cx-track);border-radius:9px;overflow:hidden}
.cx-track i{display:block;height:100%;border-radius:9px;transition:width .6s ease}
.cx-pct{text-align:right;font-weight:600;white-space:nowrap}
.cx-win{color:var(--cx-good)}

/* check-by-check list */
.cx-tabs{display:flex;flex-wrap:wrap;gap:6px;margin-top:14px}
.cx-tab{border:0;background:var(--cx-surface);border-radius:99px;padding:8px 16px;font-size:.88rem;font-weight:500;display:inline-flex;gap:8px;align-items:center;box-shadow:var(--cx-shadow)}
.cx-tab[aria-pressed="true"]{background:var(--cx-accent);color:var(--cx-on-accent)}
.cx-tab .cx-n{font-size:.75rem;padding:0 8px;border-radius:99px;background:var(--cx-surface2);color:var(--cx-muted)}
.cx-tab[aria-pressed="true"] .cx-n{background:rgba(255,255,255,.25);color:var(--cx-on-accent)}
.cx-tools{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:12px;min-height:4px}
.cx-chip{display:inline-flex;align-items:center;gap:6px;font-size:.8rem;background:var(--cx-accent-soft);color:var(--cx-accent)!important;border-radius:99px;padding:3px 10px;border:0}
.cx-list{display:grid;gap:10px;margin-top:12px}
.cx-issue{background:var(--cx-surface);border-radius:20px;overflow:hidden;box-shadow:var(--cx-shadow)}
.cx-issue.done{opacity:.6}
.cx-ihead{display:flex;gap:12px;align-items:flex-start;padding:18px 20px;width:100%;border:0;background:transparent;text-align:left}
.cx-ititle{flex:1;min-width:0;display:grid;gap:4px}
.cx-ititle b{font-size:1rem}
.cx-meta{display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:.76rem;color:var(--cx-muted)}
.cx-pill{font-size:.7rem;font-weight:600;padding:2px 8px;border-radius:99px;letter-spacing:.02em}
.cx-sev-critical{background:var(--cx-bad-soft);color:var(--cx-bad)}
.cx-sev-important{background:var(--cx-warn-soft);color:var(--cx-warn)}
.cx-sev-minor{background:var(--cx-surface2);color:var(--cx-muted)}
.cx-chev{flex:none;transition:transform .2s;color:var(--cx-muted);margin-top:2px}
.cx-issue.open .cx-chev{transform:rotate(180deg)}
.cx-ibody{padding:0 20px 20px;display:grid;gap:12px}
.cx-vals{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.cx-v{background:var(--cx-surface2);border-radius:14px;padding:10px 12px;display:flex;gap:10px;align-items:flex-start;min-width:0}
.cx-v .cx-eyebrow{font-size:.66rem}
.cx-v > div{min-width:0;overflow-wrap:anywhere;font-size:.92rem}
.cx-dot{flex:none;width:9px;height:9px;border-radius:50%;margin-top:6px;background:var(--cx-comp)}
.cx-s-pass{background:var(--cx-good)}.cx-s-warn{background:var(--cx-warn)}.cx-s-fail{background:var(--cx-bad)}
.cx-fix{margin:0;font-size:.95rem}
.cx-fix b{color:var(--cx-accent)}
.cx-more{display:grid;gap:10px;border-top:1px dashed var(--cx-line);padding-top:12px}
.cx-more[hidden]{display:none!important}
.cx-kv{display:grid;grid-template-columns:8.5rem 1fr;gap:6px 12px;font-size:.9rem;margin:0}
.cx-kv dt{color:var(--cx-muted)}.cx-kv dd{margin:0}
.cx-actions{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between}
.cx-mark{display:inline-flex;gap:8px;align-items:center;font-size:.88rem;font-weight:500;cursor:pointer}
.cx-mark input{width:17px;height:17px;accent-color:var(--cx-good)}
.cx-empty{padding:28px;text-align:center;color:var(--cx-muted);background:var(--cx-surface);border:1px dashed var(--cx-line);border-radius:20px}
.cx-btn{border:1px solid var(--cx-line);background:var(--cx-surface);border-radius:99px;padding:6px 14px;font-size:.8rem;font-weight:500;white-space:nowrap}
.cx-btn:not(:disabled):hover{border-color:var(--cx-accent);color:var(--cx-accent)}
.cx-btn:disabled{opacity:.55;cursor:default}

/* spotlights */
.cx-pair{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:14px}
.cx-side{font-size:.72rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:6px;color:var(--cx-comp)}
.cx-side.you{color:var(--cx-accent)}
.cx-serp{background:var(--cx-surface);border:1px solid var(--cx-line);border-radius:16px;padding:14px 16px;font-family:Arial,Helvetica,sans-serif;min-width:0}
.cx-serp-site{display:flex;gap:8px;align-items:center;font-size:.78rem;color:var(--cx-muted);min-width:0}
.cx-serp-site span:last-child{min-width:0;overflow-wrap:anywhere}
.cx-fav{flex:none;width:22px;height:22px;border-radius:50%;background:var(--cx-surface2);display:grid;place-items:center;font-size:.7rem;font-weight:700;color:var(--cx-fg)}
.cx-serp .cx-st{color:var(--cx-serp-title);font-size:1.1rem;margin:4px 0 2px;line-height:1.3}
.cx-serp .cx-sd{font-size:.86rem;color:var(--cx-muted);line-height:1.45}
.cx-serp .cx-sd.missing{font-style:italic}
.cx-flag{margin-top:8px;font-size:.78rem}
.cx-flag.bad{color:var(--cx-bad)}.cx-flag.good{color:var(--cx-good)}.cx-flag.warn{color:var(--cx-warn)}.cx-flag.info{color:var(--cx-muted)}
.cx-og{border:1px solid var(--cx-line);border-radius:16px;overflow:hidden;background:var(--cx-surface)}
.cx-og-img{aspect-ratio:1.91/1;display:grid;place-items:center;font-size:.82rem;text-align:center;padding:12px;overflow:hidden;position:relative}
.cx-og-img.none{background:repeating-linear-gradient(135deg,var(--cx-surface2) 0 10px,var(--cx-bg) 10px 20px);color:var(--cx-muted)}
.cx-og-img.has{padding:0;background:var(--cx-surface2)}
.cx-og-img img{width:100%;height:100%;object-fit:cover;display:block}
.cx-og-txt{padding:10px 12px;background:var(--cx-surface2);min-width:0}
.cx-og-txt small{font-size:.72rem;color:var(--cx-muted);text-transform:uppercase}
.cx-og-txt b{display:block;font-size:.95rem;overflow-wrap:anywhere}
.cx-og-txt span{font-size:.82rem;color:var(--cx-muted)}
.cx-cwv{display:grid;gap:18px;margin-top:14px}
.cx-topline{display:flex;flex-wrap:wrap;justify-content:space-between;gap:6px;margin-bottom:8px}
.cx-scale{position:relative;height:12px;border-radius:9px;display:flex}
.cx-scale .z1{background:var(--cx-good-soft);border-radius:9px 0 0 9px}
.cx-scale .z2{background:var(--cx-warn-soft)}
.cx-scale .z3{background:var(--cx-bad-soft);border-radius:0 9px 9px 0;flex:1}
.cx-marker{position:absolute;top:-5px;width:4px;height:22px;border-radius:3px;transform:translateX(-50%);background:var(--cx-comp)}
.cx-marker.you{background:var(--cx-accent)}
.cx-ticks{display:flex;justify-content:space-between;font-size:.72rem;color:var(--cx-muted);margin-top:6px}
.cx-legend{display:flex;gap:14px;font-size:.8rem;color:var(--cx-muted);flex-wrap:wrap;margin-top:10px}
.cx-legend i{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:5px;vertical-align:-1px}
.cx-tblwrap{overflow-x:auto;margin-top:12px}
.mira-compare-panel table{width:100%;border-collapse:collapse;font-size:.9rem}
.mira-compare-panel th,.mira-compare-panel td{text-align:left;padding:9px 10px;border-bottom:1px solid var(--cx-line);vertical-align:top}
.mira-compare-panel th{font-size:.72rem;letter-spacing:.07em;text-transform:uppercase;color:var(--cx-muted);font-weight:600}
.cx-tag{font-size:.78rem;font-weight:600;padding:2px 9px;border-radius:99px;white-space:nowrap}
.cx-tag.ok{background:var(--cx-good-soft);color:var(--cx-good)}.cx-tag.no{background:var(--cx-bad-soft);color:var(--cx-bad)}
.cx-note{font-size:.84rem;color:var(--cx-muted);margin:10px 0 0}

/* Locked check list (free homepage): blurred, fading preview + centred upgrade card. */
.cx-locked{position:relative;min-height:420px}
.cx-locked-preview{filter:blur(6px);user-select:none;pointer-events:none;max-height:560px;overflow:hidden;transition:filter .5s ease;
  -webkit-mask-image:linear-gradient(#000 45%,transparent 100%);mask-image:linear-gradient(#000 45%,transparent 100%)}
.cx-lock{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:20px;transition:opacity .35s ease}
.cx-lock-card{max-width:440px;text-align:center;background:rgba(255,255,255,.95);border:1px solid #ecd38a;border-radius:22px;padding:26px 28px;box-shadow:0 20px 50px rgba(23,24,26,.18)}
.cx-lock-icon{width:46px;height:46px;margin:0 auto 12px;border-radius:50%;display:grid;place-items:center;font-size:20px;background:linear-gradient(135deg,#fffaf0,#fdf1d4);border:1px solid #ecd38a}
.cx-lock-card h5{font-size:18px;font-weight:700;color:#17181a;margin:0 0 6px}
.cx-lock-card p{font-size:14px;line-height:1.55;color:#4a4a50;margin:0 0 18px}
.cx-lock-btn{display:inline-flex;align-items:center;gap:6px;padding:12px 26px;border-radius:999px;text-decoration:none;border:none;
  background:linear-gradient(135deg,#f0d27a,#d4af37 55%,#b8902f);color:#1a1406!important;font-weight:700;font-size:15px;
  box-shadow:0 6px 18px rgba(212,175,55,.4);transition:transform .15s,box-shadow .2s}
.cx-lock-btn:hover{transform:translateY(-1px);box-shadow:0 8px 22px rgba(212,175,55,.5)}
.cx-locked.unlocked{min-height:0}
.cx-locked.unlocked .cx-locked-preview{filter:none;max-height:none;-webkit-mask-image:none;mask-image:none;pointer-events:auto;user-select:auto}
.cx-locked.unlocked .cx-lock{opacity:0;pointer-events:none}

@media (max-width:760px){
  .cx-hero{grid-template-columns:1fr}.cx-vs{justify-self:center}
  .cx-verdict{border-radius:22px;padding:16px 20px}
  .cx-row{grid-template-columns:1fr 3.2rem;gap:4px 10px}
  .cx-row .cx-lbl{grid-column:1/-1}
  .cx-row.head{display:none}
  .cx-pair,.cx-vals{grid-template-columns:1fr}
  .cx-kv{grid-template-columns:1fr}
  .cx-panel{padding:18px}
}
@media (prefers-reduced-motion:reduce){.mira-compare-panel *{transition:none!important}}
/* One site ("Analyse my website"): no competitor columns. */
.cx-single .cx-row{grid-template-columns:minmax(170px,1.2fr) 2fr 3.4rem}
.cx-single .cx-vals{grid-template-columns:1fr}
.cx-single .cx-hero{grid-template-columns:1fr}
@media (max-width:760px){.cx-single .cx-row{grid-template-columns:1fr 3.2rem}}
