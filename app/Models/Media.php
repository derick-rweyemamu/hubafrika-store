<?php

namespace App\Models;

use App\Support\Media\MediaLibrary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * @property string|null $folder
 * @property int|null $uploaded_by
 */
class Media extends BaseMedia
{
    protected $casts = [
        'manipulations' => 'array',
        'custom_properties' => 'array',
        'generated_conversions' => 'array',
        'responsive_images' => 'array',
        'size' => 'integer',
        'uploaded_by' => 'integer',
    ];

    /**
     * The account that introduced the file, when known.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * The folder this file belongs to (products, users, pages, ...).
     */
    public function library(): string
    {
        $library = (string) $this->getCustomProperty('library', '');

        if (MediaLibrary::isLibrary($library)) {
            return $library;
        }

        $class = Relation::getMorphedModel((string) $this->model_type) ?? (string) $this->model_type;

        return MediaLibrary::forModel($class);
    }

    public function libraryLabel(): string
    {
        return MediaLibrary::label($this->library());
    }

    /**
     * Real file name as uploaded, before the disk safe name is applied.
     */
    public function originalFileName(): string
    {
        return (string) ($this->getCustomProperty('original_name') ?: $this->file_name);
    }

    public function isPersonal(): bool
    {
        return MediaLibrary::isPersonal($this->library());
    }

    /**
     * Whether the file can be used anywhere an image is expected.
     */
    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function scopeForLibrary(Builder $query, string $library): Builder
    {
        $classes = MediaLibrary::modelsFor($library);

        return $query->where(function (Builder $query) use ($library, $classes): void {
            $query->where('custom_properties->library', $library);

            if ($classes !== []) {
                $query->orWhereIn('model_type', $classes);
            }
        });
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = '%'.mb_substr(trim($term), 0, 100).'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('name', 'like', $term)
                ->orWhere('file_name', 'like', $term)
                ->orWhere('folder', 'like', $term);
        });
    }

    /**
     * Images stored across shared content libraries, never user avatars.
     */
    public function scopeContentImages(Builder $query, ?string $library = null): Builder
    {
        $query->where('mime_type', 'like', 'image/%');

        if ($library !== null && $library !== '' && $library !== 'all') {
            if (! MediaLibrary::isLibrary($library) || MediaLibrary::isPersonal($library)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->forLibrary($library);
        }

        // Account pictures are never offered for reuse on content.
        return $query
            ->whereNotIn('model_type', MediaLibrary::modelsFor(MediaLibrary::USERS))
            ->where(function (Builder $query): void {
                $query->whereNull('custom_properties->library')
                    ->orWhere('custom_properties->library', '!=', MediaLibrary::USERS);
            });
    }

    /**
     * Format a media record for the page builder asset manager and media modal.
     *
     * @return array{id: int, src: string, name: string, original_name: string, library: string, library_label: string, folder: ?string, type: string}
     */
    public function toBuilderAsset(): array
    {
        $library = $this->library();

        return [
            'id' => (int) $this->getKey(),
            'src' => $this->getUrl(),
            'name' => $this->libraryLabel().' / '.$this->originalFileName(),
            'original_name' => $this->originalFileName(),
            'library' => $library,
            'library_label' => MediaLibrary::label($library),
            'folder' => $this->folder,
            'type' => 'image',
        ];
    }
}
