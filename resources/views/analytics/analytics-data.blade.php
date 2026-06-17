<?php
// resources/views/analytics/analytics-data.blade.php

?>
@extends('layouts.client')

@section('title', 'Analytics Data')
@section('header', $site->name . ' - Analytics')

@section('content')
<div class="space-y-6">
    <!-- Realtime Badge -->
    <div class="flex items-center gap-2">
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
        </span>
        <span class="text-sm font-medium text-emerald-700">
            {{ $realtimeVisitors }} active visitors now
        </span>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl p-6 border card-hover">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Pageviews (7d)</p>
                <i class="fas fa-eye text-blue-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $todayStats->pageviews ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Today</p>
        </div>
        <div class="bg-white rounded-xl p-6 border card-hover">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Unique Visitors</p>
                <i class="fas fa-users text-emerald-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $todayStats->unique_visitors ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Today</p>
        </div>
        <div class="bg-white rounded-xl p-6 border card-hover">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Sessions</p>
                <i class="fas fa-clock text-purple-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $todayStats->sessions ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Today</p>
        </div>
        <div class="bg-white rounded-xl p-6 border card-hover">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Bounce Rate</p>
                <i class="fas fa-percentage text-orange-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $todayStats->bounce_rate ?? 0 }}%</p>
            <p class="text-xs text-gray-400 mt-1">Today</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl border p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Traffic Overview (Last 7 Days)</h3>
            <canvas id="trafficChart" height="300"></canvas>
        </div>

        <!-- Countries -->
        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Top Countries</h3>
            <div class="space-y-3">
                @forelse(array_slice($countryData, 0, 8, true) as $country => $count)
                @php
                    $total = array_sum($countryData);
                    $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                    $countryNames = ['US' => 'United States', 'IN' => 'India', 'GB' => 'UK', 'CA' => 'Canada', 'AU' => 'Australia', 'DE' => 'Germany', 'FR' => 'France', 'BR' => 'Brazil', 'JP' => 'Japan', 'CN' => 'China'];
                    $flag = strtolower($country);
                @endphp
                <div class="flex items-center gap-3">
                    <img src="https://flagcdn.com/w40/{{ $flag }}.png" class="w-6 h-4 rounded object-cover" alt="{{ $country }}">
                    <div class="flex-1">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium">{{ $countryNames[$country] ?? $country }}</span>
                            <span class="text-gray-500">{{ $count }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">No data yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Pages -->
        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Top Pages</h3>
            <div class="space-y-2">
                @forelse(array_slice($pageData, 0, 10, true) as $page => $count)
                <div class="flex items-center justify-between py-2 border-b last:border-0">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file text-gray-400"></i>
                        <span class="text-sm text-gray-700 truncate max-w-xs">{{ $page }}</span>
                    </div>
                    <span class="text-sm font-medium text-gray-800">{{ $count }} views</span>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">No data yet</p>
                @endforelse
            </div>
        </div>

        <!-- Devices -->
        <div class="bg-white rounded-xl border p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Devices</h3>
            <div class="flex justify-center mb-4">
                <canvas id="deviceChart" width="200" height="200"></canvas>
            </div>
            <div class="flex justify-center gap-4 text-sm">
                @foreach($deviceData as $device => $count)
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full 
                        {{ $device === 'desktop' ? 'bg-blue-500' : ($device === 'mobile' ? 'bg-emerald-500' : 'bg-purple-500') }}">
                    </span>
                    <span class="text-gray-600 capitalize">{{ $device }} ({{ $count }})</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Back Link -->
    <div class="flex justify-between">
        <a href="{{ route('client.analytics.site.detail', $site->id) }}" 
           class="text-blue-600 hover:text-blue-800 font-medium">
            <i class="fas fa-code mr-2"></i>Get Tracking Code
        </a>
        <a href="{{ route('client.analytics.dashboard') }}" 
           class="text-gray-600 hover:text-gray-800 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>Back to Websites
        </a>
    </div>
</div>

@section('scripts')
<script>
// Traffic Chart
const ctx = document.getElementById('trafficChart').getContext('2d');
const trafficChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData->pluck('date')->map(fn($d) => $d->format('M d'))->toArray()) !!},
        datasets: [{
            label: 'Pageviews',
            data: {!! json_encode($chartData->pluck('pageviews')->toArray()) !!},
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Unique Visitors',
            data: {!! json_encode($chartData->pluck('unique_visitors')->toArray()) !!},
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Device Chart
const deviceCtx = document.getElementById('deviceChart').getContext('2d');
const deviceChart = new Chart(deviceCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($deviceData)) !!},
        datasets: [{
            data: {!! json_encode(array_values($deviceData)) !!},
            backgroundColor: ['#3b82f6', '#10b981', '#a855f7', '#f59e0b']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>
@endsection
@endsection