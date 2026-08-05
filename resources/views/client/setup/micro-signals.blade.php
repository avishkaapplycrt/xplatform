@extends('layouts.client')

@section('title', 'Micro-Signals')

@section('content')
<x-setup-card
    title="Behavioral micro-signals"
    :subtitle="'The individual behaviours we track and feed into your scores. Pre-selected from the ' . $industry->name . ' template — turn off anything that does not apply.'"
    :action="route('client.setup.micro-signals.update')"
    submitLabel="Save micro-signals">

    @foreach($categories as $category)
        @continue($category->microSignals->isEmpty())

        <div class="mb-6 last:mb-0">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                    {{ $category->name }}
                </p>
                <button type="button"
                        class="text-xs text-indigo-600 hover:text-indigo-800 transition"
                        data-toggle-group="cat-{{ $category->id }}">
                    Toggle all
                </button>
            </div>

            <div class="grid sm:grid-cols-2 gap-2" data-group="cat-{{ $category->id }}">
                @foreach($category->microSignals as $signal)
                    <x-setup-check
                        name="signals[]"
                        :value="$signal->id"
                        :label="$signal->name"
                        :description="$signal->description ?? null"
                        :checked="in_array((int) $signal->id, $selectedSignals, true)" />
                @endforeach
            </div>
        </div>
    @endforeach

</x-setup-card>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-toggle-group]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const group = document.querySelector('[data-group="' + btn.dataset.toggleGroup + '"]');
            if (!group) return;

            const boxes    = group.querySelectorAll('input[type="checkbox"]');
            const turningOn = Array.from(boxes).some(function (b) { return !b.checked; });

            boxes.forEach(function (b) { b.checked = turningOn; });
        });
    });
</script>
@endpush
