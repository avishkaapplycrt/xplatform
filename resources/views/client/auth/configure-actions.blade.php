<x-guest-layout backUrl="/app/register/select-predictions" previousUrl="/app/register/select-predictions" :currentStep="7">

    <div class="px-10 py-8">

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Configure actions</h2>
            <p class="text-sm text-gray-400 mt-1">How the platform responds to predictions</p>
        </div>

        <form method="POST" action="{{ route('client.register.configure.actions.store') }}">
            @csrf

            @foreach($actions as $action)
                <label class="flex items-start gap-4 border rounded-lg p-4 mb-3 cursor-pointer hover:bg-blue-50 transition {{ in_array($action->id, $selectedActionIds) ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                    <input type="checkbox"
                           name="actions[]"
                           value="{{ $action->id }}"
                           class="w-4 h-4 mt-1"
                           {{ in_array($action->id, $selectedActionIds) ? 'checked' : '' }}>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $action->name }}</p>
                        <p class="text-sm text-gray-400">{{ $action->description }}</p>
                    </div>
                </label>
            @endforeach

            <button type="submit"
                    class="w-full mt-6 bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Continue
            </button>

        </form>

    </div>

</x-guest-layout>