{{--
    Column filters for the tables inside list popups (Part 9, Nov 2026).
    Used by Mock Master Helper and Business Helpers. Each column header gets a ▾ that opens a small menu:
      - text columns: "Contains…" search plus the most common values,
      - number columns: Min / Max,
      - date columns: From / To.
    Filters combine, show as chips above the table with a match count, and are cleared when the popup reopens.
    Nothing is sent to the server: popups filter the rows they already loaded.

    API (window.MMXTF):
      MMXTF.filterRows(rows, state, get)        rows that pass every filter; get(row, key) → cell value
      MMXTF.decorate(table, opts)               add ▾ buttons; opts = { state, columns: [{idx, key, label, values}], onChange }
      MMXTF.bar(host, table, opts)              chips + "N of M" line before the table; opts adds { shown, total, noun }
      MMXTF.attachDom(table, state)             all-in-one for tables that show every row (filters by hiding rows)
--}}
<style>
.mmx-tf-btn{display:inline-flex;align-items:center;justify-content:center;width:17px;height:17px;margin-left:5px;padding:0!important;border:1px solid #e2e5ee!important;border-radius:5px;background:#fff!important;color:#6b7280!important;cursor:pointer;vertical-align:-3px;font-size:0!important;line-height:1;transition:background .15s,border-color .15s,color .15s}
.mmx-tf-btn svg{width:10px;height:10px;transition:transform .2s}
.mmx-tf-btn:hover{background:#f5f3ff!important;border-color:#c4b5fd!important;color:#6d28d9!important}
.mmx-tf-btn.open svg{transform:rotate(180deg)}
.mmx-tf-btn.on{background:#7c3aed!important;border-color:#7c3aed!important;color:#fff!important}
.mmx-tf-menu{position:fixed;z-index:20060;width:260px;background:#fff;border:1px solid #e6e9f0;border-radius:12px;box-shadow:0 14px 34px rgba(17,24,39,.18),0 2px 6px rgba(17,24,39,.06);padding:10px;font:400 12.5px/1.4 Inter,-apple-system,'Segoe UI',Roboto,sans-serif;color:#111827;text-transform:none;letter-spacing:normal;opacity:0;transform:translateY(-4px) scale(.98);transition:opacity .15s,transform .2s cubic-bezier(.34,1.56,.64,1)}
.mmx-tf-menu.show{opacity:1;transform:none}
.mmx-tf-menu h6{margin:0 0 8px;font-size:10.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#6b7280}
.mmx-tf-in{width:100%;box-sizing:border-box;padding:7px 9px;border:1px solid #e5e7eb;border-radius:8px;font:inherit;color:#111827;background:#fff}
.mmx-tf-in:focus{outline:none;border-color:#a78bfa;box-shadow:0 0 0 3px #f5f3ff}
.mmx-tf-row{display:flex;align-items:center;gap:8px;margin-bottom:7px}
.mmx-tf-row span{width:38px;color:#6b7280;font-size:12px}
.mmx-tf-hint{font-size:11px;color:#9ca3af;margin:-2px 0 8px}
.mmx-tf-vals{max-height:210px;overflow:auto;margin:8px 0 6px;display:flex;flex-direction:column;gap:1px}
.mmx-tf-vals button{display:flex;align-items:center;gap:8px;width:100%;border:none;background:none;border-radius:7px;padding:6px 8px;font:inherit;color:#111827;text-align:left;cursor:pointer}
.mmx-tf-vals button:hover{background:#f5f3ff}
.mmx-tf-vals button.sel{background:#ede9fe;color:#5b21b6;font-weight:600}
.mmx-tf-vals button span{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mmx-tf-vals button small{color:#9ca3af;font-variant-numeric:tabular-nums}
.mmx-tf-act{display:flex;gap:6px;margin-top:8px}
.mmx-tf-act button{flex:1;border-radius:8px;padding:7px;font:600 12px Inter,sans-serif;cursor:pointer;border:1px solid #e5e7eb;background:#fff;color:#374151}
.mmx-tf-act button.go{background:#7c3aed;border-color:#7c3aed;color:#fff}
.mmx-tf-bar{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin:0 0 10px;padding:8px 10px;border:1px solid #ede9fe;background:#faf8ff;border-radius:10px;font-size:12px;color:#475569;animation:mmxTfIn .25s ease}
.mmx-tf-bar b{color:#0f172a}
.mmx-tf-chip{display:inline-flex;align-items:center;gap:4px;background:#f5f3ff;color:#5b21b6;border:1px solid #ddd6fe;border-radius:20px;padding:2px 4px 2px 9px;font-size:11.5px;font-weight:600;animation:mmxTfPop .3s cubic-bezier(.34,1.56,.64,1)}
.mmx-tf-chip button{width:17px;height:17px;border:none!important;border-radius:50%;background:transparent!important;color:inherit!important;font-size:12px!important;line-height:1;cursor:pointer;padding:0!important;display:grid;place-items:center}
.mmx-tf-chip button:hover{background:#ddd6fe!important}
.mmx-tf-clear{margin-left:auto;border:1px solid #e5e7eb!important;background:#fff!important;color:#64748b!important;border-radius:20px;padding:3px 10px!important;font-size:11.5px!important;font-weight:600;cursor:pointer}
.mmx-tf-clear:hover{color:#b91c1c!important;border-color:#fecaca!important;background:#fef2f2!important}
tr.mmx-tf-hide{display:none!important}
.mmx-tf-none td{text-align:center;color:#6b7280;padding:18px!important}
@keyframes mmxTfIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:none}}
@keyframes mmxTfPop{from{opacity:0;transform:scale(.7)}to{opacity:1;transform:none}}
@media (prefers-reduced-motion:reduce){.mmx-tf-menu,.mmx-tf-bar,.mmx-tf-chip{transition:none;animation:none}}
</style>
<script>
window.MMXTF = window.MMXTF || (function () {
    var CHEV = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';
    var MON = { jan: 0, feb: 1, mar: 2, apr: 3, may: 4, jun: 5, jul: 6, aug: 7, sep: 8, oct: 9, nov: 10, dec: 11 };
    var MENU = null;
    var esc = function (v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    var text = function (v) { var s = v == null ? '' : String(v).trim(); return s === '—' || s === '-' ? '' : s; };
    var num = function (v) {
        var s = text(v).replace(/[$,%\s]/g, '');
        return /^-?\d+(\.\d+)?$/.test(s) ? parseFloat(s) : null;
    };
    var date = function (v) {
        var s = text(v), m = s.match(/^(\d{1,2}) ([A-Za-z]{3})[a-z]* (\d{4})/);
        if (m && MON[m[2].toLowerCase()] !== undefined) return new Date(+m[3], MON[m[2].toLowerCase()], +m[1]).getTime();
        m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
        return m ? new Date(+m[1], +m[2] - 1, +m[3]).getTime() : null;
    };
    var dayOf = function (iso) { if (!iso) return null; var p = iso.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]).getTime(); };

    /* Column type from its values: number, date or text (needs most non-empty values to agree). */
    function detect(values) {
        var vals = values.map(text).filter(Boolean);
        if (!vals.length) return 'text';
        var n = vals.filter(function (v) { return num(v) !== null; }).length;
        var d = vals.filter(function (v) { return date(v) !== null; }).length;
        if (d >= vals.length * 0.8) return 'date';
        if (n >= vals.length * 0.8) return 'num';
        return 'text';
    }
    function active(f) {
        if (!f) return false;
        if (f.t === 'text') return !!(f.eq || (f.q && f.q.trim()));
        if (f.t === 'num') return f.min != null || f.max != null;
        return !!(f.from || f.to);
    }
    function pass(f, v) {
        if (!active(f)) return true;
        if (f.t === 'text') {
            var s = text(v);
            if (f.eq) return s === f.eq;
            return s.toLowerCase().indexOf(f.q.trim().toLowerCase()) > -1;
        }
        if (f.t === 'num') {
            var n = num(v);
            return n !== null && (f.min == null || n >= f.min) && (f.max == null || n <= f.max);
        }
        var d = date(v);
        return d !== null && (!f.from || d >= dayOf(f.from)) && (!f.to || d <= dayOf(f.to));
    }
    function label(f) {
        if (f.t === 'text') return f.eq ? f.eq : 'contains “' + f.q.trim() + '”';
        if (f.t === 'num') return f.min != null && f.max != null ? f.min + ' to ' + f.max : (f.min != null ? '≥ ' + f.min : '≤ ' + f.max);
        var fmt = function (iso) { var d = new Date(dayOf(iso)); return d.toLocaleDateString('en-AU', { day: '2-digit', month: 'short', year: 'numeric' }); };
        return f.from && f.to ? fmt(f.from) + ' – ' + fmt(f.to) : (f.from ? 'from ' + fmt(f.from) : 'until ' + fmt(f.to));
    }
    function filterRows(rows, state, get) {
        var keys = Object.keys(state || {}).filter(function (k) { return active(state[k]); });
        if (!keys.length) return rows.slice();
        return rows.filter(function (r) { return keys.every(function (k) { return pass(state[k], get(r, k)); }); });
    }

    function close() {
        if (!MENU) return;
        var m = MENU;
        MENU = null;
        if (m.btn) m.btn.classList.remove('open');
        m.el.classList.remove('show');
        setTimeout(function () { m.el.remove(); }, 160);
    }
    function open(btn, col, state, onChange) {
        var type = col.type || (col.type = detect(col.values));
        var f = state[col.key] && state[col.key].t === type ? state[col.key] : { t: type };
        var el = document.createElement('div');
        el.className = 'mmx-tf-menu';
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-label', 'Filter ' + col.label);
        var html = '<h6>' + esc(col.label) + '</h6>';
        if (type === 'num') {
            var ns = col.values.map(num).filter(function (n) { return n !== null; });
            html += (ns.length ? '<div class="mmx-tf-hint">Lowest ' + Math.min.apply(null, ns) + ' · highest ' + Math.max.apply(null, ns) + '</div>' : '') +
                '<div class="mmx-tf-row"><span>Min</span><input class="mmx-tf-in" data-k="min" type="number" step="any" value="' + (f.min != null ? f.min : '') + '"></div>' +
                '<div class="mmx-tf-row"><span>Max</span><input class="mmx-tf-in" data-k="max" type="number" step="any" value="' + (f.max != null ? f.max : '') + '"></div>';
        } else if (type === 'date') {
            html += '<div class="mmx-tf-row"><span>From</span><input class="mmx-tf-in" data-k="from" type="date" value="' + (f.from || '') + '"></div>' +
                '<div class="mmx-tf-row"><span>To</span><input class="mmx-tf-in" data-k="to" type="date" value="' + (f.to || '') + '"></div>';
        } else {
            var counts = {};
            col.values.map(text).filter(Boolean).forEach(function (v) { counts[v] = (counts[v] || 0) + 1; });
            var top = Object.keys(counts).sort(function (a, b) { return counts[b] - counts[a] || a.localeCompare(b); }).slice(0, 12);
            html += '<input class="mmx-tf-in" data-k="q" type="search" placeholder="Contains…" value="' + esc(f.eq ? '' : (f.q || '')) + '">' +
                (top.length ? '<div class="mmx-tf-vals">' + top.map(function (v) {
                    return '<button type="button" data-v="' + esc(v) + '"' + (f.eq === v ? ' class="sel"' : '') + '><span title="' + esc(v) + '">' + esc(v) + '</span><small>' + counts[v] + '</small></button>';
                }).join('') + '</div>' : '');
        }
        html += '<div class="mmx-tf-act"><button type="button" data-act="clear">Clear</button><button type="button" class="go" data-act="done">Done</button></div>';
        el.innerHTML = html;
        document.body.appendChild(el);
        var r = btn.getBoundingClientRect(), w = el.offsetWidth, h = el.offsetHeight;
        el.style.left = Math.min(Math.max(8, r.left - 8), window.innerWidth - w - 8) + 'px';
        el.style.top = (r.bottom + 6 + h > window.innerHeight - 8 ? Math.max(8, r.top - h - 6) : r.bottom + 6) + 'px';
        MENU = { el: el, btn: btn, key: col.key, top: r.top };
        btn.classList.add('open');
        requestAnimationFrame(function () { el.classList.add('show'); });

        var t = null;
        var set = function (nf) {
            if (active(nf)) state[col.key] = nf; else delete state[col.key];
            onChange();
        };
        el.addEventListener('input', function (e) {
            var k = e.target.getAttribute('data-k');
            if (!k) return;
            clearTimeout(t);
            t = setTimeout(function () {
                var nf = { t: type };
                el.querySelectorAll('[data-k]').forEach(function (i) {
                    var v = i.value.trim();
                    if (v === '') return;
                    nf[i.getAttribute('data-k')] = type === 'num' ? parseFloat(v) : v;
                });
                el.querySelectorAll('.mmx-tf-vals .sel').forEach(function (b) { b.classList.remove('sel'); });
                set(nf);
            }, type === 'text' ? 220 : 120);
        });
        el.addEventListener('click', function (e) {
            var vb = e.target.closest('.mmx-tf-vals button');
            if (vb) {
                var v = vb.getAttribute('data-v'), on = vb.classList.contains('sel');
                el.querySelectorAll('.mmx-tf-vals .sel').forEach(function (b) { b.classList.remove('sel'); });
                if (!on) vb.classList.add('sel');
                var q = el.querySelector('[data-k="q"]'); if (q) q.value = '';
                set(on ? { t: 'text' } : { t: 'text', eq: v });
                return;
            }
            var act = e.target.closest('[data-act]');
            if (!act) return;
            if (act.getAttribute('data-act') === 'clear') { set({ t: type }); close(); }
            else close();
        });
        var first = el.querySelector('input');
        if (first) first.focus({ preventScroll: true });
    }

    /* Adds the ▾ buttons; skips "#", "Action" and columns that already have their own filter (Plan). */
    function decorate(table, opts) {
        var head = Array.prototype.find.call(table.querySelectorAll('tr'), function (tr) { return tr.querySelector('th'); });
        if (!head) return;
        opts.columns.forEach(function (col) {
            var th = head.children[col.idx];
            if (!th || th.hasAttribute('data-mm-plan-filter') || th.querySelector('.mm-pf-btn')) return;
            var btn = th.querySelector('.mmx-tf-btn');
            if (!btn) {
                btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mmx-tf-btn';
                btn.innerHTML = CHEV;
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    e.preventDefault();
                    var again = MENU && MENU.btn === btn;
                    close();
                    if (!again) open(btn, col, opts.state, opts.onChange);
                });
                th.style.whiteSpace = 'nowrap';
                th.appendChild(btn);
            }
            var on = active(opts.state[col.key]);
            btn.classList.toggle('on', on);
            btn.title = on ? col.label + ': ' + label(opts.state[col.key]) + ' (change)' : 'Filter ' + col.label;
            btn.setAttribute('aria-label', btn.title);
            if (MENU && MENU.key === col.key && MENU.btn !== btn && MENU.el.isConnected) { MENU.btn = btn; btn.classList.add('open'); } // table was redrawn under the open menu
        });
    }

    /* Chips with ×, the match count and "Clear filters", just before the table (only while a filter is on). */
    function bar(host, table, opts) {
        var old = host.querySelector(':scope > .mmx-tf-bar');
        if (old) old.remove();
        var keys = Object.keys(opts.state).filter(function (k) { return active(opts.state[k]); });
        if (!keys.length) return;
        var byKey = {};
        opts.columns.forEach(function (c) { byKey[c.key] = c; });
        var div = document.createElement('div');
        div.className = 'mmx-tf-bar';
        div.innerHTML = 'Column filters:' + keys.map(function (k) {
            return '<span class="mmx-tf-chip">' + esc((byKey[k] ? byKey[k].label : k) + ': ' + label(opts.state[k])) +
                '<button type="button" data-rm="' + esc(k) + '" aria-label="Remove filter" title="Remove">×</button></span>';
        }).join('') + '<span><b>' + Number(opts.shown).toLocaleString() + '</b> of ' + Number(opts.total).toLocaleString() + ' ' + (opts.noun || 'rows') + ' match</span>' +
            '<button type="button" class="mmx-tf-clear">Clear filters</button>';
        div.addEventListener('click', function (e) {
            var rm = e.target.closest('[data-rm]');
            if (rm) { delete opts.state[rm.getAttribute('data-rm')]; close(); opts.onChange(); return; }
            if (e.target.closest('.mmx-tf-clear')) { Object.keys(opts.state).forEach(function (k) { delete opts.state[k]; }); close(); opts.onChange(); }
        });
        table.parentNode.insertBefore(div, table);
    }

    /* For tables that show every row: reads the columns from the header and cells, filters by hiding rows. */
    function attachDom(table, state) {
        var head = Array.prototype.find.call(table.querySelectorAll('tr'), function (tr) { return tr.querySelector('th'); });
        if (!head) return;
        var rows = Array.prototype.filter.call(table.querySelectorAll('tr'), function (tr) {
            return tr !== head && !tr.classList.contains('mm-pf-empty') && !tr.classList.contains('mmx-tf-none') && !(tr.children.length === 1 && tr.children[0].hasAttribute('colspan'));
        });
        var columns = [];
        Array.prototype.forEach.call(head.children, function (th, i) {
            var name = ((th.childNodes[0] && th.childNodes[0].textContent) || th.textContent).trim();
            if (!name || name === '#' || /^action$/i.test(name)) return;
            columns.push({ idx: i, key: 'c' + i, label: name, values: rows.map(function (tr) { return tr.children[i] ? tr.children[i].textContent : ''; }) });
        });
        var apply = function () {
            var shown = 0;
            rows.forEach(function (tr) {
                var ok = columns.every(function (c) { return pass(state[c.key], tr.children[c.idx] ? tr.children[c.idx].textContent : ''); });
                tr.classList.toggle('mmx-tf-hide', !ok);
                if (ok && !tr.hidden) shown++;
            });
            var none = table.querySelector('tr.mmx-tf-none');
            if (!shown && Object.keys(state).length && rows.length) {
                if (!none) {
                    none = document.createElement('tr');
                    none.className = 'mmx-tf-none';
                    none.innerHTML = '<td colspan="' + head.children.length + '">No rows match these column filters.</td>';
                    (rows[rows.length - 1].parentNode).appendChild(none);
                }
            } else if (none) none.remove();
            decorate(table, opts);
            bar(table.parentNode, table, { state: state, columns: columns, shown: shown, total: rows.filter(function (tr) { return !tr.hidden; }).length, noun: 'rows', onChange: apply });
        };
        var opts = { state: state, columns: columns, onChange: apply };
        apply();
        return apply;
    }

    document.addEventListener('click', function (e) {
        if (MENU && !e.target.closest('.mmx-tf-menu') && !e.target.closest('.mmx-tf-btn')) close();
    }, true);
    // Esc only closes the menu (preventDefault stops a search box from also wiping its text, which would drop the filter).
    document.addEventListener('keydown', function (e) { if (MENU && e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); close(); } }, true);
    window.addEventListener('resize', close);
    // Close on scroll only when the ▾ really moved (a redraw while typing can fire a scroll without moving it).
    document.addEventListener('scroll', function (e) {
        if (!MENU || (e.target.closest && e.target.closest('.mmx-tf-menu'))) return;
        var r = MENU.btn.isConnected ? MENU.btn.getBoundingClientRect() : null;
        if (!r || Math.abs(r.top - MENU.top) > 4) close();
    }, true);

    return { filterRows: filterRows, decorate: decorate, bar: bar, attachDom: attachDom, close: close, active: active, label: label };
})();
</script>
