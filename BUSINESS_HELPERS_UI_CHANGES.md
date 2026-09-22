# Business Helpers — visual-only changes (21 Sep 2026)

Everything below is **purely visual/interaction polish** on
`resources/views/client/business-helpers.blade.php`. Nothing about how
Marketing, Sales, or Customer Retention actually fetch, compute, or render
their answers was touched — no service class, controller, or route was
edited. If any of this should be reverted, just tell Claude to undo the
items below (or hand this file back) and it can be removed cleanly, since
every change is additive (new CSS rules / new HTML elements / new JS
functions appended at the end of the script) rather than a rewrite of
existing logic.

## 1. Left "Steps" panel can now be drag-resized

Previously only the right-hand "helper" (Mira) panel could be dragged
wider/narrower. The left column (the numbered step list — Prioritise,
Understand, Pitch… / 1, 2, 3…) had a fixed width.

- Added a drag handle (`#bhLeftResize`, same grip icon as the existing one)
  to the left edge of `.dash-left`.
- Its width is now driven by a new CSS variable, `--bh-left-w` (default
  `220px`, minimum `170px`), mirroring how `--bh-mira-w` already works for
  the right panel.
- Drag it, or focus it and use the arrow keys; double-click resets it to
  the default width. The size is remembered per browser via
  `localStorage` (`bhLeft`), same as the Mira panel already does.
- New JS: `BH_LEFT_MIN`, `BH_LEFT_DEFAULT`, `bhLeftState`, `bhLeftCap()`,
  `bhLeftApply()`, `bhLeftSave()`, `bhLeftLoad()`, and the `bhLeftDrag()`
  IIFE — all new functions, copied in shape from the existing
  `bhMira*`/`bhMiraDrag()` functions, none of the originals were edited.

## 2. A sliding indicator glides under the active tab

- Applies to the top **Marketing / Sales / Customer Retention** tabs, and
  to the **Prioritise / Understand / Pitch / Overcome / Close & Grow**
  sub-tabs underneath them.
- A thin animated bar (`.bh-slide-ind`) now slides smoothly to whichever
  tab is active, instead of the active state just changing instantly.
- Implemented as a small observer (`bhMountSlideIndicator()`) that watches
  for the existing `.on` class (the class the app already uses to mark an
  active tab) and repositions the bar under it. It does not call, wrap, or
  modify `setAgent()` or `showDashView()` — it only reacts to what those
  functions already put on the page.

## 3. Panel content fades in on every switch

- Whenever the middle answer panel (`#dashView`), the left step guide
  (`#dashGBody`), or the chat feed (`#bhChat`) gets new content, it now
  fades and rises in slightly (`.bh-fade-in`, ~0.3s) instead of appearing
  instantly.
- Implemented via `bhAnimateOnChange()`, a `MutationObserver` per
  container that toggles the CSS class after the fact. It does not touch
  any of the functions that actually build that content.
- Respects `prefers-reduced-motion` — the fade and the sliding indicator
  both turn off if the visitor's OS has reduced motion enabled.

## 4. General motion polish

- Smoothed the easing curve (`cubic-bezier(.4,0,.2,1)`) on existing hover/
  active transitions for tabs, contact-stack rows, quick-reply chips, and
  the helper-panel buttons — same properties and similar durations as
  before, just less abrupt.
- Contact-stack rows (`.stkrow`) now nudge slightly right on hover
  (`translateX(2px)`) as a small "this is clickable" cue.

## 5. A third drag handle — between the chat and the suggestions list

- Added a horizontal splitter (`#bhQuickResize`) between the dashboard chat
  feed (`#dashChat`) and the "Ask Mira · [step]" suggestions list
  (`#dashQuickHd` / `#dashQuick`), for all three agents.
- Drag it up/down to resize how much space the suggestions list takes;
  double-click or arrow keys (focus it first) reset it to the default
  height. Height is remembered via `localStorage` (`bhQuick`).
- New CSS var: `--bh-quick-h` (default `220px`, minimum `90px`), applied to
  `.dm-quick`'s existing `max-height`.
- New JS: `BH_QUICK_MIN`, `BH_QUICK_DEFAULT`, `bhQuickState`,
  `bhQuickApply()`, `bhQuickSave()`, `bhQuickLoad()`, and the
  `bhQuickDrag()` IIFE.

## 6. All three drag handles now fully collapse — "VS Code style"

Previously every handle just clamped at a minimum size; it couldn't hide
a panel completely. Now, dragging any of the three past a small threshold
snaps it fully closed, the same gesture as VS Code's sidebar border —
and dragging back out past that same line reopens it.

- **Left steps panel** — drag it thin enough and it collapses to a 40px
  rail with a "Steps" label and a `»` button to reopen (mirrors the
  helper panel's existing collapsed-rail look exactly). New: the
  `bh-left-min` state/class, the `bhLeft(action)` function, and the
  `.dash-left`'s own `.col-rail` block (copied from the Mira panel's).
- **Right helper panel** — same gesture now also collapses it, not just
  its existing minimise (−) button; dragging it back out reopens it. Its
  resize handle is now excluded from the "hide everything" rule when
  collapsed (previously the handle itself vanished when minimised via the
  button, so you could only reopen via the button — now the handle stays
  put and draggable either way).
- **Chat/suggestions splitter** — drag it down far enough and it calls the
  app's own existing `collapseDashQuicks()` (the same function the little
  chevron button already calls) — no new "hidden" state was invented, it
  reuses exactly what was already there. Drag back up and it calls the
  existing `expandDashQuicks()`.

## Files touched

- `resources/views/client/business-helpers.blade.php` — the only file
  changed. New CSS appended near the existing Mira-panel resize styles;
  new HTML is one small `<div>` inside `.dash-left`; new JS is appended at
  the end of the existing `<script>` block, right before the final
  `setAgent('mk'); bhMiraLoad();` calls (which now also call the new
  `bhLeftLoad();`).
- This file (`BUSINESS_HELPERS_UI_CHANGES.md`) — new, for your reference.

## Verified before publishing

- The Blade view still compiles and renders (`view(...)->render()`
  succeeded with real data).
- The full inline `<script>` block still parses as valid JavaScript
  (`node --check`).
- No changes were made to any `Retention*Service`, `Marketing*Service`,
  `Sales*Service`, controller, or route.
- Re-verified after adding items 5 and 6: view still renders, main
  `<script>` block (161KB after these additions) still passes
  `node --check`.
