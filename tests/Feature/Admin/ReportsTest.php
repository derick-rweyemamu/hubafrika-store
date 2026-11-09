<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Reports;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ReportsTest extends CatalogTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-07 12:00:00'));

        // Product fixtures below reference category 1, and the products table
        // enforces that foreign key.
        Category::query()->forceCreate(['id' => 1, 'name' => 'General', 'slug' => 'general']);
        BusinessSetting::query()->create(['type' => 'currency_code', 'value' => 'USD']);

        $this->seed(PermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_the_report_page_needs_the_reports_permission(): void
    {
        $this->get('/admin/reports')->assertRedirect('/login');

        $staff = User::factory()->create(['is_admin' => false, 'email_verified_at' => now()]);
        $staff->assignRole(Role::findOrCreate('reportless', 'web'));

        $this->actingAs($staff)
            ->get('/admin/reports')
            ->assertForbidden();
    }

    public function test_sales_totals_exclude_cancelled_orders_and_cover_the_selected_range(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->order($customer, 120, now()->subDays(3));
        $this->order($customer, 80, now()->subDay());
        $this->order($customer, 500, now()->subDays(2), 'cancelled');

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $report = Livewire::test(Reports::class)
            ->assertOk()
            ->viewData('report');

        $this->assertSame(200.0, $report['kpis'][0]['value']);
        $this->assertSame(2, $report['kpis'][1]['value']);
        $this->assertSame(100.0, $report['kpis'][2]['value']);
        $this->assertSame('day', $report['series']['granularity']);
        $this->assertCount(30, $report['series']['points']);
        $this->assertSame(1, $report['cancelled']['orders']);
        $this->assertSame(500.0, $report['cancelled']['revenue']);
    }

    public function test_the_range_can_be_picked_from_a_preset_or_by_hand(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Reports::class)
            ->assertSet('preset', 'last_30')
            ->call('selectPreset', 'last_7')
            ->assertSet('from', now()->subDays(6)->toDateString())
            ->assertSet('to', now()->toDateString())
            ->set('from', '2026-01-01')
            ->assertSet('preset', 'custom');
    }

    public function test_a_long_custom_range_folds_the_chart_into_months(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $series = Livewire::test(Reports::class)
            ->set('from', '2025-01-01')
            ->set('to', '2026-11-07')
            ->viewData('report')['series'];

        $this->assertSame('month', $series['granularity']);
        $this->assertCount(23, $series['points']);
        $this->assertSame('Jan 2025', $series['points'][0]['label']);
    }

    public function test_product_report_aggregates_units_revenue_and_profit(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $product = $this->product('Solar panel', ['unit_price' => 100, 'purchase_price' => 60]);

        $order = $this->order($customer, 200, now()->subDay());

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 100,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 200,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $report = Livewire::test(Reports::class)
            ->call('selectTab', 'products')
            ->viewData('report');

        $this->assertSame(200.0, $report['kpis'][0]['value']);
        $this->assertSame(2, $report['kpis'][1]['value']);
        $this->assertSame(80.0, $report['kpis'][2]['value']);

        $row = $report['table']->first();

        $this->assertSame('Solar panel', $row['name']);
        $this->assertSame(100.0, $row['share']);
        $this->assertSame(120.0, $row['cost']);
        $this->assertSame(80.0, $row['profit']);
        $this->assertSame(40.0, $row['margin']);
    }

    public function test_customer_report_separates_new_from_returning_buyers(): void
    {
        $loyal = User::factory()->create(['is_admin' => false, 'email_verified_at' => now()]);
        $loyal->forceFill(['created_at' => now()->subMonths(3)])->save();

        $fresh = User::factory()->create(['is_admin' => false, 'email_verified_at' => now()]);

        $this->order($loyal, 300, now()->subDays(2));
        $this->order($fresh, 150, now()->subDay());

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $report = Livewire::test(Reports::class)
            ->call('selectTab', 'customers')
            ->viewData('report');

        $this->assertSame(1, $report['kpis'][0]['value']);   // new customers
        $this->assertSame(2, $report['kpis'][1]['value']);   // buying customers
        $this->assertSame(1, $report['kpis'][2]['value']);   // returning buyers
        $this->assertSame(450.0, $report['spent']);
        $this->assertSame($loyal->name, $report['table']->first()['name']);
    }

    public function test_the_export_streams_the_rows_behind_the_open_tab(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->order($customer, 240, now()->subDay());

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Reports::class)
            ->call('export')
            ->assertFileDownloaded('sales-report-2026-10-09-to-2026-11-07.csv');

        Livewire::test(Reports::class)
            ->call('selectTab', 'inventory')
            ->call('export')
            ->assertFileDownloaded('inventory-report-2026-10-09-to-2026-11-07.csv');
    }

    public function test_every_tab_renders_for_an_administrator(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $this->order($customer, 90, now()->subDay());

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/admin/reports')
            ->assertOk()
            ->assertSee('Revenue over time')
            ->assertSee('Payment methods')
            ->assertSee('Where the money goes');

        Livewire::test(Reports::class)
            ->call('selectTab', 'products')
            ->assertSee('Product performance')
            ->assertSee('Revenue by category')
            ->call('selectTab', 'customers')
            ->assertSee('Top customers')
            ->assertSee('Who is buying')
            ->call('selectTab', 'inventory')
            ->assertSee('Stock movements')
            ->assertSee('Shelf health');
    }

    private function order(User $customer, float $total, Carbon $createdAt, string $status = 'pending'): Order
    {
        return Order::query()->create([
            'order_number' => 'ORD-'.Str::upper(Str::random(8)),
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'status' => $status,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'currency' => 'USD',
            'subtotal' => $total,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'total' => $total,
            'shipping_address' => '1 Example Road',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function product(string $name, array $attributes = []): Product
    {
        return Product::query()->create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'category_id' => 1,
            'unit_price' => 10,
            'current_stock' => 10,
        ], $attributes));
    }
}
