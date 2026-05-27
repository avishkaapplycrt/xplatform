<style>
.mob-ham{display:none;position:fixed;top:14px;left:14px;z-index:200;background:var(--primary,#6366f1);color:#fff;border:none;border-radius:8px;padding:8px 10px;cursor:pointer;font-size:18px;line-height:1;box-shadow:0 2px 8px rgba(0,0,0,.2)}
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:98}
.sidebar.mob-open{transform:translateX(0)!important;z-index:99}
@media(max-width:768px){.mob-ham{display:block}.sidebar-overlay.open{display:block}}
</style>
<button class="mob-ham" id="mobHam" onclick="toggleAdminSidebar()" aria-label="Open menu">&#9776;</button>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleAdminSidebar()"></div>
<aside class="sidebar" id="adminSidebar">
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
        <a href="{{ route('admin.demo-requests.index') }}" class="nav-item {{ request()->routeIs('admin.demo-requests.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Demo Requests
            @php $pendingDemos = \App\Models\BookDemo::where('status','pending')->count(); @endphp
            @if($pendingDemos > 0)
                <span style="margin-left:auto;background:var(--info);color:white;padding:2px 8px;border-radius:10px;font-size:12px">{{ $pendingDemos }}</span>
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
</aside>
<script>
function toggleAdminSidebar(){
    var s=document.getElementById('adminSidebar'),o=document.getElementById('sidebarOverlay');
    s.classList.toggle('mob-open');
    o.classList.toggle('open');
    document.body.style.overflow=s.classList.contains('mob-open')?'hidden':'';
}
</script>