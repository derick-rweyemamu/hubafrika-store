<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\CmsPosts;
use App\Models\CmsCategory;
use App\Models\CmsPost;
use App\Models\User;
use Livewire\Livewire;

class CmsPostsTest extends CatalogTestCase
{
    public function test_admin_can_save_a_draft_post_then_open_its_builder_from_the_post_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $component = Livewire::test(CmsPosts::class)
            ->set('title', 'New collection')
            ->assertSet('slug', 'new-collection')
            ->call('savePost')
            ->assertHasNoErrors();

        $post = CmsPost::query()->where('slug', 'new-collection')->firstOrFail();

        $component->assertRedirect(route('admin.cms.posts'));
        $this->assertSame($admin->id, $post->created_by);
        $this->assertFalse($post->isPublished());
        $this->assertSame(2, json_decode($post->content, true, flags: JSON_THROW_ON_ERROR)['version']);
        Livewire::test(CmsPosts::class)
            ->assertSee('Edit')
            ->assertSee('Open builder');
    }

    public function test_admin_can_save_a_post_as_published(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsPosts::class)
            ->set('title', 'Published announcement')
            ->set('status', 'published')
            ->call('savePost')
            ->assertRedirect(route('admin.cms.posts'))
            ->assertHasNoErrors();

        $post = CmsPost::query()->where('slug', 'published-announcement')->firstOrFail();
        $this->assertTrue($post->isPublished());
    }

    public function test_admin_can_edit_post_details_and_status_without_changing_its_design(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $category = CmsCategory::query()->create(['name' => 'News', 'slug' => 'news']);
        $originalContent = json_encode([
            'version' => 2,
            'published' => true,
            'project_data' => ['pages' => [['frames' => []]]],
            'html' => '<article><h1>Existing post design</h1></article>',
            'css' => 'h1 { color: red; }',
        ], JSON_THROW_ON_ERROR);
        $post = CmsPost::query()->create([
            'title' => 'Original post',
            'slug' => 'original-post',
            'category_id' => null,
            'content' => $originalContent,
        ]);

        Livewire::test(CmsPosts::class)
            ->call('editPost', $post->id)
            ->assertSet('status', 'published')
            ->assertSet('categoryId', null)
            ->set('title', 'Updated post')
            ->assertSet('slug', 'updated-post')
            ->set('categoryId', $category->id)
            ->set('status', 'draft')
            ->call('savePost')
            ->assertRedirect(route('admin.cms.posts'))
            ->assertHasNoErrors();

        $this->assertSame('Updated post', $post->refresh()->title);
        $this->assertSame('updated-post', $post->slug);
        $this->assertSame($category->id, $post->category_id);
        $this->assertFalse($post->isPublished());
        $content = json_decode($post->content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(['pages' => [['frames' => []]]], $content['project_data']);
        $this->assertSame('<article><h1>Existing post design</h1></article>', $content['html']);
        $this->assertSame('h1 { color: red; }', $content['css']);
    }

    public function test_admin_can_create_a_category_and_assign_it_to_a_post(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $component = Livewire::test(CmsPosts::class)
            ->set('categoryName', 'Shop updates')
            ->call('createCategory')
            ->assertHasNoErrors();
        $category = CmsCategory::query()->where('slug', 'shop-updates')->firstOrFail();

        $component->call('editCategory', $category->id)
            ->set('categoryName', 'Shop journal')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertSame('shop-journal', $category->refresh()->slug);

        $component->set('title', 'Spring collection')
            ->set('categoryId', $category->id)
            ->call('savePost')
            ->assertHasNoErrors();

        $post = CmsPost::query()->where('slug', 'spring-collection')->firstOrFail();
        $this->assertSame($category->id, $post->category_id);

        $component->call('deleteCategory', $category->id)
            ->assertHasNoErrors();

        $this->assertNull($post->refresh()->category_id);
    }

    public function test_visual_builder_saves_a_post_and_published_post_is_rendered(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $post = CmsPost::query()->create([
            'title' => 'Announcement',
            'slug' => 'announcement',
            'content' => '',
        ]);

        $this->get(route('admin.cms.posts.builder', $post))
            ->assertOk()
            ->assertSee('Save Post')
            ->assertSee('school-faq-accordion');

        $this->putJson(route('admin.cms.posts.builder.update', $post), [
            'title' => 'Shop announcement',
            'slug' => 'shop-announcement',
            'published' => true,
            'project_data' => ['pages' => [['frames' => []]]],
            'html' => '<article><h1>New arrivals</h1></article>',
            'css' => 'h1 { color: #111827; }',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Post published.')
            ->assertJsonPath('url', route('posts.show', 'shop-announcement'));

        $post->refresh();
        $content = json_decode($post->content, true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('Shop announcement', $post->title);
        $this->assertSame('shop-announcement', $post->slug);
        $this->assertTrue($post->isPublished());
        $this->assertSame('<article><h1>New arrivals</h1></article>', $content['html']);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('New arrivals');
    }

    public function test_unpublished_post_returns_not_found(): void
    {
        $post = CmsPost::query()->create([
            'title' => 'Draft',
            'slug' => 'draft-post',
            'content' => json_encode([
                'version' => 2,
                'published' => false,
                'project_data' => [],
                'html' => '<h1>Not public</h1>',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);

        $this->get(route('posts.show', $post->slug))->assertNotFound();
    }

    public function test_legacy_post_content_remains_available_to_the_builder_and_storefront(): void
    {
        $post = CmsPost::query()->create([
            'title' => 'Legacy story',
            'slug' => 'legacy-story',
            'content' => '<article><h1>Existing content</h1></article>',
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.cms.posts.builder', $post))
            ->assertOk()
            ->assertSee('Existing content');

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('Existing content');
    }

    public function test_post_builder_rejects_a_slug_used_by_another_post(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $post = CmsPost::query()->create([
            'title' => 'First post',
            'slug' => 'first-post',
            'content' => '',
        ]);
        CmsPost::query()->create([
            'title' => 'Existing post',
            'slug' => 'existing-post',
            'content' => '',
        ]);

        $this->putJson(route('admin.cms.posts.builder.update', $post), [
            'title' => 'Changed title',
            'slug' => 'existing-post',
            'published' => false,
            'project_data' => [],
            'html' => '<h1>Changed</h1>',
            'css' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slug');

        $this->assertSame('First post', $post->refresh()->title);
        $this->assertSame('first-post', $post->slug);
    }

    public function test_post_builder_is_admin_only(): void
    {
        $post = CmsPost::query()->create([
            'title' => 'Private post',
            'slug' => 'private-post',
            'content' => '',
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.cms.posts.builder', $post))
            ->assertForbidden();
    }
}
