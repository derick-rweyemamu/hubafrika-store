<?php

namespace App\Livewire\Concerns;

use App\Models\Media;
use App\Models\MediaReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

/**
 * Lets an admin form reuse a picture that already lives in the media library.
 *
 * A pick is only remembered as an id until the form is saved, so cancelling
 * never changes anything. Saving stores a pointer to the library file, which
 * stays where it is: the same picture can be used by several records without
 * being duplicated on disk or in the library.
 */
trait PicksLibraryMedia
{
    public bool $mediaPickerOpen = false;

    /** The image field the picker is currently choosing for (logo, icon, ...). */
    public ?string $mediaPickerTarget = null;

    /** @var array<string, array<int, int>> */
    public array $pickedMediaIds = [];

    /** @var array<string, array<int, string>> */
    public array $pickedMediaUrls = [];

    public function openMediaPicker(string $target = 'default'): void
    {
        abort_unless(auth()->user()?->can('media.view'), 403);

        $this->mediaPickerTarget = $target;
        $this->mediaPickerOpen = true;
    }

    public function closeMediaPicker(): void
    {
        $this->mediaPickerOpen = false;
        $this->mediaPickerTarget = null;
    }

    #[On('media-picker-closed')]
    public function mediaPickerClosed(): void
    {
        $this->closeMediaPicker();
    }

    /**
     * Remember a file chosen in the picker until the form is saved.
     */
    #[On('media-picked')]
    public function storePickedMedia(int $mediaId): void
    {
        abort_unless(auth()->user()?->can('media.view'), 403);

        $target = $this->mediaPickerTarget;

        if ($target === null) {
            return;
        }

        $media = Media::query()->findOrFail($mediaId);

        abort_unless($media->isImage(), 422);
        abort_if($media->isPersonal(), 403);

        $ids = $this->pickedMediaIds[$target] ?? [];
        $urls = $this->pickedMediaUrls[$target] ?? [];

        if (! $this->mediaPickerAcceptsMultiple($target)) {
            $ids = [];
            $urls = [];
        }

        if (count($ids) < $this->mediaPickerMaxFiles($target) && ! in_array($media->getKey(), $ids, true)) {
            $ids[] = $media->getKey();
            $urls[] = $media->getUrl();
        }

        $this->pickedMediaIds[$target] = $ids;
        $this->pickedMediaUrls[$target] = $urls;

        if (! $this->mediaPickerAcceptsMultiple($target)) {
            $this->closeMediaPicker();
        }
    }

    public function clearPickedMedia(string $target): void
    {
        unset($this->pickedMediaIds[$target], $this->pickedMediaUrls[$target]);

        if ($this->mediaPickerTarget === $target) {
            $this->closeMediaPicker();
        }
    }

    /**
     * Drop a single picture from a field that holds several of them.
     */
    public function removePickedMedia(string $target, int $index): void
    {
        $ids = $this->pickedMediaIds[$target] ?? [];

        if (! array_key_exists($index, $ids)) {
            return;
        }

        $urls = $this->pickedMediaUrls[$target] ?? [];

        unset($ids[$index], $urls[$index]);

        if ($ids === []) {
            unset($this->pickedMediaIds[$target], $this->pickedMediaUrls[$target]);

            return;
        }

        $this->pickedMediaIds[$target] = array_values($ids);
        $this->pickedMediaUrls[$target] = array_values($urls);
    }

    /**
     * @return array<int, int>
     */
    protected function pickedMediaIdsFor(string $target): array
    {
        return $this->pickedMediaIds[$target] ?? [];
    }

    /**
     * The library rows currently picked for a field, in the order picked.
     *
     * @return Collection<int, Media>
     */
    protected function pickedMediaItems(string $target): Collection
    {
        $ids = $this->pickedMediaIdsFor($target);

        if ($ids === []) {
            return collect();
        }

        $media = Media::query()->whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)
            ->map(fn (int $id): ?Media => $media->get($id))
            ->filter()
            ->values();
    }

    /**
     * Whether the field collects several pictures, like a product gallery.
     */
    protected function mediaPickerAcceptsMultiple(string $target): bool
    {
        return false;
    }

    protected function mediaPickerMaxFiles(string $target): int
    {
        return $this->mediaPickerAcceptsMultiple($target) ? 5 : 1;
    }

    /**
     * Point the owner's field at every file picked for the target.
     *
     * Nothing is copied: the library keeps the only copy of each picture and
     * the record stores a pointer, so picking one picture for ten records
     * leaves the library with a single file.
     *
     * @param  (callable(Media): bool)|null  $accept  Skips files the field rejects.
     * @return array<int, MediaReference> The pointers that were stored.
     */
    protected function referencePickedMedia(
        Model $owner,
        string $target,
        string $collection,
        ?callable $accept = null,
    ): array {
        $ids = $this->pickedMediaIdsFor($target);

        if ($ids === []) {
            return [];
        }

        $sources = Media::query()->whereIn('id', $ids)->get()->keyBy('id');
        $singleFile = ! $this->mediaPickerAcceptsMultiple($target);
        $references = [];

        foreach ($ids as $id) {
            $source = $sources->get($id);

            if (! $source instanceof Media || ! $source->isImage() || $source->isPersonal()) {
                continue;
            }

            if ($accept !== null && ! $accept($source)) {
                continue;
            }

            $references[] = $singleFile
                ? $owner->referenceLibraryMedia((int) $source->getKey(), $collection)
                : $owner->addLibraryMediaReference((int) $source->getKey(), $collection);
        }

        return $references;
    }

    protected function resetPickedMedia(): void
    {
        $this->pickedMediaIds = [];
        $this->pickedMediaUrls = [];
        $this->mediaPickerOpen = false;
        $this->mediaPickerTarget = null;
    }
}
