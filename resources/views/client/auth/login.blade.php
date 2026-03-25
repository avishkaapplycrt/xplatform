<x-guest-layout backUrl="/" previousUrl="/" :currentStep="1">

    {{-- Top Banner / Image Placeholder --}}
    <div class="w-full h-56 bg-gray-100 flex items-center justify-center">
        <span class="text-4xl font-bold text-gray-400">Web image</span>
    </div>

    {{-- Form Section --}}
    <div class="px-10 py-8">

        {{-- Title --}}
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Welcome to XPlatforms</h2>
            <p class="text-sm text-gray-400 mt-1">Login or create your organization</p>
        </div>

        {{-- Login Form --}}
        <form method="POST" action="/app/login">
            @csrf

            <!-- Email -->
            <div class="mb-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email"
                    name="email" :value="old('email')" placeholder="you@companyname.com"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
<div class="mb-6">
    <x-input-label for="password" :value="__('Password')" />
    <x-text-input id="password" class="block mt-1 w-full" type="password"
        name="password" placeholder="Enter password"
        required autocomplete="current-password" />
    <x-input-error :messages="$errors->get('password')" class="mt-2" />
    <div class="flex justify-end mt-1">
        <a href="/forgot-password" class="text-sm text-blue-500 hover:text-blue-700 transition">
            Forgot password?
        </a>
    </div>
</div>




            <!-- Login Button -->
            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Login
            </button>
        </form>

        {{-- Divider --}}
        <div class="flex items-center my-5">
            <div class="flex-1 border-t border-gray-200"></div>
            <span class="mx-4 text-sm text-gray-400">or</span>
            <div class="flex-1 border-t border-gray-200"></div>
        </div>

        {{-- Create Organization --}}
        <a href="/app/register"
           class="block w-full text-center border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium py-2.5 rounded-lg transition">
            Create Organization
        </a>

    </div>

</x-guest-layout>
