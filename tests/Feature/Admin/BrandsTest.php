<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Brands;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class BrandsTest extends CatalogTestCase
{
    public function test_admin_can_create_update_and_delete_a_brand_with_a_logo(): void
    {
        Storage::fake('public');
        $category = Category::query()->create(['name' => 'Accessories', 'slug' => 'accessories']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Brands::class)
            ->call('create')
            ->set('name', 'Acme')
            ->set('top', true)
            ->set('logoUpload', UploadedFile::fake()->image('acme.png', 100, 100))
            ->call('save')
            ->assertHasNoErrors();

        $brand = Brand::query()->where('name', 'Acme')->firstOrFail();
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'slug' => 'acme',
            'top' => 1,
            'logo' => null,
        ]);

        $logo = $brand->getFirstMedia('logo');
        $this->assertNotNull($logo);
        $this->assertSame('acme.png', $logo->file_name);
        $this->assertSame('brands', $logo->library());
        $this->assertSame('acme', $logo->folder);
        Storage::disk('public')->assertExists($logo->getPathRelativeToRoot());
        $oldLogoId = $logo->getKey();
        $oldLogoPath = $logo->getPathRelativeToRoot();

        Product::query()->create([
            'name' => 'Acme bag',
            'slug' => 'acme-bag',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit_price' => 15,
        ]);

        Livewire::test(Brands::class)
            ->call('edit', $brand->id)
            ->set('name', 'Acme Goods')
            ->set('logoUpload', UploadedFile::fake()->image('acme-new.png', 100, 100))
            ->call('save')
            ->assertHasNoErrors();

        $brand->refresh();
        $this->assertSame('acme-goods', $brand->slug);

        $logo = $brand->getFirstMedia('logo');
        $this->assertNotNull($logo);
        $this->assertSame('acme-new.png', $logo->file_name);
        $this->assertSame('acme-goods', $logo->folder);
        $this->assertNotSame($oldLogoId, $logo->getKey());
        Storage::disk('public')->assertMissing($oldLogoPath);
        Storage::disk('public')->assertExists($logo->getPathRelativeToRoot());

        Livewire::test(Brands::class)
            ->call('delete', $brand->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
        $this->assertDatabaseHas('products', ['slug' => 'acme-bag', 'brand_id' => null]);
        $this->assertDatabaseMissing('media', ['id' => $logo->getKey()]);
        Storage::disk('public')->assertMissing($logo->getPathRelativeToRoot());
    }

    public function test_brand_logo_rejects_non_image_uploads(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Brands::class)
            ->call('create')
            ->set('name', 'Invalid Brand')
            ->set('logoUpload', UploadedFile::fake()->createWithContent('logo.svg', '<svg></svg>'))
            ->call('save')
            ->assertHasErrors('logoUpload');

        $this->assertDatabaseMissing('brands', ['name' => 'Invalid Brand']);
        Storage::disk('public')->assertDirectoryEmpty('brands');
    }

    public function test_brand_page_is_protected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.brands'))
            ->assertForbidden();
    }
}
