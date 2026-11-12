<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasUniqueSlug
{
    public static function bootHasUniqueSlug(): void
    {
        static::saving(function (self $model): void {
            $sourceColumn = $model->slugSourceColumn();
            $source = trim((string) ($model->{$sourceColumn} ?? ''));
            $currentSlug = trim((string) ($model->slug ?? ''));

            if ($source === '') {
                return;
            }

            if ($currentSlug === '' || ($model->isDirty($sourceColumn) && ! $model->isDirty('slug'))) {
                $model->slug = static::generateUniqueSlug(
                    $source,
                    $model->exists ? (int) $model->getKey() : null,
                );
            }
        });
    }

    /**
     * Build a unique URL slug directly from the given source string.
     */
    public static function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($source) ?: static::slugFallback();
        $slug = $baseSlug;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }

    protected function slugSourceColumn(): string
    {
        return 'name';
    }

    protected static function slugFallback(): string
    {
        return 'item';
    }
}
