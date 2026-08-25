@props([
    'title',
    'subtitle' => null,
    'action',
    'submitLabel' => 'Save changes',
])

<div class="max-w-3xl mx-auto px-4 sm:px-0">

    <a href="{{ route('client.dashboard') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 mb-4 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to dashboard
    </a>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-100">
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
            @endif
        </div>

        <form method="POST" action="{{ $action }}">
            @csrf
            @method('PUT')

            <div class="px-6 py-5">
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                <a href="{{ route('client.dashboard') }}"
                   class="text-sm text-gray-500 hover:text-gray-800 transition">Cancel</a>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold
                               px-5 py-2.5 rounded-lg transition">
                    {{ $submitLabel }}
                </button>
            </div>
        </form>
    </div>
</div>
