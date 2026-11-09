@props([
    'slug' => null,
    'area' => null,
    'class' => '',
    'itemClass' => '',
    'activeClass' => '',
    'header' => true,
])

@php
    $menu = null;
    if ($slug) {
        $menu = \App\Models\CmsMenu::query()->where('slug', $slug)->first();
    } elseif ($area) {
        $menu = \App\Models\CmsMenu::forArea($area);
    } else {
        $menu = \App\Models\CmsMenu::forArea('header');
    }

    $tree = $menu ? $menu->tree() : [];
    $menuCustomClass = $menu?->css_class ?? '';
@endphp

@if ($tree !== [])
    <nav class="{{ $menuCustomClass }} {{ $class }}" aria-label="{{ $menu->name ?? 'Navigation' }}">
        <ul class="flex flex-wrap items-center gap-2 text-sm font-medium">
            @foreach ($tree as $item)
                @include('components.layouts.partials.storefront-menu-item', [
                    'menuItem' => $item,
                    'header' => $header,
                ])
            @endforeach
        </ul>
    </nav>
@endif

