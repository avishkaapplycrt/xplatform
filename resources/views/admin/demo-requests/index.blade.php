<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Requests — Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #6366f1; --primary-dark: #4f46e5; --secondary: #ec4899;
            --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
            --info: #3b82f6; --dark: #1e293b; --light: #f8fafc; --gray: #64748b;
            --sidebar-width: 260px;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; color: var(--dark); }

        /* Layout */
        .admin-wrapper { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: var(--sidebar-width); background: var(--dark); color: white; position: fixed; height: 100vh; overflow-y: auto; transition: transform 0.3s; z-index: 50; }
        .sidebar-header { padding: 24px; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar-header h1 { font-size: 20px; font-weight: 700; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .sidebar-header p { font-size: 12px; color: var(--gray); margin-top: 4px; }
        .nav-menu { padding: 16px 0; }
        .nav-item { display: flex; align-items: center; padding: 12px 24px; color: #94a3b8; text-decoration: none; transition: all 0.2s; border-left: 3px solid transparent; font-size: 14px; }
        .nav-item:hover, .nav-item.active { background: rgba(99,102,241,.1); color: white; border-left-color: var(--primary); }
        .nav-item svg { width: 20px; height: 20px; margin-right: 12px; flex-shrink: 0; }

        /* Main */
        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 24px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #e2e8f0; }
        .page-title h2 { font-size: 28px; font-weight: 700; color: var(--dark); }
        .page-title p { color: var(--gray); margin-top: 4px; }

        /* Buttons */
        .btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s; border: none; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: var(--success); color: white; font-size: 12px; padding: 6px 13px; border-radius: 6px; border: none; cursor: pointer; }
        .btn-success:hover { background: #059669; }
        .btn-view { background: rgba(99,102,241,.1); color: var(--primary); font-size: 12px; padding: 6px 13px; border-radius: 6px; border: 1px solid rgba(99,102,241,.2); cursor: pointer; font-family: inherit; }
        .btn-view:hover { background: var(--primary); color: white; }
        .btn-del { background: rgba(239,68,68,.1); color: var(--danger); font-size: 12px; padding: 6px 13px; border-radius: 6px; border: 1px solid rgba(239,68,68,.2); cursor: pointer; font-family: inherit; }
        .btn-del:hover { background: var(--danger); color: white; }

        /* Filter tabs */
        .filter-tabs { display: flex; gap: 8px; margin-bottom: 20px; }
        .filter-tab { padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 500; text-decoration: none; color: var(--gray); background: white; border: 1px solid #e2e8f0; transition: all .2s; }
        .filter-tab:hover { border-color: var(--primary); color: var(--primary); }
        .filter-tab.active { background: var(--primary); color: white; border-color: var(--primary); }
        .tab-count { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 10px; font-size: 11px; font-weight: 700; margin-left: 6px; background: rgba(255,255,255,.25); }
        .filter-tab:not(.active) .tab-count { background: #f1f5f9; color: var(--dark); }

        /* Alert */
        .alert { padding: 12px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
        .alert-success { background: rgba(16,185,129,.1); color: #065f46; border: 1px solid rgba(16,185,129,.2); }

        /* Card */
        .card { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.1); overflow: hidden; }
        .card-header { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 18px; font-weight: 700; color: var(--dark); }
        .empty-state { padding: 64px 24px; text-align: center; color: var(--gray); }
        .empty-state svg { width: 48px; height: 48px; margin: 0 auto 16px; opacity: .3; display: block; }

        /* Table */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 14px 16px; font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--gray); border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
        .data-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 13.5px; vertical-align: middle; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: #f8fafc; }

        .req-info { display: flex; align-items: center; gap: 10px; }
        .req-avatar { width: 36px; height: 36px; border-radius: 9px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 13px; flex-shrink: 0; }
        .req-name { font-weight: 600; color: var(--dark); font-size: 14px; }
        .req-sub { font-size: 12px; color: var(--gray); margin-top: 1px; }

        .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
        .status-pending  { background: rgba(245,158,11,.1); color: #b45309; }
        .status-approved { background: rgba(16,185,129,.1); color: #065f46; }
        .status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

        .demo-slot { display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; background: #eff6ff; border-radius: 5px; font-size: 12px; color: #1d4ed8; font-weight: 500; }

        .actions { display: flex; gap: 6px; align-items: center; }

        /* Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 100; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal { background: white; border-radius: 16px; width: 560px; max-width: 95vw; max-height: 88vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
        .modal-head { padding: 24px 28px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: flex-start; }
        .modal-head h3 { font-size: 18px; font-weight: 700; }
        .modal-close { background: none; border: none; font-size: 22px; color: var(--gray); cursor: pointer; line-height: 1; padding: 0 4px; }
        .modal-close:hover { color: var(--dark); }
        .modal-body { padding: 24px 28px; }
        .modal-section { margin-bottom: 20px; }
        .modal-section-title { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .8px; color: var(--gray); margin-bottom: 10px; }
        .modal-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
        .modal-field label { display: block; font-size: 11px; color: var(--gray); margin-bottom: 3px; text-transform: uppercase; letter-spacing: .5px; }
        .modal-field p { font-size: 14px; color: var(--dark); font-weight: 500; }
        .modal-field.full { grid-column: span 2; }
        .modal-notes { background: #f8fafc; border-radius: 8px; padding: 12px 14px; font-size: 13.5px; color: var(--dark); line-height: 1.6; }
        .modal-foot { padding: 16px 28px 24px; display: flex; gap: 10px; border-top: 1px solid #e2e8f0; }

        /* Pagination */
        .pag-wrap { padding: 16px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; }

        @media(max-width:768px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <x-admin-sidebar />

    <main class="main-content">
        <div class="top-header">
            <div class="page-title">
                <h2>Demo Requests</h2>
                <p>Manage inbound demo bookings from the website.</p>
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

        <div class="filter-tabs">
            <a href="{{ route('admin.demo-requests.index') }}" class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">
                All <span class="tab-count">{{ $counts['all'] }}</span>
            </a>
            <a href="{{ route('admin.demo-requests.index', ['status' => 'pending']) }}" class="filter-tab {{ $filter === 'pending' ? 'active' : '' }}">
                Pending <span class="tab-count">{{ $counts['pending'] }}</span>
            </a>
            <a href="{{ route('admin.demo-requests.index', ['status' => 'approved']) }}" class="filter-tab {{ $filter === 'approved' ? 'active' : '' }}">
                Approved <span class="tab-count">{{ $counts['approved'] }}</span>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $filter === 'all' ? 'All Requests' : ucfirst($filter).' Requests' }}</h3>
                <span style="font-size:13px;color:var(--gray)">{{ $demos->total() }} total</span>
            </div>

            @if($demos->isEmpty())
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p>No {{ $filter !== 'all' ? $filter.' ' : '' }}demo requests yet.</p>
                </div>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Requester</th>
                            <th>Company</th>
                            <th>Demo Slot</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($demos as $demo)
                            <tr>
                                <td>
                                    <div class="req-info">
                                        <div class="req-avatar">{{ strtoupper(substr($demo->first_name,0,1).substr($demo->last_name,0,1)) }}</div>
                                        <div>
                                            <div class="req-name">{{ $demo->first_name }} {{ $demo->last_name }}</div>
                                            <div class="req-sub">{{ $demo->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:600;color:var(--dark)">{{ $demo->company_name }}</div>
                                    <div class="req-sub">{{ $demo->job_title }}</div>
                                </td>
                                <td>
                                    @if($demo->demo_date)
                                        <div class="demo-slot">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ $demo->demo_date->format('d M Y') }}{{ $demo->demo_time ? ' · '.$demo->demo_time : '' }}
                                        </div>
                                    @else
                                        <span style="color:var(--gray);font-size:12px">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $demo->status }}">
                                        <span class="status-dot"></span>{{ ucfirst($demo->status) }}
                                    </span>
                                </td>
                                <td style="color:var(--gray);font-size:12px;white-space:nowrap">
                                    {{ $demo->created_at->format('d M Y') }}<br>{{ $demo->created_at->diffForHumans() }}
                                </td>
                                <td>
                                    <div class="actions">
                                        <button class="btn-view" onclick="openModal({{ $demo->id }})">View</button>
                                        @if($demo->status === 'pending')
                                            <form method="POST" action="{{ route('admin.demo-requests.approve', $demo) }}">
                                                @csrf
                                                <button type="submit" class="btn-success">Approve</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.demo-requests.destroy', $demo) }}" onsubmit="return confirm('Delete this request?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-del">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($demos->hasPages())
                    <div class="pag-wrap">{{ $demos->links() }}</div>
                @endif
            @endif
        </div>
    </main>
</div>

{{-- Detail modals --}}
@foreach($demos as $demo)
<div class="modal-overlay" id="modal-{{ $demo->id }}" onclick="if(event.target===this)closeModal({{ $demo->id }})">
    <div class="modal">
        <div class="modal-head">
            <div>
                <h3>{{ $demo->first_name }} {{ $demo->last_name }}</h3>
                <div style="font-size:13px;color:var(--gray);margin-top:2px">{{ $demo->email }}</div>
            </div>
            <button class="modal-close" onclick="closeModal({{ $demo->id }})">&times;</button>
        </div>
        <div class="modal-body">

            <div class="modal-section">
                <div class="modal-section-title">Contact &amp; Company</div>
                <div class="modal-row">
                    <div class="modal-field"><label>Job Title</label><p>{{ $demo->job_title ?: '—' }}</p></div>
                    <div class="modal-field"><label>Company</label><p>{{ $demo->company_name }}</p></div>
                    <div class="modal-field"><label>Company Size</label><p>{{ $demo->company_size ?: '—' }}</p></div>
                    <div class="modal-field"><label>Industry</label><p>{{ $demo->industry ?: '—' }}</p></div>
                </div>
            </div>

            <div class="modal-section">
                <div class="modal-section-title">Business Context</div>
                <div class="modal-row">
                    <div class="modal-field"><label>Monthly Active Customers</label><p>{{ $demo->monthly_active_customers ?: '—' }}</p></div>
                    <div class="modal-field"><label>Monthly Revenue</label><p>{{ $demo->monthly_revenue ?: '—' }}</p></div>
                    <div class="modal-field full"><label>Primary Challenge</label><p>{{ $demo->primary_challenge ?: '—' }}</p></div>
                    <div class="modal-field full"><label>Data Sources</label><p>{{ $demo->data_sources ?: '—' }}</p></div>
                </div>
            </div>

            @if($demo->demo_notes)
            <div class="modal-section">
                <div class="modal-section-title">Notes</div>
                <div class="modal-notes">{{ $demo->demo_notes }}</div>
            </div>
            @endif

            <div class="modal-section" style="margin-bottom:0">
                <div class="modal-section-title">Demo Slot</div>
                <div class="modal-row">
                    <div class="modal-field"><label>Date</label><p>{{ $demo->demo_date ? $demo->demo_date->format('d M Y') : '—' }}</p></div>
                    <div class="modal-field"><label>Time</label><p>{{ $demo->demo_time ?: '—' }}</p></div>
                    <div class="modal-field"><label>Timezone</label><p>{{ $demo->timezone ?: '—' }}</p></div>
                    <div class="modal-field"><label>Status</label>
                        <p><span class="status-badge status-{{ $demo->status }}"><span class="status-dot"></span>{{ ucfirst($demo->status) }}</span></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            @if($demo->status === 'pending')
                <form method="POST" action="{{ route('admin.demo-requests.approve', $demo) }}">
                    @csrf
                    <button type="submit" class="btn btn-success" style="padding:10px 20px;font-size:14px">Approve</button>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.demo-requests.destroy', $demo) }}" onsubmit="return confirm('Delete this request?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger" style="padding:10px 20px;font-size:14px">Delete</button>
            </form>
            <button onclick="closeModal({{ $demo->id }})" style="margin-left:auto;background:none;border:1px solid #e2e8f0;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:14px;color:var(--gray)">Close</button>
        </div>
    </div>
</div>
@endforeach

<script>
function openModal(id) { document.getElementById('modal-' + id).classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById('modal-' + id).classList.remove('open'); document.body.style.overflow = ''; }
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open')); });
</script>
</body>
</html>
