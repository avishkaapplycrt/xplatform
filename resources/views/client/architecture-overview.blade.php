{{-- resources/views/client/architecture-overview.blade.php --}}
@extends('layouts.platform')

@section('title', 'Architecture Overview')

@section('content')

@php
$client     = auth('client')->user();
$clientName = $client?->company_name ?? 'Acme Retail';
$initials   = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $clientName), 0, 2))));

$allLayers = [
    ['code'=>'L1','name'=>'Data Collection',        'color'=>'#10b981','summary'=>'12 sources  •  100+ micro-signals  •  4.2M events/day',     'dots'=>4, 'route'=>'client.layer.l1'],
    ['code'=>'L2','name'=>'Identity Resolution',     'color'=>'#3b82f6','summary'=>'14 identifier types  •  cross-device stitching  •  98.2%',  'dots'=>4, 'route'=>'client.layer.l2'],
    ['code'=>'L3','name'=>'Data Processing',         'color'=>'#8b5cf6','summary'=>'Real-time + batch  •  6 store types  •  14 quality signals','dots'=>4, 'route'=>'client.layer.l3'],
    ['code'=>'L4','name'=>'Behavioral Intelligence', 'color'=>'#f59e0b','summary'=>'9 behavioral scores  •  40+ micro-signal inputs',           'dots'=>4, 'route'=>'client.layer.l4'],
    ['code'=>'L5','name'=>'AI/ML Predictions',       'color'=>'#f97316','summary'=>'11 models  •  16 prediction micro-signals  •  94.1% avg',   'dots'=>4, 'route'=>'client.layer.l5'],
    ['code'=>'L6','name'=>'Decision & Actions',      'color'=>'#14b8a6','summary'=>'14 action micro-signals  •  rule engine + AI recs',         'dots'=>3, 'route'=>'client.layer.l6'],
    ['code'=>'L7','name'=>'Application Layer',       'color'=>'#ef4444','summary'=>'15 platform usage signals  •  10 modules',                  'dots'=>3, 'route'=>'client.layer.l7'],
    ['code'=>'L8','name'=>'Governance & Security',   'color'=>'#6b7280','summary'=>'14 compliance signals  •  RBAC  •  Audit  •  Consent',     'dots'=>3, 'route'=>'client.layer.l8'],
];

$selectedCodes = $client ? $client->analysisLayers()->pluck('code')->toArray() : [];
$layers = empty($selectedCodes)
    ? $allLayers
    : array_values(array_filter($allLayers, fn($l) => in_array($l['code'], $selectedCodes)));
@endphp

<div class="flex flex-col h-full overflow-hidden bg-gray-50">

    {{-- Clean Header --}}
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Architecture Overview</h1>
            <p class="text-xs text-gray-400 mt-0.5">Explore your platform layers and data flow</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('client.dashboard') }}" 
               class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
               title="Dashboard">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>

            <div class="relative" id="aoAvatarWrap">
                <button onclick="var d=document.getElementById('aoDropdown');d.style.display=d.style.display==='block'?'none':'block'"
                        class="w-8 h-8 rounded-full bg-cyan-500 flex items-center justify-center text-white text-xs font-bold hover:bg-cyan-600 transition-colors">
                    {{ $initials ?: 'JD' }}
                </button>
                <div id="aoDropdown" class="hidden absolute right-0 top-10 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
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
        <div class="max-w-5xl mx-auto">

            {{-- Section Title --}}
            <div class="mb-6">
                <h2 class="text-sm font-semibold text-gray-700">Platform Data Flow</h2>
                <p class="text-xs text-gray-400 mt-0.5">Click any layer to explore details</p>
            </div>

            {{-- Layer Cards --}}
            <div class="space-y-3">
                @foreach($layers as $index => $layer)
                    @php
                        $href = route($layer['route']);
                    @endphp
                    <a href="{{ $href }}" 
                       class="group block bg-white rounded-xl border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all duration-200">
                        <div class="flex items-center gap-4 px-5 py-4">
                            {{-- Layer Number Badge --}}
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                                 style="background: {{ $layer['color'] }}20; color: {{ $layer['color'] }};">
                                {{ $layer['code'] }}
                            </div>

                            {{-- Layer Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-sm font-semibold text-gray-800">{{ $layer['name'] }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium"
                                          style="background: {{ $layer['color'] }}10; color: {{ $layer['color'] }};">
                                        {{ $layer['dots'] }} of 6
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 truncate">{{ $layer['summary'] }}</p>
                            </div>

                            {{-- Progress Dots --}}
                            <div class="hidden sm:flex items-center gap-1 flex-shrink-0">
                                @for($i = 1; $i <= 6; $i++)
                                    @if($i <= $layer['dots'])
                                        <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $layer['color'] }}"></span>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-200"></span>
                                    @endif
                                @endfor
                            </div>

                            {{-- Arrow --}}
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 group-hover:text-gray-500 transition-colors">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Connection Flow Visual --}}
            <div class="mt-8 flex items-center justify-center gap-2 text-xs text-gray-300">
                <span class="w-8 h-px bg-gray-200"></span>
                <span class="text-gray-400">End-to-end data pipeline</span>
                <span class="w-8 h-px bg-gray-200"></span>
            </div>

        </div>
    </main>
</div>

@push('scripts')
<script>
    document.addEventListener('click', function(e) {
        var wrap = document.getElementById('aoAvatarWrap');
        var drop = document.getElementById('aoDropdown');
        if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
    });
</script>
@endpush

@endsection