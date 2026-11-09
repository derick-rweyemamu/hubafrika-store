<?php

namespace Tests\Feature\Storefront;

use App\Livewire\Storefront\Shop;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;
use Tests\Feature\Admin\CatalogTestCase;

class ShopTest extends CatalogTestCase
{
    public function test_cards_show_the_category_the_discounted_price_and_the_cart_button(): void
    {
        BusinessSetting::query()->create(['type' => 'currency_code', 'value' => 'TZS']);
        $category = Category::query()->create(['name' => 'Solar Panels', 'slug' => 'solar-panels']);
        $this->product([
            'name' => '550W Mono Solar Panel',
            'slug' => 'mono-solar-panel',
            'category_id' => $category->id,
            'unit_price' => 2700000,
            'discount' => 250000,
            'discount_type' => 'amount',
        ]);

        $response = $this->get(route('shop'));

        $response->assertOk()
            ->assertSee('shop-page', false)
            ->assertSee('shop-grid', false)
            ->assertSee('shop-card', false)
            ->assertSee('Solar Panels')
            ->assertSee('550W Mono Solar Panel')
            ->assertSee('TZS 2,700,000')
            ->assertSee('TZS 2,450,000')
            ->assertSee('shop-price-was', false)
            ->assertSee('Add to cart');
    }

    public function test_a_product_without_a_discount_shows_a_single_price(): void
    {
        BusinessSetting::query()->create(['type' => 'currency_code', 'value' => 'TZS']);
        $this->product([
            'name' => '5kWh Lithium Battery',
            'slug' => 'lithium-battery',
            'unit_price' => 320000,
            'discount' => 0,
        ]);

        $this->get(route('shop'))
            ->assertOk()
            ->assertSee('TZS 320,000')
            ->assertDontSee('shop-price-was', false);
    }

    public function test_percent_discounts_come_off_the_unit_price(): void
    {
        $product = $this->product([
            'name' => 'Hybrid Inverter',
            'slug' => 'hybrid-inverter',
            'unit_price' => 100,
            'discount' => 15,
            'discount_type' => 'percent',
        ]);

        $this->assertSame(85.0, $product->discountedUnitPrice());
    }

    public function test_an_amount_discount_can_never_push_the_price_below_zero(): void
    {
        $product = $this->product([
            'name' => 'Clearance Panel',
            'slug' => 'clearance-panel',
            'unit_price' => 50,
            'discount' => 80,
            'discount_type' => 'amount',
        ]);

        $this->assertSame(0.0, $product->discountedUnitPrice());
    }

    public function test_the_sidebar_lists_the_categories_and_brands_on_offer(): void
    {
        $category = Category::query()->create(['name' => 'Batteries', 'slug' => 'batteries']);
        $brand = Brand::query()->create(['name' => 'Atess', 'slug' => 'atess']);
        $this->product([
            'name' => '5kWh Lithium Battery',
            'slug' => 'lithium-battery',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $this->get(route('shop'))
            ->assertOk()
            ->assertSee('shop-side', false)
            ->assertSee('shop-filter-count', false)
            ->assertSee('Batteries')
            ->assertSee('Atess')
            ->assertSee('Price range')
            ->assertDontSee('Clear filters');
    }

    public function test_choosing_a_category_also_matches_the_products_in_its_children(): void
    {
        $parent = Category::query()->create(['name' => 'Solar', 'slug' => 'solar']);
        $child = Category::query()->create(['name' => 'Panels', 'slug' => 'panels', 'parent_id' => $parent->id]);
        $other = Category::query()->create(['name' => 'Batteries', 'slug' => 'batteries']);
        $this->product(['name' => 'Mono Panel', 'slug' => 'mono-panel', 'category_id' => $child->id]);
        $this->product(['name' => 'Lithium Battery', 'slug' => 'lithium-battery', 'category_id' => $other->id]);

        Livewire::test(Shop::class)
            ->call('filterByCategory', $parent->id)
            ->assertSet('categoryId', $parent->id)
            ->assertSee('Mono Panel')
            ->assertDontSee('Lithium Battery')
            ->assertSee('Clear filters')
            ->call('filterByCategory', $parent->id)
            ->assertSet('categoryId', null)
            ->assertSee('Lithium Battery');
    }

    public function test_choosing_a_brand_filters_the_grid(): void
    {
        $atess = Brand::query()->create(['name' => 'Atess', 'slug' => 'atess']);
        $victron = Brand::query()->create(['name' => 'Victron', 'slug' => 'victron']);
        $this->product(['name' => 'Atess Battery', 'slug' => 'atess-battery', 'brand_id' => $atess->id]);
        $this->product(['name' => 'Victron Inverter', 'slug' => 'victron-inverter', 'brand_id' => $victron->id]);

        Livewire::test(Shop::class)
            ->call('filterByBrand', $atess->id)
            ->assertSet('brandId', $atess->id)
            ->assertSee('Atess Battery')
            ->assertDontSee('Victron Inverter')
            ->call('clearFilters')
            ->assertSet('brandId', null)
            ->assertSee('Victron Inverter');
    }

    public function test_the_price_range_filters_on_the_discounted_price_the_cards_show(): void
    {
        $this->product([
            'name' => 'Discounted Panel',
            'slug' => 'discounted-panel',
            'unit_price' => 2700000,
            'discount' => 250000,
            'discount_type' => 'amount',
        ]);
        $this->product(['name' => 'Pricey Inverter', 'slug' => 'pricey-inverter', 'unit_price' => 5000000]);

        // The list price is above the ceiling, but the sale price is not.
        Livewire::test(Shop::class)
            ->set('maxPrice', '2500000')
            ->assertSee('Discounted Panel')
            ->assertDontSee('Pricey Inverter')
            ->set('minPrice', '1000000')
            ->assertSee('Discounted Panel')
            ->set('minPrice', '3000000')
            ->assertDontSee('Discounted Panel');
    }

    public function test_a_reversed_price_range_is_read_as_the_range_the_shopper_meant(): void
    {
        $this->product(['name' => 'Mid Panel', 'slug' => 'mid-panel', 'unit_price' => 2000000]);
        $this->product(['name' => 'Pricey Inverter', 'slug' => 'pricey-inverter', 'unit_price' => 5000000]);

        Livewire::test(Shop::class)
            ->set('minPrice', '2500000')
            ->set('maxPrice', '1000000')
            ->assertSee('Mid Panel')
            ->assertDontSee('Pricey Inverter');
    }

    public function test_every_filter_group_can_be_collapsed(): void
    {
        $category = Category::query()->create(['name' => 'Batteries', 'slug' => 'batteries']);
        $brand = Brand::query()->create(['name' => 'Atess', 'slug' => 'atess']);
        $this->product([
            'name' => '5kWh Lithium Battery',
            'slug' => 'lithium-battery',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $html = Livewire::test(Shop::class)->html();

        foreach (['category', 'brand', 'price'] as $group) {
            $this->assertStringContainsString('aria-controls="shop-filters-'.$group.'"', $html);
            $this->assertStringContainsString('id="shop-filters-'.$group.'"', $html);
        }

        $this->assertSame(3, substr_count($html, 'shop-filter-toggle'));
        $this->assertSame(3, substr_count($html, 'x-show="open"'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function product(array $attributes): Product
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'accessories'],
            ['name' => 'Accessories'],
        );

        return Product::query()->create($attributes + [
            'category_id' => $category->id,
            'unit_price' => 100,
            'published' => true,
            'approved' => true,
            'current_stock' => 10,
        ]);
    }
}
