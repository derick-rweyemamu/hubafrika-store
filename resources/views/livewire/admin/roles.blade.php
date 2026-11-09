<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Roles</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Bundle permissions into a role, then assign that role to a staff account.</p>
        </div>
        @if ($canManage)
            <button type="button" wire:click="create" class="admin-primary-bg rounded-lg px-4 py-2.5 text-sm font-semibold text-white">Add role</button>
        @endif
    </div>

    <x-flash-toast />

    <div class="flex flex-col gap-3 sm:flex-row">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search roles..." class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 sm:max-w-sm">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            Show
            <select wire:model.live="perPage" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
            roles
        </label>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/70">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Permissions</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Accounts</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($roles as $role)
                        <tr wire:key="role-{{ $role->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $role->name }}</span>
                                    @if ($role->name === $superRole)
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">Built in</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $role->guard_name }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $role->permissions_count }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $role->users_count }}</td>
                            <td class="space-x-1 whitespace-nowrap px-5 py-4 text-right">
                                @if ($canManage)
                                    <button type="button" wire:click="edit({{ $role->id }})" title="Edit" aria-label="Edit role" class="admin-primary-soft-bg admin-primary-text inline-flex h-8 w-8 items-center justify-center rounded-md transition hover:brightness-95">
                                        <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                                    </button>
                                    @if ($role->name !== $superRole)
                                        <button type="button" wire:click="delete({{ $role->id }})" wire:confirm="Delete this role?" title="Delete" aria-label="Delete role" class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900">
                                            <i class="fas fa-trash-can text-xs" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">Read only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No roles have been created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $roles->links() }}</div>
    </div>

    @if ($canManage && $isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="role-modal-title">
            <div class="fixed inset-0 bg-gray-950/50" wire:click="$set('isOpen', false)"></div>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <form wire:submit="save" class="w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <h3 id="role-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $roleId ? 'Edit role' : 'Add role' }}</h3>
                    </div>

                    <div class="max-h-[70vh] space-y-5 overflow-y-auto p-5">
                        <div>
                            <label for="role-name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Role name *</label>
                            <input id="role-name" type="text" wire:model="name" @disabled($roleId && $name === $superRole)
                                   class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-950 dark:disabled:bg-gray-800">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            @if ($roleId && $name === $superRole)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">The built-in administrator role keeps its name because permission checks look it up.</p>
                            @endif
                        </div>

                        @error('selectedPermissions.*') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                        <div class="space-y-4">
                            @foreach ($permissionGroups as $group => $permissions)
                                <fieldset class="rounded-lg border border-gray-200 dark:border-gray-800">
                                    <legend class="sr-only">{{ $group }}</legend>
                                    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-800 dark:bg-gray-800/70">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ $group }}</span>
                                        <button type="button" wire:click="toggleGroup(@js($group))" class="text-xs font-medium admin-primary-text">Select all / none</button>
                                    </div>
                                    <div class="grid gap-2 p-4 sm:grid-cols-2">
                                        @foreach ($permissions as $permission => $label)
                                            <label wire:key="permission-{{ $permission }}" class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-200">
                                                <input type="checkbox" value="{{ $permission }}" wire:model="selectedPermissions" class="admin-primary-focus mt-0.5 rounded border-gray-300">
                                                <span>
                                                    <span class="font-medium">{{ $label }}</span>
                                                    <span class="block text-xs text-gray-400 dark:text-gray-500">{{ $permission }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-950 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="$set('isOpen', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white">Save role</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
