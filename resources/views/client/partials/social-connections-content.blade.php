<style>
  .platform-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
    padding: 24px;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }
  .platform-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transform: translateY(-1px);
  }
  .platform-card.connected {
    border-color: #10b981;
    background: #f0fdf4;
  }
  .platform-card.connected::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #10b981;
  }
  .platform-card.error {
    border-color: #ef4444;
    background: #fef2f2;
  }
  .platform-card.error::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #ef4444;
  }
  .platform-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
  }
  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
  }
  .status-badge.connected {
    background: #dcfce7;
    color: #166534;
  }
  .status-badge.disconnected {
    background: #f3f4f6;
    color: #6b7280;
  }
  .status-badge.error {
    background: #fee2e2;
    color: #991b1b;
  }
  .status-badge.pending {
    background: #fef3c7;
    color: #92400e;
  }
  .feature-tag {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 500;
    background: #f3f4f6;
    color: #6b7280;
    margin-right: 6px;
    margin-bottom: 6px;
  }
  .connect-btn {
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
  }
  .connect-btn.primary {
    background: #111827;
    color: #fff;
  }
  .connect-btn.primary:hover {
    background: #374151;
  }
  .connect-btn.success {
    background: #10b981;
    color: #fff;
  }
  .connect-btn.success:hover {
    background: #059669;
  }
  .connect-btn.danger {
    background: #fef2f2;
    color: #ef4444;
    border: 1px solid #fecaca;
  }
  .connect-btn.danger:hover {
    background: #fee2e2;
  }
  .health-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
  }
  .health-dot.healthy { background: #10b981; }
  .health-dot.warning { background: #f59e0b; }
  .health-dot.critical { background: #ef4444; }
  .sync-meta {
    font-size: 11px;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 4px;
  }
  .kpi-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    padding: 20px;
  }
  .kpi-value {
    font-size: 28px;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.5px;
  }
  .kpi-label {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 500;
    margin-top: 8px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .metric-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    font-size: 12px;
    font-weight: 500;
    color: #374151;
  }
  .metric-pill .metric-value {
    font-weight: 700;
    color: #111827;
  }
  .toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 14px 20px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    color: #fff;
    z-index: 9999;
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s ease;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  }
  .toast.show {
    transform: translateY(0);
    opacity: 1;
  }
  .toast.success { background: #10b981; }
  .toast.error { background: #ef4444; }
  .toast.info { background: #3b82f6; }
</style>

{{-- KPI CARDS --}}
<div class="grid grid-cols-4 gap-4">
  <div class="kpi-card">
    <div class="flex items-center gap-2 mb-2">
      <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
      </div>
      <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Active Platforms</span>
    </div>
    <p class="kpi-value text-blue-500">{{ $totalConnected }}<span class="text-[14px] font-medium text-gray-400">/{{ $totalPlatforms }}</span></p>
    <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
      <span class="text-emerald-500 text-xs">▲</span> Social platforms available
    </p>
  </div>
  <div class="kpi-card">
    <div class="flex items-center gap-2 mb-2">
      <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
      </div>
      <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Syncs Today</span>
    </div>
    <p class="kpi-value text-emerald-500">{{ $syncToday }}</p>
    <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
      <span class="text-emerald-500 text-xs">▲</span> Successful sync operations
    </p>
  </div>
  <div class="kpi-card">
    <div class="flex items-center gap-2 mb-2">
      <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
        <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Sync Health</span>
    </div>
    <p class="kpi-value" style="color: {{ $syncHealth === 'healthy' ? '#10b981' : ($syncHealth === 'warning' ? '#f59e0b' : '#ef4444') }}">{{ ucfirst($syncHealth) }}</p>
    <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
      <span class="health-dot {{ $syncHealth }}"></span>
      {{ $syncHealth === 'healthy' ? 'All systems operational' : ($syncHealth === 'warning' ? 'Some tokens expired' : 'Connection issues detected') }}
    </p>
  </div>
  <div class="kpi-card">
    <div class="flex items-center gap-2 mb-2">
      <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center">
        <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
      </div>
      <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Total Followers</span>
    </div>
    <p class="kpi-value text-pink-500">{{ number_format($totalFollowers) }}</p>
    <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
      <span class="text-emerald-500 text-xs">▲</span> Across all connected platforms
    </p>
  </div>
</div>

{{-- PLATFORM CARDS GRID --}}
<div class="mt-4">
  <div class="flex items-center gap-2 mb-4">
    <div class="w-2 h-2 rounded-full bg-gray-800"></div>
    <h2 class="text-[13px] font-semibold text-gray-800">Available Social Platforms</h2>
    <div class="flex-1 h-px bg-gray-200 ml-2"></div>
  </div>

  <div class="grid grid-cols-3 gap-4">
    @foreach($platforms as $key => $meta)
    @php
      $conn = $connections[$key] ?? null;
      $isConnected = $conn && $conn->is_connected;
      $hasError = $conn && $conn->status === 'error';
      $cardClass = $isConnected ? 'connected' : ($hasError ? 'error' : '');
    @endphp
    <div class="platform-card {{ $cardClass }}" onclick="openPlatform('{{ $key }}')">
      {{-- Top row: Icon + Status --}}
      <div class="flex items-start justify-between mb-4">
        <div class="platform-icon" style="background: {{ $meta['color'] }}">
          @switch($key)
            @case('facebook')
              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
              @break
            @case('instagram')
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/>
                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
              </svg>
              @break
            @case('tiktok')
              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
              </svg>
              @break
            @case('youtube')
              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
              </svg>
              @break
            @case('linkedin')
              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
              </svg>
              @break
          @endswitch
        </div>
        <div class="status-badge {{ $conn?->status ?? 'disconnected' }}">
          @if($isConnected)
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
            Connected
          @elseif($hasError)
            <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>
            Error
          @else
            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span>
            Disconnected
          @endif
        </div>
      </div>

      {{-- Platform Name + Description --}}
      <h3 class="text-[15px] font-bold text-gray-900 mb-1">{{ $meta['name'] }}</h3>
      <p class="text-[12px] text-gray-500 leading-relaxed mb-4" style="min-height: 40px;">{{ $meta['description'] }}</p>

      {{-- Features --}}
      <div class="flex flex-wrap mb-4">
        @foreach($meta['features'] as $feature)
          <span class="feature-tag">{{ $feature }}</span>
        @endforeach
      </div>

      {{-- Connection Info + Metrics (if connected) --}}
      @if($conn)
      <div class="mb-4 p-3 rounded-lg bg-white/60 border border-gray-200/50">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[11px] text-gray-500 font-medium">{{ $conn->connection_name }}</span>
          <span class="text-[10px] text-gray-400">{{ $conn->sync_count }} syncs</span>
        </div>
        <div class="flex flex-wrap gap-2">
          <span class="metric-pill">
            <svg class="w-3 h-3 text-pink-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="metric-value">{{ $conn->formatted_followers }}</span> followers
          </span>
          <span class="metric-pill">
            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            <span class="metric-value">{{ $conn->engagement_rate }}</span> engagement
          </span>
        </div>
        <div class="sync-meta mt-2">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Last sync: {{ $conn->last_sync_at?->diffForHumans() ?? 'Never' }}
        </div>
      </div>
      @endif

      {{-- Action Buttons --}}
      <div class="flex items-center gap-2">
        @if($isConnected)
          <button class="connect-btn success" onclick="event.stopPropagation(); syncPlatform('{{ $key }}')">
            <span class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Sync Now
            </span>
          </button>
          <button class="connect-btn danger" onclick="event.stopPropagation(); disconnectPlatform('{{ $key }}')">
            Disconnect
          </button>
        @else
          <button class="connect-btn primary" onclick="event.stopPropagation(); openPlatform('{{ $key }}')">
            <span class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
              </svg>
              {{ $conn ? 'Reconnect' : 'Connect' }}
            </span>
          </button>
        @endif
      </div>
    </div>
    @endforeach
  </div>
</div>

{{-- Connection Guide --}}
<div class="mt-4 bg-gray-50 rounded-xl border border-gray-200 p-5">
  <div class="flex items-center gap-2 mb-3">
    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <h3 class="text-[13px] font-semibold text-gray-700">How to Connect Social Media</h3>
  </div>
  <div class="grid grid-cols-3 gap-4">
    <div class="flex items-start gap-3">
      <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[11px] font-bold flex-shrink-0">1</div>
      <div>
        <p class="text-[12px] font-medium text-gray-700">Select Platform</p>
        <p class="text-[11px] text-gray-500 mt-0.5">Choose your social media platform from the cards above. We support Facebook, Instagram, TikTok, YouTube, and LinkedIn.</p>
      </div>
    </div>
    <div class="flex items-start gap-3">
      <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[11px] font-bold flex-shrink-0">2</div>
      <div>
        <p class="text-[12px] font-medium text-gray-700">Authenticate</p>
        <p class="text-[11px] text-gray-500 mt-0.5">Authorize access via OAuth or enter your API credentials. Your tokens are encrypted and stored securely.</p>
      </div>
    </div>
    <div class="flex items-start gap-3">
      <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[11px] font-bold flex-shrink-0">3</div>
      <div>
        <p class="text-[12px] font-medium text-gray-700">Start Monitoring</p>
        <p class="text-[11px] text-gray-500 mt-0.5">Configure content sync, hashtag tracking, and engagement metrics. Data updates automatically.</p>
      </div>
    </div>
  </div>
</div>

{{-- Toast Notification --}}
<div id="toast" class="toast"></div>

<script>
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.className = 'toast ' + type;
  setTimeout(() => toast.classList.add('show'), 10);
  setTimeout(() => toast.classList.remove('show'), 3000);
}

function openPlatform(platform) {
  window.location.href = '{{ route("client.social.connect", ["platform" => "__PLATFORM__"]) }}'.replace('__PLATFORM__', platform);
}

function syncPlatform(platform) {
  fetch('{{ route("client.social.sync", ["platform" => "__PLATFORM__"]) }}'.replace('__PLATFORM__', platform), {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => location.reload(), 1500);
    } else {
      showToast(data.message, 'error');
    }
  })
  .catch(err => showToast('Sync failed: ' + err.message, 'error'));
}

function disconnectPlatform(platform) {
  if (!confirm('Are you sure you want to disconnect ' + platform + '?')) return;

  fetch('{{ route("client.social.disconnect", ["platform" => "__PLATFORM__"]) }}'.replace('__PLATFORM__', platform), {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast(data.message, 'error');
    }
  })
  .catch(err => showToast('Disconnect failed: ' + err.message, 'error'));
}
</script>
