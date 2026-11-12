<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Legal CMS pages anchored at the bottom right of the storefront footer.
     *
     * @var array<int, array{slugs: array<int, string>, label: string}>
     */
    private const LEGAL_PAGES = [
        ['slugs' => ['term-of-use', 'terms-of-use'], 'label' => 'Term of use'],
        ['slugs' => ['privacy-policy'], 'label' => 'Privacy Policy'],
        ['slugs' => ['cookie-policy'], 'label' => 'Cookie Policy'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('cms_menus')->count() === 0 || DB::table('cms_pages')->count() === 0) {
            return;
        }

        $footerMenuId = DB::table('cms_menus')
            ->whereIn('slug', ['footer', 'footer-menu'])
            ->value('id')
            ?? DB::table('cms_menus')->where('location', 'footer')->value('id');

        if ($footerMenuId === null) {
            return;
        }

        $maxOrder = (int) (DB::table('cms_menu_items')
            ->where('menu_id', $footerMenuId)
            ->max('order') ?? 0);
        $nextOrder = $maxOrder + 1;
        $now = now();

        foreach (self::LEGAL_PAGES as $definition) {
            $page = DB::table('cms_pages')
                ->whereIn('slug', $definition['slugs'])
                ->first(['id', 'slug', 'title']);

            if ($page === null) {
                continue;
            }

            $paths = collect($definition['slugs'])
                ->flatMap(fn (string $slug): array => ["/{$slug}", "/pages/{$slug}"])
                ->all();

            $exists = DB::table('cms_menu_items')
                ->where('menu_id', $footerMenuId)
                ->where(function ($query) use ($page, $paths): void {
                    $query->where('page_id', $page->id)
                        ->orWhereIn('url', $paths);
                })
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('cms_menu_items')->insert([
                'menu_id' => $footerMenuId,
                'page_id' => $page->id,
                'parent_id' => null,
                'title' => $definition['label'],
                'label' => $definition['label'],
                'type' => 'page',
                'url' => null,
                'target' => '_self',
                'order' => $nextOrder++,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $footerMenuId = DB::table('cms_menus')
            ->whereIn('slug', ['footer', 'footer-menu'])
            ->value('id')
            ?? DB::table('cms_menus')->where('location', 'footer')->value('id');

        if ($footerMenuId === null) {
            return;
        }

        $pageIds = DB::table('cms_pages')
            ->whereIn('slug', ['term-of-use', 'terms-of-use', 'privacy-policy', 'cookie-policy'])
            ->pluck('id')
            ->all();

        if ($pageIds === []) {
            return;
        }

        DB::table('cms_menu_items')
            ->where('menu_id', $footerMenuId)
            ->whereNull('parent_id')
            ->where('type', 'page')
            ->whereIn('page_id', $pageIds)
            ->delete();
    }
};
