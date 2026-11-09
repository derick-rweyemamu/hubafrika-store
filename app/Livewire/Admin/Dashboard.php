<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DrawsCharts;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    use DrawsCharts;

    /** Days of history the sales chart shows until the viewer picks another range. */
    public int $trendDays = 14;

    /** Stock level that marks a product as running low when it sets no alert of its own. */
    private const LOW_STOCK_THRESHOLD = 5;

    /** Daily points loaded so the chart can zoom out to a full month. */
    private const SERIES_DAYS = 30;

    /** How many orders the recent activity panel lists. */
    private const RECENT_ORDERS = 6;

    public function setTrendDays(int $days): void
    {
        $this->trendDays = max(7, min(self::SERIES_DAYS, $days));
    }

    public function render()
    {
        $abilities = [
            'orders' => (bool) auth()->user()?->can('orders.view'),
            'products' => (bool) auth()->user()?->can('products.view'),
            'inventory' => (bool) auth()->user()?->can('inventory.view'),
            'customers' => (bool) auth()->user()?->can('customers.view'),
            'settings' => (bool) auth()->user()?->can('settings.view'),
            'cms' => (bool) auth()->user()?->can('cms.manage'),
        ];

        $windowStart = now()->startOfDay()->subDays(self::SERIES_DAYS - 1);
        $previousEnd = $windowStart->copy()->subSecond();
        $previousStart = $previousEnd->copy()->startOfDay()->subDays(self::SERIES_DAYS - 1);

        // Money leaving the store is sensitive, so sales figures are only
        // loaded for people who are already allowed to see the order list.
        $sales = $abilities['orders']
            ? $this->salesMetrics($windowStart, $previousStart, $previousEnd)
            : null;

        $customers = $this->customerMetrics($windowStart, $previousStart, $previousEnd);
        $catalog = $this->catalogMetrics();
        $pipeline = $abilities['orders']
            ? $this->pipelineMetrics()
            : ['total' => 0, 'rows' => []];

        $attention = [
            'pending_orders' => $pipeline['rows']['pending']['count'] ?? 0,
            'awaiting_payments' => $abilities['orders']
                ? Order::query()->where('payment_status', 'awaiting_verification')->count()
                : 0,
            'low_stock' => $catalog['low_stock_count'],
        ];

        $currency = $this->storeCurrency();

        // An unsaved order reuses the model's currency-aware formatter, so the
        // zero-decimal rules are not duplicated in the dashboard.
        $money = (new Order)->forceFill(['currency' => $currency])->money(...);

        return view('livewire.admin.dashboard', [
            'abilities' => $abilities,
            'sales' => $sales,
            'customers' => $customers,
            'catalog' => $catalog,
            'pipeline' => $pipeline,
            'attention' => $attention,
            'topProducts' => $sales['top_products'] ?? collect(),
            'recentOrders' => $sales['recent_orders'] ?? collect(),
            'money' => $money,
            'currency' => $currency,
            'trendDays' => $this->trendDays,
        ])->layout('components.layouts.admin', ['title' => 'Dashboard']);
    }

    /**
     * Revenue, order volume and basket size for the current window.
     *
     * Cancelled orders never earned money, so they are excluded everywhere.
     *
     * @return array<string, mixed>
     */
    private function salesMetrics(Carbon $windowStart, Carbon $previousStart, Carbon $previousEnd): array
    {
        $current = Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $windowStart);

        $revenue = (float) (clone $current)->sum('total');
        $orderCount = (clone $current)->count();

        $previous = Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$previousStart, $previousEnd]);

        $previousRevenue = (float) (clone $previous)->sum('total');
        $previousOrders = (clone $previous)->count();

        $averageOrder = $orderCount > 0 ? $revenue / $orderCount : 0.0;
        $previousAverage = $previousOrders > 0 ? $previousRevenue / $previousOrders : 0.0;

        $series = $this->dailySeries($windowStart);
        $points = array_slice($series, -$this->trendDays);

        $averageSeries = array_map(
            static fn (array $point): float => $point['orders'] > 0
                ? $point['revenue'] / $point['orders']
                : 0.0,
            $series,
        );

        return [
            'revenue' => [
                'value' => $revenue,
                'delta' => $this->delta($revenue, $previousRevenue),
                'spark' => $this->sparkline(array_column($series, 'revenue')),
            ],
            'orders' => [
                'value' => $orderCount,
                'delta' => $this->delta((float) $orderCount, (float) $previousOrders),
                'spark' => $this->sparkline(array_column($series, 'orders')),
            ],
            'average' => [
                'value' => $averageOrder,
                'delta' => $this->delta($averageOrder, $previousAverage),
                'spark' => $this->sparkline($averageSeries),
            ],
            'chart' => $this->chartGeometry($points),
            'trend_revenue' => array_sum(array_column($points, 'revenue')),
            'trend_orders' => array_sum(array_column($points, 'orders')),
            'top_products' => $this->topProducts($windowStart),
            'recent_orders' => Order::query()->latest()->take(self::RECENT_ORDERS)->get(),
        ];
    }

    /**
     * One row per day, including the quiet days, so the chart has no gaps.
     *
     * @return array<int, array{label: string, revenue: float, orders: int}>
     */
    private function dailySeries(Carbon $windowStart): array
    {
        $daily = Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $windowStart)
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue, COUNT(*) as orders')
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->keyBy('day');

        $series = [];

        for ($offset = 0; $offset < self::SERIES_DAYS; $offset++) {
            $day = $windowStart->copy()->addDays($offset);
            $row = $daily->get($day->toDateString());

            $series[] = [
                'label' => $day->format('M j'),
                'revenue' => (float) ($row->revenue ?? 0),
                'orders' => (int) ($row->orders ?? 0),
            ];
        }

        return $series;
    }

    /**
     * New sign-ups and the customer base, matching the Customers screen.
     *
     * @return array<string, mixed>
     */
    private function customerMetrics(Carbon $windowStart, Carbon $previousStart, Carbon $previousEnd): array
    {
        $daily = $this->customerQuery()
            ->where('created_at', '>=', $windowStart)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->keyBy('day');

        $series = [];

        for ($offset = 0; $offset < self::SERIES_DAYS; $offset++) {
            $row = $daily->get($windowStart->copy()->addDays($offset)->toDateString());
            $series[] = (int) ($row->total ?? 0);
        }

        $current = $this->customerQuery()->where('created_at', '>=', $windowStart)->count();
        $previous = $this->customerQuery()
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();

        return [
            'value' => $current,
            'delta' => $this->delta((float) $current, (float) $previous),
            'spark' => $this->sparkline($series),
            'total' => $this->customerQuery()->count(),
            'recent' => $this->customerQuery()
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'email', 'created_at']),
        ];
    }

    /**
     * Catalogue health: how much is live and what needs restocking.
     *
     * @return array<string, mixed>
     */
    private function catalogMetrics(): array
    {
        $lowStock = Product::query()
            ->published()
            ->where(function (Builder $query): void {
                $query->where('current_stock', '<=', self::LOW_STOCK_THRESHOLD)
                    ->orWhere(function (Builder $query): void {
                        $query->whereNotNull('low_stock_quantity')
                            ->whereColumn('current_stock', '<=', 'low_stock_quantity');
                    });
            });

        return [
            'total' => Product::query()->count(),
            'published' => Product::query()->published()->count(),
            'low_stock_count' => $lowStock->count(),
            'out_of_stock_count' => (clone $lowStock)->where('current_stock', '<=', 0)->count(),
            'low_stock_products' => $lowStock->orderBy('current_stock')->take(5)->get(),
        ];
    }

    /**
     * How the whole order book is spread across fulfilment stages.
     *
     * @return array{total: int, rows: array<string, array{status: string, label: string, count: int, percent: float, bar: string}>}
     */
    private function pipelineMetrics(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (int) $counts->sum();

        $bars = [
            'pending' => 'bg-amber-500',
            'processing' => 'bg-sky-500',
            'shipped' => 'bg-indigo-500',
            'delivered' => 'bg-emerald-500',
            'cancelled' => 'bg-rose-500',
        ];

        $rows = [];

        foreach (Order::STATUSES as $status) {
            $count = (int) ($counts[$status] ?? 0);

            $rows[$status] = [
                'status' => $status,
                'label' => ucfirst($status),
                'count' => $count,
                'percent' => $total > 0 ? round($count / $total * 100, 1) : 0.0,
                'bar' => $bars[$status] ?? 'bg-gray-400',
            ];
        }

        return ['total' => $total, 'rows' => $rows];
    }

    /**
     * Best sellers by revenue over the window, with their catalogue pictures.
     *
     * @return Collection<int, array{name: string, units: int, revenue: float, percent: float, thumbnail: string|null}>
     */
    private function topProducts(Carbon $windowStart): Collection
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', $windowStart)
            ->selectRaw('order_items.product_id, order_items.product_name, SUM(order_items.quantity) as units, SUM(order_items.line_total) as revenue')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        if ($rows->isEmpty()) {
            return $rows;
        }

        $thumbnails = Product::query()
            ->whereIn('id', $rows->pluck('product_id')->filter()->all())
            ->get()
            ->keyBy('id');

        $peak = max(1, (int) $rows->max('units'));

        return $rows->map(static fn (OrderItem $row): array => [
            'name' => (string) $row->product_name,
            'units' => (int) $row->units,
            'revenue' => (float) $row->revenue,
            'percent' => round((int) $row->units / $peak * 100, 1),
            'thumbnail' => $thumbnails->get($row->product_id)?->thumbnailUrl(),
        ])->values();
    }

    /**
     * Shoppers are accounts without the admin flag and without any panel role,
     * so store staff never inflate the customer numbers.
     */
    private function customerQuery(): Builder
    {
        return User::query()
            ->where('is_admin', false)
            ->whereDoesntHave('roles');
    }

    /**
     * The store currency, falling back to USD when settings are not filled in yet.
     */
    private function storeCurrency(): string
    {
        $currency = strtoupper((string) BusinessSetting::query()
            ->where('type', 'currency_code')
            ->value('value'));

        return preg_match('/\A[A-Z]{3}\z/', $currency) === 1 ? $currency : 'USD';
    }
}
