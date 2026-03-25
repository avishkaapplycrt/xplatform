<x-guest-layout backUrl="/app/login" previousUrl="/app/login" :currentStep="1">
    {{-- Top Banner --}}
    <div class="w-full h-56 bg-gray-100 flex items-center justify-center">
        <span class="text-4xl font-bold text-gray-400">Web image</span>
    </div>

    {{-- Form Section --}}
    <div class="px-10 py-8">

        {{-- Title --}}
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Create your organization</h2>
            <p class="text-sm text-gray-400 mt-1">This sets up your dedicated tenant</p>
        </div>

        <form method="POST" action="/app/register">
            @csrf

            <!-- Company Name -->
            <div class="mb-4">
                <x-input-label for="company_name" :value="__('Company name')" />
                <x-text-input id="company_name" class="block mt-1 w-full" type="text"
                    name="company_name" :value="old('company_name')"
                    placeholder="Mockmaster.ai" required autofocus />
                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
            </div>

            <!-- Company Size -->
            <div class="mb-4">
                <x-input-label for="company_size" :value="__('Company size')" />
                <select id="company_size" name="company_size"
                    class="block mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-500">
                    <option value="" disabled selected>Select size</option>
                    <option value="1-10">1-10 employees</option>
                    <option value="11-50">11-50 employees</option>
                    <option value="51-200">51-200 employees</option>
                    <option value="201-500">201-500 employees</option>
                    <option value="500+">500+ employees</option>
                </select>
                <x-input-error :messages="$errors->get('company_size')" class="mt-2" />
            </div>

            <!-- Country -->
            <div class="mb-6">
                <x-input-label for="country" :value="__('Country')" />
                <select id="country" name="country"
                    class="block mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-500">
                    <option value="" disabled selected>Select country</option>
                  @foreach((new \Monarobase\CountryList\CountryList)->getList('en') as $code => $name)
                        <option value="{{ $code }}" {{ old('country') == $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('country')" class="mt-2" />
            </div>

            <!-- Continue Button -->
            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Continue
            </button>

        </form>
    </div>

</x-guest-layout>
