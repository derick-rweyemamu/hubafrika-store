@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'autocomplete' => null,
    'autofocus' => false,
    'inputmode' => null,
    'placeholder' => null,
])

@php
    $id = $attributes->get('id') ?? $name;
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->except('id')->class(['space-y-2']) }}>
    <label for="{{ $id }}" class="auth-label">{{ $label }}</label>

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        class="auth-input"
        @if ($value !== null) value="{{ $value }}" @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($inputmode) inputmode="{{ $inputmode }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($autofocus) autofocus @endif
        required
        @if ($hasError) aria-invalid="true" @endif
    >

    @if ($hasError)
        <p class="auth-error">{{ $errors->first($name) }}</p>
    @endif
</div>
