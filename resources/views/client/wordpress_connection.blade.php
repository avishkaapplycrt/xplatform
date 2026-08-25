@extends('layouts.platform')
@section('title', 'Connect WordPress')

@push('styles')
<style>
  .wp-step-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
    padding: 24px;
    transition: all .2s ease;
  }
  .wp-step-card:hover {
    border-color: #21759b;
    box-shadow: 0 4px 16px rgba(33, 117, 155, 0.08);
  }
  .wp-step-number {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #21759b;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .wp-step-number.completed {
    background: #22c55e;
  }
  .wp-step-number.pending {
    background: #e5e7eb;
    color: #9ca3af;
  }
  .code-block {
    background: #1e1e1e;
    border-radius: 10px;
    padding: 16px;
    font-family: 'Monaco', 'Menlo', 'Consolas', monospace;
    font-size: 12px;
    color: #d4d4d4;
    line-height: 1.7;
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-all;
    position: relative;
  }
  .copy-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    border: 1px solid #374151;
    background: #2d2d2d;
    color: #e5e7eb;
    cursor: pointer;
    transition: all .15s;
  }
  .copy-btn:hover {
    background: #374151;
  }
  .copy-btn.copied {
    background: #14532d;
    color: #86efac;
    border-color: #22c55e;
  }
  .verify-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
  }
  .verify-badge.success {
    background: #dcfce7;
    color: #16a34a;
  }
  .verify-badge.error {
    background: #fef2f2;
    color: #dc2626;
  }
  .verify-badge.pending {
    background: #fef3c7;
    color: #d97706;
  }
  .progress-bar {
    height: 4px;
    background: #f3f4f6;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 8px;
  }
  .progress-fill {
    height: 4px;
    border-radius: 999px;
    background: #21759b;
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
  .test-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    cursor: pointer;
    transition: all .15s;
  }
  .test-btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
  }
  .test-btn.loading {
    opacity: .6;
    pointer-events: none;
  }
  .connect-submit-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    background: #21759b;
    color: #fff;
    cursor: pointer;
    transition: all .15s;
  }
  .connect-submit-btn:hover {
    background: #1a5f7e;
  }
  .connect-submit-btn:disabled {
    opacity: .5;
    cursor: not-allowed;
  }
  .form-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    color: #374151;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
  }
  .form-input:focus {
    border-color: #21759b;
    box-shadow: 0 0 0 3px rgba(33, 117, 155, 0.1);
  }
  .form-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
  }
  .form-hint {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 4px;
  }
  .method-tab {
    padding: 8px 16px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    transition: all .15s;
  }
  .method-tab.active {
    background: #21759b;
    color: #fff;
    border-color: #21759b;
  }
  .method-tab:hover:not(.active) {
    background: #f9fafb;
  }
  .method-panel {
    display: none;
  }
  .method-panel.active {
    display: block;
  }
  .info-box {
    border: 1px solid #dbeafe;
    border-radius: 10px;
    background: #eff6ff;
    padding: 14px 16px;
  }
  .info-box-title {
    font-size: 12px;
    font-weight: 600;
    color: #1e40af;
    margin-bottom: 4px;
  }
  .info-box-text {
    font-size: 12px;
    color: #3b82f6;
    line-height: 1.5;
  }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Acme Retail';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
  $trackingCode = $trackingCode ?? null;
  $connection = $connection ?? null;
@endphp

<div class="flex flex-col h-full overflow-hidden bg-white">

{{-- HEADER --}}
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
      <svg width="20" height="20" viewBox="0 0 24 24" fill="#21759b">
        <circle cx="12" cy="12" r="10"/>
        <path d="M3.009 12c0 3.559 2.068 6.634 5.067 8.092L4.258 8.014A9.958 9.958 0 003.009 12zm18.013.335c0-2.908-1.755-5.462-4.41-6.878-.264-.15-.528-.3-.793-.45-.264-.15-.528-.3-.793-.45 1.584 2.558 2.377 5.116 2.377 7.675 0 1.889-.528 3.778-1.584 5.667l-.264.45c-.264.45-.528.9-.793 1.35 2.377-1.35 3.961-3.778 4.225-6.364h.028zm-5.934 7.227c1.056-.15 2.112-.6 2.905-1.2.793-.6 1.32-1.35 1.848-2.25.528-.9.793-1.95.793-3.008 0-1.05-.264-2.1-.793-3.008-.528-.9-1.056-1.65-1.848-2.25-.793-.6-1.849-1.05-2.905-1.2-1.056-.15-2.112-.15-3.169 0l3.697 10.916h.472zm-2.377-3.008L11.76 8.014c-.264 0-.528-.15-.793-.15-.264 0-.528.15-.793.15L6.76 19.562c.528.15 1.056.15 1.584.15.528 0 1.056 0 1.584-.15.528-.15 1.056-.3 1.584-.6.528-.3.793-.6 1.056-1.05.264-.45.528-.9.528-1.35.264-.45.264-.9.264-1.35 0-.45-.264-.9-.264-1.35-.264-.45-.528-.75-.793-1.05z" fill="#fff"/>
      </svg>
      <div>
        <h1 class="text-[16px] font-semibold text-gray-900">Connect WordPress</h1>
        <p class="text-[11px] text-gray-500 mt-0.5">
          Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
          <span class="ml-2 inline-flex items-center gap-1 text-green-600 font-medium text-[10px]">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Live
          </span>
        </p>
      </div>
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

  {{-- Progress Header --}}
  <div class="mb-6">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-[16px] font-bold text-gray-900">Connection Setup</h2>
      <span id="progressText" class="text-[12px] font-semibold text-gray-500">Step 1 of 3</span>
    </div>
    <div class="progress-bar">
      <div id="progressFill" class="progress-fill" style="width: 33%"></div>
    </div>
  </div>

  {{-- Step 1: Enter Site Details --}}
  <div id="step-1" class="wp-step-card mb-4">
    <div class="flex items-start gap-4">
      <div id="step1-badge" class="wp-step-number">1</div>
      <div class="flex-1">
        <h3 class="text-[14px] font-bold text-gray-900 mb-1">Enter Your WordPress Site Details</h3>
        <p class="text-[12px] text-gray-500 mb-4">Provide your website URL and a name to identify this connection.</p>

        <form id="wpConnectForm" onsubmit="submitWpConnection(event)">
          <input type="hidden" name="platform" value="wordpress">

          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <label class="form-label">Site URL <span class="text-red-500">*</span></label>
              <input type="url" name="site_url" class="form-input" placeholder="https://example.com" required
                     value="{{ $connection?->site_url ?? '' }}">
              <p class="form-hint">The full URL of your WordPress site</p>
            </div>
            <div>
              <label class="form-label">Site Name <span class="text-gray-400 font-normal">(optional)</span></label>
              <input type="text" name="site_name" class="form-input" placeholder="My WordPress Site"
                     value="{{ $connection?->site_name ?? '' }}">
              <p class="form-hint">A friendly name for this connection</p>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Connection Method</label>
            <div class="flex gap-2 mb-3">
              <button type="button" class="method-tab active" onclick="switchMethod('plugin')">Plugin</button>
              <button type="button" class="method-tab" onclick="switchMethod('manual')">Manual Code</button>
            </div>

            {{-- Plugin Method --}}
            <div id="method-plugin" class="method-panel active">
              <div class="info-box mb-3">
                <p class="info-box-title">Recommended: Scorementor WordPress Plugin</p>
                <p class="info-box-text">Install our official plugin from the WordPress plugin directory for the easiest setup. The plugin handles all tracking automatically.</p>
              </div>
            </div>

            {{-- Manual Method --}}
            <div id="method-manual" class="method-panel">
              <div class="info-box mb-3">
                <p class="info-box-title">Manual Integration</p>
                <p class="info-box-text">Add the tracking code directly to your theme's header.php file or use a code injection plugin.</p>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button type="submit" id="submitBtn" class="connect-submit-btn">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
              </svg>
              {{ $connection ? 'Update Connection' : 'Connect Website' }}
            </button>
            <span id="formStatus" class="verify-badge pending" style="display:none">
              <span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span>
              <span id="formStatusText">Processing...</span>
            </span>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Step 2: Install Tracking Code --}}
  <div id="step-2" class="wp-step-card mb-4" style="opacity: .5; pointer-events: none;">
    <div class="flex items-start gap-4">
      <div id="step2-badge" class="wp-step-number pending">2</div>
      <div class="flex-1">
        <h3 class="text-[14px] font-bold text-gray-900 mb-1">Install Tracking Code</h3>
        <p class="text-[12px] text-gray-500 mb-4">Add this tracking code to your WordPress site to start collecting events.</p>

        {{-- Plugin Tab --}}
        <div id="install-plugin" class="method-panel active">
          <div class="info-box mb-3">
            <p class="info-box-title">Plugin Installation Steps</p>
            <p class="info-box-text">1. Go to your WordPress Admin → Plugins → Add New<br>
            2. Search for "Scorementor Analytics"<br>
            3. Click Install Now → Activate<br>
            4. Go to Settings → Scorementor and enter your tracking code:</p>
          </div>
          <div class="code-block mb-3">
            <button class="copy-btn" onclick="copyCode('trackingCodePlugin')">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
              </svg>
              Copy
            </button>
            <code id="trackingCodePlugin">{{ $trackingCode ?? 'Generate a connection first...' }}</code>
          </div>
        </div>

        {{-- Manual Tab --}}
        <div id="install-manual" class="method-panel">
          <div class="info-box mb-3">
            <p class="info-box-title">Manual Code Installation</p>
            <p class="info-box-text">Copy and paste this code into your WordPress theme's header.php file, just before the closing &lt;/head&gt; tag.</p>
          </div>
          <div class="code-block mb-3">
            <button class="copy-btn" onclick="copyCode('embedCodeManual')">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
              </svg>
              Copy
            </button>
            <code id="embedCodeManual">{{ $embedCode ?? 'Generate a connection first...' }}</code>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <button id="verifyBtn" onclick="verifyConnection()" class="test-btn" disabled>
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Verify Installation
          </button>
          <span id="verifyStatus" class="verify-badge pending" style="display:none">
            <span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span>
            Checking...
          </span>
        </div>
      </div>
    </div>
  </div>

  {{-- Step 3: Connection Active --}}
  <div id="step-3" class="wp-step-card mb-4" style="opacity: .5; pointer-events: none;">
    <div class="flex items-start gap-4">
      <div id="step3-badge" class="wp-step-number pending">3</div>
      <div class="flex-1">
        <h3 class="text-[14px] font-bold text-gray-900 mb-1">Connection Active</h3>
        <p class="text-[12px] text-gray-500 mb-4">Your WordPress site is now connected and tracking events.</p>

        <div class="grid grid-cols-3 gap-3 mb-4">
          <div class="text-center p-4 rounded-xl border border-gray-200 bg-gray-50">
            <p class="text-[22px] font-bold text-gray-900" id="eventsToday">0</p>
            <p class="text-[11px] text-gray-500 mt-1">Events Today</p>
          </div>
          <div class="text-center p-4 rounded-xl border border-gray-200 bg-gray-50">
            <p class="text-[22px] font-bold text-green-600" id="connectionStatus">Active</p>
            <p class="text-[11px] text-gray-500 mt-1">Status</p>
          </div>
          <div class="text-center p-4 rounded-xl border border-gray-200 bg-gray-50">
            <p class="text-[22px] font-bold text-blue-600" id="lastSync">Just now</p>
            <p class="text-[11px] text-gray-500 mt-1">Last Sync</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <a href="{{ route('client.data-collection') }}" class="connect-submit-btn" style="text-decoration:none; background:#374151;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            View Analytics
          </a>
          <button onclick="disconnectWp()" class="test-btn" style="color:#dc2626; border-color:#fecaca;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
            Disconnect
          </button>
        </div>
      </div>
    </div>
  </div>

</div>{{-- /scrollable body --}}
</div>

@endsection

@push('scripts')
<script>
let currentConnectionId = null;
let currentMethod = 'plugin';

function switchMethod(method) {
  currentMethod = method;
  document.querySelectorAll('.method-tab').forEach(t => t.classList.remove('active'));
  event.target.classList.add('active');

  document.querySelectorAll('.method-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('method-' + method).classList.add('active');
  document.getElementById('install-' + method).classList.add('active');
}

function updateProgress(step) {
  const fill = document.getElementById('progressFill');
  const text = document.getElementById('progressText');

  if (step === 1) {
    fill.style.width = '33%';
    text.textContent = 'Step 1 of 3';
  } else if (step === 2) {
    fill.style.width = '66%';
    text.textContent = 'Step 2 of 3';
    document.getElementById('step-2').style.opacity = '1';
    document.getElementById('step-2').style.pointerEvents = 'auto';
    document.getElementById('step1-badge').classList.add('completed');
    document.getElementById('step1-badge').innerHTML = '✓';
    document.getElementById('step2-badge').classList.remove('pending');
    document.getElementById('verifyBtn').disabled = false;
  } else if (step === 3) {
    fill.style.width = '100%';
    text.textContent = 'Step 3 of 3';
    document.getElementById('step-3').style.opacity = '1';
    document.getElementById('step-3').style.pointerEvents = 'auto';
    document.getElementById('step2-badge').classList.add('completed');
    document.getElementById('step2-badge').innerHTML = '✓';
    document.getElementById('step3-badge').classList.remove('pending');
  }
}

function submitWpConnection(e) {
  e.preventDefault();
  const btn = document.getElementById('submitBtn');
  const status = document.getElementById('formStatus');
  const statusText = document.getElementById('formStatusText');

  btn.disabled = true;
  status.style.display = 'inline-flex';
  statusText.textContent = 'Connecting...';
  status.className = 'verify-badge pending';

  const formData = new FormData(e.target);
  const data = Object.fromEntries(formData.entries());

  fetch('{{ route("client.website-connections.store") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify(data)
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      currentConnectionId = res.connection.id;
      document.getElementById('trackingCodePlugin').textContent = res.tracking_code;
      document.getElementById('embedCodeManual').textContent = res.embed_code || res.tracking_code;
      statusText.textContent = 'Connected!';
      status.className = 'verify-badge success';
      updateProgress(2);
    } else {
      statusText.textContent = res.message || 'Failed';
      status.className = 'verify-badge error';
    }
  })
  .catch(err => {
    console.error(err);
    statusText.textContent = 'Error occurred';
    status.className = 'verify-badge error';
  })
  .finally(() => {
    btn.disabled = false;
  });
}

function verifyConnection() {
  const btn = document.getElementById('verifyBtn');
  const status = document.getElementById('verifyStatus');

  if (!currentConnectionId) return;

  btn.classList.add('loading');
  status.style.display = 'inline-flex';
  status.className = 'verify-badge pending';
  status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span> Checking...';

  fetch('{{ url("/app/website-connections") }}/' + currentConnectionId + '/verify')
    .then(r => r.json())
    .then(res => {
      if (res.healthy) {
        status.className = 'verify-badge success';
        status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span> Verified!';
        updateProgress(3);
      } else {
        status.className = 'verify-badge error';
        status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span> Not detected';
      }
    })
    .catch(err => {
      console.error(err);
      status.className = 'verify-badge error';
      status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span> Check failed';
    })
    .finally(() => {
      btn.classList.remove('loading');
    });
}

function disconnectWp() {
  if (!currentConnectionId) return;
  if (!confirm('Are you sure you want to disconnect this WordPress site? All tracking will stop.')) return;

  fetch('{{ url("/app/website-connections") }}/' + currentConnectionId, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      alert('WordPress site disconnected successfully.');
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

function copyCode(elementId) {
  const code = document.getElementById(elementId).textContent;
  navigator.clipboard.writeText(code).then(() => {
    const btn = event.target.closest('.copy-btn');
    const original = btn.innerHTML;
    btn.classList.add('copied');
    btn.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Copied!';
    setTimeout(() => {
      btn.classList.remove('copied');
      btn.innerHTML = original;
    }, 2000);
  });
}

// If connection exists on page load, auto-advance to step 3
@if($connection)
document.addEventListener('DOMContentLoaded', function() {
  currentConnectionId = {{ $connection->id }};
  updateProgress(2);
  setTimeout(() => updateProgress(3), 300);
});
@endif

document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush
