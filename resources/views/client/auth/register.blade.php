<x-guest-layout backUrl="/app/login" previousUrl="/app/login" :currentStep="1">

    {{-- Top Banner --}}
    <div class="w-full h-40 bg-gray-100 flex items-center justify-center">
        <span class="text-3xl font-bold text-gray-300">XPlatforms</span>
    </div>

    {{-- Form Section --}}
    <div class="px-10 py-8">

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Create your organization</h2>
            <p class="text-sm text-gray-400 mt-1">This sets up your dedicated tenant</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('client.register') }}">
            @csrf

            {{-- Company Name --}}
            <div class="mb-4">
                <x-input-label for="company_name" :value="__('Company name')" />
                <x-text-input id="company_name" class="block mt-1 w-full" type="text"
                    name="company_name" :value="old('company_name')"
                    placeholder="Acme Corp" required autofocus />
                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
            </div>

            {{-- Company Size --}}
            <div class="mb-4">
                <x-input-label for="company_size" :value="__('Company size')" />
                <select id="company_size" name="size"
                    class="block mt-1 w-full border-gray-300 rounded-lg shadow-sm
                           focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                    <option value="" disabled {{ old('size') ? '' : 'selected' }}>Select size</option>
                    @foreach(['1-10'=>'1–10 employees','11-50'=>'11–50 employees','51-200'=>'51–200 employees','201-500'=>'201–500 employees','500+'=>'500+ employees'] as $val => $label)
                        <option value="{{ $val }}" {{ old('size') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('size')" class="mt-2" />
            </div>

            {{-- Country --}}
            <div class="mb-6">
                <x-input-label for="country" :value="__('Country')" />
                <select id="country" name="country"
                    class="block mt-1 w-full border-gray-300 rounded-lg shadow-sm
                           focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                    <option value="" disabled {{ old('country') ? '' : 'selected' }}>Select country</option>
                    @foreach($countries as $code => $name)
                        <option value="{{ $code }}" {{ old('country') === $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('country')" class="mt-2" />
            </div>

            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Continue
            </button>

        </form>

        <p class="text-center text-sm text-gray-400 mt-5">
            Already have an account?
            <a href="{{ route('client.login') }}" class="text-blue-500 hover:text-blue-700 font-medium">Sign in</a>
        </p>

    </div>

</x-guest-layout>
