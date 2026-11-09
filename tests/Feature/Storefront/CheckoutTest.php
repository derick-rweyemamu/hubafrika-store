<?php

namespace Tests\Feature\Storefront;

use App\Livewire\Storefront\Checkout;
use App\Livewire\Storefront\Shop;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Livewire\Livewire;

class CheckoutTest extends OrdersTestCase
{
    public function test_customer_checkout_creates_order_snapshots_and_does_not_deduct_stock(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $customer->addresses()->create([
            'address' => '10 River Road',
            'phone' => '+254700123456',
            'postal_code' => '00100',
            'set_default' => true,
        ]);
        $product = Product::query()->create([
            'name' => 'Coffee',
            'slug' => 'coffee',
            'category_id' => 1,
            'unit_price' => 10,
            'current_stock' => 5,
            'discount' => 10,
            'discount_type' => 'percent',
            'tax' => 10,
            'tax_type' => 'percent',
        ]);
        $customer->carts()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 0,
            'tax' => 0,
        ]);
        $this->actingAs($customer);

        $component = Livewire::test(Checkout::class)
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::query()->with('items')->firstOrFail();

        $component->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $customer->id,
            'customer_email' => $customer->email,
            'status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'currency' => 'USD',
            'subtotal' => 20,
            'discount_total' => 2,
            'tax_total' => 1.8,
            'shipping_total' => 5,
            'total' => 24.8,
            'shipping_address' => '10 River Road',
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Coffee',
            'quantity' => 2,
            'unit_price' => 10,
            'discount_amount' => 2,
            'tax_amount' => 1.8,
            'line_total' => 19.8,
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 5]);
        $this->assertDatabaseMissing('carts', ['user_id' => $customer->id]);
    }

    public function test_checkout_shows_a_server_calculated_order_estimate(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $product = Product::query()->create([
            'name' => 'Coffee',
            'slug' => 'coffee',
            'category_id' => 1,
            'unit_price' => 10,
            'current_stock' => 5,
            'discount' => 10,
            'discount_type' => 'percent',
            'tax' => 10,
            'tax_type' => 'percent',
        ]);
        $customer->carts()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 0,
            'tax' => 0,
        ]);
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->assertSee('USD 24.80')
            ->assertSee('USD 20.00')
            ->assertSee('-USD 2.00')
            ->assertSee('USD 1.80')
            ->assertSee('USD 5.00');
    }

    public function test_checkout_rejects_empty_cart_and_disabled_cash_on_delivery(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', '1 Example Road')
            ->call('placeOrder')
            ->assertHasErrors('checkout');

        $product = Product::query()->create([
            'name' => 'Tea',
            'slug' => 'tea',
            'category_id' => 1,
            'unit_price' => 4,
            'current_stock' => 3,
        ]);
        $customer->carts()->create(['product_id' => $product->id, 'quantity' => 1]);
        BusinessSetting::query()
            ->where('type', 'cash_on_delivery')
            ->update(['value' => '0']);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', '1 Example Road')
            ->call('placeOrder')
            ->assertHasErrors('checkout');

        $this->assertDatabaseMissing('orders', ['user_id' => $customer->id]);
        $this->assertDatabaseHas('carts', ['user_id' => $customer->id, 'product_id' => $product->id]);
    }

    public function test_checkout_rejects_out_of_stock_items_without_creating_an_order(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $product = Product::query()->create([
            'name' => 'Tea',
            'slug' => 'tea',
            'category_id' => 1,
            'unit_price' => 4,
            'current_stock' => 1,
        ]);
        $customer->carts()->create(['product_id' => $product->id, 'quantity' => 2]);
        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', '1 Example Road')
            ->call('placeOrder')
            ->assertHasErrors('checkout');

        $this->assertDatabaseMissing('orders', ['user_id' => $customer->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 1]);
        $this->assertDatabaseHas('carts', ['user_id' => $customer->id, 'product_id' => $product->id]);
    }

    public function test_shop_adds_only_server_priced_published_products_to_the_customers_cart(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $product = Product::query()->create([
            'name' => 'Tea',
            'slug' => 'tea',
            'category_id' => 1,
            'unit_price' => 4,
            'current_stock' => 3,
            'published' => true,
        ]);
        $this->actingAs($customer);

        Livewire::test(Shop::class)
            ->call('addToCart', $product->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('carts', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'price' => 4,
            'quantity' => 1,
        ]);
    }

    public function test_shop_requires_a_variant_selection_and_uses_its_database_price(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $product = Product::query()->create([
            'name' => 'Tea Pack',
            'slug' => 'tea-pack',
            'category_id' => 1,
            'unit_price' => 4,
            'variant_product' => true,
            'current_stock' => 0,
            'published' => true,
        ]);
        $stock = ProductStock::query()->create([
            'product_id' => $product->id,
            'variant' => 'Large',
            'sku' => 'TEA-L',
            'price' => 8,
            'qty' => 3,
        ]);
        $this->actingAs($customer);

        Livewire::test(Shop::class)
            ->call('addToCart', $product->id)
            ->assertHasErrors('cart')
            ->set("selectedStockIds.{$product->id}", (string) $stock->id)
            ->call('addToCart', $product->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('carts', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'price' => 8,
            'quantity' => 1,
        ]);
    }

    public function test_customer_order_history_shows_only_the_authenticated_customers_orders(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $otherCustomer = User::factory()->create(['is_admin' => false]);
        Order::query()->create($this->orderAttributes($customer, 'ORD-CUSTOMER-1'));
        Order::query()->create($this->orderAttributes($otherCustomer, 'ORD-OTHER-1'));
        $this->actingAs($customer);

        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee('ORD-CUSTOMER-1')
            ->assertDontSee('ORD-OTHER-1');
    }

    public function test_guests_can_reach_checkout_and_register_above_the_delivery_details(): void
    {
        $this->get(route('checkout'))
            ->assertOk()
            ->assertSeeInOrder(['Create your account', 'Delivery details']);
    }

    public function test_shop_is_available_to_guests(): void
    {
        $this->get(route('shop'))
            ->assertOk()
            ->assertSee('shop-grid', false)
            ->assertSee('No available products found.');
    }

    /**
     * @return array<string, mixed>
     */
    private function orderAttributes(User $customer, string $orderNumber): array
    {
        return [
            'user_id' => $customer->id,
            'order_number' => $orderNumber,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'currency' => 'USD',
            'subtotal' => 10,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'total' => 10,
            'shipping_address' => '1 Example Road',
        ];
    }
}
