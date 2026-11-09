<?php

namespace App\Models;

use App\Models\Concerns\HasLibraryMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaModel;

/**
 * Neutral owner for files that are uploaded straight from the Media screen,
 * so they do not have to pretend to belong to a product, page or user.
 */
class MediaShelf extends Model implements HasMedia
{
    use HasLibraryMedia;

    protected $fillable = ['name'];

    /**
     * The shelf is a singleton; uploads from the Media screen always land on
     * the first row so the library folder stays predictable.
     */
    public static function attach(): self
    {
        return self::query()->firstOrCreate(['name' => 'media-library']);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('library');
    }

    /**
     * @return array<int, MediaModel>
     */
    public function shelfMedia(): array
    {
        return $this->getMedia('library')->all();
    }
}
