<?php

namespace App\Models;

use App\Models\Concerns\HasLibraryMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

class Category extends Model implements HasMedia
{
    use HasLibraryMedia;

    protected $guarded = ['id'];

    protected $casts = [
        'featured' => 'boolean',
        'top' => 'boolean',
        'digital' => 'boolean',
        'discount' => 'float',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('icon')->singleFile();
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('banner')->singleFile();
    }

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function iconUrl(): ?string
    {
        return $this->firstMediaUrl('icon') ?? $this->legacyMediaUrl($this->icon);
    }

    public function coverImageUrl(): ?string
    {
        return $this->firstMediaUrl('cover') ?? $this->legacyMediaUrl($this->cover_image);
    }

    public function bannerUrl(): ?string
    {
        return $this->firstMediaUrl('banner') ?? $this->legacyMediaUrl($this->banner);
    }
}
