@extends('layouts.platform')
@section('title', 'Connect Moosend')

@push('styles')
<style>
  .provider-connect-page { max-width: 720px; margin: 0 auto; }
  .provider-hero { text-align: center; padding: 32px 24px; border-radius: 16px; background: linear-gradient(135deg, #00d4aa15 0%, #00d4aa08 100%); border: 1px solid #00d4aa30; margin-bottom: 24px; }
  .provider-hero .icon-wrap { width: 72px; height: 72px; border-radius: 20px; background: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
  .provider-hero h2 { font-size: 20px; font-weight: 700; color: #111827; margin: 0 0 6px; }
  .provider-hero p { font-size: 13px; color: #6b7280; margin: 0; line-height: 1.5; }
  .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; margin-top: 12px; }
  .status-pill.connected { background: #dcfce7; color: #16a34a; }
  .status-pill.not-connected { background: #f3f4f6; color: #9ca3af; }
  .panel-card { border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; padding: 24px; margin-bottom: 20px; }
  .panel-title { font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
  .panel-title .dot { width: 8px; height: 8px; border-radius: 50%; background: #00d4aa; }
  .feature-item { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
  .feature-item:last-child { border-bottom: none; }
  .feature-check { width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; }
  .feature-text { font-size: 13px; color: #374151; line-height: 1.5; }
  .setup-steps { display: flex; flex-direction: column; gap: 12px; }
  .step-item { display: flex; align-items: center; gap: 12px; }
  .step-num { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
  .step-text { font-size: 13px; color: #374151; margin: 0; }
  .form-group { margin-bottom: 16px; }
  .form-label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
  .form-input { width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; color: #111827; background: #fff; transition: all .15s; box-sizing: border-box; }
  .form-input:focus { outline: none; border-color: #00d4aa; box-shadow: 0 0 0 3px #00d4aa20; }
  .input-wrap { position: relative; }
  .toggle-visibility { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; }
  .form-help { font-size: 11px; color: #9ca3af; margin: 6px 0 0; }
  .connect-action-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .15s; width: 100%; justify-content: center; border: none; }
  .connect-action-btn:hover { opacity: 0.9; transform: translateY(-1px); }
  .connect-action-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
  .back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: #6b7280; text-decoration: none; padding: 6px 12px; border-radius: 8px; transition: all .15s; margin-bottom: 16px; }
  .back-link:hover { background: #f3f4f6; color: #374151; }
  .scopes-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
  .scope-tag { padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 600; background: #00d4aa15; color: #00d4aa; border: 1px solid #00d4aa30; }
  .alert { padding: 12px 16px; border-radius: 8px; font-size: 12px; margin-bottom: 16px; display: none; }
  .alert.success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; display: block; }
  .alert.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; display: block; }
  .alert.loading { background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe; display: block; }
  .connection-info { background: #f9fafb; border-radius: 10px; padding: 16px; margin-bottom: 16px; }
  .connection-info-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; font-size: 12px; }
  .connection-info-label { color: #6b7280; }
  .connection-info-value { color: #111827; font-weight: 600; }
  .disconnect-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid #fecaca; background: #fef2f2; color: #dc2626; cursor: pointer; transition: all .15s; width: 100%; justify-content: center; }
  .disconnect-btn:hover { background: #fee2e2; }
  .sync-now-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid #e5e7eb; background: #fff; color: #374151; cursor: pointer; transition: all .15s; margin-bottom: 10px; width: 100%; justify-content: center; }
  .sync-now-btn:hover { background: #f3f4f6; }
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
    <a href="{{ route('client.email-connections') }}" class="back-link">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
      </svg>
      Back to Email Providers
    </a>
    <div class="w-px h-5 bg-gray-200"></div>
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">Connect Moosend</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">
        Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
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

  <div class="provider-connect-page">

    {{-- Provider Hero --}}
    <div class="provider-hero">
      <div class="icon-wrap">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
          <rect x="3" y="4" width="18" height="16" rx="3" fill="#00d4aa"/>
          <path d="M7 9l3 3 3-3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M7 13l3 3 3-3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.6"/>
        </svg>
      </div>
      <h2>Moosend</h2>
      <p>Integrate Moosend for email marketing analytics and subscriber behavior tracking.</p>
      <div class="status-pill {{ isset($connection) && $connection ? 'connected' : 'not-connected' }}">
        <span class="w-1.5 h-1.5 rounded-full inline-block {{ isset($connection) && $connection ? 'bg-green-500' : 'bg-gray-400' }}"></span>
        {{ isset($connection) && $connection ? 'Connected' : 'Not Connected' }}
      </div>
    </div>

    {{-- Alert Messages --}}
    <div id="alertBox" class="alert"></div>

    @if(session('success'))
      <div class="alert success" style="display:block">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert error" style="display:block">{{ session('error') }}</div>
    @endif

    @if(isset($connection) && $connection)
    {{-- Connected State --}}
    <div class="panel-card">
      <div class="panel-title"><div class="dot"></div>Connection Details</div>
      <div class="connection-info">
        <div class="connection-info-row">
          <span class="connection-info-label">Platform</span>
          <span class="connection-info-value">{{ ucfirst($connection->platform) }}</span>
        </div>
        <div class="connection-info-row">
          <span class="connection-info-label">Account Name</span>
          <span class="connection-info-value">{{ $connection->account_name ?? 'Default' }}</span>
        </div>
        <div class="connection-info-row">
          <span class="connection-info-label">Status</span>
          <span class="connection-info-value" style="color: {{ $connection->status === 'active' ? '#16a34a' : '#ca8a04' }}">{{ ucfirst($connection->status) }}</span>
        </div>
        <div class="connection-info-row">
          <span class="connection-info-label">Connected On</span>
          <span class="connection-info-value">{{ $connection->connected_at ? $connection->connected_at->format('M d, Y \a\t h:i A') : 'N/A' }}</span>
        </div>
        <div class="connection-info-row">
          <span class="connection-info-label">Last Sync</span>
          <span class="connection-info-value">{{ $connection->last_sync_at ? $connection->last_sync_at->diffForHumans() : 'Never' }}</span>
        </div>
      </div>
      <button onclick="syncNow()" class="sync-now-btn" id="syncBtn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Sync Now
      </button>
      <button onclick="disconnect()" class="disconnect-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Disconnect
      </button>
    </div>
    @else
    {{-- Not Connected State --}}
    <div class="panel-card">
      <div class="panel-title"><div class="dot"></div>What You Can Sync</div>
      <div class="features-list">
          <div class="feature-item">
            <div class="feature-check" style="background: #00d4aa20; color: #00d4aa;">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <span class="feature-text">Sync mailing lists and subscriber data</span>
          </div>
          <div class="feature-item">
            <div class="feature-check" style="background: #00d4aa20; color: #00d4aa;">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <span class="feature-text">Import campaign reports and analytics</span>
          </div>
          <div class="feature-item">
            <div class="feature-check" style="background: #00d4aa20; color: #00d4aa;">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <span class="feature-text">Track email opens, clicks, and conversions</span>
          </div>
          <div class="feature-item">
            <div class="feature-check" style="background: #00d4aa20; color: #00d4aa;">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <span class="feature-text">Monitor A/B test results and performance</span>
          </div>
      </div>
    </div>

    <div class="panel-card">
      <div class="panel-title"><div class="dot"></div>How to Connect</div>
      <div class="setup-steps">
        <div class="step-item">
          <div class="step-num" style="background: #00d4aa20; color: #00d4aa;">1</div>
          <p class="step-text">Generate an API key from your Moosend account</p>
        </div>
        <div class="step-item">
          <div class="step-num" style="background: #00d4aa20; color: #00d4aa;">2</div>
          <p class="step-text">Paste the API key in the field below</p>
        </div>
        <div class="step-item">
          <div class="step-num" style="background: #00d4aa20; color: #00d4aa;">3</div>
          <p class="step-text">Click "Connect with Moosend"</p>
        </div>
        <div class="step-item">
          <div class="step-num" style="background: #00d4aa20; color: #00d4aa;">4</div>
          <p class="step-text">Data will begin syncing automatically</p>
        </div>
      </div>
    </div>

    <div class="panel-card">
      <div class="panel-title"><div class="dot"></div>Required Permissions</div>
      <p style="font-size:12px;color:#6b7280;margin:0 0 8px;">We request the following scopes:</p>
      <div class="scopes-list">
        <span class="scope-tag">mailing_lists</span>
        <span class="scope-tag">campaigns</span>
        <span class="scope-tag">subscribers</span>
        <span class="scope-tag">reports</span>
      </div>
    </div>

    <div class="panel-card">
      <div class="panel-title"><div class="dot"></div>Connect Account</div>
      <form id="apiKeyForm" onsubmit="return connectApiKey(event)">
        <div class="form-group">
          <label class="form-label">API Key</label>
          <div class="input-wrap">
            <input type="password" id="apiKeyInput" class="form-input" placeholder="Enter your Moosend API key" required>
            <button type="button" class="toggle-visibility" onclick="toggleApiKeyVisibility()">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
          <p class="form-help">Find your API key in Moosend → Account → Settings → API Keys</p>
        </div>
        <div class="form-group">
          <label class="form-label">Account Name (Optional)</label>
          <input type="text" id="accountNameInput" class="form-input" placeholder="e.g. My Moosend Account">
        </div>
        <button type="submit" class="connect-action-btn" style="background: #00d4aa; color: #ffffff;">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
          </svg>
          Connect with Moosend
        </button>
      </form>
    </div>
    @endif

  </div>

</div>{{-- /scrollable body --}}
</div>

@endsection

@push('scripts')
<script>
function showAlert(message, type) {
  const box = document.getElementById('alertBox');
  box.textContent = message;
  box.className = 'alert ' + type;
  setTimeout(() => { box.className = 'alert'; }, 5000);
}

function toggleApiKeyVisibility() {
  const input = document.getElementById('apiKeyInput');
  if (input) input.type = input.type === 'password' ? 'text' : 'password';
}

@if(isset($connection) && $connection)
// Connected state scripts
function syncNow() {
  const btn = document.getElementById('syncBtn');
  if (!btn) return;
  btn.disabled = true;
  btn.innerHTML = '<svg class="animate-spin w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Syncing...';

  fetch('{{ route("client.email-connections.sync", $connection->id) }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    }
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showAlert('Sync initiated successfully!', 'success');
    } else {
      showAlert(res.message || 'Sync failed.', 'error');
    }
  })
  .catch(err => {
    console.error(err);
    showAlert('An error occurred during sync.', 'error');
  })
  .finally(() => {
    btn.disabled = false;
    btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Sync Now';
  });
}

function disconnect() {
  if (!confirm('Are you sure you want to disconnect Moosend? All synced data will be removed.')) return;

  fetch('{{ route("client.email-connections.destroy", $connection->id) }}', {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    }
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showAlert('Disconnected successfully! Refreshing...', 'success');
      setTimeout(() => window.location.reload(), 1200);
    } else {
      showAlert(res.message || 'Disconnection failed.', 'error');
    }
  })
  .catch(err => {
    console.error(err);
    showAlert('An error occurred. Please try again.', 'error');
  });
}
@else
// Not connected state scripts
function connectApiKey(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type="submit"]');
  if (!btn) return;
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="inline-flex items-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Connecting...</span>';

  const apiKey = document.getElementById('apiKeyInput').value;
  const accountName = document.getElementById('accountNameInput').value;

  fetch('{{ route("client.email-connections.store") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      platform: 'moosend',
      api_key: apiKey,
      account_name: accountName,
      settings: JSON.stringify({ color: '#00d4aa', text_color: '#ffffff' })
    })
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showAlert('Connected successfully! Refreshing...', 'success');
      setTimeout(() => window.location.reload(), 1200);
    } else {
      showAlert(res.message || 'Connection failed. Please try again.', 'error');
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  })
  .catch(err => {
    console.error(err);
    showAlert('An error occurred. Please try again.', 'error');
    btn.disabled = false;
    btn.innerHTML = originalText;
  });

  return false;
}
@endif

document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush
