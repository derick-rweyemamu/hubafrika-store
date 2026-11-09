@php
    $firstName = trim((string) auth()->user()->name) ?: 'there';

    $deltaTone = [
        'up' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        'down' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
        'flat' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
    ];

    $statusTone = [
        'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        'processing' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300',
        'shipped' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
        'delivered' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        'cancelled' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
    ];

    $paymentTone = [
        'paid' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        'awaiting_verification' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        'failed' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
        'refunded' => 'bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300',
        'pending' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
    ];

    // The KPI row adapts to what the viewer is allowed to see: money figures
    // are replaced by catalogue health for staff without order access.
    $cards = [];

    if ($sales) {
        $cards[] = [
            'label' => 'Revenue · 30 days',
            'value' => $money($sales['revenue']['value']),
            'note' => 'vs previous 30 days',
            'delta' => $sales['revenue']['delta'],
            'spark' => $sales['revenue']['spark'],
            'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'tone' => 'admin-primary-soft-bg admin-primary-text',
        ];

        $cards[] = [
            'label' => 'Orders · 30 days',
            'value' => number_format($sales['orders']['value']),
            'note' => 'vs previous 30 days',
            'delta' => $sales['orders']['delta'],
            'spark' => $sales['orders']['spark'],
            'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z',
            'tone' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
        ];

        $cards[] = [
            'label' => 'Average order',
            'value' => $money($sales['average']['value']),
            'note' => 'vs previous 30 days',
            'delta' => $sales['average']['delta'],
            'spark' => $sales['average']['spark'],
            'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
            'tone' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300',
        ];
    }

    $customerCard = [
        'label' => 'New customers · 30 days',
        'value' => number_format($customers['value']),
        'note' => number_format($customers['total']).' customers in total',
        'delta' => $customers['delta'],
        'spark' => $customers['spark'],
        'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'tone' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300',
    ];

    if ($sales) {
        $cards[] = $customerCard;
    } else {
        $cards[] = [
            'label' => 'Live products',
            'value' => number_format($catalog['published']),
            'note' => number_format($catalog['total']).' in the catalogue',
            'delta' => null,
            'spark' => '',
            'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
            'tone' => 'admin-primary-soft-bg admin-primary-text',
        ];

        $cards[] = [
            'label' => 'Hidden products',
            'value' => number_format(max(0, $catalog['total'] - $catalog['published'])),
            'note' => 'Not visible in the shop',
            'delta' => null,
            'spark' => '',
            'icon' => 'M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88',
            'tone' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-300',
        ];

        $cards[] = [
            'label' => 'Low stock',
            'value' => number_format($catalog['low_stock_count']),
            'note' => number_format($catalog['out_of_stock_count']).' out of stock',
            'delta' => null,
            'spark' => '',
            'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
            'tone' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300',
        ];

        $cards[] = $customerCard;
    }

    $showLowStockAlert = $abilities['inventory'] && $attention['low_stock'] > 0;
    $hasAlerts = $attention['pending_orders'] > 0 || $attention['awaiting_payments'] > 0 || $showLowStockAlert;

    $actions = [];

    if ($abilities['products']) {
        $actions[] = ['label' => 'Add a product', 'href' => route('admin.products'), 'icon' => 'M12 4.5v15m7.5-7.5h-15'];
    }

    if ($abilities['orders']) {
        $actions[] = ['label' => 'Review orders', 'href' => route('admin.orders'), 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
    }

    if ($abilities['inventory']) {
        $actions[] = ['label' => 'Adjust stock', 'href' => route('admin.inventory'), 'icon' => 'M3 7.5L12 3m0 0l9 4.5M12 3v18m9-13.5V16.5L12 21m9-13.5L12 12M3 7.5V16.5L12 21M3 7.5L12 12'];
    }

    if ($abilities['customers']) {
        $actions[] = ['label' => 'View customers', 'href' => route('admin.customers'), 'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'];
    }

    if ($abilities['cms']) {
        $actions[] = ['label' => 'Edit CMS pages', 'href' => route('admin.cms.pages'), 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'];
    }

    if ($abilities['settings']) {
        $actions[] = ['label' => 'Store settings', 'href' => route('admin.settings'), 'icon' => 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z M15 12a3 3 0 11-6 0 3 3 0 016 0z'];
    }
@endphp

<div class="space-y-5 sm:space-y-6">

    {{-- Greeting --------------------------------------------------------- --}}
    <header class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
        <div class="admin-primary-soft-bg pointer-events-none absolute -right-20 -top-24 h-56 w-56 rounded-full opacity-80 blur-2xl" aria-hidden="true"></div>

        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ now()->format('l, j F Y') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Welcome back, {{ $firstName }}</h2>
                <p class="mt-1 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                    @if ($sales)
                        {{ number_format($sales['orders']['value']) }} orders and {{ $money($sales['revenue']['value']) }} in sales over the last 30 days.
                    @else
                        A live look at the catalogue, stock levels, and the people shopping with you.
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('shop') }}" target="_blank" rel="noopener"
                   class="rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    View storefront
                </a>
                <button type="button" wire:click="$refresh"
                        class="admin-primary-bg inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold text-white shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </header>

    {{-- Needs attention ------------------------------------------------ --}}
    @if ($hasAlerts)
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @if ($attention['pending_orders'] > 0)
                <a href="{{ route('admin.orders') }}" wire:navigate
                   class="group flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50/70 px-4 py-3 transition hover:border-amber-300 dark:border-amber-500/30 dark:bg-amber-500/10">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-amber-900 dark:text-amber-200">{{ number_format($attention['pending_orders']) }} {{ $attention['pending_orders'] === 1 ? 'order awaits' : 'orders await' }} processing</span>
                        <span class="block text-xs text-amber-700/80 dark:text-amber-300/80">Start fulfilment to keep delivery dates.</span>
                    </span>
                    <span class="text-amber-400 transition group-hover:translate-x-0.5">→</span>
                </a>
            @endif

            @if ($attention['awaiting_payments'] > 0)
                <a href="{{ route('admin.orders') }}" wire:navigate
                   class="group flex items-center gap-3 rounded-xl border border-sky-200 bg-sky-50/70 px-4 py-3 transition hover:border-sky-300 dark:border-sky-500/30 dark:bg-sky-500/10">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-sky-900 dark:text-sky-200">{{ number_format($attention['awaiting_payments']) }} {{ $attention['awaiting_payments'] === 1 ? 'payment needs' : 'payments need' }} verification</span>
                        <span class="block text-xs text-sky-700/80 dark:text-sky-300/80">Confirm the Lipa transaction IDs.</span>
                    </span>
                    <span class="text-sky-400 transition group-hover:translate-x-0.5">→</span>
                </a>
            @endif

            @if ($showLowStockAlert)
                <a href="{{ route('admin.inventory') }}" wire:navigate
                   class="group flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50/70 px-4 py-3 transition hover:border-rose-300 dark:border-rose-500/30 dark:bg-rose-500/10">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-rose-900 dark:text-rose-200">{{ number_format($attention['low_stock']) }} {{ $attention['low_stock'] === 1 ? 'product is' : 'products are' }} low on stock</span>
                        <span class="block text-xs text-rose-700/80 dark:text-rose-300/80">Restock before the next batch of orders.</span>
                    </span>
                    <span class="text-rose-400 transition group-hover:translate-x-0.5">→</span>
                </a>
            @endif
        </div>
    @endif

    {{-- Store is still empty ------------------------------------------- --}}
    @if ($catalog['total'] === 0)
        <section class="rounded-xl border border-dashed border-gray-300 bg-white p-6 dark:border-gray-700 dark:bg-gray-900 sm:p-8">
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="admin-primary-soft-bg admin-primary-text flex h-12 w-12 shrink-0 items-center justify-center rounded-xl">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Your catalogue is empty</h3>
                        <p class="mt-1 max-w-lg text-sm text-gray-500 dark:text-gray-400">Add your first product, set its price and stock level, and the sales panels on this page will start filling in.</p>
                    </div>
                </div>
                @if ($abilities['products'])
                    <a href="{{ route('admin.products') }}" wire:navigate class="admin-primary-bg shrink-0 rounded-lg px-4 py-2.5 text-sm font-semibold text-white">
                        Add a product
                    </a>
                @endif
            </div>
        </section>
    @endif

    {{-- KPI cards ------------------------------------------------------- --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <article class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                        <p class="mt-2 truncate text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ $card['value'] }}</p>
                    </div>
                    <span class="{{ $card['tone'] }} flex h-10 w-10 shrink-0 items-center justify-center rounded-lg">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                        </svg>
                    </span>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @if ($card['delta'])
                        <span @class([
                            'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold',
                            $deltaTone[$card['delta']['direction']],
                        ])>
                            @if ($card['delta']['direction'] === 'up')
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                            @elseif ($card['delta']['direction'] === 'down')
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                            @endif
                            {{ $card['delta']['label'] }}
                        </span>
                    @endif
                    <span class="truncate text-[11px] font-medium text-gray-400">{{ $card['note'] }}</span>
                </div>

                @if ($card['spark'] !== '')
                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="admin-primary-text mt-3 h-10 w-full opacity-40" aria-hidden="true">
                        <path d="{{ $card['spark'] }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                    </svg>
                @endif
            </article>
        @endforeach
    </div>

    @if ($sales)
        {{-- Sales trend + order pipeline --------------------------------- --}}
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Sales trend</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {{ $money($sales['trend_revenue']) }} from {{ number_format($sales['trend_orders']) }} {{ $sales['trend_orders'] === 1 ? 'order' : 'orders' }} · last {{ $trendDays }} days
                        </p>
                    </div>

                    <div class="flex rounded-lg bg-gray-100 p-0.5 dark:bg-gray-800" role="group" aria-label="Chart period">
                        @foreach ([7, 14, 30] as $days)
                            <button type="button" wire:click="setTrendDays({{ $days }})"
                                    @if ($trendDays === $days) aria-pressed="true" @endif
                                    @class([
                                        'rounded-md px-2.5 py-1 text-xs font-semibold transition',
                                        'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-gray-100' => $trendDays === $days,
                                        'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $trendDays !== $days,
                                    ])>
                                {{ $days }}d
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5">
                    <div class="relative h-44 w-full">
                        <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="h-full w-full" aria-hidden="true">
                            <defs>
                                <linearGradient id="dashboard-revenue-gradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" style="stop-color: var(--admin-primary); stop-opacity: 0.30"/>
                                    <stop offset="100%" style="stop-color: var(--admin-primary); stop-opacity: 0"/>
                                </linearGradient>
                            </defs>

                            @foreach ([20, 40, 60, 80] as $gridLine)
                                <line x1="0" y1="{{ $gridLine }}" x2="100" y2="{{ $gridLine }}" stroke="currentColor" stroke-width="1"
                                      vector-effect="non-scaling-stroke" class="text-gray-100 dark:text-gray-800"/>
                            @endforeach

                            <path d="{{ $sales['chart']['area'] }}" fill="url(#dashboard-revenue-gradient)"/>
                            <path d="{{ $sales['chart']['line'] }}" fill="none" stroke="currentColor" stroke-width="2.5"
                                  stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"
                                  style="color: var(--admin-primary)"/>
                        </svg>

                        @if ($sales['trend_revenue'] <= 0)
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <p class="rounded-lg bg-white/80 px-3 py-1.5 text-xs font-medium text-gray-500 shadow-sm dark:bg-gray-900/80 dark:text-gray-400">
                                    No sales recorded in this period yet
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-2 flex justify-between text-[11px] font-medium text-gray-400">
                        @foreach ($sales['chart']['labels'] as $label)
                            <span>{{ $label }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3 text-xs dark:border-gray-800">
                    <span class="text-gray-500 dark:text-gray-400">
                        {{ $sales['chart']['max'] > 0 ? 'Best day '.$money($sales['chart']['max']) : 'No sales in this period' }}
                    </span>
                    <a href="{{ route('admin.orders') }}" wire:navigate class="admin-primary-text font-semibold hover:underline">Open orders →</a>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-baseline justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Order pipeline</h3>
                    <span class="text-xs text-gray-400">{{ number_format($pipeline['total']) }} all time</span>
                </div>

                @if ($pipeline['total'] > 0)
                    <div class="mt-4 flex h-3 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800" role="img" aria-label="Orders grouped by status">
                        @foreach ($pipeline['rows'] as $row)
                            @if ($row['count'] > 0)
                                <div class="{{ $row['bar'] }}" style="width: {{ $row['percent'] }}%" title="{{ $row['label'] }}: {{ number_format($row['count']) }}"></div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <ul class="mt-4 space-y-0.5">
                    @foreach ($pipeline['rows'] as $row)
                        <li>
                            <a href="{{ route('admin.orders') }}" wire:navigate
                               class="flex items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                                <span class="h-2 w-2 shrink-0 rounded-full {{ $row['bar'] }}"></span>
                                <span class="flex-1 text-sm text-gray-600 dark:text-gray-300">{{ $row['label'] }}</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['count']) }}</span>
                                <span class="w-11 text-right text-xs tabular-nums text-gray-400">{{ $row['percent'] }}%</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                    <p class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                        Cancelled orders are excluded from revenue, so the numbers above can differ from the total.
                    </p>
                </div>
            </section>
        </div>

        {{-- Top sellers + low stock -------------------------------------- --}}
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <section class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-baseline justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Top sellers · 30 days</h3>
                    <a href="{{ route('admin.orders') }}" wire:navigate class="text-xs font-semibold text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200">Orders →</a>
                </div>

                @if ($topProducts->isEmpty())
                    <p class="mt-6 flex-1 rounded-lg border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Nothing has been sold in the last 30 days yet.
                    </p>
                @else
                    <ul class="mt-3 divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($topProducts as $product)
                            <li class="flex items-center gap-3 py-3">
                                @if ($product['thumbnail'])
                                    <img src="{{ $product['thumbnail'] }}" alt="" class="h-10 w-10 shrink-0 rounded-lg object-cover">
                                @else
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-[10px] font-semibold uppercase text-gray-400 dark:bg-gray-800">
                                        {{ mb_substr($product['name'], 0, 2) }}
                                    </span>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-baseline justify-between gap-3">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product['name'] }}</p>
                                        <p class="shrink-0 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $money($product['revenue']) }}</p>
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                            <div class="admin-primary-bg h-full rounded-full" style="width: {{ max(4, $product['percent']) }}%"></div>
                                        </div>
                                        <span class="w-16 shrink-0 text-right text-[11px] text-gray-500 dark:text-gray-400">{{ number_format($product['units']) }} sold</span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-baseline justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Low stock</h3>
                    <span class="text-xs text-gray-400">
                        {{ number_format($catalog['low_stock_count']) }} flagged
                        @if ($catalog['out_of_stock_count'] > 0)
                            · {{ number_format($catalog['out_of_stock_count']) }} out
                        @endif
                    </span>
                </div>

                @if ($catalog['low_stock_products']->isEmpty())
                    <div class="mt-6 flex flex-1 flex-col items-center justify-center rounded-lg border border-dashed border-gray-200 px-4 py-8 text-center dark:border-gray-700">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200">Every published product is well stocked.</p>
                    </div>
                @else
                    <ul class="mt-3 divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($catalog['low_stock_products'] as $product)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $product->current_stock <= 0 ? 'Out of stock' : $product->current_stock.' left' }}
                                        @if ($product->low_stock_quantity)
                                            · alerts at {{ $product->low_stock_quantity }}
                                        @endif
                                    </p>
                                </div>
                                <span @class([
                                    'shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold',
                                    'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' => $product->current_stock <= 0,
                                    'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' => $product->current_stock > 0,
                                ])>
                                    {{ $product->current_stock <= 0 ? 'Restock' : 'Low' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($abilities['inventory'])
                    <div class="mt-auto border-t border-gray-100 pt-3 dark:border-gray-800">
                        <a href="{{ route('admin.inventory') }}" wire:navigate class="admin-primary-text text-xs font-semibold hover:underline">Manage inventory →</a>
                    </div>
                @endif
            </section>
        </div>

        {{-- Recent orders + quick actions -------------------------------- --}}
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                <div class="flex items-baseline justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent orders</h3>
                    <a href="{{ route('admin.orders') }}" wire:navigate class="text-xs font-semibold text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200">View all →</a>
                </div>

                @if ($recentOrders->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        New orders land here the moment a customer checks out.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-800/60">
                                <tr>
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Order</th>
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Payment</th>
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-5 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th>
                                    <th class="px-5 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Placed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($recentOrders as $order)
                                    <tr wire:key="dashboard-order-{{ $order->id }}" class="transition hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-5 py-3">
                                            <p class="max-w-[20ch] truncate font-semibold text-gray-900 dark:text-gray-100" title="{{ $order->order_number }}">{{ $order->order_number }}</p>
                                            <p class="max-w-[20ch] truncate text-xs text-gray-500 dark:text-gray-400">{{ $order->customer_name }}</p>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3">
                                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $paymentTone[$order->payment_status] ?? $paymentTone['pending'] }}">
                                                {{ $order->paymentStatusLabel() }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3">
                                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusTone[$order->status] ?? $paymentTone['pending'] }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $order->money($order->total) }}
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3 text-right text-xs text-gray-500 dark:text-gray-400">
                                            {{ $order->created_at?->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <div class="space-y-4">
                @if ($actions !== [])
                    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Quick actions</h3>

                        <div class="mt-3 grid gap-2">
                            @foreach ($actions as $action)
                                <a href="{{ $action['href'] }}" wire:navigate
                                   @class([
                                       'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition',
                                       'admin-primary-bg text-white' => $loop->first,
                                       'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' => ! $loop->first,
                                   ])>
                                    <span @class([
                                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-md',
                                        'bg-white/20 text-white' => $loop->first,
                                        'admin-primary-soft-bg admin-primary-text' => ! $loop->first,
                                    ])>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $action['icon'] }}"/>
                                        </svg>
                                    </span>
                                    <span class="flex-1">{{ $action['label'] }}</span>
                                    <span @class(['text-sm transition group-hover:translate-x-0.5', 'text-white/70' => $loop->first, 'text-gray-300 dark:text-gray-600' => ! $loop->first])>→</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-baseline justify-between gap-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Newest customers</h3>
                        @if ($abilities['customers'])
                            <a href="{{ route('admin.customers') }}" wire:navigate class="text-xs font-semibold text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200">All →</a>
                        @endif
                    </div>

                    @if ($customers['recent']->isEmpty())
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No customers have signed up yet.</p>
                    @else
                        <ul class="mt-2 divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($customers['recent'] as $customer)
                                <li class="flex items-center gap-3 py-2.5">
                                    <span class="admin-primary-soft-bg admin-primary-text flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold">
                                        {{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $customer->name }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $customer->email }}</p>
                                    </div>
                                    <span class="shrink-0 text-[11px] text-gray-400">{{ $customer->created_at?->diffForHumans() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
        </div>
    @else
        {{-- Staff without order access still get the catalogue picture --- --}}
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <section class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Low stock</h3>

                @if ($catalog['low_stock_products']->isEmpty())
                    <p class="mt-6 flex flex-1 items-center justify-center rounded-lg border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Every published product is well stocked.
                    </p>
                @else
                    <ul class="mt-3 divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($catalog['low_stock_products'] as $product)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</p>
                                <span class="shrink-0 text-xs font-semibold text-amber-600 dark:text-amber-300">{{ $product->current_stock }} left</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            @if ($actions !== [])
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Quick actions</h3>

                    <div class="mt-3 grid gap-2">
                        @foreach ($actions as $action)
                            <a href="{{ $action['href'] }}" wire:navigate
                               class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                <span class="admin-primary-soft-bg admin-primary-text flex h-8 w-8 shrink-0 items-center justify-center rounded-md">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $action['icon'] }}"/>
                                    </svg>
                                </span>
                                <span class="flex-1">{{ $action['label'] }}</span>
                                <span class="text-sm text-gray-300 dark:text-gray-600">→</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    @endif
</div>
