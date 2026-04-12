@extends('layouts.client')

@section('title', 'Dashboard')

@section('content')

@php
    $name     = $client->company_name ?? $client->name;
    $words    = explode(' ', trim($name));
    $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

    $colors = ['#6366f1','#3b82f6','#10b981','#f59e0b','#ec4899','#8b5cf6','#14b8a6'];
    $color  = $colors[abs(crc32($name)) % count($colors)];

    $statusMap = [
        'active'    => ['bg' => '#d1fae5', 'text' => '#065f46', 'label' => '✓ Active'],
        'pending'   => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => '● Pending'],
        'trial'     => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => '● Trial'],
        'suspended' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => '● Suspended'],
        'cancelled' => ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => '● Cancelled'],
    ];
    $badge = $statusMap[$client->status] ?? $statusMap['pending'];
@endphp

<div class="max-w-5xl mx-auto">

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

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">My Companies</h1>
            <p class="text-sm text-gray-400 mt-0.5">Manage your registered organizations</p>
        </div>
        <a href="{{ route('client.register') }}"
           class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Company
        </a>
    </div>

    {{-- Company grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        {{-- Clickable company card --}}
        <a href="{{ route('client.company.overview') }}"
           class="block bg-white border border-gray-200 rounded-2xl p-5 hover:shadow-md hover:border-blue-200 transition group">

            {{-- Card header: logo/avatar + name + status --}}
            <div class="flex items-center gap-3 mb-4">

                {{-- Logo or coloured initials --}}
                @if($client->logo)
                    <img src="{{ Storage::url($client->logo) }}"
                         alt="{{ $name }} logo"
                         class="w-10 h-10 rounded-xl object-cover flex-shrink-0 border border-gray-100">
                @else
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                text-white text-sm font-bold flex-shrink-0"
                         style="background: {{ $color }}">
                        {{ $initials }}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-600 transition">
                        {{ $name }}
                    </p>
                </div>

                <span class="text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap flex-shrink-0"
                      style="background: {{ $badge['bg'] }}; color: {{ $badge['text'] }}">
                    {{ $badge['label'] }}
                </span>
            </div>

            {{-- Meta --}}
            <div class="text-xs text-gray-400 space-y-1 mb-4">
                <p>Industry: <span class="text-gray-700 font-medium">{{ $client->industry->name ?? '—' }}</span></p>
                <p>Domain: <span class="text-gray-700 font-medium">{{ $client->website ?? '—' }}</span></p>
                <p>Location: <span class="text-gray-700 font-medium">{{ $client->country ?? '—' }}</span></p>
            </div>

            {{-- Stats --}}
            <div class="flex items-center gap-4 py-3 border-t border-b border-gray-100 mb-4">
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <svg class="text-indigo-500" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <strong class="text-gray-800">{{ $client->size ?? '—' }}</strong>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <svg class="text-amber-500" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7c0-2 1-3 3-3h10c2 0 3 1 3 3M4 7h16"/>
                    </svg>
                    <strong class="text-gray-800">{{ $client->selectedSources->count() }}</strong>
                    {{ Str::plural('Source', $client->selectedSources->count()) }}
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <svg class="text-green-500" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <strong class="text-gray-800">{{ ucfirst($client->plan) }}</strong>
                </div>
            </div>

            {{-- Action tags --}}
            @if($client->actions->count())
                <div class="flex flex-wrap gap-1.5 mb-4">
                    @foreach($client->actions->take(4) as $action)
                        <span class="text-xs font-medium bg-blue-50 text-blue-700 px-2 py-0.5 rounded">
                            {{ $action->name }}
                        </span>
                    @endforeach
                    @if($client->actions->count() > 4)
                        <span class="text-xs font-medium bg-purple-50 text-purple-700 px-2 py-0.5 rounded">
                            +{{ $client->actions->count() - 4 }}
                        </span>
                    @endif
                </div>
            @endif

            {{-- Card footer --}}
            <div class="flex items-center gap-3 text-xs text-gray-400">
                <span class="group-hover:text-blue-600 font-medium transition">View Details</span>
                <span class="text-gray-200">|</span>
                <span class="font-medium">Manage</span>
            </div>

        </a>

        {{-- "Add another company" placeholder card --}}
        <a href="{{ route('client.register') }}"
           class="border-2 border-dashed border-gray-200 rounded-2xl p-5 flex flex-col
                  items-center justify-center gap-3 text-gray-400
                  hover:border-blue-400 hover:text-blue-500 transition min-h-[220px]">
            <div class="w-10 h-10 rounded-full border-2 border-dashed border-current
                        flex items-center justify-center">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <p class="text-sm font-medium">Add another company</p>
        </a>

    </div>

</div>

@endsection
