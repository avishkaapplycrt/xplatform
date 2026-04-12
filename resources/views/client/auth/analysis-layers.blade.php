<x-guest-layout backUrl="/app/industry" previousUrl="/app/industry" :currentStep="3">

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
        <p class="text-sm text-gray-400 mt-1">Enable or disable each platform layer (min. 3)</p>
    </div>

    <form method="POST" action="{{ route('client.register.analysis.layers.store') }}">
        @csrf

        <div class="flex flex-col gap-2 mb-6">
            @foreach($layers as $layer)
                @php $checked = in_array($layer->id, old('layers', $selectedLayers ?? [])); @endphp
                <label class="layer-row flex items-center gap-3 border rounded-xl px-4 py-3 cursor-pointer transition {{ $checked ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white' }}">
                    <input type="checkbox" name="layers[]" value="{{ $layer->id }}"
                        class="layer-checkbox hidden"
                        {{ $checked ? 'checked' : '' }}>
                    <div class="checkbox-ui w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 {{ $checked ? 'bg-blue-500 border-blue-500' : 'border-gray-300 bg-white' }}">
                        <svg class="tick-icon w-3 h-3 text-white {{ $checked ? '' : 'hidden' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.2 7.2a1 1 0 01-1.414 0l-3.6-3.6a1 1 0 111.414-1.42l2.893 2.894 6.493-6.494a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-xs text-gray-400 font-medium w-6 shrink-0">{{ $layer->code }}</span>
                    <div>
                        <div class="text-sm font-medium text-gray-800">{{ $layer->name }}</div>
                        <div class="text-xs text-gray-400">{{ $layer->description }}</div>
                    </div>
                </label>
            @endforeach
        </div>

        <button type="submit"
            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
            Continue
        </button>

    </form>
</div>

<script>
    function applyLayerState(row) {
        const checkbox = row.querySelector('.layer-checkbox');
        const box      = row.querySelector('.checkbox-ui');
        const tick     = row.querySelector('.tick-icon');

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
    }

    document.querySelectorAll('.layer-row').forEach(function(row) {
        const checkbox = row.querySelector('.layer-checkbox');

        // The label's default click already toggles the checkbox.
        // Listen on the checkbox change event instead to avoid double-toggle.
        checkbox.addEventListener('change', function() {
            applyLayerState(row);
        });
    });
</script>

</x-guest-layout>