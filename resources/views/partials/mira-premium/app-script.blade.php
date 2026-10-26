{{-- Mira Premium workspace script (mira-premium.blade.php).
     Structure, top to bottom:
       format    memoised Intl number / date formatting
       telemetry first-party, cookieless events (sendBeacon, text/plain) + error reports
       api       typed endpoint registry: method + path + required response fields;
                 tagged errors (ApiError.code = the server's MiraApiException code)
       state     one store; async values are RemoteData (NotAsked/Loading/Success/Failure)
       render    one function per region; all page text set with textContent
       flows     scan (two-phase + polling), projects, AI visibility, chat
     Shared helpers from partials.ask-mira.*: renderBotMessage, miraRenderSiteReport,
     miraRenderComparison. --}}
(function () {
'use strict';

const BOOT        = JSON.parse(document.getElementById('miraBoot').textContent);
const API_BASE    = @json(url('/mira-premium/api'));
const CHAT_SEND   = @json(route('mira-premium.send'));
const CHAT_RESET  = @json(route('mira-premium.reset'));
const LOGIN_URL   = @json(route('client.login'));
const STORE_KEY   = 'miraPremium:project';
const $ = (id) => document.getElementById(id);

// =================================================================== format
const nfCache = new Map();
function nf(opts) {
  const key = JSON.stringify(opts || {});
  if (!nfCache.has(key)) nfCache.set(key, new Intl.NumberFormat(undefined, opts));
  return nfCache.get(key);
}
const rtf = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });
const dtf = new Intl.DateTimeFormat(undefined, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
const fmt = {
  num: (v) => v === null || v === undefined ? '—' : nf().format(v),
  pct: (v) => v === null || v === undefined ? '—' : nf({ maximumFractionDigits: 1 }).format(v) + '%',
  date: (iso) => iso ? dtf.format(new Date(iso)) : '—',
  ago: (iso) => {
    if (!iso) return '—';
    const s = Math.round((new Date(iso) - Date.now()) / 1000), a = Math.abs(s);
    if (a < 60) return rtf.format(Math.round(s), 'second');
    if (a < 3600) return rtf.format(Math.round(s / 60), 'minute');
    if (a < 86400) return rtf.format(Math.round(s / 3600), 'hour');
    return rtf.format(Math.round(s / 86400), 'day');
  },
  host: (url) => String(url || '').replace(/^https?:\/\/(www\.)?/i, '').replace(/\/$/, ''),
};
const tone = (s) => s === null || s === undefined ? 'none' : s >= 80 ? 'good' : s >= 50 ? 'warn' : 'bad';

// ================================================================ telemetry
// Cookieless and tiny: {n: name, u: url, r: referrer, p: props}. Automation is skipped
// so headless browsers don't skew the numbers.
const AUTOMATED = !!(navigator.webdriver || window._phantom || window.__nightmare || window.Cypress);
function beacon(path, payload) {
  try {
    const body = JSON.stringify(payload);
    if (navigator.sendBeacon && navigator.sendBeacon(API_BASE + path, new Blob([body], { type: 'text/plain' }))) return;
    fetch(API_BASE + path, { method: 'POST', body, keepalive: true, credentials: 'same-origin', headers: { 'Content-Type': 'text/plain' } }).catch(() => {});
  } catch (e) { /* telemetry never breaks the page */ }
}
function track(name, props) {
  if (AUTOMATED) return;
  beacon('/event', { n: name, u: location.href, r: document.referrer || undefined, p: props || undefined });
}
function reportError(err, where) {
  beacon('/error', { message: (where ? where + ': ' : '') + (err && err.message || String(err)), stack: err && err.stack || '', url: location.href });
}
// Browser extensions inject scripts near the top of the page (e.g. structured-data readers that
// choke on the miraBoot JSON block); Mira's own code runs at the end of <body>, so ignore those.
window.addEventListener('error', (e) => { if (e.lineno && e.lineno < 40) return; reportError(e.error || e.message, 'window'); });
window.addEventListener('unhandledrejection', (e) => reportError(e.reason, 'promise'));
// Declarative events: class="mira-ev-<name>" + data-ev-* props, no code needed per button.
document.addEventListener('click', (e) => {
  const el = e.target.closest('[class*="mira-ev-"]');
  if (!el) return;
  const m = el.className.match(/mira-ev-([a-z_]+)/);
  if (!m) return;
  const props = {};
  for (const [k, v] of Object.entries(el.dataset)) if (k.startsWith('ev')) props[k.slice(2).toLowerCase()] = v;
  track(m[1], props);
});

// ====================================================================== api
class ApiError extends Error {
  constructor(code, message, meta) { super(message); this.code = code; this.meta = meta || {}; }
}
// Every call the page makes, declared once. Handles and other sensitive values travel in
// bodies, never in URLs; `out` lists the fields a valid response must carry.
const ENDPOINTS = {
  me:             { m: 'GET',    p: () => '/me',                                out: ['subscription', 'usage', 'caps', 'projects'] },
  submitScan:     { m: 'POST',   p: () => '/scans',                             out: ['handle', 'valid_until', 'scan'] },
  scanReport:     { m: 'POST',   p: () => '/scans/report',                      out: ['scan'] },
  showScan:       { m: 'GET',    p: (a) => '/scans/' + a.id,                    out: ['scan'] },
  shareScan:      { m: 'POST',   p: (a) => '/scans/' + a.id + '/share',         out: ['url', 'pdf_url', 'valid_until'] },
  createProject:  { m: 'POST',   p: () => '/projects',                          out: ['project'] },
  updateProject:  { m: 'PATCH',  p: (a) => '/projects/' + a.id,                 out: ['project'] },
  deleteProject:  { m: 'DELETE', p: (a) => '/projects/' + a.id,                 out: [] },
  history:        { m: 'GET',    p: (a) => '/projects/' + a.id + '/history',    out: ['scans', 'timeline'] },
  messages:       { m: 'GET',    p: (a) => '/projects/' + a.id + '/messages',   out: ['messages'] },
  visibility:     { m: 'GET',    p: (a) => '/projects/' + a.id + '/visibility', out: ['prompts', 'report', 'caps'] },
  addPrompt:      { m: 'POST',   p: (a) => '/projects/' + a.id + '/prompts',    out: ['prompt'] },
  suggestPrompts: { m: 'POST',   p: (a) => '/projects/' + a.id + '/prompts/suggest', out: ['added'] },
  updatePrompt:   { m: 'PATCH',  p: (a) => '/prompts/' + a.id,                  out: ['prompt'] },
  deletePrompt:   { m: 'DELETE', p: (a) => '/prompts/' + a.id,                  out: [] },
  startRun:       { m: 'POST',   p: (a) => '/projects/' + a.id + '/visibility/runs', out: ['run'] },
  panel:          { m: 'GET',    p: (a) => '/projects/' + a.id + '/panels/' + a.panel + '?period=' + encodeURIComponent(a.period) + (a.refresh ? '&refresh=1' : ''), out: ['panel', 'period', 'data'] },
  ranks:          { m: 'GET',    p: (a) => '/projects/' + a.id + '/ranks',                 out: ['ranks'] },
  rankAdd:        { m: 'POST',   p: (a) => '/projects/' + a.id + '/ranks',                 out: ['added', 'ranks'] },
  rankCheck:      { m: 'POST',   p: (a) => '/projects/' + a.id + '/ranks/check',           out: ['checked', 'ranks'] },
  rankDelete:     { m: 'DELETE', p: (a) => '/projects/' + a.id + '/ranks/' + a.kw,       out: ['ranks'] },
  googleStatus:   { m: 'GET',    p: (a) => '/projects/' + a.id + '/google',     out: ['google'] },
  googleSelect:   { m: 'PATCH',  p: (a) => '/projects/' + a.id + '/google',     out: ['google'] },
  googleDisconnect: { m: 'POST', p: () => '/google/disconnect',                 out: ['google'] },
};
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
async function refreshCsrf() {
  const html = await (await fetch(location.pathname, { credentials: 'same-origin', cache: 'no-store' })).text();
  const m = html.match(/<meta name="csrf-token" content="([^"]+)"/);
  if (m) document.querySelector('meta[name="csrf-token"]').content = m[1];
}
// Tagged error from any JSON response: the workspace's {error:{code,...}} or Laravel's {message}.
function errorFrom(status, data) {
  const e = data && data.error;
  if (e && typeof e === 'object' && e.code) return new ApiError(e.code, e.message, e);
  if (typeof e === 'string' && e) return new ApiError(status === 422 ? 'InvalidInput' : 'Unavailable', e);
  if (status === 422) return new ApiError('InvalidInput', (data && data.message) || 'Please check what you entered.');
  if (status === 429) return new ApiError('QuotaExceeded', (data && data.message) || 'Too many requests — wait a moment and try again.');
  return new ApiError('Unavailable', (data && (data.error || data.message)) || 'Something went wrong (' + status + '). Please try again.');
}
async function request(url, method, body, retried) {
  const opts = { method, credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };
  if (method !== 'GET') {
    opts.headers['Content-Type'] = 'application/json; charset=utf-8';
    opts.headers['X-CSRF-TOKEN'] = csrf();
    opts.body = JSON.stringify(body || {});
  }
  let res;
  try { res = await fetch(url, opts); } catch (e) { throw new ApiError('Network', 'Could not reach Mira. Check your connection and try again.'); }
  // The session's CSRF token went stale (expired / logged in elsewhere): refresh once and retry.
  if (res.status === 419 && !retried) { await refreshCsrf(); return request(url, method, body, true); }
  let data = null;
  if (res.status !== 204) { try { data = await res.json(); } catch (e) { data = null; } }
  if (!res.ok) throw errorFrom(res.status, data);
  return data;
}
async function api(name, args, body) {
  const ep = ENDPOINTS[name];
  const data = await request(API_BASE + ep.p(args || {}), ep.m, body);
  for (const field of ep.out) {
    if (!data || !(field in data)) {
      const err = new ApiError('Decode', 'Unexpected response from Mira (' + name + ': missing "' + field + '").');
      reportError(err, 'decode');
      throw err;
    }
  }
  return data;
}

// ==================================================================== state
const RD = {
  notAsked: { tag: 'NotAsked' },
  loading: (meta) => ({ tag: 'Loading', meta: meta || {} }),
  success: (data) => ({ tag: 'Success', data }),
  failure: (error) => ({ tag: 'Failure', error }),
};
const S = {
  sub: BOOT.subscription, usage: BOOT.usage, caps: BOOT.caps, projects: BOOT.projects || [],
  recent: BOOT.recent_scans || [], turnstileKey: BOOT.turnstile_site_key,
  projectId: null, tab: 'overview', compare: false,
  scan: RD.notAsked,          // {scan, report}
  history: RD.notAsked,       // {scans, timeline}
  vis: RD.notAsked,           // {prompts, report, caps}
  ranks: RD.notAsked,         // rank tracking report (pid = project it belongs to)
  renderedReport: null, scanPoll: null, visPoll: null, turnstileToken: null,
  period: '30d', periodInfo: null, trafficMetric: 'visits',
  reports: {}, perfMetric: 'organic_traffic', perfYoy: false,
  panels: { ai: RD.notAsked, authority: RD.notAsked, search: RD.notAsked, traffic: RD.notAsked, gsc: RD.notAsked },
  chat: { busy: false, siteLoaded: false },
};
const premium = () => S.sub.premium;
const project = () => S.projects.find((p) => p.id === S.projectId) || null;
const usageOf = (metric) => S.usage.find((u) => u.metric === metric) || { used: 0, limit: 0 };

// ================================================================== helpers
// h('div', {class, text, on: {click}}, ...children) — text only via textContent.
const SVG_TAGS = new Set(['svg', 'path', 'polyline', 'circle', 'line', 'rect', 'text']);
function h(tag, attrs, ...children) {
  const el = SVG_TAGS.has(tag) ? document.createElementNS('http://www.w3.org/2000/svg', tag) : document.createElement(tag);
  for (const [k, v] of Object.entries(attrs || {})) {
    if (v === null || v === undefined || v === false) continue;
    if (k === 'text') el.textContent = v;
    else if (k === 'on') for (const [ev, fn] of Object.entries(v)) el.addEventListener(ev, fn);
    else if (k === 'style' && typeof v === 'object') Object.assign(el.style, v);
    else el.setAttribute(k === 'cls' ? 'class' : k, v === true ? '' : v);
  }
  for (const c of children.flat()) {
    if (c === null || c === undefined || c === false) continue;
    el.appendChild(typeof c === 'object' ? c : document.createTextNode(String(c)));
  }
  return el;
}
const icon = (d) => { const s = h('svg', { viewBox: '0 0 24 24' }); d.split('|').forEach((p) => s.appendChild(h('path', { d: p }))); return s; };
const ICONS = {
  lock: 'M7 11V8a5 5 0 0110 0v3|M5 11h14v10H5z', check: 'M5 12l5 5L20 7', spark: 'M12 3l1.8 4.7 4.7 1.8-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8z',
  scan: 'M3 7V4h3|M21 7V4h-3|M3 17v3h3|M21 17v3h-3|M7 12h10', chart: 'M4 20V10|M10 20V4|M16 20v-7|M22 20H2', info: 'M12 8h.01|M11 12h1v5h1',
  plus: 'M12 5v14|M5 12h14', trash: 'M4 7h16|M9 7V4h6v3|M6 7l1 13h10l1-13', refresh: 'M20 12a8 8 0 10-2.3 5.7|M20 4v6h-6', alert: 'M12 9v4|M12 17h.01|M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z',
};
// Change vs the previous value: the arrow shows direction, the colour shows whether
// that's good (for "lower is better" metrics such as open issues, pass invert).
function deltaEl(v, { invert = false, suffix = '' } = {}) {
  if (v === null || v === undefined) return null;
  const good = invert ? v < 0 : v > 0;
  const el = h('span', { cls: 'delta ' + (v === 0 ? 'flat' : good ? 'up' : 'down'),
    text: v === 0 ? '±0' + suffix : (v > 0 ? '\u25B2 ' : '\u25BC ') + Math.abs(v) + suffix });
  el.title = (v > 0 ? '+' : '') + v + suffix + ' since the previous result';
  return el;
}
function sparkline(values, w = 96, hgt = 34) {
  const pts = values.filter((v) => v !== null && v !== undefined);
  const svg = h('svg', { viewBox: `0 0 ${w} ${hgt}`, cls: 'spark', 'aria-hidden': 'true' });
  if (pts.length < 2) return svg;
  const min = Math.min(...pts), max = Math.max(...pts), span = max - min || 1;
  const xy = pts.map((v, i) => [i / (pts.length - 1) * (w - 4) + 2, hgt - 4 - (v - min) / span * (hgt - 8)]);
  svg.appendChild(h('polyline', { points: xy.map((p) => p.join(',')).join(' '), fill: 'none', stroke: '#141417', 'stroke-width': '1.6', 'stroke-linejoin': 'round', 'stroke-linecap': 'round' }));
  const last = xy[xy.length - 1];
  svg.appendChild(h('circle', { cx: last[0], cy: last[1], r: '2.8', fill: '#c9a13a' }));
  return svg;
}
function lineChart(points, key, { h: hgt = 160, max = 100 } = {}) {
  const w = 640, pad = 28;
  const vals = points.map((p) => p[key]);
  const svg = h('svg', { viewBox: `0 0 ${w} ${hgt}`, style: { width: '100%', height: hgt + 'px' }, role: 'img', 'aria-label': 'Trend over time' });
  [0, 50, 100].forEach((g) => {
    const y = hgt - pad - g / max * (hgt - pad * 1.6);
    svg.appendChild(h('line', { x1: pad, x2: w - 8, y1: y, y2: y, stroke: '#ece9e2', 'stroke-width': '1' }));
    const t = h('text', { x: 4, y: y + 4, 'font-size': '10', fill: '#a2a0a7' }); t.textContent = g; svg.appendChild(t);
  });
  if (vals.filter((v) => v !== null).length >= 1) {
    const xy = vals.map((v, i) => v === null ? null : [pad + (vals.length === 1 ? (w - pad) / 2 : i / (vals.length - 1) * (w - pad - 12)), hgt - pad - v / max * (hgt - pad * 1.6)]).filter(Boolean);
    if (xy.length > 1) svg.appendChild(h('polyline', { points: xy.map((p) => p.join(',')).join(' '), fill: 'none', stroke: '#141417', 'stroke-width': '2', 'stroke-linejoin': 'round' }));
    xy.forEach((p, i) => svg.appendChild(h('circle', { cx: p[0], cy: p[1], r: i === xy.length - 1 ? 4 : 2.6, fill: i === xy.length - 1 ? '#c9a13a' : '#141417' })));
  }
  return svg;
}
function toast(message, { error = false, action = null } = {}) {
  const el = h('div', { cls: 'toast' + (error ? ' err' : '') }, h('span', { text: message }), action);
  $('toasts').appendChild(el);
  setTimeout(() => el.remove(), 7000);
}
// No upgrade URL (config mira_premium.upgrade_url unset, for now): no button.
function upgradeLink(from, label) {
  if (!S.sub.upgrade_url) return null;
  return h('a', { cls: 'btn btn-premium btn-sm mira-ev-upgrade_click', 'data-ev-from': from, href: S.sub.upgrade_url, text: label || 'Upgrade' });
}
// One place turns a tagged error into words + the right next step.
function explain(err) {
  switch (err.code) {
    case 'QuotaExceeded': return { msg: err.message, action: upgradeLink('quota', premium() ? 'Upgrade plan' : 'See Premium') };
    case 'NotEntitled':   return { msg: err.message, action: S.sub.signed_in ? upgradeLink('locked', 'Unlock Premium') : h('a', { cls: 'btn btn-secondary btn-sm', href: LOGIN_URL, text: 'Sign in' }) };
    case 'LimitReached':  return { msg: err.message, action: upgradeLink('limit', 'Upgrade') };
    case 'Expired':       return { msg: err.message, action: null };
    case 'InvalidCaptcha':return { msg: err.message, action: null };
    default:              return { msg: err.message || 'Something went wrong.', action: null };
  }
}
function setBusy(btn, busy) { btn.disabled = busy; btn.classList.toggle('loading', busy); }

// ==================================================================== render
function renderTopbar() {
  // The plan badge and Sign in were removed from the top bar; only fill them if present.
  const badge = $('planBadge');
  if (!badge) { /* no plan badge */ } else if (premium()) {
    badge.className = 'badge badge-plan';
    badge.textContent = S.sub.status === 'active_trial' && S.sub.trial_days_left !== null
      ? `${S.sub.label} · ${S.sub.trial_days_left} day${S.sub.trial_days_left === 1 ? '' : 's'} left`
      : S.sub.label + (S.sub.dev_unlock ? ' · dev unlock' : '');
  } else {
    badge.className = 'badge badge-preview';
    badge.textContent = S.sub.status === 'expired' ? 'Plan expired · Preview' : 'Preview';
  }
  if ($('signInBtn')) $('signInBtn').hidden = S.sub.signed_in;
  const manage = $('manageBtn');
  manage.hidden = !S.sub.management_url;
  if (S.sub.management_url) manage.href = S.sub.management_url;
  $('upgradeBtn').hidden = !S.sub.upgrade_url || (premium() && S.sub.status === 'active' && S.sub.tier === 'enterprise');
  $('upgradeBtn').lastChild.textContent = premium() && S.sub.status === 'active' ? ' Upgrade plan' : (S.sub.status === 'active_trial' ? ' Subscribe' : ' Upgrade');

  const scans = usageOf('scans');
  const pill = $('usagePill');
  pill.hidden = !scans.limit;
  if (scans.limit) {
    const left = Math.max(0, scans.limit - scans.used);
    $('usagePillText').textContent = `${fmt.num(left)} scans left`;
    $('usagePillBar').style.width = Math.min(100, scans.used / scans.limit * 100) + '%';
    pill.classList.toggle('low', left / scans.limit < .15);
  }
  document.querySelectorAll('[data-lock]').forEach((el) => { el.hidden = premium(); });
  renderProjectSwitch();
  renderScanQuota();
}

function renderProjectSwitch() {
  const wrap = $('projectSwitch');
  wrap.hidden = !(premium() && S.sub.signed_in);
  if (wrap.hidden) return;
  const p = project();
  $('projectName').textContent = p ? p.name : (S.projects.length ? 'Choose project' : 'No projects yet');
  $('projectFav').textContent = p ? initials(p.name) : '+';
  const menu = $('projectMenu');
  menu.innerHTML = '';
  S.projects.forEach((pr) => menu.appendChild(h('button', {
    cls: 'menu-item', role: 'menuitem', type: 'button', 'aria-current': pr.id === S.projectId ? 'true' : 'false',
    on: { click: () => { closeMenu(); selectProject(pr.id); } },
  }, h('span', { cls: 'project-fav', text: initials(pr.name) }),
     h('span', null, pr.name, h('small', { text: pr.domain + (pr.latest_scan ? ' · score ' + pr.latest_scan.overall : '') })))));
  if (S.projects.length) menu.appendChild(h('div', { cls: 'menu-sep' }));
  const cap = S.caps.projects;
  menu.appendChild(h('button', { cls: 'menu-item', type: 'button', on: { click: () => { closeMenu(); newProjectFlow(); } } },
    h('span', { cls: 'project-fav', style: { background: 'var(--gold-soft)', color: '#7a5d14' }, text: '+' }),
    h('span', null, 'New project', h('small', { text: `${S.projects.length} of ${cap} on your plan` }))));
}
function initials(name) {
  const w = String(name || '?').replace(/[^\p{L}\p{N}\s]/gu, ' ').trim().split(/\s+/).filter(Boolean);
  return (w.length > 1 ? w[0][0] + w[1][0] : (w[0] || '?').slice(0, 2)).toUpperCase();
}
function closeMenu() { $('projectMenu').hidden = true; $('projectBtn').setAttribute('aria-expanded', 'false'); }

function renderScanQuota() {
  const u = usageOf('scans');
  const left = Math.max(0, u.limit - u.used);
  $('scanQuota').textContent = u.limit
    ? `${fmt.num(left)} of ${fmt.num(u.limit)} ${premium() ? '' : 'preview '}scans left ${u.period === 'day' ? 'today' : 'this month'} · a comparison uses 2`
    : '';
}

function setTab(tab, silent) {
  S.tab = tab;
  document.querySelectorAll('.tab').forEach((t) => t.setAttribute('aria-selected', String(t.dataset.tab === tab)));
  document.querySelectorAll('[role="tabpanel"]').forEach((p) => { p.hidden = p.id !== 'tab-' + tab; });
  if (!silent) track('tab_open', { tab });
  if (tab === 'audit') renderAudit();
  if (tab === 'visibility') { loadVisibility(); }
  if (tab === 'organic') { renderOrganic(); ['insights', 'difficulty', 'countries', 'pages', 'competitors', 'paid', 'trends'].forEach((r) => loadReport(r)); }
  if (tab === 'backlinks') { renderBacklinks(); loadReport('backlinks'); loadReport('links'); }
  if (tab === 'history') { loadHistory(); }
  if (tab === 'ranks') { loadRanks(); }
  if (tab === 'chat') $('chatInput').focus();
  try { history.replaceState(null, '', '#' + tab); } catch (e) {}
}

// ---------------------------------------------------------------- overview
function renderOverview() {
  const kpis = $('kpis');
  kpis.innerHTML = '';
  const sc = S.scan.tag === 'Success' ? S.scan.data.scan : null;
  const report = S.scan.tag === 'Success' ? S.scan.data.report : null;
  const loading = S.scan.tag === 'Loading';
  const timeline = S.history.tag === 'Success' ? S.history.data.timeline : [];

  // 1 · Site health
  kpis.appendChild(h('div', { cls: 'card kpi' },
    h('div', { cls: 'kpi-lbl' }, 'Site health', h('span', { cls: 'i', title: 'Weighted average of 13 check categories, 0–100', text: 'i' })),
    loading ? h('div', { cls: 'kpi-val' }, h('span', { cls: 'skeleton', style: { width: '96px', height: '40px', display: 'block' } }))
      : h('div', { cls: 'kpi-val' }, h('span', { cls: 'big num ' + (sc ? 'c-' + tone(sc.overall) : ''), text: sc ? sc.overall : '—' }), sc ? h('span', { cls: 'unit', text: '/100' }) : null, sc ? deltaEl(sc.delta.overall) : null),
    h('div', { cls: 'kpi-sub' },
      loading ? h('span', { text: 'Scanning ' + fmt.host(S.scan.meta.url) + '…' })
        : sc ? [h('span', { cls: 'grade', text: sc.grade }), h('span', { text: fmt.host(sc.url) }), h('span', { text: fmt.ago(sc.finished_at) })]
        : h('span', { text: 'Run your first audit above' })),
    timeline.length > 1 ? sparkline(timeline.map((t) => t.overall)) : null));

  // 2 · Open issues
  const ic = sc && sc.issue_counts;
  kpis.appendChild(h('div', { cls: 'card kpi' },
    h('div', { cls: 'kpi-lbl' }, 'Open issues', h('span', { cls: 'i', title: 'Failing or warning checks, by how much they matter', text: 'i' })),
    h('div', { cls: 'kpi-val' }, h('span', { cls: 'big num', text: sc ? fmt.num(sc.open_issues) : '—' }), sc ? deltaEl(sc.delta.open_issues, { invert: true }) : null),
    h('div', { cls: 'kpi-sub' }, ic
      ? [h('span', { cls: 'badge badge-bad', text: ic.critical + ' critical' }), h('span', { cls: 'badge badge-warn', text: ic.important + ' important' }), h('span', { cls: 'badge badge-preview', text: ic.minor + ' minor' })]
      : h('span', { text: '—' }))));

  // 3 · AI visibility
  const p = project();
  const v = p && p.visibility;
  if (!premium()) {
    kpis.appendChild(h('div', { cls: 'card kpi locked' },
      h('div', { cls: 'kpi-lbl' }, 'AI visibility', h('span', { cls: 'badge badge-lock' }, icon(ICONS.lock), 'Premium')),
      h('div', { cls: 'kpi-val' }, h('span', { cls: 'big num', text: '—' })),
      h('div', { cls: 'kpi-sub' }, h('span', { text: 'How often AI assistants name you vs competitors' })),
      h('div', { style: { marginTop: '12px' } }, upgradeLink('kpi_visibility', 'Unlock'))));
  } else {
    kpis.appendChild(h('div', { cls: 'card kpi' },
      h('div', { cls: 'kpi-lbl' }, 'AI visibility', h('span', { cls: 'i', title: 'Share of tracked-brand mentions in AI answers (modeled estimate)', text: 'i' })),
      h('div', { cls: 'kpi-val' }, h('span', { cls: 'big num', text: v && v.share_of_voice !== null ? fmt.num(Math.round(v.share_of_voice)) : '—' }), v && v.share_of_voice !== null ? h('span', { cls: 'unit', text: '% SoV' }) : null),
      h('div', { cls: 'kpi-sub' }, v ? [h('span', null, h('b', { text: v.mentions }), ' of ' + v.answered_count + ' answers name you'), h('span', { text: fmt.ago(v.finished_at) })]
        : h('a', { href: '#visibility', cls: 'muted', on: { click: (e) => { e.preventDefault(); setTab('visibility'); } }, text: p ? 'Run your first visibility check →' : 'Create a project to start →' }))));
  }

  // 4 · Plan usage
  kpis.appendChild(h('div', { cls: 'card kpi' },
    h('div', { cls: 'kpi-lbl' }, premium() ? S.sub.label + ' plan' : 'Preview allowance'),
    h('div', { cls: 'meters' }, S.usage.map((u) => {
      if (!u.limit) return h('div', { cls: 'meter-row' }, h('div', { cls: 'top' }, h('span', { text: u.label }), h('span', { cls: 'badge badge-lock' }, icon(ICONS.lock), 'Premium')));
      const pct = Math.min(100, u.used / u.limit * 100);
      return h('div', { cls: 'meter-row' + (pct >= 85 ? ' low' : '') },
        h('div', { cls: 'top' }, h('span', { text: u.label }), h('span', null, h('b', { text: fmt.num(u.used) }), ' / ' + fmt.num(u.limit) + (u.period === 'day' ? ' today' : ' this month'))),
        h('div', { cls: 'meter' }, h('i', { style: { width: pct + '%' } })));
    }))));

  renderActions(sc, report);
  renderStages(sc, report);
}

// Top fixes: Premium gets the written fix; Preview sees what's wrong, fix locked.
function renderActions(sc, report) {
  const box = $('actions');
  box.innerHTML = '';
  $('actionsSub').textContent = '';
  if (!report) {
    box.appendChild(h('p', { cls: 'muted', text: S.scan.tag === 'Loading' ? 'Working out what to fix first…' : 'Your highest-impact fixes appear here after an audit.' }));
    return;
  }
  const sev = { critical: 0, important: 1, minor: 2 };
  let rows = [];
  if (sc.kind === 'comparison') {
    rows = (report.checks || []).filter((c) => c.you_status === 'fail' || c.you_status === 'warn')
      .sort((a, b) => (sev[a.severity] ?? 2) - (sev[b.severity] ?? 2))
      .map((c) => ({ name: c.label || c.name, status: c.you_status, severity: c.severity, detail: c.you_detail, fix: c.recommendation }));
  } else {
    (report.categories || []).forEach((cat) => (cat.checks || []).forEach((c) => {
      if (c.status === 'fail' || c.status === 'warn') rows.push({ name: c.name, status: c.status, severity: c.severity, detail: c.detail, fix: c.recommendation });
    }));
    rows.sort((a, b) => (sev[a.severity] ?? 2) - (sev[b.severity] ?? 2) || (a.status === 'fail' ? -1 : 1));
  }
  rows = rows.slice(0, 4);
  if (!rows.length) { box.appendChild(h('p', { cls: 'muted', text: 'Every scored check passes. Keep it that way with regular audits.' })); return; }
  $('actionsSub').textContent = report.recommendations_source === 'ai' ? 'Written for this site' : '';
  const list = h('div', { cls: 'actions' });
  rows.forEach((r, i) => list.appendChild(h('div', { cls: 'action' },
    h('span', { cls: 'n', text: i + 1 }),
    h('h4', null, r.name, h('span', { cls: 'badge ' + (r.severity === 'critical' ? 'badge-bad' : r.severity === 'important' ? 'badge-warn' : 'badge-preview'), text: r.severity || 'minor' })),
    r.fix ? h('p', { text: r.fix })
      : premium() ? h('p', { cls: 'muted', text: r.detail || '' })
      : h('p', { cls: 'muted' }, (r.detail ? r.detail + ' ' : ''), h('span', { cls: 'badge badge-lock' }, icon(ICONS.lock), 'Fix in Premium')))));
  box.appendChild(list);
  if (!premium()) box.appendChild(h('div', { style: { marginTop: '14px' } }, upgradeLink('actions', 'Unlock every fix')));
}

function renderStages(sc, report) {
  const box = $('stages');
  box.innerHTML = '';
  if (!sc || !sc.stage_scores) { box.appendChild(h('p', { cls: 'muted', text: 'Crawl → Index → Rank → Serve → Experience → AI visibility, scored after an audit.' })); return; }
  const labels = {};
  (report && report.stages || []).forEach((s) => { labels[s.key] = s.label; });
  const list = h('div', { cls: 'stages' });
  Object.entries(sc.stage_scores).forEach(([k, v]) => list.appendChild(h('div', { cls: 'stage-row' },
    h('span', { cls: 'lbl', text: labels[k] || k.charAt(0).toUpperCase() + k.slice(1) }),
    h('span', { cls: 'track' }, h('i', { cls: 'tone-' + tone(v), style: { width: (v || 0) + '%' } })),
    h('span', { cls: 'val c-' + tone(v), text: v === null ? '—' : v }))));
  box.appendChild(list);
  box.appendChild(h('div', { style: { marginTop: '16px' } }, h('button', { cls: 'btn btn-secondary btn-sm mira-ev-report_open', type: 'button', on: { click: () => setTab('audit') } }, 'Open full report')));
}

// ------------------------------------------------------------ site overview
// Four panels, each its own request (GET projects/{id}/panels/{panel}?period=),
// rendered as they arrive. Every number shows the change vs the previous window.
const PANELS = ['ai', 'authority', 'search', 'traffic', 'gsc'];
fmt.compact = (v) => v === null || v === undefined ? '—' : nf({ notation: 'compact', maximumFractionDigits: v >= 1000 ? 1 : 0 }).format(v);
fmt.money = (v) => v === null || v === undefined ? '—' : nf({ style: 'currency', currency: 'USD', maximumFractionDigits: v >= 100 ? 0 : 2 }).format(v);
fmt.duration = (s) => {
  if (s === null || s === undefined) return '—';
  const m = Math.floor(s / 60), r = Math.round(s % 60);
  return m ? `${m}m ${r}s` : `${r}s`;
};
// Signed change in the same units as the value ("+4.7K", "−33", "+$68").
function change(v, { invert = false, money = false, pct = false, dur = false } = {}) {
  if (v === null || v === undefined) return null;
  const good = invert ? v < 0 : v > 0;
  const abs = Math.abs(v);
  // Small fractional changes keep their decimals (pages per visit +0.12, not +0).
  const plain = abs < 100 && !Number.isInteger(abs) ? nf({ maximumFractionDigits: abs < 10 ? 2 : 1 }).format(abs) : fmt.compact(abs);
  const txt = money ? fmt.money(abs) : pct ? nf({ maximumFractionDigits: 1 }).format(abs) + (pct === 'pts' ? ' pts' : '%') : dur ? fmt.duration(abs) : plain;
  const el = h('span', { cls: 'delta ' + (v === 0 ? 'flat' : good ? 'up' : 'down'), text: v === 0 ? '±0' : (v > 0 ? '+' : '−') + txt });
  el.title = 'Change vs the previous ' + (S.periodInfo ? S.periodInfo.days + ' days' : 'period');
  return el;
}
function metric(label, hint, value, delta, sub, { small = false } = {}) {
  return h('div', { cls: 'm' },
    h('div', { cls: 'm-lbl' }, label, hint ? h('span', { cls: 'i', title: hint, text: 'i' }) : null),
    h('div', { cls: 'm-val' }, h('span', { cls: 'v' + (small ? ' sm' : ''), text: value }), delta),
    sub ? h('div', { cls: 'm-sub' }, sub) : null);
}
function pnlHead(title, hint, right) {
  return h('div', { cls: 'pnl-hd' }, h('h3', { text: title }), hint ? h('span', { cls: 'i', title: hint, text: 'i' }) : null, h('span', { cls: 'right' }, right || null));
}
function skeletonPanel(title) {
  return [pnlHead(title), h('div', { cls: 'mgrid' }, [0, 1, 2, 3].map(() => h('div', null,
    h('div', { cls: 'skeleton', style: { height: '12px', width: '60%' } }), h('div', { cls: 'skeleton', style: { height: '34px', width: '70%', marginTop: '10px' } }))))];
}
// A panel whose data source isn't connected yet (DataForSEO, the tracking
// script). Visitors see a friendly "coming soon" note; the technical reason
// (d.message / d.setup from the API) stays in the browser console for the team.
function connectCard(title, hint, d, ghosts) {
  if (d.message || d.setup) console.info('[Mira Premium] ' + title + ': ' + [d.message, d.setup].filter(Boolean).join(' '));
  return [pnlHead(title, hint, h('span', { cls: 'tag-src', text: 'Coming soon' })),
    h('div', { cls: 'connect' },
      h('div', { cls: 'ghost-metrics' }, ghosts.map((g) => h('div', null, h('b', { text: '—' }), g))),
      h('p', { text: "Sorry, this data isn't available at the moment. It will be available soon." }))];
}
function sourceTag(d, label) {
  if (d.demo) return h('span', { cls: 'tag-src sandbox', title: 'Sample data mode: generated numbers so every chart can be seen. Connect DataForSEO for real data.', text: 'Sample data' });
  if (d.sandbox) return h('span', { cls: 'tag-src sandbox', title: 'DataForSEO sandbox: real response shape, dummy numbers', text: 'Sandbox data' });
  return h('span', { cls: 'tag-src', title: d.fetched_at ? 'Updated ' + fmt.date(d.fetched_at) : '', text: label });
}

function setPeriod(key) {
  S.period = key;
  document.querySelectorAll('#periodSeg button').forEach((b) => b.setAttribute('aria-checked', String(b.dataset.period === key)));
  track('tab_open', { tab: 'overview', period: key });
  loadPanels();
}
async function loadPanels(only, refresh) {
  const p = project();
  if (!only) renderPanels();
  if (!premium() || !p) return;
  const id = p.id, period = S.period;
  (only || PANELS).forEach(async (name) => {
    S.panels[name] = RD.loading();
    renderPanel(name);
    try {
      const res = await api('panel', { id, panel: name, period, refresh });
      if (S.projectId !== id || S.period !== period) return;     // a newer selection won
      S.periodInfo = res.period;
      S.panels[name] = RD.success(res.data);
    } catch (e) {
      if (S.projectId !== id || S.period !== period) return;
      S.panels[name] = RD.failure(e);
    }
    $('ovPeriodLabel').textContent = S.periodInfo ? S.periodInfo.label : '';
    renderPanel(name);
    // The Traffic card offers "Choose a GA4 property" once it knows Google is connected.
    if (name === 'gsc' && S.panels.traffic.tag === 'Success' && !S.panels.traffic.data.connected) renderPanel('traffic');
  });
  if (only) return;
  loadReport('performance');
  loadReport('positions');
}
function renderPanels() {
  const body = $('ovBody'), p = project();
  $('ovDomain').textContent = p ? p.domain : '';
  let gate = $('ovGate');
  if (!premium() || !p) {
    body.querySelectorAll('.ov-grid, .pnl-wide').forEach((el) => { el.hidden = true; });
    if (!gate) { gate = h('div', { id: 'ovGate' }); body.appendChild(gate); }
    gate.innerHTML = '';
    gate.appendChild(!premium()
      ? lockedFeature('overview', 'Your whole site, in one', 'view.', 'AI answer visibility, link authority, search rankings and real traffic — each compared with the previous period.',
          ['AI responses: mentions and cited pages, with change', 'Authority: backlinks, referring domains and new vs lost links', 'Search: keywords, traffic, value and positions', 'Traffic: visits, visitors, engagement and sources'], null)
      : stateCard(ICONS.chart, S.sub.signed_in ? 'Create a project to see its overview' : 'Sign in to see your site overview',
          'A project is your website plus the competitors you measure against. The overview fills in from audits, AI visibility runs, SEO data and your traffic.',
          S.sub.signed_in ? h('button', { cls: 'btn btn-primary', type: 'button', on: { click: newProjectFlow }, text: 'New project' }) : h('a', { cls: 'btn btn-primary', href: LOGIN_URL, text: 'Sign in' })));
    return;
  }
  if (gate) gate.remove();
  body.querySelector('.ov-grid').hidden = false;
  ['pnl-traffic', 'pnl-gsc', 'pnl-performance', 'pnl-positions'].forEach((id) => { $(id).hidden = false; });
  PANELS.forEach(renderPanel);
  renderPerformance();
  renderPositions();
}
function renderPanel(name) {
  const el = $('pnl-' + name), st = S.panels[name];
  const titles = { ai: 'AI responses', authority: 'Authority', search: 'Search', traffic: 'Traffic analytics', gsc: 'Search Console' };
  el.innerHTML = '';
  if (name === 'search') $('pnl-keywords').hidden = true;
  if (st.tag === 'NotAsked' || st.tag === 'Loading') { el.append(...skeletonPanel(titles[name])); return; }
  if (st.tag === 'Failure') {
    const ex = explain(st.error);
    el.append(pnlHead(titles[name]), h('p', { cls: 'muted', style: { fontSize: '13.5px' }, text: ex.msg }),
      h('button', { cls: 'btn btn-secondary btn-sm', type: 'button', style: { marginTop: '12px' }, on: { click: () => loadPanels([name]) }, text: 'Retry' }));
    return;
  }
  ({ ai: renderAiPanel, authority: renderAuthorityPanel, search: renderSearchPanel, traffic: renderTrafficPanel, gsc: renderGscPanel })[name](el, st.data);
}

function renderAiPanel(el, d) {
  el.append(pnlHead('AI responses', 'How often AI answers name your brand and cite your pages. Modeled from your prompt set.',
    d.sample ? sourceTag({ demo: true }) : h('span', { cls: 'tag-src', text: 'Modeled' })));
  if (!d.has_runs) {
    el.append(h('div', { cls: 'connect' }, h('div', { cls: 'ghost-metrics' }, h('div', null, h('b', { text: '—' }), 'Responses'), h('div', null, h('b', { text: '—' }), 'Mention rate')),
      h('p', { text: 'No AI visibility runs yet. Add prompts and run a check to see how ChatGPT answers about your market.' }),
      h('div', null, h('button', { cls: 'btn btn-primary btn-sm', type: 'button', on: { click: () => setTab('visibility') } }, icon(ICONS.spark), 'Set up AI visibility'))));
    return;
  }
  const a = d.all;
  el.append(h('div', { cls: 'mgrid' },
    metric('All platforms', 'Answers that name your brand in this window', fmt.num(a.responses.value), change(a.responses.delta),
      [h('span', { text: 'Pages' }), h('b', { text: fmt.num(a.pages.value) }), change(a.pages.delta)]),
    metric('Mention rate', 'Share of answered prompts that name your brand', a.rate.value === null ? '—' : fmt.pct(a.rate.value), change(a.rate.delta, { pct: 'pts' }),
      [h('span', { text: 'of' }), h('b', { text: fmt.num(a.answers.value) }), h('span', { text: 'answers' })])));
  el.append(h('table', { cls: 'ptable' },
    h('thead', null, h('tr', null, h('th', { text: 'Platform' }), h('th', { cls: 'r', text: 'Responses' }), h('th', { cls: 'r', text: 'Pages' }))),
    h('tbody', null, d.platforms.map((pl) => h('tr', { cls: pl.tracked ? null : 'off', title: pl.tracked ? (pl.model ? 'Answers from ' + pl.model : '') : 'Not tracked on this plan yet' },
      h('td', null, h('span', { cls: 'plat' }, h('span', { cls: 'pdot' }), pl.label)),
      pl.tracked ? h('td', { cls: 'r' }, h('span', { cls: 'num', text: fmt.num(pl.responses.value) }), change(pl.responses.delta)) : h('td', { cls: 'r', text: '—' }),
      pl.tracked ? h('td', { cls: 'r' }, h('span', { cls: 'num', text: fmt.num(pl.pages.value) }), change(pl.pages.delta)) : h('td', { cls: 'r', text: 'Not tracked' }))))));
  if (d.trend.length > 1) {
    el.append(h('div', { cls: 'chart-wrap' }, h('div', { cls: 'm-sub', text: 'Mention rate per run' }),
      compareChart(d.trend.map((t) => ({ date: t.date, value: t.rate })), null, { height: 90, unit: '%', max: 100 })));
  }
}

function donut(score) {
  const r = 15.5, c = 2 * Math.PI * r, svg = h('svg', { viewBox: '0 0 36 36', cls: 'donut', 'aria-hidden': 'true' });
  svg.appendChild(h('circle', { cx: 18, cy: 18, r, fill: 'none', stroke: '#ece9e2', 'stroke-width': 5 }));
  svg.appendChild(h('circle', { cx: 18, cy: 18, r, fill: 'none', stroke: '#141417', 'stroke-width': 5, 'stroke-dasharray': c, 'stroke-dashoffset': c * (1 - (score || 0) / 100) }));
  return svg;
}
function renderAuthorityPanel(el, d) {
  const hint = 'Link-based authority from DataForSEO, 0–100. Our own scale — not Ahrefs DR.';
  if (!d.connected) { el.append(...connectCard('Authority', hint, d, ['Authority score', 'Referring domains', 'Backlinks', 'New vs lost'])); return; }
  el.append(pnlHead('Authority', hint, sourceTag(d, 'DataForSEO')));
  el.append(h('div', { cls: 'donut-row' }, donut(d.score.value),
    metric('Authority score', hint, fmt.num(d.score.value), change(d.score.delta), d.first_seen ? [h('span', { text: 'Known since ' + String(d.first_seen).slice(0, 4) })] : null)));
  el.append(h('hr'), h('div', { cls: 'mgrid' },
    metric('Backlinks', 'Live links pointing at the site', fmt.compact(d.backlinks.value), change(d.backlinks.delta),
      [h('span', { text: 'New' }), h('b', { text: '+' + fmt.compact(d.backlinks.new) }), h('span', { text: '· Lost' }), h('b', { cls: 'c-bad', text: '−' + fmt.compact(d.backlinks.lost) })], { small: true }),
    metric('Ref. domains', 'Unique websites linking to the site — the strongest authority signal', fmt.compact(d.referring_domains.value), change(d.referring_domains.delta),
      [h('span', { text: 'New' }), h('b', { text: '+' + fmt.compact(d.referring_domains.new) }), h('span', { text: '· Lost' }), h('b', { cls: 'c-bad', text: '−' + fmt.compact(d.referring_domains.lost) })], { small: true })));
  if (d.history.length > 1) {
    el.append(h('div', { cls: 'chart-wrap' }, h('div', { cls: 'm-sub', text: 'Referring domains by month' }),
      compareChart(d.history.map((p) => ({ date: p.date + '-01', value: p.referring_domains })), null, { height: 90 })));
  }
}

function renderSearchPanel(el, d) {
  const hint = 'Estimated from Google rankings by DataForSEO (' + (d.location ? 'location ' + d.location.location_code : 'US') + ').';
  if (!d.connected) { el.append(...connectCard('Search', 'Organic and paid search estimates', d, ['Organic keywords', 'Organic traffic', 'Traffic value', 'Paid keywords'])); return; }
  const o = d.organic, p = d.paid;
  el.append(pnlHead('Search', hint, sourceTag(d, 'DataForSEO')));
  el.append(h('div', { cls: 'mgrid' },
    metric('Organic keywords', 'Keywords the site ranks for in the top 100', fmt.compact(o.keywords.value), change(o.keywords.delta),
      [h('span', { text: 'Top 3' }), h('b', { text: fmt.num(o.top3.value) }), change(o.top3.delta)]),
    metric('Organic traffic', 'Estimated monthly visits from organic search', fmt.compact(o.traffic.value), change(o.traffic.delta),
      [h('span', { text: 'Value' }), h('b', { text: fmt.money(o.value.value) }), change(o.value.delta, { money: true })])));
  const total = o.positions.reduce((s, b) => s + b.count, 0);
  const shades = ['#141417', '#4a4a50', '#8a8890', '#bdbab2', '#dedbd3'];
  el.append(h('div', { style: { marginTop: '16px' } }, h('div', { cls: 'm-sub', text: 'Ranking positions' }),
    h('div', { cls: 'stack' }, o.positions.map((b, i) => h('i', { title: b.band + ': ' + b.count, style: { width: (total ? b.count / total * 100 : 0) + '%', background: shades[i] } }))),
    h('div', { cls: 'stack-legend' }, o.positions.map((b, i) => h('span', null, h('s', { style: { background: shades[i] } }), b.band + ' · ' + fmt.num(b.count))))));
  el.append(h('hr'), h('div', { cls: 'mgrid' },
    metric('Paid keywords', 'Keywords the site buys Google Ads for', fmt.num(p.keywords.value), change(p.keywords.delta), null, { small: true }),
    metric('Paid traffic', 'Estimated monthly visits from ads', fmt.compact(p.traffic.value), change(p.traffic.delta), [h('span', { text: 'Cost' }), h('b', { text: p.cost ? fmt.money(p.cost) : 'N/A' })], { small: true })));

  // Full-width: organic traffic trend + top keywords.
  const wide = $('pnl-keywords');
  wide.innerHTML = '';
  if (!d.top_keywords.length && d.history.length < 2) { wide.hidden = true; return; }
  wide.hidden = false;
  wide.append(pnlHead('Organic search detail', 'Traffic trend and the keywords sending the most visits', sourceTag(d, 'DataForSEO')));
  const grid = h('div', { cls: 'traffic-grid' });
  grid.append(h('div', null, h('div', { cls: 'm-sub', text: 'Organic traffic by month' }),
    d.history.length > 1 ? compareChart(d.history.map((x) => ({ date: x.date + '-01', value: x.organic_traffic })), null, { height: 180 }) : h('p', { cls: 'muted', text: 'Not enough history yet.' })));
  grid.append(h('div', null, h('table', { cls: 'ptable', style: { marginTop: 0 } },
    h('thead', null, h('tr', null, h('th', { text: 'Keyword' }), h('th', { cls: 'r', text: 'Pos.' }), h('th', { cls: 'r', text: 'Volume' }), h('th', { cls: 'r', text: 'Traffic' }))),
    h('tbody', null, d.top_keywords.slice(0, 8).map((k) => h('tr', { title: k.url || '' },
      h('td', { text: k.keyword || '—' }), h('td', { cls: 'r num', text: k.position ?? '—' }), h('td', { cls: 'r num', text: fmt.compact(k.volume) }), h('td', { cls: 'r num', text: fmt.compact(k.traffic) })))))));
  wide.append(grid);
}

function renderTrafficPanel(el, d) {
  const ga4 = d.source === 'ga4';
  const hint = ga4 ? 'Measured by Google Analytics 4 on your site — real sessions, not estimates.' : 'Measured by the X Platforms tracking script on your site — real visits, not estimates.';
  if (!d.connected) {
    el.append(...connectCard('Traffic analytics', hint, d, ['Visits', 'Unique visitors', 'Page views', 'Bounce rate']));
    const box = el.querySelector('.connect'), g = gscConnected();
    if (d.reason === 'reconnect' && d.connect_url) box.append(h('div', null, googleButton(d.connect_url, 'Reconnect Google')));
    else if (g) box.append(h('div', null, h('button', { cls: 'btn btn-secondary btn-sm', type: 'button', on: { click: () => openGooglePicker(el) } }, 'Choose a Google Analytics property')));
    else if (d.google) box.append(h('div', null, googleButton(d.google, 'Connect Google Analytics')));
    return;
  }
  const m = d.metrics;
  el.append(pnlHead('Traffic analytics', hint, d.demo ? sourceTag(d) : h('span', { cls: 'tag-src', title: d.fetched_at ? 'Updated ' + fmt.date(d.fetched_at) : '', text: ga4 ? 'Google Analytics 4' : 'Tracking script · ' + d.site })));
  el.append(h('div', { cls: 'mgrid six' },
    metric('Visits', 'Sessions started in the window', fmt.compact(m.visits.value), change(m.visits.delta)),
    metric('Unique visitors', 'Distinct visitors', fmt.compact(m.unique_visitors.value), change(m.unique_visitors.delta)),
    metric('Page views', 'Pages viewed', fmt.compact(m.page_views.value), change(m.page_views.delta)),
    metric('Pages per visit', 'Page views ÷ visits', m.pages_per_visit.value === null ? '—' : nf({ maximumFractionDigits: 2 }).format(m.pages_per_visit.value), change(m.pages_per_visit.delta)),
    metric('Visit duration', 'Average session length', fmt.duration(m.visit_duration.value), change(m.visit_duration.delta, { dur: true })),
    metric('Bounce rate', 'Visits that viewed one page — lower is better', m.bounce_rate.value === null ? '—' : fmt.pct(m.bounce_rate.value), change(m.bounce_rate.delta, { invert: true, pct: 'pts' }))));

  const key = S.trafficMetric || 'visits';
  const toggle = h('div', { cls: 'metric-toggle' }, [['visits', 'Visits'], ['page_views', 'Page views']].map(([k, label]) =>
    h('button', { type: 'button', 'aria-pressed': String(k === key), text: label, on: { click: () => { S.trafficMetric = k; renderPanel('traffic'); } } })));
  const series = d.series;
  const grid = h('div', { cls: 'traffic-grid', style: { marginTop: '22px' } });
  grid.append(h('div', null,
    h('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '10px' } }, h('div', { cls: 'm-sub', text: (key === 'visits' ? 'Visits' : 'Page views') + ' per ' + series.bucket }), toggle),
    compareChart(series.current.map((p) => ({ date: p.date, value: p[key] })), series.previous.map((p) => ({ date: p.date, value: p[key] })), { height: 200 }),
    h('div', { cls: 'chart-legend' }, h('span', null, h('i'), 'This period'), h('span', null, h('i', { cls: 'prev' }), 'Previous period'))));
  const list = (title, rows, label, value) => {
    const max = Math.max(1, ...rows.map((r) => r[value]));
    return h('div', null, h('div', { cls: 'side-h', text: title }), rows.length ? h('div', { cls: 'mini-list' }, rows.map((r) => h('div', { cls: 'row' },
      h('span', { cls: 't', title: r[label], text: r[label] }), h('b', { cls: 'num', text: fmt.compact(r[value]) }), h('span', { cls: 'bar' }, h('i', { style: { width: r[value] / max * 100 + '%' } })))))
      : h('p', { cls: 'muted', style: { fontSize: '13px' }, text: 'No data in this window.' }));
  };
  grid.append(h('div', { cls: 'grid', style: { gap: '20px', alignContent: 'start' } },
    list('Top pages', d.top_pages, 'path', 'views'), list('Sources', d.sources, 'source', 'visits'), list('Devices', d.devices, 'device', 'visits')));
  el.append(grid);
}

// ------------------------------------------------------------ data sources
// "Where this data comes from" strip at the top of the DataForSEO tabs: one row per
// report naming the provider and the exact data pulled, with the provider's current mode.
const SOURCES = {
  overview: [
    ['AI responses', 'Your AI visibility runs · ChatGPT (OpenAI)', 'Brand mentions, cited pages and mention rate from your prompt set', 'ai'],
    ['Authority', 'DataForSEO · Backlinks API', 'Summary: authority score, backlinks, referring domains · History: monthly growth, new vs lost', 'dfs'],
    ['Search', 'DataForSEO · Labs API', 'Domain rank overview: keywords, traffic, value, positions · Historical rank overview: monthly trend · Ranked keywords: top keywords', 'dfs'],
    ['Traffic analytics', 'X Platforms tracking script, or Google Analytics 4', 'Visits, visitors, engagement, sources, devices and AI-assistant referrals — measured, not estimated', 'own'],
    ['Search Console', 'Google Search Console · your Google connection', 'Clicks, impressions, CTR, average position, top queries, pages, countries, devices', 'google'],
    ['Performance · Positions', 'DataForSEO · reuses the Search and Authority data', 'Monthly organic traffic, keywords, value and links; ranking position bands — no extra calls', 'dfs'],
  ],
  organic: [
    ['Search intent · Branded vs non-branded · SERP features', 'DataForSEO · Labs · Ranked keywords', 'Each ranking keyword’s intent, whether it names your brand, and the SERP features you own', 'dfs'],
    ['Intent & SERP features over time', 'Daily saved DataForSEO snapshots', 'Built up from the ranked keywords saved each day — history grows from your first day', 'dfs'],
    ['Traffic share by country', 'DataForSEO · Labs · Domain rank overview', 'Keywords and traffic in each of {markets} markets (one call per market)', 'dfs'],
    ['Top pages', 'DataForSEO · Labs · Relevant pages', 'Pages earning the most organic traffic, plus AI citations from your visibility runs', 'dfs'],
    ['Organic competitors', 'DataForSEO · Labs · Competitors domain', 'Sites ranking for the same keywords: common keywords, overlap, their traffic', 'dfs'],
    ['Traffic vs competitors · Share of voice', 'DataForSEO · Labs · Historical rank overview', 'Monthly organic traffic for you and each tracked competitor', 'dfs'],
    ['Paid keywords', 'DataForSEO · Labs · Ranked keywords (paid)', 'Keywords you buy Google Ads for, with position, volume and cost', 'dfs'],
  ],
  backlinks: [
    ['Backlink profile · Referring domains · New & lost', 'DataForSEO · Backlinks · Summary + History', 'Totals, authority score and month-by-month new vs lost referring domains', 'dfs'],
    ['Authority distribution · Followed vs nofollow · Top-level domains · Referring domains', 'DataForSEO · Backlinks · Referring domains', 'The strongest linking sites with their rank, link counts and first-seen date', 'dfs'],
    ['Anchors', 'DataForSEO · Backlinks · Anchors', 'The link text other sites use when they link to you', 'dfs'],
    ['Backlinks · Broken backlinks', 'DataForSEO · Backlinks · Backlinks', 'Individual links: source page, target page, follow status, and links to pages that no longer work', 'dfs'],
    ['Most linked pages', 'DataForSEO · Backlinks · Domain pages summary', 'Your pages with the most referring domains and backlinks', 'dfs'],
  ],
  visibility: [
    ['Answers · Share of voice · Mentions vs citations · Mentions by brand', 'ChatGPT (OpenAI) · your prompt set', 'Each prompt is asked to ChatGPT; we read every answer for brand mentions, position, links and sentiment', 'ai'],
    ['Top-cited pages · Domains AI cites · Coverage by topic · By prompt tag', 'Calculated from those ChatGPT answers', 'Which of your pages and which other sites the answers link to, per topic and tag', 'ai'],
    ['Estimated impressions', 'DataForSEO · Labs · Keyword overview', 'Monthly Google search volume for each prompt\u2019s keyword, used to weight mentions by real demand (checked about once a month per keyword)', 'dfs'],
    ['AI traffic', 'X Platforms tracking script, or Google Analytics 4', 'Real visits from ChatGPT, Perplexity, Gemini, Copilot and Claude, by referrer or utm_source', 'own'],
  ],
  audit: [
    ['Audit score · Stages · Issues', 'Mira crawler · fetches your pages directly', 'HTML, headings, meta tags, links, robots.txt, sitemap, structured data and AI-readiness checks', 'own'],
    ['Speed & experience', 'Google PageSpeed Insights', 'Core Web Vitals and Lighthouse performance for the page', 'google'],
    ['Recommendations', 'ChatGPT (OpenAI)', 'Prioritised fixes written from the audit findings', 'ai'],
  ],
  chat: [
    ['Ask Mira answers', 'ChatGPT (OpenAI)', 'Answers about your site, grounded in its latest audit and the data on the other tabs', 'ai'],
  ],
  history: [
    ['Audits · AI visibility runs', 'Saved in your workspace', 'Every audit and visibility run you have done, with the change since the one before', 'own'],
  ],
};
function dfsChip() {
  const d = (BOOT.sources || {}).dataforseo || {};
  const map = {
    live: ['Live', '', 'Real data for ' + d.market + ', refreshed once every ' + d.cache_hours + ' hours, the first time a report is opened.'],
    sandbox: ['Sandbox · test numbers', ' sandbox', 'DATAFORSEO_SANDBOX=true: real response format with made-up numbers. Set it to false for real data.'],
    sample: ['Sample data', ' sandbox', 'Generated numbers so every chart can be seen. Add DataForSEO credentials for real data.'],
    off: ['Not connected', ' sandbox', 'Add DATAFORSEO_LOGIN and DATAFORSEO_PASSWORD to .env.'],
  }[d.mode || 'off'];
  return { el: h('span', { cls: 'tag-src' + map[1], title: map[2], text: 'DataForSEO · ' + map[0] }), note: map[2] };
}
// Small source label for a report card heading, matching the Data sources bar.
function srcLabel(kind, extra) {
  if (kind === 'dfs') {
    const mode = ((BOOT.sources || {}).dataforseo || {}).mode || 'off', chip = dfsChip();
    return h('span', { cls: 'tag-src' + (mode === 'live' ? '' : ' sandbox'), title: chip.note, text: 'DataForSEO' + (extra ? ' · ' + extra : '') + (mode === 'live' ? '' : ' · ' + chip.el.textContent.replace('DataForSEO · ', '')) });
  }
  return h('span', { cls: 'tag-src', title: 'Answers from ChatGPT (OpenAI) to your prompt set', text: 'ChatGPT' + (extra ? ' · ' + extra : '') });
}
function renderSources(tab) {
  const el = $('src-' + tab);
  if (!el || el.childNodes.length) return;
  const d = (BOOT.sources || {}).dataforseo || {}, chip = dfsChip();
  const kind = { dfs: 'DataForSEO', ai: 'OpenAI', own: 'First-party', google: 'Google' };
  const kinds = [...new Set(SOURCES[tab].map((r) => r[3]))];
  const usesDfs = kinds.includes('dfs');
  const rows = SOURCES[tab].map(([report, source, what, k]) => h('tr', null,
    h('td', { cls: 'src-report', text: report }),
    h('td', null, h('span', { cls: 'src-kind k-' + k, text: kind[k] }), h('span', { cls: 'src-name', text: source })),
    h('td', { cls: 'src-what', text: what.replace('{markets}', d.markets || 10) })));
  const details = h('details', { cls: 'src-details' },
    h('summary', null, h('span', { cls: 'src-title', text: 'Data sources' }), usesDfs ? chip.el : null,
      kinds.filter((k) => k !== 'dfs').map((k) => h('span', { cls: 'tag-src', text: { ai: 'ChatGPT · OpenAI', own: 'First-party', google: 'Google' }[k] })),
      usesDfs && (d.mode === 'live' || d.mode === 'sandbox') ? h('span', { cls: 'src-market', text: 'Market: ' + d.market }) : null,
      h('span', { cls: 'src-toggle', text: 'Show what’s pulled' })),
    h('div', { cls: 'table-wrap' }, h('table', { cls: 'ptable src-table' },
      h('thead', null, h('tr', null, h('th', { text: 'Report' }), h('th', { text: 'Source' }), h('th', { text: 'What is pulled' }))),
      h('tbody', null, rows))),
    usesDfs ? h('p', { cls: 'src-foot', text: chip.note + (d.mode === 'live' ? ' Estimates from DataForSEO are labelled as estimates; measured data (your tracking, Google) is labelled measured.' : '') }) : null);
  details.addEventListener('toggle', () => { details.querySelector('.src-toggle').textContent = details.open ? 'Hide' : 'Show what’s pulled'; });
  el.appendChild(details);
}

// ----------------------------------------------------------- search console
// Google's own numbers through the customer's Google connection (GET panels/gsc).
// Not connected → Connect Google; connected without a property → the picker.
const COUNTRY3 = { aus: 'AU', usa: 'US', gbr: 'GB', nzl: 'NZ', can: 'CA', ind: 'IN', sgp: 'SG', deu: 'DE', irl: 'IE', zaf: 'ZA', fra: 'FR', esp: 'ES', ita: 'IT',
  nld: 'NL', bra: 'BR', mex: 'MX', jpn: 'JP', kor: 'KR', chn: 'CN', hkg: 'HK', phl: 'PH', idn: 'ID', mys: 'MY', pak: 'PK', bgd: 'BD', are: 'AE', sau: 'SA',
  swe: 'SE', nor: 'NO', dnk: 'DK', fin: 'FI', che: 'CH', aut: 'AT', bel: 'BE', pol: 'PL', prt: 'PT', tur: 'TR', nga: 'NG', ken: 'KE', egy: 'EG', vnm: 'VN', tha: 'TH' };
let regionNames = null;
function countryName(code3) {
  const a2 = COUNTRY3[String(code3).toLowerCase()];
  if (!a2) return String(code3).toUpperCase();
  try { regionNames = regionNames || new Intl.DisplayNames(undefined, { type: 'region' }); return regionNames.of(a2); } catch (e) { return a2; }
}
function gscConnected() {
  const st = S.panels.gsc;
  return !!(st && st.tag === 'Success' && st.data && st.data.email && !st.data.demo && st.data.reason !== 'reconnect');
}
function googleButton(href, label) {
  return h('a', { cls: 'btn btn-primary btn-sm mira-ev-google_connect', href }, h('span', { cls: 'g-mark', 'aria-hidden': 'true', text: 'G' }), label);
}
function renderGscPanel(el, d) {
  const hint = 'Clicks, impressions, CTR and average position from Google Search Console — measured by Google, not estimated.';
  if (!d.connected) {
    el.append(pnlHead('Search Console', hint, h('span', { cls: 'tag-src', text: d.reason === 'pick_site' ? 'Connected · ' + (d.email || 'Google') : 'Not connected' })));
    if (d.reason === 'pick_site') { googlePicker(el, { intro: d.message }); return; }
    const box = h('div', { cls: 'connect' },
      h('div', { cls: 'ghost-metrics four' }, ['Clicks', 'Impressions', 'Avg. CTR', 'Avg. position'].map((g) => h('div', null, h('b', { text: '—' }), g))),
      h('p', { text: d.message }), d.setup ? h('p', null, h('code', { text: d.setup })) : null);
    if (d.connect_url) {
      box.append(h('div', { cls: 'g-actions' }, googleButton(d.connect_url, d.reason === 'reconnect' ? 'Reconnect Google' : 'Connect Google'),
        h('span', { cls: 'muted', text: 'Read-only access to Search Console and Analytics. Disconnect any time.' })));
    }
    el.append(box);
    return;
  }
  const m = d.metrics, prevNote = d.previous_complete ? null : 'Search Console keeps 16 months, so there is no full previous year to compare with.';
  const actions = h('span', { cls: 'g-head-actions' });
  if (d.demo) actions.append(sourceTag(d));
  else {
    actions.append(h('span', { cls: 'tag-src', title: (d.email ? 'Connected as ' + d.email + '. ' : '') + (d.fetched_at ? 'Updated ' + fmt.date(d.fetched_at) : ''), text: 'Google · ' + String(d.site).replace(/^sc-domain:/, '') }),
      h('button', { cls: 'btn btn-ghost btn-sm', type: 'button', title: 'Change property or disconnect', on: { click: () => openGooglePicker(el) } }, 'Settings'));
  }
  el.append(pnlHead('Search Console', hint, actions));
  if (d.demo && d.connect_url) {
    el.append(h('div', { cls: 'note', style: { margin: '4px 0 14px', alignItems: 'center' } }, icon(ICONS.info),
      h('span', { style: { flex: 1 }, text: 'Sample numbers. Connect Google to see what Search Console records for your site.' }), googleButton(d.connect_url, 'Connect Google')));
  }
  el.append(h('div', { cls: 'mgrid four' },
    metric('Clicks', 'Clicks from Google Search to the site', fmt.compact(m.clicks.value), change(m.clicks.delta)),
    metric('Impressions', 'Times the site appeared in Google results', fmt.compact(m.impressions.value), change(m.impressions.delta)),
    metric('Avg. CTR', 'Clicks ÷ impressions', fmt.pct(m.ctr.value), change(m.ctr.delta, { pct: 'pts' })),
    metric('Avg. position', 'Average top position in results — lower is better', m.position.value === null ? '—' : nf({ maximumFractionDigits: 1 }).format(m.position.value), change(m.position.delta, { invert: true }))));
  if (prevNote) el.append(h('p', { cls: 'def', text: prevNote }));

  const key = S.gscMetric || 'clicks', labels = { clicks: 'Clicks', impressions: 'Impressions', ctr: 'CTR', position: 'Position' };
  const toggle = h('div', { cls: 'metric-toggle' }, Object.entries(labels).map(([k, label]) =>
    h('button', { type: 'button', 'aria-pressed': String(k === key), text: label, on: { click: () => { S.gscMetric = k; renderPanel('gsc'); } } })));
  const s = d.series, pts = (arr) => arr.map((p) => ({ date: p.date, value: p[key] }));
  const grid = h('div', { cls: 'traffic-grid', style: { marginTop: '22px' } });
  grid.append(h('div', null,
    h('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '10px' } },
      h('div', { cls: 'm-sub', text: labels[key] + ' per ' + s.bucket + (key === 'position' ? ' (lower is better)' : '') }), toggle),
    compareChart(pts(s.current), d.previous_complete ? pts(s.previous) : null, { height: 200, unit: key === 'ctr' ? '%' : '' }),
    h('div', { cls: 'chart-legend' }, h('span', null, h('i'), 'This period'), d.previous_complete ? h('span', null, h('i', { cls: 'prev' }), 'Previous period') : null,
      d.data_through ? h('span', { cls: 'muted', text: 'Data through ' + new Date(d.data_through + 'T00:00:00').toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' }) + ' · Google reports with a 2–3 day delay' }) : null)));
  const list = (title, rows, label) => {
    const max = Math.max(1, ...rows.map((r) => r.clicks));
    return h('div', null, h('div', { cls: 'side-h', text: title }), rows.length ? h('div', { cls: 'mini-list' }, rows.map((r) => h('div', { cls: 'row' },
      h('span', { cls: 't', title: label(r), text: label(r) }), h('b', { cls: 'num', text: fmt.compact(r.clicks) }), h('span', { cls: 'bar' }, h('i', { style: { width: r.clicks / max * 100 + '%' } })))))
      : h('p', { cls: 'muted', style: { fontSize: '13px' }, text: 'No data in this window.' }));
  };
  grid.append(h('div', { cls: 'grid', style: { gap: '20px', alignContent: 'start' } },
    list('Countries · clicks', d.countries.slice(0, 6), (r) => countryName(r.country)),
    list('Devices · clicks', d.devices, (r) => String(r.device).charAt(0) + String(r.device).slice(1).toLowerCase())));
  el.append(grid);

  const rowsOf = (rows, first) => rows.map((r) => h('tr', null, first(r), tdn(fmt.compact(r.clicks)), tdn(fmt.compact(r.impressions)), tdn(fmt.pct(r.ctr)), tdn(nf({ maximumFractionDigits: 1 }).format(r.position))));
  const head = (first) => [first, 'Clicks', 'Impressions', 'CTR', 'Position'];
  el.append(h('div', { cls: 'g-tables' },
    h('div', null, h('div', { cls: 'side-h', text: 'Top queries' }), d.queries.length
      ? table(head('Query'), rowsOf(d.queries, (r) => td(r.query)), { right: [1, 2, 3, 4] }) : h('p', { cls: 'muted', text: 'No queries in this window.' })),
    h('div', null, h('div', { cls: 'side-h', text: 'Top pages' }), d.pages.length
      ? table(head('Page'), rowsOf(d.pages, (r) => h('td', { title: r.url }, r.path)), { right: [1, 2, 3, 4] }) : h('p', { cls: 'muted', text: 'No pages in this window.' }))));
}

// Search Console property + GA4 property for the selected project, from the connected
// Google account. Opened inside a panel (Search Console or Traffic); Save re-fetches both.
function openGooglePicker(panel) {
  const old = panel.querySelector('.g-picker');
  if (old) { old.remove(); return; }
  googlePicker(panel, { closable: true });
}
async function googlePicker(panel, { intro = null, closable = false } = {}) {
  const p = project(), box = h('div', { cls: 'g-picker' }, h('div', { cls: 'skeleton', style: { height: '44px' } }));
  panel.append(box);
  let g;
  try { ({ google: g } = await api('googleStatus', { id: p.id })); }
  catch (e) { box.innerHTML = ''; box.append(h('p', { cls: 'muted', text: explain(e).msg })); return; }
  box.innerHTML = '';
  if (!g.connected) {
    box.append(h('p', { cls: 'muted', text: 'Google is not connected.' }), googleButton(g.connect_url, 'Connect Google'));
    return;
  }
  const opt = (value, label, selected) => h('option', { value, selected: selected ? 'selected' : null, text: label });
  const gsc = h('select', { 'aria-label': 'Search Console property' }, opt('', g.sites.length ? 'Not used' : 'No Search Console properties on this account'),
    g.sites.map((s) => opt(s.site_url, s.site_url.replace(/^sc-domain:/, 'Domain · ') + (s.site_url === g.suggested.gsc_site_url ? '  (matches ' + p.domain + ')' : ''),
      s.site_url === (g.selected.gsc_site_url || g.suggested.gsc_site_url))));
  const ga4 = h('select', { 'aria-label': 'Google Analytics 4 property' }, opt('', g.properties.length ? 'Not used — tracking script only' : 'No Analytics 4 properties on this account'),
    g.properties.map((x) => opt(x.property, x.name + (x.account ? ' — ' + x.account : '') + ' (' + x.property.replace('properties/', '') + ')',
      x.property === (g.selected.ga4_property || g.suggested.ga4_property))));
  const save = h('button', { cls: 'btn btn-primary btn-sm', type: 'button' }, 'Save');
  save.addEventListener('click', async () => {
    setBusy(save, true);
    try {
      await api('googleSelect', { id: p.id }, { gsc_site_url: gsc.value || null, ga4_property: ga4.value || null });
      p.gsc_site_url = gsc.value || null; p.ga4_property = ga4.value || null;
      toast('Google properties saved.');
      loadPanels(['gsc', 'traffic']);
    } catch (e) { const ex = explain(e); toast(ex.msg, { error: true, action: ex.action }); }
    finally { setBusy(save, false); }
  });
  const disconnect = h('button', { cls: 'btn btn-ghost btn-sm', type: 'button', text: 'Disconnect Google' });
  disconnect.addEventListener('click', async () => {
    if (!confirm('Disconnect Google? Search Console and Google Analytics data stop showing for every project until you connect again.')) return;
    setBusy(disconnect, true);
    try { await api('googleDisconnect'); toast('Google disconnected.'); loadPanels(['gsc', 'traffic']); }
    catch (e) { toast(explain(e).msg, { error: true }); setBusy(disconnect, false); }
  });
  const errs = Object.values(g.errors || {});
  box.append(...[
    h('div', { cls: 'g-who' }, h('span', { cls: 'g-mark', 'aria-hidden': 'true', text: 'G' }), h('span', null, 'Connected as ', h('b', { text: g.email || 'your Google account' })),
      closable ? h('button', { cls: 'btn btn-ghost btn-sm', type: 'button', style: { marginLeft: 'auto' }, on: { click: () => box.remove() }, text: 'Close' }) : null),
    intro ? h('p', { cls: 'muted', style: { fontSize: '13.5px' }, text: intro }) : null,
    errs.length ? h('div', { cls: 'note' }, icon(ICONS.alert), h('span', { text: errs.join(' ') })) : null,
    h('label', { cls: 'g-row' }, h('span', null, 'Search Console', h('small', { text: 'Clicks, impressions, CTR and positions' })), gsc),
    h('label', { cls: 'g-row' }, h('span', null, 'Google Analytics 4', h('small', { text: 'Traffic, when the tracking script isn’t installed' })), ga4),
    h('div', { cls: 'g-actions' }, save, disconnect)].filter(Boolean));
}

// Line chart: current series (solid ink) over an optional previous series (dashed),
// hairline grid, first/middle/last date labels, hover tooltip with both values.
function compareChart(cur, prev, { height = 160, unit = '', max = null } = {}) {
  const w = 640, padL = 34, padB = 22, padT = 8, innerH = height - padB - padT;
  const vals = cur.map((p) => p.value).concat((prev || []).map((p) => p.value)).filter((v) => v !== null && v !== undefined);
  const top = max ?? niceMax(Math.max(1, ...vals));
  const x = (i, n) => padL + (n <= 1 ? (w - padL - 8) / 2 : i / (n - 1) * (w - padL - 8));
  const y = (v) => padT + innerH - (v / top) * innerH;
  const wrap = h('div', { cls: 'chart-wrap', style: { marginTop: '10px' } });
  const svg = h('svg', { viewBox: `0 0 ${w} ${height}`, preserveAspectRatio: 'none', style: { width: '100%', height: height + 'px', display: 'block' }, role: 'img', 'aria-label': 'Trend chart' });
  [0, 0.5, 1].forEach((f) => {
    const gy = padT + innerH - f * innerH;
    svg.appendChild(h('line', { x1: padL, x2: w - 4, y1: gy, y2: gy, stroke: '#eeebe4', 'stroke-width': 1 }));
    const t = h('text', { x: 0, y: gy + 4, 'font-size': 10, fill: '#a2a0a7', 'font-family': 'Space Grotesk, Inter, sans-serif' }); t.textContent = fmt.compact(Math.round(top * f)) + unit; svg.appendChild(t);
  });
  const line = (pts, attrs) => {
    const xy = pts.map((p, i) => p.value === null || p.value === undefined ? null : [x(i, pts.length), y(p.value)]).filter(Boolean);
    if (xy.length > 1) svg.appendChild(h('polyline', Object.assign({ points: xy.map((q) => q.join(',')).join(' '), fill: 'none', 'stroke-linejoin': 'round', 'stroke-linecap': 'round', 'vector-effect': 'non-scaling-stroke' }, attrs)));
    return xy;
  };
  if (prev) line(prev, { stroke: '#b9b6ae', 'stroke-width': 1.6, 'stroke-dasharray': '4 4' });
  const xy = line(cur, { stroke: '#141417', 'stroke-width': 2 });
  if (xy.length) { const l = xy[xy.length - 1]; svg.appendChild(h('circle', { cx: l[0], cy: l[1], r: 3.5, fill: '#c9a13a' })); }
  const label = (iso) => new Date(iso).toLocaleDateString(undefined, { day: 'numeric', month: 'short' });
  const n = cur.length;
  [0, Math.floor((n - 1) / 2), n - 1].filter((v, i, a) => n && a.indexOf(v) === i).forEach((i) => {
    const t = h('text', { x: x(i, n), y: height - 4, 'font-size': 10, fill: '#a2a0a7', 'text-anchor': i === 0 ? 'start' : i === n - 1 ? 'end' : 'middle' });
    t.textContent = label(cur[i].date); svg.appendChild(t);
  });
  const guide = h('line', { y1: padT, y2: padT + innerH, stroke: '#141417', 'stroke-width': 1, 'stroke-dasharray': '2 3', opacity: 0 });
  svg.appendChild(guide);
  const tip = h('div', { cls: 'chart-tip' });
  wrap.append(svg, tip);
  svg.addEventListener('mousemove', (e) => {
    const box = svg.getBoundingClientRect();
    const px = (e.clientX - box.left) / box.width * w;
    const i = Math.max(0, Math.min(n - 1, Math.round((px - padL) / ((w - padL - 8) / Math.max(1, n - 1)))));
    const gx = x(i, n);
    guide.setAttribute('x1', gx); guide.setAttribute('x2', gx); guide.setAttribute('opacity', 0.35);
    tip.innerHTML = '';
    tip.append(h('div', null, label(cur[i].date) + ': ', h('b', { text: fmt.num(cur[i].value) + unit })));
    if (prev && prev[i]) tip.append(h('div', { cls: 'p' }, 'Previous (' + label(prev[i].date) + '): ' + fmt.num(prev[i].value) + unit));
    tip.style.left = (gx / w * 100) + '%'; tip.style.top = (y(cur[i].value || 0) / height * box.height) + 'px'; tip.style.opacity = 1;
  });
  svg.addEventListener('mouseleave', () => { tip.style.opacity = 0; guide.setAttribute('opacity', 0); });
  return wrap;
}
function niceMax(v) {
  const mag = Math.pow(10, Math.floor(Math.log10(v)));
  return [1, 2, 2.5, 5, 10].map((m) => m * mag).find((c) => c >= v) || v;
}

// ----------------------------------------------------------------- reports
// Site Explorer + Brand Radar reports (GET projects/{id}/panels/{name}). One loader,
// one cache per project; each report re-renders only the view that shows it.
const REPORT_VIEW = {
  performance: () => renderPerformance(), positions: () => renderPositions(),
  insights: () => renderOrganic(), countries: () => renderOrganic(), pages: () => renderOrganic(),
  competitors: () => renderOrganic(), paid: () => renderOrganic(), difficulty: () => renderOrganic(),
  backlinks: () => renderBacklinks(), links: () => renderBacklinks(), trends: () => renderOrganic(),
  ai_brand: () => renderBrandRadar(),
};
const PALETTE = ['#141417', '#3f6fd8', '#1f8a5b', '#b7791f', '#8a8890', '#c8473f'];
const YOU = '#c9a13a';
async function loadReport(name, force) {
  const p = project();
  if (!premium() || !p) return;
  const cur = S.reports[name];
  if (!force && cur && cur.pid === p.id && cur.tag !== 'Failure') { REPORT_VIEW[name](); return; }
  S.reports[name] = Object.assign(RD.loading(), { pid: p.id });
  REPORT_VIEW[name]();
  try {
    const res = await api('panel', { id: p.id, panel: name, period: S.period });
    if (S.projectId !== p.id) return;
    S.reports[name] = Object.assign(RD.success(res.data), { pid: p.id });
  } catch (e) {
    if (S.projectId !== p.id) return;
    S.reports[name] = Object.assign(RD.failure(e), { pid: p.id });
  }
  REPORT_VIEW[name]();
}
const rep = (name) => S.reports[name] || RD.notAsked;
// Renders a report's state into a card: skeleton, error, not-connected, or the body.
function reportCard(el, title, hint, name, body, ghosts) {
  el.innerHTML = '';
  const st = rep(name);
  if (st.tag === 'NotAsked' || st.tag === 'Loading') { el.append(...skeletonPanel(title)); return; }
  if (st.tag === 'Failure') {
    el.append(pnlHead(title, hint), h('p', { cls: 'muted', style: { fontSize: '13.5px' }, text: explain(st.error).msg }),
      h('button', { cls: 'btn btn-secondary btn-sm', type: 'button', style: { marginTop: '12px' }, on: { click: () => loadReport(name, true) }, text: 'Retry' }));
    return;
  }
  if (st.data.connected === false) { el.append(...connectCard(title, hint, st.data, ghosts || [])); return; }
  body(el, st.data);
}
function flag(iso) { return iso && iso.length === 2 ? String.fromCodePoint(...[...iso.toUpperCase()].map((c) => 0x1F1A5 + c.charCodeAt(0))) : ''; }
function shortUrl(u) { return String(u || '').replace(/^https?:\/\/(www\.)?/i, ''); }
function table(head, rows, { right = [] } = {}) {
  return h('div', { cls: 'table-wrap' }, h('table', { cls: 'ptable', style: { marginTop: '4px' } },
    h('thead', null, h('tr', null, head.map((t, i) => h('th', { cls: right.includes(i) ? 'r' : null, text: t })))),
    h('tbody', null, rows.length ? rows : h('tr', null, h('td', { colspan: head.length, cls: 'muted', text: 'No data yet.' })))));
}
const td = (v, cls) => h('td', { cls: cls || null }, v);
const tdn = (v) => h('td', { cls: 'r' }, h('span', { cls: 'num', text: v }));

// Multi-series line chart over a shared date axis: legend toggles, hover lists every
// series, optional vertical markers (Google updates).
function multiLine(series, { height = 220, unit = '', notes = [], fmtV = fmt.compact, daily = false } = {}) {
  // daily: day-level points (rank tracking) are labelled by day, not month.
  const axisFmt = daily ? { day: 'numeric', month: 'short' } : { month: 'short', year: '2-digit' };
  const tipFmt = daily ? { day: 'numeric', month: 'long', year: 'numeric' } : { month: 'long', year: 'numeric' };
  const w = 680, padL = 40, padB = 22, padT = 10, innerH = height - padB - padT;
  const dates = [...new Set(series.flatMap((s) => s.points.map((p) => p.date)))].sort();
  const wrap = h('div', { cls: 'chart-wrap', style: { marginTop: '10px' } });
  const hidden = new Set();
  const draw = () => {
    wrap.innerHTML = '';
    const vis = series.filter((s) => !hidden.has(s.name));
    const vals = vis.flatMap((s) => s.points.map((p) => p.value)).filter((v) => v !== null && v !== undefined);
    const top = niceMax(Math.max(1, ...vals));
    const n = dates.length;
    const x = (i) => padL + (n <= 1 ? (w - padL - 8) / 2 : i / (n - 1) * (w - padL - 8));
    const y = (v) => padT + innerH - (v / top) * innerH;
    const svg = h('svg', { viewBox: `0 0 ${w} ${height}`, preserveAspectRatio: 'none', style: { width: '100%', height: height + 'px', display: 'block' }, role: 'img', 'aria-label': 'Trend chart' });
    [0, .25, .5, .75, 1].forEach((f) => {
      const gy = padT + innerH - f * innerH;
      svg.appendChild(h('line', { x1: padL, x2: w - 4, y1: gy, y2: gy, stroke: '#eeebe4', 'stroke-width': 1 }));
      const t = h('text', { x: 0, y: gy + 4, 'font-size': 10, fill: '#a2a0a7' }); t.textContent = fmtV(Math.round(top * f)) + unit; svg.appendChild(t);
    });
    notes.forEach((nt) => {
      const i = dates.findIndex((d) => d.slice(0, 7) >= nt.date.slice(0, 7));
      if (i < 0) return;
      const nx = x(i);
      svg.appendChild(h('line', { x1: nx, x2: nx, y1: padT, y2: padT + innerH, stroke: '#c9a13a', 'stroke-width': 1, 'stroke-dasharray': '3 3' }));
      const c = h('circle', { cx: nx, cy: padT + 4, r: 4, fill: '#c9a13a' }); c.appendChild(h('title', null)); c.firstChild.textContent = nt.name + ' (' + nt.date + ')'; svg.appendChild(c);
    });
    vis.forEach((s) => {
      const byDate = new Map(s.points.map((p) => [p.date, p.value]));
      const xy = dates.map((d, i) => byDate.get(d) === null || byDate.get(d) === undefined ? null : [x(i), y(byDate.get(d))]).filter(Boolean);
      if (xy.length > 1) svg.appendChild(h('polyline', { points: xy.map((q) => q.join(',')).join(' '), fill: 'none', stroke: s.color, 'stroke-width': s.bold ? 2.4 : 1.7, 'stroke-dasharray': s.dash ? '4 4' : null, 'stroke-linejoin': 'round', 'vector-effect': 'non-scaling-stroke' }));
      if (xy.length === 1) svg.appendChild(h('circle', { cx: xy[0][0], cy: xy[0][1], r: 3, fill: s.color }));
    });
    [0, Math.floor((n - 1) / 2), n - 1].filter((v, i, a) => n && a.indexOf(v) === i).forEach((i) => {
      const t = h('text', { x: x(i), y: height - 4, 'font-size': 10, fill: '#a2a0a7', 'text-anchor': i === 0 ? 'start' : i === n - 1 ? 'end' : 'middle' });
      t.textContent = new Date(dates[i]).toLocaleDateString(undefined, axisFmt); svg.appendChild(t);
    });
    const guide = h('line', { y1: padT, y2: padT + innerH, stroke: '#141417', 'stroke-width': 1, 'stroke-dasharray': '2 3', opacity: 0 });
    svg.appendChild(guide);
    const tip = h('div', { cls: 'chart-tip' });
    svg.addEventListener('mousemove', (e) => {
      const box = svg.getBoundingClientRect();
      const px = (e.clientX - box.left) / box.width * w;
      const i = Math.max(0, Math.min(n - 1, Math.round((px - padL) / ((w - padL - 8) / Math.max(1, n - 1)))));
      guide.setAttribute('x1', x(i)); guide.setAttribute('x2', x(i)); guide.setAttribute('opacity', .35);
      tip.innerHTML = '';
      tip.append(h('div', null, h('b', { text: new Date(dates[i]).toLocaleDateString(undefined, tipFmt) })));
      vis.forEach((s) => { const p = s.points.find((q) => q.date === dates[i]); if (p) tip.append(h('div', null, s.name + ': ' + (p.value === null ? '—' : fmt.num(p.value) + unit))); });
      tip.style.left = (x(i) / w * 100) + '%'; tip.style.top = '30px'; tip.style.opacity = 1;
    });
    svg.addEventListener('mouseleave', () => { tip.style.opacity = 0; guide.setAttribute('opacity', 0); });
    wrap.append(svg, tip, h('div', { cls: 'legend' }, series.map((s) => h('button', {
      type: 'button', 'aria-pressed': String(!hidden.has(s.name)),
      on: { click: () => { hidden.has(s.name) ? hidden.delete(s.name) : hidden.add(s.name); if (hidden.size === series.length) hidden.delete(s.name); draw(); } },
    }, h('i', { style: { background: s.color } }), s.name))));
  };
  draw();
  return wrap;
}
// Columns per period: stacked (positions) or diverging (new above, lost below).
function columns(points, keys, { height = 180, diverging = false, labels = {} } = {}) {
  const w = 680, padL = 40, padB = 22, padT = 10, innerH = height - padB - padT;
  const n = points.length;
  const pos = points.map((p) => keys.filter((k) => !k.neg).reduce((s, k) => s + (p[k.key] || 0), 0));
  const neg = points.map((p) => keys.filter((k) => k.neg).reduce((s, k) => s + (p[k.key] || 0), 0));
  const top = niceMax(Math.max(1, ...pos)), bottom = diverging ? niceMax(Math.max(1, ...neg)) : 0;
  const zero = padT + innerH * top / (top + bottom);
  const scale = innerH / (top + bottom);
  const bw = Math.max(4, (w - padL - 8) / Math.max(1, n) * .62);
  const svg = h('svg', { viewBox: `0 0 ${w} ${height}`, preserveAspectRatio: 'none', style: { width: '100%', height: height + 'px', display: 'block' }, role: 'img', 'aria-label': 'Column chart' });
  svg.appendChild(h('line', { x1: padL, x2: w - 4, y1: zero, y2: zero, stroke: '#dcd9d1', 'stroke-width': 1 }));
  [[top, padT], [0, zero]].concat(diverging ? [[-bottom, padT + innerH]] : []).forEach(([v, gy]) => {
    const t = h('text', { x: 0, y: gy + 4, 'font-size': 10, fill: '#a2a0a7' }); t.textContent = fmt.compact(Math.abs(v)); svg.appendChild(t);
  });
  points.forEach((p, i) => {
    const cx = padL + (i + .5) * (w - padL - 8) / n;
    let up = zero, down = zero;
    keys.forEach((k) => {
      const v = p[k.key] || 0;
      if (!v) return;
      const hgt = v * scale;
      const r = h('rect', { x: cx - bw / 2, width: bw, y: k.neg ? down : up - hgt, height: hgt, fill: k.color, rx: 2 });
      r.appendChild(h('title', null)); r.firstChild.textContent = (labels[k.key] || k.key) + ': ' + fmt.num(v) + ' · ' + p.label;
      svg.appendChild(r);
      if (k.neg) down += hgt; else up -= hgt;
    });
    if (i === 0 || i === n - 1 || i === Math.floor(n / 2)) {
      const t = h('text', { x: cx, y: height - 4, 'font-size': 10, fill: '#a2a0a7', 'text-anchor': 'middle' }); t.textContent = p.label; svg.appendChild(t);
    }
  });
  return h('div', null, svg, h('div', { cls: 'legend' }, keys.map((k) => h('span', null, h('i', { style: { background: k.color } }), labels[k.key] || k.key))));
}
const monthLabel = (ym) => new Date(ym.length === 7 ? ym + '-01' : ym).toLocaleDateString(undefined, { month: 'short', year: '2-digit' });

// ------------------------------------------------------ overview: performance
function renderPerformance() {
  const el = $('pnl-performance');
  if (!el || !premium() || !project()) return;
  reportCard(el, 'Performance', 'Monthly organic traffic, keywords, value, paid traffic and link growth, with Google update markers.', 'performance', (box, d) => {
    const metrics = [
      ['organic_traffic', 'Organic traffic'], ['organic_keywords', 'Organic keywords'], ['referring_domains', 'Referring domains'],
      ['backlinks', 'Backlinks'], ['paid_traffic', 'Paid traffic'], ['organic_value', 'Traffic value'],
    ];
    const key = S.perfMetric || 'organic_traffic';
    const yoy = !!S.perfYoy;
    box.append(pnlHead('Performance', 'Monthly history. Gold markers are confirmed Google ranking updates.', sourceTag(d, 'DataForSEO')));
    const controls = h('div', { style: { display: 'flex', flexWrap: 'wrap', gap: '8px', justifyContent: 'space-between', alignItems: 'center' } },
      h('div', { cls: 'metric-toggle' }, metrics.map(([k, label]) => h('button', { type: 'button', 'aria-pressed': String(k === key), text: label, on: { click: () => { S.perfMetric = k; renderPerformance(); } } }))),
      h('div', { cls: 'metric-toggle' }, h('button', { type: 'button', 'aria-pressed': String(yoy), on: { click: () => { S.perfYoy = !yoy; renderPerformance(); } } }, 'Year over year')));
    box.append(controls);
    const label = metrics.find((m) => m[0] === key)[1];
    const series = [{ name: label, color: '#141417', bold: true, points: d.series.map((m) => ({ date: m.date + '-01', value: m[key] })) }];
    if (yoy) series.push({ name: label + ' · a year earlier', color: '#b9b6ae', dash: true, points: d.series.map((m) => ({ date: m.date + '-01', value: m.last_year ? m.last_year[key] ?? null : null })) });
    box.append(multiLine(series, { height: 240, notes: d.notes, unit: key === 'organic_value' ? '' : '' }));
  }, ['Organic traffic', 'Keywords', 'Referring domains', 'Backlinks']);
}
function renderPositions() {
  const el = $('pnl-positions');
  if (!el || !premium() || !project()) return;
  reportCard(el, 'Organic keywords by position', 'How many keywords rank in each position band, month by month.', 'positions', (box, d) => {
    box.append(pnlHead('Organic keywords by position', 'Keywords ranking in each band, by month — the shape of your search visibility.', sourceTag(d, 'DataForSEO')));
    if (d.current) {
      box.append(h('div', { cls: 'mgrid', style: { gridTemplateColumns: 'repeat(5,1fr)' } }, d.bands.map((b, i) =>
        metric(b, null, fmt.compact(d.current[b]), null, i === 0 && d.movement.new !== null ? [h('span', { text: 'New' }), h('b', { text: fmt.num(d.movement.new) }), h('span', { text: '· Lost' }), h('b', { cls: 'c-bad', text: fmt.num(d.movement.lost) })] : null, { small: true }))));
    }
    const shades = { '1-3': '#141417', '4-10': '#4a4a50', '11-20': '#8a8890', '21-50': '#bdbab2', '51-100': '#dedbd3' };
    box.append(h('div', { style: { marginTop: '14px' } }, columns(d.history.map((p) => Object.assign({ label: monthLabel(p.date) }, p.positions)),
      d.bands.map((b) => ({ key: b, color: shades[b] })), { height: 200, labels: Object.fromEntries(d.bands.map((b) => [b, 'Pos. ' + b])) })));
  }, ['1–3', '4–10', '11–20', '21–100']);
}

// ------------------------------------------------------------ Organic search
function gateReport(body, feature, title, em, text, bullets) {
  body.innerHTML = '';
  if (!premium()) { body.appendChild(lockedFeature(feature, title, em, text, bullets, null)); return true; }
  if (!project()) {
    body.appendChild(stateCard(ICONS.chart, S.sub.signed_in ? 'Create a project first' : 'Sign in to see this report', 'Reports run on a project — your website plus the competitors you measure against.',
      S.sub.signed_in ? h('button', { cls: 'btn btn-primary', type: 'button', on: { click: newProjectFlow }, text: 'New project' }) : h('a', { cls: 'btn btn-primary', href: LOGIN_URL, text: 'Sign in' })));
    return true;
  }
  return false;
}
function renderOrganic() {
  const body = $('orgBody');
  if (!body || S.tab !== 'organic') return;
  if (gateReport(body, 'organic', 'Every search signal,', 'explained.', 'Where your traffic comes from, which pages earn it, who you compete with, and what you pay for.',
    ['Traffic by search intent and branded vs non-branded', 'Traffic share across 10 markets', 'Top pages with the AI answers that cite them', 'Organic competitors and a monthly traffic race'])) return;
  const c = () => h('div', { cls: 'card pnl' });
  const intent = c(), branded = c(), serp = c(), countries = c(), pages = c(), comps = c(), race = c(), paid = c(), sov = c(), trendsC = c(), kd = c();
  body.append(h('div', { cls: 'rep-grid three', style: { marginTop: 0 } }, intent, branded, serp),
    h('div', { cls: 'rep-full' }, kd),
    h('div', { cls: 'rep-full' }, trendsC),
    h('div', { cls: 'rep-full' }, countries), h('div', { cls: 'rep-full' }, pages),
    h('div', { cls: 'rep-grid' }, comps, race), h('div', { cls: 'rep-full' }, sov), h('div', { cls: 'rep-full' }, paid));

  const intentColors = { informational: '#3f6fd8', commercial: '#c9a13a', transactional: '#1f8a5b', navigational: '#8a8890' };
  reportCard(intent, 'Traffic by search intent', null, 'insights', (box, d) => {
    box.append(pnlHead('Traffic by search intent', 'Why people search for you: learning, comparing, buying or navigating. ' + d.note, sourceTag(d, 'DataForSEO')));
    box.append(h('div', { cls: 'split-bar' }, d.intent.map((r) => h('i', { title: r.intent + ' ' + r.share + '%', style: { width: r.share + '%', background: intentColors[r.intent] } }))));
    box.append(h('div', { cls: 'kv' }, d.intent.flatMap((r) => [h('span', { cls: 'k' }, h('s', { style: { background: intentColors[r.intent] } }), r.intent.charAt(0).toUpperCase() + r.intent.slice(1)),
      h('span', { cls: 'v', text: fmt.pct(r.share) }), h('span', { cls: 'm', text: fmt.compact(r.traffic) + ' visits' })])));
  }, ['Informational', 'Commercial']);
  kd.classList.add('rank-table');
  reportCard(kd, 'Keyword difficulty', null, 'difficulty', (box, d) => {
    box.append(pnlHead('Keyword difficulty', d.note + ' 0–100: higher is harder.', sourceTag(d, 'DataForSEO')));
    if (!d.scored) { box.append(h('p', { cls: 'muted', text: 'No difficulty scores for these keywords yet.' })); return; }
    const total = d.bands.reduce((s, b) => s + b.keywords, 0) || 1;
    box.append(h('div', { cls: 'mgrid three' },
      metric('Average difficulty', 'Mean difficulty of the keywords the site ranks for', String(d.average), null, [h('span', { cls: 'kd-chip', style: { background: kdColor(d.average) }, text: kdBand(d.bands, d.average) })]),
      metric('Easy wins', 'Difficulty under 30 and ranking 4–20: closest to the top 3', fmt.num(d.easy_wins.length), null, [h('span', { text: 'keywords to push first' })]),
      metric('Keywords scored', 'Ranked keywords with a difficulty score', fmt.num(d.scored), null, [h('span', { text: 'of ' + fmt.num(d.sample) + ' analysed' })])));
    box.append(h('div', { style: { marginTop: '16px' } }, h('div', { cls: 'm-sub', text: 'Keywords by difficulty' }),
      h('div', { cls: 'stack' }, d.bands.map((b) => h('i', { title: b.label + ': ' + b.keywords + ' keywords', style: { width: (b.keywords / total * 100) + '%', background: kdColor(b.min + 1) } }))),
      h('div', { cls: 'stack-legend' }, d.bands.map((b) => h('span', null, h('s', { style: { background: kdColor(b.min + 1) } }), b.label + ' · ' + b.keywords)))));
    const rows = (d.easy_wins.length ? d.easy_wins : d.keywords).slice(0, 10);
    box.append(h('div', { cls: 'm-sub', style: { marginTop: '18px' }, text: d.easy_wins.length ? 'Easy wins: low difficulty, already on page 1–2' : 'Top keywords by traffic' }),
      table(['Keyword', 'Difficulty', 'Position', 'Volume', 'Intent'], rows.map((k) => h('tr', null,
        td(k.keyword), h('td', null, kdChip(k.difficulty, k.band)), tdn(k.position ?? '—'), tdn(fmt.compact(k.volume)), td(k.intent || '—', 'muted'))), { right: [2, 3] }));
  }, ['Average difficulty', 'Easy wins']);
  reportCard(branded, 'Branded vs non-branded', null, 'insights', (box, d) => {
    const b = d.branded;
    box.append(pnlHead('Branded vs non-branded', 'Traffic from searches that include your brand vs everything else. Non-branded is the growth engine.', sourceTag(d, 'DataForSEO')));
    box.append(h('div', { cls: 'mgrid' }, metric('Non-branded', null, fmt.pct(b.non_branded.share), null, [h('b', { text: fmt.compact(b.non_branded.traffic) }), h('span', { text: 'visits · ' + b.non_branded.keywords + ' keywords' })]),
      metric('Branded', null, fmt.pct(b.branded.share), null, [h('b', { text: fmt.compact(b.branded.traffic) }), h('span', { text: 'visits · ' + b.branded.keywords + ' keywords' })])));
    box.append(h('div', { cls: 'split-bar', style: { marginTop: '14px' } }, h('i', { style: { width: b.non_branded.share + '%', background: '#141417' } }), h('i', { style: { width: b.branded.share + '%', background: YOU } })));
  }, ['Non-branded', 'Branded']);
  reportCard(serp, 'SERP features owned', null, 'insights', (box, d) => {
    box.append(pnlHead('SERP features owned', 'Keywords where the site holds a special result, not just a blue link. ' + d.note, sourceTag(d, 'DataForSEO')));
    box.append(h('div', { cls: 'kv' }, d.serp_features.flatMap((f) => [h('span', { cls: 'k', text: f.label }), h('span', { cls: 'v', text: fmt.num(f.owned) }), h('span', { cls: 'm', text: 'keywords' })])));
    box.append(h('p', { cls: 'def', style: { marginTop: '12px' }, text: 'AI Overview citations are Google AI answers that link to your site.' }));
  }, ['Featured snippets', 'AI Overview citations']);
  reportCard(countries, 'Traffic share by country', null, 'countries', (box, d) => {
    box.append(pnlHead('Traffic share by country', d.note, sourceTag(d, 'DataForSEO')));
    const max = Math.max(1, ...d.countries.map((r) => r.share));
    box.append(table(['Country', 'Share', 'Traffic', 'Keywords', 'Top 3', 'Value'], d.countries.map((r) => h('tr', null,
      td(h('span', { cls: 'plat' }, h('span', { cls: 'flag', text: flag(r.iso) }), r.country)),
      h('td', { style: { width: '32%' } }, h('div', { style: { display: 'flex', alignItems: 'center', gap: '10px' } },
        h('div', { cls: 'split-bar', style: { flex: 1, height: '6px' } }, h('i', { style: { width: r.share / max * 100 + '%', background: '#141417' } })), h('span', { cls: 'num', style: { minWidth: '44px', textAlign: 'right' }, text: fmt.pct(r.share) }))),
      tdn(fmt.compact(r.traffic)), tdn(fmt.compact(r.keywords)), tdn(fmt.num(r.top3)), tdn(fmt.money(r.value)))), { right: [2, 3, 4, 5] }));
  }, ['Top country', 'Share']);
  reportCard(pages, 'Top pages', null, 'pages', (box, d) => {
    box.append(pnlHead('Top pages', 'Pages bringing the most organic search traffic, and how often AI answers cite each one.', sourceTag(d, 'DataForSEO')));
    const head = ['Page', 'Traffic', 'Share', 'Keywords', 'Top 3', 'Value', 'AI citations'].concat(d.ai_visits_tracked ? ['AI visits'] : []);
    box.append(table(head, d.pages.map((r) => h('tr', null,
      td(h('a', { href: r.url, target: '_blank', rel: 'noopener noreferrer', cls: 'truncate', title: r.url, text: r.path })),
      tdn(fmt.compact(r.traffic)), tdn(fmt.pct(r.share)), tdn(fmt.compact(r.keywords)), tdn(fmt.num(r.top3)), tdn(fmt.money(r.value)),
      h('td', { cls: 'r' }, r.ai_citations ? h('span', { cls: 'chip-you', style: { marginLeft: 0 }, text: r.ai_citations + ' answers' }) : h('span', { cls: 'muted', text: '—' })),
      d.ai_visits_tracked ? tdn(r.ai_visits ? fmt.compact(r.ai_visits) : '—') : null)), { right: [1, 2, 3, 4, 5, 6, 7] }));
    if (d.ai_visits_tracked) box.append(h('p', { cls: 'def', style: { marginTop: '8px' }, text: 'AI visits: real visits from ChatGPT, Perplexity, Gemini and other assistants in the last 30 days, from your tracking script.' }));
  }, ['Top page', 'Traffic']);
  reportCard(comps, 'Organic competitors', null, 'competitors', (box, d) => {
    box.append(pnlHead('Organic competitors', 'Sites ranking for the same keywords as you. Overlap = shared keywords ÷ your keywords.', sourceTag(d, 'DataForSEO')));
    box.append(table(['Competitor', 'Common keywords', 'Overlap', 'Their traffic'], d.competitors.map((r) => h('tr', null,
      td([r.domain, r.tracked ? h('span', { cls: 'chip-comp', text: 'tracked' }) : null]), tdn(fmt.compact(r.common)), tdn(r.overlap === null ? '—' : fmt.pct(r.overlap)), tdn(fmt.compact(r.traffic)))), { right: [1, 2, 3] }));
  }, ['Competitors', 'Common keywords']);
  reportCard(race, 'Traffic vs competitors', null, 'competitors', (box, d) => {
    box.append(pnlHead('Traffic vs competitors', 'Estimated monthly organic traffic for you and your tracked competitors.', sourceTag(d, 'DataForSEO')));
    if (d.race.length < 2) box.append(h('p', { cls: 'def', text: 'Add competitors with a domain under AI visibility → Brand & competitors to race them here.' }));
    box.append(multiLine(d.race.map((r, i) => ({ name: r.you ? project().name + ' (you)' : (r.name || r.domain), color: r.you ? YOU : PALETTE[i % PALETTE.length], bold: r.you,
      points: r.series.map((p) => ({ date: p.date + '-01', value: p.traffic })) })), { height: 220 }));
  }, ['You', 'Competitors']);
  reportCard(paid, 'Paid keywords', null, 'paid', (box, d) => {
    box.append(pnlHead('Paid keywords', 'Keywords the site bids on in Google Ads, with the ad shown.', sourceTag(d, 'DataForSEO')));
    box.append(table(['Keyword', 'Position', 'Volume', 'CPC', 'Ad'], d.keywords.map((r) => h('tr', null,
      td(r.keyword || '—'), tdn(r.position ?? '—'), tdn(fmt.compact(r.volume)), tdn(r.cpc ? fmt.money(r.cpc) : '—'),
      td(h('span', { cls: 'truncate', title: (r.title || '') + ' — ' + (r.url || ''), text: r.title || shortUrl(r.url) })))), { right: [1, 2, 3] }));
    if (!d.keywords.length) box.append(h('p', { cls: 'def', style: { marginTop: '8px' }, text: 'No paid search activity found for this market.' }));
  }, ['Paid keywords', 'Ads']);
  reportCard(sov, 'Organic share of voice', null, 'competitors', (box, d) => {
    box.append(pnlHead('Organic share of voice', 'Each site\u2019s share of the combined organic traffic of you and your tracked competitors, by month.', sourceTag(d, 'DataForSEO')));
    const s = d.share_of_voice;
    if (!s || s.domains.length < 2 || !s.series.length) { box.append(h('p', { cls: 'def', text: 'Add competitors with a domain under AI visibility \u2192 Brand & competitors to see your share of the market.' })); return; }
    const colors = Object.fromEntries(s.domains.map((dm, i) => [dm, dm === project().domain ? YOU : PALETTE[i % PALETTE.length]]));
    const last = s.series[s.series.length - 1];
    box.append(h('div', { cls: 'mgrid', style: { gridTemplateColumns: `repeat(${Math.min(5, s.domains.length)},1fr)` } }, s.domains.slice(0, 5).map((dm) =>
      metric(dm === project().domain ? dm + ' (you)' : dm, null, fmt.pct(last[dm]), s.series.length > 1 ? change(Math.round((last[dm] - s.series[0][dm]) * 10) / 10, { pct: 'pts' }) : null, null, { small: true }))));
    box.append(h('div', { style: { marginTop: '14px' } }, columns(s.series.map((m) => Object.assign({ label: monthLabel(m.date) }, m)), s.domains.map((dm) => ({ key: dm, color: colors[dm] })), { height: 200 })));
  }, ['You', 'Competitors']);
  reportCard(trendsC, 'Search intent & SERP features over time', null, 'trends', (box, d) => {
    box.append(pnlHead('Search intent & SERP features over time', d.note, sourceTag(d, 'DataForSEO')));
    if (d.points.length < 2) { box.append(h('p', { cls: 'def', text: `Tracking since ${d.since || 'today'}. History builds by one point a day — check back tomorrow for a trend.` })); }
    const grid = h('div', { cls: 'traffic-grid' });
    grid.append(h('div', null, h('div', { cls: 'm-sub', text: 'Traffic share by intent (%)' }),
      columns(d.points.map((p) => Object.assign({ label: new Date(p.date).toLocaleDateString(undefined, { day: 'numeric', month: 'short' }) }, p.intent)),
        [{ key: 'informational', color: '#3f6fd8' }, { key: 'commercial', color: '#c9a13a' }, { key: 'transactional', color: '#1f8a5b' }, { key: 'navigational', color: '#8a8890' }], { height: 180 })));
    grid.append(h('div', null, h('div', { cls: 'm-sub', text: 'SERP features owned' }),
      multiLine([['featured_snippet', 'Featured snippets', '#141417'], ['ai_overview_reference', 'AI Overview citations', YOU], ['local_pack', 'Local pack', '#3f6fd8']].map(([k, label, color]) =>
        ({ name: label, color, points: d.points.map((p) => ({ date: p.date, value: p.features[k] ?? 0 })) })), { height: 180, fmtV: (v) => String(v) })));
    box.append(grid);
  }, ['Intent', 'SERP features']);
}

// ----------------------------------------------------------------- Backlinks
function renderBacklinks() {
  const body = $('blBody');
  if (!body || S.tab !== 'backlinks') return;
  if (gateReport(body, 'backlinks', 'See who vouches for', 'you.', 'Every site linking to you, how strong it is, what it says about you — and what you gained or lost.',
    ['Referring domains growth and new vs lost, month by month', 'Authority distribution of linking sites', 'Referring domains and anchor text tables', 'Followed vs nofollow and link types'])) return;
  const c = () => h('div', { cls: 'card pnl' });
  const kpi = c(), growth = c(), newlost = c(), dist = c(), follow = c(), tld = c(), domains = c(), anchors = c(), linksC = c(), linkedC = c(), brokenC = c();
  body.append(kpi, h('div', { cls: 'rep-grid' }, growth, newlost), h('div', { cls: 'rep-grid three' }, dist, follow, tld), h('div', { cls: 'rep-full' }, linksC),
    h('div', { cls: 'rep-grid' }, linkedC, brokenC), h('div', { cls: 'rep-grid' }, domains, anchors));
  reportCard(linksC, 'Backlinks', null, 'links', (box, d) => {
    box.append(pnlHead('Backlinks', 'The strongest link from each linking site: where it sits, what it says, and where it points.', sourceTag(d, 'DataForSEO')));
    box.append(table(['Referring page', 'Authority', 'Anchor & context', 'Target', 'Type'], d.backlinks.slice(0, 25).map((r) => h('tr', null,
      td(h('div', null, h('div', { cls: 'truncate', style: { maxWidth: '260px', fontWeight: 500 }, title: r.from, text: r.from_title || shortUrl(r.from) }), h('div', { cls: 'muted truncate', style: { maxWidth: '260px', fontSize: '12px' }, text: shortUrl(r.from) }))),
      tdn(r.authority ?? '—'),
      td(h('span', { cls: 'def' }, (r.text_pre ? '\u2026' + r.text_pre.slice(-40) + ' ' : ''), h('b', { text: r.anchor || '(image / empty)' }), (r.text_post ? ' ' + r.text_post.slice(0, 40) + '\u2026' : ''))),
      td(h('span', { cls: 'truncate', style: { maxWidth: '180px' }, title: r.to, text: shortUrl(r.to) })),
      td([r.dofollow ? h('span', { cls: 'badge badge-good', text: 'follow' }) : h('span', { cls: 'badge badge-preview', text: 'nofollow' }), r.type !== 'anchor' ? h('span', { cls: 'chip', style: { marginLeft: '6px' }, text: r.type }) : null]))), { right: [1] }));
  }, ['Links']);
  reportCard(linkedC, 'Most linked pages', null, 'links', (box, d) => {
    box.append(pnlHead('Most linked pages', 'Your pages that attract the most linking sites — your link magnets.', sourceTag(d, 'DataForSEO')));
    box.append(table(['Page', 'Ref. domains', 'Backlinks', 'Broken'], d.pages.slice(0, 12).map((r) => h('tr', null,
      td(h('span', { cls: 'truncate', style: { maxWidth: '240px' }, title: r.url, text: shortUrl(r.url) })), tdn(fmt.compact(r.referring_domains)), tdn(fmt.compact(r.backlinks)),
      h('td', { cls: 'r' }, r.broken ? h('span', { cls: 'badge badge-bad', text: r.broken }) : h('span', { cls: 'muted', text: '0' })))), { right: [1, 2, 3] }));
  }, ['Pages']);
  reportCard(brokenC, 'Broken backlinks', null, 'links', (box, d) => {
    box.append(pnlHead('Broken backlinks', 'Links to pages that error. Redirect each target to a live page to win the authority back.', sourceTag(d, 'DataForSEO')));
    if (!d.broken.length) { box.append(h('p', { cls: 'def', text: 'No broken backlinks found — every link points at a live page.' })); return; }
    box.append(table(['From', 'Authority', 'Broken target', 'Status'], d.broken.slice(0, 15).map((r) => h('tr', null,
      td(h('span', { cls: 'truncate', style: { maxWidth: '180px' }, title: r.from, text: r.domain || shortUrl(r.from) })), tdn(r.authority ?? '—'),
      td(h('span', { cls: 'truncate', style: { maxWidth: '200px' }, title: r.to, text: shortUrl(r.to) })), td(h('span', { cls: 'badge badge-bad', text: String(r.status || 'error') })))), { right: [1] }));
    box.append(h('p', { cls: 'def', style: { marginTop: '10px' }, text: 'Fix: add a 301 redirect from each broken target to its closest live page.' }));
  }, ['Broken']);
  const H = 'Link data from DataForSEO. Authority is our 0–100 link score, not Ahrefs DR.';
  reportCard(kpi, 'Backlink profile', H, 'backlinks', (box, d) => {
    const s = d.summary;
    box.append(pnlHead('Backlink profile', H, sourceTag(d, 'DataForSEO')));
    box.append(h('div', { cls: 'mgrid six', style: { gridTemplateColumns: 'repeat(5,1fr)' } },
      h('div', { cls: 'm' }, h('div', { cls: 'donut-row' }, donut(s.score), metric('Authority', H, fmt.num(s.score), null, null))),
      metric('Ref. domains', 'Unique sites linking to you', fmt.compact(s.referring_domains), null, [h('span', { text: 'Followed' }), h('b', { text: fmt.compact(s.followed_domains) })]),
      metric('Backlinks', 'Live links to you', fmt.compact(s.backlinks), null, null),
      metric('Referring IPs', 'Distinct IP addresses linking — a spread signal', fmt.compact(s.referring_ips), null, null),
      metric('Broken backlinks', 'Links pointing at pages that error — reclaim them with redirects', fmt.compact(s.broken_backlinks), null, null)));
  }, ['Authority', 'Ref. domains', 'Backlinks', 'Referring IPs']);
  reportCard(growth, 'Referring domains', null, 'backlinks', (box, d) => {
    box.append(pnlHead('Referring domains', 'The cumulative growth or decline of sites linking to you.', sourceTag(d, 'DataForSEO')));
    box.append(multiLine([{ name: 'Referring domains', color: '#141417', bold: true, points: d.history.map((p) => ({ date: p.date + '-01', value: p.referring_domains })) },
      { name: 'Backlinks', color: '#b9b6ae', points: d.history.map((p) => ({ date: p.date + '-01', value: p.backlinks })) }], { height: 200 }));
  }, ['Growth']);
  reportCard(newlost, 'New & lost referring domains', null, 'backlinks', (box, d) => {
    box.append(pnlHead('New & lost referring domains', 'Sites that started or stopped linking each month.', sourceTag(d, 'DataForSEO')));
    box.append(columns(d.history.slice(-12).map((p) => ({ label: monthLabel(p.date), new: p.new_referring_domains, lost: p.lost_referring_domains })),
      [{ key: 'new', color: '#1f8a5b' }, { key: 'lost', color: '#c8473f', neg: true }], { height: 200, diverging: true, labels: { new: 'New', lost: 'Lost' } }));
  }, ['New', 'Lost']);
  reportCard(dist, 'Authority distribution', null, 'backlinks', (box, d) => {
    const max = Math.max(1, ...d.distribution.map((b) => b.domains));
    box.append(pnlHead('Authority distribution', `Linking sites by authority (top ${d.sampled} referring domains). More on the right = stronger profile.`, sourceTag(d, 'DataForSEO')));
    box.append(h('div', { cls: 'hist' }, d.distribution.map((b) => h('div', { cls: 'col', title: b.band + ': ' + b.domains + ' domains' },
      h('b', { text: b.domains || '' }), h('i', { style: { height: b.domains / max * 100 + '%' } }), h('span', { text: b.band.split('–')[0] })))));
  }, ['0–20', '80–100']);
  reportCard(follow, 'Followed vs nofollow', null, 'backlinks', (box, d) => {
    const t = d.follow.followed + d.follow.nofollow || 1;
    box.append(pnlHead('Followed vs nofollow', 'Followed links pass authority; nofollow links mostly don’t.', sourceTag(d, 'DataForSEO')));
    box.append(h('div', { cls: 'split-bar' }, h('i', { style: { width: d.follow.followed / t * 100 + '%', background: '#141417' } }), h('i', { style: { width: d.follow.nofollow / t * 100 + '%', background: '#d8d5cd' } })));
    box.append(h('div', { cls: 'kv' }, h('span', { cls: 'k' }, h('s', { style: { background: '#141417' } }), 'Followed'), h('span', { cls: 'v', text: fmt.compact(d.follow.followed) }), h('span', { cls: 'm', text: fmt.pct(Math.round(d.follow.followed / t * 1000) / 10) }),
      h('span', { cls: 'k' }, h('s', { style: { background: '#d8d5cd' } }), 'Nofollow'), h('span', { cls: 'v', text: fmt.compact(d.follow.nofollow) }), h('span', { cls: 'm', text: fmt.pct(Math.round(d.follow.nofollow / t * 1000) / 10) })));
    const types = Object.entries(d.types || {}).sort((a, b) => b[1] - a[1]).slice(0, 5);
    if (types.length) box.append(h('div', { cls: 'side-h', style: { marginTop: '16px' }, text: 'Link types' }), h('div', { cls: 'kv', style: { marginTop: 0 } }, types.flatMap(([k, v]) => [h('span', { cls: 'k', text: k }), h('span', { cls: 'v', text: fmt.compact(v) }), h('span')])));
  }, ['Followed', 'Nofollow']);
  reportCard(tld, 'Top-level domains', null, 'backlinks', (box, d) => {
    const rows = Object.entries(d.tlds || {}); const max = Math.max(1, ...rows.map((r) => r[1]));
    box.append(pnlHead('Top-level domains', 'Where your links come from: .com, country domains, .edu, .gov…', sourceTag(d, 'DataForSEO')));
    box.append(h('div', { cls: 'mini-list' }, rows.map(([k, v]) => h('div', { cls: 'row' }, h('span', { cls: 't', text: '.' + k }), h('b', { cls: 'num', text: fmt.compact(v) }), h('span', { cls: 'bar' }, h('i', { style: { width: v / max * 100 + '%' } }))))));
  }, ['.com', 'Country']);
  reportCard(domains, 'Referring domains', null, 'backlinks', (box, d) => {
    box.append(pnlHead('Referring domains', 'Strongest sites linking to you.', sourceTag(d, 'DataForSEO')));
    box.append(table(['Domain', 'Authority', 'Links', 'First seen'], d.domains.slice(0, 25).map((r) => h('tr', { cls: r.lost_date ? 'off' : null },
      td([r.domain, r.nofollow ? h('span', { cls: 'chip-comp', text: 'nofollow' }) : null, r.lost_date ? h('span', { cls: 'badge badge-bad', style: { marginLeft: '6px' }, text: 'lost' }) : null]),
      tdn(r.authority ?? '—'), tdn(fmt.compact(r.backlinks)), tdn(r.first_seen ? String(r.first_seen).slice(0, 10) : '—'))), { right: [1, 2, 3] }));
  }, ['Domains']);
  reportCard(anchors, 'Anchors', null, 'backlinks', (box, d) => {
    box.append(pnlHead('Anchors', 'The clickable text other sites use when linking to you, by referring domains.', sourceTag(d, 'DataForSEO')));
    box.append(table(['Anchor', 'Ref. domains', 'Share', 'Links'], d.anchors.slice(0, 20).map((r) => h('tr', null,
      td(h('span', { cls: 'truncate', title: r.anchor, text: r.anchor })), tdn(fmt.compact(r.referring_domains)), tdn(fmt.pct(r.share)), tdn(fmt.compact(r.backlinks)))), { right: [1, 2, 3] }));
  }, ['Anchors']);
}

// --------------------------------------------------- AI visibility: Brand Radar
function renderBrandRadar() {
  const host = document.getElementById('brandRadar');
  if (!host) return;
  const st = rep('ai_brand');
  host.innerHTML = '';
  if (st.tag !== 'Success' || !st.data.has_runs) return;
  const d = st.data;
  if (d.sample) host.appendChild(h('div', { cls: 'note', style: { marginBottom: '14px', background: 'var(--warn-soft)', borderColor: '#f0d9b0', color: 'var(--warn)' } }, icon(ICONS.info),
    h('span', { text: 'Includes sample AI visibility history (php artisan mira:sample-data). Remove it with --purge before showing real results.' })));
  const c = () => h('div', { cls: 'card pnl' });
  const mc = c(), bot = c(), pagesC = c(), domainsC = c(), topicsC = c(), gapC = c(), brandsC = c(), impC = c(), tagC = c(), aiTrafC = c();
  host.append(h('div', { cls: 'rep-grid three' }, impC, aiTrafC, tagC), h('div', { cls: 'rep-grid' }, mc, bot), h('div', { cls: 'rep-grid three' }, pagesC, domainsC, topicsC), h('div', { cls: 'rep-grid' }, gapC, brandsC));
  const imp = d.impressions;
  impC.append(pnlHead('Estimated impressions', 'Answers that name you, weighted by the monthly Google search volume behind each prompt — potential exposure, not measured reach.', h('span', { cls: 'g-head-actions' }, srcLabel('dfs', 'search volume'), h('span', { cls: 'tag-src', text: 'Modeled' }))));
  if (!imp) impC.append(h('p', { cls: 'def', text: 'Appears once prompt search volumes are known (needs the SEO data provider). Volumes refresh monthly on each run.' }));
  else {
    impC.append(h('div', { cls: 'mgrid' }, metric('Your impressions', null, fmt.compact(imp.brand), null, [h('span', { text: 'of' }), h('b', { text: fmt.compact(imp.possible) }), h('span', { text: 'possible' })]),
      metric('Impression share', null, fmt.pct(imp.share), null, null)));
    impC.append(h('div', { cls: 'sov', style: { marginTop: '14px' } }, imp.entities.map((e) => h('div', { cls: 'sov-row' + (e.is_brand ? ' you' : '') },
      h('span', { cls: 'nm' }, h('span', { cls: 'dot' }), e.name), h('span', { cls: 'track' }, h('i', { style: { width: e.share + '%' } })), h('span', { cls: 'v', text: fmt.pct(e.share) })))));
  }
  const tr = S.panels.traffic && S.panels.traffic.tag === 'Success' ? S.panels.traffic.data : null;
  aiTrafC.append(pnlHead('AI traffic', 'Real visits from AI assistants (referrer or utm_source), measured by your tracking script or Google Analytics 4.', tr && tr.demo ? sourceTag(tr) : h('span', { cls: 'tag-src', text: 'Measured' + (tr && tr.source === 'ga4' ? ' · Google Analytics 4' : tr && tr.connected ? ' · tracking script' : '') })));
  if (!tr || !tr.connected || !tr.ai_traffic) aiTrafC.append(h('p', { cls: 'def', text: 'Install the X Platforms tracking script or connect Google Analytics 4 to see visits from ChatGPT, Perplexity, Gemini and others.' }));
  else {
    const a = tr.ai_traffic, max = Math.max(1, ...a.platforms.map((p) => p.visits));
    aiTrafC.append(metric('AI-referred visits', null, fmt.compact(a.visits.value), change(a.visits.delta), [h('span', { text: S.periodInfo ? S.periodInfo.label : '' })]));
    aiTrafC.append(h('div', { cls: 'mini-list', style: { marginTop: '12px' } }, a.platforms.slice(0, 5).map((p) => h('div', { cls: 'row' },
      h('span', { cls: 't', text: p.platform }), h('b', { cls: 'num', text: fmt.compact(p.visits) }), h('span', { cls: 'bar' }, h('i', { style: { width: p.visits / max * 100 + '%', background: YOU } }))))));
  }
  tagC.append(pnlHead('By prompt tag', 'Mention rate for each tag in the latest run. Tag prompts by topic, funnel stage or campaign.', srcLabel('ai')));
  tagC.append(d.tags.length ? h('div', { cls: 'mini-list' }, d.tags.map((t) => h('div', { cls: 'row' }, h('span', { cls: 't', text: '#' + t.tag }), h('b', { cls: 'num', text: t.mentions + '/' + t.answers }),
    h('span', { cls: 'bar' }, h('i', { style: { width: t.rate + '%', background: '#141417' } })))))
    : h('p', { cls: 'def', text: 'No tagged prompts yet. Add tags when you add a prompt.' }));
  mc.append(pnlHead('Mentions vs citations', 'Monthly totals across your runs.', srcLabel('ai')),
    h('p', { cls: 'def' }, h('b', { text: 'Mentions' }), ' — your brand name appears in an AI answer. ', h('b', { text: 'Citations' }), ' — the answer links to your pages as a source.'),
    columns(d.monthly.map((m) => ({ label: monthLabel(m.month), mentions: m.mentions, citations: m.citations })), [{ key: 'mentions', color: '#141417' }, { key: 'citations', color: YOU }],
      { height: 180, labels: { mentions: 'Mentions', citations: 'Citations' } }));
  bot.append(pnlHead('Mentions by brand', 'How often each tracked brand is named, run by run.', srcLabel('ai')),
    multiLine(d.brands_over_time.map((b, i) => ({ name: b.you ? b.name + ' (you)' : b.name, color: b.you ? YOU : PALETTE[i % PALETTE.length], bold: b.you,
      points: b.series.map((p) => ({ date: p.date, value: p.mentions })) })), { height: 200, fmtV: (v) => String(v) }));
  pagesC.append(pnlHead('Top-cited pages', 'Your pages AI answers link to, and the average position among cited sources.', srcLabel('ai')),
    table(['Page', 'Cited in', 'Avg pos.'], d.cited_pages.map((p) => h('tr', null, td(h('span', { cls: 'truncate', style: { maxWidth: '200px' }, title: p.url, text: shortUrl(p.url) })), tdn(p.cited_in), tdn('#' + p.avg_position))), { right: [1, 2] }));
  domainsC.append(pnlHead('Top-cited domains', `Domains most often cited in answers that mention ${d.brand}.`, srcLabel('ai')),
    table(['Domain', 'Answers'], d.cited_domains.map((r) => h('tr', null, td([r.domain, r.kind === 'you' ? h('span', { cls: 'chip-you', text: 'you' }) : null]), tdn(r.answers))), { right: [1] }));
  topicsC.append(pnlHead('Topics', `Topics where AI mentions ${d.brand} most (latest run).`, srcLabel('ai')),
    h('div', { cls: 'mini-list' }, d.topics.map((t) => h('div', { cls: 'row' }, h('span', { cls: 't', text: t.topic }), h('b', { cls: 'num', text: t.mentions + '/' + t.answers }), h('span', { cls: 'bar' }, h('i', { style: { width: t.rate + '%', background: YOU } }))))));
  gapC.append(pnlHead('Mention gaps', 'Prompts where a competitor is named and you aren’t — your clearest content opportunities.', srcLabel('ai')),
    d.gaps.length ? h('div', null, d.gaps.map((g) => h('div', { cls: 'gap-row' }, h('span', null, g.prompt, g.topic ? h('span', { cls: 'chip', style: { marginLeft: '8px' }, text: g.topic }) : null),
      h('span', null, g.competitors.map((x) => h('span', { cls: 'chip-comp', text: x })))))) : h('p', { cls: 'def', text: 'No gaps in the latest run — every answer naming a competitor also named you.' }));
  const s = d.sentiment, total = s.analyzed || 1;
  brandsC.append(pnlHead('Most mentioned brands', 'Every brand the latest answers named — including ones you don’t track yet.', srcLabel('ai')),
    d.most_mentioned.length ? h('div', { cls: 'mini-list' }, d.most_mentioned.map((b) => h('div', { cls: 'row' }, h('span', { cls: 't' }, b.name, b.kind === 'you' ? h('span', { cls: 'chip-you', text: 'you' }) : b.kind === 'competitor' ? h('span', { cls: 'chip-comp', text: 'tracked' }) : null),
      h('b', { cls: 'num', text: b.answers }), h('span', { cls: 'bar' }, h('i', { style: { width: b.answers / Math.max(1, d.latest.answers) * 100 + '%', background: b.kind === 'you' ? YOU : '#141417' } })))))
      : h('p', { cls: 'def', text: 'Brand extraction runs from the next visibility check.' }),
    h('div', { cls: 'side-h', style: { marginTop: '18px' }, text: 'How AI describes ' + d.brand }),
    s.analyzed ? h('div', null, h('div', { cls: 'sent' }, h('i', { style: { width: s.positive / total * 100 + '%', background: '#1f8a5b' } }), h('i', { style: { width: s.neutral / total * 100 + '%', background: '#d8d5cd' } }), h('i', { style: { width: s.negative / total * 100 + '%', background: '#c8473f' } })),
      h('div', { cls: 'legend' }, h('span', null, h('i', { style: { background: '#1f8a5b' } }), 'Positive ' + s.positive), h('span', null, h('i', { style: { background: '#d8d5cd' } }), 'Neutral ' + s.neutral), h('span', null, h('i', { style: { background: '#c8473f' } }), 'Negative ' + s.negative)))
      : h('p', { cls: 'def', text: 'Sentiment is classified from the next visibility check.' }));
}

// ------------------------------------------------------------------- audit
function renderAudit() {
  const state = $('auditState'), wrap = $('auditWrap');
  state.innerHTML = '';
  switch (S.scan.tag) {
    case 'NotAsked':
      wrap.hidden = true;
      state.appendChild(stateCard(ICONS.scan, 'No audit yet', 'Enter a website above and run an audit. Add a competitor to see exactly where they beat you.'));
      return;
    case 'Loading':
      wrap.hidden = true;
      state.appendChild(progressCard(S.scan.meta));
      return;
    case 'Failure': {
      wrap.hidden = true;
      const ex = explain(S.scan.error);
      state.appendChild(stateCard(ICONS.alert, 'The audit could not finish', ex.msg, ex.action || h('button', { cls: 'btn btn-primary', type: 'button', on: { click: () => $('scanBtn').click() }, text: 'Try again' })));
      return;
    }
    case 'Success': {
      const { scan, report } = S.scan.data;
      wrap.hidden = false;
      $('auditTitle').textContent = scan.kind === 'comparison' ? 'Competitor comparison' : 'Site audit';
      const meta = $('auditMeta');
      meta.innerHTML = '';
      meta.append(h('span', { text: fmt.host(scan.url) + (scan.competitor_url ? ' vs ' + fmt.host(scan.competitor_url) : '') }),
        h('span', { text: '·' }), h('span', { text: fmt.date(scan.finished_at) }),
        h('span', { cls: 'badge ' + (scan.mode === 'premium' ? 'badge-plan' : scan.mode === 'sample' ? 'badge-warn' : 'badge-preview'),
          text: scan.mode === 'premium' ? 'Premium report' : scan.mode === 'sample' ? 'Sample report' : 'Preview report' }));
      $('auditUpsell').hidden = scan.mode !== 'preview';
      wireAuditActions(scan);
      if (S.renderedReport !== scan.uuid && report) {
        S.renderedReport = scan.uuid;
        if (scan.kind === 'comparison') miraRenderComparison(report); else miraRenderSiteReport(report);
      }
    }
  }
}
// Report header buttons: a signed read-only link (Share), the same page in print mode (PDF), and a fresh audit.
function wireAuditActions(scan) {
  const busy = (btn, on) => { btn.disabled = on; btn.classList.toggle('loading', on); };
  $('auditShare').onclick = async (e) => {
    const btn = e.currentTarget;
    busy(btn, true);
    try {
      const { url, valid_until } = await api('shareScan', { id: scan.id });
      let copied = false;
      try { await navigator.clipboard.writeText(url); copied = true; } catch (err) { copied = false; }
      const open = h('a', { cls: 'btn btn-ghost btn-sm', href: url, target: '_blank', rel: 'noopener', text: 'Open' });
      toast((copied ? 'Link copied. ' : 'Share this link: ' + url + ' — ') + 'Anyone with it can view this report until ' + fmt.date(valid_until) + '.', { action: open });
    } catch (err) { toast(explain(err).msg, { error: true }); }
    finally { busy(btn, false); }
  };
  $('auditPdf').onclick = async (e) => {
    const btn = e.currentTarget;
    // Open the tab now (inside the click) so it isn't blocked as a pop-up, then point it at the report.
    const tab = window.open('', '_blank');
    busy(btn, true);
    try {
      const { pdf_url } = await api('shareScan', { id: scan.id });
      if (tab) tab.location = pdf_url; else location.href = pdf_url;
    } catch (err) { if (tab) tab.close(); toast(explain(err).msg, { error: true }); }
    finally { busy(btn, false); }
  };
  $('auditRerun').onclick = () => {
    $('scanUrl').value = scan.url;
    if (scan.competitor_url && !S.compare) $('compToggle').click();
    if (scan.competitor_url) $('scanComp').value = scan.competitor_url;
    S.freshScan = true;   // read once by submitScan
    $('scanForm').requestSubmit();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };
}
function stateCard(iconPath, title, text, action) {
  return h('div', { cls: 'card state' }, h('span', { cls: 'icon' }, icon(iconPath)), h('h3', { text: title }), h('p', { text: text }), action || null);
}
function progressCard(meta) {
  const elapsed = Math.round((Date.now() - (meta.started || Date.now())) / 1000);
  const steps = ['Fetching the page', 'Running 13 check categories', 'Measuring speed', meta.competitor ? 'Checking the competitor' : (premium() ? 'Writing fixes for your site' : 'Scoring')];
  const at = Math.min(steps.length - 1, Math.floor(elapsed / 8));
  return h('div', { cls: 'card state' },
    h('h3', { text: 'Auditing ' + fmt.host(meta.url) }),
    h('div', { cls: 'progress' }, h('div', { cls: 'track' }, h('i')),
      h('div', { cls: 'steps' }, steps.map((s, i) => h('span', { cls: i < at ? 'done' : i === at ? 'on' : '', text: (i < at ? '✓ ' : '') + s })))),
    h('p', { text: 'This usually takes 15–60 seconds. You can keep working — we’ll let you know.' }));
}

// ------------------------------------------------------------- visibility
// ------------------------------------------------------------ rank tracking
// Keyword difficulty colours: green (easy) → red (very hard), one per band.
const KD_COLORS = ['#2e9e5b', '#62b35a', '#c9a227', '#e58a2b', '#d6582f', '#b3261e'];
const KD_MINS = [0, 15, 30, 50, 70, 85];
function kdColor(v) { if (v === null || v === undefined) return '#bdbab2'; let i = 0; KD_MINS.forEach((m, k) => { if (v >= m) i = k; }); return KD_COLORS[i]; }
function kdBand(bands, v) { let l = ''; (bands || []).forEach((b) => { if (v >= b.min) l = b.label; }); return l; }
function kdChip(v, band) {
  return v === null || v === undefined ? h('span', { cls: 'muted', text: '—' })
    : h('span', { cls: 'kd-chip', title: band || '', style: { background: kdColor(v) } }, h('b', { text: String(v) }), band ? ' ' + band : '');
}
function moveTag(v) {
  if (v === null || v === undefined) return h('span', { cls: 'muted', text: '—' });
  if (v === 0) return h('span', { cls: 'delta flat', text: '±0' });
  return h('span', { cls: 'delta ' + (v > 0 ? 'up' : 'down'), title: v > 0 ? 'Moved up' : 'Moved down', text: (v > 0 ? '▲ ' : '▼ ') + Math.abs(v) });
}
// Position sparkline: up is better, gaps where the site was outside the top 100.
function posSpark(points) {
  const w = 90, hgt = 26, vals = points.map((p) => p.position).filter((v) => v);
  if (vals.length < 2) return h('span', { cls: 'muted', text: vals.length ? 'today only' : '—' });
  const max = Math.max(...vals), min = Math.min(...vals), span = Math.max(1, max - min);
  const x = (i) => 2 + i / Math.max(1, points.length - 1) * (w - 4);
  const y = (v) => 3 + (v - min) / span * (hgt - 6);
  const pts = points.map((p, i) => p.position ? x(i) + ',' + y(p.position) : null).filter(Boolean).join(' ');
  return h('svg', { viewBox: `0 0 ${w} ${hgt}`, style: { width: w + 'px', height: hgt + 'px' }, role: 'img', 'aria-label': 'Position over time' },
    h('polyline', { points: pts, fill: 'none', stroke: '#141417', 'stroke-width': 1.6, 'stroke-linejoin': 'round' }));
}
async function loadRanks(force) {
  const p = project();
  if (!premium() || !p) { renderRanks(); return; }
  if (!force && S.ranks.pid === p.id && S.ranks.tag === 'Success') { renderRanks(); return; }
  S.ranks = Object.assign(RD.loading(), { pid: p.id });
  renderRanks();
  try {
    const res = await api('ranks', { id: p.id });
    if (S.projectId !== p.id) return;
    S.ranks = Object.assign(RD.success(res.ranks), { pid: p.id });
  } catch (e) {
    if (S.projectId !== p.id) return;
    S.ranks = Object.assign(RD.failure(e), { pid: p.id });
  }
  renderRanks();
}
async function rankAction(btn, name, args, body, done) {
  const p = project();
  if (btn) { btn.disabled = true; btn.classList.add('loading'); }
  try {
    const res = await api(name, Object.assign({ id: p.id }, args || {}), body);
    S.ranks = Object.assign(RD.success(res.ranks), { pid: p.id });
    renderRanks();
    if (done) done(res);
  } catch (e) { toast(explain(e).msg, { error: true }); }
  finally { if (btn) { btn.disabled = false; btn.classList.remove('loading'); } }
}
function renderRanks() {
  const body = $('ranksBody');
  if (!body || S.tab !== 'ranks') return;
  if (gateReport(body, 'ranks', 'Know where you rank,', 'every day.', 'Track the Google position of the keywords that matter, see how hard each one is, and how your share of clicks compares with your competitors.',
    ['Daily Google positions for your chosen keywords', 'Keyword difficulty and search volume for every keyword', 'Visibility and share of voice against competitors', 'Movement since yesterday and last week'])) return;
  const st = S.ranks;
  const c = () => h('div', { cls: 'card pnl' });
  if (st.tag === 'NotAsked' || st.tag === 'Loading') { const k = c(); k.append(...skeletonPanel('Rank tracking')); body.append(k); return; }
  if (st.tag === 'Failure') {
    const k = c(); k.append(pnlHead('Rank tracking'), h('p', { cls: 'muted', text: explain(st.error).msg }),
      h('button', { cls: 'btn btn-secondary btn-sm', type: 'button', style: { marginTop: '12px' }, on: { click: () => loadRanks(true) }, text: 'Retry' }));
    body.append(k); return;
  }
  const d = st.data;
  if (d.connected === false) { const k = c(); k.append(...connectCard('Rank tracking', null, d, ['Visibility', 'Average position', 'Top 10'])); body.append(k); return; }
  const s = d.summary, me = d.domain;

  // Add keywords + check now.
  const addCard = c();
  const input = h('textarea', { id: 'rankInput', cls: 'rank-input', rows: 2, placeholder: 'Add keywords, one per line or separated by commas', 'aria-label': 'Keywords to track' });
  const addBtn = h('button', { cls: 'btn btn-primary btn-sm', type: 'button', text: 'Track keywords' });
  addBtn.addEventListener('click', () => {
    if (!input.value.trim()) { input.focus(); return; }
    rankAction(addBtn, 'rankAdd', null, { keywords: input.value }, (res) => toast(res.added + ' keyword' + (res.added === 1 ? '' : 's') + ' added and checked.'));
  });
  const checkBtn = h('button', { cls: 'btn btn-secondary btn-sm', type: 'button', text: 'Check now', title: 'Re-check keywords not checked in the last hour' });
  checkBtn.addEventListener('click', () => rankAction(checkBtn, 'rankCheck', null, null, (res) => toast(res.checked ? res.checked + ' keyword' + (res.checked === 1 ? '' : 's') + ' re-checked.' : 'Everything was checked in the last hour.')));
  addCard.append(pnlHead('Tracked keywords', 'Checked automatically once a day. Positions are Google ' + (d.location ? 'for location ' + d.location.location_code : '') + ', top 100.', sourceTag(d, 'DataForSEO SERP')),
    h('div', { cls: 'rank-add' }, input, h('div', { cls: 'rank-add-btns' }, addBtn, s.tracked ? checkBtn : null)),
    h('div', { cls: 'm-sub', style: { marginTop: '8px' }, text: fmt.num(s.tracked) + ' of ' + fmt.num(d.limit) + ' keywords on your plan' + (d.fetched_at ? ' · last checked ' + fmt.date(d.fetched_at) : '') }));
  if (d.suggestions && d.suggestions.length) {
    addCard.append(h('div', { cls: 'rank-sugs' }, h('span', { cls: 'm-sub', text: 'You already rank for:' }),
      d.suggestions.map((k) => h('button', { cls: 'chip rank-sug', type: 'button', title: 'Track this keyword', text: '+ ' + k,
        on: { click: (e) => rankAction(e.currentTarget, 'rankAdd', null, { keywords: k }) } }))));
  }
  body.append(addCard);
  if (!s.tracked) return;

  // Summary.
  const sum = c();
  sum.append(h('div', { cls: 'mgrid six' },
    metric('Visibility', 'Share of the clicks you would get if you ranked #1 for every tracked keyword', fmt.pct(s.visibility), null),
    metric('Average position', 'Across keywords in the top 100', s.avg_position === null ? '—' : String(s.avg_position), null),
    metric('Top 3', 'Keywords in positions 1–3', fmt.num(s.top3), null),
    metric('Top 10', 'Keywords on page 1', fmt.num(s.top10), null),
    metric('Ranking', 'Keywords in the top 100', fmt.num(s.ranking) + ' / ' + fmt.num(s.tracked), null),
    metric('Since yesterday', 'Keywords that moved up / down', '▲ ' + s.improved + '  ▼ ' + s.declined, null)));
  body.append(sum);

  // Share of voice + visibility trend.
  const sov = c(), trend = c();
  const maxShare = Math.max(1, ...d.share_of_voice.map((x) => x.share));
  sov.append(pnlHead('Share of voice', 'Each site\'s estimated clicks (search volume × click-through at its position) as a share of all tracked sites, on today\'s positions'),
    h('div', { cls: 'sov' }, d.share_of_voice.map((x, i) => h('div', { cls: 'sov-row' + (x.domain === me ? ' you' : '') },
      h('span', { cls: 'sov-name', text: x.domain + (x.domain === me ? ' (you)' : '') }),
      h('span', { cls: 'sov-bar' }, h('i', { style: { width: (x.share / maxShare * 100) + '%', background: x.domain === me ? YOU : PALETTE[i % PALETTE.length] } })),
      h('span', { cls: 'num', text: fmt.pct(x.share) })))));
  if (d.domains.length < 2) sov.append(h('p', { cls: 'def', style: { marginTop: '10px' }, text: 'Add competitor websites to the project to compare share of voice.' }));
  trend.append(pnlHead('Visibility over time', 'Daily visibility for you and each competitor'));
  trend.append(d.trend.length > 1
    ? multiLine(d.domains.map((dom, i) => ({ name: dom === me ? dom + ' (you)' : dom, color: dom === me ? YOU : PALETTE[i % PALETTE.length], bold: dom === me,
        points: d.trend.map((p) => ({ date: p.date, value: p[dom] })) })), { height: 200, daily: true, fmtV: (v) => fmt.pct(v) })
    : h('p', { cls: 'muted', text: 'The trend appears after the second daily check.' }));
  body.append(h('div', { cls: 'rep-grid' }, sov, trend));

  // Keyword table.
  const tbl = h('div', { cls: 'card pnl rank-table' });
  const comps = d.domains.filter((x) => x !== me);
  tbl.append(pnlHead('Positions', 'Today\'s Google position, movement, best position in the last 30 days and the page that ranks'),
    table(['Keyword', 'Difficulty', 'Volume', 'Position', '1 day', '7 days', 'Best', 'Last 30 days', ...comps.map((x) => x.split('.')[0]), ''], d.keywords.map((k) => h('tr', { title: k.url || '' },
      td(h('span', null, h('b', { text: k.keyword }), k.url ? h('small', { cls: 'rank-url', text: shortUrl(k.url) }) : null, k.intent ? h('small', { cls: 'rank-url', text: k.intent }) : null)),
      h('td', null, kdChip(k.difficulty, k.band)), tdn(fmt.compact(k.volume)),
      h('td', { cls: 'r' }, h('b', { cls: 'num', text: k.position ?? '100+' })), h('td', { cls: 'r' }, moveTag(k.change_1d)), h('td', { cls: 'r' }, moveTag(k.change_7d)),
      tdn(k.best ?? '—'), h('td', null, posSpark(k.history)),
      ...comps.map((x) => tdn(k.competitors && k.competitors[x] ? k.competitors[x] : '—')),
      h('td', { cls: 'r' }, h('button', { cls: 'btn btn-ghost btn-sm', type: 'button', title: 'Stop tracking', 'aria-label': 'Stop tracking ' + k.keyword, text: '×',
        on: { click: (e) => rankAction(e.currentTarget, 'rankDelete', { kw: k.id }) } })))),
      { right: [2, 3, 4, 5, 6, ...comps.map((_, i) => 8 + i)] }));
  body.append(tbl);
}

function lockedFeature(from, title, em, text, bullets, preview) {
  return h('div', { cls: 'locked-card' },
    h('div', null,
      h('span', { cls: 'badge badge-lock' }, icon(ICONS.lock), 'Mira Premium'),
      h('h3', null, title + ' ', h('em', { text: em })),
      h('p', { text: text }),
      h('ul', null, bullets.map((b) => h('li', null, icon(ICONS.check), b))),
      h('div', { cls: 'cta' }, upgradeLink(from, S.sub.status === 'active_trial' ? 'Subscribe' : 'Unlock Premium'),
        S.sub.signed_in ? null : h('a', { cls: 'btn btn-secondary', href: LOGIN_URL, text: 'Sign in' }))),
    preview);
}
function sampleSov() {
  const rows = [['Your brand', 34, true], ['Competitor A', 41], ['Competitor B', 25]];
  return h('div', { cls: 'locked-preview' }, h('div', { cls: 'side-h', text: 'Share of voice · sample' }),
    h('div', { cls: 'sov' }, rows.map(([n, v, you]) => h('div', { cls: 'sov-row' + (you ? ' you' : '') },
      h('span', { cls: 'nm' }, h('span', { cls: 'dot' }), n), h('span', { cls: 'track' }, h('i', { style: { width: v + '%' } })), h('span', { cls: 'v', text: v + '%' })))));
}

async function loadVisibility(force) {
  const body = $('visBody');
  if (!premium()) {
    track('locked_view', { feature: 'visibility' });
    body.innerHTML = '';
    body.appendChild(lockedFeature('visibility', 'See how often AI recommends', 'you.',
      'Mira asks the questions your customers ask AI assistants, then measures whether your brand is named, cited and ranked — against the competitors you choose.',
      ['Prompt sets built for your market, plus your own questions', 'Mentions, citations and average position per run', 'Share of voice vs competitors, tracked over time', 'Which domains AI cites instead of you'], sampleSov()));
    return;
  }
  const p = project();
  if (!p) {
    body.innerHTML = '';
    body.appendChild(stateCard(ICONS.chart, 'Create a project to track AI visibility', 'A project is your website plus the competitors you want to be measured against.',
      h('button', { cls: 'btn btn-primary', type: 'button', on: { click: newProjectFlow }, text: 'New project' })));
    return;
  }
  if (S.vis.tag !== 'Success' || force) {
    if (S.vis.tag !== 'Success') { S.vis = RD.loading(); renderVisibility(); }
    try { S.vis = RD.success(await api('visibility', { id: p.id })); }
    catch (e) { S.vis = RD.failure(e); }
  }
  renderVisibility();
  loadReport('ai_brand', !!force);
  const active = S.vis.tag === 'Success' && S.vis.data.report.active;
  clearTimeout(S.visPoll);
  if (active) S.visPoll = setTimeout(() => loadVisibility(true), 3000);
}

function renderVisibility() {
  const body = $('visBody');
  body.innerHTML = '';
  if (S.vis.tag === 'Loading' || S.vis.tag === 'NotAsked') {
    body.appendChild(h('div', { cls: 'grid kpis' }, [0, 1, 2, 3].map(() => h('div', { cls: 'card kpi' }, h('div', { cls: 'skeleton', style: { height: '86px' } })))));
    return;
  }
  if (S.vis.tag === 'Failure') {
    const ex = explain(S.vis.error);
    body.appendChild(stateCard(ICONS.alert, 'Could not load AI visibility', ex.msg, ex.action));
    return;
  }
  const { prompts, report, caps } = S.vis.data;
  const p = project();
  const latest = report.latest, sum = latest && latest.summary;

  // KPIs
  body.appendChild(h('div', { cls: 'grid kpis' },
    kpiTile('Mentions', latest ? latest.mentions : '—', latest ? 'of ' + latest.answered_count + ' answers' : 'No run yet', deltaEl(latest && latest.new_mentions), 'Answers that name your brand'),
    kpiTile('Share of voice', sum && sum.brand.share !== null ? fmt.num(Math.round(sum.brand.share)) : '—', sum && sum.brand.share !== null ? '% of tracked-brand mentions' : 'Add competitors to compare', deltaEl(latest && latest.share_change, { suffix: ' pts' }), 'Your mentions ÷ all tracked brands’ mentions'),
    kpiTile('Avg position', sum && sum.brand.avg_position ? '#' + sum.brand.avg_position : '—', 'Order named among tracked brands', null, '1 = named first'),
    kpiTile('Citations', latest ? latest.citations : '—', 'Answers linking ' + p.domain, null, 'Answers that cite your own domain')));
  body.appendChild(h('div', { id: 'brandRadar' }));

  // Run controls
  const run = report.active;
  const runBtn = h('button', { cls: 'btn btn-primary', type: 'button', disabled: !!run || !prompts.some((x) => x.is_active), on: { click: startRun } }, icon(ICONS.spark), run ? 'Running…' : 'Run visibility check');
  const vu = usageOf('visibility_runs');
  body.appendChild(h('div', { cls: 'card', style: { marginTop: '14px' } },
    h('div', { cls: 'card-hd' }, h('div', null, h('h3', { text: 'Prompt set · ' + prompts.filter((x) => x.is_active).length + ' active' }),
      h('span', { cls: 'sub', text: `${prompts.length} of ${caps.prompts} prompts · up to ${caps.prompts_per_run} answered per run · ${Math.max(0, vu.limit - vu.used)} runs left this month` }),
        h('div', { style: { marginTop: '6px' } }, srcLabel('dfs', 'keyword volumes'))),
      h('div', { style: { display: 'flex', gap: '8px', flexWrap: 'wrap', alignItems: 'center' } }, scheduleControl(p),
        h('button', { cls: 'btn btn-secondary', type: 'button', on: { click: suggestPrompts } }, icon(ICONS.spark), 'Suggest prompts'), runBtn)),
    run ? h('div', { cls: 'card-bd' }, h('div', { cls: 'progress', style: { width: '100%', justifyItems: 'start' } }, h('div', { cls: 'track' }, h('i')),
      h('span', { cls: 'muted', style: { fontSize: '13px' }, text: run.status === 'queued' ? 'Starting…' : `Asking the AI engine · ${run.progress || 0} of ${run.prompts_count} prompts answered` }))) : null));

  const grid = h('div', { cls: 'vis-grid' });
  // Left: brand + competitors, prompts
  grid.appendChild(h('div', { cls: 'grid', style: { alignContent: 'start' } }, brandEditor(p), promptManager(prompts, caps)));
  // Right: results
  const right = h('div', { cls: 'grid', style: { alignContent: 'start' } });
  if (!latest) {
    right.appendChild(stateCard(ICONS.chart, 'No visibility data yet', prompts.length ? 'Run a visibility check to see how AI answers your prompts.' : 'Suggest prompts or add your own, then run a check.'));
  } else {
    right.appendChild(h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Share of voice' }), h('span', { cls: 'g-head-actions' }, h('span', { cls: 'sub', text: fmt.ago(latest.finished_at) }), srcLabel('ai'))),
      h('div', { cls: 'card-bd' }, h('div', { cls: 'sov' }, sum.entities.map((e) => h('div', { cls: 'sov-row' + (e.is_brand ? ' you' : '') },
        h('span', { cls: 'nm', title: e.name }, h('span', { cls: 'dot' }), e.name), h('span', { cls: 'track' }, h('i', { style: { width: (e.share || 0) + '%' } })),
        h('span', { cls: 'v', text: e.share === null ? '—' : fmt.pct(e.share) })))),
        report.timeline.length > 1 ? h('div', { style: { marginTop: '18px' } }, h('div', { cls: 'side-h', text: 'Mentions per run' }), lineChart(report.timeline.map((t) => ({ v: t.answered ? Math.round(t.mentions / t.answered * 100) : 0 })), 'v', { h: 120 })) : null)));
    right.appendChild(h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Domains AI cites' }), srcLabel('ai')),
      h('div', { cls: 'card-bd' }, sum.top_domains.length ? h('table', { cls: 'table' }, h('thead', null, h('tr', null, h('th', { text: 'Domain' }), h('th', { cls: 'r', text: 'Answers' }))),
        h('tbody', null, sum.top_domains.map((d) => h('tr', null, h('td', null, d.domain, ' ', d.kind !== 'other' ? h('span', { cls: 'badge ' + (d.kind === 'you' ? 'badge-plan' : 'badge-info'), text: d.kind === 'you' ? 'you' : 'competitor' }) : null), h('td', { cls: 'r num', text: d.count })))))
        : h('p', { cls: 'muted', text: 'No sources were cited in this run.' }))));
    right.appendChild(h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Coverage by topic' }), srcLabel('ai')),
      h('div', { cls: 'card-bd' }, h('div', { cls: 'stages' }, sum.topics.map((t) => h('div', { cls: 'stage-row' },
        h('span', { cls: 'lbl', text: t.topic }), h('span', { cls: 'track' }, h('i', { cls: 'tone-' + tone(t.rate), style: { width: t.rate + '%' } })),
        h('span', { cls: 'val', text: t.mentions + '/' + t.prompts })))))));
    right.appendChild(answersCard(latest, p));
  }
  grid.appendChild(right);
  body.appendChild(grid);
  renderBrandRadar();
  body.appendChild(h('div', { cls: 'note', style: { marginTop: '14px' } }, icon(ICONS.info),
    h('span', { text: `Modeled estimate. Answers are generated by ${report.model} without live web access, vary between runs, and cover only the prompts in your set — so read the trend, not any single number.` })));
}
function kpiTile(label, value, sub, delta, hint) {
  return h('div', { cls: 'card kpi' }, h('div', { cls: 'kpi-lbl' }, label, h('span', { cls: 'i', title: hint, text: 'i' })),
    h('div', { cls: 'kpi-val' }, h('span', { cls: 'big num', text: value }), delta), h('div', { cls: 'kpi-sub' }, h('span', { text: sub })));
}
function answersCard(latest, p) {
  const terms = (p.brand_terms || []).concat(p.competitors.map((c) => c.name)).filter((t) => t && t.length > 1)
    .map((t) => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
  const re = terms.length ? new RegExp('(' + terms.join('|') + ')', 'gi') : null;
  const highlight = (text) => {
    const box = h('div', { cls: 'body' });
    if (!re) { box.textContent = text; return box; }
    let last = 0; text.replace(re, (m, _g, off) => { box.append(text.slice(last, off), h('mark', { text: m })); last = off + m.length; return m; });
    box.append(text.slice(last));
    return box;
  };
  return h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Answers' }), h('span', { cls: 'g-head-actions' }, h('span', { cls: 'sub', text: 'What AI said, prompt by prompt' }), srcLabel('ai'))),
    h('div', { cls: 'card-bd' }, latest.answers.map((a) => h('details', { cls: 'answer' },
      h('summary', null, h('span', { cls: 'flag ' + (a.brand_mentioned ? 'yes' : 'no'), text: a.brand_mentioned ? '#' + a.brand_position : '–' }),
        h('span', null, a.prompt, h('div', { cls: 'chips', style: { marginTop: '6px' } }, a.topic ? h('span', { cls: 'chip', text: a.topic }) : null,
          a.brands_mentioned.map((b) => h('span', { cls: 'chip' + (b === p.brand_terms[0] ? ' you' : ''), text: b })), a.brand_cited ? h('span', { cls: 'chip you', text: 'cites you' }) : null)),
        h('span', { cls: 'muted', style: { fontSize: '12px' }, text: a.error ? 'no answer' : 'view' })),
      a.error ? h('p', { cls: 'muted', style: { marginTop: '10px' }, text: a.error }) : highlight(a.answer || '')))));
}
// Off / daily / weekly / monthly runs of the prompt set (artisan mira:visibility-scheduled, hourly).
function scheduleControl(p) {
  const sel = h('select', { 'aria-label': 'Run schedule', style: { height: '40px', border: '1px solid var(--line-2)', borderRadius: '999px', background: 'var(--card)', padding: '0 14px', fontSize: '13px', fontWeight: '600' } },
    [['off', 'Manual runs'], ['daily', 'Run daily'], ['weekly', 'Run weekly'], ['monthly', 'Run monthly']].map(([v, l]) => h('option', { value: v, text: l, selected: (p.visibility_schedule || 'off') === v ? 'selected' : null })));
  sel.addEventListener('change', async () => {
    try {
      const { project: updated } = await api('updateProject', { id: p.id }, { visibility_schedule: sel.value });
      upsertProject(updated);
      toast(sel.value === 'off' ? 'Scheduled runs turned off.' : `Runs ${sel.value} — first one within the hour. Each run uses one AI visibility run from your plan.`);
      renderVisibility();
    } catch (e) { const ex = explain(e); toast(ex.msg, { error: true, action: ex.action }); sel.value = p.visibility_schedule || 'off'; }
  });
  const next = p.visibility_schedule && p.visibility_schedule !== 'off' && p.next_visibility_run_at
    ? h('span', { cls: 'muted', style: { fontSize: '12px' }, text: 'Next ' + fmt.ago(p.next_visibility_run_at) }) : null;
  return h('span', { style: { display: 'inline-flex', alignItems: 'center', gap: '8px' } }, sel, next);
}
function brandEditor(p) {
  const terms = h('input', { value: p.brand_terms.join(', '), placeholder: 'Acme, Acme Analytics' });
  const excludes = h('input', { value: (p.exclude_terms || []).join(', '), placeholder: 'e.g. apple pie, apple juice' });
  let caseSensitive = p.match_mode === 'case_sensitive';
  const caseBtn = h('button', { cls: 'switch', type: 'button', role: 'switch', 'aria-checked': String(caseSensitive), 'aria-label': 'Match brand case-sensitively' });
  caseBtn.addEventListener('click', () => { caseSensitive = !caseSensitive; caseBtn.setAttribute('aria-checked', String(caseSensitive)); });
  const rows = h('div', { cls: 'grid', style: { gap: '8px' } });
  const addRow = (c) => {
    const n = h('input', { value: c ? c.name : '', placeholder: 'Competitor name' });
    const d = h('input', { value: c ? c.domain : '', placeholder: 'competitor.com' });
    const row = h('div', { cls: 'comp-row' }, h('label', { cls: 'field' }, n), h('label', { cls: 'field' }, d),
      h('button', { cls: 'btn btn-ghost btn-sm', type: 'button', 'aria-label': 'Remove competitor', on: { click: () => row.remove() } }, icon(ICONS.trash)));
    row._v = () => ({ name: n.value.trim(), domain: d.value.trim() });
    rows.appendChild(row);
  };
  p.competitors.forEach(addRow);
  if (!p.competitors.length) addRow(null);
  const save = h('button', { cls: 'btn btn-primary btn-sm', type: 'button' }, 'Save');
  save.addEventListener('click', async () => {
    setBusy(save, true);
    try {
      const competitors = [...rows.children].map((r) => r._v()).filter((c) => c.name.length >= 2);
      const brand_terms = terms.value.split(',').map((t) => t.trim()).filter((t) => t.length >= 2);
      const exclude_terms = excludes.value.split(',').map((t) => t.trim()).filter((t) => t.length >= 2);
      const { project: updated } = await api('updateProject', { id: p.id }, { brand_terms, competitors, exclude_terms, match_mode: caseSensitive ? 'case_sensitive' : 'word' });
      upsertProject(updated);
      toast('Brand and competitors saved. They apply from the next run.');
      renderVisibility();
    } catch (e) { const ex = explain(e); toast(ex.msg, { error: true, action: ex.action }); }
    finally { setBusy(save, false); }
  });
  return h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Brand & competitors' }), h('span', { cls: 'sub', text: `up to ${S.caps.competitors} competitors` })),
    h('div', { cls: 'card-bd editor' },
      h('label', null, 'How your brand is named', h('span', { cls: 'field' }, terms)),
      h('div', { style: { display: 'flex', alignItems: 'center', gap: '10px', fontSize: '13px' } }, caseBtn,
        h('span', null, h('b', { text: 'Exact case only' }), h('span', { cls: 'muted', text: ' — count "Apple", not "apple"' }))),
      h('label', null, 'Never count these phrases', h('span', { cls: 'field' }, excludes)),
      h('div', null, h('div', { cls: 'side-h', text: 'Competitors' }), rows),
      h('div', { style: { display: 'flex', gap: '8px', justifyContent: 'space-between' } },
        h('button', { cls: 'btn btn-ghost btn-sm', type: 'button', on: { click: () => { if (rows.children.length < S.caps.competitors) addRow(null); else toast(`Your plan tracks up to ${S.caps.competitors} competitors.`); } } }, icon(ICONS.plus), 'Add competitor'), save)));
}
function promptManager(prompts, caps) {
  const p = project();
  const list = h('div', { cls: 'prompt-list' });
  if (!prompts.length) list.appendChild(h('p', { cls: 'muted', style: { padding: '8px 0' }, text: 'No prompts yet. Suggest a set for your market, or add the questions your customers ask.' }));
  prompts.forEach((pr) => {
    const sw = h('button', { cls: 'switch', type: 'button', role: 'switch', 'aria-checked': String(pr.is_active), 'aria-label': 'Include in runs' });
    sw.addEventListener('click', async () => {
      try { const { prompt } = await api('updatePrompt', { id: pr.id }, { is_active: !pr.is_active }); Object.assign(pr, prompt); renderVisibility(); }
      catch (e) { toast(explain(e).msg, { error: true }); }
    });
    list.appendChild(h('div', { cls: 'prompt' + (pr.is_active ? '' : ' off') }, sw,
      h('div', null, pr.prompt, h('div', { cls: 'meta', style: { flexWrap: 'wrap' } },
        pr.topic ? h('span', { cls: 'chip', text: pr.topic }) : null,
        pr.source === 'seed' ? h('span', { cls: 'chip', text: 'suggested' }) : null,
        pr.source === 'sample' ? h('span', { cls: 'chip', style: { background: 'var(--warn-soft)', color: 'var(--warn)' }, text: 'sample' }) : null,
        (pr.tags || []).map((t) => h('span', { cls: 'chip you', text: '#' + t })),
        pr.keyword ? h('span', { cls: 'chip', title: 'Search keyword behind this prompt — weights Estimated impressions', text: pr.keyword + (pr.search_volume !== null && pr.search_volume !== undefined ? ' · ' + fmt.compact(pr.search_volume) + '/mo' : '') }) : null)),
      h('button', { cls: 'btn btn-ghost btn-sm', type: 'button', 'aria-label': 'Delete prompt', on: { click: async () => {
        try { await api('deletePrompt', { id: pr.id }); S.vis.data.prompts = S.vis.data.prompts.filter((x) => x.id !== pr.id); renderVisibility(); }
        catch (e) { toast(explain(e).msg, { error: true }); }
      } } }, icon(ICONS.trash))));
  });
  const input = h('input', { placeholder: 'e.g. What is the best analytics tool for small shops?', maxlength: '300' });
  const topic = h('select', { 'aria-label': 'Topic' }, h('option', { value: '', text: 'Topic' }), ['Discovery', 'Comparison', 'Pricing', 'Trust', 'How-to', 'Local'].map((t) => h('option', { value: t, text: t })));
  const add = h('button', { cls: 'btn btn-secondary', type: 'submit' }, icon(ICONS.plus), 'Add');
  const tagsIn = h('input', { placeholder: 'tags, comma separated', maxlength: '120', style: { maxWidth: '170px' }, 'aria-label': 'Tags' });
  const form = h('form', { cls: 'add-row', style: { flexWrap: 'wrap' } }, h('label', { cls: 'field', style: { minWidth: '240px' } }, input), h('label', { cls: 'field', style: { flex: '0 0 auto' } }, tagsIn), topic, add);
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (input.value.trim().length < 8) return;
    setBusy(add, true);
    try {
      const tags = tagsIn.value.split(',').map((t) => t.trim()).filter(Boolean);
      const { prompt } = await api('addPrompt', { id: p.id }, { prompt: input.value, topic: topic.value || null, tags });
      S.vis.data.prompts.push(prompt); track('prompt_add'); renderVisibility();
    } catch (err) { const ex = explain(err); toast(ex.msg, { error: true, action: ex.action }); }
    finally { setBusy(add, false); }
  });
  return h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Prompts' }), h('span', { cls: 'sub', text: prompts.length + ' / ' + caps.prompts })),
    h('div', { cls: 'card-bd' }, list, form));
}
async function suggestPrompts(e) {
  const btn = e.currentTarget, p = project();
  setBusy(btn, true);
  try {
    const { added } = await api('suggestPrompts', { id: p.id });
    S.vis.data.prompts.push(...added);
    track('prompts_suggest', { count: added.length });
    toast(added.length ? `${added.length} prompts added. Edit or switch off any that don’t fit.` : 'No new prompts to add.');
    renderVisibility();
  } catch (err) { const ex = explain(err); toast(ex.msg, { error: true, action: ex.action }); }
  finally { setBusy(btn, false); }
}
async function startRun(e) {
  const btn = e.currentTarget, p = project();
  setBusy(btn, true);
  try {
    await api('startRun', { id: p.id });
    track('visibility_run');
    await refreshMe();
    await loadVisibility(true);
  } catch (err) { const ex = explain(err); toast(ex.msg, { error: true, action: ex.action }); setBusy(btn, false); }
}

// ----------------------------------------------------------------- history
async function loadHistory(force) {
  const body = $('historyBody');
  if (!premium()) {
    track('locked_view', { feature: 'history' });
    body.innerHTML = '';
    body.appendChild(lockedFeature('history', 'Watch every score', 'move.',
      'Every audit is saved to its project, so you can prove progress and catch regressions the day they happen.',
      ['Score and open-issue trend per project', 'Change since the previous audit on every metric', 'Reopen any past report in one click'], null));
    return;
  }
  const p = project();
  if (!p) { body.innerHTML = ''; body.appendChild(stateCard(ICONS.chart, 'No project selected', 'Create a project and audits of its site are saved here.', h('button', { cls: 'btn btn-primary', type: 'button', on: { click: newProjectFlow }, text: 'New project' }))); return; }
  if (S.history.tag !== 'Success' || force) {
    try { S.history = RD.success(await api('history', { id: p.id })); } catch (e) { S.history = RD.failure(e); }
  }
  renderHistory();
  renderOverview();
}
function renderHistory() {
  const body = $('historyBody');
  if (S.tab !== 'history') return;
  body.innerHTML = '';
  if (S.history.tag === 'Failure') { body.appendChild(stateCard(ICONS.alert, 'Could not load history', explain(S.history.error).msg)); return; }
  if (S.history.tag !== 'Success') return;
  const { scans, timeline } = S.history.data;
  if (!scans.length) { body.appendChild(stateCard(ICONS.chart, 'No audits for ' + project().name + ' yet', 'Run an audit of ' + project().domain + ' and it is saved here.')); return; }
  body.appendChild(h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Site health over time' }), h('span', { cls: 'sub', text: timeline.length + ' audits' })),
    h('div', { cls: 'card-bd' }, lineChart(timeline, 'overall'))));
  body.appendChild(h('div', { cls: 'card', style: { marginTop: '14px' } }, h('div', { cls: 'card-bd table-wrap' }, h('table', { cls: 'table' },
    h('thead', null, h('tr', null, ['Date', 'Audit', 'Score', 'Change', 'Open issues', 'Status'].map((t, i) => h('th', { cls: i >= 2 && i <= 4 ? 'r' : null, text: t })))),
    h('tbody', null, scans.map((s) => h('tr', { cls: s.status === 'done' ? 'click' : null, on: { click: () => s.status === 'done' && openScan(s.id) } },
      h('td', { text: fmt.date(s.created_at) }),
      h('td', { cls: 'url', text: fmt.host(s.url) + (s.competitor_url ? ' vs ' + fmt.host(s.competitor_url) : '') }),
      h('td', { cls: 'r num c-' + tone(s.overall), text: s.overall ?? '—' }),
      h('td', { cls: 'r' }, deltaEl(s.delta.overall) || '—'),
      h('td', { cls: 'r num', text: s.open_issues ?? '—' }),
      h('td', null, s.mode === 'sample' ? h('span', { cls: 'badge badge-warn', text: 'sample' }) : h('span', { cls: 'badge ' + (s.status === 'done' ? 'badge-good' : s.status === 'failed' ? 'badge-bad' : 'badge-preview'), text: s.status })))))))));
}
async function openScan(id) {
  try { S.scan = RD.success(await api('showScan', { id })); setTab('audit'); renderOverview(); track('report_open', { from: 'history' }); }
  catch (e) { toast(explain(e).msg, { error: true }); }
}

// =================================================================== flows
// ----- scan: phase 1 submit → handle; phase 2 poll report until done/failed
let turnstileReady = null;
function loadTurnstile() {
  if (turnstileReady) return turnstileReady;
  turnstileReady = new Promise((resolve, reject) => {
    window.onMiraTurnstile = () => resolve(window.turnstile);
    const s = h('script', { src: 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=onMiraTurnstile', async: true, defer: true });
    s.onerror = () => reject(new ApiError('InvalidCaptcha', 'The human check could not load. Disable blockers and retry.'));
    document.head.appendChild(s);
  });
  return turnstileReady;
}
async function humanToken() {
  if (premium() || !S.turnstileKey) return null;
  if (S.turnstileToken) return S.turnstileToken;
  const ts = await loadTurnstile();
  return new Promise((resolve, reject) => {
    $('turnstileSlot').innerHTML = '';
    ts.render('#turnstileSlot', {
      sitekey: S.turnstileKey, action: 'mira_scan', theme: 'light', retry: 'never', 'response-field': false,
      callback: (token) => { S.turnstileToken = token; resolve(token); },
      'error-callback': () => reject(new ApiError('InvalidCaptcha', 'The human check failed. Please try again.')),
    });
  });
}

async function submitScan(e) {
  e.preventDefault();
  const btn = $('scanBtn'), msg = $('scanMsg');
  const url = $('scanUrl').value.trim(), competitor = S.compare ? $('scanComp').value.trim() : '';
  msg.textContent = ''; msg.className = '';
  if (!url) { msg.textContent = 'Enter a website first.'; msg.className = 'err'; $('scanUrl').focus(); return; }
  setBusy(btn, true);
  try {
    const token = await humanToken();
    // Subscribers: audits of a new site start a project automatically (while the plan has room).
    let projectId = S.projectId;
    if (premium() && S.sub.signed_in) {
      const host = fmt.host(url).split('/')[0].toLowerCase();
      const match = S.projects.find((p) => p.domain === host);
      if (match) projectId = match.id;
      else if (S.projects.length < S.caps.projects) {
        try { const { project: created } = await api('createProject', null, { url }); upsertProject(created); projectId = created.id; track('project_create', { from: 'scan' }); }
        catch (err) { projectId = null; }
      } else projectId = null;
      if (projectId && projectId !== S.projectId) selectProject(projectId, true);
    }
    const fresh = S.freshScan === true; S.freshScan = false;
    const res = await api('submitScan', null, { url, competitor_url: competitor || null, project_id: projectId, turnstile_token: token, fresh });
    S.turnstileToken = null; $('turnstileSlot').innerHTML = '';
    track('scan_submit', { kind: competitor ? 'comparison' : 'site', reused: res.reused });
    S.scan = RD.loading({ url: res.scan.url, competitor: res.scan.competitor_url, started: Date.now() });
    renderOverview(); renderAudit();
    await refreshMe();
    pollScan(res.handle, 0);
  } catch (err) {
    S.turnstileToken = null;
    const ex = explain(err);
    msg.textContent = ex.msg; msg.className = 'err';
    if (ex.action) msg.appendChild(h('span', { style: { marginLeft: '10px' } }, ex.action));
    if (err.code === 'QuotaExceeded') track('quota_hit', { metric: 'scans' });
  } finally { setBusy(btn, false); }
}
function pollScan(handle, attempt) {
  clearTimeout(S.scanPoll);
  S.scanPoll = setTimeout(async () => {
    try {
      const res = await api('scanReport', null, { handle });
      if (res.scan.status === 'done') {
        S.scan = RD.success(res); S.renderedReport = null;
        track('scan_done', { kind: res.scan.kind, mode: res.scan.mode });
        toast('Audit ready · ' + fmt.host(res.scan.url) + ' scored ' + res.scan.overall, {
          action: h('a', { href: '#audit', on: { click: (e) => { e.preventDefault(); setTab('audit'); } }, text: 'View report' }) });
        await refreshMe();
        if (premium() && S.projectId) { S.history = RD.notAsked; loadHistory(true); }
      } else if (res.scan.status === 'failed') {
        S.scan = RD.failure(new ApiError(res.scan.error.code, res.scan.error.message));
        track('scan_failed', { code: res.scan.error.code });
        await refreshMe();
      } else {
        if (attempt < 120) pollScan(handle, attempt + 1);
        else S.scan = RD.failure(new ApiError('Unavailable', 'The audit is taking unusually long. Check History in a few minutes.'));
      }
    } catch (err) { S.scan = RD.failure(err); }
    renderOverview();
    if (S.tab === 'audit') renderAudit();
  }, attempt < 10 ? 2000 : 3500);
}

// ----- projects
function upsertProject(p) {
  const i = S.projects.findIndex((x) => x.id === p.id);
  if (i >= 0) S.projects[i] = p; else S.projects.push(p);
  renderProjectSwitch();
}
function selectProject(id, quiet) {
  S.projectId = id;
  try { localStorage.setItem(STORE_KEY, String(id)); } catch (e) {}
  S.history = RD.notAsked; S.vis = RD.notAsked; S.ranks = RD.notAsked; S.chat.siteLoaded = false;
  for (const k of Object.keys(S.panels)) S.panels[k] = RD.notAsked;
  S.reports = {};
  renderProjectSwitch();
  loadPanels();
  const p = project();
  if (p && !quiet) {
    $('scanUrl').value = p.url;
    if (p.latest_scan) openLatest(p.latest_scan.id); else { S.scan = RD.notAsked; renderOverview(); }
    loadHistory(true);
    loadChatHistory();
  }
  if (S.tab === 'visibility') loadVisibility(true);
}
async function openLatest(id) {
  try { S.scan = RD.success(await api('showScan', { id })); S.renderedReport = null; } catch (e) { S.scan = RD.notAsked; }
  renderOverview();
  if (S.tab === 'audit') renderAudit();
}
async function newProjectFlow() {
  const url = window.prompt('Website for the new project (e.g. yourwebsite.com):', $('scanUrl').value || '');
  if (!url) return;
  try {
    const { project: p } = await api('createProject', null, { url });
    upsertProject(p); track('project_create', { from: 'menu' });
    selectProject(p.id);
    toast('Project created. Run an audit to get its first scores.');
  } catch (e) { const ex = explain(e); toast(ex.msg, { error: true, action: ex.action }); }
}
async function refreshMe() {
  try {
    const me = await api('me');
    S.sub = me.subscription; S.usage = me.usage; S.caps = me.caps;
    if (me.projects.length) S.projects = me.projects;
    renderTopbar();
  } catch (e) { /* the page keeps its last known state */ }
}

// ----- chat (Ask Mira): first message is the site URL, then questions about it
const chatMsgs = $('chatMsgs');
function chatEmpty() {
  chatMsgs.innerHTML = '';
  chatMsgs.appendChild(h('div', { cls: 'chat-empty' }, h('span', { cls: 'mira-mark', style: { width: '44px', height: '44px', fontSize: '24px' }, text: 'M' }),
    h('h3', null, 'Ask anything about ', h('em', { text: 'your site.' })),
    h('p', { text: 'Paste your website URL and Mira reads it, then answers your SEO, AEO and GEO questions with fixes written for your pages.' })));
}
function addMsg(cls, text) {
  chatMsgs.querySelector('.chat-empty')?.remove();
  const el = h('div', { cls: 'chat-msg ' + cls });
  if (cls === 'bot') renderBotMessage(el, text); else el.textContent = text;
  chatMsgs.appendChild(el);
  chatMsgs.scrollTop = chatMsgs.scrollHeight;
  return el;
}
function setSite(site) {
  const box = $('siteBox');
  box.innerHTML = '';
  if (!site) { box.appendChild(h('span', { cls: 'muted', text: 'No website yet. Paste your URL in the chat to start.' })); $('chatInput').placeholder = 'Paste your website URL to start'; return; }
  box.append(h('span', { cls: 'project-fav', text: initials(site.title) }),
    h('div', { style: { minWidth: '0' } }, h('div', { cls: 't', text: site.title }), h('a', { cls: 'u', href: site.url, target: '_blank', rel: 'noopener noreferrer', text: fmt.host(site.url) })));
  $('chatInput').placeholder = 'Ask Mira anything about ' + fmt.host(site.url) + '…';
}
function renderChatQuota() {
  const u = usageOf('chat_messages');
  $('chatQuota').textContent = u.limit ? `${Math.max(0, u.limit - u.used)} of ${u.limit} questions left ${u.period === 'day' ? 'today' : 'this month'}` : '';
}
async function chatPost(message) {
  return request(CHAT_SEND, 'POST', { message, project_id: premium() ? S.projectId : null });
}
async function sendChat(text) {
  const input = $('chatInput');
  const message = (text ?? input.value).trim();
  if (!message || S.chat.busy) return;
  S.chat.busy = true; input.value = '';
  chatMsgs.querySelectorAll('.chat-suggest').forEach((r) => r.remove());
  const isUrl = /^(https?:\/\/|www\.)/i.test(message) || /^[a-z0-9-]+(\.[a-z0-9-]+)+(\/\S*)?$/i.test(message);
  try {
    // A subscriber's project site is loaded automatically before their first question.
    const p = project();
    if (!isUrl && !S.chat.siteLoaded && premium() && p) {
      const typingSite = addMsg('bot typing', 'Reading ' + p.domain + '…');
      const loaded = await chatPost(p.url);
      typingSite.remove();
      if (loaded.site) { setSite(loaded.site); S.chat.siteLoaded = true; }
    }
    addMsg('user', message);
    track('chat_send', { kind: isUrl ? 'url' : 'question' });
    const typing = addMsg('bot typing', isUrl ? 'Reading your website…' : 'Thinking…');
    let data;
    try { data = await chatPost(message); } finally { typing.remove(); }
    addMsg('bot', data.reply);
    if (data.site) { setSite(data.site); S.chat.siteLoaded = true; }
    if (data.usage) { S.usage = data.usage; renderChatQuota(); renderTopbar(); }
    if (data.suggestions && data.suggestions.length) addSuggestions(data.suggestions);
  } catch (err) {
    const ex = explain(err);
    const note = addMsg('notice', ex.msg);
    if (ex.action) note.appendChild(h('div', { style: { marginTop: '10px' } }, ex.action));
    if (err.code === 'QuotaExceeded') track('quota_hit', { metric: 'chat_messages' });
  } finally { S.chat.busy = false; }
}
function addSuggestions(questions) {
  const row = h('div', { cls: 'chat-suggest' });
  questions.forEach((q) => row.appendChild(h('button', { cls: 'suggest-chip', type: 'button', text: q, on: { click: () => { if (!S.chat.busy) { row.remove(); sendChat(q); } } } })));
  chatMsgs.appendChild(row);
  chatMsgs.scrollTop = chatMsgs.scrollHeight;
}
async function loadChatHistory() {
  const p = project();
  if (!premium() || !p) return;
  try {
    const { messages } = await api('messages', { id: p.id });
    chatEmpty();
    messages.forEach((m) => addMsg(m.role === 'user' ? 'user' : 'bot', m.content));
    setSite(messages.length ? { title: p.name, url: p.url } : null);
  } catch (e) { /* history is a convenience */ }
}
async function resetChat() {
  try { await request(CHAT_RESET, 'POST', {}); } catch (e) {}
  S.chat.siteLoaded = false;
  chatEmpty(); setSite(null);
}

// Shared compare-script hook: the report names the visitor's site for the chat card.
window.onComparisonReport = function (c) { if (c && c.you && !S.chat.siteLoaded) setSite({ title: c.you.name, url: c.you.url }); };

// ==================================================================== wiring
$('scanForm').addEventListener('submit', submitScan);
$('compToggle').addEventListener('click', () => {
  S.compare = !S.compare;
  $('compField').hidden = !S.compare;
  $('scanForm').classList.toggle('with-comp', S.compare);
  $('compToggle').setAttribute('aria-pressed', String(S.compare));
  $('compToggleText').textContent = S.compare ? 'Single site' : 'Compare';
  if (S.compare) $('scanComp').focus();
});
document.querySelectorAll('.tab').forEach((t) => t.addEventListener('click', () => setTab(t.dataset.tab)));
document.querySelectorAll('#periodSeg button').forEach((b) => b.addEventListener('click', () => setPeriod(b.dataset.period)));
$('projectBtn').addEventListener('click', (e) => {
  e.stopPropagation();
  const menu = $('projectMenu');
  menu.hidden = !menu.hidden;
  $('projectBtn').setAttribute('aria-expanded', String(!menu.hidden));
});
document.addEventListener('click', (e) => { if (!e.target.closest('#projectSwitch')) closeMenu(); });
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });
$('chatForm').addEventListener('submit', (e) => { e.preventDefault(); sendChat(); });
$('chatInput').addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChat(); } });
$('chatReset').addEventListener('click', resetChat);
document.querySelectorAll('[data-ask]').forEach((b) => b.addEventListener('click', () => { setTab('chat', true); sendChat(b.dataset.ask); }));

// =================================================================== start
(function init() {
  renderTopbar();
  if (!S.sub.signed_in) {
    // Projects (and everything saved to them) belong to an account — say so up front.
    document.querySelector('.hero').insertAdjacentElement('afterend', h('div', { cls: 'note', style: { margin: '0 0 16px', alignItems: 'center' } }, icon(ICONS.info),
      h('span', { style: { flex: 1 }, text: 'You\u2019re viewing as a visitor. Sign in to see your projects, AI visibility history and saved reports.' }),
      h('a', { cls: 'btn btn-primary btn-sm', href: LOGIN_URL, text: 'Sign in' })));
  }
  chatEmpty();
  renderChatQuota();
  // Every visit starts fresh: an empty website box and no earlier project or
  // scan opened (saved projects stay in the project switcher, past scans in
  // History). The exception is coming back from "Connect Google", which
  // reopens the project that was just connected and says how it went.
  const qs = new URLSearchParams(location.search);
  let googleProject = null;
  if (qs.has('google') || qs.has('project')) {
    googleProject = parseInt(qs.get('project'), 10) || null;
    if (qs.get('google') === 'connected') toast(qs.get('google_message') || 'Google connected. Search Console and Analytics data will appear in the overview.', { error: !!qs.get('google_message') });
    else if (qs.get('google') === 'error') toast(qs.get('google_message') || 'Google connection failed.', { error: true });
    track('google_return', { result: qs.get('google') || '' });
    history.replaceState(null, '', location.pathname + location.hash);
  }
  try { localStorage.removeItem(STORE_KEY); } catch (e) {}
  $('scanUrl').value = '';
  $('scanComp').value = '';
  if (premium() && googleProject && S.projects.some((p) => p.id === googleProject)) selectProject(googleProject);
  Object.keys(SOURCES).forEach(renderSources);
  renderOverview();
  renderPanels();
  const hash = location.hash.replace('#', '');
  setTab(['overview', 'organic', 'ranks', 'backlinks', 'audit', 'visibility', 'chat', 'history'].includes(hash) ? hash : 'overview', true);
  track('page_view', { tier: S.sub.tier });
})();
})();
