<?php

namespace App\Livewire\Admin;

use App\Models\CmsCategory;
use App\Models\CmsPost;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CmsPosts extends Component
{
    /**
     * Actions can be invoked on their own, without passing through the route
     * middleware, so the permission is re-checked on every request.
     */
    public function boot(): void
    {
        abort_unless(auth()->user()?->can('cms.manage'), 403);
    }

    public string $title = '';

    public string $slug = '';

    public string $status = 'draft';

    public ?int $editingPostId = null;

    public ?int $categoryId = null;

    public string $categoryName = '';

    public string $categorySlug = '';

    public ?int $editingCategoryId = null;

    public function updatedTitle(string $title): void
    {
        $this->slug = Str::slug($title);
    }

    public function updatedCategoryName(string $categoryName): void
    {
        $this->categorySlug = Str::slug($categoryName);
    }

    public function createCategory(): void
    {
        $this->saveCategory();
    }

    public function editCategory(int $categoryId): void
    {
        $category = CmsCategory::query()->findOrFail($categoryId);
        $this->editingCategoryId = $category->id;
        $this->categoryName = $category->name;
        $this->categorySlug = $category->slug;
        $this->resetValidation();
    }

    public function cancelCategoryEdit(): void
    {
        $this->reset(['categoryName', 'categorySlug', 'editingCategoryId']);
        $this->resetValidation();
    }

    public function saveCategory(): void
    {
        $this->categorySlug = Str::slug($this->categoryName);
        $validated = $this->validate([
            'categoryName' => ['required', 'string', 'max:255'],
            'categorySlug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('cms_categories', 'slug')->ignore($this->editingCategoryId),
            ],
        ]);

        $categoryData = [
            'name' => $validated['categoryName'],
            'slug' => $validated['categorySlug'],
        ];
        $isEditing = $this->editingCategoryId !== null;

        if ($isEditing) {
            $category = CmsCategory::query()->findOrFail($this->editingCategoryId);
            $category->update($categoryData);
        } else {
            $category = CmsCategory::query()->create($categoryData);
        }

        $this->categoryId = $category->id;
        $this->cancelCategoryEdit();
        session()->flash('message', $isEditing ? 'Category updated.' : 'Category created.');
    }

    public function deleteCategory(int $categoryId): void
    {
        CmsCategory::query()->findOrFail($categoryId)->delete();

        if ($this->categoryId === $categoryId) {
            $this->categoryId = null;
        }

        if ($this->editingCategoryId === $categoryId) {
            $this->cancelCategoryEdit();
        }

        session()->flash('message', 'Category deleted.');
    }

    public function savePost(): void
    {
        $this->slug = Str::slug($this->title);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('cms_posts', 'slug')->ignore($this->editingPostId),
            ],
            'categoryId' => ['nullable', 'integer', 'exists:cms_categories,id'],
            'status' => ['required', 'in:draft,published'],
        ]);

        if ($this->editingPostId !== null) {
            $post = CmsPost::query()->findOrFail($this->editingPostId);
            $content = json_decode((string) $post->content, true);

            if (is_array($content)) {
                $content['published'] = $validated['status'] === 'published';
            } else {
                $content = [
                    'version' => 2,
                    'published' => $validated['status'] === 'published',
                    'project_data' => [],
                    'html' => is_string($post->content) ? $post->content : '',
                    'css' => '',
                ];
            }

            $post->update([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'category_id' => $validated['categoryId'],
                'content' => json_encode($content, JSON_THROW_ON_ERROR),
            ]);
            session()->flash('message', 'Post updated.');
        } else {
            CmsPost::query()->create([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'category_id' => $validated['categoryId'],
                'content' => json_encode([
                    'version' => 2,
                    'published' => $validated['status'] === 'published',
                    'project_data' => [],
                    'html' => '',
                    'css' => '',
                ], JSON_THROW_ON_ERROR),
                'created_by' => auth()->id(),
            ]);
            session()->flash('message', 'Post saved.');
        }

        $this->cancelPostEdit();
        $this->redirectRoute('admin.cms.posts');
    }

    public function editPost(int $postId): void
    {
        $post = CmsPost::query()->findOrFail($postId);
        $this->editingPostId = $post->id;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->status = $post->isPublished() ? 'published' : 'draft';
        $this->categoryId = $post->category_id;
        $this->resetValidation();
    }

    public function cancelPostEdit(): void
    {
        $this->reset(['title', 'slug', 'status', 'categoryId', 'editingPostId']);
        $this->resetValidation();
    }

    public function assignCategory(int $postId, string $categoryId = ''): void
    {
        $validated = Validator::make(
            ['categoryId' => $categoryId === '' ? null : $categoryId],
            ['categoryId' => ['nullable', 'integer', Rule::exists('cms_categories', 'id')]],
        )->validate();

        CmsPost::query()->findOrFail($postId)->update(['category_id' => $validated['categoryId']]);
        session()->flash('message', 'Post category updated.');
    }

    public function render()
    {
        return view('livewire.admin.cms-posts', [
            'posts' => CmsPost::query()->with('category')->orderBy('title')->get(),
            'categories' => CmsCategory::query()->orderBy('name')->get(),
        ])->layout('components.layouts.admin', ['title' => 'CMS posts']);
    }
}
