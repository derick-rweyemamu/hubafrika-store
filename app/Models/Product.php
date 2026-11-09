<?php

namespace App\Models;

use App\Models\Concerns\HasLibraryMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

class Product extends Model implements HasMedia
{
    use HasLibraryMedia;

    /**
     * The price the customer actually pays, as a SQL expression, so filters
     * and sorting agree with the figures printed on the storefront cards.
     */
    public const EFFECTIVE_PRICE_SQL = "(case when discount_type = 'percent' then unit_price * (1 - discount / 100) else unit_price - discount end)";

    protected $guarded = ['id'];

    protected $casts = [
        'attributes' => 'array',        // JSON string to Array
        'choice_options' => 'array',
        'colors' => 'array',
        'variations' => 'array',
        'photos' => 'array',
        'published' => 'boolean',
        'approved' => 'boolean',
        'featured' => 'boolean',
        'draft' => 'boolean',
        'cash_on_delivery' => 'boolean',
        'unit_price' => 'float',
        'discount' => 'float',
        'tax' => 'float',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', 1)->where('approved', 1);
    }

    /**
     * Restrict the rows to a range of prices the customer actually pays, i.e.
     * the unit price less the admin discount, so the range matches the figures
     * printed on the storefront cards.
     */
    public function scopePriceBetween(Builder $query, ?float $min, ?float $max): Builder
    {
        if ($min === null && $max === null) {
            return $query;
        }

        // The bounds are inlined as numeric literals on purpose: bound
        // parameters reach SQLite as text, and SQLite ranks every number below
        // text, so a bound comparison against this expression always passes.
        return $query
            ->when($min !== null, fn (Builder $query): Builder => $query->whereRaw(self::EFFECTIVE_PRICE_SQL.' >= '.(float) $min))
            ->when($max !== null, fn (Builder $query): Builder => $query->whereRaw(self::EFFECTIVE_PRICE_SQL.' <= '.(float) $max));
    }

    /**
     * Order the rows by the price the customer pays, not the list price.
     */
    public function scopeOrderByPrice(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderByRaw(self::EFFECTIVE_PRICE_SQL.' '.$direction);
    }

    /**
     * Unit price after the discount saved by the admin. A percent discount is
     * a share of the unit price, an amount discount a flat subtraction; either
     * way the price never drops below zero.
     */
    public function discountedUnitPrice(): float
    {
        $unitPrice = (float) $this->unit_price;
        $discount = $this->discount_type === 'percent'
            ? $unitPrice * (float) $this->discount / 100
            : (float) $this->discount;

        return round($unitPrice - min($unitPrice, max(0, $discount)), 2);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('thumbnail')->singleFile();
    }

    public function thumbnailUrl(): ?string
    {
        return $this->firstMediaUrl('thumbnail')
            ?? $this->firstMediaUrl('gallery')
            ?? $this->legacyMediaUrl($this->thumbnail_img);
    }

    /**
     * @return array<int, string>
     */
    public function galleryUrls(): array
    {
        $media = $this->mediaUrls('gallery');

        if ($media !== []) {
            return $media;
        }

        return array_values(array_filter(array_map(
            fn (mixed $path): ?string => $this->legacyMediaUrl(is_string($path) ? $path : null),
            (array) $this->photos,
        )));
    }
}
