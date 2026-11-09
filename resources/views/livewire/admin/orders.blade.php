@php
    $canManage = auth()->user()?->can('orders.manage') ?? false;
@endphp

<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Orders</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage fulfilment, cash on delivery, and Vodacom Lipa verification.</p>
    </div>
    <x-flash-toast />
    <div class="flex flex-col gap-3 sm:flex-row">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search order number or customer..."
               class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 sm:max-w-md">
        <select wire:model.live="status" aria-label="Filter by order status" class="rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
            <option value="all">All statuses</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <select wire:model.live="paymentStatus" aria-label="Filter by payment status" class="rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
            <option value="all">All payments</option>
            <option value="pending">Payment pending</option>
            <option value="awaiting_verification">Awaiting verification</option>
            <option value="paid">Paid</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
        </select>
    </div>
    <div class="space-y-4">
        @forelse ($orders as $order)
            <article wire:key="admin-order-{{ $order->id }}" class="space-y-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-1">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $order->order_number }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $order->customer_name }} · {{ $order->customer_email }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->customer_phone ?: 'No delivery phone' }} · {{ $order->shipping_address }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ collect([$order->shipping_city, $order->shipping_state, $order->shipping_country, $order->shipping_postal_code])->filter()->implode(', ') }}</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at?->format('M j, Y · g:i A') }}</p>
                        <div class="mt-2 flex flex-wrap gap-2 sm:justify-end">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-950 dark:text-blue-200">{{ ucfirst($order->status) }}</span>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">Payment {{ str_replace('_', ' ', $order->payment_status) }}</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            {{ $order->payments->last()?->methodLabel() ?? ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                            @if ($order->payment_reference)
                                · <span class="font-mono">{{ $order->payment_reference }}</span>
                            @endif
                            @if ($order->payments->last()?->payer_phone)
                                · payer {{ $order->payments->last()->payer_phone }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="space-y-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                    @foreach ($order->items as $item)
                        <div wire:key="admin-order-item-{{ $item->id }}" class="flex justify-between gap-4 text-sm">
                            <span class="text-gray-600 dark:text-gray-300">{{ $item->product_name }}{{ $item->variant ? ' · '.$item->variant : '' }} × {{ $item->quantity }}</span>
                            <span class="whitespace-nowrap text-gray-800 dark:text-gray-100">{{ $order->currency }} {{ number_format((float) $item->line_total, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                @if ($canManage)
                    <div class="flex flex-col gap-4 border-t border-gray-100 pt-4 dark:border-gray-800 lg:flex-row lg:items-end lg:justify-between">
                        <div class="flex flex-wrap gap-2">
                            @if ($order->status === 'pending')
                                <button type="button" wire:click="updateStatus({{ $order->id }}, 'processing')" class="rounded-lg bg-blue-700 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-800">Start processing</button>
                                <button type="button" wire:click="updateStatus({{ $order->id }}, 'cancelled')" wire:confirm="Cancel this order? No stock has been deducted yet, so inventory stays as it is." class="rounded-lg border border-red-300 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950">Cancel</button>
                            @elseif ($order->status === 'processing')
                                <button type="button" wire:click="updateStatus({{ $order->id }}, 'shipped')" class="rounded-lg bg-blue-700 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-800">Mark shipped</button>
                                <button type="button" wire:click="updateStatus({{ $order->id }}, 'cancelled')" wire:confirm="Cancel this order? The items will be returned to inventory." class="rounded-lg border border-red-300 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950">Cancel</button>
                            @elseif ($order->status === 'shipped')
                                <button type="button" wire:click="updateStatus({{ $order->id }}, 'delivered')" class="rounded-lg bg-green-700 px-3 py-2 text-sm font-semibold text-white hover:bg-green-800">Mark delivered</button>
                            @endif
                        </div>

                        @if ($order->payment_status === 'awaiting_verification')
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="verifyPayment({{ $order->id }})" class="rounded-lg bg-green-700 px-3 py-2 text-sm font-semibold text-white hover:bg-green-800">Verify Lipa payment</button>
                                <button type="button" wire:click="rejectPayment({{ $order->id }})" wire:confirm="Reject this transaction ID? The customer will have to submit a new one." class="rounded-lg border border-red-300 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950">Reject ID</button>
                            </div>
                        @endif

                        @if ($order->canAcceptPayment())
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <label class="sr-only" for="payment-reference-{{ $order->id }}">Payment reference</label>
                                <input id="payment-reference-{{ $order->id }}" type="text" maxlength="100" wire:model="paymentReferences.{{ $order->id }}" placeholder="Payment reference (optional)"
                                       class="rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                <button type="button" wire:click="markPaymentReceived({{ $order->id }})" class="rounded-lg border border-green-300 px-3 py-2 text-sm font-semibold text-green-800 hover:bg-green-50 dark:border-green-900 dark:text-green-300 dark:hover:bg-green-950">Record payment</button>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="border-t border-gray-100 pt-4 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        View only — changing the status or recording a payment needs the <span class="font-mono">orders.manage</span> permission.
                    </p>
                @endif
                @error("orders.{$order->id}") <p role="alert" class="text-sm text-red-600">{{ $message }}</p> @enderror
                @error("paymentReferences.{$order->id}") <p role="alert" class="text-sm text-red-600">{{ $message }}</p> @enderror
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">No orders found.</div>
        @endforelse
    </div>
    <div>{{ $orders->links() }}</div>
</div>
