<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Points a record at a picture that already lives in the media library.
 *
 * The file is never copied: the library row keeps owning it, so the same
 * picture can be used by a menu link, a page and a product at once without
 * storing it again.
 *
 * @property int $media_id
 * @property int $position
 */
class MediaReference extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'media_id' => 'integer',
        'position' => 'integer',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(): ?string
    {
        return $this->media?->getUrl();
    }
}
