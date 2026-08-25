@extends('layouts.client')

@section('title', 'Data Sources')

@section('content')
<x-setup-card
    title="Data sources"
    subtitle="The systems each layer should ingest from. Selecting one here marks it as in scope — you connect the actual account from the integrations pages."
    :action="route('client.setup.data-sources.update')"
    submitLabel="Save data sources">

    @forelse($layers as $layer)
        <div class="mb-6 last:mb-0">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">
                {{ $layer->code }} — {{ $layer->name }}
            </p>

            <div class="flex flex-col gap-2">
                @foreach($sourcesByLayer[$layer->id] as $source)
                    <x-setup-check
                        name="data_sources[]"
                        :value="$source->id"
                        :label="$source->name"
                        :description="$source->subtitle"
                        :checked="in_array((int) $source->id, $selected, true)" />
                @endforeach
            </div>
        </div>
    @empty
        <div class="flex flex-col items-center justify-center py-10 text-center
                    border-2 border-dashed border-gray-200 rounded-xl">
            <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor"
                 stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375"/>
            </svg>
            <p class="text-sm font-semibold text-gray-600">No sources available yet</p>
            <p class="text-xs text-gray-400 mt-1 max-w-sm">
                None of your enabled layers have selectable data sources.
                Connect a platform directly from the integrations pages instead.
            </p>
        </div>
    @endforelse

</x-setup-card>
@endsection
