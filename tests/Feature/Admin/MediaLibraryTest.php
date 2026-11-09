<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\MediaBrowser;
use App\Models\Brand;
use App\Models\Media;
use App\Models\MediaShelf;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MediaLibraryTest extends CatalogTestCase
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
    private function staffWith(array $permissions, string $roleName = 'Media staff'): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $role->syncPermissions($permissions);

        $staff = User::factory()->create(['is_admin' => false]);
        $staff->assignRole($role);

        return $staff;
    }

    private function brandWithLogo(string $file = 'brand-logo.png'): Brand
    {
        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);
        $brand->addLibraryMedia(UploadedFile::fake()->image($file, 60, 60), 'logo', 'acme');

        return $brand;
    }

    public function test_uploads_keep_the_real_file_name_inside_the_owner_library_folder(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $brand = $this->brandWithLogo('Real Brand Logo.png');

        $media = $brand->getFirstMedia('logo');
        $this->assertNotNull($media);
        // The exact upload name is kept, while the file on disk is made URL safe.
        $this->assertSame('Real Brand Logo.png', $media->originalFileName());
        $this->assertSame('Real-Brand-Logo.png', $media->file_name);
        $this->assertSame('brands', $media->library());
        $this->assertSame('acme', $media->folder);
        $this->assertSame(
            'brands/acme/'.$media->getKey().'/Real-Brand-Logo.png',
            $media->getPathRelativeToRoot(),
        );
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
        $this->assertSame($media->getUrl(), $brand->logoUrl());
    }

    public function test_media_screen_upload_uses_the_selected_folder_and_sub_folder(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(MediaBrowser::class)
            ->call('create')
            ->set('upload', UploadedFile::fake()->image('Summer Banner.png', 80, 40))
            ->set('uploadLibrary', 'pages')
            ->set('uploadFolder', 'banners/2026')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isOpen', false);

        $media = Media::query()->sole();
        $this->assertSame('Summer Banner.png', $media->originalFileName());
        $this->assertSame('Summer-Banner.png', $media->file_name);
        $this->assertSame('banners/2026', $media->folder);
        $this->assertSame('pages', $media->library());
        $this->assertSame('pages/banners/2026/'.$media->getKey().'/Summer-Banner.png', $media->getPathRelativeToRoot());
        $this->assertNotNull($media->uploaded_by);
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_account_pictures_are_kept_out_of_the_shared_content_tab(): void
    {
        $this->brandWithLogo('brand-logo.png');

        $customer = User::factory()->create(['is_admin' => false]);
        $customer->addLibraryMedia(UploadedFile::fake()->image('customer-avatar.png', 40, 40), 'avatar', 'user-'.$customer->id);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(MediaBrowser::class)
            ->assertSee('brand-logo.png')
            ->assertDontSee('customer-avatar.png')
            ->call('selectLibrary', 'users')
            ->assertSee('Account pictures are kept in a separate folder')
            ->assertSee('customer-avatar.png')
            ->assertDontSee('brand-logo.png');
    }

    public function test_a_view_only_staff_member_cannot_upload_or_delete_media(): void
    {
        $this->brandWithLogo();
        $media = Media::query()->sole();
        $path = $media->getPathRelativeToRoot();

        $this->actingAs($this->staffWith(['media.view']));

        Livewire::test(MediaBrowser::class)
            ->assertOk()
            ->assertDontSee('Upload file')
            ->call('create')
            ->assertForbidden();

        Livewire::test(MediaBrowser::class)
            ->call('delete', $media->getKey())
            ->assertForbidden();

        $this->assertModelExists($media);
        Storage::disk('public')->assertExists($path);
    }

    public function test_a_media_manager_can_upload_and_delete_files(): void
    {
        $media = $this->brandWithLogo()->getFirstMedia('logo');
        $path = $media->getPathRelativeToRoot();

        $this->actingAs($this->staffWith(['media.view', 'media.manage'], 'Media manager'));

        Livewire::test(MediaBrowser::class)
            ->assertSee('Upload file')
            ->call('create')
            ->set('upload', UploadedFile::fake()->image('extra-file.png', 30, 30))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('media', 2);

        Livewire::test(MediaBrowser::class)
            ->call('delete', $media->getKey())
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('media', ['id' => $media->getKey()]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_the_media_screen_is_gated_by_its_permission(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.media'))
            ->assertForbidden();

        $this->actingAs($this->staffWith(['media.view']))
            ->get(route('admin.media'))
            ->assertOk()
            ->assertSee('Media library');
    }

    public function test_backfill_reports_legacy_files_without_writing_on_a_dry_run(): void
    {
        Storage::disk('public')->put('uploads/legacy-logo.png', UploadedFile::fake()->image('legacy-logo.png', 20, 20)->getContent());
        Brand::query()->create(['name' => 'Acme', 'slug' => 'acme', 'logo' => 'uploads/legacy-logo.png']);

        $this->artisan('media:backfill', ['--dry-run' => true])
            ->expectsOutputToContain('would import')
            ->assertSuccessful();

        $this->assertDatabaseCount('media', 0);
        Storage::disk('public')->assertExists('uploads/legacy-logo.png');
    }

    public function test_backfill_imports_legacy_files_and_keeps_the_original_on_disk(): void
    {
        Storage::disk('public')->put('uploads/legacy-logo.png', UploadedFile::fake()->image('legacy-logo.png', 20, 20)->getContent());
        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme', 'logo' => 'uploads/legacy-logo.png']);

        $this->artisan('media:backfill')->assertSuccessful();

        $media = $brand->refresh()->getFirstMedia('logo');
        $this->assertNotNull($media);
        $this->assertSame('legacy-logo.png', $media->file_name);
        $this->assertSame('brands', $media->library());
        $this->assertSame('acme', $media->folder);
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
        Storage::disk('public')->assertExists('uploads/legacy-logo.png');

        $this->artisan('media:backfill')
            ->expectsOutputToContain('Already present: 1')
            ->assertSuccessful();

        $this->assertDatabaseCount('media', 1);
    }

    public function test_backfill_can_prune_the_legacy_file_after_importing_it(): void
    {
        Storage::disk('public')->put('uploads/old-banner.png', UploadedFile::fake()->image('old-banner.png', 20, 20)->getContent());
        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme', 'logo' => 'uploads/old-banner.png']);

        $this->artisan('media:backfill', ['--prune' => true])->assertSuccessful();

        $media = $brand->refresh()->getFirstMedia('logo');
        $this->assertNotNull($media);
        Storage::disk('public')->assertMissing('uploads/old-banner.png');
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_the_shelf_holds_media_created_on_the_media_screen(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(MediaBrowser::class)
            ->call('create')
            ->set('upload', UploadedFile::fake()->image('shelf-file.png', 20, 20))
            ->call('save')
            ->assertHasNoErrors();

        $shelf = MediaShelf::attach();

        $this->assertCount(1, $shelf->shelfMedia());
        $this->assertSame('shelf-file.png', $shelf->shelfMedia()[0]->file_name);
    }
}
