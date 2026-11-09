<?php

namespace App\Livewire\Storefront;

use App\Models\CmsPost as Post;

class CmsPost extends CmsPage
{
    public function render()
    {
        $post = Post::query()->where('slug', $this->slug)->firstOrFail();

        return $this->renderContent($post);
    }
}
