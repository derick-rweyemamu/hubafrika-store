{{--
    The 403 page — what the admin middleware throws when an account may not
    open the panel, and what a denied policy looks like on the storefront.
--}}
@php
    $isAdmin = request()->is('admin', 'admin/*');
    $signedOut = auth()->guest();
@endphp

@include('errors.partials.page', [
    'context' => $isAdmin ? 'admin' : 'frontend',
    'code' => 403,
    'heading' => __('Access denied'),
    'description' => $isAdmin
        ? ($signedOut
            ? __('You need to sign in with an administrator account before the admin panel will open.')
            : __('Your account does not have permission for this screen. Ask an administrator to grant you the matching permission.'))
        : __('You do not have permission to open this page. If a link brought you here, it may have expired or belong to another account.'),
    'actions' => $isAdmin
        ? ($signedOut
            ? [
                ['label' => __('Sign in'), 'href' => route('login'), 'tone' => 'primary'],
                ['label' => __('Back to storefront'), 'href' => route('shop'), 'tone' => 'secondary'],
            ]
            : [
                ['label' => __('Back to dashboard'), 'href' => route('admin.dashboard'), 'tone' => 'primary'],
                ['label' => __('View storefront'), 'href' => route('shop'), 'tone' => 'secondary'],
            ])
        : ($signedOut
            ? [
                ['label' => __('Back to storefront'), 'href' => route('home'), 'tone' => 'primary'],
                ['label' => __('Sign in'), 'href' => route('login'), 'tone' => 'secondary'],
            ]
            : [
                ['label' => __('Back to storefront'), 'href' => route('home'), 'tone' => 'primary'],
                ['label' => __('My orders'), 'href' => route('orders.index'), 'tone' => 'secondary'],
            ]),
    'hint' => $isAdmin ? true : null,
])
