<div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(18rem,0.7fr)]">
    <section class="space-y-5">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Checkout</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Choose how you want to pay. Order prices are in {{ $currency }}.</p>
        </div>

        @error('checkout')
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200">{{ $message }}</div>
        @enderror

        <form wire:submit="placeOrder" class="space-y-5 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            @guest
                <section class="space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Create your account</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Your cart is saved. Create an account to place the order and follow its delivery, or
                            <a href="{{ route('login') }}" class="font-semibold text-blue-700 dark:text-blue-300">sign in</a>
                            - your cart follows you either way.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="checkout-name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Full name *</label>
                            <input id="checkout-name" type="text" wire:model="name" autocomplete="name" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="checkout-email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Email *</label>
                            <input id="checkout-email" type="email" wire:model="email" autocomplete="username" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="checkout-password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Password *</label>
                            <input id="checkout-password" type="password" wire:model="password" autocomplete="new-password" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="checkout-password-confirmation" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Confirm password *</label>
                            <input id="checkout-password-confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                        </div>
                    </div>
                </section>
            @endguest

            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delivery details</h2>
            <div>
                <label for="checkout-address" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Street address *</label>
                <textarea id="checkout-address" wire:model="shippingAddress" rows="3" autocomplete="street-address" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950"></textarea>
                @error('shippingAddress') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="checkout-country" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Country</label>
                    <input id="checkout-country" type="text" wire:model="shippingCountry" autocomplete="country-name" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                    @error('shippingCountry') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="checkout-state" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">State / province</label>
                    <input id="checkout-state" type="text" wire:model="shippingState" autocomplete="address-level1" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                    @error('shippingState') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="checkout-city" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">City</label>
                    <input id="checkout-city" type="text" wire:model="shippingCity" autocomplete="address-level2" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                    @error('shippingCity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="checkout-postal" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Postal code</label>
                    <input id="checkout-postal" type="text" wire:model="shippingPostalCode" autocomplete="postal-code" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                    @error('shippingPostalCode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="checkout-phone" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Delivery phone</label>
                    <input id="checkout-phone" type="tel" wire:model="shippingPhone" autocomplete="tel" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                    @error('shippingPhone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <fieldset class="space-y-3">
                <legend class="text-lg font-semibold text-gray-900 dark:text-gray-100">Payment method</legend>
                @forelse ($paymentOptions as $method => $label)
                    <label for="payment-{{ $method }}" class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-white p-4 text-sm has-[:checked]:border-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:has-[:checked]:border-white">
                        <input id="payment-{{ $method }}" type="radio" wire:model.live="paymentMethod" value="{{ $method }}" class="mt-1">
                        <span class="min-w-0">
                            <span class="block font-semibold text-gray-800 dark:text-gray-100">{{ $label }}</span>
                            @if ($method === \App\Models\Payment::METHOD_CASH_ON_DELIVERY)
                                <span class="mt-1 block text-gray-600 dark:text-gray-400">Pay the courier when your order arrives.</span>
                            @else
                                <span class="mt-1 block text-gray-600 dark:text-gray-400">Send the money to the Lipa number below, then submit the transaction ID from your SMS.</span>
                            @endif
                        </span>
                    </label>
                @empty
                    <p class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100">No payment method is available right now. Please try again later.</p>
                @endforelse
                @error('paymentMethod') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </fieldset>

            @if ($paymentMethod === \App\Models\Payment::METHOD_VODACOM_LIPA)
                <div class="space-y-4 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">Vodacom Lipa number</p>
                        <p class="font-mono text-2xl font-bold tracking-wider text-amber-900 dark:text-amber-100">{{ $lipaAccount['number'] !== '' ? $lipaAccount['number'] : 'Not configured' }}</p>
                    </div>
                    @if ($lipaAccount['business_name'] !== '')
                        <p class="text-sm text-amber-900 dark:text-amber-100">Account name: <span class="font-semibold">{{ $lipaAccount['business_name'] }}</span></p>
                    @endif
                    <p class="text-sm text-amber-900 dark:text-amber-100">Amount to send: <span class="font-semibold">{{ $currency }} {{ number_format($total, 2) }}</span></p>
                    @if ($lipaAccount['instructions'] !== '')
                        <p class="text-sm text-amber-900/90 dark:text-amber-100/90">{{ $lipaAccount['instructions'] }}</p>
                    @endif
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="payment-phone" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">M-Pesa number used *</label>
                            <input id="payment-phone" type="tel" wire:model="paymentPayerPhone" autocomplete="tel" placeholder="0754000000" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                            @error('paymentPayerPhone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="payment-reference" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Transaction ID *</label>
                            <input id="payment-reference" type="text" wire:model="paymentReference" maxlength="100" placeholder="From your M-Pesa SMS" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                            @error('paymentReference') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-amber-900/80 dark:text-amber-100/80">Your order is reserved once the transaction ID is submitted. We verify it against the Lipa statement before dispatch.</p>
                </div>
            @endif
            <button type="submit" wire:loading.attr="disabled" class="w-full rounded-lg bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700 disabled:opacity-60 dark:bg-white dark:text-gray-900">
                Place order
            </button>
        </form>
    </section>

    <aside class="h-fit space-y-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Order summary</h2>
            <a href="{{ route('cart') }}" wire:navigate class="text-sm font-medium text-blue-700 dark:text-blue-300">Edit cart</a>
        </div>
        @forelse ($cartItems as $cartItem)
            @php($previewItem = $previewItems[$cartItem->id] ?? [])
            <div wire:key="checkout-item-{{ $cartItem->id }}" class="flex justify-between gap-4 border-b border-gray-100 pb-3 text-sm dark:border-gray-800">
                <div class="min-w-0">
                    <p class="font-medium text-gray-800 dark:text-gray-100">{{ $cartItem->product?->name ?? 'Unavailable product' }}</p>
                    <p class="text-gray-500 dark:text-gray-400">Qty {{ $cartItem->quantity }}</p>
                    @if ($previewItem['requiresSelection'] ?? false)
                        <p class="text-amber-700 dark:text-amber-300">Choose a valid product option in your cart.</p>
                    @endif
                </div>
                @unless ($previewItem['unavailable'] ?? false)
                    <p class="whitespace-nowrap text-gray-700 dark:text-gray-200">{{ $currency }} {{ number_format($previewItem['lineTotal'] ?? 0, 2) }}</p>
                @endunless
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">Your cart is empty.</p>
        @endforelse
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
            <span>Subtotal</span>
            <span>{{ $currency }} {{ number_format($subtotal, 2) }}</span>
        </div>
        @if ($discountTotal > 0)
            <div class="flex justify-between text-sm text-green-700 dark:text-green-300">
                <span>Discount</span>
                <span>-{{ $currency }} {{ number_format($discountTotal, 2) }}</span>
            </div>
        @endif
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
            <span>Tax</span>
            <span>{{ $currency }} {{ number_format($taxTotal, 2) }}</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
            <span>Delivery</span>
            <span>{{ $shippingTotal > 0 ? $currency.' '.number_format($shippingTotal, 2) : 'Free' }}</span>
        </div>
        <div class="flex justify-between border-t border-gray-200 pt-3 font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-100">
            <span>Total</span>
            <span>{{ $currency }} {{ number_format($total, 2) }}</span>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">This is an estimate. Product prices, discounts, tax, and stock are verified again when you place the order.</p>
    </aside>
</div>
