<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\CmsMenuItem;
use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Throwable;

/**
 * Import files that were uploaded before the media library existed.
 *
 * The original files stay on disk unless --prune is passed, and each record
 * keeps a note of what was imported so the command can be re-run safely.
 */
class BackfillMediaCommand extends Command
{
    protected $signature = 'media:backfill
        {--dry-run : Report what would be imported without writing anything}
        {--prune : Delete the legacy file after it has been imported}';

    protected $description = 'Import existing brand, category, product, menu, branding and avatar images into the media library';

    private int $imported = 0;

    private int $skipped = 0;

    private int $missing = 0;

    public function handle(): int
    {
        $this->importBrands();
        $this->importCategories();
        $this->importProducts();
        $this->importMenuItems();
        $this->importBranding();
        $this->importAvatars();

        $this->newLine();
        $this->info("Imported: {$this->imported} | Already present: {$this->skipped} | Missing files: {$this->missing}");

        return self::SUCCESS;
    }

    private function importBrands(): void
    {
        Brand::query()->with('media')->chunkById(100, function ($brands): void {
            foreach ($brands as $brand) {
                $this->importFile($brand, 'logo', $brand->logo, $brand->slug);
            }
        });
    }

    private function importCategories(): void
    {
        Category::query()->with('media')->chunkById(100, function ($categories): void {
            foreach ($categories as $category) {
                $this->importFile($category, 'icon', $category->icon, $category->slug);
                $this->importFile($category, 'cover', $category->cover_image, $category->slug);
                $this->importFile($category, 'banner', $category->banner, $category->slug);
            }
        });
    }

    private function importProducts(): void
    {
        Product::query()->with('media')->chunkById(100, function ($products): void {
            foreach ($products as $product) {
                $photos = array_values(array_unique(array_filter(
                    (array) ($product->photos ?? []),
                    fn (mixed $path): bool => is_string($path) && trim($path) !== '',
                )));

                if ($photos === [] && is_string($product->thumbnail_img)) {
                    $photos = [$product->thumbnail_img];
                }

                foreach ($photos as $path) {
                    $this->importFile($product, 'gallery', $path, $product->slug);
                }
            }
        });
    }

    private function importMenuItems(): void
    {
        CmsMenuItem::query()->with('media')->chunkById(100, function ($items): void {
            foreach ($items as $item) {
                $path = $item->image ?: $item->mega_menu_image;

                if ($path === null) {
                    continue;
                }

                $this->importFile($item, 'image', $path, 'menu-'.$item->menu_id);
            }
        });
    }

    private function importBranding(): void
    {
        BusinessSetting::query()
            ->whereIn('type', ['business_logo', 'business_logo_dark', 'favicon'])
            ->with('media')
            ->get()
            ->each(function (BusinessSetting $setting): void {
                $this->importFile($setting, 'branding', $setting->value, $setting->type);
            });
    }

    private function importAvatars(): void
    {
        User::query()->with('media')->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                $this->importFile($user, 'avatar', $user->avatar, 'user-'.$user->id);
            }
        });
    }

    private function importFile(Model&HasMedia $model, string $collection, ?string $path, ?string $folder): void
    {
        $path = trim((string) $path);

        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $label = class_basename($model).'#'.$model->getKey().' '.$collection;

        // A single-file collection that already holds media has been imported
        // before; gallery style collections are matched file by file below.
        if (
            $model->getMediaCollection($collection)?->singleFile === true
            && $model->getMedia($collection)->isNotEmpty()
        ) {
            $this->skipped++;

            return;
        }

        /** @var Media|null $duplicate */
        $duplicate = Media::query()
            ->where('model_type', $model->getMorphClass())
            ->where('model_id', $model->getKey())
            ->where('collection_name', $collection)
            ->where('file_name', basename($path))
            ->first();

        if ($duplicate !== null) {
            $this->skipped++;

            return;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $this->missing++;
            $this->line("  <fg=yellow>missing</> {$label}: {$path}");

            return;
        }

        if ($this->option('dry-run')) {
            $this->imported++;
            $this->line("  <fg=cyan>would import</> {$label}: {$path}");

            return;
        }

        try {
            $model->addLibraryMedia($disk->path($path), $collection, $folder);
        } catch (Throwable $exception) {
            $this->missing++;
            $this->line("  <fg=red>failed</> {$label}: {$path} ({$exception->getMessage()})");

            return;
        }

        $this->imported++;
        $this->line("  <fg=green>imported</> {$label}: {$path}");

        if ($this->option('prune')) {
            $disk->delete($path);
        }
    }
}
