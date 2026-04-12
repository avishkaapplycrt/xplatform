@php
    $backHref = $isFirst
        ? route('client.register.analysis.layers')
        : route('client.register.connect.data.source', ['layer_id' => $prevLayerId]);
@endphp

<x-guest-layout :backUrl="$backHref" :previousUrl="$backHref" :currentStep="4">

    <div class="p-8">

        <h2 class="text-2xl font-semibold text-gray-900 text-center mb-1">
            Connect data sources
        </h2>
        <p class="text-sm text-blue-400 text-center mb-1">
            Select systems for data ingestion
        </p>
        <p class="text-xs text-gray-400 text-center mb-6">
            <span class="font-semibold text-gray-600">{{ $currentLayer->name }}</span>
            &mdash; {{ $position + 1 }} of {{ $total }}
        </p>

        <form method="POST" action="{{ route('client.register.connect.data.source.store') }}">
            @csrf

            {{-- layer_id sent as hidden field so it's reliably in POST body --}}
            <input type="hidden" name="layer_id" value="{{ $layerId }}">

            @error('data_sources')
                <p class="text-sm text-red-500 text-center mb-4">{{ $message }}</p>
            @enderror

            @if($sources->isNotEmpty())

                <div class="flex flex-col gap-3 mb-6">
                    @foreach($sources as $source)
                        @php $checked = in_array($source->id, $previouslySelected); @endphp
                        <label id="lbl-{{ $source->id }}"
                               class="flex items-center gap-3 border rounded-xl px-4 py-3 cursor-pointer transition-all
                                      {{ $checked ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:border-blue-300 hover:bg-gray-50' }}">
                            <input type="checkbox"
                                   name="data_sources[]"
                                   value="{{ $source->id }}"
                                   class="w-4 h-4 accent-blue-600 shrink-0"
                                   {{ $checked ? 'checked' : '' }}
                                   onchange="
                                       const l = document.getElementById('lbl-{{ $source->id }}');
                                       l.classList.toggle('border-blue-500', this.checked);
                                       l.classList.toggle('bg-blue-50', this.checked);
                                       l.classList.toggle('border-gray-200', !this.checked);
                                       l.classList.toggle('bg-white', !this.checked);
                                   ">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $source->name }}</p>
                                <p class="text-xs text-gray-400">{{ $source->subtitle }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>

            @else

                <div class="flex flex-col items-center justify-center py-8 mb-6
                            border-2 border-dashed border-gray-200 rounded-xl text-center">
                    <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor"
                         stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375"/>
                    </svg>
                    <p class="text-sm font-semibold text-gray-600 mb-1">{{ $currentLayer->name }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        Data sources for this layer will be configured during onboarding.
                    </p>
                </div>

            @endif

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-3 rounded-xl transition">
                {{ $isLast ? 'Continue' : 'Next' }}
            </button>

        </form>
    </div>

</x-guest-layout>
