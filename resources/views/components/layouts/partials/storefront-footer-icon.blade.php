{{--
    Icons used by the storefront footer: contact icons drawn from the link
    scheme (tel:/mailto:) and the social networks detected in the footer menu.
--}}
@switch($name ?? null)
    @case('mail')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
            <rect x="3" y="5" width="18" height="14" rx="2.5" />
            <path d="m4 7 8 5.5L20 7" />
        </svg>
        @break

    @case('phone')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
            <path d="M6.6 3.75h2.15l1.35 3.4-1.75 1.28a11.4 11.4 0 0 0 5.22 5.22l1.28-1.75 3.4 1.35v2.15a2.4 2.4 0 0 1-2.62 2.4A14.9 14.9 0 0 1 4.2 6.37 2.4 2.4 0 0 1 6.6 3.75Z" />
        </svg>
        @break

    @case('facebook')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path fill="currentColor" d="M13.5 21v-7.5h2.55l.45-3h-3V8.68c0-.87.26-1.43 1.55-1.43h1.58V4.56c-.28-.04-1.24-.12-2.36-.12-2.33 0-3.92 1.42-3.92 4.03v2.03H7.5v3h2.85V21h3.15Z" />
        </svg>
        @break

    @case('instagram')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false">
            <rect x="3.5" y="3.5" width="17" height="17" rx="5" />
            <circle cx="12" cy="12" r="4" />
            <circle cx="17" cy="7" r="1.1" fill="currentColor" stroke="none" />
        </svg>
        @break

    @case('x')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path fill="currentColor" d="M17.53 3h3.02l-6.6 7.54L21.75 21h-6.1l-4.78-6.24L5.4 21H2.37l7.06-8.07L2.25 3h6.25l4.32 5.7L17.53 3Zm-1.06 16.2h1.67L7.6 4.7H5.8l10.67 14.5Z" />
        </svg>
        @break

    @case('whatsapp')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path fill="currentColor" d="M12 3a9 9 0 0 0-7.7 13.64L3 21l4.5-1.22A9 9 0 1 0 12 3Zm0 1.7a7.3 7.3 0 0 1 3.9 13.45l-.45.26-3 .82.72-2.63-.3-.4A7.3 7.3 0 1 1 12 4.7Zm-2.55 3.4c-.16 0-.42.06-.64.3-.22.24-.85.83-.85 2 0 1.19.87 2.33 1 2.5.12.15 1.68 2.7 4.1 3.65 2.01.8 2.42.64 2.86.6.44-.05 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.8-.76.96-.14.16-.28.18-.52.06a6.5 6.5 0 0 1-1.9-1.18 7.2 7.2 0 0 1-1.32-1.64c-.14-.24-.02-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.19-.46-.38-.4-.54-.41h-.44Z" />
        </svg>
        @break

    @case('youtube')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path fill="currentColor" d="M21.6 7.2a2.5 2.5 0 0 0-1.76-1.77C18.28 5 12 5 12 5s-6.28 0-7.84.43A2.5 2.5 0 0 0 2.4 7.2C2 8.77 2 12 2 12s0 3.23.4 4.8a2.5 2.5 0 0 0 1.76 1.77C5.72 19 12 19 12 19s6.28 0 7.84-.43a2.5 2.5 0 0 0 1.76-1.77C22 15.23 22 12 22 12s0-3.23-.4-4.8ZM10 15.2V8.8l5.2 3.2-5.2 3.2Z" />
        </svg>
        @break

    @case('linkedin')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false">
            <rect x="3.5" y="3.5" width="17" height="17" rx="3" />
            <path d="M8 10.5V17M8 7.4v.1M12 17v-3.6a2.4 2.4 0 0 1 4.8 0V17" />
        </svg>
        @break

    @case('tiktok')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false">
            <path d="M14 3.5v9.9a3.4 3.4 0 1 1-2.9-3.36" />
            <path d="M14 3.5c.4 2.3 1.95 3.75 4.3 4.05" />
        </svg>
        @break
@endswitch
