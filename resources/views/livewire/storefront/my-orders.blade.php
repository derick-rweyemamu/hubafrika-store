<div class="pf-page space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">My orders</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Track your orders and payment status.</p>
    </div>
    <x-account-tabs active="orders" />
    <x-flash-toast />
    @forelse ($orders as $order)
        <article wire:key="my-order-{{ $order->id }}" class="space-y-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $order->order_number }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at?->format('M j, Y · g:i A') }}</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-medium">
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-800 dark:bg-blue-950 dark:text-blue-200">{{ ucfirst($order->status) }}</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700 dark:bg-gray-800 dark:text-gray-200">Payment {{ str_replace('_', ' ', $order->payment_status) }}</span>
                </div>
            </div>
            <div class="space-y-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                @foreach ($order->items as $item)
                    <div wire:key="my-order-item-{{ $item->id }}" class="flex justify-between gap-4 text-sm">
                        <span class="text-gray-600 dark:text-gray-300">{{ $item->product_name }} × {{ $item->quantity }}</span>
                        <span class="whitespace-nowrap text-gray-800 dark:text-gray-100">{{ $order->currency }} {{ number_format((float) $item->line_total, 2) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between border-t border-gray-100 pt-4 font-semibold dark:border-gray-800">
                <span>Total</span>
                <span>{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Payment method: {{ $order->payments->last()?->methodLabel() ?? ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
            @if ($order->payment_method === \App\Models\Payment::METHOD_VODACOM_LIPA && $order->payment_status === 'awaiting_verification')
                <div class="space-y-2 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950">
                    <p class="text-sm text-amber-900 dark:text-amber-100">
                        @if ($lipaAccount['number'] !== '')
                            Send <span class="font-semibold">{{ $order->money($order->total) }}</span>
                            to Lipa number <span class="font-mono font-semibold">{{ $lipaAccount['number'] }}</span>, then submit your transaction ID on the order page.
                        @else
                            Contact us so we can send you the Lipa number to pay <span class="font-semibold">{{ $order->money($order->total) }}</span>.
                        @endif
                    </p>
                    <a href="{{ route('orders.show', $order) }}" wire:navigate class="inline-flex rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900">Open order</a>
                </div>
            @elseif ($order->payment_reference)
                <p class="text-sm text-gray-500 dark:text-gray-400">Transaction ID: <span class="font-mono">{{ $order->payment_reference }}</span></p>
            @endif
            <p class="text-sm text-gray-500 dark:text-gray-400">Delivery to: {{ $order->shipping_address }}{{ $order->shipping_city ? ', '.$order->shipping_city : '' }}{{ $order->shipping_country ? ', '.$order->shipping_country : '' }}</p>
            <a href="{{ route('orders.show', $order) }}" wire:navigate class="inline-flex text-sm font-semibold text-blue-700 hover:underline dark:text-blue-300">View order &amp; receipt &rarr;</a>
        </article>
    @empty
        <div class="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-900">
            <p class="text-gray-600 dark:text-gray-300">You haven't placed an order yet.</p>
            <a href="{{ route('shop') }}" wire:navigate class="mt-4 inline-flex rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Browse the shop</a>
        </div>
    @endforelse
    <div>{{ $orders->links() }}</div>
</div>
