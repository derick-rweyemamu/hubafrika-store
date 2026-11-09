<?php

namespace Tests\Feature\Storefront;

use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\CmsPage;
use App\Models\Product;
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
            ->assertSee('sandbox="allow-scripts"', false)
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

    public function test_an_unknown_root_slug_still_returns_not_found(): void
    {
        $this->get('/not-a-page')->assertNotFound();
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
