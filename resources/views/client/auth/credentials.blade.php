<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'XPlatforms') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-100 min-h-screen flex flex-col">

    {{-- Top Navigation Bar --}}
    <div class="w-full bg-white border-b border-gray-200 px-6 py-3 flex items-center">
        <a href="{{ route('client.register.configure.actions') }}"
           class="flex flex-col items-center gap-0.5 text-gray-500 hover:text-gray-800 transition w-24">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="text-xs">Back</span>
        </a>
        <div class="flex-1 flex items-center justify-center">
            <span class="text-sm text-gray-500 font-medium">Almost done</span>
        </div>
        <div class="w-24"></div>
    </div>

    {{-- Page Content --}}
    <div class="flex-1 flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-xl bg-white shadow-md rounded-2xl overflow-hidden">

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
        </div>
    </div>

</body>
</html>
