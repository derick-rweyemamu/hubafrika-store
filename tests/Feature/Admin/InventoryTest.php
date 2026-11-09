<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Inventory;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\User;
use Livewire\Livewire;

class InventoryTest extends CatalogTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Products below reference category 1, which the products table enforces.
        Category::query()->create(['id' => 1, 'name' => 'General', 'slug' => 'general']);
    }

    public function test_admin_can_add_stock_and_it_is_recorded_in_the_movement_log(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $product = $this->product(currentStock: 4);

        Livewire::test(Inventory::class)
            ->call('adjust', $product->id)
            ->set('quantity', '10')
            ->set('reason', StockMovement::REASON_RESTOCK)
            ->set('note', 'Invoice 4711')
            ->call('saveAdjustment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 14]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'product_stock_id' => null,
            'user_id' => $admin->id,
            'quantity' => 10,
            'balance' => 14,
            'reason' => StockMovement::REASON_RESTOCK,
            'note' => 'Invoice 4711',
        ]);
    }

    public function test_stock_cannot_be_reduced_below_zero(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $product = $this->product(currentStock: 3);

        Livewire::test(Inventory::class)
            ->call('adjust', $product->id)
            ->set('quantity', '-5')
            ->call('saveAdjustment')
            ->assertHasErrors('quantity');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 3]);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_a_zero_quantity_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $product = $this->product(currentStock: 3);

        Livewire::test(Inventory::class)
            ->call('adjust', $product->id)
            ->set('quantity', '0')
            ->call('saveAdjustment')
            ->assertHasErrors('quantity');

        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_admin_can_adjust_a_variant_option(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $product = $this->product();
        $product->forceFill(['variant_product' => true, 'current_stock' => 0])->save();
        $stock = ProductStock::query()->create([
            'product_id' => $product->id,
            'variant' => 'Large',
            'sku' => 'TS-L',
            'price' => 10,
            'qty' => 2,
        ]);

        Livewire::test(Inventory::class)
            ->call('adjust', $product->id)
            ->set('stockId', (string) $stock->id)
            ->set('quantity', '6')
            ->set('reason', StockMovement::REASON_CORRECTION)
            ->call('saveAdjustment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_stocks', ['id' => $stock->id, 'qty' => 8]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'current_stock' => 0]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'product_stock_id' => $stock->id,
            'quantity' => 6,
            'balance' => 8,
            'reason' => StockMovement::REASON_CORRECTION,
        ]);
    }

    public function test_the_movement_log_lists_recent_changes(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $product = $this->product(currentStock: 1);

        Livewire::test(Inventory::class)
            ->call('adjust', $product->id)
            ->set('quantity', '4')
            ->set('reason', StockMovement::REASON_RESTOCK)
            ->set('note', 'Supplier delivery')
            ->call('saveAdjustment');

        Livewire::test(Inventory::class)
            ->assertSee('Recent movements')
            ->assertSee('Supplier delivery')
            ->assertSee('+4');
    }

    public function test_the_inventory_page_is_admin_only(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.inventory'))
            ->assertOk()
            ->assertSee('Inventory');

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.inventory'))
            ->assertForbidden();
    }

    private function product(int $currentStock = 5): Product
    {
        return Product::query()->create([
            'name' => 'Solar kit',
            'slug' => 'solar-kit',
            'category_id' => 1,
            'unit_price' => 100,
            'current_stock' => $currentStock,
        ]);
    }
}
