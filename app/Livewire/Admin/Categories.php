<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PicksLibraryMedia;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use RuntimeException;

class Categories extends Component
{
    use PicksLibraryMedia, WithFileUploads, WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $isOpen = false;

    public ?int $categoryId = null;

    public string $name = '';

    public string $parentId = '';

    public string $orderLevel = '0';

    public string $discount = '0';

    public bool $featured = false;

    public bool $top = false;

    public bool $digital = false;

    public ?TemporaryUploadedFile $bannerUpload = null;

    public ?TemporaryUploadedFile $iconUpload = null;

    public ?TemporaryUploadedFile $coverImageUpload = null;

    public ?string $currentBanner = null;

    public ?string $currentIcon = null;

    public ?string $currentCoverImage = null;

    public function updatingSearch(): void
    {
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

        $category = Category::query()->findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->parentId = (string) ($category->parent_id ?: '');
        $this->orderLevel = (string) $category->order_level;
        $this->discount = (string) $category->discount;
        $this->featured = $category->featured;
        $this->top = $category->top;
        $this->digital = $category->digital;
        $this->currentBanner = $category->bannerUrl();
        $this->currentIcon = $category->iconUrl();
        $this->currentCoverImage = $category->coverImageUrl();
        $this->bannerUpload = null;
        $this->iconUpload = null;
        $this->coverImageUpload = null;
        $this->resetPickedMedia();
        $this->isOpen = true;
    }

    public function save(): void
    {
        $this->authorizeAdmin();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:50'],
            'parentId' => ['nullable', 'integer', 'exists:categories,id'],
            'orderLevel' => ['required', 'integer', 'min:0'],
            'discount' => ['required', 'numeric', 'between:0,100'],
            'featured' => ['boolean'],
            'top' => ['boolean'],
            'digital' => ['boolean'],
            'bannerUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'iconUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'coverImageUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $parentId = $validated['parentId'] === null || $validated['parentId'] === ''
            ? null
            : (int) $validated['parentId'];

        if (
            $this->categoryId !== null
            && $parentId !== null
            && ($parentId === $this->categoryId || $this->isDescendant($parentId, $this->categoryId))
        ) {
            $this->addError('parentId', 'A category cannot be nested under itself or one of its descendants.');

            return;
        }

        $category = $this->categoryId === null
            ? new Category
            : Category::query()->findOrFail($this->categoryId);
        $parent = $parentId === null ? null : Category::query()->findOrFail($parentId);
        $legacyImages = array_filter([
            'banner' => $category->banner,
            'icon' => $category->icon,
            'cover_image' => $category->cover_image,
        ]);

        DB::transaction(function () use ($category, $validated, $parentId, $parent): void {
            $category->fill([
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug($validated['name'], $category->exists ? $category->id : null),
                'parent_id' => $parentId,
                'level' => $parent === null ? 0 : $parent->level + 1,
                'order_level' => $validated['orderLevel'],
                'discount' => $validated['discount'],
                'featured' => $validated['featured'],
                'top' => $validated['top'],
                'digital' => $validated['digital'],
            ]);
            $category->save();
            $this->syncDescendantLevels($category);
        });

        $this->storeUploadedImages($category, $legacyImages);

        session()->flash('message', $this->categoryId === null
            ? 'Category created successfully.'
            : 'Category updated successfully.');
        $this->isOpen = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $this->authorizeAdmin();

        $category = Category::query()->findOrFail($id);

        $hasPivotProducts = Schema::hasTable('product_categories')
            && DB::table('product_categories')->where('category_id', $category->id)->exists();

        if ($category->products()->exists() || $category->children()->exists() || $hasPivotProducts) {
            session()->flash('error', 'This category cannot be deleted while it has products or subcategories.');

            return;
        }

        $images = array_filter([$category->banner, $category->icon, $category->cover_image]);
        $category->clearMediaCollection('banner');
        $category->clearMediaCollection('icon');
        $category->clearMediaCollection('cover');
        $category->delete();
        $this->deleteStoredFiles($images);

        session()->flash('message', 'Category deleted successfully.');
    }

    public function render()
    {
        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;

        return view('livewire.admin.categories', [
            'categories' => Category::query()
                ->with('parent')
                ->withCount(['products', 'children'])
                ->when($this->search !== '', fn (Builder $query) => $query->where('name', 'like', '%'.mb_substr($this->search, 0, 100).'%'))
                ->orderBy('order_level')
                ->orderBy('name')
                ->paginate($perPage),
            'parentOptions' => Category::query()->orderBy('name')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Categories']);
    }

    private function resetForm(): void
    {
        $this->reset([
            'categoryId',
            'name',
            'parentId',
            'orderLevel',
            'discount',
            'featured',
            'top',
            'digital',
            'bannerUpload',
            'iconUpload',
            'coverImageUpload',
            'currentBanner',
            'currentIcon',
            'currentCoverImage',
        ]);
        $this->orderLevel = '0';
        $this->discount = '0';
        $this->resetPickedMedia();
    }

    private function isDescendant(int $candidateId, int $categoryId): bool
    {
        $pendingIds = [$categoryId];
        $visitedIds = [$categoryId];

        while ($pendingIds !== []) {
            $children = Category::query()
                ->whereIn('parent_id', $pendingIds)
                ->pluck('id')
                ->map(static fn (int|string $id): int => (int) $id)
                ->all();

            if (in_array($candidateId, $children, true)) {
                return true;
            }

            $pendingIds = array_values(array_diff($children, $visitedIds));
            $visitedIds = [...$visitedIds, ...$pendingIds];
        }

        return false;
    }

    /**
     * @param  array<int, int>  $visitedIds
     */
    private function syncDescendantLevels(Category $category, array $visitedIds = []): void
    {
        if (in_array($category->id, $visitedIds, true)) {
            return;
        }

        $visitedIds[] = $category->id;
        $children = Category::query()->where('parent_id', $category->id)->get();

        foreach ($children as $child) {
            if (in_array($child->id, $visitedIds, true)) {
                continue;
            }

            $child->level = $category->level + 1;
            $child->save();
            $this->syncDescendantLevels($child, $visitedIds);
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId): string
    {
        $baseSlug = Str::slug($name) ?: 'category';
        $slug = $baseSlug;
        $suffix = 2;

        while (Category::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * Move fresh uploads into the media library, keeping the legacy column as
     * a fallback only until it is replaced.
     *
     * @param  array<string, string>  $legacyImages
     */
    private function storeUploadedImages(Category $category, array $legacyImages): void
    {
        $uploads = [
            'banner' => [$this->bannerUpload, 'banner'],
            'icon' => [$this->iconUpload, 'icon'],
            'cover_image' => [$this->coverImageUpload, 'cover'],
        ];

        foreach ($uploads as $field => [$upload, $collection]) {
            if ($upload !== null) {
                $category->addLibraryMedia($upload, $collection, $category->slug);
                // A fresh upload replaces an image that was only pointed at.
                $category->clearLibraryMediaReferences($collection);
            } elseif ($this->pickedMediaIdsFor($field) !== []) {
                $this->referencePickedMedia($category, $field, $collection);
            } else {
                continue;
            }

            $category->{$field} = null;
            $category->save();

            if (isset($legacyImages[$field])) {
                $this->deleteStoredFiles([$legacyImages[$field]]);
            }
        }
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deleteStoredFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (! Storage::disk('public')->delete($path)) {
                throw new RuntimeException('A category image could not be deleted.');
            }
        }
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->can('categories.manage'), 403);
    }
}
