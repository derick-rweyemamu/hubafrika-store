<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsCategory extends Model
{
    protected $table = 'cms_categories';

    protected $guarded = ['id'];

    public function posts(): HasMany
    {
        return $this->hasMany(CmsPost::class, 'category_id');
    }
}
