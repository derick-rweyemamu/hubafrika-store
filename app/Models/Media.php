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
}
