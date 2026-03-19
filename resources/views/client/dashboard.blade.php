{{-- resources/views/client/dashboard.blade.php --}}
@extends('layouts.client')

@section('title', 'Dashboard')

@section('content')
<div x-data="analyticsDashboard()" x-init="init()" class="space-y-6">
    
    <!-- Connection Status -->
    <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div :class="connectionStatus === 'connected' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                 class="px-3 py-1 rounded-full text-sm font-medium flex items-center">
                <span class="w-2 h-2 rounded-full mr-2" 
                      :class="connectionStatus === 'connected' ? 'bg-green-500 animate-pulse' : 'bg-red-500'"></span>
                <span x-text="connectionStatus === 'connected' ? 'Live Data' : 'Disconnected'"></span>
            </div>
            <span class="text-sm text-gray-500">
                Source: <span x-text="activeSource"></span>
            </span>
        </div>
        <div class="text-sm text-gray-500" x-show="lastUpdated">
            Last updated: <span x-text="lastUpdated"></span>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Active Users -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-gray-500 text-sm font-medium">Active Users (5m)</h3>
                <span class="p-2 bg-blue-50 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-gray-900" x-text="formatNumber(metrics.active_users)">-</p>
        </div>

        <!-- Revenue Today -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-gray-500 text-sm font-medium">Revenue Today</h3>
                <span class="p-2 bg-green-50 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-gray-900" x-text="formatCurrency(metrics.revenue_today)">$0</p>
        </div>

        <!-- Orders Today -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-gray-500 text-sm font-medium">Orders Today</h3>
                <span class="p-2 bg-purple-50 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-gray-900" x-text="formatNumber(metrics.orders_today)">-</p>
        </div>

        <!-- Avg Order Value -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-gray-500 text-sm font-medium">Avg Order Value</h3>
                <span class="p-2 bg-orange-50 rounded-lg">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-gray-900" 
               x-text="formatCurrency(metrics.orders_today > 0 ? metrics.revenue_today / metrics.orders_today : 0)">$0</p>
        </div>
    </div>

    <!-- Insights -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">AI-Powered Insights</h3>
        <div class="space-y-4">
            @forelse($insights as $insight)
                <div class="border-l-4 p-4 rounded-r-lg 
                    {{ $insight->category === 'growth' ? 'border-green-500 bg-green-50' : '' }}
                    {{ $insight->category === 'risk' ? 'border-red-500 bg-red-50' : '' }}
                    {{ $insight->category === 'anomaly' ? 'border-yellow-500 bg-yellow-50' : '' }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $insight->title }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ $insight->description }}</p>
                        </div>
                        <span class="text-xs font-medium px-2 py-1 rounded
                            {{ $insight->impact_level === 'high' ? 'bg-green-200 text-green-800' : '' }}
                            {{ $insight->impact_level === 'medium' ? 'bg-yellow-200 text-yellow-800' : '' }}
                            {{ $insight->impact_level === 'low' ? 'bg-gray-200 text-gray-800' : '' }}">
                            {{ $insight->impact_level }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No new insights. Check back later!</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function analyticsDashboard() {
    return {
        connectionStatus: 'connecting',
        activeSource: '{{ $dataSources->first()->connection_name ?? "None" }}',
        lastUpdated: null,
        metrics: {
            active_users: {{ $metrics['active_users']['value'] ?? 0 }},
            revenue_today: {{ $metrics['revenue_today']['value'] ?? 0 }},
            orders_today: {{ $metrics['orders_today']['value'] ?? 0 }},
        },
        
        init() {
            this.initWebSocket();
            this.fetchInitialData();
            setInterval(() => this.pollData(), 30000);
        },
        
        initWebSocket() {
            window.Pusher = Pusher;
            
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ config('broadcasting.connections.pusher.key') }}',
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                forceTLS: true,
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Authorization': 'Bearer {{ $wsToken }}'
                    }
                }
            });
            
            window.Echo.private('client.{{ auth('client')->id() }}.analytics')
                .listen('.metric.update', (e) => {
                    this.connectionStatus = 'connected';
                    this.metrics[e.metric] = e.value;
                    this.lastUpdated = new Date(e.timestamp).toLocaleTimeString();
                })
                .error(() => {
                    this.connectionStatus = 'error';
                });
        },
        
        async fetchInitialData() {
            try {
                const res = await fetch('{{ route('client.analytics.metrics') }}');
                const data = await res.json();
                this.metrics = { ...this.metrics, ...data.metrics };
                this.connectionStatus = 'connected';
                this.lastUpdated = new Date().toLocaleTimeString();
            } catch (e) {
                this.connectionStatus = 'error';
            }
        },
        
        async pollData() {
            if (this.connectionStatus === 'connected') return;
            await this.fetchInitialData();
        },
        
        formatNumber(num) {
            return num?.toLocaleString() ?? '-';
        },
        
        formatCurrency(num) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: '{{ auth('client')->user()->currency }}'
            }).format(num || 0);
        }
    }
}
</script>
@endpush
@endsection