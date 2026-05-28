<x-guest-layout title="Select Analysis Layers" backUrl="/app/industry" previousUrl="/app/industry" :currentStep="3">

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="p-8">

    {{-- Page Title --}}
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Select analysis layers</h2>
        <p class="text-sm text-gray-400 mt-1">L1 is required — select 2 more layers to continue</p>
    </div>

    <form method="POST" action="{{ route('client.register.analysis.layers.store') }}" id="layers-form">
        @csrf

        <div class="flex flex-col gap-2 mb-4">
            @foreach($layers as $layer)
                @php
                    $isL1      = $layer->code === 'L1';
                    $checked   = $isL1 || in_array($layer->id, old('layers', $selectedLayers ?? []));
                @endphp

                <label class="layer-row flex items-center gap-3 border rounded-xl px-4 py-3 transition
                              {{ $isL1 ? 'border-blue-500 bg-blue-50 cursor-default' : 'cursor-pointer ' . ($checked ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white') }}"
                       onclick="{{ $isL1 ? 'return false' : '' }}">

                    {{-- L1: always-submitted hidden input --}}
                    @if($isL1)
                        <input type="hidden" name="layers[]" value="{{ $layer->id }}">
                    @else
                        <input type="checkbox" name="layers[]" value="{{ $layer->id }}"
                            class="layer-checkbox hidden"
                            {{ $checked ? 'checked' : '' }}>
                    @endif

                    <div class="checkbox-ui w-5 h-5 rounded border-2 flex items-center justify-center shrink-0
                                {{ ($isL1 || $checked) ? 'bg-blue-500 border-blue-500' : 'border-gray-300 bg-white' }}">
                        <svg class="tick-icon w-3 h-3 text-white {{ ($isL1 || $checked) ? '' : 'hidden' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.2 7.2a1 1 0 01-1.414 0l-3.6-3.6a1 1 0 111.414-1.42l2.893 2.894 6.493-6.494a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>

                    <span class="text-xs font-medium w-6 shrink-0 {{ $isL1 ? 'text-blue-500' : 'text-gray-400' }}">
                        {{ $layer->code }}
                    </span>

                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-800">
                            {{ $layer->name }}
                            @if($isL1)
                                <span class="ml-2 text-[10px] font-semibold text-blue-500 bg-blue-100 px-1.5 py-0.5 rounded">Required</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400">{{ $layer->description }}</div>
                    </div>

                </label>
            @endforeach
        </div>

        {{-- Selection counter --}}
        <p id="layer-counter" class="text-xs text-center mb-4 text-gray-400">
            Select <span id="remaining-count">2</span> more layer(s) to continue
        </p>

        <button type="submit" id="submit-btn"
            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition disabled:opacity-40 disabled:cursor-not-allowed"
            disabled>
            Continue
        </button>

    </form>
</div>

<script>
    const REQUIRED = 3; // L1 is always 1, so user must pick 2 more

    function countChecked() {
        return document.querySelectorAll('.layer-checkbox:checked').length + 1; // +1 for L1
    }

    function updateState() {
        const total     = countChecked();
        const remaining = REQUIRED - total;
        const btn       = document.getElementById('submit-btn');
        const counter   = document.getElementById('layer-counter');
        const remSpan   = document.getElementById('remaining-count');

        if (remaining > 0) {
            remSpan.textContent = remaining;
            counter.style.color = '#9ca3af';
            btn.disabled = true;
        } else {
            counter.textContent  = '3 layers selected — ready to continue';
            counter.style.color  = '#10b981';
            btn.disabled = false;
        }
    }

    document.querySelectorAll('.layer-row').forEach(function(row) {
        const checkbox = row.querySelector('.layer-checkbox');
        if (!checkbox) return; // L1 has no checkbox

        checkbox.addEventListener('change', function() {
            // Apply visual state
            const box  = row.querySelector('.checkbox-ui');
            const tick = row.querySelector('.tick-icon');
            if (checkbox.checked) {
                row.classList.add('border-blue-500', 'bg-blue-50');
                row.classList.remove('border-gray-200', 'bg-white');
                box.classList.add('bg-blue-500', 'border-blue-500');
                box.classList.remove('border-gray-300', 'bg-white');
                tick.classList.remove('hidden');
            } else {
                row.classList.remove('border-blue-500', 'bg-blue-50');
                row.classList.add('border-gray-200', 'bg-white');
                box.classList.remove('bg-blue-500', 'border-blue-500');
                box.classList.add('border-gray-300', 'bg-white');
                tick.classList.add('hidden');
            }
            updateState();
        });
    });

    // Init on page load
    updateState();
</script>

</x-guest-layout>
