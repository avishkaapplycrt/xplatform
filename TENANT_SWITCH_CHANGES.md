# Active Tenant switch — change record

Date: 20 Oct 2026 · Files changed: 2 · Not committed yet

## What it does

The **Active Tenant** box at the top of the sidebar (`resources/views/layouts/platform.blade.php`)
is now a dropdown with two tenants:

| Tenant         | Sidebar shows          | Opens                          |
|----------------|------------------------|--------------------------------|
| Mock Master    | Mock Master Helper     | `/app/mock-master-helper`      |
| Test Account   | Business Helpers       | `/app/business-helpers`        |

Everything below them in the sidebar (Platform Layers, Revenue Optimization, Customer Success,
Reports, Email Settings, Add-on Module…) stays the same for both tenants.

How the active tenant is decided:
1. On the Mock Master Helper page → **Mock Master**; on the Business Helpers page → **Test Account**.
2. On any other page → the last tenant picked in the dropdown (kept in the session as `active_tenant`).
3. Nothing picked yet → **Test Account**.

The box used to show the client's company name ("Test Company") and did nothing when clicked.

## Changes

### 1. `routes/web.php` — new route (6 lines added)

Added inside the client group (prefix `app`, names `client.`), just above the `business-helpers` route:

```php
// Active Tenant dropdown (layouts/platform): remember the choice, then open that tenant's helper.
Route::get('tenant/{tenant}', function (string $tenant) {
    session(['active_tenant' => $tenant]);

    return redirect()->route($tenant === 'mockmaster' ? 'client.mock-master-helper' : 'client.business-helpers');
})->whereIn('tenant', ['mockmaster', 'test'])->name('tenant.switch');
```

URL: `/app/tenant/mockmaster` or `/app/tenant/test` · route name `client.tenant.switch`.

### 2. `resources/views/layouts/platform.blade.php` — sidebar

- The Active Tenant `<button>` became a dropdown (uses the layout's existing `xpDd()` helper and
  `data-dd-wrap` / `data-dd-menu` pattern, so it closes when clicking elsewhere).
- A small `@php` block decides `$activeTenant`.
- The **Business Helpers** link is wrapped in `@if ($activeTenant === 'test') … @endif`.
- The **Mock Master Helper** link is wrapped in `@if ($activeTenant === 'mockmaster') … @endif`,
  and its top padding changed from `pt-1` to `pt-3` because it is now the first item under the box.

Nothing else in the layout, the helper pages or any controller changed.

## How to revert

**Before committing** (undo both files exactly):

```bash
git checkout -- routes/web.php resources/views/layouts/platform.blade.php
```

**After committing** (replace `<commit>` with the commit id):

```bash
git revert <commit>
```

**By hand:** remove the route above from `routes/web.php`, and in `platform.blade.php` put back the
original Active Tenant block and remove the two `@if` / `@endif` pairs (original code is in the
diff below, lines starting with `-`).

## Full diff

```diff
diff --git a/resources/views/layouts/platform.blade.php b/resources/views/layouts/platform.blade.php
index 18f5850..a87351e 100644
--- a/resources/views/layouts/platform.blade.php
+++ b/resources/views/layouts/platform.blade.php
@@ -26,19 +26,41 @@
     {{-- Left Sidebar --}}
     <aside id="platformSidebar" class="w-[200px] bg-white border-r border-gray-200 flex flex-col h-full flex-shrink-0 transition-all duration-200">
 
-        {{-- Active Tenant --}}
+        {{-- Active Tenant: "Mock Master" shows Mock Master Helper, "Test Account" shows Business Helpers.
+             The helper page you are on decides first, then the last choice (route client.tenant.switch). --}}
+        @php
+            $tenants = ['mockmaster' => 'Mock Master', 'test' => 'Test Account'];
+            $activeTenant = request()->routeIs('client.mock-master-helper*') ? 'mockmaster'
+                : (request()->routeIs('client.business-helpers*') ? 'test' : session('active_tenant', 'test'));
+        @endphp
         <div class="px-3 pt-4 pb-3 border-b border-gray-100">
             <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Active Tenant</p>
 
-            <button
-                class="w-full flex items-center justify-between bg-white border border-gray-200 rounded-md px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
-                <span>{{ auth('client')->user()?->company_name ?? 'Acme Retail' }}</span>
-                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
-                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
-                </svg>
-            </button>
+            <div class="relative" data-dd-wrap>
+                <button type="button" onclick="xpDd('tenantMenu')" aria-haspopup="menu"
+                    class="w-full flex items-center justify-between bg-white border border-gray-200 rounded-md px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
+                    <span>{{ $tenants[$activeTenant] }}</span>
+                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
+                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
+                    </svg>
+                </button>
+                <div id="tenantMenu" data-dd-menu role="menu" style="display:none"
+                     class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-50 py-1">
+                    @foreach ($tenants as $key => $label)
+                        <a href="{{ route('client.tenant.switch', $key) }}" role="menuitem"
+                           class="flex items-center justify-between px-3 py-2 text-xs transition
+                                  {{ $activeTenant === $key ? 'text-teal-700 font-medium bg-teal-50' : 'text-gray-600 hover:bg-gray-50' }}">
+                            <span>{{ $label }}</span>
+                            @if ($activeTenant === $key)
+                                <svg class="w-3.5 h-3.5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
+                            @endif
+                        </a>
+                    @endforeach
+                </div>
+            </div>
         </div>
 
+        @if ($activeTenant === 'test')
         {{-- Business Helpers --}}
         <div class="px-3 pt-3">
             <a href="{{ route('client.business-helpers') }}"
@@ -54,9 +76,11 @@ class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition
                 <span class="truncate">Business Helpers</span>
             </a>
         </div>
+        @endif
 
+        @if ($activeTenant === 'mockmaster')
         {{-- Mock Master Helper --}}
-        <div class="px-3 pt-1">
+        <div class="px-3 pt-3">
             <a href="{{ route('client.mock-master-helper') }}"
                class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition
                       {{ request()->routeIs('client.mock-master-helper')
@@ -70,6 +94,7 @@ class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition
                 <span class="truncate">Mock Master Helper</span>
             </a>
         </div>
+        @endif
 
         {{-- Platform Layers Navigation --}}
         <div class="px-3 pt-3 flex-1 overflow-y-auto">
diff --git a/routes/web.php b/routes/web.php
index d08669c..4d2ecba 100644
--- a/routes/web.php
+++ b/routes/web.php
@@ -323,6 +323,12 @@
     Route::get('layers/rl',      fn() => view('client.layers.rl'))->name('layer.rl');
     Route::get('architecture',   fn() => view('client.architecture-overview'))->name('architecture');
     Route::get('data-collection',fn() => view('client.data-collection.mobile_events', array_merge(EmailLog::deliveryStats(), CallLog::callStats(), \App\Models\InstagramMedia::socialStats())))->name('data-collection');
+    // Active Tenant dropdown (layouts/platform): remember the choice, then open that tenant's helper.
+    Route::get('tenant/{tenant}', function (string $tenant) {
+        session(['active_tenant' => $tenant]);
+
+        return redirect()->route($tenant === 'mockmaster' ? 'client.mock-master-helper' : 'client.business-helpers');
+    })->whereIn('tenant', ['mockmaster', 'test'])->name('tenant.switch');
     Route::get('business-helpers', function () {
         $groupPrompts = fn (string $agent) => \App\Models\AgentPredefinedPrompt::forAgent($agent)
             ->where('is_mock_master', false)
```

## Update — 26 Oct 2026: "Test Account" became "Admin Account"

| Tenant         | Sidebar shows                               | Dropdown opens             |
|----------------|---------------------------------------------|----------------------------|
| Admin Account  | Business Helpers **and** Mock Master Helper | `/app/business-helpers`    |
| Mock Master    | Mock Master Helper only                     | `/app/mock-master-helper`  |

- `platform.blade.php`: tenant key `test` → `admin` (label "Admin Account"); the active tenant now comes only
  from the session (default `admin`), so opening Mock Master Helper as admin stays on Admin Account; the
  Mock Master Helper link is always shown, Business Helpers only for `admin`.
- `routes/web.php`: `tenant/{tenant}` accepts `mockmaster` / `admin` (the old `/app/tenant/test` now 404s);
  `business-helpers` redirects to Mock Master Helper while the Mock Master tenant is active.

Revert this update only: `git diff` / `git checkout -- routes/web.php resources/views/layouts/platform.blade.php`
before committing, or `git revert <commit>` after.
