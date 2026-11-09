@php
    $canManage = auth()->user()?->can('users.manage') ?? false;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Users</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage user accounts and their access. Administrator accounts are protected and never appear in this list.</p>
        </div>
        @if ($canManage)
            <button type="button" wire:click="create" class="admin-primary-bg rounded-lg px-4 py-2.5 text-sm font-semibold text-white">
                Add user
            </button>
        @endif
    </div>

    <x-flash-toast />

    <div class="flex flex-col gap-3 sm:flex-row">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search users..."
               class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 sm:max-w-sm">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            Status
            <select wire:model.live="status" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="all">All users</option>
                <option value="active">Active</option>
                <option value="unverified">Unverified</option>
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
            users
        </label>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/70">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Orders</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Joined</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email ?? 'No email' }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $user->phone ?: '—' }}</td>
                            <td class="px-5 py-4 text-sm">
                                @php($userRoles = $user->roles->pluck('name'))
                                @if ($userRoles->isEmpty())
                                    <span class="text-gray-400 dark:text-gray-500">Customer</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ $userRoles->implode(', ') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $user->orders_count }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' => $user->banned,
                                    'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' => ! $user->banned && $user->email_verified_at === null,
                                    'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-200' => ! $user->banned && $user->email_verified_at !== null,
                                ])>
                                    {{ $user->banned ? 'Suspended' : ($user->email_verified_at === null ? 'Unverified' : 'Active') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                            <td class="space-x-1 whitespace-nowrap px-5 py-4 text-right">
                                @if ($canManage)
                                    <button type="button" wire:click="edit({{ $user->id }})" title="Edit" aria-label="Edit user" class="admin-primary-soft-bg admin-primary-text inline-flex h-8 w-8 items-center justify-center rounded-md transition hover:brightness-95">
                                        <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" wire:click="toggleSuspension({{ $user->id }})"
                                            wire:confirm="{{ $user->banned ? 'Restore this user account?' : 'Suspend this user account? Their data will be retained.' }}"
                                            title="{{ $user->banned ? 'Restore' : 'Suspend' }}"
                                            aria-label="{{ $user->banned ? 'Restore user' : 'Suspend user' }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md transition {{ $user->banned ? 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-950 dark:text-green-300 dark:hover:bg-green-900' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-950 dark:text-amber-300 dark:hover:bg-amber-900' }}">
                                        <i class="fas {{ $user->banned ? 'fa-rotate-left' : 'fa-user-slash' }} text-xs" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" wire:click="delete({{ $user->id }})"
                                            wire:confirm="Delete this user account? Their order history is kept, but the login and saved addresses are removed."
                                            title="Delete" aria-label="Delete user"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900">
                                        <i class="fas fa-trash-can text-xs" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <p class="font-medium text-gray-700 dark:text-gray-200">No users found</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Adjust your search or add a user account.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $users->links() }}</div>
    </div>

    @if ($canManage && $isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
            <div class="fixed inset-0 bg-gray-950/50" wire:click="close"></div>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <h3 id="user-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $userId ? 'Edit user' : 'Add user' }}</h3>
                        <button type="button" wire:click="close" aria-label="Close" class="rounded-lg px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">✕</button>
                    </div>

                    <form wire:submit="save">
                        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                            <div>
                                <label for="user-name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Full name *</label>
                                <input id="user-name" type="text" wire:model="name" autocomplete="name" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="user-email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Email *</label>
                                <input id="user-email" type="email" wire:model="email" autocomplete="email" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="user-phone" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Phone</label>
                                <input id="user-phone" type="tel" wire:model="phone" autocomplete="tel" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="user-password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ $userId ? 'Reset password' : 'Password *' }}</label>
                                <input id="user-password" type="password" wire:model="password" autocomplete="new-password" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $userId ? 'Leave blank to keep the current password.' : 'Use at least 8 characters.' }}</p>
                                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="user-role" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Role</label>
                                <select id="user-role" wire:model="role" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                    <option value="">Customer (no panel access)</option>
                                    @foreach ($roles as $roleName)
                                        <option value="{{ $roleName }}">{{ $roleName }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Roles decide which admin pages this account can open.</p>
                                @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="user-address" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Profile address</label>
                                <input id="user-address" type="text" wire:model="address" autocomplete="street-address" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="user-country" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Country</label>
                                <input id="user-country" type="text" wire:model="country" autocomplete="country-name" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('country') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="user-state" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">State / province</label>
                                <input id="user-state" type="text" wire:model="state" autocomplete="address-level1" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('state') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="user-city" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">City</label>
                                <input id="user-city" type="text" wire:model="city" autocomplete="address-level2" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="user-postal-code" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Postal code</label>
                                <input id="user-postal-code" type="text" wire:model="postalCode" autocomplete="postal-code" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('postalCode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-950 sm:flex-row sm:justify-end">
                            <button type="button" wire:click="close" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                            <button type="submit" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white">Save user</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
