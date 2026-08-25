{{--
    "Finish setup" checklist. Registration only collects an account and an
    industry; everything else is seeded from industry defaults and reviewed
    here, in context. Hidden once the client dismisses it or completes it all.
--}}
@php $pct = $checklist['total'] > 0 ? round($checklist['done'] / $checklist['total'] * 100) : 0; @endphp

<div class="mb-5 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

    {{-- Header --}}
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-4">
        <div class="flex-1 min-w-0">
            <h2 class="text-sm font-semibold text-gray-900">Finish setting up</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                @if($checklist['complete'])
                    Everything's configured. Nice.
                @else
                    We've pre-configured what we can from your industry — connect a data source to start seeing results.
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3 flex-shrink-0">
            <div class="text-right">
                <span class="text-sm font-semibold text-gray-800">
                    {{ $checklist['done'] }}/{{ $checklist['total'] }}
                </span>
                <div class="w-28 h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden">
                    <div class="h-full bg-indigo-500 rounded-full transition-all duration-500"
                         style="width: {{ $pct }}%"></div>
                </div>
            </div>

            <form method="POST" action="{{ route('client.onboarding.dismiss') }}">
                @csrf
                <button type="submit"
                        title="Dismiss this checklist"
                        class="text-gray-300 hover:text-gray-500 transition p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Items --}}
    <ul class="divide-y divide-gray-50">
        @foreach($checklist['items'] as $item)
            <li class="flex items-center gap-3 px-5 py-3">

                @if($item['done'])
                    <span class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor"
                             stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                @else
                    <span class="w-5 h-5 rounded-full border-2 border-dashed border-gray-300 flex-shrink-0"></span>
                @endif

                <div class="flex-1 min-w-0">
                    <p class="text-sm {{ $item['done'] ? 'text-gray-700' : 'text-gray-900 font-medium' }}">
                        {{ $item['label'] }}
                    </p>
                    <p class="text-xs text-gray-400 truncate">{{ $item['description'] }}</p>
                </div>

                <a href="{{ $item['url'] }}"
                   class="text-xs font-semibold px-3 py-1.5 rounded-lg transition flex-shrink-0
                          {{ $item['done']
                                ? 'text-gray-500 hover:text-gray-800 hover:bg-gray-50'
                                : 'text-white bg-indigo-600 hover:bg-indigo-700' }}">
                    {{ $item['cta'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
