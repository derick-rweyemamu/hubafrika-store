<?php

namespace App\Support\Media;

use App\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Stores each file in a readable, per-library folder:
 * `{prefix}/{library}/{sub folder}/{media id}/`.
 *
 * The media id is appended last so two uploads can keep the same real
 * filename without ever overwriting each other.
 */
class LibraryPathGenerator implements PathGenerator
{
    public function getPath(SpatieMedia $media): string
    {
        return $this->getBasePath($media).'/';
    }

    public function getPathForConversions(SpatieMedia $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(SpatieMedia $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    protected function getBasePath(SpatieMedia $media): string
    {
        return implode('/', array_filter([
            trim((string) config('media-library.prefix', ''), '/'),
            $media instanceof Media ? $media->library() : MediaLibrary::LIBRARY,
            $media instanceof Media ? $media->folder : null,
            $media->getKey(),
        ], fn (?string $segment): bool => $segment !== null && $segment !== ''));
    }
}
