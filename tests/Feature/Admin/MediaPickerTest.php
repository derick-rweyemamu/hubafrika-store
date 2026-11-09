<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Brands;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\CmsMenus;
use App\Livewire\Admin\MediaPicker;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\Settings;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\CmsMenuItem;
use App\Models\Media;
use App\Models\MediaReference;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MediaPickerTest extends CatalogTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(PermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function staffWith(array $permissions, string $roleName = 'Catalog staff'): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $role->syncPermissions($permissions);

        $staff = User::factory()->create(['is_admin' => false]);
        $staff->assignRole($role);

        return $staff;
    }

    private function libraryPicture(string $name = 'hero.png', int $width = 80, int $height = 40): Media
    {
        $owner = Brand::query()->create(['name' => 'Library', 'slug' => 'library-'.Str::lower(Str::random(8))]);

        return $owner->addLibraryMedia(UploadedFile::fake()->image($name, $width, $height), 'logo', 'library');
    }

    public function test_the_picker_lists_library_pictures_and_reports_the_choice(): void
    {
        $picture = $this->libraryPicture('summer-hero.png');

        $customer = User::factory()->create(['is_admin' => false]);
        $customer->addLibraryMedia(UploadedFile::fake()->image('customer-avatar.png', 40, 40), 'avatar', 'user-'.$customer->id);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(MediaPicker::class)
            ->assertOk()
            ->assertSee('summer-hero.png')
            ->assertDontSee('customer-avatar.png')
            ->call('select', $picture->getKey())
            ->assertSet('selectedIds', [$picture->getKey()])
            ->assertDispatched('media-picked', mediaId: $picture->getKey())
            ->call('close')
            ->assertDispatched('media-picker-closed');
    }

    public function test_the_picker_refuses_files_that_are_not_pictures(): void
    {
        $owner = Brand::query()->create(['name' => 'Library', 'slug' => 'library']);
        $document = $owner->addLibraryMedia(UploadedFile::fake()->create('invoice.pdf', 20, 'application/pdf'), 'logo', 'library');

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(MediaPicker::class)
            ->call('select', $document->getKey())
            ->assertStatus(422);
    }

    public function test_a_brand_logo_can_be_picked_from_the_library_without_being_copied(): void
    {
        $source = $this->libraryPicture('acme-logo.png', 60, 60);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Brands::class)
            ->call('create')
            ->set('name', 'Acme')
            ->call('openMediaPicker', 'logo')
            ->assertSet('mediaPickerOpen', true)
            ->call('storePickedMedia', $source->getKey())
            ->assertSet('mediaPickerOpen', false)
            ->assertSet('pickedMediaIds', ['logo' => [$source->getKey()]])
            ->call('save')
            ->assertHasNoErrors();

        $brand = Brand::query()->where('name', 'Acme')->firstOrFail();

        // The brand shows the library picture itself, no second row or file.
        $this->assertSame(0, $brand->getMedia('logo')->count());
        $this->assertSame($source->getUrl(), $brand->logoUrl());
        $this->assertSame(1, Media::query()->count());
        $this->assertDatabaseHas('media_references', [
            'mediable_type' => Brand::class,
            'mediable_id' => $brand->id,
            'collection' => 'logo',
            'media_id' => $source->getKey(),
        ]);
        Storage::disk('public')->assertExists($source->getPathRelativeToRoot());
    }

    public function test_a_category_banner_can_be_picked_from_the_library(): void
    {
        $source = $this->libraryPicture('category-banner.png', 120, 60);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Categories::class)
            ->call('create')
            ->set('name', 'Clothing')
            ->call('openMediaPicker', 'banner')
            ->call('storePickedMedia', $source->getKey())
            ->call('save')
            ->assertHasNoErrors();

        $category = Category::query()->where('name', 'Clothing')->firstOrFail();

        $this->assertSame(0, $category->getMedia('banner')->count());
        $this->assertSame($source->getUrl(), $category->bannerUrl());
        $this->assertDatabaseHas('media_references', [
            'mediable_type' => Category::class,
            'mediable_id' => $category->id,
            'collection' => 'banner',
            'media_id' => $source->getKey(),
        ]);
    }

    public function test_a_product_gallery_collects_several_picked_pictures(): void
    {
        $first = $this->libraryPicture('shirt-front.png', 80, 80);
        $second = $this->libraryPicture('shirt-back.png', 80, 80);
        $category = Category::query()->create(['name' => 'Clothing', 'slug' => 'clothing']);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Products::class)
            ->call('create')
            ->set('name', 'Cotton Shirt')
            ->set('categoryId', (string) $category->id)
            ->set('unitPrice', '24.50')
            ->set('currentStock', '4')
            ->call('openMediaPicker', 'gallery')
            ->call('storePickedMedia', $first->getKey())
            ->call('storePickedMedia', $second->getKey())
            ->assertSet('mediaPickerOpen', true)
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::query()->where('name', 'Cotton Shirt')->firstOrFail();

        $this->assertCount(2, $product->referencedMedia('gallery'));
        $this->assertSame(0, $product->getMedia('gallery')->count());
        $this->assertSame([$first->getUrl(), $second->getUrl()], $product->galleryUrls());
        $this->assertSame($first->getUrl(), $product->thumbnailUrl());
    }

    public function test_a_product_gallery_keeps_its_picks_when_the_product_is_edited(): void
    {
        $first = $this->libraryPicture('mug-front.png', 80, 80);
        $second = $this->libraryPicture('mug-side.png', 80, 80);
        $category = Category::query()->create(['name' => 'Kitchen', 'slug' => 'kitchen']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $product = Product::query()->create([
            'name' => 'Enamel Mug',
            'slug' => 'enamel-mug',
            'category_id' => $category->id,
            'unit_price' => 12,
            'current_stock' => 3,
            'published' => true,
        ]);
        $product->addLibraryMediaReference((int) $first->getKey(), 'gallery');
        $product->addLibraryMediaReference((int) $second->getKey(), 'gallery');

        Livewire::test(Products::class)
            ->call('edit', $product->id)
            ->assertSet('existingPhotos.0.url', $first->getUrl())
            ->assertSet('existingPhotos.1.url', $second->getUrl())
            ->call('save')
            ->assertHasNoErrors();

        // Opening and saving the product again must not drop its pictures.
        $this->assertSame([$first->getUrl(), $second->getUrl()], $product->refresh()->galleryUrls());
    }

    public function test_a_product_cannot_keep_more_than_five_gallery_pictures(): void
    {
        $category = Category::query()->create(['name' => 'Clothing', 'slug' => 'clothing']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $component = Livewire::test(Products::class)
            ->call('create')
            ->set('name', 'Overloaded Mug')
            ->set('categoryId', (string) $category->id)
            ->set('unitPrice', '5')
            ->set('currentStock', '1')
            ->call('openMediaPicker', 'gallery');

        foreach (range(1, 5) as $index) {
            $component->call('storePickedMedia', $this->libraryPicture("mug-{$index}.png")->getKey());
        }

        // An upload added after the picks is still counted against the limit.
        $component
            ->set('uploads', [UploadedFile::fake()->image('mug-upload.png', 60, 60)])
            ->call('save')
            ->assertHasErrors('uploads');

        $this->assertDatabaseMissing('products', ['name' => 'Overloaded Mug']);
    }

    public function test_a_single_picked_gallery_picture_can_be_dropped_before_saving(): void
    {
        $first = $this->libraryPicture('tee-front.png', 80, 80);
        $second = $this->libraryPicture('tee-back.png', 80, 80);
        $category = Category::query()->create(['name' => 'Clothing', 'slug' => 'clothing']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Products::class)
            ->call('create')
            ->set('name', 'Two Photo Tee')
            ->set('categoryId', (string) $category->id)
            ->set('unitPrice', '9')
            ->set('currentStock', '2')
            ->call('openMediaPicker', 'gallery')
            ->call('storePickedMedia', $first->getKey())
            ->call('storePickedMedia', $second->getKey())
            ->call('removePickedMedia', 'gallery', 0)
            ->assertSet('pickedMediaIds', ['gallery' => [$second->getKey()]])
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::query()->where('name', 'Two Photo Tee')->firstOrFail();

        $this->assertSame([$second->getUrl()], $product->galleryUrls());
    }

    public function test_a_branding_picture_can_be_picked_from_the_library(): void
    {
        $source = $this->libraryPicture('brand-mark.png', 120, 60);
        BusinessSetting::query()->create(['type' => 'business_logo', 'value' => null]);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Settings::class)
            ->call('edit', 'business_logo')
            ->call('openMediaPicker', 'branding')
            ->call('storePickedMedia', $source->getKey())
            ->call('save')
            ->assertHasNoErrors();

        $setting = BusinessSetting::query()->where('type', 'business_logo')->firstOrFail();

        $this->assertSame(0, $setting->getMedia('branding')->count());
        $this->assertSame($source->getUrl(), $setting->imageUrl());
    }

    public function test_a_menu_link_image_can_be_picked_from_the_library(): void
    {
        $source = $this->libraryPicture('menu-card.png', 120, 60);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $component = Livewire::test(CmsMenus::class)
            ->set('label', 'Featured Deals')
            ->set('url', '/featured-deals')
            ->call('addItem')
            ->assertHasNoErrors();

        $item = CmsMenuItem::query()->where('label', 'Featured Deals')->firstOrFail();

        $component->call('editItem', $item->id)
            ->call('openMediaPicker', 'item_image')
            ->call('storePickedMedia', $source->getKey())
            ->call('saveMenuItem')
            ->assertHasNoErrors();

        $item = $item->refresh();

        $this->assertSame(0, $item->getMedia('image')->count());
        $this->assertSame($source->getUrl(), $item->imageUrl());
        $this->assertDatabaseHas('media_references', [
            'mediable_type' => CmsMenuItem::class,
            'mediable_id' => $item->id,
            'collection' => 'image',
            'media_id' => $source->getKey(),
        ]);
    }

    public function test_the_same_library_picture_can_be_used_by_several_records(): void
    {
        $source = $this->libraryPicture('shared-hero.png', 120, 60);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        foreach (['Northwind', 'Southwind'] as $name) {
            Livewire::test(Brands::class)
                ->call('create')
                ->set('name', $name)
                ->call('openMediaPicker', 'logo')
                ->call('storePickedMedia', $source->getKey())
                ->call('save')
                ->assertHasNoErrors();
        }

        // One picture in the library, two brands showing it.
        $this->assertSame(1, Media::query()->count());
        $this->assertSame(2, MediaReference::query()->where('media_id', $source->getKey())->count());
        $this->assertSame($source->getUrl(), Brand::query()->where('name', 'Northwind')->firstOrFail()->logoUrl());
        $this->assertSame($source->getUrl(), Brand::query()->where('name', 'Southwind')->firstOrFail()->logoUrl());
    }

    public function test_picking_a_picture_removes_the_copy_the_record_used_to_own(): void
    {
        $source = $this->libraryPicture('replacement.png', 120, 60);
        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);
        $brand->addLibraryMedia(UploadedFile::fake()->image('old-logo.png', 60, 60), 'logo', 'acme');
        $oldFile = $brand->getFirstMedia('logo')->getPathRelativeToRoot();

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Brands::class)
            ->call('edit', $brand->id)
            ->call('openMediaPicker', 'logo')
            ->call('storePickedMedia', $source->getKey())
            ->call('save')
            ->assertHasNoErrors();

        $brand->refresh();

        $this->assertSame(0, $brand->getMedia('logo')->count());
        Storage::disk('public')->assertMissing($oldFile);
        $this->assertSame($source->getUrl(), $brand->logoUrl());
    }

    public function test_picking_the_picture_a_record_already_owns_keeps_the_file(): void
    {
        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);
        $logo = $brand->addLibraryMedia(UploadedFile::fake()->image('own-logo.png', 60, 60), 'logo', 'acme');

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Brands::class)
            ->call('edit', $brand->id)
            ->call('openMediaPicker', 'logo')
            ->call('storePickedMedia', $logo->getKey())
            ->call('save')
            ->assertHasNoErrors();

        $brand->refresh();

        $this->assertModelExists($logo);
        Storage::disk('public')->assertExists($logo->getPathRelativeToRoot());
        $this->assertSame($logo->getUrl(), $brand->logoUrl());
    }

    public function test_deleting_a_record_forgets_the_pictures_it_pointed_at(): void
    {
        $source = $this->libraryPicture('menu-card.png', 120, 60);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);
        $brand->referenceLibraryMedia((int) $source->getKey(), 'logo');

        Livewire::test(Brands::class)
            ->call('delete', $brand->id)
            ->assertHasNoErrors();

        $this->assertSame(0, MediaReference::query()->count());
        // The library picture itself is untouched.
        $this->assertModelExists($source);
    }

    public function test_a_favicon_pick_must_be_a_square_png(): void
    {
        $source = $this->libraryPicture('wide-banner.png', 200, 60);
        BusinessSetting::query()->create(['type' => 'favicon', 'value' => null]);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Settings::class)
            ->call('edit', 'favicon')
            ->call('openMediaPicker', 'branding')
            ->call('storePickedMedia', $source->getKey())
            ->call('save')
            ->assertHasErrors('upload');

        $this->assertSame(0, BusinessSetting::query()->where('type', 'favicon')->firstOrFail()->getMedia('branding')->count());
    }

    public function test_picking_a_library_picture_needs_the_media_view_permission(): void
    {
        $source = $this->libraryPicture();
        $category = Category::query()->create(['name' => 'Clothing', 'slug' => 'clothing']);

        $this->actingAs($this->staffWith(['categories.view', 'categories.manage']));

        Livewire::test(Categories::class)
            ->call('create')
            ->call('openMediaPicker', 'banner')
            ->assertForbidden();

        Livewire::test(Categories::class)
            ->call('create')
            ->call('storePickedMedia', $source->getKey())
            ->assertForbidden();

        Livewire::test(MediaPicker::class)->assertForbidden();
    }
}
