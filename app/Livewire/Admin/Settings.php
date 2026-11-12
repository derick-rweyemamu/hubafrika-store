<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PicksLibraryMedia;
use App\Models\BusinessSetting;
use App\Support\RichText;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Settings extends Component
{
    use PicksLibraryMedia, WithFileUploads;

    /** The image field key used by the media picker on this screen. */
    private const BRANDING_PICK = 'branding';

    private const TABS = [
        'general' => [
            'label' => 'General',
            'description' => 'Store identity, contact channels, social links, and availability.',
            'groups' => ['Basic settings', 'Contact & social', 'Brand assets'],
        ],
        'shipping-checkout' => [
            'label' => 'Shipping & payment',
            'description' => 'Delivery costs and the payment methods customers can choose.',
            'groups' => ['Shipping', 'Payment methods', 'Vodacom Lipa'],
        ],
        'admin-design' => [
            'label' => 'Admin design',
            'description' => 'Dashboard palettes, typography, and appearance.',
            'groups' => ['Backend palettes', 'Backend fonts', 'Admin appearance'],
        ],
        'storefront-design' => [
            'label' => 'Storefront design',
            'description' => 'Customer-facing palettes, typography, and sizing.',
            'groups' => ['Frontend palettes', 'Frontend fonts', 'Frontend font sizes'],
        ],
    ];

    private const FONT_FAMILIES = [
        'Inter',
        'Poppins',
        'Playfair Display',
        'Plus Jakarta Sans',
        'Arial',
        'Georgia',
        'System',
    ];

    private const PALETTE_FIELDS = [
        'primary',
        'secondary',
        'accent',
        'bg',
        'surface',
        'text',
        'bg_dark',
        'surface_dark',
        'text_dark',
    ];

    /**
     * Short explanations shown under the value on the commerce cards.
     *
     * @var array<string, string>
     */
    private const COMMERCE_HINTS = [
        'flat_rate_shipping_cost' => 'Added once per order at checkout.',
        'free_shipping' => 'Skips the flat rate for every order.',
        'cash_on_delivery' => 'Customers pay the courier on delivery.',
        'lipa_enabled' => 'Offers Lipa as a checkout option.',
        'lipa_requires_verification' => 'Holds the order until the payment is verified.',
        'lipa_number' => 'Merchant number customers pay into.',
        'lipa_business_name' => 'Shown in the payment prompt.',
        'lipa_instructions' => 'Shown to the customer after checkout.',
        'lipa_webhook_secret' => 'Verifies the Vodacom callback signature.',
    ];

    public bool $isOpen = false;

    public string $activeTab = 'general';

    public ?string $settingType = null;

    public ?string $settingKind = null;

    public ?string $settingLabel = null;

    public ?string $value = null;

    public ?TemporaryUploadedFile $upload = null;

    public ?string $currentImageUrl = null;

    public ?string $editingType = null;

    public array $palette = [];

    public bool $paletteDataInvalid = false;

    public function selectTab(string $tab): void
    {
        abort_unless(auth()->user()?->can('settings.view'), 403);
        abort_unless(array_key_exists($tab, self::TABS), 404);

        $this->activeTab = $tab;
    }

    public function activatePalette(string $type): void
    {
        $this->authorizeAdmin();

        abort_unless(preg_match('/\A(backend|frontend)_palette_[1-4]\z/', $type) === 1, 404);

        $paletteSetting = BusinessSetting::query()
            ->where('type', $type)
            ->firstOrFail();
        $palette = json_decode($paletteSetting->value, true);

        abort_unless(
            is_array($palette)
                && collect(self::PALETTE_FIELDS)->every(
                    fn (string $field): bool => isset($palette[$field])
                        && preg_match('/\A#[0-9A-Fa-f]{6}\z/', (string) $palette[$field]) === 1,
                ),
            422,
            'This palette has invalid color data.',
        );

        $prefix = str_starts_with($type, 'backend_') ? 'backend' : 'frontend';
        $activeType = "{$prefix}_palette_active";
        $paletteNumber = substr($type, -1);

        BusinessSetting::updateOrCreate(
            ['type' => $activeType],
            ['value' => $paletteNumber, 'lang' => null],
        );

        if ($prefix === 'backend') {
            $this->dispatch('admin-palette-activated', palette: $palette);
        }

        $this->activeTab = $prefix === 'backend' ? 'admin-design' : 'storefront-design';
        session()->flash('message', ucfirst($prefix).' palette "'.($palette['name'] ?? 'Unnamed').'" is now active.');
    }

    public function toggleMaintenanceMode(): void
    {
        $this->authorizeAdmin();

        $setting = BusinessSetting::query()
            ->where('type', 'maintenance_mode')
            ->firstOrFail();
        $setting->update([
            'value' => $setting->value === '1' ? '0' : '1',
        ]);

        session()->flash('message', 'Maintenance mode '.($setting->value === '1' ? 'enabled.' : 'disabled.'));
    }

    public function toggleBoolean(string $type): void
    {
        $this->authorizeAdmin();

        $definition = $this->definitions()[$type] ?? abort(404);
        abort_unless($definition['kind'] === 'boolean', 404);

        $setting = BusinessSetting::query()
            ->where('type', $type)
            ->firstOrFail();
        $setting->update([
            'value' => $setting->value === '1' ? '0' : '1',
        ]);

        $this->activeTab = $this->tabForGroup($definition['group']);
        session()->flash('message', $definition['label'].' '.($setting->value === '1' ? 'enabled.' : 'disabled.'));
    }

    public function updatedSettingType(?string $type): void
    {
        $this->authorizeAdmin();

        $definition = $this->definitions()[$type] ?? null;

        $this->settingKind = $definition['kind'] ?? null;
        $this->settingLabel = $definition['label'] ?? null;
        $this->value = match ($this->settingKind) {
            'boolean' => '0',
            'color' => '#4f46e5',
            'currency_code' => 'USD',
            'font' => 'Inter',
            'admin_font' => 'Inter',
            'font_size' => '1rem',
            'palette_selection' => '1',
            default => '',
        };
        $this->upload = null;
        $this->currentImageUrl = null;
        $this->palette = $this->emptyPalette();
        $this->paletteDataInvalid = false;
        $this->resetValidation();
    }

    public function edit(string $type): void
    {
        $this->authorizeAdmin();
        $definition = $this->definitions()[$type] ?? abort(404);
        $setting = BusinessSetting::query()
            ->where('type', $type)
            ->first();

        abort_if($setting === null && $definition['kind'] !== 'image', 404);

        $this->resetValidation();
        $this->settingType = $type;
        $this->settingKind = $definition['kind'];
        $this->settingLabel = $definition['label'];
        $this->editingType = $setting?->type;
        $this->value = $setting?->value;
        $this->upload = null;
        $this->currentImageUrl = $definition['kind'] === 'image'
            ? $this->storedImageUrl($setting)
            : null;
        $this->paletteDataInvalid = false;
        $this->resetPickedMedia();

        if ($this->settingKind === 'palette') {
            $palette = json_decode($setting->value, true);
            $this->paletteDataInvalid = json_last_error() !== JSON_ERROR_NONE || ! is_array($palette);
            $this->palette = array_merge(
                $this->emptyPalette(),
                is_array($palette) ? array_intersect_key($palette, $this->emptyPalette()) : [],
            );
        }

        $this->isOpen = true;
    }

    public function save(): void
    {
        $this->authorizeAdmin();

        if ($this->editingType !== null && $this->editingType !== $this->settingType) {
            abort(403);
        }

        $settingType = $this->settingType;
        $definition = $settingType === null ? null : ($this->definitions()[$settingType] ?? null);

        if ($settingType === null || $definition === null) {
            abort(403);
        }

        $existingSetting = BusinessSetting::query()
            ->where('type', $settingType)
            ->first();

        if ($definition['kind'] === 'image') {
            abort_if($existingSetting !== null && $this->editingType === null, 403);
        } else {
            abort_unless($this->editingType === $settingType && $existingSetting !== null, 403);
        }

        $validationRules = [
            'settingType' => [
                'required',
                Rule::in(array_keys($this->definitions())),
                Rule::unique('business_settings', 'type')->ignore($this->editingType, 'type'),
            ],
        ];

        if ($definition['kind'] === 'image') {
            // A picture chosen from the library replaces the upload, but it
            // still has to satisfy the same size rules as a fresh file.
            $pickError = $this->pickedImageError($settingType);

            if ($pickError !== null) {
                $this->addError('upload', $pickError);

                return;
            }

            $validationRules['upload'] = $this->pickedMediaIdsFor(self::BRANDING_PICK) === []
                ? $this->imageRules($settingType)
                : ['nullable'];
        } else {
            $validationRules = [...$validationRules, ...$this->valueRules($definition['kind'], $settingType)];
        }

        $validated = $this->validate($validationRules);

        if ($definition['kind'] === 'image') {
            $oldImagePath = $existingSetting?->value;

            $setting = BusinessSetting::updateOrCreate(
                ['type' => $settingType],
                ['value' => null, 'lang' => null],
            );

            if (($validated['upload'] ?? null) !== null) {
                $setting->addLibraryMedia($validated['upload'], 'branding', $settingType);
                // A fresh upload replaces an image that was only pointed at.
                $setting->clearLibraryMediaReferences('branding');
            } else {
                $this->referencePickedMedia($setting, self::BRANDING_PICK, 'branding');
            }

            $this->deleteStoredImage($oldImagePath, $settingType);
            $this->activeTab = 'general';
            session()->flash('message', $this->editingType === null
                ? 'Business image uploaded successfully.'
                : 'Business image replaced successfully.');

            $this->redirect(route('admin.settings'), navigate: true);

            return;
        }

        $value = match ($definition['kind']) {
            'palette' => json_encode($validated['palette'], JSON_THROW_ON_ERROR),
            'textarea' => RichText::sanitize((string) ($validated['value'] ?? '')) ?? '',
            default => $validated['value'],
        };

        BusinessSetting::updateOrCreate(
            ['type' => $validated['settingType']],
            ['value' => $value, 'lang' => null],
        );

        if ($definition['kind'] === 'palette' && str_starts_with($validated['settingType'], 'backend_')) {
            $activeNumber = BusinessSetting::query()
                ->where('type', 'backend_palette_active')
                ->value('value');

            if ($activeNumber === substr($validated['settingType'], -1)) {
                $this->dispatch('admin-palette-activated', palette: $validated['palette']);
            }
        }

        $this->activeTab = $this->tabForGroup($definition['group']);

        session()->flash('message', $this->editingType === null
            ? 'Setting created successfully.'
            : 'Setting updated successfully.');

        $this->isOpen = false;
        $this->reset(['settingType', 'settingKind', 'settingLabel', 'value', 'upload', 'currentImageUrl', 'editingType', 'palette']);
        $this->paletteDataInvalid = false;
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('settings.view'), 403);

        $fontFamilyCss = [
            'Inter' => 'Inter, sans-serif',
            'Poppins' => 'Poppins, sans-serif',
            'Playfair Display' => '"Playfair Display", serif',
            'Plus Jakarta Sans' => '"Plus Jakarta Sans", sans-serif',
            'Arial' => 'Arial, sans-serif',
            'Georgia' => 'Georgia, serif',
            'System' => 'system-ui, sans-serif',
        ];
        $fontRoleDescriptions = [
            'font_backend_primary_header' => 'Page and section titles',
            'font_backend_sub_header' => 'Subtitles and labels',
            'font_backend_accent' => 'Highlights and quotes',
            'font_backend_body' => 'Paragraphs and tables',
            'font_frontend_primary_header' => 'Page and section titles',
            'font_frontend_sub_header' => 'Subtitles and labels',
            'font_frontend_accent' => 'Highlights and quotes',
            'font_frontend_body' => 'Paragraphs and product details',
        ];
        $fontSizeDescriptions = [
            'font_size_base' => 'Default paragraph and interface text',
            'font_size_heading' => 'Page and section titles',
            'font_size_sub' => 'Subtitles and supporting text',
            'font_size_accent' => 'Highlighted text and callouts',
            'font_size_small' => 'Captions and fine print',
        ];

        $definitions = $this->definitions();
        $settingsByType = BusinessSetting::query()
            ->whereIn('type', array_keys($definitions))
            ->get()
            ->keyBy('type');

        $settingGroups = [];
        $maintenanceMode = $settingsByType->get('maintenance_mode')?->value === '1';

        foreach ($definitions as $type => $definition) {
            $setting = $settingsByType->get($type);

            if (
                ($setting === null && $definition['kind'] !== 'image')
                || $definition['kind'] === 'palette_selection'
                || $type === 'maintenance_mode'
            ) {
                continue;
            }

            $settingValue = $setting?->value ?? '';
            $paletteData = $definition['kind'] === 'palette'
                ? json_decode($settingValue, true)
                : null;
            $palettePrefix = str_starts_with($type, 'backend_palette_') ? 'backend' : 'frontend';
            $activePalette = $settingsByType->get("{$palettePrefix}_palette_active")?->value ?? '1';
            $isValidPalette = is_array($paletteData)
                && collect(self::PALETTE_FIELDS)->every(
                    fn (string $field): bool => isset($paletteData[$field])
                        && preg_match('/\A#[0-9A-Fa-f]{6}\z/', (string) $paletteData[$field]) === 1,
                );

            $imageUrl = $definition['kind'] === 'image'
                ? $this->storedImageUrl($setting)
                : null;
            $displayValue = match ($definition['kind']) {
                'boolean' => $settingValue === '1' ? 'Enabled' : 'Disabled',
                'palette' => is_array($paletteData) ? ($paletteData['name'] ?? 'Unnamed palette') : 'Invalid palette JSON',
                'palette_selection' => 'Palette '.$settingValue,
                'image' => $imageUrl === null ? 'Not uploaded' : 'Uploaded',
                'secret' => $settingValue === '' ? 'Not set' : '••••••••',
                'textarea' => ($plain = RichText::plainText($settingValue, 220)) === '' ? 'Not set' : $plain,
                default => $settingValue === '' ? 'Not set' : $settingValue,
            };

            $settingGroups[$definition['group']][] = [
                'type' => $type,
                'label' => $definition['label'],
                'kind' => $definition['kind'],
                'displayValue' => $displayValue,
                'fontFamilyCss' => $definition['kind'] === 'font'
                    ? ($fontFamilyCss[$settingValue] ?? $fontFamilyCss['System'])
                    : null,
                'fontRoleDescription' => $fontRoleDescriptions[$type] ?? null,
                'fontSizeCss' => $definition['kind'] === 'font_size'
                    && preg_match('/\A\d+(?:\.\d+)?(?:px|rem|em|%)\z/', $settingValue) === 1
                    ? $settingValue
                    : null,
                'fontSizeDescription' => $fontSizeDescriptions[$type] ?? null,
                'palette' => $paletteData,
                'isValidPalette' => $definition['kind'] === 'palette' && $isValidPalette,
                'isActive' => $definition['kind'] === 'palette'
                    && substr($type, -1) === $activePalette,
                'previewColor' => $definition['kind'] === 'color'
                    ? $setting->value
                    : ($definition['kind'] === 'palette' && is_array($paletteData)
                        ? ($paletteData['primary'] ?? null)
                        : null),
                'imageUrl' => $imageUrl,
                'exists' => $setting !== null,
                'isEnabled' => $definition['kind'] === 'boolean' && $settingValue === '1',
                'hint' => self::COMMERCE_HINTS[$type] ?? null,
            ];
        }

        $tabs = [];

        foreach (self::TABS as $tabId => $tab) {
            $tabSettings = collect($tab['groups'])
                ->flatMap(fn (string $group): array => $settingGroups[$group] ?? [])
                ->values();

            $tabs[$tabId] = [
                ...$tab,
                'count' => $tabSettings->count(),
                'settings' => collect($tab['groups'])
                    ->filter(fn (string $group): bool => isset($settingGroups[$group]))
                    ->mapWithKeys(fn (string $group): array => [$group => $settingGroups[$group]])
                    ->all(),
            ];
        }

        if (! array_key_exists($this->activeTab, $tabs)) {
            $this->activeTab = 'general';
        }

        return view('livewire.admin.settings', [
            'tabs' => $tabs,
            'activeSettings' => $tabs[$this->activeTab]['settings'],
            'maintenanceMode' => $maintenanceMode,
        ])->layout('components.layouts.admin', ['title' => 'Settings']);
    }

    /**
     * @return array<string, array{group: string, label: string, kind: string}>
     */
    private function definitions(): array
    {
        $definitions = [
            'site_name' => ['group' => 'Basic settings', 'label' => 'Site name', 'kind' => 'text'],
            'site_motto' => ['group' => 'Basic settings', 'label' => 'Site motto', 'kind' => 'text'],
            'footer_about' => ['group' => 'Basic settings', 'label' => 'Footer about us', 'kind' => 'textarea'],
            'contact_email' => ['group' => 'Contact & social', 'label' => 'Contact email', 'kind' => 'email'],
            'contact_phone' => ['group' => 'Contact & social', 'label' => 'Contact phone', 'kind' => 'phone'],
            'whatsapp_number' => ['group' => 'Contact & social', 'label' => 'WhatsApp number', 'kind' => 'whatsapp'],
            'social_facebook' => ['group' => 'Contact & social', 'label' => 'Facebook URL', 'kind' => 'url'],
            'social_instagram' => ['group' => 'Contact & social', 'label' => 'Instagram URL', 'kind' => 'url'],
            'social_x' => ['group' => 'Contact & social', 'label' => 'X (Twitter) URL', 'kind' => 'url'],
            'social_youtube' => ['group' => 'Contact & social', 'label' => 'YouTube URL', 'kind' => 'url'],
            'social_linkedin' => ['group' => 'Contact & social', 'label' => 'LinkedIn URL', 'kind' => 'url'],
            'social_tiktok' => ['group' => 'Contact & social', 'label' => 'TikTok URL', 'kind' => 'url'],
            'currency_code' => ['group' => 'Basic settings', 'label' => 'Currency code', 'kind' => 'currency_code'],
            'currency_symbol' => ['group' => 'Basic settings', 'label' => 'Currency symbol', 'kind' => 'text'],
            'default_language' => ['group' => 'Basic settings', 'label' => 'Default language', 'kind' => 'language'],
            'default_country_id' => ['group' => 'Basic settings', 'label' => 'Default country ID', 'kind' => 'country'],
            'flat_rate_shipping_cost' => ['group' => 'Shipping', 'label' => 'Flat-rate shipping cost', 'kind' => 'decimal'],
            'free_shipping' => ['group' => 'Shipping', 'label' => 'Free shipping', 'kind' => 'boolean'],
            'cash_on_delivery' => ['group' => 'Payment methods', 'label' => 'Cash on delivery', 'kind' => 'boolean'],
            'lipa_enabled' => ['group' => 'Payment methods', 'label' => 'Vodacom Lipa', 'kind' => 'boolean'],
            'lipa_requires_verification' => ['group' => 'Payment methods', 'label' => 'Verify Lipa payments before processing', 'kind' => 'boolean'],
            'lipa_number' => ['group' => 'Vodacom Lipa', 'label' => 'Lipa number', 'kind' => 'lipa_number'],
            'lipa_business_name' => ['group' => 'Vodacom Lipa', 'label' => 'Business name shown to the payer', 'kind' => 'text'],
            'lipa_instructions' => ['group' => 'Vodacom Lipa', 'label' => 'Payment instructions', 'kind' => 'textarea'],
            'lipa_webhook_secret' => ['group' => 'Vodacom Lipa', 'label' => 'Callback signing secret', 'kind' => 'secret'],
            'maintenance_mode' => ['group' => 'Basic settings', 'label' => 'Maintenance mode', 'kind' => 'boolean'],
            'backend_palette_active' => ['group' => 'Backend palettes', 'label' => 'Active backend palette', 'kind' => 'palette_selection'],
            'frontend_palette_active' => ['group' => 'Frontend palettes', 'label' => 'Active frontend palette', 'kind' => 'palette_selection'],
            'font_size_base' => ['group' => 'Frontend font sizes', 'label' => 'Base font size', 'kind' => 'font_size'],
            'font_size_heading' => ['group' => 'Frontend font sizes', 'label' => 'Heading font size', 'kind' => 'font_size'],
            'font_size_sub' => ['group' => 'Frontend font sizes', 'label' => 'Subheading font size', 'kind' => 'font_size'],
            'font_size_accent' => ['group' => 'Frontend font sizes', 'label' => 'Accent font size', 'kind' => 'font_size'],
            'font_size_small' => ['group' => 'Frontend font sizes', 'label' => 'Small font size', 'kind' => 'font_size'],
            'appearance_primary_color' => ['group' => 'Admin appearance', 'label' => 'Admin primary color', 'kind' => 'color'],
            'appearance_font_family' => ['group' => 'Admin appearance', 'label' => 'Admin font family', 'kind' => 'admin_font'],
        ];

        foreach (['backend' => 'Backend palettes', 'frontend' => 'Frontend palettes'] as $prefix => $group) {
            foreach (range(1, 4) as $number) {
                $definitions["{$prefix}_palette_{$number}"] = [
                    'group' => $group,
                    'label' => ucfirst($prefix).' palette '.$number,
                    'kind' => 'palette',
                ];
            }
        }

        foreach ([
            'font_backend_primary_header' => ['Backend fonts', 'Primary header font'],
            'font_backend_sub_header' => ['Backend fonts', 'Subheader font'],
            'font_backend_accent' => ['Backend fonts', 'Accent font'],
            'font_backend_body' => ['Backend fonts', 'Body font'],
            'font_frontend_primary_header' => ['Frontend fonts', 'Primary header font'],
            'font_frontend_sub_header' => ['Frontend fonts', 'Subheader font'],
            'font_frontend_accent' => ['Frontend fonts', 'Accent font'],
            'font_frontend_body' => ['Frontend fonts', 'Body font'],
        ] as $type => [$group, $label]) {
            $definitions[$type] = ['group' => $group, 'label' => $label, 'kind' => 'font'];
        }

        $definitions['business_logo'] = [
            'group' => 'Brand assets',
            'label' => 'Business logo',
            'kind' => 'image',
        ];
        $definitions['business_logo_dark'] = [
            'group' => 'Brand assets',
            'label' => 'Dark-mode logo',
            'kind' => 'image',
        ];
        $definitions['favicon'] = [
            'group' => 'Brand assets',
            'label' => 'Favicon',
            'kind' => 'image',
        ];

        return $definitions;
    }

    /**
     * @return array<int, string>
     */
    /**
     * Check a picture picked from the media library against the same limits a
     * fresh upload has to pass. Returns the message to show, or null.
     */
    private function pickedImageError(string $settingType): ?string
    {
        $media = $this->pickedMediaItems(self::BRANDING_PICK)->first();

        if ($media === null) {
            return null;
        }

        $size = @getimagesize($media->getPath());

        if ($size === false) {
            return 'The picture picked from the media library could not be read.';
        }

        [$width, $height] = $size;

        if ($settingType === 'favicon') {
            if (strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION)) !== 'png') {
                return 'Pick a PNG picture for the favicon.';
            }

            if ($width !== $height || $width < 16 || $width > 512) {
                return 'Pick a square PNG between 16 and 512 pixels for the favicon.';
            }

            return null;
        }

        if ($width > 4096 || $height > 2048) {
            return 'Pick a picture no larger than 4096 by 2048 pixels.';
        }

        return null;
    }

    private function imageRules(string $type): array
    {
        $mimes = $type === 'favicon'
            ? ['png']
            : ['jpg', 'jpeg', 'png', 'webp'];

        $rules = [
            'required',
            'image',
            'mimes:'.implode(',', $mimes),
            'extensions:'.implode(',', $mimes),
            'max:2048',
        ];

        if ($type === 'favicon') {
            $rules[] = 'dimensions:min_width=16,min_height=16,max_width=512,max_height=512,ratio=1/1';
        } else {
            $rules[] = 'dimensions:max_width=4096,max_height=2048';
        }

        return $rules;
    }

    private function storedImageUrl(?BusinessSetting $setting): ?string
    {
        return $setting?->imageUrl();
    }

    private function deleteStoredImage(?string $path, string $type): void
    {
        $extensions = $type === 'favicon' ? 'png' : 'jpg|jpeg|png|webp';

        if (
            $path === null
            || preg_match('/\Abusiness-branding\/'.$type.'\/[A-Za-z0-9_-]+\.(?:'.$extensions.')\z/i', $path) !== 1
        ) {
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($path) && ! $disk->delete($path)) {
            throw new \RuntimeException('Unable to remove the previous business image.');
        }
    }

    /**
     * @return array<string, array<int|string, mixed>>
     */
    private function valueRules(string $kind, ?string $type = null): array
    {
        if ($kind === 'palette') {
            $rules = [
                'palette' => ['required', 'array:name,'.implode(',', self::PALETTE_FIELDS)],
                'palette.name' => ['required', 'string', 'max:80'],
            ];

            foreach (self::PALETTE_FIELDS as $field) {
                $rules["palette.{$field}"] = ['required', 'regex:/\A#[0-9A-Fa-f]{6}\z/'];
            }

            return $rules;
        }

        $rules = match ($kind) {
            'boolean' => ['required', Rule::in(['0', '1'])],
            'color' => ['required', 'regex:/\A#[0-9A-Fa-f]{6}\z/'],
            'country' => ['required', 'integer', 'min:1'],
            'currency_code' => ['required', 'regex:/\A[A-Z]{3}\z/'],
            'decimal' => ['required', 'numeric', 'min:0'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'font' => ['required', Rule::in(self::FONT_FAMILIES)],
            'admin_font' => ['required', Rule::in(['Inter', 'Arial', 'Georgia', 'System'])],
            'font_size' => ['required', 'regex:/\A\d+(?:\.\d+)?(?:px|rem|em|%)\z/'],
            'language' => ['required', 'regex:/\A[a-zA-Z]{2,5}(?:[-_][a-zA-Z0-9]{2,8})?\z/'],
            'lipa_number' => ['required', 'regex:/\A\d{5,9}\z/'],
            'palette_selection' => ['required', 'integer', 'between:1,4'],
            'phone' => ['nullable', 'regex:/\A[+\d\s().-]{5,35}\z/'],
            'secret' => ['nullable', 'string', 'max:255'],
            'textarea' => [
                $type === 'footer_about' ? 'nullable' : 'required',
                'string',
                'max:10000',
                function (string $attribute, mixed $value, \Closure $fail) use ($type): void {
                    if ($type !== 'footer_about' && RichText::sanitize((string) $value) === null) {
                        $fail('The payment instructions field is required.');
                    }
                },
            ],
            'url' => ['nullable', 'url:http,https', 'max:500'],
            'whatsapp' => [
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $trimmed = trim((string) $value);
                    if ($trimmed === '') {
                        return;
                    }

                    if (preg_match('/\Ahttps?:\/\//i', $trimmed) === 1) {
                        $scheme = strtolower((string) parse_url($trimmed, PHP_URL_SCHEME));
                        if (filter_var($trimmed, FILTER_VALIDATE_URL) === false || ! in_array($scheme, ['http', 'https'], true)) {
                            $fail('Enter a valid WhatsApp phone number or https://wa.me link.');
                        }

                        return;
                    }

                    $digits = preg_replace('/\D+/', '', $trimmed) ?? '';
                    if (preg_match('/\A[+\d\s().-]+\z/', $trimmed) !== 1 || strlen($digits) < 7 || strlen($digits) > 15) {
                        $fail('Enter a valid WhatsApp phone number (7-15 digits) or WhatsApp URL.');
                    }
                },
            ],
            default => ['required', 'string', 'max:500'],
        };

        return ['value' => $rules];
    }

    /**
     * @return array<string, string>
     */
    private function emptyPalette(): array
    {
        return array_fill_keys(['name', ...self::PALETTE_FIELDS], '');
    }

    private function tabForGroup(string $group): string
    {
        foreach (self::TABS as $tabId => $tab) {
            if (in_array($group, $tab['groups'], true)) {
                return $tabId;
            }
        }

        return 'general';
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->can('settings.manage'), 403);
    }
}
