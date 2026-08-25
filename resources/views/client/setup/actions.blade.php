@extends('layouts.client')

@section('title', 'Automated Actions')

@section('content')
<x-setup-card
    title="Automated actions"
    subtitle="What the platform is allowed to do when a scenario fires — discounts, campaigns, win-backs and escalations."
    :action="route('client.setup.actions.update')"
    submitLabel="Save actions">

    <div class="flex flex-col gap-3">
        @foreach($actions as $action)
            <x-setup-check
                name="actions[]"
                :value="$action->id"
                :label="$action->name"
                :description="$action->description"
                :checked="in_array((int) $action->id, $selectedIds, true)" />
        @endforeach
    </div>

</x-setup-card>
@endsection
