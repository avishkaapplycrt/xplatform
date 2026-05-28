<x-guest-layout title="Select Predictions"
    backUrl="{{ route('client.register.micro.signals') }}"
    previousUrl="{{ route('client.register.micro.signals') }}"
    :currentStep="6"
>
    <div class="px-10 py-8">

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Select predictions</h2>
            <p class="text-sm text-gray-400 mt-1">AI models for {{ $industry->name }}</p>
        </div>

        @if(session('error') || $errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('error') ?? $errors->first() }}
            </div>
        @endif

        <form action="{{ route('client.register.select.predictions.store') }}" method="POST">
            @csrf

            @foreach($predictions as $prediction)
                <label class="flex items-center gap-4 border rounded-lg p-4 mb-3 cursor-pointer hover:bg-blue-50 transition {{ in_array($prediction->id, old('predictions', $selectedPredictionIds)) ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                    <input type="checkbox"
                           name="predictions[]"
                           value="{{ $prediction->id }}"
                           class="w-4 h-4 mt-0.5 accent-blue-600"
                           {{ in_array($prediction->id, old('predictions', $selectedPredictionIds)) ? 'checked' : '' }}
                           onchange="
                               const lbl = this.closest('label');
                               lbl.classList.toggle('border-blue-500', this.checked);
                               lbl.classList.toggle('bg-blue-50', this.checked);
                               lbl.classList.toggle('border-gray-200', !this.checked);
                           ">
                    <span class="text-sm font-medium text-gray-800">{{ $prediction->name }}</span>
                </label>
            @endforeach

            <button type="submit"
                    class="w-full mt-4 bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Continue
            </button>
        </form>

    </div>
</x-guest-layout>
