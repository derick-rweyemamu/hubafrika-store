@php
    $isLoggedIn = auth()->check();

    // The layout names the CMS area it renders, so the chrome follows the type
    // of page instead of the visitor's login state: website pages read the
    // header menu, the shop reads the store menu.
    $navigationArea = $navigationArea ?? 'header';

    $mainMenu = $storefrontMenus[$navigationArea]['menu'] ?? null;
    $menuItems = $storefrontMenus[$navigationArea]['tree'] ?? [];
    $utilityItems = $storefrontMenus['topbar']['tree'] ?? [];

    $siteName = trim((string) ($businessAssets['site_name'] ?? config('app.name')));
    $siteNameWords = preg_split('/\s+/', $siteName) ?: [$siteName];
    $siteNameLast = array_pop($siteNameWords);
    $siteNameLead = implode(' ', $siteNameWords);
    $siteMotto = $businessAssets['site_motto'] ?? null;
    $customerName = $isLoggedIn ? \Illuminate\Support\Str::before(auth()->user()->name, ' ') : null;
@endphp

<div x-data="{ drawer: false }"
     @keydown.escape.window="drawer = false"
     x-effect="document.documentElement.style.overflow = drawer ? 'hidden' : ''">

    {{-- Utility bar: admin managed top bar links, falling back to the site motto --}}
    <div class="sf-topbar">
        <div class="mx-auto flex max-w-[1200px] flex-col gap-1.5 px-5 py-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                @if (! empty($siteMotto))
                    <span class="sf-topbar-text">{{ $siteMotto }}</span>
                @endif

                @foreach ($utilityItems as $item)
                    @if ($item['href'] !== null)
                        <a href="{{ $item['href'] }}" class="sf-topbar-link">{{ $item['label'] }}</a>
                    @else
                        <span class="sf-topbar-text">{{ $item['label'] }}</span>
                    @endif
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <div class="relative" x-data="{ account: false }" @click.outside="account = false">
                        <button type="button" @click="account = !account" :aria-expanded="account"
                            class="sf-topbar-link inline-flex items-center gap-1">
                            <span>{{ __('Hi') }}, {{ $customerName }}</span>
                            <svg :class="{ 'rotate-180': account }" class="h-3 w-3 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-cloak x-show="account" x-transition.origin.top.right class="sf-account-panel absolute right-0 top-full z-50 mt-2">
                            @if (auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="sf-account-link font-semibold">{{ __('Admin dashboard') }}</a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="sf-account-link">{{ __('My account') }}</a>
                            <a href="{{ route('orders.index') }}" class="sf-account-link">{{ __('My orders') }}</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="sf-account-link sf-account-link--danger w-full">{{ __('Log out') }}</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="sf-topbar-link">{{ __('Log in') }}</a>
                    @if (Route::has('register'))
                        <span class="sf-topbar-divider" aria-hidden="true">|</span>
                        <a href="{{ route('register') }}" class="sf-topbar-link">{{ __('Create account') }}</a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    {{-- Header: logo, CMS navigation, cart --}}
    <header class="sf-header">
        <div class="sf-header-inner mx-auto flex max-w-[1200px] items-center gap-4 px-5 sm:gap-6">
            <button type="button" @click="drawer = true" :aria-expanded="drawer"
                class="sf-icon-btn sf-icon-btn--menu shrink-0 lg:hidden" aria-label="{{ __('Open navigation menu') }}" aria-controls="sf-mobile-drawer">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <a href="{{ route('home') }}" class="sf-logo inline-flex shrink-0 items-center gap-2">
                @if (! empty($businessAssets['logo']))
                    <img src="{{ $businessAssets['logo'] }}" alt="{{ $siteName }}" class="h-9 w-auto object-contain">
                @else
                    <span>{{ $siteNameLead !== '' ? $siteNameLead.' ' : '' }}<b>{{ $siteNameLast }}</b></span>
                @endif
            </a>

            <nav class="sf-nav hidden flex-1 justify-center self-stretch lg:flex"
                aria-label="{{ $mainMenu?->name ?? __('Main navigation') }}">
                <ul class="flex h-full items-stretch justify-center">
                    @foreach ($menuItems as $menuItem)
                        @include('components.layouts.partials.storefront-menu-item', ['menuItem' => $menuItem])
                    @endforeach
                </ul>
            </nav>

            <div class="ml-auto flex shrink-0 items-center gap-2 lg:ml-0">
                <livewire:storefront.cart-button />
            </div>
        </div>
    </header>

    {{-- Mobile drawer: the same CMS tree with collapsible levels --}}
    <div x-cloak x-show="drawer" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true"
        aria-label="{{ __('Navigation menu') }}">
        <div class="absolute inset-0 bg-black/50" @click="drawer = false"></div>

        <div id="sf-mobile-drawer" class="sf-drawer"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full">
            <div class="sf-drawer-head flex items-center justify-between gap-3 px-5 py-4">
                <a href="{{ route('home') }}" class="sf-logo inline-flex items-center gap-2">
                    @if (! empty($businessAssets['logo']))
                        <img src="{{ $businessAssets['logo'] }}" alt="{{ $siteName }}" class="h-8 w-auto object-contain">
                    @else
                        <span>{{ $siteNameLead !== '' ? $siteNameLead.' ' : '' }}<b>{{ $siteNameLast }}</b></span>
                    @endif
                </a>

                <button type="button" @click="drawer = false" class="sf-icon-btn sf-icon-btn--inverse shrink-0"
                    aria-label="{{ __('Close navigation menu') }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto py-3">
                <ul>
                    @foreach ($menuItems as $menuItem)
                        @include('components.layouts.partials.storefront-menu-mobile-item', ['menuItem' => $menuItem])
                    @endforeach
                </ul>

                @if ($menuItems === [])
                    <p class="sf-topbar-text px-5 py-4">{{ __('No menu items have been published yet.') }}</p>
                @endif
            </div>

            <div class="border-t px-5 py-4" style="border-color: color-mix(in srgb, var(--storefront-text) 12%, transparent)">
                <livewire:storefront.cart-button :drawer="true" />

                @auth
                    <div class="mt-3 space-y-1">
                        @if (auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="sf-account-link font-semibold">{{ __('Admin dashboard') }}</a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="sf-account-link">{{ __('My account') }}</a>
                        <a href="{{ route('orders.index') }}" class="sf-account-link">{{ __('My orders') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="sf-account-link sf-account-link--danger w-full">{{ __('Log out') }}</button>
                        </form>
                    </div>
                @else
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="sf-outline-btn">{{ __('Log in') }}</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="sf-outline-btn">{{ __('Create account') }}</a>
                        @endif
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>
