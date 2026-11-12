@php
    $canManage = auth()->user()?->can('products.manage') ?? false;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Product catalog</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage products, prices, stock, and visibility.</p>
        </div>
        @if ($canManage)
            <button type="button" wire:click="create" class="admin-primary-bg rounded-lg px-4 py-2.5 text-sm font-semibold text-white">
                Add product
            </button>
        @endif
    </div>

    <x-flash-toast />

    <div class="flex flex-col gap-3 sm:flex-row">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search products..."
               class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 sm:max-w-sm">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            Show
            <select wire:model.live="perPage" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
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
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category / brand</th>
                        <th>
                            <button type="button" wire:click="sortBy('unit_price')" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Price {{ $sortField === 'unit_price' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th>
                            <button type="button" wire:click="sortBy('current_stock')" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Stock {{ $sortField === 'current_stock' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($products as $product)
                        <tr wire:key="product-{{ $product->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($product->thumbnailUrl())
                                        <img src="{{ $product->thumbnailUrl() }}" alt="" class="h-11 w-11 rounded-lg object-cover">
                                    @else
                                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-gray-100 text-xs font-semibold text-gray-400 dark:bg-gray-800">IMG</div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                <div>{{ $product->category?->name ?? 'No category' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $product->brand?->name ?? 'No brand' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700 dark:text-gray-200">
                                {{ number_format((float) $product->unit_price, 2) }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $product->current_stock }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-200' => $product->published,
                                    'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' => ! $product->published,
                                ])>
                                    {{ $product->published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="space-x-1 whitespace-nowrap px-5 py-4 text-right">
                                @if ($canManage)
                                    <button type="button" wire:click="edit({{ $product->id }})" title="Edit" aria-label="Edit product" class="admin-primary-soft-bg admin-primary-text inline-flex h-8 w-8 items-center justify-center rounded-md transition hover:brightness-95">
                                        <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" wire:click="delete({{ $product->id }})" wire:confirm="Delete this product and its images?" title="Delete" aria-label="Delete product" class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900">
                                        <i class="fas fa-trash-can text-xs" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-medium text-gray-700 dark:text-gray-200">No products found</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a category before adding your first product.</p>
                                @if (auth()->user()?->can('categories.view'))
                                    <a href="{{ route('admin.categories') }}" wire:navigate class="admin-primary-text mt-3 inline-flex text-sm font-semibold">Manage categories</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $products->links() }}</div>
    </div>

    @if ($canManage && $isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="product-modal-title">
            <div class="fixed inset-0 bg-gray-950/50" wire:click="$set('isOpen', false)"></div>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <form wire:submit="save" class="w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-900">
                    <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <div>
                            <h3 id="product-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $productId ? 'Edit product' : 'Add product' }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Fields marked required must be completed.</p>
                        </div>
                        <button type="button" wire:click="$set('isOpen', false)" class="rounded-lg px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="Close">✕</button>
                    </div>

                    <div class="max-h-[70vh] space-y-6 overflow-y-auto p-5">
                        @if ($categories->isEmpty())
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100">
                                Add a category before creating products.
                                @if (auth()->user()?->can('categories.view'))
                                    <a href="{{ route('admin.categories') }}" wire:navigate class="font-semibold underline">Manage categories</a>
                                @endif
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="product-name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Product name *</label>
                                <input id="product-name" type="text" wire:model.live.debounce.250ms="name" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="product-slug" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">URL slug</label>
                                <input id="product-slug" type="text" value="{{ $slug }}" placeholder="Generated from product name" readonly
                                       class="w-full rounded-lg border-gray-300 bg-gray-100 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Generated directly from the product name.</p>
                            </div>

                            <div>
                                <label for="product-category" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Category *</label>
                                <select id="product-category" wire:model="categoryId" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                    <option value="">Select category</option>
                                    @foreach ($categories as $category)
                                        <option wire:key="category-option-{{ $category->id }}" value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('categoryId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="product-brand" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Brand</label>
                                <select id="product-brand" wire:model="brandId" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                    <option value="">No brand</option>
                                    @foreach ($brands as $brand)
                                        <option wire:key="brand-option-{{ $brand->id }}" value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                @error('brandId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="product-unit" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Unit</label>
                                <select id="product-unit" wire:model="unitId" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                    <option value="">No unit</option>
                                    @foreach ($units as $unit)
                                        <option wire:key="unit-option-{{ $unit->id }}" value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('unitId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="product-unit-price" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Selling price *</label>
                                <input id="product-unit-price" type="number" min="0" step="0.01" wire:model="unitPrice" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('unitPrice') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="product-purchase-price" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Purchase price</label>
                                <input id="product-purchase-price" type="number" min="0" step="0.01" wire:model="purchasePrice" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('purchasePrice') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="product-stock" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Stock quantity *</label>
                                <input id="product-stock" type="number" min="0" step="1" wire:model="currentStock" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('currentStock') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="product-barcode" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Barcode</label>
                                <input id="product-barcode" type="text" wire:model="barcode" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('barcode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="product-discount" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Discount</label>
                                <div class="flex gap-2">
                                    <input id="product-discount" type="number" min="0" step="0.01" wire:model="discount" class="admin-primary-focus min-w-0 flex-1 rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                    <select wire:model="discountType" aria-label="Discount type" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                        <option value="amount">Amount</option>
                                        <option value="percent">Percent</option>
                                    </select>
                                </div>
                                @error('discount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="product-tax" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Tax</label>
                                <div class="flex gap-2">
                                    <input id="product-tax" type="number" min="0" step="0.01" wire:model="tax" class="admin-primary-focus min-w-0 flex-1 rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                    <select wire:model="taxType" aria-label="Tax type" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                        <option value="">No tax</option>
                                        <option value="amount">Amount</option>
                                        <option value="percent">Percent</option>
                                    </select>
                                </div>
                                @error('tax') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="product-tags" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Tags</label>
                                <input id="product-tags" type="text" wire:model="tags" placeholder="Separate tags with commas" class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                                @error('tags') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="product-description" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Description</label>
                                <x-rich-editor id="product-description" wire:model="description" placeholder="Write product details, specifications, and highlights..." />
                                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="product-photos" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Product images</label>
                                <input id="product-photos" type="file" accept="image/jpeg,image/png,image/webp" multiple wire:model="uploads" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 dark:text-gray-300 dark:file:bg-gray-800">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Up to 5 images, 4 MB each. JPG, PNG, or WebP.</p>
                                @error('uploads') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                @error('uploads.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                <div wire:loading wire:target="uploads" class="mt-2 text-sm text-gray-500">Uploading images...</div>
                                @if (auth()->user()?->can('media.view'))
                                    <button type="button" wire:click="openMediaPicker('gallery')" class="mt-2 rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                        Choose images from media
                                    </button>
                                @endif
                                <div class="mt-3 flex flex-wrap gap-3">
                                    @foreach ($existingPhotos as $index => $photo)
                                        <div wire:key="existing-photo-{{ $index }}" class="relative">
                                            <img src="{{ $photo['url'] }}" alt="Current product image" class="h-20 w-20 rounded-lg object-cover">
                                            <button type="button" wire:click="removePhoto({{ $index }})" class="absolute -right-2 -top-2 rounded-full bg-red-600 px-2 py-1 text-xs text-white" aria-label="Remove current image">×</button>
                                        </div>
                                    @endforeach
                                    @foreach ($uploads as $index => $upload)
                                        <img wire:key="new-photo-{{ $index }}" src="{{ $upload->temporaryUrl() }}" alt="Selected product image" class="h-20 w-20 rounded-lg object-cover">
                                    @endforeach
                                    @foreach ($pickedMediaUrls['gallery'] ?? [] as $index => $pickedUrl)
                                        <div wire:key="picked-photo-{{ $index }}" class="relative">
                                            <img src="{{ $pickedUrl }}" alt="Product image picked from the media library" class="h-20 w-20 rounded-lg object-cover">
                                            <button type="button" wire:click="removePickedMedia('gallery', {{ $index }})" class="absolute -right-2 -top-2 rounded-full bg-gray-900 px-2 py-1 text-xs text-white" aria-label="Remove picked image">×</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-5 sm:col-span-2">
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                    <input type="checkbox" wire:model="published" class="admin-primary-focus rounded border-gray-300">
                                    Published
                                </label>
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                    <input type="checkbox" wire:model="featured" class="admin-primary-focus rounded border-gray-300">
                                    Featured
                                </label>
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                    <input type="checkbox" wire:model="digital" class="admin-primary-focus rounded border-gray-300">
                                    Digital product
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-950 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="$set('isOpen', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">Save product</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($mediaPickerOpen && auth()->user()?->can('media.view'))
        <livewire:admin.media-picker />
    @endif
</div>
