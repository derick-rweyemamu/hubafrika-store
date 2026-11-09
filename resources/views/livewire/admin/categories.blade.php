@php
    $canManage = auth()->user()?->can('categories.manage') ?? false;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Categories</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Organise the catalog into a browsable category tree.</p>
        </div>
        @if ($canManage)
            <button type="button" wire:click="create" class="admin-primary-bg rounded-lg px-4 py-2.5 text-sm font-semibold text-white">Add category</button>
        @endif
    </div>

    <x-flash-toast />

    <div class="flex flex-col gap-3 sm:flex-row">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search categories..." class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 sm:max-w-sm">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            Show
            <select wire:model.live="perPage" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
            categories
        </label>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/70">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Products</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subcategories</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Flags</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($categories as $category)
                        <tr wire:key="category-{{ $category->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($category->iconUrl())
                                        <img src="{{ $category->iconUrl() }}" alt="" class="h-10 w-10 rounded-lg object-contain">
                                    @elseif ($category->coverImageUrl())
                                        <img src="{{ $category->coverImageUrl() }}" alt="" class="h-10 w-10 rounded-lg object-cover">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-xs font-semibold text-gray-400 dark:bg-gray-800">CAT</div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $category->name }}
                                            @if ($category->parent)
                                                <span class="ml-1 text-xs font-normal text-gray-500 dark:text-gray-400">in {{ $category->parent->name }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $category->slug }} &middot; level {{ $category->level }} &middot; order {{ $category->order_level }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $category->products_count }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $category->children_count }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                <div class="flex flex-wrap gap-1">
                                    @if ($category->featured)
                                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-300">Featured</span>
                                    @endif
                                    @if ($category->top)
                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-300">Top</span>
                                    @endif
                                    @if ($category->digital)
                                        <span class="rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-950 dark:text-purple-300">Digital</span>
                                    @endif
                                    @if ((float) $category->discount > 0)
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">{{ rtrim(rtrim(number_format((float) $category->discount, 2, '.', ''), '0'), '.') }}% off</span>
                                    @endif
                                    @if (! $category->featured && ! $category->top && ! $category->digital && (float) $category->discount <= 0)
                                        <span class="text-xs text-gray-400 dark:text-gray-500">None</span>
                                    @endif
                                </div>
                            </td>
                            <td class="space-x-1 whitespace-nowrap px-5 py-4 text-right">
                                @if ($canManage)
                                    <button type="button" wire:click="edit({{ $category->id }})" title="Edit" aria-label="Edit category" class="admin-primary-soft-bg admin-primary-text inline-flex h-8 w-8 items-center justify-center rounded-md transition hover:brightness-95">
                                        <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" wire:click="delete({{ $category->id }})" wire:confirm="Delete this category?" title="Delete" aria-label="Delete category" class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900">
                                        <i class="fas fa-trash-can text-xs" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No categories have been created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $categories->links() }}</div>
    </div>

    @if ($canManage && $isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="category-modal-title">
            <div class="fixed inset-0 bg-gray-950/50" wire:click="$set('isOpen', false)"></div>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <form wire:submit="save" class="w-full max-w-2xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <h3 id="category-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $categoryId ? 'Edit category' : 'Add category' }}</h3>
                    </div>
                    <div class="space-y-5 p-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="category-name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Category name *</label>
                                <input id="category-name" type="text" wire:model="name" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="category-parent" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Parent category</label>
                                <select id="category-parent" wire:model="parentId" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                    <option value="">No parent (top level)</option>
                                    @foreach ($parentOptions as $option)
                                        <option value="{{ $option->id }}">{{ str_repeat('— ', $option->level) }}{{ $option->name }}</option>
                                    @endforeach
                                </select>
                                @error('parentId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="category-order" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Display order</label>
                                <input id="category-order" type="number" min="0" wire:model="orderLevel" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('orderLevel') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="category-discount" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Discount (%)</label>
                                <input id="category-discount" type="number" step="0.01" min="0" max="100" wire:model="discount" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('discount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                <input type="checkbox" wire:model="featured" class="admin-primary-focus rounded border-gray-300">
                                Featured
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                <input type="checkbox" wire:model="top" class="admin-primary-focus rounded border-gray-300">
                                Top category
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                <input type="checkbox" wire:model="digital" class="admin-primary-focus rounded border-gray-300">
                                Digital products only
                            </label>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-3">
                            <div>
                                <label for="category-banner" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Banner</label>
                                <input id="category-banner" type="file" accept="image/jpeg,image/png,image/webp" wire:model="bannerUpload" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 dark:text-gray-300 dark:file:bg-gray-800">
                                @error('bannerUpload') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                <button type="button" wire:click="openMediaPicker('banner')" class="mt-2 rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                    Choose banner from media
                                </button>
                                @if ($bannerUpload)
                                    <img src="{{ $bannerUpload->temporaryUrl() }}" alt="Selected banner" class="mt-3 h-16 w-full rounded-lg object-cover">
                                @elseif (($pickedMediaUrls['banner'][0] ?? null) !== null)
                                    <img src="{{ $pickedMediaUrls['banner'][0] }}" alt="Banner picked from the media library" class="mt-3 h-16 w-full rounded-lg object-cover">
                                @elseif ($currentBanner)
                                    <img src="{{ $currentBanner }}" alt="Current banner" class="mt-3 h-16 w-full rounded-lg object-cover">
                                @endif
                                @if (($pickedMediaUrls['banner'][0] ?? null) !== null)
                                    <button type="button" wire:click="clearPickedMedia('banner')" class="mt-1 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400">Clear picked banner</button>
                                @endif
                            </div>
                            <div>
                                <label for="category-icon" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Icon</label>
                                <input id="category-icon" type="file" accept="image/jpeg,image/png,image/webp" wire:model="iconUpload" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 dark:text-gray-300 dark:file:bg-gray-800">
                                @error('iconUpload') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                <button type="button" wire:click="openMediaPicker('icon')" class="mt-2 rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                    Choose icon from media
                                </button>
                                @if ($iconUpload)
                                    <img src="{{ $iconUpload->temporaryUrl() }}" alt="Selected icon" class="mt-3 h-16 w-16 rounded-lg object-contain">
                                @elseif (($pickedMediaUrls['icon'][0] ?? null) !== null)
                                    <img src="{{ $pickedMediaUrls['icon'][0] }}" alt="Icon picked from the media library" class="mt-3 h-16 w-16 rounded-lg object-contain">
                                @elseif ($currentIcon)
                                    <img src="{{ $currentIcon }}" alt="Current icon" class="mt-3 h-16 w-16 rounded-lg object-contain">
                                @endif
                                @if (($pickedMediaUrls['icon'][0] ?? null) !== null)
                                    <button type="button" wire:click="clearPickedMedia('icon')" class="mt-1 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400">Clear picked icon</button>
                                @endif
                            </div>
                            <div>
                                <label for="category-cover" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Cover image</label>
                                <input id="category-cover" type="file" accept="image/jpeg,image/png,image/webp" wire:model="coverImageUpload" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 dark:text-gray-300 dark:file:bg-gray-800">
                                @error('coverImageUpload') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                <button type="button" wire:click="openMediaPicker('cover_image')" class="mt-2 rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                    Choose cover from media
                                </button>
                                @if ($coverImageUpload)
                                    <img src="{{ $coverImageUpload->temporaryUrl() }}" alt="Selected cover image" class="mt-3 h-16 w-full rounded-lg object-cover">
                                @elseif (($pickedMediaUrls['cover_image'][0] ?? null) !== null)
                                    <img src="{{ $pickedMediaUrls['cover_image'][0] }}" alt="Cover picked from the media library" class="mt-3 h-16 w-full rounded-lg object-cover">
                                @elseif ($currentCoverImage)
                                    <img src="{{ $currentCoverImage }}" alt="Current cover image" class="mt-3 h-16 w-full rounded-lg object-cover">
                                @endif
                                @if (($pickedMediaUrls['cover_image'][0] ?? null) !== null)
                                    <button type="button" wire:click="clearPickedMedia('cover_image')" class="mt-1 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400">Clear picked cover</button>
                                @endif
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">JPG, PNG, or WebP up to 2 MB per image.</p>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-950 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="$set('isOpen', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white">Save category</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($mediaPickerOpen)
        <livewire:admin.media-picker />
    @endif
</div>
