<?php

namespace App\Livewire\Admin;

use App\Models\CmsPage;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CmsPages extends Component
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

    public ?int $editingPageId = null;

    public function updatedTitle(string $title): void
    {
        $this->slug = Str::slug($title);
    }

    public function startNewPage(): void
    {
        $this->reset(['title', 'slug', 'status', 'editingPageId']);
        $this->resetValidation();
    }

    public function editPage(int $pageId): void
    {
        $page = CmsPage::query()->findOrFail($pageId);
        $this->editingPageId = $page->id;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->status = $page->isPublished() ? 'published' : 'draft';
        $this->resetValidation();
    }

    public function savePage(): void
    {
        $this->slug = Str::slug($this->title);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('cms_pages', 'slug')->ignore($this->editingPageId),
                function (string $attribute, string $value, Closure $fail): void {
                    if (CmsPage::isReservedSlug($value)) {
                        $fail(__('The slug :slug is used by the store itself. Pick another one.', ['slug' => $value]));
                    }
                },
            ],
            'status' => ['required', 'in:draft,published'],
        ]);

        if ($this->editingPageId !== null) {
            $page = CmsPage::query()->findOrFail($this->editingPageId);
            $content = json_decode((string) $page->content, true);

            if (is_array($content)) {
                $content['published'] = $validated['status'] === 'published';
            } else {
                $content = [
                    'version' => 2,
                    'published' => $validated['status'] === 'published',
                    'project_data' => [],
                    'html' => is_string($page->content) ? $page->content : '',
                    'css' => '',
                ];
            }

            $page->update([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'content' => json_encode($content, JSON_THROW_ON_ERROR),
            ]);
            session()->flash('message', 'Page updated.');
        } else {
            CmsPage::query()->create([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'content' => json_encode([
                    'version' => 2,
                    'published' => $validated['status'] === 'published',
                    'project_data' => [],
                    'html' => '',
                    'css' => '',
                ], JSON_THROW_ON_ERROR),
                'created_by' => auth()->id(),
            ]);
            session()->flash('message', 'Page saved.');
        }

        $this->startNewPage();
        $this->redirectRoute('admin.cms.pages');
    }

    public function render()
    {
        return view('livewire.admin.cms-pages', [
            'pages' => CmsPage::query()->orderBy('title')->get(),
        ])->layout('components.layouts.admin', ['title' => 'CMS pages']);
    }
}
