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
}
