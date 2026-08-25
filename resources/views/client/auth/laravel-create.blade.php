@extends('layouts.client')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Connect Laravel Site</h1>

    <form action="{{ route('client.laravel.sites.store') }}" method="POST" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Site Name</label>
            <input type="text" name="site_name" required class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Laravel Site URL</label>
            <input type="url" name="site_url" required placeholder="https://your-laravel-site.com" 
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">API Token</label>
            <input type="password" name="api_token" required class="w-full border rounded px-3 py-2">
            <p class="text-xs text-gray-500 mt-1">Generate token on target site: php artisan tinker → App\Models\ApiToken::create(['name'=>'Analytics', 'token'=>hash('sha256', bin2hex(random_bytes(32)))])</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Sync Frequency</label>
            <select name="sync_frequency" class="w-full border rounded px-3 py-2">
                <option value="hourly">Every Hour</option>
                <option value="6hours" selected>Every 6 Hours</option>
                <option value="daily">Daily</option>
            </select>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Connect Site
            </button>
            <button type="button" onclick="testConnection()" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                Test Connection
            </button>
        </div>
    </form>
</div>

<script>
function testConnection() {
    const url = document.querySelector('input[name="site_url"]').value;
    const token = document.querySelector('input[name="api_token"]').value;
    
    if (!url || !token) {
        alert('Please fill in URL and token first');
        return;
    }

    fetch('{{ route("client.laravel.sites.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ url, api_token: token })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.success ? 'Connection successful! App: ' + data.data.app_name : 'Failed: ' + data.message);
    });
}
</script>
@endsection