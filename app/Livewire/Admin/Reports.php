<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DrawsCharts;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Reports extends Component
{
    use DrawsCharts;

    /** Report screens, in the order the tabs are shown. */
    public const TABS = [
        'sales' => 'Sales',
        'products' => 'Products',
        'customers' => 'Customers',
        'inventory' => 'Inventory',
    ];

    /** Date ranges offered above every report. */
    public const PRESETS = [
        'today' => 'Today',
        'last_7' => '7 days',
        'last_30' => '30 days',
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'this_year' => 'This year',
    ];

    /** Stock level that marks a product as running low when it sets no alert of its own. */
    private const LOW_STOCK_THRESHOLD = 5;

    /** Longest series still charted one point per day. */
    private const DAILY_SERIES_LIMIT = 45;

    /** Longest series still charted one point per week. */
    private const WEEKLY_SERIES_LIMIT = 200;

    /** Rows a report table lists before pointing at the export. */
    private const TABLE_ROWS = 12;

    public string $tab = 'sales';

    public string $preset = 'last_30';

    public string $from = '';

    public string $to = '';

    public function mount(): void
    {
        $this->applyPreset($this->preset);
    }

    public function selectTab(string $tab): void
    {
        $this->tab = array_key_exists($tab, self::TABS) ? $tab : 'sales';
    }

    public function selectPreset(string $preset): void
    {
        if (array_key_exists($preset, self::PRESETS)) {
            $this->applyPreset($preset);
        }
    }

    public function updatedFrom(): void
    {
        $this->preset = 'custom';
    }

    public function updatedTo(): void
    {
        $this->preset = 'custom';
    }

    public function render()
    {
        [$start, $end] = $this->range();
        [$previousStart, $previousEnd] = $this->previousRange($start, $end);

        $currency = $this->storeCurrency();

        $report = match ($this->tab) {
            'products' => $this->productReport($start, $end),
            'customers' => $this->customerReport($start, $end, $previousStart, $previousEnd),
            'inventory' => $this->inventoryReport($start, $end),
            default => $this->salesReport($start, $end, $previousStart, $previousEnd),
        };

        return view('livewire.admin.reports', [
            'tabs' => self::TABS,
            'presets' => self::PRESETS,
            'start' => $start,
            'end' => $end,
            'previousStart' => $previousStart,
            'previousEnd' => $previousEnd,
            'currency' => $currency,
            // An unsaved order reuses the model's currency-aware formatter so
            // the zero-decimal rules are not repeated in this screen.
            'money' => (new Order)->forceFill(['currency' => $currency])->money(...),
            'report' => $report,
        ])->layout('components.layouts.admin', ['title' => 'Reports']);
    }

    /**
     * Download the rows behind the open report as a CSV file.
     */
    public function export(): StreamedResponse
    {
        abort_unless(auth()->user()?->can('reports.view'), 403);

        [$start, $end] = $this->range();
        $tab = array_key_exists($this->tab, self::TABS) ? $this->tab : 'sales';

        [$filename, $header, $rows] = match ($tab) {
            'products' => $this->productExport($start, $end),
            'customers' => $this->customerExport($start, $end),
            'inventory' => $this->inventoryExport($start, $end),
            default => $this->salesExport($start, $end),
        };

        return response()->streamDownload(function () use ($header, $rows): void {
            $handle = fopen('php://output', 'w');

            // Spreadsheets only read UTF-8 without mojibake when the byte order
            // mark leads the file.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $header);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Revenue, basket size and where the money came from.
     *
     * Cancelled orders never earned money, so every money figure leaves them
     * out. They are reported separately so the lost value stays visible.
     *
     * @return array<string, mixed>
     */
    private function salesReport(Carbon $start, Carbon $end, Carbon $previousStart, Carbon $previousEnd): array
    {
        $billable = fn (): Builder => Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled');

        $totals = $billable()
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('COALESCE(SUM(total), 0) as revenue')
            ->selectRaw('COALESCE(SUM(subtotal), 0) as subtotal')
            ->selectRaw('COALESCE(SUM(discount_total), 0) as discounts')
            ->selectRaw('COALESCE(SUM(tax_total), 0) as tax')
            ->selectRaw('COALESCE(SUM(shipping_total), 0) as shipping')
            ->first();

        $previous = Order::query()
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('COALESCE(SUM(total), 0) as revenue')
            ->first();

        $revenue = (float) $totals->revenue;
        $orders = (int) $totals->orders;
        $units = $this->unitsSold($start, $end);

        $previousRevenue = (float) $previous->revenue;
        $previousOrders = (int) $previous->orders;
        $previousUnits = $this->unitsSold($previousStart, $previousEnd);

        $average = $orders > 0 ? $revenue / $orders : 0.0;
        $previousAverage = $previousOrders > 0 ? $previousRevenue / $previousOrders : 0.0;

        $cancelled = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'cancelled')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('COALESCE(SUM(total), 0) as revenue')
            ->first();

        $series = $this->revenueSeries($start, $end, $billable);

        $methods = $billable()
            ->groupBy('payment_method')
            ->selectRaw('payment_method, COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn (Order $row): array => [
                'label' => $row->paymentMethodLabel(),
                'orders' => (int) $row->orders,
                'revenue' => (float) $row->revenue,
                'percent' => $revenue > 0 ? round((float) $row->revenue / $revenue * 100, 1) : 0.0,
            ])
            ->all();

        $statusCounts = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue')
            ->get()
            ->keyBy('status');

        $statusTotal = (int) $statusCounts->sum('orders');

        $statuses = [];

        foreach (Order::STATUSES as $status) {
            $row = $statusCounts->get($status);

            $statuses[] = [
                'label' => ucfirst($status),
                'orders' => (int) ($row->orders ?? 0),
                'revenue' => (float) ($row->revenue ?? 0),
                'percent' => $statusTotal > 0 ? round((int) ($row->orders ?? 0) / $statusTotal * 100, 1) : 0.0,
            ];
        }

        return [
            'kpis' => [
                ['label' => 'Net revenue', 'value' => $revenue, 'format' => 'money', 'delta' => $this->delta($revenue, $previousRevenue)],
                ['label' => 'Orders', 'value' => $orders, 'format' => 'count', 'delta' => $this->delta((float) $orders, (float) $previousOrders)],
                ['label' => 'Average order', 'value' => $average, 'format' => 'money', 'delta' => $this->delta($average, $previousAverage)],
                ['label' => 'Items sold', 'value' => $units, 'format' => 'count', 'delta' => $this->delta((float) $units, (float) $previousUnits)],
            ],
            'series' => $series,
            'chart' => $this->chartGeometry($series['points']),
            'methods' => $methods,
            'statuses' => $statuses,
            'money_rows' => [
                ['label' => 'Product subtotal', 'value' => (float) $totals->subtotal],
                ['label' => 'Discounts given', 'value' => -1 * (float) $totals->discounts],
                ['label' => 'Tax collected', 'value' => (float) $totals->tax],
                ['label' => 'Shipping charged', 'value' => (float) $totals->shipping],
                ['label' => 'Total collected', 'value' => $revenue],
            ],
            'cancelled' => [
                'orders' => (int) $cancelled->orders,
                'revenue' => (float) $cancelled->revenue,
            ],
        ];
    }

    /**
     * What sold, what it earned, and what it cost.
     *
     * @return array<string, mixed>
     */
    private function productReport(Carbon $start, Carbon $end): array
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->selectRaw('order_items.product_id, order_items.product_name')
            ->selectRaw('SUM(order_items.quantity) as units')
            ->selectRaw('SUM(order_items.line_total) as revenue')
            ->selectRaw('COUNT(DISTINCT order_items.order_id) as orders')
            ->selectRaw('MAX(products.purchase_price) as unit_cost')
            ->orderByDesc('revenue')
            ->get();

        $revenue = (float) $rows->sum('revenue');
        $units = (int) $rows->sum('units');

        $thumbnails = Product::query()
            ->whereIn('id', $rows->pluck('product_id')->filter()->all())
            ->get()
            ->keyBy('id');

        $priced = $rows->filter(static fn (OrderItem $row): bool => $row->unit_cost !== null && (float) $row->unit_cost > 0.0);

        $profit = $priced->sum(static fn (OrderItem $row): float => (float) $row->revenue - (float) $row->unit_cost * (int) $row->units);

        $table = $rows->take(self::TABLE_ROWS)->map(static function (OrderItem $row) use ($thumbnails, $revenue): array {
            $units = (int) $row->units;
            $lineRevenue = (float) $row->revenue;
            $cost = $row->unit_cost !== null && (float) $row->unit_cost > 0.0
                ? (float) $row->unit_cost * $units
                : null;

            return [
                'name' => (string) $row->product_name,
                'thumbnail' => $thumbnails->get($row->product_id)?->thumbnailUrl(),
                'units' => $units,
                'orders' => (int) $row->orders,
                'revenue' => $lineRevenue,
                'share' => $revenue > 0.0 ? round($lineRevenue / $revenue * 100, 1) : 0.0,
                'cost' => $cost,
                'profit' => $cost === null ? null : $lineRevenue - $cost,
                'margin' => $cost !== null && $lineRevenue > 0.0
                    ? round(($lineRevenue - $cost) / $lineRevenue * 100, 1)
                    : null,
            ];
        })->values();

        $categories = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('categories.name, SUM(order_items.quantity) as units, SUM(order_items.line_total) as revenue')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get()
            ->map(static fn (OrderItem $row): array => [
                'label' => (string) $row->name,
                'units' => (int) $row->units,
                'revenue' => (float) $row->revenue,
                'percent' => $revenue > 0 ? round((float) $row->revenue / $revenue * 100, 1) : 0.0,
            ])
            ->all();

        $soldIds = $rows->pluck('product_id')->filter()->unique()->values()->all();

        return [
            'kpis' => [
                ['label' => 'Product revenue', 'value' => $revenue, 'format' => 'money', 'delta' => null],
                ['label' => 'Units sold', 'value' => $units, 'format' => 'count', 'delta' => null],
                ['label' => 'Gross profit', 'value' => (float) $profit, 'format' => 'money', 'delta' => null],
                ['label' => 'Products sold', 'value' => $rows->count(), 'format' => 'count', 'delta' => null],
            ],
            'table' => $table,
            'hidden_rows' => max(0, $rows->count() - $table->count()),
            'categories' => $categories,
            'average_unit_price' => $units > 0 ? $revenue / $units : 0.0,
            'priced_products' => $priced->count(),
            'unsold_products' => Product::query()
                ->published()
                ->when($soldIds !== [], fn (Builder $query) => $query->whereNotIn('id', $soldIds))
                ->count(),
        ];
    }

    /**
     * Who bought, how often, and how much they are worth.
     *
     * @return array<string, mixed>
     */
    private function customerReport(Carbon $start, Carbon $end, Carbon $previousStart, Carbon $previousEnd): array
    {
        $billable = fn (): Builder => Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled');

        $buyerIds = $billable()->whereNotNull('user_id')->distinct()->pluck('user_id');

        $spending = $billable()
            ->whereNotNull('user_id')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('COALESCE(SUM(total), 0) as spent')
            ->first();

        $rows = $billable()
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as orders, COALESCE(SUM(total), 0) as spent, MAX(created_at) as last_order')
            ->orderByDesc('spent')
            ->limit(self::TABLE_ROWS)
            ->get();

        $users = User::query()
            ->whereIn('id', $rows->pluck('user_id')->all())
            ->get(['id', 'name', 'email'])
            ->keyBy('id');

        $newCustomers = $this->customerQuery()->whereBetween('created_at', [$start, $end])->count();
        $previousNewCustomers = $this->customerQuery()->whereBetween('created_at', [$previousStart, $previousEnd])->count();

        $buyers = $buyerIds->count();
        $spent = (float) $spending->spent;

        $repeatBuyers = $billable()
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->selectRaw('user_id')
            ->get()
            ->count();

        $returning = $buyerIds->isEmpty()
            ? 0
            : User::query()->whereIn('id', $buyerIds->all())->where('created_at', '<', $start)->count();

        $guests = $billable()
            ->whereNull('user_id')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('COALESCE(SUM(total), 0) as spent')
            ->first();

        $table = $rows->map(static function (Order $row) use ($users): array {
            $user = $users->get($row->user_id);
            $orders = (int) $row->orders;
            $total = (float) $row->spent;

            return [
                'name' => $user?->name ?? 'Deleted account',
                'email' => $user?->email ?? '—',
                'orders' => $orders,
                'spent' => $total,
                'average' => $orders > 0 ? $total / $orders : 0.0,
                'last_order' => $row->last_order !== null ? Carbon::parse($row->last_order) : null,
            ];
        })->values();

        return [
            'kpis' => [
                ['label' => 'New customers', 'value' => $newCustomers, 'format' => 'count', 'delta' => $this->delta((float) $newCustomers, (float) $previousNewCustomers)],
                ['label' => 'Buying customers', 'value' => $buyers, 'format' => 'count', 'delta' => null],
                ['label' => 'Returning buyers', 'value' => $returning, 'format' => 'count', 'delta' => null],
                [
                    'label' => 'Repeat order rate',
                    'value' => $buyers > 0 ? round($repeatBuyers / $buyers * 100, 1) : 0.0,
                    'format' => 'percent',
                    'delta' => null,
                ],
            ],
            'table' => $table,
            'hidden_rows' => max(0, $buyers - $table->count()),
            'buyers' => $buyers,
            'spent' => $spent,
            'average_basket' => $buyers > 0 ? $spent / $buyers : 0.0,
            'guests' => [
                'orders' => (int) $guests->orders,
                'spent' => (float) $guests->spent,
            ],
            'total_customers' => $this->customerQuery()->count(),
        ];
    }

    /**
     * What the shelves are worth and how they moved during the range.
     *
     * @return array<string, mixed>
     */
    private function inventoryReport(Carbon $start, Carbon $end): array
    {
        $stock = Product::query()
            ->published()
            ->selectRaw('COUNT(*) as products')
            ->selectRaw('COALESCE(SUM(current_stock), 0) as units')
            ->selectRaw('COALESCE(SUM(current_stock * COALESCE(purchase_price, 0)), 0) as cost_value')
            ->selectRaw('COALESCE(SUM(current_stock * unit_price), 0) as retail_value')
            ->selectRaw('SUM(CASE WHEN purchase_price IS NULL OR purchase_price <= 0 THEN 1 ELSE 0 END) as unpriced')
            ->first();

        $lowStock = $this->lowStockQuery();

        $movements = StockMovement::query()
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('reason')
            ->selectRaw('reason, COUNT(*) as changes, COALESCE(SUM(quantity), 0) as units')
            ->get()
            ->keyBy('reason');

        $rows = [];
        $received = 0;
        $shipped = 0;

        foreach (StockMovement::REASON_LABELS as $reason => $label) {
            $row = $movements->get($reason);
            $units = (int) ($row->units ?? 0);

            if (in_array($reason, [StockMovement::REASON_RESTOCK, StockMovement::REASON_RETURN], true)) {
                $received += max(0, $units);
            }

            if ($reason === StockMovement::REASON_ORDER) {
                $shipped += abs($units);
            }

            $rows[] = [
                'label' => $label,
                'changes' => (int) ($row->changes ?? 0),
                'units' => $units,
            ];
        }

        return [
            'kpis' => [
                ['label' => 'Stock at cost', 'value' => (float) $stock->cost_value, 'format' => 'money', 'delta' => null],
                ['label' => 'Retail value', 'value' => (float) $stock->retail_value, 'format' => 'money', 'delta' => null],
                ['label' => 'Low stock', 'value' => (clone $lowStock)->count(), 'format' => 'count', 'delta' => null],
                ['label' => 'Out of stock', 'value' => (clone $lowStock)->where('current_stock', '<=', 0)->count(), 'format' => 'count', 'delta' => null],
            ],
            'rows' => $rows,
            'units' => (int) $stock->units,
            'products' => (int) $stock->products,
            'unpriced' => (int) $stock->unpriced,
            'received' => $received,
            'shipped' => $shipped,
        ];
    }

    /**
     * One point per day, folded into weeks or months when the range is long.
     *
     * @param  callable(): Builder  $query
     * @return array{points: array<int, array{label: string, revenue: float, orders: int}>, granularity: string, best: array{label: string, revenue: float}}
     */
    private function revenueSeries(Carbon $start, Carbon $end, callable $query): array
    {
        $daily = $query()
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(total), 0) as revenue, COUNT(*) as orders')
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->keyBy('day');

        $granularity = $this->granularity($start, $end);
        $buckets = [];

        for ($day = $start->copy()->startOfDay(); $day->lessThanOrEqualTo($end); $day->addDay()) {
            $key = match ($granularity) {
                'day' => $day->toDateString(),
                'week' => $day->copy()->startOfWeek()->toDateString(),
                default => $day->format('Y-m'),
            };

            $buckets[$key] ??= [
                'label' => match ($granularity) {
                    'week' => $day->copy()->startOfWeek()->format('M j'),
                    'month' => $day->format('M Y'),
                    default => $day->format('M j'),
                },
                'revenue' => 0.0,
                'orders' => 0,
            ];

            $row = $daily->get($day->toDateString());
            $buckets[$key]['revenue'] += (float) ($row->revenue ?? 0);
            $buckets[$key]['orders'] += (int) ($row->orders ?? 0);
        }

        $points = array_values($buckets);

        $best = ['label' => '—', 'revenue' => 0.0];

        foreach ($points as $point) {
            if ($point['revenue'] > $best['revenue']) {
                $best = ['label' => $point['label'], 'revenue' => (float) $point['revenue']];
            }
        }

        return [
            'points' => $points,
            'granularity' => $granularity,
            'best' => $best,
        ];
    }

    /**
     * Pick the finest bucket that still keeps the chart readable.
     */
    private function granularity(Carbon $start, Carbon $end): string
    {
        $days = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;

        return match (true) {
            $days <= self::DAILY_SERIES_LIMIT => 'day',
            $days <= self::WEEKLY_SERIES_LIMIT => 'week',
            default => 'month',
        };
    }

    /**
     * Units sold on non-cancelled orders inside the range.
     */
    private function unitsSold(Carbon $start, Carbon $end): int
    {
        return (int) OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->sum('order_items.quantity');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(): array
    {
        $start = $this->parseDate($this->from) ?? now()->startOfDay()->subDays(29);
        $end = $this->parseDate($this->to) ?? now()->startOfDay();

        if ($end->lessThan($start)) {
            [$start, $end] = [$end, $start];
        }

        return [$start->startOfDay(), $end->endOfDay()];
    }

    /**
     * The equally long window immediately before the selected one.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function previousRange(Carbon $start, Carbon $end): array
    {
        $days = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $previousEnd = $start->copy()->subSecond();

        return [$previousEnd->copy()->startOfDay()->subDays($days - 1), $previousEnd];
    }

    private function parseDate(string $value): ?Carbon
    {
        $value = trim($value);

        if (preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $value) !== 1) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }

    private function applyPreset(string $preset): void
    {
        $preset = array_key_exists($preset, self::PRESETS) ? $preset : 'last_30';
        $today = now()->startOfDay();

        [$start, $end] = match ($preset) {
            'today' => [$today, $today],
            'last_7' => [$today->copy()->subDays(6), $today],
            'this_month' => [$today->copy()->startOfMonth(), $today],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [$today->copy()->startOfYear(), $today],
            default => [$today->copy()->subDays(29), $today],
        };

        $this->preset = $preset;
        $this->from = $start->toDateString();
        $this->to = $end->toDateString();
    }

    private function customerQuery(): Builder
    {
        // Shoppers are accounts without the admin flag and without any panel
        // role, so store staff never inflate the customer numbers.
        return User::query()
            ->where('is_admin', false)
            ->whereDoesntHave('roles');
    }

    private function lowStockQuery(): Builder
    {
        return Product::query()
            ->published()
            ->where(function (Builder $query): void {
                $query->where('current_stock', '<=', self::LOW_STOCK_THRESHOLD)
                    ->orWhere(function (Builder $query): void {
                        $query->whereNotNull('low_stock_quantity')
                            ->whereColumn('current_stock', '<=', 'low_stock_quantity');
                    });
            });
    }

    private function storeCurrency(): string
    {
        $currency = strtoupper((string) BusinessSetting::query()
            ->where('type', 'currency_code')
            ->value('value'));

        return preg_match('/\A[A-Z]{3}\z/', $currency) === 1 ? $currency : 'USD';
    }

    /**
     * @return array{0: string, 1: array<int, string>, 2: Collection<int, array<int, string|float|int>>}
     */
    private function salesExport(Carbon $start, Carbon $end): array
    {
        $series = $this->revenueSeries($start, $end, fn (): Builder => Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled'));

        $rows = collect($series['points'])->map(static fn (array $point): array => [
            $point['label'],
            round((float) $point['revenue'], 2),
            $point['orders'],
        ]);

        return [
            $this->filename('sales', $start, $end),
            ['Period ('.$series['granularity'].')', 'Revenue', 'Orders'],
            $rows,
        ];
    }

    /**
     * @return array{0: string, 1: array<int, string>, 2: Collection<int, array<int, string|float|int>>}
     */
    private function productExport(Carbon $start, Carbon $end): array
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->selectRaw('order_items.product_name')
            ->selectRaw('SUM(order_items.quantity) as units')
            ->selectRaw('SUM(order_items.line_total) as revenue')
            ->selectRaw('COUNT(DISTINCT order_items.order_id) as orders')
            ->selectRaw('MAX(products.purchase_price) as unit_cost')
            ->orderByDesc('revenue')
            ->get()
            ->map(static function (OrderItem $row): array {
                $units = (int) $row->units;
                $revenue = (float) $row->revenue;
                $cost = $row->unit_cost !== null && (float) $row->unit_cost > 0.0 ? (float) $row->unit_cost : null;

                return [
                    (string) $row->product_name,
                    $units,
                    (int) $row->orders,
                    round($revenue, 2),
                    $cost === null ? '' : round($cost, 2),
                    $cost === null ? '' : round($revenue - $cost * $units, 2),
                    $cost === null ? '' : round(($revenue - $cost * $units) / max($revenue, 0.01) * 100, 1),
                ];
            });

        return [
            $this->filename('products', $start, $end),
            ['Product', 'Units sold', 'Orders', 'Revenue', 'Unit cost', 'Gross profit', 'Margin %'],
            $rows,
        ];
    }

    /**
     * @return array{0: string, 1: array<int, string>, 2: Collection<int, array<int, string|float|int>>}
     */
    private function customerExport(Carbon $start, Carbon $end): array
    {
        $rows = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as orders, COALESCE(SUM(total), 0) as spent, MAX(created_at) as last_order')
            ->orderByDesc('spent')
            ->get();

        $users = User::query()
            ->whereIn('id', $rows->pluck('user_id')->all())
            ->get(['id', 'name', 'email'])
            ->keyBy('id');

        return [
            $this->filename('customers', $start, $end),
            ['Customer', 'Email', 'Orders', 'Total spent', 'Average order', 'Last order'],
            $rows->map(static function (Order $row) use ($users): array {
                $user = $users->get($row->user_id);
                $orders = (int) $row->orders;
                $spent = (float) $row->spent;

                return [
                    $user?->name ?? 'Deleted account',
                    $user?->email ?? '',
                    $orders,
                    round($spent, 2),
                    round($orders > 0 ? $spent / $orders : 0, 2),
                    $row->last_order !== null ? Carbon::parse($row->last_order)->toDateString() : '',
                ];
            }),
        ];
    }

    /**
     * @return array{0: string, 1: array<int, string>, 2: Collection<int, array<int, string|float|int>>}
     */
    private function inventoryExport(Carbon $start, Carbon $end): array
    {
        $rows = Product::query()
            ->published()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'barcode', 'current_stock', 'low_stock_quantity', 'unit_price', 'purchase_price'])
            ->map(static fn (Product $product): array => [
                $product->name,
                (string) ($product->barcode ?: $product->slug),
                (int) $product->current_stock,
                $product->low_stock_quantity === null ? '' : (int) $product->low_stock_quantity,
                round((float) $product->unit_price, 2),
                $product->purchase_price === null ? '' : round((float) $product->purchase_price, 2),
                round((float) $product->current_stock * (float) $product->unit_price, 2),
                round((float) $product->current_stock * (float) ($product->purchase_price ?? 0), 2),
            ]);

        return [
            $this->filename('inventory', $start, $end),
            ['Product', 'Barcode', 'In stock', 'Low stock alert', 'Unit price', 'Cost price', 'Retail value', 'Cost value'],
            $rows,
        ];
    }

    private function filename(string $report, Carbon $start, Carbon $end): string
    {
        return sprintf('%s-report-%s-to-%s.csv', $report, $start->toDateString(), $end->toDateString());
    }
}
