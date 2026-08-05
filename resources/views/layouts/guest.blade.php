@props([
    'backUrl' => '/',
    'previousUrl' => null,
    'currentStep' => 1,
    'totalSteps' => 2,
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-100 min-h-screen flex flex-col">

        {{-- Top Navigation Bar --}}
        <div class="w-full bg-white border-b border-gray-200 px-6 py-3 flex items-center">

            {{-- Left: Back --}}
            <a href="{{ $backUrl ?? '/' }}"
               class="flex flex-col items-center gap-0.5 text-gray-500 hover:text-gray-800 transition w-24">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="text-xs">Back</span>
            </a>

            {{-- Center: Previous + Step Dots --}}
            <div class="flex-1 flex items-center justify-center gap-6">

                {{-- Previous Button (always visible) --}}
                @if(isset($previousUrl))
                    <a href="{{ $previousUrl }}"
                       class="text-sm text-gray-500 border border-gray-300 rounded-lg px-4 py-1.5 hover:bg-gray-100 transition">
                        Previous
                    </a>
                @else
                    <span class="text-sm text-gray-300 border border-gray-200 rounded-lg px-4 py-1.5 cursor-not-allowed select-none">
                        Previous
                    </span>
                @endif

                {{-- Step Dots + Label --}}
                <div class="flex flex-col items-center gap-1">
                    <div class="flex items-center gap-1.5">
                        @for ($i = 1; $i <= ($totalSteps ?? 2); $i++)
                            <div class="rounded-full transition-all duration-300
                                {{ $i === ($currentStep ?? 1)
                                    ? 'w-5 h-2.5 bg-blue-600'
                                    : ($i < ($currentStep ?? 1)
                                        ? 'w-2.5 h-2.5 bg-blue-400'
                                        : 'w-2.5 h-2.5 bg-gray-300') }}">
                            </div>
                        @endfor
                    </div>
                    <span class="text-xs text-gray-500 font-medium">
                        Step {{ $currentStep ?? 1 }} of {{ $totalSteps ?? 2 }}
                    </span>
                </div>

            </div>

            {{-- Right: Spacer --}}
            <div class="w-24"></div>

        </div>

        {{-- Page Content --}}
        <div class="flex-1 flex items-center justify-center px-4 py-10">
            <div class="w-full max-w-xl bg-white shadow-md rounded-2xl overflow-hidden">
                {{ $slot }}
            </div>
        </div>

    </body>

    <script>
    // Prevent double-submit on all forms inside the guest layout
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                const btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-60', 'cursor-not-allowed');
                }
            });
        });
    });
    </script>
</html>