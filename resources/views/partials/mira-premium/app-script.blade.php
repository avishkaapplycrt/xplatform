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
window.addEventListener('error', (e) => reportError(e.error || e.message, 'window'));
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
  panel:          { m: 'GET',    p: (a) => '/projects/' + a.id + '/panels/' + a.panel + '?period=' + encodeURIComponent(a.period), out: ['panel', 'period', 'data'] },
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
  renderedReport: null, scanPoll: null, visPoll: null, turnstileToken: null,
  period: '30d', periodInfo: null, trafficMetric: 'visits',
  reports: {}, perfMetric: 'organic_traffic', perfYoy: false,
  panels: { ai: RD.notAsked, authority: RD.notAsked, search: RD.notAsked, traffic: RD.notAsked },
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
function upgradeLink(from, label) {
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
  const badge = $('planBadge');
  if (premium()) {
    badge.className = 'badge badge-plan';
    badge.textContent = S.sub.status === 'active_trial' && S.sub.trial_days_left !== null
      ? `${S.sub.label} · ${S.sub.trial_days_left} day${S.sub.trial_days_left === 1 ? '' : 's'} left`
      : S.sub.label + (S.sub.dev_unlock ? ' · dev unlock' : '');
  } else {
    badge.className = 'badge badge-preview';
    badge.textContent = S.sub.status === 'expired' ? 'Plan expired · Preview' : 'Preview';
  }
  $('signInBtn').hidden = S.sub.signed_in;
  const manage = $('manageBtn');
  manage.hidden = !S.sub.management_url;
  if (S.sub.management_url) manage.href = S.sub.management_url;
  $('upgradeBtn').hidden = premium() && S.sub.status === 'active' && S.sub.tier === 'enterprise';
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
  if (tab === 'organic') { renderOrganic(); ['insights', 'countries', 'pages', 'competitors', 'paid'].forEach((r) => loadReport(r)); }
  if (tab === 'backlinks') { renderBacklinks(); loadReport('backlinks'); }
  if (tab === 'history') { loadHistory(); }
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
const PANELS = ['ai', 'authority', 'search', 'traffic'];
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
function connectCard(title, hint, d, ghosts) {
  return [pnlHead(title, hint, h('span', { cls: 'tag-src', text: 'Not connected' })),
    h('div', { cls: 'connect' },
      h('div', { cls: 'ghost-metrics' }, ghosts.map((g) => h('div', null, h('b', { text: '—' }), g))),
      h('p', { text: d.message }),
      d.setup ? h('p', null, h('code', { text: d.setup })) : null)];
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
async function loadPanels() {
  const p = project();
  renderPanels();
  if (!premium() || !p) return;
  const id = p.id, period = S.period;
  PANELS.forEach(async (name) => {
    S.panels[name] = RD.loading();
    renderPanel(name);
    try {
      const res = await api('panel', { id, panel: name, period });
      if (S.projectId !== id || S.period !== period) return;     // a newer selection won
      S.periodInfo = res.period;
      S.panels[name] = RD.success(res.data);
    } catch (e) {
      if (S.projectId !== id || S.period !== period) return;
      S.panels[name] = RD.failure(e);
    }
    $('ovPeriodLabel').textContent = S.periodInfo ? S.periodInfo.label : '';
    renderPanel(name);
  });
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
  ['pnl-traffic', 'pnl-performance', 'pnl-positions'].forEach((id) => { $(id).hidden = false; });
  PANELS.forEach(renderPanel);
  renderPerformance();
  renderPositions();
}
function renderPanel(name) {
  const el = $('pnl-' + name), st = S.panels[name];
  const titles = { ai: 'AI responses', authority: 'Authority', search: 'Search', traffic: 'Traffic analytics' };
  el.innerHTML = '';
  if (name === 'search') $('pnl-keywords').hidden = true;
  if (st.tag === 'NotAsked' || st.tag === 'Loading') { el.append(...skeletonPanel(titles[name])); return; }
  if (st.tag === 'Failure') {
    const ex = explain(st.error);
    el.append(pnlHead(titles[name]), h('p', { cls: 'muted', style: { fontSize: '13.5px' }, text: ex.msg }),
      h('button', { cls: 'btn btn-secondary btn-sm', type: 'button', style: { marginTop: '12px' }, on: { click: loadPanels }, text: 'Retry' }));
    return;
  }
  ({ ai: renderAiPanel, authority: renderAuthorityPanel, search: renderSearchPanel, traffic: renderTrafficPanel })[name](el, st.data);
}

function renderAiPanel(el, d) {
  el.append(pnlHead('AI responses', 'How often AI answers name your brand and cite your pages. Modeled from your prompt set.', h('span', { cls: 'tag-src', text: 'Modeled' })));
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
  const hint = 'Measured by the X Platforms tracking script on your site — real visits, not estimates.';
  if (!d.connected) { el.append(...connectCard('Traffic analytics', hint, d, ['Visits', 'Unique visitors', 'Page views', 'Bounce rate'])); return; }
  const m = d.metrics;
  el.append(pnlHead('Traffic analytics', hint, d.demo ? sourceTag(d) : h('span', { cls: 'tag-src', text: 'Tracking script · ' + d.site })));
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
  competitors: () => renderOrganic(), paid: () => renderOrganic(),
  backlinks: () => renderBacklinks(), ai_brand: () => renderBrandRadar(),
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
function multiLine(series, { height = 220, unit = '', notes = [], fmtV = fmt.compact } = {}) {
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
      t.textContent = new Date(dates[i]).toLocaleDateString(undefined, { month: 'short', year: '2-digit' }); svg.appendChild(t);
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
      tip.append(h('div', null, h('b', { text: new Date(dates[i]).toLocaleDateString(undefined, { month: 'long', year: 'numeric' }) })));
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
  const intent = c(), branded = c(), serp = c(), countries = c(), pages = c(), comps = c(), race = c(), paid = c();
  body.append(h('div', { cls: 'rep-grid three', style: { marginTop: 0 } }, intent, branded, serp),
    h('div', { cls: 'rep-full' }, countries), h('div', { cls: 'rep-full' }, pages),
    h('div', { cls: 'rep-grid' }, comps, race), h('div', { cls: 'rep-full' }, paid));

  const intentColors = { informational: '#3f6fd8', commercial: '#c9a13a', transactional: '#1f8a5b', navigational: '#8a8890' };
  reportCard(intent, 'Traffic by search intent', null, 'insights', (box, d) => {
    box.append(pnlHead('Traffic by search intent', 'Why people search for you: learning, comparing, buying or navigating. ' + d.note, sourceTag(d, 'DataForSEO')));
    box.append(h('div', { cls: 'split-bar' }, d.intent.map((r) => h('i', { title: r.intent + ' ' + r.share + '%', style: { width: r.share + '%', background: intentColors[r.intent] } }))));
    box.append(h('div', { cls: 'kv' }, d.intent.flatMap((r) => [h('span', { cls: 'k' }, h('s', { style: { background: intentColors[r.intent] } }), r.intent.charAt(0).toUpperCase() + r.intent.slice(1)),
      h('span', { cls: 'v', text: fmt.pct(r.share) }), h('span', { cls: 'm', text: fmt.compact(r.traffic) + ' visits' })])));
  }, ['Informational', 'Commercial']);
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
    box.append(table(['Page', 'Traffic', 'Share', 'Keywords', 'Top 3', 'Value', 'AI citations'], d.pages.map((r) => h('tr', null,
      td(h('a', { href: r.url, target: '_blank', rel: 'noopener noreferrer', cls: 'truncate', title: r.url, text: r.path })),
      tdn(fmt.compact(r.traffic)), tdn(fmt.pct(r.share)), tdn(fmt.compact(r.keywords)), tdn(fmt.num(r.top3)), tdn(fmt.money(r.value)),
      h('td', { cls: 'r' }, r.ai_citations ? h('span', { cls: 'chip-you', style: { marginLeft: 0 }, text: r.ai_citations + ' answers' }) : h('span', { cls: 'muted', text: '—' })))), { right: [1, 2, 3, 4, 5, 6] }));
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
}

// ----------------------------------------------------------------- Backlinks
function renderBacklinks() {
  const body = $('blBody');
  if (!body || S.tab !== 'backlinks') return;
  if (gateReport(body, 'backlinks', 'See who vouches for', 'you.', 'Every site linking to you, how strong it is, what it says about you — and what you gained or lost.',
    ['Referring domains growth and new vs lost, month by month', 'Authority distribution of linking sites', 'Referring domains and anchor text tables', 'Followed vs nofollow and link types'])) return;
  const c = () => h('div', { cls: 'card pnl' });
  const kpi = c(), growth = c(), newlost = c(), dist = c(), follow = c(), tld = c(), domains = c(), anchors = c();
  body.append(kpi, h('div', { cls: 'rep-grid' }, growth, newlost), h('div', { cls: 'rep-grid three' }, dist, follow, tld), h('div', { cls: 'rep-grid' }, domains, anchors));
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
  const c = () => h('div', { cls: 'card pnl' });
  const mc = c(), bot = c(), pagesC = c(), domainsC = c(), topicsC = c(), gapC = c(), brandsC = c();
  host.append(h('div', { cls: 'rep-grid' }, mc, bot), h('div', { cls: 'rep-grid three' }, pagesC, domainsC, topicsC), h('div', { cls: 'rep-grid' }, gapC, brandsC));
  mc.append(pnlHead('Mentions vs citations', 'Monthly totals across your runs.'),
    h('p', { cls: 'def' }, h('b', { text: 'Mentions' }), ' — your brand name appears in an AI answer. ', h('b', { text: 'Citations' }), ' — the answer links to your pages as a source.'),
    columns(d.monthly.map((m) => ({ label: monthLabel(m.month), mentions: m.mentions, citations: m.citations })), [{ key: 'mentions', color: '#141417' }, { key: 'citations', color: YOU }],
      { height: 180, labels: { mentions: 'Mentions', citations: 'Citations' } }));
  bot.append(pnlHead('Mentions by brand', 'How often each tracked brand is named, run by run.'),
    multiLine(d.brands_over_time.map((b, i) => ({ name: b.you ? b.name + ' (you)' : b.name, color: b.you ? YOU : PALETTE[i % PALETTE.length], bold: b.you,
      points: b.series.map((p) => ({ date: p.date, value: p.mentions })) })), { height: 200, fmtV: (v) => String(v) }));
  pagesC.append(pnlHead('Top-cited pages', 'Your pages AI answers link to, and the average position among cited sources.'),
    table(['Page', 'Cited in', 'Avg pos.'], d.cited_pages.map((p) => h('tr', null, td(h('span', { cls: 'truncate', style: { maxWidth: '200px' }, title: p.url, text: shortUrl(p.url) })), tdn(p.cited_in), tdn('#' + p.avg_position))), { right: [1, 2] }));
  domainsC.append(pnlHead('Top-cited domains', `Domains most often cited in answers that mention ${d.brand}.`),
    table(['Domain', 'Answers'], d.cited_domains.map((r) => h('tr', null, td([r.domain, r.kind === 'you' ? h('span', { cls: 'chip-you', text: 'you' }) : null]), tdn(r.answers))), { right: [1] }));
  topicsC.append(pnlHead('Topics', `Topics where AI mentions ${d.brand} most (latest run).`),
    h('div', { cls: 'mini-list' }, d.topics.map((t) => h('div', { cls: 'row' }, h('span', { cls: 't', text: t.topic }), h('b', { cls: 'num', text: t.mentions + '/' + t.answers }), h('span', { cls: 'bar' }, h('i', { style: { width: t.rate + '%', background: YOU } }))))));
  gapC.append(pnlHead('Mention gaps', 'Prompts where a competitor is named and you aren’t — your clearest content opportunities.'),
    d.gaps.length ? h('div', null, d.gaps.map((g) => h('div', { cls: 'gap-row' }, h('span', null, g.prompt, g.topic ? h('span', { cls: 'chip', style: { marginLeft: '8px' }, text: g.topic }) : null),
      h('span', null, g.competitors.map((x) => h('span', { cls: 'chip-comp', text: x })))))) : h('p', { cls: 'def', text: 'No gaps in the latest run — every answer naming a competitor also named you.' }));
  const s = d.sentiment, total = s.analyzed || 1;
  brandsC.append(pnlHead('Most mentioned brands', 'Every brand the latest answers named — including ones you don’t track yet.'),
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
        h('span', { cls: 'badge ' + (scan.mode === 'premium' ? 'badge-plan' : 'badge-preview'), text: scan.mode === 'premium' ? 'Premium report' : 'Preview report' }));
      $('auditUpsell').hidden = scan.mode === 'premium';
      if (S.renderedReport !== scan.uuid && report) {
        S.renderedReport = scan.uuid;
        if (scan.kind === 'comparison') miraRenderComparison(report); else miraRenderSiteReport(report);
      }
    }
  }
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
      h('span', { cls: 'sub', text: `${prompts.length} of ${caps.prompts} prompts · up to ${caps.prompts_per_run} answered per run · ${Math.max(0, vu.limit - vu.used)} runs left this month` })),
      h('div', { style: { display: 'flex', gap: '8px', flexWrap: 'wrap' } },
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
    right.appendChild(h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Share of voice' }), h('span', { cls: 'sub', text: fmt.ago(latest.finished_at) })),
      h('div', { cls: 'card-bd' }, h('div', { cls: 'sov' }, sum.entities.map((e) => h('div', { cls: 'sov-row' + (e.is_brand ? ' you' : '') },
        h('span', { cls: 'nm', title: e.name }, h('span', { cls: 'dot' }), e.name), h('span', { cls: 'track' }, h('i', { style: { width: (e.share || 0) + '%' } })),
        h('span', { cls: 'v', text: e.share === null ? '—' : fmt.pct(e.share) })))),
        report.timeline.length > 1 ? h('div', { style: { marginTop: '18px' } }, h('div', { cls: 'side-h', text: 'Mentions per run' }), lineChart(report.timeline.map((t) => ({ v: t.answered ? Math.round(t.mentions / t.answered * 100) : 0 })), 'v', { h: 120 })) : null)));
    right.appendChild(h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Domains AI cites' }), h('span', { cls: 'sub', text: 'Who gets the link instead of you' })),
      h('div', { cls: 'card-bd' }, sum.top_domains.length ? h('table', { cls: 'table' }, h('thead', null, h('tr', null, h('th', { text: 'Domain' }), h('th', { cls: 'r', text: 'Answers' }))),
        h('tbody', null, sum.top_domains.map((d) => h('tr', null, h('td', null, d.domain, ' ', d.kind !== 'other' ? h('span', { cls: 'badge ' + (d.kind === 'you' ? 'badge-plan' : 'badge-info'), text: d.kind === 'you' ? 'you' : 'competitor' }) : null), h('td', { cls: 'r num', text: d.count })))))
        : h('p', { cls: 'muted', text: 'No sources were cited in this run.' }))));
    right.appendChild(h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Coverage by topic' })),
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
  return h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Answers' }), h('span', { cls: 'sub', text: 'What AI said, prompt by prompt' })),
    h('div', { cls: 'card-bd' }, latest.answers.map((a) => h('details', { cls: 'answer' },
      h('summary', null, h('span', { cls: 'flag ' + (a.brand_mentioned ? 'yes' : 'no'), text: a.brand_mentioned ? '#' + a.brand_position : '–' }),
        h('span', null, a.prompt, h('div', { cls: 'chips', style: { marginTop: '6px' } }, a.topic ? h('span', { cls: 'chip', text: a.topic }) : null,
          a.brands_mentioned.map((b) => h('span', { cls: 'chip' + (b === p.brand_terms[0] ? ' you' : ''), text: b })), a.brand_cited ? h('span', { cls: 'chip you', text: 'cites you' }) : null)),
        h('span', { cls: 'muted', style: { fontSize: '12px' }, text: a.error ? 'no answer' : 'view' })),
      a.error ? h('p', { cls: 'muted', style: { marginTop: '10px' }, text: a.error }) : highlight(a.answer || '')))));
}
function brandEditor(p) {
  const terms = h('input', { value: p.brand_terms.join(', '), placeholder: 'Acme, Acme Analytics' });
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
      const { project: updated } = await api('updateProject', { id: p.id }, { brand_terms, competitors });
      upsertProject(updated);
      toast('Brand and competitors saved. They apply from the next run.');
      renderVisibility();
    } catch (e) { const ex = explain(e); toast(ex.msg, { error: true, action: ex.action }); }
    finally { setBusy(save, false); }
  });
  return h('div', { cls: 'card' }, h('div', { cls: 'card-hd' }, h('h3', { text: 'Brand & competitors' }), h('span', { cls: 'sub', text: `up to ${S.caps.competitors} competitors` })),
    h('div', { cls: 'card-bd editor' },
      h('label', null, 'How your brand is named', h('span', { cls: 'field' }, terms)),
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
      h('div', null, pr.prompt, h('div', { cls: 'meta' }, pr.topic ? h('span', { cls: 'chip', text: pr.topic }) : null, pr.source === 'seed' ? h('span', { cls: 'chip', text: 'suggested' }) : null)),
      h('button', { cls: 'btn btn-ghost btn-sm', type: 'button', 'aria-label': 'Delete prompt', on: { click: async () => {
        try { await api('deletePrompt', { id: pr.id }); S.vis.data.prompts = S.vis.data.prompts.filter((x) => x.id !== pr.id); renderVisibility(); }
        catch (e) { toast(explain(e).msg, { error: true }); }
      } } }, icon(ICONS.trash))));
  });
  const input = h('input', { placeholder: 'e.g. What is the best analytics tool for small shops?', maxlength: '300' });
  const topic = h('select', { 'aria-label': 'Topic' }, h('option', { value: '', text: 'Topic' }), ['Discovery', 'Comparison', 'Pricing', 'Trust', 'How-to', 'Local'].map((t) => h('option', { value: t, text: t })));
  const add = h('button', { cls: 'btn btn-secondary', type: 'submit' }, icon(ICONS.plus), 'Add');
  const form = h('form', { cls: 'add-row' }, h('label', { cls: 'field' }, input), topic, add);
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (input.value.trim().length < 8) return;
    setBusy(add, true);
    try {
      const { prompt } = await api('addPrompt', { id: p.id }, { prompt: input.value, topic: topic.value || null });
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
      h('td', null, h('span', { cls: 'badge ' + (s.status === 'done' ? 'badge-good' : s.status === 'failed' ? 'badge-bad' : 'badge-preview'), text: s.status })))))))));
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
    const res = await api('submitScan', null, { url, competitor_url: competitor || null, project_id: projectId, turnstile_token: token });
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
  S.history = RD.notAsked; S.vis = RD.notAsked; S.chat.siteLoaded = false;
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
  chatEmpty();
  renderChatQuota();
  if (premium() && S.projects.length) {
    let stored = null;
    try { stored = parseInt(localStorage.getItem(STORE_KEY), 10); } catch (e) {}
    selectProject(S.projects.some((p) => p.id === stored) ? stored : S.projects[0].id);
  } else if (S.recent.length && S.recent[0].status === 'done') {
    $('scanUrl').value = S.recent[0].url;
    openLatest(S.recent[0].id);
  }
  renderOverview();
  if (!(premium() && S.projects.length)) renderPanels();
  const hash = location.hash.replace('#', '');
  setTab(['overview', 'organic', 'backlinks', 'audit', 'visibility', 'chat', 'history'].includes(hash) ? hash : 'overview', true);
  track('page_view', { tier: S.sub.tier });
})();
})();
