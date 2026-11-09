@props(['status'])

@if ($status)
    <div {{ $attributes->class(['auth-notice']) }} role="status">
        {{ $status }}
    </div>
@endif
