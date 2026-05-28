<x-guest-layout title="Your Credentials" backUrl="/app/register/configure-actions" previousUrl="/app/register/configure-actions" :currentStep="8">

    <div class="px-10 py-8">

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Set up your login</h2>
            <p class="text-sm text-gray-400 mt-1">Create the credentials you'll use to sign in</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('client.credentials.store') }}">
            @csrf

            <div class="mb-4">
                <x-input-label for="email" :value="__('Email address')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email"
                    name="email" :value="old('email')"
                    placeholder="you@companyname.com"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password"
                    name="password"
                    placeholder="Create a strong password"
                    required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mb-6">
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation"
                    placeholder="Repeat your password"
                    required autocomplete="new-password" />
            </div>

            <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Complete registration
            </button>

        </form>

    </div>

</x-guest-layout>
