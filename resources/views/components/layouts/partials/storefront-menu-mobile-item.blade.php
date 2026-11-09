@php
    $mobileDepth = $mobileDepth ?? 0;
    $hasChildren = $menuItem['children'] !== [];
@endphp

<li x-data="{ open: false }" class="{{ $menuItem['css_class'] ?? '' }}">
    <div class="flex items-center gap-2 pr-2">
        @if ($menuItem['href'] !== null)
            <a href="{{ $menuItem['href'] }}" class="sf-mobile-link">{{ $menuItem['label'] }}</a>
        @else
            <span class="sf-mobile-link">{{ $menuItem['label'] }}</span>
        @endif

        @if (! empty($menuItem['badge']))
            <span class="sf-badge">{{ $menuItem['badge'] }}</span>
        @endif

        @if ($hasChildren)
            <button type="button" @click="open = !open" :aria-expanded="open"
                aria-label="{{ __('Toggle :label submenu', ['label' => $menuItem['label']]) }}"
                class="sf-mobile-toggle ml-auto shrink-0">
                <svg :class="{ 'rotate-180': open }" class="h-4 w-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        @endif
    </div>

    @if ($hasChildren)
        <ul x-cloak x-show="open" class="sf-mobile-sub">
            @foreach ($menuItem['children'] as $child)
                @include('components.layouts.partials.storefront-menu-mobile-item', [
                    'menuItem' => $child,
                    'mobileDepth' => $mobileDepth + 1,
                ])
            @endforeach
        </ul>
    @endif
</li>
