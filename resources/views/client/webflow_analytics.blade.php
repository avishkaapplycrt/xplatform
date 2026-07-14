@extends('layouts.platform')
@section('title', 'Webflow Analytics')

@push('styles')
<style>
  .analytics-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
    padding: 20px;
    transition: all .2s ease;
  }
  .analytics-card:hover {
    border-color: #4353ff;
    box-shadow: 0 4px 16px #4353ff14;
  }
  .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    line-height: 1.2;
  }
  .stat-label {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
  }
  .chart-bar {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    height: 160px;
    padding: 16px 0;
  }
  .chart-bar-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
  }
  .chart-bar-fill {
    width: 100%;
    border-radius: 6px 6px 0 0;
    background: #4353ff;
    transition: height .5s ease;
    min-height: 4px;
  }
  .chart-bar-label {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 500;
  }
  .chart-bar-value {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
  }
  .event-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    transition: background .15s;
  }
  .event-row:hover {
    background: #f9fafb;
  }
  .event-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .event-icon.page_view { background: #dbeafe; color: #2563eb; }
  .event-icon.click { background: #fef3c7; color: #d97706; }
  .event-icon.scroll_depth { background: #dcfce7; color: #16a34a; }
  .event-icon.form_submit { background: #fce7f3; color: #db2777; }
  .event-icon.time_on_page { background: #e0e7ff; color: #6366f1; }
  .event-icon.default { background: #f3f4f6; color: #6b7280; }
  .page-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border-radius: 8px;
    transition: background .15s;
  }
  .page-row:hover {
    background: #f9fafb;
  }
  .page-url {
    font-size: 12px;
    color: #374151;
    font-weight: 500;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .page-views {
    font-size: 12px;
    font-weight: 600;
    color: #111827;
    background: #f3f4f6;
    padding: 2px 10px;
    border-radius: 6px;
  }
  .progress-track {
    height: 8px;
    background: #f3f4f6;
    border-radius: 999px;
    overflow: hidden;
  }
  .progress-fill {
    height: 8px;
    border-radius: 999px;
    background: #4353ff;
    transition: width .5s ease;
  }
  .back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 8px;
    transition: all .15s;
  }
  .back-link:hover {
    background: #f3f4f6;
    color: #374151;
  }
  .badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
  }
  .badge.active {
    background: #dcfce7;
    color: #16a34a;
  }
  .badge.inactive {
    background: #fef2f2;
    color: #dc2626;
  }
  .empty-state {
    text-align: center;
    padding: 48px 24px;
    color: #9ca3af;
  }
  .empty-state svg {
    width: 48px;
    height: 48px;
    margin-bottom: 12px;
    opacity: .5;
  }
  .empty-state-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
  }
  .empty-state-text {
    font-size: 12px;
    color: #9ca3af;
  }
  .table-header {
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding: 8px 12px;
  }
  .refresh-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    cursor: pointer;
    transition: all .15s;
  }
  .refresh-btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
  }
  .refresh-btn.spinning svg {
    animation: spin 1s linear infinite;
  }
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
  .webflow-blue { color: #4353ff; }
  .webflow-bg { background: #4353ff; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

<header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
  <div class="flex items-center gap-3">
    <a href="{{ route('client.website-connections') }}" class="back-link">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
      </svg>
      Back to Connections
    </a>
    <div class="w-px h-5 bg-gray-200"></div>
    <div class="flex items-center gap-2">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <rect x="3" y="3" width="18" height="18" rx="4" fill="#4353ff"/>
        <path d="M8 8h3v8H8V8zm5 0h3v8h-3V8z" fill="#fff" opacity="0.3"/>
        <circle cx="12" cy="12" r="3" fill="#fff"/>
      </svg>
      <div>
        <h1 class="text-[16px] font-semibold text-gray-900">Webflow Analytics</h1>
        <p class="text-[11px] text-gray-500 mt-0.5">
          {{ $connection->site_name ?? $connection->site_url }}
          <span class="ml-2 badge {{ $connection->status === 'active' ? 'active' : 'inactive' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span>
            {{ ucfirst($connection->status) }}
          </span>
        </p>
      </div>
    </div>
  </div>
  <div class="flex items-center gap-3">
    <button onclick="refreshData()" id="refreshBtn" class="refresh-btn">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
      </svg>
      Refresh
    </button>
    <a href="{{ route('client.dashboard') }}"
       class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
       title="Home">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
    </a>
    <div style="position:relative" id="l1AvatarWrap">
      <button onclick="var d=document.getElementById('l1Dropdown');d.style.display=d.style.display==='block'?'none':'block'"
              style="width:32px;height:32px;border-radius:50%;background:#06b6d4;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;border:none;cursor:pointer">
        {{ $av }}
      </button>
      <div id="l1Dropdown"
           style="display:none;position:absolute;right:0;top:40px;width:192px;background:#fff;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.12);border:1px solid #e5e7eb;padding:4px 0;z-index:999">
        <div style="padding:8px 16px;border-bottom:1px solid #f3f4f6">
          <p style="font-size:12px;font-weight:600;color:#111827;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cn }}</p>
          <p style="font-size:10px;color:#9ca3af;margin:2px 0 0">Client Account</p>
        </div>
        <a href="{{ route('client.dashboard') }}"
           style="display:flex;align-items:center;gap:8px;padding:8px 16px;font-size:12px;color:#374151;text-decoration:none"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
          <svg width="14" height="14" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          Profile Settings
        </a>
        <hr style="margin:4px 0;border:none;border-top:1px solid #f3f4f6">
        <form method="POST" action="{{ route('client.logout') }}">
          @csrf
          <button type="submit"
                  style="width:100%;display:flex;align-items:center;gap:8px;padding:8px 16px;font-size:12px;color:#dc2626;background:none;border:none;cursor:pointer;text-align:left"
                  onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
            <svg width="14" height="14" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Log Out
          </button>
        </form>
      </div>
    </div>
  </div>
</header>

<div class="flex-1 overflow-y-auto px-5 py-4">

  <!-- KPI Cards -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="analytics-card">
      <div class="flex items-center justify-between mb-2">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        <span class="text-[10px] font-semibold text-gray-400">Today</span>
      </div>
      <div class="stat-value">{{ number_format($eventsToday) }}</div>
      <div class="stat-label">Events Today</div>
    </div>

    <div class="analytics-card">
      <div class="flex items-center justify-between mb-2">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <span class="text-[10px] font-semibold text-gray-400">All Time</span>
      </div>
      <div class="stat-value">{{ number_format($totalPageViews) }}</div>
      <div class="stat-label">Page Views</div>
    </div>

    <div class="analytics-card">
      <div class="flex items-center justify-between mb-2">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
        </svg>
        <span class="text-[10px] font-semibold text-gray-400">All Time</span>
      </div>
      <div class="stat-value">{{ number_format($totalClicks) }}</div>
      <div class="stat-label">Clicks</div>
    </div>

    <div class="analytics-card">
      <div class="flex items-center justify-between mb-2">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <span class="text-[10px] font-semibold text-gray-400">Unique</span>
      </div>
      <div class="stat-value">{{ number_format($uniqueVisitors) }}</div>
      <div class="stat-label">Unique Visitors</div>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <!-- 7-Day Trend -->
    <div class="analytics-card lg:col-span-2">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-gray-900">Events Trend (Last 7 Days)</h3>
        <span class="text-[11px] text-gray-400">Daily events</span>
      </div>
      @if($last7Days->sum('count') > 0)
      @php
        $maxCount = max($last7Days->max('count'), 1);
      @endphp
      <div class="chart-bar">
        @foreach($last7Days as $day)
        <div class="chart-bar-item">
          <div class="chart-bar-value">{{ $day['count'] }}</div>
          <div class="chart-bar-fill" style="height: {{ ($day['count'] / $maxCount) * 140 }}px"></div>
          <div class="chart-bar-label">{{ $day['date'] }}</div>
        </div>
        @endforeach
      </div>
      @else
      <div class="empty-state">
        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
        </svg>
        <div class="empty-state-title">No data yet</div>
        <div class="empty-state-text">Events will appear once visitors interact with your Webflow site.</div>
      </div>
      @endif
    </div>

    <!-- Events by Type -->
    <div class="analytics-card">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-gray-900">Events by Type</h3>
      </div>
      @if($eventsByType->count() > 0)
      @php
        $totalTypeEvents = $eventsByType->sum('count');
        $colors = ['#4353ff', '#22c55e', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4'];
      @endphp
      <div class="space-y-3">
        @foreach($eventsByType as $index => $type)
        @php
          $percentage = $totalTypeEvents > 0 ? round(($type->count / $totalTypeEvents) * 100) : 0;
        @endphp
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-[12px] font-medium text-gray-700 capitalize">{{ str_replace('_', ' ', $type->event_type) }}</span>
            <span class="text-[11px] font-semibold text-gray-500">{{ $type->count }} ({{ $percentage }}%)</span>
          </div>
          <div class="progress-track">
            <div class="progress-fill" style="width: {{ $percentage }}%; background: {{ $colors[$index % count($colors)] }}"></div>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="empty-state" style="padding: 32px 16px;">
        <div class="empty-state-title">No events recorded</div>
        <div class="empty-state-text">Install tracking code to start collecting data.</div>
      </div>
      @endif
    </div>
  </div>

  <!-- Top Pages & Recent Events -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <!-- Top Pages -->
    <div class="analytics-card">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-gray-900">Top Pages</h3>
        <span class="text-[11px] text-gray-400">By page views</span>
      </div>
      @if($topPages->count() > 0)
      <div class="space-y-1">
        <div class="table-header flex">
          <span class="flex-1">Page URL</span>
          <span class="w-16 text-right">Views</span>
        </div>
        @foreach($topPages as $page)
        <div class="page-row">
          <span class="page-url" title="{{ $page->page_url }}">{{ $page->page_url }}</span>
          <span class="page-views">{{ $page->views }}</span>
        </div>
        @endforeach
      </div>
      @else
      <div class="empty-state" style="padding: 32px 16px;">
        <div class="empty-state-title">No page views yet</div>
        <div class="empty-state-text">Visitors haven't loaded any tracked pages.</div>
      </div>
      @endif
    </div>

    <!-- Recent Events -->
    <div class="analytics-card">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-gray-900">Recent Events</h3>
        <span class="text-[11px] text-gray-400">Last 50</span>
      </div>
      @if($recentEvents->count() > 0)
      <div class="space-y-1 max-h-[320px] overflow-y-auto pr-1">
        @foreach($recentEvents as $event)
        @php
          $eventData = is_array($event->data) ? $event->data : json_decode($event->data, true);
          $iconClass = $event->event_type;
          $iconMap = [
            'page_view' => '<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
            'click' => '<path d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>',
            'scroll_depth' => '<path d="M19 14l-7 7m0 0l-7-7m7 7V3"/>',
            'form_submit' => '<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
            'time_on_page' => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
          ];
          $svgPath = $iconMap[$event->event_type] ?? '<circle cx="12" cy="12" r="10"/>';
        @endphp
        <div class="event-row">
          <div class="event-icon {{ $iconClass }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              {!! $svgPath !!}
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[12px] font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $event->event_type) }}</p>
            <p class="text-[11px] text-gray-400 truncate">
              @if($event->page_url)
                {{ Str::limit($event->page_url, 50) }}
              @else
                No page data
              @endif
            </p>
          </div>
          <span class="text-[11px] text-gray-400 whitespace-nowrap">{{ $event->created_at->diffForHumans() }}</span>
        </div>
        @endforeach
      </div>
      @else
      <div class="empty-state" style="padding: 32px 16px;">
        <div class="empty-state-title">No events yet</div>
        <div class="empty-state-text">Events will appear here in real-time.</div>
      </div>
      @endif
    </div>
  </div>

  <!-- Scroll Depth & Engagement -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <!-- Scroll Depth -->
    <div class="analytics-card">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-gray-900">Scroll Depth</h3>
        <span class="text-[11px] text-gray-400">How far visitors scroll</span>
      </div>
      @if($scrollDepths->count() > 0)
      <div class="space-y-3">
        @foreach($scrollDepths as $depth => $count)
        @php
          $maxDepthCount = $scrollDepths->max();
          $percentage = $maxDepthCount > 0 ? round(($count / $maxDepthCount) * 100) : 0;
        @endphp
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-[12px] font-medium text-gray-700">{{ $depth }}%</span>
            <span class="text-[11px] font-semibold text-gray-500">{{ $count }} events</span>
          </div>
          <div class="progress-track">
            <div class="progress-fill" style="width: {{ $percentage }}%"></div>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="empty-state" style="padding: 32px 16px;">
        <div class="empty-state-title">No scroll data</div>
        <div class="empty-state-text">Visitors haven't scrolled enough pages yet.</div>
      </div>
      @endif
    </div>

    <!-- Engagement Metrics -->
    <div class="analytics-card">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-gray-900">Engagement</h3>
        <span class="text-[11px] text-gray-400">Average metrics</span>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div class="text-center p-4 rounded-xl border border-gray-200 bg-gray-50">
          <p class="text-[24px] font-bold text-gray-900">{{ round($avgTimeOnPage) }}s</p>
          <p class="text-[11px] text-gray-500 mt-1">Avg. Time on Page</p>
        </div>
        <div class="text-center p-4 rounded-xl border border-gray-200 bg-gray-50">
          <p class="text-[24px] font-bold text-gray-900">{{ number_format($totalForms) }}</p>
          <p class="text-[11px] text-gray-500 mt-1">Form Submissions</p>
        </div>
        <div class="text-center p-4 rounded-xl border border-gray-200 bg-gray-50">
          <p class="text-[24px] font-bold text-gray-900">{{ number_format($totalEvents) }}</p>
          <p class="text-[11px] text-gray-500 mt-1">Total Events</p>
        </div>
        <div class="text-center p-4 rounded-xl border border-gray-200 bg-gray-50">
          <p class="text-[24px] font-bold text-gray-900">{{ $connection->last_sync_at ? $connection->last_sync_at->diffForHumans() : 'Never' }}</p>
          <p class="text-[11px] text-gray-500 mt-1">Last Sync</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Connection Info -->
  <div class="analytics-card">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-[14px] font-bold text-gray-900">Connection Details</h3>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-[12px]">
      <div>
        <p class="text-gray-400 mb-1">Platform</p>
        <p class="font-semibold text-gray-800 capitalize">{{ $connection->platform }}</p>
      </div>
      <div>
        <p class="text-gray-400 mb-1">Site URL</p>
        <p class="font-semibold text-gray-800 truncate">{{ $connection->site_url }}</p>
      </div>
      <div>
        <p class="text-gray-400 mb-1">Tracking Code</p>
        <p class="font-semibold text-gray-800 font-mono text-[11px]">{{ $connection->tracking_code }}</p>
      </div>
      <div>
        <p class="text-gray-400 mb-1">Connected At</p>
        <p class="font-semibold text-gray-800">{{ $connection->connected_at ? $connection->connected_at->format('M d, Y H:i') : 'N/A' }}</p>
      </div>
    </div>
  </div>

</div>
</div>
@endsection

@push('scripts')
<script>
function refreshData() {
  const btn = document.getElementById('refreshBtn');
  btn.classList.add('spinning');
  btn.disabled = true;

  setTimeout(() => {
    window.location.reload();
  }, 800);
}

document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush
