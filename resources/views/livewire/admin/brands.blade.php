@php
    $canManage = auth()->user()?->can('brands.manage') ?? false;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Brands</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage the brands assigned to your products.</p>
        </div>
        @if ($canManage)
            <button type="button" wire:click="create" class="admin-primary-bg rounded-lg px-4 py-2.5 text-sm font-semibold text-white">Add brand</button>
        @endif
    </div>

    <x-flash-toast />

    <div class="flex flex-col gap-3 sm:flex-row">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search brands..." class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 sm:max-w-sm">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            Show
            <select wire:model.live="perPage" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
            brands
        </label>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/70">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Brand</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Products</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Placement</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($brands as $brand)
                        <tr wire:key="brand-{{ $brand->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($brand->logoUrl())
                                        <img src="{{ $brand->logoUrl() }}" alt="" class="h-10 w-10 rounded-lg object-contain">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-xs font-semibold text-gray-400 dark:bg-gray-800">LOGO</div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $brand->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $brand->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $brand->products_count }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $brand->top ? 'Featured' : 'Standard' }}</td>
                            <td class="space-x-1 whitespace-nowrap px-5 py-4 text-right">
                                @if ($canManage)
                                    <button type="button" wire:click="edit({{ $brand->id }})" title="Edit" aria-label="Edit brand" class="admin-primary-soft-bg admin-primary-text inline-flex h-8 w-8 items-center justify-center rounded-md transition hover:brightness-95">
                                        <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" wire:click="delete({{ $brand->id }})" wire:confirm="Delete this brand? Products assigned to it will remain without a brand." title="Delete" aria-label="Delete brand" class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900">
                                        <i class="fas fa-trash-can text-xs" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No brands have been created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $brands->links() }}</div>
    </div>

    @if ($canManage && $isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="brand-modal-title">
            <div class="fixed inset-0 bg-gray-950/50" wire:click="$set('isOpen', false)"></div>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <form wire:submit="save" class="w-full max-w-xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <h3 id="brand-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $brandId ? 'Edit brand' : 'Add brand' }}</h3>
                    </div>
                    <div class="space-y-5 p-5">
                        <div>
                            <label for="brand-name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Brand name *</label>
                            <input id="brand-name" type="text" wire:model="name" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="brand-logo" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Brand logo</label>
                            <input id="brand-logo" type="file" accept="image/jpeg,image/png,image/webp" wire:model="logoUpload" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 dark:text-gray-300 dark:file:bg-gray-800">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, or WebP up to 2 MB.</p>
                            @error('logoUpload') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            <div wire:loading wire:target="logoUpload" class="mt-2 text-sm text-gray-500">Uploading logo...</div>
                            @if (auth()->user()?->can('media.view'))
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <button type="button" wire:click="openMediaPicker('logo')"
                                            class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                        Choose from media
                                    </button>
                                    @foreach ($pickedMediaUrls['logo'] ?? [] as $pickedUrl)
                                        <span class="relative inline-flex">
                                            <img src="{{ $pickedUrl }}" alt="Picture picked from the media library" class="h-10 w-10 rounded-lg border border-gray-200 object-contain dark:border-gray-700">
                                            <button type="button" wire:click="clearPickedMedia('logo')" aria-label="Remove picked logo"
                                                    class="absolute -right-1.5 -top-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-900 text-[10px] font-bold text-white">&times;</button>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            @if ($logoUpload)
                                <img src="{{ $logoUpload->temporaryUrl() }}" alt="Selected brand logo" class="mt-3 h-16 w-16 rounded-lg object-contain">
                            @elseif ($currentLogo)
                                <img src="{{ $currentLogo }}" alt="Current brand logo" class="mt-3 h-16 w-16 rounded-lg object-contain">
                            @endif
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                            <input type="checkbox" wire:model="top" class="admin-primary-focus rounded border-gray-300">
                            Feature this brand
                        </label>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-950 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="$set('isOpen', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white">Save brand</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($mediaPickerOpen && auth()->user()?->can('media.view'))
        <livewire:admin.media-picker />
    @endif
</div>
