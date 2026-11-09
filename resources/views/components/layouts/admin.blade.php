@php
    // Admin chrome follows the site name saved in Business Settings.
    $siteName = trim((string) ($businessAssets['site_name'] ?? '')) ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $siteName }}</title>
    @if ($businessAssets['favicon'])
        <link rel="icon" type="image/png" href="{{ $businessAssets['favicon'] }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Runs once: theme init (no flash) + Alpine shell state --}}
    <script data-navigate-once>
        localStorage.removeItem('sidebar-collapsed')

        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('shell', () => ({
                collapsed: false,
                groups: {},
                mobileOpen: false,
                desktop: matchMedia('(min-width: 1024px)').matches,
                dark: document.documentElement.classList.contains('dark'),
                fly: null,
                timer: null,

                init() {
                    this.collapsed = false

                    const mq = matchMedia('(min-width: 1024px)')
                    mq.addEventListener('change', e => { this.desktop = e.matches; this.mobileOpen = false; this.fly = null })
                },

                // Collapsed (icon-only) only applies on desktop
                get mini() { return this.collapsed && this.desktop },

                toggleTheme() {
                    this.dark = !this.dark
                    document.documentElement.classList.toggle('dark', this.dark)
                    localStorage.theme = this.dark ? 'dark' : 'light'
                },

                toggleGroup(key) { this.groups[key] = !this.groups[key] },

                applyPalette(palette) {
                    Object.entries(palette).forEach(([key, value]) => {
                        if (key !== 'name' && /^#[0-9A-Fa-f]{6}$/.test(value)) {
                            document.body.style.setProperty(`--admin-${key.replaceAll('_', '-')}`, value)
                        }
                    })
                },

                // Floating flyout: tooltip for single links, submenu card for groups
                openFly(el, label, items = []) {
                    if (!this.mini) return
                    clearTimeout(this.timer)
                    this.fly = { label, items, top: el.getBoundingClientRect().top }
                },
                keepFly()  { clearTimeout(this.timer) },
                closeFly() { this.timer = setTimeout(() => this.fly = null, 120) },
            }))
        })
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body x-data="shell" @admin-palette-activated.window="applyPalette($event.detail.palette)"
      class="admin-theme text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100"
      style="--admin-primary: {{ $appearanceSettings['palette']['primary'] }}; --admin-secondary: {{ $appearanceSettings['palette']['secondary'] }}; --admin-accent: {{ $appearanceSettings['palette']['accent'] }}; --admin-bg: {{ $appearanceSettings['palette']['bg'] }}; --admin-surface: {{ $appearanceSettings['palette']['surface'] }}; --admin-text: {{ $appearanceSettings['palette']['text'] }}; --admin-bg-dark: {{ $appearanceSettings['palette']['bg_dark'] }}; --admin-surface-dark: {{ $appearanceSettings['palette']['surface_dark'] }}; --admin-text-dark: {{ $appearanceSettings['palette']['text_dark'] }}; font-family: {{ $appearanceSettings['fontFamily'] }};">

    {{-- Mobile overlay --}}
    <div x-cloak x-show="mobileOpen" @click="mobileOpen = false" x-transition.opacity
         class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

    @include('components.layouts.partials.sidebar')

    {{-- Main area --}}
    <div class="flex min-h-screen flex-col">

        {{-- Top bar --}}
        <header class="sticky top-0 z-20 h-16 border-b border-gray-200 bg-white/80 backdrop-blur
                       dark:border-gray-800 dark:bg-gray-900/80">
            <div :class="mini ? 'lg:ml-24' : 'lg:ml-[280px]'"
                 class="flex h-full items-center gap-3 px-4 sm:px-6">
            <button @click="mobileOpen = true" class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-800 lg:hidden" aria-label="Open menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <button type="button" @click="collapsed = !collapsed; fly = null"
                    class="hidden rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 lg:inline-flex"
                    :aria-label="mini ? 'Expand sidebar' : 'Collapse sidebar'"
                    :aria-expanded="!mini"
                    aria-controls="admin-sidebar">
                <svg x-show="!mini" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"/></svg>
                <svg x-cloak x-show="mini" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 4.5l7.5 7.5-7.5 7.5m6-15l7.5 7.5-7.5 7.5"/></svg>
            </button>

            <h1 class="text-base font-semibold">{{ $title ?? 'Dashboard' }}</h1>

            <div class="ml-auto flex items-center gap-2">
                {{-- Theme toggle --}}
                <button @click="toggleTheme()" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'">
                    <svg x-show="!dark" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75 9.75 9.75 0 018.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                    <svg x-cloak x-show="dark" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                </button>

                {{-- Settings --}}
                <a href="{{ route('admin.settings') }}" wire:navigate
                   class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                   title="{{ __('Settings') }}" aria-label="{{ __('Settings') }}"
                   @if (request()->routeIs('admin.settings')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.72 7.72 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.397-1.109-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.93 6.93 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </a>

                {{-- Signed-in user menu --}}
                <div class="relative" x-data="{ userMenu: false }"
                     @click.outside="userMenu = false" @keydown.escape.window="userMenu = false">
                    <button type="button" @click="userMenu = !userMenu" :aria-expanded="userMenu"
                            aria-haspopup="true" aria-controls="admin-user-menu"
                            class="flex items-center gap-2 rounded-full p-1 hover:bg-gray-100 dark:hover:bg-gray-800"
                            aria-label="{{ __('Account menu') }}">
                        <span class="admin-primary-bg flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </span>
                        <span class="hidden max-w-[14ch] truncate text-sm font-semibold sm:block">
                            {{ auth()->user()->name }}
                        </span>
                        <svg :class="{ 'rotate-180': userMenu }" class="hidden h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200 sm:block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>

                    <div id="admin-user-menu" x-cloak x-show="userMenu" x-transition.origin.top.right
                         class="absolute right-0 top-full z-30 mt-2 w-60 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                            <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                        </div>

                        <div class="p-1.5">
                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                {{ __('View profile') }}
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                    {{ __('Logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            <div :class="mini ? 'lg:ml-24' : 'lg:ml-[280px]'">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-gray-200 py-4 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
            <div :class="mini ? 'lg:ml-24' : 'lg:ml-[280px]'"
                 class="px-4 text-center sm:px-6">
                &copy; {{ now()->year }} {{ $siteName }}. All rights reserved.
            </div>
        </footer>
    </div>

    {{-- Floating flyout (outside the sidebar so overflow never clips it) --}}
    <div x-cloak x-show="fly" x-transition.opacity.duration.100ms
         @mouseenter="keepFly()" @mouseleave="closeFly()"
         class="fixed left-[72px] z-50 pl-2" :style="`top:${fly ? fly.top : 0}px`">

        {{-- Tooltip (single link) --}}
        <template x-if="fly && fly.items.length === 0">
            <div class="whitespace-nowrap rounded-lg bg-gray-900 px-3 py-2.5 text-sm font-medium text-white shadow-lg
                        dark:bg-gray-100 dark:text-gray-900" x-text="fly.label"></div>
        </template>

        {{-- Submenu card (group) --}}
        <template x-if="fly && fly.items.length > 0">
            <div class="min-w-48 rounded-xl border border-gray-200 bg-white p-1.5 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                <p class="px-3 pb-1 pt-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400" x-text="fly.label"></p>
                <template x-for="c in fly.items" :key="c.label">
                    <a :href="c.href" wire:navigate
                       class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700"
                       x-text="c.label"></a>
                </template>
            </div>
        </template>
    </div>

    <x-confirm-dialog />

    @livewireScripts
</body>
</html>