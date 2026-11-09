@props(['active' => 'info'])

@php
    /**
     * One navigation row for the whole customer account area. The profile
     * panels keep their "?tab=" URLs, so every section shares a single level
     * of navigation instead of nesting a second tab bar inside the first.
     */
    $tabs = [
        [
            'key' => 'info',
            'label' => __('Profile info'),
            'href' => route('profile.edit'),
            'icon' => 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z',
        ],
        [
            'key' => 'orders',
            'label' => __('Orders'),
            'href' => route('orders.index'),
            'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12A1.125 1.125 0 0119.75 21H4.25a1.125 1.125 0 01-1.119-1.243l1.264-12A1.125 1.125 0 015.513 8.25h12.974c.576 0 1.059.435 1.119 1.007z',
        ],
        [
            'key' => 'payments',
            'label' => __('Payments'),
            'href' => route('payments.index'),
            'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
        ],
        [
            'key' => 'update',
            'label' => __('Profile update'),
            'href' => route('profile.edit', ['tab' => 'update']),
            'icon' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z',
        ],
        [
            'key' => 'security',
            'label' => __('Account update'),
            'href' => route('profile.edit', ['tab' => 'security']),
            'icon' => 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
        ],
    ];
@endphp

<nav class="pf-tabs" aria-label="{{ __('Account sections') }}">
    @foreach ($tabs as $tab)
        <a href="{{ $tab['href'] }}"
           @class(['pf-tab', 'is-active' => $active === $tab['key']])
           @if ($active === $tab['key']) aria-current="page" @endif>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/></svg>
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
