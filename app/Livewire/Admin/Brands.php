<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PicksLibraryMedia;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use RuntimeException;

class Brands extends Component
{
    use PicksLibraryMedia, WithFileUploads, WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $isOpen = false;

    public ?int $brandId = null;

    public string $name = '';

    public bool $top = false;

    public ?TemporaryUploadedFile $logoUpload = null;

    public ?string $currentLogo = null;

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

        $brand = Brand::query()->findOrFail($id);
        $this->brandId = $brand->id;
        $this->name = $brand->name;
        $this->top = $brand->top;
        $this->currentLogo = $brand->logoUrl();
        $this->logoUpload = null;
        $this->resetPickedMedia();
        $this->isOpen = true;
    }

    public function save(): void
    {
        $this->authorizeAdmin();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:50'],
            'top' => ['boolean'],
            'logoUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $brand = $this->brandId === null
            ? new Brand
            : Brand::query()->findOrFail($this->brandId);
        $legacyLogo = $brand->logo;

        $brand->fill([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $brand->exists ? $brand->id : null),
            'top' => $validated['top'],
        ]);
        $brand->save();

        if ($this->logoUpload !== null || $this->pickedMediaIdsFor('logo') !== []) {
            if ($this->logoUpload !== null) {
                $brand->addLibraryMedia($this->logoUpload, 'logo', $brand->slug);
                // A fresh upload replaces a logo that was only pointed at.
                $brand->clearLibraryMediaReferences('logo');
            } else {
                $this->referencePickedMedia($brand, 'logo', 'logo');
            }

            $brand->logo = null;
            $brand->save();

            if ($legacyLogo !== null) {
                $this->deleteStoredFiles([$legacyLogo]);
            }
        }

        session()->flash('message', $this->brandId === null
            ? 'Brand created successfully.'
            : 'Brand updated successfully.');
        $this->isOpen = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $this->authorizeAdmin();

        $brand = Brand::query()->findOrFail($id);
        $logo = $brand->logo;

        $brand->clearMediaCollection('logo');

        DB::transaction(function () use ($brand): void {
            Product::query()->where('brand_id', $brand->id)->update(['brand_id' => null]);
            $brand->delete();
        });

        if ($logo !== null) {
            $this->deleteStoredFiles([$logo]);
        }

        session()->flash('message', 'Brand deleted successfully.');
    }

    public function render()
    {
        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;

        return view('livewire.admin.brands', [
            'brands' => Brand::query()
                ->withCount('products')
                ->when($this->search !== '', fn (Builder $query) => $query->where('name', 'like', '%'.mb_substr($this->search, 0, 100).'%'))
                ->orderBy('name')
                ->paginate($perPage),
        ])->layout('components.layouts.admin', ['title' => 'Brands']);
    }

    private function resetForm(): void
    {
        $this->reset(['brandId', 'name', 'top', 'logoUpload', 'currentLogo']);
        $this->resetPickedMedia();
        $this->top = false;
    }

    private function uniqueSlug(string $name, ?int $ignoreId): string
    {
        $baseSlug = Str::slug($name) ?: 'brand';
        $slug = $baseSlug;
        $suffix = 2;

        while (Brand::query()
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
                throw new RuntimeException('A brand logo could not be deleted.');
            }
        }
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->can('brands.manage'), 403);
    }
}
