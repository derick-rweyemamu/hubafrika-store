@php
    // The builder page is rendered inside its own sandboxed document, so the
    // shop card look is carried by inline styles and scoped stylesheet.
    $money = fn (float $amount): string => $currency.' '.number_format($amount, fmod($amount, 1.0) > 0 ? 2 : 0);
    $shopUrl = route('shop');
    $displayLimit = $limit ?? $products->count();
    $columns = $cols ?? \App\Support\Storefront\ProductGrid::columnsForLimit((int) $displayLimit);
@endphp

<style>
    .hubafrika-product-grid {
        display: grid;
        gap: 22px;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        background: transparent !important;
    }
    @media (min-width: 640px) {
        .hubafrika-product-grid {
            grid-template-columns: repeat({{ min($columns, 2) }}, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .hubafrika-product-grid--cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)) !important; }
        .hubafrika-product-grid--cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        .hubafrika-product-grid--cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
        .hubafrika-product-grid--cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
        .hubafrika-product-grid--cols-5 { grid-template-columns: repeat(5, minmax(0, 1fr)) !important; }
    }
    .pc {
        position: relative;
        display: flex;
        flex-direction: column;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    .pc .pi {
        background: transparent !important;
        aspect-ratio: 1 / 1;
        display: grid;
        place-items: center;
        border: 1px solid var(--bd, #e3e3e3);
        overflow: hidden;
        text-decoration: none;
        color: inherit;
    }
    .pc .pi img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 12px;
        transition: transform .3s ease;
    }
    .pc .pi:hover img {
        transform: scale(1.04);
    }
    .pc .pi-empty {
        display: grid;
        place-items: center;
        height: 100%;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--mut, #777);
    }
    .pc .sale {
        position: absolute;
        top: 10px;
        left: 10px;
        background: var(--ac, var(--storefront-primary, #307F3E));
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 10px;
        text-transform: uppercase;
        letter-spacing: .08em;
        z-index: 2;
        line-height: 1.4;
    }
    .pc .br {
        font-size: 12px;
        color: var(--mut, #777);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-top: 12px;
    }
    .pc h4 {
        margin: 4px 0 2px;
        font-size: 15px;
        font-weight: 500;
        line-height: 1.35;
    }
    .pc h4 a {
        color: inherit;
        text-decoration: none;
    }
    .pc h4 a:hover {
        color: var(--ac, var(--storefront-primary, #307F3E));
    }
    .pc .pr {
        margin: 4px 0 12px;
        font-weight: 600;
        font-size: 15px;
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 6px;
    }
    .pc .pr s {
        color: var(--mut, #777);
        font-weight: 400;
        margin-right: 6px;
        font-size: 13.5px;
    }
    .btn {
        display: inline-block;
        background: var(--ac, var(--storefront-primary, #307F3E));
        color: #fff;
        border: 1px solid var(--ac, var(--storefront-primary, #307F3E));
        padding: 11px 26px;
        font: 600 13px inherit;
        font-family: inherit;
        text-transform: uppercase;
        letter-spacing: .08em;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        box-sizing: border-box;
    }
    .btn:hover {
        background: #25632f;
        border-color: #25632f;
        color: #fff;
    }
    .btn.o {
        background: none;
        color: var(--ac, var(--storefront-primary, #307F3E));
    }
    .btn.o:hover {
        background: var(--ac, var(--storefront-primary, #307F3E));
        color: #fff;
    }
    .pc .btn {
        width: 100%;
        margin-top: auto;
    }
</style>

<div class="hubafrika-product-grid hubafrika-product-grid--cols-{{ $columns }} grid" data-cols="{{ $columns }}" style="background:transparent">
    @forelse ($products as $product)
        @php
            $listPrice = (float) $product->unit_price;
            $salePrice = $product->discountedUnitPrice();
            $brandOrCategory = $product->brand?->name ?? $product->category?->name ?? 'Hub Afrika';
        @endphp

        <div class="pc" data-cms-product="{{ $product->id }}">
            @if ($salePrice < $listPrice)
                <span class="sale">Sale</span>
            @endif

            <a href="{{ $shopUrl }}" class="pi">
                @if ($product->thumbnailUrl())
                    <img src="{{ $product->thumbnailUrl() }}" alt="{{ $product->name }}" loading="lazy">
                @else
                    <span class="pi-empty">No image</span>
                @endif
            </a>

            <div class="br">{{ $brandOrCategory }}</div>
            <h4><a href="{{ $shopUrl }}">{{ $product->name }}</a></h4>

            <div class="pr">
                @if ($salePrice < $listPrice)
                    <s>{{ $money($listPrice) }}</s>
                @endif
                <span>{{ $money($salePrice) }}</span>
            </div>

            <a href="{{ route('cart.add', $product) }}" class="btn o">Add to cart</a>
        </div>
    @empty
        <p style="grid-column:1 / -1;margin:0;padding:60px 20px;border:1px dashed #d4d4d8;color:#71717a;font-size:14px;text-align:center">No published products are available yet.</p>
    @endforelse
</div>
