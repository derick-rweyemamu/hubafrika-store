{{--
    The 404 page. One view per status code, so the admin panel and the
    storefront share it and pick their skin from the request path.
--}}
@php
    $isAdmin = request()->is('admin', 'admin/*');
@endphp

@include('errors.partials.page', [
    'context' => $isAdmin ? 'admin' : 'frontend',
    'code' => 404,
    'heading' => $isAdmin ? __('Page not found') : __("We couldn't find that page"),
    'description' => $isAdmin
        ? __('This admin screen has moved, been renamed, or the link is out of date. Head back to the dashboard and pick the page again.')
        : __('The link may be broken, or the page may have been removed. Let us get you back to the shop — there is plenty waiting for you.'),
    'actions' => $isAdmin
        ? [
            ['label' => __('Back to dashboard'), 'href' => route('admin.dashboard'), 'tone' => 'primary'],
            ['label' => __('View storefront'), 'href' => route('shop'), 'tone' => 'secondary'],
        ]
        : [
            ['label' => __('Continue shopping'), 'href' => route('shop'), 'tone' => 'primary'],
            ['label' => __('Back to storefront'), 'href' => route('home'), 'tone' => 'secondary'],
        ],
    'hint' => $isAdmin ? true : null,
])
