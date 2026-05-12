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
    <aside class="w-[220px] flex-shrink-0 flex flex-col border-r border-gray-200 bg-white">

        {{-- Brand --}}
        <div class="flex items-center h-12 px-4 border-b border-gray-100 flex-shrink-0">
            <a href="{{ route('client.dashboard') }}"
               class="text-sm font-bold text-indigo-600 tracking-tight truncate">
                {{ config('app.name') }}
            </a>
        </div>

        {{-- Active Tenant selector --}}
        <div class="px-3 pt-4 pb-3 flex-shrink-0">
            <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-[0.12em] mb-1.5">
                Active Tenant
            </p>
            <div class="w-full flex items-center justify-between gap-2 px-3 py-2
                        bg-gray-50 border border-gray-200 rounded-lg">
                <span class="text-xs font-semibold text-gray-800 truncate">{{ $clientName }}</span>
                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>

        {{-- Platform Layers nav --}}
        <div class="px-3 pb-1 flex-shrink-0">
            <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-[0.12em]">
                Platform Layers
            </p>
        </div>

        <nav class="flex-1 overflow-y-auto px-2 pb-2 space-y-0.5">

            {{-- Architecture Overview — active --}}
            <a href="{{ route('client.company.overview') }}"
               class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg border-l-2 border-emerald-400
                      bg-emerald-50 text-emerald-700 font-semibold text-xs">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Architecture Overview
            </a>

            {{-- L1–L8 sidebar items --}}
            @foreach($allLayers as $layer)
                @php
                    $layerRoute = match($layer['code']) {
                        'L1' => route('client.layer.l1'),
                        default => '#',
                    };
                @endphp
                <a href="{{ $layerRoute }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg
                          hover:bg-gray-50 transition {{ $layerRoute === '#' ? 'opacity-50 cursor-default' : '' }}">
                    <span class="text-[10px] font-bold w-5 text-center flex-shrink-0"
                          style="color:{{ $layer['color'] }}">{{ $layer['code'] }}</span>
                    <span class="text-xs text-gray-600 truncate">{{ $layer['name'] }}</span>
                </a>
            @endforeach

        </nav>

        {{-- Sidebar footer --}}
        <div class="px-4 py-3 border-t border-gray-100 flex-shrink-0 space-y-1">
            <p class="flex items-center gap-1.5 text-[10px] text-gray-500">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block flex-shrink-0"></span>
                All systems operational
            </p>
            <p class="text-[10px] text-gray-400">150+ micro-signals active</p>
            <p class="text-[10px] text-gray-400 truncate">• {{ $clientName }}</p>
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

            <div class="flex items-center gap-5 flex-shrink-0">
                {{-- Profiles resolved --}}
                <div class="flex items-center gap-1.5 text-[11px] text-gray-500">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="font-semibold text-gray-700">8.7M profiles</span>
                </div>

                {{-- Accuracy --}}
                <div class="flex items-center gap-1.5 text-[11px] text-gray-500">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-semibold text-gray-700">94.1% accuracy</span>
                </div>

                {{-- Avatar --}}
                <div class="w-8 h-8 rounded-full flex items-center justify-center
                            text-white text-xs font-bold flex-shrink-0"
                     style="background:{{ $avatarColor }}">{{ $initials }}</div>
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
                                default => '#',
                            };
                        @endphp
                        <a href="{{ $href }}"
                           class="group flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 transition {{ $href === '#' ? 'cursor-default opacity-70' : '' }}">

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

</body>
</html>
