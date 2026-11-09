<?php

namespace App\Models;

use App\Models\Concerns\HasLibraryMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

class Brand extends Model implements HasMedia
{
    use HasLibraryMedia;

    protected $guarded = ['id'];

    protected $casts = [
        'top' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function logoUrl(): ?string
    {
        return $this->firstMediaUrl('logo') ?? $this->legacyMediaUrl($this->logo);
    }
}
