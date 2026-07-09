@extends('layouts.platform')
@section('title', $gatewayConfig['name'] . ' Connection')

@push('styles')
<style>
  .pg-form-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
    padding: 28px;
  }
  .pg-form-group {
    margin-bottom: 20px;
  }
  .pg-form-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
  }
  .pg-form-label .required {
    color: #ef4444;
    margin-left: 2px;
  }
  .pg-form-input {
    width: 100%;
    padding: 10px 14px;
    font-size: 13px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    color: #111827;
    transition: all .15s;
    box-sizing: border-box;
  }
  .pg-form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.08);
  }
  .pg-form-input::placeholder {
    color: #9ca3af;
  }
  .pg-form-select {
    width: 100%;
    padding: 10px 14px;
    font-size: 13px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    color: #111827;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 36px;
  }
  .pg-form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.08);
  }
  .pg-toggle-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .pg-toggle {
    position: relative;
    width: 40px;
    height: 22px;
    border-radius: 11px;
    background: #e5e7eb;
    cursor: pointer;
    transition: background .2s;
    flex-shrink: 0;
  }
  .pg-toggle.active {
    background: #10b981;
  }
  .pg-toggle::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    transition: transform .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
  }
  .pg-toggle.active::after {
    transform: translateX(18px);
  }
  .pg-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all .15s;
  }
  .pg-btn-primary {
    background: #111827;
    color: #fff;
  }
  .pg-btn-primary:hover {
    background: #374151;
  }
  .pg-btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
  }
  .pg-btn-secondary:hover {
    background: #e5e7eb;
  }
  .pg-btn-success {
    background: #10b981;
    color: #fff;
  }
  .pg-btn-success:hover {
    background: #059669;
  }
  .pg-webhook-box {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .pg-webhook-box code {
    font-size: 12px;
    font-family: 'SF Mono', Monaco, monospace;
    color: #0369a1;
    background: #e0f2fe;
    padding: 4px 8px;
    border-radius: 4px;
    flex: 1;
    word-break: break-all;
  }
  .pg-section-title {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .pg-section-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
  }
  .pg-alert {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
  }
  .pg-alert-info {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
  }
  .pg-alert-success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #15803d;
  }
  .pg-alert-warning {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #b45309;
  }
  .pg-test-result {
    display: none;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    margin-top: 10px;
  }
  .pg-test-result.success {
    display: block;
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
  }
  .pg-test-result.error {
    display: block;
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
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
      <a href="{{ route('client.payment-gateway-connections.index') }}"
         class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
         title="Back">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <div>
        <h1 class="text-[16px] font-semibold text-gray-900">{{ $gatewayConfig['name'] }}</h1>
        <p class="text-[11px] text-gray-500 mt-0.5">{{ $gatewayConfig['description'] }}</p>
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

    <div style="max-width:720px;margin:0 auto">

      {{-- Status Alert --}}
      @if($connection && $connection->is_connected)
      <div class="pg-alert pg-alert-success">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><strong>{{ $gatewayConfig['name'] }}</strong> is connected and {{ $connection->is_active ? 'active' : 'inactive' }}.</span>
      </div>
      @else
      <div class="pg-alert pg-alert-info">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>Connect your <strong>{{ $gatewayConfig['name'] }}</strong> account to start syncing payment data.</span>
      </div>
      @endif

      {{-- Connection Form --}}
      <div class="pg-form-card">
        <form method="POST" action="{{ route('client.payment-gateway-connections.connect', $gateway) }}" id="connectionForm">
          @csrf

          {{-- API Credentials Section --}}
          <div class="pg-section-title">
            <div class="pg-section-dot" style="background:{{ $gatewayConfig['color'] }}"></div>
            API Credentials
          </div>

          @foreach($gatewayConfig['fields'] as $fieldKey => $fieldConfig)
          <div class="pg-form-group">
            <label class="pg-form-label">
              {{ $fieldConfig['label'] }}
              @if($fieldConfig['required'])
                <span class="required">*</span>
              @endif
            </label>
            @if($fieldConfig['type'] === 'password')
              <input type="password"
                     name="{{ $fieldKey }}"
                     class="pg-form-input"
                     placeholder="Enter {{ strtolower($fieldConfig['label']) }}"
                     {{ $fieldConfig['required'] ? 'required' : '' }}>
            @elseif($fieldConfig['type'] === 'url')
              <input type="url"
                     name="{{ $fieldKey }}"
                     class="pg-form-input"
                     placeholder="{{ $fieldConfig['placeholder'] ?? 'https://...' }}"
                     {{ $fieldConfig['required'] ? 'required' : '' }}>
            @else
              <input type="text"
                     name="{{ $fieldKey }}"
                     class="pg-form-input"
                     placeholder="{{ $fieldConfig['placeholder'] ?? 'Enter ' . strtolower($fieldConfig['label']) }}"
                     {{ $fieldConfig['required'] ? 'required' : '' }}>
            @endif
          </div>
          @endforeach

          {{-- Settings Section --}}
          <div class="pg-section-title" style="margin-top:28px">
            <div class="pg-section-dot" style="background:#3b82f6"></div>
            Configuration Settings
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="pg-form-group">
              <label class="pg-form-label">Environment <span class="required">*</span></label>
              <select name="environment" class="pg-form-select" required>
                <option value="sandbox" {{ $connection && $connection->environment === 'sandbox' ? 'selected' : '' }}>Sandbox (Test)</option>
                <option value="production" {{ $connection && $connection->environment === 'production' ? 'selected' : '' }}>Production (Live)</option>
              </select>
            </div>
            <div class="pg-form-group">
              <label class="pg-form-label">Currency <span class="required">*</span></label>
              <select name="currency" class="pg-form-select" required>
                <option value="USD" {{ $connection && $connection->currency === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                <option value="EUR" {{ $connection && $connection->currency === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                <option value="GBP" {{ $connection && $connection->currency === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                <option value="AUD" {{ $connection && $connection->currency === 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                <option value="CAD" {{ $connection && $connection->currency === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                <option value="INR" {{ $connection && $connection->currency === 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                <option value="SGD" {{ $connection && $connection->currency === 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar</option>
              </select>
            </div>
          </div>

          <div class="pg-form-group">
            <label class="pg-form-label">Activate Connection</label>
            <div class="pg-toggle-wrap">
              <div class="pg-toggle {{ $connection && $connection->is_active ? 'active' : '' }}" id="activeToggle" onclick="toggleActive()"></div>
              <span style="font-size:12px;color:#6b7280" id="activeLabel">{{ $connection && $connection->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <input type="hidden" name="is_active" id="isActiveInput" value="{{ $connection && $connection->is_active ? '1' : '0' }}">
          </div>

          {{-- Webhook Section --}}
          @if($gatewayConfig['supports_webhook'] && $webhookUrl)
          <div class="pg-section-title" style="margin-top:28px">
            <div class="pg-section-dot" style="background:#10b981"></div>
            Webhook Configuration
          </div>
          <div class="pg-webhook-box">
            <svg width="16" height="16" fill="none" stroke="#0369a1" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
            <code>{{ $webhookUrl }}</code>
            <button type="button" onclick="copyWebhook()" style="background:none;border:none;cursor:pointer;padding:4px;color:#0369a1" title="Copy URL">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
            </button>
          </div>
          <p style="font-size:11px;color:#9ca3af;margin-top:8px">Copy this URL and paste it into your {{ $gatewayConfig['name'] }} webhook settings to receive real-time payment events.</p>
          @endif

          {{-- Action Buttons --}}
          <div style="display:flex;gap:10px;margin-top:28px;padding-top:20px;border-top:1px solid #f3f4f6">
            <button type="submit" class="pg-btn pg-btn-primary">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              {{ $connection && $connection->is_connected ? 'Update Connection' : 'Connect ' . $gatewayConfig['name'] }}
            </button>
            <button type="button" class="pg-btn pg-btn-secondary" onclick="testConnection()">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Test Connection
            </button>
            <a href="{{ route('client.payment-gateway-connections.index') }}" class="pg-btn pg-btn-secondary" style="margin-left:auto">
              Cancel
            </a>
          </div>

          <div id="testResult" class="pg-test-result"></div>
        </form>
      </div>

      {{-- Danger Zone --}}
      @if($connection && $connection->is_connected)
      <div class="pg-form-card" style="margin-top:16px;border-color:#fecaca;background:#fef2f2">
        <div class="pg-section-title">
          <div class="pg-section-dot" style="background:#ef4444"></div>
          Danger Zone
        </div>
        <p style="font-size:12px;color:#7f1d1d;margin-bottom:14px">Disconnecting will stop all payment data sync. This action cannot be undone.</p>
        <div style="display:flex;gap:10px">
          <form method="POST" action="{{ route('client.payment-gateway-connections.disconnect', $gateway) }}" style="display:inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="pg-btn" style="background:#ef4444;color:#fff" onclick="return confirm('Are you sure you want to disconnect {{ $gatewayConfig['name'] }}?')">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
              Disconnect Gateway
            </button>
          </form>
          <form method="POST" action="{{ route('client.payment-gateway-connections.destroy', $gateway) }}" style="display:inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="pg-btn" style="background:#fff;color:#ef4444;border:1px solid #fecaca" onclick="return confirm('Remove all {{ $gatewayConfig['name'] }} configuration? This cannot be undone.')">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Remove Configuration
            </button>
          </form>
        </div>
      </div>
      @endif

    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function toggleActive() {
  const toggle = document.getElementById('activeToggle');
  const input = document.getElementById('isActiveInput');
  const label = document.getElementById('activeLabel');

  toggle.classList.toggle('active');
  const isActive = toggle.classList.contains('active');
  input.value = isActive ? '1' : '0';
  label.textContent = isActive ? 'Active' : 'Inactive';
}

function copyWebhook() {
  const code = document.querySelector('.pg-webhook-box code');
  if (code) {
    navigator.clipboard.writeText(code.textContent).then(function() {
      alert('Webhook URL copied to clipboard!');
    });
  }
}

function testConnection() {
  const resultDiv = document.getElementById('testResult');
  resultDiv.className = 'pg-test-result';
  resultDiv.textContent = 'Testing connection...';
  resultDiv.style.display = 'block';

  fetch('{{ route('client.payment-gateway-connections.test', $gateway) }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    }
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      resultDiv.className = 'pg-test-result success';
      resultDiv.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' + data.message;
    } else {
      resultDiv.className = 'pg-test-result error';
      resultDiv.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>' + data.message;
    }
  })
  .catch(function() {
    resultDiv.className = 'pg-test-result error';
    resultDiv.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>Connection test failed. Please check your credentials.';
  });
}

document.addEventListener('click', function(e) {
  var wrap = document.getElementById('l1AvatarWrap');
  var drop = document.getElementById('l1Dropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
@endpush
