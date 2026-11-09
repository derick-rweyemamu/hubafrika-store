@php
    $granularityLabels = ['day' => 'day by day', 'week' => 'week by week', 'month' => 'month by month'];

    $rangeLabel = $start->isSameDay($end)
        ? $start->format('j M Y')
        : $start->format('j M Y').' - '.$end->format('j M Y');

    $compareLabel = $previousStart->format('j M').' - '.$previousEnd->format('j M Y');

    $deltaTone = [
        'up' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        'down' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
        'flat' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
    ];

    $deltaArrow = [
        'up' => 'M4.5 15.75l7.5-7.5 7.5 7.5',
        'down' => 'M19.5 8.25l-7.5 7.5-7.5-7.5',
        'flat' => 'M4.5 12h15',
    ];

    // What each tile measures, shown when there is no previous period to compare with.
    $hints = [
        'sales' => ['vs previous period', 'vs previous period', 'vs previous period', 'vs previous period'],
        'products' => [
            'from order lines',
            'in this period',
            ($report['priced_products'] ?? 0).' with a cost price',
            'different products',
        ],
        'customers' => ['vs previous period', 'placed an order', 'accounts older than this range', 'more than one order'],
        'inventory' => ['as of today', 'as of today', 'at or below alert', 'needs restocking'],
    ];

    $statusTone = [
        'Pending' => 'bg-amber-500',
        'Processing' => 'bg-sky-500',
        'Shipped' => 'bg-indigo-500',
        'Delivered' => 'bg-emerald-500',
        'Cancelled' => 'bg-rose-500',
    ];

    $tabIcons = [
        'sales' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        'products' => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
        'customers' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'inventory' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
    ];

    $card = 'rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900';
    $th = 'px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400';
    $thRight = 'px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400';

    // Losses keep the minus sign in front of the currency, not behind it.
    $signedMoney = static fn (float|int $value): string => $value < 0
        ? '-'.$money(abs($value))
        : $money($value);
@endphp

<div class="space-y-6">
    {{-- Header ------------------------------------------------------- --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Reports</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Sales, products, customers and stock, worked out live from your orders and stock log.
            </p>
        </div>

        <button type="button" wire:click="export" wire:loading.attr="disabled" wire:target="export"
                class="admin-primary-bg inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition disabled:cursor-wait disabled:opacity-70">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            <span wire:loading.remove wire:target="export">Export CSV</span>
            <span wire:loading wire:target="export">Preparing...</span>
        </button>
    </div>

    {{-- Range picker -------------------------------------------------- --}}
    <section class="{{ $card }} p-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex flex-wrap items-center gap-1" role="group" aria-label="Report date range">
                @foreach ($presets as $key => $label)
                    <button type="button" wire:click="selectPreset('{{ $key }}')"
                            @if ($preset === $key) aria-pressed="true" @endif
                            @class([
                                'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                                'admin-primary-soft-bg admin-primary-text' => $preset === $key,
                                'text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200' => $preset !== $key,
                            ])>
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                <label for="report-from" class="sr-only">From date</label>
                <input id="report-from" type="date" wire:model.live="from"
                       class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 sm:w-auto">

                <span class="text-xs font-medium text-gray-400">to</span>

                <label for="report-to" class="sr-only">To date</label>
                <input id="report-to" type="date" wire:model.live="to"
                       class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 sm:w-auto">
            </div>
        </div>

        <p class="mt-3 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
            Showing <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $rangeLabel }}</span>
            · compared with {{ $compareLabel }}
            @if ($preset === 'custom')
                · <span class="admin-primary-text font-semibold">custom range</span>
            @endif
        </p>
    </section>

    {{-- Tabs ---------------------------------------------------------- --}}
    <div class="{{ $card }} flex flex-wrap gap-1 p-1" role="tablist" aria-label="Report type">
        @foreach ($tabs as $key => $label)
            <button type="button" wire:click="selectTab('{{ $key }}')" role="tab"
                    aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                    @class([
                        'inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition',
                        'admin-primary-soft-bg admin-primary-text' => $tab === $key,
                        'text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800/60 dark:hover:text-gray-200' => $tab !== $key,
                    ])>
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tabIcons[$key] }}"/>
                </svg>
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="space-y-4 transition-opacity" wire:loading.class.delay="opacity-50" wire:target="selectTab, selectPreset, from, to">
        {{-- Shared KPI strip --------------------------------------------- --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($report['kpis'] as $kpi)
                <div class="{{ $card }} p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $kpi['label'] }}</p>

                    <p class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                        @if ($kpi['format'] === 'money')
                            {{ $money($kpi['value']) }}
                        @elseif ($kpi['format'] === 'percent')
                            {{ number_format($kpi['value'], 1) }}%
                        @else
                            {{ number_format($kpi['value']) }}
                        @endif
                    </p>

                    <div class="mt-3 flex items-center gap-2">
                        @if ($kpi['delta'])
                            <span @class(['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold', $deltaTone[$kpi['delta']['direction']]])>
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $deltaArrow[$kpi['delta']['direction']] }}"/>
                                </svg>
                                {{ $kpi['delta']['label'] }}
                            </span>
                        @endif
                        <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $hints[$tab][$loop->index] ?? '' }}</span>
                    </div>
                </div>
            @endforeach
        </section>

        @if ($tab === 'sales')
            {{-- Revenue over time ----------------------------------------- --}}
            <section class="{{ $card }} p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Revenue over time</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {{ $rangeLabel }} · {{ $granularityLabels[$report['series']['granularity']] ?? $report['series']['granularity'] }}
                        </p>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="relative h-56 w-full">
                        <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="h-full w-full" aria-hidden="true">
                            <defs>
                                <linearGradient id="reports-revenue-gradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" style="stop-color: var(--admin-primary); stop-opacity: 0.30"/>
                                    <stop offset="100%" style="stop-color: var(--admin-primary); stop-opacity: 0"/>
                                </linearGradient>
                            </defs>

                            @foreach ([20, 40, 60, 80] as $gridLine)
                                <line x1="0" y1="{{ $gridLine }}" x2="100" y2="{{ $gridLine }}" stroke="currentColor" stroke-width="1"
                                      vector-effect="non-scaling-stroke" class="text-gray-100 dark:text-gray-800"/>
                            @endforeach

                            <path d="{{ $report['chart']['area'] }}" fill="url(#reports-revenue-gradient)"/>
                            <path d="{{ $report['chart']['line'] }}" fill="none" stroke="currentColor" stroke-width="2.5"
                                  stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"
                                  style="color: var(--admin-primary)"/>
                        </svg>

                        @if ($report['chart']['max'] <= 0)
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <p class="rounded-lg bg-white/80 px-3 py-1.5 text-xs font-medium text-gray-500 shadow-sm dark:bg-gray-900/80 dark:text-gray-400">
                                    No sales recorded in this range
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-2 flex justify-between text-[11px] font-medium text-gray-400">
                        @foreach ($report['chart']['labels'] as $label)
                            <span>{{ $label }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3 text-xs dark:border-gray-800">
                    <span class="text-gray-500 dark:text-gray-400">
                        @if ($report['chart']['max'] > 0)
                            Best {{ $report['series']['granularity'] }}: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $money($report['series']['best']['revenue']) }}</span> on {{ $report['series']['best']['label'] }}
                        @else
                            Nothing to chart yet
                        @endif
                    </span>
                    <a href="{{ route('admin.orders') }}" wire:navigate class="admin-primary-text font-semibold hover:underline">Open orders →</a>
                </div>
            </section>

            {{-- Method and status split ---------------------------------- --}}
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section class="{{ $card }} p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Payment methods</h3>

                    @if ($report['methods'] === [])
                        <p class="mt-4 rounded-lg border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            No paid activity in this range.
                        </p>
                    @else
                        <ul class="mt-4 space-y-4">
                            @foreach ($report['methods'] as $method)
                                <li>
                                    <div class="flex items-baseline justify-between gap-3">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $method['label'] }}</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $money($method['revenue']) }}</span>
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                            <div class="admin-primary-bg h-full rounded-full" style="width: {{ max($method['percent'], 2) }}%"></div>
                                        </div>
                                        <span class="w-24 shrink-0 text-right text-[11px] text-gray-500 dark:text-gray-400">
                                            {{ number_format($method['orders']) }} {{ $method['orders'] === 1 ? 'order' : 'orders' }} · {{ $method['percent'] }}%
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="{{ $card }} p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Order status</h3>

                    <ul class="mt-2 divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($report['statuses'] as $status)
                            <li class="flex items-center gap-3 py-2.5">
                                <span class="h-2 w-2 shrink-0 rounded-full {{ $statusTone[$status['label']] ?? 'bg-gray-400' }}"></span>
                                <span class="flex-1 text-sm text-gray-600 dark:text-gray-300">{{ $status['label'] }}</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($status['orders']) }}</span>
                                <span class="w-12 text-right text-xs tabular-nums text-gray-400">{{ $status['percent'] }}%</span>
                                <span class="w-28 text-right text-xs text-gray-500 dark:text-gray-400">{{ $money($status['revenue']) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-3 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        Cancelled orders are left out of revenue, so the amounts can add up to more than the period total.
                    </p>
                </section>
            </div>

            {{-- Money breakdown ------------------------------------------ --}}
            <section class="{{ $card }} p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Where the money goes</h3>

                <dl class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-5">
                    @foreach ($report['money_rows'] as $row)
                        <div class="rounded-lg bg-gray-50 px-3 py-2.5 dark:bg-gray-800/60">
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $row['label'] }}</dt>
                            <dd @class([
                                'mt-1 text-sm font-semibold',
                                'text-rose-600 dark:text-rose-400' => $row['value'] < 0,
                                'text-gray-900 dark:text-gray-100' => $row['value'] >= 0,
                            ])>
                                {{ $signedMoney($row['value']) }}
                            </dd>
                        </div>
                    @endforeach
                </dl>

                @if ($report['cancelled']['orders'] > 0)
                    <p class="mt-4 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                        {{ number_format($report['cancelled']['orders']) }}
                        {{ $report['cancelled']['orders'] === 1 ? 'cancelled order' : 'cancelled orders' }}
                        worth {{ $money($report['cancelled']['revenue']) }} {{ $report['cancelled']['orders'] === 1 ? 'sits' : 'sit' }} outside these figures.
                    </p>
                @endif
            </section>
        @endif

        @if ($tab === 'products')
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <section class="{{ $card }} p-5 xl:col-span-2">
                    <div class="flex items-baseline justify-between gap-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Revenue by category</h3>
                        <span class="text-xs text-gray-400">top 6</span>
                    </div>

                    @if ($report['categories'] === [])
                        <p class="mt-4 rounded-lg border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            No categorised sales in this range.
                        </p>
                    @else
                        <ul class="mt-4 space-y-4">
                            @foreach ($report['categories'] as $category)
                                <li>
                                    <div class="flex items-baseline justify-between gap-3">
                                        <span class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ $category['label'] }}</span>
                                        <span class="shrink-0 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $money($category['revenue']) }}</span>
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                            <div class="admin-primary-bg h-full rounded-full" style="width: {{ max($category['percent'], 2) }}%"></div>
                                        </div>
                                        <span class="w-24 shrink-0 text-right text-[11px] text-gray-500 dark:text-gray-400">
                                            {{ number_format($category['units']) }} units · {{ $category['percent'] }}%
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="{{ $card }} flex flex-col p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Selling snapshot</h3>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Average unit price</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $money($report['average_unit_price']) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Products with a cost price</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($report['priced_products']) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Published, no sales in range</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($report['unsold_products']) }}</dd>
                        </div>
                    </dl>

                    <p class="mt-auto border-t border-gray-100 pt-3 text-xs leading-relaxed text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        Gross profit only counts products with a purchase price, so it is a floor rather than the full picture.
                    </p>
                </section>
            </div>

            <section class="{{ $card }} overflow-hidden">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Product performance</h3>
                    <span class="text-xs text-gray-400">ranked by revenue</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800/70">
                            <tr>
                                <th class="{{ $th }}">Product</th>
                                <th class="{{ $thRight }}">Units</th>
                                <th class="{{ $thRight }} hidden md:table-cell">Orders</th>
                                <th class="{{ $thRight }}">Revenue</th>
                                <th class="{{ $th }} w-40">Share</th>
                                <th class="{{ $thRight }}">Profit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($report['table'] as $row)
                                <tr wire:key="report-product-{{ $loop->index }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            @if ($row['thumbnail'])
                                                <img src="{{ $row['thumbnail'] }}" alt="" class="h-9 w-9 shrink-0 rounded-lg object-cover">
                                            @else
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-[10px] font-semibold uppercase text-gray-400 dark:bg-gray-800">
                                                    {{ mb_substr($row['name'], 0, 2) }}
                                                </span>
                                            @endif
                                            <span class="max-w-[24ch] truncate font-medium text-gray-900 dark:text-gray-100" title="{{ $row['name'] }}">{{ $row['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-right text-sm tabular-nums text-gray-600 dark:text-gray-300">{{ number_format($row['units']) }}</td>
                                    <td class="hidden px-5 py-3 text-right text-sm tabular-nums text-gray-600 dark:text-gray-300 md:table-cell">{{ number_format($row['orders']) }}</td>
                                    <td class="px-5 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $money($row['revenue']) }}</td>
                                    <td class="px-5 py-3">
                                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                            <div class="admin-primary-bg h-full rounded-full" style="width: {{ max($row['share'], 2) }}%"></div>
                                        </div>
                                        <span class="mt-1 block text-[11px] text-gray-500 dark:text-gray-400">{{ $row['share'] }}% of revenue</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        @if ($row['profit'] === null)
                                            <span class="text-xs text-gray-400" title="No purchase price on this product">—</span>
                                        @else
                                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $signedMoney($row['profit']) }}</span>
                                            <span @class([
                                                'mt-0.5 block text-[11px] font-medium',
                                                'text-emerald-600 dark:text-emerald-400' => $row['margin'] >= 25,
                                                'text-amber-600 dark:text-amber-400' => $row['margin'] < 25 && $row['margin'] >= 0,
                                                'text-rose-600 dark:text-rose-400' => $row['margin'] < 0,
                                            ])>{{ $row['margin'] }}% margin</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Nothing was sold in this range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-200 px-5 py-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <span>
                        @if ($report['hidden_rows'] > 0)
                            Showing the top {{ count($report['table']) }} of {{ count($report['table']) + $report['hidden_rows'] }} products.
                        @else
                            Every product sold in this range is listed.
                        @endif
                    </span>
                    <button type="button" wire:click="export" class="admin-primary-text font-semibold hover:underline">Export the full list</button>
                </div>
            </section>
        @endif

        @if ($tab === 'customers')
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <section class="{{ $card }} overflow-hidden xl:col-span-2">
                    <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Top customers</h3>
                        <span class="text-xs text-gray-400">ranked by spend</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-800/70">
                                <tr>
                                    <th class="{{ $th }}">Customer</th>
                                    <th class="{{ $thRight }}">Orders</th>
                                    <th class="{{ $thRight }}">Spent</th>
                                    <th class="{{ $thRight }} hidden sm:table-cell">Avg order</th>
                                    <th class="{{ $thRight }} hidden md:table-cell">Last order</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($report['table'] as $row)
                                    <tr wire:key="report-customer-{{ $loop->index }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <span class="admin-primary-soft-bg admin-primary-text flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold">
                                                    {{ strtoupper(mb_substr($row['name'], 0, 1)) }}
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $row['name'] }}</p>
                                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $row['email'] }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-right text-sm tabular-nums text-gray-600 dark:text-gray-300">{{ number_format($row['orders']) }}</td>
                                        <td class="px-5 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $money($row['spent']) }}</td>
                                        <td class="hidden px-5 py-3 text-right text-sm text-gray-600 dark:text-gray-300 sm:table-cell">{{ $money($row['average']) }}</td>
                                        <td class="hidden whitespace-nowrap px-5 py-3 text-right text-xs text-gray-500 dark:text-gray-400 md:table-cell">
                                            {{ $row['last_order']?->format('j M Y') ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No signed-in customer ordered in this range.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-200 px-5 py-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <span>
                            @if ($report['hidden_rows'] > 0)
                                Showing the top {{ count($report['table']) }} of {{ $report['buyers'] }} buying customers.
                            @else
                                Every buying customer is listed.
                            @endif
                        </span>
                        <button type="button" wire:click="export" class="admin-primary-text font-semibold hover:underline">Export the full list</button>
                    </div>
                </section>

                <section class="{{ $card }} flex flex-col p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Who is buying</h3>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Spend from accounts</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $money($report['spent']) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Average spend per buyer</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $money($report['average_basket']) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Guest orders</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($report['guests']['orders']) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Guest spend</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $money($report['guests']['spent']) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Customer accounts</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($report['total_customers']) }}</dd>
                        </div>
                    </dl>

                    <p class="mt-auto border-t border-gray-100 pt-3 text-xs leading-relaxed text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        Accounts add up to {{ $report['buyers'] }} buyers here; guests check out without one, so their orders are counted separately.
                    </p>
                </section>
            </div>
        @endif

        @if ($tab === 'inventory')
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <section class="{{ $card }} overflow-hidden xl:col-span-2">
                    <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Stock movements</h3>
                        <span class="text-xs text-gray-400">{{ $rangeLabel }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-800/70">
                                <tr>
                                    <th class="{{ $th }}">Reason</th>
                                    <th class="{{ $thRight }}">Changes</th>
                                    <th class="{{ $thRight }}">Units</th>
                                    <th class="{{ $thRight }}">Direction</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($report['rows'] as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $row['label'] }}</td>
                                        <td class="px-5 py-3 text-right text-sm tabular-nums text-gray-600 dark:text-gray-300">{{ number_format($row['changes']) }}</td>
                                        <td class="px-5 py-3 text-right text-sm font-semibold tabular-nums {{ $row['units'] < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                            {{ $row['units'] > 0 ? '+' : '' }}{{ number_format($row['units']) }}
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            @if ($row['changes'] === 0)
                                                <span class="text-xs text-gray-400">—</span>
                                            @elseif ($row['units'] < 0)
                                                <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">Out</span>
                                            @else
                                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">In</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-200 px-5 py-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        Order and cancellation rows are written automatically; the rest come from manual adjustments.
                    </div>
                </section>

                <section class="{{ $card }} flex flex-col p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Shelf health</h3>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Units on hand</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($report['units']) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Published products</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($report['products']) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Units received</dt>
                            <dd class="font-semibold text-emerald-600 dark:text-emerald-400">+{{ number_format($report['received']) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Units sold from stock</dt>
                            <dd class="font-semibold text-rose-600 dark:text-rose-400">-{{ number_format($report['shipped']) }}</dd>
                        </div>
                    </dl>

                    <p class="mt-auto border-t border-gray-100 pt-3 text-xs leading-relaxed text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        @if ($report['unpriced'] > 0)
                            {{ number_format($report['unpriced']) }} products have no purchase price, so the stock value is a floor.
                        @else
                            Every product has a purchase price, so stock value is complete.
                        @endif
                        Stock values are a snapshot of today, not of the selected range.
                    </p>
                </section>
            </div>
        @endif
    </div>
</div>
