<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'name',
        'source',
        'is_active',
        'unsubscribed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'unsubscribed_at' => 'datetime',
        ];
    }

    /**
     * Scope query to only active subscribers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to filter by search keyword.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $term = '%'.trim($search).'%';

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('email', 'like', $term)
                ->orWhere('name', 'like', $term);
        });
    }
}
