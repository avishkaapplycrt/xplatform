@extends('layouts.client')

@section('title', 'Predictive Models')

@section('content')
<x-setup-card
    title="Predictive models"
    :subtitle="'The models that run against your behavioral data. These are the ones available for ' . $industry->name . '.'"
    :action="route('client.setup.predictions.update')"
    submitLabel="Save models">

    @if($predictions->isEmpty())
        <p class="text-sm text-gray-500 text-center py-8">
            No predictive models are configured for {{ $industry->name }} yet.
        </p>
    @else
        <div class="flex flex-col gap-3">
            @foreach($predictions as $prediction)
                <x-setup-check
                    name="predictions[]"
                    :value="$prediction->id"
                    :label="$prediction->name"
                    :description="$prediction->description ?? null"
                    :checked="in_array((int) $prediction->id, $selectedIds, true)" />
            @endforeach
        </div>
    @endif

</x-setup-card>
@endsection
