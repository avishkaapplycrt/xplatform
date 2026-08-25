<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>L1 – Data Collection – {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .tab-active   { border-bottom: 2px solid #111827; color: #111827; font-weight: 600; }
        .tab-inactive { border-bottom: 2px solid transparent; color: #9ca3af; font-weight: 500; }
        .source-card-default  { border: 1px solid #e5e7eb; }
        .source-card-selected { border: 2px solid #10b981; background: #f0fdf4; }
    </style>
</head>
<body class="bg-white font-sans antialiased overflow-hidden">

@php
    $clientName  = $client->company_name ?? $client->name ?? 'Acme Retail';
    $words       = explode(' ', trim($clientName));
    $initials    = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
    $avatarColors = ['#6366f1','#3b82f6','#10b981','#f59e0b','#ec4899','#8b5cf6','#14b8a6'];
    $avatarColor  = $avatarColors[abs(crc32($clientName)) % count($avatarColors)];

    $sentimentItems = [
        ['label' => 'Credit card',        'pct' => 52, 'color' => '#10b981'],
        ['label' => 'Apple / Google pay', 'pct' => 24, 'color' => '#3b82f6'],
        ['label' => 'Paypal',             'pct' => 14, 'color' => '#8b5cf6'],
        ['label' => 'bank transfer',      'pct' => 55, 'color' => '#f59e0b'],
        ['label' => 'Buy Now pay later',  'pct' => 66, 'color' => '#ec4899'],
    ];
@endphp

{{-- ================================================================
     FULL-PAGE WRAPPER
     ================================================================ --}}
<div class="flex flex-col h-screen overflow-hidden">

    {{-- Two-column layout fills the rest of the viewport --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ============================================================
             LEFT SIDEBAR
             ============================================================ --}}
        <aside class="w-[220px] flex-shrink-0 flex flex-col border-r border-gray-200 bg-white">

            {{-- Brand --}}
            <div class="flex items-center h-12 px-4 border-b border-gray-100 flex-shrink-0">
                <a href="{{ route('client.dashboard') }}"
                   class="text-sm font-bold text-indigo-600 tracking-tight truncate">
                    {{ config('app.name') }}
                </a>
            </div>

            {{-- Active Tenant --}}
            <div class="px-3 pt-4 pb-2 flex-shrink-0">
                <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-[0.12em] mb-1.5">
                    Active Tenant
                </p>
                <button class="w-full flex items-center justify-between gap-2 px-3 py-2
                               bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg text-left transition">
                    <span class="text-xs font-semibold text-gray-800 truncate">{{ $clientName }}</span>
                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            {{-- Platform Layers label --}}
            <div class="px-3 pb-1 flex-shrink-0">
                <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-[0.12em]">
                    Platform Layers
                </p>
            </div>

            <nav class="flex-1 overflow-y-auto px-2 pb-2 space-y-0.5">

                {{-- Architecture Overview — inactive --}}
                <a href="{{ route('client.company.overview') }}"
                   class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg
                          hover:bg-gray-50 text-gray-500 text-xs transition">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Architecture Overview
                </a>

                {{-- L1 — ACTIVE --}}
                <div class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg border-l-2 border-emerald-400
                            bg-emerald-50 text-emerald-700 text-xs font-semibold cursor-default">
                    <span class="text-[10px] font-bold w-5 text-center flex-shrink-0 text-emerald-500">L1</span>
                    <span class="truncate">Data Collection</span>
                </div>

                {{-- L2–L8 --}}
                @foreach($allLayers as $layer)
                    @if($layer['code'] === 'L1') @continue @endif
                    <div class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg
                                hover:bg-gray-50 cursor-pointer transition opacity-50 text-xs">
                        <span class="text-[10px] font-bold w-5 text-center flex-shrink-0"
                              style="color:{{ $layer['color'] }}">{{ $layer['code'] }}</span>
                        <span class="text-gray-600 truncate">{{ $layer['name'] }}</span>
                    </div>
                @endforeach

            </nav>

            {{-- Sidebar footer --}}
            <div class="px-4 py-3 border-t border-gray-100 flex-shrink-0 space-y-0.5">
                <p class="flex items-center gap-1.5 text-[10px] text-gray-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block flex-shrink-0"></span>
                    All systems operational
                </p>
                <p class="text-[10px] text-gray-400">150+ micro-signals active &nbsp;• {{ $clientName }}</p>
            </div>

        </aside>

        {{-- ============================================================
             MAIN CONTENT
             ============================================================ --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-50">

            {{-- Top header bar --}}
            <header class="flex-shrink-0 flex items-center justify-between
                           bg-white border-b border-gray-200 px-6 h-12">
                <div>
                    <h1 class="text-[16px] font-semibold text-gray-900">Data Collection</h1>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Tenant: <span class="text-teal-600 font-medium">{{ $clientName }}</span>
                        <span class="ml-2 inline-flex items-center gap-1 text-green-600 font-medium text-[10px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Live · ML Scoring Active
                        </span>
                    </p>
                </div>

                <div class="flex items-center gap-4 flex-shrink-0">
                    {{-- Home button --}}
                    <a href="{{ route('client.dashboard') }}"
                       class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                       title="Home">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </a>

                    <div class="flex items-center gap-1.5 text-[11px] text-gray-500">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="font-semibold text-gray-700">8.7M profiles</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] text-gray-500">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold text-gray-700">94.1% accuracy</span>
                    </div>

                    {{-- Avatar with dropdown --}}
                    <div class="relative" data-dd-wrap>
                        <button onclick="xpDd('ddL1')"
                                class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-cyan-400"
                                style="background:{{ $avatarColor }}">{{ $initials }}</button>
                        <div id="ddL1" data-dd-menu
                             class="absolute right-0 top-10 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                             style="display:none">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs font-semibold text-gray-800 truncate">{{ $clientName }}</p>
                                <p class="text-[10px] text-gray-400 truncate">Client Account</p>
                            </div>
                            <a href="{{ route('client.dashboard') }}"
                               class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Profile Settings
                            </a>
                            <hr class="my-1 border-gray-100">
                            <form method="POST" action="{{ route('client.logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full flex items-center gap-2 px-4 py-2 text-xs text-red-600 hover:bg-red-50">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
            <main class="flex-1 overflow-y-auto p-5 space-y-4">

                {{-- ================================================
                     4 KPI CARDS
                     ================================================ --}}
                <div class="grid grid-cols-4 gap-4">

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                            </svg>
                        </div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1">Total Micro-Signals</p>
                        <p class="text-3xl font-bold text-emerald-500 leading-none">150+</p>
                        <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
                            <span class="text-emerald-500">▲</span> Across all 8 layers
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                            </svg>
                        </div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1">Events / Day</p>
                        <p class="text-3xl font-bold text-amber-500 leading-none">4.2M</p>
                        <p class="text-[11px] mt-1.5 flex items-center gap-1">
                            <span class="text-emerald-500">▲ 18%</span>
                            <span class="text-gray-400">WoW</span>
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1">Profiles Resolved</p>
                        <p class="text-3xl font-bold text-violet-500 leading-none">8.7M</p>
                        <p class="text-[11px] mt-1.5">
                            <span class="text-emerald-500">▲ 98.2%</span>
                            <span class="text-gray-400"> match rate</span>
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1">Active Tenants</p>
                        <p class="text-3xl font-bold text-amber-500 leading-none">4</p>
                        <p class="text-[11px] mt-1.5">
                            <span class="text-emerald-500">▲ 2</span>
                            <span class="text-gray-400"> added this Q</span>
                        </p>
                    </div>

                </div>

                {{-- ================================================
                     MICRO-SIGNAL SOURCE CARDS  (4 × 3 grid)
                     ================================================ --}}
                @php
                $sources = [
                    ['id' => 'website-events',   'label' => 'Website Events',    'value' => '840',   'unit' => 'messages'],
                    ['id' => 'mobile-app',        'label' => 'Mobile App Events', 'value' => '3200',  'unit' => 'today'],
                    ['id' => 'crm-data',          'label' => 'CRM Data',          'value' => '1540',  'unit' => 'syncs'],
                    ['id' => 'transactions',      'label' => 'Transactions',      'value' => '1540',  'unit' => 'payments'],
                    ['id' => 'chat-support',      'label' => 'Chat & Support',    'value' => '840',   'unit' => 'messages'],
                    ['id' => 'email-engagement',  'label' => 'Email Engagement',  'value' => '1,900', 'unit' => 'opens'],
                    ['id' => 'ad-campaigns',      'label' => 'Ad Campaigns',      'value' => '2,100', 'unit' => 'clicks'],
                    ['id' => 'social-signals',    'label' => 'Social Signals',    'value' => '500',   'unit' => 'mentions'],
                    ['id' => 'surveys-feedback',  'label' => 'Surveys & Feedback','value' => '340',   'unit' => 'responses'],
                    ['id' => 'loyalty-referral',  'label' => 'Loyalty / Referral','value' => '220',   'unit' => 'referrals'],
                    ['id' => 'call-center',       'label' => 'Call Center',       'value' => '430',   'unit' => 'calls'],
                    ['id' => 'pos-offline',       'label' => 'POS / Offline',     'value' => '310',   'unit' => 'purchases'],
                ];
                @endphp

                <div class="grid grid-cols-4 gap-3">
                    @foreach($sources as $src)
                        <button data-source="{{ $src['id'] }}"
                                onclick="selectSource('{{ $src['id'] }}')"
                                class="source-card bg-white rounded-xl px-4 py-3 flex items-center gap-3
                                       text-left transition-all hover:border-emerald-300
                                       {{ $src['id'] === 'chat-support' ? 'source-card-selected' : 'source-card-default' }}">

                            {{-- Thumbnail --}}
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex-shrink-0"></div>

                            {{-- Text --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-900 leading-snug truncate">{{ $src['label'] }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5 leading-none">
                                    {{ $src['value'] }}&thinsp;<span class="text-gray-300">/ {{ $src['unit'] }}</span>
                                </p>
                            </div>

                            {{-- Growth --}}
                            <span class="text-xs font-semibold text-emerald-500 flex-shrink-0 whitespace-nowrap">▲ 12%</span>
                        </button>
                    @endforeach
                </div>

                {{-- ================================================
                     TABS + PANEL
                     ================================================ --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                    {{-- Tab bar --}}
                    <div class="flex items-end border-b border-gray-200 px-2">
                        <button id="tab-conversation" onclick="switchTab('conversation')"
                                class="px-4 py-3 text-sm transition tab-active">
                            Conversation Events
                        </button>
                        <button id="tab-sentiment" onclick="switchTab('sentiment')"
                                class="px-4 py-3 text-sm transition tab-inactive">
                            Sentiment Tracking
                        </button>
                        <button id="tab-resolution" onclick="switchTab('resolution')"
                                class="px-4 py-3 text-sm transition tab-inactive">
                            Resolution Metrics
                        </button>
                    </div>

                    {{-- ── Panel: Conversation Events ── --}}
                    <div id="panel-conversation" class="tab-panel py-8 px-10">

                        {{-- Chat & Support content --}}
                        <div id="chat-support-panel">
                            <div class="flex flex-col items-center text-center mb-8">
                                {{-- Globe icon --}}
                                <div style="width:80px; height:80px; border-radius:50%; background:#bfdbfe; display:flex; align-items:center; justify-content:center; margin-bottom:20px">
                                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <ellipse cx="12" cy="12" rx="4" ry="10"/>
                                        <line x1="2" y1="12" x2="22" y2="12"/>
                                        <path d="M2 7.5c2.5 1 5 1.5 10 1.5s7.5-.5 10-1.5"/>
                                        <path d="M2 16.5c2.5-1 5-1.5 10-1.5s7.5.5 10 1.5"/>
                                    </svg>
                                </div>
                                <h2 id="panel-heading" class="text-2xl font-bold text-gray-900 mb-1.5">
                                    Chat &amp; Support – Conversation Events
                                </h2>
                                <p class="text-sm text-gray-400">Analytics pannel for this micro signal</p>
                            </div>

                            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; max-width:640px; margin:0 auto">
                                <div style="border:1px solid #e5e7eb; border-radius:12px; padding:24px; text-align:center">
                                    <p style="font-size:22px; font-weight:700; color:#10b981; line-height:1">340k/day</p>
                                    <p style="font-size:12px; color:#9ca3af; margin-top:8px">Events today</p>
                                </div>
                                <div style="border:1px solid #e5e7eb; border-radius:12px; padding:24px; text-align:center">
                                    <p style="font-size:22px; font-weight:700; color:#6366f1; line-height:1">live</p>
                                    <p style="font-size:12px; color:#9ca3af; margin-top:8px">Status</p>
                                </div>
                                <div style="border:1px solid #e5e7eb; border-radius:12px; padding:24px; text-align:center">
                                    <p style="font-size:22px; font-weight:700; color:#8b5cf6; line-height:1">97%</p>
                                    <p style="font-size:12px; color:#9ca3af; margin-top:8px">Signal Quality</p>
                                </div>
                            </div>
                        </div>

                        {{-- Other source placeholder --}}
                        <div id="generic-panel" style="display:none" class="flex flex-col items-center text-center py-6">
                            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-400">Analytics coming soon</p>
                            <p class="text-xs text-gray-300 mt-1">Select Chat &amp; Support to view details</p>
                        </div>

                    </div>

                    {{-- ── Panel: Sentiment Tracking ── --}}
                    <div id="panel-sentiment" class="tab-panel" style="display:none; padding:20px 24px">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px">

                            {{-- Left card --}}
                            <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:18px">
                                    <span style="width:10px; height:10px; border-radius:50%; background:#10b981; flex-shrink:0; display:inline-block"></span>
                                    <span style="font-size:10px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.08em; white-space:nowrap">Chat Sentiment Distribution</span>
                                    <div style="flex:1; height:2px; background:#10b981; border-radius:2px"></div>
                                </div>
                                @foreach($sentimentItems as $item)
                                    <div style="margin-bottom:{{ !$loop->last ? '16px' : '0' }}">
                                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px">
                                            <span style="font-size:14px; color:#374151">{{ $item['label'] }}</span>
                                            <span style="font-size:14px; font-weight:700; color:{{ $item['color'] }}">{{ $item['pct'] }}%</span>
                                        </div>
                                        <div style="width:100%; height:8px; background:#f3f4f6; border-radius:9999px; overflow:hidden">
                                            <div style="width:{{ $item['pct'] }}%; height:8px; background:{{ $item['color'] }}; border-radius:9999px"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Right card --}}
                            <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:18px">
                                    <span style="width:10px; height:10px; border-radius:50%; background:#10b981; flex-shrink:0; display:inline-block"></span>
                                    <span style="font-size:10px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.08em; white-space:nowrap">Sentiment By Topic</span>
                                    <div style="flex:1; height:2px; background:#10b981; border-radius:2px"></div>
                                </div>
                                @foreach($sentimentItems as $item)
                                    <div style="margin-bottom:{{ !$loop->last ? '16px' : '0' }}">
                                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px">
                                            <span style="font-size:14px; color:#374151">{{ $item['label'] }}</span>
                                            <span style="font-size:14px; font-weight:700; color:{{ $item['color'] }}">{{ $item['pct'] }}%</span>
                                        </div>
                                        <div style="width:100%; height:8px; background:#f3f4f6; border-radius:9999px; overflow:hidden">
                                            <div style="width:{{ $item['pct'] }}%; height:8px; background:{{ $item['color'] }}; border-radius:9999px"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>

                    {{-- ── Panel: Resolution Metrics ── --}}
                    <div id="panel-resolution" class="tab-panel py-8 px-10" style="display:none">
                        <div class="flex flex-col items-center text-center mb-8">
                            <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center mb-5">
                                <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor"
                                     stroke-width="1.3" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/>
                                    <ellipse cx="12" cy="12" rx="4" ry="10"/>
                                    <path d="M2 12h20M2 7c2.67 1.33 5.33 2 8 2s5.33-.67 8-2M2 17c2.67-1.33 5.33-2 8-2s5.33.67 8 2"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-1.5">
                                Chat &amp; Support – Resolution Metrics
                            </h2>
                            <p class="text-sm text-gray-400">Analytics pannel for this micro signal</p>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; max-width:640px; margin:0 auto">
                            <div style="border:1px solid #e5e7eb; border-radius:12px; padding:24px; text-align:center">
                                <p style="font-size:22px; font-weight:700; color:#10b981; line-height:1">340k/day</p>
                                <p style="font-size:12px; color:#9ca3af; margin-top:8px">Events today</p>
                            </div>
                            <div style="border:1px solid #e5e7eb; border-radius:12px; padding:24px; text-align:center">
                                <p style="font-size:22px; font-weight:700; color:#6366f1; line-height:1">live</p>
                                <p style="font-size:12px; color:#9ca3af; margin-top:8px">Status</p>
                            </div>
                            <div style="border:1px solid #e5e7eb; border-radius:12px; padding:24px; text-align:center">
                                <p style="font-size:22px; font-weight:700; color:#8b5cf6; line-height:1">97%</p>
                                <p style="font-size:12px; color:#9ca3af; margin-top:8px">Signal Quality</p>
                            </div>
                        </div>
                    </div>

                </div>{{-- /tabs card --}}

            </main>
        </div>{{-- /main --}}

    </div>{{-- /flex layout --}}

</div>{{-- /full-page --}}

<script>
function switchTab(tab) {
    // Panels
    document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
    document.getElementById('panel-' + tab).style.display = '';

    // Tab buttons
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.className = btn.className.replace('tab-active', 'tab-inactive');
    });
    const btn = document.getElementById('tab-' + tab);
    btn.className = btn.className.replace('tab-inactive', 'tab-active');
}

function selectSource(sourceId) {
    // Reset all cards
    document.querySelectorAll('.source-card').forEach(card => {
        card.classList.remove('source-card-selected');
        card.classList.add('source-card-default');
    });

    // Highlight clicked card
    const clicked = document.querySelector('[data-source="' + sourceId + '"]');
    clicked.classList.remove('source-card-default');
    clicked.classList.add('source-card-selected');

    // Panel content (only inside conversation events tab)
    const chatPanel    = document.getElementById('chat-support-panel');
    const genericPanel = document.getElementById('generic-panel');

    if (sourceId === 'chat-support') {
        chatPanel.style.display = '';
        genericPanel.style.display = 'none';
        document.getElementById('panel-heading').textContent = 'Chat & Support – Conversation Events';
    } else {
        chatPanel.style.display = 'none';
        genericPanel.style.display = 'flex';
    }
}
function xpDd(id){var m=document.getElementById(id);m.style.display=m.style.display==='block'?'none':'block';}
document.addEventListener('click',function(e){if(!e.target.closest('[data-dd-wrap]'))document.querySelectorAll('[data-dd-menu]').forEach(function(m){m.style.display='none';});});
</script>

</body>
</html>
