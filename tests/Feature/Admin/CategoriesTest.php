<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Categories;
use App\Livewire\Admin\Products;
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
            ->assertSet('slug', 'electronics')
            ->set('description', 'Home electronics and smart appliances.')
            ->set('discount', '5')
            ->set('coverImageUpload', UploadedFile::fake()->image('electronics.png', 100, 100))
            ->call('save')
            ->assertHasNoErrors();

        $parent = Category::query()->where('name', 'Electronics')->firstOrFail();
        $this->assertDatabaseHas('categories', [
            'id' => $parent->id,
            'slug' => 'electronics',
            'description' => 'Home electronics and smart appliances.',
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

    public function test_category_generates_unique_slug_from_name_and_supports_clearing_description(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $existing = Category::query()->create(['name' => 'Solar Inverters']);
        $this->assertSame('solar-inverters', $existing->slug);

        $existing->update(['name' => 'Hybrid Inverters']);
        $this->assertSame('hybrid-inverters', $existing->refresh()->slug);

        $duplicate = Category::query()->create([
            'name' => 'Hybrid Inverters',
            'description' => 'Off-grid and hybrid inverter units.',
        ]);
        $this->assertSame('hybrid-inverters-2', $duplicate->slug);

        Livewire::test(Categories::class)
            ->call('edit', $duplicate->id)
            ->assertSet('slug', 'hybrid-inverters-2')
            ->assertSet('description', 'Off-grid and hybrid inverter units.')
            ->set('name', 'Hybrid Inverters')
            ->assertSet('slug', 'hybrid-inverters-2')
            ->set('description', '   ')
            ->call('save')
            ->assertHasNoErrors();

        $duplicate->refresh();
        $this->assertSame('hybrid-inverters-2', $duplicate->slug);
        $this->assertNull($duplicate->description);
    }

    public function test_category_description_uses_rich_editor_sanitizes_html_and_renders_plain_text_in_table(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Categories::class)
            ->call('create')
            ->assertSee('id="category-description"', false)
            ->assertSee('role="toolbar"', false)
            ->set('name', 'Solar Inverters')
            ->set('description', '<h2>MPPT Series</h2><p>High <strong>efficiency</strong> unit.<script>alert(1)</script></p><p><a href="javascript:alert(2)">Bad link</a> and <a href="https://example.com/specs" target="_blank" onclick="evil()">Specs</a>.</p>')
            ->call('save')
            ->assertHasNoErrors();

        $category = Category::query()->where('slug', 'solar-inverters')->firstOrFail();

        $this->assertStringContainsString('<h2>MPPT Series</h2>', (string) $category->description);
        $this->assertStringContainsString('<strong>efficiency</strong>', (string) $category->description);
        $this->assertStringContainsString('<a href="https://example.com/specs"', (string) $category->description);
        $this->assertStringContainsString('rel="noopener noreferrer"', (string) $category->description);
        $this->assertStringNotContainsString('<script', (string) $category->description);
        $this->assertStringNotContainsString('alert(1)', (string) $category->description);
        $this->assertStringNotContainsString('javascript:', (string) $category->description);
        $this->assertStringNotContainsString('onclick=', (string) $category->description);

        Livewire::test(Categories::class)
            ->assertSee('MPPT Series')
            ->assertSee('High efficiency unit.')
            ->assertDontSee('<h2>MPPT Series</h2>', false)
            ->call('edit', $category->id)
            ->set('description', '<p><br></p>')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($category->refresh()->description);
    }

    public function test_blocked_category_deletion_surfaces_fresh_flash_toast_and_inline_details_until_products_are_removed(): void
    {
        $category = Category::query()->create(['name' => 'Mounting Hardware', 'slug' => 'mounting-hardware']);
        $otherCategory = Category::query()->create(['name' => 'Solar Accessories', 'slug' => 'solar-accessories']);
        $rail = Product::query()->create([
            'name' => 'Aluminium Mounting Rail (4m)',
            'slug' => 'mounting-rail-4m',
            'category_id' => $category->id,
            'unit_price' => 45,
        ]);
        $bracket = Product::query()->create([
            'name' => 'Solar Panel Z-Bracket Set (4 pieces)',
            'slug' => 'panel-z-bracket-set',
            'category_id' => $category->id,
            'unit_price' => 15,
        ]);
        DB::table('product_categories')->insert([
            ['product_id' => $rail->id, 'category_id' => $category->id],
            ['product_id' => $bracket->id, 'category_id' => $category->id],
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $component = Livewire::test(Categories::class)
            ->call('edit', $category->id)
            ->call('save')
            ->assertSee('Category updated successfully.');

        preg_match('/wire:key="(flash-toast-[^"]+)"/', $component->html(), $firstToastMatch);
        $this->assertNotEmpty($firstToastMatch[1] ?? null);

        $component->call('delete', $category->id)
            ->assertSee('Category Mounting Hardware cannot be deleted')
            ->assertSee('2 products')
            ->assertSee('Aluminium Mounting Rail (4m)')
            ->assertSee('Solar Panel Z-Bracket Set (4 pieces)')
            ->assertSee('Open Products to reassign or delete items');

        preg_match('/wire:key="(flash-toast-[^"]+)"/', $component->html(), $secondToastMatch);
        $this->assertNotEmpty($secondToastMatch[1] ?? null);
        $this->assertNotSame($firstToastMatch[1], $secondToastMatch[1]);
        $this->assertModelExists($category);

        $component->call('dismissDeleteError')
            ->assertSet('deleteError', null);

        Livewire::test(Products::class)
            ->call('edit', $rail->id)
            ->set('categoryId', (string) $otherCategory->id)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(Products::class)
            ->call('edit', $bracket->id)
            ->set('categoryId', (string) $otherCategory->id)
            ->call('save')
            ->assertHasNoErrors();

        $component->call('delete', $category->id)
            ->assertHasNoErrors()
            ->assertSee('Category deleted successfully.');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
