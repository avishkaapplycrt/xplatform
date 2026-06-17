<?php
// resources/views/analytics/layout.blade.php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Analytics Dashboard') - xPlatforms</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { transition: all 0.3s; }
        .card-hover { transition: transform 0.2s, box-shadow 0.2s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
        .animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
    </style>
    @yield('styles')
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="sidebar w-64 bg-slate-900 text-white flex-shrink-0">
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-xl font-bold flex items-center gap-2">
                    <i class="fas fa-chart-line text-emerald-400"></i>
                    Analytics
                </h1>
            </div>
            <nav class="p-4 space-y-2">
                <a href="{{ route('client.analytics.dashboard') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('client.analytics.dashboard') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    <i class="fas fa-home w-5"></i>
                    Dashboard
                </a>
                <a href="{{ route('client.analytics.site.create') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('client.analytics.site.create') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    <i class="fas fa-plus-circle w-5"></i>
                    Add Website
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <header class="bg-white border-b px-8 py-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">@yield('header')</h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                    <a href="{{ route('client.logout') }}" class="text-sm text-red-500 hover:text-red-700">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </header>

            <div class="p-8">
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @yield('scripts')
</body>
</html>