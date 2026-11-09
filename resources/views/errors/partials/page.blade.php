{{--
    Shared shell for the error pages.

    Error views are rendered while an exception is being handled, so nothing
    here touches the database, the auth guard or the panel layouts. The two
    skins below only reuse the palette variables and the auth/admin CSS that
    app.css already ships.

    Expects: $context ('admin'|'frontend'), $code, $heading, $description,
             $actions (list of label/href/tone), and optionally $hint.
--}}
@php
    $isAdmin = ($context ?? 'frontend') === 'admin';
    $appName = config('app.name');
    $year = now()->year;
    $path = '/'.ltrim(request()->path(), '/');
    $actions = $actions ?? [];

    $bodyClass = $isAdmin
        ? 'admin-theme min-h-screen antialiased'
        : 'auth-theme min-h-screen font-sans antialiased';

    $bodyStyle = $isAdmin
        ? '--admin-primary: #4F46E5; --admin-secondary: #64748B; --admin-accent: #F59E0B; --admin-bg: #F9FAFB; --admin-surface: #FFFFFF; --admin-text: #111827; --admin-bg-dark: #030712; --admin-surface-dark: #111827; --admin-text-dark: #F9FAFB;'
        : '--storefront-primary: #F59E0B; --storefront-secondary: #0F172A; --storefront-accent: #10B981; --storefront-bg: #FFFBEB; --storefront-surface: #FFFFFF; --storefront-text: #1F2937;';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $code }} — {{ $heading }} | {{ $appName }}</title>

    @if ($isAdmin)
        {{-- The admin panel is the only area with a dark skin; mirror its choice. --}}
        <script>
            if (localStorage.theme === 'dark' ||
                (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        </script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="{{ $bodyClass }}" style="{{ $bodyStyle }}">

@if ($isAdmin)
    <div class="relative flex min-h-screen flex-col overflow-hidden">
        <div aria-hidden="true"
             class="pointer-events-none absolute -top-52 left-1/2 h-96 w-[42rem] -translate-x-1/2 rounded-full opacity-20 blur-3xl"
             style="background-color: var(--admin-primary)"></div>

        <header class="relative flex items-center justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800 sm:px-8">
            <div class="flex items-center gap-3">
                <span class="admin-primary-bg flex h-9 w-9 items-center justify-center rounded-lg text-sm font-bold text-white">
                    {{ mb_strtoupper(mb_substr($appName, 0, 1)) }}
                </span>
                <div class="leading-tight">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $appName }}</p>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">{{ __('Admin panel') }}</p>
                </div>
            </div>

            <a href="{{ route('admin.dashboard') }}"
               class="admin-primary-text text-xs font-semibold uppercase tracking-[0.14em] hover:underline">
                {{ __('Dashboard') }}
            </a>
        </header>

        <main class="relative flex flex-1 items-center justify-center px-4 py-12 sm:px-6">
            <div class="w-full max-w-xl">
                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-xl ring-1 ring-gray-900/5 dark:border-gray-800 dark:bg-gray-900 sm:p-10">
                    <span class="admin-primary-soft-bg admin-primary-text mx-auto flex h-14 w-14 items-center justify-center rounded-2xl">
                        @if ($code === 403)
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7.5 3v5.1c0 4.7-3.1 8.9-7.5 10.4-4.4-1.5-7.5-5.7-7.5-10.4V6l7.5-3z" />
                                <path stroke-linecap="round" d="M12 8.25v3.5M12 15h.01" />
                            </svg>
                        @else
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z" />
                            </svg>
                        @endif
                    </span>

                    <p class="mt-5 text-5xl font-black leading-none tracking-tight admin-primary-text">{{ $code }}</p>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100 sm:text-[1.75rem]">{{ $heading }}</h1>
                    <p class="mx-auto mt-3 max-w-sm text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $description }}</p>

                    <div class="mt-8 flex flex-col items-stretch justify-center gap-2.5 sm:flex-row sm:items-center">
                        @foreach ($actions as $action)
                            @if ($action['tone'] === 'primary')
                                <a href="{{ $action['href'] }}"
                                   class="admin-primary-bg rounded-lg px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900">
                                    {{ $action['label'] }}
                                </a>
                            @else
                                <a href="{{ $action['href'] }}"
                                   class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                                    {{ $action['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>

                    @isset($hint)
                        <p class="mt-7 truncate text-[11px] text-gray-400 dark:text-gray-500">
                            <span class="rounded bg-gray-100 px-1.5 py-0.5 font-mono font-semibold dark:bg-gray-800">{{ strtoupper(request()->method()) }}</span>
                            <span class="ml-1 font-mono">{{ \Illuminate\Support\Str::limit($path, 72) }}</span>
                        </p>
                    @endisset
                </div>

                @unless (isset($hint))
                    <p class="mt-6 truncate text-center text-[11px] text-gray-400 dark:text-gray-500">
                        <span class="font-mono">{{ \Illuminate\Support\Str::limit($path, 72) }}</span>
                    </p>
                @endunless
            </div>
        </main>

        <footer class="relative border-t border-gray-200 py-5 text-center text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
            &copy; {{ $year }} {{ $appName }} &middot; {{ __('Administration') }}
        </footer>
    </div>
@else
    <div class="auth-page flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:px-6 sm:py-14">
        <main class="auth-card w-full max-w-2xl overflow-hidden rounded-[1.75rem] shadow-[0_32px_80px_-32px_rgba(15,23,42,0.55)] sm:rounded-[2rem]">
            <div class="p-3 sm:p-5">
                <section class="auth-panel relative overflow-hidden rounded-[1.4rem] px-6 py-12 text-center sm:px-12 sm:py-16">
                    <span aria-hidden="true"
                          class="pointer-events-none absolute -top-12 right-1 select-none text-[8rem] font-black leading-none tracking-tighter"
                          style="color: color-mix(in srgb, var(--storefront-primary) 14%, transparent)">{{ $code }}</span>

                    <span class="relative mx-auto flex h-14 w-14 items-center justify-center rounded-2xl"
                          style="background-color: color-mix(in srgb, var(--storefront-primary) 16%, transparent); color: var(--storefront-primary)">
                        @if ($code === 403)
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7.5 3v5.1c0 4.7-3.1 8.9-7.5 10.4-4.4-1.5-7.5-5.7-7.5-10.4V6l7.5-3z" />
                                <path stroke-linecap="round" d="M12 8.25v3.5M12 15h.01" />
                            </svg>
                        @else
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z" />
                            </svg>
                        @endif
                    </span>

                    <p class="relative mt-6 text-[0.7rem] font-bold uppercase tracking-[0.28em]"
                       style="color: color-mix(in srgb, var(--storefront-secondary) 70%, var(--storefront-primary))">
                        {{ $appName }}
                    </p>

                    <h1 class="auth-heading relative mt-3 text-2xl font-bold sm:text-[1.75rem]">{{ $heading }}</h1>

                    <p class="auth-muted relative mx-auto mt-4 max-w-sm text-sm leading-relaxed">{{ $description }}</p>

                    <div class="relative mx-auto mt-9 w-full max-w-xs space-y-4">
                        @foreach ($actions as $action)
                            @if ($action['tone'] === 'primary')
                                <a href="{{ $action['href'] }}" class="auth-btn">{{ $action['label'] }}</a>
                            @else
                                <a href="{{ $action['href'] }}"
                                   class="auth-link inline-flex items-center justify-center gap-1.5 text-[0.7rem] font-semibold uppercase tracking-[0.16em]">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                                    </svg>
                                    {{ $action['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </section>
            </div>
        </main>

        <p class="mt-8 text-center text-[0.7rem] font-medium uppercase tracking-[0.16em]"
           style="color: color-mix(in srgb, #ffffff 72%, var(--storefront-primary))">
            &copy; {{ $year }} {{ $appName }}
        </p>
    </div>
@endif

</body>
</html>
