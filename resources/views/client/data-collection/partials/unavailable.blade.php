{{-- Shown in Data Collection panels without data: a source that isn't
     connected yet, or a signal the sync doesn't bring in. Pass $message /
     $tag to say why, and $actionUrl / $actionLabel for a "Connect …" button. --}}
<div class="dc-unavailable">
  <span class="dc-soon">{{ $tag ?? 'Coming soon' }}</span>
  <p>{{ $message ?? "Sorry, this data isn't available at the moment. It will be available soon." }}</p>
  @if (!empty($actionUrl))
    <a href="{{ $actionUrl }}" class="dc-connect">{{ $actionLabel ?? 'Connect' }}</a>
  @endif
</div>
