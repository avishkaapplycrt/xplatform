{{-- Sidebar footer: how many of the signed-in client's data sources are
     connected (replaces the old fixed "All systems operational / 150+
     micro-signals active" text). Cached briefly — it's on every page. --}}
@php
    $ssClient = auth('client')->user();
    $ssSources = $ssClient
        ? \Illuminate\Support\Facades\Cache::remember('sources-connected:' . $ssClient->id, 60,
            fn () => app(\App\Services\DataCollectionStats::class)->counts($ssClient)['sources'])
        : 0;
@endphp
<div class="px-3 py-3 border-t border-gray-100 bg-gray-50">
    <div class="flex items-center gap-1.5 mb-1">
        <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $ssSources ? 'bg-green-500' : 'bg-gray-300' }}"></span>
        <span class="text-[10px] text-gray-700 font-medium">{{ $ssSources }} of 5 data sources connected</span>
    </div>
    @if (!$ssSources)
        <a href="{{ route('client.layer.l1') }}" class="text-[10px] text-teal-600 hover:underline pl-3.5">Connect a source</a>
    @endif
    <p class="text-[10px] text-gray-400 pl-3.5">{{ $ssClient?->company_name }}</p>
</div>
