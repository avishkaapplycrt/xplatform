{{-- resources/views/client/chat-support-connect.blade.php --}}
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
  .provider-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #f3f4f6;
  }
  .provider-icon-lg {
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
    display: block;
    text-decoration: none;
  }
  .oauth-box:hover {
    border-color: #3b82f6;
    background: #eff6ff;
  }
  .add-to-slack-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    color: #fff;
    background: #4A154B;
    transition: all 0.15s;
    text-decoration: none;
  }
  .add-to-slack-btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
  }
  .workspace-info {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 20px;
  }
  .workspace-info h4 {
    font-size: 13px;
    font-weight: 600;
    color: #166534;
    margin: 0 0 4px 0;
  }
  .workspace-info p {
    font-size: 12px;
    color: #15803d;
    margin: 0;
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
  .twilio-guide {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 20px;
  }
  .twilio-guide h4 {
    font-size: 13px;
    font-weight: 600;
    color: #0369a1;
    margin: 0 0 8px 0;
  }
  .twilio-guide ol {
    margin: 0;
    padding-left: 16px;
  }
  .twilio-guide li {
    font-size: 12px;
    color: #0c4a6e;
    margin-bottom: 4px;
  }
  .twilio-guide a {
    color: #0284c7;
    text-decoration: underline;
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
      <a href="{{ route('client.chat-support-connections') }}"
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

      {{-- Provider Header --}}
      <div class="provider-header">
        <div class="provider-icon-lg" style="background: {{ $meta['color'] }}">
          @switch($provider)
            @case('whatsapp')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
              </svg>
              @break
            @case('slack')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                <path d="M5.042 15.165a2.528 2.528 0 01-2.52 2.523A2.528 2.528 0 010 15.165a2.527 2.527 0 012.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 012.521-2.52 2.527 2.527 0 012.521 2.52v6.313A2.528 2.528 0 018.834 24a2.528 2.528 0 01-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 01-2.521-2.52A2.528 2.528 0 018.834 0a2.528 2.528 0 012.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 012.521 2.521 2.528 2.528 0 01-2.521 2.521H2.522A2.528 2.528 0 010 8.834a2.528 2.528 0 012.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 012.522-2.521A2.528 2.528 0 0124 8.834a2.528 2.528 0 01-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 01-2.523 2.521 2.527 2.527 0 01-2.52-2.521V2.522A2.527 2.527 0 0115.165 0a2.528 2.528 0 012.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 012.523 2.522A2.528 2.528 0 0115.165 24a2.527 2.527 0 01-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 01-2.52-2.523 2.526 2.526 0 012.52-2.52h6.313A2.527 2.527 0 0124 15.165a2.528 2.528 0 01-2.522 2.523h-6.313z"/>
              </svg>
              @break
            @case('twilio')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 0C5.381 0 0 5.381 0 12s5.381 12 12 12 12-5.381 12-12S18.619 0 12 0zm0 22.875C5.981 22.875 1.125 18.019 1.125 12S5.981 1.125 12 1.125 22.875 5.981 22.875 12 18.019 22.875 12 22.875zM8.25 8.25h3.375v3.375H8.25V8.25zm0 4.5h3.375v3.375H8.25V12.75zm4.5-4.5h3.375v3.375H12.75V8.25zm0 4.5h3.375v3.375H12.75V12.75z"/>
              </svg>
              @break
            @case('zendesk')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11.08 0H0v17.293h4.797V4.79h6.283V0zm1.839 24h11.08V6.707H19.2v12.503h-6.283V24zM4.797 24h6.283V11.08H0v4.797h4.797V24zm14.406-17.293H12.92V0h11.08v4.797h-4.797v1.91z"/>
              </svg>
              @break
            @case('tawk')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5.01L2 22l5.09-1.33A9.96 9.96 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm-1 14.5h-2v-2h2v2zm0-3h-2v-5h2v5zm4 3h-2v-2h2v2zm0-3h-2v-5h2v5z"/>
              </svg>
              @break
            @case('intercom')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22c-5.523 0-10-4.477-10-10S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-6h2v-8h-2v8zm0-10h2V4h-2v2z"/>
              </svg>
              @break
            @case('livechat')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
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

      {{-- OAuth Connect Box (for OAuth platforms only) --}}
      @if($meta['auth_type'] === 'oauth2')
      <div class="mb-6">
        @php
          $oauthRoute = match($provider) {
            'slack'    => route('client.chat-support.slack.redirect'),
            'whatsapp' => route('client.chat-support.whatsapp.redirect'),
            default    => null,
          };
        @endphp

        {{-- If already connected via OAuth, show workspace info --}}
        @if($existing && $existing->is_connected && ($existing->settings['connected_via'] ?? null) === 'oauth')
          <div class="workspace-info">
            <div class="flex items-center gap-2 mb-2">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <h4>Connected Workspace</h4>
            </div>
            <p><strong>{{ $existing->workspace_name ?? 'Unknown Workspace' }}</strong></p>
            <p class="text-[11px] mt-1">Workspace ID: {{ $existing->workspace_id ?? 'N/A' }}</p>
            <p class="text-[11px] mt-1">Connected {{ $existing->last_sync_at?->diffForHumans() ?? 'recently' }}</p>
          </div>

          <a href="{{ $oauthRoute }}" class="add-to-slack-btn" style="background: #4A154B;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M5.042 15.165a2.528 2.528 0 01-2.52 2.523A2.528 2.528 0 010 15.165a2.527 2.527 0 012.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 012.521-2.52 2.527 2.527 0 012.521 2.52v6.313A2.528 2.528 0 018.834 24a2.528 2.528 0 01-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 01-2.521-2.52A2.528 2.528 0 018.834 0a2.528 2.528 0 012.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 012.521 2.521 2.528 2.528 0 01-2.521 2.521H2.522A2.528 2.528 0 010 8.834a2.528 2.528 0 012.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 012.522-2.521A2.528 2.528 0 0124 8.834a2.528 2.528 0 01-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 01-2.523 2.521 2.527 2.527 0 01-2.52-2.521V2.522A2.527 2.527 0 0115.165 0a2.528 2.528 0 012.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 012.523 2.522A2.528 2.528 0 0115.165 24a2.527 2.527 0 01-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 01-2.52-2.523 2.526 2.526 0 012.52-2.52h6.313A2.527 2.527 0 0124 15.165a2.528 2.528 0 01-2.522 2.523h-6.313z"/>
            </svg>
            Reconnect {{ $meta['name'] }}
          </a>
        @else
          <a href="{{ $oauthRoute }}" class="add-to-slack-btn" style="background: {{ $meta['color'] }};">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M5.042 15.165a2.528 2.528 0 01-2.52 2.523A2.528 2.528 0 010 15.165a2.527 2.527 0 012.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 012.521-2.52 2.527 2.527 0 012.521 2.52v6.313A2.528 2.528 0 018.834 24a2.528 2.528 0 01-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 01-2.521-2.52A2.528 2.528 0 018.834 0a2.528 2.528 0 012.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 012.521 2.521 2.528 2.528 0 01-2.521 2.521H2.522A2.528 2.528 0 010 8.834a2.528 2.528 0 012.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 012.522-2.521A2.528 2.528 0 0124 8.834a2.528 2.528 0 01-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 01-2.523 2.521 2.527 2.527 0 01-2.52-2.521V2.522A2.527 2.527 0 0115.165 0a2.528 2.528 0 012.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 012.523 2.522A2.528 2.528 0 0115.165 24a2.527 2.527 0 01-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 01-2.52-2.523 2.526 2.526 0 012.52-2.52h6.313A2.527 2.527 0 0124 15.165a2.528 2.528 0 01-2.522 2.523h-6.313z"/>
            </svg>
            Add to {{ $meta['name'] }}
          </a>
          <p class="text-[11px] text-gray-500 mt-2 text-center">Click to authorize your {{ $meta['name'] }} workspace. You'll be redirected to approve permissions.</p>
        @endif

        @if(!empty($meta['scopes']))
        <div class="mt-4">
          <p class="text-[11px] text-gray-500 mb-2">Required permissions:</p>
          <div class="flex flex-wrap">
            @foreach($meta['scopes'] as $scope)
              <span class="scope-tag">{{ $scope }}</span>
            @endforeach
          </div>
        </div>
        @endif
      </div>
      @endif

      {{-- Connection Form (for API key / manual entry providers) --}}
      @if($meta['auth_type'] === 'api_key')
      <form id="chatSupportConnectForm" onsubmit="return handleSubmit(event)">
        @csrf

        {{-- Connection Name --}}
        <div class="form-group">
          <label class="form-label">Connection Name</label>
          <input type="text" name="connection_name" class="form-input"
                 value="{{ $existing?->connection_name ?? $meta['name'] . ' Connection' }}"
                 placeholder="e.g., Main {{ $meta['name'] }} Account" required>
          <p class="form-hint">Give this connection a recognizable name for easy identification.</p>
        </div>

        {{-- Twilio Guide --}}
        @if($provider === 'twilio')
        <div class="twilio-guide">
          <h4>How to get your Twilio credentials</h4>
          <ol>
            <li>Log in to your <a href="https://console.twilio.com" target="_blank">Twilio Console</a></li>
            <li>Your <strong>Account SID</strong> and <strong>Auth Token</strong> are on the dashboard</li>
            <li>Go to <strong>Phone Numbers → Manage → Active Numbers</strong> to get your phone number</li>
            <li>Copy and paste all three values below</li>
          </ol>
        </div>
        @endif

        {{-- Provider-Specific Fields --}}
        @switch($provider)
          @case('whatsapp')
            <div class="form-group">
              <label class="form-label">Phone Number (with country code)</label>
              <input type="text" name="phone_number" class="form-input"
                     value="{{ $existing?->phone_number ?? '' }}"
                     placeholder="e.g., +1234567890" required>
              <p class="form-hint">Your WhatsApp Business phone number with country code</p>
            </div>
            <div class="form-group">
              <label class="form-label">API Key / Access Token</label>
              <input type="password" name="api_key" class="form-input"
                     placeholder="Enter your WhatsApp Business API key" required>
              <p class="form-hint">Generate from Meta for Developers → WhatsApp → API Setup</p>
            </div>
            <div class="form-group">
              <label class="form-label">Webhook URL (Optional)</label>
              <input type="url" name="webhook_url" class="form-input"
                     value="{{ $existing?->webhook_url ?? '' }}"
                     placeholder="https://your-domain.com/webhook/whatsapp">
              <p class="form-hint">URL to receive real-time message webhooks</p>
            </div>
            @break

          @case('twilio')
            <div class="form-group">
              <label class="form-label">Account SID</label>
              <input type="text" name="account_sid" class="form-input"
                     value="{{ $existing?->account_sid ?? '' }}"
                     placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" required
                     pattern="^AC[0-9a-fA-F]{32}$">
              <p class="form-hint">Found in Twilio Console → Account Info. Starts with "AC"</p>
            </div>
            <div class="form-group">
              <label class="form-label">Auth Token</label>
              <input type="password" name="auth_token" class="form-input"
                     placeholder="Enter your Twilio Auth Token" required minlength="32">
              <p class="form-hint">Keep this secure — never share your auth token</p>
            </div>
            <div class="form-group">
              <label class="form-label">Twilio Phone Number</label>
              <input type="text" name="phone_number" class="form-input"
                     value="{{ $existing?->phone_number ?? '' }}"
                     placeholder="+1234567890" required
                     pattern="^\+[1-9]\d{1,14}$">
              <p class="form-hint">Your Twilio phone number in E.164 format (e.g., +14155551234)</p>
            </div>
            @break

          @case('tawk')
            <div class="form-group">
              <label class="form-label">Property ID / App ID</label>
              <input type="text" name="app_id" class="form-input"
                     value="{{ $existing?->app_id ?? '' }}"
                     placeholder="e.g., 12345a67b8c9d0e123f4g5h6" required>
              <p class="form-hint">Found in Tawk.to Dashboard → Admin → Property Settings</p>
            </div>
            <div class="form-group">
              <label class="form-label">API Key</label>
              <input type="password" name="api_key" class="form-input"
                     placeholder="Enter your Tawk.to API key" required>
              <p class="form-hint">Generate from Tawk.to Dashboard → Developer API</p>
            </div>
            <div class="form-group">
              <label class="form-label">Channel ID (Optional)</label>
              <input type="text" name="channel_id" class="form-input"
                     value="{{ $existing?->channel_id ?? '' }}"
                     placeholder="Specific widget/channel ID">
            </div>
            @break

          @case('livechat')
            <div class="form-group">
              <label class="form-label">License ID</label>
              <input type="text" name="license_id" class="form-input"
                     value="{{ $existing?->license_id ?? '' }}"
                     placeholder="e.g., 12345678" required>
              <p class="form-hint">Found in LiveChat Settings → Account → License ID</p>
            </div>
            <div class="form-group">
              <label class="form-label">Personal Access Token</label>
              <input type="password" name="api_key" class="form-input"
                     placeholder="Enter your LiveChat PAT" required>
              <p class="form-hint">Generate from LiveChat Developer Console → Personal Access Tokens</p>
            </div>
            <div class="form-group">
              <label class="form-label">Webhook URL (Optional)</label>
              <input type="url" name="webhook_url" class="form-input"
                     value="{{ $existing?->webhook_url ?? '' }}"
                     placeholder="https://your-domain.com/webhook/livechat">
              <p class="form-hint">URL to receive chat event webhooks</p>
            </div>
            @break
        @endswitch

        {{-- Sync Configuration --}}
        <div class="mt-6 mb-4">
          <h3 class="text-[13px] font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Message Sync Configuration
          </h3>

          <div class="bg-gray-50 rounded-lg p-4">
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Incoming Messages</p>
                <p class="text-[11px] text-gray-500">Sync messages received from customers</p>
              </div>
              <div class="toggle-switch active" onclick="this.classList.toggle('active')" data-sync="incoming">
                <input type="hidden" name="sync_config[message_types][]" value="incoming">
              </div>
            </div>
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Outgoing Messages</p>
                <p class="text-[11px] text-gray-500">Sync replies sent by agents</p>
              </div>
              <div class="toggle-switch active" onclick="this.classList.toggle('active')" data-sync="outgoing">
                <input type="hidden" name="sync_config[message_types][]" value="outgoing">
              </div>
            </div>
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">System Events</p>
                <p class="text-[11px] text-gray-500">Track status changes, assignments, tags</p>
              </div>
              <div class="toggle-switch active" onclick="this.classList.toggle('active')" data-sync="system">
                <input type="hidden" name="sync_config[message_types][]" value="system">
              </div>
            </div>
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Media Attachments</p>
                <p class="text-[11px] text-gray-500">Sync images, files, and media content</p>
              </div>
              <div class="toggle-switch active" onclick="this.classList.toggle('active')" data-sync="media">
                <input type="hidden" name="sync_config[message_types][]" value="media">
              </div>
            </div>
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Sentiment Analysis</p>
                <p class="text-[11px] text-gray-500">Analyze message sentiment automatically</p>
              </div>
              <div class="toggle-switch" onclick="this.classList.toggle('active')" data-sync="sentiment">
                <input type="hidden" name="sync_config[message_types][]" value="sentiment" disabled>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Sync Frequency</label>
          <select name="sync_config[sync_frequency]" class="form-input" style="cursor: pointer;">
            <option value="realtime" selected>Real-time (Webhook)</option>
            <option value="1min">Every 1 minute</option>
            <option value="5min">Every 5 minutes</option>
            <option value="15min">Every 15 minutes</option>
          </select>
          <p class="form-hint">How often should we sync messages from {{ $meta['name'] }}?</p>
        </div>

        <div class="form-group">
          <label class="form-label">Auto-Reply (Optional)</label>
          <select name="sync_config[auto_reply]" class="form-input" style="cursor: pointer;">
            <option value="0" selected>Disabled</option>
            <option value="1">Enabled — Send automated responses</option>
          </select>
          <p class="form-hint">Automatically send predefined responses during off-hours</p>
        </div>

        <div class="form-group">
          <label class="form-label">Store Media Files</label>
          <select name="sync_config[store_media]" class="form-input" style="cursor: pointer;">
            <option value="1" selected>Yes — Download and store attachments</option>
            <option value="0">No — Store references only</option>
          </select>
          <p class="form-hint">Whether to download media files or just store URLs</p>
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
          <a href="{{ route('client.chat-support-connections') }}" class="btn-secondary">Cancel</a>
        </div>
      </form>
      @endif

    </div>

  </div>
</div>

<div id="toast" class="toast"></div>

@endsection

@push('scripts')
<script>
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.style.background = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

function testConnection() {
  const result = document.getElementById('testResult');
  const text = document.getElementById('testResultText');

  result.className = 'test-result loading show';
  text.textContent = 'Testing connection to {{ $meta['name'] }}...';

  // For Twilio, do a real test via API
  @if($provider === 'twilio')
    const form = document.getElementById('chatSupportConnectForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    fetch('{{ route("client.chat-support.test", ["provider" => $provider]) }}', {
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
        result.className = 'test-result success show';
        text.textContent = data.message;
      } else {
        result.className = 'test-result error show';
        text.textContent = data.message;
      }
    })
    .catch(err => {
      result.className = 'test-result error show';
      text.textContent = 'Test failed: ' + err.message;
    });
  @else
    setTimeout(() => {
      result.className = 'test-result success show';
      text.textContent = 'Connection successful! {{ $meta['name'] }} API is accessible.';
    }, 1500);
  @endif
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

  // Collect active message types
  const activeTypes = [];
  document.querySelectorAll('.toggle-switch.active').forEach(toggle => {
    const syncType = toggle.dataset.sync;
    if (syncType) activeTypes.push(syncType);
  });

  const data = Object.fromEntries(new FormData(form));
  data.sync_config = {
    message_types: activeTypes,
    sync_frequency: data['sync_config[sync_frequency]'],
    auto_reply: data['sync_config[auto_reply]'] === '1',
    store_media: data['sync_config[store_media]'] === '1'
  };

  // Clean up old fields
  Object.keys(data).forEach(key => {
    if (key.startsWith('sync_config[')) delete data[key];
  });

  fetch('{{ route("client.chat-support.store", ["provider" => $provider]) }}', {
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
      setTimeout(() => window.location.href = '{{ route("client.chat-support-connections") }}', 1200);
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
