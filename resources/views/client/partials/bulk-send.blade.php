{{--
    "Send to all" window, shared by Mock Master Helper and Business Helpers.

    XBulk.open(cfg) shows every customer from cfg.load(), pre-ticks the ones that pass
    the list's current filters, and lets the user re-select by group (Plan / Segment),
    toggle each applied filter, search, tick or untick, then send in batches and
    download a PDF (or CSV) report of who was sent what.

    cfg = {
      title, reportTitle,
      load:      () => Promise<rows>   rows: {id, name, email, phone, group, date, search, raw}
      channels:  ['email', 'whatsapp'],
      groupLabel: 'Plan',              chips for each row.group value
      groupClass: (group) => css class for the group pill (optional)
      filters:   [{label, test(row)}]  the list's applied filters, shown as chips
      templates: [{key, label, hint, subject, body}],
      fill:      (text, row) => text   personalises a template for one row
      note:      placeholder help text under the message box
      send:      (channel, items, total) => Promise<[{id, status, to, error}]>
                 items: [{row, subject, body}] — one batch (up to cfg.batch, default 10)
    }
--}}
<style>
.xb-overlay{display:none;position:fixed;inset:0;background:rgba(17,24,39,.45);z-index:10000;align-items:center;justify-content:center;padding:24px}
.xb-overlay.show{display:flex}
.xb{background:#fff;border-radius:12px;max-width:1060px;width:100%;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25);font-size:13px;color:#111827}
.xb-hd{display:flex;align-items:flex-start;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #e5e7eb;font-weight:600}
.xb-hd small{display:block;font-size:11.5px;font-weight:500;color:#6b7280;margin-top:3px}
.xb-hd-r{display:flex;align-items:center;gap:6px;margin-left:auto;min-width:0}
.xb-acct{display:flex;align-items:center;gap:9px;min-width:0;padding:5px 12px 5px 6px;border:1px solid #ede9fe;background:#faf8ff;border-radius:999px}
.xb-acct-ic{flex:none;width:26px;height:26px;border-radius:50%;background:#ede9fe;color:#7c3aed;display:flex;align-items:center;justify-content:center}
.xb-acct-tx{display:flex;flex-direction:column;min-width:0;line-height:1.2}
.xb-acct-tx span{font-size:10.5px;font-weight:600;color:#6b7280;letter-spacing:.02em}
.xb-acct-tx b{font-size:12.5px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:280px}
.xb-acct-st{flex:none;font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:999px;white-space:nowrap;text-decoration:none}
.xb-acct-st.on{background:#dcfce7;color:#166534}
.xb-acct-st.off{background:#fef3c7;color:#92400e}
.xb-acct-st.off:hover{background:#fde68a}
@media (max-width:640px){.xb-acct-tx span{display:none}.xb-acct-tx b{max-width:150px}}
.xb-x{border:none;background:none;font-size:16px;color:#9ca3af;cursor:pointer;line-height:1;padding:4px;margin-left:14px}
.xb-x:hover{color:#111827}
.xb-body{overflow:auto;padding:14px 22px 20px}
.xb-chips{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-bottom:10px;font-size:11.5px;color:#6b7280}
.xb-chips>b{font-size:10.5px;letter-spacing:.5px;text-transform:uppercase;margin-right:4px;min-width:118px}
.xb-chip{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:4px 11px;font-size:11.5px;font-weight:600;cursor:pointer;border:1px solid #ddd6fe;background:#f5f3ff;color:#5b21b6;transition:background .15s,color .15s,border-color .15s}
.xb-chip:hover{border-color:#a78bfa}
.xb-chip.part{background:#fff;border-style:dashed}
.xb-chip.off{background:#fef2f2;border-color:#fecaca;color:#b91c1c;text-decoration:line-through;text-decoration-thickness:1px}
.xb-chip.off:hover{border-color:#f87171}
.xb-chip.static{cursor:default}
.xb-chip small{font-weight:500;opacity:.8;text-decoration:none}
.xb-chip:disabled{cursor:not-allowed;opacity:.7}
.xb-pick{position:relative;display:inline-block}
.xb-pick-btn{display:inline-flex;align-items:center;gap:6px;border:1px solid #c4b5fd;background:#fff;color:#5b21b6;border-radius:8px;padding:5px 11px;font-size:12px;font-weight:600;cursor:pointer}
.xb-pick-btn small{font-weight:500;color:#6b7280}
.xb-pick-btn:disabled{cursor:not-allowed;opacity:.7}
.xb-pick-menu{position:absolute;top:calc(100% + 6px);left:0;z-index:30;width:310px;max-height:300px;overflow-y:auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 14px 34px rgba(17,24,39,.16);padding:6px}
.xb-pick-item{display:flex;align-items:flex-start;gap:10px;padding:8px 10px;border-radius:9px;cursor:pointer;margin-bottom:3px;border:1px solid transparent}
.xb-pick-item input{margin-top:2px;width:15px;height:15px;flex-shrink:0}
.xb-pick-item span{display:flex;flex-direction:column;min-width:0}
.xb-pick-item b{font-size:12.5px;font-weight:600}
.xb-pick-item small{font-size:11px;opacity:.85}
.xb-pick-item.on{background:#f5f3ff;color:#5b21b6;border-color:#ede9fe}
.xb-pick-item.on input{accent-color:#7c3aed}
.xb-pick-item.part{background:#fff;color:#5b21b6;border-color:#ddd6fe;border-style:dashed}
.xb-pick-item.part input{accent-color:#7c3aed}
.xb-pick-item.off{background:#fef2f2;color:#b91c1c;border-color:#fee2e2}
.xb-pick-item.off input{accent-color:#dc2626}
.xb-pick-sep{font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#9ca3af;padding:8px 10px 4px;border-top:1px solid #f1f2f6;margin-top:4px}
.xb-grid{display:grid;grid-template-columns:1.25fr 1fr;gap:22px;align-items:start;margin-top:6px}
@media (max-width:860px){.xb-grid{grid-template-columns:1fr}}
.xb-tools{display:flex;gap:10px;align-items:center;margin-bottom:8px}
.xb-seg{display:inline-flex;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;flex-shrink:0}
.xb-seg button{border:none;background:#fff;padding:7px 14px;font-size:12px;font-weight:600;color:#6b7280;cursor:pointer}
.xb-seg button.on{background:#7c3aed;color:#fff}
.xb-seg button:disabled{cursor:not-allowed}
.xb-search{flex:1;min-width:0;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:12.5px}
.xb-bar{display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#374151;margin:4px 0 6px;gap:8px}
.xb-bar button{border:none;background:none;color:#7c3aed;font-size:12px;font-weight:600;cursor:pointer;padding:2px 6px}
.xb-bar button:disabled{color:#c4b5fd;cursor:not-allowed}
.xb-list{border:1px solid #e5e7eb;border-radius:10px;max-height:44vh;overflow:auto}
.xb-row{display:grid;grid-template-columns:20px 1fr auto 104px;gap:10px;align-items:center;padding:8px 12px;border-bottom:1px solid #f1f2f6;cursor:pointer;font-size:12.5px}
.xb-row:last-child{border-bottom:none}
.xb-row:hover{background:#faf9ff}
.xb-row.na{opacity:.55;cursor:not-allowed}
.xb-row input{accent-color:#7c3aed;width:15px;height:15px}
.xb-n,.xb-d{display:flex;flex-direction:column;min-width:0}
.xb-n b{font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.xb-n small,.xb-d small{color:#6b7280;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.xb-d{text-align:right;color:#374151;font-size:11.5px}
.xb-g{font-size:11px;font-weight:600;padding:2px 8px;border-radius:999px;background:#f3f4f6;color:#374151;white-space:nowrap}
.xb-empty{padding:18px;color:#6b7280;font-size:12.5px;margin:0}
.xb-lbl{display:block;font-size:11px;font-weight:700;color:#6b7280;margin:0 0 4px}
.xb-in{width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;font-family:inherit;margin-bottom:10px;background:#fff}
.xb-note{font-size:11px;color:#6b7280;margin:-4px 0 12px}
.xb-send{width:100%;display:inline-flex;align-items:center;justify-content:center;padding:10px 16px;border:none;border-radius:8px;background:#7c3aed;color:#fff;font-size:13px;font-weight:600;cursor:pointer}
.xb-send:disabled{background:#e5e7eb;color:#9ca3af;cursor:not-allowed}
.xb-spin{display:inline-block;width:13px;height:13px;margin-right:8px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:xbspin .7s linear infinite;vertical-align:-2px}
@keyframes xbspin{to{transform:rotate(360deg)}}
.xb-status{margin-top:10px;font-size:12.5px}
.xb-prog{height:6px;border-radius:999px;background:#ede9fe;overflow:hidden;margin-bottom:8px}
.xb-prog i{display:block;height:100%;background:#7c3aed;transition:width .25s}
.xb-stop{border:1px solid #e5e7eb;background:#fff;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;color:#374151;cursor:pointer}
.xb-done{color:#166534;margin-bottom:8px}
.xb-dl{display:flex;gap:8px}
.xb-dl button{flex:1;padding:9px 12px;border-radius:8px;border:none;background:#16a34a;color:#fff;font-size:12.5px;font-weight:600;cursor:pointer}
.xb-dl button.alt{flex:0 0 auto;background:#fff;color:#374151;border:1px solid #e5e7eb}
.xb-open{display:inline-flex;align-items:center;gap:6px;padding:6px 13px;border-radius:6px;border:1px solid #7c3aed;color:#7c3aed;font-size:12px;font-weight:600;cursor:pointer;background:#fff}
.xb-open:hover{background:#f5f3ff}
.xb-open-bar{display:flex;justify-content:flex-end;padding:8px 14px;border-bottom:1px solid #eef0f5}
/* Send confirmation — replaces the browser confirm() */
.xb-cf-ov{position:fixed;inset:0;z-index:10001;display:flex;align-items:center;justify-content:center;padding:24px;background:rgba(17,24,39,.35);backdrop-filter:blur(2px);animation:xbfade .15s ease-out}
.xb-cf{background:#fff;border-radius:14px;width:100%;max-width:440px;box-shadow:0 24px 64px rgba(17,24,39,.28);overflow:hidden;font-size:13px;color:#111827;animation:xbpop .18s cubic-bezier(.2,.9,.3,1.2)}
.xb-cf-top{display:flex;gap:14px;padding:20px 22px 6px}
.xb-cf-ic{flex:0 0 40px;height:40px;border-radius:50%;background:#f5f3ff;color:#7c3aed;display:flex;align-items:center;justify-content:center}
.xb-cf-ic.wa{background:#ecfdf5;color:#16a34a}
.xb-cf h3{margin:0;font-size:15.5px;font-weight:700;line-height:1.3}
.xb-cf-top p{margin:4px 0 0;color:#6b7280;font-size:12.5px;line-height:1.45}
.xb-cf-sum{margin:12px 22px 0;border:1px solid #eef0f5;border-radius:10px;background:#fafafb}
.xb-cf-sum div{display:grid;grid-template-columns:78px 1fr;gap:10px;padding:8px 12px;font-size:12.5px}
.xb-cf-sum div + div{border-top:1px solid #eef0f5}
.xb-cf-sum dt{color:#6b7280;font-weight:600;font-size:11.5px;padding-top:1px}
.xb-cf-sum dd{margin:0;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#111827}
.xb-cf-warn{display:flex;gap:8px;align-items:flex-start;margin:12px 22px 0;color:#92400e;font-size:12px;line-height:1.45}
.xb-cf-ft{display:flex;justify-content:flex-end;gap:8px;padding:18px 22px 20px}
.xb-cf-ft button{padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit}
.xb-cf-no{border:1px solid #e5e7eb;background:#fff;color:#374151}
.xb-cf-no:hover{background:#f9fafb}
.xb-cf-yes{border:none;background:#7c3aed;color:#fff;display:inline-flex;align-items:center;gap:7px}
.xb-cf-yes:hover{background:#6d28d9}
.xb-cf-yes.wa{background:#16a34a}
.xb-cf-yes.wa:hover{background:#15803d}
.xb-cf-ft button:focus-visible{outline:2px solid #7c3aed;outline-offset:2px}
@keyframes xbfade{from{opacity:0}to{opacity:1}}
@keyframes xbpop{from{opacity:0;transform:translateY(8px) scale(.97)}to{opacity:1;transform:none}}
@media (prefers-reduced-motion:reduce){.xb-cf-ov,.xb-cf{animation:none}}
</style>
<script>
window.XBulk = (function () {
    var S = null; // current window state
    // Logged-in client account (clients table), shown in the window header.
    @php
        $xbClient = auth('client')->user();
        $xbAccount = [
            'company'   => $xbClient?->company_name,
            'email'     => $xbClient?->email,
            'connected' => (bool) \App\Support\ClientMailSender::settings(),
            'setupUrl'  => route('client.email.sender'),
        ];
    @endphp
    var ACCOUNT = @json($xbAccount);
    var SEND_ICON = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>';

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function el(id) { return document.getElementById(id); }
    function addr(r) {
        return S.channel === 'email' ? (r.email || '') : (String(r.phone || '').replace(/\D/g, '') ? r.phone : '');
    }
    function isOn(r) { return !S.excluded[r.id] && !!addr(r); }
    function recipients() { return S.rows.filter(isOn); }
    function visible() {
        var q = S.query.trim().toLowerCase();
        if (!q) return S.rows;
        var words = q.split(/\s+/);
        return S.rows.filter(function (r) {
            var hay = [r.name, r.email, r.phone, r.group, r.date, r.search].join(' ').toLowerCase();
            return words.every(function (w) { return hay.indexOf(w) > -1; });
        });
    }
    function setRows(rows, on) {
        rows.forEach(function (r) { if (on) delete S.excluded[r.id]; else S.excluded[r.id] = true; });
    }

    function open(cfg) {
        S = { cfg: cfg, rows: [], excluded: {}, channel: (cfg.channels || ['email'])[0], query: '', sending: false, cancel: false, results: null, sent: null };
        var ov = el('xbOverlay');
        if (!ov) {
            ov = document.createElement('div');
            ov.id = 'xbOverlay';
            ov.className = 'xb-overlay';
            ov.onclick = function (e) { if (e.target === ov) close(); };
            document.body.appendChild(ov);
        }
        ov.innerHTML =
            '<div class="xb" role="dialog" aria-modal="true" aria-labelledby="xbTitle">' +
                '<div class="xb-hd"><span id="xbTitle">' + esc(cfg.title || 'Send to all') + '<small id="xbSub">Loading customers…</small></span>' +
                '<div class="xb-hd-r">' +
                    (ACCOUNT.email
                        ? '<div class="xb-acct" title="' + esc((ACCOUNT.company ? ACCOUNT.company + ' · ' : '') + ACCOUNT.email) + '">' +
                            '<span class="xb-acct-ic"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>' +
                            '<span class="xb-acct-tx"><span>' + esc(ACCOUNT.company || 'Client account') + '</span><b>' + esc(ACCOUNT.email) + '</b></span>' +
                            (ACCOUNT.connected
                                ? '<span class="xb-acct-st on" title="Emails are sent through your own mail account">Connected</span>'
                                : '<a class="xb-acct-st off" href="' + esc(ACCOUNT.setupUrl) + '" target="_blank" rel="noopener" title="Emails go out from the shared XPlatform address until you connect your mail account">Not connected · Set up</a>') +
                          '</div>'
                        : '') +
                    '<button type="button" class="xb-x" onclick="XBulk.close()" aria-label="Close">✕</button>' +
                '</div></div>' +
                '<div class="xb-body" id="xbBody"><p style="color:#6b7280">Loading everyone in this list…</p></div>' +
            '</div>';
        ov.classList.add('show');
        Promise.resolve().then(cfg.load).then(function (rows) {
            S.rows = rows || [];
            // Pre-tick only the customers that pass every filter applied on the list.
            S.rows.forEach(function (r) {
                if (!(cfg.filters || []).every(function (f) { return f.test(r); })) S.excluded[r.id] = true;
            });
            render();
        }).catch(function () {
            el('xbBody').innerHTML = '<p style="color:#b91c1c">Couldn\'t load the customers — please try again.</p>';
        });
    }

    function close() {
        if (S && S.sending) return;
        var ov = el('xbOverlay');
        if (ov) ov.classList.remove('show');
    }
    document.addEventListener('keydown', function (e) {
        if (el('xbConfirm')) return; // the confirmation handles its own keys
        var ov = el('xbOverlay');
        if (e.key === 'Escape' && ov && ov.classList.contains('show')) close();
    });

    // In-app replacement for confirm(): resolves true on Send, false on Cancel / Esc / backdrop.
    function confirmSend(rec, email, subject) {
        return new Promise(function (resolve) {
            var n = rec.length, who = n.toLocaleString() + ' customer' + (n === 1 ? '' : 's');
            var names = rec.slice(0, 2).map(function (r) { return r.name || addr(r); });
            var list = names.join(', ') + (n > 2 ? ' and ' + (n - 2).toLocaleString() + ' more' : '');
            var wa = email ? '' : ' wa';
            var icon = email
                ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>'
                : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.4A8.4 8.4 0 1 1 21 11.5z"/></svg>';

            var ov = document.createElement('div');
            ov.id = 'xbConfirm';
            ov.className = 'xb-cf-ov';
            ov.innerHTML =
                '<div class="xb-cf" role="alertdialog" aria-modal="true" aria-labelledby="xbCfTitle" aria-describedby="xbCfDesc">' +
                    '<div class="xb-cf-top"><div class="xb-cf-ic' + wa + '">' + icon + '</div><div>' +
                        '<h3 id="xbCfTitle">Send ' + (email ? 'this email' : 'this WhatsApp message') + ' to ' + who + '?</h3>' +
                        '<p id="xbCfDesc">Each message is personalised and sent to the ' + (email ? 'email address' : 'phone number') + ' saved for that customer.</p>' +
                    '</div></div>' +
                    '<dl class="xb-cf-sum">' +
                        '<div><dt>Channel</dt><dd>' + (email ? 'Email' : 'WhatsApp') + '</dd></div>' +
                        (email ? '<div><dt>Subject</dt><dd title="' + esc(subject) + '">' + esc(subject || '(no subject)') + '</dd></div>' : '') +
                        '<div><dt>To</dt><dd title="' + esc(list) + '">' + esc(list) + '</dd></div>' +
                    '</dl>' +
                    '<div class="xb-cf-warn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex:none;margin-top:1px"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>' +
                        '<span>Messages can\'t be recalled once sent. You can stop the run part-way.</span></div>' +
                    '<div class="xb-cf-ft">' +
                        '<button type="button" class="xb-cf-no">Cancel</button>' +
                        '<button type="button" class="xb-cf-yes' + wa + '">' + SEND_ICON + 'Send to ' + who + '</button>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(ov);

            var yes = ov.querySelector('.xb-cf-yes'), no = ov.querySelector('.xb-cf-no');
            function done(ok) {
                document.removeEventListener('keydown', onKey, true);
                ov.remove();
                resolve(ok);
            }
            function onKey(e) {
                if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); done(false); }
                else if (e.key === 'Tab') { // keep focus inside the two buttons
                    e.preventDefault();
                    (document.activeElement === yes ? no : yes).focus();
                }
            }
            yes.onclick = function () { done(true); };
            no.onclick = function () { done(false); };
            ov.onclick = function (e) { if (e.target === ov) done(false); };
            document.addEventListener('keydown', onKey, true);
            yes.focus();
        });
    }

    function render() {
        var cfg = S.cfg;
        el('xbSub').textContent = S.rows.length.toLocaleString() + ' customers in this list · the ones matching your filters are ticked';
        var channels = cfg.channels || ['email'];
        var seg = channels.length > 1
            ? '<div class="xb-seg" role="group" aria-label="Send by">' + channels.map(function (c) {
                return '<button type="button" data-ch="' + c + '"' + (c === S.channel ? ' class="on"' : '') + ' onclick="XBulk.channel(\'' + c + '\')">' + (c === 'email' ? 'Email' : 'WhatsApp') + '</button>';
              }).join('') + '</div>'
            : '';
        var tpl = (cfg.templates || []).map(function (t) {
            return '<option value="' + esc(t.key) + '">' + esc(t.label) + (t.hint ? ' — ' + esc(t.hint) : '') + '</option>';
        }).join('');
        el('xbBody').innerHTML =
            '<div class="xb-chips" id="xbFilters"></div>' +
            '<div class="xb-chips" id="xbGroups"></div>' +
            '<div class="xb-grid">' +
              '<div>' +
                '<div class="xb-tools">' + seg +
                  '<input id="xbSearch" class="xb-search" type="search" placeholder="Search name, email or date (e.g. 12 Oct 2026)" oninput="XBulk.search(this.value)" aria-label="Search customers">' +
                '</div>' +
                '<div class="xb-bar"><span id="xbCount"></span>' +
                  '<span><button type="button" onclick="XBulk.select(true)">Select all</button><button type="button" onclick="XBulk.select(false)">Deselect all</button></span></div>' +
                '<div class="xb-list" id="xbList"></div>' +
              '</div>' +
              '<div>' +
                '<label class="xb-lbl" for="xbTpl">Message template</label>' +
                '<select id="xbTpl" class="xb-in" onchange="XBulk.template(this.value)"><option value="">Choose a template…</option>' + tpl + '</select>' +
                '<label class="xb-lbl" for="xbSubject" id="xbSubjectLbl">Email subject</label>' +
                '<input id="xbSubject" class="xb-in" type="text" oninput="XBulk.refresh()">' +
                '<label class="xb-lbl" for="xbMsg">Message</label>' +
                '<textarea id="xbMsg" class="xb-in" rows="8" oninput="XBulk.refresh()"></textarea>' +
                '<p class="xb-note">' + esc(cfg.note || '') + '</p>' +
                '<button type="button" id="xbSend" class="xb-send" onclick="XBulk.send()" disabled></button>' +
                '<div id="xbStatus" class="xb-status"></div>' +
              '</div>' +
            '</div>';
        renderList();
        refresh();
    }

    /* Applied filters and group chips: violet while any of their customers is selected,
       red once all of them are deselected. Clicking toggles that whole set. */
    function renderChips() {
        var cfg = S.cfg, dis = S.sending ? ' disabled' : '';
        var filters = cfg.filters || [];
        el('xbFilters').innerHTML = '<b>Filters applied</b>' + (filters.length
            ? filters.map(function (f, i) {
                var on = S.rows.some(function (r) { return f.test(r) && isOn(r); });
                return '<button type="button" class="xb-chip' + (on ? '' : ' off') + '"' + dis + ' onclick="XBulk.toggleFilter(' + i + ')" title="' + (on ? 'Click to deselect everyone matching this filter' : 'Click to select them again') + '">' + esc(f.label) + '</button>';
              }).join('')
            : '<span class="xb-chip static">No filters — everyone in this list</span>');

        var groups = {};
        S.rows.forEach(function (r) {
            var g = r.group || '—';
            groups[g] = groups[g] || { total: 0, sel: 0, can: 0 };
            groups[g].total++;
            if (addr(r)) groups[g].can++;
            if (isOn(r)) groups[g].sel++;
        });
        var order = cfg.groupOrder || [];
        var names = Object.keys(groups).sort(function (a, b) {
            var ia = order.indexOf(a), ib = order.indexOf(b);
            if (ia > -1 || ib > -1) return (ia < 0 ? 99 : ia) - (ib < 0 ? 99 : ib);
            return groups[b].total - groups[a].total;
        });
        var menu = el('xbPickMenu'), keepScroll = menu ? menu.scrollTop : 0;
        el('xbGroups').innerHTML = '<b>Select by ' + esc((cfg.groupLabel || 'group').toLowerCase()) + '</b>' + pickerHtml(names, groups) + names.map(function (g) {
            var c = groups[g];
            var state = c.sel === 0 ? ' off' : (c.sel < c.can ? ' part' : '');
            return '<button type="button" class="xb-chip' + state + '"' + dis + ' data-group="' + esc(g) + '" onclick="XBulk.toggleGroup(this.getAttribute(\'data-group\'))" title="' + (c.sel ? 'Click to deselect all ' + esc(g) : 'Click to select all ' + esc(g)) + '">' +
                esc(g) + ' <small>' + c.sel + '/' + c.total + '</small></button>';
        }).join('');
        // Tick boxes show "some selected" as indeterminate; keep the open menu's scroll position.
        document.querySelectorAll('#xbPickMenu input[data-part="1"]').forEach(function (i) { i.indeterminate = true; });
        if (el('xbPickMenu')) el('xbPickMenu').scrollTop = keepScroll;
    }

    /* "Choose plans ▾" — a scrollable checklist of presets (cfg.groupPresets, e.g. Any paid plan)
       and every group, each with a tick box. Ticked (violet) = selected, unticked (red) = not. */
    function groupState(list, groups) {
        var sel = 0, can = 0;
        list.forEach(function (g) { if (groups[g]) { sel += groups[g].sel; can += groups[g].can; } });
        return sel === 0 ? 'off' : (sel < can ? 'part' : 'on');
    }
    function pickerHtml(names, groups) {
        var cfg = S.cfg, dis = S.sending ? ' disabled' : '';
        var item = function (kind, key, label, desc, list) {
            var st = groupState(list, groups);
            var count = list.reduce(function (n, g) { return n + (groups[g] ? groups[g].sel : 0); }, 0) + '/' +
                        list.reduce(function (n, g) { return n + (groups[g] ? groups[g].total : 0); }, 0);
            return '<label class="xb-pick-item ' + st + '"><input type="checkbox"' + (st === 'on' ? ' checked' : '') + (st === 'part' ? ' data-part="1"' : '') + dis +
                ' data-kind="' + kind + '" data-key="' + esc(key) + '" onchange="XBulk.pick(this)">' +
                '<span><b>' + esc(label) + '</b><small>' + (desc ? esc(desc) + ' · ' : '') + count + '</small></span></label>';
        };
        var presets = (cfg.groupPresets || []).map(function (p, i) {
            var list = p.groups ? p.groups.filter(function (g) { return groups[g]; }) : names;
            return list.length ? item('preset', i, p.label, p.desc, list) : '';
        }).join('');
        var each = names.map(function (g) { return item('group', g, g, (cfg.groupDesc || {})[g] || '', [g]); }).join('');
        var picked = names.filter(function (g) { return groups[g].sel > 0; }).length;
        return '<span class="xb-pick">' +
            '<button type="button" class="xb-pick-btn"' + dis + ' onclick="XBulk.pickOpen()" aria-expanded="' + (S.pickOpen ? 'true' : 'false') + '">Choose ' + esc((cfg.groupLabel || 'group').toLowerCase()) + 's ▾ <small>' + picked + ' of ' + names.length + '</small></button>' +
            (S.pickOpen ? '<div class="xb-pick-menu" id="xbPickMenu" role="group">' + presets +
                (presets ? '<div class="xb-pick-sep">By ' + esc((cfg.groupLabel || 'group').toLowerCase()) + '</div>' : '') + each + '</div>' : '') +
            '</span>';
    }
    function pickOpen() { S.pickOpen = !S.pickOpen; renderChips(); }
    function pick(input) {
        var list;
        if (input.getAttribute('data-kind') === 'preset') {
            var p = S.cfg.groupPresets[+input.getAttribute('data-key')];
            list = p.groups || null;
        } else {
            list = [input.getAttribute('data-key')];
        }
        var rows = S.rows.filter(function (r) { return !list || list.indexOf(r.group || '—') > -1; });
        setRows(rows, input.checked);
        renderList(); refresh();
    }
    document.addEventListener('click', function (e) {
        if (S && S.pickOpen && !e.target.closest('.xb-pick')) { S.pickOpen = false; renderChips(); }
    });

    function renderList() {
        var rows = visible();
        var list = el('xbList');
        if (!rows.length) { list.innerHTML = '<p class="xb-empty">No customers match this search.</p>'; return; }
        var gc = S.cfg.groupClass;
        list.innerHTML = rows.map(function (r) {
            var a = addr(r);
            return '<label class="xb-row' + (a ? '' : ' na') + '">' +
                '<input type="checkbox"' + (isOn(r) ? ' checked' : '') + (a && !S.sending ? '' : ' disabled') + ' onchange="XBulk.tick(\'' + esc(r.id) + '\', this.checked)">' +
                '<span class="xb-n"><b>' + esc(r.name) + '</b><small>' + (a ? esc(a) : (S.channel === 'email' ? 'No email on file' : 'No phone on file')) + '</small></span>' +
                '<span><span class="' + esc(gc ? gc(r.group) : 'xb-g') + '">' + esc(r.group || '—') + '</span></span>' +
                '<span class="xb-d">' + esc(r.date || '') + '<small>' + esc(r.dateSub || '') + '</small></span>' +
            '</label>';
        }).join('');
    }

    function refresh() {
        if (!S || !el('xbCount')) return;
        var rec = recipients(), vis = visible();
        var noAddr = S.rows.filter(function (r) { return !addr(r); }).length;
        el('xbCount').innerHTML = '<b>' + rec.length.toLocaleString() + '</b> of ' + S.rows.length.toLocaleString() + ' selected' +
            (S.query ? ' · showing ' + vis.length.toLocaleString() : '') +
            (noAddr ? ' · <span style="color:#b45309">' + noAddr + ' without ' + (S.channel === 'email' ? 'an email' : 'a phone') + '</span>' : '');
        var email = S.channel === 'email';
        el('xbSubject').style.display = email ? '' : 'none';
        el('xbSubjectLbl').style.display = email ? '' : 'none';
        if (!S.sending && !S.results) {
            var ready = rec.length > 0 && el('xbMsg').value.trim() && (!email || el('xbSubject').value.trim());
            el('xbSend').disabled = !ready;
            el('xbSend').textContent = 'Send ' + (email ? 'email' : 'WhatsApp') + ' to ' + rec.length.toLocaleString() + ' customer' + (rec.length === 1 ? '' : 's');
        }
        renderChips();
    }

    function toggleFilter(i) {
        var f = S.cfg.filters[i];
        var on = S.rows.some(function (r) { return f.test(r) && isOn(r); });
        if (on) {
            setRows(S.rows.filter(f.test), false);
        } else {
            var others = S.cfg.filters.filter(function (o, j) { return j !== i; });
            setRows(S.rows.filter(function (r) { return f.test(r) && others.every(function (o) { return o.test(r); }); }), true);
        }
        renderList(); refresh();
    }
    function toggleGroup(g) {
        var rows = S.rows.filter(function (r) { return (r.group || '—') === g; });
        setRows(rows, !rows.some(isOn));
        renderList(); refresh();
    }
    function tick(id, on) {
        var r = S.rows.find(function (x) { return String(x.id) === String(id); });
        if (r) setRows([r], on);
        refresh();
    }
    function select(on) { setRows(visible(), on); renderList(); refresh(); }
    function search(q) { S.query = q; renderList(); refresh(); }
    function channel(c) {
        if (S.sending) return;
        S.channel = c;
        document.querySelectorAll('.xb-seg button').forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-ch') === c); });
        renderList(); refresh();
    }
    function template(key) {
        var t = (S.cfg.templates || []).find(function (x) { return x.key === key; });
        if (!t) return;
        el('xbSubject').value = t.subject || '';
        el('xbMsg').value = t.body || '';
        refresh();
    }

    function send() {
        var rec = recipients();
        var email = S.channel === 'email';
        var subject = el('xbSubject').value, body = el('xbMsg').value;
        if (!rec.length || S.sending || el('xbConfirm')) return;
        confirmSend(rec, email, subject).then(function (ok) {
            if (ok) start(rec, email, subject, body);
        });
    }

    function start(rec, email, subject, body) {
        S.sending = true;
        S.cancel = false;
        S.results = [];
        S.sent = {
            channel: S.channel, subject: email ? subject : '', body: body, at: new Date(), total: rec.length,
            filters: (S.cfg.filters || []).map(function (f) { return f.label; })
        };
        document.querySelectorAll('#xbBody input, #xbBody textarea, #xbBody select, .xb-seg button, .xb-bar button').forEach(function (x) { x.disabled = true; });
        renderChips();
        var btn = el('xbSend'), status = el('xbStatus');
        btn.disabled = true;
        var size = S.cfg.batch || 10, batches = [];
        for (var i = 0; i < rec.length; i += size) batches.push(rec.slice(i, i + size));
        var done = 0;
        var byId = {};
        rec.forEach(function (r) { byId[r.id] = r; });

        function progress() {
            btn.innerHTML = '<span class="xb-spin"></span>Sending ' + done + ' of ' + rec.length + '…';
            status.innerHTML = '<div class="xb-prog"><i style="width:' + Math.round(done / rec.length * 100) + '%"></i></div>' +
                '<button type="button" class="xb-stop" onclick="XBulk.stop(this)">Stop</button>';
        }
        function next(n) {
            if (n >= batches.length || S.cancel) return finish();
            var items = batches[n].map(function (r) {
                return { row: r, subject: email ? S.cfg.fill(subject, r) : '', body: S.cfg.fill(body, r) };
            });
            Promise.resolve(S.cfg.send(S.channel, items, rec.length))
                .then(function (results) {
                    var got = {};
                    (results || []).forEach(function (x) { got[x.id] = x; });
                    batches[n].forEach(function (r) {
                        var x = got[r.id] || { status: 'failed', error: 'No result returned.' };
                        S.results.push({ name: r.name, to: x.to || addr(r), group: r.group, status: x.status, error: x.error || '' });
                    });
                })
                .catch(function () {
                    batches[n].forEach(function (r) { S.results.push({ name: r.name, to: addr(r), group: r.group, status: 'failed', error: 'Could not reach the server.' }); });
                })
                .then(function () { done += batches[n].length; progress(); next(n + 1); });
        }
        function finish() {
            S.sending = false;
            var sent = S.results.filter(function (x) { return x.status === 'sent'; }).length;
            var failed = S.results.length - sent, notSent = rec.length - S.results.length;
            btn.textContent = 'Done';
            status.innerHTML =
                '<div class="xb-done"><b>' + sent + ' sent</b>' + (failed ? ' · <span style="color:#b91c1c">' + failed + ' not sent</span>' : '') +
                (notSent ? ' · ' + notSent + ' skipped (stopped)' : '') + '</div>' +
                '<div class="xb-dl"><button type="button" onclick="XBulk.reportPdf()">Download report (PDF)</button>' +
                '<button type="button" class="alt" onclick="XBulk.reportCsv()">CSV</button></div>';
        }
        progress();
        next(0);
    }
    function stop(b) { S.cancel = true; b.disabled = true; b.textContent = 'Stopping after this batch…'; }

    function fileName(ext) {
        var d = S.sent.at, pad = function (n) { return (n < 10 ? '0' : '') + n; };
        var base = String(S.cfg.fileBase || 'send-to-all').replace(/[^a-z0-9-]+/gi, '-').toLowerCase();
        return base + '-' + S.sent.channel + '-' + d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + '-' + pad(d.getHours()) + pad(d.getMinutes()) + '.' + ext;
    }
    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            var s = document.createElement('script');
            s.src = src; s.onload = resolve; s.onerror = reject;
            document.head.appendChild(s);
        });
    }
    /* PDF via jsPDF + AutoTable (loaded on first use); CSV if they can't load. */
    function reportPdf() {
        var ready = window.jspdf && window.jspdf.jsPDF && window.jspdf.jsPDF.API.autoTable
            ? Promise.resolve()
            : loadScript('https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js')
                .then(function () { return loadScript('https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js'); });
        ready.then(function () {
            var m = S.sent, r = S.results;
            var sent = r.filter(function (x) { return x.status === 'sent'; }).length;
            var doc = new window.jspdf.jsPDF({ unit: 'pt', format: 'a4' });
            var y = 48;
            doc.setFont('helvetica', 'bold'); doc.setFontSize(16);
            doc.text(S.cfg.reportTitle || 'Send to all report', 40, y); y += 22;
            doc.setFont('helvetica', 'normal'); doc.setFontSize(10);
            [
                'Sent on: ' + m.at.toLocaleString(),
                'Channel: ' + (m.channel === 'email' ? 'Email' : 'WhatsApp') + (m.subject ? '   ·   Subject: ' + m.subject : ''),
                'Filters: ' + (m.filters.length ? m.filters.join(' · ') : 'none'),
                'Result: ' + sent + ' sent, ' + (r.length - sent) + ' not sent' + (m.total > r.length ? ', ' + (m.total - r.length) + ' skipped (stopped)' : '') + ' — ' + m.total + ' selected'
            ].forEach(function (l) { doc.splitTextToSize(l, 515).forEach(function (t) { doc.text(t, 40, y); y += 14; }); });
            doc.setFont('helvetica', 'bold'); doc.text('Message', 40, y + 6); y += 20;
            doc.setFont('helvetica', 'normal');
            doc.splitTextToSize(m.body, 515).slice(0, 12).forEach(function (t) { doc.text(t, 40, y); y += 13; });
            doc.autoTable({
                startY: y + 10,
                head: [['#', 'Customer', m.channel === 'email' ? 'Email' : 'Phone', S.cfg.groupLabel || 'Group', 'Status', 'Note']],
                body: r.map(function (x, i) { return [i + 1, x.name || '', x.to || '', x.group || '', x.status, x.error || '']; }),
                styles: { fontSize: 8.5, cellPadding: 4 },
                headStyles: { fillColor: [124, 58, 237] },
                columnStyles: { 0: { cellWidth: 26 }, 4: { cellWidth: 48 } },
                didParseCell: function (d) {
                    if (d.section === 'body' && d.column.index === 4) d.cell.styles.textColor = d.cell.raw === 'sent' ? [22, 163, 74] : [185, 28, 28];
                }
            });
            doc.save(fileName('pdf'));
        }).catch(function () {
            alert('The PDF tool could not load (no internet connection?). Downloading the report as CSV instead.');
            reportCsv();
        });
    }
    function reportCsv() {
        var q = function (v) { return '"' + String(v == null ? '' : v).replace(/"/g, '""') + '"'; };
        var m = S.sent;
        var rows = [['#', 'Customer', m.channel === 'email' ? 'Email' : 'Phone', S.cfg.groupLabel || 'Group', 'Status', 'Note']]
            .concat(S.results.map(function (x, i) { return [i + 1, x.name, x.to, x.group, x.status, x.error]; }));
        var blob = new Blob(['﻿' + rows.map(function (r) { return r.map(q).join(','); }).join('\r\n')], { type: 'text/csv;charset=utf-8' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = fileName('csv');
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
    }

    /* Small "Send to all" button for list headers; onclick is a JS expression. */
    function button(onclick) {
        return '<button type="button" class="xb-open" onclick="' + esc(onclick) + '" data-tip="Email or WhatsApp everyone in this list, using its filters.">' + SEND_ICON + 'Send to all</button>';
    }

    return {
        open: open, close: close, refresh: refresh, toggleFilter: toggleFilter, toggleGroup: toggleGroup,
        tick: tick, select: select, search: search, channel: channel, template: template, pick: pick, pickOpen: pickOpen,
        send: send, stop: stop, reportPdf: reportPdf, reportCsv: reportCsv, button: button, esc: esc
    };
})();
</script>
