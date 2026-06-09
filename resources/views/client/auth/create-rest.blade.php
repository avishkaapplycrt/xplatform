{{-- resources/views/client/auth/create-rest.blade.php --}}
@extends('layouts.client')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Add WordPress Site (REST Polling)</h1>

    <form action="{{ route('client.analytics.sites.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium mb-1">Site Name</label>
                <input type="text" name="site_name" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Client</label>
                <select name="client_id" required class="w-full border rounded px-3 py-2">
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ auth()->guard('client')->id() == $client->id ? 'selected' : '' }}>
                            {{ $client->name ?? $client->company_name ?? 'Client #' . $client->id }}
                        </option>
                    @endforeach
                </select>
                @if($clients->count() == 1)
                    <input type="hidden" name="client_id" value="{{ $clients->first()->id }}">
                @endif
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">WordPress URL</label>
            <input type="url" name="site_url" required placeholder="https://client-site.com" 
                   class="w-full border rounded px-3 py-2">
        </div>

        <input type="hidden" name="api_type" value="rest_poll">

        <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="font-semibold mb-3">Authentication Method</h3>
            
            <div class="space-y-4">
                <label class="flex items-center gap-3">
                    <input type="radio" name="auth_type" value="application_password" checked class="text-blue-600">
                    <div>
                        <span class="font-medium">Application Password (Recommended)</span>
                        <p class="text-sm text-gray-600">WordPress 5.6+ built-in. Generate at: User Profile → Application Passwords</p>
                    </div>
                </label>

                <label class="flex items-center gap-3">
                    <input type="radio" name="auth_type" value="basic" class="text-blue-600">
                    <div>
                        <span class="font-medium">Basic Auth (Username/Password)</span>
                        <p class="text-sm text-gray-600">Requires Basic Auth plugin installed on WordPress</p>
                    </div>
                </label>

                <label class="flex items-center gap-3">
                    <input type="radio" name="auth_type" value="bearer" class="text-blue-600">
                    <div>
                        <span class="font-medium">Bearer Token</span>
                        <p class="text-sm text-gray-600">Custom JWT or API token</p>
                    </div>
                </label>
            </div>
        </div>

        <div id="auth-fields" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Username / Admin Email</label>
                    <input type="text" name="credentials[username]" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Password / App Password</label>
                    <input type="password" name="credentials[password]" required class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">For Application Password: NOT your login password. Generate a separate app password.</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold mb-3">WooCommerce (Optional)</h3>
            <label class="flex items-center gap-2 mb-4">
                <input type="checkbox" name="has_woocommerce" value="1" class="rounded">
                <span>This site has WooCommerce installed</span>
            </label>

            <div id="wc-fields" class="hidden space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Consumer Key</label>
                        <input type="text" name="credentials[wc_consumer_key]" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Consumer Secret</label>
                        <input type="password" name="credentials[wc_consumer_secret]" class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                <p class="text-sm text-gray-600">
                    Generate WooCommerce API keys: WP Admin → WooCommerce → Settings → Advanced → REST API
                </p>
            </div>
        </div>

        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="font-semibold mb-2">Sync Settings</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Sync Frequency</label>
                    <select name="sync_frequency" class="w-full border rounded px-3 py-2">
                        <option value="hourly">Every Hour</option>
                        <option value="6hours" selected>Every 6 Hours</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">SSL Verify</label>
                    <select name="connection_config[ssl_verify]" class="w-full border rounded px-3 py-2">
                        <option value="1" selected>Yes (Production)</option>
                        <option value="0">No (Local/Development)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Add Site & Test Connection
            </button>
            <button type="button" onclick="testConnection()" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                Test Connection First
            </button>
        </div>
    </form>
</div>

<script>
document.querySelector('input[name="has_woocommerce"]').addEventListener('change', function() {
    document.getElementById('wc-fields').classList.toggle('hidden', !this.checked);
});

function testConnection() {
    const url = document.querySelector('input[name="site_url"]').value;
    const username = document.querySelector('input[name="credentials[username]"]').value;
    const password = document.querySelector('input[name="credentials[password]"]').value;
    
    if (!url || !username || !password) {
        alert('Please fill in URL, username, and password first');
        return;
    }

    fetch('{{ route("client.analytics.sites.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ url, username, password })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.success ? 'Connection successful! WordPress version: ' + data.wp_version : 'Failed: ' + data.message);
    });
}
</script>
@endsection