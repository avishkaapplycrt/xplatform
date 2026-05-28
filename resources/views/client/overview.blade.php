<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Architecture Overview – {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased">

@php
    $clientName  = $client->company_name ?? $client->name ?? 'Acme Retail';
    $words       = explode(' ', trim($clientName));
    $initials    = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

    $avatarColors = ['#6366f1','#3b82f6','#10b981','#f59e0b','#ec4899','#8b5cf6','#14b8a6'];
    $avatarColor  = $avatarColors[abs(crc32($clientName)) % count($avatarColors)];
@endphp

{{-- Full-viewport two-column layout --}}
<div class="flex h-screen overflow-hidden bg-white">

    {{-- ======================================================
         LEFT SIDEBAR
         ====================================================== --}}
    <aside class="w-[184px] bg-white border-r border-gray-200 flex flex-col h-full flex-shrink-0">

        {{-- Active Tenant --}}
        <div class="px-3 pt-4 pb-3 border-b border-gray-100">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Active Tenant</p>
            <button class="w-full flex items-center justify-between bg-white border border-gray-200 rounded-md px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                <span>{{ $clientName }}</span>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        {{-- Platform Layers Navigation --}}
        <div class="px-3 pt-3 flex-1 overflow-y-auto">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide px-1 mb-2">Platform Layers</p>

            <nav class="space-y-1">
                {{-- Architecture Overview — active on this page --}}
                <a href="{{ route('client.company.overview') }}"
                   class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition
                          bg-teal-50 text-teal-700 font-medium border-l-2 border-teal-400">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 text-teal-500"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3l8 8-8 8-8-8 8-8z"/>
                    </svg>
                    <span class="truncate">Architecture Overview</span>
                </a>

                @php
                $allSidebarLayers = [
                    ['num'=>'L1','label'=>'Data Collection',        'color'=>'text-emerald-600','bg'=>'bg-emerald-50', 'route'=>'client.layer.l1'],
                    ['num'=>'L2','label'=>'Identity Resolution',    'color'=>'text-blue-600',   'bg'=>'bg-blue-50',   'route'=>'client.layer.l2'],
                    ['num'=>'L3','label'=>'Data Processing',        'color'=>'text-violet-600', 'bg'=>'bg-violet-50', 'route'=>'client.layer.l3'],
                    ['num'=>'L4','label'=>'Behavioral Intelligence','color'=>'text-amber-600',  'bg'=>'bg-amber-50',  'route'=>'client.layer.l4'],
                    ['num'=>'L5','label'=>'AI/ML Predictions',      'color'=>'text-orange-600', 'bg'=>'bg-orange-50', 'route'=>'client.layer.l5'],
                    ['num'=>'L6','label'=>'Decision & Actions',     'color'=>'text-green-600',  'bg'=>'bg-green-50',  'route'=>'client.layer.l6'],
                    ['num'=>'L7','label'=>'Application Layer',      'color'=>'text-red-600',    'bg'=>'bg-red-50',    'route'=>'client.layer.l7'],
                    ['num'=>'L8','label'=>'Governance & Security',  'color'=>'text-gray-600',   'bg'=>'bg-gray-100',  'route'=>'client.layer.l8'],
                ];
                $selectedLayerCodes = auth('client')->user()?->analysisLayers()->pluck('code')->toArray() ?? [];
                $sidebarLayers = empty($selectedLayerCodes)
                    ? $allSidebarLayers
                    : array_values(array_filter($allSidebarLayers, fn($l) => in_array($l['num'], $selectedLayerCodes)));
                @endphp

                @foreach($sidebarLayers as $layer)
                    <a href="{{ route($layer['route']) }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition text-gray-500 hover:bg-gray-50 hover:text-gray-900">
                        <span class="text-[10px] font-bold {{ $layer['color'] }} {{ $layer['bg'] }} rounded px-1.5 py-0.5 leading-none flex-shrink-0">
                            {{ $layer['num'] }}
                        </span>
                        <span class="truncate">{{ $layer['label'] }}</span>
                    </a>
                @endforeach

                {{-- RL Engine add-on --}}
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-wide px-1 mb-1.5">Add-on Module</p>
                    <a href="{{ route('client.layer.rl') }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition text-gray-500 hover:bg-gray-50 hover:text-gray-900">
                        <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 rounded px-1.5 py-0.5 leading-none flex-shrink-0">RL</span>
                        <span class="truncate">RL Engine</span>
                    </a>
                </div>
            </nav>
        </div>

        {{-- Status Bar --}}
        <div class="px-3 py-3 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center gap-1.5 mb-1">
                <span class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0"></span>
                <span class="text-[10px] text-gray-700 font-medium">All systems operational</span>
            </div>
            <p class="text-[10px] text-gray-400 pl-3.5">150+ micro-signals active</p>
            <p class="text-[10px] text-gray-400 pl-3.5">{{ $clientName }}</p>
        </div>

    </aside>

    {{-- ======================================================
         MAIN CONTENT
         ====================================================== --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-50">

        {{-- Top bar --}}
        <header class="flex-shrink-0 flex items-center justify-between
                       bg-white border-b border-gray-200 px-6 h-12">
            <div class="flex items-center gap-3 min-w-0">
                <div class="min-w-0">
                    <h1 class="text-sm font-bold text-gray-900 leading-none">Architecture Overview</h1>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Tenant:&nbsp;
                        <a href="{{ route('client.dashboard') }}"
                           class="text-emerald-500 font-medium hover:underline">{{ $clientName }}</a>
                        <span class="inline-flex items-center gap-1 ml-2 font-medium text-emerald-500">
                            <span class="w-1.5 h-1.5 rounded-full inline-block bg-emerald-400"></span>
                            Live
                        </span>
                    </p>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:12px;flex-shrink:0">
                {{-- Home button --}}
                <a href="{{ route('client.dashboard') }}"
                   style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;color:#6b7280;text-decoration:none;border:1px solid #e5e7eb"
                   onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'"
                   title="Home">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>

                {{-- Profiles --}}
                <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:#4b5563">
                    <svg width="14" height="14" fill="none" stroke="#a78bfa" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span style="font-weight:600;color:#374151">8.7M profiles</span>
                </span>

                {{-- Accuracy --}}
                <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:#4b5563">
                    <svg width="14" height="14" fill="none" stroke="#f472b6" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span style="font-weight:600;color:#374151">94.1% accuracy</span>
                </span>

                {{-- Avatar with dropdown --}}
                <div style="position:relative" id="ovAvatarWrap">
                    <button onclick="var d=document.getElementById('ovDropdown');d.style.display=d.style.display==='block'?'none':'block'"
                            style="width:32px;height:32px;border-radius:50%;background:{{ $avatarColor }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;border:none;cursor:pointer">
                        {{ $initials }}
                    </button>
                    <div id="ovDropdown"
                         style="display:none;position:absolute;right:0;top:40px;width:192px;background:#fff;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.12);border:1px solid #e5e7eb;padding:4px 0;z-index:999">
                        <div style="padding:8px 16px;border-bottom:1px solid #f3f4f6">
                            <p style="font-size:12px;font-weight:600;color:#111827;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $clientName }}</p>
                            <p style="font-size:10px;color:#9ca3af;margin:2px 0 0">Client Account</p>
                        </div>
                        <a href="{{ route('client.dashboard') }}"
                           style="display:flex;align-items:center;gap:8px;padding:8px 16px;font-size:12px;color:#374151;text-decoration:none"
                           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                            <svg width="14" height="14" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile Settings
                        </a>
                        <hr style="margin:4px 0;border:none;border-top:1px solid #f3f4f6">
                        <form method="POST" action="{{ route('client.logout') }}">
                            @csrf
                            <button type="submit"
                                    style="width:100%;display:flex;align-items:center;gap:8px;padding:8px 16px;font-size:12px;color:#dc2626;background:none;border:none;cursor:pointer;text-align:left"
                                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                <svg width="14" height="14" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Scrollable body --}}
        <main class="flex-1 overflow-y-auto p-6 space-y-5">

            {{-- ================================================
                 4 KPI CARDS
                 ================================================ --}}
            <div class="grid grid-cols-4 gap-4">

                {{-- 1 · Total Micro-Signals --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor"
                                 stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1">
                        Total Micro-Signals
                    </p>
                    <p class="text-3xl font-bold text-emerald-500 leading-none">150+</p>
                    <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
                        <span class="text-emerald-500">▲</span> Across all 8 layers
                    </p>
                </div>

                {{-- 2 · Events / Day --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1">
                        Events / Day
                    </p>
                    <p class="text-3xl font-bold leading-none" style="color:#3b82f6">4.2M</p>
                    <p class="text-[11px] mt-1.5 flex items-center gap-1">
                        <span class="text-emerald-500">▲ 18%</span>
                        <span class="text-gray-400">WoW</span>
                    </p>
                </div>

                {{-- 3 · Profiles Resolved --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor"
                                 stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1">
                        Profiles Resolved
                    </p>
                    <p class="text-3xl font-bold text-violet-500 leading-none">8.7M</p>
                    <p class="text-[11px] mt-1.5">
                        <span class="text-emerald-500">▲ 98.2%</span>
                        <span class="text-gray-400"> match rate</span>
                    </p>
                </div>

                {{-- 4 · Active Tenants --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                 stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1">
                        Active Tenants
                    </p>
                    <p class="text-3xl font-bold text-amber-500 leading-none">4</p>
                    <p class="text-[11px] mt-1.5">
                        <span class="text-emerald-500">▲ 2</span>
                        <span class="text-gray-400"> added this Q</span>
                    </p>
                </div>

            </div>

            {{-- ================================================
                 PLATFORM DATA FLOW
                 ================================================ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                <div class="flex items-center justify-between px-6 py-3.5 border-b border-gray-100">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-[0.12em]">
                        Platform Data Flow
                        <span class="text-gray-300 font-normal mx-1">•</span>
                        Click to drill into any layer
                    </p>
                </div>

                <div class="divide-y divide-gray-50">
                    @foreach($allLayers as $layer)
                        @php
                            $href = match($layer['code']) {
                                'L1' => route('client.layer.l1'),
                                'L2' => route('client.layer.l2'),
                                'L3' => route('client.layer.l3'),
                                'L4' => route('client.layer.l4'),
                                'L5' => route('client.layer.l5'),
                                'L6' => route('client.layer.l6'),
                                'L7' => route('client.layer.l7'),
                                'L8' => route('client.layer.l8'),
                                default => '#',
                            };
                        @endphp
                        <a href="{{ $href }}"
                           class="group flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 transition">

                            {{-- Layer code --}}
                            <span class="text-[11px] font-bold w-6 text-center flex-shrink-0"
                                  style="color:{{ $layer['color'] }}">
                                {{ $layer['code'] }}
                            </span>

                            {{-- Layer name --}}
                            <span class="text-sm font-semibold text-gray-800 w-48 flex-shrink-0 truncate">
                                {{ $layer['name'] }}
                            </span>

                            {{-- Stats text --}}
                            <span class="flex-1 text-xs text-gray-400 truncate">
                                {{ $layer['summary'] }}
                            </span>

                            {{-- Health dots (6 circles) --}}
                            <div class="flex items-center gap-1 flex-shrink-0">
                                @for($i = 1; $i <= 6; $i++)
                                    @if($i <= $layer['dots'])
                                        <span class="w-2 h-2 rounded-full inline-block"
                                              style="background:{{ $layer['color'] }}"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full inline-block border"
                                              style="border-color:{{ $layer['color'] }};opacity:0.25"></span>
                                    @endif
                                @endfor
                            </div>

                            {{-- Arrow button --}}
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center
                                         text-white flex-shrink-0 transition opacity-90
                                         group-hover:opacity-100"
                                  style="background:{{ $layer['color'] }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                     stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>

                        </a>
                    @endforeach
                </div>

            </div>

            {{-- ================================================
                 BOTTOM TENANT MINI-CARDS
                 ================================================ --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($allClients as $tenant)
                    <div class="bg-white rounded-xl border shadow-sm p-4 transition
                                {{ $tenant['name'] === $clientName ? 'border-indigo-300 ring-1 ring-indigo-200' : 'border-gray-100 hover:border-gray-200' }}">

                        {{-- Card header: name + plan badge --}}
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold text-gray-800 truncate">
                                {{ $tenant['name'] }}
                            </span>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded flex-shrink-0"
                                  style="background:{{ $tenant['plan_bg'] }};color:{{ $tenant['plan_text'] }}">
                                {{ $tenant['plan'] }}
                            </span>
                        </div>

                        {{-- Layer activity bar — 8 colored segments --}}
                        <div class="flex gap-px mb-3">
                            @foreach($allLayers as $layer)
                                <div class="h-1 flex-1 rounded-sm"
                                     style="background:{{ $layer['color'] }}"></div>
                            @endforeach
                        </div>

                        {{-- Stats line --}}
                        <p class="text-[10px] text-gray-400 leading-relaxed">
                            All 8 Layers
                            &nbsp;•&nbsp;
                            150+ signals active
                        </p>

                    </div>
                @endforeach


            </div>

        </main>
    </div>

</div>

<script>
document.addEventListener('click', function(e) {
  var wrap = document.getElementById('ovAvatarWrap');
  var drop = document.getElementById('ovDropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
</body>
</html>
