<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Categories;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class CategoriesTest extends CatalogTestCase
{
    public function test_admin_can_create_update_and_delete_categories(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Categories::class)
            ->call('create')
            ->set('name', 'Electronics')
            ->set('discount', '5')
            ->set('coverImageUpload', UploadedFile::fake()->image('electronics.png', 100, 100))
            ->call('save')
            ->assertHasNoErrors();

        $parent = Category::query()->where('name', 'Electronics')->firstOrFail();
        $this->assertDatabaseHas('categories', [
            'id' => $parent->id,
            'slug' => 'electronics',
            'level' => 0,
            'discount' => 5,
            'cover_image' => null,
        ]);

        $cover = $parent->getFirstMedia('cover');
        $this->assertNotNull($cover);
        $this->assertSame('electronics.png', $cover->file_name);
        $this->assertSame('categories', $cover->library());
        $this->assertSame('electronics', $cover->folder);
        Storage::disk('public')->assertExists($cover->getPathRelativeToRoot());

        Livewire::test(Categories::class)
            ->call('create')
            ->set('name', 'Phones')
            ->set('parentId', (string) $parent->id)
            ->call('save')
            ->assertHasNoErrors();

        $child = Category::query()->where('name', 'Phones')->firstOrFail();
        $this->assertSame($parent->id, $child->parent_id);
        $this->assertSame(1, $child->level);

        Livewire::test(Categories::class)
            ->call('edit', $parent->id)
            ->set('name', 'Consumer Electronics')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('consumer-electronics', $parent->refresh()->slug);
        $this->assertSame($cover->getKey(), $parent->getFirstMedia('cover')?->getKey());
        Storage::disk('public')->assertExists($cover->getPathRelativeToRoot());

        Livewire::test(Categories::class)
            ->call('delete', $child->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('categories', ['id' => $child->id]);
    }

    public function test_category_cannot_be_nested_under_itself_or_its_descendant(): void
    {
        $root = Category::query()->create(['name' => 'Root', 'slug' => 'root', 'level' => 0]);
        $child = Category::query()->create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $root->id, 'level' => 1]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Categories::class)
            ->call('edit', $root->id)
            ->set('parentId', (string) $child->id)
            ->call('save')
            ->assertHasErrors('parentId');

        $this->assertSame(0, $root->refresh()->level);
        $this->assertNull($root->parent_id);
    }

    public function test_category_with_children_cannot_be_deleted(): void
    {
        $category = Category::query()->create(['name' => 'In use', 'slug' => 'in-use']);
        Category::query()->create(['name' => 'Child category', 'slug' => 'child-category', 'parent_id' => $category->id, 'level' => 1]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Categories::class)
            ->call('delete', $category->id)
            ->assertSee('cannot be deleted');

        $this->assertModelExists($category);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = Category::query()->create(['name' => 'In catalog', 'slug' => 'in-catalog']);
        $product = Product::query()->create([
            'name' => 'Catalog item',
            'slug' => 'catalog-item',
            'category_id' => $category->id,
            'unit_price' => 12,
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Categories::class)
            ->call('delete', $category->id)
            ->assertSee('cannot be deleted');

        $this->assertModelExists($category);
        $this->assertModelExists($product);
    }

    public function test_category_used_as_an_additional_product_category_cannot_be_deleted(): void
    {
        $category = Category::query()->create(['name' => 'Additional', 'slug' => 'additional']);
        $primaryCategory = Category::query()->create(['name' => 'Primary', 'slug' => 'primary']);
        $product = Product::query()->create([
            'name' => 'Multi-category item',
            'slug' => 'multi-category-item',
            'category_id' => $primaryCategory->id,
            'unit_price' => 12,
        ]);
        DB::table('product_categories')->insert([
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Categories::class)
            ->call('delete', $category->id)
            ->assertSee('cannot be deleted');

        $this->assertModelExists($category);
    }

    public function test_category_page_is_protected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.categories'))
            ->assertForbidden();
    }
}
