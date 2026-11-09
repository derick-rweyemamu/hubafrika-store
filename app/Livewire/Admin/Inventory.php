<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Inventory extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $isOpen = false;

    public ?int $productId = null;

    public string $productName = '';

    /** @var array<int, array{id: int, variant: string, qty: int}> */
    public array $variants = [];

    /**
     * Variant row to adjust; "0" changes the product's own current_stock.
     */
    public string $stockId = '0';

    public string $quantity = '';

    public string $reason = StockMovement::REASON_RESTOCK;

    public string $note = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function adjust(int $productId): void
    {
        $this->authorizeAdmin();
        $this->resetValidation();

        $product = Product::query()->with('stocks')->findOrFail($productId);

        $this->productId = $product->id;
        $this->productName = $product->name;
        $this->variants = $product->stocks
            ->map(fn (ProductStock $stock): array => [
                'id' => $stock->id,
                'variant' => $stock->variant,
                'qty' => (int) $stock->qty,
            ])
            ->values()
            ->all();
        $this->stockId = '0';
        $this->quantity = '';
        $this->reason = StockMovement::REASON_RESTOCK;
        $this->note = '';
        $this->isOpen = true;
    }

    public function saveAdjustment(): void
    {
        $this->authorizeAdmin();

        $validated = $this->validate([
            'productId' => ['required', 'integer', 'exists:products,id'],
            'stockId' => ['required', 'integer', 'min:0'],
            'quantity' => ['required', 'integer', 'not_in:0', 'min:-100000', 'max:100000'],
            'reason' => ['required', Rule::in(array_keys(StockMovement::MANUAL_REASONS))],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'quantity.not_in' => 'Enter a quantity above or below zero.',
        ]);

        $failure = DB::transaction(function () use ($validated): ?string {
            $product = Product::query()->lockForUpdate()->find($validated['productId']);

            if ($product === null) {
                return 'That product no longer exists.';
            }

            $stock = null;
            $stockId = (int) $validated['stockId'];

            if ($stockId > 0) {
                $stock = ProductStock::query()
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->find($stockId);

                if ($stock === null) {
                    return 'Choose a valid option for this product.';
                }
            }

            $quantity = (int) $validated['quantity'];
            $current = $stock !== null ? (int) $stock->qty : (int) $product->current_stock;
            $balance = $current + $quantity;

            if ($balance < 0) {
                return $stock !== null
                    ? "Only {$current} in stock for the {$stock->variant} option."
                    : "Only {$current} in stock for {$product->name}.";
            }

            if ($stock !== null) {
                $stock->forceFill(['qty' => $balance])->save();
            } else {
                $product->forceFill(['current_stock' => $balance])->save();
            }

            StockMovement::log($product, $stock, $quantity, $validated['reason'], $validated['note'] ?: null);

            return null;
        });

        if ($failure !== null) {
            $this->addError('quantity', $failure);

            return;
        }

        session()->flash('message', 'Stock updated successfully.');
        $this->isOpen = false;
        $this->reset(['productId', 'productName', 'variants', 'stockId', 'quantity', 'note']);
    }

    public function render()
    {
        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;

        return view('livewire.admin.inventory', [
            'products' => Product::query()
                ->withCount('stocks')
                ->when($this->search !== '', fn (Builder $query) => $query->where(function (Builder $query): void {
                    $search = '%'.mb_substr($this->search, 0, 100).'%';
                    $query->where('name', 'like', $search)->orWhere('barcode', 'like', $search);
                }))
                ->orderBy('name')
                ->paginate($perPage),
            'unitNames' => Unit::query()->pluck('name', 'id'),
            'movements' => StockMovement::query()
                ->with(['product', 'productStock', 'user'])
                ->latest('id')
                ->limit(20)
                ->get(),
            'reasons' => StockMovement::MANUAL_REASONS,
        ])->layout('components.layouts.admin', ['title' => 'Inventory']);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->can('inventory.manage'), 403);
    }
}
