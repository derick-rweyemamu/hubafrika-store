<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class CmsMenu extends Model
{
    /**
     * Area => the menu slugs that can serve it, most specific first.
     *
     * @var array<string, array<int, string>>
     */
    private const AREA_SLUGS = [
        'header' => ['header', 'website', 'website-menu'],
        'store' => ['store', 'store-menu', 'storefront'],
        'footer' => ['footer', 'footer-menu'],
        'topbar' => ['topbar', 'top-bar', 'utility'],
        'sidebar' => ['sidebar'],
        'mobile' => ['mobile'],
    ];

    /**
     * Area names used by the layouts, mapped to the canonical area name.
     *
     * @var array<string, string>
     */
    private const AREA_ALIASES = [
        'app' => 'header',
        'web' => 'header',
        'website' => 'header',
        'website-menu' => 'header',
        'frontend' => 'header',
        'storefront' => 'store',
        'store-menu' => 'store',
        'footer-menu' => 'footer',
        'top-bar' => 'topbar',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getTitleAttribute(?string $value): string
    {
        return $value ?: ($this->attributes['name'] ?? 'Menu');
    }

    /**
     * Menus only store a single "name" column, so the title alias must not be
     * written to the database as a separate attribute.
     */
    public function setTitleAttribute(?string $value): void
    {
        $this->attributes['name'] = $value;
    }

    public function getNameAttribute(?string $value): string
    {
        return $value ?: ($this->attributes['title'] ?? 'Menu');
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['name'] = $value;
    }

    public function items(): HasMany
    {
        return $this->hasMany(CmsMenuItem::class, 'menu_id')->orderBy('order')->orderBy('id');
    }

    public function rootItems(): HasMany
    {
        return $this->hasMany(CmsMenuItem::class, 'menu_id')
            ->whereNull('parent_id')
            ->orderBy('order')
            ->orderBy('id');
    }

    /**
     * Resolve the menu that renders a frontend area. Each layout names the area
     * it renders, so the chrome never depends on the visitor's login state.
     */
    public static function forArea(string $area): ?self
    {
        return self::pickForArea($area, static::query()->where('status', true)->get());
    }

    /**
     * Pick the menu for an area out of an already loaded set of menus, so a
     * page that renders several areas only queries the table once.
     *
     * @param  Collection<int|string, self>  $menus
     */
    public static function pickForArea(string $area, Collection $menus): ?self
    {
        $area = self::AREA_ALIASES[$area] ?? $area;

        foreach (self::AREA_SLUGS[$area] ?? [$area] as $slug) {
            $menu = $menus->firstWhere('slug', $slug);

            if ($menu instanceof self) {
                return $menu;
            }
        }

        // Menus created before the slugs were standardised only carry the area
        // in their location column.
        $menu = $menus->firstWhere('location', $area);

        return $menu instanceof self ? $menu : null;
    }

    /**
     * Build the structured tree array with mega menu column groupings for this menu.
     *
     * Navigation drops items that lead nowhere, but a footer column header is
     * legitimately link-less while its links are still being added, so those
     * renderers ask for the placeholders to be kept.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tree(bool $includeLinklessItems = false): array
    {
        if (! $this->relationLoaded('items')) {
            $this->load(['items' => fn ($q) => $q->with(['page', 'post'])->orderBy('order')->orderBy('id')]);
        }

        $childrenByParent = $this->items->groupBy(
            fn (CmsMenuItem $item): string => $item->parent_id === null ? 'root' : (string) $item->parent_id,
        );

        $buildTree = function (?int $parentId, array $ancestors = []) use (&$buildTree, $childrenByParent, $includeLinklessItems): array {
            $groupKey = $parentId === null ? 'root' : (string) $parentId;

            $tree = $childrenByParent->get($groupKey, collect())
                ->map(function (CmsMenuItem $item) use (&$buildTree, $ancestors, $includeLinklessItems): ?array {
                    if (in_array($item->id, $ancestors, true)) {
                        return null;
                    }

                    $children = $buildTree($item->id, [...$ancestors, $item->id]);
                    $href = $item->storefrontHref();

                    if (! $includeLinklessItems && $href === null && $children === []) {
                        return null;
                    }

                    $columnCount = (int) ($item->mega_menu_columns ?: 3);
                    $columnGroups = array_fill(0, max(1, $columnCount), []);
                    foreach ($children as $child) {
                        $colIdx = max(1, min($columnCount, (int) ($child['mega_menu_column'] ?? 1))) - 1;
                        $columnGroups[$colIdx][] = $child;
                    }

                    return [
                        'id' => $item->id,
                        'label' => $item->label,
                        'title' => $item->title,
                        'badge' => $item->badge,
                        'css_class' => $item->css_class,
                        'href' => $href,
                        'children' => $children,
                        'mega_menu' => (bool) $item->is_mega_menu,
                        'mega_menu_columns' => $columnCount,
                        'mega_menu_column' => $item->mega_menu_column === null ? null : (int) $item->mega_menu_column,
                        'mega_menu_column_groups' => $columnGroups,
                        'mega_menu_data' => $item->mega_menu_data,
                        'mega_menu_type' => $item->mega_menu_type ?: 'columns',
                        'mega_menu_image' => $item->megaMenuImageUrl(),
                        'mega_menu_image_position' => $item->mega_menu_image_position ?: 'left',
                        'mega_menu_image_title' => $item->mega_menu_image_title,
                        'mega_menu_image_link' => $item->mega_menu_image_link,
                    ];
                })
                ->filter()
                ->values()
                ->all();

            return $tree;
        };

        return $buildTree(null);
    }
}
