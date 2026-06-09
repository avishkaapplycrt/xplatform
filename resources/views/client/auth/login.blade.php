<x-guest-layout title="Sign In" backUrl="/" previousUrl="/" :currentStep="1">

    {{-- Top Banner --}}
    <div class="w-full h-40 bg-gray-100 flex items-center justify-center">
        <span class="text-3xl font-bold text-gray-300">XPlatforms</span>
    </div>

    {{-- Form Section --}}
    <div class="px-10 py-8">

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
            <p class="text-sm text-gray-400 mt-1">Sign in to your organization</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-5">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                {{ session('error') }}
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

        <form method="POST" action="{{ route('client.login') }}">
            @csrf

            <div class="mb-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email"
                    name="email" :value="old('email')" placeholder="you@company.com"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-2">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password"
                    name="password" placeholder="Enter your password"
                    required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end mb-6">
                <a href="{{ route('client.password.request') }}"
                   class="text-sm text-blue-500 hover:text-blue-700 transition">
                    Forgot password?
                </a>
            </div>

            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Sign in
            </button>
        </form>

        <div class="flex items-center my-5">
            <div class="flex-1 border-t border-gray-200"></div>
            <span class="mx-4 text-sm text-gray-400">or</span>
            <div class="flex-1 border-t border-gray-200"></div>
        </div>

        <a href="{{ route('client.register') }}"
           class="block w-full text-center border border-gray-300 text-gray-600
                  hover:bg-gray-50 font-medium py-2.5 rounded-lg transition">
            Create Organization
        </a>

    </div>

</x-guest-layout>
