<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Units;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Livewire\Livewire;

class UnitsTest extends CatalogTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Products below reference category 1, which the products table enforces.
        Category::query()->create(['id' => 1, 'name' => 'General', 'slug' => 'general']);
    }

    public function test_admin_can_create_update_and_delete_a_unit(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Units::class)
            ->call('create')
            ->set('name', 'Kilogram')
            ->call('save')
            ->assertHasNoErrors();

        $unit = Unit::query()->where('name', 'Kilogram')->firstOrFail();

        Livewire::test(Units::class)
            ->call('edit', $unit->id)
            ->set('name', 'Kilograms')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('units', ['id' => $unit->id, 'name' => 'Kilograms']);

        $product = Product::query()->create([
            'name' => 'Maize flour',
            'slug' => 'maize-flour',
            'category_id' => 1,
            'unit_price' => 3000,
            'unit' => $unit->id,
        ]);

        Livewire::test(Units::class)->call('delete', $unit->id)->assertHasNoErrors();

        $this->assertDatabaseMissing('units', ['id' => $unit->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'unit' => null]);
    }

    public function test_unit_names_must_be_unique(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        Unit::query()->create(['name' => 'Litre']);

        Livewire::test(Units::class)
            ->call('create')
            ->set('name', 'Litre')
            ->call('save')
            ->assertHasErrors('name');

        $this->assertSame(1, Unit::query()->where('name', 'Litre')->count());
    }

    public function test_the_units_page_is_admin_only(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.units'))
            ->assertOk()
            ->assertSee('Units');

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.units'))
            ->assertForbidden();
    }
}
