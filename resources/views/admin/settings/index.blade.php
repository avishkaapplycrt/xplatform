<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #1e293b;
            --gray: #64748b;
            --sidebar-width: 260px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f1f5f9;
            color: var(--dark);
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: white;
            position: fixed;
            height: 100vh;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h1 {
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 24px;
        }

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
        }

        .page-title p {
            color: var(--gray);
            margin-top: 4px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
        }

        .card-body {
            padding: 24px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 16px;
            background: #f8fafc;
            border-radius: 8px;
        }

        .info-label {
            color: var(--gray);
            font-size: 14px;
        }

        .info-value {
            font-weight: 600;
            color: var(--dark);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-ok {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }

        .cache-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 16px;
        }

        .cache-item {
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .cache-status {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }

        .cache-status.cached {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .cache-status.not-cached {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        @media(max-width:768px){
            .sidebar{transform:translateX(-100%)}
            .main-content{margin-left:0}
            .stats-grid,.settings-grid{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>🔮 Analytics Platform</h1>
                <p>Master Administration</p>
            </div>
            
            <nav class="nav-menu">
                <a href="{{ route('admin.dashboard') }}" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.clients.index') }}" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    All Clients
                </a>
                <a href="{{ route('admin.clients.pending') }}" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Pending Approval
                </a>
                <a href="{{ route('admin.analytics.index') }}" class="nav-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Analytics
                </a>
                <a href="{{ route('admin.settings.index') }}" class="nav-item active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-header">
                <div class="page-title">
                    <h2>System Settings</h2>
                    <p>Configure platform settings and view system information</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout
                    </button>
                </form>
            </div>

            <!-- System Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">System Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">PHP Version</span>
                            <span class="info-value">{{ $systemInfo['php_version'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Laravel Version</span>
                            <span class="info-value">{{ $systemInfo['laravel_version'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Database</span>
                            <span class="info-value">{{ $systemInfo['database_connection'] }} {{ $systemInfo['database_version'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Timezone</span>
                            <span class="info-value">{{ $systemInfo['timezone'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Debug Mode</span>
                            <span class="status-badge {{ $systemInfo['debug_mode'] === 'Enabled' ? 'status-ok' : '' }}">
                                <span class="status-dot"></span>
                                {{ $systemInfo['debug_mode'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cache Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cache Status</h3>
                </div>
                <div class="card-body">
                    <div class="cache-grid">
                        <div class="cache-item">
                            <div class="cache-status {{ $cacheStats['config_cached'] ? 'cached' : 'not-cached' }}">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($cacheStats['config_cached'])
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    @endif
                                </svg>
                            </div>
                            <div style="font-weight: 600;">Config</div>
                            <div style="font-size: 12px; color: var(--gray);">{{ $cacheStats['config_cached'] ? 'Cached' : 'Not Cached' }}</div>
                        </div>
                        <div class="cache-item">
                            <div class="cache-status {{ $cacheStats['routes_cached'] ? 'cached' : 'not-cached' }}">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($cacheStats['routes_cached'])
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    @endif
                                </svg>
                            </div>
                            <div style="font-weight: 600;">Routes</div>
                            <div style="font-size: 12px; color: var(--gray);">{{ $cacheStats['routes_cached'] ? 'Cached' : 'Not Cached' }}</div>
                        </div>
                        <div class="cache-item">
                            <div class="cache-status {{ $cacheStats['events_cached'] ? 'cached' : 'not-cached' }}">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($cacheStats['events_cached'])
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    @endif
                                </svg>
                            </div>
                            <div style="font-weight: 600;">Events</div>
                            <div style="font-size: 12px; color: var(--gray);">{{ $cacheStats['events_cached'] ? 'Cached' : 'Not Cached' }}</div>
                        </div>
                        <div class="cache-item">
                            <div class="cache-status {{ $cacheStats['views_cached'] ? 'cached' : 'not-cached' }}">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($cacheStats['views_cached'])
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    @endif
                                </svg>
                            </div>
                            <div style="font-weight: 600;">Views</div>
                            <div style="font-size: 12px; color: var(--gray);">{{ $cacheStats['views_cached'] ? 'Cached' : 'Not Cached' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Settings -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Application Settings</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">App Name</span>
                            <span class="info-value">{{ $settings['app_name'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">App URL</span>
                            <span class="info-value">{{ $settings['app_url'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Mail Driver</span>
                            <span class="info-value">{{ $settings['mail_driver'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Queue Driver</span>
                            <span class="info-value">{{ $settings['queue_driver'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Cache Driver</span>
                            <span class="info-value">{{ $settings['cache_driver'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Session Driver</span>
                            <span class="info-value">{{ $settings['session_driver'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>