{{-- resources/views/client/crm-connections.blade.php --}}
@extends('layouts.platform')

@section('title', 'CRM Connections')

@push('styles')
<style>
  .provider-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
    padding: 24px;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }
  .provider-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transform: translateY(-1px);
  }
  .provider-card.connected {
    border-color: #10b981;
    background: #f0fdf4;
  }
  .provider-card.connected::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #10b981;
  }
  .provider-card.error {
    border-color: #ef4444;
    background: #fef2f2;
  }
  .provider-card.error::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #ef4444;
  }
  .provider-icon {
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
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

  {{-- HEADER --}}
  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">CRM Connections</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">
        Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
        <span class="ml-2 inline-flex items-center gap-1 text-green-600 font-medium text-[10px]">
          <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Live
        </span>
      </p>
    </div>
    <div class="flex items-center gap-4 text-[11px] text-gray-500">
      <a href="{{ route('client.dashboard') }}"
         class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
         title="Home">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </a>
      <span class="flex items-center gap-1.5">
        <span class="health-dot {{ $syncHealth }}"></span>
        <span class="font-medium text-gray-600">Sync {{ ucfirst($syncHealth) }}</span>
      </span>
      <span class="flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <span class="font-medium text-gray-600">{{ $syncToday }} synced today</span>
      </span>

      {{-- Avatar --}}
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

  {{-- SCROLLABLE BODY --}}
  <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-4 gap-4">
      <div class="kpi-card">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </div>
          <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Active Connections</span>
        </div>
        <p class="kpi-value text-blue-500">{{ $totalConnected }}<span class="text-[14px] font-medium text-gray-400">/{{ $totalProviders }}</span></p>
        <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
          <span class="text-emerald-500 text-xs">▲</span> CRM providers available
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
          <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Last Sync</span>
        </div>
        <p class="kpi-value text-amber-500">{{ $lastSync ? \Carbon\Carbon::parse($lastSync)->diffForHumans() : 'Never' }}</p>
        <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
          <span class="text-emerald-500 text-xs">▲</span> {{ $lastSync ? \Carbon\Carbon::parse($lastSync)->format('M d, H:i') : 'No syncs yet' }}
        </p>
      </div>
    </div>

    {{-- PROVIDER CARDS GRID --}}
    <div>
      <div class="flex items-center gap-2 mb-4">
        <div class="w-2 h-2 rounded-full bg-gray-800"></div>
        <h2 class="text-[13px] font-semibold text-gray-800">Available CRM Providers</h2>
        <div class="flex-1 h-px bg-gray-200 ml-2"></div>
      </div>

      <div class="grid grid-cols-3 gap-4">
        @foreach($providers as $key => $meta)
        @php
          $conn = $connections[$key] ?? null;
          $isConnected = $conn && $conn->is_connected;
          $hasError = $conn && $conn->status === 'error';
          $isDisconnected = !$conn || $conn->status === 'disconnected';
          $cardClass = $isConnected ? 'connected' : ($hasError ? 'error' : '');
        @endphp
        <div class="provider-card {{ $cardClass }}" onclick="openProvider('{{ $key }}')">
          {{-- Top row: Icon + Status --}}
          <div class="flex items-start justify-between mb-4">
            <div class="provider-icon" style="background: {{ $meta['color'] }}">
              @switch($key)
                @case('salesforce')
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 3a3 3 0 00-3 3v12a3 3 0 003 3 3 3 0 003-3 3 3 0 00-3-3H6a3 3 0 00-3 3 3 3 0 003 3 3 3 0 003-3V6a3 3 0 00-3-3 3 3 0 00-3 3 3 3 0 003 3h12a3 3 0 003-3 3 3 0 00-3-3z"/>
                  </svg>
                  @break
                @case('hubspot')
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/><path d="M12 2v4m0 12v4m10-10h-4M6 12H2m15.07-7.07l-2.83 2.83M9.76 14.24l-2.83 2.83m12.14 0l-2.83-2.83M9.76 9.76L6.93 6.93"/>
                  </svg>
                  @break
                @case('zoho')
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                  </svg>
                  @break
                @case('pipedrive')
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                  </svg>
                  @break
                @case('monday')
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
                  </svg>
                  @break
              @endswitch
            </div>
            <div class="status-badge {{ $isConnected ? 'connected' : ($hasError ? 'error' : 'disconnected') }}">
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

          {{-- Provider Name + Description --}}
          <h3 class="text-[15px] font-bold text-gray-900 mb-1">{{ $meta['name'] }}</h3>
          <p class="text-[12px] text-gray-500 leading-relaxed mb-4" style="min-height: 40px;">{{ $meta['description'] }}</p>

          {{-- Features --}}
          <div class="flex flex-wrap mb-4">
            @foreach($meta['features'] as $feature)
              <span class="feature-tag">{{ $feature }}</span>
            @endforeach
          </div>

          {{-- Connection Info (if connection exists) --}}
          @if($conn)
          <div class="mb-4 p-3 rounded-lg bg-white/60 border border-gray-200/50">
            <div class="flex items-center justify-between mb-1">
              <span class="text-[11px] text-gray-500 font-medium">{{ $conn->connection_name }}</span>
              <span class="text-[10px] text-gray-400">{{ $conn->sync_count }} syncs</span>
            </div>
            <div class="sync-meta">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Last sync: {{ $conn->last_sync_at?->diffForHumans() ?? 'Never' }}
            </div>
            @if($conn->last_error)
            <div class="sync-meta mt-1 text-red-400">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Error: {{ Str::limit($conn->last_error, 50) }}
            </div>
            @endif
          </div>
          @endif

          {{-- Action Buttons --}}
          <div class="flex items-center gap-2">
            @if($isConnected)
              <button class="connect-btn success" onclick="event.stopPropagation(); syncProvider('{{ $key }}')">
                <span class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                  </svg>
                  Sync Now
                </span>
              </button>
              <button class="connect-btn danger" onclick="event.stopPropagation(); disconnectProvider('{{ $key }}')">
                Disconnect
              </button>
            @else
              <button class="connect-btn primary" onclick="event.stopPropagation(); openProvider('{{ $key }}')">
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
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
      <div class="flex items-center gap-2 mb-3">
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="text-[13px] font-semibold text-gray-700">How to Connect Your CRM</h3>
      </div>
      <div class="grid grid-cols-3 gap-4">
        <div class="flex items-start gap-3">
          <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[11px] font-bold flex-shrink-0">1</div>
          <div>
            <p class="text-[12px] font-medium text-gray-700">Select Provider</p>
            <p class="text-[11px] text-gray-500 mt-0.5">Choose your CRM from the cards above. We support Salesforce, HubSpot, Zoho, Pipedrive, and Monday.com.</p>
          </div>
        </div>
        <div class="flex items-start gap-3">
          <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[11px] font-bold flex-shrink-0">2</div>
          <div>
            <p class="text-[12px] font-medium text-gray-700">Enter Credentials</p>
            <p class="text-[11px] text-gray-500 mt-0.5">Provide your API key or OAuth credentials. Your data is encrypted and stored securely.</p>
          </div>
        </div>
        <div class="flex items-start gap-3">
          <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[11px] font-bold flex-shrink-0">3</div>
          <div>
            <p class="text-[12px] font-medium text-gray-700">Start Syncing</p>
            <p class="text-[11px] text-gray-500 mt-0.5">Configure sync settings and start importing your contacts, leads, and deals automatically.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Toast Notification --}}
<div id="toast" class="toast"></div>

@endsection

@push('scripts')
<script>
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.className = 'toast ' + type;
  setTimeout(() => toast.classList.add('show'), 10);
  setTimeout(() => toast.classList.remove('show'), 3000);
}

function openProvider(provider) {
  window.location.href = '{{ route("client.crm.connect", ["provider" => "__PROVIDER__"]) }}'.replace('__PROVIDER__', provider);
}

function syncProvider(provider) {
  fetch('{{ route("client.crm.sync", ["provider" => "__PROVIDER__"]) }}'.replace('__PROVIDER__', provider), {
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

function disconnectProvider(provider) {
  if (!confirm('Are you sure you want to disconnect ' + provider + '?')) return;

  fetch('{{ route("client.crm.disconnect", ["provider" => "__PROVIDER__"]) }}'.replace('__PROVIDER__', provider), {
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

// Close dropdown on outside click
document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush