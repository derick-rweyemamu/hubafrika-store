<?php

namespace App\Support\Storefront;

use App\Models\BusinessSetting;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Renders the page builder's "Featured products" placeholder as a grid of
 * shop-style cards. Every placeholder carries its own sort order and limit in
 * its data attributes, so a page can show several differently configured
 * blocks without the filters sidebar.
 */
class ProductGrid
{
    public const DEFAULT_SORT = 'newest';

    public const DEFAULT_LIMIT = 8;

    public const MAX_LIMIT = 24;

    /**
     * Replace every products placeholder in the saved page HTML.
     */
    public static function render(string $html, string $currency): string
    {
        return preg_replace_callback(
            '/<div\b(?=[^>]*\bdata-hubafrika-products\b)[^>]*>[\s\S]*?<\/div>/i',
            fn (array $match): string => self::renderPlaceholder($match[0], $currency),
            $html,
        ) ?? $html;
    }

    /**
     * The published products the block lists, in the requested order.
     *
     * @return Collection<int, Product>
     */
    public static function products(
        string $sort = self::DEFAULT_SORT,
        int $limit = self::DEFAULT_LIMIT,
        string|int|null $category = null,
        string|int|null $brand = null,
    ): Collection {
        $query = Product::query()->published()->with(['category', 'brand']);

        if (! empty($category)) {
            if (is_numeric($category)) {
                $query->where('category_id', (int) $category);
            } else {
                $query->whereHas('category', fn ($q) => $q->where('slug', $category)->orWhere('name', $category));
            }
        }

        if (! empty($brand)) {
            if (is_numeric($brand)) {
                $query->where('brand_id', (int) $brand);
            } else {
                $query->whereHas('brand', fn ($q) => $q->where('slug', $brand)->orWhere('name', $brand));
            }
        }

        match ($sort) {
            'name_asc' => $query->orderBy('name'),
            'brand_asc' => $query->orderByRaw('(select name from brands where brands.id = products.brand_id) asc'),
            'category_asc' => $query->orderByRaw('(select name from categories where categories.id = products.category_id) asc'),
            'price_asc' => $query->orderByPrice('asc'),
            'price_desc' => $query->orderByPrice('desc'),
            default => $query->orderByDesc('created_at'),
        };

        // The id keeps the order stable when the sort column ties.
        return $query
            ->orderByDesc('id')
            ->limit(max(1, min(self::MAX_LIMIT, $limit)))
            ->get();
    }

    public static function currency(): string
    {
        return (string) (BusinessSetting::query()->where('type', 'currency_code')->value('value') ?: 'USD');
    }

    /**
     * Determine optimal column count for large screens based on product limit.
     */
    public static function columnsForLimit(int $limit): int
    {
        return match (true) {
            $limit <= 1 => 1,
            $limit === 2 => 2,
            $limit === 3 || $limit === 6 || $limit === 9 => 3,
            $limit === 5 || $limit === 10 => 5,
            default => 4,
        };
    }

    private static function renderPlaceholder(string $tag, string $currency): string
    {
        $sort = self::attribute($tag, 'data-sort') ?? self::DEFAULT_SORT;
        $limit = self::attribute($tag, 'data-limit');
        $category = self::attribute($tag, 'data-category');
        $brand = self::attribute($tag, 'data-brand');
        $limitCount = is_numeric($limit) ? (int) $limit : self::DEFAULT_LIMIT;

        return view('livewire.storefront.cms-products', [
            'products' => self::products(
                $sort,
                $limitCount,
                $category,
                $brand,
            ),
            'currency' => $currency,
            'limit' => $limitCount,
            'cols' => self::columnsForLimit($limitCount),
        ])->render();
    }

    /**
     * Read a data attribute from the placeholder's opening tag. The builder
     * writes double quotes; single quotes and bare values are accepted too.
     */
    private static function attribute(string $tag, string $name): ?string
    {
        $pattern = '/\b'.preg_quote($name, '/').'\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))/i';

        if (preg_match($pattern, $tag, $match) !== 1) {
            return null;
        }

        foreach (array_slice($match, 1) as $value) {
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
