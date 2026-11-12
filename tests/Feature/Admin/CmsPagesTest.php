<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\CmsPages;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class CmsPagesTest extends CatalogTestCase
{
    public function test_admin_can_save_a_draft_page_then_open_its_builder_from_the_page_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $component = Livewire::test(CmsPages::class)
            ->set('title', 'About the shop')
            ->assertSet('slug', 'about-the-shop')
            ->set('slug', 'manually-changed')
            ->call('savePage')
            ->assertHasNoErrors();

        $page = CmsPage::query()->where('slug', 'about-the-shop')->firstOrFail();

        $component->assertRedirect(route('admin.cms.pages'));
        $this->assertSame($admin->id, $page->created_by);
        $this->assertFalse($page->isPublished());
        $this->assertSame(2, json_decode($page->content, true, flags: JSON_THROW_ON_ERROR)['version']);
        Livewire::test(CmsPages::class)
            ->assertSee('Edit')
            ->assertSee('Open builder');
    }

    public function test_admin_can_save_a_page_as_published(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsPages::class)
            ->set('title', 'Published landing page')
            ->set('status', 'published')
            ->call('savePage')
            ->assertRedirect(route('admin.cms.pages'))
            ->assertHasNoErrors();

        $page = CmsPage::query()->where('slug', 'published-landing-page')->firstOrFail();
        $this->assertTrue($page->isPublished());
    }

    public function test_admin_can_edit_page_details_and_status_without_changing_its_design(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $originalContent = json_encode([
            'version' => 2,
            'published' => true,
            'project_data' => ['pages' => [['frames' => []]]],
            'html' => '<h1>Existing design</h1>',
            'css' => 'h1 { color: red; }',
        ], JSON_THROW_ON_ERROR);
        $page = CmsPage::query()->create([
            'title' => 'Original name',
            'slug' => 'original-name',
            'content' => $originalContent,
        ]);

        Livewire::test(CmsPages::class)
            ->call('editPage', $page->id)
            ->assertSet('status', 'published')
            ->set('title', 'Updated name')
            ->assertSet('slug', 'updated-name')
            ->set('status', 'draft')
            ->call('savePage')
            ->assertRedirect(route('admin.cms.pages'))
            ->assertHasNoErrors();

        $this->assertSame('Updated name', $page->refresh()->title);
        $this->assertSame('updated-name', $page->slug);
        $this->assertFalse($page->isPublished());
        $content = json_decode($page->content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(['pages' => [['frames' => []]]], $content['project_data']);
        $this->assertSame('<h1>Existing design</h1>', $content['html']);
        $this->assertSame('h1 { color: red; }', $content['css']);
    }

    public function test_visual_builder_saves_project_html_css_and_published_status(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $page = CmsPage::query()->create([
            'title' => 'About',
            'slug' => 'about',
            'content' => json_encode([
                'version' => 2,
                'published' => false,
                'project_data' => [],
                'html' => '',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);

        $this->putJson(route('admin.cms.pages.builder.update', $page), [
            'title' => 'Our story',
            'slug' => 'our-story',
            'published' => true,
            'project_data' => ['pages' => [['frames' => []]]],
            'html' => '<section><h1>Welcome</h1><div data-hubafrika-products="true" style="min-height:120px"></div></section>',
            'css' => 'h1 { color: #111827; }',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('url', route('pages.show', 'our-story'));

        $page->refresh();
        $content = json_decode($page->content, true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('Our story', $page->title);
        $this->assertSame('our-story', $page->slug);
        $this->assertTrue($page->isPublished());
        $this->assertSame(2, $content['version']);
        $this->assertSame('<section><h1>Welcome</h1><div data-hubafrika-products="true" style="min-height:120px"></div></section>', $content['html']);
        $this->assertSame('h1 { color: #111827; }', $content['css']);
        $this->assertSame(['pages' => [['frames' => []]]], $content['project_data']);
    }

    public function test_visual_builder_loads_existing_canvas_page_content(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $page = CmsPage::query()->create([
            'title' => 'Legacy page',
            'slug' => 'legacy',
            'content' => json_encode([
                'version' => 1,
                'published' => false,
                'blocks' => [
                    [
                        'type' => 'heading',
                        'text' => 'Saved before the visual builder',
                        'x' => 5,
                        'y' => 20,
                        'width' => 90,
                        'height' => 140,
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ]);

        $this->get(route('admin.cms.pages.builder', $page))
            ->assertOk()
            ->assertSee('Page builder')
            ->assertSee('<html lang=', false)
            ->assertSee('height: 100%', false)
            ->assertDontSee('id="admin-sidebar"', false)
            ->assertSee('showDevices: false', false)
            ->assertSee('data-builder-device="Desktop"', false)
            ->assertSee('data-builder-device="Tablet"', false)
            ->assertSee('data-builder-device="Mobile"', false)
            ->assertSee('Saved before the visual builder')
            ->assertSee('grid-6-col')
            ->assertSee('hubafrika-page-header')
            ->assertSee('Page Header')
            ->assertSee('widget-tabs')
            ->assertSee('widget-contact-form')
            ->assertSee('widget-google-maps')
            ->assertSee('school-faq-accordion')
            ->assertSee('font-awesome/6.5.2/css/all.min.css', false)
            ->assertSee('--storefront-primary:', false)
            ->assertSee('syncCanvasTheme')
            ->assertSee('data-hubafrika-tabs')
            ->assertSee('hubafrika-marquee-track')
            ->assertSee('data-hubafrika-logo-slider')
            ->assertSee('data-slides-visible')
            ->assertSee('data-slide-step')
            ->assertSee('data-slide-loop')
            ->assertSee('data-logo-slide')
            ->assertSee('Images to slide at a time')
            ->assertSee('Open in new window')
            ->assertDontSee('hubafrika-logo-nav--prev', false)
            ->assertDontSee('hubafrika-logo-nav--next', false)
            ->assertDontSee('href="/application"', false)
            ->assertDontSee('x-data="{ activeTab', false)
            ->assertSee('.gjs-layer.gjs-selected');
    }

    public function test_visual_builder_rejects_an_invalid_slug_without_changing_the_page(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $page = CmsPage::query()->create([
            'title' => 'Original title',
            'slug' => 'original',
            'content' => json_encode([
                'version' => 2,
                'published' => false,
                'project_data' => [],
                'html' => '',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);

        $this->putJson(route('admin.cms.pages.builder.update', $page), [
            'title' => 'Changed title',
            'slug' => 'invalid slug',
            'published' => true,
            'project_data' => [],
            'html' => '<h1>Changed</h1>',
            'css' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slug');

        $this->assertSame('Original title', $page->refresh()->title);
        $this->assertSame('original', $page->slug);
    }

    public function test_the_visual_builder_offers_library_pictures_without_uploading_them(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $page = CmsPage::query()->create([
            'title' => 'Assets',
            'slug' => 'assets',
            'content' => json_encode([
                'version' => 2,
                'published' => false,
                'project_data' => ['assets' => [], 'pages' => [['frames' => []]]],
                'html' => '<h1>Assets</h1>',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);

        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);
        $brand->addLibraryMedia(UploadedFile::fake()->image('acme-logo.png', 40, 40), 'logo', 'acme');

        $customer = User::factory()->create(['is_admin' => false]);
        $customer->addLibraryMedia(UploadedFile::fake()->image('customer-avatar.png', 20, 20), 'avatar', 'user-'.$customer->id);

        // The page can place a picture that already exists in the library
        // instead of uploading a second copy of it, even when project_data already exists.
        $this->get(route('admin.cms.pages.builder', $page))
            ->assertOk()
            ->assertSee('acme-logo.png', false)
            ->assertDontSee('customer-avatar.png', false)
            ->assertSee('open-media-library', false)
            ->assertSee('hubafrika-media-modal', false)
            ->assertSee('editor.AssetManager.add(initialLibraryAssets)', false);
    }

    public function test_visual_builder_media_library_endpoint_filters_and_excludes_personal_avatars(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);
        $brand->addLibraryMedia(UploadedFile::fake()->image('brand-banner.png', 40, 40), 'logo', 'acme');

        $category = Category::query()->create(['name' => 'Inverters', 'slug' => 'inverters']);
        $category->addLibraryMedia(UploadedFile::fake()->image('inverter-cat.png', 40, 40), 'banner', 'inverters');

        $customer = User::factory()->create(['is_admin' => false]);
        $customer->addLibraryMedia(UploadedFile::fake()->image('private-avatar.png', 20, 20), 'avatar', 'user-'.$customer->id);

        $this->getJson(route('admin.cms.media-library'))
            ->assertOk()
            ->assertJsonFragment(['original_name' => 'brand-banner.png', 'library' => 'brands'])
            ->assertJsonFragment(['original_name' => 'inverter-cat.png', 'library' => 'categories'])
            ->assertJsonMissing(['original_name' => 'private-avatar.png']);

        $this->getJson(route('admin.cms.media-library', ['library' => 'brands', 'search' => 'brand']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.original_name', 'brand-banner.png');
    }

    public function test_builder_media_upload_accepts_single_and_multi_file_inputs(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $page = CmsPage::query()->create([
            'title' => 'Landing',
            'slug' => 'landing',
            'content' => '',
        ]);

        $this->post(route('admin.media.upload'), [
            'file' => UploadedFile::fake()->image('hero-single.png', 100, 60),
            'owner_type' => 'page',
            'owner_id' => $page->id,
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.0.original_name', 'hero-single.png')
            ->assertJsonPath('data.0.library', 'pages');

        $this->post(route('admin.media.upload'), [
            'files' => [UploadedFile::fake()->image('gallery-multi.png', 100, 60)],
            'owner_type' => 'page',
            'owner_id' => $page->id,
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.0.original_name', 'gallery-multi.png')
            ->assertJsonPath('data.0.library', 'pages');

        $this->assertSame(2, $page->refresh()->getMedia('blocks')->count());
    }

    public function test_visual_page_builder_provides_categories_and_brands_for_product_block_traits(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $category = Category::query()->create(['name' => 'Solar Inverters', 'slug' => 'solar-inverters']);
        $brand = Brand::query()->create(['name' => 'Victron Master', 'slug' => 'victron-master']);

        $page = CmsPage::query()->create([
            'title' => 'Builder Traits Test',
            'slug' => 'builder-traits-test',
            'content' => '',
        ]);

        $this->get(route('admin.cms.pages.builder', $page))
            ->assertOk()
            ->assertViewHas('categories')
            ->assertViewHas('brands')
            ->assertSee('Solar Inverters')
            ->assertSee('Victron Master')
            ->assertSee('data-category')
            ->assertSee('data-brand')
            ->assertSee('Newest products');
    }

    public function test_visual_page_builder_is_admin_only(): void
    {
        $page = CmsPage::query()->create([
            'title' => 'Private',
            'slug' => 'private',
            'content' => '',
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.cms.pages.builder', $page))
            ->assertForbidden();
    }

    public function test_a_page_cannot_claim_a_slug_the_store_already_serves(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsPages::class)
            ->set('title', 'Shop')
            ->call('savePage')
            ->assertHasErrors('slug');

        $this->assertDatabaseMissing('cms_pages', ['slug' => 'shop']);
    }
}
