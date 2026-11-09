<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Dashboard;
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

class DashboardTest extends CatalogTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Product fixtures below reference category 1, and the products table
        // enforces that foreign key.
        Category::query()->forceCreate(['id' => 1, 'name' => 'General', 'slug' => 'general']);
        BusinessSetting::query()->create(['type' => 'currency_code', 'value' => 'USD']);

        $this->seed(PermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_sales_metrics_only_include_recent_orders_that_were_not_cancelled(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->order($customer, 100, now()->subDays(2));
        $this->order($customer, 50, now()->subDays(40));
        $this->order($customer, 900, now()->subDay(), 'cancelled');

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $sales = Livewire::test(Dashboard::class)
            ->assertOk()
            ->viewData('sales');

        $this->assertSame(100.0, $sales['revenue']['value']);
        $this->assertSame(1, $sales['orders']['value']);
        $this->assertSame(100.0, $sales['average']['value']);
        $this->assertSame('up', $sales['revenue']['delta']['direction']);
    }

    public function test_low_stock_lists_published_products_at_or_below_their_own_threshold(): void
    {
        $this->product('Empty shelf', ['current_stock' => 0]);
        $this->product('Nearly gone', ['current_stock' => 3]);
        $this->product('Custom alert', ['current_stock' => 8, 'low_stock_quantity' => 10]);
        $this->product('Plenty', ['current_stock' => 50]);
        $this->product('Hidden item', ['current_stock' => 0, 'published' => false]);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $catalog = Livewire::test(Dashboard::class)
            ->assertOk()
            ->viewData('catalog');

        $this->assertSame(3, $catalog['low_stock_count']);
        $this->assertSame(1, $catalog['out_of_stock_count']);
        $this->assertSame(
            ['Empty shelf', 'Nearly gone', 'Custom alert'],
            $catalog['low_stock_products']->pluck('name')->all(),
        );
    }

    public function test_the_trend_range_can_be_changed_and_stays_within_a_month(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Dashboard::class)
            ->assertOk()
            ->call('setTrendDays', 7)
            ->assertSet('trendDays', 7)
            ->call('setTrendDays', 90)
            ->assertSet('trendDays', 30)
            ->call('setTrendDays', 1)
            ->assertSet('trendDays', 7);
    }

    public function test_staff_without_order_access_see_catalogue_health_instead_of_sales(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $this->order($customer, 250, now());

        $role = Role::findOrCreate('Dashboard staff', 'web');
        $role->syncPermissions(['products.view']);

        $staff = User::factory()->create(['is_admin' => false, 'name' => 'Stella Staff']);
        $staff->assignRole($role);

        $this->actingAs($staff)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Welcome back, Stella')
            ->assertSee('Live products')
            ->assertDontSee('Sales trend')
            ->assertDontSee('Recent orders');
    }

    public function test_staff_accounts_never_count_as_customers(): void
    {
        $manager = User::factory()->create(['is_admin' => false]);
        $manager->assignRole(Role::findOrCreate('manager', 'web'));

        User::factory()->create(['is_admin' => false]);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $customers = Livewire::test(Dashboard::class)
            ->assertOk()
            ->viewData('customers');

        $this->assertSame(1, $customers['value']);
        $this->assertSame(1, $customers['total']);
    }

    public function test_the_dashboard_renders_sales_panels_for_an_administrator(): void
    {
        $customer = User::factory()->create(['is_admin' => false, 'name' => 'Cara Customer']);
        $product = $this->product('Coffee beans', ['unit_price' => 25, 'current_stock' => 2]);

        $order = $this->order($customer, 50, now()->subHours(2));
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 25,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 50,
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Sales trend')
            ->assertSee('Order pipeline')
            ->assertSee('Top sellers · 30 days')
            ->assertSee('Coffee beans')
            ->assertSee($order->order_number)
            ->assertSee('Low stock')
            ->assertSee('Cara Customer');
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
