@extends('layouts.client')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Connected Laravel Sites</h1>
        <a href="{{ route('client.analytics.laravel.sites.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Connect Laravel Site
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($sites->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500 mb-4">No Laravel sites connected yet.</p>
            <a href="{{ route('client.analytics.laravel.sites.create') }}" class="text-blue-600 hover:underline">
                Connect your first Laravel site →
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($sites as $site)
            <div class="bg-white rounded-lg shadow p-6 border-l-4 {{ $site->is_active ? 'border-green-500' : 'border-red-500' }}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-bold text-lg">{{ $site->site_name }}</h3>
                        <a href="{{ $site->site_url }}" target="_blank" class="text-sm text-blue-600 hover:underline">
                            {{ $site->site_url }}
                        </a>
                    </div>
                    <span class="px-2 py-1 text-xs rounded {{ $site->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $site->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="space-y-2 text-sm text-gray-600 mb-4">
                    <div class="flex justify-between">
                        <span>Site ID:</span>
                        <code class="text-xs bg-gray-100 px-1">{{ $site->site_id }}</code>
                    </div>
                    <div class="flex justify-between">
                        <span>API Type:</span>
                        <span class="capitalize">{{ str_replace('_', ' ', $site->api_type) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Sync Frequency:</span>
                        <span class="capitalize">{{ str_replace('_', ' ', $site->sync_frequency) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Last Sync:</span>
                        <span>{{ $site->last_sync_at ? $site->last_sync_at->diffForHumans() : 'Never' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Events:</span>
                        <span>{{ $site->events()->count() }}</span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('client.analytics.laravel.sites.show', $site) }}" class="flex-1 bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700 text-sm">
                        Dashboard
                    </a>
                    <form action="{{ route('client.analytics.laravel.sites.destroy', $site) }}" method="POST" class="inline" onsubmit="return confirm('Delete this site?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm" title="Delete">
                            🗑
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection