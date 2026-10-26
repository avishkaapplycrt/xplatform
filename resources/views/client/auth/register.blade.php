<x-guest-layout title="Create Account" backUrl="/app/login" previousUrl="/app/login" :currentStep="1" :totalSteps="3">

    {{-- Top Banner --}}
    <div class="w-full h-40 bg-gray-100 flex items-center justify-center">
        <span class="text-3xl font-bold text-gray-300">XPlatforms</span>
    </div>

    {{-- Form Section --}}
    <div class="px-10 py-8">

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
            <p class="text-sm text-gray-400 mt-1">One more step after this and you're in</p>
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

            <hr class="border-gray-100 mb-6">

            {{-- Credentials --}}
            <div class="mb-4">
                <x-input-label for="email" :value="__('Work email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email"
                    name="email" :value="old('email')"
                    placeholder="you@companyname.com"
                    required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="password" :value="__('Password')" />
                <div style="position:relative">
                    <x-text-input id="password" class="block mt-1 w-full" type="password"
                        name="password"
                        placeholder="At least 8 characters"
                        style="padding-right:48px"
                        required autocomplete="new-password" />
                    <button type="button" class="pw-toggle" data-pw-toggle="password" aria-label="Show password" aria-pressed="false"
                        style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:38px;height:38px;display:flex;align-items:center;justify-content:center;border:0;background:transparent;color:#9ca3af;cursor:pointer;border-radius:8px">
                        <svg class="pw-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="pw-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none"><path d="M17.9 17.9A10.4 10.4 0 0112 19c-6.5 0-10-7-10-7a18.5 18.5 0 015.1-5.9M9.9 5.2A9.5 9.5 0 0112 5c6.5 0 10 7 10 7a18.6 18.6 0 01-2.2 3.2M14.1 14.1a3 3 0 01-4.2-4.2M2 2l20 20"/></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mb-6">
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <div style="position:relative">
                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                        name="password_confirmation"
                        placeholder="Repeat your password"
                        style="padding-right:48px"
                        required autocomplete="new-password" />
                    <button type="button" class="pw-toggle" data-pw-toggle="password_confirmation" aria-label="Show password" aria-pressed="false"
                        style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:38px;height:38px;display:flex;align-items:center;justify-content:center;border:0;background:transparent;color:#9ca3af;cursor:pointer;border-radius:8px">
                        <svg class="pw-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="pw-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none"><path d="M17.9 17.9A10.4 10.4 0 0112 19c-6.5 0-10-7-10-7a18.5 18.5 0 015.1-5.9M9.9 5.2A9.5 9.5 0 0112 5c6.5 0 10 7 10 7a18.6 18.6 0 01-2.2 3.2M14.1 14.1a3 3 0 01-4.2-4.2M2 2l20 20"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Create account
            </button>

        </form>

        <p class="text-center text-sm text-gray-400 mt-5">
            Already have an account?
            <a href="{{ route('client.login') }}" class="text-blue-500 hover:text-blue-700 font-medium">Sign in</a>
        </p>

    </div>

    {{-- Show / hide password: the eye button on each password box. --}}
    <script>
        document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-pw-toggle'));
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                btn.querySelector('.pw-eye').style.display = show ? 'none' : '';
                btn.querySelector('.pw-eye-off').style.display = show ? '' : 'none';
                input.focus();
            });
            btn.addEventListener('mouseenter', function () { btn.style.color = '#4b5563'; });
            btn.addEventListener('mouseleave', function () { btn.style.color = '#9ca3af'; });
        });
    </script>

</x-guest-layout>
