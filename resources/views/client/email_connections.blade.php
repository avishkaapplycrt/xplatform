@extends('layouts.platform')
@section('title', 'Email Engagement Connections')

@push('styles')
<style>
  .provider-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
    padding: 28px 24px;
    text-align: center;
    cursor: pointer;
    transition: all .2s ease;
    position: relative;
    overflow: hidden;
  }
  .provider-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.12);
    transform: translateY(-3px);
  }
  .provider-card .icon-wrap {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    transition: all .2s ease;
  }
  .provider-card:hover .icon-wrap {
    transform: scale(1.05);
  }
  .provider-card .provider-name {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 6px;
  }
  .provider-card .provider-desc {
    font-size: 12px;
    color: #9ca3af;
    line-height: 1.5;
    min-height: 54px;
  }
  .provider-card .connect-btn {
    margin-top: 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    cursor: pointer;
    transition: all .15s ease;
  }
  .provider-card:hover .connect-btn {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
  }
  .provider-card .status-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    background: #f3f4f6;
    color: #9ca3af;
  }
  .provider-card.connected .status-badge {
    background: #dcfce7;
    color: #16a34a;
  }
  .provider-card.connected {
    border-color: #22c55e;
  }
  .provider-card.connected .connect-btn {
    background: #22c55e;
    color: #fff;
    border-color: #22c55e;
  }
  .provider-card.connected:hover .connect-btn {
    background: #16a34a;
    border-color: #16a34a;
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
  .section-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
  }
  .section-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
  }
  .section-line {
    flex: 1;
    height: 1px;
  }
  .section-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    white-space: nowrap;
  }
  .panel-card {
    border: 1px solid #edf0f2;
    border-radius: 12px;
    background: #fff;
    padding: 16px;
  }
  .connected-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 8px;
  }
  .sync-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    cursor: pointer;
    transition: all .15s;
  }
  .sync-btn:hover {
    background: #f3f4f6;
  }
  .sync-btn.loading {
    opacity: .6;
    pointer-events: none;
  }
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
  <div class="flex items-center gap-3">
    <a href="{{ route('client.data-collection') }}" class="back-link">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
      </svg>
      Back to Data Collection
    </a>
    <div class="w-px h-5 bg-gray-200"></div>
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Email Engagement Connections</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">
        Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
        <span class="ml-2 inline-flex items-center gap-1 text-green-600 font-medium text-[10px]">
          <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Live
        </span>
      </p>
    </div>
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
<div class="flex-1 overflow-y-auto px-5 py-4">

  {{-- Page Title --}}
  <div class="mb-6">
    <h2 class="text-[18px] font-bold text-gray-900">Choose Your Email Provider</h2>
    <p class="text-[12px] text-gray-500 mt-1">Connect your email marketing platform to sync campaigns, audiences, and engagement analytics.</p>
  </div>

  {{-- Provider Cards Grid --}}
  <div class="grid grid-cols-5 gap-4">

    {{-- MailChimp --}}
    <a href="{{ route('client.email-connections.mailchimp') }}" class="provider-card {{ $providers[0]['connected'] ? 'connected' : '' }}" style="text-decoration:none; display:block;">
      <span class="status-badge">{{ $providers[0]['connected'] ? 'Connected' : 'Not Connected' }}</span>
      <div class="icon-wrap" style="background: {{ $providers[0]['color'] }}20;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
          <rect x="3" y="5" width="18" height="14" rx="3" fill="#ffe01b"/>
          <path d="M7 9l5 5 5-5" stroke="#241c15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="12" cy="12" r="2" fill="#241c15"/>
        </svg>
      </div>
      <p class="provider-name">MailChimp</p>
      <p class="provider-desc">{{ $providers[0]['description'] }}</p>
      <span class="connect-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
        {{ $providers[0]['connected'] ? 'Manage' : 'Connect' }}
      </span>
    </a>

    {{-- Brevo --}}
    <a href="{{ route('client.email-connections.brevo') }}" class="provider-card {{ $providers[1]['connected'] ? 'connected' : '' }}" style="text-decoration:none; display:block;">
      <span class="status-badge">{{ $providers[1]['connected'] ? 'Connected' : 'Not Connected' }}</span>
      <div class="icon-wrap" style="background: {{ $providers[1]['color'] }}20;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
          <rect x="3" y="4" width="18" height="16" rx="3" fill="#0b996e"/>
          <path d="M7 8l5 4 5-4" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M7 12l5 4 5-4" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.6"/>
        </svg>
      </div>
      <p class="provider-name">Brevo</p>
      <p class="provider-desc">{{ $providers[1]['description'] }}</p>
      <span class="connect-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
        {{ $providers[1]['connected'] ? 'Manage' : 'Connect' }}
      </span>
    </a>

    {{-- Constant Contact --}}
    <a href="{{ route('client.email-connections.constantcontact') }}" class="provider-card {{ $providers[2]['connected'] ? 'connected' : '' }}" style="text-decoration:none; display:block;">
      <span class="status-badge">{{ $providers[2]['connected'] ? 'Connected' : 'Not Connected' }}</span>
      <div class="icon-wrap" style="background: {{ $providers[2]['color'] }}20;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
          <rect x="3" y="4" width="18" height="16" rx="3" fill="#1856ed"/>
          <circle cx="12" cy="12" r="4" fill="#fff"/>
          <circle cx="12" cy="12" r="2" fill="#1856ed"/>
        </svg>
      </div>
      <p class="provider-name">Constant Contact</p>
      <p class="provider-desc">{{ $providers[2]['description'] }}</p>
      <span class="connect-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
        {{ $providers[2]['connected'] ? 'Manage' : 'Connect' }}
      </span>
    </a>

    {{-- MailerLite --}}
    <a href="{{ route('client.email-connections.mailerlite') }}" class="provider-card {{ $providers[3]['connected'] ? 'connected' : '' }}" style="text-decoration:none; display:block;">
      <span class="status-badge">{{ $providers[3]['connected'] ? 'Connected' : 'Not Connected' }}</span>
      <div class="icon-wrap" style="background: {{ $providers[3]['color'] }}20;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
          <rect x="3" y="4" width="18" height="16" rx="3" fill="#00aaff"/>
          <path d="M8 9h8M8 12h6M8 15h4" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <p class="provider-name">MailerLite</p>
      <p class="provider-desc">{{ $providers[3]['description'] }}</p>
      <span class="connect-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
        {{ $providers[3]['connected'] ? 'Manage' : 'Connect' }}
      </span>
    </a>

    {{-- Moosend --}}
    <a href="{{ route('client.email-connections.moosend') }}" class="provider-card {{ $providers[4]['connected'] ? 'connected' : '' }}" style="text-decoration:none; display:block;">
      <span class="status-badge">{{ $providers[4]['connected'] ? 'Connected' : 'Not Connected' }}</span>
      <div class="icon-wrap" style="background: {{ $providers[4]['color'] }}20;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
          <rect x="3" y="4" width="18" height="16" rx="3" fill="#00d4aa"/>
          <path d="M7 9l3 3 3-3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M7 13l3 3 3-3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.6"/>
        </svg>
      </div>
      <p class="provider-name">Moosend</p>
      <p class="provider-desc">{{ $providers[4]['description'] }}</p>
      <span class="connect-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
        {{ $providers[4]['connected'] ? 'Manage' : 'Connect' }}
      </span>
    </a>

  </div>

  @if($connections->isNotEmpty())
  {{-- Engagement Stats --}}
  <div class="mt-6 panel-card" style="border:1px solid #e5e7eb; border-radius:12px; background:#fff; padding:20px 24px;">
    <div class="section-header" style="margin-bottom:16px">
      <div class="section-dot" style="background:#3b82f6"></div>
      <span class="section-title" style="color:#3b82f6">Email Engagement Stats</span>
      <div class="section-line" style="background:#bfdbfe"></div>
    </div>
    <div class="grid grid-cols-4 gap-4" style="padding-bottom:18px; margin-bottom:18px; border-bottom:1px solid #f3f4f6;">
      <div>
        <p class="text-[12px] text-gray-500">Delivered</p>
        <p class="text-[22px] font-bold text-gray-900 mt-1">{{ number_format($stats['delivered']) }}</p>
        <p class="text-[11px] text-gray-400 mt-3">Delivery rate</p>
        <p class="text-[14px] font-semibold text-gray-700">{{ $stats['delivery_rate'] }}%</p>
      </div>
      <div>
        <p class="text-[12px] text-gray-500">Opens</p>
        <p class="text-[22px] font-bold text-gray-900 mt-1">{{ number_format($stats['opens']) }}</p>
        <p class="text-[11px] text-gray-400 mt-3">Open rate</p>
        <p class="text-[14px] font-semibold text-gray-700">{{ $stats['open_rate'] }}%</p>
      </div>
      <div>
        <p class="text-[12px] text-gray-500">Clicks</p>
        <p class="text-[22px] font-bold text-gray-900 mt-1">{{ number_format($stats['clicks']) }}</p>
        <p class="text-[11px] text-gray-400 mt-3">Click-through rate</p>
        <p class="text-[14px] font-semibold text-gray-700">{{ $stats['click_rate'] }}%</p>
      </div>
      <div>
        <p class="text-[12px] text-gray-500">Conversions</p>
        <p class="text-[22px] font-bold text-gray-900 mt-1">{{ number_format($stats['conversions']) }}</p>
        <p class="text-[11px] text-gray-400 mt-3">Conversion rate</p>
        <p class="text-[14px] font-semibold text-gray-700">{{ $stats['conversion_rate'] }}%</p>
      </div>
    </div>
    <div>
      <p class="text-[12px]" style="color:#dc2626">Unsubscribes</p>
      <p class="text-[22px] font-bold text-gray-900 mt-1">{{ number_format($stats['unsubscribes']) }}</p>
      <p class="text-[11px] text-gray-400 mt-3">Unsubscribe rate</p>
      <p class="text-[14px] font-semibold text-gray-700">{{ $stats['unsubscribe_rate'] }}%</p>
    </div>
  </div>
  @endif

  {{-- How It Works --}}
  <div class="mt-6 panel-card" style="border:1px solid #e5e7eb; border-radius:12px; background:#fff; padding:20px 24px;">
    <div class="section-header" style="margin-bottom:16px">
      <div class="section-dot" style="background:#3b82f6"></div>
      <span class="section-title" style="color:#3b82f6">How Email Engagement Works</span>
      <div class="section-line" style="background:#bfdbfe"></div>
    </div>
    <div class="grid grid-cols-3 gap-4">
      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 text-blue-500 font-bold text-sm">1</div>
        <div>
          <p class="text-[13px] font-semibold text-gray-900">Select Provider</p>
          <p class="text-[11px] text-gray-500 mt-1">Choose your email marketing platform from the cards above.</p>
        </div>
      </div>
      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 text-blue-500 font-bold text-sm">2</div>
        <div>
          <p class="text-[13px] font-semibold text-gray-900">Authenticate</p>
          <p class="text-[11px] text-gray-500 mt-1">Enter your API key or authorize via OAuth securely.</p>
        </div>
      </div>
      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 text-blue-500 font-bold text-sm">3</div>
        <div>
          <p class="text-[13px] font-semibold text-gray-900">Sync Data</p>
          <p class="text-[11px] text-gray-500 mt-1">Campaigns, audiences, and engagement metrics flow automatically.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Connected Providers List --}}
  <div class="mt-6 panel-card" style="border:1px solid #e5e7eb; border-radius:12px; background:#fff; padding:20px 24px;">
    <div class="section-header" style="margin-bottom:16px">
      <div class="section-dot" style="background:#10b981"></div>
      <span class="section-title" style="color:#10b981">Connected Providers</span>
      <div class="section-line" style="background:#a7f3d0"></div>
    </div>
    <div id="connected-providers-list">
      @forelse($connections as $conn)
      <div class="connected-row" data-connection-id="{{ $conn->id }}">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: {{ $conn->settings['color'] ?? '#f3f4f6' }};">
            <span class="text-[10px] font-bold" style="color: {{ $conn->settings['text_color'] ?? '#6b7280' }};">{{ strtoupper(substr($conn->platform, 0, 2)) }}</span>
          </div>
          <div>
            <p class="text-[13px] font-semibold text-gray-900">{{ ucfirst($conn->platform) }}</p>
            <p class="text-[11px] text-gray-500">Connected {{ $conn->connected_at->diffForHumans() }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-[10px] font-semibold px-2 py-1 rounded-full {{ $conn->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
            {{ ucfirst($conn->status) }}
          </span>
          <button onclick="syncProvider({{ $conn->id }})" class="sync-btn" title="Sync Now">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Sync
          </button>
          <button onclick="disconnectProvider({{ $conn->id }})" class="sync-btn" style="color:#dc2626; border-color:#fecaca;" title="Disconnect">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>
      @empty
      <div class="connected-row">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-[13px] font-semibold text-gray-900">No providers connected yet</p>
            <p class="text-[11px] text-gray-500">Select a provider above to connect your first email platform.</p>
          </div>
        </div>
      </div>
      @endforelse
    </div>
  </div>

</div>{{-- /scrollable body --}}
</div>

@endsection

@push('scripts')
<script>
function syncProvider(id) {
  if (!confirm('Sync data from this provider now?')) return;

  const btn = event.target.closest('.sync-btn');
  btn.classList.add('loading');

  fetch('{{ url("/app/email-connections") }}/' + id + '/sync', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      alert('Sync initiated successfully.');
    } else {
      alert(res.message || 'Sync failed.');
    }
  })
  .catch(err => {
    console.error(err);
    alert('An error occurred. Please try again.');
  })
  .finally(() => {
    btn.classList.remove('loading');
  });
}

function disconnectProvider(id) {
  if (!confirm('Are you sure you want to disconnect this provider? All synced data will be removed.')) return;

  fetch('{{ url("/app/email-connections") }}/' + id, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      alert('Provider disconnected successfully.');
      window.location.reload();
    } else {
      alert(res.message || 'Disconnection failed.');
    }
  })
  .catch(err => {
    console.error(err);
    alert('An error occurred. Please try again.');
  });
}

document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush
