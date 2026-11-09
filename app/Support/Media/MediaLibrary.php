<?php

namespace App\Support\Media;

use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\CmsMenuItem;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\MediaShelf;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Central registry that decides which sub-folder (library) a media file lives in.
 *
 * Every library maps to a real directory below the media disk, so uploads made
 * by users can never be mixed with catalogue or CMS uploads.
 */
class MediaLibrary
{
    public const USERS = 'users';

    public const PRODUCTS = 'products';

    public const BRANDS = 'brands';

    public const CATEGORIES = 'categories';

    public const PAGES = 'pages';

    public const POSTS = 'posts';

    public const MENUS = 'menus';

    public const BRANDING = 'branding';

    public const LIBRARY = 'library';

    /**
     * Model class => library folder.
     *
     * @var array<class-string<Model>, string>
     */
    public const MODEL_LIBRARIES = [
        User::class => self::USERS,
        Product::class => self::PRODUCTS,
        Brand::class => self::BRANDS,
        Category::class => self::CATEGORIES,
        CmsPage::class => self::PAGES,
        CmsPost::class => self::POSTS,
        CmsMenuItem::class => self::MENUS,
        BusinessSetting::class => self::BRANDING,
        MediaShelf::class => self::LIBRARY,
    ];

    /**
     * Human readable labels, also used to build the Media screen tabs.
     *
     * @var array<string, string>
     */
    public const LABELS = [
        self::PRODUCTS => 'Products',
        self::BRANDS => 'Brands',
        self::CATEGORIES => 'Categories',
        self::PAGES => 'CMS pages',
        self::POSTS => 'CMS posts',
        self::MENUS => 'Navigation',
        self::BRANDING => 'Branding',
        self::USERS => 'Users',
        self::LIBRARY => 'Uploaded here',
    ];

    /**
     * Libraries that only ever hold account media. They are shown on their own
     * tab and are excluded from the shared content libraries.
     *
     * @var array<int, string>
     */
    public const PERSONAL_LIBRARIES = [self::USERS];

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return self::LABELS;
    }

    /**
     * @return array<int, string>
     */
    public static function contentLibraries(): array
    {
        return array_values(array_diff(array_keys(self::LABELS), self::PERSONAL_LIBRARIES));
    }

    public static function isLibrary(string $library): bool
    {
        return array_key_exists($library, self::LABELS);
    }

    public static function isPersonal(string $library): bool
    {
        return in_array($library, self::PERSONAL_LIBRARIES, true);
    }

    public static function label(string $library): string
    {
        return self::LABELS[$library] ?? ucfirst($library);
    }

    /**
     * Resolve the library for an owner model class or morph alias.
     */
    public static function forModel(Model|string $model): string
    {
        $class = $model instanceof Model ? $model::class : $model;

        return self::MODEL_LIBRARIES[$class] ?? self::LIBRARY;
    }

    /**
     * @return array<int, class-string<Model>>
     */
    public static function modelsFor(string $library): array
    {
        return array_keys(self::MODEL_LIBRARIES, $library, true);
    }

    /**
     * Normalise a user supplied sub folder into a safe path segment.
     */
    public static function normalizeFolder(?string $folder): ?string
    {
        $folder = trim((string) $folder);

        if ($folder === '') {
            return null;
        }

        $segments = array_filter(array_map(function (string $segment): string {
            $segment = preg_replace('/[^A-Za-z0-9 _-]+/', '', $segment) ?? '';

            return trim(preg_replace('/[\s_]+/', '-', $segment) ?? '', '-');
        }, preg_split('#[/\\\\]+#', $folder) ?: []));

        if ($segments === []) {
            return null;
        }

        return mb_substr(implode('/', array_slice($segments, 0, 3)), 0, 150);
    }

    /**
     * Which library is allowed to own the given collection name?
     */
    public static function collectionBelongsTo(string $collection, string $library): bool
    {
        return in_array($collection, self::collectionsFor($library), true);
    }

    /**
     * Collection names grouped per library, used for validation and display.
     *
     * @return array<int, string>
     */
    public static function collectionsFor(string $library): array
    {
        return match ($library) {
            self::PRODUCTS => ['gallery', 'thumbnail'],
            self::BRANDS => ['logo'],
            self::CATEGORIES => ['icon', 'cover', 'banner'],
            self::PAGES, self::POSTS => ['blocks', 'cover'],
            self::MENUS => ['image', 'mega-menu'],
            self::BRANDING => ['branding'],
            self::USERS => ['avatar'],
            default => ['library'],
        };
    }
}
