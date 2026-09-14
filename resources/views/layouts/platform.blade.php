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
    <aside id="platformSidebar" class="w-[200px] bg-white border-r border-gray-200 flex flex-col h-full flex-shrink-0 transition-all duration-200">

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

        {{-- Business Helpers --}}
        <div class="px-3 pt-3">
            <a href="{{ route('client.business-helpers') }}"
               class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition
                      {{ request()->routeIs('client.business-helpers')
                         ? 'bg-teal-50 text-teal-700 font-medium border-l-2 border-teal-400'
                         : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-3.5 h-3.5 flex-shrink-0 {{ request()->routeIs('client.business-helpers') ? 'text-teal-500' : 'text-gray-400' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span class="truncate">Business Helpers</span>
            </a>
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
                    ['num'=>'L5','label'=>'AI/ML Predictions',      'color'=>'text-orange-600', 'bg'=>'bg-orange-50', 'route'=>'client.aiml.dashboard'],
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
                    @php
                        $href     = route($layer['route']);
                        $isActive = request()->routeIs($layer['route'])
                                 || ($layer['num'] === 'L1' && request()->routeIs('client.data-collection'));
                    @endphp
                    <a href="{{ $href }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition
                              {{ $isActive ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span class="text-[10px] font-bold {{ $layer['color'] }} {{ $layer['bg'] }} rounded px-1.5 py-0.5 leading-none flex-shrink-0">
                            {{ $layer['num'] }}
                        </span>
                        <span class="truncate">{{ $layer['label'] }}</span>
                    </a>
                @endforeach

                {{-- Revenue Optimization Group --}}
                @php
                $revenueRoutes = [
                    'client.revenue.dashboard',
                    'client.revenue.pricing',
                    'client.revenue.upsell',
                    'client.revenue.forecast',
                ];
                $isRevenueActive = collect($revenueRoutes)->contains(fn($r) => request()->routeIs($r));
                @endphp
                <div class="space-y-1 mt-2">
                    <button onclick="toggleRevenueMenu()"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs rounded-md transition
                                   {{ $isRevenueActive ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 {{ $isRevenueActive ? 'text-green-500' : 'text-gray-400' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="truncate">Revenue Optimization</span>
                        </div>
                        <svg id="revenueChevron" class="w-3 h-3 transition-transform {{ $isRevenueActive ? 'rotate-180' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="revenueSubmenu" class="{{ $isRevenueActive ? '' : 'hidden' }} pl-7 space-y-1">
                        <a href="{{ route('client.revenue.dashboard') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.revenue.dashboard') ? 'text-green-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.revenue.dashboard') ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Dashboard</span>
                        </a>
                        <a href="{{ route('client.revenue.pricing') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.revenue.pricing') ? 'text-green-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.revenue.pricing') ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Pricing Intelligence</span>
                        </a>
                        <a href="{{ route('client.revenue.upsell') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.revenue.upsell') ? 'text-green-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.revenue.upsell') ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Upsell Opportunities</span>
                        </a>
                        <a href="{{ route('client.revenue.forecast') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.revenue.forecast') ? 'text-green-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.revenue.forecast') ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Revenue Forecast</span>
                        </a>
                    </div>
                </div>

                {{-- Customer Success Group --}}
                @php
                $successRoutes = [
                    'client.success.onboarding',
                    'client.success.health',
                    'client.success.checkins',
                    'client.success.nps',
                ];
                $isSuccessActive = collect($successRoutes)->contains(fn($r) => request()->routeIs($r));
                @endphp
                <div class="space-y-1">
                    <button onclick="toggleSuccessMenu()"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs rounded-md transition
                                   {{ $isSuccessActive ? 'bg-pink-50 text-pink-700 font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 {{ $isSuccessActive ? 'text-pink-500' : 'text-gray-400' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span class="truncate">Customer Success</span>
                        </div>
                        <svg id="successChevron" class="w-3 h-3 transition-transform {{ $isSuccessActive ? 'rotate-180' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="successSubmenu" class="{{ $isSuccessActive ? '' : 'hidden' }} pl-7 space-y-1">
                        <a href="{{ route('client.success.onboarding') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.success.onboarding') ? 'text-pink-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.success.onboarding') ? 'bg-pink-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Onboarding</span>
                        </a>
                        <a href="{{ route('client.success.health') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.success.health') ? 'text-pink-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.success.health') ? 'bg-pink-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Health Scores</span>
                        </a>
                        <a href="{{ route('client.success.checkins') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.success.checkins') ? 'text-pink-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.success.checkins') ? 'bg-pink-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Check-ins</span>
                        </a>
                        <a href="{{ route('client.success.nps') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.success.nps') ? 'text-pink-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.success.nps') ? 'bg-pink-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">NPS Surveys</span>
                        </a>
                    </div>
                </div>

                {{-- Segmentation --}}
                <a href="{{ route('client.segmentation.index') }}"
                   class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition
                          {{ request()->routeIs('client.segmentation.*')
                             ? 'bg-cyan-50 text-cyan-700 font-medium'
                             : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 {{ request()->routeIs('client.segmentation.*') ? 'text-cyan-500' : 'text-gray-400' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="truncate">Segmentation</span>
                </a>

                {{-- Lead Scoring --}}
                <a href="{{ route('client.leadscoring.dashboard') }}"
                   class="flex items-center gap-2 px-3 py-2 text-xs rounded-md transition
                          {{ request()->routeIs('client.leadscoring.*')
                             ? 'bg-purple-50 text-purple-700 font-medium'
                             : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 {{ request()->routeIs('client.leadscoring.*') ? 'text-purple-500' : 'text-gray-400' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate">Lead Scoring</span>
                </a>

                {{-- Alerts & Retention --}}
                @php
                $alertRoutes = ['client.alerts.index', 'client.retention.dashboard'];
                $isAlertActive = collect($alertRoutes)->contains(fn($r) => request()->routeIs($r));
                @endphp
                <div class="space-y-1">
                    <button onclick="toggleAlertsMenu()"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs rounded-md transition
                                   {{ $isAlertActive ? 'bg-red-50 text-red-700 font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 {{ $isAlertActive ? 'text-red-500' : 'text-gray-400' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span class="truncate">Alerts & Retention</span>
                        </div>
                        <svg id="alertsChevron" class="w-3 h-3 transition-transform {{ $isAlertActive ? 'rotate-180' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="alertsSubmenu" class="{{ $isAlertActive ? '' : 'hidden' }} pl-7 space-y-1">
                        <a href="{{ route('client.alerts.index') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.alerts.index') ? 'text-red-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.alerts.index') ? 'bg-red-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Real-Time Alerts</span>
                        </a>
                        <a href="{{ route('client.retention.dashboard') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.retention.dashboard') ? 'text-red-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.retention.dashboard') ? 'bg-red-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Churn & Retention</span>
                        </a>
                    </div>
                </div>

                {{-- Analytics Reports --}}
                @php
                $reportRoutes = [
                    'client.reports.executive-dashboard',
                    'client.reports.website.overview',
                    'client.reports.email.overview',
                    'client.reports.crm.overview',
                    'client.reports.social.overview',
                    'client.reports.chat-support.overview',
                    'client.reports.transactions.overview',
                    'client.reports.growth.business-health',
                    'client.reports.custom.index',
                ];
                $isReportsActive = collect($reportRoutes)->contains(fn($r) => request()->routeIs($r));
                @endphp

                <div class="space-y-1 mt-2">
                    <button onclick="toggleReportsMenu()"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs rounded-md transition
                                   {{ $isReportsActive ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 {{ $isReportsActive ? 'text-purple-500' : 'text-gray-400' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="truncate">Analytics Reports</span>
                        </div>
                        <svg id="reportsChevron" class="w-3 h-3 transition-transform {{ $isReportsActive ? 'rotate-180' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div id="reportsSubmenu" class="{{ $isReportsActive ? '' : 'hidden' }} pl-7 space-y-1">
                        <a href="{{ route('client.reports.executive-dashboard') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.reports.executive-dashboard') ? 'text-purple-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.reports.executive-dashboard') ? 'bg-purple-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Executive Dashboard</span>
                        </a>
                        <a href="{{ route('client.reports.website.overview') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.reports.website.*') ? 'text-purple-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.reports.website.*') ? 'bg-purple-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Website</span>
                        </a>
                        <a href="{{ route('client.reports.email.overview') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.reports.email.*') ? 'text-purple-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.reports.email.*') ? 'bg-purple-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Email</span>
                        </a>
                        <a href="{{ route('client.reports.crm.overview') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.reports.crm.*') ? 'text-purple-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.reports.crm.*') ? 'bg-purple-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">CRM</span>
                        </a>
                        <a href="{{ route('client.reports.social.overview') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.reports.social.*') ? 'text-purple-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.reports.social.*') ? 'bg-purple-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Social Media</span>
                        </a>
                        <a href="{{ route('client.reports.chat-support.overview') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.reports.chat-support.*') ? 'text-purple-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.reports.chat-support.*') ? 'bg-purple-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Chat & Support</span>
                        </a>
                        <a href="{{ route('client.reports.transactions.overview') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.reports.transactions.*') ? 'text-purple-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.reports.transactions.*') ? 'bg-purple-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Transactions</span>
                        </a>
                        <a href="{{ route('client.reports.growth.business-health') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.reports.growth.*') ? 'text-purple-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.reports.growth.*') ? 'bg-purple-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Growth Intelligence</span>
                        </a>
                        <a href="{{ route('client.reports.custom.index') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.reports.custom.*') ? 'text-purple-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.reports.custom.*') ? 'bg-purple-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Custom Reports</span>
                        </a>
                    </div>
                </div>

                {{-- Email Settings Group --}}
                @php
                $emailRoutes = ['client.email.templates', 'client.email.template.categories', 'client.email.logs'];
                $isEmailActive = collect($emailRoutes)->contains(fn($r) => request()->routeIs($r));
                @endphp

                <div class="space-y-1">
                    <button onclick="toggleEmailMenu()"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs rounded-md transition
                                   {{ $isEmailActive ? 'bg-sky-50 text-sky-700 font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 {{ $isEmailActive ? 'text-sky-500' : 'text-gray-400' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="truncate">Email Settings</span>
                        </div>
                        <svg id="emailChevron" class="w-3 h-3 transition-transform {{ $isEmailActive ? 'rotate-180' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div id="emailSubmenu" class="{{ $isEmailActive ? '' : 'hidden' }} pl-7 space-y-1">
                        <a href="{{ route('client.email.templates') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.email.templates') ? 'text-sky-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.email.templates') ? 'bg-sky-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Templates</span>
                        </a>
                        <a href="{{ route('client.email.template.categories') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.email.template.categories') ? 'text-sky-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.email.template.categories') ? 'bg-sky-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Categories</span>
                        </a>
                        <a href="{{ route('client.email.logs') }}"
                           class="flex items-center gap-2 px-3 py-1.5 text-[11px] rounded-md transition
                                  {{ request()->routeIs('client.email.logs') ? 'text-sky-700 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            <span class="w-1 h-1 rounded-full {{ request()->routeIs('client.email.logs') ? 'bg-sky-500' : 'bg-gray-300' }}"></span>
                            <span class="truncate">Logs</span>
                        </a>
                    </div>
                </div>

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

    function toggleEmailMenu() {
        var submenu = document.getElementById('emailSubmenu');
        var chevron = document.getElementById('emailChevron');
        if (submenu.classList.contains('hidden')) {
            submenu.classList.remove('hidden');
            chevron.classList.add('rotate-180');
        } else {
            submenu.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }
    }

    function toggleReportsMenu() {
        var submenu = document.getElementById('reportsSubmenu');
        var chevron = document.getElementById('reportsChevron');
        if (submenu.classList.contains('hidden')) {
            submenu.classList.remove('hidden');
            chevron.classList.add('rotate-180');
        } else {
            submenu.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }
    }

    function toggleRevenueMenu() {
        var submenu = document.getElementById('revenueSubmenu');
        var chevron = document.getElementById('revenueChevron');
        if (submenu.classList.contains('hidden')) {
            submenu.classList.remove('hidden');
            chevron.classList.add('rotate-180');
        } else {
            submenu.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }
    }

    function toggleSuccessMenu() {
        var submenu = document.getElementById('successSubmenu');
        var chevron = document.getElementById('successChevron');
        if (submenu.classList.contains('hidden')) {
            submenu.classList.remove('hidden');
            chevron.classList.add('rotate-180');
        } else {
            submenu.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }
    }

    function toggleAlertsMenu() {
        var submenu = document.getElementById('alertsSubmenu');
        var chevron = document.getElementById('alertsChevron');
        if (submenu.classList.contains('hidden')) {
            submenu.classList.remove('hidden');
            chevron.classList.add('rotate-180');
        } else {
            submenu.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }
    }
    </script>
</body>
</html>