{{-- Ask Mira competitor comparison report: draws a WebsiteComparisonService
     result (route chat.compare) into #miraComparePanel. Shared by the
     homepage and Premium Mira; styled by partials.ask-mira.compare-styles.
     Layout: score cards + verdict, Google's pipeline stages (filter the
     report), section bars, every check side by side in tabs (competitor
     ahead / both need work / you're ahead / both passing) with "Mark as
     fixed" and a projected score, then spotlights. c.locked (free homepage)
     blurs the check list behind a "Try Premium Mira" card; Premium Mira gets
     it open. All site text via textContent. --}}
function miraEl(tag, cls, text) {
  var el = document.createElement(tag);
  if (cls) el.className = cls;
  if (text !== undefined && text !== null) el.textContent = text;
  return el;
}

// h('div', 'cls', child, 'text', …) — children are nodes or strings (as text).
function miraH(tag, cls) {
  var el = miraEl(tag, cls);
  for (var i = 2; i < arguments.length; i++) {
    var c = arguments[i];
    if (c === null || c === undefined || c === false) continue;
    el.appendChild(typeof c === 'object' ? c : document.createTextNode(String(c)));
  }
  return el;
}

var MIRA_CMP_RANK = { pass: 2, warn: 1, fail: 0 };
var MIRA_CMP_GROUPS = [['behind', 'Competitor is ahead'], ['both', 'Both need work'], ['ahead', "You're ahead"], ['pass', 'Both passing']];
var MIRA_SITE_GROUPS = [['fix', 'Fix these'], ['good', 'Doing well']];
var MIRA_CMP_SEV = { critical: 0, important: 1, minor: 2 };
var MIRA_CMP_BOTS = {
  'GPTBot': 'OpenAI · model training', 'OAI-SearchBot': 'ChatGPT search results', 'ClaudeBot': 'Anthropic · Claude',
  'PerplexityBot': 'Perplexity answers', 'Google-Extended': 'Gemini model training', 'CCBot': 'Common Crawl dataset'
};
var MIRA_CMP_KPI_WHY = {
  'Organic sessions': 'Qualified traffic from Google',
  'Engaged session rate': 'Real engagement vs bounces',
  'Conversion rate': 'Whether search traffic turns into sales',
  'Index coverage': 'How many of your pages Google can show',
  'CTR by position': 'Whether titles and descriptions win clicks',
  'Referring domains / backlinks': 'Authority from other sites',
  'AI Overview inclusion': 'How often AI answers mention you',
  'AI referral traffic': 'Visits from ChatGPT, Perplexity and others'
};

function miraCmpTone(s) { return s === null || s === undefined ? 'var(--cx-track)' : s >= 80 ? 'var(--cx-good)' : s >= 50 ? 'var(--cx-warn)' : 'var(--cx-bad)'; }
function miraCmpGrade(s) { return s >= 90 ? 'A' : s >= 80 ? 'B' : s >= 70 ? 'C' : s >= 60 ? 'D' : 'F'; }
function miraCmpHost(url) { return String(url || '').replace(/^https?:\/\/(www\.)?/i, '').replace(/\/$/, ''); }

function miraRenderComparison(c) {
  // Opens on its own page (route mira.report) — see askMiraReportTab.
  if (c.report_url && !window.ASK_MIRA_REPORT_PAGE) askMiraReportTab.open(c.report_url);
  else miraRenderReport(c);
  // Optional page hook — Premium Mira shows the visitor's site in its "Your website" card.
  if (typeof onComparisonReport === 'function') onComparisonReport(c);
}

// "Analyse my website": the same report for one site (no competitor), from a
// WebsiteAnalyzerService::buildReport() result. Called by renderAnalysisReport()
// (partials.ask-mira.report-script) on pages that include this script.
function miraRenderSiteReport(report) {
  var all = [];
  report.categories.forEach(function (cat) {
    (cat.checks || []).forEach(function (ch) { all.push(ch); });
  });
  var find = function (name) { return all.filter(function (ch) { return ch.name === name; })[0] || {}; };
  var host = miraCmpHost(report.url).split('/')[0];
  miraRenderReport({
    you: {
      url: report.url, name: report.title || host, overall: report.overall, grade: report.grade,
      categories: report.categories.map(function (cat) { return { key: cat.key, label: cat.label, score: cat.score }; }),
      serp_preview: report.serp_preview, rich_result_types: report.rich_result_types || [], open_graph: report.open_graph,
      ai_crawlers: find('AI crawler access').crawlers || {}, llms_txt: find('llms.txt').detail,
      vitals: all.filter(function (ch) { return ch.category === 'speed'; })
    },
    competitor: null,
    checks: [].concat.apply([], report.categories.map(function (cat) {
      return (cat.checks || []).map(function (ch) {
        return { category: cat.key, section: cat.label, name: ch.name, label: ch.name, severity: ch.severity || 'minor',
                 you_status: ch.status, you_detail: ch.detail, why: ch.why, check_with: ch.check_with, recommendation: ch.recommendation };
      });
    })),
    stages: report.stages || [], scoring: report.scoring, needs_connected_data: report.needs_connected_data || [],
    gaps: [], locked: !report.recommendations_source || report.recommendations_source === 'none'
  });
}

// Draws the report into #miraComparePanel: a comparison when c.competitor is
// set, otherwise one site ("My website only" — no competitor columns).
function miraRenderReport(c) {
  var panel = document.getElementById('miraComparePanel');
  panel.innerHTML = '';
  // This report replaces the older pie-card report and its upsell (homepage only).
  document.getElementById('analysisPanel')?.classList.remove('show');
  document.getElementById('chatUpsellAside')?.classList.remove('show');
  var single = !c.competitor;
  var comp = c.competitor || { name: '', url: '', overall: 0, categories: [], vitals: [], ai_crawlers: {} };
  panel.classList.toggle('cx-single', single);
  var GROUPS = single ? MIRA_SITE_GROUPS : MIRA_CMP_GROUPS;

  var checks = c.checks || [];
  var stages = c.stages || [];
  var weights = (c.scoring && c.scoring.status_weights) || { pass: 1, warn: 0.5, fail: 0 };
  var catWeights = (c.scoring && c.scoring.category_weights) || {};
  var sections = c.you.categories;
  var theirScore = {};
  comp.categories.forEach(function (cat) { theirScore[cat.key] = cat.score; });
  var stageOf = {};
  stages.forEach(function (st) { st.categories.forEach(function (k) { stageOf[k] = st; }); });
  var sectionLabel = {};
  sections.forEach(function (s) { sectionLabel[s.key] = s.label; });

  // "Mark as fixed" ticks, per your site, kept in this browser only.
  var storeKey = 'askmira:fixed:' + c.you.url;
  var fixed = {};
  try { (JSON.parse(localStorage.getItem(storeKey)) || []).forEach(function (n) { fixed[n] = true; }); } catch (e) {}
  var saveFixed = function () { try { localStorage.setItem(storeKey, JSON.stringify(Object.keys(fixed))); } catch (e) {} };

  var S = { stage: 'all', section: null, group: null, sort: 'sev', open: {}, unlocked: false };

  // Scores. The server's numbers are shown; the projection re-runs the same
  // maths (config/website_checks.php) with ticked checks counted as passing.
  var scorable = function (ch) { return ch.you_status in weights; };
  var recompute = function (withFixes) {
    var byCat = {};
    checks.forEach(function (ch) {
      if (!scorable(ch)) return;
      var st = withFixes && fixed[ch.name] ? 'pass' : ch.you_status;
      (byCat[ch.category] = byCat[ch.category] || []).push(weights[st]);
    });
    var total = 0, wsum = 0;
    Object.keys(byCat).forEach(function (k) {
      var sc = Math.round(byCat[k].reduce(function (a, b) { return a + b; }, 0) / byCat[k].length * 100);
      var w = k in catWeights ? catWeights[k] : 1;
      total += sc * w; wsum += w;
    });
    return wsum ? total / wsum : 0;
  };
  // Server score plus what the ticked fixes add.
  var projected = function () { return Math.min(100, Math.round(c.you.overall + recompute(true) - recompute(false))); };
  var stageScore = function (st, side) {
    var src = side === 'you' ? sections : comp.categories, total = 0, wsum = 0;
    src.forEach(function (s) {
      if (st !== 'all' && st.categories.indexOf(s.key) < 0) return;
      if (s.score === null || s.score === undefined) return;
      var w = s.key in catWeights ? catWeights[s.key] : 1;
      total += s.score * w; wsum += w;
    });
    return wsum ? Math.round(total / wsum) : null;
  };

  var groupOf = function (ch) {
    var y = MIRA_CMP_RANK[ch.you_status], k = MIRA_CMP_RANK[ch.them_status];
    if (y === undefined) return null;          // info / not measured — shown in the spotlights instead
    if (single) return y < 2 ? 'fix' : 'good';
    if (k === undefined) k = y;
    if (k > y) return 'behind';
    if (y > k) return 'ahead';
    return y < 2 ? 'both' : 'pass';
  };
  var listable = checks.filter(function (ch) { return groupOf(ch) !== null; });
  var openToFix = listable.filter(function (ch) { return ch.you_status !== 'pass'; });

  // ---------- score cards + verdict ----------
  var ring = function (score) {
    var r = 36, circ = 2 * Math.PI * r, ns = 'http://www.w3.org/2000/svg';
    var wrap = miraEl('div', 'cx-ring');
    var svg = document.createElementNS(ns, 'svg'); svg.setAttribute('viewBox', '0 0 88 88');
    var mk = function (stroke) {
      var ci = document.createElementNS(ns, 'circle');
      ci.setAttribute('cx', 44); ci.setAttribute('cy', 44); ci.setAttribute('r', r);
      ci.setAttribute('fill', 'none'); ci.setAttribute('stroke-width', 8); ci.setAttribute('stroke', stroke);
      svg.appendChild(ci); return ci;
    };
    mk('var(--cx-track)');
    var arc = mk(miraCmpTone(score));
    arc.setAttribute('stroke-linecap', 'round');
    arc.setAttribute('stroke-dasharray', circ);
    arc.style.strokeDashoffset = circ;
    wrap.appendChild(svg);
    var val = miraEl('div', 'cx-val cx-num', score); val.style.color = miraCmpTone(score);
    wrap.appendChild(val);
    // Animate the arc in after layout.
    requestAnimationFrame(function () { requestAnimationFrame(function () { arc.style.strokeDashoffset = circ * (1 - score / 100); }); });
    return wrap;
  };
  var siteCard = function (s, isYou) {
    return miraH('div', 'cx-panel cx-site' + (isYou ? ' you' : ''), ring(s.overall),
      miraH('div', 'cx-site-text',
        miraEl('div', 'cx-eyebrow', isYou ? 'Your website' : 'Competitor'),
        miraEl('h3', null, s.name),
        miraEl('div', 'cx-url', miraCmpHost(s.url)),
        miraEl('div', 'cx-grade cx-num', s.overall + '/100 · Grade ' + s.grade)));
  };
  panel.appendChild(miraH('section', 'cx-hero', siteCard(c.you, true), single ? null : miraEl('div', 'cx-vs', 'VS'), single ? null : siteCard(comp, false)));

  var verdict = miraEl('div', 'cx-verdict');
  panel.appendChild(verdict);
  var renderVerdict = function () {
    verdict.innerHTML = '';
    var y = c.you.overall, k = comp.overall, msg = miraEl('p');
    if (single) msg.append(miraEl('b', 'cx-num', openToFix.length + ' check' + (openToFix.length === 1 ? '' : 's')), openToFix.length === 1 ? ' needs attention on your site.' : ' need attention on your site.');
    else if (k > y) msg.append('Your competitor is ', miraEl('b', 'cx-num', (k - y) + ' points'), ' ahead overall. Fix the gaps below to catch up.');
    else if (y > k) msg.append("You're ", miraEl('b', 'cx-num', (y - k) + ' points'), " ahead overall. Keep the lead by fixing what's left.");
    else msg.append("You're level overall. The checks below show where each of you can still improve.");
    verdict.appendChild(miraH('div', null, msg));
    var marked = openToFix.filter(function (ch) { return fixed[ch.name]; }).length;
    var proj = miraEl('span', 'cx-proj');
    if (marked) {
      var p = projected();
      proj.append(marked + ' of ' + openToFix.length + ' marked fixed · projected score ', miraEl('span', 'cx-num', y), ' → ', miraEl('b', 'cx-num', p));
    } else {
      proj.textContent = openToFix.length ? 'Mark fixes as done to see your projected score.' : 'Every scored check on your site passes.';
    }
    verdict.appendChild(proj);
  };

  // ---------- stages ----------
  var stagesBox = miraEl('div', 'cx-stages');
  if (stages.length) {
    panel.appendChild(miraH('section', null,
      miraH('div', 'cx-head', miraEl('h2', null, 'How Google sees each site'), miraEl('span', 'cx-muted', 'Pick a stage to focus the report')),
      stagesBox));
  }
  var miniBars = function (y, k) {
    var mini = miraEl('span', 'cx-mini');
    [['You', y, 'var(--cx-accent)'], ['Comp', k, 'var(--cx-comp)']].slice(0, single ? 1 : 2).forEach(function (r) {
      var lbl = miraEl('span', null, r[0]); lbl.style.color = r[2];
      var t = miraEl('span', 'cx-t'), i = miraEl('i');
      i.style.width = (r[1] || 0) + '%'; i.style.background = miraCmpTone(r[1]);
      t.appendChild(i);
      var v = miraEl('span', 'cx-num', r[1] === null ? '—' : r[1]); v.style.textAlign = 'right';
      mini.append(lbl, t, v);
    });
    return mini;
  };
  // Premium Mira (window.MIRA_STAGE_DASHBOARD): the stages as a metrics
  // dashboard instead of cards, in the style of SEO-tool overviews. Left: an
  // "All stages" panel with headline numbers and a stage table. Right: one
  // tile per stage (donut, score, gap to the competitor, checks passing).
  var passCount = function (st, side) {
    var key = side === 'you' ? 'you_status' : 'them_status', pass = 0, total = 0;
    checks.forEach(function (ch) {
      if (st !== 'all' && st.categories.indexOf(ch.category) < 0) return;
      if (!(ch[key] in weights)) return;
      total++;
      if (ch[key] === 'pass') pass++;
    });
    return { pass: pass, total: total };
  };
  // Your number minus the competitor's, as a pill: ▲n ahead, ▼n behind, = level
  // (the arrow is drawn by CSS; aria-label spells it out).
  var delta = function (y, k) {
    if (single || y === null || k === null || y === undefined || k === undefined) return null;
    var d = y - k;
    var el = miraEl('span', 'cx-delta cx-num ' + (d > 0 ? 'up' : d < 0 ? 'down' : 'flat'), d ? Math.abs(d) : '=');
    el.setAttribute('aria-label', d > 0 ? d + ' ahead' : d < 0 ? -d + ' behind' : 'level');
    return el;
  };
  var toneCls = function (s) { return 'cx-tone-' + (s === null || s === undefined ? 'none' : s >= 80 ? 'good' : s >= 50 ? 'warn' : 'bad'); };
  // Donuts and bars fill in once, when the report first appears — not on every stage click.
  var dashAnimated = false;
  var animateTo = function (el, prop, from, to) {
    if (dashAnimated) { el.style[prop] = to; return; }
    el.style[prop] = from;
    requestAnimationFrame(function () { requestAnimationFrame(function () { el.style[prop] = to; }); });
  };
  var info = function (text) {
    var i = miraEl('span', 'cx-info', 'i');
    i.title = text; i.setAttribute('aria-label', text);
    return i;
  };
  var miniRing = function (score) {
    var ns = 'http://www.w3.org/2000/svg', r = 15, circ = 2 * Math.PI * r;
    var gid = 'cxg' + (miraRenderReport.ringId = (miraRenderReport.ringId || 0) + 1);
    var svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('viewBox', '0 0 40 40'); svg.setAttribute('class', 'cx-mring ' + toneCls(score)); svg.setAttribute('aria-hidden', 'true');
    // Gradient stroke coloured by the tone class (--c1 → --c2).
    var defs = document.createElementNS(ns, 'defs'), grad = document.createElementNS(ns, 'linearGradient');
    grad.setAttribute('id', gid); grad.setAttribute('x1', '0'); grad.setAttribute('y1', '0'); grad.setAttribute('x2', '1'); grad.setAttribute('y2', '1');
    ['--c1', '--c2'].forEach(function (v, i) {
      var stop = document.createElementNS(ns, 'stop');
      stop.setAttribute('offset', i ? '1' : '0'); stop.setAttribute('style', 'stop-color:var(' + v + ')');
      grad.appendChild(stop);
    });
    defs.appendChild(grad); svg.appendChild(defs);
    ['#efeff2', 'url(#' + gid + ')'].forEach(function (stroke, i) {
      var ci = document.createElementNS(ns, 'circle');
      ci.setAttribute('cx', 20); ci.setAttribute('cy', 20); ci.setAttribute('r', r);
      ci.setAttribute('fill', 'none'); ci.setAttribute('stroke-width', 4); ci.setAttribute('stroke', stroke);
      if (i) {
        ci.setAttribute('class', 'arc'); ci.setAttribute('stroke-linecap', 'round'); ci.setAttribute('stroke-dasharray', circ);
        animateTo(ci, 'strokeDashoffset', circ, circ * (1 - (score || 0) / 100));
      }
      svg.appendChild(ci);
    });
    return svg;
  };
  // Thin score bar; a notch marks the competitor's score.
  var scoreBar = function (y, k) {
    var bar = miraEl('div', 'cx-tbar ' + toneCls(y)), fill = miraEl('i');
    animateTo(fill, 'width', '0%', (y || 0) + '%');
    bar.appendChild(fill);
    if (!single && k !== null && k !== undefined) {
      var mark = miraEl('u'); mark.style.left = k + '%'; mark.title = 'Competitor: ' + k;
      bar.appendChild(mark);
    }
    return bar;
  };
  var num = function (v) { return v === null || v === undefined ? '—' : v; };
  var renderDash = function () {
    stagesBox.innerHTML = '';
    stagesBox.className = 'cx-dash';
    var select = function (key) { S.stage = key; S.section = null; renderAll(); };
    var pressable = function (el, key) {
      el.type = 'button';
      el.setAttribute('aria-pressed', S.stage === key);
      el.addEventListener('click', function () { select(key); });
      return el;
    };

    // ---- All stages ----
    // Headline score is the server's overall (as on the score cards), not a stage average.
    var yAll = c.you.overall, kAll = single ? null : comp.overall;
    var pAll = passCount('all', 'you'), qAll = passCount('all', 'comp');
    var metric = function (label, value, d, sub) {
      return miraH('div', 'cx-metric', miraEl('div', 'cx-metric-lbl', label),
        miraH('div', 'cx-metric-val', miraEl('span', 'cx-big cx-num', value), d), sub);
    };
    var table = miraEl('div', 'cx-dash-table');
    table.appendChild(miraH('div', 'cx-dash-tr head', miraEl('span', null, 'Stage'), miraEl('span', null, single ? 'Score' : 'You'), miraEl('span', null, single ? 'Passing' : 'Competitor')));
    stages.forEach(function (st) {
      var y = stageScore(st, 'you'), k = stageScore(st, 'comp'), p = passCount(st, 'you');
      var dot = miraEl('i', 'cx-dot ' + toneCls(y));
      table.appendChild(pressable(miraH('button', 'cx-dash-tr',
        miraH('span', 'cx-dash-st', dot, st.label),
        miraH('span', null, miraEl('b', 'cx-num', num(y)), delta(y, k)),
        single ? miraEl('span', 'cx-num', p.pass + '/' + p.total) : miraEl('span', 'cx-num', num(k))), st.key));
    });
    stagesBox.appendChild(miraH('div', 'cx-dash-ov',
      pressable(miraH('button', 'cx-dash-title', miraEl('span', null, 'All stages'), info('Every check across the ' + stages.length + ' stages'),
        miraEl('span', 'cx-dash-tag', S.stage === 'all' ? 'Whole report' : 'Show all')), 'all'),
      miraH('div', 'cx-dash-metrics',
        metric('Overall score', num(yAll), delta(yAll, kAll),
          single ? miraH('div', 'cx-sub', 'Grade ', miraEl('b', null, c.you.grade)) : miraH('div', 'cx-sub', 'Competitor ', miraEl('b', 'cx-num', num(kAll)))),
        metric('Checks passing', pAll.pass, delta(pAll.pass, qAll.pass),
          miraH('div', 'cx-sub', 'of ', miraEl('b', 'cx-num', pAll.total), ' scored'))),
      table));

    // ---- One tile per stage ----
    var tiles = miraEl('div', 'cx-dash-tiles');
    stages.forEach(function (st, i) {
      var y = stageScore(st, 'you'), k = stageScore(st, 'comp');
      var p = passCount(st, 'you'), q = passCount(st, 'comp'), open = p.total - p.pass;
      var tail = single
        ? miraEl('span', 'cx-delta cx-num note ' + (open ? 'down' : 'up'), open ? open + ' to fix' : 'all clear')
        : delta(p.pass, q.pass);
      tiles.appendChild(pressable(miraH('button', 'cx-tile',
        miraH('div', 'cx-tile-lbl', miraEl('span', 'cx-tile-step cx-num', (i < 9 ? '0' : '') + (i + 1)), miraEl('span', null, st.label), info(st.question)),
        miraH('div', 'cx-tile-val', miniRing(y), miraEl('span', 'cx-big cx-num', num(y)), delta(y, k)),
        scoreBar(y, k),
        miraH('div', 'cx-sub', 'Passing ', miraEl('b', 'cx-num', p.pass + '/' + p.total), tail)), st.key));
    });
    stagesBox.appendChild(tiles);
    dashAnimated = true;
  };

  var renderStages = function () {
    if (window.MIRA_STAGE_DASHBOARD) { renderDash(); return; }
    stagesBox.innerHTML = '';
    var btn = function (key, step, name, q, st) {
      var b = miraH('button', 'cx-stage', miraEl('span', 'cx-step', step), miraEl('span', 'cx-name', name), miraEl('span', 'cx-q', q),
        miniBars(stageScore(st, 'you'), stageScore(st, 'comp')));
      b.type = 'button';
      b.setAttribute('aria-pressed', S.stage === key);
      b.addEventListener('click', function () { S.stage = key; S.section = null; renderAll(); });
      return b;
    };
    stagesBox.appendChild(btn('all', 'Whole report', 'All stages', 'Every check across the ' + stages.length + ' stages', 'all'));
    stages.forEach(function (st, i) { stagesBox.appendChild(btn(st.key, 'Stage ' + (i + 1), st.label, st.question, st)); });
  };

  // ---------- section by section ----------
  var rows = miraEl('div', 'cx-rows');
  panel.appendChild(miraH('section', 'cx-panel',
    miraH('div', 'cx-head', miraEl('h2', null, 'Section by section'), miraEl('span', 'cx-muted', 'Click a section to see its checks')),
    rows));
  var renderRows = function () {
    rows.innerHTML = '';
    rows.appendChild(miraH('div', 'cx-row head', miraEl('span'), miraEl('span', 'cx-eyebrow you', 'You'), miraEl('span'), single ? null : miraEl('span', 'cx-eyebrow', 'Competitor'), single ? null : miraEl('span')));
    sections.forEach(function (s) {
      var st = stageOf[s.key];
      if (S.stage !== 'all' && (!st || st.key !== S.stage)) return;
      var y = s.score, k = s.key in theirScore ? theirScore[s.key] : null;
      var track = function (v) { var t = miraEl('span', 'cx-track'), i = miraEl('i'); i.style.width = (v || 0) + '%'; i.style.background = miraCmpTone(v); t.appendChild(i); return t; };
      var pct = function (v, win) { var p = miraEl('span', 'cx-pct cx-num', v === null ? '—' : v + '%'); if (win) p.append(' ', miraEl('span', 'cx-win', '✓')); return p; };
      var row = miraH('button', 'cx-row',
        miraH('span', 'cx-lbl', s.label, st ? miraEl('small', null, st.label) : null),
        track(y), pct(y, y !== null && k !== null && y > k),
        single ? null : track(k), single ? null : pct(k, y !== null && k !== null && k > y));
      row.type = 'button';
      row.setAttribute('aria-label', s.label + ': you ' + (y === null ? 'not measured' : y + '%') + (single ? '' : ', competitor ' + (k === null ? 'not measured' : k + '%')));
      row.addEventListener('click', function () {
        S.section = s.key;
        var pool = listable.filter(function (ch) { return ch.category === s.key; });
        S.group = (GROUPS.map(function (g) { return g[0]; }).filter(function (g) { return pool.some(function (ch) { return groupOf(ch) === g; }); })[0]) || GROUPS[0][0];
        renderIssues();
        issues.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
      rows.appendChild(row);
    });
  };

  // ---------- check by check ----------
  var issues = miraEl('section');
  var tabs = miraEl('div', 'cx-tabs'), tools = miraEl('div', 'cx-tools'), list = miraEl('div', 'cx-list');
  var sortSel = miraEl('select');
  issues.appendChild(miraH('div', 'cx-head', miraEl('h2', null, single ? 'What to fix on your site' : 'Where you stand, check by check')));
  panel.appendChild(issues);

  var card = function (ch) {
    var isOpen = !!S.open[ch.name], canFix = ch.you_status !== 'pass', done = canFix && fixed[ch.name];
    var el = miraEl('article', 'cx-issue' + (isOpen ? ' open' : '') + (done ? ' done' : ''));
    var st = stageOf[ch.category];
    var sev = ch.severity || 'minor';
    var toggle = function () { S.open[ch.name] = !S.open[ch.name]; renderList(); };
    var chev = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    chev.setAttribute('class', 'cx-chev'); chev.setAttribute('width', 18); chev.setAttribute('height', 18);
    chev.setAttribute('viewBox', '0 0 24 24'); chev.setAttribute('fill', 'none'); chev.setAttribute('stroke', 'currentColor'); chev.setAttribute('stroke-width', 2);
    chev.innerHTML = '<path d="m6 9 6 6 6-6"/>';
    var head = miraH('button', 'cx-ihead',
      miraH('span', 'cx-ititle', miraEl('b', null, ch.label || ch.name),
        miraH('span', 'cx-meta', miraEl('span', 'cx-pill cx-sev-' + sev, sev.charAt(0).toUpperCase() + sev.slice(1)),
          ch.section + (st ? ' · ' + st.label : ''))),
      chev);
    head.type = 'button';
    head.setAttribute('aria-expanded', isOpen);
    head.addEventListener('click', toggle);
    el.appendChild(head);

    var v = function (label, status, detail) {
      var dot = miraEl('span', 'cx-dot cx-s-' + status); dot.setAttribute('aria-label', status || 'not checked');
      return miraH('div', 'cx-v', dot, miraH('div', null, miraEl('div', 'cx-eyebrow', label), detail || '—'));
    };
    var body = miraH('div', 'cx-ibody', miraH('div', 'cx-vals', v('You', ch.you_status, ch.you_detail), single ? null : v('Competitor', ch.them_status, ch.them_detail)));
    if (canFix) {
      body.appendChild(miraH('p', 'cx-fix', miraEl('b', null, 'How to fix: '),
        ch.recommendation || 'Unlock Mira Premium to see the step-by-step fix for this check and the exact change to make on your website.'));
    }
    var more = miraEl('div', 'cx-more');
    more.hidden = !isOpen;
    var kv = miraEl('dl', 'cx-kv');
    if (ch.why) kv.append(miraEl('dt', null, 'Why it matters'), miraEl('dd', null, ch.why));
    if (ch.check_with) kv.append(miraEl('dt', null, 'Check with'), miraEl('dd', null, ch.check_with));
    more.appendChild(kv);
    body.appendChild(more);

    var actions = miraEl('div', 'cx-actions');
    if (canFix) {
      var box = miraEl('input'); box.type = 'checkbox'; box.checked = !!done;
      box.addEventListener('change', function () {
        if (box.checked) fixed[ch.name] = true; else delete fixed[ch.name];
        saveFixed(); renderVerdict(); renderList();
      });
      actions.appendChild(miraH('label', 'cx-mark', box, 'Mark as fixed'));
    } else {
      actions.appendChild(miraEl('span'));
    }
    if (!isOpen && (ch.why || ch.check_with)) {
      var more2 = miraEl('button', 'cx-btn', 'Why it matters'); more2.type = 'button';
      more2.addEventListener('click', toggle);
      actions.appendChild(more2);
    }
    body.appendChild(actions);
    el.appendChild(body);
    return el;
  };

  var pool = function () {
    return listable.filter(function (ch) {
      var st = stageOf[ch.category];
      return (S.stage === 'all' || (st && st.key === S.stage)) && (!S.section || ch.category === S.section);
    });
  };
  var renderList = function () {
    var items = pool().filter(function (ch) { return groupOf(ch) === S.group; });
    items.sort(S.sort === 'gap'
      ? function (a, b) { return (MIRA_CMP_RANK[b.them_status] - MIRA_CMP_RANK[b.you_status]) - (MIRA_CMP_RANK[a.them_status] - MIRA_CMP_RANK[a.you_status]) || MIRA_CMP_SEV[a.severity] - MIRA_CMP_SEV[b.severity]; }
      : function (a, b) { return MIRA_CMP_SEV[a.severity] - MIRA_CMP_SEV[b.severity] || MIRA_CMP_RANK[a.you_status] - MIRA_CMP_RANK[b.you_status]; });
    list.innerHTML = '';
    if (!items.length) { list.appendChild(miraEl('div', 'cx-empty', 'Nothing in this group for the current filter.')); return; }
    items.forEach(function (ch) { list.appendChild(card(ch)); });
  };
  var renderIssues = function () {
    var p = pool();
    if (!S.group) S.group = (GROUPS.filter(function (g) { return p.some(function (ch) { return groupOf(ch) === g[0]; }); })[0] || GROUPS[0])[0];
    tabs.innerHTML = '';
    GROUPS.forEach(function (g) {
      var b = miraH('button', 'cx-tab', g[1], miraEl('span', 'cx-n cx-num', p.filter(function (ch) { return groupOf(ch) === g[0]; }).length));
      b.type = 'button';
      b.setAttribute('aria-pressed', S.group === g[0]);
      b.addEventListener('click', function () { S.group = g[0]; renderIssues(); });
      tabs.appendChild(b);
    });
    tools.innerHTML = '';
    var chips = miraEl('div');
    var chip = function (text, clear) {
      var b = miraEl('button', 'cx-chip', text + ' ✕'); b.type = 'button';
      b.addEventListener('click', function () { clear(); renderAll(); });
      chips.append(b, ' ');
    };
    if (S.stage !== 'all') chip('Stage: ' + (stages.filter(function (s) { return s.key === S.stage; })[0] || {}).label, function () { S.stage = 'all'; S.section = null; });
    if (S.section) chip('Section: ' + sectionLabel[S.section], function () { S.section = null; });
    tools.appendChild(chips);
    tools.appendChild(miraH('label', 'cx-muted', 'Sort by ', sortSel));
    tools.style.justifyContent = 'space-between';
    renderList();
  };
  [['sev', 'Severity'], ['gap', 'Biggest gap first']].slice(0, single ? 1 : 2).forEach(function (o) { var opt = miraEl('option', null, o[1]); opt.value = o[0]; sortSel.appendChild(opt); });
  sortSel.style.cssText = 'font:inherit;font-size:.86rem;background:var(--cx-surface);color:var(--cx-fg);border:0;border-radius:99px;padding:7px 12px;box-shadow:var(--cx-shadow);margin-left:6px';
  sortSel.addEventListener('change', function () { S.sort = sortSel.value; renderList(); });

  // Spotlights (Google / share previews, Core Web Vitals, AI crawlers, data):
  // created here so a locked single-site report can blur them too.
  var spots = miraEl('div');
  spots.style.cssText = 'display:grid;gap:16px';

  var gapCount = c.gaps ? c.gaps.length : 0;
  if (single && c.locked && openToFix.length) {
    // Free homepage "Analyse my website": the tabs stay as a teaser;
    // everything from "Sort by" to the bottom of the report is blurred behind
    // a Try Premium Mira card whose button goes to the pricing page.
    var sLocked = miraEl('div', 'cx-locked');
    var sPreview = miraH('div', 'cx-locked-preview', tools, list, spots);
    sPreview.setAttribute('aria-hidden', 'true');
    sPreview.inert = true;
    list.style.marginBottom = '16px';
    var sBtn = miraEl('a', 'cx-lock-btn', '★ Try Premium Mira'); sBtn.href = '{{ route("pricing") }}';
    var sLock = miraEl('div', 'cx-lock');
    sLock.appendChild(miraH('div', 'cx-lock-card', miraEl('div', 'cx-lock-icon', '🔒'),
      miraEl('h5', null, 'Unlock your full website report'),
      miraEl('p', null, 'See how to fix all ' + openToFix.length + ' issue' + (openToFix.length === 1 ? '' : 's') + ' on your site — step by step, plus your Google preview, speed and AI visibility.'),
      sBtn));
    sLocked.append(sPreview, sLock);
    issues.append(tabs, sLocked);
  } else if (c.locked && gapCount) {
    // Free homepage: the check list is blurred behind a Mira Premium card.
    // The blur covers the check list and every section after it (links
    // preview, Core Web Vitals, AI crawler access), all the way down.
    var locked = miraEl('div', 'cx-locked cx-locked-full');
    list.style.marginBottom = '16px';
    var preview = miraH('div', 'cx-locked-preview', tabs, tools, list, spots);
    preview.setAttribute('aria-hidden', 'true');
    preview.inert = true;
    var lock = miraEl('div', 'cx-lock');
    // The button goes to the pricing page.
    var btn = miraEl('a', 'cx-lock-btn', '★ Try Premium Mira'); btn.href = '{{ route("pricing") }}';
    lock.appendChild(miraH('div', 'cx-lock-card', miraEl('div', 'cx-lock-icon', '🔒'),
      miraEl('h5', null, 'Unlock your competitor gap report'),
      miraEl('p', null, 'See all ' + gapCount + ' thing' + (gapCount === 1 ? '' : 's') + ' ' + comp.name.split(/\s[|\-–—]\s/)[0] + ' does better than you — and exactly how to fix each one.'),
      btn));
    locked.append(preview, lock);
    issues.appendChild(locked);
  } else {
    issues.append(tabs, tools, list);
  }

  // ---------- spotlights ----------
  if (!spots.parentNode) panel.appendChild(spots);
  var findCheck = function (name) { return checks.filter(function (ch) { return ch.name === name; })[0] || {}; };
  var flag = function (status, text) {
    var cls = status === 'pass' ? 'good' : status === 'fail' ? 'bad' : status === 'warn' ? 'warn' : 'info';
    return miraEl('div', 'cx-flag ' + cls, (cls === 'good' ? '✓ ' : '! ') + text);
  };
  var side = function (isYou) { return single ? null : miraEl('div', 'cx-side' + (isYou ? ' you' : ''), isYou ? 'You' : 'Competitor'); };
  var serp = function (s, isYou) {
    var p = s.serp_preview || {}, key = isYou ? 'you' : 'them';
    var t = findCheck('Title tag'), d = findCheck('Meta description');
    var desc = p.description || "No description set. Google will pick text from the page, often a menu or cookie notice.";
    var box = miraH('div', 'cx-serp',
      miraH('div', 'cx-serp-site', miraEl('span', 'cx-fav', (s.name || '?').trim().charAt(0).toUpperCase()),
        miraH('span', null, s.name, miraEl('br'), p.display_url || miraCmpHost(s.url))),
      miraEl('div', 'cx-st', p.title || miraCmpHost(s.url)),
      miraEl('div', 'cx-sd' + (p.description_source === 'meta' ? '' : ' missing'), desc));
    return miraH('div', null, side(isYou), box,
      t[key + '_status'] ? flag(t[key + '_status'], 'Title: ' + t[key + '_detail'] + (p.title_truncated ? ' · Google cuts it off' : '')) : null,
      d[key + '_status'] ? flag(d[key + '_status'], 'Meta description: ' + d[key + '_detail'] + (p.description_source === 'page_text' ? ' · Google picks page text instead' : '')) : null,
      flag((s.rich_result_types || []).length ? 'pass' : 'info', 'Rich results: ' + ((s.rich_result_types || []).join(', ') || 'none yet')));
  };
  var ogCard = function (s, isYou) {
    var og = s.open_graph || {}, ch = findCheck('Open Graph tags'), key = isYou ? 'you' : 'them';
    var img = miraEl('div', 'cx-og-img none');
    img.append('No og:image', miraEl('br'), 'Facebook and LinkedIn show no picture');
    var src = null;
    try { if (og.image) { var u = new URL(og.image, s.url); if (/^https?:$/.test(u.protocol)) src = u.href; } } catch (e) {}
    if (src) {
      var pic = miraEl('img'); pic.alt = ''; pic.loading = 'lazy'; pic.referrerPolicy = 'no-referrer'; pic.src = src;
      var noPic = img.cloneNode(true);
      pic.addEventListener('error', function () { holder.replaceChild(noPic, img); });
      img = miraH('div', 'cx-og-img has', pic);
    }
    var holder = miraH('div', 'cx-og', img,
      miraH('div', 'cx-og-txt', miraEl('small', null, miraCmpHost(s.url).split('/')[0]),
        miraEl('b', null, og.title || (s.serp_preview && s.serp_preview.title) || s.name),
        miraEl('span', null, og.description ? (og.description.length > 90 ? og.description.slice(0, 90).trim() + '…' : og.description) : 'No description')));
    return miraH('div', null, side(isYou), holder,
      ch[key + '_status'] ? flag(ch[key + '_status'], ch[key + '_detail'] + (ch[key + '_status'] === 'pass' ? '' : ' · preview falls back to the page title')) : null);
  };
  var fmtVital = function (v, unit) { return unit === 'ms' ? (v >= 1000 ? (v / 1000).toFixed(1) + ' s' : Math.round(v) + ' ms') : Number(v).toFixed(2); };
  var renderSpots = function () {
    spots.innerHTML = '';
    var show = function (key) { return S.stage === 'all' || S.stage === key; };
    var pane = function (title, eyebrow) {
      var p = miraH('section', 'cx-panel', miraH('div', 'cx-head', miraEl('h2', null, title), eyebrow ? miraEl('span', 'cx-eyebrow', eyebrow) : null));
      spots.appendChild(p); return p;
    };

    if (show('serve')) {
      var p1 = pane('How your links look', 'Serve stage');
      var h1 = miraEl('h3', null, 'In Google search results'); h1.style.cssText = 'font-size:1rem;margin-top:14px';
      var h2 = miraEl('h3', null, 'When shared on Facebook or LinkedIn'); h2.style.cssText = 'font-size:1rem;margin-top:22px';
      if (single) {
        // One site: the Google result and the share card side by side.
        h1.style.cssText = h2.style.cssText = 'font-size:1rem;margin:0 0 10px';
        p1.appendChild(miraH('div', 'cx-pair', miraH('div', null, h1, serp(c.you, true)), miraH('div', null, h2, ogCard(c.you, true))));
      } else {
        p1.append(h1, miraH('div', 'cx-pair', serp(c.you, true), serp(comp, false)),
          h2, miraH('div', 'cx-pair', ogCard(c.you, true), ogCard(comp, false)));
      }
    }

    if (show('experience')) {
      var p2 = pane('Core Web Vitals', 'Experience stage · mobile');
      var mine = c.you.vitals || [], theirs = comp.vitals || [];
      var measured = mine.concat(theirs).filter(function (m) { return m.value !== undefined; });
      if (!measured.length) {
        p2.appendChild(miraEl('p', 'cx-note', single ? "Core Web Vitals weren't measured for this report, so Page Speed isn't counted in your score." : "Core Web Vitals weren't measured for this comparison, so Page Speed isn't counted in either score."));
      } else {
        var leg = miraEl('div', 'cx-legend');
        [['var(--cx-accent)', 'You'], ['var(--cx-comp)', 'Competitor'], ['var(--cx-good-soft)', 'Good'], ['var(--cx-warn-soft)', 'Needs improvement'], ['var(--cx-bad-soft)', 'Poor']]
          .filter(function (l) { return !(single && l[1] === 'Competitor'); }).forEach(function (l) { var i = miraEl('i'); i.style.background = l[0]; leg.appendChild(miraH('span', null, i, l[1])); });
        var cwv = miraEl('div', 'cx-cwv');
        mine.forEach(function (m) {
          var them = theirs.filter(function (x) { return x.name === m.name; })[0] || {};
          var ref = m.value !== undefined ? m : them;
          var short = (m.name.match(/\(([A-Z]+)\)/) || [])[1] || m.name, full = m.name.replace(/\s*\([A-Z]+\)/, '');
          var top = miraH('div', 'cx-topline', miraH('b', null, short + ' ', miraEl('span', 'cx-muted', '· ' + full)));
          var vals = miraEl('span', 'cx-num');
          var you = miraEl('span', null, 'You ' + (m.value !== undefined ? fmtVital(m.value, m.unit) : 'not measured')); you.style.color = 'var(--cx-accent)';
          var comp = miraEl('span', null, 'Competitor ' + (them.value !== undefined ? fmtVital(them.value, them.unit) : 'not measured')); comp.style.color = 'var(--cx-comp)';
          if (single) vals.append(you); else vals.append(you, ' · ', comp);
          top.appendChild(vals);
          var row = miraH('div', 'cx-metric', top);
          if (ref.limits) {
            var max = ref.limits.poor * 1.5, pct = function (v) { return Math.min(100, v / max * 100); };
            var scale = miraEl('div', 'cx-scale');
            var z1 = miraEl('span', 'z1'), z2 = miraEl('span', 'z2'), z3 = miraEl('span', 'z3');
            z1.style.width = pct(ref.limits.good) + '%'; z2.style.width = (pct(ref.limits.poor) - pct(ref.limits.good)) + '%';
            scale.append(z1, z2, z3);
            [[m, 'you', 'You'], [them, '', 'Competitor']].slice(0, single ? 1 : 2).forEach(function (x) {
              if (x[0].value === undefined) return;
              var mk = miraEl('span', 'cx-marker ' + x[1]); mk.style.left = pct(x[0].value) + '%'; mk.title = x[2] + ': ' + fmtVital(x[0].value, x[0].unit);
              scale.appendChild(mk);
            });
            row.append(scale, miraH('div', 'cx-ticks cx-num', miraEl('span', null, '0'), miraEl('span', null, 'Good ≤ ' + fmtVital(ref.limits.good, ref.unit)),
              miraEl('span', null, 'Poor > ' + fmtVital(ref.limits.poor, ref.unit)), miraEl('span', null, fmtVital(max, ref.unit))));
          } else {
            row.appendChild(miraEl('p', 'cx-note', m.detail));
          }
          cwv.appendChild(row);
        });
        var sources = measured.map(function (m) { return m.source; }).filter(function (v, i, a) { return v && a.indexOf(v) === i; });
        p2.append(leg, cwv, miraEl('p', 'cx-note', 'Mobile data from Google PageSpeed Insights' + (sources.length ? ' (' + sources.join(', ') + ')' : '') + '. Metrics without data aren\'t counted in the score.'));
      }
    }

    if (show('ai')) {
      var p3 = pane('AI crawler access', 'AI Visibility stage');
      var bots = Object.keys(Object.assign({}, c.you.ai_crawlers || {}, comp.ai_crawlers || {}));
      if (bots.length) {
        var tag = function (state) { return state ? miraEl('span', 'cx-tag ' + (state === 'blocked' ? 'no' : 'ok'), state === 'blocked' ? 'Blocked' : 'Allowed') : miraEl('span', 'cx-muted', '—'); };
        var tbody = miraEl('tbody');
        bots.forEach(function (b) {
          tbody.appendChild(miraH('tr', null, miraH('td', null, miraEl('b', null, b)), miraEl('td', 'cx-muted', MIRA_CMP_BOTS[b] || ''),
            miraH('td', null, tag((c.you.ai_crawlers || {})[b])), single ? null : miraH('td', null, tag((comp.ai_crawlers || {})[b]))));
        });
        p3.appendChild(miraH('div', 'cx-tblwrap', miraH('table', null,
          miraH('thead', null, miraH('tr', null, miraEl('th', null, 'Crawler'), miraEl('th', null, 'Used for'), miraEl('th', null, single ? 'Your site' : 'You'), single ? null : miraEl('th', null, 'Competitor'))), tbody)));
      }
      var llms = function (d) { return /^Found/.test(d || '') ? 'found' : 'not found'; };
      p3.appendChild(miraEl('p', 'cx-note', 'Blocking a crawler is a choice, not an error. Blocking training bots (GPTBot, CCBot) keeps content out of future models, while blocking search bots removes you from AI answers. llms.txt: '
        + llms(c.you.llms_txt) + ' on your site' + (single ? '' : ', ' + llms(comp.llms_txt) + " on your competitor's") + ". It's optional and has no proven effect on rankings."));
    }

    // "Needs connected data" is shown on single-site reports only, not comparisons.
    var kpis = single ? (c.needs_connected_data || []) : [];
    if (kpis.length) {
      var p4 = miraH('section', 'cx-panel', miraH('div', 'cx-head', miraEl('h2', null, 'Needs connected data'), miraEl('span', 'cx-muted', "These can't be measured by scanning a website")));
      var kb = miraEl('tbody');
      kpis.forEach(function (k) {
        var connect = miraEl('button', 'cx-btn', 'Connect'); connect.type = 'button'; connect.disabled = true; connect.title = 'Available once integrations are built';
        kb.appendChild(miraH('tr', null, miraH('td', null, miraEl('b', null, k.kpi)), miraEl('td', 'cx-muted', MIRA_CMP_KPI_WHY[k.kpi] || ''),
          miraEl('td', null, k.tool), (function () { var td = miraEl('td', 'cx-muted', 'Not measured'); td.style.whiteSpace = 'nowrap'; return td; })(), miraH('td', null, connect)));
      });
      p4.appendChild(miraH('div', 'cx-tblwrap', miraH('table', null,
        miraH('thead', null, miraH('tr', null, miraEl('th', null, 'KPI'), miraEl('th', null, 'What it tells you'), miraEl('th', null, 'Source'), miraEl('th', null, 'Status'), miraEl('th'))), kb)));
      spots.appendChild(p4);
    }
  };

  var renderAll = function () { renderVerdict(); renderStages(); renderRows(); renderIssues(); renderSpots(); };
  renderAll();

  panel.hidden = false;
  panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
