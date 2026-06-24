@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $cn), 0, 2))));
  
  // Helper for EdTech data
  $getInitials = function($name) {
      $parts = explode(' ', $name);
      return strtoupper(substr($parts[0] ?? 'S', 0, 1) . substr($parts[1] ?? '', 0, 1));
  };
  $getSegmentColor = function($segment) {
      return match($segment) {
          'champion' => '#FFD700',
          'loyal' => '#A855F7',
          'at_risk' => '#F43F5E',
          'new' => '#0EA5E9',
          'dormant' => '#10B981',
          default => '#0EA5E9',
      };
  };
  $getSegmentLabel = function($segment) {
      return match($segment) {
          'champion' => 'Champion',
          'loyal' => 'Loyal',
          'at_risk' => 'At Risk',
          'new' => 'New',
          'dormant' => 'Dormant',
          default => 'New',
      };
  };
@endphp
@extends('layouts.platform')
@section('title', 'Behavioral Analytics Dashboard')

@push('styles')
<style>
/* ── Analytics Dashboard ─────────────────────────────────────────────────── */
.analytics-wrap {
    font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    background: #f8fafc;
    min-height: calc(100vh - 57px);
    height: auto;
    overflow-y: auto;
    padding-bottom: 40px;  /* Add breathing room at bottom */
}
.analytics-wrap *{box-sizing:border-box}

/* Header stats row */
.stats-row{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;padding:24px 28px 0}
.stat-card{background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,.04);transition:all .2s}
.stat-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.08);transform:translateY(-2px)}
.stat-label{font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px}
.stat-value{font-size:28px;font-weight:800;color:#1e293b;line-height:1}
.stat-change{font-size:12px;font-weight:600;margin-top:6px;display:flex;align-items:center;gap:4px}
.stat-change.up{color:#10b981}
.stat-change.down{color:#ef4444}
.stat-change.neutral{color:#64748b}
.stat-bar{height:4px;background:#f1f5f9;border-radius:2px;margin-top:10px;overflow:hidden}
.stat-bar-fill{height:100%;border-radius:2px;transition:width 1s ease}

/* Section titles */
.section-title{font-size:14px;font-weight:700;color:#1e293b;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.section-title::before{content:'';width:4px;height:18px;border-radius:2px;background:#3b82f6}

/* Charts grid */
.charts-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;padding:24px 28px}
.chart-card{background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.chart-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.chart-title{font-size:13px;font-weight:700;color:#1e293b}
.chart-badge{padding:3px 10px;border-radius:20px;font-size:10px;font-weight:600;background:#f1f5f9;color:#64748b}
.chart-container{height:240px;position:relative}

/* Segment pills */
.segments-row{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;padding:0 28px 24px}
.segment-card{background:#fff;border-radius:10px;padding:16px;border:1px solid #e2e8f0;text-align:center;transition:all .2s;cursor:pointer}
.segment-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.08)}
.segment-icon{font-size:24px;margin-bottom:8px}
.segment-name{font-size:12px;font-weight:600;color:#475569;margin-bottom:4px}
.segment-count{font-size:22px;font-weight:800;color:#1e293b}
.segment-pct{font-size:11px;color:#94a3b8}

/* Tables */
.tables-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;padding:0 28px 24px}
.table-card{background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.table-header{font-size:13px;font-weight:700;color:#1e293b;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f1f5f9}
.table-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f8fafc}
.table-row:last-child{border-bottom:none}
.table-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}
.table-info{flex:1;min-width:0}
.table-name{font-size:12px;font-weight:600;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.table-meta{font-size:10px;color:#94a3b8}
.table-score{font-size:14px;font-weight:800;flex-shrink:0}

/* Activity bar */
.activity-bar{display:flex;gap:16px;padding:0 28px 24px}
.activity-card{flex:1;background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:16px}
.activity-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px}
.activity-info{flex:1}
.activity-label{font-size:12px;font-weight:600;color:#64748b}
.activity-value{font-size:24px;font-weight:800;color:#1e293b;margin-top:2px}
.activity-sub{font-size:11px;color:#94a3b8;margin-top:2px}

/* Revenue cards */
.revenue-row{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;padding:0 28px 28px}
.revenue-card{background:linear-gradient(135deg,#1e293b 0%,#334155 100%);border-radius:12px;padding:24px;color:#fff;position:relative;overflow:hidden}
.revenue-card::before{content:'';position:absolute;top:-50px;right:-50px;width:150px;height:150px;background:rgba(255,255,255,.03);border-radius:50%}
.revenue-label{font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:8px}
.revenue-value{font-size:32px;font-weight:800;color:#fff}
.revenue-change{font-size:13px;font-weight:600;margin-top:8px}
.revenue-change.up{color:#4ade80}
.revenue-change.down{color:#f87171}

/* Responsive */
@media (max-width:1200px){
  .stats-row{grid-template-columns:repeat(3,1fr)}
  .charts-grid{grid-template-columns:1fr}
  .tables-grid{grid-template-columns:1fr}
  .segments-row{grid-template-columns:repeat(3,1fr)}
}
@media (max-width:768px){
  .stats-row{grid-template-columns:repeat(2,1fr)}
  .segments-row{grid-template-columns:repeat(2,1fr)}
  .revenue-row{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $cn), 0, 2))));
@endphp

{{-- Header --}}
<header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
  <div>
    <h1 class="text-[16px] font-semibold text-gray-900">Behavioral Analytics Dashboard</h1>
    <p class="text-[11px] text-gray-500 mt-0.5">
      Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
      <!-- <span class="ml-2 inline-flex items-center gap-1 text-green-600 font-medium text-[10px]">
        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Live
      </span> -->
      @if($dataSourceConnected ?? false)
          <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              Live EdTech Data
          </span>
      @else
          <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
              Demo Data
          </span>
      @endif
    </p>
  </div>
  <div class="flex items-center gap-4 text-[11px] text-gray-500">
    <a href="{{ route('client.dashboard') }}" class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition" title="Home">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    </a>
    <a href="{{ route('client.layer.l4') }}" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 font-medium text-[11px] hover:bg-indigo-100 transition">
        Decision Centre →
    </a>
    <div style="position:relative" id="dcAvatarWrap">
      <button onclick="var d=document.getElementById('dcDropdown');d.style.display=d.style.display==='block'?'none':'block'" style="width:32px;height:32px;border-radius:50%;background:#06b6d4;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;border:none;cursor:pointer">{{ $av }}</button>
      <div id="dcDropdown" style="display:none;position:absolute;right:0;top:40px;width:192px;background:#fff;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.12);border:1px solid #e5e7eb;padding:4px 0;z-index:999">
        <div style="padding:8px 16px;border-bottom:1px solid #f3f4f6">
          <p style="font-size:12px;font-weight:600;color:#111827;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cn }}</p>
          <p style="font-size:10px;color:#9ca3af;margin:2px 0 0">Client Account</p>
        </div>
        <form method="POST" action="{{ route('client.logout') }}">@csrf
          <button type="submit" style="width:100%;display:flex;align-items:center;gap:8px;padding:8px 16px;font-size:12px;color:#dc2626;background:none;border:none;cursor:pointer;text-align:left" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
            <svg width="14" height="14" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Log Out
          </button>
        </form>
      </div>
    </div>
  </div>
</header>

<div class="analytics-wrap">

  {{-- Top KPI Stats --}}
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-label">Total Profiles</div>
      <div class="stat-value">{{ number_format($totalProfiles) }}</div>
      <div class="stat-change up">↑ 12% from last month</div>
      <div class="stat-bar"><div class="stat-bar-fill" style="width:75%;background:#3b82f6"></div></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Avg Intent Score</div>
      <div class="stat-value" style="color:#f59e0b">{{ $avgIntent }}</div>
      <div class="stat-change up">↑ 5% from last month</div>
      <div class="stat-bar"><div class="stat-bar-fill" style="width:{{ $avgIntent }}%;background:#f59e0b"></div></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Avg Churn Risk</div>
      <div class="stat-value" style="color:#ef4444">{{ $avgChurn }}%</div>
      <div class="stat-change down">↑ 2% from last month</div>
      <div class="stat-bar"><div class="stat-bar-fill" style="width:{{ $avgChurn }}%;background:#ef4444"></div></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Avg Loyalty</div>
      <div class="stat-value" style="color:#10b981">{{ $avgLoyalty }}</div>
      <div class="stat-change up">↑ 8% from last month</div>
      <div class="stat-bar"><div class="stat-bar-fill" style="width:{{ $avgLoyalty }}%;background:#10b981"></div></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Avg Engagement</div>
      <div class="stat-value" style="color:#8b5cf6">{{ $avgEngagement }}</div>
      <div class="stat-change neutral">→ 0% change</div>
      <div class="stat-bar"><div class="stat-bar-fill" style="width:{{ $avgEngagement }}%;background:#8b5cf6"></div></div>
    </div>
  </div>

  {{-- Segment Distribution --}}
  <div style="padding:24px 28px 0">
    <div class="section-title">Segment Distribution</div>
  </div>
  <div class="segments-row">
    @php
      $segmentColors = ['champion' => '#f59e0b', 'loyal' => '#8b5cf6', 'at_risk' => '#ef4444', 'new' => '#3b82f6', 'dormant' => '#10b981'];
      $segmentIcons = ['champion' => '🏆', 'loyal' => '⭐', 'at_risk' => '⚠️', 'new' => '✨', 'dormant' => '💤'];
      $segmentNames = ['champion' => 'Champions', 'loyal' => 'Loyal', 'at_risk' => 'At Risk', 'new' => 'New', 'dormant' => 'Dormant'];
      $totalSeg = max(1, array_sum($segments));
    @endphp
    @foreach($segments as $seg => $count)
    <div class="segment-card" style="border-top:3px solid {{ $segmentColors[$seg] }}">
      <div class="segment-icon">{{ $segmentIcons[$seg] }}</div>
      <div class="segment-name">{{ $segmentNames[$seg] }}</div>
      <div class="segment-count" style="color:{{ $segmentColors[$seg] }}">{{ $count }}</div>
      <div class="segment-pct">{{ round($count / $totalSeg * 100) }}% of total</div>
    </div>
    @endforeach
  </div>

  {{-- Charts Row --}}
  <div class="charts-grid">
    {{-- Intent Score Distribution --}}
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title">Intent Score Distribution</div>
        <div class="chart-badge">Last 30 days</div>
      </div>
      <div class="chart-container">
        <canvas id="intentChart"></canvas>
      </div>
    </div>

    {{-- Churn Risk Distribution --}}
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title">Churn Risk Distribution</div>
        <div class="chart-badge">Real-time</div>
      </div>
      <div class="chart-container">
        <canvas id="churnChart"></canvas>
      </div>
    </div>

    {{-- Trend Lines --}}
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title">Score Trends (7 Weeks)</div>
        <div class="chart-badge">Weekly avg</div>
      </div>
      <div class="chart-container">
        <canvas id="trendChart"></canvas>
      </div>
    </div>

    {{-- Segment Pie Chart --}}
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title">Segment Breakdown</div>
        <div class="chart-badge">{{ $totalProfiles }} total</div>
      </div>
      <div class="chart-container">
        <canvas id="segmentChart"></canvas>
      </div>
    </div>
  </div>

  {{-- Activity Summary --}}
  <div style="padding:0 28px 0">
    <div class="section-title">Activity Summary</div>
  </div>
  <div class="activity-bar">
    <div class="activity-card">
      <div class="activity-icon" style="background:#dcfce7;color:#16a34a">⚡</div>
      <div class="activity-info">
        <div class="activity-label">Active Last 7 Days</div>
        <div class="activity-value" style="color:#16a34a">{{ $recentActive }}</div>
        <div class="activity-sub">{{ round($recentActive / max(1, $totalProfiles) * 100) }}% of total profiles</div>
      </div>
    </div>
    <div class="activity-card">
      <div class="activity-icon" style="background:#fee2e2;color:#dc2626">😴</div>
      <div class="activity-info">
        <div class="activity-label">Inactive 30+ Days</div>
        <div class="activity-value" style="color:#dc2626">{{ $recentInactive }}</div>
        <div class="activity-sub">{{ round($recentInactive / max(1, $totalProfiles) * 100) }}% of total profiles</div>
      </div>
    </div>
    <div class="activity-card">
      <div class="activity-icon" style="background:#dbeafe;color:#2563eb">💰</div>
      <div class="activity-info">
        <div class="activity-label">Total Estimated LTV</div>
        <div class="activity-value" style="color:#2563eb">${{ number_format($totalLTV) }}</div>
        <div class="activity-sub">Across all profiles</div>
      </div>
    </div>
    <div class="activity-card">
      <div class="activity-icon" style="background:#fef3c7;color:#d97706">🚨</div>
      <div class="activity-info">
        <div class="activity-label">Revenue at Risk</div>
        <div class="activity-value" style="color:#d97706">${{ number_format($atRiskLTV) }}</div>
        <div class="activity-sub">From at-risk profiles</div>
      </div>
    </div>
  </div>

  {{-- Top Profiles Tables --}}
  <div style="padding:24px 28px 0">
    <div class="section-title">Top Profiles</div>
  </div>
  <div class="tables-grid">
    {{-- Highest Intent --}}
    <div class="table-card">
      <div class="table-header">🔥 Highest Intent</div>
      @foreach($topIntent as $profile)
      <div class="table-row">
          <div class="table-avatar" style="background:{{ $dataSourceConnected ? $getSegmentColor($profile->segment) : $profile->segmentColor() }}">
              {{ $dataSourceConnected ? $getInitials($profile->name) : $profile->initials() }}
          </div>
          <div class="table-info">
              <div class="table-name">{{ $profile->name }}</div>
              <div class="table-meta">
                  {{ $dataSourceConnected ? $getSegmentLabel($profile->segment) : $profile->segmentLabel() }} · {{ $profile->email ?? 'N/A' }}
              </div>
          </div>
          <div class="table-score" style="color:#f59e0b">{{ $profile->intent_score }}</div>
      </div>
      @endforeach
    </div>

    {{-- Highest Churn Risk --}}
    <div class="table-card">
      <div class="table-header">⚠️ Highest Churn Risk</div>
      @foreach($topChurnRisk as $profile)
      <div class="table-row">
        <div class="table-avatar" style="background:{{ $dataSourceConnected ? $getSegmentColor($profile->segment) : $profile->segmentColor() }}">
            {{ $dataSourceConnected ? $getInitials($profile->name) : $profile->initials() }}
        </div>
        <div class="table-info">
          <div class="table-name">{{ $profile->name }}</div>
          <div class="table-meta">
              {{ $dataSourceConnected ? $getSegmentLabel($profile->segment) : $profile->segmentLabel() }} · {{ $profile->email ?? 'N/A' }}
          </div>
        </div>
        <div class="table-score" style="color:#ef4444">{{ $profile->churn_score }}%</div>
      </div>
      @endforeach
    </div>

    {{-- Most Loyal --}}
    <div class="table-card">
      <div class="table-header">⭐ Most Loyal</div>
      @foreach($topLoyal as $profile)
      <div class="table-row">
        <div class="table-avatar" style="background:{{ $dataSourceConnected ? $getSegmentColor($profile->segment) : $profile->segmentColor() }}">
            {{ $dataSourceConnected ? $getInitials($profile->name) : $profile->initials() }}
        </div>
        <div class="table-info">
          <div class="table-name">{{ $profile->name }}</div>
          <div class="table-meta">
              {{ $dataSourceConnected ? $getSegmentLabel($profile->segment) : $profile->segmentLabel() }} · {{ $profile->email ?? 'N/A' }}
          </div>
        </div>
        <div class="table-score" style="color:#10b981">{{ $profile->loyalty_score }}</div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Revenue Overview --}}
  <div style="padding:24px 28px 0">
    <div class="section-title">Revenue Overview</div>
  </div>
  <div class="revenue-row">
    <div class="revenue-card">
      <div class="revenue-label">Total Estimated LTV</div>
      <div class="revenue-value">${{ number_format($totalLTV) }}</div>
      <div class="revenue-change up">↑ 15% from Q1</div>
    </div>
    <div class="revenue-card" style="background:linear-gradient(135deg,#7c2d12 0%,#c2410c 100%)">
      <div class="revenue-label">Revenue at Risk</div>
      <div class="revenue-value">${{ number_format($atRiskLTV) }}</div>
      <div class="revenue-change down">↑ {{ round($atRiskLTV / max(1, $totalLTV) * 100) }}% of total LTV</div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// ── Charts ───────────────────────────────────────────────────────────────────
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
    x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } }
  }
};

// Intent Distribution Bar Chart
new Chart(document.getElementById('intentChart'), {
  type: 'bar',
  data: {
    labels: {!! json_encode(array_keys($intentDistribution)) !!},
    datasets: [{
      label: 'Profiles',
      data: {!! json_encode(array_values($intentDistribution)) !!},
      backgroundColor: ['#3b82f6', '#60a5fa', '#93c5fd', '#f59e0b', '#fbbf24'],
      borderRadius: 6,
      borderSkipped: false
    }]
  },
  options: chartOptions
});

// Churn Risk Doughnut Chart
new Chart(document.getElementById('churnChart'), {
  type: 'doughnut',
  data: {
    labels: {!! json_encode(array_keys($churnDistribution)) !!},
    datasets: [{
      data: {!! json_encode(array_values($churnDistribution)) !!},
      backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#7f1d1d'],
      borderWidth: 0,
      hoverOffset: 4
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '65%',
    plugins: {
      legend: { position: 'right', labels: { font: { size: 11 }, color: '#475569', padding: 16 } }
    }
  }
});

// Trend Line Chart
new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels: {!! json_encode($trendLabels) !!},
    datasets: [
      { label: 'Intent', data: {!! json_encode($trendIntent) !!}, borderColor: '#f59e0b', backgroundColor: '#f59e0b20', tension: .4, fill: true, pointRadius: 3 },
      { label: 'Churn', data: {!! json_encode($trendChurn) !!}, borderColor: '#ef4444', backgroundColor: '#ef444420', tension: .4, fill: true, pointRadius: 3 },
      { label: 'Engagement', data: {!! json_encode($trendEngagement) !!}, borderColor: '#8b5cf6', backgroundColor: '#8b5cf620', tension: .4, fill: true, pointRadius: 3 }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'top', align: 'end', labels: { font: { size: 11 }, color: '#475569', usePointStyle: true, pointStyle: 'circle' } } },
    scales: {
      y: { beginAtZero: true, max: 100, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
      x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } }
    }
  }
});

// Segment Pie Chart
new Chart(document.getElementById('segmentChart'), {
  type: 'pie',
  data: {
    labels: {!! json_encode(['Champions', 'Loyal', 'At Risk', 'New', 'Dormant']) !!},
    datasets: [{
      data: {!! json_encode(array_values($segments)) !!},
      backgroundColor: ['#f59e0b', '#8b5cf6', '#ef4444', '#3b82f6', '#10b981'],
      borderWidth: 2,
      borderColor: '#fff'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'right', labels: { font: { size: 11 }, color: '#475569', padding: 16, usePointStyle: true } }
    }
  }
});

// Dropdown close on outside click
document.addEventListener('click', function(e) {
  var wrap = document.getElementById('dcAvatarWrap');
  var drop = document.getElementById('dcDropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush
