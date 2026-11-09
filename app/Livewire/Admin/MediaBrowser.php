<?php

namespace App\Livewire\Admin;

use App\Models\Media;
use App\Models\MediaShelf;
use App\Support\Media\MediaLibrary;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MediaBrowser extends Component
{
    use WithFileUploads, WithPagination;

    public string $library = 'all';

    public string $search = '';

    public int $perPage = 24;

    public bool $isOpen = false;

    public ?TemporaryUploadedFile $upload = null;

    public string $uploadLibrary = MediaLibrary::LIBRARY;

    public string $uploadFolder = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingLibrary(): void
    {
        $this->resetPage();
    }

    public function selectLibrary(string $library): void
    {
        if ($library !== 'all' && ! MediaLibrary::isLibrary($library)) {
            return;
        }

        $this->library = $library;
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $this->upload = null;
        $this->uploadFolder = '';
        $this->uploadLibrary = $this->library === 'all'
            ? MediaLibrary::LIBRARY
            : $this->library;
        $this->isOpen = true;
    }

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate([
            'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'uploadLibrary' => ['required', 'string', 'in:'.implode(',', array_keys(MediaLibrary::labels()))],
            'uploadFolder' => ['nullable', 'string', 'max:100'],
        ]);

        MediaShelf::attach()->addLibraryMedia(
            $this->upload,
            'library',
            $validated['uploadFolder'] ?: null,
            [],
            ['library' => $validated['uploadLibrary']],
        );

        session()->flash('message', 'File uploaded successfully.');
        $this->isOpen = false;
        $this->upload = null;
        $this->uploadFolder = '';
    }

    public function delete(int $id): void
    {
        $this->authorizeManage();

        Media::query()->findOrFail($id)->delete();

        session()->flash('message', 'File deleted successfully.');
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('media.view'), 403);

        $perPage = in_array($this->perPage, [12, 24, 48], true) ? $this->perPage : 24;

        return view('livewire.admin.media-browser', [
            'media' => $this->query()
                ->when($this->search !== '', fn (Builder $query) => $query->search($this->search))
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate($perPage),
            'libraries' => MediaLibrary::labels(),
        ])->layout('components.layouts.admin', ['title' => 'Media']);
    }

    private function query(): Builder
    {
        $query = Media::query()->with('uploader');

        // Account pictures never show up together with catalogue or CMS media.
        if ($this->library === 'all') {
            return $query->whereNotIn('model_type', MediaLibrary::modelsFor(MediaLibrary::USERS));
        }

        return $query->forLibrary($this->library);
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()?->can('media.manage'), 403);
    }
}
