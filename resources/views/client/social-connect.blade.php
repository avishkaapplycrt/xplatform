{{-- resources/views/client/social-connect.blade.php --}}
@extends('layouts.platform')

@section('title', 'Connect ' . $meta['name'])

@push('styles')
<style>
  .connect-form-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
    padding: 32px;
    max-width: 640px;
    margin: 0 auto;
  }
  .form-group {
    margin-bottom: 20px;
  }
  .form-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
  }
  .form-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 13px;
    color: #111827;
    background: #fff;
    transition: all 0.15s;
    outline: none;
  }
  .form-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }
  .form-input::placeholder {
    color: #9ca3af;
  }
  .form-hint {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 4px;
  }
  .btn-primary {
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    background: #111827;
    color: #fff;
    transition: all 0.15s;
  }
  .btn-primary:hover {
    background: #374151;
  }
  .btn-secondary {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    cursor: pointer;
    background: #fff;
    color: #374151;
    transition: all 0.15s;
  }
  .btn-secondary:hover {
    background: #f9fafb;
  }
  .btn-success {
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    background: #10b981;
    color: #fff;
    transition: all 0.15s;
  }
  .btn-success:hover {
    background: #059669;
  }
  .platform-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #f3f4f6;
  }
  .platform-icon-lg {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    font-weight: 700;
    flex-shrink: 0;
  }
  .sync-config-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
  }
  .sync-config-row:last-child {
    border-bottom: none;
  }
  .toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
    background: #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
  }
  .toggle-switch.active {
    background: #10b981;
  }
  .toggle-switch::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: #fff;
    border-radius: 50%;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
  }
  .toggle-switch.active::after {
    left: 22px;
  }
  .test-result {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    display: none;
    align-items: center;
    gap: 8px;
  }
  .test-result.show {
    display: flex;
  }
  .test-result.success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
  }
  .test-result.error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
  }
  .test-result.loading {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
  }
  .scope-tag {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 6px;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 11px;
    font-weight: 500;
    font-family: monospace;
    margin-right: 6px;
    margin-bottom: 6px;
  }
  .oauth-box {
    border: 2px dashed #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
    background: #fafafa;
    transition: all 0.15s;
  }
  .oauth-box:hover {
    border-color: #3b82f6;
    background: #eff6ff;
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
      <a href="{{ route('client.social-connections') }}"
         class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
         title="Back">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <div>
        <h1 class="text-[16px] font-semibold text-gray-900">Connect {{ $meta['name'] }}</h1>
        <p class="text-[11px] text-gray-500 mt-0.5">
          Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
        </p>
      </div>
    </div>
    <div class="flex items-center gap-4 text-[11px] text-gray-500">
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

  {{-- BODY --}}
  <div class="flex-1 overflow-y-auto px-5 py-6">

    <div class="connect-form-card">

      {{-- Flash Messages (OAuth callback errors/success) --}}
      @if(session('error'))
        <div class="test-result error show mb-4">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif
      @if(session('success'))
        <div class="test-result success show mb-4">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      {{-- Platform Header --}}
      <div class="platform-header">
        <div class="platform-icon-lg" style="background: {{ $meta['color'] }}">
          @switch($platform)
            @case('facebook')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
              @break
            @case('instagram')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/>
                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
              </svg>
              @break
            @case('tiktok')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
              </svg>
              @break
            @case('youtube')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
              </svg>
              @break
            @case('linkedin')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
              </svg>
              @break
          @endswitch
        </div>
        <div>
          <h2 class="text-[18px] font-bold text-gray-900">{{ $meta['name'] }} Integration</h2>
          <p class="text-[12px] text-gray-500 mt-0.5">{{ $meta['description'] }}</p>
          <div class="flex items-center gap-2 mt-2">
            <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-gray-100 text-gray-600 uppercase tracking-wide">{{ $meta['auth_type'] }}</span>
            <a href="{{ $meta['docs_url'] }}" target="_blank" class="text-[11px] text-blue-500 hover:text-blue-600 flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
              API Docs
            </a>
          </div>
        </div>
      </div>

      {{-- OAuth Connect Box (for OAuth platforms) --}}
      @if($meta['auth_type'] === 'oauth2')
      <div class="mb-6">
        <div class="oauth-box" onclick="initiateOAuth('{{ $platform }}')" style="cursor: pointer;">
          <div class="platform-icon-lg mx-auto mb-3" style="background: {{ $meta['color'] }}; width: 48px; height: 48px;">
            @switch($platform)
              @case('facebook')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                @break
              @case('instagram')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                @break
              @case('tiktok')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                @break
              @case('youtube')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                @break
              @case('linkedin')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                @break
            @endswitch
          </div>
          <p class="text-[14px] font-semibold text-gray-700 mb-1">Connect with {{ $meta['name'] }}</p>
          <p class="text-[12px] text-gray-500">Click to authorize via OAuth 2.0</p>
        </div>
        <div class="mt-3">
          <p class="text-[11px] text-gray-500 mb-2">Required permissions:</p>
          <div class="flex flex-wrap">
            @foreach($meta['scopes'] as $scope)
              <span class="scope-tag">{{ $scope }}</span>
            @endforeach
          </div>
        </div>
      </div>
      @endif

      {{-- Connection Form --}}
      <form id="socialConnectForm" onsubmit="return handleSubmit(event)">
        @csrf

        {{-- Connection Name --}}
        <div class="form-group">
          <label class="form-label">Connection Name</label>
          <input type="text" name="connection_name" class="form-input"
                 value="{{ $existing?->connection_name ?? $meta['name'] . ' Account' }}"
                 placeholder="e.g., Main {{ $meta['name'] }} Page" required>
          <p class="form-hint">Give this connection a recognizable name for easy identification.</p>
        </div>

        {{-- Platform-Specific Fields --}}
        @switch($platform)
          @case('facebook')
            <div class="form-group">
              <label class="form-label">Page ID</label>
              <input type="text" name="page_id" class="form-input"
                     value="{{ $existing?->page_id ?? '' }}"
                     placeholder="e.g., 123456789012345" required>
              <p class="form-hint">Your Facebook Page ID (found in Page Settings → Page Info)</p>
            </div>
            <div class="form-group">
              <label class="form-label">Access Token
                @if($existing?->access_token)
                  <span style="color:#10b981;font-weight:500;">(saved via OAuth)</span>
                @endif
              </label>
              <input type="password" name="access_token" class="form-input"
                     placeholder="{{ $existing?->access_token ? 'Token already saved — leave blank to keep it' : 'Enter your Facebook access token' }}"
                     {{ $existing?->access_token ? '' : 'required' }}>
              <p class="form-hint">
                @if($existing?->access_token)
                  Stored encrypted. Leave blank to keep the existing token, or paste a new one to replace it.
                @else
                  Generate from Facebook Developers → Tools → Access Token Debugger — or click "Connect with Facebook" above.
                @endif
              </p>
            </div>
            @break

          @case('instagram')
            <div class="form-group">
              <label class="form-label">Instagram Business Account ID</label>
              <input type="text" name="account_id" class="form-input"
                     value="{{ $existing?->account_id ?? '' }}"
                     placeholder="e.g., 17841405309211844" required>
              <p class="form-hint">Your Instagram Business Account ID (auto-filled when connecting via OAuth above)</p>
            </div>
            <div class="form-group">
              <label class="form-label">Access Token
                @if($existing?->access_token)
                  <span style="color:#10b981;font-weight:500;">(saved via OAuth)</span>
                @endif
              </label>
              <input type="password" name="access_token" class="form-input"
                     placeholder="{{ $existing?->access_token ? 'Token already saved — leave blank to keep it' : 'Enter your Instagram access token' }}"
                     {{ $existing?->access_token ? '' : 'required' }}>
              <p class="form-hint">
                @if($existing?->access_token)
                  Stored encrypted. Leave blank to keep the existing token, or paste a new one to replace it.
                @else
                  Generate from your Meta App → Instagram → API setup → Generate token — or click "Connect with Instagram" above.
                @endif
              </p>
            </div>
            @break

          @case('tiktok')
            <div class="form-group">
              <label class="form-label">Account ID / Open ID</label>
              <input type="text" name="account_id" class="form-input"
                     value="{{ $existing?->account_id ?? '' }}"
                     placeholder="Enter your TikTok account ID" required>
              <p class="form-hint">Your TikTok for Business Account ID</p>
            </div>
            <div class="form-group">
              <label class="form-label">Access Token</label>
              <input type="password" name="access_token" class="form-input"
                     placeholder="Enter your TikTok access token" required>
              <p class="form-hint">Generate from TikTok for Developers → Manage Apps → Your App</p>
            </div>
            @break

          @case('youtube')
            <div class="form-group">
              <label class="form-label">Channel ID</label>
              <input type="text" name="channel_id" class="form-input"
                     value="{{ $existing?->channel_id ?? '' }}"
                     placeholder="e.g., UCxxxxxxxxxxxxxxxxxxx" required>
              <p class="form-hint">Your YouTube Channel ID (found in YouTube Studio → Settings → Channel)</p>
            </div>
            <div class="form-group">
              <label class="form-label">Access Token</label>
              <input type="password" name="access_token" class="form-input"
                     placeholder="Enter your YouTube API access token" required>
              <p class="form-hint">Generate from Google Cloud Console → APIs & Services → Credentials</p>
            </div>
            @break

          @case('linkedin')
            <div class="form-group">
              <label class="form-label">Organization / Page ID</label>
              <input type="text" name="page_id" class="form-input"
                     value="{{ $existing?->page_id ?? '' }}"
                     placeholder="e.g., 12345678" required>
              <p class="form-hint">Your LinkedIn Organization ID (found in LinkedIn Admin → Settings)</p>
            </div>
            <div class="form-group">
              <label class="form-label">Access Token</label>
              <input type="password" name="access_token" class="form-input"
                     placeholder="Enter your LinkedIn access token" required>
              <p class="form-hint">Generate from LinkedIn Developers → My Apps → Auth → OAuth 2.0</p>
            </div>
            @break
        @endswitch

        {{-- Profile URL (Optional) --}}
        <div class="form-group">
          <label class="form-label">Profile URL (Optional)</label>
          <input type="url" name="profile_url" class="form-input"
                 value="{{ $existing?->profile_url ?? '' }}"
                 placeholder="https://...">
          <p class="form-hint">Link to your public profile or page</p>
        </div>

        {{-- Username (Optional) --}}
        <div class="form-group">
          <label class="form-label">Username / Handle (Optional)</label>
          <input type="text" name="username" class="form-input"
                 value="{{ $existing?->username ?? '' }}"
                 placeholder="@username">
        </div>

        {{-- Sync Configuration --}}
        <div class="mt-6 mb-4">
          <h3 class="text-[13px] font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Content Sync Configuration
          </h3>

          <div class="bg-gray-50 rounded-lg p-4">
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Posts / Videos Sync</p>
                <p class="text-[11px] text-gray-500">Import posts, videos, and media content</p>
              </div>
              <div class="toggle-switch active" onclick="this.classList.toggle('active')" data-sync="posts">
                <input type="hidden" name="sync_config[content_types][]" value="posts">
              </div>
            </div>
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Comments</p>
                <p class="text-[11px] text-gray-500">Sync comments and replies</p>
              </div>
              <div class="toggle-switch active" onclick="this.classList.toggle('active')" data-sync="comments">
                <input type="hidden" name="sync_config[content_types][]" value="comments">
              </div>
            </div>
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Reactions / Likes</p>
                <p class="text-[11px] text-gray-500">Track engagement reactions</p>
              </div>
              <div class="toggle-switch active" onclick="this.classList.toggle('active')" data-sync="reactions">
                <input type="hidden" name="sync_config[content_types][]" value="reactions">
              </div>
            </div>
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Hashtag Tracking</p>
                <p class="text-[11px] text-gray-500">Monitor branded hashtags and trends</p>
              </div>
              <div class="toggle-switch" onclick="this.classList.toggle('active')" data-sync="hashtags">
                <input type="hidden" name="sync_config[content_types][]" value="hashtags" disabled>
              </div>
            </div>
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Audience Insights</p>
                <p class="text-[11px] text-gray-500">Sync follower demographics and analytics</p>
              </div>
              <div class="toggle-switch active" onclick="this.classList.toggle('active')" data-sync="insights">
                <input type="hidden" name="sync_config[content_types][]" value="insights">
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Sync Frequency</label>
          <select name="sync_config[sync_frequency]" class="form-input" style="cursor: pointer;">
            <option value="realtime">Real-time (Webhook)</option>
            <option value="15min">Every 15 minutes</option>
            <option value="hourly" selected>Hourly</option>
            <option value="daily">Daily</option>
          </select>
          <p class="form-hint">How often should we sync data from {{ $meta['name'] }}?</p>
        </div>

        <div class="form-group">
          <label class="form-label">Auto-Publish (Optional)</label>
          <select name="sync_config[auto_publish]" class="form-input" style="cursor: pointer;">
            <option value="0" selected>Disabled — Import only</option>
            <option value="1">Enabled — Bidirectional sync</option>
          </select>
          <p class="form-hint">Allow publishing content from this platform to your social accounts</p>
        </div>

        {{-- Test Result --}}
        <div id="testResult" class="test-result mb-4">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
          <span id="testResultText"></span>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
          <button type="submit" name="status" value="connected" class="btn-success flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
            Connect & Save
          </button>
          <button type="button" class="btn-secondary flex items-center gap-2" onclick="testConnection()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Test Connection
          </button>
          <a href="{{ route('client.social-connections') }}" class="btn-secondary">Cancel</a>
        </div>
      </form>

    </div>

  </div>
</div>

<div id="toast" class="toast" style="position:fixed;bottom:24px;right:24px;padding:14px 20px;border-radius:10px;font-size:13px;font-weight:500;color:#fff;z-index:9999;transform:translateY(100px);opacity:0;transition:all 0.3s ease;box-shadow:0 10px 40px rgba(0,0,0,0.15);"></div>

@endsection

@push('scripts')
<script>
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.style.background = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6';
  toast.style.transform = 'translateY(0)';
  toast.style.opacity = '1';
  setTimeout(() => {
    toast.style.transform = 'translateY(100px)';
    toast.style.opacity = '0';
  }, 3000);
}

function initiateOAuth(platform) {
  const oauthRoutes = {
    facebook: '{{ route("client.social.facebook.oauth") }}',
    instagram: '{{ route("client.social.instagram.oauth") }}'
  };
  if (oauthRoutes[platform]) {
    window.location.href = oauthRoutes[platform];
    return;
  }
  showToast('Redirecting to ' + platform + ' OAuth...', 'info');
}

function testConnection() {
  const result = document.getElementById('testResult');
  const text = document.getElementById('testResultText');

  result.className = 'test-result loading show';
  text.textContent = 'Testing connection to {{ $meta['name'] }}...';

  fetch('{{ route("client.social.test", ["platform" => $platform]) }}', {
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
      result.className = 'test-result success show';
      let msg = data.message || 'Connection successful! {{ $meta['name'] }} API is accessible.';
      if (data.details) {
        const extras = Object.entries(data.details)
          .map(([k, v]) => k.replace(/_/g, ' ') + ': ' + v)
          .join(' · ');
        if (extras) msg += ' (' + extras + ')';
      }
      text.textContent = msg;
    } else {
      result.className = 'test-result error show';
      text.textContent = data.message || 'Connection test failed.';
    }
  })
  .catch(err => {
    result.className = 'test-result error show';
    text.textContent = 'Test failed: ' + err.message;
  });
}

function handleSubmit(e) {
  e.preventDefault();
  const form = e.target;

  // Handle toggle switches
  document.querySelectorAll('.toggle-switch').forEach(toggle => {
    const input = toggle.querySelector('input');
    if (input) {
      input.disabled = !toggle.classList.contains('active');
    }
  });

  // Collect sync config content types
  const activeTypes = [];
  document.querySelectorAll('.toggle-switch.active').forEach(toggle => {
    const syncType = toggle.dataset.sync;
    if (syncType) activeTypes.push(syncType);
  });

  const data = Object.fromEntries(new FormData(form));
  data.sync_config = {
    content_types: activeTypes,
    sync_frequency: data['sync_config[sync_frequency]'],
    auto_publish: data['sync_config[auto_publish]'] === '1'
  };

  // Clean up old fields
  Object.keys(data).forEach(key => {
    if (key.startsWith('sync_config[')) delete data[key];
  });

  fetch('{{ route("client.social.store", ["platform" => $platform]) }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => window.location.href = '{{ route("client.social-connections") }}', 1200);
    } else {
      showToast(data.message || 'Connection failed', 'error');
    }
  })
  .catch(err => {
    showToast('Error: ' + err.message, 'error');
  });

  return false;
}

// Toggle switch handling
document.querySelectorAll('.toggle-switch').forEach(toggle => {
  toggle.addEventListener('click', function() {
    const input = this.querySelector('input');
    if (input) {
      input.disabled = this.classList.contains('active');
    }
  });
});

// Close dropdown on outside click
document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush