<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\CmsMenus;
use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\User;
use Livewire\Livewire;

class CmsMenusTest extends CatalogTestCase
{
    public function test_admin_can_add_a_published_page_to_the_header_menu(): void
    {
        $page = CmsPage::query()->create([
            'title' => 'Our story',
            'slug' => 'our-story',
            'content' => json_encode([
                'version' => 1,
                'published' => true,
                'blocks' => [],
            ], JSON_THROW_ON_ERROR),
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsMenus::class)
            ->set('label', 'About us')
            ->set('pageId', $page->id)
            ->call('addItem')
            ->assertHasNoErrors();

        $menu = CmsMenu::query()->where('slug', 'header')->firstOrFail();
        $this->assertDatabaseHas('cms_menu_items', [
            'menu_id' => $menu->id,
            'page_id' => $page->id,
            'label' => 'About us',
        ]);
        $this->get(route('pages.show', 'our-story'))
            ->assertOk()
            ->assertSee('About us')
            ->assertSee(route('pages.show', 'our-story'), false);
    }

    public function test_menu_rejects_javascript_custom_urls(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsMenus::class)
            ->set('label', 'Unsafe link')
            ->set('url', 'javascript:alert(1)')
            ->call('addItem')
            ->assertHasErrors('url');

        $this->assertDatabaseCount('cms_menu_items', 0);
    }

    public function test_menu_accepts_hash_symbol_as_custom_url(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsMenus::class)
            ->set('label', 'Services Dropdown')
            ->set('url', '#')
            ->call('addItem')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cms_menu_items', [
            'label' => 'Services Dropdown',
            'url' => '#',
        ]);
    }

    public function test_menu_accepts_hash_anchor_as_custom_url(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsMenus::class)
            ->set('label', 'Contact Us Section')
            ->set('url', '#contact-us')
            ->call('addItem')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cms_menu_items', [
            'label' => 'Contact Us Section',
            'url' => '#contact-us',
        ]);
    }

    public function test_menu_item_modal_accepts_hash_symbol_for_custom_type(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsMenus::class)
            ->call('openCreateMenuItem')
            ->set('item_title', 'Products Dropdown')
            ->set('item_type', 'custom')
            ->set('item_url', '#')
            ->call('saveMenuItem')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cms_menu_items', [
            'title' => 'Products Dropdown',
            'type' => 'custom',
            'url' => '#',
        ]);
    }

    public function test_custom_menu_item_with_hash_is_preserved_in_storefront_menu_tree(): void
    {
        $menu = CmsMenu::query()->create([
            'title' => 'Main Nav',
            'slug' => 'test-nav',
            'location' => 'header',
        ]);

        $item = CmsMenuItem::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Features',
            'label' => 'Features',
            'type' => 'custom',
            'url' => '#features',
            'order' => 1,
        ]);

        $this->assertEquals('#features', $item->storefrontHref());
        $tree = $menu->tree();
        $this->assertCount(1, $tree);
        $this->assertEquals('#features', $tree[0]['href']);
    }

    public function test_admin_can_add_a_custom_link_to_the_footer_menu(): void
    {
        CmsPage::query()->create([
            'title' => 'Contact',
            'slug' => 'contact',
            'content' => json_encode([
                'version' => 1,
                'published' => true,
                'blocks' => [],
            ], JSON_THROW_ON_ERROR),
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsMenus::class)
            ->call('selectLocation', 'footer')
            ->set('label', 'Contact')
            ->set('url', '/contact')
            ->call('addItem')
            ->assertHasNoErrors();

        $this->get(route('pages.show', 'contact'))
            ->assertOk()
            ->assertSee('Contact')
            ->assertSee('href="/contact"', false);
    }

    public function test_admin_can_add_a_published_post_and_nested_menu_item(): void
    {
        $post = CmsPost::query()->create([
            'title' => 'New arrivals',
            'slug' => 'new-arrivals',
            'content' => json_encode([
                'version' => 2,
                'published' => true,
                'project_data' => [],
                'html' => '<article><h1>New arrivals</h1></article>',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $component = Livewire::test(CmsMenus::class)
            ->set('label', 'Stories')
            ->set('url', '/stories')
            ->call('addItem')
            ->assertHasNoErrors();

        $menu = CmsMenu::query()->where('slug', 'header')->firstOrFail();
        $parent = $menu->items()->where('label', 'Stories')->firstOrFail();

        $component->set('label', 'New arrivals')
            ->set('pageId', null)
            ->set('postId', $post->id)
            ->set('parentId', $parent->id)
            ->call('addItem')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cms_menu_items', [
            'menu_id' => $menu->id,
            'post_id' => $post->id,
            'parent_id' => $parent->id,
            'label' => 'New arrivals',
        ]);

        $this->createWebsitePage();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Stories')
            ->assertSee('New arrivals')
            ->assertSee(route('posts.show', 'new-arrivals'), false);
    }

    public function test_admin_can_enable_a_multi_column_mega_menu_for_a_header_item(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $component = Livewire::test(CmsMenus::class)
            ->set('label', 'Catalog')
            ->set('url', '/catalog')
            ->call('addItem')
            ->assertHasNoErrors();
        $menu = CmsMenu::query()->where('slug', 'header')->firstOrFail();
        $catalog = $menu->items()->where('label', 'Catalog')->firstOrFail();

        $component->set('label', 'Women')
            ->set('url', '/women')
            ->set('parentId', $catalog->id)
            ->call('addItem')
            ->assertHasNoErrors();
        $women = $menu->items()->where('label', 'Women')->firstOrFail();

        $component->set('label', 'Dresses')
            ->set('url', '/women/dresses')
            ->set('parentId', $women->id)
            ->call('addItem')
            ->assertHasNoErrors();

        $component->call('editItem', $catalog->id)
            ->set('isMegaMenu', true)
            ->set('megaMenuColumns', 3)
            ->assertSee('Number of columns')
            ->call('saveMenuItem')
            ->assertHasNoErrors();

        $component->call('editItem', $women->id)
            ->assertSee('Mega-menu column')
            ->assertSee('Column 3')
            ->set('megaMenuColumn', 3)
            ->call('saveMenuItem')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cms_menu_items', [
            'id' => $catalog->id,
            'is_mega_menu' => true,
            'mega_menu_columns' => 3,
        ]);
        $this->assertDatabaseHas('cms_menu_items', [
            'id' => $women->id,
            'mega_menu_column' => 3,
        ]);
        $this->createWebsitePage();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Women')
            ->assertSee('Dresses')
            ->assertSee('style="grid-column: 3"', false)
            ->assertSee('lg:grid-cols-3', false);
    }

    public function test_mega_menu_cannot_be_enabled_on_a_nested_item(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $component = Livewire::test(CmsMenus::class)
            ->set('label', 'Catalog')
            ->set('url', '/catalog')
            ->call('addItem');
        $menu = CmsMenu::query()->where('slug', 'header')->firstOrFail();
        $catalog = $menu->items()->where('label', 'Catalog')->firstOrFail();

        $component->set('label', 'Shoes')
            ->set('url', '/shoes')
            ->set('parentId', $catalog->id)
            ->set('isMegaMenu', true)
            ->call('addItem')
            ->assertHasErrors('isMegaMenu');

        $this->assertDatabaseMissing('cms_menu_items', ['label' => 'Shoes']);
    }

    public function test_menu_rejects_a_parent_item_from_another_menu(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $component = Livewire::test(CmsMenus::class)
            ->set('label', 'Header parent')
            ->set('url', '/parent')
            ->call('addItem');
        $parent = CmsMenuItem::query()->where('label', 'Header parent')->firstOrFail();

        $component->call('selectLocation', 'footer')
            ->set('label', 'Invalid child')
            ->set('url', '/child')
            ->set('parentId', $parent->id)
            ->call('addItem')
            ->assertHasErrors('parentId');

        $this->assertDatabaseMissing('cms_menu_items', ['label' => 'Invalid child']);
    }

    public function test_admin_can_edit_menu_items_without_creating_a_cycle(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $component = Livewire::test(CmsMenus::class)
            ->set('label', 'Parent')
            ->set('url', '/parent')
            ->call('addItem');
        $menu = CmsMenu::query()->where('slug', 'header')->firstOrFail();
        $parent = $menu->items()->where('label', 'Parent')->firstOrFail();

        $component->set('label', 'Child')
            ->set('parentId', $parent->id)
            ->set('url', '/child')
            ->call('addItem');
        $child = $menu->items()->where('label', 'Child')->firstOrFail();

        $component->call('editItem', $parent->id)
            ->set('label', 'Updated parent')
            ->set('parentId', $child->id)
            ->call('saveMenuItem')
            ->assertHasErrors('parentId');

        $this->assertSame('Parent', $parent->refresh()->label);
        $this->assertNull($parent->parent_id);

        $component->call('editItem', $child->id)
            ->set('label', 'Updated child')
            ->set('url', '/updated-child')
            ->call('saveMenuItem')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cms_menu_items', [
            'id' => $child->id,
            'label' => 'Updated child',
            'url' => '/updated-child',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_admin_can_reorder_menu_links(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $component = Livewire::test(CmsMenus::class);
        $component->set('label', 'First')->set('url', '/first')->call('addItem');
        $component->set('label', 'Second')->set('url', '/second')->call('addItem');
        $menu = CmsMenu::query()->where('slug', 'header')->firstOrFail();
        $secondItem = $menu->items()->where('label', 'Second')->firstOrFail();

        $component->call('moveItem', $secondItem->id, 'up')
            ->assertHasNoErrors();

        $this->assertSame(
            ['Second', 'First'],
            $menu->items()->orderBy('order')->pluck('label')->all(),
        );
    }

    public function test_menu_manager_is_admin_only(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.cms.menus'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_new_menu(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsMenus::class)
            ->call('openCreateMenuModal')
            ->set('menuName', 'Store Navigation')
            ->set('menuSlug', 'store-nav')
            ->set('menuCssClass', 'bg-white shadow-sm')
            ->call('saveMenuDetails')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cms_menus', [
            'name' => 'Store Navigation',
            'slug' => 'store-nav',
            'css_class' => 'bg-white shadow-sm',
        ]);
    }

    public function test_admin_can_rename_an_existing_menu(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $component = Livewire::test(CmsMenus::class);
        $menu = CmsMenu::query()->where('slug', 'header')->firstOrFail();

        $component->call('selectLocation', 'header')
            ->call('openEditMenuModal')
            ->set('menuName', 'Primary Website Menu')
            ->set('menuSlug', 'header')
            ->call('saveMenuDetails')
            ->assertHasNoErrors();

        $this->assertSame('Primary Website Menu', $menu->refresh()->name);
    }

    public function test_admin_can_organize_items_in_mega_menu_columns_and_move_between_columns(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $component = Livewire::test(CmsMenus::class);
        $menu = CmsMenu::query()->where('slug', 'header')->firstOrFail();
        $mega = $menu->items()->create([
            'label' => 'Products',
            'url' => '/products',
            'order' => 1,
            'is_mega_menu' => true,
            'mega_menu_columns' => 4,
        ]);

        $item = $menu->items()->create([
            'label' => 'Solar Panels',
            'url' => '/solar-panels',
            'parent_id' => $mega->id,
            'mega_menu_column' => 1,
            'order' => 1,
        ]);

        $component->call('selectLocation', 'header')
            ->call('openMegaDesigner', $mega->id)
            ->assertSee('Mega Menu Designer')
            ->assertSee('Solar Panels')
            ->call('setItemColumn', $item->id, 3)
            ->assertHasNoErrors();

        $this->assertSame(3, $item->refresh()->mega_menu_column);

        $component->call('setMegaColumns', $mega->id, 5)
            ->assertHasNoErrors();

        $this->assertSame(5, $mega->refresh()->mega_menu_columns);
    }

    public function test_logged_in_user_sees_store_menu_under_storefront_layout(): void
    {
        $storeMenu = CmsMenu::query()->where('slug', 'store')->first()
            ?? CmsMenu::query()->create(['name' => 'Store Menu', 'slug' => 'store']);

        $storeMenu->items()->create([
            'label' => 'Exclusive Member Deals',
            'url' => '/member-deals',
            'order' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $this->get(route('shop'))
            ->assertOk()
            ->assertSee('Exclusive Member Deals')
            ->assertSee('href="/member-deals"', false);
    }

    public function test_menu_accepts_classes_and_badges_defined_by_admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsMenus::class)
            ->set('label', 'Featured Deals')
            ->set('badge', 'HOT')
            ->set('cssClass', 'text-amber-600 font-bold')
            ->set('url', '/featured-deals')
            ->call('addItem')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cms_menu_items', [
            'label' => 'Featured Deals',
            'badge' => 'HOT',
            'css_class' => 'text-amber-600 font-bold',
            'url' => '/featured-deals',
        ]);

        $this->createWebsitePage();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Featured Deals')
            ->assertSee('HOT')
            ->assertSee('text-amber-600 font-bold', false);
    }

    public function test_admin_can_link_a_menu_item_to_a_page_built_in_code(): void
    {
        CmsPage::query()->create([
            'title' => 'About us',
            'slug' => 'about-us',
            'content' => json_encode(['version' => 1, 'published' => true, 'blocks' => []], JSON_THROW_ON_ERROR),
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsMenus::class)
            ->call('openCreateMenuItem')
            ->set('item_type', 'site')
            ->assertSee('Select Site Page')
            ->assertSee('Shop (/shop)')
            ->set('item_title', 'Shop')
            ->set('item_site_path', '/shop')
            ->call('addItem')
            ->assertHasNoErrors()
            ->call('openCreateMenuItem')
            ->set('item_type', 'page')
            ->assertSee('About us (/about-us)');

        $menu = CmsMenu::query()->where('slug', 'header')->firstOrFail();
        $item = $menu->items()->where('title', 'Shop')->firstOrFail();

        $this->assertSame('site', $item->type);
        $this->assertSame('/shop', $item->url);
        $this->assertSame('/shop', $item->storefrontHref());
    }

    public function test_a_site_page_link_needs_one_of_the_known_pages(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CmsMenus::class)
            ->set('item_title', 'Nowhere')
            ->set('item_type', 'site')
            ->call('addItem')
            ->assertHasErrors('item_site_path');

        Livewire::test(CmsMenus::class)
            ->set('item_title', 'Nowhere')
            ->set('item_type', 'site')
            ->set('item_site_path', '/not-a-store-page')
            ->call('addItem')
            ->assertHasErrors('item_site_path');

        $this->assertDatabaseMissing('cms_menu_items', ['title' => 'Nowhere']);
    }

    public function test_menu_description_and_mega_promo_sanitize_rich_html_and_widen_column(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $longDescription = '<p>'.str_repeat('Solar kits and inverter accessories ', 8).'<strong>Certified</strong><script>alert(1)</script></p>';

        $component = Livewire::test(CmsMenus::class)
            ->call('openCreateMenu')
            ->assertSee('role="toolbar"', false)
            ->set('menu_title', 'Clean Energy Navigation')
            ->set('menu_slug', 'clean-energy-nav')
            ->set('menu_location', 'sidebar')
            ->set('menu_description', $longDescription)
            ->call('saveMenu')
            ->assertHasNoErrors();

        $menu = CmsMenu::query()->where('slug', 'clean-energy-nav')->firstOrFail();

        $this->assertGreaterThan(255, strlen((string) $menu->description));
        $this->assertStringContainsString('<strong>Certified</strong>', (string) $menu->description);
        $this->assertStringNotContainsString('<script', (string) $menu->description);

        $component->call('editMenu', $menu->id)
            ->set('menu_description', '<p><br></p>')
            ->call('saveMenu')
            ->assertHasNoErrors();

        $this->assertNull($menu->refresh()->description);

        $component->call('selectMenu', $menu->id)
            ->set('label', 'Solutions')
            ->set('url', '/solutions')
            ->set('isMegaMenu', true)
            ->call('addItem')
            ->assertHasNoErrors();

        $megaItem = CmsMenuItem::query()
            ->where('menu_id', $menu->id)
            ->where('title', 'Solutions')
            ->firstOrFail();

        $component->call('openMegaBuilder', $megaItem->id)
            ->set('mega_columns.0.type', 'promo')
            ->set('mega_columns.0.title', 'Bundle Offer')
            ->set('mega_columns.0.content', '<h3>Featured Kit</h3><p>Save <strong>15%</strong> today.<script>alert(1)</script></p><p><a href="javascript:evil()">Skip</a></p>')
            ->call('saveMegaBuilder')
            ->assertHasNoErrors();

        $storedJson = json_encode($megaItem->refresh()->mega_menu_data, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString('Featured Kit', $storedJson);
        $this->assertStringContainsString('15%', $storedJson);
        $this->assertStringNotContainsString('<script', $storedJson);
        $this->assertStringNotContainsString('javascript:', $storedJson);

        $component->call('openMegaPreview', $megaItem->id)
            ->assertSee('<h3>Featured Kit</h3>', false)
            ->assertSee('<strong>15%</strong>', false)
            ->assertDontSee('<script>', false);
    }

    /**
     * The header menu renders inside the website layout, so header changes are
     * asserted on the landing page rather than inside the shop, which reads the
     * store menu.
     */
    private function createWebsitePage(): CmsPage
    {
        return CmsPage::query()->create([
            'title' => 'Landing',
            'slug' => 'home',
            'content' => json_encode([
                'version' => 1,
                'published' => true,
                'blocks' => [],
            ], JSON_THROW_ON_ERROR),
        ]);
    }
}
