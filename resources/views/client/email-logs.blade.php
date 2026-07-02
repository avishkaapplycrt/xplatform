@extends('layouts.platform')
@section('title', 'Email History & Logs')

@push('styles')
<style>
  .el-root { display: flex; flex-direction: column; height: 100vh; overflow: hidden; background: #f9fafb; }
  .el-main-scroll { flex: 1; min-height: 0; overflow-y: auto; padding: 16px 20px; }
  .el-topbar { flex-shrink: 0; background: #fff; border-bottom: 1px solid #e5e7eb; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; z-index: 10; }
  .el-topbar-title { font-size: 16px; font-weight: 600; color: #111827; }
  .el-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 999px; }
  .el-pill.live { background: #dcfce7; color: #166534; }
  .el-pill.demo { background: #fef3c7; color: #92400e; }

  .el-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; max-width: 1200px; margin: 0 auto 20px; }
  .el-stat-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; }
  .el-stat-val { font-size: 24px; font-weight: 700; color: #111827; line-height: 1; }
  .el-stat-lbl { font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-top: 6px; }
  .el-stat-delta { font-size: 11px; font-weight: 600; margin-top: 4px; }
  .el-stat-delta.up { color: #10B981; }
  .el-stat-delta.down { color: #F43F5E; }

  .el-filters { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px 20px; margin-bottom: 16px; max-width: 1200px; margin: 0 auto 16px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
  .el-filter-group { display: flex; align-items: center; gap: 6px; }
  .el-filter-label { font-size: 11px; font-weight: 600; color: #374151; text-transform: uppercase; }
  .el-filter-input { padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 12px; outline: none; }
  .el-filter-input:focus { border-color: #0EA5E9; }
  .el-filter-select { padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 12px; outline: none; background: #fff; cursor: pointer; }
  .el-btn { display: inline-flex; align-items: center; gap: 4px; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all .15s; text-decoration: none; }
  .el-btn-primary { background: #0EA5E9; color: #fff; }
  .el-btn-secondary { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
  .el-btn-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

  .el-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; max-width: 1200px; margin: 0 auto; }
  .el-table { width: 100%; border-collapse: collapse; }
  .el-table th { padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
  .el-table td { padding: 12px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
  .el-table tr:hover td { background: #f9fafb; }
  .el-table tr:last-child td { border-bottom: none; }

  .el-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
  .el-email { font-size: 12px; color: #0EA5E9; font-weight: 500; }
  .el-subject { font-size: 13px; font-weight: 600; color: #111827; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .el-date { font-size: 11px; color: #6b7280; }
  .el-preview { font-size: 11px; color: #9ca3af; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

  .el-pagination { display: flex; justify-content: center; gap: 4px; padding: 16px; }
  .el-page { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; color: #374151; border: 1px solid #e5e7eb; }
  .el-page.active { background: #0EA5E9; color: #fff; border-color: #0EA5E9; }
  .el-page:hover:not(.active) { background: #f3f4f6; }

  .el-empty { text-align: center; padding: 60px 20px; color: #9ca3af; }
  .el-empty svg { width: 48px; height: 48px; margin-bottom: 16px; opacity: .4; }

  .el-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 100; align-items: center; justify-content: center; }
  .el-modal.active { display: flex; }
  .el-modal-box { background: #fff; border-radius: 12px; width: 600px; max-width: 92%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0,0,0,.1); }
  .el-modal-hd { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
  .el-modal-hd h3 { margin: 0; font-size: 15px; font-weight: 600; color: #111827; }
  .el-modal-body { padding: 20px; overflow-y: auto; font-size: 13px; line-height: 1.6; }
  .el-modal-ft { padding: 12px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px; }

  .el-detail-row { display: flex; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
  .el-detail-row:last-child { border-bottom: none; }
  .el-detail-label { width: 140px; font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; flex-shrink: 0; }
  .el-detail-value { flex: 1; font-size: 13px; color: #111827; word-break: break-word; }
  .el-detail-body { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; font-family: monospace; font-size: 12px; white-space: pre-wrap; max-height: 300px; overflow-y: auto; }

  @media (max-width: 768px) {
    .el-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .el-filters { flex-direction: column; align-items: stretch; }
    .el-filter-group { width: 100%; }
    .el-filter-input, .el-filter-select { width: 100%; }
  }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Test Company';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
  $isLive = $dataSourceConnected ?? false;
@endphp

<div class="el-root">

  {{-- ── TOP BAR ── --}}
  <div class="el-topbar">
    <div>
      <div class="el-topbar-title">Email History & Logs</div>
      <div style="font-size:11px; color:#6b7280; margin-top:2px;">
        Tenant: <span style="color:#0d9488; font-weight:500;">{{ $cn }}</span>
        @if($isLive)
          <span class="el-pill live" style="margin-left:8px;">
            <span style="width:6px; height:6px; border-radius:50%; background:#22c55e; display:inline-block;"></span>Live
          </span>
        @else
          <span class="el-pill demo" style="margin-left:8px;">
            <span style="width:6px; height:6px; border-radius:50%; background:#f59e0b; display:inline-block;"></span>Demo Data
          </span>
        @endif
      </div>
    </div>
    <div style="display:flex; align-items:center; gap:16px; font-size:11px; color:#6b7280;">
      <a href="{{ route('client.dashboard') }}"
         style="display:flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; color:#9ca3af; text-decoration:none;"
         onmouseover="this.style.background='#f3f4f6'; this.style.color='#374151';"
         onmouseout="this.style.background='transparent'; this.style.color='#9ca3af';"
         title="Home">
        <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </a>
      <span style="display:flex; align-items:center; gap:4px;">
        <svg style="width:14px; height:14px; color:#a78bfa;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span style="font-weight:600; color:#374151;">{{ number_format($stats['total'] ?? 0) }} logs</span>
      </span>
      <span style="display:flex; align-items:center; gap:4px;">
        <svg style="width:14px; height:14px; color:#f472b6;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span style="font-weight:600; color:#374151;">{{ $stats['open_rate'] ?? 0 }}% open rate</span>
      </span>
      <div style="position:relative;" data-dd-wrap>
        <button onclick="xpDd('ddEl')"
                style="width:32px; height:32px; border-radius:50%; background:#06b6d4; display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; font-weight:700; border:none; cursor:pointer;">
          {{ $av }}
        </button>
        <div id="ddEl" data-dd-menu
             style="position:absolute; right:0; top:42px; width:192px; background:#fff; border-radius:8px; box-shadow:0 10px 15px -3px rgba(0,0,0,.1); border:1px solid #e5e7eb; padding:4px 0; z-index:50; display:none;">
          <div style="padding:8px 16px; border-bottom:1px solid #f3f4f6;">
            <p style="font-size:12px; font-weight:600; color:#1f2937; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $cn }}</p>
            <p style="font-size:10px; color:#9ca3af; margin:2px 0 0;">Client Account</p>
          </div>
          <a href="{{ route('client.dashboard') }}"
             style="display:flex; align-items:center; gap:8px; padding:8px 16px; font-size:12px; color:#374151; text-decoration:none;"
             onmouseover="this.style.background='#f9fafb';"
             onmouseout="this.style.background='transparent';">
            <svg style="width:14px; height:14px; color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Profile Settings
          </a>
          <hr style="margin:4px 0; border:none; border-top:1px solid #f3f4f6;">
          <form method="POST" action="{{ route('client.logout') }}" style="margin:0;">
            @csrf
            <button type="submit"
                    style="width:100%; display:flex; align-items:center; gap:8px; padding:8px 16px; font-size:12px; color:#dc2626; background:none; border:none; cursor:pointer; text-align:left;"
                    onmouseover="this.style.background='#fef2f2';"
                    onmouseout="this.style.background='transparent';">
              <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              Log Out
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- ── SCROLLABLE BODY ── --}}
  <div class="el-main-scroll">

    @if(session('success'))
      <div style="max-width:1200px; margin:0 auto 16px; padding: 12px 16px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; color: #166534; font-size: 13px;">
        ✓ {{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div style="max-width:1200px; margin:0 auto 16px; padding: 12px 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #dc2626; font-size: 13px;">
        ✗ {{ session('error') }}
      </div>
    @endif

    {{-- Stats Cards --}}
    <div class="el-stats-grid">
      <div class="el-stat-card">
        <div class="el-stat-val" style="color:#0EA5E9;">{{ number_format($stats['total_sent'] ?? 0) }}</div>
        <div class="el-stat-lbl">Total Sent</div>
        <div class="el-stat-delta up">+{{ $stats['today_count'] ?? 0 }} today</div>
      </div>
      <div class="el-stat-card">
        <div class="el-stat-val" style="color:#10B981;">{{ $stats['open_rate'] ?? 0 }}%</div>
        <div class="el-stat-lbl">Open Rate</div>
        <div class="el-stat-delta up">Engagement metric</div>
      </div>
      <div class="el-stat-card">
        <div class="el-stat-val" style="color:#A855F7;">{{ number_format($stats['total_bulk'] ?? 0) }}</div>
        <div class="el-stat-lbl">Bulk Campaigns</div>
        <div class="el-stat-delta up">Multi-recipient</div>
      </div>
      <div class="el-stat-card">
        <div class="el-stat-val" style="color:#F43F5E;">{{ number_format($stats['total_failed'] ?? 0) }}</div>
        <div class="el-stat-lbl">Failed</div>
        <div class="el-stat-delta down">Needs attention</div>
      </div>
    </div>

    {{-- Filters --}}
    <div class="el-filters">
      <form method="GET" action="{{ route('client.email.logs') }}" style="display:contents;">
        <div class="el-filter-group">
          <span class="el-filter-label">Search</span>
          <input type="text" name="search" value="{{ request('search') }}" class="el-filter-input" placeholder="Email or subject..." style="width:180px;">
        </div>
        <div class="el-filter-group">
          <span class="el-filter-label">Status</span>
          <select name="status" class="el-filter-select">
            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            <option value="queued" {{ request('status') == 'queued' ? 'selected' : '' }}>Queued</option>
            <option value="bounced" {{ request('status') == 'bounced' ? 'selected' : '' }}>Bounced</option>
          </select>
        </div>
        <div class="el-filter-group">
          <span class="el-filter-label">Type</span>
          <select name="type" class="el-filter-select">
            <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Types</option>
            <option value="single" {{ request('type') == 'single' ? 'selected' : '' }}>Single</option>
            <option value="bulk" {{ request('type') == 'bulk' ? 'selected' : '' }}>Bulk</option>
          </select>
        </div>
        <div class="el-filter-group">
          <span class="el-filter-label">From</span>
          <input type="date" name="from" value="{{ request('from') }}" class="el-filter-input">
        </div>
        <div class="el-filter-group">
          <span class="el-filter-label">To</span>
          <input type="date" name="to" value="{{ request('to') }}" class="el-filter-input">
        </div>
        <div class="el-filter-group" style="margin-left:auto;">
          <button type="submit" class="el-btn el-btn-primary">Apply Filters</button>
          <a href="{{ route('client.email.logs') }}" class="el-btn el-btn-secondary">Reset</a>
        </div>
      </form>
    </div>

    {{-- Table --}}
    <div class="el-card">
      @if($logs->count() > 0)
        <table class="el-table">
          <thead>
            <tr>
              <th>Recipient</th>
              <th>Subject</th>
              <th>Type</th>
              <th>Status</th>
              <th>Template</th>
              <th>Sent</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($logs as $log)
              <tr>
                <td>
                  <div class="el-subject">{{ $log->recipient_name ?? 'Unknown' }}</div>
                  <div class="el-email">{{ $log->email_address }}</div>
                </td>
                <td>
                  <div class="el-subject" title="{{ $log->subject }}">{{ $log->subject ?? 'No subject' }}</div>
                  <div class="el-preview" title="{{ strip_tags($log->body ?? '') }}">{{ Str::limit(strip_tags($log->body ?? ''), 60) }}</div>
                </td>
                <td>
                  <span class="el-badge" style="background:{{ $log->type_bg_color }}; color:{{ $log->type_color }};">
                    @if($log->type == 'bulk')
                      <svg style="width:10px; height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                      {{ $log->bulk_count }} recipients
                    @else
                      <svg style="width:10px; height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      Single
                    @endif
                  </span>
                </td>
                <td>
                  <span class="el-badge" style="background:{{ $log->status_bg_color }}; color:{{ $log->status_color }};">
                    <span style="width:6px; height:6px; border-radius:50%; background:currentColor; display:inline-block;"></span>
                    {{ ucfirst($log->status) }}
                  </span>
                  @if($log->error_message)
                    <div style="font-size:10px; color:#F43F5E; margin-top:2px;" title="{{ $log->error_message }}">
                      {{ Str::limit($log->error_message, 30) }}
                    </div>
                  @endif
                </td>
                <td>
                  @if($log->template)
                    <span class="el-badge" style="background:#f3f4f6; color:#374151;">
                      {{ $log->template->name }}
                    </span>
                  @else
                    <span style="font-size:11px; color:#9ca3af;">No template</span>
                  @endif
                </td>
                <td>
                  <div class="el-date">{{ $log->sent_at ? $log->sent_at->format('M d, Y H:i') : $log->created_at->format('M d, Y H:i') }}</div>
                  @if($log->opened_at)
                    <div style="font-size:10px; color:#10B981; margin-top:2px;">
                      <svg style="width:10px; height:10px; display:inline;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      Opened {{ $log->opened_at->diffForHumans() }}
                    </div>
                  @endif
                  @if($log->device_type)
                    <div style="font-size:10px; color:#6b7280; margin-top:2px;">{{ ucfirst($log->device_type) }}</div>
                  @endif
                </td>
                <td>
                  <div style="display:flex; gap:6px;">
                    <button class="el-btn el-btn-secondary" onclick="viewLog({{ $log->id }})" style="padding:4px 10px; font-size:11px;">
                      <svg style="width:12px; height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      View
                    </button>
                    @if($log->status == 'failed')
                      <form method="POST" action="{{ route('client.email.logs.retry', $log) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="el-btn el-btn-primary" style="padding:4px 10px; font-size:11px;">
                          <svg style="width:12px; height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                          Retry
                        </button>
                      </form>
                    @endif
                    <form method="POST" action="{{ route('client.email.logs.delete', $log) }}" style="display:inline;" onsubmit="return confirm('Delete this log?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="el-btn el-btn-danger" style="padding:4px 10px; font-size:11px;">
                        <svg style="width:12px; height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <div class="el-pagination">
          {{ $logs->links() }}
        </div>
      @else
        <div class="el-empty">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          <p style="font-size:14px; font-weight:600; color:#374151; margin-bottom:4px;">No email logs yet</p>
          <p style="font-size:12px; margin-bottom:16px;">Send emails from the AI/ML Predictions page to see logs here</p>
          <a href="{{ route('client.layer.l5') }}" class="el-btn el-btn-primary">Go to AI/ML Predictions</a>
        </div>
      @endif
    </div>

    <div style="max-width:1200px; margin:16px auto 0; text-align:center;">
      <a href="{{ route('client.email.templates') }}" class="el-btn el-btn-secondary">← Back to Templates</a>
    </div>

  </div>
</div>

<!-- View Log Modal -->
<div id="logModal" class="el-modal">
  <div class="el-modal-box">
    <div class="el-modal-hd">
      <h3>Email Log Details</h3>
      <button onclick="closeLogModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:18px;">✕</button>
    </div>
    <div class="el-modal-body" id="logModalBody">Loading...</div>
    <div class="el-modal-ft">
      <button onclick="closeLogModal()" class="el-btn el-btn-secondary">Close</button>
    </div>
  </div>
</div>

<script>
function xpDd(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('[data-dd-wrap]')) {
    document.querySelectorAll('[data-dd-menu]').forEach(m => m.style.display = 'none');
  }
});

function viewLog(id) {
  const modal = document.getElementById('logModal');
  const body = document.getElementById('logModalBody');
  fetch('/app/email-logs/' + id + '/detail')
    .then(r => r.text())
    .then(html => { body.innerHTML = html; modal.classList.add('active'); })
    .catch(err => { body.innerHTML = '<p style="color:#F43F5E;">Error loading details</p>'; modal.classList.add('active'); });
}

function closeLogModal() {
  document.getElementById('logModal').classList.remove('active');
}

document.getElementById('logModal').addEventListener('click', function(e) {
  if (e.target === this) closeLogModal();
});
</script>

@endsection