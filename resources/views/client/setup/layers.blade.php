@extends('layouts.client')

@section('title', 'Intelligence Layers')

@section('content')
<x-setup-card
    title="Intelligence layers"
    subtitle="Which stages of the pipeline run for your account. L1 collects the raw events everything else depends on, so it always stays on."
    :action="route('client.setup.layers.update')"
    submitLabel="Save layers">

    <div class="flex flex-col gap-3">
        @foreach($layers as $layer)
            <x-setup-check
                name="layers[]"
                :value="$layer->id"
                :label="$layer->code . ' — ' . $layer->name"
                :description="$layer->description"
                :checked="in_array((int) $layer->id, $selectedLayers, true)" />
        @endforeach
    </div>

</x-setup-card>
@endsection
