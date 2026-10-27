{{--
    Mock Master Helper — Subscription + date-range filter bar for one Sales / Retention list,
    with Apply, Reset and Send to all. Same look as the Campaign tab's filter bar.

    Expects:
      $dataset   — list key for mock-master-helper/list-audience (e.g. chRenewals)
      $kind      — row layout used when re-drawing the table (today, accounts, savefirst,
                   watchlist, convert, abandoned, renewals, winback)
      $subLabel  — label for the subscription select (e.g. "Subscription", "Course interest")
      $dateLabel — what the dates filter on (e.g. "Expires", "Checkout", "Joined")
--}}
<div class="mm-filter-bar mm-lf" data-dataset="{{ $dataset }}" data-kind="{{ $kind }}" data-date-label="{{ $dateLabel }}" data-sub-label="{{ $subLabel }}" onmouseenter="mmLfOptions(this)" onfocusin="mmLfOptions(this)" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;padding:12px 14px;border-bottom:1px solid var(--ln);background:var(--p1)">
    <label style="display:flex;flex-direction:column;gap:4px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--g3)">{{ $subLabel }}
        <select name="subscription" style="min-width:220px;padding:6px 8px;border:1px solid var(--ln);border-radius:6px;font-size:12px;background:#fff">
            <option value="">All</option>
        </select>
    </label>
    <label style="display:flex;flex-direction:column;gap:4px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--g3)">{{ $dateLabel }} from
        <input type="date" name="from" style="padding:6px 8px;border:1px solid var(--ln);border-radius:6px;font-size:12px;background:#fff">
    </label>
    <label style="display:flex;flex-direction:column;gap:4px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--g3)">{{ $dateLabel }} to
        <input type="date" name="to" style="padding:6px 8px;border:1px solid var(--ln);border-radius:6px;font-size:12px;background:#fff">
    </label>
    <button type="button" onclick="mmListFilterApply(this)" style="padding:7px 14px;border-radius:6px;border:none;background:#7c3aed;color:#fff;font-size:12px;font-weight:600;cursor:pointer">Apply</button>
    <button type="button" onclick="mmListFilterReset(this)" style="padding:7px 14px;border-radius:6px;border:1px solid var(--ln);color:var(--g3);font-size:12px;font-weight:600;cursor:pointer;background:#fff">Reset</button>
    <button type="button" onclick="mmBulkOpenList('{{ $dataset }}', this)" data-tip="Email or WhatsApp everyone in this list, using these filters and the Plan and number filters on the columns." style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:6px;border:1px solid #7c3aed;color:#7c3aed;font-size:12px;font-weight:600;cursor:pointer;background:#fff">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>Send to all</button>
    <span class="mm-lf-info" style="font-size:11.5px;color:var(--g3);align-self:center"></span>
</div>
