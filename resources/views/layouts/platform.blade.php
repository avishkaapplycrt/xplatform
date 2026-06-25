{{-- resources/views/layouts/platform.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Platform') - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-screen flex overflow-hidden bg-gray-100 font-sans antialiased text-[13px]">

    {{-- Left Sidebar --}}
    <aside class="w-[184px] bg-white border-r border-gray-200 flex flex-col h-full flex-shrink-0">

        {{-- Active Tenant --}}
        <div class="px-3 pt-4 pb-3 border-b border-gray-100">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Active Tenant</p>

            <button
                class="w-full flex items-center justify-between bg-white border border-gray-200 rounded-md px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                <span>{{ auth('client')->user()?->company_name ?? 'Acme Retail' }}</span>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        {{-- Platform Layers Navigation --}}
        <div class="px-3 pt-3 flex-1 overflow-y-auto">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide px-1 mb-2">Platform Layers</p>

            <nav class="space-y-1">
                {{-- Architecture Overview --}}
                <a href="{{ route('client.architecture') }}"
                   class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition
                          {{ request()->routeIs('client.architecture')
                             ? 'bg-teal-50 text-teal-700 font-medium border-l-2 border-teal-400'
                             : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 {{ request()->routeIs('client.architecture') ? 'text-teal-500' : 'text-gray-400' }}"
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
                    ['num'=>'L4','label'=>'Behavioral Intelligence','color'=>'text-amber-600',  'bg'=>'bg-amber-50',  'route'=>'client.analytics'],
                    ['num'=>'L5','label'=>'AI/ML Predictions',      'color'=>'text-orange-600', 'bg'=>'bg-orange-50', 'route'=>'client.layer.l5'],
                    ['num'=>'L6','label'=>'Decision & Actions',     'color'=>'text-green-600',  'bg'=>'bg-green-50',  'route'=>'client.layer.l6'],
                    ['num'=>'L7','label'=>'Application Layer',      'color'=>'text-red-600',    'bg'=>'bg-red-50',    'route'=>'client.layer.l7'],
                    ['num'=>'L8','label'=>'Governance & Security',  'color'=>'text-gray-600',   'bg'=>'bg-gray-100',  'route'=>'client.layer.l8'],
                ];
                $selectedLayerCodes = auth('client')->user()?->analysisLayers()->pluck('code')->toArray() ?? [];
                // Fall back to all layers for clients who skipped the registration flow
                $sidebarLayers = empty($selectedLayerCodes)
                    ? $allSidebarLayers
                    : array_values(array_filter($allSidebarLayers, fn($l) => in_array($l['num'], $selectedLayerCodes)));
                @endphp

                @foreach($sidebarLayers as $layer)
                    @php
                        $href     = route($layer['route']);
                        $isActive = request()->routeIs($layer['route'])
                                 || ($layer['num'] === 'L1' && request()->routeIs('client.data-collection'));
                    @endphp
                    <a href="{{ $href }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition
                              {{ $isActive ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}
                              {{ !$layer['route'] ? 'opacity-50 cursor-default pointer-events-none' : '' }}">
                        <span class="text-[10px] font-bold {{ $layer['color'] }} {{ $layer['bg'] }} rounded px-1.5 py-0.5 leading-none flex-shrink-0">
                            {{ $layer['num'] }}
                        </span>
                        <span class="truncate">{{ $layer['label'] }}</span>
                    </a>
                @endforeach

                {{-- RL Engine add-on --}}
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-wide px-1 mb-1.5">Add-on Module</p>
                    @php $rlActive = request()->routeIs('client.layer.rl'); @endphp
                    <a href="{{ route('client.layer.rl') }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition
                              {{ $rlActive ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
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
            <p class="text-[10px] text-gray-400 pl-3.5">{{ auth('client')->user()?->company_name ?? 'Acme Retail' }}</p>
        </div>
    </aside>

    {{-- Main Content Area --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('scripts')
    <script>
    function xpDd(id){var m=document.getElementById(id);m.style.display=m.style.display==='block'?'none':'block';}
    document.addEventListener('click',function(e){if(!e.target.closest('[data-dd-wrap]'))document.querySelectorAll('[data-dd-menu]').forEach(function(m){m.style.display='none';});});
    </script>
</body>
</html>
