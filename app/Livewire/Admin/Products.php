<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PicksLibraryMedia;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaReference;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use RuntimeException;

class Products extends Component
{
    use PicksLibraryMedia, WithFileUploads, WithPagination;

    private const SORTABLE_COLUMNS = ['name', 'unit_price', 'current_stock', 'created_at'];

    public string $search = '';

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public bool $isOpen = false;

    public ?int $productId = null;

    public string $name = '';

    public string $categoryId = '';

    public string $brandId = '';

    public string $unitId = '';

    public string $unitPrice = '';

    public string $purchasePrice = '';

    public string $currentStock = '0';

    public string $discount = '0';

    public string $discountType = 'amount';

    public string $tax = '';

    public string $taxType = '';

    public string $barcode = '';

    public string $tags = '';

    public string $description = '';

    public bool $published = true;

    public bool $featured = false;

    public bool $digital = false;

    /**
     * Photos already attached to the product.
     *
     * Each entry is `['media' => ?int, 'path' => ?string, 'url' => string]`
     * so media-library uploads and legacy paths can be managed side by side.
     *
     * @var array<int, array{media: int|null, path: string|null, url: string}>
     */
    public array $existingPhotos = [];

    /** @var array<int, TemporaryUploadedFile> */
    public array $uploads = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        abort_unless(auth()->user()?->can('products.view'), 403);

        if (! in_array($field, self::SORTABLE_COLUMNS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorizeAdmin();
        $this->resetValidation();
        $this->resetForm();
        $this->isOpen = true;
    }

    public function edit(int $id): void
    {
        $this->authorizeAdmin();
        $this->resetValidation();

        $product = Product::query()->findOrFail($id);

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->categoryId = (string) $product->category_id;
        $this->brandId = (string) ($product->brand_id ?? '');
        $this->unitId = (string) ($product->unit ?? '');
        $this->unitPrice = (string) $product->unit_price;
        $this->purchasePrice = (string) ($product->purchase_price ?? '');
        $this->currentStock = (string) $product->current_stock;
        $this->discount = (string) ($product->discount ?? 0);
        $this->discountType = $product->discount_type ?? 'amount';
        $this->tax = (string) ($product->tax ?? '');
        $this->taxType = $product->tax_type ?? '';
        $this->barcode = $product->barcode ?? '';
        $this->tags = $product->tags ?? '';
        $this->description = $product->description ?? '';
        $this->published = $product->published;
        $this->featured = $product->featured;
        $this->digital = $product->digital;
        $this->existingPhotos = $this->photoPreview($product);
        $this->uploads = [];
        $this->resetPickedMedia();
        $this->isOpen = true;
    }

    public function removePhoto(int $index): void
    {
        $this->authorizeAdmin();

        if (array_key_exists($index, $this->existingPhotos)) {
            unset($this->existingPhotos[$index]);
            $this->existingPhotos = array_values($this->existingPhotos);
        }
    }

    public function save(): void
    {
        $this->authorizeAdmin();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:200'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'brandId' => ['nullable', 'integer', 'exists:brands,id'],
            'unitId' => ['nullable', 'integer', 'exists:units,id'],
            'unitPrice' => ['required', 'numeric', 'min:0'],
            'purchasePrice' => ['nullable', 'numeric', 'min:0'],
            'currentStock' => ['required', 'integer', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'discountType' => ['required', 'in:amount,percent'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'taxType' => ['nullable', 'in:amount,percent'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:60000'],
            'published' => ['boolean'],
            'featured' => ['boolean'],
            'digital' => ['boolean'],
            'uploads' => ['array', 'max:5'],
            'uploads.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($validated['discountType'] === 'percent' && (float) $validated['discount'] > 100) {
            $this->addError('discount', 'A percentage discount cannot exceed 100.');

            return;
        }

        $product = $this->productId === null
            ? new Product
            : Product::query()->findOrFail($this->productId);
        $savedLegacyPhotos = array_values($product->photos ?? []);
        $retainedMediaIds = [];
        $retainedReferenceIds = [];
        $retainedLegacyPhotos = [];

        foreach ($this->existingPhotos as $photo) {
            if (($photo['media'] ?? null) !== null) {
                $retainedMediaIds[] = (int) $photo['media'];
            } elseif (($photo['reference'] ?? null) !== null) {
                $retainedReferenceIds[] = (int) $photo['reference'];
            } elseif (($photo['path'] ?? null) !== null) {
                $retainedLegacyPhotos[] = (string) $photo['path'];
            }
        }

        if (count($retainedMediaIds) + count($retainedReferenceIds) + count($retainedLegacyPhotos) + count($this->uploads) + count($this->pickedMediaIdsFor('gallery')) > 5) {
            $this->addError('uploads', 'A product can have up to 5 images.');

            return;
        }

        $slug = $this->uniqueSlug($validated['name'], $product->exists ? $product->id : null);

        DB::transaction(function () use ($product, $validated, $retainedLegacyPhotos, $slug): void {
            $product->fill([
                'name' => $validated['name'],
                'slug' => $slug,
                'category_id' => $validated['categoryId'],
                'brand_id' => $validated['brandId'] ?: null,
                'unit' => $validated['unitId'] ?: null,
                'photos' => $retainedLegacyPhotos,
                'thumbnail_img' => $retainedLegacyPhotos[0] ?? null,
                'unit_price' => $validated['unitPrice'],
                'purchase_price' => $validated['purchasePrice'] ?: null,
                'current_stock' => $validated['currentStock'],
                'discount' => $validated['discount'] ?: 0,
                'discount_type' => $validated['discountType'],
                'tax' => $validated['tax'] ?: null,
                'tax_type' => $validated['taxType'] ?: null,
                'barcode' => $validated['barcode'] ?: null,
                'tags' => $validated['tags'] ?: null,
                'description' => $validated['description'] ?: null,
                'published' => $validated['published'],
                'featured' => $validated['featured'],
                'digital' => $validated['digital'],
            ]);
            $product->save();
        });

        foreach ($product->getMedia('gallery') as $media) {
            if (! in_array((int) $media->getKey(), $retainedMediaIds, true)) {
                $media->delete();
            }
        }

        $product->pruneLibraryMediaReferences('gallery', $retainedReferenceIds);

        foreach ($this->uploads as $upload) {
            $product->addLibraryMedia($upload, 'gallery', $slug);
        }

        $this->referencePickedMedia($product, 'gallery', 'gallery');

        $this->deleteStoredFiles(array_diff($savedLegacyPhotos, $retainedLegacyPhotos));

        session()->flash('message', $this->productId === null
            ? 'Product created successfully.'
            : 'Product updated successfully.');
        $this->isOpen = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $this->authorizeAdmin();

        $product = Product::query()->findOrFail($id);

        if ($product->num_of_sale > 0) {
            session()->flash('error', 'Products with sales cannot be deleted. Unpublish the product instead.');

            return;
        }

        $photos = $product->photos ?? [];
        $product->clearMediaCollection('gallery');
        $product->clearMediaCollection('thumbnail');
        $product->delete();

        $this->deleteStoredFiles($photos);

        session()->flash('message', 'Product deleted successfully.');
    }

    public function render()
    {
        $sortField = in_array($this->sortField, self::SORTABLE_COLUMNS, true)
            ? $this->sortField
            : 'created_at';
        $sortDirection = in_array($this->sortDirection, ['asc', 'desc'], true)
            ? $this->sortDirection
            : 'desc';
        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;

        return view('livewire.admin.products', [
            'products' => Product::query()
                ->with(['category', 'brand'])
                ->when($this->search !== '', fn (Builder $query) => $query->where('name', 'like', '%'.mb_substr($this->search, 0, 100).'%'))
                ->orderBy($sortField, $sortDirection)
                ->paginate($perPage),
            'categories' => Category::query()->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('name')->get(['id', 'name']),
        ])->layout('components.layouts.admin', ['title' => 'Products']);
    }

    private function resetForm(): void
    {
        $this->reset([
            'productId',
            'name',
            'categoryId',
            'brandId',
            'unitId',
            'unitPrice',
            'purchasePrice',
            'currentStock',
            'discount',
            'discountType',
            'tax',
            'taxType',
            'barcode',
            'tags',
            'description',
            'published',
            'featured',
            'digital',
            'existingPhotos',
            'uploads',
        ]);

        $this->currentStock = '0';
        $this->discount = '0';
        $this->discountType = 'amount';
        $this->published = true;
        $this->resetPickedMedia();
    }

    /**
     * The gallery accepts several library pictures in one visit to the picker.
     */
    protected function mediaPickerAcceptsMultiple(string $target): bool
    {
        return $target === 'gallery';
    }

    /**
     * Only as many picks as the gallery still has room for.
     */
    protected function mediaPickerMaxFiles(string $target): int
    {
        if ($target !== 'gallery') {
            return 1;
        }

        return max(0, 5 - count($this->existingPhotos) - count($this->uploads));
    }

    /**
     * Gallery pictures in the order they are shown: pictures picked from the
     * library first, then uploaded copies, then the legacy `photos` column
     * when neither exists.
     *
     * @return array<int, array{media: int|null, reference: int|null, path: string|null, url: string}>
     */
    private function photoPreview(Product $product): array
    {
        $preview = $product->mediaReferenceItems('gallery')
            ->filter(fn (MediaReference $reference): bool => $reference->media !== null)
            ->map(fn (MediaReference $reference): array => [
                'media' => null,
                'reference' => (int) $reference->getKey(),
                'path' => null,
                'url' => (string) $reference->media?->getUrl(),
            ])
            ->values()
            ->all();

        foreach ($product->getMedia('gallery') as $item) {
            $preview[] = [
                'media' => (int) $item->getKey(),
                'reference' => null,
                'path' => null,
                'url' => $item->getUrl(),
            ];
        }

        if ($preview !== []) {
            return $preview;
        }

        foreach ((array) ($product->photos ?? []) as $path) {
            if (! is_string($path) || trim($path) === '') {
                continue;
            }

            $preview[] = [
                'media' => null,
                'reference' => null,
                'path' => $path,
                'url' => asset('storage/'.ltrim($path, '/')),
            ];
        }

        return $preview;
    }

    private function uniqueSlug(string $name, ?int $ignoreId): string
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deleteStoredFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (! Storage::disk('public')->delete($path)) {
                throw new RuntimeException('A product image could not be deleted.');
            }
        }
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->can('products.manage'), 403);
    }
}
