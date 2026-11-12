<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSetting;
use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Models\CmsPage;
use App\Models\User;
use Database\Seeders\CmsCategorySeeder;
use Database\Seeders\CmsMenuItemSeeder;
use Database\Seeders\CmsMenuSeeder;
use Database\Seeders\CmsPageSeeder;
use Database\Seeders\CmsPostSeeder;
use Tests\Feature\Admin\CatalogTestCase;

class StorefrontMenuTest extends CatalogTestCase
{
    public function test_guest_sees_the_website_menu_inside_the_shared_storefront_chrome(): void
    {
        $this->createHomePage();
        $this->createMenu('header', 'header', [
            ['label' => 'Guest about us', 'url' => '/pages/about-us'],
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Guest about us')
            ->assertSee('sf-nav-link', false)
            ->assertSee('sf-header', false)
            ->assertSee('sf-cart', false);
    }

    public function test_signed_in_customers_read_the_same_header_menu_on_website_pages(): void
    {
        $this->createHomePage();
        $this->createMenu('header', 'header', [
            ['label' => 'Guest about us', 'url' => '/pages/about-us'],
        ]);
        $this->createMenu('store', 'store', [
            ['label' => 'Customer dashboard', 'url' => '/my-orders'],
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('home'));

        $response->assertOk()
            ->assertSee('Guest about us')
            ->assertDontSee('Customer dashboard')
            ->assertSee('sf-nav-link', false)
            ->assertSee('sf-header', false)
            ->assertSee('sf-cart', false);
    }

    public function test_the_shop_page_reads_the_store_menu(): void
    {
        $this->createMenu('header', 'header', [
            ['label' => 'Website about us', 'url' => '/pages/about-us'],
        ]);
        $this->createMenu('store', 'store', [
            ['label' => 'Member deals', 'url' => '/member-deals'],
        ]);

        $response = $this->get(route('shop'));

        $response->assertOk()
            ->assertSee('Member deals')
            ->assertDontSee('Website about us')
            ->assertSee('sf-nav-link', false)
            ->assertSee('sf-header', false);
    }

    public function test_the_footer_menu_renders_on_every_frontend_layout(): void
    {
        $this->createHomePage();

        $footer = $this->createMenu('footer', 'footer', [
            ['label' => 'Customer care'],
        ]);
        CmsMenuItem::query()->create([
            'menu_id' => $footer->id,
            'label' => 'Delivery policy',
            'url' => '/pages/delivery',
            'parent_id' => $footer->items()->value('id'),
        ]);

        foreach ([route('home'), route('shop')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Customer care')
                ->assertSee('Delivery policy')
                ->assertSee('sf-footer-link', false);
        }
    }

    public function test_a_footer_column_header_without_links_is_still_rendered(): void
    {
        $this->createHomePage();

        $this->createMenu('footer', 'footer', [
            ['label' => 'Services'],
            ['label' => 'Privacy policy', 'url' => '/pages/privacy'],
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Services')
            ->assertSee('sf-footer-heading', false)
            ->assertSee('Privacy policy')
            ->assertSee('sf-footer-legal', false);
    }

    public function test_the_chrome_renders_only_cms_navigation_without_hardcoded_links(): void
    {
        $this->createHomePage();
        $this->createMenu('header', 'header', [
            ['label' => 'CMS home', 'url' => '/'],
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('CMS home')
            ->assertDontSee(route('shop'), false)
            ->assertDontSee('+255 625 003 073')
            ->assertDontSee('sales@hubafrika.net');
    }

    public function test_utility_bar_and_footer_columns_are_rendered_from_the_cms(): void
    {
        $this->createHomePage();

        $this->createMenu('topbar', 'topbar', [
            ['label' => 'Call our sales team', 'url' => '/pages/contact'],
        ]);

        $footer = $this->createMenu('footer', 'footer', [
            ['label' => 'Customer care'],
        ]);
        CmsMenuItem::query()->create([
            'menu_id' => $footer->id,
            'label' => 'Delivery policy',
            'url' => '/pages/delivery',
            'parent_id' => $footer->items()->value('id'),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Call our sales team')
            ->assertSee('sf-topbar-link', false)
            ->assertSee('Customer care')
            ->assertSee('Delivery policy')
            ->assertSee('sf-footer-link', false);
    }

    public function test_footer_columns_contact_links_and_social_buttons_come_from_the_cms(): void
    {
        $this->createHomePage();

        // A header menu keeps the navigation from falling back to the first menu.
        $this->createMenu('header', 'header', [
            ['label' => 'Home', 'url' => '/'],
        ]);

        $footer = $this->createMenu('footer', 'footer', [
            ['label' => 'Get in touch'],
            ['label' => 'Terms of use', 'url' => '/pages/terms'],
        ]);

        $columnId = $footer->items()->where('label', 'Get in touch')->value('id');

        foreach ([
            ['label' => '+255 625 003 073', 'url' => 'tel:+255625003073'],
            ['label' => 'info@hubafrika.net', 'url' => 'mailto:info@hubafrika.net'],
            ['label' => 'Facebook', 'url' => 'https://www.facebook.com/hubafrika'],
        ] as $index => $item) {
            CmsMenuItem::query()->create([
                'menu_id' => $footer->id,
                'label' => $item['label'],
                'url' => $item['url'],
                'parent_id' => $columnId,
                'order' => $index,
            ]);
        }

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Get in touch')
            ->assertSee('tel:+255625003073', false)
            ->assertSee('mailto:info@hubafrika.net', false)
            ->assertSee('sf-footer-icon', false)
            ->assertSee('sf-social-link', false)
            ->assertSee('Terms of use')
            ->assertSee('sf-footer-legal', false)
            ->assertSee('sf-news-input', false)
            ->assertSee('Sign Up')
            ->assertSee('sf-footer-main', false)
            ->assertDontSee('sf-footer-top', false);

        // The social link is promoted to the button strip, not repeated as a column link.
        $this->assertSame(1, substr_count($response->getContent(), 'class="sf-social-link"'));
    }

    public function test_footer_renders_about_us_content_under_the_logo_before_menu_columns(): void
    {
        $this->createHomePage();

        BusinessSetting::query()->create([
            'type' => 'site_motto',
            'value' => 'Clean energy for Tanzania.',
            'lang' => null,
        ]);

        $footer = $this->createMenu('footer', 'footer', [
            ['label' => 'Navigate'],
        ]);
        CmsMenuItem::query()->create([
            'menu_id' => $footer->id,
            'label' => 'Installation',
            'url' => '/pages/installation',
            'parent_id' => $footer->items()->value('id'),
        ]);

        // Falls back to site_motto when footer_about is not set yet.
        $fallbackHtml = $this->get(route('home'))
            ->assertOk()
            ->assertSee('sf-footer-about', false)
            ->assertSee('Clean energy for Tanzania.')
            ->getContent();

        $this->assertLessThan(
            strpos($fallbackHtml, 'sf-footer-columns'),
            strpos($fallbackHtml, 'sf-footer-brand'),
        );
        $this->assertLessThan(
            strpos($fallbackHtml, 'sf-footer-about'),
            strpos($fallbackHtml, 'sf-footer-logo'),
        );

        BusinessSetting::query()->create([
            'type' => 'footer_about',
            'value' => 'Trusted solar equipment and hybrid storage systems across East Africa.',
            'lang' => null,
        ]);

        $customHtml = $this->get(route('home'))
            ->assertOk()
            ->assertSee('Trusted solar equipment and hybrid storage systems across East Africa.')
            ->getContent();

        $footerHtml = substr($customHtml, strpos($customHtml, '<footer'));

        $this->assertStringContainsString(
            'Trusted solar equipment and hybrid storage systems across East Africa.',
            $footerHtml,
        );
        $this->assertStringNotContainsString('Clean energy for Tanzania.', $footerHtml);
    }

    public function test_mega_menu_panel_is_centred_under_its_item_and_capped_to_the_navigation_width(): void
    {
        $this->createHomePage();

        $menu = $this->createMenu('header', 'header', [
            ['label' => 'Services'],
        ]);
        $parentId = $menu->items()->value('id');

        CmsMenuItem::query()->create([
            'menu_id' => $menu->id,
            'label' => 'Installation',
            'url' => '/pages/installation',
            'parent_id' => $parentId,
        ]);

        CmsMenuItem::query()->whereKey($parentId)->update([
            'is_mega_menu' => true,
            'mega_menu_type' => 'columns',
            'mega_menu_columns' => 2,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('is-mega', false)
            ->assertSee('sf-mega', false)
            ->assertSee('x-ref="mega"', false)
            ->assertSee('placeMega()', false)
            ->assertSee('Installation');
    }

    public function test_seeded_footer_menu_renders_term_of_use_privacy_policy_and_cookie_policy_in_bottom_bar(): void
    {
        User::factory()->create(['id' => 1, 'is_admin' => true]);
        $this->seed([
            CmsCategorySeeder::class,
            CmsPageSeeder::class,
            CmsPostSeeder::class,
            CmsMenuSeeder::class,
            CmsMenuItemSeeder::class,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSeeInOrder([
                'class="sf-footer-bottom"',
                'class="sf-footer-copy"',
                'class="sf-footer-legal"',
                '/term-of-use',
                'Term of use',
                '/privacy-policy',
                'Privacy Policy',
                '/cookie-policy',
                'Cookie Policy',
            ], false);

        foreach ([
            '/term-of-use' => 'Term of use',
            '/privacy-policy' => 'Privacy Policy',
            '/cookie-policy' => 'Cookie Policy',
        ] as $url => $title) {
            $this->get($url)
                ->assertOk()
                ->assertSee($title);
        }
    }

    private function createHomePage(): void
    {
        CmsPage::query()->create([
            'title' => 'Welcome',
            'slug' => 'home',
            'content' => json_encode([
                'version' => 1,
                'published' => true,
                'blocks' => [],
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * @param  array<int, array{label: string, url?: string, parent_id?: int|null}>  $items
     */
    private function createMenu(string $slug, string $location, array $items): CmsMenu
    {
        $menu = CmsMenu::query()->create([
            'name' => ucfirst($slug).' menu',
            'title' => ucfirst($slug).' menu',
            'slug' => $slug,
            'location' => $location,
            'status' => 1,
        ]);

        foreach ($items as $index => $item) {
            CmsMenuItem::query()->create([
                'menu_id' => $menu->id,
                'label' => $item['label'],
                'url' => $item['url'] ?? null,
                'parent_id' => $item['parent_id'] ?? null,
                'order' => $index,
            ]);
        }

        return $menu;
    }
}
