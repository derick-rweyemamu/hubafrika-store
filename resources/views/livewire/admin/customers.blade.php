@php
    $canManage = auth()->user()?->can('customers.manage') ?? false;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Customers</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage customer profiles and delivery addresses. Suspended accounts keep their records.</p>
        </div>
        @if ($canManage)
            <button type="button" wire:click="create" class="admin-primary-bg rounded-lg px-4 py-2.5 text-sm font-semibold text-white">
                Add customer
            </button>
        @endif
    </div>

    <x-flash-toast />

    <div class="flex flex-col gap-3 sm:flex-row">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search customers..."
               class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 sm:max-w-sm">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            Status
            <select wire:model.live="status" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="all">All customers</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            Show
            <select wire:model.live="perPage" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            customers
        </label>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/70">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Customer</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Addresses</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($customers as $customer)
                        <tr wire:key="customer-{{ $customer->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $customer->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->email ?? 'No email' }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $customer->phone ?: '—' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $customer->addresses_count }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-200' => ! $customer->banned,
                                    'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' => $customer->banned,
                                ])>
                                    {{ $customer->banned ? 'Suspended' : 'Active' }}
                                </span>
                            </td>
                            <td class="space-x-1 whitespace-nowrap px-5 py-4 text-right">
                                @if ($canManage)
                                    <button type="button" wire:click="edit({{ $customer->id }})" title="Edit" aria-label="Edit customer" class="admin-primary-soft-bg admin-primary-text inline-flex h-8 w-8 items-center justify-center rounded-md transition hover:brightness-95">
                                        <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" wire:click="toggleSuspension({{ $customer->id }})"
                                            wire:confirm="{{ $customer->banned ? 'Restore this customer account?' : 'Suspend this customer account? Their data will be retained.' }}"
                                            title="{{ $customer->banned ? 'Restore' : 'Suspend' }}"
                                            aria-label="{{ $customer->banned ? 'Restore customer' : 'Suspend customer' }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md transition {{ $customer->banned ? 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-950 dark:text-green-300 dark:hover:bg-green-900' : 'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900' }}">
                                        <i class="fas {{ $customer->banned ? 'fa-rotate-left' : 'fa-user-slash' }} text-xs" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p class="font-medium text-gray-700 dark:text-gray-200">No customers found</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Adjust your search or add a customer account.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $customers->links() }}</div>
    </div>

    @if ($canManage && $isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="customer-modal-title">
            <div class="fixed inset-0 bg-gray-950/50" wire:click="close"></div>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <h3 id="customer-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $customerId ? 'Edit customer' : 'Add customer' }}</h3>
                        <button type="button" wire:click="close" aria-label="Close" class="rounded-lg px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">✕</button>
                    </div>

                    <form wire:submit="save">
                        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                            <div>
                                <label for="customer-name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Full name *</label>
                                <input id="customer-name" type="text" wire:model="name" autocomplete="name" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="customer-email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Email *</label>
                                <input id="customer-email" type="email" wire:model="email" autocomplete="email" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="customer-phone" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Phone</label>
                                <input id="customer-phone" type="tel" wire:model="phone" autocomplete="tel" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="customer-password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ $customerId ? 'Reset password' : 'Password *' }}</label>
                                <input id="customer-password" type="password" wire:model="password" autocomplete="new-password" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $customerId ? 'Leave blank to keep the current password.' : 'Use at least 8 characters.' }}</p>
                                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="customer-address" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Profile address</label>
                                <input id="customer-address" type="text" wire:model="address" autocomplete="street-address" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="customer-country" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Country</label>
                                <input id="customer-country" type="text" wire:model="country" autocomplete="country-name" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('country') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="customer-state" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">State / province</label>
                                <input id="customer-state" type="text" wire:model="state" autocomplete="address-level1" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('state') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="customer-city" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">City</label>
                                <input id="customer-city" type="text" wire:model="city" autocomplete="address-level2" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="customer-postal-code" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Postal code</label>
                                <input id="customer-postal-code" type="text" wire:model="postalCode" autocomplete="postal-code" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('postalCode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-950 sm:flex-row sm:justify-end">
                            <button type="button" wire:click="close" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                            <button type="submit" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white">Save customer</button>
                        </div>
                    </form>

                    @if ($editingCustomer)
                        <div class="space-y-4 border-t border-gray-200 p-5 dark:border-gray-800">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">Delivery addresses</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage the addresses saved for this customer.</p>
                                </div>
                                <button type="button" wire:click="createAddress" class="admin-primary-text rounded-lg px-3 py-2 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-800">Add address</button>
                            </div>

                            @forelse ($editingCustomer->addresses as $savedAddress)
                                <div wire:key="address-{{ $savedAddress->id }}" class="flex flex-col gap-3 rounded-lg border border-gray-200 p-3 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-800 dark:text-gray-100">{{ $savedAddress->address }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $savedAddress->phone }} {{ $savedAddress->postal_code }}</p>
                                        @if ($savedAddress->set_default)
                                            <span class="text-xs font-medium text-green-700 dark:text-green-300">Default address</span>
                                        @endif
                                    </div>
                                    <div class="flex shrink-0 gap-2">
                                        <button type="button" wire:click="editAddress({{ $savedAddress->id }})" class="admin-primary-text rounded-md px-2 py-1 text-sm font-medium">Edit</button>
                                        <button type="button" wire:click="deleteAddress({{ $savedAddress->id }})" wire:confirm="Delete this saved address?" class="rounded-md px-2 py-1 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-950">Delete</button>
                                    </div>
                                </div>
                            @empty
                                <p class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:bg-gray-950 dark:text-gray-400">No saved delivery addresses.</p>
                            @endforelse

                            @if ($isAddressFormOpen)
                                <form wire:submit="saveAddress" class="space-y-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-950">
                                    <div>
                                        <label for="saved-address-line" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Address *</label>
                                        <input id="saved-address-line" type="text" wire:model="addressLine" autocomplete="street-address" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                                        @error('addressLine') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="saved-address-phone" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Phone</label>
                                            <input id="saved-address-phone" type="tel" wire:model="addressPhone" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                                            @error('addressPhone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label for="saved-address-postal" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Postal code</label>
                                            <input id="saved-address-postal" type="text" wire:model="addressPostalCode" autocomplete="postal-code" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                                            @error('addressPostalCode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                        <input type="checkbox" wire:model="addressIsDefault" class="admin-primary-focus rounded border-gray-300">
                                        Set as default address
                                    </label>
                                    @error('addressIsDefault') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    <div class="flex justify-end gap-3">
                                        <button type="button" wire:click="createAddress" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Clear</button>
                                        <button type="submit" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white">Save address</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
