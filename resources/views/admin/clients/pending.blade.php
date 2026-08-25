<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approval — Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #6366f1; --primary-dark: #4f46e5; --secondary: #ec4899;
            --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
            --info: #3b82f6; --dark: #1e293b; --light: #f8fafc; --gray: #64748b;
            --sidebar-width: 260px;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; color: var(--dark); }

        .admin-wrapper { display: flex; min-height: 100vh; }

        .sidebar { width: var(--sidebar-width); background: var(--dark); color: white; position: fixed; height: 100vh; overflow-y: auto; transition: transform 0.3s; z-index: 50; }
        .sidebar-header { padding: 24px; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar-header h1 { font-size: 20px; font-weight: 700; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .sidebar-header p { font-size: 12px; color: var(--gray); margin-top: 4px; }
        .nav-menu { padding: 16px 0; }
        .nav-item { display: flex; align-items: center; padding: 12px 24px; color: #94a3b8; text-decoration: none; transition: all 0.2s; border-left: 3px solid transparent; font-size: 14px; }
        .nav-item:hover, .nav-item.active { background: rgba(99,102,241,.1); color: white; border-left-color: var(--primary); }
        .nav-item svg { width: 20px; height: 20px; margin-right: 12px; flex-shrink: 0; }

        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 24px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #e2e8f0; }
        .page-title h2 { font-size: 28px; font-weight: 700; color: var(--dark); }
        .page-title p { color: var(--gray); margin-top: 4px; }

        .btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s; border: none; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: var(--success); color: white; font-size: 12px; padding: 6px 13px; border-radius: 6px; border: none; cursor: pointer; }
        .btn-success:hover { background: #059669; }
        .btn-view { background: rgba(99,102,241,.1); color: var(--primary); font-size: 12px; padding: 6px 13px; border-radius: 6px; border: 1px solid rgba(99,102,241,.2); cursor: pointer; font-family: inherit; text-decoration: none; }
        .btn-view:hover { background: var(--primary); color: white; }
        .btn-del { background: rgba(239,68,68,.1); color: var(--danger); font-size: 12px; padding: 6px 13px; border-radius: 6px; border: 1px solid rgba(239,68,68,.2); cursor: pointer; font-family: inherit; }
        .btn-del:hover { background: var(--danger); color: white; }

        .alert { padding: 12px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
        .alert-success { background: rgba(16,185,129,.1); color: #065f46; border: 1px solid rgba(16,185,129,.2); }

        .card { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.1); overflow: hidden; }
        .card-header { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 18px; font-weight: 700; color: var(--dark); }
        .empty-state { padding: 64px 24px; text-align: center; color: var(--gray); }
        .empty-state svg { width: 48px; height: 48px; margin: 0 auto 16px; opacity: .3; display: block; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 14px 16px; font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--gray); border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
        .data-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 13.5px; vertical-align: middle; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: #f8fafc; }

        .cli-info { display: flex; align-items: center; gap: 10px; }
        .cli-avatar { width: 36px; height: 36px; border-radius: 9px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 13px; flex-shrink: 0; }
        .cli-name { font-weight: 600; color: var(--dark); font-size: 14px; }
        .cli-sub { font-size: 12px; color: var(--gray); margin-top: 1px; }

        .actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }

        @media(max-width:768px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <x-admin-sidebar />

    <main class="main-content">
        <div class="top-header">
            <div class="page-title">
                <h2>Pending Approval</h2>
                <p>Clients awaiting activation.</p>
            </div>
            <div style="display:flex;gap:12px;align-items:center">
                <span style="color:var(--gray);font-size:14px">{{ now()->format('F j, Y') }}</span>
                <form method="POST" action="{{ route('admin.logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pending Clients</h3>
                <span style="font-size:13px;color:var(--gray)">{{ $clients->count() }} total</span>
            </div>

            @if($clients->isEmpty())
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>No clients pending approval.</p>
                </div>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Requested</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                            <tr>
                                <td>
                                    <div class="cli-info">
                                        <div class="cli-avatar">{{ strtoupper(substr($client->company_name ?: $client->email, 0, 2)) }}</div>
                                        <div>
                                            <div class="cli-name">{{ $client->company_name ?: '—' }}</div>
                                            <div class="cli-sub">{{ $client->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color:var(--gray);font-size:12px;white-space:nowrap">
                                    {{ $client->created_at->format('d M Y') }}<br>{{ $client->created_at->diffForHumans() }}
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.clients.show', $client) }}" class="btn-view">View</a>
                                        <form method="POST" action="{{ route('admin.clients.approve', $client) }}">
                                            @csrf
                                            <button type="submit" class="btn-success">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" onsubmit="return confirm('Delete this client? This cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-del">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </main>
</div>
</body>
</html>
