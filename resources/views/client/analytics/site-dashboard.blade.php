@extends('layouts.client')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $site->site_name }}</h1>
            <a href="{{ $site->site_url }}" target="_blank" class="text-blue-600 hover:underline text-sm">{{ $site->site_url }}</a>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('client.analytics.sites.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                ← Back to Sites
            </a>
            @if($site->hasWooCommerce())
            <a href="{{ route('client.analytics.woocommerce.overview', $site) }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                🛒 WooCommerce
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Period Selector -->
    <div class="mb-6">
        <form method="GET" class="flex gap-2">
            <select name="days" onchange="this.form.submit()" class="border rounded px-3 py-2">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
            </select>
        </form>
    </div>

    <!-- Traffic Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 mb-1">Page Views</div>
            <div class="text-2xl font-bold">{{ number_format($traffic['page_views']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 mb-1">Unique Visitors</div>
            <div class="text-2xl font-bold">{{ number_format($traffic['unique_visitors']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 mb-1">User Logins</div>
            <div class="text-2xl font-bold">{{ number_format($traffic['user_logins']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 mb-1">Registrations</div>
            <div class="text-2xl font-bold">{{ number_format($traffic['registrations']) }}</div>
        </div>
    </div>

    <!-- Content Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 mb-1">Total Posts</div>
            <div class="text-2xl font-bold">{{ number_format($content['total_posts']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 mb-1">Posts This Month</div>
            <div class="text-2xl font-bold">{{ number_format($content['posts_this_month']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 mb-1">Total Comments</div>
            <div class="text-2xl font-bold">{{ number_format($content['total_comments']) }}</div>
        </div>
    </div>

    <!-- Sync Status -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h3 class="font-semibold mb-2">Sync Status</h3>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full {{ $site->is_active ? 'bg-green-500' : 'bg-red-500' }}"></div>
            <span>{{ $site->is_active ? 'Active' : 'Inactive' }}</span>
            <span class="text-gray-500">|</span>
            <span class="text-gray-500">Last Sync: {{ $site->last_sync_at ? $site->last_sync_at->diffForHumans() : 'Never' }}</span>
            <span class="text-gray-500">|</span>
            <span class="text-gray-500">Frequency: {{ ucfirst(str_replace('_', ' ', $site->sync_frequency)) }}</span>
        </div>
    </div>

    <!-- Recent Events -->
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold mb-4">Recent Events (Last 50)</h3>
        @if($recentEvents->isEmpty())
            <p class="text-gray-500">No events recorded yet. Data will appear after the first sync.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Event Type</th>
                            <th class="px-4 py-2 text-left">Entity ID</th>
                            <th class="px-4 py-2 text-left">Synced At</th>
                            <th class="px-4 py-2 text-left">WP Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentEvents as $event)
                        <tr class="border-t">
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded text-xs {{ match($event->event_type) {
                                    'page_view' => 'bg-blue-100 text-blue-800',
                                    'user_login' => 'bg-green-100 text-green-800',
                                    'user_registration' => 'bg-purple-100 text-purple-800',
                                    'post_publish' => 'bg-yellow-100 text-yellow-800',
                                    'comment' => 'bg-gray-100 text-gray-800',
                                    'purchase' => 'bg-pink-100 text-pink-800',
                                    default => 'bg-gray-100 text-gray-800'
                                } }}">
                                    {{ str_replace('_', ' ', $event->event_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $event->wp_entity_id ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $event->synced_at->diffForHumans() }}</td>
                            <td class="px-4 py-2">{{ $event->wp_created_at ? $event->wp_created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection