<x-guest-layout backUrl="/app/register" previousUrl="/app/register" :currentStep="2">

<div class="p-8">

    {{-- Page Title --}}
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Select your industry</h2>
        <p class="text-sm text-gray-400 mt-1">Loads default signals, predictions, and templates</p>
    </div>

    <form method="POST" action="{{ route('client.industry.store') }}">
        @csrf

        {{-- Industry 3-Column Grid --}}
        <div data-grid style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px;">
            @foreach($industries as $industry)
                <label class="industry-card flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:border-blue-400 transition"
                    data-signals="{{ implode('|', $industry->microSignals->pluck('name')->toArray()) }}"
                    data-predictions="{{ implode('|', $industry->predictionModels->pluck('name')->toArray()) }}"
                    data-name="{{ $industry->name }}">
                    <input type="radio" name="industry_id" value="{{ $industry->id }}" class="accent-blue-600" required>
                    <span class="text-sm text-gray-700">{{ $industry->name }}</span>
                </label>
            @endforeach

        </div>

        {{-- Template Preview Box --}}
        <div id="templateBox" class="hidden border border-blue-200 rounded-xl p-4 mb-6 relative">

            {{-- Info Icon --}}
            <div class="absolute top-3 right-3 group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400 cursor-pointer" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                </svg>
                <div class="absolute right-0 top-6 z-10 hidden group-hover:block w-64 bg-gray-800 text-white text-xs rounded-lg p-3 shadow-lg">
                    This is just a template. You can customise signals and predictions after registration.
                </div>
            </div>

            <p id="templateTitle" class="text-blue-500 text-xs font-bold uppercase tracking-wide mb-3"></p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 mb-2">Signals</p>
                    <ul id="signalsList" class="text-sm text-gray-700 space-y-1 list-none"></ul>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-2">Predictions</p>
                    <ul id="predictionsList" class="text-sm text-gray-700 space-y-1 list-none"></ul>
                </div>
            </div>
        </div>

        {{-- Continue Button --}}
        <button type="submit"
            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
            Continue
        </button>

    </form>
</div>


<script>
    {{-- Highlight selected card and show template preview --}}
    document.querySelectorAll('.industry-card').forEach(function(card) {
        card.addEventListener('click', function() {
            document.querySelectorAll('.industry-card').forEach(function(c) {
                c.classList.remove('border-blue-500', 'bg-blue-50');
            });

            this.classList.add('border-blue-500', 'bg-blue-50');

            const name        = this.dataset.name;
            const signals     = this.dataset.signals ? this.dataset.signals.split('|') : [];
            const predictions = this.dataset.predictions ? this.dataset.predictions.split('|') : [];

            document.getElementById('templateTitle').textContent = name.toUpperCase() + ' TEMPLATE';

            const signalsList = document.getElementById('signalsList');
            signalsList.innerHTML = '';
            signals.forEach(function(s) {
                const li = document.createElement('li');
                li.textContent = s;
                signalsList.appendChild(li);
            });

            const predictionsList = document.getElementById('predictionsList');
            predictionsList.innerHTML = '';
            predictions.forEach(function(p) {
                const li = document.createElement('li');
                li.textContent = p;
                predictionsList.appendChild(li);
            });

            document.getElementById('templateBox').classList.remove('hidden');
        });
    });

</script>

</x-guest-layout>