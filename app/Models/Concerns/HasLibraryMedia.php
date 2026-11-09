<?php

namespace App\Models\Concerns;

use App\Models\MediaReference;
use App\Support\Media\MediaLibrary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaModel;

/**
 * Shared media-library integration: every owner registers its own collections,
 * lives in its own library folder, and can show a picture that already lives
 * in the library by pointing at it instead of storing it again.
 */
trait HasLibraryMedia
{
    use InteractsWithMedia;

    /**
     * Pointers this record stores, grouped by the field that shows them.
     *
     * @var array<string, Collection<int, MediaReference>>
     */
    protected array $libraryMediaReferenceCache = [];

    public static function bootHasLibraryMedia(): void
    {
        // A pointer is worthless once the record that shows it is gone.
        static::deleting(function (Model $model): void {
            $model->clearLibraryMediaReferences();
        });
    }

    public function library(): string
    {
        return MediaLibrary::forModel($this);
    }

    /**
     * Library pictures win over uploaded copies, so replacing a field with a
     * pick from the library takes effect as soon as the form is saved.
     */
    public function firstMediaUrl(string $collection): ?string
    {
        return $this->referencedMedia($collection)->first()?->getUrl()
            ?? $this->getFirstMedia($collection)?->getUrl();
    }

    /**
     * @return array<int, string>
     */
    public function mediaUrls(string $collection): array
    {
        return $this->referencedMedia($collection)
            ->map(fn (MediaModel $media): string => $media->getUrl())
            ->merge($this->getMedia($collection)->map(fn (MediaModel $media): string => $media->getUrl()))
            ->values()
            ->all();
    }

    /**
     * Attach a file, keeping the real upload name and the optional sub folder.
     *
     * @param  array<string, mixed>  $properties
     * @param  array<string, mixed>  $customProperties
     */
    public function addLibraryMedia(
        mixed $file,
        string $collection,
        ?string $folder = null,
        array $properties = [],
        array $customProperties = [],
    ): MediaModel {
        $properties = array_filter([
            'folder' => MediaLibrary::normalizeFolder($folder),
            'uploaded_by' => auth()->id(),
        ], fn (mixed $value): bool => $value !== null) + $properties;

        $fileAdder = $this->addMedia($file)->withProperties($properties);

        // A path points at a file that already belongs to the application, so
        // it is copied instead of being moved out of its current location.
        if (is_string($file)) {
            $fileAdder->preservingOriginal();
        }

        // The stored file name is sanitised for the disk; the exact upload name
        // is kept so screens can show it the way the user typed it.
        $originalName = $file instanceof UploadedFile
            ? $file->getClientOriginalName()
            : (is_string($file) ? basename($file) : null);

        if ($originalName !== null) {
            $customProperties += ['original_name' => $originalName];
        }

        if ($customProperties !== []) {
            $fileAdder->withCustomProperties($customProperties);
        }

        return $fileAdder->toMediaCollection($collection);
    }

    /**
     * Every pointer that targets a library picture, newest field last.
     */
    public function mediaReferences(): MorphMany
    {
        return $this->morphMany(MediaReference::class, 'mediable');
    }

    /**
     * The pointers a field shows, in the order they were picked.
     *
     * @return Collection<int, MediaReference>
     */
    public function mediaReferenceItems(string $collection): Collection
    {
        if (! isset($this->libraryMediaReferenceCache[$collection])) {
            $this->libraryMediaReferenceCache[$collection] = $this->exists
                ? $this->mediaReferences()
                    ->with('media')
                    ->where('collection', $collection)
                    ->orderBy('position')
                    ->orderBy('id')
                    ->get()
                : collect();
        }

        return $this->libraryMediaReferenceCache[$collection];
    }

    /**
     * The library pictures a field shows.
     *
     * @return Collection<int, MediaModel>
     */
    public function referencedMedia(string $collection): Collection
    {
        return $this->mediaReferenceItems($collection)
            ->map(fn (MediaReference $reference): ?MediaModel => $reference->media)
            ->filter()
            ->values();
    }

    /**
     * Show a library picture in a field that holds one image.
     *
     * The whole file stays in the library: only a pointer is stored, so using
     * the same picture on several records never duplicates it. A picture the
     * record used to own is removed, since the field now shows the library one.
     */
    public function referenceLibraryMedia(int $mediaId, string $collection): MediaReference
    {
        $source = MediaModel::query()->findOrFail($mediaId);

        // Pointing at an upload this record already owns must not delete it:
        // the file is the very thing being pointed at.
        $ownsSource = $source->model_type === $this->getMorphClass()
            && (int) $source->model_id === (int) $this->getKey()
            && $source->collection_name === $collection;

        if (! $ownsSource) {
            $this->clearMediaCollection($collection);
        }

        $this->clearLibraryMediaReferences($collection);

        return $this->addLibraryMediaReference($mediaId, $collection);
    }

    /**
     * Point at a library picture while leaving the field's other pictures
     * alone, which is what galleries need.
     */
    public function addLibraryMediaReference(int $mediaId, string $collection): MediaReference
    {
        $existing = $this->mediaReferences()
            ->where('collection', $collection)
            ->where('media_id', $mediaId)
            ->first();

        if ($existing instanceof MediaReference) {
            return $existing;
        }

        $reference = $this->mediaReferences()->create([
            'collection' => $collection,
            'media_id' => $mediaId,
            'position' => (int) $this->mediaReferences()->where('collection', $collection)->max('position') + 1,
        ]);

        unset($this->libraryMediaReferenceCache[$collection]);

        return $reference;
    }

    /**
     * Forget the pointers a field no longer shows.
     *
     * @param  array<int, int>  $keepIds
     */
    public function pruneLibraryMediaReferences(string $collection, array $keepIds): void
    {
        $this->mediaReferences()
            ->where('collection', $collection)
            ->whereNotIn('id', $keepIds)
            ->delete();

        unset($this->libraryMediaReferenceCache[$collection]);
    }

    /**
     * @param  string|null  $collection  Null clears every field.
     */
    public function clearLibraryMediaReferences(?string $collection = null): void
    {
        $this->mediaReferences()
            ->when($collection !== null, fn ($query) => $query->where('collection', $collection))
            ->delete();

        $this->libraryMediaReferenceCache = [];
    }

    /**
     * Drop both the uploaded pictures and the pointers a field shows.
     */
    public function clearLibraryMedia(string $collection): void
    {
        $this->clearMediaCollection($collection);
        $this->clearLibraryMediaReferences($collection);
    }

    /**
     * Resolve a path stored by the pre-media-library upload screens.
     */
    protected function legacyMediaUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
