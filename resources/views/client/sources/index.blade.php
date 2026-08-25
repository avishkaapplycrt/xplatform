@extends('layouts.client')

@section('title', 'Data Sources')

@section('content')
<div class="px-4 sm:px-0">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Data Sources</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('client.crm.index') }}"
               class="inline-flex items-center gap-2 border border-gray-200 hover:border-gray-300 bg-white text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M15.54 8.27V5.74a1.66 1.66 0 0 0 .96-1.5V4.2a1.66 1.66 0 0 0-3.32 0v.04a1.66 1.66 0 0 0 .96 1.5v2.53a4.72 4.72 0 0 0-2.24 1.04L5.4 5.07a1.84 1.84 0 1 0-.88 1.56l6.37 4.11a4.74 4.74 0 0 0-.62 2.34 4.72 4.72 0 0 0 .9 2.78l-1.93 1.93a1.57 1.57 0 1 0 1.1 1.07l2.07-2.07a4.72 4.72 0 1 0 3.13-8.52Zm0 6.92a2.37 2.37 0 1 1 0-4.74 2.37 2.37 0 0 1 0 4.74Z" fill="#FF7A59"/>
                </svg>
                CRM Connections
            </a>
            <a href="{{ route('client.sources.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Connect Database
            </a>
        </div>
    </div>

    @if($sources->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <svg class="mx-auto w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7V5a2 2 0 012-2h12a2 2 0 012 2v2"/>
            </svg>
            <p class="text-sm font-medium text-gray-500">No data sources connected yet.</p>
            <a href="{{ route('client.sources.create') }}"
               class="mt-4 inline-block text-sm text-indigo-600 hover:underline">Connect your first data source</a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($sources as $source)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3" id="source-card-{{ $source->id }}">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $source->connection_name }}</p>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">{{ $source->db_type }}</p>
                        </div>
                        <span id="status-badge-{{ $source->id }}" class="text-[11px] font-semibold px-2 py-0.5 rounded-full flex-shrink-0
                            {{ $source->status === 'connected' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200' }}">
                            {{ ucfirst($source->status) }}
                        </span>
                    </div>

                    <div class="text-xs text-gray-500 space-y-0.5">
                        <p>Host: <span class="text-gray-700">{{ $source->host }}:{{ $source->port }}</span></p>
                        <p>Database: <span class="text-gray-700">{{ $source->database_name }}</span></p>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                        <button type="button"
                                onclick="testConnection({{ $source->id }})"
                                id="test-btn-{{ $source->id }}"
                                class="text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 px-2 py-1 rounded transition flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Test
                        </button>

                        <form method="POST" action="{{ route('client.sources.destroy', $source) }}"
                              onsubmit="return confirm('Remove this data source?')" class="ml-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-xs text-red-500 hover:text-red-700 transition">
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<script>
function testConnection(sourceId) {
    const btn = document.getElementById('test-btn-' + sourceId);
    const badge = document.getElementById('status-badge-' + sourceId);
    
    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Testing...';

    fetch(`/app/sources/${sourceId}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            badge.className = 'text-[11px] font-semibold px-2 py-0.5 rounded-full flex-shrink-0 bg-emerald-50 text-emerald-700 border border-emerald-200';
            badge.textContent = 'Connected';
            alert('✅ Connection is working!\nStatus: ' + data.status);
        } else {
            badge.className = 'text-[11px] font-semibold px-2 py-0.5 rounded-full flex-shrink-0 bg-red-50 text-red-600 border border-red-200';
            badge.textContent = 'Failed';
            alert('❌ Connection failed!\nError: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('❌ Request failed: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Test';
    });
}
</script>
@endsection