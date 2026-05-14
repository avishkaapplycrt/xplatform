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

<div class="flex flex-col h-full overflow-hidden">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 px-5 py-3 flex items-center justify-between flex-shrink-0">
        <div>
            <h1 class="text-[15px] font-semibold text-gray-900 leading-none">Architecture Overview</h1>
            <div class="flex items-center gap-2 mt-1.5">
                <span class="text-[11px] text-gray-500">Tenant:</span>
                <span class="text-[11px] font-medium text-teal-600">{{ $clientName }}</span>
                <span class="flex items-center gap-1 text-[10px] text-green-600 font-medium">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                    Live
                </span>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:12px">
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

            {{-- Stats --}}
            <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:#4b5563">
                <svg width="14" height="14" fill="none" stroke="#a78bfa" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span style="font-weight:500">8.7M profiles</span>
            </span>
            <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:#4b5563">
                <svg width="14" height="14" fill="none" stroke="#f472b6" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span style="font-weight:500">94.1% accuracy</span>
            </span>

            {{-- Avatar with dropdown --}}
            <div style="position:relative" id="aoAvatarWrap">
                <button onclick="var d=document.getElementById('aoDropdown');d.style.display=d.style.display==='block'?'none':'block'"
                        style="width:32px;height:32px;border-radius:50%;background:#06b6d4;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;border:none;cursor:pointer">
                    {{ $initials ?: 'JD' }}
                </button>
                <div id="aoDropdown"
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
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
    </header>

    {{-- Body --}}
    <main class="flex-1 overflow-y-auto px-4 py-4">
        <div class="space-y-4">

            {{-- KPI Cards --}}
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="w-5 h-5 mb-4"><svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l8 8-8 8-8-8 8-8z"/></svg></div>
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold">Total Micro-Signals</p>
                    <p class="mt-1 text-[22px] leading-none font-bold text-emerald-400">150+</p>
                    <p class="mt-3 text-xs text-gray-500 flex items-center gap-1"><svg class="w-2.5 h-2.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 4l6 6H4l6-6z"/></svg>Across all 8 layers</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="w-5 h-5 mb-4"><svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L3 14h8l-1 8 11-14h-8l1-6z"/></svg></div>
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold">Events / Day</p>
                    <p class="mt-1 text-[22px] leading-none font-bold text-blue-500">4.2M</p>
                    <p class="mt-3 text-xs text-gray-500 flex items-center gap-1"><svg class="w-2.5 h-2.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 4l6 6H4l6-6z"/></svg>18% WoW</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="w-5 h-5 mb-4"><svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold">Profiles Resolved</p>
                    <p class="mt-1 text-[22px] leading-none font-bold text-violet-500">8.7M</p>
                    <p class="mt-3 text-xs text-gray-500 flex items-center gap-1"><svg class="w-2.5 h-2.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 4l6 6H4l6-6z"/></svg>98.2% match rate</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="w-5 h-5 mb-4"><svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></div>
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold">Active Tenants</p>
                    <p class="mt-1 text-[22px] leading-none font-bold text-amber-500">4</p>
                    <p class="mt-3 text-xs text-gray-500 flex items-center gap-1"><svg class="w-2.5 h-2.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 4l6 6H4l6-6z"/></svg>2 added this Q</p>
                </div>
            </div>

            {{-- Platform Data Flow --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">
                        Platform Data Flow
                        <span class="text-gray-300 mx-1">•</span>
                        Click to drill into any layer
                    </p>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($layers as $layer)
                        @php
                            $href = route($layer['route']);
                        @endphp
                        <a href="{{ $href }}"
                           class="group flex items-center gap-4 px-5 py-3 hover:bg-gray-50 transition">
                            <span class="text-[11px] font-bold w-6 text-center flex-shrink-0"
                                  style="color:{{ $layer['color'] }}">{{ $layer['code'] }}</span>
                            <span class="text-xs font-semibold text-gray-800 w-44 flex-shrink-0 truncate">{{ $layer['name'] }}</span>
                            <span class="flex-1 text-[11px] text-gray-400 truncate">{{ $layer['summary'] }}</span>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                @for($i = 1; $i <= 6; $i++)
                                    @if($i <= $layer['dots'])
                                        <span class="w-2 h-2 rounded-full inline-block" style="background:{{ $layer['color'] }}"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full inline-block border" style="border-color:{{ $layer['color'] }};opacity:0.25"></span>
                                    @endif
                                @endfor
                            </div>
                            <button class="w-7 h-7 rounded-lg flex items-center justify-center text-white flex-shrink-0 opacity-80 group-hover:opacity-100 transition"
                                    style="background:{{ $layer['color'] }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </main>
</div>

@endsection
