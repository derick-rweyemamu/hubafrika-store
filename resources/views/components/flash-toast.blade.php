{{--
    Top-right flash toast.

    Rendered inside a Livewire component so it re-renders with the component
    after an action flashes a message. `message`, `error` and `status` keys are
    supported, so it also works on plain (non-Livewire) pages.
--}}
@php
    // Laravel's form controllers flash short status codes rather than sentences.
    $statusMessages = [
        'profile-updated' => __('Your profile has been updated.'),
        'password-updated' => __('Your password has been updated.'),
        'verification-link-sent' => __('A new verification link has been sent to your email address.'),
    ];

    $toasts = [];

    foreach (['message' => 'success', 'status' => 'success', 'error' => 'error'] as $key => $tone) {
        if (! session()->has($key) || blank(session($key))) {
            continue;
        }

        $text = (string) session($key);

        $toasts[] = [
            'tone' => $tone,
            'text' => $key === 'status' ? ($statusMessages[$text] ?? $text) : $text,
        ];
    }
@endphp

@if ($toasts !== [])
    <div class="pointer-events-none fixed inset-x-3 top-3 z-[9990] flex flex-col items-stretch gap-2 sm:inset-x-auto sm:right-5 sm:top-5 sm:w-full sm:max-w-sm">
        @foreach ($toasts as $toast)
            <div
                x-data="{ visible: false }"
                x-init="$nextTick(() => visible = true); setTimeout(() => visible = false, 6000)"
                x-show="visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 sm:translate-x-6 translate-y-0"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 sm:translate-x-6"
                role="{{ $toast['tone'] === 'error' ? 'alert' : 'status' }}"
                class="pointer-events-auto flex items-start gap-3 rounded-xl border bg-white p-4 shadow-lg ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10 {{ $toast['tone'] === 'error' ? 'border-red-200 dark:border-red-900' : 'border-green-200 dark:border-green-900' }}"
            >
                <span @class([
                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-full',
                    'bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-300' => $toast['tone'] === 'error',
                    'bg-green-50 text-green-600 dark:bg-green-950 dark:text-green-300' => $toast['tone'] === 'success',
                ])>
                    @if ($toast['tone'] === 'error')
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @endif
                </span>
                <p class="min-w-0 flex-1 pt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $toast['text'] }}</p>
                <button
                    type="button"
                    x-on:click="visible = false"
                    aria-label="Dismiss notification"
                    class="rounded p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endforeach
    </div>
@endif
