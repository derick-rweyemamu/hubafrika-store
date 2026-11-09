@php
    // The builder page is rendered inside its own sandboxed document, so the
    // shop card look is carried by inline styles instead of the app stylesheet.
    $money = fn (float $amount): string => $currency.' '.number_format($amount, fmod($amount, 1.0) > 0 ? 2 : 0);
    $shopUrl = route('shop');
@endphp

{{-- The primary colour comes from the storefront theme, the same one the shop
     prints on its buttons. --}}
<style>
    .hubafrika-products__cta:hover,
    .hubafrika-products__cta:focus-visible {
        background: var(--storefront-primary, #b45309);
        color: #fff;
    }
</style>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:30px 20px">
    @forelse ($products as $product)
        @php
            $listPrice = (float) $product->unit_price;
            $salePrice = $product->discountedUnitPrice();
            $categoryLabel = $product->category?->name ?? $product->brand?->name ?? 'Uncategorised';
        @endphp

        <article data-cms-product="{{ $product->id }}" style="display:flex;min-width:0;flex-direction:column">
            <a href="{{ $shopUrl }}" style="display:block;overflow:hidden;aspect-ratio:11 / 10;background:#f4f4f5;border:1px solid #e4e4e7;color:inherit;text-decoration:none">
                @if ($product->thumbnailUrl())
                    <img src="{{ $product->thumbnailUrl() }}" alt="{{ $product->name }}" loading="lazy" style="width:100%;height:100%;padding:12px;object-fit:contain">
                @else
                    <span style="display:grid;place-items:center;height:100%;color:#a1a1aa;font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase">No image</span>
                @endif
            </a>

            <div style="display:flex;flex:1 1 auto;flex-direction:column;padding-top:12px">
                <p style="margin:0;color:#71717a;font-size:11.5px;font-weight:500;letter-spacing:.09em;line-height:1.4;text-transform:uppercase">{{ $categoryLabel }}</p>
                <h3 style="margin:4px 0 0;color:#18181b;font-size:13px;font-weight:700;line-height:1.35">{{ $product->name }}</h3>
                <p style="display:flex;flex-wrap:wrap;align-items:baseline;gap:8px;margin:6px 0 0;color:#18181b;font-size:15px;font-weight:700;line-height:1.3">
                    @if ($salePrice < $listPrice)
                        <span style="color:#71717a;font-size:13.5px;font-weight:500;text-decoration:line-through">{{ $money($listPrice) }}</span>
                    @endif
                    <span>{{ $money($salePrice) }}</span>
                </p>

                <div style="margin-top:auto;padding-top:16px">
                    <a href="{{ route('cart.add', $product) }}" class="hubafrika-products__cta" style="display:flex;align-items:center;justify-content:center;min-height:42px;padding:10px 12px;border:1px solid var(--storefront-primary, #b45309);color:var(--storefront-primary, #b45309);font-size:12px;font-weight:700;letter-spacing:.08em;line-height:1.2;text-align:center;text-transform:uppercase;text-decoration:none">Add to cart</a>
                </div>
            </div>
        </article>
    @empty
        <p style="grid-column:1 / -1;margin:0;padding:60px 20px;border:1px dashed #d4d4d8;color:#71717a;font-size:14px;text-align:center">No published products are available yet.</p>
    @endforelse
</div>
