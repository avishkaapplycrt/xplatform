@extends('layouts.platform')
@section('title', 'Payment Gateway Connections')

@push('styles')
<style>
  .pg-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
    padding: 24px;
    transition: all .2s ease;
    position: relative;
    overflow: hidden;
  }
  .pg-card:hover {
    border-color: #bfdbfe;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transform: translateY(-2px);
  }
  .pg-card.connected {
    border-color: #bbf7d0;
    background: #f0fdf4;
  }
  .pg-card.connected:hover {
    border-color: #86efac;
    box-shadow: 0 4px 20px rgba(16,185,129,0.08);
  }
  .pg-icon-wrap {
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
  .pg-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
  }
  .pg-status-badge.connected {
    background: #dcfce7;
    color: #16a34a;
  }
  .pg-status-badge.disconnected {
    background: #f3f4f6;
    color: #9ca3af;
  }
  .pg-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    padding: 7px 16px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    cursor: pointer;
    text-decoration: none;
    transition: all .15s;
  }
  .pg-btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
  }
  .pg-btn-primary {
    background: #111827;
    color: #fff;
    border-color: #111827;
  }
  .pg-btn-primary:hover {
    background: #374151;
    border-color: #374151;
  }
  .pg-btn-success {
    background: #10b981;
    color: #fff;
    border-color: #10b981;
  }
  .pg-btn-success:hover {
    background: #059669;
    border-color: #059669;
  }
  .pg-btn-danger {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fecaca;
  }
  .pg-btn-danger:hover {
    background: #fee2e2;
  }
  .pg-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
  }
  .pg-header-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #10b981;
  }
  .pg-header-line {
    flex: 1;
    height: 1px;
    background: #e5e7eb;
  }
  .pg-header-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #374151;
    white-space: nowrap;
  }
  .pg-stat {
    text-align: center;
    padding: 14px 10px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
  }
  .pg-stat-value {
    font-size: 22px;
    font-weight: 800;
    line-height: 1;
  }
  .pg-stat-label {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 6px;
  }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
  $connectedCount = collect($gateways)->where('is_connected', true)->count();
  $activeCount = collect($gateways)->where('is_active', true)->count();
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

  {{-- HEADER --}}
  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Payment Gateway Connections</h1>
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
        <svg class="w-3.5 h-3.5 text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span class="font-medium text-gray-600">{{ count($gateways) }} Gateways</span>
      </span>
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

    {{-- KPI Stats --}}
    <div class="grid grid-cols-4 gap-4">
      <div class="pg-stat">
        <p class="pg-stat-value text-blue-500">{{ count($gateways) }}</p>
        <p class="pg-stat-label">Total Gateways</p>
      </div>
      <div class="pg-stat">
        <p class="pg-stat-value text-emerald-500">{{ $connectedCount }}</p>
        <p class="pg-stat-label">Connected</p>
      </div>
      <div class="pg-stat">
        <p class="pg-stat-value text-amber-500">{{ $activeCount }}</p>
        <p class="pg-stat-label">Active</p>
      </div>
      <div class="pg-stat">
        <p class="pg-stat-value text-violet-500">{{ count($gateways) - $connectedCount }}</p>
        <p class="pg-stat-label">Available</p>
      </div>
    </div>

    {{-- Section Header --}}
    <div class="pg-header">
      <div class="pg-header-dot"></div>
      <span class="pg-header-title">Available Payment Gateways</span>
      <div class="pg-header-line"></div>
    </div>

    {{-- Gateway Cards Grid --}}
    <div class="grid grid-cols-3 gap-4">
      @foreach($gateways as $gateway)
      <div class="pg-card {{ $gateway['is_connected'] ? 'connected' : '' }}">
        {{-- Top Row: Icon + Status --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px">
          <div style="display:flex;align-items:center;gap:12px">
            <div class="pg-icon-wrap" style="background:{{ $gateway['color'] }}">
              @if($gateway['icon'] === 'stripe')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="4"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
              @elseif($gateway['icon'] === 'shopify')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              @elseif($gateway['icon'] === 'zapier')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
              @elseif($gateway['icon'] === 'webhooks')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
              @elseif($gateway['icon'] === 'paypal')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="4"/><path d="M16 11h0"/></svg>
              @elseif($gateway['icon'] === 'woocommerce')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
              @endif
            </div>
            <div>
              <p style="font-size:14px;font-weight:700;color:#111827">{{ $gateway['name'] }}</p>
              <p style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $gateway['description'] }}</p>
            </div>
          </div>
          <span class="pg-status-badge {{ $gateway['is_connected'] ? 'connected' : 'disconnected' }}">
            <span style="width:5px;height:5px;border-radius:50%;background:currentColor;display:inline-block"></span>
            {{ $gateway['is_connected'] ? 'Connected' : 'Not Connected' }}
          </span>
        </div>

        {{-- Connection Info --}}
        @if($gateway['is_connected'] && $gateway['connection'])
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;margin-bottom:14px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <span style="font-size:11px;color:#9ca3af">Environment</span>
            <span style="font-size:11px;font-weight:600;color:#374151;text-transform:capitalize">{{ $gateway['connection']->environment }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <span style="font-size:11px;color:#9ca3af">Currency</span>
            <span style="font-size:11px;font-weight:600;color:#374151">{{ $gateway['connection']->currency }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:11px;color:#9ca3af">Last Synced</span>
            <span style="font-size:11px;font-weight:600;color:#374151">{{ $gateway['connection']->last_synced_at ? $gateway['connection']->last_synced_at->diffForHumans() : 'Never' }}</span>
          </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div style="display:flex;gap:8px">
          @if($gateway['is_connected'])
            <a href="{{ route('client.payment-gateway-connections.show', $gateway['key']) }}" class="pg-btn pg-btn-primary" style="flex:1;justify-content:center">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Manage
            </a>
            <form method="POST" action="{{ route('client.payment-gateway-connections.disconnect', $gateway['key']) }}" style="display:inline">
              @csrf
              @method('PATCH')
              <button type="submit" class="pg-btn pg-btn-danger" onclick="return confirm('Disconnect {{ $gateway['name'] }}?')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </form>
          @else
            <a href="{{ route('client.payment-gateway-connections.show', $gateway['key']) }}" class="pg-btn pg-btn-primary" style="flex:1;justify-content:center">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              Connect
            </a>
          @endif
        </div>
      </div>
      @endforeach
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush
