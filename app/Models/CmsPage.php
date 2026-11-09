<?php

namespace App\Models;

use App\Models\Concerns\HasLibraryMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

class CmsPage extends Model implements HasMedia
{
    use HasLibraryMedia;

    /**
     * First path segments the application already answers on. Pages live at
     * the site root now, so a page may not claim one of these slugs.
     *
     * @var array<int, string>
     */
    public const RESERVED_SLUGS = [
        'admin', 'api', 'build', 'cart', 'checkout', 'confirm-password', 'forgot-password',
        'livewire', 'login', 'logout', 'my-orders', 'my-payments', 'newsletter', 'pages',
        'payments', 'post', 'profile', 'register', 'reset-password', 'shop', 'storage', 'up',
        'verify-email',
    ];

    public static function isReservedSlug(string $slug): bool
    {
        return in_array(strtolower($slug), self::RESERVED_SLUGS, true);
    }

    protected $guarded = ['id'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('blocks');
    }

    public function coverUrl(): ?string
    {
        return $this->firstMediaUrl('cover');
    }

    /**
     * @return array{published: bool, blocks: array<int, array<string, mixed>>, html?: string, css?: string, project_data?: array<string, mixed>}
     */
    public function builderData(): array
    {
        $content = json_decode((string) $this->content, true);

        if (
            is_array($content)
            && ($content['version'] ?? null) === 2
            && is_array($content['project_data'] ?? null)
            && is_string($content['html'] ?? null)
        ) {
            return [
                'published' => ($content['published'] ?? false) === true,
                'blocks' => [],
                'html' => $content['html'],
                'css' => is_string($content['css'] ?? null) ? $content['css'] : '',
                'project_data' => $content['project_data'],
            ];
        }

        if (
            is_array($content)
            && ($content['version'] ?? null) === 1
            && is_array($content['blocks'] ?? null)
        ) {
            return [
                'published' => ($content['published'] ?? false) === true,
                'blocks' => collect($content['blocks'])
                    ->map(fn (mixed $block): ?array => is_array($block) ? self::safeBlock($block) : null)
                    ->filter()
                    ->values()
                    ->all(),
            ];
        }

        return [
            'published' => is_string($this->content) && trim($this->content) !== '',
            'blocks' => is_string($this->content) && trim($this->content) !== ''
                ? [[
                    'type' => 'text',
                    'text' => $this->content,
                    'x' => 4,
                    'y' => 4,
                    'width' => 92,
                    'height' => 180,
                ]]
                : [],
        ];
    }

    public function isPublished(): bool
    {
        return $this->builderData()['published'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(CmsMenuItem::class, 'page_id');
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>|null
     */
    public static function safeBlock(array $block): ?array
    {
        $type = $block['type'] ?? null;

        if (! in_array($type, ['heading', 'text', 'image', 'button', 'product_grid'], true)) {
            return null;
        }

        $x = max(0, min(95, self::number($block['x'] ?? null, 0)));
        $width = max(5, min(100 - $x, self::number($block['width'] ?? null, 90)));
        $safe = [
            'type' => $type,
            'x' => $x,
            'y' => max(0, min(10000, self::number($block['y'] ?? null, 0))),
            'width' => $width,
            'height' => max(60, min(1500, self::number($block['height'] ?? null, 140))),
        ];

        if (in_array($type, ['heading', 'text', 'product_grid'], true)) {
            $safe['text'] = is_string($block['text'] ?? null) ? $block['text'] : '';
        } elseif ($type === 'image') {
            $imageUrl = is_string($block['image_url'] ?? null) ? $block['image_url'] : '';

            $safe['image_url'] = CmsMenuItem::isSafeUrl($imageUrl) ? $imageUrl : '';
            $safe['alt'] = is_string($block['alt'] ?? null) ? $block['alt'] : '';
        } else {
            $url = is_string($block['url'] ?? null) ? $block['url'] : '';

            $safe['url'] = CmsMenuItem::isSafeUrl($url) ? $url : '';
            $safe['label'] = is_string($block['label'] ?? null) ? $block['label'] : '';
        }

        return $safe;
    }

    private static function number(mixed $value, int $default): int
    {
        return is_numeric($value) ? (int) $value : $default;
    }
}
