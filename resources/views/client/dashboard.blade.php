<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard – {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased">

@php
    $name     = $client->company_name ?? $client->name ?? 'My Company';
    $words    = explode(' ', trim($name));
    $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

    $colors = ['#6366f1','#3b82f6','#10b981','#f59e0b','#ec4899','#8b5cf6','#14b8a6'];
    $color  = $colors[abs(crc32($name)) % count($colors)];

    $statusMap = [
        'active'    => ['bg' => '#d1fae5', 'text' => '#065f46', 'label' => 'Active',    'type' => 'active'],
        'pending'   => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => 'Trial',     'type' => 'trial'],
        'trial'     => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => 'Trial',     'type' => 'trial'],
        'suspended' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Suspended', 'type' => 'suspended'],
        'cancelled' => ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => 'Cancelled', 'type' => 'other'],
    ];
    $badge = $statusMap[$client->status] ?? $statusMap['pending'];
@endphp

<div class="flex h-screen overflow-hidden bg-white">

    {{-- ======================================================
         LEFT SIDEBAR  (narrow — no active layer here)
         ====================================================== --}}
    <aside class="hidden md:flex w-[220px] flex-shrink-0 flex-col border-r border-gray-200 bg-white" id="clientSidebar">

        {{-- Brand --}}
        <div class="flex items-center h-14 px-4 border-b border-gray-100 flex-shrink-0">
            <span class="text-sm font-bold text-indigo-600 tracking-tight truncate">
                {{ config('app.name') }}
            </span>
        </div>

        <nav class="flex-1 overflow-y-auto px-2 py-3 space-y-0.5">
            {{-- Dashboard — active --}}
            <a href="{{ route('client.dashboard') }}"
               class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg border-l-2 border-indigo-400
                      bg-indigo-50 text-indigo-700 font-semibold text-xs">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('client.sources.index') }}"
               class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg hover:bg-gray-50
                      text-gray-500 text-xs transition">
                <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7c0-2 1-3 3-3h10c2 0 3 1 3 3M4 7h16"/>
                </svg>
                Data Sources
            </a>

            <a href="{{ route('client.chatbot') }}"
               class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg hover:bg-gray-50
                      text-gray-500 text-xs transition">
                <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Chat Bot
            </a>
        </nav>

        {{-- Footer --}}
        <div class="px-3 py-3 border-t border-gray-100 flex-shrink-0">
            <form method="POST" action="{{ route('client.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg
                               text-xs font-medium text-red-500 hover:bg-red-50 hover:text-red-700
                               transition">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                    </svg>
                    Log out
                </button>
            </form>
        </div>

    </aside>

    {{-- ======================================================
         MAIN CONTENT
         ====================================================== --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-50">

        {{-- Top search bar --}}
        <header class="flex-shrink-0 flex items-center justify-between
                       bg-white border-b border-gray-200 px-4 md:px-6 h-14 gap-4">

            {{-- Mobile hamburger --}}
            <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition flex-shrink-0"
                    onclick="toggleClientSidebar()" aria-label="Open menu">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Search --}}
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
                </svg>
                <input type="text" placeholder="Search Company"
                       class="w-full pl-9 pr-4 py-2 text-sm bg-gray-50 border border-gray-200
                              rounded-lg text-gray-700 placeholder-gray-400
                              focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300
                              transition">
            </div>

            {{-- Bell + avatar + name --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                <button class="relative p-1.5 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                         stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-0.5 right-0.5 w-4 h-4 bg-red-500 text-white
                                 text-[9px] font-bold rounded-full flex items-center justify-center leading-none">
                        9
                    </span>
                </button>

                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                                text-white text-xs font-bold flex-shrink-0"
                         style="background:{{ $color }}">{{ $initials }}</div>
                    <span class="text-sm font-medium text-gray-700">{{ $name }}</span>
                </div>

                <form method="POST" action="{{ route('client.logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200
                                   bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                        </svg>
                        Log out
                    </button>
                </form>
            </div>
        </header>

        {{-- Scrollable body --}}
        <main class="flex-1 overflow-y-auto p-6">

            {{-- Flash messages --}}
            @if(session('warning'))
                <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg">
                    {{ session('warning') }}
                </div>
            @endif
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Setup checklist — replaces the old multi-step registration wizard --}}
            @if(!$checklist['dismissed'] && !$checklist['complete'])
                @include('client.partials.onboarding-checklist', ['checklist' => $checklist])
            @endif

            {{-- Company cards grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- Actual client card --}}
                @php
                    $tWords  = explode(' ', trim($name));
                    $tInits  = strtoupper(substr($tWords[0],0,1) . (isset($tWords[1]) ? substr($tWords[1],0,1) : ''));
                @endphp
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col gap-3">

                    {{-- Header: avatar + name + status badge --}}
                    <div class="flex items-center gap-3">
                        @if($client->logo)
                            <img src="{{ Storage::url($client->logo) }}" alt="{{ $name }} logo"
                                 class="w-9 h-9 rounded-lg object-cover flex-shrink-0 border border-gray-100">
                        @else
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center
                                        text-white text-xs font-bold flex-shrink-0"
                                 style="background:{{ $color }}">{{ $tInits }}</div>
                        @endif
                        <span class="flex-1 text-sm font-semibold text-gray-800 truncate">{{ $name }}</span>

                        @if($badge['type'] === 'active')
                            <span class="flex items-center gap-1 text-[11px] font-semibold
                                         text-emerald-600 bg-emerald-50 border border-emerald-200
                                         px-2 py-0.5 rounded-full flex-shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                Active
                            </span>
                        @elseif($badge['type'] === 'trial')
                            <span class="text-[11px] font-semibold text-amber-600 bg-amber-50
                                         border border-amber-200 px-2 py-0.5 rounded-full flex-shrink-0">
                                Trial
                            </span>
                        @elseif($badge['type'] === 'suspended')
                            <span class="flex items-center gap-1 text-[11px] font-semibold
                                         text-red-600 bg-red-50 border border-red-200
                                         px-2 py-0.5 rounded-full flex-shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>
                                Suspended
                            </span>
                        @else
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full flex-shrink-0"
                                  style="background:{{ $badge['bg'] }};color:{{ $badge['text'] }}">
                                {{ $badge['label'] }}
                            </span>
                        @endif
                    </div>

                    {{-- Meta --}}
                    <div class="text-xs text-gray-500 space-y-0.5">
                        <p>Industry: <span class="text-gray-700">{{ $client->industry->name ?? '—' }}</span></p>
                        <p>Domain: <span class="text-gray-700">{{ $client->website ?? '—' }}</span></p>
                        <p>Location: <span class="text-gray-700">{{ $client->country ?? '—' }}</span></p>
                    </div>

                    {{-- Stats row --}}
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="font-semibold text-gray-700">{{ $client->size ?? '—' }}</span> Users
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7c0-2 1-3 3-3h10c2 0 3 1 3 3M4 7h16"/>
                            </svg>
                            <span class="font-semibold text-gray-700">{{ $client->selectedSources->count() }}</span>
                            {{ Str::plural('Source', $client->selectedSources->count()) }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="font-semibold text-gray-700">{{ ucfirst($client->plan ?? '—') }}</span>
                        </span>
                    </div>

                    {{-- Tags + actions --}}
                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-gray-100">
                        <div class="flex flex-wrap gap-1.5">
                            @if($client->actions->count())
                                @foreach($client->actions->take(3) as $action)
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded
                                                 bg-indigo-50 text-indigo-600 border border-indigo-100">
                                        {{ $action->name }}
                                    </span>
                                @endforeach
                                @if($client->actions->count() > 3)
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded
                                                 bg-purple-50 text-purple-600 border border-purple-100">
                                        +{{ $client->actions->count() - 3 }}
                                    </span>
                                @endif
                            @endif
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0 text-xs text-gray-400">
                            <a href="{{ route('client.company.overview') }}"
                               class="hover:text-gray-600 transition">View Details</a>
                            <a href="{{ route('client.company.overview') }}"
                               class="hover:text-gray-600 transition">Manage</a>
                        </div>
                    </div>

                </div>

                {{-- "Add another company" ghost card --}}
                <a href="{{ route('client.register') }}"
                   class="bg-white rounded-xl border-2 border-dashed border-gray-200
                          hover:border-indigo-300 hover:bg-indigo-50 p-4 flex flex-col
                          items-center justify-center gap-2 text-gray-400
                          hover:text-indigo-500 transition min-h-[200px]">
                    <div class="w-10 h-10 rounded-full border-2 border-dashed border-current
                                flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium">Add another company</p>
                </a>

            </div>

        </main>
    </div>

</div>

{{-- Mobile sidebar overlay --}}
<div id="clientSidebarOverlay"
     class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"
     onclick="toggleClientSidebar()"></div>

{{-- Mobile sidebar drawer --}}
<aside id="clientSidebarMob"
       class="fixed inset-y-0 left-0 z-50 w-[220px] flex flex-col border-r border-gray-200 bg-white
              -translate-x-full transition-transform duration-300">
    <div class="flex items-center h-14 px-4 border-b border-gray-100 flex-shrink-0">
        <span class="text-sm font-bold text-indigo-600 tracking-tight truncate flex-1">
            {{ config('app.name') }}
        </span>
        <button onclick="toggleClientSidebar()" class="p-1 rounded hover:bg-gray-100">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <nav class="flex-1 overflow-y-auto px-2 py-3 space-y-0.5">
        <a href="{{ route('client.dashboard') }}"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg border-l-2 border-indigo-400
                  bg-indigo-50 text-indigo-700 font-semibold text-xs">
            Dashboard
        </a>
        <a href="{{ route('client.sources.index') }}"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg hover:bg-gray-50 text-gray-500 text-xs transition">
            Data Sources
        </a>
    </nav>
    <div class="px-3 py-3 border-t border-gray-100 flex-shrink-0">
        <form method="POST" action="{{ route('client.logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-xs font-medium
                           text-red-500 hover:bg-red-50 hover:text-red-700 transition">
                Log out
            </button>
        </form>
    </div>
</aside>

<script>
function toggleClientSidebar(){
    var d=document.getElementById('clientSidebarMob'),o=document.getElementById('clientSidebarOverlay');
    var isOpen=d.style.transform==='translateX(0px)';
    d.style.transform=isOpen?'translateX(-100%)':'translateX(0)';
    o.classList.toggle('hidden',isOpen);
    document.body.style.overflow=isOpen?'':'hidden';
}
</script>
</body>
</html>
