<div class="space-y-2">
    <label for="{{ $name }}" class="auth-label">{{ $label }}</label>

    <div class="relative">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $visible ? 'text' : 'password' }}"
            class="auth-input auth-input--reveal"
            autocomplete="{{ $autocomplete }}"
            @if ($autofocus) autofocus @endif
            required
            @if ($errors->has($name)) aria-invalid="true" @endif
        >

        <button type="button"
                wire:click="toggleVisibility"
                class="auth-icon-btn absolute bottom-0.5 right-0"
                aria-controls="{{ $name }}"
                aria-pressed="{{ $visible ? 'true' : 'false' }}"
                aria-label="{{ $visible ? __('Hide password') : __('Show password') }}"
                title="{{ $visible ? __('Hide password') : __('Show password') }}">
            @if ($visible)
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.243 4.243L9.878 9.878" />
                </svg>
            @else
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            @endif
        </button>
    </div>

    @if ($errors->has($name))
        <p class="auth-error">{{ $errors->first($name) }}</p>
    @endif
</div>
