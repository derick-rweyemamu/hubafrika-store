<?php

namespace App\Models;

use App\Models\Concerns\HasLibraryMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

class CmsPost extends Model implements HasMedia
{
    use HasLibraryMedia;

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

        return [
            'published' => is_string($this->content) && trim($this->content) !== '',
            'blocks' => [],
            'html' => is_string($this->content) ? $this->content : '',
            'css' => '',
            'project_data' => [],
        ];
    }

    public function isPublished(): bool
    {
        return $this->builderData()['published'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CmsCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(CmsMenuItem::class, 'post_id');
    }
}
