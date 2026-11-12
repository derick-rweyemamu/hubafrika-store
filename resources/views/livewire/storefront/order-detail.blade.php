<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('orders.index') }}" wire:navigate class="text-sm font-medium text-blue-700 dark:text-blue-300 print:hidden">&larr; All orders</a>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Placed {{ $order->created_at?->format('M j, Y g:i A') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 print:hidden">
            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-950 dark:text-blue-200">{{ ucfirst($order->status) }}</span>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">Payment {{ $order->paymentStatusLabel() }}</span>
            <button type="button" onclick="window.print()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">Print receipt</button>
        </div>
    </div>

    <x-flash-toast />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(18rem,0.6fr)]">
        <div class="space-y-5">
            <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Progress</h2>
                <ol class="mt-4 space-y-4">
                    @foreach ($timeline as $step)
                        <li class="flex gap-3">
                            <span @class([
                                'mt-1.5 h-3 w-3 shrink-0 rounded-full',
                                'bg-green-600' => $step['state'] === 'done',
                                'bg-blue-600 ring-4 ring-blue-100 dark:ring-blue-950' => $step['state'] === 'current',
                                'bg-red-600' => $step['state'] === 'failed',
                                'bg-gray-300 dark:bg-gray-700' => $step['state'] === 'pending',
                            ])></span>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $step['label'] }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $step['description'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Items</h2>
                <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($order->items as $item)
                        <li class="flex justify-between gap-4 py-3 text-sm">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $item->product_name }}</p>
                                <p class="text-gray-500 dark:text-gray-400">
                                    Qty {{ $item->quantity }} · {{ $order->money($item->unit_price) }} each{{ $item->variant ? ' · '.$item->variant : '' }}
                                </p>
                            </div>
                            <p class="whitespace-nowrap font-medium text-gray-900 dark:text-gray-100">{{ $order->money($item->line_total) }}</p>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-4 space-y-2 border-t border-gray-100 pt-4 text-sm dark:border-gray-800">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <dt>Subtotal</dt>
                        <dd>{{ $order->money($order->subtotal) }}</dd>
                    </div>
                    @if ((float) $order->discount_total > 0)
                        <div class="flex justify-between text-green-700 dark:text-green-300">
                            <dt>Discount</dt>
                            <dd>-{{ $order->money($order->discount_total) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <dt>Tax</dt>
                        <dd>{{ $order->money($order->tax_total) }}</dd>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <dt>Delivery</dt>
                        <dd>{{ (float) $order->shipping_total > 0 ? $order->money($order->shipping_total) : 'Free' }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2 text-base font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-100">
                        <dt>Total</dt>
                        <dd>{{ $order->money($order->total) }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Payment</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Method: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $order->paymentMethodLabel() }}</span></p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Status: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $order->paymentStatusLabel() }}</span></p>
                @if ($order->payment_reference)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Transaction ID: <span class="font-mono text-gray-900 dark:text-gray-100">{{ $order->payment_reference }}</span></p>
                @endif

                @if ($order->payment_method === \App\Models\Payment::METHOD_VODACOM_LIPA && $order->payment_status === 'awaiting_verification')
                    <div class="mt-4 space-y-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950">
                        @if ($lipaAccount['number'] !== '')
                            <p class="text-sm text-amber-900 dark:text-amber-100">
                                Send <span class="font-semibold">{{ $order->money($order->total) }}</span>
                                to Lipa number <span class="font-mono font-semibold">{{ $lipaAccount['number'] }}</span>@if ($lipaAccount['business_name'] !== '') as {{ $lipaAccount['business_name'] }}@endif.
                            </p>
                            @if (\App\Support\RichText::plainText($lipaAccount['instructions']) !== '')
                                <div class="sf-rich-text sf-rich-text--compact text-sm text-amber-900/90 dark:text-amber-100/90">{!! \App\Support\RichText::toHtml($lipaAccount['instructions']) !!}</div>
                            @endif
                        @else
                            <p class="text-sm text-amber-900 dark:text-amber-100">
                                Contact us so we can send you the Lipa number to pay <span class="font-semibold">{{ $order->money($order->total) }}</span>.
                            </p>
                        @endif
                        <form wire:submit="submitReference" class="space-y-2">
                            <label for="order-reference" class="block text-sm font-medium text-amber-900 dark:text-amber-100">Transaction ID from your SMS</label>
                            <input id="order-reference" type="text" maxlength="100" wire:model="reference"
                                   class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                            @error('reference') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                            <button type="submit" wire:loading.attr="disabled" class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 disabled:opacity-60 dark:bg-white dark:text-gray-900">
                                Submit transaction ID
                            </button>
                        </form>
                    </div>
                @elseif ($order->payment_status === 'paid')
                    <p class="mt-3 rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800 dark:bg-green-950 dark:text-green-200">Paid in full. Thank you.</p>
                @elseif ($order->payment_status === 'failed')
                    <p class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-800 dark:bg-red-950 dark:text-red-200">We could not verify this payment. Contact us with your M-Pesa confirmation SMS.</p>
                @endif
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delivery</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $order->customer_name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $order->shipping_address }}</p>
                @if (collect([$order->shipping_city, $order->shipping_state, $order->shipping_country])->filter()->isNotEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ collect([$order->shipping_city, $order->shipping_state, $order->shipping_country])->filter()->implode(', ') }}</p>
                @endif
                @if ($order->shipping_postal_code)
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ $order->shipping_postal_code }}</p>
                @endif
                @if ($order->customer_phone)
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $order->customer_phone }}</p>
                @endif
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $order->customer_email }}</p>
            </section>

            <p class="text-xs text-gray-500 dark:text-gray-400">Receipt for {{ $order->order_number }}. Keep this page for your records.</p>
        </aside>
    </div>
</div>
