@php
    $canManage = auth()->user()?->can('units.manage') ?? false;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Units</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The measuring units you can assign to products, such as pieces, kilograms or litres.</p>
        </div>
        @if ($canManage)
            <button type="button" wire:click="create" class="admin-primary-bg rounded-lg px-4 py-2.5 text-sm font-semibold text-white">Add unit</button>
        @endif
    </div>

    <x-flash-toast />

    <div class="flex flex-col gap-3 sm:flex-row">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search units..." class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 sm:max-w-sm">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            Show
            <select wire:model.live="perPage" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
            units
        </label>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/70">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Products</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($units as $unit)
                        <tr wire:key="unit-{{ $unit->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $unit->name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $unit->products_count }}</td>
                            <td class="space-x-1 whitespace-nowrap px-5 py-4 text-right">
                                @if ($canManage)
                                    <button type="button" wire:click="edit({{ $unit->id }})" title="Edit" aria-label="Edit unit" class="admin-primary-soft-bg admin-primary-text inline-flex h-8 w-8 items-center justify-center rounded-md transition hover:brightness-95">
                                        <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" wire:click="delete({{ $unit->id }})" wire:confirm="Delete this unit? Products that use it will be left without a unit." title="Delete" aria-label="Delete unit" class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900">
                                        <i class="fas fa-trash-can text-xs" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No units have been created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $units->links() }}</div>
    </div>

    @if ($canManage && $isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="unit-modal-title">
            <div class="fixed inset-0 bg-gray-950/50" wire:click="$set('isOpen', false)"></div>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <form wire:submit="save" class="w-full max-w-xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <h3 id="unit-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $unitId ? 'Edit unit' : 'Add unit' }}</h3>
                    </div>
                    <div class="space-y-5 p-5">
                        <div>
                            <label for="unit-name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Unit name *</label>
                            <input id="unit-name" type="text" wire:model="name" placeholder="Piece, Kilogram, Litre..." class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-950 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="$set('isOpen', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white">Save unit</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
