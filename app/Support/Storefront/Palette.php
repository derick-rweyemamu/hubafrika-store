<?php

namespace App\Support\Storefront;

use App\Models\BusinessSetting;
use Illuminate\Support\Collection;

/**
 * Resolves the colour palette the admin saved for an area of the site. The
 * storefront layouts print it as CSS custom properties; content that renders
 * in its own document (the CMS page frame) asks for the colours here.
 */
class Palette
{
    /**
     * Defaults used when the storefront has no saved palette.
     *
     * @var array<string, string>
     */
    public const FRONTEND = [
        'primary' => '#F59E0B',
        'secondary' => '#0F172A',
        'accent' => '#10B981',
        'bg' => '#FFFBEB',
        'surface' => '#FFFFFF',
        'text' => '#1F2937',
        'bg_dark' => '#111827',
        'surface_dark' => '#1F2937',
        'text_dark' => '#F9FAFB',
    ];

    /**
     * The palette saved for an area, with unset or malformed colours replaced
     * by the defaults so a saved design can never print an invalid colour.
     *
     * @param  Collection<string, string>  $settings
     * @param  array<string, string>  $fallback
     * @return array<string, string>
     */
    public static function resolve(Collection $settings, string $prefix, array $fallback): array
    {
        $activeNumber = $settings->get("{$prefix}_palette_active", '1');

        if (! preg_match('/\A[1-4]\z/', (string) $activeNumber)) {
            $activeNumber = '1';
        }

        $palette = json_decode((string) $settings->get("{$prefix}_palette_{$activeNumber}"), true);

        if (! is_array($palette)) {
            return $fallback;
        }

        foreach ($fallback as $field => $default) {
            if (! isset($palette[$field]) || preg_match('/\A#[0-9A-Fa-f]{6}\z/', (string) $palette[$field]) !== 1) {
                $palette[$field] = $default;
            }
        }

        return array_intersect_key($palette, $fallback);
    }

    /**
     * The primary storefront colour, i.e. the one the shop prints on its
     * buttons and links.
     */
    public static function primary(): string
    {
        return self::frontend()['primary'];
    }

    /**
     * The whole active storefront palette.
     *
     * @return array<string, string>
     */
    public static function frontend(): array
    {
        $settings = BusinessSetting::query()
            ->whereIn('type', [
                'frontend_palette_active',
                ...array_map(fn (int $number): string => "frontend_palette_{$number}", range(1, 4)),
            ])
            ->pluck('value', 'type');

        return self::resolve($settings, 'frontend', self::FRONTEND);
    }

    /**
     * The CSS font stack the admin picked for the storefront body text.
     */
    public static function bodyFont(): string
    {
        $families = [
            'Inter' => 'Inter, sans-serif',
            'Arial' => 'Arial, sans-serif',
            'Georgia' => 'Georgia, serif',
            'System' => 'system-ui, sans-serif',
            'Poppins' => 'Poppins, sans-serif',
            'Playfair Display' => '"Playfair Display", serif',
            'Plus Jakarta Sans' => '"Plus Jakarta Sans", sans-serif',
        ];

        $chosen = (string) BusinessSetting::query()->where('type', 'font_frontend_body')->value('value');

        return $families[$chosen] ?? $families['Inter'];
    }
}
