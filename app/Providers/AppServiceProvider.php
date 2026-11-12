<?php

namespace App\Providers;

use App\Models\BusinessSetting;
use App\Models\CmsMenu;
use App\Models\User;
use App\Support\Storefront\Palette;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Flag-based administrators and members of the super role hold every
        // permission, so they never need to be granted abilities one by one.
        Gate::before(fn (User $user): ?bool => $user->isAdmin() ? true : null);

        ViewFacade::composer('components.layouts.admin', function (View $view): void {
            $settings = BusinessSetting::query()
                ->whereIn('type', [
                    'appearance_primary_color',
                    'appearance_font_family',
                    'backend_palette_active',
                    ...array_map(fn (int $number): string => "backend_palette_{$number}", range(1, 4)),
                    'font_backend_body',
                    'site_name',
                    'business_logo',
                    'business_logo_dark',
                    'favicon',
                ])
                ->pluck('value', 'type');

            $legacyPrimary = $settings->get('appearance_primary_color', '#4F46E5');
            $palette = Palette::resolve($settings, 'backend', [
                'primary' => preg_match('/\A#[0-9A-Fa-f]{6}\z/', (string) $legacyPrimary) === 1
                    ? $legacyPrimary
                    : '#4F46E5',
                'secondary' => '#64748B',
                'accent' => '#F59E0B',
                'bg' => '#F9FAFB',
                'surface' => '#FFFFFF',
                'text' => '#111827',
                'bg_dark' => '#030712',
                'surface_dark' => '#111827',
                'text_dark' => '#F3F4F6',
            ]);
            $fontFamily = $settings->get('appearance_font_family', 'Inter');
            $fontFamilies = [
                'Inter' => 'Inter, sans-serif',
                'Arial' => 'Arial, sans-serif',
                'Georgia' => 'Georgia, serif',
                'System' => 'system-ui, sans-serif',
                'Poppins' => 'Poppins, sans-serif',
                'Playfair Display' => '"Playfair Display", serif',
                'Plus Jakarta Sans' => '"Plus Jakarta Sans", sans-serif',
            ];

            $view->with('appearanceSettings', [
                'palette' => $palette,
                'primaryColor' => $palette['primary'],
                'fontFamily' => $fontFamilies[$settings->get('font_backend_body', $fontFamily)]
                    ?? $fontFamilies[$fontFamily]
                    ?? $fontFamilies['Inter'],
            ]);
            $view->with('businessAssets', $this->businessAssets($settings));
        });

        ViewFacade::composer([
            'layouts.app',
            'components.layouts.app',
            'layouts.frontend',
            'components.layouts.frontend',
            'components.layouts.storefront',
            'layouts.guest',
            'components.layouts.guest',
            'components.layouts.navigation',
            'layouts.navigation',
            'welcome',
        ], function (View $view): void {
            $settings = BusinessSetting::query()
                ->whereIn('type', [
                    'frontend_palette_active',
                    ...array_map(fn (int $number): string => "frontend_palette_{$number}", range(1, 4)),
                    'font_frontend_primary_header',
                    'font_frontend_sub_header',
                    'font_frontend_accent',
                    'font_frontend_body',
                    'font_size_base',
                    'font_size_heading',
                    'font_size_sub',
                    'font_size_accent',
                    'font_size_small',
                    'site_name',
                    'site_motto',
                    'footer_about',
                    'contact_email',
                    'contact_phone',
                    'whatsapp_number',
                    'social_facebook',
                    'social_instagram',
                    'social_x',
                    'social_youtube',
                    'social_linkedin',
                    'social_tiktok',
                    'business_logo',
                    'business_logo_dark',
                    'favicon',
                ])
                ->pluck('value', 'type');

            $fontFamilies = [
                'Inter' => 'Inter, sans-serif',
                'Arial' => 'Arial, sans-serif',
                'Georgia' => 'Georgia, serif',
                'System' => 'system-ui, sans-serif',
                'Poppins' => 'Poppins, sans-serif',
                'Playfair Display' => '"Playfair Display", serif',
                'Plus Jakarta Sans' => '"Plus Jakarta Sans", sans-serif',
            ];

            $headerFont = $fontFamilies[$settings->get('font_frontend_primary_header', 'Inter')] ?? 'Inter, sans-serif';
            $bodyFont = $fontFamilies[$settings->get('font_frontend_body', 'Inter')] ?? 'Inter, sans-serif';
            $accentFont = $fontFamilies[$settings->get('font_frontend_accent', 'Inter')] ?? 'Inter, sans-serif';

            $view->with('storefrontPalette', Palette::resolve($settings, 'frontend', [
                'primary' => '#F59E0B',
                'secondary' => '#0F172A',
                'accent' => '#10B981',
                'bg' => '#FFFBEB',
                'surface' => '#FFFFFF',
                'text' => '#1F2937',
                'bg_dark' => '#111827',
                'surface_dark' => '#1F2937',
                'text_dark' => '#F9FAFB',
            ]));

            $view->with('storefrontFonts', [
                'header' => $headerFont,
                'body' => $bodyFont,
                'accent' => $accentFont,
                'fontSizeBase' => $settings->get('font_size_base', '1rem'),
                'fontSizeHeading' => $settings->get('font_size_heading', '1.5rem'),
                'fontSizeSub' => $settings->get('font_size_sub', '1.125rem'),
                'fontSizeSmall' => $settings->get('font_size_small', '0.875rem'),
            ]);

            $view->with('businessAssets', $this->businessAssets($settings));
        });

        ViewFacade::composer([
            'components.layouts.storefront',
            'components.layouts.frontend',
            'layouts.frontend',
            'components.layouts.app',
            'layouts.app',
            'components.layouts.navigation',
            'layouts.navigation',
            'welcome',
        ], function (View $view): void {
            $menus = CmsMenu::query()
                ->where('status', true)
                ->with([
                    'items' => fn ($query) => $query
                        ->with(['page', 'post'])
                        ->orderBy('order')
                        ->orderBy('id'),
                ])
                ->get();

            // Every area keeps the menu the admin assigned to it: the header
            // area feeds the website layouts, the store area feeds the shop
            // and account pages, and the footer area feeds every frontend
            // footer. The footer also keeps column headers that have no links
            // yet, so a half-built column still shows on the storefront.
            $navigation = [];

            foreach (['header', 'store', 'footer', 'topbar'] as $area) {
                $menu = CmsMenu::pickForArea($area, $menus);

                $navigation[$area] = [
                    'menu' => $menu,
                    'tree' => $menu?->tree($area === 'footer') ?? [],
                ];
            }

            $view->with('storefrontMenus', $navigation);
        });
    }

    /**
     * @param  Collection<string, string>  $settings
     * @return array{site_name: ?string, site_motto: ?string, footer_about: ?string, logo: ?string, logoDark: ?string, favicon: ?string}
     */
    private function businessAssets(Collection $settings): array
    {
        // Branding files are media-library uploads; a legacy path is used as
        // the fallback for assets uploaded before the media library existed.
        $branding = BusinessSetting::query()
            ->whereIn('type', ['business_logo', 'business_logo_dark', 'favicon'])
            ->with('media')
            ->get()
            ->keyBy('type');

        $email = trim((string) $settings->get('contact_email', ''));
        $validEmail = $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : null;
        $phone = trim((string) $settings->get('contact_phone', ''));
        $phoneHref = $this->normalizePhoneHref($phone);

        return [
            'site_name' => $settings->get('site_name'),
            'site_motto' => $settings->get('site_motto'),
            'footer_about' => $settings->get('footer_about'),
            'logo' => $branding->get('business_logo')?->imageUrl(),
            'logoDark' => $branding->get('business_logo_dark')?->imageUrl(),
            'favicon' => $branding->get('favicon')?->imageUrl(),
            'contact' => [
                'email' => $validEmail,
                'emailHref' => $validEmail !== null ? 'mailto:'.$validEmail : null,
                'phone' => $phoneHref !== null ? $phone : null,
                'phoneHref' => $phoneHref,
                'whatsappHref' => $this->normalizeWhatsappHref($settings->get('whatsapp_number')),
            ],
            'socials' => $this->storefrontSocialLinks($settings),
        ];
    }

    private function normalizePhoneHref(mixed $raw): ?string
    {
        $phone = trim((string) ($raw ?? ''));
        if ($phone === '') {
            return null;
        }

        $hasLeadingPlus = str_starts_with($phone, '+');
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (strlen($digits) < 5) {
            return null;
        }

        return 'tel:'.($hasLeadingPlus ? '+' : '').$digits;
    }

    private function normalizeWhatsappHref(mixed $raw): ?string
    {
        $value = trim((string) ($raw ?? ''));
        if ($value === '') {
            return null;
        }

        if (preg_match('/\Ahttps?:\/\//i', $value) === 1) {
            $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
            if (filter_var($value, FILTER_VALIDATE_URL) !== false && in_array($scheme, ['http', 'https'], true)) {
                return $value;
            }

            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($digits) < 7 || strlen($digits) > 15) {
            return null;
        }

        return 'https://wa.me/'.$digits;
    }

    /**
     * Build ordered external social links from configured business settings.
     *
     * @param  Collection<string, mixed>  $settings
     * @return array<int, array{label: string, href: string, icon: string, external: bool, social: bool}>
     */
    private function storefrontSocialLinks(Collection $settings): array
    {
        $networks = [
            'social_facebook' => ['label' => 'Facebook', 'icon' => 'facebook'],
            'social_instagram' => ['label' => 'Instagram', 'icon' => 'instagram'],
            'social_x' => ['label' => 'X (Twitter)', 'icon' => 'x'],
            'social_youtube' => ['label' => 'YouTube', 'icon' => 'youtube'],
            'social_linkedin' => ['label' => 'LinkedIn', 'icon' => 'linkedin'],
            'social_tiktok' => ['label' => 'TikTok', 'icon' => 'tiktok'],
        ];

        $links = [];
        foreach ($networks as $type => $meta) {
            $url = trim((string) ($settings->get($type) ?? ''));
            if ($url === '') {
                continue;
            }

            $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
            if (filter_var($url, FILTER_VALIDATE_URL) === false || ! in_array($scheme, ['http', 'https'], true)) {
                continue;
            }

            $links[] = [
                'label' => $meta['label'],
                'href' => $url,
                'icon' => $meta['icon'],
                'external' => true,
                'social' => true,
            ];
        }

        return $links;
    }
}
