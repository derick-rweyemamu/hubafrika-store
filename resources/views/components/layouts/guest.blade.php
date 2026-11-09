@props([
    'title' => null,
    'subtitle' => null,
])

@php
    $siteName = $businessAssets['site_name'] ?? config('app.name', 'Laravel');
    $heading = $title ?? __('Welcome');
    $bodyFont = $storefrontFonts['body'] ?? 'Inter, sans-serif';
    $headerFont = $storefrontFonts['header'] ?? $bodyFont;
    $headingSize = $storefrontFonts['fontSizeHeading'] ?? '1.5rem';
    $subHeadingSize = $storefrontFonts['fontSizeSub'] ?? '1.125rem';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $heading }} &middot; {{ $siteName }}</title>

        @if (! empty($businessAssets['favicon']))
            <link rel="icon" type="image/png" href="{{ $businessAssets['favicon'] }}">
        @endif

        <!-- Fonts: the families selectable in the admin panel -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="auth-theme font-sans antialiased"
          style="--storefront-primary: {{ $storefrontPalette['primary'] ?? '#F59E0B' }}; --storefront-secondary: {{ $storefrontPalette['secondary'] ?? '#0F172A' }}; --storefront-accent: {{ $storefrontPalette['accent'] ?? '#10B981' }}; --storefront-bg: {{ $storefrontPalette['bg'] ?? '#FFFBEB' }}; --storefront-surface: {{ $storefrontPalette['surface'] ?? '#FFFFFF' }}; --storefront-text: {{ $storefrontPalette['text'] ?? '#1F2937' }}; --storefront-bg-dark: {{ $storefrontPalette['bg_dark'] ?? '#111827' }}; --storefront-surface-dark: {{ $storefrontPalette['surface_dark'] ?? '#1F2937' }}; --storefront-text-dark: {{ $storefrontPalette['text_dark'] ?? '#F9FAFB' }}; font-family: {{ $bodyFont }};">
        <div class="auth-page flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
            <main class="auth-card w-full max-w-6xl overflow-hidden rounded-[1.75rem] shadow-[0_32px_80px_-32px_rgba(15,23,42,0.55)] sm:rounded-[2rem]">
                <div class="grid gap-5 p-3 sm:p-5 lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1fr)] lg:gap-8 lg:p-8">
                    <!-- Credentials panel -->
                    <section class="auth-panel order-2 flex flex-col justify-center gap-8 rounded-[1.4rem] px-5 py-9 sm:px-9 sm:py-11 lg:order-1 lg:px-12 lg:py-14">
                        <div class="space-y-6 text-center">
                            <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4" aria-label="{{ $siteName }}">
                                @if ($businessAssets['logo'] || $businessAssets['logoDark'])
                                    @if ($businessAssets['logo'])
                                        <img src="{{ $businessAssets['logo'] }}" alt="{{ $siteName }}"
                                             @class(['h-14 w-auto max-w-[11rem] object-contain', 'dark:hidden' => $businessAssets['logoDark']])>
                                    @endif
                                    @if ($businessAssets['logoDark'])
                                        <img src="{{ $businessAssets['logoDark'] }}" alt="{{ $siteName }}"
                                             @class(['h-14 w-auto max-w-[11rem] object-contain', 'hidden dark:block' => $businessAssets['logo']])>
                                    @endif
                                @else
                                    <x-application-logo class="h-11 w-11" style="color: var(--storefront-primary)" />
                                @endif
                            </a>

                            <div class="space-y-3">
                                <h1 class="auth-heading font-bold" style="font-family: {{ $headerFont }}; font-size: {{ $headingSize }};">
                                    {{ $heading }}
                                </h1>

                                @if ($subtitle)
                                    <p class="auth-muted mx-auto max-w-xs text-sm leading-relaxed">
                                        {{ $subtitle }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{ $slot }}

                        @isset($footer)
                            <div class="space-y-4">
                                <div class="h-px w-full" style="background-color: color-mix(in srgb, var(--storefront-primary) 22%, transparent)"></div>
                                <div class="text-center text-sm">
                                    {{ $footer }}
                                </div>
                            </div>
                        @endisset

                        <div class="text-center">
                            <a href="{{ url('/') }}" class="auth-link inline-flex items-center gap-1.5 text-[0.7rem] font-semibold uppercase tracking-[0.16em]">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                                </svg>
                                {{ __('Back to storefront') }}
                            </a>
                        </div>
                    </section>

                    <!-- Showcase panel -->
                    <aside class="order-1 hidden flex-col items-center justify-center gap-9 px-4 py-10 lg:order-2 lg:flex">
                        <div class="relative flex w-full max-w-md flex-col items-center gap-7">
                            <!-- Decorative foliage -->
                            <svg class="auth-showcase-leaf pointer-events-none absolute -left-8 top-24 h-48 w-24 -rotate-12" viewBox="0 0 96 200" fill="none" aria-hidden="true">
                                <path d="M48 4C32 40 10 60 10 96c0 40 22 68 38 82 16-14 38-42 38-82 0-36-22-56-38-92Z" fill="currentColor" opacity="0.55" />
                                <path d="M48 178V62" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" opacity="0.6" />
                            </svg>
                            <svg class="auth-showcase-leaf pointer-events-none absolute -right-6 top-32 h-40 w-20 rotate-12" viewBox="0 0 96 200" fill="none" aria-hidden="true">
                                <path d="M48 4C32 40 10 60 10 96c0 40 22 68 38 82 16-14 38-42 38-82 0-36-22-56-38-92Z" fill="currentColor" opacity="0.45" />
                                <path d="M48 178V62" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" opacity="0.5" />
                            </svg>

                            <!-- Floating security glyphs -->
                            <div class="relative z-10 flex items-center justify-center gap-4 sm:gap-6">
                                <span class="auth-showcase-tile flex h-10 w-10 items-center justify-center rounded-lg">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span class="auth-showcase-tile flex h-10 w-10 items-center justify-center rounded-lg">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.03 5.91c-.56-.1-1.16.03-1.56.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.82c0-.6.24-1.17.66-1.59l6.5-6.5c.4-.4.53-1 .43-1.56A6 6 0 1 1 21.75 8.25Z" />
                                    </svg>
                                </span>
                                <span class="auth-showcase-tile flex h-10 w-10 items-center justify-center rounded-lg">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round" d="M3.6 9h16.8M3.6 15h16.8" />
                                        <path d="M12 3c2.3 2.4 3.5 5.5 3.5 9s-1.2 6.6-3.5 9c-2.3-2.4-3.5-5.5-3.5-9s1.2-6.6 3.5-9Z" />
                                    </svg>
                                </span>
                                <span class="auth-showcase-tile flex h-10 w-10 items-center justify-center rounded-lg">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                        <circle cx="12" cy="12" r="3.4" />
                                        <path stroke-linecap="round" d="M12 2.6v2.2M12 19.2v2.2M21.4 12h-2.2M4.8 12H2.6M18.6 5.4l-1.6 1.6M7 17l-1.6 1.6M18.6 18.6 17 17M7 7 5.4 5.4" />
                                    </svg>
                                </span>
                            </div>

                            <!-- Browser mock -->
                            <div class="relative z-10 w-full rounded-2xl p-2" style="background-color: color-mix(in srgb, var(--storefront-primary) 12%, transparent)">
                                <div class="overflow-hidden rounded-xl bg-white shadow-xl">
                                    <div class="auth-showcase-bar flex items-center gap-1.5 px-3 py-2.5">
                                        <span class="h-1.5 w-1.5 rounded-full bg-white/55"></span>
                                        <span class="h-1.5 w-1.5 rounded-full bg-white/55"></span>
                                        <span class="h-1.5 w-1.5 rounded-full bg-white/55"></span>
                                    </div>
                                    <div class="auth-showcase-browser flex flex-col items-center gap-5 px-5 py-7 sm:px-8 sm:py-9">
                                        <svg class="h-12 w-12 text-white/85" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3.75h10.5V6.75A5.25 5.25 0 0 0 12 1.5Zm-3.75 9V6.75a3.75 3.75 0 1 1 7.5 0v3.75h-7.5Z" clip-rule="evenodd" />
                                            <path d="M4.5 10.5h15a1.5 1.5 0 0 1 1.5 1.5v9.75A1.5 1.5 0 0 1 19.5 23.25h-15A1.5 1.5 0 0 1 3 21.75V12a1.5 1.5 0 0 1 1.5-1.5Zm6 5.25a1.5 1.5 0 1 1 3 0v2.25h-3v-2.25Z" />
                                        </svg>

                                        <div class="w-full space-y-3">
                                            <div class="auth-showcase-row flex items-center gap-3 rounded-md px-3 py-2.5">
                                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M12 2.25a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9ZM7.5 12.75a5.25 5.25 0 0 0 0 10.5h9a5.25 5.25 0 0 0 0-10.5h-9Z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-[0.7rem] font-semibold uppercase tracking-[0.3em]">xxxxxxxxxx</span>
                                            </div>
                                            <div class="auth-showcase-row flex items-center gap-3 rounded-md px-3 py-2.5">
                                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-sm font-semibold tracking-[0.35em]">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="max-w-sm space-y-3 text-center">
                            <span class="auth-badge uppercase">{{ __('Secure customer access') }}</span>

                            <h2 class="auth-subheading font-semibold" style="font-family: {{ $headerFont }}; font-size: {{ $subHeadingSize }};">
                                {{ __('One account for orders, wishlists and checkout') }}
                            </h2>

                            <p class="auth-muted text-sm leading-relaxed">
                                {{ __('Sign in to :shop to track your deliveries, save products you love and check out faster.', ['shop' => $siteName]) }}
                            </p>

                            <div class="flex flex-wrap justify-center gap-2 pt-1">
                                <span class="auth-badge">{{ __('Encrypted session') }}</span>
                                <span class="auth-badge">{{ __('Order tracking') }}</span>
                                <span class="auth-badge">{{ __('Wishlist sync') }}</span>
                            </div>
                        </div>
                    </aside>
                </div>
            </main>

            <p class="mt-6 text-center text-xs font-medium text-white/85">
                &copy; {{ now()->year }} {{ $siteName }}
            </p>
        </div>

        <x-confirm-dialog />
        @livewireScripts
    </body>
</html>
