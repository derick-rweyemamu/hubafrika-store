<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Products;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class ProductsTest extends CatalogTestCase
{
    public function test_admin_can_create_update_and_delete_a_product_with_its_images(): void
    {
        Storage::fake('public');
        $category = Category::query()->create(['name' => 'Clothing', 'slug' => 'clothing']);
        $brand = Brand::query()->create(['name' => 'Northwind', 'slug' => 'northwind']);
        $unitId = DB::table('units')->insertGetId(['name' => 'Piece']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Products::class)
            ->call('create')
            ->set('name', 'Cotton Shirt')
            ->assertSet('slug', 'cotton-shirt')
            ->set('categoryId', (string) $category->id)
            ->set('brandId', (string) $brand->id)
            ->set('unitId', (string) $unitId)
            ->set('unitPrice', '24.50')
            ->set('purchasePrice', '10')
            ->set('currentStock', '8')
            ->set('discount', '10')
            ->set('discountType', 'percent')
            ->set('barcode', 'shirt-001')
            ->set('tags', 'cotton,shirt')
            ->set('description', 'A cotton shirt.')
            ->set('published', true)
            ->set('featured', true)
            ->set('uploads', [UploadedFile::fake()->image('shirt.png', 100, 100)])
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::query()->where('name', 'Cotton Shirt')->firstOrFail();
        $this->assertModelExists($product);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'slug' => 'cotton-shirt',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit' => $unitId,
            'unit_price' => 24.5,
            'purchase_price' => 10,
            'current_stock' => 8,
            'discount_type' => 'percent',
            'barcode' => 'shirt-001',
            'published' => 1,
        ]);
        $this->assertSame([], $product->photos);
        $this->assertNull($product->thumbnail_img);

        $photo = $product->getFirstMedia('gallery');
        $this->assertNotNull($photo);
        $this->assertSame('shirt.png', $photo->file_name);
        $this->assertSame('products', $photo->library());
        $this->assertSame('cotton-shirt', $photo->folder);
        Storage::disk('public')->assertExists($photo->getPathRelativeToRoot());
        $this->assertSame($photo->getUrl(), $product->thumbnailUrl());
        $oldPhotoPath = $photo->getPathRelativeToRoot();

        Livewire::test(Products::class)
            ->call('edit', $product->id)
            ->set('name', 'Cotton Shirt Premium')
            ->call('removePhoto', 0)
            ->call('save')
            ->assertHasNoErrors();

        $product->refresh();
        $this->assertSame('cotton-shirt-premium', $product->slug);
        $this->assertSame([], $product->photos);
        $this->assertTrue($product->getMedia('gallery')->isEmpty());
        Storage::disk('public')->assertMissing($oldPhotoPath);

        Livewire::test(Products::class)
            ->call('delete', $product->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_requires_a_valid_category_and_price(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Products::class)
            ->call('create')
            ->set('name', 'Uncategorized product')
            ->set('unitPrice', '-1')
            ->set('currentStock', '0')
            ->call('save')
            ->assertHasErrors(['categoryId', 'unitPrice']);

        $this->assertDatabaseMissing('products', ['name' => 'Uncategorized product']);
    }

    public function test_admin_product_page_is_protected_and_renders_the_catalog(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.products'))
            ->assertOk()
            ->assertSee('Product catalog')
            ->assertSee('Manage categories');

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.products'))
            ->assertForbidden();
    }

    public function test_product_sorting_rejects_unapproved_columns(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Products::class)
            ->call('sortBy', 'name desc; drop table products')
            ->assertSet('sortField', 'created_at');
    }

    public function test_products_with_sales_cannot_be_deleted(): void
    {
        $category = Category::query()->create(['name' => 'Furniture', 'slug' => 'furniture']);
        $product = Product::query()->create([
            'name' => 'Chair',
            'slug' => 'chair',
            'category_id' => $category->id,
            'unit_price' => 25,
            'num_of_sale' => 1,
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Products::class)
            ->call('delete', $product->id)
            ->assertSee('Products with sales cannot be deleted');

        $this->assertModelExists($product);
    }

    public function test_product_generates_unique_slug_from_name_and_loads_description_for_editing(): void
    {
        $category = Category::query()->create(['name' => 'Solar Power']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $first = Product::query()->create([
            'name' => 'MPPT Charge Controller',
            'category_id' => $category->id,
            'unit_price' => 120,
            'description' => '60A MPPT solar charge controller.',
        ]);
        $this->assertSame('mppt-charge-controller', $first->slug);

        $first->update(['name' => 'Smart MPPT Controller']);
        $this->assertSame('smart-mppt-controller', $first->refresh()->slug);

        Livewire::test(Products::class)
            ->call('create')
            ->set('name', 'Smart MPPT Controller')
            ->assertSet('slug', 'smart-mppt-controller-2')
            ->set('categoryId', (string) $category->id)
            ->set('unitPrice', '145')
            ->set('currentStock', '5')
            ->set('description', 'Updated controller model.')
            ->call('save')
            ->assertHasNoErrors();

        $duplicate = Product::query()->where('slug', 'smart-mppt-controller-2')->firstOrFail();
        $this->assertSame('Updated controller model.', $duplicate->description);

        Livewire::test(Products::class)
            ->call('edit', $duplicate->id)
            ->assertSet('slug', 'smart-mppt-controller-2')
            ->assertSet('description', 'Updated controller model.')
            ->set('description', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($duplicate->refresh()->description);
    }

    public function test_product_description_uses_rich_editor_sanitizes_html_and_clears_empty_markup(): void
    {
        $category = Category::query()->create(['name' => 'Batteries', 'slug' => 'batteries']);
        $unitId = DB::table('units')->insertGetId(['name' => 'Piece']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Products::class)
            ->call('create')
            ->assertSee('id="product-description"', false)
            ->assertSee('role="toolbar"', false)
            ->set('name', 'LiFePO4 Storage Bank')
            ->set('categoryId', (string) $category->id)
            ->set('unitId', (string) $unitId)
            ->set('unitPrice', '950.00')
            ->set('currentStock', '12')
            ->set('description', '<p>Heavy-duty <strong>LiFePO4</strong> battery bank.<script>alert(1)</script></p><ul><li>6000 cycles</li></ul><p><a href="javascript:alert(2)">Bad</a></p>')
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::query()->where('slug', 'lifepo4-storage-bank')->firstOrFail();

        $this->assertStringContainsString('<strong>LiFePO4</strong>', (string) $product->description);
        $this->assertStringContainsString('<ul>', (string) $product->description);
        $this->assertStringContainsString('<li>6000 cycles</li>', (string) $product->description);
        $this->assertStringNotContainsString('<script', (string) $product->description);
        $this->assertStringNotContainsString('alert(1)', (string) $product->description);
        $this->assertStringNotContainsString('javascript:', (string) $product->description);

        Livewire::test(Products::class)
            ->call('edit', $product->id)
            ->set('description', '<p><br></p>')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($product->refresh()->description);
    }
}
