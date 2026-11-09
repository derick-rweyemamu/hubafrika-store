<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Orders;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\Storefront\OrdersTestCase;

class OrdersTest extends OrdersTestCase
{
    public function test_admin_processing_deducts_stock_once_and_updates_order_status(): void
    {
        $product = Product::query()->create([
            'name' => 'Coffee',
            'slug' => 'coffee',
            'category_id' => 1,
            'unit_price' => 10,
            'current_stock' => 5,
        ]);
        $order = $this->createOrderWithItem($product, 2);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Orders::class)
            ->call('updateStatus', $order->id, 'processing')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'processing']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 3]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'num_of_sale' => 2]);

        Livewire::test(Orders::class)
            ->call('updateStatus', $order->id, 'processing')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 3]);
    }

    public function test_admin_cannot_process_an_order_that_exceeds_available_stock(): void
    {
        $product = Product::query()->create([
            'name' => 'Tea',
            'slug' => 'tea',
            'category_id' => 1,
            'unit_price' => 4,
            'current_stock' => 1,
        ]);
        $order = $this->createOrderWithItem($product, 2);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Orders::class)
            ->call('updateStatus', $order->id, 'processing')
            ->assertSee('Insufficient stock')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 1]);
    }

    public function test_admin_can_record_manual_payment_reference(): void
    {
        $product = Product::query()->create([
            'name' => 'Tea',
            'slug' => 'tea',
            'category_id' => 1,
            'unit_price' => 4,
            'current_stock' => 1,
        ]);
        $order = $this->createOrderWithItem($product, 1);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Orders::class)
            ->set("paymentReferences.{$order->id}", 'M-PESA-12345')
            ->call('markPaymentReceived', $order->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'payment_reference' => 'M-PESA-12345',
        ]);
    }

    public function test_processing_a_variant_order_decrements_its_variant_stock(): void
    {
        $product = Product::query()->create([
            'name' => 'T-shirt',
            'slug' => 't-shirt',
            'category_id' => 1,
            'unit_price' => 10,
            'variant_product' => true,
            'current_stock' => 0,
        ]);
        $stock = ProductStock::query()->create([
            'product_id' => $product->id,
            'variant' => 'Medium',
            'sku' => 'TS-M',
            'price' => 10,
            'qty' => 4,
        ]);
        $order = $this->createOrderWithItem($product, 2, $stock);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Orders::class)
            ->call('updateStatus', $order->id, 'processing')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_stocks', ['id' => $stock->id, 'qty' => 2]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 0, 'num_of_sale' => 2]);
    }

    public function test_cancelling_a_processed_order_returns_the_stock_to_inventory(): void
    {
        $product = Product::query()->create([
            'name' => 'Coffee',
            'slug' => 'coffee',
            'category_id' => 1,
            'unit_price' => 10,
            'current_stock' => 5,
        ]);
        $order = $this->createOrderWithItem($product, 2);
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $component = Livewire::test(Orders::class);

        $component->call('updateStatus', $order->id, 'processing');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 3, 'num_of_sale' => 2]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => -2,
            'balance' => 3,
            'reason' => 'order',
        ]);

        $component->call('updateStatus', $order->id, 'cancelled')
            ->assertSee('stock was returned to inventory');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 5, 'num_of_sale' => 0]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 2,
            'balance' => 5,
            'reason' => 'cancellation',
        ]);
    }

    public function test_cancelling_a_pending_order_leaves_stock_untouched(): void
    {
        $product = Product::query()->create([
            'name' => 'Coffee',
            'slug' => 'coffee',
            'category_id' => 1,
            'unit_price' => 10,
            'current_stock' => 5,
        ]);
        $order = $this->createOrderWithItem($product, 2);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Orders::class)
            ->call('updateStatus', $order->id, 'cancelled')
            ->assertSee('Stock was never deducted');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 5, 'num_of_sale' => 0]);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_a_refunded_order_offers_no_record_payment_button(): void
    {
        $product = Product::query()->create([
            'name' => 'Coffee',
            'slug' => 'coffee',
            'category_id' => 1,
            'unit_price' => 10,
            'current_stock' => 5,
        ]);
        $order = $this->createOrderWithItem($product, 1);
        $order->update(['payment_status' => 'refunded']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Orders::class)
            ->assertDontSee('Record payment')
            ->assertDontSee('Payment reference (optional)')
            ->assertSee('Payment refunded');
    }

    public function test_a_refunded_order_cannot_record_another_payment(): void
    {
        $product = Product::query()->create([
            'name' => 'Coffee',
            'slug' => 'coffee',
            'category_id' => 1,
            'unit_price' => 10,
            'current_stock' => 5,
        ]);
        $order = $this->createOrderWithItem($product, 1);
        $order->update(['payment_status' => 'refunded']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Orders::class)
            ->set("paymentReferences.{$order->id}", 'M-PESA-99999')
            ->call('markPaymentReceived', $order->id)
            ->assertSee('This payment was refunded');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'refunded',
            'total' => 10,
        ]);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_a_refunded_order_cannot_be_marked_as_failed(): void
    {
        $product = Product::query()->create([
            'name' => 'Coffee',
            'slug' => 'coffee',
            'category_id' => 1,
            'unit_price' => 10,
            'current_stock' => 5,
        ]);
        $order = $this->createOrderWithItem($product, 1);
        $order->update(['payment_status' => 'refunded']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Orders::class)
            ->call('markPaymentFailed', $order->id)
            ->assertSee('This payment was refunded');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'refunded',
        ]);
    }

    public function test_orders_page_is_admin_only(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.orders'))
            ->assertOk()
            ->assertSee('Orders');

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.orders'))
            ->assertForbidden();
    }

    private function createOrderWithItem(Product $product, int $quantity, ?ProductStock $stock = null): Order
    {
        $order = Order::query()->create([
            'order_number' => 'ORD-TEST-'.$product->id.'-'.$quantity,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'currency' => 'USD',
            'subtotal' => $product->unit_price * $quantity,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'total' => $product->unit_price * $quantity,
            'shipping_address' => '1 Example Road',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_stock_id' => $stock?->id,
            'product_name' => $product->name,
            'sku' => $stock?->sku,
            'variant' => $stock?->variant,
            'quantity' => $quantity,
            'unit_price' => $product->unit_price,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => $product->unit_price * $quantity,
        ]);

        return $order;
    }
}
