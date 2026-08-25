@extends('layouts.platform')

@section('title', 'Data Processing')

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
                <h1 class="text-lg font-semibold text-gray-900">Data Processing</h1>
                <p class="text-xs text-gray-400 mt-0.5">Layer 3 — Real-time and batch data pipelines</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-violet-50 text-violet-600 text-xs font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-pulse"></span>
                Live
            </span>
            <div class="relative" id="l3AvatarWrap">
                <button onclick="var d=document.getElementById('l3Dropdown');d.style.display=d.style.display==='block'?'none':'block'"
                        class="w-8 h-8 rounded-full bg-violet-500 flex items-center justify-center text-white text-xs font-bold hover:bg-violet-600 transition-colors">
                    {{ $initials ?: 'JD' }}
                </button>
                <div id="l3Dropdown" class="hidden absolute right-0 top-10 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
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
                    <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center flex-shrink-0">
                        <svg width="24" height="24" fill="none" stroke="#8b5cf6" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-sm font-semibold text-gray-900">Data Processing Engine</h2>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Handles both real-time streaming and batch processing pipelines. Supports 6 data store types 
                            with 14 quality signals for validation, enrichment, and transformation of incoming data streams.
                        </p>
                        <div class="flex items-center gap-4 mt-3">
                            <span class="text-xs text-gray-400">Real-time + batch</span>
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span class="text-xs text-gray-400">6 store types</span>
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span class="text-xs text-gray-400">14 quality signals</span>
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-gray-600">Throughput</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">4.2M</p>
                    <p class="text-xs text-gray-400 mt-1">Events processed / day</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                            <svg width="16" height="16" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-gray-600">Latency</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">< 100ms</p>
                    <p class="text-xs text-gray-400 mt-1">End-to-end processing</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                            <svg width="16" height="16" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-gray-600">Data Quality</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">99.7%</p>
                    <p class="text-xs text-gray-400 mt-1">Clean records rate</p>
                </div>
            </div>

            {{-- Data Store Types --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Data Store Types</h3>
                    <p class="text-xs text-gray-400 mt-0.5">6 storage backends for different data workloads</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                        @php
                        $stores = [
                            ['name' => 'Relational DB', 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4', 'color' => '#3b82f6'],
                            ['name' => 'Time-Series', 'icon' => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z', 'color' => '#10b981'],
                            ['name' => 'Document Store', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => '#f59e0b'],
                            ['name' => 'Cache Layer', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => '#ef4444'],
                            ['name' => 'Data Lake', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'color' => '#8b5cf6'],
                            ['name' => 'Queue System', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'color' => '#14b8a6'],
                        ];
                        @endphp
                        @foreach($stores as $store)
                            <div class="flex flex-col items-center gap-2 p-3 rounded-lg border border-gray-100 hover:border-gray-200 hover:bg-gray-50 transition-all cursor-default">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: {{ $store['color'] }}15;">
                                    <svg width="20" height="20" fill="none" stroke="{{ $store['color'] }}" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $store['icon'] }}"/>
                                    </svg>
                                </div>
                                <span class="text-[11px] font-medium text-gray-600 text-center">{{ $store['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Quality Signals --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Quality Signals</h3>
                    <p class="text-xs text-gray-400 mt-0.5">14 validation and enrichment checks applied to every record</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                        @php
                        $signals = [
                            ['name' => 'Schema Validation', 'color' => '#3b82f6'],
                            ['name' => 'Type Checking', 'color' => '#10b981'],
                            ['name' => 'Null Detection', 'color' => '#ef4444'],
                            ['name' => 'Range Checks', 'color' => '#f59e0b'],
                            ['name' => 'Format Verify', 'color' => '#8b5cf6'],
                            ['name' => 'Deduplication', 'color' => '#14b8a6'],
                            ['name' => 'Enrichment', 'color' => '#f97316'],
                            ['name' => 'Anomaly Detect', 'color' => '#dc2626'],
                            ['name' => 'Consistency', 'color' => '#06b6d4'],
                            ['name' => 'Completeness', 'color' => '#84cc16'],
                            ['name' => 'Timeliness', 'color' => '#a855f7'],
                            ['name' => 'Accuracy', 'color' => '#eab308'],
                            ['name' => 'Uniqueness', 'color' => '#ec4899'],
                            ['name' => 'Validity', 'color' => '#6366f1'],
                        ];
                        @endphp
                        @foreach($signals as $signal)
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-100 hover:border-gray-200 hover:bg-gray-50 transition-all">
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: {{ $signal['color'] }}"></span>
                                <span class="text-[11px] font-medium text-gray-600">{{ $signal['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Processing Pipeline --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Processing Pipeline</h3>
                    <p class="text-xs text-gray-400 mt-0.5">How data flows through the system</p>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        @php
                        $steps = [
                            ['label' => 'Ingest', 'desc' => 'Collect', 'color' => '#6b7280'],
                            ['label' => 'Validate', 'desc' => 'Quality', 'color' => '#3b82f6'],
                            ['label' => 'Transform', 'desc' => 'Enrich', 'color' => '#8b5cf6'],
                            ['label' => 'Route', 'desc' => 'Dispatch', 'color' => '#f59e0b'],
                            ['label' => 'Store', 'desc' => 'Persist', 'color' => '#10b981'],
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
        var wrap = document.getElementById('l3AvatarWrap');
        var drop = document.getElementById('l3Dropdown');
        if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
    });
</script>
@endpush

@endsection