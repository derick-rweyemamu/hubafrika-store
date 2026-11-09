@php
    // 'nav' renders a top level header link (with hover dropdown or mega menu),
    // 'menu' renders a link inside a dropdown panel, 'list' renders stacked links.
    $variant = $variant ?? (($header ?? true) ? 'nav' : 'list');
    $depth = $depth ?? 0;

    $href = $menuItem['href'];
    $hasChildren = $menuItem['children'] !== [];
    $isMega = $hasChildren && ! empty($menuItem['mega_menu']);
    $isActive = $href !== null
        && ! str_contains((string) $href, '#')
        && rtrim((string) $href, '/') === rtrim(request()->url(), '/');
@endphp

@if ($variant === 'nav')
    <li @class([
        'sf-nav-item',
        'is-mega' => $isMega,
        'is-active' => $isActive,
        $menuItem['css_class'] ?? '',
    ])
        @if ($isMega)
            x-data="{
                placeMega() {
                    const panel = this.$refs.mega;
                    const bounds = panel?.offsetParent?.getBoundingClientRect();

                    if (!bounds) {
                        return;
                    }

                    const trigger = this.$el.getBoundingClientRect();
                    const width = Math.min(panel.offsetWidth, bounds.width);
                    const centred = trigger.left + trigger.width / 2 - bounds.left - width / 2;
                    const clamped = Math.max(0, Math.min(centred, bounds.width - width));

                    panel.style.left = Math.round(clamped) + 'px';
                },
            }"
            x-on:mouseenter="placeMega()"
            x-on:focusin="placeMega()"
            x-on:resize.window="placeMega()"
        @endif
    >
        @if ($href !== null)
            <a href="{{ $href }}" class="sf-nav-link" @if ($isActive) aria-current="page" @endif>
                <span>{{ $menuItem['label'] }}</span>
                @if (! empty($menuItem['badge']))
                    <span class="sf-badge">{{ $menuItem['badge'] }}</span>
                @endif
                @if ($hasChildren)
                    <span class="sf-nav-caret" aria-hidden="true">&#9662;</span>
                @endif
            </a>
        @else
            <span class="sf-nav-link">
                <span>{{ $menuItem['label'] }}</span>
                @if (! empty($menuItem['badge']))
                    <span class="sf-badge">{{ $menuItem['badge'] }}</span>
                @endif
                @if ($hasChildren)
                    <span class="sf-nav-caret" aria-hidden="true">&#9662;</span>
                @endif
            </span>
        @endif

        @if ($hasChildren)
            @if ($isMega)
                <div class="sf-dropdown sf-mega" x-ref="mega">
                    <div class="flex max-w-[1200px] flex-col gap-6 px-5 py-6 lg:flex-row">
                        @if (($menuItem['mega_menu_type'] ?? '') === 'image_menu'
                            && ! empty($menuItem['mega_menu_image'])
                            && ($menuItem['mega_menu_image_position'] ?? '') === 'left')
                            <div class="sf-mega-image w-full shrink-0 lg:w-[220px]">
                                @if (! empty($menuItem['mega_menu_image_link']))
                                    <a href="{{ $menuItem['mega_menu_image_link'] }}" class="block">
                                @endif
                                <img src="{{ $menuItem['mega_menu_image'] }}" alt="{{ $menuItem['mega_menu_image_title'] ?? $menuItem['label'] }}" class="h-44 w-full object-cover">
                                @if (! empty($menuItem['mega_menu_image_title']))
                                    <p class="px-3 py-2 text-xs font-bold uppercase tracking-wider">{{ $menuItem['mega_menu_image_title'] }}</p>
                                @endif
                                @if (! empty($menuItem['mega_menu_image_link']))
                                    </a>
                                @endif
                            </div>
                        @endif

                        <ul @class([
                            'grid flex-1 gap-6',
                            'grid-cols-1 lg:grid-cols-2' => $menuItem['mega_menu_columns'] === 2,
                            'grid-cols-1 lg:grid-cols-3' => $menuItem['mega_menu_columns'] === 3,
                            'grid-cols-1 lg:grid-cols-4' => $menuItem['mega_menu_columns'] === 4,
                            'grid-cols-1 lg:grid-cols-5' => $menuItem['mega_menu_columns'] === 5,
                            'grid-cols-1 lg:grid-cols-6' => $menuItem['mega_menu_columns'] === 6,
                        ])>
                            @foreach ($menuItem['mega_menu_column_groups'] as $columnIndex => $columnItems)
                                @if ($columnItems === [])
                                    @continue
                                @endif
                                <li class="sf-mega-column min-w-0" style="--sf-col: {{ $columnIndex + 1 }}">
                                    <ul class="space-y-3">
                                        @foreach ($columnItems as $child)
                                            @include('components.layouts.partials.storefront-menu-item', [
                                                'menuItem' => $child,
                                                'variant' => 'list',
                                                'depth' => $depth + 1,
                                            ])
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>

                        @if (($menuItem['mega_menu_type'] ?? '') === 'image_menu'
                            && ! empty($menuItem['mega_menu_image'])
                            && ($menuItem['mega_menu_image_position'] ?? '') === 'right')
                            <div class="sf-mega-image w-full shrink-0 lg:w-[220px]">
                                @if (! empty($menuItem['mega_menu_image_link']))
                                    <a href="{{ $menuItem['mega_menu_image_link'] }}" class="block">
                                @endif
                                <img src="{{ $menuItem['mega_menu_image'] }}" alt="{{ $menuItem['mega_menu_image_title'] ?? $menuItem['label'] }}" class="h-44 w-full object-cover">
                                @if (! empty($menuItem['mega_menu_image_title']))
                                    <p class="px-3 py-2 text-xs font-bold uppercase tracking-wider">{{ $menuItem['mega_menu_image_title'] }}</p>
                                @endif
                                @if (! empty($menuItem['mega_menu_image_link']))
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <ul class="sf-dropdown">
                    @foreach ($menuItem['children'] as $child)
                        @include('components.layouts.partials.storefront-menu-item', [
                            'menuItem' => $child,
                            'variant' => 'menu',
                            'depth' => $depth + 1,
                        ])
                    @endforeach
                </ul>
            @endif
        @endif
    </li>
@elseif ($variant === 'menu')
    <li>
        @if ($href !== null)
            <a href="{{ $href }}" @class(['sf-dropdown-link', $menuItem['css_class'] ?? ''])>
                <span>{{ $menuItem['label'] }}</span>
                @if (! empty($menuItem['badge']))
                    <span class="sf-badge">{{ $menuItem['badge'] }}</span>
                @endif
            </a>
        @else
            <span @class(['sf-dropdown-heading', $menuItem['css_class'] ?? ''])>{{ $menuItem['label'] }}</span>
        @endif

        @if ($hasChildren)
            <ul class="sf-dropdown-sub">
                @foreach ($menuItem['children'] as $child)
                    @include('components.layouts.partials.storefront-menu-item', [
                        'menuItem' => $child,
                        'variant' => 'menu',
                        'depth' => $depth + 1,
                    ])
                @endforeach
            </ul>
        @endif
    </li>
@else
    <li @class([$menuItem['css_class'] ?? ''])>
        <span class="flex items-center gap-1.5">
            @if ($href !== null)
                <a href="{{ $href }}" class="sf-menu-link">{{ $menuItem['label'] }}</a>
            @else
                <span class="sf-menu-heading">{{ $menuItem['label'] }}</span>
            @endif

            @if (! empty($menuItem['badge']))
                <span class="sf-badge">{{ $menuItem['badge'] }}</span>
            @endif
        </span>

        @if ($hasChildren)
            <ul class="sf-menu-sub">
                @foreach ($menuItem['children'] as $child)
                    @include('components.layouts.partials.storefront-menu-item', [
                        'menuItem' => $child,
                        'variant' => 'list',
                        'depth' => $depth + 1,
                    ])
                @endforeach
            </ul>
        @endif
    </li>
@endif
