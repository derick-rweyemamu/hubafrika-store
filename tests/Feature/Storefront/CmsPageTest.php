<?php

namespace Tests\Feature\Storefront;

use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\CmsPage;
use App\Models\Product;
use App\Support\Storefront\PageBlocks;
use App\Support\Storefront\ProductGrid;
use Tests\Feature\Admin\CatalogTestCase;

class CmsPageTest extends CatalogTestCase
{
    public function test_published_visual_builder_page_renders_in_an_isolated_iframe(): void
    {
        CmsPage::query()->create([
            'title' => 'About',
            'slug' => 'about',
            'content' => json_encode([
                'version' => 2,
                'published' => true,
                'project_data' => [],
                'html' => '<section><h1>Our story</h1><div data-hubafrika-products="true"></div><script>alert(1)</script></section>',
                'css' => 'h1 { color: #111827; }',
            ], JSON_THROW_ON_ERROR),
        ]);

        $this->get(route('pages.show', 'about'))
            ->assertOk()
            ->assertSee('sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox allow-forms"', false)
            ->assertSee('Content-Security-Policy', false)
            ->assertSee('No published products are available yet.')
            ->assertDontSee('data-hubafrika-products', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_published_canvas_page_renders_escaped_block_content(): void
    {
        CmsPage::query()->create([
            'title' => 'About',
            'slug' => 'about',
            'content' => json_encode([
                'version' => 1,
                'published' => true,
                'blocks' => [
                    [
                        'type' => 'text',
                        'text' => '<script>alert(1)</script>',
                        'x' => '4; background-image: url(https://untrusted.invalid/image)',
                        'y' => 4,
                        'width' => 90,
                        'height' => 140,
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ]);

        $this->get(route('pages.show', 'about'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('untrusted.invalid', false);
    }

    public function test_draft_canvas_page_is_not_publicly_accessible(): void
    {
        CmsPage::query()->create([
            'title' => 'Private',
            'slug' => 'private',
            'content' => json_encode([
                'version' => 1,
                'published' => false,
                'blocks' => [],
            ], JSON_THROW_ON_ERROR),
        ]);

        $this->get(route('pages.show', 'private'))->assertNotFound();
    }

    public function test_existing_plain_text_page_content_remains_public_and_escaped(): void
    {
        CmsPage::query()->create([
            'title' => 'Legacy page',
            'slug' => 'legacy-page',
            'content' => '<b>Existing content</b>',
        ]);

        $this->get(route('pages.show', 'legacy-page'))
            ->assertOk()
            ->assertSee('&lt;b&gt;Existing content&lt;/b&gt;', false)
            ->assertDontSee('<b>Existing content</b>', false);
    }

    public function test_pages_answer_at_the_site_root_and_the_old_prefix_redirects(): void
    {
        CmsPage::query()->create([
            'title' => 'About us',
            'slug' => 'about-us',
            'content' => '<p>We sell solar kits.</p>',
        ]);

        $this->assertSame(url('/about-us'), route('pages.show', 'about-us'));

        $this->get('/about-us')
            ->assertOk()
            ->assertSee('We sell solar kits.');

        $this->get('/pages/about-us')
            ->assertStatus(301)
            ->assertRedirect('/about-us');
    }

    public function test_each_featured_products_block_uses_its_own_sort_order_and_limit(): void
    {
        BusinessSetting::query()->create(['type' => 'currency_code', 'value' => 'TZS']);
        $this->product(['name' => 'Alpha Panel', 'slug' => 'alpha-panel', 'unit_price' => 300]);
        $this->product(['name' => 'Beta Battery', 'slug' => 'beta-battery', 'unit_price' => 100]);
        // Highest list price, middle sale price: sorting must use the sale price.
        $this->product(['name' => 'Gamma Cable', 'slug' => 'gamma-cable', 'unit_price' => 400, 'discount' => 200]);

        CmsPage::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => json_encode([
                'version' => 2,
                'published' => true,
                'project_data' => [],
                'html' => '<section><div data-hubafrika-products="true" data-sort="price_asc" data-limit="2"></div>'
                    .'<div data-hubafrika-products="true" data-sort="name_asc" data-limit="3"></div>'
                    .'<div data-hubafrika-products="true" data-sort="price_desc" data-limit="3"></div></section>',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);

        $response = $this->get(route('pages.show', 'home'));

        $response->assertOk()
            ->assertDontSee('data-hubafrika-products', false)
            ->assertSeeInOrder([
                'Beta Battery', 'Gamma Cable',                  // cheapest two, even though Gamma has the highest list price
                'Alpha Panel', 'Beta Battery', 'Gamma Cable',    // alphabetical
                'Alpha Panel', 'Gamma Cable', 'Beta Battery',    // priciest first
            ]);

        $this->assertSame(8, substr_count($response->getContent(), 'data-cms-product='));
    }

    public function test_a_products_block_without_settings_shows_the_eight_newest_products(): void
    {
        foreach (range(1, 10) as $number) {
            $this->product(['name' => "Featured item {$number}", 'slug' => "featured-item-{$number}"]);
        }

        CmsPage::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => json_encode([
                'version' => 2,
                'published' => true,
                'project_data' => [],
                'html' => '<section><div data-hubafrika-products="true"></div></section>',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);

        $response = $this->get(route('pages.show', 'home'));

        $response->assertOk()
            ->assertSeeInOrder(['Featured item 10', 'Featured item 9'])
            ->assertDontSee('Featured item 2');

        $this->assertSame(8, substr_count($response->getContent(), 'data-cms-product='));
    }

    public function test_a_products_block_never_shows_more_than_twenty_four_products(): void
    {
        foreach (range(1, 25) as $number) {
            $this->product(['name' => "Catalogue item {$number}", 'slug' => "catalogue-item-{$number}"]);
        }

        CmsPage::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => json_encode([
                'version' => 2,
                'published' => true,
                'project_data' => [],
                'html' => '<section><div data-hubafrika-products="true" data-limit="99"></div></section>',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);

        $response = $this->get(route('pages.show', 'home'))->assertOk();

        $this->assertSame(24, substr_count($response->getContent(), 'data-cms-product='));
    }

    public function test_a_products_block_can_be_ordered_by_brand_or_category(): void
    {
        $solar = Category::query()->create(['name' => 'Solar', 'slug' => 'solar']);
        $batteries = Category::query()->create(['name' => 'Batteries', 'slug' => 'batteries']);
        $volt = Brand::query()->create(['name' => 'Volt', 'slug' => 'volt']);
        $amp = Brand::query()->create(['name' => 'Amp', 'slug' => 'amp']);

        // The brand and the category orders disagree, so each block is proved.
        $cell = $this->product([
            'name' => 'Alpha cell',
            'slug' => 'alpha-cell',
            'category_id' => $batteries->id,
            'brand_id' => $volt->id,
        ]);
        $panel = $this->product([
            'name' => 'Beta panel',
            'slug' => 'beta-panel',
            'category_id' => $solar->id,
            'brand_id' => $amp->id,
        ]);

        CmsPage::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => json_encode([
                'version' => 2,
                'published' => true,
                'project_data' => [],
                'html' => '<section><div data-hubafrika-products="true" data-sort="brand_asc"></div>'
                    .'<div data-hubafrika-products="true" data-sort="category_asc"></div></section>',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);

        $response = $this->get(route('pages.show', 'home'))->assertOk();

        // The document is printed inside the frame's srcdoc attribute, so the
        // quotes around the ids arrive escaped.
        preg_match_all('/data-cms-product=(?:"|&quot;)(\d+)/', (string) $response->getContent(), $matches);

        $this->assertSame(
            [$panel->id, $cell->id, $cell->id, $panel->id],
            array_map('intval', $matches[1]),
        );
        $response->assertSee('Add to cart')
            ->assertSee(route('cart.add', $cell), false);
    }

    public function test_a_products_block_can_filter_by_category_and_brand(): void
    {
        $solar = Category::query()->create(['name' => 'Solar', 'slug' => 'solar']);
        $batteries = Category::query()->create(['name' => 'Batteries', 'slug' => 'batteries']);
        $volt = Brand::query()->create(['name' => 'Volt', 'slug' => 'volt']);
        $amp = Brand::query()->create(['name' => 'Amp', 'slug' => 'amp']);

        $batteryVolt = $this->product([
            'name' => 'Battery Volt',
            'slug' => 'battery-volt',
            'category_id' => $batteries->id,
            'brand_id' => $volt->id,
        ]);
        $solarVolt = $this->product([
            'name' => 'Solar Volt',
            'slug' => 'solar-volt',
            'category_id' => $solar->id,
            'brand_id' => $volt->id,
        ]);
        $solarAmp = $this->product([
            'name' => 'Solar Amp',
            'slug' => 'solar-amp',
            'category_id' => $solar->id,
            'brand_id' => $amp->id,
        ]);

        CmsPage::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => json_encode([
                'version' => 2,
                'published' => true,
                'project_data' => [],
                'html' => '<section>'
                    .'<div data-hubafrika-products="true" data-category="'.$solar->id.'"></div>'
                    .'<div data-hubafrika-products="true" data-brand="'.$volt->id.'"></div>'
                    .'<div data-hubafrika-products="true" data-category="'.$solar->id.'" data-brand="'.$volt->id.'"></div>'
                    .'</section>',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);

        $response = $this->get(route('pages.show', 'home'))->assertOk();

        preg_match_all('/data-cms-product=(?:"|&quot;)(\d+)/', (string) $response->getContent(), $matches);
        $ids = array_map('intval', $matches[1]);

        // First block: only Solar category ($solarVolt, $solarAmp)
        $this->assertContains($solarVolt->id, array_slice($ids, 0, 2));
        $this->assertContains($solarAmp->id, array_slice($ids, 0, 2));
        $this->assertNotContains($batteryVolt->id, array_slice($ids, 0, 2));

        // Second block: only Volt brand ($batteryVolt, $solarVolt)
        $this->assertContains($batteryVolt->id, array_slice($ids, 2, 2));
        $this->assertContains($solarVolt->id, array_slice($ids, 2, 2));
        $this->assertNotContains($solarAmp->id, array_slice($ids, 2, 2));

        // Third block: Solar category AND Volt brand ($solarVolt only)
        $this->assertSame([$solarVolt->id], array_slice($ids, 4));
    }

    public function test_an_unknown_root_slug_still_returns_not_found(): void
    {
        $this->get('/not-a-page')->assertNotFound();
    }

    public function test_home_route_renders_products_block_on_welcome_view(): void
    {
        $panel = $this->product(['name' => 'Home Featured Panel', 'slug' => 'home-featured-panel']);

        CmsPage::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'content' => json_encode([
                    'version' => 2,
                    'published' => true,
                    'project_data' => [],
                    'html' => '<section><div data-hubafrika-products="true" data-sort="newest" data-limit="4"></div></section>',
                    'css' => '',
                ], JSON_THROW_ON_ERROR),
            ],
        );

        $response = $this->get('/')->assertOk();

        $response->assertDontSee('data-hubafrika-products', false)
            ->assertSee('Home Featured Panel')
            ->assertSee('data-cms-product="'.$panel->id.'"', false)
            ->assertSee('Add to cart');
    }

    public function test_products_grid_columns_adapt_to_limit_and_use_transparent_cards(): void
    {
        $this->assertSame(3, ProductGrid::columnsForLimit(6));
        $this->assertSame(4, ProductGrid::columnsForLimit(8));
        $this->assertSame(3, ProductGrid::columnsForLimit(3));
        $this->assertSame(4, ProductGrid::columnsForLimit(4));
        $this->assertSame(5, ProductGrid::columnsForLimit(10));

        $p = $this->product(['name' => 'Column Test Panel', 'slug' => 'col-test-panel']);

        CmsPage::query()->updateOrCreate(
            ['slug' => 'grid-test'],
            [
                'title' => 'Grid Test',
                'content' => json_encode([
                    'version' => 2,
                    'published' => true,
                    'project_data' => [],
                    'html' => '<section>'
                        .'<div data-hubafrika-products="true" data-limit="6"></div>'
                        .'<div data-hubafrika-products="true" data-limit="8"></div>'
                        .'</section>',
                    'css' => '',
                ], JSON_THROW_ON_ERROR),
            ],
        );

        $response = $this->get(route('pages.show', 'grid-test'))->assertOk();

        $response->assertSee('hubafrika-product-grid--cols-3', false)
            ->assertSee('hubafrika-product-grid--cols-4', false)
            ->assertSee('background: transparent !important;', false);
    }

    public function test_builder_page_loads_interactive_page_blocks_script_palette_variables_and_csp_frames(): void
    {
        CmsPage::query()->updateOrCreate(
            ['slug' => 'interactive-test'],
            [
                'title' => 'Interactive Test',
                'content' => json_encode([
                    'version' => 2,
                    'published' => true,
                    'project_data' => [],
                    'html' => '<section data-hubafrika-tabs="true" data-hubafrika-counter="true" data-target="1250" data-hubafrika-progress="true" data-value="85" data-hubafrika-flip="true"><a data-hubafrika-share="facebook" href="#">Share</a></section>',
                    'css' => '',
                ], JSON_THROW_ON_ERROR),
            ],
        );

        $response = $this->get(route('pages.show', 'interactive-test'))->assertOk();

        $response->assertSee('font-awesome/6.5.2/css/all.min.css', false)
            ->assertSee('--storefront-primary:', false)
            ->assertSee('frame-src https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com', false)
            ->assertSee('initTabs', false)
            ->assertSee('initCounter', false)
            ->assertSee('initProgress', false)
            ->assertSee('initFlipBoxes', false)
            ->assertSee('initShare', false);

        $script = PageBlocks::script('test-nonce', 'https://hubafrika.test/interactive-test');

        $this->assertStringContainsString('nonce="test-nonce"', $script);
        $this->assertStringContainsString('https://hubafrika.test/interactive-test', $script);
        $this->assertStringNotContainsString('x-data', $script);
    }

    public function test_page_header_block_renders_dynamically_across_pages_and_respects_custom_title_and_parent(): void
    {
        $template = '<section class="hubafrika-page-header" data-hubafrika-page-header="true" data-auto-title="true" data-header-parent="">'
            .'<div class="hubafrika-page-header-inner">'
            .'<h1 class="hubafrika-page-header-title" data-page-header-title="true">Placeholder</h1>'
            .'<p class="hubafrika-page-header-crumb"><a href="/">Home</a><span aria-hidden="true"> / </span><span data-page-header-trail="true">Placeholder</span></p>'
            .'</div></section>';

        $aboutHtml = PageBlocks::renderPageHeaders($template, 'About us');
        $this->assertStringContainsString('data-page-header-title="true">About us</h1>', $aboutHtml);
        $this->assertStringContainsString('data-page-header-trail="true">About us</span>', $aboutHtml);

        $promoHtml = PageBlocks::renderPageHeaders($template, '$100 Offer');
        $this->assertStringContainsString('data-page-header-title="true">$100 Offer</h1>', $promoHtml);

        $customTemplate = str_replace('data-auto-title="true"', 'data-auto-title="false"', $template);
        $this->assertSame($customTemplate, PageBlocks::renderPageHeaders($customTemplate, 'About us'));

        $installTemplate = str_replace('data-header-parent=""', 'data-header-parent="Services"', $template);

        CmsPage::query()->updateOrCreate(
            ['slug' => 'header-about-test'],
            [
                'title' => 'About us',
                'content' => json_encode([
                    'version' => 2,
                    'published' => true,
                    'project_data' => [],
                    'html' => $template,
                    'css' => '',
                ], JSON_THROW_ON_ERROR),
            ],
        );

        CmsPage::query()->updateOrCreate(
            ['slug' => 'header-install-test'],
            [
                'title' => 'Installation',
                'content' => json_encode([
                    'version' => 2,
                    'published' => true,
                    'project_data' => [],
                    'html' => $installTemplate,
                    'css' => '',
                ], JSON_THROW_ON_ERROR),
            ],
        );

        $this->get(route('pages.show', 'header-about-test'))
            ->assertOk()
            ->assertSee('hubafrika-page-header', false)
            ->assertSee('About us', false)
            ->assertSee('Inter:wght@300;400', false);

        $this->get(route('pages.show', 'header-install-test'))
            ->assertOk()
            ->assertSee('Installation', false)
            ->assertSee('Services / Installation', false);
    }

    public function test_logo_slider_renders_max_w_7xl_container_configurable_step_and_target_blank_links(): void
    {
        CmsPage::query()->create([
            'title' => 'Partners',
            'slug' => 'partners-slider-test',
            'content' => json_encode([
                'version' => 2,
                'published' => true,
                'project_data' => [],
                'html' => '<section><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="hubafrika-logo-slider" data-hubafrika-logo-slider="true" data-slides-visible="4" data-slide-step="2" data-slide-loop="true"><div class="hubafrika-logo-slider-viewport"><div class="hubafrika-marquee-track hubafrika-logo-slider-track"><div class="hubafrika-logo-slide"><a href="https://partner.example.com" target="_blank" rel="noopener noreferrer" data-logo-slide="true" class="hubafrika-logo-slide-card"><img src="/images/storefront/carousel-renewable.jpg" alt="Partner 1" /></a></div></div></div></div></div></section>',
                'css' => '',
            ], JSON_THROW_ON_ERROR),
        ]);

        $this->get(route('pages.show', 'partners-slider-test'))
            ->assertOk()
            ->assertSee('max-w-7xl', false)
            ->assertSee('data-hubafrika-logo-slider', false)
            ->assertSee('data-slide-step', false)
            ->assertSee('https://partner.example.com', false)
            ->assertSee('initLogoSlider', false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function product(array $attributes): Product
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'accessories'],
            ['name' => 'Accessories'],
        );

        return Product::query()->create($attributes + [
            'category_id' => $category->id,
            'unit_price' => 100,
            'published' => true,
            'approved' => true,
            'current_stock' => 10,
        ]);
    }
}
