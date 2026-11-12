@php
    // Prices drop the cents when the amount is whole (the reference design
    // reads "TZS 320,000" rather than "TZS 320,000.00").
    $money = fn (float $amount): string => $currency.' '.number_format($amount, fmod($amount, 1.0) > 0 ? 2 : 0);
@endphp

<div class="shop-page">
    <header class="shop-head">
        <p class="shop-sub">
            {{ $products->total() }} {{ Str::plural('product', $products->total()) }} available · Prices in {{ $currency }}
        </p>

        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search products..."
               aria-label="Search products" class="shop-search">
    </header>

    <x-flash-toast />

    @error('cart')
        <div role="alert" class="shop-alert">{{ $message }}</div>
    @enderror

    <div class="shop-layout">
        <aside class="shop-side" aria-label="Product filters">
            @if ($categories->isNotEmpty())
                <section class="shop-filter" wire:key="shop-group-category"
                         x-data="{ open: window.matchMedia('(min-width: 1024px)').matches }">
                    <h2 class="shop-filter-heading">
                        <button type="button" class="shop-filter-toggle" @click="open = !open"
                                :aria-expanded="open ? 'true' : 'false'" aria-controls="shop-filters-category">
                            <span>Category</span>
                            <span class="shop-filter-chevron" :class="{ 'is-open': open }" aria-hidden="true">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6" />
                                </svg>
                            </span>
                        </button>
                    </h2>
                    <div id="shop-filters-category" class="shop-filter-body" x-show="open" x-cloak>
                        <ul class="shop-filter-list">
                            <li>
                                <button type="button" wire:click="filterByCategory(null)"
                                        @class(['shop-filter-link', 'is-active' => $categoryId === null])>
                                    <span>All categories</span>
                                </button>
                            </li>
                            @foreach ($categories as $category)
                                <li wire:key="shop-category-{{ $category->id }}">
                                    <button type="button" wire:click="filterByCategory({{ $category->id }})"
                                            @class([
                                                'shop-filter-link',
                                                'is-child' => $category->parent_id !== null,
                                                'is-active' => $categoryId === $category->id,
                                            ])>
                                        <span>{{ $category->name }}</span>
                                        <span class="shop-filter-count">{{ $category->products_count }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            @if ($brands->isNotEmpty())
                <section class="shop-filter" wire:key="shop-group-brand"
                         x-data="{ open: window.matchMedia('(min-width: 1024px)').matches }">
                    <h2 class="shop-filter-heading">
                        <button type="button" class="shop-filter-toggle" @click="open = !open"
                                :aria-expanded="open ? 'true' : 'false'" aria-controls="shop-filters-brand">
                            <span>Brand</span>
                            <span class="shop-filter-chevron" :class="{ 'is-open': open }" aria-hidden="true">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6" />
                                </svg>
                            </span>
                        </button>
                    </h2>
                    <div id="shop-filters-brand" class="shop-filter-body" x-show="open" x-cloak>
                        <ul class="shop-filter-list">
                            <li>
                                <button type="button" wire:click="filterByBrand(null)"
                                        @class(['shop-filter-link', 'is-active' => $brandId === null])>
                                    <span>All brands</span>
                                </button>
                            </li>
                            @foreach ($brands as $brand)
                                <li wire:key="shop-brand-{{ $brand->id }}">
                                    <button type="button" wire:click="filterByBrand({{ $brand->id }})"
                                            @class(['shop-filter-link', 'is-active' => $brandId === $brand->id])>
                                        <span>{{ $brand->name }}</span>
                                        <span class="shop-filter-count">{{ $brand->products_count }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            <section class="shop-filter" wire:key="shop-group-price"
                     x-data="{ open: window.matchMedia('(min-width: 1024px)').matches }">
                <h2 class="shop-filter-heading">
                    <button type="button" class="shop-filter-toggle" @click="open = !open"
                            :aria-expanded="open ? 'true' : 'false'" aria-controls="shop-filters-price">
                        <span>Price range</span>
                        <span class="shop-filter-chevron" :class="{ 'is-open': open }" aria-hidden="true">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6" />
                            </svg>
                        </span>
                    </button>
                </h2>
                <div id="shop-filters-price" class="shop-filter-body" x-show="open" x-cloak>
                    <div class="shop-price-range">
                        <input type="number" min="0" step="1" inputmode="numeric" placeholder="Min"
                               aria-label="Minimum price" wire:model.live.debounce.500ms="minPrice" class="shop-price-input">
                        <span class="shop-price-dash" aria-hidden="true">–</span>
                        <input type="number" min="0" step="1" inputmode="numeric" placeholder="Max"
                               aria-label="Maximum price" wire:model.live.debounce.500ms="maxPrice" class="shop-price-input">
                    </div>
                    <p class="shop-price-note">Prices in {{ $currency }}</p>
                </div>
            </section>

            @if ($categoryId !== null || $brandId !== null || filled($minPrice) || filled($maxPrice))
                <button type="button" wire:click="clearFilters" class="shop-reset">Clear filters</button>
            @endif
        </aside>

        <div class="shop-main">
            <div class="shop-grid">
                @forelse ($products as $product)
                    @php
                        $listPrice = (float) $product->unit_price;
                        $salePrice = $product->discountedUnitPrice();
                    @endphp

                    <article wire:key="shop-product-{{ $product->id }}" class="shop-card">
                        <div class="shop-media">
                            @if ($product->thumbnailUrl())
                                <img src="{{ $product->thumbnailUrl() }}" alt="{{ $product->name }}" loading="lazy">
                            @else
                                <span class="shop-media-empty">No image</span>
                            @endif
                        </div>

                        <div class="shop-body">
                            <p class="shop-cat">{{ $product->category?->name ?? $product->brand?->name ?? 'Uncategorised' }}</p>
                            <h2 class="shop-name">{{ $product->name }}</h2>
                            <p class="shop-price">
                                @if ($salePrice < $listPrice)
                                    <span class="shop-price-was">{{ $money($listPrice) }}</span>
                                @endif
                                <span>{{ $money($salePrice) }}</span>
                            </p>

                            @if ($product->variant_product)
                                <label for="shop-option-{{ $product->id }}" class="sr-only">Option for {{ $product->name }}</label>
                                <select id="shop-option-{{ $product->id }}" wire:model="selectedStockIds.{{ $product->id }}" class="shop-select">
                                    <option value="">Choose an option</option>
                                    @foreach ($product->stocks as $stock)
                                        <option wire:key="stock-{{ $stock->id }}" value="{{ $stock->id }}">{{ $stock->variant }}{{ $stock->sku ? ' · '.$stock->sku : '' }} — {{ $stock->qty }} available</option>
                                    @endforeach
                                </select>
                            @endif

                            <div class="shop-actions">
                                <button type="button" wire:click="addToCart({{ $product->id }})"
                                        wire:loading.attr="disabled" wire:target="addToCart" class="shop-btn">
                                    Add to cart
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="shop-empty">No available products found.</p>
                @endforelse
            </div>

            <div class="shop-pagination">{{ $products->links() }}</div>
        </div>
    </div>
</div>
