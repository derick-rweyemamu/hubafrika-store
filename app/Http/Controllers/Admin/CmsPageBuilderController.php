<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\Media;
use App\Support\Media\MediaLibrary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CmsPageBuilderController extends Controller
{
    /**
     * How many library pictures the asset manager offers on top of the page's
     * own uploads, newest first.
     */
    private const LIBRARY_ASSET_LIMIT = 200;

    public function edit(CmsPage $page): View
    {
        return $this->builderView($page, 'page', 'admin.cms.pages', 'pages.show');
    }

    public function editPost(CmsPost $post): View
    {
        return $this->builderView($post, 'post', 'admin.cms.posts', 'posts.show');
    }

    public function update(Request $request, CmsPage $page): JsonResponse
    {
        return $this->saveBuilderContent($request, $page, 'pages.show');
    }

    public function updatePost(Request $request, CmsPost $post): JsonResponse
    {
        return $this->saveBuilderContent($request, $post, 'posts.show');
    }

    private function builderView(CmsPage|CmsPost $content, string $entityName, string $adminRoute, string $previewRoute): View
    {
        abort_unless(auth()->user()?->can('cms.manage'), 403);

        $data = $content->builderData();
        $projectData = $data['project_data'] ?? null;
        $initialHtml = $data['html'] ?? $this->legacyHtml($data['blocks']);

        if ($initialHtml === '') {
            $initialHtml = $this->starterHtml($content->title);
        }

        return view('admin.cms.builder', [
            'page' => $content,
            'projectData' => $projectData,
            'initialHtml' => $initialHtml,
            'published' => $data['published'],
            'entityName' => $entityName,
            'backUrl' => route($adminRoute),
            'updateUrl' => route($adminRoute.'.builder.update', $content),
            'previewUrl' => route($previewRoute, $content->slug),
            'uploadUrl' => route('admin.media.upload'),
            'mediaLibraryUrl' => route('admin.cms.media-library'),
            'mediaLibraries' => $this->contentLibraryLabels(),
            'canUploadMedia' => (bool) auth()->user()?->can('media.manage'),
            'assetUrls' => $this->builderAssets($content),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    /**
     * Serve searchable, folder-filterable Media Library images to the visual
     * builder modal so pages and posts can reuse any stored image cleanly.
     */
    public function mediaLibrary(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('cms.manage'), 403);

        $library = trim((string) $request->query('library', 'all'));
        $search = trim((string) $request->query('search', ''));
        $perPage = min(48, max(12, (int) $request->query('per_page', 24)));

        $paginator = Media::query()
            ->contentImages($library)
            ->when($search !== '', fn (Builder $query): Builder => $query->search($search))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'data' => collect($paginator->items())
                ->map(fn (Media $media): array => $media->toBuilderAsset())
                ->values()
                ->all(),
            'libraries' => $this->contentLibraryLabels(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Pictures offered in the builder's asset manager.
     *
     * The page's own uploads come first, then everything already stored in the
     * library, so a picture can be placed by URL instead of being uploaded
     * again for every page that shows it.
     *
     * @return array<int, array<string, mixed>>
     */
    private function builderAssets(CmsPage|CmsPost $content): array
    {
        $own = $content->getMedia('blocks')
            ->map(fn (Media $media): array => $media->toBuilderAsset());

        $library = Media::query()
            ->contentImages()
            ->orderByDesc('id')
            ->limit(self::LIBRARY_ASSET_LIMIT)
            ->get()
            ->map(fn (Media $media): array => $media->toBuilderAsset());

        return $own->concat($library)
            ->unique('src')
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function contentLibraryLabels(): array
    {
        return collect(MediaLibrary::labels())
            ->except([MediaLibrary::USERS])
            ->all();
    }

    private function saveBuilderContent(Request $request, CmsPage|CmsPost $content, string $previewRoute): JsonResponse
    {
        abort_unless(auth()->user()?->can('cms.manage'), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique($content->getTable(), 'slug')->ignore($content->getKey()),
            ],
            'published' => ['required', 'boolean'],
            'project_data' => ['required', 'array'],
            'html' => ['required', 'string', 'max:500000'],
            'css' => ['nullable', 'string', 'max:200000'],
        ]);

        $builderContent = json_encode([
            'version' => 2,
            'published' => (bool) $validated['published'],
            'project_data' => $validated['project_data'],
            'html' => $validated['html'],
            'css' => $validated['css'] ?? '',
        ], JSON_THROW_ON_ERROR);

        if (strlen($builderContent) > 1_000_000) {
            throw ValidationException::withMessages([
                'project_data' => 'The design is too large to save.',
            ]);
        }

        $content->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'content' => $builderContent,
        ]);

        return response()->json([
            'success' => true,
            'message' => $validated['published']
                ? ucfirst($content instanceof CmsPost ? 'post' : 'page').' published.'
                : 'Draft saved.',
            'url' => route($previewRoute, $content->slug),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function legacyHtml(array $blocks): string
    {
        $safeBlocks = collect($blocks)
            ->map(fn (array $block): ?array => CmsPage::safeBlock($block))
            ->filter()
            ->values();

        if ($safeBlocks->isEmpty()) {
            return '';
        }

        $height = $safeBlocks->max(fn (array $block): int => (int) $block['y'] + (int) $block['height']) ?? 0;
        $html = '<main style="position:relative;min-height:'.($height + 40).'px">';

        foreach ($safeBlocks as $block) {
            $style = sprintf(
                'position:absolute;left:%d%%;top:%dpx;width:%d%%;min-height:%dpx;',
                $block['x'],
                $block['y'],
                $block['width'],
                $block['height'],
            );

            $html .= match ($block['type']) {
                'heading' => '<h2 style="'.$style.'">'.e($block['text']).'</h2>',
                'text' => '<p style="'.$style.'">'.nl2br(e($block['text'])).'</p>',
                'image' => '<img style="'.$style.'" src="'.e($block['image_url']).'" alt="'.e($block['alt']).'">',
                'button' => '<a style="'.$style.'" href="'.e($block['url']).'">'.e($block['label']).'</a>',
                'product_grid' => '<section style="'.$style.'"><h2>'.e($block['text']).'</h2><div data-hubafrika-products="true" style="min-height:120px"></div></section>',
            };
        }

        return $html.'</main>';
    }

    private function starterHtml(string $title): string
    {
        return '<section style="margin:0 auto;max-width:1120px;padding:96px 24px;text-align:center">'
            .'<p style="color:#b45309;font-size:14px;font-weight:700;letter-spacing:.12em;text-transform:uppercase">Hubafrika</p>'
            .'<h1 style="margin:16px 0;font-size:clamp(36px,6vw,64px);font-weight:800;line-height:1.05">'.e($title).'</h1>'
            .'<p style="margin:0 auto;max-width:640px;color:#475569;font-size:18px;line-height:1.7">Drag a section from the Blocks panel to start building your page.</p>'
            .'</section>';
    }
}
