<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\MediaShelf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\HasMedia;

/**
 * Accepts uploads coming from the page builder asset manager and returns the
 * payload GrapesJS expects: {"data": [{"src": "..."}]}.
 */
class MediaUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('media.manage'), 403);

        $validated = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'owner_type' => ['nullable', 'in:page,post,shelf'],
            'owner_id' => ['nullable', 'integer'],
        ]);

        $owner = $this->resolveOwner(
            $validated['owner_type'] ?? null,
            isset($validated['owner_id']) ? (int) $validated['owner_id'] : null,
        );

        if (! $owner instanceof HasMedia) {
            abort(422, 'The selected owner cannot hold media.');
        }

        [$collection, $folder] = match (true) {
            $owner instanceof CmsPage, $owner instanceof CmsPost => ['blocks', $owner->slug],
            default => ['library', 'uploads'],
        };

        $media = $owner->addLibraryMedia($request->file('file'), $collection, $folder);

        return response()->json([
            'data' => [[
                'src' => $media->getUrl(),
                'name' => $media->file_name,
                'type' => 'image',
                'id' => $media->getKey(),
            ]],
        ]);
    }

    private function resolveOwner(?string $type, ?int $id): ?Model
    {
        return match ($type) {
            'page' => $id === null ? null : CmsPage::query()->find($id),
            'post' => $id === null ? null : CmsPost::query()->find($id),
            default => MediaShelf::attach(),
        };
    }
}
