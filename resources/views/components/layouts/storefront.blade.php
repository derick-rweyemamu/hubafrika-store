<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? ($businessAssets['site_name'] ?? config('app.name')) }}</title>
    @if (!empty($businessAssets['favicon']))
        <link rel="icon" type="image/png" href="{{ $businessAssets['favicon'] }}">
    @endif

    <!-- Fonts: the families selectable in the admin panel -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="storefront-theme min-h-screen bg-gray-50 font-sans text-gray-900 dark:bg-gray-950 dark:text-gray-100"
      style="--storefront-primary: {{ $storefrontPalette['primary'] ?? '#F59E0B' }}; --storefront-secondary: {{ $storefrontPalette['secondary'] ?? '#0F172A' }}; --storefront-accent: {{ $storefrontPalette['accent'] ?? '#10B981' }}; --storefront-bg: {{ $storefrontPalette['bg'] ?? '#FFFBEB' }}; --storefront-surface: {{ $storefrontPalette['surface'] ?? '#FFFFFF' }}; --storefront-text: {{ $storefrontPalette['text'] ?? '#1F2937' }}; --storefront-bg-dark: {{ $storefrontPalette['bg_dark'] ?? '#111827' }}; --storefront-surface-dark: {{ $storefrontPalette['surface_dark'] ?? '#1F2937' }}; --storefront-text-dark: {{ $storefrontPalette['text_dark'] ?? '#F9FAFB' }}; font-family: {{ $storefrontFonts['body'] ?? 'Inter, sans-serif' }};">
    <div class="flex min-h-screen flex-col">
        {{-- Content pages read the header menu, commerce pages the store menu --}}
        @include('components.layouts.navigation', ['navigationArea' => $navigationArea ?? 'header'])

        @if (! empty($pageHeader))
            @include('components.layouts.partials.page-header', ['pageHeader' => $pageHeader])
        @endif

        <main class="{{ ($fullWidth ?? false) ? 'w-full flex-1' : 'mx-auto w-full max-w-[1200px] flex-1 px-5 py-8' }}">
            @if (isset($slot) && !empty((string) $slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>

        @include('components.layouts.partials.storefront-footer')
    </div>
    @include('components.layouts.partials.whatsapp-float')
    <x-confirm-dialog />
    @livewireScripts
</body>
</html>
