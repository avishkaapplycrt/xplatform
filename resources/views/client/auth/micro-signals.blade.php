<x-guest-layout title="Select Micro Signals"
    backUrl="{{ route('client.register.connect.data.source') }}"
    previousUrl="{{ route('client.register.connect.data.source') }}"
    :currentStep="5">

    <div class="px-10 py-8">

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Select Micro-Signals</h2>
            <p class="text-sm text-gray-400 mt-1">
                Choose at least one signal per category for
                <span class="font-medium text-gray-600">{{ $industry->name }}</span>
            </p>
        </div>

        {{-- Global error --}}
        @if($errors->has('signals'))
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                {{ $errors->first('signals') }}
            </div>
        @endif

        <form method="POST"
              action="{{ route('client.register.micro.signals.store') }}"
              id="micro-signals-form">
            @csrf

            @forelse($categories as $category)
                @if($category->microSignals->isNotEmpty())

                    <div class="mb-5" data-category-id="{{ $category->id }}">

                        {{-- Category header --}}
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                {{ $category->name }}
                            </h3>
                            <span class="category-error hidden text-xs text-red-500 font-medium"
                                  data-for="{{ $category->id }}">
                                Select at least one ↑
                            </span>
                        </div>

                        {{-- Signal checkboxes --}}
                        @foreach($category->microSignals as $signal)
                            <label class="signal-label flex items-center gap-3 border rounded-lg p-3 mb-2
                                          cursor-pointer transition
                                          {{ in_array($signal->id, $selectedSignals) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}"
                                   data-category="{{ $category->id }}">
                                <input type="checkbox"
                                       name="signals[]"
                                       value="{{ $signal->id }}"
                                       class="signal-checkbox w-4 h-4 accent-blue-600 shrink-0"
                                       data-category="{{ $category->id }}"
                                       {{ in_array($signal->id, $selectedSignals) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700 select-none">{{ $signal->name }}</span>
                            </label>
                        @endforeach

                    </div>

                @endif
            @empty
                <p class="text-center text-gray-400 py-8">
                    No signals available for your industry yet.
                </p>
            @endforelse

            <button type="submit"
                    class="w-full mt-4 bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Continue
            </button>

        </form>
    </div>

</x-guest-layout>

<script>
(function () {
    // Toggle label highlight when checkbox changes
    document.querySelectorAll('.signal-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const label = this.closest('.signal-label');
            label.classList.toggle('border-blue-500', this.checked);
            label.classList.toggle('bg-blue-50', this.checked);
            label.classList.toggle('border-gray-200', !this.checked);

            // Clear per-category error when user selects something
            const categoryId = this.dataset.category;
            const anyChecked = document.querySelectorAll(
                '.signal-checkbox[data-category="' + categoryId + '"]:checked'
            ).length > 0;

            const errEl = document.querySelector('.category-error[data-for="' + categoryId + '"]');
            if (errEl && anyChecked) errEl.classList.add('hidden');
        });
    });

    // Client-side per-category validation before submit
    document.getElementById('micro-signals-form').addEventListener('submit', function (e) {
        const categories = document.querySelectorAll('[data-category-id]');
        let allValid = true;

        categories.forEach(function (catDiv) {
            const id         = catDiv.dataset.categoryId;
            const anyChecked = catDiv.querySelectorAll('.signal-checkbox:checked').length > 0;
            const errEl      = catDiv.querySelector('.category-error[data-for="' + id + '"]');

            if (!anyChecked) {
                allValid = false;
                if (errEl) errEl.classList.remove('hidden');
            } else {
                if (errEl) errEl.classList.add('hidden');
            }
        });

        if (!allValid) {
            e.preventDefault();
            // Re-enable button so user can fix and resubmit
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-60', 'cursor-not-allowed');
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
})();
</script>
