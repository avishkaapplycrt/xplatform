@extends('layouts.platform')
@section('title', 'Connect Webflow')

@push('styles')
<style>
  .step-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
    padding: 24px;
    transition: all .2s ease;
  }
  .step-card:hover {
    border-color: #4353ff;
    box-shadow: 0 4px 16px #4353ff14;
  }
  .step-number {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #4353ff;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .step-number.completed {
    background: #22c55e;
  }
  .step-number.pending {
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
    max-height: 200px;
    overflow-y: auto;
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
    background: #4353ff;
    color: #fff;
    cursor: pointer;
    transition: all .15s;
  }
  .connect-submit-btn:hover {
    filter: brightness(0.9);
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
    border-color: #4353ff;
    box-shadow: 0 0 0 3px #4353ff1a;
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
    background: #4353ff;
    color: #fff;
    border-color: #4353ff;
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
  .install-step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f3f4f6;
  }
  .install-step:last-child {
    border-bottom: none;
  }
  .install-step-num {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: #4353ff15;
    color: #4353ff;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
  }
  .install-step-text {
    font-size: 13px;
    color: #374151;
    line-height: 1.5;
  }
  .step-disabled {
    opacity: 0.5;
    pointer-events: none;
    filter: grayscale(0.8);
  }
  .step-enabled {
    opacity: 1;
    pointer-events: auto;
    filter: none;
  }
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
        <h1 class="text-[16px] font-semibold text-gray-900">Connect Webflow</h1>
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

<div class="flex-1 overflow-y-auto px-5 py-4">

  <div class="mb-6">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-[16px] font-bold text-gray-900">Connection Setup</h2>
      <span id="progressText" class="text-[12px] font-semibold text-gray-500">Step 1 of 3</span>
    </div>
    <div class="progress-bar">
      <div id="progressFill" class="progress-fill" style="width: 33%"></div>
    </div>
  </div>

  <div id="step-1" class="step-card mb-4">
    <div class="flex items-start gap-4">
      <div id="step1-badge" class="step-number">1</div>
      <div class="flex-1">
        <h3 class="text-[14px] font-bold text-gray-900 mb-1">Enter Your Webflow Site Details</h3>
        <p class="text-[12px] text-gray-500 mb-4">Provide your Webflow site URL and add the tracking code via Custom Code.</p>

        <form id="connectForm" onsubmit="submitConnection(event)">
          <input type="hidden" id="modalPlatform" name="platform" value="webflow">

          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <label class="form-label">Site URL <span class="text-red-500">*</span></label>
              <input type="url" name="site_url" class="form-input" placeholder="https://example.com" required
                     value="{{ $connection?->site_url ?? '' }}">
              <p class="form-hint">The full URL of your Webflow site</p>
            </div>
            <div>
              <label class="form-label">Site Name <span class="text-gray-400 font-normal">(optional)</span></label>
              <input type="text" name="site_name" class="form-input" placeholder="My Webflow Site"
                     value="{{ $connection?->site_name ?? '' }}">
              <p class="form-hint">A friendly name for this connection</p>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Connection Method</label>
            <div class="flex gap-2 mb-3">
              <button type="button" class="method-tab active" onclick="switchMethod('app')">Project Settings</button>
              <button type="button" class="method-tab" onclick="switchMethod('manual')">Page-Level Code</button>
            </div>

            <div id="method-app" class="method-panel active">
              <div class="info-box mb-3">
                <p class="info-box-title">Project Settings</p>
                <p class="info-box-text">Add the tracking code in your Webflow Project Settings for all pages.</p>
              </div>
              <div class="mt-3">
              <div class="install-step"><div class="install-step-num">1</div><div class="install-step-text">Go to your Webflow Dashboard → Project Settings</div></div>
              <div class="install-step"><div class="install-step-num">2</div><div class="install-step-text">Navigate to the Custom Code tab</div></div>
              <div class="install-step"><div class="install-step-num">3</div><div class="install-step-text">Paste the tracking code in the Head Code section</div></div>
              <div class="install-step"><div class="install-step-num">4</div><div class="install-step-text">Save changes and publish your site</div></div>
            </div>
            </div>

            <div id="method-manual" class="method-panel">
              <div class="info-box mb-3">
                <p class="info-box-title">Page-Level Code</p>
                <p class="info-box-text">Add tracking to specific pages only using Webflow page settings.</p>
              </div>
              <div class="mt-3">
              <div class="install-step"><div class="install-step-num">1</div><div class="install-step-text">Open the specific page in the Webflow Designer</div></div>
              <div class="install-step"><div class="install-step-num">2</div><div class="install-step-text">Click the page settings (gear icon)</div></div>
              <div class="install-step"><div class="install-step-num">3</div><div class="install-step-text">Go to Custom Code → Inside &lt;head&gt; tag</div></div>
              <div class="install-step"><div class="install-step-num">4</div><div class="install-step-text">Paste the code and save</div></div>
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

  <div id="step-2" class="step-card mb-4">
    <div class="flex items-start gap-4">
      <div id="step2-badge" class="step-number pending">2</div>
      <div class="flex-1">
        <h3 class="text-[14px] font-bold text-gray-900 mb-1">Install Tracking Code</h3>
        <p class="text-[12px] text-gray-500 mb-4">Add this tracking code to your Webflow site to start collecting events.</p>

        <div id="install-app" class="method-panel active">
          <div class="info-box mb-3">
            <p class="info-box-title">Project Settings Installation</p>
            <p class="info-box-text">Follow the steps below to complete the integration.</p>
          </div>
          <div class="code-block mb-3">
            <button class="copy-btn" onclick="copyCode('trackingCodeApp')">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
              </svg>
              Copy
            </button>
            <code id="trackingCodeApp">{{ $trackingCode ?? 'Generate a connection first...' }}</code>
          </div>
        </div>

        <div id="install-manual" class="method-panel">
          <div class="info-box mb-3">
            <p class="info-box-title">Manual Code Installation</p>
            <p class="info-box-text">Copy and paste this code into your site's header.</p>
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
          <button id="verifyBtn" onclick="verifyConnection()" class="test-btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Verify Installation
          </button>
          <button id="forceVerifyBtn" onclick="forceVerifyConnection()" class="test-btn" style="display:none; border-color:#dbeafe; color:#3b82f6;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Force Verify
          </button>
          <span id="verifyStatus" class="verify-badge pending" style="display:none">
            <span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span>
            Checking...
          </span>
        </div>
      </div>
    </div>
  </div>

  <div id="step-3" class="step-card mb-4">
    <div class="flex items-start gap-4">
      <div id="step3-badge" class="step-number pending">3</div>
      <div class="flex-1">
        <h3 class="text-[14px] font-bold text-gray-900 mb-1">Connection Active</h3>
        <p class="text-[12px] text-gray-500 mb-4">Your Webflow site is now connected and tracking events.</p>

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
          <a href="{{ $connection ? route('client.website-connections.analytics', $connection->id) : route('client.data-collection') }}" class="connect-submit-btn" style="text-decoration:none; background:#374151;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            View Analytics
          </a>
          <button onclick="disconnectSite()" class="test-btn" style="color:#dc2626; border-color:#fecaca;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
            Disconnect
          </button>
        </div>
      </div>
    </div>
  </div>

</div>
</div>

@endsection

@push('scripts')
<script>
let currentConnectionId = {{ $connection?->id ?? 'null' }};
let currentMethod = 'app';

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
  const step2 = document.getElementById('step-2');
  const step3 = document.getElementById('step-3');

  if (step === 1) {
    fill.style.width = '33%';
    text.textContent = 'Step 1 of 3';
    if (step2) step2.classList.add('step-disabled');
    if (step3) step3.classList.add('step-disabled');
  } else if (step === 2) {
    fill.style.width = '66%';
    text.textContent = 'Step 2 of 3';
    if (step2) {
      step2.classList.remove('step-disabled');
      step2.classList.add('step-enabled');
    }
    document.getElementById('step1-badge').classList.add('completed');
    document.getElementById('step1-badge').innerHTML = '&#10003;';
    document.getElementById('step2-badge').classList.remove('pending');
    const verifyBtn = document.getElementById('verifyBtn');
    if (verifyBtn) verifyBtn.disabled = false;
  } else if (step === 3) {
    fill.style.width = '100%';
    text.textContent = 'Step 3 of 3';
    if (step2) {
      step2.classList.remove('step-disabled');
      step2.classList.add('step-enabled');
    }
    if (step3) {
      step3.classList.remove('step-disabled');
      step3.classList.add('step-enabled');
    }
    document.getElementById('step2-badge').classList.add('completed');
    document.getElementById('step2-badge').innerHTML = '&#10003;';
    document.getElementById('step3-badge').classList.remove('pending');
    const verifyBtn = document.getElementById('verifyBtn');
    if (verifyBtn) verifyBtn.disabled = false;
  }
}

function submitConnection(e) {
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
      document.getElementById('trackingCodeApp').textContent = res.tracking_code;
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

function disconnectSite() {
  if (!currentConnectionId) return;
  if (!confirm('Are you sure you want to disconnect this Webflow site? All tracking will stop.')) return;

  fetch('{{ url("/app/website-connections") }}/' + currentConnectionId, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      alert('Webflow site disconnected successfully.');
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

// If connection exists on page load, auto-advance
@if($connection)
document.addEventListener('DOMContentLoaded', function() {
  const step2 = document.getElementById('step-2');
  const step3 = document.getElementById('step-3');
  if (step2) step2.classList.add('step-disabled');
  if (step3) step3.classList.add('step-disabled');
  updateProgress(2);
  setTimeout(() => updateProgress(3), 300);
});
@endif

document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});

(function() {
  const step2 = document.getElementById('step-2');
  const step3 = document.getElementById('step-3');
  if (step2) step2.classList.add('step-disabled');
  if (step3) step3.classList.add('step-disabled');
  if (currentConnectionId && currentConnectionId !== 'null') {
    const verifyBtn = document.getElementById('verifyBtn');
    if (verifyBtn) verifyBtn.disabled = false;
  }
})();
</script>
@endpush
