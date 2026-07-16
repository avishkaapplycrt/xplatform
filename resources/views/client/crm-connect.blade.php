{{-- resources/views/client/crm-connect.blade.php --}}
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
      <a href="{{ route('client.crm-connections') }}"
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
            @case('salesforce')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 3a3 3 0 00-3 3v12a3 3 0 003 3 3 3 0 003-3 3 3 0 00-3-3H6a3 3 0 00-3 3 3 3 0 003 3 3 3 0 003-3V6a3 3 0 00-3-3 3 3 0 00-3 3 3 3 0 003 3h12a3 3 0 003-3 3 3 0 00-3-3z"/>
              </svg>
              @break
            @case('hubspot')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/><path d="M12 2v4m0 12v4m10-10h-4M6 12H2m15.07-7.07l-2.83 2.83M9.76 14.24l-2.83 2.83m12.14 0l-2.83-2.83M9.76 9.76L6.93 6.93"/>
              </svg>
              @break
            @case('zoho')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
              </svg>
              @break
            @case('pipedrive')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
              </svg>
              @break
            @case('monday')
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
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

      {{-- Connection Form --}}
      <form id="crmConnectForm" onsubmit="return handleSubmit(event)">
        @csrf

        {{-- Connection Name --}}
        <div class="form-group">
          <label class="form-label">Connection Name</label>
          <input type="text" name="connection_name" class="form-input"
                 value="{{ $existing?->connection_name ?? $meta['name'] . ' Connection' }}"
                 placeholder="e.g., Main Salesforce Account" required>
          <p class="form-hint">Give this connection a recognizable name for easy identification.</p>
        </div>

        {{-- Provider-Specific Fields --}}
        @switch($provider)
          @case('salesforce')
            <div class="form-group">
              <label class="form-label">Instance URL</label>
              <input type="url" name="instance_url" class="form-input"
                     value="{{ $existing?->instance_url ?? '' }}"
                     placeholder="https://yourinstance.salesforce.com" required>
              <p class="form-hint">Your Salesforce instance URL (e.g., https://company.my.salesforce.com)</p>
            </div>
            <div class="form-group">
              <label class="form-label">Access Token</label>
              <input type="password" name="access_token" class="form-input"
                     placeholder="Enter your Salesforce access token" required>
              <p class="form-hint">Generate from Setup → App Manager → Connected Apps</p>
            </div>
            <div class="form-group">
              <label class="form-label">Refresh Token (Optional)</label>
              <input type="password" name="refresh_token" class="form-input"
                     placeholder="Enter refresh token for auto-renewal">
              <p class="form-hint">Required for automatic token refresh</p>
            </div>
            @break

          @case('hubspot')
            <div class="form-group">
              <label class="form-label">Private App Access Token</label>
              <input type="password" name="api_key" class="form-input"
                     placeholder="Enter your HubSpot Private App token" required>
              <p class="form-hint">Create in Settings → Integrations → Private Apps</p>
            </div>
            <div class="form-group">
              <label class="form-label">Portal ID (Optional)</label>
              <input type="text" name="portal_id" class="form-input"
                     value="{{ $existing?->portal_id ?? '' }}"
                     placeholder="Your HubSpot Portal ID">
              <p class="form-hint">Found in your HubSpot account settings</p>
            </div>
            @break

          @case('zoho')
            <div class="form-group">
              <label class="form-label">Client ID / API Key</label>
              <input type="password" name="api_key" class="form-input"
                     placeholder="Enter your Zoho Client ID" required>
              <p class="form-hint">From Zoho API Console → Self Client</p>
            </div>
            <div class="form-group">
              <label class="form-label">Client Secret</label>
              <input type="password" name="api_secret" class="form-input"
                     placeholder="Enter your Zoho Client Secret" required>
              <p class="form-hint">Keep this secure - never share your client secret</p>
            </div>
            @break

          @case('pipedrive')
            <div class="form-group">
              <label class="form-label">API Token</label>
              <input type="password" name="api_key" class="form-input"
                     placeholder="Enter your Pipedrive API token" required>
              <p class="form-hint">Found in Settings → Personal Preferences → API</p>
            </div>
            @break

          @case('monday')
            <div class="form-group">
              <label class="form-label">Client ID</label>
              <input type="password" name="api_key" class="form-input"
                     placeholder="Enter your Monday.com Client ID" required>
              <p class="form-hint">From Monday.com Developers → My Apps → OAuth → Client ID</p>
            </div>
            <div class="form-group">
              <label class="form-label">Client Secret</label>
              <input type="password" name="api_secret" class="form-input"
                     placeholder="Enter your Monday.com Client Secret" required>
              <p class="form-hint">Keep this secure - never share your client secret</p>
            </div>
            @break
        @endswitch

        {{-- Sync Configuration --}}
        <div class="mt-6 mb-4">
          <h3 class="text-[13px] font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Sync Configuration
          </h3>

          <div class="bg-gray-50 rounded-lg p-4">
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Contacts Sync</p>
                <p class="text-[11px] text-gray-500">Import and update contacts from CRM</p>
              </div>
              <div class="toggle-switch active" onclick="toggleSync(this)" data-sync="contacts">
                <input type="hidden" name="sync_config[entities][]" value="contacts">
              </div>
            </div>
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Leads / Deals Sync</p>
                <p class="text-[11px] text-gray-500">Sync opportunities and pipeline data</p>
              </div>
              <div class="toggle-switch active" onclick="toggleSync(this)" data-sync="leads">
                <input type="hidden" name="sync_config[entities][]" value="leads">
              </div>
            </div>
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Companies / Accounts</p>
                <p class="text-[11px] text-gray-500">Import company/account records</p>
              </div>
              <div class="toggle-switch" onclick="toggleSync(this)" data-sync="companies">
                <input type="hidden" name="sync_config[entities][]" value="companies" disabled>
              </div>
            </div>
            <div class="sync-config-row">
              <div>
                <p class="text-[12px] font-medium text-gray-700">Activities / Tasks</p>
                <p class="text-[11px] text-gray-500">Sync calls, meetings, and tasks</p>
              </div>
              <div class="toggle-switch" onclick="toggleSync(this)" data-sync="activities">
                <input type="hidden" name="sync_config[entities][]" value="activities" disabled>
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
            <option value="weekly">Weekly</option>
          </select>
          <p class="form-hint">How often should we sync data from your CRM?</p>
        </div>

        <div class="form-group">
          <label class="form-label">Sync Direction</label>
          <select name="sync_config[sync_direction]" class="form-input" style="cursor: pointer;">
            <option value="import" selected>Import only (CRM → Platform)</option>
            <option value="export">Export only (Platform → CRM)</option>
            <option value="bidirectional">Bidirectional sync</option>
          </select>
          <p class="form-hint">Choose data flow direction for this connection</p>
        </div>

        {{-- Test Result --}}
        <div id="testResult" class="test-result mb-4">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
          <span id="testResultText"></span>
        </div>

        {{-- Hidden status field --}}
        <input type="hidden" name="status" value="connected">

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
          <button type="submit" class="btn-success flex items-center gap-2">
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
          <a href="{{ route('client.crm-connections') }}" class="btn-secondary">Cancel</a>
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

function testConnection() {
  const result = document.getElementById('testResult');
  const text = document.getElementById('testResultText');
  
  result.className = 'test-result loading show';
  text.textContent = 'Testing connection to {{ $meta['name'] }}...';
  
  // Simulate API test (replace with actual endpoint call)
  setTimeout(() => {
    // In production, make actual API call here
    result.className = 'test-result success show';
    text.textContent = 'Connection successful! {{ $meta['name'] }} API is accessible.';
  }, 1500);
}

function toggleSync(element) {
    element.classList.toggle('active');
    const input = element.querySelector('input');
    if (input) {
        input.disabled = !element.classList.contains('active');
    }
}

function handleSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const provider = '{{ $provider }}';
    
    // Collect active sync entities
    const activeEntities = [];
    document.querySelectorAll('.toggle-switch.active').forEach(toggle => {
        const syncType = toggle.dataset.sync;
        if (syncType) activeEntities.push(syncType);
    });
    
    // Build data from form
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Ensure status is set
    data.status = data.status || 'connected';
    
    data.sync_config = {
        entities: activeEntities,
        sync_frequency: data['sync_config[sync_frequency]'] || 'hourly',
        sync_direction: data['sync_config[sync_direction]'] || 'import'
    };
    
    // Clean up old keys
    Object.keys(data).forEach(key => {
        if (key.startsWith('sync_config[entities]')) delete data[key];
        if (key === 'sync_config[sync_frequency]') delete data[key];
        if (key === 'sync_config[sync_direction]') delete data[key];
    });
    
    fetch('{{ route("client.crm.store", ["provider" => $provider]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(async r => {
        const responseData = await r.json();
        if (!r.ok || !responseData.success) {
            if (responseData.errors) {
                const messages = Object.values(responseData.errors).flat().join('\n');
                throw new Error(messages);
            }
            throw new Error(responseData.message || 'Request failed');
        }
        return responseData;
    })
    .then(data => {
        // For OAuth providers — redirect to authorization page
        if ((provider === 'zoho' || provider === 'monday') && data.oauth_url) {
            showToast('Redirecting to ' + provider + ' authorization...', 'success');
            setTimeout(() => {
                window.location.href = data.oauth_url;
            }, 1000);
            return;
        }
        
        showToast(data.message, 'success');
        setTimeout(() => window.location.href = '{{ route("client.crm-connections") }}', 1200);
    })
    .catch(err => {
        showToast(err.message || 'Error occurred', 'error');
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