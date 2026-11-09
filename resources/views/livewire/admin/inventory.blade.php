@php
    $canManage = auth()->user()?->can('inventory.manage') ?? false;
@endphp

<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Inventory</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Adjust stock levels with a reason, and review every change in the movement log.</p>
    </div>

    <x-flash-toast />

    <div class="flex flex-col gap-3 sm:flex-row">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name or barcode..." class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 sm:max-w-sm">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            Show
            <select wire:model.live="perPage" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
            products
        </label>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/70">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Product</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Options</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">In stock</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($products as $product)
                        <tr wire:key="inventory-{{ $product->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $product->barcode ?: $product->slug }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $unitNames[$product->unit] ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $product->stocks_count ?: '—' }}</td>
                            <td class="px-5 py-4 text-sm">
                                @if ($product->current_stock <= 0)
                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950 dark:text-red-200">{{ $product->current_stock }} · out of stock</span>
                                @elseif ($product->current_stock <= 5)
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950 dark:text-amber-200">{{ $product->current_stock }} · low</span>
                                @else
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $product->current_stock }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                @if ($canManage)
                                    <button type="button" wire:click="adjust({{ $product->id }})" title="Adjust stock" aria-label="Adjust stock for {{ $product->name }}" class="admin-primary-soft-bg admin-primary-text inline-flex h-8 w-8 items-center justify-center rounded-md transition hover:brightness-95">
                                        <i class="fas fa-sliders text-xs" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No products match your search.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $products->links() }}</div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent movements</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The last 20 stock changes, including order processing and cancellations.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/70">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">When</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Product</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Change</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Balance</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Reason</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($movements as $movement)
                        <tr wire:key="movement-{{ $movement->id }}">
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->created_at?->format('M j, Y g:i A') }}</td>
                            <td class="px-5 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ $movement->product?->name ?? 'Deleted product' }}@if ($movement->productStock) <span class="text-gray-500 dark:text-gray-400">({{ $movement->productStock->variant }})</span>@endif
                                @if ($movement->note)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $movement->note }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm font-semibold {{ $movement->quantity > 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->balance }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->reasonLabel() }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->user?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No stock changes recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($canManage && $isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="inventory-modal-title">
            <div class="fixed inset-0 bg-gray-950/50" wire:click="$set('isOpen', false)"></div>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <form wire:submit="saveAdjustment" class="w-full max-w-xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <h3 id="inventory-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Adjust stock</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $productName }}</p>
                    </div>
                    <div class="space-y-5 p-5">
                        @if ($variants !== [])
                            <div>
                                <label for="inventory-variant" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Option</label>
                                <select id="inventory-variant" wire:model="stockId" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                    <option value="0">Whole product</option>
                                    @foreach ($variants as $variant)
                                        <option wire:key="variant-option-{{ $variant['id'] }}" value="{{ $variant['id'] }}">{{ $variant['variant'] }} — {{ $variant['qty'] }} in stock</option>
                                    @endforeach
                                </select>
                                @error('stockId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div>
                            <label for="inventory-quantity" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Change *</label>
                            <input id="inventory-quantity" type="number" step="1" wire:model="quantity" placeholder="e.g. 12 to add, -3 to remove" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use a negative number to remove stock.</p>
                            @error('quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="inventory-reason" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Reason *</label>
                            <select id="inventory-reason" wire:model="reason" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @foreach ($reasons as $value => $label)
                                    <option wire:key="reason-option-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="inventory-note" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Note</label>
                            <input id="inventory-note" type="text" maxlength="255" wire:model="note" placeholder="Optional, for example a supplier or invoice number" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                            @error('note') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-950 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="$set('isOpen', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white">Save adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
