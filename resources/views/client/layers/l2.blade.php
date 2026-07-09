@extends('layouts.platform')

@section('title', 'Identity Resolution')

@section('content')

@php
$client     = auth('client')->user();
$clientName = $client?->company_name ?? 'Acme Retail';
$initials   = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $clientName), 0, 2))));
@endphp

<div class="flex flex-col h-full overflow-hidden bg-gray-50/50">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('client.architecture') }}" 
                class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
                title="Back to Architecture">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-semibold text-gray-900">Identity Resolution</h1>
                <p class="text-xs text-gray-400 mt-0.5">Layer 2 — Cross-device identity stitching</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                Live
            </span>
            <div class="relative" id="l2AvatarWrap">
                <button onclick="var d=document.getElementById('l2Dropdown');d.style.display=d.style.display==='block'?'none':'block'"
                        class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold hover:bg-blue-600 transition-colors">
                    {{ $initials ?: 'JD' }}
                </button>
                <div id="l2Dropdown" class="hidden absolute right-0 top-10 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                    <div class="px-4 py-2 border-b border-gray-50">
                        <p class="text-xs font-semibold text-gray-900 truncate">{{ $clientName }}</p>
                        <p class="text-[10px] text-gray-400">Client Account</p>
                    </div>
                    <a href="{{ route('client.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-600 hover:bg-gray-50 transition-colors">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile Settings
                    </a>
                    <form method="POST" action="{{ route('client.logout') }}" class="border-t border-gray-50">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-red-500 hover:bg-red-50 transition-colors text-left">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-6xl mx-auto space-y-6">

            {{-- Layer Info Card --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                        <svg width="24" height="24" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-sm font-semibold text-gray-900">Identity Resolution Engine</h2>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Unifies customer profiles across 14 identifier types including device IDs, email hashes, 
                            phone numbers, and cookie signatures. Achieves cross-device stitching with deterministic 
                            and probabilistic matching.
                        </p>
                        <div class="flex items-center gap-4 mt-3">
                            <span class="text-xs text-gray-400">14 identifier types</span>
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span class="text-xs text-gray-400">Cross-device stitching</span>
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span class="text-xs text-gray-400">98.2% match rate</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                            <svg width="16" height="16" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-gray-600">Match Rate</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">98.2%</p>
                    <p class="text-xs text-gray-400 mt-1">Deterministic + probabilistic</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                            <svg width="16" height="16" fill="none" stroke="#8b5cf6" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-gray-600">Profiles Resolved</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">8.7M</p>
                    <p class="text-xs text-gray-400 mt-1">Unified customer profiles</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                            <svg width="16" height="16" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-gray-600">Processing Speed</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">< 50ms</p>
                    <p class="text-xs text-gray-400 mt-1">Real-time resolution</p>
                </div>
            </div>

            {{-- Identifier Types --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Identifier Types</h3>
                    <p class="text-xs text-gray-400 mt-0.5">14 sources used for identity matching</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                        @php
                        $identifiers = [
                            ['name' => 'Email Hash', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => '#3b82f6'],
                            ['name' => 'Phone', 'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'color' => '#10b981'],
                            ['name' => 'Device ID', 'icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z', 'color' => '#8b5cf6'],
                            ['name' => 'Cookie', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'color' => '#f59e0b'],
                            ['name' => 'IP Address', 'icon' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9', 'color' => '#ef4444'],
                            ['name' => 'Fingerprint', 'icon' => 'M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11', 'color' => '#14b8a6'],
                            ['name' => 'SSO Token', 'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z', 'color' => '#f97316'],
                        ];
                        @endphp
                        @foreach($identifiers as $id)
                            <div class="flex flex-col items-center gap-2 p-3 rounded-lg border border-gray-100 hover:border-gray-200 hover:bg-gray-50 transition-all cursor-default">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: {{ $id['color'] }}15;">
                                    <svg width="20" height="20" fill="none" stroke="{{ $id['color'] }}" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $id['icon'] }}"/>
                                    </svg>
                                </div>
                                <span class="text-[11px] font-medium text-gray-600 text-center">{{ $id['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Resolution Flow --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Resolution Pipeline</h3>
                    <p class="text-xs text-gray-400 mt-0.5">How identities are matched and unified</p>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        @php
                        $steps = [
                            ['label' => 'Ingest', 'desc' => 'Raw signals', 'color' => '#6b7280'],
                            ['label' => 'Normalize', 'desc' => 'Standardize', 'color' => '#3b82f6'],
                            ['label' => 'Match', 'desc' => 'Compare IDs', 'color' => '#8b5cf6'],
                            ['label' => 'Score', 'desc' => 'Confidence', 'color' => '#f59e0b'],
                            ['label' => 'Merge', 'desc' => 'Unify profile', 'color' => '#10b981'],
                        ];
                        @endphp
                        @foreach($steps as $i => $step)
                            <div class="flex flex-col items-center gap-2 flex-1">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-xs font-bold"
                                     style="background: {{ $step['color'] }};">
                                    {{ $i + 1 }}
                                </div>
                                <span class="text-xs font-semibold text-gray-700">{{ $step['label'] }}</span>
                                <span class="text-[10px] text-gray-400">{{ $step['desc'] }}</span>
                            </div>
                            @if($i < count($steps) - 1)
                                <div class="flex-1 h-px bg-gray-200 mx-2 mb-6"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

@push('scripts')
<script>
    document.addEventListener('click', function(e) {
        var wrap = document.getElementById('l2AvatarWrap');
        var drop = document.getElementById('l2Dropdown');
        if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
    });
</script>
@endpush

@endsection