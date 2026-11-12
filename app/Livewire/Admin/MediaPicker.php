<?php

namespace App\Livewire\Admin;

use App\Models\Media;
use App\Support\Media\MediaLibrary;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

/**
 * Modal that lists the pictures already stored in the media library so an
 * admin form can reuse one instead of uploading it a second time.
 *
 * The pick is reported to the hosting component as a `media-picked` event;
 * this component never writes to the database itself.
 */
class MediaPicker extends Component
{
    public string $library = 'all';

    public string $search = '';

    public int $mediaPage = 1;

    public int $perPage = 12;

    /**
     * Pictures already handed to the hosting form during this visit, so a
     * field that takes several pictures shows what has been chosen so far.
     *
     * @var array<int, int>
     */
    public array $selectedIds = [];

    public function updatingSearch(): void
    {
        $this->mediaPage = 1;
    }

    public function selectLibrary(string $library): void
    {
        if ($library !== 'all' && (! MediaLibrary::isLibrary($library) || MediaLibrary::isPersonal($library))) {
            return;
        }

        $this->library = $library;
        $this->mediaPage = 1;
    }

    public function previousPage(): void
    {
        $this->mediaPage = max(1, $this->mediaPage - 1);
    }

    public function nextPage(int $lastPage): void
    {
        $this->mediaPage = min(max(1, $lastPage), $this->mediaPage + 1);
    }

    public function close(): void
    {
        $this->dispatch('media-picker-closed');
    }

    public function select(int $id): void
    {
        abort_unless(auth()->user()?->can('media.view'), 403);

        $media = Media::query()->findOrFail($id);

        abort_unless($media->isImage(), 422);
        abort_if($media->isPersonal(), 403);

        if (! in_array($media->getKey(), $this->selectedIds, true)) {
            $this->selectedIds[] = $media->getKey();
        }

        $this->dispatch('media-picked', mediaId: $media->getKey());
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('media.view'), 403);

        $perPage = in_array($this->perPage, [12, 24, 48], true) ? $this->perPage : 12;

        $media = $this->query()
            ->when($this->search !== '', fn (Builder $query) => $query->search($this->search))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'mediaPage', $this->mediaPage);

        return view('livewire.admin.media-picker', [
            'media' => $media,
            'libraries' => collect(MediaLibrary::labels())
                ->except([MediaLibrary::USERS])
                ->all(),
        ]);
    }

    private function query(): Builder
    {
        return Media::query()->contentImages($this->library);
    }
}
