<div class="space-y-6">
    <p class="text-sm text-gray-600 dark:text-gray-400">Prices and stock are rechecked when you place your order.</p>
    <x-flash-toast />
    @if ($cartItems->isEmpty())
        <div class="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-900">
            <p class="text-gray-600 dark:text-gray-300">Your cart is empty.</p>
            <a href="{{ route('shop') }}" wire:navigate class="mt-4 inline-flex rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Browse products</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($cartItems as $cartItem)
                <article wire:key="cart-item-{{ $cartItem->id }}" class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center">
                    @if ($cartItem->product?->thumbnailUrl())
                        <img src="{{ $cartItem->product->thumbnailUrl() }}" alt="" class="h-20 w-20 rounded-lg object-cover">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400 dark:bg-gray-800">No image</div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate font-semibold text-gray-900 dark:text-gray-100">{{ $cartItem->product?->name ?? 'Unavailable product' }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $currency }} {{ number_format((float) $cartItem->price, 2) }} each</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <label for="quantity-{{ $cartItem->id }}" class="text-sm text-gray-600 dark:text-gray-300">Qty</label>
                        <input id="quantity-{{ $cartItem->id }}" type="number" min="1" max="100" value="{{ $cartItem->quantity }}"
                               wire:change="updateQuantity({{ $cartItem->id }}, $event.target.value)"
                               class="w-24 rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                    </div>
                    <p class="whitespace-nowrap font-semibold text-gray-900 dark:text-gray-100">{{ $currency }} {{ number_format((float) $cartItem->price * $cartItem->quantity, 2) }}</p>
                    <button type="button" wire:click="remove({{ $cartItem->id }})" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950">Remove</button>
                    @error("quantity.{$cartItem->id}") <p class="w-full text-sm text-red-600">{{ $message }}</p> @enderror
                </article>
            @endforeach
        </div>
        <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Delivery and final product prices are calculated at checkout.
                @guest You can create your account there. @endguest
            </p>
            <a href="{{ route('checkout') }}" wire:navigate class="rounded-lg bg-gray-900 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900">Continue to checkout</a>
        </div>
    @endif
</div>
