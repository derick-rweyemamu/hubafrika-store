<?php

namespace App\Models;

use App\Models\Concerns\HasLibraryMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;

class CmsMenuItem extends Model implements HasMedia
{
    use HasLibraryMedia;

    protected $guarded = ['id'];

    protected $casts = [
        'is_mega_menu' => 'boolean',
        'mega_menu_data' => 'array',
        'status' => 'boolean',
        'order' => 'integer',
        'mega_menu_columns' => 'integer',
        'mega_column' => 'integer',
        'mega_menu_column' => 'integer',
    ];

    public const MEGA_TYPES = [
        'columns' => 'Standard columns',
        'image_menu' => 'Columns with banner image',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(CmsMenu::class, 'menu_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'page_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(CmsPost::class, 'post_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order')->orderBy('id');
    }

    public function getTitleAttribute(?string $value): string
    {
        return $value ?: ($this->attributes['label'] ?? '');
    }

    public function setTitleAttribute(?string $value): void
    {
        $this->attributes['title'] = $value;
        $this->attributes['label'] = $value;
    }

    public function getLabelAttribute(?string $value): string
    {
        return $value ?: ($this->attributes['title'] ?? '');
    }

    public function setLabelAttribute(?string $value): void
    {
        $this->attributes['label'] = $value;
        $this->attributes['title'] = $value;
    }

    public function getBadgeTextAttribute(?string $value): ?string
    {
        return $value ?: ($this->attributes['badge'] ?? null);
    }

    public function setBadgeTextAttribute(?string $value): void
    {
        $this->attributes['badge_text'] = $value;
        $this->attributes['badge'] = $value;
    }

    public function getMegaColumnAttribute(?int $value): int
    {
        return (int) ($value ?: ($this->attributes['mega_menu_column'] ?? 1));
    }

    public function setMegaColumnAttribute(?int $value): void
    {
        $this->attributes['mega_column'] = $value;
        $this->attributes['mega_menu_column'] = $value;
    }

    public function getResolvedUrlAttribute(): string
    {
        if ($this->type === 'page' && $this->page) {
            return route('pages.show', $this->page->slug);
        }
        if ($this->type === 'post' && $this->post) {
            return route('posts.show', $this->post->slug);
        }
        if (! empty($this->url)) {
            return $this->url;
        }
        if ($this->page_id && $this->page) {
            return route('pages.show', $this->page->slug);
        }
        if ($this->post_id && $this->post) {
            return route('posts.show', $this->post->slug);
        }

        return '#';
    }

    public function storefrontHref(): ?string
    {
        if ($this->page_id !== null) {
            return $this->page?->isPublished() ? route('pages.show', $this->page->slug) : null;
        }

        if ($this->post_id !== null) {
            return $this->post?->isPublished() ? route('posts.show', $this->post->slug) : null;
        }

        return self::isSafeUrl((string) $this->url) ? $this->url : null;
    }

    public function megaMenuImageUrl(): ?string
    {
        $media = $this->firstMediaUrl('mega-menu') ?? $this->firstMediaUrl('image');

        if ($media !== null) {
            return $media;
        }

        $img = $this->image ?: $this->mega_menu_image;
        if (empty($img)) {
            return null;
        }

        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '/')) {
            return $img;
        }

        return Storage::disk('public')->url($img);
    }

    public function imageUrl(): ?string
    {
        return $this->megaMenuImageUrl();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('mega-menu')->singleFile();
    }

    /**
     * Storefront pages that are built in code rather than created in the CMS,
     * keyed by path so a menu link can point at them without typing the URL.
     *
     * @return array<string, string>
     */
    public static function sitePages(): array
    {
        return [
            '/' => __('Home'),
            '/shop' => __('Shop'),
            '/cart' => __('Cart'),
            '/checkout' => __('Checkout'),
            '/my-orders' => __('My orders'),
            '/my-payments' => __('My payments'),
            '/profile' => __('My account'),
        ];
    }

    public static function isSafeUrl(string $url): bool
    {
        if (
            str_starts_with($url, '/')
            && ! str_starts_with($url, '//')
            && ! str_contains($url, '\\')
            && preg_match('/[\s\x00-\x1F]/', $url) !== 1
        ) {
            return true;
        }

        // Contact links used by the storefront footer: a phone number for
        // tel: and a plain address for mailto:, nothing else.
        if (preg_match('/\Atel:\+?[0-9][0-9\s().-]{4,24}\z/i', $url) === 1) {
            return true;
        }

        if (preg_match('/\Amailto:([^?@\s]+@[^?@\s]+\.[a-z]{2,})\z/i', $url, $matches) === 1) {
            return filter_var($matches[1], FILTER_VALIDATE_EMAIL) !== false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
