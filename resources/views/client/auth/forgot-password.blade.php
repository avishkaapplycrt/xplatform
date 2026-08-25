<x-guest-layout title="Forgot Password" backUrl="{{ route('client.login') }}" previousUrl="{{ route('client.login') }}" :currentStep="1">

    <div class="px-10 py-10">

        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor"
                     stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Forgot your password?</h2>
            <p class="text-sm text-gray-400 mt-2 leading-relaxed">
                Enter your registered email and we'll send<br>you a link to reset your password.
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-5">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('client.password.email') }}">
            @csrf

            <div class="mb-6">
                <x-input-label for="email" :value="__('Email address')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email"
                    name="email" :value="old('email')"
                    placeholder="you@company.com"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Send reset link
            </button>

        </form>

        <p class="text-center text-sm text-gray-400 mt-5">
            Remembered it?
            <a href="{{ route('client.login') }}" class="text-blue-500 hover:text-blue-700 font-medium">Back to sign in</a>
        </p>

    </div>

</x-guest-layout>
