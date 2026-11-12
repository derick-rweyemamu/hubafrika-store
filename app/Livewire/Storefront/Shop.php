<?php

namespace App\Livewire\Storefront;

use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Support\Storefront\CartSession;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Shop extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $categoryId = null;

    public ?int $brandId = null;

    public string $minPrice = '';

    public string $maxPrice = '';

    /** @var array<int, string> */
    public array $selectedStockIds = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatingMaxPrice(): void
    {
        $this->resetPage();
    }

    /**
     * Clicking the selected category again clears the filter.
     */
    public function filterByCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId === $this->categoryId ? null : $categoryId;
        $this->resetPage();
    }

    /**
     * Clicking the selected brand again clears the filter.
     */
    public function filterByBrand(?int $brandId): void
    {
        $this->brandId = $brandId === $this->brandId ? null : $brandId;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('categoryId', 'brandId', 'minPrice', 'maxPrice');
        $this->resetPage();
    }

    /**
     * Guests collect a cart too: it is held by the browser session and is
     * handed to the account they create when they continue to checkout.
     */
    public function addToCart(int $productId): void
    {
        $this->resetValidation('cart');

        $product = Product::query()
            ->whereKey($productId)
            ->where('published', true)
            ->where('approved', true)
            ->firstOrFail();
        $stock = null;

        if ($product->variant_product) {
            $stockId = filter_var($this->selectedStockIds[$productId] ?? null, FILTER_VALIDATE_INT);

            if (! is_int($stockId) || $stockId < 1) {
                $this->addError('cart', "Choose an option for {$product->name} first.");

                return;
            }

            $stock = ProductStock::query()
                ->where('product_id', $product->id)
                ->whereKey($stockId)
                ->firstOrFail();
        }

        $result = CartSession::add($product, $stock);

        if ($result['error'] !== null) {
            $this->addError('cart', $result['error']);

            return;
        }

        $this->dispatch('cart-updated');
        session()->flash('message', "{$product->name} added to your cart.");
    }

    public function render()
    {
        $search = mb_substr($this->search, 0, 100);
        [$minPrice, $maxPrice] = $this->priceRange();
        $published = fn (Builder $query): Builder => $query->where('published', true)->where('approved', true);

        return view('livewire.storefront.shop', [
            'products' => Product::query()
                ->where('published', true)
                ->where('approved', true)
                ->with(['category', 'brand', 'stocks'])
                ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'))
                ->when($this->categoryId !== null, fn (Builder $query) => $query->whereIn('category_id', $this->categoryFamily($this->categoryId)))
                ->when($this->brandId !== null, fn (Builder $query) => $query->where('brand_id', $this->brandId))
                ->priceBetween($minPrice, $maxPrice)
                ->orderBy('name')
                ->paginate(12),
            'currency' => BusinessSetting::query()->where('type', 'currency_code')->value('value') ?? 'USD',
            'categories' => Category::query()
                ->whereHas('products', $published)
                ->withCount(['products' => $published])
                ->orderBy('name')
                ->get(['id', 'name', 'parent_id']),
            'brands' => Brand::query()
                ->whereHas('products', $published)
                ->withCount(['products' => $published])
                ->orderBy('name')
                ->get(['id', 'name']),
        ])->layout('components.layouts.storefront', [
            'title' => 'Shop',
            'navigationArea' => 'store',
            'pageHeader' => [
                'title' => 'Shop',
            ],
        ]);
    }

    /**
     * @return array{0: float|null, 1: float|null}
     */
    private function priceRange(): array
    {
        $min = is_numeric($this->minPrice) ? max(0, (float) $this->minPrice) : null;
        $max = is_numeric($this->maxPrice) ? max(0, (float) $this->maxPrice) : null;

        // Reversed bounds read as the range the shopper meant.
        if ($min !== null && $max !== null && $min > $max) {
            return [$max, $min];
        }

        return [$min, $max];
    }

    /**
     * A parent category also matches the products filed under its children.
     *
     * @return array<int, int>
     */
    private function categoryFamily(int $categoryId): array
    {
        return Category::query()
            ->where(function (Builder $query) use ($categoryId): void {
                $query->whereKey($categoryId)->orWhere('parent_id', $categoryId);
            })
            ->pluck('id')
            ->all();
    }
}
