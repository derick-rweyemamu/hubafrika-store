<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\CmsPages;
use App\Models\Brand;
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
            ->assertSee('Saved before the visual builder')
            ->assertSee('grid-6-col')
            ->assertSee('widget-tabs')
            ->assertSee('school-faq-accordion')
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
                'project_data' => [],
                'html' => '<h1>Assets</h1>',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);

        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);
        $brand->addLibraryMedia(UploadedFile::fake()->image('acme-logo.png', 40, 40), 'logo', 'acme');

        $customer = User::factory()->create(['is_admin' => false]);
        $customer->addLibraryMedia(UploadedFile::fake()->image('customer-avatar.png', 20, 20), 'avatar', 'user-'.$customer->id);

        // The page can place a picture that already exists in the library
        // instead of uploading a second copy of it.
        $this->get(route('admin.cms.pages.builder', $page))
            ->assertOk()
            ->assertSee('acme-logo.png', false)
            ->assertDontSee('customer-avatar.png', false);
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
