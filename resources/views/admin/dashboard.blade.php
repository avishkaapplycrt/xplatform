<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - {{ config('app.name') }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --sidebar-width: 260px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f1f5f9;
            color: var(--dark);
        }

        /* Layout */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h1 {
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-header p {
            font-size: 12px;
            color: var(--gray);
            margin-top: 4px;
        }

        .nav-menu {
            padding: 16px 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(99, 102, 241, 0.1);
            color: white;
            border-left-color: var(--primary);
        }

        .nav-item svg {
            width: 20px;
            height: 20px;
            margin-right: 12px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 24px;
        }

        /* Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .page-title h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }

        .page-title p {
            color: var(--gray);
            margin-top: 4px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .stat-card.success::before { background: var(--success); }
        .stat-card.warning::before { background: var(--warning); }
        .stat-card.danger::before { background: var(--danger); }
        .stat-card.info::before { background: var(--info); }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(99, 102, 241, 0.1);
        }

        .stat-icon svg {
            width: 24px;
            height: 24px;
            color: var(--primary);
        }

        .stat-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge-warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .badge-danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray);
        }

        .stat-footer {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .trend-up { color: var(--success); }
        .trend-down { color: var(--danger); }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
        }

        .card-body {
            padding: 24px;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
        }

        /* Activity Feed */
        .activity-list {
            space-y: 16px;
        }

        .activity-item {
            display: flex;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-icon.blue { background: rgba(59, 130, 246, 0.1); color: var(--info); }
        .activity-icon.green { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .activity-icon.purple { background: rgba(99, 102, 241, 0.1); color: var(--primary); }
        .activity-icon.orange { background: rgba(245, 158, 11, 0.1); color: var(--warning); }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-size: 14px;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .activity-time {
            font-size: 12px;
            color: var(--gray);
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 16px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--gray);
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .data-table tr:hover td {
            background: #f8fafc;
        }

        .client-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .client-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .client-details h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
        }

        .client-details p {
            font-size: 12px;
            color: var(--gray);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .status-suspended { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

        /* Plan badges */
        .plan-badge {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .plan-free { background: #f3f4f6; color: #6b7280; }
        .plan-starter { background: #dbeafe; color: #1d4ed8; }
        .plan-professional { background: #fce7f3; color: #be185d; }
        .plan-enterprise { background: #ddd6fe; color: #5b21b6; }

        /* Health Grid */
        .health-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 24px;
        }

        .health-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .health-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .health-label {
            font-size: 12px;
            color: var(--gray);
        }

        .health-status {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-left: 8px;
        }

        .status-healthy { background: var(--success); }
        .status-warning { background: var(--warning); }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .health-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <!-- <aside class="sidebar">
            <div class="sidebar-header">
                <h1>🔮 Analytics Platform</h1>
                <p>Master Administration</p>
            </div>
            
            <nav class="nav-menu">
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.clients.index') }}" class="nav-item {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    All Clients
                </a>
                <a href="{{ route('admin.clients.pending') }}" class="nav-item {{ request()->routeIs('admin.clients.pending') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Pending Approval
                    @php
                        $pendingCount = \App\Models\Client::where('status', 'pending')->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span style="margin-left: auto; background: var(--warning); color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px;">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.analytics.index') }}" class="nav-item {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Analytics
                </a>
                <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Settings
                </a>
            </nav>
        </aside> -->
        <x-admin-sidebar />

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <div class="top-header">
                <div class="page-title">
                    <h2>System Overview</h2>
                    <p>Welcome back! Here's what's happening with your platform today.</p>
                </div>
                <div class="header-actions">
                    <span style="color: var(--gray); font-size: 14px;">{{ now()->format('F j, Y') }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <span class="stat-badge badge-success">+12%</span>
                    </div>
                    <div class="stat-value">{{ $stats['total_clients'] }}</div>
                    <div class="stat-label">Total Clients</div>
                    <div class="stat-footer">
                        <span class="trend-up">↑ 12%</span>
                        <span>from last month</span>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1);">
                            <svg style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="stat-badge badge-success">Active</span>
                    </div>
                    <div class="stat-value" style="color: var(--success);">{{ $stats['active_clients'] }}</div>
                    <div class="stat-label">Active Clients</div>
                    <div class="stat-footer">
                        <span>{{ round(($stats['active_clients'] / max($stats['total_clients'], 1)) * 100) }}% activation rate</span>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1);">
                            <svg style="color: var(--warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        @if($stats['pending_approval'] > 0)
                            <span class="stat-badge badge-warning">Action Needed</span>
                        @endif
                    </div>
                    <div class="stat-value" style="color: var(--warning);">{{ $stats['pending_approval'] }}</div>
                    <div class="stat-label">Pending Approval</div>
                    <div class="stat-footer">
                        <a href="{{ route('admin.clients.pending') }}" style="color: var(--warning); text-decoration: none;">Review applications →</a>
                    </div>
                </div>

                <div class="stat-card info">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1);">
                            <svg style="color: var(--info);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                        </div>
                        <span class="stat-badge badge-success">{{ round(($stats['data_sources']['connected'] / max($stats['data_sources']['total'], 1)) * 100) }}%</span>
                    </div>
                    <div class="stat-value" style="color: var(--info);">{{ $stats['data_sources']['connected'] }}/{{ $stats['data_sources']['total'] }}</div>
                    <div class="stat-label">Data Sources Connected</div>
                    <div class="stat-footer">
                        @if($stats['data_sources']['failed'] > 0)
                            <span class="trend-down">{{ $stats['data_sources']['failed'] }} failed</span>
                        @else
                            <span class="trend-up">All systems operational</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Chart Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Weekly Growth</h3>
                        <select style="padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px;">
                            <option>Last 7 Days</option>
                            <option>Last 30 Days</option>
                            <option>This Year</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="growthChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Activity Feed -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                        <a href="#" style="font-size: 14px; color: var(--primary); text-decoration: none;">View All</a>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <div class="activity-list" style="padding: 0 24px;">
                            @foreach($recentActivity as $activity)
                                <div class="activity-item">
                                    <div class="activity-icon {{ $activity['color'] }}">
                                        @if($activity['icon'] === 'user-plus')
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                        @elseif($activity['icon'] === 'database')
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                                        @else
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                                        @endif
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">{{ $activity['message'] }}</div>
                                        <div class="activity-time">{{ $activity['time']->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Clients Table -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h3 class="card-title">Recent Clients</h3>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">
                        View All Clients
                    </a>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Data Sources</th>
                                <th>Insights</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentClients as $client)
                                <tr>
                                    <td>
                                        <div class="client-info">
                                            <div class="client-avatar">{{ strtoupper(substr($client->company_name, 0, 2)) }}</div>
                                            <div class="client-details">
                                                <h4>{{ $client->company_name }}</h4>
                                                <p>{{ $client->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="plan-badge plan-{{ $client->plan }}">{{ $client->plan }}</span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $client->status }}">{{ ucfirst($client->status) }}</span>
                                    </td>
                                    <td>{{ $client->connected_sources }}</td>
                                    <td>{{ $client->insights_count ?? 0 }}</td>
                                    <td>{{ $client->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- System Health -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">System Health</h3>
                    <span style="display: flex; align-items: center; font-size: 14px; color: var(--success);">
                        <span class="health-status status-healthy"></span>
                        All Systems Operational
                    </span>
                </div>
                <div class="card-body">
                    <div class="health-grid">
                        <div class="health-item">
                            <div class="health-value">{{ $health['database_size_mb'] }} MB</div>
                            <div class="health-label">Database Size</div>
                        </div>
                        <div class="health-item">
                            <div class="health-value">{{ $health['queue_size'] }}</div>
                            <div class="health-label">Queue Jobs</div>
                        </div>
                        <div class="health-item">
                            <div class="health-value">{{ $health['failed_jobs'] }}</div>
                            <div class="health-label">Failed Jobs</div>
                        </div>
                        <div class="health-item">
                            <div class="health-value">{{ $health['avg_response_time'] }}</div>
                            <div class="health-label">Avg Response</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Growth Chart
        const ctx = document.getElementById('growthChart').getContext('2d');
        const growthChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($weeklyData, 'day')) !!},
                datasets: [{
                    label: 'New Clients',
                    data: {!! json_encode(array_column($weeklyData, 'new_clients')) !!},
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }, {
                    label: 'Active Users',
                    data: {!! json_encode(array_map(fn($v) => $v / 100, array_column($weeklyData, 'active_users'))) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                        },
                        bodyFont: {
                            size: 13,
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 12,
                            },
                            color: '#64748b',
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(226, 232, 240, 0.6)',
                        },
                        ticks: {
                            font: {
                                size: 12,
                            },
                            color: '#64748b',
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            display: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return value + 'k';
                            },
                            font: {
                                size: 12,
                            },
                            color: '#64748b',
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>