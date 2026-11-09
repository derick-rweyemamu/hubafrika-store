<?php

namespace App\Livewire\Storefront;

use App\Support\Storefront\CartSession;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Header cart link with the running item count. The Shop and Cart components
 * fire `cart-updated`, so the figure follows every add, quantity change and
 * removal without a page reload.
 */
class CartButton extends Component
{
    public bool $drawer = false;

    public int $count = 0;

    public function mount(bool $drawer = false): void
    {
        $this->drawer = $drawer;
        $this->count = $this->cartCount();
    }

    #[On('cart-updated')]
    public function refreshCount(): void
    {
        $this->count = $this->cartCount();
    }

    public function render(): View
    {
        return view('livewire.storefront.cart-button');
    }

    private function cartCount(): int
    {
        return (int) CartSession::query()->sum('quantity');
    }
}
