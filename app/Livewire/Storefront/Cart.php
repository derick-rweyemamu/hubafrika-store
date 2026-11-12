<?php

namespace App\Livewire\Storefront;

use App\Models\BusinessSetting;
use App\Models\ProductStock;
use App\Support\Storefront\CartSession;
use Livewire\Component;

class Cart extends Component
{
    public function updateQuantity(int $id, int $quantity): void
    {
        $cartItem = CartSession::query()->with('product')->findOrFail($id);
        $product = $cartItem->product;

        if ($product === null || $quantity < max(1, (int) $product->min_qty) || $quantity > 100) {
            $this->addError("quantity.{$id}", 'Enter a quantity within the allowed range.');

            return;
        }

        $availableStock = (int) $product->current_stock;

        if ($product->variant_product) {
            $selection = json_decode((string) $cartItem->variation, true);
            $stockId = is_array($selection)
                ? filter_var($selection['product_stock_id'] ?? null, FILTER_VALIDATE_INT)
                : false;
            $stock = is_int($stockId)
                ? ProductStock::query()
                    ->where('product_id', $product->id)
                    ->whereKey($stockId)
                    ->first()
                : null;

            if ($stock === null) {
                $this->addError("quantity.{$id}", 'This product option is no longer available.');

                return;
            }

            $availableStock = $stock->qty;
        }

        if ($quantity > $availableStock) {
            $this->addError("quantity.{$id}", 'The requested quantity exceeds available stock.');

            return;
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();
        $this->resetValidation("quantity.{$id}");
        $this->dispatch('cart-updated');
    }

    public function remove(int $id): void
    {
        CartSession::query()->findOrFail($id)->delete();
        session()->flash('message', 'Item removed from your cart.');
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.storefront.cart', [
            'cartItems' => CartSession::query()->with('product')->orderBy('id')->get(),
            'currency' => BusinessSetting::query()->where('type', 'currency_code')->value('value') ?? 'USD',
        ])->layout('components.layouts.storefront', [
            'title' => 'Your cart',
            'navigationArea' => 'store',
            'pageHeader' => [
                'title' => 'Your cart',
                'parents' => [
                    ['label' => 'Shop', 'url' => route('shop')],
                ],
            ],
        ]);
    }
}
